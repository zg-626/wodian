<?php

namespace app\common\repositories\user;

use app\common\model\store\order\StoreOrderOffline;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\BaseRepository;
use think\facade\Db;

class BonusOfflineService extends BaseRepository
{
    // 环比增长率
    protected $growthRate = 1.15;
    // 第一期分红的金额阈值
    protected $initialThreshold = 7000;
    // 用户分红比例
    protected $userRatio = 0.5;
    // 商家分红比例
    protected $merchantRatio = 0.5;

    /**
     * 计算并执行分红
     * @return bool|array
     */
    public function calculateBonus()
    {
        Db::startTrans();
        try {
            // 获取分红池可用金额
            $poolInfo = Db::name('dividend_pool')->where('id',6)->order('id', 'desc')->find();
            //print_r($poolInfo);exit;
            if (!$poolInfo || $poolInfo['available_amount'] <= 0) {
                return false;
            }

            // 保留两位小数
            $totalAmount = round($poolInfo['available_amount'], 2);

            // 检查分红条件
            if (!$this->checkBonusCondition($totalAmount, $poolInfo)) {
                return false;
            }

            // 获取参与分红的用户和商家
            $users = $this->getValidUsers();
            $merchants = $this->getValidMerchants();

            // 计算分红金额
            $userBonus = $totalAmount * $this->userRatio;
            $merchantBonus = $totalAmount * $this->merchantRatio;

            // 执行分红
            $userBonusAmounts = $this->distributeUserBonus($users, $userBonus);
            $merchantBonusAmounts = $this->distributeMerchantBonus($merchants, $merchantBonus);

            // 记录分红日志
            $poolId = $this->recordDividendPeriod($totalAmount, $poolInfo);
            $this->recordDividendLog($poolId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts);

            // 更新分红池可用金额
            Db::name('dividend_pool')->where('id', $poolInfo['id'])->update([
                'available_amount' => 0,
                'distributed_amount' => Db::raw('distributed_amount + ' . $totalAmount),
                'update_time' => date('Y-m-d H:i:s')
            ]);

            Db::commit();
            return [
                'total_amount' => $totalAmount,
                'user_bonus' => $userBonus,
                'merchant_bonus' => $merchantBonus
            ];
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 检查分红条件
     */
    protected function checkBonusCondition($totalAmount, $poolInfo)
    {
         // 获取上一次分红记录
        $lastBonusRecord = Db::name('dividend_period_log')->order('id', 'desc')->find();
        
        if (!$lastBonusRecord) {
            return $totalAmount >= $this->initialThreshold;
        }
        return $totalAmount / $lastBonusRecord['total_amount'] >= $this->growthRate;
    }

    /**
     * 获取有效用户
     */
    protected function getValidUsers()
    {
        return User::where('status', 1)
            ->where('integral', '>', 0)
            ->field('uid, integral,coupon_amount')
            ->select();
    }

    /**
     * 获取有效商家
     */
    protected function getValidMerchants()
    {
        return Merchant::where('is_del', 0)
            ->where('integral', '>', 0)
            ->field('mer_id, integral,coupon_amount')
            ->select();
    }

    /**
     * 分配用户分红
     */
    protected function distributeUserBonus($users, $totalBonus)
    {
        $totalUserPoints = array_sum(array_column($users->toArray(), 'integral'));
        $bonusAmounts = []; // 记录每个用户的分红金额
        
        foreach ($users as $user) {
            $bonus = round($user->integral * $totalBonus / $totalUserPoints, 2);// 两位小数
            $bonusAmounts[$user->uid] = $bonus; // 保存分红金额
            
            Db::name('user')->where('uid', $user->uid)->inc('coupon_amount', $bonus)->update();
            
            // 记录用户分红明细
            Db::name('user_bill')->insert([
                'uid' => $user->uid,
                'link_id' => 0,
                'pm' => 1,
                'title' => '分红收益',
                'category' => 'coupon_amount',
                'type' => 'dividend',
                'number' => $bonus,
                'balance' => $user->coupon_amount + $bonus,
                'mark' => '金额分红收益' . $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        return $bonusAmounts;
    }

    /**
     * 分配商家分红
     */
    protected function distributeMerchantBonus($merchants, $totalBonus)
    {
        $totalMerchantPoints = array_sum(array_column($merchants->toArray(), 'integral'));
        foreach ($merchants as $merchant) {
            $bonus = round(($merchant->integral / $totalMerchantPoints) * $totalBonus,2);
            Db::name('merchant')->where('mer_id', $merchant->mer_id)->inc('coupon_amount', $bonus)->update();
            
            // 记录商家分红明细
            Db::name('user_bill')->insert([
                'mer_id' => $merchant->mer_id,
                'link_id' => 0,
                'pm' => 1,
                'title' => '分红收益',
                'category' => 'coupon_amount',
                'type' => 'dividend',
                'number' => $bonus,
                'balance' => $merchant->coupon_amount + $bonus,
                'mark' => '金额分红收益' . $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 记录分红池日志
     */
    protected function recordDividendPeriod($totalAmount, $poolInfo)
    {
        $period = 1;
        if ($lastLog = Db::name('dividend_period_log')->order('id', 'desc')->find()) {
            $period = $lastLog['period'] + 1;
        }
        
        return Db::name('dividend_period_log')->insertGetId([
            'period' => $period,
            'total_amount' => $totalAmount,
            'growth_rate' => $lastLog ? $totalAmount / $lastLog['total_amount'] : 0,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 记录分红日志
     * @param int $periodId 分红期数ID
     * @param array $users 用户列表
     * @param array $merchants 商家列表
     * @param array $userBonusAmounts 用户分红金额
     * @param array $merchantBonusAmounts 商家分红金额
     */
    protected function recordDividendLog($periodId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts)
    {
        $logs = [];
        foreach ($users as $user) {
            $bonus = $userBonusAmounts[$user->uid] ?? 0;
            $logs[] = [
                'period_id' => $periodId,
                'type' => 'user',
                'relation_id' => $user->uid,
                'integral' => $user->integral,
                'coupon_amount' => $user->coupon_amount,
                'bonus_amount' => $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        
        foreach ($merchants as $merchant) {
            $bonus = $merchantBonusAmounts[$merchant->mer_id] ?? 0;
            $logs[] = [
                'period_id' => $periodId,
                'type' => 'merchant',
                'relation_id' => $merchant->mer_id,
                'integral' => $merchant->integral,
                'coupon_amount' => $merchant->coupon_amount,
                'bonus_amount' => $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        Db::name('dividend_distribution_log')->insertAll($logs);
    }
}

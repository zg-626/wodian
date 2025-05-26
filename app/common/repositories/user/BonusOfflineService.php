<?php

namespace app\common\repositories\user;

use app\common\model\store\order\StoreOrder;
use app\common\model\store\order\StoreOrderOffline;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\BaseRepository;
use think\facade\Db;

class BonusOfflineService extends BaseRepository
{
    // 环比增长率
    protected $growthRate = 1.15;// 增长率
    // 第一期分红的金额阈值
    protected $initialThreshold = 20000;
    protected $baseAmount = 20000; // 基础金额
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
            // 获取城市分红池可用金额
            $poolInfo = Db::name('dividend_pool')->where('city_id','<>',0)->order('id', 'desc')->select()->toArray();

            foreach ($poolInfo as $key => $value) {
                if (!$value || $value['available_amount'] <= 0) {
                    return false;
                }
                // 保留两位小数
                $totalAmount = round($value['available_amount'], 2);

                // 获取上一次分红记录
                $lastBonusRecord = Db::name('dividend_period_log')->where('id',$value['id'])->order('id', 'desc')->find();
                
                // 计算本次可分红金额
                $bonusAmount = 0;
                if (!$lastBonusRecord) {
                    // 首次分红，判断是否达到初始阈值
                    if ($totalAmount >= $this->initialThreshold) {
                        $bonusAmount = $totalAmount - $this->baseAmount;
                    }
                } else {
                    // 非首次分红，计算增长部分
                    $growthThreshold = $lastBonusRecord['total_amount'] * $this->growthRate;
                    if ($totalAmount >= $growthThreshold) {
                        $bonusAmount = $totalAmount - $this->baseAmount;
                    }
                }

                // 如果没有可分红金额，跳过
                if ($bonusAmount <= 0) {
                    continue;
                }

                // 获取当前城市参与分红的用户和商家
                $users = $this->getValidUsers($value);
                $merchants = $this->getValidMerchants($value);

                // 计算分红金额
                $userBonus = $bonusAmount * $this->userRatio;
                $merchantBonus = $bonusAmount * $this->merchantRatio;

                // 执行分红
                $userBonusAmounts = $this->distributeUserBonus($users, $userBonus);
                $merchantBonusAmounts = $this->distributeMerchantBonus($merchants, $merchantBonus);

                // 记录分红日志
                $poolId = $this->recordDividendPeriod($bonusAmount, $value);
                $this->recordDividendLog($poolId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts);

                // 更新分红池，保留基础金额
                Db::name('dividend_pool')->where('id', $value['id'])->update([
                    'available_amount' => $this->baseAmount,
                    'grand_amount' => Db::raw('grand_amount + '. $this->baseAmount),// 累计未发放的分红金额,用于月底发放
                    'distributed_amount' => Db::raw('distributed_amount + ' . $bonusAmount),
                    'update_time' => date('Y-m-d H:i:s')
                ]);

                Db::commit();
                return [
                    'total_amount' => $totalAmount,
                    'bonus_amount' => $bonusAmount,
                    'user_bonus' => $userBonus,
                    'merchant_bonus' => $merchantBonus,
                    'base_amount' => $this->baseAmount
                ];
            }

        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 检查分红条件
     */
    protected function checkBonusCondition($totalAmount, $poolInfo): bool
    {
         // 获取上一次分红记录
        $lastBonusRecord = Db::name('dividend_period_log')->where('id',$poolInfo['id'])->order('id', 'desc')->find();
        
        if (!$lastBonusRecord) {
            return $totalAmount >= $this->initialThreshold;
        }
        return $totalAmount / $lastBonusRecord['total_amount'] >= $this->growthRate;
    }

    /**
     * 获取有效用户
     */
    protected function getValidUsers($value)
    {
        /*$user= User::where('status', 1)
            ->where('integral', '>', 0)
            ->field('uid, integral,coupon_amount')
            ->select();*/
        $user =Db::name('store_order_offline')->alias('s')->join('User b', 's.uid = b.uid')->where('s.city_id',$value['city_id'])->where(['s.paid' => 1])->field('s.city,s.city_id,s.uid,b.coupon_amount,b.integral')->group('uid')->select();
        return $user;
     }

    /**
     * 获取有效商家
     */
    protected function getValidMerchants($value)
    {
        /*return Merchant::where('is_del', 0)
            ->where('integral', '>', 0)
            ->field('mer_id, integral,coupon_amount')
            ->select();*/
        $merchant =Db::name('store_order_offline')->alias('s')->join('Merchant b', 's.mer_id = b.mer_id')->where('s.city_id',$value['city_id'])->where(['b.status' => 1,'s.paid' => 1])->field('b.city,b.city_id,s.mer_id,b.coupon_amount,b.integral')->group('mer_id')->select();
        return $merchant;
    }

    /**
     * 分配用户分红
     */
    protected function distributeUserBonus($users, $totalBonus): array
    {
        $totalUserPoints = array_sum(array_column($users->toArray(), 'integral'));
        $bonusAmounts = []; // 记录每个用户的分红金额
        foreach ($users->toArray() as $user) {
            $bonus = round($user['integral'] * $totalBonus / $totalUserPoints, 2);// 两位小数
            $bonusAmounts[$user['uid']] = $bonus; // 保存分红金额
            
            Db::name('user')->where('uid', $user['uid'])->inc('coupon_amount', $bonus)->update();
            
            // 记录用户分红明细
            Db::name('user_bill')->insert([
                'uid' => $user['uid'],
                'link_id' => 0,
                'pm' => 1,
                'title' => '分红收益',
                'category' => 'coupon_amount',
                'type' => 'dividend',
                'number' => $bonus,
                'balance' => $user['coupon_amount'] + $bonus,
                'mark' => '金额分红收益' . $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        return $bonusAmounts;
    }

    /**
     * 分配商家分红
     */
    protected function distributeMerchantBonus($merchants, $totalBonus): array
    {
        $totalMerchantPoints = array_sum(array_column($merchants->toArray(), 'integral'));
        $bonusAmounts = []; // 记录每个商家的分红金额
        foreach ($merchants as $merchant) {
            $bonus = round(($merchant['integral'] / $totalMerchantPoints) * $totalBonus,2);
            $bonusAmounts[$merchant['mer_id']] = $bonus; // 保存分红金额
            Db::name('merchant')->where('mer_id', $merchant['mer_id'])->inc('coupon_amount', $bonus)->update();
            
            // 记录商家分红明细
            Db::name('user_bill')->insert([
                'mer_id' => $merchant['mer_id'],
                'link_id' => 0,
                'pm' => 1,
                'title' => '分红收益',
                'category' => 'coupon_amount',
                'type' => 'dividend',
                'number' => $bonus,
                'balance' => $merchant['coupon_amount'] + $bonus,
                'mark' => '金额分红收益' . $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        return $bonusAmounts;
    }

    /**
     * 记录分红池日志
     */
    protected function recordDividendPeriod($totalAmount, $poolInfo)
    {
        $period = 1;
        if ($lastLog = Db::name('dividend_period_log')->where('id',$poolInfo['id'])->order('id', 'desc')->find()) {
            $period = $lastLog['period'] + 1;
        }
        
        return Db::name('dividend_period_log')->insertGetId([
            'period' => $period,
            'id' => $poolInfo['id'],
            'city_id' => $poolInfo['city_id'],
            'city' => $poolInfo['city'],
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
    protected function recordDividendLog($periodId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts): void
    {
        $logs = [];
        foreach ($users as $user) {
            $bonus = $userBonusAmounts[$user['uid']] ?? 0;
            $logs[] = [
                'period_id' => $periodId,
                'type' => 'user',
                'relation_id' => $user['uid'],
                'integral' => $user['integral'],
                'coupon_amount' => $user['coupon_amount'],
                'bonus_amount' => $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        
        foreach ($merchants as $merchant) {
            $bonus = $merchantBonusAmounts[$merchant['mer_id']] ?? 0;
            $logs[] = [
                'period_id' => $periodId,
                'type' => 'merchant',
                'relation_id' => $merchant['mer_id'],
                'integral' => $merchant['integral'],
                'coupon_amount' => $merchant['coupon_amount'],
                'bonus_amount' => $bonus,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        Db::name('dividend_distribution_log')->insertAll($logs);
    }

    /**
     * 月底发放基础金额
     */
    public function distributeBaseAmount()
    {
        Db::startTrans();
        try {
            // 获取所有城市分红池
            $poolInfo = Db::name('dividend_pool')
                ->where('city_id', '<>', 0)
                ->where('grand_amount', '>=', $this->baseAmount)
                ->select()
                ->toArray();

            foreach ($poolInfo as $pool) {
                // 获取参与分红的用户和商家
                $users = $this->getValidUsers($pool);
                $merchants = $this->getValidMerchants($pool);

                // 计算分红金额
                $userBonus = $this->baseAmount * $this->userRatio;
                $merchantBonus = $this->baseAmount * $this->merchantRatio;

                // 执行分红
                $userBonusAmounts = $this->distributeUserBonus($users, $userBonus);
                $merchantBonusAmounts = $this->distributeMerchantBonus($merchants, $merchantBonus);

                // 记录分红日志
                $poolId = $this->recordDividendPeriod($this->baseAmount, $pool);
                $this->recordDividendLog($poolId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts);

                // 更新分红池
                Db::name('dividend_pool')
                    ->where('id', $pool['id'])
                    ->update([
                        'available_amount' => 0,
                        'grand_amount' => 0,
                        'distributed_amount' => Db::raw('distributed_amount + ' . $this->baseAmount),
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}

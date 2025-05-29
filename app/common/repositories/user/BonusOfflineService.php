<?php

namespace app\common\repositories\user;

use app\common\model\store\order\StoreOrder;
use app\common\model\store\order\StoreOrderOffline;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\BaseRepository;
use think\facade\Db;
use think\facade\Log;

class BonusOfflineService extends BaseRepository
{
    // 环比增长率
    protected $growthRate = 1.15;// 增长率
    // 第一期分红的金额阈值
    protected $initialThreshold = 20000;
    // 用户分红比例
    protected $userRatio = 0.5;
    // 商家分红比例
    protected $merchantRatio = 0.5;

    /**
     * 获取当前的基础金额
     * @param int $poolId 分红池ID
     * @return float
     */
    protected function getCurrentBaseAmount($poolId)
    {
        $threshold = Db::name('dividend_pool')
            ->where('id', $poolId)
            ->value('initial_threshold');

        // 如果initial_threshold为null或0或0.00，返回默认基础金额
        return (!$threshold) ? $this->initialThreshold : $threshold;
    }

    /**
     * 计算并执行分红
     * @return bool|array
     */
    public function calculateBonus($pool)
    {
        try {
            // 获取城市分红池可用金额
            $poolInfo = Db::name('dividend_pool')->where('city_id','<>',0)->order('id', 'desc')->select()->toArray();

            //foreach ($poolInfo as $key => $value) {
                if (!$pool || $pool['available_amount'] <= 0) {
                    return true;
                }
                // 保留两位小数
                $totalAmount = round($pool['available_amount'], 2);

                // 获取上一次分红记录
                $lastBonusRecord = Db::name('dividend_period_log')->where('dp_id',$pool['id'])->order('id', 'desc')->find();
                $bonusAmount = 0;
                // 获取当前周期的初始阈值
                $currentThreshold = Db::name('dividend_pool')
                ->where('id', $pool['id'])
                ->value('initial_threshold') ?? $this->initialThreshold;
                // 计算本次可分红金额
                if (!$lastBonusRecord) {
                    // 首次分红，判断是否达到初始阈值且满足增长率
                    $shouldAmount = round($currentThreshold * $this->growthRate, 2);
                    if ($totalAmount >= $shouldAmount && $totalAmount >= $this->initialThreshold) {
                        $bonusAmount = round($totalAmount - $currentThreshold, 2);
                    }
                } else {
                    // 非首次分红，判断是否达到上期阈值且满足增长率
                    $shouldAmount = round($lastBonusRecord['total_amount'] * $this->growthRate, 2);
                    if ($totalAmount >= $shouldAmount) {
                        $bonusAmount = round($totalAmount - $currentThreshold, 2);
                    }
                }

                // 如果没有可分红金额，跳过
                if ($bonusAmount <= 0) {
                    return true;
                }

                // 获取当前城市参与分红的用户和商家
                $users = $this->getValidUsers($pool);
                $merchants = $this->getValidMerchants($pool);

                // 计算分红金额
                $userBonus = round($bonusAmount * $this->userRatio, 2);
                $merchantBonus = round($bonusAmount * $this->merchantRatio, 2);

                // 执行分红
                $userBonusAmounts = $this->distributeUserBonus($users, $userBonus);
                $merchantBonusAmounts = $this->distributeMerchantBonus($merchants, $merchantBonus);

                // 记录分红日志
                $poolId = $this->recordDividendPeriod($bonusAmount, $pool,$totalAmount,$shouldAmount,2);
                $this->recordDividendLog($poolId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts);

                // 获取当前基础金额
                $currentBaseAmount = $this->getCurrentBaseAmount($pool['id']);

                // 更新分红池，保留基础金额
                Db::name('dividend_pool')->where('id', $pool['id'])->update([
                    'available_amount' => $currentBaseAmount,
                    'grand_amount' => $currentBaseAmount,// 累计未发放的分红金额,用于月底发放
                    'distributed_amount' => Db::raw('distributed_amount + ' . $bonusAmount),
                    'update_time' => date('Y-m-d H:i:s')
                ]);

                return [
                    'total_amount' => $totalAmount?? '',
                    'bonus_amount' => $bonusAmount?? '',
                    'user_bonus' => $userBonus?? '',
                    'merchant_bonus' => $merchantBonus?? '',
                    'base_amount' => $this->initialThreshold?? '',
                ];
            //}

        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * 检查分红条件
     */
    protected function checkBonusCondition($totalAmount, $poolInfo): bool
    {
         // 获取上一次分红记录
        $lastBonusRecord = Db::name('dividend_period_log')->where('dp_id',$poolInfo['id'])->order('id', 'desc')->find();
        
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
    protected function recordDividendPeriod($bonusAmount, $poolInfo, $totalAmount, $shouldAmount, $type)
    {
        $period = 1;
        if ($lastLog = Db::name('dividend_period_log')->where('dp_id',$poolInfo['id'])->order('id', 'desc')->find()) {
            $period = $lastLog['period'] + 1;
        }

        return Db::name('dividend_period_log')->insertGetId([
            'period' => $period,
            'dp_id' => $poolInfo['id'],
            'city_id' => $poolInfo['city_id'],
            'city' => $poolInfo['city'],
            'execute_type' => $type,
            'total_amount' => $totalAmount,// 分红总金额
            'actual_amout' => $bonusAmount,// 实际分红金额
            'should_amount' => $shouldAmount,// 应该要达到分红金额
            'growth_rate' => $type === 1 ? 0 : ($lastLog ? $totalAmount / $lastLog['total_amount'] : 0),// 月初分红不记录增长率
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
    public function distributeBaseAmount($pool)
    {
        Db::startTrans();
        try {
            // 获取所有城市分红池
            /*$poolInfo = Db::name('dividend_pool')
                ->where('city_id', '<>', 0)
                ->where('grand_amount', '>=', $this->initialThreshold)
                ->select()
                ->toArray();*/

            if($pool['grand_amount']<$this->initialThreshold){
                return true;
            }

            // 获取参与分红的用户和商家
            $users = $this->getValidUsers($pool);
            $merchants = $this->getValidMerchants($pool);

            // 金额的60%为发放金额
            $grand_amount = round($pool['grand_amount'] * 0.6, 2);
            // 金额的40%为剩余金额
            $available_amount = round($pool['grand_amount'] * 0.4, 2);
            // 计算分红金额
            $userBonus = $grand_amount * $this->userRatio;
            $merchantBonus = $grand_amount * $this->merchantRatio;

            // 执行分红
            $userBonusAmounts = $this->distributeUserBonus($users, $userBonus);
            $merchantBonusAmounts = $this->distributeMerchantBonus($merchants, $merchantBonus);

            // 获取当前基础金额
            $currentBaseAmount = $this->getCurrentBaseAmount($pool['id']);

            // 记录分红日志
            $poolId = $this->recordDividendPeriod($currentBaseAmount, $pool, $grand_amount, $grand_amount,1);
            $this->recordDividendLog($poolId, $users, $merchants, $userBonusAmounts, $merchantBonusAmounts);

            // 更新分红池时需要保存最后一次的总金额作为新的基准
            $lastTotalAmount = Db::name('dividend_period_log')
                ->where('dp_id', $pool['id'])
                ->order('id', 'desc')
                ->value('total_amount') ?? $this->initialThreshold;

            // 更新分红池
            Db::name('dividend_pool')
                ->where('id', $pool['id'])
                ->update([
                    'available_amount' => $available_amount,
                    'grand_amount' => 0,
                    'initial_threshold' => $lastTotalAmount, // 新增字段，记录当前周期的初始阈值
                    'distributed_amount' => Db::raw('distributed_amount + ' . $currentBaseAmount),
                    'update_time' => date('Y-m-d H:i:s')
            ]);

            return [
                'base_amount' => $currentBaseAmount,
            ];


            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}

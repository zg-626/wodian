<?php

namespace app\common\repositories\user;

use app\common\model\store\order\StoreOrder;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\BaseRepository;
use think\facade\Db;


class BonusService extends BaseRepository
{
    // 环比增长率
    protected $growthRate = 1.15;
    // 抵扣金计算比例（第一期）
    protected $couponRatio = 0.005;
    // 第一期分红的积分阈值
    protected $initialThreshold = 200;
    // 计算分红
    public function calculateBonus()
    {
        // 获取所有未分红的订单积分总和
        $totalPoints = StoreOrder::where(['status' => 3, 'is_bonus' => 0])->sum('give_integral');
        // 获取上一期的抵用券记录
        $lastCouponRecord = Db::name('coupon_pool')->order('id', 'desc')->find();
        // 检查是否是第一期分红
        $lastBonusRecord = Db::name('bonus_pool')->order('id', 'desc')->find();
        if (!$lastBonusRecord) {
            // 第一期分红：检查是否达到阈值
            if ($totalPoints < $this->initialThreshold) {
                return false; // 未达到阈值，不进行分红
            }
            $couponAmount = 1; // 当期抵扣金初始值
        } else {
            // 后续分红：检查是否达到15%增长
            if ($totalPoints / $lastBonusRecord['total_points'] < 1.15) {
                return false; // 未达到15%增长，不进行分红
            }
            $couponAmount = $lastCouponRecord['next_coupon_amount']; // 当期抵扣金为上一期的下期抵扣金
        }
        // 计算下期抵扣金
        $nextCouponAmount = $couponAmount * $this->growthRate;
        // 获取参与用户
        $users = User::where('status', 1)
            ->where('integral', '<>', 0)
            ->field('uid, integral')
            ->select();
        // 获取参与商家
        $merchants = Merchant::where('is_del', 0)
            ->where('integral', '<>', 0)
            ->field('mer_id, integral')
            ->select();
        // 更新用户抵扣金
        foreach ($users as $user) {
            // 第一期抵扣金：用户积分 × 0.005
            if (!$lastCouponRecord) {
                $userCouponAmount = $user->integral * $this->couponRatio;
            }
            // 后续抵扣金：按用户积分占用户总积分的比例分配
            else {
                $totalUserPoints = array_sum(array_column($users->toArray(), 'integral'));
                $userCouponAmount = ($user->integral / $totalUserPoints) * $couponAmount;
            }
            Db::name('user_coupon')->insert([
                'uid'           => $user->uid,
                'coupon_amount' => $userCouponAmount,
                'create_time'    => date('Y-m-d H:i:s'),
            ]);
        }
        // 更新商家抵扣金
        foreach ($merchants as $merchant) {
            // 第一期抵扣金：商家积分 × 0.005
            if (!$lastCouponRecord) {
                $merchantCouponAmount = $merchant->integral * $this->couponRatio;
            }
            // 后续抵扣金：按商家积分占总积分的比例分配
            else {
                $totalMerchantPoints = array_sum(array_column($merchants->toArray(), 'integral'));
                $merchantCouponAmount = ($merchant->integral / $totalMerchantPoints) * $couponAmount;
            }
            Db::name('merchant_coupon')->insert([
                'mer_id'        => $merchant->mer_id,
                'coupon_amount' => $merchantCouponAmount,
                'create_time'    => date('Y-m-d H:i:s'),
            ]);
        }
        // 标记已分红的订单
        StoreOrder::where(['status' => 3, 'is_bonus' => 0])->update(['is_bonus' => 1]);
        // 记录本次分红池
        Db::name('bonus_pool')->insert([
            'total_points' => $totalPoints,
            'create_time'   => date('Y-m-d H:i:s'),
        ]);
        // 记录本期抵用券金额和分红信息
        Db::name('coupon_pool')->insert([
            'period'              => $lastCouponRecord ? $lastCouponRecord['period'] + 1 : 1,
            'order_points'        => $totalPoints,
            'coupon_amount'       => $couponAmount,
            'growth_rate'         => $this->growthRate,
            'next_coupon_amount'  => $nextCouponAmount,
            'create_time'          => date('Y-m-d H:i:s'),
        ]);
        return true;
    }


}

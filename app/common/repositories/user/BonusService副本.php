<?php

namespace app\common\repositories\user;

use app\common\model\store\order\StoreOrder;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\BaseRepository;
use think\facade\Db;


class BonusService副本 extends BaseRepository
{
    // 第一期分红的积分阈值
    protected $initialThreshold = 200;

    // 计算分红
    public function calculateBonus()
    {
        // 获取所有订单积分总和
        $totalPoints = StoreOrder::where(['status' => 3,'is_bonus' => 0])->sum('give_integral');
        /*echo 'totalPoints: ' . $totalPoints . PHP_EOL;
        exit();*/
        // 检查是否是第一期分红
        $lastBonusRecord = Db::name('bonus_pool')->order('id', 'desc')->find();
        if (!$lastBonusRecord) {
            // 第一期分红：检查是否达到阈值
            if ($totalPoints < $this->initialThreshold) {
                return false; // 未达到阈值，不进行分红
            }
        } else {
            // 后续分红：检查是否达到15%增长
            if ($totalPoints / $lastBonusRecord['total_points'] < 1.15) {
                return false; // 未达到15%增长，不进行分红
            }
        }

        // 获取所有用户和商家的积分比例（integral 不等于 0）
        $users = User::where('status', 1)
            ->where('integral', '<>', 0)
            ->field('uid, integral')
            ->select();

        $merchants = Merchant::where('is_del', 0)
            ->where('integral', '<>', 0)
            ->field('mer_id, integral')
            ->select();

        // 总积分
        $totalUserPoints = array_sum(array_column($users->toArray(), 'integral'));
        $totalMerchantPoints = array_sum(array_column($merchants->toArray(), 'integral'));
        // 分红分配
        $userBonus = $totalPoints * 0.5; // 用户分50%
        $merchantBonus = $totalPoints * 0.5; // 商家分50%
        // 更新用户积分
        foreach ($users as $user) {
            $user->integral += ($user->integral / $totalUserPoints) * $userBonus;
            $user->save();
        }
        // 更新商家积分
        foreach ($merchants as $merchant) {
            $merchant->integral += ($merchant->integral / $totalMerchantPoints) * $merchantBonus;
            $merchant->save();
        }
        // 标记已分红的订单
        StoreOrder::where('is_bonus', 0)->update(['is_bonus' => 1]);

        // 记录本次分红池
        Db::name('bonus_pool')->insert([
            'total_points' => $totalPoints,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

}

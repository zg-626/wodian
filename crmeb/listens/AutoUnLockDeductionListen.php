<?php

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


namespace crmeb\listens;


use app\common\repositories\user\UserBillRepository;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use Swoole\Timer;
use think\facade\Db;

class AutoUnLockDeductionListen extends TimerService implements ListenerInterface
{

    public function handle($event): void
    {
        //TODO 自动解冻抵用卷
        $this->tick(30000, function () {
            $userBill = app()->make(UserBillRepository::class);
            request()->clearCache();
            $timer = ((int)systemConfig('lock_brokerage_timer'));
            $time = date('Y-m-d H:i:s', $timer ? strtotime("- $timer day") : time());
            $bills = $userBill->getTimeoutDeductionBill($time);
            Db::transaction(function () use ($userBill, $bills) {
                foreach ($bills as $bill) {
                    if ($bill->number > 0 && $bill->user) {
                        $brokerage = bcsub($bill->number, $userBill->refundBrokerage($bill->link_id, $bill->uid), 2);
                        if ($brokerage > 0) {
                            $bill->user->coupon_amount = bcadd($bill->user->coupon_amount, $brokerage, 2);
                            $bill->user->save();
                        }
                    }
                    $bill->status = 1;
                    $bill->balance = $bill->user ? $bill->user->coupon_amount : 0;
                    $bill->save();
                }
            });
        });
    }
}

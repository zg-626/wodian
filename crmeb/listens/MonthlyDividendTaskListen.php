<?php

namespace crmeb\listens;

use app\common\repositories\user\BonusOfflineService;
use app\common\repositories\user\UserRepository;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use think\facade\Log;

class MonthlyDividendTaskListen extends TimerService implements ListenerInterface
{
    public function handle($event): void
    {
        // 每月1号00:01执行
        $this->tick(1000 * 60 * 60 * 24, function () {
            if (date('d') === '01' && date('H') === '00' && date('i') === '01') {
                /** @var UserRepository $userRepository */
                $userRepository = app()->make(UserRepository::class);
                try {
                    $userRepository->getMerchantInfo();
                } catch (\Exception $e) {
                    Log::info('月初检测商务流水失败：' . $e->getMessage());
                }
            }
        });
    }
}
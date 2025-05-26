<?php

namespace crmeb\listens;

use app\common\repositories\user\BonusOfflineService;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use think\facade\Log;

class MonthlyDividendTaskListen extends TimerService implements ListenerInterface
{
    public function handle($event): void
    {
        // 每月1号00:00执行
        $this->tick(1000 * 60 * 60 * 24, function () {
            if (date('d') === '01' && date('H') === '00' && date('i') === '01') {
                /** @var BonusOfflineService $bonusOfflineService */
                $bonusOfflineService = app()->make(BonusOfflineService::class);
                try {
                    $bonusOfflineService->distributeBaseAmount();
                } catch (\Exception $e) {
                    Log::info('月初基础金额分红失败：' . $e->getMessage());
                }
            }
        });
    }
}
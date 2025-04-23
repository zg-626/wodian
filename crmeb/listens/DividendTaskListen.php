<?php

namespace crmeb\listens;

use app\common\repositories\user\BonusOfflineService;
use app\common\repositories\user\DividendPoolService;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use Swoole\Timer;
use think\facade\Log;

class DividendTaskListen  extends TimerService implements ListenerInterface
{
    public function handle($event): void
    {
        /** @var DividendPoolService $dividendPoolService **/
        // $dividendPoolService = app()->make(DividendPoolService::class);
        // $dividendPoolService->calculateAndDistributeDividend();
        // 使用新的奖池方式
        $this->tick(1000 * 60 * 70, function () {
            /** @BonusOfflineService $bonusOfflineService */
            $bonusOfflineService = app()->make(BonusOfflineService::class);
            try {
                $bonusOfflineService->calculateBonus();
            } catch (\Exception $e) {
                Log::info('自动检测奖池分红失败' . date('Y-m-d H:i:s', time()));
            }
        });
    }
}

<?php

namespace crmeb\listens;

use app\common\repositories\user\BonusOfflineService;
use app\common\repositories\user\DividendPoolService;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use Swoole\Timer;
use think\facade\Log;

class DividendTaskListen extends TimerService implements ListenerInterface
{
    public function handle($event): void
    {
        // 每5天检查一次分红
        $this->tick(1000 * 60 * 60 * 24 * 5, function () {
            /** @var BonusOfflineService $bonusOfflineService **/
            $bonusOfflineService = app()->make(BonusOfflineService::class);
            try {
                $info = $bonusOfflineService->calculateBonus();
                record_log('时间: ' . date('Y-m-d H:i:s') . ', 系统分红: ' . json_encode($info, JSON_UNESCAPED_UNICODE), 'red');
            } catch (\Exception $e) {
                Log::info('自动检测奖池分红失败' . date('Y-m-d H:i:s', time()));
            }
        });
    }
}

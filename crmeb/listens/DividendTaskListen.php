<?php

namespace crmeb\listens;

use app\common\repositories\user\DividendPoolService;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;

class DividendTaskListen  implements ListenerInterface
{
    public function handle($event): void
    {
        /** @var DividendPoolService $dividendPoolService **/
        $dividendPoolService = app()->make(DividendPoolService::class);
        $dividendPoolService->calculateAndDistributeDividend();
    }
}

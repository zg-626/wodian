<?php

namespace crmeb\listens;

use app\common\repositories\store\order\StoreOrderOfflineRepository;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\TimerService;
use think\facade\Log;

class AutoCancelOfflineOrderListen extends TimerService implements ListenerInterface
{

    public function handle($event): void
    {
        $this->tick(60000, function () {
            /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            request()->clearCache();
            $timer = ((int)systemConfig('auto_close_order_timer')) ?: 15;
            $time = date('Y-m-d H:i:s', strtotime("- $timer minutes"));
            $groupOrderIds = $storeOrderOfflineRepository->getTimeOutIds($time);
            foreach ($groupOrderIds as $id) {
                try {
                    $storeOrderOfflineRepository->cancel($id);
                } catch (\Exception $e) {
                    Log::info('自动关闭线下支付订单失败' . $e->getMessage() . $e->getLine() . var_export($id, 1));
                }
            }
        });
    }
}
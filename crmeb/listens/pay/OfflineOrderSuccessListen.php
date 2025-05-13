<?php

namespace crmeb\listens\pay;

use app\common\repositories\store\order\StoreGroupOrderRepository;
use app\common\repositories\store\order\StoreOrderOfflineRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use think\facade\Log;

class OfflineOrderSuccessListen
{
    public function handle($data): void
    {
        Log::info('微信服务商支付成功回调执行队列' . var_export([$data], 1));
        /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
        $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
        $storeOrderOfflineRepository->paySuccess($data);
    }

}
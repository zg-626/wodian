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
        $orderSn = $data['order_sn'];
        $is_combine = $data['is_combine'] ?? 0;
        //$groupOrder = app()->make(StoreGroupOrderRepository::class)->getWhere(['group_order_sn' => $orderSn]);
        //if (!$groupOrder || $groupOrder->paid == 1) return;
        /*$orders = [];
        if ($is_combine) {
            foreach ($data['data']['sub_orders'] as $order) {
                $orders[$order['out_trade_no']] = $order;
            }
        }*/
        Log::info('微信服务商支付成功回调执行队列' . var_export([$data], 1));
        /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
        $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
        $storeOrderOfflineRepository->paySuccess($data);
    }

}
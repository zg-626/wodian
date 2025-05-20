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
        Log::info('拉卡拉支付成功回调执行队列' . var_export([$data], 1));

        // 从这里判断走线下支付流程，还是美团订单
        if ($data['remark'] == 'offline_order') {
            /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            $storeOrderOfflineRepository->paySuccess($data);
        }else{
            $orderSn = $data['out_trade_no'];
            $is_combine = 0;
            $groupOrder = app()->make(StoreGroupOrderRepository::class)->getWhere(['group_order_sn' => $orderSn]);
            if (!$groupOrder || $groupOrder->paid == 1) return;
            $orders = [];

            Log::info('美团执行队列' . var_export([$data,$groupOrder], 1));
            /** @var StoreOrderRepository $toreOrderRepository */
            $toreOrderRepository=app()->make(StoreOrderRepository::class);
            $toreOrderRepository->paySuccess($groupOrder, $is_combine, $orders, $data);
        }
    }

}
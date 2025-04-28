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


use app\common\repositories\store\order\StoreOrderOfflineRepository;
use app\common\repositories\store\order\StoreOrderProfitsharingRepository;
use crmeb\interfaces\ListenerInterface;
use crmeb\jobs\OrderOfflineProfitsharingJob;
use crmeb\services\TimerService;
use think\facade\Log;
use think\facade\Queue;

class AutoOrderLakalaListen extends TimerService implements ListenerInterface
{

    public function handle($event): void
    {
        $this->tick(60000, function () {
            request()->clearCache();
            /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
            $storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
            $models =$storeOrderProfitsharingRepository ->getAutoLakalasharing();
            /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            try {
                foreach ($models as $model) {
                    Log::info('拉卡拉自动分账' . var_export($model->order_id, 1));
                    $order = $storeOrderOfflineRepository->getWhere(['order_id' => $model['order_id']]);
                    $date = substr($order['lkl_log_date'], 0, 8);
                    $params = [
                        'lkl_mer_cup_no' => $order['lkl_mer_cup_no'],
                        'lkl_log_no' => $order['lkl_log_no'], // 用最新的流水号
                        'lkl_log_date' => $date,
                    ];
                    $storeOrderOfflineRepository->lklQueryAmt($params, $order);

                }
            } catch (\Exception $e) {
                Log::info('拉卡拉自动分账失败' . $e->getMessage() . $e->getFile() . $e->getLine());
            }
        });
    }
}

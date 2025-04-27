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

class AutoOrderOfflineShippingListen extends TimerService implements ListenerInterface
{

    public function handle($event): void
    {
        $this->tick(30000, function () {
            request()->clearCache();
            /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            $models =$storeOrderOfflineRepository ->getAutoOfflineShipping();
            foreach ($models as $model) {
                Log::info('线下自动发货' . var_export($model->order_id, 1));
                $storeOrderOfflineRepository->virtualDelivery($model);
                // 更新为已发货
                $model->is_share = 1;
                $model->save();
            }
        });
    }
}

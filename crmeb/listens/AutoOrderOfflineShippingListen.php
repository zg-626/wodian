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
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;

class AutoOrderOfflineShippingListen extends TimerService implements ListenerInterface
{

    public function handle($event): void
    {
        $this->tick(60000*10, function () {
            request()->clearCache();
            /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            $models = $storeOrderOfflineRepository->getAutoOfflineShipping();

            $successCount = 0;
            $failCount = 0;
            $exceptionOrders = [];

            foreach ($models as $model) {
                try {
                    Log::info('开始处理线下自动发货订单'.$model->order_id);

                    // 添加前置检查
                    if ($model->is_share == 1) {
                        Log::warning('订单已发货，跳过处理'.$model->order_id);
                        continue;
                    }

                    $storeOrderOfflineRepository->virtualDelivery($model);

                    // 使用事务确保数据一致性
                    DB::transaction(function () use ($model) {
                        $model->is_share = 1;
                        $model->save();
                    });

                    $successCount++;
                    Log::info('订单发货成功'.$model->order_id);

                } catch (\Throwable $e) {
                    $failCount++;
                    $exceptionOrders[$model->order_id] = $e->getMessage();

                    // 分级记录日志
                    if ($e instanceof \PDOException) {
                        Log::error('数据库操作异常', [
                            'order_id' => $model->order_id,
                            'error' => $e->getMessage()
                        ]);
                    } else {
                        Log::warning('业务处理异常', [
                            'order_id' => $model->order_id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // 可在此添加通知逻辑（如邮件/企微通知）
                    // $this->sendAlert($model, $e);
                }
            }
            $data=[
                'total' => count($models),
                'success' => $successCount,
                'failed' => $failCount,
                'failed_orders' => $exceptionOrders
            ];
            // 最终统计日志
            Log::info('自动发货任务执行完成'.json_encode($data));
        });
    }

}

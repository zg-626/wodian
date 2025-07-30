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


namespace app\controller\admin\offline;

use app\common\repositories\store\order\StoreOrderOfflineRepository;
use crmeb\basic\BaseController;
use crmeb\services\ExcelService;
use think\App;

/**
 * 线下订单
 **/
class Order extends BaseController
{
    protected $repository;

    public function __construct(App $app, StoreOrderOfflineRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * 列表
     * @return mixed
     */
    public function getAllList()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'mer_id', 'pay_type','status', 'keywords', 'order_sn', 'is_trader','group_order_sn']);
        $where['order_sn']=$where['group_order_sn'];
        unset($where['group_order_sn']);
        $data = $this->repository->adminGetList($where, $page, $limit);
        return app('json')->success($data);
    }

    /**
     * 金额统计
     * @return mixed
     */
    public function title()
    {
        $where = $this->request->params(['date', 'mer_id', 'pay_type','status', 'keywords', 'order_sn', 'is_trader']);
        return app('json')->success($this->repository->getStat($where, $where['status']));
    }

    /**
     * 头部统计
     * @return mixed
     */
    public function chart()
    {
        return app('json')->success($this->repository->OrderTitleNumber(null, null));
    }

    /**
     * 详情
     * @return mixed
     **/
    public function detail($id)
    {
        $data = $this->repository->getOne($id, null);
        if (!$data){
            return app('json')->fail('数据不存在');
        }
        return app('json')->success($data);
    }

    /**
     * 导出
     * @return mixed
     **/
    public function export()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'mer_id', 'pay_type','status', 'keywords', 'order_sn', 'is_trader']);
        /** @var ExcelService $service */
        $service = app()->make(ExcelService::class);
        $data = $service->offlineOrder($where, $page, $limit);
        return app('json')->success($data);
    }

}

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

use crmeb\basic\BaseController;
use app\common\repositories\store\ExcelRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\store\order\StoreOrderRepository as repository1;
use app\common\repositories\store\order\StoreOrderOfflineRepository as repository;
use crmeb\services\ExcelService;
use think\App;

class Order extends BaseController
{
    protected $repository;
    protected $isSpread;

    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * TODO
     * @return mixed
     * @author Qinii
     * @day 2020-06-25
     */
    public function getAllList()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['type', 'date', 'mer_id', 'keywords', 'status', 'username', 'order_sn', 'is_trader', 'activity_type', 'group_order_sn', 'store_name', 'spread_name', 'top_spread_name', 'filter_delivery', 'filter_product']);
        $pay_type = $this->request->param('pay_type', '');
        if ($pay_type != '') $where['pay_type'] = $this->repository::PAY_TYPE_FILTEER[$pay_type];
        $where['is_spread'] = $this->request->param('is_spread', 0);
        $data = $this->repository->adminGetList($where, $page, $limit);
        return app('json')->success($data);
    }

}

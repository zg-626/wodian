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
 * 线下平台账单
 **/
class FinancialRecord extends BaseController
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
    public function getList()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date',['type', 1]]);
        $data = $this->repository->getFinancialRecordList($where, $page, $limit);
        return app('json')->success('线下平台账单',$data);
    }

    /**
     * 金额统计
     * @return mixed
     */
    public function title()
    {
        $where = $this->request->params(['date',['type', 1]]);
        return app('json')->success($this->repository->getFinancialRecordTitle($where));
    }

}

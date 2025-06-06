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


namespace app\controller\admin\system\dividend;

use app\common\repositories\system\dividend\DividendExecuteLogRepository;
use crmeb\basic\BaseController;
use crmeb\services\ExcelService;
use think\App;

class DividendExecuteLog extends BaseController
{
    protected $repository;

    public function __construct(App $app, DividendExecuteLogRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * 列表
     * @return mixed
     */
    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'city','execute_type', 'status']);
        $data = $this->repository->getList($where, $page, $limit);
        return app('json')->success($data);
    }

    /**
     * 导出
     * @return mixed
     **/
    public function export()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'city','execute_type', 'status']);
        /** @var ExcelService $service */
        $service = app()->make(ExcelService::class);
        $data = $service->dividendExecuteLog($where, $page, $limit);
        return app('json')->success($data);
    }

}

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


namespace app\controller\admin\user;


use app\common\repositories\store\ExcelRepository;
use crmeb\basic\BaseController;
use app\common\repositories\user\UserBillRepository;
use crmeb\services\ExcelService;
use think\App;

class UserBill extends BaseController
{
    protected $repository;

    public function __construct(App $app, UserBillRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * 用户余额相关记录
     * @return \think\response\Json
     * @author Qinii
     * @day 2023/10/16
     */
    public function getList()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword', 'date', 'type']);
        $where['category'] = [$this->repository::CATEGORY_NOW_MONEY,$this->repository::CATEGORY_SVIP_PAY];
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    /**
     * 用户等级成长值记录
     * @return \think\response\Json
     * @author Qinii
     * @day 2023/10/16
     */
    public function getMembers()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword', 'date', 'type', ['category', $this->repository::CATEGORY_SYS_MEMBERS], 'uid']);
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    public function type()
    {
        $category = $this->request->param('category', $this->repository::CATEGORY_NOW_MONEY);
        return app('json')->success($this->repository->type($category));
    }

    public function export()
    {
        $where = $this->request->params(['keyword', 'date', 'type']);
        [$page, $limit] = $this->getPage();
        $data = app()->make(ExcelService::class)->bill($where, $page, $limit);
        return app('json')->success($data);
    }
}

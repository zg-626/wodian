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

namespace app\controller\api\article;

use app\common\dao\system\merchant\MerchantDao;
use app\common\repositories\store\CityAreaRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserGroupRepository;
use app\common\repositories\user\UserRepository;
use app\common\repositories\user\UserVisitRepository;
use crmeb\services\SwooleTaskService;
use think\App;
use app\common\repositories\article\ArticleRepository as repository;
use crmeb\basic\BaseController;

class Article extends BaseController
{
    /**
     * @var repository
     */
    protected $repository;

    /**
     * StoreBrand constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * @return mixed
     * @author Qinii
     */
    public function lst($cid)
    {
        [$page, $limit] = $this->getPage();
        $where = ['status' => 1,'cid' => $cid];
        return app('json')->success($this->repository->search(0,$where, $page, $limit));
    }

    public function detail($id)
    {
        if (!$this->repository->merApiExists($id))
            return app('json')->fail('文章不存在');
        $data = $this->repository->getWith($id,['content']);
        if ($this->request->isLogin()) {
            $uid = $this->request->uid();
            $make = app()->make(UserVisitRepository::class);
            $count = $make->search(['uid' => $uid, 'type' => 'article'])->where('type_id', $id)->whereTime('create_time', '>', date('Y-m-d H:i:s', strtotime('- 300 seconds')))->count();
            if (!$count) {
                SwooleTaskService::visit(intval($uid), $id, 'article');
                $this->repository->incVisit($id);
            }
        }

        return app('json')->success($data);
    }

    public function list()
    {
        $where = ['status' => 1];
        return app('json')->success($this->repository->search(0,$where, 1, 9));
    }

    // 测试接口
    public function test()
    {
        /** @var CityAreaRepository $cityArea */
        $cityArea= app()->make(CityAreaRepository::class);
        print_r($cityArea->getAddressChildList());
        /*$MerchantDao = app()->make(MerchantDao::class); // MerchantDao
        $merchant = $MerchantDao->search(['mer_id' => 78])->field('mer_id,integral,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type')->find();

        // 如果没有业务员，则没有佣金
        if ($merchant->salesman_id===0) return;
        // 查询业务员信息
        $salesman = app()->make(UserRepository::class)->get($merchant->salesman_id);
        // 根据业务员分组查询佣金比例
        $commission = app()->make(UserGroupRepository::class)->get($salesman['group_id']);
        $commission = $commission->extension;
        echo "<pre>";
        print_r($commission);*/
        return app('json')->success('测试成功');
    }
}

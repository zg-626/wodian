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


namespace app\controller\pc\store;


use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\UserRelationRepository;
use crmeb\basic\BaseController;
use think\App;

class Merchant extends BaseController
{
    protected $repository;

    public function __construct(App $app, MerchantRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->userInfo = $this->request->isLogin() ? $this->request->userInfo() : null;
    }


    /**
     * TODO 是否关注 商品/商户
     * @return \think\response\Json
     * @author Qinii
     * @day 4/27/21
     */
    public function care()
    {
        $id = $this->request->param('id');
        $type = $this->request->param('type');
        if(empty($id) || empty($type)) return app('json')->fail('缺少参数');
        $care = false;
        if($this->userInfo)
            $care = app()->make(UserRelationRepository::class)->getUserRelation(['type' => $type,'type_id' => $id],$this->userInfo->uid);
        return app('json')->success(['care' => $care]);
    }
}

<?php

namespace app\controller\api\meituan;

use crmeb\basic\BaseController;
use app\common\repositories\WaimaiRepositories;
use think\App;

class Waimai extends BaseController
{
    protected $repository;
    protected $user;
    public function __construct(App $app, WaimaiRepositories $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->user = $this->request->userInfo();
    }
    //美团外卖入口
    public function mtWaimai()
    {
        $params = $this->request->params([
            'address',
            ['product_type', 'mt_waimai'],
        ]);
        $user = $this->user;
        if (!$user->phone) return app('json')->fail('请绑定手机号');
        $params['mobile'] = $user->phone;
        //$params['mobile'] = 13051579900;
        $info = $this->repository->mt_waimai($params);
        return app('json')->success($info);
    }

    // 订单详情查询接口
    public function orderDetail()
    {
        $params = $this->request->params([
            'sqtBizOrderId'
        ]);
        /*$user = $this->user;
        if (!$user->phone) return app('json')->fail('请绑定手机号');
        $params['mobile'] = $user->phone;*/
        //$params['mobile'] = 13051579900;
        $info = $this->repository->orderDetail($params);
        return app('json')->success($info);
    }

}
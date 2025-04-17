<?php

namespace app\controller\api\lakala;

use crmeb\basic\BaseController;
use think\response\Json;

class Lakala extends BaseController
{
    // 支付回调
    public function notify()
    {
        return app('json')->success('请求成功');
    }

}
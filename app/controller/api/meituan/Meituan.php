<?php

namespace app\controller\api\meituan;

use crmeb\basic\BaseController;
use think\response\Json;

class Meituan extends BaseController
{
    // 交易标准三方收银台支付回调接口
    public function callback()
    {
        $info=[
            'status'=>0,
            'msg'=>'成功',
            'data'=>'123456']
        ;
        return  json($info);
    }

    //交易标准三方收银台关单外部接口
    public function close()
    {
        $info=[
            'status'=>0,
            'msg'=>'成功',
            'data'=>'123456']
        ;
        return  json($info);
    }

    // 交易标准三方收银台退款外部接口
    public function refund()
    {
        $info=[
            'status'=>0,
            'msg'=>'成功',
            'data'=>'123456']
        ;
        return  json($info);
    }

    // 交易标准三方收银台支付查询外部接口
    public function query()
    {
        $info=[
            'status'=>0,
            'msg'=>'成功',
            'data'=>'123456']
        ;
        return  json($info);
    }

    // 交易标准三方收银台下单外部接口
    public function pay()
    {
        $info=[
            'status'=>0,
            'msg'=>'成功',
            'data'=>'123456']
        ;
        return  json($info);
    }

}
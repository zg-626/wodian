<?php

namespace app\controller\api\meituan;

use app\common\repositories\WaimaiRepositories;
use crmeb\basic\BaseController;
use think\response\Json;

class Meituan extends BaseController
{
    // 交易标准三方收银台支付回调接口
    public function callback(WaimaiRepositories $repository)
    {
        $params = $this->request->params([
            'tradeNo',
            'thirdTradeNo',
            'serviceFeeAmount',
            'tradeAmount'
        ]);
        $result = $repository->payCallback($params);
        // 如果结果已经是JSON字符串，直接返回
        if (is_string($result) && is_array(json_decode($result, true)) && json_last_error() == JSON_ERROR_NONE) {
            return response($result)->contentType('application/json');
        }

        // 否则进行JSON编码
        return json($result);
    }

    // 交易标准三方收银台支付查询外部接口
    public function query(WaimaiRepositories $repository)
    {
        $params = $this->request->params([
            'accessKey',
            'content',
        ]);
        $result = $repository->query($params);
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 美团返回数据: ' . json_encode($result,JSON_UNESCAPED_UNICODE), 'meituan_order_create');
        // 如果结果已经是JSON字符串，直接返回
        if (is_string($result) && is_array(json_decode($result, true)) && json_last_error() == JSON_ERROR_NONE) {
            return response($result)->contentType('application/json');
        }

        // 否则进行JSON编码
        return json($result);
    }

    // 交易标准三方收银台下单外部接口
    /**
     * 下单接口
     ***/
    public function pay(WaimaiRepositories $repository)
    {
        $params = $this->request->params([
            'accessKey',
            'content',
        ]);
        $result = $repository->create($params);
        // 如果结果已经是JSON字符串，直接返回
        if (is_string($result) && is_array(json_decode($result, true)) && json_last_error() == JSON_ERROR_NONE) {
            return response($result)->contentType('application/json');
        }

        // 否则进行JSON编码
        return json($result);
    }

    //交易标准三方收银台关单外部接口
    /**
     * 关单接口
     **/
    public function close(WaimaiRepositories $repository)
    {
        $params = $this->request->params([
            'accessKey',
            'content',
        ]);
        $result = $repository->close($params);
        // 如果结果已经是JSON字符串，直接返回
        if (is_string($result) && is_array(json_decode($result, true)) && json_last_error() == JSON_ERROR_NONE) {
            return response($result)->contentType('application/json');
        }

        // 否则进行JSON编码
        return json($result);
    }

    // 交易标准三方收银台退款外部接口
    /**
     * 退款接口
     **/
    public function refund(WaimaiRepositories $repository)
    {
        $params = $this->request->params([
            'accessKey',
            'content',
        ]);
        $result = $repository->refund($params);
        // 如果结果已经是JSON字符串，直接返回
        if (is_string($result) && is_array(json_decode($result, true)) && json_last_error() == JSON_ERROR_NONE) {
            return response($result)->contentType('application/json');
        }

        // 否则进行JSON编码
        return json($result);
    }

}
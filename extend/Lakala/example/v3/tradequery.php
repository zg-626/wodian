<?php
/**
 * 查询交易
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();

$api = new \Lakala\OpenAPISDK\V3\Api\QueryTradequeryApi($config);

$request = new \Lakala\OpenAPISDK\V3\Model\QueryTradequeryRequest();
// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');

// 必填参数（二选一） out_trade_no、trade_no参数二选一
$request->setOutTradeNo('23424234234');
$request->setTradeNo('');


try {
    $response = $api->queryTradequery($request);
    if ($response->getRespData()) {
        print_r($response->getRespData());
    }
    else {
        print_r($response);
    }
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

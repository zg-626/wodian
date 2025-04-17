<?php
/**
 * 退款交易
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

$api = new \Lakala\OpenAPISDK\V3\Api\RelationRefundApi($config);

$request = new \Lakala\OpenAPISDK\V3\Model\RelationRefundRequest();
// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');
$request->setOutTradeNo('23423492423');
$request->setRefundAmount('10000');
$request->setLocationInfo($tradeLocationInfo);

// 非必填参数
$request->setRefundReason('');
$request->setOriginOutTradeNo('');
$request->setOriginTradeNo('');
$request->setOriginLogNo('');

try {
    $response = $api->relationRefund($request);
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

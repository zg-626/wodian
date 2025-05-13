<?php
/**
 * 聚合收银台 -> 收银台订单查询
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);

$request = new \Lakala\OpenAPISDK\V3\Model\ModelRequest();
// 请求字段
$request->setReqData([
    'merchant_no' => '822290070111135',
]);

try {
    $response = $api->tradeApi('/api/v3/ccss/counter/order/close', $request);
    print_r($response->getRespData());
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

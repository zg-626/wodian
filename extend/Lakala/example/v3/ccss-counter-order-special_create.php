<?php
/**
 * 聚合收银台 -> 收银台订单创建
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);

$request = new \Lakala\OpenAPISDK\V3\Model\ModelRequest();
// 请求字段
$request->setReqData([
    'out_order_no' => date('YmdHis', time()),
    'merchant_no' => '822290070111135',
    'total_amount' => 2000,
    'order_info	' => '公交卡购票',
    'order_efficient_time' => date('YmdHis', time() + 30*60), // 订单有效期 - 半小时内有效
]);

try {
    $response = $api->tradeApi('/api/v3/ccss/counter/order/special_create', $request);
    print_r($response->getRespData());
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

<?php
/**
 * 通用接口调用 -> 查询交易
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V2\V2Configuration();
$api = new \Lakala\OpenAPISDK\V2\Api\V2LakalaApi($config);

try {
    // 字符串格式的body  
    $body = '{"timestamp":"1728531818230","rnd":null,"ver":"1.0.0","reqId":null,"reqData":{"mercId":"822290070111135","termNo":"29034705","ornOrderId":"LKLB2C202206161830570630001BUF"},"locationInfo":null,"termExtInfo":null}';

    $response = $api->apiWithBody('/api/v2/labs/txn/query', $body);

    print_r($response->getRespData());
    echo $response->getRetCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}


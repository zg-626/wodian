<?php
/**
 * 通用接口调用 -> 查询交易
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);

try {
    // json格式body
    $json = new stdClass;
    $json->req_time = "20240929033128";
    $json->version = "3.0";
    $json->req_data = new stdClass;
    $json->req_data->merchant_no = "822290070111135";
    $json->req_data->term_no = "29034705";
    $json->req_data->out_trade_no = "20240929033128";
    $json->req_data->trade_no = "";
    $response = $api->apiWithBody('/api/v3/labs/query/tradequery', $json);

    print_r($response->getRespData());
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}


<?php
/**
 * 通用接口调用 -> 查询交易
 */

require_once '../../vendor/autoload.php';

use Lakala\OpenAPISDK\v3\Configuration;

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config, \Lakala\OpenAPISDK\V3\Api\EncryptMode::RESPONSE);

try {
    // 字符串格式的body  
    $body = '{"timestamp": 1655452194480,"ver": "1.0.0","reqId": "bf167a53f4a34716b66e26a9a5d803a6","reqData": {"bmcpNo": "1","mercId": "8221000581200BS"}}';

    // json格式body
    $json = new stdClass;
    $json->timestamp = "1655452194480";
    $json->ver = "1.0.0";
    $json->reqId = "bf167a53f4a34716b66e26a9a5d803a6";
    $json->reqData = new stdClass;
    $json->reqData->bmcpNo = "1";
    $json->reqData->mercId = "8221000581200BS";

    $response = $api->apiWithBody('/api/v2/laep/industry/bankcard/list', $json);

    print_r($response->getRespData());
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}


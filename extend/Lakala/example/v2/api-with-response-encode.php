<?php
/**
 * 通用接口调用 -> 查询交易
 */

require_once '../../vendor/autoload.php';


$config = new \Lakala\OpenAPISDK\V2\V2Configuration();
$api = new \Lakala\OpenAPISDK\V2\Api\V2LakalaApi($config, \Lakala\OpenAPISDK\V2\Api\V2EncryptMode::RESPONSE);

try {
    $request = new \Lakala\OpenAPISDK\V2\Model\V2ModelRequest();
    // 请求字段
    $request->setReqData([
        'bmcpNo' => '1',
        'mercId' => '8221000581200BS'
    ]);

    $response = $api->tradeApi('/api/v2/laep/industry/bankcard/list', $request);

    print_r($response->getRespData());
    echo $response->getRetCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
    echo $e->getMessage();
}


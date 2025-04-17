<?php
/**
 * 通用接口调用 -> 查询交易
 */

require_once '../../vendor/autoload.php';


$config = new \Lakala\OpenAPISDK\V2\V2Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V2\Model\V2TradeLocationInfo('106.37.232.115');
$termExtInfo = new \Lakala\OpenAPISDK\V2\Model\V2TermExtInfo();

$api = new \Lakala\OpenAPISDK\V2\Api\V2LakalaApi($config);

try {
    $request = new \Lakala\OpenAPISDK\V2\Model\V2ModelRequest();
    $request->setLocationInfo($tradeLocationInfo);
    $request->setTermExtInfo($termExtInfo);
    // 请求字段
    $request->setReqData([
        'mercId' => '822290070111135',
        'termNo' => '29034705',
        'ornOrderId' => 'LKLB2C202206161830570630001BUF'
    ]);

    $response = $api->tradeApi('/api/v2/labs/txn/query', $request);

    print_r($response);
    echo $response->getRetCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
    echo $e->getMessage();
}


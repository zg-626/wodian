<?php
/**
 * 商户通知地址
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V2\V2Configuration();
$api = new \Lakala\OpenAPISDK\V2\Api\V2LakalaNotifyApi($config);

try {
    # 接收通知请求
    $request = $api->notiApi();
    # 通知请求头信息
    $headers = $request->getHeaders();
    # 通知请求原文
    $originalText = $request->getOriginalText();

    $obj = json_decode($originalText);

    // 处理$obj中交易状态 -> 可能多次调用

    // 通知拉卡拉，业务处理成功
    $api->success();
} catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
    // echo $e->getMessage();
    // 通知拉卡拉，发生异常
    $api->fail($e->getMessage());
}
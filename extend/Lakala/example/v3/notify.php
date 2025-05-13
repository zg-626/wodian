<?php
/**
 * 商户通知地址
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaNotifyApi($config);

try {
    # 接收通知请求
    $request = $api->notiApi();
    # 通知请求头信息
    $headers = $request->getHeaders();
    # 通知请求原文
    $originalText = $request->getOriginalText();

    $obj = json_decode($originalText);

    // 处理$obj中交易状态 -> 可能多次调用

    // 通知拉卡拉业，务处理成功
    $api->success();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    // echo $e->getMessage();
    // 通知拉卡拉，发生异常
    $api->fail($e->getMessage());
}
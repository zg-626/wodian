<?php
/**
 * 通用接口调用 -> 附件上传
 */

require_once '../../vendor/autoload.php';


$config = new \Lakala\OpenAPISDK\V2\V2Configuration();

$api = new \Lakala\OpenAPISDK\V2\Api\V2LakalaApi($config);
try {
    $request = new \Lakala\OpenAPISDK\V2\Model\V2ModelRequest();

    $data = file_get_contents('FR_ID_CARD_FRONT.jpg');
    $attContext = base64_encode($data);

    // 请求字段 - 法人身份证正面
    $request->setReqData([
        'attContext' => $attContext,
        'attType' => 'FR_ID_CARD_FRONT',
        'attExtName' => 'jpg',
        'orderNo' => '20231012113426kjEnsUsE',
        'orgCode' => '1',
        'version' => '1.0',
    ]);

    $response = $api->tradeApi('/api/v2/mms/openApi/uploadFile', $request);

    print_r($response);
    echo $response->getRetCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
    echo $e->getMessage();
}


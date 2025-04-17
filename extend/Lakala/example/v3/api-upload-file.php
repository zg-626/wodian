<?php
/**
 * 通用接口调用 -> 附件上传
 */

require_once '../../vendor/autoload.php';


$config = new \Lakala\OpenAPISDK\V3\Configuration();

$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);
try {
    $request = new \Lakala\OpenAPISDK\V3\Model\ModelRequest();

    $data = file_get_contents('../v2/FR_ID_CARD_FRONT.jpg');
    $attContext = base64_encode($data);

    // 请求字段 - 法人身份证正面
    $request->setReqData([
        'att_context' => $attContext,
        'att_type' => 'FR_ID_CARD_FRONT',
        'att_ext_name' => 'jpg',
        'order_no' => '20231012113426kjEnsUsE',
        'org_code' => '1',
        'version' => '1.0',
    ]);

    $response = $api->tradeApi('/api/v3/mms/open_api/upload_file', $request);

    print_r($response);
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}


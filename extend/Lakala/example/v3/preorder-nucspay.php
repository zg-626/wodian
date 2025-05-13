<?php
/**
 * 主扫交易示例 - 网联小钱包
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

// 主扫交易Api
$api = new \Lakala\OpenAPISDK\V3\Api\TransPreorderApi($config);

// 主扫交易参数
$request = new \Lakala\OpenAPISDK\V3\Model\TransPreorderRequest();
// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');
$request->setOutTradeNo(date('YmdHis', time()));
$request->setAccountType('NUCSPAY');
$request->setTransType('41');
$request->setTotalAmount('10000'); // 单位分
$request->setLocationInfo($tradeLocationInfo);

// 非必填参数
$request->setBusiMode('');
$request->setSubject('');
$request->setPayOrderNo('');
$request->setNotifyUrl('https://www.test.com/lakela_order_payment_callback.php');
$request->setSettleType('');
$request->setRemark('');
$request->setPromoInfo('');
$request->setOutOrderNo('');
$request->setPnrInsIdCd('');

// 网联小钱包主扫场景 - 账户端业务信息
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderNucspayAccBusiFields();
$acc_busi_fields->setNucIssrId('121213313');

// 账户端业务信息
$request->setAccBusiFields($acc_busi_fields);

try {
    $response = $api->transPreorder($request);
    if ($response->getRespData()) {
        print_r($response->getRespData());
        print_r($response->getAccRespFields());
    }
    else {
        print_r($response);
    }
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

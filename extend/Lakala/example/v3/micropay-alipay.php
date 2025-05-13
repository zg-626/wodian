<?php
/**
 * 被扫交易示例 - 支付宝
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

// 被扫交易Api
$api = new \Lakala\OpenAPISDK\V3\Api\TransMicropayApi($config);

// 被扫交易参数
$request = new \Lakala\OpenAPISDK\V3\Model\TransMicropayRequest();
// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');
$request->setOutTradeNo(date('YmdHis', time()));
$request->setAuthCode('ALIPAY');
$request->setTotalAmount('10000'); // 单位分
$request->setLocationInfo($tradeLocationInfo);

// 非必填参数
$request->setBusiMode('');
$request->setSubject('');
$request->setPayOrderNo('');
$request->setNotifyUrl('');
$request->setSettleType('');
$request->setRemark('');
$request->setScanType('');
$request->setPromoInfo('');
$request->setOutOrderNo('');
$request->setPnrInsIdCd('');

// 支付宝被扫场景 - 账户端业务信息
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayAlipayAccBusiFields();
$acc_busi_fields->setBusinessParams('');
$acc_busi_fields->setStoreId('');
$acc_busi_fields->setTimeoutExpress('');
$acc_busi_fields->setDisablePayChannels('');
$acc_busi_fields->setBusinessParams('');
$acc_busi_fields->setMinAge('');

$extend_params = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayAlipayExtendParams();
$extend_params->setSysServiceProviderId('');
$extend_params->setHbFqNum('');
$extend_params->setHbFqSellerPercent('');

$acc_busi_fields->setExtendParams($extend_params);

$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayAlipayGoodsDetail();
$goods_detail->setGoodsId('1042e1e1e');
$goods_detail->setAlipayGoodsId('');
$goods_detail->setGoodsName('');
$goods_detail->setQuantity('');
$goods_detail->setPrice('');
$goods_detail->setGoodsCategory('');
$goods_detail->setCategoriesTree('');
$goods_detail->setBody('');
$goods_detail->setShowUrl('');

$acc_busi_fields->setGoodsDetail([$goods_detail]);

// 账户端业务信息
$request->setAccBusiFields($acc_busi_fields);

try {
    $response = $api->transMicropay($request);
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

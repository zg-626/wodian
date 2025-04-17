<?php
/**
 * 主扫交易示例 - 支付宝 - 请求报文加密码
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

/**
 * 主扫交易Api
 * 
 * @config                 配置文件
 * @useSM4ForRequestBody   请求体用sm4加密码
 */
$api = new \Lakala\OpenAPISDK\V3\Api\TransPreorderApi($config, \Lakala\OpenAPISDK\V3\Api\EncryptMode::REQUEST);

// 主扫交易参数
$request = new \Lakala\OpenAPISDK\V3\Model\TransPreorderRequest();
// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');
$request->setOutTradeNo(date('YmdHis', time()));
$request->setAccountType('ALIPAY');
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

// 支付宝主扫场景 - 账户端业务信息
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayAccBusiFields();
$acc_busi_fields->setUserId('2843132323');
$acc_busi_fields->setTimeoutExpress('');
$acc_busi_fields->setQuitUrl('');
$acc_busi_fields->setStoreId('');
$acc_busi_fields->setDisablePayChannels('');
$acc_busi_fields->setBusinessParams('');
$acc_busi_fields->setMinAge('');

// 支付宝主扫场景 - extend_params
$extend_params = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayExtendParams();
$extend_params->setSysServiceProviderId('12121');
$extend_params->setHbFqNum('');
$extend_params->setHbFqSellerPercent('');
$extend_params->setFoodOrderType('');

$acc_busi_fields->setExtendParams($extend_params);

// 支付宝主扫场景 - 商品信息
$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayGoodsDetail();
$goods_detail->setGoodsId('1042e1e1e');
$goods_detail->setAlipayGoodsId('');
$goods_detail->setGoodsName('');
$goods_detail->setQuantity('');
$goods_detail->setPrice('');
$goods_detail->setGoodsCategory('');
$goods_detail->setCategoriesTree('');
$goods_detail->setBody('');
$goods_detail->setShowUrl('');

$goods_detail1 = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayGoodsDetail();
$goods_detail1->setGoodsId('37024242');
$goods_detail1->setAlipayGoodsId('');
$goods_detail1->setGoodsName('');
$goods_detail1->setQuantity('');
$goods_detail1->setPrice('');
$goods_detail1->setGoodsCategory('');
$goods_detail1->setCategoriesTree('');
$goods_detail1->setBody('');
$goods_detail1->setShowUrl('');

$acc_busi_fields->setGoodsDetail([$goods_detail, $goods_detail1]);

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

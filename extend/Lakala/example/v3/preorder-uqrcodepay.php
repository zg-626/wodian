<?php
/**
 * 主扫交易示例 - 银联
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
$request->setAccountType('UQRCODEPAY');
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

// 银联主扫场景 - 账户端业务信息
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAccBusiFields();
$acc_busi_fields->setUserId('2843132323');
$acc_busi_fields->setTimeoutExpress('');
$acc_busi_fields->setAcqAddnDataOrderInfo('');
$acc_busi_fields->setAcqAddnDataGoodsInfo('');
$acc_busi_fields->setFrontUrl('');
$acc_busi_fields->setFrontFailUrl('');
$acc_busi_fields->setInstalWill('');
$acc_busi_fields->setUnQrcode('');
$acc_busi_fields->setUserAuthCode('');

$acqAddnDataOrderInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAcqAddnDataOrderInfo();
$acqAddnDataOrderInfo->setTitle('标题');
$acqAddnDataOrderInfo->setDctAmount('');
$acqAddnDataOrderInfo->setAddnInfo('');

$addnInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAddnInfo();
$addnInfo->setPreproduct('');
$addnInfo->setLockplan('');

$riskInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayRiskInfo();
$riskInfo->setItemNo('');
$riskInfo->setOrderSource('');
$riskInfo->setPayUserId('');
$riskInfo->setPayCodeId('');
$riskInfo->setMerchantType('');

$addnInfo->setRiskInfo($riskInfo);

$acqAddnDataOrderInfo->setAddnInfo($addnInfo);

$acc_busi_fields->setAcqAddnDataOrderInfo($acqAddnDataOrderInfo);

$acq_addn_data_goods_info = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAcqAddnDataGoodsInfo();
$acq_addn_data_goods_info->setId('');
$acq_addn_data_goods_info->setName('');
$acq_addn_data_goods_info->setPrice('');
$acq_addn_data_goods_info->setQuantity('');
$acq_addn_data_goods_info->setCategory('');
$acq_addn_data_goods_info->setAddninfo('');

$acc_busi_fields->setAcqAddnDataGoodsInfo([$acq_addn_data_goods_info]);

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

<?php
/**
 * 退款交易 - 扩展响应字段
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

class RelationRefundResponseExt extends \Lakala\OpenAPISDK\V3\Model\RelationRefundResponse implements \JsonSerializable
{
    protected $ext1;

    public function setExt1($ext1)
    {
        $this->ext1 = $ext1;
        return $this;
    }
    
    public function getExt1()
    {
        return $this->ext1;
    }
}

class RelationRefundApiExt extends \Lakala\OpenAPISDK\V3\Api\RelationRefundApi
{
    public function relationRefundExt($relationRefundRequest)
    {
        $resourcePath = '/api/v3/labs/relation/refund';
        return $this->tradeApi($resourcePath, $relationRefundRequest, 'RelationRefundResponseExt');
    }
}

$api = new RelationRefundApiExt($config);

$request = new \Lakala\OpenAPISDK\V3\Model\RelationRefundRequest();

// 必填参数
$request->setMerchantNo('822290070111135');
$request->setTermNo('29034705');
$request->setOutTradeNo('23423492423');
$request->setRefundAmount('10000');
$request->setLocationInfo($tradeLocationInfo);

// 非必填参数
$request->setRefundReason('');
$request->setOriginOutTradeNo('');
$request->setOriginTradeNo('');
$request->setOriginLogNo('');

try {
    $response = $api->relationRefundExt($request);
    if ($response->getRespData()) {
        print_r($response->getRespData());
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

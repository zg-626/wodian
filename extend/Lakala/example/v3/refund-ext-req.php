<?php
/**
 * 退款交易 - 扩展请求参数
 */

require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

$api = new \Lakala\OpenAPISDK\V3\Api\RelationRefundApi($config);


class QueryTradequeryRequestExt extends \Lakala\OpenAPISDK\V3\Model\RelationRefundRequest implements \JsonSerializable
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
    

    public function jsonSerialize()
    {
        $js = parent::jsonSerialize();
        $js['ext1'] = $this->ext1;
        return $js;
    }
}

$request = new QueryTradequeryRequestExt();
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

$request->setExt1('hello ext 1');

try {
    $response = $api->relationRefund($request);
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

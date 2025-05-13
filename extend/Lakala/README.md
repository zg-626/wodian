# 拉卡拉开放平台SDK集成


### 使用 autoload
在 composer.json 文件中,定义自动加载规则:

```config
{
    "autoload": {
        "psr-4": {
            "Lakala\\OpenAPISDK\\V2\\" : "v2/src/",
            "Lakala\\OpenAPISDK\\V3\\" : "v3/src/"
        }
    }
}
```

### 运行 dump-autoload
配置好 composer.json 后,运行以下命令生成自动加载文件:

```shell
> composer dump-autoload
```

### 在项目中引入 autoload 文件
```php
require __DIR__ . '/vendor/autoload.php';
```

### 拉卡拉开放平台SDK配置文件
拉卡拉开放平台SDK配置 .lklopensdk.env，根据获取的开放平台信息设置

```config
#是否为测试环境
APP_DEBUG = true

[APP]
APP_ID      = OP00000003
SERIAL_NO   = 00dfba8194c41b84cf
# 生产主机地址
HOST_PRO    = https://s2.lakala.com
# 测试主机地址
HOST_TEST   = https://test.wsmsd.cn/sit

[CERT]
SM4_KEY = uIj6CPg1GZAY10dXFfsEAQ==
#相对当前配置文件的路径
MERCHANT_PRIVATE_KEY_PATH   = ../DEV/private_key.pem
LKL_CERTIFICATE_PATH        = ../DEV/lkl-apigw-v2.cer
```

使用
```php
# 读取配置文件
$config = new \Lakala\OpenAPISDK\V3\Configuration();
# 指定配置，相对Configuration.php文件的路径
$config = new \Lakala\OpenAPISDK\V3\Configuration($configfile);
# 或直接传入配置信息，path为绝对路径
$config = new \Lakala\OpenAPISDK\V3\Configuration(
    array(
        'app_debug' => true,
        'app_id' => 'OP00000003',
        'serial_no' => '00dfba8194c41b84cf',
        'host_pro' => 'https://s2.lakala.com',
        'host_test' => 'https://test.wsmsd.cn/sit',
        'sm4_key' => 'uIj6CPg1GZAY10dXFfsEAQ==',
        'merchant_private_key_path' => '/path/example/RSAKeys/DEV/OP00000003_private_key.pem',
        'lkl_certificate_path' => '/path/example/RSAKeys/DEV/lkl-apigw-v2.cer'
    )
);
```

# 拉卡拉开放平台SDK使用

## 请求体加密码（SM4）

```php
/**
 * Api请求体加密码需在第二个参数设置为true,默认为false
 * 
 * @config                 配置文件
 * @useSM4ForRequestBody   请求体是否用sm4加密码
 */
$api = new \Lakala\OpenAPISDK\V3\Api\******Api($config, false);

```


## 主扫交易
```php
# 加载配置
$config = new \Lakala\OpenAPISDK\V3\Configuration();
# 多配置文件，传递config文件名，（可是相对于默认配置文件的相对路径，如：../../.lklopensdk2.env）
$config2 = new \Lakala\OpenAPISDK\V3\Configuration('.lklopensdk2.env');


$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

/**
 * 主扫交易
 * 
 * @config                 配置文件
 * @useSM4ForRequestBody   请求体是否用sm4加密码
 */
$api = new \Lakala\OpenAPISDK\V3\Api\TransPreorderApi($config, false);
$request = new \Lakala\OpenAPISDK\V3\Model\TransPreorderRequest();
$request->setOutTradeNo(date('YmdHis', time()));
$request->setTotalAmount('100');
$request->setLocationInfo($tradeLocationInfo);
$request->setNotifyUrl('https://www.test.com/lakela/order/payment/callback.php');
$request->setRemark('5i - 订单支付');

# 支付宝主扫场景
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayAccBusiFields();
$acc_busi_fields->setUserId('2843132323');

$extend_params = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayExtendParams();
$extend_params->setSysServiceProviderId('12121');

$acc_busi_fields->setExtendParams($extend_params);

$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayGoodsDetail();
$goods_detail->setGoodsId('1042e1e1e');
$goods_detail1 = new \Lakala\OpenAPISDK\V3\Model\TradePreorderAlipayGoodsDetail();
$goods_detail1->setGoodsId('37024242');

$acc_busi_fields->setGoodsDetail([$goods_detail, $goods_detail1]);

$request->setAccBusiFields($acc_busi_fields);

/* 微信主扫场景
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderWechaAccBusiFields();
$acc_busi_fields->setUserId('2843132323');

$detail = new \Lakala\OpenAPISDK\V3\Model\TradePreorderWechaDetail;
$detail->setCostPrice('100');

$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradePreorderWechaGoodsDetail;
$goods_detail->setGoodsId('3452234');

$detail->setGoodsDetail([$goods_detail]);

$acc_busi_fields->setDetail($detail);

$request->setAccBusiFields($acc_busi_fields);
*/

/* 银联云闪付主扫场景
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAccBusiFields();
$acc_busi_fields->setUserId('2843132323');

$acqAddnDataOrderInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAcqAddnDataOrderInfo();
$acqAddnDataOrderInfo->setTitle('标题');

$addnInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAddnInfo();
$addnInfo->setLockplan('3');

$riskInfo = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayRiskInfo();
$riskInfo->setItemNo('3');
$riskInfo->setOrderSource('枚举如下：微信APP扫一扫、京东金融、京东');
$riskInfo->setPayCodeId('商户收款码ID，可以是商家展业所应用的静态码/动态码所发生的终端设备唯一识别序列号');
$addnInfo->setRiskInfo($riskInfo);

$acqAddnDataOrderInfo->setAddnInfo($addnInfo);

$acc_busi_fields->setAcqAddnDataOrderInfo($acqAddnDataOrderInfo);

$acq_addn_data_goods_info = new \Lakala\OpenAPISDK\V3\Model\TradePreorderUnionPayAcqAddnDataGoodsInfo();
$acq_addn_data_goods_info->setId('1221212');

$acc_busi_fields->setAcqAddnDataGoodsInfo([$acq_addn_data_goods_info]);

$request->setAccBusiFields($acc_busi_fields);
*/

/* 网联小钱包主扫场景
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradePreorderNucspayAccBusiFields();
$acc_busi_fields->setNucIssrId('121213313');
$request->setAccBusiFields($acc_busi_fields);
*/

try {
    $response = $api->transPreorder($request);
    print_r($response->getRespData());
    print_r($response->getAccRespFields());
    echo $response->getCode();

    # 响应头信息
    print_r($response->getHeaders());

    # 响应原文
    echo $response->getOriginalText();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

```

## 被扫交易
```php
# 加载配置
$config = new \Lakala\OpenAPISDK\V3\Configuration();

$tradeLocationInfo = new \Lakala\OpenAPISDK\V3\Model\TradeLocationInfo('106.37.232.115');

/**
 * 被扫交易
 * 
 * @config                 配置文件
 * @useSM4ForRequestBody   请求体是否用sm4加密码
 */
$api = new \Lakala\OpenAPISDK\V3\Api\TransMicropayApi($config, false);
$request = new \Lakala\OpenAPISDK\V3\Model\TransMicropayRequest();
$request->setOutTradeNo(date('YmdHis', time()));
$request->setAuthCode('h30230nd9823f-w20189312f3ri0023jf0239423');
$request->setTotalAmount('100');
$request->setLocationInfo($tradeLocationInfo);

#支付宝被扫场景
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayAccBusiFields();
$acc_busi_fields->setUserId('2843132323');

$extend_params = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayExtendParams();
$extend_params->setSysServiceProviderId('12121');

$acc_busi_fields->setExtendParams($extend_params);

$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayGoodsDetail();
$goods_detail->setGoodsId('1042e1e1e');
$goods_detail1 = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayGoodsDetail();
$goods_detail1->setGoodsId('37024242');

$acc_busi_fields->setGoodsDetail([$goods_detail, $goods_detail1]);

$request->setAccBusiFields($acc_busi_fields);

# 微信被扫场景
/*
$acc_busi_fields = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayWechaAccBusiFields();
$acc_busi_fields->setUserId('2843132323');

$detail = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayWechaDetail;
$detail->setCostPrice('100');

$goods_detail = new \Lakala\OpenAPISDK\V3\Model\TradeMicropayWechaGoodsDetail;
$goods_detail->setGoodsId('3452234');

$detail->setGoodsDetail([$goods_detail]);

$acc_busi_fields->setDetail($detail);

$request->setAccBusiFields($acc_busi_fields);
*/

try {
    $response = $api->transMicropay($request);
    print_r($response->getRespData());
    print_r($response->getAccRespFields());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

```

## 退款交易
```php
$api = new \Lakala\OpenAPISDK\V3\Api\RelationRefundApi($config);
$request = new \Lakala\OpenAPISDK\V3\Model\RelationRefundRequest();
$request->setOutTradeNo(date('YmdHis', time()));
$request->setRefundAmount('100');
$request->setLocationInfo($tradeLocationInfo);

try {
    $response = $api->relationRefund($request);
    print_r($response->getRespData());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

## 查询交易
```php
$api = new \Lakala\OpenAPISDK\V3\Api\QueryTradequeryApi($config);
$request = new \Lakala\OpenAPISDK\V3\Model\QueryTradequeryRequest();
$request->setOutTradeNo(date('YmdHis', time()));

try {
    $response = $api->queryTradequery($request);

    print_r($response->getRespData());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

## 通用接口调用 -> 查询交易
```php
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);
$request = new \Lakala\OpenAPISDK\V3\Model\ModelRequest();
# 请求字段
$request->setReqData([
    'merchant_no' => $config->getMerchantNo(),
    'term_no' => $config->getTermNo(),
    'out_trade_no' => date('YmdHis', time()),
    'trade_no' => '',
]);

try {
    $response = $api->tradeApi('/api/v3/labs/query/tradequery', $request);
    print_r($response->getRespData());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

## 使用报文体接口调用 -> 查询交易
```php
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);
try {
    $body = '{"req_time":"20240929033128","version":"3.0","req_data":{"merchant_no":"822290070111135","term_no":"29034705","out_trade_no":"20240929033128","trade_no":""}}';
    $response = $api->apiWithBody('/api/v3/labs/query/tradequery', $body);
    print_r($response->getRespData());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

或

```php
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaApi($config);
try {
    $json = new stdClass;
    $json->req_time = "20240929033128";
    $json->version = "3.0";
    $json->req_data = new stdClass;
    $json->req_data->merchant_no = "822290070111135";
    $json->req_data->term_no = "29034705";
    $json->req_data->out_trade_no = "20240929033128";
    $json->req_data->trade_no = "";

    // json格式body
    $response = $api->apiWithBody('/api/v3/labs/query/tradequery', $json);
    print_r($response->getRespData());
    echo $response->getCode();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

## 拉卡拉开放平台回调示例
```php
require_once '../../vendor/autoload.php';

$config = new \Lakala\OpenAPISDK\V3\Configuration();
$api = new \Lakala\OpenAPISDK\V3\Api\LakalaNotifyApi($config);

try {
    $response = $api->notiApi();
    # 响应头信息
    $headers = $response->getHeaders();
    # 响应原文
    $originalText = $response->getOriginalText();

    $obj = json_decode($originalText);

    // 处理$obj中交易状态 -> 可能多次调用

    // 通知拉卡拉业务处理成功
    $api->success();
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    // echo $e->getMessage();
    // 通知拉卡拉发生异常
    $api->fail($e->getMessage());
}
```

## 请求参数扩展
```php
$api = new \Lakala\OpenAPISDK\V3\Api\RelationRefundApi($config);

#扩展请求参数类
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

#扩展请求参数赋值
$request->setExt1('hello ext 1');

try {
    $response = $api->relationRefund($request);
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}
```

## 响应字段扩展
```php
#扩展响应字段数类
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
#扩展请求类
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

try {
    $response = $api->relationRefundExt($request);
} catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
    echo $e->getMessage();
}

```
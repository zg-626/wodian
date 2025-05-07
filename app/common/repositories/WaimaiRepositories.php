<?php

namespace app\common\repositories;
use app\common\model\meituan\MeituanOrder;
use app\common\repositories\BaseRepository;
use crmeb\services\MeituanService;
use think\Exception;

class WaimaiRepositories extends BaseRepository
{
    public $entId = '104984';
    public $accessKey = 'EI69RLOYPPMP-TK';
    public $secretKey = 'FOAd7WvvJb+lDSHSaAeUnQ==';
    // 测试环境地址
    public $url = 'https://waimai-openapi.apigw.test.meituan.com/api/sqt/open/login/h5/loginFree/redirection?test_open_swimlane=test-open';
    // 线上环境地址
    public $onlineUrl = 'https://bep-openapi.meituan.com/api/sqt/open/login/h5/loginFree/redirection';

    //美团外卖入口
    public function mt_waimai($params)
    {
        $meituanService = new MeituanService();
        $url = $this->url;
        $staffPhone = isset($params['mobile']) ? $params['mobile'] : ''; //员工手机号 1. 登录时, staffPhone/staffEmail/staffNum 三者必填一个, 与企业员工唯一识别对应
        $staffEmail = isset($params['staffEmail']) ? $params['staffEmail'] : ''; //员工邮箱
        $staffNum = isset($params['staffNum']) ? $params['staffNum'] : ''; //员工工号
        $externalOrgId = isset($params['externalOrgId']) ? $params['externalOrgId'] : ''; //部门唯一标识
        $orderId = isset($params['orderId']) ? $params['orderId'] : ''; //唯一订单号

        $ts = $meituanService->getMillisecond();
        $staffInfo = ['staffPhone' => $staffPhone];
        $nonce = $meituanService->randstr(32);

        $product_type = isset($params['product_type']) ? $params['product_type'] : 'mt_waimai'; // 跳转类型
        $longitude = isset($params['longitude']) ? $params['longitude'] : ''; //经度 116.480881
        $latitude = isset($params['latitude']) ? $params['latitude'] : ''; //纬度 39.989410
        $geotype = isset($params['geotype']) ? $params['geotype'] : 'wgs84'; //gcj02(火星坐标系)或者wgs84(国际坐标系)
        $address = isset($params['address']) ? $params['address'] : ''; //经纬度对应的中文地址北京市朝阳区阜通东大街6号
        $location = ['longitude' => $longitude, 'latitude' => $latitude, 'geotype' => $geotype, 'address' => $address];
        $bizParam = ['location' => $location];
        $bizParam = [];
        $data = ['productType' => $product_type, 'ts' => $ts, 'entId' => $this->entId, 'staffInfo' => $staffInfo, 'nonce' => $nonce,'sceneType'=>9];
        $content = $meituanService->aes_encrypt($data, $this->secretKey);
        $postData = ['accessKey' => $this->accessKey, 'content' => $content];
        $result = $meituanService->loginFree2Posts($url, $postData);
        return $result;
    }

    /**
     * 下单接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdCreateOrder
     * 用户在美团企业版下单时，美团企业版会调用【下单接口】通知客户平台，接口响应支付页面 URL，用户会跳转客户平台的支付页面进行支付。
     * @param array $params
     ***/
    public function create($params){
        list($accessKey,$content) = array_values($params);
        if(!$accessKey){
            return $this->response(self::$ERROR_401,'秘钥错误');
        }
        if(!$content){
            return $this->response(self::$ERROR_401,'请求体内容错误');
        }
        $meituanService = new MeituanService();
        $content = $meituanService->aes_decrypt($content,$this->secretKey);
        if(!$content){
            return $this->response(self::$ERROR_403,'解密验签失败');
        }
        if(!isset($content['staffInfo']) || !$content['staffInfo']){
            return $this->response(self::$ERROR_402,'员工信息参数缺失');
        }
        $trade_no = $content['tradeNo'];
        $order = MeituanOrder::where('trade_no',$trade_no)->find();
        if($order){
            if($order['pay_status'] != self::$PAY_STATUS_0){
                return $this->response(self::$ERROR_412,self::payStatusList()[$order['pay_status']]);
            }
        }

        $data['trade_no'] = $content['tradeNo'];
        $data['content'] = $content;
        $data['pay_status'] = self::$PAY_STATUS_0;
        try {
            MeituanOrder::create($data);
        } catch (Exception $e) {
            return $this->response(self::$ERROR_500,'File：' . $e->getFile() . " ，Line：" . $e->getLine() . '，Message：' . $e->getMessage());
        }

        $thirdTradeNo = $content['tradeNo'];
        $thirdPayUrl = "https://cashier.example.com/pay?tradeNo=1625341310296658007&thirdPayOrderId=757206679686983682&token=CC1NRDRJLC76-TK";
//        $thirdPayUrl = request()->domain().'/pay?';
        $data = compact('thirdTradeNo','thirdPayUrl');
        $dataEncrypt = $meituanService->aes_encrypt($data, $this->secretKey);
        return $this->response(0,'成功',$dataEncrypt);
    }

    public static $ERROR_401 = 401;
    public static $ERROR_402 = 402;
    public static $ERROR_403 = 403;
    public static $ERROR_410 = 410;
    public static $ERROR_411 = 411;
    public static $ERROR_412 = 412;
    public static $ERROR_500 = 500;
    public static $ERROR_501 = 501;
    public static $ERROR_510 = 510;
    public static $PAY_STATUS_0 = 0;
    public static $PAY_STATUS_1 = 1;
    public static $PAY_STATUS_2 = 2;
    public static $PAY_STATUS_10 = 10;
    public static function payStatusList()
    {
        return [
            self::$PAY_STATUS_0 => '待支付',
            self::$PAY_STATUS_1 => '支付成功',
            self::$PAY_STATUS_2 => '支付失败',
            self::$PAY_STATUS_10 => '支付超时关单',
        ];
    }


    public function response($status, $msg, $data = array())
    {
        return array('status' => $status, 'msg' => $msg, 'data' => $data);
    }

}
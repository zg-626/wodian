<?php

namespace app\common\repositories;
use app\common\model\meituan\MeituanOrder;
use app\common\model\meituan\MeituanOrderRefund;
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
    public function create($params)
    {
        $content_res = $this->validateParams($params);
        if ($content_res['status'] != 0) {
            return $content_res;
        }
        $content = $content_res['data'];

        if (!isset($content['staffInfo']) || !$content['staffInfo']) {
            return $this->response(self::$ERROR_402, '员工信息参数缺失');
        }
        $tradeNo = $content['tradeNo'];
        $order = MeituanOrder::where('trade_no', $tradeNo)->find();
        if ($order) {
            if ($order['pay_status'] != self::$PAY_STATUS_0) {
                return $this->response(self::$ERROR_412, self::payStatusList()[$order['pay_status']]);
            }
        }

        $data['trade_no'] = $tradeNo;
        $data['trade_amount'] = $content['tradeAmount'];
        $data['pay_status'] = self::$PAY_STATUS_0;
        $data['create_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
        try {
            MeituanOrder::create($data);
        } catch (Exception $e) {
            return $this->response(self::$ERROR_501, 'File：' . $e->getFile() . " ，Line：" . $e->getLine() . '，Message：' . $e->getMessage());
        }

        $thirdTradeNo = $content['tradeNo'];
        // TODO 客户平台支付页面 URL，美团企业版以 GET 方式重定向到该地址，必须是 HTTPS 协议，否则 IOS 系统不能访问。
        $thirdPayUrl = "https://cashier.example.com/pay?tradeNo=1625341310296658007&thirdPayOrderId=757206679686983682&token=CC1NRDRJLC76-TK";
//        $thirdPayUrl = request()->domain().'/pay?';
        $data = compact('thirdTradeNo', 'thirdPayUrl');
        $meituanService = new MeituanService();
        $dataEncrypt = $meituanService->aes_encrypt($data, $this->secretKey);
        return $this->response(0, '成功', $dataEncrypt);
    }

    /**
     * 关单接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdClosePay
     * 当订单超过支付时效，美团企业版通过关单接口向客户平台发起关单请求，客户平台需要将未付款的交易单关闭并拦截用户支付。在交易创建后，用户在超过交易关单时间后仍然未支付成功，会触发交易关单。关单时间参考下单接口中的tradeExpiringTime。
     * @param array $params
     ***/
    public function close($params)
    {
        $content_res = $this->validateParams($params);
        if ($content_res['status'] != 200) {
            return $content_res;
        }
        $content = $content_res['data'];

        $tradeNo = $content['tradeNo'];
        $order = MeituanOrder::where('trade_no', $tradeNo)->find();
        if (!$order) {
            return $this->response(self::$ERROR_410, '支付单不存在');
        }

        $data['pay_status'] = self::$PAY_STATUS_10;
        $data['close_time'] = date('Y-m-d H:i:s');
        $data['close_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
        try {
            $order->save($data);
        } catch (Exception $e) {
            return $this->response(self::$ERROR_501, 'File：' . $e->getFile() . " ，Line：" . $e->getLine() . '，Message：' . $e->getMessage());
        }

        $thirdTradeNo = $tradeNo;
        $data = compact('tradeNo', 'thirdTradeNo');
        $meituanService = new MeituanService();
        $dataEncrypt = $meituanService->aes_encrypt($data, $this->secretKey);
        return $this->response(0, '成功', $dataEncrypt);

    }

    /**
     * 退款接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdRefund
     * 当用户在美团企业版发起退款时，美团企业版根据【退款接口】通知客户平台，为保证双方交易状态一致，客户平台需执行退款，并返回退款成功。当接口出现网络超时或服务繁忙响应（错误码 501）时，美团企业版会重试退款，具体重试策略参考附录
     * @param array $params
     ***/
    public function refund($params)
    {
        $content_res = $this->validateParams($params);
        if ($content_res['status'] != 200) {
            return $content_res;
        }
        $content = $content_res['data'];

        $tradeNo = $content['tradeNo'];
        $order = MeituanOrder::where('trade_no', $tradeNo)->find();
        if (!$order) {
            return $this->response(self::$ERROR_410, '支付单不存在');
        }
        if ($order['refund_status'] == self::$PAY_STATUS_1) {
            return $this->response(self::$ERROR_411, '退款超额');
        }
        $be_refund_amount = $order['refund_amount']; // 已退款金额
        $refundAmount = $content['refundAmount']; // 本次退款金额
        $total_refund_amount = $be_refund_amount + $refundAmount; // 已退款金额 + 本次退款金额 = 总退款金额
        $trade_amount = $order['trade_amount']; // 支付金额
        if ($total_refund_amount > $trade_amount) {
            return $this->response(self::$ERROR_411, '退款超额');
        }

        $refund = MeituanOrderRefund::where('trade_no', $tradeNo)->where('trade_refund_no', $content['tradeRefundNo'])->find();
        if (!$refund) {
            $third_refund_no = $tradeNo . date('Ymd') . time();
            $data['trade_no'] = $tradeNo;
            $data['trade_refund_no'] = $content['tradeRefundNo'];
            $data['third_refund_no'] = $third_refund_no;
            $data['refund_amount'] = $refundAmount;
            $data['refund_status'] = self::$PAY_STATUS_0;
            $data['refund_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
            // TODO 退款操作 start

            // TODO 退款操作 end
            if ($total_refund_amount == $trade_amount) {
                $order_data['refund_amount'] = $trade_amount;
                $order_data['refund_time'] = date('Y-m-d H:i:s');
                $order_data['refund_status'] = self::$PAY_STATUS_1;
                $data['refund_time'] = date('Y-m-d H:i:s');
                $data['refund_status'] = self::$PAY_STATUS_1;
            } else {
                $order_data['refund_amount'] = $order['refund_amount'] + $refundAmount;
                $order_data['refund_time'] = date('Y-m-d H:i:s');
                $order_data['refund_status'] = self::$PAY_STATUS_2;
                $data['refund_time'] = date('Y-m-d H:i:s');
                $data['refund_status'] = self::$PAY_STATUS_2;
            }
            try {
                MeituanOrderRefund::create($data);
                $order->save($order_data);
            } catch (Exception $e) {
                return $this->response(self::$ERROR_501, 'File：' . $e->getFile() . " ，Line：" . $e->getLine() . '，Message：' . $e->getMessage());
            }
        } else {
            $third_refund_no = $refund['third_refund_no'];
            $refundAmount = $refund['refund_amount'];
        }

        $thirdRefundNo = $third_refund_no;
        $refundDetails = "[{\"fundBearer\":\"cust\",\"detailAmount\":$refundAmount}]";
        $data = compact('thirdRefundNo', 'refundDetails');
        $meituanService = new MeituanService();
        $dataEncrypt = $meituanService->aes_encrypt($data, $this->secretKey);
        return $this->response(0, '成功', $dataEncrypt);

    }

    public function validateParams($params)
    {
        list($accessKey, $content) = array_values($params);
        if (!$accessKey) {
            return $this->response(self::$ERROR_401, '秘钥参数错误');
        }
        if (!$content) {
            return $this->response(self::$ERROR_401, '请求体内容参数错误');
        }
        $meituanService = new MeituanService();
        $content = $meituanService->aes_decrypt($content, $this->secretKey);
        if (!$content) {
            return $this->response(self::$ERROR_403, '解密验签失败');
        }
        return $this->response(0, 'success', $content);
    }

    public function response($status, $msg, $data = array())
    {
        return array('status' => $status, 'msg' => $msg, 'data' => $data);
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

    public static function errorStatusList()
    {
        return [
            self::$ERROR_401 => '参数错误',
            self::$ERROR_402 => '参数缺失',
            self::$ERROR_403 => '解密验签失败',
            self::$ERROR_410 => '支付单不存在',
            self::$ERROR_411 => '退款超额',
            self::$ERROR_412 => '订单已支付',
            self::$ERROR_500 => '服务端异常',
            self::$ERROR_501 => '服务繁忙（可重试）',
            self::$ERROR_510 => '员工账户不可用',
        ];
    }

    public static $PAY_STATUS_0 = 0; // 0 未退款
    public static $PAY_STATUS_1 = 1; // 1 退款成功
    public static $PAY_STATUS_2 = 2; // 2 部分退款成功
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

}
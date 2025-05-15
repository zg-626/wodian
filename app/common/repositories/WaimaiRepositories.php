<?php

namespace app\common\repositories;

use app\common\model\meituan\MeituanOrder;
use app\common\model\meituan\MeituanOrderRefund;
use app\common\model\store\order\StoreGroupOrder;
use app\common\model\store\order\StoreOrder;
use app\common\model\user\User;
use app\common\repositories\BaseRepository;
use crmeb\services\MeituanService;
use think\Exception;

class WaimaiRepositories extends BaseRepository
{
    public $entId = '104984';
    public $accessKey = 'EI69RLOYPPMP-TK';
    public $secretKey = 'FOAd7WvvJb+lDSHSaAeUnQ==';
    // 美团免登录测试环境地址
    public $url = 'https://waimai-openapi.apigw.test.meituan.com';
    // 美团免登录线上环境地址
    public $onlineUrl = 'https://bep-openapi.meituan.com';

    //美团外卖入口
    public function mt_waimai($params)
    {
        $meituanService = new MeituanService();
        $url = $this->url . '/api/sqt/open/login/h5/loginFree/redirection?test_open_swimlane=test-open';
        //$url = $this->onlineUrl.'/api/sqt/open/login/h5/loginFree/redirection';
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
        $data = ['productType' => $product_type, 'ts' => $ts, 'entId' => $this->entId, 'staffInfo' => $staffInfo, 'nonce' => $nonce, 'sceneType' => 9];
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

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 美团订单数据: ' . json_encode($content, JSON_UNESCAPED_UNICODE), 'meituan_order_create');

        if (!isset($content['staffInfo']) || !$content['staffInfo']) {
            return $this->response(self::$ERROR_402, '员工信息参数缺失');
        }

        $tradeNo = $content['tradeNo'];
        $order = MeituanOrder::where('trade_no', $tradeNo)->find();
        if ($order) {
            // 如果订单支付状态不是未支付，则返回对应状态
            if ($order['pay_status'] != self::$PAY_STATUS_0) {
                return $this->response(self::$ERROR_412, self::payStatusList()[$order['pay_status']]);
            }
            
            // 获取用户信息
            $phone = $order['phone'];
            $user = User::where('phone', $phone)->field('uid')->find();
            
            // 构建返回相同的支付URL和流水号
            $thirdTradeNo = $tradeNo;
            $groupOrderId = $order['group_order_id'];
            $thirdPayUrl = request()->domain() . '/pages/store/meituan/index?tradeNo=' . $thirdTradeNo . '&phone=' . $phone . '&groupOrderId=' . $groupOrderId;
            $data = compact('thirdTradeNo', 'thirdPayUrl');
            $meituanService = new MeituanService();
            return $this->response(0, '成功', $meituanService->aes_encrypt($data, $this->secretKey));
        }

        if ($order && $order['pay_status'] != self::$PAY_STATUS_0) {
            return $this->response(self::$ERROR_412, self::payStatusList()[$order['pay_status']]);
        }

        $phone= $content['staffInfo']['staffPhone'];
        // 如果staffPhone为空，则使用staffNum
        if($phone===''){
            $phone=$content['staffInfo']['staffNum'];
        }

        $data['phone'] = $phone;
        $data['trade_no'] = $tradeNo;
        $data['trade_amount'] = $content['tradeAmount'];
        $data['service_fee_amount'] = $content['serviceFeeAmount'];
        $data['business_discount_pay_amount'] = $content['businessDiscountPayAmount']??0;
        $data['pay_status'] = self::$PAY_STATUS_0;
        $data['create_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);

        $user = User::where('phone', $phone)->field('uid,nickname,phone')->findOrEmpty()->toArray();
        $data['uid'] = $user['uid']?? 0;
        if(!$user){
            return $this->response(self::$ERROR_510, self::errorStatusList()[self::$ERROR_510]);
        }
        $groupOrder = [
            'uid' => $user['uid'] ?? 0,
            'group_order_sn' => 'wxo' . $tradeNo,
            'total_postage' => 0,
            'total_price' => $content['tradeAmount'],
            'total_num' => 1,
            'real_name' => $user['nickname'] ?? '',
            'user_phone' => $user['phone'] ?? '',
            'user_address' => '',
            'pay_price' => $content['tradeAmount']?: 0,
            'coupon_price' => $content['businessDiscountPayAmount']??0,
            'pay_postage' => 0,
            'cost' => $content['tradeAmount']?: 0,
            'coupon_id' => '',
            'pay_type' => 0,
            'give_coupon_ids' => '',
            'integral' => 0,
            'integral_price' => 0,
            'deduction' => 0,
            'deduction_price' => 0,
            'give_integral' => 0,
            'activity_type' => 0,

            'is_meituan' => 1,
            'trade_no' => $tradeNo,
        ];
        $_order = [
            'activity_type' => 0,
            'commission_rate' => 0,
            'order_type' => 0,
            'is_virtual' => 0,
            'extension_one' => 0,
            'extension_two' => 0,
            'order_sn' => $groupOrder['group_order_sn'],
            'uid' => $user['uid'] ?? 0,
            'spread_uid' => 0,
            'top_uid' => 0,
            'is_selfbuy' => 1,
            'real_name' => $user['nickname'] ?? '',
            'user_phone' => $user['phone'] ?? '',
            'user_address' => '',
            'cart_id' => '',
            'total_num' => $groupOrder['total_num'],
            'total_price' => $groupOrder['total_price'],
            'total_postage' => 0,
            'pay_postage' => $groupOrder['pay_postage'],
            'svip_discount' => 0,
            'pay_price' => $groupOrder['pay_price'],
            'integral' => $groupOrder['integral'],
            'integral_price' => $groupOrder['integral_price'],
            'give_integral' => $groupOrder['give_integral'],
            'deduction' => $groupOrder['deduction'],
            'deduction_price' => $groupOrder['deduction_price'],
            'mer_id' => 0,
            'cost' => $groupOrder['cost'],
            'order_extend' => '',
            'coupon_id' => $groupOrder['coupon_id'],
            'mark' => '',
            'coupon_price' => $groupOrder['coupon_price'],
            'platform_coupon_price' => 0,
            'pay_type' => 0,
            'refund_switch' => 1,

            'is_meituan' => $groupOrder['is_meituan'],
            'trade_no' => $groupOrder['trade_no'],
            'create_content' => $data['create_content'],
        ];
        try {
            $group_order = StoreGroupOrder::create($groupOrder);
            $groupOrderId = $group_order['group_order_id'];
            $data['group_order_id'] = $groupOrderId;
            MeituanOrder::create($data);
            $_order['group_order_id'] = $groupOrderId;
            StoreOrder::create($_order);
        } catch (Exception $e) {
            return $this->response(self::$ERROR_501, 'File：' . $e->getFile() . " ，Line：" . $e->getLine() . '，Message：' . $e->getMessage());
        }

        $thirdTradeNo = $tradeNo;
        // TODO 客户平台支付页面 URL，美团企业版以 GET 方式重定向到该地址，必须是 HTTPS 协议，否则 IOS 系统不能访问。
        //$thirdPayUrl = "https://cashier.example.com/pay?tradeNo=1625341310296658007&thirdPayOrderId=757206679686983682&phone=18511111111";
        $thirdPayUrl = request()->domain() . '/pages/store/meituan/index?tradeNo=' . $thirdTradeNo . '&phone=' . $phone . '&groupOrderId=' . $groupOrderId;
        $data = compact('thirdTradeNo', 'thirdPayUrl');
        $meituanService = new MeituanService();
        return $this->response(0, '成功', $meituanService->aes_encrypt($data, $this->secretKey));
    }

    /**
     * 支付状态查询接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdPayQuery
     * 美团企业版通过【支付状态查询】接口主动查询客户平台的交易支付状态。
     * 触发条件：调用【下单接口】后，超过5s未收到支付成功消息，即会调用【支付状态查询】接口。
     * 调用频次：一共尝试9次查询，1-3次，每隔5s查询一次；4-6次，每隔10s查询一次；7-9次，每隔300s查询一次。
     * @param array $params
     ***/
    public function query($params)
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
        $create_content = json_decode($order['create_content'], true);

        $thirdTradeNo = $tradeNo;
        $payStatus = $order['pay_status'];
        $tradeTime = $order['create_time'];
        $tradeAmount = $create_content['tradeAmount'];
        $entPayAmount = $create_content['entPayAmount'];
        $businessDiscountPayAmount = $create_content['businessDiscountPayAmount']?: 0;
        $serviceFeeAmount = $create_content['serviceFeeAmount'];
        $paymentDetails = $order['paymentDetails'];
        $data = compact('tradeNo', 'thirdTradeNo', 'payStatus', 'tradeTime', 'tradeAmount', 'entPayAmount', 'businessDiscountPayAmount', 'serviceFeeAmount', 'paymentDetails');
        $meituanService = new MeituanService();
        return $this->response(0, '成功', $meituanService->aes_encrypt($data, $this->secretKey));
    }

    /**
     * 关单接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdClosePay
     * 当订单超过支付时效，美团企业版通过关单接口向客户平台发起关单请求，客户平台需要将未付款的交易单关闭并拦截用户支付。
     * 在交易创建后，用户在超过交易关单时间后仍然未支付成功，会触发交易关单。关单时间参考下单接口中的tradeExpiringTime。
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
        return $this->response(0, '成功', $meituanService->aes_encrypt($data, $this->secretKey));

    }

    /**
     * 退款接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdRefund
     * 当用户在美团企业版发起退款时，美团企业版根据【退款接口】通知客户平台，为保证双方交易状态一致，客户平台需执行退款，并返回退款成功。
     * 当接口出现网络超时或服务繁忙响应（错误码 501）时，美团企业版会重试退款，具体重试策略参考附录
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
        return $this->response(0, '成功', $meituanService->aes_encrypt($data, $this->secretKey));
    }

    /**
     * 美团支付成功通知接口
     * https://bep-openapi.meituan.com/api/sqt/openplatform_web/site/index.html#/apiDoc/standardThirdPayCallback#支付成功通知接口
     * 客户平台通过【支付成功通知】接口将支付成功状态同步给美团企业版，美团企业版将推动交易完成。
     * @param array $params
     */
    public function payCallback($params)
    {
        $meituanService = new MeituanService();
        $url = $this->url . '/api/sqt/open/standardThird/v2/pay/callback?tradeModel=FLOW';
        //$url = $this->onlineUrl.'/api/sqt/open/standardThird/v2/pay/callback?tradeModel=FLOW';

        $ts = $meituanService->getMillisecond();// 13位时间戳。若请求发起时间与平台接受请求时间相差大于10分钟，平台将直接拒绝本次请求
        $tradeNo = $params['tradeNo'];// 交易号，每笔支付的唯一标识，需要以此字段做幂等处理
        $thirdTradeNo = $params['thirdTradeNo'];// 客户平台交易号
        $tradeAmount = $params['tradeAmount'];// 支付金额(不包含服务费)，单位元，支持小数点后两位
        $nonce = $meituanService->randstr(32);

        $data = ['ts' => $ts, 'entId' => $this->entId, 'tradeNo' => $tradeNo, 'nonce' => $nonce, 'thirdTradeNo' => $thirdTradeNo, 'tradeAmount' => $tradeAmount];
        $content = $meituanService->aes_encrypt($data, $this->secretKey);
        $postData = ['accessKey' => $this->accessKey, 'content' => $content];
        $result = $meituanService->loginFree2Post($url, $postData);
        return $result;

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
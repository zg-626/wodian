<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


namespace crmeb\services\easywechat\partnerPay;


use app\common\model\store\order\StoreRefundOrder;
use crmeb\services\easywechat\PartnerClient;
use think\exception\ValidateException;
use think\facade\Log;
use think\facade\Route;
use function EasyWeChat\Payment\generate_sign;

class Client extends PartnerClient
{

    public function handleNotify($callback)
    {
        $request = request();
        $success = $request->post('event_type') === 'TRANSACTION.SUCCESS';
        $data = $this->decrypt($request->post('resource', []));

        $handleResult = call_user_func_array($callback, [json_decode($data, true), $success]);
        if (is_bool($handleResult) && $handleResult) {
            $response = [
                'code' => 'SUCCESS',
                'message' => 'OK',
            ];
        } else {
            $response = [
                'code' => 'FAIL',
                'message' => $handleResult,
            ];
        }

        return response($response, 200, [], 'json');
    }

    public function pay($type, array $order)
    {
        $params = [
            'out_trade_no' => $order['out_trade_no'],
            'sp_mchid' => $this->app['config']['service_payment']['merchant_id'],// 服务商商户号
            //'sp_appid' => 'wx5acfa79d79184c88',// 服务商APPID
            'description' => $order['description'],
            'sp_appid' => $this->app['config']['app_id'],// 服务商APPID
            'sub_mchid' => $order['sub_mchid'],// 子商户id
            'attach' => $order['attach'],
            'scene_info' => [
                'device_id' => 'shop system',
                'payer_client_ip' => request()->ip(),
            ],
            'amount' => [
                'total' => intval($order['pay_price'] * 100),
                'currency' => 'CNY',
            ],
            'settle_info' => [
                'profit_sharing' => false // 不分帐
            ],
            'notify_url' => systemConfig('site_url') . Route::buildUrl('partnerNotify')->build(),
            //'notify_url' => rtrim(systemConfig('site_url'), '/') . Route::buildUrl($this->app['config']['service_payment']['type'] . 'CombinePayNotify', ['type' => $order['attach']])->build(),
        ];

        if (isset($order['openid'])) {
            $params['payer'] = [
                'sp_openid' => $order['openid'],
            ];
        }

        if ($type === 'h5') {
            $params['scene_info']['h5_info'] = [
                'type' => $order['h5_type'] ?? 'Wap'
            ];
        }

        Log::info('微信服务商v3支付：'.var_export($params,true));
        $content = json_encode($params, JSON_UNESCAPED_UNICODE);

        $res = $this->request('/v3/pay/partner/transactions/' . $type, 'POST', ['sign_body' => $content]);
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错:' . $res['message']);
        }
        return $res;
    }

    public function payApp(array $options)
    {
        $res = $this->pay('app', $options);
        return $this->configForAppPayment($res['prepay_id']);
    }

    /**
     * @param string $type 场景类型，枚举值： iOS：IOS移动应用； Android：安卓移动应用； Wap：WAP网站应用
     */
    public function payH5(array $options, $type = 'Wap')
    {
        $options['h5_type'] = $type;
        return $this->pay('h5', $options);
    }


    /*Array
        (
        [out_trade_no] => wxs174436295438596387
        [pay_price] => 20
        [attach] => offline_order
        [sub_mchid] => 1713511214
        [body] => 线下门店支付
    )*/
    public function payJs($openId, array $options)
    {
        $options['openid'] = $openId;
        $res = $this->pay('jsapi', $options);
        return $this->configForJSSDKPayment($res['prepay_id']);
    }

    public function payNative(array $options)
    {
        return $this->pay('native', $options);
    }

    // 服务商请求分账
    /*
     * array (
          'appid' => 'wxaa4bb5782ea68d7e',
          'sub_mchid' => '1713506668',
          'transaction_id' => '4200001656202212131458556229',
          'out_order_no' => 'pr174470435280019259',
          'receivers' =>
          array (
            0 =>
            array (
              'amount' => 400,
              'description' => '订单分账',
              'unfreeze_unsplit' => true,
              'account 　' => 1709024127,
              'type' => 'MERCHANT_ID',
            ),
          ),
        )
     * */
    public function profitsharingOrder(array $options)
    {
        $params = [
            //'appid' => $this->app['config']['app_id'],
            'appid' => 'wxda2922aa5121cc98',
            'sub_mchid' => $options['sub_mchid'],
            'transaction_id' => $options['transaction_id'],
            'out_order_no' => $options['out_order_no'],
            'unfreeze_unsplit' => $receiver['unfreeze_unsplit'] ?? true,
            'receivers' => []
        ];

        foreach ($options['receivers'] as $receiver) {
            $data = [
                'amount' => intval($receiver['amount'] * 100),
                'description' => $receiver['body'] ?? $options['body'] ?? '',
            ];
            $data['account'] = $receiver['receiver_account'];
            $data['type'] = 'MERCHANT_ID';

            $params['receivers'][] = $data;
        }

        Log::info('微信服务商v3分账：'.var_export($params,true));
        $content = json_encode($params);

        $res = $this->request('/v3/profitsharing/orders', 'POST', ['sign_body' => $content]);
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错:' . $res['message']);
        }
        return $res;
    }

    /**
     * 服务商添加分账接收方（必须加密 name，适用于 MERCHANT_ID 方式）
     * @param array $options [
     *     'sub_mchid' => '子商户号', // 发起分账的子商户
     *     'name' => '商户全称',    // 必传，必须是商户的注册全称（加密后传输）
     *     'receiver_account' => '1709305541', // 接收方商户号
     * ]
     * @return array 微信返回结果
     * @throws ValidateException
     */
    public function profitsharingAddReceiver(array $options)
    {
        // 如果未传入 name，直接报错（因为 MERCHANT_ID 必须传）
        if (empty($options['name'])) {
            throw new ValidateException('分账接收方 name（商户全称）必须填写！');
        }

        $encryptedName = $this->encryptSensitiveInformation($options['name']);

        // 2. 准备请求参数
        $params = [
            'sub_mchid' => '1713931286', // 子商户号
            'appid' => 'wxda2922aa5121cc98', // 服务商APPID
            'type' => 'MERCHANT_ID', // 固定
            'account' => '1709305541', // 接收方商户号（你自己的服务商商户号）
            'name' => $encryptedName, // 已加密的商户全称
            'relation_type' => 'SERVICE_PROVIDER', // 服务商身份
        ];

        // 3. 记录请求日志（可选）
        Log::info('接收方请求参数（加密后）：'.var_export($params,true));

        // 4. 发起请求
        $content = json_encode($params);
        $res = $this->request(
            '/v3/profitsharing/receivers/add',
            'POST',
            ['sign_body' => $content]
        );

        // 5. 错误处理
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错: ' . $res['message']);
        }
        return $res;
    }

    public function profitsharingFinishOrder(array $params)
    {
        $content = json_encode($params);
        $res = $this->request('/v3/ecommerce/profitsharing/finish-order', 'POST', ['sign_body' => $content]);
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错:' . $res['message']);
        }
        return $res;
    }

    public function payOrderRefund(string $order_sn, array $options)
    {
        $params = [
            'sub_mchid' => $options['sub_mchid'],
            'sp_appid' => $this->app['config']['app_id'],
            'out_trade_no' => $options['order_sn'],
            'out_refund_no' => $options['refund_order_sn'],
            'amount' => [
                'refund' => intval($options['refund_price'] * 100),
                'total' => intval($options['pay_price'] * 100),
                'currency' => 'CNY'
            ]
        ];
        if (isset($options['reason'])) {
            $params['reason'] = $options['reason'];
        }
        if (isset($options['refund_account'])) {
            $params['refund_account'] = $options['refund_account'];
        }
        $content = json_encode($params);
        $res = $this->request('/v3/ecommerce/refunds/apply', 'POST', ['sign_body' => $content], true);
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错:' . $res['message']);
        }
        return $res;
    }

    public function returnAdvance($refund_id, $sub_mchid)
    {
        $res = $this->request('/v3/ecommerce/refunds/' . $refund_id . '/return-advance', 'POST', ['sign_body' => json_encode(compact('sub_mchid'))], true);
        if (isset($res['code'])) {
            throw new ValidateException('微信接口报错:' . $res['message']);
        }
        return $res;
    }

    public function configForPayment($prepayId, $json = true)
    {
        $params = [
            'appId' => $this->app['config']['app_id'],
            'timeStamp' => strval(time()),
            'nonceStr' => uniqid(),
            'package' => "prepay_id=$prepayId",
            'signType' => 'RSA',
        ];
        $message = $params['appId'] . "\n" .
            $params['timeStamp'] . "\n" .
            $params['nonceStr'] . "\n" .
            $params['package'] . "\n";
        openssl_sign($message, $raw_sign, $this->getPrivateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        $params['paySign'] = $sign;

        return $json ? json_encode($params) : $params;
    }

    /**
     * Generate app payment parameters.
     *
     * @param string $prepayId
     *
     * @return array
     */
    public function configForAppPayment($prepayId)
    {
        $params = [
            'appid' => $this->app['config']['app_id'],
            'partnerid' => $this->app['config']['service_payment']['merchant_id'],
            'prepayid' => $prepayId,
            'noncestr' => uniqid(),
            'timestamp' => time(),
            'package' => 'Sign=WXPay',
        ];
        $message = $params['appid'] . "\n" .
            $params['timestamp'] . "\n" .
            $params['noncestr'] . "\n" .
            $params['prepayid'] . "\n";
        openssl_sign($message, $raw_sign, $this->getPrivateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        $params['sign'] = $sign;

        return $params;
    }

    public function configForJSSDKPayment($prepayId)
    {
        $config = $this->configForPayment($prepayId, false);

        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);

        return $config;
    }

}

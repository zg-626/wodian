<?php

namespace app\controller\api\lakala;

use app\common\model\system\merchant\MerchantEcLkl as LklModel;
use app\common\repositories\store\order\StoreOrderOfflineRepository;
use crmeb\basic\BaseController;
use think\facade\Log;
use think\response\Json;
use Lakala\OpenAPISDK\V2\V2Configuration;
use Lakala\OpenAPISDK\V2\Api\V2LakalaNotifyApi;
use Lakala\OpenAPISDK\V3\Api\LakalaNotifyApi;
use Lakala\OpenAPISDK\V3\Configuration;
use think\facade\Db;

class Lakala extends BaseController
{
    // 支付回调
    public function notify()
    {
        return app('json')->success('请求成功');
    }

    /**
     * @desc 电子合同签约结果回调通知
     * @author ZhouTing
     * @date 2025-04-17 18:44
     */
    public function lklEcApplyNotify()
    {
        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            // $headers = $request->getHeaders();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '电子合同签约回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($originalText, true);

            // {"ecApplyId": 635798487769907200,"ecName": "特约商户支付服务合作协议V3.1","ecNo": "QT20221021000157861","ecStatus": "COMPLETED","orderNo": "177212022102111161183863984","orgId": 1,"version": "1.0"}
            $info = LklModel::getInfo(['lkl_ec_apply_id' => $obj['ecApplyId']]);
            if (!empty($info)) {
                $info->save(['lkl_ec_no' => $obj['ecNo'], 'lkl_ec_status' => $obj['ecStatus']]);
            }
            // 通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉电子合同签约回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    /**
     * @desc 商户进件 回调通知
     * @author ZhouTing
     * @date 2025-04-21 19:38
     */
    public function lklMerchantApplyNotify()
    {
        // $param = input('');
        // Db::name('third_notify')->insert(['title' => '商户进件回调3333333', 'content' => json_encode($param, JSON_UNESCAPED_UNICODE), 'createtime' => time()]);
        // record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户进件回调3333333: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');
        $param = '{"data":"rmB6Wop7zPouc\/FN+FUSPCgFBLMQSVgGLxTt7KaaCnmb\/d2NidwjiglqNU\/L6GB7U0A8jTyT593B4\/3JPYgKyJpZ651UZw6rqlTMglHG9O54SrKQ3XfWQW8qKNtipTy1B3vsCcy4Zj5XOoPac53q0MtvgRG7WoFqzy1te8QuEZtsduFivYLHGeTfIK\/SNPVmf1LBXXkos5TtvbVz5upHCO4HHkf71krJlOXIfOVDInz9pqJrzu3Mk0zgPP\/2A1lUXIY8HNLkiU3SpGRw5Zz2vjihgoeGfwuDW4ddtGhWiRAyoyZ2fqJsAU2Vk0rXM07rxMDwWiL7x4ty9Vx44ZJXLaVb89utbd72+rj+aPazNWNeEOFS3yYJFg4k04t\/o+BOoYKoBJxoIhGF1T7HGxk13DpICnJgWPo4ZyFJR8nzv3ALoWWlPvk6ZYCQXArqseilK5T6KhMQvrTMIDYgA1leYoXvYF+uZ6b7Y6SZcxKcyzYN1rM4sTC+1foNNhk03TBGiUVa5F5Kqrcc2l\/phl0HcUiUb52EtAzn4a7ixqYlRTW4HyaJeNoHrTjuolm8uGLETFvd93rPz2CLNiFjnv639deBQf96c3++kLdyhGhJ5Z4FGvHnsLD3u7kmyZa8vUP8KSpDlb9jkKJLrdBd8aSbOpyTBcphEij1CMUb55vYZ3k7sGR4uPQo4LwacNZrinxRZcIIbqj8VxOAeDmT5DY+qkDulNycMlPTrCJCccorSPaB2kp51xl85chutAjD\/tkOjRMV0w7e9\/34vawAh7REYIAgSjXkQ\/b5RFgLom+ejwawwXkwjGW4Q1A2PF9C+BcQ7NVRYjf4geyqAy\/tCcYZEoPwK8T8u0kvcZJBAbs1BpUQhkPw15+1SKECysxuFYboetJr4GdKNdcfrNmrs+pE1HWb8lRgQqr7pov5BGjkTnoRqjsKBTInrX3frxJMLEEoVuKfLGln6Cjz17C1WiV\/ApdO8T5M3pM4aIsAn9VFR4zf9\/WDGU5ZB8Bk\/8owEq8w","id":245989226934281}';
        $resArr = json_decode($param, true);
        echo "<pre>";
        print_r($resArr);

        $pubKey = 'MIIEMTCCAxmgAwIBAgIGAXUrc4b4MA0GCSqGSIb3DQEBCwUAMHYxCzAJBgNVBAYTAkNOMRAwDgYDVQQIDAdCZWlKaW5nMRAwDgYDVQQHDAdCZWlKaW5nMRcwFQYDVQQKDA5MYWthbGEgQ28uLEx0ZDEqMCgGA1UEAwwhTGFrYWxhIE9yZ2FuaXphdGlvbiBWYWxpZGF0aW9uIENBMB4XDTIwMTAxNTA4NDk1MloXDTMwMTAxMzA4NDk1MlowZTELMAkGA1UEBhMCQ04xEDAOBgNVBAgMB0JlaUppbmcxEDAOBgNVBAcMB0JlaUppbmcxFzAVBgNVBAoMDkxha2FsYSBDby4sTHRkMRkwFwYDVQQDDBBBUElHVy5MQUtBTEEuQ09NMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwAXZw9lupWcFXouCNhm0DQT47Zf4KOIRF8rqT8Ps3pYzT8odROJ8rq4P+lciGrg29czpqrRM22yQktFritvcM7JlE6jFbGH3rycnvGvhRYU/j1N9k0ozm8oVwmKX357/OtGzNivBECGSnU9LBkp4Nm9M1K4cOwEuZ0xsQEthZjQYF0mDpnlWmVJL5i1Lq834atN2qrb/mzMHBNtDJnqRV7rPL39lKpe7LJiitsC2JuW1UbWZZU1NNwA/rz2d83C+KD1DLJ0+sMYY2Q3TOQ4BPAowDEwOH7XAXrHM/0kRm+ZeIFlwevEGIQWmMt1Ogz+AW4Iq0slINc4wOINKvH9tHwIDAQABo4HVMIHSMIGSBgNVHSMEgYowgYeAFCnH4DkZPR6CZxRn/kIqVsModJHpoWekZTBjMQswCQYDVQQGEwJDTjEQMA4GA1UECAwHQmVpSmluZzEQMA4GA1UEBwwHQmVpSmluZzEXMBUGA1UECgwOTGFrYWxhIENvLixMdGQxFzAVBgNVBAMMDkxha2FsYSBSb290IENBggYBaiUALIowHQYDVR0OBBYEFIya0Yc4OSBer55JLyA0AYe9m8mTMAwGA1UdEwEB/wQCMAAwDgYDVR0PAQH/BAQDAgeAMA0GCSqGSIb3DQEBCwUAA4IBAQCBEwOlk3mXigNv94Drn3dcaY2ml/y+8yNpAIuUhuBE00WFoqEX5lOatFy5fzdXuC12lBVQ8SjSm3aH7k2X0eXqDzkOHiur2ZBRKmJ++J4TeenuSUOjSIbQK/DTvxaqFUjYwFSVCyizpy7wfU4wKt+jOuFb9LyULJ9lkM1dV9Kh7Lmd9+nlJYYuPEPULJkkVZqSALSiiJudXnTwlISjZTXEAkJpdIlMw+hvPTAkoG95B95M+OV/uLbItGK+qT4+RHWo8EbBDPQYo6J4QYHOxRlfMoGBMyrz6XDt7ELLmT7ld4aE02w6KQPfK3gqkLDT+/STozvaNmXzBJh7J6KqxJBH';
        $res = self::publicKeyDecrypt($resArr['data'], $pubKey);
        echo "<pre>";
        print_r($res);
        die();
        // return app('json')->success('请求成功');
    }

    /**
     * 公钥解密
     * @param $data
     * @return string
     */
    public static function publicKeyDecrypt($data, $pubKey)
    {
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true)
            . "\n-----END PUBLIC KEY-----";
        $crypto = '';
        foreach (str_split(base64_decode($data), 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $pubKey);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    /**
     * @desc 分账关系绑定 回调
     * @author ZhouTing
     * @date 2025-04-22 10:50
     */
    public function lklApplyBindNotify()
    {
        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '分账关系绑定申请回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($originalText, true);

            // {"optType":"ADD","applyId":956958237062774784,"merCupNo":"82210008699006U","retUrl":"https://sqfamily.lnkj6.com/api/notify/lklApplyBindNotify","entrustFileName":"合作协议","auditStatus":"1","merInnerNo":"5002025032128588892","receiverNo":"SR2024000069899","remark":"仅测试","auditStatusText":"审核通过","uploadAttachType":"SPLIT_ENTRUST_FILE","entrustFilePath":"MMS/20250325/165022-fafd706c212d4bcab580c36406f61699.pdf"}
            $info = LklModel::getInfo(['lkl_mer_cup_no' => $obj['merCupNo']]);
            if (!empty($info)) {
                $info->save(['lkl_mer_bind_status' => $obj['auditStatus']]);
            }
            // 通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账关系绑定申请回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 商户分账业务开通申请 回调
     * @author ZhouTing
     * @date 2025-04-22 14:03
     */
    public function lklApplyLedgerMerNotify()
    {
        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '商户分账业务开通回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($originalText, true);

            // {"applyId":959056782070833152,"merCupNo":"822100058122KVN","retUrl":"https://sqfamily.lnkj6.com/api/notify/lklApplyLedgerMerNotify","entrustFileName":"结算授权委托书","auditStatus":"1","merInnerNo":"4002025032958845205","remark":"审核通过","auditStatusText":"审核通过","uploadAttachType":"SPLIT_ENTRUST_FILE","entrustFilePath":"MMS/20250329/105027-4704fe3fb9004b4ba6873fadcfc559b1.pdf"}
            $info = LklModel::getInfo(['lkl_mer_cup_no' => $obj['merCupNo']]);
            if (!empty($info)) {
                $info->save(['lkl_mer_ledger_status' => $obj['auditStatus']]);
            }
            // 通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉商户分账业务开通回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 聚合主扫 支付成功回调
     * @author ZhouTing
     * @param 
     * @date 2025-04-22 14:23
     */
    public function lklPayNotify()
    {
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            // $headers = $request->getHeaders();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '拉卡拉支付成功回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($originalText, true);
            if ($obj['trade_status'] == 'SUCCESS') {
                //钱包类型 account_type 微信：WECHAT 支付宝：ALIPAY 银联：UQRCODEPAY 翼支付: BESTPAY 苏宁易付宝: SUNING  数字人民币-DCPAY
                // parse_str($obj['remark'], $remark);
                // $type = isset($remark['pay_type']) ? $remark['pay_type'] : 3;

                $out_trade_no = $obj['out_trade_no'];
                Log::info('拉卡拉支付成功回调' . var_export($obj, 1));
                try {
                    event('pay_success_' . $obj['remark'], ['order_sn' => $out_trade_no, 'data' => $obj]);
                } catch (\Exception $e) {
                    Log::info('拉卡拉支付成功回调失败:' . $e->getMessage() . $e->getFile() . $e->getLine());
                    return false;
                }
            }

            // 通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉支付回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 - 发货确认通知 回调
     * @author ZhouTing
     * @date 2025-04-22 14:25
     */
    public function lklSendcompleteNotify()
    {
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '拉卡拉发货确认回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($originalText, true);

            if ($obj['trade_state'] == 'SUCCESS') {
                // 替换更新发货后的流水号
                $out_trade_no = $obj['origin_out_trade_no'];
                /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
                $res = $storeOrderOfflineRepository->getWhere(['order_sn' => $out_trade_no]);
                if (!empty($res)) {
                    $res->lkl_log_no = $obj['log_no'] ?? '';
                    $res->save();
                    $params = [
                        'lkl_mer_cup_no' => $res['lkl_mer_cup_no'],
                        'lkl_log_no' => $obj['log_no'], // 用最新的流水号
                        'lkl_log_date' => $res['lkl_log_date'],
                    ];
                    // 可分账金额查询
                    $api = new \Lakala\LklApi();
                    $result = $api::lklQueryAmt($params);
                    if (!$result) {
                        record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉可分账金额查询异常: ' . $api->getErrorInfo(), 'queryAmt');
                    }
                    $can_separate_amt = $result['total_separate_amt'];
                    if ($can_separate_amt > 0) {
                        $this->lklSeparate($params, $can_separate_amt, $res);
                    }
                }
            }

            //通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉发货确认回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    // 拉卡拉分账参数拼接
    public function lklSeparate($param, $can_separate_amt, $res): void
    {
        // 平台抽取的费用
        $handling_fee = (float)bcmul($res->handling_fee, 100, 2);
        $param['can_separate_amt'] = $can_separate_amt;
        $param['recv_datas'] = [
            [
                'recv_merchant_no' => '123456', // TODO 拉卡拉分账接收方 后期需要修改
                'separate_value' => $handling_fee
            ],
            [
                'recv_no' => $param['lkl_mer_cup_no'],
                'separate_value' => $can_separate_amt - $handling_fee
            ]
        ];
        $api = new \Lakala\LklApi();
        $result = $api::lklSeparate($param);
        if (!$result) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉分账异常: ' . $api->getErrorInfo(), 'separate');
        }
    }
    /**
     * @desc 拉卡拉 - 订单分账 回调
     * @author ZhouTing
     * @date 2025-04-23 16:11
     */
    public function lklSeparateNotify()
    {
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            $request = $api->notiApi();
            // $headers = $request->getHeaders();
            $originalText = $request->getOriginalText();
            Db::name('third_notify')->insert(['title' => '拉卡拉订单分账回调', 'content' => $originalText, 'createtime' => time()]);

            // $originalText = '{"separate_no":"20250409770188013954770800","out_separate_no":"2025040919375840689521","cmd_type":"SEPARATE","log_no":"66231317811820","log_date":"20250409","cal_type":"0","separate_type":"1","separate_date":"20250409","finish_date":"20250409","total_amt":"997","status":"SUCCESS","final_status":"SUCCESS","actual_separate_amt":"997","total_fee_amt":"0","detail_datas":[{"recv_merchant_no":"","recv_no":"SR2024000069562","amt":"49"},{"recv_merchant_no":"82210008699006U","recv_no":"82210008699006U","amt":"948"}]}';
            $obj = json_decode($originalText, true);


            //通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉订单分账回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }
}

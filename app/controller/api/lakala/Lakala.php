<?php

namespace app\controller\api\lakala;

use app\common\model\system\merchant\MerchantEcLkl as LklModel;
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
        $content = $_POST;
        Db::name('third_notify')->insert(['title' => '商户进件回调1', 'content' => json_encode($content), 'createtime' => time()]);

        $data = file_get_contents("php://input");
        Db::name('third_notify')->insert(['title' => '商户进件回调2', 'content' => $data, 'createtime' => time()]);

//        $info = LklModel::getInfo(['lkl_ec_apply_id' => $obj['ecApplyId']]);
//        if (!empty($info)) {
//            $info->save(['lkl_mer_cup_status' => '']);
//        }

        return app('json')->success('请求成功');
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
                $out_trade_no = $obj['origin_out_trade_no'];
                //$obj['log_no']
            }

            //通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉发货确认回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }
}

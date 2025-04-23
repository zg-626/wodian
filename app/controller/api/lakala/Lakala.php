<?php

namespace app\controller\api\lakala;

use app\common\model\system\merchant\MerchantEcLkl as LklModel;
use crmeb\basic\BaseController;
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
            //1、更新电子合同签约状态
            $ecInfo = LklModel::where(['lkl_ec_apply_id' => $obj['ecApplyId']])->field('id')->find();
            if (!empty($ecInfo)) {
                $ecInfo->save(['lkl_ec_no' => $obj['ecNo'], 'lkl_ec_status' => $obj['ecStatus']]);
            }

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

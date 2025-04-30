<?php

namespace app\controller\api\lakala;

use app\common\model\system\merchant\MerchantEcLkl as LklModel;
use app\common\repositories\store\order\StoreOrderOfflineRepository;
use app\common\repositories\store\order\StoreOrderProfitsharingRepository;
use crmeb\basic\BaseController;
use DateTime;
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
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);

        Db::name('third_notify')->insert(['title' => '电子合同签约回调', 'content' => $param, 'createtime' => time()]);

        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            $obj = json_decode($param, true);
            // $request = $api->notiApi();
            // $headers = $request->getHeaders();
            // $originalText = $request->getOriginalText();
            // Db::name('third_notify')->insert(['title' => '电子合同签约回调', 'content' => $originalText, 'createtime' => time()]);

            // $obj = json_decode($param, true);

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
        // {"contractId":"202504252678700933","customerType":"TP_PERSONAL","customerNo":100137827,"customerName":"巴里*玖玖","customerAddress":"南城街道田心***************行街店)","externalCustomerNo":"8223020581208NV","phoneNo":"185****1122","licenseName":"巴****","identityNo":"513701********9777","identityExpireStart":"2015-01-24","identityExpireEnd":"2035-01-19","legalName":"仲*瑾","orgCode":"200028","userNo":20000101,"agentNo":20000101,"agencyNo":30000081,"termNos":"D9349032","activeNo":"689349032464","openTime":"2025-04-25 16:07:57","status":"SUCCESS","customerTag":"ORDINARY","coreTermIds":[]}
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);

        Db::name('third_notify')->insert(['title' => '商户进件回调', 'content' => $param, 'createtime' => time()]);

        // record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户进件回调: ' . $param, 'lkl');
        //解密公钥
        $pubKey = \Lakala\LklApi::$config['pubKey'];

        $res = self::publicKeyDecrypt($param['data'], $pubKey);
        $obj = json_decode($res, true);

        $info = LklModel::getInfo(['lkl_mer_cup_no' => $obj['customerNo']]);
        if (!empty($info)) {
            if ($obj['status'] == 'SUCCESS') {
                $data['lkl_mer_cus_no'] = $obj['externalCustomerNo'];
                $data['lkl_mer_term_no'] = $obj['termNos'];
            }
            $data['lkl_mer_cup_status'] = $obj['status'];
            $info->save($data);
        }
        return app('json')->success($res);
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
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);

        Db::name('third_notify')->insert(['title' => '分账关系绑定申请回调', 'content' => $param, 'createtime' => time()]);

        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            // $request = $api->notiApi();
            // $originalText = $request->getOriginalText();
            // Db::name('third_notify')->insert(['title' => '分账关系绑定申请回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($param, true);

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
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);

        Db::name('third_notify')->insert(['title' => '商户分账业务开通回调', 'content' => $param, 'createtime' => time()]);

        $config = new V2Configuration();
        $api = new V2LakalaNotifyApi($config);
        try {
            // $request = $api->notiApi();
            // $originalText = $request->getOriginalText();
            // Db::name('third_notify')->insert(['title' => '商户分账业务开通回调', 'content' => $originalText, 'createtime' => time()]);

            $obj = json_decode($param, true);

            // {"applyId":959056782070833152,"merCupNo":"822100058122KVN","retUrl":"https://sqfamily.lnkj6.com/api/notify/lklApplyLedgerMerNotify","entrustFileName":"结算授权委托书","auditStatus":"1","merInnerNo":"4002025032958845205","remark":"审核通过","auditStatusText":"审核通过","uploadAttachType":"SPLIT_ENTRUST_FILE","entrustFilePath":"MMS/20250329/105027-4704fe3fb9004b4ba6873fadcfc559b1.pdf"}
            $info = LklModel::getInfo(['lkl_mer_cup_no' => $obj['merCupNo']]);
            if (!empty($info)) {
                $info->save(['lkl_mer_ledger_status' => $obj['auditStatus']]);
            }
            // 通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户分账业务开通回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 聚合主扫 支付成功回调
     * @author ZhouTing
     * @date 2025-04-22 14:23
     */
    public function lklPayNotify()
    {
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);

        Db::name('third_notify')->insert(['title' => '拉卡拉支付回调', 'content' => $param, 'createtime' => time()]);
        // record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉支付回调: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            // $obj = json_encode($param, JSON_UNESCAPED_UNICODE);
            $obj = json_decode($param, true);
            if ($obj['trade_status'] == 'SUCCESS') {
                $out_trade_no = $obj['out_trade_no'];

                try {
                    event('pay_success_' . $obj['remark'], ['order_sn' => $out_trade_no, 'data' => $obj]);
                } catch (\Exception $e) {
                    Log::info('拉卡拉支付回调失败:' . $e->getMessage() . $e->getFile() . $e->getLine());
                    // return false;
                    $api->fail($e->getMessage());
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
        $param = input('');
        $obj = json_encode($param, JSON_UNESCAPED_UNICODE);
        $obj = json_decode($obj, true);
        Db::name('third_notify')->insert(['title' => '拉卡拉发货确认回调', 'content' => json_encode($param, JSON_UNESCAPED_UNICODE), 'createtime' => time()]);
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉发货确认回调: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            Log::info('拉卡拉发货确认回调更新:0');
            if ($obj['trade_state'] == 'SUCCESS') {
                Log::info('拉卡拉发货确认回调更新:1');
                // 替换更新发货后的流水号
                $out_trade_no = $obj['origin_out_trade_no'];
                /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
                $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
                $res = $storeOrderOfflineRepository->getWhere(['order_sn' => $out_trade_no]);
                if (!empty($res)) {
                    Log::info('拉卡拉发货确认回调更新:2');
                    $res->origin_log_no = $res->lkl_log_no ?? '';
                    $res->lkl_log_no = $obj['log_no'] ?? '';
                    $res->is_share = 1;
                    $res->save();
                    Log::info('拉卡拉发货确认回调更新:3');
                    // 同步更新订单分账表
                    /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
                    $storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
                    $models =$storeOrderProfitsharingRepository ->getWhere(['order_id' => $res['order_id']]);
                    $models->status = 2;// 可分账
                    $models->save();

                }
            }

            //通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉发货确认回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }


    /**
     * @desc 拉卡拉 - 订单分账 回调
     * @author ZhouTing
     * @date 2025-04-23 16:11
     */
    public function lklSeparateNotify()
    {
        $param = input('');
        $param = json_encode($param, JSON_UNESCAPED_UNICODE);
        Db::name('third_notify')->insert(['title' => '拉卡拉订单分账回调', 'content' => $param, 'createtime' => time()]);
        // record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉订单分账回调: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');
        $config = new Configuration();
        $api = new LakalaNotifyApi($config);
        try {
            // $request = $api->notiApi();
            // $headers = $request->getHeaders();
            // $originalText = $request->getOriginalText();
            // Db::name('third_notify')->insert(['title' => '拉卡拉订单分账回调', 'content' => $originalText, 'createtime' => time()]);

            // $originalText = '{"separate_no":"20250409770188013954770800","out_separate_no":"2025040919375840689521","cmd_type":"SEPARATE","log_no":"66231317811820","log_date":"20250409","cal_type":"0","separate_type":"1","separate_date":"20250409","finish_date":"20250409","total_amt":"997","status":"SUCCESS","final_status":"SUCCESS","actual_separate_amt":"997","total_fee_amt":"0","detail_datas":[{"recv_merchant_no":"","recv_no":"SR2024000069562","amt":"49"},{"recv_merchant_no":"82210008699006U","recv_no":"82210008699006U","amt":"948"}]}';
            $obj = json_decode($param, true);
            if ($obj['final_status'] == 'SUCCESS') {
                // 更新分账状态
                $out_trade_no = $obj['out_separate_no'];
                /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
                $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
                $res = $storeOrderOfflineRepository->getWhere(['order_sn' => $out_trade_no]);
                if (!empty($res)) {
                    $res->is_share = 1;
                    $res->save();

                    // 同步更新订单分账表
                    /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
                    $storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
                    $models =$storeOrderProfitsharingRepository ->getWhere(['order_id' => $res['order_id']]);
                    $models->status = 1;// 分账成功
                    $models->save();

                }
            }

            //通知拉卡拉，业务处理成功
            $api->success();
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉订单分账回调异常: ' . $e->getMessage(), 'lkl');
            $api->fail($e->getMessage());
        }
    }
}

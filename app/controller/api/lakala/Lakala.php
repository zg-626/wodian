<?php

namespace app\controller\api\lakala;

use crmeb\basic\BaseController;
use think\response\Json;
use Lakala\OpenAPISDK\V2\V2Configuration;
use Lakala\OpenAPISDK\V2\Api\V2LakalaNotifyApi;
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
            $ecInfo = Db::name('merchant_ec_lkl')->where(['lkl_ec_apply_id' => $obj['ecApplyId']])->find();
            if (!empty($ecInfo)) {
                Db::name('merchant_ec_lkl')->where('id', $ecInfo['id'])->update(['lkl_ec_no' => $obj['ecNo'], 'lkl_ec_status' => $obj['ecStatus']]);
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
}

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

namespace app\controller\api\article;

use app\common\dao\system\merchant\MerchantDao;
use app\common\repositories\store\CityAreaRepository;
use app\common\repositories\store\order\StoreOrderOfflineRepository;
use app\common\repositories\store\order\StoreOrderProfitsharingRepository;
use app\common\repositories\system\attachment\AttachmentRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserGroupRepository;
use app\common\repositories\user\UserRepository;
use app\common\repositories\user\UserVisitRepository;
use crmeb\jobs\OrderOfflineProfitsharingJob;
use crmeb\jobs\SendSmsJob;
use crmeb\services\QrcodeService;
use crmeb\services\SwooleTaskService;
use crmeb\services\WechatService;
use think\App;
use app\common\repositories\article\ArticleRepository as repository;
use crmeb\basic\BaseController;
use think\exception\ValidateException;
use think\facade\Queue;

class Article extends BaseController
{
    /**
     * @var repository
     */
    protected $repository;

    /**
     * StoreBrand constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * @return mixed
     * @author Qinii
     */
    public function lst($cid)
    {
        [$page, $limit] = $this->getPage();
        $where = ['status' => 1,'cid' => $cid];
        return app('json')->success($this->repository->search(0,$where, $page, $limit));
    }

    public function detail($id)
    {
        if (!$this->repository->merApiExists($id))
            return app('json')->fail('文章不存在');
        $data = $this->repository->getWith($id,['content']);
        if ($this->request->isLogin()) {
            $uid = $this->request->uid();
            $make = app()->make(UserVisitRepository::class);
            $count = $make->search(['uid' => $uid, 'type' => 'article'])->where('type_id', $id)->whereTime('create_time', '>', date('Y-m-d H:i:s', strtotime('- 300 seconds')))->count();
            if (!$count) {
                SwooleTaskService::visit(intval($uid), $id, 'article');
                $this->repository->incVisit($id);
            }
        }

        return app('json')->success($data);
    }

    public function list()
    {
        $where = ['status' => 1];
        return app('json')->success($this->repository->search(0,$where, 1, 9));
    }

    // 测试接口
    public function test()
    {
        /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
        /*$storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
        $ids =$storeOrderProfitsharingRepository ->getAutoOfflineProfitsharing();*/
        //print_r($ids);

        /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
        /*$storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
        $model = $storeOrderProfitsharingRepository->get(1);*/
        /*try {
            $storeOrderProfitsharingRepository->partnerProfitsharing($model);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }*/
        /*try {
            WechatService::create()->partnerPay()->profitsharingAddReceiver([
                'sub_mchid' => '1709305541', // 发起分账的子商户
                'account' => '1709305541', // 接收方商户号（你自己的服务商商户号）
                'name'      => '中仁商业商贸服务', // 必须与注册名称一致
            ]);

        } catch (\Exception $e) {
            return  $e->getMessage();

        }*/
        // 测试支付回调
        $data=array (
            'order_sn' => 'wxs174528612192293719',
            'appid' => 'wx4409eaedbd62b213',
            'attach' => 'user_order',
            'bank_type' => 'OTHERS',
            'cash_fee' => '1',
            'fee_type' => 'CNY',
            'is_subscribe' => 'N',
            'mch_id' => '1288093001',
            'nonce_str' => '6397efa100165',
            'openid' => 'oOdvCvjvCG0FnCwcMdDD_xIODRO0',
            'out_trade_no' => 'wxs174528612192293719',
            'result_code' => 'SUCCESS',
            'return_code' => 'SUCCESS',
            'sign' => '125C56DE030A461E45D421E44C88BC30',
            'time_end' => '20221213112118',
            'total_fee' => '1',
            'trade_type' => 'JSAPI',
            'transaction_id' => '4200001656202212131458556229',
        );
        /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository  **/

        $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
        try {
            $storeOrderOfflineRepository->paySuccess($data);
        } catch (\Exception $e) {
            return $e->getMessage().$e->getLine();
                }
        // 修改商户省市区
        /*try {
            // 获取需要更新的商户数据（只查询有城市ID的商户）
            $merchantDao = app()->make(MerchantDao::class);
            $merchants = $merchantDao->search(['city_id', '<>', 0])
                ->field('mer_id, province_id, city_id, district_id')
                ->select();

            // 提前加载所有需要的地域数据（省市区）
            $cityRepo = app()->make(CityAreaRepository::class);
            $areas = $cityRepo->search([])
                ->where('level', '<>', 4)
                ->column('name', 'id');  // 以ID为键的数组

            // 批量更新商户数据
            foreach ($merchants as $merchant) {
                $merchant->province = $areas[$merchant->province_id] ?? null;
                $merchant->city = $areas[$merchant->city_id] ?? null;
                $merchant->district = $areas[$merchant->district_id] ?? null;

                // 只保存有变化的字段
                $merchant->save();
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
            // 记录错误日志
            //\Log::error('更新商户省市区失败: ' . $e->getMessage());
            return false;
        }

        var_dump($merchant);exit();*/
        /*$merchant = app ()->make(MerchantDao::class);
        $merchant = $merchant->search(['mer_id' => 78])->field('mer_id,integral,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type')->find();

        // 如果没有业务员，则没有佣金
        //if ($merchant->salesman_id===0) return;
        // 查询业务员信息
        $salesman = app()->make(UserRepository::class)->get($merchant->salesman_id);
        // 查询业务员分组
        $commissionGroup = app()->make(UserGroupRepository::class)->get($salesman['group_id']);
        // 佣金比例
        $commission = $commissionGroup->extension;
        // 平台抽取的手续费
        $commission_rate = 20;

        // 用于发放的金额基数
        $money=bcmul(0.6,$commission_rate,2);// 根据让利金额的百分之60  再分配给其他人员
        // 业务员的佣金
        $extension_one = bcmul($commission/100, $money, 2);

        $userBillRepository = app()->make(UserBillRepository::class);
        $userBillRepository->incBill($merchant->salesman_id, 'brokerage', 'order_one', [
            'link_id' => 1,
            'status' => 1,
            'title' => '获得商务推广佣金',
            'number' => $extension_one,
            'mark' => '成功消费' . floatval(20) . '元,奖励商务推广佣金' . floatval($extension_one),
            'balance' => $salesman->coupon_amount + (int)$extension_one
        ]);

        $salesman->coupon_amount += (int)$extension_one;

        $salesman->save();
        // 业务员绑定的区域经理
        if(!$salesman->superior_uid){
            // 查询区域经理信息
            $superiorInfo = app()->make(UserRepository::class)->get($salesman->superior_id);
            // 查询区域经理分组
            $superiorGroup = app()->make(UserGroupRepository::class)->get($superiorInfo['group_id']);
            // 佣金比例
            $superior_commission = $superiorGroup->extension;

            // 业务经理的佣金
            $superior_extension = bcmul($superior_commission/100, $money, 2);

            $userBillRepository->incBill($merchant->salesman_id, 'superior_brokerage', 'order_one', [
                'link_id' => 1,
                'status' => 1,
                'title' => '获得经理推广佣金',
                'number' => $superior_extension,
                'mark' => '成功消费' . floatval(20) . '元,奖励经理推广佣金' . floatval($superior_extension),
                'balance' => $superiorInfo->coupon_amount + (int)$superior_extension
            ]);

            $superiorInfo->coupon_amount += (int)$superior_extension;
            $superiorInfo->save();

        }
        // 业务员的佣金
        $extension_one = bcmul($commission/100, $money, 2);*/
        //print_r($money);
        //print_r($extension_one);
        /*$shopId=77;
        $ratio=3.00;
        $siteUrl = rtrim(systemConfig('site_url'), '/');
        $codeUrl = $siteUrl .'/payPage'. '?target=eqcode'. '&shopId=' . $shopId. '&pvRatio=' . $ratio;//二维码链接
        $name = md5('shop' . $shopId . date('Ymd')) . '.jpg';
        $logoPath = 'https://liuniushop.oss-cn-shanghai.aliyuncs.com/def/97d0520240325162723113.png'; // Logo 图片路径
        $imageInfo = app()->make(QrcodeService::class)->getQRCodeLogoPath($codeUrl, $name,$logoPath);
        if (is_string($imageInfo)) throw new ValidateException('二维码生成失败');
        $imageInfo['dir'] = tidy_url($imageInfo['dir'], null, $siteUrl);
        $attachmentRepository = app()->make(AttachmentRepository::class);
        $attachmentRepository->create(systemConfig('upload_type') ?: 1, -2, $shopId, [
            'attachment_category_id' => 0,
            'attachment_name' => $imageInfo['name'],
            'attachment_src' => $imageInfo['dir']
        ]);
        $urlCode = $imageInfo['dir'];
        print_r($urlCode);*/
        /** @var CityAreaRepository $cityArea */
       /* $cityArea= app()->make(CityAreaRepository::class);
        print_r($cityArea->getAddressChildList());*/
        /*$MerchantDao = app()->make(MerchantDao::class); // MerchantDao
        $merchant = $MerchantDao->search(['mer_id' => 78])->field('mer_id,integral,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type')->find();

        // 如果没有业务员，则没有佣金
        if ($merchant->salesman_id===0) return;
        // 查询业务员信息
        $salesman = app()->make(UserRepository::class)->get($merchant->salesman_id);
        // 根据业务员分组查询佣金比例
        $commission = app()->make(UserGroupRepository::class)->get($salesman['group_id']);
        $commission = $commission->extension;
        echo "<pre>";
        print_r($commission);*/
        return app('json')->success('测试成功');
    }
}

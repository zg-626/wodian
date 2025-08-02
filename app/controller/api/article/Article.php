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
use app\common\model\meituan\MeituanOrder;
use app\common\model\store\order\StoreOrder;
use app\common\model\store\order\StoreOrderOffline;
use app\common\model\system\dividend\DividendDistributionLog;
use app\common\model\system\dividend\DividendPool;
use app\common\model\system\dividend\DividendPoolLog;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\model\user\UserBill;
use app\common\repositories\store\CityAreaRepository;
use app\common\repositories\store\order\StoreOrderOfflineRepository;
use app\common\repositories\store\order\StoreOrderProfitsharingRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\system\attachment\AttachmentRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserGroupRepository;
use app\common\repositories\user\UserMerchantRepository;
use app\common\repositories\user\UserRepository;
use app\common\repositories\user\UserVisitRepository;
use app\common\repositories\WaimaiRepositories;
use crmeb\jobs\OrderOfflineProfitsharingJob;
use crmeb\jobs\SendSmsJob;
use crmeb\services\OfflineMiniProgramService;
use crmeb\services\QrcodeService;
use crmeb\services\SwooleTaskService;
use crmeb\services\WechatService;
use Exception;
use think\App;
use app\common\repositories\article\ArticleRepository as repository;
use crmeb\basic\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
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
        //查找支付者openid

        /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
        /*$storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
        $order = $storeOrderOfflineRepository->get(1099);
        $res= $storeOrderOfflineRepository->virtualDelivery($order);
        print_r($res);*/
        /*$storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
        $res = $storeOrderOfflineRepository->getWhere(['order_sn' => 'wxs174574285800550945']);
        if (!empty($res)) {

            try {
                $res->lkl_log_no = '66201573773646';
                $res->save();
                $date = substr($res['lkl_log_date'], 0, 8);

                $params = [
                    'lkl_mer_cup_no' => $res['lkl_mer_cup_no'],
                    'lkl_log_no' => '66201573773646', // 用最新的流水号
                    'lkl_log_date' => $date,
                ];
                // 可分账金额查询
                $api = new \Lakala\LklApi();
                $result = $api::lklQueryAmt($params);
                if (!$result) {
                    record_log('时间: ' . date('Y-m-d H:i:s') . ', 拉卡拉可分账金额查询异常: ' . $api->getErrorInfo(), 'queryAmt');
                }
            } catch (\Exception $e) {
                print_r($e->getMessage().$e->getLine());
            }

            /*if ($can_separate_amt > 0) {
                $this->lklSeparate($params, $can_separate_amt, $res);
            }*/
        //}*/
        /** @var StoreOrderOfflineRepository $storeOrderOfflineRepository */
//        $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
//        $order = $storeOrderOfflineRepository->get(1256);
//        $merchantRepository = app()->make(MerchantRepository::class);
//        $merchant = $merchantRepository->get(469);
        $userRepository = app()->make(UserRepository::class);
//        $userGroupRepository = app()->make(UserGroupRepository::class);
//        $userBillRepository = app()->make(UserBillRepository::class);
//
//        // 如果没有上级，则没有佣金
//        if ($merchant->salesman_id === 0 || $order['commission_rate'] < 0) return;
//
//        // 计算订单佣金基数
//        $commission_rate = (string)$order['commission_rate']/100;
//        $commission_money = bcmul($order['pay_price'], $commission_rate, 2);
//        // 用于发放的金额基数
//        $money = bcmul(0.6, $commission_money, 2);
//
//        // 获取商家绑定的上级信息


//
//        // 如果商家上级是省级代理商，终止发放佣金
//        //if ($salesman->group_id === self::USER_GROUP['AGENT_3']) return;
//
//        // 实时获取上级分组信息及比例
//        $salesmanGroup = $userGroupRepository->get($salesman['group_id']);
//
//        // 处理上级佣金
//        $extension_one = bcmul($salesmanGroup->extension/100, $money, 3);
//        var_dump($commission_rate);
//        var_dump($commission_money);
//        var_dump($salesmanGroup->extension/100);
//        var_dump($money);
//        var_dump($extension_one);
        try {
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            //$order = StoreOrderOffline::where(['order_id' => 3569, 'paid' => 1])->find();
            /*if($order->deduction > 0){
                $totalPrice = $order->total_price;
                $feeRate = $order->commission_rate; // 手续费率

                // 平台手续费(根据总金额)
                $platformFee = $order->handling_fee;
                // 第三方实际手续费
                $thirdPlatformFee = round((float)$order->pay_price * (float)$feeRate / 100,2);

                // 用户实际支付金额（不能为负数）
                $actualPayment = max($totalPrice - $order->deduction, 0);

                // 计算平台补贴
                if ($actualPayment > 0) {
                    // 情况1：正常抵扣（实际支付 > 0）
                    // 补贴 = 抵扣券金额 - (平台手续费 - 第三方实际手续费)
                    // 即：补贴商家抵扣券金额，但扣除平台多承担的手续费部分
                    $subsidyAdjustment = $platformFee - $thirdPlatformFee; // 平台多承担的手续费
                    $subsidy = max($order->deduction - $subsidyAdjustment, 0);
                } else {
                    // 情况2：零元购（实际支付 = 0）
                    // 补贴 = 抵扣券金额 - 手续费（确保手续费被覆盖）
                    $subsidy = max($order->deduction - $platformFee, 0);
                }

                echo "补贴: ". $subsidy ."<br>";
            }*/
            /*$handling_fee = floatval($order->handling_fee);
            $total_amount = bcmul((string)$handling_fee, "0.4", 2);
            // 记录本次分红池,手续费的40%
            try {
                $poolInfo = Db::name('dividend_pool')->where('city_id', $order->city_id)->order('id', 'desc')->find();
                Log::info('查询分红池');
                if (!$poolInfo) {
                    Log::info('创建分红池' . $order->city);
                    // 第一次创建分红池记录
                    Db::name('dividend_pool')->insert([
                        'total_amount' => $total_amount,
                        'available_amount' => $total_amount,
                        'initial_threshold' => $total_amount,
                        'distributed_amount' => 0,
                        'city_id' => $order->city_id,
                        'city' => $order->city,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    Log::info('更新分红池' . $poolInfo['id']);
                    // 更新现有分红池
                    Db::name('dividend_pool')->where('id', $poolInfo['id'])->update([
                        'total_amount' => Db::raw('total_amount + ' . $total_amount),
                        'available_amount' => Db::raw('available_amount + ' . $total_amount),
                        'initial_threshold' => Db::raw('initial_threshold + ' . $total_amount),
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
                }

                // 分红池流水表
                Db::name('dividend_pool_log')->insert([
                    'order_id' => $order->order_id,
                    'amount' => $total_amount,
                    'handling_fee' => $handling_fee,
                    'mer_id' => $order->mer_id,
                    'uid' => $order->uid,
                    'city' => $order->city,
                    'city_id' => $order->city_id,
                    'create_time' => date('Y-m-d H:i:s'),
                    'remark' => '订单分红入池'
                ]);
            } catch (\Exception $e) {
                Log::info('查询分红池失败' . $order->order_id . $e->getMessage().$e->getLine());
            }*/
            /*foreach ($order as $item) {
                // 查询订单的城市信息
                $merchant = Merchant::where('mer_id', $item->mer_id)->find();
                //print_r($merchant);exit();
                // 同步订单的城市信息
                $item->city_id = $merchant['city_id'];
                $item->city = $merchant['city'];
                $item->province_id = $merchant['province_id'];
                $item->province = $merchant['province'];
                $item->save();
            }*/
            /*$date = substr($order['lkl_log_date'], 0, 8);
            $time = substr($order['lkl_log_date'], 8, 6);
            $param['tranDate'] = date('Ymd');
            $param['tranTime'] = date('his');
            $param['txnAmt'] = $order['pay_price'];
            $param['lkl_log_no'] = $order['lkl_log_no'];
            $param['logdat'] = $date;
            $param['sendMerId'] = '822473052110FF4';
            $param['sendTermId'] = 'L9242635';
            $param['revcData'] = [
                [
                    'revcMerId' => '822473052110FF4',
                    'revcTermId' => 'L9242635',
                    'ledgerPercent' => 100
                ]
            ];
            $api = new \Lakala\LklApi();
            $result = $api::orderSettle($param);
            print_r($result);exit();*/
            // 测试美团退款
            /** @var WaimaiRepositories $repository */
            $repository = app()->make(WaimaiRepositories::class);
            //$store_order = StoreOrder::where('trade_no', 1929484805910286360)->find();
            //$result = $repository->refundLogic('1929484805910286360', $store_order->pay_price,$store_order->lkl_log_no);
            //print_r($result);exit();
            //print_r($result);
            //$user = app()->make(UserRepository::class)->get($order['uid']);
//            if($order->deduction > 0){
//                /** @var MerchantRepository $merchantRepository */
//                $merchantRepository=app()->make(MerchantRepository::class);
//                $merchantRepository->addOlllineMoney($order->mer_id, 'order', $order->order_id, $order->deduction);
//            }
            // 更新用户支付时间
            /** @var UserMerchantRepository $userMerchantRepository */
            //$userMerchantRepository = app()->make(UserMerchantRepository::class);
            //$userMerchantRepository->updatePayTime($order->uid, $order->mer_id, $order->handling_fee,true,$order->order_id);
            /** @var StoreOrderRepository $storeOrderRepository */
            $storeOrderRepository = app()->make(StoreOrderRepository::class);
            //$storeOrderRepository->addCommission($order->mer_id,$order);
            /** @var MerchantRepository $merchantRepository */
            //$merchantRepository=app()->make(MerchantRepository::class);
            // 如果用户使用了抵扣券，给商户增加余额，用于平台补贴
            /*if($order->deduction > 0){

                $merchantRepository->addOlllineMoney($order->mer_id, 'order', $order->order_id, $order->deduction_money);
            }*/
            $order = $storeOrderOfflineRepository->getWhere(['order_id' => [4801]]);
            $storeOrderOfflineRepository->computeds($order);
            //$storeOrderOfflineRepository->red($order);
            //$info=$storeOrderOfflineRepository->paySuccess($order);
            //$storeOrderOfflineRepository->virtualDelivery($order);
            /** @var WaimaiRepositories $repository */
            /*$params = $this->request->params([
                'accessKey',
                'content',
            ]);
            $repository = app()->make(WaimaiRepositories::class);
            $result = $repository->create($params);*/

        } catch (Exception $e) {
            print_r($e->getMessage().$e->getLine());
        }
        return app('json')->success('测试成功');
    }

    // 订单结算
    public function orderSettle()
    {
        try {
            $storeOrderOfflineRepository = app()->make(StoreOrderOfflineRepository::class);
            $order = $storeOrderOfflineRepository->getWhere(['order_id' => [1814]]);
            $date = substr($order['lkl_log_date'], 0, 8);
            $time = substr($order['lkl_log_date'], 8, 6);
            $param['tranDate'] = date('Ymd');
            $param['tranTime'] = date('his');
            $param['txnAmt'] = $order['pay_price'];
            $param['lkl_log_no'] = $order['lkl_log_no'];
            $param['logdat'] = $date;
            $param['sendMerId'] = '822473052110FF4';
            $param['sendTermId'] = 'L9242635';
            $param['revcData'] = [
                [
                    'revcMerId' => '822473052110FF4',
                    'revcTermId' => 'L9242635',
                    'ledgerPercent' => 100
                ]
            ];
            $api = new \Lakala\LklApi();
            $result = $api::orderSettle($param);
            return app('json')->success($result);


        } catch (Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    // 结算结果查询
    public function orderSettleQuery()
    {
        try {
            $param['queryId'] = time();
            $param['ledgerTranSid'] = $this->request->param('ledgerTranSid');

            $api = new \Lakala\LklApi();
            $result = $api::orderSettleQuery($param);
            return app('json')->success($result);


        } catch (Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    // 退款逻辑
    public function refundLogic()
    {
        $order = StoreOrderOffline::where(['order_id' => 2668, 'paid' => 1])->select();

        foreach ($order as $item) {
            // 查询订单的城市信息
            $merchant = Merchant::where('mer_id', $item->mer_id)->find();

            // 拉卡拉退款参数
            $params = [
                'order_sn' => $item['order_sn'].'616',
                'refund_amount' => $item['pay_price'],
                'origin_log_no' => $item['origin_log_no'],
                'merchant_no' => $merchant['merchant_no'],
                'term_nos' => $merchant['term_nos'],
                'trans_type' => 51,
            ];
            $api = new \Lakala\LklApi();
            $result = $api::lklRefund($params);
            if (!$result) {
                record_log('拉卡拉退款异常: ' . $api->getErrorInfo(), 'lakl_refund_error');
                //return app('json')->fail($api->getErrorInfo());
            }

        }

        return true;

    }

    // 计算订单剩下的佣金
    public function computeCommission()
    {
        try {
            // 某个城市的订单数据，平台佣金=handling_fee*0.6
            $orders = StoreOrderOffline::where(['paid' => 1, 'city_id' => 20188])->where('pay_price', '>', 0)->where('handling_fee', '>', 0)->where('create_time', '>=', '2025-05-01 00:00:00')
                ->field('order_id, handling_fee, create_time,pay_price')
                ->select();

            $monthlyEarnings = [];
            $totalEarnings = 0;

            foreach ($orders as $item) {
                // 计算平台收益，保留两位小数
                $platformRevenue = round($item['handling_fee'] * 0.6, 2);

                // 获取该订单关联的佣金支出记录，添加 category 条件
                $userBill = UserBill::where(['link_id' => $item['order_id']])
                    ->whereIn('category', ['brokerage'])
                    ->field('sum(number) as number')
                    ->select()
                    ->toArray();

                // 计算总支出，保留两位小数
                $totalExpense = round($userBill[0]['number'], 2);

                // 计算该订单的实际收益，保留两位小数
                $actualRevenue = round($platformRevenue - $totalExpense, 2);

                // 获取订单创建的月份
                $month = date('Y-m', strtotime($item['create_time']));

                // 累加每月收益
                if (!isset($monthlyEarnings[$month])) {
                    $monthlyEarnings[$month] = 0;
                }
                $monthlyEarnings[$month] = round($monthlyEarnings[$month] + $actualRevenue, 2);

                // 累加总收益，保留两位小数
                $totalEarnings = round($totalEarnings + $actualRevenue, 2);
            }

            return app('json')->success([
                'monthly_earnings' => $monthlyEarnings,
                'total_earnings' => $totalEarnings
            ]);
        } catch (Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    // 计算分红数据
    public function computeDividend($startTime,$endTime,$city_id)
    {
        $where = [];
        if($city_id !==''){
            $where = ['city_id'=>$city_id];
        }

        $orders = StoreOrderOffline::where(['paid' => 1])->where($where)->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->field('sum(total_price) as total_price,sum(handling_fee) as handling_fee,sum(pay_price) as pay_price')
            ->select()->toArray();

        // 查询所有分红池数据
        $list = DividendPool::field('sum(total_amount) as total_amount,sum(distributed_amount) as distributed_amount,sum(available_amount) as available_amount')
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->select()
            ->toArray();

        // 查询流水表数据
        $log = DividendPoolLog::field('sum(amount) as amount,sum(handling_fee) as handling_fee')
            ->where('pm', 1)
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->select()
            ->toArray();

        // 查询流水表数据,抵用券支出
        $logs = DividendPoolLog::field('sum(amount) as amount,sum(handling_fee) as handling_fee')
            ->where('pm', 0)
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->select()
            ->toArray();

        // 用户分红数据
        $user = DividendDistributionLog::field('sum(bonus_amount) as bonus_amount')
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->select()
            ->toArray();

        // 获取佣金支出记录
        $userBill = UserBill::where(['mer_id' => 0])
            ->whereIn('category', ['brokerage'])
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->field('sum(number) as number')
            ->select()
            ->toArray();

        // 获取抵用券支出记录
        $userBills = UserBill::where(['mer_id' => 0])
            ->whereIn('category', ['coupon_amount'])
            ->whereIn('type', ['order_one', 'order_two'])
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->field('sum(number) as number')
            ->select()
            ->toArray();

        // 获取平台补贴支出记录
        $userBillss = UserBill::where(['uid' => 0])
            ->whereIn('category', ['mer_lock_money'])
            ->whereIn('type', ['order'])
            ->where('create_time', '>=', $startTime)
            ->where('create_time', '<', $endTime)
            ->field('sum(number) as number')
            ->select()
            ->toArray();

        // 美团订单
        $meituan = MeituanOrder::where(['pay_status' => 1, 'refund_status' => 0])
            ->where('create_time', '<', $endTime)
            ->field('sum(pay_price) as pay_price,sum(trade_amount) as trade_amount')
            ->select()
            ->toArray();

        $handling_fee=$orders[0]['handling_fee'];

        return app('json')->success([
            '平台总流水' => $orders[0]['total_price'],
            '平台实际流水' => $orders[0]['pay_price'],
            '平台总手续费' => $handling_fee,
            '平台维护费（总手续费x60%）' => round($handling_fee * 0.6, 2),
            '平台总分红池（总手续费x40%）' => round($handling_fee * 0.4, 2),
            //'平台总分红池（累计分红池）' => $list[0]['total_amount'],
            //'分红池剩余金额' => $list[0]['available_amount'],
            '分红池剩余金额' => round($handling_fee * 0.4, 2)-$user[0]['bonus_amount'],
            //'平台总分红池（根据订单流水统计）' => $log[0]['amount'],
            '已分红金额(根据分红流水统计)' => $user[0]['bonus_amount'],
            //'平台总分红池（40%）' => round($log[0]['amount'] * 0.4, 2),
            '平台发放佣金' => $userBill[0]['number'],
            '推广抵用券' => $userBills[0]['number'],
            //'平台补贴' => $userBillss[0]['number'],
            '平台剩余（平台维护费-平台发放佣金）' =>bcsub(round($handling_fee * 0.6, 2), $userBill[0]['number'], 2),
            '美团总金额'=>$meituan[0]['trade_amount'],
            '美团实际支付金额'=>$meituan[0]['pay_price'],
            '美团使用抵用券'=>bcsub($meituan[0]['trade_amount'],$meituan[0]['pay_price'],2),
            '后台赠送抵用券'=>$logs[0]['amount'],
        ]);

    }

    // 根据订单更新商户累计收款
    public function updateMerchantTotalPrice()
    {
        $orders = StoreOrderOffline::where(['paid' => 1])->select()->toArray();

        // 先按商户ID分组汇总金额
        $merchantTotals = [];
        foreach ($orders as $item) {
            $merchant_id = $item['mer_id'];
            if (!isset($merchantTotals[$merchant_id])) {
                $merchantTotals[$merchant_id] = 0;
            }
            $merchantTotals[$merchant_id] += $item['pay_price'];
        }

        // 批量更新商户累计金额
        foreach ($merchantTotals as $merchant_id => $total) {
            (new \app\common\model\system\merchant\Merchant)
                ->where(['mer_id' => $merchant_id])
                ->update(['grand_money' => $total]);
        }

        return app('json')->success();

    }

    /**
     * TODO 生成线下支付订单
     * @param $money
     * @param $mer_id
     * @param StoreOrderOfflineRepository $storeOrderOfflineRepository
     * @author esc
     * @day 2025/03/07
     */
    public function enter($money,$mer_id, StoreOrderOfflineRepository $storeOrderOfflineRepository)
    {
        $params = $this->request->params(['uid','user_deduction',['commission_rate',0]]);
        if ($money<0)
            return app('json')->fail('金额不能小于0');
        if(!$mer_id)
            return app('json')->fail('缺少商户id');
        $params['is_app'] = $this->request->isApp();
        $params['pay_type'] = 'manual';
        $params['to_uid'] = 0;
        return $storeOrderOfflineRepository->enter($money,$mer_id,$params);
    }

    // 根据佣金记录处理退积分，退抵用券，退佣金
    public function backIntegral()
    {
        $params = $this->request->params(['order_id']);
        if(!$params['order_id'])
            return app('json')->fail('缺少订单id');

        Db::startTrans();
        try {
            $userBills = UserBill::where(['link_id' => $params['order_id']])->select();
            if($userBills->isEmpty())
                return app('json')->fail('记录不存在');

            foreach ($userBills as $userBill) {
                // 积分
                if($userBill['category'] == 'integral' && $userBill['uid'] > 0){
                    $user = User::where(['uid' => $userBill['uid']])->find();
                    $user->integral = bcsub($user->integral,$userBill['number'],2);
                    $user->save();
                }
                // 商家积分
                if($userBill['category'] == 'integral' && $userBill['mer_id'] > 0){
                    $merchant = Merchant::where(['mer_id' => $userBill['mer_id']])->find();
                    $merchant->integral = bcsub($merchant->integral,$userBill['number'],2);
                    $merchant->save();
                }
                // 商家积分2
                if($userBill['category'] == 'mer_integral' && $userBill['mer_id'] > 0){
                    $merchant = Merchant::where(['mer_id' => $userBill['mer_id']])->find();
                    $merchant->integral = bcsub($merchant->integral,$userBill['number'],2);
                    $merchant->save();
                }
                // 抵用券
                if($userBill['title'] == '获得推广抵用券'){
                    $user = User::where(['uid' => $userBill['uid']])->find();
                    $user->coupon_amount = bcsub($user->coupon_amount,$userBill['number'],2);
                    $user->save();
                }
                if($userBill['title'] == '线下消费使用抵用券'){
                    $user = User::where(['uid' => $userBill['uid']])->find();
                    $user->coupon_amount = bcadd($user->coupon_amount,$userBill['number'],2);
                    $user->save();
                }
                // 佣金
                if($userBill['category'] == 'brokerage'){
                    $user = User::where(['uid' => $userBill['uid']])->find();
                    $user->brokerage_price = bcsub($user->brokerage_price,$userBill['number'],2);
                    $user->save();
                }
                // 处理商家锁客
                if($userBill['category'] == 'mer_lock_money'){
                    $merchant = Merchant::where(['mer_id' => $userBill['mer_id']])->find();
                    $merchant->mer_money = bcsub($merchant->mer_money,$userBill['number'],2);
                    $merchant->save();
                }
            }

            // 所有记录改为已失效
            UserBill::where(['link_id' => $params['order_id']])->update(['status' => -1]);
            Db::commit();
            return app('json')->success('退款成功');
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('退款失败: '.$e->getMessage());
            return app('json')->fail('退款失败');
        }
    }
}

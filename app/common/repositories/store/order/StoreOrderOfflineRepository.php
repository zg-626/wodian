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


namespace app\common\repositories\store\order;


use app\common\dao\store\order\StoreOrderOfflineDao;
use app\common\dao\system\merchant\MerchantDao;
use app\common\dao\user\LabelRuleDao;
use app\common\model\store\order\StoreOrderOffline;
use app\common\model\user\User;
use app\common\repositories\BaseRepository;
use app\common\repositories\store\coupon\StoreCouponUserRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\system\groupData\GroupDataRepository;
use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\MemberinterestsRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserMerchantRepository;
use app\common\repositories\user\UserRepository;
use app\common\repositories\wechat\WechatUserRepository;
use crmeb\jobs\OrderProfitsharingJob;
use crmeb\jobs\SendSmsJob;
use crmeb\services\OfflinePayService;
use crmeb\services\PayService;
use crmeb\services\SwooleTaskService;
use FormBuilder\Factory\Elm;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;
use think\Model;

/**
 * Class LabelRuleRepository
 * @package app\common\repositories\user
 * @author xaboy
 * @day 2020/10/20
 * @mixin StoreOrderOfflineDao
 */
class StoreOrderOfflineRepository extends BaseRepository
{

    // 线下门店支付
    const TYPE_SVIP = 'O-';

    /**
     * LabelRuleRepository constructor.
     * @param StoreOrderOfflineDao $dao
     */
    public function __construct(StoreOrderOfflineDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, $page, $limit)
    {
        $query = $this->dao->search($where);
        $count = $query->count();
        $list = $query->with([
            'user' => function($query){
                $query->field('uid,nickname,avatar,phone,is_svip,svip_endtime');
            },
            'merchant' => function ($query) {
                $query->field('mer_id,mer_name,mer_state,mer_avatar,is_trader,type_id')->with(['type_name']);
            }
        ])->order('create_time DESC')->page($page, $limit)->select()->toArray();
        return compact('count', 'list');
    }

    /**
     * @param $data
     * @return mixed
     * @author xaboy
     * @day 2020/10/21
     */
    public function add($money,$mer_id, $user, $params)
    {
        $wechatUserRepository = app()->make(WechatUserRepository::class);
        $openId = $wechatUserRepository->idByRoutineId($user['wechat_user_id']);
        if (!$openId)
            throw new ValidateException('请关联微信小程序!');
        $total_price=$money;

        $handling_fee=0;

        /*** @var MerchantDao $merchant */
        $merchant = app()->make(MerchantDao::class);

        $merchant = $merchant->search(['mer_id' => $mer_id])->field('mer_id,commission_rate,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type,sub_mchid')->find();

        // 判断有没有申请子商户
        if ($merchant['sub_mchid'] == 0) {
            throw new ValidateException('该商家未申请子商户，无法下单');
        }

        if($merchant['commission_rate']==0){
            throw new ValidateException('该商家未设置积分比例');
        }

        $rate=0;
        // 计算平台手续费
        if(($money > 0) && $merchant['commission_rate'] > 0) {
            $rate = $merchant['commission_rate'] /100;
            $handling_fee = bcmul($money,$rate, 2);
        }

        //积分配置
        $sysIntegralConfig = systemConfig(['integral_money', 'integral_status', 'integral_order_rate']);

        //$svip_status = $user->is_svip > 0 && systemConfig('svip_switch_status') == '1';

        $pay_price = $money;

        //$svip_integral_rate = $svip_status ? app()->make(MemberinterestsRepository::class)->getSvipInterestVal(MemberinterestsRepository::HAS_TYPE_PAY) : 0;

        //计算赠送积分, 只有普通商品赠送积分
        //$giveIntegralFlag = $sysIntegralConfig['integral_status'] && $sysIntegralConfig['integral_order_rate'] > 0;
        $giveIntegralFlag = 1;

        $total_give_integral = 0;

        //$order_total_give_integral = 0;
        if ($pay_price > 0 && $rate) {
            $total_give_integral = floor(bcmul($pay_price, $rate, 0));
        }

        // 抵扣金额
        if(isset($params['user_deduction']) && $params['user_deduction'] > 0){
            // 计算抵扣后的抵扣金
            $user_coupon_amount = $user->coupon_amount;
            $deduction_money = bcsub($user_coupon_amount, $params['user_deduction'], 0);
            $user->coupon_amount = $deduction_money;
            $user->save();

        }

        //$order_total_give_integral = bcadd($total_give_integral, $order_total_give_integral, 0);

        $isSelfBuy = $user->is_promoter && systemConfig('extension_self') ? 1 : 0;
        if ($isSelfBuy) {
            $spreadUser = $user;
            $topUser = $user->valid_spread;
        } else {
            $spreadUser = $user->valid_spread;
            $topUser = $user->valid_top;
        }

        $spreadUid = $spreadUser->uid ?? 0;
        $topUid = $topUser->uid ?? 0;
        $extension_one=0;
        $extension_two=0;
        // 推广比例
        $extension_one_rate = systemConfig('extension_one_rate')?:0.03;
        $extension_two_rate = systemConfig('extension_two_rate')?:0.02;

        if ($spreadUid) {
            $extension_one = $money > 0 ? bcmul($money, $extension_one_rate, 2) : 0;
        }
        if ($topUid) {
            $extension_two = $money > 0 ? bcmul($money, $extension_two_rate, 2) : 0;
        }

        $order_sn = $this->getNewOrderId(StoreOrderRepository::TYPE_SN_USER_ORDER);
        $data = [
            'title'     => '线下门店支付',
            'link_id'   => 0,
            'order_sn'  => $order_sn,
            'pay_price' => $money,
            'order_info' => 0,
            'uid'        => $user->uid,
            'order_type' => self::TYPE_SVIP,
            'pay_type'   =>  $params['pay_type'],
            'commission_rate'=>$merchant['commission_rate'],
            'handling_fee' => $handling_fee,
            'status'     => 1,
            'mer_id'     => $mer_id,
            'give_integral' => $total_give_integral,
            'other'     => 0,
            'spread_uid' => $spreadUid,
            'top_uid' => $topUid,
            'is_selfbuy' => $isSelfBuy,
            'extension_one' => $extension_one,
            'extension_two' => $extension_two,
            'total_price' => $total_price,
            'deduction' => $params['user_deduction']?: 0,
            'deduction_money' => $params['user_deduction']?: 0,
            'to_uid'=>$params['to_uid']?:0
        ];

        // 微信服务商支付
        /*$body = [
            'out_trade_no' => $order_sn,
            'pay_price' => $money,
            'attach' => 'offline_order',
            'sub_mchid' => $merchant['sub_mchid'],
            'description' => '线下商品消费',
            'body' =>'线下门店支付'
        ];


        if ($params['return_url'] && $type === 'alipay') $body['return_url'] = $params['return_url'];*/
        $type = $params['pay_type'];
        if (in_array($type, ['weixin', 'alipay','routine'], true) && $params['is_app']) {
            $type .= 'App';
        }
        $info = $this->dao->create($data);
        // 拉卡拉支付参数
        $params = [
            'order_no' => $order_sn,
            'total_amount' => $money,
            'remark' => 'offline_order',
            'merchant_no' => $merchant['merchant_no'],
            'openid' => $openId,
            'goods_id' => '1',
        ];

        if ($money){
//            try {
//                //$service = new PayService($type,$body, 'offline_order');
//
//                /*$service = new OfflinePayService($type, $body);
//                $config = $service->pay($user);
//                return app('json')->status($type, $config + ['order_id' => $info->order_id]);*/
//                // TODO 测试身份佣金，直接支付成功
//                //$this->paySuccess($data);
//                $api = new \Lakala\LklApi();
//                $result = $api::lklPreorder($params);
//                return app('json')->status('success', $result + ['order_id' => $info->order_id]);
//            } catch (\Exception $e) {
//                return app('json')->fail('error', $e->getMessage(), ['order_id' => $info->order_id]);
//            }
            $api = new \Lakala\LklApi();
            $result = $api::lklPreorder($params);
            if (!$result) {
                return app('json')->fail($api->getErrorInfo());
            }
            $config=[
                'config' => $result
            ];
            return app('json')->status($type, $config + ['order_id' => $info->order_id]);
        } else {
            $this->paySuccess($data);
            return app('json')->status('success', ['order_id' => $info->order_id]);
        }
    }

    /**
     * @return string
     * @author xaboy
     * @day 2020/6/9
     */
    public function getNewOrderId($type)
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = number_format((floatval($msec) + floatval($sec)) * 1000, 0, '', '');
        $orderId = $type . $msectime . mt_rand(10000, max(intval($msec * 10000) + 10000, 98369));
        return $orderId;
    }

    public function paySuccess($data)
    {
        /*
          array (
            'order_sn' => 'wxs167090166498470921',
            'data' =>
            EasyWeChat\Support\Collection::__set_state(array(
               'items' =>
              array (
                'appid' => 'wx4409eaedbd62b213',
                'attach' => 'user_order',
                'bank_type' => 'OTHERS',
                'cash_fee' => '1',
                'fee_type' => 'CNY',
                'is_subscribe' => 'N',
                'mch_id' => '1288093001',
                'nonce_str' => '6397efa100165',
                'openid' => 'oOdvCvjvCG0FnCwcMdDD_xIODRO0',
                'out_trade_no' => 'wxs167090166498470921',
                'result_code' => 'SUCCESS',
                'return_code' => 'SUCCESS',
                'sign' => '125C56DE030A461E45D421E44C88BC30',
                'time_end' => '20221213112118',
                'total_fee' => '1',
                'trade_type' => 'JSAPI',
                'transaction_id' => '4200001656202212131458556229',
              ),
        )),
         */
        $res = $this->dao->getWhere(['order_sn' => $data['order_sn']]);
        $type = explode('-',$res['order_type'])[0].'-';
        if ($type == self::TYPE_SVIP) {
            return Db::transaction(function () use($data, $res) {
                $res->paid = 1;
                $res->transaction_id = $data['data']['transaction_id']??'';
                $res->pay_time = date('y_m-d H:i:s', time());
                $res->save();
                $order = $res;
                // 商户增加余额
                //app()->make(MerchantRepository::class)->addLockMoney($order->mer_id, 'order', $order->order_id, $order->pay_price);

                // 赠送积分
                $this->giveIntegral($order);

                // 赠送商户积分
                //$this->giveMerIntegral($order->mer_id,$order);
                app()->make(MerchantRepository::class)->addMerIntegral($order->mer_id, 'lock', $order->order_id, $order->give_integral);

                // 所有身份赠送佣金
                /** @var StoreOrderRepository $storeOrderRepository */
                $storeOrderRepository = app()->make(StoreOrderRepository::class);
                $storeOrderRepository->addCommission($order->mer_id,$order);

                // 更新用户支付时间
                /** @var UserMerchantRepository $userMerchantRepository */
                $userMerchantRepository = app()->make(UserMerchantRepository::class);
                $userMerchantRepository->updatePayTime($order->uid, $order->mer_id, $order->pay_price,true,$order->order_id);
                SwooleTaskService::merchant('notice', [
                    'type' => 'new_order',
                    'data' => [
                        'title' => '新订单',
                        'message' => '您有一个新的订单',
                        'id' => $order->order_id
                    ]
                ], $order->mer_id);

                // 创建分账账单
                /** @var StoreOrderProfitsharingRepository $storeOrderProfitsharingRepository */
                $storeOrderProfitsharingRepository = app()->make(StoreOrderProfitsharingRepository::class);
                // 支付金額不是0
                if ($order->pay_price !== 0) {
                    $profitsharing= [
                        'profitsharing_sn' => $storeOrderProfitsharingRepository->getOrderSn(),
                        'order_id' => $order->order_id,
                        'transaction_id' => $order->transaction_id ?? '',
                        'mer_id' => $order->mer_id,
                        'profitsharing_price' => $order->pay_price,
                        'profitsharing_mer_price' => $order->pay_price - $order->handling_fee,
                        'type' => $storeOrderProfitsharingRepository::PROFITSHARING_TYPE_ORDER,
                    ];

                    $profitsharingInfo = $storeOrderProfitsharingRepository->create($profitsharing);
                }
                $user = app()->make(UserRepository::class)->get($res['uid']);
                // 发放推广抵用券
                $this->computed($res,$user);
                $handling_fee = floatval($order->handling_fee);
                $total_amount = bcmul((string)$handling_fee, "0.4", 2);

                // 记录本次分红池,手续费的40%
                $poolInfo = Db::name('dividend_pool')->order('id', 'desc')->find();
                if (!$poolInfo) {
                    // 第一次创建分红池记录
                    Db::name('dividend_pool')->insert([
                        'total_amount' => $total_amount,
                        'available_amount' => $total_amount,
                        'distributed_amount' => 0,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // 更新现有分红池
                    Db::name('dividend_pool')->where('id', $poolInfo['id'])->update([
                        'total_amount' => Db::raw('total_amount + ' . $total_amount),
                        'available_amount' => Db::raw('available_amount + ' . $total_amount),
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
                    'create_time' => date('Y-m-d H:i:s'),
                    'remark' => '订单分红入池'
                ]);

                return $this->payAfter($res);
            });
        }
    }

    /**
     * @param StoreOrderOffline $order
     * @param User $user
     * @author xaboy
     * @day 2020/8/3
     */
    public function computed(StoreOrderOffline $order, User $user)
    {
        $userBillRepository = app()->make(UserBillRepository::class);
        if ($order->spread_uid) {
            $spreadUid = $order->spread_uid;
            $topUid = $order->top_uid;
        } else if ($order->is_selfbuy) {
            $spreadUid = $user->uid;
            $topUid = $user->spread_uid;
        } else {
            $spreadUid = $user->spread_uid;
            $topUid = $user->top_uid;
        }
        //TODO 添加冻结佣金
        if ($order->extension_one > 0 && $spreadUid) {
            /*$userBillRepository->incBill($spreadUid, 'brokerage', 'order_one', [
                'link_id' => $order['order_id'],
                'status' => 0,
                'title' => '获得推广佣金',
                'number' => $order->extension_one,
                'mark' => $user['nickname'] . '成功消费' . floatval($order['pay_price']) . '元,奖励推广佣金' . floatval($order->extension_one),
                'balance' => 0
            ]);*/
            // 佣金改为抵用券
            $userBillRepository->incBill($spreadUid, 'brokerage_price', 'order_one', [
                'link_id' => $order['order_id'],
                'status' => 0,
                'title' => '获得推广抵用券',
                'number' => $order->extension_one,
                'mark' => $user['nickname'] . '成功消费' . floatval($order['pay_price']) . '元,奖励推广抵用券' . floatval($order->extension_one),
                'balance' => 0
            ]);
            $userRepository = app()->make(UserRepository::class);
            $userRepository->incBrokerage($spreadUid, $order->extension_one);
            //            app()->make(FinancialRecordRepository::class)->dec([
            //                'order_id' => $order->order_id,
            //                'order_sn' => $order->order_sn,
            //                'user_info' => $userRepository->getUsername($spreadUid),
            //                'user_id' => $spreadUid,
            //                'financial_type' => 'brokerage_one',
            //                'number' => $order->extension_one,
            //            ], $order->mer_id);
        }
        if ($order->extension_two > 0 && $topUid) {
            // 获得推广佣金改为获得推广抵用券
            $userBillRepository->incBill($topUid, 'brokerage_price', 'order_two', [
                'link_id' => $order['order_id'],
                'status' => 0,
                'title' => '获得推广抵用券',
                'number' => $order->extension_two,
                'mark' => $user['nickname'] . '成功消费' . floatval($order['pay_price']) . '元,奖励推广抵用券' . floatval($order->extension_two),
                'balance' => 0
            ]);
            /*$userBillRepository->incBill($topUid, 'brokerage', 'order_two', [
                'link_id' => $order['order_id'],
                'status' => 0,
                'title' => '获得推广佣金',
                'number' => $order->extension_two,
                'mark' => $user['nickname'] . '成功消费' . floatval($order['pay_price']) . '元,奖励推广佣金' . floatval($order->extension_two),
                'balance' => 0
            ]);*/
            $userRepository = app()->make(UserRepository::class);
            $userRepository->incBrokerage($topUid, $order->extension_two);
            //            app()->make(FinancialRecordRepository::class)->dec([
            //                'order_id' => $order->order_id,
            //                'order_sn' => $order->order_sn,
            //                'user_info' => $userRepository->getUsername($topUid),
            //                'user_id' => $topUid,
            //                'financial_type' => 'brokerage_two',
            //                'number' => $order->extension_two,
            //            ], $order->mer_id);
        }
    }

    public function giveMerIntegral($mer_id, $groupOrder)
    {
        if ($groupOrder->give_integral > 0) {
            /**
             * @var MerchantDao $merchant
             */
            $merchant = app()->make(MerchantDao::class);
            $merchant->addIntegral($mer_id, $groupOrder->give_integral);
        }
    }

    public function giveIntegral($offlineOrder)
    {
        if ($offlineOrder->give_integral > 0) {
            app()->make(UserBillRepository::class)->incBill($offlineOrder->uid, 'integral', 'lock', [
                'link_id' => $offlineOrder['order_id'],
                'status' => 0,
                'title' => '用户增加积分',
                'number' => $offlineOrder->give_integral,
                'mark' => '用户成功消费,增加积分' . $offlineOrder->give_integral,
                //'mark' => '线下消费' . floatval($offlineOrder['pay_price']) . '元,赠送积分' . floatval($offlineOrder->give_integral),
                'balance' => $offlineOrder->user->integral
            ]);
        }
    }

    public function payAfter($ret)
    {
        $userBillRepository = app()->make(UserBillRepository::class);

        $title = '线下门店支付';

        $mark = '线下门店支付';

        $userBillRepository->incBill($ret['uid'],UserBillRepository::CATEGORY_NOW_MONEY,'offline_order',[
            'link_id' => $ret->order_id,
            'title' => $title,
            'number'=> $ret->pay_price,
            'status'=> 1,
            'mark' => $mark,
        ]);
        return true;
    }

    /**
     * @param $id
     * @param null $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/6/10
     */
    public function cancel($id, $uid = null)
    {
        $offlineOrder = $this->search(['paid' => 0, 'uid' => $uid ?? ''])->where('order_id', $id)->find();
        if (!$offlineOrder)
            throw new ValidateException('订单不存在');
        if ($offlineOrder['paid'] != 0)
            throw new ValidateException('订单状态错误,无法删除');
        //TODO 关闭订单
        Db::transaction(function () use ($offlineOrder, $id, $uid) {
            $offlineOrder->is_del = 1;
            $orderStatus = [];

            //退回积分
            if ($offlineOrder->integral > 0) {
                $make = app()->make(UserRepository::class);
                $make->update($offlineOrder->uid, ['integral' => Db::raw('integral+' . $offlineOrder->integral)]);
                app()->make(UserBillRepository::class)->incBill($offlineOrder->uid, 'integral', 'cancel', [
                    'link_id' => $offlineOrder['order_id'],
                    'status' => 1,
                    'title' => '退回积分',
                    'number' => $offlineOrder['integral'],
                    'mark' => '订单自动关闭,退回' . intval($offlineOrder->integral) . '积分',
                    'balance' => $make->get($offlineOrder->uid)->integral
                ]);
                // 退回商家积分
                app()->make(MerchantRepository::class)->subMerIntegral($offlineOrder->mer_id, 'mer_integral', $offlineOrder->order_id, $offlineOrder->integral);
            }

            // 退回抵扣金
            if($offlineOrder->deduction > 0){
                $make = app()->make(UserRepository::class);
                $make->update($offlineOrder->uid, ['coupon_amount' => Db::raw('coupon_amount+' . $offlineOrder->deduction)]);
            }

            $offlineOrder->save();
            //$storeOrderStatusRepository->batchCreateLog($orderStatus);
        });
    }

    public function v2CartIdByOrderInfo($user, $money, bool $userDeduction = false)
    {
        $uid = $user->uid;
        $user_coupon_amount = $user->coupon_amount;
        $deductionlFlag = $userDeduction  > 0;
        $deduction = [
            'use' => 0, // 使用的抵扣金数量
            'price' => 0 // 抵扣的金额
        ];
        $finalAmount=0;
        //计算抵扣金抵扣
        if ($deductionlFlag && $user_coupon_amount > 0 && $money > 0) {

            $eductionRate = 100;

            // 如果抵扣比例大于0
            if ($eductionRate > 0) {
                // 计算抵扣金额（抵扣金额 = 订单金额 * 抵扣比例）
                $deductionAmount = min($money, $user_coupon_amount); // 最大只能抵扣订单金额或抵扣金数量
                $deduction['use'] = intval($deductionAmount); // 使用的抵扣金数量
                $deduction['price'] = $deductionAmount; // 抵扣的金额
                // 更新剩余抵扣金
                $user_coupon_amount -= $deduction['use'];
                // 计算抵扣后的金额
                $finalAmount = $money - $deduction['price'];
            }

        }

        $data = [
            'use_deduction' => $deduction['use'],
            'deduction_price' => $user_coupon_amount ,
            'finalAmount' => $finalAmount

        ];
        return $data;
    }

    /**
     * @param $id
     * @param null $uid
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020/6/10
     */
    public function getDetail($id, $uid = null)
    {
        $where = [];
        $with = [
            'merchant' => function ($query) {
                return $query->field('mer_id,mer_name,service_phone')->append(['services_type']);
            }
        ];
        if ($uid) {
            $where['uid'] = $uid;
        } else if (!$uid) {
            $with['user'] = function ($query) {
                return $query->field('uid,nickname');
            };
        }
        $order = $this->dao->search($where)->where('order_id', $id)->where('is_del', 0)->with($with)->find();
        if (!$order) {
            return null;
        }
        return $order;
    }

    /**
     *  获取订单列表头部统计数据
     * @Author:Qinii
     * @Date: 2020/9/12
     * @param int|null $merId
     * @param int|null $orderType
     * @return array
     */
    public function OrderTitleNumber(?int $merId, ?int $orderType)
    {
        $where = [];
        $sysDel = $merId ? 0 : null;                    //商户删除
        if ($merId) $where['mer_id'] = $merId;          //商户订单

        //1: 未支付 2: 已支付 7: 已删除
        $all = $this->dao->search($where, $sysDel)->where($this->getOrderType(0))->count();
        $statusAll = $all;
        $unpaid = $this->dao->search($where, $sysDel)->where($this->getOrderType(1))->count();
        $unshipped = $this->dao->search($where, $sysDel)->where($this->getOrderType(2))->count();
        $del = $this->dao->search($where, $sysDel)->where($this->getOrderType(7))->count();

        return compact('all', 'statusAll', 'unpaid', 'unshipped', 'del');
    }

    /**
     * @param $status
     * @return mixed
     * @author Qinii
     */
    public function getOrderType($status)
    {
        $param['StoreOrderOffline.is_del'] = 0;
        switch ($status) {
            case 1:
                $param['StoreOrderOffline.paid'] = 0;
                break;    // 未支付
            case 2:
                $param['StoreOrderOffline.paid'] = 1;
                break;  // 已支付
            case 7:
                $param['StoreOrderOffline.is_del'] = 1;
                break;  // 已删除
            default:
                unset($param['StoreOrderOffline.is_del']);
                break;  //全部
        }
        return $param;
    }

    /**
     * @param array $where
     * @param $page
     * @param $limit
     * @return array
     * @author Qinii
     */
    public function merchantGetList(array $where, $page, $limit,$date)
    {
        $status = $where['status'];
        unset($where['status']);
        $query = $this->dao->search($where)->where($this->getOrderType($status))->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->with([
                'user' => function($query){
                    $query->field('uid,nickname,phone');
                }
            ]);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        // 手机号脱敏
        foreach ($list as $k => $v) {
            $list[$k]['user']['phone'] = substr_replace($v['user']['phone'], '****', 3, 4);
        }

        return compact('count', 'list');
    }

}

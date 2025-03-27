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
use app\common\repositories\BaseRepository;
use app\common\repositories\store\coupon\StoreCouponUserRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\system\groupData\GroupDataRepository;
use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\MemberinterestsRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserRepository;
use crmeb\jobs\SendSmsJob;
use crmeb\services\PayService;
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
        $total_price=$money;
        $commission_rate=0;
        $rate = 0.2;
        if($money>0){
            // 计算平台手续费
            /**
             * @var MerchantDao $merchant
             */
            $merchant = app()->make(MerchantDao::class);

            $merchant = $merchant->search(['mer_id' => $mer_id])->field('mer_id,commission_rate,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type')->find();

            if ($merchant['commission_rate'] > 0) {
                $rate = $merchant['commission_rate'] / 100;
            }

            $commission_rate = bcmul($rate, $money, 3);
        }

        //积分配置
        $sysIntegralConfig = systemConfig(['integral_money', 'integral_status', 'integral_order_rate']);
        $svip_status = $user->is_svip > 0 && systemConfig('svip_switch_status') == '1';
        $pay_price = $money;
        $svip_integral_rate = $svip_status ? app()->make(MemberinterestsRepository::class)->getSvipInterestVal(MemberinterestsRepository::HAS_TYPE_PAY) : 0;
        //计算赠送积分, 只有普通商品赠送积分
        $giveIntegralFlag = $sysIntegralConfig['integral_status'] && $sysIntegralConfig['integral_order_rate'] > 0;
        $total_give_integral = 0;
        //$order_total_give_integral = 0;
        if ($giveIntegralFlag  && $pay_price > 0) {
            $total_give_integral = floor(bcmul($pay_price, $rate, 0));
            if ($total_give_integral > 0 && $svip_status && $svip_integral_rate > 0) {
                $total_give_integral = bcmul($svip_integral_rate, $total_give_integral, 0);
            }
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

        $order_sn = app()->make(StoreOrderRepository::class)->getNewOrderId(StoreOrderRepository::TYPE_SN_USER_ORDER);
        $data = [
            'title'     => '线下门店支付',
            'link_id'   => 0,
            'order_sn'  => $order_sn,
            'pay_price' => $money,
            'order_info' => 0,
            'uid'        => $user->uid,
            'order_type' => self::TYPE_SVIP,
            'pay_type'   =>  $params['pay_type'],
            'commission_rate'=>$commission_rate,
            'status'     => 1,
            'mer_id'     => $mer_id,
            'gieve_integral' => $total_give_integral,
            'other'     => 0,
            'total_price' => $total_price,
            'deduction' => $params['user_deduction']?: 0,
            'deduction_money' => $params['user_deduction']?: 0,
            'to_uid'=>$params['to_uid']?:0
        ];
        $body = [
            'order_sn' => $order_sn,
            'pay_price' => $money,
            'attach' => 'offline_order',
            'body' =>'线下门店支付'
        ];
        $type = $params['pay_type'];
        if (in_array($type, ['weixin', 'alipay'], true) && $params['is_app']) {
            $type .= 'App';
        }
        if ($params['return_url'] && $type === 'alipay') $body['return_url'] = $params['return_url'];

        $info = $this->dao->create($data);
        if ($money){
            try {
                $service = new PayService($type,$body, 'offline_order');
                $config = $service->pay($user);
                return app('json')->status($type, $config + ['order_id' => $info->order_id]);
            } catch (\Exception $e) {
                return app('json')->status('error', $e->getMessage(), ['order_id' => $info->order_id]);
            }
        } else {
            $res = $this->paySuccess($data);
            return app('json')->status('success', ['order_id' => $info->order_id]);
        }
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
                $res->pay_time = date('y_m-d H:i:s', time());
                $res->save();
                $order = $res;
                // 商户增加余额
                app()->make(MerchantRepository::class)->addLockMoney($order->mer_id, 'order', $order->order_id, $order->pay_price);
                // 赠送积分
                $this->giveIntegral($order);
                // 赠送商户积分
                //$this->giveMerIntegral($order->mer_id,$groupOrder);
                app()->make(MerchantRepository::class)->addMerIntegral($order->mer_id, 'lock', $order->order_id, $order->give_integral);

                return $this->payAfter($res, $res);
            });
        }
    }

    public function giveIntegral($groupOrder)
    {
        if ($groupOrder->give_integral > 0) {
            app()->make(UserBillRepository::class)->incBill($groupOrder->uid, 'integral', 'lock', [
                'link_id' => $groupOrder['order_id'],
                'status' => 0,
                'title' => '下单赠送积分',
                'number' => $groupOrder->give_integral,
                'mark' => '线下成功消费' . floatval($groupOrder['pay_price']) . '元,赠送积分' . floatval($groupOrder->give_integral),
                'balance' => $groupOrder->user->integral
            ]);
        }
    }

    public function payAfter($data, $ret)
    {
        $financialRecordRepository = app()->make(FinancialRecordRepository::class);
        $userBillRepository = app()->make(UserBillRepository::class);

        $user = app()->make(UserRepository::class)->get($ret['uid']);

        $title = '线下门店支付';

        $mark = '线下门店支付';

        $userBillRepository->incBill($ret['uid'],UserBillRepository::CATEGORY_SVIP_PAY,'offline_order',[
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
        $groupOrder = $this->search(['paid' => 0, 'uid' => $uid ?? ''])->where('order_id', $id)->find();
        if (!$groupOrder)
            throw new ValidateException('订单不存在');
        if ($groupOrder['paid'] != 0)
            throw new ValidateException('订单状态错误,无法删除');
        //TODO 关闭订单
        Db::transaction(function () use ($groupOrder, $id, $uid) {
            $groupOrder->is_del = 1;
            $orderStatus = [];

            //退回积分
            if ($groupOrder->integral > 0) {
                $make = app()->make(UserRepository::class);
                $make->update($groupOrder->uid, ['integral' => Db::raw('integral+' . $groupOrder->integral)]);
                app()->make(UserBillRepository::class)->incBill($groupOrder->uid, 'integral', 'cancel', [
                    'link_id' => $groupOrder['order_id'],
                    'status' => 1,
                    'title' => '退回积分',
                    'number' => $groupOrder['integral'],
                    'mark' => '订单自动关闭,退回' . intval($groupOrder->integral) . '积分',
                    'balance' => $make->get($groupOrder->uid)->integral
                ]);
            }
            // 退回商家积分
            app()->make(MerchantRepository::class)->subMerIntegral($groupOrder->mer_id, 'mer_integral', $groupOrder->order->order_id, $groupOrder->integral);

            // 退回抵扣金
            if($groupOrder->deduction > 0){
                $make = app()->make(UserRepository::class);
                $make->update($groupOrder->uid, ['coupon_amount' => Db::raw('coupon_amount+' . $groupOrder->deduction)]);
            }
            //订单记录
            /*$storeOrderStatusRepository = app()->make(StoreOrderStatusRepository::class);
            foreach ($groupOrder->orderList as $order) {
                if ($order->activity_type == 3 && $order->presellOrder) {
                    $order->presellOrder->status = 0;
                    $order->presellOrder->save();
                }
                $order->is_del = 1;
                $order->save();
                $orderStatus[] = [
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'type' => $storeOrderStatusRepository::TYPE_ORDER,
                    'change_message' => '取消订单',
                    'change_type' => $storeOrderStatusRepository::ORDER_STATUS_CANCEL,
                    'uid' => $uid ?:0 ,
                    'nickname' => $uid ? $order->user->nickname : '系统',
                    'user_type' => $storeOrderStatusRepository::U_TYPE_SYSTEM,
                ];
            }*/
            /*$orderStatus[] = [
                'order_id' => $groupOrder->order_id,
                'order_sn' => $groupOrder->order_sn,
                'type' => $storeOrderStatusRepository::TYPE_ORDER,
                'change_message' => '取消订单',
                'change_type' => $storeOrderStatusRepository::ORDER_STATUS_CANCEL,
                'uid' => $uid ?:0 ,
                'nickname' => $uid ? $order->user->nickname : '系统',
                'user_type' => $storeOrderStatusRepository::U_TYPE_SYSTEM,
            ];*/
            $groupOrder->save();
            //$storeOrderStatusRepository->batchCreateLog($orderStatus);
        });
        Queue::push(CancelGroupOrderJob::class, $id);
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

}

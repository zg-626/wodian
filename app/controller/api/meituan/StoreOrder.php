<?php

namespace app\controller\api\meituan;

use app\common\repositories\delivery\DeliveryOrderRepository;
use app\common\repositories\store\order\StoreCartRepository;
use app\common\repositories\store\order\StoreGroupOrderRepository;
use app\common\repositories\store\order\StoreOrderCreateRepository;
use app\common\repositories\store\order\StoreOrderReceiptRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserRepository;
use app\common\repositories\WaimaiRepositories;
use app\common\repositories\wechat\WechatUserRepository;
use app\validate\api\UserReceiptValidate;
use crmeb\basic\BaseController;
use crmeb\services\LockService;
use think\App;
use think\exception\ValidateException;
use think\facade\Log;
use think\response\Json;

class StoreOrder extends BaseController
{
    /**
     * @var StoreOrderRepository
     */
    protected $repository;

    /**
     * StoreOrder constructor.
     * @param App $app
     * @param StoreOrderRepository $repository
     */
    public function __construct(App $app, StoreOrderRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function v2CheckOrder(StoreCartRepository $cartRepository, StoreOrderCreateRepository $orderCreateRepository)
    {
        $cartId = (array)$this->request->param('cart_id', []);
        $addressId = (int)$this->request->param('address_id');
        $couponIds = (array)$this->request->param('use_coupon', []);
        $takes = (array)$this->request->param('takes', []);
        $useIntegral = (bool)$this->request->param('use_integral', false);
        $userDeduction = (bool)$this->request->param('user_deduction', false);
        $user = $this->request->userInfo();
        $uid = $user->uid;
        if (!($count = count($cartId)) || $count != count($cartRepository->validIntersection($cartId, $uid)))
            return app('json')->fail('数据无效');
        $orderInfo = $orderCreateRepository->v2CartIdByOrderInfo($user, $cartId, $takes, $couponIds, $useIntegral,$userDeduction, $addressId);

        return app('json')->success($orderInfo);
    }

    public function pay()
    {
        $payType = 'routine';
        $key = (string)$this->request->param('key');
        $user_deduction = $this->request->param('user_deduction');
        $phone = $this->request->param('phone');
        $trade_no = $this->request->param('trade_no');
        /** @var UserRepository $user*/
        $user = app()->make(UserRepository::class);
        $userInfo = $user->getWhere(['phone' => $phone]);
        /*if(!$key){
            return app('json')->fail('订单操作超时,请刷新页面');
        }*/
        if (!$userInfo)
            return app('json')->fail('用户不存在，请在小程序注册');
        /** @var StoreGroupOrderRepository $groupOrderRepository */
        $groupOrderRepository = app()->make(StoreGroupOrderRepository::class);
        $groupOrder = $groupOrderRepository->getOrderByTradeNo($userInfo->uid, $trade_no);
        if (!$groupOrder)
            return app('json')->fail('订单不存在');
        //$tradeNo = $groupOrder->trade_no;
        // 同步修改美团订单表
        $order = (new \app\common\model\meituan\MeituanOrder)->where('trade_no', $trade_no)->find();
        if (!$order) {
            return app('json')->fail('美团订单不存在');
        }
        $wechatUserRepository = app()->make(WechatUserRepository::class);
        $openId = $wechatUserRepository->idByRoutineId($userInfo['wechat_user_id']);
        if (!$openId)
            throw new ValidateException('请关联微信小程序!');

        if (!in_array($payType, ['weixin', 'routine', 'h5', 'alipay', 'alipayQr', 'weixinQr', 'native'], true))
            return app('json')->fail('请选择正确的支付方式');
        try {
            $trade_amount = $order->trade_amount;
            $pay_price = $trade_amount;

            // 计算抵扣后的实际支付金额
            if ($user_deduction > 0) {
                $user_coupon_amount = $userInfo->coupon_amount;
                // 判断用户的抵用券是否大于抵用券额
                if($user_coupon_amount < $user_deduction){
                    return app('json')->fail('您的抵用券不足');
                }
                // 取两位数点
                $pay_price = round($trade_amount - $user_deduction, 2);

                // 更新订单信息
                $groupOrder->pay_price = $pay_price;
                $groupOrder->deduction = $user_deduction;
                $groupOrder->deduction_money = $user_deduction;
                $groupOrder->save();

                $order->pay_price = $pay_price;
                $order->save();

                // 更新主订单状态
                $store_order = \app\common\model\store\order\StoreOrder::where('trade_no', $groupOrder->trade_no)->find();
                if ($store_order) {
                    $store_order->deduction = $user_deduction;
                    $store_order->deduction_money = $user_deduction;
                    $store_order->save();
                }
                // 更新用户抵用券
                $deduction_money = bcsub($user_coupon_amount, $user_deduction, 2);
                $userInfo->coupon_amount = $deduction_money;
                $userInfo->save();
            }

            // 判断实际支付金额是否为0
            if ($pay_price <= 0) {
                // 实际支付金额为0，直接标记为支付成功
                $this->repository->paySuccess($groupOrder);
                return app('json')->status('success', '支付成功', ['order_id' => $groupOrder['group_order_id']]);
            } else {

                // 实际支付金额大于0，走第三方支付流程
                // 拉卡拉支付参数
                $order_sn = $groupOrder->group_order_sn;
                $order->pay_price = $pay_price;
                $order->save();
                $params = [
                    'order_no' => $order_sn,
                    'total_amount' => $pay_price,
                    'remark' => 'meituan',
                    'merchant_no' => '822584053112XE1',
                    'term_nos' => 'L8394421',
                    'openid' => $openId,
                    'trans_type' => 71,
                    'goods_id' => '2',
                    'settle_type' => '0',
                ];
                $api = new \Lakala\LklApi();
                $result = $api::lklPreorder($params);
                if (!$result) {
                    return app('json')->fail($api->getErrorInfo());
                }

                $config = [
                    'config' => $result
                ];
                return app('json')->status($payType, $config);
            }
        } catch (\Exception $e) {
            return app('json')->status('error', $e->getMessage().$e->getLine(), ['order_id' => $groupOrder->group_order_id]);
        }
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/6/10
     */
    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where['status'] = $this->request->param('status');
        $where['search'] = $this->request->param('store_name');
        $where['uid'] = $this->request->uid();
        $where['paid'] = 1;
//        $where['is_user'] = 1;
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    /**
     * @param $id
     * @return mixed
     * @author xaboy
     * @day 2020/6/10
     */
    public function detail($id)
    {
        $order = $this->repository->getDetail((int)$id, $this->request->uid());
        if (!$order)
            return app('json')->fail('订单不存在');
        if ($order->order_type == 1) {
            $order->append(['take', 'refund_status']);
        }
        return app('json')->success($order->toArray());
    }

    /**
     * @return mixed
     * @author xaboy
     * @day 2020/6/10
     */
    public function number()
    {
        return app('json')->success(['orderPrice' => $this->request->userInfo()->pay_price] + $this->repository->userOrderNumber($this->request->uid()));
    }

    /**
     * @param StoreGroupOrderRepository $groupOrderRepository
     * @return mixed
     * @author xaboy
     * @day 2020/6/10
     */
    public function groupOrderList(StoreGroupOrderRepository $groupOrderRepository)
    {
        [$page, $limit] = $this->getPage();
        $list = $groupOrderRepository->getList(['uid' => $this->request->uid(), 'paid' => 0], $page, $limit);
        return app('json')->success($list);
    }

    /**
     * @param $id
     * @param StoreGroupOrderRepository $groupOrderRepository
     * @return mixed
     * @author xaboy
     * @day 2020/6/10
     */
    public function groupOrderDetail($id, StoreGroupOrderRepository $groupOrderRepository)
    {
        $groupOrder = $groupOrderRepository->detail($this->request->uid(), (int)$id);
        if (!$groupOrder)
            return app('json')->fail('订单不存在');
        else
            return app('json')->success($groupOrder->append(['cancel_time', 'cancel_unix'])->toArray());
    }

    public function groupOrderStatus($id, StoreGroupOrderRepository $groupOrderRepository)
    {
        $groupOrder = $groupOrderRepository->status($this->request->uid(), intval($id));
        if (!$groupOrder)
            return app('json')->fail('订单不存在');
        if ($groupOrder->paid) $groupOrder->append(['give_coupon']);
        $activity_type = 0;
        $activity_id = 0;
        foreach ($groupOrder->orderList as $order) {
            $activity_type = max($order->activity_type, $activity_type);
            if ($order->activity_type == 4 && $groupOrder->paid) {
                $order->append(['orderProduct']);
                $activity_id = $order->orderProduct[0]['activity_id'];
            }
        }
        $groupOrder->activity_type = $activity_type;
        $groupOrder->activity_id = $activity_id;
        return app('json')->success($groupOrder->toArray());
    }

    /**
     * @param $id
     * @param StoreGroupOrderRepository $groupOrderRepository
     * @return mixed
     * @author xaboy
     * @day 2020/6/10
     */
    public function cancelGroupOrder($id, StoreGroupOrderRepository $groupOrderRepository)
    {
        $groupOrderRepository->cancel((int)$id, $this->request->uid());
        return app('json')->success('取消成功');
    }

    public function groupOrderPay($id, StoreGroupOrderRepository $groupOrderRepository)
    {
        //TODO 佣金结算,佣金退回,物流查询
        $type = $this->request->param('type');
        $is_points = $this->request->param('is_points',0);
        if (!in_array($type, StoreOrderRepository::PAY_TYPE))
            return app('json')->fail('请选择正确的支付方式');
        $groupOrder = $groupOrderRepository->detail($this->request->uid(), (int)$id, false);
        if (!$groupOrder)
            return app('json')->fail('订单不存在或已支付');
        $this->repository->changePayType($groupOrder, array_search($type, StoreOrderRepository::PAY_TYPE));
        if ($groupOrder['pay_price'] == 0) {
            $this->repository->paySuccess($groupOrder);
            return app('json')->status('success', '支付成功', ['order_id' => $groupOrder['group_order_id']]);
        }

        try {
            return $this->repository->pay($type, $this->request->userInfo(), $groupOrder, $this->request->param('return_url'), $this->request->isApp());
        } catch (\Exception $e) {
            return app('json')->status('error', $e->getMessage(), ['order_id' => $groupOrder->group_order_id]);
        }
    }

    public function take($id)
    {
        $this->repository->takeOrder($id, $this->request->userInfo());
        return app('json')->success('确认收货成功');
    }

    public function express($id)
    {
        $order = $this->repository->getWhere(['order_id' => $id, 'is_del' => 0]);
        if (!$order)
            return app('json')->fail('订单不存在');
        if (!$order->delivery_type || !$order->delivery_id)
            return app('json')->fail('订单未发货');
        $express = $this->repository->express($id,null);
        $order->append(['orderProduct']);
        return app('json')->success(compact('express', 'order'));
    }

    public function verifyCode($id)
    {
        $order = $this->repository->getWhere(['order_id' => $id, 'uid' => $this->request->uid(), 'is_del' => 0, 'order_type' => 1]);
        if (!$order)
            return app('json')->fail('订单状态有误');
        return app('json')->success(['qrcode' => $this->repository->wxQrcode($id, $order)]);
    }

    public function del($id)
    {
        $this->repository->userDel($id, $this->request->uid());
        return app('json')->success('删除成功');
    }

    public function createReceipt($id)
    {
        $data = $this->request->params(['receipt_type' , 'receipt_title' , 'duty_paragraph', 'receipt_title_type', 'bank_name', 'bank_code', 'address','tel', 'email']);
        $order = $this->repository->getWhere(['order_id' => $id, 'uid' => $this->request->uid(), 'is_del' => 0]);
        if (!$order) return app('json')->fail('订单不属于您或不存在');
        app()->make(StoreOrderReceiptRepository::class)->add($data, $order);
        return app('json')->success('操作成功');
    }

    public function getOrderDelivery($id, DeliveryOrderRepository $orderRepository)
    {
        $res = $orderRepository->show($id, $this->request->uid());
        return app('json')->success($res);
    }

}
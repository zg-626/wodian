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
use app\common\dao\user\LabelRuleDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\system\groupData\GroupDataRepository;
use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\user\MemberinterestsRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserRepository;
use crmeb\jobs\SendSmsJob;
use crmeb\services\PayService;
use FormBuilder\Factory\Elm;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;

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
            $total_give_integral = floor(bcmul($pay_price, 20, 0));
            if ($total_give_integral > 0 && $svip_status && $svip_integral_rate > 0) {
                $total_give_integral = bcmul($svip_integral_rate, $total_give_integral, 0);
            }
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
            'status'     => 1,
            'mer_id'     => $mer_id,
            'gieve_integral' => $total_give_integral,
            'other'     => 0,
        ];
        $body = [
            'order_sn' => $order_sn,
            'pay_price' => $money,
            'attach' => 'user_order',
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
                $service = new PayService($type,$body, 'user_order');
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
        // 付费会员充值
        if ($type == self::TYPE_SVIP) {
            return Db::transaction(function () use($data, $res) {
                $res->paid = 1;
                $res->pay_time = date('y_m-d H:i:s', time());
                $res->save();
                return $this->payAfter($res, $res);
            });
        }
    }

    public function payAfter($data, $ret)
    {
        $financialRecordRepository = app()->make(FinancialRecordRepository::class);
        $userBillRepository = app()->make(UserBillRepository::class);
        $info = json_decode($data['order_info']);
        $user = app()->make(UserRepository::class)->get($ret['uid']);
        $day = $info->svip_type == 3 ? 0 : $info->svip_number;
        $endtime = ($user['svip_endtime'] && $user['is_svip'] != 0) ? $user['svip_endtime'] : date('Y-m-d H:i:s',time());
        $svip_endtime =  date('Y-m-d H:i:s',strtotime("$endtime  +$day day" ));

        $user->is_svip = $info->svip_type;
        $user->svip_endtime = $svip_endtime;
        $user->save();
        $ret->status = 1;
        $ret->pay_time = date('Y-m-d H:i:s',time());
        $ret->end_time = $svip_endtime;
        $ret->save();
        $title = '支付会员';
        if ($info->svip_type == 3) {
            $date = '终身会员';
            $mark = '终身会员';
        } else {
            $date = $svip_endtime;
            $mark = '到期时间'.$svip_endtime;
        }

        $financialRecordRepository->inc([
            'order_id' => $ret->order_id,
            'order_sn' => $ret->order_sn,
            'user_info'=> $user->nickname,
            'user_id'  => $user->uid,
            'financial_type' => $financialRecordRepository::FINANCIA_TYPE_SVIP,
            'number' => $ret->pay_price,
            'type'  => 2,
        ],0);

        $userBillRepository->incBill($ret['uid'],UserBillRepository::CATEGORY_SVIP_PAY,'svip_pay',[
            'link_id' => $ret->order_id,
            'title' => $title,
            'number'=> $ret->pay_price,
            'status'=> 1,
            'mark' => $mark,
        ]);
        if ($user->phone) Queue::push(SendSmsJob::class,['tempId' => 'SVIP_PAY_SUCCESS','id' => ['phone' => $user->phone, 'date' => $date]]);

        //小程序发货管理
        event('mini_order_shipping', ['member', $ret, 3, '', '']);
        return ;
    }


    /**
     * 统计会员信息
     * @return array
     */
    public function countMemberInfo(array $where = [])
    {
        return [
            [
                'className' => 'el-icon-s-goods',
                'count' => $this->dao->search(['paid' => 1] + $where)->group('uid')->count(),
                'field' => 'member_nums',
                'name' => '累计付费会员人数'
            ],
            [
                'className' => 'el-icon-s-goods',
                'count' => $this->dao->search(['paid' => 1, 'pay_price' => 0] + $where)->sum('UserOrder.pay_price'),
                'field' => 'total_membership_fee',
                'name' => '累计支付会员费'
            ],
            [
                'className' => 'el-icon-s-goods',
                'count' => app()->make(UserRepository::class)->search(['svip_type' => 0])->count(),
                'field' => 'member_expire_nums',
                'name' => '累计已过期人数'
            ],
        ];
    }

}

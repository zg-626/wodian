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


namespace app\common\model\store\order;


use app\common\model\BaseModel;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;

class StoreOrderOffline extends BaseModel
{

    public static function tablePk(): ?string
    {
        return 'order_id';
    }

    public static function tableName(): string
    {
        return 'store_order_offline';
    }

    public function user()
    {
        return $this->hasOne(User::class,'uid','uid');
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'mer_id', 'mer_id');
    }

    public function getCombinePayParams()
    {
        $params = [
            'order_sn' => $this->order_sn,
            'sub_orders' => [],
            'attach' => 'offline_order',
            'body' => '线下订单支付',
        ];

        if ($this->pay_price > 0) {
            $subOrder = [
                'pay_price' => $this->pay_price,
                'order_sn' => $this->order_sn,
                'sub_mchid' => $this->merchant->sub_mchid,
            ];
            $params['sub_orders'][] = $subOrder;
        }

        return $params;
    }
}

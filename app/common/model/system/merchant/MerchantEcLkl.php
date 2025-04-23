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

namespace app\common\model\system\merchant;

use app\common\model\BaseModel;

class MerchantEcLkl extends BaseModel
{
    /**
     * @return string
     * @author xaboy
     * @day 2020-03-30
     */
    public static function tablePk(): string
    {
        return 'id';
    }

    /**
     * @return string
     * @author xaboy
     * @day 2020-03-30
     */
    public static function tableName(): string
    {
        return 'merchant_ec_lkl';
    }

    /**
     * 获取信息
     **/
    public static function getInfo($where, $field){
        $field_1 = 'id,mer_id,lkl_ec_status,lkl_mer_cup_status,lkl_mer_ledger_status,lkl_mer_bind_status';
        if($field == ''){
            $field = $field_1;
        }
        if($field != '*'){
            $field = $field_1.','.$field;
        }
        $info = self::where($where)->field($field)->find();
        return $info;
    }

}

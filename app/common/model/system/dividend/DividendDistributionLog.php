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


namespace app\common\model\system\dividend;

use app\common\model\BaseModel;

class DividendDistributionLog extends BaseModel
{

    public static function tablePk(): ?string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'dividend_distribution_log';
    }

    // 追加属性
    protected $append = [
        'type_text'
    ];

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['type'] ?? "");
        return $value ? $this->getTypeList()[$value] : '';
    }

    public function getTypeList()
    {
        return ['user' => '用户', 'merchant' => '商家'];
    }

    public function dividendPool()
    {
        return $this->hasOne(DividendPool::class, 'id', 'period_id');
    }
}

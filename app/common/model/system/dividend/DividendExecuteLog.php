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

class DividendExecuteLog extends BaseModel
{

    public static function tablePk(): ?string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'dividend_execute_log';
    }

    // 追加属性
    protected $append = [
        'execute_type_text',
        'status_text'
    ];

    public function getExecuteTypeTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['execute_type'] ?? "");
        return $value ? $this->getTypeList()[$value] : '';
    }

    public function getTypeList()
    {
        return ['1' => '月初分红', '2' => '5天分红'];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : ($data['status'] ?? "");
        return $value ? $this->getStatusList()[$value] : '';
    }

    public function getStatusList()
    {
        return ['失败', '成功'];
    }

    public function dividendPool()
    {
        return $this->hasOne(DividendPool::class, 'id', 'dp_id');
    }
}

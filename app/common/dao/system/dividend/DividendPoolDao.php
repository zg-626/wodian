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


namespace app\common\dao\system\dividend;

use app\common\dao\BaseDao;
use app\common\model\system\dividend\DividendPool;

class DividendPoolDao extends BaseDao
{

    protected function getModel(): string
    {
        return DividendPool::class;
    }

    public function search(array $where)
    {
        $query = DividendPool::getDB()
            ->when(isset($where['city_id']) && $where['city_id'] > 0, function ($query) use ($where) {
                $query->where('city_id', $where['city_id']);
            })
            ->when(isset($where['city']) && $where['city'] !== '', function ($query) use ($where) {
                $query->whereLike('city', "%{$where['city']}%");
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'create_time');
            });
        return $query;
    }

}

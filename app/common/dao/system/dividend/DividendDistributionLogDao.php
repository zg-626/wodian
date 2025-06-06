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
use app\common\model\system\dividend\DividendDistributionLog;

class DividendDistributionLogDao extends BaseDao
{

    protected function getModel(): string
    {
        return DividendDistributionLog::class;
    }

    public function search(array $where)
    {
        $query = DividendDistributionLog::getDB()
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'create_time');
            })
            ->when(isset($where['dp_ids']) && $where['dp_ids'], function ($query) use ($where) {
                $query->where('period_id', 'in', $where['dp_ids']);
            })
            ->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
                $query->where('type', $where['type']);
            });
        return $query;
    }

}

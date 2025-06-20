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


namespace app\common\repositories\system\dividend;


use app\common\dao\system\dividend\DividendDistributionLogDao;
use app\common\model\system\dividend\DividendPool;
use app\common\repositories\BaseRepository;
use think\facade\Cache;
use think\facade\Db;

/**
 * Class DividendDistributionLogRepository
 * @package app\common\repositories\system\dividend
 * @author xaboy
 * @day 2020/8/5
 * @mixin DividendDistributionLogDao
 */
class DividendDistributionLogRepository extends BaseRepository
{

    public function __construct(DividendDistributionLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getList(array $where, int $page, int $limit)
    {
        if ($where['city']) {
            $dp_ids = DividendPool::where('city', 'like', '%' . $where['city'] . '%')->column('id');
            if ($dp_ids) {
                $where['dp_ids'] = $dp_ids;
            } else{
                $count = 0;
                $list = [];
                return compact('count', 'list');
            }
        }
        $query = $this->dao->search($where)
            ->with([
                'dividendPool' => function ($query) {
                    $query->field('id,city_id,city');
                },
                /*'dividendPeriodLog' => function ($query) {
                    $query->field('id,city_id,city');
                },*/
            ])
            ->order('id desc');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

}

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


use app\common\dao\system\dividend\DividendPoolLogDao;
use app\common\repositories\BaseRepository;
use think\facade\Cache;
use think\facade\Db;

/**
 * Class DividendPoolLogRepository
 * @package app\common\repositories\system\dividend
 * @author xaboy
 * @day 2020/8/5
 * @mixin DividendPoolLogDao
 */
class DividendPoolLogRepository extends BaseRepository
{

    public function __construct(DividendPoolLogDao $dao)
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
    public function getList(array $where,int $page,int $limit)
    {
        $query = $this->dao->search($where)
            ->with([
//                'merchant' => function ($query) {
//                    $query->field('mer_id,mer_name,is_trader,mer_state,mer_avatar');
//                },
                'user' => function ($query) {
                    $query->field('uid,nickname,phone,avatar');
                },
            ])
            ->order('id desc');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

}

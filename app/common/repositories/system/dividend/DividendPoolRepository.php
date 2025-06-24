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


use app\common\dao\system\dividend\DividendPoolDao;
use app\common\model\system\dividend\DividendPeriodLog;
use app\common\model\system\dividend\DividendPoolLog;
use app\common\repositories\BaseRepository;
use think\facade\Cache;
use think\facade\Db;

/**
 * Class DividendPoolRepository
 * @package app\common\repositories\system\dividend
 * @author xaboy
 * @day 2020/8/5
 * @mixin DividendPoolDao
 */
class DividendPoolRepository extends BaseRepository
{

    public function __construct(DividendPoolDao $dao)
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
        $query = $this->dao->search($where)->order('id asc');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        foreach ($list as &$item) {
            $new_amount = $item['initial_threshold'];
            // 获取当前最新周期数据
            $next_threshold=DividendPeriodLog::where('dp_id',$item['id'])->where('execute_type',2)->order('id desc')->value('next_threshold')?:10000;
            if($next_threshold){
                if($next_threshold>$new_amount){
                    // 差值
                    $item['difference'] = bcsub($next_threshold,$new_amount,2);
                    // 计算抹平差值的流水
                    $item['water'] = round($item['difference']/0.4/0.2,2);
                }else{
                    $item['difference'] = 0;
                }
            }else{
                if($next_threshold>$new_amount){
                    // 差值
                    $item['difference'] = bcsub($next_threshold,$new_amount,2);
                    // 计算抹平差值的流水
                    $item['water'] = round($item['difference']/0.4/0.2,2);
                }else{
                    $item['difference'] = 0;
                }
            }

        }
        return compact('count', 'list');
    }

}

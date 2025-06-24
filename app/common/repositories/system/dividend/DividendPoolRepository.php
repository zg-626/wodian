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
            $initial_threshold = $item['initial_threshold'];// 当前最新累计金额
            // 获取当前最新周期数据
            $lastInfo=DividendPeriodLog::where('dp_id',$item['id'])->where('execute_type',2)->order('id desc')->field('next_threshold,initial_threshold')->find();
            if($lastInfo){
                $last_next_threshold=$lastInfo['next_threshold'];// 下一个周期应该达到的金额
                $item['initial_threshold'] = $lastInfo['initial_threshold'];// 上期开始的金额
                if($last_next_threshold>$initial_threshold){
                    // 差值
                    $item['difference'] = bcsub($last_next_threshold,$initial_threshold,2);
                    // 计算抹平差值的流水
                    $item['water'] = round($item['difference']/0.4/0.2,2);
                }else{
                    $item['difference'] = 0;
                }
            }else{
                $new_amount = systemConfig('sys_red_money')??10000;
                if($initial_threshold>$new_amount){
                    // 差值
                    $item['difference'] = bcsub($initial_threshold,$new_amount,2);
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

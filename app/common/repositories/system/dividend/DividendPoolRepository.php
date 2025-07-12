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
            $current_amount = $item['initial_threshold']; // 当前最新累计金额
            // 获取当前最新周期数据
            $lastInfo=DividendPeriodLog::where('dp_id',$item['id'])->where('execute_type',2)->order('id desc')->field('next_threshold,initial_threshold')->find();
            if($lastInfo){
                $target_amount = $lastInfo['next_threshold'];
                $start_amount = $lastInfo['initial_threshold']; // 新增：获取开始值

                if($target_amount > $current_amount){
                    $item['difference'] = bcsub($target_amount, $current_amount, 2);
                    $item['water'] = round($item['difference']/0.08, 2);

                    // 新增：进度信息
                    $item['total_difference'] = bcsub($target_amount, $start_amount, 2); // 总需要增加
                    $item['completed'] = bcsub($current_amount, $start_amount, 2); // 已完成部分
                    $item['progress_rate'] = round($item['completed']/$item['total_difference']*100, 2); // 完成百分比
                } else {
                    $item['difference'] = 0;
                    $item['water'] = 0;
                    $item['total_difference'] = 0;
                    $item['completed'] = bcsub($target_amount, $start_amount, 2);
                    $item['progress_rate'] = 100;
                }
            }else{
                $target_amount = systemConfig('sys_red_money')??10000;
                if($target_amount > $current_amount){
                    // 差值
                    $item['difference'] = bcsub($target_amount, $current_amount, 2);
                    // 计算抹平差值的流水
                    $item['water'] = round($item['difference']/0.4/0.2, 2);
                }else{
                    $item['difference'] = 0;
                }
            }
        }
        return compact('count', 'list');
    }

}

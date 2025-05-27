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

namespace app\controller\api\dividend;

use app\common\repositories\article\ArticleRepository as repository;
use app\common\repositories\user\BonusOfflineService;
use app\common\repositories\user\BonusService;
use app\common\repositories\user\DividendPoolService;
use app\common\repositories\user\UserBillRepository;
use crmeb\basic\BaseController;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

class Dividend extends BaseController
{
    /**
     * @var repository
     */
    protected $repository;

    /**
     * StoreBrand constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    // 测试接口
    public function test()
    {
        /** @var DividendPoolService $dividendPoolService **/
        /*$dividendPoolService = app()->make(DividendPoolService::class);
        $dividendPoolService->calculateAndDistributeDividend();*/
        /** @var BonusService $bonusService **/
        /*$bonusService = app()->make(BonusService::class);
        $info = $bonusService->calculateBonus();
        echo "<pre>";
        print_r($info);*/
        /** @var BonusOfflineService $bonusOfflineService **/
        $bonusOfflineService = app()->make(BonusOfflineService::class);
        try {
            $info=$bonusOfflineService->calculateBonus();
            echo "<pre>";
            print_r($info);
        }catch (\Exception $e) {
            echo "<pre>";
            print_r($e->getMessage());
        }

    }

    // 定时分红
    public function dividend()
    {
        /** @var BonusOfflineService $bonusOfflineService */
        $bonusOfflineService = app()->make(BonusOfflineService::class);

        // 使用ThinkPHP的Cache门面实现分布式锁
        $lockKey = 'dividend_task_lock';
        $lockValue = uniqid(mt_rand(), true);

        try {
            // 获取锁，5分钟超时
            if (!Cache::store('redis')->set($lockKey, $lockValue, 300, 'NX')) {
                return json(['code' => 0, 'msg' => '分红任务正在执行中']);
            }

            $currentDay = date('d');
            $lastExecuteDay = $this->getLastExecuteDay();

            // 检查今天是否已执行过
            if ($this->isExecutedToday()) {
                return json(['code' => 0, 'msg' => '今日已执行过分红任务']);
            }

            // 月初分红
            if ($currentDay === '01') {
                $bonusOfflineService->distributeBaseAmount();
                $this->recordExecuteLog(1); // 记录月初分红
                Log::info('月初基础金额分红执行完成: ' . date('Y-m-d H:i:s'));
            }

            // 5天分红
            if ($this->shouldExecuteDividend($lastExecuteDay)) {
                $info = $bonusOfflineService->calculateBonus();
                if ($info!==false) {
                    $this->recordExecuteLog(2, $info['bonus_amount'] ?? 0); // 记录5天分红
                }
                record_log('时间: ' . date('Y-m-d H:i:s') . ', 系统分红: ' . json_encode($info, JSON_UNESCAPED_UNICODE), 'red');
            }

            return json(['code' => 1, 'msg' => '分红任务执行成功']);

        } catch (\Exception $e) {
            Log::info('分红任务执行失败：' . $e->getMessage());
            return json(['code' => 0, 'msg' => '分红任务执行失败：' . $e->getMessage().$e->getLine()]);
        } finally {
            // 释放锁（确保是自己的锁）
            if (Cache::store('redis')->get($lockKey) === $lockValue) {
                Cache::store('redis')->delete($lockKey);
            }
        }
    }

    private function isExecutedToday(): bool
    {
        return Db::name('dividend_execute_log')
            ->where('execute_date', date('Y-m-d'))
            ->where('status', 1)
            ->count() > 0;
    }

    private function recordExecuteLog(int $type, float $amount = 0): void
    {
        Db::name('dividend_execute_log')->insert([
            'execute_date' => date('Y-m-d'),
            'execute_type' => $type,
            'status' => 1,
            'bonus_amount' => $amount,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取上次执行日期
     */
    private function getLastExecuteDay(): string
    {
        // 查询最近一次5天分红记录
        $lastRecord = Db::name('dividend_execute_log')
            ->where('execute_type', 2)
            ->where('status', 1)
            ->order('execute_date desc')
            ->value('execute_date');
        
        return $lastRecord ? date('d', strtotime($lastRecord)) : '0';
    }

    /**
     * 判断是否需要执行分红
     */
    private function shouldExecuteDividend(string $lastDay): bool
    {
        if ($lastDay === '0') return true;

        $currentDay = (int)date('d');
        $lastDay = intval($lastDay);

        // 如果跨月了，需要特殊处理
        if ($currentDay < $lastDay) {
            $lastDay = $currentDay; // 重置上次执行日期
        }

        // 检查是否已经过了5天
        return ($currentDay - $lastDay) >= 5;
    }
}

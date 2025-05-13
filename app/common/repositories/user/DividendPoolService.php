<?php

namespace app\common\repositories\user;

use app\common\repositories\user\UserBillRepository;
use app\common\repositories\BaseRepository;
use app\common\repositories\divide\PouchRepository;
use app\common\repositories\divide\DividendRepository;
use app\common\repositories\merchant\MerchantRepository;
use app\common\repositories\order\OrderRepository;

class DividendPoolService extends BaseRepository
{
    protected $orderRepository;
    protected $userBillRepository;
    protected $merchantRepository;
    protected $dividendRepository;
    protected $pouchRepository;

    public function __construct(OrderRepository $orderRepository, UserBillRepository $userBillRepository, MerchantRepository $merchantRepository, DividendRepository $dividendRepository, PouchRepository $pouchRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->userBillRepository = $userBillRepository;
        $this->merchantRepository = $merchantRepository;
        $this->dividendRepository = $dividendRepository;
        $this->pouchRepository = $pouchRepository;
    }

    public function calculateAndDistributeDividend()
    {
        // 准备开始日期和结束日期
        $startDate = date('Y-m-d 00:00:00', strtotime('-2 days'));
        $endDate = date('Y-m-d 23:59:59');

        // 查询最近两天的订单
        $orders = $this->orderRepository->where('create_time', 'between', [$startDate, $endDate])->order('create_time', 'desc')->select();

        // 计算积分
        $totalIntegral = $this->calculateUserAndMerchantIntegrals($orders);

        // 检查分红池的环比增长
        $latestGrowthRatio = $this->checkGrowthRatio();

        // 进行分红
        if ($latestGrowthRatio >= 1.15) {
            $dividend = $totalIntegral * 0.4;  // 假设分红比例是40%
            $this->distributeDividend($dividend);
        }
    }

    private function calculateUserAndMerchantIntegrals($orders)
    {
        $totalIntegral = 0;
        foreach ($orders as $order) {
            $userIntegral = $order['消费金额'] * 0.2;
            $merchantIntegral = $order['消费金额'] * 0.2;

            // 更新用户表的积分
            $user = $this->userBillRepository->getId($order['user_id']);
            $user->积分 += $userIntegral;
            $user->save();

            // 更新商家表的积分
            $merchant = $this->merchantRepository->getId($order['merchant_id']);
            $merchant->积分 += $merchantIntegral;
            $merchant->save();

            // 把积分存入分红池
            $dividendPool = $this->pouchRepository->find(['name' => '分红池']);
            if (!$dividendPool) {
                $dividendPool = $this->pouchRepository->create(['积分' => ($userIntegral + $merchantIntegral), 'name' => '分红池']);
            } else {
                $dividendPool->积分 += ($userIntegral + $merchantIntegral);
                $dividendPool->save();
            }

            $totalIntegral += $userIntegral + $merchantIntegral;
        }

        return $totalIntegral;
    }

    private function checkGrowthRatio()
    {
        // 获取最新一期分红池的积分
        $latestDividendPool = $this->pouchRepository->order('id', 'desc')->find();
        $currentIntegral = $latestDividendPool['积分'] ?? 0;

        // 获取上一期分红池的积分
        $lastDividendPool = $this->pouchRepository->order('id', 'desc')->limit(1, 1)->find();
        $lastIntegral = $lastDividendPool ? $lastDividendPool['积分'] : 0;

        // 计算环比增长比例
        if ($lastIntegral > 0) {
            $growthRatio = $currentIntegral / $lastIntegral;
        } else {
            $growthRatio = 1;
        }

        return $growthRatio;
    }

    private function distributeDividend($dividend)
    {
        // 分给用户和商家
        $dividendForEach = $dividend / 2;

        // 更新用户表的积分
        $users = $this->userBillRepository->select();
        foreach ($users as $user) {
            $user->积分 += $dividendForEach;
            $user->save();
        }

        // 更新商家表的积分
        $merchants = $this->merchantRepository->select();
        foreach ($merchants as $merchant) {
            $merchant->积分 += $dividendForEach;
            $merchant->save();
        }

        // 更新分红池的积分
        $dividendPool = $this->pouchRepository->find(['name' => '分红池']);
        if ($dividendPool) {
            $dividendPool->积分 -= $dividend;
            $dividendPool->save();
        }
    }
}

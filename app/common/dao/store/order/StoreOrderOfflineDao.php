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


namespace app\common\dao\store\order;

use app\common\dao\BaseDao;
use app\common\dao\system\merchant\MerchantDao;
use app\common\model\store\order\StoreOrderOffline;
use app\common\model\user\LabelRule;
use think\db\BaseQuery;
use think\facade\Db;

class StoreOrderOfflineDao extends BaseDao
{

    protected function getModel(): string
    {
        return StoreOrderOffline::class;
    }

    public function search(array $where)
    {
        return StoreOrderOffline::hasWhere('user', function ($query) use ($where) {
            $query->when(isset($where['uid']) && $where['uid'] != '', function ($query) use ($where) {
                $query->where('uid', $where['uid']);
            })
                ->when(isset($where['keyword']) && $where['keyword'] != '', function ($query) use ($where) {
                    $query->whereLike('nickname', "%{$where['keyword']}%");
                })
                ->when(isset($where['phone']) && $where['phone'] != '', function ($query) use ($where) {
                    $query->where('phone', $where['phone']);
                });
            $query->where(true);
        })
            ->when(isset($where['order_sn']) && $where['order_sn'] !== '', function ($query) use ($where) {
                $query->whereLike('order_sn', "%{$where['order_sn']}%");
            })
            ->when(isset($where['title']) && $where['title'] !== '', function ($query) use ($where) {
                $query->whereLike('title', "%{$where['title']}%");
            })
            ->when(isset($where['order_type']) && $where['order_type'] !== '', function ($query) use ($where) {
                $query->where('order_type', $where['order_type']);
            })->when(isset($where['is_del']) && $where['is_del'] !== '', function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })
            ->when(isset($where['paid']) && $where['paid'] !== '', function ($query) use ($where) {
                $query->where('paid', $where['paid']);
            })
            ->when(isset($where['pay_type']) && $where['pay_type'] !== '', function ($query) use ($where) {
                $query->where('pay_type', $where['pay_type']);
            })
            ->when(isset($where['pay_time']) && $where['pay_time'] !== '', function ($query) use ($where) {
                $query->whereDay('pay_time', $where['pay_time']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', $where['mer_id']);
            })
            ->when(isset($where['pay_price']) && $where['pay_price'] !== '', function ($query) use ($where) {
                $query->where('StoreOrderOffline.pay_price', '>', $where['pay_price']);
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'StoreOrderOffline.create_time');
            });
    }

    /**
     * @param $time
     * @param bool $is_remind
     * @return array
     * @author xaboy
     * @day 2020/6/9
     */
    public function getTimeOutIds($time)
    {
        return StoreOrderOffline::getDB()->where('is_del', 0)->where('paid', 0)->where('create_time', '<=', $time)->column('order_id');
    }

    /**
     * @param $id
     * @param $uid
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020/6/11
     */
    public function userOrder($id, $uid)
    {
        return StoreOrderOffline::getDB()->where('order_id', $id)->where('uid', $uid)->where('is_del', 0)->where('paid', 1)->where('is_system_del', 0)->find();
    }

    /**
     * @param array $where
     * @param $ids
     * @return BaseQuery
     * @author xaboy
     * @day 2020/6/26
     */
    public function usersOrderQuery(array $where, $ids, $uid)
    {
        return StoreOrderOffline::getDB()->where(function ($query) use ($uid, $ids) {
            $query->whereIn('uid', $ids)
                ->whereOr(function ($query) use ($uid) {
                    if ($uid) {
                        $query->where('uid', $uid)->where('is_selfbuy', 1);
                    }
                });
        })->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
            getModelTime($query, $where['date'], 'pay_time');
        })->when(isset($where['create_time']) && $where['create_time'] !== '', function ($query) use ($where) {
            getModelTime($query, $where['create_time'], 'create_time');
        })->when(isset($where['keyword']) && $where['keyword'] !== '', function ($query) use ($where) {
            $_uid = User::where('nickname', 'like', "%{$where['keyword']}%")->column('uid');
            $orderId = StoreOrderProduct::alias('op')
                ->join('storeProduct sp', 'op.product_id = sp.product_id')
                ->whereLike('store_name', "%{$where['keyword']}%")
                ->column('order_id');
            $query->where(function ($query) use ($orderId, $where, $_uid) {
                $query->whereLike('order_id|order_sn', "%{$where['keyword']}%")->whereOr('order_id', 'in', $orderId)->whereOr('uid', 'in', $_uid);
            });
        })->where('paid', 1)->order('pay_time DESC');
    }

    /**
     * @param $field
     * @param $value
     * @param int|null $except
     * @return bool
     * @author xaboy
     * @day 2020/6/11
     */
    public function fieldExists($field, $value, ?int $except = null): bool
    {
        return ($this->getModel()::getDB())->when($except, function ($query) use ($field, $except) {
                $query->where($field, '<>', $except);
            })->where($field, $value)->count() > 0;
    }

    /**
     * @param $id
     * @return mixed
     * @author xaboy
     * @day 2020/6/12
     */
    public function getMerId($id)
    {
        return StoreOrderOffline::getDB()->where('order_id', $id)->value('mer_id');
    }

    /**
     * @param array $where
     * @return bool
     * @author Qinii
     * @day 2020-06-12
     */
    public function merFieldExists(array $where)
    {
        return ($this->getModel()::getDB())->where($where)->count() > 0;
    }

    /**
     * TODO
     * @param $reconciliation_id
     * @return mixed
     * @author Qinii
     * @day 2020-06-15
     */
    public function reconciliationUpdate($reconciliation_id)
    {
        return ($this->getModel()::getDB())->whereIn('reconciliation_id', $reconciliation_id)->update(['reconciliation_id' => 0]);
    }

    public function dayOrderNum($day, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($day, function ($query, $day) {
            getModelTime($query, $day, 'pay_time');
        })->count();
    }

    public function dayOrderPrice($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('pay_price');
    }

    public function dayOrderTotalPrice($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('total_price');
    }

    public function dayOrderSettlementPrice($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->where('origin_log_no','<>','')->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('pay_price');
    }

    public function dayOrderUnSettlementPrice($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->where('origin_log_no', '')->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('pay_price');
    }

    // 手续费
    public function dayOrderCommission($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('handling_fee');
    }

    // 已结算手续费
    public function dayOrderSettlementCommission($day, $merId = null)
    {
        return getModelTime(StoreOrderOffline::getDB()->where('paid', 1)->where('origin_log_no','<>','')->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        }), $day, 'pay_time')->sum('handling_fee');
    }

    public function dateOrderPrice($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->sum('pay_price');
    }

    public function dateOrderInfo($date, $merId = null)
    {
        // 实际付款金额
        $pay_price = StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->sum('pay_price');

        $total_price = StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->sum('total_price');

        // 手续费
        $handling_fee =StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->sum('handling_fee');
        // 实际到账，精确两位数
        $actualPrice =bcsub($total_price,$handling_fee,2);
        // 获取商家信息
        $MerchantDao = app()->make(MerchantDao::class);
        $merchant = $MerchantDao->search(['mer_id' => $merId])->field('mer_id,integral,salesman_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type')->find();
        $integral=$merchant['integral'];
        return ['pay_price' => $pay_price,'total_price'=>$total_price, 'handling_fee' => $handling_fee, 'actualPrice' => $actualPrice, 'integral' => $integral];
    }

    public function dateOrderNum($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->count();
    }

    public function dayOrderUserNum($day, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($day, function ($query, $day) {
            getModelTime($query, $day, 'pay_time');
        })->group('uid')->count();
    }

    public function orderUserNum($date, $paid = null, $merId = null)
    {
        return StoreOrderOffline::getDB()->when($paid, function ($query, $paid) {
            $query->where('paid', $paid);
        })->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) use ($paid) {
            if (!$paid) {
                getModelTime($query, $date);
            } else
                getModelTime($query, $date, 'pay_time');
        })->group('uid')->count();
    }

    public function orderUserGroup($date, $paid = null, $merId = null)
    {
        return StoreOrderOffline::getDB()->when($paid, function ($query, $paid) {
            $query->where('paid', $paid);
        })->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) {
            getModelTime($query, $date, 'pay_time');
        })->group('uid')->field(Db::raw('uid,sum(pay_price) as pay_price,count(order_id) as total'))->select();
    }

    public function oldUserNum(array $ids, $merId = null)
    {
        return StoreOrderOffline::getDB()->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->whereIn('uid', $ids)->where('paid', 1)->group('uid')->count();
    }

    public function oldUserIds(array $ids, $merId = null)
    {
        return StoreOrderOffline::getDB()->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->whereIn('uid', $ids)->where('paid', 1)->group('uid')->column('uid');
    }

    public function orderPrice($date, $paid = null, $merId = null)
    {
        return StoreOrderOffline::getDB()->when($paid, function ($query, $paid) {
            $query->where('paid', $paid);
        })->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->when($date, function ($query, $date) use ($paid) {
            if (!$paid) {
                $query->where(function ($query) use ($date) {
                    $query->where(function ($query) use ($date) {
                        $query->where('paid', 1);
                        getModelTime($query, $date, 'pay_time');
                    })->whereOr(function ($query) use ($date) {
                        $query->where('paid', 0);
                        getModelTime($query, $date);
                    });
                });
            } else
                getModelTime($query, $date, 'pay_time');
        })->sum('pay_price');
    }

    public function orderGroupNum($date, $merId = null)
    {
        $field = Db::raw('sum(pay_price) as pay_price,count(*) as total,count(distinct uid) as user,pay_time,from_unixtime(unix_timestamp(pay_time),\'%m-%d\') as `day`');
        if ($date == 'year') {
            $field = Db::raw('sum(pay_price) as pay_price,count(*) as total,count(distinct uid) as user,pay_time,from_unixtime(unix_timestamp(pay_time),\'%m\') as `day`');
        }
        $query = StoreOrderOffline::getDB()->field($field)
            ->where('paid', 1)->when($date, function ($query, $date) {
                getModelTime($query, $date, 'pay_time');
            })->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            });
        return $query->order('pay_time ASC')->group('day')->select();
    }

    public function orderGroupNumPage($where, $page, $limit, $merId = null)
    {
        return StoreOrderOffline::getDB()->when(isset($where['dateRange']), function ($query) use ($where) {
            getModelTime($query, date('Y/m/d 00:00:00', $where['dateRange']['start']) . '-' . date('Y/m/d 00:00:00', $where['dateRange']['stop']), 'pay_time');
        })->field(Db::raw('sum(pay_price) as pay_price,count(*) as total,count(distinct uid) as user,pay_time,from_unixtime(unix_timestamp(pay_time),\'%m-%d\') as `day`'))
            ->where('paid', 1)->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            })->order('pay_time DESC')->page($page, $limit)->group('day')->select();
    }

    public function dayOrderPriceGroup($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->field(Db::raw('sum(pay_price) as price, from_unixtime(unix_timestamp(pay_time),\'%H:%i\') as time'))
            ->where('paid', 1)->when($date, function ($query, $date) {
                getModelTime($query, $date, 'pay_time');
            })->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            })->group('time')->select();
    }

    public function dayOrderNumGroup($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->field(Db::raw('count(*) as total, from_unixtime(unix_timestamp(pay_time),\'%H:%i\') as time'))
            ->where('paid', 1)->when($date, function ($query, $date) {
                getModelTime($query, $date, 'pay_time');
            })->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            })->group('time')->select();
    }

    public function dayOrderUserGroup($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->field(Db::raw('count(DISTINCT uid) as total, from_unixtime(unix_timestamp(pay_time),\'%H:%i\') as time'))
            ->where('paid', 1)->when($date, function ($query, $date) {
                getModelTime($query, $date, 'pay_time');
            })->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            })->group('time')->select();
    }

    /**
     * 获取当前时间到指定时间的支付金额 管理员
     * @param string $start 开始时间
     * @param string $stop 结束时间
     * @return mixed
     */
    public function chartTimePrice($start, $stop, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)
            ->where('pay_time', '>=', $start)
            ->where('pay_time', '<', $stop)
            ->when($merId, function ($query, $merId) {
                $query->where('mer_id', $merId);
            })
            ->field('sum(pay_price) as num,FROM_UNIXTIME(unix_timestamp(pay_time), \'%Y-%m-%d\') as time')
            ->group('time')
            ->order('pay_time ASC')->select()->toArray();
    }

    /**
     * @param $date
     * @param null $merId
     * @return mixed
     */
    public function chartTimeNum($date, $merId = null)
    {
        return StoreOrderOffline::getDB()->where('paid', 1)->when($date, function ($query) use ($date) {
            getModelTime($query, $date, 'pay_time');
        })->when($merId, function ($query, $merId) {
            $query->where('mer_id', $merId);
        })->field('count(order_id) as num,FROM_UNIXTIME(unix_timestamp(pay_time), \'%Y-%m-%d\') as time')
            ->group('time')
            ->order('pay_time ASC')->select()->toArray();
    }

    /**
     * @param $end
     * @return mixed
     * @author xaboy
     * @day 2020/9/16
     */
    public function getFinishTimeoutIds($end)
    {
        return StoreOrderStatus::getDB()->alias('A')->leftJoin('StoreOrder B', 'A.order_id = B.order_id')
            ->where('A.change_type', 'take')
            ->where('A.change_time', '<', $end)->where('B.paid', 1)->where('B.status', 2)
            ->column('A.order_id');
    }


    /**
     * TODO 参与人数
     * @param array $data
     * @param int|null $uid
     * @return BaseQuery
     * @author Qinii
     * @day 2020-11-11
     */
    public function getTattendCount(array $data, ?int $uid)
    {
        $query = StoreOrderOffline::hasWhere('orderProduct', function ($query) use ($data, $uid) {
            $query->when(isset($data['activity_id']), function ($query) use ($data) {
                $query->where('activity_id', $data['activity_id']);
            })
                ->when(isset($data['product_sku']), function ($query) use ($data) {
                    $query->where('product_sku', $data['product_sku']);
                })
                ->when(isset($data['product_id']), function ($query) use ($data) {
                    $query->where('product_id', $data['product_id']);
                })
                ->when(isset($data['exsits_id']), function ($query) use ($data) {
                    switch ($data['product_type']) {
                        case 3:
                            $make = app()->make(ProductAssistSetRepository::class);
                            $id = 'product_assist_id';
                            break;
                        case 4:
                            $make = app()->make(ProductGroupBuyingRepository::class);
                            $id = 'product_group_id';
                            break;
                    }
                    $where = [$id => $data['exsits_id']];
                    $activity_id = $make->getSearch($where)->column($make->getPk());
                    if ($activity_id) {
                        $id = array_unique($activity_id);
                        $query->where('activity_id', 'in', $id);
                    } else {
                        $query->where('activity_id', '<', 0);
                    }
                })
                ->where('product_type', $data['product_type']);
            if ($uid) $query->where('uid', $uid);
        });
        $query->where('activity_type', $data['product_type']);
        switch ($data['product_type']) {
            case 0:
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('paid', 1);
                    })->whereOr(function ($query) {
                        $query->where('paid', 0)->where('is_del', 0);
                    });
                });
                break;
            case 1: //秒杀
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('paid', 1);
                    })->whereOr(function ($query) {
                        $query->where('paid', 0)->where('is_del', 0);
                    });
                })->when(isset($data['day']), function ($query) use ($data) {
                    $query->whereDay('StoreOrder.create_time', $data['day']);
                });
                break;
            case 2: //预售

                /**
                 * 第一阶段参与人数：所有人
                 * 第二阶段参与人数：支付了第一阶段
                 */
                //第二阶段
                if ($data['type'] == 1) {
                    $query->where(function ($query) {
                        $query->where('paid', 1)->whereOr(function ($query) {
                            $query->where('paid', 0)->where('is_del', 0);
                        });
                    });
                }
                if ($data['type'] == 2) $query->where('paid', 1)->where('status', 'in', [0, 1, 2, 3, -1]);
                break;
            case 3: //助力
                $query->where(function ($query) {
                    $query->where('paid', 1)->whereOr(function ($query) {
                        $query->where('paid', 0)->where('is_del', 0);
                    });
                });
                break;
            case 4: //
                $query->where(function ($query) {
                    $query->where('paid', 1)->whereOr(function ($query) {
                        $query->where('paid', 0)->where('is_del', 0);
                    })
                        ->where('status', '>', -1);
                });
                break;
        }
        return $query;
    }

    /**
     *  未使用
     * TODO 成功支付人数
     * @param int $productType
     * @param int $activityId
     * @param int|null $uid
     * @param int|null $status
     * @author Qinii
     * @day 2020-10-30
     */
    public function getTattendSuccessCount($data, ?int $uid)
    {
        $query = StoreOrderOffline::hasWhere('orderProduct', function ($query) use ($data, $uid) {
            $query->when(isset($data['activity_id']), function ($query) use ($data) {
                $query->where('activity_id', $data['activity_id']);
            })
                ->when(isset($data['product_sku']), function ($query) use ($data) {
                    $query->where('product_sku', $data['product_sku']);
                })
                ->when(isset($data['product_id']), function ($query) use ($data) {
                    $query->where('product_id', $data['product_id']);
                })
                ->when(isset($data['exsits_id']), function ($query) use ($data) {
                    switch ($data['product_type']) {
                        case 3:
                            $make = app()->make(ProductAssistSetRepository::class);
                            $id = 'product_assist_id';
                            break;
                        case 4:
                            $make = app()->make(ProductGroupBuyingRepository::class);
                            $id = 'product_group_id';
                            break;
                    }
                    $where = [$id => $data['exsits_id']];
                    $activity_id = $make->getSearch($where)->column($make->getPk());
                    if ($activity_id) {
                        $id = array_unique($activity_id);
                        $query->where('activity_id', 'in', $id);
                    } else {
                        $query->where('activity_id', '<', 0);
                    }
                })
                ->where('product_type', $data['product_type']);
            if ($uid) $query->where('uid', $uid);
        });
        $query->where('activity_type', $data['product_type'])->where('paid', 1);

        switch ($data['product_type']) {
            case 1: //秒杀
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('paid', 1);
                    });
                })->when(isset($data['day']), function ($query) use ($data) {
                    $query->whereDay('StoreOrder.create_time', $data['day']);
                });
                break;
            case 2: //预售
                if ($data['type'] == 1) {    //第一阶段
                    $query->where('status', 'in', [0, 1, 2, 3, 10]);
                } else {        //第二阶段
                    $query->where('status', 'in', [0, 1, 2, 3]);
                }
                break;
            case 3: //助力
                break;
            case 4:
                break;
        }
        return $query;
    }


    /**
     * TODO 获取退款单数量
     * @param $where
     * @return mixed
     * @author Qinii
     * @day 1/4/21
     */
    public function getSeckillRefundCount($where, $type = 1)
    {
        $query = StoreOrderProduct::getDB()->alias('P')->join('StoreRefundOrder R', 'P.order_id = R.order_id');
        $query->join('StoreOrder O', 'O.order_id = P.order_id');
        $query
            ->when(isset($where['activity_id']), function ($query) use ($where) {
                $query->where('P.activity_id', $where['activity_id']);
            })
            ->when(isset($where['product_sku']), function ($query) use ($where) {
                $query->where('P.product_sku', $where['product_sku']);
            })
            ->when(isset($where['day']), function ($query) use ($where) {
                $query->whereDay('P.create_time', $where['day']);
            })
            ->when($type == 1, function ($query) use ($where) {
                $query->where('O.verify_time', null)->where('O.delivery_type', null);
            }, function ($query) {
                $query->where('R.refund_type', 2);
            })
            ->where('P.product_type', 1)->where('R.status', 3);
        return $query->sum('R.refund_num');
    }


    /**
     * TODO 用户的某个商品购买数量
     * @param int $uid
     * @param int $productId
     * @return int
     * @author Qinii
     * @day 2022/9/26
     */
    public function getMaxCountNumber(int $uid, int $productId)
    {
//        return StoreOrderOffline::hasWhere('orderProduct',function($query) use($productId){
//            $query->where('product_id', $productId);
//        })
//        ->where(function($query) {
//            $query->where('is_del',0)->whereOr(function($query){
//                $query->where('is_del',1)->where('paid',1);
//            });
//        })->where('StoreOrder.uid',$uid)->select()
//       ;
        return (int)StoreOrderProduct::hasWhere('orderInfo', function ($query) use ($uid) {
            $query->where('uid', $uid)->where(function ($query) {
                $query->where('is_del', 0)->whereOr(function ($query) {
                    $query->where('is_del', 1)->where('paid', 1);
                });
            });
        })->where('product_id', $productId)->sum('product_num');
    }

    public function getOrderSn($order_id)
    {
        return StoreOrderOffline::getDB()->where($this->getPk(), $order_id)->value('order_sn', '');
    }

    public function getSubOrderNotSend(int $group_order_id, int $order_id)
    {
        return StoreOrderOffline::getDB()->where('group_order_id', $group_order_id)->where('status', 0)->where('order_id', '<>', $order_id)->count();
    }


    /**
     * 获取商户排行
     * @param string $date
     * @param string $type
     * @param string $sort
     * @return mixed
     */
    public function getMerchantTop(string $date, string $type = 'sales', string $sort = 'desc')
    {

        $query = StoreOrderOffline::getDB()->with(['merchant' => function ($query) {
            $query->field('mer_id,mer_name,mer_avatar');
        }]);
        $query = getModelTime($query, $date)->where('paid', 1)->where('mer_id', '>', 0)->field('mer_id,sum(total_num) as sales,sum(pay_price + pay_postage) as price')->group('mer_id');
        switch ($type) {
            case 'sales':
                $query->order('sales ' . $sort);
                break;
            case 'price':
                $query->order('price ' . $sort);
                break;
        }

        return $query->limit(0, 10)->select()->toArray();
    }
}

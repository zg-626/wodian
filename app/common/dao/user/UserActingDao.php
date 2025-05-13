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


namespace app\common\dao\user;


use app\common\dao\BaseDao;
use app\common\model\user\UserGroupApply;
use app\common\model\user\UserGroupApply as model;
use think\db\BaseQuery;

class UserActingDao extends BaseDao
{
    /**
     * @return string
     * @author Qinii
     */
    protected function getModel(): string
    {
        return model::class;
    }


    public function userFieldExists($field, $value,$uid): bool
    {
        return (($this->getModel()::getDB())->where('uid',$uid)->where($field,$value)->count()) > 0;
    }

    public function changeDefault(int $uid)
    {
        return ($this->getModel()::getDB())->where('uid',$uid)->update(['is_default' => 0]);
    }

    public function getAll(int $uid)
    {
        return (($this->getModel()::getDB())->where('uid',$uid));
    }

    /**
     * @param array $where
     * @return BaseQuery
     * @author xaboy
     * @day 2020/5/28
     */
    public function search(array $where)
    {
        return UserGroupApply::getDB()->when(isset($where['uid']) && $where['uid'] !== '', function ($query) use ($where) {
            $query->where('uid', $where['uid']);
        })->when(isset($where['keyword']) && $where['keyword'] !== '', function ($query) use ($where) {
            $query->whereLike('content|reply|remake|realname|contact', '%'.$where['keyword'].'%');
        })->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
            $query->where('type',$where['type']);
        })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
            $query->where('status', $where['status']);
        })->when(isset($where['realname']) && $where['realname'] !== '', function ($query) use ($where) {
            $query->where('realname','like', '%'.$where['realname'].'%');
        })->when(isset($where['is_del']) && $where['is_del'] !== '', function ($query) use ($where) {
            $query->where('is_del',$where['is_del']);
        })->order('create_time DESC');
    }
}

<?php

namespace app\common\dao\system\operate;

use app\common\dao\BaseDao;
use app\common\model\BaseModel;
use app\common\model\system\operate\OperateLog;

class OperateLogDao extends BaseDao
{
    /**
     * @return BaseModel
     * @author xaboy
     * @day 2020-03-30
     */
    protected function getModel(): string
    {
        return OperateLog::class;
    }

    public function search($where)
    {
        return $this->getModel()::getDb()
            ->when(isset($where['type']) && $where['type'] != '', function ($query) use ($where) {
                $query->where('type', $where['type']);
            })
            ->when(isset($where['category']) && $where['category'] != '', function ($query) use ($where) {
                $query->where('category', $where['category']);
            })
            ->when(isset($where['relevance_title']) && $where['relevance_title'] != '', function ($query) use ($where) {
                $query->whereLike('relevance_title', "%{$where['relevance_title']}%");
            })
            ->when(isset($where['operator_nickname']) && $where['operator_nickname'] != '', function ($query) use ($where) {
                $query->whereLike('operator_nickname', "%{$where['operator_nickname']}%");
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] != '', function ($query) use ($where) {
                $query->where('mer_id', $where['mer_id']);
            })
            ->when(isset($where['date']) && $where['date'] != '', function ($query) use ($where) {
                getModelTime($query, $where['date']);
            })
            ->when(isset($where['relevance_id']) && $where['relevance_id'] != '', function ($query) use ($where) {
                $query->where('relevance_id', $where['relevance_id']);
            })
            ->when(isset($where['relevance_type']) && $where['relevance_type'] != '', function ($query) use ($where) {
                if (is_array($where['relevance_type'])) {
                    $query->whereIn('relevance_id', $where['relevance_id']);
                } else {
                    $query->where('relevance_id', $where['relevance_id']);
                }
            })
            ->when(isset($where['relevance_title']) && $where['relevance_title'] != '', function ($query) use ($where) {
                $query->where('relevance_title', $where['relevance_title']);
            });
    }
}

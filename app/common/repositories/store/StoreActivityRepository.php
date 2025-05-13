<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------
namespace app\common\repositories\store;

use app\common\dao\store\StoreActivityDao;
use app\common\model\store\StoreActivity;
use app\common\repositories\BaseRepository;
use app\common\repositories\store\product\ProductLabelRepository;
use app\common\repositories\store\product\SpuRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\RelevanceRepository;
use crmeb\services\QrcodeService;
use Exception;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;

/**
 * @mixin StoreActivityDao
 */
class StoreActivityRepository extends BaseRepository
{
    //氛围图
    const ACTIVITY_TYPE_ATMOSPHERE = 1;
    //活动边框
    const ACTIVITY_TYPE_BORDER = 2;
    //报名活动
    const ACTIVITY_TYPE_FORM = 4;

    //指定范围类型
    //0全部商品
    const TYPE_ALL = 0;
    //指定商品
    const TYPE_MUST_PRODUCT = 1;
    //指定分类
    const TYPE_MUST_CATEGORY = 2;
    //指定商户
    const TYPE_MUST_STORE = 3;
    //指定商品标签
    const TYPE_MUST_PRODUCT_LABEL = 4;

    /**
     * @var StoreActivityDao
     */
    protected $dao;

    /**
     * StoreActivityDao constructor.
     * @param StoreActivityDao $dao
     */
    public function __construct(StoreActivityDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList($where, $page, $limit)
    {
        $query = $this->dao->search($where)->append(['time_status']);
        $count = $query->count();
        $list = $query->page($page, $limit)->order('sort DESC,activity_id DESC')->select();
        return compact('count', 'list');
    }

    /**
     *  创建活动
     * @param array $data
     * @param $extend
     * @param $func
     * @author Qinii
     * @day 2023/10/13
     */
    public function createActivity(array $data, $extend = null, $func = null)
    {
        $paramsData = $this->getParams($data, $extend);
        Db::transaction(function () use ($data, $extend, $func, $paramsData) {
            $createData = $this->dao->create($data);
            if (isset($paramsData['ids']) && !empty($paramsData['ids']))
                app()->make(RelevanceRepository::class)->createMany($createData->activity_id, $paramsData['ids'], $paramsData['type']);
            if ($func && function_exists($func)) $this->$func($createData, $extend);
        });
    }

    /**
     * 整理关联参数
     * @param $data
     * @param $extend
     * @return array
     * @author Qinii
     * @day 2023/10/13
     */
    public function getParams($data, $extend)
    {
        if (!$extend) return [];
        $res = [];
        $type = '';
        switch ($data['scope_type']) {
            case self::TYPE_ALL;
                break;
            case self::TYPE_MUST_PRODUCT:
                if (!isset($extend['spu_ids']) || empty($extend['spu_ids'])) throw new ValidateException('请选择指定商品');
                $res = app()->make(SpuRepository::class)->getSearch(['spu_ids' => $extend['spu_ids'], 'status' => 1])->column('spu_id');
                $type = RelevanceRepository::SCOPE_TYPE_PRODUCT;
                break;
            case self::TYPE_MUST_CATEGORY:
                if (!isset($extend['cate_ids']) || empty($extend['cate_ids'])) throw new ValidateException('请选择指定商品分类');
                $res = app()->make(StoreCategoryRepository::class)->getSearch(['ids' => $extend['cate_ids'], 'status' => 1])->column('store_category_id');
                $type = RelevanceRepository::SCOPE_TYPE_CATEGORY;
                break;
            case self::TYPE_MUST_STORE:
                if (!isset($extend['mer_ids']) || empty($extend['mer_ids'])) throw new ValidateException('请选择指定商户');
                $res = app()->make(MerchantRepository::class)->getSearch(['mer_ids' => $extend['mer_ids']])->column('mer_id');
                $type = RelevanceRepository::SCOPE_TYPE_STORE;
                break;
            case self::ACTIVITY_TYPE_FORM:
                if (!isset($extend['label_ids']) || empty($extend['label_ids'])) throw new ValidateException('请选择指定商品标签');
                $res = app()->make(ProductLabelRepository::class)->getSearch(['product_label_id' => $extend['label_ids']])->column('product_label_id');
                $type = RelevanceRepository::SCOPE_TYPE_PRODUCT_LABEL;
                break;
        }
        $ids = array_unique($res);
        return compact('ids', 'type');
    }

    public function updateActivity(int $id, array $data, $extend = null, $func = null)
    {
        $paramsData = $this->getParams($data, $extend);
        Db::transaction(function () use ($id, $data, $extend, $func, $paramsData) {
            $createData = $this->dao->update($id, $data);
            if (!empty($paramsData)) {
                app()->make(RelevanceRepository::class)->clear($id, $paramsData['type'], 'left_id');
                if (isset($paramsData['ids']) && !empty($paramsData['ids']))
                    app()->make(RelevanceRepository::class)->createMany($id, $paramsData['ids'], $paramsData['type']);
            }
            if ($func && function_exists($func)) $this->$func($createData, $extend);
        });
    }

    /**
     * TODO 详情
     * @param $where
     * @param $page
     * @param $limit
     * @return array
     * @author Qinii
     * @day 2022/9/17
     */
    public function getAdminList($where, $page, $limit, array $with = [])
    {
        $query = $this->dao->search($where, $with)->order('sort DESC,activity_id DESC');
        $count = $query->count();
        $list = $query->page($page, $limit)->select()->append(['time_status']);
        return compact('count', 'list');
    }

    /**
     * TODO 详情
     * @param $id
     * @return array
     * @author Qinii
     * @day 2022/9/16
     */
    public function detail($id, $type = true)
    {
        $with = [];
        if ($type) $with[] = 'socpeData';
        $data = $this->dao->getSearch([$this->dao->getPk() => $id])->with($with)->append(['time_status'])->find()->toArray();
        if ($type) {
            try {
                $arr = array_column($data['socpeData'], 'right_id');
                if ($data['scope_type'] == self::TYPE_MUST_CATEGORY) {
                    $data['cate_ids'] = $arr;
                } else if ($data['scope_type'] == self::TYPE_MUST_STORE) {
                    $data['mer_ids'] = $arr;
                } else if ($data['scope_type'] == self::TYPE_MUST_PRODUCT_LABEL) {
                    $data['label_ids'] = $arr;
                } else {
                    $data['spu_ids'] = $arr;
                }
            } catch (Exception $e) {
            }
            unset($data['socpeData']);
        }
        return $data;
    }

    /**
     * TODO 删除活动
     * @param $id
     * @return mixed
     * @author Qinii
     * @day 2022/9/17
     */
    public function deleteActivity($id)
    {
        return Db::transaction(function () use ($id) {
            $this->dao->delete($id);
            app()->make(RelevanceRepository::class)->clear($id, [RelevanceRepository::SCOPE_TYPE_PRODUCT, RelevanceRepository::SCOPE_TYPE_STORE, RelevanceRepository::SCOPE_TYPE_CATEGORY], 'left_id');
        });
    }

    public function getActivityBySpu(int $type, $spuId, $cateId, $merId, $labelId)
    {
        $make = app()->make(RelevanceRepository::class);
        $list = $this->dao->getSearch(['activity_type' => $type, 'status' => 1, 'is_show' => 1, 'gt_end_time' => date('Y-m-d H:i:s', time())])
            ->setOption('field', [])
            ->field('activity_id,scope_type,activity_type,pic')
            ->order('create_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $item) {
            switch ($item['scope_type']) {
                case self::TYPE_ALL:
                    return $item;
                case self::TYPE_MUST_PRODUCT:
                    $_type = RelevanceRepository::SCOPE_TYPE_PRODUCT;
                    $right_id = $spuId ?: 0;
                    break;
                case self::TYPE_MUST_CATEGORY:
                    $_type = RelevanceRepository::SCOPE_TYPE_CATEGORY;
                    $right_id = $cateId ?: 0;
                    break;
                case self::TYPE_MUST_STORE:
                    $_type = RelevanceRepository::SCOPE_TYPE_STORE;
                    $right_id = $merId ?: 0;
                    break;
                case self::TYPE_MUST_PRODUCT_LABEL:
                    $_type = RelevanceRepository::SCOPE_TYPE_PRODUCT_LABEL;
                    $right_id = $labelId ?: '';
                    break;
            }
            if (isset($_type)) {
                $res = $make->checkHas($item['activity_id'], $right_id, $_type);
                if ($res) return $item;
            }
        }
        return [];
    }

    public function wxQrcode(int $uid, StoreActivity $activity)
    {
        $name = md5('wxactform_i' . $uid . $activity['activity_id'] . date('Ymd')) . '.jpg';
        $key = 'form_' . $activity['activity_id'] . '_' . $uid;
        return app()->make(QrcodeService::class)->getWechatQrcodePath($name, '/pages/activity/registrate_activity/index?id=' . $activity['activity_id'] . '&spid=' . $uid, false, $key);
    }

    public function mpQrcode(int $uid, StoreActivity $activity)
    {
        $name = md5('mpactform_i' . $uid . $activity['activity_id'] . date('Ymd')) . '.jpg';

        return app()->make(QrcodeService::class)->getRoutineQrcodePath($name, 'pages/activity/registrate_activity/index', 'id=' . $activity['activity_id'] . 'spid=' . $uid);
    }

    /**
     * 验证活动状态
     * @param StoreActivity|null $activity
     * @return true
     */
    public function verifyActivityStatus(?StoreActivity $activity, $isCreate = false)
    {
        if (empty($activity)) {
            throw new ValidateException('活动数据异常');
        }
        if (!$activity['is_show']) {
            throw new ValidateException('活动已被关闭');
        }
        if ($isCreate) {
            if ($activity['status'] == -1) {
                throw new ValidateException('活动已结束');
            }
            if ($activity['status'] == 0) {
                throw new ValidateException('活动未开始');
            }
            //如果存在结束时间，则判断当前时间是否大于结束时间
            $end_time = $activity['end_time'] ? (strtotime($activity['end_time']) <= time() ?: false) : false;
            if ($end_time)
                throw new ValidateException('活动已结束');
            //如果没有结束时间 则判断总人数
            if ($activity['count'] > 0 && $activity['count'] <= $activity['total'])
                throw new ValidateException('活动参与人数已满');
        }
        return true;
    }

    public function verifyActivityData(int $uid, int $activity_id)
    {
        $formRelated = app()->make(StoreActivityRelatedRepository::class);
        $createData = [
            'uid' => $uid,
            'activity_id' => $activity_id,
            'activity_type' => $formRelated::ACTIVITY_TYPE_FORM,
        ];
        return $formRelated->getSearch($createData)->value('id');
    }
}

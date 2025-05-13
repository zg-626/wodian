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
use app\common\dao\store\StoreActivityRelatedDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\form\FormRepository;
use think\exception\ValidateException;
use think\facade\Db;

/**
 * @mixin StoreActivityRelatedDao
 */
class StoreActivityRelatedRepository extends BaseRepository
{
    const ACTIVITY_TYPE_FORM = 1;

    /**
     * @var StoreActivityRelatedDao
     */
    protected $dao;

    /**
     * StoreActivityDao constructor.
     * @param StoreActivityRelatedDao $dao
     */
    public function __construct(StoreActivityRelatedDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList($where, $page, $limit)
    {
        $query = $this->dao->search($where)->with([
            'activity' => function ($query) {
                $query->field('activity_id,activity_name,status,activity_type,pic,start_time,end_time')->append(['time_status']);
            }
        ]);
        $count = $query->count();
        $list = $query->page($page, $limit)->order('id DESC,create_time DESC')->select();
        return compact('count', 'list');
    }

    public function show($id, $uid)
    {
        $where['id'] = $id;
        $where['uid'] = $uid;
        $data = $this->dao->getSearch($where)->with([
            'activity' => function ($query) {
                $query->append(['time_status']);
            }
        ])->find();
        if (!$data) throw new ValidateException('数据不存在');
        $form = $data['activity'];
        if (!$form) throw new ValidateException('活动不存在或无法查看');
        return compact('data');
    }

    /**
     *  保存提交信息，增加已提交数量
     * @param array $data
     * @return mixed
     * @author Qinii
     * @day 2023/11/22
     */
    public function save(int $activity_id, array $data)
    {
        return Db::transaction(function () use ($activity_id, $data) {
            $this->dao->create($data);
            app()->make(StoreActivityRepository::class)->incTotal($activity_id);
        });
    }
}

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


namespace app\common\repositories\user;

use app\common\repositories\BaseRepository;
use app\common\dao\user\UserActingDao as dao;
use app\common\repositories\store\shipping\CityRepository;
use app\common\repositories\system\config\ConfigValueRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\merchant\MerchantTypeRepository;
use crmeb\jobs\SendSmsJob;
use FormBuilder\Factory\Elm;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Queue;
use think\facade\Route;

/**
 * Class UserRepositoryRepository
 * @package app\common\repositories\user
 * @day 2020/6/3
 * @mixin dao
 */
class UserActingRepository extends BaseRepository
{
    /**
     * @var dao
     */
    protected $dao;


    /**
     * UserRepositoryRepository constructor.
     * @param dao $dao
     */
    public function __construct(dao $dao)
    {
        $this->dao = $dao;
    }


    /**
     * @param int $id
     * @param int $uid
     * @return bool
     * @author Qinii
     */
    public function fieldExists(int $id, int $uid)
    {
        return $this->dao->userFieldExists($this->dao->getPk(), $id, $uid);
    }

    /**
     * @param int $uid
     * @return bool
     * @author Qinii
     */
    public function defaultExists(int $uid)
    {
        return $this->dao->userFieldExists('is_default', 1, $uid);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Qinii
     */
    public function checkDefault(int $id)
    {
        $res = $this->dao->getWhere([$this->dao->getPk() => $id]);
        return $res['is_default'];
    }

    /**
     * @param $province
     * @param $city
     * @return mixed
     * @author Qinii
     */
    public function getCityId($province, $city)
    {
        $make = app()->make(CityRepository::class);
        $provinceData = $make->getWhere(['name' => $province]);
        $cityData = $make->getWhere(['name' => $city, 'parent_id' => $provinceData['city_id']]);
        if (!$cityData) $cityData = $make->getWhere([['name', 'like', '直辖' . '%'], ['parent_id', '=', $provinceData['city_id']]]);
        return $cityData['city_id'];
    }

    /**
     * @param $uid
     * @param $page
     * @param $limit
     * @return array
     * @author Qinii
     */
    public function getList($uid, $page, $limit)
    {
        /*$query = $this->dao->getWhere(['uid' => $uid])->with();
        $count = $query->count();
        $list = $query->page($page, $limit)->order('id desc')->select();
        return compact('count','list');*/

        $query = $this->dao->search(['uid', $uid])->with(
            [
                'group' => function ($query) {
                $query->field('group_id,group_name');
                },
                'user' => function ($query) {
                    $query->field('uid,nickname,phone');
                },]
        );
        $count = $query->count();
        $list = $query->page($page, $limit)->withAttr('images', function ($val) {
            return $val ? json_decode($val, true) : [];
        })->select();
        return compact('count', 'list');
    }

    /**
     * @param $id
     * @param $uid
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Qinii
     */
    public function get($id, $uid)
    {
        return $this->dao->getWhere(['address_id' => $id, 'uid' => $uid])->append(['area']);
    }

    public function markForm($id)
    {
        $data = $this->dao->get($id);
        $form = Elm::createForm(Route::buildUrl('userActingMarkForm', ['id' => $id])->build());
        $form->setRule([
            Elm::textarea('mark', '备注：', $data['mark'])->placeholder('请输入备注')->required(),
        ]);
        return $form->setTitle('修改备注');
    }

    public function statusForm($id)
    {
        $form = Elm::createForm(Route::buildUrl('userActingStatus', ['id' => $id])->build());
        $form->setRule([
            Elm::select('status', '审核状态：', 1)->options([
                ['value' => 1, 'label' => '同意'],
                ['value' => 2, 'label' => '拒绝'],
            ])->control([
                /*[
                    'value' => 1,
                    'rule' => [
                        Elm::radio('create_mer', '自动创建分组：', 1)->options([
                            ['value' => 1, 'label' => '创建'],
                            ['value' => 2, 'label' => '不创建'],
                        ])
                    ]
                ],*/
                [
                    'value' => 2,
                    'rule' => [
                        Elm::textarea('fail_msg', '失败原因：', '信息填写有误')->placeholder('请输入失败原因')
                    ]
                ]
            ]),
        ]);
        return $form->setTitle('修改审核状态');
    }

    public function updateStatus($id, $data)
    {
        $intention = $this->dao->get($id);
        if (!$intention)
            throw new ValidateException('信息不存在');
        if ($intention->status)
            throw new ValidateException('状态有误,修改失败');

        $margin = app()->make(UserGroupRepository::class)->get($intention['group_id']);

        if ($margin) {
            // 修改分组
            //UserRepository::class->update($intention['uid'], $data);
            /** @var UserRepository $userRepository */
            $userRepository = app()->make(UserRepository::class);
            $user = $userRepository->get($intention['uid']);
            $user->group_id = $intention['group_id'];
            $user->save();
        }
    }
}

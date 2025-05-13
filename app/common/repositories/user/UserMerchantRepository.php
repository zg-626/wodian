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


use app\common\dao\user\UserMerchantDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\config\ConfigValueRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use FormBuilder\Factory\Elm;
use think\facade\Db;
use think\facade\Route;

/**
 * Class UserMerchantRepository
 * @package app\common\repositories\user
 * @author xaboy
 * @day 2020/10/20
 * @mixin UserMerchantDao
 */
class UserMerchantRepository extends BaseRepository
{
    /**
     * UserMerchantRepository constructor.
     * @param UserMerchantDao $dao
     */
    public function __construct(UserMerchantDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, $page, $limit)
    {
        // 获取默认头像
        $user_default_avatar = app()->make(ConfigValueRepository::class)->get('user_default_avatar', 0);
        $query = $this->dao->search($where);
        $count = $query->count();
        $make = app()->make(UserLabelRepository::class);
        $list = $query->setOption('field', [])->field('A.uid,A.user_merchant_id,B.avatar,B.nickname,B.user_type,A.last_pay_time,A.first_pay_time,A.label_id,A.create_time,A.last_time,A.pay_num,A.pay_price,B.phone,B.is_svip,B.svip_endtime')
            ->page($page, $limit)->order('A.user_merchant_id DESC')->select()
            ->each(function ($item) use ($where, $make, $user_default_avatar) {
                if (env('SHOW_PHONE',false) && $item->phone && is_numeric($item->phone)){
                    if (app('request')->userType() !== 2 || app('request')->adminInfo()['level'] != 0) {
                        $item->phone = substr_replace($item->phone, '****', 3, 4);
                    }
                }
                $item->label = count($item['label_id']) ? $make->labels($item['label_id'], $where['mer_id']) : [];
                //$item->svip_endtime = date('Y-m-d H:i:s', strtotime("+100 year"));
                if (empty($item->avatar)) {
                    return $item->avatar = $user_default_avatar;
                }
                return $item;
            });

        return compact('count', 'list');
    }

    /**
     * @param $uid
     * @param $merId
     * @return \app\common\dao\BaseDao|\think\Model
     * @author xaboy
     * @day 2020/10/20
     */
    public function create($uid, $merId)
    {
        return $this->dao->create([
            'uid' => $uid,
            'mer_id' => $merId,
        ]);
    }

    /**
     * @param $uid
     * @param $mer_id
     * @return \app\common\dao\BaseDao|array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/10/20
     */
    public function getInfo($uid, $mer_id)
    {
        $user = $this->dao->getWhere(compact('uid', 'mer_id'));
        if (!$user) {
            $user = $this->create($uid, $mer_id);
        }
        return $user;
    }

    /**
     * @param $uid
     * @param $mer_id
     * @return \app\common\dao\BaseDao|array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/10/20
     */
    public function getMerUser($uid, $mer_id,$pay_price,$order_id)
    {
        // 用户信息
        $merUser = $this->dao->getWhere(compact('uid'));
        if (!$merUser)
        {
            //给该商家5%的佣金，后续消费无变化
            /** @var MerchantRepository $merchantRepository */
            $merchantRepository = app()->make(MerchantRepository::class);
            $merchantRepository->addCommission($mer_id,$pay_price,$order_id);
        }
    }

    /**
     * @param $uid
     * @param $mer_id
     * @return \app\common\dao\BaseDao|array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/10/20
     */
    public function getMerUserRefund($uid, $mer_id,$pay_price,$order_id)
    {
        // 用户信息
        $merUser = $this->dao->getWhere(compact('uid'));
        if ($merUser)
        {
            // 取消绑定
            $user = $this->delete($merUser['id']);
            //扣减商家5%的佣金，后续消费无变化
            /** @var MerchantRepository $merchantRepository */
            $merchantRepository = app()->make(MerchantRepository::class);
            $merchantRepository->subCommission($mer_id,$pay_price,$order_id);
        }
    }

    /**
     * @param $uid
     * @param $merId
     * @param $pay_price
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author xaboy
     * @day 2020/10/21
     */
    public function updatePayTime($uid, $merId, $pay_price, $flag,$order_id,$handling_fee)
    {
        // 锁客，每个用户只绑定一个商户
        $this->getMerUser($uid,$merId,$handling_fee,$order_id);
        $user = $this->getInfo($uid, $merId);
        $time = date('Y-m-d H:i:s');
        $user->last_pay_time = $time;
        if ($flag)
            $user->pay_num++;
        $user->pay_price = bcadd($user->pay_price, $pay_price, 2);
        if (!$user->first_pay_time) $user->first_pay_time = $time;
        $user->save();
    }

    public function rmLabel($id)
    {
        return $this->dao->search(['label_id' => $id])->update([
            'A.label_id' => Db::raw('(trim(BOTH \',\' FROM replace(CONCAT(\',\',A.label_id,\',\'),\',' . $id . ',\',\',\')))')
        ]);
    }

    public function changeLabelForm($merId, $id)
    {
        $user = $this->dao->get($id);

        /** @var UserLabelRepository $make */
        $userLabelRepository = app()->make(UserLabelRepository::class);
        $data = $userLabelRepository->allOptions($merId);
        return Elm::createForm(Route::buildUrl('merchantUserChangeLabel', compact('id'))->build(), [
            Elm::selectMultiple('label_id', '用户标签：', $userLabelRepository->intersection($user->label_id, $merId, 0))->options(function () use ($data) {
                $options = [];
                foreach ($data as $value => $label) {
                    $options[] = compact('value', 'label');
                }
                return $options;
            })
        ])->setTitle('修改用户标签');
    }
}

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


use app\common\dao\user\UserBrokerageDao;
use app\common\model\user\User;
use app\common\model\user\UserBrokerage;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\CacheRepository;
use FormBuilder\Factory\Elm;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Route;

/**
 * @mixin UserBrokerageDao
 */
class UserBrokerageRepository extends BaseRepository
{

    const BROKERAGE_RULE_TYPE = ['spread_user', 'pay_money', 'pay_num', 'spread_money', 'spread_pay_num'];

    public function __construct(UserBrokerageDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, $page, $limit)
    {
        $query = $this->dao->search($where)->order('brokerage_level ASC,create_time DESC');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('list', 'count');
    }

    public function getNextLevel($level,$type = 0)
    {
        return $this->search(['next_level' => $level,'type' => $type])->order('brokerage_level ASC,create_time DESC')->find();
    }

    public function options(array $where)
    {
        return $this->dao->search($where)->field('brokerage_level as value,brokerage_name as label')->order('brokerage_level ASC,create_time DESC')->select();
    }

    public function all(int $type)
    {
        return $this->dao->search(['type' => $type])->order('brokerage_level ASC,create_time DESC')->select();
    }

    public function inc(User $user, $type, $inc)
    {
        $nextLevel = $this->getNextLevel($user->brokerage_level);
        if (!$nextLevel) return false;
        $make = app()->make(UserBillRepository::class);
        $bill = $make->getWhere(['uid' => $user->uid, 'link_id' => $nextLevel->user_brokerage_id, 'category' => 'sys_brokerage', 'type' => $type]);
        if ($bill) {
            $bill->number = bcadd($bill->number, $inc, 2);
            $bill->save();
        } else {
            $make->incBill($user->uid, 'sys_brokerage', $type, [
                'number' => $inc,
                'title' => $type,
                'balance' => 0,
                'status' => 0,
                'link_id' => $nextLevel->user_brokerage_id
            ]);
        }

        return $this->checkLevel($user, $nextLevel);
    }

    public function checkLevel(User $user, UserBrokerage $nextLevel)
    {
        $info = app()->make(UserBillRepository::class)->search(['uid' => $user->uid, 'category' => 'sys_brokerage', 'link_id' => $nextLevel->user_brokerage_id])
            ->column('number', 'type');
        foreach ($nextLevel['brokerage_rule'] as $k => $rule) {
            if (!isset($info[$k]) && $rule['num'] > 0) return false;
            if ($rule['num'] > 0 && $rule['num'] > $info[$k]) return false;
        }
        $nextLevel->user_num++;
        Db::transaction(function () use ($nextLevel, $user) {
            $nextLevel->save();
            if ($user->brokerage && $user->brokerage->user_num > 0) {
                $user->brokerage->user_num--;
                $user->brokerage->save();
            }
            $user->brokerage_level = $nextLevel->brokerage_level;
            $user->save();

            $key = 'notice_brokerage_level_' . $user->uid;
            app()->make(CacheRepository::class)->save($key,$nextLevel->brokerage_level);
        });
        return true;
    }

    public function getLevelRate(User $user, UserBrokerage $nextLevel)
    {
        $info = app()->make(UserBillRepository::class)->search(['uid' => $user->uid, 'category' => 'sys_brokerage', 'link_id' => $nextLevel->user_brokerage_id])
            ->column('number', 'type');
        $brokerage_rule = $nextLevel['brokerage_rule'];
        foreach ($nextLevel['brokerage_rule'] as $k => $rule) {
            if ($rule['num'] <= 0) {
                unset($brokerage_rule[$k]);
                continue;
            }
            if (!isset($info[$k])) {
                $rate = 0;
            } else if ($rule['num'] > $info[$k]) {
                $rate = bcdiv($info[$k], $rule['num'], 2) * 100;
            } else {
                $rate = 100;
            }
            $brokerage_rule[$k]['rate'] = $rate;
            $brokerage_rule[$k]['task'] = (float)(min($info[$k] ?? 0, $rule['num']));
        }
        return $brokerage_rule;
    }

    public function form(?int $id = null)
    {
        $formData = [];
        if ($id) {
            $form = Elm::createForm(Route::buildUrl('systemUserMemberUpdate', ['id' => $id])->build());
            $data = $this->dao->get($id);
            if (!$data) throw new ValidateException('数据不存在');
            $formData = $data->toArray();

        } else {
            $form = Elm::createForm(Route::buildUrl('systemUserMemberCreate')->build());
        }

        $rules = [
            Elm::number('brokerage_level', '会员等级：')->required(),
            Elm::input('brokerage_name', '会员名称：')->placeholder('请输入会员名称')->required(),
            Elm::frameImage('brokerage_icon', '会员图标：', '/' . config('admin.admin_prefix') . '/setting/uploadPicture?field=brokerage_icon&type=1')
                ->required()
                ->value($formData['brokerage_icon'] ?? '')
                ->modal(['modal' => false])
                ->icon('el-icon-camera')
                ->width('1000px')
                ->height('600px'),
            Elm::number('value', ' 所需成长值：',$formData['brokerage_rule']['value'] ?? 0)->required(),
            Elm::frameImage('image', '背景图：', '/' . config('admin.admin_prefix') . '/setting/uploadPicture?field=image&type=1')
                ->value($formData['brokerage_rule']['image']??'')
                ->required()
                ->modal(['modal' => false])
                ->icon('el-icon-camera')
                ->width('1000px')
                ->height('600px'),
        ];
        $form->setRule($rules);
        return $form->setTitle(is_null($id) ? '添加会员等级' : '编辑会员等级')->formData($formData);
    }

    public function incMemberValue(int $uid, string $type, int $id)
    {
        if (!systemConfig('member_status')) return ;
        $make = app()->make(UserBillRepository::class);
        // 判断是否要重复添加
        if($make->ToRepeat($uid,$type,$id)){
            return;
        }
        $config = [
            'member_pay_num'   => '下单获得成长值',
            'member_sign_num'  => '签到获得成长值',
            'member_reply_num' => '评价获得成长值',
            'member_share_num' => '邀请获得成长值',
            'member_community_num'  => '社区种草内容获得成长值',
        ];
        $inc = systemConfig($type) > 0 ? systemConfig($type) : 0;
        $user = app()->make(UserRepository::class)->getWhere(['uid' => $uid],'*',['member']);
        $svip_status = $user->is_svip > 0 && systemConfig('svip_switch_status') == '1';
        if ($svip_status) {
            $svipRate = app()->make(MemberinterestsRepository::class)->getSvipInterestVal(MemberinterestsRepository::HAS_TYPE_MEMBER);
            if ($svipRate > 0) {
                $inc = bcmul($svipRate, $inc, 0);
            }
        }

        $user->member_value = $user->member_value + $inc;
        $make->incBill($user->uid, 'sys_members', $type, [
            'number'  => $inc,
            'title'   => $config[$type],
            'balance' => $user->member_value,
            'status'  => 0,
            'link_id' => $id,
            'mark' => $config[$type].':'.$inc,
        ]);

        $this->checkMemberValue($user, $inc);

    }

    /**
     * TODO 连续升级
     * @param $nextLevel
     * @param $num
     * @return array
     * @author Qinii
     * @day 1/11/22
     */
    public function upUp($nextLevel, $num, $use_value)
    {
        $newLevel = $this->getNextLevel($nextLevel->brokerage_level, 1);
        if ($newLevel) {
            $newNum = $num - $newLevel->brokerage_rule['value'];
            if ($newNum > 0) {
                $use_value += $newLevel->brokerage_rule['value'];
                [$nextLevel,$num,$use_value] = $this->upUp($newLevel, $newNum, $use_value);
            }
        }
        return [$nextLevel,$num,$use_value];
    }

    /**
     * TODO 升级操作
     * @param User $user
     * @param int $inc
     * @author Qinii
     * @day 1/11/22
     */
    public function checkMemberValue(User $user, int $inc)
    {
        /**
         * 下一级所需经验值
         * 当前的经验值加上增加经验值是否够升级
         */
        $nextLevel = $this->getNextLevel($user->member_level, 1);
        if (!$nextLevel) return $user;
        $user = Db::transaction(function () use ($inc, $user,$nextLevel) {
            if ($user->member_value >= $nextLevel->brokerage_rule['value']) {
                $num = $user->member_value - $nextLevel->brokerage_rule['value'];
                $use_value = $nextLevel->brokerage_rule['value'];  // 升级消耗成长值
                if ($num > 0) {
                    [$nextLevel,$num,$use_value] = $this->upUp($nextLevel, $num, $use_value);
                }
                if ($user->member) {
                    $user->member->user_num--;
                    $user->member->save();
                }
                $nextLevel->user_num++;
                $nextLevel->save();
                $user->member_level = $nextLevel->brokerage_level;
                $key = 'notice_member_level_' . $user->uid;
                app()->make(CacheRepository::class)->save($key,$nextLevel->brokerage_level);
                // 添加升级所需成长值记录
                app()->make(UserBillRepository::class)->decBill($user->uid, 'sys_members', 'member_upgrade', [
                    'number' => $use_value,
                    'title' => '升级消耗成长值',
                    'balance' => $num,
                    'status' => 0,
                    'mark' => '升级消耗成长值' . ':' . $use_value,
                ]);
            } else {
                $num = $user->member_value;
            }
            $user->member_value = $num;
            $user->save();
            return $user;
        });

        return $user;
    }
}

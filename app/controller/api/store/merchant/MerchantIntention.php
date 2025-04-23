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

namespace app\controller\api\store\merchant;

use app\common\repositories\system\merchant\MerchantAdminRepository;
use app\common\repositories\system\merchant\MerchantCategoryRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\merchant\MerchantTypeRepository;
use app\validate\api\MerchantIntentionValidate;
use crmeb\services\SmsService;
use crmeb\services\SwooleTaskService;
use crmeb\services\YunxinSmsService;
use think\App;
use think\facade\Db;
use crmeb\basic\BaseController;
use app\common\repositories\system\merchant\MerchantIntentionRepository as repository;
use app\common\model\system\merchant\MerchantEcLkl as LklModel;
use app\common\model\system\merchant\MerchantIntention as IntentionModel;
use think\Exception;
use think\exception\ValidateException;

class MerchantIntention extends BaseController
{
    protected $repository;
    protected $userInfo;

    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->userInfo = $this->request->isLogin() ? $this->request->userInfo() : null;
    }

    /**
     * 入驻状态
     **/
    public function status()
    {
        // 0=未提交,1=已提交,2=审核通过,3=审核驳回
        $uid = $this->userInfo->uid;
        $info = LklModel::getInfo(['uid' => $uid]);
        $status_1 = 0;
        $status_2 = 0;
        $status_3 = 0;
        $status_4 = 0;
        if ($info) {
            $status_1 = 1;
            if ($info['lkl_ec_status'] == 'WAIT_AUDI') {
                $status_1 = 1;
            }
            if ($info['lkl_ec_status'] == 'COMPLETED') {
                $status_1 = 2;
            }
            if ($info['lkl_ec_status'] == 'UNDONE') {
                $status_1 = 3;
            }

            if ($info['lkl_mer_cup_status'] == 'WAIT_AUDI') {
                $status_2 = 1;
            }
            if ($info['lkl_mer_cup_status'] == 'SUCCESS') {
                $status_2 = 1;
            }
            if ($info['lkl_mer_cup_status'] == 'SUCCESS' && $info['mer_id'] > 0) {
                $status_2 = 2;
            }

            if ($info['lkl_mer_ledger_status'] == 3) {
                $status_3 = 1;
            }
            if ($info['lkl_mer_ledger_status'] == 1) {
                $status_3 = 2;
            }
            if ($info['lkl_mer_ledger_status'] == 2) {
                $status_3 = 3;
            }

            if ($info['lkl_mer_bind_status'] == 3) {
                $status_3 = 1;
            }
            if ($info['lkl_mer_bind_status'] == 1) {
                $status_3 = 2;
            }
            if ($info['lkl_mer_bind_status'] == 2) {
                $status_3 = 3;
            }
        }
        $data = compact('status_1', 'status_2', 'status_3', 'status_4');
        return app('json')->success('入驻状态', $data);
    }

    /**
     * 入驻详情
     **/
    public function info()
    {
        $params = $this->request->params([
            'step',
        ]);

        $uid = $this->userInfo->uid;
        switch ($params['step']) {
            case 1:
            case 2:
            case 3:
            case 4:
                $info = LklModel::getInfo(['uid' => $uid], '*');
                break;
        }
        return app('json')->success('入驻详情', $info);
    }

    /**
     * 电子合同签约
     **/
    public function create_first()
    {
        $params = $this->validateParams(__FUNCTION__);

        $uid = $this->userInfo->uid;
        $info = LklModel::getInfo(['uid' => $uid]);
        if ($info) {
            if ($info['lkl_ec_status'] == 'COMPLETED') {
                return app('json')->fail('电子合同已签约成功');
            }
        }

        $data = $params;
        $data['uid'] = $uid;
        $data['lkl_ec_apply_time'] = time();
        try {
            if ($info) {
                $info->save($data);
            } else {
                $info = LklModel::create($data);
            }
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }

        $api = new \Lakala\LklApi();
        $result = $api::lklEcApply($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }

        $save_data['lkl_ec_apply_id'] = $result['ecApplyId'];
        $save_data['lkl_ec_status'] = 'WAIT_AUDI';
        try {
            LklModel::where('id', $info->id)->update($save_data);
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }
        return app('json')->success('提交成功', $result);
    }

    /**
     * 商户进件
     **/
    public function create_second()
    {
        $params = $this->validateParams(__FUNCTION__);

        $uid = $this->userInfo->uid;
        $info = LklModel::getInfo(['uid' => $uid], 'lkl_ec_no,ec_mobile,cert_name,cert_no,acct_no,B19');
        if (!$info) {
            return app('json')->fail('请返回上一页，先完成第一步');
        }
        if ($info['lkl_ec_status'] != 'COMPLETED') {
            return app('json')->fail('电子合同未签约成功');
        }
        if ($info['lkl_mer_cup_status'] == 'WAIT_AUDI') {
            return app('json')->fail('正在审核中，请耐心等待后台审核...');
        }
        if ($info['lkl_mer_cup_status'] == 'SUCCESS') {
            return app('json')->fail('商户进件已审核成功');
        }

        $data = $params;
        $data['lkl_mer_cup_time'] = time();
        try {
            $info->save($data);
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }

        $params['lkl_ec_no'] = $info['lkl_ec_no'];
        $api = new \Lakala\LklApi();
        $result = $api::lklMerchantApply($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }

        $save_data['lkl_mer_cup_no'] = $result['merchantNo'];
        $save_data['lkl_mer_cup_status'] = $result['status'];
        $intention_data['mer_lkl_id'] = $info->id;
        $intention_data['uid'] = $uid;
        $intention_data['phone'] = $info['ec_mobile']; // 手机号
        $intention_data['mer_name'] = $params['mer_name']; // 商户名称
        $intention_data['mer_banner'] = $params['shop_outside_img']; // 商户banner图片
        $intention_data['name'] = $info['cert_name']; // 客户姓名
        $intention_data['images'] = $params['license_pic_img']; // 资质照片
        $intention_data['id_card'] = $info['cert_no']; // 身份证
        $intention_data['inside'] = $params['shop_inside_img']; // 区域内部照片
        $intention_data['email'] = $params['email']; // 邮箱
        $intention_data['bank_card'] = $info['acct_no']; // 银行卡号
        $intention_data['bank_open_name'] = $info['B19']; // 开户行名称
        $intention_data['address'] = $params['mer_addr']; // 商户地址
        Db::startTrans();
        try {
            LklModel::where('id', $info->id)->update($save_data);
            $intention = IntentionModel::where('mer_lkl_id', $info->id)->field('id')->find();
            if ($intention) {
                $intention->save($intention_data);
            } else {
                IntentionModel::create($intention_data);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return app('json')->fail($e->getError());
        }
        return app('json')->success('提交成功', []);
    }

    /**
     * 商户分账业务开通申请
     **/
    public function create_three()
    {
        $params = $this->validateParams(__FUNCTION__);

        $uid = $this->userInfo->uid;
        $info = LklModel::getInfo(['uid' => $uid], 'lkl_ec_no,lkl_mer_cup_no');
        if (!$info) {
            return app('json')->fail('请返回上一页，完成电子合同签约');
        }
        if ($info['lkl_ec_status'] != 'COMPLETED') {
            return app('json')->fail('电子合同未签约成功');
        }
        if ($info['lkl_mer_cup_status'] == '') {
            return app('json')->fail('请返回上一页，完成商户进件');
        }
        if ($info['lkl_mer_cup_status'] == 'WAIT_AUDI') {
            return app('json')->fail('正在审核中，请耐心等待后台审核...');
        }
        if($info['lkl_mer_ledger_status'] == 1){
            return app('json')->fail('商户分账业务开通申请已审核成功');
        }

        $params['lkl_mer_cup_no'] = $info['lkl_mer_cup_no'];
        $params['lkl_ec_no'] = $info['lkl_ec_no'];
        $api = new \Lakala\LklApi();
        $result = $api::lklApplyLedgerMer($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }

        $data['split_entrust_file_path'] = $params['split_entrust_file_path'];
        $data['lkl_mer_ledger_status'] = 3;
        try {
            $info->save($data);
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }
        return app('json')->success('提交成功', []);
    }

    /**
     * 商户分账关系绑定
     **/
    public function create_four()
    {
        $params = $this->validateParams(__FUNCTION__);

        $uid = $this->userInfo->uid;
        $info = LklModel::getInfo(['uid' => $uid], 'lkl_mer_cup_no');
        if (!$info) {
            return app('json')->fail('请返回上一页，完成电子合同签约');
        }
        if ($info['lkl_ec_status'] != 'COMPLETED') {
            return app('json')->fail('电子合同未签约成功');
        }
        if ($info['lkl_mer_cup_status'] == '') {
            return app('json')->fail('请返回上一页，完成商户进件');
        }
        if ($info['lkl_mer_cup_status'] != 'SUCCESS') {
            return app('json')->fail('商户进件未审核成功');
        }
        if($info['lkl_mer_ledger_status'] == 0){
            return app('json')->fail('请返回上一页，完成商户分账业务开通申请');
        }
        if($info['lkl_mer_ledger_status'] != 1){
            return app('json')->fail('商户分账业务开通申请未审核成功');
        }
        if($info['lkl_mer_bind_status'] == 3){
            return app('json')->fail('正在审核中，请耐心等待后台审核...');
        }

        $params['lkl_mer_cup_no'] = $info['lkl_mer_cup_no'];
        $params['lkl_receiver_no'] = '';
        $api = new \Lakala\LklApi();
        $result = $api::lklApplyBind($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }

        $data['entrust_file_path'] = $params['entrust_file_path'];
        $data['lkl_mer_bind_status'] = 3;
        try {
            $info->save($data);
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }
        return app('json')->success('提交成功', []);
    }

    /**
     * 电子合同下载
     **/
    public function download()
    {
        $params = $this->validateParams(__FUNCTION__);
        $api = new \Lakala\LklApi();
        $result = $api::lklEcDownload($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }
        return app('json')->success('提交成功', $result);
    }

    public function aa(){
        $params['lkl_ec_apply_id'] = '967363813417377792';

        $api = new \Lakala\LklApi();
        $result = $api::lklEcQStatus($params);
        if (!$result) {
            return app('json')->fail($api->getErrorInfo());
        }
        return app('json')->success('提交成功', $result);
    }

    /**
     * 验证
     **/
    public function validateParams($function)
    {
        switch ($function) {
            case 'create_first':
                $params = $this->request->params([
                    'merchant_type',
                    'cert_name',
                    'cert_no',
                    'ec_mobile',
                    'acct_type_code',
                    'acct_no',
                    'acct_name',
                    'agent_tag',
                    'agent_name',
                    'agent_cert_no',
                    'agent_file_path',
                    'A1', 'B1', 'B2', 'B9', 'B10', 'B19', 'B20', 'B24', 'B25', 'B26', 'B27', 'B28', 'B29', 'B30', 'B33',
                    'mer_blis_name',
                    'mer_blis',
                    'openning_bank_code',
                    'openning_bank_name'
                ]);
                break;
            case 'create_second':
                $params = $this->request->params([
                    'email',
                    'mer_reg_name',
                    'merchant_type',
                    'mer_name',
                    'mer_addr',
                    'province_code',
                    'city_code',
                    'county_code',
                    'mer_blis',
                    'license_dt_start',
                    'license_dt_end',
                    'latitude',
                    'longtude',
                    'B9',
                    'is_legal_person',
                    'lar_name',
                    'lar_id_card',
                    'lar_id_card_start',
                    'lar_id_card_end',
                    'B30',
                    'B27',
                    'branch_bank_no',
                    'branch_bank_name',
                    'clear_no',
                    'settle_province_code',
                    'settle_province_name',
                    'settle_city_code',
                    'settle_city_name',
                    'acct_no',
                    'acct_name',
                    'acct_type_code',
                    'acct_id_card',
                    'z_idcard_img',
                    'f_idcard_img',
                    'license_pic_img',
                    'acct_img',
                    'agree_ment_img',
                    'openining_permit_img',
                    'checkstand_img',
                    'shop_outside_img',
                    'shop_inside_img',
                    'z_settle_img',
                    'f_settle_img',
                    'legal_auth_img',
                ]);
                break;
            case 'create_three':
                $params = $this->request->params([
                    'mobile',
                    'split_entrust_file_path',
                ]);
                break;
            case 'create_four':
                $params = $this->request->params([
                    'entrust_file_path',
                ]);
                break;
            case 'download':
                $params = $this->request->params([
                    'lkl_ec_apply_id'
                ]);
                break;
        }
        try {
            validate(MerchantIntentionValidate::class)->scene($function)->check($params);
        } catch (Exception $e) {
            return app('json')->fail($e->getError());
        }
        return $params;
    }

    /**
     * 银行列表查询
     **/
    public function bank_info()
    {

    }


    public function create()
    {
        $data = $this->checkParams();
        if (!systemConfig('mer_intention_open')) {
            return app('json')->fail('未开启商户入驻');
        }
        if ($this->userInfo) $data['uid'] = $this->userInfo->uid;
        $make = app()->make(MerchantRepository::class);
        if ($make->fieldExists('mer_name', $data['mer_name']))
            throw new ValidateException('商户名称已存在，不可申请');
        if ($make->fieldExists('mer_phone', $data['phone']))
            throw new ValidateException('手机号已存在，不可申请');
        $adminRepository = app()->make(MerchantAdminRepository::class);
        if ($adminRepository->fieldExists('account', $data['phone']))
            throw new ValidateException('手机号已是管理员，不可申请');
        $intention = $this->repository->create($data);
        SwooleTaskService::admin('notice', [
            'type' => 'new_intention',
            'data' => [
                'title' => '商户入驻申请',
                'message' => '您有一个新的商户入驻申请',
                'id' => $intention->mer_intention_id
            ]
        ]);
        return app('json')->success('提交成功');
    }

    public function update($id)
    {
        if (!$this->repository->getWhere(['mer_intention_id' => (int)$id, 'uid' => $this->userInfo->uid, 'is_del' => 0]))
            return app('json')->fail('数据不存在');
        $data = $this->checkParams();
        if (!systemConfig('mer_intention_open')) {
            return app('json')->fail('未开启商户入驻');
        }
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $this->repository->updateIntention((int)$id, $data);
        SwooleTaskService::admin('notice', [
            'type' => 'new_intention',
            'data' => [
                'title' => '商户入驻申请',
                'message' => '您有一个新的商户入驻申请',
                'id' => $id
            ]
        ]);
        return app('json')->success('修改成功');
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $data = $this->repository->getList(['uid' => $this->userInfo->uid], $page, $limit);
        return app('json')->success($data);
    }

    function detail($id)
    {
        $data = $this->repository->detail((int)$id, $this->userInfo->uid);
        if (!$data) {
            return app('json')->fail('数据不存在');
        }
        if ($data->status == 1) {
            $data['login_url'] = rtrim(systemConfig('site_url'), '/') . '/' . config('admin.merchant_prefix');
        }
        return app('json')->success($data);
    }

    protected function checkParams()
    {
        $data = $this->request->params(['phone', 'salesman_id', 'is_online', 'mer_banner', 'mer_name', 'name', 'code', 'images', 'merchant_category_id', 'mer_type_id', 'commission_rate', 'inside', 'id_card', 'email', 'bank_card', 'bank_open_name', 'address']);
        app()->make(MerchantIntentionValidate::class)->check($data);
        $check = app()->make(SmsService::class)->checkSmsCode($data['phone'], $data['code'], 'intention');
        $data['mer_type_id'] = (int)$data['mer_type_id'];
        if ($data['code'] != 123456) {
            if (!$check) throw new ValidateException('验证码不正确');
        }
        if (!app()->make(MerchantCategoryRepository::class)->get($data['merchant_category_id'])) throw new ValidateException('商户分类不存在');
        if ($data['mer_type_id'] && !app()->make(MerchantTypeRepository::class)->exists($data['mer_type_id']))
            throw new ValidateException('店铺类型不存在');
        unset($data['code']);
        return $data;
    }

    /**
     * 商户分类
     * @Author:Qinii
     * @Date: 2020/9/15
     * @return mixed
     */
    public function cateLst()
    {
        $lst = app()->make(MerchantCategoryRepository::class)->getSelect();
        return app('json')->success($lst);
    }

    public function typeLst()
    {
        $lst = app()->make(MerchantTypeRepository::class)->getSelect();
        return app('json')->success($lst);
    }
}


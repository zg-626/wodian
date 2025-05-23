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

use app\common\model\system\merchant\MerchantAdmin;
use app\common\repositories\store\service\StoreServiceRepository;
use app\common\repositories\system\financial\FinancialRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserMerchantRepository;
use app\common\repositories\user\UserRealAuthRepository;
use think\App;
use crmeb\basic\BaseController;
use app\common\repositories\system\merchant\MerchantRepository as repository;

class Merchant extends BaseController
{
    protected $repository;
    protected $userInfo;

    /**
     * ProductCategory constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->userInfo =$this->request->isLogin() ? $this->request->userInfo():null;
    }

    /**
     * @Author:Qinii
     * @Date: 2020/5/27
     * @return mixed
     */
    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword', 'order', 'is_best', 'location', 'category_id', 'type_id','is_trader','is_online','district']);
        return app('json')->success($this->repository->getList($where, $page, $limit, $this->userInfo));
    }

    // 各身份邀请的商户列表
    public function agentLst()
    {
        [$page, $limit] = $this->getPage();
        $where['salesman_id'] = $this->request->uid();
        return app('json')->success($this->repository->lst($where, $page, $limit));
    }

    /**
     * @Author:Qinii
     * @Date: 2020/5/29
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        if (!$this->repository->apiGetOne($id))
            return app('json')->fail('店铺已打烊');

        if ($this->request->isLogin()) {
            app()->make(UserMerchantRepository::class)->updateLastTime($this->request->uid(), intval($id));
        }
        return app('json')->success($this->repository->detail($id, $this->userInfo));
    }

    public function systemDetail()
    {
        $config = systemConfig(['site_logo', 'site_name','login_logo']);
        return app('json')->success([
            'mer_avatar' => $config['site_logo'],
            'mer_name' => $config['site_name'],
            'mer_id' => 0,
            'postage_score' => '5.0',
            'product_score' => '5.0',
            'service_score' => '5.0',
        ]);
    }



    /**
     * @Author:Qinii
     * @Date: 2020/5/29
     * @param $id
     * @return mixed
     */
    public function productList($id)
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword','order','mer_cate_id','cate_id', 'order', 'price_on', 'price_off', 'brand_id', 'pid']);
        if(!$this->repository->apiGetOne($id)) return app('json')->fail(' 店铺已打烊');
        return app('json')->success($this->repository->productList($id,$where, $page, $limit,$this->userInfo));
    }

    /**
     * @Author:Qinii
     * @Date: 2020/5/29
     * @param int $id
     * @return mixed
     */
    public function categoryList($id)
    {
        if(!$this->repository->merExists((int)$id))
            return app('json')->fail('店铺已打烊');
        return app('json')->success($this->repository->categoryList($id));
    }

    public function qrcode($id)
    {
        if(!$this->repository->merExists((int)$id))
            return app('json')->fail('店铺已打烊');
        $url = $this->request->param('type') == 'routine' ? $this->repository->routineQrcode(intval($id)) : $this->repository->wxQrcode(intval($id));
        return app('json')->success(compact('url'));
    }

    // 付款码
    public function payCode($id)
    {
        return app('json')->success($this->repository->getPayCode(intval($id)));
    }

    // 创建付款码
    public function createPayCode($mer_id,$ratio)
    {
        $info =$this->repository->merExists((int)$mer_id);
        if(!$info)
            return app('json')->fail('店铺已打烊');

        if(!$ratio)
            return app('json')->fail('请输入付款码比例');
        $info=$this->repository->createPayCode((int)$mer_id,$ratio,$info);
        return app('json')->success($info);

    }

    // 修改付款码
    public function updatePayCode($id,$mer_id,$ratio)
    {
        $info =$this->repository->get((int)$mer_id)->toArray();
        if(!$info)
            return app('json')->fail('店铺已打烊');
        if(!$ratio)
            return app('json')->fail('请输入付款码比例');
        $info=$this->repository->updatePayCode((int)$id,$ratio,$info);
        return app('json')->success($info);
    }

    // 删除付款码
    public function delPayCode($id){
        $this->repository->delPayCode((int)$id);
        return app('json')->success('删除成功');
    }

    // 开启关闭付款码
    public function closePayCode($id,$status){
        $this->repository->closePayCode((int)$id,$status);
        return app('json')->success('设置成功');
    }

    // 付款码列表
    public function payCodeLst($id){
        $info =$this->repository->payCodeLst((int)$id);
        return app('json')->success($info);
    }

    public function localLst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword', 'order', 'is_best', 'location', 'category_id', 'type_id']);
        $where['delivery_way'] = 1;
        return app('json')->success($this->repository->getList($where, $page, $limit, $this->userInfo));
    }

    /**
     * TODO 申请转账保存
     * @return \think\response\Json
     * @author Qinii
     * @day 3/19/21
     */
    public function withdraw()
    {
        $data = $this->request->param(['extract_money','financial_type','mark','mer_id','account','financial_type','name','bank','bank_code','wechat','wechat_code','alipay','alipay_code']);

        $uid = $this->request->uid();
        $open = systemConfig('real_name_open');
        // 判断实名认证开关，只有开启才需要验证
        if ($open) {
            /** @var UserRealAuthRepository $userRealAuthRepository **/
            $userRealAuthRepository = app()->make(UserRealAuthRepository::class);
            $user = $userRealAuthRepository->getUserAuth($uid);
            if ($user && $user['status']!==1) {
                return app('json')->fail('请先进行实名认证');
            }
        }

        if(!$data['extract_money']){
            return app('json')->fail('请输入提现金额');
        }
        if(!$data['financial_type']){
            return app('json')->fail('请选择提现方式');
        }
        if(!$data['mer_id'])
            return app('json')->fail('请选择商户');
        $data['mer_admin_id'] = MerchantAdmin::where('mer_id',$data['mer_id'])->value('merchant_admin_id');

        /** @var FinancialRepository $financialRepository **/
        $financialRepository = app()->make(FinancialRepository::class);
        try {
            $financialRepository->saveApplys($data['mer_id'],$data);
        }catch (\Exception $e){
            return app('json')->fail($e->getMessage());
        }
        return app('json')->success('提交成功');
    }

    /**
     * TODO 抵用券互换
     * @return \think\response\Json
     * @author Qinii
     * @day 3/19/21
     */
    public function couponExchange()
    {
        // 用户信息
        $user = $this->request->userInfo()->hidden(['label_id', 'group_id', 'pwd', 'addres', 'card_id', 'last_time', 'last_ip', 'create_time', 'mark', 'status', 'spread_uid', 'spread_time', 'real_name', 'birthday', 'brokerage_price']);
        $user->append(['merchant']);
        //$user = $user->toArray();
        // 用户绑定的商家信息
        $merchant = $user['merchant'];

        $uid = $user['uid'];
        $open = systemConfig('real_name_open');
        // 判断实名认证开关，只有开启才需要验证
        if ($open) {
            /** @var UserRealAuthRepository $userRealAuthRepository **/
            $userRealAuthRepository = app()->make(UserRealAuthRepository::class);
            $user = $userRealAuthRepository->getUserAuth($uid);
            if ($user && $user['status']!==1) {
                return app('json')->fail('请先进行实名认证');
            }
        }

        if($merchant['coupon_amount']<=0){
            return app('json')->fail('商家抵用券不足');
        }

        try {
            $this->repository->couponExchange($user,$merchant);
        }catch (\Exception $e){
            return app('json')->fail($e->getMessage());
        }
        return app('json')->success('提交成功');
    }

    public function withdrawLst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'status', 'financial_type', 'financial_status', 'keyword', 'is_trader', 'mer_id']);
        $where['type'] = 0;
        /** @var FinancialRepository $financialRepository **/
        $financialRepository = app()->make(FinancialRepository::class);
        $data = $financialRepository->getAdminList($where, $page, $limit);
        return app('json')->success($data);
    }

    // 商家数据记录
    public function merchant_data(UserBillRepository $billRepository)
    {
        [$page, $limit] = $this->getPage();
        [$start,$stop]= $this->request->params(['start','stop'],true);
        $category = $this->request->param('category');
        $type = $this->request->param('type')??'';
        $mer_id = $this->request->param('mer_id');
        $where['date'] = $start&&$stop? date('Y/m/d',$start).'-'.date('Y/m/d',$stop) : '';
        $where['category'] = $category;
        $where['type'] = $type;
        return app('json')->success($billRepository->merList($where, $mer_id, $page, $limit));
    }

}

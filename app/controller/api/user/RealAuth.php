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


namespace app\controller\api\user;


use app\common\repositories\user\UserRealAuthRepository;
use app\validate\api\UserRealAuthValidate;
use crmeb\basic\BaseController;
use think\App;
use think\exception\ValidateException;

/**
 * 用户实名认证控制器
 * Class RealAuth
 * @package app\controller\api\user
 */
class RealAuth extends BaseController
{
    /**
     * @var UserRealAuthRepository
     */
    protected $repository;

    /**
     * RealAuth constructor.
     * @param App $app
     * @param UserRealAuthRepository $repository
     */
    public function __construct(App $app, UserRealAuthRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * 获取用户实名认证状态
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthInfo()
    {
        $uid = $this->request->uid();
        return app('json')->success($this->repository->getUserAuth($uid));
    }

    /**
     * 提交实名认证
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyAuth()
    {
        $data = $this->request->params(['real_name', 'id_card']);
        
        // 验证数据
        $validate = new UserRealAuthValidate();
        if (!$validate->check($data)) {
            return app('json')->fail($validate->getError());
        }
        
        $uid = $this->request->uid();
        $result = $this->repository->applyAuth($uid, $data['real_name'], $data['id_card']);

        if ($result['status']!= '') {
            return app('json')->success('实名认证成功');
        }

        return app('json')->fail($result['message'] ?? '实名认证失败');
    }
}
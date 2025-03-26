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

use app\common\repositories\store\CityAreaRepository;
use think\App;
use crmeb\basic\BaseController;
use app\validate\api\UserGroupApplyValidate as validate;
use app\common\repositories\user\UserGroupApplyRepository as repository;
use think\exception\ValidateException;

class UserGroupApply extends BaseController
{
    /**
     * @var repository
     */
    protected $repository;

    /**
     * UserGroupApply constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }


    public function lst()
    {
        [$page,$limit] = $this->getPage();
        return app('json')->success($this->repository->getList($this->request->uid(),$page,$limit));
    }

    public function detail($id)
    {
        $uid = $this->request->uid();
        if (!$this->repository->existsWhere(['id' => $id, 'uid' => $uid])) {
            return app('json')->fail('申请不存在');
        }
        return app('json')->success($this->repository->get($id, $uid));
    }

    /**
     * @param validate $validate
     * @return mixed
     * @author Qinii
     */
    public function create(validate $validate)
    {
        $data = $this->checkParams($validate);

        if ($data['group_id']) {
            if ($this->repository->fieldExists($data['group_id'], $this->request->uid())>0)
                return app('json')->fail('请勿重复申请');
        };
        $data['uid'] = $this->request->uid();
        $address = $this->repository->create($data);
        return app('json')->success('添加成功', $address->toArray());
    }

    /**
     * @param $id
     * @param validate $validate
     * @return mixed
     * @author Qinii
     */
    public function update($id, validate $validate)
    {
//        if (!$this->repository->fieldExists($id, $this->request->uid()))
//            return app('json')->fail('信息不存在');
        $data = $this->checkParams($validate);
        $data['status'] = 0;
        $data['fail_msg'] = '';
        $this->repository->update($id, $data);
        return app('json')->success('编辑成功');
    }

    /**
     * @param $id
     * @return mixed
     * @author Qinii
     */
    public function delete($id)
    {
        if (!$this->repository->fieldExists($id, $this->request->uid()))
            return app('json')->fail('信息不存在');
        if ($this->repository->checkDefault($id))
            return app('json')->fail('默认地址不能删除');
        $this->repository->delete($id);
        return app('json')->success('删除成功');
    }

    /**
     * @param validate $validate
     * @return array
     * @author Qinii
     */
    public function checkParams(validate $validate)
    {
        $data = $this->request->params(['group_id','real_name', 'phone']);
        $validate->check($data);

        return $data;
    }
}

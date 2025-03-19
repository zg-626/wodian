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

namespace app\controller\admin\user;


use app\validate\admin\UserActingValidate;
use crmeb\basic\BaseController;
use think\App;
use app\common\repositories\user\UserActingRepository as repository;
use think\db\exception\DbException;

class UserActing extends BaseController
{
    /**
     * @var UserActingRepository
     */
    protected $repository;

    /**
     * User constructor.
     * @param App $app
     * @param  $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    /**
     * @return mixed
     * @author Qinii
     */
    public function lst()
    {
        $where = $this->request->params(['keyword', 'group_id', 'status', 'real_name', ['is_del', 0]]);
        [$page, $limit] = $this->getPage();
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }


    /**
     * @param $id
     * @return mixed
     * @author Qinii
     */
    public function detail($id)
    {
        if (!$this->repository->fieldExists('feedback_id', $id))
            return app('json')->fail('数据不存在');
        $feedback = $this->repository->get($id)->toArray();
        [$feedback['category'], $feedback['type']] = explode('/', $feedback['type'], 2);
        return app('json')->success($feedback);
    }

    public function replyForm($id)
    {
        return app('json')->success(formToData($this->repository->replyForm($id)));
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DbException
     * @author Qinii
     */
    public function reply($id)
    {
        if (!$this->repository->fieldExists('feedback_id', $id))
            return app('json')->fail('数据不存在');
        $data = $this->request->params(['reply', 'remake']);
        if (!empty($data['reply'])) {
            $data['status'] = 1;
            $data['update_time'] = date('Y-m-d H:i:s');
        }

        $this->repository->update($id, $data);
        if (!empty($data['reply'])) event('user.feedbackReply', compact('id', 'data'));
        return app('json')->success('回复成功');
    }

    /**
     * @param $id
     * @return mixed
     * @author Qinii
     */
    public function delete($id)
    {
        if (!$this->repository->fieldExists('feedback_id', $id))
            return app('json')->fail('数据不存在');
        $this->repository->update($id, ['is_del' => 1]);
        return app('json')->success('删除成功');
    }

    /**
     * @param $id
     * @return mixed
     * @throws DbException
     * @throws FormBuilderException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-05-07
     */
    public function statusForm($id)
    {
        if (!$this->repository->exists($id))
            return app('json')->fail('数据不存在');
        return app('json')->success(formToData($this->repository->statusForm($id)));
    }

    /**
     * @param $id
     * @param UserActingValidate $validate
     * @return mixed
     * @throws DbException
     * @author xaboy
     * @day 2020-05-07
     */
    public function switchStatus($id, UserActingValidate $validate)
    {
        if (!$this->repository->getWhereCount(['id' => $id, 'is_del' => 0]))
            return app('json')->fail('数据不存在');
        $data = $this->request->params(['status', 'fail_msg', 'create_mer']);
        $data['status'] = $data['status'] == 1 ? 1 : 2;
        $this->repository->updateStatus($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * @param UserActingValidate $validate
     * @return array
     * @author xaboy
     * @day 2020-05-07
     */
    protected function checkParams(UserActingValidate $validate)
    {
        $data = $this->request->params(['group_id']);
        $validate->check($data);
        return $data;
    }

    public function form($id)
    {
        if (!$this->repository->getWhereCount(['id' => $id, 'is_del' => 0]))
            return app('json')->fail('数据不存在');
        return app('json')->success(formToData($this->repository->markForm($id)));
    }

    public function mark($id)
    {
        if (!$this->repository->getWhereCount(['id' => $id, 'is_del' => 0]))
            return app('json')->fail('数据不存在');
        $data = $this->request->param('mark');
        $this->repository->update($id, ['mark' => $data]);
        return app('json')->success('修改成功');
    }
}

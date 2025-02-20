<?php

namespace app\controller\merchant\store\product;

use app\common\repositories\store\product\ProductUnitRepository as repository;
use app\validate\admin\ProductUnitValidate;
use crmeb\basic\BaseController;
use think\App;

class ProductUnit extends BaseController
{

    protected $repository;

    /**
     * Product constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function list()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['value']);
        $where['mer_id'] = $this->request->merId();
        $data = $this->repository->list($where, $page, $limit);
        return app('json')->success($data);
    }

    public function createForm()
    {
        return app('json')->success(formToData($this->repository->createForm($this->request->merId())));
    }

    public function create(ProductUnitValidate $validate)
    {
        $data = $this->checkParams($validate);
        $this->repository->create($this->request->merId(), $data);
        return app('json')->success('添加成功');
    }

    public function updateForm($id)
    {
        return app('json')->success(formToData($this->repository->updateForm((int)$id, $this->request->merId())));
    }

    public function update($id, ProductUnitValidate $validate)
    {
        $data = $this->checkParams($validate);
        $this->repository->update($id, $this->request->merId(), $data);
        return app('json')->success('编辑成功');
    }

    public function delete($id)
    {
        $this->repository->delete($id, $this->request->merId());
        return app('json')->success('删除成功');
    }

    public function getSelectList()
    {
        return app('json')->success($this->repository->getSelectList($this->request->merId()));
    }


    public function checkParams(ProductUnitValidate $validate)
    {
        $params = ['value', 'sort'];
        $data = $this->request->params($params);
        $validate->check($data);
        return $data;
    }

}

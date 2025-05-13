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


namespace app\common\repositories\store;


use app\common\dao\store\CityAreaDao;
use app\common\repositories\BaseRepository;
use FormBuilder\Factory\Elm;
use think\exception\ValidateException;
use think\facade\Route;

/**
 * @mixin CityAreaDao
 */
class CityAreaRepository extends BaseRepository
{
    public function __construct(CityAreaDao $dao)
    {
        $this->dao = $dao;
    }

    public function getChildren($pid)
    {
        if($pid==0){
            return $this->search(['pid' => $pid])->where('snum','>',0)->select();
        }
        return $this->search(['pid' => $pid])->select();
    }

    public function getList($where)
    {
        return  $this->dao->getSearch($where)->with(['parent'])->order('id ASC')->select()->append(['children','hasChildren']);
    }

    public function form(?int $id, ?int $parentId)
    {
        $parent = ['id' => 0, 'name' => '全国', 'level' => 0,];
        $formData = [];
        if ($id) {
            $formData = $this->dao->getWhere(['id' => $id],'*',['parent'])->toArray();
            if (!$formData) throw new ValidateException('数据不存在');
            $form = Elm::createForm(Route::buildUrl('systemCityAreaUpdate', ['id' => $id])->build());
            if (!is_null($formData['parent'])) $parent = $formData['parent'];
        } else {
            $form = Elm::createForm(Route::buildUrl('systemCityAreaCreate')->build());
            if ($parentId) $parent = $this->dao->getWhere(['id' => $parentId]);
        }
        $form->setRule([
            Elm::input('parent_id', '', $parent['id'] ?? 0)->hiddenStatus(true),
            Elm::input('level', '', $parent['level'] + 1)->hiddenStatus(true),
            Elm::input('parent_name', '上级地址：', $parent['name'])->disabled(true)->placeholder('请输入上级地址'),
            Elm::input('name', '地址名称：', '')->placeholder('请输入地址名称')->required(),
        ]);
        return $form->setTitle($id ? '编辑城市' : '添加城市')->formData($formData);
    }

    public function create($data)
    {
        if($data['parent_id'] > 0){
            // 修改父级snum
            $this->dao->incField($data['parent_id'],'snum');
        }
        return $this->dao->create($data);
    }

    /**
     * TODO 添加
     * @param $name
     * @param $pid
     * @param $lv
     * @return mixed
     * @author Qinii
     * @day 2023/8/2
     */
    public function treeCreate($name,$code, $pid = 0, $lv = 1)
    {
        $type = [
            1 => 'province',
            2 => 'city',
            3 => 'area',
            4 => 'street',
        ];
        $path = '/';
        if ($pid){
            $res =  $this->dao->get($pid);
            $path = $res['path'].$res['id'].'/';
        }
        $data = [
            'type' => $type[$lv],
            'parent_id' => $pid,
            'level' => $lv,
            'name' => $name,
            'path'=> $path,
            'code' => $code
        ];
        $result =  $this->dao->findOrCreate($data);
        return $result->id;
    }

    /**
     * TODO 计算子集个数
     * @author Qinii
     * @day 2023/8/2
     */
    public function sumChildren($pid = '')
    {
        $data = $this->dao->getSearch(['parent_id' => $pid])->where('level','<',4)->select();
        foreach ($data as $datum) {
            $snum = $this->dao->getSearch(['parent_id' => $datum->id])->count();
            $datum->snum = $snum;
            $datum->save();
        }
    }

    /**
     *  文件倒入，地址信息
     * @param $fiel
     * @author Qinii
     * @day 2024/1/19
     */
    public function updateCityForTxt($fiel)
    {
        $fiel = json_decode(file_get_contents($fiel));
        $this->tree($fiel);
        return true;
    }

    /**
     * @return array
     * @author xaboy
     * @day 2020/7/22
     */
    public function getAddressChildList()
    {
        $res = $this->dao->options();
        /*echo "<pre>";
        print_r($res);exit();*/
        //$res = formatCascaderData($res, 'name',4,'parent_id');
        $res = $this->tree($res);
        //$res = formatCategory($res, 'name','parent_id');
        /*foreach ($res as $k => $v) {
            if (!isset($v['children']) || !count($v['children']))
                unset($res[$k]);
        }*/
        echo "<pre>";
        print_r($res);exit();
        return array_values($res);
    }

    /**
     *  循环整理地址信息
     * @param $data
     * @param $pid
     * @param $path
     * @param $level
     * @return bool
     * @author Qinii
     * @day 2024/1/19
     */
    public function tree($data,$pid = 0,$path = '/',$level = 1)
    {
        $type = [
            1 => 'province',
            2 => 'city',
            3 => 'area',
            4 => 'street'
        ];
        foreach ($data as $k => $datum) {
            $_path = '';
            $where = [
                'code' => $datum->code,
                'name' => $datum->name,
                'path' => $path,
                'level'=> $level,
                'parent_id' => $pid,
                'type' => $type[$level]
            ];
            $rest = $this->dao->findOrCreate($where);
            if (isset($datum->children)) {
                $_path = $path.$rest->id.'/';
                $this->tree($datum->children, $rest->id, $_path, $level +1);
            }
        }
        return true;
    }


    public function delete($id)
    {
        $res = $this->dao->get($id);
        if (empty($res)) {
            throw new ValidateException('数据不存在');
        }
        if ($res['parent_id'] > 0) {
            // 修改父级snum
            $this->dao->decField($res['parent_id'], 'snum');
        }
        return $this->dao->delete($id);
    }
}

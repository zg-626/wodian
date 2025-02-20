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


namespace app\common\repositories\system\form;


use app\common\dao\system\form\FormDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\store\product\ProductRepository;
use crmeb\services\ExcelService;
use think\exception\ValidateException;
use think\facade\Cache;

/**
 * Class FormRepository
 *
 * @mixin FormDao
 */
class FormRepository extends BaseRepository
{
    public function __construct(FormDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, $page, $limit)
    {
        $query = $this->dao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->hidden(['form_keys'])->select();
        return compact('count', 'list');
    }

    /**
     * 获取表单的字段及验证属性
     * @param $formId
     * @return array
     * @author Qinii
     * @day 2023/10/8
     */
    public function getFormKeys(int $formId, $merId = null)
    {
        $where = ['form_id' => $formId, 'status' => 1, 'is_del' => 0];
        if ($merId) $where['mer_id'] = $merId;
        $form_info = $this->dao->getSearch($where)->field('form_keys,value')->find();
        if (!$form_info) throw new ValidateException('表单信息不存在');
        $data = [];
        $res = $form_info['form_keys'];
        foreach ($res as $item) {
            $data[] = $item->key;
            $val[$item->key] = [
                'label' => $item->label,
                'val' => $item->val,
                'type'=> $item->type
            ];
        }
        $form = json_encode($form_info['form_keys'], JSON_UNESCAPED_UNICODE);
        $form_value = json_encode($form_info['value'], JSON_UNESCAPED_UNICODE);
        return compact('form', 'data', 'val', 'form_value');
    }

    public function excel(array $where, int $page, int $limit, int $merId)
    {
        $cahce_key = 'form_headers_' . $where['link_id'] . '_' . $merId;
        if (![$header, $info] = Cache::get($cahce_key)) {
            $header = ['活动ID', '活动名称', '用户ID', '昵称', '手机号码'];
            $keys = $this->getFormKeys($where['link_id'], $merId);
            $form = json_decode($keys['form']);
            foreach ($form as $key) {
                $header[] = $key->label;
                $info[] = $key->key;
            }
            $header[] = '创建时间';
            Cache::set($cahce_key, [$header, $info], 60 * 15);
        }
        return app()->make(ExcelService::class)->userForm($header, $info, $where, $page, $limit);
    }

    public function delete(int $id, int $mer_id = 0)
    {
        // 删除表单
        $this->dao->delete($id);
        if ($mer_id) {
            // 删除所有表单关联商品
            app()->make(ProductRepository::class)->deleteProductFormByFormId($id, $mer_id);
        }

    }
}

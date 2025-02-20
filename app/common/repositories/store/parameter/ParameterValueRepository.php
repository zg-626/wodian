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

namespace app\common\repositories\store\parameter;

use app\common\dao\store\parameter\ParameterValueDao;
use app\common\repositories\BaseRepository;
use think\facade\Db;

class ParameterValueRepository extends BaseRepository
{
    /**
     * @var ParameterValueDao
     */
    protected $dao;


    /**
     * ParameterRepository constructor.
     * @param ParameterValueDao $dao
     */
    public function __construct(ParameterValueDao  $dao)
    {
        $this->dao = $dao;
    }

    public function create($id, $data,$merId)
    {
        if (empty($data)) return ;
        foreach ($data as $datum) {
            if ($datum['name'] && $datum['value']) {
                $create[] = [
                    'product_id' => $id,
                    'name' => $datum['name'] ,
                    'value' => $datum['value'],
                    'sort' => $datum['sort'],
                    'parameter_id' => $datum['parameter_id'] ?? 0,
                    'mer_id' => $datum['mer_id'] ?? $merId,
                    'create_time' => date('Y-m-d H:i:s',time())
                ];
            }
        }
        if ($create) $this->dao->insertAll($create);
    }


    /**
     *  获取所有参数的值，并合并所关联的商品ID
     * @param $where
     * @return array
     * @author Qinii
     * @day 2023/10/21
     */
    public function getOptions($where)
    {
        $data = $this->dao->getSearch($where)->column('parameter_id,name','value');
        return array_values($data);
    }

    /**
     *  根据筛选的参数，查询出商品ID
     * @param $filter_params
     * @param $page
     * @param $limit
     * @return array
     * @author Qinii
     * @day 2023/11/14
     */
    public  function filter_params($filter_params)
    {
        $productId = [];
        if (!empty($filter_params)) {
            if (!is_array($filter_params)) $filter_params = json_decode($filter_params,true);
            $value = [];
            foreach ($filter_params as $k => $v) {
                $id[] = $k;
                $value = array_merge($value,$v);
            }
            if (empty($id) || empty($value)) return false;
            $productData = $this->dao->getSearch([])->alias('P')
                ->join('ParameterValue V','P.product_id = V.product_id')
                ->whereIn('P.parameter_id',$id)->whereIn('P.value',$value)
                ->whereIn('V.parameter_id',$id)->whereIn('V.value',$value)
                ->group('P.product_id')
                ->field('P.product_id')
                ->select();
            if ($productData) {
                $productData = $productData->toArray();
                $productId = array_column($productData,'product_id');
            }
        }
        return $productId;
    }

}


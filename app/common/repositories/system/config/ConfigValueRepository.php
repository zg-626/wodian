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


namespace app\common\repositories\system\config;


use app\common\dao\system\config\SystemConfigValueDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\store\product\ProductRepository;
use app\common\repositories\system\groupData\GroupDataRepository;
use app\common\repositories\system\groupData\GroupRepository;
use crmeb\jobs\SyncProductTopJob;
use crmeb\services\DownloadImageService;
use crmeb\services\RedisCacheService;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Queue;

/**
 * Class ConfigValueRepository
 * @package app\common\repositories\system\config
 * @mixin SystemConfigValueDao
 */
class ConfigValueRepository extends BaseRepository
{

    public $special = [
        'serve_account'
    ];

    const CONFIG_KEY_PREFIX = 'merchant_sys_config_';

    /**
     * ConfigValueRepository constructor.
     * @param SystemConfigValueDao $dao
     */
    public function __construct(SystemConfigValueDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $keys
     * @param int $merId
     * @return array
     * @author xaboy
     * @day 2020-03-27
     */
    public function more(array $keys, int $merId): array
    {
        $config = $this->dao->fields($keys, $merId);
        foreach ($keys as $key) {
            if (!isset($config[$key])) $config[$key] = '';
        }
        return $config;
    }

    /**
     * @param string $key
     * @param int $merId
     * @return mixed|string|null
     * @author xaboy
     * @day 2020-05-08
     */
    public function get(string $key, int $merId)
    {
        $value = $this->dao->value($key, $merId);
        return $value ?? '';
    }

    /**
     * @param int|array $cid
     * @param array $formData
     * @param int $merId
     * @author xaboy
     * @day 2020-03-27
     */
    public function save($cid, array $formData, int $merId)
    {
        $keys = array_keys($formData);
        $keys = app()->make(ConfigRepository::class)->intersectionKey($cid, $keys);
        if (!count($keys)) return;
        foreach ($keys as $key => $info) {
            if (!isset($formData[$key]))
                unset($formData[$key]);
            else {
                if ($info['config_type'] == 'number') {
                    if ($formData[$key] === '' || $formData[$key] < 0)
                        throw new ValidateException($info['config_name'] . '不能小于0');
                    $formData[$key] = floatval($formData[$key]);
                }
                $this->separate($key,$formData[$key],$merId);
            }
        }
        $this->setFormData($formData, $merId);
    }

    /**
     * TODO 需要做特殊处理的配置参数
     * @param $key
     * @author Qinii
     * @day 2022/11/17
     */
    public function separate($key,$value,$merId)
    {
        switch($key) {
            case 'mer_svip_status':
                //修改商户的会员状态
                app()->make(ProductRepository::class)->getSearch([])->where(['mer_id' => $merId,'product_type' => 0])->update([$key => $value]);
                break;
                //热卖排行
            case 'hot_ranking_switch':
                if ($value) {
                    Queue::push(SyncProductTopJob::class, []);
                }
                break;
            case 'margin_remind_day':
                if ($value && floor($value) != $value) throw new ValidateException('时间不可为小数');
                break;
            case 'svip_switch_status':
                if ($value == 1) {
                    $groupDataRepository = app()->make(GroupDataRepository::class);
                    $groupRepository = app()->make(GroupRepository::class);
                    $group_id = $groupRepository->getSearch(['group_key' => 'svip_pay'])->value('group_id');
                    $where['group_id'] = $group_id;
                    $where['status'] = 1;
                    $count = $groupDataRepository->getSearch($where)->field('group_data_id,value,sort,status')->count();
                    if (!$count)
                        throw new ValidateException('请先添加会员类型');
                }
                break;
            default:
                break;
        }
        return ;
    }

    public function setFormData(array $formData, int $merId)
    {
        Db::transaction(function () use ($merId, $formData) {
            foreach ($formData as $key => $value) {
                if ($this->dao->merExists($key, $merId))
                    $this->dao->merUpdate($merId, $key, ['value' => $value]);
                else
                    $this->dao->create([
                        'mer_id' => $merId,
                        'value' => $value,
                        'config_key' => $key
                    ]);
            }
        });
        $this->syncConfig();
    }

    public function syncConfig()
    {
        $list = $this->query([])->column('value,config_key,mer_id');
        $make = app()->make(RedisCacheService::class);
        $oldKeys = $make->keys(self::CONFIG_KEY_PREFIX.'*') ?: [];
        $oldKeys = array_combine($oldKeys, $oldKeys);
        $mset = [];
        foreach ($list as $item) {
            $key = self::CONFIG_KEY_PREFIX . $item['mer_id'] . '_' . $item['config_key'];
            $mset[$key] = $item['value'];
            unset($oldKeys[$key]);
        }
        $mset[self::CONFIG_KEY_PREFIX.'configFlag'] = time();
        $make->mset($mset);
        if (count($oldKeys)) {
            $make->handler()->del(...array_values($oldKeys));
        }
        Cache::delete('get_api_config');
    }

    /**
     *  清楚缓存后需要重新增加缓存的配置
     * @author Qinii
     * @day 2023/10/20
     */
    public function special()
    {
        $list = $this->query([])->column('value,config_key,mer_id');
        foreach ($list as $item) {
            if (in_array($item['config_key'], $this->special)) {
                Cache::set($item['config_key'], json_decode($item['value']));
            }
        }
    }

    public function getConfig(int $merId, $name)
    {
        $make = app()->make(RedisCacheService::class);
        if (is_array($name)) {
            if (!count($name)) {
                return [];
            }
            $names = $name;
        } else {
            $names = [$name];
        }
        $keys = [];
        foreach ($names as $item) {
            $keys[] = self::CONFIG_KEY_PREFIX . $merId . '_' . $item;
        }
        $values = $make->mGet($keys) ?: [];
        if (!is_array($name)) {
            return ($values[0] ?? '') ? json_decode($values[0]) : '';
        }
        $data = [];
        if (!count($values)) {
            foreach ($names as $v) {
                $data[$v] = '';
            }
            return $data;
        }
        foreach ($values as $i => $value) {
            $data[$names[$i]] = $value ? json_decode($value) : '';
        }
        return $data;
    }

}

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


namespace app\controller\pc;


use app\common\repositories\store\product\SpuRepository;
use app\common\repositories\store\StoreCategoryRepository;
use app\common\repositories\system\groupData\GroupDataRepository;
use crmeb\basic\BaseController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Common extends BaseController
{
    public function home()
    {
        $config = [];
        foreach (['pc_home_tab', 'pc_home_banner'] as $key) {
            $config[$key] = systemGroupData($key);
        }
        $config['pc_top_banner'] = systemGroupData('pc_top_banner', 0, 1)[0] ?? null;
        return app('json')->success($config);
    }

    public function homeRecommend()
    {
        [$page, $limit] = $this->getPage();
        $data = systemGroupData('pc_home_rec', $page, $limit);
        $count = app()->make(GroupDataRepository::class)->getGroupDataCount('pc_home_rec', 0);
        $storeSpu = app()->make(SpuRepository::class);
        $where = ['is_gift_bag' => 0, 'product_type' => 0, 'common' => 1];
        foreach ($data as $k => $item) {
            $where['cate_pid'] = $item['cid'];
            $where['order'] = 'star';
            $data[$k]['list'] = $storeSpu->getApiSearch($where, 0, 8, null)['list'];
        }
        return app('json')->success(['count' => $count, 'list' => $data]);
    }

    public function hotBanner($type)
    {
        if (!in_array($type, ['new', 'hot', 'best', 'good']))
            $data = [];
        else
            $data = systemGroupData('pc_' . $type . '_banner');
        return app('json')->success($data);
    }

    public function config()
    {
        $config = systemConfig(['site_logo', 'wechat_qrcode', 'site_name', 'site_url', 'wx_scan_login', 'sys_phone','beian_sn', 'is_open_service','mer_intention_open']);
        $config['copyright'] = systemGroupData('pc_copyright');
        return app('json')->success($config);
    }

    public function mer_config($merId)
    {
        $merId = (int)$merId;
        if (!$merId) return app('json')->fail('商户不存在');
        $config = [
            'top_banner' => merchantConfig($merId, 'mer_pc_top'),
            'banner' => merchantGroupData($merId, 'mer_pc_banner')
        ];
        return app('json')->success($config);
    }
}

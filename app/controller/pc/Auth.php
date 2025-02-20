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


use app\common\repositories\user\UserRepository;
use crmeb\basic\BaseController;
use crmeb\services\WechatService;
use think\facade\Cache;

class Auth extends BaseController
{
    public function scanLogin()
    {
//        if (!systemConfig('wx_scan_login')) {
//            return app('json')->fail('扫码登录未开启');
//        }
        $qrcode = WechatService::create(false)->qrcodeService();
        $key = md5(time() . uniqid(true, false) . random_int(1, 10000));
        $timeout = 600;
        Cache::set('_scan_login' . $key, 0, $timeout);
        $data = $qrcode->temporary('_sys_scan_login.' . $key, 30 * 24 * 3600)->toArray();
        return app('json')->success(['timeout' => $timeout, 'key' => $key, 'qrcode' => $qrcode->url($data['ticket'])]);
    }

    public function checkScanLogin()
    {
        $key = (string)$this->request->param('key');
        if ($key) {
            $uid = Cache::get('_scan_login' . $key);
            if ($uid) {
                Cache::delete('_scan_login' . $key);
                $userRepository = app()->make(UserRepository::class);
                $user = $userRepository->get($uid);
                if (!$user) {
                    return app('json')->status(400, '登录失败');
                }
                if (!$user['status']) {
                    return app('json')->status(400, '账号已被禁用');
                }
                $tokenInfo = $userRepository->createToken($user);
                $user = $user->hidden(['label_id', 'group_id', 'main_uid', 'pwd', 'addres', 'card_id', 'last_time', 'last_ip', 'create_time', 'mark', 'status', 'spread_uid', 'spread_time', 'real_name', 'birthday', 'brokerage_price']);
                return app('json')->status(200, $userRepository->returnToken($user, $tokenInfo));
            }
        }

        return app('json')->status(201, '未登录');
    }
}

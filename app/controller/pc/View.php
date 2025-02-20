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


use crmeb\basic\BaseController;

class View extends BaseController
{
    public function pc()
    {
        $pathinfo = ltrim($this->request->pathinfo(), '/');
        $open = systemConfig('open_pc');
        if ($open === '0' || strpos($pathinfo, 'pages') === 0) {
            return app()->make(\app\controller\View::class)->h5();
        }
        if ($open === '2' || $pathinfo) {
            return $this->render();
        }
        if ((!$this->request->isMobile()) && !strpos($this->request->server('HTTP_USER_AGENT'), 'MicroMessenger')) {
            return $this->render();
        }
        return app()->make(\app\controller\View::class)->h5();
    }

    public function render()
    {
        $DB = DIRECTORY_SEPARATOR;
        return view(app()->getRootPath() . 'public' . $DB . 'pc' . $DB . 'index.html');
    }
}

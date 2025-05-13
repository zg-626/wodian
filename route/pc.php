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

use think\facade\Route;
use app\common\middleware\UserTokenMiddleware;

Route::any('/', 'pc.View/pc')->middleware(\app\common\middleware\InstallMiddleware::class)
    ->middleware(\app\common\middleware\CheckSiteOpenMiddleware::class);

Route::miss(function () {
    return app()->make(\app\controller\pc\View::class)->pc();
})->middleware(\app\common\middleware\InstallMiddleware::class)
    ->middleware(\app\common\middleware\CheckSiteOpenMiddleware::class);

Route::group('api/pc/', function () {
    Route::get('config', 'pc.Common/config');
    Route::get('mer_config/:mer_id', 'pc.Common/mer_config');
    Route::get('mer_category', 'pc.store.MerchantCategory/lst');
    Route::get('home', 'pc.Common/home');

    Route::get('hot_banner/:type', 'pc.Common/hotBanner');

    Route::group(function () {
        Route::get('rec_list', 'pc.Common/homeRecommend');
        Route::get('care','pc.store.Merchant/Care');
    })->middleware(UserTokenMiddleware::class, false);

    Route::group('login/scan', function () {
        Route::get('', 'pc.Auth/scanLogin');
        Route::post('check', 'pc.Auth/checkScanLogin');
    });
})->middleware(\app\common\middleware\AllowOriginMiddleware::class);

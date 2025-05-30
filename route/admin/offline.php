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
use app\common\middleware\AdminAuthMiddleware;
use app\common\middleware\AdminTokenMiddleware;
use app\common\middleware\AllowOriginMiddleware;
use app\common\middleware\LogMiddleware;

Route::group(function () {
    Route::group('offline_order', function () {
        Route::get('lst', 'Order/getAllList')->name('systemOrderLst')->option([
            '_alias' => '列表',
        ]);
        Route::get('title', 'Order/title')->name('systemOrderStat')->option([
            '_alias' => '金额统计',
        ]);
        Route::get('chart', 'Order/chart')->name('systemOrderTitle')->option([
            '_alias' => '头部统计',
        ]);
        Route::get('detail/:id', 'Order/detail')->name('systemOrderDetail')->option([
            '_alias' => '详情',
        ]);
        Route::get('excel', 'Order/Excel')->name('systemOrderExcel')->option([
            '_alias' => '导出',
        ]);
        Route::get('status/:id', 'Order/status')->name('systemOrderStatus')->option([
            '_alias' => '记录',
        ]);
        Route::get('children/:id', 'Order/childrenList')->name('systemOrderChildrenList')->option([
            '_alias' => '关联订单',
        ]);
    })->prefix('admin.offline.')->option([
        '_path' => '/offline_order/list',
        '_auth' => true,
        '_append'=> [
            [
                '_name'  =>'systemStoreExcelLst',
                '_path'  =>'/offline_order/list',
                '_alias' => '导出列表',
                '_auth'  => true,
            ],
            [
                '_name'  =>'systemStoreExcelDownload',
                '_path'  =>'/offline_order/list',
                '_alias' => '导出列表',
                '_auth'  => true,
            ],
        ]
    ]);;

})->middleware(AllowOriginMiddleware::class)
    ->middleware(AdminTokenMiddleware::class, true)
    ->middleware(AdminAuthMiddleware::class)
    ->middleware(LogMiddleware::class);

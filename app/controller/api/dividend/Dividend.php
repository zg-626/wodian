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

namespace app\controller\api\dividend;

use app\common\repositories\article\ArticleRepository as repository;
use app\common\repositories\user\BonusService;
use app\common\repositories\user\DividendPoolService;
use app\common\repositories\user\UserBillRepository;
use crmeb\basic\BaseController;
use think\App;

class Dividend extends BaseController
{
    /**
     * @var repository
     */
    protected $repository;

    /**
     * StoreBrand constructor.
     * @param App $app
     * @param repository $repository
     */
    public function __construct(App $app, repository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    // 测试接口
    public function test()
    {
        /** @var DividendPoolService $dividendPoolService **/
        /*$dividendPoolService = app()->make(DividendPoolService::class);
        $dividendPoolService->calculateAndDistributeDividend();*/
        /** @var BonusService $bonusService **/
        $bonusService = app()->make(BonusService::class);
        $info = $bonusService->calculateBonus();
        echo "<pre>";
        print_r($info);
    }
}

<?php

namespace Lakala;

use Nette\Utils\Random;

require __DIR__ . '/vendor/autoload.php';

/**
 * @desc 内部使用的对接拉卡拉接口
 * @author ZhouTing
 * @date 2025-04-17 14:38
 */
class LklApi
{
    private static $errorMsg;

    const DEFAULT_ERROR_MSG = '操作失败,请稍候再试!';

    /**
     * @desc 电子合同申请(EC005)
     * @author ZhouTing
     * @param A1 签约商户的商户名称
     * @date 2025-04-17 14:39
     */
    public static function lklEcApply($param)
    {
    }
}

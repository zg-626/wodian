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


namespace app\common\dao\wechat;


use app\common\dao\BaseDao;
use app\common\model\BaseModel;
use app\common\model\wechat\PayQrcode;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * Class PayQrcodeDao
 * @package app\common\dao\WeChat
 * @author xaboy
 * @day 2020-04-28
 */
class PayQrcodeDao extends BaseDao
{

    /**
     * @return BaseModel
     * @author xaboy
     * @day 2020-03-30
     */
    protected function getModel(): string
    {
        return PayQrcode::class;
    }

    /**
     * @param $ticket
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-04-28
     */
    public function ticketByQrcode($ticket)
    {
        return PayQrcode::getDB()->where('ticket', $ticket)->find();
    }


    /**
     * @param $mer_id
     * @return array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-04-28
     */
    public function getQrcode($mer_id)
    {
        return PayQrcode::getDB()->where('mer_id', $mer_id)->where('status', 1)->select()->toArray();
    }
}

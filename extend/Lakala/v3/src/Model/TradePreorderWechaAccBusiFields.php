<?php
/**
 * 微信主扫场景下acc_busi_fields域内容
 * TradePreorderWechaAccBusiFields
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

use Lakala\OpenAPISDK\V3\ObjectSerializer;

class TradePreorderWechaAccBusiFields extends TradeAccBusiFields implements \JsonSerializable
{
    protected $timeoutExpress;
    protected $subAppid;
    protected $userId;
    protected $detail;
    protected $goodsTag;
    protected $attach;
    protected $deviceInfo;
    protected $limitPay;
    protected $sceneInfo;
    protected $limitPayer;
    
    public function setTimeoutExpress($timeoutExpress)
    {
        $this->timeoutExpress = $timeoutExpress;
        return $this;
    }
    
    public function getTimeoutExpress()
    {
        return $this->timeoutExpress;
    }
    
    public function setSubAppid($subAppid)
    {
        $this->subAppid = $subAppid;
        return $this;
    }
    
    public function getSubAppid()
    {
        return $this->subAppid;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setDetail($detail)
    {
        $this->detail = $detail;
        return $this;
    }
    
    public function getDetail()
    {
        return $this->detail;
    }
    
    public function setGoodsTag($goodsTag)
    {
        $this->goodsTag = $goodsTag;
        return $this;
    }
    
    public function getGoodsTag()
    {
        return $this->goodsTag;
    }
    
    public function setAttach($attach)
    {
        $this->attach = $attach;
        return $this;
    }
    
    public function getAttach()
    {
        return $this->attach;
    }
    
    public function setDeviceInfo($deviceInfo)
    {
        $this->deviceInfo = $deviceInfo;
        return $this;
    }
    
    public function getDeviceInfo()
    {
        return $this->deviceInfo;
    }
    
    public function setLimitPay($limitPay)
    {
        $this->limitPay = $limitPay;
        return $this;
    }
    
    public function getLimitPay()
    {
        return $this->limitPay;
    }
    
    public function setSceneInfo($sceneInfo)
    {
        $this->sceneInfo = $sceneInfo;
        return $this;
    }
    
    public function getSceneInfo()
    {
        return $this->sceneInfo;
    }
    
    public function setLimitPayer($limitPayer)
    {
        $this->limitPayer = $limitPayer;
        return $this;
    }
    
    public function getLimitPayer()
    {
        return $this->limitPayer;
    }

    public function jsonSerialize()
    {
        return [
            'timeout_express' => $this->timeoutExpress,
            'sub_appid' => $this->subAppid,
            'user_id' => $this->userId,
            'detail' => ObjectSerializer::jsonencode($this->detail),
            'goods_tag' => $this->goodsTag,
            'attach' => $this->attach,
            'device_info' => $this->deviceInfo,
            'limit_pay' => $this->limitPay,
            'scene_info' => $this->sceneInfo,
            'limit_payer' => $this->limitPayer,
        ];
    }
}
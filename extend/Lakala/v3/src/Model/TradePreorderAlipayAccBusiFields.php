<?php
/**
 * 支付宝主扫场景下acc_busi_fields域内容
 * TradePreorderAlipayAccBusiFields
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

use Lakala\OpenAPISDK\V3\ObjectSerializer;

class TradePreorderAlipayAccBusiFields extends TradeAccBusiFields implements \JsonSerializable
{
    protected $userId;
    protected $timeoutExpress;
    protected $extendParams;
    protected $goodsDetail;
    protected $quitUrl;
    protected $storeId;
    protected $disablePayChannels;
    protected $businessParams;
    protected $minAge;
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setTimeoutExpress($timeoutExpress)
    {
        $this->timeoutExpress = $timeoutExpress;
        return $this;
    }
    
    public function getTimeoutExpress()
    {
        return $this->timeoutExpress;
    }
    
    public function setExtendParams($extendParams)
    {
        $this->extendParams = $extendParams;
        return $this;
    }
    
    public function getExtendParams()
    {
        return $this->extendParams;
    }
    
    public function setGoodsDetail($goodsDetail)
    {
        $this->goodsDetail = $goodsDetail;
        return $this;
    }
    
    public function getGoodsDetail()
    {
        return $this->goodsDetail;
    }
    
    public function setQuitUrl($quitUrl)
    {
        $this->quitUrl = $quitUrl;
        return $this;
    }
    
    public function getQuitUrl()
    {
        return $this->quitUrl;
    }
    
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }
    
    public function getStoreId()
    {
        return $this->storeId;
    }
    
    public function setDisablePayChannels($disablePayChannels)
    {
        $this->disablePayChannels = $disablePayChannels;
        return $this;
    }
    
    public function getDisablePayChannels()
    {
        return $this->disablePayChannels;
    }
    
    public function setBusinessParams($businessParams)
    {
        $this->businessParams = $businessParams;
        return $this;
    }
    
    public function getBusinessParams()
    {
        return $this->businessParams;
    }
    
    public function setMinAge($minAge)
    {
        $this->minAge = $minAge;
        return $this;
    }
    
    public function getMinAge()
    {
        return $this->minAge;
    }

    public function jsonSerialize()
    {
        $goods = array();
        if (isset($this->goodsDetail)) {
            foreach ($this->goodsDetail as $value) {
                $goods[] = $value->jsonSerialize();
            }
        }
        return [
            'user_id' => $this->userId,
            'timeout_express' => $this->timeoutExpress,
            'extend_params' => ObjectSerializer::jsonencode($this->extendParams),
            'goods_detail' => ObjectSerializer::jsonencode($goods),
            'quit_url' => $this->quitUrl,
            'store_id' => $this->storeId,
            'disable_pay_channels' => $this->disablePayChannels,
            'business_params' => $this->businessParams,
            'min_age' => $this->minAge
        ];
    }
}
<?php
/**
 * 支付宝被扫场景下acc_busi_fields域内容
 * TradeMicropayAlipayAccBusiFields
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

use Lakala\OpenAPISDK\V3\ObjectSerializer;

class TradeMicropayAlipayAccBusiFields extends TradeAccBusiFields implements \JsonSerializable
{
    protected $extendParams;
    protected $businessParams;
    protected $goodsDetail;
    protected $storeId;
    protected $timeoutExpress;
    protected $disablePayChannels;
    protected $minAge;
    
    public function setExtendParams($extendParams)
    {
        $this->extendParams = $extendParams;
        return $this;
    }
    
    public function getExtendParams()
    {
        return $this->extendParams;
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
    
    public function setGoodsDetail($goodsDetail)
    {
        $this->goodsDetail = $goodsDetail;
        return $this;
    }
    
    public function getGoodsDetail()
    {
        return $this->goodsDetail;
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
    
    public function setTimeoutExpress($timeoutExpress)
    {
        $this->timeoutExpress = $timeoutExpress;
        return $this;
    }
    
    public function getTimeoutExpress()
    {
        return $this->timeoutExpress;
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
            'extend_params' => ObjectSerializer::jsonencode($this->extendParams),
            'business_params' => $this->businessParams,
            'goods_detail' => ObjectSerializer::jsonencode($goods),
            'store_id' => $this->storeId,
            'timeout_express' => $this->timeoutExpress,
            'disable_pay_channels' => $this->disablePayChannels,
            'min_age' => $this->minAge,
        ];
    }
}
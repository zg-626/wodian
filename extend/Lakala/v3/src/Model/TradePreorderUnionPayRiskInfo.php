<?php
/**
 * 银联云闪risk_info
 * TradePreorderUnionPayRiskInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradePreorderUnionPayRiskInfo implements \JsonSerializable
{
    protected $itemNo;
    protected $orderSource;
    protected $payUserId;
    protected $payCodeId;
    protected $merchantType;
    
    public function setItemNo($itemNo)
    {
        $this->itemNo = $itemNo;
        return $this;
    }
    
    public function getItemNo()
    {
        return $this->itemNo;
    }
    
    public function setOrderSource($orderSource)
    {
        $this->orderSource = $orderSource;
        return $this;
    }
    
    public function getOrderSource()
    {
        return $this->orderSource;
    }
    
    public function setPayUserId($payUserId)
    {
        $this->payUserId = $payUserId;
        return $this;
    }
    
    public function getPayUserId()
    {
        return $this->payUserId;
    }
    
    public function setPayCodeId($payCodeId)
    {
        $this->payCodeId = $payCodeId;
        return $this;
    }
    
    public function getPayCodeId()
    {
        return $this->payCodeId;
    }
    
    public function setMerchantType($merchantType)
    {
        $this->merchantType = $merchantType;
        return $this;
    }
    
    public function getMerchantType()
    {
        return $this->merchantType;
    }    

    public function jsonSerialize()
    {
        return [
            'itemNo' => $this->itemNo,
            'orderSource' => $this->orderSource,
            'payUserId' => $this->payUserId,
            'payCodeId' => $this->payCodeId,
            'merchantType' => $this->merchantType,
        ];
    }
}
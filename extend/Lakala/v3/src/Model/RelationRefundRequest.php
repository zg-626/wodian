<?php
/**
 * 退款交易 请求字段
 * RelationRefundRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class RelationRefundRequest extends ModelRequest implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号
    protected $merchantNo;
    // 终端号	M	String(32)	拉卡拉分配的商户号
    protected $termNo;
    // 商户交易流水号	M	String(32)	商户系统唯一
    protected $outTradeNo;
    // 退款金额	M	String(12)	单位分，整数数字型字符
    protected $refundAmount;
    // 退款原因	C	String(32)	退款原因描述
    protected $refundReason;
    // 原商户交易流水号	C	String(32)	下单时的商户请求流水号（退款时origin_out_trade_no，origin_trade_no，origin_log_no必送其一）
    protected $originOutTradeNo;
    // 原拉卡拉交易流水号	C	String(32)	下单成功时，返回的拉卡拉交易流水。 origin_out_trade_no、origin_log_no、origin_trade_no至少一个必填（调用收银台下单接口拉起交易后发起退款时至少要传两个），同时存在时优先级顺序如下： origin_trade_no、origin_log_no、origin_out_trade_no。
    protected $originTradeNo;
    // 原对账单流水号	C	String(14)	对账单中的交易流水。 origin_out_trade_no、origin_log_no、origin_trade_no至少一个必填（调用收银台下单接口拉起交易后发起退款时至少要传两个，同时存在时优先级顺序如下： origin_trade_no、origin_log_no、origin_out_trade_no。
    protected $originLogNo;
    // 地址位置信息	M	Object	地址位置信息，风控要求必送
    protected $locationInfo;
    
    public function setMerchantNo($merchantNo)
    {
        $this->merchantNo = $merchantNo;
        return $this;
    }
    
    public function getMerchantNo()
    {
        return $this->merchantNo;
    }
    
    public function setTermNo($termNo)
    {
        $this->termNo = $termNo;
        return $this;
    }
    
    public function getTermNo()
    {
        return $this->termNo;
    }
    
    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        return $this;
    }
    
    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }
    
    public function setRefundAmount($refundAmount)
    {
        $this->refundAmount = $refundAmount;
        return $this;
    }
    
    public function getRefundAmount()
    {
        return $this->refundAmount;
    }
    
    public function setRefundReason($refundReason)
    {
        $this->refundReason = $refundReason;
        return $this;
    }
    
    public function getRefundReason()
    {
        return $this->refundReason;
    }
    
    public function setOriginOutTradeNo($originOutTradeNo)
    {
        $this->originOutTradeNo = $originOutTradeNo;
        return $this;
    }
    
    public function getOriginOutTradeNo()
    {
        return $this->originOutTradeNo;
    }
    
    public function setOriginTradeNo($originTradeNo)
    {
        $this->originTradeNo = $originTradeNo;
        return $this;
    }
    
    public function getOriginTradeNo()
    {
        return $this->originTradeNo;
    }
    
    public function setOriginLogNo($originLogNo)
    {
        $this->originLogNo = $originLogNo;
        return $this;
    }
    
    public function getOriginLogNo()
    {
        return $this->originLogNo;
    }
    
    public function setLocationInfo($locationInfo)
    {
        $this->locationInfo = $locationInfo;
        return $this;
    }
    
    public function getLocationInfo()
    {
        return $this->locationInfo;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];
        if (strlen($this->merchantNo)===0) $invalidProperties[] = '商户号不能为空';
        if (strlen($this->termNo)===0) $invalidProperties[] = '终端号不能为空';
        if (strlen($this->outTradeNo)===0) $invalidProperties[] = '商户交易流水号不能为空';
        if (strlen($this->refundAmount)===0) $invalidProperties[] = '退款金额不能为空';
        if ($this->locationInfo == null) $invalidProperties[] = '地址位置信息不能为空';

        return $invalidProperties;
    }

    public function jsonSerialize()
    {
        $this->setReqData([
            'merchant_no' => $this->merchantNo,
            'term_no' => $this->termNo,
            'out_trade_no' => $this->outTradeNo,
            'refund_amount' => $this->refundAmount,
            'refund_reason' => $this->refundReason,
            'origin_out_trade_no' => $this->originOutTradeNo,
            'origin_trade_no' => $this->originTradeNo,
            'origin_log_no' => $this->originLogNo,
            'location_info' => $this->locationInfo === null ? $this->locationInfo : $this->locationInfo->jsonSerialize(),

        ]);
        return parent::jsonSerialize();
    }
}
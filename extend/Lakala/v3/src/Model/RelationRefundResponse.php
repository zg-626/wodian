<?php
/**
 * RelationRefundRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class RelationRefundResponse extends ModelResponse implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号（请求接口中商户号）
    protected $merchantNo;
    // 商户请求流水号	M	String(32)	请求中的商户请求流水号
    protected $outTradeNo;
    // 拉卡拉退款单号	M	String(32)	拉卡拉交易流水号
    protected $tradeNo;
    // 拉卡拉对账单流水号	M	String(14)	拉卡拉对账单流水号
    protected $logNo;
    // 账户端交易订单号	C	String(48)	账户端交易流水号
    protected $accTradeNo;
    // 钱包类型	C	String(32)	微信：WECHAT 支付宝：ALIPAY 银联：UQRCODEPAY 翼支付: BESTPAY 苏宁易付宝: SUNING
    protected $accountType;
    // 交易金额	M	String(12)	单位分，整数数字型字符串
    protected $totalAmount;
    // 申请退款金额	M	String(12)	单位分，整数数字型字符串
    protected $refundAmount;
    // 	实际退款金额	M	String(12)	单位分，整数数字型字符串
    protected $payerAmount;
    // 退款时间	C	String(14)	实际退款时间。yyyyMMddHHmmss
    protected $tradeTime;
    // 原拉卡拉订单号	C	String(32)	如果请求中携带，则返回
    protected $originTradeNo;
    // 原商户请求流水号	C	String(64)	如果请求中携带，则返回
    protected $originOutTradeNo;
    // 单品营销 附加数据	C	String(8000)	参与单品营销优惠时返回
    protected $upIssAddnData;
    // 银联优惠信息、出资方信息	C	String(500)	参与单品营销优惠时返回
    protected $upCouponInfo;
    // 出资方信息	C	String(512)	数字货币中行返回示例说明：[{“fundchannel”:”BOC”,”amount”:”18”}]
    protected $tradeInfo;
    
    public function setMerchantNo($merchantNo)
    {
        $this->merchantNo = $merchantNo;
        return $this;
    }
    
    public function getMerchantNo()
    {
        return $this->merchantNo;
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
    
    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
        return $this;
    }
    
    public function getTradeNo()
    {
        return $this->tradeNo;
    }
    
    public function setLogNo($logNo)
    {
        $this->logNo = $logNo;
        return $this;
    }
    
    public function getLogNo()
    {
        return $this->logNo;
    }
    
    public function setAccTradeNo($accTradeNo)
    {
        $this->accTradeNo = $accTradeNo;
        return $this;
    }
    
    public function getAccTradeNo()
    {
        return $this->accTradeNo;
    }
    
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }
    
    public function getAccountType()
    {
        return $this->accountType;
    }
    
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }
    
    public function getTotalAmount()
    {
        return $this->totalAmount;
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
    
    public function setPayerAmount($payerAmount)
    {
        $this->payerAmount = $payerAmount;
        return $this;
    }
    
    public function getPayerAmount()
    {
        return $this->payerAmount;
    }
    
    public function setTradeTime($tradeTime)
    {
        $this->tradeTime = $tradeTime;
        return $this;
    }
    
    public function getTradeTime()
    {
        return $this->tradeTime;
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
    
    public function setOriginOutTradeNo($originOutTradeNo)
    {
        $this->originOutTradeNo = $originOutTradeNo;
        return $this;
    }
    
    public function getOriginOutTradeNo()
    {
        return $this->originOutTradeNo;
    }
    
    public function setUpIssAddnData($upIssAddnData)
    {
        $this->upIssAddnData = $upIssAddnData;
        return $this;
    }
    
    public function getUpIssAddnData()
    {
        return $this->upIssAddnData;
    }
    
    public function setUpCouponInfo($upCouponInfo)
    {
        $this->upCouponInfo = $upCouponInfo;
        return $this;
    }
    
    public function getUpCouponInfo()
    {
        return $this->upCouponInfo;
    }
    
    public function setTradeInfo($tradeInfo)
    {
        $this->tradeInfo = $tradeInfo;
        return $this;
    }
    
    public function getTradeInfo()
    {
        return $this->tradeInfo;
    }
}
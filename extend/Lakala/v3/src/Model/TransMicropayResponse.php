<?php
/**
 * TransMicropayResponse
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TransMicropayResponse extends ModelResponse implements \JsonSerializable
{
    // 是否需要发起查询	M	String(32)	0=不需要 1=需要 当返回1时，代表订单处理中，商户需主动发起查询
    protected $needQuery;
    // 商户号	M	String(32)	拉卡拉分配的商户号（请求接口中商户号）
    protected $merchantNo;
    // 商户交易流水号	M	String(32)	请求报文中的商户交易流水号
    protected $outTradeNo;
    // 拉卡拉交易流水号	M	String(32)	拉卡拉交易流水号
    protected $tradeNo;
    // 拉卡拉对账单流水号	M	String(14)	拉卡拉对账单流水号
    protected $logNo;
    // 账户端交易订单号	C	String(48)	账户端交易流水号
    protected $accTradeNo;
    /**
      * 钱包类型	M	String(16)
      * 微信        WECHAT
      * 支付宝      ALIPAY
      * 银联        UQRCODEPAY
      * 翼支付      BESTPAY
      * 苏宁易付宝   SUNING
      * 数字货币    DCPAY
      */
    protected $accountType;
    // 订单金额	M	String(12)	单位分，整数数字型字符 订单金额=付款人实际发生金额+商户优惠金额+账户端优惠金额
    protected $totalAmount;
    // 付款人实际发生金额	M	String(12)
    protected $payerAmount;
    // 账户端应结订单金额	M	String(12)	应结订单金额，单位分 ，账户端应结订单金额=付款人实际发生金额+账户端优惠金额
    protected $accSettleAmount;
    // 商户优惠金额（账户端）	C	String(12)	账户端返回商户优惠金额，单位分
    protected $accMdiscountAmount;
    // 账户端优惠金额	C	String(12)	账户端返回账户端优惠金额，单位分
    protected $accDiscountAmount;
    // 账户端其它优惠金额	C	String(12)	账户端返回账户端其它优惠金额，单位分
    protected $accOtherDiscountAmount;
    // 交易完成时间	M	String(14)	以账户端返回时间为准
    protected $tradeTime;
    // 付款银行	C	String(128)	付款银行
    protected $bankType;
    /**
     * 银行卡类型	C	String(16)	
     * 00：借记
     * 01：贷记
     * 02：微信零钱
     * 03：支付宝花呗
     * 04：支付宝其他
     * 05：数字货币
     * 06：拉卡拉支付账户
     * 99：未知
     */
    protected $cardType;
    // 备注	C	String(128)
    protected $remark;
    // 账户端返回信息域	C	Object	账户端返回信息域
    protected $accRespFields;
    
    public function setNeedQuery($needQuery)
    {
        $this->needQuery = $needQuery;
        return $this;
    }
    
    public function getNeedQuery()
    {
        return $this->needQuery;
    }
    
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
    
    public function setPayerAmount($payerAmount)
    {
        $this->payerAmount = $payerAmount;
        return $this;
    }
    
    public function getPayerAmount()
    {
        return $this->payerAmount;
    }
    
    public function setAccSettleAmount($accSettleAmount)
    {
        $this->accSettleAmount = $accSettleAmount;
        return $this;
    }
    
    public function getAccSettleAmount()
    {
        return $this->accSettleAmount;
    }
    
    public function setAccMdiscountAmount($accMdiscountAmount)
    {
        $this->accMdiscountAmount = $accMdiscountAmount;
        return $this;
    }
    
    public function getAccMdiscountAmount()
    {
        return $this->accMdiscountAmount;
    }
    
    public function setAccDiscountAmount($accDiscountAmount)
    {
        $this->accDiscountAmount = $accDiscountAmount;
        return $this;
    }
    
    public function getAccDiscountAmount()
    {
        return $this->accDiscountAmount;
    }
    
    public function setAccOtherDiscountAmount($accOtherDiscountAmount)
    {
        $this->accOtherDiscountAmount = $accOtherDiscountAmount;
        return $this;
    }
    
    public function getAccOtherDiscountAmount()
    {
        return $this->accOtherDiscountAmount;
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
    
    public function setBankType($bankType)
    {
        $this->bankType = $bankType;
        return $this;
    }
    
    public function getBankType()
    {
        return $this->bankType;
    }
    
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;
        return $this;
    }
    
    public function getCardType()
    {
        return $this->cardType;
    }
    
    public function setRemark($remark)
    {
        $this->remark = $remark;
        return $this;
    }
    
    public function getRemark()
    {
        return $this->remark;
    }
    
    public function setAccRespFields($accRespFields)
    {
        $this->accRespFields = $accRespFields;
        return $this;
    }
    
    public function getAccRespFields()
    {
        return $this->accRespFields;
    }
}
<?php
/**
 * QueryTradequeryRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class QueryTradequeryResponse extends ModelResponse implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号（请求接口中商户号）
    protected $merchantNo;
    // 商户请求流水号	M	String(32)	请求中的商户请求流水号
    protected $outTradeNo;
    // 拉卡拉商户订单号	M	String(32)	拉卡拉生成的交易流水
    protected $tradeNo;
    // 	拉卡拉对账单流水号	M	String(14)	trade_no的后14位
    protected $logNo;
    // 交易大类	C	String(32)	PREORDER-主扫，MICROPAY-被扫，REFUND-退款，CANCEL-撤销，无-其它类型
    protected $tradeMainType;
    // 拆单属性	C	String(1)	只有涉及合单交易时会出现：M-主单，S-子单
    protected $splitAttr;
    // 拆单信息	C	List<>	如果查询订单是主单，则返回。见splitInfo字段说明。拆单信息见split_info域说明
    protected $splitInfo;
    // 账户端交易订单号	M	String(48)	账户端交易流水号
    protected $accTradeNo;
    // 钱包类型	M	String(32)	微信：WECHAT 支付宝：ALIPAY 银联：UQRCODEPAY 翼支付: BESTPAY 苏宁易付宝: SUNING
    protected $accountType;
    // 交易状态	M	String(16)	INIT-初始化 CREATE-下单成功 SUCCESS-交易成功 FAIL-交易失败 DEAL-交易处理中 UNKNOWN-未知状态 CLOSE-订单关闭 PART_REFUND-部分退款 REFUND-全部退款(或订单被撤销）交易状态	M	String(16)	INIT-初始化 CREATE-下单成功 SUCCESS-交易成功 FAIL-交易失败 DEAL-交易处理中 UNKNOWN-未知状态 CLOSE-订单关闭 PART_REFUND-部分退款 REFUND-全部退款(或订单被撤销）
    protected $tradeState;
    // 	交易状态描述	C	String(256)	交易状态描述
    protected $tradeStateDesc;
    // 订单金额	M	String(12)	单位分，整数数字型字符
    protected $totalAmount;
    // 付款人实付金额	C	String(12)	付款人实付金额，单位分
    protected $payerAmount;
    // 账户端结算金额	C	String(12)	账户端应结订单金额，单位分
    protected $accSettleAmount;
    // 商户侧优惠金额（账户端）	C	String(12)	商户优惠金额，单位分
    protected $accMdiscountAmount;
    // 账户端优惠金额	C	String(12)	拉卡拉优惠金额，单位分
    protected $accDiscountAmount;
    // 账户端其它优惠金额	C	String(12)	
    protected $accOtherDiscountAmount;
    // 交易完成时间	C	String(14)	实际支付时间。yyyyMMddHHmmss
    protected $tradeTime;
    // 用户标识1	C	String(128)	微信sub_open_id 支付宝buyer_logon_id（买家支付宝账号）
    protected $userId1;
    // 用户标识2	C	String(128)	微信openId支 付宝buyer_user_id 银联user_id
    protected $userId2;
    // 付款银行	C	String(128)	付款银行
    protected $bankType;
    // 银行卡类型	C	String(16)	00：借记 01：贷记 02：微信零钱 03：支付宝花呗 04：支付宝其他 05：数字货币 06：拉卡拉支付账户 99：未知
    protected $cardType;
    // 活动 ID	C	String(32)	在账户端商户后台配置的批次 ID
    protected $accActivityId;
    // 账户端返回信息域	C	Object	账户端返回信息域
    protected $accRespFields;
    // 合单退款拆单信息	C	List<>	如果查询订单是退款主单，则返回。见refundSplitInfo字段说明。拆单信息见refund_split_info域说明
    protected $refundSplitInfo;
    
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
    
    public function setTradeMainType($tradeMainType)
    {
        $this->tradeMainType = $tradeMainType;
        return $this;
    }
    
    public function getTradeMainType()
    {
        return $this->tradeMainType;
    }
    
    public function setSplitAttr($splitAttr)
    {
        $this->splitAttr = $splitAttr;
        return $this;
    }
    
    public function getSplitAttr()
    {
        return $this->splitAttr;
    }
    
    public function setSplitInfo($splitInfo)
    {
        $this->splitInfo = $splitInfo;
        return $this;
    }
    
    public function getSplitInfo()
    {
        return $this->splitInfo;
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
    
    public function setTradeState($tradeState)
    {
        $this->tradeState = $tradeState;
        return $this;
    }
    
    public function getTradeState()
    {
        return $this->tradeState;
    }
    
    public function setTradeStateDesc($tradeStateDesc)
    {
        $this->tradeStateDesc = $tradeStateDesc;
        return $this;
    }
    
    public function getTradeStateDesc()
    {
        return $this->tradeStateDesc;
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
    
    public function setUserId1($userId1)
    {
        $this->userId1 = $userId1;
        return $this;
    }
    
    public function getUserId1()
    {
        return $this->userId1;
    }
    
    public function setUserId2($userId2)
    {
        $this->userId2 = $userId2;
        return $this;
    }
    
    public function getUserId2()
    {
        return $this->userId2;
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
    
    public function setAccActivityId($accActivityId)
    {
        $this->accActivityId = $accActivityId;
        return $this;
    }
    
    public function getAccActivityId()
    {
        return $this->accActivityId;
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
    
    public function setRefundSplitInfo($refundSplitInfo)
    {
        $this->refundSplitInfo = $refundSplitInfo;
        return $this;
    }
    
    public function getRefundSplitInfo()
    {
        return $this->refundSplitInfo;
    }
}
<?php
/**
 * 被扫交易 请求字段
 * TransMicropayRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TransMicropayRequest extends ModelRequest implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号
    protected $merchantNo;
    // 终端号	M	String(32)	拉卡拉分配的业务终端号
    protected $termNo;
    // 商户交易流水号	M	String(32)	商户系统唯一，不可重复
    protected $outTradeNo;
    /**
     * 支付授权码	M	String(32)	扫码支付授权码，设备读取用户APP中的条码或者二维码信息，用户付款码条形码规则见说明
     * 
     * 属性	    说明	备注
     * 微信	    WECHAT	付款码10 11 12 13 14 15开头
     * 支付宝	ALIPAY	付款码25 26 27 28 29 30开头
     * 银联	    UQRCODEPAY	付款码62开头
     * 数币人民币	DCPAY	付款码01开头
     * 翼支付	BESTPAY	付款码51开头
     * 苏宁	    SUNING	付款码83开头
     */
    protected $authCode;
    // 金额	M	String(12)	单位分，整数型字符
    protected $totalAmount;
    /**
     * 地址位置信息	M	Object	地址位置信息，风控要求必送
     * 
     * request_ip	请求方IP地址	M	String(64)	请求方的IP地址，存在必填，格式如36.45.36.95
     * base_station	基站信息	C	String(128)	客户端设备的基站信息（主扫时基站信息使用该字段）
     * location	纬度,经度	C	String(32)	商户终端的地理位置，整体格式：纬度,经度，+表示北纬、东经，-表示南纬、 西经。
     *                              经度格式：1位正负号+3位整数+1位小数点+5位小数；
     *                              纬度格式：1位正负号+2位整数+1位小数点+6位小数；
     *                              举例：+31.221345,+121.12345
     */
    protected $locationInfo;
    // 业务模式	C	String(8)	业务模式： ACQ-收单 不填，默认为“ACQ-收单”
    protected $busiMode;
    // 订单标题	C	String(42)	标题，用于简单描述订单或商品（账户端控制，实际最多42个字符），微信支付必送。
    protected $subject;
    // 拉卡拉支付业务订单号	C	String(64)	拉卡拉订单系统订单号，以拉卡拉支付业务订单号为驱动的支付行为，需上传该字段。 订单交易下单，交易时上送订单系统订单号，交易流程中会校验有效性、判重
    protected $payOrderNo;
    // 商户通知地址	C	String(128)	商户通知地址，如上传，且 pay_order_no 不存在情况下，且支付响应报文是交易中状态的场景下，则按此地址通知商户
    protected $notifyUrl;
    // 结算类型	C	String(1)	“0”或者空，常规结算方式；
    protected $settleType;
    // 备注	C	String(128)
    protected $remark;
    // 扫码类型	C	String(1)	0或不填：扫码支付 1：支付宝刷脸支付；2: 微信刷脸支付
    protected $scanType;
    // 营销信息	C	String(1024)	拉卡拉优惠相关信息，JSON格式。暂不支持
    protected $promoInfo;
    // 账户端业务信息域	C	Object	参见以下acc_busi_fields字段详细说明,不同类型的auth_code对应不同的账户端，需要填写不同的信息
    protected $accBusiFields;
    // 商户订单号	C	String(32)	商品订单号，如动态码关联的某个商品订单号，每个外部订单来源下的外部商户订单号不可重复
    protected $outOrderNo;
    // 服务商机构标识码	C	String(11)	银联分配的服务商机构标识码
    protected $pnrInsIdCd;

    public function __construct()
    {
        parent::__construct();

        $this->busiMode = 'ACQ';
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
    
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
        return $this;
    }
    
    public function getAuthCode()
    {
        return $this->authCode;
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
    
    public function setLocationInfo($locationInfo)
    {
        $this->locationInfo = $locationInfo;
        return $this;
    }
    
    public function getLocationInfo()
    {
        return $this->locationInfo;
    }
    
    public function setBusiMode($busiMode)
    {
        $this->busiMode = $busiMode;
        return $this;
    }
    
    public function getBusiMode()
    {
        return $this->busiMode;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    public function getSubject()
    {
        return $this->subject;
    }
    
    public function setPayOrderNo($payOrderNo)
    {
        $this->payOrderNo = $payOrderNo;
        return $this;
    }
    
    public function getPayOrderNo()
    {
        return $this->payOrderNo;
    }
    
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }
    
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }
    
    public function setSettleType($settleType)
    {
        $this->settleType = $settleType;
        return $this;
    }
    
    public function getSettleType()
    {
        return $this->settleType;
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
    
    public function setScanType($scanType)
    {
        $this->scanType = $scanType;
        return $this;
    }
    
    public function getScanType()
    {
        return $this->scanType;
    }
    
    public function setPromoInfo($promoInfo)
    {
        $this->promoInfo = $promoInfo;
        return $this;
    }
    
    public function getPromoInfo()
    {
        return $this->promoInfo;
    }
    
    public function setAccBusiFields($accBusiFields)
    {
        $this->accBusiFields = $accBusiFields;
        return $this;
    }
    
    public function getAccBusiFields()
    {
        return $this->accBusiFields;
    }
    
    public function setOutOrderNo($outOrderNo)
    {
        $this->outOrderNo = $outOrderNo;
        return $this;
    }
    
    public function getOutOrderNo()
    {
        return $this->outOrderNo;
    }

    public function setPnrInsIdCd($pnrInsIdCd)
    {
        $this->pnrInsIdCd = $pnrInsIdCd;
        return $this;
    }
    
    public function getPnrInsIdCd()
    {
        return $this->pnrInsIdCd;
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
        if (strlen($this->authCode)===0) $invalidProperties[] = '支付授权码不能为空';
        if (strlen($this->totalAmount)===0) $invalidProperties[] = '金额不能为空';
        if ($this->locationInfo == null) $invalidProperties[] = '地址位置信息不能为空';

        return $invalidProperties;
    }

    public function jsonSerialize()
    {
        $this->setReqData([
            'merchant_no' => $this->merchantNo,
            'term_no' => $this->termNo,
            'out_trade_no' => $this->outTradeNo,
            'auth_code' => $this->authCode,
            'total_amount' => $this->totalAmount,
            'location_info' => $this->locationInfo === null ? $this->locationInfo : $this->locationInfo->jsonSerialize(),
            'busi_mode' => $this->busiMode,
            'subject' => $this->subject,
            'pay_order_no' => $this->payOrderNo,
            'notify_url' => $this->notifyUrl,
            'settle_type' => $this->settleType,
            'remark' => $this->remark,
            'scan_type' => $this->scanType,
            'promo_info' => $this->promoInfo,
            'acc_busi_fields' => $this->accBusiFields === null ? $this->accBusiFields : $this->accBusiFields->jsonSerialize(),
            'out_order_no' => $this->outOrderNo,
            'pnr_ins_id_cd' => $this->pnrInsIdCd,
        ]);
        return parent::jsonSerialize();
    }
}
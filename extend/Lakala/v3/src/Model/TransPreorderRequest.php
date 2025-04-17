<?php
/**
 * 主扫交易 请求字段
 * TransPreorderRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TransPreorderRequest extends ModelRequest implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号
    protected $merchantNo;
    // 终端号	M	String(32)	拉卡拉分配的业务终端号
    protected $termNo;
    // 商户交易流水号	M	String(32)	商户系统唯一，对应数据库表中外部请求流水号。
    protected $outTradeNo;
    /**
     * 钱包类型	M	String(32)	
     * 
     * 微信             WECHAT
     * 支付宝           ALIPAY
     * 银联             UQRCODEPAY     默认
     * 翼支付           BESTPAY
     * 苏宁易付宝        SUNING 
     * 拉卡拉支付账户     LKLACC 
     * 网联小钱包        NUCSPAY
     */
    protected $accountType;
    /**
     * 接入方式	M	String(2)
     * 
     * 41   NATIVE（（ALIPAY，云闪付支付，京东白条分期）
     * 51   JSAPI（微信公众号支付，支付宝服务窗支付，银联JS支付，翼支付JS支付、拉卡拉钱包支付）
     * 71   微信小程序支付
     * 61   APP支付
     */
    protected $transType;
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
    // 订单标题	C	String(42)	标题，用于简单描述订单或商品主题，会传递给账户端 （账户端控制，实际最多42个字符），微信支付必送。
    protected $subject;
    // 支付业务订单号	C	String(64)	拉卡拉订单系统订单号，以拉卡拉支付业务订单号为驱动的支付行为，需上传该字段。
    protected $payOrderNo;
    // 商户通知地址	C	String(128)	商户通知地址，如果上传，且 pay_order_no 不存在情况下，则按此地址通知商户(详见“[交易通知]”接口)
    protected $notifyUrl;
    // 结算类型	C	String(1)	“0”或者空，常规结算方式，如需接拉卡拉分账通需传“1”，商户未开通分账之前切记不用上送此参数。；
    protected $settleType;
    // 备注	C	String(128)
    protected $remark;
    // 优惠信息	C	String(1024)	拉卡拉优惠相关信息，JSON格式。暂不支持
    protected $promoInfo;
    // 账户端业务信息域	C	Object	参见以下acc_busi_fields字段详细说明,不同的account_type和trans_type，需要传入的参数不一样
    protected $accBusiFields;
    // 商户订单号	C	String(32)	商品订单号，如动态码关联的某个商品订单号，每个外部订单来源下的外部商户订单号不可重复
    protected $outOrderNo;
    // 服务商机构标识码	C	String(11)	银联分配的服务商机构标识码
    protected $pnrInsIdCd;

    public function __construct()
    {
        parent::__construct();

        $this->accountType = 'UQRCODEPAY';
        $this->transType = '41';
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

    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function setTransType($transType)
    {
        $this->transType = $transType;
        return $this;
    }

    public function getTransType()
    {
        return $this->transType;
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
        if (strlen($this->accountType)===0) $invalidProperties[] = '钱包类型不能为空';
        if (strlen($this->transType)===0) $invalidProperties[] = '接入方式接入方式不能为空';
        if (strlen($this->totalAmount)===0) $invalidProperties[] = '金额不能为空';
        if ($this->locationInfo == null) $invalidProperties[] = '地址位置信息不能为空';
        if ($this->accountType === 'WECHAT' && strlen($this->subject) === 0) {
            $invalidProperties[] = '微信支付必需上送订单标题字段[subject]';
        }

        return $invalidProperties;
    }

    public function jsonSerialize()
    {
        $this->setReqData([
            'merchant_no' => $this->merchantNo,
            'term_no' => $this->termNo,
            'out_trade_no' => $this->outTradeNo,
            'account_type' => $this->accountType,
            'trans_type' => $this->transType,
            'total_amount' => $this->totalAmount,
            'location_info' => $this->locationInfo === null ? $this->locationInfo : $this->locationInfo->jsonSerialize(),
            'busi_mode' => $this->busiMode,
            'subject' => $this->subject,
            'pay_order_no' => $this->payOrderNo,
            'notify_url' => $this->notifyUrl,
            'settle_type' => $this->settleType,
            'remark' => $this->remark,
            'promo_info' => $this->promoInfo,
            'acc_busi_fields' => $this->accBusiFields === null ? $this->accBusiFields : $this->accBusiFields->jsonSerialize(),
            'out_order_no' => $this->outOrderNo,
            'pnr_ins_id_cd' => $this->pnrInsIdCd,
        ]);
        return parent::jsonSerialize();
    }
}
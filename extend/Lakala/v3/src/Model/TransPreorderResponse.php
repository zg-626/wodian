<?php
/**
 * TransPreorderRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TransPreorderResponse extends ModelResponse implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号（请求接口中商户号）
    protected $merchantNo;
    // 商户请求流水号	M	String(32)	请求报文中的商户请求流水号
    protected $outTradeNo;
    // 拉卡拉交易流水号	M	String(32)	拉卡拉交易流水号
    protected $tradeNo;
    // 拉卡拉对账单流水号	M	String(14)	拉卡拉对账单流水号
    protected $logNo;
    // 结算商户号	M	String(32)	拉卡拉分配的商户号
    protected $settleMerchantNo;
    // 结算终端号	M	String(32)	拉卡拉分配的业务终端号
    protected $settleTermNo;
    // 账户端返回信息域	C	Object	账户端返回信息域
    protected $accRespFields;

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

    public function setSettleMerchantNo($settleMerchantNo)
    {
        $this->settleMerchantNo = $settleMerchantNo;
        return $this;
    }

    public function getSettleMerchantNo()
    {
        return $this->settleMerchantNo;
    }

    public function setSettleTermNo($settleTermNo)
    {
        $this->settleTermNo = $settleTermNo;
        return $this;
    }

    public function getSettleTermNo()
    {
        return $this->settleTermNo;
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
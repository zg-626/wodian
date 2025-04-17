<?php
/**
 * 查询交易 请求字段
 * QueryTradequeryRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class QueryTradequeryRequest extends ModelRequest implements \JsonSerializable
{
    // 商户号	M	String(32)	拉卡拉分配的商户号
    protected $merchantNo;
    // 终端号	M	String(32)	拉卡拉分配的业务终端号
    protected $termNo;
    // 商户交易流水号	C	String(32) 下单时的商户请求流水号 说明：out_trade_no、trade_no、必有其一。如果存在多个字段上送，优先级顺序如下： trade_no、 out_trade_no
    protected $outTradeNo;
    // 拉卡拉交易流水号	C	String(32) 拉卡拉交易流水号
    protected $tradeNo;
    
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
    
    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
        return $this;
    }
    
    public function getTradeNo()
    {
        return $this->tradeNo;
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
        if (strlen($this->outTradeNo)===0 && strlen($this->tradeNo)===0) $invalidProperties[] = '商户交易流水号、拉卡拉交易流水号至少一项不能为空';

        return $invalidProperties;
    }

    public function jsonSerialize()
    {
        $this->setReqData([
            'merchant_no' => $this->merchantNo,
            'term_no' => $this->termNo,
            'out_trade_no' => $this->outTradeNo,
            'trade_no' => $this->tradeNo,
        ]);
        return parent::jsonSerialize();
    }
}
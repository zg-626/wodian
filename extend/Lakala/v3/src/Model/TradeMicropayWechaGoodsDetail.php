<?php
/**
 * 微信goods_detail字段说明
 * TradeMicropayWechaGoodsDetail
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradeMicropayWechaGoodsDetail extends TradeGoodsDetail implements \JsonSerializable
{
    protected $goodsId;
    protected $wxpayGoodsId;
    protected $goodsName;
    protected $quantity;
    protected $price;
    
    public function setGoodsId($goodsId)
    {
        $this->goodsId = $goodsId;
        return $this;
    }
    
    public function getGoodsId()
    {
        return $this->goodsId;
    }
    
    public function setWxpayGoodsId($wxpayGoodsId)
    {
        $this->wxpayGoodsId = $wxpayGoodsId;
        return $this;
    }
    
    public function getWxpayGoodsId()
    {
        return $this->wxpayGoodsId;
    }
    
    public function setGoodsName($goodsName)
    {
        $this->goodsName = $goodsName;
        return $this;
    }
    
    public function getGoodsName()
    {
        return $this->goodsName;
    }
    
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    public function getQuantity()
    {
        return $this->quantity;
    }
    
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
    
    public function getPrice()
    {
        return $this->price;
    }

    public function jsonSerialize()
    {
        return [
            'goods_id' => $this->goodsId,
            'wxpay_goods_id' => $this->wxpayGoodsId,
            'goods_name' => $this->goodsName,
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
}
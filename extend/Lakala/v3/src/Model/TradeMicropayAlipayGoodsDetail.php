<?php
/**
 * goods_detail 支付宝goods_detail字段
 * TradeMicropayAlipayGoodsDetail
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradeMicropayAlipayGoodsDetail extends TradeGoodsDetail implements \JsonSerializable
{
    protected $goodsId;
    protected $alipayGoodsId;
    protected $goodsName;
    protected $quantity;
    protected $price;
    protected $goodsCategory;
    protected $categoriesTree;
    protected $body;
    protected $showUrl;
    
    public function setGoodsId($goodsId)
    {
        $this->goodsId = $goodsId;
        return $this;
    }
    
    public function getGoodsId()
    {
        return $this->goodsId;
    }
    
    public function setAlipayGoodsId($alipayGoodsId)
    {
        $this->alipayGoodsId = $alipayGoodsId;
        return $this;
    }
    
    public function getAlipayGoodsId()
    {
        return $this->alipayGoodsId;
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
    
    public function setGoodsCategory($goodsCategory)
    {
        $this->goodsCategory = $goodsCategory;
        return $this;
    }
    
    public function getGoodsCategory()
    {
        return $this->goodsCategory;
    }
    
    public function setCategoriesTree($categoriesTree)
    {
        $this->categoriesTree = $categoriesTree;
        return $this;
    }
    
    public function getCategoriesTree()
    {
        return $this->categoriesTree;
    }
    
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setShowUrl($showUrl)
    {
        $this->showUrl = $showUrl;
        return $this;
    }
    
    public function getShowUrl()
    {
        return $this->showUrl;
    }
    
    public function jsonSerialize()
    {
        return [
            'goods_id' => $this->goodsId,
            'alipay_goods_id' => $this->alipayGoodsId,
            'goods_name' => $this->goodsName,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'goods_category' => $this->goodsCategory,
            'categories_tree' => $this->categoriesTree,
            'body' => $this->body,
            'show_url' => $this->showUrl,
        ];
    }
}
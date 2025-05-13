<?php
/**
 * 银联云闪acq_addn_data_order_info字段
 * TradePreorderUnionPayAcqAddnDataGoodsInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradePreorderUnionPayAcqAddnDataGoodsInfo implements \JsonSerializable
{
    protected $id;
    protected $name;
    protected $price;
    protected $quantity;
    protected $category;
    protected $addninfo;
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
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
    
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    public function getQuantity()
    {
        return $this->quantity;
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }
    
    public function getCategory()
    {
        return $this->category;
    }
    
    public function setAddninfo($addninfo)
    {
        $this->addninfo = $addninfo;
        return $this;
    }
    
    public function getAddninfo()
    {
        return $this->addninfo;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'category' => $this->category,
            'addninfo' => $this->addninfo,
        ];
    }
}
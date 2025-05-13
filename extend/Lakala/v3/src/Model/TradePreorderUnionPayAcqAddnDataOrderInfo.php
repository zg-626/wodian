<?php
/**
 * 银联云闪acq_addn_data_order_info字段
 * TradePreorderUnionPayAcqAddnDataOrderInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradePreorderUnionPayAcqAddnDataOrderInfo implements \JsonSerializable
{
    protected $title;
    protected $dctAmount;
    protected $addnInfo;
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setDctAmount($dctAmount)
    {
        $this->dctAmount = $dctAmount;
        return $this;
    }
    
    public function getDctAmount()
    {
        return $this->dctAmount;
    }
    
    public function setAddnInfo($addnInfo)
    {
        $this->addnInfo = $addnInfo;
        return $this;
    }
    
    public function getAddnInfo()
    {
        return $this->addnInfo;
    }

    public function jsonSerialize()
    {
        return [
            'title' => $this->title,
            'dctAmount' => $this->dctAmount,
            'addnInfo' => $this->addnInfo,
        ];
    }
}
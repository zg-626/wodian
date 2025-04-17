<?php
/**
 * 支付宝被扫场景下 extend_params字段说明
 * TradeMicropayWechaDetail
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

use Lakala\OpenAPISDK\V3\ObjectSerializer;

class TradeMicropayWechaDetail implements \JsonSerializable
{
    protected $costPrice;
    protected $receiptId;
    protected $goodsDetail;
    
    public function setCostPrice($costPrice)
    {
        $this->costPrice = $costPrice;
        return $this;
    }

    public function getCostPrice()
    {
        return $this->costPrice;
    }

    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
        return $this;
    }

    public function getReceiptId()
    {
        return $this->receiptId;
    }
    
    public function setGoodsDetail($goodsDetail)
    {
        $this->goodsDetail = $goodsDetail;
        return $this;
    }
    
    public function getGoodsDetail()
    {
        return $this->goodsDetail;
    }

    public function jsonSerialize()
    {
        $goods = array();
        if (isset($this->goodsDetail)) {
            foreach ($this->goodsDetail as $value) {
                $goods[] = $value->jsonSerialize();
            }
        }
        return [
            'cost_price' => $this->costPrice,
            'receipt_id' => $this->receiptId,
            'goods_detail' => $goods,
        ];
    }
}
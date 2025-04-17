<?php
/**
 * 支付宝被扫场景下 extend_params字段说明
 * TradeMicropayAlipayExtendParams
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradeMicropayAlipayExtendParams extends TradeExtendParams implements \JsonSerializable
{
    protected $sysServiceProviderId;
    protected $hbFqNum;
    protected $hbFqSellerPercent;
    
    public function setSysServiceProviderId($sysServiceProviderId)
    {
        $this->sysServiceProviderId = $sysServiceProviderId;
        return $this;
    }
    
    public function getSysServiceProviderId()
    {
        return $this->sysServiceProviderId;
    }
    
    public function setHbFqNum($hbFqNum)
    {
        $this->hbFqNum = $hbFqNum;
        return $this;
    }
    
    public function getHbFqNum()
    {
        return $this->hbFqNum;
    }
    
    public function setHbFqSellerPercent($hbFqSellerPercent)
    {
        $this->hbFqSellerPercent = $hbFqSellerPercent;
        return $this;
    }
    
    public function getHbFqSellerPercent()
    {
        return $this->hbFqSellerPercent;
    }

    public function jsonSerialize()
    {
        return [
            'sys_service_provider_id' => $this->sysServiceProviderId,
            'hb_fq_num' => $this->hbFqNum,
            'hb_fq_seller_percent' => $this->hbFqSellerPercent,
        ];
    }
}
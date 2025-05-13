<?php
/**
 * 银联云闪addn_info字段
 * TradePreorderUnionPayAddnInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradePreorderUnionPayAddnInfo implements \JsonSerializable
{
    protected $preproduct;
    protected $lockplan;
    protected $riskInfo;
    
    public function setPreproduct($preproduct)
    {
        $this->preproduct = $preproduct;
        return $this;
    }
    
    public function getPreproduct()
    {
        return $this->preproduct;
    }
    
    public function setLockplan($lockplan)
    {
        $this->lockplan = $lockplan;
        return $this;
    }
    
    public function getLockplan()
    {
        return $this->lockplan;
    }
    
    public function setRiskInfo($riskInfo)
    {
        $this->riskInfo = $riskInfo;
        return $this;
    }
    
    public function getRiskInfo()
    {
        return $this->riskInfo;
    }

    public function jsonSerialize()
    {
        return [
            'preproduct' => $this->preproduct,
            'lockplan' => $this->lockplan,
            'riskInfo' => $this->riskInfo,
        ];
    }
}
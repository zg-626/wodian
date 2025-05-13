<?php
/**
 * 查询交易 请求字段
 * TradeLocationInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradeLocationInfo implements \JsonSerializable
{
    protected $requestIp;
    protected $baseStation;
    protected $location;

    public function __construct($ip)
    {
        $this->requestIp = $ip;
    }
    
    public function setRequestIp($requestIp)
    {
        $this->requestIp = $requestIp;
        return $this;
    }
    
    public function getRequestIp()
    {
        return $this->requestIp;
    }
    
    public function setBaseStation($baseStation)
    {
        $this->baseStation = $baseStation;
        return $this;
    }
    
    public function getBaseStation()
    {
        return $this->baseStation;
    }
    
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }
    
    public function getLocation()
    {
        return $this->location;
    }

    public function jsonSerialize()
    {
        return [
            'request_ip' => $this->requestIp,
            'base_station' => $this->baseStation,
            'location' => $this->location,
        ];
    }
}
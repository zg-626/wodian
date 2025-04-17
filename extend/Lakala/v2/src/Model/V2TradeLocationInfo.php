<?php
/**
 * 地址位置信息
 * TradeLocationInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V2\Model;

class V2TradeLocationInfo implements \JsonSerializable
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
            'requestIp' => $this->requestIp,
            'baseStation' => $this->baseStation,
            'location' => $this->location,
        ];
    }
}
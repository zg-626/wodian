<?php
/**
 * V2ModelRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V2\Model;

class V2ModelRequest {
    // 请求时间	M	String(14)	请求时间，格式yyyyMMddHHmmss
    protected $timestamp;
    // 随机数	C	String(32)	随机数
    protected $rnd;
    // 版本号	C	String(6)	1.0.0
    protected $ver;
    // 请求序列号	C	String(32)	-
    protected $reqId;
    // 请求参数	M	Object	参见各个接口的请求参数格式
    protected $reqData;
    // 地址位置信息	M	Object
    protected $locationInfo;
    // 终端信息	M	Object	无特殊处理需求或无终端信息，填写”termExtInfo”:{}
    protected $termExtInfo;

    public function __construct()
    {
        $this->ver = '1.0.0';

        $mictime = microtime();
        list($usec, $sec) = explode(" ", $mictime);
        $this->timestamp = $sec . substr($usec, 2, 3);

        $this->reqData = [];
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }
    
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    public function setRnd($rnd)
    {
        $this->rnd = $rnd;
        return $this;
    }
    
    public function getRnd()
    {
        return $this->rnd;
    }
    
    public function setVer($ver)
    {
        $this->ver = $ver;
        return $this;
    }
    
    public function getVer()
    {
        return $this->ver;
    }
    
    public function setReqId($reqId)
    {
        $this->reqId = $reqId;
        return $this;
    }
    
    public function getReqId()
    {
        return $this->reqId;
    }
    
    public function setReqData($reqData)
    {
        $this->reqData = $reqData;
        return $this;
    }
    
    public function getReqData()
    {
        return $this->reqData;
    }
    
    public function setLocationInfo($locationInfo)
    {
        $this->locationInfo = $locationInfo;
        return $this;
    }
    
    public function getLocationInfo()
    {
        return $this->locationInfo;
    }
    
    public function setTermExtInfo($termExtInfo)
    {
        $this->termExtInfo = $termExtInfo;
        return $this;
    }
    
    public function getTermExtInfo()
    {
        return $this->termExtInfo;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    public function jsonSerialize()
    {
        return [
            'timestamp' => $this->timestamp,
            'rnd' => $this->rnd,
            'ver' => $this->ver,
            'reqId' => $this->reqId,
            'reqData' => $this->reqData,
            'locationInfo' => $this->locationInfo === null ? $this->locationInfo : $this->locationInfo->jsonSerialize(),
            'termExtInfo' => $this->termExtInfo === null ? $this->termExtInfo : $this->termExtInfo->jsonSerialize(),
        ];
    }
}
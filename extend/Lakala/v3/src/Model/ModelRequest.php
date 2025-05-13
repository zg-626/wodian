<?php
/**
 * ModelRequest
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class ModelRequest {
    // 请求时间	M	String(14)	请求时间，格式yyyyMMddHHmmss
    protected $reqTime;
    // 版本号	M	String(8)	3.0
    protected $version;
    // 请求参数	M	Object	参见各个接口的请求参数格式
    protected $reqData;

    public function __construct()
    {
        $this->version = '3.0';
        $this->reqTime = date('YmdHis', time());
        $this->reqData = [];
    }

    public function setReqTime($reqTime)
    {
        $this->reqTime = $reqTime;
        return $this;
    }
    
    public function getReqTime()
    {
        return $this->reqTime;
    }
    
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
    
    public function getVersion()
    {
        return $this->version;
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
            'req_time' => $this->getReqTime(),
            'version' => $this->getVersion(),
            'req_data' => $this->getReqData()
        ];
    }
}
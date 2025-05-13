<?php
/**
 * V2ModelResponse
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V2\Model;

class V2ModelResponse {
    protected $retCode;
    protected $retMsg;
    protected $sign;
    protected $timestamp;
    protected $rnd;
    protected $reqId;
    protected $respId;
    protected $ver;
    protected $respData;
    
    public function setRetCode($retCode)
    {
        $this->retCode = $retCode;
        return $this;
    }
    
    public function getRetCode()
    {
        return $this->retCode;
    }
    
    public function setRetMsg($retMsg)
    {
        $this->retMsg = $retMsg;
        return $this;
    }
    
    public function getRetMsg()
    {
        return $this->retMsg;
    }
    
    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }
    
    public function getSign()
    {
        return $this->sign;
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
    
    public function setReqId($reqId)
    {
        $this->reqId = $reqId;
        return $this;
    }
    
    public function getReqId()
    {
        return $this->reqId;
    }
    
    public function setRespId($respId)
    {
        $this->respId = $respId;
        return $this;
    }
    
    public function getRespId()
    {
        return $this->respId;
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
    
    public function setRespData($respData)
    {
        $this->respData = $respData;
        return $this;
    }
    
    public function getRespData()
    {
        return $this->respData;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setOriginalText($originalText) {
        $this->originalText = $originalText;
        return $this;
    }

    public function getOriginalText() {
        return $this->originalText;
    }

    public function jsonSerialize()
    {
       return V2ObjectSerializer::sanitizeForSerialization($this);
    }
}
<?php
/**
 * ModelTradeNotify
 * 交易回调Model
 * 
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class ModelTradeNotify {
    // 响应头信息
    protected $headers;
    // 响应原文
    protected $originalText;

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
       return ObjectSerializer::sanitizeForSerialization($this);
    }
}
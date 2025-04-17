<?php
/**
 * ModelResponse
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class ModelResponse {
    // 返回业务代码	M	String(8)	返回业务代码(000000为成功，其余按照错误信息来定)
    /**
     * 交易码	 交易码描述
     * BBS00000	成功
     * BBS10000	交易状态为支付中
     * 000100	网络请求失败
     * 000101	网络请求超时
     * 其它	失败
     */
    protected $code;
    // 返回业务代码描述	M	String(64)	返回业务代码描述
    protected $msg;
    // 请求时间	M	String(14)	请求时间，格式yyyyMMddHHmmss
    protected $respTime;
    // 响应参数	C	Object	返回数据.下文定义的响应均为该属性中的内容
    protected $respData;
    // 响应头信息
    protected $headers;
    // 响应原文
    protected $originalText;

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setMsg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function setRespTime($respTime)
    {
        $this->respTime = $respTime;
        return $this;
    }

    public function getRespTime()
    {
        return $this->respTime;
    }

    public function setRespData($respData)
    {
        $this->respData = $respData;

        if ($respData !== null) {
            foreach ($respData as $property => $value) {
                $camelProp = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
                $setter = 'set' . $camelProp;
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
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
       return ObjectSerializer::sanitizeForSerialization($this);
    }
}
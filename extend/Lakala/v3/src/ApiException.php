<?php
/**
 * ApiException
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3;

use Exception;

class ApiException extends Exception
{
    protected $responseBody;
    protected $responseHeaders;

    public function __construct($message = "", $code = 0, $responseBody = null, $responseHeaders = null)
    {
        parent::__construct($message, $code);
        $this->responseBody = $responseBody;
        $this->responseHeaders = $responseHeaders;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }
}
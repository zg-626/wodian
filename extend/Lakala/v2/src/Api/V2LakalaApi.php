<?php

namespace Lakala\OpenAPISDK\V2\Api;

use Lakala\OpenAPISDK\V2\V2ApiException;
use Lakala\OpenAPISDK\V2\V2ObjectSerializer;
use Lakala\OpenAPISDK\V2\V2Configuration;
use Lakala\OpenAPISDK\V2\Util\V2HttpService;
use Lakala\OpenAPISDK\V2\Util\V2LakalaSM4;

use Lakala\OpenAPISDK\V2\Model\V2ModelRequest;
use Lakala\OpenAPISDK\V2\Model\V2ModelResponse;

/**
 * V2EncryptMode Class
 * 请求或者响应加解密类型
 */
class V2EncryptMode
{
    // 普通无加解密：请求为明文，返回也是明文
    const NONE = 'none';
    // 只请求加密，返回为明文
    const REQUEST = 'request';
    // 请求明文、响应需解密
    const RESPONSE = 'response';
    // 请求需加密、返回需解密
    const BOTH = 'both';
}

/**
 * LakalaApi Class
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2\Api
 * @author   lucongyu
 * @link     https://o.lakala.com
 */
class V2LakalaApi
{

    /**
     * @var string 随机字符串集
     */
    protected $charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * 算法
     * @var string
     */
    protected $schema = "LKLAPI-SHA256withRSA";

    // 请求响应body使用SM4加密类型
    protected $v2EncryptMode;

    protected $config;
    protected $v2HttpService;

    protected $resourcePath;

    public function __construct(V2Configuration $config, $v2EncryptMode = V2EncryptMode::NONE)
    {
        $this->v2EncryptMode = $v2EncryptMode;
        $this->config = $config;
        $this->v2HttpService = new \Lakala\OpenAPISDK\V2\Util\V2HttpService();
    }

    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = $resourcePath;
        return $this;
    }
    
    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    public function tradeApi($resourcePath, V2ModelRequest $v2ModelRequest,
                                            $returnType = '\Lakala\OpenAPISDK\V2\Model\V2ModelResponse',
                                            $method = 'POST')
    {
        $headerParams = [];

        if (!$v2ModelRequest->valid()) {
            throw new V2ApiException(implode(',', $v2ModelRequest->listInvalidProperties()));
        }

        $httpBody = $v2ModelRequest->jsonSerialize();
        if ($httpBody !== null) {
            $httpBody = json_encode(V2ObjectSerializer::sanitizeForSerialization($httpBody));
        }
        return $this->apiWithBody($resourcePath, $httpBody, $headerParams, $method, $returnType);
    }

    public function apiWithBody($resourcePath, $httpBody, $headerParams = [], $method = 'POST',
                                               $returnType = '\Lakala\OpenAPISDK\V2\Model\V2ModelResponse')
    {
        $this->setResourcePath($resourcePath);

        if (!is_string($httpBody)) {
            $httpBody = json_encode($httpBody);
        }

        list($response, $statusCode, $httpHeader) = $this->callApi(
            $method,
            $httpBody,
            $headerParams,
            $returnType
        );

        if ($statusCode < 200 || $statusCode > 299) {
            throw new V2ApiException(
                sprintf('[%d] 连接API时出错 (%s)', $statusCode, $this->getResourcePath()),
                $statusCode,
                $httpHeader,
                $response
            );
        }

        return $response;
    }

    protected function callApi($method, $httpBody, $headerParams, $returnType)
    {
        $request = $this->prepareRequest($method, $httpBody, $headerParams);
        try {
            $options = $this->createRequestOptions();
            $options['header'] = $request['headers'];
            $options['data'] = $request['body'];
            $response = $this->v2HttpService->request($method, $request['url'], $options);
        } catch (Exception $e) {
            throw new V2ApiException("[{$e->getCode()}] {$e->getMessage()}", $e->getCode(), null, null);
        }

        $statusCode = $response['info']['http_code'];
        $responseHeaders = isset($response['header']) ? $response['header'] : null;

        if ($statusCode < 200 || $statusCode > 299) {
            throw new V2ApiException(
                sprintf('[%d] 连接API时出错 (%s)', $statusCode, $request['url']),
                $statusCode,
                $responseHeaders,
                $response['body']
            );
        }

        $responseVerifySign = $this->responseVerifySign($responseHeaders, $response['body']);
        if (!$responseVerifySign) {
            throw new V2ApiException(
                sprintf('[%d] 验证拉卡拉响应验签错误 (%s)', $statusCode, $request['url']),
                $statusCode,
                $responseHeaders,
                $response['body']
            );
        }
        // 请求咱解密
        if($this->v2EncryptMode == V2EncryptMode::RESPONSE || $this->v2EncryptMode == V2EncryptMode::BOTH) {
            // echo "\n<!-- \n" . $response['body'] . "\n--->\n";
            $sm4 = new V2LakalaSM4();
            $body = $sm4->decrypt(base64_decode($this->config->getSm4Key()), $response['body']);
            $response['content'] = json_decode($body);
            $response['body'] = $body;
        }
        $response['content']->originalText = $response['body'];

        return [
            V2ObjectSerializer::deserialize($response['content'], $returnType, $responseHeaders),
            $statusCode,
            $responseHeaders
        ];
    }

    protected function prepareRequest($method, $httpBody, $headerParams)
    {
        $url = $this->config->getHost() . $this->getResourcePath();

        // SM4加密请求体
        if($this->v2EncryptMode == V2EncryptMode::REQUEST || $this->v2EncryptMode == V2EncryptMode::BOTH) {
            $sm4 = new V2LakalaSM4();
            $httpBody = $sm4->encrypt(base64_decode($this->config->getSm4Key()), $httpBody);
        }

        $headers = $this->createHeaderParams($headerParams, $httpBody);

        return [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $httpBody,
        ];
    }

    protected function createHeaderParams($headerParams, $httpBody)
    {
        $headers = $this->config->getDefaultHeaders();

        if ($headerParams) {
            $headers = array_merge($headers, $headerParams);
        }

        $authorization = $this->getAuthorization($httpBody);
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: $authorization";

        return $headers;
    }

    protected function createRequestOptions()
    {
        $options = [
            'timeout' => 10,
            'respond_type' => \Lakala\OpenAPISDK\V2\Util\V2HttpService::RESPOND_TYPE_ARRAY,
        ];

        return $options;
    }

    protected function getAuthorization($body)
    {
        $randomString = $this->getRandomString(12);
        $timestamp = time();
        $data = $this->config->getAppId() . "\n"
                . $this->config->getSerialNo() . "\n"
                . $timestamp . "\n"
                . $randomString . "\n"
                . $body . "\n";

        $sign = $this->rsaSign($data);

        $authorization = $this->schema . " appid=\"" . $this->config->getAppId() . "\","
                        . "serial_no=\"" . $this->config->getSerialNo() . "\","
                        . "timestamp=\"" . $timestamp . "\","
                        . "nonce_str=\"" . $randomString . "\","
                        . "signature=\"" . $sign . "\"";

        return $authorization;
    }

    protected function getRandomString($length = 10) {
        $randomString = '';
        $charsetLength = strlen($this->charset);
    
        // 生成随机字符串
        for ($i = 0; $i < $length; $i++) {
            $randomChar = $this->charset[rand(0, $charsetLength - 1)];  // 随机选择字符
            $randomString .= $randomChar;
        }
    
        return $randomString;
    }

    //生成 sha256WithRSA 签名
    protected function rsaSign($content) {
        $privateContent = file_get_contents($this->config->getMerchantPrivateKeyPath());
        $privateKey = openssl_pkey_get_private($privateContent);
        if (!$privateKey) {
            throw new V2ApiException('获取私钥失败');
        }
        $res = openssl_sign($content, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        if (function_exists('openssl_free_key')) {
            openssl_free_key($privateKey);
        }
        if (!$res) {
            throw new V2ApiException('[10004] 拉卡拉字符串签名失败');
        }
        return base64_encode($sign);
    }

    protected function responseVerifySign($headers, $body) {
        $sign = $headers['Lklapi-Signature'];
        $sign = base64_decode($sign);
        $data = $headers['Lklapi-Appid'] . "\n"
                . $headers['Lklapi-Serial'] . "\n"
                . $headers['Lklapi-Timestamp'] . "\n"
                . $headers['Lklapi-Nonce'] . "\n"
                . $body . "\n";
        // $dir = dirname(__FILE__);
        // $dir = str_replace('/src/Api', '', $dir);
        $certContent = file_get_contents($this->config->getLklCertificatePath());
        $key = openssl_pkey_get_public($certContent);
        $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA256) === 1;
        return $result;
    }
}
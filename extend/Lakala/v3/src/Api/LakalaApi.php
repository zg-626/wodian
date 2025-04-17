<?php

namespace Lakala\OpenAPISDK\V3\Api;

use Lakala\OpenAPISDK\V3\ApiException;
use Lakala\OpenAPISDK\V3\ObjectSerializer;
use Lakala\OpenAPISDK\V3\Configuration;
use Lakala\OpenAPISDK\V3\Util\HttpService;
use Lakala\OpenAPISDK\V3\Util\LakalaSM4;

use Lakala\OpenAPISDK\V3\Model\ModelRequest;
use Lakala\OpenAPISDK\V3\Model\ModelResponse;

/**
 * EncryptMode Class
 * 请求或者响应加解密类型
 */
class EncryptMode
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
 * @package  Lakala\OpenAPISDK\V3\Api
 * @author   lucongyu
 * @link     https://o.lakala.com
 */
class LakalaApi
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
    protected $encryptMode;

    protected $config;
    protected $httpService;

    protected $resourcePath;

    public function __construct(Configuration $config, $encryptMode = EncryptMode::NONE)
    {
        $this->encryptMode = $encryptMode;
        $this->config = $config;
        $this->httpService = new \Lakala\OpenAPISDK\V3\Util\HttpService();
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

    public function tradeApi($resourcePath, ModelRequest $modelRequest,
                                            $returnType = '\Lakala\OpenAPISDK\V3\Model\ModelResponse',
                                            $method = 'POST')
    {
        $headerParams = [];

        if (!$modelRequest->valid()) {
            throw new ApiException(implode(',', $modelRequest->listInvalidProperties()));
        }

        $httpBody = $modelRequest->jsonSerialize();
        if ($httpBody !== null) {
            $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($httpBody), JSON_UNESCAPED_UNICODE);
        }
        return $this->apiWithBody($resourcePath, $httpBody, $headerParams, $method, $returnType);
    }

    public function apiWithBody($resourcePath, $httpBody, $headerParams = [], $method = 'POST',
                                               $returnType = '\Lakala\OpenAPISDK\V3\Model\ModelResponse')
    {
        $this->setResourcePath($resourcePath);

        if (!is_string($httpBody)) {
            $httpBody = json_encode($httpBody, JSON_UNESCAPED_UNICODE);
        }

        list($response, $statusCode, $httpHeader) = $this->callApi(
            $method,
            $httpBody,
            $headerParams,
            $returnType
        );

        if ($statusCode < 200 || $statusCode > 299) {
            throw new ApiException(
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
            $response = $this->httpService->request($method, $request['url'], $options);
        } catch (Exception $e) {
            throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", $e->getCode(), null, null);
        }

        $statusCode = $response['info']['http_code'];
        $responseHeaders = isset($response['header']) ? $response['header'] : null;

        if ($statusCode < 200 || $statusCode > 299) {
            throw new ApiException(
                sprintf('[%d] 连接API时出错 (%s)', $statusCode, $request['url']),
                $statusCode,
                $responseHeaders,
                $response['body']
            );
        }

        $responseVerifySign = $this->responseVerifySign($responseHeaders, $response['body']);
        if (!$responseVerifySign) {
            throw new ApiException(
                sprintf('[%d] 验证拉卡拉响应验签错误 (%s)', $statusCode, $request['url']),
                $statusCode,
                $responseHeaders,
                $response['body']
            );
        }
        // 请求咱解密
        if($this->encryptMode == EncryptMode::RESPONSE || $this->encryptMode == EncryptMode::BOTH) {
            // echo "\n<!-- \n" . $response['body'] . "\n--->\n";
            $sm4 = new LakalaSM4();
            $body = $sm4->decrypt(base64_decode($this->config->getSm4Key()), $response['body']);
            $response['content'] = json_decode($body);
            $response['body'] = $body;
        }
        $response['content']->originalText = $response['body'];

        return [
            ObjectSerializer::deserialize($response['content'], $returnType, $responseHeaders),
            $statusCode,
            $responseHeaders
        ];
    }

    protected function prepareRequest($method, $httpBody, $headerParams)
    {
        $url = $this->config->getHost() . $this->getResourcePath();

        // SM4加密请求体
        if($this->encryptMode == EncryptMode::REQUEST || $this->encryptMode == EncryptMode::BOTH) {
            $sm4 = new LakalaSM4();
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
            'respond_type' => \Lakala\OpenAPISDK\V3\Util\HttpService::RESPOND_TYPE_ARRAY,
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
            throw new ApiException('获取私钥失败');
        }
        $res = openssl_sign($content, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        if (function_exists('openssl_free_key')) {
            openssl_free_key($privateKey);
        }
        if (!$res) {
            throw new ApiException('[10004] 拉卡拉字符串签名失败');
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
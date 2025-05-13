<?php
/**
 * HttpService
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Util
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Util;

/**
 * HTTP请求服务
 * Class HttpService
 * @package service
 */
class HttpService
{
    // Response types
    const RESPOND_TYPE_SIMPLE = 'simple';
    const RESPOND_TYPE_ORIGINAL = 'original';
    const RESPOND_TYPE_ARRAY = 'array';
    const RESPOND_TYPE_JSON = 'json';

    /**
     * Send a GET request
     * @param string $url HTTP request URL
     * @param array $query GET request parameters
     * @param array $options CURL options
     * @return mixed
     */
    public static function get($url, array $query = [], array $options = [])
    {
        $options['query'] = $query;
        return self::request('GET', $url, $options);
    }

    /**
     * Send a POST request
     * @param string $url HTTP request URL
     * @param array $data POST request data
     * @param array $options CURL options
     * @return mixed
     */
    public static function post($url, array $data = [], array $options = [])
    {
        $options['data'] = $data;
        return self::request('POST', $url, $options);
    }

    /**
     * Send a HTTP request
     * @param string $method Request method
     * @param string $url Request URL
     * @param array $options Request options [header, data, ssl_cer, ssl_key, respond_type]
     * @return mixed
     */
    public static function request($method, $url, array $options = [])
    {
        $curl = curl_init();
// print_r($options);
        // Set GET parameters
        if (!empty($options['query'])) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($options['query']);
        }

        // Set POST data
        if (strtoupper($method) === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data']);
        }

        // Set request timeout
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout'] ?? 60);

        // Set response type
        $validRespondTypes = [self::RESPOND_TYPE_SIMPLE, self::RESPOND_TYPE_ORIGINAL, self::RESPOND_TYPE_ARRAY, self::RESPOND_TYPE_JSON];
        $respondType = in_array($options['respond_type'] ?? '', $validRespondTypes) ? $options['respond_type'] : self::RESPOND_TYPE_SIMPLE;

        // Set headers
        if (!empty($options['header'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['header']);
        }

        // Set SSL certificates
        if (!empty($options['ssl_cer']) && file_exists($options['ssl_cer'])) {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $options['ssl_cer']);
        }
        if (!empty($options['ssl_key']) && file_exists($options['ssl_key'])) {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $options['ssl_key']);
        }

        // Set common options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, $respondType !== self::RESPOND_TYPE_SIMPLE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $content = curl_exec($curl);
        $curlInfo = curl_getinfo($curl);

        // Remove BOM header
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $response = self::formatResponse($content, $curlInfo, $respondType);
        curl_close($curl);
        return $response;
    }

    /**
     * Format response based on respond type
     * @param string $content
     * @param array $curlInfo
     * @param string $respondType
     * @return mixed
     */
    private static function formatResponse($content, $curlInfo, $respondType)
    {
        switch ($respondType) {
            case self::RESPOND_TYPE_ORIGINAL:
                return $content;
            case self::RESPOND_TYPE_ARRAY:
            case self::RESPOND_TYPE_JSON:
                $headerSize = $curlInfo['header_size'];
                $header = substr($content, 0, $headerSize);
                $body = substr($content, $headerSize);
                $response = [
                    'body' => $body,
                    'content' => json_decode($body),
                    'info' => $curlInfo,
                    'header' => self::parseResponseHeader($header)
                ];
                return $respondType === self::RESPOND_TYPE_JSON ? json_encode($response, JSON_UNESCAPED_UNICODE) : $response;
            default:
                return $content;
        }
    }

    /**
     * Parse response header
     * @param string $header
     * @return array
     */
    private static function parseResponseHeader($header)
    {
        $headers = [];
        $headerLines = explode("\r\n", $header);
        $headers['status'] = $headerLines[0];
        array_shift($headerLines);
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[ucwords(trim($key), '-')] = trim($value);
            }
        }
        return $headers;
    }
}
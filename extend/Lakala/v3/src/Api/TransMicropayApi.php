<?php
/**
 * 被扫交易
 * TransMicropayApi
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Api;

use Lakala\OpenAPISDK\V3\Model\TransMicropayRequest;
use Lakala\OpenAPISDK\V3\Model\TransMicropayResponse;

class TransMicropayApi extends LakalaApi
{
    public function transMicropay(TransMicropayRequest $transMicropayRequest)
    {
        $resourcePath = $this->encryptMode == EncryptMode::REQUEST || $this->encryptMode == EncryptMode::BOTH
                      ? '/api/v3/labs/trans/micropay_encry'
                      : '/api/v3/labs/trans/micropay';

        return $this->tradeApi($resourcePath, $transMicropayRequest, '\Lakala\OpenAPISDK\V3\Model\TransMicropayResponse');
    }
}
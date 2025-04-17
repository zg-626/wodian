<?php
/**
 * 主扫交易
 * TransPreorderApi
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Api;

use Lakala\OpenAPISDK\V3\Model\TransPreorderRequest;
use Lakala\OpenAPISDK\V3\Model\TransPreorderResponse;

class TransPreorderApi extends LakalaApi
{

    public function transPreorder(TransPreorderRequest $transPreorderRequest)
    {
        $resourcePath = $this->encryptMode == EncryptMode::REQUEST || $this->encryptMode == EncryptMode::BOTH 
                      ? '/api/v3/labs/trans/preorder_encry'
                      : '/api/v3/labs/trans/preorder';

        return $this->tradeApi($resourcePath, $transPreorderRequest, '\Lakala\OpenAPISDK\V3\Model\TransPreorderResponse');
    }
}
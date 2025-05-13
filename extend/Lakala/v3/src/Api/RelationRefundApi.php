<?php
/**
 * 退款交易
 * RelationRefundApi
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Api;

use Lakala\OpenAPISDK\V3\Model\RelationRefundRequest;
use Lakala\OpenAPISDK\V3\Model\RelationRefundResponse;

class RelationRefundApi extends LakalaApi
{
    public function relationRefund(RelationRefundRequest $relationRefundRequest)
    {
        $resourcePath = '/api/v3/labs/relation/refund';
        return $this->tradeApi($resourcePath, $relationRefundRequest, '\Lakala\OpenAPISDK\V3\Model\RelationRefundResponse');
    }
}
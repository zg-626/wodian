<?php
/**
 * 查询交易
 * QueryTradequeryApi
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Api;

use Lakala\OpenAPISDK\V3\Model\QueryTradequeryRequest;
use Lakala\OpenAPISDK\V3\Model\QueryTradequeryResponse;

class QueryTradequeryApi extends LakalaApi
{
    public function queryTradequery(QueryTradequeryRequest $queryTradequeryRequest)
    {
        $resourcePath = '/api/v3/labs/query/tradequery';

        return $this->tradeApi($resourcePath, $queryTradequeryRequest, '\Lakala\OpenAPISDK\V3\Model\QueryTradequeryResponse');
    }
}
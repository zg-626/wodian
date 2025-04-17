<?php
/**
 * 网联小钱包acc_busi_fields域内容
 * TradePreorderNucspayAccBusiFields
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

class TradePreorderNucspayAccBusiFields extends TradeAccBusiFields implements \JsonSerializable
{
    protected $nucIssrId;

    public function setNucIssrId($nucIssrId)
    {
        $this->nucIssrId = $nucIssrId;
        return $this;
    }
    
    public function getNucIssrId()
    {
        return $this->nucIssrId;
    }

    public function jsonSerialize()
    {
        return [
            'nuc_issr_id' => $this->nucIssrId,
        ];
    }
}
<?php
/**
 * 银联云闪付主扫场景下acc_busi_fields域内容
 * TradePreorderUnionPayAccBusiFields
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V3\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V3\Model;

use Lakala\OpenAPISDK\V3\ObjectSerializer;

class TradePreorderUnionPayAccBusiFields extends TradeAccBusiFields implements \JsonSerializable
{
    protected $userId;
    protected $timeoutExpress;
    protected $acqAddnDataOrderInfo;
    protected $acqAddnDataGoodsInfo;
    protected $frontUrl;
    protected $frontFailUrl;
    protected $instalWill;
    protected $unQrcode;
    protected $userAuthCode;
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setTimeoutExpress($timeoutExpress)
    {
        $this->timeoutExpress = $timeoutExpress;
        return $this;
    }
    
    public function getTimeoutExpress()
    {
        return $this->timeoutExpress;
    }
    
    public function setAcqAddnDataOrderInfo($acqAddnDataOrderInfo)
    {
        $this->acqAddnDataOrderInfo = $acqAddnDataOrderInfo;
        return $this;
    }
    
    public function getAcqAddnDataOrderInfo()
    {
        return $this->acqAddnDataOrderInfo;
    }
    
    public function setAcqAddnDataGoodsInfo($acqAddnDataGoodsInfo)
    {
        $this->acqAddnDataGoodsInfo = $acqAddnDataGoodsInfo;
        return $this;
    }
    
    public function getAcqAddnDataGoodsInfo()
    {
        return $this->acqAddnDataGoodsInfo;
    }
    
    public function setFrontUrl($frontUrl)
    {
        $this->frontUrl = $frontUrl;
        return $this;
    }
    
    public function getFrontUrl()
    {
        return $this->frontUrl;
    }
    
    public function setFrontFailUrl($frontFailUrl)
    {
        $this->frontFailUrl = $frontFailUrl;
        return $this;
    }
    
    public function getFrontFailUrl()
    {
        return $this->frontFailUrl;
    }
    
    public function setInstalWill($instalWill)
    {
        $this->instalWill = $instalWill;
        return $this;
    }
    
    public function getInstalWill()
    {
        return $this->instalWill;
    }
    
    public function setUnQrcode($unQrcode)
    {
        $this->unQrcode = $unQrcode;
        return $this;
    }
    
    public function getUnQrcode()
    {
        return $this->unQrcode;
    }
    
    public function setUserAuthCode($userAuthCode)
    {
        $this->userAuthCode = $userAuthCode;
        return $this;
    }
    
    public function getUserAuthCode()
    {
        return $this->userAuthCode;
    }

    public function jsonSerialize()
    {
        $goods = array();
        if (isset($this->acqAddnDataGoodsInfo)) {
            foreach ($this->acqAddnDataGoodsInfo as $value) {
                $goods[] = $value->jsonSerialize();
            }
        }
        return [
            'user_id' => $this->userId,
            'timeout_express' => $this->timeoutExpress,
            'acq_addn_data_order_info' => ObjectSerializer::jsonencode($this->acqAddnDataOrderInfo),
            'acq_addn_data_goods_info' => $goods,
            'front_url' => $this->frontUrl,
            'front_fail_url' => $this->frontFailUrl,
            'instal_will' => $this->instalWill,
            'un_qrcode' => $this->unQrcode,
            'user_auth_code' => $this->userAuthCode,
        ];
    }
}
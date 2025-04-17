<?php
/**
 * 终端信息
 * V2TermExtInfo
 * PHP version 7.4
 *
 * @category Class
 * @package  Lakala\OpenAPISDK\V2\Model
 * @author   lucongyu
 * @link     https://o.lakala.com
 */

namespace Lakala\OpenAPISDK\V2\Model;

class V2TermExtInfo implements \JsonSerializable
{
    protected $termSN;
    protected $termBaseStation;
    protected $termLoc;
    protected $termIp;
    protected $termSerialNo;
    protected $termType;
    protected $termModel;
    protected $termManu;
    protected $appCode;
    protected $appVer;
    protected $termFP;
    
    public function setTermSN($termSN)
    {
        $this->termSN = $termSN;
        return $this;
    }
    
    public function getTermSN()
    {
        return $this->termSN;
    }
    
    public function setTermBaseStation($termBaseStation)
    {
        $this->termBaseStation = $termBaseStation;
        return $this;
    }
    
    public function getTermBaseStation()
    {
        return $this->termBaseStation;
    }
    
    public function setTermLoc($termLoc)
    {
        $this->termLoc = $termLoc;
        return $this;
    }
    
    public function getTermLoc()
    {
        return $this->termLoc;
    }
    
    public function setTermIp($termIp)
    {
        $this->termIp = $termIp;
        return $this;
    }
    
    public function getTermIp()
    {
        return $this->termIp;
    }
    
    public function setTermSerialNo($termSerialNo)
    {
        $this->termSerialNo = $termSerialNo;
        return $this;
    }
    
    public function getTermSerialNo()
    {
        return $this->termSerialNo;
    }
    
    public function setTermType($termType)
    {
        $this->termType = $termType;
        return $this;
    }
    
    public function getTermType()
    {
        return $this->termType;
    }
    
    public function setTermModel($termModel)
    {
        $this->termModel = $termModel;
        return $this;
    }
    
    public function getTermModel()
    {
        return $this->termModel;
    }
    
    public function setTermManu($termManu)
    {
        $this->termManu = $termManu;
        return $this;
    }
    
    public function getTermManu()
    {
        return $this->termManu;
    }
    
    public function setAppCode($appCode)
    {
        $this->appCode = $appCode;
        return $this;
    }
    
    public function getAppCode()
    {
        return $this->appCode;
    }
    
    public function setAppVer($appVer)
    {
        $this->appVer = $appVer;
        return $this;
    }
    
    public function getAppVer()
    {
        return $this->appVer;
    }
    
    public function setTermFP($termFP)
    {
        $this->termFP = $termFP;
        return $this;
    }
    
    public function getTermFP()
    {
        return $this->termFP;
    }

    public function jsonSerialize()
    {
        return [
            'termSN' => $this->termSN,
            'termBaseStation' => $this->termBaseStation,
            'termLoc' => $this->termLoc,
            'termIp' => $this->termIp,
            'termSerialNo' => $this->termSerialNo,
            'termType' => $this->termType,
            'termModel' => $this->termModel,
            'termManu' => $this->termManu,
            'appCode' => $this->appCode,
            'appVer' => $this->appVer,
            'termFP' => $this->termFP,
        ];
    }
}
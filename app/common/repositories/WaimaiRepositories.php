<?php

namespace app\common\repositories;
use app\common\repositories\BaseRepository;
use crmeb\services\MeituanService;

class WaimaiRepositories extends BaseRepository
{
    public $entId = '104984';
    public $accessKey = 'EI69RLOYPPMP-TK';
    public $secretKey = 'FOAd7WvvJb+lDSHSaAeUnQ==';
    // 测试环境地址
    public $url = 'https://waimai-openapi.apigw.test.meituan.com/api/sqt/open/login/h5/loginFree/redirection?test_open_swimlane=test-open';
    // 线上环境地址
    public $onlineUrl = 'https://bep-openapi.meituan.com/api/sqt/open/login/h5/loginFree/redirection';

    //美团外卖入口
    public function mt_waimai($params)
    {
        $meituanService = new MeituanService();
        $url = $this->url;
        $staffPhone = isset($params['mobile']) ? $params['mobile'] : ''; //员工手机号 1. 登录时, staffPhone/staffEmail/staffNum 三者必填一个, 与企业员工唯一识别对应
        $staffEmail = isset($params['staffEmail']) ? $params['staffEmail'] : ''; //员工邮箱
        $staffNum = isset($params['staffNum']) ? $params['staffNum'] : ''; //员工工号
        $externalOrgId = isset($params['externalOrgId']) ? $params['externalOrgId'] : ''; //部门唯一标识
        $orderId = isset($params['orderId']) ? $params['orderId'] : ''; //唯一订单号

        $ts = $meituanService->getMillisecond();
        $staffInfo = ['staffPhone' => $staffPhone];
        $nonce = $meituanService->randstr(32);

        $product_type = isset($params['product_type']) ? $params['product_type'] : 'mt_waimai'; // 跳转类型
        $longitude = isset($params['longitude']) ? $params['longitude'] : ''; //经度 116.480881
        $latitude = isset($params['latitude']) ? $params['latitude'] : ''; //纬度 39.989410
        $geotype = isset($params['geotype']) ? $params['geotype'] : 'wgs84'; //gcj02(火星坐标系)或者wgs84(国际坐标系)
        $address = isset($params['address']) ? $params['address'] : ''; //经纬度对应的中文地址北京市朝阳区阜通东大街6号
        $location = ['longitude' => $longitude, 'latitude' => $latitude, 'geotype' => $geotype, 'address' => $address];
        $bizParam = ['location' => $location];
        $bizParam = [];
        $data = ['productType' => $product_type, 'ts' => $ts, 'entId' => $this->entId, 'staffInfo' => $staffInfo, 'nonce' => $nonce,'sceneType'=>9];
        $content = $meituanService->aes_encrypt($data, $this->secretKey);
        $postData = ['accessKey' => $this->accessKey, 'content' => $content];
        $result = $meituanService->loginFree2Posts($url, $postData);
        return $result;
    }

}
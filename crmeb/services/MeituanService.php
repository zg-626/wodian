<?php

namespace crmeb\services;

class MeituanService
{
    public function aes_encrypt(array $data, $secretKey)
    {
        // echo json_encode($data,JSON_UNESCAPED_UNICODE);
        $data = openssl_encrypt(json_encode($data,JSON_UNESCAPED_UNICODE), 'AES-128-ECB', base64_decode($secretKey));
        //      var_dump($data);
        $data = str_replace('/', '_', $data);
        $data = str_replace('+', '-', $data);
        $data = str_replace('=', '', $data);
        //        dd($data);
        return $data;
    }

    public function aes_decrypt(string $str, $secretKey)
    {
        $str = str_replace('_', '/', $str);
        $str = str_replace('-', '+', $str);
        $data = openssl_decrypt($str, 'AES-128-ECB', base64_decode($secretKey));
        //        dd($data);
        // var_dump($data);
        return json_decode($data, TRUE);
    }

    public function loginFree2Post($url, $data)
    {
        // $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ));//重点
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }

        return $response;
    }

    public function getMillisecond() {
        $t = explode(' ', microtime());
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($s1) + floatval($s2)) * 1000);
    }
    public function randstr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    // MeituanService.php
    public function loginFree2Posts($url, $postData) {
        $ch = curl_init();

        // 基本配置
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,  // 关键！获取header信息
            CURLOPT_NOBODY => false, // 获取body（虽然302没有body）
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_FOLLOWLOCATION => false, // 禁止自动跳转
        ]);

        $rawResponse = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // 分割头和体（302响应通常没有body）
        $headers = substr($rawResponse, 0, $headerSize);

        // 解析头信息
        $parsedHeaders = [];
        foreach(explode("\r\n", $headers) as $line) {
            if (preg_match('/^(.*?):\s*(.*)/', $line, $matches)) {
                $parsedHeaders[trim($matches[1])] = trim($matches[2]);
            } elseif (preg_match('/^(HTTP\/[\d.]+)/', $line)) {
                $parsedHeaders['HTTP_STATUS'] = $line;
            }
        }

        return [
            'success' => curl_getinfo($ch, CURLINFO_HTTP_CODE) == 302,
            'headers' => $parsedHeaders,
            'location' => $parsedHeaders['Location'] ?? null,
            'trace_id' => $parsedHeaders['M-TraceId'] ?? null
        ];
    }


    //美团外卖入口
    public function mt_waimai($params)
    {
        $url = 'https://waimai-openapi.apigw.test.meituan.com/api/sqt/open/login/h5/loginFree/redirection?test_open_swimlane=test-open';
        $staffPhone = isset($params['mobile']) ? $params['mobile'] : ''; //员工手机号 1. 登录时, staffPhone/staffEmail/staffNum 三者必填一个, 与企业员工唯一识别对应
        $staffEmail = isset($params['staffEmail']) ? $params['staffEmail'] : ''; //员工邮箱
        $staffNum = isset($params['staffNum']) ? $params['staffNum'] : ''; //员工工号
        $externalOrgId = isset($params['externalOrgId']) ? $params['externalOrgId'] : ''; //部门唯一标识
        $orderId = isset($params['orderId']) ? $params['orderId'] : ''; //唯一订单号

        $ts = $this->getMillisecond();
        $staffInfo = ['staffPhone' => $staffPhone];
        $nonce = $this->randstr(32);

        $longitude = isset($params['longitude']) ? $params['longitude'] : ''; //经度 116.480881
        $latitude = isset($params['latitude']) ? $params['latitude'] : ''; //纬度 39.989410
        $geotype = isset($params['geotype']) ? $params['geotype'] : 'wgs84'; //gcj02(火星坐标系)或者wgs84(国际坐标系)
        $address = isset($params['address']) ? $params['address'] : ''; //经纬度对应的中文地址北京市朝阳区阜通东大街6号
        $location = ['longitude' => $longitude, 'latitude' => $latitude, 'geotype' => $geotype, 'address' => $address];
        $bizParam = ['location' => $location];
        $bizParam = [];
        $data = ['productType' => 'mt_waimai', 'ts' => $ts, 'entId' => '104984', 'staffInfo' => $staffInfo, 'nonce' => $nonce,];
        $content = $this->aes_encrypt($data, '+AN4Qre9BaJsmPQBSzEXGA==');
        $postData = ['accessKey' => 'EI69RLOYPPMP-TK', 'content' => $content];
        $result = $this->loginFree2Post($url, $postData);
        return $result;
    }
}
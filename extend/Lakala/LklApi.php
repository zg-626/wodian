<?php

namespace Lakala;

use Exception;
use GuzzleHttp\Client;
use Nette\Utils\Random;
use Lakala\OpenAPISDK\V2\V2Configuration;
use Lakala\OpenAPISDK\V2\Api\V2LakalaApi;
use Lakala\OpenAPISDK\V2\Model\V2ModelRequest;

require __DIR__ . '/vendor/autoload.php';

/**
 * @desc 内部使用的对接拉卡拉接口
 * @author ZhouTing
 * @date 2025-04-17 14:38
 */
class LklApi
{
    private static $errorMsg;

    const DEFAULT_ERROR_MSG = '操作失败,请稍候再试!';
    const DEBUG = true; //测试环境

    private static $config = [
        'org_code' => '1', //机构号
        'client_id' => 'testsit', //第三方client_id
        'client_secret' => 'EguwEckByf2I6u6z', //第三方client_secret
        'access_token_url' => self::DEBUG ? 'http://htkauth-sit.lakala.sh.in/oauth/token' : 'https://tkapi.lakala.com/auth/oauth/token', //请求获取token
        'merchant_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/merchant' : 'https://htkactvi.lakala.com/registration/merchant', //商户进件
        'organization_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration' : 'https://htkactvi.lakala.com/registration', //地区信息
        'bank_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/bank' : 'https://htkactvi.lakala.com/registration/bank',
        'user_no' => '20000101', //商户归属用户信息
    ];

    /**
     * @desc 电子合同申请(EC005)
     * @author ZhouTing
     * @param merchant_type 商户类型：0 小微商户，1 企业
     * @param cert_name 法人/经营者姓名
     * @param cert_no 法人/经营者证件号码
     * @param ec_mobile 签约手机号
     * @param mer_blis_name 营业执照名称/个人真实姓名  个体工商户或企业商户必传
     * @param mer_blis 营业执照号 个体工商户或企业商户必传
     * @param acct_type_code 企业/经营者结算卡性质 57 对公、58 对私
     * @param openning_bank_code 企业/经营者结算开户行号
     * @param openning_bank_name 企业/经营者结算开户行名称
     * @param acct_no 企业/经营者结算卡号
     * @param acct_name 企业/经营者结算卡名称
     * @param agent_tag 是否经办签约 0 不启用 1 启用
     * @param agent_name 经办人名称（要与证件号对应）agent_tag 为1时 必传
     * @param agent_cert_no 经办人证件号 agent_tag 为1时 必传
     * @param agent_file_path 经办授权委托书文件路径 agent_tag 为1时 必传
     * @param A1 签约商户的商户名称
     * @param B1 商户注册登记表的年
     * @param B2 商户注册登记表的月
     * @param B9 主营业务
     * @param B10 商户对外经营名称
     * @param B19 开户行
     * @param B20 结算账号
     * @param B24 法人姓名
     * @param B25 法人证件及号码
     * @param B26 法人手机号
     * @param B27 联系人姓名
     * @param B28 联系人邮箱
     * @param B29 联系人证件及号码
     * @param B30 联系人手机号
     * @param B33 终端布放地址 => 拉卡拉让填 联系人地址
     * @文档：https://o.lakala.com/#/home/document/detail?id=499
     * @date 2025-04-17 14:39
     */
    public static function lklEcApply($param)
    {
        //结算卡性质 对私 对接 拉卡拉卡BIN信息查询
        if ($param['acct_type_code'] == '58') {
            $carData = self::lklCardBin($param['acct_no']);
            $openning_bank_code = $carData['bankCode'];
            $openning_bank_name = $carData['bankName'];
        } else {
            $openning_bank_code = $param['openning_bank_code'];
            $openning_bank_name = $param['openning_bank_name'];
        }

        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::numeric(8),
            'orgId' => self::$config['org_code'],
            'ecTypeCode' => 'EC005',
            'certType' => 'RESIDENT_ID', //法人/经营者证件类型 RESIDENT_ID（身份证）
            'certName' => $param['cert_name'],
            'certNo' => $param['cert_no'],
            'mobile' => $param['ec_mobile'],
            'openningBankCode' => $openning_bank_code,
            'openningBankName' => $openning_bank_name,
            'acctTypeCode' => $param['acct_type_code'],
            'acctNo' => $param['acct_no'],
            'acctName' => $param['acct_name'],
            'agentTag' => $param['agent_tag'],
            'retUrl' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/api/lakala/lklEcApplyNotify'
        ];
        //个体工商户/企业(有营业执照)
        if (!empty($param['merchant_type'])) {
            $sepParam['businessLicenseNo'] = $param['mer_blis'];
            $sepParam['businessLicenseName'] = $param['mer_blis_name'];
        }

        //是否经办签约
        if ($sepParam['agentTag'] == 1) {
            $agent_image = self::lklUploadFile('FR_ID_CARD_FRONT', imageUrl($param['agent_file_path']));
            if (!$agent_image) return self::setErrorInfo(self::getErrorInfo());

            $sepParam['agentName'] = $param['agent_name'];
            $sepParam['agentCertType'] = 'RESIDENT_ID'; //经办人证件类型 RESIDENT_ID（身份证）
            $sepParam['agentCertNo'] = $param['agent_cert_no'];
            $sepParam['agentFileName'] = '电子签约授权委托书';
            $sepParam['agentFilePath'] = $agent_image;
        }

        $ecContentParameters = [
            'A1' => $param['A1'],
            'A2' => '是',
            'A19' => '0.3', //扫码支付业务：技术服务费费率
            'A58' => '1', //结算模式：商户交易资金结算模式选择 1主动结算模式 2被动结算模式
            'A60' => '是', //退货业务：不开通
            'A67' => date('Y'), //甲方签约的年
            'A68' => date('m'), //甲方签约的月
            'A69' => date('d'), //甲方签约的日
            'A70' => date('Y'), //乙方签约的年
            'A71' => date('m'), //乙方签约的月
            'A72' => date('d'), //乙方签约的日
            'B1' => $param['B1'],
            'B2' => $param['B2'],
            'B3' => '是',
            'B8' => $param['mer_blis_name'], //商户的工商注册名称
            'B9' => $param['B9'],
            'B10' => $param['B10'],
            'B14' => $param['mer_blis'], //营业执照注册号
            'B19' => $param['B19'],
            'B20' => $param['B20'],
            'B24' => $param['B24'],
            'B25' => $param['B25'],
            'B26' => $param['B26'],
            'B27' => $param['B27'],
            'B28' => $param['B28'],
            'B29' => $param['B29'],
            'B30' => $param['B30'],
            'B31' => $param['B10'], //分店营业名称 =>拉卡拉方指定内容 商户对外经营名称
            'B32' => $param['B27'], //网点联系人姓名 => 拉卡拉方指定内容 联系人姓名
            'B33' => $param['B33'], //终端布放地址 => 拉卡拉指定内容 联系人地址
            'B34' => $param['B30'], //网点联系人手机 => 拉卡拉指定内容 联系人手机号
            'D1' => date('Y-m-d'), //安心签服务授权委托书：签章日期
            'E1' => '重庆友伴同行网络科技有限公司', //结算授权委托书：授权公司名称  拉卡拉指定内容  授权公司名称
            'E2' => '商家入驻合作协议',
            'E3' => '3', //结算授权委托书：清分结算方式选择
            'E4' => '本商户',
            'E13' => '重庆友伴同行网络科技有限公司',
            'E14' => '80', //结算授权委托书：商户分得最低分账比例 => 拉卡拉方指定内容
            'E15' => '是',
            'E19' => date('Y-m-d'), //结算授权委托书：签章日期
        ];

        //对公结算
        if ($sepParam['acctTypeCode'] == '57') {
            $ecContentParameters['B16'] = '是';
        } else {
            //对私结算
            $ecContentParameters['B17'] = '是';
            $ecContentParameters['B18'] = $param['acct_name'];
        }
        $sepParam['ecContentParameters'] = json_encode($ecContentParameters);

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同申请参数: ' . json_encode($sepParam), 'lkl');
        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ec/apply', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同申请请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                //resultUrl 在线签约、ecApplyId 电子合同申请受理号
                return $resdata['respData'];
            } else {
                return self::setErrorInfo('电子合同申请失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同申请请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 商户进件第一步 获取access_token
     * @author ZhouTing
     * @date 2025-04-18 09:11
     */
    public static function lklAccessToken()
    {
        $client = new Client();
        try {
            $response = $client->post(self::$config['access_token_url'], [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => self::$config['client_id'],
                    'client_secret' => self::$config['client_secret']
                ]
            ]);

            $rawBody = (string) $response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取access_token失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 商户进件第二步 商户进件
     * @author ZhouTing
     * @param email 商户邮箱
     * @param mer_reg_name 商户注册名称 - 与营业执照一致 20字内
     * @param merchant_type 商户类型：0 小微商户，1 企业
     * @param mer_name 商户名称
     * @param mer_addr 商户详细地址
     * @param province_code 省代码
     * @param city_code 市代码
     * @param county_code 区县代码
     * @param mer_blis 营业执照号 企业必传
     * @param license_dt_start 营业执照开始时间 企业必传 yyyy-MM-dd
     * @param license_dt_end 营业执照过期时间 企业必传 yyyy-MM-dd
     * @param latitude 进件所在地址经度
     * @param longtude 进件所在地址纬度
     * @param B9 主营业务
     * @param is_legal_person 是否法人进件 0 否 1 是
     * @param lar_name 法人姓名
     * @param lar_id_card 法人证件号码
     * @param lar_id_card_start 法人证件开始日期 yyyy-MM-dd
     * @param lar_id_card_end 法人证件过期时间 yyyy-MM-dd 长期传：9999-12-31
     * @param B30 商户联系人手机号码
     * @param B27 商户联系人姓名
     * @date 2025-04-18 10:08
     */
    public static function lklMerchantApply($param)
    {
        $sepParam = [
            'userNo' => self::$config['user_no'],
            'email' => $param['email'],
            'busiCode' => 'WECHAT_PAY', //业务类型 专业化扫码
            'merRegName' => $param['mer_reg_name'],
            'merType' => empty($param['merchant_type']) ? 'TP_PERSONAL' : 'TP_MERCHANT',
            'merName' => $param['mer_name'],
            'merAddr' => $param['mer_addr'],
            'provinceCode' => $param['province_code'],
            'cityCode' => $param['city_code'],
            'countyCode' => $param['county_code'],
            'latitude' => $param['latitude'],
            'longtude' => $param['longtude'],
            'source' => 'APP',
            'businessContent' => $param['B9'],
            'isLegalPerson' => $param['is_legal_person'],
            'contactMobile' => $param['B30'],
            'contactName' => $param['B27']
        ];

        //个体工商户/企业
        if ($param['merchant_type'] == 1) {
            $sepParam['licenseNo'] = $param['mer_blis'];
            $sepParam['licenseDtStart'] = $param['license_dt_start'];
            $sepParam['licenseDtEnd'] = $param['license_dt_end'];
        }

        //法人进件
        if ($param['is_legal_person']) {
            $sepParam['larName'] = $param['lar_name'];
            $sepParam['larIdType'] = '01';
            $sepParam['larIdCard'] = $param['lar_id_card'];
            $sepParam['larIdCardStart'] = $param['lar_id_card_start'];
            $sepParam['larIdCardEnd'] = $param['lar_id_card_end'];
        }
    }

    /**
     * @desc 拉卡拉电子合同下载
     * @author ZhouTing
     * @param lkl_ec_apply_id 电子合同申请受理号
     * @date 2025-04-17 15:08
     */
    public static function lklEcDownload($param)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::numeric(8),
            'orgId' => self::$config['org_code'],
            'ecApplyId' => $param['lkl_ec_apply_id'],
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求参数: ' . json_encode($sepParam), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ec/download', $request);
            if (!empty($response)) {
                $res = $response->getOriginalText();
                // record_log('Time: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求结果: ' . $res, 'lkl');
                $resdata = json_decode($res, true);
                if (!empty($resdata['retCode']) && $resdata['retCode'] == '000000') {
                    $outputFilePath = self::decodeFromUrlSafeStringToFile($resdata['respData']['ecFile'], $param['ecApplyId'] . '.pdf');
                    echo "<pre>";
                    print_r($outputFilePath);
                    die();
                } else {
                    return self::setErrorInfo('lkl' . $resdata['retMsg']);
                }
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 地区信息
     * @author ZhouTing
     * @param parent_code 编码
     * @date 2025-04-18 13:53
     */
    public static function lklOrganization($param)
    {
        $parent_code = empty($param['parent_code']) ? 1 : $param['parent_code'];
        $client = new Client();
        try {
            $response = $client->get(self::$config['organization_url'] . '/organization/' . $parent_code);

            $rawBody = (string) $response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取access_token失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 银行列表查询
     * @author ZhouTing
     * @param area_code 地区编码
     * @param bank_name 银行名称
     * @date 2025-04-18 16:59
     */
    public static function lklBankInfo($param)
    {
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 银行列表参数: ' . json_encode($param), 'lkl');

        $client = new Client();

        try {
            // 发送 GET 请求
            $response = $client->get(self::$config['bank_url'], [
                'query' => [
                    'areaCode' => $param['area_code'],
                    'bankName' => isset($param['bank_name']) ? $param['bank_name'] : ''
                ]
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true); // 解析 JSON 响应

            return $result;
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取银行列表失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 卡BIN信息查询
     * @author ZhouTing
     * @param cardNo 银行卡号
     * @doc：https://o.lakala.com/#/home/document/detail?id=179
     * @date 2025-04-17 18:26
     */
    public static function lklCardBin($cardNo)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::numeric(8),
            'orgCode' => self::$config['org_code'],
            'cardNo' => $cardNo
        ];

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/cardBin', $request);
            $res = $response->getOriginalText();

            record_log('Time: ' . date('Y-m-d H:i:s') . ', 卡BIN查询结果: ' . $res, 'lkl');
            $res = json_decode($res, true);
            if ($res['retCode'] == '000000') {
                return $res['respData'];
            } else {
                return self::setErrorInfo($res['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 卡BIN请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo($e->getMessage());
        }
    }

    /**
     * @desc 电子合同转码下载
     * @author ZhouTing
     * @date 2025-04-17 14:59
     */
    public static function decodeFromUrlSafeStringToFile($input, $outputFilePath)
    {
        //将 '-' 替换回 '+'，将 '_' 替换回 '/'
        $input = str_replace(['-', '_'], ['+', '/'], $input);

        //计算需要添加的填充字符数量
        $padding = strlen($input) % 4;
        if ($padding > 0) {
            $input .= str_repeat('=', 4 - $padding);
        }

        $binaryData = base64_decode($input);

        //将二进制数据保存为文件
        file_put_contents('ec' . DIRECTORY_SEPARATOR . $outputFilePath, $binaryData);
        return $outputFilePath;
    }

    /**
     * @desc 上传附件
     * @author ZhouTing
     * @param attType 附件类型
     * @param url 附件
     * @doc：https://o.lakala.com/#/home/document/detail?id=90
     * @date 2025-03-04 10:38
     */
    public static function lklUploadFile($attType, $url)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::numeric(8),
            'orgCode' => self::$config['org_code'],
            'attType' => $attType,
            'attExtName' => 'pdf',
            'attContext' => base64_encode(file_get_contents($url))
        ];
        $log = $sepParam;
        $log['attContext'] = $url;
        record_log('Time: ' . date('Y-m-d H:i:s') . ', 上传附件请求参数: ' . json_encode($log), 'lkl');
        unset($log);

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/uploadFile', $request);
            $res = $response->getOriginalText();
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 上传附件请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData']['attFileId'];
            } else {
                return self::setErrorInfo('附件上传失败，' . $res['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 上传附件异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo($e->getMessage());
        }
    }

    /**
     * 设置错误信息
     * @param string $errorMsg
     * @return bool
     */
    protected static function setErrorInfo($errorMsg = self::DEFAULT_ERROR_MSG)
    {
        self::$errorMsg = $errorMsg;
        return false;
    }

    /**
     * 获取错误信息
     * @param string $defaultMsg
     * @return string
     */
    public static function getErrorInfo($defaultMsg = self::DEFAULT_ERROR_MSG)
    {
        return !empty(self::$errorMsg) ? self::$errorMsg : $defaultMsg;
    }
}

<?php

namespace Lakala;

use Exception;
use GuzzleHttp\Client;
use Nette\Utils\Random;
use Lakala\OpenAPISDK\V2\V2Configuration;
use Lakala\OpenAPISDK\V2\Api\V2LakalaApi;
use Lakala\OpenAPISDK\V2\Model\V2ModelRequest;
use Lakala\OpenAPISDK\V3\Api\LakalaApi;
use Lakala\OpenAPISDK\V3\Configuration;
use Lakala\OpenAPISDK\V3\Model\ModelRequest;

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
        'org_code' => '982406', //机构号
        'client_id' => 'testsit', //第三方client_id
        'client_secret' => 'EguwEckByf2I6u6z', //第三方client_secret
        'access_token_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkauth/oauth/token' : 'https://tkapi.lakala.com/auth/oauth/token', //请求获取token
        'merchant_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/merchant' : 'https://htkactvi.lakala.com/registration/merchant', //商户进件
        'organization_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration' : 'https://htkactvi.lakala.com/registration', //地区信息
        'bank_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/bank' : 'https://htkactvi.lakala.com/registration/bank', //银行地区信息
        'htk_file_upload_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/file/upload' : 'https://htkactvi.lakala.com/registration/file/upload', //拓客 文件上传
        'customer_cate_url' => self::DEBUG ? 'https://test.wsmsd.cn/sit/htkregistration/customer/category' : 'https://htkactvi.lakala.com/registration/customer/category', //商户类别(进件获取mcc使用)
        'user_no' => '20000101', //商户归属用户信息
        'activity_id' => '4', //归属活动信息
        'request_ip' => '39.100.91.239', //请求方IP  地址位置信息，风控要求必送
        'app_id' => 'wxda2922aa5121cc98'
    ];

    /**
     * @desc 电子合同申请(EC005)
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
     * @author ZhouTing
     */
    public static function lklEcApply($param)
    {
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同原始参数: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');

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
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
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
            'retUrl' => request()->domain() . '/api/lakala/lklEcApplyNotify'
        ];
        //个体工商户/企业(有营业执照)
        if (!empty($param['merchant_type'])) {
            $sepParam['businessLicenseNo'] = $param['mer_blis'];
            $sepParam['businessLicenseName'] = $param['mer_blis_name'];
        }

        //是否经办签约
        if ($sepParam['agentTag'] == 1) {
            $agent_image = self::lklUploadFile('FR_ID_CARD_FRONT', $param['agent_file_path']);
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

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同申请参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');
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
     * @desc 电子合同查询
     * @param lkl_ec_apply_id 电子合同申请受理号
     * @doc：https://o.lakala.com/#/home/document/detail?id=293
     * @date 2025-04-23 10:46
     * @author ZhouTing
     */
    public static function lklEcQStatus($param)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgId' => self::$config['org_code'],
            'ecApplyId' => $param['lkl_ec_apply_id']
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同查询参数: ' . json_encode($sepParam), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ec/qStatus', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同查询请求结果: ' . $res, 'lkl');

            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData'];
            } else {
                return self::setErrorInfo('电子合同查询失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同查询异常: ' . $e->getMessage(), 'lkl');
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
        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->post(self::$config['access_token_url'], [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => self::$config['client_id'],
                    'client_secret' => self::$config['client_secret']
                ]
            ]);

            $rawBody = (string)$response->getBody();
            //{"access_token":"98e63557-e428-4419-84ef-e4408a0d72f6","token_type":"bearer","expires_in":1131297,"scope":"all"}
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取access_token失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 商户进件第二步 商户进件
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
     * @param branch_bank_no 结算账户开户行号 银行列表(lklBankInfo)获取
     * @param branch_bank_name 结算账户开户行名称 银行列表(lklBankInfo)获取
     * @param clear_no 结算账户清算行号 银行列表(lklBankInfo)获取
     * @param settle_province_code 结算信息省份代码 银行地区(lklBankOrganization)获取
     * @param settle_province_name 结算信息省份名称 银行地区(lklBankOrganization)获取
     * @param settle_city_code 结算信息城市代码 银行地区(lklBankOrganization)获取
     * @param settle_city_name 结算信息城市名称 银行地区(lklBankOrganization)获取
     * @param acct_no 结算人银行卡号
     * @param acct_name 结算人账户名称
     * @param acct_type_code 结算账户类型 57对公，58对私
     * @param acct_id_card 结算人证件号码
     * @param z_idcard_img 身份证正面
     * @param f_idcard_img 身份证反面
     * @param license_pic_img 营业执照照片 企业必传
     * @param acct_img 银行卡照
     * @param agree_ment_img 入网协议
     * @param openining_permit_img 开户许可证（对公必传）
     * @param checkstand_img 收银台照片
     * @param shop_outside_img 门头照片
     * @param shop_inside_img 店铺内部照片
     * @param z_settle_img 结算人身份证正面照
     * @param f_settle_img 结算人身份证反面照
     * @param legal_auth_img  法人授权函(非法人进件时，必传)
     * @param lkl_ec_no 电子合同编号
     * @date 2025-04-18 10:08
     * @author ZhouTing
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
            'contactName' => $param['B27'],
            'openningBankCode' => $param['branch_bank_no'],
            'openningBankName' => $param['branch_bank_name'],
            'clearingBankCode' => $param['clear_no'],
            'settleProvinceCode' => $param['settle_province_code'],
            'settleProvinceName' => $param['settle_province_name'],
            'settleCityCode' => $param['settle_city_code'],
            'settleCityName' => $param['settle_city_name'],
            'accountNo' => $param['acct_no'],
            'accountName' => $param['acct_name'],
            'accountType' => $param['acct_type_code'],
            'accountIdCard' => $param['acct_id_card'],
            'bizContent' => [
                'termNum' => '1',
                'activityId' => self::$config['activity_id'],
                'mcc' => '',
                'fees' => [
                    [
                        'feeCode' => 'WECHAT',
                        'feeValue' => 0.3
                    ],
                    [
                        'feeCode' => 'ALIPAY',
                        'feeValue' => 0.3
                    ],
                    [
                        'feeCode' => 'CREDIT_CARD',
                        'feeValue' => 0.6
                    ],
                    [
                        'feeCode' => 'DEBIT_CARD',
                        'feeValue' => 0.5,
                        'topFee' => 20
                    ]
                ]
            ],
            'settleType' => 'D1', //结算类型 D0秒到， D1次日结算
            'settlementType' => 'AUTOMATIC', //结算方式 MANUAL:手动结算(结算至拉卡拉APP钱包),AUTOMATIC:自动结算到银行卡,REGULAR:定时结算（仅企业商户支持）
            'contractNo' => $param['lkl_ec_no'],
        ];

        //附件信息
        $imageTypes = [
            ['file' => $param['z_idcard_img'], 'type' => 'ID_CARD_FRONT'],
            ['file' => $param['f_idcard_img'], 'type' => 'ID_CARD_BEHIND'],
            ['file' => $param['acct_img'], 'type' => 'BANK_CARD'],
            ['file' => $param['agree_ment_img'], 'type' => 'AGREE_MENT'],
            ['file' => $param['checkstand_img'], 'type' => 'CHECKSTAND_IMG'],
            ['file' => $param['shop_outside_img'], 'type' => 'SHOP_OUTSIDE_IMG'],
            ['file' => $param['shop_inside_img'], 'type' => 'SHOP_INSIDE_IMG'],
            ['file' => $param['z_settle_img'], 'type' => 'SETTLE_ID_CARD_BEHIND'],
            ['file' => $param['f_settle_img'], 'type' => 'SETTLE_ID_CARD_FRONT'],
        ];
        $attchments = [];
        foreach ($imageTypes as $imageType) {
            $sImg = self::lklHtkFileUpload($imageType);
            if (!$sImg) {
                return self::setErrorInfo(self::getErrorInfo());
            }
            $attchments[] = [
                'id' => $sImg['url'],
                'type' => $imageType['type']
            ];
        }

        //个体工商户/企业
        if ($param['merchant_type'] == 1) {
            $sepParam['licenseNo'] = $param['mer_blis'];
            $sepParam['licenseDtStart'] = $param['license_dt_start'];
            $sepParam['licenseDtEnd'] = $param['license_dt_end'];
            //营业执照照片
            $sLicenseImg = self::lklHtkFileUpload(['file' => $param['license_pic_img'], 'type' => 'BUSINESS_LICENCE']);
            if (!$sLicenseImg) return self::setErrorInfo(self::getErrorInfo());
            $attchments = array_merge($attchments, [['id' => $sLicenseImg['url'], 'type' => 'BUSINESS_LICENCE']]);
        }

        //法人进件
        if ($param['is_legal_person']) {
            $sepParam['larName'] = $param['lar_name'];
            $sepParam['larIdType'] = '01';
            $sepParam['larIdCard'] = $param['lar_id_card'];
            $sepParam['larIdCardStart'] = $param['lar_id_card_start'];
            $sepParam['larIdCardEnd'] = $param['lar_id_card_end'];
        } else {
            //需上传法人授权函
            $sLegalAuthImg = self::lklHtkFileUpload(['file' => $param['legal_auth_img'], 'type' => 'LETTER_OF_AUTHORIZATION']);
            if (!$sLegalAuthImg) return self::setErrorInfo(self::getErrorInfo());
            $attchments = array_merge($attchments, [['id' => $sLegalAuthImg['url'], 'type' => 'LETTER_OF_AUTHORIZATION']]);
        }

        //对公
        if ($param['acct_type_code'] == '57') {
            //开户许可证
            $oPImg = self::lklHtkFileUpload(['file' => $param['openining_permit_img'], 'type' => 'OPENING_PERMIT']);
            if (!$oPImg) return self::setErrorInfo(self::getErrorInfo());
            $attchments = array_merge($attchments, [['id' => $oPImg['url'], 'type' => 'OPENING_PERMIT']]);
        }
        $sepParam['attachments'] = $attchments;

        // $multipartData = [];
        // foreach ($sepParam as $key => $value) {
        //     if (is_array($value)) {
        //         $multipartData[] = [
        //             'name' => $key,
        //             'contents' => json_encode($value, JSON_UNESCAPED_UNICODE)
        //         ];
        //     } else {
        //         $multipartData[] = [
        //             'name' => $key,
        //             'contents' => (string)$value
        //         ];
        //     }
        // }

        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $requestData = [
            'json' => $sepParam,
            'headers' => [
                'Authorization' => 'bearer ' . $token['access_token'],
                'Content-Type' => 'application/json',
            ],
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客商户进件请求参数: ' . json_encode($requestData, JSON_UNESCAPED_UNICODE), 'lkl');

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->post(self::$config['merchant_url'], $requestData);

            $rawBody = (string)$response->getBody();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客商户进件结果: ' . $rawBody, 'lkl');

            return json_decode($rawBody, true);
        } catch (Exception $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客商户进件异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('拉卡拉商户进件失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉分账接收方创建申请（开放平台）
     * @param receiver_name 分账接收方名称
     * @param receiver_mobile 分账接收方联系手机号
     * @param acct_no 收款账户卡号
     * @param acct_name 收款账户名称
     * @param acct_type_code 收款账户账户类型 57 对公、58 对私
     * @param acct_id_card 收款账户证件号
     * @param lkl_acct_open_bank_code 收款账户开户行号(对公必传)
     * @param lkl_acct_open_bank_name 收款账户开户名称(对公必传)
     * @param lkl_acct_clear_bank_code 收款账户清算行行号(对公必传)
     * @param mer_blis 营业执照号(对公必传)
     * @param mer_blis_name 营业执照名称(对公必传)
     * @param lar_name 法人姓名(对公必传)
     * @param lar_id_card 法人证件号码(对公必传)
     * @param z_legal_img 法人身份证正面(对公必传)
     * @param f_legal_img 法人身份证反面(对公必传)
     * @param license_pic_img 营业执照照片(对公必传)
     * @param z_idcard_image 个人身份证正面(对私必传)
     * @param f_idcard_image 个人身份证反面(对私必传)
     * @doc：https://o.lakala.com/#/home/document/detail?id=382
     * @date 2025-04-21 19:55
     * @author ZhouTing
     */
    public static function lklApplyLedgerReceiver($param)
    {
        if ($param['acctTypeCode'] == '57') {
            $bankCode = $param['lkl_acct_open_bank_code'];
            $acctOpenBankName = $param['lkl_acct_open_bank_name'];
            $acctClearBankCode = $param['lkl_acct_clear_bank_code'];
        } else {
            //卡BIN信息查询
            $carData = self::lklCardBin($param['acctNo']);
            if (!$carData) {
                return self::setErrorInfo(self::getErrorInfo());
            }
            $bankCode = $carData['bankCode'];
            $acctOpenBankName = $carData['bankName'];
            $acctClearBankCode = $carData['clearingBankCode'];
        }

        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'receiverName' => $param['receiver_name'],
            'contactMobile' => $param['receiver_mobile'],
            'acctNo' => $param['acct_no'],
            'acctName' => $param['acct_name'],
            'acctTypeCode' => $param['acct_type_code'],
            'acctCertificateType' => '17', //收款账户证件类型 17 身份证
            'acctCertificateNo' => $param['acct_id_card'],
            'acctOpenBankCode' => $bankCode,
            'acctOpenBankName' => $acctOpenBankName,
            'acctClearBankCode' => $acctClearBankCode,
            'settleType' => '03', //提款类型 01：主动提款 03：交易自动结算（客户规定）
        ];
        //对公
        if ($param['acctTypeCode'] == '57') {
            $sepParam['licenseNo'] = $param['mer_blis'];
            $sepParam['licenseName'] = $param['mer_blis_name'];
            $sepParam['legalPersonName'] = $param['lar_name'];
            $sepParam['legalPersonCertificateType'] = 17; //法人证件类型 17 身份证
            $sepParam['legalPersonCertificateNo'] = $param['lar_id_card'];
            //对公：法人身份证正面，银行卡，营业执照正面
            $fZImg = self::lklUploadFile('FR_ID_CARD_FRONT', $param['z_legal_img']);
            if (!$fZImg) return self::setErrorInfo(self::getErrorInfo());
            $fFImg = self::lklUploadFile('FR_ID_CARD_BEHIND', $param['f_legal_img']);
            if (!$fFImg) return self::setErrorInfo(self::getErrorInfo());
            $licenseZImg = self::lklUploadFile('BUSINESS_LICENCE', $param['license_pic_img']);
            if (!$licenseZImg) return self::setErrorInfo(self::getErrorInfo());
            $sepParam['attachList'] = [
                [
                    'attachName' => '法人身份证正面',
                    'attachStorePath' => $fZImg,
                    'attachType' => 'FR_ID_CARD_FRONT'
                ],
                [
                    'attachName' => '法人身份证反面',
                    'attachStorePath' => $fFImg,
                    'attachType' => 'FR_ID_CARD_BEHIND'
                ],
                [
                    'attachName' => '营业执照',
                    'attachStorePath' => $licenseZImg,
                    'attachType' => 'BUSINESS_LICENCE'
                ]
            ];
        } else {
            //对私
            $zImg = self::lklUploadFile('ID_CARD_FRONT', $param['z_idcard_image']);
            if (!$zImg) return self::setErrorInfo(self::getErrorInfo());
            $fImg = self::lklUploadFile('ID_CARD_BEHIND', $param['f_idcard_image']);
            if (!$fImg) return self::setErrorInfo(self::getErrorInfo());
            $sepParam['attachList'] = [
                [
                    'attachName' => '身份证正面',
                    'attachStorePath' => $zImg,
                    'attachType' => 'ID_CARD_FRONT'
                ],
                [
                    'attachName' => '身份证反面',
                    'attachStorePath' => $fImg,
                    'attachType' => 'ID_CARD_BEHIND'
                ]
            ];
        }

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账接收方创建申请参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ledger/applyLedgerReceiver', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账接收方创建请求结果: ' . $res, 'lkl');

            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData']['receiverNo'];
            } elseif ($resdata['retCode'] == '-10010') {
                return self::setErrorInfo('分账接收方身份信息错误'); //拉卡拉返回的是：入参校验失败
            } else {
                return self::setErrorInfo('分账接收方创建申请失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账接收方创建异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉分账关系绑定申请(开放平台) 商户与平台
     * @param lkl_mer_cup_no 拉卡拉商户编号 分账商户银联商户号(店铺)
     * @param lkl_receiver_no 分账接收方编号
     * @param entrust_file_path 合作协议
     * @date 2025-04-22 10:20
     * @author ZhouTing
     */
    public static function lklApplyBind($param)
    {
        //拉卡拉分账关系绑定申请，合作协议附件
        $entrust_image = self::lklUploadFile('XY', $param['entrust_file_path']);
        if (!$entrust_image) {
            return self::setErrorInfo(self::getErrorInfo());
        }

        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'merCupNo' => $param['lkl_mer_cup_no'],
            'receiverNo' => $param['lkl_receiver_no'],
            'entrustFileName' => '合作协议',
            'entrustFilePath' => $entrust_image,
            'retUrl' => request()->domain() . '/api/lakala/lklApplyBindNotify'
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账关系绑定申请参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ledger/applyBind', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账关系绑定申请请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData'];
            } else {
                return self::setErrorInfo('分账关系绑定申请失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 分账关系绑定申请异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 商户分账业务开通申请
     * @param lkl_mer_cup_no 银联商户号 = 拉卡拉商户编号
     * @param mobile 联系手机号
     * @param split_entrust_file_path 分账结算委托书文件
     * @param lkl_ec_no 电子合同编号
     * @doc：https://o.lakala.com/#/home/document/detail?id=379
     * @date 2025-04-22 11:39
     * @author ZhouTing
     */
    public static function lklApplyLedgerMer($param)
    {
        //EC003无需上传分账结算授权委托书,只传合同编号即可，EC005必须上传结算授权委托书,合同编号非必传
        $split_image = self::lklUploadFile('OTHERS', $param['split_entrust_file_path']);
        if (!$split_image) return self::setErrorInfo(self::getErrorInfo());

        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'merCupNo' => $param['lkl_mer_cup_no'],
            'contactMobile' => $param['mobile'],
            'splitLowestRatio' => 80, //最低分账比例
            'splitEntrustFileName' => '结算授权委托书',
            'splitEntrustFilePath' => $split_image,
            'eleContractNo' => $param['lkl_ec_no'],
            'retUrl' => request()->domain() . '/api/lakala/lklApplyLedgerMerNotify'
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户分账业务开通请求参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ledger/applyLedgerMer', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户分账业务开通申请请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return true;
            } else {
                return self::setErrorInfo('商户分账业务开通申请失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 商户分账业务开通申请请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉聚合主扫 支付
     * @param lkl_mer_cup_no 银联商户号 = 拉卡拉商户编号
     * @param lkl_mer_term_no 商户终端号
     * @param total_amount 实付金额(元)
     * @param openid 支付人openid
     * @param goods_id 商品编码
     * @param order_no 订单号
     * @doc：https://o.lakala.com/#/home/document/detail?id=110
     * @date 2025-04-22 14:04
     * @author ZhouTing
     */
    public static function lklPreorder($param)
    {
        $sepParam = [
            'merchant_no' => '82239707230067X', //TODO： $param['lkl_mer_cup_no']
            'term_no' => 'J7567761', //TODO： $param['lkl_mer_term_no']
            'out_trade_no' => $param['order_no'],
            'account_type' => 'WECHAT', //钱包类型 微信：WECHAT 支付宝：ALIPAY 银联：UQRCODEPAY 翼支付: BESTPAY 苏宁易付宝: SUNING 拉卡拉支付账户：LKLACC 网联小钱包：NUCSPAY 京东钱包：JD
            'trans_type' => '71', //接入方式 41:NATIVE（（ALIPAY，云闪付支持，京东白条分期），51:JSAPI（微信公众号支付，支付宝服务窗支付，银联JS支付，翼支付JS支付、拉卡拉钱包支付），71:微信小程序支付，61:APP支付（微信APP支付）
            'total_amount' => bcmul($param['total_amount'], 100, 0), //金额 单位：分
            'location_info' => [
                'request_ip' => self::$config['request_ip'], //请求方的IP地址
            ], //地址位置信息
            'subject' => '支付', //标题
            'notify_url' => request()->domain() . '/api/lakala/lklPayNotify',
            'settle_type' => '1', //结算类型，0或者空常规结算方式，如需接拉卡拉分账需传1
            'remark' => $param['remark'], //商户定义，原样回传
            'acc_busi_fields' => [
                'timeout_express' => '15', //拉卡拉方预下单的订单有效时间(分钟)=>微信后台并不会依据此失效时间发起关单
                'sub_appid' => self::$config['app_id'],
                'user_id' => $param['openid'],
                'detail' => [
                    'goods_detail' => [
                        [
                            'goods_id' => $param['goods_id'],
                            'quantity' => 1, //购买数量
                            'price' => floatval(bcmul($param['total_amount'], 100, 2))
                        ]
                    ]
                ]
            ],
            'complete_notify_url' => request()->domain() . '/api/lakala/lklSendcompleteNotify' //发货确认通知地址 必须用户确认收货后方可进行订单分账
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 聚合主扫(微信端)请求参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new Configuration();
        $api = new LakalaApi($config);
        $request = new ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v3/labs/trans/preorder', $request);
            if (!empty($response)) {
                $res = $response->getOriginalText();
                record_log('时间: ' . date('Y-m-d H:i:s') . ', 聚合主扫(微信端)请求结果: ' . $res, 'lkl');
                $resdata = json_decode($res, true);
                if (!empty($resdata['code']) && $resdata['code'] == 'BBS00000') {
                    $acc = $resdata['resp_data']['acc_resp_fields'];
                    $acc['nonceStr'] = $acc['nonce_str'];
                    $acc['paySign'] = $acc['pay_sign'];
                    $acc['signType'] = $acc['sign_type'];
                    $acc['appId'] = $acc['app_id'];
                    $acc['timeStamp'] = $acc['time_stamp'];
                    return $acc;
                } else {
                    return self::setErrorInfo('lkl' . $resdata['msg']);
                }
            }
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 聚合主扫(微信端)请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 订单分账 第一步 可分账金额查询(订单可分账金额为：实付金额 - 拉卡拉所收手续费)
     * @param lkl_mer_cup_no 拉卡拉银联商户号
     * @param lkl_log_no 对账单流水号
     * @param lkl_log_date 交易日期 yyyyMMdd
     * @doc：https://o.lakala.com/#/home/document/detail?id=394
     * @date 2025-04-23 15:51
     * @author ZhouTing
     */
    public static function lklQueryAmt($param)
    {
        $sepParam = [
            'merchant_no' => $param['lkl_mer_cup_no'],
            'log_no' => $param['lkl_log_no'],
            'log_date' => $param['lkl_log_date'],
        ];

        record_log('Time: ' . date('Y-m-d H:i:s') . ', 可分账金额查询请求参数: ' . json_encode($sepParam), 'lkl');

        $config = new Configuration();
        $api = new LakalaApi($config);
        $request = new ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v3/sacs/queryAmt', $request);
            $res = $response->getOriginalText();
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 可分账金额查询请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['code'] == 'SACS0000') {
                return $resdata['resp_data'];
            } else {
                return self::setErrorInfo($resdata['msg']);
            }
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 可分账金额查询异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 订单分账 第二步 发送指令 订单分账
     * @param lkl_mer_cup_no 拉卡拉银联商户号
     * @param lkl_log_no 对账单流水号
     * @param lkl_log_date 交易日期
     * @param can_separate_amt 可分账金额（明细之和） 单位：分
     * @param recv_datas 分账接收数据对象
     * @doc：https://o.lakala.com/#/home/document/detail?id=389
     * @date 2025-04-23 15:52
     * @author ZhouTing
     */
    public static function lklSeparate($param)
    {
        $sepParam = [
            'merchant_no' => $param['lkl_mer_cup_no'],
            'log_no' => $param['lkl_log_no'], //拉卡拉对账单流水号
            'log_date' => $param['lkl_log_date'],
            'out_separate_no' => date('YmdHis', time()) . Random::generate(8), //商户分账指令流水号
            'total_amt' => (string)($param['can_separate_amt']), //单位：分
            'lkl_org_no' => self::$config['org_code'],
            'cal_type' => '0', //分账计算类型 0- 按照指定金额，1- 按照指定比例。默认 0
            'notify_url' => request()->domain() . '/api/notify/lklSeparateNotify',
        ];

        $sepParam['recv_datas'] = $param['recv_datas'];

        record_log('Time: ' . date('Y-m-d H:i:s') . ', 订单分账请求参数: ' . json_encode($sepParam), 'lkl');

        $config = new Configuration();
        $api = new LakalaApi($config);
        $request = new ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v3/sacs/separate', $request);
            $res = $response->getOriginalText();
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 订单分账请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['code'] == 'SACS0000') {
                return $resdata['resp_data'];
            } else {
                return self::setErrorInfo($resdata['msg']);
            }
        } catch (\Lakala\OpenAPISDK\V3\ApiException $e) {
            record_log('Time: ' . date('Y-m-d H:i:s') . ', 订单分账异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉微信实名认证结果查询
     * @param lkl_mer_cup_no 拉卡拉商户编号 分账商户银联商户号(店铺)
     * @doc：https://o.lakala.com/#/home/document/detail?id=181
     * @date 2025-04-24 11:50
     * @author ZhouTing
     */
    public static function lklWechatRealNameQuery($param)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'merInnerNo' => $param['lkl_mer_cup_no']
        ];
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 微信实名认证查询请求参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/wechatRealNameQuery', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 微信实名认证查询请求请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData'];
            } else {
                return self::setErrorInfo('微信实名认证查询请求失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 微信实名认证查询请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 支付宝实名认证信息查询
     * @param lkl_mer_cup_no 拉卡拉商户编号 分账商户银联商户号(店铺)
     * @param sub_mch_id 子商户号
     * @doc：https://o.lakala.com/#/home/document/detail?id=345
     * @date 2025-04-24 13:29
     * @author ZhouTing
     */
    public static function lklAlipayRealNameQuery($param)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'merInnerNo' => $param['lkl_mer_cup_no'],
            'subMchId' => $param['sub_mch_id']
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 支付宝实名认证查询参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/alipayRealNameQuery', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 支付宝实名认证查询请求结果: ' . $res, 'lkl');
            $resdata = json_decode($res, true);
            if ($resdata['retCode'] == '000000') {
                return $resdata['respData'];
            } else {
                return self::setErrorInfo('支付宝实名认证查询失败，' . $resdata['retMsg']);
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 支付宝实名认证查询异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉电子合同下载
     * @param lkl_ec_apply_id 电子合同申请受理号
     * @date 2025-04-17 15:08
     * @author ZhouTing
     */
    public static function lklEcDownload($param)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgId' => self::$config['org_code'],
            'ecApplyId' => $param['lkl_ec_apply_id'],
        ];

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/ec/download', $request);
            if (!empty($response)) {
                $res = $response->getOriginalText();
                record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求结果: ' . $res, 'lkl');
                $resdata = json_decode($res, true);
                if (!empty($resdata['retCode']) && $resdata['retCode'] == '000000') {
                    $outputFilePath = self::decodeFromUrlSafeStringToFile($resdata['respData']['ecFile'], $param['lkl_ec_apply_id'] . '.pdf');
                    echo "<pre>";
                    print_r($outputFilePath);
                    die();
                } else {
                    return self::setErrorInfo('lkl' . $resdata['retMsg']);
                }
            }
        } catch (\Lakala\OpenAPISDK\V2\V2ApiException $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 电子合同下载请求异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('lkl' . $e->getMessage());
        }
    }

    /**
     * @desc 商户大类（拓客商户进件 获取mcc第一步）
     * @author ZhouTing
     * @date 2025-04-21 16:02
     */
    public static function lklParentCate()
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->get(self::$config['customer_cate_url'], [
                'headers' => [
                    'Authorization' => 'bearer ' . $token['access_token']
                ]
            ]);

            $rawBody = (string)$response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取商户类别失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 商户小类（拓客商户进件 获取mcc第二步）
     * @param parent_code 商户大类编码
     * @date 2025-04-21 17:47
     * @author ZhouTing
     */
    public static function lklChildCate($param)
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->get(self::$config['customer_cate_url'] . '/' . $param['parent_code'], [
                'headers' => [
                    'Authorization' => 'bearer ' . $token['access_token']
                ]
            ]);

            $rawBody = (string)$response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取商户小类别失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 地区信息(拓客平台)
     * @param parent_code 编码
     * @date 2025-04-18 13:53
     * @author ZhouTing
     */
    public static function lklOrganization($param)
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $parent_code = empty($param['parent_code']) ? 1 : $param['parent_code'];
        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->get(self::$config['organization_url'] . '/organization/' . $parent_code, [
                'headers' => [
                    'Authorization' => 'bearer ' . $token['access_token']
                ]
            ]);

            $rawBody = (string)$response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取地区失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 银行地区信息
     * @param parent_code 编码
     * @date 2025-04-19 10:20
     * @author ZhouTing
     */
    public static function lklBankOrganization($param)
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $param['parent_code'] = (isset($param['parent_code']) && !empty($param['parent_code'])) ? $param['parent_code'] : 1;

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 银行地区参数: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);
        try {
            $response = $client->get(self::$config['organization_url'] . '/organization/bank/' . $param['parent_code'], [
                'headers' => [
                    'Authorization' => 'bearer ' . $token['access_token']
                ]
            ]);

            $rawBody = (string)$response->getBody();
            return json_decode($rawBody, true);
        } catch (Exception $e) {
            return self::setErrorInfo('拉卡拉获取银行地区失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 拉卡拉 银行列表查询
     * @param area_code 地区编码
     * @param bank_name 银行名称
     * @date 2025-04-18 16:59
     * @author ZhouTing
     */
    public static function lklBankInfo($param)
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        record_log('时间: ' . date('Y-m-d H:i:s') . ', 银行列表参数: ' . json_encode($param, JSON_UNESCAPED_UNICODE), 'lkl');

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);

        try {
            // 发送 GET 请求
            $response = $client->get(self::$config['bank_url'], [
                'headers' => [
                    'Authorization' => 'bearer ' . $token['access_token']
                ],
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
     * @desc 卡BIN信息查询(开放平台)
     * @param cardNo 银行卡号
     * @doc：https://o.lakala.com/#/home/document/detail?id=179
     * @date 2025-04-17 18:26
     * @author ZhouTing
     */
    public static function lklCardBin($cardNo)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
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

            record_log('时间: ' . date('Y-m-d H:i:s') . ', 卡BIN查询结果: ' . $res, 'lkl');
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
     * @desc 电子合同转码下载(开放平台)
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
     * @desc 文件上传(拓客平台)
     * @param file 远程文件
     * @param type 图片类型
     * @date 2025-04-21 09:04
     * @author ZhouTing
     */
    public static function lklHtkFileUpload($param)
    {
        $token = self::lklAccessToken();
        if (!is_array($token)) return self::setErrorInfo(self::setErrorInfo());

        $client = new Client([
            'verify' => false // 禁用 SSL 验证
        ]);

        $fileContent = $client->get($param['file'])->getBody()->getContents();

        $sepParam = [
            'headers' => [
                'Authorization' => 'bearer ' . $token['access_token']
            ],
            'multipart' => [
                [
                    'name' => 'imgType',
                    'contents' => $param['type']
                ],
                [
                    'name' => 'sourcechnl',
                    'contents' => '0'
                ],
                [
                    'name' => 'isOcr',
                    'contents' => 'false'
                ],
                [
                    'name' => 'file',
                    'contents' => $fileContent,
                    'filename' => basename($param['file'])
                ]
            ]
        ];
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客文件上传请求参数: ' . json_encode($sepParam, JSON_UNESCAPED_UNICODE), 'lkl');

        try {
            $response = $client->post(self::$config['htk_file_upload_url'], $sepParam);

            $rawBody = (string)$response->getBody();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客文件上传结果: ' . $rawBody, 'lkl');

            return json_decode($rawBody, true);
        } catch (Exception $e) {
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 拓客文件上传异常: ' . $e->getMessage(), 'lkl');
            return self::setErrorInfo('拉卡拉拓客文件上传失败，' . $e->getMessage());
        }
    }

    /**
     * @desc 上传附件(开放平台)
     * @param attType 附件类型
     * @param url 附件
     * @doc：https://o.lakala.com/#/home/document/detail?id=90
     * @date 2025-03-04 10:38
     * @author ZhouTing
     */
    public static function lklUploadFile($attType, $url)
    {
        $sepParam = [
            'version' => '1.0',
            'orderNo' => date('YmdHis', time()) . Random::generate(8),
            'orgCode' => self::$config['org_code'],
            'attType' => $attType,
            'attExtName' => 'pdf',
            'attContext' => base64_encode($url)
        ];
        $log = $sepParam;
        $log['attContext'] = $url;
        record_log('时间: ' . date('Y-m-d H:i:s') . ', 上传附件请求参数: ' . json_encode($log, JSON_UNESCAPED_UNICODE), 'lkl');
        unset($log);

        $config = new V2Configuration();
        $api = new V2LakalaApi($config);
        $request = new V2ModelRequest();
        $request->setReqData($sepParam);
        try {
            $response = $api->tradeApi('/api/v2/mms/openApi/uploadFile', $request);
            $res = $response->getOriginalText();
            record_log('时间: ' . date('Y-m-d H:i:s') . ', 上传附件请求结果: ' . $res, 'lkl');
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

    public static $APPLYMENT_STATE_FAIL = 'APPLYMENT_STATE_FAIL';
    public static $APPLYMENT_STATE_COMMIT = 'APPLYMENT_STATE_COMMIT';
    public static $APPLYMENT_STATE_WAITTING_FOR_AUDIT = 'APPLYMENT_STATE_WAITTING_FOR_AUDIT';
    public static $APPLYMENT_STATE_EDITTING = 'APPLYMENT_STATE_EDITTING';
    public static $APPLYMENT_STATE_WAITTING_FOR_CONFIRM_CONTACT = 'APPLYMENT_STATE_WAITTING_FOR_CONFIRM_CONTACT';
    public static $APPLYMENT_STATE_WAITTING_FOR_CONFIRM_LEGALPERSON = 'APPLYMENT_STATE_WAITTING_FOR_CONFIRM_LEGALPERSON';
    public static $APPLYMENT_STATE_PASSED = 'APPLYMENT_STATE_PASSED';
    public static $APPLYMENT_STATE_REJECTED = 'APPLYMENT_STATE_REJECTED';
    public static $APPLYMENT_STATE_FREEZED = 'APPLYMENT_STATE_FREEZED';
    public static $APPLYMENT_STATE_CANCELED = 'APPLYMENT_STATE_CANCELED';

    public static $AUTHORIZE_STATE_UNAUTHORIZED = 'AUTHORIZE_STATE_UNAUTHORIZED';
    public static $AUTHORIZE_STATE_AUTHORIZED = 'AUTHORIZE_STATE_AUTHORIZED';

    public static function realNameState($type, $applyment_state, $authorize_state)
    {
        if ($type == 'wechat') {
            $applyment_state_list = [
                self::$APPLYMENT_STATE_FAIL => '提交失败',
                self::$APPLYMENT_STATE_COMMIT => '已提交',
                self::$APPLYMENT_STATE_WAITTING_FOR_AUDIT => '审核中',
                self::$APPLYMENT_STATE_EDITTING => '编辑中',
                self::$APPLYMENT_STATE_WAITTING_FOR_CONFIRM_CONTACT => '待确认联系信息',
                self::$APPLYMENT_STATE_WAITTING_FOR_CONFIRM_LEGALPERSON => '待账户验证',
                self::$APPLYMENT_STATE_PASSED => '审核通过',
                self::$APPLYMENT_STATE_REJECTED => '审核驳回',
                self::$APPLYMENT_STATE_FREEZED => '已冻结',
                self::$APPLYMENT_STATE_CANCELED => '已作废'
            ];

            $authorize_state_list = [
                self::$AUTHORIZE_STATE_UNAUTHORIZED => '未授权',
                self::$AUTHORIZE_STATE_AUTHORIZED => '已授权',
            ];

            $applyment_state_text = $applyment_state_list[$applyment_state] ?? '';
            $authorize_state_text = $authorize_state_list[$authorize_state] ?? '';
            return compact('applyment_state_text', 'authorize_state_text');
        }
    }
}

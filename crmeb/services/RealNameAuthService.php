<?php

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


namespace crmeb\services;


use AlibabaCloud\Dara\Util\Console;
use AlibabaCloud\Tea\Utils\Utils;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\SDK\Cloudauth\V20190307\Cloudauth;
use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\Id2MetaVerifyRequest;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\Id2MetaVerifyResponse;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use AlibabaCloud\Credentials\Credential\Config;
use think\exception\ValidateException;

class RealNameAuthService
{
    /**
     * 实名认证
     * @param string $realName 真实姓名
     * @param string $idCard 身份证号
     * @return array
     */
    public static function verify($realName, $idCard)
    {
        try {
            // 构建request
            $request = new Id2MetaVerifyRequest([]);
            $request->paramType = "normal";
            $request->userName = $realName;
            $request->identifyNum = $idCard;
            
            // 自动路由服务
            $response = self::id2MetaVerifyAutoRoute($request);
            
            if (!$response) {
                throw new ValidateException('实名认证服务异常，请稍后再试');
            }
            
            // 解析结果
            $result = Utils::toMap($response);
            
            // 验证结果
            if (isset($result['body']) && isset($result['body']['code']) && $result['body']['code'] == '200') {
                $data = $result['body']['data'];
                return [
                    'status' => true,
                    'message' => '认证成功',
                    'data' => $data
                ];
            } else {
                $message = isset($result['body']['message']) ? $result['body']['message'] : '认证失败';
                return [
                    'status' => false,
                    'message' => $message,
                    'data' => []
                ];
            }
        } catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            return [
                'status' => false,
                'message' => $error->message,
                'data' => []
            ];
        }
    }

    /**
     * 主备服务点循环调用，获取到成功结果返回。
     * @param Id2MetaVerifyRequest $request
     * @return Id2MetaVerifyResponse
     */
    private static function id2MetaVerifyAutoRoute($request){
        $endpoints = [
            "cloudauth.cn-shanghai.aliyuncs.com",
            "cloudauth.cn-beijing.aliyuncs.com"
        ];
        $lastResponse = null;
        foreach($endpoints as $endpoint){
            try {
                // 调用服务
                $response = self::id2MetaVerify($endpoint, $request);
                
                // 有一个服务调用成功即返回
                if (!Utils::isUnset($response) && Utils::equalNumber($response->statusCode, 200)) {
                    if (!Utils::isUnset($response->body) && Utils::equalString($response->body->code, "200")) {
                        $lastResponse = $response;
                        break;
                    }
                }
            }
            catch (Exception $error) {
                // 记录错误日志，继续尝试下一个节点
                continue;
            }
        }
        return $lastResponse;
    }

    /**
     * 获取服务Client实例，调用验证方法。
     * @param string $endpoint
     * @param Id2MetaVerifyRequest $request
     * @return Id2MetaVerifyResponse
     */
    private static function id2MetaVerify($endpoint, $request){
        // 获取SDK Client实例
        $client = self::createClient($endpoint);
        // 构建RuntimeObject
        $runtime = new RuntimeOptions([]);
        $runtime->readTimeout = 5000;
        $runtime->connectTimeout = 5000;
        // 连接
        return $client->id2MetaVerifyWithOptions($request, $runtime);
    }

    /**
     * 安全创建服务Client实例。
     * @param string $endpoint
     * @return Cloudauth
     */
    private static function createClient($endpoint){
        // 直接设置AccessKey
        $credentialConfig = new Config([
            // 您的 AccessKey ID
            "accessKeyId" => systemConfig('aliyun_AccessKeyId'),
            // 您的 AccessKey Secret
            "accessKeySecret" => systemConfig('aliyun_AccessKeySecret')
        ]);
        $credential = new Credential($credentialConfig);
        // 创建SDK Client实例
        $apiConfig = new \Darabonba\OpenApi\Models\Config([]);
        $apiConfig->credential = $credential;
        $apiConfig->endpoint = $endpoint;
        return new Cloudauth($apiConfig);
    }
}
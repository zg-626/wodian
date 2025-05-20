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


use AlibabaCloud\Tea\Utils\Utils;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\SDK\Cloudauth\V20190307\Cloudauth;
use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\Id2MetaVerifyRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config as OpenApiConfig;
use think\exception\ValidateException;
use think\facade\Log;

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
            // 获取客户端
            $client = self::createClient();
            
            // 构建请求
            $request = new Id2MetaVerifyRequest([
                "paramType" => "normal",
                "identifyNum" => $idCard,
                "userName" => $realName
            ]);
            
            // 设置运行时选项
            $runtime = new RuntimeOptions([]);
            $runtime->readTimeout = 10000;
            $runtime->connectTimeout = 10000;
            
            // 发送请求
            $response = $client->id2MetaVerifyWithOptions($request, $runtime);
            
            // 记录响应日志
            Log::info('实名认证响应: ' . json_encode($response, JSON_UNESCAPED_UNICODE));

            // 解析结果
            if (isset($response->body) && isset($response->body->code) && $response->body->code == "200") {
                // 检查业务结果码
                if (isset($response->body->resultObject) && isset($response->body->resultObject->bizCode)) {
                    $bizCode = $response->body->resultObject->bizCode;

                    // bizCode为1表示认证通过，2表示认证不通过
                    if ($bizCode == "1") {
                        return [
                            'status' => true,
                            'message' => '认证成功',
                            'data' => [
                                'bizCode' => $bizCode,
                                'requestId' => $response->body->requestId ?? '',
                                'message' => $response->body->message ?? 'success'
                            ]
                        ];
                    }

                    if ($bizCode == "2") {
                        return [
                            'status' => false,
                            'message' => '身份信息不匹配',
                            'data' => [
                                'bizCode' => $bizCode,
                                'requestId' => $response->body->requestId ?? '',
                                'message' => $response->body->message ?? 'fail'
                            ]
                        ];
                    }

                    return [
                       'status' => false,
                       'message' => '查⽆记录',
                        'data' => [
                            'bizCode' => $bizCode,
                           'requestId' => $response->body->requestId?? '',
                           'message' => $response->body->message?? 'fail'
                        ]
                    ];
                }

                return [
                    'status' => false,
                    'message' => '认证结果异常',
                    'data' => isset($response->body) ? json_decode(json_encode($response->body), true) : []
                ];
            }

            $message = isset($response->body->message) ? $response->body->message : '认证失败';
            return [
                'status' => false,
                'message' => $message,
                'data' => []
            ];
        } catch (Exception $error) {
            // 处理异常
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            
            // 记录错误日志
            $errorMessage = $error->message;
            $recommend = isset($error->data["Recommend"]) ? $error->data["Recommend"] : '';
            Log::error('实名认证异常: ' . $errorMessage . ', 建议: ' . $recommend);
            
            return [
                'status' => false,
                'message' => '实名认证服务异常，请稍后再试',
                'data' => []
            ];
        }
    }

    /**
     * 创建客户端
     * @return Cloudauth
     */
    private static function createClient(){
        // 方式一：使用AccessKey（如果您已配置环境变量或实例RAM角色，可以使用方式二）
        $config = new OpenApiConfig([
            // 您的 AccessKey ID
            "accessKeyId" => systemConfig('aliyun_AccessKeyId'),
            // 您的 AccessKey Secret
            "accessKeySecret" => systemConfig('aliyun_AccessKeySecret'),
            // 访问的端点
            "endpoint" => "cloudauth.aliyuncs.com"
        ]);
        
        // 方式二：使用无AK方式（需要配置环境变量或实例RAM角色）
        // $credential = new Credential();
        // $config = new Config([
        //     "credential" => $credential,
        //     "endpoint" => "cloudauth.aliyuncs.com"
        // ]);
        
        return new Cloudauth($config);
    }
}
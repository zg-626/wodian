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
//use AlibabaCloud\Tea\Console\Console;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\SDK\Cloudauth\V20190307\Cloudauth;
use AlibabaCloud\Credentials\Credential;

use AlibabaCloud\SDK\Cloudauth\V20190307\Models\Id2MetaVerifyRequest;
use AlibabaCloud\SDK\Cloudauth\V20190307\Models\Id2MetaVerifyResponse;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use AlibabaCloud\Credentials\Credential\Config;

class SampleService
{
    /**
     * @param string[] $args
     * @return void
     */
    public static function main($args){
        try {
            // 构建request。
            $request = new Id2MetaVerifyRequest([]);
            $request->paramType = "normal";
            $request->userName = @$args[0];
            $request->identifyNum = @$args[1];
            // 自动路由服务。
            $response = self::id2MetaVerifyAutoRoute($request);
            $ret = Utils::toJSONString(Utils::toMap($response));
            Console::log("最终结果（若此处为空，则所有服务点均异常，请逐步调试）：" . $ret . "");
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            Console::error($error->message);
        }
    }

    /**
     * 主备服务点循环调用，获取到成功结果返回。
     * @param Id2MetaVerifyRequest $request
     * @return Id2MetaVerifyResponse
     */
    public static function id2MetaVerifyAutoRoute($request){
        $endpoints = [
            "cloudauth.cn-shanghai.aliyuncs.com",
            "cloudauth.cn-beijing.aliyuncs.com"
        ];
        $lastResponse = null;
        foreach($endpoints as $endpoint){
            try {
                // 调用服务。
                $response = self::id2MetaVerify($endpoint, $request);
                // 节点调用结果
                $ret = Utils::toJSONString(Utils::toMap($response));
                Console::log("节点 " . $endpoint . " 结果：" . $ret . " ");
                // 有一个服务调用成功即返回。
                if (!Utils::isUnset($response) && Utils::equalNumber($response->statusCode, 200)) {
                    if (!Utils::isUnset($response->body) && Utils::equalString($response->body->code, "200")) {
                        $lastResponse = $response;
                        break;
                    }
                }
            }
            catch (Exception $error) {
                if (!($error instanceof TeaError)) {
                    $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
                }
                Console::error("节点 " . $endpoint . " 调用异常：" . $error->message . "");
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
    public static function id2MetaVerify($endpoint, $request){
        // 获取SDK Client实例。
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
    public static function createClient($endpoint){
        // 直接设置AccessKey
        $credentialConfig = new Config([
            'accessKeyId' => '您的AccessKey_ID',
            'accessKeySecret' => '您的AccessKey_Secret'
        ]);
        $credential = new Credential($credentialConfig);
        // 创建SDK Client实例。
        $apiConfig = new \Darabonba\OpenApi\Models\Config([]);
        $apiConfig->credential = $credential;
        $apiConfig->endpoint = $endpoint;
        return new Cloudauth($apiConfig);
    }

}

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


namespace app\validate\api;


use think\Validate;

/**
 * 用户实名认证验证器
 * Class UserRealAuthValidate
 * @package app\validate\api
 */
class UserRealAuthValidate extends Validate
{
    /**
     * 定义验证规则
     * @var array
     */
    protected $rule = [
        'real_name' => 'require|chs|length:2,20',
        'id_card' => 'require|idCard',
    ];

    /**
     * 定义错误信息
     * @var array
     */
    protected $message = [
        'real_name.require' => '请输入真实姓名',
        'real_name.chs' => '真实姓名只能是汉字',
        'real_name.length' => '真实姓名长度为2-20个字符',
        'id_card.require' => '请输入身份证号',
        'id_card.idCard' => '身份证号格式不正确',
    ];

    /**
     * 验证身份证号
     * @param $value
     * @return bool
     */
    protected function idCard($value)
    {
        $pattern = '/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/';
        return preg_match($pattern, $value) ? true : false;
    }
}
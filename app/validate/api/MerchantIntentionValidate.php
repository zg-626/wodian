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

class MerchantIntentionValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'phone|手机号' => 'require|mobile',
        'name|姓名' => 'require',
        'salesman_id|商务id' => 'require',
        'mer_name|姓名' => 'require|max:32',
        'merchant_category_id|商户分类' => 'require',
        'mer_type_id|店铺类型' => 'integer',
        'code|验证码' => 'require',
        'images|资质' => 'array',
        'id_card|法人身份证正反面' => 'array',
        'inside|经营区域内部照片' => 'array',
        //'address|经营地址' => 'require',
        // 邮箱
        //'email|邮箱' => 'require|email',
        // 银行卡号
        //'bank_card|银行卡号' => 'require',
        // 银行名称
        //'bank_name|银行名称' => 'require',
        // 开户行名称
        //'bank_open_name|开户行名称' => 'require',
        // 开户行支行名称
        //'bank_branch_name|开户行支行名称' => 'require',

        'merchant_type|商户类型' => 'require|in:0,1',
        'cert_name|法人/经营者姓名' => 'require',
        'cert_no|法人/经营者证件号' => 'require',
        'ec_mobile|签约手机号' => 'require',
        'acct_type_code|企业/经营者结算卡性质' => 'require|in:57,58',
        'acct_no|企业/经营者结算卡号' => 'require',
        'acct_name|企业/经营者结算卡名称' => 'require',
        'agent_tag|是否经办签约' => 'require|in:0,1',
        'agent_name|经办人名称' => 'requireIf:agent_tag,1',
        'agent_cert_no|经办人证件号' => 'requireIf:agent_tag,1',
        'agent_file_path|经办授权委托书文件路径' => 'requireIf:agent_tag,1',
        'A1|签约商户的商户名称' => 'require',
        'B1|商户注册登记表的年' => 'require',
        'B2|商户注册登记表的月' => 'require',
        'B9|主营业务' => 'require',
        'B10|商户对外经营名称' => 'require',
        'B19|开户行' => 'require',
        'B20|结算账号' => 'require',
        'B24|法人姓名' => 'require',
        'B25|法人证件号' => 'require',
        'B26|法人手机号' => 'require',
        'B27|联系人姓名' => 'require',
        'B28|联系人邮箱' => 'require',
        'B29|联系人证件号码' => 'require',
        'B30|联系人手机号' => 'require',
        'B33|联系人地址' => 'require',
        'mer_blis_name|营业执照名称/个人真实姓名' => 'requireIf:merchant_type,1',
        'mer_blis|营业执照号' => 'requireIf:merchant_type,1',
        'openning_bank_code|企业/经营者结算开户行号' => 'requireIf:acct_type_code,57',
        'openning_bank_name|企业/经营者结算开户行名称' => 'requireIf:acct_type_code,57',
    ];

    protected $message = [
        'merchant_type.require' => '请选择商户类型',
        'merchant_type.in' => '请选择商户类型！',
        'cert_name.require' => '请输入法人/经营者姓名',
        'cert_no .require' => '请输入法人/经营者证件号码',
        'ec_mobile.require' => '请输入签约手机号',
        'acct_type_code.require' => '请选择企业/经营者结算卡性质',
        'acct_type_code.in' => '请选择企业/经营者结算卡性质！',
        'acct_no.require' => '请输入企业/经营者结算卡号',
        'acct_name.require' => '请输入企业/经营者结算卡名称',
        'agent_tag.require' => '请选择是否经办签约',
        'agent_tag.in' => '请选择是否经办签约！',
        'agent_name.requireIf' => '请输入经办人名称',
        'agent_cert_no.requireIf' => '请输入经办人证件号',
        'agent_file_path.requireIf' => '请输入经办授权委托书文件路径',
        'A1.require' => '请输入签约商户的商户名称',
        'B1.require' => '请输入商户注册登记表的年',
        'B2.require' => '请输入商户注册登记表的月',
        'B9.require' => '请输入主营业务',
        'B10.require' => '请输入商户对外经营名称',
        'B19.require' => '请输入开户行',
        'B20.require' => '请输入结算账号',
        'B24.require' => '请输入法人姓名',
        'B25.require' => '请输入法人证件号',
        'B26.require' => '请输入法人手机号',
        'B27.require' => '请输入联系人姓名',
        'B28.require' => '请输入联系人邮箱',
        'B29.require' => '请输入联系人证件号码',
        'B30.require' => '请输入联系人手机号',
        'B33.require' => '请输入联系人地址',
        'mer_blis_name.requireIf' => '请输入营业执照名称/个人真实姓名',
        'mer_blis.requireIf' => '请输入营业执照号',
        'openning_bank_code.requireIf' => '请输入企业/经营者结算开户行号',
        'openning_bank_name.requireIf' => '请输入企业/经营者结算开户行名称',
    ];

    protected $scene = [
        'create' => [
            'merchant_type',
            'cert_name',
            'cert_no',
            'ec_mobile',
            'acct_type_code',
            'acct_no',
            'acct_name',
            'agent_tag',
            'agent_name',
            'agent_cert_no',
            'agent_file_path',
            'A1', 'B1', 'B2', 'B9', 'B10', 'B19', 'B20', 'B24', 'B25', 'B26', 'B27', 'B28', 'B29', 'B30', 'B33',
            'mer_blis_name',
            'mer_blis',
            'openning_bank_code',
            'openning_bank_name'
        ],
    ];
}

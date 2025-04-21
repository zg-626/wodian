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

        ''
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

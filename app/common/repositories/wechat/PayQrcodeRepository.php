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


namespace app\common\repositories\wechat;


use app\common\dao\BaseDao;
use app\common\dao\wechat\PayQrcodeDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\attachment\AttachmentRepository;
use crmeb\services\QrcodeService;
use crmeb\services\WechatService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\Model;

/**
 * Class PayQrcodeRepository
 * @package app\common\repositories\wechat
 * @author xaboy
 * @day 2020-04-28
 * @mixin PayQrcodeDao
 */
class PayQrcodeRepository extends BaseRepository
{
    /**
     * PayQrcodeRepository constructor.
     * @param PayQrcodeDao $dao
     */
    public function __construct(PayQrcodeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param $merId
     * @param $ratio
     * @param $mer_avatar
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-04-28
     */
    public function createQrcode($merId, $ratio, $info): array
    {
        $qrcode = $this->dao->getWhere(['mer_id' => $merId,'status'=>1, 'commission_rate' => $ratio]);
        if ($qrcode) {
            throw new ValidateException('此比例已存在，请勿重复创建');
        }
        // 比例不能大于自身的比例
        if($ratio > $info['commission_rate']){
            throw new ValidateException('比例不能大于签到的比例');
        }
        // 比例不能小于2
        if($ratio < 2){
            throw new ValidateException('比例不能小于2');
        }
        $siteUrl = rtrim(systemConfig('site_url'), '/');
        // 参数
        $params = '/payPage'. '?target=eqcode'. '&shopId=' . $merId. '&pvRatio=' . $ratio;
        $codeUrl = $siteUrl .$params;//二维码链接
        $name = md5('shop' . $merId . $ratio. date('Ymd')) . '.jpg';
        $logoPath = $info['mer_avatar']; // Logo 图片路径
        $imageInfo = app()->make(QrcodeService::class)->getQRCodeLogoPath($codeUrl, $name,$logoPath);
        if (is_string($imageInfo)) throw new ValidateException('二维码生成失败');
        $imageInfo['dir'] = tidy_url($imageInfo['dir'], null, $siteUrl);
        $attachmentRepository = app()->make(AttachmentRepository::class);
        $attachmentRepository->create(systemConfig('upload_type') ?: 1, -2, $merId, [
            'attachment_category_id' => 0,
            'attachment_name' => $imageInfo['name'],
            'attachment_src' => $imageInfo['dir']
        ]);

        $urlCode = $imageInfo['dir'];

        $data = [
            'mer_id' => $merId,
            'qrcode' => $urlCode,
            'ticket' => $params,
            'third_type' => 'shop',
            'commission_rate' => $ratio,
            'add_time' => time()
        ];

        $this->dao->create($data);

        // 获取海报背景
        $pay_image = systemConfig('pay_image');
        return [
            'qrcode' => $urlCode,
            'ratio' => $ratio,
            'pay_image' => $pay_image
        ];
    }

    /**
     * @param $merId
     * @param $ratio
     * @param $mer_avatar
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-04-28
     */
    public function updateQrcode($id, $ratio, $info): array
    {
        $merId=$info['mer_id'];
        // 排除自身id
        $qrcode = $this->dao->getWhere(['mer_id' => $merId,'status'=>1,'commission_rate' => $ratio, 'wechat_qrcode_id' => ['neq', $id]]);
        if ($qrcode) {
            throw new ValidateException('此比例已存在，请重新输入');
        }
        // 比例不能大于自身的比例
        if($ratio > $info['commission_rate']){
            throw new ValidateException('比例不能大于签到的比例');
        }
        // 比例不能小于2
        if($ratio < 2){
            throw new ValidateException('比例不能小于2');
        }
        $siteUrl = rtrim(systemConfig('site_url'), '/');
        // 参数
        $params = '/payPage'. '?target=eqcode'. '&shopId=' . $merId. '&pvRatio=' . $ratio;
        $codeUrl = $siteUrl .$params;//二维码链接
        $name = md5('shop' . $merId . $ratio. date('Ymd')) . '.jpg';
        $logoPath = $info['mer_avatar']; // Logo 图片路径
        $imageInfo = app()->make(QrcodeService::class)->getQRCodeLogoPath($codeUrl, $name,$logoPath);
        if (is_string($imageInfo)) throw new ValidateException('二维码生成失败');
        $imageInfo['dir'] = tidy_url($imageInfo['dir'], null, $siteUrl);
        $attachmentRepository = app()->make(AttachmentRepository::class);
        $attachmentRepository->create(systemConfig('upload_type') ?: 1, -2, $merId, [
            'attachment_category_id' => 0,
            'attachment_name' => $imageInfo['name'],
            'attachment_src' => $imageInfo['dir']
        ]);

        $urlCode = $imageInfo['dir'];

        $data = [
            'mer_id' => $merId,
            'qrcode' => $urlCode,
            'ticket' => $params,
            'third_type' => 'shop',
            'commission_rate' => $ratio,
            'add_time' => time()
        ];

        $qrcodeInfo = $this->dao->getWhere(['wechat_qrcode_id' => $id]);
        $qrcodeInfo->save($data);
        // 获取海报背景
        $pay_image = systemConfig('pay_image');
        return [
            'qrcode' => $urlCode,
            'ratio' => $ratio,
            'pay_image' => $pay_image
        ];
    }

    // 删除付款码
    public function deleteQrcode($id): void
    {
        $qrcodeInfo = $this->dao->getWhere(['wechat_qrcode_id' => $id]);
        if($qrcodeInfo){
            $qrcodeInfo->delete();
        }

    }

    // 开启关闭付款码
    public function closePayCode($id,$status): void
    {
        $qrcodeInfo = $this->dao->getWhere(['wechat_qrcode_id' => $id]);
        if($qrcodeInfo){
            $qrcodeInfo->save(['status' => $status]);
        }

    }


    /**
     * @param $type
     * @param $id
     * @return array|\think\Collection|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author xaboy
     * @day 2020-04-28
     */
    public function payCodeLst($merId)
    {
        $qrcode= $this->dao->getQrcode($merId);
        // 获取海报背景
        $pay_image = systemConfig('pay_image');
        if($qrcode){
            foreach ($qrcode as $k=>$v){
                $qrcode[$k]['pay_image'] = $pay_image;
            }

        }
        return $qrcode;


    }


}

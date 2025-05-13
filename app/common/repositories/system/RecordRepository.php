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


namespace app\common\repositories\system;

use app\common\dao\system\RecordDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\user\UserAddressRepository;

class RecordRepository extends BaseRepository
{

    //文章关联商品
    const TYPE_ADDRESS_RECORD  =  'address_record';

    protected $dao;

    /**
     * @param RecordDao $dao
     */
    public function __construct(RecordDao $dao)
    {
        $this->dao = $dao;
    }

    public function addRecord(string $type, array $data)
    {
        if (empty($data)) return ;
        switch ($type) {
            case self::TYPE_ADDRESS_RECORD :
                $userAddressRepository = app()->make(UserAddressRepository::class);
                $addres = $userAddressRepository->getWhere(['address_id' => $data['address_id']]);
                $cityid =  ($addres['city'] == '市辖区') ? $addres['district_id'] : $addres['city_id'] ;
                $city =  ($addres['city'] == '市辖区') ? $addres['district'] : $addres['city'] ;
                $this->dao->incType($type, $addres['province_id'], $data['num'],['title' => $addres['province']]);
                $this->dao->incType($type, $cityid, $data['num'],['title' => $city]);
                break;
        }
    }
}

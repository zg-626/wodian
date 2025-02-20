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


namespace app\common\repositories\user;


use app\common\dao\user\UserInfoDao;
use app\common\dao\user\UserSignDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\config\ConfigValueRepository;
use FormBuilder\Factory\Elm;
use Swoole\Database\MysqliException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Route;

/**
 * @mixin UserInfoDao
 */
class UserInfoRepository extends BaseRepository
{

    protected $type = [
        'input'     => ['label' => '文本',   'type' => 'varchar(255)',  'value' => '', 'default' => ''],
        'int'       => ['label' => '数字',   'type' => 'int(11)',      'value' => 0,  'default' => 0],
        'phone'     => ['label' => '手机号', 'type' => 'varchar(11)',  'value' => '',  'default' => ''],
        'date'      => ['label' => '时间',   'type' => 'date',         'value' => null,'default' => 'NULL'],
        'radio'     => ['label' => '单选',   'type' => 'tinyint(1)',   'value' => 0,  'default' => 0],
        'address'   => ['label' => '地址',   'type' => 'varchar(255)', 'value' => '', 'default' => ''],
        'id_card'   => ['label' => '身份证', 'type' => 'varchar(255)', 'value' => '',  'default' => ''],
        'email'     => ['label' => '邮箱',   'type' => 'varchar(255)', 'value' => '', 'default' => ''],
    ];
    const FIXED_FIELD = ['uid'];

    /**
     * @var UserInfoDao
     */
    protected $dao;

    /**
     * UserSignRepository constructor.
     * @param UserInfoDao $dao
     */
    public function __construct(UserInfoDao $dao)
    {
        $this->dao = $dao;
    }

    // 验证是否是有效的列名
    protected function isValidColumnName($columnName)
    {
        // 检查是否以字母开头，只包含字母、数字和下划线
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
            throw new ValidateException("无效的列名: {$columnName}");
        }
    }


    public function executeSql($db, $sql, $params = [])
    {
        try {
            $db->execute($sql, $params);
        } catch (MysqliException $exception) {
            throw new ValidateException($exception->getMessage());
        }
    }

    /**
     * 创建字段
     * @param array $data
     * @param $type
     * @return bool
     *
     * @date 2023/09/25
     * @author yyw
     */
    protected function changeField(array $data, $type = true)
    {
        // 获取数据库连接实例
        $db = Db::connect();
        //操作的数据表
        $tableName = env('database.prefix', 'eb_') . 'user_fields';
        // 操作的字段名称
        $fieldName = $data['field'];
        // 验证字段端名是否合法
        $this->isValidColumnName($fieldName);
        //
        if ($type) {
            // 字段类型，可以根据需要进行修改
            $fieldType = $this->type[$data['type']]['type'] ?? 'varchar(255)';
            $comment = $data['title'] ?: $data['msg'];
            $default = $this->type[$data['type']]['default'];
            $sql = "ALTER TABLE `$tableName` ADD COLUMN `$fieldName` $fieldType NULL DEFAULT '$default' COMMENT '$comment'";
        } else {
            $sql = "ALTER TABLE `$tableName` DROP COLUMN `$fieldName`";

        }
        $this->executeSql($db, $sql);
        return true;
    }

    /**
     * 删除字段
     * @param string $fieldName
     * @return bool
     *
     * @date 2023/09/25
     * @author yyw
     */
    protected function deleteField(string $fieldName)
    {
        // 验证字段名称是否有效
        $this->isValidColumnName($fieldName);

        // 获取数据库连接实例
        $db = Db::connect();

        // 操作的数据表
        $tableName = env('database.prefix', 'eb_') . 'user_fields';

        // 构建 SQL 删除字段的语句
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$fieldName`";

        $this->executeSql($db, $sql);

        return true;
    }


    public function getList(array $where = [], int $page = 1, int $limit = 10)
    {
        $query = $this->dao->getSearch($where);
        $count = $query->count();
        $list = $query->order(['sort', 'create_time' => 'ASC'])->select()->each(function ($item) {
            return $item->type_name = $this->type[$item->type]['label'];
        });
        $avatar = systemConfig('user_default_avatar');
        return compact('count', 'list', 'avatar');
    }

    public function createFrom()
    {
        $action = Route::buildUrl('systemUserInfoCreate')->build();
        $form = Elm::createForm($action, [
            Elm::select('type', '字段类型：')->options($this->getType())->control([
                [
                    'value' => 'radio',
                    'rule' => [
                        Elm::textarea('content', '配置内容')->placeholder('请输入配置内容')->required()
                    ]
                ]
            ])->required(),
            Elm::input('title', '字端名称：')->placeholder('请输入字端名称')->required(),
            Elm::input('field', '字段key：')->placeholder('请输入字段key')->required(),
            Elm::input('msg', '提示信息：')->placeholder('请输入提示信息')->required()
        ]);

        return $form->setTitle('添加字段');

    }


    public function create(array $data)
    {
        // 验证类型
        if (!isset($this->type[$data['type']])) {
            throw new ValidateException('字段类型异常');
        }
        if ($data['type'] == 'radio' && (!is_array($data['content']) || count($data['content']) < 2)) {
            throw new ValidateException('请至少创建两个选项');
        }
        $data['content'] = json_encode($data['content']);
        $data['sort'] = 999;
        return Db::transaction(function () use ($data) {
            $this->changeField($data);
            $this->dao->create($data);
        });
    }

    public function saveAll(array $data = [])
    {
        app()->make(ConfigValueRepository::class)->setFormData(['user_default_avatar' => $data['avatar']], 0);
        //保存用户表单
        if (!empty($data['user_extend_info'])) {

            foreach ($data['user_extend_info'] as $sort => $item) {
                if (!isset($item['field']) || !isset($item['is_used']) || !isset($item['is_require']) || !isset($item['is_show'])) {
                    throw new ValidateException('参数不能为空');
                }
                if (!in_array($item['is_used'], [0, 1]) || !in_array($item['is_require'], [0, 1]) || !in_array($item['is_show'], [0, 1])) {
                    throw new ValidateException('参数类性错误');
                }
                $this->dao->query([])->where('field', $item['field'])->update(['is_used' => $item['is_used'], 'is_require' => $item['is_require'], 'is_show' => $item['is_show'], 'sort' => $sort]);
            }
        }
        return true;
    }

    public function delete(int $id)
    {
        $info = $this->dao->get($id);
        if (empty($info) || empty($info['field'])) {
            throw new ValidateException('表单数据异常');
        }
        if ($info['is_default']) {
            throw new ValidateException('默认表单不能删除');
        }
        return Db::transaction(function () use ($info, $id) {
            $this->deleteField($info['field']);
            $this->dao->delete($id);
        });
    }


    public function getType()
    {
        $res = [];
        foreach ($this->type as $k => $v) {
            $v['value'] = $k;
            unset($v['type']);
            $res[] = $v;
        }
        return $res;
    }

    public function getSelectList()
    {
        return $this->dao->getSearch(['is_used' => 1])->whereNotIn('type', ['radio', 'date'])->order(['sort', 'create_time' => 'ASC'])->column('title as label,field as value');
    }

    /**
     * 验证字段是否为默认字段
     * @param string $fields
     * @return bool
     */
    public function getFieldsIsItDefault(string $field)
    {
        return (bool)$this->getSearch(['field' => $field])->value('is_default', 0);
    }
}

<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 本地镜像验证
 */
class LocalImageValidate extends Validate
{
    protected $rule = [
        'id'        => 'require|integer|gt:0',
        'group_id'  => 'integer|egt:0',
        'name'      => 'require|max:255',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'group_id.integer'  => 'id_error',
        'group_id.egt'      => 'id_error',
        'name.require'      => 'local_image_name_require',
        'name.max'          => 'local_image_name_error',
    ];

    protected $scene = [
        'create' => ['group_id', 'name'],
        'update' => ['id', 'group_id', 'name'],
    ];
}
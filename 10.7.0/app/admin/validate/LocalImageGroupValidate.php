<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 本地镜像分组验证
 */
class LocalImageGroupValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:20',
        'icon'          => 'require',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'name.require'      => 'local_image_group_name_require',
        'name.max'          => 'local_image_group_name_error',
        'icon.require'      => 'local_image_group_icon_require',
    ];

    protected $scene = [
        'create' => ['name', 'icon'],
        'update' => ['id', 'name', 'icon'],
    ];
}
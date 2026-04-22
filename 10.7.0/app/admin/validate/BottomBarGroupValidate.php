<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 模板控制器-底部栏分组验证
 */
class BottomBarGroupValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'language'      => 'in:zh-cn,en-us',
        'name'          => 'require|max:20',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'language.in'       => 'param_error',
        'name.require'      => 'bottom_bar_group_name_require',
        'name.max'          => 'bottom_bar_group_name_error',
    ];

    protected $scene = [
        'create' => ['language', 'name'],
        'update' => ['id', 'name'],
    ];
}
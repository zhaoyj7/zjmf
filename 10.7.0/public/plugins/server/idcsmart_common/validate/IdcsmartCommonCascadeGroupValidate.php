<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 级联组验证器
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeGroupValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer',
        'configoption_id' => 'require|integer',
        'group_name' => 'require|max:255',
        'level' => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require' => 'param_error',
        'id.integer' => 'param_error',
        'configoption_id.require' => 'param_error',
        'configoption_id.integer' => 'param_error',
        'group_name.require' => 'param_error',
        'group_name.max' => 'param_error',
        'level.require' => 'param_error',
        'level.integer' => 'param_error',
        'level.gt' => 'param_error',
    ];

    protected $scene = [
        'create' => ['configoption_id', 'group_name', 'level'],
        'update' => ['id', 'group_name'],
    ];
}

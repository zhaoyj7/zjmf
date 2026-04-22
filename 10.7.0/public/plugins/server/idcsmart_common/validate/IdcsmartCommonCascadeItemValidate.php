<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 级联项验证器
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeItemValidate extends Validate
{
    protected $rule = [
        'id' => 'require|integer',
        'configoption_id' => 'require|integer',
        'item_name' => 'require|max:255',
        'parent_item_id' => 'integer',
        'order' => 'integer',
        'hidden' => 'in:0,1',
    ];

    protected $message = [
        'id.require' => 'param_error',
        'id.integer' => 'param_error',
        'configoption_id.require' => 'param_error',
        'configoption_id.integer' => 'param_error',
        'item_name.require' => 'param_error',
        'item_name.max' => 'param_error',
        'parent_item_id.integer' => 'param_error',
        'order.integer' => 'param_error',
        'hidden.in' => 'param_error',
    ];

    protected $scene = [
        'create' => ['configoption_id', 'item_name', 'parent_item_id', 'order', 'hidden'],
        'update' => ['id', 'item_name', 'order', 'hidden'],
    ];
}

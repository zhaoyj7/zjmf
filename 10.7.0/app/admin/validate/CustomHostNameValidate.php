<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 自定义产品标识验证
 */
class CustomHostNameValidate extends Validate
{
	protected $rule = [
		'id'                                => 'require|integer',
        'custom_host_name_prefix'           => 'require|regex:/^[a-zA-Z][a-zA-Z0-9_\-\.]{1,10}$/|length:1,10',
        'custom_host_name_string_allow'     => 'require|array|checkAllow:thinkphp',
        'custom_host_name_string_length'    => 'require|integer|between:5,50',
        'product_group_id'                  => 'require|array',
    ];

    protected $message  =   [
    	'id.require'     			                => 'id_error',
    	'id.integer'     			                => 'id_error',
        'custom_host_name_prefix.require'           => 'custom_host_name_prefix_require',
        'custom_host_name_prefix.regex'             => 'custom_host_name_prefix_error',
        'custom_host_name_prefix.length'            => 'custom_host_name_prefix_error',
        'custom_host_name_string_allow.require'     => 'custom_host_name_string_allow_require',
        'custom_host_name_string_allow.array'       => 'param_error', 
        'custom_host_name_string_length.require'    => 'custom_host_name_string_length_require',
        'custom_host_name_string_length.integer'    => 'custom_host_name_string_length_error',
        'custom_host_name_string_length.between'    => 'custom_host_name_string_length_error',
        'product_group_id.require'                  => 'param_error',
        'product_group_id.array'                    => 'param_error',
    ];

    protected $scene = [
        'create' => ['custom_host_name_prefix', 'custom_host_name_string_allow', 'custom_host_name_string_length'],
        'update' => ['id', 'custom_host_name_prefix', 'custom_host_name_string_allow', 'custom_host_name_string_length'],
        'related' => ['id', 'product_group_id'],
    ];

    // 验证选中字段
    public function checkAllow($value, $type, $data)
    {
        if(!is_array($value)){
            return 'param_error';
        }

        if(empty($value)){
            return 'param_error';
        }

        if(count(array_unique($value))!=count($value)){
            return 'param_error';
        }

        foreach ($value as $v) {
            if(!in_array($v, ['number', 'upper', 'lower'])){
                return 'param_error';
            }
        }

        return true;
    }
}
<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 通用接口验证
 */
class PublicValidate extends Validate
{
	protected $rule = [
        'action'        => 'require|in:admin_login,admin_verify,admin_update',
        'name'          => 'requireIf:action,admin_login|requireIf:action,admin_verify|max:50',
        'email'         => 'requireIf:action,admin_update|email|unique:admin',
        'phone_code'    => 'requireIf:action,admin_update',
        'phone'         => 'requireIf:action,admin_update|max:11|number|unique:admin,phone_code^phone',
    ];

    protected $message  =   [
        'action.require'            => 'param_error', 
        'action.in'                 => 'param_error',
        'name.requireIf'     		=> 'please_enter_admin_name',
        'name.max'     			    => 'admin_name_cannot_exceed_50_chars',
        'email.requireIf'           => 'please_enter_vaild_email',
        'email.email'               => 'please_enter_vaild_email',
        'email.unique'        		=> 'admin_email_unique',
        'phone_code.requireIf'      => 'please_select_phone_code', 
        'phone.requireIf'           => 'please_enter_vaild_phone', 
        'phone.max'                 => 'please_enter_vaild_phone', 
        'phone.number'              => 'please_enter_vaild_phone',
        'phone.unique'        		=> 'admin_phone_unique',
    ];

    protected $scene = [
        'sened_phone_code' => ['action', 'name', 'phone_code', 'phone'],
        'sened_email_code' => ['action', 'name', 'email'],
    ];
}
<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 管理员绑定验证
 */
class AdminBindValidate extends Validate
{
	protected $rule = [
        'email' 	    => 'require|email',
        'phone_code'    => 'require|number',
        'phone'         => 'require|max:11|number',
        'code' 		    => 'require',
        'method'        => 'require|in:totp,phone,email',
    ];

    protected $message  =   [
        'email.require'         => 'please_enter_vaild_email',
        'email.email'           => 'please_enter_vaild_email',
        'phone_code.require'    => 'please_select_phone_code',
        'phone.require'         => 'please_enter_vaild_phone',
        'phone.max'             => 'please_enter_vaild_phone',
        'phone.number'          => 'please_enter_vaild_phone',
        'code.require'          => 'please_enter_code',
        'method.require'        => 'param_error',
        'method.in'             => 'param_error',
    ];

    protected $scene = [
        'verify_old_phone' => ['code'],
        'update_phone' => ['phone_code', 'phone', 'code'],
        'verify_old_email' => ['code'],
        'update_email' => ['email', 'code'],
        'bind_totp' => ['code'],
        'unbind_totp' => ['method', 'code'],
    ];

}
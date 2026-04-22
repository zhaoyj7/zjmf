<?php
namespace app\admin\validate;

use app\common\model\NoticeSettingModel;
use think\Validate;

/**
 * 邮件模板验证
 */
class NoticeEmailValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'name'       => 'require|max:100',
        'subject'       => 'require|max:100',
        'message'       => 'require',
        'email'       => 'require|email',
        'notice_setting_name' 		=> 'checkNoticeSettingName:thinkphp',
    ];

    protected $message  =   [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'name.require'           => 'please_enter_email_name',
        'subject.require'           => 'please_enter_email_subject',
        'name.max'               => 'email_name_cannot_exceed_100_chars',
        'subject.max'               => 'email_subject_cannot_exceed_100_chars',
        'message.require'           => 'please_enter_email_message',
        'email.require'             => 'email_cannot_be_empty',
        'email.email'               => 'email_format_error',
    ];

    protected $scene = [
        'create' => ['name','subject', 'message', 'notice_setting_name'],
        'update' => ['id','name','subject', 'message', 'notice_setting_name'],
        'test' => ['id', 'email'],
    ];

    protected function checkNoticeSettingName($value)
    {
        if (empty($value)){
            return true;
        }
        $NoticeSettingModel = new NoticeSettingModel();
        $exist = $NoticeSettingModel->where('name', $value)->find();
        return $exist ? true : 'param_error';
    }
}
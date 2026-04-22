<?php

$domain = request()->domain();
return [
    # 短信/邮件模板初始化
    "renew_notice_template" => [
        'host_renew' => [
            'name_lang' => '产品续费',
            'type' => 'host',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '产品续费',
                'content' => '您购买的产品：【@var(product_name)】，现已续费成功,到期时间（【@var(product_due_time)】）'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '产品续费',
                'content' => '您购买的产品：【@var(product_name)】，现已续费成功,到期时间（【@var(product_due_time)】）'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '产品续费',
                'title' => '[{system_website_name}]产品续费成功',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_renew/config/email_template/host_renew.html')
            ],
        ],
        'host_auto_renew_fail' => [
            'name_lang' => '自动续费扣款失败提醒',
            'type' => 'host',
            'sms_name' => 'Idcsmart',
            'sms_template' => [
                'title' => '自动续费扣款失败提醒',
                'content' => '尊敬的用户，您的账户余额不足以支付自动续费账单，请及时充值，以免影响服务使用。'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '自动续费扣款失败提醒',
                'content' => '尊敬的用户，您的账户余额不足以支付自动续费账单，请及时充值，以免影响服务使用。'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '自动续费扣款失败提醒',
                'title' => '[{system_website_name}]自动续费扣款失败提醒',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_renew/config/email_template/host_auto_renew_fail.html')
            ],
        ],
    ],

];
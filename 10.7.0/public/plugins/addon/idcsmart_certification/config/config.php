<?php

$domain = request()->domain();
return [
    # 实名附件保存地址
    'certification_upload_url' => WEB_ROOT . 'plugins/addon/idcsmart_certification/upload/',
    # 实名附件访问地址
    'get_certification_upload_url' => $domain . '/plugins/addon/idcsmart_certification/upload/',

    'certification_notice_template' => [
    	'idcsmart_certification_pass' => [
			'name_lang' => '实名认证通过',
            'type' => 'client_account',
			'sms_name' => 'Idcsmart',           
			'sms_template' => [
				'title' => '实名认证通过',
				'content' => '恭喜！您的账户实名认证已通过。'
			],
			'sms_global_name' => 'Idcsmart',
			'sms_global_template' => [
				'title' => '实名认证通过',
				'content' => '恭喜！您的账户实名认证已通过。'
			],
			'email_name' => 'Smtp',
			'email_template' => [
				'name' => '实名认证通过',
				'title' => '实名认证通过',
				'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_certification/config/email_template/idcsmart_certification_pass.html')
			],	
		],
        'idcsmart_certification_reject' => [
            'name_lang' => '实名认证失败',
            'type' => 'client_account',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '实名认证失败',
                'content' => '尊敬的用户，您好！很抱歉，您的实名认证未能通过，请核对提交的信息并重新认证。@var(system_website_name)。'
            ],
            'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '实名认证失败',
                'content' => '尊敬的用户，您好！很抱歉，您的实名认证未能通过，请核对提交的信息并重新认证。@var(system_website_name)。'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '实名认证失败',
                'title' => '[{system_website_name}] 实名认证失败',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_certification/config/email_template/idcsmart_certification_reject.html')
            ],
            
        ],
    ],
    'certification_apply_notice_template' => [
    	'idcsmart_certification_apply_notice' => [
			'name_lang' => '实名认证审核通知',
            'type' => 'client_account',
			'sms_name' => 'Idcsmart',           
			'sms_template' => [
				'title' => '实名认证审核通知',
				'content' => '用户 @var(client_id) 提交了实名认证审核请求，请及时登录后台进行审核处理。'
			],
			'sms_global_name' => 'Idcsmart',
			'sms_global_template' => [
				'title' => '实名认证审核通知',
				'content' => '用户 @var(client_id) 提交了实名认证审核请求，请及时登录后台进行审核处理。'
			],
			'email_name' => 'Smtp',
			'email_template' => [
				'name' => '实名认证审核通知',
				'title' => '[{system_website_name}]实名认证审核通知',
				'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_certification/config/email_template/idcsmart_certification_apply_notice.html')
			],	
		],
    ]
];
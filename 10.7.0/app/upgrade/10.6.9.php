<?php
use think\facade\Db;

upgradeData1069();
function upgradeData1069()
{
	$sql = [
        "ALTER TABLE `idcsmart_product` ADD COLUMN `natural_month_prepaid` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '自然月预付费开关(0=关闭,1=开启)';",
        "ALTER TABLE `idcsmart_order` ADD COLUMN `unpaid_timeout` INT(11) NOT NULL DEFAULT 0 COMMENT '未支付超时时间戳，0表示不限制';",
        "ALTER TABLE `idcsmart_order` ADD INDEX `idx_unpaid_timeout` (`status`, `unpaid_timeout`)",
        "UPDATE `idcsmart_plugin` SET `version`='3.1.0' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.1.0' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='3.5.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='3.5.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.5.0' WHERE `name`='MfDcim';",
    ];

    $mfCloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfCloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_image` ADD COLUMN `is_market` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否镜像市场(0=普通镜像,1=镜像市场镜像)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `simulate_physical_machine_enable` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '模拟物理机运行(0=关闭,1=开启)';";
    }

    $idcsmartCommonServer = Db::name('server')->where('module', 'idcsmart_common')->find();
    if (!empty($idcsmartCommonServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_custom_cycle` ADD COLUMN `cycle_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '周期类型(0=普通周期,1=自然月周期)';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_custom_cycle` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态(0=禁用,1=启用)';";
    }

    $renewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
    if (!empty($renewPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_renew` ADD COLUMN `natural_month_due_time` INT(11) NOT NULL DEFAULT 0 COMMENT '自然月预付费精确到期时间';";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $idcsmartCertificationPlugin = Db::name('plugin')->where('name', 'IdcsmartCertification')->find();
    if (!empty($idcsmartCertificationPlugin)){
        notice_action_delete('idcsmart_certification_pass');
        notice_action_create([
            'name' => 'idcsmart_certification_pass',
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
                'content' => '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]实名认证通过</h2>
<br /><strong>尊敬的用户</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 55px;">您好！</span></div>
<div class="card">您的账户实名认证已通过<br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 60px;">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span><br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{send_time}</span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body> </html>'
            ],
        ]);

        notice_action_delete('idcsmart_certification_apply_notice');
        notice_action_create([
            'name' => 'idcsmart_certification_apply_notice',
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
                'content' => '<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>实名认证审核通知</title>
<style>
li{list-style: none;}
a{text-decoration: none;}
body{margin: 0;}
.box{ background-color: #EBEBEB; height: 100%; }
.logo_top {padding: 20px 0;}
.logo_top img{ display: block; width: auto; margin: 0 auto; }
.card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; }
.contimg{ text-align: center; }
button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; }
.lvst{ color: #57AC80; }
.banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; }
.banquan li span{ display: inline-block; padding: 0 8px; }
@media (max-width: 650px){
.card{ padding: 5% 5%; }
.logo_top img,.contimg img{width: 280px;}
.box{height: auto;}
.card{width: auto;}
}
@media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}}
</style>
</head>
<body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]实名认证审核通知</h2>
<br /><strong>尊敬的管理员：</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 55px;"> 用户{client_id} 提交了实名认证审核请求，请及时登录后台进行审核处理。 </span></div>
<div class="card"><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span><br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>
</html>',
            ],
        ]);
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.9' where `setting`='system_version';");

    // 清除缓存
    if (class_exists("\\app\\common\\logic\\CacheLogic")){
        \app\common\logic\CacheLogic::clearAllCache();
    }
}
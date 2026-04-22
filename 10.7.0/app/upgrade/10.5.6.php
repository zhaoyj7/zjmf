<?php
use think\facade\Db;

upgradeData1056();
function upgradeData1056()
{
    $sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_overdue_not_delete_open','0','商品到期后不自动删除(0=关闭1=开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_overdue_not_delete_product_ids','','到期后不自动删除的商品ID');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('clientarea_logo_url','','会员中心LOGO跳转地址');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('clientarea_logo_url_blank','0','会员中心LOGO跳转是否打开新页面(0=否1=是)');",
        "ALTER TABLE `idcsmart_host_ip` CHANGE COLUMN `assign_ip` `assign_ip` longtext NOT NULL COMMENT '附加IP';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('supplier_credit_warning_notice', '0', 0, 0, '代理商余额预警(0=关闭,1=开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('supplier_credit_amount', '', 0, 0, '代理商自定义余额大小');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('supplier_credit_push_frequency', '1', 0, 0, '代理商余额预警推送频率');",
        "ALTER TABLE `idcsmart_supplier` ADD COLUMN `credit` varchar(20) NOT NULL DEFAULT '' COMMENT '上游账户余额';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_renew_with_new_open','0','商品续费时重新计算续费金额(0关闭，1开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_renew_with_new_product_ids','','所选商品ID');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('host_sync_due_time_open','0','产品到期时间与上游一致(0关闭，1开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('host_sync_due_time_apply_range','0','产品到期时间与上游一致应用范围(0全部上游商品，1自定义上游商品)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('host_sync_due_time_product_ids','','产品到期时间与上游一致的商品ID');",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='TpCaptcha';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='MfDcim';",
    ];
    $PluginModel = new \app\admin\model\PluginModel();
    $plugin = $PluginModel->where('name', 'IdcsmartRenew')->where('module', 'addon')->find();
    if (!empty($plugin)){
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('get_auto_renew_host_id', {$plugin['status']}, 'IdcsmartRenew', 'addon', 0);";
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('task_run', {$plugin['status']}, 'IdcsmartRenew', 'addon', 0);";
        $sql[] = "UPDATE `idcsmart_plugin_hook` SET `name`='minute_cron' WHERE `plugin`='IdcsmartRenew' AND `name`='before_host_renewal_first';";
    }

    # 云接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_duration` ADD COLUMN `support_apply_for_suspend` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '周期是否支持申请停用(0=否,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `migrate_task_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '魔方云迁移任务ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` ADD COLUMN `firewall_type` varchar(100) NOT NULL DEFAULT '' COMMENT '防火墙类型';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` ADD COLUMN `defence_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '防御规则ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `sync_firewall_rule` tinyint(3) NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `order_default_defence` varchar(100) NOT NULL DEFAULT '' COMMENT '订购默认防御';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `sync_firewall_rule` tinyint(3) NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `order_default_defence` varchar(100) NOT NULL DEFAULT '' COMMENT '订购默认防御';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `due_not_free_gpu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '计费到期不自动释放GPU(0=否,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` MODIFY COLUMN `value` varchar(100) NOT NULL DEFAULT '' COMMENT '单选值';";
        $sql[] = "CREATE TABLE `idcsmart_module_mf_cloud_ip_defence` (
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `defence` varchar(50) NOT NULL DEFAULT '' COMMENT '防御',
  KEY `ip` (`ip`),
  KEY `defence` (`defence`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '云IP防御表';";
    }
    # DCIM接口
    $cloudServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_option` ADD COLUMN `firewall_type` varchar(100) NOT NULL DEFAULT '' COMMENT '防火墙类型';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_option` ADD COLUMN `defence_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '防御规则ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `sync_firewall_rule` tinyint(3) NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `order_default_defence` varchar(255) NOT NULL DEFAULT '' COMMENT '订购默认防御';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `sync_firewall_rule` tinyint(3) NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `order_default_defence` varchar(255) NOT NULL DEFAULT '' COMMENT '订购默认防御';";
        $sql[] = "CREATE TABLE `idcsmart_module_mf_dcim_ip_defence` (
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `defence` varchar(50) NOT NULL DEFAULT '' COMMENT '防御',
  KEY `ip` (`ip`),
  KEY `defence` (`defence`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'DCIM IP防御表';";
    }

    // 增加模板
    notice_action_create([
        'name' => 'supplier_credit_warning_notice',
        'name_lang' => '账户余额不足通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '账户余额不足通知',
            'content' => '尊敬的用户，您的@var(supplier)账户余额不足，为确保服务正常使用，请及时充值。详情请访问@var(supplier_url)。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '账户余额不足通知',
            'content' => '尊敬的用户，您的@var(supplier)账户余额不足，为确保服务正常使用，请及时充值。详情请访问@var(supplier_url)。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '账户余额不足通知',
            'title' => '{supplier}账户余额不足通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>账户余额不足通知</title>
    <style>
        li {
            list-style: none;
        }

        a {
            text-decoration: none;
        }

        body {
            margin: 0;
        }

        .box {
            background-color: #EBEBEB;
            height: 100%;
        }

        .card {
            width: 650px;
            margin: 0 auto;
            background-color: white;
            font-size: 0.8rem;
            line-height: 22px;
            padding: 40px 50px;
            box-sizing: border-box;
        }

        .contimg {
            text-align: center;
        }

        button {
            background-color: #F75697;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            color: white;
            border: 0;
        }

        .lvst {
            color: #57AC80;
        }

        .banquan {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            color: #B7B8B9;
            font-size: 0.4rem;
            padding: 20px 0;
            margin: 0;
            padding-left: 0;
        }

        .banquan li span {
            display: inline-block;
            padding: 0 8px;
        }

        @media (max-width: 650px) {
            .card {
                padding: 5% 5%;
            }

            .box {
                height: auto;
            }

            .card {
                width: auto;
            }
        }

        @media (max-width: 280px) {
            .contimg img {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="box">
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]上游账户余额不足通知</h2>
<br /><strong>尊敬的用户：</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的 <strong>{supplier}</strong> 账户余额不足，为确保服务正常使用，请及时充值。详情请访问 <strong>{supplier_url}</strong>。</span><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    // 增加模板
    notice_action_create([
        'name' => 'idcsmart_certification_reject',
        'name_lang' => '实名认证失败',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '实名认证失败',
            'content' => '尊敬的用户，您好！很抱歉，您的实名认证未能通过，请核对提交的信息并重新认证。@var(system_website_name)。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '实名认证失败',
            'content' => '尊敬的用户，您好！很抱歉，您的实名认证未能通过，请核对提交的信息并重新认证。@var(system_website_name)。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '实名认证失败',
            'title' => '[{system_website_name}] 实名认证失败',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        li {
            list-style: none;
        }

        a {
            text-decoration: none;
        }

        body {
            margin: 0;
        }

        .box {
            background-color: #EBEBEB;
            height: 100%;
        }

        .logo_top {
            padding: 20px 0;
        }

        .logo_top img {
            display: block;
            width: auto;
            margin: 0 auto;
        }

        .card {
            width: 650px;
            margin: 0 auto;
            background-color: white;
            font-size: 0.8rem;
            line-height: 22px;
            padding: 40px 50px;
            box-sizing: border-box;
        }

        .contimg {
            text-align: center;
        }

        button {
            background-color: #F75697;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            color: white;
            border: 0;
        }

        .lvst {
            color: #57AC80;
        }

        .banquan {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            color: #B7B8B9;
            font-size: 0.4rem;
            padding: 20px 0;
            margin: 0;
            padding-left: 0;
        }

        .banquan li span {
            display: inline-block;
            padding: 0 8px;
        }

        @media (max-width: 650px) {
            .card {
                padding: 5% 5%;
            }

            .logo_top img,
            .contimg img {
                width: 280px;
            }

            .box {
                height: auto;
            }

            .card {
                width: auto;
            }
        }

        @media (max-width: 280px) {
            .logo_top img,
            .contimg img {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}] 实名认证失败通知</h2>
<br /><strong>尊敬的用户，您好！</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 很抱歉，您的实名认证未能通过，请核对提交的信息并重新认证。 </span> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 60px;"> 感谢您的理解与支持！ </span> <br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'order_review_pass',
        'name_lang' => '订单支付成功通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '订单支付成功通知',
            'content' => '尊敬的用户，您的订单（订单编号：@var (order_id)）已支付成功。感谢您的支持！@var (system_website_name)',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '订单支付成功通知',
            'content' => '尊敬的用户，您的订单（订单编号：@var (order_id)）已支付成功。感谢您的支持！@var (system_website_name)',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '订单支付成功通知',
            'title' => '[{system_website_name}] 订单支付成功通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        li {
            list-style: none;
        }

        a {
            text-decoration: none;
        }

        body {
            margin: 0;
        }

        .box {
            background-color: #EBEBEB;
            height: 100%;
        }

        .logo_top {
            padding: 20px 0;
        }

        .logo_top img {
            display: block;
            width: auto;
            margin: 0 auto;
        }

        .card {
            width: 650px;
            margin: 0 auto;
            background-color: white;
            font-size: 0.8rem;
            line-height: 22px;
            padding: 40px 50px;
            box-sizing: border-box;
        }

        .contimg {
            text-align: center;
        }

        button {
            background-color: #F75697;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            color: white;
            border: 0;
        }

        .lvst {
            color: #57AC80;
        }

        .banquan {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            color: #B7B8B9;
            font-size: 0.4rem;
            padding: 20px 0;
            margin: 0;
            padding-left: 0;
        }

        .banquan li span {
            display: inline-block;
            padding: 0 8px;
        }

        @media (max-width: 650px) {
            .card {
                padding: 5% 5%;
            }

            .logo_top img,
            .contimg img {
                width: 280px;
            }

            .box {
                height: auto;
            }

            .card {
                width: auto;
            }
        }

        @media (max-width: 280px) {
            .logo_top img,
            .contimg img {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}] 订单支付成功通知</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的订单（订单编号：{order_id}）已支付成功。 </span> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 60px;"> 感谢您的支持！ </span> <br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'order_review_reject',
        'name_lang' => '支付未通过审核通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '支付未通过审核通知',
            'content' => '尊敬的用户，您好！ 很抱歉，您的订单（订单编号：@var (order_id)）支付信息未通过审核。请核对信息后重新发起审核。@var (system_website_name)',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '支付未通过审核通知',
            'content' => '尊敬的用户，您好！ 很抱歉，您的订单（订单编号：@var (order_id)）支付信息未通过审核。请核对信息后重新发起审核。@var (system_website_name)',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '支付未通过审核通知',
            'title' => '[{system_website_name}] 支付未通过审核通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        li {
            list-style: none;
        }

        a {
            text-decoration: none;
        }

        body {
            margin: 0;
        }

        .box {
            background-color: #EBEBEB;
            height: 100%;
        }

        .logo_top {
            padding: 20px 0;
        }

        .logo_top img {
            display: block;
            width: auto;
            margin: 0 auto;
        }

        .card {
            width: 650px;
            margin: 0 auto;
            background-color: white;
            font-size: 0.8rem;
            line-height: 22px;
            padding: 40px 50px;
            box-sizing: border-box;
        }

        .contimg {
            text-align: center;
        }

        button {
            background-color: #F75697;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            color: white;
            border: 0;
        }

        .lvst {
            color: #57AC80;
        }

        .banquan {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            color: #B7B8B9;
            font-size: 0.4rem;
            padding: 20px 0;
            margin: 0;
            padding-left: 0;
        }

        .banquan li span {
            display: inline-block;
            padding: 0 8px;
        }

        @media (max-width: 650px) {
            .card {
                padding: 5% 5%;
            }

            .logo_top img,
            .contimg img {
                width: 280px;
            }

            .box {
                height: auto;
            }

            .card {
                width: auto;
            }
        }

        @media (max-width: 280px) {
            .logo_top img,
            .contimg img {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}] 支付未通过审核通知</h2>
<br /><strong>尊敬的用户，您好！</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 很抱歉，您的订单（订单编号：{order_id}）支付信息未通过审核。 </span> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 请核对信息后重新发起审核。 </span> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 60px;"> 感谢您的支持！ </span> <br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.6' where `setting`='system_version';");
}
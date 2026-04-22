<?php
use think\facade\Db;

upgradeData1058();
function upgradeData1058()
{
    $sql = [
        "UPDATE `idcsmart_notice_setting` SET `name_lang`='线下支付审核成功通知' WHERE `name`='order_review_pass';",
        "ALTER TABLE `idcsmart_transaction` ADD COLUMN `notes` VARCHAR(2000) NOT NULL DEFAULT '' COMMENT '备注';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('login_email_password','1','是否开启邮箱密码登录:1开启0关闭');",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `is_sub` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否子产品，1是，0否默认';",
        "CREATE TABLE `idcsmart_product_notice_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '通知类型(email=邮件,sms=短信)',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否默认规则(0=否,1=是)',
  `notice_setting` text NOT NULL COMMENT '通知动作json',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品全局通知动作组表';",
        "CREATE TABLE `idcsmart_product_notice_group_product` (
  `product_notice_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品通知组ID',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '通知类型(email=邮箱,sms=短信)',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  KEY `product_notice_group_id` (`product_notice_group_id`),
  KEY `product_id_type` (`product_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品通知组商品ID';",
        "ALTER TABLE `idcsmart_notice_setting` ADD COLUMN `type` varchar(100) NOT NULL DEFAULT '' COMMENT '通知分类';",
        "UPDATE `idcsmart_notice_setting` SET `type`='client_account' WHERE `name` IN ('code','client_login_success','client_register_success','client_change_phone','client_change_email','client_change_password','idcsmart_certification_pass','idcsmart_certification_reject');",
        "UPDATE `idcsmart_notice_setting` SET `type`='order_pay' WHERE `name` IN ('order_create','order_overdue','admin_order_amount','order_pay','order_recharge','aliyun_credit_too_low','aliyun_credit_suspend','aliyun_credit_unsuspend','room_box_after_payment_deposit','room_box_reminder_final_payment','cash_back_to_account_notice','voucher_use_notice','cash_withdrawal_notice','offline_payment_application','order_review_pass','order_review_reject','sale_order_notice');",
        "UPDATE `idcsmart_notice_setting` SET `type`='host' WHERE `name` IN ('host_pending','host_active','host_suspend','host_unsuspend','host_terminate','host_upgrad','host_renew','host_renewal_first','host_renewal_second','host_overdue_first','host_overdue_second','host_overdue_third','client_create_refund','client_refund_success','admin_refund_reject','client_refund_cancel','host_transfer_out_expired','host_transfer_out_fail','host_transfer_out_return','host_transfer_out_cancel','host_transfer_wait_accept','host_transfer_in_cancel','host_transfer_in_accept','host_transfer_out_accept','huawei_rds_backup_not_enough','idcsmart_app_market_client_buy_product','client_domain_expire','manual_resource_operate_finish','manual_resource_operate_cancel');",
        "UPDATE `idcsmart_notice_setting` SET `type`='credit_limit' WHERE `name` IN ('credit_limit_change_notice','credit_limit_expired_notice','credit_limit_overdue_notice','credit_limit_pay_off_notice','credit_limit_repayment_notice','credit_limit_suspend_notice');",
        "UPDATE `idcsmart_notice_setting` SET `type`='maintenance' WHERE `name` IN ('admin_create_account','host_failed_action','supplier_credit_warning_notice','oss_exception_notice','host_module_action','updownstream_action_failed_notice','task_queue_exception','aodun_firewall_alters_notice','aodun_firewall_agent_alters_notice','stock_control_alert_more_than_max','stock_control_alert_less_than_min');",
        "UPDATE `idcsmart_notice_setting` SET `type`='ticket' WHERE `name` IN ('client_create_ticket_premium','client_close_ticket_premium','admin_reply_ticket_premium','client_reply_ticket_premium','client_create_ticket','client_close_ticket','admin_reply_ticket','client_reply_ticket');",
        "UPDATE `idcsmart_notice_setting` SET `type`='finance' WHERE `name` IN ('invoice_issue_notice','apply_invoice_notice','invoice_drawer_bind_company','invoice_addon_create_verify','invoice_addon_create_success','invoice_reject_notice');",
        "UPDATE `idcsmart_notice_setting` SET `type`='other' WHERE `name` IN ('room_box_distribution_information_is_not_perfect','room_box_delivery_logistics','idcsmart_app_market_complaint_developer','recommend_notice');",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='Idcsmartali';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.4' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.6' WHERE `name`='MfDcim';",
    ];

    // 处理当前通知组数据
    $product = Db::name('product')->field('id,email_notice_setting,sms_notice_setting')->select();

    $defaultGroup = '{"host_pending":0,"host_active":0,"host_suspend":0,"host_unsuspend":0,"host_terminate":0,"host_upgrad":0,"host_module_action":0,"client_create_refund":0,"client_refund_success":0,"admin_refund_reject":0,"client_refund_cancel":0}';
    $defaultGroupMd5 = md5($defaultGroup);
    
    $noticeType = ['email','sms'];
    
    // 添加默认分组
    $time = time();
    $groupId = 1;
    $groupMd5Link = [];
    foreach($noticeType as $type){
        $sql[] = "INSERT INTO `idcsmart_product_notice_group` (`id`,`name`,`type`,`is_default`,`notice_setting`,`create_time`) VALUES ({$groupId},'默认分组','{$type}',1,'{$defaultGroup}',{$time});";
        $groupMd5Link[ $type ][ $defaultGroupMd5 ] = $groupId;
        $groupId++;
    }
    
    $emailIndex = 2;
    $smsIndex = 2;

    $productNoticeGroupProductInsert = [];
    foreach($product as $v){
        // 处理邮件
        $md5 = md5($v['email_notice_setting']);
        if(!isset($groupMd5Link[ 'email' ][ $md5 ])){
            $sql[] = "INSERT INTO `idcsmart_product_notice_group` (`id`,`name`,`type`,`is_default`,`notice_setting`,`create_time`) VALUES ({$groupId},'分组{$emailIndex}','email',0,'{$v['email_notice_setting']}',{$time});";
            $groupMd5Link[ 'email' ][ $md5 ] = $groupId;
            $emailIndex++;
            $groupId++;
        }
        $productNoticeGroupProductInsert[] = "({$groupMd5Link[ 'email' ][ $md5 ]}, 'email', {$v['id']})";
        // 处理短信
        $md5 = md5($v['sms_notice_setting']);
        if(!isset($groupMd5Link[ 'sms' ][ $md5 ])){
            $sql[] = "INSERT INTO `idcsmart_product_notice_group` (`id`,`name`,`type`,`is_default`,`notice_setting`,`create_time`) VALUES ({$groupId},'分组{$smsIndex}','sms',0,'{$v['sms_notice_setting']}',{$time});";
            $groupMd5Link[ 'sms' ][ $md5 ] = $groupId;
            $smsIndex++;
            $groupId++;
        }
        $productNoticeGroupProductInsert[] = "({$groupMd5Link[ 'sms' ][ $md5 ]}, 'sms', {$v['id']})";
    }
    $total = ceil(count($productNoticeGroupProductInsert)/500);
    for($i = 1; $i <= $total; $i++){
        $arr = array_slice($productNoticeGroupProductInsert, ($i-1)*500, 500);
        $sql[] = "INSERT INTO `idcsmart_product_notice_group_product` (`product_notice_group_id`,`type`,`product_id`) VALUES " . implode(',', $arr) . ';';
    }

    # DCIM接口
    $cloudServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_host_link` ADD COLUMN `parent_host_id` INT(11) NOT NULL DEFAULT 0 COMMENT '主产品ID';";
    }

    # 魔方云接口
    $mfCloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfCloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `parent_host_id` INT(11) NOT NULL DEFAULT 0 COMMENT '主产品ID';";
    }

    // 增加产品列表权限
    $authId = Db::name('auth')->where('title', 'auth_business_host_view')->value('id');
    $authRuleId = Db::name('auth_rule')->where('name', 'app\admin\controller\ModuleController::moduleList')->value('id');
    if(!empty($authId) && !empty($authRuleId)){
        $authRuleLink = Db::name('auth_rule_link')->where('auth_id', $authId)->where('auth_rule_id', $authRuleId)->find();
        if(empty($authRuleLink)){
            Db::name('auth_rule_link')->insert([
                'auth_id'       => $authId,
                'auth_rule_id'  => $authRuleId,
            ]);
        }
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    // 增加模板
    notice_action_create([
        'name' => 'cash_withdrawal_notice',
        'name_lang' => '提现打款',
        'type' => 'order_pay',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '提现打款',
            'content' => '您有一笔【@var(withdraw_amount)】的提现已打款，请及时查收。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '提现打款',
            'content' => '您有一笔【@var(withdraw_amount)】的提现已打款，请及时查收。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '提现打款',
            'title' => '[{system_website_name}]提现打款',
            'content' => '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]提现打款</h2>
<br /><strong>尊敬的用户</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 55px;">您好！</span></div>
<div class="card">您有一笔【{withdraw_amount}】的提现已打款，请及时查收。<br /><br />&nbsp; <span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span><br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{send_time}</span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body> </html>',
        ],
    ]);

    notice_action_create([
        'name' => 'disable_client',
        'name_lang' => '账户封禁通知',
        'type' => 'client_account',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '账户封禁通知',
            'content' => '您的账号@var(account)已被封禁，如有疑问请联系客服',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '账户封禁通知',
            'content' => '您的账号@var(account)已被封禁，如有疑问请联系客服',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '账户封禁通知',
            'title' => '[{system_website_name}]账户封禁通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>账户封禁通知</title>
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
<div class="logo_top"><img src="{system_logo_url}" alt="系统Logo" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}] 账户封禁通知</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">很抱歉，您的账号 {account} 已被封禁。</span><br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 如果您对此存有疑问或认为这是错误，请及时联系客服处理。</span><br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    Db::execute("update `idcsmart_configuration` set `value`='10.5.8' where `setting`='system_version';");
}
<?php
use think\facade\Db;

upgradeData1059();
function upgradeData1059()
{
    $sql = [
        "CREATE TABLE `idcsmart_product_on_demand` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `billing_cycle_unit` varchar(20) NOT NULL DEFAULT '' COMMENT '出账周期单位(hour=每小时,day=每天,month=每月)',
  `billing_cycle_day` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '出账周期号数，每月有效',
  `billing_cycle_point` varchar(10) NOT NULL DEFAULT '' COMMENT '出账周期时间点, 每天/每月生效',
  `duration_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期ID',
  `duration_ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期比例',
  `billing_granularity` varchar(20) NOT NULL DEFAULT 'minute' COMMENT '计费粒度(minute=每分钟)',
  `min_credit` varchar(20) NOT NULL DEFAULT '' COMMENT '购买时最低余额',
  `min_usage_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最低使用时长',
  `min_usage_time_unit` varchar(20) NOT NULL DEFAULT '' COMMENT '最低使用时长单位(second=秒,minute=分,hour=小时)',
  `upgrade_min_billing_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '升降级最低计费时长',
  `upgrade_min_billing_time_unit` varchar(20) NOT NULL DEFAULT '' COMMENT '升降级最低计费时长单位(second=秒,minute=分,hour=小时)',
  `grace_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '宽限期',
  `grace_time_unit` varchar(20) NOT NULL DEFAULT '' COMMENT '宽限期单位(hour=小时,day=天)',
  `keep_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '保留期',
  `keep_time_unit` varchar(20) NOT NULL DEFAULT '' COMMENT '保留期单位(hour=小时,day=天)',
  `keep_time_billing_item` varchar(3000) NOT NULL DEFAULT '' COMMENT 'json,保存模块提供项目标识',
  `initial_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '初装费',
  `client_auto_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '允许用户设置自动释放',
  `on_demand_to_duration` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '允许按需转包年包月',
  `duration_to_on_demand` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '允许包年包月/试用转按需',
  `credit_limit_pay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '允许信用额支付',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品按需配置表';",
        "CREATE TABLE `idcsmart_change_billing_cycle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `old_billing_cycle` varchar(50) NOT NULL DEFAULT '' COMMENT '原计费方式,试用传试用标识',
  `new_billing_cycle` varchar(50) NOT NULL DEFAULT '' COMMENT '新计费方式',
  `host_data` varchar(3000) NOT NULL DEFAULT '' COMMENT 'json,变更后的产品信息',
  `data` varchar(3000) NOT NULL DEFAULT '' COMMENT 'json,变更参数,用于模块执行',
  `status` varchar(50) NOT NULL DEFAULT 'Pending' COMMENT '状态(未支付/已申请,Pending=等待执行,Completed=执行完成,Cancel=已取消))',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `host_id` (`host_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='变更计费方式表';",
        "CREATE TABLE `idcsmart_on_demand_payment_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '出账类型(cron=定时出账,on_demand_recurring_prepayment=按需转包年包月,upgrade=升降级,terminate=停用)',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出账开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出账结束时间',
  `billing_time` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '折算出账时间(小时)',
  `status` varchar(50) NOT NULL DEFAULT '' COMMENT '状态(wait=等待出账,exec=出账中,complete=出账完成,fail=出账失败)',
  `error_msg` varchar(3000) NOT NULL DEFAULT '' COMMENT '错误原因',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `data` varchar(3000) NOT NULL DEFAULT '' COMMENT 'json字段用来存入价格收费相关东西',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品出账队列表';",
        "CREATE TABLE `idcsmart_client_traffic_warning` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '模块名称',
  `warning_switch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '预警开关(0=关闭,1=开启)',
  `leave_percent` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余百分比',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户流量预警设置表';",
        "CREATE TABLE `idcsmart_host_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `traffic_limit_exceed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '流量是否超额(0=否,1=是)',
  `traffic_enough` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '流量是否足够(0=否,1=是)',
  `traffic_limit_exceed_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次超额时间',
  `traffic_not_enough_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次流量不足时间',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品特殊通知动作触发记录表';",

        "ALTER TABLE `idcsmart_host` MODIFY COLUMN `renew_amount` decimal(12, 4) NOT NULL DEFAULT '0.0000' COMMENT '续费金额';",
        "ALTER TABLE `idcsmart_host` MODIFY COLUMN `base_renew_amount` decimal(12, 4) NOT NULL DEFAULT '0.0000' COMMENT '基础续费金额';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `keep_time_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '保留期价格';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `on_demand_flow_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需流量/GB价格';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `on_demand_billing_cycle_unit` varchar(20) NOT NULL DEFAULT 'hour' COMMENT '出账周期单位(hour=每小时,day=每天,month=每月)';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `on_demand_billing_cycle_day` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '出账周期号数，每月有效';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `on_demand_billing_cycle_point` varchar(10) NOT NULL DEFAULT '00:00' COMMENT '出账周期时间点, 每天/每月生效';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `auto_release_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '自动释放时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `next_payment_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '按需下次出账时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `start_billing_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始计费时间,出账后更新时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `start_grace_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始宽限期时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `end_grace_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束宽限期时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `start_keep_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始保留期时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `end_keep_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束保留期时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `change_billing_cycle_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '变更计费方式表ID,有代表申请了包年包月转按需';",
        "ALTER TABLE `idcsmart_upgrade` MODIFY COLUMN `renew_price` decimal(12, 4) NOT NULL DEFAULT '0.0000' COMMENT '续费价格';",
        "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `base_renew_price` decimal(12, 4) NOT NULL DEFAULT '0.0000' COMMENT '基础续费价格';",
        "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `on_demand_flow_price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '流量按需价格,-1代表不变更';",
        "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `settle_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '按需结算时间';",
        "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `keep_time_price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '保留期价格,-1代表不变更';",
        "UPDATE `idcsmart_notice_setting` SET `name_lang`='退款申请' WHERE `name`='client_create_refund';",
        "UPDATE `idcsmart_notice_setting` SET `name_lang`='退款成功' WHERE `name`='client_refund_success';",
        "UPDATE `idcsmart_notice_setting` SET `name_lang`='退款驳回' WHERE `name`='admin_refund_reject';",
        "UPDATE `idcsmart_notice_setting` SET `name_lang`='取消请求' WHERE `name`='client_refund_cancel';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('grace_time', '', 0, 0, '宽限期');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('grace_time_unit', 'hour', 0, 0, '宽限期单位(hour=小时,day=天)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('keep_time', '', 0, 0, '保留期');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('keep_time_unit', 'hour', 0, 0, '保留期单位(hour=小时,day=天)');",
        "DELETE FROM `idcsmart_plugin_hook` WHERE `plugin`='IdcsmartOrderCombine' AND `name`='order_paid';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('on_demand_cron_end_time', '0', 0, 0, '按需出账任务最后执行时间');",

        // 后台访问设置
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_second_verify', '0', 0, 0, '二次验证:1开启0关闭');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_second_verify_method_default', '', 0, 0, '首选二次验证方式:sms短信email邮件totp');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('prohibit_admin_bind_phone', '0', 0, 0, '禁止后台用户自助绑定手机号:1是0否');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('prohibit_admin_bind_email', '0', 0, 0, '禁止后台用户自助绑定邮箱:1是0否');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_password_or_verify_code_retry_times', '', 0, 0, '密码或验证码重试次数');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_frozen_time', '', 0, 0, '冻结时间,分钟');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_login_expire_time', '', 0, 0, '登录有效期,分钟');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('login_phone_password', '', 0, 0, '是否开启手机密码登录:1开启0关闭');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_login_expire_time', '', 0, 0, '登录有效期,分钟');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('first_login_type', 'account_login', 0, 0, '首选登录方式(account_login=账户凭证登录)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('captcha_admin_login_error', '0', 0, 0, '管理员登录失败图形验证码开关:1开启0关闭');",
        "ALTER TABLE `idcsmart_admin` ADD COLUMN `totp_secret` varchar(20) NOT NULL DEFAULT '' COMMENT 'TOTP密钥';",
        "ALTER TABLE `idcsmart_admin` ADD COLUMN `totp_bind` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'TOTP绑定0:未绑定1:已绑定';",
        "ALTER TABLE `idcsmart_admin` ADD COLUMN `lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '锁定0=否1=是';",
        "ALTER TABLE `idcsmart_admin` ADD COLUMN `lock_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '锁定到期时间';",

        // 自定义到期时间
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('auto_renew_in_advance', '0', 0, 0, '自动续费提前开关(0=关闭1=开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('auto_renew_in_advance_num', '5', 0, 0, '自动续费提前时间数');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('auto_renew_in_advance_unit', 'minute', 0, 0, '自动续费提前时间单位(minute=分钟,hour=小时,day=天)');",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `auto_renew_in_advance` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '自动续费提前开关(0=关闭1=开启)';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `auto_renew_in_advance_num` int(10) unsigned NOT NULL DEFAULT '5' COMMENT '自动续费提前时间数';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `auto_renew_in_advance_unit` varchar(10) NOT NULL DEFAULT 'minute' COMMENT '自动续费提前时间单位(minute=分钟,hour=小时,day=天)';",

        // 冻结余额
        "ALTER TABLE `idcsmart_client_credit` ADD COLUMN `rel_id` INT(11) NOT NULL DEFAULT '0' COMMENT '冻结余额关联ID';",
        "ALTER TABLE `idcsmart_client_credit` ADD INDEX rel_id (`rel_id`);",
        "ALTER TABLE `idcsmart_client_credit` ADD COLUMN `client_notes` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '前台备注';",
        "ALTER TABLE `idcsmart_client_credit` ADD COLUMN `is_unfreeze` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否已解冻，1是，0否默认';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `freeze_credit` DECIMAL(12,2) NOT NULL DEFAULT '0.00' COMMENT '冻结的余额';",

        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.4' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.8' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.6' WHERE `name`='MfCloud';",
    ];

    # DCIM接口
    $cloudServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `custom_rand_password_rule` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '自定义随机密码位数(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `default_password_length` int(11) unsigned NOT NULL DEFAULT '12' COMMENT '默认密码长度';";
    }

    # 魔方云接口
    $mfCloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfCloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` ADD COLUMN `on_demand_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需计费价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `on_demand_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需计费价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `on_demand_flow_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '流量按需计费价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_backup_config` ADD COLUMN `on_demand_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需计费价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `custom_rand_password_rule` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '自定义随机密码位数(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `default_password_length` int(11) unsigned NOT NULL DEFAULT '12' COMMENT '默认密码长度';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `support_on_demand` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '线路是否支持按需';";

        // 处理异常数据
        $option = Db::name('module_mf_cloud_option')
                ->field('id,other_config')
                ->where('rel_type', 'IN', [3,12])
                ->select();
        foreach($option as $v){
            $v['other_config'] = json_decode($v['other_config'], true);
            if(!isset($v['other_config']['in_bw'])){
                continue;
            }
            $v['other_config']['in_bw'] = (int)$v['other_config']['in_bw'];
            $v['other_config']['out_bw'] = (int)$v['other_config']['out_bw'];
            $v['other_config']['traffic_type'] = (int)$v['other_config']['traffic_type'];
            $v['other_config'] = json_encode($v['other_config']);

            $sql[] = "UPDATE `idcsmart_module_mf_cloud_option` SET `other_config`='{$v['other_config']}' WHERE `id`='{$v['id']}';";
        }
    }

    // 是否有续费插件
    $idcsmartRenewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
    if (!empty($idcsmartRenewPlugin)){
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('after_host_edit', {$idcsmartRenewPlugin['status']}, 'IdcsmartRenew', 'addon', 0);";
    }
    // 是否有优惠码插件
    $promoCodePlugin = Db::name('plugin')->where('name', 'PromoCode')->find();
    if (!empty($promoCodePlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_promo_code` ADD COLUMN `on_demand_to_recurring_prepayment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '按需转包年包月优惠(0=关闭,1=开启)';";
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    // 增加模板
    notice_action_create([
        'name' => 'client_credit_not_enough',
        'name_lang' => '余额不足提醒',
        'type' => 'client_account',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '余额不足提醒',
            'content' => '尊敬的用户，您的账户余额可能不足以支付下次出账，请及时充值，以免影响服务使用。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '余额不足提醒',
            'content' => '尊敬的用户，您的账户余额可能不足以支付下次出账，请及时充值，以免影响服务使用。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '余额不足提醒',
            'title' => '[{system_website_name}]余额不足提醒',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>余额不足提醒</title>
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
<h2 style="text-align: center;">[{system_website_name}] 余额不足提醒</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的账户余额可能不足以支付下次出账，请及时充值以避免服务中断。 </span> <br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'on_demand_order_auto_pay_fail',
        'name_lang' => '扣款失败提醒',
        'type' => 'order_pay',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '扣款失败提醒',
            'content' => '尊敬的用户，您的账户余额不足以支付本次按需账单，请及时充值，以免影响服务使用。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '扣款失败提醒',
            'content' => '尊敬的用户，您的账户余额不足以支付本次按需账单，请及时充值，以免影响服务使用。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '扣款失败提醒',
            'title' => '[{system_website_name}]扣款失败提醒',
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
<h2 style="text-align: center;">[{system_website_name}] 扣款失败提醒</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">因您的账户余额不足，按需账单{order_id}自动扣款失败。<br /><br />如未及时支付，您的账户将被限制新购与续费权限，并在{retention_period}后删除相关产品。<br /><br />请及时充值并前往账单页面完成支付，以避免服务中断。<br /></span><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'system_traffic_not_enough',
        'name_lang' => '流量不足提醒',
        'type' => 'host',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '流量不足提醒',
            'content' => '尊敬的用户，您的@var(product_name)剩余流量为@var(RemainingTraffic)，已低于提醒值@var(AlertThreshold)。请及时处理以免影响正常使用。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '流量不足提醒',
            'content' => '尊敬的用户，您的@var(product_name)剩余流量为@var(RemainingTraffic)，已低于提醒值@var(AlertThreshold)。请及时处理以免影响正常使用。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '流量不足提醒',
            'title' => '[{system_website_name}]流量不足提醒',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>流量不足提醒</title>
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
<h2 style="text-align: center;">[{system_website_name}] 流量不足提醒</h2>
<br /><strong>尊敬的用户，</strong><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">您的<span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> <strong>{product_name}</strong></span>剩余流量为 <strong>{RemainingTraffic}</strong>，已低于提醒值<strong>{AlertThreshold}</strong>。<br /></span><br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'system_traffic_limit_exceed',
        'name_lang' => '流量超限通知',
        'type' => 'host',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '流量超限通知',
            'content' => '尊敬的用户，您的@var(product_name)流量已用完，请及时购买流量包以避免服务中断。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '流量超限通知',
            'content' => '尊敬的用户，您的@var(product_name)流量已用完，请及时购买流量包以避免服务中断。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '流量超限通知',
            'title' => '[{system_website_name}]流量超限通知',
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
<h2 style="text-align: center;">[{system_website_name}] 流量超限通知</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">您的<span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"><strong>{product_name}</strong></span></span>流量已用完，请及时购买流量包避免服务中断。<br /><br /></span><br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'client_credit_freeze',
        'name_lang' => '账号余额冻结通知',
        'type' => 'client_account',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '账号余额冻结通知',
            'content' => '尊敬的用户，您的@var(client_username)账户有一笔余额被冻结，冻结金额为@var(frozen_amount)。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '账号余额冻结通知',
            'content' => '尊敬的用户，您的@var(client_username)账户有一笔余额被冻结，冻结金额为@var(frozen_amount)。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '账户余额冻结通知',
            'title' => '[{system_website_name}] 账户余额冻结通知',
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
<h2 style="text-align: center;">[{system_website_name}] 账户余额冻结通知</h2>
<br /><strong>尊敬的用户，</strong><br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的 <span style="margin: 0; padding: 0; display: inline-block;"><strong>{client_username}</strong></span> 账户有一笔余额被冻结，冻结金额为{frozen_amount}。 <br /><br />冻结原因：{frozen_reason}。 <br /><br />如有疑问，请及时联系平台客服。 </span> <br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    notice_action_create([
        'name' => 'client_credit_unfreeze',
        'name_lang' => '账户余额解冻通知',
        'type' => 'client_account',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '账户余额解冻通知',
            'content' => '尊敬的用户，您的@var(client_username)账户中被冻结的余额已经解冻，解冻金额为@var(frozen_amount)。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '账户余额解冻通知',
            'content' => '尊敬的用户，您的@var(client_username)账户中被冻结的余额已经解冻，解冻金额为@var(frozen_amount)。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '账户余额解冻通知',
            'title' => '[{system_website_name}] 账户余额解冻通知',
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
<h2 style="text-align: center;">[{system_website_name}] 账户余额解冻通知</h2>
<br /><strong>尊敬的用户，</strong><br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的 <span style="margin: 0; padding: 0; display: inline-block;"><strong>{client_username}</strong></span> 账户中被冻结的余额已经解冻，解冻金额为{frozen_amount}。 <br /><br />如有疑问，请及时联系平台客服。 </span> <br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    $adminAuth = [
        [
            'title' => 'auth_system_configuration_admin_management_unlock_admin',
            'url' => '',
            'description' => '解锁账户', # 权限描述
            'parent' => 'auth_system_configuration_admin_management', # 父权限
            'auth_rule' => [
                'app\admin\controller\AdminController::adminUnlock',
            ],
        ],
        [
            'title' => 'auth_system_configuration_admin_management_reset_bind_status',
            'url' => '',
            'description' => '重置绑定状态', # 权限描述
            'parent' => 'auth_system_configuration_admin_management', # 父权限
            'auth_rule' => [
                'app\admin\controller\AdminController::adminUnbindTotp',
            ],
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($adminAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.9' where `setting`='system_version';");
}
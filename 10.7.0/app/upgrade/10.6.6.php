<?php

use app\common\logic\CacheLogic;
use think\facade\Db;

upgradeData1066();
function upgradeData1066()
{
	$sql = [
        "ALTER TABLE `idcsmart_self_defined_field` ADD COLUMN `is_global` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '全局(0=否1=是)';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('recharge_order_support_refund', '0', 0, 0, '充值订单是否支持退款(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_login_password_encrypt', '0', 0, 0, '后台登录密码是否加密传输(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_new_order_notify', '0', 0, 0, '新购订单价格倒挂-通知管理员(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_new_order_reject', '0', 0, 0, '新购订单价格倒挂-拒绝下单(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_renew_notify', '0', 0, 0, '续费订单价格倒挂-通知管理员(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_renew_reject', '0', 0, 0, '续费订单价格倒挂-拒绝下单(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_upgrade_notify', '0', 0, 0, '升降级订单价格倒挂-通知管理员(0=否,1=是)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('upstream_intercept_upgrade_reject', '0', 0, 0, '升降级订单价格倒挂-拒绝下单(0=否,1=是)');",
        "ALTER TABLE `idcsmart_notice_setting`ADD UNIQUE INDEX `name` (`name`);",
        "ALTER TABLE `idcsmart_sms_template` ADD COLUMN `notice_setting_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '默认动作名称';",
        "ALTER TABLE `idcsmart_email_template` ADD COLUMN `notice_setting_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '默认动作名称';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('donot_save_client_product_password', '0', 0, 0, '不保存用户产品密码(0=否，1=是)');",
        "CREATE TABLE `idcsmart_mf_cloud_data_center_map_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '区域组名',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云区域组表';",
        "CREATE TABLE `idcsmart_mf_cloud_data_center_map_group_link` (
  `mf_cloud_data_center_map_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `data_center_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  KEY `group_id` (`mf_cloud_data_center_map_group_id`),
  KEY `product_id_data_center_id` (`product_id`,`data_center_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云区域组数据中心关联表';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `upstream_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '上游原价(已转换汇率)';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `auto_unsuspend_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '自动解除暂停时间';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `discount_renew_price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '可应用续费等级折扣金额';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `renew_use_current_client_level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '续费是否使用当前等级折扣';",
        "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `renew_price_difference_client_level_discount` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '续费用户等级差价';",

        // 跨模块列表
        "ALTER TABLE `idcsmart_menu` ADD COLUMN `is_cross_module` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为跨模块列表';",
        "ALTER TABLE `idcsmart_menu` ADD COLUMN `select_field` varchar(1000) NOT NULL DEFAULT '' COMMENT '选择字段';",
        "ALTER TABLE `idcsmart_menu` CHANGE COLUMN `module` `module` varchar(1000) NOT NULL DEFAULT '' COMMENT '模块';",
        
        // 独立通知任务队列相关
        "CREATE TABLE IF NOT EXISTS `idcsmart_task_notice_wait` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联主任务表ID',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '任务类型(email/sms),其他通知',
  `task_data` text NOT NULL COMMENT '任务数据JSON',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `status` varchar(20) NOT NULL DEFAULT 'Wait' COMMENT '任务状态Wait/Exec/Finish/Failed',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '版本号(乐观锁)',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始执行时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `finish_time` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间',
  `retry` int(11) NOT NULL DEFAULT '0' COMMENT '重试次数',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '任务描述',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `version` (`version`),
  KEY `start_time` (`start_time`),
  KEY `retry` (`retry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='独立通知任务队列表';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('notice_independent_task_enabled', '0', 0, 0, '独立通知任务队列开关(0=关闭,1=开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('notice_task_retry_times', '3', 0, 0, '通知任务重试次数');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('notice_task_timeout', '300', 0, 0, '通知任务超时时间(秒)');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('notice_task_time', '', 0, 0, '通知任务队列执行时间戳');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('captcha_client_security_verify', '0', 0, 0, '安全校验是否开启图形验证码0=否,1=是');",
    ];

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_cpu_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'CPU是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_memory_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '内存是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_bw_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '带宽是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv4_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IPv4是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv6_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IPv6是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_system_disk_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '系统盘是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_data_disk_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据盘是否应用等级优惠续费(0=不启用,1=启用)';";
    }

    $mfDcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if (!empty($mfDcimServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_memory_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '内存是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_disk_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '硬盘是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_gpu_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显卡是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_bw_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '带宽是否应用等级优惠续费(0=不启用,1=启用)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_ip_num_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IP是否应用等级优惠续费(0=不启用,1=启用)';";
    }

    $idcsmartCloudPlugin = Db::name('plugin')->where('name', 'IdcsmartCloud')->find();
    if (!empty($idcsmartCloudPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` ADD COLUMN `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID';";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` ADD INDEX `supplier_id` (`supplier_id`);";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` ADD COLUMN `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID';";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` ADD INDEX `supplier_id` (`supplier_id`);";
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('before_res_module_create_account', '{$idcsmartCloudPlugin['id']}', 'IdcsmartCloud', 'addon', 0);";
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('after_supplier_delete', '{$idcsmartCloudPlugin['id']}', 'IdcsmartCloud', 'addon', 0);";
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='3.0.1' WHERE `name`='IdcsmartCloud';";
    }

    $idcsmartRenewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
    if (!empty($idcsmartRenewPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_renew` ADD COLUMN `client_level_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费用户等级折扣';";
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    // 增加价格倒挂通知动作
    notice_action_create([
        'name' => 'loss_trade_alert',
        'name_lang' => '价格倒挂通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '价格倒挂通知',
            'content' => '系统检测到商品@var(product_name)出现价格倒挂风险,当前上游价格：@var(purchase_price) 元， 平台卖价：@var(order_amount) 元，存在亏损风险，请及时处理。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '价格倒挂通知',
            'content' => '系统检测到商品@var(product_name)出现价格倒挂风险,当前上游价格：@var(purchase_price) 元， 平台卖价：@var(order_amount) 元，存在亏损风险，请及时处理。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '价格倒挂通知',
            'title' => '[{system_website_name}] 价格倒挂通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>价格倒挂通知</title>
    <style>
        li { list-style: none; }
        a { text-decoration: none; }
        body { margin: 0; }
        .box { background-color: #EBEBEB; height: 100%; }
        .logo_top { padding: 20px 0; }
        .logo_top img { display: block; width: auto; margin: 0 auto; }
        .card {
            width: 650px;
            margin: 0 auto;
            background-color: white;
            font-size: 0.8rem;
            line-height: 22px;
            padding: 40px 50px;
            box-sizing: border-box;
        }
        .contimg { text-align: center; }
        button {
            background-color: #F75697;
            padding: 8px 16px;
            border-radius: 6px;
            outline: none;
            color: white;
            border: 0;
        }
        .lvst { color: #57AC80; }
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
            .card { padding: 5% 5%; }
            .logo_top img, .contimg img { width: 280px; }
            .box { height: auto; }
            .card { width: auto; }
        }
        @media (max-width: 280px) {
            .logo_top img, .contimg img { width: 100%; }
        }
    </style>
</head>

<body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}] 价格倒挂通知</h2>
<br /><strong>尊敬的管理员，</strong><br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 系统检测到商品<strong>{product_name}</strong>出现价格倒挂风险。 <br />订单类型：<span class="lvst">{order_type}</span> <br />当前上游价格：<span class="lvst">{purchase_price}</span> 元， 平台卖价：<span class="lvst">{order_amount}</span> 元。 <br />存在亏损风险，请及时处理。 <br /><br /></span> <br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    // 清除所有缓存
    if (class_exists("\\app\\common\\logic\\CacheLogic")){
        \app\common\logic\CacheLogic::clearAllCache();
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.6' where `setting`='system_version';");
}
<?php
use think\facade\Db;

upgradeData1063();
function upgradeData1063()
{
	$sql = [
        "INSERT INTO `idcsmart_configuration` (`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_login_ip_whitelist', '', 0, 0, '后台登录IP白名单，换行分隔，空表示不限制');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('balance_notice_show','1','会员中心是否显示余额提醒');",
        "UPDATE `idcsmart_configuration` SET `value`=0 WHERE `setting`='host_sync_due_time_apply_range';",
        "UPDATE `idcsmart_configuration` SET `value`=1 WHERE `setting`='host_sync_due_time_open';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `price_basis` varchar(20) NOT NULL DEFAULT 'agent' COMMENT '价格基础:standard标准价,agent代理价' AFTER `mode`;",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.1' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.1' WHERE `name`='IdcsmartNews';",
	];

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_cpu_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_cpu_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU升级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_memory_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_memory_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存升级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_bw_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_bw_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽升降级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv4_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv4订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv4_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv4升降级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv6_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv6订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_ipv6_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv6升降级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_system_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统盘订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_system_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统盘升级等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_data_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据盘订购等级优惠(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `level_discount_data_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据盘升级等级优惠(0=关闭,1=开启)';";
    }

    // 是否有续费插件
    $IdcsmartNewsPlugin = Db::name('plugin')->where('name', 'IdcsmartNews')->find();
    if (!empty($IdcsmartNewsPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `is_top` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否置顶(0=否,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `top_time` INT(11) NOT NULL DEFAULT '0' COMMENT '置顶时间';";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD KEY `is_top` (`is_top`);";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.3' where `setting`='system_version';");
}
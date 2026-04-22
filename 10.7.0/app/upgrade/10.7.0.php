<?php
use think\facade\Db;

upgradeData1070();
function upgradeData1070()
{
	$sql = [
        "ALTER TABLE `idcsmart_cloud_server_area` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_cloud_server_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_physical_server_area` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_physical_server_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_ssl_certificate_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_sms_service_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_trademark_register_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_trademark_service_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_server_hosting_area` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_server_hosting_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_cabinet_rental_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_icp_service_product` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `need_manual_sync` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '需要手动同步0=否1=是';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('www_url','','官网地址');",
        "UPDATE `idcsmart_plugin` SET `version`='3.1.4' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.6' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='3.1.2' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='4.0.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='4.0.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='4.0.0' WHERE `name`='MfDcim';",
    ];

    $mfCloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfCloudServer)){
        $sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_mf_cloud_security_group_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `protocol` varchar(20) NOT NULL DEFAULT 'tcp' COMMENT '协议类型(all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis)',
  `port` varchar(100) NOT NULL DEFAULT '' COMMENT '端口',
  `direction` varchar(10) NOT NULL DEFAULT 'in' COMMENT '方向(in=入站,out=出站)',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0=禁用,1=启用)',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `upstream_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云安全组协议配置表';";
    }

    $idcsmartCommonServer = Db::name('server')->where('module', 'idcsmart_common')->find();
    if (!empty($idcsmartCommonServer)){
        $sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_common_cascade_group` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '级联组ID',
            `configoption_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置项ID',
            `group_name` varchar(255) NOT NULL DEFAULT '' COMMENT '级联组名称',
            `level` int(11) NOT NULL DEFAULT '1' COMMENT '层级编号，从1开始',
            `upstream_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游ID',
            `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
            `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
            PRIMARY KEY (`id`),
            KEY `configoption_id` (`configoption_id`),
            KEY `level` (`level`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='级联配置项组表';";
        $sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_common_cascade_item` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '级联项ID',
            `cascade_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属级联组ID',
            `parent_item_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级联项ID，顶级为0',
            `item_name` varchar(255) NOT NULL DEFAULT '' COMMENT '级联项名称',
            `fee_type` varchar(20) NOT NULL DEFAULT 'fixed' COMMENT '计费类型：fixed固定价格，qty数量计费，stage阶梯计费',
            `is_leaf` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否为末端级联项：1是，0否',
            `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
            `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏：1是，0否',
            `upstream_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游ID',
            `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
            `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
            PRIMARY KEY (`id`),
            KEY `cascade_group_id` (`cascade_group_id`),
            KEY `parent_item_id` (`parent_item_id`),
            KEY `is_leaf` (`is_leaf`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='级联配置项表';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption_sub` ADD COLUMN `cascade_item_id` int(11) NOT NULL DEFAULT '0' COMMENT '级联项ID，用于级联配置项' AFTER `product_configoption_id`;";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption_sub` ADD KEY `cascade_item_id` (`cascade_item_id`);";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_host_configoption` ADD COLUMN `cascade_item_id` int(11) NOT NULL DEFAULT '0' COMMENT '级联项ID' AFTER `repeat`,ADD KEY `cascade_item_id` (`cascade_item_id`);";
    }

    $PromoCodePlugin = Db::name('plugin')->where('name', 'PromoCode')->find();
    if (!empty($PromoCodePlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_promo_code` ADD COLUMN `exclude_with_client_level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '不与用户等级同享(0=关闭,1=开启)';";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $adminAuth = [
        [
            'title' => 'auth_upstream_downstream_upstream_product_manual_sync_resource',
            'url' => '',
            'description' => '手动同步上游资源', # 权限描述
            'parent' => 'auth_upstream_downstream_upstream_product', # 父权限
            'auth_rule' => [
                'app\admin\controller\UpstreamProductController::manualSyncResource',
            ],
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($adminAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.7.0' where `setting`='system_version';");
}
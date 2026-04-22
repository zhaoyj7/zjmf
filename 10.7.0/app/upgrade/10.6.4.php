<?php
use think\facade\Db;

upgradeData1064();
function upgradeData1064()
{
	$sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('login_register_redirect_show','0','登录注册页面展示跳转:1是0否');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('login_register_redirect_text','','按钮文案');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('login_register_redirect_url','','跳转地址');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('login_register_redirect_blank','0','跳转按钮是否新窗口打开:1是0否');",
        "CREATE TABLE `idcsmart_theme_banner` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '会员中心轮播图ID',
  `theme` VARCHAR(255) NOT NULL DEFAULT 'default' COMMENT '主题',
  `img` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '图片',
  `url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `start_time` INT(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` INT(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `show` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
  `notes` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `order` INT(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `show` (`show`),
  KEY `order` (`order`),
  KEY `time_range` (`start_time`,`end_time`),
  KEY `theme` (`theme`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='会员中心首页轮播图表';",
        "CREATE TABLE `idcsmart_theme_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
  `display_one` varchar(25) DEFAULT 'ticket' COMMENT '公告区域展示内容1：ticket工单，announcement公告，recommend推荐计划',
  `display` varchar(25) NOT NULL DEFAULT 'announcement' COMMENT '公告区域展示内容2：announcement公告，recommend推荐计划',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme` (`theme`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.1' WHERE `name`='IdcsmartWithdraw';",
	];

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `vpc_private_ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'VPC内网IP';";
    }

    $promoCodePlugin = Db::name('plugin')->where('name', 'PromoCode')->find();
    if (!empty($promoCodePlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_promo_code` ADD COLUMN `flow_packet` tinyint(1) NOT NULL DEFAULT '0' COMMENT '流量包使用0关闭1开启';";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $adminAuth = [
        [
            'title' => 'auth_host_push_downstream',
            'url' => '',
            'description' => '推送到下游', # 权限描述
            'parent' => 'auth_business_host_detail', # 父权限
            'auth_rule' => [
                'app\admin\controller\HostController::pushDownstream',
            ],
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($adminAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.4' where `setting`='system_version';");
}
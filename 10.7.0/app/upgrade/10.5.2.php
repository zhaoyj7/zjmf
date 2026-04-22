<?php
use think\facade\Db;

upgradeData1052();
function upgradeData1052()
{
	$sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cart_instruction','0','购物车说明开关(0=关闭1=开启)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cart_instruction_content','','购物车说明内容');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cart_change_product','0','购物车说切换商品开关(0=关闭1=开启)');",
        "INSERT INTO `idcsmart_nav`(`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) VALUES ('admin','nav_admin_index','index.htm','',0,0,'','');",
        "INSERT INTO `idcsmart_nav`(`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) VALUES ('home','nav_index','home.htm','',0,0,'','');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('self_defined_field_apply_range','0','自定义字段应用范围(0=无1=商品分组新增商品)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('custom_host_name_apply_range','0','自定义标识应用范围(0=无1=商品分组新增商品)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('first_login_method','code','首选登录方式(code=验证码,password=密码)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('first_password_login_method','email','密码登录首选(phone=手机,email=邮箱)');",
        "UPDATE idcsmart_order o SET o.gateway= 'credit',o.gateway_name='余额支付' WHERE o.amount = o.credit AND o.`status` = 'Paid' AND o.amount <>0 ;",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_duration_group_presets_open','0','是否开启商品周期分组预设');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_duration_group_presets_apply_range','0','商品周期分组预设应用范围(0全局默认，1接口新增商品)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_duration_group_presets_default_id','0','商品周期分组预设全局默认分组ID');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_new_host_renew_with_ratio_open','0','新产品按周期比例折算');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_new_host_renew_with_ratio_apply_range','0','新产品续费按周期比例折算范围(2商品分组下新产品，1接口下新产品，0全部新产品)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_new_host_renew_with_ratio_apply_range_2','','二级分组id，逗号分隔');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_new_host_renew_with_ratio_apply_range_1','','接口id，逗号分隔');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_global_renew_rule','0','商品到期日计算规则(0实际到期日，1产品到期日)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('product_global_show_base_info','0','基础信息展示(0关闭，1开启)');",
        "CREATE TABLE `idcsmart_self_defined_field_link` (
  `self_defined_field_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义字段ID',
  `product_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品分组ID',
  KEY `self_defined_field_id` (`self_defined_field_id`),
  KEY `product_group_id` (`product_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义字段关联表';",
        "CREATE TABLE `idcsmart_custom_host_name` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `custom_host_name_prefix` varchar(10) NOT NULL DEFAULT '' COMMENT '自定义主机标识前缀',
  `custom_host_name_string_allow` varchar(100) NOT NULL DEFAULT '' COMMENT '允许的字符串(number=数字,upper=大写字母,lower=小写字母)',
  `custom_host_name_string_length` int(11) NOT NULL DEFAULT '0' COMMENT '字符串长度',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义产品标识表';",
        "CREATE TABLE `idcsmart_custom_host_name_link` (
  `custom_host_name_id` int(11) NOT NULL DEFAULT '0',
  `product_group_id` int(11) NOT NULL DEFAULT '0',
  KEY `custom_host_name_id` (`custom_host_name_id`),
  KEY `product_group_id` (`product_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义产品标识关联表';",
        "CREATE TABLE `idcsmart_sync_image_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `result` varchar(255) NOT NULL DEFAULT '' COMMENT '同步结果',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='同步镜像日志表';",
        "CREATE TABLE `idcsmart_local_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '本地镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='本地镜像表';",
        "CREATE TABLE `idcsmart_local_image_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='本地镜像分组表';",
        "ALTER TABLE `idcsmart_trademark_register_product` DROP COLUMN `version`;",
        "ALTER TABLE `idcsmart_task_wait` ADD COLUMN `version` INT(11) NOT NULL DEFAULT '0' COMMENT '版本号，乐观锁';",
        "CREATE TABLE `idcsmart_product_duration_group_presets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品周期分组预设',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `ratio_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启周期比例',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        "CREATE TABLE `idcsmart_product_duration_group_presets_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '周期预设分组关联接口',
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口ID',
  `gid` int(11) NOT NULL DEFAULT '0' COMMENT '周期预设分组ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        "CREATE TABLE `idcsmart_product_duration_presets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品周期预设',
  `gid` int(11) NOT NULL DEFAULT '0' COMMENT '周期分组预设ID',
  `name` varchar(25) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时长',
  `unit` varchar(25) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `ratio` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期比例',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
	];

    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.2.2' WHERE `name`='IdcsmartRefund';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.2.2' WHERE `name`='MfDcim';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.2.2' WHERE `name`='MfCloud';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.2.2' WHERE `name`='IdcsmartCommon';";

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $globalSettingAuth = [
        [
            'title' => 'auth_product_management_global_setting',
            'url' => '',
            'description' => '全局设置', # 权限描述
            'parent' => 'auth_product_management', # 父权限 
            'child' => [
                [
                    'title' => 'auth_product_management_global_setting_custom',
                    'url' => 'global_setting_custom',
                    'description' => '商品自定义', # 权限描述
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::productGlobalSetting',
                        'app\admin\controller\ConfigurationController::productGlobalSettingUpdate',
                        'app\admin\controller\SelfDefinedFieldController::selfDefinedFieldList',
                        'app\admin\controller\SelfDefinedFieldController::create',
                        'app\admin\controller\SelfDefinedFieldController::update',
                        'app\admin\controller\SelfDefinedFieldController::delete',
                        'app\admin\controller\SelfDefinedFieldController::dragToSort',
                        'app\admin\controller\SelfDefinedFieldController::relatedProductGroup',
                        'app\admin\controller\CustomHostNameController::list',
                        'app\admin\controller\CustomHostNameController::create',
                        'app\admin\controller\CustomHostNameController::update',
                        'app\admin\controller\CustomHostNameController::delete',
                        'app\admin\controller\CustomHostNameController::relatedProductGroup',
                    ],
                ],
                [
                    'title' => 'auth_product_management_global_setting_cycle',
                    'url' => 'global_setting_cycle',
                    'description' => '全局默认周期设置', # 权限描述
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::productGlobalSetting',
                        'app\admin\controller\ConfigurationController::productGlobalSettingUpdate',
                        'app\admin\controller\ProductDurationGroupPresetsController::presetsList',
                        'app\admin\controller\ProductDurationGroupPresetsController::index',
                        'app\admin\controller\ProductDurationGroupPresetsController::create',
                        'app\admin\controller\ProductDurationGroupPresetsController::update',
                        'app\admin\controller\ProductDurationGroupPresetsController::delete',
                        'app\admin\controller\ProductDurationGroupPresetsController::copy',
                        'app\admin\controller\ProductDurationGroupPresetsLinkController::linkList',
                        'app\admin\controller\ProductDurationGroupPresetsLinkController::create',
                        'app\admin\controller\ProductDurationGroupPresetsLinkController::update',
                        'app\admin\controller\ProductDurationGroupPresetsLinkController::delete',
                        'app\admin\controller\ServerController::serverList',
                    ],
                ],
                [
                    'title' => 'auth_product_management_global_setting_os',
                    'url' => 'global_setting_os',
                    'description' => '操作系统配置', # 权限描述
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::productGlobalSetting',
                        'app\admin\controller\ConfigurationController::productGlobalSettingUpdate',
                        'app\admin\controller\ProductController::syncImageLogList',
                        'app\admin\controller\ProductController::syncImage',
                        'app\admin\controller\ProductController::moduleProductList',
                        'app\admin\controller\LocalImageController::list',
                        'app\admin\controller\LocalImageController::create',
                        'app\admin\controller\LocalImageController::update',
                        'app\admin\controller\LocalImageController::delete',
                        'app\admin\controller\LocalImageController::order',
                        'app\admin\controller\LocalImageGroupController::list',
                        'app\admin\controller\LocalImageGroupController::create',
                        'app\admin\controller\LocalImageGroupController::update',
                        'app\admin\controller\LocalImageGroupController::delete',
                        'app\admin\controller\LocalImageGroupController::order',
                    ],
                ],
                [
                    'title' => 'auth_product_management_global_setting_ratio',
                    'url' => 'global_setting_ratio',
                    'description' => '产品周期比例折算', # 权限描述
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::productGlobalSetting',
                        'app\admin\controller\ConfigurationController::productGlobalSettingUpdate',
                        'app\admin\controller\ServerController::serverList',
                        'app\admin\controller\ProductController::productList',
                        'app\admin\controller\ProductGroupController::productGroupFirstList',
                        'app\admin\controller\ProductGroupController::productGroupSecondList',
                    ],
                ], 
                [
                    'title' => 'auth_product_management_global_setting_other',
                    'url' => 'global_setting_other',
                    'description' => '商品其他设置', # 权限描述
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::productGlobalSetting',
                        'app\admin\controller\ConfigurationController::productGlobalSettingUpdate',
                    ],
                ], 
            ],
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($globalSettingAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    $globalSettingAuth = $AuthModel->where('title', 'auth_product_management_global_setting')->find();
    $AuthModel->where('title', 'auth_product_management_notice_setting')->update(['parent_id' => $globalSettingAuth['id'], 'url' => 'global_setting_notice.htm']);

    Db::execute("update `idcsmart_configuration` set `value`='10.5.2' where `setting`='system_version';");
}
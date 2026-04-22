<?php
use think\facade\Db;

upgradeData1060();
function upgradeData1060()
{
    $sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('global_list_limit', '10', 0, 0, '全局列表展示条数');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_theme', 'default', 0, 0, '首页PC主题');",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_theme_mobile', '', 0, 0, '首页手机主题');",
        "ALTER TABLE `idcsmart_menu` ADD COLUMN `show_quick_order` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否展示快捷订购';",
        "ALTER TABLE `idcsmart_menu` ADD COLUMN `quick_order_url` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '快捷订购地址';",
        "INSERT INTO `idcsmart_nav` (`type`, `name`, `url`, `icon`, `parent_id`, `order`, `module`, `plugin`) values('home','nav_ordered','productList.htm','','0','1','','');",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.4' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.4' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='Idcsmart';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='Idcsmartali';",
        "UPDATE `idcsmart_plugin` SET `version`='2.4.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='2.4.0' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='2.4.0' WHERE `name`='MfCloud';",
        "CREATE TABLE `idcsmart_addon_idcsmart_ticket_forward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单ID',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作管理员ID',
  `forward_admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '转入管理员ID',
  `ticket_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `admin_id` (`admin_id`),
  KEY `forward_admin_id` (`forward_admin_id`),
  KEY `ticket_type_id` (`ticket_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;",
    ];

    $clientareaTheme = configuration('clientarea_theme');
    $clientareaThemeMobile = configuration('clientarea_theme_mobile');
    // 产品详情默认主题
    $homeHostTheme = [];
    $homeHostThemeMobile = [];
    $ModuleLogic = new \app\common\logic\ModuleLogic();
    $moduleList = $ModuleLogic->getModuleList();
    foreach($moduleList as $k=>$v){
        // PC主题
        $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/pc');
        if(!empty($moduleThemeList)){
            if(in_array($clientareaTheme, $moduleThemeList)){
                $homeHostTheme[ $v['name'] ] = $clientareaTheme;
            }else{
                $homeHostTheme[ $v['name'] ] = 'default';
            }
        }else{
            $homeHostTheme[ $v['name'] ] = '';
        }
        // Mobile主题
        $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/mobile');
        if(!empty($moduleThemeList)){
            if(in_array($clientareaThemeMobile, $moduleThemeList)){
                $homeHostThemeMobile[ $v['name'] ] = $clientareaThemeMobile;
            }else{
                if(in_array('default', $moduleThemeList)){
                    $homeHostThemeMobile[ $v['name'] ] = 'default';
                }else{
                    $homeHostThemeMobile[ $v['name'] ] = '';
                }
            }
        }else{
            $homeHostThemeMobile[ $v['name'] ] = '';
        }
    }
    $homeHostTheme = json_encode($homeHostTheme);
    $homeHostThemeMobile = json_encode($homeHostThemeMobile);

    $sql[] = "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_host_theme', '{$homeHostTheme}', 0, 0, '会员中心产品详情PC主题');";
    $sql[] = "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_host_theme_mobile', '{$homeHostThemeMobile}', 0, 0, '会员中心产品详情手机主题');";

    $commonServer = Db::name('server')->where('module', 'idcsmart_common')->find();
    if(!empty($commonServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption` ADD COLUMN `upgrade` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支持升降级：1是,0否默认';";
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $adminAuth = [
        [
            'title' => 'auth_user_list_view',
            'url' => 'client',
            'description' => '查看页面', # 权限描述
            'parent' => 'auth_user_list', # 父权限
            'auth_rule' => [
                'app\admin\controller\ClientController::clientList',
                'app\admin\controller\ClientController::index',
                'app\admin\controller\ConfigurationController::systemList',
            ],
        ],
        [
            'title' => 'auth_business_order_view',
            'url' => 'order',
            'description' => '查看页面', # 权限描述
            'parent' => 'auth_business_order', # 父权限
            'auth_rule' => [
                'app\admin\controller\OrderController::orderList',
                'app\admin\controller\ProductController::productList',
                'app\admin\controller\ProductGroupController::productGroupFirstList',
                'app\admin\controller\ProductGroupController::productGroupSecondList',
                'app\admin\controller\OrderController::getOrderRecycleBinConfig',
                'app\admin\controller\ClientController::index',
                'app\admin\controller\ConfigurationController::systemList',
            ],
        ],
        [
            'title' => 'auth_business_host_view',
            'url' => 'host',
            'description' => '查看页面', # 权限描述
            'parent' => 'auth_business_host', # 父权限
            'auth_rule' => [
                'app\admin\controller\HostController::hostList',
                'app\admin\controller\ServerController::serverList',
                'app\admin\controller\ProductController::productList',
                'app\admin\controller\ProductGroupController::productGroupFirstList',
                'app\admin\controller\ProductGroupController::productGroupSecondList',
                'app\admin\controller\ModuleController::moduleList',
                'app\admin\controller\ClientController::index',
                'app\admin\controller\ConfigurationController::systemList',
            ],
        ],
        [
            'title' => 'auth_system_configuration_system_configuration_theme_configuration',
            'url'   => '',
            'description' => '主题设置',
            'parent' => 'auth_system_configuration',
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($adminAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    // 添加导航
    $nav = [
        'name'          => 'nav_configuration_theme',
        'url'           => 'configuration_theme.htm',
        'in'            => 'nav_system_settings',
        'icon'          => '',
        'menu_name'     => '主题设置',
    ];

    $NavModel = new \app\common\model\NavModel();
    $NavModel->createSystemNav($nav, 'admin');

    Db::execute("update `idcsmart_configuration` set `value`='10.6.0' where `setting`='system_version';");
}
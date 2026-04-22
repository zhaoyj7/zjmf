<?php
use think\facade\Db;
use app\common\model\HostModel;

upgradeData1047();
function upgradeData1047()
{
    $sql = array(
        "ALTER TABLE `idcsmart_menu` ADD COLUMN `res_module` varchar(255) NOT NULL DEFAULT '' COMMENT 'res模块名称';",
        "UPDATE `idcsmart_menu` SET `res_module`=CONCAT('[\"',`module`,'\"]') WHERE `menu_type`='res_module';",
        "UPDATE `idcsmart_menu` SET `module`='',`menu_type`='module' WHERE `menu_type`='res_module';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家';",
        "ALTER TABLE `idcsmart_client` ADD INDEX  country_id (`country_id`);",
        "UPDATE `idcsmart_client` `a` LEFT JOIN `idcsmart_country` `b` ON `a`.`country`=`b`.`name` OR `a`.`country`=`b`.`name_zh` SET `a`.`country_id`=`b`.`id` WHERE `b`.`id`>0;",
        "ALTER TABLE `idcsmart_client` DROP COLUMN `country`;",
        "DROP TABLE IF EXISTS `idcsmart_admin_view`;",
        "CREATE TABLE `idcsmart_admin_view` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `view` varchar(100) NOT NULL DEFAULT '' COMMENT '页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水)',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认视图0否1是',
  `choose` tinyint(1) NOT NULL DEFAULT '0' COMMENT '选中视图0否1是',
  `last_visit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '最后访问视图0否1是',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `select_field` varchar(3000) NOT NULL DEFAULT '' COMMENT '选定字段标识',
  `data_range_switch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用数据范围0否1是',
  `select_data_range` text NOT NULL COMMENT '当前选定数据范围',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0关闭1开启',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `view` (`view`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('captcha_client_verify','1',0,0,'验证手机/邮箱图形验证码开关(0=关闭,1=开启)');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('captcha_client_update','1',0,0,'修改手机/邮箱图形验证码开关(0=关闭,1=开启)');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('captcha_client_password_reset','1',0,0,'重置密码图形验证码开关(0=关闭,1=开启)');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('captcha_client_oauth','1',0,0,'三方登录图形验证码开关(0=关闭,1=开启)');",
        "CREATE TABLE `idcsmart_host_addition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `country_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国家表ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) NOT NULL DEFAULT '' COMMENT '区域',
  `power_status` varchar(20) NOT NULL DEFAULT '' COMMENT 'on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障',
  `image_icon` varchar(255) NOT NULL DEFAULT '' COMMENT '操作系统图标(Windows,CentOS,Ubuntu,Debian,ESXi,XenServer,FreeBSD,Fedora,ArchLinux,Rocky,AlmaLinux,OpenEuler,RedHat)',
  `image_name` varchar(255) NOT NULL DEFAULT '' COMMENT '操作系统名称',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '实例用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '实例密码',
  `port` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '端口',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品附加表';",
    );

    $sql[] = "CREATE TABLE `idcsmart_addon_idcsmart_ticket_delivery` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '工单传递规则表',
  `product_id` INT(11) NOT NULL DEFAULT '0' COMMENT '本地商品ID',
  `ticket_type_id` INT(11) NOT NULL DEFAULT '0' COMMENT '工单类型ID',
  `blocked_words` TEXT COMMENT '屏蔽词，逗号分隔',
  `create_time` INT(11) NOT NULL DEFAULT '0',
  `update_time` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;";
    $sql[] = "CREATE TABLE `idcsmart_addon_idcsmart_ticket_upstream` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '工单关联的上游工单，1对N，所以需创建此表',
  `host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '本地产品ID',
  `upstream_host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '上游产品ID',
  `ticket_id` INT(11) NOT NULL DEFAULT '0' COMMENT '本地工单ID',
  `upstream_ticket_id` INT(11) NOT NULL DEFAULT '0' COMMENT '上游工单ID',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delivery_status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '传递状态：1已开启传递，0已关闭传递',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `is_downstream` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否是下游传递上来的工单，1是，0否默认';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_delivery` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否显示【下游传递】(由下游后台配置决定)，1是，0否默认';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_source` VARCHAR(255) NOT NULL DEFAULT 'IdcsmartTicket' COMMENT '下游工单来源：IdcsmartTicket普通工单';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '下游token，传递至下游时需要此token验证';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '下游地址，传递至下游时需要';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_ticket_id` INT(11) NOT NULL DEFAULT 0 COMMENT '下游工单ID';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `downstream_delivery_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '下游工单传递状态：1已开启传递，0已关闭传递';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket` ADD COLUMN `token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '本地token，上游推送时需要此token进行验证';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket_reply` ADD COLUMN `is_downstream` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否是下游传递上来的工单，1是，0否默认';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket_reply` ADD COLUMN `downstream_ticket_reply_id` INT(11) NOT NULL DEFAULT 0 COMMENT '下游工单回复ID';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket_reply` ADD COLUMN `upstream_ticket_reply_id` INT(11) NOT NULL DEFAULT 0 COMMENT '上游工单回复ID';";
    $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) VALUES ('five_minute_cron',1,'IdcsmartTicket','addon',0);";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='IdcsmartTicket';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='IdcsmartRenew';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='IdcsmartRefund';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='PromoCode';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='IdcsmartCertification';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='MfDcim';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='MfCloud';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='IdcsmartCommon';";

    $adminField = Db::name('admin_field')->select()->toArray();
    foreach ($adminField as $key => $value) {
        $sql[] = "insert into `idcsmart_admin_view`(`name`,`view`,`default`,`choose`,`last_visit`,`admin_id`,`select_field`,`data_range_switch`,`select_data_range`,`order`,`status`,`create_time`,`update_time`) values ('默认视图','{$value['view']}',1,1,1,{$value['admin_id']},'{$value['select_field']}',0,'[]',0,1,{$value['create_time']},{$value['update_time']});";
    }
    $sql[] = "DROP TABLE IF EXISTS `idcsmart_admin_field`;";

    $widget = Db::name('admin_role_widget')->where('admin_role_id', 1)->find();
    if(!empty($widget)){
        $widget = explode(',', $widget['widget']);
        $widget[] = 'ToDo';
        Db::name('admin_role_widget')->where('admin_role_id', 1)->update(['widget' => implode(',', array_unique($widget))]);
    }
    
    // 是否有云的接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `upgrade_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '升降级是否显示(0=否,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `manual_manage` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '手动管理商品(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_data_center` ADD COLUMN `gpu_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'GPU名称';";

        // 调整线路GPU
        $option = Db::name('module_mf_cloud_option')
                ->field('o.*,l.data_center_id,l.gpu_name')
                ->alias('o')
                ->join('module_mf_cloud_line l', 'o.rel_id=l.id')
                ->where('o.rel_type', 8)
                ->select()
                ->toArray();

        // 记录下已存过的显卡
        $gpuName = [];
        $gpuNum = [];
        foreach($option as $v){
            if(!isset($gpuNum[ $v['data_center_id'] ][ $v['value'] ])){
                $gpuNum[ $v['data_center_id'] ][ $v['value'] ] = $v['id'];
                $sql[] = "UPDATE `idcsmart_module_mf_cloud_option` SET `rel_type`=10,`rel_id`={$v['data_center_id']} WHERE `id`={$v['id']};";
            }else{
                $sql[] = "DELETE FROM `idcsmart_module_mf_cloud_option` WHERE `id`={$v['id']};";
                $sql[] = "DELETE FROM `idcsmart_module_mf_cloud_price` WHERE `rel_type`=0 AND `rel_id`={$v['id']};";
            }
            if(!isset($gpuName[ $v['data_center_id'] ])){
                $gpuName[ $v['data_center_id'] ] = $v['gpu_name'];
                $sql[] = "UPDATE `idcsmart_module_mf_cloud_data_center` SET `gpu_name`='{$v['gpu_name']}' WHERE `id`={$v['data_center_id']};";
            }
        }
    }

    // 是否有DCIM的接口
    $dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($dcimServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)';";
    }

    // 是否有优惠码插件
    $promoCodePlugin = Db::name('plugin')->where('name', 'PromoCode')->find();
    if (!empty($promoCodePlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_promo_code` ADD COLUMN `client_level` text NOT NULL COMMENT '不包含的用户等级' AFTER `client_type`;";
        $sql[] = "ALTER TABLE `idcsmart_addon_promo_code` CHANGE COLUMN `client_type` `client_type` varchar(30) NOT NULL DEFAULT 'all' COMMENT '用户类型:all所有客户,new无产品用户,old用户必须存在激活中的产品,not_have_client_level未拥有指定用户等级';";
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='PromoCode';";
    }


    $templates = [
        # 短信/邮件模板
        'host_module_action' => [
            'name_lang' => '',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '产品模块操作',
                'content' => '「@var(product_name)」正在发起@var(module_action)操作。'
            ],
            'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '产品模块操作',
                'content' => '「@var(product_name)」正在发起@var(module_action)操作。'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '产品模块操作',
                'title' => '[{system_website_name}]产品模块操作',
                'content' => '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]产品模块操作</h2>
<br /><strong>尊敬的用户</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 55px;">您好！</span></div>
<div class="card">{product_name}正在发起{module_action}操作。<br /><br />&nbsp; <span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span><br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{send_time}</span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body> </html>'
            ],
            
        ],  
    ];
    foreach ($templates as $key=>$template){
        $template['name'] = $key;
        notice_action_create($template);
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }



    Db::execute("update `idcsmart_configuration` set `value`='10.4.7' where `setting`='system_version';");

    // 同步信息
    $HostModel = new HostModel();
    $hostId = HostModel::where('is_delete', 0)->whereIn('status', ['Active','Suspended'])->column('id');
    foreach($hostId as $id){
        $HostModel->syncAccount($id);
    }
}
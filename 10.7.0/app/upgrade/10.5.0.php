<?php
use think\facade\Db;

upgradeData1050();
function upgradeData1050()
{
	$sql = [
        "ALTER TABLE `idcsmart_order` ADD COLUMN `submit_application_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交申请时间';",
        "ALTER TABLE `idcsmart_order` ADD COLUMN `voucher` varchar(3000) NOT NULL DEFAULT '' COMMENT '凭证文件名,英文逗号分隔';",
        "ALTER TABLE `idcsmart_order` ADD COLUMN `review_fail_reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '审核失败原因';",
        "INSERT  INTO `idcsmart_email_template`(`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) VALUES ('线下支付申请','[{system_website_name}]线下支付申请','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\n<div class=\"box\">\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\n<div class=\"card\">\n<h2 style=\"text-align: center;\">[{system_website_name}]线下支付申请</h2>\n<br /><strong>尊敬的管理员</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\n<div class=\"card\">「{order_id}」已申请线下支付，请核查<br /><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\n<ul class=\"banquan\">\n<li>{system_website_name}</li>\n</ul>\n</div>\n</body> </html>','',1723531967,0);",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',0,'线下支付申请','「@var(order_id)」已申请线下支付，请核查','',2,'Idcsmart','',1723531967,0,'','');",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',1,'线下支付申请','「@var(order_id)」已申请线下支付，请核查','',2,'Idcsmart','',1723531967,0,'','');",
        "INSERT  INTO `idcsmart_notice_setting`(`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) VALUES ('offline_payment_application','线下支付申请','',0,'',0,0,'',0,0);",
        "INSERT  INTO `idcsmart_email_template`(`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) VALUES ('上下游操作失败通知','上下游操作失败通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\n<div class=\"box\">\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\n<div class=\"card\">\n<h2 style=\"text-align: center;\">[{system_website_name}]上下游操作失败通知</h2>\n<br /><strong>尊敬的管理员</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\n<div class=\"card\">「{client_username}」的产品「{product_id}」上下游「{action}」失败<br /><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\n<ul class=\"banquan\">\n<li>{system_website_name}</li>\n</ul>\n</div>\n</body> </html>','',1723531967,0);",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',0,'上下游操作失败通知','「@var(client_username)」的产品「@var(product_id)」上下游「@var(action)」失败','',2,'Idcsmart','',1723531967,0,'','');",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',1,'上下游操作失败通知','「@var(client_username)」的产品「@var(product_id)」上下游「@var(action)」失败','',2,'Idcsmart','',1723531967,0,'','');",
        "INSERT  INTO `idcsmart_notice_setting`(`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) VALUES ('updownstream_action_failed_notice','上下游操作失败通知','',0,'',0,0,'',0,0);",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `mode` VARCHAR(25) NOT NULL DEFAULT 'only_api' COMMENT '代理模式：only_api仅调用接口，sync同步商品';",
        "ALTER TABLE `idcsmart_server` ADD COLUMN `upstream_use` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否上下游使用';",
        "UPDATE `idcsmart_plugin` SET `help_url`='https://my.idcsmart.com/cart/goods.htm?id=337' WHERE `name`='Idcsmart' AND `module`='sms';",
        "UPDATE `idcsmart_plugin` SET `help_url`='https://my.idcsmart.com/cart/goods.htm?id=922' WHERE `name`='Idcsmartmail' AND `module`='mail';",
        "UPDATE `idcsmart_plugin` SET `help_url`='https://my.idcsmart.com/cart/goods.htm?id=817' WHERE `name`='Idcsmartali' AND `module`='certification';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `base_info` varchar(1000) NOT NULL DEFAULT '' COMMENT '基础信息';",
        "ALTER TABLE `idcsmart_self_defined_field` ADD COLUMN `explain_content` text NOT NULL COMMENT '说明内容';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartFileDownload';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartNews';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartSshKey';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartHelp';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartSubAccount';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartAnnouncement';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.0' WHERE `name`='MfDcim';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_lock_fifteen_minute_last_time','0','15分钟执行定时任务结束时间');",
	];

    $auth = Db::name('auth')->where('title', 'auth_business_order')->find();
    if (!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');

        $insertAuth = [
            [
                'title'     => 'auth_business_order_upload_voucher',
                'order'     => $maxOrder+1,
                'parent_id' => $auth['id'],
                'description' => '上传凭证',
                'rules'         => [
                    'app\admin\controller\OrderController::uploadOrderVoucher',
                ]
            ],
            [
                'title'     => 'auth_business_order_review_voucher',
                'order'     => $maxOrder+2,
                'parent_id' => $auth['id'],
                'description' => '审核凭证',
                'rules'         => [
                    'app\admin\controller\OrderController::reviewOrder',
                ]
            ],
            [
                'title'         => 'auth_business_order_view_voucher',
                'order'         => $maxOrder+3,
                'parent_id'     => $auth['id'],
                'description'   => '查看凭证',
                'rules'         => [],
            ],
        ];

        foreach($insertAuth as $v){
            $authId = Db::name('auth')->insertGetId([
                'title'         => $v['title'],
                'url'           => '',
                'order'         => $v['order'],
                'parent_id'     => $v['parent_id'],
                'module'        => '',
                'plugin'        => '',
                'description'   => $v['description'],
            ]);
            foreach($v['rules'] as $vv){
                $authRule = Db::name('auth_rule')->where('name', $vv)->find();
                if(empty($authRule)){
                    $authRuleId = Db::name('auth_rule')->insertGetId([
                        'name' => $vv,
                    ]);
                }else{
                    $authRuleId = $authRule['id'];
                }
                if(!empty($authRuleId)){
                    Db::name('auth_rule_link')->insert([
                        'auth_rule_id'  => $authRuleId,
                        'auth_id'       => $authId,
                    ]);
                }
            }
            Db::name('auth_link')->insert([
                'admin_role_id' => 1,
                'auth_id'       => $authId,
            ]);
        }
    }


    // 是否有云的接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_image` ADD COLUMN `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_backup_config` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `is_agent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '下游可用,是否代理商(0=不是,1=是)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_data_center` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_disk` ADD COLUMN `type2` varchar(20) NOT NULL DEFAULT 'data' COMMENT 'system=系统盘,data=数据盘';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_disk` ADD COLUMN `status` tinyint(7) unsigned NOT NULL DEFAULT '3' COMMENT '状态';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_disk` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游磁盘ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_duration` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_image` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_image_group` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_vpc_network` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_resource_package` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_limit_rule` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";

        $hostLink = Db::name('module_mf_cloud_host_link')
                    ->alias('hl')
                    ->field('h.id,hl.config_data')
                    ->join('host h', 'hl.host_id=h.id')
                    ->select();
        foreach($hostLink as $v){
            $configData = json_decode($v['config_data'], true);
            $tempData = [
                'cpu'               => $configData['cpu']['value'] . '核',
                'memory'            => $configData['memory']['value'] . str_replace('B', '', $configData['memory_unit'] ?? 'G'),
                'system_disk_size'  => $configData['system_disk']['value'] . 'G',
            ];
            $tempData = implode('-', $tempData);
            $sql[] = "UPDATE `idcsmart_host` SET `base_info`='{$tempData}' WHERE `id`={$v['id']};";
        }
    }

    // 是否有DCIM的接口
    $dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($dcimServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_image` ADD COLUMN `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_data_center` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_duration` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_image` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_image_group` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_limit_rule` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_option` ADD COLUMN `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID';";

        $hostLink = Db::name('module_mf_dcim_host_link')
                    ->alias('hl')
                    ->field('h.id,hl.config_data')
                    ->join('host h', 'hl.host_id=h.id')
                    ->select();
        foreach($hostLink as $v){
            $configData = json_decode($v['config_data'], true);
            $adminField = $configData['admin_field'] ?? $configData['model_config'] ?? [];
            if(empty($adminField)){
                continue;
            }
            $tempData = [
                'cpu'               => $adminField['cpu'],
                'memory'            => $adminField['memory'],
                'disk'              => $adminField['disk'],
            ];
            if(isset($adminField['gpu']) && !empty($adminField['gpu'])){
                $tempData['gpu'] = $adminField['gpu'];
            }
            $tempData = implode('-', $tempData);
            $sql[] = "UPDATE `idcsmart_host` SET `base_info`='{$tempData}' WHERE `id`={$v['id']};";
        }

    }

    // 是否有通用的接口
    $commonServer = Db::name('server')->where('module', 'idcsmart_common')->find();
    if(!empty($commonServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption` ADD COLUMN `upstream_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption` ADD COLUMN `qty_change` int(11) NOT NULL DEFAULT '1' COMMENT '数量变化最小值';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption_sub` DROP COLUMN `qty_change`;";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_product_configoption_sub` ADD COLUMN `upstream_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_custom_cycle` ADD COLUMN `upstream_id` INT(11) NOT NULL DEFAULT 0 COMMENT '上游ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_idcsmart_common_server` ADD COLUMN `upstream_use` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否上下游使用';";
    }

    // 是否有机柜租用的接口
    $dcimCabinetServer = Db::name('server')->where('module', 'mf_dcim_cabinet')->find();
    if(!empty($dcimCabinetServer)){
        $hostLink = Db::name('module_mf_dcim_cabinet_host_link')
                    ->alias('hl')
                    ->field('h.id,hl.config_data')
                    ->join('host h', 'hl.host_id=h.id')
                    ->select();
        foreach($hostLink as $v){
            $configData = json_decode($v['config_data'], true);
            if(!isset($configData['package'])){
                continue;
            }
            $u = ($configData['package']['u'] ?? 0).'U';
            $uType = $configData['package']['u_type'] ? lang_plugins('mf_dcim_cabinet_u_type_'.$configData['package']['u_type']) : '';

            $tempData = [
                'u'               => !empty($uType) ? $u . '('. $uType .')' : $u,
                'electric'        => $configData['package']['electric'] . $configData['package']['electric_unit'],
                'bw'              => $configData['package']['bw'] . 'M',
            ];
            $tempData = implode('-', $tempData);
            $sql[] = "UPDATE `idcsmart_host` SET `base_info`='{$tempData}' WHERE `id`={$v['id']};";
        }

    }    

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.0' where `setting`='system_version';");

    // $url = "https://license.soft13.idcsmart.com/app/api/auth_rc_plugin";
    // $license = configuration('system_license');//系统授权码
    // $ip = $_SERVER['SERVER_ADDR'];//服务器地址
    // $arr = parse_url($_SERVER['HTTP_HOST']);
    // $domain = isset($arr['host'])? ($arr['host'].(isset($arr['port']) ? (':'.$arr['port']) : '')) :$arr['path'];
    // $type = 'finance';
    
    // $systemVersion = configuration('system_version');//系统当前版本
    // $data = [
    //     'ip' => $ip,
    //     'domain' => $domain,
    //     'type' => $type,
    //     'license' => $license,
    //     'install_version' => $systemVersion,
    //     'request_time' => time(),
    // ];
    // $res = curl($url,$data,20,'POST');
    // if($res['http_code'] == 200){
    //     $result = json_decode($res['content'], true);
    // }
    // if(isset($result['status']) && $result['status']==200){
    //     $list = $result['data']['list'] ?? [];
    //     $plugin = Db::name('plugin')->column('name');
    //     foreach ($list as $key => $value) {
    //         if(in_array($value['uuid'], ['ClientCare','CreditLimit','EContract','IdcsmartDomain','IdcsmartInvoice','TicketPremium','IdcsmartRecommend','BtVirtualHost','DirectAdmin','MfCloudDisk','MfCloudIp','MfDcimCabinet','WestDomain','ZgsjDomain']) && in_array($value['uuid'], $plugin)){
    //             $PluginModel = new \app\admin\model\PluginModel();
    //             $PluginModel->download(['id' => $value['id']]);
    //         }
    //     }
    // }
}
<?php
use think\facade\Db;

upgradeData1068();
function upgradeData1068()
{
	$sql = [
        "ALTER TABLE `idcsmart_product` ADD COLUMN `sync_stock` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否同步库存，1是默认，0否';",
        "INSERT INTO `idcsmart_configuration` (`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('exception_login_certification_plugin', 'Idcsmartali', 0, 0, '异常登录实名认证插件');",
        "INSERT INTO `idcsmart_configuration` (`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_logo', 'logo.png', 0, 0, '后台logo');",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.5' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.9' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.8' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.6' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='2.4.0' WHERE `name`='Idcsmartali';",
        "DROP TABLE IF EXISTS `idcsmart_mf_cloud_data_center_map_group`;",
        "CREATE TABLE `idcsmart_mf_cloud_data_center_map_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '区域组名',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云区域组表';",
    ];

    $idcsmartRefundPlugin = Db::name('plugin')->where('name', 'IdcsmartRefund')->find();
    if (!empty($idcsmartRefundPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `api_refund_allow` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'API开通是否允许退款:0否,1是' AFTER `action`;";
    }

    $renewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
    if (!empty($renewPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_renew` ADD COLUMN `is_continuous_renew` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否连续续费';";
    }

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `ipv6_group_id` varchar(10) NOT NULL DEFAULT '' COMMENT 'IPv6分组ID';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_duration` ADD COLUMN `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为默认周期(0=否,1=是)';";
    }

    $idcsmartRenewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
    if (!empty($idcsmartRenewPlugin)){
        notice_action_create([
            'name' => 'host_auto_renew_fail',
            'name_lang' => '自动续费扣款失败提醒',
            'type' => 'host',
            'sms_name' => 'Idcsmart',
            'sms_template' => [
                'title' => '自动续费扣款失败提醒',
                'content' => '尊敬的用户，您的账户余额不足以支付自动续费账单，请及时充值，以免影响服务使用。'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '自动续费扣款失败提醒',
                'content' => '尊敬的用户，您的账户余额不足以支付自动续费账单，请及时充值，以免影响服务使用。'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '自动续费扣款失败提醒',
                'title' => '[{system_website_name}]自动续费扣款失败提醒',
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
<h2 style="text-align: center;">[{system_website_name}] 自动续费扣款失败提醒</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">因您的账户余额不足，账单{order_id}续费自动扣款失败。<br /><br />请及时充值并前往账单页面完成支付，以避免服务中断。<br /></span><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
            ],
        ]);
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.8' where `setting`='system_version';");
}
<?php
use think\facade\Db;

upgradeData1057();
function upgradeData1057()
{
    $sql = [
        "ALTER TABLE `idcsmart_product` ADD COLUMN `pay_ontrial` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '试用配置，status:是否开启，
cycle_type:时长单位(hour/day/month)，cycle_num:时长，client_limit:用户限制(no不限制/new新用户/host用户必须存在激活中的产品)，
account_limit:账户限制(email绑定邮件/phone绑定手机/certification)，old_client_exclusive:老用户专享(商品ID多选，逗号分隔)，
max:单用户最大试用数量';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `base_config_options` TEXT COMMENT '产品初始配置参数';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `is_ontrial` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '当前是否试用，1是，0否默认，用于续费判断';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `first_payment_ontrial` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '首次购买是否试用，1是，0否默认，用于试用产品数量统计';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('ip_white_list','','IP白名单');",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='Idcsmart';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.1' WHERE `name`='IdcsmartSubAccount';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.3' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.3' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.5' WHERE `name`='MfDcim';",
    ];

    # DCIM接口
    $cloudServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `auto_sync_dcim_stock` tinyint(3) NOT NULL DEFAULT '0' COMMENT '自动同步DCIM库存(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `qty` int(11) NOT NULL DEFAULT '0' COMMENT '库存数量';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `ontrial` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否开启试用：0否默认，1是';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `ontrial_price` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '试用价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `ontrial_stock_control` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '试用库存开关：0否，1是';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `ontrial_qty` INT(11) NOT NULL DEFAULT 0 COMMENT '试用库存';";
    }

    # 魔方云接口
    $mfCloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfCloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ontrial` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否开启试用：0否默认，1是';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ontrial_price` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '试用价格';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ontrial_stock_control` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '试用库存开关：0否，1是';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ontrial_qty` INT(11) NOT NULL DEFAULT 0 COMMENT '试用库存';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `free_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '免费磁盘类型';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_disk` ADD COLUMN `free_size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '免费大小';";
        $sql[] = "UPDATE `idcsmart_module_mf_cloud_disk` SET `free_size`=`size` WHERE `is_free`=1 AND `type2`='data' AND `free_size`=0;";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_option` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 COMMENT '排序';";
    }

    // 增加模板
    notice_action_create([
        'name' => 'task_queue_exception',
        'name_lang' => '任务队列异常通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '任务队列异常通知',
            'content' => '您的任务队列异常，请及时查看！',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '任务队列异常通知',
            'content' => '您的任务队列异常，请及时查看！',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '任务队列异常通知',
            'title' => '[{system_website_name}] 任务队列异常通知',
            'content' => '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>任务队列异常通知</title>
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

            .box {
                height: auto;
            }

            .card {
                width: auto;
            }
        }

        @media (max-width: 280px) {
            .contimg img {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="box">
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]任务队列异常通知</h2>
<br /><strong>尊敬的管理员：</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的任务队列异常，请及时查看！</span><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>

</html>',
        ],
    ]);

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.7' where `setting`='system_version';");
}
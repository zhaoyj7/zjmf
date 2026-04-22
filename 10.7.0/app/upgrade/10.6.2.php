<?php
use think\facade\Db;

upgradeData1062();
function upgradeData1062()
{
	$sql = [
        "ALTER TABLE `idcsmart_product_group` ADD COLUMN `description` text NOT NULL COMMENT '描述';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `credit_remind` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '余额提醒：0关闭默认，1开启';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `credit_remind_amount` DECIMAL(10,2) NOT NULL DEFAULT '100.00' COMMENT '阈值';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `credit_remind_send` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否已提醒，1是，0否';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `upgrade_renew_cal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '升降级时是否按原价处理续费金额：1是，0否默认';",
	];

    # DCIM接口
    $cloudServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_bw_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽是否应用等级优惠订购(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_ip_num_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IP是否应用等级优惠订购(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `upstream_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上游是否隐藏(0=否,1=是)';";
    }

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `upstream_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上游是否隐藏(0=否,1=是)';";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    notice_action_create([
        'name' => 'clientarea_credit_remind',
        'name_lang' => '会员中心账户余额不足通知',
        'type' => 'client_account',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '会员中心账户余额不足通知',
            'content' => '尊敬的用户，您的账号(@var(username))余额仅剩@var(credit)，已低于提醒值@var(@var(credit_remind_amount))。请及时充值，以免影响正常使用。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '会员中心账户余额不足通知',
            'content' => '尊敬的用户，您的账号(@var(username))余额仅剩@var(credit)，已低于提醒值@var(@var(credit_remind_amount))。请及时充值，以免影响正常使用。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '会员中心账户余额不足提醒',
            'title' => '[{system_website_name}] 账户余额不足提醒',
            'content' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>余额不足提醒</title>
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
<h2 style="text-align: center;">[{system_website_name}] 余额不足提醒</h2>
<br /><strong>尊敬的用户，</strong> <span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> 您的账号<span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;"> <strong>{username}</strong></span>剩余余额为 <strong>{credit}</strong>，已低于提醒值<strong>{credit_remind_amount}</strong>。 <br /></span> <br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;"> {send_time} </span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body>
</html>',
        ],
    ]);

    Db::execute("update `idcsmart_configuration` set `value`='10.6.2' where `setting`='system_version';");
}
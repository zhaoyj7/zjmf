<?php
use think\facade\Db;

upgradeData1067();
function upgradeData1067()
{
	$sql = [
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.7' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.3' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.7' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.5' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.5' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='AliPayDmf';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='UserCustom';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='WxPay';",
	];

    $idcsmartRenewPlugin = Db::name('plugin')->where('name', 'IdcsmartWithdraw')->find();
    if (!empty($idcsmartRenewPlugin)){
        notice_action_delete('cash_withdrawal_apply_notice');
        notice_action_create([
            'name' => 'cash_withdrawal_apply_notice',
            'name_lang' => '用户提现申请',
            'type' => 'order_pay',
            'sms_name' => 'Idcsmart',
            'sms_template' => [
                'title' => '用户提现申请通知',
                'content' => '用户【@var(client_name)】提交了提现申请，金额：@var(withdraw_amount)，方式：@var(withdraw_method)，请及时处理。'
            ],
            'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '用户提现申请通知',
                'content' => '用户【@var(client_name)】提交了提现申请，金额：@var(withdraw_amount)，方式：@var(withdraw_method)，请及时处理。'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '用户提现申请通知',
                'title' => '[{system_website_name}]用户提现申请通知',
                'content' => '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .info-table{ width: 100%; margin: 20px 0; border-collapse: collapse; } .info-table td{ padding: 8px 12px; border-bottom: 1px solid #f0f0f0; } .info-table td:first-child{ color: #666; width: 120px; } .info-table td:last-child{ color: #333; font-weight: 500; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>
<div class="box">
<div class="logo_top"><img src="{system_logo_url}" alt="" /></div>
<div class="card">
<h2 style="text-align: center;">[{system_website_name}]用户提现申请通知</h2>
<br /><strong>尊敬的管理员</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 35px;">您好！有新的用户提现申请，请及时处理。</span></div>
<div class="card">
<table class="info-table">
<tr>
<td>申请编号：</td>
<td>#{withdraw_id}</td>
</tr>
<tr>
<td>用户名称：</td>
<td>{client_name}</td>
</tr>
<tr>
<td>提现金额：</td>
<td style="color: #F75697; font-size: 1.2rem; font-weight: bold;">{withdraw_amount}</td>
</tr>
<tr>
<td>提现方式：</td>
<td>{withdraw_method}</td>
</tr>
<tr>
<td>提现账号：</td>
<td>{withdraw_account}</td>
</tr>
<tr>
<td>申请时间：</td>
<td>{apply_time}</td>
</tr>
</table><br />&nbsp; <span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span><br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{send_time}</span></div>
<ul class="banquan">
<li>{system_website_name}</li>
</ul>
</div>
</body> </html>'
            ],
        ]);
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.7' where `setting`='system_version';");

    // 清除缓存
    if (class_exists("\\app\\common\\logic\\CacheLogic")){
        \app\common\logic\CacheLogic::clearAllCache();
    }
}
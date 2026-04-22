<?php
use think\facade\Db;

upgradeData1055();
function upgradeData1055()
{
    // 先把退款的SQL插入了来
    $sql = [
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `refund_type` varchar(30) NOT NULL DEFAULT 'order' COMMENT '退款类型(order=订单退款,addon=插件退款)';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `refund_credit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款余额部分';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `refund_gateway` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款渠道部分';",
        "ALTER TABLE `idcsmart_refund_record` ADD INDEX `host_id`(`host_id`);",
        "UPDATE `idcsmart_refund_record` SET `refund_credit`=`amount` WHERE `type`='credit';",
        "UPDATE `idcsmart_refund_record` SET `refund_gateway`=`amount` WHERE `type` IN ('original','transaction');",
        "ALTER TABLE `idcsmart_order` ADD COLUMN `is_refund` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否正在订单退款(0=否,1=是)';",
        "ALTER TABLE `idcsmart_order` ADD COLUMN `refund_gateway_to_credit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '渠道退到余额部分';",
        "ALTER TABLE `idcsmart_transaction` ADD COLUMN `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID';",
        "ALTER TABLE `idcsmart_transaction` ADD INDEX `host_id`(`host_id`);",
        "UPDATE `idcsmart_order` SET `is_refund`=1 WHERE `id` IN (SELECT `order_id` FROM `idcsmart_refund_record` WHERE `status` IN ('Pending','Refunding'));",
    ];

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

	$sql = [
        "ALTER TABLE `idcsmart_host` ADD COLUMN `failed_action` varchar(50) NOT NULL DEFAULT '' COMMENT '失败动作(create=开通失败,suspend=暂停失败,terminate=删除失败)';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `failed_action_times` tinyint(7) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `failed_action_need_handle` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败动作需要手动处理(0=否,1=是)';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `failed_action_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '失败动作原因';",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `failed_action_trigger_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发时间';",
        "CREATE TABLE `idcsmart_notice` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '通知信息',
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` TEXT COMMENT '内容',
  `attachment` TEXT COMMENT '附件，逗号分隔',
  `accept_time` INT(11) NOT NULL DEFAULT '0' COMMENT '接收时间',
  `read` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否已读：1是，0否',
  `priority` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '优先级：0普通，1高',
  `type` VARCHAR(25) NOT NULL DEFAULT '' COMMENT '消息类型：idcsmart官方通知，system系统通知',
  `rel_id` INT(11) NOT NULL DEFAULT '0' COMMENT '关联ID，消息类型是idcsmart时，表示官方消息ID',
  `create_time` INT(11) NOT NULL DEFAULT '0',
  `update_time` INT(11) NOT NULL DEFAULT '0',
  `is_delete` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否已删除，软删除',
  `delete_time` INT(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='TpCaptcha';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='Idcsmart';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='Smtp';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='AliPayDmf';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='Idcsmartmail';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='UserCustom';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='WxPay';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartFileDownload';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartNews';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='Idcsmartali';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartSshKey';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartHelp';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartSubAccount';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartAnnouncement';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.0' WHERE `name`='LocalOss';",
	];

    # dcim接口
    $dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($dcimServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_host_link` ADD COLUMN `reset_flow_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '流量重置时间';";
    }

    // 是否有子账户插件
    $idcsmartSubAccountPlugin = Db::name('plugin')->where('name', 'IdcsmartSubAccount')->find();
    if (!empty($idcsmartSubAccountPlugin)){
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('client_list_where_query_append', {$idcsmartSubAccountPlugin['status']}, 'IdcsmartSubAccount', 'addon', 0);";
    }
    // 是否有退款插件
    $idcsmartRefundPlugin = Db::name('plugin')->where('name', 'IdcsmartRefund')->find();
    if (!empty($idcsmartRefundPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund` ADD COLUMN `refund_record_id` int(10) NOT NULL DEFAULT '0' COMMENT '退款记录ID';";

        $IdcsmartRefundModel = new \addon\idcsmart_refund\model\IdcsmartRefundModel();
        $idcsmartRefund = $IdcsmartRefundModel
                        ->field('id,client_id,amount,create_time,status,reject_reason,update_time,host_id')
                        ->where('amount', '<>', -1)
                        ->select();

        // 同状态映射
        $status = [
            'Refund'    => 'Refunded',
        ];

        // 已退款的产品ID
        $hostId = [];

        // 同步原插件中的退款记录
        $RefundRecordModel = new \app\common\model\RefundRecordModel();
        foreach($idcsmartRefund as $v){
            $refundRecordId = $RefundRecordModel->insertGetId([
                'order_id'          => 0,
                'client_id'         => $v['client_id'],
                'admin_id'          => 0,
                'type'              => 'credit',
                'transaction_id'    => 0,
                'amount'            => $v['amount'],
                'create_time'       => $v['create_time'],
                'status'            => $status[ $v['status'] ] ?? $v['status'],
                'reason'            => $v['reject_reason'],
                'refund_time'       => $v['status'] == 'Refund' ? $v['update_time'] : 0,
                'host_id'           => $v['host_id'],
                'refund_type'       => 'addon',
            ]);
            $sql[] = "UPDATE `idcsmart_addon_idcsmart_refund` SET `refund_record_id`={$refundRecordId} WHERE `id`={$v['id']};";

            if($v['status'] == 'Refund'){
                $hostId[] = $v['host_id'];
            }
        }
        // 重新计算这些产品对应订单
        if(!empty($hostId)){
            $OrderModel = new \app\common\model\OrderModel();

            $orders = $OrderModel
                    ->alias('o')
                    ->field('o.*')
                    ->join('order_item oi', 'oi.order_id=o.id')
                    ->whereIn('oi.host_id', $hostId)
                    ->whereIn('o.status', ['Paid','Refunded'])
                    ->group('o.id')
                    ->select();
            foreach($orders as $order){
                $orderRefund = $OrderModel->orderRefundIndex([
                    'order' => $order,
                ]);

                // 修改退款总额
                $credit = max( bcsub($order['credit'], $orderRefund['refund_addon'], 2), 0);
                $refundAmount = bcadd($orderRefund['refund_credit'], $orderRefund['refund_gateway'], 2);

                $sql[] = "UPDATE `idcsmart_order` SET `credit`='{$credit}',`status`='Refunded',`refund_amount`='{$refundAmount}' WHERE `id`='{$order['id']}';";
            }
        }
    }

    // 增加模板
    notice_action_create([
        'name' => 'host_failed_action',
        'name_lang' => '待处理产品通知',
        'sms_name' => 'Idcsmart',
        'sms_template' => [
            'title' => '待处理产品通知',
            'content' => '您有@var(wait_handle_host_num)个待手动处理的产品，请及时处理。',
        ],
        'sms_global_name' => 'Idcsmart',
        'sms_global_template' => [
            'title' => '待处理产品通知',
            'content' => '您有@var(wait_handle_host_num)个待手动处理的产品，请及时处理。',
        ],
        'email_name' => 'Smtp',
        'email_template' => [
            'name' => '待处理产品通知',
            'title' => '[{system_website_name}] 待处理产品通知',
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
<h2 style="text-align: center;">[{system_website_name}] 待处理产品通知</h2>
<br /><strong>尊敬的用户，</strong> <br /><span style="margin: 0; padding: 0; display: inline-block; margin-top: 20px;">您有{wait_handle_host_num}个待手动处理的产品，请及时处理。</span> <br /><br /><span style="margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;"> <strong>{system_website_name}</strong> </span> <br /><span style="margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;">{send_time}</span></div>
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

    // 游客可见商品
    $productIds = Db::name('product')->column('id');
    updateConfiguration('tourist_visible_product_ids', implode(',', $productIds));

    Db::execute("update `idcsmart_configuration` set `value`='10.5.5' where `setting`='system_version';");
}
<?php
use think\facade\Db;

upgradeData1065();
function upgradeData1065()
{
	$sql = [
        "ALTER TABLE `idcsmart_theme_config` ADD COLUMN `display_time` INT(11) NOT NULL DEFAULT 1 COMMENT '轮播时间，单位分钟，xx分钟轮播切换';",
        "CREATE TABLE `idcsmart_order_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `content` text NOT NULL COMMENT '记录内容',
  `attachment` text NOT NULL COMMENT '附件，多个用逗号分隔',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单信息记录表';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.5' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.2' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='AliPayDmf';",
        "UPDATE `idcsmart_plugin` SET `version`='2.3.2' WHERE `name`='WxPay';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.4' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.3' WHERE `name`='MfDcim';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.3' WHERE `name`='PromoCode';",
    ];

    $mfcloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if (!empty($mfcloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `disk_range_limit_switch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '磁盘大小购买限制开关(0=关闭,1=开启)';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `disk_range_limit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '磁盘大小购买限制';";
    }

    $ticketPlugin = Db::name('plugin')->where('name', 'IdcsmartTicket')->find();
    if (!empty($ticketPlugin)){
        $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) VALUES ('append_send_param','{$ticketPlugin['status']}','IdcsmartTicket','addon',0);";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket_reply` ADD COLUMN `quote_reply_id` INT(11) NOT NULL DEFAULT 0 COMMENT '引用的回复ID，0表示未引用' AFTER `upstream_ticket_reply_id`;";
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_ticket_reply` ADD INDEX `idx_quote_reply_id` (`quote_reply_id`);";
    }

    $refundPlugin = Db::name('plugin')->where('name', 'IdcsmartRefund')->find();
    if (!empty($refundPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `full_refund_days` INT(11) NOT NULL DEFAULT '0' COMMENT '全额退款天数,0表示不启用' AFTER `range`;";
    }

    // 移动原来订单备注到订单信息记录
    $order = Db::name('order')->field("id,notes")->where('notes', '<>', '')->select()->toArray();
    foreach($order as $v){
        $sql[] = "INSERT INTO `idcsmart_order_record`(`order_id`, `admin_id`, `content`, `attachment`, `create_time`) VALUES ({$v['id']}, 0, '{$v['notes']}', '', 0);";
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $adminAuth = [
        [
            'title' => 'auth_order_detail_info_record',
            'url' => '',
            'description' => '信息记录', # 权限描述
            'parent' => 'auth_business_order_detail', # 父权限
            'auth_rule' => [],
            'child' => [
                [
                    'title' => 'auth_order_detail_info_record_view',
                    'url' => 'order_records',
                    'description' => '查看页面',
                    'auth_rule' => [
                        'app\admin\controller\OrderRecordController::list',
                        'app\admin\controller\OrderController::index',
                        'app\admin\controller\OrderController::orderList',
                    ],
                ],
                [
                    'title' => 'auth_order_detail_info_record_create_record',
                    'url' => '',
                    'description' => '新增记录',
                    'auth_rule' => [
                        'app\admin\controller\OrderRecordController::create',
                    ],
                ],
                [
                    'title' => 'auth_order_detail_info_record_update_record',
                    'url' => '',
                    'description' => '编辑记录',
                    'auth_rule' => [
                        'app\admin\controller\OrderRecordController::update',
                    ],
                ],
                [
                    'title' => 'auth_order_detail_info_record_delete_record',
                    'url' => '',
                    'description' => '删除记录',
                    'auth_rule' => [
                        'app\admin\controller\OrderRecordController::delete',
                    ],
                ],
            ]
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($adminAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }
    $AuthModel->deleteSystemAuth(['auth_business_order_detail_notes','auth_business_order_detail_notes_view','auth_business_order_detail_notes_save_notes']);

    Db::execute("update `idcsmart_configuration` set `value`='10.6.5' where `setting`='system_version';");
}
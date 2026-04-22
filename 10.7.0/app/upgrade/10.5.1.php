<?php
use think\facade\Db;

upgradeData1051();
function upgradeData1051()
{
	$sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_system_log_delete_swhitch','0','系统日志自动删除开关');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_system_log_delete_day','0','系统日志创建X天后自动删除');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_sms_log_delete_swhitch','0','短信日志自动删除开关');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_sms_log_delete_day','0','短信日志创建X天后自动删除');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_email_log_delete_swhitch','0','邮件日志自动删除开关');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cron_email_log_delete_day','0','邮件日志创建X天后自动删除');",
        "CREATE TABLE `idcsmart_sms_code_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '短信验证码日志',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '验证码类型',
  `phone_code` int(11) NOT NULL DEFAULT '44' COMMENT '国际区号',
  `phone` varchar(100) NOT NULL DEFAULT '' COMMENT '手机号',
  `user_type` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人类型client用户admin管理员system系统cron定时任务',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `user_name` varchar(100) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `abnormal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否异常',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口号',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        "ALTER TABLE `idcsmart_web_nav` ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'zh-cn' COMMENT '语言';",
        "ALTER TABLE `idcsmart_bottom_bar_group` ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'zh-cn' COMMENT '语言';",
        "ALTER TABLE `idcsmart_bottom_bar_nav` ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'zh-cn' COMMENT '语言';",
        "ALTER TABLE `idcsmart_seo` ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'zh-cn' COMMENT '语言';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `receive_sms` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接收短信';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `receive_email` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接收邮件';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('task_fail_retry_open','1','任务是否重试');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('task_fail_retry_times','3','任务重试次数');",
        "ALTER TABLE `idcsmart_task_wait` ADD COLUMN `version` INT(11) NOT NULL DEFAULT '0' COMMENT '版本号，乐观锁';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `show_base_info` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '产品列表是否展示基础信息：1是默认，0否';",
	];

    // 是否有退款插件
    $idcsmartRefundPlugin = Db::name('plugin')->where('name', 'IdcsmartRefund')->find();
    if (!empty($idcsmartRefundPlugin)){
        $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `action` VARCHAR(25) NOT NULL DEFAULT 'Suspend' COMMENT '退款后产品操作：Suspend暂停，Terminate删除';";
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='IdcsmartRefund';";
    }

    // 修改api方式的商品接口类型
    $proudctId = Db::query("SELECT `product_id` FROM `idcsmart_upstream_product` WHERE `mode`='only_api';");
    $proudctId = array_column($proudctId, 'product_id');
    if(!empty($proudctId)){
        $whereProduct = '(' . implode(',', $proudctId) . ')';

        $sql[] = "UPDATE `idcsmart_product` SET `type`='server',`rel_id`=0 WHERE `id` IN {$whereProduct};";
        $sql[] = "UPDATE `idcsmart_host` SET `server_id`=0 WHERE `product_id` IN {$whereProduct};";
    }

    # 云接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
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
            if (isset($configData['gpu_name']) && isset($configData['gpu_num'])){
                $tempData['gpu'] = $configData['gpu_name'] . '*' . $configData['gpu_num'];
            }
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

    Db::execute("update `idcsmart_configuration` set `value`='10.5.1' where `setting`='system_version';");
}
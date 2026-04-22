<?php
use think\facade\Db;

upgradeData1048();
function upgradeData1048()
{
	$sql = [
        "ALTER TABLE `idcsmart_product` ADD COLUMN `custom_host_name` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自定义主机标识开关(0=关闭,1=开启)';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `custom_host_name_prefix` varchar(10) NOT NULL DEFAULT '' COMMENT '自定义主机标识前缀';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `custom_host_name_string_allow` varchar(100) NOT NULL DEFAULT '' COMMENT '允许的字符串(number=数字,upper=大写字母,lower=小写字母)';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `custom_host_name_string_length` int(11) NOT NULL DEFAULT '0' COMMENT '字符串长度';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `email_notice_setting` text NOT NULL COMMENT '邮件通知管理';",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `sms_notice_setting` text NOT NULL COMMENT '短信通知管理';",
        "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('admin_enforce_safe_method_scene', 'all', 0, 0, '后台强制安全选项场景(all=全部,client_delete=用户删除,update_client_status=用户停启用,host_operate=产品相关操作,order_delete=订单删除,clear_order_recycle=清空回收站,plugin_uninstall_disable=插件卸载/禁用),多个英文逗号分隔');",
        "ALTER TABLE `idcsmart_host_addition` ADD COLUMN `username` varchar(255) NOT NULL DEFAULT '' COMMENT '实例用户名';",
        "ALTER TABLE `idcsmart_host_addition` ADD COLUMN `password` varchar(255) NOT NULL DEFAULT '' COMMENT '实例密码';",
        "ALTER TABLE `idcsmart_host_addition` ADD COLUMN `port` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '端口';",
        "UPDATE `idcsmart_host_addition` SET `username`='administrator' WHERE `image_icon`='Windows';",
        "UPDATE `idcsmart_host_addition` SET `username`='root' WHERE `image_icon`<>'Windows' AND `image_icon`<>'';",
        "DELETE FROM `idcsmart_plugin_hook` WHERE `name`='before_update_amount' AND `plugin`='IdcsmartInvoice';",
        "UPDATE `idcsmart_sms_template` SET `title`='产品模块操作失败通知' WHERE `title`='产品模块操作';",
        "UPDATE `idcsmart_email_template` SET `name`='产品模块操作失败通知' WHERE `name`='产品模块操作';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.0.2' WHERE `name`='IdcsmartCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.2' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.3' WHERE `name`='PromoCode';",
	];

	// 是否有云的接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `in_bw` varchar(20) NOT NULL DEFAULT '' COMMENT '流入带宽';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `traffic_type` tinyint(3) unsigned NOT NULL DEFAULT '3' COMMENT '流量计费方向(1=进,2=出,3=进+出)';";
        $sql[] = "UPDATE `idcsmart_module_mf_cloud_recommend_config` SET `in_bw`=`bw`;";
        $sql[] = "UPDATE `idcsmart_host_addition` ha JOIN `idcsmart_module_mf_cloud_host_link` hl on ha.`host_id`=hl.`host_id` SET ha.`password`=hl.`password` WHERE hl.`password`<>'' AND ha.`password`='';";

        // 匹配下流量线路
        $data = Db::name('module_mf_cloud_recommend_config')
                ->field('rc.id,o.other_config')
                ->alias('rc')
                ->join('module_mf_cloud_option o', 'rc.product_id=o.product_id AND rc.line_id=o.rel_id AND rc.flow=o.value')
                ->where('rc.flow', '>', 0)
                ->where('o.rel_type', 3)
                ->select();
        foreach($data as $v){
            $v['other_config'] = json_decode($v['other_config'], true);
            $sql[] = "UPDATE `idcsmart_module_mf_cloud_recommend_config` SET `bw`='{$v['other_config']['out_bw']}',`in_bw`='{$v['other_config']['in_bw']}',`traffic_type`='{$v['other_config']['traffic_type']}' WHERE `id`='{$v['id']}';";
        }
        // 匹配下带宽线路
        $data = Db::name('module_mf_cloud_recommend_config')
                ->field('rc.id,o.other_config')
                ->alias('rc')
                ->join('module_mf_cloud_line l', 'rc.line_id=l.id')
                ->join('module_mf_cloud_option o', 'rc.product_id=o.product_id AND rc.line_id=o.rel_id AND ((rc.bw=o.value) OR (rc.bw>=o.min_value AND rc.bw<=o.max_value))')
                ->where('l.bill_type', 'bw')
                ->where('o.rel_type', 2)
                ->select();
        foreach($data as $v){
            $v['other_config'] = json_decode($v['other_config'], true);
            if(!isset($v['other_config']['in_bw']) || !is_numeric($v['other_config']['in_bw'])){
                continue;
            }
            $sql[] = "UPDATE `idcsmart_module_mf_cloud_recommend_config` SET `in_bw`='{$v['other_config']['in_bw']}' WHERE `id`='{$v['id']}';";
        }
        // 修改云高级规则数据
        $limitRule = Db::name('module_mf_cloud_limit_rule')
                    ->field('id,product_id,rule,result')
                    ->where('id', '>', 0)
                    ->select();
        // 缓存内存配置类型
        $memoryType = [];
        foreach($limitRule as $v){
            $v['rule'] = json_decode($v['rule'], true);
            $ruleRule = $v['rule'];
            // 是否有内存,范围转选项
            if(isset($v['rule']['memory']) && isset($v['rule']['memory']['min'])){
                if(!isset($memoryType[$v['product_id']])){
                    $memoryType[$v['product_id']] = Db::name('module_mf_cloud_option')->where('product_id', $v['product_id'])->where('rel_type', 1)->value('type') ?? '';
                }
                // 仅当单选时
                if($memoryType[$v['product_id']] == 'radio'){
                    $memory = Db::name('module_mf_cloud_option')
                            ->where('product_id', $v['product_id'])
                            ->where('rel_type', 1)
                            ->where('value', '>=', $v['rule']['memory']['min'] ?: 0)
                            ->where('value', '<=', $v['rule']['memory']['max'] ?: 99999999)
                            ->order('value', 'asc')
                            ->column('value');
                    // 获取范围内的内存
                    $ruleRule['memory'] = [
                        'value' => $memory ?? [],
                        'opt'   => $v['rule']['memory']['opt'],
                    ];
                }
            }
            $v['result'] = json_decode($v['result'], true);
            $ruleResult = $v['result'];
            if(isset($v['result']['cpu']) && isset($v['result']['cpu']['opt'])){
                $ruleResult['cpu'] = [];
                $ruleResult['cpu'][] = $v['result']['cpu'];
            }
            if(isset($v['result']['memory']) && isset($v['result']['memory']['opt'])){
                if(!isset($memoryType[$v['product_id']])){
                    $memoryType[$v['product_id']] = Db::name('module_mf_cloud_option')->where('product_id', $v['product_id'])->where('rel_type', 1)->value('type') ?? '';
                }
                // 仅当单选时
                if($memoryType[$v['product_id']] == 'radio'){
                    $memory = Db::name('module_mf_cloud_option')
                            ->where('product_id', $v['product_id'])
                            ->where('rel_type', 1)
                            ->where('value', '>=', $v['result']['memory']['min'] ?: 0)
                            ->where('value', '<=', $v['result']['memory']['max'] ?: 99999999)
                            ->order('value', 'asc')
                            ->column('value');
                    // 获取范围内的内存
                    $v['result']['memory'] = [
                        'value' => $memory ?? [],
                        'opt'   => $ruleResult['memory']['opt'],
                    ];
                }
                $ruleResult['memory'] = [];
                $ruleResult['memory'][] = $v['result']['memory'];
            }
            if(isset($v['result']['image']) && isset($v['result']['image']['opt'])){
                $ruleResult['image'] = [];
                $ruleResult['image'][] = $v['result']['image'];
            }
            $ruleRule = json_encode($ruleRule);
            $ruleMd5 = md5($ruleRule);

            $sql[] = "UPDATE `idcsmart_module_mf_cloud_limit_rule` SET `rule`='{$ruleRule}',`result`='".json_encode($ruleResult)."',rule_md5='{$ruleMd5}' WHERE `id`='{$v['id']}';";
        }
    }

    // 是否有DCIM的接口
    $dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($dcimServer)){
        $sql[] = "UPDATE `idcsmart_host_addition` ha JOIN `idcsmart_module_mf_dcim_host_link` hl on ha.`host_id`=hl.`host_id` SET ha.`password`=hl.`password` WHERE hl.`password`<>'' AND ha.`password`='';";

        // 修改DCIM高级规则数据
        $limitRule = Db::name('module_mf_dcim_limit_rule')
                    ->field('id,result')
                    ->where('id', '>', 0)
                    ->select();
        foreach($limitRule as $v){
            $v['result'] = json_decode($v['result'], true);
            $ruleResult = $v['result'];
            if(isset($v['result']['model_config']) && isset($v['result']['model_config']['opt'])){
                $ruleResult['model_config'] = [];
                $ruleResult['model_config'][] = $v['result']['model_config'];
            }
            if(isset($v['result']['bw']) && isset($v['result']['bw']['opt'])){
                $ruleResult['bw'] = [];
                $ruleResult['bw'][] = $v['result']['bw'];
            }
            if(isset($v['result']['flow']) && isset($v['result']['flow']['opt'])){
                $ruleResult['flow'] = [];
                $ruleResult['flow'][] = $v['result']['flow'];
            }
            if(isset($v['result']['image']) && isset($v['result']['image']['opt'])){
                $ruleResult['image'] = [];
                $ruleResult['image'][] = $v['result']['image'];
            }
            $sql[] = "UPDATE `idcsmart_module_mf_dcim_limit_rule` SET `result`='".json_encode($ruleResult)."' WHERE `id`='{$v['id']}';";
        }
    }

    $customHostNameAuth = [
        [
            'title' => 'auth_product_detail_custom_host_name',
            'url' => 'product_custom_name',
            'description' => '自定义标识', # 权限描述
            'parent' => 'auth_product_detail', # 父权限 
            'auth_rule' => [
                'app\admin\controller\ProductController::getCustomHostName',
                'app\admin\controller\ProductController::saveCustomHostName',
            ],
        ],
        [
            'title' => 'auth_product_management_notice_setting',
            'url' => 'product_notice_manage',
            'description' => '通知管理', # 权限描述
            'parent' => 'auth_product_management', # 父权限 
            'auth_rule' => [
                'app\admin\controller\ProductController::smsNoticeSetting',
                'app\admin\controller\ProductController::updateSmsNoticeSetting',
                'app\admin\controller\ProductController::emailNoticeSetting',
                'app\admin\controller\ProductController::updateEmailNoticeSetting',
            ],
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($customHostNameAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    // $ProductModel = new \app\common\model\ProductModel();
    // $noticeSetting = $ProductModel->noticeSetting;

    // $NoticeSettingModel = new \app\common\model\NoticeSettingModel();
    // $settingList = $NoticeSettingModel->field('name,sms_enable,email_enable')->select()->toArray();
    // foreach ($settingList as $k => $v) {
    //     if(!in_array($v['name'], $ProductModel->noticeSetting)){
    //         unset($settingList[$k]);
    //     }
    // }
    // $settingList = array_values($settingList);

    // $product = $ProductModel->field('id,creating_notice_sms,created_notice_sms,creating_notice_mail,created_notice_mail')->select()->toArray();
    // foreach ($product as $value) {
    //     $smsNoticeSetting = [];
    //     $emailNoticeSetting = [];
    //     foreach ($settingList as  $v) {
    //         if($v['name']=='host_pending'){
    //             $smsNoticeSetting[$v['name']] = $value['creating_notice_sms'];
    //             $emailNoticeSetting[$v['name']] = $value['creating_notice_mail'];
    //         }else if($v['name']=='host_active'){
    //             $smsNoticeSetting[$v['name']] = $value['creating_notice_sms'];
    //             $emailNoticeSetting[$v['name']] = $value['creating_notice_mail'];
    //         }else{
    //             $smsNoticeSetting[$v['name']] = $v['sms_enable'];
    //             $emailNoticeSetting[$v['name']] = $v['email_enable'];
    //         }
    //     }
    //     $sql[] = "UPDATE `idcsmart_product` SET `sms_notice_setting`='".json_encode($smsNoticeSetting)."',`email_notice_setting`='".json_encode($emailNoticeSetting)."' WHERE `id`={$value['id']}";
    // }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.4.8' where `setting`='system_version';");
}
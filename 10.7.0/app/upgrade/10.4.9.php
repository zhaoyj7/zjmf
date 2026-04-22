<?php
use think\facade\Db;

upgradeData1049();
function upgradeData1049()
{
	$sql = [
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('recharge_notice','0','充值提示开关');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('recharge_money_notice_content','','充值金额提示内容');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('recharge_pay_notice_content','','充值支付提示');",
        "ALTER TABLE `idcsmart_web_nav` ADD COLUMN `blank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '打开新窗口0否1是';",
        "ALTER TABLE `idcsmart_bottom_bar_nav` ADD COLUMN `blank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '打开新窗口0否1是';",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('cart_theme_mobile','default',0,0,'购物车手机端主题');",
        "ALTER TABLE `idcsmart_product` ADD COLUMN `renew_rule` VARCHAR(25) NOT NULL DEFAULT 'due' COMMENT '续费规则：due到期日，current当前时间';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.4' WHERE `name`='IdcsmartTicket';",
	];

    $AuthModel = new \app\admin\model\AuthModel();
    $AuthRuleModel = new \app\admin\model\AuthRuleModel();
    $AuthRuleLinkModel = new \app\admin\model\AuthRuleLinkModel();
	$auth = $AuthModel->where('title', 'auth_template_controller_nav')->find();
    if(!empty($auth)){
        $authRule = $AuthRuleModel->create([
            'name' => 'app\admin\controller\WebNavController::blank',
            'title' => '',
        ]);

        $AuthRuleLinkModel->create([
            'auth_rule_id' => $authRule->id,
            'auth_id' => $auth->id,
        ]);
    }
    $auth = $AuthModel->where('title', 'auth_template_controller_bottom_bar')->find();
    if(!empty($auth)){
        $authRule = $AuthRuleModel->create([
            'name' => 'app\admin\controller\BottomBarNavController::blank',
            'title' => '',
        ]);

        $AuthRuleLinkModel->create([
            'auth_rule_id' => $authRule->id,
            'auth_id' => $auth->id,
        ]);
    }


	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.4.9' where `setting`='system_version';");
}
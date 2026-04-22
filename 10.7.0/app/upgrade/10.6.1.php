<?php
use think\facade\Db;

upgradeData1054();
function upgradeData1054()
{
	$sql = [
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartAnnouncement';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartFileDownload';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartHelp';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartNews';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartSshKey';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartSubAccount';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='3.0.0' WHERE `name`='MfDcim';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES('clientarea_theme_color','default','会员中心主题色');",
	];

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.6.1' where `setting`='system_version';");
}
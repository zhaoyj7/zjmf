<?php
use think\facade\Db;

upgradeData1053();
function upgradeData1053()
{
	$sql = [
        "ALTER TABLE `idcsmart_host` ADD COLUMN `transfer_time` int(11) NOT NULL DEFAULT '0' COMMENT '产品最新转移时间';",
        "ALTER TABLE `idcsmart_plugin` ADD `description_url` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '说明文档';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%BD%93%E9%9D%A2%E4%BB%98.html' WHERE `name`='AliPayDmf';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E7%BD%91%E9%A1%B5%E6%94%AF%E4%BB%98.html' WHERE `name`='AliPayH5';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/Easy-USDT.html' WHERE `name`='Epusdt';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E6%94%AF%E4%BB%98%E5%AE%9D%E5%9B%BD%E9%99%85%E6%94%AF%E4%BB%98.html' WHERE `name`='http://doc.idcsmart.com/Easy-USDT.html';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/GoAllPay%E9%98%BF%E9%87%8C.html' WHERE `name`='GoallpayAli';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/GoAllPay%E9%93%B6%E8%81%94.html' WHERE `name`='GoallpayUnionpay';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/GoAllPay%E5%BE%AE%E4%BF%A1.html' WHERE `name`='GoallpayWechat';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/OCGC%E6%94%AF%E4%BB%98.html' WHERE `name`='OcgcPay';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/PayPal%E6%94%AF%E4%BB%98.html' WHERE `name`='Paypal';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/Stripe%E8%81%9A%E5%90%88%E6%94%AF%E4%BB%98.html' WHERE `name`='Stripe';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E7%BA%BF%E4%B8%8B%E6%94%AF%E4%BB%98.html' WHERE `name`='UserCustom';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E5%BE%AE%E4%BF%A1H5%E6%94%AF%E4%BB%98.html' WHERE `name`='WxPayH5';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E9%92%89%E9%92%89%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Dingtalk';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/Google%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Google';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/QQ%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Qq';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E4%BC%81%E4%B8%9A%E5%BE%AE%E4%BF%A1%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Qyweixin';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E5%BE%AE%E5%8D%9A%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Weibo';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E5%BE%AE%E4%BF%A1%E4%B8%89%E6%96%B9%E7%99%BB%E5%BD%95.html' WHERE `name`='Weixin';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E6%99%BA%E7%AE%80%E9%AD%94%E6%96%B9%E8%8A%9D%E9%BA%BB%E4%BF%A1%E7%94%A8.html' WHERE `name`='Idcsmartali';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E9%98%BF%E9%87%8C%E8%BA%AB%E4%BB%BD%E8%AF%81%E4%BA%8C%E8%A6%81%E7%B4%A0.html' WHERE `name`='Alitwo';",
        "UPDATE `idcsmart_plugin` SET `description_url`='http://doc.idcsmart.com/%E9%98%BF%E9%87%8C%E8%BA%AB%E4%BB%BD%E8%AF%81%E4%B8%89%E8%A6%81%E7%B4%A0.html' WHERE `name`='Phonethree';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='IdcsmartWithdraw';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.1' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='AliPayDmf';",
        "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='UserCustom';",
        "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='WxPay';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.3' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.3' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.2.3' WHERE `name`='MfDcim';",
        "ALTER TABLE `idcsmart_product` MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'server' COMMENT '关联类型:server,server_group';",
	];

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.3' where `setting`='system_version';");
}
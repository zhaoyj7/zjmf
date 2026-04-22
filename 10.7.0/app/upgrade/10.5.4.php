<?php
use think\facade\Db;

upgradeData1054();
function upgradeData1054()
{
	$sql = [
	];

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.5.4' where `setting`='system_version';");
}
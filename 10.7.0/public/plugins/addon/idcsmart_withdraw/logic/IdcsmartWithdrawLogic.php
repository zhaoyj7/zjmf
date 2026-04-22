<?php
namespace addon\idcsmart_withdraw\logic;

use addon\idcsmart_withdraw\IdcsmartWithdraw;

class IdcsmartWithdrawLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = require_once dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartWithdraw())->getConfig();

        $config = array_merge($fileConfig,$dbConfig);

        return isset($config[$name])?$config[$name]:$config;
    }
}
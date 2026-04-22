#!/usr/bin/env php
<?php
/**
 * 独立通知任务队列入口文件
 * 使用方式: php task_notice.php
 * Supervisor配置: command=php /path/to/cron/task_notice.php
 */

// 加载自动加载文件
use think\App;

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';
define('IDCSMART_ROOT',dirname(__DIR__ ). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ). '/public/'); # 网站入口目录
define('UPLOAD_DEFAULT',WEB_ROOT . '/upload/common/default/'); # 文件保存默认路径
// 应用初始化
$App = new App();
// 修改缓存目录
$App->setRuntimePath( IDCSMART_ROOT . 'runtime_cli/');

$output = $App->console->call('task_notice');
echo $output->fetch();

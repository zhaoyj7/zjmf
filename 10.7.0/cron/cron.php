<?php
namespace think;

// 命令行入口文件
// 加载基础文件
require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';
define('IDCSMART_ROOT',dirname(__DIR__ ). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ). '/public/'); # 网站入口目录
define('UPLOAD_DEFAULT',WEB_ROOT . '/upload/common/default/'); # 文件保存默认路径

// 应用初始化
$App = new App();
// 修改缓存目录
$App->setRuntimePath( IDCSMART_ROOT . 'runtime_cli/');

$output = $App->console->call('cron');
echo $output->fetch();
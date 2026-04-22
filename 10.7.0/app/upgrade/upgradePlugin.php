<?php
use think\facade\Db;

upgradePlugin();
function upgradePlugin()
{
	$url = "https://license.soft13.idcsmart.com/app/api/auth_rc_plugin";
    $license = configuration('system_license');//系统授权码
    $ip = $_SERVER['SERVER_ADDR'];//服务器地址
    $arr = parse_url($_SERVER['HTTP_HOST']);
    $domain = isset($arr['host'])? ($arr['host'].(isset($arr['port']) ? (':'.$arr['port']) : '')) :$arr['path'];
    $type = 'finance';
    
    $systemVersion = Db::name('configuration')->field('name,version')->where('setting', 'system_version')->value('value');//系统当前版本
    $data = [
        'ip' => $ip,
        'domain' => $domain,
        'type' => $type,
        'license' => $license,
        'install_version' => $systemVersion,
        'request_time' => time(),
    ];
    $res = curl($url,$data,20,'POST');
    if($res['http_code'] == 200){
        $result = json_decode($res['content'], true);
    }

    $plugin_upgrade_log = IDCSMART_ROOT . "public/upgrade/plugin_upgrade.log";

    $log = [];

    file_put_contents($plugin_upgrade_log, json_encode($log));

    if(isset($result['status']) && $result['status']==200){
        $list = $result['data']['list'] ?? [];
        $plugin = Db::name('plugin')->field('name,version')->select()->toArray();
        $plugin = array_column($plugin, 'version', 'name');
        $themes = getThemeDirectories();
        foreach ($list as $key => $value) {
            if(isset($plugin[$value['uuid']]) && version_compare($value['version'], $plugin[$value['uuid']], '>')){
                $log[$value['uuid']] = $value['name'].'-------------下载中';
                file_put_contents($plugin_upgrade_log, json_encode($log));
                $PluginModel = new \app\admin\model\PluginModel();
                $res = $PluginModel->download(['id' => $value['id']]);
                if($res['status']==200){
                    $log[$value['uuid']] = $value['name'].'-------------下载完成';
                    file_put_contents($plugin_upgrade_log, json_encode($log));
                }else{
                    $log[$value['uuid']] = $value['name'].'-------------下载失败';
                    file_put_contents($plugin_upgrade_log, json_encode($log));
                }
            }elseif (!empty($value['type']) && $value['type']=='template'){
                if(in_array(strtolower($value['uuid']), $themes)){
                    $log[$value['uuid']] = $value['name'].'-------------下载中';
                    file_put_contents($plugin_upgrade_log, json_encode($log));
                    $PluginModel = new \app\admin\model\PluginModel();
                    $res = $PluginModel->download(['id' => $value['id']]);
                    if($res['status']==200){
                        $log[$value['uuid']] = $value['name'].'-------------下载完成';
                        file_put_contents($plugin_upgrade_log, json_encode($log));
                    }
                }
            }
        }
    }
}
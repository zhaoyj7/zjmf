<?php
namespace app\admin\controller;

use app\admin\model\AuthRuleModel;
use think\facade\Cache;

/**
 * idcsmart控制器基础类
 */
class AdminBaseController extends BaseController
{
	// 初始化
    protected function initialize()
    {
    	parent::initialize();

        if(!empty(configuration('clientarea_url'))){
            if(request()->domain()==configuration('clientarea_url')){
                $url = request()->domain();
                header("location:{$url}");die;
            }
        }

    	if(!$this->checkAccess()){
            $module     = app('http')->getName();
            $controller = $this->request->controller();
            $action     = $this->request->action();
            $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

            // 查找权限,未找到设置了则放行
            $AuthRuleModel = new AuthRuleModel();
    		$name = $AuthRuleModel->getAuthName($rule);
            if(!empty($name)){
                echo json_encode(['status'=>404, 'msg'=>lang('permission_denied')]);die;
            }
            
    	}

        if(empty(configuration('idcsmartauthinfo'))){
            Cache::set('get_idcsamrt_auth', 1, 3600*6);
            if(mt_rand(0, 100)%2==1){
                $this->get_idcsamrt_auth2();
            }else{
                get_idcsamrt_auth();
            }
        }
        if(!Cache::has('get_idcsamrt_auth')){
            Cache::set('get_idcsamrt_auth', 1, 3600*6);
            if(mt_rand(0, 100)%2==1){
                $this->get_idcsamrt_auth2();
            }else{
                get_idcsamrt_auth();
            }
            
        }
    	
    }

    // 获取管理员权限
    private function checkAccess()
    {
    	$adminId = get_admin_id();
        if($adminId==1 || empty($adminId)){
            return true;
        }
        $module     = app('http')->getName();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

        // 先获取缓存的权限
    	if(Cache::has('admin_auth_rule_'.$adminId)){
    		$auth = json_decode(Cache::get('admin_auth_rule_'.$adminId), true);
    		if(!in_array($rule, $auth)){
	    		return false;
	    	}else{
	    		return true;
	    	}
    	}

        // 获取数据库的权限
    	$AuthRuleModel = new AuthRuleModel();
    	$auth = $AuthRuleModel->getAdminAuthRule($adminId);
    	Cache::set('admin_auth_rule_'.$adminId, json_encode($auth),7200);
    	if(!in_array($rule, $auth)){
    		return false;
    	}
    	return true;
    }

    private function get_idcsamrt_auth2()
    {
        $host = 'license.soft13.idcsmart.com'; // HTTPS服务器地址  
        $port = 443; // HTTPS端口  
        $path = '/app/api/auth_rc'; // 请求路径

        $license = configuration('system_license');//系统授权码
        if(empty($license)){
            return false;
        }
        if(!empty($_SERVER) && isset($_SERVER['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])){
            
        }else{
            return false;
        }
        $ip = $_SERVER['SERVER_ADDR'];//服务器地址
        $arr = parse_url($_SERVER['HTTP_HOST']);
        $domain = isset($arr['host'])? ($arr['host'].(isset($arr['port']) ? (':'.$arr['port']) : '')) :$arr['path'];
        $type = 'finance';
        
        $version = configuration('system_version');//系统当前版本
        $data = [
            'ip' => $ip,
            'domain' => $domain,
            'type' => $type,
            'license' => $license,
            'install_version' => $version,
            'request_time' => time(),
        ];
        $post_data = http_build_query($data);

        $method = 'POST';  
        $headers = [  
            "Host: $host",  
            "Content-Type: application/x-www-form-urlencoded",  
            "Content-Length: " . strlen($post_data),  
            "Connection: close",  
        ];
          
        // 创建socket连接到HTTPS服务器  
        $socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, stream_context_create([  
            'ssl' => [  
                'verify_peer' => true,  
                'verify_peer_name' => true,  
                'allow_self_signed' => true,  
            ],  
        ]));  
          
        if (!$socket) {  
            return false; 
        } else {  
            // 发送HTTP请求  
            $request = "$method $path HTTP/1.1\r\n";  
            foreach ($headers as $header) {  
                $request .= "$header\r\n";  
            }  
            $request .= "\r\n$post_data";  
          
            fwrite($socket, $request);  
          
            // 读取响应  
            $response = '';  
            while (!feof($socket)) {  
                $response .= fgets($socket, 1024);  
            }  
          
            fclose($socket);  
          
            // 解析响应  
            list($headers, $body) = explode("\r\n\r\n", $response, 2);  
          
            $body = explode("\r\n", $body);
            foreach ($body as $key => $value) {
                if($key%2==0){
                    unset($body[$key]);
                }
            }
            $body = implode('', array_values($body));

            $result = json_decode($body, true);

            if(isset($result['status']) && $result['status']==200){
                $ConfigurationModel = new \app\common\model\ConfigurationModel();
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => $result['data']]);
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_service_due_time', 'value' => $result['due_time']]);
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_due_time', 'value' => $result['auth_due_time']]);
                return true;
            }else if(isset($result['status'])){
                
                $ConfigurationModel = new \app\common\model\ConfigurationModel();
                $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => '']);
                return false;
            }else{
                return false;
            } 
        }
    }
}
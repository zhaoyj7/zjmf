<?php

use app\common\model\ConfigurationModel;
use app\common\model\UpstreamOrderModel;
use think\facade\Cache;
use app\admin\model\PluginModel;
use app\common\model\SystemLogModel;
use app\common\model\OrderTmpModel;
use app\common\model\ClientCreditModel;
use app\common\model\ClientModel;
use think\facade\Event;
use app\common\model\TaskWaitModel;
use app\common\model\NoticeSettingModel;
use app\common\model\FileLogModel;
use app\admin\model\AdminModel;
use app\home\model\OauthModel;
use app\common\logic\LangLogic;
use app\common\model\ProductModel;
use app\common\model\SmsCodeLogModel;

/**
 * 时间 2024-02-07
 * @title 读取目录下所有文件(除.和..)并放入files数组
 * @desc  读取目录下所有文件(除.和..)并放入files数组
 * @author wyh
 * @version v1
 * @param string dir - 目录 require
 * @param array files - 文件数组
 * @return array files - 文件
 */
function read_dir($dir = '', $files = []){
    if(!is_dir($dir)){
        return $files;
    }
    $handle = opendir($dir);
    if ($handle) {//目录打开正常
        while(($file = readdir($handle)) !== false){
            if($file != "." && $file != ".."){
                if(!is_dir("$dir/$file")){
                    $files[]="$dir/$file";
                }else{
                    $files = read_dir("$dir/$file", $files);
                }
            }
        }
        closedir($handle);
    }
    return $files;
}

/**
 * 时间 2024-02-07
 * @title 格式化打印
 * @desc  格式化打印
 * @author wyh
 * @version v1
 * @param array input - 需要打印的字符串数组 require
 */
function format_print(...$input)
{
    echo "<pre>";
    var_dump($input);die;
}

/**
 * @title 后台获取当前登录管理员ID
 * @desc 后台获取当前登录管理员ID
 * @author wyh
 * @version v1
 * @return int
 */
function get_admin_id()
{
    return intval(request()->admin_id);
}

/**
 * @title 前台获取当前登录用户ID
 * @desc 前台获取当前登录用户ID
 * @author wyh
 * @version v1
 * @return int
 */
function get_client_id($origin = true)
{
    if($origin===true){
        // 减少重复查询
        if(!isset(request()->client_parent_id)){
            request()->client_parent_id = 0;

            $result = hook('get_client_parent_id',['client_id'=>request()->client_id]);

            foreach ($result as $value){
                if ($value){
                    request()->client_parent_id = (int)$value;
                    break;
                }
            }
        }
        return request()->client_parent_id ?: intval(request()->client_id);
    }else{
        return intval(request()->client_id);
    }
    
}

/**
 * @title 获取请求头的jwt
 * @desc 获取请求头的jwt
 * @author wyh
 * @version v1
 * @return string
 */
function get_header_jwt()
{
    $header = request()->header();
    $jwt = '';
    if(isset($header['authorization'])){
        $jwt = count(explode(' ',$header['authorization']))>1?explode(' ',$header['authorization'])[1]:'';
    }
    return $jwt;
}

/**
 * @title 生成jwt
 * @desc 生成jwt
 * @author wyh
 * @version v1
 * @param array info - 基础信息,如['id'=>1,'name'=>'wyh']
 * @param int expire - 过期时间,单位秒(s),默认7200s
 * @param bool is_admin - 是否后台创建
 * @return string
 */
function create_jwt($info, $expire = 7200, $is_admin=false)
{
    # jwt的签发密钥，验证token的时候需要用到,此密钥通用,未采用存数据库方式动态生成!有一定的非安全性
    if ($is_admin){
        $key = config('idcsmart.jwt_key_admin') . AUTHCODE;
    }else{
        $key = config('idcsmart.jwt_key_client') . AUTHCODE;
    }
    # jwt的签发密钥存数据库,因ip以及客户端问题,以及后台以用户登录产生问题,此方法搁置
    /*if ($is_admin){
        $AdminLoginModel = new AdminLoginModel();
        $key = $AdminLoginModel->getJwtKey($info['id']);
    }else{
        $ClientLoginModel = new ClientLoginModel();
        $key = $ClientLoginModel->getJwtKey($info['id']);
    }*/

    $time = time();

    $token = array(
        "info" => $info,
        "iss" => "www.idcsmart.com", # 签发组织
        "aud" => "www.idcsmart.com", # 接收该JWT的一方
        "ip" => get_client_ip(),
        "iat" => $time, # 签发时间
        "nbf" => $time, # not before，如果当前时间在nbf里的时间之前，则Token不被接受；一般都会留一些余地，比如几分钟。
        "exp" => $time + $expire, # expire 指定token的生命周期
    );

    $jwt = Firebase\JWT\JWT::encode($token, $key, 'HS256');

    $key = 'login_token_'.$jwt;
    Cache::set($key,$info['id'],$expire);

    return $jwt;
}

/**
 * @title 添加钩子
 * @desc 添加钩子
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  params - 传入参数
 * @return mixed
 */
function hook($hook,$params=null)
{
    return Event::trigger($hook ,$params);
}

/**
 * @title 添加钩子,只执行一个
 * @desc 添加钩子,只执行一个
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  params - 传入参数
 * @return mixed
 */
function hook_one($hook,$params=null)
{
    return Event::trigger($hook ,$params,true);
}

/**
 * @title 监听钩子
 * @desc 监听钩子
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  fun - 执行方法
 * @return mixed
 */
function add_hook($hook,$fun)
{
    return Event::listen($hook,$fun);
}

/**
* @title 内部调用API
* @desc 内部调用API
* @author xiong
* @version v1
* @param string $cmd - 调用API名称 require
* @param array $data - 传入的参数
* @return array
*/
function local_api($cmd,$data=[]){
	list($project,$module,$action) = explode("_",$cmd);
	$http_app = app('http')->getName();
	if($http_app==$project && strtolower(request()->controller())==strtolower($module) && strtolower(request()->action())==strtolower($action)){
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	request()->page = isset($data['page']) ? intval($data['page']):config('idcsmart.page');
    request()->limit = isset($data['limit']) ? intval($data['limit']):config('idcsmart.limit');
    request()->sort = isset($data['sort']) ? intval($data['sort']):config('idcsmart.sort');
    $class = "\app\\{$project}\\controller\\{$module}Controller";
	if (!class_exists($class)) {
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	request()->local_api_data = $data;
	$cls = new $class( app() );
	$cls_methods = get_class_methods($cls);
	if(!in_array($action,$cls_methods)){
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	$result = $cls->$action()->getData();
	return $result;
}

/**
 * @title 调用插件API
 * @desc 代码内部调用插件API
 * @author wyh
 * @version v1
 * @param string addon - 插件 require
 * @param string controller - 控制器前缀 require
 * @param string action - 方法 require
 * @param array param - 传入的参数
 * @param boolean admin - 是否后台
 * @return array
 */
function plugin_api($addon,$controller,$action,$param=[],$admin=false)
{
    $addon = parse_name($addon);

    $controller = ucwords($controller);
    if ($admin){
        $class = "addon\\{$addon}\\controller\\{$controller}Controller";
    }else{
        $class = "addon\\{$addon}\\controller\\clientarea\\{$controller}Controller";
    }

    if (!class_exists($class)){
        return [];
    }

    # 追加默认参数
    $request = request();
    $request->local_api_data = $param;

    $request->page = isset($param['page']) ? intval($param['page']):config('idcsmart.page');
    $request->limit = isset($param['limit']) ? intval($param['limit']):config('idcsmart.limit');
    $request->sort = isset($param['sort']) ? intval($param['sort']):config('idcsmart.sort');

    $result = app('app')->invoke([$class,$action],[$param]);

    return $result->getData();
}

/**
 * @title 调用代理模块API
 * @desc 代码内部调用代理模块API
 * @author theworld
 * @version v1
 * @param string reserver - 代理模块 require
 * @param string controller - 控制器前缀 require
 * @param string action - 方法 require
 * @param array param - 传入的参数
 * @param boolean admin - 是否后台
 * @return array
 */
function reserver_api($reserver,$controller,$action,$param=[],$admin=false)
{
    $reserver = parse_name($reserver);

    $controller = ucwords($controller);
    if ($admin){
        $class = "reserver\\{$reserver}\\controller\\admin\\{$controller}Controller";
    }else{
        $class = "reserver\\{$reserver}\\controller\\home\\{$controller}Controller";
    }

    if (!class_exists($class)){
        return [];
    }

    # 追加默认参数
    $request = request();
    $request->local_api_data = $param;

    $request->page = isset($param['page']) ? intval($param['page']):config('idcsmart.page');
    $request->limit = isset($param['limit']) ? intval($param['limit']):config('idcsmart.limit');
    $request->sort = isset($param['sort']) ? intval($param['sort']):config('idcsmart.sort');

    $result = app('app')->invoke([$class,$action],[$param]);

    return $result->getData();
}

/**
* @title 获取语言列表
* @desc 获取语言列表
* @author xiong
* @version v1
* @param string app admin 应用名称,只有admin和home这两个值
* @return array
* @return string [].display_name - 语言名称
* @return string [].display_flag - 国家代码
* @return string [].display_lang - 语言标识
*/
function lang_list($app = 'admin')
{
	if($app == 'admin') $app = DIR_ADMIN;
	if($app == 'home') $app = 'clientarea';
	$path= public_path() .'/'. $app .'/language';
	if(!file_exists($path))	return [];
	$handler = opendir($path);//当前目录中的文件夹下的文件夹
	$lang_data_now_all = [];
	while (($filename = readdir($handler)) !== false) {
	   if ($filename != "." && $filename != ".." ) {
			if(strpos($filename,".php")===false) continue;
			$_LANG=include $path."/".$filename;
			if(empty($_LANG['display_name'])) continue;
			$lang_data_now['display_name'] = $_LANG['display_name'];
			$lang_data_now['display_flag'] = $_LANG['display_flag'];
			$lang_data_now['display_img'] = '/upload/common/country/'.$_LANG['display_flag'].'.png';
			$lang_data_now['display_lang'] = str_replace(".php","",$filename);
			$lang_data_now_all[] = $lang_data_now;
			unset($_LANG);
		}
	}
	closedir($handler);
    return $lang_data_now_all;
}
/**
* @title 获取语言
* @desc 获取语言
* @author xiong
* @version v1
* @param string name - 名称
* @param array param - 要替换语言中的参数
* @return string
*/
function lang($name = '', $param = [])
{
    // 多语言优化
    return LangLogic::renderSystemLang($name, $param);
	// $defaultLang = config('lang.default_lang');
 //    if(!empty(get_client_id())){
 //        $defaultLang = get_client_lang();
 //    }else{
 //        $defaultLang = get_system_lang(true);
 //    }
    
	// $langAdmin = include WEB_ROOT.'/'.DIR_ADMIN.'/language/'. $defaultLang .'.php';
	// $langHome = include WEB_ROOT.'/clientarea/language/'. $defaultLang .'.php';
	// $lang = array_merge($langAdmin, $langHome);
	// if(empty($name)){
	// 	return $lang;
	// }else if(empty($lang[$name])){
	// 	return $name;
	// }else{
	// 	$language = $lang[$name];
	// 	foreach($param as $k => $v){
	// 		$language = str_replace($k, $v , $language);
	// 	}
	// 	return $language;
	// }
}

/**
 * @title 获取插件语言
 * @desc 获取插件语言
 * @author xiong
 * @version v1
 * @param string name - 名称
 * @param array param - 要替换语言中的参数
 * @return string
 */
function lang_plugins($name = '', $param = [], $reload = false)
{
    return LangLogic::renderPluginLang($name, $param, $reload);
    #$currentAddon = request()->param('_plugin')??'';
    #$name = $currentAddon?$currentAddon . '_' . $name:$name;
    // $defaultLang = config('lang.default_lang');
    // if(!empty(get_client_id())){
    //     $defaultLang = get_client_lang();
    // }else{
    //     $app = app('http')->getName();
    //     if ($app=='home'){
    //         $defaultLang = get_client_lang();
    //     }else{
    //         $defaultLang = get_system_lang(true);
    //     }
    // }
    // $cacheName = 'pluginLang_'.$defaultLang;
    // $lang = Cache::get($cacheName);
    // if(!empty($lang) && $reload===false){
    //     $lang = json_decode($lang, true);
    // }else{
    //     $lang = [];
    //     # 加载插件多语言(wyh 20220616 改:涉及到一个插件需要调另一个插件以及系统调插件钩子的情况,所以只有加载所有已安装使用插件的多语言)
    //     $addonDir = WEB_ROOT . 'plugins/addon/';
    //     $addons = array_map('basename', glob($addonDir . '*', GLOB_ONLYDIR));
    //     $PluginModel = new PluginModel();
    //     foreach ($addons as $addon){
    //         $parseName = parse_name($addon,1);
    //         # 说明:存在一定的安全性,判断是否安装且启用的插件
    //         $plugin = $PluginModel->where('name',$parseName)
    //             //->where('status',1)
    //             ->find();
    //         if (!empty($plugin) && is_file($addonDir . $addon . "/lang/{$defaultLang}.php")){
    //             $pluginLang = include $addonDir . $addon . "/lang/{$defaultLang}.php";
    //             $lang = array_merge($lang,$pluginLang);
    //         }
    //     }
    //     # 加载模块多语言
    //     $serverDir = WEB_ROOT . 'plugins/server/';
    //     $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
    //     foreach ($servers as $server){
    //         if (is_file($serverDir . $server . "/lang/{$defaultLang}.php")){
    //             $pluginLang = include $serverDir . $server . "/lang/{$defaultLang}.php";
    //             $lang = array_merge($lang,$pluginLang);
    //         }
    //     }

    //     # 加载模块多语言
    //     $reserverDir = WEB_ROOT . 'plugins/reserver/';
    //     $servers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
    //     foreach ($servers as $server){
    //         if (is_file($reserverDir . $server . "/lang/{$defaultLang}.php")){
    //             $pluginLang = include $reserverDir . $server . "/lang/{$defaultLang}.php";
    //             $lang = array_merge($lang,$pluginLang);
    //         }
    //     }

    //     # 加载模板控制器多语言
    //     $templateDir = WEB_ROOT . 'web/';
    //     $templates = array_map('basename', glob($templateDir . '*', GLOB_ONLYDIR));
    //     foreach ($templates as $template){
    //         if (is_file($templateDir . $template . "/controller/lang/{$defaultLang}.php")){
    //             $pluginLang = include $templateDir . $template . "/controller/lang/{$defaultLang}.php";
    //             $lang = array_merge($lang,$pluginLang);
    //         }
    //     }
    //     Cache::set($cacheName, json_encode($lang), 24*3600);
    // }

    // if(empty($name)){
    //     return $lang;
    // }else if(!isset($lang[$name])){
    //     return $name;
    // }else{
    //     $language = $lang[$name];
    //     foreach($param as $k => $v){
    //         $language = str_replace($k, $v , $language);
    //     }
    //     return $language;
    // }
}

/**
 * @title 获取系统使用语言
 * @desc 获取系统使用语言,分前后台
 * @author wyh
 * @version v1
 * @param string is_admin - 是否后台:true是
 * @return array
 */
function get_system_lang($is_admin=true)
{
    $header = request()->header();
    $langAdmin = lang_list('admin');
    $langHome = lang_list('home');
    $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
    if(isset($header['language']) && !empty($header['language']) && in_array($header['language'], $lang)){
        $lang = $header['language'];
    }else if ($is_admin){
        $lang = configuration('lang_admin');
    }else{
        $lang = configuration('lang_home');
    }
    return $lang;
}

/**
 * @title 获取客户使用语言
 * @desc 获取客户使用语言
 * @author wyh
 * @version v1
 * @return string
 */
function get_client_lang()
{
    $ClientModel = new ClientModel();
    $client_id = get_client_id();
    $client = $ClientModel->find($client_id);
    if(!empty($client)){
        $language = !empty($client['language']) ? $client['language'] : get_system_lang(false);
    }else{
        $language = cookie("web_language")??get_system_lang(false);
    }
    return $language;
}

/**
* @title CURL
* @desc 公共curl
* @author xiong
* @version v1
* @param string url - url地址 require
* @param array data [] 传递的参数
* @param string timeout 30 超时时间
* @param string request POST 请求类型
* @param array header [] 头部参数
* @param  bool curlFile false 是否curl上传文件
* @return int http_code - http状态码
* @return string error - 错误信息
* @return string content - 内容
*/
function curl($url, $data = [], $timeout = 30, $request = 'POST', $header = [], $curlFile = false)
{
    $curl = curl_init();
    $request = strtoupper($request);

    if($request == 'GET'){
        $s = '';
        if(!empty($data)){
            foreach($data as $k=>$v){
                if($v === ''){
                    $data[$k] = '';
                }
            }
            $s = http_build_query($data);
        }
        if(strpos($url, '?') !== false){
            if($s){
                $s = '&'.$s;
            }
        }else{
            if($s){
                $s = '?'.$s;
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url.$s);
    }else{
        curl_setopt($curl, CURLOPT_URL, $url);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_REFERER, request() ->host());
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if($request == 'GET'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
    }
    if($request == 'POST'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if(is_array($data) && !$curlFile){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if($request == 'PUT' || $request == 'DELETE'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if(!empty($header)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    $content = curl_exec($curl);
    $error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	return ['http_code'=>$http_code, 'error'=>$error , 'content' => $content];
}

/**
 * @title 密码加密
 * @desc 前后台登录密码加密方式
 * @author wyh
 * @version v1
 * @param string pw - 密码 require
 * @param string authCode - 系统唯一身份验证字符
 * @return string
 */
function idcsmart_password($pw, $authCode = '')
{
    error_reporting(0);
    if (defined('IS_ZKEYS') && IS_ZKEYS){ # 兼容zkeys迁移密码
        $result = md5($authCode . $pw);
        //$result = md5(htmlspecialchars($pw));
    }else{
        if (is_null($pw)){
            return '';
        }

        if (empty($authCode)) {
            $authCode = AUTHCODE;
        }

        $result = "###" . md5(md5($authCode . $pw));
    }

    return $result;
}

// 这个不开放出去
function idcsmart_password_zkeys($pw,$authCode="")
{
    $result = md5($authCode . $pw);
    return $result;
}

/**
 * @title 密码比较
 * @desc 密码比较,正确返回true
 * @author wyh
 * @version v1
 * @param string password - 密码 require
 * @param string passwordInDb - 密码 require
 * @return bool
 */
function idcsmart_password_compare($password, $passwordInDb)
{
    // zkeys加密方式更改，兼容zkeys最新的两种加密方式，以及V10加密 20231110
    if (defined('IS_ZKEYS') && IS_ZKEYS){
        return (idcsmart_password($password,"http://www.niaoyun.com/") == $passwordInDb) ||
            (idcsmart_password(htmlspecialchars($password)) == $passwordInDb) ||
            (idcsmart_password_zkeys($password,'http://www.niaoyun.com/') == $passwordInDb) ||
            (idcsmart_password_zkeys($password) == $passwordInDb);
    }

    return idcsmart_password($password) == $passwordInDb;
}


/**
 * @title 对称加密
 * @desc 对称加密
 * @author wyh
 * @version v1
 * @param string data - 加密数据 required
 * @return string
 */
function aes_password_encode($data){
    $key = md5('idcsmart');
    $v = substr($key,0,8);
    $result = openssl_encrypt($data, 'DES-CBC', $key, OPENSSL_RAW_DATA, $v);
    return base64_encode($result);
}

/**
 * @title 解密
 * @desc 解密:aes_password_encode方法解密
 * @author wyh
 * @version v1
 * @param string data - 加密数据 required
 * @return string
 */
function aes_password_decode($data){
    $data = base64_decode($data);
    $key = md5('idcsmart');
    $v = substr($key,0,8);
    $result = openssl_decrypt($data, 'DES-CBC', $key, OPENSSL_RAW_DATA, $v);
    return $result;
}

/**
 * @title 金额格式化
 * @desc 金额格式化,返回保留两位小数的金额
 * @author theworld
 * @version v1
 * @param float amount - 金额 require
 * @param int scale 2 保留小数位数
 * @return string
 */
function amount_format($amount, $scale = 2){
    $amount = round((float)$amount, $scale + 1);
    $amount = (float)bcdiv((string)$amount, 1, $scale);
    return number_format($amount, $scale, ".", "");
}

/**
 * @title 获取客户端IP地址
 * @desc 获取客户端IP地址
 * @author wyh
 * @version v1
 * @param int type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param bool adv  是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = true)
{
    static $ipWhiteList = null;
    // 获取 X-Forwarded-For 头部
    $ip = getenv('HTTP_X_FORWARDED_FOR');

    if ($ip) {
        if ($ipWhiteList === null) {
            $list = explode("\n", configuration('ip_white_list'));
            $ipWhiteList = array_filter(array_map('trim', $list));
        }
        // 将 X-Forwarded-For 中的 IP 地址分割成数组并去掉多余的空格
        $ipList = array_map('trim', explode(',', $ip));
        // 当只有客户端ip时
        if (count($ipList)==1){
            // 代理ip是否在白名单
            if (ip_in_whitelist(request()->ip($type, $adv), $ipWhiteList)){
                return $ipList[0]; // 返回真实ip
            }
        }else{
            // 遍历 X-Forwarded-For 中的所有 IP 地址（从客户端到代理）
            foreach ($ipList as $index => $proxyIp) {
                // 从第二个 IP 地址开始，检查代理服务器 IP 是否在白名单中
                if ($index > 0 && ip_in_whitelist($proxyIp, $ipWhiteList)) {
                    // 如果代理 IP 在白名单中，则返回第一个 IP（客户端的真实 IP）
                    return $ipList[0];  // 真实客户端 IP 通常是第一个
                }
            }
        }

    }

    // 如果没有找到信任的代理或没有 X-Forwarded-For 头部，则使用默认方法获取客户端 IP
    return request()->ip($type, $adv);
}

/**
 * @title 检查IP是否在白名单中
 * @desc 支持单个IP、CIDR网段、IP范围三种格式
 * @param string $ip 要检查的IP地址
 * @param array $whitelist 白名单数组
 * @return bool
 */
function ip_in_whitelist($ip, $whitelist)
{
    // 验证IP格式
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    
    $ipLong = ip2long($ip);
    if ($ipLong === false) {
        return false;
    }
    
    foreach ($whitelist as $item) {
        if (empty($item)) {
            continue;
        }
        
        // CIDR格式: 192.168.3.0/24
        if (strpos($item, '/') !== false) {
            if (ip_in_cidr($ip, $item)) {
                return true;
            }
        }
        // IP范围格式: 192.168.3.1-192.168.3.5
        elseif (strpos($item, '-') !== false) {
            if (ip_in_range($ip, $item)) {
                return true;
            }
        }
        // 单个IP
        else {
            if ($ip === $item) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * @title 检查IP是否在CIDR网段中
 * @param string $ip 要检查的IP地址
 * @param string $cidr CIDR格式的网段 如: 192.168.3.0/24
 * @return bool
 */
function ip_in_cidr($ip, $cidr)
{
    list($subnet, $mask) = explode('/', $cidr);
    
    $ipLong = ip2long($ip);
    $subnetLong = ip2long($subnet);
    $maskLong = -1 << (32 - (int)$mask);
    
    if ($ipLong === false || $subnetLong === false) {
        return false;
    }
    
    return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
}

/**
 * @title 检查IP是否在IP范围中
 * @param string $ip 要检查的IP地址
 * @param string $range IP范围 如: 192.168.3.1-192.168.3.5
 * @return bool
 */
function ip_in_range($ip, $range)
{
    list($startIp, $endIp) = array_map('trim', explode('-', $range));
    
    $ipLong = ip2long($ip);
    $startLong = ip2long($startIp);
    $endLong = ip2long($endIp);
    
    if ($ipLong === false || $startLong === false || $endLong === false) {
        return false;
    }
    
    return $ipLong >= $startLong && $ipLong <= $endLong;
}


/**
 * @title 获取插件类名
 * @desc 获取插件类名
 * @author wyh
 * @version v1
 * @param string name 插件名
 * @param string module 模块目录
 * @return string
 */
function get_plugin_class($name, $module)
{
    $name = ucwords($name);
    $pluginDir = parse_name($name);
    if($module=='template'){
        $class = "{$module}\\{$pluginDir}\\controller\\{$name}";
    }else{
        $class = "{$module}\\{$pluginDir}\\{$name}";
    }
    
    return $class;
}

/**
 * @title 编码图片base64格式
 * @desc 编码图片base64格式
 * @author wyh
 * @version v1
 * @param string image_file 图片地址
 * @return string
 */
function base64_encode_image($image_file)
{
    $base64_image = null;
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    if (!isset($image_data[0])){
        return '';
    }
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

/**
 * @title base64_decode_image($base64_image_content,$path):base64格式编码转换为图片并保存对应文件夹
 * @desc base64_decode_image($base64_image_content,$path):base64格式编码转换为图片并保存对应文件夹
 * @author wyh
 * @version v1
 * @param string base64_image_content base64
 * @param string path 保存路径
 * @return string
 */
function base64_decode_image($base64_image_content,$path)
{
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $new_file = $path;
        if(!file_exists($new_file)){
            mkdir($new_file, 0700);
        }
        $image = md5(uniqid()).time().".{$type}";
        $new_file = $new_file.$image;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $image;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * @title 支付接口
 * @desc 支付接口
 * @author wyh
 * @version v1
 * @return array list - 支付接口
 * @return int list[].id - ID
 * @return string list[].title - 名称
 * @return string list[].name - 标识
 * @return string list[].url - 图片:base64格式
 * @return int count - 总数
 */
function gateway_list()
{
    $PluginModel = new PluginModel();

    $gateways = $PluginModel->plugins('gateway');

    return $gateways;
}

/**
 * @title 验证支付接口
 * @desc 验证支付接口
 * @author wyh
 * @version v1
 * @param string WxPay 支付插件标识
 * @return bool
 */
function check_gateway($gateway)
{
    $PluginModel = new PluginModel();

    return $PluginModel->checkPlugin($gateway,'gateway');
}

/**
 * @title 获取系统配置
 * @desc 获取系统配置
 * @author wyh
 * @version v1
 * @param string|array setting 配置项键
 * @return mixed|array
 */
function configuration($setting)
{
    if (!is_array($setting)){
        $setting = [$setting];
    }

    $array = [];

    $ConfigurationModel = new ConfigurationModel();
    $configurations = $ConfigurationModel->index();
    foreach ($configurations as $configuration){
        foreach ($setting as $v){
            if ($v == $configuration['setting']){
                $array[$v] = $configuration['value'];
            }
            if (!isset($array[$v])){
                $array[$v] = '';
            }
        }
    }

    return count($setting)==1?$array[$setting[0]]:$array;
}

/**
 * @title 保存系统配置
 * @desc 保存系统配置
 * @author wyh
 * @version v1
 * @param string setting 配置项键
 * @param string value 值
 * @return boolean
 */
function updateConfiguration($setting,$value)
{
    $ConfigurationModel = new ConfigurationModel();
    $ConfigurationModel->saveConfiguration(['setting' => $setting, 'value' => $value]);
    return true;
}

/**
 * @title 检查手机格式
 * @desc 检查手机格式,中国手机不带国际电话区号,国际手机号格式为:国际电话区号-手机号
 * @author theworld
 * @version v1
 * @param string mobile 手机号
 * @return boolean
 */
function check_mobile($mobile)
{
    if (preg_match('/(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$/', $mobile)) {
        return true;
    } else {
        if (preg_match('/^\d{1,4}-\d{1,11}$/', $mobile)) {
            // 部分国家支持以0开头的手机号
//            if (preg_match('/^\d{1,4}-0+/', $mobile)) {
//                //不能以0开头
//                return false;
//            }

            return true;
        }

        return false;
    }
}

/**
 * @title 获取图形验证码
 * @desc 获取图形验证码
 * @author wyh
 * @version v1
 * @param boolean is_admin false 是否后台
 * @return string
 */
function get_captcha($is_admin=false)
{
    $captchaPlugin = configuration('captcha_plugin')??'TpCaptcha';

    $html = plugin_reflection($captchaPlugin,[],'captcha',$is_admin?'describe_admin':'describe');

    return $html;
}

/**
 * @title 验证图形验证码
 * @desc 验证图形验证码
 * @author wyh
 * @version v1
 * @param string captcha 12345 验证码
 * @param string token d7e57706218451cbb23c19cfce583fef 验证码唯一识别码
 * @return boolean
 */
function check_captcha($captcha,$token,$base=false)
{
    $data = [
        'captcha' => $captcha,
        'token' => $token,
        'base' => $base
    ];

    $captchaPlugin = configuration('captcha_plugin')??'TpCaptcha';

    $result = plugin_reflection($captchaPlugin,$data,'captcha','verify');

    if ($result['status']==200){
        return true;
    }else{
        return false;
    }
}

/**
 * @title 生成随机字符
 * @desc 生成随机字符
 * @author wyh
 * @version v1
 * @param int len 8 长度
 * @param string format ALL 格式,ALL大小写字母加数字,CHAR大小写字母,NUMBER数字
 * @return string
 */
function rand_str($len=8,$format='ALL'){
    $is_abc = $is_numer = 0;
    $password = $tmp ='';
    switch($format){
        case 'ALL':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
        case 'CHAR':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 'NUMBER':
            $chars='0123456789';
            break;
        default :
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
    }
    //mt_srand((double)microtime()*1000000*getmypid());
    while(strlen($password)<$len){
        $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
        if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
            $is_numer = 1;
        }
        if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
            $is_abc = 1;
        }
        $password.= $tmp;
    }
    if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
        $password = rand_str($len,$format);
    }

    return $password;
}

/**
 * @title 隐藏部分字符串
 * @desc 隐藏部分字符串
 * @author theworld
 * @version v1
 * @param string str - 需要隐藏的字符串
 * @param string replacement * 隐藏后显示的字符
 * @param int start 1 起始位置
 * @param int length 3 隐藏长度
 * @return string
 */
function hide_str($str, $replacement = '*', $start = 1, $length = 3)
{
    $len = mb_strlen($str,'utf-8');
    if ($len > intval($start+$length)) {
        $str1 = mb_substr($str, 0, $start, 'utf-8');
        $str2 = mb_substr($str, intval($start+$length), NULL, 'utf-8');
    } else {
        $str1 = mb_substr($str, 0, 1, 'utf-8');
        $str2 = mb_substr($str, $len-1, 1, 'utf-8');
        $length = $len - 2;
    }
    $newStr = $str1;
    for ($i = 0; $i < $length; $i++) {
        $newStr .= $replacement;
    }
    $newStr .= $str2;

    return $newStr;
}

/**
 * @title 临时订单ID生成
 * @desc 临时订单ID生成
 * @author wyh
 * @version v1
 * @param int rule 1 生成规则,1:毫秒时间戳+8位随机数,2:时间戳+8位随机数,3:10位随机数
 * @return int
 */
function idcsmart_tmp_order_id($rule=1)
{
    if ($rule == 1){
        $microtime = implode('',explode('.',microtime(true)));
        $tmp =  $microtime. rand_str(8,'NUMBER');
    }elseif ($rule == 2){
        $tmp = time() . rand_str(8,'NUMBER');
    }else{
        $tmp = rand_str(10,'NUMBER');
    }

    return $tmp;
}

/**
 * @title 添加系统日志
 * @desc 添加系统日志
 * @author theworld
 * @version v1
 * @param string description - 描述
 * @param string type - 关联类型
 * @param int relId - 关联ID
 * @param int relId - 关联用户ID
 * @return boolean
 */
function active_log($description, $type = '', $relId = 0, $clientId = 0)
{
    // 实例化模型类
    $SystemLogModel = new SystemLogModel();

    $description = htmlspecialchars($description);
    
    $param = [
        'description' => $description,
        'type' => $type,
        'rel_id' => $relId,
        'client_id' => $clientId,
    ];
    // 添加日志
    $result = $SystemLogModel->createSystemLog($param);

    return true;
}

/**
 * @title 添加短信验证码日志
 * @desc 添加短信验证码日志
 * @author theworld
 * @version v1
 * @param string type - 验证码类型
 * @param int phoneCode - 国际区号
 * @param string phone - 手机号
 * @param int abnormal - 异常期间0否1是
 * @return boolean
 */
function sms_code_log($type = '', $phoneCode = 0, $phone = '', $abnormal = 0)
{
    // 实例化模型类
    $SmsCodeLogModel = new SmsCodeLogModel();
    
    $param = [
        'type' => $type,
        'phone_code' => $phoneCode,
        'phone' => $phone,
        'abnormal' => $abnormal,
    ];
    // 添加日志
    $result = $SmsCodeLogModel->createSmsCodeLog($param);

    return true;
}

/**
 * @title 更新操作的日志描述记录
 * @desc 更新操作的日志描述记录
 * @author wyh
 * @version v1
 * @param array old - 旧数据
 * @param array new - 新数据
 * @param string type - 类型
 * @param boolean plugin - 是否插件
 * @return string
 */
function log_description($old=[],$new=[],$type='product',$plugin=false)
{
    $description = '';
    foreach ($old as $key=>$value){
        if (isset($new[$key]) && ($value != $new[$key])){
            if ($plugin){
                $description .= lang('log_admin_update_description',['{field}'=>lang_plugins('field_'.$type.'_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]) .',';
            }else{
                $description .= lang('log_admin_update_description',['{field}'=>lang('field_'.$type.'_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]) .',';
            }
        }
    }

    return rtrim($description,',');
}

/**
 * 时间 2022-05-24
 * @title 订单支付回调系统处理
 * @desc 订单支付回调系统处理
 * @author wyh
 * @version v1
 * @param string param.tmp_order_id 1653364762428172693291 临时订单ID required
 * @param float param.amount 1.00 金额 required
 * @param string param.trans_id qwery134151786 交易流水ID required
 * @param string param.currency CNY 货币 required
 * @param string param.paid_time 2022-05-24 时间 required
 * @param string param.gateway AliPay 支付方式 required
 * @return bool
 */
function order_pay_handle($param)
{
    $OrderTmpModel = new OrderTmpModel();

    return $OrderTmpModel->orderPayHandle($param);
}

/**
 * @title 修改用户余额
 * @desc 修改用户余额
 * @author theworld
 * @version v1
 * @param string param.type - 类型:人工Artificial 充值Recharge 应用至订单Applied 超付Overpayment 少付Underpayment 退款Refund
 * @param float param.amount - 金额 required
 * @param string param.notes - 备注 required
 * @param int param.client_id - 用户ID required
 * @param int param.order_id - 订单ID
 * @param int param.host_id - 产品ID
 * @return boolean
 */
function update_credit($param){
    // 实例化模型类
    $ClientCreditModel = new ClientCreditModel();

    $param = [
        'type' => $param['type'] ?? '',
        'amount' => $param['amount'] ?? 0,
        'notes' => $param['notes'] ?? '',
        'id' => $param['client_id'] ?? 0,
        'order_id' => $param['order_id'] ?? 0,
        'host_id' => $param['host_id'] ?? 0,
    ];
    // 修改用户余额
    $result = $ClientCreditModel->updateClientCredit($param);
    return $result['status']==200 ?? false;
}

/**
 * @title 生成产品标识
 * @desc 生成产品标识
 * @author theworld
 * @version v1
 * @return string
 */
function generate_host_name($product_id = 0)
{
    $ProductModel = new ProductModel();
    $product = $ProductModel->find($product_id);
    if(!empty($product) && $product['custom_host_name']==1){
        $prefix = $product['custom_host_name_prefix'];
        
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = strtolower($upper);
        $num = '0123456789';
        $randstr = $str = '';

        $allow = array_filter(explode(',', $product['custom_host_name_string_allow']));
        if(in_array('number', $allow)){
            $str .= $num;
        }
        if(in_array('upper', $allow)){
            $str .= $upper;
        }
        if(in_array('lower', $allow)){
            $str .= $lower;
        }

        $len = strlen($str)-1;
        $length = $product['custom_host_name_string_length'];

        if ($len<$length){
            $n = ceil($length/$len);
            for ($j=0; $j<$n; $j++){
                $str .= $str;
            }
            $len = strlen($str) - 1;
        }
        for($i=0; $i<$len; $i++){
            $num = mt_rand(0, $len);
            $randstr .= $str[$num];
        }
        return $prefix . substr($randstr, 0, $length);
    }else{
        $prefix = 'ser';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = strtolower($upper);
        $num = '0123456789';

        $randstr = $str = '';
        $str .= $num;

        $len = strlen($str)-1;
        $length = 12;

        if ($len<$length){
            $n = ceil($length/$len);
            for ($j=0; $j<$n; $j++){
                $str .= $str;
            }
            $len = strlen($str) - 1;
        }
        for($i=0; $i<$len; $i++){
            $num = mt_rand(0, $len);
            $randstr .= $str[$num];
        }
        return $prefix . substr($randstr, 0, $length);
    }
    
}

/**
 * @title 获取系统钩子
 * @desc 获取系统钩子
 * @author wyh
 * @version v1
 * @return array
 */
function get_system_hooks()
{
    $class = new \ReflectionClass('app\\home\\controller\\HooksController');
    $methods = $class->getMethods();
    $methodsFilter = [];
    foreach ($methods as $method){
        $methodsFilter[] = $method->name;
    }

    $methodsFilter = array_merge($methodsFilter,config('idcsmart.template_hooks'));

    return $methodsFilter;
}

/**
 * @title 映射插件方法
 * @desc 映射插件方法
 * @author wyh
 * @version v1
 * @param string plugin - 插件标识 required
 * @param string param  - 参数 required
 * @param string module - 模块
 * @param string action - 方法
 * @return mixed
 */
function plugin_reflection($plugin,$param,$module='gateway',$action='handle')
{
    $class = get_plugin_class($plugin,$module);

    if (!class_exists($class)){
        return '';
    }

    # 实现默认方法:插件标识+Handle
    $action = parse_name(parse_name($plugin) . '_' . $action,1);

    $methods = get_class_methods($class);

    if (!in_array($action,$methods)){
        return '';
    }

    return app('app')->invoke([$class,$action],[$param]);
}

/**
 * @title 验证插件方法是否存在
 * @desc 验证插件方法是否存在
 * @author wyh
 * @version v1
 * @param string plugin - 插件标识 required
 * @param string module - 模块
 * @param string action - 方法
 * @return boolean
 */
function plugin_method_exist($plugin,$module='gateway',$action='handle')
{
    $class = get_plugin_class($plugin,$module);

    if (!class_exists($class)){
        return false;
    }

    # 实现默认方法:插件标识+Handle
    $action = parse_name(parse_name($plugin) . '_' . $action,1);

    $methods = get_class_methods($class);

    if (!in_array($action,$methods)){
        return false;
    }

    return true;
}

/**
 * @title 生成访问插件addon的url
 * @desc 生成访问插件addon的url
 * @author wyh
 * @version v1
 * @param string url - url格式：插件名://控制器名/方法 required
 * @param array vars  - 参数
 * @param bool is_admin - 是否后台
 * @return string
 */
function idcsmart_addon_url($url, $vars = [], $is_admin = false)
{
    $url              = parse_url($url);
    $caseInsensitive = true;
    $plugin           = $caseInsensitive ? parse_name($url['scheme']) : $url['scheme'];
    $controller       = $caseInsensitive ? parse_name($url['host']) : $url['host'];
    $action           = trim($caseInsensitive ? strtolower($url['path']) : $url['path'], '/');
    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $vars = array_merge($query, $vars);
    }
    /* 基础参数 */
    $params = [
        '_plugin'     => $plugin,
        '_controller' => $controller,
        '_action'     => $action,
    ];
    $params = array_merge($params,$vars);

    if ($is_admin){
        $new = '/'. DIR_ADMIN . '/addon?' . http_build_query($params);
    }else{
        $plugin = parse_name($plugin,1);
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name',$plugin)->find();
        $params['_plugin'] = $plugin->id;
        $new = 'console/addon?' . http_build_query($params);
    }

    return $new;
}

/**
 * @title 是否新客户
 * @desc 是否新客户,新客户判断标准:无产品购买记录或历史已支付订单金额为0(标记支付金额为0，用户第三方支付为0，余额支付为0，同时满足这三个条件就算新用户)
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return bool
 */
function new_client($client_id)
{
    $ClientModel = new ClientModel();

    return $ClientModel->newClient($client_id);
}

/**
 * @title 是否旧客户
 * @desc 是否旧客户,新客户判断标准:无产品购买记录或历史已支付订单金额为0
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return bool
 */
function old_client($client_id)
{
    if (empty($client_id)){
        return false;
    }

    $ClientModel = new ClientModel();
    $client = $ClientModel->find($client_id);
    if (empty($client)){
        return false;
    }

    return !new_client($client_id);
}

/**
 * @title 判断文件是否是图片
 * @desc 判断文件是否是图片
 * @author wyh
 * @version v1
 * @param string filename - 文件名 required
 * @return bool
 */
function is_image($filename)
{
    if(file_exists($filename)) {
        if (!($info = @getimagesize($filename))){
            return false;
        }
        $ext = image_type_to_extension($info['2']);
        $types = '.gif|.jpeg|.png|.bmp|.ico|.svg'; # 定义检查的图片类型
        return stripos($types,$ext);
    } elseif (filter_var($filename,FILTER_VALIDATE_URL)){
        // 简单方式判断，url必须是授信的
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    } else {
        return false;
    }
}

/**
 * @title 判断文件是否是PDF文件
 * @desc 判断文件是否是PDF文件
 * @author wyh
 * @version v1
 * @param string filename - 文件名 required
 * @return bool
 */
function is_pdf($filename)
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filename);
    finfo_close($finfo);
    return $mimeType === 'application/pdf';
}

/**
 * 时间 2022-05-19
 * @title 添加到任务队列
 * @desc 添加到任务队列
 * @author xiong
 * @version v1
 * @param string param.type - 名称,sms短信发送,email邮件发送,host_create开通主机,host_suspend暂停主机,host_unsuspend解除暂停主机,host_terminate删除主机,执行在插件中的任务 required
 * @param int param.rel_id - 相关id
 * @param string param.description - 描述 required
 * @param array param.task_data - 任务要执行的数据 required
 * @param bool param.notice - 是否通知任务，通知任务独立处理
 */
function add_task($param)
{
	return (new TaskWaitModel())->createTaskWait($param);

}
/**
 * @title 创建动作
 * @desc 创建动作
 * @author xiong
 * @version v1
 * @param string param.name - 动作英文标识 required
 * @param string param.name_lang  - 动作名称（在页面显示的名称） required
 * @param string param.type other 通知分类(配置文件idcsmart.notice_setting_type中支持的,默认不传其他)
 * @param string param.sms_name  - 短信接口标识名（可以为空，默认智简魔方短信接口）
 * @param string param.sms_template[].title  - 短信模板标题 required
 * @param string param.sms_template[].content  - 短信模板内容 required
 * @param string param.sms_global_name  - 国际短信接口标识名（可以为空，默认智简魔方短信接口）
 * @param string param.sms_global_template[].title  - 国际短信模板标题 required
 * @param string param.sms_global_template[].content  - 国际短信模板内容 required
 * @param string param.email_name  - 邮件接口名称（可以为空，默认SMTP接口）
 * @param string param.email_template[].name  - 邮件模板名称 required
 * @param string param.email_template[].title  - 邮件模板标题 required
 * @param string param.email_template[].content  - 邮件模板内容 required
 * @return mixed
 */
function notice_action_create($param)
{
	return (new NoticeSettingModel())->noticeActionCreate($param);
}

/**
 * @title 删除动作
 * @desc 删除动作,短信邮件模板
 * @author xiong
 * @version v1
 * @param string name - 动作英文标识 required
 */
function notice_action_delete($name)
{
	return (new NoticeSettingModel())->noticeActionDelete($name);
}

/**
 * @title 加密
 * @desc 加密
 * @author wyh
 * @version v1
 * @param string password - 密码 required
 * @return string
 */
function password_encrypt($password)
{
    $key = config('idcsmart.aes.key');

    $iv = config('idcsmart.aes.iv');

    $data = openssl_encrypt($password, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    $data = base64_encode($data);

    return $data;
}

/**
 * @title 密码解密
 * @desc 前端CryptoJs加密,php解密
 * @author wyh
 * @version v1
 * @param string password - 加密密码 required
 * @return string
 */
function password_decrypt($password)
{
    $key = config('idcsmart.aes.key');

    $iv = config('idcsmart.aes.iv');

    $encrypted = base64_decode($password);

    $plainText = openssl_decrypt($encrypted,'AES-128-CBC',$key,OPENSSL_RAW_DATA,$iv);

    return $plainText;
}

/**
 * @title 获取目录下文件夹
 * @desc 获取目录下文件夹
 * @author theworld
 * @version v1
 * @param string path - 目录路径 required
 * @return array
 */
function get_files($path)
{
    if(!is_dir($path)){
        return [];
    }
    $arr = [];//存放文件名
    $handler = opendir($path);//当前目录中的文件夹下的文件夹
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != ".." &&  strpos($filename,'.') ===false) {
            //$arr[]=$filename;
            array_push($arr, $filename);
        }
    }
    closedir($handler);
    return $arr;
}

/**
 * @title 实名认证接口
 * @desc 实名认证接口
 * @author wyh
 * @version v1
 * @return array list - 支付接口
 * @return int list[].id - ID
 * @return string list[].title - 名称
 * @return string list[].name - 标识
 * @return string list[].url - 图片:base64格式
 * @return int count - 总数
 */
function certification_list()
{
    $PluginModel = new PluginModel();

    $certification = $PluginModel->plugins('certification');

    return $certification;
}

/**
 * @title 检查客户是否实名认证
 * @desc 检查客户是否实名认证
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return boolean
 */
function check_certification($client_id)
{
    $result = hook('check_certification',['client_id'=>$client_id]);

    foreach ($result as $value){
        if ($value){
            return true;
        }
    }

    return false;
}

/**
 * @title 是否开启未认证无法充值功能
 * @desc 是否开启未认证无法充值功能
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return boolean
 */
function check_certification_recharge()
{
    $result = hook('check_certification_recharge');

    foreach ($result as $value){
        if ($value){
            return true;
        }
    }

    return false;
}

/**
 * @title 导出EXCEL
 * @desc 导出EXCEL
 * @author theworld
 * @version v1
 * @param string filename - 文件名称
 * @param array field - 导出字段,参数名对应显示名称
 * @param array data - 导出数据,二维数组
 */
function export_excel(string $filename = '', array $field = [], array $data = [])
{
    require(IDCSMART_ROOT . 'vendor/excel/vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
    $enToCn = $field;
    $cnToEn = array_flip($enToCn);
    $intToCn = array_keys($cnToEn);
    $intToEn = array_keys($enToCn);

    $name = $filename;
    $excel = new \PHPExcel();
    iconv('UTF-8', 'gb2312', $name); //针对中文名转码
    $excel->setActiveSheetIndex(0);
    $sheel = $excel->getActiveSheet();
    $sheel->setTitle($name); //设置表名
    $sheel->getDefaultRowDimension()->setRowHeight(14.25);//设置默认行高
    $sheel->getDefaultColumnDimension()->setWidth(18);//设置默认列宽
    $letterArr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK'];
    foreach ($intToEn as $k => $v) {
        $sheel->setCellValueExplicit($letterArr[$k] . 1, $enToCn[$v]);
    }
    $nn = count($intToEn);
    // 写入内容
    for($i=0; $i<count($data); $i++){
        $j = $i+2;
        foreach ($intToEn as $k => $v) {
            $sheel->setCellValueExplicit($letterArr[$k] . $j, $data[$i][$v]."\t");
        }
    }
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$name.'.xlsx');
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

    $objWriter->save('php://output');
    exit;
}

/**
 * @title 生成CSV
 * @desc 生成CSV
 * @author theworld
 * @version v1
 * @param string filename - 文件名称,含路径
 * @param array data - 导出数据,二维数组
 */
function writeCsv($filename, $data) {  
    $file = fopen($filename, "w");  
    foreach ($data as $row) {  
        fputcsv($file, $row);  
    }  
    fclose($file);  
} 

/**
 * @title 获取授权信息
 * @desc 获取授权信息
 * @author theworld
 * @version v1
 * @return boolean
 */
function get_idcsamrt_auth()
{
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
    
    $url = "https://license.soft13.idcsmart.com/app/api/auth_rc";
    $res = curl($url,$data,20,'POST');
    if($res['http_code'] == 200){
        $result = json_decode($res['content'], true);
    }else{
        return false;
    }
    if(isset($result['status']) && $result['status']==200){
        $ConfigurationModel = new ConfigurationModel();
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => $result['data']]);
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_service_due_time', 'value' => $result['due_time']]);
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_due_time', 'value' => $result['auth_due_time']]);
        return true;
    }else if(isset($result['status'])){
        $ConfigurationModel = new ConfigurationModel();
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => '']);
        return false;
    }else{
        return false;
    }
}

/**
 * @title 魔方缓存
 * @desc 魔方缓存
 * @author wyh
 * @version v1
 * @param string key - 键
 * @param string value - 值:为null表示删除，’‘表示获取，其他设置
 * @param int timeout - 过期时间
 * @return mixed
 */
function idcsmart_cache($key,$value='',$timeout=null)
{
    return \app\common\lib\IdcsmartCache::cache($key,$value,$timeout);
}

/**
 * @title API鉴权登录
 * @desc API鉴权登录
 * @author wyh
 * @version v1
 * @param int api_id - 供应商ID
 * @param boolean force - 是否强制登录
 * @return array
 */
function idcsmart_api_login($api_id,$force=false)
{
    $SupplierModel = new \app\common\model\SupplierModel();

    return $SupplierModel->apiAuth($api_id,$force);
}

/**
 * @title 代理商请求供应商接口通用方法
 * @desc  代理商请求供应商接口通用方法
 * @author wyh
 * @version v1
 * @param   int    api_id  财务APIid
 * @param   string path    接口路径
 * @param   array  data    请求数据
 * @param   int    timeout 超时时间
 * @param   string request 请求方式(GET,POST,PUT,DELETE)
 * @param   string response_type 响应类型：json，html
 */
function idcsmart_api_curl($api_id,$path,$data=[],$timeout=30,$request='POST',$response_type='json',$curlFile = false)
{
    //idcsmart_cache('api_auth_login_' . AUTHCODE . '_' . $api_id,null);
    $login = idcsmart_api_login($api_id);
    if ($login['status']!=200){
        return $login;
    }
    if($login['data']['supplier']['type']=='whmcs'){
        $header = [
            'Email: '.$login['data']['supplier']['username'],
            'Password: '.$login['data']['supplier']['token'],
        ];

        $apiUrl = $login['data']['url'] . '/modules/addons/idcsmart_reseller/logic/index.php?action='. $path;

        $result = curl($apiUrl,$data,$timeout,$request,$header);
        if($result['http_code'] != 200){
            return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }
        $result = json_decode($result['content'], true);
        if(isset($result['status'])){
            if($result['status']=='success' || $result['status']==200){
                $result['status'] = 200;
            }else{
                $result['status'] = 400;
            }
        }else{
            $result['status'] = 400;
        }
    }else{
        $header = [
            'Authorization: Bearer '.$login['data']['jwt']
        ];

        $apiUrl = $login['data']['url'] . '/' .$path;

        $result = curl($apiUrl,$data,$timeout,$request,$header,$curlFile);
        if($result['http_code'] != 200){
            return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }
        // 直接返回html
        if ($response_type=='html'){
            return $result['content'];
        }
        $result = json_decode($result['content'], true);
        if(empty($result)){
            $result = ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>'' ];
        }
        if ($result['status']==401 || $result['status']==405){
            $login = idcsmart_api_login($api_id, true);

            if ($login['status']!=200){
                return $login;
            }

            $header = [
                'Authorization: Bearer '.$login['data']['jwt']
            ];
            $result = curl($apiUrl,$data,$timeout,$request,$header,$curlFile);
            
            if($result['http_code'] != 200){
                return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
            }
            $result = json_decode($result['content'], true);
            if ($result['status']==401){
                $result['status']=400;
                $result['msg'] = lang('api_account_or_password_error');
            }
        } 
    }

    

    return $result;
}

/**
 * @title 魔方生成RSA公私钥
 * @desc 魔方生成RSA公私钥
 * @author theworld
 * @version v1
 * @return string public_key - 公钥
 * @return string private_key - 私钥
 */
function idcsmart_openssl_rsa_key_create()
{
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    $res = openssl_pkey_new($config);

    openssl_pkey_export($res, $privateKey);

    $publicKey = openssl_pkey_get_details($res);
    $publicKey = $publicKey["key"];

    return ['public_key' => $publicKey, 'private_key' => $privateKey];
}

/**
 * @title 上游同步产品信息到下游
 * @desc  上游同步产品信息到下游
 * @author theworld
 * @version v1
 * @param   int    host_id 财务产品ID
 * @param   string action  动作module_create模块开通module_suspend模块暂停module_unsuspend模块解除暂停module_terminate模块删除update_host修改产品delete_host删除产品host_renew产品续费
 */
function upstream_sync_host($host_id, $action = '', $type = '', $renewPrice = 0)
{
    $HostModel = new \app\common\model\HostModel();

    return $HostModel->upstreamSyncHost($host_id, $action, $type, $renewPrice);
}

/**
 * @title 更新上游订单利润
 * @desc  更新上游订单利润
 * @author theworld
 * @version v1
 * @param   int    order_id 财务订单ID
 */
function update_upstream_order_profit($order_id)
{
    $OrderModel = new \app\common\model\OrderModel();

    return $OrderModel->updateUpstreamOrderProfit($order_id);
}

/**
 * @title debug加密
 * @desc debug加密
 * @author wyh
 * @version v1
 * @param string originalData - 源数据
 * @param string private_key - 私钥
 * @return string
 */
function zjmf_private_encrypt($originalData,$private_key){
    $crypted = '';
    foreach (str_split($originalData, 117) as $chunk) {
        openssl_private_encrypt($chunk, $encryptData, $private_key);
        $crypted .= $encryptData;
    }
    return base64_encode($crypted);
}

/**
 * @title 生成签名
 * @desc 生成签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @return array
 */
function create_sign($params, $token){
    $rand_str = rand_str(6);
    $params['token'] = $token;
    $params['rand_str'] = $rand_str;
    ksort($params, SORT_STRING);
    $str = json_encode($params);
    $sign = md5($str);
    $sign = strtoupper($sign);
    $res['signature'] = $sign;
    $res['rand_str'] = $rand_str;
    return $res;
}

/**
 * @title 是否使用手机端
 * @desc 是否使用手机端
 * @author wyh
 * @version v1
 * @return boolean
 */
function use_mobile()
{
    // 优化执行循序，当使用手机端时，才执行模板检查
    return request()->isMobile() && configuration("clientarea_theme_mobile_switch") && checkModuleMobile();
}

/**
 * @title 检查模块是否有手机端模版
 * @desc 检查模块是否有手机端模版，没有就返回false，系统模板使用pc模板
 * @author wyh
 * @version v1
 * @return boolean
 */
function checkModuleMobile()
{
    return \app\common\logic\CommonLogic::checkModuleMobile();
}

/**
 * @title 文件资源生成签名
 * @desc 文件资源生成签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @param string rand_str - 随机字符串
 * @return array
 */
function generate_signature($params,$token,$rand_str=""){
    $rand_str = $rand_str?:rand_str(6);
    unset($params['sign']);
    $params['token'] = $token;
    $params['rand_str'] = $rand_str;
    ksort($params, SORT_STRING);
    $str = json_encode($params);
    $sign = md5($str);
    $sign = strtoupper($sign);
    $res['signature'] = $sign;
    $res['rand_str'] = $rand_str;
    return $res;
}

/**
 * @title 文件资源验证签名
 * @desc 文件资源验证签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @param string rand_str - 随机字符串
 * @return boolean
 */
function validate_signature($params,$token,$rand_str=""){
    return $params['sign']==generate_signature($params,$token,$rand_str)['signature'];
}

/**
 * @title 获取同步回调地址
 * @desc 获取同步回调地址
 * @author wyh
 * @version v1
 * @param string tmp_order_id - 订单ID
 * @return string
 */
function get_gateway_return_url($tmp_order_id){
    $OrderTmpModel = new OrderTmpModel();
    return $OrderTmpModel->getGatewayReturnUrl($tmp_order_id);
}

/**
 * @title 上传文件到对象存储
 * @desc 上传文件到对象存储
 * @author theworld
 * @version v1
 * @param string file_path - 文件路径
 * @param string file_name - 文件名
 * @param string type - 文件类型：defautl系统默认、ticket工单、app应用等
 */
function idcsmart_oss_upload($file_path, $file_name, $type = 'default')
{
    $ossMethod = configuration("oss_method");
    $result = plugin_reflection($ossMethod,[
        'file_path' => $file_path,
        'file_name' => $file_name
    ],'oss','upload');
    if (empty($result)){
        return ['status'=>400,'msg'=>lang("non_existent_storage_method")];
    }
    
    if (isset($result['status']) && $result['status']==200){
        unlink(rtrim($file_path,'/') . "/" . $file_name);
        // 保存数据至数据库
        $FileLogModel = new FileLogModel();
        $uuid = explode("^",$file_name)[0]??explode(".",$file_name)[0];
        $file = $FileLogModel->where('uuid', $uuid)->find();
        if(!empty($file)){
            $FileLogModel->update([
                'save_name' => $file_name,
                'name' => explode("^",$file_name)[1]??"",
                'type' => $type,
                'oss_method' => $ossMethod,
                'create_time' => time(),
                'client_id' => get_client_id(),
                'admin_id' => get_admin_id(),
                'source' => get_client_id()>0?"client":"admin",
                'url' => $result['data']['url'] ?? ''
            ], ['uuid' => $uuid]);
        }else{
            $FileLogModel->insert([
                'uuid' => $uuid,
                'save_name' => $file_name,
                'name' => explode("^",$file_name)[1]??"",
                'type' => $type,
                'oss_method' => $ossMethod,
                'create_time' => time(),
                'client_id' => get_client_id(),
                'admin_id' => get_admin_id(),
                'source' => get_client_id()>0?"client":"admin",
                'url' => $result['data']['url'] ?? ''
            ]);
        }
        return ['status'=>200,'msg'=>lang('success_message'),'data' => ['url' => $result['data']['url'] ?? '']];
    }else{
        return ['status'=>400,'msg'=>$result['msg']??lang('move_fail')];
    }
}

/**
 * 模板数据标签替换
 * @param $content 模板内容
 * @param $tag 替换标签
 * @param $templateName $tag是debug时的debug所在标签模板名称
 * @return string 返回替换后的字符串
 */
function view_tpl_replace($content,$data,$templateName="")
{   
    $debug = '{debug}';
    if(stripos($content, $debug)!==false){
        //debug替换输出
        $debugData = '';
        foreach($data as $dataKey => $dataVal){
            $td = '<td>$' . $dataKey . '</td>';
            if(is_array($dataVal)){
                $td .= '<td><b>Value</b><br>';
                $td .= view_tpl_array_out($dataVal, false);
                $td .= '</td>';
            }else{
                $td .= '<td><b>Value</b><br>"'.$dataVal.'"</td>';
            }
            $debugData .= '<tr>'.$td.'</tr>';
        }

        $hn = '<h1>当前调试模板debug标签所在文件地址:"'.$templateName.'" </h1>';
        $debugHtml = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>当前模板输出的数据</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">           
            body, h1, h2, h3, td, th, p {
                font-family: sans-serif;
                font-weight: normal;
                font-size: 0.9em;
                margin: 1px;
                padding: 0;
            }
            h1 {
                margin: 0;
                text-align: left;
                padding: 2px;
                background-color: green;
                color: #fff;
                font-weight: bold;
                font-size: 1.2em;
            }
            h2 {
                background-color: #333;
                color: white;
                text-align: left;
                font-weight: bold;
                padding: 2px;
                border-top: 1px solid black;
            }
            table {
                width: 100%;
            }
            tr, td {
                font-family: monospace;
                vertical-align: top;
                text-align: left;
            }
            td {
                color: green;           
                padding:15px 10px;
            }
            tr:nth-of-type(odd) {
                background-color: #eeeeee;
            }
            tr:nth-of-type(even) {
                background-color: #fafafa;
            }            
        </style>
</head>
<body>
'.$hn.'
<h2>当前模板输出的数据:<br> </h2>
<table>
<tbody>
'.$debugData.'
</tbody>
</table>
</body>
</html>
';

        $debugHtml = ltrim(rtrim(preg_replace(array("/> *([^ ]*) *</","//","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'),$debugHtml)));
                
        //echo $debugHtml;exit;
        $streplace='
        <script type="text/javascript">
        idcsmart_debug_console = window.open("", "'.md5(time()).'", "width=1024,height=600,left=50,top=50,resizable,scrollbars=yes");
        idcsmart_debug_console.document.write("'.addslashes($debugHtml).'");
        </script>
        ';
                        
        $strreplace = "_smarty_console.document.close();";
        $content1 = substr($content,0,stripos($content,$debug)+strlen($debug));                   
        $content1 = str_replace($debug,$streplace,$content1);                
        $content = $content1.substr($content,stripos($content,$debug));
    }            
    
    // 多余的debug 和tagdata标签替换成空
    $content =str_replace($debug,"",$content);

    return $content;
}

function view_tpl_array_out($dataVal, $nbsp = ""){
    if($nbsp!==false) $nbsp.="&nbsp;&nbsp;";
    $nbsp_br = '';
    $td = '';
    foreach($dataVal as $key=>$val){
        if(is_array($val)){
            if($nbsp && $nbsp!="") $nbsp_br = $nbsp;
            if(!$nbsp)$nbsp="";
            $td.=''.$nbsp_br.$key.'=><br>'.view_tpl_array_out($val,$nbsp);
        }else{
            $td.=$nbsp.$key.'=>"'.$val.'"<br>';
        }
    }
    return $td;
}

/**
 * 获取币种汇率
 * @return object 返回USD(美元)对不同货币的汇率,{"USD": 1,"AED": 3.67,...}
 */
function getRate()
{
    $res = curl(config('app.getRateUrl'), [], 15, 'GET');
    if($res['http_code'] == 200){
        $result = json_decode($res['content'], true);
        $exchangeRates = $result['rates'] ?? [];
    }else{
        $exchangeRates = [];
    }
    return $exchangeRates;
}

/**
 * @title 获取对象存储文件访问地址和原文件名
 * @desc 获取对象存储文件访问地址和原文件名
 * @author wyh
 * @version v1
 * @param string param.file_path - 文件保存路径
 * @param string param.file_name - 文件名
 * @param int param.timeout - 超时时间，时间戳，单位秒
 * @return array
 * @return string url - 访问地址
 * @return string name - 原文件名
 * @return string save_name - 附件名
 */
function getOssUrl($param)
{
    // 需要兼容老数据，没有文件日志记录的情况
    /*$FileLogModel = new FileLogModel();
    $fileLog = $FileLogModel->where('save_name',$param['file_name'])->find();
    if (!empty($fileLog)){
        $name = $fileLog['name']??"";
    }else{
        $name = explode('^',$param['file_name'])[1]??$param['file_name'];
    }*/

    $name = explode('^',$param['file_name'])[1]??$param['file_name'];

    if ($ossMethod = \cache('oss_method')){

    }else{
        $ossMethod = configuration("oss_method");
        \cache('oss_method',$ossMethod);
    }
    $result = plugin_reflection($ossMethod,$param,'oss','download');
    $url = $result['data']['url']??"";
    return ['url'=>$url,'name'=>$name,'save_name'=>$param['file_name']];
}

/**
 * @title 处理上下游升降级配置结果
 * @desc 处理上下游升降级配置结果
 * @author wyh
 * @version v1
 * @time 2024-05-26
 * @param object param.RouteLogic - 代理模块路由逻辑类对象 require
 * @param object param.supplier - 供应商对象 require
 * @param object param.host - 产品对象 require
 * @param int param.is_downstream - 是否下游，多级代理情况下
 * @param array result - 上游返回的结果数组
 * @param string result.data.price_difference - 升降级差价
 * @param string result.data.renew_price_difference - 续费差价
 * @param string result.data.base_price - 升降级后整个产品的基础价格
 * @return array  - 返回处理结果
 * @return int status - 状态，200或400
 * @return string msg - 描述
 * @return array data - 返回数据，当status==200时，才返回此字段
 * @return string data.base_price - 升降级后整个产品的基础价格
 * @return string data.base_price_client_level_discount - 升降级后整个产品的基础价格折扣
 * @return string data.description - 描述，保存到订单子项描述里
 * @return string data.new_first_payment_amount - 新首付金额
 * @return string data.new_first_payment_amount_client_level_discount - 新首付金额折扣
 * @return string data.price - 购买价格，必须>=0的
 * @return string data.price_difference - 价格差价，可为负数
 * @return string data.price_difference_client_level_discount - 价格差价折扣，可为负数
 * @return string data.profit - 利润，可为负数
 * @return string data.renew_price_difference - 续费差价，可为负数
 * @return string data.renew_price_difference_client_level_discount - 续费差价折扣，可为负数
 */
function upstream_upgrade_result_deal($param,$result)
{
    $UpstreamLogic = new \app\common\logic\UpstreamLogic();

    return $UpstreamLogic->upstreamUpgradeResultDeal($param,$result);
}

/**
 * 时间 2024-05-27
 * @title 验证前台强制安全选项
 * @desc  验证前台强制安全选项
 * @author hh
 * @version v1
 * @return  bool redirect - 是否重定向
 * @return  string url - 重定向地址
 */
function check_home_enforce_safe_method_redirect()
{
    // 当不在账户详情时,判断是否开启了
    $clientId = get_client_id();
    $redirect = false;
    if(!empty($clientId)){
        $client = ClientModel::find($clientId);

        $homeEnforceSafeMethod = configuration(['home_enforce_safe_method']);
        $homeEnforceSafeMethod = !empty($homeEnforceSafeMethod) ? explode(',', $homeEnforceSafeMethod) : [];
        if(!$redirect && in_array('phone', $homeEnforceSafeMethod)){
            $redirect = empty($client['phone']);
        }
        if(!$redirect && in_array('email', $homeEnforceSafeMethod)){
            $redirect = empty($client['email']);
        }
        if(!$redirect && in_array('operate_password', $homeEnforceSafeMethod)){
            $redirect = empty($client['operate_password']);
        }
        if(!$redirect && in_array('certification', $homeEnforceSafeMethod)){
            $certification = hook_one('certification_detail', ['client_id'=>$clientId]);
            if(!empty($certification) && is_array($certification)){
                $redirect = empty($certification['person']) && empty($certification['company']);
            }
        }
        if(!$redirect && in_array('oauth', $homeEnforceSafeMethod)){
            $oauth = OauthModel::where('client_id', $clientId)->find();
            $redirect = empty($oauth);
        }
    }
    return ['redirect'=>$redirect, 'url'=>request()->domain() . '/account.htm'];
}

/**
 * 时间 2024-05-27
 * @title 验证前台强制安全选项
 * @desc  验证前台强制安全选项
 * @author hh
 * @version v1
 * @return  bool redirect - 是否重定向
 * @return  string url - 重定向地址
 */
function check_admin_enforce_safe_method_redirect()
{
    $adminId = get_admin_id();
    $redirect = false;
    if(!empty($adminId)){
        $adminEnforceSafeMethod = configuration(['admin_enforce_safe_method']);
        $adminEnforceSafeMethod = !empty($adminEnforceSafeMethod) ? explode(',', $adminEnforceSafeMethod) : [];
        if(in_array('operate_password', $adminEnforceSafeMethod)){
            // 当前管理员是否设置了操作密码
            $operatePassword = AdminModel::where('id', $adminId)->value('operate_password');
            $redirect = empty($operatePassword);
        }
    }
    return ['redirect'=>$redirect, 'url'=>request()->domain() . '/' . DIR_ADMIN . '/security_center.htm'];
}

/**
 * 时间 2024-06-05
 * @title 是否通知
 * @desc  是否通知
 * @author wyh
 * @version v1
 * @param string param.type - 方式：email邮件，sms短信，
 * @param int param.client_id - 客户ID
 * @return bool -
 */
function client_notice($param)
{
    $ClientModel = new ClientModel();

    return $ClientModel->clientNotice($param);
}


/**
 * @title 创建站内信
 * @desc 创建站内信
 * @author theworld
 * @version v1
 * @param object param - 站内信参数 required
 * @param string param.name - 通知动作英文标识 required
 * @return boolean
 */
function create_internal_message($param)
{
    $result = hook_one('create_internal_message', $param);

    return true;
}

/**
 * @title 获取主域名
 * @desc 获取主域名
 * @author wyh
 * @version v1
 * @param string url - 域名 required
 * @return string
 */
function get_root_domain($url)
{
    // 解析URL并获取主机名部分
    $host = parse_url($url, PHP_URL_HOST);

    // 如果主机名是IP地址，则直接返回空
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return '';
    }

    // 使用正则表达式提取主域名
    if (preg_match('/([a-z0-9-]+\.[a-z]{2,})$/i', $host, $matches)) {
        return $matches[1];
    }

    return '';
}

/**
 * @时间 2024-08-30
 * @title 多语言插件替换内容
 * @desc  多语言插件替换内容
 * @author hh
 * @version v1
 * @param   string $replace - 要替换的内容
 * @return  string
 */
function multi_language_replace($replace)
{
    if(app('http')->getName() == 'home'){
        $multiLanguage = hook_one('multi_language', [
            'replace' => [
                'name'  => $replace,
            ],
        ]);
        if(isset($multiLanguage['name'])){
            $replace = $multiLanguage['name'];
        }
    }
    return $replace;
}

/**
 * @时间 2024-08-30
 * @title 删除无用文件
 * @desc  删除无用文件
 * @author theworld
 * @version v1
 */
function deleteUnusedFile()
{
    if (file_exists(WEB_ROOT . 'tools.php')){
        @unlink(WEB_ROOT . 'tools.php');
    }
    return true;
}

/**
 * @时间 2024-11-27
 * @title 获取登录设备
 * @desc  获取登录设备
 * @author hh
 * @version v1
 * @return  string
 */
function get_request_device()
{
    $userAgent = request()->header('user-agent');
    $userAgent = strtolower($userAgent);

    // 常用软件
    // if(preg_match('/micromessenger/', $userAgent)){
    //     return '微信';
    // }

    // if(preg_match('/alipayclient/', $userAgent)){
    //     return '支付宝';
    // }

    // 手机设备
    if (preg_match('/(android|iphone|ipod|blackberry|iemobile|opera mini)/', $userAgent)) {

        // IPhone
        if(preg_match('/iphone;/', $userAgent)){
            return 'IPhone';
        }
        // oppo
        if(preg_match('/; oppo/', $userAgent)){
            return 'OPPO手机';
        }
        // 红米
        if(preg_match('/(; redmi|; hm note)/', $userAgent)){
            return '红米手机';
        }
        // 小米
        if(preg_match('/; mi/', $userAgent)){
            return '小米手机';
        }
        // 华为
        if(preg_match('/(honor|huawei|; h60-)/', $userAgent)){
            return '华为手机';
        }
        // 联想
        if(preg_match('/; lenovo/', $userAgent)){
            return '联想手机';
        }
        // 联想
        if(preg_match('/; vivo/', $userAgent)){
            return 'VIVO手机';
        }
        // 三星
        if(preg_match('/(; samsung|; nexus|; galaxy|; sm-)/', $userAgent)){
            return '三星手机';
        }
        // 魅族
        if(preg_match('/(; mz|; mx|; m1 note|; m2 note)/', $userAgent)){
            return '魅族手机';
        }
        // 一加
        if(preg_match('/(; oneplus)/', $userAgent)){
            return '一加手机';
        }
        // 酷派
        if(preg_match('/(coolpad)/', $userAgent)){
            return '酷派手机';
        }

        return '移动设备';
    }
    
    // 平板设备
    if (preg_match('/(tablet|ipad|playbook|silk)/', $userAgent)) {
        return '平板设备';
    }
    
    // 智能电视或游戏主机
    if (preg_match('/(smarttv|hbbtv|appletv)/', $userAgent)) {
        return '智能电视';
    }

    if (preg_match('/(xbox|wiiu|playstation)/', $userAgent)) {
        return '游戏主机';
    }
    
    // 桌面设备（包括桌面浏览器和某些未知设备）
    // 注意：这个判断是最后进行的，因为很多设备的User-Agent可能包含“Windows NT”或“Macintosh”等，但实际上是移动设备或平板
    if (preg_match('/(windows nt|macintosh)/', $userAgent) && !preg_match('/(android|iphone|ipod|blackberry|iemobile|opera mini|tablet|ipad|playbook|silk|smarttv|hbbtv|appletv|xbox|wiiu|playstation)/', $userAgent)) {
        return '电脑';
    }
    
    // 未知设备
    return '未知设备';
}

/**
 * @时间 2024-12-18
 * @title 添加通知
 * @desc  添加通知
 * @author wyh
 * @version v1
 * @param string param.type - 消息类型：idcsmart官方通知，system系统通知 required
 * @param string param.title - 消息标题 required
 * @param string param.content - 消息内容 required
 * @param array param.attachment - 附件
 * @param int param.priority - 优先级：0普通，1高
 * @param int param.rel_id - 关联ID
 * @return bool
 */
function add_notice($param)
{
    $NoticeModel = new \app\admin\model\NoticeModel();
    $result = $NoticeModel->createNotice($param);
    return $result['status']==200;
}


function authFailLog($param, $result)
{
    $dir = IDCSMART_ROOT.'/tmp';
    if(!is_dir($dir)){
        mkdir($dir);
    }

    $fp = fopen($dir.'/auth_fail_log.txt', 'a+');

    fwrite($fp, date("Y-m-d H:i:s").' : '.json_encode($param).' : '.json_encode($result).PHP_EOL);

    fclose($fp);

    return true;
}

/**
 * @时间 2025-02-24
 * @title 格式化到期时间用于通知
 * @desc  格式化到期时间用于通知
 * @author hh
 * @version v1
 * @param   int time - 时间戳 require
 * @return  string
 */
function format_due_time_for_noitce($time)
{
    if(empty($time)){
        return '2099-12-31 23:59:59';
    }
    return date('Y-m-d H:i:s', $time);
}

/**
 * @时间 2025-02-24
 * @title 系统通知
 * @desc  系统通知
 * @author hh
 * @version v1
 * @param   array param - 参数
 * @param   string param.name - 发送动作 require
* @param   string param.email_description - 邮件任务描述,为空不发邮件
* @param   string param.sms_description - 短信任务描述,为空不发短信
* @param   array param.task_data - 任务数据
* @param   int param.task_data.order_id - 获取订单/用户相关参数
* @param   int param.task_data.host_id - 获取产品/用户相关参数
* @param   int param.task_data.client_id - 获取用户相关参数
* @param   array param.task_data.template_param - 模板变量
 */
function system_notice(array $param)
{
    $SystemNoticeLogic = new \app\common\logic\SystemNoticeLogic($param);
    $SystemNoticeLogic->exec();
}

/**
 * @时间 2025-11-20
 * @title 亏本交易拦截检查
 * @desc  检查上游价格是否高于本地售价,根据配置进行通知或拒绝
 * @author wyh
 * @version v1
 * @param   int orderId - 订单ID require
 * @param   float upstreamAmount - 上游价格 require
 * @param   string scene - 场景类型(new_order/renew/upgrade) require
 * @param   string productName - 商品名称(可选,用于通知)
 * @param   int hostId - 产品ID
 * @return  array ['pass'=>true/false, 'msg'=>'错误信息']
 */
function checkLossTrade($orderId, $upstreamAmount, $scene, $productName = '', $hostId = 0)
{
    // 3. 计算本地售价
    // 如果传入了hostId,只计算该产品的金额;否则计算整个订单金额
    $OrderItemModel = new \app\common\model\OrderItemModel();
    $query = $OrderItemModel->where('order_id', $orderId);

    if ($hostId > 0) {
        $query->where('host_id', $hostId);
    }

    $localAmount = $query->sum('amount');

    if ($localAmount === null) {
        $localAmount = 0;
    }
    // wyh 20251127 修改上游订单金额和利润，平台币导致只能这里处理
    UpstreamOrderModel::update([
        'amount' => $localAmount,
        'profit' => bcsub($localAmount, $upstreamAmount, 2)
    ], ['order_id' => $orderId]);
    try {
        // 1. 根据场景获取对应的配置项
        $sceneConfigMap = [
            'new_order' => [
                'notify' => 'upstream_intercept_new_order_notify',
                'reject' => 'upstream_intercept_new_order_reject',
            ],
            'renew' => [
                'notify' => 'upstream_intercept_renew_notify',
                'reject' => 'upstream_intercept_renew_reject',
            ],
            'upgrade' => [
                'notify' => 'upstream_intercept_upgrade_notify',
                'reject' => 'upstream_intercept_upgrade_reject',
            ],
        ];
        // 2. 检查场景是否有效
        if (!isset($sceneConfigMap[$scene])) {
            return ['pass' => true, 'msg' => ''];
        }
        // 3. 获取场景配置
        $configKeys = $sceneConfigMap[$scene];
        $ConfigurationModel = new ConfigurationModel();
        $config = $ConfigurationModel->whereIn('setting',[$configKeys['notify'],$configKeys['reject']])->select()->toArray();
        $configMap = array_column($config, 'value', 'setting');
        $notify = (int)$configMap[$configKeys['notify']];
        $reject = (int)$configMap[$configKeys['reject']];
//        $notify = (int)configuration($configKeys['notify']);
//        $reject = (int)configuration($configKeys['reject']);
        // 如果两个都未启用,直接通过
        if (!$notify && !$reject) {
            return ['pass' => true, 'msg' => ''];
        }

        // 4. 判断是否价格倒挂(上游价格 > 本地售价)
        // 转为分(整数)比较,避免浮点数精度问题
        $upstreamCent = intval(bcmul($upstreamAmount, 100, 0));
        $localCent = intval(bcmul($localAmount, 100, 0));
        
        if ($upstreamCent <= $localCent) {
            // 没有倒挂,通过
            return ['pass' => true, 'msg' => ''];
        }
        
        // 5. 价格倒挂处理
        // 获取商品名称(如果未传入)
        if (empty($productName)) {
            if ($hostId > 0){
                $HostModel = new \app\common\model\HostModel();
                $host = $HostModel->find($hostId);
                $ProductModel = new ProductModel();
                $product = $ProductModel->find($host['product_id']??0);
                $productName = $product['name'] ?? '未知商品';
            }else{
                $OrderModel = new \app\common\model\OrderModel();
                $orderInfo = $OrderModel->alias('o')
                    ->leftJoin('order_item oi', 'o.id=oi.order_id')
                    ->leftJoin('product p', 'oi.product_id=p.id')
                    ->where('o.id', $orderId)
                    ->field('p.name as product_name')
                    ->find();
                $productName = $orderInfo['product_name'] ?? '未知商品';
            }
        }
        
        // 场景名称映射
        $sceneNameMap = [
            'new_order' => '新购',
            'renew' => '续费',
            'upgrade' => '升降级'
        ];
        $orderType = $sceneNameMap[$scene] ?? $scene;
        
        // 5a. 发送通知
        if ($notify) {
            system_notice([
                'name' => 'loss_trade_alert',
                'email_description' => '价格倒挂通知邮件',
                'sms_description' => '价格倒挂通知短信',
                'task_data' => [
                    'order_id' => $orderId,
                    'template_param' => [
                        'product_name' => $productName,
                        'purchase_price' => number_format($upstreamAmount, 2),
                        'order_amount' => number_format($localAmount, 2),
                        'order_type' => $orderType,
                        'system_website_name' => configuration('website_name') ?: '系统',
                        'system_logo_url' => configuration('logo_url') ?: '',
                        'send_time' => date('Y-m-d H:i:s')
                    ]
                ]
            ]);
        }

        // 5b. 是否拒绝
        if ($reject) {
            return [
                'pass' => false,
                'msg' => sprintf(
                    '检测到价格倒挂,禁止交易。商品:%s,上游价格:%.2f元,平台售价:%.2f元',
                    $productName,
                    $upstreamAmount,
                    $localAmount
                )
            ];
        }
        
        // 只通知不拒绝
        return ['pass' => true, 'msg' => ''];
        
    } catch (\Exception $e) {
        // 异常情况不影响正常流程,记录日志后放行
        active_log('亏本交易检查异常: ' . $e->getMessage(), 'order', $orderId);
        return ['pass' => true, 'msg' => ''];
    }
}

/**
 * @时间 2025-03-28
 * @title 计算计费时长
 * @desc  计算计费时长
 * @author hh
 * @version v1
 * @param   int startTime - 开始时间 require
 * @param   int endTime - 结束时间 require
 * @param   int minUsageTime - 最小使用时间,NULL不对比
 * @return  string
 */
function cal_billing_time($startTime, $endTime, $minUsageTime = NULL): string
{
    // 计算时间差
    $diffTime = $endTime - $startTime;
    $diffTime = $diffTime > 0 ? $diffTime : 0;

    if(is_numeric($minUsageTime) && $diffTime < $minUsageTime){
        $diffTime = $minUsageTime;
    }

    // 目前是按小时计费,换算为小时
    $hour = round($diffTime/3600, 6);
    $hour = amount_format($hour, 4);

    return $hour;
}

/**
 * @时间 2025-04-02
 * @title 自动直接支付订单
 * @desc  自动直接支付订单
 * @author hh
 * @version v1
 * @param   array param - 参数 require
 * @param   int param.order_id - 订单ID require
 * @param   bool param.is_admin false 是否管理员操作
 * @param   array param.gateway - 使用的支付方式,将会按顺序使用直到成功,默认余额
 * @return  int status - 状态码(200=成功,400=失败)
 * @return  string msg - 信息
 */
function auto_direct_pay_order($param): array
{
    // 支付方式
    $gateway = $param['gateway'] ?? [
        'credit',
    ];

    $result = [
        'status' => 400,
        'msg'    => lang('gateway_error'),
    ];
    foreach($gateway as $v){
        // 余额支付
        if($v == 'credit'){
            $OrderTmpModel = new OrderTmpModel();
            $OrderTmpModel->isAdmin = $param['is_admin'] ?? false;
            $res = $OrderTmpModel->pay([
                'id'        => $param['order_id'],
                'gateway'   => 'credit',
            ]);
            if($res['status'] == 200 && $res['code'] == 'Paid'){
                $result = [
                    'status' => 200,
                    'msg'    => lang('buy_success'),
                ];
                break;
            }else{
                $result = [
                    'status' => 400,
                    'msg'    => $res['msg'],
                ];
            }
        }else{
            // 其他支付方式
            $hookParam = [
                'order_id'  => $param['order_id'],
                'is_admin'  => $param['is_admin'] ?? false,
                'gateway'   => $v,
            ];
            $hookRes = hook('auto_direct_pay_order', $hookParam);
            foreach($hookRes as $v){
                if(!empty($v) && is_array($v) && isset($v['status']) && isset($v['msg'])){
                    $result = $v;
                    if($result['status'] == 200){
                        break 2;
                    }
                }
            }
        }
    }
    return $result;
}

/**
 * 遍历获取主题目录名称
 * @return array 返回所有主题目录名称的去重数组
 */
function getThemeDirectories()
{
    $basePath = IDCSMART_ROOT . 'public/';
    $modules = ['cart', 'clientarea', 'home'];
    $allThemes = [];

    foreach ($modules as $module) {
        $templatePath = $basePath . $module . '/template/';

        if (!is_dir($templatePath)) {
            continue;
        }

        // 扫描移动端主题
        $mobilePath = $templatePath . 'mobile/';
        if (is_dir($mobilePath)) {
            $themes = scanThemeDirectory($mobilePath);
            $allThemes = array_merge($allThemes, $themes);
        }

        // 扫描PC端主题
        $pcPath = $templatePath . 'pc/';
        if (is_dir($pcPath)) {
            $themes = scanThemeDirectory($pcPath);
            $allThemes = array_merge($allThemes, $themes);
        }
    }

    // 去重并重新索引
    return array_values(array_unique($allThemes));
}

/**
 * 扫描指定目录下的主题目录名称
 * @param string $path 要扫描的路径
 * @return array 主题目录名称列表
 */
function scanThemeDirectory($path)
{
    $themes = [];

    if (!is_dir($path)) {
        return $themes;
    }

    $directories = scandir($path);

    foreach ($directories as $dir) {
        if ($dir === '.' || $dir === '..' || $dir === 'readme.md') {
            continue;
        }

        $fullPath = $path . $dir;

        // 检查是否为目录
        if (is_dir($fullPath)) {
            $themes[] = $dir;
        }
    }

    return $themes;
}

/**
 * 时间 2025-11-25
 * @title 处理安全验证
 * @desc 处理敏感操作的安全验证
 * @author wyh
 * @version v1
 * @param int $clientId 用户ID
 * @param array $param 请求参数
 * @param string $scene 场景
 * @return array
 */
function handle_security_verify($clientId, $param, $scene)
{
    $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();

    // 1. 检查是否已提交安全验证参数
    if (!empty($param['security_verify_method'])) {
        switch ($param['security_verify_method']) {
            case 'operate_password':
                // 验证操作密码
                if (empty($param['security_verify_value'])) {
                    return ['status' => 400, 'msg' => lang('operate_password_required')];
                }
                $verifyResult = $SecurityVerifyLogic->verifyOperatePassword($clientId, $param['security_verify_value']);
                if ($verifyResult['status'] !== 200) {
                    return $verifyResult;
                }
                return ['status' => 200];

            case 'email_code':
                // 验证邮箱验证码
                if (empty($param['security_verify_value'])) {
                    return ['status' => 400, 'msg' => lang('verification_code_required')];
                }
                $verifyResult = $SecurityVerifyLogic->verifyCode($clientId, 'email_code', $param['security_verify_value'], $scene);
                if ($verifyResult['status'] !== 200) {
                    return $verifyResult;
                }
                return ['status' => 200];

            case 'phone_code':
                // 验证手机验证码
                if (empty($param['security_verify_value'])) {
                    return ['status' => 400, 'msg' => lang('verification_code_required')];
                }
                $verifyResult = $SecurityVerifyLogic->verifyCode($clientId, 'phone_code', $param['security_verify_value'], $scene);
                if ($verifyResult['status'] !== 200) {
                    return $verifyResult;
                }
                return ['status' => 200];

            case 'certification':
                // 验证实名认证
                if (empty($param['certify_id'])) {
                    return ['status' => 400, 'msg' => lang('certify_id_required')];
                }
                if (!$SecurityVerifyLogic->isCertificationPassed($clientId, $param['certify_id'])) {
                    return ['status' => 400, 'msg' => lang('certification_not_passed')];
                }
                return ['status' => 200];

            default:
                return ['status' => 400, 'msg' => lang('security_verify_method_invalid')];
        }
    }

    // 2. 获取可用验证方式
    $availableMethods = $SecurityVerifyLogic->getAvailableMethods($clientId, $scene);

    // 如果没有可用验证方式，直接通过
    if (empty($availableMethods)) {
        return ['status' => 200];
    }

    // 3. 返回需要验证的提示
    return [
        'status' => 400,
        'msg' => lang('security_verify_required'),
        'data' => [
            'need_security_verify' => true,
            'available_methods' => $availableMethods
        ]
    ];
}

/**
 * 时间 2026-01-06
 * @title 计算自然月预付费的到期时间
 * @desc 计算自然月预付费的到期时间
 * @author hh
 * @version v1
 * @param int $startTime - 开始时间戳 require
 * @param int $cycleTime - 周期时长(1=月,3=季,12=年) require
 * @return int 到期时间戳(下一个周期的1号零点)
 */
function calculate_natural_month_due_time($startTime, $cycleTime)
{
    $startYear = date('Y', $startTime);
    $startMonth = intval(date('m', $startTime));
    
    if ($cycleTime == 1) {
        // 月付：下个月1号零点
        $dueDate = date('Y-m-01 00:00:00', strtotime(date('Y-m-d', $startTime) . " +1 month"));
    } elseif ($cycleTime == 3) {
        // 季付：下一个季度首月(1、4、7、10月)的1号零点
        if ($startMonth >= 1 && $startMonth <= 3) {
            $dueDate = $startYear . '-04-01 00:00:00';
        } elseif ($startMonth >= 4 && $startMonth <= 6) {
            $dueDate = $startYear . '-07-01 00:00:00';
        } elseif ($startMonth >= 7 && $startMonth <= 9) {
            $dueDate = $startYear . '-10-01 00:00:00';
        } else { // 10-12月
            $dueDate = ($startYear + 1) . '-01-01 00:00:00';
        }
    } elseif ($cycleTime == 12) {
        // 年付：下一年1月1日零点
        $dueDate = ($startYear + 1) . '-01-01 00:00:00';
    } else {
        // 其他周期按原逻辑
        $dueDate = date('Y-m-01 00:00:00', strtotime(date('Y-m-d', $startTime) . " +{$cycleTime} month"));
    }
    
    return strtotime($dueDate);
}

/**
 * 时间 2026-01-06
 * @title 自然月预付费价格按天折算
 * @desc 自然月预付费价格按天折算
 * @author hh
 * @version v1
 * @param float $price - 周期原价 require
 * @param int $startTime - 开始时间戳 require
 * @param int $endTime - 结束时间戳(自然月到期时间) require
 * @param int $cycleTime - 周期时长(1=月,3=季,12=年) require
 * @return float 折算后的价格
 */
function calculate_natural_month_price($price, $startTime, $endTime, $cycleTime)
{
    // 计算实际使用天数
    $actualDays = ceil(($endTime - $startTime) / 86400);
    
    // 计算周期总天数(按自然月计算)
    $totalDays = 0;
    $startYear = date('Y', $startTime);
    $startMonth = intval(date('m', $startTime));
    
    if ($cycleTime == 1) {
        // 月付：当月天数
        $totalDays = date('t', $startTime);
    } elseif ($cycleTime == 3) {
        // 季付：当前所在季度的三个月天数(1-3、4-6、7-9、10-12)
        if ($startMonth >= 1 && $startMonth <= 3) {
            $totalDays = date('t', strtotime($startYear . '-01-01')) + 
                        date('t', strtotime($startYear . '-02-01')) + 
                        date('t', strtotime($startYear . '-03-01'));
        } elseif ($startMonth >= 4 && $startMonth <= 6) {
            $totalDays = date('t', strtotime($startYear . '-04-01')) + 
                        date('t', strtotime($startYear . '-05-01')) + 
                        date('t', strtotime($startYear . '-06-01'));
        } elseif ($startMonth >= 7 && $startMonth <= 9) {
            $totalDays = date('t', strtotime($startYear . '-07-01')) + 
                        date('t', strtotime($startYear . '-08-01')) + 
                        date('t', strtotime($startYear . '-09-01'));
        } else { // 10-12月
            $totalDays = date('t', strtotime($startYear . '-10-01')) + 
                        date('t', strtotime($startYear . '-11-01')) + 
                        date('t', strtotime($startYear . '-12-01'));
        }
    } elseif ($cycleTime == 12) {
        // 年付：当前年份的总天数
        $totalDays = (date('L', $startTime) ? 366 : 365);
    } else {
        // 其他周期：从当月开始往后推cycleTime个月
        $currentTime = strtotime($startYear . '-' . $startMonth . '-01');
        for ($i = 0; $i < $cycleTime; $i++) {
            $totalDays += date('t', $currentTime);
            $currentTime = strtotime('+1 month', $currentTime);
        }
    }
    
    // 按天折算
    if ($totalDays > 0) {
        return floatval(bcmul($price, bcdiv($actualDays, $totalDays, 6), 2));
    }
    
    return floatval($price);
}
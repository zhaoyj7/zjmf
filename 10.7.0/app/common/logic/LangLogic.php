<?php 
namespace app\common\logic;

use think\facade\Cache;
use app\admin\model\PluginModel;

/**
 * @title 语言逻辑类
 * @desc  语言逻辑类
 * @use app\common\logic\LangLogic
 */
class LangLogic
{
	// 系统语言缓存
	public static $lang = [];

	// 插件语言缓存
	public static $pluginLang = [];

	// 当前语言标识
	public static $defaultLang = '';

	// 可用语言标识
	public static $enableLang = [
		'zh-cn',
		'en-us',
		'zh-hk',
	];

	/**
	 * 时间 2024-05-20
	 * @title 语言检测
	 * @desc  语言检测
	 * @author hh
	 * @version v1
	 * @param   string
	 */
	public static function getDefaultLang()
	{
		if(empty(self::$defaultLang)){
			$defaultLang = config('lang.default_lang');
		    if(!empty(get_client_id())){
		        $defaultLang = get_client_lang();
		    }else{
		        $app = app('http')->getName();
		        if ($app=='home'){
		            $defaultLang = get_client_lang();
		        }else{
		            $defaultLang = get_system_lang(true);
		        }
		    }
		    // 判断可用语言
		    if(!in_array($defaultLang, self::$enableLang)){
		    	$defaultLang = 'zh-cn';
		    }
		    self::$defaultLang = $defaultLang;
		}
		return self::$defaultLang;
	}

	/**
	 * 时间 2024-05-28
	 * @title 加载系统语言
	 * @desc  加载系统语言
	 * @author hh
	 * @version v1
	 */
	public static function loadSystemLang()
	{
		$defaultLang = self::getDefaultLang();
		if(empty(self::$lang)){
			$langAdmin = include WEB_ROOT.'/'.DIR_ADMIN.'/language/'. $defaultLang .'.php';
			$langHome = include WEB_ROOT.'/clientarea/language/'. $defaultLang .'.php';
			self::$lang = array_merge($langAdmin, $langHome);
		}
	}

	/**
	 * 时间 2024-05-28
	 * @title 系统语言渲染
	 * @desc  系统语言渲染
	 * @author hh
	 * @version v1
	 * @param   string name - 语言标识 require
	 * @param   array  param - 语言替换数组
	 * @return  string|array
	 */
	public static function renderSystemLang($name = '', $vars = [])
	{
		// 加载语言包
		self::loadSystemLang();
		if(empty($name)){
			$value = self::$lang;
		}else if(!isset(self::$lang[$name])){
			$value = $name;
		}else{
			$value = self::$lang[$name];

			if(!empty($vars) && is_array($vars)){
				// 关联索引解析
                $replace = array_keys($vars);
                $value = str_replace($replace, $vars, $value);
			}
		}
		return $value;
	}

	/**
	 * 时间 2024-05-28
	 * @title 加载插件语言
	 * @desc  加载插件语言
	 * @author hh
	 * @version v1
	 * @param   boolean reload - 是否重新加载
	 */
	public static function loadPluginLang($reload = false)
	{
		$defaultLang = self::getDefaultLang();
		$cacheName = \app\common\logic\CacheLogic::CACHE_LANG_PREFIX . $defaultLang;
		if($reload){
			$lang = [];
	        # 加载插件多语言(wyh 20220616 改:涉及到一个插件需要调另一个插件以及系统调插件钩子的情况,所以只有加载所有已安装使用插件的多语言)
	        $addonDir = WEB_ROOT . 'plugins/addon/';
	        $addons = array_map('basename', glob($addonDir . '*', GLOB_ONLYDIR));
	        $PluginModel = new PluginModel();
	        foreach ($addons as $addon){
	            $parseName = parse_name($addon,1);
	            # 说明:存在一定的安全性,判断是否安装且启用的插件
	            $plugin = $PluginModel->where('name',$parseName)
	                //->where('status',1)
	                ->find();
	            if (!empty($plugin) && is_file($addonDir . $addon . "/lang/{$defaultLang}.php")){
	                $pluginLang = include $addonDir . $addon . "/lang/{$defaultLang}.php";
	                if(is_array($pluginLang)){
	                	$lang = array_merge($lang,$pluginLang);
	                }
	            }
	        }
	        # 加载模块多语言
	        $serverDir = WEB_ROOT . 'plugins/server/';
	        $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
	        foreach ($servers as $server){
	            if (is_file($serverDir . $server . "/lang/{$defaultLang}.php")){
	                $pluginLang = include $serverDir . $server . "/lang/{$defaultLang}.php";
	                if(is_array($pluginLang)){
	                	$lang = array_merge($lang,$pluginLang);
	                }
	            }
	        }

	        # 加载模块多语言
	        $reserverDir = WEB_ROOT . 'plugins/reserver/';
	        $servers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
	        foreach ($servers as $server){
	            if (is_file($reserverDir . $server . "/lang/{$defaultLang}.php")){
	                $pluginLang = include $reserverDir . $server . "/lang/{$defaultLang}.php";
	                if(is_array($pluginLang)){
	                	$lang = array_merge($lang,$pluginLang);
	                }
	            }
	        }

	        # 加载模板控制器多语言
	        $templateDir = WEB_ROOT . 'web/';
	        $templates = array_map('basename', glob($templateDir . '*', GLOB_ONLYDIR));
	        foreach ($templates as $template){
	            if (is_file($templateDir . $template . "/controller/lang/{$defaultLang}.php")){
	                $pluginLang = include $templateDir . $template . "/controller/lang/{$defaultLang}.php";
	                if(is_array($pluginLang)){
	                	$lang = array_merge($lang,$pluginLang);
	                }
	            }
	        }
	        idcsmart_cache($cacheName, json_encode($lang), 24*3600);
	        self::$pluginLang = $lang;
		}else{
			if(empty(self::$pluginLang)){
				$lang = idcsmart_cache($cacheName);
				if(empty($lang)){
					// 没有语言缓存,重新加载
					self::loadPluginLang(true);
				}else{
					self::$pluginLang = json_decode($lang, true);
				}
			}
		}
	}

	/**
	 * 时间 2024-05-28
	 * @title 插件语言渲染
	 * @desc  插件语言渲染
	 * @author hh
	 * @version v1
	 * @param   string name - 语言标识 require
	 * @param   array  vars - 语言替换数组
	 * @param   bool  reload false 是否更新缓存
	 * @return  string|array
	 */
	public static function renderPluginLang($name = '', $vars = [], $reload = false)
	{
		// 加载语言包
		self::loadPluginLang($reload);
		if(empty($name)){
			$value = self::$pluginLang;
		}else if(!isset(self::$pluginLang[$name])){
			$value = $name;
		}else{
			$value = self::$pluginLang[$name];

			if(!empty($vars) && is_array($vars)){
				// 关联索引解析
                $replace = array_keys($vars);
                $value = str_replace($replace, $vars, $value);
			}
		}
		return $value;
	}


}
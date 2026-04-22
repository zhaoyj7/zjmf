<?php
namespace app\home\controller;

use addon\idcsmart_news\model\IdcsmartNewsModel;
use think\facade\View;
use app\admin\model\PluginModel;
use think\template\exception\TemplateNotFoundException;

class ViewClientController extends HomeBaseController
{
    /**
     * 时间 2023-05-04
     * @title 前台会员中心首页模板统一入口
     * @desc 前台会员中心首页模板统一入口
     * @url /console
     * @method GET
     * @author wyh
     * @version v1
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function index()
    {
        $param = $this->request->param();

        hook('clientarea_index',$param);

        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog'] = 'clientarea';
        $tplName = empty($param['view_html'])?'home':$param['view_html'];

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            cookie('clientarea_theme_mobile',$param['theme_mobile']??"default");
            $data['themes'] = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile']??"default";
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else{
            $data['themes'] = configuration('clientarea_theme');
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }

        $mobile = use_mobile();
        $cartTheme = $mobile?configuration('cart_theme_mobile'):configuration("cart_theme");

        if(in_array($cartTheme, ['mf101','mfm201','mf501'])){
            $info = configuration('idcsmartauthinfo');
            if(!empty($info)){
                $code = explode('|zjmf|', base64_decode($info));
                $authkey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDg6DKmQVwkQCzKcFYb0BBW7N2f
I7DqL4MaiT6vibgEzH3EUFuBCRg3cXqCplJlk13PPbKMWMYsrc5cz7+k08kgTpD4
tevlKOMNhYeXNk5ftZ0b6MAR0u5tiyEiATAjRwTpVmhOHOOh32MMBkf+NNWrZA/n
zcLRV8GU7+LcJ8AH/QIDAQAB
-----END PUBLIC KEY-----";
                $pukey = openssl_pkey_get_public($authkey);
                $destr = '';
                foreach($code AS $v){
                    openssl_public_decrypt(base64_decode($v),$de,$pukey);
                    $destr .= $de;
                }
                $auth = json_decode($destr,true);
                if(isset($auth['app'])){
                    if(!in_array(parse_name($cartTheme, 1), $auth['app'])){
                        $cartTheme = 'default';
                        $data['themes_mobile'] = 'default';
                    }
                }else{
                    $cartTheme = 'default';
                    $data['themes_mobile'] = 'default';
                }
            }else{
                $cartTheme = 'default';
                $data['themes_mobile'] = 'default';
            }
        }

        if ($mobile){
            $view_path = '../public/clientarea/template/mobile/'.$data['themes_mobile'].'/';
            $data['themes'] = 'mobile/' . $data['themes_mobile'];
            if(!file_exists(IDCSMART_ROOT."public/clientarea/template/{$data['themes']}/{$tplName}.php")){
                $view_path = '../public/clientarea/template/mobile/default/';
            }
            $data['themes_cart'] = 'mobile/'.$cartTheme; // 购物车主题
        }else{
            $view_path = '../public/clientarea/template/pc/'.$data['themes'].'/';
            $data['themes'] = 'pc/' . $data['themes'];
            if(!file_exists(IDCSMART_ROOT."public/clientarea/template/{$data['themes']}/{$tplName}.php")){
                $view_path = '../public/clientarea/template/pc/default/';
                // 使用默认主题
                $data['themes'] = 'pc/default';
            }
            $data['themes_cart'] = 'pc/'.$cartTheme; // 购物车主题
        }

        $data['public_themes'] = $data['themes'];

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $data['addons'] = $addons['list'];

        $data['iframe_url'] = $param['iframe_url']??"";

        $config['view_path'] = $view_path;

        $data['system_version'] = configuration('system_version');
        $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
        if(!in_array($tplName, ['account',"login","userStatus","goodsList","source","news_detail","helpTotal","goods","goods_iframe","agreement","regist","forget","oauth"])){
            $check = check_home_enforce_safe_method_redirect();
            if($check['redirect']){
                header('Location: '.$check['url']);die;
            }
        }
        View::config($config);

        if (APP_DEBUG){
            $content = View::fetch("/".$tplName,$data);
        }else{
            try {
                $content = View::fetch("/".$tplName,$data);
            }catch (\Exception $e){
                return (new ViewController(app()))->errorPage();
            }
        }

        // css,js追加系统版本
        $version = '?v='.configuration('system_version'); 

        $pattern = '/<link\s+[^>]*?href="([^"]+\.css)"[^>]*>/i';
          
        $content = preg_replace_callback($pattern, function($matches) use ($version) {  
            return str_replace($matches[1], $matches[1] . $version, $matches[0]);  
        }, $content);  

        $pattern = '/<script\s+[^>]*? src = "([^"]+\.js) "[^>]*>/i';

            $content = preg_replace_callback($pattern, function ($matches) use($version) {
                return str_replace($matches[1], $matches[1].$version, $matches[0]);
            }, $content);

            return $content;
    }

    /**
     * 时间 2023-05-04
     * @title 前台会员中心插件模板统一入口
     * @desc 前台会员中心插件模板统一入口
     * @url /console/plugin/:plugin_id/:view_html
     * @method GET
     * @author wyh
     * @version v1
     * @param int plugin_id - desc:插件ID validate:required
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function plugin() {
        $param = $this -> request -> param();
        $plugin_id = $param['plugin_id'];
        // 新闻特殊判断
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->find($plugin_id);
        if (!empty($plugin) && $plugin['name']=='IdcsmartNews'){
            $id = $param['id'] ?? 0;
            $IdcsmartNewsModel = new IdcsmartNewsModel();
            $content = $IdcsmartNewsModel->where('id',$id)->value('content');
        }
        $data['news_template_seo'] = $content??'';

        $tplName = empty($param['view_html']) ? 'index' : $param['view_html'];
        $addon = (new PluginModel()) -> plugins('addon')['list'];
        $addon = array_column($addon, 'name', 'id');
        $name = parse_name($addon[$plugin_id] ?? '');
        if (empty($name)) {
            throw new TemplateNotFoundException(lang('not_found'), $name);
            #exit('not found template1');
        }
        if ($name != 'idcsmart_certification') {
            $check = check_home_enforce_safe_method_redirect();
            if ($check['redirect']) {
                header('Location: '.$check['url']); die;
            }
        }
        $mobile = use_mobile();
        $data['template_catalog'] = 'clientarea';

        if (isset($param['theme']) && !empty($param['theme'])) {
            cookie('clientarea_theme', $param['theme']);
            cookie('clientarea_theme_mobile', $param['theme_mobile'] ?? "default");
            $data['themes'] = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile'] ?? "default";
        } elseif(cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else {
            $data['themes'] = configuration('clientarea_theme');
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }
        if ($mobile) {
            $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/mobile/'.$data['themes_mobile'].'/'; // mobile/ 插件先不处理手机端
            $data['themes'] = 'mobile/'.$data['themes_mobile'];
            $data['public_themes'] = $data['themes'];
            // wyh 20250423 改，插件手机模板不存在时，则使用手机端默认模板；手机端默认模板不存在时，则使用pc端配置的模板；pc端配置模板不存在时，则使用pc端默认模板；
            if (!file_exists($tpl.$tplName.".html")){
                $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/mobile/default/';
                $data['themes'] = 'mobile/default';
                $data['public_themes'] = $data['themes'];
                if (!file_exists($tpl.$tplName.".html")){
                    $tpl = '../public/plugins/addon/'.$name."/template/clientarea/pc/{$data['themes']}/";
                    $data['themes'] = "pc/{$data['themes']}";
                    $data['public_themes'] = $data['themes'];
                    if (!file_exists($tpl.$tplName.".html")){
                        $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/pc/default/';
                        $data['themes'] = 'pc/default';
                        $data['public_themes'] = $data['themes'];
                    }
                }
            }
        } else {
            if(file_exists(IDCSMART_ROOT."/public/clientarea/template/pc/{$data['themes']}/header.php") && file_exists(IDCSMART_ROOT."/public/clientarea/template/pc/{$data['themes']}/footer.php")){
                $data['public_themes'] = 'pc/'.$data['themes'];
            }else{
                $data['public_themes'] = 'pc/default';
            }

            $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/pc/'.$data['themes'].'/'; // pc/

            if(!file_exists(IDCSMART_ROOT."public/plugins/addon/{$name}/template/clientarea/pc/{$data['themes']}/{$tplName}.html")){
                $tpl = '../public/plugins/addon/'.$name.'/template/clientarea/pc/default/'; // pc/
                $data['themes'] = 'default';
            }

            $data['themes'] = 'pc/'.$data['themes'];
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel -> plugins('addon');

        $data['addons'] = $addons['list'];

        $data['system_version'] = configuration('system_version');
        $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
        if (file_exists($tpl.$tplName.".html")) {
            $content = $this -> view('header', $data);
            $content.= $this -> pluginView($tplName, $data, $name);
            $content.= $this -> view('footer', $data);

            // css,js追加系统版本
            $version = '?v='.configuration('system_version');

            $pattern = '/<link\s+[^>]*?href="([^"]+\.css)"[^>]*>/i';

            $content = preg_replace_callback($pattern, function ($matches) use($version) {
                return str_replace($matches[1], $matches[1].$version, $matches[0]);
            }, $content);

            $pattern = '/<script\s+[^>]*?src="([^"]+\.js)"[^>]*>/i';

            $content = preg_replace_callback($pattern, function ($matches) use($version) {
                return str_replace($matches[1], $matches[1].$version, $matches[0]);
            }, $content);

            return $content;
        } else {
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
            #exit('not found template');
        }

    }

    /**
     * 时间 2024-10-17
     * @title 前台会员中心模块模板统一入口
     * @desc 前台会员中心模块模板统一入口
     * @url /console/module/:module/:view_html
     * @method GET
     * @author hh
     * @version v1
     * @param string module - desc:模块名称 validate:required
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function module() {
        $param = $this->request->param();
        $name = $param['module'];

        $tplName = empty($param['view_html']) ? 'index' : $param['view_html'];
        
        $module = parse_name($name ?? '', 1);
        $module = PluginModel::where('name', $module)->where('status', 1)->where('module', 'server')->find();
        if (empty($module)) {
            throw new TemplateNotFoundException(lang('not_found'), $tplName);
            #exit('not found template1');
        }
        $name = parse_name($module['name']);
        
        $mobile = use_mobile();
        $data['template_catalog'] = 'clientarea';

        if (isset($param['theme']) && !empty($param['theme'])) {
            cookie('clientarea_theme', $param['theme']);
            cookie('clientarea_theme_mobile', $param['theme_mobile'] ?? "default");
            $data['themes'] = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile'] ?? "default";
        } elseif(cookie('clientarea_theme')){
            $data['themes'] = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else {
            $data['themes'] = configuration('clientarea_theme');
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }

        // TODO wyh 与国际版有区别
        if ($mobile) {
            $tpl = '../public/plugins/server/'.$name.'/template/clientarea/mobile/'.$data['themes_mobile'].'/'; // mobile/ 插件先不处理手机端
            $data['themes'] = 'mobile/'.$data['themes_mobile'];
            $data['public_themes'] = $data['themes'];
        } else {
            if(file_exists(IDCSMART_ROOT."/public/clientarea/template/pc/{$data['themes']}/header.php") && file_exists(IDCSMART_ROOT."/public/clientarea/template/pc/{$data['themes']}/footer.php")){
                $data['public_themes'] = 'pc/'.$data['themes'];
            }else{
                $data['public_themes'] = 'pc/default';
            }

            $tpl = '../public/plugins/server/'.$name.'/template/clientarea/pc/'.$data['themes'].'/'; // pc/

            if(!file_exists(IDCSMART_ROOT."public/plugins/server/{$name}/template/clientarea/pc/{$data['themes']}/{$tplName}.html")){
                $tpl = '../public/plugins/server/'.$name.'/template/clientarea/pc/default/'; // pc/
                $data['themes'] = 'default';
            }

            $data['themes'] = 'pc/'.$data['themes'];
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel -> plugins('addon');

        $data['addons'] = $addons['list'];

        $data['system_version'] = configuration('system_version');
        $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
        if (file_exists($tpl.$tplName.".html")) {
            $content = $this -> view('header', $data);
            $content.= $this -> moduleView($tplName, $data, $name);
            $content.= $this -> view('footer', $data);

            // css,js追加系统版本
            $version = '?v='.configuration('system_version');

            $pattern = '/<link\s+[^>]*?href="([^"]+\.css)"[^>]*>/i';

            $content = preg_replace_callback($pattern, function ($matches) use($version) {
                return str_replace($matches[1], $matches[1].$version, $matches[0]);
            }, $content);

            $pattern = '/<script\s+[^>]*?src="([^"]+\.js)"[^>]*>/i';

            $content = preg_replace_callback($pattern, function ($matches) use($version) {
                return str_replace($matches[1], $matches[1].$version, $matches[0]);
            }, $content);

            return $content;
        } else {
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
            #exit('not found template');
        }

    }


    private function view($tplName, $data) {
        View:: config(['view_path' => '../public/clientarea/template/'.$data['public_themes'].'/', 'view_suffix' => 'php']);
        return View:: fetch('/'.$tplName, $data);
    }

    private function pluginView($tplName, $data, $name) {
        View:: config(['view_path' => '../public/plugins/addon/'.$name.'/template/clientarea/'.$data['themes'].'/', 'view_suffix' => 'html']);
        return View:: fetch('/'.$tplName, $data);
    }

    private function moduleView($tplName, $data, $name) {
        View:: config(['view_path' => '../public/plugins/server/'.$name.'/template/clientarea/'.$data['themes'].'/', 'view_suffix' => 'html']);
        return View:: fetch('/'.$tplName, $data);
    }

    //模板继承文件读取
    private function themeConfig($file) {
        $theme = $file.'/theme.config'; $themes = [];
        if (file_exists($theme)) {
            $theme = file_get_contents($theme);

            $theme = explode("\r\n", $theme);
            $theme = array_filter($theme);

            foreach($theme as $v){
                $theme_config = explode(":", $v);
                $themes[trim($theme_config[0])] = trim(trim(trim($theme_config[1], "'"), '"'));
            }
        }
        return $themes;
    }
}
        
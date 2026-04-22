<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;
use think\template\exception\TemplateNotFoundException;
use app\common\model\SeoModel;
use app\common\model\WebNavModel;
use app\common\model\BottomBarNavModel;
use app\common\model\SideFloatingWindowModel;
use app\common\model\ConfigurationModel;
use app\common\model\IndexBannerModel;
use app\common\model\CloudServerProductModel;
use app\common\model\PhysicalServerProductModel;
use app\common\model\SslCertificateProductModel;
use app\common\model\SmsServiceProductModel;
use app\common\model\TrademarkRegisterProductModel;
use app\common\model\ServerHostingProductModel;
use app\common\model\CabinetRentalProductModel;
use app\common\model\IcpServiceProductModel;
use app\common\model\FriendlyLinkModel;
use app\common\model\HonorModel;
use app\common\model\PartnerModel;

class ViewController extends HomeBaseController
{
    public function errorPage()
    {
        $data['template_catalog'] = 'clientarea';
        $data['themes'] = 'default';
        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $data['addons'] = $addons['list'];
        $data['system_version'] = configuration('system_version');
        $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
        $mobile = use_mobile();
        if ($mobile){
            $data['public_themes'] = $data['themes'] = 'mobile/'.$data['themes'];
            $content = '';
        }else{
            $data['public_themes'] = $data['themes'] = 'pc/'.$data['themes'];
            $view_path = '../public/clientarea/template/'.$data['themes'].'/';
            $config['view_path'] = $view_path;
            $config['view_suffix'] = 'php';
            /*if($tplName=='index'){
                $config['view_suffix'] = 'html';
            }*/
            View::config($config);

            $content = View::fetch("/404",$data);
        }
        return $content;
    }

    /**
     * 时间 2023-05-04
     * @title 前台首页模板统一入口
     * @desc 前台首页模板统一入口
     * @url /console
     * @method GET
     * @author wyh
     * @version v1
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function index()
    {
        $web_switch = configuration('web_switch');
        if($web_switch){
            $param = $this->request->param();
            $data = [
                'title'=>'首页-智简魔方',
            ];

            $data['template_catalog'] = 'web';
            //$tplName = empty($param['view_html'])?'index':$param['view_html'];
            
            if(empty($param['html'])){ 
                $tplName = 'index';
            }else if(!empty($param['html3'])){
                $tplName = $param['html']."/".$param['html2']."/".$param['html3'];  
            }else if(!empty($param['html2'])){
                $tplName = $param['html']."/".$param['html2'];  
            }else{
                $tplName = $param['html'];  
            }

            if (isset($param['theme']) && !empty($param['theme'])){
                cookie('web_theme',$param['theme']);
                $data['themes'] = $param['theme'];
            } elseif (cookie('web_theme')){
                $data['themes'] = cookie('web_theme');
            } else{
                $data['themes'] = configuration('web_theme');
            }

            if(in_array($data['themes'], ['mf101','mfm201','mf501'])){
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
                        if(!in_array(parse_name($data['themes'], 1), $auth['app'])){
                            $data['themes'] = 'default';
                        }
                    }else{
                        $data['themes'] = 'default';
                    }
                }else{
                    $data['themes'] = 'default';
                }
            }

            if($tplName=='index'){
                $view_path = '../public/web/'.$data['themes'].'/';
                //header('location:/theme/index.html');die;
                //$view_path = '../public/theme/';
            }else{
                $view_path = '../public/web/'.$data['themes'].'/';
            }

            if(!file_exists($view_path.$tplName.'.html')){
                $theme_config=$this->themeConfig($view_path);
                if(!empty($theme_config['config-parent-theme'])){
                    $view_path = '../public/web/'.$theme_config['config-parent-theme'].'/';
                }
            }

            $PluginModel = new PluginModel();
            $addons = $PluginModel->plugins('addon');

            $data['addons'] = $addons['list'];
            $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
            $config['view_path'] = $view_path;
            /*if($tplName=='index'){
                $config['view_suffix'] = 'html';
            }*/
            $config['view_suffix'] = 'html';

            View::config($config);

            $data['url'] = request()->url(true);  

            //seo
            $data['title'] = lang('web_seo_default_title_'.$tplName);
            if(empty($data['title']) || $data['title']==('web_seo_default_title_'.$tplName)){
                $data['title'] = configuration('website_name');
            }else{
                $data['title'] = $data['title'].(!empty(configuration('website_name')) ? ('-'.configuration('website_name')) : '');
            }
            $data['keywords'] = lang('web_seo_default_keywords_'.$tplName);
            if(empty($data['keywords']) || $data['keywords']==('web_seo_default_keywords_'.$tplName)){
                $data['keywords'] = configuration('website_name');
            }
            $data['description'] = lang('web_seo_default_description_'.$tplName);
            if(empty($data['description']) || $data['description']==('web_seo_default_description_'.$tplName)){
                $data['description'] = configuration('website_name');
            }
            $data['pub_date'] = date('Y-m-d\TH:i:s', strtotime('2023-01-01 09:00:00'));
            $data['up_date'] = date('Y-m-d\TH:i:s', strtotime('2023-01-01 09:00:00'));

            $seo = $this->webSeoCustom(['tpl_name' => $tplName, 'url' => $data['url']]);
            if(!empty($seo)){
                if(isset($seo['title']) && !empty($seo['title'])){
                    $data['title'] = $seo['title'];
                }
                if(isset($seo['keywords']) && !empty($seo['keywords'])){
                    $data['keywords'] = $seo['keywords'];
                }
                if(isset($seo['description']) && !empty($seo['description'])){
                    $data['description'] = $seo['description'];
                }
                if(isset($seo['pub_date']) && !empty($seo['pub_date'])){
                    $data['pub_date'] = date('Y-m-d\TH:i:s', $seo['pub_date']);
                }
                if(isset($seo['up_date']) && !empty($seo['up_date'])){
                    $data['up_date'] = date('Y-m-d\TH:i:s', $seo['up_date']);
                }
            }

            try {
                $result_hook = hook('web_seo_custom', ['tpl_name' => $tplName, 'url' => $data['url']]);
                $result_hook = array_values(array_filter($result_hook ?? []));
            }catch (\Exception $e){
                $result_hook = [];
            }
            foreach ($result_hook as $key => $value) {
                if(isset($value['title']) && !empty($value['title'])){
                    $data['title'] = $value['title'];
                }
                if(isset($value['keywords']) && !empty($value['keywords'])){
                    $data['keywords'] = $value['keywords'];
                }
                if(isset($value['description']) && !empty($value['description'])){
                    $data['description'] = $value['description'];
                }
                if(isset($value['pub_date']) && !empty($value['pub_date'])){
                    $data['pub_date'] = date('Y-m-d\TH:i:s', $value['pub_date']);
                }
                if(isset($value['up_date']) && !empty($value['up_date'])){
                    $data['up_date'] = date('Y-m-d\TH:i:s', $value['up_date']);
                }
            }

            $customData = $this->webDataCustom(['tpl_name' => $tplName, 'url' => $data['url']]);
            if(!empty($customData)){
                $data['data'] = $customData;
            }

            $result_hook = hook('web_data_custom', ['tpl_name' => $tplName, 'url' => $data['url']]);
            $result_hook = array_values(array_filter($result_hook ?? []));
            foreach ($result_hook as $key => $value) {
                if(isset($value['data'])){
                    $data['data'] = isset($data['data']) ? array_merge($data['data'], $value['data']) : $value['data'];
                }
            }

            if (APP_DEBUG){
                $content = View::fetch("/".$tplName,$data);
            }else{
                try {
                    $content = View::fetch("/".$tplName,$data);
                }catch (\Exception $e){
                    return $this->errorPage();
                }
            }

            // css,js追加系统版本
            $version = '?v='.configuration('system_version'); 

            $pattern = '/<link\s+[^>]*?href="([^"]+\.css)"[^>]*>/i';
              
            $content = preg_replace_callback($pattern, function($matches) use ($version) {  
                return str_replace($matches[1], $matches[1] . $version, $matches[0]);  
            }, $content);  

            $pattern = '/<script\s+[^>]*?src="([^"]+\.js)"[^>]*>/i';
              
            $content = preg_replace_callback($pattern, function($matches) use ($version) {  
                return str_replace($matches[1], $matches[1] . $version, $matches[0]);  
            }, $content);  

            return $content;
        }
        else{
            return $this->viewHome();
        }
        
    }

    /*public function plugin()
    {
        $param = $this->request->param();
        $plugin_id = $param['plugin_id'];
        $tplName = empty($param['view_html'])?'index':$param['view_html'];
        $addon = (new PluginModel())->plugins('addon')['list'];
        $addon = array_column($addon,'name','id');
        $name=parse_name($addon[$plugin_id]??'');
        if(empty($name)){
            throw new TemplateNotFoundException(lang('not_found'), $name);
            #exit('not found template1');
        }
        $tpl = '../public/plugins/addon/'.$name.'/template/web/';

        $data['template_catalog'] = 'web';

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('web_theme',$param['theme']);
            $data['themes'] = $param['theme'];
        } elseif (cookie('web_theme')){
            $data['themes'] = cookie('web_theme');
        } else{
            $data['themes'] = configuration('web_theme');
        }

        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');

        $data['addons'] = $addons['list'];

        if(file_exists($tpl.$tplName.".html")){
            $content=$this->view('header',$data);
            $content.=$this->pluginView($tplName,$data,$name);
            $content.=$this->view('footer',$data);
            return $content;
        }else{
            throw new TemplateNotFoundException(lang('not_found'), $tpl);
            #exit('not found template');
        }

    }

    private function view($tplName, $data){
        View::config(['view_path' => '../public/web/default/', 'view_suffix' => 'html']);
        return View::fetch('/'.$tplName,$data);
    }

    private function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/web/', 'view_suffix' => 'html']);
        return View::fetch('/'.$tplName,$data);
    }*/
    //模板继承文件读取
    private function themeConfig($file){
        $theme=$file.'/theme.config';$themes=[];
        if(file_exists($theme)){
            $theme=file_get_contents($theme);

            $theme=explode("\r\n",$theme);
            $theme=array_filter($theme);

            foreach($theme as $v){
                $theme_config=explode(":",$v);
                $themes[trim($theme_config[0])]=trim(trim(trim($theme_config[1],"'"),'"'));
            }
        }
        return $themes;
    }

    /**
     * 时间 2024-04-09
     * @title 网站seo自定义
     * @desc 网站seo自定义
     * @author theworld
     * @version v1
     * @param string param.tpl_name - 模板名称 
     * @return string title - 标题
     * @return string description - 描述
     * @return string keywords - 关键字
     * @return int pub_date - 发布时间
     * @return int up_date - 更新时间
     */
    private function webSeoCustom($param)
    {
        $SeoModel = new SeoModel();
        $seo = $SeoModel->where('page_address', $param['tpl_name'].'.html')->find();
        if(!empty($seo)){
            return ['title' => $seo['title'].(!empty(configuration('website_name')) ? ('-'.configuration('website_name')) : ''), 'description' => $seo['description'], 'keywords' => $seo['keywords'], 'pub_date' => $seo['create_time'], 'up_date' => !empty($seo['update_time']) ? $seo['update_time'] : $seo['create_time']];
        }else{
            return [];
        }
    }

    /**
     * 时间 2024-04-09
     * @title 网站数据自定义
     * @desc 网站数据自定义
     * @author theworld
     * @version v1
     * @param string param.tpl_name - 模板名称 
     * @return array data - 自定义数据
     */
    private function webDataCustom($param)
    {
        $data = [];

        $WebNavModel = new WebNavModel();
        $res = $WebNavModel->webHeaderNav();
        $data['header_nav'] = $res['list'];
        $defaultPage = $res['default_page'];

        $BottomBarNavModel = new BottomBarNavModel();
        $res = $BottomBarNavModel->webFooterNav();
        $data['footer_nav'] = $res['list'];

        $SideFloatingWindowModel = new SideFloatingWindowModel();
        $res = $SideFloatingWindowModel->sideFloatingWindowList();
        $data['side_floating_window'] = $res['list'];

        $ConfigurationModel = new ConfigurationModel();
        $data['config'] = $ConfigurationModel->webList();

        // 获取友情链接
        $FriendlyLinkModel = new FriendlyLinkModel();
        $friendlyLink = $FriendlyLinkModel->friendlyLinkList();
        $data['friendly_link'] = $friendlyLink['list'];

        // 获取荣誉资质
        $HonorModel = new HonorModel();
        $honor = $HonorModel->honorList();
        $data['honor'] = $honor['list'];

        // 获取合作伙伴
        $PartnerModel = new PartnerModel();
        $partner = $PartnerModel->partnerList();
        $data['partner'] = $partner['list'];

        foreach ($defaultPage as $key => $value) {
            if($param['tpl_name'].'.html'==ltrim($value['file_address'], '/')){
                Switch($value['id']){
                    case 1:
                        $IndexBannerModel = new IndexBannerModel();
                        $res = $IndexBannerModel->webData();
                        $data['banner'] = $res['banner'];
                        break;
                    case 3:
                        $CloudServerProductModel = new CloudServerProductModel();
                        $res = $CloudServerProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['banner'] = $res['banner'];
                        $data['more_offers'] = $res['more_offers'];
                        $data['discount'] = $res['discount'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 4:
                        $PhysicalServerProductModel = new PhysicalServerProductModel();
                        $res = $PhysicalServerProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['banner'] = $res['banner'];
                        $data['more_offers'] = $res['more_offers'];
                        $data['discount'] = $res['discount'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 5:
                        $SslCertificateProductModel = new SslCertificateProductModel();
                        $res = $SslCertificateProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 6:
                        $SmsServiceProductModel = new SmsServiceProductModel();
                        $res = $SmsServiceProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 7:
                        $TrademarkRegisterProductModel = new TrademarkRegisterProductModel();
                        $res = $TrademarkRegisterProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['service'] = $res['service'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 8:
                        $ServerHostingProductModel = new ServerHostingProductModel();
                        $res = $ServerHostingProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 9:
                        $CabinetRentalProductModel = new CabinetRentalProductModel();
                        $res = $CabinetRentalProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                    case 10:
                        $IcpServiceProductModel = new IcpServiceProductModel();
                        $res = $IcpServiceProductModel->webData();
                        $data['product'] = $res['list'];
                        $data['icp_product_id'] = $res['icp_product_id'];
                        $data['currency_prefix'] = configuration('currency_prefix');
                        $data['currency_suffix'] = configuration('currency_suffix');
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * 时间 2025-05-20
     * @title 前台会员中心首页模板统一入口
     * @desc 前台会员中心首页模板统一入口
     * @url /home
     * @method GET
     * @author hh
     * @version v1
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function viewHome()
    {   
        $param = $this->request->param();
        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog'] = 'clientarea';
        $data['template_catalog_home'] = 'home';
        $tplName = 'home';
        // $tplName = empty($param['view_html'])?'home':$param['view_html'];

        // if(!in_array($tplName, ["goodsList","goods","goods_iframe","shoppingCar"])){
        $check = check_home_enforce_safe_method_redirect();
        if($check['redirect']){
            header('Location: '.$check['url']);die;
        }
        // }

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            // cookie('cart_theme_mobile',$param['theme_mobile']??"default");
            $data['themes'] = $clientareaPcTheme = $param['theme'];
            $data['themes_mobile'] = $param['theme_mobile']??"default";
        } elseif (cookie('clientarea_theme')){
            $data['themes'] = $clientareaPcTheme = cookie('clientarea_theme');
            $data['themes_mobile'] = cookie('clientarea_theme_mobile');
        } else{
            $data['themes'] = $clientareaPcTheme = configuration('clientarea_theme')??"default";
            $data['themes_mobile'] = configuration('clientarea_theme_mobile');
        }
        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
        $data['addons'] = $addons['list'];
        $data['system_version'] = configuration('system_version');

        $mobile = use_mobile();
        $type = $mobile?'mobile':'pc';
        // 会员中心手机主题或者pc主题
        $data['themes'] = $mobile?$data['themes_mobile']:$data['themes'];
        $data['clientarea_theme_color'] = configuration('clientarea_theme_color')??'default';
        $clientareaData = $data;

        // 购物车手机主题或者pc主题
        $homeTheme = $mobile?configuration('home_theme_mobile'):configuration("home_theme");

        if(in_array($homeTheme, ['mh101','mh201','mh301'])){
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
                    if(!in_array(parse_name($homeTheme, 1), $auth['app'])){
                        $homeTheme = 'default';
                        $data['themes'] = 'default';
                    }
                }else{
                    $homeTheme = 'default';
                    $data['themes'] = 'default';
                }
            }else{
                $homeTheme = 'default';
                $data['themes'] = 'default';
            }
        }

        $data['themes'] = $type."/".$data['themes'];

        $homeTheme = $type . "/" . $homeTheme;
        $view_path = "../public/home/template/".$homeTheme.'/';
        // 模板文件不存在，使用默认首页主题模板文件
        if(!file_exists(IDCSMART_ROOT."public/home/template/{$homeTheme}/".$tplName.".php")){
            $type = 'pc';
            $homeTheme = 'pc/default';
            $data['themes'] = $type.'/'.$data['themes'];
            $view_path = "../public/home/template/{$type}/default/";
        }

        $clientareaData['template_catalog'] = 'clientarea';
        $clientareaData['themes_home'] = $homeTheme;

        // 判断模板文件是否存在，不存在则使用默认主题
        if(!file_exists(IDCSMART_ROOT."public/clientarea/template/".$data['themes'].'/header.php')){
            $data['themes'] = $type."/default";
        }
        // 引用会员中心header，footer
        $clientareaThemeHeader = "../public/clientarea/template/".$data['themes'].'/header.php';
        $clientareaThemeFooter = "../public/clientarea/template/".$data['themes'].'/footer.php';

        $clientareaData['themes'] = $data['themes'];
        $clientareaData['public_themes'] = $data['themes'];
        $header = View::fetch($clientareaThemeHeader,$clientareaData);
        $footer = View::fetch($clientareaThemeFooter,$clientareaData);
        $config['view_path'] = $view_path;
        View::config($config);

        if (APP_DEBUG){
            $content = $header.View::fetch("/".$tplName,$data).$footer;
        }else{
            try {
                $content = $header.View::fetch("/".$tplName,$data).$footer;
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

        $pattern = '/<script[ ]+src="([^"]+\.js)"[^>]*>/i';

        $content = preg_replace_callback($pattern, function ($matches) use($version) {
            return str_replace($matches[1], $matches[1].$version, $matches[0]);
        }, $content);

        return $content;
    }
}

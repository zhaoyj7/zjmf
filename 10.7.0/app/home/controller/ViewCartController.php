<?php
namespace app\home\controller;

use think\facade\View;
use app\admin\model\PluginModel;

class ViewCartController extends HomeBaseController
{
    /**
     * 时间 2024-02-22
     * @title 前台会员中心购物车模板统一入口
     * @desc 前台会员中心购物车模板统一入口
     * @url /cart
     * @method GET
     * @author wyh
     * @version v1
     * @param string theme - desc:会员中心主题模板 validate:optional
     * @param string view_html - desc:模板名称 validate:optional
     */
    public function index()
    {   
        $param = $this->request->param();
        $data = [
            'title'=>'首页-智简魔方',
        ];

        $data['template_catalog_cart'] = 'cart';
        $tplName = empty($param['view_html'])?'home':$param['view_html'];

        if(!in_array($tplName, ["goodsList","goods","goods_iframe","shoppingCar"])){
            $check = check_home_enforce_safe_method_redirect();
            if($check['redirect']){
                header('Location: '.$check['url']);die;
            }
        }

        if (isset($param['theme']) && !empty($param['theme'])){
            cookie('clientarea_theme',$param['theme']);
            cookie('cart_theme_mobile',$param['theme_mobile']??"default");
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
                        $data['themes'] = 'default';
                    }
                }else{
                    $cartTheme = 'default';
                    $data['themes'] = 'default';
                }
            }else{
                $cartTheme = 'default';
                $data['themes'] = 'default';
            }
        }

        $data['themes'] = $type."/".$data['themes'];

        $cartTheme = $type . "/" . $cartTheme;
        $view_path = "../public/cart/template/".$cartTheme.'/';
        // 模板文件不存在，使用默认购物车主题模板文件
        if(!file_exists(IDCSMART_ROOT."public/cart/template/{$cartTheme}/".$tplName.".php")){
            $view_path = "../public/cart/template/{$type}/default/";
            //$data['themes'] = $type."/default";
        }

        $clientareaData['template_catalog'] = 'clientarea';
        $clientareaData['themes_cart'] = $cartTheme; // 购物车主题

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

        $pattern = '/<script\s+[^>]*? src = "([^"]+\.js) "[^>]*>/i';

        $content = preg_replace_callback($pattern, function ($matches) use($version) {
            return str_replace($matches[1], $matches[1].$version, $matches[0]);
        }, $content);

        return $content;
    }

}
        
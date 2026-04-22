<?php

namespace app\common\logic;

use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\common\model\ProductModel;
use app\common\model\UpstreamProductModel;

/**
 * @title 公共类
 * @desc 公共类
 * @use app\common\logic\CommonLogic
 */
class CommonLogic
{
    public static function checkModuleMobile()
    {
        $timeout = 3600;
        $param = request()->param();
        if(!empty($param['view_html'])){
            if ($param['view_html']=='product'){ // 产品列表
                $m = $param['m']??0;
                $key = "mobile_template_product_".$m;
                if (!empty($flag = idcsmart_cache($key))){
                    if ($flag=='false'){
                        return false;
                    }elseif ($flag=='true'){
                        return true;
                    }
                }
                if (!empty($m)){
                    $MenuModel = new MenuModel();
                    $menu = $MenuModel->where('menu_type','module')
                        ->where('id',$m)
                        ->find();
                    $module = $menu['module']??'';
                    if (!empty($module)){
                        $homeHostThemeMobile = configuration('home_host_theme_mobile');
                        $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                        $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                        if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_list.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }
                idcsmart_cache($key,'true',$timeout);
            }elseif ($param['view_html']=='goods'){ // 选配页面
                $productId = $param['id']??0;
                $key = "mobile_template_goods_".$productId;
                if (!empty($flag = idcsmart_cache($key))){
                    if ($flag=='false'){
                        return false;
                    }elseif ($flag=='true'){
                        return true;
                    }
                }
                $upstreamProduct = UpstreamProductModel::where('product_id',$productId)->find();
                if (!empty($upstreamProduct)){ // 代理商品
                    if ($upstreamProduct['mode']=='sync'){ // 本地代理
                        $product = ProductModel::where('id',$productId)->find();
                        if (!empty($product)){
                            $module = $product->getModule();
                            $mobileTheme = configuration('cart_theme_mobile');
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                                if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/default/goods.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }else{ // 接口代理，走reserver
                        $module = $upstreamProduct['res_module'];
                        if (!empty($module)){
                            $mobileTheme = configuration('cart_theme_mobile');
                            if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                                if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/cart/mobile/default/goods.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }
                }else{ // 本地商品
                    $product = ProductModel::where('id',$productId)->find();
                    if (!empty($product)){
                        $module = $product->getModule();
                        if (!empty($module)){
                            $mobileTheme = configuration('cart_theme_mobile');
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                                if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/default/goods.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }
                }
                idcsmart_cache($key,'true',$timeout);
            }elseif ($param['view_html']=='productdetail'){ // 产品内页
                $hostId = $param['id']??0;
                $host = HostModel::where('id',$hostId)->find();
                $productId = $host['product_id']??0;
                $key = "mobile_template_productdetail_".$productId;
                if (!empty($flag = idcsmart_cache($key))){
                    if ($flag=='false'){
                        return false;
                    }elseif ($flag=='true'){
                        return true;
                    }
                }
                $upstreamProduct = UpstreamProductModel::where('product_id',$productId)->find();
                if (!empty($upstreamProduct)){ // 代理商品
                    if ($upstreamProduct['mode']=='sync'){ // 本地代理
                        $product = ProductModel::where('id',$productId)->find();
                        if (!empty($product)){
                            $module = $product->getModule();
                            
                            $homeHostThemeMobile = configuration('home_host_theme_mobile');
                            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                            $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                                if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }else{ // 接口代理，走reserver
                        $module = $upstreamProduct['res_module'];
                        if($module == 'mf_finance'){
                            $module = 'mf_cloud';
                        }else if($module == 'mf_finance_dcim'){
                            $module = 'mf_dcim';
                        }else if($module == 'mf_finance_common'){
                            $module = 'idcsmart_common';
                        }
                        if (!empty($module)){
                            $homeHostThemeMobile = configuration('home_host_theme_mobile');
                            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                            $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';

                            if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                                if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }
                }else{
                    if (!empty($host)){
                        $module = $host->getModule();
                        if (!empty($module)){
                            $homeHostThemeMobile = configuration('home_host_theme_mobile');
                            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                            $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                                if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                    idcsmart_cache($key,'false',$timeout);
                                    return false;
                                }
                            }
                        }
                    }
                }
                idcsmart_cache($key,'true',$timeout);
            }
        }
        elseif (app('http')->getName()=='home' && request()->method()=='GET' && request()->controller()=='Host' && request()->action()=='menuHostList'){
            // 产品列表页面
            $m = $param['id']??0;
            $key = "mobile_template_product_".$m;
            if (!empty($flag = idcsmart_cache($key))){
                if ($flag=='false'){
                    return false;
                }elseif ($flag=='true'){
                    return true;
                }
            }
            if (!empty($m)){
                $MenuModel = new MenuModel();
                $menu = $MenuModel->where('menu_type','module')
                    ->where('id',$m)
                    ->find();
                $module = $menu['module']??'';
            }
            if (!empty($module)){
                $homeHostThemeMobile = configuration('home_host_theme_mobile');
                $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                    if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_list.html")){
                        idcsmart_cache($key,'false',$timeout);
                        return false;
                    }
                }
            }
            idcsmart_cache($key,'true',$timeout);
        }
        elseif (app('http')->getName()=='home' && request()->method()=='GET' && request()->controller()=='Product' && request()->action()=='moduleClientConfigOption'){
            // 选配页面
            $productId = $param['id']??0;
            $key = "mobile_template_goods_".$productId;
            if (!empty($flag = idcsmart_cache($key))){
                if ($flag=='false'){
                    return false;
                }elseif ($flag=='true'){
                    return true;
                }
            }
            $upstreamProduct = UpstreamProductModel::where('product_id',$productId)->find();
            if (!empty($upstreamProduct)){ // 代理商品
                if ($upstreamProduct['mode']=='sync'){ // 本地代理
                    $product = ProductModel::where('id',$productId)->find();
                    if (!empty($product)){
                        $module = $product->getModule();
                        $mobileTheme = configuration('cart_theme_mobile');
                        if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/default/goods.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }else{ // 接口代理，走reserver
                    $module = $upstreamProduct['res_module'];
                    if (!empty($module)){
                        $mobileTheme = configuration('cart_theme_mobile');
                        if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                            if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/cart/mobile/default/goods.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }
            }else{ // 本地商品
                $product = ProductModel::where('id',$productId)->find();
                if (!empty($product)){
                    $module = $product->getModule();
                    if (!empty($module)){
                        $mobileTheme = configuration('cart_theme_mobile');
                        if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/{$mobileTheme}/goods.html")){
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/cart/mobile/default/goods.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }
            }
            idcsmart_cache($key,'true',$timeout);
        }
        elseif (app('http')->getName()=='home' && request()->method()=='GET' && request()->controller()=='Host' && request()->action()=='clientArea'){
            // 产品内页
            $hostId = $param['id']??0;
            $host = HostModel::where('id',$hostId)->find();
            $productId = $host['product_id']??0;
            $key = "mobile_template_productdetail_".$productId;
            if (!empty($flag = idcsmart_cache($key))){
                if ($flag=='false'){
                    return false;
                }elseif ($flag=='true'){
                    return true;
                }
            }
            $upstreamProduct = UpstreamProductModel::where('product_id',$productId)->find();
            if (!empty($upstreamProduct)){ // 代理商品
                if ($upstreamProduct['mode']=='sync'){ // 本地代理
                    $product = ProductModel::where('id',$productId)->find();
                    if (!empty($product)){
                        $module = $product->getModule();
                        
                        $homeHostThemeMobile = configuration('home_host_theme_mobile');
                        $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                        $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                        if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }else{ // 接口代理，走reserver
                    $module = $upstreamProduct['res_module'];
                    if($module == 'mf_finance'){
                        $module = 'mf_cloud';
                    }else if($module == 'mf_finance_dcim'){
                        $module = 'mf_dcim';
                    }else if($module == 'mf_finance_common'){
                        $module = 'idcsmart_common';
                    }
                    if (!empty($module)){
                        $homeHostThemeMobile = configuration('home_host_theme_mobile');
                        $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                        $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                        if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                            if (!file_exists(WEB_ROOT."plugins/reserver/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }
            }else{
                if (!empty($host)){
                    $module = $host->getModule();
                    if (!empty($module)){
                        $homeHostThemeMobile = configuration('home_host_theme_mobile');
                        $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
                        $mobileTheme = $homeHostThemeMobile[ $module ] ?? 'default';
                        if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                            if (!file_exists(WEB_ROOT."plugins/server/{$module}/template/clientarea/mobile/default/product_detail.html")){
                                idcsmart_cache($key,'false',$timeout);
                                return false;
                            }
                        }
                    }
                }
            }
            idcsmart_cache($key,'true',$timeout);
        }
        return true;
    }
}
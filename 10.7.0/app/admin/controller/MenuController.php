<?php
namespace app\admin\controller;

use app\common\model\MenuModel;
use app\admin\validate\MenuValidate;

/**
 * @title 导航管理
 * @desc 导航管理
 * @use app\admin\controller\MenuController
 */
class MenuController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new MenuValidate();
    }

    /**
     * 时间 2022-08-05
     * @title 获取后台导航
     * @desc 获取后台导航
     * @url /admin/v1/menu/admin
     * @method GET
     * @author theworld
     * @version v1
     * @return array menu - desc:菜单
     * @return int menu[].id - desc:菜单ID
     * @return string menu[].type - desc:菜单类型 system系统 plugin插件 custom自定义
     * @return string menu[].name - desc:名称
     * @return object menu[].language - desc:语言
     * @return string menu[].url - desc:网址
     * @return string menu[].icon - desc:图标
     * @return int menu[].nav_id - desc:导航ID
     * @return int menu[].parent_id - desc:父ID
     * @return array menu[].child - desc:子菜单
     * @return int menu[].child[].id - desc:菜单ID
     * @return string menu[].child[].type - desc:菜单类型 system系统 plugin插件 custom自定义
     * @return string menu[].child[].name - desc:名称
     * @return object menu[].child[].language - desc:语言
     * @return string menu[].child[].icon - desc:图标
     * @return string menu[].child[].url - desc:网址
     * @return int menu[].child[].nav_id - desc:导航ID
     * @return int menu[].child[].parent_id - desc:父ID
     * @return array language - desc:语言
     * @return string language[].display_name - desc:语言名称
     * @return string language[].display_flag - desc:国家代码
     * @return string language[].display_img - desc:图片
     * @return string language[].display_lang - desc:语言标识
     * @return array system_nav - desc:系统默认导航
     * @return string system_nav[].id - desc:导航ID
     * @return string system_nav[].name - desc:名称
     * @return string system_nav[].url - desc:网址
     * @return array plugin_nav - desc:插件默认导航
     * @return string plugin_nav[].title - desc:插件标题
     * @return array plugin_nav[].nav - desc:插件导航
     * @return int plugin_nav[].nav[].id - desc:导航ID
     * @return string plugin_nav[].nav[].name - desc:名称
     * @return string plugin_nav[].nav[].url - desc:网址
     */
    public function getAdminMenu()
    {
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 获取后台导航
        $data = $MenuModel->getAdminMenu();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 获取前台导航
     * @desc 获取前台导航
     * @url /admin/v1/menu/home
     * @method GET
     * @author theworld
     * @version v1
     * @return array menu - desc:菜单
     * @return int menu[].id - desc:菜单ID
     * @return string menu[].type - desc:菜单类型 system系统 plugin插件 custom自定义 module模块 embedded内嵌
     * @return string menu[].name - desc:名称
     * @return object menu[].language - desc:语言
     * @return string menu[].url - desc:网址
     * @return int menu[].second_reminder - desc:二次提醒 0否 1是
     * @return string menu[].icon - desc:图标
     * @return int menu[].nav_id - desc:导航ID
     * @return int menu[].parent_id - desc:父ID
     * @return array menu[].module - desc:模块类型
     * @return array menu[].res_module - desc:res模块类型
     * @return array menu[].product_id - desc:包含商品
     * @return int menu[].is_cross_module - desc:是否为跨模块列表
     * @return array menu[].select_field - desc:选择字段 area=区域 product_name=商品名称 billing_cycle=计费方式 is_auto_renew=自动续费 base_info=基础信息 ip=IP os=OS active_time=开通时间 due_time=到期时间 status=状态 notes=备注
     * @return array menu[].child - desc:子菜单
     * @return int menu[].child[].id - desc:菜单ID
     * @return string menu[].child[].type - desc:菜单类型 system系统 plugin插件 custom自定义 module模块 embedded内嵌
     * @return string menu[].child[].name - desc:名称
     * @return object menu[].child[].language - desc:语言
     * @return string menu[].child[].url - desc:网址
     * @return int menu[].child[].second_reminder - desc:二次提醒 0否 1是
     * @return string menu[].child[].icon - desc:图标
     * @return int menu[].child[].nav_id - desc:导航ID
     * @return int menu[].child[].parent_id - desc:父ID
     * @return array menu[].child[].module - desc:模块类型
     * @return array menu[].child[].res_module - desc:res模块类型
     * @return array menu[].child[].product_id - desc:包含商品
     * @return int menu[].child[].is_cross_module - desc:是否为跨模块列表
     * @return array menu[].child[].select_field - desc:选择字段 area=区域 product_name=商品名称 billing_cycle=计费方式 is_auto_renew=自动续费 base_info=基础信息 ip=IP os=OS active_time=开通时间 due_time=到期时间 status=状态 notes=备注
     * @return array language - desc:语言
     * @return string language[].display_name - desc:语言名称
     * @return string language[].display_flag - desc:国家代码
     * @return string language[].display_img - desc:图片
     * @return string language[].display_lang - desc:语言标识
     * @return array system_nav - desc:系统默认导航
     * @return string system_nav[].id - desc:导航ID
     * @return string system_nav[].name - desc:名称
     * @return string system_nav[].url - desc:网址
     * @return array plugin_nav - desc:插件默认导航
     * @return string plugin_nav[].title - desc:插件标题
     * @return array plugin_nav[].nav - desc:插件导航
     * @return int plugin_nav[].nav[].id - desc:导航ID
     * @return string plugin_nav[].nav[].name - desc:名称
     * @return string plugin_nav[].nav[].url - desc:网址
     * @return array module - desc:模块
     * @return string module[].name - desc:模块名称
     * @return string module[].display_name - desc:模块显示名称
     * @return array res_module - desc:上游模块
     * @return string res_module[].name - desc:上游模块名称
     * @return string res_module[].display_name - desc:上游模块显示名称
     */
    public function getHomeMenu()
    {
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 获取前台导航
        $data = $MenuModel->getHomeMenu();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 保存后台导航
     * @desc 保存后台导航
     * @url /admin/v1/menu/admin
     * @method PUT
     * @author theworld
     * @version v1
     * @param array menu - desc:菜单 validate:required
     * @param string menu[].type - desc:菜单类型 system系统 plugin插件 custom自定义 validate:required
     * @param string menu[].name - desc:名称 validate:required
     * @param object menu[].language - desc:语言 validate:required
     * @param string menu[].url - desc:网址 菜单类型为自定义时需要传递 validate:optional
     * @param string menu[].icon - desc:图标 validate:optional
     * @param int menu[].nav_id - desc:导航ID 菜单类型不为自定义时需要传递 validate:optional
     * @param array menu[].child - desc:子菜单 validate:required
     * @param string menu[].child[].type - desc:菜单类型 system系统 plugin插件 custom自定义 validate:required
     * @param string menu[].child[].name - desc:名称 validate:required
     * @param object menu[].child[].language - desc:语言 validate:required
     * @param string menu[].child[].url - desc:网址 菜单类型为自定义时需要传递 validate:optional
     * @param string menu[].child[].icon - desc:图标 validate:optional
     * @param int menu[].child[].nav_id - desc:导航ID 菜单类型不为自定义时需要传递 validate:optional
     */
    public function saveAdminMenu()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 保存后台导航
        $result = $MenuModel->saveAdminMenu($param);   
        
        return json($result);
    }

    /**
     * 时间 2022-08-05
     * @title 保存前台导航
     * @desc 保存前台导航
     * @url /admin/v1/menu/home
     * @method PUT
     * @author theworld
     * @version v1
     * @param array menu - desc:菜单 validate:required
     * @param string menu[].type - desc:菜单类型 system系统 plugin插件 custom自定义 module产品列表 embedded内嵌 validate:required
     * @param string menu[].name - desc:名称 validate:required
     * @param object menu[].language - desc:语言 validate:required
     * @param string menu[].url - desc:网址 菜单类型为自定义时需要传递 validate:optional
     * @param int menu[].second_reminder - desc:二次提醒 0否 1是 菜单类型为自定义时需要传递 validate:optional
     * @param string menu[].icon - desc:图标 validate:optional
     * @param int menu[].nav_id - desc:导航ID 菜单类型为系统或插件时需要传递 validate:optional
     * @param array menu[].module - desc:模块类型 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].res_module - desc:res模块类型 菜单类型为产品列表时可以传递 云 whmcs_cloud mf_cloud mf_finance DCIM whmcs_dcim mf_dcim mf_finance_dcim 可以多选 validate:optional
     * @param array menu[].product_id - desc:商品ID 菜单类型为产品列表时需要传递 validate:optional
     * @param int menu[].is_cross_module - desc:是否为跨模块列表 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].select_field - desc:选择字段 area=区域 product_name=商品名称 billing_cycle=计费方式 is_auto_renew=自动续费 base_info=基础信息 ip=IP os=OS active_time=开通时间 due_time=到期时间 status=状态 notes=备注 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].child - desc:子菜单 validate:required
     * @param string menu[].child[].type - desc:菜单类型 system系统 plugin插件 custom自定义 module产品列表 embedded内嵌 validate:required
     * @param string menu[].child[].name - desc:名称 validate:required
     * @param object menu[].child[].language - desc:语言 validate:required
     * @param string menu[].child[].url - desc:网址 菜单类型为自定义时需要传递 validate:optional
     * @param int menu[].child[].second_reminder - desc:二次提醒 0否 1是 菜单类型为自定义时需要传递 validate:optional
     * @param string menu[].child[].icon - desc:图标 validate:optional
     * @param int menu[].child[].nav_id - desc:导航ID 菜单类型为系统或插件时需要传递 validate:optional
     * @param array menu[].child[].module - desc:模块类型 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].child[].res_module - desc:res模块类型 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].child[].product_id - desc:商品ID 菜单类型为产品列表时需要传递 validate:optional
     * @param int menu[].child[].is_cross_module - desc:是否为跨模块列表 菜单类型为产品列表时可以传递 validate:optional
     * @param array menu[].child[].select_field - desc:选择字段 area=区域 product_name=商品名称 billing_cycle=计费方式 is_auto_renew=自动续费 base_info=基础信息 ip=IP os=OS active_time=开通时间 due_time=到期时间 status=状态 notes=备注 菜单类型为产品列表时可以传递 validate:optional
     */
    public function saveHomeMenu()
    {
        // 接收参数
        $param = $this->request->param();
        $param['menu2'] = $param['menu'] ?? [];
        // 参数验证
        if (!$this->validate->scene('save_home')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        unset($param['menu2']);
        
        // 实例化模型类
        $MenuModel = new MenuModel();
        
        // 保存前台导航
        $result = $MenuModel->saveHomeMenu($param);   
        
        return json($result);
    }

    /**
     * 时间 2025-08-21
     * @title 自定义前台导航图标
     * @desc 自定义前台导航图标
     * @url /admin/v1/menu/home/icon
     * @method PUT
     * @author wyh
     * @version v1
     * @param string icon - desc:图标 validate:required
     * @return string show_name - desc:图标显示名称
     * @return string icon_uri - desc:图标资源地址
     */
    public function customHomeMenuIcon()
    {
        $param = $this->request->param();

        // 实例化模型类
        $MenuModel = new MenuModel();

        // 保存前台导航
        $result = $MenuModel->customHomeMenuIcon($param);

        return json($result);
    }

}
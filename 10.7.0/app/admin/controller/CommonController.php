<?php

namespace app\admin\controller;

use app\admin\model\NoticeModel;
use app\admin\model\PluginModel;
use app\common\model\ClientCreditModel;
use app\common\model\CountryModel;
use app\admin\model\AuthModel;
use app\common\logic\UploadLogic;
use app\common\model\ClientModel;
use app\common\model\HostAdditionModel;
use app\common\model\HostIpModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\MenuModel;
use app\home\model\ClientareaAuthModel;
use app\common\model\OrderModel;
use app\admin\model\AdminViewModel;
use app\admin\validate\AdminViewValidate;
use app\common\logic\TemplateLogic;
use server\mf_cloud\model\DataCenterModel;

/**
 * @title 公共接口
 * @desc 公共接口
 * @use app\admin\controller\CommonController
 */
class CommonController extends AdminBaseController
{
//    public function upgradeTest()
//    {
//        $customHostNameAuth = [
//            [
//                'title' => 'auth_aodun_firewall_firewall_attack_notice_log',
//                'url' => '',
//                'description' => '攻击提醒',
//                'parent' => 'auth_aodun_firewall',
//                'child' => [
//                    [
//                        'title' => 'auth_aodun_firewall_firewall_attack_notice_log_view',
//                        'url' => 'firewall_attack_notice_log',
//                        'auth_rule' => [
//                            'addon\aodun_firewall\controller\AodunFirewallAttackNoticeLogController::notice',
//                            'addon\aodun_firewall\controller\AodunFirewallAttackNoticeLogController::noticePost',
//                            'addon\aodun_firewall\controller\AodunFirewallAttackNoticeLogController::list',
//                        ],
//                        'description' => '攻击提醒页面',
//                    ]
//                ]
//            ],
//            [
//                'title' => 'auth_aodun_firewall_firewall_set_meal',
//                'url' => '',
//                'description' => '防御规则',
//                'parent' => 'auth_aodun_firewall',
//                'child' => [
//                    [
//                        'title' => 'auth_aodun_firewall_firewall_set_meal_view',
//                        'url' => 'firewall_set_meal',
//                        'auth_rule' => [
//                            'addon\aodun_firewall\controller\AodunFirewallSetMealController::sync',
//                            'addon\aodun_firewall\controller\AodunFirewallSetMealController::list',
//                            'addon\aodun_firewall\controller\AodunFirewallSetMealController::update',
//                            'addon\aodun_firewall\controller\AodunFirewallSetMealController::enabled',
//                        ],
//                        'description' => '防御规则页面',
//                    ]
//                ]
//            ],
//            [
//                'title' => 'auth_aodun_firewall_firewall_ip_object',
//                'url' => '',
//                'description' => 'IP对象列表',
//                'parent' => 'auth_aodun_firewall',
//                'child' => [
//                    [
//                        'title' => 'auth_aodun_firewall_firewall_ip_object_view',
//                        'url' => 'firewall_ip_object',
//                        'auth_rule' => [
//                            'addon\aodun_firewall\controller\AodunFirewallIpObjectController::webhookToken',
//                            'addon\aodun_firewall\controller\AodunFirewallIpObjectController::create',
//                            'addon\aodun_firewall\controller\AodunFirewallIpObjectController::list',
//                            'addon\aodun_firewall\controller\AodunFirewallIpObjectController::status',
//                            'addon\aodun_firewall\controller\AodunFirewallIpObjectController::delete',
//                        ],
//                        'description' => 'IP对象列表',
//                    ]
//                ]
//            ],
//        ];
//
//        $AuthModel = new \app\admin\model\AuthModel();
//        foreach ($customHostNameAuth as $value) {
//            $AuthModel->createSystemAuth($value);
//        }
//
////        $param = $this->request->param();
////
////        $addon = $param['addon']??'';
////
////        $className = parse_name($addon, 0);
////
////        $class = '\addon\\'.$className.'\\'.$addon;
////
////        if (class_exists($class) && method_exists($class, 'upgrade')){
////            $obj = new $class();
////            $obj->upgrade();
////        }
//
//        return json(['status'=>200,'msg'=>'请求成功']);
//    }

    /**
     * 时间 2022-5-17
     * @title 支付接口
     * @desc 支付接口
     * @url /admin/v1/gateway
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:支付接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return string list[].url - desc:图片 base64格式或者自定义图片路径
     * @return int count - desc:总数
     */
    public function gateway()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>gateway_list()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-18
     * @title 短信接口
     * @desc 短信接口
     * @url /admin/v1/sms
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:短信接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return int list[].sms_type - desc:短信类型 1国际 0国内
     * @return int count - desc:总数
     */
    public function sms()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('sms')
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-18
     * @title 邮件接口
     * @desc 邮件接口
     * @url /admin/v1/email
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:邮件接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return int count - desc:总数
     */
    public function email()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('mail')
        ];
        return json($result);
    }

    /**
     * 时间 2022-9-7
     * @title 验证码接口
     * @desc 验证码接口
     * @url /admin/v1/captcha_list
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:验证码接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return int count - desc:总数
     */
    public function captchaList()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->plugins('captcha')
        ];
        return json($result);
    }


    /**
     * 时间 2022-5-19
     * @title 公共配置
     * @desc 公共配置
     * @url /admin/v1/common
     * @method GET
     * @author xiong
     * @version v1
     * @return string currency_code - desc:货币代码
     * @return string currency_prefix - desc:货币符号
     * @return string currency_suffix - desc:货币后缀
     * @return string website_name - desc:网站名称
     * @return string system_logo - desc:系统LOGO
     * @return string admin_logo - desc:后台LOGO
     * @return int edition - desc:版本 1专业版 0开源版
     * @return array lang_admin - desc:后台语言列表
     * @return array lang_home - desc:前台语言列表
     * @return array admin_enforce_safe_method - desc:后台强制安全选项 operate_password操作密码
     * @return int global_list_limit - desc:全局列表展示条数
     * @return int recharge_order_support_refund - desc:充值订单是否支持退款 0否 1是
     */
    public function common()
    {
        $setting = [
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'website_name',
            'system_logo',
            'admin_logo',
            'admin_enforce_safe_method',
            'global_list_limit',
            'clientarea_theme_color',
            'recharge_order_support_refund',
        ];
		
		$lang = [ 
			'lang_admin'=> lang_list('admin') ,
			'lang_home'=> lang_list('home') ,
		];
		
		$data = array_merge($lang,configuration($setting));
        $data['admin_enforce_safe_method'] = !empty($data['admin_enforce_safe_method']) ? explode(',', $data['admin_enforce_safe_method']) : [];
        $data['global_list_limit'] = !empty($data['global_list_limit']) ? (int)$data['global_list_limit'] : 10;
        $data['clientarea_theme_color'] = !empty($data['clientarea_theme_color']) ? $data['clientarea_theme_color'] : 'default';
        $data['recharge_order_support_refund'] = (int)$data['recharge_order_support_refund'];

        // 是否专业版和企业版
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
        }

        // 专业版
        if (isset($auth['version']) && in_array($auth['version'],[1,3])){
            $data['edition'] = 1;
        }else{
            $data['edition'] = 0;
        }

        // 异常登录实名认证列表
        $pluginList = (new PluginModel())->pluginList(['module'=>'certification']);
        $pluginListFilter = [];
        foreach ($pluginList['list'] as $item){
            if (!empty($item['status']) && $item['status']==1 && plugin_method_exist($item['name'],'certification','status')){
                $pluginListFilter[] = [
                    'name' => $item['name'],
                    'title' => $item['title']
                ];
            }
        }
        $data['exception_login_certification_plugin_list'] = $pluginListFilter;


        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
			
        ];
        return json($result);
    }
    /**
     * 时间 2022-5-16
     * @title 国家列表
     * @desc 国家列表 包括国家名 中文名 区号
     * @url /admin/v1/country
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围为国家名 中文名 区号 validate:optional
     * @return array list - desc:国家列表
     * @return string list[].name - desc:国家名
     * @return string list[].name_zh - desc:中文名
     * @return int list[].phone_code - desc:区号
     * @return string list[].iso - desc:国家英文缩写
     * @return int count - desc:国家总数
     */
    public function countryList()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $CountryModel = new CountryModel();

        // 获取国家列表
        $data = $CountryModel->countryList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 权限列表
     * @desc 权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/auth
     * @method GET
     * @return array list - desc:权限列表
     * @return int list[].id - desc:权限ID
     * @return string list[].title - desc:权限标题
     * @return string list[].url - desc:地址
     * @return int list[].order - desc:排序
     * @return int list[].parent_id - desc:父级ID
     * @return string list[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].plugin - desc:插件标识名
     * @return array list[].child - desc:权限子集
     * @return int list[].child[].id - desc:权限ID
     * @return string list[].child[].title - desc:权限标题
     * @return string list[].child[].url - desc:地址
     * @return int list[].child[].order - desc:排序
     * @return int list[].child[].parent_id - desc:父级ID
     * @return string list[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].plugin - desc:插件标识名
     * @return array list[].child[].child - desc:权限子集
     * @return int list[].child[].child[].id - desc:权限ID
     * @return string list[].child[].child[].title - desc:权限标题
     * @return string list[].child[].child[].url - desc:地址
     * @return int list[].child[].child[].order - desc:排序
     * @return int list[].child[].child[].parent_id - desc:父级ID
     * @return string list[].child[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].child[].plugin - desc:插件标识名
     * @return string widget[].id - desc:挂件标识
     * @return string widget[].title - desc:挂件标题
     */
    public function authList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new AuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 当前管理员权限列表
     * @desc 当前管理员权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/auth
     * @method GET
     * @return array list - desc:权限列表
     * @return int list[].id - desc:权限ID
     * @return string list[].title - desc:权限标题
     * @return string list[].url - desc:地址
     * @return int list[].order - desc:排序
     * @return int list[].parent_id - desc:父级ID
     * @return string list[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].plugin - desc:插件标识名
     * @return array list[].child - desc:权限子集
     * @return int list[].child[].id - desc:权限ID
     * @return string list[].child[].title - desc:权限标题
     * @return string list[].child[].url - desc:地址
     * @return int list[].child[].order - desc:排序
     * @return int list[].child[].parent_id - desc:父级ID
     * @return string list[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].plugin - desc:插件标识名
     * @return array list[].child[].child - desc:权限子集
     * @return int list[].child[].child[].id - desc:权限ID
     * @return string list[].child[].child[].title - desc:权限标题
     * @return string list[].child[].child[].url - desc:地址
     * @return int list[].child[].child[].order - desc:排序
     * @return int list[].child[].child[].parent_id - desc:父级ID
     * @return string list[].child[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].child[].plugin - desc:插件标识名
     * @return array auths - desc:权限
     */
    public function adminAuthList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new AuthModel())->adminAuthList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-10
     * @title 获取后台导航
     * @desc 获取后台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu
     * @method GET
     * @return array menu - desc:菜单
     * @return int menu[].id - desc:菜单ID
     * @return string menu[].name - desc:名称
     * @return string menu[].url - desc:网址
     * @return string menu[].icon - desc:图标
     * @return int menu[].parent_id - desc:父ID
     * @return array menu[].child - desc:子菜单
     * @return int menu[].child[].id - desc:菜单ID
     * @return string menu[].child[].name - desc:名称
     * @return string menu[].child[].url - desc:网址
     * @return string menu[].child[].icon - desc:图标
     * @return int menu[].child[].parent_id - desc:父ID
     * @return int menu_id - desc:选中菜单ID
     * @return string url - desc:登录后跳转地址
     */
    public function adminMenu(){
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new MenuModel())->adminMenu()
        ];
        return json($result);
    }

    /**
     * 时间 2022-6-20
     * @title 文件上传
     * @desc 文件上传
     * @url /admin/v1/upload
     * @method POST
     * @author wyh
     * @version v1
     * @param resource file - desc:文件资源 validate:required
     * @param int move - desc:移动资源 0否 1是 仅支持图片 validate:optional
     * @return string save_name - desc:文件名
     * @return string data.image_base64 - desc:图片base64 文件为图片才返回
     * @return string data.image_url - desc:图片地址
     */
    public function upload()
    {
        // 接收参数
        $param = $this->request->param();

        $move = (isset($param['move']) && $param['move']==1) ?? false;
        
        $filename = $this->request->file('file');

        if (!isset($filename)){
            return json(['status'=>400,'msg'=>lang('param_error')]);
        }
        if (empty($filename->getOriginalExtension())){
            return json(['status'=>400,'msg'=>lang('param_error')]);
        }

        $str=explode($filename->getOriginalExtension(),$filename->getOriginalName())[0];
        if(preg_match("/['!@^&]|\/|\\\|\"/",substr($str,0,strlen($str)-1))){
            return json(['status'=>400,'msg'=>lang('file_name_error')]);
        }

        $UploadLogic = new UploadLogic();

        $result = $UploadLogic->uploadHandle($filename, true, '^', $move);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 全局搜索
     * @desc 全局搜索
     * @url /admin/v1/global_search
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围为用户姓名 公司 邮箱 手机号 备注 商品名称 商品一级分组名称 商品二级分组名称 产品ID 标识 validate:required
     * @return array clients - desc:用户列表
     * @return int clients[].id - desc:用户ID
     * @return string clients[].username - desc:姓名
     * @return string clients[].company - desc:公司
     * @return string clients[].email - desc:邮箱
     * @return string clients[].phone_code - desc:国际电话区号
     * @return string clients[].phone - desc:手机号
     * @return array products - desc:商品列表
     * @return int products[].id - desc:商品ID
     * @return string products[].name - desc:商品名称
     * @return string products[].product_group_name_first - desc:商品一级分组名称
     * @return string products[].product_group_name_second - desc:商品二级分组名称
     * @return array hosts - desc:产品列表
     * @return int hosts[].id - desc:产品ID
     * @return string hosts[].name - desc:标识
     * @return string hosts[].product_name - desc:商品名称
     * @return int hosts[].client_id - desc:用户ID
     */
    public function globalSearch()
    {
        // 接收参数
        $param = $this->request->param();
        $keywords = $param['keywords'] ?? '';
        if(!empty($keywords)){
            $clients = (new ClientModel())->searchClient($param, 'global');
            $products = (new ProductModel())->searchProduct($keywords);
            $hosts = (new HostModel())->searchHost($keywords);
            $data = [
                'clients' => $clients['list'],
                'products' => $products['list'],
                'hosts' => $hosts['list'],
            ];
        }else{
            $data = [
                'clients' => [],
                'products' => [],
                'hosts' => [],
            ];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取已激活插件
     * @desc 获取已激活插件
     * @url /admin/v1/active_plugin
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:插件列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     */
    public function activePluginList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->activePluginList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 会员中心权限列表
     * @desc 会员中心权限列表
     * @author theworld
     * @version v1
     * @url /admin/v1/clientarea_auth
     * @method GET
     * @return array list - desc:权限列表
     * @return int list[].id - desc:权限ID
     * @return string list[].title - desc:权限标题
     * @return string list[].url - desc:地址
     * @return int list[].order - desc:排序
     * @return int list[].parent_id - desc:父级ID
     * @return string list[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].plugin - desc:插件标识名
     * @return array list[].rules - desc:权限规则标题
     * @return array list[].child - desc:权限子集
     * @return int list[].child[].id - desc:权限ID
     * @return string list[].child[].title - desc:权限标题
     * @return string list[].child[].url - desc:地址
     * @return int list[].child[].order - desc:排序
     * @return int list[].child[].parent_id - desc:父级ID
     * @return string list[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].plugin - desc:插件标识名
     * @return string list[].child[].rules - desc:权限规则标题
     * @return array list[].child[].child - desc:权限子集
     * @return int list[].child[].child[].id - desc:权限ID
     * @return string list[].child[].child[].title - desc:权限标题
     * @return string list[].child[].child[].url - desc:地址
     * @return int list[].child[].child[].order - desc:排序
     * @return int list[].child[].child[].parent_id - desc:父级ID
     * @return string list[].child[].child[].module - desc:插件模块路径 如gateway支付接口 sms短信接口 mail邮件接口 addon插件
     * @return string list[].child[].child[].plugin - desc:插件标识名
     * @return string list[].child[].child[].rules - desc:权限规则标题
     */
    public function clientareaAuthList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ClientareaAuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-20
     * @title 获取全局单表单字段搜索选项
     * @desc 获取全局单表单字段搜索选项
     * @url /admin/v1/common_search_table
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:搜索框显示数据
     * @return string list[].table - desc:表
     * @return string list[].name - desc:显示名称
     * @return array list[].field - desc:字段
     * @return string list[].field[].key - desc:搜索字段
     * @return string list[].field[].name - desc:搜索字段显示名称
     * @return string list[].field[].type - desc:搜索框类型 input输入框 date时间 select选择框
     * @return array list[].field[].option - desc:选择框选项 搜索框类型为选择框时会返回
     * @return string list[].field[].option[].name - desc:选项显示名称 搜索字段不为product_id时返回
     * @return string list[].field[].option[].value - desc:选项值 搜索字段不为product_id时返回
     * @return array list[].field[].option[].child - desc:商品二级分组 搜索字段为product_id时返回
     * @return string list[].field[].option[].child[].name - desc:商品二级分组名称 搜索字段为product_id时返回
     * @return array list[].field[].option[].child[].product - desc:商品 搜索字段为product_id时返回
     * @return int list[].field[].option[].child[].product[].id - desc:商品ID 搜索字段为product_id时返回
     * @return string list[].field[].option[].child[].product[].name - desc:商品名称 搜索字段为product_id时返回
     */
    public function commonSearchTableList()
    {
        $lang = lang();

        $orderStatus = [];
        $status = config('idcsmart.order_status');
        foreach ($status as $key => $value) {
            $orderStatus[] = ['name' => $lang['order_status_'.$value], 'value' => $value];
        }

        $orderType = [];
        $type = config('idcsmart.order_type');
        foreach ($type as $key => $value) {
            $orderType[] = ['name' => $lang['order_type_'.$value], 'value' => $value];
        }

        $hostStatus = [];
        $status = config('idcsmart.host_status');
        foreach ($status as $key => $value) {
            $hostStatus[] = ['name' => $lang['host_status_'.$value], 'value' => $value];
        }

        $product = (new ProductModel())->getProductList();

        $server = (new ServerModel())->getAllServer();
        foreach ($server as $key => $value) {
            $server[$key]['value'] = $value['id'];
            unset($server[$key]['id']);
        }

        $data = [
            [
                'table' => 'client',
                'name' => $lang['common_search_client'],
                'field' => [
                    ['key' => 'id', 'name' => $lang['common_search_client_id'], 'type' => 'input'],
                    ['key' => 'username', 'name' => $lang['common_search_client_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_client_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_client_phone'], 'type' => 'input'],
                    ['key' => 'company', 'name' => $lang['common_search_client_company'], 'type' => 'input'],
                ],
            ],
            [
                'table' => 'host',
                'name' => $lang['common_search_host'],
                'field' => [
                    ['key' => 'host_id', 'name' => $lang['common_search_host_id'], 'type' => 'input'],
                    ['key' => 'status', 'name' => $lang['common_search_host_status'], 'type' => 'select', 'option' => $hostStatus],
                    ['key' => 'product_id', 'name' => $lang['common_search_host_product_id'], 'type' => 'select', 'option' => $product],
                    ['key' => 'name', 'name' => $lang['common_search_host_name'], 'type' => 'input'],
                    ['key' => 'due_time', 'name' => $lang['common_search_host_due_time'], 'type' => 'date'],
                    ['key' => 'username', 'name' => $lang['common_search_host_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_host_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_host_phone'], 'type' => 'input'],
                    ['key' => 'billing_cycle', 'name' => $lang['common_search_host_billing_cycle'], 'type' => 'input'],
                    ['key' => 'server_id', 'name' => $lang['common_search_host_server_id'], 'type' => 'select', 'option' => $server],
                ],
            ],
            [
                'table' => 'order',
                'name' => $lang['common_search_order'],
                'field' => [
                    ['key' => 'order_id', 'name' => $lang['common_search_order_id'], 'type' => 'input'],
                    ['key' => 'product_id', 'name' => $lang['common_search_order_product_id'], 'type' => 'select', 'option' => $product],
                    ['key' => 'username', 'name' => $lang['common_search_order_username'], 'type' => 'input'],
                    ['key' => 'email', 'name' => $lang['common_search_order_email'], 'type' => 'input'],
                    ['key' => 'phone', 'name' => $lang['common_search_order_phone'], 'type' => 'input'],
                    ['key' => 'amount', 'name' => $lang['common_search_order_amount'], 'type' => 'input'],
                    ['key' => 'pay_time', 'name' => $lang['common_search_order_pay_time'], 'type' => 'date'],
                    ['key' => 'status', 'name' => $lang['common_search_order_status'], 'type' => 'select', 'option' => $orderStatus],
                    ['key' => 'type', 'name' => $lang['common_search_order_type'], 'type' => 'select', 'option' => $orderType],
                ],
            ],
        ];

        $result = [
            'status' => 200,
            'msg' => $lang['success_message'],
            'data' => [
                'list' => $data
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-20
     * @title 全局单表单字段搜索
     * @desc 全局单表单字段搜索
     * @url /admin/v1/common_search
     * @method GET
     * @author theworld
     * @version v1
     * @param string table - desc:表 validate:required
     * @param string key - desc:搜索字段 validate:required
     * @param string value - desc:搜索传递的值 validate:required
     * @return string table - desc:表
     * @return array list - desc:搜索后返回的列表 用于跳转到对应页面显示
     * @return int count - desc:总数 当数量为1时跳转到对应内页
     */
    public function commonSearch()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $table = $param['table'] ?? '';
        $key = $param['key'] ?? '';
        $value = $param['value'] ?? '';

        if($table=='client'){
            $param['type'] = $key;
            $param['keywords'] = $value;
            $data = (new ClientModel())->clientList($param);
        }else if($table=='host'){
            if($key=='due_time'){
                $param['start_time'] = $value;
                $param['end_time'] = $value;
            }else{
                $param[$key] = $value;
            }
            $data = (new HostModel())->hostList($param);
        }else if($table=='order'){
            $param[$key] = $value;
            $data = (new OrderModel())->orderList($param);
        }else{
            $data = [
                'list' => [],
                'count' => 0,
            ];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 获取视图详情
     * @desc 获取视图详情
     * @author theworld
     * @version v1
     * @url /admin/v1/view
     * @method GET
     * @param string id - desc:视图ID 和页面标识二选一必传 validate:optional
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 和视图ID二选一必传 validate:optional
     * @return string field[].name - desc:字段分组名称
     * @return string field[].field[].key - desc:字段标识
     * @return string field[].field[].name - desc:字段名称
     * @return array select_field - desc:当前选定字段标识
     * @return int data_range_switch - desc:是否启用数据范围 0否 1是
     * @return array select_data_range - desc:当前选定数据范围
     * @return string select_data_range[].key - desc:当前选定数据范围字段标识
     * @return string select_data_range[].rule - desc:当前选定数据范围规则 equal等于 not_equal不等于 include包含 not_include不包含 empty为空 not_empty不为空 interval区间 dynamic动态
     * @return mixed select_data_range[].value - desc:当前选定数据范围的值 规则为empty和not_empty时不需要传递
     * @return string select_data_range[].value.start - desc:开始日期 数据范围为date且规则为interval时必传
     * @return string select_data_range[].value.end - desc:结束日期 数据范围为date且规则为interval时必传
     * @return string select_data_range[].value.condition1 - desc:动态条件1 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传
     * @return int select_data_range[].value.day1 - desc:动态时间1 数据范围为date且规则为dynamic且condition1不为now时必传
     * @return string select_data_range[].value.condition2 - desc:动态条件2 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传
     * @return int select_data_range[].value.day2 - desc:动态时间2 数据范围为date且规则为dynamic且condition2不为now时必传
     * @return array password_field - desc:密码类型字段
     * @return array admin_view_list - desc:可切换视图列表
     * @return int admin_view_list[].id - desc:视图ID
     * @return string admin_view_list[].name - desc:视图名称
     * @return int admin_view_list[].default - desc:默认视图 0否 1是
     */
    public function adminViewIndex()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('view')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $data = $AdminViewModel->adminViewIndex($param);

        if(isset($data['status']) && $data['status']==400){
            $result = $data;
        }else{
            $result = [
                'status' => 200,
                'msg'    => lang('success_message'),
                'data'   => $data,
            ];
        }

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 获取视图可用数据范围
     * @desc 获取视图可用数据范围
     * @author theworld
     * @version v1
     * @url /admin/v1/view/data_range
     * @method GET
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     * @return string data_range[].name - desc:数据范围分组名称
     * @return string data_range[].field[].key - desc:数据范围字段标识
     * @return string data_range[].field[].name - desc:数据范围字段名称
     * @return string data_range[].field[].type - desc:数据范围字段类型 input输入 multi_select下拉多选 select下拉单选 date日期
     * @return array data_range[].field[].option - desc:选项 类型为multi_select或select时返回
     * @return int data_range[].field[].option[].id - desc:ID 标识为server_name client_level sale country时返回
     * @return string data_range[].field[].option[].name - desc:名称 标识为product_name server_name client_level sale country时返回
     * @return array data_range[].field[].option[].child - desc:商品二级分组 标识为product_name时返回
     * @return string data_range[].field[].option[].child[].name - desc:商品二级分组名称 标识为product_name时返回
     * @return array data_range[].field[].option[].child[].product - desc:商品 标识为product_name时返回
     * @return int data_range[].field[].option[].child[].product[].id - desc:商品ID 标识为product_name时返回
     * @return string data_range[].field[].option[].child[].product[].name - desc:商品名称 标识为product_name时返回
     * @return array data_range[].field[].rule - desc:数据范围规则 equal等于 not_equal不等于 include包含 not_include不包含 empty为空 not_empty不为空 interval区间 dynamic动态
     */
    public function adminViewDataRange()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('view')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $data = $AdminViewModel->adminViewDataRange($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 视图列表
     * @desc 视图列表
     * @author theworld
     * @version v1
     * @url /admin/v1/view/list
     * @method GET
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     * @return array list - desc:视图列表
     * @return int list[].id - desc:视图ID
     * @return string list[].name - desc:视图名称
     * @return int list[].default - desc:默认视图 0否 1是
     * @return int list[].status - desc:状态 0关闭 1开启
     * @return int choose - desc:当前指定视图 为0代表默认展示最后浏览视图
     * @return array choose_list - desc:可指定视图列表
     * @return int choose_list[].id - desc:视图ID
     * @return string choose_list[].name - desc:视图名称
     */
    public function adminViewList()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('view')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $data = $AdminViewModel->adminViewList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 指定视图
     * @desc 指定视图
     * @author theworld
     * @version v1
     * @url /admin/v1/view/choose
     * @method PUT
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     * @param int choose - desc:指定视图ID 为0代表默认展示最后浏览视图 validate:required
     */
    public function chooseAdminView()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('choose')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->chooseAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 新建视图
     * @desc 新建视图
     * @author theworld
     * @version v1
     * @url /admin/v1/view
     * @method POST
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     * @param string name - desc:视图名称 validate:required
     * @param array select_field - desc:选定字段标识 validate:required
     * @param int data_range_switch - desc:是否启用数据范围 0否 1是 validate:required
     * @param array select_data_range - desc:当前选定数据范围 validate:optional
     * @param string select_data_range[].key - desc:选定数据范围字段标识 validate:optional
     * @param string select_data_range[].rule - desc:选定数据范围规则 equal等于 not_equal不等于 include包含 not_include不包含 empty为空 not_empty不为空 interval区间 dynamic动态 validate:optional
     * @param mixed select_data_range[].value - desc:当前选定数据范围的值 规则为empty和not_empty时不需要传递 validate:optional
     * @param string select_data_range[].value.start - desc:开始日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.end - desc:结束日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.condition1 - desc:动态条件1 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day1 - desc:动态时间1 数据范围为date且规则为dynamic且condition1不为now时必传 validate:optional
     * @param string select_data_range[].value.condition2 - desc:动态条件2 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day2 - desc:动态时间2 数据范围为date且规则为dynamic且condition2不为now时必传 validate:optional
     */
    public function createAdminView()
    {
        $param = $this->request->param();
        $param['admin_id'] = get_admin_id();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->createAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 编辑视图
     * @desc 编辑视图
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id
     * @method PUT
     * @param int id - desc:视图ID validate:required
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     * @param string name - desc:视图名称 validate:required
     * @param array select_field - desc:选定字段标识 validate:required
     * @param int data_range_switch - desc:是否启用数据范围 0否 1是 validate:required
     * @param array select_data_range - desc:当前选定数据范围 validate:optional
     * @param string select_data_range[].key - desc:选定数据范围字段标识 validate:optional
     * @param string select_data_range[].rule - desc:选定数据范围规则 equal等于 not_equal不等于 include包含 not_include不包含 empty为空 not_empty不为空 interval区间 dynamic动态 validate:optional
     * @param mixed select_data_range[].value - desc:当前选定数据范围的值 规则为empty和not_empty时不需要传递 validate:optional
     * @param string select_data_range[].value.start - desc:开始日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.end - desc:结束日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.condition1 - desc:动态条件1 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day1 - desc:动态时间1 数据范围为date且规则为dynamic且condition1不为now时必传 validate:optional
     * @param string select_data_range[].value.condition2 - desc:动态条件2 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day2 - desc:动态时间2 数据范围为date且规则为dynamic且condition2不为now时必传 validate:optional
     */
    public function updateAdminView()
    {
        $param = $this->request->param();
        $param['admin_id'] = get_admin_id();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->updateAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 编辑视图字段
     * @desc 编辑视图字段
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id/field
     * @method PUT
     * @param int id - desc:视图ID validate:required
     * @param array select_field - desc:选定字段标识 validate:required
     */
    public function updateAdminViewField()
    {
        $param = $this->request->only(['id', 'select_field']);

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('select_field')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->updateAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 编辑视图数据范围
     * @desc 编辑视图数据范围
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id/data_range
     * @method PUT
     * @param int id - desc:视图ID validate:required
     * @param array select_data_range - desc:当前选定数据范围 validate:required
     * @param string select_data_range[].key - desc:选定数据范围字段标识 validate:optional
     * @param string select_data_range[].rule - desc:选定数据范围规则 equal等于 not_equal不等于 include包含 not_include不包含 empty为空 not_empty不为空 interval区间 dynamic动态 validate:optional
     * @param mixed select_data_range[].value - desc:当前选定数据范围的值 规则为empty和not_empty时不需要传递 validate:optional
     * @param string select_data_range[].value.start - desc:开始日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.end - desc:结束日期 数据范围为date且规则为interval时必传 validate:optional
     * @param string select_data_range[].value.condition1 - desc:动态条件1 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day1 - desc:动态时间1 数据范围为date且规则为dynamic且condition1不为now时必传 validate:optional
     * @param string select_data_range[].value.condition2 - desc:动态条件2 now当前 ago天前 later天后 数据范围为date且规则为dynamic时必传 validate:optional
     * @param int select_data_range[].value.day2 - desc:动态时间2 数据范围为date且规则为dynamic且condition2不为now时必传 validate:optional
     */
    public function updateAdminViewDataRange()
    {
        $param = $this->request->only(['id', 'select_data_range']);
        $param['data_range_switch'] = 1;

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('select_data_range')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->updateAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 删除视图
     * @desc 删除视图
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id
     * @method DELETE
     * @param int id - desc:视图ID validate:required
     */
    public function deleteAdminView()
    {
        $param = $this->request->param();

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->deleteAdminView($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 复制视图
     * @desc 复制视图
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id/copy
     * @method POST
     * @param int id - desc:视图ID validate:required
     */
    public function copyAdminView()
    {
        $param = $this->request->param();

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->copyAdminView($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 视图切换状态
     * @desc 视图切换状态
     * @author theworld
     * @version v1
     * @url /admin/v1/view/:id/status
     * @method PUT
     * @param int id - desc:视图ID validate:required
     * @param int status - desc:状态 0关闭 1开启 validate:required
     */
    public function statusAdminView()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->statusAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 视图排序
     * @desc 视图排序
     * @author theworld
     * @version v1
     * @url /admin/v1/view/order
     * @method PUT
     * @param array id - desc:视图ID数组 validate:required
     * @param string view - desc:页面标识 client用户管理 order订单管理 host产品管理 transaction交易流水 validate:required
     */
    public function orderAdminView()
    {
        $param = $this->request->param();

        $AdminViewValidate = new AdminViewValidate();
        if (!$AdminViewValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminViewValidate->getError())]);
        }

        $AdminViewModel = new AdminViewModel();
        $result = $AdminViewModel->orderAdminView($param);

        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 模板控制器Tab
     * @desc 模板控制器Tab
     * @author theworld
     * @version v1
     * @url /admin/v1/template_tab
     * @method GET
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @return array list - desc:模板控制器Tab列表
     * @return string list[].name - desc:标识
     * @return string list[].title - desc:标题
     * @return string list[].url - desc:地址
     */
    public function templateTabList()
    {
        $param = $this->request->param();

        $TemplateLogic = new TemplateLogic();
        $result = $TemplateLogic->templateTabList($param);

        return json($result);
    }

    /**
     * 时间 2024-08-19
     * @title 公共发送参数
     * @desc 公共发送参数
     * @url /admin/v1/send_param
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:发送参数列表
     * @return string list[].label - desc:标签
     * @return array list[].param - desc:参数
     * @return string list[].param[].value - desc:值
     * @return string list[].param[].label - desc:标签
     */
    public function sendParam()
    {
        $defaultParam = [
            'system' => [
                'system_website_name',
                'system_website_url',
                'send_time' ,
                'action_trigger_time',
            ],
            'order' => [
                'order_id',
                'order_create_time',
                'order_amount',
                'pay_time',
            ],
            'host' => [
                'product_id',
                'product_name',
                'product_marker_name',
                'product_first_payment_amount',
                'product_renew_amount',
                'product_binlly_cycle',
                'product_active_time',
                'product_due_time',
                'product_suspend_reason',
                'renewal_first' ,
                'renewal_second',
                'dedicate_ip',
                'product_username',
                'product_password',
                'product_port',
                'product',
            ],
            'client' => [
                'client_register_time',
                'client_username',
                'client_email',
                'client_phone',
                'client_company',
                'client_last_login_time',
                'client_last_login_ip',
                'account',
                'client_id',
            ],
        ];

        $list = [];
        foreach ($defaultParam as $key => $value) {
            $param = [];
            foreach ($value as $v) {
                $param[] = ['value' => $v, 'label' => lang('send_param_'.$v)];
            }
            $list[] = ['label' => lang($key.'_send_param'), 'param' => $param];
        }

        $res = hook('append_send_param');
        foreach ($res as $value) {
            if (isset($value['label']) && !empty($value['label']) && isset($value['param']) && is_array($value['param'])){
                $list[] = ['label' => $value['label'], 'param' => $value['param']];
            }
        }

        $result = ['status' => 200, 'msg' => lang('success_message'), 'data' => ['list' => $list]];
        return json($result);
    }

    public function moveFrom()
    {
        $ClientModel = new ClientModel();
        $clients = $ClientModel->where('id','>',0)->select()->toArray();
        $HostModel = new HostModel();
        $hosts = $HostModel->where('id','>',0)->select()->toArray();
        $HostIpModel = new HostIpModel();
        $hostIps = $HostIpModel->where('id','>',0)->select()->toArray();
        if (!is_dir(IDCSMART_ROOT . 'movesql')){
            mkdir(IDCSMART_ROOT . 'movesql');
        }
        if (class_exists("\\server\\mf_cloud\\model\\HostLinkModel")){
            $HostLinkModel = new \server\mf_cloud\model\HostLinkModel();
            $hostlinks = $HostLinkModel->where('id','>',0)->select()->toArray();
            $DataCenterModel = new \server\mf_cloud\model\DataCenterModel();
            $ImageModel = new \server\mf_cloud\model\ImageModel();
            foreach ($hostlinks as &$hostlink){
                $hostlink['data_center'] = $DataCenterModel->where('id',$hostlink['data_center_id'])->find();
                $hostlink['image'] = $ImageModel->field('i.*,ig.name as image_group_name,ig.icon')
                    ->alias('i')
                    ->leftJoin('module_mf_cloud_image_group ig','i.image_group_id=ig.id')
                    ->where('i.id',$hostlink['image_id'])
                    ->find();
            }
            file_put_contents(IDCSMART_ROOT . 'movesql/idcsmart_module_mf_cloud_host_link.sql', json_encode($hostlinks, JSON_UNESCAPED_UNICODE));
        }
        $ClientCreditModel = new ClientCreditModel();
        $clientCredits = $ClientCreditModel->where('id','>',0)->select()->toArray();
        if (!is_dir(IDCSMART_ROOT . 'movesql')){
            mkdir(IDCSMART_ROOT . 'movesql');
        }
        file_put_contents(IDCSMART_ROOT . 'movesql/idcsmart_client.sql', json_encode($clients, JSON_UNESCAPED_UNICODE));
        file_put_contents(IDCSMART_ROOT . 'movesql/idcsmart_client_credit.sql', json_encode($clientCredits, JSON_UNESCAPED_UNICODE));
        file_put_contents(IDCSMART_ROOT . 'movesql/idcsmart_host.sql', json_encode($hosts, JSON_UNESCAPED_UNICODE));
        file_put_contents(IDCSMART_ROOT . 'movesql/idcsmart_host_ip.sql', json_encode($hostIps, JSON_UNESCAPED_UNICODE));
        return json([
            'status' => 200,
            'msg' => lang('success_message'),
        ]);
    }

    public function moveTo()
    {
        $ClientModel = new ClientModel();
        $ClientModel->startTrans();
        try {
            // 客户数据
            $clients = file_get_contents(IDCSMART_ROOT . 'movesql/idcsmart_client.sql');
            $clients = json_decode($clients,true);
            $clientData = [];
            foreach ($clients as $client){
                $clientData[] = [
                    'id' => $client['id'],
                    'username' => $client['username'],
                    'status' => $client['status'],
                    'email' => $client['email'],
                    'phone_code' => $client['phone_code'],
                    'phone' => $client['phone'],
                    'password' => $client['password'],
                    'credit' => $client['credit'],
                    'company' => $client['company'],
                    'address' => $client['address'],
                    'language' => $client['language'],
                    'notes' => $client['notes'],
                    'client_notes' => $client['client_notes'],
                    'last_login_time' => $client['last_login_time'],
                    'last_login_ip' => $client['last_login_ip'],
                    'last_action_time' => $client['last_action_time'],
                    'create_time' => $client['create_time'],
                    'update_time' => $client['update_time'],
                    'operate_password' => '',
                    'notice_open' => 1,
                    'notice_method' => 'all',
                    'country_id' => 44,
                    'receive_sms' => 1,
                    'receive_email' => 1,
                ];
            }
            if (!empty($clientData)){
                $ClientModel->where('id','>',0)->delete();
                $ClientModel->insertAll($clientData);
            }
            // 客户余额日志
            $clientCredits = file_get_contents(IDCSMART_ROOT . 'movesql/idcsmart_client_credit.sql');
            $clientCredits = json_decode($clientCredits,true);
            $ClientCreditModel = new ClientCreditModel();
            $clientCreditData = [];
            foreach ($clientCredits as $clientCredit){
                $clientCreditData[] = [
                    'id' => $clientCredit['id'],
                    'type' => $clientCredit['type'],
                    'amount' => $clientCredit['amount'],
                    'credit' => $clientCredit['credit'],
                    'notes' => $clientCredit['notes'],
                    'order_id' => $clientCredit['order_id'],
                    'host_id' => $clientCredit['host_id'],
                    'client_id' => $clientCredit['client_id'],
                    'admin_id' => $clientCredit['admin_id'],
                    'create_time' => $clientCredit['create_time'],
                ];
            }
            if (!empty($clientCreditData)){
                $ClientCreditModel->where('id','>',0)->delete();
                $ClientCreditModel->insertAll($clientCreditData);
            }
            // 产品数据
            $hosts = file_get_contents(IDCSMART_ROOT . 'movesql/idcsmart_host.sql');
            $hosts = json_decode($hosts,true);
            $HostModel = new HostModel();
            $hostData = [];
            foreach ($hosts as $host){
                $hostData[] = [
                    'id' => $host['id'],
                    'client_id' => $host['client_id'],
                    'order_id' => $host['order_id'],
                    'product_id' => $host['product_id'],
                    'server_id' => $host['server_id'],
                    'name' => $host['name'],
                    'status' => $host['status'],
                    'suspend_type' => $host['suspend_type'],
                    'suspend_reason'=> $host['suspend_reason'],
                    'suspend_time'=> $host['suspend_time'],
                    'gateway'=> $host['gateway'],
                    'gateway_name'=> $host['gateway_name'],
                    'first_payment_amount'=> $host['first_payment_amount'],
                    'renew_amount'=> $host['renew_amount'],
                    'billing_cycle'=> $host['billing_cycle'],
                    'billing_cycle_name'=> $host['billing_cycle_name'],
                    'billing_cycle_time'=> $host['billing_cycle_time'],
                    'notes'=> $host['notes'],
                    'client_notes'=> $host['client_notes'],
                    'active_time'=> $host['active_time'],
                    'due_time'=> $host['due_time'],
                    'termination_time'=> $host['termination_time'],
                    'create_time'=> $host['create_time'],
                    'update_time'=> $host['update_time'],
                    'downstream_info'=> $host['downstream_info'],
                    'downstream_host_id'=> $host['downstream_host_id'],
                    'base_price'=> $host['base_price'],
                    'ratio_renew'=> $host['ratio_renew'],
                    'is_delete'=> $host['is_delete'],
                    'base_renew_amount'=> 0,
                    'base_info'=> '',
                    'transfer_time'=> 0,
                    'failed_action'=> '',
                    'failed_action_times'=> 0,
                    'failed_action_need_handle'=> 0,
                    'failed_action_reason'=> '',
                    'failed_action_trigger_time'=> 0,
                ];
            }
            if (!empty($hostData)){
                $HostModel->where('id','>',0)->delete();
                $HostModel->insertAll($hostData);
            }
            // 产品ip数据
            $hostIps = file_get_contents(IDCSMART_ROOT . 'movesql/idcsmart_host_ip.sql');
            $hostIps = json_decode($hostIps,true);
            $HostIpModel = new HostIpModel();
            $hostIpData = [];
            foreach ($hostIps as $hostIp){
                $hostIpData[] = [
                    'id' => $hostIp['id'],
                    'host_id' => $hostIp['host_id'],
                    'dedicate_ip' => $hostIp['dedicate_ip'],
                    'assign_ip' => $hostIp['assign_ip'],
                    'ip_num' => $hostIp['ip_num'],
                    'create_time' => $hostIp['create_time'],
                    'update_time' => $hostIp['update_time'],
                ];
            }
            if (!empty($hostIpData)){
                $HostIpModel->where('id','>',0)->delete();
                $HostIpModel->insertAll($hostIpData);
            }
            // 魔方云关联数据
            if (class_exists("\\server\\mf_cloud\\model\\HostLinkModel")){
                $hostlinks = file_get_contents(IDCSMART_ROOT . 'movesql/idcsmart_module_mf_cloud_host_link.sql');
                $hostlinks = json_decode($hostlinks,true);
                $HostLinkModel = new \server\mf_cloud\model\HostLinkModel();
                $HostAdditionModel  = new \app\common\model\HostAdditionModel();
                $hostLinkData = [];
                $time = time();
                foreach ($hostlinks as $hostlink){
                    $hostLinkData[] = [
                        'id' => $hostlink['id'],
                        'host_id' => $hostlink['host_id'],
                        'rel_id' => $hostlink['rel_id'],
                        'data_center_id' => $hostlink['data_center_id'],
                        'image_id' => $hostlink['image_id'],
                        'backup_num' => $hostlink['backup_num'],
                        'snap_num' => $hostlink['snap_num'],
                        'power_status' => $hostlink['power_status'],
                        'ip' => $hostlink['ip'],
                        'vpc_network_id' => $hostlink['vpc_network_id'],
                        'config_data' => $hostlink['config_data'],
                        'ssh_key_id' => $hostlink['ssh_key_id'],
                        'password' => $hostlink['password'],
                        'create_time' => $hostlink['create_time'],
                        'update_time' => $hostlink['update_time'],
                        'type' => $hostlink['type'],
                        'recommend_config_id' => $hostlink['recommend_config_id'],
                        'default_ipv4' => -1,
                        'migrate_task_id' => 0,
                    ];
                    if(!empty($hostlink['image'])){
                        if($hostlink['image']['image_group_name'] == 'Windows'){
                            $username = 'administrator';
                        }else{
                            $username = 'root';
                        }
                    }else{
                        $username = '';
                    }
                    $hostAddition = $HostAdditionModel->where('host_id',$hostlink['host_id'])->find();
                    if (empty($hostAddition)){
                        $HostAdditionModel->insert([
                            'host_id' => $hostlink['host_id'],
                            'country_id' => $hostlink['data_center']['country_id']??0,
                            'city' => $hostlink['data_center']['city']??'',
                            'area' => $hostlink['data_center']['area']??'',
                            'power_status' => $hostlink['power_status'],
                            'image_icon' => $hostlink['image']['icon']??'',
                            'image_name' => $hostlink['image']['name']??'',
                            'username' => $username,
                            'password' => aes_password_decode($hostlink['password']),
                            'port' => '',
                            'create_time' => $time,
                            'update_time' => $time,
                        ]);
                    }else{
                        if (!empty($hostlink['data_center']['country_id']) && !empty($hostlink['image']['icon'])){
                            $HostAdditionModel->where('host_id',$hostlink['host_id'])->update([
                                'country_id' => $hostlink['data_center']['country_id'],
                                'city' => $hostlink['data_center']['city']??'',
                                'area' => $hostlink['data_center']['area']??'',
                                'power_status' => $hostlink['power_status'],
                                'image_icon' => $hostlink['image']['icon'],
                            ]);
                        }
                    }
                }
                if (!empty($hostLinkData)){
                    $HostLinkModel->where('id','>',0)->delete();
                    $HostLinkModel->insertAll($hostLinkData);
                }
            }
            $ClientModel->commit();
        }catch (\Exception $e){
            $ClientModel->rollback();
            return json([
                'status' => 400,
                'msg' => $e->getMessage(),
            ]);
        }
        return json([
            'status' => 200,
            'msg' => lang('success_message'),
        ]);
    }

    public function setClientareaThemeColor()
    {
        $param = $this->request->param();

        $result = curl("https://my.idcsmart.com/console/v1/idcsmart_business/version",[
            'license' => configuration('system_license'),
        ],30,'GET');
        if ($result['http_code']==200){
            $version = json_decode($result['content'],true)['data']['version']??'';
            if ($version!='专业版'){
                return json([
                    'status' => 400,
                    'msg' => "此功能仅专业版支持",
                ]);
            }
        }

        updateConfiguration('clientarea_theme_color',$param['clientarea_theme_color']??'default');

        return json([
            'status' => 200,
            'msg' => lang('success_message'),
        ]);
    }

}
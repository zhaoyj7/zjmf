<?php

namespace app\home\controller;

use app\admin\model\PluginModel;
use app\common\logic\SmsLogic;
use app\common\logic\UploadLogic;
use app\common\model\CountryModel;
use app\common\logic\VerificationCodeLogic;
use app\common\model\FileLogModel;
use app\common\model\ThemeBannerModel;
use app\common\model\ThemeConfigModel;
use app\home\validate\CommonValidate;
use PDO;
use PDOException;
use think\facade\Cache;
use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\home\model\ClientareaAuthModel;
use app\common\model\FeedbackModel;
use app\common\model\FeedbackTypeModel;
use app\common\model\ConsultModel;
use app\common\model\FriendlyLinkModel;
use app\common\model\HonorModel;
use app\common\model\PartnerModel;
use app\home\validate\FeedbackValidate;
use app\home\validate\ConsultValidate;
use app\common\model\ConfigurationModel;
use app\common\model\ClientModel;

/**
 * @title 公共接口(前台,无需登录)
 * @desc 公共接口(前台,无需登录)
 * @use app\home\controller\CommonController
 */
class CommonController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CommonValidate();
    }

    /**
     * 时间 2022-5-16
     * @title 获取国家列表
     * @desc 获取国家列表,包括国家名，中文名，区号
     * @author theworld
     * @version v1
     * @url /console/v1/country
     * @method GET
     * @param string keywords - desc:关键字 搜索范围国家名中文名区号 validate:optional
     * @return array list - desc:国家列表
     * @return string list[].name - desc:国家名
     * @return string list[].name_zh - desc:中文名
     * @return int list[].phone_code - desc:区号
     * @return string list[].iso - desc:国家英文缩写
     * @return int count - desc:国家总数
     */
    public function countryList()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $CountryModel = new CountryModel();

        //获取国家列表
        $data = $CountryModel->countryList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 发送手机验证码
     * @desc 发送手机验证码
     * @author theworld
     * @version v1
     * @url /console/v1/phone/code
     * @method POST
     * @param string action - desc:验证动作 login登录 register注册 verify验证手机 update修改手机 password_reset重置密码 exception_login异常登录 update_password修改密码 update_operate_password修改操作密码 host_transfer产品转移 validate:required
     * @param int phone_code - desc:国际电话区号 未登录或修改手机时需要 validate:optional
     * @param string phone - desc:手机号 未登录或修改手机时需要 validate:optional
     * @param string token - desc:图形验证码唯一识别码 validate:optional
     * @param string captcha - desc:图形验证码 validate:optional
     */
    public function sendPhoneCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('sened_phone_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendPhoneCode($param);

        return json($result);
        
    }

    /**
     * 时间 2022-5-19
     * @title 发送邮件验证码
     * @desc 发送邮件验证码
     * @author theworld
     * @version v1
     * @url /console/v1/email/code
     * @method POST
     * @param string action - desc:验证动作 login登录 register注册 verify验证邮箱 update修改邮箱 password_reset重置密码 exception_login异常登录 update_password修改密码 update_operate_password修改操作密码 host_transfer产品转移 validate:required
     * @param string email - desc:邮箱 未登录或修改邮箱时需要 validate:optional
     * @param string token - desc:图形验证码唯一识别码 validate:optional
     * @param string captcha - desc:图形验证码 validate:optional
     */
    public function sendEmailCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('sened_email_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendEmailCode($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /console/v1/captcha
     * @method GET
     * @author wyh
     * @version v1
     * @return string html - desc:html文档
     */
    public function captcha()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'html' => get_captcha()
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 支付接口
     * @desc 支付接口
     * @url /console/v1/gateway
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:支付接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return string list[].url - desc:图片 base64格式
     * @return int count - desc:总数
     */
    public function gateway()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => gateway_list()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 公共配置
     * @desc 公共配置
     * @url /console/v1/common
     * @method GET
     * @author wyh
     * @version v1
     * @param string account - desc:账户 validate:optional
     * @return array lang_list - desc:语言列表
     * @return string lang_home - desc:前台默认语言 zh-cn
     * @return int lang_home_open - desc:前台多语言开关 1开启 0关闭
     * @return int maintenance_mode - desc:维护模式开关 1开启 0关闭
     * @return string maintenance_mode_message - desc:维护模式内容
     * @return string website_name - desc:网站名称
     * @return string website_url - desc:网站域名地址
     * @return string terms_service_url - desc:服务条款地址
     * @return string terms_privacy_url - desc:隐私条款地址
     * @return array prohibit_user_information_changes - desc:禁止用户信息变更
     * @return int login_phone_verify - desc:手机号登录短信验证开关 1开启 0关闭
     * @return int captcha_client_register - desc:客户注册图形验证码开关 1开启 0关闭
     * @return int captcha_client_login - desc:客户登录图形验证码开关 1开启 0关闭
     * @return int captcha_client_login_error - desc:客户登录失败图形验证码开关 1开启 0关闭
     * @return int captcha_client_login_error_3_times - desc:客户登录失败3次
     * @return int captcha_client_verify - desc:验证手机邮箱图形验证码开关 1开启 0关闭
     * @return int captcha_client_update - desc:修改手机邮箱图形验证码开关 1开启 0关闭
     * @return int captcha_client_password_reset - desc:重置密码图形验证码开关 1开启 0关闭
     * @return int captcha_client_oauth - desc:三方登录图形验证码开关 1开启 0关闭
     * @return int captcha_client_security_verify - desc:安全校验是否开启图形验证码 1开启 0关闭
     * @return int register_email - desc:邮箱注册开关 1开启 0关闭
     * @return int register_phone - desc:手机号注册开关 1开启 0关闭
     * @return int recharge_open - desc:启用充值 1启用 0否
     * @return float recharge_min - desc:单笔最小金额
     * @return float recharge_max - desc:单笔最大金额
     * @return string currency_code - desc:货币代码 CNY
     * @return string currency_prefix - desc:货币符号 ￥
     * @return string currency_suffix - desc:货币后缀 元
     * @return int code_client_email_register - desc:邮箱注册数字验证码开关 1开启 0关闭
     * @return string system_logo - desc:系统LOGO
     * @return string put_on_record - desc:备案信息
     * @return string enterprise_name - desc:企业名称
     * @return string enterprise_telephone - desc:企业电话
     * @return string enterprise_mailbox - desc:企业邮箱
     * @return string enterprise_qrcode - desc:企业二维码
     * @return string online_customer_service_link - desc:在线客服链接
     * @return string icp_info - desc:ICP信息
     * @return string icp_info_link - desc:ICP信息链接
     * @return string public_security_network_preparation - desc:公安网备
     * @return string public_security_network_preparation_link - desc:公安网备链接
     * @return string telecom_appreciation - desc:电信增值
     * @return string copyright_info - desc:版权信息
     * @return string official_website_logo - desc:官网LOGO
     * @return string cloud_product_link - desc:云产品跳转链接
     * @return string dcim_product_link - desc:DCIM产品跳转链接
     * @return string clientarea_logo_url - desc:会员中心LOGO跳转地址
     * @return int clientarea_logo_url_blank - desc:会员中心LOGO跳转是否打开新页面 1是 0否
     * @return array feedback_type - desc:意见反馈类型
     * @return int feedback_type[].id - desc:意见反馈类型ID
     * @return string feedback_type[].name - desc:名称
     * @return string feedback_type[].description - desc:描述
     * @return array friendly_link - desc:友情链接
     * @return int friendly_link[].id - desc:友情链接ID
     * @return string friendly_link[].name - desc:名称
     * @return string friendly_link[].url - desc:链接地址
     * @return array honor - desc:荣誉资质
     * @return int honor[].id - desc:荣誉资质ID
     * @return string honor[].name - desc:名称
     * @return string honor[].img - desc:图片地址
     * @return array partner - desc:合作伙伴
     * @return int partner[].id - desc:合作伙伴ID
     * @return string partner[].name - desc:名称
     * @return string partner[].img - desc:图片地址
     * @return string partner[].description - desc:描述
     * @return int cron_due_renewal_first_swhitch - desc:是否开启自动续费功能
     * @return int cron_due_renewal_first_day - desc:提前多少天续费
     * @return array oauth - desc:三方登录列表
     * @return string oauth[].img - desc:图标地址
     * @return string oauth[].name - desc:三方登录标识
     * @return string oauth[].title - desc:三方登录名称
     * @return string oauth[].url - desc:请求地址
     * @return array home_enforce_safe_method - desc:前台强制安全选项 phone=手机 email=邮箱 operate_password=操作密码 certification=实名认证 oauth=三方登录扫码
     * @return string recharge_money_notice_content - desc:充值金额提示内容
     * @return string recharge_pay_notice_content - desc:充值支付提示内容
     * @return string first_login_method - desc:账户凭证首选登录方式 code=验证码 password=密码
     * @return string first_password_login_method - desc:密码登录首选 phone=手机 email=邮箱
     * @return int cart_instruction - desc:购物车说明开关 0关闭 1开启
     * @return string cart_instruction_content - desc:购物车说明内容
     * @return int cart_change_product - desc:购物车切换商品开关 0关闭 1开启
     * @return object plugin_configuration - desc:插件配置
     * @return int plugin_configuration.idcsmart_sale.register_hide_sale - desc:用户注册时隐藏我有销售 0关闭 1开启
     * @return int login_email_password - desc:是否开启邮箱密码登录 1开启 0关闭
     * @return int login_phone_password - desc:是否开启手机密码登录 1开启 0关闭
     * @return string first_login_type - desc:首选登录方式 account_login=账户凭证登录 mp_weixin_notice=微信公众号登录
     * @return string clientarea_theme_display_one - desc:主题区域展示内容1 announcement公告 recommend推荐计划
     * @return string clientarea_theme_display - desc:主题区域展示内容2 ticket工单 announcement公告 recommend推荐计划
     * @return array clientarea_theme_banner_list - desc:banner列表
     * @return int donot_save_client_product_password - desc:不保存用户产品密码 1是 0否
     * @return int edition - desc:版本 1专业版 0基础版
     */
    public function common()
    {
        $param = $this->request->param();
		$lang = [ 
			'lang_list'=> lang_list('home') ,
		];
        $setting = [
            'lang_home',
            'lang_home_open',
            'maintenance_mode',
            'maintenance_mode_message',
            'website_name',
            'website_url',
            'terms_service_url',
            'terms_privacy_url',
            'prohibit_user_information_changes',
            'login_phone_verify',
            'captcha_client_register',
            'captcha_client_login',
            'captcha_client_login_error',
            'captcha_client_verify',
            'captcha_client_update',
            'captcha_client_password_reset',
            'captcha_client_oauth',
            'captcha_client_security_verify',
            'register_email',
            'register_phone',
            'recharge_open',
            'recharge_min',
            'recharge_max',
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'code_client_email_register',
            'code_client_phone_register',
            'system_logo',
            'put_on_record',
            'enterprise_name',
            'enterprise_telephone',
            'enterprise_mailbox',
            'enterprise_qrcode',
            'online_customer_service_link',
            'icp_info',
            'icp_info_link',
            'public_security_network_preparation',
            'public_security_network_preparation_link',
            'telecom_appreciation',
            'copyright_info',
            'official_website_logo',
            'cloud_product_link',
            'dcim_product_link',
            'cron_due_renewal_first_swhitch',
            'cron_due_renewal_first_day',
            'first_navigation',
            'second_navigation',
            'home_enforce_safe_method',
            'recharge_notice',
            'recharge_money_notice_content',
            'recharge_pay_notice_content',
            'first_login_method',
            'first_password_login_method',
            'cart_instruction', 
            'cart_instruction_content',
            'cart_change_product',
            'clientarea_logo_url',
            'clientarea_logo_url_blank',
            'login_email_password',
            'login_phone_password',
            'first_login_type',
            'balance_notice_show',
            'login_register_redirect_show',
            'login_register_redirect_text',
            'login_register_redirect_url',
            'login_register_redirect_blank',
            'clientarea_theme',
            'home_theme',
            'donot_save_client_product_password',
        ];

        //$data = configuration($setting);
		$data = array_merge($lang,configuration($setting));
        // 余额提醒开关
        $data['balance_notice_show'] = (int)$data['balance_notice_show'];
        $data['prohibit_user_information_changes'] = array_filter(explode(',', $data['prohibit_user_information_changes']));
        foreach ($data['prohibit_user_information_changes'] as $key => $value) {
            if(!in_array($value, ['phone', 'email', 'password'])){
                $data['prohibit_user_information_changes'][$key] = (int)$value;
            }
        }
        // 前台强制安全选项
        $data['home_enforce_safe_method'] = !empty($data['home_enforce_safe_method']) ? explode(',', $data['home_enforce_safe_method']) : [];

        // 充值提示
        if($data['recharge_notice'] != 1){
            $data['recharge_money_notice_content'] = '';
            $data['recharge_pay_notice_content'] = '';
        }
        unset($data['recharge_notice']);

        // 购物车开关
        $data['cart_instruction'] = (int)$data['cart_instruction'];
        if($data['cart_instruction'] != 1){
            $data['cart_instruction_content'] = '';
        }
        $data['cart_change_product'] = (int)$data['cart_change_product'];
        $data['donot_save_client_product_password'] = (int)$data['donot_save_client_product_password'];

        $data['system_logo'] = config('idcsmart.system_logo_url') . $data['system_logo'];
        $account = $param['account']??'';

        $ConfigurationModel = new ConfigurationModel();
        $firstLoginTypeList = $ConfigurationModel->getFirstLoginTypeList();
        $firstLoginTypeList = array_column($firstLoginTypeList, 'name', 'value');
        $data['first_login_type'] = !empty($firstLoginTypeList[ $data['first_login_type'] ]) ? $data['first_login_type'] : 'account_login';

        // 获取意见反馈类型
        $FeedbackTypeModel = new FeedbackTypeModel();
        $feedbackType = $FeedbackTypeModel->feedbackTypeList();
        $data['feedback_type'] = $feedbackType['list'];

        // 获取友情链接
        $FriendlyLinkModel = new FriendlyLinkModel();
        $friendlyLink = $FriendlyLinkModel->friendlyLinkList();
        $data['friendly_link'] = $friendlyLink['list'];

        // 获取荣誉资质
        $HonorModel = new HonorModel();
        $honor = $HonorModel->honorList();
        $data['honor'] = $honor['list'];

        // 获取意见反馈类型
        $PartnerModel = new PartnerModel();
        $partner = $PartnerModel->partnerList();
        $data['partner'] = $partner['list'];

        # 登录3次失败
        if ($account){
            $ip = get_client_ip();
            $key = "password_login_times_{$account}_{$ip}";
            if (Cache::get($key)>3){
                $data = array_merge($data,['captcha_client_login_error_3_times'=>1]);
            }else{
                $data = array_merge($data,['captcha_client_login_error_3_times'=>0]);
            }
        }else{
            $data = array_merge($data,['captcha_client_login_error_3_times'=>0]);
        }

        // 获取三方登录方式
        $PluginModel = new PluginModel();
        $oauth = $PluginModel->oauthList();
        $data['oauth'] = $oauth['list'] ?? [];

        // wyh 20240410 新增 购买前必填用户自定义字段
        $data['custom_fields'] = [];
        $hookResults = hook('common_custom_fields');
        foreach ($hookResults as $hookResult){
            if (isset($hookResult['status']) && $hookResult['status']==200){
                $data['custom_fields'] = array_merge($data['custom_fields'],$hookResult['data']??[]);
            }
        }

        // 插件配置
        $data['plugin_configuration'] = [];
        $hookResults = hook('common_plugin_configuration');
        foreach ($hookResults as $hookResult){
            if (isset($hookResult['status']) && $hookResult['status']==200){
                $data['plugin_configuration'] = array_merge($data['plugin_configuration'],$hookResult['data']??[]);
            }
        }

        // 购物车主题应用多语言
        $data['first_navigation'] = multi_language_replace($data['first_navigation']);
        $data['second_navigation'] = multi_language_replace($data['second_navigation']);
        $data['website_name'] = multi_language_replace($data['website_name']);

        // 登录注册页面跳转处理
        $data['login_register_redirect_show'] = (int)$data['login_register_redirect_show'];
        $data['login_register_redirect_blank'] = (int)$data['login_register_redirect_blank'];
        $data['login_register_redirect_text'] = $data['login_register_redirect_text']??"";
        $data['login_register_redirect_url'] = $data['login_register_redirect_url']??"";

        // 会员中心主题相关
        $ThemeConfigModel = new ThemeConfigModel();
        $themeConfig = $ThemeConfigModel->where('theme', $data['home_theme'])->find();
        $data['clientarea_theme_display_one'] = $themeConfig['display_one']??'ticket';
        $data['clientarea_theme_display'] = $themeConfig['display']??'announcement';
        $data['clientarea_theme_display_time'] = $themeConfig['display_time']??1;
        $ThemeBannerModel = new ThemeBannerModel();
        $data['clientarea_theme_banner_list'] = $ThemeBannerModel->where('theme', $data['home_theme'])
            ->where('show',1)
            ->where('start_time','<=',time())
            ->where('end_time','>=',time())
            ->order('order','asc')
            ->select()
            ->toArray();

        // 判断是否专业版
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

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-6-20
     * @title 文件上传
     * @desc 文件上传
     * @url /console/v1/upload
     * @method POST
     * @author wyh
     * @version v1
     * @param resource file - desc:文件资源 validate:required
     * @param int move - desc:移动资源 0否 1是 仅支持图片 validate:optional
     * @return string save_name - desc:文件名
     * @return string data.image_base64 - desc:图片base64 文件为图片才返回
     * @return string data.image_url - desc:图片地址 文件为图片才返回
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
     * @url /console/v1/global_search
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围用户姓名公司邮箱手机号商品名称商品一级分组名称商品二级分组名称产品ID标识商品名称 validate:required
     * @return array hosts - desc:产品列表
     * @return int hosts[].id - desc:产品ID
     * @return string hosts[].name - desc:标识
     * @return string hosts[].product_name - desc:商品名称
     */
    public function globalSearch()
    {
        // 接收参数
        $param = $this->request->param();
        $keywords = $param['keywords'] ?? '';
        if(!empty($keywords)){
            $hosts = (new HostModel())->searchHost($keywords);
            $data = [
                'hosts' => $hosts['list'],
            ];
        }else{
            $data = [
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
     * 时间 2022-08-10
     * @title 获取前台导航
     * @desc 获取前台导航
     * @author theworld
     * @version v1
     * @url /console/v1/menu
     * @method GET
     * @return array menu - desc:菜单列表
     * @return int menu[].id - desc:菜单ID
     * @return string menu[].name - desc:名称
     * @return string menu[].url - desc:网址
     * @return string menu[].menu_type - desc:菜单类型 system系统 plugin插件 custom自定义 module模块 res_module上游模块 embedded内嵌
     * @return int menu[].second_reminder - desc:二次提醒 0否 1是
     * @return string menu[].icon - desc:图标
     * @return int menu[].parent_id - desc:父ID
     * @return int menu[].is_cross_module - desc:是否为跨模块列表
     * @return array menu[].select_field - desc:选择字段 area=区域 product_name=商品名称 billing_cycle=计费方式 is_auto_renew=自动续费 base_info=基础信息 ip=IP os=OS active_time=开通时间 due_time=到期时间 status=状态 notes=备注
     * @return array menu[].child - desc:子菜单
     * @return int menu[].child[].id - desc:菜单ID
     * @return string menu[].child[].name - desc:名称
     * @return string menu[].child[].url - desc:网址
     * @return int menu[].child[].second_reminder - desc:二次提醒 0否 1是
     * @return string menu[].child[].icon - desc:图标
     * @return int menu[].child[].parent_id - desc:父ID
     * @return int menu[].child[].is_cross_module - desc:是否为跨模块列表
     * @return array menu[].child[].select_field - desc:选择字段
     */
    public function homeMenu(){
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new MenuModel())->homeMenu()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 权限列表
     * @desc 权限列表
     * @author theworld
     * @version v1
     * @url /console/v1/auth
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
     * @return string list[].child[].module - desc:插件模块路径
     * @return string list[].child[].plugin - desc:插件标识名
     * @return string list[].child[].rules - desc:权限规则标题
     * @return array list[].child[].child - desc:权限子集
     * @return int list[].child[].child[].id - desc:权限ID
     * @return string list[].child[].child[].title - desc:权限标题
     * @return string list[].child[].child[].url - desc:地址
     * @return int list[].child[].child[].order - desc:排序
     * @return int list[].child[].child[].parent_id - desc:父级ID
     * @return string list[].child[].child[].module - desc:插件模块路径
     * @return string list[].child[].child[].plugin - desc:插件标识名
     * @return string list[].child[].child[].rules - desc:权限规则标题
     * @return string widget[].id - desc:挂件标识
     * @return string widget[].title - desc:挂件标题
     */
    public function authList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ClientareaAuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 提交意见反馈
     * @desc 提交意见反馈
     * @url /console/v1/feedback
     * @method POST
     * @author theworld
     * @version v1
     * @param int type - desc:类型 validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param array attachment - desc:附件 validate:optional
     * @param string contact - desc:联系方式 validate:optional
     */
    public function createFeedback()
    {
        // 接收参数
        $param = $this->request->param();

        $FeedbackValidate = new FeedbackValidate();
        // 参数验证
        if (!$FeedbackValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FeedbackModel = new FeedbackModel();
        
        // 提交意见反馈
        $result = $FeedbackModel->createFeedback($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 提交方案咨询
     * @desc 提交方案咨询
     * @url /console/v1/consult
     * @method POST
     * @author theworld
     * @version v1
     * @param string contact - desc:联系人 validate:required
     * @param string company - desc:公司名称 validate:optional
     * @param string phone - desc:手机号码 手机号码和邮箱二选一必填 validate:optional
     * @param string email - desc:联系邮箱 手机号码和邮箱二选一必填 validate:optional
     * @param string matter - desc:咨询产品 validate:required
     */
    public function createConsult()
    {
        // 接收参数
        $param = $this->request->param();

        $ConsultValidate = new ConsultValidate();
        // 参数验证
        if (!$ConsultValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ConsultModel = new ConsultModel();
        
        // 提交方案咨询
        $result = $ConsultModel->createConsult($param);

        return json($result);
    }

    /**
     * 时间 2024-01-26
     * @title 获取文件资源(支持预览或者下载)
     * @desc 获取文件资源(支持预览或者下载)
     * @url /console/v1/resource
     * @method GET
     * @author wyh
     * @version v1
     * @param string fid - desc:文件唯一ID validate:required
     * @param string rand_str - desc:随机字符串 validate:required
     * @param string sign - desc:签名 validate:required
     * @param int expires - desc:过期时间 validate:required
     * @return string - desc:文件内容
     */
    public function resource()
    {
        $param = $this->request->param();

        if (!isset($param['fid']) || empty($param['fid'])
            || !isset($param['rand_str']) || empty($param['rand_str'])
            || !isset($param['sign']) || empty($param['sign'])
            || !isset($param['expires']) || empty($param['expires'])){
            return json(['status'=>400,'msg'=>lang("param_error")]);
        }

        if (!validate_signature(['fid'=>$param['fid'],'sign'=>$param['sign']],AUTHCODE.$param['expires'],$param['rand_str'])){
            return json(['status'=>400,'msg'=>lang("resource_sign_error")]);
        }

        if (time()>$param['expires'] || empty($cache = idcsmart_cache('file_tmp_url_timeout_'.$param['fid']))){
            return json(['status'=>400,'msg'=>lang("resource_expired")]);
        }

        $cacheArray = json_decode($cache,true);

        $filePath = $cacheArray['file_path'] . $cacheArray['file_name'];

        if (!file_exists($filePath)){
            return json(['status'=>400,'msg'=>lang("resource_not_exist")]);
        }

        // 预览
        if (isset($cacheArray['action']) && $cacheArray['action']=='preview'){
            // 设置HTTP头部信息
            $imageMimeType = mime_content_type($filePath);

            //header('Content-Type: application/octet-stream');
            //header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Type: '.$imageMimeType);
            // 只展示，不下载
            header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));

            // 输出文件内容
            readfile($filePath);

            // 删除已下载的文件
            // unlink($filePath);

            // 防止thinkPHP后续操作，导致Content-Type设置不生效
            die;
        }else{ // 下载
            return download($filePath,$cacheArray['file_name']);
        }
    }


    /**
     * 时间 2024-01-02
     * @title 更新子商品价格
     * @desc 更新子商品价格
     * @author wyh
     * @version v1
     * @url /console/v1/update_son_host_base_price
     * @method  GET
     * @param string module - 模块
     * @param int id - 起始产品ID
     */
//    public function updateSonHostBasePrice(){
//        $param = $this->request->param();
//        $module = $param['module']??"idcsmart_common_dcim";
//        $id = $param['id']??0;
//        if (in_array($module,['idcsmart_common_dcim','idcsmart_common_business','idcsmart_common_cloud','idcsmart_common_finance'])){
//            $HostModel = new \app\common\model\HostModel();
//            $hosts = $HostModel->where('id','>',$id)->select();
//            $ProductModel = new \app\common\model\ProductModel();
//            if ($module=="idcsmart_common_dcim"){
//                // dcim
//                $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
//            }elseif ($module=="idcsmart_common_business"){
//                $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
//            }elseif ($module=="idcsmart_common_cloud"){
//                $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
//            }elseif ($module=="idcsmart_common_finance"){
//                $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
//            }
//
//            foreach ($hosts as $host){
//                $productId = $host['product_id'];
//                // 1、魔方DCIM
//                $product = $ProductModel->alias('p')
//                    ->field('p.id,s.module,ss.module as module2')
//                    ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\''. $module .'\'')
//                    ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
//                    ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\''. $module .'\'')
//                    ->where('p.id',$productId)
//                    ->find();
//                if (!empty($product) && ($product['module']==$module || $product['module2']==$module)){
//                    // 1、更新产品价格
//                    $this->updateHostBasePrice($host['id'],0,$module);
//
//                    $sonHostId = $IdcsmartCommonSonHost->where('host_id',$host['id'])->value('son_host_id');
//                    // 2、存在子产品
//                    if (!empty($sonHostId)){
//                        $this->updateHostBasePrice($sonHostId,$host['id'],$module);
//                    }
//                }
//            }
//        }
//        return json([
//            'status' => 200,
//            'msg' => lang('success_message')
//        ]);
//    }
//
//    private function updateHostBasePrice($sonHostId,$hostId,$module)
//    {
//        $CtyunOss = new \oss\ctyun_oss\CtyunOss();
//        $result = $CtyunOss->CtyunOssUpload([]);
//        var_dump($result);die;
//
//        if ($module=="idcsmart_common_dcim"){
//            // dcim
//            $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
//            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_dcim\model\IdcsmartCommonHostConfigoptionModel();
//            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_dcim\model\IdcsmartCommonProductConfigoptionModel();
//            $IdcsmartCommonLogic = new \server\idcsmart_common_dcim\logic\IdcsmartCommonLogic();
//            $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common_dcim\model\IdcsmartCommonCustomCycleModel();
//        }elseif ($module=="idcsmart_common_business"){
//            $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
//            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_business\model\IdcsmartCommonHostConfigoptionModel();
//            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_business\model\IdcsmartCommonProductConfigoptionModel();
//            $IdcsmartCommonLogic = new \server\idcsmart_common_business\logic\IdcsmartCommonLogic();
//            $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common_business\model\IdcsmartCommonCustomCycleModel();
//        }elseif ($module=="idcsmart_common_cloud"){
//            $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
//            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_cloud\model\IdcsmartCommonHostConfigoptionModel();
//            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_cloud\model\IdcsmartCommonProductConfigoptionModel();
//            $IdcsmartCommonLogic = new \server\idcsmart_common_cloud\logic\IdcsmartCommonLogic();
//            $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common_cloud\model\IdcsmartCommonCustomCycleModel();
//        }elseif ($module=="idcsmart_common_finance"){
//            $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
//            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common_finance\model\IdcsmartCommonHostConfigoptionModel();
//            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common_finance\model\IdcsmartCommonProductConfigoptionModel();
//            $IdcsmartCommonLogic = new \server\idcsmart_common_finance\logic\IdcsmartCommonLogic();
//            $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common_finance\model\IdcsmartCommonCustomCycleModel();
//        }
//
//        $HostModel = new \app\common\model\HostModel();
//        $sonHost = $HostModel->find($sonHostId);
//        if (empty($sonHost)){
//            return;
//        }
//        $sonHostConfigoptions = $IdcsmartCommonHostConfigoptionModel->where('host_id',$sonHostId)->select()->toArray();
//
//        $param = [];
//
//        $configoptionsParam = [];
//        foreach ($sonHostConfigoptions as $sonHostConfigoption){
//            $configoption = $IdcsmartCommonProductConfigoptionModel->where('id',$sonHostConfigoption['configoption_id'])->find();
//            // 数量数组
//            if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
//                $configoptionsParam[$configoption['id']][] = $sonHostConfigoption['qty'];
//            }
//            // 多选数组
//            elseif ($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
//                $configoptionsParam[$configoption['id']][] = $sonHostConfigoption['configoption_sub_id'];
//            }
//            // 其他
//            else{
//                $configoptionsParam[$configoption['id']] = $sonHostConfigoption['configoption_sub_id'];
//            }
//        }
//
//        // 免费不处理
//        if ($sonHost['billing_cycle']=='free'){
//            return;
//        }
//
//        if ($sonHost['billing_cycle']=='onetime'){
//            $cycle = 'onetime';
//        }else{
//            $billingCycleTime = $sonHost['billing_cycle_time'];
//            // 获取子商品所有自定义周期
//            if ($module=='idcsmart_common_dcim'){
//                $pricingTable = 'module_idcsmart_common_dcim_custom_cycle_pricing';
//            }elseif ($module=='idcsmart_common_business'){
//                $pricingTable = 'module_idcsmart_common_business_custom_cycle_pricing';
//            }elseif ($module=='idcsmart_common_cloud'){
//                $pricingTable = 'module_idcsmart_common_cloud_custom_cycle_pricing';
//            }elseif ($module=='idcsmart_common_finance'){
//                $pricingTable = 'module_idcsmart_common_finance_custom_cycle_pricing';
//            }
//            $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
//                ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
//                ->leftJoin($pricingTable . ' ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
//                ->where('cc.product_id',$sonHost['product_id'])
//                ->where('ccp.rel_id',$sonHost['product_id'])
//                ->where('ccp.amount','>=',0) # 可显示出得周期
//                ->select()
//                ->toArray();
//            // 获取子产品当前周期
//            $cycle = 0;
//            foreach ($customCycles as $customCycle){
//                $cycleTime = $IdcsmartCommonLogic->customCycleTime($customCycle['cycle_time'],$customCycle['cycle_unit'],0);
//                if ($cycleTime==$billingCycleTime || $customCycle['name']==$sonHost['billing_cycle_name']){
//                    $cycle = $customCycle['id'];
//                    break;
//                }
//            }
//        }
//
//        // 构造计算价格参数
//        $param['configoption'] = $configoptionsParam;
//        $param['product_id'] = $sonHost['product_id'];
//        $param['config_options']['host_id'] = $hostId;
//        $param['cycle'] = $cycle;
//        $param['orgin'] = 1; // 取原价
//        var_dump("产品ID：{$hostId}；子产品ID：".$sonHostId."\n");
//        // 错误产品更新过滤
//        if (in_array($sonHostId,[])){
//            return;
//        }
//        $result = $IdcsmartCommonLogic->cartCalculatePrice($param);
//        if ($result['status']==200){
//            // 子产品原价
//            $basePrice = $result['data']['base_price']??$result['data']['price'];
//            // 更新子产品原价
//            $sonHost->save(['base_price'=>$basePrice]);
//        }
//    }


    /**
     * 时间 2025-11-14
     * @title 修改语言
     * @desc 修改语言
     * @author theworld
     * @version v1
     * @url /console/v1/language
     * @method PUT
     * @param string language - desc:语言 validate:required
     */
    public function updateLanguage()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改语言
        $result = $ClientModel->updateLanguage($param);

        return json($result);
    }
}
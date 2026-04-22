<?php
namespace app\admin\controller;

use app\common\model\ConfigurationModel;
use app\common\model\ThemeConfigModel;
use app\common\model\ThemeBannerModel;
use app\admin\validate\ConfigurationValidate;
use think\captcha\Captcha;

/**
 * @title 系统设置
 * @desc 系统设置
 * @use app\admin\controller\ConfigurationController
 */
class ConfigurationController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new ConfigurationValidate();
    }
    /**
     * 时间 2022-5-10
     * @title 获取系统设置
     * @desc 获取系统设置
     * @url /admin/v1/configuration/system
     * @method GET
     * @author xiong
     * @version v1
     * @return string lang_admin - desc:后台默认语言
     * @return int lang_home_open - desc:前台多语言开关 1开启 0关闭
     * @return string lang_home - desc:前台默认语言
     * @return int maintenance_mode - desc:维护模式开关 1开启 0关闭
     * @return string maintenance_mode_message - desc:维护模式内容
     * @return string website_name - desc:网站名称
     * @return string website_url - desc:网站域名地址
     * @return string terms_service_url - desc:服务条款地址
     * @return string terms_privacy_url - desc:隐私条款地址
     * @return string system_logo - desc:系统LOGO
     * @return string admin_logo - desc:后台LOGO
     * @return int client_start_id_value - desc:用户注册开始ID
     * @return int order_start_id_value - desc:用户订单开始ID
     * @return string clientarea_url - desc:会员中心地址
     * @return string www_url - desc:官网地址
     * @return string tab_logo - desc:标签页LOGO
     * @return int home_show_deleted_host - desc:前台是否展示已删除产品 1是 0否
     * @return array prohibit_user_information_changes - desc:禁止用户信息变更
     * @return array user_information_fields - desc:用户信息字段
     * @return mixed user_information_fields[].id - desc:用户信息字段ID
     * @return string user_information_fields[].name - desc:用户信息字段名称
     * @return string clientarea_logo_url - desc:会员中心LOGO跳转地址
     * @return int clientarea_logo_url_blank - desc:会员中心LOGO跳转是否打开新页面 1是 0否
     * @return object customfield - desc:自定义参数
     * @return string ip_white_list - desc:IP白名单 回车分隔
     * @return int global_list_limit - desc:全局列表展示条数
     * @return int donot_save_client_product_password - desc:不保存用户产品密码 1是 0否
     * @return int edition - desc:版本标识 1专业版 0开源版
     */
    public function systemList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取系统设置
		$data=$ConfigurationModel->systemList();
        $data['customfield'] = (object)$data['customfield'];
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存系统设置
     * @desc 保存系统设置
     * @url /admin/v1/configuration/system
     * @method PUT
     * @author xiong
     * @version v1
     * @param string lang_admin - desc:后台默认语言 validate:optional
     * @param int lang_home_open - desc:前台多语言开关 1开启 0关闭 validate:optional
     * @param string lang_home - desc:前台默认语言 validate:optional
     * @param int maintenance_mode - desc:维护模式开关 1开启 0关闭 validate:optional
     * @param string maintenance_mode_message - desc:维护模式内容 validate:optional
     * @param string website_name - desc:网站名称 validate:optional
     * @param string website_url - desc:网站域名地址 validate:optional
     * @param string terms_service_url - desc:服务条款地址 validate:optional
     * @param string terms_privacy_url - desc:隐私条款地址 validate:optional
     * @param string system_logo - desc:系统LOGO validate:optional
     * @param string admin_logo - desc:后台LOGO 仅专业版可修改 validate:optional
     * @param int client_start_id_value - desc:用户注册开始ID validate:optional
     * @param int order_start_id_value - desc:用户订单开始ID validate:optional
     * @param string clientarea_url - desc:会员中心地址 validate:optional
     * @param string www_url - desc:官网地址 validate:optional
     * @param string tab_logo - desc:标签页LOGO validate:optional
     * @param int home_show_deleted_host - desc:前台是否展示已删除产品 1是 0否 validate:optional
     * @param array prohibit_user_information_changes - desc:禁止用户信息变更 validate:optional
     * @param string clientarea_logo_url - desc:会员中心LOGO跳转地址 validate:optional
     * @param int clientarea_logo_url_blank - desc:会员中心LOGO跳转是否打开新页面 1是 0否 validate:optional
     * @param object customfield - desc:自定义参数 validate:optional
     * @param string ip_white_list - desc:IP白名单 回车分隔 validate:optional
     * @param int global_list_limit - desc:全局列表展示条数 validate:optional
     * @param int donot_save_client_product_password - desc:不保存用户产品密码 1是 0否 validate:optional
     */
    public function systemUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('system_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存系统设置
		$result = $ConfigurationModel->systemUpdate($param);   
		
        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 获取登录设置
     * @desc 获取登录设置
     * @url /admin/v1/configuration/login
     * @method GET
     * @author xiong
     * @version v1
     * @return int register_email - desc:邮箱注册开关 1开启 0关闭
     * @return int register_phone - desc:是否允许手机号注册 1开启 0关闭
     * @return int login_phone_verify - desc:是否支持手机验证码登录 1开启 0关闭
     * @return int home_login_check_ip - desc:前台登录检查IP 1开启 0关闭
     * @return int admin_login_check_ip - desc:后台登录检查IP 1开启 0关闭
     * @return int code_client_email_register - desc:邮箱注册是否需要验证码 1开启 0关闭
     * @return int code_client_phone_register - desc:手机注册是否需要验证码 1开启 0关闭
     * @return int limit_email_suffix - desc:是否限制邮箱后缀 1开启 0关闭
     * @return string email_suffix - desc:邮箱后缀
     * @return int home_login_check_common_ip - desc:前台是否检测常用登录IP 1开启 0关闭
     * @return array home_login_ip_exception_verify - desc:用户异常登录验证方式 operate_password操作密码 email_code邮箱验证码 phone_code手机验证码 certification实名校验
     * @return array home_enforce_safe_method - desc:前台强制安全选项 phone手机 email邮箱 operate_password操作密码 certification实名认证 oauth三方登录扫码
     * @return array admin_enforce_safe_method - desc:后台强制安全选项 operate_password操作密码
     * @return int admin_allow_remember_account - desc:后台是否允许记住账号 1开启 0关闭
     * @return int login_email_password - desc:是否开启邮箱密码登录 1开启 0关闭
     * @return array admin_enforce_safe_method_scene - desc:后台强制安全选项场景 all全部 client_delete用户删除 update_client_status用户停启用 host_operate产品相关操作 order_delete订单删除 clear_order_recycle清空回收站 plugin_uninstall_disable插件卸载禁用
     * @return int admin_second_verify - desc:二次验证 1开启 0关闭
     * @return string admin_second_verify_method_default - desc:首选二次验证方式 sms短信 email邮件 totp
     * @return int prohibit_admin_bind_phone - desc:禁止后台用户自助绑定手机号 1是 0否
     * @return int prohibit_admin_bind_email - desc:禁止后台用户自助绑定邮箱 1是 0否
     * @return int admin_password_or_verify_code_retry_times - desc:密码或验证码重试次数
     * @return int admin_frozen_time - desc:冻结时间 单位分钟
     * @return int admin_login_expire_time - desc:登录有效期 单位分钟
     * @return string first_login_type - desc:首选登录方式
     * @return array first_login_type_list - desc:首选登录方式列表
     * @return string first_login_type_list[].value - desc:首选登录方式标识
     * @return string first_login_type_list[].name - desc:首选登录方式名称
     * @return int login_phone_password - desc:是否开启手机密码登录 1开启 0关闭
     * @return int home_login_expire_time - desc:前台登录有效期 单位分钟
     * @return string admin_login_ip_whitelist - desc:后台登录IP白名单 换行分隔 空表示不限制
     * @return int login_register_redirect_show - desc:登录注册页面展示跳转 1是 0否
     * @return string login_register_redirect_text - desc:按钮文案
     * @return string login_register_redirect_url - desc:跳转地址
     * @return int login_register_redirect_blank - desc:跳转按钮是否新窗口打开 1是 0否
     * @return int admin_login_password_encrypt - desc:后台登录密码是否加密传输 1是 0否
     * @return string exception_login_certification_plugin - desc:异常登录实名认证插件
     */
    public function loginList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取登录设置
		$data=$ConfigurationModel->loginList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存登录设置
     * @desc 保存登录设置
     * @url /admin/v1/configuration/login
     * @method PUT
     * @author xiong
     * @version v1
     * @param int register_email - desc:邮箱注册开关 1开启 0关闭 validate:optional
     * @param int register_phone - desc:是否允许手机号注册或密码登录 1开启 0关闭 validate:optional
     * @param int login_phone_verify - desc:是否支持手机验证码登录 1开启 0关闭 validate:optional
     * @param int home_login_check_ip - desc:前台登录检查IP 1开启 0关闭 validate:optional
     * @param int admin_login_check_ip - desc:后台登录检查IP 1开启 0关闭 validate:optional
     * @param int code_client_email_register - desc:邮箱注册是否需要验证码 1开启 0关闭 validate:optional
     * @param int code_client_phone_register - desc:手机注册是否需要验证码 1开启 0关闭 validate:optional
     * @param int limit_email_suffix - desc:是否限制邮箱后缀 1开启 0关闭 validate:optional
     * @param string email_suffix - desc:邮箱后缀 validate:optional
     * @param int home_login_check_common_ip - desc:前台是否检测常用登录IP 1开启 0关闭 validate:optional
     * @param array home_login_ip_exception_verify - desc:用户异常登录验证方式 operate_password操作密码 email_code邮箱验证码 phone_code手机验证码 certification实名校验 validate:optional
     * @param array home_enforce_safe_method - desc:前台强制安全选项 phone手机 email邮箱 operate_password操作密码 certification实名认证 oauth三方登录扫码 validate:optional
     * @param array admin_enforce_safe_method - desc:后台强制安全选项 operate_password操作密码 validate:optional
     * @param int admin_allow_remember_account - desc:后台是否允许记住账号 1开启 0关闭 validate:optional
     * @param array admin_enforce_safe_method_scene - desc:后台强制安全选项场景 all全部 client_delete用户删除 update_client_status用户停启用 host_operate产品相关操作 order_delete订单删除 clear_order_recycle清空回收站 plugin_uninstall_disable插件卸载禁用 validate:optional
     * @param string first_login_method - desc:账户凭证首选登录方式 code验证码 password密码 validate:optional
     * @param string first_password_login_method - desc:密码登录首选 phone手机 email邮箱 validate:optional
     * @param int login_email_password - desc:是否开启邮箱登录 1开启 0关闭 validate:optional
     * @param int admin_second_verify - desc:二次验证 1开启 0关闭 validate:optional
     * @param string admin_second_verify_method_default - desc:首选二次验证方式 sms短信 email邮件 totp validate:optional
     * @param int prohibit_admin_bind_phone - desc:禁止后台用户自助绑定手机号 1是 0否 validate:optional
     * @param int prohibit_admin_bind_email - desc:禁止后台用户自助绑定邮箱 1是 0否 validate:optional
     * @param int admin_password_or_verify_code_retry_times - desc:密码或验证码重试次数 validate:optional
     * @param int admin_frozen_time - desc:冻结时间 单位分钟 validate:optional
     * @param int admin_login_expire_time - desc:登录有效期 单位分钟 validate:optional
     * @param string first_login_type - desc:首选登录方式 validate:optional
     * @param int login_phone_password - desc:是否开启手机密码登录 1开启 0关闭 validate:optional
     * @param int home_login_expire_time - desc:前台登录有效期 单位分钟 validate:optional
     * @param string admin_login_ip_whitelist - desc:后台登录IP白名单 换行分隔 空表示不限制 validate:optional
     * @param int login_register_redirect_show - desc:登录注册页面展示跳转 1是 0否 validate:optional
     * @param string login_register_redirect_text - desc:按钮文案 validate:optional
     * @param string login_register_redirect_url - desc:跳转地址 validate:optional
     * @param int login_register_redirect_blank - desc:跳转按钮是否新窗口打开 1是 0否 validate:optional
     * @param int admin_login_password_encrypt - desc:后台登录密码是否加密传输 1是 0否 validate:optional
     * @param string exception_login_certification_plugin - desc:异常登录实名认证插件 validate:optional
     */
    public function loginUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('login_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存系统设置
		$result = $ConfigurationModel->loginUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取图形验证码预览
     * @desc 获取图形验证码预览
     * @url /admin/v1/configuration/security/captcha
     * @method GET
     * @author xiong
     * @version v1
     * @param int captcha_width - desc:图形验证码宽度 validate:required
     * @param int captcha_height - desc:图形验证码高度 validate:required
     * @param int captcha_length - desc:图形验证码字符长度 validate:required
     * @return string captcha - desc:图形验证码图片
     */
    public function securityCaptcha()
    {
		//接收参数
		$param = $this->request->param();
		$config = [
            'imageW' => $param['captcha_width'],
            'imageH' => $param['captcha_height'],
            'length' => $param['captcha_length'],
            'codeSet' => '1234567890',
        ];
        $Captcha = new Captcha(app('config'),app('session'));
        $response = $Captcha->create($config);
		$result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
				'captcha' => 'data:image/png;base64,' . base64_encode($response->getData()),
			]
        ];
        return json($result);
    }  
    /**
     * 时间 2022-5-10
     * @title 获取验证码设置
     * @desc 获取验证码设置
     * @url /admin/v1/configuration/security
     * @method GET
     * @author xiong
     * @version v1
     * @return int captcha_client_register - desc:客户注册图形验证码开关 1开启 0关闭
     * @return int captcha_client_login - desc:客户登录图形验证码开关 1开启 0关闭
     * @return int captcha_client_login_error - desc:客户登录失败图形验证码开关 1开启 0关闭
     * @return int captcha_admin_login - desc:管理员登录图形验证码开关 1开启 0关闭
     * @return int captcha_admin_login_error - desc:管理员登录失败图形验证码开关 1开启 0关闭
     * @return string captcha_plugin - desc:验证码插件 从/admin/v1/captcha_list接口获取
     * @return int code_client_email_register - desc:邮箱注册数字验证码开关 1开启 0关闭
     * @return int captcha_client_verify - desc:验证手机或邮箱图形验证码开关 1开启 0关闭
     * @return int captcha_client_update - desc:修改手机或邮箱图形验证码开关 1开启 0关闭
     * @return int captcha_client_password_reset - desc:重置密码图形验证码开关 1开启 0关闭
     * @return int captcha_client_oauth - desc:三方登录图形验证码开关 1开启 0关闭
     * @return int captcha_client_security_verify - desc:安全校验图形验证码开关 1开启 0关闭
     */
    public function securityList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取验证码设置
		$data=$ConfigurationModel->securityList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存验证码设置
     * @desc 保存验证码设置
     * @url /admin/v1/configuration/security
     * @method PUT
     * @author xiong
     * @version v1
     * @param int captcha_client_register - desc:客户注册图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_login - desc:客户登录图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_login_error - desc:客户登录失败图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_admin_login - desc:管理员登录图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_admin_login_error - desc:管理员登录失败图形验证码开关 1开启 0关闭 validate:optional
     * @param string captcha_plugin - desc:验证码插件 从/admin/v1/captcha_list接口获取 validate:optional
     * @param int code_client_email_register - desc:邮箱注册数字验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_verify - desc:验证手机或邮箱图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_update - desc:修改手机或邮箱图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_password_reset - desc:重置密码图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_oauth - desc:三方登录图形验证码开关 1开启 0关闭 validate:optional
     * @param int captcha_client_security_verify - desc:安全校验图形验证码开关 1开启 0关闭 validate:optional
     */
    public function securityUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('security_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->securityUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取货币设置
     * @desc 获取货币设置
     * @url /admin/v1/configuration/currency
     * @method GET
     * @author xiong
     * @version v1
     * @return string currency_code - desc:货币代码
     * @return string currency_prefix - desc:货币符号
     * @return string currency_suffix - desc:货币后缀
     * @return int recharge_open - desc:启用充值 1开启 0关闭
     * @return int recharge_min - desc:单笔最小金额
     * @return int recharge_notice - desc:充值提示开关 1开启 0关闭
     * @return string recharge_money_notice_content - desc:充值金额提示内容
     * @return string recharge_pay_notice_content - desc:充值支付提示内容
     * @return int recharge_order_support_refund - desc:充值订单是否支持退款 0否 1是
     */
    public function currencyList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取验证码设置
		$data=$ConfigurationModel->currencyList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存货币设置
     * @desc 保存货币设置
     * @url /admin/v1/configuration/currency
     * @method PUT
     * @author xiong
     * @version v1
     * @param string currency_code - desc:货币代码 validate:optional
     * @param string currency_prefix - desc:货币符号 validate:optional
     * @param string currency_suffix - desc:货币后缀 validate:optional
     * @param int recharge_open - desc:启用充值 1开启 0关闭 validate:optional
     * @param int recharge_min - desc:单笔最小金额 validate:optional
     * @param int recharge_notice - desc:充值提示开关 1开启 0关闭 validate:optional
     * @param string recharge_money_notice_content - desc:充值金额提示内容 validate:optional
     * @param string recharge_pay_notice_content - desc:充值支付提示内容 validate:optional
     * @param int recharge_order_support_refund - desc:充值订单是否支持退款 0否 1是 validate:optional
     */
    public function currencyUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('currency_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->currencyUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取自动化设置
     * @desc 获取自动化设置
     * @url /admin/v1/configuration/cron
     * @method GET
     * @author xiong
     * @version v1
     * @return string cron_shell - desc:自动化脚本
     * @return string cron_status - desc:自动化状态 正常返回success 不正常返回error
     * @return int cron_due_suspend_swhitch - desc:产品到期暂停开关 1开启 0关闭
     * @return int cron_due_suspend_day - desc:产品到期X天后暂停
     * @return int cron_due_unsuspend_swhitch - desc:财务原因产品暂停后付款自动解封开关 1开启 0关闭
     * @return int cron_due_terminate_swhitch - desc:产品到期删除开关 1开启 0关闭
     * @return int cron_due_terminate_day - desc:产品到期X天后删除
     * @return int cron_due_renewal_first_swhitch - desc:续费第一次提醒开关 1开启 0关闭
     * @return int cron_due_renewal_first_day - desc:续费X天后到期第一次提醒
     * @return int cron_due_renewal_second_swhitch - desc:续费第二次提醒开关 1开启 0关闭
     * @return int cron_due_renewal_second_day - desc:续费X天后到期第二次提醒
     * @return int cron_overdue_first_swhitch - desc:产品逾期第一次提醒开关 1开启 0关闭
     * @return int cron_overdue_first_day - desc:产品逾期X天后第一次提醒
     * @return int cron_overdue_second_swhitch - desc:产品逾期第二次提醒开关 1开启 0关闭
     * @return int cron_overdue_second_day - desc:产品逾期X天后第二次提醒
     * @return int cron_overdue_third_swhitch - desc:产品逾期第三次提醒开关 1开启 0关闭
     * @return int cron_overdue_third_day - desc:产品逾期X天后第三次提醒
     * @return int cron_ticket_close_swhitch - desc:自动关闭工单开关 1开启 0关闭
     * @return int cron_ticket_close_day - desc:已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - desc:推介月报开关 1开启 0关闭
     * @return int cron_order_overdue_swhitch - desc:订单未付款通知开关 1开启 0关闭
     * @return int cron_order_overdue_day - desc:订单未付款X天后通知
     * @return string cron_task_shell - desc:任务队列命令
     * @return string cron_task_status - desc:任务队列最新状态 success成功 error失败
     * @return int cron_order_unpaid_delete_swhitch - desc:订单自动删除开关 1开启 0关闭
     * @return int cron_order_unpaid_delete_day - desc:订单未付款X天后自动删除
     * @return int cron_day_start_time - desc:定时任务开始时间
     * @return int cron_system_log_delete_swhitch - desc:系统日志自动删除开关 1开启 0关闭
     * @return int cron_system_log_delete_day - desc:系统日志创建X天后自动删除
     * @return int cron_sms_log_delete_swhitch - desc:短信日志自动删除开关 1开启 0关闭
     * @return int cron_sms_log_delete_day - desc:短信日志创建X天后自动删除
     * @return int cron_email_log_delete_swhitch - desc:邮件日志自动删除开关 1开启 0关闭
     * @return int cron_email_log_delete_day - desc:邮件日志创建X天后自动删除
     * @return int task_fail_retry_open - desc:任务是否重试
     * @return int task_fail_retry_times - desc:任务重试次数
     * @return string cron_on_demand_cron_status - desc:按需出账状态 正常返回success 不正常返回error
     * @return string cron_on_demand_cron_shell - desc:按需出账任务命令
     * @return int notice_independent_task_enabled - desc:独立通知任务队列开关 1开启 0关闭
     * @return string cron_task_notice_status - desc:独立通知任务队列状态 正常返回success 不正常返回error 仅开关开启时返回
     * @return string cron_task_notice_shell - desc:独立通知任务队列命令 仅开关开启时返回
     */
    public function cronList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取自动化设置
		$data=$ConfigurationModel->cronList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存自动化设置
     * @desc 保存自动化设置
     * @url /admin/v1/configuration/cron
     * @method PUT
     * @author xiong
     * @version v1
     * @param int cron_due_suspend_swhitch - desc:产品到期暂停开关 1开启 0关闭 validate:required
     * @param int cron_due_suspend_day - desc:产品到期X天后暂停 validate:required
     * @param int cron_due_unsuspend_swhitch - desc:财务原因产品暂停后付款自动解封开关 1开启 0关闭 validate:required
     * @param int cron_due_terminate_swhitch - desc:产品到期删除开关 1开启 0关闭 validate:required
     * @param int cron_due_terminate_day - desc:产品到期X天后删除 validate:required
     * @param int cron_due_renewal_first_swhitch - desc:续费第一次提醒开关 1开启 0关闭 validate:required
     * @param int cron_due_renewal_first_day - desc:续费X天后到期第一次提醒 validate:required
     * @param int cron_due_renewal_second_swhitch - desc:续费第二次提醒开关 1开启 0关闭 validate:required
     * @param int cron_due_renewal_second_day - desc:续费X天后到期第二次提醒 validate:required
     * @param int cron_overdue_first_swhitch - desc:产品逾期第一次提醒开关 1开启 0关闭 validate:required
     * @param int cron_overdue_first_day - desc:产品逾期X天后第一次提醒 validate:required
     * @param int cron_overdue_second_swhitch - desc:产品逾期第二次提醒开关 1开启 0关闭 validate:required
     * @param int cron_overdue_second_day - desc:产品逾期X天后第二次提醒 validate:required
     * @param int cron_overdue_third_swhitch - desc:产品逾期第三次提醒开关 1开启 0关闭 validate:required
     * @param int cron_overdue_third_day - desc:产品逾期X天后第三次提醒 validate:required
     * @param int cron_ticket_close_swhitch - desc:自动关闭工单开关 1开启 0关闭 validate:required
     * @param int cron_ticket_close_day - desc:已回复状态的工单超过x小时后关闭 validate:required
     * @param int cron_aff_swhitch - desc:推介月报开关 1开启 0关闭 validate:required
     * @param int cron_order_overdue_swhitch - desc:订单未付款通知开关 1开启 0关闭 validate:required
     * @param int cron_order_overdue_day - desc:订单未付款X天后通知 validate:required
     * @param int cron_order_unpaid_delete_swhitch - desc:订单自动删除开关 1开启 0关闭 validate:required
     * @param int cron_order_unpaid_delete_day - desc:订单未付款X天后自动删除 validate:required
     * @param int cron_day_start_time - desc:定时任务开始时间 validate:required
     * @param int cron_system_log_delete_swhitch - desc:系统日志自动删除开关 1开启 0关闭 validate:required
     * @param int cron_system_log_delete_day - desc:系统日志创建X天后自动删除 validate:required
     * @param int cron_sms_log_delete_swhitch - desc:短信日志自动删除开关 1开启 0关闭 validate:required
     * @param int cron_sms_log_delete_day - desc:短信日志创建X天后自动删除 validate:required
     * @param int cron_email_log_delete_swhitch - desc:邮件日志自动删除开关 1开启 0关闭 validate:required
     * @param int cron_email_log_delete_day - desc:邮件日志创建X天后自动删除 validate:required
     * @param int notice_independent_task_enabled - desc:独立通知任务队列开关 1开启 0关闭 validate:required
     */
    public function cronUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('cron_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->cronUpdate($param);   
		
        return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 获取主题设置
     * @desc 获取主题设置
     * @url /admin/v1/configuration/theme
     * @method GET
     * @author theworld
     * @version v1
     * @return string admin_theme - desc:后台主题
     * @return string clientarea_theme - desc:会员中心主题
     * @return string cart_theme - desc:购物车主题
     * @return string cart_theme_mobile - desc:购物车主题手机端
     * @return int cart_instruction - desc:购物车说明开关 0关闭 1开启
     * @return string cart_instruction_content - desc:购物车说明内容
     * @return int cart_change_product - desc:购物车切换商品开关 0关闭 1开启
     * @return int web_switch - desc:官网开关 0关闭 1开启
     * @return string web_theme - desc:官网主题
     * @return array admin_theme_list - desc:后台主题列表
     * @return string first_navigation - desc:一级导航名称
     * @return string second_navigation - desc:二级导航名称
     * @return string admin_theme_list[].name - desc:名称
     * @return string admin_theme_list[].img - desc:图片
     * @return array clientarea_theme_list - desc:会员中心主题列表
     * @return string clientarea_theme_list[].name - desc:名称
     * @return string clientarea_theme_list[].img - desc:图片
     * @return array web_theme_list - desc:官网主题列表
     * @return string web_theme_list[].name - desc:名称
     * @return string web_theme_list[].img - desc:图片
     * @return string cart_theme_mobile_list[].name - desc:名称
     * @return string cart_theme_mobile_list[].img - desc:图片
     * @return string home_theme - desc:首页PC主题
     * @return string home_theme_mobile - desc:首页手机主题
     * @return array home_theme_list - desc:首页PC主题列表
     * @return string home_theme_list[].name - desc:名称
     * @return string home_theme_list[].img - desc:图片
     * @return array home_theme_mobile_list - desc:首页手机主题列表
     * @return string home_theme_mobile_list[].name - desc:名称
     * @return string home_theme_mobile_list[].img - desc:图片
     * @return array home_host_theme - desc:会员中心产品详情PC主题 键值对 键是模块标识 值是主题
     * @return array home_host_theme_mobile - desc:会员中心产品详情手机主题 键值对 键是模块标识 值是主题
     * @return array module_list - desc:模块列表
     * @return string module_list[].name - desc:模块标识
     * @return string module_list[].display_name - desc:模块名称
     * @return string module_list[].theme_list[].name - desc:PC主题名称
     * @return string module_list[].theme_list[].img - desc:图片
     * @return string module_list[].theme_mobile_list[].name - desc:手机主题名称
     * @return string module_list[].theme_mobile_list[].img - desc:图片
     */
    public function themeList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取主题设置
        $data = $ConfigurationModel->themeList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 保存主题设置
     * @desc 保存主题设置
     * @url /admin/v1/configuration/theme
     * @method PUT
     * @author theworld
     * @version v1
     * @param string admin_theme - desc:后台主题 validate:required
     * @param string clientarea_theme - desc:会员中心主题 validate:required
     * @param string cart_theme - desc:购物车主题 validate:required
     * @param string clientarea_theme_mobile_switch - desc:是否开启购物车主题 validate:required
     * @param string cart_theme_mobile - desc:购物车主题手机端 validate:required
     * @param int cart_instruction - desc:购物车说明开关 0关闭 1开启 validate:required
     * @param string cart_instruction_content - desc:购物车说明内容 validate:optional
     * @param int cart_change_product - desc:购物车切换商品开关 0关闭 1开启 validate:required
     * @param int web_switch - desc:官网开关 0关闭 1开启 validate:required
     * @param string web_theme - desc:官网主题 validate:required
     * @param string first_navigation - desc:一级导航名称 validate:optional
     * @param string second_navigation - desc:二级导航名称 validate:optional
     * @param string home_theme - desc:首页PC主题 validate:optional
     * @param string home_theme_mobile - desc:首页手机主题 validate:optional
     * @param array home_host_theme - desc:会员中心产品详情PC主题 键值对 键是模块标识 值是主题 validate:optional
     * @param array home_host_theme_mobile - desc:会员中心产品详情手机主题 键值对 键是模块标识 值是主题 validate:optional
     */
    public function themeUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('theme_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存主题设置
        $result = $ConfigurationModel->themeUpdate($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 获取实名设置
     * @desc 获取实名设置
     * @url /admin/v1/configuration/certification
     * @method GET
     * @author wyh
     * @version v1
     * @return int certification_open - desc:实名认证是否开启 1开启默认 0关
     * @return int certification_approval - desc:是否人工复审 1开启默认 0关
     * @return int certification_notice - desc:审批通过后是否通知客户 1通知默认 0否
     * @return int certification_update_client_name - desc:是否自动更新姓名 1是 0否默认
     * @return int certification_upload - desc:是否需要上传证件照 1是 0否默认
     * @return int certification_update_client_phone - desc:手机一致性 1是 0否默认
     * @return int certification_uncertified_suspended_host - desc:未认证暂停产品 1是 0否默认
     */
    public function certificationList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取主题设置
        $data = $ConfigurationModel->certificationList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 保存实名设置
     * @desc 保存实名设置
     * @url /admin/v1/configuration/certification
     * @method PUT
     * @author theworld
     * @version v1
     * @param int certification_open - desc:实名认证是否开启 1开启默认 0关 validate:required
     * @param int certification_approval - desc:是否人工复审 1开启默认 0关 validate:required
     * @param int certification_notice - desc:审批通过后是否通知客户 1通知默认 0否 validate:required
     * @param int certification_update_client_name - desc:是否自动更新姓名 1是 0否默认 validate:required
     * @param int certification_upload - desc:是否需要上传证件照 1是 0否默认 validate:required
     * @param int certification_update_client_phone - desc:手机一致性 1是 0否默认 validate:required
     * @param int certification_uncertified_suspended_host - desc:未认证暂停产品 1是 0否默认 validate:required
     */
    public function certificationUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('certification_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存主题设置
        $result = $ConfigurationModel->certificationUpdate($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 获取信息配置
     * @desc 获取信息配置
     * @url /admin/v1/configuration/info
     * @method GET
     * @author theworld
     * @version v1
     * @return string enterprise_name - desc:企业名称
     * @return string enterprise_telephone - desc:企业电话
     * @return string enterprise_mailbox - desc:企业邮箱
     * @return string enterprise_qrcode - desc:企业二维码
     * @return string online_customer_service_link - desc:在线客服链接
     * @return string icp_info - desc:ICP信息
     * @return string icp_info_link - desc:ICP信息链接
     * @return string public_security_network_preparation - desc:公安网备
     * @return string public_security_network_preparation_link - desc:公安网备链接
     * @return string telecom_appreciation - desc:电信增値
     * @return string copyright_info - desc:版权信息
     * @return string official_website_logo - desc:官网LOGO
     * @return string cloud_product_link - desc:云产品跳转链接
     * @return string dcim_product_link - desc:DCIM产品跳转链接
     */
    public function infoList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取信息配置
        $data = $ConfigurationModel->infoList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 保存信息配置
     * @desc 保存信息配置
     * @url /admin/v1/configuration/info
     * @method PUT
     * @author theworld
     * @version v1
     * @param string enterprise_name - desc:企业名称 validate:required
     * @param string enterprise_telephone - desc:企业电话 validate:required
     * @param string enterprise_mailbox - desc:企业邮箱 validate:required
     * @param string enterprise_qrcode - desc:企业二维码 validate:required
     * @param string online_customer_service_link - desc:在线客服链接 validate:required
     * @param string icp_info - desc:ICP信息 validate:required
     * @param string icp_info_link - desc:ICP信息链接 validate:required
     * @param string public_security_network_preparation - desc:公安网备 validate:required
     * @param string public_security_network_preparation_link - desc:公安网备链接 validate:required
     * @param string telecom_appreciation - desc:电信增値 validate:required
     * @param string copyright_info - desc:版权信息 validate:required
     * @param string official_website_logo - desc:官网LOGO validate:required
     * @param string cloud_product_link - desc:云产品跳转链接 validate:required
     * @param string dcim_product_link - desc:DCIM产品跳转链接 validate:required
     */
    public function infoUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('info_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存信息配置
        $result = $ConfigurationModel->infoUpdate($param);

        return json($result);
    }

    /**
     * 时间 2023-09-07
     * @title debug页面
     * @desc debug页面
     * @url /admin/v1/configuration/debug
     * @method GET
     * @author wyh
     * @version v1
     * @return int debug_model - desc:是否开启debug模式 1开启
     * @return string debug_model_auth - desc:debug模式授权码
     * @return string debug_model_expire_time - desc:到期时间
     */
    public function debugInfo()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取信息配置
        $data = $ConfigurationModel->debugInfo();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];

        return json($result);
    }

    /**
     * 时间 2023-09-07
     * @title 保存debug页面
     * @desc 保存debug页面
     * @url /admin/v1/configuration/debug
     * @method PUT
     * @author wyh
     * @version v1
     * @param int debug_model - desc:是否开启debug模式 1开启 validate:required
     */
    public function debug()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存信息配置
        $result = $ConfigurationModel->debug($param);

        return json($result);
    }

    /**
     * 时间 2024-01-26
     * @title 对象存储页面
     * @desc 对象存储页面
     * @url /admin/v1/configuration/oss
     * @method GET
     * @author wyh
     * @version v1
     * @return string oss_method - desc:对象存储方式 默认本地存储LocalOss
     * @return string oss_sms_plugin - desc:短信接口
     * @return string oss_sms_plugin_template - desc:短信模板
     * @return array oss_sms_plugin_admin - desc:短信通知人员
     * @return string oss_mail_plugin - desc:邮件接口
     * @return string oss_mail_plugin_template - desc:邮件模板
     * @return array oss_mail_plugin_admin - desc:邮件通知人员
     */
    public function getOssConfig()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取信息配置
        $data = $ConfigurationModel->getOssConfig();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];

        return json($result);
    }

    /**
     * 时间 2024-01-26
     * @title 保存对象存储页面
     * @desc 保存对象存储页面
     * @url /admin/v1/configuration/oss
     * @method PUT
     * @author wyh
     * @version v1
     * @param string oss_method - desc:对象存储方式 默认本地存储LocalOss validate:optional
     * @param string oss_sms_plugin - desc:短信接口 validate:optional
     * @param string oss_sms_plugin_template - desc:短信模板 validate:optional
     * @param array oss_sms_plugin_admin - desc:短信通知人员 validate:optional
     * @param string oss_mail_plugin - desc:邮件接口 validate:optional
     * @param string oss_mail_plugin_template - desc:邮件模板 validate:optional
     * @param array oss_mail_plugin_admin - desc:邮件通知人员 validate:optional
     * @param string password - desc:修改本地存储时需要传此字段 validate:optional
     */
    public function ossConfig()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存信息配置
        $result = $ConfigurationModel->ossConfig($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 获取网站参数配置
     * @desc 获取网站参数配置
     * @url /admin/v1/configuration/web
     * @method GET
     * @author theworld
     * @version v1
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
     */
    public function webList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取网站参数配置
        $data = $ConfigurationModel->webList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 保存网站参数配置
     * @desc 保存网站参数配置
     * @url /admin/v1/configuration/web
     * @method PUT
     * @author theworld
     * @version v1
     * @param string enterprise_name - desc:企业名称 validate:required
     * @param string enterprise_telephone - desc:企业电话 validate:required
     * @param string enterprise_mailbox - desc:企业邮箱 validate:required
     * @param string enterprise_qrcode - desc:企业二维码 validate:required
     * @param string online_customer_service_link - desc:在线客服链接 validate:required
     * @param string icp_info - desc:ICP信息 validate:required
     * @param string icp_info_link - desc:ICP信息链接 validate:required
     * @param string public_security_network_preparation - desc:公安网备 validate:required
     * @param string public_security_network_preparation_link - desc:公安网备链接 validate:required
     * @param string telecom_appreciation - desc:电信增値 validate:required
     * @param string copyright_info - desc:版权信息 validate:required
     * @param string official_website_logo - desc:官网LOGO validate:required
     */
    public function webUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('web')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存网站参数配置
        $result = $ConfigurationModel->webUpdate($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 获取云服务器配置
     * @desc 获取云服务器配置
     * @url /admin/v1/configuration/cloud_server
     * @method GET
     * @author theworld
     * @version v1
     * @return int cloud_server_more_offers - desc:更多优惠 0关闭 1开启
     */
    public function cloudServerList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取云服务器配置
        $data = $ConfigurationModel->cloudServerList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 保存云服务器配置
     * @desc 保存云服务器配置
     * @url /admin/v1/configuration/cloud_server
     * @method PUT
     * @author theworld
     * @version v1
     * @param int cloud_server_more_offers - desc:更多优惠 0关闭 1开启 validate:required
     */
    public function cloudServerUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('cloud_server')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存云服务器配置
        $result = $ConfigurationModel->cloudServerUpdate($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 获取物理服务器配置
     * @desc 获取物理服务器配置
     * @url /admin/v1/configuration/physical_server
     * @method GET
     * @author theworld
     * @version v1
     * @return int physical_server_more_offers - desc:更多优惠 0关闭 1开启
     */
    public function physicalServerList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取物理服务器配置
        $data = $ConfigurationModel->physicalServerList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 保存物理服务器配置
     * @desc 保存物理服务器配置
     * @url /admin/v1/configuration/physical_server
     * @method PUT
     * @author theworld
     * @version v1
     * @param int physical_server_more_offers - desc:更多优惠 0关闭 1开启 validate:required
     */
    public function physicalServerUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('physical_server')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存物理服务器配置
        $result = $ConfigurationModel->physicalServerUpdate($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 获取ICP配置
     * @desc 获取ICP配置
     * @url /admin/v1/configuration/icp
     * @method GET
     * @author theworld
     * @version v1
     * @return int icp_product_id - desc:购买和咨询商品ID
     */
    public function icpList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取ICP配置
        $data = $ConfigurationModel->icpList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 保存ICP配置
     * @desc 保存ICP配置
     * @url /admin/v1/configuration/icp
     * @method PUT
     * @author theworld
     * @version v1
     * @param int icp_product_id - desc:购买和咨询商品ID validate:required
     */
    public function icpUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('icp')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存ICP配置
        $result = $ConfigurationModel->icpUpdate($param);

        return json($result);
    }

    /**
     * 时间 2024-10-22
     * @title 获取商品全局设置
     * @desc 获取商品全局设置
     * @url /admin/v1/configuration/product
     * @method GET
     * @author theworld
     * @version v1
     * @return int self_defined_field_apply_range - desc:自定义字段应用范围 0无 1商品分组新增商品
     * @return int custom_host_name_apply_range - desc:自定义标识应用范围 0无 1商品分组新增商品
     * @return int product_duration_group_presets_open - desc:是否开启商品周期分组预设
     * @return int product_duration_group_presets_apply_range - desc:商品周期分组预设应用范围 0全局默认 1接口新增商品
     * @return int product_duration_group_presets_default_id - desc:商品周期分组预设全局默认分组ID
     * @return int product_new_host_renew_with_ratio_open - desc:新产品续费按周期比例折算 0关闭 1开启
     * @return int product_new_host_renew_with_ratio_apply_range - desc:新产品续费按周期比例折算范围 2商品分组下新产品 1接口下新产品 0全部新产品
     * @return array product_new_host_renew_with_ratio_apply_range_2 - desc:二级分组id 逗号分隔
     * @return array product_new_host_renew_with_ratio_apply_range_1 - desc:接口id 逗号分隔
     * @return int product_global_renew_rule - desc:商品到期日计算规则 0实际到期日 1产品到期日
     * @return int product_global_show_base_info - desc:基础信息展示 0关闭 1开启
     * @return int product_renew_with_new_open - desc:商品续费时重新计算续费金额 0关闭 1开启
     * @return array product_renew_with_new_product_ids - desc:所选商品ID
     * @return int product_overdue_not_delete_open - desc:商品到期后不自动删除 0关闭 1开启
     * @return array product_overdue_not_delete_product_ids - desc:到期后不自动删除的商品ID
     * @return int host_sync_due_time_open - desc:产品到期时间与上游一致 0关闭 1开启
     * @return int host_sync_due_time_apply_range - desc:产品到期时间与上游一致应用范围 0全部上游商品 1自定义上游商品
     * @return array host_sync_due_time_product_ids - desc:产品到期时间与上游一致的商品ID
     * @return int auto_renew_in_advance - desc:自动续费提前开关 0关闭 1开启
     * @return int auto_renew_in_advance_num - desc:自动续费提前时间数
     * @return string auto_renew_in_advance_unit - desc:自动续费提前时间单位 minute分钟 hour小时 day天
     */
    public function productGlobalSetting()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取商品全局设置
        $data = $ConfigurationModel->productGlobalSetting();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-10-22
     * @title 保存商品全局设置
     * @desc 保存商品全局设置
     * @url /admin/v1/configuration/product
     * @method PUT
     * @author theworld
     * @version v1
     * @param int self_defined_field_apply_range - desc:自定义字段应用范围 0无 1商品分组新增商品 validate:required
     * @param int custom_host_name_apply_range - desc:自定义标识应用范围 0无 1商品分组新增商品 validate:required
     * @param int product_duration_group_presets_open - desc:是否开启商品周期分组预设 validate:required
     * @param int product_duration_group_presets_apply_range - desc:商品周期分组预设应用范围 0全局默认 1接口新增商品 validate:required
     * @param int product_duration_group_presets_default_id - desc:商品周期分组预设全局默认分组ID validate:required
     * @param int product_new_host_renew_with_ratio_open - desc:新产品续费按周期比例折算 0关闭 1开启 validate:optional
     * @param int product_new_host_renew_with_ratio_apply_range - desc:新产品续费按周期比例折算范围 2商品分组下新产品 1接口下新产品 0全部新产品 validate:optional
     * @param array product_new_host_renew_with_ratio_apply_range_2 - desc:二级分组id数组 validate:optional
     * @param array product_new_host_renew_with_ratio_apply_range_1 - desc:接口id数组 validate:optional
     * @param int product_global_renew_rule - desc:商品到期日计算规则 0实际到期日 1产品到期日 validate:optional
     * @param int product_global_show_base_info - desc:基础信息展示 0关闭 1开启 validate:optional
     * @param int product_renew_with_new_open - desc:商品续费时重新计算续费金额 0关闭 1开启 validate:optional
     * @param array product_renew_with_new_product_ids - desc:所选商品ID validate:optional
     * @param int product_overdue_not_delete_open - desc:商品到期后不自动删除 0关闭 1开启 validate:optional
     * @param array product_overdue_not_delete_product_ids - desc:到期后不自动删除的商品ID validate:optional
     * @param int host_sync_due_time_open - desc:产品到期时间与上游一致 0关闭 1开启 validate:optional
     * @param int host_sync_due_time_apply_range - desc:产品到期时间与上游一致应用范围 0全部上游商品 1自定义上游商品 validate:optional
     * @param array host_sync_due_time_product_ids - desc:产品到期时间与上游一致的商品ID validate:optional
     * @param int auto_renew_in_advance - desc:自动续费提前开关 0关闭 1开启 validate:optional
     * @param int auto_renew_in_advance_num - desc:自动续费提前时间数 validate:optional
     * @param string auto_renew_in_advance_unit - desc:自动续费提前时间单位 minute分钟 hour小时 day天 validate:optional
     */
    public function productGlobalSettingUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('product')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存商品全局设置
        $result = $ConfigurationModel->productGlobalSettingUpdate($param);

        return json($result);
    }

    /**
     * 时间 2024-12-23
     * @title 游客可见商品
     * @desc 游客可见商品
     * @url /admin/v1/configuration/tourist_visible_product
     * @method GET
     * @author wyh
     * @version v1
     * @return array tourist_visible_product_ids - desc:商品ID
     */
    public function touristVisibleProduct()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取游客可见商品
        $result = $ConfigurationModel->touristVisibleProduct();

        return json($result);
    }

    /**
     * 时间 2024-12-23
     * @title 游客可见商品
     * @desc 游客可见商品
     * @url /admin/v1/configuration/tourist_visible_product
     * @method PUT
     * @author wyh
     * @version v1
     * @param array tourist_visible_product_ids - desc:商品ID validate:optional
     */
    public function touristVisibleProductUpdate()
    {
        $param = $this->request->param();

        $ConfigurationModel = new ConfigurationModel();

        $result = $ConfigurationModel->touristVisibleProductUpdate($param);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 获取代理商余额预警设置
     * @desc 获取代理商余额预警设置
     * @url /admin/v1/configuration/supplier_credit_warning
     * @method GET
     * @author hh
     * @version v1
     * @return int supplier_credit_warning_notice - desc:余额预警 0关闭 1开启
     * @return string supplier_credit_amount - desc:自定义余额提醒大小
     * @return int supplier_credit_push_frequency - desc:推送频率 1一天一次 2一天两次 3一天三次
     */
    public function supplierCreditWarning()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取商品全局设置
        $data = $ConfigurationModel->supplierCreditWarning();
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 保存代理商余额预警设置
     * @desc 保存代理商余额预警设置
     * @url /admin/v1/configuration/supplier_credit_warning
     * @method PUT
     * @author hh
     * @version v1
     * @param int supplier_credit_warning_notice - desc:余额预警 0关闭 1开启 validate:optional
     * @param float supplier_credit_amount - desc:自定义余额提醒大小 validate:optional
     * @param int supplier_credit_push_frequency - desc:推送频率 1一天一次 2一天两次 3一天三次 validate:optional
     */
    public function supplierCreditWarningUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('supplier_credit_amount')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存商品全局设置
        $result = $ConfigurationModel->supplierCreditWarningUpdate($param);

        return json($result);
    }

    /**
     * 时间 2025-04-01
     * @title 全局按需设置
     * @desc 全局按需设置
     * @url /admin/v1/configuration/global_on_demand
     * @method GET
     * @author hh
     * @version v1
     * @return string grace_time - desc:宽限期
     * @return string grace_time_unit - desc:宽限期单位 hour小时 day天
     * @return string keep_time - desc:保留期
     * @return string keep_time_unit - desc:保留期单位 hour小时 day天
     */
    public function globalOnDemand()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取商品全局设置
        $data = $ConfigurationModel->globalOnDemand();
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-04-01
     * @title 保存全局按需设置
     * @desc 保存全局按需设置
     * @url /admin/v1/configuration/global_on_demand
     * @method PUT
     * @author hh
     * @version v1
     * @param int grace_time - desc:宽限期 validate:required
     * @param string grace_time_unit - desc:宽限期单位 hour小时 day天 validate:required
     * @param int keep_time - desc:保留期 validate:required
     * @param string keep_time_unit - desc:保留期单位 hour小时 day天 validate:required
     */
    public function globalOnDemandUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('global_on_demand')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存商品全局设置
        $result = $ConfigurationModel->globalOnDemandUpdate($param);

        return json($result);
    }

    /**
     * 时间 2025-11-20
     * @title 获取亏本交易拦截配置
     * @desc 获取亏本交易拦截配置
     * @url /admin/v1/configuration/upstream_intercept
     * @method GET
     * @author wyh
     * @version v1
     * @return int upstream_intercept_new_order_notify - desc:新购通知管理员 0否 1是
     * @return int upstream_intercept_new_order_reject - desc:新购拒绝下单 0否 1是
     * @return int upstream_intercept_renew_notify - desc:续费通知管理员 0否 1是
     * @return int upstream_intercept_renew_reject - desc:续费拒绝下单 0否 1是
     * @return int upstream_intercept_upgrade_notify - desc:升降级通知管理员 0否 1是
     * @return int upstream_intercept_upgrade_reject - desc:升降级拒绝下单 0否 1是
     */
    public function upstreamInterceptList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();
        
        //获取亏本交易拦截配置
        $data = $ConfigurationModel->upstreamInterceptList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-11-20
     * @title 保存亏本交易拦截配置
     * @desc 保存亏本交易拦截配置
     * @url /admin/v1/configuration/upstream_intercept
     * @method PUT
     * @author wyh
     * @version v1
     * @param int upstream_intercept_new_order_notify - desc:新购通知管理员 0否 1是 validate:required
     * @param int upstream_intercept_new_order_reject - desc:新购拒绝下单 0否 1是 validate:required
     * @param int upstream_intercept_renew_notify - desc:续费通知管理员 0否 1是 validate:required
     * @param int upstream_intercept_renew_reject - desc:续费拒绝下单 0否 1是 validate:required
     * @param int upstream_intercept_upgrade_notify - desc:升降级通知管理员 0否 1是 validate:required
     * @param int upstream_intercept_upgrade_reject - desc:升降级拒绝下单 0否 1是 validate:required
     */
    public function upstreamInterceptUpdate()
    {
        //接收参数
        $param = $this->request->param();
        
        //参数验证
        if (!$this->validate->scene('upstream_intercept')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();
        
        //保存亏本交易拦截配置
        $result = $ConfigurationModel->upstreamInterceptUpdate($param);
        
        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 获取指定主题配置
     * @desc 获取指定主题配置
     * @url /admin/v1/configuration/theme_config/:theme
     * @method GET
     * @author wyh
     * @version v1
     * @param string theme - desc:主题名称 validate:required
     * @return string theme - desc:主题名称
     * @return string display_one - desc:公告区域展示内容1 ticket工单 announcement公告 recommend推荐计划
     * @return string display - desc:公告区域展示内容2 announcement公告 recommend推荐计划
     * @return array display_options - desc:展示内容选项
     * @return string display_options[].value - desc:选项值
     * @return string display_options[].name - desc:选项名称
     * @return int display_time - desc:轮播时间设置 单位分钟
     */
    public function themeConfigDetail()
    {
        $param = $this->request->param();
        $theme = $param['theme'] ?? 'default';

        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        if (!in_array($theme, $clientareaThemeList)){
            return json(['status' => 400 , 'msg' => lang('theme_not_exist')]);
        }

        //实例化模型类
        $ThemeConfigModel = new ThemeConfigModel();

        //获取主题配置
        $data = $ThemeConfigModel->getThemeConfig($theme);
        
        // 添加展示内容选项
        $data['display_one_options'] = [
            ['value' => 'ticket', 'name' => lang('theme_config_ticket')],
            ['value' => 'announcement', 'name' => lang('theme_config_announcement')],
            ['value' => 'recommend', 'name' => lang('theme_config_recommend')]
        ];
        
        $data['display_options'] = [
            ['value' => 'announcement', 'name' => lang('theme_config_announcement')],
            ['value' => 'recommend', 'name' => lang('theme_config_recommend')]
        ];

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 保存指定主题配置
     * @desc 保存指定主题配置
     * @url /admin/v1/configuration/theme_config/:theme
     * @method PUT
     * @author wyh
     * @version v1
     * @param string theme - desc:主题名称 validate:required
     * @param string display_one - desc:公告区域展示内容1 ticket工单 announcement公告 recommend推荐计划 validate:optional
     * @param string display - desc:公告区域展示内容2 announcement公告 recommend推荐计划 validate:optional
     */
    public function themeConfigUpdate()
    {
        //接收参数
        $param = $this->request->param();
        $theme = $param['theme'] ?? 'default';

        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        if (!in_array($theme, $clientareaThemeList)){
            return json(['status' => 400 , 'msg' => lang('theme_not_exist')]);
        }

        //参数验证
        if (!$this->validate->scene('theme_config_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ThemeConfigModel = new ThemeConfigModel();

        //保存主题配置
        $result = $ThemeConfigModel->updateThemeConfig($theme, $param);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 获取主题轮播图列表
     * @desc 获取主题轮播图列表
     * @url /admin/v1/configuration/theme_banner
     * @method GET
     * @author wyh
     * @version v1
     * @param string theme - desc:主题名称筛选 validate:optional
     * @return array list - desc:轮播图列表
     * @return int list[].id - desc:轮播图ID
     * @return string list[].theme - desc:主题
     * @return string list[].img - desc:图片
     * @return string list[].url - desc:跳转链接
     * @return int list[].start_time - desc:展示开始时间
     * @return int list[].end_time - desc:展示结束时间
     * @return int list[].show - desc:是否展示 0否 1是
     * @return string list[].notes - desc:备注
     * @return int list[].order - desc:排序
     * @return int list[].create_time - desc:创建时间
     * @return int list[].update_time - desc:更新时间
     */
    public function themeBannerList()
    {
        $param = $this->request->param();

        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        if (!in_array($param['theme']??'', $clientareaThemeList)){
            return json(['status' => 400 , 'msg' => lang('theme_not_exist')]);
        }

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //获取轮播图列表
        $data = $ThemeBannerModel->themeBannerList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 创建主题轮播图
     * @desc 创建主题轮播图
     * @url /admin/v1/configuration/theme_banner
     * @method POST
     * @author wyh
     * @version v1
     * @param string theme - desc:主题 validate:required
     * @param string img - desc:图片 validate:required
     * @param string url - desc:跳转链接 validate:required
     * @param int start_time - desc:展示开始时间 validate:required
     * @param int end_time - desc:展示结束时间 validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function themeBannerCreate()
    {
        //接收参数
        $param = $this->request->param();

        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        if (!in_array($param['theme']??'', $clientareaThemeList)){
            return json(['status' => 400 , 'msg' => lang('theme_not_exist')]);
        }
        
        // 重新映射参数名以符合验证规则
        $validateParam = [
            'theme_banner_theme' => $param['theme'] ?? '',
            'theme_banner_img' => $param['img'] ?? '',
            'theme_banner_url' => $param['url'] ?? '',
            'theme_banner_start_time' => $param['start_time'] ?? '',
            'theme_banner_end_time' => $param['end_time'] ?? '',
            'theme_banner_show' => $param['show'] ?? '',
            'theme_banner_notes' => $param['notes'] ?? '',
        ];

        //参数验证
        if (!$this->validate->scene('theme_banner_create')->check($validateParam)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //创建轮播图
        $result = $ThemeBannerModel->createThemeBanner($param);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 更新主题轮播图
     * @desc 更新主题轮播图
     * @url /admin/v1/configuration/theme_banner/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:轮播图ID validate:required
     * @param string theme - desc:主题 validate:required
     * @param string img - desc:图片 validate:required
     * @param string url - desc:跳转链接 validate:required
     * @param int start_time - desc:展示开始时间 validate:required
     * @param int end_time - desc:展示结束时间 validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function themeBannerUpdate()
    {
        //接收参数
        $param = $this->request->param();

        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        if (!in_array($param['theme']??'', $clientareaThemeList)){
            return json(['status' => 400 , 'msg' => lang('theme_not_exist')]);
        }
        
        // 重新映射参数名以符合验证规则
        $validateParam = [
            'theme_banner_theme' => $param['theme'] ?? '',
            'theme_banner_img' => $param['img'] ?? '',
            'theme_banner_url' => $param['url'] ?? '',
            'theme_banner_start_time' => $param['start_time'] ?? '',
            'theme_banner_end_time' => $param['end_time'] ?? '',
            'theme_banner_show' => $param['show'] ?? '',
            'theme_banner_notes' => $param['notes'] ?? '',
        ];

        //参数验证
        if (!$this->validate->scene('theme_banner_update')->check($validateParam)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //更新轮播图
        $result = $ThemeBannerModel->updateThemeBanner($param);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 删除主题轮播图
     * @desc 删除主题轮播图
     * @url /admin/v1/configuration/theme_banner/:id
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int id - desc:轮播图ID validate:required
     */
    public function themeBannerDelete()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //删除轮播图
        $result = $ThemeBannerModel->deleteThemeBanner($param['id']);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 切换主题轮播图显示状态
     * @desc 切换主题轮播图显示状态
     * @url /admin/v1/configuration/theme_banner/:id/show
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:轮播图ID validate:required
     * @param int show - desc:是否展示 0否 1是 validate:required
     */
    public function themeBannerShow()
    {
        //接收参数
        $param = $this->request->param();
        
        // 重新映射参数名以符合验证规则
        $validateParam = [
            'theme_banner_show' => $param['show'] ?? '',
        ];

        //参数验证
        if (!$this->validate->scene('theme_banner_show')->check($validateParam)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //切换显示状态
        $result = $ThemeBannerModel->showThemeBanner($param);

        return json($result);
    }

    /**
     * 时间 2025-01-16
     * @title 主题轮播图排序
     * @desc 主题轮播图排序
     * @url /admin/v1/configuration/theme_banner/order
     * @method PUT
     * @author wyh
     * @version v1
     * @param array id - desc:轮播图ID数组 传主题下所有轮播图的ID validate:required
     */
    public function themeBannerOrder()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $ThemeBannerModel = new ThemeBannerModel();

        //轮播图排序
        $result = $ThemeBannerModel->orderThemeBanner($param);

        return json($result);
    }

}


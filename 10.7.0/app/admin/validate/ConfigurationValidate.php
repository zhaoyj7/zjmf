<?php
namespace app\admin\validate;

use app\common\model\ProductGroupModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\UpstreamProductModel;
use think\Validate;

/**
 * 配置项验证
 */
class ConfigurationValidate extends Validate
{
	protected $rule = [
		# 系统设置
        'lang_admin' => 'require',
        'lang_home_open' => 'require|in:0,1',
        'lang_home' => 'require',
        'maintenance_mode' => 'require|in:0,1',
        'website_name' => 'require|max:255',
        'website_url' => 'require|max:255|url',	
        'terms_service_url' => 'require|max:255|url',
        'terms_privacy_url' => 'require|max:255|url',
        'system_logo' => 'require',	
        'client_start_id_value' => 'require|integer|between:1,99999999',		
        'order_start_id_value' => 'require|integer|between:1,99999999',
        'clientarea_url' => 'max:255|url',
        'home_show_deleted_host' => 'require|in:0,1',
        'clientarea_logo_url' => 'max:255|url',
        'clientarea_logo_url_blank' => 'require|in:0,1',
        'global_list_limit' => 'require|integer|between:1,500',
        'donot_save_client_product_password' => 'require|in:0,1',
        'ip_white_list' => 'checkIpWhiteList:thinkphp',
		
		# 登录设置
		'register_email' => 'require|in:0,1',
		'register_phone' => 'require|in:0,1',
		'login_phone_verify' => 'require|in:0,1',
		'home_login_check_ip' => 'require|in:0,1',
		'admin_login_check_ip' => 'require|in:0,1',
		'code_client_email_register' => 'require|in:0,1',
		'code_client_phone_register' => 'require|in:0,1',
		'limit_email_suffix' => 'require|in:0,1',
		'email_suffix' => 'requireIf:limit_email_suffix,1|checkEmailSuffix:thinkphp',
		'home_login_check_common_ip' => 'require|in:0,1',
		'home_login_ip_exception_verify' => 'array|checkHomeLoginIpExceptionVerify:thinkphp',
		'home_enforce_safe_method' => 'array|checkHomeEnforceSafeMethod:thinkphp',
		'admin_enforce_safe_method' => 'array|checkAdminEnforceSafeMethod:thinkphp',
		'admin_allow_remember_account' => 'require|in:0,1',
		'admin_enforce_safe_method_scene'	=> 'requireWith:admin_enforce_safe_method|array|checkAdminEnforceSafeMethodScene:thinkphp',
        'first_login_method' => 'require|in:code,password',
        'first_password_login_method' => 'require|in:email,phone',
        'login_email_password' => 'require|in:0,1',
        'admin_second_verify' => 'require|in:0,1',
        'admin_second_verify_method_default' => 'in:sms,email,totp',
        'prohibit_admin_bind_phone' => 'require|in:0,1',
        'prohibit_admin_bind_email' => 'require|in:0,1',
        'admin_password_or_verify_code_retry_times' => 'integer|between:0,10',
        'admin_frozen_time' => 'integer|between:0,1440',
        'admin_login_expire_time' => 'integer|egt:0',
        'login_phone_password' => 'require|in:0,1',
        'home_login_expire_time' => 'integer|egt:0',
        'admin_login_ip_whitelist' => 'checkAdminLoginIpWhitelist:thinkphp',
        'admin_login_password_encrypt' => 'require|in:0,1',
        
        # 登录注册页面跳转设置
        'login_register_redirect_show' => 'require|in:0,1',
        'login_register_redirect_text' => 'requireIf:login_register_redirect_show,1|max:50',
        'login_register_redirect_url' => 'requireIf:login_register_redirect_show,1|max:255|url',
        'login_register_redirect_blank' => 'requireIf:login_register_redirect_show,1|in:0,1',

		# 安全设置
		'captcha_client_register' => 'require|in:0,1',
		'captcha_client_login' => 'require|in:0,1',
		'captcha_client_login_error' => 'require|in:0,1',
		'captcha_admin_login' => 'require|in:0,1',
		'captcha_width' => 'require|between:200,400',	
		'captcha_height' => 'require|between:50,100',	
		'captcha_length' => 'require|between:4,6|integer',	
		'captcha_client_verify' => 'require|in:0,1',
		'captcha_client_update' => 'require|in:0,1',
		'captcha_client_password_reset' => 'require|in:0,1',
		'captcha_client_oauth' => 'require|in:0,1',
		'captcha_client_security_verify' => 'require|in:0,1',
        'captcha_admin_login_error' => 'require|in:0,1',
		
		# 货币设置
		'currency_code' => 'require',
		'currency_prefix' => 'require',
		'currency_suffix' => 'require',
		'recharge_open' => 'require|in:0,1',
		'recharge_min' => 'gt:0|float',
		'recharge_max' => 'egt:recharge_min|float',
		'recharge_notice' => 'require|in:0,1',
        'balance_notice_show' => 'require|in:0,1',
		'recharge_money_notice_content' => 'max:65535',
		'recharge_pay_notice_content' => 'max:65535',

		# 定时任务
		'cron_due_suspend_day' => 'number',
		'cron_due_terminate_day' => 'number',
		'cron_due_renewal_first_day' => 'number',
		'cron_due_renewal_second_day' => 'number',
		'cron_overdue_first_day' => 'number',
		'cron_overdue_second_day' => 'number',
		'cron_overdue_third_day' => 'number',
		'cron_ticket_close_day' => 'number',
		'cron_order_overdue_day' => 'number',
		'cron_due_suspend_swhitch' => 'require|in:0,1',
		'cron_due_unsuspend_swhitch' => 'require|in:0,1',
		'cron_due_terminate_swhitch' => 'require|in:0,1',
		'cron_due_renewal_first_swhitch' => 'require|in:0,1',
		'cron_due_renewal_second_swhitch' => 'require|in:0,1',
		'cron_overdue_first_swhitch' => 'require|in:0,1',
		'cron_overdue_second_swhitch' => 'require|in:0,1',
		'cron_overdue_third_swhitch' => 'require|in:0,1',
		'cron_ticket_close_swhitch' => 'require|in:0,1',
		'cron_aff_swhitch' => 'require|in:0,1',
		'cron_order_overdue_swhitch' => 'require|in:0,1',
		'cron_order_unpaid_delete_swhitch' => 'require|in:0,1',
		'cron_order_unpaid_delete_day' => 'number',
		'cron_system_log_delete_swhitch' => 'require|in:0,1',
		'cron_system_log_delete_day' => 'number',
		'cron_sms_log_delete_swhitch' => 'require|in:0,1',
		'cron_sms_log_delete_day' => 'number',
		'cron_email_log_delete_swhitch' => 'require|in:0,1',
		'cron_email_log_delete_day' => 'number',

		# 主题设置
        'admin_theme' => 'require',
        'clientarea_theme' => 'require',
        'web_switch' => 'require|in:0,1',
        'web_theme' => 'require',
        'cart_theme' => 'require',
        'cart_theme_mobile' => '',
        'first_navigation' => 'require',
        'second_navigation' => 'require',
        'clientarea_theme_mobile' => 'require',
        'cart_instruction' => 'require|in:0,1',
        'cart_instruction_content' => 'requireIf:cart_instruction,1',
        'cart_change_product' => 'require|in:0,1',
        'home_theme' => 'require',
        // 'home_theme_mobile' => '',

        # 实名设置
        'certification_open' => 'require|in:0,1',
        'certification_approval' => 'require|in:0,1',
        'certification_notice' => 'require|in:0,1',
        'certification_update_client_name' => 'require|in:0,1',
        'certification_upload' => 'require|in:0,1',
        'certification_update_client_phone' => 'require|in:0,1',
        'certification_uncertified_suspended_host' => 'require|in:0,1',

        // # 官网设置
        // 'enterprise_name' => 'max:255',
        // 'enterprise_telephone' => 'max:50',
        // 'enterprise_mailbox' => 'max:255',
        // //'enterprise_qrcode' => 'require',
        // //'online_customer_service_link' => 'require',
        // 'icp_info' => 'max:255',
        // 'icp_info_link' => 'max:255|url',
        // 'public_security_network_preparation' => 'max:255',
        // 'public_security_network_preparation_link' => 'max:255|url',
        // 'telecom_appreciation' => 'max:255',
        // 'copyright_info' => 'max:255',
        // //'official_website_logo' => 'require',
        // 'cloud_product_link' => 'max:255|url',
        // 'dcim_product_link' => 'max:255|url',

        # 订单回收站设置
        'order_recycle_bin'	=> 'require|in:0,1',
        'order_recycle_bin_save_days' => 'integer|between:0,999',

         # 网站参数设置
        'enterprise_name'                           => 'require|max:255',
        'enterprise_telephone'                      => 'require|max:50',
        'enterprise_mailbox'                        => 'require|max:255',
        'enterprise_qrcode'                         => 'require',
        'online_customer_service_link'              => 'require',
        'icp_info'                                  => 'require|max:255',
        'icp_info_link'                             => 'require|max:255|url',
        'public_security_network_preparation'       => 'require|max:255',
        'public_security_network_preparation_link'  => 'require|max:255|url',
        'telecom_appreciation'                      => 'require|max:255',
        'copyright_info'                            => 'require|max:255',
        'official_website_logo'                     => 'require',
        'cloud_server_more_offers'                  => 'require|in:0,1',
        'physical_server_more_offers'               => 'require|in:0,1',
        'icp_product_id'                            => 'require|integer|gt:0',

        # 商品全局设置
        'self_defined_field_apply_range'            		=> 'require|in:0,1',
        'custom_host_name_apply_range'              		=> 'require|in:0,1',
        'product_duration_group_presets_open'       		=> 'require|in:0,1',
        'product_duration_group_presets_apply_range'		=> 'require|in:0,1',
        'product_duration_group_presets_default_id' 		=> 'require|integer',
        'product_new_host_renew_with_ratio_open'    		=> 'require|in:0,1',
        'product_new_host_renew_with_ratio_apply_range'    	=> 'require|in:0,1,2',
        'product_global_renew_rule'                 		=> 'require|in:0,1',
        'product_global_show_base_info'             		=> 'require|in:0,1',
        'product_new_host_renew_with_ratio_apply_range_1'	=> 'array|checkRatioApplyRangeGroup:thinkphp',
        'product_new_host_renew_with_ratio_apply_range_2'  	=> 'array|checkRatioApplyRangeServer:thinkphp',
        'product_overdue_not_delete_open'       			=> 'require|in:0,1',
        'product_overdue_not_delete_product_ids'			=> 'array|checkRatioApplyRangeProduct:thinkphp',
        'host_sync_due_time_open'       					=> 'require|in:0,1',
        'host_sync_due_time_apply_range'       				=> 'require|in:0,1',
        'host_sync_due_time_product_ids'					=> 'requireIf:host_sync_due_time_apply_range,1|array|checkRatioApplyRangeUpstreamProduct:thinkphp',
        'auto_renew_in_advance'       					    => 'require|in:0,1',
        'auto_renew_in_advance_num' 		                => 'require|integer|gt:0',
        'auto_renew_in_advance_unit'				        => 'require|in:minute,hour,day',

        # 代理商余额预警
        'supplier_credit_warning_notice'            => 'require|in:0,1',
        'supplier_credit_amount'                    => 'require|float|between:0,99999999',
        'supplier_credit_push_frequency'            => 'require|in:1,2,3',

        # 亏本交易拦截
        'upstream_intercept_new_order_notify'       => 'require|in:0,1',
        'upstream_intercept_new_order_reject'       => 'require|in:0,1',
        'upstream_intercept_renew_notify'           => 'require|in:0,1',
        'upstream_intercept_renew_reject'           => 'require|in:0,1',
        'upstream_intercept_upgrade_notify'         => 'require|in:0,1',
        'upstream_intercept_upgrade_reject'         => 'require|in:0,1',

        # 全局按需设置
        'grace_time'                => 'require|integer|between:0,99999999',
        'grace_time_unit'           => 'require|in:hour,day',
        'keep_time'                 => 'require|integer|between:0,99999999',
        'keep_time_unit'            => 'require|in:hour,day',

        # 主题配置设置
        'theme'                     => 'require|max:255',
        'display_one'               => 'in:ticket,announcement,recommend',
        'display'                   => 'in:announcement,recommend',

        # 主题轮播图设置
        'theme_banner_theme'        => 'require|max:255',
        'theme_banner_img'          => 'require|max:500',
        'theme_banner_url'          => 'max:500|url',
        'theme_banner_start_time'   => 'require|integer|gt:0',
//        'theme_banner_end_time'     => 'require|integer|gt:theme_banner_start_time',
        'theme_banner_end_time'     => 'require|integer',
        'theme_banner_show'         => 'require|in:0,1',
        'theme_banner_notes'        => 'max:1000',
        'theme_banner_order'        => 'integer|egt:0',
    ];

    protected $message  =  [
        # 系统设置
        'lang_admin.require' => 'configuration_admin_default_language_cannot_empty',
        'lang_home_open.require' => 'configuration_home_default_language_open_cannot_empty',
        'lang_home_open.in' => 'configuration_home_default_language_open',
        'lang_home.require' => 'configuration_home_default_language_cannot_empty',
        'maintenance_mode.require' => 'configuration_maintenance_mode_cannot_empty',
        'maintenance_mode.in' => 'configuration_maintenance_mode',
        'website_name.require' => 'configuration_website_name',
        'website_name.max' => 'configuration_website_name_cannot_exceed_255_chars',
        'website_url.require' => 'configuration_website_url',
        'website_url.max' => 'configuration_website_url_cannot_exceed_255_chars',
        'website_url.url' => 'configuration_website_url_error',
        'clientarea_url.max' => 'configuration_clientarea_url_cannot_exceed_255_chars',
        'clientarea_url.url' => 'configuration_clientarea_url_error',
        'terms_service_url.require' => 'configuration_terms_service_url',
        'terms_service_url.max' => 'configuration_terms_service_url_cannot_exceed_255_chars',
        'terms_service_url.url' => 'configuration_website_url_error',
        'terms_privacy_url.require' => 'configuration_terms_privacy_url',
        'terms_privacy_url.max' => 'configuration_terms_privacy_url_cannot_exceed_255_chars',
        'terms_privacy_url.url' => 'configuration_website_url_error',
        'system_logo.require' => 'configuration_system_logo',
        'client_start_id_value.require' => 'configuration_client_start_id_value_cannot_empty',
        'client_start_id_value.integer' => 'configuration_client_start_id_value_error',
        'client_start_id_value.between' => 'configuration_client_start_id_value_error',
        'order_start_id_value.require' => 'configuration_order_start_id_value_cannot_empty',
        'order_start_id_value.integer' => 'configuration_order_start_id_value_error',
        'order_start_id_value.between' => 'configuration_order_start_id_value_error',
        'home_show_deleted_host.require' => 'configuration_home_show_deleted_host_cannot_empty',
        'home_show_deleted_host.in' => 'configuration_home_show_deleted_host',
        'clientarea_logo_url.max' => 'configuration_clientarea_logo_url_cannot_exceed_255_chars',
        'clientarea_logo_url.url' => 'configuration_website_url_error',
        'clientarea_logo_url_blank.require' => 'configuration_clientarea_logo_url_blank_cannot_empty',
        'clientarea_logo_url_blank.in' => 'configuration_clientarea_logo_url_blank',
        'global_list_limit.require' => 'configuration_global_list_limit_require',
        'global_list_limit.integer' => 'configuration_global_list_limit_format_error',
        'global_list_limit.between' => 'configuration_global_list_limit_format_error',
        'donot_save_client_product_password.require' => 'configuration_donot_save_client_product_password_cannot_empty',
        'donot_save_client_product_password.in' => 'configuration_donot_save_client_product_password',
        'ip_white_list.checkIpWhiteList' => 'configuration_ip_white_list_format_error',
		
		# 登录设置
		'register_email.require' => 'configuration_register_email_cannot_empty',
		'register_email.in' => 'configuration_register_email',
		'register_phone.require' => 'configuration_register_phone_cannot_empty',
		'register_phone.in' => 'configuration_register_phone',
		'login_phone_verify.require' => 'configuration_login_phone_verify_cannot_empty',
		'login_phone_verify.in' => 'configuration_login_phone_verify',
		'email_suffix.requireIf' => 'configuration_email_suffix_cannot_empty',
		'home_login_check_common_ip.require' => 'configuration_home_login_check_common_ip_require',
		'home_login_check_common_ip.in' => 'configuration_home_login_check_common_ip_in',
		// 'home_login_ip_exception_verify.require' => 'configuration_home_login_ip_exception_verify_require',
		'home_login_ip_exception_verify.array' => 'configuration_home_login_ip_exception_verify_require',
		// 'home_enforce_safe_method.require' => 'configuration_home_enforce_safe_method_require',
		'home_enforce_safe_method.array' => 'configuration_home_enforce_safe_method_require',
		// 'admin_enforce_safe_method.require' => 'configuration_admin_enforce_safe_method_require',
		'admin_enforce_safe_method.array' => 'configuration_admin_enforce_safe_method_require',
		'admin_allow_remember_account.require' => 'configuration_admin_allow_remember_account_require',
		'admin_allow_remember_account.in' => 'configuration_admin_allow_remember_account_in',
		'admin_enforce_safe_method_scene.requireWith' => 'configuration_admin_enforce_safe_method_scene_require',
		'admin_enforce_safe_method_scene.array' => 'configuration_admin_enforce_safe_method_scene_require',
        'admin_second_verify.require' => 'param_error',
        'admin_second_verify.in' => 'param_error',
        'admin_second_verify_method_default.in' => 'param_error',
        'prohibit_admin_bind_phone.require' => 'param_error',
        'prohibit_admin_bind_phone.in' => 'param_error',
        'prohibit_admin_bind_email.require' => 'param_error',
        'prohibit_admin_bind_email.in' => 'param_error',
        'admin_password_or_verify_code_retry_times.integer' => 'configuration_admin_password_or_verify_code_retry_times_error',
        'admin_password_or_verify_code_retry_times.between' => 'configuration_admin_password_or_verify_code_retry_times_error',
        'admin_frozen_time.integer' => 'configuration_admin_frozen_time_error',
        'admin_frozen_time.between' => 'configuration_admin_frozen_time_error',
        'admin_login_expire_time.integer' => 'configuration_admin_login_expire_time_error',
        'admin_login_expire_time.egt' => 'configuration_admin_login_expire_time_error',
        'home_login_expire_time.integer' => 'configuration_home_login_expire_time_error',
        'home_login_expire_time.egt' => 'configuration_home_login_expire_time_error',
        'admin_login_password_encrypt.require' => 'param_error',
        'admin_login_password_encrypt.in' => 'param_error',
		
		# 安全设置
		'captcha_client_register.require' => 'configuration_captcha_client_register_cannot_empty',
		'captcha_client_register.in' => 'configuration_captcha_client_register',
		'captcha_client_login.require' => 'configuration_captcha_client_login_cannot_empty',
		'captcha_client_login.in' => 'configuration_captcha_client_login',
		'captcha_client_login_error.require' => 'configuration_captcha_client_login_error_cannot_empty',
		'captcha_client_login_error.in' => 'configuration_captcha_client_login_error',
		'captcha_admin_login.require' => 'configuration_captcha_admin_login_cannot_empty',
		'captcha_admin_login.in' => 'configuration_captcha_admin_login',
		'captcha_width.require' => 'configuration_captcha_width_cannot_empty',
		'captcha_width.between' => 'configuration_captcha_width',
		'captcha_height.require' => 'configuration_captcha_height_cannot_empty',
		'captcha_height.between' => 'configuration_captcha_height',
		'captcha_length.require' => 'configuration_captcha_length_cannot_empty',
		'captcha_length.between' => 'configuration_captcha_length',
		'captcha_length.integer' => 'configuration_captcha_length',
		'captcha_client_verify.require' => 'configuration_captcha_client_verify_cannot_empty',
		'captcha_client_verify.in' => 'configuration_captcha_client_verify',
		'captcha_client_update.require' => 'configuration_captcha_client_update_cannot_empty',
		'captcha_client_update.in' => 'configuration_captcha_client_update',
		'captcha_client_password_reset.require' => 'configuration_captcha_client_password_reset_cannot_empty',
		'captcha_client_password_reset.in' => 'configuration_captcha_client_password_reset',
		'captcha_client_oauth.require' => 'configuration_captcha_client_oauth_cannot_empty',
		'captcha_client_oauth.in' => 'configuration_captcha_client_oauth',
        'captcha_admin_login_error.require' => 'configuration_captcha_admin_login_error_cannot_empty',
        'captcha_admin_login_error.in' => 'configuration_captcha_admin_login_error',
		
		# 货币设置
		'currency_code.require' => 'configuration_currency_code_cannot_empty',
		'currency_prefix.require' => 'configuration_currency_prefix_cannot_empty',
		'currency_suffix.require' => 'configuration_currency_suffix_cannot_empty',
		'recharge_open.require' => 'configuration_recharge_open_cannot_empty',
		'recharge_open.in' => 'configuration_recharge_open',
		'recharge_min.gt' => 'configuration_recharge_min_float',
		'recharge_min.float' => 'configuration_recharge_min_float',
		'recharge_max.egt' => 'configuration_recharge_max_egt_recharge_min',
		'recharge_notice.require' => 'configuration_recharge_notice_require',
		'recharge_notice.in' => 'configuration_recharge_notice_in',
        'balance_notice_show.require' => 'configuration_balance_notice_show_require',
        'balance_notice_show.in' => 'configuration_balance_notice_show_in',
		'recharge_money_notice_content.max' => 'configuration_recharge_money_notice_content_max',
		'recharge_pay_notice_content.max' => 'configuration_recharge_pay_notice_content_max',

		# 定时任务
		
		'cron_due_suspend_day.number' => 'configuration_cron_due_suspend_day_cannot_empty',		
		'cron_due_terminate_day.number' => 'configuration_cron_due_terminate_day_cannot_empty',		
		'cron_due_renewal_first_day.number' => 'configuration_cron_due_renewal_first_day_cannot_empty',	
		'cron_due_renewal_second_day.number' => 'configuration_cron_due_renewal_second_day_cannot_empty',		
		'cron_overdue_first_day.number' => 'configuration_cron_overdue_first_day_cannot_empty',
		'cron_overdue_second_day.number' => 'configuration_cron_overdue_second_day_cannot_empty',
		'cron_overdue_third_day.number' => 'configuration_cron_overdue_third_day_cannot_empty',
		'cron_ticket_close_day.number' => 'configuration_cron_ticket_close_day_cannot_empty',
		'cron_order_overdue_day.number' => 'configuration_cron_order_overdue_day_cannot_empty',
		'cron_order_unpaid_delete_day.number' => 'configuration_cron_order_unpaid_delete_day_cannot_empty',
		'cron_system_log_delete_day.number' => 'configuration_cron_system_log_delete_day_cannot_empty',
		'cron_sms_log_delete_day.number' => 'configuration_cron_sms_log_delete_day_cannot_empty',
		'cron_email_log_delete_day.number' => 'configuration_cron_email_log_delete_day_cannot_empty',
		
		'cron_due_suspend_swhitch.require' => 'configuration_cron_due_suspend_swhitch',		
		'cron_due_unsuspend_swhitch.require' => 'configuration_cron_due_unsuspend_swhitch',		
		'cron_due_terminate_swhitch.require' => 'configuration_cron_due_terminate_swhitch',	
		'cron_due_renewal_first_swhitch.require' => 'configuration_cron_due_renewal_first_swhitch',		
		'cron_due_renewal_second_swhitch.require' => 'configuration_cron_due_renewal_second_swhitch',
		'cron_overdue_first_swhitch.require' => 'configuration_cron_overdue_first_swhitch',
		'cron_overdue_second_swhitch.require' => 'configuration_cron_overdue_second_swhitch',
		'cron_overdue_third_swhitch.require' => 'configuration_cron_overdue_third_swhitch',
		'cron_ticket_close_swhitch.require' => 'configuration_cron_ticket_close_swhitch',
		'cron_aff_swhitch.require' => 'configuration_cron_aff_swhitch',
		'cron_order_overdue_swhitch.require' => 'configuration_cron_order_overdue_swhitch',
		'cron_order_unpaid_delete_swhitch.require' => 'configuration_cron_order_unpaid_delete_swhitch',
		'cron_system_log_delete_swhitch.require' => 'configuration_cron_system_log_delete_swhitch',
		'cron_sms_log_delete_swhitch.require' => 'configuration_cron_sms_log_delete_swhitch',
		'cron_email_log_delete_swhitch.require' => 'configuration_cron_email_log_delete_swhitch',
		
		'cron_due_suspend_swhitch.in' => 'configuration_cron_due_suspend_swhitch',		
		'cron_due_unsuspend_swhitch.in' => 'configuration_cron_due_unsuspend_swhitch',		
		'cron_due_terminate_swhitch.in' => 'configuration_cron_due_terminate_swhitch',	
		'cron_due_renewal_first_swhitch.in' => 'configuration_cron_due_renewal_first_swhitch',		
		'cron_due_renewal_second_swhitch.in' => 'configuration_cron_due_renewal_second_swhitch',
		'cron_overdue_first_swhitch.in' => 'configuration_cron_overdue_first_swhitch',
		'cron_overdue_second_swhitch.in' => 'configuration_cron_overdue_second_swhitch',
		'cron_overdue_third_swhitch.in' => 'configuration_cron_overdue_third_swhitch',
		'cron_ticket_close_swhitch.in' => 'configuration_cron_ticket_close_swhitch',
		'cron_aff_swhitch.in' => 'configuration_cron_aff_swhitch',
		'cron_order_overdue_swhitch.in' => 'configuration_cron_order_overdue_swhitch',
		'cron_order_unpaid_delete_swhitch.in' => 'configuration_cron_order_unpaid_delete_swhitch',
		'cron_system_log_delete_swhitch.in' => 'configuration_cron_system_log_delete_swhitch',
		'cron_sms_log_delete_swhitch.in' => 'configuration_cron_sms_log_delete_swhitch',
		'cron_email_log_delete_swhitch.in' => 'configuration_cron_email_log_delete_swhitch',

		# 主题设置
		'admin_theme.require' => 'configuration_theme_admin_theme_cannot_empty',
		'clientarea_theme.require' => 'configuration_theme_clientarea_theme_cannot_empty',
		'web_switch.require' => 'param_error',
		'web_switch.in' => 'param_error',
		'web_theme.require' => 'configuration_theme_web_theme_cannot_empty',
		'cart_instruction.require' => 'param_error',
		'cart_instruction.in' => 'param_error',
		'cart_instruction_content.requireIf' => 'configuration_cart_instruction_content_require',
		'cart_change_product.require' => 'param_error',
		'cart_change_product.in' => 'param_error',
        'home_theme.require' => 'param_error',
        // 'home_theme_mobile.require' => 'param_error',

        # 实名设置
		'certification_open.require' => 'configuration_certification_open_require',
		'certification_approval.require' => 'configuration_certification_approval_require',
		'certification_notice.require' => 'configuration_certification_notice_require',
		'certification_update_client_name.require' => 'configuration_certification_update_client_name_require',
        'certification_upload.require' => 'configuration_certification_upload_require',
		'certification_update_client_phone.require' => 'configuration_certification_update_client_phone_require',
		'certification_uncertified_suspended_host.require' => 'configuration_certification_uncertified_suspended_host_require',

		// # 信息设置
  //       //'enterprise_name.require' => 'enterprise_name_require',
  //       'enterprise_name.max' => 'enterprise_name_max',
  //       //'enterprise_telephone.require' => 'enterprise_telephone_require',
  //       'enterprise_telephone.max' => 'enterprise_telephone_max',
  //       //'enterprise_mailbox.require' => 'enterprise_mailbox_require',
  //       'enterprise_mailbox.max' => 'enterprise_mailbox_max',
  //       //'enterprise_qrcode.require' => 'enterprise_qrcode_require',
  //       //'online_customer_service_link.require' => 'online_customer_service_link_require',
  //       //'icp_info.require' => 'icp_info_require',
  //       'icp_info.max' => 'icp_info_max',
  //       //'icp_info_link.require' => 'icp_info_link_require',
  //       'icp_info_link.max' => 'icp_info_link_max',
  //       'icp_info_link.url' => 'icp_info_link_error',
  //       //'public_security_network_preparation.require' => 'public_security_network_preparation_require',
  //       'public_security_network_preparation.max' => 'public_security_network_preparation_max',
  //       //'public_security_network_preparation_link.require' => 'public_security_network_preparation_link_require',
  //       'public_security_network_preparation_link.max' => 'public_security_network_preparation_link_max',
  //       'public_security_network_preparation_link.url' => 'public_security_network_preparation_link_error',
  //       //'telecom_appreciation.require' => 'telecom_appreciation_require',
  //       'telecom_appreciation.max' => 'telecom_appreciation_max',
  //       //'copyright_info.require' => 'copyright_info_require',
  //       'copyright_info.max' => 'copyright_info_max',
  //       //'official_website_logo.require' => 'official_website_logo_require',
  //       //'cloud_product_link.require' => 'cloud_product_link_require',
  //       'cloud_product_link.max' => 'cloud_product_link_max',
  //       'cloud_product_link.url' => 'cloud_product_link_error',
  //       //'dcim_product_link.require' => 'dcim_product_link_require',
  //       'dcim_product_link.max' => 'dcim_product_link_max',
  //       'dcim_product_link.url' => 'dcim_product_link_error',

        # 订单回收站
        'order_recycle_bin.require' => 'order_recycle_bin_param_require',
        'order_recycle_bin.in' => 'order_recycle_bin_param_in',
        'order_recycle_bin_save_days.integer' => 'order_recycle_bin_save_days_format_error',
        'order_recycle_bin_save_days.between' => 'order_recycle_bin_save_days_format_error',

        # 网站参数设置
        'enterprise_name.require'                       => 'enterprise_name_require',
        'enterprise_name.max'                           => 'enterprise_name_max',
        'enterprise_telephone.require'                  => 'enterprise_telephone_require',
        'enterprise_telephone.max'                      => 'enterprise_telephone_max',
        'enterprise_mailbox.require'                    => 'enterprise_mailbox_require',
        'enterprise_mailbox.max'                        => 'enterprise_mailbox_max',
        'enterprise_qrcode.require'                     => 'enterprise_qrcode_require',
        'online_customer_service_link.require'          => 'online_customer_service_link_require',
        'icp_info.require'                              => 'icp_info_require',
        'icp_info.max'                                  => 'icp_info_max',
        'icp_info_link.require'                         => 'icp_info_link_require',
        'icp_info_link.max'                             => 'icp_info_link_max',
        'icp_info_link.url'                             => 'icp_info_link_error',
        'public_security_network_preparation.require'   => 'public_security_network_preparation_require',
        'public_security_network_preparation.max'       => 'public_security_network_preparation_max',
        'public_security_network_preparation_link.max'  => 'public_security_network_preparation_link_max',
        'public_security_network_preparation_link.url'  => 'public_security_network_preparation_link_error',
        'telecom_appreciation.require'                  => 'telecom_appreciation_require',
        'telecom_appreciation.max'                      => 'telecom_appreciation_max',
        'copyright_info.require'                        => 'copyright_info_require',
        'copyright_info.max'                            => 'copyright_info_max',
        'official_website_logo.require'                 => 'official_website_logo_require',
        'cloud_server_more_offers.require'              => 'param_error',
        'cloud_server_more_offers.in'                   => 'param_error',
        'physical_server_more_offers.require'           => 'param_error',
        'physical_server_more_offers.in'                => 'param_error',
        'icp_product_id.require'                        => 'id_error',
        'icp_product_id.integer'                        => 'id_error',
        'icp_product_id.gt'                             => 'id_error',

        # 商品全局设置
        'self_defined_field_apply_range.require'        => 'param_error',
        'self_defined_field_apply_range.in'             => 'param_error',
        'custom_host_name_apply_range.require'          => 'param_error',
        'custom_host_name_apply_range.in'               => 'param_error',
        'product_overdue_not_delete_open.require'       => 'param_error',
        'product_overdue_not_delete_open.in'       		=> 'param_error',
        'host_sync_due_time_open.require'       		=> 'param_error',
        'host_sync_due_time_open.in'       				=> 'param_error',
        'host_sync_due_time_apply_range.require'       	=> 'param_error',
        'host_sync_due_time_apply_range.in'       		=> 'param_error',
        'auto_renew_in_advance.require'                 => 'param_error',
        'auto_renew_in_advance.in'                      => 'param_error',
        'auto_renew_in_advance_num.require'             => 'configuration_auto_renew_in_advance_num_require',
        'auto_renew_in_advance_num.integer'             => 'configuration_auto_renew_in_advance_num_error',
        'auto_renew_in_advance_num.gt'                  => 'configuration_auto_renew_in_advance_num_error',
        'auto_renew_in_advance_unit.require'            => 'param_error',
        'auto_renew_in_advance_unit.in'                 => 'param_error',


        # 代理商余额预警
        'supplier_credit_warning_notice.require'        => 'param_error',
        'supplier_credit_warning_notice.in'             => 'param_error',
        'supplier_credit_amount.require'                => 'supplier_credit_amount_require',
        'supplier_credit_amount.float'                  => 'supplier_credit_amount_format_error',
        'supplier_credit_amount.between'                => 'supplier_credit_amount_format_error',
        'supplier_credit_push_frequency.require'        => 'param_error',
        'supplier_credit_push_frequency.in'             => 'param_error',

        # 亏本交易拦截
        'upstream_intercept_new_order_notify.require'   => 'param_error',
        'upstream_intercept_new_order_notify.in'        => 'param_error',
        'upstream_intercept_new_order_reject.require'   => 'param_error',
        'upstream_intercept_new_order_reject.in'        => 'param_error',
        'upstream_intercept_renew_notify.require'       => 'param_error',
        'upstream_intercept_renew_notify.in'            => 'param_error',
        'upstream_intercept_renew_reject.require'       => 'param_error',
        'upstream_intercept_renew_reject.in'            => 'param_error',
        'upstream_intercept_upgrade_notify.require'     => 'param_error',
        'upstream_intercept_upgrade_notify.in'          => 'param_error',
        'upstream_intercept_upgrade_reject.require'     => 'param_error',
        'upstream_intercept_upgrade_reject.in'          => 'param_error',

        # 全局按需设置
        'grace_time.require'                            => 'product_on_demand_grace_time_require',
        'grace_time.integer'                            => 'product_on_demand_grace_time_format_error',
        'grace_time.between'                            => 'product_on_demand_grace_time_format_error',
        'grace_time_unit.require'                       => 'product_on_demand_grace_time_unit_require',
        'grace_time_unit.in'                            => 'product_on_demand_grace_time_unit_error',
        'keep_time.require'                             => 'product_on_demand_keep_time_require',
        'keep_time.integer'                             => 'product_on_demand_keep_time_format_error',
        'keep_time.between'                             => 'product_on_demand_keep_time_format_error',
        'keep_time_unit.require'                        => 'product_on_demand_keep_time_unit_require',
        'keep_time_unit.in'                             => 'product_on_demand_keep_time_unit_error',

        # 主题配置设置
        'theme.require'                                 => 'theme_config_theme_require',
        'theme.max'                                     => 'theme_config_theme_max',
        'display_one.in'                                => 'theme_config_display_one_error',
        'display.in'                                    => 'theme_config_display_error',

        # 主题轮播图设置
        'theme_banner_theme.require'                    => 'theme_banner_theme_require',
        'theme_banner_theme.max'                        => 'theme_banner_theme_max',
        'theme_banner_img.require'                      => 'theme_banner_img_require',
        'theme_banner_img.max'                          => 'theme_banner_img_max',
        'theme_banner_url.require'                      => 'theme_banner_url_require',
        'theme_banner_url.max'                          => 'theme_banner_url_max',
        'theme_banner_url.url'                          => 'theme_banner_url_error',
        'theme_banner_start_time.require'               => 'theme_banner_start_time_require',
        'theme_banner_start_time.integer'               => 'theme_banner_start_time_error',
        'theme_banner_start_time.gt'                    => 'theme_banner_start_time_error',
        'theme_banner_end_time.require'                 => 'theme_banner_end_time_require',
        'theme_banner_end_time.integer'                 => 'theme_banner_end_time_error',
        'theme_banner_end_time.gt'                      => 'theme_banner_time_range_error',
        'theme_banner_show.require'                     => 'theme_banner_show_require',
        'theme_banner_show.in'                          => 'theme_banner_show_error',
        'theme_banner_notes.max'                        => 'theme_banner_notes_max',
        'theme_banner_order.integer'                    => 'theme_banner_order_error',
        'theme_banner_order.egt'                        => 'theme_banner_order_error',
        'login_register_redirect_text.requireIf'        => 'login_register_redirect_text_requireIf',
        'login_register_redirect_text.max'              => 'login_register_redirect_text_max',
        'login_register_redirect_url.requireIf'         => 'login_register_redirect_url_requireIf',
        'login_register_redirect_url.url'               => 'login_register_redirect_url_error',
        'login_register_redirect_blank.requireIf'       => 'login_register_redirect_blank_requireIf',
        'login_register_redirect_blank.in'              => 'login_register_redirect_blank_in',
    ];
    protected $scene = [
        'system_update' => ['lang_admin','lang_home_open','lang_home','maintenance_mode','website_name','website_url','terms_service_url','terms_privacy_url','system_logo','admin_logo','client_start_id_value','order_start_id_value','clientarea_url','home_show_deleted_host','clientarea_logo_url','clientarea_logo_url_blank','global_list_limit','donot_save_client_product_password','ip_white_list'],
        'login_update' => ['register_email','register_phone','login_phone_verify','home_login_check_ip','admin_login_check_ip','code_client_phone_register','code_client_phone_register','home_login_check_common_ip','home_login_ip_exception_verify','home_enforce_safe_method','admin_enforce_safe_method','admin_allow_remember_account','admin_enforce_safe_method_scene','login_email_password','admin_second_verify','admin_second_verify_method_default','prohibit_admin_bind_phone','prohibit_admin_bind_email','admin_password_or_verify_code_retry_times','admin_frozen_time','admin_login_expire_time','login_phone_password','home_login_expire_time','login_register_redirect_show','login_register_redirect_text','login_register_redirect_url','login_register_redirect_blank','admin_login_password_encrypt'],
        'security_update' => ['captcha_client_register','captcha_client_login','captcha_client_login_error','captcha_admin_login','captcha_client_verify','captcha_client_update','captcha_client_password_reset','captcha_client_oauth','captcha_admin_login_error','captcha_client_security_verify'],
        'currency_update' => ['currency_code','currency_prefix','recharge_open','recharge_min','recharge_max','recharge_notice','balance_notice_show','recharge_money_notice_content','recharge_pay_notice_content'],
        'cron_update' => 
	        [
			 'cron_due_suspend_day',
			'cron_due_terminate_day',
			'cron_due_renewal_first_day',
			'cron_due_renewal_second_day',
			'cron_overdue_first_day',
			'cron_overdue_second_day',
			'cron_overdue_third_day',
			'cron_ticket_close_day',
			'cron_due_suspend_swhitch',
			'cron_due_unsuspend_swhitch',
			'cron_due_terminate_swhitch',
			'cron_due_renewal_first_swhitch',
			'cron_due_renewal_second_swhitch',
			'cron_overdue_first_swhitch',
			'cron_overdue_second_swhitch',
			'cron_overdue_third_close_swhitch',
			'cron_ticket_swhitch',
			'cron_aff_swhitch',
			'cron_order_overdue_swhitch',
			'cron_order_overdue_day',
			'cron_order_unpaid_delete_swhitch',
			'cron_order_unpaid_delete_day',
			'cron_system_log_delete_swhitch',
            'cron_system_log_delete_day',
            'cron_sms_log_delete_swhitch',
            'cron_sms_log_delete_day',
            'cron_email_log_delete_swhitch',
            'cron_email_log_delete_day',
            'notice_independent_task_enabled',
			],
		'theme_update' => ['admin_theme', 'clientarea_theme', 'web_switch', 'web_theme','cart_theme', 'cart_instruction', 'cart_instruction_content', 'cart_change_product','home_theme','home_theme_mobile'],
		'certification_update' => [
		    'certification_open',
            'certification_approval',
            'certification_notice',
            'certification_update_client_name',
            'certification_update_client_phone',
            'certification_uncertified_suspended_host',
            'certification_upload'
        ],
        // 'info_update' => [
	       //  'enterprise_name',
	       //  'enterprise_telephone',
	       //  'enterprise_mailbox',
	       //  'enterprise_qrcode',
	       //  'online_customer_service_link',
	       //  'icp_info',
	       //  'icp_info_link',
	       //  'public_security_network_preparation',
	       //  'public_security_network_preparation_link',
	       //  'telecom_appreciation',
	       //  'copyright_info',
	       //  'official_website_logo',
	       //  'cloud_product_link',
	       //  'dcim_product_link',
        // ],
        'order_recycle_bin' => [
            'order_recycle_bin',
            'order_recycle_bin_save_days',
        ],
        'web' => [
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
        ],
        'cloud_server' => ['cloud_server_more_offers'],
        'physical_server' => ['physical_server_more_offers'],
        'icp' => ['icp_product_id'],
        'product' => ['self_defined_field_apply_range', 'custom_host_name_apply_range', 'product_overdue_not_delete_open', 'product_overdue_not_delete_product_ids', 'auto_renew_in_advance', 'auto_renew_in_advance_num', 'auto_renew_in_advance_unit'],
        'supplier_credit_amount' => [
            'supplier_credit_warning_notice',
            'supplier_credit_amount',
            'supplier_credit_push_frequency',
        ],
        'upstream_intercept' => [
            'upstream_intercept_new_order_notify',
            'upstream_intercept_new_order_reject',
            'upstream_intercept_renew_notify',
            'upstream_intercept_renew_reject',
            'upstream_intercept_upgrade_notify',
            'upstream_intercept_upgrade_reject',
        ],
        'global_on_demand' => [
            'grace_time',
            'grace_time_unit',
            'keep_time',
            'keep_time_unit',
        ],
        'theme_config_update' => ['display_one', 'display'],
        'theme_banner_create' => ['theme_banner_theme', 'theme_banner_img', 'theme_banner_url', 'theme_banner_start_time', 'theme_banner_end_time', 'theme_banner_show', 'theme_banner_notes'],
        'theme_banner_update' => ['theme_banner_theme', 'theme_banner_img', 'theme_banner_url', 'theme_banner_start_time', 'theme_banner_end_time', 'theme_banner_show', 'theme_banner_notes'],
        'theme_banner_show' => ['theme_banner_show'],
    ];

    public function checkEmailSuffix($suffix)
    {
    	$suffix = explode(',', $suffix);
    	foreach ($suffix as $key => $value) {
    		if(filter_var('test'.$value, FILTER_VALIDATE_EMAIL) === false){
    			return 'email_suffix_error';
    		}
    	}
    	return true;
    }

    public function checkHomeLoginIpExceptionVerify($value)
    {
    	$enable = ['operate_password','email_code','phone_code','certification'];
    	foreach($value as $v){
    		if(!in_array($v, $enable)){
    			return 'param_error';
    		}
    	}
    	return true;
    }

    public function checkHomeEnforceSafeMethod($value)
    {
    	$enable = ['phone','email','operate_password','certification','oauth'];
    	foreach($value as $v){
    		if(!in_array($v, $enable)){
    			return 'param_error';
    		}
    	}
    	return true;
    }

    public function checkAdminEnforceSafeMethod($value)
    {
    	$enable = ['operate_password'];
    	foreach($value as $v){
    		if(!in_array($v, $enable)){
    			return 'param_error';
    		}
    	}
    	return true;
    }

    public function checkAdminEnforceSafeMethodScene($value){
    	// 选择全部
    	if(in_array('all', $value)){
    		if(count($value) == 1){
    			return true;
    		}else{
    			return 'param_error';
    		}
    	}
    	$enable = ['client_delete','update_client_status','host_operate','order_delete','clear_order_recycle','plugin_uninstall_disable'];
    	foreach($value as $v){
    		if(!in_array($v, $enable)){
    			return 'param_error';
    		}
    	}
    	return true;
    }

    protected function checkRatioApplyRangeGroup($value,$rule,$data)
    {
        if (!empty($value)){
            $ProductGroupModel = new ProductGroupModel();
            $count = $ProductGroupModel->whereIn('id',$value)->where('parent_id'>0)->count();
            if ($count != count($value)){
                return 'product_group_is_not_exist';
            }
        }

        return true;
    }

    protected function checkRatioApplyRangeServer($value,$rule,$data)
    {
        if (!empty($value)){
            $ServerModel = new ServerModel();
            $count = $ServerModel->whereIn('id',$value)->count();
            if ($count != count($value)){
                return 'server_is_not_exist';
            }
        }

        return true;
    }

    protected function checkRatioApplyRangeProduct($value,$rule,$data)
    {
        if (!empty($value)){
            $ProductModel = new ProductModel();
            $count = $ProductModel->whereIn('id',$value)->count();
            if ($count != count($value)){
                return 'product_is_not_exist';
            }
        }

        return true;
    }

    protected function checkRatioApplyRangeUpstreamProduct($value,$rule,$data)
    {
    	if (!empty($value)){
            $UpstreamProductModel = new UpstreamProductModel();
            $count = $UpstreamProductModel->whereIn('product_id',$value)->count();
            if ($count != count($value)){
                return 'product_is_not_exist';
            }
            $ProductModel = new ProductModel();
            $count = $ProductModel->whereIn('id',$value)->count();
            if ($count != count($value)){
                return 'product_is_not_exist';
            }
        }

        return true;
    }

    /**
     * 验证IP白名单格式(支持单个IP、CIDR网段、IP范围)
     * @param string $whitelist IP白名单，换行分隔
     * @return bool|string
     */
    public function checkIpWhiteList($whitelist)
    {
        if (empty($whitelist)) {
            return true;
        }

        // 将白名单按换行符分割
        $ipList = array_filter(array_map('trim', explode("\n", $whitelist)));
        
        foreach ($ipList as $item) {
            if (empty($item)) {
                continue;
            }
            
            // 检查是否是CIDR格式
            if (strpos($item, '/') !== false) {
                if (!$this->isValidCidr($item)) {
                    return 'configuration_ip_white_list_format_error';
                }
            }
            // 检查是否是IP范围格式
            elseif (strpos($item, '-') !== false) {
                if (!$this->isValidIpRange($item)) {
                    return 'configuration_ip_white_list_format_error';
                }
            }
            // 单个IP验证
            else {
                if (!filter_var($item, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return 'configuration_ip_white_list_format_error';
                }
            }
        }
        
        return true;
    }

    /**
     * 验证后台登录IP白名单格式
     * @param string $whitelist IP白名单，换行分隔
     * @return bool|string
     */
    public function checkAdminLoginIpWhitelist($whitelist)
    {
        if (empty($whitelist)) {
            return true;
        }

        // 将白名单按换行符分割
        $ipList = array_filter(array_map('trim', explode("\n", $whitelist)));
        
        foreach ($ipList as $ip) {
            if (empty($ip)) {
                continue;
            }
            
            // 检查是否是CIDR格式
            if (strpos($ip, '/') !== false) {
                if (!$this->isValidCidr($ip)) {
                    return 'admin_login_ip_whitelist_format_error';
                }
            } else {
                // 单个IP验证
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return 'admin_login_ip_whitelist_format_error';
                }
            }
        }
        
        return true;
    }

    /**
     * 验证CIDR格式(仅支持IPv4)
     * @param string $cidr CIDR格式的网段
     * @return bool
     */
    private function isValidCidr($cidr)
    {
        $parts = explode('/', $cidr);
        if (count($parts) !== 2) {
            return false;
        }
        
        list($ip, $mask) = $parts;
        $mask = (int)$mask;
        
        // 验证IPv4地址
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        
        // 验证IPv4掩码范围 0-32
        return $mask >= 0 && $mask <= 32;
    }

    /**
     * 验证IP范围格式(仅支持IPv4)
     * @param string $range IP范围格式 如: 192.168.3.1-192.168.3.5
     * @return bool
     */
    private function isValidIpRange($range)
    {
        $parts = explode('-', $range);
        if (count($parts) !== 2) {
            return false;
        }
        
        list($startIp, $endIp) = array_map('trim', $parts);
        
        // 验证两个IP都是有效的IPv4地址
        if (!filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || 
            !filter_var($endIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        
        // 验证起始IP小于等于结束IP
        $startLong = ip2long($startIp);
        $endLong = ip2long($endIp);
        
        return $startLong !== false && $endLong !== false && $startLong <= $endLong;
    }

}
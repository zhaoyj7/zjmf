<?php
namespace app\common\model;

use app\admin\model\AdminModel;
use app\admin\model\PluginModel;
use think\Exception;
use think\Model;
use think\facade\Db;
use app\common\logic\TemplateLogic;
use app\common\logic\ModuleLogic;

/**
 * @title 用户模型
 * @desc 用户模型
 * @use app\common\model\ConfigurationModel
 */
class ConfigurationModel extends Model
{

    protected $name = 'configuration';
    protected $pk = 'setting';
    private $config=[
        'system'=>[
            'lang_admin',
            'lang_home',
            'lang_home_open',
            'maintenance_mode',
            'maintenance_mode_message',
            'website_name',
            'website_url',
            'terms_service_url',
            'terms_privacy_url',
            'system_logo',
            'admin_logo',
            'client_start_id_value',
            'order_start_id_value',
            'clientarea_url',
            'www_url',
            'tab_logo',
            'home_show_deleted_host',
            'prohibit_user_information_changes',
            'clientarea_logo_url',
            'clientarea_logo_url_blank',
            'ip_white_list',
            'global_list_limit',
            'donot_save_client_product_password',
        ],
        'login'=>[
            'login_phone_verify',
            'register_email',
            'register_phone',
            'home_login_check_ip',
            'admin_login_check_ip',
            'code_client_email_register',
            'code_client_phone_register',
            'limit_email_suffix',
            'email_suffix',
            'home_login_check_common_ip',
            'home_login_ip_exception_verify',
            'home_enforce_safe_method',
            'admin_enforce_safe_method',
            'admin_allow_remember_account',
            'admin_enforce_safe_method_scene',
            'first_login_method',
            'first_password_login_method',
            'login_email_password',
            'admin_second_verify',
            'admin_second_verify_method_default',
            'prohibit_admin_bind_phone',
            'prohibit_admin_bind_email',
            'admin_password_or_verify_code_retry_times',
            'admin_frozen_time',
            'admin_login_expire_time',
            'first_login_type',
            'login_phone_password',
            'home_login_expire_time',
            'admin_login_ip_whitelist',
            'login_register_redirect_show',
            'login_register_redirect_text',
            'login_register_redirect_url',
            'login_register_redirect_blank',
            'admin_login_password_encrypt',
            'exception_login_certification_plugin'
        ],
        'security'=>[
            'captcha_client_register',
            'captcha_client_login',
            'captcha_admin_login',
            'captcha_client_login_error',
            'captcha_admin_login_error',
            'captcha_plugin',
            'code_client_email_register',
            'captcha_client_verify',
            'captcha_client_update',
            'captcha_client_password_reset',
            'captcha_client_oauth',
            'captcha_client_security_verify',
        ],
        'currency'=>[
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'recharge_open',
            'recharge_min',
            'recharge_max',
            'recharge_notice',
            'balance_notice_show',
            'recharge_money_notice_content',
            'recharge_pay_notice_content',
            'recharge_order_support_refund',
        ],
        'cron'=>[
            'cron_due_suspend_swhitch',
            'cron_due_suspend_day',
            'cron_due_unsuspend_swhitch',
            'cron_due_terminate_swhitch',
            'cron_due_terminate_day',
            'cron_due_renewal_first_swhitch',
            'cron_due_renewal_second_swhitch',
            'cron_due_renewal_first_day',
            'cron_due_renewal_second_day',
            'cron_overdue_first_swhitch',
            'cron_overdue_second_swhitch',
            'cron_overdue_third_swhitch',
            'cron_overdue_first_day',
            'cron_overdue_second_day',
            'cron_overdue_third_day',
            'cron_ticket_close_swhitch',
            'cron_ticket_close_day',
            'cron_aff_swhitch',
            'cron_order_overdue_swhitch',
            'cron_order_overdue_day',
            'cron_task_shell',
            'cron_task_status',
            'cron_day_start_time',
            'cron_order_unpaid_delete_swhitch',
            'cron_order_unpaid_delete_day',
            'cron_system_log_delete_swhitch',
            'cron_system_log_delete_day',
            'cron_sms_log_delete_swhitch',
            'cron_sms_log_delete_day',
            'cron_email_log_delete_swhitch',
            'cron_email_log_delete_day',
            'task_fail_retry_open',
            'task_fail_retry_times',
            'notice_independent_task_enabled',
        ],
        'send'=>[
            'send_sms',
            'send_sms_global',
            'send_email',
        ],
        'theme' => [
            'admin_theme',
            'clientarea_theme',
            'web_switch',
            'web_theme',
            'cart_theme',
            'clientarea_theme_mobile_switch',
            'first_navigation',
            'second_navigation',
            'clientarea_theme_mobile',
            'cart_theme_mobile',
            'cart_instruction', 
            'cart_instruction_content',
            'cart_change_product',
            'home_theme',
            'home_theme_mobile',
            'home_host_theme',
            'home_host_theme_mobile',
        ],
        'certification' => [
            'certification_open',
            'certification_approval',
            'certification_notice',
            'certification_update_client_name',
            'certification_upload',
            'certification_update_client_phone',
            'certification_uncertified_suspended_host',
        ],
        'info' => [
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
        ],
        'debug' => [
            'debug_model_auth',
            'debug_model_expire_time'
        ],
        'oss' => [
            'oss_method',
            'oss_sms_plugin',
            'oss_sms_plugin_template',
            'oss_sms_plugin_admin',
            'oss_mail_plugin',
            'oss_mail_plugin_template',
            'oss_mail_plugin_admin',
        ],
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
        'cloud_server' => [
            'cloud_server_more_offers'
        ],
        'physical_server' => [
            'physical_server_more_offers'
        ],
        'icp' => [
            'icp_product_id'
        ],
        'product' => [
            'self_defined_field_apply_range',
            'custom_host_name_apply_range',
            'product_duration_group_presets_open',
            'product_duration_group_presets_apply_range',
            'product_duration_group_presets_default_id',
            'product_new_host_renew_with_ratio_open',
            'product_new_host_renew_with_ratio_apply_range',
            'product_new_host_renew_with_ratio_apply_range_2',
            'product_new_host_renew_with_ratio_apply_range_1',
            'product_global_renew_rule',
            'product_global_show_base_info',
            'product_renew_with_new_open',
            'product_renew_with_new_product_ids',
            'product_overdue_not_delete_open',
            'product_overdue_not_delete_product_ids',
            'host_sync_due_time_open',
            'host_sync_due_time_apply_range',
            'host_sync_due_time_product_ids',
            'auto_renew_in_advance',
            'auto_renew_in_advance_num',
            'auto_renew_in_advance_unit',
        ],
        'credit_warning' => [
            'supplier_credit_warning_notice',
            'supplier_credit_amount',
            'supplier_credit_push_frequency',
        ],
        'global_on_demand' => [
            'grace_time',
            'grace_time_unit',
            'keep_time',
            'keep_time_unit',
        ],
    ];
    
    /**
     * 时间 2022-5-10
     * @title 获取所有配置项数据
     * @desc 获取所有配置项数据
     * @author xiong
     * @version v1
     * @return string [].setting - 配置项名称
     * @return string [].value - 配置项值
     */
    public function index()
    {
        // 优化：使用缓存减少数据库查询（缓存30分钟）
        $cacheKey = \app\common\logic\CacheLogic::CACHE_CONFIGURATION;
        $configurations = idcsmart_cache($cacheKey);
        
        if (empty($configurations)){
            $configurations = $this->field('setting,value')->select()->toArray();
            // 缓存30分钟
            idcsmart_cache($cacheKey, $configurations, 1800);
        }
        
        return $configurations;
    }

    /**
     * 时间 2022-5-10
     * @title 保存配置项数据
     * @desc 保存配置项数据
     * @author xiong
     * @version v1
     * @return string setting - 配置项名称
     * @return string value - 配置项值
     */
    public function saveConfiguration($param)
    {
        $setting = $param['setting'] ?? '';
        $value = $param['value'] ?? '';
        if(!empty($setting)){
            $configuration = $this->index();
            $this->startTrans();
            try {
                if(!in_array($setting, array_column($configuration, 'setting'))){
                    $this->create([
                        'setting' => $setting,
                        'value' => $value,
                        'description' => $setting,
                        'create_time' => time()
                    ]);
                }else{
                    $this->update([
                        'value' => $value,
                        'update_time' => time()
                    ], ['setting' => $setting]);
                }
                $this->commit();
                
                // 清除配置缓存
                idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('fail_message')];
            }
        }else{
            return ['status' => 400, 'msg' => lang('param_error')];
        }
        
    }

    /**
     * 时间 2022-5-10
     * @title 获取系统设置
     * @desc 获取系统设置
     * @author xiong
     * @version v1
     * @return  string lang_admin - 后台默认语言
     * @return  int lang_home_open - 前台多语言开关:1开启0关闭
     * @return  string lang_home - 前台默认语言
     * @return  int maintenance_mode - 维护模式开关:1开启0关闭
     * @return  string maintenance_mode_message - 维护模式内容
     * @return  string website_name - 网站名称
     * @return  string website_url - 网站域名地址
     * @return  string terms_service_url - 服务条款地址
     * @return  string terms_privacy_url - 隐私条款地址
     * @return  string system_logo - 系统LOGO
     * @return  int client_start_id_value - 用户注册开始ID
     * @return  int order_start_id_value - 用户订单开始ID
     * @return  string clientarea_url - 会员中心地址
     * @return  string www_url - 官网地址
     * @return  string tab_logo - 标签页LOGO
     * @return  int home_show_deleted_host - 前台是否展示已删除产品:1是0否
     * @return  array prohibit_user_information_changes - 禁止用户信息变更
     * @return  array user_information_fields - 用户信息字段
     * @return  int|string user_information_fields.id - 用户信息字段ID
     * @return  string user_information_fields.name - 用户信息字段名称
     * @return  string clientarea_logo_url - 会员中心LOGO跳转地址
     * @return  int clientarea_logo_url_blank - 会员中心LOGO跳转是否打开新页面:1是0否
     * @return  array customfield - 自定义参数
     * @return  string ip_white_list - IP白名单
     * @return  int global_list_limit - 全局列表展示条数
     * @return  int donot_save_client_product_password - 不保存用户产品密码:1是0否
     */
    public function systemList()
    {
        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['system'])){
                if($v['setting'] == 'lang_home_open' || $v['setting'] == 'maintenance_mode' || $v['setting'] == 'client_start_id_value' || $v['setting'] == 'order_start_id_value' || $v['setting']=='home_show_deleted_host' || $v['setting']=='clientarea_logo_url_blank' || $v['setting'] == 'global_list_limit' || $v['setting']=='donot_save_client_product_password'){
                    $data[$v['setting']] = (int)$v['value'];
                }elseif ($v['setting']=='system_logo' || $v['setting'] == 'tab_logo' || $v['setting'] == 'admin_logo'){
                    $data[$v['setting']] = config('idcsmart.system_logo_url') . $v['value'];
                }elseif ($v['setting']=='prohibit_user_information_changes'){
                    $data[$v['setting']] = array_filter(explode(',', $v['value']));
                    foreach ($data[$v['setting']] as $key => $value) {
                        if(!in_array($value, ['phone', 'email', 'password'])){
                            $data[$v['setting']][$key] = (int)$value;
                        }
                    }
                }else{
                    $data[$v['setting']] = (string)$v['value'];
                }
            }
        }

        $lang = lang();
        $data['user_information_fields'] = [
            ['id' => 'phone', 'name' => $lang['prohibit_user_information_changes_phone']],
            ['id' => 'email', 'name' => $lang['prohibit_user_information_changes_email']],
            ['id' => 'password', 'name' => $lang['prohibit_user_information_changes_password']],
        ];

        $data['customfield'] = [];
        $hookRes = hook('configuration_system_list');
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 200){
                $data['customfield'] = array_merge($data['customfield'], $v['data'] ?? []);
                if(isset($v['data']['client_custom_field']['list2']) && !empty($v['data']['client_custom_field']['list2'])){
                    $data['user_information_fields'] = array_values(array_merge($data['user_information_fields'], $v['data']['client_custom_field']['list2']));
                }
            }
        }

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

        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 保存系统设置
     * @desc 保存系统设置
     * @author xiong
     * @version v1
     * @param  string lang_admin - 后台默认语言
     * @param  int lang_home_open - 前台多语言开关:1开启0关闭
     * @param  string lang_home - 前台默认语言
     * @param  int maintenance_mode - 维护模式开关:1开启0关闭
     * @param  string maintenance_mode_message - 维护模式内容
     * @param  string website_name - 网站名称
     * @param  string website_url - 网站域名地址
     * @param  string terms_service_url - 服务条款地址
     * @param  string terms_privacy_url - 隐私条款地址
     * @param  string system_logo - 系统LOGO
     * @param  int client_start_id_value - 用户注册开始ID
     * @param  int order_start_id_value - 用户订单开始ID
     * @param  string clientarea_url - 会员中心地址
     * @param  string www_url - 官网地址
     * @param  string tab_logo - 标签页LOGO
     * @param  int home_show_deleted_host - 前台是否展示已删除产品:1是0否
     * @param  array prohibit_user_information_changes - 禁止用户信息变更
     * @param  string clientarea_logo_url - 会员中心LOGO跳转地址
     * @param  int clientarea_logo_url_blank - 会员中心LOGO跳转是否打开新页面:1是0否
     * @param  array customfield - 自定义参数
     * @param  int global_list_limit - 全局列表展示条数
     * @param int donot_save_client_product_password - 不保存用户产品密码:1是0否
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function systemUpdate($param)
    {
        $admin = array_column(lang_list('admin'),'display_lang','display_lang');
        $home =  array_column(lang_list('home'),'display_lang','display_lang');
        if(empty($admin[$param['lang_admin']])){
            return ['status' => 400, 'msg' => lang('configuration_admin_default_language_error')];
        }
        if(empty($admin[$param['lang_home']])){
            return ['status' => 400, 'msg' => lang('configuration_home_default_language_error')];
        }
        
        // 判断是否专业版，只有专业版才允许修改admin_logo
        $info = configuration('idcsmartauthinfo');
        $edition = 0;
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
            if (isset($auth['version']) && in_array($auth['version'],[1,3])){
                $edition = 1;
            }
        }
        
        // 非专业版不允许提交admin_logo参数
        if($edition == 0 && isset($param['admin_logo'])){
            unset($param['admin_logo']);
        }
        
        $hookRes = hook('before_configuration_system_update', $param);
        foreach ($hookRes as $k => $v) {
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $customfield = $param['customfield'] ?? []; // 自定义参数
        if(isset($param['customfield'])) unset($param['customfield']); // 不删除报错
        if(isset($param['user_information_fields'])) unset($param['user_information_fields']); // 不删除报错

        $param['lang_home_open'] = intval($param['lang_home_open']);
        $param['maintenance_mode'] = intval($param['maintenance_mode']);
        $param['system_logo'] = explode('/',$param['system_logo'])[count(explode('/',$param['system_logo']))-1];
        if(isset($param['admin_logo']) && !empty($param['admin_logo'])){
            $param['admin_logo'] = explode('/',$param['admin_logo'])[count(explode('/',$param['admin_logo']))-1];
        }
        if(isset($param['tab_logo']) && !empty($param['tab_logo'])){
            $param['tab_logo'] = explode('/',$param['tab_logo'])[count(explode('/',$param['tab_logo']))-1];
        }
        $param['home_show_deleted_host'] = intval($param['home_show_deleted_host']);
        $param['prohibit_user_information_changes'] = implode(',', $param['prohibit_user_information_changes']);
        $param['donot_save_client_product_password'] = intval($param['donot_save_client_product_password']);

        # 日志
        $description = [];
        $systemList = $this->systemList();
        $systemList['prohibit_user_information_changes'] = implode(',', $systemList['prohibit_user_information_changes']);
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='lang_home_open'){
                $lang_old = lang("configuration_log_lang_home_open_{$systemList[$k]}");
                $lang_new = lang("configuration_log_lang_home_open_{$v}");
            }else if($k=='maintenance_mode'){
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }else if($k=='home_show_deleted_host'){
                $lang_old = lang("configuration_log_whether_{$systemList[$k]}");
                $lang_new = lang("configuration_log_whether_{$v}");
            }else if($k=='donot_save_client_product_password'){
                $lang_old = lang("configuration_log_whether_{$systemList[$k]}");
                $lang_new = lang("configuration_log_whether_{$v}");
            }else if($k == 'customfield'){
                // 排除
                continue;
            }else{
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);


        $this->startTrans();
        try {
            foreach($this->config['system'] as $v){
                if ($edition==0 && $v=='admin_logo'){
                    continue;
                }
                $list[]=[
                    'setting'=>$v,
                    'value'=> $param[$v],
                ];
            }
            if($param['client_start_id_value']!=$systemList['client_start_id_value']){
                $res = Db::query("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name='".config('database.connections.mysql.prefix')."client'");
                $AUTO_INCREMENT = $res[0]['AUTO_INCREMENT'] ?? 1; 
                if($AUTO_INCREMENT<$param['client_start_id_value']){
                    $AUTO_INCREMENT = $param['client_start_id_value'];
                    Db::execute("ALTER TABLE ".config('database.connections.mysql.prefix')."client AUTO_INCREMENT={$AUTO_INCREMENT};");
                }
            }
            if($param['order_start_id_value']!=$systemList['order_start_id_value']){
                $res = Db::query("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name='".config('database.connections.mysql.prefix')."order'");
                $AUTO_INCREMENT = $res[0]['AUTO_INCREMENT'] ?? 1; 
                if($AUTO_INCREMENT<$param['order_start_id_value']){
                    $AUTO_INCREMENT = $param['order_start_id_value'];
                    Db::execute("ALTER TABLE ".config('database.connections.mysql.prefix')."order AUTO_INCREMENT={$AUTO_INCREMENT};");
                }
            }
            if($param['lang_admin']!=$systemList['lang_admin'] || $param['lang_home']!=$systemList['lang_home']){
                lang('success_message', [], true);
            }

            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_system', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);

            if(isset($param['tab_logo']) && !empty($param['tab_logo']) && file_exists(UPLOAD_DEFAULT.$param['tab_logo'])){
                copy(UPLOAD_DEFAULT.$param['tab_logo'], WEB_ROOT.'favicon.ico'); 
            }

            if (empty($param['lang_home_open'])){
                $ClientModel = new ClientModel();
                $clientIds = $ClientModel->column('id');
                $chunkClientIds = array_chunk($clientIds,1000);
                foreach ($chunkClientIds as $chunkClientId){
                    $ClientModel->whereIn('id', $chunkClientId)->update(['language'=>$param['lang_home']]);
                }
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);

        $param['customfield'] = $customfield;
        hook('after_configuration_system_update', $param);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 获取登录设置
     * @desc 获取登录设置
     * @author xiong
     * @version v1
     * @return  int register_email - 邮箱注册开关:1开启0关闭
     * @return  int register_phone - 是否允许手机号注册/密码登录:1开启0关闭
     * @return  int login_phone_verify - 是否支持手机验证码登录:1开启0关闭
     * @return  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @return  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     * @return  int code_client_email_register - 邮箱注册是否需要验证码:1开启0关闭
     * @return  int code_client_phone_register - 手机注册是否需要验证码:1开启0关闭
     * @return  int limit_email_suffix - 是否限制邮箱后缀:1开启0关闭
     * @return  string email_suffix - 邮箱后缀
     * @return  int home_login_check_common_ip - 前台是否检测常用登录IP:1开启0关闭
     * @return  array home_login_ip_exception_verify - 用户异常登录验证方式(operate_password=操作密码,email_code=邮箱验证码,phone_code=手机验证码,certification=实名校验)
     * @return  array home_enforce_safe_method - 前台强制安全选项(phone=手机,email=邮箱,operate_password=操作密码,certification=实名认证,oauth=三方登录扫码)
     * @return  array admin_enforce_safe_method - 后台强制安全选项(operate_password=操作密码)
     * @return  int admin_allow_remember_account - 后台是否允许记住账号:1开启0关闭
     * @return  array admin_enforce_safe_method_scene - 后台强制安全选项场景(all=全部,client_delete=用户删除,update_client_status=用户停启用,host_operate=产品相关操作,order_delete=订单删除,clear_order_recycle=清空回收站,plugin_uninstall_disable=插件卸载/禁用)
     * @return  string first_login_method - 账户凭证首选登录方式(code=验证码,password=密码)
     * @return  string first_password_login_method - 密码登录首选(phone=手机,email=邮箱)
     * @return  int login_email_password - 是否开启邮箱密码登录:1开启0关闭
     * @return  int admin_second_verify - 二次验证:1开启0关闭
     * @return  string admin_second_verify_method_default - 首选二次验证方式:sms短信email邮件totp
     * @return  int prohibit_admin_bind_phone - 禁止后台用户自助绑定手机号:1是0否
     * @return  int prohibit_admin_bind_email - 禁止后台用户自助绑定邮箱:1是0否
     * @return  int admin_password_or_verify_code_retry_times - 密码或验证码重试次数
     * @return  int admin_frozen_time - 冻结时间,分钟
     * @return  int admin_login_expire_time - 登录有效期,分钟
     * @return  string first_login_type - 首选登录方式
     * @return  array first_login_type_list - 首选登录方式列表
     * @return  string first_login_type_list[].value - 首选登录方式标识
     * @return  string first_login_type_list[].name - 首选登录方式名称
     * @return  int login_phone_password - 是否开启手机密码登录:1开启0关闭
     * @return  int home_login_expire_time - 前台登录有效期,分钟
     */
    public function loginList()
    {
        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['login'])){
                if($v['setting']=='email_suffix' || $v['setting']=='admin_login_ip_whitelist' || $v['setting']=='login_register_redirect_text' || $v['setting']=='login_register_redirect_url' || $v['setting']=='exception_login_certification_plugin'){
                    $data[$v['setting']] = (string)$v['value'];
                }else if(in_array($v['setting'], ['home_login_ip_exception_verify','home_enforce_safe_method','admin_enforce_safe_method','admin_enforce_safe_method_scene'])){
                    $data[$v['setting']] = !empty($v['value']) ? explode(',', $v['value']) : [];
                }else if (in_array($v['setting'],['first_login_method','first_password_login_method','admin_second_verify_method_default','first_login_type'])){
                    $data[$v['setting']] = $v['value'];
                } else{
                    $data[$v['setting']] = (int)$v['value']; 
                }
            }
        }
        // 追加可选登录方式
        $firstLoginTypeList = $this->getFirstLoginTypeList();
        $data['first_login_type_list'] = $firstLoginTypeList;
        $firstLoginTypeList = array_column($firstLoginTypeList, 'name', 'value');
        $data['first_login_type'] = !empty($firstLoginTypeList[ $data['first_login_type'] ]) ? $data['first_login_type'] : 'account_login';

        return $data;
    }

    /**
     * 时间 2022-5-10
     * @title 保存登录设置
     * @desc 保存登录设置
     * @author xiong
     * @version v1
     * @param  int register_email - 邮箱注册开关:1开启0关闭
     * @param  int register_phone - 是否允许手机号注册/密码登录:1开启0关闭
     * @param  int login_phone_verify - 是否支持手机验证码登录:1开启0关闭
     * @param  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @param  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     * @param  int code_client_email_register - 邮箱注册是否需要验证码:1开启0关闭
     * @param  int code_client_phone_register - 手机注册是否需要验证码:1开启0关闭
     * @param  int limit_email_suffix - 是否限制邮箱后缀:1开启0关闭
     * @param  string email_suffix - 邮箱后缀
     * @param  int home_login_check_common_ip - 前台是否检测常用登录IP:1开启0关闭
     * @param  array home_login_ip_exception_verify - 用户异常登录验证方式(operate_password=操作密码,email_code=邮箱验证码,phone_code=手机验证码,certification=实名校验)
     * @param  array home_enforce_safe_method - 前台强制安全选项(phone=手机,email=邮箱,operate_password=操作密码,certification=实名认证,oauth=三方登录扫码)
     * @param  array admin_enforce_safe_method - 后台强制安全选项(operate_password=操作密码)
     * @param  int admin_allow_remember_account - 后台是否允许记住账号:1开启0关闭
     * @param  array admin_enforce_safe_method_scene - 后台强制安全选项场景(all=全部,client_delete=用户删除,update_client_status=用户停启用,host_operate=产品相关操作,order_delete=订单删除,clear_order_recycle=清空回收站,plugin_uninstall_disable=插件卸载/禁用)
     * @param  string first_login_method - 首选登录方式(code=验证码,password=密码)
     * @param  string first_password_login_method - 密码登录首选(phone=手机,email=邮箱)
     * @param  int login_email_password - 是否开启邮箱登录:1开启0关闭
     * @param  int admin_second_verify - 二次验证:1开启0关闭
     * @param  string admin_second_verify_method_default - 首选二次验证方式:sms短信email邮件totp
     * @param  int prohibit_admin_bind_phone - 禁止后台用户自助绑定手机号:1是0否
     * @param  int prohibit_admin_bind_email - 禁止后台用户自助绑定邮箱:1是0否
     * @param  int admin_password_or_verify_code_retry_times - 密码或验证码重试次数
     * @param  int admin_frozen_time - 冻结时间,分钟
     * @param  int admin_login_expire_time - 登录有效期,分钟
     * @param  string first_login_type - 首选登录方式
     * @param  int login_phone_password - 是否开启手机密码登录:1开启0关闭
     * @param  int home_login_expire_time - 前台登录有效期,分钟
     */
    public function loginUpdate($param)
    {
        foreach($param as $k=>$v){
            if($k=='email_suffix' || $k=='admin_login_ip_whitelist' || $k=='login_register_redirect_text' || $k=='login_register_redirect_url' || $k=='exception_login_certification_plugin'){
                $param[$k] = (string)$v;
            }else if(in_array($k, ['home_login_ip_exception_verify','home_enforce_safe_method','admin_enforce_safe_method','admin_enforce_safe_method_scene','first_login_method','first_password_login_method','admin_second_verify_method_default','first_login_type'])){

            }else{
                $param[$k] = (int)$v;
            }
        }
        # 日志
        $description = [];
        $loginList = $this->loginList();
        $fistLoginTypeList = $loginList['first_login_type_list'];
        $fistLoginTypeList = array_column($fistLoginTypeList, 'name', 'value');
        unset($loginList['first_login_type_list']);

        foreach($loginList as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';

            if(in_array($k, ['email_suffix','admin_password_or_verify_code_retry_times','admin_frozen_time','admin_login_expire_time','home_login_expire_time','admin_login_ip_whitelist','login_register_redirect_text','login_register_redirect_url'])){
                $lang_old = $v;
                $lang_new = $param[$k];
            }else if(in_array($k, ['home_login_ip_exception_verify','home_enforce_safe_method','admin_enforce_safe_method'])){
                if(!empty($v)){
                    $lang_old = [];
                    foreach($loginList[$k] as $vv){
                        $lang_old[] = lang('configuration_login_safe_method_'.$vv);
                    }
                    $lang_old = implode(',', $lang_old);
                }else{
                    $lang_old = lang('configuration_none');
                }
                if(!empty($param[$k])){
                    $lang_new = [];
                    foreach($param[$k] as $vv){
                        $lang_new[] = lang('configuration_login_safe_method_'.$vv);
                    }
                    $lang_new = implode(',', $lang_new);
                }else{
                    $lang_new = lang('configuration_none');
                }
            }else if($k == 'admin_enforce_safe_method_scene'){
                $lang_old = [];
                foreach($loginList[$k] as $vv){
                    $lang_old[] = lang('configuration_admin_enforce_safe_method_scene_'.$vv);
                }
                $lang_old = implode(',', $lang_old);

                $lang_new = [];
                foreach($param[$k] as $vv){
                    $lang_new[] = lang('configuration_admin_enforce_safe_method_scene_'.$vv);
                }
                $lang_new = implode(',', $lang_new);
            }else if($k == 'admin_second_verify_method_default'){
                $lang_old = lang("configuration_admin_second_verify_method_{$v}");
                $lang_new = lang("configuration_admin_second_verify_method_{$param[$k]}");
            }else if($k == 'admin_second_verify'){
                $lang_old = lang("configuration_log_switch_{$v}");
                $lang_new = lang("configuration_log_switch_{$param[$k]}");
            }else if($k == 'first_login_type'){
                if(isset($param[$k])){
                    if(empty($fistLoginTypeList[ $param[$k] ])){
                        return ['status'=>400, 'msg'=>lang('configuration_first_login_type_error') ];
                    }
                    $lang_old = $fistLoginTypeList[ $v ] ?? '';
                    $lang_new = $fistLoginTypeList[ $param[$k] ];
                }else{
                    $lang_old = $lang_new = '';
                    $param[$k] = $v;
                }
            }else{
                $lang_old = lang("configuration_log_whether_{$v}");
                $lang_new = lang("configuration_log_whether_{$param[$k]}");
            }
            if($lang_old == $lang_new){
                continue;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }

        $this->startTrans();
        try {
            foreach($this->config['login'] as $v){
                if(in_array($v, ['home_login_ip_exception_verify','home_enforce_safe_method','admin_enforce_safe_method','admin_enforce_safe_method_scene'])){
                    $param[$v] = implode(',', $param[$v]);
                }
                $list[]=[
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description){
                $description = implode(',', $description);
                active_log(lang('admin_configuration_login', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            }
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }
    /**
     * 时间 2022-5-10
     * @title 获取验证码设置
     * @desc 获取验证码设置
     * @author xiong
     * @version v1
     * @return  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @return  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login_error - 管理员登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_width - 图形验证码宽度
     * @return  int captcha_height - 图形验证码高度
     * @return  int captcha_length - 图形验证码字符长度
     * @return  int code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     * @return  int captcha_client_verify - 验证手机/邮箱图形验证码开关:1开启0关闭
     * @return  int captcha_client_update - 修改手机/邮箱图形验证码开关:1开启0关闭
     * @return  int captcha_client_password_reset - 重置密码图形验证码开关:1开启0关闭
     * @return  int captcha_client_oauth - 三方登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_security_verify - 安全校验图形验证码开关:1开启0关闭
     */
    public function securityList()
    {

        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['security'])){
                if($v=="captcha_width" || $v=="captcha_height"){
                    $data[$v['setting']] = (float)$v['value'];
                } else{
                    $data[$v['setting']] = $v['value'];
                }
            }
        }
        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 保存验证码设置
     * @desc 保存验证码设置
     * @author xiong
     * @version v1
     * @param  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @param  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @param  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @param  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @param  int captcha_admin_login_error - 管理员登录失败图形验证码开关:1开启0关闭
     * @param  string captcha_plugin - 验证码插件(从/admin/v1/captcha_list接口获取)
     * @param  int code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     * @param  int captcha_client_verify - 验证手机/邮箱图形验证码开关:1开启0关闭
     * @param  int captcha_client_update - 修改手机/邮箱图形验证码开关:1开启0关闭
     * @param  int captcha_client_password_reset - 重置密码图形验证码开关:1开启0关闭
     * @param  int captcha_client_oauth - 三方登录图形验证码开关:1开启0关闭
     * @param  int captcha_client_security_verify - 安全校验图形验证码开关:1开启0关闭
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function securityUpdate($param)
    {
        if (!empty($param['captcha_plugin'])){
            $PluginModel = new PluginModel();
            $captchaPlugin = $PluginModel->where('name',$param['captcha_plugin'])->where('module','captcha')->where('status',1)->find();
            if (empty($captchaPlugin)){
                return ['status'=>400,'msg'=>lang('plugin_is_not_exist')];
            }
        }

        # 日志
        $description = [];
        $systemList = $this->securityList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='captcha_width' || $k=='captcha_height' || $k=='captcha_length'){
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }else if($k=='captcha_client_login_error'){
                $lang_old = lang("configuration_log_captcha_client_login_error_{$systemList[$k]}");
                $lang_new = lang("configuration_log_captcha_client_login_error_{$v}");
            }else if($k=='captcha_admin_login_error'){
                $lang_old = lang("configuration_log_captcha_admin_login_error_{$systemList[$k]}");
                $lang_new = lang("configuration_log_captcha_admin_login_error_{$v}");
            }else{
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {
            foreach($this->config['security'] as $v){
                $param[$v] = $v=='captcha_plugin'?$param[$v]:intval($param[$v]??0);
                $list[]=[
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_security', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail') . ':' . $e->getMessage()];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }
    /**
     * 时间 2022-5-10
     * @title 获取货币设置
     * @desc 获取货币设置
     * @author xiong
     * @version v1
     * @return  string currency_code - 货币代码
     * @return  string currency_prefix - 货币符号
     * @return  string currency_suffix - 货币后缀
     * @return  int recharge_open - 启用充值:1开启0关闭
     * @return  int recharge_min - 单笔最小金额
     * @return  int recharge_notice - 充值提示开关:1开启0关闭
     * @return  string recharge_money_notice_content - 充值金额提示内容
     * @return  string recharge_pay_notice_content - 充值支付提示内容
     * @return  int recharge_order_support_refund - 充值订单是否支持退款(0=否,1=是)
     */
     public function currencyList(): array
    {

        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['currency'])){
                if($v['setting'] == 'recharge_open' || $v['setting'] == 'recharge_min' || $v['setting'] == 'recharge_max'){
                    $data[$v['setting']] = (float)$v['value'];
                }else if($v['setting'] == 'recharge_notice' || $v['setting'] == 'balance_notice_show' || $v['setting'] == 'recharge_order_support_refund'){
                    $data[$v['setting']] = (int)$v['value'];
                }else{
                    $data[$v['setting']] = (string)$v['value'];
                }
            }
        }
        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 保存货币设置
     * @desc 保存货币设置
     * @author xiong
     * @version v1
     * @param  string param.currency_code - 货币代码
     * @param  string param.currency_prefix - 货币符号
     * @param  string param.currency_suffix - 货币后缀
     * @param  int param.recharge_open - 启用充值:1开启0关闭
     * @param  int param.recharge_min - 单笔最小金额
     * @param  int param.recharge_max - 单笔最大金额
     * @param  int param.recharge_notice - 充值提示开关:1开启0关闭
     * @param  string param.recharge_money_notice_content - 充值金额提示内容
     * @param  string param.recharge_pay_notice_content - 充值支付提示内容
     * @return int param.recharge_order_support_refund - 充值订单是否支持退款(0=否,1=是)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function currencyUpdate($param)
    {
        # 日志
        $description = [];
        $systemList = $this->currencyList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='recharge_open' || $k == 'recharge_notice' || $k == 'balance_notice_show' || $k == 'recharge_order_support_refund'){
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }else{
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {
            foreach($this->config['currency'] as $v){
                if($v == 'recharge_min'){
                    $param[$v] = round($param[$v],2);
                }else if($v == 'recharge_max'){
                    $param[$v] = round($param[$v],2);
                }else if($v == 'recharge_open' || $v == 'recharge_notice' || $v == 'balance_notice_show' || $v == 'recharge_order_support_refund'){
                    $param[$v] = intval($param[$v]);
                }else if($v == 'recharge_money_notice_content' || $v == 'recharge_pay_notice_content'){
                    $param[$v] = $param[$v] ?? '';
                }

                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }

            if($param['currency_code']!=$systemList['currency_code']){
                $SupplierModel = new SupplierModel();
                $supplier = $SupplierModel->select()->toArray();
                $rateList = getRate();
                $localCurrency = $param['currency_code'];

                foreach ($supplier as $key => $value) {
                    $upstreamCurrency = $value['currency_code'];

                    if ($localCurrency == $upstreamCurrency){
                        $rate = 1;
                    }else{
                        if(isset($rateList[$upstreamCurrency])){
                            $rate = bcdiv($rateList[$localCurrency], $rateList[$upstreamCurrency], 5); # 需要高精度
                        }else{
                            $rate = 1;
                        }
                    }
                    
                    $SupplierModel->update([
                        'currency_code' => $upstreamCurrency,
                        'rate' => $rate,
                        'rate_update_time' => $rate!=$value['rate'] ? time() : $value['rate_update_time'],
                    ], ['id' => $value['id']]);
                }
            }

            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_currency', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }
    /**
     * 时间 2022-5-10
     * @title 获取自动化设置
     * @desc 获取自动化设置
     * @author xiong
     * @version v1
     * @return int cron_shell - 自动化脚本
     * @return int cron_status - 自动化状态,正常返回success,不正常返回error
     * @return int cron_due_suspend_swhitch - 产品到期暂停开关 1开启，0关闭
     * @return int cron_due_suspend_day - 产品到期暂停X天后暂停
     * @return int cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关 1开启，0关闭
     * @return int cron_due_terminate_swhitch - 产品到期删除开关 1开启，0关闭
     * @return int cron_due_terminate_day - 产品到期X天后删除
     * @return int cron_due_renewal_first_swhitch - 续费第一次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_first_day - 续费X天后到期第一次提醒
     * @return int cron_due_renewal_second_swhitch - 续费第二次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_second_day - 续费X天后到期第二次提醒
     * @return int cron_overdue_first_swhitch - 产品逾期第一次提醒开关 1开启，0关闭
     * @return int cron_overdue_first_day - 产品逾期X天后第一次提醒
     * @return int cron_overdue_second_swhitch - 产品逾期第二次提醒开关 1开启，0关闭
     * @return int cron_overdue_second_day - 产品逾期X天后第二次提醒
     * @return int cron_overdue_third_swhitch - 产品逾期第三次提醒开关 1开启，0关闭
     * @return int cron_overdue_third_day - 产品逾期X天后第三次提醒
     * @return int cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭
     * @return int cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @return int cron_order_overdue_day - 订单未付款X天后通知 required
     * @return int cron_task_shell - 任务队列命令 required
     * @return int cron_task_status - 任务队列最新状态 required
     * @return int cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @return int cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @return int cron_day_start_time - 定时任务开始时间 required
     * @return int cron_system_log_delete_swhitch - 系统日志自动删除开关 1开启，0关闭 required
     * @return int cron_system_log_delete_day - 系统日志创建X天后自动删除 required
     * @return int cron_sms_log_delete_swhitch - 短信日志自动删除开关 1开启，0关闭 required
     * @return int cron_sms_log_delete_day - 短信日志创建X天后自动删除 required
     * @return int cron_email_log_delete_swhitch - 邮件日志自动删除开关 1开启，0关闭 required
     * @return int cron_email_log_delete_day - 邮件日志创建X天后自动删除 required
     * @return string cron_on_demand_cron_status - 按需出账状态,正常返回success,不正常返回error
     * @return string cron_on_demand_cron_shell - 按需出账任务命令
     */
    public function cronList()
    {
        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['cron'])){
                $data[$v['setting']] = (int)$v['value'];
            }
        }
        $config = $this->whereIn('setting', ['cron_lock_last_time','task_time','on_demand_cron_end_time',
            'notice_task_time'])
            ->select()
            ->toArray();
        $configMap = array_column($config, 'value', 'setting');
        //最后执行时间判断
        if((time() - $configMap['cron_lock_last_time'] > 10*60)){
            $data['cron_status'] = 'error';
        }else{
            $data['cron_status'] = 'success';
        }
        $data['cron_shell'] = 'php '. root_path() .'cron/cron.php';

        // 任务队列命令及状态
        $task_time = $configMap['task_time'];
        // 改为10分钟变更为异常
        if(($task_time!=='' && (time()-$task_time)>=10*60) || $task_time==='0'){
            $data['cron_task_status'] = 'error';
        }else{
            $data['cron_task_status'] = 'success';
        }

        $data['cron_task_shell'] = 'php '. root_path() .'cron/task.php';

        // 按需出账任务队列命令及状态
        $onDemandCronEndTime = $configMap['on_demand_cron_end_time'];
        // 开始时间
        if(time() - $onDemandCronEndTime >= 10*60){
            $data['cron_on_demand_cron_status'] = 'error';
        }else{
            $data['cron_on_demand_cron_status'] = 'success';
        }
        $data['cron_on_demand_cron_shell'] = 'php '. root_path() .'cron/on_demand_cron.php';

        // wyh 20251124新增：独立通知任务队列命令及状态
        $data['notice_independent_task_enabled'] = (int)configuration('notice_independent_task_enabled');

        $noticeTaskTime = $configMap['notice_task_time'];

        // 状态判断（10分钟无更新则异常）
        if (($noticeTaskTime !== '' && (time() - $noticeTaskTime) >= 10*60)
            || $noticeTaskTime === '0') {
            $data['cron_task_notice_status'] = 'error';
        } else {
            $data['cron_task_notice_status'] = 'success';
        }

        // 命令行
        $data['cron_task_notice_shell'] = 'php '. root_path() .'cron/task_notice.php';

        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 保存自动化设置
     * @desc 保存自动化设置
     * @author xiong
     * @version v1
     * @param int param.cron_due_suspend_swhitch - 产品到期暂停开关1开启，0关闭 required
     * @param int param.cron_due_suspend_day - 产品到期暂停X天后暂停 required
     * @param int param.cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关1开启，0关闭 required
     * @param int param.cron_due_terminate_swhitch - 产品到期删除开关1开启，0关闭 required
     * @param int param.cron_due_terminate_day - 产品到期X天后删除 required
     * @param int param.cron_due_renewal_first_swhitch - 续费第一次提醒开关1开启，0关闭 required
     * @param int param.cron_due_renewal_first_day - 续费X天后到期第一次提醒 required
     * @param int param.cron_due_renewal_second_swhitch - 续费第二次提醒开关1开启，0关闭 required
     * @param int param.cron_due_renewal_second_day - 续费X天后到期第二次提醒 required
     * @param int param.cron_overdue_first_swhitch - 产品逾期第一次提醒开关1开启，0关闭 required
     * @param int param.cron_overdue_first_day - 产品逾期X天后第一次提醒 required
     * @param int param.cron_overdue_second_swhitch - 产品逾期第二次提醒开关1开启，0关闭 required
     * @param int param.cron_overdue_second_day - 产品逾期X天后第二次提醒 required
     * @param int param.cron_overdue_third_swhitch - 产品逾期第三次提醒开关1开启，0关闭 required
     * @param int param.cron_overdue_third_day - 产品逾期X天后第三次提醒 required
     * @param int param.cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭 required
     * @param int param.cron_ticket_close_day - 已回复状态的工单超过x小时后关闭 required
     * @param int param.cron_aff_swhitch - 推介月报开关 1开启，0关闭 required
     * @param int param.cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @param int param.cron_order_overdue_day - 订单未付款X天后通知 required
     * @param int param.cron_day_start_time - 定时任务开始时间 required
     * @param int param.cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @param int param.cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @param int param.param.cron_system_log_delete_swhitch - 系统日志自动删除开关 1开启，0关闭 required
     * @param int param.cron_system_log_delete_day - 系统日志创建X天后自动删除 required
     * @param int param.cron_sms_log_delete_swhitch - 短信日志自动删除开关 1开启，0关闭 required
     * @param int param.cron_sms_log_delete_day - 短信日志创建X天后自动删除 required
     * @param int param.cron_email_log_delete_swhitch - 邮件日志自动删除开关 1开启，0关闭 required
     * @param int param.cron_email_log_delete_day - 邮件日志创建X天后自动删除 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function cronUpdate($param)
    {
        $day=[
            'cron_due_suspend_day',
            'cron_due_terminate_day',
            'cron_due_renewal_first_day',
            'cron_due_renewal_second_day',
            'cron_overdue_first_day',
            'cron_overdue_second_day',
            'cron_overdue_third_day',
            'cron_ticket_close_day',
            'cron_order_overdue_day',
            'cron_day_start_time',
            'cron_order_unpaid_delete_day',
            'cron_system_log_delete_day',
            'cron_sms_log_delete_day',
            'cron_email_log_delete_day',
        ];
        foreach($day as $v){
            if(!isset($param[$v]) || empty($param[$v])){
                $param[$v]=0;
            }
        }

        //暂停和删除
        if($param['cron_due_suspend_day']>$param['cron_due_terminate_day'] && $param['cron_due_suspend_swhitch']==1 && $param['cron_due_terminate_swhitch']==1){
            return ['status' => 400, 'msg' => lang('configuration_cron_suspend_day_less_terminate_day')];//产品到期暂停天数应小于产品到期删除天数
        }
        //续费提醒
        if($param['cron_due_renewal_first_day']<$param['cron_due_renewal_second_day'] && $param['cron_due_renewal_first_swhitch']==1 && $param['cron_due_renewal_second_swhitch']==1){
            return ['status' => 400, 'msg' => lang('configuration_cron_renewal_first_day_less_renewal_second_day')];//第一次续费提醒天数应大于第二次续费提醒天数
        }
        //逾期天数
        $overdueday = [];
        if($param['cron_overdue_first_swhitch']==1){
            $overdueday[count($overdueday)] = $param['cron_overdue_first_day'];
        }
        if($param['cron_overdue_second_swhitch']==1){
            $overdueday[count($overdueday)] = $param['cron_overdue_second_day'];
        }
        if($param['cron_overdue_third_swhitch']==1){
            $overdueday[count($overdueday)] = $param['cron_overdue_third_day'];
        }
        if($param['cron_due_terminate_swhitch']==1){
            $overdueday[count($overdueday)] = $param['cron_due_terminate_day'];
        }
        $overdueday_sort = $overdueday;
        sort($overdueday_sort);
        $overdueday_array_diff=array_diff_assoc($overdueday,$overdueday_sort);
        if(!empty($overdueday_array_diff)){
            return ['status' => 400, 'msg' => lang('configuration_cron_overdue_day_less_terminate_day')];//第一次逾期提醒天数应小于第二次逾期提醒天数小于第三次逾期提醒天数小于产品到期删除天数
        }
        # 日志
        $description = [];
        $systemList = $this->cronList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            if ($k=='notice_independent_task_enabled'){
                $description[] = lang('admin_old_to_new',['{field}'=>'"'.lang('configuration_notice_independent_task_enabled').'"', '{old}'=>'"'.lang('configuration_log_notice_independent_task_enabled_'.$systemList[$k]).'"', '{new}'=>'"'.lang('configuration_log_notice_independent_task_enabled_'.$v).'"']);
            }else{
                $lang = '"'.lang("configuration_log_".str_replace('day','swhitch',$k)).'"';
                $unit = '';
                if($k=='cron_ticket_close_day'){
                    $unit = lang("configuration_log_cron_due_hour");
                }else{
                    $unit = lang("configuration_log_cron_due_day");
                }

                if(strpos($k,'swhitch')>0){
                    $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                    $lang_new = lang("configuration_log_switch_{$v}");
                }else{
                    $lang_old = $systemList[$k].$unit;
                    $lang_new = $v.$unit;
                }
                $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
            }
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {

            foreach($this->config['cron'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_cron', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }
    /**
     * 时间 2022-5-10
     * @title 默认发送设置
     * @desc 默认发送设置
     * @author xiong
     * @version v1
     * @return  string send_sms - 默认短信发送国内接口
     * @return  string send_sms_global - 默认短信发送国际接口
     * @return  string send_email - 默认邮件信发送接口
     */
    public function sendList()
    {
        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['send'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 默认发送设置
     * @desc 默认发送设置
     * @author xiong
     * @version v1
     * @param  string send_sms - 默认短信发送国内接口
     * @param  string send_sms_global - 默认短信发送国际接口
     * @param  string send_email - 默认邮件信发送接口
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function sendUpdate($param)
    {

        $this->startTrans();
        try {
            foreach($this->config['send'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-08-12
     * @title 获取主题设置
     * @desc 获取主题设置
     * @author theworld
     * @version v1
     * @return string admin_theme - 后台主题
     * @return string clientarea_theme - 会员中心主题
     * @return int web_switch - 官网开关0关闭1开启
     * @return string web_theme - 官网主题
     * @return array admin_theme_list - 后台主题列表
     * @return string first_navigation - 一级导航名称
     * @return string second_navigation - 二级导航名称
     * @return string admin_theme_list[].name - 名称
     * @return string admin_theme_list[].img - 图片
     * @return array clientarea_theme_list - 会员中心主题列表
     * @return string clientarea_theme_list[].name - 名称
     * @return string clientarea_theme_list[].img - 图片
     * @return array web_theme_list - 官网主题列表
     * @return string web_theme_list[].name - 名称
     * @return string web_theme_list[].img - 图片
     * @return string cart_theme_list[].name - 名称
     * @return string cart_theme_list[].img - 图片
     * @return string home_theme - 首页PC主题
     * @return string home_theme_mobile - 首页手机主题
     * @return array home_theme_list - 首页PC主题列表
     * @return string home_theme_list[].name - 名称
     * @return string home_theme_list[].img - 图片
     * @return array home_theme_mobile_list - 首页手机主题列表
     * @return string home_theme_mobile_list[].name - 名称
     * @return string home_theme_mobile_list[].img - 图片
     * @return array home_host_theme - 会员中心产品详情PC主题,键值对,键是模块标识,值是主题
     * @return array home_host_theme_mobile - 会员中心产品详情手机主题,键值对,键是模块标识,值是主题
     * @return array module_list - 模块列表
     * @return string module_list[].name - 模块标识
     * @return string module_list[].display_name - 模块名称
     * @return string module_list[].theme_list[].name - PC主题名称
     * @return string module_list[].theme_list[].img - 图片
     * @return string module_list[].theme_mobile_list[].name - 手机主题名称
     * @return string module_list[].theme_mobile_list[].img - 图片
     */
    public function themeList()
    {
        $configuration = $this->index();
        $data = [
            'admin_theme' => '',
            'clientarea_theme' => '',
            'web_theme' => '',
            'clientarea_theme_mobile' => '',
            'cart_theme' => '',
            'cart_theme_mobile' => '',
            'first_navigation' => '',
            'second_navigation' => '',
            'admin_theme_list' => [],
            'clientarea_theme_list' => [],
            'clientarea_theme_mobile_list' => [],
            'web_theme_list' => [],
            'cart_theme_list' => [],
            'cart_theme_mobile_list' => [],
            'cart_instruction' => 0, 
            'cart_instruction_content' => '',
            'cart_change_product' => 0,
            'home_theme' => 'default',
            'home_theme_mobile' => '',
        ];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['theme'])){
                if(in_array($v['setting'], ['cart_instruction','cart_change_product'])){
                    $data[$v['setting']] = (int)$v['value'];
                }else if(in_array($v['setting'], ['home_host_theme','home_host_theme_mobile'])){
                    $data[$v['setting']] = json_decode($v['value'], true);
                }else{
                    $data[$v['setting']] = (string)$v['value'];
                }
            }
        }
        $domain = request()->domain();
        $adminThemeList = get_files(IDCSMART_ROOT . 'public/'. DIR_ADMIN .'/template');
        foreach ($adminThemeList as $key => $value) {
            $data['admin_theme_list'][] = ['name' => $value, 'img' => $domain . '/'. DIR_ADMIN .'/template/'.$value.'/theme.jpg'];
        }
        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/clientarea/template/pc');
        foreach ($clientareaThemeList as $key => $value) {
            $data['clientarea_theme_list'][] = ['name' => $value, 'img' => $domain . '/clientarea/template/pc/'.$value.'/theme.jpg'];
        }
        $clientareaThemeMobileList = get_files(IDCSMART_ROOT . 'public/clientarea/template/mobile');
        foreach ($clientareaThemeMobileList as $key => $value) {
            $data['clientarea_theme_mobile_list'][] = ['name' => $value, 'img' => $domain . '/clientarea/template/mobile/'.$value.'/theme.jpg'];
        }
        $webThemeList = get_files(IDCSMART_ROOT . 'public/web');
        $TemplateLogic = new TemplateLogic();

        foreach ($webThemeList as $key => $value) {
            $result = $TemplateLogic->templateTabList(['theme' => $value]);
            $url = $result['data']['list'][0]['url'] ?? 'template_nav.htm';

            $data['web_theme_list'][] = ['name' => $value, 'img' => $domain . '/web/'.$value.'/theme.jpg', 'url' => $url];
        }
        // 购物车PC主题
        $cartThemeList = get_files(IDCSMART_ROOT . 'public/cart/template/pc');
        foreach ($cartThemeList as $key => $value) {
            $data['cart_theme_list'][] = ['name' => $value, 'img' => $domain . '/cart/template/pc/'.$value.'/theme.jpg'];
        }
        // 购物车Mobile主题
        $cartThemeMobileList = get_files(IDCSMART_ROOT . 'public/cart/template/mobile');
        foreach ($cartThemeMobileList as $key => $value) {
            $data['cart_theme_mobile_list'][] = ['name' => $value, 'img' => $domain . '/cart/template/mobile/'.$value.'/theme.jpg'];
        }
        // 首页PC主题
        $homeThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        foreach ($homeThemeList as $key => $value) {
            $data['home_theme_list'][] = ['name' => $value, 'img' => $domain . '/home/template/pc/'.$value.'/theme.jpg'];
        }
        // 首页Mobile主题
        $homeThemeMobileList = get_files(IDCSMART_ROOT . 'public/home/template/mobile');
        foreach ($homeThemeMobileList as $key => $value) {
            $data['home_theme_mobile_list'][] = ['name' => $value, 'img' => $domain . '/home/template/mobile/'.$value.'/theme.jpg'];
        }
        $data['home_host_theme'] = $data['home_host_theme'] ?? [];
        $data['home_host_theme_mobile'] = $data['home_host_theme_mobile'] ?? [];
        // 产品详情主题
        $ModuleLogic = new ModuleLogic();
        $moduleList = $ModuleLogic->getModuleList();
        $haveMf401 = in_array('mf401', $clientareaThemeList);
        $haveMfm201 = in_array('mfm201', $clientareaThemeMobileList);
        foreach($moduleList as $k=>$v){
            // PC主题
            $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/pc');
            $moduleList[$k]['theme_list'] = [];
            foreach($moduleThemeList as $value){
                if(!$haveMf401 && $value == 'mf401'){
                    continue;
                }
                $moduleList[$k]['theme_list'][] = ['name' => $value, 'img' => $domain . '/plugins/server/'.$v['name'].'/template/clientarea/pc/'.$value.'/theme.jpg'];
            }
            if(!empty($moduleList[$k]['theme_list'])){
                if(!isset($data['home_host_theme'][ $v['name'] ])){
                    $data['home_host_theme'][ $v['name'] ] = 'default';
                }
            }else{
                $data['home_host_theme'][ $v['name'] ] = '';
            }
            // Mobile主题
            $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/mobile');
            $moduleList[$k]['theme_mobile_list'] = [];
            foreach($moduleThemeList as $value){
                if(!$haveMfm201 && $value == 'mfm201'){
                    continue;
                }
                $moduleList[$k]['theme_mobile_list'][] = ['name' => $value, 'img' => $domain . '/plugins/server/'.$v['name'].'/template/clientarea/mobile/'.$value.'/theme.jpg'];
            }
            if(!empty($moduleList[$k]['theme_mobile_list'])){
                if(!isset($data['home_host_theme_mobile'][ $v['name'] ])){
                    $data['home_host_theme_mobile'][ $v['name'] ] = '';
                }
            }else{
                $data['home_host_theme_mobile'][ $v['name'] ] = '';
            }
        }
        $data['module_list'] = $moduleList;
        return $data;
    }

    /**
     * 时间 2022-08-12
     * @title 保存主题设置
     * @desc 保存主题设置
     * @author theworld
     * @version v1
     * @param string param.admin_theme - 后台主题 required
     * @param string param.clientarea_theme - 会员中心主题 required
     * @param string param.cart_theme - 购物车主题 required
     * @param int param.web_switch - 官网开关0关闭1开启 required
     * @param string param.web_theme - 官网主题 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function themeUpdate($param)
    {
        $adminThemeList = get_files(IDCSMART_ROOT . 'public/'.DIR_ADMIN.'/template');
        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/clientarea/template/pc');
        $clientareaThemeMobileList = get_files(IDCSMART_ROOT . 'public/clientarea/template/mobile');
        $webThemeList = get_files(IDCSMART_ROOT . 'public/web');
        $cartThemeList = get_files(IDCSMART_ROOT . 'public/cart/template/pc');
        $cartThemeMobileList = get_files(IDCSMART_ROOT . 'public/cart/template/mobile');
        $homeThemeList = get_files(IDCSMART_ROOT . 'public/home/template/pc');
        $homeThemeMobileList = get_files(IDCSMART_ROOT . 'public/home/template/mobile');

        if(!in_array($param['admin_theme'], $adminThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_admin_theme_cannot_error')];
        }
        if(!in_array($param['clientarea_theme'], $clientareaThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_clientarea_theme_cannot_error')];
        }
        if(!in_array($param['web_theme'], $webThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_web_theme_cannot_error')];
        }
        if(!empty($param['clientarea_theme_mobile_switch']) && !empty($clientareaThemeMobileList) && !in_array($param['clientarea_theme_mobile'], $clientareaThemeMobileList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_clientarea_theme_mobile_cannot_error')];
        }
        if(!in_array($param['cart_theme'], $cartThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_cart_theme_cannot_error')];
        }
        if(!empty($param['clientarea_theme_mobile_switch']) && !empty($cartThemeMobileList) && !in_array($param['cart_theme_mobile'], $cartThemeMobileList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_cart_theme_cannot_error')];
        }
        if(!in_array($param['home_theme'], $homeThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_home_theme_error')];
        }
        if(!empty($param['clientarea_theme_mobile_switch']) && !empty($homeThemeMobileList) && !in_array($param['home_theme_mobile'], $homeThemeMobileList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_home_theme_error')];
        }
        // 产品详情主题
        $param['home_host_theme'] = $param['home_host_theme'] ?? [];
        $param['home_host_theme_mobile'] = $param['home_host_theme_mobile'] ?? [];
        $ModuleLogic = new ModuleLogic();
        $moduleList = $ModuleLogic->getModuleList();
        foreach($moduleList as $k=>$v){
            // PC主题
            $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/pc');
            if(empty($moduleThemeList)){
                $param['home_host_theme'][ $v['name'] ] = '';
            }else{
                if(empty($param['home_host_theme'][ $v['name'] ]) || !in_array($param['home_host_theme'][ $v['name'] ], $moduleThemeList)){
                    return ['status'=>400, 'msg'=>lang('configuration_theme_home_host_theme_error', ['{module}'=>$v['display_name']]) ];
                }
            }
            // Mobile主题
            $moduleThemeList = get_files(IDCSMART_ROOT . 'public/plugins/server/'.$v['name'].'/template/clientarea/mobile');
            if(empty($moduleThemeList) || empty($param['home_host_theme_mobile'][ $v['name'] ]) ){
                $param['home_host_theme_mobile'][ $v['name'] ] = '';
            }else{
                if(!in_array($param['home_host_theme_mobile'][ $v['name'] ], $moduleThemeList)){
                    return ['status'=>400, 'msg'=>lang('configuration_theme_home_host_theme_mobile_error', ['{module}'=>$v['display_name']]) ];
                }
            }
        }
        $param['home_host_theme'] = json_encode($param['home_host_theme']);
        $param['home_host_theme_mobile'] = json_encode($param['home_host_theme_mobile']);

        $this->startTrans();
        try {
            foreach($this->config['theme'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-09-23
     * @title 获取实名设置
     * @desc 获取实名设置
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_approval - 是否人工复审:1开启默认，0关
     * @return int certification_notice - 审批通过后,是否通知客户:1通知默认,0否
     * @return int certification_update_client_name - 是否自动更新姓名:1是,0否默认
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_update_client_phone - 手机一致性:1是,0否默认
     * @return int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认
     */
    public function certificationList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['certification'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2022-08-12
     * @title 保存实名设置
     * @desc 保存实名设置
     * @author theworld
     * @version v1
     * @param int certification_open - 实名认证是否开启:1开启默认,0关 required
     * @param int certification_approval - 是否人工复审:1开启默认，0关 required
     * @param int certification_notice - 审批通过后,是否通知客户:1通知默认,0否 required
     * @param int certification_update_client_name - 是否自动更新姓名:1是,0否默认 required
     * @param int certification_upload - 是否需要上传证件照:1是,0否默认 required
     * @param int certification_update_client_phone - 手机一致性:1是,0否默认 required
     * @param int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认 required
     */
    public function certificationUpdate($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['certification'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-02-28
     * @title 获取信息配置
     * @desc 获取信息配置
     * @author theworld
     * @version v1
     * @return string enterprise_name - 企业名称
     * @return string enterprise_telephone - 企业电话
     * @return string enterprise_mailbox - 企业邮箱
     * @return string enterprise_qrcode - 企业二维码
     * @return string online_customer_service_link - 在线客服链接
     * @return string icp_info - ICP信息
     * @return string icp_info_link - ICP信息信息链接
     * @return string public_security_network_preparation - 公安网备
     * @return string public_security_network_preparation_link - 公安网备链接
     * @return string telecom_appreciation - 电信增值
     * @return string copyright_info - 版权信息
     * @return string official_website_logo - 官网LOGO
     * @return string cloud_product_link - 云产品跳转链接
     * @return string dcim_product_link - DCIM产品跳转链接
     */
    public function infoList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['info'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2023-02-28
     * @title 保存信息配置
     * @desc 保存信息配置
     * @author theworld
     * @version v1
     * @param string enterprise_name - 企业名称 required
     * @param string enterprise_telephone - 企业电话 required
     * @param string enterprise_mailbox - 企业邮箱 required
     * @param string enterprise_qrcode - 企业二维码 required
     * @param string online_customer_service_link - 在线客服链接 required
     * @param string icp_info - ICP信息 required
     * @param string icp_info_link - ICP信息信息链接 required
     * @param string public_security_network_preparation - 公安网备 required
     * @param string public_security_network_preparation_link - 公安网备链接 required
     * @param string telecom_appreciation - 电信增值 required
     * @param string copyright_info - 版权信息 required
     * @param string official_website_logo - 官网LOGO required
     * @param string cloud_product_link - 云产品跳转链接 required
     * @param string dcim_product_link - DCIM产品跳转链接 required
     */
    public function infoUpdate($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['info'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-09-07
     * @title debug页面
     * @desc debug页面
     * @author wyh
     * @version v1
     * @return string debug_model - 1开启debug模式
     * @return string debug_model_auth - debug模式授权码
     * @return string debug_model_expire_time - 到期时间
     */
    public function debugInfo()
    {
//        $configuration = $this->index();
        $configuration = $this->select()->toArray();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['debug'])){
                $data[$v['setting']] = (string)($v['value']??"");
            }
        }
        $data['debug_model'] = intval(cache('debug_model'));
        return $data;
    }

    /**
     * 时间 2023-09-07
     * @title 保存debug页面
     * @desc 保存debug页面
     * @author wyh
     * @version v1
     * @param string debug_model - 1开启debug模式 required
     */
    public function debug($param)
    {
        if ($param['debug_model']==1){
            $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAnDPK9GhJh/beaBTstVoL0j1C2KbC2Nr2J9eVeFPqlYZKfsrEbdezbpztqzCjXQWVBfFbQmp6sCeuL1GWGFC3qTKOYxKAwWPgtBtPNEQIw7Ym9KX5suS3SYxi04bVhsof8fHaR4pSl88cG6Q7+FaJqLibqwIpmwAx3ZKrThUVqmNwKkHLC4W6mkQo6wE7u4Laiyd+LJxthW0BItKXw6G7Ns39gAYulBE0Nz1SGA+VvutzZzzwz2aE6YMjpFX2cP+qGC56HPs0e38v1eV5oE6R/U7Kif7KPKlWePmuS8lW8EelV1OwfsTwFc+EM9OEtORNlDKmdctns9/IcxdajjKmHwIDAQABAoIBAHvXtHnClUnvOLZcoK/IDMdLOsx6qtE0CSXdjuwv3DVgm3+bU9GiyuhQEz8++Mavvk9P5ILr2QoA6+EoVlBA7tx+8NUrvlmVznn9jPZrWmeQ66HcVfS30XnGjDQZGwIbDujMT7uYt5MU6bwgoktqkQnsE7+pn0L9DIwX1Sm7HcpQf23HaCFb3+ok+FrrDQUgzMDqMYUIQWrfmXo1+FKu1LPGF85QsxIwNxtedlUHlAHFfuF/Zq9dZVF38FTtRU7Z8rX7ewpdx9kMfAKWu0fMdKDXgWixIHGmq4KV3ZpCN9DYQ2Ft7/0RenIGgIuf2WUsNFV+EyrTC7qaqcFr6F4a5sECgYEAyMABYW7ii5ouu3njvRW9OLefabbpytuy99LIoebPyjjUgYzeDrQ8HiL1ZhdtMYhtvy7crhW871tOgl0aVSxK420U2WTToGIO78+twVBhhD3yzlMhBVbPy6I0N1v+BZ71sH1e72PfmAFbLb/HtGbWAE1+Jd4TVIQDd6yD6DfmFCMCgYEAxzElJdB83bJznew7DmSXJxRp6l5Q5N1a8jTiflpwjNNum5QLx0FePmwmGHIAglvPQBHCAj+dGyNnlaqSBDgOwK15Un3G7BRLDpAoCxc/pUWWEl1SoPonH/qXvgpmcdHkKkAS3D9ExR+u2zE8YzgS/BzLjoqGGpvJX/hAE0IkV9UCgYAWp7SALmdaodfMSIEvAZkNIYvX/lB8GDcmSJ9jxgyFIcy5ohAdULHIJOHU16f3AxJ/lOZKryFXUdKWW7NxEUKST+keb4aCfw54edN+EXgv2F3icvczBw0EShXieXs9XycS99MS6Q5+tQh5LT94WHKmLhiiZWGBFDTf+JQaTNSmSQKBgHpcBBfAhJOjBUajUHu86uUEszNXEJYmK7HRLrizUaQQVUeYn8ucqgnqYVRu40UwpJUU03qSHS4Ih572ko+o59cQORClVsa6iIi/oPl/JIefwVoynYlpYRNR2ljRBrEwX9pcVbmZ2+LDXaQkEJZaYb8g6SH8kfhSbldXpfSukqipAoGAGYEQFcaZ+wEhIsFUBsgHSiVHKD904HIZHoAJn1HBF2UtOH2j/znhjnYY3Xh9yBJ1uoht7u7VHQPDsTys9/IJF2lUjUCqt2PsJDpEBtbyKd8+tU1mvZ9eEOxiL5Ihzy2DhiUW1YZT8PzCkUCZT5Lyo3dLeoR3CK896Fsk3Bi+VKQ=
-----END RSA PRIVATE KEY-----';

            $domain = request()->domain();

            $url = $domain . '/' . DIR_ADMIN;

            $password = rand_str(32);

            cache('debug_model_password',$password,24*3600);

            $debug_msg = ['url'=>$url, 'username'=>'debuguser', 'password'=>$password, 'service_due_time'=>configuration('idcsmart_service_due_time')];

            $debug_msg['node'] = [
                'name' => configuration('enterprise_name')?:"",
                'ip' => '',
                'type' => '',
                'port' => '',
                'ssh_pass' => ''
            ];

            $debug_html = zjmf_private_encrypt(json_encode($debug_msg), $private_key);

            cache('debug_model',intval($param['debug_model']),24*3600);

            $this->startTrans();
            try {
                foreach($this->config['debug'] as $v){
                    if ($v=='debug_model_auth'){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$debug_html,
                        ];
                    }elseif ($v=="debug_model_expire_time"){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>time()+24*3600,
                        ];
                    }else{
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$param[$v],
                        ];
                    }
                }
                $this->saveAll($list);

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('update_fail')];
            }
            return ['status' => 200, 'msg' => lang('update_success')];
        }else{
            cache('debug_model_password',null);
            $this->startTrans();
            try {
                foreach($this->config['debug'] as $v){
                    if ($v=='debug_model_auth'){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>"",
                        ];
                    }elseif ($v=="debug_model_expire_time"){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>time(),
                        ];
                    }else{
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$param[$v],
                        ];
                    }
                }
                $this->saveAll($list);

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('update_fail')];
            }

            cache('debug_model',intval($param['debug_model']),24*3600);
            // 清除配置缓存
            idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
            return ['status' => 200, 'msg' => lang('update_success')];
        }
    }

    /**
     * 时间 2024-01-26
     * @title 对象存储页面
     * @desc 对象存储页面
     * @author wyh
     * @version v1
     * @return string oss_method - 对象存储方式，默认本地存储：LocalOss
     * @return string oss_sms_plugin - 短信接口
     * @return string oss_sms_plugin_template - 短信模板
     * @return array oss_sms_plugin_admin - 短信通知人员
     * @return string oss_mail_plugin - 邮件接口
     * @return string oss_mail_plugin_template - 邮件模板
     * @return array oss_mail_plugin_admin - 邮件通知人员
     */
    public function getOssConfig()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['oss'])){
                if ($v['setting']=='oss_sms_plugin_admin' || $v['setting']=="oss_mail_plugin_admin"){
                    if (!empty($v['value'])){
                        $data[$v['setting']] = explode(",",$v['value']);
                    }else{
                        $data[$v['setting']] = [];
                    }
                }else{
                    $data[$v['setting']] = (string)($v['value']??"");
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2024-01-26
     * @title 保存对象存储页面
     * @desc 保存对象存储页面
     * @author wyh
     * @version v1
     * @param string oss_method - 对象存储方式，默认本地存储：LocalOss
     * @param string oss_sms_plugin - 短信接口
     * @param string oss_sms_plugin_template - 短信模板
     * @param array oss_sms_plugin_admin - 短信通知人员
     * @param string oss_mail_plugin - 邮件接口
     * @param string oss_mail_plugin_template - 邮件模板
     * @param array oss_mail_plugin_admin - 邮件通知人员
     * @param string password - 当修改本地存储时，需要传此字段
     */
    public function ossConfig($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['oss'] as $v){
                if ($v=='oss_sms_plugin_admin' || $v=='oss_mail_plugin_admin'){
                    $param[$v] = implode(',',$param[$v]);
                }
                // 切换存储方式需要验证
                if ($v=='oss_method' && !empty($param[$v]) && $param[$v]!=configuration("oss_method")){
                    $AdminModel = new AdminModel();
                    $admin = $AdminModel->find(get_admin_id());
                    if (empty($admin)){
                        throw new \Exception(lang("admin_is_not_exist"));
                    }
                    if (!isset($param['password']) || !idcsmart_password_compare($param['password'],$admin['password'])){
                        throw new \Exception(lang("admin_name_or_password_error"));
                    }
                    // 切换成功，更改缓存
                    \cache('oss_method',$param[$v]);
                }

                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                    'description'=>'',
                    'create_time'=>time(),
                    'update_time'=>0
                ];
            }
            $this->saveAll($list);

            // 保存到发送设置中
            $PluginModel = new PluginModel();
            $smsPlugin = $PluginModel->find($param['oss_sms_plugin']);
            $mailPlugin = $PluginModel->find($param['oss_mail_plugin']);
            $NoticeSettingModel = new NoticeSettingModel();
            $NoticeSettingModel->update([
                'sms_global_name' => "",
                'sms_global_template' => 0,
                'sms_name' => $smsPlugin['name']??"",
                'sms_template' => $param['oss_sms_plugin_template']??0,
                'sms_enable' => 1,
                'email_name' => $mailPlugin['name']??"",
                'email_template' => $param['oss_mail_plugin_template']??0,
                'email_enable' => 1,
            ],['name'=>'oss_exception_notice']);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail') . ":" . $e->getMessage()];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-03-18
     * @title 获取订单回收站设置
     * @desc  获取订单回收站设置
     * @author hh
     * @version v1
     * @return  string order_recycle_bin - 订单回收站(0=关闭,1=开启)
     * @return  string order_recycle_bin_save_days - 保留天数(0=永不删除)
     */
    public function getOrderRecycleBinConfig()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['order_recycle_bin'])){
                $data[$v['setting']] = (string)($v['value'] ?? "");
            }
        }
        return $data;
    }

    /**
     * 时间 2024-03-18
     * @title 修改订单回收站设置
     * @desc  修改订单回收站设置
     * @author hh
     * @version v1
     * @param   int param.order_recycle_bin - 订单回收站(0=关闭,1=开启) require
     * @param   int param.order_recycle_bin_save_days - 保留天数(0=永不删除)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function orderRecycleBinConfigUpdate($param)
    {
        $old = $this->getOrderRecycleBinConfig();
        $description = [];

        $status = [
            lang('configuration_log_switch_0'),
            lang('configuration_log_switch_1'),
        ];

        $this->startTrans();
        try {
            foreach($this->config['order_recycle_bin'] as $v){
                if(isset($param[$v]) && is_numeric($param[$v])){
                    $list[] = [
                        'setting'   => $v,
                        'value'     => $param[$v],
                    ];
                    if($old[$v] != $param[$v]){
                        if($v == 'order_recycle_bin'){
                            $description[] = lang('order_recycle_bin') . $status[ $param[$v] ];
                        }else if($v == 'order_recycle_bin_save_days'){
                            $description[] = lang('log_admin_update_description', [
                                '{field}' => lang('order_recycle_bin_save_days'),
                                '{old}'   => $old[$v],
                                '{new}'   => $param[$v],
                            ]);
                        }
                    }
                }
            }
            $this->saveAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        if(!empty($description)){
            $description = lang('configuration_log_order_recycle_bin_update_success', [
                '{detail}' => implode(',', $description),
            ]);
            active_log($description);
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 获取网站参数配置
     * @desc 获取网站参数配置
     * @author theworld
     * @version v1
     * @return string enterprise_name - 企业名称
     * @return string enterprise_telephone - 企业电话
     * @return string enterprise_mailbox - 企业邮箱
     * @return string enterprise_qrcode - 企业二维码
     * @return string online_customer_service_link - 在线客服链接
     * @return string icp_info - ICP信息
     * @return string icp_info_link - ICP信息信息链接
     * @return string public_security_network_preparation - 公安网备
     * @return string public_security_network_preparation_link - 公安网备链接
     * @return string telecom_appreciation - 电信增值
     * @return string copyright_info - 版权信息
     * @return string official_website_logo - 官网LOGO
     * @return string www_url - 官网地址
     */
    public function webList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['web'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2024-04-02
     * @title 保存网站参数配置
     * @desc 保存网站参数配置
     * @author theworld
     * @version v1
     * @param string enterprise_name - 企业名称 required
     * @param string enterprise_telephone - 企业电话 required
     * @param string enterprise_mailbox - 企业邮箱 required
     * @param string enterprise_qrcode - 企业二维码 required
     * @param string online_customer_service_link - 在线客服链接 required
     * @param string icp_info - ICP信息 required
     * @param string icp_info_link - ICP信息链接 required
     * @param string public_security_network_preparation - 公安网备 required
     * @param string public_security_network_preparation_link - 公安网备链接 required
     * @param string telecom_appreciation - 电信增值 required
     * @param string copyright_info - 版权信息 required
     * @param string official_website_logo - 官网LOGO required
     */
    public function webUpdate($param)
    {
        $this->startTrans();
        try {
            $configuration = $this->webList();

            foreach($this->config['web'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'enterprise_name' => lang('configuration_enterprise_name'),
                'enterprise_telephone' => lang('configuration_enterprise_telephone'),
                'enterprise_mailbox' => lang('configuration_enterprise_mailbox'),
                'enterprise_qrcode' => lang('configuration_enterprise_qrcode'),
                'online_customer_service_link' => lang('configuration_online_customer_service_link'),
                'icp_info' => lang('configuration_icp_info'),
                'icp_info_link' => lang('configuration_icp_info_link'),
                'public_security_network_preparation' => lang('configuration_public_security_network_preparation'),
                'public_security_network_preparation_link' => lang('configuration_public_security_network_preparation_link'),
                'telecom_appreciation' => lang('configuration_telecom_appreciation'),
                'copyright_info' => lang('configuration_copyright_info'),
                'official_website_logo' => lang('configuration_official_website_logo'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 获取云服务器配置
     * @desc 获取云服务器配置
     * @author theworld
     * @version v1
     * @return int cloud_server_more_offers - 更多优惠0关闭1开启
     */
    public function cloudServerList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['cloud_server'])){
                $data[$v['setting']] = (int)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2024-04-02
     * @title 保存云服务器配置
     * @desc 保存云服务器配置
     * @author theworld
     * @version v1
     * @param int cloud_server_more_offers - 更多优惠0关闭1开启 required
     */
    public function cloudServerUpdate($param)
    {
        $this->startTrans();
        try {
            $configuration = $this->cloudServerList();

            foreach($this->config['cloud_server'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'cloud_server_more_offers' => lang('configuration_cloud_server_more_offers'),
            ];

            $param['cloud_server_more_offers'] = lang('switch_'.$param['cloud_server_more_offers']);
            $configuration['cloud_server_more_offers'] = lang('switch_'.$configuration['cloud_server_more_offers']);

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 获取物理服务器配置
     * @desc 获取物理服务器配置
     * @author theworld
     * @version v1
     * @return int physical_server_more_offers - 更多优惠0关闭1开启
     */
    public function physicalServerList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['physical_server'])){
                $data[$v['setting']] = (int)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2024-04-02
     * @title 保存物理服务器配置
     * @desc 保存物理服务器配置
     * @author theworld
     * @version v1
     * @param int physical_server_more_offers - 更多优惠0关闭1开启 required
     */
    public function physicalServerUpdate($param)
    {
        $this->startTrans();
        try {
            $configuration = $this->physicalServerList();

            foreach($this->config['physical_server'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'physical_server_more_offers' => lang('configuration_physical_server_more_offers'),
            ];

            $param['physical_server_more_offers'] = lang('switch_'.$param['physical_server_more_offers']);
            $configuration['physical_server_more_offers'] = lang('switch_'.$configuration['physical_server_more_offers']);

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 获取ICP配置
     * @desc 获取ICP配置
     * @author theworld
     * @version v1
     * @return int icp_product_id - 购买/咨询商品ID
     */
    public function icpList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['icp'])){
                $data[$v['setting']] = (int)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2024-04-02
     * @title 保存ICP配置
     * @desc 保存ICP配置
     * @author theworld
     * @version v1
     * @param int icp_product_id - 购买/咨询商品ID required
     */
    public function icpUpdate($param)
    {
        $ProductModel = new ProductModel();
        $relProduct = $ProductModel->find($param['icp_product_id']);
        if(empty($relProduct)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $this->startTrans();
        try {
            $configuration = $this->icpList();

            foreach($this->config['icp'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'icp_product_id' => lang('configuration_icp_product_id'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-10-22
     * @title 获取商品全局设置
     * @desc 获取商品全局设置
     * @author theworld
     * @version v1
     * @return int self_defined_field_apply_range - 自定义字段应用范围(0=无1=商品分组新增商品)
     * @return int custom_host_name_apply_range - 自定义标识应用范围(0=无1=商品分组新增商品)
     * @return int product_duration_group_presets_open - 是否开启商品周期分组预设
     * @return int product_duration_group_presets_apply_range - 商品周期分组预设应用范围(0全局默认，1接口新增商品)
     * @return int product_duration_group_presets_default_id - 商品周期分组预设全局默认分组ID
     * @return int product_new_host_renew_with_ratio_open - 新产品续费按周期比例折算(0关闭，1开启)
     * @return int product_new_host_renew_with_ratio_apply_range - 新产品续费按周期比例折算范围(2商品分组下新产品，1接口下新产品，0全部新产品)
     * @return array product_new_host_renew_with_ratio_apply_range_2 - 二级分组id，逗号分隔
     * @return array product_new_host_renew_with_ratio_apply_range_1 - 接口id，逗号分隔
     * @return int product_global_renew_rule - 商品到期日计算规则(0实际到期日，1产品到期日)
     * @return int product_global_show_base_info - 基础信息展示(0关闭，1开启)
     * @return int product_renew_with_new_open - 商品续费时重新计算续费金额(0关闭，1开启)
     * @return array product_renew_with_new_product_ids - 所选商品ID
     * @return int product_overdue_not_delete_open - 商品到期后不自动删除(0关闭，1开启)
     * @return array product_overdue_not_delete_product_ids - 到期后不自动删除的商品ID
     * @return int host_sync_due_time_open - 产品到期时间与上游一致(0关闭，1开启)
     * @return int host_sync_due_time_apply_range - 产品到期时间与上游一致应用范围(0全部上游商品，1自定义上游商品)
     * @return array host_sync_due_time_product_ids - 产品到期时间与上游一致的商品ID
     * @return int auto_renew_in_advance - 自动续费提前开关(0=关闭1=开启)
     * @return int auto_renew_in_advance_num - 自动续费提前时间数
     * @return string auto_renew_in_advance_unit - 自动续费提前时间单位(minute=分钟,hour=小时,day=天)
     */
    public function productGlobalSetting()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['product'])){
                if (in_array($v['setting'], ['product_new_host_renew_with_ratio_apply_range_2', 'product_new_host_renew_with_ratio_apply_range_1','product_renew_with_new_product_ids','product_overdue_not_delete_product_ids','host_sync_due_time_product_ids'])){
                    $data[$v['setting']] = !empty($v['value'])?array_filter(explode(',', $v['value'])):[];
                }else if(in_array($v['setting'], ['auto_renew_in_advance_unit'])){
                    $data[$v['setting']] = $v['value'];
                }else{
                    $data[$v['setting']] = (int)$v['value'];
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2024-10-22
     * @title 保存商品全局设置
     * @desc 保存商品全局设置
     * @param int self_defined_field_apply_range - 自定义字段应用范围(0=无1=商品分组新增商品) required
     * @param int custom_host_name_apply_range - 自定义标识应用范围(0=无1=商品分组新增商品) required
     * @param int product_duration_group_presets_open - 是否开启商品周期分组预设 required
     * @param int product_duration_group_presets_apply_range - 商品周期分组预设应用范围(0全局默认，1接口新增商品) required
     * @param int product_duration_group_presets_default_id - 商品周期分组预设全局默认分组ID required
     * @param int product_new_host_renew_with_ratio_open - 新产品续费按周期比例折算(0关闭，1开启)
     * @param int product_new_host_renew_with_ratio_apply_range - 新产品续费按周期比例折算范围(2商品分组下新产品，1接口下新产品，0全部新产品)
     * @param array product_new_host_renew_with_ratio_apply_range_2 - 二级分组id分组
     * @param array product_new_host_renew_with_ratio_apply_range_1 - 接口id分组
     * @param int product_global_renew_rule - 商品到期日计算规则(0实际到期日，1产品到期日)
     * @param int product_global_show_base_info - 基础信息展示(0关闭，1开启)
     * @param int product_renew_with_new_open - 商品续费时重新计算续费金额(0关闭，1开启)
     * @param array product_renew_with_new_product_ids - 所选商品ID
     * @param int product_overdue_not_delete_open - 商品到期后不自动删除(0关闭，1开启)
     * @param array product_overdue_not_delete_product_ids - 到期后不自动删除的商品ID
     * @param int host_sync_due_time_open - 产品到期时间与上游一致(0关闭，1开启)
     * @param int host_sync_due_time_apply_range - 产品到期时间与上游一致应用范围(0全部上游商品，1自定义上游商品)
     * @param array host_sync_due_time_product_ids - 产品到期时间与上游一致的商品ID
     * @param int auto_renew_in_advance - 自动续费提前开关(0=关闭1=开启)
     * @param int auto_renew_in_advance_num - 自动续费提前时间数
     * @param string auto_renew_in_advance_unit - 自动续费提前时间单位(minute=分钟,hour=小时,day=天)
     * @author theworld
     * @version v1
     */
    public function productGlobalSettingUpdate($param)
    {
        $this->startTrans();
        try {
            $configuration = $this->productGlobalSetting();

            // 更改所有商品数据
            $ProductModel = new ProductModel();
            $ProductModel->globalSetting($param);

            foreach($this->config['product'] as $v){
                if (in_array($v,['product_new_host_renew_with_ratio_apply_range_1', 'product_new_host_renew_with_ratio_apply_range_2','product_renew_with_new_product_ids','product_overdue_not_delete_product_ids','host_sync_due_time_product_ids'])){
                    $param[$v] = implode(',', $param[$v]);
                }
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'self_defined_field_apply_range' => lang('configuration_self_defined_field_apply_range'),
                'custom_host_name_apply_range' => lang('configuration_custom_host_name_apply_range'),
                'product_duration_group_presets_open' => lang('configuration_product_duration_group_presets_open'),
                'product_duration_group_presets_apply_range' => lang('configuration_product_duration_group_presets_apply_range'),
                'product_duration_group_presets_default_id' => lang('configuration_product_duration_group_presets_default_id'),
                'product_new_host_renew_with_ratio_open' => lang('configuration_product_new_host_renew_with_ratio_open'),
                'product_new_host_renew_with_ratio_apply_range' => lang('configuration_product_new_host_renew_with_ratio_apply_range'),
                'product_new_host_renew_with_ratio_apply_range_2' => lang('configuration_product_new_host_renew_with_ratio_apply_range_2'),
                'product_new_host_renew_with_ratio_apply_range_1' => lang('configuration_product_new_host_renew_with_ratio_apply_range_1'),
                'product_global_renew_rule' => lang('configuration_product_global_renew_rule'),
                'product_global_show_base_info' => lang('configuration_product_global_show_base_info'),
                'product_renew_with_new_open' => lang('configuration_product_renew_with_new_open'),
                'product_renew_with_new_product_ids' => lang('configuration_product_renew_with_new_product_ids'),
                'product_overdue_not_delete_open' => lang('configuration_product_overdue_not_delete_open'),
                'product_overdue_not_delete_product_ids' => lang('configuration_product_overdue_not_delete_product_ids'),
                'host_sync_due_time_open' => lang('configuration_host_sync_due_time_open'),
                'host_sync_due_time_apply_range' => lang('configuration_host_sync_due_time_apply_range'),
                'host_sync_due_time_product_ids' => lang('configuration_host_sync_due_time_product_ids'),
                'auto_renew_in_advance' => lang('configuration_auto_renew_in_advance'),
                'auto_renew_in_advance_num' => lang('configuration_auto_renew_in_advance_num'),
                'auto_renew_in_advance_unit' => lang('configuration_auto_renew_in_advance_unit'),
            ];

            $param['self_defined_field_apply_range'] = lang('apply_range_'.$param['self_defined_field_apply_range']);
            $configuration['self_defined_field_apply_range'] = lang('apply_range_'.$configuration['self_defined_field_apply_range']);
            $param['custom_host_name_apply_range'] = lang('apply_range_'.$param['custom_host_name_apply_range']);
            $configuration['custom_host_name_apply_range'] = lang('apply_range_'.$configuration['custom_host_name_apply_range']);

            $param['product_duration_group_presets_open'] = lang('product_duration_group_presets_open_'.$param['product_duration_group_presets_open']);
            $configuration['product_duration_group_presets_open'] = lang('product_duration_group_presets_open_'.$configuration['product_duration_group_presets_open']);
            $param['product_duration_group_presets_apply_range'] = lang('product_duration_group_presets_apply_range_'.$param['product_duration_group_presets_apply_range']);
            $configuration['product_duration_group_presets_apply_range'] = lang('product_duration_group_presets_apply_range_'.$configuration['product_duration_group_presets_apply_range']);
            $param['product_duration_group_presets_default_id'] = lang('product_duration_group_presets_default_id_change',['{id}'=>$param['product_duration_group_presets_default_id']]);
            $configuration['product_duration_group_presets_default_id'] = lang('product_duration_group_presets_default_id_change',['{id}'=>$configuration['product_duration_group_presets_default_id']]);

            $param['product_new_host_renew_with_ratio_open'] = lang('product_new_host_renew_with_ratio_open_'.$param['product_new_host_renew_with_ratio_open']);
            $configuration['product_new_host_renew_with_ratio_open'] = lang('product_new_host_renew_with_ratio_open_'.$configuration['product_new_host_renew_with_ratio_open']);
            $param['product_new_host_renew_with_ratio_apply_range'] = lang('product_new_host_renew_with_ratio_apply_range_'.$param['product_new_host_renew_with_ratio_apply_range']);
            $configuration['product_new_host_renew_with_ratio_apply_range'] = lang('product_new_host_renew_with_ratio_apply_range_'.$configuration['product_new_host_renew_with_ratio_apply_range']);
            $param['product_new_host_renew_with_ratio_apply_range_2'] = lang('product_new_host_renew_with_ratio_apply_range_2_change',['{ids}'=>$param['product_new_host_renew_with_ratio_apply_range_2']]);
            $configuration['product_new_host_renew_with_ratio_apply_range_2'] = lang('product_new_host_renew_with_ratio_apply_range_2_change',['{ids}'=>implode(',',$configuration['product_new_host_renew_with_ratio_apply_range_2'])]);
            $param['product_new_host_renew_with_ratio_apply_range_1'] = lang('product_new_host_renew_with_ratio_apply_range_1_change',['{ids}'=>$param['product_new_host_renew_with_ratio_apply_range_1']]);
            $configuration['product_new_host_renew_with_ratio_apply_range_1'] = lang('product_new_host_renew_with_ratio_apply_range_1_change',['{ids}'=>implode(',',$configuration['product_new_host_renew_with_ratio_apply_range_1'])]);
            $param['product_global_renew_rule'] = lang('product_global_renew_rule_'.$param['product_global_renew_rule']);
            $configuration['product_global_renew_rule'] = lang('product_global_renew_rule_'.$configuration['product_global_renew_rule']);
            $param['product_global_show_base_info'] = lang('product_global_show_base_info_'.$param['product_global_show_base_info']);
            $configuration['product_global_show_base_info'] = lang('product_global_show_base_info_'.$configuration['product_global_show_base_info']);
            $param['product_renew_with_new_open'] = lang('product_renew_with_new_open_'.$param['product_renew_with_new_open']);
            $configuration['product_renew_with_new_open'] = lang('product_renew_with_new_open_'.$configuration['product_renew_with_new_open']);
            $param['product_renew_with_new_product_ids'] = lang('product_renew_with_new_product_ids_change',['{ids}'=>$param['product_renew_with_new_product_ids']]);
            $configuration['product_renew_with_new_product_ids'] = lang('product_renew_with_new_product_ids_change',['{ids}'=>implode(',',$configuration['product_renew_with_new_product_ids'])]);
            $param['product_overdue_not_delete_open'] = lang('product_overdue_not_delete_open_'.$param['product_overdue_not_delete_open']);
            $configuration['product_overdue_not_delete_open'] = lang('product_overdue_not_delete_open_'.$configuration['product_overdue_not_delete_open']);
            $param['product_overdue_not_delete_product_ids'] = lang('product_overdue_not_delete_product_ids_change',['{ids}'=>$param['product_overdue_not_delete_product_ids']]);
            $configuration['product_overdue_not_delete_product_ids'] = lang('product_overdue_not_delete_product_ids_change',['{ids}'=>implode(',',$configuration['product_overdue_not_delete_product_ids'])]);
            $param['host_sync_due_time_open'] = lang('host_sync_due_time_open_'.$param['host_sync_due_time_open']);
            $configuration['host_sync_due_time_open'] = lang('host_sync_due_time_open_'.$configuration['host_sync_due_time_open']);
            $param['host_sync_due_time_apply_range'] = lang('host_sync_due_time_apply_range_'.$param['host_sync_due_time_apply_range']);
            $configuration['host_sync_due_time_apply_range'] = lang('host_sync_due_time_apply_range_'.$configuration['host_sync_due_time_apply_range']);
            $param['host_sync_due_time_product_ids'] = lang('host_sync_due_time_product_ids_change',['{ids}'=>$param['host_sync_due_time_product_ids']]);
            $configuration['host_sync_due_time_product_ids'] = lang('host_sync_due_time_product_ids_change',['{ids}'=>implode(',',$configuration['host_sync_due_time_product_ids'])]);
            $param['auto_renew_in_advance'] = lang('auto_renew_in_advance_'.$param['auto_renew_in_advance']);
            $configuration['auto_renew_in_advance'] = lang('auto_renew_in_advance_'.$configuration['auto_renew_in_advance']);
            $param['auto_renew_in_advance_unit'] = lang('auto_renew_in_advance_unit_'.$param['auto_renew_in_advance_unit']);
            $configuration['auto_renew_in_advance_unit'] = lang('auto_renew_in_advance_unit_'.$configuration['auto_renew_in_advance_unit']);

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    public function touristVisibleProduct()
    {
        $ids = configuration('tourist_visible_product_ids');
        if (!empty($ids)){
            $ids = explode(',', $ids);
        }else{
            $ids = [];
        }
        return [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'tourist_visible_product_ids' => $ids,
            ],
        ];
    }

    public function touristVisibleProductUpdate($param)
    {
        $ids = (!empty($param['tourist_visible_product_ids']) && is_array($param['tourist_visible_product_ids']))?implode(',',$param['tourist_visible_product_ids']):'';
        updateConfiguration('tourist_visible_product_ids', $ids);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2025-01-16
     * @title 获取代理商余额预警设置
     * @desc  获取代理商余额预警设置
     * @author hh
     * @version v1
     * @return int supplier_credit_warning_notice - 余额预警(0=关闭,1=开启)
     * @return string supplier_credit_amount - 自定义余额提醒大小
     * @return int supplier_credit_push_frequency - 推送频率(1=一天一次,2=一天两次,3=一天三次)
     */
    public function supplierCreditWarning()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['credit_warning'])){
                if (in_array($v['setting'], ['supplier_credit_amount'])){
                    $data[$v['setting']] = amount_format($v['value']);
                }else{
                    $data[$v['setting']] = (int)$v['value'];
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2025-01-16
     * @title 保存代理商余额预警设置
     * @desc  保存代理商余额预警设置
     * @param int param.supplier_credit_warning_notice - 余额预警(0=关闭,1=开启)
     * @param float param.supplier_credit_amount - 自定义余额提醒大小
     * @param int param.supplier_credit_push_frequency - 推送频率(1=一天一次,2=一天两次,3=一天三次)
     * @author hh
     * @version v1
     */
    public function supplierCreditWarningUpdate($param)
    {
        $this->startTrans();
        try {
            $configuration = $this->supplierCreditWarning();

            $list = [];
            foreach($configuration as $k=>$v){
                $list[] = [
                    'setting'   => $k,
                    'value'     => $param[$k],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'supplier_credit_warning_notice' => lang('configuration_supplier_credit_warning_notice'),
                'supplier_credit_amount' => lang('configuration_supplier_credit_amount'),
                'supplier_credit_push_frequency' => lang('configuration_supplier_credit_push_frequency'),
            ];

            $param['supplier_credit_warning_notice'] = lang('configuration_supplier_credit_warning_notice_'.$param['supplier_credit_warning_notice']);
            $param['supplier_credit_push_frequency'] = lang('configuration_supplier_credit_push_frequency_'.$param['supplier_credit_push_frequency']);

            $configuration['supplier_credit_warning_notice'] = lang('configuration_supplier_credit_warning_notice_'.$configuration['supplier_credit_warning_notice']);
            $configuration['supplier_credit_push_frequency'] = lang('configuration_supplier_credit_push_frequency_'.$configuration['supplier_credit_push_frequency']);
            
            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration_supplier_credit_warning', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2025-04-01
     * @title 全局按需设置
     * @desc  全局按需设置
     * @author hh
     * @version v1
     * @return   string grace_time - 宽限期
     * @return   string grace_time_unit - 宽限期单位(hour=小时,day=天)
     * @return   string keep_time - 保留期
     * @return   string keep_time_unit - 保留期单位(hour=小时,day=天)
     */
    public function globalOnDemand(): array
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['global_on_demand'])){
                $data[$v['setting']] = $v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2025-04-01
     * @title 保存全局按需设置
     * @desc  保存全局按需设置
     * @param   array param - 参数 require
     * @param   int param.grace_time - 宽限期 require
     * @param   string param.grace_time_unit - 宽限期单位(hour=小时,day=天) require
     * @param   int param.keep_time - 保留期 require
     * @param   string param.keep_time_unit - 保留期单位(hour=小时,day=天) require
     * @author hh
     * @version v1
     */
    public function globalOnDemandUpdate(array $param): array
    {
        $this->startTrans();
        try {
            $configuration = $this->globalOnDemand();

            $list = [];
            foreach($configuration as $k=>$v){
                $list[] = [
                    'setting'   => $k,
                    'value'     => $param[$k],
                ];
            }
            $this->saveAll($list);

            $description = [];

            $desc = [
                'grace_time' => lang('field_product_on_demand_grace_time'),
                'keep_time' => lang('field_product_on_demand_keep_time'),
            ];

            $param['grace_time'] = $param['grace_time'] . lang('grace_time_unit_' . $param['grace_time_unit']);
            $param['keep_time'] = $param['keep_time'] . lang('keep_time_unit_' . $param['keep_time_unit']);

            $configuration['grace_time'] = $configuration['grace_time'] . lang('grace_time_unit_' . $configuration['grace_time_unit']);
            $configuration['keep_time'] = $configuration['keep_time'] . lang('keep_time_unit_' . $configuration['keep_time_unit']);
            
            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $configuration[$k] != $param[$k]){
                    $old = $configuration[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_configuration_global_on_demand', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2025-04-18
     * @title 获取可选登录方式列表
     * @desc  获取可选登录方式列表
     * @author hh
     * @version v1
     * @return  array
     * @return  string [].value - 登录方式标识
     * @return  string [].name - 登录方式名称
     */
    public function getFirstLoginTypeList(): array
    {
        // 追加可选登录方式
        $firstLoginTypeList = [
            [
                'value' => 'account_login',
                'name'  => lang('configuration_first_login_type_account_login'),
            ]
        ];
        $hookRes = hook('append_first_login_type');
        foreach($hookRes as $v){
            if(is_array($v) && !empty($v['value']) && !empty($v['name'])){
                $firstLoginTypeList[] = [
                    'value' => $v['value'],
                    'name'  => $v['name'],
                ];
            }
        }
        return $firstLoginTypeList;
    }

    /**
     * 时间 2025-11-20
     * @title 获取亏本交易拦截配置
     * @desc  获取亏本交易拦截配置
     * @author wyh
     * @version v1
     * @return int upstream_intercept_new_order_notify - 新购通知管理员(0=否,1=是)
     * @return int upstream_intercept_new_order_reject - 新购拒绝下单(0=否,1=是)
     * @return int upstream_intercept_renew_notify - 续费通知管理员(0=否,1=是)
     * @return int upstream_intercept_renew_reject - 续费拒绝下单(0=否,1=是)
     * @return int upstream_intercept_upgrade_notify - 升降级通知管理员(0=否,1=是)
     * @return int upstream_intercept_upgrade_reject - 升降级拒绝下单(0=否,1=是)
     */
    public function upstreamInterceptList()
    {
        $config = $this->whereIn('setting', ['upstream_intercept_new_order_notify', 'upstream_intercept_new_order_reject',
            'upstream_intercept_renew_notify', 'upstream_intercept_renew_reject', 'upstream_intercept_upgrade_notify',
            'upstream_intercept_upgrade_reject'])->select()->toArray();
        $configMap = array_column($config, 'value', 'setting');
        // 直接读取各个独立的配置项
        $data = [
            'upstream_intercept_new_order_notify' => (int)$configMap['upstream_intercept_new_order_notify'],
            'upstream_intercept_new_order_reject' => (int)$configMap['upstream_intercept_new_order_reject'],
            'upstream_intercept_renew_notify' => (int)$configMap['upstream_intercept_renew_notify'],
            'upstream_intercept_renew_reject' => (int)$configMap['upstream_intercept_renew_reject'],
            'upstream_intercept_upgrade_notify' => (int)$configMap['upstream_intercept_upgrade_notify'],
            'upstream_intercept_upgrade_reject' => (int)$configMap['upstream_intercept_upgrade_reject'],
        ];
        
        return $data;
    }

    /**
     * 时间 2025-11-20
     * @title 保存亏本交易拦截配置
     * @desc  保存亏本交易拦截配置
     * @param array param - 参数
     * @param int param.upstream_intercept_new_order_notify - 新购通知管理员(0=否,1=是)
     * @param int param.upstream_intercept_new_order_reject - 新购拒绝下单(0=否,1=是)
     * @param int param.upstream_intercept_renew_notify - 续费通知管理员(0=否,1=是)
     * @param int param.upstream_intercept_renew_reject - 续费拒绝下单(0=否,1=是)
     * @param int param.upstream_intercept_upgrade_notify - 升降级通知管理员(0=否,1=是)
     * @param int param.upstream_intercept_upgrade_reject - 升降级拒绝下单(0=否,1=是)
     * @author wyh
     * @version v1
     */
    public function upstreamInterceptUpdate($param)
    {
        $this->startTrans();
        try {
            // 获取旧配置
            $oldConfig = $this->upstreamInterceptList();
            
            // 定义配置项映射
            $configFields = [
                'upstream_intercept_new_order_notify' => '新购-通知管理员',
                'upstream_intercept_new_order_reject' => '新购-拒绝下单',
                'upstream_intercept_renew_notify' => '续费-通知管理员',
                'upstream_intercept_renew_reject' => '续费-拒绝下单',
                'upstream_intercept_upgrade_notify' => '升降级-通知管理员',
                'upstream_intercept_upgrade_reject' => '升降级-拒绝下单',
            ];
            
            $statusMap = [0 => '关闭', 1 => '开启'];
            $description = [];
            
            // 逐个更新配置项
            foreach ($configFields as $field => $fieldName) {
                if (isset($param[$field])) {
                    $newValue = (int)$param[$field];
                    
                    // 更新数据库
                    $this->where('setting', $field)->update([
                        'value' => $newValue,
                        'update_time' => time(),
                    ]);
                    
                    // 记录变更日志
                    if ($oldConfig[$field] != $newValue) {
                        $old = $statusMap[$oldConfig[$field]] ?? $oldConfig[$field];
                        $new = $statusMap[$newValue] ?? $newValue;
                        
                        $description[] = lang('log_admin_update_description', [
                            '{field}' => $fieldName,
                            '{old}' => $old,
                            '{new}' => $new,
                        ]);
                    }
                }
            }
            
            // 记录操作日志
            if (!empty($description)) {
                $logDescription = lang('log_update_configuration_upstream_intercept', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{detail}' => implode(',', $description),
                ]);
                active_log($logDescription, 'configuration', 0);
            }
            
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        
        // 清除配置缓存
        idcsmart_cache(\app\common\logic\CacheLogic::CACHE_CONFIGURATION, null);
        
        return ['status' => 200, 'msg' => lang('update_success')];
    }


}
<?php
namespace app\admin\controller;

use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;

/**
 * @title 插件管理
 * @desc 插件管理
 * @use app\admin\controller\PluginController
 */
class PluginController extends AdminBaseController
{
    /**
     * 时间 2022-5-16
     * @title 获取实名认证接口列表
     * @desc 获取实名认证接口列表
     * @url /admin/v1/plugin/certification
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:实名认证接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function certificationPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 实名认证接口安装
     * @desc 实名认证接口安装
     * @url /admin/v1/plugin/certification/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function certificationInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 实名认证接口卸载
     * @desc 实名认证接口卸载
     * @url /admin/v1/plugin/certification/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function certificationUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)实名认证接口
     * @desc 禁用(启用)实名认证接口
     * @url /admin/v1/plugin/certification/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function certificationStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个实名认证接口配置
     * @desc 获取单个实名认证接口配置
     * @url /admin/v1/plugin/certification/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:实名认证接口
     * @return int plugin.id - desc:实名认证接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function certificationSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存实名认证接口配置
     * @desc 保存实名认证接口配置
     * @url /admin/v1/plugin/certification/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function certificationSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'certification';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取验证码接口列表
     * @desc 获取验证码接口列表
     * @url /admin/v1/plugin/captcha
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:验证码接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function captchaPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 验证码接口安装
     * @desc 验证码接口安装
     * @url /admin/v1/plugin/captcha/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function captchaInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 验证码接口卸载
     * @desc 验证码接口卸载
     * @url /admin/v1/plugin/captcha/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function captchaUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)验证码接口
     * @desc 禁用(启用)验证码接口
     * @url /admin/v1/plugin/captcha/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function captchaStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个验证码接口配置
     * @desc 获取单个验证码接口配置
     * @url /admin/v1/plugin/captcha/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:验证码接口
     * @return int plugin.id - desc:验证码接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function captchaSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存验证码接口配置
     * @desc 保存验证码接口配置
     * @url /admin/v1/plugin/captcha/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function captchaSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'captcha';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取三方登录接口列表
     * @desc 获取三方登录接口列表
     * @url /admin/v1/plugin/oauth
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:三方登录接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function oauthPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 三方登录接口安装
     * @desc 三方登录接口安装
     * @url /admin/v1/plugin/oauth/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function oauthInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 三方登录接口卸载
     * @desc 三方登录接口卸载
     * @url /admin/v1/plugin/oauth/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function oauthUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)三方登录接口
     * @desc 禁用(启用)三方登录接口
     * @url /admin/v1/plugin/oauth/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function oauthStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个三方登录接口配置
     * @desc 获取单个三方登录接口配置
     * @url /admin/v1/plugin/oauth/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:三方登录接口
     * @return int plugin.id - desc:三方登录接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function oauthSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存三方登录接口配置
     * @desc 保存三方登录接口配置
     * @url /admin/v1/plugin/oauth/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function oauthSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'oauth';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取短信接口列表
     * @desc 获取短信接口列表
     * @url /admin/v1/plugin/sms
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:短信接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return int list[].sms_type - desc:短信类型 1国际 0国内
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function smsPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 短信接口安装
     * @desc 短信接口安装
     * @url /admin/v1/plugin/sms/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function smsInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 短信接口卸载
     * @desc 短信接口卸载
     * @url /admin/v1/plugin/sms/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function smsUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)短信接口
     * @desc 禁用(启用)短信接口
     * @url /admin/v1/plugin/sms/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function smsStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个短信接口配置
     * @desc 获取单个短信接口配置
     * @url /admin/v1/plugin/sms/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:短信接口
     * @return int plugin.id - desc:短信接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function smsSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存短信接口配置
     * @desc 保存短信接口配置
     * @url /admin/v1/plugin/sms/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function smsSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 拉取短信签名
     * @desc 拉取短信签名
     * @url /admin/v1/plugin/sms/:name/sign
     * @method POST
     * @author theworld
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function smsPullSign()
    {
        $param = $this->request->param();
        $param['module'] = 'sms';

        $result = (new PluginModel())->smsPullSign($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取邮件接口列表
     * @desc 获取邮件接口列表
     * @url /admin/v1/plugin/mail
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:邮件接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function mailPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 邮件接口安装
     * @desc 邮件接口安装
     * @url /admin/v1/plugin/mail/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function mailInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 邮件接口卸载
     * @desc 邮件接口卸载
     * @url /admin/v1/plugin/mail/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function mailUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)邮件接口
     * @desc 禁用(启用)邮件接口
     * @url /admin/v1/plugin/mail/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function mailStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个邮件接口配置
     * @desc 获取单个邮件接口配置
     * @url /admin/v1/plugin/mail/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:邮件接口
     * @return int plugin.id - desc:邮件接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function mailSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存邮件接口配置
     * @desc 保存邮件接口配置
     * @url /admin/v1/plugin/mail/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function mailSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'mail';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取支付接口列表
     * @desc 获取支付接口列表
     * @url /admin/v1/plugin/gateway
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:支付接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function gatewayPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 支付接口安装
     * @desc 支付接口安装
     * @url /admin/v1/plugin/gateway/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function gatewayInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 支付接口卸载
     * @desc 支付接口卸载
     * @url /admin/v1/plugin/gateway/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function gatewayUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)支付接口
     * @desc 禁用(启用)支付接口
     * @url /admin/v1/plugin/gateway/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function gatewayStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个支付接口配置
     * @desc 获取单个支付接口配置
     * @url /admin/v1/plugin/gateway/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:支付接口
     * @return int plugin.id - desc:支付接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function gatewaySetting()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存支付接口配置
     * @desc 保存支付接口配置
     * @url /admin/v1/plugin/gateway/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function gatewaySettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'gateway';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取插件列表
     * @desc 获取插件列表
     * @url /admin/v1/plugin/addon
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:插件列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return string list[].module - desc:addon插件 server模块
     * @return int count - desc:总数
     */
    public function addonPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $plugin = (new PluginModel())->pluginList($param);
        // 获取下模块数据,把模块数据放前面
        $module = (new ModuleLogic())->enableModuleList();

        $list = array_merge($module['list'], $plugin['list']);
        $count = $module['count'] + $plugin['count'];

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'list'  => $list,
                'count' => $count,
            ],
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件安装
     * @desc 插件安装
     * @url /admin/v1/plugin/addon/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function addonInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件卸载
     * @desc 插件卸载
     * @url /admin/v1/plugin/addon/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function addonUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)插件
     * @desc 禁用(启用)插件
     * @url /admin/v1/plugin/addon/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function addonStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'addon';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取对象存储接口列表
     * @desc 获取对象存储接口列表
     * @url /admin/v1/plugin/oss
     * @method GET
     * @author wyh
     * @version v1
     * @return array list - desc:对象存储接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function ossPluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 对象存储接口安装
     * @desc 对象存储接口安装
     * @url /admin/v1/plugin/oss/:name
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function ossInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 对象存储接口卸载
     * @desc 对象存储接口卸载
     * @url /admin/v1/plugin/oss/:name
     * @method DELETE
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function ossUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 禁用(启用)对象存储接口
     * @desc 禁用(启用)对象存储接口
     * @url /admin/v1/plugin/oss/:name/:status
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function ossStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 获取单个对象存储接口配置
     * @desc 获取单个对象存储接口配置
     * @url /admin/v1/plugin/oss/:name
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:对象存储接口
     * @return int plugin.id - desc:对象存储接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function ossSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 保存对象存储接口配置
     * @desc 保存对象存储接口配置
     * @url /admin/v1/plugin/oss/:name
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function ossSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'oss';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }

    /**
     * 时间 2024-05-07
     * @title 对象存储是否存有数据判断接口
     * @desc 对象存储是否存有数据判断接口
     * @url /admin/v1/plugin/oss/:name/data
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return boolean has_data - desc:是否存有数据
     */
    public function ossData()
    {
        $param = $this->request->param();

        $param['module'] = 'oss';

        $result = (new PluginModel())->ossData($param);

        return json($result);
    }

    /**
     * 时间 2024-05-07
     * @title 检测对象存储是否联通
     * @desc 检测对象存储是否联通
     * @url /admin/v1/plugin/oss/:name/link
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return boolean link - desc:是否联通
     */
    public function ossLink()
    {
        $param = $this->request->param();

        $param['module'] = 'oss';

        $result = (new PluginModel())->ossLink($param);

        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 官网主题卸载
     * @desc 官网主题卸载
     * @url /admin/v1/plugin/template/:theme
     * @method DELETE
     * @author theworld
     * @version v1
     * @param string theme - desc:主题标识 validate:required
     */
    public function templateUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'template';
        $param['name'] = parse_name($param['theme'], 1);

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2022-5-16
     * @title 插件升级
     * @desc 插件升级:module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表,template主题模板,oss对象存储
     * @url /admin/v1/plugin/:module/:name/upgrade
     * @method POST
     * @author wyh
     * @version v1
     * @param string module - desc:模块 gateway支付接口列表 addon插件列表 sms短信接口列表 mail邮件接口列表 validate:required
     * @param string name - desc:插件标识 模块不是主题时必填 validate:optional
     * @param string theme - desc:主题标识 模块是主题时必填 validate:optional
     */
    public function upgrade()
    {
        $param = $this->request->param();
        if(isset($param['theme'])){
            $param['name'] = parse_name($param['theme'], 1);
        }

        $result = (new PluginModel())->upgrade($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 插件同步
     * @desc 插件同步
     * @url /admin/v1/plugin/sync
     * @method GET
     * @author theworld
     * @version v1
     * @param string module - desc:模块 addon插件 gateway支付接口 sms短信接口 mail邮件接口 certification实名接口 server模块 oauth第三方登录 sub_server子模块 widget首页挂件 oss对象存储 validate:required
     * @return array list - desc:插件列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:名称
     * @return string list[].type - desc:应用类型 addon插件 gateway支付接口 sms短信接口 mail邮件接口 certification实名接口 server模块 oauth第三方登录 sub_server子模块 widget首页挂件 oss对象存储
     * @return string list[].version - desc:版本
     * @return string list[].uuid - desc:标识
     * @return int list[].create_time - desc:创建时间
     * @return int list[].downloaded - desc:是否已下载 0否 1是
     * @return int list[].upgrade - desc:是否可升级 0否 1是
     * @return string list[].error_msg - desc:错误信息 该信息不为空代表不可下载和升级插件
     */
    public function sync()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->sync($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 插件下载
     * @desc 插件下载
     * @url /admin/v1/plugin/:id/download
     * @method GET
     * @author theworld
     * @version v1
     * @param string id - desc:插件ID validate:required
     */
    public function download()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->download($param);

        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 带Hook插件列表
     * @desc 带Hook插件列表
     * @url /admin/v1/plugin/hook
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:插件列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].name - desc:标识
     * @return string list[].author - desc:开发者
     * @return int list[].status - desc:状态 0禁用 1正常
     */
    public function pluginHookList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginHookList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2023-06-30
     * @title 带Hook插件排序
     * @desc 带Hook插件排序
     * @url /admin/v1/plugin/hook/order
     * @method PUT
     * @author theworld
     * @version v1
     * @param array id - desc:插件ID数组 validate:required
     */
    public function pluginHookOrder()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->pluginHookOrder($param);

        return json($result);
    }

    /**
     * 时间 2024-09-20
     * @title 支付插件排序
     * @desc 支付插件排序
     * @url /admin/v1/plugin/order/gateway
     * @method PUT
     * @author wyh
     * @version v1
     * @param array id - desc:插件ID数组 validate:required
     */
    public function gatewayPluginOrder()
    {
        $param = $this->request->param();

        $result = (new PluginModel())->gatewayPluginOrder($param);

        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 获取发票接口列表
     * @desc 获取发票接口列表
     * @url /admin/v1/plugin/invoice
     * @method GET
     * @author hh
     * @version v1
     * @return array list - desc:发票接口列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].name - desc:标识
     * @return string list[].version - desc:版本
     * @return string list[].author - desc:开发者
     * @return string list[].author_url - desc:开发者链接
     * @return int list[].status - desc:状态 0禁用 1正常 3未安装
     * @return string list[].help_url - desc:申请链接
     * @return string list[].menu_id - desc:导航ID
     * @return string list[].url - desc:导航链接
     * @return int count - desc:总数
     */
    public function invoicePluginList()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new PluginModel())->pluginList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 发票接口安装
     * @desc 发票接口安装
     * @url /admin/v1/plugin/invoice/:name
     * @method POST
     * @author hh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function invoiceInstall()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = (new PluginModel())->install($param);

        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 发票接口卸载
     * @desc 发票接口卸载
     * @url /admin/v1/plugin/invoice/:name
     * @method DELETE
     * @author hh
     * @version v1
     * @param string name - desc:标识 validate:required
     */
    public function invoiceUninstall()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = (new PluginModel())->uninstall($param);

        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 禁用(启用)发票接口
     * @desc 禁用(启用)发票接口
     * @url /admin/v1/plugin/invoice/:name/:status
     * @method PUT
     * @author hh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param string status - desc:状态 1启用 0禁用 validate:required
     */
    public function invoiceStatus()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = (new PluginModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 获取单个发票接口配置
     * @desc 获取单个发票接口配置
     * @url /admin/v1/plugin/invoice/:name
     * @method GET
     * @author hh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @return object plugin - desc:发票接口
     * @return int plugin.id - desc:发票接口ID
     * @return int plugin.status - desc:状态 0禁用 1启用 3未安装
     * @return string plugin.name - desc:标识
     * @return string plugin.title - desc:名称
     * @return string plugin.url - desc:图标地址
     * @return string plugin.author - desc:作者
     * @return string plugin.author_url - desc:作者链接
     * @return string plugin.version - desc:版本
     * @return string plugin.description - desc:描述
     * @return string plugin.module - desc:所属模块
     * @return int plugin.order - desc:排序
     * @return string plugin.help_url - desc:帮助链接
     * @return int plugin.create_time - desc:创建时间
     * @return int plugin.update_time - desc:更新时间
     * @return array plugin.config - desc:配置
     * @return string plugin.config[].title - desc:配置名称
     * @return string plugin.config[].type - desc:配置类型 text文本
     * @return string plugin.config[].value - desc:默认值
     * @return string plugin.config[].tip - desc:提示
     * @return string plugin.config[].field - desc:配置字段名 保存时传的键
     */
    public function invoiceSetting()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => [
                'plugin' => (new PluginModel())->setting($param)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2025-01-26
     * @title 保存发票接口配置
     * @desc 保存发票接口配置
     * @url /admin/v1/plugin/invoice/:name
     * @method PUT
     * @author hh
     * @version v1
     * @param string name - desc:标识 validate:required
     * @param array config.field - desc:配置 field为返回的配置字段 validate:required
     */
    public function invoiceSettingPost()
    {
        $param = $this->request->param();
        $param['module'] = 'invoice';

        $result = (new PluginModel())->settingPost($param);

        return json($result);
    }
}


<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;
use app\common\logic\VerificationCodeLogic;
use app\common\model\SystemLogModel;
use think\facade\Db;
use think\facade\Cache;

/**
 * @title 后台开放类
 * @desc 后台开放类,不需要授权
 */
class PublicController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminValidate();
    }

    /**
     * 时间 2022-5-18
     * @title 登录信息
     * @desc 登录信息
     * @url /admin/v1/login
     * @method GET
     * @author wyh
     * @version v1
     * @param string name - desc:管理员用户名 validate:optional
     * @return int captcha_admin_login - desc:管理员登录图形验证码开关 1开启 0关闭
     * @return int captcha_admin_login_error - desc:客户登录失败图形验证码开关 1开启 0关闭
     * @return string website_name - desc:网站名称
     * @return string lang_admin - desc:语言
     * @return int admin_allow_remember_account - desc:后台是否允许记住账号 1开启 0关闭
     * @return int captcha_admin_login_error_3_times - desc:管理员登录失败3次开关 1是 0否
     * @return int admin_login_password_encrypt - desc:后台登录密码是否加密传输 1是 0否
     */
    public function loginInfo()
    {
        $param = $this->request->param();

        $setting = [
            'captcha_admin_login',
            'captcha_admin_login_error',
            'website_name',
            'lang_admin',
            'admin_allow_remember_account',
            'admin_second_verify',
            'admin_login_password_encrypt',
        ];
        $data = configuration($setting);

        $name = $param['name']??'';
        # 登录3次失败
        if ($name){
            $ip = get_client_ip();
            $key = "admin_password_login_times_{$name}_{$ip}";
            if (Cache::get($key)>3){
                $data = array_merge($data,['captcha_admin_login_error_3_times'=>1]);
            }else{
                $data = array_merge($data,['captcha_admin_login_error_3_times'=>0]);
            }
        }else{
            $data = array_merge($data,['captcha_admin_login_error_3_times'=>0]);
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'captcha_admin_login' => isset($data['captcha_admin_login']) ? (int)$data['captcha_admin_login'] : 0,
                'captcha_admin_login_error' => isset($data['captcha_admin_login_error']) ? (int)$data['captcha_admin_login_error'] : 0,
                'website_name' => $data['website_name'] ?? '',
                'lang_admin' => $data['lang_admin'] ?? '',
                'admin_allow_remember_account' => isset($data['admin_allow_remember_account']) ? (int)$data['admin_allow_remember_account'] : 1,
                'admin_second_verify' => isset($data['admin_second_verify']) ? (int)$data['admin_second_verify'] : 0,
                'captcha_admin_login_error_3_times' => isset($data['captcha_admin_login_error_3_times']) ? (int)$data['captcha_admin_login_error_3_times'] : 0,
                'admin_login_password_encrypt' => isset($data['admin_login_password_encrypt']) ? (int)$data['admin_login_password_encrypt'] : 0,
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-13
     * @title 后台登录
     * @desc 后台登录
     * @url /admin/v1/login
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:用户名 validate:required
     * @param string password - desc:密码 validate:required
     * @param string remember_password - desc:是否记住密码 1是 0否 validate:required
     * @param string token - desc:图形验证码唯一识别码 validate:optional
     * @param string captcha - desc:图形验证码 validate:optional
     * @param string code - desc:验证码 validate:optional
     * @return object data - desc:返回数据
     * @return string data.jwt - desc:jwt 登录后放在请求头Authorization里 格式:Bearer+空格+jwt值
     * @return int data.second_verify - desc:二次验证 0否 1是
     * @return string data.method - desc:二次验证方式 sms短信 email邮件 totp
     */
    public function login()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('login')->check($param)) {
            return json(['status' => 400, 'msg' => lang($this->validate->getError())]);
        }
        // 是否允许记住密码
        $adminAllowRememberAccount = configuration('admin_allow_remember_account') ?: 1;
        if($adminAllowRememberAccount != 1){
            $param['remember_password'] = 0;
        }

        hook_one('before_admin_login', ['name' => $param['name'] ?? '', 'password' => $param['password'] ?? '', 'remember_password' => $param['remember_password'] ?? '',
            'token' => $param['token'] ?? '', 'captcha' => $param['captcha'] ?? '', 'customfield' => $param['customfield'] ?? []]);

        $result = (new AdminModel())->login($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /admin/v1/captcha
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
                'html' => get_captcha(true)
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 发送手机验证码
     * @desc 发送手机验证码
     * @author theworld
     * @version v1
     * @url /admin/v1/phone/code
     * @method POST
     * @param string action - desc:验证动作 admin_login登录 admin_verify验证手机 admin_update修改手机 validate:required
     * @param string name - desc:管理员用户名 登录和验证手机时需要 validate:optional
     * @param int phone_code - desc:国际电话区号 修改手机时需要 validate:optional
     * @param string phone - desc:手机号 修改手机时需要 validate:optional
     */
    public function sendPhoneCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        $PublicValidate = new \app\admin\validate\PublicValidate();
        if (!$PublicValidate->scene('sened_phone_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($PublicValidate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendPhoneCode($param);

        return json($result);

    }

    /**
     * 时间 2025-04-02
     * @title 发送邮件验证码
     * @desc 发送邮件验证码
     * @author theworld
     * @version v1
     * @url /admin/v1/email/code
     * @method POST
     * @param string action - desc:验证动作 admin_login登录 admin_verify验证邮箱 admin_update修改邮箱 validate:required
     * @param string name - desc:管理员用户名 登录和验证邮箱时需要 validate:optional
     * @param string email - desc:邮箱 修改邮箱时需要 validate:optional
     */
    public function sendEmailCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        $PublicValidate = new \app\admin\validate\PublicValidate();
        if (!$PublicValidate->scene('sened_email_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($PublicValidate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendEmailCode($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 获取二次验证方式
     * @desc 获取二次验证方式
     * @author theworld
     * @version v1
     * @url /admin/v1/second/verify/method
     * @method POST
     * @param string name - desc:管理员用户名 validate:required
     * @return string method - desc:二次验证方式 sms短信 email邮件 totp
     */
    public function getSecondVerifyMethod()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('second')->check($param)) {
            return json(['status' => 400, 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->getSecondVerifyMethod($param);

        return json($result);
    }

}
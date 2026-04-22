<?php

namespace app\home\controller;

use app\admin\model\PluginModel;
use app\common\model\ClientModel;
use app\home\validate\AccountValidate;
use think\response\Json;

/**
 * @title 登录注册
 * @desc 登录注册
 * @use app\home\controller\LoginController
 */
class LoginController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AccountValidate();
    }

    /**
     * 时间 2022-05-20
     * @title 登录
     * @desc 登录
     * @author wyh
     * @version v1
     * @url /console/v1/login
     * @method POST
     * @param string type code desc:登录类型 code验证码登录 password密码登录 validate:required
     * @param string account 18423467948 desc:手机号或邮箱 validate:required
     * @param string phone_code 86 desc:国家区号 手机号登录时需要传此参数 validate:optional
     * @param string code 1234 desc:验证码 登录类型为验证码登录code时需要传此参数 validate:optional
     * @param string password 123456 desc:密码 登录类型为密码登录password时需要传此参数 validate:optional
     * @param string remember_password 1 desc:记住密码 登录类型为密码登录password时需要传此参数 1是 0否 validate:optional
     * @param string captcha 1234 desc:图形验证码 开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数 validate:optional
     * @param string token fd5adaf7267a5b2996cc113e45b38f05 desc:图形验证码唯一识别码 开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数 validate:optional
     * @param object customfield {} desc:自定义字段 格式{"field1":"test","field2":"test2"} validate:optional
     * @param string client_operate_password - desc:操作密码 兼容旧版 validate:optional
     * @param string security_verify_method - desc:安全验证方式 operate_password操作密码 email_code邮箱验证码 phone_code手机验证码 certification实名校验 validate:optional
     * @param string security_verify_value - desc:安全验证值 操作密码或验证码 validate:optional
     * @param string certify_id - desc:实名认证ID 实名校验时需要 validate:optional
     * @param string security_verify_token - desc:安全验证token 异常登录时返回的token需要传安全验证方式对应返回的security_verify_token validate:optional
     * @return string data.jwt - desc:jwt 登录后放在请求头Authorization里拼接成如下格式Bearer空格yJ0eX.test.ste
     * @return bool data.need_security_verify - desc:是否需要安全验证
     * @return int data.client_id - desc:用户ID 需要安全验证时返回用于发送验证码
     * @return array data.available_methods - desc:可用的验证方式列表
     */
    public function login()
    {
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();
        # 客户登录前钩子
        hook_one('before_client_login',['type'=>$param['type']??'','account'=>$param['account']??'','phone_code'=>$param['phone_code']??'',
            'code'=>$param['code']??'','password'=>$param['password']??'','remember_password'=>$param['remember_password']??'',
            'captcha'=>$param['captcha']??'','token'=>$param['token']??'','customfield'=>$param['customfield']??[]]);

        $result = $ClientModel->login($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 注册
     * @desc 注册
     * @author wyh
     * @version v1
     * @url /console/v1/register
     * @method POST
     * @param string type phone desc:注册类型 phone手机注册 email邮箱注册 validate:required
     * @param string account 18423467948 desc:手机号或邮箱 validate:required
     * @param string phone_code 86 desc:国家区号 注册类型为手机注册时需要传此参数 validate:optional
     * @param string username wyh desc:姓名 validate:optional
     * @param string code 1234 desc:验证码 validate:required
     * @param string password 123456 desc:密码 validate:required
     * @param string re_password 1 desc:重复密码 validate:required
     * @param object customfield {} desc:自定义字段 格式{"field1":"test","field2":"test2"} validate:optional
     * @return string data.jwt - desc:jwt 注册后放在请求头Authorization里拼接成如下格式Bearer空格yJ0eX.test.ste
     */
    public function register()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('register')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户
        $result = $ClientModel->register($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 忘记密码
     * @desc 忘记密码
     * @author wyh
     * @version v1
     * @url /console/v1/account/password_reset
     * @method POST
     * @param string type phone desc:注册类型 phone手机注册 email邮箱注册 validate:required
     * @param string account 18423467948 desc:手机号或邮箱 validate:required
     * @param string phone_code 86 desc:国家区号 注册类型为手机注册时需要传此参数 validate:optional
     * @param string code 1234 desc:验证码 validate:required
     * @param string password 123456 desc:密码 validate:required
     * @param string re_password 1 desc:重复密码 validate:required
     */
    public function passwordReset()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('password_reset')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户
        $result = $ClientModel->passwordReset($param);

        return json($result);
    }

    /**
     * 时间 2025-11-25
     * @title 创建实名认证
     * @desc 创建实名认证会话，返回二维码URL
     * @author wyh
     * @version v1
     * @url /console/v1/login/exception/certification/create
     * @method POST
     * @param string account - desc:账户 validate:required
     * @param string phone_code - desc:手机区号 validate:optional
     * @param string security_verify_token - desc:安全验证token 相应操作接口返回的token防止被刷 validate:required
     * @return string data.certify_id - desc:认证ID
     * @return string data.certify_url - desc:认证URL 用于生成二维码
     */
    public function createExceptionCertification()
    {
        $param = $this->request->param();

        if (empty($param['account'])){
            return json(['status' => 400, 'msg' => lang('param_error')]);
        }

        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name','Idcsmartali')->where('status',1)->find();
        if (empty($plugin)){
            return json(['status' => 400, 'msg' => lang('certification_security_verify_default')]);
        }

        $ClientModel = new ClientModel();
        if (strpos($param['account'],'@')>0){
            $client = $ClientModel->where('email',$param['account'])->find();
        }else{
            if (empty($param['phone_code'])){
                return json(['status' => 400, 'msg' => lang('param_error')]);
            }
            $client = $ClientModel->where('phone',$param['account'])
                ->where('phone_code',$param['phone_code'])
                ->find();
        }
        $param['client_id'] = $client['id']??0;

        if (empty($param['client_id'])) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }
        $cacheToken = idcsmart_cache('security_verify_token_certification_'.$param['client_id']);
        if (empty($param['security_verify_token']) || empty($cacheToken)){
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }
        if ($cacheToken != $param['security_verify_token']) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }

        // 检查安全校验方式是否开启实名校验
        $exceptionVerifyConfig = configuration('home_login_ip_exception_verify');
        $exceptionVerifyMethods = !empty($exceptionVerifyConfig) ? explode(',', $exceptionVerifyConfig) : [];
        if (!in_array('certification', $exceptionVerifyMethods)) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }

        // 检查用户是否已实名认证
        if (!check_certification($param['client_id'])) {
            return json(['status' => 400, 'msg' => lang('client_not_certified')]);
        }

        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $result = $SecurityVerifyLogic->createCertification($param['client_id']);

        return json($result);
    }

    /**
     * 时间 2025-11-25
     * @title 查询实名认证状态
     * @desc 轮询查询实名认证状态
     * @author wyh
     * @version v1
     * @url /console/v1/login/exception/certification/status
     * @method GET
     * @param string account - desc:账户 validate:required
     * @param string phone_code - desc:手机区号 validate:optional
     * @param string security_verify_token - desc:安全验证token 相应操作接口返回的token防止被刷 validate:required
     * @param string certify_id - desc:认证ID validate:required
     * @return int data.verify_status - desc:状态 0待验证 1已通过
     */
    public function getExceptionCertificationStatus()
    {
        $param = $this->request->param();

        if (empty($param['account'])){
            return json(['status' => 400, 'msg' => lang('param_error')]);
        }

        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name','Idcsmartali')->where('status',1)->find();
        if (empty($plugin)){
            return json(['status' => 400, 'msg' => lang('certification_security_verify_default')]);
        }

        $ClientModel = new ClientModel();
        if (strpos($param['account'],'@')>0){
            $client = $ClientModel->where('email',$param['account'])->find();
        }else{
            if (empty($param['phone_code'])){
                return json(['status' => 400, 'msg' => lang('param_error')]);
            }
            $client = $ClientModel->where('phone',$param['account'])
                ->where('phone_code',$param['phone_code'])
                ->find();
        }
        $param['client_id'] = $client['id']??0;
        if (empty($param['client_id'])) {
            return json(['status' => 400, 'msg' => lang('param_error')]);
        }

        $cacheToken = idcsmart_cache('security_verify_token_certification_'.$param['client_id']);
        if (empty($param['security_verify_token']) || empty($cacheToken)){
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }
        if ($cacheToken != $param['security_verify_token']) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }

        if (empty($param['certify_id'])) {
            return json(['status' => 400, 'msg' => lang('param_error')]);
        }

        // 检查安全校验方式是否开启实名校验
        $exceptionVerifyConfig = configuration('home_login_ip_exception_verify');
        $exceptionVerifyMethods = !empty($exceptionVerifyConfig) ? explode(',', $exceptionVerifyConfig) : [];
        if (!in_array('certification', $exceptionVerifyMethods)) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }

        // 检查用户是否已实名认证
        if (!check_certification($param['client_id'])) {
            return json(['status' => 400, 'msg' => lang('client_not_certified')]);
        }

        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $result = $SecurityVerifyLogic->verifyCertification($param['client_id'], $param['certify_id']);

        return json($result);
    }
}
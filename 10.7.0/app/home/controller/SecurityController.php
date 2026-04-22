<?php

namespace app\home\controller;

/**
 * @title 安全验证
 * @desc 安全验证相关接口
 * @use app\home\controller\SecurityController
 */
class SecurityController extends HomeBaseController
{
    /**
     * 时间 2025-11-25
     * @title 创建实名认证
     * @desc 创建实名认证会话返回二维码URL 已登录用户通用
     * @author wyh
     * @version v1
     * @url /console/v1/security/certification/create
     * @method POST
     * @return string data.certify_id - desc:认证ID
     * @return string data.certify_url - desc:认证URL 用于生成二维码
     */
    public function createCertification()
    {
        $clientId = request()->client_id;
        
        if (empty($clientId)) {
            return json(['status' => 400, 'msg' => lang('login_please')]);
        }

        // 检查安全校验方式是否开启实名校验
        $exceptionVerifyConfig = configuration('home_login_ip_exception_verify');
        $exceptionVerifyMethods = !empty($exceptionVerifyConfig) ? explode(',', $exceptionVerifyConfig) : [];
        if (!in_array('certification', $exceptionVerifyMethods)) {
            return json(['status' => 400, 'msg' => lang('fail_message')]);
        }

        // 检查用户是否已实名认证
        if (!check_certification($clientId)) {
            return json(['status' => 400, 'msg' => lang('client_not_certified')]);
        }

        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $result = $SecurityVerifyLogic->createCertification($clientId);

        return json($result);
    }

    /**
     * 时间 2025-11-25
     * @title 查询实名认证状态
     * @desc 轮询查询实名认证状态 已登录用户通用
     * @author wyh
     * @version v1
     * @url /console/v1/security/certification/status
     * @method GET
     * @param string certify_id - desc:认证ID validate:required
     * @return int data.verify_status - desc:状态 0待验证 1已通过
     */
    public function getCertificationStatus()
    {
        $param = $this->request->param();
        $clientId = request()->client_id;
        
        if (empty($clientId)) {
            return json(['status' => 400, 'msg' => lang('login_please')]);
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
        if (!check_certification($clientId)) {
            return json(['status' => 400, 'msg' => lang('client_not_certified')]);
        }

        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $result = $SecurityVerifyLogic->verifyCertification($clientId, $param['certify_id']);

        return json($result);
    }

    /**
     * 时间 2025-11-25
     * @title 获取可用安全验证方式
     * @desc 获取当前用户可用的安全验证方式列表
     * @author wyh
     * @version v1
     * @url /console/v1/security/available_methods
     * @method GET
     * @param string scene - desc:场景 change_password修改密码 reset_operate_password修改操作密码 host_transfer产品转移 validate:optional
     * @return array data.available_methods - desc:可用的验证方式列表
     * @return string data.available_methods[].value - desc:验证方式值
     * @return string data.available_methods[].label - desc:验证方式名称
     * @return string data.available_methods[].tip - desc:提示信息
     */
    public function getAvailableMethods()
    {
        $param = $this->request->param();
        $clientId = request()->client_id;
        
        if (empty($clientId)) {
            return json(['status' => 400, 'msg' => lang('login_please')]);
        }

        $scene = $param['scene'] ?? 'change_password';

        $SecurityVerifyLogic = new \app\common\logic\SecurityVerifyLogic();
        $availableMethods = $SecurityVerifyLogic->getAvailableMethods($clientId, $scene);

        return json([
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'available_methods' => $availableMethods
            ]
        ]);
    }
}

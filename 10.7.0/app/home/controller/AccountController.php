<?php
namespace app\home\controller;

use app\common\model\ClientModel;
use app\common\model\ClientCreditModel;
use app\common\model\ClientTrafficWarningModel;
use app\home\validate\AccountValidate;

/**
 * @title 账户管理
 * @desc 账户管理
 * @use app\home\controller\AccountController
 */
class AccountController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AccountValidate();
    }

    /**
     * 时间 2022-05-19
     * @title 账户详情
     * @desc 账户详情
     * @author theworld
     * @version v1
     * @url /console/v1/account
     * @method GET
     * @return object account - desc:账户信息
     * @return int account.id - desc:用户ID
     * @return string account.username - desc:姓名
     * @return string account.email - desc:邮箱
     * @return int account.phone_code - desc:国际电话区号
     * @return string account.phone - desc:手机号
     * @return string account.company - desc:公司
     * @return int account.country_id - desc:国家ID
     * @return string account.address - desc:地址
     * @return string account.language - desc:语言
     * @return string account.notes - desc:备注
     * @return string account.credit - desc:余额
     * @return bool account.set_operate_password - desc:是否设置了操作密码
     * @return object account.customfield - desc:自定义字段
     * @return string account.customfield.pending_amount - desc:待审核的提现金额
     * @return string account.currency_prefix - desc:货币符号
     * @return array account.oauth - desc:三方登录列表
     * @return string account.oauth[].name - desc:标识
     * @return string account.oauth[].title - desc:名称
     * @return string account.oauth[].url - desc:跳转链接
     * @return bool account.oauth[].link - desc:是否绑定 true是 false否
     * @return string account.oauth[].img - desc:图标地址
     * @return string account.oauth[].img_unbound - desc:未绑定图标地址
     * @return int account.notice_open - desc:是否接收短信邮件通知 1是 0否
     * @return string account.notice_method - desc:通知方式 all所有 email邮件 sms短信
     * @return float account.total_credit - desc:账户余额
     * @return int account.credit_remind - desc:余额提醒 0关闭 1开启
     * @return float account.credit_remind_amount - desc:余额提醒阈值
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        $id = get_client_id(false); // 获取用户ID
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $account = $ClientModel->indexClient($id);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 账户编辑
     * @desc 账户编辑
     * @author theworld
     * @version v1
     * @url /console/v1/account
     * @method PUT
     * @param string username - desc:姓名 validate:optional
     * @param string company - desc:公司 validate:optional
     * @param int country_id - desc:国家ID validate:optional
     * @param string address - desc:地址 validate:optional
     * @param string language - desc:语言 validate:optional
     * @param string notes - desc:备注 validate:optional
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户
        $result = $ClientModel->updateClient($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @url /console/v1/account/phone/old
     * @method PUT
     * @param string code - desc:验证码 validate:required
     */
    public function verifyOldPhone()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('verify_old_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 验证原手机
        $result = $ClientModel->verifyOldPhone($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改手机
     * @desc 修改手机,如果已经绑定了手机需要验证原手机
     * @author theworld
     * @version v1
     * @url /console/v1/account/phone
     * @method PUT
     * @param int phone_code - desc:国际电话区号 validate:required
     * @param string phone - desc:手机号 validate:required
     * @param string code - desc:验证码 validate:required
     */
    public function updatePhone()
    {
        // 接收参数
        $param = $this->request->param();
        $param['id'] = get_client_id(false); // 获取用户ID用于验证

        // 参数验证
        if (!$this->validate->scene('update_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改手机
        $result = $ClientModel->updateClientPhone($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @url /console/v1/account/email/old
     * @method PUT
     * @param string code - desc:验证码 validate:required
     */
    public function verifyOldEmail()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('verify_old_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 验证原邮箱
        $result = $ClientModel->verifyOldEmail($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改邮箱
     * @desc 修改邮箱,如果已经绑定了邮箱需要验证原邮箱
     * @author theworld
     * @version v1
     * @url /console/v1/account/email
     * @method PUT
     * @param string email - desc:邮箱 validate:required
     * @param string code - desc:验证码 validate:required
     */
    public function updateEmail()
    {
        // 接收参数
        $param = $this->request->param();
        $param['id'] = get_client_id(false); // 获取用户ID用于验证

        // 参数验证
        if (!$this->validate->scene('update_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改邮箱
        $result = $ClientModel->updateClientEmail($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改密码
     * @desc 修改密码
     * @author theworld
     * @version v1
     * @url /console/v1/account/password
     * @method PUT
     * @param string old_password - desc:旧密码 validate:required
     * @param string new_password - desc:新密码 validate:required
     * @param string repassword - desc:确认密码 validate:required
     * @param string security_verify_method - desc:安全验证方式 operate_password=操作密码 email_code=邮箱验证码 phone_code=手机验证码 certification=实名校验 validate:optional
     * @param string security_verify_value - desc:安全验证值 操作密码或验证码 validate:optional
     * @param string certify_id - desc:实名认证ID 实名校验时需要 validate:optional
     * @return bool data.need_security_verify - desc:是否需要安全验证
     * @return array data.available_methods - desc:可用的验证方式列表
     */
    public function updatePassword()
    {
        // 接收参数
        $param = $this->request->param();
        $clientId = request()->client_id;

        // 参数验证
        if (!$this->validate->scene('update_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户密码
        $result = $ClientModel->updateClientPassword($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 验证码修改密码
     * @desc 验证码修改密码
     * @author wyh
     * @version v1
     * @url /console/v1/account/password/code
     * @method PUT
     * @param string type - desc:验证类型 phone手机 email邮箱 validate:required
     * @param string code - desc:验证码 validate:required
     * @param string password - desc:密码 validate:required
     * @param string re_password - desc:重复密码 validate:required
     */
    public function codeUpdatePassword()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('code_update_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户密码
        $result = $ClientModel->codeUpdatePassword($param);

        return json($result);
    }

    /**
     * 时间 2022-5-23
     * @title 注销
     * @desc 注销
     * @url /console/v1/logout
     * @method POST
     * @author wyh
     * @version v1
     */
    public function logout()
    {
        // 接收参数
        $param = $this->request->param();

        $result = (new ClientModel())->logout($param);

        return json($result);
    }

    /**
     * 时间 2022-07-19
     * @title 余额变更记录列表
     * @desc 余额变更记录列表
     * @author theworld
     * @version v1
     * @url /console/v1/credit
     * @method GET
     * @param int start_time - desc:开始时间 时间戳s validate:optional
     * @param int end_time - desc:结束时间 时间戳s validate:optional
     * @param string type - desc:类型 Artificial人工 Recharge充值 Applied应用至订单 Refund退款 Withdraw提现 validate:optional
     * @param string keywords - desc:关键字 记录ID或备注 validate:optional
     * @param int order_id - desc:订单ID validate:optional
     * @param int page 1 desc:页数 validate:optional
     * @param int limit 10 desc:每页条数 validate:optional
     * @return array list - desc:记录列表
     * @return int list[].id - desc:记录ID
     * @return string list[].type - desc:类型
     * @return string list[].amount - desc:金额
     * @return string list[].notes - desc:备注
     * @return int list[].create_time - desc:变更时间
     * @return string list[].admin_name - desc:管理员名称
     * @return int count - desc:记录总数
     * @return string page_total_amount - desc:当前页金额总计
     * @return string total_amount - desc:金额总计
     */
    public function creditList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $ClientCreditModel = new ClientCreditModel();

        // 获取记录
        $data = $ClientCreditModel->clientCreditList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 修改操作密码
     * @desc 修改操作密码
     * @url /console/v1/account/operate_password
     * @method PUT
     * @author hh
     * @version v1
     * @param string origin_operate_password - desc:原操作密码 已有操作密码必传 validate:optional
     * @param string operate_password - desc:新操作密码 validate:required
     * @param string re_operate_password - desc:重复操作密码 validate:required
     * @param string security_verify_method - desc:安全验证方式 operate_password=操作密码 email_code=邮箱验证码 phone_code=手机验证码 certification=实名校验 validate:optional
     * @param string security_verify_value - desc:安全验证值 操作密码或验证码 validate:optional
     * @param string certify_id - desc:实名认证ID 实名校验时需要 validate:optional
     * @return bool data.need_security_verify - desc:是否需要安全验证
     * @return array data.available_methods - desc:可用的验证方式列表
     */
    public function updateOperatePassword()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('operate_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ClientModel())->updateOperatePassword($param);

        return json($result);
    }

    /**
     * 时间 2025-04-21
     * @title 用户流量预警详情
     * @desc 用户流量预警详情
     * @url /console/v1/account/traffic_warning
     * @method GET
     * @author hh
     * @version v1
     * @param string module - desc:模块标识 mf_cloud=魔方云 mf_dcim=DCIM validate:required
     * @return int warning_switch - desc:预警开关 0=关闭 1=开启
     * @return int leave_percent - desc:预警百分比 0是关闭
     */
    public function clientTrafficWarningIndex()
    {
        $param = $this->request->param();
        $param['client_id'] = get_client_id();

        $data = (new ClientTrafficWarningModel())->clientTrafficWarningIndex($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2025-04-21
     * @title 保存用户流量预警
     * @desc 保存用户流量预警
     * @url /console/v1/account/traffic_warning
     * @method PUT
     * @author hh
     * @version v1
     * @param string module - desc:模块标识 mf_cloud=魔方云 mf_dcim=DCIM validate:required
     * @param int warning_switch - desc:预警开关 0=关闭 1=开启 validate:required
     * @param int leave_percent - desc:预警百分比 5 10 15 20 关闭传0 validate:required
     */
    public function clientTrafficWarningUpdate()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update_traffic_warning')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ClientTrafficWarningModel())->clientTrafficWarningUpdate($param);

        return json($result);
    }

    /**
     * 时间 2025-04-25
     * @title 冻结记录
     * @desc 冻结记录
     * @author wyh
     * @version v1
     * @url /console/v1/account/credit/freeze
     * @method GET
     * @return array list - desc:冻结记录列表
     * @return int list[].id - desc:冻结记录ID
     * @return float list[].amount - desc:冻结金额
     * @return string list[].client_notes - desc:备注
     * @return int list[].create_time - desc:冻结时间
     * @return string list[].nickname - desc:操作人
     * @return int count - desc:记录总数
     */
    public function freezeList()
    {
        $param = $this->request->param();

        $ClientCreditModel = new ClientCreditModel();

        $ClientCreditModel->isAdmin = false;

        $result = $ClientCreditModel->freezeList($param);

        return json($result);
    }

    /**
     * 时间 2025-07-24
     * @title 余额提醒
     * @desc 余额提醒
     * @author wyh
     * @version v1
     * @url /console/v1/account/credit/remind
     * @method POST
     * @param int credit_remind - desc:余额提醒 0关闭 1开启 validate:required
     * @param float credit_remind_amount - desc:余额提醒阈值 validate:optional
     */
    public function creditRemind()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('credit_remind')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ClientCreditModel = new ClientCreditModel();

        $ClientCreditModel->isAdmin = false;

        $result = $ClientCreditModel->creditRemind($param);

        return json($result);
    }
}

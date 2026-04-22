<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;

/**
 * @title 管理员
 * @desc 管理员管理
 * @use app\admin\controller\AdminController
 */
class AdminController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminValidate();
    }

    /**
     * 时间 2022-5-10
     * @title 管理员列表
     * @desc 管理员列表
     * @url /admin/v1/admin
     * @method GET
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字 ID 名称 用户名 邮箱 validate:optional
     * @param string status - desc:状态 0禁用 1正常 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id nickname name email validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:管理员列表
     * @return int list[].id - desc:ID
     * @return string list[].nickname - desc:名称
     * @return string list[].name - desc:用户名
     * @return string list[].email - desc:邮箱
     * @return string list[].roles - desc:分组名称
     * @return int list[].status - desc:状态 0禁用 1正常
     * @return int list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return int list[].lock - desc:锁定 0否 1是
     * @return int list[].lock_time - desc:锁定到期时间
     * @return int list[].totp_bind - desc:是否绑定totp 0否 1是
     * @return int count - desc:管理员总数
     */
    public function adminList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);
        
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new AdminModel())->adminList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员
     * @desc 获取单个管理员
     * @url /admin/v1/admin/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:管理员ID validate:required
     * @return object admin - desc:管理员
     * @return int admin.id - desc:ID
     * @return string admin.nickname - desc:名称
     * @return string admin.name - desc:用户名
     * @return string admin.email - desc:邮箱
     * @return string admin.role_id - desc:分组ID
     * @return string admin.roles - desc:所属分组 逗号分隔
     * @return string admin.status - desc:状态 0禁用 1正常
     * @return int admin.phone_code - desc:国际电话区号
     * @return string admin.phone - desc:手机号
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'admin' => (new AdminModel())->indexAdmin(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员
     * @desc 添加管理员
     * @url /admin/v1/admin
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:用户名 validate:required
     * @param string password - desc:密码 validate:required
     * @param string repassword - desc:重复密码 validate:required
     * @param string email - desc:邮箱 validate:required
     * @param string nickname - desc:名称 validate:required
     * @param string role_id - desc:分组ID validate:required
     * @param int phone_code - desc:国际电话区号 validate:optional
     * @param string phone - desc:手机号 validate:optional
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->createAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员
     * @desc 修改管理员
     * @url /admin/v1/admin/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:管理员ID validate:required
     * @param string name - desc:用户名 validate:required
     * @param string password - desc:密码 validate:optional
     * @param string repassword - desc:重复密码 validate:optional
     * @param string email - desc:邮箱 validate:required
     * @param string nickname - desc:名称 validate:required
     * @param string role_id - desc:分组ID validate:required
     * @param int phone_code - desc:国际电话区号 validate:optional
     * @param string phone - desc:手机号 validate:optional
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		if(!empty($param['password']) || !empty($param['repassword'])){
			//密码验证
			if (!$this->validate->scene('update_password')->check($param)){
				return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
			}
		}
        $result = (new AdminModel())->updateAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员
     * @desc 删除管理员
     * @url /admin/v1/admin/:id
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int id - desc:管理员ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->deleteAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 管理员状态切换
     * @desc 管理员状态切换
     * @url /admin/v1/admin/:id/status
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:管理员ID validate:required
     * @param int status - desc:状态 0禁用 1启用 validate:required
     */
    public function status()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-13
     * @title 注销
     * @desc 注销
     * @url /admin/v1/logout
     * @method POST
     * @author wyh
     * @version v1
     */
    public function logout()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->logout($param);

        return json($result);
    }

    /**
     * 时间 2022-9-7
     * @title 修改管理员密码
     * @desc 修改管理员密码
     * @url /admin/v1/admin/password/update
     * @method PUT
     * @author wyh
     * @version v1
     * @param string origin_password - desc:原密码 validate:required
     * @param string password - desc:密码 validate:required
     * @param string repassword - desc:重复密码 validate:required
     */
    public function updatePassword()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->updateAdminPassword($param);

        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 获取当前管理员信息
     * @desc 获取当前管理员信息
     * @url /admin/v1/login_info
     * @method GET
     * @author hh
     * @version v1
     * @return string name - desc:用户名
     * @return string nickname - desc:姓名
     * @return bool set_operate_password - desc:是否设置了操作密码
     * @return bool totp_bind - desc:是否绑定totp 0否 1是
     * @return string email - desc:邮箱
     * @return string phone_code - desc:国际电话区号
     * @return string phone - desc:手机号
     * @return string admin_role_name - desc:管理组名称
     * @return int prohibit_admin_bind_phone - desc:禁止后台用户自助绑定手机号 1是 0否
     * @return int prohibit_admin_bind_email - desc:禁止后台用户自助绑定邮箱 1是 0否
     */
    public function currentAdmin()
    {
        $result = (new AdminModel())->currentAdmin();
        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 修改管理员操作密码
     * @desc 修改管理员操作密码
     * @url /admin/v1/admin/operate_password
     * @method PUT
     * @author hh
     * @version v1
     * @param string origin_operate_password - desc:原操作密码 已有操作密码时必传 validate:optional
     * @param string operate_password - desc:新操作密码 validate:optional
     * @param string re_operate_password - desc:重复操作密码 validate:optional
     */
    public function updateAdminOperatePassword()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('operate_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->updateAdminOperatePassword($param);

        return json($result);
    }

    /**
     * 时间 2024-05-21
     * @title 修改管理员姓名
     * @desc 修改管理员姓名
     * @url /admin/v1/admin/nickname
     * @method PUT
     * @author theworld
     * @version v1
     * @param string nickname - desc:姓名 validate:optional
     */
    public function updateAdminNickname()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('nickname')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->updateAdminNickname($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/verify_old_phone
     * @method POST
     * @param string code - desc:验证码 validate:required
     */
    public function verifyOldPhone()
    {
        $param = $this->request->param();
        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('verify_old_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->verifyOldPhone($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 修改手机
     * @desc 修改手机
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/phone
     * @method PUT
     * @param int phone_code - desc:国际电话区号 validate:required
     * @param string phone - desc:手机号 validate:required
     * @param string code - desc:验证码 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function updatePhone()
    {
        $param = $this->request->param();
        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('update_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->updatePhone($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/verify_old_email
     * @method POST
     * @param string code - desc:验证码 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function verifyOldEmail()
    {
        $param = $this->request->param();
        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('verify_old_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->verifyOldEmail($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 修改邮箱
     * @desc 修改邮箱
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/email
     * @method PUT
     * @param string email - desc:邮箱 validate:required
     * @param string code - desc:验证码 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function updateEmail()
    {
        $param = $this->request->param();
        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('update_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->updateEmail($param);

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 获取TOTP密钥
     * @desc 获取TOTP密钥
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/totp
     * @method GET
     * @return string secret - desc:TOTP密钥
     * @return string url - desc:二维码地址
     */
    public function getTotp()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->getTotp();

        return json($result);
    }

    /**
     * 时间 2025-04-02
     * @title 绑定TOTP
     * @desc 绑定TOTP
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/totp
     * @method PUT
     * @param string code - desc:验证码 validate:required
     */
    public function bindTotp()
    {
        $param = $this->request->param();

        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('bind_totp')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->bindTotp($param);

        return json($result);
    }

    /**
     * 时间 2025-04-22
     * @title 解绑TOTP
     * @desc 解绑TOTP
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/totp
     * @method DELETE
     * @param string method - desc:验证方式 totp phone email validate:required
     * @param string code - desc:验证码 validate:required
     */
    public function unbindTotp()
    {
        $param = $this->request->param();

        //参数验证
        $AdminBindValidate = new \app\admin\validate\AdminBindValidate();
        if (!$AdminBindValidate->scene('unbind_totp')->check($param)){
            return json(['status' => 400 , 'msg' => lang($AdminBindValidate->getError())]);
        }

        $result = (new AdminModel())->unbindTotp($param);

        return json($result);
    }

    /**
     * 时间 2025-04-22
     * @title 管理员解绑其他管理员TOTP
     * @desc 管理员解绑其他管理员TOTP
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/:id/totp
     * @method DELETE
     * @param int id - desc:管理员ID validate:required
     */
    public function adminUnbindTotp()
    {
        $param = $this->request->param();


        $result = (new AdminModel())->adminUnbindTotp($param['id']);

        return json($result);
    }

    /**
     * 时间 2025-04-22
     * @title 管理员解锁其他管理员
     * @desc 管理员解锁其他管理员
     * @author theworld
     * @version v1
     * @url /admin/v1/admin/:id/lock
     * @method DELETE
     * @param int id - desc:管理员ID validate:required
     */
    public function adminUnlock()
    {
        $param = $this->request->param();


        $result = (new AdminModel())->adminUnlock($param['id']);

        return json($result);
    }
}


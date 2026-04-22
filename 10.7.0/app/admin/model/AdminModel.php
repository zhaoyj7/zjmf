<?php
namespace app\admin\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UpgradeSystemLogic;

/**
 * @title 管理员模型
 * @desc 管理员模型
 * @use app\admin\model\AdminModel
 */
class AdminModel extends Model
{
    protected $name = 'admin';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'nickname'        => 'string',
        'name'            => 'string',
        'password'        => 'string',
        'email'           => 'string',
        'status'          => 'int',
        'last_login_time' => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
        'phone_code'      => 'int',
        'phone'           => 'string',
        'operate_password'=> 'string',
        'totp_secret'     => 'string',
        'totp_bind'       => 'int',
        'lock'            => 'int',
        'lock_time'       => 'int',
    ];

    /**
     * 时间 2022-5-10
     * @title 管理员列表
     * @desc 管理员列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字:ID,名称,用户名,邮箱
     * @param string status - 状态0:禁用,1:正常
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,nickname,name,email
     * @param string sort - 升/降序 asc,desc
     * @return array list - 管理员列表
     * @return int list[].id - ID
     * @return int list[].nickname - 名称
     * @return int list[].name - 用户名
     * @return int list[].email - 邮箱
     * @return int list[].roles - 分组名称
     * @return int list[].status - 状态;0:禁用,1:正常
     * @return int list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
     * @return int list[].lock - 锁定0=否1=是
     * @return int list[].lock_time - 锁定到期时间
     * @return int list[].totp_bind - 是否绑定totp(0=否1=是)
     * @return int count - 管理员总数
     */
    public function adminList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','nickname','email'])){
            $param['orderby'] = 'a.id';
        }else{
            $param['orderby'] = 'a.'.$param['orderby'];
        }

        $param['status'] = $param['status'] ?? '';

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('a.id|a.nickname|a.name|a.email', 'like', "%{$param['keywords']}%");
            }
            if(in_array($param['status'], ['0', '1'])){
                $query->where('a.status', $param['status']);
            }
        };

        $admins = $this->alias('a')
            ->field('a.id,a.nickname,a.name,a.email,a.status,a.phone_code,a.phone,group_concat(ar.name) as roles,a.lock,a.lock_time,a.totp_bind')
            ->leftjoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftjoin('admin_role ar','arl.admin_role_id=ar.id')
            ->where($where)
            ->group('a.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        $count = $this->alias('a')
            ->leftjoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftjoin('admin_role ar','arl.admin_role_id=ar.id')
            ->where($where)
            ->count();

        return ['list'=>$admins,'count'=>$count];
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员
     * @desc 获取单个管理员
     * @author wyh
     * @version v1
     * @param int id - 管理员分组ID required
     * @return int id - ID
     * @return string nickname - 名称
     * @return string name - 用户名
     * @return string email - 邮箱
     * @return string role_id - 分组ID
     * @return string roles - 所属分组,逗号分隔
     * @return string status - 状态;0:禁用;1:正常
     * @return int phone_code - 国际电话区号
     * @return string phone - 手机号
     */
    public function indexAdmin($id)
    {
        $admin = $this->alias('a')
            ->field('a.id,a.nickname,a.name,a.email,a.status,a.phone_code,a.phone,ar.id as role_id,group_concat(ar.name) as roles')
            ->leftJoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftJoin('admin_role ar','ar.id=arl.admin_role_id')
            ->where('a.id',$id)
            ->group('a.id')
            ->find($id);
        return $admin?:(object)[];
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员
     * @desc 添加管理员
     * @author wyh
     * @version v1
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.repassword 123456 重复密码 required
     * @param string param.email 123@qq.com 邮箱 required
     * @param string param.nickname 小华 名称 required
     * @param string param.role_id 1 分组ID required
     * @param int phone_code - 国际电话区号
     * @param string phone - 手机号
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createAdmin($param)
    {
        $adminRole = AdminRoleModel::find(intval($param['role_id']));
        if (empty($adminRole)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }

        $this->startTrans();
        try{
            $admin = $this->create([
                'name' => $param['name']?:'',
                'password' => idcsmart_password($param['password']),
                'email' => $param['email']?:'',
                'nickname' => $param['nickname']?:'',
                'status' => isset($param['status'])?intval($param['status']):1,
                'create_time' => time(),
                'phone_code' => $param['phone_code'] ?? 44,
                'phone' => $param['phone'] ?? '',
            ]);

            AdminRoleLinkModel::create([
                'admin_role_id' => intval($param['role_id']),
                'admin_id' => $admin->id,
            ]);

            # 记录日志
            active_log(lang('log_create_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name']]),'admin',$admin->id);
			
            system_notice([
                'name'              => 'admin_create_account',
                'email_description' => lang('superadmin_add_admin_send_mail'),
                'task_data' => [
                    'email'=>$param['email'],
					'template_param'=>[
						'admin_name'=>$param['name'],
						'admin_password'=>$param['password'],
                    ],
                ],
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        hook('after_admin_create',['id'=>(int)$admin->id, 'name'=>$param['name']??'','password'=>$param['password']??'','email'=>$param['email']??'',
            'nickname'=>$param['nickname']??'','status'=>$param['status']??'','role_id'=>$param['role_id']??'','customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('create_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员
     * @desc 修改管理员
     * @author wyh
     * @version v1
     * @param string param.id 1 管理员ID required
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.repassword 123456 重复密码 required
     * @param string param.email 123@qq.com 邮箱 required
     * @param string param.nickname 小华 名称 required
     * @param string param.role_id 1 分组ID required
     * @param int phone_code - 国际电话区号
     * @param string phone - 手机号
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAdmin($param)
    {
        $admin = $this->find(intval($param['id']));
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $adminRole = AdminRoleModel::find(intval($param['role_id']));
        if (empty($adminRole)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }
        # 修改密码 强制退出登录
        if(!empty($param['password'])){
            if (!idcsmart_password_compare($param['password'],$admin['password'])){
                Cache::set('admin_update_password_'.$param['id'],time(),3600*24*7); # 7天未操作接口,就可以不退出
            }
        }

        $oldRoleId = AdminRoleLinkModel::where('admin_id',intval($param['id']))->value('admin_role_id');
        if ($oldRoleId!=$param['role_id'] && $param['id']==1){
            return ['status'=>400,'msg'=>lang('supper_admin_cannot_update_role')];
        }

        # 日志详情
        $description = '';
        if ($admin['name'] != $param['name']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_name'),'{content}'=>$param['name']]) .',';
        }
        if(!empty($param['password'])){
            if ($admin['password'] != idcsmart_password($param['password'])){
                $description .= lang('log_change_password') .',';
            }
        }
        if ($admin['email'] != $param['email']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_email'),'{content}'=>$param['email']]) .',';
        }
        if ($admin['nickname'] != $param['nickname']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_nickname'),'{content}'=>$param['nickname']]) .',';
        }
        if ($admin['status'] != $param['status']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_status'),'{content}'=>$param['status']]) .',';
        }
        if ($oldRoleId != $param['role_id']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_role_id'),'{content}'=>$param['role_id']]);
        }
        if(isset($param['phone_code']) && $admin['phone_code'] != $param['phone_code']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('client_phone_code'),'{content}'=>$param['phone_code']]);
        }
        if(isset($param['phone']) && $admin['phone'] != $param['phone']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('client_phone'),'{content}'=>$param['phone']]);
        }

        $this->startTrans();
        try{
            $update=[
                'name' => $param['name'],
                'email' => $param['email']?:'',
                'nickname' => $param['nickname']?:'',
                'status' => isset($param['status'])?intval($param['status']):1,
                'update_time' => time(),
            ];
            if(!empty($param['password'])){
                $update['password']=idcsmart_password($param['password']);
            }
            if(is_numeric($param['phone_code'])){
                $update['phone_code'] = $param['phone_code'];
            }
            if(isset($param['phone'])){
                $update['phone'] = $param['phone'];
            }
            $this->update($update,['id'=>intval($param['id'])]);

            # 删除原关联
            AdminRoleLinkModel::where('admin_id',intval($param['id']))->delete();

            AdminRoleLinkModel::create([
                'admin_role_id' => intval($param['role_id']),
                'admin_id' => intval($param['id']),
            ]);

            # 记录日志
            active_log(lang('log_update_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name'],'{description}'=>$description]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        hook('after_admin_edit',['name'=>$param['name']??'','password'=>$param['password']??'','email'=>$param['email']??'',
            'nickname'=>$param['nickname']??'','status'=>$param['status']??'','role_id'=>$param['role_id']??'','customfield'=>$param['customfield']??[]]);


        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员
     * @desc 删除管理员
     * @author wyh
     * @version v1
     * @param int id 1 管理员ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteAdmin($param)
    {

        $id = $param['id']??0;

        # 超级管理员不可删除
        if ($id == 1){
            return ['status'=>400,'msg'=>lang('super_admin_cannot_delete')];
        }

        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $hookRes = hook('before_admin_delete',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try{
            $this->destroy($id);

            AdminRoleLinkModel::where('admin_id',$id)->delete();
            AdminWidgetModel::where('admin_id',$id)->delete();
            # 记录日志
            active_log(lang('admin_delete_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        hook('after_admin_delete',['id'=>$id]);

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-5-11
     * @title 管理员状态切换
     * @desc 管理员状态切换
     * @author wyh
     * @version v1
     * @param int param.id 1 管理员ID required
     * @param int param.status 1 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function status($param)
    {
        # 超级管理员不可操作
        if (intval($param['id']) == 1){
            return ['status'=>400,'msg'=>lang('super_admin_cannot_opreate')];
        }

        $admin = $this->find(intval($param['id']));
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $status = intval($param['status']);

        if ($admin['status'] == $status){
            return ['status'=>400,'msg'=>lang('cannot_repeat_opreate')];
        }

        try{
            $this->update([
                'status' => $status,
                'update_time' => time(),
            ],['id'=>intval($param['id'])]);
        }catch (\Exception $e){
            if ($status == 0){
                return ['status'=>400,'msg'=>lang('disable_fail')];
            }else{
                return ['status'=>400,'msg'=>lang('enable_fail')];
            }
        }

        if ($status == 0){
            # 记录日志
            active_log(lang('admin_disable_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);
            return ['status'=>200,'msg'=>lang('disable_success')];
        }else{
            # 记录日志
            active_log(lang('admin_enable_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);
            return ['status'=>200,'msg'=>lang('enable_success')];
        }

    }

    /**
     * 时间 2022-5-13
     * @title 后台登录
     * @desc 后台登录
     * @author wyh
     * @version v1
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.remember_password 1 是否记住密码(1是,0否) required
     * @param string token d7e57706218451cbb23c19cfce583fef 验证码唯一识别码(开启登录图形验证码开关时传此参数)
     * @param string captcha 12345 验证码(开启登录图形验证码开关时传此参数)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data - 返回数据
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     * @return int data.second_verify - 二次验证0否1是
     * @return string data.method - 二次验证方式sms短信email邮件totp
     */
    public function login($param)
    {
        # 检查IP白名单
        $ipWhitelist = configuration('admin_login_ip_whitelist');
        if (!empty($ipWhitelist)) {
            $currentIp = get_client_ip();
            if (!$this->isIpInWhitelist($currentIp, $ipWhitelist)) {
                active_log(lang('log_admin_login_ip_not_in_whitelist_log',['{admin}'=>$param['name']??'','{ip}'=>$currentIp]),'admin',0);
                return ['status'=>400,'msg'=>lang('log_admin_login_ip_not_in_whitelist',['{admin}'=>$param['name']??'','{ip}'=>$currentIp])];
            }
        }

        $isLogin = true;
        if(configuration('admin_second_verify')==1){
            $res = $this->getSecondVerifyMethod($param);
            if($res['status']==200 && !empty($res['data']['method'])){
                if(isset($param['code'])){
                    $isLogin = false;
                }
            }
        }

        # 登录3次失败,开启图形验证码,且2个小时内操作有效
        $ip = get_client_ip();
        $key = "admin_password_login_times_{$param['name']}_{$ip}";
        if($isLogin){
            Cache::set($key,intval(Cache::get($key))+1,3600*2);
        }

        # 图形验证码
        if (configuration('captcha_admin_login') && $isLogin){
            $captchaVerify = true;
            if(configuration('captcha_admin_login_error')==1){
                if(Cache::get($key)<=3){
                    $captchaVerify = false;
                }
            }
            if($captchaVerify) {
                if (!isset($param['captcha']) || empty($param['captcha'])) {
                    return ['status' => 400, 'msg' => lang('login_captcha'),'data'=>['captcha'=>1]];
                }
                if (!isset($param['token']) || empty($param['token'])) {
                    return ['status' => 400, 'msg' => lang('login_captcha_token'),'data'=>['captcha'=>1]];
                }
                $token = $param['token'];
                if (!check_captcha($param['captcha'], $token)) {
                    return ['status' => 400, 'msg' => lang('login_captcha_error'),'data'=>['captcha'=>1]];
                }
            }
        }

        $name = $param['name'];

        $password = $param['password'];
        
        // 判断是否需要解密密码
        $adminLoginPasswordEncrypt = configuration('admin_login_password_encrypt');
        if($adminLoginPasswordEncrypt == 1){
            try{
                $password = password_decrypt($password);
            }catch(\Exception $e){
                return ['status'=>400,'msg'=>lang('password_decrypt_error')];
            }
        }

        $rememberPassword = $param['remember_password'];

        if (strpos($name,"@")>0){
            $where['email'] = $name;
        }else{
            $where['name'] = $name;
        }

        # 调试模式
        $debug = false;
        if (intval(cache("debug_model")) && $name=='debuguser'){
            $admin = $this->where('id',1)->find();
            # 验证密码通过
            if ($password==cache("debug_model_password")){
                $debug = true;
            }
        }else{
            $admin = $this->where($where)->find();
        }


        if (empty($admin)){
            active_log(lang('log_admin_login_not_exist',['{admin}'=>$name]),'admin',0);
            return ['status'=>400,'msg'=>lang('admin_name_or_password_error')];
        }

        if ($admin['status'] == 0){
            active_log(lang('log_admin_login_disabled',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);
            return ['status'=>400,'msg'=>lang('admin_is_disabled')];
        }

        if ($admin['lock'] == 1 && !$debug){
            if($admin['lock_time']<time()){
                $this->update([
                    'lock' => 0,
                    'lock_time' => 0,
                    'update_time' => time(),
                ], ['id' => $admin['id']]);
            }else{
                active_log(lang('log_admin_login_locked',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);
                return ['status'=>400,'msg'=>lang('admin_is_locked')];
            }
        }

        $failedTimes = intval(Cache::get('admin_login_failed_'.$admin['id']));

        if (idcsmart_password_compare($password,$admin['password']) || $debug){
            if(configuration('admin_second_verify')==1 && !$debug){
                $method = $param['method'] ?? '';
                if(!in_array($method, ['sms', 'email', 'totp'])){
                    $res = $this->getSecondVerifyMethod($param);
                    if($res['status']==200 && !empty($res['data']['method'])){
                        $method = $res['data']['method'];
                    }
                }

                if(!empty($method)){
                    if(!isset($param['code'])){
                        return ['status'=>400, 'msg'=>lang('please_second_verify'), 'data' => ['second_verify' => 1, 'method' => $method]];
                    }
                    if($method=='totp'){
                        if(empty($admin['totp_bind'])){
                            return ['status'=>400,'msg'=>lang('admin_not_bind_totp'), 'data' => ['second_verify' => 1, 'method' => $method]];
                        }

                        $GoogleAuthenticator = new \app\common\lib\GoogleAuthenticator();
                        $result = $GoogleAuthenticator->verifyCode($admin['totp_secret'], strval($param['code']));

                        if(!$result){
                            $lock = $this->adminLock($admin['id'], $failedTimes);

                            if($lock){
                                return ['status'=>400,'msg'=>lang('admin_is_locked')];
                            }else{
                                return ['status'=>400, 'msg'=>lang('totp_code_error'), 'data' => ['second_verify' => 1, 'method' => $method]];
                            }
                        }
                    }else if($method=='sms'){
                        // 验证码验证
                        $code = Cache::get('verification_code_admin_login_'.$admin['phone_code'].'_'.$admin['phone']);
                        if(empty($code)){
                            return ['status' => 400, 'msg' => lang('please_get_verification_code'), 'data' => ['second_verify' => 1, 'method' => $method]];
                        }

                        if($code!=$param['code']){
                            $lock = $this->adminLock($admin['id'], $failedTimes);

                            if($lock){
                                return ['status'=>400,'msg'=>lang('admin_is_locked')];
                            }else {
                                return ['status' => 400, 'msg' => lang('verification_code_error'), 'data' => ['second_verify' => 1, 'method' => $method]];
                            }
                        }

                        Cache::delete('verification_code_admin_login_'.$admin['phone_code'].'_'.$admin['phone']); // 验证通过,删除验证码缓存
                    }else if($method=='email'){
                        // 验证码验证
                        $code = Cache::get('verification_code_admin_login_'.$admin['email']);
                        if(empty($code)){
                            return ['status' => 400, 'msg' => lang('please_get_verification_code'), 'data' => ['second_verify' => 1, 'method' => $method]];
                        }

                        if($code!=$param['code']){
                            $lock = $this->adminLock($admin['id'], $failedTimes);

                            if($lock){
                                return ['status'=>400,'msg'=>lang('admin_is_locked')];
                            }else {
                                return ['status' => 400, 'msg' => lang('verification_code_error'), 'data' => ['second_verify' => 1, 'method' => $method]];
                            }
                        }

                        Cache::delete('verification_code_admin_login_'.$admin['email']); // 验证通过,删除验证码缓存
                    }
                }
            }

            $ip = get_client_ip(0,true);

            $this->startTrans();
            try{
                $this->update([
                    'last_login_ip' => $ip,
                    'last_login_time' => time(),
                    'last_action_time' => time()
                ],$where);

                $AdminLoginModel = new AdminLoginModel();
                $AdminLoginModel->adminLogin($admin->id);

                // 获取数据库的权限
                $AuthRuleModel = new AuthRuleModel();
                $auth = $AuthRuleModel->getAdminAuthRule($admin->id);
                Cache::set('admin_auth_rule_'.$admin->id, json_encode($auth),7200);

                Cache::delete($key);
                Cache::delete('admin_login_failed_'.$admin->id);

                # 邮件提醒
                # 记录日志,赋值
                $request = request();
                $request->admin_id = $admin->id;
                $request->admin_name = $admin['name'];
                active_log(lang('log_admin_login',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);

                $this->commit();
            }catch (\Exception $e){
                $this->rollback();
                return ['status'=>400,'msg'=>lang('login_fail') . ":" .  $e->getMessage()];
            }

            # 创建jwt
            $adminInfo = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'remember_password' => $rememberPassword,
                'is_admin' => true # 后台
            ];

            if(!empty(configuration('admin_login_expire_time'))){
                $expired = configuration('admin_login_expire_time')*60;
            }else{
                if ($rememberPassword == 1){
                    $expired = 3600*24*7; # 7天退出登录
                }else{
                    $expired = 3600*24*1; # 最多1天退出
                }
            }

            $jwt = create_jwt($adminInfo,$expired,true);

            $data = [
                'jwt' => $jwt,
            ];

            cookie('admin_idcsmart_jwt', $jwt);

            deleteUnusedFile();

            hook('after_admin_login',['id'=>$admin->id,'customfield'=>$param['customfield']??[]]);

            $UpgradeSystemLogic = new UpgradeSystemLogic();
            $UpgradeSystemLogic->upgradeData();

            // 清空该管理员的操作密码缓存
            idcsmart_cache('ADMIN_OPERATE_PASSWORD_'.$admin['id'], NULL);

            return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
        }else{
            $lock = $this->adminLock($admin['id'], $failedTimes);

            active_log(lang('log_admin_login_password_error',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);

            if($lock){
                return ['status'=>400,'msg'=>lang('admin_is_locked')];
            }else{
                return ['status'=>400,'msg'=>lang('admin_name_or_password_error')];
            }

        }

    }

    public function adminLock($adminId,$failedTimes)
    {
        if(!empty(configuration('admin_password_or_verify_code_retry_times')) && $failedTimes+1>configuration('admin_password_or_verify_code_retry_times')){
            $this->update([
                'lock' => 1,
                'lock_time' => time()+configuration('admin_frozen_time')*60,
                'update_time' => time(),
            ], ['id' => $adminId]);
            Cache::delete('admin_login_failed_'.$adminId);

            return true;
        }else{
            Cache::set('admin_login_failed_'.$adminId, $failedTimes+1, 1800);
        }

        return false;
    }

    /**
     * 时间 2022-5-13
     * @title 注销
     * @desc 注销
     * @author wyh
     * @version v1
     */
    public function logout($param)
    {
        $adminId = get_admin_id();

        $admin = $this->find($adminId);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $jwt = get_header_jwt();

        Cache::set('login_token_'.$jwt,null);
        cookie('admin_idcsmart_jwt', null);

        // 清空该管理员的操作密码缓存
        idcsmart_cache('ADMIN_OPERATE_PASSWORD_'.$admin['id'], NULL);

        # 记录日志
        active_log(lang('log_admin_logout',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);

        hook('after_admin_logout',['id'=>$adminId,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('logout_success')];

    }

    /**
     * 时间 2022-9-7
     * @title 修改管理员密码
     * @desc 修改管理员密码
     * @author wyh
     * @version v1
     * @param  string param.origin_password - 原密码 required
     * @param  string param.password 123456 密码 required
     * @param  string param.repassword 123456 重复密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAdminPassword($param)
    {
        $admin = $this->find(get_admin_id());
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }
        // 原密码不对
        if (!idcsmart_password_compare($param['origin_password'],$admin['password'])){
            return ['status'=>400, 'msg'=>lang('origin_password_error')];
        }
        # 修改密码 强制退出登录
        if (!idcsmart_password_compare($param['password'],$admin['password'])){
            Cache::set('admin_update_password_'.get_admin_id(),time(),3600*24*7); # 7天未操作接口,就可以不退出
        }else{
            return ['status'=>400,'msg'=>lang('admin_password_is_same')];
        }

        # 日志详情
        $description = '';
        if ($admin['password'] != idcsmart_password($param['password'])){
            $description = lang('log_change_password');
        }

        $this->startTrans();
        try{
            $update['password']=idcsmart_password($param['password']);
            $this->update($update,['id'=>get_admin_id()]);
            # 记录日志
            active_log(lang('log_update_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>request()->admin_name,'{description}'=>$description]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>401,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-09-16
     * @title 在线管理员列表
     * @desc 在线管理员列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @return array list - 管理员列表
     * @return int list[].id - ID
     * @return int list[].nickname - 名称
     * @return int list[].name - 用户名
     * @return int list[].email - 邮箱
     * @return int list[].last_action_time - 上次操作时间
     * @return int count - 管理员总数
     */
    public function onlineAdminList($param)
    {
        # 最近一小时在线
        $where = function (Query $query) use($param) {
            $query->where('last_action_time', '>=', time() - 3600);
        };

        $time = time();
        $admins = $this->field('id,nickname,name,email,last_action_time')
            ->where($where)
            ->withAttr('last_action_time', function($value) use ($time) {
                $visitTime = $time - $value;
                if($visitTime>365*24*3600){
                    $value = lang('one_year_ago');
                }else{
                    $day = floor($visitTime/(24*3600));
                    $visitTime = $visitTime%(24*3600);
                    $hour = floor($visitTime/3600);
                    $visitTime = $visitTime%3600;
                    $minute = floor($visitTime/60);
                    $value = ($day>0 ? $day.lang('day') : '').($hour>0 ? $hour.lang('hour') : '').($minute>0 ? $minute.lang('minute') : '');
                    $value = !empty($value) ? $value.lang('ago') : $minute.lang('minute').lang('ago');
                }
                return $value;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('last_action_time', 'desc')
            ->select()
            ->toArray();

        $count = $this->field('id')
            ->where($where)
            ->count();

        return ['list'=>$admins, 'count'=>$count];
    }

    /**
     * 时间 2024-05-20
     * @title 获取当前管理员信息
     * @desc  获取当前管理员信息
     * @author hh
     * @version v1
     * @return  string name - 用户名
     * @return  string nickname - 姓名
     * @return  bool set_operate_password - 是否设置了操作密码
     * @return  bool totp_bind - 是否绑定totp(0=否1=是)
     * @return  string email - 邮箱
     * @return  string phone_code - 国际电话区号
     * @return  string phone - 手机号
     * @return  string admin_role_name - 管理组名称
     * @return  int prohibit_admin_bind_phone - 禁止后台用户自助绑定手机号:1是0否
     * @return  int prohibit_admin_bind_email - 禁止后台用户自助绑定邮箱:1是0否
     * @return  int status - 状态码,200成功,400失败
     * @return  string msg - 提示信息
     */
    public function currentAdmin()
    {
        $adminId = get_admin_id();

        $admin = $this->alias('a')
            ->field('a.name,a.nickname,a.operate_password,a.totp_bind,a.email,a.phone_code,a.phone,c.name admin_role_name')
            ->leftjoin('admin_role_link b', 'b.admin_id=a.id')
            ->leftjoin('admin_role c', 'b.admin_role_id=c.id')
            ->where('a.id', $adminId)
            ->find();
        if (empty($admin)){
            return ['status'=>401, 'msg'=>lang('admin_is_not_exist')];
        }



        $data = [
            'name'                  => $admin['name'],
            'nickname'              => $admin['nickname'],
            'set_operate_password'  => !empty($admin['operate_password']),
            'totp_bind'             => $admin['totp_bind'],
            'email'                 => $admin['email'],
            'phone_code'            => $admin['phone_code'],
            'phone'                 => $admin['phone'],
            'admin_role_name'       => $admin['admin_role_name'],
            'prohibit_admin_bind_phone' => intval(configuration('prohibit_admin_bind_phone')),
            'prohibit_admin_bind_email' => intval(configuration('prohibit_admin_bind_email')),
        ];

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2024-05-20
     * @title 修改操作密码
     * @desc  修改操作密码
     * @author hh
     * @version v1
     * @param   string param.origin_operate_password - 原操作密码 已有操作密码必传
     * @param   string param.operate_password - 新操作密码
     * @param   string param.re_operate_password - 重复操作密码
     * @return  int status - 状态码,200成功,400失败
     * @return  string msg - 提示信息
     */
    public function updateAdminOperatePassword($param)
    {
        $adminId = get_admin_id();
        $admin = $this->find($adminId);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }
        // 原密码不对
        if(!empty($admin['operate_password'])){
            if(!isset($param['origin_operate_password']) || empty($param['origin_operate_password'])){
                return ['status'=>400, 'msg'=>lang('origin_operate_password_require')];
            }
            if(idcsmart_password($param['origin_operate_password']) !== $admin['operate_password']){
                return ['status'=>400, 'msg'=>lang('origin_operate_password_error')];
            }
        }

        # 日志详情
        $description = lang('log_origin_operate_password_update_success', [
            '{admin}'   => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
        ]);

        $this->startTrans();
        try{
            $update['operate_password'] = idcsmart_password($param['operate_password']);
            $this->update($update, ['id'=>get_admin_id()]);
            # 记录日志
            active_log($description, 'admin', $admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2024-05-21
     * @title 修改管理员姓名
     * @desc  修改管理员姓名
     * @author  theworld
     * @version v1
     * @param   string param.nickname - 姓名
     * @return  int status - 状态码,200成功,400失败
     * @return  string msg - 提示信息
     */
    public function updateAdminNickname($param)
    {
        $adminId = get_admin_id();
        $admin = $this->find($adminId);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        # 日志详情
        # 日志详情
        $description = '';
        if ($admin['nickname'] != $param['nickname']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_nickname'),'{content}'=>$param['nickname']]);
        }

        $this->startTrans();
        try{
            $update['nickname'] = $param['nickname'];
            $this->update($update, ['id'=>get_admin_id()]);

            if(!empty($description)){
                # 记录日志
                active_log(lang('log_update_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name'],'{description}'=>$description]),'admin',$admin->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2025-04-02
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldPhone($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }
        if(empty($admin['phone'])){
            return ['status'=>400, 'msg'=>lang('admin_not_bind_phone')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone'].'_success', 1, 300); // 验证成功结果保存5分钟
        $ip = get_client_ip();
        Cache::delete('verification_code_time_'.$ip); // 验证通过,删除验证码缓存

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2025-04-02
     * @title 修改手机
     * @desc 修改手机
     * @author theworld
     * @version v1
     * @param int param.phone_code - 国际电话区号 required
     * @param string param.phone - 手机号 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updatePhone($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(!empty($admin['phone']) && configuration('prohibit_admin_bind_phone')==1){
            return ['status'=>400, 'msg'=>lang('cannot_update_phone')];
        }

        // 如果已有手机则需要验证原手机
        if(!empty($admin['phone'])){
            $verifyResult = Cache::get('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone'].'_success'); // 获取验证原手机结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_phone')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_admin_update_'.$param['phone_code'].'_'.$param['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_admin_update_'.$param['phone_code'].'_'.$param['phone']); // 验证通过,删除验证码缓存

        // 修改手机
        $this->startTrans();
        try {
            $this->update([
                'phone_code' => $param['phone_code'],
                'phone' => $param['phone'],
                'update_time' => time()
            ], ['id' => $id]);

            # 记录日志
            if(!empty($admin['phone'])){
                active_log(lang('admin_change_bound_mobile', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#', '{phone}'=>$param['phone'], '{old_phone}'=>$admin['phone']]), 'admin', $id);
            }else{
                active_log(lang('admin_bound_mobile', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#', '{phone}'=>$param['phone']]), 'admin', $id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2025-04-02
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldEmail($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }
        if(empty($admin['email'])){
            return ['status'=>400, 'msg'=>lang('admin_not_bind_email')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_admin_verify_'.$admin['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }

        Cache::delete('verification_code_admin_verify_'.$admin['email']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_admin_verify_'.$admin['email'].'_success', 1, 300); // 验证成功结果保存5分钟
        $ip = get_client_ip();
        Cache::delete('verification_email_code_time_'.$ip); // 验证通过,删除验证码缓存

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2025-04-02
     * @title 修改邮箱
     * @desc 修改邮箱
     * @author theworld
     * @version v1
     * @param string param.email - 邮箱 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateEmail($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(!empty($admin['email']) && configuration('prohibit_admin_bind_email')==1){
            return ['status'=>400, 'msg'=>lang('cannot_update_email')];
        }

        // 如果已有邮箱则需要验证原邮箱
        if(!empty($admin['email'])){
            $verifyResult = Cache::get('verification_code_admin_verify_'.$admin['email'].'_success'); // 获取验证原邮箱结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_email')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_admin_update_'.$param['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_admin_update_'.$param['email']); // 验证通过,删除验证码缓存

        // 修改邮箱
        $this->startTrans();
        try {
            $this->update([
                'email' => $param['email'],
                'update_time' => time()
            ], ['id' => $id]);

            # 记录日志
            if(!empty($admin['phone'])){
                active_log(lang('admin_change_bound_email', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#', '{email}'=>$param['email'], '{old_email}'=>$admin['email']]), 'admin', $id);
            }else{
                active_log(lang('admin_bound_email', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#', '{email}'=>$param['email']]), 'admin', $id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2025-04-02
     * @title 获取TOTP密钥
     * @desc 获取TOTP密钥
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.secret - TOTP密钥
     * @return string data.url - 二维码地址
     */
    public function getTotp()
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(!empty($admin['totp_bind'])){
            return ['status'=>400,'msg'=>lang('admin_already_bind_totp')];
        }

        if(!empty($admin['totp_secret'])){
            $secret = $admin['totp_secret'];
            $url =  "otpauth://totp/" . urlencode($admin['name']) . '?secret=' . $secret  . "&issuser=idcsmart";
            return ['status'=>200, 'msg'=>lang('success_message'), 'data' => ['secret' => $secret, 'url' => $url]];
        }else{
            $GoogleAuthenticator = new \app\common\lib\GoogleAuthenticator();
            $secret = $GoogleAuthenticator->createSecret();
            $this->update([
                'totp_secret' => $secret,
                'update_time' => time()
            ], ['id' => $id]);
            $url =  "otpauth://totp/" . urlencode($admin['name']) . '?secret=' . $secret  . "&issuser=idcsmartbusiness";
            return ['status'=>200, 'msg'=>lang('success_message'), 'data' => ['secret' => $secret, 'url' => $url]];
        }
    }

    /**
     * 时间 2025-04-02
     * @title 绑定TOTP
     * @desc 绑定TOTP
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function bindTotp($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(!empty($admin['totp_bind'])){
            return ['status'=>400,'msg'=>lang('admin_already_bind_totp')];
        }

        if(empty($admin['totp_secret'])){
            return ['status'=>400,'msg'=>lang('admin_without_secret')];
        }

        $GoogleAuthenticator = new \app\common\lib\GoogleAuthenticator();
        $res = $GoogleAuthenticator->verifyCode($admin['totp_secret'], strval($param['code']));

        if($res){
            $this->update([
                'totp_bind' => 1,
                'update_time' => time()
            ], ['id' => $id]);

            active_log(lang('log_admin_bound_totp', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#']), 'admin', $id);

            return ['status'=>200, 'msg'=>lang('success_message')];
        }else{
            return ['status'=>400, 'msg'=>lang('totp_code_error')];
        }
    }

    /**
     * 时间 2025-04-02
     * @title 解绑TOTP
     * @desc 解绑TOTP
     * @author theworld
     * @version v1
     * @param string param.method - 验证方式totp,phone,email required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function unbindTotp($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(empty($admin['totp_bind'])){
            return ['status'=>400,'msg'=>lang('admin_not_bind_totp')];
        }

        if(empty($admin['totp_secret'])){
            return ['status'=>400,'msg'=>lang('admin_not_bind_totp')];
        }

        if($param['method']=='totp'){
            $GoogleAuthenticator = new \app\common\lib\GoogleAuthenticator();
            $res = $GoogleAuthenticator->verifyCode($admin['totp_secret'], strval($param['code']));

            if(!$res){
                return ['status'=>400, 'msg'=>lang('totp_code_error')];
            }
        }else if($param['method']=='phone'){
            // 验证码验证
            $code = Cache::get('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone']);
            if(empty($code)){
                return ['status' => 400, 'msg' => lang('please_get_verification_code')];
            }

            if($code!=$param['code']){
                return ['status' => 400, 'msg' => lang('verification_code_error')];
            }

            Cache::delete('verification_code_admin_verify_'.$admin['phone_code'].'_'.$admin['phone']); // 验证通过,删除验证码缓存
        }else if($param['method']=='email'){
            // 验证码验证
            $code = Cache::get('verification_code_admin_verify_'.$admin['email']);
            if(empty($code)){
                return ['status' => 400, 'msg' => lang('please_get_verification_code')];
            }

            if($code!=$param['code']){
                return ['status' => 400, 'msg' => lang('verification_code_error')];
            }

            Cache::delete('verification_code_admin_verify_'.$admin['email']); // 验证通过,删除验证码缓存
        }

        $this->update([
            'totp_bind' => 0,
            'totp_secret' => '',
            'update_time' => time()
        ], ['id' => $id]);

        active_log(lang('log_admin_unbound_totp', ['{admin}'=>'admin#'.$id.'#'.request()->admin_name.'#']), 'admin', $id);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * 时间 2025-04-22
     * @title 管理员解绑其他管理员TOTP
     * @desc 管理员解绑其他管理员TOTP
     * @author theworld
     * @version v1
     * @param int id - 管理员ID required
     */
    public function adminUnbindTotp($id)
    {
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(empty($admin['totp_bind'])){
            return ['status'=>400,'msg'=>lang('admin_not_bind_totp')];
        }

        $this->startTrans();
        try {
            $this->update([
                'totp_secret' => '',
                'totp_bind' => 0,
                'update_time' => time()
            ], ['id' => $id]);

            active_log(lang('log_admin_unbound_admin_totp', ['{admin}'=>'admin#'.request()->admin_id.'#'.request()->admin_name.'#', '{name}' => $admin['name']]), 'admin', $id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2025-04-22
     * @title 管理员解锁其他管理员
     * @desc 管理员解锁其他管理员
     * @author theworld
     * @version v1
     * @param int id - 管理员ID required
     */
    public function adminUnlock($id)
    {
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'lock' => 0,
                'lock_time' => 0,
                'update_time' => time()
            ], ['id' => $id]);

            active_log(lang('log_admin_unlock_admin', ['{admin}'=>'admin#'.request()->admin_id.'#'.request()->admin_name.'#', '{name}' => $admin['name']]), 'admin', $id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2025-04-02
     * @title 验证TOTP
     * @desc 验证TOTP
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyTotp($param)
    {
        // 获取登录用户ID
        $id = get_admin_id();
        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        if(empty($admin['totp_bind'])){
            return ['status'=>400,'msg'=>lang('admin_not_bind_totp')];
        }

        if(empty($admin['totp_secret'])){
            return ['status'=>400,'msg'=>lang('admin_without_secret')];
        }

        $GoogleAuthenticator = new \app\common\lib\GoogleAuthenticator();
        $res = $GoogleAuthenticator->verifyCode($admin['totp_secret'], $param['code']);

        if($res){
            return ['status'=>200, 'msg'=>lang('success_message')];
        }else{
            return ['status'=>400, 'msg'=>lang('totp_code_error')];
        }

    }

    /**
     * 时间 2025-04-02
     * @title 获取二次验证方式
     * @desc 获取二次验证方式
     * @author theworld
     * @version v1
     * @param string param.name - 管理员用户名 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.method - 二次验证方式 sms短信email邮件totp
     */
    public function getSecondVerifyMethod($param)
    {
        $admin = $this->where('name', $param['name'])->find();
        if (empty($admin)){
            return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'']];
        }

        if(!empty(configuration('admin_second_verify_method_default'))){
            if(configuration('admin_second_verify_method_default')=='totp'){
                if($admin['totp_bind']){
                    return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'totp']];
                }
            }else if(configuration('admin_second_verify_method_default')=='sms'){
                if($admin['phone']){
                    return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'sms']];
                }
            }else if(configuration('admin_second_verify_method_default')=='email'){
                if($admin['email']){
                    return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'email']];
                }
            }
        }

        if($admin['totp_bind']){
            return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'totp']];
        }else if($admin['phone']){
            return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'sms']];
        }else if($admin['email']){
            return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'email']];
        }else{
            return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>['method'=>'']];
        }
    }

    /**
     * 检查IP是否在白名单中
     * @param string $ip 要检查的IP地址
     * @param string $whitelist IP白名单，换行分隔
     * @return bool
     */
    public function isIpInWhitelist($ip, $whitelist)
    {
        if (empty($whitelist)) {
            return true;
        }

        // 将白名单按换行符分割
        $ipList = array_filter(array_map('trim', explode("\n", $whitelist)));
        
        foreach ($ipList as $allowedIp) {
            if (empty($allowedIp)) {
                continue;
            }
            
            // 检查是否是CIDR格式
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInCidr($ip, $allowedIp)) {
                    return true;
                }
            } else {
                // 直接IP匹配
                if ($ip === $allowedIp) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * 检查IP是否在CIDR网段中
     * @param string $ip IP地址
     * @param string $cidr CIDR格式的网段
     * @return bool
     */
    private function ipInCidr($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        // IPv6支持
        if (strpos($ip, ':') !== false || strpos($subnet, ':') !== false) {
            return $this->ipv6InCidr($ip, $cidr);
        }
        
        // IPv4处理
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || 
            !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        
        $mask = (int)$mask;
        if ($mask < 0 || $mask > 32) {
            return false;
        }
        
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
    }

    /**
     * 检查IPv6是否在CIDR网段中
     * @param string $ip IPv6地址
     * @param string $cidr IPv6 CIDR格式的网段
     * @return bool
     */
    private function ipv6InCidr($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || 
            !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }
        
        $mask = (int)$mask;
        if ($mask < 0 || $mask > 128) {
            return false;
        }
        
        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);
        
        if ($ipBin === false || $subnetBin === false) {
            return false;
        }
        
        $byteMask = $mask >> 3;
        $bitMask = $mask & 7;
        
        // 比较完整字节
        if ($byteMask > 0 && substr($ipBin, 0, $byteMask) !== substr($subnetBin, 0, $byteMask)) {
            return false;
        }
        
        // 比较剩余位
        if ($bitMask > 0 && $byteMask < 16) {
            $mask = 0xFF << (8 - $bitMask);
            if ((ord($ipBin[$byteMask]) & $mask) !== (ord($subnetBin[$byteMask]) & $mask)) {
                return false;
            }
        }
        
        return true;
    }
}
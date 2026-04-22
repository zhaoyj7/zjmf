<?php
namespace app\http\middleware;

use app\admin\model\AdminModel;

/*
 * @title 后台需要操作密码验证的中间件
 * @desc  后台需要操作密码验证的中间件
 * @use   app\http\middleware\CheckAdminOperatePassword
 * @author hh
 * */
class CheckAdminOperatePassword
{
    public function handle($request,\Closure $next, $scene = '')
    {
        $adminId = get_admin_id();
        
        $config = configuration(['admin_enforce_safe_method','admin_enforce_safe_method_scene']);
        $adminEnforceSafeMethod = !empty($config['admin_enforce_safe_method']) ? explode(',', $config['admin_enforce_safe_method']) : [];
        $adminEnforceSafeMethodScene = !empty($config['admin_enforce_safe_method_scene']) ? explode(',', $config['admin_enforce_safe_method_scene']) : [];

        if(in_array('operate_password', $adminEnforceSafeMethod)){
            // 检查场景是否需要操作密码
            $needOperatePassword = in_array('all', $adminEnforceSafeMethodScene) || in_array($scene, $adminEnforceSafeMethodScene);

            if($needOperatePassword){
                $cacheKey = 'ADMIN_OPERATE_PASSWORD_' . $adminId;

                $operatePassword = request()->param('admin_operate_password') ?: idcsmart_cache($cacheKey);

                if(idcsmart_password((string)$operatePassword) !== AdminModel::where('id', $adminId)->value('operate_password')){
                    return json([
                        'status'    => 400,
                        'msg'       => lang('operate_password_error'),
                        'data'      => [
                            'operate_password'      => 1,
                            'admin_operate_methods' => request()->param('admin_operate_methods') ?? '',
                        ]
                    ]);
                }else{
                    // 验证成功,是否保留15分钟
                    if(request()->param('remember_operate_password')){
                        idcsmart_cache($cacheKey, $operatePassword, 15*60);
                    }
                }
            }
        }
        return $next($request);
    }
}
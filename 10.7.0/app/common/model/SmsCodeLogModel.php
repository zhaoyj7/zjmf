<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 短信验证码日志模型
 * @desc 短信验证码日志模型
 * @use app\common\model\SmsCodeLogModel
 */
class SmsCodeLogModel extends Model
{
	protected $name = 'sms_code_log';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'phone_code'    => 'int',
        'phone'         => 'string',
        'user_type'     => 'string',
        'user_id'       => 'int',
        'user_name'     => 'string',
        'abnormal'      => 'int',
        'ip'            => 'string',
        'port'          => 'int',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2024-09-09
     * @title 添加短信验证码日志
     * @desc 添加短信验证码日志
     * @author theworld
     * @version v1
     * @param string param.type - 验证码类型
     * @param int param.phone_code - 国际区号
     * @param string param.phone - 手机号
     * @param int param.abnormal - 异常期间0否1是
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createSmsCodeLog($param)
    {   
        $clientIp = get_client_ip(); // 获取客户端IP
        $remotePort = request()->remotePort(); // 获取端口

        $param['type'] = $param['type'] ?? '';
        $param['phone_code'] = $param['phone_code'] ?? 44;
        $param['phone'] = $param['phone'] ?? '';
        $param['abnormal'] = intval($param['abnormal'] ?? 0);
        $adminId = get_admin_id();
        $clientId = get_client_id();
        if(empty($adminId) && empty($clientId)){
            $userType = 'unknown';
            $userId = 0;
            $userName = 'unknown';
        }else if(!empty($adminId) && empty($clientId)){
            $userType = 'admin';
            $userId = $adminId;
            $userName = request()->admin_name;
        }else if(empty($adminId) && !empty($clientId) && empty(request()->api_id)){
            $userType = 'client';
            $userId = $clientId;
            $userName = request()->client_name;
            if($clientId!=get_client_id(false)){
                $userName = $userName.lang('sub_account');
                $userId = get_client_id(false);
            }
        }else if(empty($adminId) && !empty($clientId) && !empty(request()->api_id)){
            $userType = 'api';
            $userId = request()->api_id;
            $userName = request()->api_name;
        }

        try {

            $this->create([
                'type' => $param['type'],
                'phone_code' => $param['phone_code'],
                'phone' => $param['phone'],
                'user_type' => $userType,
                'user_id' => $userId,
                'user_name' => $userName,
                'abnormal' => $param['abnormal'],
                'ip' => $clientIp,
                'port' => $remotePort,
                'create_time' => time(),
            ]);

            
        } catch (\Exception $e) {
            // 回滚事务
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

}

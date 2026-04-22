<?php
namespace app\common\model;

use think\Model;

/**
 * @title 用户流量预警模型
 * @desc  用户流量预警模型
 * @use app\common\model\ClientTrafficWarningModel
 */
class ClientTrafficWarningModel extends Model
{
    protected $name = 'client_traffic_warning';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'client_id'         => 'int',
        'module'            => 'string',
        'warning_switch'    => 'int',
        'leave_percent'     => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    // 支持的模块标识
    protected $module = ['mf_cloud','mf_dcim','mf_cloud_mysql'];

    // 缓存当前获取过的
    protected static $clientTrafficWarning = [];

    /**
     * 时间 2025-04-21
     * @title 用户流量预警详情
     * @desc  用户流量预警详情
     * @author hh
     * @version v1
     * @param array param - 请求参数 require
     * @param int param.client_id - 用户ID require
     * @param string param.module - 模块标识 require
     * @return string module - 模块标识
     * @return int warning_switch - 预警开关(0=关闭,1=开启)
     * @return int leave_percent - 预警百分比
     */
    public function clientTrafficWarningIndex($param): array
    {
        if(empty($param['client_id']) || empty($param['module']) || !in_array($param['module'], $this->module)){
            return [];
        }
        $clientTrafficWarning = $this
                            ->field('module,warning_switch,leave_percent')
                            ->where('client_id', $param['client_id'])
                            ->where('module', $param['module'])
                            ->find();
        if(empty($clientTrafficWarning)){
            $this->create([
                'client_id'         => $param['client_id'],
                'module'            => $param['module'],
                'warning_switch'    => 0,
                'leave_percent'     => 0,
                'create_time'       => time(),
            ]);

            $clientTrafficWarning = [
                'module'            => $param['module'],
                'warning_switch'    => 0,
                'leave_percent'     => 0,
            ];
        }else{
            $clientTrafficWarning = $clientTrafficWarning->toArray();
        }
        return $clientTrafficWarning;
    }

    /**
     * 时间 2025-04-21
     * @title 保存用户流量预警
     * @desc  保存用户流量预警
     * @author hh
     * @version v1
     * @param array param - 请求参数 require
     * @param string param.module - 模块标识 require
     * @param int param.warning_switch - 预警开关(0=关闭,1=开启) require
     * @param int param.leave_percent - 预警百分比 require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 信息
     */
    public function clientTrafficWarningUpdate($param): array
    {
        $clientId = get_client_id();
        if(empty($param['module']) || !in_array($param['module'], $this->module)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        // 关闭了
        if($param['warning_switch'] == 1){
            if(empty($param['leave_percent'])){
                return ['status'=>400, 'msg'=>lang('please_select_traffic_leave_percent')];
            }
        }else{
            $param['leave_percent'] = 0;
        }

        $clientTrafficWarning = $this->clientTrafficWarningIndex([
            'client_id' => $clientId,
            'module'    => $param['module'],
        ]);

        $update = $this
                ->where('client_id', $clientId)
                ->where('module', $param['module'])
                ->update([
                    'warning_switch'    => $param['warning_switch'],
                    'leave_percent'     => $param['leave_percent'],
                    'update_time'       => time(),
                ]);

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2025-04-21
     * @title 用户流量预警详情
     * @desc  用户流量预警详情
     * @author hh
     * @version v1
     * @param array param - 请求参数 require
     * @param int param.client_id - 用户ID require
     * @param string param.module - 模块标识 require
     * @return string module - 模块标识
     * @return int warning_switch - 预警开关(0=关闭,1=开启)
     * @return int leave_percent - 预警百分比
     */
    public static function getClientTrafficWarning($param)
    {
        if(!empty(self::$clientTrafficWarning[$param['client_id']][$param['module']])){
            return self::$clientTrafficWarning[$param['client_id']][$param['module']];
        }
        $ClientTrafficWarningModel = new ClientTrafficWarningModel();
        $clientTrafficWarning = $ClientTrafficWarningModel->clientTrafficWarningIndex($param);
        
        self::$clientTrafficWarning[$param['client_id']][$param['module']] = $clientTrafficWarning;
        return $clientTrafficWarning;
    }


}
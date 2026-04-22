<?php 
namespace server\mf_dcim\logic;

use app\common\logic\DownstreamHostLogic;
use app\common\model\HostModel;
use app\common\model\UpgradeModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamOrderModel;
use server\mf_dcim\model\HostLinkModel;
use think\facade\Db;

/**
 * @title 下游DCIM操作类
 * @use   server\mf_dcim\logic\DownstreamCloudLogic
 */
class DownstreamCloudLogic extends DownstreamHostLogic
{
	/**
	 * 时间 2024-08-08
	 * @title 获取电源状态
	 * @desc  获取电源状态
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.status - 实例状态(on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status()
	{
		$path = sprintf('console/v1/mf_dcim/%d/status', $this->upstreamHostId);

		$result = $this->curl($path, [], 'GET');
        return $result;
	}

    /**
     * @时间 2024-08-09
     * @title 开机
     * @desc  开机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function on()
    {
        $path = sprintf('console/v1/mf_dcim/%d/on', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 关机
     * @desc  关机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function off()
    {
        $path = sprintf('console/v1/mf_dcim/%d/off', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 重启
     * @desc  重启
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function reboot()
    {
        $path = sprintf('console/v1/mf_dcim/%d/reboot', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 获取控制台地址
     * @desc  获取控制台地址
     * @author hh
     * @version v1
     * @param   int param.more 0 是否获取更多返回(0=否,1=是)
     * @return  string url - 控制台地址
     * @return  string vnc_url - vncwebsocket地址(more=1返回)
     * @return  string vnc_pass - VNC密码(more=1返回)
     * @return  string password - 实例密码(more=1返回)
     * @return  string token - 临时令牌(more=1返回)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function vnc()
    {
        $path = sprintf('console/v1/mf_dcim/%d/vnc?more=1', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-25
     * @title 重启VNC
     * @desc  重启VNC
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function restartVnc()
    {
        $path = sprintf('console/v1/mf_dcim/%d/vnc/restart', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * 时间 2024-12-20
     * @title 获取重装状态
     * @desc 获取重装状态
     * @author hh
     * @version v1
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return array data - 状态数据
     * @return int data.task_type - 任务类型(0=重装中)
     */
    public function getReinstallStatus()
    {
        $path = sprintf('console/v1/mf_dcim/%d/reinstall_status', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 重置密码
     * @desc  重置密码
     * @author hh
     * @version v1
     * @param   string param.password - 新密码 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function resetPassword($param)
    {
        $path = sprintf('console/v1/mf_dcim/%d/reset_password', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 救援模式
     * @desc  救援模式
     * @author hh
     * @version v1
     * @param   int param.type - 指定救援系统类型(1=windows,2=linux) require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function rescue($param)
    {
        $path = sprintf('console/v1/mf_dcim/%d/rescue', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 退出救援模式
     * @desc  退出救援模式
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function exitRescue()
    {
        $path = sprintf('console/v1/mf_dcim/%d/rescue/exit', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 重装系统
     * @desc  重装系统
     * @author hh
     * @version v1
     * @param   int param.image_id - 镜像ID require
     * @param   int param.password - 密码 require
     * @param   int param.port - 端口 require
     * @param   int param.part_type - 分区类型0全盘格式化1第一分区格式化 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function reinstall($param)
    {
        $path = sprintf('console/v1/mf_dcim/%d/reinstall', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 获取图表数据
     * @desc  获取图表数据
     * @author hh
     * @version v1
     * @param   int param.start_time - 开始秒级时间
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 图表数据
     * @return  int data.list[].time - 时间(秒级时间戳)
     * @return  float data.list[].in_bw - 进带宽
     * @return  float data.list[].out_bw - 出带宽
     * @return  string data.unit - 当前单位
     */
    public function chart($param)
    {
        $path = sprintf('console/v1/mf_dcim/%d/chart', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 获取网络流量
     * @desc  获取网络流量
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.total -总流量
     * @return  string data.used -已用流量
     * @return  string data.leave - 剩余流量
     * @return  string data.reset_flow_date - 流量归零时间
     * @return  int data.total_num - 总流量大小(0=不限)
     * @return  float data.used_num - 已用流量大小
     * @return  int data.base_flow - 基础流量(0=不限)
     * @return  int data.temp_flow - 临时流量
     */
    public function flowDetail()
    {
        $path = sprintf('console/v1/mf_dcim/%d/flow', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 获取DCIM远程信息
     * @desc  获取DCIM远程信息
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.username - 远程用户名
     * @return  string data.password - 远程密码
     * @return  string data.port - 远程端口
     * @return  int data.ip_num - IP数量
     */
    public function remoteInfo()
    {
        $path = sprintf('console/v1/mf_dcim/%d/remote_info', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title IP列表
     * @desc  IP列表
     * @author hh
     * @version v1
     * @param int param.page 1 页数
     * @param int param.limit - 每页条数
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return array data.list - 列表数据
     * @return string data.list[].ip - IP
     * @return string data.list[].subnet_mask - 掩码
     * @return string data.list[].gateway - 网关
     * @return int data.count - 总条数
     */
    public function ipList($param)
    {
        $path = sprintf('console/v1/mf_dcim/%d/ip', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /* 升降级方法 */

    /**
     * 时间 2024-08-12
     * @title 上游购买镜像
     * @desc  上游购买镜像
     * @author hh
     * @version v1
     * @param   int param.image_id - 镜像ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function buyImage($param, $orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_dcim/%d/image/order', $this->upstreamHostId);

            $result = $this->curl($path, $param, 'POST');
            if($result['status'] == 200){
                $UpstreamOrderModel = new UpstreamOrderModel();
                $UpstreamOrderModel->where('order_id',$orderId)
                    ->where('host_id',$this->host['id'])
                    ->update([
                        'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                    ]);
                $creditData = [
                    'id'    => $result['data']['id'],
                    'use'   => 1,
                ];
                # 使用余额
                $result1 = $this->curl('/console/v1/credit', $creditData, 'POST');
                if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                    // 使用平台币支付
                    if (!empty($result1['data']['customfields']['agent_use_coin'])){
                        $resultCoin = $this->curl('/console/v1/coin/pay',[
                            'auto'=>1,
                            'coin_coupon_ids'=>[],
                            'order_id' => $result1['data']['id'],
                            'use' => 1,
                        ],'POST');
                        if ($resultCoin['status']==200){
                            $result['data']['amount'] = $resultCoin['data']['amount']??0;
                        }
                    }
                    // 亏本交易拦截检查
                    $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $this->host['id']);
                    if (!$checkResult['pass']) {
                        return ['status'=>400, 'msg'=>$checkResult['msg']];
                    }
                    $payData = [
                        'id'        => $result['data']['id'],
                        'gateway'   => 'credit'
                    ];
                    # 支付(余额支付失败后回退信用额支付)
                    $result = $this->payWithFallback($payData);
                }
            }
        }catch(\Exception $e){
            $result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 上游产品配置升级
     * @desc  上游产品配置升级
     * @author hh
     * @version v1
     * @param   string param.ip_num - 公网IP数量
     * @param   string param.bw - 带宽
     * @param   int param.flow - 流量包
     * @param   int param.peak_defence - 防御峰值
     * @param   array param.optional_memory - 变更后的内存({"5":1},5是ID,1是数量)
     * @param   array param.optional_disk - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @param   array param.optional_gpu - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeCommonConfig($param,$orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_dcim/%d/common_config/order', $this->upstreamHostId);

            $result = $this->curl($path, $param, 'POST');
            if($result['status'] == 200){
                $UpstreamOrderModel = new UpstreamOrderModel();
                $UpstreamOrderModel->where('order_id',$orderId)
                    ->where('host_id',$this->host['id'])
                    ->update([
                        'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                    ]);
                $creditData = [
                    'id'    => $result['data']['id'],
                    'use'   => 1,
                ];
                # 使用余额
                $result1 = $this->curl('/console/v1/credit', $creditData, 'POST');
                if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                    // 使用平台币支付
                    if (!empty($result1['data']['customfields']['agent_use_coin'])){
                        $resultCoin = $this->curl('/console/v1/coin/pay',[
                            'auto'=>1,
                            'coin_coupon_ids'=>[],
                            'order_id' => $result1['data']['id'],
                            'use' => 1,
                        ],'POST');
                        if ($resultCoin['status']==200){
                            $result['data']['amount'] = $resultCoin['data']['amount']??0;
                        }
                    }
                    // 亏本交易拦截检查
                    $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $this->host['id']);
                    if (!$checkResult['pass']) {
                        return ['status'=>400, 'msg'=>$checkResult['msg']];
                    }
                    $payData = [
                        'id'        => $result['data']['id'],
                        'gateway'   => 'credit'
                    ];
                    # 支付(余额支付失败后回退信用额支付)
                    $result = $this->payWithFallback($payData);
                }
            }
        }catch(\Exception $e){
            $result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 上游生成升级防御订单
     * @desc  上游生成升级防御订单
     * @author theworld
     * @version v1
     * @param   int param.ip - IP require
     * @param   string param.peak_defence - 防御峰值 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeDefence($param, $orderId=0)
    {
        try{
            $subHostId = $this->host['id'];
            $hostLink = HostLinkModel::where('host_id',$subHostId)->where('ip',$param['ip'])->find();
            if (!empty($hostLink)){
                $hostId = $hostLink['parent_host_id']??0;
                $this->host = HostModel::where('id',$hostId)->find();
                $this->upstreamHostId = UpstreamHostModel::where('host_id',$hostId)->value('upstream_host_id');
            }
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_dcim/%d/upgrade_defence/order', $this->upstreamHostId);

            $result = $this->curl($path, $param, 'POST');
            if($result['status'] == 200){
                if (isset($result['data']['amount']) && $result['data']['amount'] == 0){
                    $hostIps[ $param['ip'] ] = '';

                    $defence = explode('_', $param['peak_defence']);

                    $defenceRuleId = array_pop($defence);
                    $firewallType = implode('_', $defence);
                    hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $defenceRuleId, 'host_ips' => $hostIps]);

                    // 更改ip对应子产品到期时间以及续费金额
                    if (!empty($hostLink)){
                        $upgrade = UpgradeModel::where('order_id',$orderId)
                            ->where('host_id',$subHostId)
                            ->where('type','config_option')
                            ->find();
                        $custom = json_decode($upgrade['data'],true);
                        $renewAmount = $upgrade['renew_price']??0;
                        $update = [
                            'renew_amount' => max($renewAmount, 0), // upgrade表里面存的是主产品续费金额+续费差价，需要减去主产品续费金额
                            'update_time' => time(),
                        ];
                        if (!empty($custom['upgrade_with_duration'])){
                            $update = array_merge($update, [
                                'due_time' => time() + ($custom['due_time']??0),
                                'billing_cycle_name' => $custom['duration']['name']??'',
                                'billing_cycle_time' => $custom['due_time']??0,
                            ]);
                        }
                        if (!empty($custom['is_default_defence'])){
                            $parentHost = HostModel::where('id',$hostId)->find();
                            if (!empty($parentHost)){
                                $update = array_merge($update, [
                                    'due_time' => $parentHost['due_time'],
                                ]);
                            }
                        }
                        HostModel::where('id',$hostLink['host_id'])->update($update);
                    }
                    return $result;
                }
                $UpstreamOrderModel = new UpstreamOrderModel();
                $UpstreamOrderModel->where('order_id',$orderId)
                    ->where('host_id',$this->host['id'])
                    ->update([
                        'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                    ]);
                $creditData = [
                    'id'    => $result['data']['id'],
                    'use'   => 1,
                ];
                # 使用余额
                $result1 = $this->curl('/console/v1/credit', $creditData, 'POST');
                if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                    // 使用平台币支付
                    if (!empty($result1['data']['customfields']['agent_use_coin'])){
                        $resultCoin = $this->curl('/console/v1/coin/pay',[
                            'auto'=>1,
                            'coin_coupon_ids'=>[],
                            'order_id' => $result1['data']['id'],
                            'use' => 1,
                        ],'POST');
                        if ($resultCoin['status']==200){
                            $result['data']['amount'] = $resultCoin['data']['amount']??0;
                        }
                    }
                    // 亏本交易拦截检查
                    $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $this->host['id']);
                    if (!$checkResult['pass']) {
                        return ['status'=>400, 'msg'=>$checkResult['msg']];
                    }
                    $payData = [
                        'id'        => $result['data']['id'],
                        'gateway'   => 'credit'
                    ];
                    # 支付(余额支付失败后回退信用额支付)
                    $result = $this->payWithFallback($payData);
                    if ($result['status']==200){
                        $hostIps[ $param['ip'] ] = '';

                        $defence = explode('_', $param['peak_defence']);

                        $defenceRuleId = array_pop($defence);
                        $firewallType = implode('_', $defence);
                        hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $defenceRuleId, 'host_ips' => $hostIps]);

                        // 更改ip对应子产品到期时间以及续费金额
                        if (!empty($hostLink)){
                            $upgrade = UpgradeModel::where('order_id',$orderId)
                                ->where('host_id',$subHostId)
                                ->where('type','config_option')
                                ->find();
                            $custom = json_decode($upgrade['data'],true);
                            $renewAmount = $upgrade['renew_price']??0;
                            $update = [
                                'renew_amount' => max($renewAmount, 0), // upgrade表里面存的是主产品续费金额+续费差价，需要减去主产品续费金额
                                'update_time' => time(),
                            ];
                            if (!empty($custom['upgrade_with_duration'])){
                                $update = array_merge($update, [
                                    'due_time' => time() + ($custom['due_time']??0),
                                    'billing_cycle_name' => $custom['duration']['name']??'',
                                    'billing_cycle_time' => $custom['due_time']??0,
                                ]);
                            }
                            if (!empty($custom['is_default_defence'])){
                                $parentHost = HostModel::where('id',$hostId)->find();
                                if (!empty($parentHost)){
                                    $update = array_merge($update, [
                                        'due_time' => $parentHost['due_time'],
                                    ]);
                                }
                            }
                            HostModel::where('id',$hostLink['host_id'])->update($update);
                        }
                    }
                }
            }
        }catch(\Exception $e){
            $result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return $result;
    }
}
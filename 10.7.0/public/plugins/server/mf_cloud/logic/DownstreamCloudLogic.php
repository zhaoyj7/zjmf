<?php 
namespace server\mf_cloud\logic;

use app\common\logic\DownstreamHostLogic;
use app\common\model\HostModel;
use app\common\model\UpgradeModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamOrderModel;
use server\mf_cloud\model\HostLinkModel;
use think\facade\Db;

/**
 * @title 下游云操作类
 * @use   server\mf_cloud\logic\DownstreamCloudLogic
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
		$path = sprintf('console/v1/mf_cloud/%d/status', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/on', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/off', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 强制关机
     * @desc  强制关机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function hardOff()
    {
        $path = sprintf('console/v1/mf_cloud/%d/hard_off', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/reboot', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * @时间 2024-08-09
     * @title 强制重启
     * @desc  强制重启
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function hardReboot()
    {
        $path = sprintf('console/v1/mf_cloud/%d/hard_reboot', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/vnc?more=1', $this->upstreamHostId);

        $result = $this->curl($path, [], 'POST');
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
        $path = sprintf('console/v1/mf_cloud/%d/reset_password', $this->upstreamHostId);

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
     * @param   string param.password - 救援系统临时密码 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function rescue($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/rescue', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/rescue/exit', $this->upstreamHostId);

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
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function reinstall($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/reinstall', $this->upstreamHostId);

        // 不支持SSH密钥
        $param['ssh_key_id'] = 0;

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 获取图表数据
     * @desc  获取图表数据
     * @author hh
     * @version v1
     * @param   int start_time - 开始秒级时间
     * @param   string type - 图表类型(cpu=CPU,memory=内存,disk_io=硬盘IO,bw=带宽) require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.list[].time - 时间(秒级时间戳)
     * @return  float data.list[].value - CPU使用率
     * @return  int data.list[].total - 总内存(单位:B)
     * @return  int data.list[].used - 内存使用量(单位:B)
     * @return  float data.list[].read_bytes - 读取速度(B/s)
     * @return  float data.list[].write_bytes - 写入速度(B/s)
     * @return  float data.list[].read_iops - 读取IOPS
     * @return  float data.list[].write_iops - 写入IOPS
     * @return  float data.list[].in_bw - 进带宽(bps)
     * @return  float data.list[].out_bw - 出带宽(bps)
     */
    public function chart($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/chart', $this->upstreamHostId);

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
     */
    public function flowDetail()
    {
        $path = sprintf('console/v1/mf_cloud/%d/flow', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 获取魔方云远程信息
     * @desc  获取魔方云远程信息
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.rescue - 是否正在救援系统(0=不是,1=是)
     * @return  string data.username - 远程用户名
     * @return  string data.password - 远程密码
     * @return  int data.port - 远程端口
     * @return  int data.ip_num - IP数量
     * @return  int data.simulate_physical_machine - 模拟物理机运行(0=关闭,1=开启) 
     */
    public function remoteInfo()
    {
        $path = sprintf('console/v1/mf_cloud/%d/remote_info', $this->upstreamHostId);

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
        $path = sprintf('console/v1/mf_cloud/%d/ip', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title IPv6列表
     * @desc  IPv6列表
     * @author hh
     * @version v1
     * @param int param.page 1 页数
     * @param int param.limit - 每页条数
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return array data.list - 列表数据
     * @return string data.list[].ipv6 - IPv6地址
     * @return int data.count - 总条数
     */
    public function ipv6List($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/ipv6', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-12
     * @title 模拟物理机运行
     * @desc  模拟物理机运行
     * @author hh
     * @version v1
     * @param  int param.simulate_physical_machine - 模拟物理机运行(0=关闭,1=开启) require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function simulatePhysicalMachine($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/simulate_physical_machine', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 磁盘列表
     * @desc  磁盘列表
     * @author hh
     * @version v1
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  int data.list[].id - 磁盘ID
     * @return  string data.list[].name - 名称
     * @return  int data.list[].size - 磁盘大小(GB)
     * @return  int data.list[].create_time - 创建时间
     * @return  string data.list[].type - 磁盘类型
     * @return  string data.list[].type2 - 类型(system=系统盘,data=数据盘)
     * @return  int data.list[].is_free - 是否免费盘(0=否,1=是),免费盘不能扩容
     * @return  int data.list[].status - 磁盘状态(0=卸载,1=挂载,2=正在挂载,3=创建中)
     * @return  string data.list[].type2 - 类型(system=系统盘,data=数据盘)
     */
    public function diskList()
    {
        $path = sprintf('console/v1/mf_cloud/%d/disk', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 卸载磁盘
     * @desc  卸载磁盘
     * @author hh
     * @version v1
     * @param  int param.disk_id - 磁盘ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return string data.name - 磁盘名称
     */
    public function diskUnmount($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/disk/%d/unmount', $this->upstreamHostId, $param['disk_id']);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 挂载磁盘
     * @desc  挂载磁盘
     * @author hh
     * @version v1
     * @param  int param.disk_id - 磁盘ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return string data.name - 磁盘名称
     */
    public function diskMount($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/disk/%d/mount', $this->upstreamHostId, $param['disk_id']);

        $result = $this->curl($path, [], 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 快照列表
     * @desc  快照列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  int data.list[].id - 快照ID
     * @return  string data.list[].name - 快照名称
     * @return  int data.list[].create_time - 创建时间
     * @return  string data.list[].notes - 备注
     * @return  int data.list[].status - 状态(0=创建中,1=创建完成)
     * @return  int data.count - 总条数
     */
    public function snapshotList($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/snapshot', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 创建快照
     * @desc  创建快照
     * @author hh
     * @version v1
     * @param   int param.name - 快照名称 require
     * @param   int param.disk_id - 磁盘ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function snapshotCreate($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/snapshot', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 快照还原
     * @desc  快照还原
     * @author hh
     * @version v1
     * @param   int param.snapshot_id - 快照ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  string data.name - 快照名称
     */
    public function snapshotRestore($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/snapshot/restore', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 删除快照
     * @desc  删除快照
     * @author hh
     * @version v1
     * @param   int param.snapshot_id - 快照ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  string data.name - 快照名称
     */
    public function snapshotDelete($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/snapshot/%d', $this->upstreamHostId, $param['snapshot_id']);

        $result = $this->curl($path, [], 'DELETE');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 备份列表
     * @desc  备份列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  int data.list[].id - 备份ID
     * @return  string data.list[].name - 备份名称
     * @return  int data.list[].create_time - 创建时间
     * @return  string data.list[].notes - 备注
     * @return  int data.list[].status - 状态(0=创建中,1=创建成功)
     * @return  int data.count - 总条数
     */
    public function backupList($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/backup', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 创建备份
     * @desc  创建备份
     * @author hh
     * @version v1
     * @param   int param.name - 备份名称 require
     * @param   int param.disk_id - 磁盘ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function backupCreate($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/backup', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 备份还原
     * @desc  备份还原
     * @author hh
     * @version v1
     * @param   int param.backup_id - 备份ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  string data.name - 备份名称
     */
    public function backupRestore($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/backup/restore', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-13
     * @title 删除备份
     * @desc  删除备份
     * @author hh
     * @version v1
     * @param   int param.backup_id - 备份ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  string data.name - 备份名称
     */
    public function backupDelete($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/backup/%d', $this->upstreamHostId, $param['backup_id']);

        $result = $this->curl($path, [], 'DELETE');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title NAT转发列表
     * @desc  NAT转发列表
     * @author hh
     * @version v1
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  int data.list[].id - 转发ID
     * @return  string data.list[].name - 名称
     * @return  string data.list[].ip - IP端口
     * @return  int data.list[].int_port - 内部端口
     * @return  int data.list[].protocol - 协议(1=tcp,2=udp,3=tcp+udp)
     * @return  int data.count - 总条数
     */
    public function natAclList()
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_acl', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 创建NAT转发
     * @desc  创建NAT转发
     * @author hh
     * @version v1
     * @param   string param.name - 名称 require
     * @param   int param.int_port - 内部端口 require
     * @param   int param.protocol - 协议(1=tcp,2=udp,3=tcp+udp) require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function natAclCreate($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_acl', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 删除NAT转发
     * @desc  删除NAT转发
     * @author hh
     * @version v1
     * @param   string param.nat_acl_id - NAT转发ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function natAclDelete($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_acl', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'DELETE');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title NAT建站列表
     * @desc  NAT建站列表
     * @author hh
     * @version v1
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  int data.list[].id - 建站ID
     * @return  string data.list[].domain - 域名
     * @return  int data.list[].ext_port - 外部端口
     * @return  int data.list[].int_port - 内部端口
     * @return  int data.count - 总条数
     */
    public function natWebList()
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_web', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 创建NAT建站
     * @desc  创建NAT建站
     * @author hh
     * @version v1
     * @param   string param.domain - 域名 require
     * @param   int param.int_port - 内部端口 require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function natWebCreate($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_web', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 删除NAT建站
     * @desc  删除NAT建站
     * @author hh
     * @version v1
     * @param   string param.nat_web_id - NAT建站ID require
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     */
    public function natWebDelete($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/nat_web', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'DELETE');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 获取cpu/内存使用信息
     * @desc  获取cpu/内存使用信息
     * @author hh
     * @version v1
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return  string data.cpu_usage - CPU使用率
     * @return  string data.memory_total - 内存总量(‘-’代表获取不到)
     * @return  string data.memory_usable - 已用内存(‘-’代表获取不到)
     * @return  string data.memory_usage - 内存使用百分比(‘-1’代表获取不到)
     */
    public function realData()
    {
        $path = sprintf('console/v1/mf_cloud/%d/real_data', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 创建VPC网络
     * @desc  创建VPC网络
     * @author hh
     * @version v1
     * @param   string param.name - VPC网络名称 require
     * @param   string param.ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - VPC网络ID
     */
    public function vpcNetworkCreate($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/vpc_network', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'POST');
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 创建VPC网络
     * @desc  创建VPC网络
     * @author hh
     * @version v1
     * @param   int param.vpc_network_id - 新VPCID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 变更后VPC网络名称
     */
    public function changeVpcNetwork($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/vpc_network', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'PUT');
        return $result;
    }

    /**
     * 时间 2024-12-20
     * @title 下载RDP
     * @desc  下载RDP
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.content - 下载RDP内容
     * @return  string data.name - 下载文件名
     */
    public function downloadRdp()
    {
        $path = sprintf('console/v1/mf_cloud/%d/download_rdp', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * 时间 2024-12-20
     * @title 是否可以续费
     * @desc  是否可以续费
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function whetherRenew()
    {
        $path = sprintf('console/v1/mf_cloud/%d/whether_renew', $this->upstreamHostId);

        $result = $this->curl($path, [], 'GET');
        return $result;
    }

    /**
     * @时间 2025-04-17
     * @title 实例流量数据
     * @desc  实例流量数据
     * @author hh
     * @version v1
     * @param   array param - 参数 require
	 * @param   int param.start_time - 开始时间 require
	 * @param   int param.end_time - 结束时间
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function flowData($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/flow_data', $this->upstreamHostId);

        $result = $this->curl($path, $param, 'GET');
        return $result;
    }

    /**
     * 时间 2025-09-17
     * @title 流量包列表
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     */
    public function trafficPackageList($param)
    {
        $path = sprintf('console/v1/mf_cloud/%d/traffic_package', $this->upstreamHostId);

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
            $path = sprintf('console/v1/mf_cloud/%d/image/order', $this->upstreamHostId);

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
                    $payData = [
                        'id'        => $result['data']['id'],
                        'gateway'   => 'credit'
                    ];
                    // 亏本交易拦截检查
                    $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $this->host['id']);
                    if (!$checkResult['pass']) {
                        return ['status'=>400, 'msg'=>$checkResult['msg']];
                    }
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
     * @param   int param.cpu - 核心数 require
     * @param   int param.memory - 内存 require
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeCommonConfig($param,$orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/common_config/order', $this->upstreamHostId);

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
     * @title 上游产品IP升级
     * @desc  上游产品IP升级
     * @author hh
     * @version v1
     * @param   int param.ip_num - 附加IP数量 require
     * @param   int param.ipv6_num - 附加IPv6数量
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeIpNum($param,$orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/ip_num/order', $this->upstreamHostId);

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
     * @title 上游产品快照/备份数量升降级
     * @desc  上游产品快照/备份数量升降级
     * @author hh
     * @version v1
     * @param   string param.type - 类型(snap=快照,backup=备份)
     * @param   int param.num - 备份/快照数量
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeBackup($param, $orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/backup_config/order', $this->upstreamHostId);

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
     * @title 上游磁盘扩容升降级
     * @desc  上游磁盘扩容升降级
     * @author hh
     * @version v1
     * @param   int param.resize_data_disk[].id - 磁盘ID
     * @param   int param.resize_data_disk[].size - 磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeResizeDisk($param, $orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/disk/resize/order', $this->upstreamHostId);

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
     * @title 上游购买磁盘升降级
     * @desc  上游购买磁盘升降级
     * @author hh
     * @version v1
     * @param   array param.remove_disk_id - 要取消订购的磁盘ID
     * @param   array param.add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
     * @param   int param.add_disk[].size - 磁盘大小
     * @param   string param.add_disk[].type - 磁盘类型
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function buyDisk($param, $orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/disk/order', $this->upstreamHostId);

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
     * @title 上游套餐升降级
     * @desc  上游套餐升降级
     * @author hh
     * @version v1
     * @param   int param.recommend_config_id - 套餐ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function upgradeRecommendConfig($param, $orderId=0)
    {
        try{
            // 先在上游创建订单
            $path = sprintf('console/v1/mf_cloud/%d/recommend_config/order', $this->upstreamHostId);

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
     * @author hh
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
            $path = sprintf('console/v1/mf_cloud/%d/upgrade_defence/order', $this->upstreamHostId);

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
<?php
namespace server\mf_cloud\controller\admin;

use think\response\Json;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\validate\ConfigValidate;
use server\mf_cloud\validate\BackupConfigValidate;
use server\mf_cloud\validate\ResourcePackageValidate;
use server\mf_cloud\validate\GlobalDefenceValidate;

/**
 * @title 魔方云(自定义配置)-其他设置
 * @desc 魔方云(自定义配置)-其他设置
 * @use server\mf_cloud\controller\admin\ConfigController
 */
class ConfigController
{
	/**
	 * 时间 2022-06-20
	 * @title 获取设置
	 * @desc 获取设置
	 * @url /admin/v1/mf_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @return  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @return  int rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口)
     * @return  int support_normal_network - 经典网络(0=不支持,1=支持)
     * @return  int support_vpc_network - VPC网络(0=不支持,1=支持)
     * @return  int support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int disk_limit_enable - 性能限制(0=不启用,1=启用)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @return  string ipv6_num - IPv6数量
     * @return  string nat_acl_limit - NAT转发限制
     * @return  string nat_web_limit - NAT建站限制
     * @return  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string memory_unit - 内存单位(GB,MB)
     * @return  string type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低,4=填满一个)
     * @return  int disk_limit_switch - 数据盘数量限制开关(0=关闭,1=开启)
     * @return  int disk_limit_num - 数据盘限制数量
     * @return  int free_disk_switch - 免费数据盘开关(0=关闭,1=开启)
     * @return  int free_disk_size - 免费数据盘大小(G)
     * @return  string free_disk_type - 免费数据盘类型
     * @return  int only_sale_recommend_config - 仅售卖套餐(0=关闭,1=开启)
     * @return  int no_upgrade_tip_show - 不可升降级时订购页提示(0=关闭,1=开启)
     * @return  int default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @return  int default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @return  bool is_agent - 是否是代理商(是的时候才能添加资源包)
     * @return  string rand_ssh_port_start - 随机端口开始端口
     * @return  string rand_ssh_port_end - 随机端口结束端口
     * @return  string rand_ssh_port_windows - 指定端口Windows
     * @return  string rand_ssh_port_linux - 指定端口Linux
     * @return  int default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启)
     * @return  int manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  int backup_data[].id - 备份配置ID
     * @return  int backup_data[].num - 备份数量
     * @return  string backup_data[].price - 备份价格
     * @return  string backup_data[].on_demand_price - 备份按需价格
     * @return  int snap_data[].id - 快照配置ID
     * @return  int snap_data[].num - 快照数量
     * @return  string snap_data[].price - 快照价格
     * @return  string snap_data[].on_demand_price - 快照按需价格
     * @return  int resource_package[].id - 资源包ID
     * @return  int resource_package[].rid - 魔方云资源包ID
     * @return  array duration_id - 不允许申请停用周期ID
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int level_discount_cpu_order - CPU是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_cpu_upgrade - CPU是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_cpu_renew - CPU是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_ipv4_order - IPv4是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_ipv4_upgrade - IPv4是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_ipv4_renew - IPv4是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_ipv6_order - IPv6是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_ipv6_upgrade - IPv6是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_ipv6_renew - IPv6是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_system_disk_order - 系统盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_system_disk_upgrade - 系统盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_system_disk_renew - 系统盘是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_data_disk_order - 数据盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_data_disk_upgrade - 数据盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_data_disk_renew - 数据盘是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启)
     * @return  int disk_range_limit - 磁盘大小购买限制(GB)
     * @return  int simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启)
	 */
	public function index()
    {
		$param = request()->param();

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->indexConfig($param);
        if(!empty($result['data']['on_demand_duration'])){
            $result['data']['on_demand_duration'] = (object)$result['data']['on_demand_duration'];
        }

		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存设置
	 * @desc 保存设置
	 * @url /admin/v1/mf_cloud/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param  int product_id - 商品ID require
     * @param  string type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V) require
     * @param  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低,4=填满一个) require
     * @param  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @param  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @param  int rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口,3=用户自定义) require
     * @param  int support_normal_network - 经典网络(0=不支持,1=支持)
     * @param  int support_vpc_network - VPC网络(0=不支持,1=支持)
     * @param  int support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @param  int backup_enable - 是否启用备份(0=不启用,1=启用) require
     * @param  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用) require
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用) require
     * @param  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @param  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @param  string ipv6_num - IPv6数量
     * @param  string nat_acl_limit - NAT转发限制
     * @param  string nat_web_limit - NAT建站限制
     * @param  int default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @param  int default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @param  array backup_data - 允许备份数量数据
     * @param  int backup_data[].num - 数量
     * @param  float backup_data[].price - 价格
     * @param  float backup_data[].on_demand_price - 按需价格
     * @param  array snap_data - 允许快照数量数据
     * @param  int snap_data[].num - 数量
     * @param  float snap_data[].price - 价格
     * @param  float snap_data[].on_demand_price - 按需价格
     * @param  array resource_package - 资源包数据
     * @param  int resource_package[].rid - 魔方云资源包ID
     * @param  string resource_package[].name - 资源包名称
     * @param  string rand_ssh_port_start - 随机端口开始端口 requireIf,rand_ssh_port=1
     * @param  string rand_ssh_port_end - 随机端口结束端口 requireIf,rand_ssh_port=1
     * @param  string rand_ssh_port_windows - 指定端口Windows requireIf,rand_ssh_port=2
     * @param  string rand_ssh_port_linux - 指定端口Linux requireIf,rand_ssh_port=2
     * @param  int default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启) require
     * @param  int manual_manage - 手动管理商品(0=关闭,1=开启) requrie
     * @param  array duration_id - 不允许申请停用周期ID
     * @param  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @param  string default_password_length - 默认密码长度
     * @param  int level_discount_cpu_order - CPU是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_cpu_upgrade - CPU是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_cpu_renew - CPU是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_ipv4_order - IPv4是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_ipv4_upgrade - IPv4是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_ipv4_renew - IPv4是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_ipv6_order - IPv6是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_ipv6_upgrade - IPv6是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_ipv6_renew - IPv6是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_system_disk_order - 系统盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_system_disk_upgrade - 系统盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_system_disk_renew - 系统盘是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_data_disk_order - 数据盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_data_disk_upgrade - 数据盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_data_disk_renew - 数据盘是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启) require
     * @return  int level_discount_gpu_order - GPU是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_gpu_upgrade - GPU是否应用等级优惠升降级(0=不启用,1=启用)
	 */
	public function save()
    {
		$param = request()->param();

		$ConfigValidate = new ConfigValidate();
		if (!$ConfigValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $BackupConfigValidate = new BackupConfigValidate();
        if(isset($param['backup_data']) && is_array($param['backup_data'])){
        	foreach($param['backup_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['backup_data'] = null;
        }
        if(isset($param['snap_data']) && is_array($param['snap_data'])){
        	foreach($param['snap_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['snap_data'] = null;
        }
        if(isset($param['resource_package']) && is_array($param['resource_package'])){
            $ResourcePackageValidate = new ResourcePackageValidate();

            foreach($param['resource_package'] as $v){
                if (!$ResourcePackageValidate->scene('save')->check($v)){
                    return json(['status' => 400 , 'msg' => lang_plugins($ResourcePackageValidate->getError())]);
                }
            }
        }else{
            $param['resource_package'] = null;
        }

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->saveConfig($param);
		return json($result);
	}

    /**
     * 时间 2023-02-02
     * @title 切换性能限制开关
     * @desc 切换性能限制开关
     * @url  /admin/v1/mf_cloud/config/disk_limit_enable
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int status - 开关状态(0=关闭,1=开启) require
     */
    public function toggleDiskLimitEnable()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('toggle')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $param['field'] = 'disk_limit_enable';

        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->toggleSwitch($param);
        return json($result);
    }

    /**
     * 时间 2023-08-22
     * @title 检查切换类型后是否清空冲突数据
     * @desc 检查切换类型后是否清空冲突数据
     * @url /admin/v1/mf_cloud/config/check_clear
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int type - 类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V) require
     * @return  bool clear - 是否清空(false=否,true=是)
     * @return  array recommend_config_id - 套餐ID
     * @return  array line_id - 线路ID
     * @return  string desc - 描述
     */
    public function checkClear()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('check_clear')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $data = $ConfigModel->isClear($param['product_id'], $param['type']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2023-09-06
     * @title 保存数据盘数量限制
     * @desc 保存数据盘数量限制
     * @url /admin/v1/mf_cloud/config/disk_num_limit
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int disk_limit_switch - 数据盘数量限制开关(0=关闭,1=开启)
     * @param   int disk_limit_num - 数据盘限制数量
     */
    public function saveDiskNumLimitConfig()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('disk_num_limit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->saveDiskNumLimitConfig($param);
        return json($result);
    }

    /**
     * 时间 2023-09-11
     * @title 保存免费数据盘配置
     * @desc 保存免费数据盘配置
     * @url /admin/v1/mf_cloud/config/free_disk
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int free_disk_switch - 免费数据盘开关(0=关闭,1=开启) require
     * @param   int free_disk_size - 免费数据盘大小(G)
     * @param   string free_disk_type - 免费数据盘类型
     */
    public function saveFreeDiskConfig()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('free_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->saveFreeDiskConfig($param);
        return json($result);
    }

    /**
     * 时间 2023-10-24
     * @title 切换仅售卖套餐开关
     * @desc 切换仅售卖套餐开关
     * @url  /admin/v1/mf_cloud/config/only_sale_recommend_config
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int status - 开关状态(0=关闭,1=开启) require
     */
    public function toggleOnlySaleRecommendConfigEnable()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('toggle')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        if($param['status'] == 1){
            $count = RecommendConfigModel::where('product_id', $param['product_id'])->count();
            if($count == 0){
                return json(['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_add_recommend_config_first')]);
            }
        }
        
        $param['field'] = 'only_sale_recommend_config';

        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->toggleSwitch($param);
        return json($result);
    }

    /**
     * 时间 2023-11-20
     * @title 切换不可升降级订购页提示开关
     * @desc 切换不可升降级订购页提示开关
     * @url  /admin/v1/mf_cloud/config/no_upgrade_tip_show
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int status - 开关状态(0=关闭,1=开启) require
     */
    public function toggleNoUpgradeTipShowEnable()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('toggle')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        
        $param['field'] = 'no_upgrade_tip_show';

        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->toggleSwitch($param);
        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 保存全局防御设置
     * @desc  保存全局防御设置
     * @url /admin/v1/mf_cloud/config/global_defence
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @param   string order_default_defence - 订购默认防御
     */
    public function saveGlobalDefenceConfig()
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('global_defence')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->saveGlobalDefenceConfig($param);
        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 获取防火墙防御规则
     * @desc  获取防火墙防御规则
     * @url /admin/v1/mf_cloud/firewall_defence_rule
     * @method  GET
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array rule - 防火墙规则
     * @return  string rule[].type - 防火墙类型
     * @return  array rule[].list - 防御规则
     * @return  int rule[].list[].id - 防御规则ID
     * @return  string rule[].list[].name - 名称
     * @return  string rule[].list[].defense_peak - 防御峰值,单位Gbps
     * @return  int rule[].list[].enabled - 是否可用(0=否1=是)
     * @return  int rule[].list[].create_time - 创建时间
     * @return  int rule[].list[].update_time - 更新时间
     * @return  string rule[].type - 防火墙类型
     * @return  string rule[].name - 防火墙名称
     */
    public function firewallDefenceRule()
    {
        $param = request()->param();

        $ConfigModel = new ConfigModel();
        $data = $ConfigModel->firewallDefenceRule($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 导入防火墙防御规则
     * @desc  导入防火墙防御规则
     * @url /admin/v1/mf_cloud/firewall_defence_rule
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string firewall_type - 防火墙类型
     * @param   array defence_rule_id - 防御规则ID
     */
    public function importDefenceRule()
    {
        $param = request()->param();

        $GlobalDefenceValidate = new GlobalDefenceValidate();
        if (!$GlobalDefenceValidate->scene('import')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($GlobalDefenceValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::GLOBAL_DEFENCE;

        $OptionModel = new OptionModel();

        $result = $OptionModel->importDefenceRule($param);
        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 全局防护配置列表
     * @desc  全局防护配置列表
     * @url /admin/v1/mf_cloud/global_defence
     * @method  GET
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].value - 防御峰值(G)
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  string defence_data[].firewall_type - 防火墙类型
     * @return  int defence_data[].defence_rule_id - 防御规则ID
     * @return  string defence_data[].defence_rule_name - 防御规则名称
     * @return  string defence_data[].defense_peak - 防御峰值
     * @return  int defence_data[].duration_price[].id - 周期ID
     * @return  string defence_data[].duration_price[].name - 周期名称
     * @return  string defence_data[].duration_price[].price - 价格
     */
    public function globalDefenceList()
    {
        $param = request()->param();
        $param['rel_type'] = OptionModel::GLOBAL_DEFENCE;
        $param['rel_id'] = 0;

        $OptionModel = new OptionModel();
        $result = $OptionModel->globalDefenceList($param);

        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 全局防护配置详情
     * @desc  全局防护配置详情
     * @url /admin/v1/mf_cloud/global_defence/:id
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 防御峰值
     * @return  string firewall_type - 防火墙类型
     * @return  int defence_rule_id - 防御规则ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function globalDefenceIndex()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $data = $OptionModel->globalDefenceIndex((int)$param['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 修改全局防护配置
     * @desc  修改全局防护配置
     * @url /admin/v1/mf_cloud/global_defence/:id
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     */
    public function globalDefenceUpdate()
    {
        $param = request()->param();

        $GlobalDefenceValidate = new GlobalDefenceValidate();
        if (!$GlobalDefenceValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($GlobalDefenceValidate->getError())]);
        }

        $OptionModel = new OptionModel();

        $result = $OptionModel->optionUpdate($param);
        return json($result);
    }

    /**
     * 时间 2025-03-20
     * @title 全局防护拖动排序
     * @desc  全局防护拖动排序
     * @url /admin/v1/mf_cloud/global_defence/:id/drag_sort
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int param.prev_id - 前一个防御ID(0=表示置顶) require
     * @param   int param.id - 当前防御ID require
     */
    public function globalDefenceDragSort()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $result = $OptionModel->globalDefenceDragSort($param);

        return json($result);
    }

    /**
     * 时间 2025-01-14
     * @title 删除全局防护配置
     * @desc  删除全局防护配置
     * @url /admin/v1/mf_cloud/global_defence/:id
     * @method  DELETE
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     */
    public function globalDefenceDelete()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $result = $OptionModel->optionDelete((int)$param['id'], OptionModel::GLOBAL_DEFENCE);
        return json($result);
    }

    /**
     * 时间 2025-10-24
     * @title 保存磁盘大小购买限制
     * @desc  保存磁盘大小购买限制
     * @url /admin/v1/mf_cloud/config/disk_range_limit
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启) require
     * @param   int disk_range_limit - 磁盘大小购买限制(GB) requireIf,disk_range_limit_switch=1
     */
    public function saveDiskRangeLimitConfig(): Json
    {
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('disk_range_limit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->saveDiskRangeLimitConfig($param);
        return json($result);
    }



}
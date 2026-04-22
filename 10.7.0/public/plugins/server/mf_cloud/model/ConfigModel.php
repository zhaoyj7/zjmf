<?php 
namespace server\mf_cloud\model;

use app\common\model\ProductDurationRatioModel;
use think\Model;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\logic\DownstreamCloudLogic;

/**
 * @title 设置模型
 * @use server\mf_cloud\model\ConfigModel
 */
class ConfigModel extends Model{

	protected $name = 'module_mf_cloud_config';

    // 设置字段信息
    protected $schema = [
        'id'                            => 'int',
        'product_id'                    => 'int',
        'node_priority'                 => 'int',
        'ip_mac_bind'                   => 'int',
        'support_ssh_key'               => 'int',
        'rand_ssh_port'                 => 'int',
        'support_normal_network'        => 'int',
        'support_vpc_network'           => 'int',
        'support_public_ip'             => 'int',
        'backup_enable'                 => 'int',
        'snap_enable'                   => 'int',
        'disk_limit_enable'             => 'int',
        'reinstall_sms_verify'          => 'int',
        'reset_password_sms_verify'     => 'int',
        'niccard'                       => 'int',
        'cpu_model'                     => 'int',
        'ipv6_num'                      => 'string',
        'nat_acl_limit'                 => 'string',
        'nat_web_limit'                 => 'string',
        'memory_unit'                   => 'string',
        'type'                          => 'string',
        'disk_limit_switch'             => 'int',
        'disk_limit_num'                => 'int',
        'free_disk_switch'              => 'int',
        'free_disk_size'                => 'int',
        'only_sale_recommend_config'    => 'int',
        'no_upgrade_tip_show'           => 'int',
        'default_nat_acl'               => 'int',
        'default_nat_web'               => 'int',
        'rand_ssh_port_start'           => 'string',
        'rand_ssh_port_end'             => 'string',
        'rand_ssh_port_windows'         => 'string',
        'rand_ssh_port_linux'           => 'string',
        'default_one_ipv4'              => 'int',
        'manual_manage'                 => 'int',
        'upstream_id'                   => 'int',
        'is_agent'                      => 'int',
        'global_defence_strategy'       => 'int',
        'sync_firewall_rule'            => 'int',
        'order_default_defence'         => 'string',
        'free_disk_type'                => 'string',
        'custom_rand_password_rule'     => 'int',
        'default_password_length'       => 'int',
        'level_discount_cpu_order'      => 'int',
        'level_discount_cpu_upgrade'    => 'int',
        'level_discount_cpu_renew'      => 'int',
        'level_discount_memory_order'   => 'int',
        'level_discount_memory_upgrade' => 'int',
        'level_discount_memory_renew'   => 'int',
        'level_discount_bw_order'       => 'int',
        'level_discount_bw_upgrade'     => 'int',
        'level_discount_bw_renew'       => 'int',
        'level_discount_ipv4_order'     => 'int',
        'level_discount_ipv4_upgrade'   => 'int',
        'level_discount_ipv4_renew'     => 'int',
        'level_discount_ipv6_order'     => 'int',
        'level_discount_ipv6_upgrade'   => 'int',
        'level_discount_ipv6_renew'     => 'int',
        'level_discount_system_disk_order'   => 'int',
        'level_discount_system_disk_upgrade' => 'int',
        'level_discount_system_disk_renew'   => 'int',
        'level_discount_data_disk_order'     => 'int',
        'level_discount_data_disk_upgrade'   => 'int',
        'level_discount_data_disk_renew'     => 'int',
        // 'level_discount_gpu_order'           => 'int',
        // 'level_discount_gpu_upgrade'         => 'int',
        'disk_range_limit_switch'           => 'int',
        'disk_range_limit'                  => 'int',
        'simulate_physical_machine_enable'  => 'int',
    ];

    // 缓存
    protected $firewallDefenceRule = [];

    // 类型常量
    const TYPE_HOST      = 'host';
    const TYPE_LIGHTHOST = 'lightHost';
    const TYPE_HYPERV    = 'hyperv';

    /**
     * 时间 2022-06-20
     * @title 获取设置
     * @desc  获取设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @return  int data.support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @return  int data.rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口)
     * @return  int data.support_normal_network - 经典网络(0=不支持,1=支持)
     * @return  int data.support_vpc_network - VPC网络(0=不支持,1=支持)
     * @return  int data.support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int data.disk_limit_enable - 性能限制(0=不启用,1=启用)
     * @return  int data.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int data.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int data.niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @return  string data.ipv6_num - IPv6数量
     * @return  string data.nat_acl_limit - NAT转发限制
     * @return  string data.nat_web_limit - NAT建站限制
     * @return  int data.cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string data.memory_unit - 内存单位(GB,MB)
     * @return  string data.type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int data.node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低,4=填满一个)
     * @return  int data.disk_limit_switch - 数据盘数量限制开关(0=关闭,1=开启)
     * @return  int data.disk_limit_num - 数据盘限制数量
     * @return  int data.free_disk_switch - 免费数据盘开关(0=关闭,1=开启)
     * @return  int data.free_disk_size - 免费数据盘大小(G)
     * @return  string data.free_disk_type - 免费数据盘类型
     * @return  int data.only_sale_recommend_config - 仅售卖套餐(0=关闭,1=开启)
     * @return  int data.no_upgrade_tip_show - 不可升降级时订购页提示(0=关闭,1=开启)
     * @return  int data.default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @return  int data.default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @return  bool data.is_agent - 是否是代理商(是的时候才能添加资源包)
     * @return  string data.rand_ssh_port_start - 随机端口开始端口
     * @return  string data.rand_ssh_port_end - 随机端口结束端口
     * @return  string data.rand_ssh_port_windows - 指定端口Windows
     * @return  string data.rand_ssh_port_linux - 指定端口Linux
     * @return  int data.default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启)
     * @return  int data.manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  int data.backup_data[].id - 备份配置ID
     * @return  int data.backup_data[].num - 备份数量
     * @return  string data.backup_data[].price - 备份价格
     * @return  string data.backup_data[].on_demand_price - 备份按需价格
     * @return  int data.snap_data[].id - 快照配置ID
     * @return  int data.snap_data[].num - 快照数量
     * @return  string data.snap_data[].price - 快照价格
     * @return  string data.snap_data[].on_demand_price - 快照按需价格
     * @return  int data.resource_package[].id - 资源包ID
     * @return  int data.resource_package[].rid - 魔方云资源包ID
     * @return  string data.resource_package[].name - 资源包名称
     * @return  array data.duration_id - 不允许申请停用周期ID
     * @return  int data.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string data.order_default_defence - 订购默认防御峰值
     * @return  string data.on_demand.name - 按需
     * @return  string data.on_demand.unit - 单位,hour=小时
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int data.level_discount_cpu_order - CPU是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_cpu_upgrade - CPU是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_ipv4_order - IPv4是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_ipv4_upgrade - IPv4是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_ipv6_order - IPv6是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_ipv6_upgrade - IPv6是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_system_disk_order - 系统盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_system_disk_upgrade - 系统盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_data_disk_order - 数据盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_data_disk_upgrade - 数据盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_gpu_order - GPU是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_gpu_upgrade - GPU是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启)
     * @return  int data.disk_range_limit - 磁盘大小购买限制(GB)
     * @return  int data.simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启)
     */
    public function indexConfig($param): array
    {
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $ProductModel->id;
            $this->insert($insert);
        }else{
            unset($config['id'], $config['product_id']);
        }

        // 是否支持代理商
        $config['is_agent'] = false;
        if($ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                $config['is_agent'] = isset($hash['account_type']) && $hash['account_type'] == 'agent';
            }
        }

        $BackupConfigModel = new BackupConfigModel();
        $backupData = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'backup']);
        $config['backup_data'] = $backupData['list'];

        $backupData = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'snap']);
        $config['snap_data'] = $backupData['list'];


        $config['resource_package'] = [];
        if($config['is_agent']){
            $config['resource_package'] = ResourcePackageModel::field('id,rid,name')->where('product_id', $ProductModel->id)->select()->toArray();
        }

        $DurationModel = new DurationModel();
        $duration = $DurationModel->getNotSupportApplyForSuspendDuration($param['product_id']);
        $config['duration_id'] = array_column($duration, 'id');

        // 获取按需计费配置
        $onDemand = $DurationModel->onDemand($param['product_id']);
        if(!empty($onDemand)){
            $config['on_demand'] = [
                'name'  => '按需',
                'unit'  => '小时',
            ];
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 保存其他设置
     * @desc 保存其他设置
     * @author hh
     * @version v1
     * @param  int param.product_id - 商品ID require
     * @param  string param.type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V) require
     * @param  int param.node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低,4=填满一个) require
     * @param  int param.ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @param  int param.support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @param  int param.rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口,3=用户自定义) require
     * @param  int param.support_normal_network - 经典网络(0=不支持,1=支持)
     * @param  int param.support_vpc_network - VPC网络(0=不支持,1=支持)
     * @param  int param.support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @param  int param.backup_enable - 是否启用备份(0=不启用,1=启用) require
     * @param  int param.snap_enable - 是否启用快照(0=不启用,1=启用)
     * @param  int param.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用) require
     * @param  int param.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用) require
     * @param  int param.niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @param  int param.cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @param  string param.ipv6_num - IPv6数量
     * @param  string param.nat_acl_limit - NAT转发限制
     * @param  string param.nat_web_limit - NAT建站限制
     * @param  int param.default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @param  int param.default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @param  array param.backup_data - 允许备份数量数据
     * @param  int param.backup_data[].num - 数量
     * @param  float param.backup_data[].price - 价格
     * @param  float param.backup_data[].on_demand_price - 按需价格
     * @param  array param.snap_data - 允许快照数量数据
     * @param  int param.snap_data[].num - 数量
     * @param  float param.snap_data[].price - 价格
     * @param  float param.snap_data[].on_demand_price - 按需价格
     * @param  array param.resource_package - 资源包数据
     * @param  int param.resource_package[].rid - 魔方云资源包ID
     * @param  string param.resource_package[].name - 资源包名称
     * @param  string param.rand_ssh_port_start - 随机端口开始端口 requireIf,rand_ssh_port=1
     * @param  string param.rand_ssh_port_end - 随机端口结束端口 requireIf,rand_ssh_port=1
     * @param  string param.rand_ssh_port_windows - 指定端口Windows requireIf,rand_ssh_port=2
     * @param  string param.rand_ssh_port_linux - 指定端口Linux requireIf,rand_ssh_port=2
     * @param  int param.default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启) require
     * @param  int param.manual_manage - 手动管理商品(0=关闭,1=开启) require
     * @param  array param.duration_id - 不允许申请停用周期ID
     * @param  string param.custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @param  string param.default_password_length - 默认密码长度
     * @param  int param.level_discount_cpu_order - CPU是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_cpu_upgrade - CPU是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_ipv4_order - IPv4是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_ipv4_upgrade - IPv4是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_ipv6_order - IPv6是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_ipv6_upgrade - IPv6是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_system_disk_order - 系统盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_system_disk_upgrade - 系统盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_data_disk_order - 数据盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int param.level_discount_data_disk_upgrade - 数据盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int param.level_discount_gpu_order - GPU是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int param.level_discount_gpu_upgrade - GPU是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启) require
     * @return int status - 状态(200=成功,400=失败)
     * @return string msg - 信息
     */
    public function saveConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $isAgent = false;
        $productId = $ProductModel->id;
        if($ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                $isAgent = isset($hash['account_type']) && $hash['account_type'] == 'agent';
            }
        }
        if($param['type'] == 'hyperv'){
            // 不能填写的给默认值
            $param['ipv6_num'] = '';
            $param['nat_acl_limit'] = '';
            $param['nat_web_limit'] = '';
            $param['niccard'] = 0;
            $param['cpu_model'] = 0;
            $param['ip_mac_bind'] = 0;
            $param['support_ssh_key'] = 0;
            $param['support_normal_network'] = 1;
            $param['support_vpc_network'] = 0;
            $param['support_public_ip'] = 1;
            $param['snap_enable'] = 0;
            if(isset($param['snap_data'])){
                unset($param['snap_data']);
            }
        }else if($param['type'] == 'lightHost'){
            $param['support_normal_network'] = 1;
            $param['support_vpc_network'] = 0;
            $param['support_public_ip'] = 1;
        }
        
        $appendLog = '';
        if(isset($param['backup_data'])){
            if(count($param['backup_data']) > 5){
                return ['status'=>400, 'msg'=>lang_plugins('over_max_allow_num')];
            }
            if( count(array_unique(array_column($param['backup_data'], 'num'))) != count($param['backup_data'])){
                return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
            }
            $BackupConfigModel = new BackupConfigModel();
            $res = $BackupConfigModel->saveBackupConfig($param['product_id'], $param['backup_data'], 'backup');
            $appendLog .= $res['data']['desc'];
        }
        if(isset($param['snap_data'])){
            if(count($param['snap_data']) > 5){
                return ['status'=>400, 'msg'=>lang_plugins('over_max_allow_num')];
            }
            if( count(array_unique(array_column($param['snap_data'], 'num'))) != count($param['snap_data'])){
                return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
            }
            $BackupConfigModel = new BackupConfigModel();
            $res = $BackupConfigModel->saveBackupConfig($param['product_id'], $param['snap_data'], 'snap');
            $appendLog .= $res['data']['desc'];
        }
        if($isAgent && isset($param['resource_package'])){
            $ResourcePackageModel = new ResourcePackageModel();
            $ResourcePackageModel->saveResourcePackage($productId, $param['resource_package']);
        }

        $clearData = $this->isClear($productId, $param['type']);

        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        // $this->update($param, ['product_id'=>$param['product_id']], ['type','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit','default_nat_acl','default_nat_web','rand_ssh_port_start','rand_ssh_port_end','rand_ssh_port_windows','rand_ssh_port_linux','default_one_ipv4','manual_manage','custom_rand_password_rule','default_password_length','level_discount_cpu_order','level_discount_cpu_upgrade','level_discount_memory_order','level_discount_memory_upgrade','level_discount_bw_order','level_discount_bw_upgrade','level_discount_ipv4_order','level_discount_ipv4_upgrade','level_discount_ipv6_order','level_discount_ipv6_upgrade','level_discount_system_disk_order','level_discount_system_disk_upgrade','level_discount_data_disk_order','level_discount_data_disk_upgrade','level_discount_gpu_order','level_discount_gpu_upgrade']);
        $this->update($param, ['product_id'=>$param['product_id']], ['type','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit','default_nat_acl','default_nat_web','rand_ssh_port_start','rand_ssh_port_end','rand_ssh_port_windows','rand_ssh_port_linux','default_one_ipv4','manual_manage','custom_rand_password_rule','default_password_length','level_discount_cpu_order','level_discount_cpu_upgrade','level_discount_cpu_renew','level_discount_memory_order','level_discount_memory_upgrade','level_discount_memory_renew','level_discount_bw_order','level_discount_bw_upgrade','level_discount_bw_renew','level_discount_ipv4_order','level_discount_ipv4_upgrade','level_discount_ipv4_renew','level_discount_ipv6_order','level_discount_ipv6_upgrade','level_discount_ipv6_renew','level_discount_system_disk_order','level_discount_system_disk_upgrade','level_discount_system_disk_renew','level_discount_data_disk_order','level_discount_data_disk_upgrade','level_discount_data_disk_renew','simulate_physical_machine_enable']);
        if($clearData['clear']){
            if(isset($clearData['line_id']) && !empty($clearData['line_id'])){
                LineModel::whereIn('id', $clearData['line_id'])->delete();
            }
        }

        $DurationModel = new DurationModel();
        $oldDuration = $DurationModel->getNotSupportApplyForSuspendDuration($productId);
        $oldDuration = array_column($oldDuration, 'name');

        $DurationModel->where('product_id', $productId)->where('support_apply_for_suspend', 0)->update(['support_apply_for_suspend'=>1]);
        if(!empty($param['duration_id'])){
            $DurationModel->where('product_id', $productId)->whereIn('id', $param['duration_id'])->update(['support_apply_for_suspend'=>0]);
        }
        $newDuration = $DurationModel->getNotSupportApplyForSuspendDuration($productId);
        $newDuration = array_column($newDuration, 'name');

        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        $nodePriority = [
            '',
            lang_plugins('node_priority_1'),
            lang_plugins('node_priority_2'),
            lang_plugins('node_priority_3'),
            lang_plugins('node_priority_4'),
        ];
        $niccard = [
            lang_plugins('mf_cloud_default'),
            'Realtek 8139',
            'Intel PRO/1000',
            'Virtio',
        ];
        $cpuModel = [
            lang_plugins('mf_cloud_default'),
            'host-passthrough',
            'host-model',
            'custom',
        ];
        $type = [
            'host'      => lang_plugins('mf_cloud_kvm_plus'),
            'lightHost' => lang_plugins('mf_cloud_kvm_light'),
            'hyperv'    => 'Hyper-V',
        ];
        $randSshPort = [
            lang_plugins('mf_cloud_default'),
            lang_plugins('mf_cloud_rand_port'),
            lang_plugins('mf_cloud_custom_port'),
            lang_plugins('mf_cloud_user_custom_port'),
        ];

        $desc = [
            'node_priority'             => lang_plugins('mf_cloud_config_node_priority'),
            'ip_mac_bind'               => lang_plugins('mf_cloud_config_ip_mac_bind'),
            'support_ssh_key'           => lang_plugins('mf_cloud_config_support_ssh_key'),
            'rand_ssh_port'             => lang_plugins('mf_cloud_config_rand_ssh_port'),
            'support_normal_network'    => lang_plugins('mf_cloud_config_support_normal_network'),
            'support_vpc_network'       => lang_plugins('mf_cloud_config_support_vpc_network'),
            'backup_enable'             => lang_plugins('backup_enable'),
            'snap_enable'               => lang_plugins('snap_enable'),
            'reinstall_sms_verify'      => lang_plugins('mf_cloud_reinstall_sms_verify'),
            'reset_password_sms_verify' => lang_plugins('mf_cloud_reset_password_sms_verify'),
            'niccard'                   => lang_plugins('mf_cloud_niccard'),
            'cpu_model'                 => lang_plugins('mf_cloud_cpu_model'),
            'ipv6_num'                  => lang_plugins('mf_cloud_ipv6_num'),
            'nat_acl_limit'             => lang_plugins('mf_cloud_nat_acl_limit'),
            'nat_web_limit'             => lang_plugins('mf_cloud_nat_web_limit'),
            'type'                      => lang_plugins('mf_cloud_type'),
            'default_nat_acl'           => lang_plugins('mf_cloud_default_nat_acl'),
            'default_nat_web'           => lang_plugins('mf_cloud_default_nat_web'),
            'rand_ssh_port_start'       => lang_plugins('mf_cloud_rand_ssh_port_start'),
            'rand_ssh_port_end'         => lang_plugins('mf_cloud_rand_ssh_port_end'),
            'rand_ssh_port_windows'     => lang_plugins('mf_cloud_rand_ssh_port_windows'),
            'rand_ssh_port_linux'       => lang_plugins('mf_cloud_rand_ssh_port_linux'),
            'default_one_ipv4'          => lang_plugins('mf_cloud_default_one_ipv4'),
            'manual_manage'             => lang_plugins('mf_cloud_manual_manage'),
            'duration_id'               => lang_plugins('mf_cloud_not_support_apply_for_suspend_duration'),
            'custom_rand_password_rule' => lang_plugins('mf_cloud_custom_rand_password_rule'),
            'default_password_length'   => lang_plugins('mf_cloud_default_password_length'),
            'simulate_physical_machine_enable' => lang_plugins('mf_cloud_simulate_physical_machine_enable'),
        ];

        $config['node_priority']                = $nodePriority[ $config['node_priority'] ];
        $config['ip_mac_bind']                  = $switch[ $config['ip_mac_bind'] ];
        $config['support_ssh_key']              = $switch[ $config['support_ssh_key'] ];
        $config['rand_ssh_port']                = $randSshPort[ $config['rand_ssh_port'] ];
        $config['support_normal_network']       = $switch[ $config['support_normal_network'] ];
        $config['support_vpc_network']          = $switch[ $config['support_vpc_network'] ];
        $config['backup_enable']                = $switch[ $config['backup_enable'] ];
        $config['snap_enable']                  = $switch[ $config['snap_enable'] ];
        $config['reinstall_sms_verify']         = $switch[ $config['reinstall_sms_verify'] ];
        $config['reset_password_sms_verify']    = $switch[ $config['reset_password_sms_verify'] ];
        $config['niccard']                      = $niccard[ $config['niccard'] ];
        $config['cpu_model']                    = $cpuModel[ $config['cpu_model'] ];
        $config['type']                         = $type[ $config['type'] ];
        $config['default_nat_acl']              = $switch[ $config['default_nat_acl'] ];
        $config['default_nat_web']              = $switch[ $config['default_nat_web'] ];
        $config['default_one_ipv4']             = $switch[ $config['default_one_ipv4'] ];
        $config['manual_manage']                = $switch[ $config['manual_manage'] ];
        $config['duration_id']                  = implode(',', $oldDuration);
        $config['custom_rand_password_rule']    = $switch[ $config['custom_rand_password_rule'] ];
        $config['default_password_length']      = $config['default_password_length'];
        $config['simulate_physical_machine_enable'] = $switch[ $config['simulate_physical_machine_enable'] ];

        if(isset($param['node_priority']) && $param['node_priority'] !== '')   $param['node_priority'] = $nodePriority[ $param['node_priority'] ];
        if(isset($param['ip_mac_bind']) && $param['ip_mac_bind'] !== '') $param['ip_mac_bind'] = $switch[ $param['ip_mac_bind'] ];
        if(isset($param['support_ssh_key']) && $param['support_ssh_key'] !== '') $param['support_ssh_key'] = $switch[ $param['support_ssh_key'] ];
        if(isset($param['rand_ssh_port']) && $param['rand_ssh_port'] !== '') $param['rand_ssh_port'] = $randSshPort[ $param['rand_ssh_port'] ];
        if(isset($param['support_normal_network']) && $param['support_normal_network'] !== '') $param['support_normal_network'] = $switch[ $param['support_normal_network'] ];
        if(isset($param['support_vpc_network']) && $param['support_vpc_network'] !== '') $param['support_vpc_network'] = $switch[ $param['support_vpc_network'] ];
        if(isset($param['backup_enable']) && $param['backup_enable'] !== '') $param['backup_enable'] = $switch[ $param['backup_enable'] ];
        if(isset($param['snap_enable']) && $param['snap_enable'] !== '') $param['snap_enable'] = $switch[ $param['snap_enable'] ];
        if(isset($param['reinstall_sms_verify']) && $param['reinstall_sms_verify'] !== '') $param['reinstall_sms_verify'] = $switch[ $param['reinstall_sms_verify'] ];
        if(isset($param['reset_password_sms_verify']) && $param['reset_password_sms_verify'] !== '') $param['reset_password_sms_verify'] = $switch[ $param['reset_password_sms_verify'] ];
        if(isset($param['niccard']) && $param['niccard'] !== '') $param['niccard'] = $niccard[ $param['niccard'] ];
        if(isset($param['cpu_model']) && $param['cpu_model'] !== '') $param['cpu_model'] = $cpuModel[ $param['cpu_model'] ];
        if(isset($param['type']) && $param['type'] !== '') $param['type'] = $type[ $param['type'] ];
        if(isset($param['default_nat_acl']) && $param['default_nat_acl'] !== '') $param['default_nat_acl'] = $switch[ $param['default_nat_acl'] ];
        if(isset($param['default_nat_web']) && $param['default_nat_web'] !== '') $param['default_nat_web'] = $switch[ $param['default_nat_web'] ];
        if(isset($param['default_one_ipv4']) && $param['default_one_ipv4'] !== '') $param['default_one_ipv4'] = $switch[ $param['default_one_ipv4'] ];
        if(isset($param['manual_manage']) && $param['manual_manage'] !== '') $param['manual_manage'] = $switch[ $param['manual_manage'] ];
        $param['duration_id'] = implode(',', $newDuration);
        if(isset($param['custom_rand_password_rule']) && $param['custom_rand_password_rule'] !== '') $param['custom_rand_password_rule'] = $switch[ $param['custom_rand_password_rule'] ];
        if(isset($param['default_password_length']) && $param['default_password_length'] !== '') $param['default_password_length'] = $param['default_password_length'];
        if(isset($param['simulate_physical_machine_enable']) && $param['simulate_physical_machine_enable'] !== '') $param['simulate_physical_machine_enable'] = $switch[ $param['simulate_physical_machine_enable'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description) || !empty($appendLog) ){
            $description = lang_plugins('log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description.$appendLog,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-02-02
     * @title 切换配置开关
     * @desc 切换配置开关
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.field - 要修改的字段 require
     * @param   int param.status - 开关状态(0=关闭,1=开启) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function toggleSwitch($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update([ $param['field'] => $param['status'] ], ['product_id'=>$ProductModel->id]);

        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-02-01
     * @title 获取其他设置默认值
     * @desc 获取其他设置默认值
     * @author hh
     * @version v1
     * @return  string type - 类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)
     * @return  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低,4=填满一个)
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
     * @return  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string ipv6_num - IPv6数量
     * @return  string nat_acl_limit - NAT转发
     * @return  string nat_web_limit - NAT建站
     * @return  string memory_unit - 内存单位(GB,MB)
     * @return  int disk_limit_switch - 数据盘数量限制开关(0=关闭,1=开启)
     * @return  int disk_limit_num - 数据盘限制数量
     * @return  int free_disk_switch - 免费数据盘开关(0=关闭,1=开启)
     * @return  int free_disk_size - 免费数据盘大小(G)
     * @return  int only_sale_recommend_config - 仅售卖套餐(0=关闭,1=开启)
     * @return  int no_upgrade_tip_show - 不可升降级时订购页提示(0=关闭,1=开启)
     * @return  int default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @return  int default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @return  string rand_ssh_port_start - 随机端口开始端口
     * @return  string rand_ssh_port_end - 随机端口结束端口
     * @return  string rand_ssh_port_windows - 指定端口Windows
     * @return  string rand_ssh_port_linux - 指定端口Linux
     * @return  int default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启)
     * @return  int manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string order_default_defence - 订购默认防御峰值
     * @return  string free_disk_type - 免费数据盘类型
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启)
     * @return  int disk_range_limit - 磁盘大小购买限制(GB)
     * @return  int simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启)
     */
    public function getDefaultConfig(): array
    {
        $defaultConfig = [
            'type'                           => 'host',
            'node_priority'                  => 1,
            'ip_mac_bind'                    => 0,
            'support_ssh_key'                => 0,
            'rand_ssh_port'                  => 0,
            'support_normal_network'         => 1,
            'support_vpc_network'            => 0,
            'support_public_ip'              => 0,
            'backup_enable'                  => 0,
            'snap_enable'                    => 0,
            'disk_limit_enable'              => 0,
            'reinstall_sms_verify'           => 0,
            'reset_password_sms_verify'      => 0,
            'niccard'                        => 0,
            'cpu_model'                      => 0,
            'ipv6_num'                       => '',
            'nat_acl_limit'                  => '',
            'nat_web_limit'                  => '',
            'memory_unit'                    => 'GB',
            'disk_limit_switch'              => 0,
            'disk_limit_num'                 => 16,
            'free_disk_switch'               => 0,
            'free_disk_size'                 => 1,
            'only_sale_recommend_config'     => 0,
            'no_upgrade_tip_show'            => 1,
            'default_nat_acl'                => 0,
            'default_nat_web'                => 0,
            'rand_ssh_port_start'            => '',
            'rand_ssh_port_end'              => '',
            'rand_ssh_port_windows'          => '',
            'rand_ssh_port_linux'            => '',
            'default_one_ipv4'               => 1,
            'manual_manage'                  => 0,
            'sync_firewall_rule'             => 0,
            'order_default_defence'          => '',
            'free_disk_type'                 => '',
            'custom_rand_password_rule'      => 1,
            'default_password_length'        => 12,
            'level_discount_cpu_order'       => 1,
            'level_discount_cpu_upgrade'     => 1,
            'level_discount_cpu_renew'       => 1,
            'level_discount_memory_order'    => 1,
            'level_discount_memory_upgrade'  => 1,
            'level_discount_memory_renew'    => 1,
            'level_discount_bw_order'        => 1,
            'level_discount_bw_upgrade'      => 1,
            'level_discount_bw_renew'      => 1,
            'level_discount_ipv4_order'      => 1,
            'level_discount_ipv4_upgrade'    => 1,
            'level_discount_ipv4_renew'    => 1,
            'level_discount_ipv6_order'      => 1,
            'level_discount_ipv6_upgrade'    => 1,
            'level_discount_ipv6_renew'    => 1,
            'level_discount_system_disk_order'   => 1,
            'level_discount_system_disk_upgrade' => 1,
            'level_discount_system_disk_renew' => 1,
            'level_discount_data_disk_order'     => 1,
            'level_discount_data_disk_upgrade'   => 1,
            'level_discount_data_disk_renew'   => 1,
            // 'level_discount_gpu_order'           => 1,
            // 'level_discount_gpu_upgrade'         => 1,
            'disk_range_limit_switch'       => 0,
            'disk_range_limit'              => 1,
            'simulate_physical_machine_enable' => 1,
        ];
        return $defaultConfig;
    }

    /**
     * 时间 2023-08-22
     * @title 是否清空配置
     * @desc  是否清空配置,套餐和线路
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @param   string newType - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V) require
     * @return  bool clear - 是否清空(false=否,true=是)
     * @return  array recommend_config_id - 套餐ID
     * @return  array line_id - 线路ID
     * @return  string desc - 描述
     */
    public function isClear($productId, $newType)
    {
        $result = [
            'clear' => false
        ];

        $config = $this->where('product_id', $productId)->find();
        if(empty($config)){
            return $result;
        }
        if($config['type'] == 'host'){
            if($newType == 'lightHost'){
                
            }else if($newType == 'hyperv'){
                $flowLine = LineModel::alias('l')
                            ->field('l.id,l.name')
                            ->leftJoin('module_mf_cloud_data_center dc', 'l.data_center_id=dc.id')
                            ->where('dc.product_id', $productId)
                            ->where('l.bill_type', 'flow')
                            ->select()
                            ->toArray();

                if(!empty($flowLine)){
                    $desc = lang_plugins('mf_cloud_switch_type_will_delete');
                    $desc .= lang_plugins('mf_cloud_line') . ':' . implode(',', array_column($flowLine, 'name'));
                    
                    $result = [
                        'clear' => true,
                        'recommend_config_id' => [],
                        'line_id' => array_column($flowLine, 'id'),
                        'desc' => rtrim($desc, ','),
                    ];
                }
            }
        }else if($config['type'] == 'lightHost'){
            if($newType == 'host'){
                
            }else if($newType == 'hyperv'){
                $flowLine = LineModel::alias('l')
                            ->field('l.id,l.name')
                            ->leftJoin('module_mf_cloud_data_center dc', 'l.data_center_id=dc.id')
                            ->where('dc.product_id', $productId)
                            ->where('l.bill_type', 'flow')
                            ->select()
                            ->toArray();
                if(!empty($flowLine)){
                    $desc = lang_plugins('mf_cloud_switch_type_will_delete');
                    $desc .= lang_plugins('mf_cloud_line') . ':' . implode(',', array_column($flowLine, 'name'));

                    $result = [
                        'clear' => true,
                        'recommend_config_id' => [],
                        'line_id' => array_column($flowLine, 'id'),
                        'desc' => rtrim($desc, ','),
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * 时间 2023-09-06
     * @title 保存数据盘数量限制
     * @desc  保存数据盘数量限制
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.disk_limit_switch - 数据盘数量限制开关(0=关闭,1=开启)
     * @param   int param.disk_limit_num - 数据盘限制数量
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveDiskNumLimitConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update($param, ['product_id'=>$param['product_id']], ['disk_limit_switch','disk_limit_num']);
        
        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        
        $desc = [
            'disk_limit_switch' => lang_plugins('mf_cloud_disk_limit_switch'),
            'disk_limit_num'    => lang_plugins('mf_cloud_disk_limit_num'),
        ];

        $config['disk_limit_switch'] = $switch[ $config['disk_limit_switch'] ];
        $param['disk_limit_switch']  = $switch[ $param['disk_limit_switch'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-09-11
     * @title 保存免费数据盘配置
     * @desc  保存免费数据盘配置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.free_disk_switch - 免费数据盘开关(0=关闭,1=开启) require
     * @param   int param.free_disk_size - 免费数据盘大小(G)
     * @param   string param.free_disk_type - 免费数据盘类型
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveFreeDiskConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        if(isset($param['free_disk_size']) && $param['free_disk_switch'] <= 0){
            unset($param['free_disk_size']);
        }

        $this->update($param, ['product_id'=>$param['product_id']], ['free_disk_switch','free_disk_size','free_disk_type']);
        
        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        
        $desc = [
            'free_disk_switch' => lang_plugins('mf_cloud_free_disk_switch'),
            'free_disk_size'    => lang_plugins('mf_cloud_free_disk_size'),
            'free_disk_type'    => lang_plugins('mf_cloud_free_disk_type'),
        ];

        $config['free_disk_switch'] = $switch[ $config['free_disk_switch'] ];
        $param['free_disk_switch']  = $switch[ $param['free_disk_switch'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2024-02-19
     * @title 获取数据盘数量限制
     * @desc 获取数据盘数量限制
     * @author hh
     * @version v1
     * @param   int $productId - 商品ID require
     * @return  int
     */
    public function getDataDiskLimitNum($productId)
    {
        $config = $this
            ->field('disk_limit_switch,disk_limit_num')
            ->where('product_id', $productId)
            ->find();
        if(!empty($config)){
            return $config['disk_limit_switch'] == 1 ? $config['disk_limit_num'] : 16;
        }else{
            return 16;
        }
    }

    /**
     * @时间 2025-01-14
     * @title 保存全局防御设置
     * @desc  保存全局防御设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @param   string param.order_default_defence - 订购默认防御
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveGlobalDefenceConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;

        $OptionModel = new OptionModel();
        if($param['sync_firewall_rule'] == 1){
            // 验证
            if(!empty($param['order_default_defence'])){
                $option = $OptionModel
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::GLOBAL_DEFENCE)
                        ->where('value', $param['order_default_defence'])
                        ->find();
                if(empty($option)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_defence_rule_not_found') ];
                }
            }else{
                $param['order_default_defence'] = '';
            }
        }else{
            $param['order_default_defence'] = '';
        }
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }

        $this->startTrans();
        try{
            $this->update($param, ['product_id'=>$param['product_id']], ['sync_firewall_rule','order_default_defence']);

            if($param['sync_firewall_rule'] == 0){
                $optionId = $OptionModel
                        ->where('product_id', $param['product_id'])
                        ->where('rel_type', OptionModel::GLOBAL_DEFENCE)
                        ->column('id');
                if(!empty($optionId)){
                    $OptionModel->whereIn('id', $optionId)->delete();
                    PriceModel::where('product_id', $param['product_id'])->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
                }
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }
        
        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        
        $desc = [
            'sync_firewall_rule' => lang_plugins('mf_cloud_config_sync_firewall_rule'),
        ];

        $config['sync_firewall_rule'] = $switch[ $config['sync_firewall_rule'] ];
        $param['sync_firewall_rule']  = $switch[ $param['sync_firewall_rule'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2025-01-14
     * @title 获取防火墙防御规则
     * @desc  获取防火墙防御规则
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
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
    public function firewallDefenceRule($param)
    {
        $rule = [];
        $hookRes = hook('firewall_set_meal_list', ['product_id' => intval($param['product_id'] ?? 0)]);
        foreach ($hookRes as $key => $value) {
            if(isset($value['type']) && !empty($value['list']) ){
                $rule[] = $value;
            }
        }
        $result = [
            'rule' => $rule,
        ];
        return $result;
    }

    /**
     * @时间 2025-01-15
     * @title 获取防火墙规则
     * @desc  获取防火墙规则
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.firewall_type - 防火墙类型 require
     * @param   int param.defence_rule_id - 防御规则ID require
     */
    public function getFirewallDefenceRule($param)
    {
        $data = [];
        if(!isset($this->firewallDefenceRule[ $param['product_id'] ])){
            $result = $this->firewallDefenceRule($param);
            
            $this->firewallDefenceRule[ $param['product_id'] ] = $result;
        }else{
            $result = $this->firewallDefenceRule[ $param['product_id'] ];
        }
        foreach($result['rule'] as $v){
            if($param['firewall_type'] == $v['type']){
                foreach($v['list'] as $vv){
                    if($param['defence_rule_id'] == $vv['id']){
                        $data = $vv;
                        break;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2025-10-24
     * @title 保存磁盘大小购买限制
     * @desc  保存磁盘大小购买限制
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.product_id - 商品ID require
     * @param   int param.disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启) require
     * @param   int param.disk_range_limit - 磁盘大小购买限制(GB) requireIf,disk_range_limit_switch=1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveDiskRangeLimitConfig($param): array
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }

        // 关闭时直接默认为1
        if($param['disk_range_limit_switch'] == 0 && (empty($param['disk_range_limit']) || $param['disk_range_limit'] < 1 || $param['disk_range_limit'] > 2000) ){
            $param['disk_range_limit'] = 1;
        }

        $this->update($param, ['product_id'=>$param['product_id']], ['disk_range_limit_switch','disk_range_limit']);
        
        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        
        $desc = [
            'disk_range_limit_switch' => lang_plugins('mf_cloud_disk_range_limit_switch'),
            'disk_range_limit'        => lang_plugins('mf_cloud_disk_range_limit'),
        ];

        $config['disk_range_limit_switch'] = $switch[ $config['disk_range_limit_switch'] ];
        $param['disk_range_limit_switch']  = $switch[ $param['disk_range_limit_switch'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }


}
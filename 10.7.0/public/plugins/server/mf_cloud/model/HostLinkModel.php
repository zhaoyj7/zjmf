<?php 
namespace server\mf_cloud\model;

use app\common\model\UpgradeModel;
use app\common\model\UpstreamProductModel;
use server\mf_cloud\logic\CloudLogic;
use think\Model;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use app\common\model\ServerModel;
use app\common\model\ConfigurationModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use app\common\model\HostIpModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\HostAdditionModel;
use app\common\model\ProductOnDemandModel;
use server\mf_cloud\logic\DownstreamCloudLogic;
use app\common\logic\DownstreamProductLogic;

/**
 * @title 产品关联模型
 * @use server\mf_cloud\model\HostLinkModel
 */
class HostLinkModel extends Model
{
	protected $name = 'module_mf_cloud_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'host_id'               => 'int',
        'rel_id'                => 'int',
        'data_center_id'        => 'int',
        'image_id'              => 'int',
        'backup_num'            => 'int',
        'snap_num'              => 'int',
        'power_status'          => 'string',
        'ip'                    => 'string',
        'ssh_key_id'            => 'int',
        'password'              => 'string',
        'vpc_network_id'        => 'int',
        'config_data'           => 'string',
        'create_time'           => 'int',
        'update_time'           => 'int',
        'type'                  => 'string',
        'recommend_config_id'   => 'string',
        'default_ipv4'          => 'int',
        'migrate_task_id'       => 'int',
        'parent_host_id'        => 'int',
        'vpc_private_ip'        => 'string',
    ];

    /**
     * 时间 2022-06-30
     * @title 详情
     * @desc 详情
     * @author hh
     * @version v1
     * @param   int $hostId - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int data.order_id - 订单ID
     * @return  string data.ip - IP地址
     * @return  int data.ip_num - 附加IPv4数量
     * @return  int data.ipv6_num - 附加IPv6数量
     * @return  int data.backup_num - 允许备份数量
     * @return  int data.snap_num - 允许快照数量
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  string data.cpu - CPU
     * @return  int data.memory - 内存
     * @return  int data.system_disk.size - 系统盘大小(G)
     * @return  string data.system_disk.type - 系统盘类型
     * @return  int data.line.id - 线路ID
     * @return  string data.line.name - 线路名称
     * @return  string data.line.bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int data.line.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  int data.bw - 带宽
     * @return  int data.peak_defence - 防御峰值(G)
     * @return  string data.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  string data.gpu - 显卡
     * @return  string data.username - 用户名
     * @return  string data.password - 密码
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.area - 区域
     * @return  string data.data_center.country - 国家
     * @return  string data.data_center.iso - 图标
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  int data.image.image_group_id - 镜像分组ID
     * @return  string data.image.image_group_name - 镜像分组
     * @return  string data.image.icon - 图标
     * @return  int data.ssh_key.id - SSH密钥ID
     * @return  string data.ssh_key.name - SSH密钥名称
     * @return  int data.nat_acl_limit - NAT转发数量
     * @return  int data.nat_web_limit - NAT建站数量
     * @return  int data.config.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int data.config.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int data.config.manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  int data.config.simulate_physical_machine_enable - 模拟物理机运行(0=关闭,1=开启)
     * @return  string data.config.manual_resource_control_mode - 手动资源控制方式not_support不支持,cloud_client客户端
     * @return  int data.security_group.id - 关联的安全组ID(0=没关联)
     * @return  string data.security_group.name - 关联的安全组名称
     * @return  int data.recommend_config.id - 套餐ID(有表示是套餐)
     * @return  int data.recommend_config.product_id - 商品ID
     * @return  string data.recommend_config.name - 套餐名称
     * @return  string data.recommend_config.description - 套餐描述
     * @return  int data.recommend_config.order - 排序
     * @return  int data.recommend_config.data_center_id - 数据中心ID
     * @return  int data.recommend_config.cpu - CPU
     * @return  int data.recommend_config.memory - 内存(GB)
     * @return  int data.recommend_config.system_disk_size - 系统盘大小(G)
     * @return  int data.recommend_config.data_disk_size - 数据盘大小(G)
     * @return  int data.recommend_config.bw - 带宽
     * @return  int data.recommend_config.peak_defence - 防御峰值(G)
     * @return  string data.recommend_config.system_disk_type - 系统盘类型
     * @return  string data.recommend_config.data_disk_type - 数据盘类型
     * @return  int data.recommend_config.flow - 流量
     * @return  int data.recommend_config.line_id - 线路ID
     * @return  int data.recommend_config.create_time - 创建时间
     * @return  int data.recommend_config.ip_num - IP数量
     * @return  int data.recommend_config.upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int data.recommend_config.hidden - 是否隐藏(0=否,1=是)
     * @return  int data.recommend_config.gpu_num - 显卡数量
     * @return  int data.recommend_config.due_not_free_gpu - 不自动释放GPU(0=否,1=是)
     * @return  int data.recommend_config.ipv4_num_upgrade - 是否支持IPv4数量升降级(0=否,1=是)
     * @return  int data.recommend_config.ipv6_num_upgrade - 是否支持IPv6数量升降级(0=否,1=是)
     * @return  int data.recommend_config.flow_upgrade - 是否支持流量升降级(0=否,1=是),流量线路显示
     * @return  int data.recommend_config.bw_upgrade - 是否支持带宽升降级(0=否,1=是),带宽线路显示
     * @return  int data.recommend_config.defence_upgrade - 是否支持防御峰值升降级(0=否,1=是)
     * @return  int data.data_disk.count - 数据盘数量
     * @return  int data.data_disk.total_size - 数据盘总大小
     * @return  int data.support_apply_for_suspend - 是否支持申请停用(0=否,1=是)
     * @return  string data.vpc_private_ip - VPC内网IP
     */
    public function detail($hostId)
    {
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];

        $data = [];
        
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return $res;
        }
        if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
            return $res;
        }
        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp([
            'host_id' => $hostId,
        ]);

        $HostAdditionModel = new HostAdditionModel();
        $hostAddition = $HostAdditionModel->where('host_id', $hostId)->find();

        $hostLink = $this->where('host_id', $hostId)->find();
        if (!empty($hostLink['parent_host_id'])){
            return $res;
        }
        
        $configData = json_decode($hostLink['config_data'], true);

        $data['type'] = $hostLink['type'] ?? 'host';
        $data['order_id'] = $host['order_id'];
        $data['client_id'] = $host['client_id'];
        $data['ip'] = $hostIp['dedicate_ip'];
        if(!empty($hostLink['recommend_config_id'])){
            $recommendConfig = RecommendConfigModel::find($hostLink['recommend_config_id']);

            $data['ip_num'] = isset($configData['ip']['value']) ? (int)($configData['ip']['value'] + 1) : ($recommendConfig['ip_num'] ?? 0);
        }else{
            $data['ip_num'] = isset($configData['ip']['value']) ? (int)$configData['ip']['value'] : 0;
        }
        $data['ipv6_num'] = isset($configData['ipv6_num']) ? (int)$configData['ipv6_num'] : 0;
        $data['backup_num'] = $hostLink['backup_num'];
        $data['snap_num'] = $hostLink['snap_num'];
        $data['power_status'] = $hostAddition['power_status'] ?? '';
        $data['cpu'] = $configData['cpu']['value'];
        $data['memory'] = $configData['memory']['value'];

        if(!empty($hostLink['recommend_config_id'])){
            if(!empty($recommendConfig)){
                $data['recommend_config'] = $recommendConfig;
            }else{
                if(!isset($configData['recommend_config']['due_not_free_gpu'])){
                    $configData['recommend_config']['due_not_free_gpu'] = 0;
                    $configData['recommend_config']['ipv4_num_upgrade'] = 0;
                    $configData['recommend_config']['ipv6_num_upgrade'] = 0;
                    $configData['recommend_config']['flow_upgrade'] = 0;
                    $configData['recommend_config']['bw_upgrade'] = 0;
                    $configData['recommend_config']['defence_upgrade'] = 0;
                }
                $data['recommend_config'] = $configData['recommend_config'];
            }
        }

        // 系统盘
        $systemDisk = DiskModel::field('id,size,type')->where('host_id', $hostId)->where('type2', 'system')->find();
        $data['system_disk'] = [
            'size' => $systemDisk['size'] ?? 0,
            'type' => $systemDisk['type'] ?? '',
        ];
        // 获取数据盘
        $dataDisk = DiskModel::field('id,size,type')->where('host_id', $hostId)->where('type2', 'data')->select()->toArray();
        $data['data_disk'] = [
            'count'         => count($dataDisk),
            'total_size'    => array_sum(array_column($dataDisk, 'size')),
        ];

        $LineModel = new LineModel();
        $line = $LineModel
                ->field('id,name,bill_type,sync_firewall_rule')
                ->find($configData['line']['id'] ?? 0);

        if(!empty($line)){
            $data['line'] = $line->toArray();
        }else{
            $data['line'] = [
                'id'        => $configData['line']['id'] ?? 0,
                'name'      => $configData['line']['name'] ?? '',
                'bill_type' => $configData['line']['bill_type'] ?? 'bw',
                'sync_firewall_rule' => $configData['line']['sync_firewall_rule'] ?? 0,
            ];
        }
        $data['bw'] = $configData['bw']['value'] ?? 0;
        if(isset($configData['flow'])){
            $data['flow'] = (int)$configData['flow']['value'];
            $data['bw'] = $configData['flow']['other_config']['out_bw'] ?? 0;
        }
        $data['peak_defence'] = $configData['defence']['value'] ?? 0;
        $data['network_type'] = $configData['network_type'];
        $data['gpu'] = '';
        
        $image = ImageModel::alias('i')
                ->field('i.id,i.name,i.image_group_id,ig.name image_group_name,ig.icon')
                ->leftJoin('module_mf_cloud_image_group ig', 'i.image_group_id=ig.id')
                ->where('i.id', $hostLink['image_id'])
                ->find();
        // if(!empty($image)){
        //     if($image['image_group_name'] == 'Windows'){
        //         $data['username'] = 'administrator';
        //     }else{
        //         $data['username'] = 'root';
        //     }
        // }else{
        //     $data['username'] = '';
        // }
        $data['username'] = $hostAddition['username'] ?? '';
        $data['password'] = $hostAddition['password'] ?? '';

        $dataCenter = DataCenterModel::find($configData['data_center']['id']);
        if(empty($dataCenter)){
            $dataCenter = $configData['data_center'];
        }
        $data['data_center'] = [
            'id' => $dataCenter['id'],
            'city' => $dataCenter['city'],
            'area' => $dataCenter['area'],
        ];
        $country = CountryModel::find($dataCenter['country_id']);

        $language = get_client_lang();
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $data['data_center']['country'] = $country[ $countryName ];
        $data['data_center']['iso'] = $country['iso'];
        
        $data['image'] = $image ?? (object)[];

        if(isset($configData['gpu_num']) && $configData['gpu_num']>0){
            $gpuName = $configData['gpu_name'] ?? ($configData['line']['gpu_name'] ?? '');

            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'name'              => $gpuName,
                ],
            ]);
            $gpuName = $multiLanguage['name'] ?? $gpuName;
            
            $data['gpu'] = $configData['gpu_num'].'*'.$gpuName;
        }

        // 当是VPC时,获取当前网络信息
        if($hostLink['vpc_network_id']>0){
            $data['vpc_network'] = VpcNetworkModel::field('id,name,ips')->find($hostLink['vpc_network_id']) ?? (object)[];
        }

        unset($data['client_id']);

        if($hostLink['ssh_key_id']>0){
            // ssh密钥
            $enableIdcsmartSshKeyAddon = PluginModel::where('name', 'IdcsmartSshKey')->where('module', 'addon')->where('status',1)->find();
            if(!empty($enableIdcsmartSshKeyAddon)){
                $sshKey = IdcsmartSshKeyModel::find($hostLink['ssh_key_id']);
                
                $data['ssh_key'] = [
                    'id' => $hostLink['ssh_key_id'],
                    'name' => $sshKey['name'] ?? '',
                ];
            }
        }else{
            $data['ssh_key'] = [
                'id' => 0,
                'name' => '',
            ];
        }
        $data['nat_acl_limit'] = $configData['nat_acl_limit'] ?? 0;
        $data['nat_web_limit'] = $configData['nat_web_limit'] ?? 0;
        $data['nat_acl_limit'] = (int)$data['nat_acl_limit'];
        $data['nat_web_limit'] = (int)$data['nat_web_limit'];

        $data['config'] = ConfigModel::field('reinstall_sms_verify,reset_password_sms_verify,manual_manage,simulate_physical_machine_enable')->where('product_id', $host['product_id'])->find() ?? (object)[];

        $data['custom_show'] = [];
        if(isset($data['config']['manual_manage'])){
            $data['config']['manual_resource_control_mode'] = '';
            if($this->isEnableManualResource() && $data['config']['manual_manage']==1){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $hostId)->find();
                if(!empty($manual_resource)){
                    $data['custom_show'] = json_decode($manual_resource['custom_show'], true) ?? [];
                    $data['config']['manual_resource_control_mode'] = $manual_resource['control_mode'];
                }else{
                    $data['custom_show'] = [];
                    $data['config']['manual_resource_control_mode'] = 'not_support';
                }
                $hostAddition = HostAdditionModel::where('host_id', $hostId)->find();
                $data['image'] = [
                    'id' => 0,
                    'name' => $hostAddition['image_name'],
                    'image_group_id' => 0,
                    'image_group_name' => $hostAddition['image_icon'],
                    'icon' => $hostAddition['image_icon'],
                ]; 
            }
        }

        $multiLanguage = hook_one('multi_language', [
            'replace' => [
                'name'              => isset($configData['recommend_config']) ? $data['recommend_config']['name'] : '',
                'description'       => isset($configData['recommend_config']) ? $data['recommend_config']['description'] : '',
                'system_disk_type'  => isset($configData['recommend_config']) ? $data['recommend_config']['system_disk_type'] : '',
                'data_disk_type'    => isset($configData['recommend_config']) ? $data['recommend_config']['data_disk_type'] : '',
                'line_name'         => $data['line']['name'],
                // 'city'              => $data['data_center']['city'],
                // 'area'              => $data['data_center']['area'],
            ],
        ]);

        if(isset($configData['recommend_config'])){
            $data['recommend_config']['name'] = $multiLanguage['name'] ?? $data['recommend_config']['name'];
            $data['recommend_config']['description'] = $multiLanguage['description'] ?? $data['recommend_config']['description'];
            $data['recommend_config']['system_disk_type'] = $multiLanguage['system_disk_type'] ?? $data['recommend_config']['system_disk_type'];
            $data['recommend_config']['data_disk_type'] = $multiLanguage['name'] ?? $data['recommend_config']['data_disk_type'];
        }
        $data['line']['name'] = $multiLanguage['line_name'] ?? $data['line']['name'];
        
        // 安全组不放入缓存
        $securityGroupId = 0;
        try{
            if(class_exists('addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel')){
                $addon = PluginModel::where('name', 'IdcsmartCloud')->where('module', 'addon')->where('status',1)->find();
                if($addon){
                    $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();
                    $securityGroupId = IdcsmartSecurityGroupHostLinkModel::where('host_id', $hostId)->value('addon_idcsmart_security_group_id');
                    if(!empty($securityGroupId)){
                        $IdcsmartSecurityGroupModel = IdcsmartSecurityGroupModel::find($securityGroupId);
                    }
                }
            }
        }catch(\Exception $e){
            //$securityGroupId = 0;
        }
        if(!empty($securityGroupId)){
            $data['security_group'] = [
                'id'=>$securityGroupId,
                'name'=>$IdcsmartSecurityGroupModel['name'] ?? '',
            ];
        }else{
            $data['security_group'] = [
                'id'=>0,
                'name'=>'',
            ];
        }
        // 获取当前周期是否隐藏申请停用
        $duration = DurationModel::where('product_id', $host['product_id'])->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
        if(!empty($duration)){
            $data['support_apply_for_suspend'] = $duration['support_apply_for_suspend'];
        }else{
            $data['support_apply_for_suspend'] = 1;
        }
        $data['vpc_private_ip'] = $hostLink['vpc_private_ip'];
        $res['data'] = $data;

        return $res;
    }

    /**
     * 时间 2023-02-27
     * @title 获取部分详情
     * @desc 获取部分详情,下游用来获取部分信息
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.area - 区域
     * @return  string data.data_center.country - 国家
     * @return  string data.data_center.iso - 图标
     * @return  string data.ip - IP地址
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  string data.image.image_group_name - 镜像分类
     * @return  string data.image.icon - 图标
     */
    public function detailPart($hostId)
    {
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];

        $data = [];
        
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return $res;
        }
        if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
            return $res;
        }

        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp([
            'host_id'   => $hostId,
        ]);

        $HostAdditionModel = new HostAdditionModel();
        $hostAddition = $HostAdditionModel->where('host_id', $hostId)->find();

        $hostLink = $this->where('host_id', $hostId)->find();
        $configData = json_decode($hostLink['config_data'], true);

        $dataCenter = DataCenterModel::find($configData['data_center']['id']);
        if(empty($dataCenter)){
            $dataCenter = $configData['data_center'];
        }
        $data['data_center'] = [
            'id' => $dataCenter['id'],
            'city' => $dataCenter['city'],
            'area' => $dataCenter['area'],
        ];
        $country = CountryModel::find($dataCenter['country_id']);

        $language = get_client_lang();
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $data['data_center']['country'] = $country[ $countryName ];
        $data['data_center']['iso'] = $country['iso'];

        $data['ip'] = $hostIp['dedicate_ip'];
        $data['power_status'] = $hostAddition['power_status'] ?? '';
        
        $image = ImageModel::alias('i')
                ->field('i.id,i.name,ig.name image_group_name,ig.icon')
                ->leftJoin('module_mf_cloud_image_group ig', 'i.image_group_id=ig.id')
                ->where('i.id', $hostLink['image_id'])
                ->find();
        $data['image'] = $image ?? (object)[];
        
        $res['data'] = $data;
        return $res;
    }

    /**
     * 时间 2024-05-24
     * @title 后台详情
     * @desc  后台详情,用于提供后台实例操作获取配置
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  int image[].id - 操作系统分类ID
     * @return  string image[].name - 操作系统分类名称
     * @return  string image[].icon - 操作系统分类图标
     * @return  int image[].image[].id - 操作系统ID
     * @return  int image[].image[].image_group_id - 操作系统分类ID
     * @return  string image[].image[].name - 操作系统名称
     * @return  int image[].image[].charge - 是否收费(0=否,1=是)
     * @return  string image[].image[].price - 价格
     * @return  string config.type - 实例类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int config.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int config.rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口)
     * @return  string config.rand_ssh_port_start - 随机端口开始端口
     * @return  string config.rand_ssh_port_end - 随机端口结束端口
     * @return  string config.rand_ssh_port_windows - 指定端口Windows
     * @return  string config.rand_ssh_port_linux - 指定端口Linux
     * @return  int config.manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  int line.bill_type - 线路类型(bw=带宽计费,flow=流量计费)
     */
    public function adminDetail($param)
    {
        $id = $param['id'] ?? 0;
        $isMarket = $param['is_market'] ?? NULL;

        $productId = HostModel::where('id', $id)->value('product_id');

        $data = [
            'image'     => [],
            'config'    => [],
        ];
        // 获取镜像列表
        $ImageModel = new ImageModel();
        $image = $ImageModel->homeImageList([
            'product_id' => $productId,
            'is_market'  => $isMarket,
        ]);
        $hostLink = HostLinkModel::where('host_id', $id)->find();
        $configData = json_decode($hostLink['config_data'] ?? '', true) ?? [];

        $data['image'] = $image['data']['list'] ?? [];
        $data['market_image_count'] = $image['data']['list']['market_image_count'] ?? 0;

        $data['config'] = ConfigModel::field('type,support_ssh_key,rand_ssh_port,rand_ssh_port_start,rand_ssh_port_end,rand_ssh_port_windows,rand_ssh_port_linux,manual_manage')
                ->where('product_id', $productId)
                ->find();

        $data['line'] = [
            'bill_type' => $configData['line']['bill_type'] ?? 'bw',
        ];

        return $data;
    }

    /* 模块定义操作 */

    /**
     * 时间 2023-02-09
     * @title 模块开通
     * @desc 模块开通
     * @author hh
     * @version v1
     */
    public function createAccount($param)
    {
        $productId = $param['product']['id'];
        $hostId = $param['host']['id'];
        $IdcsmartCloud = new IdcsmartCloud($param['server']);

        $serverHash = ToolLogic::formatParam($param['server']['hash']);
        $isAgent = isset($serverHash['account_type']) && $serverHash['account_type'] == 'agent';
        $IdcsmartCloud->setIsAgent($isAgent);
        $parentHost = $param['host'];
        // 获取当前配置
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(!empty($hostLink) && $hostLink['rel_id'] > 0){
            return ['status'=>400, 'msg'=>lang_plugins('host_already_created')];
        }
        $configData = json_decode($hostLink['config_data'], true);

        // 当前产品为子产品时，直接开通成功
        if(!empty($hostLink['parent_host_id'])){
            $parentHostLink = $this->where('host_id',$hostLink['parent_host_id'])->find();
            // 主产品已开通成功
            if (!empty($parentHostLink['rel_id'])){
                // 子产品未开通
                if ($param['host']['status']!='Active'){
                    // 更新子产品状态
                    HostModel::where('id',$hostId)->update([
                        'status' => 'Active'
                    ]);
                    $detail = $IdcsmartCloud->cloudDetail($parentHostLink['rel_id']);
                    if($detail['status'] == 200){
                        $ips = [];
                        foreach ($detail['data']['ip'] as $v) {
                            $ips[] = $v['ipaddress'];
                        }
                        // 这里需要一个ip一个ip的进行处理，找到所有子产品的ip(产品内页单独购买ip的防御时，需要传参ip进行处理！目前不考虑)
                        $subHostIps = $this->where('parent_host_id',$hostLink['parent_host_id'])->column('ip');
                        $diffIps = array_values(array_diff($ips,$subHostIps));
                        $IpDefenceModel = new IpDefenceModel();
                        $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'], 'defence' => $configData['defence']['value'], 'ip' => [$diffIps[0]??'']]);

                        hook('firewall_set_meal_modify', ['type' => $configData['defence']['firewall_type'], 'set_meal_id' => $configData['defence']['defence_rule_id'], 'host_ips' => [($diffIps[0]??'')=>'']]);

                        //将IP存入子产品
                        $this->where('host_id',$hostId)->update([
                            'ip' => $diffIps[0]??''
                        ]);
                        return [
                            'status'=>200,
                            'msg'   =>lang_plugins('host_create_success')
                        ];
                    }
                }
            }
            return [
                'status'=>400,
                'msg'   =>lang_plugins('host_create_fail')
            ];
        }

        // 开通参数
        $post = [];
        if($param['product']['custom_host_name']==1){
            $post['hostname'] = $param['host']['name'];
        }
        
        // 定义用户参数
        $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面
        $username = $prefix.$param['client']['id'];
        
        $userData = [
            'username'  => $username,
            'email'     => $param['client']['email'] ?: '',
            'status'    => 1,
            'real_name' => $param['client']['username'] ?: '',
            'password'  => rand_str(),
        ];
        if($isAgent){
            if(isset($configData['resource_package']['rid'])){
                $userData['rid'] = $configData['resource_package']['rid'];
            }else{
                // 创建的时候没选择直接默认后台创建的一个
                $userData['rid'] = ResourcePackageModel::where('product_id', $productId)->value('rid');
            }
            if(empty($userData['rid'])){
                $result['status']   = 400;
                $result['msg']      = lang_plugins('mf_cloud_resource_package_id_error');
                return $result;
            }
            $post['rid'] = $userData['rid'];
        }

        $IdcsmartCloud->userCreate($userData);
        $userCheck = $IdcsmartCloud->userCheck($username);
        if($userCheck['status'] != 200){
            return $userCheck;
        }
        $post['client'] = $userCheck['data']['id'];

        $dataCenter = DataCenterModel::find($hostLink['data_center_id']);
        if(!empty($dataCenter)){
            $post[ $dataCenter['cloud_config'] ] = $dataCenter['cloud_config_id'];
        }else{
            $post[ $configData['data_center']['cloud_config'] ] = $configData['data_center']['cloud_config_id'];
        }
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
        $config = $config['data'];

        if($config['manual_manage'] ==1){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_manual_manage_cannot_create_account')];
        }

        $line = LineModel::find($configData['line']['id']);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
        }

        // 格式套餐参数
        if(!empty($hostLink['recommend_config_id'])){
            $recommendConfig = RecommendConfigModel::find($hostLink['recommend_config_id']) ?: $configData['recommend_config'];
            $recommendConfig['gpu_name'] = $configData['gpu_name'] ?? ($configData['line']['gpu_name'] ?? '');

            $post['memory'] = $recommendConfig['memory'] * 1024;
        }else{
            $recommendConfig = [
                'product_id'        => $productId,
                'data_center_id'    => $hostLink['data_center_id'],
                'cpu'               => $configData['cpu']['value'],
                'memory'            => $configData['memory']['value'],
                'system_disk_size'  => $configData['system_disk']['value'],
                'system_disk_type'  => $configData['system_disk']['other_config']['disk_type'] ?? '',
                'bw'                => $configData['bw']['value'] ?? 0,
                'peak_defence'      => $configData['defence']['value'] ?? 0,
                'flow'              => $configData['flow']['value'] ?? 0,
                'line_id'           => $configData['line']['id'],
                'ip_num'            => $configData['ip']['value'] ?? 1,
                'gpu_num'           => $configData['gpu_num'] ?? 0,
                'gpu_name'          => $configData['gpu_name'] ?? ($configData['line']['gpu_name'] ?? ''),
                'ipv6_num'          => $configData['ipv6_num'] ?? 0,
                'in_bw'             => isset($configData['bw']['other_config']['in_bw']) && is_numeric($configData['bw']['other_config']['in_bw']) ? $configData['bw']['other_config']['in_bw'] : ($configData['bw']['value'] ?? 0)
            ];

            if(isset($configData['memory_unit']) && $configData['memory_unit'] == 'MB'){
                $post['memory'] = $configData['memory']['value'];
            }else{
                $post['memory'] = $configData['memory']['value'] * 1024;
            }
            // 可能是流量线路
            if(isset($configData['flow']['other_config'])){
                $recommendConfig['bw'] = $configData['flow']['other_config']['out_bw'];
                $recommendConfig['in_bw'] = $configData['flow']['other_config']['in_bw'] ?: $configData['flow']['other_config']['out_bw'];
            }
        }

        $RecommendConfigModel = new RecommendConfigModel();
        $rcParam = $RecommendConfigModel->formatRecommendConfig($recommendConfig);

        // 单独保存下进带宽
        if(isset($configData['bw']) && $rcParam['in_bw'] != $rcParam['out_bw']){
            $configData['bw']['other_config']['in_bw'] = $rcParam['in_bw'];
        }
        if(isset($configData['flow']) && !empty($configData['flow'])){
            $configData['flow']['other_config']['in_bw'] = $rcParam['in_bw'];
            $configData['flow']['other_config']['out_bw'] = $rcParam['out_bw'];
        }
        $this->where('id', $hostLink['id'])->update(['config_data'=>json_encode($configData)]);

        // config
        $post['type'] = isset($config['type']) && !empty($config['type']) ? $config['type'] : 'host';
        $post['node_priority'] = $config['node_priority'];
        $post['cpu_model'] = $config['cpu_model'];
        $post['niccard'] = $config['niccard'] ?: null;

        if($config['type'] == 'hyperv'){
            $post['bind_mac'] = 1;
        }
        // 选择的参数
        $post['bind_mac'] = $configData['ip_mac_bind'] ?? 0;
        // 嵌套虚拟化逻辑问题 TAPD-ID1005913
        $post['bind_mac'] = abs($post['bind_mac'] - 1);
        $post['network_type'] = $configData['network_type'] ?? 'normal';
        if($post['network_type'] == 'normal'){
            $post['ipv6_num'] = $rcParam['ipv6_num'];
            $post['ipv6_group_id'] = $rcParam['ipv6_group_id'];
        }
        $support_nat = ($post['type'] == 'lightHost' || $post['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
        if($support_nat){
            $post['nat_acl_limit'] = $configData['nat_acl_limit'] ?? -1;
            $post['nat_web_limit'] = $configData['nat_web_limit'] ?? -1;
        }

        $post['link_clone'] = $rcParam['link_clone'];
        $post['cpu'] = $rcParam['cpu'];
        $post['advanced_cpu'] = $rcParam['advanced_cpu'];
        $post['advanced_bw'] = $rcParam['advanced_bw'];
        if($rcParam['cpu_limit'] > 0){
            $post['cpu_limit'] = $rcParam['cpu_limit'];
        }
        $post['system_disk_size'] = $rcParam['system_disk']['size'];
        $post['store'] = $rcParam['system_disk']['store_id'];
        $post['in_bw'] = $rcParam['in_bw'];
        $post['out_bw'] = $rcParam['out_bw'];
        $post['gpu_num'] = $rcParam['gpu_num'];
        // 处理流量参数,按需
        if($param['host']['billing_cycle'] == 'on_demand'){
            $post['flow_strategy'] = 'statistics';
            $post['traffic_quota'] = 0;
            $post['traffic_type'] = $configData['flow']['other_config']['traffic_type'] ?? 3;
        }else{
            // 按周期
            $post['flow_strategy'] = 'cycle';
            $post['traffic_quota'] = $rcParam['flow'];
            if($rcParam['bill_cycle'] == 'month'){
                $post['flow_reset_cycle'] = 'month';
                $post['reset_flow_day'] = 1;
            }else if($rcParam['bill_cycle'] == 'last_30days'){
                $post['flow_reset_cycle'] = 'month';
                $post['reset_flow_day'] = date('j');
            }else if($rcParam['bill_cycle'] == '7days'){
                $post['flow_reset_cycle'] = 'day';
                $param['flow_reset_cycle_num'] = 7;
            }
            $post['traffic_type'] = $rcParam['traffic_type'];
        }

        $post['ip_group'] = $rcParam['ip_group'];

        if($config['disk_limit_enable'] == 1){
            // 获取磁盘限制
            $diskLimit = DiskLimitModel::where('product_id', $param['product']['id'])
                        ->where('type', DiskLimitModel::SYSTEM_DISK)
                        ->where('min_value', '<=', $post['system_disk_size'])
                        ->where('max_value', '>=', $post['system_disk_size'])
                        ->find();
            if(!empty($diskLimit)){
                if($config['type'] == 'hyperv'){
                    $post['system_iops_min']  = $diskLimit['read_iops'];
                    $post['system_iops_max']  = $diskLimit['write_iops'];
                }else{
                    $post['system_read_bytes_sec']  = $diskLimit['read_bytes'];
                    $post['system_write_bytes_sec'] = $diskLimit['write_bytes'];
                    $post['system_read_iops_sec']   = $diskLimit['read_iops'];
                    $post['system_write_iops_sec']  = $diskLimit['write_iops'];
                }
            }
        }
        // 获取当前产品数据盘
        $DiskModel = new DiskModel();
        $dataDisk = $DiskModel->where('host_id', $param['host']['id'])->select()->toArray();
        if(empty($dataDisk)){
            // 原来下的单
            $dataDisk = [];

            // 系统盘
            $dataDisk[] = [
                'name'          => '系统盘',
                'size'          => $configData['system_disk']['value'],
                'host_id'       => $hostId,
                'type'          => $configData['system_disk']['other_config']['disk_type'] ?? '',
                'price'         => $configData['system_disk']['price'] ?? '0.00',
                'create_time'   => time(),
                'is_free'       => 1,
                'status'        => 1,
                'type2'         => 'system',
                'free_size'     => 0,
            ];
            // if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0){
            //     $dataDisk[] = [
            //         'name'          => '免费盘',
            //         'size'          => $config['free_disk_size'],
            //         'host_id'       => $hostId,
            //         'type'          => '',
            //         'price'         => '0.00',
            //         'create_time'   => time(),
            //         'is_free'       => 1,
            //         'status'        => 3,
            //         'type2'         => 'data',
            //     ];
            // }
            // 结算后就先添加数据
            if(isset($configData['data_disk'])){
                foreach($configData['data_disk'] as $k=>$v){
                    $dataDisk[] = [
                        'name'          => lang_plugins('mf_cloud_disk') . rand_str(8, 'NUMBER'),
                        'size'          => $v['value'],
                        'host_id'       => $hostId,
                        'type'          => $v['other_config']['disk_type'] ?? '',
                        'price'         => $v['price'] ?? '0.00',
                        'create_time'   => time(),
                        'is_free'       => $v['is_free'] ?? 0,
                        'status'        => 3,
                        'type2'         => 'data',
                        'free_size'     => !empty($v['free_size']) ? $v['free_size'] : 0,
                    ];
                }
            }
            if(!empty($dataDisk)){
                $DiskModel->insertAll($dataDisk);
                // 重新获取下
                $dataDisk = $DiskModel->where('host_id', $param['host']['id'])->select()->toArray();
            }
        }
        
        foreach($dataDisk as $v){
            if($v['type2'] == 'system'){
                continue;
            }
            $dataDiskStoreId = 0;

            $optionDataDisk = OptionModel::where('product_id', $productId)
                    ->where('rel_type', OptionModel::DATA_DISK)
                    ->where('rel_id', 0)
                    ->whereLike('other_config', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$v['type']])), '}').'%')
                    ->where(function($query) use ($v) {
                        $query->whereOr('value', $v['size'])
                              ->whereOr('(min_value<='.$v['size'].' AND max_value>='.$v['size'].')');
                    })
                    ->find();
            if(!empty($optionDataDisk)){
                $dataDiskStoreId = $optionDataDisk['other_config']['store_id'] ?? 0;
            }
            // 获取磁盘限制
            if($config['disk_limit_enable'] == 1){
                $diskLimit = DiskLimitModel::where('product_id', $productId)
                            ->where('type', DiskLimitModel::DATA_DISK)
                            ->where('min_value', '<=', $v['size'])
                            ->where('max_value', '>=', $v['size'])
                            ->find();
                if(!empty($diskLimit)){
                    if($config['type'] == 'hyperv'){
                        $post['other_data_disk'][] = [
                            'size'              => $v['size'],
                            'iops_min'          => $diskLimit['read_iops'],
                            'iops_max'          => $diskLimit['write_iops'],
                            'store'             => $dataDiskStoreId,
                        ];
                    }else{
                        $post['other_data_disk'][] = [
                            'size'              => $v['size'],
                            'read_bytes_sec'    => $diskLimit['read_bytes'],
                            'write_bytes_sec'   => $diskLimit['write_bytes'],
                            'read_iops_sec'     => $diskLimit['read_iops'],
                            'write_iops_sec'    => $diskLimit['write_iops'],
                            'store'             => $dataDiskStoreId,
                        ];
                    }
                }else{
                    $post['other_data_disk'][] = [
                        'size'  => $v['size'],
                        'store' => $dataDiskStoreId,
                    ];
                }
            }else{
                $post['other_data_disk'][] = [
                    'size'  => $v['size'],
                    'store' => $dataDiskStoreId,
                ];
            }
        }
        // 如果是套餐
        if(!empty($hostLink['recommend_config_id'])){
            $ipNum = $configData['recommend_config']['ip_num'];
        }else{
            // 转发建站的实例默认不要IP
            if($support_nat){
                $ipNum = 0;
            }else{
                // 默认有一个IP数量
                if($config['default_one_ipv4'] == 1){
                    $ipNum = 1;
                }else{
                    $ipNum = 0;
                }
            }
            // 开通,重新保存默认IPv4
            $this->where('id', $hostLink['id'])->update([
                'default_ipv4'  => $ipNum,
            ]);

            $ipNum += $configData['ip']['value'] ?? 0;
        }
        $post['ip_num'] = $ipNum;

        // 是否有安全组,判断插件
        $addon = PluginModel::where('name', 'IdcsmartCloud')->where('module', 'addon')->where('status',1)->find();
        if(!empty($addon) && class_exists('addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel')){
            $securityGroupHostLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $param['host']['id'])->find();
            if(!empty($securityGroupHostLink)){
                $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $securityGroupHostLink['addon_idcsmart_security_group_id'])
                                    ->where('server_id', $param['server']['id'])
                                    ->where('type', $post['type'])
                                    ->find();
                if(!empty($securityGroupLink)){
                    $post['security_group'] = $securityGroupLink['security_id'];
                }else{
                    // 获取安全组数据
                    $securityGroup = IdcsmartSecurityGroupModel::find($securityGroupHostLink['addon_idcsmart_security_group_id']);
                    if(empty($securityGroup)){
                        return ['status'=>400, 'msg'=>lang_plugins('security_group_not_found')];
                    }
                    // 自动创建安全组
                    $securityGroupData = [
                        'name'                  => 'security-'.rand_str(12),
                        'description'           => $securityGroup['name'],
                        'uid'                   => $post['client'],
                        'type'                  => $post['type'],
                        'create_default_rule'   => 0,   // 不创建默认规则
                    ];
                    if($isAgent){
                        $securityGroupData['rid'] = $post['rid'];
                    }
                    $securityGroupCreateRes = $IdcsmartCloud->securityGroupCreate($securityGroupData);
                    if($securityGroupCreateRes['status'] != 200){
                        return $securityGroupCreateRes;
                    }
                    if(!isset($securityGroupCreateRes['data']['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_create_security_group')];
                    }
                    $post['security_group'] = $securityGroupCreateRes['data']['id'];
                    // 保存关联
                    $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
                    $IdcsmartSecurityGroupLinkModel->saveSecurityGroupLink([
                        'addon_idcsmart_security_group_id'  => $securityGroupHostLink['addon_idcsmart_security_group_id'],
                        'server_id'                         => $param['server']['id'],
                        'security_id'                       => $securityGroupCreateRes['data']['id'],
                        'type'                              => $post['type'],
                    ]);
                    // 创建规则
                    $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
                    $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $securityGroupHostLink['addon_idcsmart_security_group_id'])->select()->toArray();
                    foreach($securityGroupRule as $v){
                        $ruleId = $v['id'];
                        unset($v['id'], $v['lock']);
                        $v = IdcsmartSecurityGroupRuleModel::transRule($v);

                        $securityGroupRuleCreateRes = $IdcsmartCloud->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], $v);
                        if($securityGroupRuleCreateRes['status'] == 200){
                            $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                                'addon_idcsmart_security_group_rule_id' => $ruleId,
                                'server_id'                             => $param['server']['id'],
                                'security_rule_id'                      => $securityGroupRuleCreateRes['data']['id'] ?? 0,
                                'type'                                  => $post['type'],
                            ]);
                        }
                    }
                    // 轻量版添加一条拒绝所有
                    if($post['type'] == 'lightHost'){
                        $IdcsmartCloud->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], [
                            'description'   => lang_plugins('mf_cloud_deny_all'),
                            'direction'     => 'in',
                            'protocol'      => 'all',
                            'lock'          => 1,
                            'start_ip'      => '0.0.0.0',
                            'end_ip'        => '0.0.0.0',
                            'start_port'    => 1,
                            'end_port'      => 65535,
                            'priority'      => 1000,
                            'action'        => 'drop',
                        ]);
                    }
                }
            }
        }
        if($hostLink['backup_num']>0){
            $post['backup_num'] = $hostLink['backup_num'];
        }else{
            $post['backup_num'] = -1;
        }
        if($hostLink['snap_num']>0){
            $post['snap_num'] = $hostLink['snap_num'];
        }else{
            $post['snap_num'] = -1;
        }
        
        // 以镜像方式创建暂时,以后加入其他方式
        $image = ImageModel::find($hostLink['image_id']);
        if(!empty($image)){
            if($image['charge'] == 1 && !empty($image['price'])){
                $HostImageLinkModel = new HostImageLinkModel();
                $HostImageLinkModel->saveLink($param['host']['id'], $image['id']);
            }
        }else{
            $image = $configData['image'] ?? [];
        }
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
        }
        if($config['rand_ssh_port'] == 1){
            $post['port'] = isset($configData['port']) && !empty($configData['port']) ? $configData['port'] : mt_rand($config['rand_ssh_port_start'] ?: 100, $config['rand_ssh_port_end'] ?: 65535);
        }else if($config['rand_ssh_port'] == 2){
            // 指定端口,获取镜像分组
            $imageGroup = ImageGroupModel::find($image['image_group_id']);
            if(!empty($imageGroup) && $imageGroup['icon'] == 'Windows'){
                $post['port'] = $config['rand_ssh_port_windows'] ?: 3389;
            }else{
                $post['port'] = $config['rand_ssh_port_linux'] ?: 22;
            }
        }else if($config['rand_ssh_port'] == 3){
            // 用户传入
            if(!empty($configData['rand_port']) && empty($configData['port'])){
                $port = mt_rand(20000, 65535);
            }else{
                $port = isset($configData['port']) && !empty($configData['port']) ? $configData['port'] : mt_rand(20000, 65535);
                if($port != 22 && ($port < 100 || $port > 65535)){
                    $port = mt_rand(20000, 65535);
                }
            }
            $post['port'] = $port;
        }

        $post['os'] = $image['rel_image_id'];

        // 是否使用了SSH key
        if(!empty($hostLink['ssh_key_id'])){
            $enableIdcsmartSshKeyAddon = PluginModel::where('name', 'IdcsmartSshKey')->where('module', 'addon')->where('status',1)->find();
            if(empty($enableIdcsmartSshKeyAddon)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_disable_ssh_key_addon')];
            }
            $sshKey = IdcsmartSshKeyModel::find($hostLink['ssh_key_id']);
            if(empty($sshKey)){
                return ['status'=>400, 'msg'=>lang_plugins('ssh_key_not_found')];
            }
            $sshKeyRes = $IdcsmartCloud->sshKeyCreate([
                'type' => 1,
                'uid'  => $post['client'],
                'name' => 'skey_'.rand_str(),
                'public_key'=>$sshKey['public_key'],
            ]);
            if($sshKeyRes['status'] != 200){
                return ['status'=>400, 'msg'=>$sshKeyRes['msg'] ?? lang_plugins('ssh_key_create_failed')];
            }
            $post['ssh_key'] = $sshKeyRes['data']['id'];
            $post['password_type'] = 1;
        }else{
            $hostAddition = HostAdditionModel::where('host_id', $param['host']['id'])->find();

            $post['password_type'] = 0;
            $post['rootpass'] = $hostAddition['password'] ?? aes_password_decode($hostLink['password']);
        }
        $post['num'] = 1;

        // VPC
        if($post['network_type'] == 'vpc' && !$support_nat){
            // 获取当前VPC网络
            $vpcNetwork = VpcNetworkModel::find($hostLink['vpc_network_id']);
            if(!empty($vpcNetwork)){
                // 检查下VPC在魔方云是否还存在
                if(!empty($vpcNetwork['rel_id'])){
                    $remoteVpc = $IdcsmartCloud->vpcNetworkDetail($vpcNetwork['rel_id']);
                    if($remoteVpc['status'] == 200){
                        $post['vpc'] = $vpcNetwork['rel_id'];
                    }else{
                        // 批量开通并发是否有问题? 找不到了
                        $post['vpc_name'] = $vpcNetwork['vpc_name'];
                        $post['vpc_ips'] = $vpcNetwork['ips'];
                    }
                }else{
                    $post['vpc_name'] = $vpcNetwork['vpc_name'];
                    $post['vpc_ips'] = $vpcNetwork['ips'];
                }
            }else{
                // 连自己关联的VPC都找不到,随机创建个
                $post['vpc_name'] = 'VPC-'.rand_str(8);
            }
        }
        $res = $IdcsmartCloud->cloudCreate($post);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'   =>lang_plugins('host_create_success')
            ];

            $update = [];
            $update['rel_id'] = $res['data']['id'];

            // 获取详情同步信息
            $detail = $IdcsmartCloud->cloudDetail($res['data']['id']);
            if($detail['status'] == 200){
                // $update['password'] = aes_password_encode($detail['data']['rootpassword']);
                $update['type'] = $detail['data']['type'];

                // 保存VPCID
                if(!$support_nat && $post['network_type'] == 'vpc' && isset($detail['data']['network'][0]['vpc']) && $detail['data']['network'][0]['vpc']>0){
                    VpcNetworkModel::where('id', $hostLink['vpc_network_id'])->update(['rel_id'=>$detail['data']['network'][0]['vpc'] ]);
                }
                
                // 去掉同步主机名
                // HostModel::where('id', $param['host']['id'])->update(['name'=>$detail['data']['hostname']]);

                $this->syncIp(['host_id'=>$param['host']['id'], 'id'=>$res['data']['id']], $IdcsmartCloud, $detail, false);

                $this->where('id', $hostLink['id'])->update($update);

                if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 1 && !empty($configData['defence']['defence']['firewall_type'])){

                    $subHosts = $this->where('parent_host_id',$param['host']['id'])->select()->toArray();
                    foreach ($subHosts as $subHost){
                        $host = HostModel::where('id',$subHost['host_id'])->find();
                        $param['host'] = $host;
                        $this->createAccount($param);
                    }
                    $param['host'] = $parentHost;
//                    $hostIps = [];
//                    $ips = [];
//                    foreach ($detail['data']['ip'] as $v) {
//                        $ips[] = $v['ipaddress'];
//                        $hostIps[ $v['ipaddress'] ] = '';
//                    }
//                    $IpDefenceModel = new IpDefenceModel();
//                    $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'], 'defence' => $configData['defence']['value'], 'ip' => $ips]);
//
//                    hook('firewall_set_meal_modify', ['type' => $configData['defence']['firewall_type'], 'set_meal_id' => $configData['defence']['defence_rule_id'], 'host_ips' => $hostIps]);
//
//                    //将IP存入子产品
//                    $subHosts = $this->where('parent_host_id',$param['host']['id'])->select();
//                    foreach ($subHosts as $key=>$subHost) {
//                        $subHost->save([
//                            'ip' => $ips[$key]??''
//                        ]);
//                    }

                }
            }

            // 如果有免费盘放在config_data里面但是不保存
            // if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0){
            //     if(!isset($configData['data_disk'])) $configData['data_disk'] = [];

            //     array_unshift($configData['data_disk'], [
            //         'value'         => $config['free_disk_size'],
            //         'price'         => 0,
            //         'is_free'       => 1,
            //         'other_config'  => [
            //             'disk_type' => ''
            //         ],
            //     ]);
            // }
            
            foreach($detail['data']['disk'] as $k=>$v){
                if($v['type'] == 'system'){
                    $DiskModel->where('host_id', $param['host']['id'])->where('type2', 'system')->update([
                        'name'      => $v['name'],
                        'size'      => $v['size'],
                        'rel_id'    => $v['id'],
                    ]);
                }else{
                    $DiskModel->where('host_id', $param['host']['id'])->where('size', $v['size'])->where('rel_id', 0)->limit(1)->update([
                        'name'      => $v['name'],
                        'rel_id'    => $v['id'],
                        'status'    => $v['status'],
                    ]);
                }
            }
            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($param['host']['id'], [
                'power_status'    => 'on',
                'password'        => $detail['data']['rootpassword'],
                'port'            => $detail['data']['port'],
            ]);
        }else{
            $result = [
                'status'=>400,
                'msg'=>$res['msg'] ?: lang_plugins('host_create_failed'),
            ];

            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($param['host']['id'], [
                'power_status'    => 'fault',
                'port'            => $post['port'] ?? 0
            ]);
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块暂停
     * @desc 模块暂停
     * @author hh
     * @version v1
     */
    public function suspendAccount($param)
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
        $config = $config['data'];

        if($config['manual_manage'] ==1){
            return ['status'=>200, 'msg'=>lang_plugins('suspend_success')];
        }

        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        // 子产品，删除防御
        if (!empty($hostLink['parent_host_id'])){
            // 查找线路默认防御
            $configData = json_decode($hostLink['config_data'], true);
            $firewallType = 'aodun_firewall';
            $lineId = $configData['line']['id']??0;
            $LineModel = new LineModel();
            $line = $LineModel->where('id',$lineId)->find();
            $defaultDefence = $line['order_default_defence']??'';
            $orderDefaultDefenceId = str_replace($firewallType.'_','',$defaultDefence);
            $parentHost = HostModel::where('id',$hostLink['parent_host_id'])->find();
            HostModel::where('id',$param['host']['id'])->update([
                'first_payment_amount' => 0,
                'renew_amount' => 0,
                'base_price' => 0,
                'due_time' => $parentHost['due_time']??0
            ]);
            $current = IpDefenceModel::where('host_id', $param['host']['id'])
                ->where('ip', $hostLink['ip'])
                ->value('defence');
            $targetDefence = $configData['line']['order_default_defence'] ?? '';
            if ($current !== $targetDefence) {
                IpDefenceModel::where('host_id',$param['host']['id'])->where('ip',$hostLink['ip'])
                    ->update([
                        'defence' => $configData['line']['order_default_defence']??''
                    ]);
                hook('firewall_set_meal_modify',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'set_meal_id'=>$orderDefaultDefenceId]);
            };
//            IpDefenceModel::where('host_id',$param['host']['id'])->where('ip',$hostLink['ip'])
//                ->update([
//                    'defence' => $configData['line']['order_default_defence']??''
//                ]);
//            hook('firewall_set_meal_modify',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'set_meal_id'=>$orderDefaultDefenceId]);
//            hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'default_defence_id'=>$orderDefaultDefenceId]);
            return ['status'=>200, 'msg'=>lang_plugins('suspend_success')];
        }
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
        }
        $configData = json_decode($hostLink['config_data'], true);
        $freeGpu = ($configData['due_not_free_gpu'] ?? 0) == 0 ? 1 : 0;

        $IdcsmartCloud = new IdcsmartCloud($param['server']);
        $cloudDetail = $IdcsmartCloud->cloudDetail($id);
        if(!empty($cloudDetail['data']['status']) && $cloudDetail['data']['status'] == 'suspend'){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('suspend_success'),
            ];
            return $result;
        }

        $res = $IdcsmartCloud->cloudSuspend($id, [
            'free_gpu'  => $freeGpu,
        ]);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('suspend_success'),
            ];
        }else{
            $result = [
                'status' => 400,
                'msg'    => $res['msg'] ?? lang_plugins('suspend_failed'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块解除暂停
     * @desc 模块解除暂停
     * @author hh
     * @version v1
     */
    public function unsuspendAccount($param)
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
        $config = $config['data'];

        if($config['manual_manage'] ==1){
            return ['status'=>200, 'msg'=>lang_plugins('unsuspend_success')];
        }
        // 续费的解除暂停直接成功,后续让renew方法处理
        if(!empty($param['is_renew'])){
            return ['status'=>200, 'msg'=>lang_plugins('unsuspend_success')];
        }
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        // 子产品解除暂停后，添加防御
        if (!empty($hostLink['parent_host_id'])){
            $defence = json_decode($hostLink['config_data'], true)['defence']??[];
            $firewallType = $defence['firewall_type']??'';
            hook('firewall_set_meal_modify',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'set_meal_id'=>$defence['defence_rule_id']??0]);
            return ['status'=>200, 'msg'=>lang_plugins('unsuspend_success')];
        }

        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
        }

        $IdcsmartCloud = new IdcsmartCloud($param['server']);
        $res = $IdcsmartCloud->cloudUnsuspend($id);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('unsuspend_success'),
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('unsuspend_failed'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块删除
     * @desc 模块删除
     * @author hh
     * @version v1
     */
    public function terminateAccount($param)
    {
        $parentHost = $param['host']??[];
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);
        $config = $config['data']??[];

        if(isset($config['manual_manage']) && $config['manual_manage']==1){
            if($this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
                if(!empty($manual_resource)){
                    $ManualResourceModel->where('host_id', $param['host']['id'])->update(['host_id' => 0, 'update_time' => time()]);
                }
            }
            return [
                'status'=>200,
                'msg'   =>lang_plugins('delete_success')
            ];
        }

        if (empty($param['host'])){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('delete_success')
            ];
        }

        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        // 子产品，删除防御
        if (!empty($hostLink['parent_host_id'])){
            try {
                HostModel::where('id',$param['host']['id'])->update([
                    'status'           => 'Deleted',
                    'termination_time' => time(),
                    'update_time'      => time(),
                ]);
                // 查找线路默认防御
                $configData = json_decode($hostLink['config_data'], true);
                $firewallType = $configData['defence']['firewall_type']??'';
                $lineId = $configData['line']['id']??0;
                $LineModel = new LineModel();
                $line = $LineModel->where('id',$lineId)->find();
                $defaultDefence = $line['order_default_defence']??'';
                $orderDefaultDefenceId = str_replace($firewallType.'_','',$defaultDefence);
                if (!empty($hostLink['ip'])){
                    hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'default_defence_id'=>$orderDefaultDefenceId]);
                }
            }catch (\Exception $e){
            }

            return ['status'=>200, 'msg'=>lang_plugins('delete_success')];
        }
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            $result = [
                'status'    => 200,
                'msg'       => lang_plugins('delete_success'),
            ];
            return $result;
            // return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
        }

        $IdcsmartCloud = new IdcsmartCloud($param['server']);
        $res = $IdcsmartCloud->cloudDelete($id);
        if($res['status'] == 200 || $res['http_code'] == 404){
            // 删除子产品防御
            $subHostIds = $this->where('parent_host_id',$param['host']['id'])->column('host_id');
            foreach ($subHostIds as $subHostId){
                $subHost = HostModel::find($subHostId);
                $param['host'] = $subHost;
                $this->terminateAccount($param);
            }
            // 恢复
            $param['host'] = $parentHost;
            // 把磁盘数据保存到config_data
            // $diskData = DiskModel::field('size,price,type')->where('host_id', $param['host']['id'])->where('is_free', 0)->select();

            // $diskConfig = [];
            // foreach($diskData as $v){
            //     $diskConfig[] = [
            //         'value' => $v['size'],
            //         'price' => $v['price'],
            //         'other_config' => [
            //             'disk_type' => $v['type'],
            //         ],
            //     ];
            // }
            $configData = json_decode($hostLink['config_data'], true);
            // $configData['data_disk'] = $diskConfig;

            $HostIpModel = new HostIpModel();
            $hostIp = $HostIpModel->getHostIp([
                'host_id'   => $param['host']['id'],
            ]);

            $update = [
                'rel_id'            => 0,
                'vpc_network_id'    => 0,
                // 'config_data'       => json_encode($configData),
            ];

            $this->where('host_id', $param['host']['id'])->update($update);
            DiskModel::where('host_id', $param['host']['id'])->delete();

            $notes = [
                '产品标识：'.$param['host']['name'],
                'IP地址：'.$hostIp['dedicate_ip'],
                '操作系统：'.$configData['image']['name'],
                'ID：'.$hostLink['rel_id']
            ];
            HostModel::where('id', $param['host']['id'])->update(['notes'=>$param['host']['notes'] . "\r\n" . implode("\r\n", $notes)]);
            DiskModel::where('host_id', $param['host']['id'])->update(['rel_id'=>0]);

            $result = [
                'status'=>200,
                'msg'=>lang_plugins('delete_success'),
            ];

            hook('after_mf_cloud_host_terminate', ['id'=>$param['host']['id'] ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => $res['msg'] ?? lang_plugins('delete_failed'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2024-02-19
     * @title 续费后调用
     * @desc 续费后调用
     * @author hh
     * @version v1
     */
    public function renew($param)
    {
        $hostId = $param['host']['id'];
        $productId = $param['product']['id'];

        $hostLink = $this->where('host_id', $hostId)->find();
        if(!empty($hostLink)){
            // 子产品续费，防御解除暂停
            if ($hostLink['parent_host_id']){
                $configData = json_decode($hostLink['config_data'], true);
                $duration = DurationModel::where('product_id', $productId)->where('name', $param['host']['billing_cycle_name'])->find();
                if(!empty($duration)){
                    $configData['duration'] = $duration;
                    $this->where('host_id', $hostId)->update(['config_data'=>json_encode($configData)]);
                }
                if ($param['host']['status']=='Suspended'){
                    $configData = json_decode($hostLink['config_data'], true);
                    $defence = $configData['defence']??[];
                    $firewallType = $defence['firewall_type']??'';
                    hook('firewall_set_meal_modify',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'set_meal_id'=>$defence['defence_rule_id']??0]);
                }
                return NULL;
            }
            $configData = json_decode($hostLink['config_data'], true);
            $unsuspend = true;

            // 获取当前周期
            $duration = DurationModel::where('product_id', $productId)->where('name', $param['host']['billing_cycle_name'])->find();
            if(!empty($duration)){
                $configData['duration'] = $duration;

                $this->where('host_id', $hostId)->update(['config_data'=>json_encode($configData)]);

                $ConfigModel = new ConfigModel();
                $config = $ConfigModel->indexConfig(['product_id'=>$productId ]);

                // 手动资源
                if(isset($config['data']['manual_manage']) && $config['data']['manual_manage'] == 1 && $this->isEnableManualResource()){
                    // 移动到续费里面去了
//                    system_notice([
//                        'name'                  => 'host_module_action',
//                        'email_description'     => lang('host_module_action'),
//                        'sms_description'       => lang('host_module_action'),
//                        'task_data' => [
//                            'client_id' => $param['host']['client_id'],
//                            'host_id'	=> $param['host']['id'],
//                            'template_param'=>[
//                                'module_action' => lang_plugins('renew'),
//                            ],
//                        ],
//                    ]);
//                    $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
//                    $ManualResourceLogModel->createLog([
//                        'host_id'                   => $hostId,
//                        'type'                      => 'renew',
//                        'client_id'                 => $param['host']['client_id'],
//                        'data' => $param['renew_log']??[],
//                    ]);

                    return NULL;
                }

                // 非下游时
                $DownstreamCloudLogic = new DownstreamCloudLogic($param['host']);
                if(!$DownstreamCloudLogic->isDownstream()){
                    $configData['due_not_free_gpu'] = $configData['due_not_free_gpu'] ?? 0;

                    // 没有GPU/不自动释放不检查
                    if(empty($configData['gpu_num']) || $configData['due_not_free_gpu'] == 1){
                        $this->unsuspendAccount($param);
                        return NULL;
                    }
                    $IdcsmartCloud = new IdcsmartCloud($param['server']);

                    // 先获取实例详情
                    $cloudDetail = $IdcsmartCloud->cloudDetail((int)$hostLink['rel_id']);
                    $cloudDetail = $cloudDetail['data'] ?? [];
                    if(!empty($cloudDetail)){
                        // 主动续费直接返回,排除非暂停和流量暂停的
                        if($cloudDetail['status'] != 'suspend' || $cloudDetail['suspend_type'] == 'traffic'){
                            return NULL;
                        }

                        $res = $IdcsmartCloud->cloudHardwareThrough((int)$hostLink['rel_id']);
                        if($res['status'] == 200){
                            $realGpuNum = 0;
                            if(!empty($res['data']['data']['pci'])){
                                foreach($res['data']['data']['pci'] as $v){
                                    if($v['type'] == 'display'){
                                        $realGpuNum++;
                                    }
                                }
                            }
                            // 实际没有GPU,检查数据中心是否足够
                            if(empty($realGpuNum)){
                                $dataCenter = DataCenterModel::find($hostLink['data_center_id']);
                                if(!empty($dataCenter)){
                                    $res = $IdcsmartCloud->getFreeGpu([
                                        'type'  => $dataCenter['cloud_config'],
                                        'id'    => $dataCenter['cloud_config_id'],
                                        'num'   => $configData['gpu_num'],
                                    ]);
                                    if(!empty($res['data']['data'])){
                                        $migrate = true;
                                        foreach($res['data']['data'] as $k=>$v){
                                            if($v['node_id'] == $cloudDetail['node_id']){
                                                $migrate = false;
                                                break;
                                            }
                                        }
                                        // 无需迁移
                                        if(!$migrate){
                                            $res = $IdcsmartCloud->cloudMountPciGpu($hostLink['rel_id'], $configData['gpu_num']);
                                            if($res['status'] == 200){
                                                $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_success', [
                                                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                                ]);
                                            }else{
                                                $unsuspend = false;

                                                $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                                                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                                    '{reason}'  => $res['msg'] ?? '',
                                                ]);
                                            }
                                        }else{
                                            // 排序
                                            usort($res['data']['data'], function($a, $b){
                                                return $a['num'] - $b['num'] ? 1 : -1;
                                            });
                                            // 发起迁移
                                            foreach($res['data']['data'] as $v){
                                                if($v['node_id'] == $cloudDetail['node_id']){
                                                    continue;
                                                }
                                                $res = $IdcsmartCloud->cloudMigrate($hostLink['rel_id'], [
                                                    'node'              => $v['node_id'],
                                                    'hot_migrate'       => 0,
                                                    'auto_mount_gpu_num'=> $configData['gpu_num'],
                                                    'auto_unsuspend'    => 1,
                                                ]);
                                                if($res['status'] == 200){
                                                    break;
                                                }
                                                sleep(1);
                                            }
                                            if($res['status'] == 200){
                                                // 记录续费迁移任务ID
                                                $this->where('host_id', $hostId)->update([
                                                    'migrate_task_id'   => $res['data']['taskid'] ?? 0,
                                                ]);

                                                $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_start_migrate_success', [
                                                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                                ]);
                                            }else{
                                                $unsuspend = false;

                                                $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_start_migrate_fail', [
                                                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                                    '{reason}'  => $res['msg'] ?? '',
                                                ]);
                                            }
                                        }
                                    }else{
                                        $unsuspend = false;

                                        $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                                            '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                            '{reason}'  => lang_plugins('mf_cloud_data_center_gpu_not_enough2'),
                                        ]);
                                    }
                                }else{
                                    $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                        '{reason}'  => lang_plugins('data_center_not_found'),
                                    ]);
                                }
                            }else{
                                $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                    '{reason}'  => lang_plugins('mf_cloud_host_already_mount_gpu'),
                                ]);
                            }
                        }else{
                            $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                                '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                                '{reason}'  => $res['msg'] ?? '',
                            ]);
                        }
                    }else{
                        $description = lang_plugins('log_mf_cloud_host_renew_mount_gpu_fail', [
                            '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                            '{reason}'  => lang_plugins('mf_cloud_get_host_detail_fail', [
                                '{id}'  => (int)$hostLink['rel_id'],
                            ]),
                        ]);
                    }

                    if(!empty($description)){
                        active_log($description, 'host', $param['host']['id']);
                    }
                    if($unsuspend){
                        $this->unsuspendAccount($param);
                    }
                }
            }
        }
    }

    /**
     * 时间 2022-06-28
     * @title 升降级后调用
     * @throws \Exception
     * @version v1
     * @author hh
     */
    public function changePackage($param)
    {
        // 判断是什么类型
        if(!isset($param['custom']['type'])){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
        $config = $config['data'];

        $productId = $param['product']['id'];   // 商品ID
        $hostId    = $param['host']['id'];      // 产品ID
        $custom    = $param['custom'] ?? [];    // 升降级参数
        $orderId   = $param['order_id']??0;

        $DownstreamCloudLogic = new DownstreamCloudLogic($param['host']);
        if($DownstreamCloudLogic->isDownstream()){
            // 下游时单独处理每个升降级
            if($custom['type'] == 'buy_image'){
                $HostImageLinkModel = new HostImageLinkModel();
                $HostImageLinkModel->saveLink($hostId, $custom['image_id']);

                $image = ImageModel::find($custom['image_id']);

                $post = [
                    'image_id' => $image['upstream_id'] ?? 0,
                ];

                $result = $DownstreamCloudLogic->buyImage($post,$orderId);
                
                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_buy_image'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);

                active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'upgrade_common_config'){
                $hostLink = $this->where('host_id', $hostId)->find();

                $configData = json_decode($hostLink['config_data'], true);
                $oldConfigData = $configData;
                $newConfigData = $custom['new_config_data'];
                foreach($newConfigData as $k=>$v){
                    $configData[$k] = $v;
                }
                // 保存新的配置
                $update = [
                    'config_data' => json_encode($configData),
                ];
                $this->update($update, ['host_id'=>$hostId]);

                HostModel::where('id', $hostId)->update([
                    'base_info'     => $this->formatBaseInfo($configData),
                ]);

                $post = array_filter([
                    'cpu'           => $configData['cpu']['value'] ?? NULL,
                    'memory'        => $configData['memory']['value'] ?? NULL,
                    'bw'            => $configData['bw']['value'] ?? NULL,
                    'flow'          => $configData['flow']['value'] ?? NULL,
                    'peak_defence'  => $configData['defence']['value'] ?? NULL,
                ]);
                if(!empty($newConfigData['flow'])){
                    $post['bw'] = $configData['flow']['other_config']['out_bw'] ?? 0;
                    $post['flow'] = $configData['flow']['value'];
                }
                $result = $DownstreamCloudLogic->upgradeCommonConfig($post,$orderId);
                
                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_common_config'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'buy_disk'){
                $hostLink = HostLinkModel::where('host_id', $hostId)->find();
                $DiskModel = new DiskModel();

                $post = [
                    'remove_disk_id' => [],
                    'add_disk'       => [],
                ];

                if(!empty($custom['remove_disk_id'])){
                    foreach($custom['remove_disk_id'] as $v){
                        $disk = $DiskModel->find($v);
                        if(!empty($disk)){
                            DiskModel::where('host_id', $hostId)->where('id', $v)->delete();
                            
                            $post['remove_disk_id'][] = $disk['upstream_id'];
                        }
                    }
                }
                if(!empty($custom['add_disk'])){
                    foreach($custom['add_disk'] as $v){
                        $DiskModel->createDataDisk([
                            'size'      => $v['size'],
                            'host_id'   => $hostId,
                            'type'      => $v['type'] ?? '',
                            'price'     => $v['price'] ?? 0,
                        ]);

                        $post['add_disk'][] = [
                            'size'  => $v['size'],
                            'type'  => $v['type'] ?? '',
                        ];
                    }
                }
                
                $result = $DownstreamCloudLogic->buyDisk($post,$orderId);

                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_buy_disk'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $hostId);
            }else if($param['custom']['type'] == 'resize_disk'){
                $custom = $param['custom'];

                $hostLink = $this->where('host_id', $param['host']['id'])->find();
                $id = $hostLink['rel_id'] ?? 0;

                $resizeDataDisk = [];

                foreach($custom['resize_disk'] as $v){
                    DiskModel::where('host_id', $hostId)->where('id', $v['id'])->update(['size'=>$v['size'], 'price'=>$v['price'] ]);

                    $resizeDataDisk[] = [
                        'id'    => (int)DiskModel::where('id', $v['id'])->value('upstream_id'),
                        'size'  => $v['size'],
                    ];
                }

                $post = [
                    'resize_data_disk' => $resizeDataDisk,
                ];

                $result = $DownstreamCloudLogic->upgradeResizeDisk($post,$orderId);

                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_resize_disk'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'modify_backup'){
                $hostLink = $this->where('host_id', $hostId)->find();
                
                $update = [ $custom['backup_type'].'_num'=>$custom['num'] ];

                $type = ['backup'=>lang_plugins('backup'), 'snap'=>lang_plugins('snap')];

                $configData = json_decode($hostLink['config_data'], true);
                $configData[ $custom['backup_type'] ] = $custom['backup_config'];
                $update['config_data'] = json_encode($configData);

                $this->update($update, ['host_id'=>$param['host']['id']]);

                $post = [
                    'type'  => $custom['backup_type'],
                    'num'   => $custom['num'],
                ];

                $result = $DownstreamCloudLogic->upgradeBackup($post,$orderId);
                
                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_modify_backup'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $hostId);
            }else if($custom['type'] == 'upgrade_ip_num'){
                // 升级IP数量
                $hostLink = $this->where('host_id', $hostId)->find();

                // 直接保存configData
                $configData = json_decode($hostLink['config_data'], true);
                $oldIpNum = $configData['ip']['value'] ?? 0;
                $oldIpv6Num = $configData['ipv6_num'] ?? 0;

                if(!empty($custom['ip_data'])){
                    $configData['ip'] = $custom['ip_data'];
                }
                if(!empty($custom['ipv6_data'])){
                    $configData['ipv6_num'] = $custom['ipv6_data']['value'];
                }

                $this->where('id', $hostLink['id'])->update(['config_data'=>json_encode($configData)]);

                $post = array_filter([
                    'ip_num'     => $configData['ip']['value'] ?? NULL,
                    'ipv6_num'   => $configData['ipv6_num'] ?? NULL,
                    'ip'         => $custom['remove_ipv4'] ?? NULL,
                    'ipv6'       => $custom['remove_ipv6'] ?? NULL,
                ], function($x){
                    return $x !== null;
                });
                $result = $DownstreamCloudLogic->upgradeIpNum($post,$orderId);
                
                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_scene_change_ip_num'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'upgrade_recommend_config'){
                // 套餐升降级
                $hostLink = HostLinkModel::where('host_id', $hostId)->find();

                $configData = json_decode($hostLink['config_data'], true);
                $oldConfigData = $configData;
                $newConfigData = $custom['new_config_data'];
                foreach($newConfigData as $k=>$v){
                    $configData[$k] = $v;
                }

                $RecommendConfigModel = new RecommendConfigModel();
                $newRcParam = $RecommendConfigModel->formatRecommendConfig($newConfigData['recommend_config']);

                $configData['cpu'] = [
                    'value' => $newConfigData['recommend_config']['cpu'],
                ];
                $configData['memory'] = [
                    'value' => $newConfigData['recommend_config']['memory'],
                ];
                $configData['system_disk'] = [
                    'value' => $newConfigData['recommend_config']['system_disk_size'],
                    'other_config' => [
                        'disk_type' => $newConfigData['recommend_config']['system_disk_type'],
                    ],
                ];
                if($newRcParam['data_disk']['size'] > 0){
                    $configData['data_disk'][] = [
                        'value'         => $newConfigData['recommend_config']['data_disk_size'],
                        'other_config'  => [
                            'disk_type' => $newConfigData['recommend_config']['data_disk_type']
                        ],
                    ];
                }
                $configData['bw'] = [
                    'value' => $newConfigData['recommend_config']['bw'],
                    'other_config' => [
                        'in_bw' => $newRcParam['in_bw'] != $newRcParam['out_bw'] ? $newRcParam['in_bw'] : '',
                    ],
                ];
                $configData['flow'] = [
                    'value' => $newConfigData['recommend_config']['flow'],
                    'other_config' => [
                        'in_bw' => $newRcParam['in_bw'],
                        'out_bw'=> $newRcParam['out_bw'],
                    ],
                ];
                $configData['defence'] = [
                    'value' => $newConfigData['recommend_config']['peak_defence'],
                ];
                // if($newConfigData['recommend_config']['ip_num'] >= 1){
                $configData['ip'] = [
                    'value' => $newConfigData['recommend_config']['ip_num'] - 1,
                ];
                // }else{
                //     $configData['ip'] = [
                //         'value' => 0,
                //     ];
                // }
                if(isset($newConfigData['recommend_config']['ipv6_num']) && !empty($newConfigData['recommend_config']['ipv6_num'])){
                    $configData['ipv6_num'] = $newConfigData['recommend_config']['ipv6_num'];
                }else{
                    $configData['ipv6_num'] = 0;
                }

                // 保存新的配置
                $update = [
                    'config_data'           => json_encode($configData),
                    'recommend_config_id'   => $custom['recommend_config_id'],
                ];

                HostLinkModel::update($update, ['host_id'=>$hostId]);

                HostModel::where('id', $hostId)->update([
                    'base_info'     => $this->formatBaseInfo($configData),
                ]);

                $oldRcParam = $RecommendConfigModel->formatRecommendConfig($oldConfigData['recommend_config']);
                
                // 获取磁盘
                $DiskModel = new DiskModel();
                $disk = DiskModel::where('host_id', $hostId)->where('is_free', 0)->find();
                
                // 数据盘
                if($oldRcParam['data_disk']['size'] != $newRcParam['data_disk']['size']){
                    if($newRcParam['data_disk']['size'] > $oldRcParam['data_disk']['size']){
                        if(!empty($disk)){
                            // 成功失败都修改
                            DiskModel::where('id', $disk['id'])->update(['size'=>$newRcParam['data_disk']['size']]);
                        }else{
                            $DiskModel->createDataDisk([
                                'size'          => $newRcParam['data_disk']['size'],
                                'host_id'       => $hostId,
                                'type'          => $newRcParam['data_disk']['type'],
                            ]);
                        }
                    }else if($newRcParam['data_disk']['size'] == 0){
                        // 新套餐没有数据盘
                        if(!empty($disk)){
                            DiskModel::where('id', $disk['id'])->delete();
                        }
                    }
                }
                
                $post = [
                    'recommend_config_id' => RecommendConfigModel::where('id', $custom['recommend_config_id'])->value('upstream_id'),
                ];
                $result = $DownstreamCloudLogic->upgradeRecommendConfig($post,$orderId);

                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_upgrade_recommend_config'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'upgrade_package_config'){
                $hostLink = HostLinkModel::where('host_id', $hostId)->find();

                $configData = json_decode($hostLink['config_data'], true);
                $oldConfigData = $configData;
                $newConfigData = $custom['new_config_data'];
                foreach($newConfigData as $k=>$v){
                    $configData[$k] = $v;
                }

                // 保存新的配置
                $update = [
                    'config_data'           => json_encode($configData),
                ];

                HostLinkModel::update($update, ['host_id'=>$hostId]);

                // $post = [
                //     'recommend_config_id' => RecommendConfigModel::where('id', $custom['recommend_config_id'])->value('upstream_id'),
                // ];
                // $result = $DownstreamCloudLogic->upgradeRecommendConfig($post,$orderId);

                // $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                //     '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                //     '{act}'     => lang_plugins('mf_cloud_upgrade_recommend_config'),
                //     '{param}'   => json_encode($post),
                //     '{msg}'     => $result['msg'] ?? '',
                // ]);
                // active_log($description, 'host', $param['host']['id']);
            }else if($custom['type'] == 'upgrade_defence'){
                // 升级IP网段
                $hostLink = $this->where('host_id', $hostId)->find();

                $hostIps = [$custom['ip'] => ''];

                $IpDefenceModel = new IpDefenceModel();
                $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'], 'defence' => $custom['defence']['value'], 'ip' => array_keys($hostIps)]);

                hook('firewall_agent_set_meal_modify', ['type' => $custom['defence']['firewall_type'], 'set_meal_id' => $custom['defence']['defence_rule_id'], 'host_ips' => $hostIps]);
                
                $post = [
                    'ip'            => $custom['ip'],
                    'peak_defence'  => $custom['peak_defence'],
                ];
                $result = $DownstreamCloudLogic->upgradeDefence($post, $orderId);
                
                $description = lang_plugins('log_mf_cloud_downstream_upgrade_config_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{act}'     => lang_plugins('mf_cloud_host_upgrade_ip_defence'),
                    '{param}'   => json_encode($post),
                    '{msg}'     => $result['msg'] ?? '',
                ]);
                active_log($description, 'host', $param['host']['id']);
            }
            return ['status'=>200];
        }


        if($custom['type'] == 'buy_image'){
            // 购买镜像
            $HostImageLinkModel = new HostImageLinkModel();
            $HostImageLinkModel->saveLink($hostId, $custom['image_id']);
        }else if($custom['type'] == 'upgrade_common_config'){
            $hostLink = HostLinkModel::where('host_id', $hostId)->find();

            $configData = json_decode($hostLink['config_data'], true);
            $oldConfigData = $configData;
            $newConfigData = $custom['new_config_data'];
            foreach($newConfigData as $k=>$v){
                $configData[$k] = $v;
            }

            // 保存新的配置
            $update = [
                'config_data' => json_encode($configData),
            ];

            HostLinkModel::update($update, ['host_id'=>$hostId]);

            HostModel::where('id', $hostId)->update([
                'base_info'     => $this->formatBaseInfo($configData),
            ]);

            if($config['manual_manage']==1){
                return ['status'=>200];
            }
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                $description = lang_plugins('mf_cloud_upgrade_config_error_for_no_rel_id');
                active_log($description, 'host', $hostId);
                return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            $IdcsmartCloud = new IdcsmartCloud($param['server']);

            // 要升级
            $post = [];
            // 要修改的带宽
            $bw = [];

            // cpu变更
            if(isset($newConfigData['cpu'])){
                $post['cpu'] = $newConfigData['cpu']['value'];
                $post['advanced_cpu'] = $newConfigData['cpu']['other_config']['advanced_cpu'] ?? null;
            }
            if(isset($newConfigData['memory'])){
                if(isset($newConfigData['memory_unit']) && $newConfigData['memory_unit'] == 'MB'){
                    $post['memory'] = $newConfigData['memory']['value'];
                }else{
                    $post['memory'] = $newConfigData['memory']['value']*1024;
                }
            }
            if(isset($newConfigData['bw'])){
                $bw['in_bw'] = $newConfigData['bw']['value'];
                $bw['out_bw'] = $newConfigData['bw']['value'];

                if(is_numeric($newConfigData['bw']['other_config']['in_bw'])){
                    $bw['in_bw'] = $newConfigData['bw']['other_config']['in_bw'];
                }
                $post['advanced_bw'] = $newConfigData['cpu']['other_config']['advanced_bw'] ?? null;
            }
            if(isset($newConfigData['flow'])){
                // 非按需
                if(!empty($newConfigData['flow']['other_config']['bill_cycle'])){
                    $post['traffic_quota'] = $newConfigData['flow']['value'];
                    $post['traffic_type'] = $newConfigData['flow']['other_config']['traffic_type'];
                    if($newConfigData['flow']['other_config']['bill_cycle'] == 'month'){
                        $post['reset_flow_day'] = 1;
                    }else{
                        $post['reset_flow_day'] = date('j', $param['host']['active_time']);
                    }
                }else if(!empty($newConfigData['flow']['other_config']['traffic_type'])){
                    $post['traffic_type'] = $newConfigData['flow']['other_config']['traffic_type'];
                }

                $bw['in_bw'] = $newConfigData['flow']['other_config']['in_bw'];
                $bw['out_bw'] = $newConfigData['flow']['other_config']['out_bw'];
            }
            $description = [];

            $autoBoot = false;
            if(isset($newConfigData['cpu']) || isset($newConfigData['memory'])){
                $status = $IdcsmartCloud->cloudStatus($id);
                if($status['status'] == 200){
                    // 关机
                    if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
                        $this->safeCloudOff($IdcsmartCloud, $id);
                        // $res = $IdcsmartCloud->cloudHardOff($id);
                        // // 检查任务
                        // for($i = 0; $i<40; $i++){
                        //     $detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
                        //     if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
                        //         break;
                        //     }
                        //     sleep(10);
                        // }
                        $autoBoot = true;
                    }
                }
            }
            // 修改cpu限制
            if(isset($newConfigData['cpu']) && $oldConfigData['cpu']['other_config']['cpu_limit'] != $newConfigData['cpu']['other_config']['cpu_limit']){
                $res = $IdcsmartCloud->cloudModifyCpuLimit($id, $newConfigData['cpu']['other_config']['cpu_limit']);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_cpu_limit_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_cpu_limit_success');
                }
            }
            // 修改IPv6数量
            // if(isset($newConfigData['cpu']) && $oldConfigData['cpu']['other_config']['ipv6_num'] != $newConfigData['cpu']['other_config']['ipv6_num']){
            //     $res = $IdcsmartCloud->cloudModifyIpv6($id, (int)$newConfigData['cpu']['other_config']['ipv6_num']);
            //     if($res['status'] != 200){
            //         $description[] = '修改IPv6数量失败,原因:'.$res['msg'];
            //     }else{
            //         $description[] = '修改IPv6数量成功';
            //     }
            // }
            if(!empty($bw)){
                $res = $IdcsmartCloud->cloudModifyBw($id, $bw);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_success');
                }
            }
            $res = $IdcsmartCloud->cloudModify($id, $post);
            if($res['status'] != 200){
                $description[] = lang_plugins('mf_cloud_upgrade_common_config_fail') . $res['msg'];
            }else{
                $description[] = lang_plugins('mf_cloud_upgrade_common_config_success');
            }
            if($autoBoot){
                $IdcsmartCloud->cloudOn($id);
            }
            $description = lang_plugins('mf_cloud_upgrade_config_complete').implode(',', $description);
            active_log($description, 'host', $param['host']['id']);
        }else if($custom['type'] == 'buy_disk'){
            $hostLink = HostLinkModel::where('host_id', $hostId)->find();
            $id = $hostLink['rel_id'] ?? 0;

            $DiskModel = new DiskModel();
            $IdcsmartCloud = new IdcsmartCloud($param['server']);
            // 这里不用验证了
            $autoBoot = false;

            $delSuccess = [];
            $delFail = [];
            $addSuccess = [];
            $addFail = [];
            $storeId = 0;

            // 磁盘数据
            $diskInfo = [];
            // 先处理数据
            if(!empty($custom['remove_disk_id'])){
                foreach($custom['remove_disk_id'] as $k=>$v){
                    $disk = DiskModel::where('host_id', $hostId)->where('id', $v)->find();
                    if(empty($disk)){
                        continue;
                    }
                    $diskInfo[ $v ] = $disk;

                    DiskModel::where('host_id', $hostId)->where('id', $v)->delete();
                }
            }
            if(!empty($custom['add_disk'])){
                foreach($custom['add_disk'] as $k=>$v){
                    $diskId = $DiskModel->createDataDisk([
                        'size'      => $v['size'],
                        'host_id'   => $hostId,
                        'type'      => $v['type'] ?? '',
                        'price'     => $v['price'] ?? 0,
                    ]);
                    $custom['add_disk'][$k]['disk_id'] = $diskId;
                }
            }
            if($config['manual_manage']==1){
                // 手动资源修改状态
                DiskModel::where('host_id', $hostId)->where('status', 3)->update(['status'=>1]);
                return ['status' => 200];
            }

            // 调用魔方云
            $description = [];
            if(!empty($custom['remove_disk_id'])){
                $status = $IdcsmartCloud->cloudStatus($id);
                if($status['status'] == 200){
                    // 关机
                    if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
                        $this->safeCloudOff($IdcsmartCloud, $id);
                        $autoBoot = true;
                    }
                }
                foreach($custom['remove_disk_id'] as $v){
                    $disk = $diskInfo[ $v ] ?? [];
                    if(empty($disk)){
                        continue;
                    }
                    $deleteRes = $IdcsmartCloud->diskDelete($disk['rel_id']);
                    if($deleteRes['status'] == 200){
                        $delSuccess[] = $v;
                    }else{
                        $delFail[] = $v.','.lang_plugins('mf_cloud_reason').':'.$deleteRes['msg'];
                    }
                }
                if(!empty($delSuccess)){
                    $description[] = lang_plugins('mf_cloud_cancel_order_disk_success') . implode(',', $delSuccess);
                }
                if(!empty($delFail)){
                    $description[] = lang_plugins('mf_cloud_cancel_order_disk_fail').implode(',', $delFail);
                }
            }
            if(!empty($custom['add_disk'])){
                // 查找当前可用存储
                if(empty($storeId)){
                    // 和系统盘一致
                    $detail = $IdcsmartCloud->cloudDetail($id);

                    if($detail['status'] == 200){
                        $storeId = $detail['data']['disk'][0]['store_id'] ?? 0;
                    }
                }
                foreach($custom['add_disk'] as $v){
                    $addRes = $IdcsmartCloud->addAndMountDisk($id, [
                        'size'      => $v['size'],
                        'store'     => $storeId,
                        'driver'    => 'virtio',
                        'cache'     => 'writeback',
                        'io'        => 'native',
                    ]);
                    if($addRes['status'] != 200){
                        $addFail[] = $v['size'].','.lang_plugins('mf_cloud_reason').':'.$addRes['msg'];
                    }else{
                        $addSuccess[] = $v['size'];

                        // 修改关联关系
                        DiskModel::where('host_id', $hostId)->where('id', $v['disk_id'])->update([
                            'rel_id'    => $addRes['data']['diskid'] ?? 0,
                        ]);
                    }
                }
                if(!empty($addSuccess)){
                    $description[] = lang_plugins('mf_cloud_buy_disk_success') . implode(',', $addSuccess);
                }
                if(!empty($addFail)){
                    $description[] = lang_plugins('mf_cloud_buy_disk_fail') . implode(',', $addFail);
                }
            }
            if($autoBoot){
                $IdcsmartCloud->cloudOn($id);
            }
            // 重新获取磁盘列表
            // $res = $IdcsmartCloud->cloudDetail($id);
            // if($res['status'] == 200 && isset($res['data']['disk'])){
            //     $disk = $res['data']['disk'];

            //     $dataDisk = [];
            //     foreach($disk as $v){
            //         if($v['type'] == 'data' && $v['id'] != $hostLink['free_disk_id']){
            //             $dataDisk[] = $v['size'];
            //         }
            //     }
            //     HostLinkModel::update(['data_disk_size'=>json_encode($dataDisk)], ['host_id'=>$param['host']['id']]);
            // }
            $description = lang_plugins('mf_cloud_upgrade_disk_complete') . implode(',', $description);
            active_log($description, 'host', $hostId);
        }else if($param['custom']['type'] == 'resize_disk'){
            $custom = $param['custom'];

            $hostLink = HostLinkModel::where('host_id', $param['host']['id'])->find();
            $id = $hostLink['rel_id'] ?? 0;

            if($config['manual_manage']==1){
                foreach($custom['resize_disk'] as $v){
                    DiskModel::where('host_id', $hostId)->where('id', $v['id'])->update(['size'=>$v['size'], 'price'=>$v['price'] ]);
                }
                return ['status' => 200];
            }

            $IdcsmartCloud = new IdcsmartCloud($param['server']);

            // 直接关机扩容
            $autoBoot = false;
            $status = $IdcsmartCloud->cloudStatus($id);
            if($status['status'] == 200){
                // 关机
                if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
                    $this->safeCloudOff($IdcsmartCloud, $id);
                    // $res = $IdcsmartCloud->cloudHardOff($id);
                    // // 检查任务
                    // for($i = 0; $i<40; $i++){
                    //     $detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
                    //     if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
                    //         break;
                    //     }
                    //     sleep(10);
                    // }
                    $autoBoot = true;
                }
            }

            $success = [];
            $fail = [];
            $description = [];

            foreach($custom['resize_disk'] as $v){
                $disk = DiskModel::find($v['id']);
                if(empty($disk)){
                    $fail[] = lang_plugins('mf_cloud_disk') . 'ID:'.$v['id'].','.lang_plugins('mf_cloud_reason').':'.lang_plugins('disk_not_found');
                    continue;
                }
                $resizeRes = $IdcsmartCloud->diskModify($disk['rel_id'], ['size'=>$v['size']]);
                if($resizeRes['status'] == 200){
                    $success[] = $v['id'];
                }else{
                    $fail[] = lang_plugins('mf_cloud_disk') . 'ID:'.$v['id'].','.lang_plugins('mf_cloud_reason').':'.$resizeRes['msg'];
                }
                // 成功失败都修改
                DiskModel::where('host_id', $hostId)->where('id', $v['id'])->update(['size'=>$v['size'], 'price'=>$v['price'] ]);
            }
            if($autoBoot){
                $IdcsmartCloud->cloudOn($id);
            }
            // 重新获取磁盘列表
            // $res = $IdcsmartCloud->cloudDetail($this->id);
            // if($res['status'] == 200 && isset($res['data']['disk'])){
            //     $disk = $res['data']['disk'];

            //     $dataDisk = [];
            //     foreach($disk as $v){
            //         if($v['type'] == 'data' && $v['id'] != $hostLink['free_disk_id']){
            //             $dataDisk[] = $v['size'];
            //         }
            //     }
            //     HostLinkModel::update(['data_disk_size'=>json_encode($dataDisk)], ['host_id'=>$param['host']['id']]);
            // }

            if(!empty($success)){
                $description[] = lang_plugins('mf_cloud_upgrade_resize_disk_success') . implode(',', $success);
            }
            if(!empty($fail)){
                $description[] = lang_plugins('mf_cloud_upgrade_resize_disk_fail') . implode(',', $fail);
            }
            $description = lang_plugins('mf_cloud_upgrade_resize_disk_complete') . implode(',', $description);
            active_log($description, 'host', $param['host']['id']);
        }else if($custom['type'] == 'modify_backup'){
            $hostLink = $this->where('host_id', $hostId)->find();
            $id = $hostLink['rel_id'] ?? 0;
            $IdcsmartCloud = new IdcsmartCloud($param['server']);

            $update = [ $custom['backup_type'].'_num'=>$custom['num'] ];

            $type = ['backup'=>lang_plugins('backup'), 'snap'=>lang_plugins('snap')];

            $configData = json_decode($hostLink['config_data'], true);
            $configData[ $custom['backup_type'] ] = $custom['backup_config'];
            $update['config_data'] = json_encode($configData);

            HostLinkModel::update($update, ['host_id'=>$param['host']['id']]);

            if($config['manual_manage']==1){
                return ['status' => 200];
            }

            $res = $IdcsmartCloud->cloudModify($hostLink['rel_id'], $update);
            if($res['status'] == 200){
                $description = lang_plugins('log_mf_cloud_upgrade_backup_num_success', [
                    '{type}' => $type[$custom['backup_type']],
                    '{num}'  => $custom['num'],
                ]);
            }else{
                $description = lang_plugins('log_mf_cloud_upgrade_backup_num_fail', [
                    '{type}'    => $type[$custom['backup_type']],
                    '{num}'     => $custom['num'],
                    '{reason}'  => $res['msg'],
                ]);
            }
            active_log($description, 'host', $hostId);
        }else if($custom['type'] == 'upgrade_ip_num'){
            // 升级IP数量
            $hostLink = $this->where('host_id', $hostId)->find();
            $id = $hostLink['rel_id'] ?? 0;

            // 直接保存configData
            $configData = json_decode($hostLink['config_data'], true);
            $oldIpNum = $configData['ip']['value'] ?? 0;
            $oldIpv6Num = $configData['ipv6_num'] ?? 0;

            if(!empty($custom['ip_data'])){
                $configData['ip'] = $custom['ip_data'];
            }
            if(!empty($custom['ipv6_data'])){
                $configData['ipv6_num'] = $custom['ipv6_data']['value'];
            }

            $this->where('id', $hostLink['id'])->update(['config_data'=>json_encode($configData)]);

            if($config['manual_manage']==1){
                return ['status' => 200];
            }

            $ipGroup = 0;
            $ipv6GroupId = 0;
            // 获取下线路信息
            $line = LineModel::find($configData['line']['id']);
            if(!empty($line)){
                $ipv6GroupId = $line['ipv6_group_id'];
                if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value']) && !empty($line['defence_ip_group'])){
                    $ipGroup = $line['defence_ip_group'];
                }else{
                    $ipGroup = $line['bw_ip_group'];
                }
            }
            $supportNat = ($hostLink['type'] == 'lightHost' || $configData['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
            // 当前实例是否免费携带IP
            $baseIpNum = abs($hostLink['default_ipv4']);
            if($supportNat){
                $baseIpNum = 0;
            }

            $IdcsmartCloud = new IdcsmartCloud($param['server']);
            $description = [];

            if(!empty($custom['ip_data'])){
                // 指定IP降级
                if(!empty($custom['remove_ipv4'])){
                    $res = $IdcsmartCloud->floatIpDelete($id, [
                        'ip'   => $custom['remove_ipv4'],
                        'type' => 'ip',
                    ]);
                    if($res['status'] == 200){
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_remove_target_ip_success', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpNum,
                            '{new}'       => $custom['ip_data']['value'],
                            '{ip}'        => implode(',', $custom['remove_ipv4']),
                        ]);
                    }else{
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_remove_target_ip_fail', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpNum,
                            '{new}'       => $custom['ip_data']['value'],
                            '{ip}'        => implode(',', $custom['remove_ipv4']),
                            '{reason}'    => $res['msg'],
                        ]);
                    }
                }else{
                    // 修改IP数量
                    if(!empty($ipGroup)){
                        $res = $IdcsmartCloud->cloudModifyIpNum($id, ['num'=>$custom['ip_data']['value']+$baseIpNum, 'ip_group'=>$ipGroup, 'is_force_group'=>1 ]);
                    }else{
                        $res = $IdcsmartCloud->cloudModifyIpNum($id, ['num'=>$custom['ip_data']['value']+$baseIpNum ]);
                    }
                    if($res['status'] == 200){
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_success', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpNum,
                            '{new}'       => $custom['ip_data']['value'],
                        ]);
                    }else{
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_fail', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpNum,
                            '{new}'       => $custom['ip_data']['value'],
                            '{reason}'    => $res['msg'],
                        ]);
                    }
                }
            }
            if(!empty($custom['ipv6_data'])){
                // 指定IPv6降级
                if(!empty($custom['remove_ipv6'])){
                    $res = $IdcsmartCloud->cloudDeleteIpv6($id, [
                        'ipv6' => $custom['remove_ipv6'],
                        'type' => 'ip',
                    ]);
                    if($res['status'] == 200){
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_remove_target_ipv6_success', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpv6Num,
                            '{new}'       => $custom['ipv6_data']['value'],
                            '{ip}'        => implode(',', $custom['remove_ipv6']),
                        ]);
                    }else{
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ip_remove_target_ipv6_fail', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpv6Num,
                            '{new}'       => $custom['ipv6_data']['value'],
                            '{ip}'        => implode(',', $custom['remove_ipv6']),
                            '{reason}'    => $res['msg'],
                        ]);
                    }
                }else{
                    // 修改IP数量
                    $res = $IdcsmartCloud->cloudModifyIpv6($id, $custom['ipv6_data']['value'], $ipv6GroupId);
                    if($res['status'] == 200){
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_success', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpv6Num,
                            '{new}'       => $custom['ipv6_data']['value'],
                        ]);
                    }else{
                        $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_fail', [
                            '{hostname}'  => $param['host']['name'],
                            '{old}'       => $oldIpv6Num,
                            '{new}'       => $custom['ipv6_data']['value'],
                            '{reason}'    => $res['msg'],
                        ]);
                    }
                }
            }

            $ip = $this->syncIp(['host_id'=>$hostId, 'id'=>$id], $IdcsmartCloud);
            if(!empty($ip)){
                if(!empty($ip['assign_ip']) && !empty($ip['dedicate_ip']) ){
                    $ips = explode(',', $ip['assign_ip']);
                    $ips[] = $ip['dedicate_ip'];
                }else if(!empty($ip['dedicate_ip'])){
                    $ips = [ $ip['dedicate_ip'] ];
                }else{
                    $ips = [];
                }
                $ips = array_filter($ips, function($val){
                    return filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                });
                // wyh 20250319 改
                try {
                    $CloudLogic = new CloudLogic($param['host']['id']);
                    $ipChangeRes = $CloudLogic->ipChange([
                        'ips' => $ips,
                    ]);
                    if($ipChangeRes['status'] != 200){
                        throw new \Exception($ipChangeRes['msg']);
                    }
                } catch (\Exception $e) {
                    return ['status'=>400, 'msg'=>$e->getMessage()];
                }

//                $IpDefenceModel = new IpDefenceModel();
//                if(!empty($ips)){
//                    $IpDefenceModel->where('host_id', $param['host']['id'])->whereNotIn('ip', $ips)->delete();
//
//                    if(isset($custom['new_config_data']['default_defence']) && !empty($custom['new_config_data']['default_defence'])){
//                        $hostIps = [];
//                        $exist = $IpDefenceModel->where('host_id', $param['host']['id'])->column('ip');
//                        foreach ($ips as $v) {
//                            if(!in_array($v, $exist)){
//                                $hostIps[$v] = '';
//                            }
//                        }
//
//                        if(!empty($hostIps)){
//                            $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'],'defence' => $custom['new_config_data']['default_defence']['value'], 'ip' => array_keys($hostIps)]);
//
//                            hook('firewall_set_meal_modify', ['type' => $custom['new_config_data']['default_defence']['firewall_type'], 'set_meal_id' => $custom['new_config_data']['default_defence']['defence_rule_id'], 'host_ips' => $hostIps]);
//                        }
//                    }
//                }else{
//                    $IpDefenceModel->where('host_id', $param['host']['id'])->delete();
//                }
            }

            $description = implode(',', $description);
            active_log($description, 'host', $hostId);
        }else if($custom['type'] == 'upgrade_recommend_config'){
            // 套餐升降级
            $hostLink = HostLinkModel::where('host_id', $hostId)->find();

            $configData = json_decode($hostLink['config_data'], true);
            $oldConfigData = $configData;
            $newConfigData = $custom['new_config_data'];
            foreach($newConfigData as $k=>$v){
                $configData[$k] = $v;
            }

            $RecommendConfigModel = new RecommendConfigModel();
            $newRcParam = $RecommendConfigModel->formatRecommendConfig($newConfigData['recommend_config']);

            $configData['cpu'] = [
                'value' => $newConfigData['recommend_config']['cpu'],
            ];
            $configData['memory'] = [
                'value' => $newConfigData['recommend_config']['memory'],
            ];
            $configData['system_disk'] = [
                'value' => $newConfigData['recommend_config']['system_disk_size'],
                'other_config' => [
                    'disk_type' => $newConfigData['recommend_config']['system_disk_type'],
                ],
            ];
            if($newRcParam['data_disk']['size'] > 0){
                $configData['data_disk'][] = [
                    'value'         => $newConfigData['recommend_config']['data_disk_size'],
                    'other_config'  => [
                        'disk_type' => $newConfigData['recommend_config']['data_disk_type']
                    ],
                ];
            }
            $configData['bw'] = [
                'value' => $newConfigData['recommend_config']['bw'],
                'other_config' => [
                    'in_bw' => $newRcParam['in_bw'] != $newRcParam['out_bw'] ? $newRcParam['in_bw'] : '',
                ],
            ];
            $configData['flow'] = [
                'value' => $newConfigData['recommend_config']['flow'],
                'other_config' => [
                    'in_bw' => $newRcParam['in_bw'],
                    'out_bw'=> $newRcParam['out_bw'],
                ],
            ];
            $configData['defence'] = [
                'value' => $newConfigData['recommend_config']['peak_defence'],
            ];
            if($newConfigData['recommend_config']['ip_num'] > 1){
                $configData['ip'] = [
                    'value' => $newConfigData['recommend_config']['ip_num'] - 1,
                ];
            }else{
                $configData['ip'] = [
                    'value' => 0,
                ];
            }
            if(isset($newConfigData['recommend_config']['ipv6_num']) && !empty($newConfigData['recommend_config']['ipv6_num'])){
                $configData['ipv6_num'] = $newConfigData['recommend_config']['ipv6_num'];
            }else{
                $configData['ipv6_num'] = 0;
            }

            // 保存新的配置
            $update = [
                'config_data'           => json_encode($configData),
                'recommend_config_id'   => $custom['recommend_config_id'],
            ];

            HostLinkModel::update($update, ['host_id'=>$hostId]);

            HostModel::where('id', $hostId)->update([
                'base_info'     => $this->formatBaseInfo($configData),
            ]);

            if($config['manual_manage']==1){
                $oldRcParam = $RecommendConfigModel->formatRecommendConfig($oldConfigData['recommend_config']);
                // 数据盘
                $disk = DiskModel::where('host_id', $hostId)->where('is_free', 0)->find();
                if($oldRcParam['data_disk']['size'] != $newRcParam['data_disk']['size']){
                    if($newRcParam['data_disk']['size'] > $oldRcParam['data_disk']['size']){
                        if(!empty($disk)){
                            // 磁盘扩容
                            DiskModel::where('id', $disk['id'])->update(['size'=>$newRcParam['data_disk']['size']]);
                        }else{
                            DiskModel::create([
                                'name'          => '',
                                'size'          => $newRcParam['data_disk']['size'],
                                'rel_id'        => 0,
                                'host_id'       => $hostId,
                                'create_time'   => time(),
                                'type'          => $newRcParam['data_disk']['type'],
                                'price'         => 0,
                                'is_free'       => 0,
                            ]);
                        }
                    }else if($newRcParam['data_disk']['size'] == 0){
                        // 新套餐没有数据盘
                        if(!empty($disk)){
                            DiskModel::where('id', $disk['id'])->delete();
                        }
                    }
                }
                return ['status' => 200];
            }
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                $description = lang_plugins('mf_cloud_upgrade_config_error_for_no_rel_id');
                active_log($description, 'host', $hostId);
                return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            $IdcsmartCloud = new IdcsmartCloud($param['server']);

            $autoBoot = false;  // 自动开启
            $needOff = false;   // 是否需要关机
            $post = [];         // 升级的参数
            $bw = [];           // 带宽
            $ipPost = [];       // IP接口参数
            $description = [];  // 日志
            
            $oldRcParam = $RecommendConfigModel->formatRecommendConfig($oldConfigData['recommend_config']);
            // 使用config_data里面的带宽
            // if(!empty($oldConfigData['recommend_config']['flow'])){
            //     if(isset($oldConfigData['flow']['other_config']['in_bw'])){
            //         $oldRcParam['in_bw'] = $oldConfigData['flow']['other_config']['in_bw'];
            //     }
            //     if(isset($oldConfigData['flow']['other_config']['out_bw'])){
            //         $oldRcParam['out_bw'] = $oldConfigData['flow']['other_config']['out_bw'];
            //     }
            // }else{
            //     if(isset($oldConfigData['bw']['value'])){
            //         $oldRcParam['out_bw'] = $oldConfigData['bw']['value'];
            //     }
            //     if(isset($oldConfigData['bw']['other_config']['in_bw'])){
            //         $oldRcParam['in_bw'] = $oldConfigData['bw']['other_config']['in_bw'];
            //     }
            // }

            if($oldRcParam['cpu'] != $newRcParam['cpu']){
                $post['cpu'] = $newRcParam['cpu'];
                $needOff = true;
            }
            if($oldRcParam['memory'] != $newRcParam['memory']){
                $post['memory'] = $newRcParam['memory'];
                $needOff = true;
            }
            // if($oldRcParam['in_bw'] != $newRcParam['in_bw']){
                $bw['in_bw'] = $newRcParam['in_bw'];
            // }
            // if($oldRcParam['out_bw'] != $newRcParam['out_bw']){
                $bw['out_bw'] = $newRcParam['out_bw'];
            // }
            if($oldRcParam['flow'] != $newRcParam['flow']){
                $post['traffic_quota'] = $newRcParam['flow'];
            }
            if($oldRcParam['advanced_cpu'] != $newRcParam['advanced_cpu']){
                $post['advanced_cpu'] = $newRcParam['advanced_cpu'];
            }
            if($oldRcParam['advanced_bw'] != $newRcParam['advanced_bw']){
                $post['advanced_bw'] = $newRcParam['advanced_bw'];
            }
            if($oldRcParam['traffic_type'] != $newRcParam['traffic_type']){
                $post['traffic_type'] = $newRcParam['traffic_type'];
            }
            if($oldRcParam['bill_cycle'] != $newRcParam['bill_cycle']){
                if($newRcParam['bill_cycle'] == 'month'){
                    $post['reset_flow_day'] = 1;
                }else{
                    $post['reset_flow_day'] = date('j', $param['host']['active_time']);
                }
            }
            if($configData['network_type'] == 'normal' && ($oldRcParam['ip_num'] != $newRcParam['ip_num'] || $oldRcParam['ipv6_num'] != $newRcParam['ipv6_num'])){
                $needOff = true;
            }
            // 获取磁盘
            $disk = DiskModel::where('host_id', $hostId)->where('is_free', 0)->find();
            if(!empty($disk) && $newRcParam['data_disk']['size'] != $disk['size']){
                $needOff = true;
            }
            if($needOff){
                $status = $IdcsmartCloud->cloudStatus($id);
                if($status['status'] == 200){
                    // 关机
                    if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
                        $this->safeCloudOff($IdcsmartCloud, $id);
                        $autoBoot = true;
                    }
                }
            }
            // 修改cpu限制
            if($oldRcParam['cpu_limit'] != $newRcParam['cpu_limit']){
                $res = $IdcsmartCloud->cloudModifyCpuLimit($id, $newRcParam['cpu_limit']);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_cpu_limit_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_cpu_limit_success');
                }
            }
            if(!empty($bw)){
                $res = $IdcsmartCloud->cloudModifyBw($id, $bw);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_success');
                }
            }
            // IP
            if($oldRcParam['ip_num'] != $newRcParam['ip_num']){
                $res = $IdcsmartCloud->cloudModifyIpNum($id, [
                    'num'       => $newRcParam['ip_num'],
                    'ip_group'  => $newRcParam['ip_group'],
                ]);
                if($res['status'] == 200){
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_success', [
                        '{hostname}'    => $param['host']['name'],
                        '{old}'         => $oldRcParam['ip_num'],
                        '{new}'         => $newRcParam['ip_num'],
                    ]);
                }else{
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_fail', [
                        '{hostname}'    => $param['host']['name'],
                        '{old}'         => $oldRcParam['ip_num'],
                        '{new}'         => $newRcParam['ip_num'],
                        '{reason}'      => $res['msg'],
                    ]);
                }
            }
            // IPv6
            if($oldRcParam['ipv6_num'] != $newRcParam['ipv6_num']){
                $res = $IdcsmartCloud->cloudModifyIpv6($id, $newRcParam['ipv6_num'], $newRcParam['ipv6_group_id']);
                if($res['status'] == 200){
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_success', [
                        '{hostname}'    => $param['host']['name'],
                        '{old}'         => $oldRcParam['ipv6_num'],
                        '{new}'         => $newRcParam['ipv6_num'],
                    ]);
                }else{
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_fail', [
                        '{hostname}'    => $param['host']['name'],
                        '{old}'         => $oldRcParam['ipv6_num'],
                        '{new}'         => $newRcParam['ipv6_num'],
                        '{reason}'      => $res['msg'],
                    ]);
                }
            }
            // 数据盘
            if($oldRcParam['data_disk']['size'] != $newRcParam['data_disk']['size']){
                if($newRcParam['data_disk']['size'] > $oldRcParam['data_disk']['size']){
                    if(!empty($disk)){
                        // 磁盘扩容
                        $resizeRes = $IdcsmartCloud->diskModify($disk['rel_id'], ['size'=>$newRcParam['data_disk']['size']]);
                        if($resizeRes['status'] == 200){
                            $description[] = lang_plugins('mf_cloud_upgrade_resize_disk_success') . $disk['rel_id'];
                        }else{
                            $description[] = lang_plugins('mf_cloud_disk') . 'ID:'.$disk['rel_id'].','.lang_plugins('mf_cloud_reason').':'.$resizeRes['msg'];
                        }
                        // 成功失败都修改
                        DiskModel::where('id', $disk['id'])->update(['size'=>$newRcParam['data_disk']['size']]);
                    }else{
                        // 查找当前可用存储
                        if(empty($newRcParam['data_disk']['store_id'])){
                            // 和系统盘一致
                            $detail = $IdcsmartCloud->cloudDetail($id);

                            if($detail['status'] == 200){
                                $newRcParam['data_disk']['store_id'] = $detail['data']['disk'][0]['store_id'] ?? 0;
                            }
                        }

                        $addRes = $IdcsmartCloud->addAndMountDisk($id, [
                            'size'  => $newRcParam['data_disk']['size'],
                            'store' => $newRcParam['data_disk']['store_id'],
                            'driver'=> 'virtio',
                            'cache' => 'writeback',
                            'io'    => 'native'
                        ]);
                        if($addRes['status'] == 200){
                            DiskModel::create([
                                'name'          => '',
                                'size'          => $newRcParam['data_disk']['size'],
                                'rel_id'        => $addRes['data']['diskid'] ?? 0,
                                'host_id'       => $hostId,
                                'create_time'   => time(),
                                'type'          => $newRcParam['data_disk']['type'],
                                'price'         => 0,
                                'is_free'       => 0,
                            ]);
                            $description[] = lang_plugins('mf_cloud_buy_disk_success') . $newRcParam['data_disk']['size'];
                        }else{
                            $description[] = lang_plugins('mf_cloud_buy_disk_fail') . $newRcParam['data_disk']['size'].','.lang_plugins('mf_cloud_reason').':'.$addRes['msg'];
                        }
                    }
                }else if($newRcParam['data_disk']['size'] == 0){
                    // 新套餐没有数据盘
                    if(!empty($disk)){
                        $deleteRes = $IdcsmartCloud->diskDelete($disk['rel_id']);
                        if($deleteRes['status'] == 200){
                            $description[] = lang_plugins('log_mf_cloud_delete_data_disk_success', [
                                '{name}'    => $disk['name'],
                                '{size}'    => $disk['size'],
                            ]);

                            DiskModel::where('id', $disk['id'])->delete();
                        }else{
                            $description[] = lang_plugins('log_mf_cloud_delete_data_disk_fail', [
                                '{name}'      => $disk['name'],
                                '{reason}'    => $deleteRes['msg'],
                            ]);
                        }
                    }
                }
            }
            if(!empty($post)){
                $res = $IdcsmartCloud->cloudModify($id, $post);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_common_config_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_common_config_success');
                }
            }
            if($autoBoot){
                $IdcsmartCloud->cloudOn($id);
            }

            $this->syncIp(['host_id'=>$hostId, 'id'=>$id], $IdcsmartCloud);

            $description = lang_plugins('mf_cloud_upgrade_config_complete').implode(',', $description);
            active_log($description, 'host', $param['host']['id']);
        }else if($custom['type'] == 'upgrade_package_config'){
            $hostLink = HostLinkModel::where('host_id', $hostId)->find();
            $id = $hostLink['rel_id'];

            // 先保存数据
            $configData = json_decode($hostLink['config_data'], true);
            $oldConfigData = $configData;
            $newConfigData = $custom['new_config_data'];
            foreach($newConfigData as $k=>$v){
                $configData[$k] = $v;
            }

            // 保存新的配置
            $update = [
                'config_data'           => json_encode($configData),
            ];

            HostLinkModel::update($update, ['host_id'=>$hostId]);

            $ipGroup = 0;
            $ipv6GroupId = 0;
            // 获取下线路信息
            $line = LineModel::find($configData['line']['id']);
            if(!empty($line)){
                $ipv6GroupId = $line['ipv6_group_id'];
                if($line['defence_enable'] == 1 && !empty($configData['defence']['value'])){
                    $ipGroup = $line['defence_ip_group'];
                }else{
                    $ipGroup = $line['bw_ip_group'];
                }
            }

            $IdcsmartCloud = new IdcsmartCloud($param['server']);
            $description = [];

            $post = [];
            $bw = [];

            if(!empty($newConfigData['flow'])){
                $post['traffic_quota'] = $newConfigData['flow']['value'];
                $post['traffic_type'] = $newConfigData['flow']['other_config']['traffic_type'];
                if($newConfigData['flow']['other_config']['bill_cycle'] == 'month'){
                    $post['reset_flow_day'] = 1;
                }else{
                    $post['reset_flow_day'] = date('j', $param['host']['active_time']);
                }

                $bw['in_bw'] = $newConfigData['flow']['other_config']['in_bw'];
                $bw['out_bw'] = $newConfigData['flow']['other_config']['out_bw'];
            }
            if(!empty($newConfigData['bw'])){
                $bw['in_bw'] = $newConfigData['bw']['other_config']['in_bw'] === '' ? $newConfigData['bw']['value'] : 0;
                $bw['out_bw'] = $newConfigData['bw']['value'];
            }
            if(!empty($newConfigData['ip'])){
                // 修改IP数量
                $res = $IdcsmartCloud->cloudModifyIpNum($id, [
                    'num'       => $newConfigData['ip']['value']+1,
                    'ip_group'  => $ipGroup
                ]);
                if($res['status'] == 200){
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_success', [
                        '{hostname}'  => $param['host']['name'],
                        '{old}'       => isset($oldConfigData['ip']['value']) ? $oldConfigData['ip']['value']+1 : 0,
                        '{new}'       => $newConfigData['ip']['value']+1,
                    ]);
                }else{
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ip_num_fail', [
                        '{hostname}'  => $param['host']['name'],
                        '{old}'       => isset($oldConfigData['ip']['value']) ? $oldConfigData['ip']['value']+1 : 0,
                        '{new}'       => $newConfigData['ip']['value']+1,
                        '{reason}'    => $res['msg'],
                    ]);
                }
            }
            if(isset($newConfigData['ipv6_num'])){
                // 修改IP数量
                $res = $IdcsmartCloud->cloudModifyIpv6($id, $newConfigData['ipv6_num'], $ipv6GroupId);
                if($res['status'] == 200){
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_success', [
                        '{hostname}'  => $param['host']['name'],
                        '{old}'       => $oldConfigData['ipv6_num'] ?? 0,
                        '{new}'       => $newConfigData['ipv6_num'] ?? 0,
                    ]);
                }else{
                    $description[] = lang_plugins('log_mf_cloud_upgrade_ipv6_num_fail', [
                        '{hostname}'  => $param['host']['name'],
                        '{old}'       => $oldConfigData['ipv6_num'] ?? 0,
                        '{new}'       => $newConfigData['ipv6_num'] ?? 0,
                        '{reason}'    => $res['msg'],
                    ]);
                }
            }
            if(!empty($bw)){
                $res = $IdcsmartCloud->cloudModifyBw($id, $bw);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_bw_success');
                }
            }
            if(!empty($post)){
                $res = $IdcsmartCloud->cloudModify($id, $post);
                if($res['status'] != 200){
                    $description[] = lang_plugins('mf_cloud_upgrade_common_config_fail') . $res['msg'];
                }else{
                    $description[] = lang_plugins('mf_cloud_upgrade_common_config_success');
                }
            }
            $this->syncIp(['host_id'=>$hostId, 'id'=>$id], $IdcsmartCloud);

            $description = lang_plugins('mf_cloud_upgrade_config_complete').implode(',', $description);
            active_log($description, 'host', $param['host']['id']);
        }else if($custom['type'] == 'upgrade_defence'){
            // 升级IP网段
            $hostLink = $this->where('host_id', $hostId)->find();
            if (!empty($hostLink)){
                $configData = json_decode($hostLink['config_data']??[], true);
                // 变更子产品周期
                if (!empty($custom['upgrade_with_duration'])){
                    HostModel::where('id',$hostId)->update([
                        'due_time' => time() + ($custom['due_time']??0),
                        'update_time' => time(),
                        'billing_cycle_name' => $custom['duration']['name']??'',
                        'billing_cycle_time' => $custom['due_time']??0,
                    ]);
                    $configData['duration'] = $custom['duration']??[];
                    HostLinkModel::where('host_id',$hostId)->update([
                        'config_data' => json_encode($configData)
                    ]);
                }
                $line = LineModel::where('id',$configData['line']['id']??0)->find();
                // 降级到默认防御，更改子产品周期与主产品一致
                if (!empty($line) && $line['order_default_defence']===($custom['defence']['firewall_type'].'_'.$custom['defence']['defence_rule_id'])){
                    $host = HostModel::where('id',$hostLink['parent_host_id']??0)->find();
                    if (!empty($host)){
                        HostModel::where('id',$hostId)->update([
                            'due_time' => $host['due_time'],
                            'billing_cycle_name' => $host['billing_cycle_name'],
                            'billing_cycle_time' => $host['billing_cycle_time'],
                            'update_time' => time(),
                        ]);
                        $parentHostLink = HostLinkModel::where('host_id',$hostLink['parent_host_id'])->find();
                        $parentConfigData = json_decode($parentHostLink['config_data']??[], true);
                        $configData['duration'] = $parentConfigData['duration']??[];
                        HostLinkModel::where('host_id',$hostId)->update([
                            'config_data' => json_encode($configData)
                        ]);
                    }
                }
            }

            $hostIps = [$custom['ip'] => ''];

            $IpDefenceModel = new IpDefenceModel();
            $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'], 'defence' => $custom['defence']['value'], 'ip' => array_keys($hostIps)]);

            hook('firewall_set_meal_modify', ['type' => $custom['defence']['firewall_type'], 'set_meal_id' => $custom['defence']['defence_rule_id'], 'host_ips' => $hostIps]);
            
            $description = lang_plugins('log_mf_cloud_host_upgrade_ip_defence_success', [
                '{host}' => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#'
            ]);
            active_log($description, 'host', $hostId);
        }

        return ['status'=>200];
    }

    /**
     * 时间 2023-02-09
     * @title 结算后
     * @desc 结算后
     * @author hh
     * @version v1
     */
    public function afterSettle($param)
    {
        // 参数不需要重新验证了,计算已经验证了
        $custom = $param['custom'] ?? [];
        if(!empty($custom['rand_password'])){
            $custom['password'] = ToolLogic::generateRandomPassword(12);
        }
        $clientId = !empty(get_admin_id()) ? HostModel::where('id', $param['host_id'])->value('client_id') : get_client_id();
        $hostId = $param['host_id'];
        $time = time();
        // 子产品
        if (!empty($custom['parent_host_id'])){
            $parentHost = HostModel::where('client_id', $clientId)->find($custom['parent_host_id']);
            if(empty($parentHost)){
                throw new \Exception(lang_plugins('mf_cloud_parent_host_not_found'));
            }
            $parentHostLink = $this->where('host_id', $custom['parent_host_id'])->find();
            if(empty($parentHostLink)){
                throw new \Exception(lang_plugins('mf_cloud_parent_host_not_found'));
            }
            $position = $param['position'] ?? 0;
            $configData = DurationModel::$configData[$position];
            $configData = $configData['defence'] ?? $configData;
            $data = [
                'host_id'               => $param['host_id'],
                'data_center_id'        => $parentHostLink['data_center_id'],
                'image_id'              => $parentHostLink['image_id'],
                'backup_num'            => $parentHostLink['backup_num'],
                'snap_num'              => $parentHostLink['snap_num'],
                // 'power_status'          => 'on',
                'password'              => $parentHostLink['password'],
                'config_data'           => json_encode($configData),
                'create_time'           => time(),
                'type'                  => $parentHostLink['type'],
                'recommend_config_id'   => $parentHostLink['recommend_config_id'],
                'default_ipv4'          => $parentHostLink['default_ipv4'],
                'ssh_key_id'            => $parentHostLink['ssh_key_id'],
                'vpc_network_id'        => $parentHostLink['vpc_network_id'],
                'parent_host_id'        => $custom['parent_host_id'],
            ];
            $res = $this->where('host_id', $param['host_id'])->find();
            if (empty($res)) {
                $this->create($data);
            } else {
                $this->update($data, ['host_id' => $param['host_id']]);
            }
            $hostData = [
                'client_notes' => $custom['notes'] ?? '',
                'base_info' => '',//$this->formatBaseInfo($configData),
            ];
            HostModel::where('id', $param['host_id'])->update($hostData);

            $enableIdcsmartRenewAddon = PluginModel::where('name', 'IdcsmartRenew')->where('module', 'addon')->where('status', 1)->find();
            if ($enableIdcsmartRenewAddon && class_exists('addon\idcsmart_renew\model\IdcsmartRenewAutoModel')) {
                $parentRenew = IdcsmartRenewAutoModel::where('host_id', $custom['parent_host_id'])->find();
                if(!empty($parentRenew)){
                    IdcsmartRenewAutoModel::where('host_id', $hostId)->delete();
                    IdcsmartRenewAutoModel::create([
                        'host_id' => $hostId,
                        'status' => 0,//$parentRenew['status'],
                    ]);
                }
            }
            // 这些数据不需要
            // 下单的时候保存配置到附加表
//            if(class_exists('app\common\model\HostAdditionModel')){
//                $HostAdditionModel = new HostAdditionModel();
//                $parentHostAddition = $HostAdditionModel->where('host_id',$custom['parent_host_id'])->find();
//                if (!empty($parentHostAddition)){
//                    $HostAdditionModel->hostAdditionSave($hostId, [
//                        'country_id'    => $parentHostAddition['country_id'],
//                        'city'          => $parentHostAddition['city'],
//                        'area'          => $parentHostAddition['area'],
//                        'image_icon'    => $parentHostAddition['image_icon'],
//                        'image_name'    => $parentHostAddition['image_name'],
//                        'username'      => $parentHostAddition['username'],
//                        'password'      => $parentHostAddition['password'],
//                    ]);
//                }
//            }
//            // 磁盘内容
//            $dataDisk = [];
//            $DiskModel = new DiskModel();
//            $parentDisk = $DiskModel->where('host_id',$custom['parent_host_id'])->select()->toArray();
//            foreach($parentDisk as $v){
//                $dataDisk[] = [
//                    'name'          => $v['name'],
//                    'size'          => $v['size'],
//                    'host_id'       => $hostId,
//                    'type'          => $v['type'],
//                    'price'         => $v['price'],
//                    'create_time'   => time(),
//                    'is_free'       => $v['is_free'],
//                    'status'        => $v['status'],
//                    'type2'         => $v['type2'],
//                    'free_size'     => $v['free_size'],
//                ];
//            }
//            if(!empty($dataDisk)){
//                $DiskModel->insertAll($dataDisk);
//            }
        }
        else{
            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
            $config = $config['data'];

            // 减少套餐试用库存
            if ($custom['duration_id']==config('idcsmart.pay_ontrial') && !empty($custom['recommend_config_id'])){
                RecommendConfigModel::where('id', $custom['recommend_config_id'])
                    ->where('ontrial_stock_control',1)
                    ->dec('ontrial_qty',1)->update();
            }

            $position = $param['position'] ?? 0;
            $configData = DurationModel::$configData[$position];
            $configData['network_type'] = $custom['network_type'];
            $port = $configData['port'] ?? mt_rand(20000, 65535);

            $custom['data_center_id'] = $configData['data_center']['id'];

            // 下游不支持的参数
            $DownstreamProductLogic = new DownstreamProductLogic($param['product']);
            if($DownstreamProductLogic->isDownstreamSync){
                $custom['ssh_key_id'] = 0;
                // $custom['security_group_id'] = 0;
                // $custom['security_group_protocol'] = [];
            }
            $data = [
                'host_id'               => $param['host_id'],
                'data_center_id'        => $custom['data_center_id'] ?? 0,
                'image_id'              => $custom['image_id'],
                'backup_num'            => $configData['backup']['num'] ?? 0,
                'snap_num'              => $configData['snap']['num'] ?? 0,
                // 'power_status'          => 'on',
                'password'              => aes_password_encode($custom['password'] ?? ''),
                'config_data'           => json_encode($configData),
                'create_time'           => time(),
                'type'                  => $config['type'],
                'recommend_config_id'   => $custom['recommend_config_id'] ?? 0,
                'default_ipv4'          => 0,
            ];
            if($data['recommend_config_id'] == 0 && $config['default_one_ipv4'] == 1){
                $support_nat = ($config['type'] == 'lightHost' || $configData['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
                if(!$support_nat){
                    $data['default_ipv4'] = 1;
                }
            }

            if(isset($custom['ssh_key_id']) && !empty($custom['ssh_key_id'])){
                $addon = PluginModel::where('name', 'IdcsmartSshKey')->where('module', 'addon')->where('status',1)->find();
                if(!empty($addon)){
                    $sshKey = IdcsmartSshKeyModel::find($custom['ssh_key_id']);
                    if(empty($sshKey) || $sshKey['client_id'] != $clientId){
                        throw new \Exception(lang_plugins('ssh_key_not_found'));
                    }
                    $data['ssh_key_id'] = $custom['ssh_key_id'];
                    $data['password'] = aes_password_encode('');

                    $custom['password'] = '';
                }else{
                    throw new \Exception(lang_plugins('mf_cloud_not_support_ssh_key'));
                }
            }
            // 创建VPC的情况
            if($custom['network_type'] == 'vpc'){
                // 支持转发建站
                if(isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit'])){

                }else{
                    if(isset($custom['vpc']['id']) && !empty($custom['vpc']['id'])){
                        $vpcNetwork = VpcNetworkModel::find($custom['vpc']['id']);
                        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId){
                            throw new \Exception(lang_plugins('vpc_network_not_found'));
                        }
                        if(!$vpcNetwork->checkVpcIsEnable($vpcNetwork, $param['product']['id'], $custom['data_center_id'])){
                            throw new \Exception(lang_plugins('vpc_network_not_enable'));
                        }
                        $data['vpc_network_id'] = $custom['vpc']['id'];
                    }else{
                        $VpcNetworkModel = new VpcNetworkModel();
                        $vpcCreateRes = $VpcNetworkModel->vpcNetworkCreateNew([
                            'id'                => $param['product']['id'],
                            'data_center_id'    => $custom['data_center_id'],
                            'name'              => 'VPC-'.rand_str(8),
                            'ips'               => isset($custom['vpc']['ips']) && !empty($custom['vpc']['ips']) ? $custom['vpc']['ips'] : '10.0.0.0/16',
                        ], $clientId);
                        if($vpcCreateRes['status'] != 200){
                            throw new \Exception($vpcCreateRes['msg']);
                        }

                        $data['vpc_network_id'] = $vpcCreateRes['data']['id'];
                    }
                }
            }
            $res = $this->where('host_id', $param['host_id'])->find();
            if(empty($res)){
                $this->create($data);
            }else{
                $this->update($data, ['host_id'=>$param['host_id']]);
            }
            $hostData = [
                'client_notes'  => $custom['notes'] ?? '',
                'base_info'     => $this->formatBaseInfo($configData),
            ];
            HostModel::where('id', $param['host_id'])->update($hostData);
            $addon = PluginModel::where('name', 'IdcsmartCloud')->where('module', 'addon')->where('status',1)->find();
            if(!empty($addon)){
                $linkSecurity = false;
                if(isset($custom['security_group_id']) && !empty($custom['security_group_id'])){
                    $securityGroup = IdcsmartSecurityGroupModel::find($custom['security_group_id']);
                    if(empty($securityGroup) || $securityGroup['client_id'] != $clientId){
                        throw new \Exception(lang_plugins('mf_cloud_security_group_not_found'));
                    }
                    $linkSecurity = true;
                }else if(isset($custom['security_group_protocol']) && !empty($custom['security_group_protocol'])){
                    // 传了安全组规则过来
                    $securityGroup = IdcsmartSecurityGroupModel::create([
                        'client_id'     => $clientId,
                        'type'          => 'host',
                        'name'          => 'security-'.rand_str(),
                        'create_time'   => $time,
                    ]);

                    // 新协议方式
                    $newSecurityProtocol = false;
                    foreach($custom['security_group_protocol'] as $v){
                        if(strpos($v, 'id_') === 0){
                            $newSecurityProtocol = true;
                        }
                    }

                    $protocol = [];
                    if($newSecurityProtocol){
                        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
                        $protocolList = $SecurityGroupConfigModel->getConfigList($param['product']['id'], true);
                        $protocolList = $protocolList['list'] ?? [];

                        foreach($protocolList as $v){
                            $protocolKey = 'id_'.$v['id'];
                            $protocol[ $protocolKey ] = [
                                'port'          => $v['port'],
                                'description'   => $v['description'],
                                'direction'     => $v['direction'],
                                'protocol'      => $v['protocol'],
                            ];
                        }
                    }else{
                        // 兼容原来的协议
                        $protocol = [
                            'icmp' => [
                                'port'          => '1-65535',
                                'description'   => lang_plugins('mf_cloud_ping_service_release'),
                                'direction'     => 'in',
                            ],
                            'ssh' => [
                                'port'          => '22',
                                'description'   => lang_plugins('mf_cloud_release_linux_ssh_login'),
                                'direction'     => 'in',
                            ],
                            'telnet' => [
                                'port'          => '23',
                                'description'   => lang_plugins('mf_cloud_release_service_telnet'),
                                'direction'     => 'in',
                            ],
                            'http' => [
                                'port'          => '80',
                                'description'   => lang_plugins('mf_cloud_release_http_protocol'),
                                'direction'     => 'in',
                            ],
                            'https' => [
                                'port'          => '443',
                                'description'   => lang_plugins('mf_cloud_release_https_protocol'),
                                'direction'     => 'in',
                            ],
                            'mssql' => [
                                'port'          => '1433',
                                'description'   => lang_plugins('mf_cloud_release_service_mssql'),
                                'direction'     => 'in',
                            ],
                            'oracle' => [
                                'port'          => '1521',
                                'description'   => lang_plugins('mf_cloud_release_service_oracle'),
                                'direction'     => 'in',
                            ],
                            'mysql' => [
                                'port'          => '3306',
                                'description'   => lang_plugins('mf_cloud_release_service_mysql'),
                                'direction'     => 'in',
                            ],
                            'rdp' => [
                                'port'          => '3389',
                                'description'   => lang_plugins('mf_cloud_release_service_windows'),
                                'direction'     => 'in',
                            ],
                            'postgresql' => [
                                'port'          => '5432',
                                'description'   => lang_plugins('mf_cloud_release_service_postgresql'),
                                'direction'     => 'in',
                            ],
                            'redis' => [
                                'port'          => '6379',
                                'description'   => lang_plugins('mf_cloud_release_service_redis'),
                                'direction'     => 'in',
                            ],
                            'udp_53'=>[
                                'port'          => '53',
                                'description'   => lang_plugins('mf_cloud_release_service_dns'),
                                'direction'     => 'in',
                                'protocol'      => 'udp',
                            ],
                        ];
                    }
                    // 默认支持规则
                    $protocol['remote_port'] = [
                        'port'          => $port,
                        'description'   => lang_plugins('mf_cloud_release_remote_port', ['{port}'=>$port]),
                        'direction'     => 'in',
                        'protocol'      => 'tcp',
                    ];
                    $protocol['all'] = [
                        'port'          => '1-65535',
                        'description'   => lang_plugins('mf_cloud_release_all_out_traffic'),
                        'direction'     => 'out',
                    ];

                    $custom['security_group_protocol'] = array_unique($custom['security_group_protocol']);
                    if(!in_array('all', $custom['security_group_protocol'])){
                        $custom['security_group_protocol'][] = 'all';
                    }

                    $securityGroupRule = [];

                    foreach($custom['security_group_protocol'] as $v){
                        if(!isset($protocol[$v])){
                            continue;
                        }
                        // 放通远程端口,如果和ssh/rdp重复,就不添加
                        if($v == 'remote_port'){
                            $ConfigurationModel = new ConfigurationModel();
                            $configuration = $ConfigurationModel->systemList();
                            if(empty($configuration['edition'])){
                                continue;
                            }
                            if($port == 22 && in_array('ssh', $custom['security_group_protocol'])){
                                continue;
                            }
                            if($port == 3389 && in_array('rdp', $custom['security_group_protocol'])){
                                continue;
                            }
                        }

                        $securityGroupRule[] = [
                            'addon_idcsmart_security_group_id'      => $securityGroup->id,
                            'description'                           => $protocol[$v]['description'],
                            'direction'                             => $protocol[$v]['direction'],
                            'protocol'                              => $protocol[$v]['protocol'] ?? $v,
                            'port'                                  => $protocol[$v]['port'],
                            'ip'                                    => '0.0.0.0/0',
                            'create_time'                           => $time,
                        ];
                    }

                    if(!empty($securityGroupRule)){
                        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();
                        $IdcsmartSecurityGroupRuleModel->insertAll($securityGroupRule);
                    }
                    $custom['security_group_id'] = (int)$securityGroup->id;
                    $linkSecurity = true;
                }
                if($linkSecurity){
                    // 当前产品是否是下游
                    $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();
                    if($DownstreamProductLogic->isDownstreamSync){
                        $syncSecurityGroupToSupplier = $IdcsmartSecurityGroupHostLinkModel->syncSecurityGroupToSupplier([
                            'security_group_id' => $custom['security_group_id'],
                            'supplier_id'       => $DownstreamProductLogic->supplierId,
                        ]);
                        if($syncSecurityGroupToSupplier['status'] != 200){
                            throw new \Exception( lang_plugins('mf_cloud_sync_security_group_fail') );
                        }
                    }
                    $IdcsmartSecurityGroupHostLinkModel->saveSecurityGroupHostLink($custom['security_group_id'], $hostId);
                }
            }
            // 自动续费
            if(isset($custom['auto_renew']) && $custom['auto_renew'] == 1){
                $enableIdcsmartRenewAddon = PluginModel::where('name', 'IdcsmartRenew')->where('module', 'addon')->where('status',1)->find();
                if($enableIdcsmartRenewAddon && class_exists('addon\idcsmart_renew\model\IdcsmartRenewAutoModel')){
                    IdcsmartRenewAutoModel::where('host_id', $hostId)->delete();
                    IdcsmartRenewAutoModel::create([
                        'host_id' => $hostId,
                        'status'  => 1,
                    ]);
                }
            }
            // 下单的时候保存配置到附加表
            if(class_exists('app\common\model\HostAdditionModel')){
                $image = ImageModel::alias('i')
                    ->field('i.id,i.name,i.image_group_id,ig.name image_group_name,ig.icon')
                    ->leftJoin('module_mf_cloud_image_group ig', 'i.image_group_id=ig.id')
                    ->where('i.id', $data['image_id'])
                    ->find();

                $ImageGroupModel = new ImageGroupModel();

                $HostAdditionModel = new HostAdditionModel();
                $HostAdditionModel->hostAdditionSave($hostId, [
                    'country_id'    => $configData['data_center']['country_id'],
                    'city'          => $configData['data_center']['city'],
                    'area'          => $configData['data_center']['area'],
                    'image_icon'    => $image['icon'] ?? '',
                    'image_name'    => $image['name'] ?? '',
                    'username'      => $ImageGroupModel->isWindows($image) ? 'administrator' : 'root',
                    'password'      => $custom['password'] ?? '',
                    'port'          => $port,
                ]);
            }
            // 磁盘内容
            $dataDisk = [];
            // 系统盘
            $dataDisk[] = [
                'name'          => '系统盘',
                'size'          => $configData['system_disk']['value'],
                'host_id'       => $hostId,
                'type'          => $configData['system_disk']['other_config']['disk_type'] ?? '',
                'price'         => $configData['system_disk']['price'] ?? '0.00',
                'create_time'   => time(),
                'is_free'       => 1,
                'status'        => 3,
                'type2'         => 'system',
                'free_size'     => 0,
            ];
            // if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0){
            //     $dataDisk[] = [
            //         'name'          => '免费盘',
            //         'size'          => $config['free_disk_size'],
            //         'host_id'       => $hostId,
            //         'type'          => '',
            //         'price'         => '0.00',
            //         'create_time'   => time(),
            //         'is_free'       => 1,
            //         'status'        => 3,
            //         'type2'         => 'data',
            //     ];
            // }
            // 结算后就先添加数据
            if(isset($configData['data_disk'])){
                foreach($configData['data_disk'] as $k=>$v){
                    $dataDisk[] = [
                        'name'          => lang_plugins('mf_cloud_disk') . rand_str(8, 'NUMBER'),
                        'size'          => $v['value'],
                        'host_id'       => $hostId,
                        'type'          => $v['other_config']['disk_type'] ?? '',
                        'price'         => $v['price'] ?? '0.00',
                        'create_time'   => time(),
                        'is_free'       => $v['is_free'] ?? 0,
                        'status'        => 3,
                        'type2'         => 'data',
                        'free_size'     => !empty($v['free_size']) ? $v['free_size'] : 0,
                    ];
                }
            }
            if(!empty($dataDisk)){
                $DiskModel = new DiskModel();
                $DiskModel->insertAll($dataDisk);
            }
        }
    }

    /**
     * 时间 2023-02-20
     * @title 获取当前配置所有周期价格
     * @desc 获取当前配置所有周期价格
     * @author hh
     * @version v1
     */
    public function durationPrice($param)
    {
        $HostModel = new HostModel();
        $host_id = $param['host']['id'];
        $host = $HostModel->find($host_id);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $productId = $host['product_id'];

        $product = ProductModel::find($productId);

        // TODO wyh 20231219 续费使用比例
        $DurationRatioModel = new DurationRatioModel();
        $ratios = $DurationRatioModel->indexRatio($productId);
        if (empty($ratios)){
            return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>[]];
        }else{
            // 试用，续费后更新此字段
            if ($host['is_ontrial']){
                $DurationModel = new DurationModel();
                $baseConfigOptions = json_decode($host['base_config_options'],true);
                $baseConfigOptions['id'] = $host['product_id'];
                $baseConfigOptions['is_ontrial'] = 1;
                $result = $DurationModel->getAllDurationPrice($baseConfigOptions);
                if ($result['status']!=200){
                    return $result;
                }
                $duration = [];
                foreach ($result['data'] as $item){
                    if ($item['id']!=config('idcsmart.pay_ontrial')){
                        $cycleTime = strtotime('+ '.$item['num'].' '.$item['unit'], $host['due_time']) - $host['due_time'];
                        $duration[] = [
                            'id' => $item['id'],
                            'duration' => $cycleTime,
                            'price' => $item['price'],
                            'billing_cycle' => $item['name'],
                            'name_show' => $item['name_show'],
                            'base_price' => $item['price'],
                            'prr' => 1
                        ];
                    }
                }
                $result = [
                    'status'=>200,
                    'msg'=>lang_plugins('success_message'),
                    'data'=>$duration
                ];
                return $result;
            }else if($host['billing_cycle'] == 'on_demand'){
                $duration = [];

                // 如果产品是流量计费,则不支持
                $hostLink = $this
                            ->where('host_id', $host['id'])
                            ->find();
                if(empty($hostLink['rel_id'])){
                    $result = [
                        'status'=>200,
                        'msg'=>lang_plugins('success_message'),
                        'data'=>$duration
                    ];
                    return $result;
                }
                $configData = json_decode($hostLink['config_data'], true);
                if(empty($configData['line']) || $configData['line']['bill_type'] == 'flow'){
                    $result = [
                        'status'=>200,
                        'msg'=>lang_plugins('success_message'),
                        'data'=>$duration
                    ];
                    return $result;
                }
                // 按需,返回转包年包月的金额,按周期比例,不用了
                // $productOnDemand = ProductOnDemandModel::getProductOnDemand($productId);
                // if(!empty($productOnDemand['duration_id']) && $productOnDemand['duration_ratio'] > 0){
                //     $basePrice = NULL;
                //     foreach ($ratios as $ratio){
                //         if($ratio['ratio'] == 0){
                //             continue;
                //         }
                //         if($ratio['id'] == $productOnDemand['duration_id']){
                //             $basePrice = bcdiv(bcmul($host['base_renew_amount'], $productOnDemand['duration_ratio'], 4), $ratio['ratio'], 4);
                //         }
                //     }
                //     if(is_numeric($basePrice)){
                //         $time = time();
                //         foreach ($ratios as $ratio){
                //             if($ratio['ratio'] == 0){
                //                 continue;
                //             }
                //             $cycleTime = strtotime('+ '.$ratio['num'].' '.$ratio['unit'], $time) - $time;
                            
                //             $price = bcmul(bcmul($basePrice, $ratio['ratio'], 4), $ratio['price_factor'], 2);
                //             $price = amount_format($price);
                            
                //             $duration[] = [
                //                 'id'        => $ratio['id'],
                //                 'duration'  => $cycleTime,
                //                 'price'     => $price,
                //                 'billing_cycle' => $ratio['name'],
                //                 'name_show'  => multi_language_replace($ratio['name']),
                //                 'base_price' => $price,
                //             ];
                //         }
                //     }
                // }
                // 按需,返回转包年包月的金额,获取当前配置重新计算
                $currentConfig = $this->currentConfig($host['id']);
                if(empty($currentConfig)){
                    $result = [
                        'status'=>200,
                        'msg'=>lang_plugins('success_message'),
                        'data'=>$duration
                    ];
                    return $result;
                }
                // 获取周期价格
                $currentConfig['id'] = $host['product_id'];
                $currentConfig['billing_cycle'] = 'recurring_prepayment';
                $currentConfig['client_id'] = $host['client_id'];
                // 排除镜像价格
                unset($currentConfig['image_id']);

                $DurationModel = new DurationModel();
                $res = $DurationModel->getAllDurationPrice($currentConfig, true);
                if($res['status'] != 200){
                    $result = [
                        'status'=>200,
                        'msg'=>lang_plugins('success_message'),
                        'data'=>$duration
                    ];
                    return $result;
                }
                $time = time();
                foreach($res['data'] as $v){
                    if(empty($v['price'])){
                        continue;
                    }
                    $cycleTime = strtotime('+ '.$v['num'].' '.$v['unit'], $time) - $time;
                    
                    $duration[] = [
                        'id'        => $v['id'],
                        'duration'  => $cycleTime,
                        'price'     => $v['price'],
                        'billing_cycle' => $v['name'],
                        'name_show'  => multi_language_replace($v['name']),
                        'base_price' => $v['price'],
                        'discount_order_price'  => $v['discount_order_price'] ?? '0.0000',
                        'discount_renew_price'  => $v['discount_renew_price'] ?? '0.0000',
                    ];
                }
                $result = [
                    'status'=>200,
                    'msg'=>lang_plugins('success_message'),
                    'data'=>$duration
                ];
                return $result;
            }else{
                $duration = [];
                $currentDurationRatio = 0; // 当前周期比例
                $currentDurationPriceFactor = 0; // 价格系数
                foreach ($ratios as &$ratio){
                    $durationName = $ratio['name'];
                    if(app('http')->getName() == 'home'){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $ratio['name'],
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $durationName = $multiLanguage['name'];
                        }
                    }
                    $cycleTime = strtotime('+ '.$ratio['num'].' '.$ratio['unit'], $param['host']['due_time']) - $param['host']['due_time'];

                    if ($host['billing_cycle_time']==$cycleTime || $host['billing_cycle_name']==$ratio['name']){
                        $currentDurationRatio = $ratio['ratio'];
                        $currentDurationPriceFactor = $ratio['price_factor'];
                    }
                    $ratio['duration'] = $cycleTime;
                    $ratio['price'] = 0;
                    $ratio['billing_cycle'] = $ratio['name'];
                    $ratio['name_show'] = $durationName;
                    
                    // 自然月预付费：计算精确到期时间和实际周期时长
                    // if(!empty($product['natural_month_prepaid']) && $ratio['unit'] == 'month'){
                    //     $ratio['is_natural_month_prepaid'] = 1;
                    //     // 基于当前到期时间计算下一个自然月到期时间
                    //     $ratio['due_time'] = calculate_natural_month_due_time($param['host']['due_time'], $ratio['num']);
                    //     // 计算实际周期时长（用于价格折算）
                    //     $ratio['actual_cycle_time'] = $ratio['due_time'] - $param['host']['due_time'];
                    // }
                }
                // 计算当前折扣
                if($host['renew_use_current_client_level'] == 1){
                    $currentDiscount = '0.00';
                    $hookDiscountResults = hook('client_discount_by_amount', [
                        'client_id'		=> $host['client_id'],
                        'product_id'	=> $host['product_id'],
                        'amount'		=> $host['discount_renew_price'],
                        'scale'			=> 4,
                    ]);
                    foreach ($hookDiscountResults as $hookDiscountResult){
                        if ($hookDiscountResult['status'] == 200){
                            $currentDiscount = $hookDiscountResult['data']['discount'] ?? 0;
                        }
                    }
                }

                // 产品当前周期比例>0
                if ($currentDurationRatio>0){
                    foreach ($ratios as $ratio2){
                        // 周期比例>0
                        if ($ratio2['ratio']>0){
                            $priceFactorRatio = $ratio2['price_factor']/$currentDurationPriceFactor;
                            $price = bcmul(1,round($host['base_price']*$priceFactorRatio*$ratio2['ratio']/$currentDurationRatio,2),2);

                            $basePrice = $price; // 保存完整周期价格
                            $clientLevelDiscount = 0;
                            // $renewClientLevelDiscount = 0;
                            
                            // 普通周期：不需要折算
                            if($host['renew_use_current_client_level'] == 1){
                                $clientLevelDiscount = bcmul(1,round($currentDiscount*$priceFactorRatio*$ratio2['ratio']/$currentDurationRatio,2),2);
                                // $renewClientLevelDiscount = $clientLevelDiscount;
                            }

                            $durationItem = [
                                'id' => $ratio2['id'],
                                'duration' => $ratio2['duration'],
                                'price' => $price,
                                'billing_cycle' => $ratio2['billing_cycle'],
                                'name_show' => $ratio2['name_show'],
                                'base_price' => $basePrice, // 完整周期价格
                                'prr' => $ratio2['ratio']/$currentDurationRatio,
                                'prr_numerator' => $ratio2['ratio'],
                                'prr_denominator' => $currentDurationRatio,
                                // 'renew_client_level_discount' => $renewClientLevelDiscount,
                            ];
                            
                            // 普通周期：discount_renew_price
                            if($host['renew_use_current_client_level'] == 1){
                                $durationItem['client_level_discount'] = $clientLevelDiscount;
                                $durationItem['discount_renew_price'] = bcmul(1,round($host['discount_renew_price']*$priceFactorRatio*$ratio2['ratio']/$currentDurationRatio,2),2);
                            }
                            $duration[] = $durationItem;
                        }
                    }
                }

                $result = [
                    'status'=>200,
                    'msg'=>lang_plugins('success_message'),
                    'data'=>$duration
                ];
                return $result;
            }
        }
    }

    /**
     * 时间 2023-02-09
     * @title 获取商品最低价格周期
     * @desc 获取商品最低价格周期
     * @author hh
     * @version v1
     */
    public function getPriceCycle($productId)
    {
        $ProductModel = ProductModel::find($productId);
        if(empty($ProductModel)){
            return false;
        }
        bcscale(2);

        $price = null;
        $cycle = '';
        if($ProductModel['pay_type'] == 'free'){
            $price = 0;
        }else if($ProductModel['pay_type'] == 'onetime'){
            $price = 0;
        }else{
            // 默认配置计算
            $DataCenterModel = new DataCenterModel();
            $orderPage = $DataCenterModel->orderPage([
                'product_id' => $productId,
                'scene'      => 'recommend',
            ]);
            // 获取镜像
            $ImageModel = new ImageModel();
            $homeImageList = $ImageModel->homeImageList([
                'product_id' => $productId,
            ]);
            $imageId = $homeImageList['data']['list'][0]['image'][0]['id'] ?? 0; // 默认操作系统

            // 有套餐计算套餐
            if(isset($orderPage['data_center'][0]['city'][0]['area'][0]['recommend_config'][0])){
                $dataCenterId = $orderPage['data_center'][0]['city'][0]['area'][0]['id'] ?? 0;
                $lineId = $orderPage['data_center'][0]['city'][0]['area'][0]['line'][0]['id'] ?? 0;
                $recommendConfigId = $orderPage['data_center'][0]['city'][0]['area'][0]['recommend_config'][0]['id'] ?? 0;

                $LineModel = new LineModel();
                $homeLineConfig = $LineModel->homeLineConfig($lineId);

                $DurationModel = new DurationModel();
                $duration = $DurationModel->getAllDurationPrice([
                    'id'                    => $productId,
                    'data_center_id'        => $dataCenterId,
                    'line_id'               => $lineId,
                    'recommend_config_id'   => $recommendConfigId,
                    'image_id'              => $imageId,
                    'peak_defence'          => $homeLineConfig['order_default_defence'] ?? '',
                    'set_price'             => 1,
                ]);

                if($duration['status'] == 200 && isset($duration['data'])){
                    foreach($duration['data'] as $v){
                        $price = $v['price'];
                        $cycle = $v['name_show'] ?? $v['name'];
                        break;
                    }
                }
            }else{
                $config = ConfigModel::where('product_id', $productId)->find();
                if(empty($config['only_sale_recommend_config'])){
                    $orderPage = $DataCenterModel->orderPage([
                        'product_id' => $productId,
                        'scene'      => 'custom',
                    ]);

                    // 购买参数
                    $buyParam = [
                        'id'             => $productId,
                        'data_center_id' => $orderPage['data_center'][0]['city'][0]['area'][0]['id'] ?? 0,
                        'cpu'            => $orderPage['cpu'][0]['value'] ?? 0,
                        'memory'         => 0,
                        'image_id'       => $imageId,
                        'system_disk'    => [
                            'size'       => 0,
                            'disk_type'  => '',
                        ],
                        'line_id'        => $orderPage['data_center'][0]['city'][0]['area'][0]['line'][0]['id'] ?? 0,
                        'line_type'      => $orderPage['data_center'][0]['city'][0]['area'][0]['line'][0]['bill_type'] ?? '',
                        'bw'             => 0,
                        'flow'           => '',
                        'peak_defence'   => '',
                        'ip_num'         => '',
                        'gpu_num'        => $orderPage['data_center'][0]['city'][0]['area'][0]['gpu'][0]['value'] ?? 0,
                        'ipv6_num'       => '',
                    ];
                    // 当无限制时取配置项默认值
                    if(isset($orderPage['memory'][0])){
                        if($orderPage['memory'][0]['type'] == 'radio'){
                            $buyParam['memory'] = $orderPage['memory'][0]['value'];
                        }else{
                            $buyParam['memory'] = $orderPage['memory'][0]['min_value'];
                        }
                    }
                    if(isset($orderPage['system_disk'][0])){
                        if($orderPage['system_disk'][0]['type'] == 'radio'){
                            $buyParam['system_disk'] = [
                                'size'      => $orderPage['system_disk'][0]['value'],
                                'disk_type' => $orderPage['system_disk'][0]['other_config']['disk_type'] ?? '',
                            ];
                        }else{
                            $buyParam['system_disk'] = [
                                'size'      => $orderPage['system_disk'][0]['min_value'],
                                'disk_type' => $orderPage['system_disk'][0]['other_config']['disk_type'] ?? '',
                            ];
                        }
                    }
                    if(!empty($buyParam['line_id'])){
                        // 获取线路详情
                        $LineModel = new LineModel();
                        $homeLineConfig = $LineModel->homeLineConfig($buyParam['line_id']);

                        $buyParam['line_type'] = $homeLineConfig['bill_type'];

                        if($homeLineConfig['bill_type'] == 'bw'){
                            // 获取带宽
                            foreach($homeLineConfig['bw'] as $v){
                                if($v['type'] == 'radio'){
                                    $buyParam['bw'] = $v['value'];
                                }else{
                                    $buyParam['bw'] = $v['min_value'];
                                }
                                break;
                            }
                        }else{
                            foreach($homeLineConfig['flow'] as $v){
                                $buyParam['flow'] = $v['value'];
                                $buyParam['bw'] = $v['bw'][0]['out_bw'] ?? $v['other_config']['out_bw'];
                                break;
                            }
                        }

                        // 其他配置
                        if(!empty($homeLineConfig['order_default_defence'])){
                            $buyParam['peak_defence'] = $homeLineConfig['order_default_defence'];
                        }
                        if(isset($homeLineConfig['ip'])){
                            // 获取IP数量
                            foreach($homeLineConfig['ip'] as $item){
                                $item['type'] = $item['type'] ?? 'radio';
                                if($item['type'] == 'radio'){
                                    $buyParam['ip_num'] = $item['value'];
                                }else{
                                    $buyParam['ip_num'] = $item['min_value'];
                                }
                                break;
                            }
                        }
                        if(isset($homeLineConfig['ipv6'])){
                            // 获取IPv6数量
                            foreach($homeLineConfig['ipv6'] as $item){
                                $item['type'] = $item['type'] ?? 'radio';
                                if($item['type'] == 'radio'){
                                    $buyParam['ipv6_num'] = $item['value'];
                                }else{
                                    $buyParam['ipv6_num'] = $item['min_value'];
                                }
                                break;
                            }
                        }
                    }

                    // 匹配限制规则
                    $LimitRuleModel = new LimitRuleModel();
                    $maxBack = max(count($orderPage['limit_rule']), 10);
                    for($i = 0; $i < $maxBack; $i++){
                        $passNum = 0;
                        foreach($orderPage['limit_rule'] as $v){
                            $matchRule = $LimitRuleModel->limitRuleMatch($v['rule'], $buyParam);
                            if($matchRule){
                                $limitResult = $v['result'];
                                // 移动结果
                                $changeParam = false;
                                if(isset($limitResult['cpu'])){
                                    $match = $LimitRuleModel->limitRuleResultMatch(['cpu'=>$limitResult['cpu']], $buyParam, ['cpu']);
                                    // 不可用,在寻找可用的
                                    if(!$match){
                                        foreach($orderPage['cpu'] as $vv){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['cpu'=>$limitResult['cpu']], ['cpu'=>$vv['value']], ['cpu']);
                                            if($match){
                                                $buyParam['cpu'] = $vv['value'];
                                                $changeParam = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if(isset($limitResult['memory'])){
                                    $match = $LimitRuleModel->limitRuleResultMatch(['memory'=>$limitResult['memory']], $buyParam, ['memory']);
                                    // 不可用,在寻找可用的
                                    if(!$match){
                                        foreach($orderPage['memory'] as $vv){
                                            if($vv['type'] == 'radio'){
                                                $match = $LimitRuleModel->limitRuleResultMatch(['memory'=>$limitResult['memory']], ['memory'=>$vv['value']], ['memory']);
                                                if($match){
                                                    $buyParam['memory'] = $vv['value'];
                                                    $changeParam = true;
                                                    break;
                                                }
                                            }else{
                                                $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['memory'], $vv);
                                                if(!is_null($find['min_value'])){
                                                    $buyParam['memory'] = $find['min_value'];
                                                    $changeParam = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if(isset($limitResult['image'])){
                                    $match = $LimitRuleModel->limitRuleResultMatch(['image'=>$limitResult['image']], $buyParam, ['image']);
                                    // 不可用,在寻找可用的
                                    if(!$match){
                                        foreach($homeImageList['data']['list'] as $vv){
                                            foreach($vv['image'] as $vvv){
                                                $match = $LimitRuleModel->limitRuleResultMatch(['image'=>$limitResult['image']], ['image_id'=>$vvv['id']], ['image']);
                                                if($match){
                                                    $buyParam['image_id'] = $vvv['id'];
                                                    $changeParam = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                                if(isset($limitResult['system_disk'])){
                                    $match = $LimitRuleModel->limitRuleResultMatch(['system_disk'=>$limitResult['system_disk']], $buyParam, ['system_disk']);
                                    // 不可用,在寻找可用的
                                    if(!$match){
                                        foreach($orderPage['system_disk'] as $vv){
                                            if($vv['type'] == 'radio'){
                                                $match = $LimitRuleModel->limitRuleResultMatch(['system_disk'=>$limitResult['system_disk']], ['system_disk'=>$vv['value']], ['system_disk']);
                                                if($match){
                                                    $buyParam['system_disk']['size'] = $vv['value'];
                                                    $buyParam['system_disk']['disk_type'] = $vv['other_config']['disk_type'] ?? '';
                                                    $changeParam = true;
                                                    break;
                                                }
                                            }else{
                                                $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['system_disk'], $vv);
                                                if(!is_null($find['min_value'])){
                                                    $buyParam['system_disk']['size'] = $find['min_value'];
                                                    $buyParam['system_disk']['disk_type'] = $vv['other_config']['disk_type'] ?? '';
                                                    $changeParam = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if(!empty($homeLineConfig)){
                                    if($homeLineConfig['bill_type'] == 'bw'){
                                        if(isset($limitResult['bw'])){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['bw'=>$limitResult['bw']], $buyParam, ['bw']);
                                            // 不可用,在寻找可用的
                                            if(!$match){
                                                foreach($homeLineConfig['bw'] as $vv){
                                                    if($vv['type'] == 'radio'){
                                                        $match = $LimitRuleModel->limitRuleResultMatch(['bw'=>$limitResult['bw']], ['bw'=>$vv['value'], 'line_type'=>'bw'], ['bw']);
                                                        if($match){
                                                            $buyParam['bw'] = $vv['value'];
                                                            $buyParam['flow'] = NULL;
                                                            $changeParam = true;
                                                            break;
                                                        }
                                                    }else{
                                                        $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['bw'], $vv);
                                                        if(!is_null($find['min_value'])){
                                                            $buyParam['bw'] = $find['min_value'];
                                                            $buyParam['flow'] = NULL;
                                                            $changeParam = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        if(isset($limitResult['flow'])){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['flow'=>$limitResult['flow']], $buyParam, ['flow']);
                                            // 不可用,在寻找可用的
                                            if(!$match){
                                                foreach($homeLineConfig['flow'] as $vv){
                                                    $match = $LimitRuleModel->limitRuleResultMatch(['flow'=>$limitResult['flow']], ['flow'=>$vv['value'], 'line_type'=>'flow'], ['flow']);
                                                    if($match){
                                                        $buyParam['bw'] = $vv['other_config']['out_bw'];
                                                        $buyParam['flow'] = $vv['value'];
                                                        $changeParam = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if(isset($homeLineConfig['ip'])){
                                        if(isset($limitResult['ipv4_num'])){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['ipv4_num'=>$limitResult['ipv4_num']], $buyParam, ['ipv4_num']);
                                            // 不可用,在寻找可用的
                                            if(!$match){
                                                foreach($homeLineConfig['ip'] as $vv){
                                                    $match = $LimitRuleModel->limitRuleResultMatch(['ipv4_num'=>$limitResult['ipv4_num']], ['ip_num'=>$vv['value']], ['ipv4_num']);
                                                    if($match){
                                                         if($vv['type'] == 'radio'){
                                                            $match = $LimitRuleModel->limitRuleResultMatch(['ipv4_num'=>$limitResult['ipv4_num']], ['ip_num'=>$vv['value']], ['ipv4_num']);
                                                            if($match){
                                                                $buyParam['ip_num'] = $vv['value'];
                                                                $changeParam = true;
                                                                break;
                                                            }
                                                        }else{
                                                            $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['ipv4_num'], $vv);
                                                            if(!is_null($find['min_value'])){
                                                                $buyParam['ip_num'] = $find['min_value'];
                                                                $changeParam = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                if($changeParam){
                                    break;
                                }
                            }
                            $passNum++;
                        }
                        if($passNum == count($orderPage['limit_rule'])){
                            break;
                        }
                    }
                    
                    $DurationModel = new DurationModel();
                    $duration = $DurationModel->getAllDurationPrice($buyParam);
                    if($duration['status'] == 200 && isset($duration['data'])){
                        foreach($duration['data'] as $v){
                            $price = $v['price'];
                            $cycle = $v['name_show'] ?? $v['name'];
                            break;
                        }
                    }
                }
            }
        }
        return ['price'=>is_numeric($price) ? amount_format($price, 2) : $price, 'cycle'=>$cycle, 'product'=>$ProductModel ];
    }

    /**
     * 时间 2024-02-19
     * @title 产品内页模块配置信息输出
     * @desc  产品内页模块配置信息输出
     * @author hh
     * @version v1
     */
    public function adminField($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return [];
        }

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id']]);
        $config = $config['data'];
        
        $configData = !empty($hostLink) ? json_decode($hostLink['config_data'], true) : [];
        $dataCenter = DataCenterModel::find($configData['data_center']['id'] ?? 0);
        if(!empty($dataCenter)){
            $configData['data_center'] = $dataCenter->toArray();
        }
        $syncFirewallRule = 0;
        $line = LineModel::find($configData['line']['id'] ?? 0);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
            $syncFirewallRule = $line['defence_enable'] == 1 ? $line['sync_firewall_rule'] : 0;
        }else{
            $syncFirewallRule = $configData['line']['defence_enable'] == 1 ? ($configData['line']['sync_firewall_rule'] ?? 0) : 0;
        }

        $image = ImageModel::find($hostLink['image_id']);

        // 获取磁盘
        $disk = DiskModel::where('host_id', $param['host']['id'])->where('type2', 'data')->order('is_free', 'desc')->select();

        $DataCenterModel = new DataCenterModel();
        $HostAdditionModel = new HostAdditionModel();
        $hostAddition = HostAdditionModel::where('host_id', $param['host']['id'])->find();

        $in_bw = '';
        $out_bw = '';
        if(isset($configData['bw']['other_config']['in_bw'])){
            $in_bw = $configData['bw']['other_config']['in_bw'] ?: $configData['bw']['value'];
            $out_bw = $configData['bw']['value'];
        }else if(isset($configData['flow'])){
            $in_bw = $configData['flow']['other_config']['in_bw'] ?? 0;
            $out_bw = $configData['flow']['other_config']['out_bw'] ?? 0;
        }
        $data = [];

        if($config['manual_manage']==1 && $this->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();

            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_cloud_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'      => lang_plugins('mf_cloud_manual_resource'),
                        'key'       => 'cloud_manual_resource',
                        'value'     => !empty($manual_resource) ? ($manual_resource['dedicated_ip'].'('.$manual_resource['id'].')') : '',
                        'disable'   => true,
                    ],
                    [
                        'name'  => lang_plugins('mf_cloud_instance_username'),
                        'key'   => 'username',
                        'value' => $hostAddition['username'] ?? '',
                    ],
                    [
                        'name'  => lang_plugins('mf_cloud_instance_password'),
                        'key'   => 'password',
                        'value' => $hostAddition['password'] ?? '',
                    ],
                ],
            ];
        }else{
            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_cloud_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'  => lang_plugins('mf_cloud_id'),
                        'key'   => 'zjmf_cloud_id',
                        'value' => $hostLink['rel_id'],
                        'disable'   => $config['manual_manage']==1 ? true : false,
                    ],
                    [
                        'name'  => lang_plugins('mf_cloud_instance_username'),
                        'key'   => 'username',
                        'value' => $hostAddition['username'] ?? '',
                    ],
                    [
                        'name'  => lang_plugins('mf_cloud_instance_password'),
                        'key'   => 'password',
                        'value' => $hostAddition['password'] ?? '',
                    ],
                ],
            ];
        }
        if(!empty($hostLink['recommend_config_id'])){
            $recommendConfigName = RecommendConfigModel::where('id', $hostLink['recommend_config_id'])->value('name') ?? $configData['recommend_config']['name'];
            array_unshift($data[0]['field'], [
                'name'      => lang_plugins('mf_cloud_recommend_config'),
                'key'       => 'recommend_config',
                'value'     => $recommendConfigName,
                'disable'   => true,
            ]);
        }
        // 实例配置
        $data[] = [
            'name' => lang_plugins('mf_cloud_instance_config'),
            'field'=> [
                [
                    'name'  => 'CPU',
                    'key'   => 'cpu',
                    'value' => $configData['cpu']['value'] ?? '',
                ],
                [
                    'name'  => lang_plugins('memory'),
                    'key'   => 'memory',
                    'value' => $configData['memory']['value'] ?? '',
                ],
                [
                    'name'      => lang_plugins('mf_cloud_option_value_8').'('.lang_plugins('mf_cloud_line_gpu_name').')',
                    'key'       => 'gpu',
                    'value'     => isset($configData['gpu_num']) && $configData['gpu_num'] > 0 ? $configData['gpu_num'].'*'.($configData['gpu_name'] ?? $configData['line']['gpu_name']) : '--',
                    'disable'   => true,
                ],
                [
                    'name'      => lang_plugins('mf_cloud_os'),
                    'key'       => 'image',
                    'value'     => $image['name'] ?? '',
                    'disable'   => true,
                ],
                [
                    'name'      => lang_plugins('system_disk'),
                    'key'       => 'system_disk',
                    'value'     => $configData['system_disk']['value'] ?? '',
                    'disable'   => true,
                ],
            ],
        ];
        foreach($disk as $v){
            if($v['is_free'] == 1){
                $data[1]['field'][] = [
                    'name'      => lang_plugins('mf_cloud_free_disk').'('.$v['name'].')',
                    'key'       => 'disk_'.$v['id'],
                    'value'     => $v['size'],
                    'disable'   => true,
                ];
            }else{
                $data[1]['field'][] = [
                    'name'      => lang_plugins('mf_cloud_disk').'('.$v['name'].')',
                    'key'       => 'disk_'.$v['id'],
                    'value'     => $v['size'],
                ];
            }
        }
        // 快照备份
        if($hostLink['type'] != 'hyperv'){
            $data[1]['field'][] = [
                'name'      => lang_plugins('snap'),
                'key'       => 'snap_num',
                'value'     => $hostLink['snap_num'],
            ];
        }
        $data[1]['field'][] = [
            'name'      => lang_plugins('backup'),
            'key'       => 'backup_num',
            'value'     => $hostLink['backup_num'],
        ];

        // 网络配置
        $data[] = [
            'name' => lang_plugins('mf_cloud_network_config'),
            'field'=> [
                [
                    'name'      => lang_plugins('mf_cloud_recommend_config_network_type'),
                    'key'       => 'network_type',
                    'value'     => lang_plugins('mf_cloud_recommend_config_'.($configData['network_type'] ?? 'normal').'_network'),
                    'disable'   => true,
                ],
                [
                    'name'      => lang_plugins('mf_cloud_line'),
                    'key'       => 'line',
                    'value'     => $configData['line']['name'] ?? '',
                    'disable'   => true,
                ],
            ],
        ];

        // 带宽型
        if(isset($configData['bw'])){
            $data[2]['field'][] = [
                'name'      => lang_plugins('bw'),
                'key'       => 'bw',
                'value'     => $configData['bw']['value'] ?? '',
            ];

            if($hostLink['type'] != 'hyperv'){
                $data[2]['field'][] = [
                    'name'      => lang_plugins('mf_cloud_line_bw_in_bw'),
                    'key'       => 'in_bw',
                    'value'     => $configData['bw']['other_config']['in_bw'] ?? '',
                ];
            }
        }else if(isset($configData['flow'])){
            $data[2]['field'][] = [
                'name'      => lang_plugins('mf_cloud_out_server_bw'),
                'key'       => 'out_bw',
                'value'     => $configData['flow']['other_config']['out_bw'] ?? '',
            ];
            $data[2]['field'][] = [
                'name'      => lang_plugins('mf_cloud_in_server_bw'),
                'key'       => 'in_bw',
                'value'     => $configData['flow']['other_config']['in_bw'] ?? '',
            ];
            $data[2]['field'][] = [
                'name'      => lang_plugins('mf_cloud_option_value_3'),
                'key'       => 'flow',
                'value'     => $configData['flow']['value'] ?? '',
            ];
        }
        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp([
            'host_id'   => $param['host']['id'],
        ]);

        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_cloud_main_ip'),
            'key'   => 'ip',
            'value' => $hostIp['dedicate_ip'],
        ];
        // VPC内网IP
        if($configData['network_type'] == 'vpc'){
            $data[2]['field'][] = [
                'name'  => lang_plugins('VPC内网IP'),
                'key'   => 'vpc_private_ip',
                'value' => $hostLink['vpc_private_ip'],
                'disable'=> true,
            ];
        }

        // 仅经典网络可用
        $supportNat = ($hostLink['type'] == 'lightHost' || $configData['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
        $ipv4Num = 0;
        // 套餐
        if(isset($configData['recommend_config'])){
            $ipv4Num = max($configData['recommend_config']['ip_num'], 0);
        }else{
            // 配置
            $baseIpNum = abs($hostLink['default_ipv4']);
            if($supportNat){
                $baseIpNum = 0;
            }
            $ipv4Num = ($configData['ip']['value'] ?? 0) + $baseIpNum;
        }
        $data[2]['field'][] = [
            'name'      => lang_plugins('mf_cloud_ipv4_num'),
            'key'       => 'ip_num',
            'value'     => $ipv4Num,
            'disable'   => $syncFirewallRule == 1,
            'sync_firewall_rule'   => $configData['line']['sync_firewall_rule'] ?? 0,
        ];
        if($configData['network_type'] == 'normal' && !$supportNat){
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_cloud_ipv6_num'),
                'key'   => 'ipv6_num',
                'value' => $configData['ipv6_num'] ?? '',
            ];
        }
        if($syncFirewallRule != 1){
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_cloud_option_value_4'),
                'key'   => 'defence',
                'value' => $configData['defence']['value'] ?? '',
            ];
        }
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_cloud_port'),
            'key'   => 'port',
            'value' => $hostAddition['port'] ?? '',
        ];
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_cloud_not_free_gpu'),
            'key'   => 'due_not_free_gpu',
            'value' => $configData['due_not_free_gpu'] ?? 0,
            'type'  => 'checkbox',
        ];
        return $data;
    }

    /**
     * 时间 2024-02-19
     * @title 产品保存后
     * @desc 产品保存后
     * @param int param.module_admin_field.cpu - CPU
     * @param int param.module_admin_field.memory - 内存
     * @param int param.module_admin_field.bw - 带宽
     * @param int param.module_admin_field.in_bw - 进带宽
     * @param int param.module_admin_field.out_bw - 出带宽
     * @param int param.module_admin_field.flow - 流量
     * @param int param.module_admin_field.snap_num - 快照数量
     * @param int param.module_admin_field.backup_num - 备份数量
     * @param int param.module_admin_field.defence - 防御峰值
     * @param int param.module_admin_field.ip_num - IP数量
     * @param string param.module_admin_field.ip - 主IP
     * @param int param.module_admin_field.zjmf_cloud_id - 魔方云实例ID
     * @param int param.module_admin_field.disk_[0-9]+ - 对应数据盘大小
     * @param string param.module_admin_field.username - 用户名
     * @param string param.module_admin_field.password - 密码
     * @param int param.module_admin_field.due_not_free_gpu - 不自动释放GPU(0=否,1=是)
     * @throws \Exception
     * @author hh
     * @version v1
     */
    public function hostUpdate($param)
    {
        $hostId = $param['host']['id'];
        $moduleAdminField  = $param['module_admin_field'];

        $hostLink = $this->where('host_id', $param['host']['id'])->find();

        $DownstreamCloudLogic = new DownstreamCloudLogic($param['host']);

        if(!empty($hostLink) && !$DownstreamCloudLogic->isDownstream()){
            $oriAdminField = $this->adminField($param);

            $adminField = [];
            foreach($oriAdminField as $k=>$v){
                foreach($v['field'] as $kk=>$vv){
                    $adminField[ $vv['key'] ] = $vv['value'];
                }
            }
            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($param['host']['id'], [
                'username'  => $moduleAdminField['username'],
                'password'  => $moduleAdminField['password'],
                'port'      => $moduleAdminField['port'],
            ]);

            // $adminField = array_column($adminField, 'value', 'key');

            $configData = json_decode($hostLink['config_data'], true);
            
            $update = [];           // 修改的参数
            $post = [];             // 云配置参数
            $bw = [];               // 带宽参数
            $change = false;        // 是否变更
            $ip_change = false;     // IP数量是否变更
            $ipv6_change = false;   // IPv6数量是否变更
            $disk_change = [];

            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

            if($config['data']['manual_manage']==1 && $this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
                if(!empty($manual_resource)){
                    $ManualResourceModel->where('host_id', $param['host']['id'])->update([
                        'username'  => $moduleAdminField['username'],
                        'password'  => aes_password_encode($moduleAdminField['password']),
                        'port'      => $moduleAdminField['port'],
                    ]);
                }
            }

            $supportNat = ($hostLink['type'] == 'lightHost' || $configData['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
            // 当前实例是否免费携带IP
            $baseIpNum = abs($hostLink['default_ipv4']);
            if($supportNat){
                $baseIpNum = 0;
            }
            if(isset($moduleAdminField['cpu']) && !empty($moduleAdminField['cpu']) && $moduleAdminField['cpu'] != $adminField['cpu']){
                $configData['cpu']['value'] = $moduleAdminField['cpu'];
                if(isset($configData['recommend_config'])){
                    $configData['recommend_config']['cpu'] = $moduleAdminField['cpu'];
                }

                $post['cpu'] = $moduleAdminField['cpu'];
                $change = true;
            }
            if(isset($moduleAdminField['memory']) && !empty($moduleAdminField['memory']) && $moduleAdminField['memory'] != $adminField['memory']){
                $configData['memory']['value'] = $moduleAdminField['memory'];

                if(!empty($hostLink['recommend_config_id'])){
                    $configData['recommend_config']['memory'] = $moduleAdminField['memory'];

                    $post['memory'] = $moduleAdminField['memory']*1024;
                }else{
                    // 获取单位
                    $memoryUnit = ConfigModel::where('product_id', $param['product']['id'])->value('memory_unit') ?? 'GB';
                    if($memoryUnit == 'MB'){
                        $post['memory'] = $moduleAdminField['memory'];
                    }else{
                        $post['memory'] = $moduleAdminField['memory']*1024;
                    }
                }
                $change = true;
            }
            // 带宽型
            if(isset($configData['bw'])){
                if(isset($moduleAdminField['bw']) && is_numeric($moduleAdminField['bw']) && $moduleAdminField['bw'] != $adminField['bw']){
                    $configData['bw']['value'] = $moduleAdminField['bw'];
                    if(isset($configData['recommend_config'])){
                        $configData['recommend_config']['bw'] = $moduleAdminField['bw'];
                    }

                    $bw['in_bw'] = $moduleAdminField['bw'];
                    $bw['out_bw'] = $moduleAdminField['bw'];
                    $change = true;
                }
                if($hostLink['type'] != 'hyperv'){
                    if(isset($moduleAdminField['in_bw'])){
                        if($moduleAdminField['in_bw'] != $adminField['in_bw']){
                            $configData['bw']['other_config']['in_bw'] = $moduleAdminField['in_bw'];

                            // 使用带宽参数
                            if($moduleAdminField['in_bw'] === '' && is_numeric($adminField['in_bw'])){
                                if($configData['bw']['value'] != $adminField['in_bw']){
                                    $bw['in_bw'] = $configData['bw']['value'];
                                }
                            }else{
                                $bw['in_bw'] = $moduleAdminField['in_bw'];
                            }
                            $change = true;
                        }else{
                            if(is_numeric($adminField['in_bw'])){
                                $bw['in_bw'] = $adminField['in_bw'];
                            }
                        }
                    }
                }
            }else if(isset($configData['flow'])){
                // 流量型
                if(isset($moduleAdminField['flow']) && $moduleAdminField['flow'] != $adminField['flow']){
                    $configData['flow']['value'] = $moduleAdminField['flow'];
                    if(isset($configData['recommend_config'])){
                        $configData['recommend_config']['flow'] = $moduleAdminField['flow'];
                    }

                    $post['traffic_quota'] = (int)$moduleAdminField['flow'];
                    $change = true;
                }
                if(isset($moduleAdminField['in_bw']) && is_numeric($moduleAdminField['in_bw']) && $moduleAdminField['in_bw'] != $adminField['in_bw']){
                    $configData['flow']['other_config']['in_bw'] = $moduleAdminField['in_bw'];

                    $bw['in_bw'] = $moduleAdminField['in_bw'];
                    $change = true;
                }
                if(isset($moduleAdminField['out_bw']) && is_numeric($moduleAdminField['out_bw']) && $moduleAdminField['out_bw'] != $adminField['out_bw']){
                    $configData['flow']['other_config']['out_bw'] = $moduleAdminField['out_bw'];

                    $bw['out_bw'] = $moduleAdminField['out_bw'];
                    $change = true;
                }
            }
            // 备份快照
            if(isset($moduleAdminField['snap_num']) && is_numeric($moduleAdminField['snap_num']) && $moduleAdminField['snap_num'] >= 0 && $moduleAdminField['snap_num'] != $adminField['snap_num']){
                $update['snap_num'] = $moduleAdminField['snap_num'];
                if($update['snap_num'] < 1){
                    $post['snap_num'] = -1;
                }else{
                    $post['snap_num'] = $moduleAdminField['snap_num'];
                }
            }
            if(isset($moduleAdminField['backup_num']) && is_numeric($moduleAdminField['backup_num']) && $moduleAdminField['backup_num'] >= 0 && $moduleAdminField['backup_num'] != $adminField['backup_num']){
                $update['backup_num'] = $moduleAdminField['backup_num'];
                if($update['backup_num'] < 1){
                    $post['backup_num'] = -1;
                }else{
                    $post['backup_num'] = $moduleAdminField['backup_num'];
                }
            }
            if(isset($moduleAdminField['defence']) && $moduleAdminField['defence'] != $adminField['defence']){
                if(!isset($configData['defence'])){
                    $configData['defence'] = [
                        'value' => 0,
                        'price' => 0,
                    ];
                }
                $configData['defence']['value'] = (int)$moduleAdminField['defence'];

                $change = true;
            }
            if(isset($moduleAdminField['ip_num']) && $moduleAdminField['ip_num'] != $adminField['ip_num']){
                if(!isset($configData['ip_num'])){
                    $configData['ip_num'] = [
                        'value' => 0,
                        'price' => 0,
                    ];
                }
                $configData['ip']['value'] = max((int)$moduleAdminField['ip_num'] - $baseIpNum, 0);
                if(isset($configData['recommend_config'])){
                    $configData['recommend_config']['ip_num'] = $moduleAdminField['ip_num'];
                }

                $change = true;
                $ip_change = true;
            }
            if(isset($moduleAdminField['ip']) && $moduleAdminField['ip'] != $adminField['ip']){
                $update['ip'] = $moduleAdminField['ip'];
            }
            if(isset($moduleAdminField['ipv6_num']) && $moduleAdminField['ipv6_num'] != $adminField['ipv6_num']){
                if(!isset($configData['ipv6_num'])){
                    $configData['ipv6_num'] = 0;
                }
                $configData['ipv6_num'] = (int)$moduleAdminField['ipv6_num'];
                
                $change = true;
                $ipv6_change = true;
            }
            if(isset($moduleAdminField['due_not_free_gpu']) && $moduleAdminField['due_not_free_gpu'] != $adminField['due_not_free_gpu']){
                $configData['due_not_free_gpu'] = (int)$moduleAdminField['due_not_free_gpu'];
                $change = true;
            }

            $IdcsmartCloud = new IdcsmartCloud($param['server']);

            if(isset($adminField['zjmf_cloud_id']) && isset($moduleAdminField['zjmf_cloud_id']) && is_numeric($moduleAdminField['zjmf_cloud_id']) && $adminField['zjmf_cloud_id'] != $moduleAdminField['zjmf_cloud_id']){
                $update['rel_id'] = (int)$moduleAdminField['zjmf_cloud_id'];
                $update['migrate_task_id'] = 0;

                $hostLink['rel_id'] = $update['rel_id'];

                if(!empty($update['rel_id'])){
                    $cloudDetail = $IdcsmartCloud->cloudDetail($update['rel_id']);
                    if($cloudDetail['status'] == 200){
                        // $update['password'] = aes_password_encode($cloudDetail['data']['rootpassword']);
                        $update['type'] = $cloudDetail['data']['type'];
                        // 是否有转发/建站
                        if($cloudDetail['data']['nat_acl_limit'] > 0){
                            $configData['nat_acl_limit'] = $cloudDetail['data']['nat_acl_limit'];
                        }else{
                            if(isset($configData['nat_acl_limit'])){
                                unset($configData['nat_acl_limit']);
                            }
                        }
                        if($cloudDetail['data']['nat_web_limit'] > 0){
                            $configData['nat_web_limit'] = $cloudDetail['data']['nat_web_limit'];
                        }else{
                            if(isset($configData['nat_web_limit'])){
                                unset($configData['nat_web_limit']);
                            }
                        }
                    }else{
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_id_error')];
                    }
                }
            }
            if($change){
                $update['config_data'] = json_encode($configData);
            }
            if(isset($update['ip'])){
                $HostIpModel = new HostIpModel();
                $hostIp = $HostIpModel->getHostIp([
                    'host_id'   => $hostId,
                ]);

                $HostIpModel->hostIpSave([
                    'host_id'       => $hostId,
                    'dedicate_ip'   => $update['ip'],
                    'assign_ip'     => $hostIp['assign_ip'],
                ]);
                // unset($update['ip']);
            }
            if(!empty($update)){
                HostLinkModel::update($update, ['host_id'=>$hostId]);
            }
            HostModel::where('id', $hostId)->update([
                'base_info'     => $this->formatBaseInfo($configData),
            ]);
            
            // 如果实例ID修改了,磁盘不能扩容
            if(!isset($update['rel_id'])){
                foreach($moduleAdminField as $k=>$v){
                    if(strpos($k, 'disk_') === 0){
                        $disk = DiskModel::where('host_id', $hostId)->where('id', str_replace('disk_', '', $k))->find();
                        if(!empty($disk) && is_numeric($v) && $v > $disk['size'] && $disk['is_free'] == 0){
                            $disk_change[] = [
                                'id'        => $disk['id'],
                                'name'      => $disk['name'],
                                'rel_id'    => $disk['rel_id'],
                                'new_size'  => $v,
                                'old_size'  => $disk['size'],
                            ];
                        }
                    }
                }
            }
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                return ['status'=>200, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            
            $detail = '';

            $autoBoot = false;
            if(isset($post['cpu']) || isset($post['memory']) || $ip_change || $ipv6_change || !empty($disk_change)){
                $status = $IdcsmartCloud->cloudStatus($id);
                if($status['status'] == 200){
                    // 关机
                    if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
                        $this->safeCloudOff($IdcsmartCloud, $id);
                        $autoBoot = true;
                    }
                }
            }
            if(!empty($bw)){
                $res = $IdcsmartCloud->cloudModifyBw($id, $bw);
                if($res['status'] != 200){
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_bw_fail').$res['msg'];
                }else{
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_bw_success');
                }
            }
            if(!empty($post)){
                $res = $IdcsmartCloud->cloudModify($id, $post);
                if($res['status'] != 200){
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_common_config_fail').$res['msg'];
                }else{
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_common_config_success');
                }
            }

            $ipGroup = 0;
            $ipv6GroupId = 0;
            // 获取下线路信息
            $line = LineModel::find($configData['line']['id']);
            if(!empty($line)){
                $ipv6GroupId = $line['ipv6_group_id'];
                if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value'])){
                    $ipGroup = $line['defence_ip_group'];
                }else if($line['bill_type'] == 'bw'){
                    $ipGroup = $line['bw_ip_group'];
                }
            }
            if($ip_change){
                $supportNat = ($hostLink['type'] == 'lightHost' || $configData['network_type'] == 'vpc') && (isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit']));
                // 当前实例是否免费携带IP
                $baseIpNum = abs($hostLink['default_ipv4']);
                if($supportNat){
                    $baseIpNum = 0;
                }

                $res = $IdcsmartCloud->cloudModifyIpNum($id, ['num'=>(int)$moduleAdminField['ip_num'], 'ip_group'=>$ipGroup ]);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_ip_num_success', [
                        '{old}' => $adminField['ip_num'],
                        '{new}' => (int)$moduleAdminField['ip_num'],
                    ]);
                }else{
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_ip_num_fail', [
                        '{old}'     => $adminField['ip_num'],
                        '{new}'     => (int)$moduleAdminField['ip_num'],
                        '{reason}'  => $res['msg'],
                    ]);
                }
            }
            if($ipv6_change){
                $res = $IdcsmartCloud->cloudModifyIpv6($id, $moduleAdminField['ipv6_num'], $ipv6GroupId);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_ipv6_num_success', [
                        '{old}' => $adminField['ipv6_num'],
                        '{new}' => (int)$moduleAdminField['ipv6_num'],
                    ]);
                }else{
                    $detail .= ','.lang_plugins('mf_cloud_upgrade_ipv6_num_fail', [
                        '{old}'     => $adminField['ipv6_num'],
                        '{new}'     => (int)$moduleAdminField['ipv6_num'],
                        '{reason}'  => $res['msg'],
                    ]);
                }
            }
            foreach($disk_change as $v){
                $resizeRes = $IdcsmartCloud->diskModify($v['rel_id'], ['size'=>$v['new_size']]);
                if($resizeRes['status'] == 200){
                    DiskModel::where('id', $v['id'])->update(['size'=>$v['new_size'] ]);

                    $detail .= lang_plugins('mf_cloud_modify_disk_size_success', [
                        '{name}' => $v['name'],
                        '{old}'  => $v['old_size'],
                        '{new}'  => $v['new_size'],
                    ]);
                }else{
                    $detail .= lang_plugins('mf_cloud_modify_disk_size_fail', [
                        '{name}'    => $v['name'],
                        '{reason}'  => $resizeRes['msg'],
                    ]);
                }
            }
            if($autoBoot){
                $IdcsmartCloud->cloudOn($id);
            }

            if(!empty($detail)){
                // 不手动修改IP,就自动同步IP
                if(!isset($update['ip'])){
                    $ip = $this->syncIp(['host_id'=>$param['host']['id'], 'id'=>$id], $IdcsmartCloud);
                    if(!empty($ip)){
                        if(!empty($ip['assign_ip']) && !empty($ip['dedicate_ip']) ){
                            $ips = explode(',', $ip['assign_ip']);
                            $ips[] = $ip['dedicate_ip'];
                        }else if(!empty($ip['dedicate_ip'])){
                            $ips = [ $ip['dedicate_ip'] ];
                        }else{
                            $ips = [];
                        }
                        $ips = array_filter($ips, function($val){
                            return filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                        });
                        try {
                            $CloudLogic = new CloudLogic($param['host']['id']);
                            $CloudLogic->ipChange([
                                'ips'	=> $ips,
                            ]);
                        } catch (\Exception $e) {
                            return ['status'=>400, 'msg'=>$e->getMessage()];
                        }
                    }
                }

                $description = lang_plugins('log_mf_cloud_host_update_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{detail}'  => $detail,
                ]);
                active_log($description, 'host', $param['host']['id']);
            }
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-02-19
     * @title 安全关机
     * @desc  安全关机,3次软关机失败后再强制关机
     * @author hh
     * @version v1
     * @param   IdcsmartCloud $IdcsmartCloud - IdcsmartCloud实例 require
     * @param   int $id - 魔方云实例ID require
     * @return  bool
     */
    protected function safeCloudOff($IdcsmartCloud, $id)
    {
        $off = false;
        // 先尝试3次软关机
        for($i = 0; $i<3; $i++){
            $res = $IdcsmartCloud->cloudOff($id);
            if($res['status'] != 200){
                sleep(5);
                continue;
            }
            // 检查任务
            for($j = 0; $j<40; $j++){
                $detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
                if(isset($detail['data']['status'])){
                    if($detail['data']['status'] == 2){
                        $off = true;
                        break 2;
                    }
                    if(!in_array($detail['data']['status'], [0,1])){
                        break;
                    }
                }
                sleep(5);
            }
        }
        if(!$off){
            $res = $IdcsmartCloud->cloudHardOff($id);
            if($res['status'] == 200){
                // 检查任务
                for($i = 0; $i<40; $i++){
                    $detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
                    if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
                        break;
                    }
                    sleep(10);
                }
            }
        }
        return $off;
    }

    /**
     * 时间 2025-05-13
     * @title 获取当前实例配置
     * @desc  获取当前实例配置
     * @author hh
     * @version v1
     * @return  int duration_id - 周期ID
     * @return  int data_center_id - 数据中心ID
     * @return  int line_id - 线路ID
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  int recommend_config_id - 套餐ID
     * @return  int image_id - 操作系统ID
     * @return  int cpu - CPU
     * @return  int memory - 内存
     * @return  int system_disk.size - 系统盘大小
     * @return  string system_disk.disk_type - 系统盘类型
     * @return  int bw - 带宽
     * @return  int flow - 流量
     * @return  int peak_defence - 防御
     * @return  int ip_num - 附加IPv4数量
     * @return  int ipv6_num - 附加IPv6数量
     * @return  int data_disk[].rel_id - 关联云磁盘ID
     * @return  int data_disk[].size - 数据盘大小
     * @return  string data_disk[].disk_type - 数据盘类型
     * @return  string billing_cycle - 计费方式
     */
    public function currentConfig($hostId): array
    {
        $hostLink = $this->where('host_id', $hostId)->find();
        if(empty($hostLink)){
            return [];
        }
        $configData = json_decode($hostLink['config_data'], true);

        $host = HostModel::field('product_id,billing_cycle')
                    ->where('id', $hostId)
                    ->find();
        $productId = $host['product_id'];

        $data = [];
        // 匹配周期
        if(isset($configData['duration']['id'])){
            if(is_numeric($configData['duration']['id'])){
                $durationId = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->value('id') ?? 0;
                $data['duration_id'] = $durationId ?: $configData['duration']['id'];
            }else{
                $data['duration_id'] = $configData['duration']['id'];
            }
        }
        $data['data_center_id'] = $hostLink['data_center_id'];
        $data['line_id'] = $configData['line']['id'] ?? 0;
        $data['network_type'] = $configData['network_type'] ?? 'normal';
        $data['image_id'] = $hostLink['image_id'];
        $data['billing_cycle'] = $host['billing_cycle'];
        // 套餐ID
        if($hostLink['recommend_config_id'] > 0){
            $data['recommend_config_id'] = $hostLink['recommend_config_id'];
        }else{
            $data['cpu'] = $configData['cpu']['value'] ?? 0;
            $data['memory'] = $configData['memory']['value'] ?? 0;

            // 线路
            if(!empty($configData['line'])){
                if($configData['line']['bill_type'] == 'bw'){
                    $data['bw'] = $configData['bw']['value'] ?? 0;
                    $data['flow'] = 0;
                }else{
                    $data['bw'] = $configData['flow']['other_config']['out_bw'] ?? 0;
                    $data['flow'] = $configData['flow']['value'] ?? 0;
                }
            }else{
                $data['bw'] = $configData['bw']['value'] ?? 0;
                $data['flow'] = $configData['flow']['value'] ?? 0;
            }
            $data['peak_defence'] = $configData['defence']['value'] ?? NULL;
            $data['ip_num'] = $configData['ip']['value'] ?? NULL;
            $data['ipv6_num'] = $configData['ipv6_num'] ?? NULL;
            $data['gpu_num'] = $configData['gpu_num'] ?? 0;

            $DiskModel = new DiskModel();
            // 获取系统盘
            $systemDisk = $DiskModel
                    ->where('host_id', $hostId)
                    ->where('type2', 'system')
                    ->find();
            $data['system_disk'] = [
                'size'  => $systemDisk['size'] ?? $configData['system_disk']['size'] ?? 0,
                'disk_type'  => $systemDisk['type'] ?? $configData['system_disk']['other_config']['disk_type'] ?? '',
            ];
            // 获取当前数据盘
            $data['data_disk'] = [];
            $disk = $DiskModel
                ->where('host_id', $hostId)
                ->where('type2', 'data')
                ->order('is_free', 'desc')
                ->select();
            foreach($disk as $v){
                $data['data_disk'][] = [
                    'rel_id'    => $v['rel_id'],
                    'size'      => $v['size'],
                    'disk_type' => $v['type'],
                    'is_free'   => $v['is_free'],
                ];
            }
        }
        $data['backup_num'] = $hostLink['backup_num'];
        $data['snap_num'] = $hostLink['snap_num'];

        return $data;
    }

    /**
     * 时间 2024-06-05
     * @title 同步IP信息
     * @desc  同步IP信息
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @param   int param.id - 魔方云实例ID require
     * @param   IdcsmartCloud IdcsmartCloud - IdcsmartCloud类实例 require
     * @param   array detail 自动获取 IdcsmartCloud类实例cloudDetail返回值
     * @param   bool write_log - 是否写日志
     * @return  array
     * @return  string dedicate_ip - 主IP
     * @return  string assign_ip - 附加IP
     */
    public function syncIp($param, $IdcsmartCloud, $detail = NULL, $write_log = true)
    {
        $hostId = $param['host_id'];
        $id = $param['id'];

        $result = $this->getIp($param, $IdcsmartCloud, $detail);
        if(empty($result)){
            return [];
        }

        if(isset($result['vpc_private_ip'])){
            $this->where('host_id', $hostId)->update([
                'vpc_private_ip' => $result['vpc_private_ip'],
            ]);
        }

        // 保存IP信息
        $HostIpModel = new HostIpModel();
        $HostIpModel->hostIpSave([
            'host_id'       => $hostId,
            'dedicate_ip'   => $result['dedicate_ip'],
            'assign_ip'     => $result['assign_ip'],
            'write_log'     => $write_log,
        ]);

        return ['dedicate_ip'=>$result['dedicate_ip'], 'assign_ip'=>$result['assign_ip'] ];
    }

    /**
     * 时间 2024-06-17
     * @title 获取远程IP
     * @desc  获取远程IP
     * @author hh
     * @version v1
     * @param   int param.host_id - 产品ID require
     * @param   int param.id - 魔方云实例ID require
     * @param   IdcsmartCloud IdcsmartCloud - IdcsmartCloud类实例 require
     * @param   array detail 自动获取 IdcsmartCloud类实例cloudDetail返回值
     * @return  array 为空表示未获取到
     * @return  string dedicate_ip - 主IP
     * @return  string assign_ip - 附加IP
     */
    public function getIp($param, $IdcsmartCloud, $detail = NULL)
    {
        $hostId = $param['host_id'];
        $id = $param['id'];

        $detail = $detail ?? $IdcsmartCloud->cloudDetail($id);
        if(!isset($detail['data'])){
            return [];
        }
        
        // 获取默认转发IP
        $natIp = '';
        if($detail['data']['nat_acl_limit'] >= 0 || $detail['data']['nat_web_limit'] >= 0){
            $natAclList = $IdcsmartCloud->natAclList($id, ['page'=>1, 'per_page'=>1, 'orderby'=>'id', 'sort'=>'asc']);
            if(isset($natAclList['data']['data']) && !empty($natAclList['data']['data'])){
                $natIp = $natAclList['data']['nat_host_ip'] . ':' . $natAclList['data']['data'][0]['ext_port'];
            }
        }

        $assignIp = array_column($detail['data']['ip'], 'ipaddress') ?? [];
        $assignIp = array_filter($assignIp, function($x) use ($detail) {
            return $x != $detail['data']['mainip'];
        });
        if(!empty($natIp)){
            if(empty($detail['data']['mainip'])){
                $detail['data']['mainip'] = $natIp;
            }else{
                // 交换IP
                $assignIp[] = $detail['data']['mainip'];
                $detail['data']['mainip'] = $natIp;

                // 去掉内网IP
                // $assignIp = array_filter($assignIp, function($x){
                //     return filter_var($x, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) !== false;
                // });
                // $assignIp = array_values($assignIp);
            }
        }
        if(isset($detail['data']['ipv6']) && !empty($detail['data']['ipv6'])){
            foreach($detail['data']['ipv6'] as $v){
                if($v['ipv6'] != $detail['data']['mainip']){
                    $assignIp[] = $v['ipv6'];
                }
            }
        }

        $vpcPrivateIP = '';
        if(!empty($detail['data']['network_type']) && $detail['data']['network_type'] == 'vpc'){
            if(!empty($detail['data']['network'][0]['ipaddress'][0]['ipaddress'])){
                $vpcPrivateIP = $detail['data']['network'][0]['ipaddress'][0]['ipaddress'];
            }
        }

        $result = [
            'dedicate_ip'   => $detail['data']['mainip'] ?? '',
            'assign_ip'     => implode(',', $assignIp),
            'vpc_private_ip' => $vpcPrivateIP,
        ];
        return $result;
    }

    /**
     * 时间 2024-06-17
     * @title 同步信息
     * @desc  同步信息
     * @author hh
     * @version v1
     */
    public function syncAccount($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [

            ],
        ];
        // 升级时第一次同步
        if(!empty($hostLink['power_status'])){
            $result['data']['power_status'] = $hostLink['power_status'];
            // 仅同步一次
            $this->where('host_id', $param['host']['id'])->update(['power_status'=>'']);
        }
        $image = ImageModel::alias('i')
                ->field('i.id,i.name,i.image_group_id,ig.name image_group_name,ig.icon')
                ->leftJoin('module_mf_cloud_image_group ig', 'i.image_group_id=ig.id')
                ->where('i.id', $hostLink['image_id'])
                ->find();
        if(!empty($image)){
            $ImageGroupModel = new ImageGroupModel();

            $result['data']['image_icon'] = $image['icon'] ?? '';
            $result['data']['image_name'] = $image['name'];
            $result['data']['username'] = $ImageGroupModel->isWindows($image) ? 'administrator' : 'root';
        }
        $dataCenter = DataCenterModel::find($hostLink['data_center_id']);
        if(empty($dataCenter)){
            $configData = json_decode($hostLink['config_data'], true);
            $dataCenter = $configData['data_center'] ?? [];
        }
        if(!empty($dataCenter)){
            $result['data']['country_id'] = $dataCenter['country_id'];
            $result['data']['city'] = $dataCenter['city'];
            $result['data']['area'] = $dataCenter['area'];
        }

        $configData = json_decode($hostLink['config_data'], true);

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);
        // 手动资源的时候
        if($config['data']['manual_manage']==1){
            if($this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
                if(!empty($manual_resource)){
                    $manual_resource['assigned_ips'] = str_replace("\r", '', $manual_resource['assigned_ips']);
                    $manual_resource['assigned_ips'] = str_replace("\n", ',', $manual_resource['assigned_ips']);

                    $result['data']['dedicate_ip'] = $manual_resource['dedicated_ip'];
                    $result['data']['assign_ip'] = $manual_resource['assigned_ips'];
                    $result['data']['username'] = $manual_resource['username'];
                    $result['data']['password'] = aes_password_decode($manual_resource['password']);
                }
            }
        }else{
            // 获取IP,镜像密码
            if($hostLink['rel_id'] > 0){
                $IdcsmartCloud = new IdcsmartCloud($param['server']);
                $detail = $IdcsmartCloud->cloudDetail($hostLink['rel_id']);
                if(isset($detail['data']) && !empty($detail['data'])){
                    $getIp = $this->getIp([
                        'host_id' => $param['host']['id'],
                        'id'      => $hostLink['rel_id'],
                    ], $IdcsmartCloud, $detail);
                    if(!empty($getIp)){
                        $result['data']['dedicate_ip'] = $getIp['dedicate_ip'];
                        $result['data']['assign_ip'] = $getIp['assign_ip'];
                    }

                    $result['data']['port'] = $detail['data']['port'];
                    $result['data']['password'] = $detail['data']['rootpassword'];

                    // $update = [
                    //     'password' => aes_password_encode($detail['data']['rootpassword']),
                    // ];
                    // 匹配镜像
                    $image = ImageModel::where('product_id', $param['product']['id'])->where('rel_image_id', $detail['data']['system'])->order('enable', 'desc')->find();
                    if(!empty($image)){
                        $imageGroup = ImageGroupModel::find($image['image_group_id']);

                        $result['data']['image_icon'] = $imageGroup['icon'] ?? '';
                        $result['data']['image_name'] = $image['name'];
                        $result['data']['username'] = $detail['data']['svg'] == 1 ? 'administrator' : 'root';

                        // $update['image_id'] = $image['id'];
                        $this->where('id', $hostLink['id'])->update(['image_id'=>$image['id']]);
                    }

                    if(isset($getIp['vpc_private_ip'])){
                        $this->where('id', $hostLink['id'])->update([
                            'vpc_private_ip' => $getIp['vpc_private_ip'],
                        ]);
                    }
                }
            }
        }
        $result['data']['base_info'] = $this->formatBaseInfo($configData);

        if (!empty($ip = $result['data'])){
            if(!empty($ip['assign_ip']) && !empty($ip['dedicate_ip']) ){
                $ips = explode(',', $ip['assign_ip']);
                $ips[] = $ip['dedicate_ip'];
            }else if(!empty($ip['dedicate_ip'])){
                $ips = [ $ip['dedicate_ip'] ];
            }else{
                $ips = [];
            }
            $ips = array_filter($ips, function($val){
                return filter_var($val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            });
            $CloudLogic = new CloudLogic($param['host']['id']);
            $CloudLogic->ipChange([
                'ips' => $ips
            ]);
        }


        return $result;
    }

    public function otherParams($productId)
    {
        // 周期
        $duration = DurationModel::alias('d')
                    ->field('d.*,pdr.ratio')
                    ->join('product_duration_ratio pdr', 'd.id=pdr.duration_id AND pdr.product_id='.$productId)
                    ->where('d.product_id', $productId)
                    ->select()
                    ->toArray();
        // 数据中心
        $dataCenter = DataCenterModel::where('product_id', $productId)->select()->toArray();
        // 线路
        $line = [];
        if(!empty($dataCenter)){
            $line = LineModel::whereIn('data_center_id', array_column($dataCenter, 'id'))->select()->toArray();
        }
        // 配置项,TODO 可能需要隐藏部分信息
        $option = OptionModel::where('product_id', $productId)->select()->toArray();
        // 高级规则
        $limitRule = LimitRuleModel::where('product_id', $productId)->select()->toArray();
        // 操作系统分组
        $imageGroup = ImageGroupModel::where('product_id', $productId)->select()->toArray();
        // 操作系统
        // $image = ImageModel::where('product_id', $productId)->where('enable', 1)->select()->toArray();
        $image = ImageModel::where('product_id', $productId)->select()->toArray();
        // 套餐
        // $recommendConfig = RecommendConfigModel::where('product_id', $productId)->where(function($query){
        //     $query->whereOr('hidden', 0)->whereOr('upgrade_show', 1);
        // })->select()->toArray();
        $recommendConfig = RecommendConfigModel::where('product_id', $productId)->select()->toArray();
        // 套餐升级范围
        $recommendConfigUpgradeRange = [];
        if(!empty($recommendConfig)){
            $recommendConfigUpgradeRange = RecommendConfigUpgradeRangeModel::whereIn('recommend_config_id', array_column($recommendConfig, 'id'))->select()->toArray();
        }
        // 配置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];
        // 价格
        $price = PriceModel::where('product_id', $productId)->select()->toArray();
        // 安全组
        $securityGroupConfig = SecurityGroupConfigModel::where('product_id', $productId)->select()->toArray();
        
        return [
            'duration'                      => $duration,
            'data_center'                   => $dataCenter,
            'line'                          => $line,
            'option'                        => $option,
            'limit_rule'                    => $limitRule,
            'image_group'                   => $imageGroup,
            'image'                         => $image,
            'recommend_config'              => $recommendConfig,
            'recommend_config_upgrade_range'=> $recommendConfigUpgradeRange,
            'config'                        => $config,
            'price'                         => $price,
            'security_group_config'         => $securityGroupConfig,
        ];
    }

    public function syncOtherParams($productId, $param, $otherParams, $upstreamProductModel)
    {
        // 汇率
        $rate = $param['supplier']['rate']??1;
        $isSyncPrice = false; // 是否同步价格

        if ($param['profit_type']==0){ // 百分比
            $rate = bcdiv($rate*$param['profit_percent'], 100, 2);
            $isSyncPrice = true;
        }
        // 原来为百分比，修改为自定义时；或者强制同步：需要拉取价格
        if (($upstreamProductModel['profit_type']==0 && $param['profit_type']==1) || (isset($param['force']) && $param['force'])){
            $isSyncPrice = true;
        }

        $time = time();

        // 同步上游数据到本地
        if(!empty($otherParams)){
            // 周期
            $duration = DurationModel::where('product_id', $productId)->select()->toArray();
            $durationIdArr = array_column($duration, 'id', 'upstream_id');
            $oldId = array_column($duration, 'id');
            $newId = [];

            foreach($otherParams['duration'] as $v){
                if(isset($durationIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'name'  => $v['name'],
                        'num'   => $v['num'],
                        'unit'  => $v['unit'],
                        'support_apply_for_suspend' => $v['support_apply_for_suspend'] ?? 0,
                    ];
                    if($isSyncPrice){
                        $update['price_factor'] = $v['price_factor'];
                        $update['price'] = bcmul($v['price'],$rate,2);
                    }
                    DurationModel::where('id', $durationIdArr[ $v['id'] ])->update($update);
                    $newId[] = $durationIdArr[ $v['id'] ];
                }else{
                    // 添加
                    $duration = DurationModel::create([
                        'product_id'    => $productId,
                        'name'          => $v['name'],
                        'num'           => $v['num'],
                        'unit'          => $v['unit'],
                        'create_time'   => $time,
                        'price_factor'  => $v['price_factor'],
                        'price'         => bcmul($v['price'],$rate,2),
                        'upstream_id'   => $v['id'],
                        'support_apply_for_suspend'   => $v['support_apply_for_suspend'] ?? 0,
                    ]);
                    $durationIdArr[ $v['id'] ] = $duration->id;
                    $newId[] = $duration->id;
                }
            }
            $deleteDurationId = array_diff($oldId, $newId);
            if(!empty($deleteDurationId)){
                DurationModel::whereIn('id', $deleteDurationId)->delete();
            }
            // 周期比例
            $durationRatio = DurationRatioModel::where('product_id', $productId)->select()->toArray();
            $durationRatioArr = array_column($durationRatio, 'ratio', 'duration_id');
            DurationRatioModel::where('product_id', $productId)->delete();

            foreach($otherParams['duration'] as $v){
                $update = [
                    'duration_id'   => $durationIdArr[ $v['id'] ] ?? 0,
                    'product_id'    => $productId,
                    'ratio'         => $v['ratio'],
                ];
                if(!$isSyncPrice && isset($durationRatioArr[ $update['duration_id'] ])){
                    $update['ratio'] = $durationRatioArr[ $update['duration_id'] ];
                }
                DurationRatioModel::create($update);
            }
            // 数据中心
            $dataCenter = DataCenterModel::where('product_id', $productId)->select()->toArray();
            $dataCenterIdArr = array_column($dataCenter, 'id', 'upstream_id');
            $oldId = array_column($dataCenter, 'id');
            $newId = [];

            foreach($otherParams['data_center'] as $v){
                if(isset($dataCenterIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'country_id'        => $v['country_id'],
                        'city'              => $v['city'],
                        'area'              => $v['area'],
                        'cloud_config'      => $v['cloud_config'],
                        'cloud_config_id'   => $v['cloud_config_id'],
                        'gpu_name'          => $v['gpu_name'],
                    ];
                    DataCenterModel::where('id', $dataCenterIdArr[ $v['id'] ])->update($update);
                    $newId[] = $dataCenterIdArr[ $v['id'] ];
                }else{
                    $dataCenter = DataCenterModel::create([
                        'product_id'        => $productId,
                        'country_id'        => $v['country_id'],
                        'city'              => $v['city'],
                        'area'              => $v['area'],
                        'cloud_config'      => $v['cloud_config'],
                        'cloud_config_id'   => $v['cloud_config_id'],
                        'order'             => $v['order'],
                        'create_time'       => $time,
                        'gpu_name'          => $v['gpu_name'],
                        'upstream_id'       => $v['id'],
                    ]);
                    $dataCenterIdArr[ $v['id'] ] = $dataCenter->id;
                    $newId[] = $dataCenter->id;
                }
            }
            $deleteDataCenterId = array_diff($oldId, $newId);
            if(!empty($deleteDataCenterId)){
                DataCenterModel::whereIn('id', $deleteDataCenterId)->delete();
            }
            // 线路
            $line = [];
            if(!empty($oldId)){
                $line = LineModel::whereIn('data_center_id', $oldId)->select()->toArray();
            }
            $lineIdArr = array_column($line, 'id', 'upstream_id');
            $oldId = array_column($line, 'id');
            $newId = [];

            foreach($otherParams['line'] as $v){
                if(isset($lineIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'data_center_id'    => $dataCenterIdArr[ $v['data_center_id'] ] ?? 0,
                        'bill_type'         => $v['bill_type'],
                        'bw_ip_group'       => $v['bw_ip_group'],
                        'defence_enable'    => $v['defence_enable'],
                        'defence_ip_group'  => $v['defence_ip_group'],
                        'ip_enable'         => $v['ip_enable'],
                        'link_clone'        => $v['link_clone'],
                        'ipv6_enable'       => $v['ipv6_enable'],
//                        'hidden'            => $v['hidden'],
                        'upstream_hidden'   => $v['hidden'],
                        'order_default_defence' => $v['order_default_defence'] ?? '',
                        'sync_firewall_rule' => $v['sync_firewall_rule'] ?? 0,
                    ];
                    // 上游隐藏了，本地也需要隐藏，否则不同步
                    if ($v['hidden']==1){
                        $update['hidden'] = 1;
                    }
                    LineModel::where('id', $lineIdArr[ $v['id'] ])->update($update);
                    $newId[] = $lineIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);
                    $v['upstream_hidden'] = $v['hidden'];
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;
                    $v['data_center_id'] = $dataCenterIdArr[ $v['data_center_id'] ] ?? 0;
                    if(empty($v['data_center_id'])){
                        continue;
                    }
                    $line = LineModel::create($v);
                    $lineIdArr[ $upstreamId ] = $line->id;
                    $newId[] = $line->id;
                }
            }
            
            $deleteLineId = array_diff($oldId, $newId);
            if(!empty($deleteLineId)){
                LineModel::whereIn('id', $deleteLineId)->delete();
            }
            // 配置项
            $option = OptionModel::where('product_id', $productId)->select()->toArray();
            $optionIdArr = array_column($option, 'id', 'upstream_id');
            $oldId = array_column($option, 'id');
            $newId = [];

            foreach($otherParams['option'] as $v){
                if(isset($optionIdArr[ $v['id'] ])){
                    // 线路
                    $optionRelId = NULL;
                    if(in_array($v['rel_type'], [2,3,4,5,9])){
                        if(!empty($lineIdArr[ $v['rel_id'] ])){
                            $optionRelId = $lineIdArr[ $v['rel_id'] ];
                        }
                    }else if(in_array($v['rel_type'], [10])){
                        if(!empty($dataCenterIdArr[ $v['rel_id'] ])){
                            $optionRelId = $dataCenterIdArr[ $v['rel_id'] ];
                        }
                    }
                    // 修改
                    $update = [
                        'type'          => $v['type'],
                        'value'         => $v['value'],
                        'min_value'     => $v['min_value'],
                        'max_value'     => $v['max_value'],
                        'step'          => $v['step'],
                        'other_config'  => $v['other_config'],
                        'firewall_type'  => $v['firewall_type'] ?? '',
                        'defence_rule_id'  => $v['defence_rule_id'] ?? 0,
                        'order'  => $v['order'] ?? 0,
                    ];
                    if(!empty($optionRelId)){
                        $update['rel_id'] = $optionRelId;
                    }

                    OptionModel::where('id', $optionIdArr[ $v['id'] ])->update($update);
                    $newId[] = $optionIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;
                    // 线路
                    if(in_array($v['rel_type'], [2,3,4,5,9])){
                        $v['rel_id'] = $lineIdArr[ $v['rel_id'] ] ?? 0;
                    }else if(in_array($v['rel_type'], [10])){
                        $v['rel_id'] = $dataCenterIdArr[ $v['rel_id'] ] ?? 0;
                    }

                    $option = OptionModel::create($v);
                    $optionIdArr[ $upstreamId ] = $option->id;
                    $newId[] = $option->id;
                }
            }
            $deleteOptionId = array_diff($oldId, $newId);
            if(!empty($deleteOptionId)){
                OptionModel::whereIn('id', $deleteOptionId)->delete();
            }

            // 镜像分组
            $imageGroup = ImageGroupModel::where('product_id', $productId)->select()->toArray();
            $imageGroupIdArr = array_column($imageGroup, 'id', 'upstream_id');
            $oldId = array_column($imageGroup, 'id');
            $newId = [];

            foreach($otherParams['image_group'] as $v){
                if(isset($imageGroupIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'name'          => $v['name'],
                        'icon'          => $v['icon'],
                        'order'         => $v['order'],
                    ];
                    ImageGroupModel::where('id', $imageGroupIdArr[ $v['id'] ])->update($update);
                    $newId[] = $imageGroupIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;

                    $imageGroup = ImageGroupModel::create($v);
                    $imageGroupIdArr[ $upstreamId ] = $imageGroup->id;
                    $newId[] = $imageGroup->id;
                }
            }
            $deleteImageGroupId = array_diff($oldId, $newId);
            if(!empty($deleteImageGroupId)){
                ImageGroupModel::whereIn('id', $deleteImageGroupId)->delete();
            }
            
            // 镜像
            $image = ImageModel::where('product_id', $productId)->select()->toArray();
            $imageIdArr = array_column($image, 'id', 'upstream_id');
            $oldId = array_column($image, 'id');
            $newId = [];
            
            foreach($otherParams['image'] as $v){
                if(isset($imageIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'image_group_id'    => $imageGroupIdArr[ $v['image_group_id'] ] ?? 0,
                        'name'              => $v['name'],
                        'charge'            => $v['charge'],
                        'rel_image_id'      => 0,
                        'enable'            => $v['enable'],
                        // 'order'             => $v['order'],
                    ];
                    if($isSyncPrice){
                        if($update['charge'] == 1){
                            $update['price'] = bcmul($v['price'],$rate,2);
                        }else{
                            $update['price'] = 0;
                        }
                    }
                    ImageModel::where('id', $imageIdArr[ $v['id'] ])->update($update);
                    $newId[] = $imageIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['upstream_id'] = $upstreamId;
                    $v['image_group_id'] = $imageGroupIdArr[ $v['image_group_id'] ] ?? 0;
                    // 收费时
                    if($v['charge'] == 1){
                        $v['price'] = bcmul($v['price'],$rate,2);
                    }else{
                        $v['price'] = 0;
                    }
                    // 隐藏ID
                    $v['rel_image_id'] = 0;

                    $image = ImageModel::create($v);
                    $imageIdArr[ $upstreamId ] = $image->id;
                    $newId[] = $image->id;
                }
            }
            $deleteImageId = array_diff($oldId, $newId);
            if(!empty($deleteImageId)){
                ImageModel::whereIn('id', $deleteImageId)->delete();
            }
            
            // 套餐
            $recommendConfig = RecommendConfigModel::where('product_id', $productId)->select()->toArray();
            $recommendConfigIdArr = array_column($recommendConfig, 'id', 'upstream_id');
            $oldId = array_column($recommendConfig, 'id');
            $newId = [];
            
            foreach($otherParams['recommend_config'] as $v){
                if(isset($recommendConfigIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        // 'name'                  => $v['name'],
                        // 'description'           => $v['description'],
                        // 'order'                 => $v['order'],
                        'data_center_id'        => $dataCenterIdArr[ $v['data_center_id'] ] ?? 0,
                        'cpu'                   => $v['cpu'],
                        'memory'                => $v['memory'],
                        'system_disk_size'      => $v['system_disk_size'],
                        'data_disk_size'        => $v['data_disk_size'],
                        'bw'                    => $v['bw'],
                        'peak_defence'          => $v['peak_defence'],
                        'system_disk_type'      => $v['system_disk_type'],
                        'data_disk_type'        => $v['data_disk_type'],
                        'flow'                  => $v['flow'],
                        'line_id'               => $lineIdArr[ $v['line_id'] ] ?? 0,
                        'ip_num'                => $v['ip_num'],
                        'upgrade_range'         => $v['upgrade_range'],
                        'hidden'                => $v['hidden'],
                        'gpu_num'               => $v['gpu_num'],
                        'ipv6_num'              => $v['ipv6_num'],
                        'upgrade_show'          => $v['upgrade_show'],
                        'in_bw'                 => $v['in_bw'],
                        'traffic_type'          => $v['traffic_type'],
                        'due_not_free_gpu'      => $v['due_not_free_gpu'] ?? 0,
                    ];
                    RecommendConfigModel::where('id', $recommendConfigIdArr[ $v['id'] ])->update($update);
                    $newId[] = $recommendConfigIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['data_center_id'] = $dataCenterIdArr[ $v['data_center_id'] ] ?? 0;
                    $v['line_id'] = $lineIdArr[ $v['line_id'] ] ?? 0;
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;

                    $recommendConfig = RecommendConfigModel::create($v);
                    $recommendConfigIdArr[ $upstreamId ] = $recommendConfig->id;
                    $newId[] = $recommendConfig->id;
                }
            }
            $deleteRecommendConfigId = array_diff($oldId, $newId);
            if(!empty($deleteRecommendConfigId)){
                RecommendConfigModel::whereIn('id', $deleteRecommendConfigId)->delete();
            }
            
            // 直接删除老的
            RecommendConfigUpgradeRangeModel::whereIn('recommend_config_id', $oldId)->delete();
            foreach($otherParams['recommend_config_upgrade_range'] as $v){
                $v['recommend_config_id'] = $recommendConfigIdArr[ $v['recommend_config_id'] ] ?? 0;
                $v['rel_recommend_config_id'] = $recommendConfigIdArr[ $v['rel_recommend_config_id'] ] ?? 0;
                if(empty($v['recommend_config_id']) || empty($v['rel_recommend_config_id'])){
                    continue;
                }
                RecommendConfigUpgradeRangeModel::create($v);
            }
            // 高级规则
            $limitRule = LimitRuleModel::where('product_id', $productId)->select()->toArray();
            $limitRuleIdArr = array_column($limitRule, 'id', 'upstream_id');
            $oldId = array_column($limitRule, 'id');
            $newId = [];

            foreach ($otherParams['limit_rule'] as $v){
                $upstreamId = $v['id'];
                unset($v['id']);

                $v['product_id'] = $productId;
                $v['rule'] = json_decode($v['rule'], true);
                $v['result'] = json_decode($v['result'], true);
                if(isset($v['rule']['data_center']['id'])){
                    foreach($v['rule']['data_center']['id'] as $kk=>$vv){
                        if(isset($dataCenterIdArr[$vv])){
                            $v['rule']['data_center']['id'][$kk] = (int)$dataCenterIdArr[$vv];
                        }else{
                            unset($v['rule']['data_center']['id'][$kk]);
                        }
                    }
                    $v['rule']['data_center']['id'] = array_values($v['rule']['data_center']['id']);
                }
                if(isset($v['rule']['image']['id'])){
                    foreach($v['rule']['image']['id'] as $kk=>$vv){
                        if(isset($imageIdArr[$vv])){
                            $v['rule']['image']['id'][$kk] = (int)$imageIdArr[$vv];
                        }else{
                            unset($v['rule']['image']['id'][$kk]);
                        }
                    }
                    $v['rule']['image']['id'] = array_values($v['rule']['image']['id']);
                }
                if(isset($v['result']['image'])){
                    foreach($v['result']['image'] as $kk=>$resultItem){
                        foreach($resultItem['id'] as $kkk=>$vvv){
                            if(isset($imageIdArr[$vvv])){
                                $v['result']['image'][$kk]['id'][$kkk] = (int)$imageIdArr[$vvv];
                            }else{
                                unset($v['result']['image'][$kk]['id'][$kkk]);
                            }
                        }
                        $v['result']['image'][$kk]['id'] = array_values($v['result']['image'][$kk]['id']);
                    }
                }
                $v['rule'] = json_encode($v['rule']);
                $v['result'] = json_encode($v['result']);
                $v['rule_md5'] = md5($v['rule']);
                $v['upstream_id'] = $upstreamId;

                if(isset($limitRuleIdArr[ $upstreamId ])){
                    LimitRuleModel::where('id', $limitRuleIdArr[ $upstreamId ])->update($v);
                    $newId[] = $limitRuleIdArr[ $upstreamId ];
                }else{
                    $r = LimitRuleModel::create($v);
                    $newId[] = $r->id;
                }
            }
            $deleteLimitRuleId = array_diff($oldId, $newId);
            if(!empty($deleteLimitRuleId)){
                LimitRuleModel::whereIn('id', $deleteLimitRuleId)->delete();
            }

            // 安全组配置,必须上游有下游才支持
            if(!empty($otherParams['security_group_config'])){
                $securityGroupConfigs = SecurityGroupConfigModel::where('product_id', $productId)->select()->toArray();
                $securityGroupConfigIdArr = array_column($securityGroupConfigs, 'id', 'upstream_id');
                $oldId = array_column($securityGroupConfigs, 'id');
                $newId = [];

                foreach($otherParams['security_group_config'] as $v){
                    if(isset($securityGroupConfigIdArr[ $v['id'] ])){
                        // 修改
                        $update = [
                            'description'  => $v['description'],
                            'protocol'     => $v['protocol'],
                            'port'         => $v['port'],
                            'direction'    => $v['direction'],
                            'status'       => $v['status'],
                            'sort'         => $v['sort'],
                        ];
                        SecurityGroupConfigModel::where('id', $securityGroupConfigIdArr[ $v['id'] ])->update($update);
                        $newId[] = $securityGroupConfigIdArr[ $v['id'] ];
                    }else{
                        $upstreamId = $v['id'];
                        unset($v['id']);

                        $v['product_id'] = $productId;
                        $v['create_time'] = $time;
                        $v['upstream_id'] = $upstreamId;

                        $securityGroupConfig = SecurityGroupConfigModel::create($v);
                        $securityGroupConfigIdArr[ $upstreamId ] = $securityGroupConfig->id;
                        $newId[] = $securityGroupConfig->id;
                    }
                }
                $deleteSecurityGroupConfigId = array_diff($oldId, $newId);
                if(!empty($deleteSecurityGroupConfigId)){
                    SecurityGroupConfigModel::whereIn('id', $deleteSecurityGroupConfigId)->delete();
                }
            }

            // 价格
            $price = PriceModel::where('product_id', $productId)->select()->toArray();
            $priceIdArr = [];
            foreach($price as $v){
                $priceIdArr[ $v['rel_type'] ][ $v['rel_id'] ][ $v['duration_id'] ] = $v['id'];
            }
            $oldId = array_column($price, 'id');
            $newId = [];

            foreach($otherParams['price'] as $v){
                $upstreamId = $v['id'];
                unset($v['id']);

                $v['product_id'] = $productId;
                $v['duration_id'] = $durationIdArr[ $v['duration_id'] ] ?? 0;
                if($v['rel_type'] == PriceModel::REL_TYPE_OPTION){
                    $v['rel_id'] = $optionIdArr[ $v['rel_id'] ] ?? 0;
                }else if($v['rel_type'] == PriceModel::REL_TYPE_RECOMMEND_CONFIG){
                    $v['rel_id'] = $recommendConfigIdArr[ $v['rel_id'] ] ?? 0;
                }
                // 找不到对应周期
                if(empty($v['duration_id'])){
                    continue;
                }
                if(isset($priceIdArr[ $v['rel_type'] ][ $v['rel_id'] ][ $v['duration_id'] ])){
                    if($isSyncPrice){
                        PriceModel::where('id', $priceIdArr[ $v['rel_type'] ][ $v['rel_id'] ][ $v['duration_id'] ])->update([
                            'price' => bcmul($v['price'],$rate,2),
                        ]);
                    }
                    $newId[] = $priceIdArr[ $v['rel_type'] ][ $v['rel_id'] ][ $v['duration_id'] ];
                }else{
                    $v['price'] = bcmul($v['price'],$rate,2);
                    $r = PriceModel::create($v);
                    $newId[] = $r->id;
                }
            }
            // $deletePriceId = array_diff($oldId, $newId);
            // if(!empty($deletePriceId)){
            //     PriceModel::whereIn('id', $deletePriceId)->delete();
            // }
            if(!empty($otherParams['config'])){
                $configData = [
                    'product_id'        => $productId,
                    'ip_mac_bind'       => $otherParams['config']['ip_mac_bind'],
                    'support_ssh_key'   => 0, // $otherParams['config']['support_ssh_key'], // 代理密钥不支持
                    'rand_ssh_port'     => $otherParams['config']['rand_ssh_port'],
                    'support_normal_network' => $otherParams['config']['support_normal_network'],
                    'support_vpc_network' => $otherParams['config']['support_vpc_network'],
                    'support_public_ip' => $otherParams['config']['support_public_ip'],
                    'backup_enable' => $otherParams['config']['backup_enable'],
                    'snap_enable' => $otherParams['config']['snap_enable'],
                    'disk_limit_enable' => 0, // $otherParams['config']['disk_limit_enable'], // 性能限制隐藏
                    // 'reinstall_sms_verify' => 0,
                    // 'reset_password_sms_verify' => 0,
                    'niccard' => $otherParams['config']['niccard'],
                    // 'ipv6_num' => $otherParams['config']['ipv6_num'], // 弃用
                    'nat_acl_limit' => $otherParams['config']['nat_acl_limit'],
                    'nat_web_limit' => $otherParams['config']['nat_web_limit'],
                    'cpu_model' => $otherParams['config']['cpu_model'],
                    'memory_unit' => $otherParams['config']['memory_unit'],
                    'type' => $otherParams['config']['type'],
                    'node_priority' => $otherParams['config']['node_priority'],
                    'disk_limit_switch' => $otherParams['config']['disk_limit_switch'],
                    'disk_limit_num' => $otherParams['config']['disk_limit_num'],
                    'free_disk_switch' => $otherParams['config']['free_disk_switch'],
                    'free_disk_size' => $otherParams['config']['free_disk_size'],
                    'only_sale_recommend_config' => $otherParams['config']['only_sale_recommend_config'],
                    'no_upgrade_tip_show' => $otherParams['config']['no_upgrade_tip_show'],
                    'default_nat_acl' => $otherParams['config']['default_nat_acl'],
                    'default_nat_web' => $otherParams['config']['default_nat_web'],
                    'rand_ssh_port_start' => $otherParams['config']['rand_ssh_port_start'],
                    'rand_ssh_port_end' => $otherParams['config']['rand_ssh_port_end'],
                    'rand_ssh_port_windows' => $otherParams['config']['rand_ssh_port_windows'],
                    'rand_ssh_port_linux' => $otherParams['config']['rand_ssh_port_linux'],
                    'default_one_ipv4' => $otherParams['config']['default_one_ipv4'],
                    'manual_manage' => 0,
                    'is_agent' => $otherParams['config']['is_agent'] ? 1 : 0,
                    'sync_firewall_rule' => $otherParams['config']['sync_firewall_rule'] ?? 0,
                    'order_default_defence' => $otherParams['config']['order_default_defence'] ?? '',
                    'free_disk_type' => $otherParams['config']['free_disk_type'] ?? '',
                    'custom_rand_password_rule' => $otherParams['config']['custom_rand_password_rule'] ?? 0,
                    'default_password_length' => $otherParams['config']['default_password_length'] ?? 12,
                    'simulate_physical_machine_enable' => $otherParams['config']['simulate_physical_machine_enable'] ?? 1,
                ];
                if(isset($otherParams['config']['level_discount_cpu_order'])){
                    $configData['level_discount_cpu_order'] = $otherParams['config']['level_discount_cpu_order'];
                    $configData['level_discount_cpu_upgrade'] = $otherParams['config']['level_discount_cpu_upgrade'];
                    $configData['level_discount_memory_order'] = $otherParams['config']['level_discount_memory_order'];
                    $configData['level_discount_memory_upgrade'] = $otherParams['config']['level_discount_memory_upgrade'];
                    $configData['level_discount_bw_order'] = $otherParams['config']['level_discount_bw_order'];
                    $configData['level_discount_bw_upgrade'] = $otherParams['config']['level_discount_bw_upgrade'];
                    $configData['level_discount_ipv4_order'] = $otherParams['config']['level_discount_ipv4_order'];
                    $configData['level_discount_ipv4_upgrade'] = $otherParams['config']['level_discount_ipv4_upgrade'];
                    $configData['level_discount_ipv6_order'] = $otherParams['config']['level_discount_ipv6_order'];
                    $configData['level_discount_ipv6_upgrade'] = $otherParams['config']['level_discount_ipv6_upgrade'];
                    $configData['level_discount_system_disk_order'] = $otherParams['config']['level_discount_system_disk_order'];
                    $configData['level_discount_system_disk_upgrade'] = $otherParams['config']['level_discount_system_disk_upgrade'];
                    $configData['level_discount_data_disk_order'] = $otherParams['config']['level_discount_data_disk_order'];
                    $configData['level_discount_data_disk_upgrade'] = $otherParams['config']['level_discount_data_disk_upgrade'];
                }
                if(isset($otherParams['config']['disk_range_limit_switch'])){
                    $configData['disk_range_limit_switch'] = $otherParams['config']['disk_range_limit_switch'];
                    $configData['disk_range_limit'] = $otherParams['config']['disk_range_limit'];
                }
                if(isset($otherParams['config']['level_discount_cpu_renew'])){
                    $configData['level_discount_cpu_renew'] = $otherParams['config']['level_discount_cpu_renew'];
                    $configData['level_discount_memory_renew'] = $otherParams['config']['level_discount_memory_renew'];
                    $configData['level_discount_bw_renew'] = $otherParams['config']['level_discount_bw_renew'];
                    $configData['level_discount_ipv4_renew'] = $otherParams['config']['level_discount_ipv4_renew'];
                    $configData['level_discount_ipv6_renew'] = $otherParams['config']['level_discount_ipv6_renew'];
                    $configData['level_discount_system_disk_renew'] = $otherParams['config']['level_discount_system_disk_renew'];
                    $configData['level_discount_data_disk_renew'] = $otherParams['config']['level_discount_data_disk_renew'];
                }

                $config = ConfigModel::where('product_id', $productId)->find();
                if(!empty($config)){
                    ConfigModel::where('product_id', $productId)->update($configData);
                }else{
                    ConfigModel::create($configData);
                }

                $backupConfig = BackupConfigModel::where('product_id', $productId)->select()->toArray();
                $backupConfigIdArr = array_column($backupConfig, 'id', 'upstream_id');
                $oldId = array_column($backupConfig, 'id');
                $newId = [];

                foreach($otherParams['config']['backup_data'] as $v){
                    if(isset($backupConfigIdArr[ $v['id'] ])){
                        $update = [
                            'num'   => $v['num'],
                        ];
                        if($isSyncPrice){
                            $update['price'] = bcmul($v['price'],$rate,2);
                        }
                        BackupConfigModel::where('id', $backupConfigIdArr[ $v['id'] ])->update($update);
                        $newId[] = $backupConfigIdArr[ $v['id'] ];
                    }else{
                        $backupConfig = BackupConfigModel::create([
                            'product_id'    => $productId,
                            'type'          => 'backup',
                            'num'           => $v['num'],
                            'price'         => bcmul($v['price'],$rate,2),
                            'upstream_id'   => $v['id'],
                        ]);
                        $backupConfigIdArr[ $v['id'] ] = $backupConfig->id;
                        $newId[] = $backupConfig->id;
                    }
                }
                foreach($otherParams['config']['snap_data'] as $v){
                    if(isset($backupConfigIdArr[ $v['id'] ])){
                        $update = [
                            'num'   => $v['num'],
                        ];
                        if($isSyncPrice){
                            $update['price'] = bcmul($v['price'],$rate,2);
                        }
                        BackupConfigModel::where('id', $backupConfigIdArr[ $v['id'] ])->update($update);
                        $newId[] = $backupConfigIdArr[ $v['id'] ];
                    }else{
                        $backupConfig = BackupConfigModel::create([
                            'product_id'    => $productId,
                            'type'          => 'snap',
                            'num'           => $v['num'],
                            'price'         => bcmul($v['price'],$rate,2),
                            'upstream_id'   => $v['id'],
                        ]);
                        $backupConfigIdArr[ $v['id'] ] = $backupConfig->id;
                        $newId[] = $backupConfig->id;
                    }
                }
                $deleteBackupConfigId = array_diff($oldId, $newId);
                if(!empty($deleteBackupConfigId)){
                    BackupConfigModel::whereIn('id', $deleteBackupConfigId)->delete();
                }
                // 代理商时
                if($configData['is_agent'] == 1){
                    $resourcePackage = ResourcePackageModel::where('product_id', $productId)->select()->toArray();
                    $resourcePackageIdArr = array_column($resourcePackage, 'id', 'upstream_id');
                    $oldId = array_column($resourcePackage, 'id');
                    $newId = [];

                    foreach($otherParams['config']['resource_package'] as $v){
                        if(isset($resourcePackageIdArr[ $v['id'] ])){
                            $update = [
                                'name'  => $v['name'],
                                'rid'   => 0,
                            ];
                            ResourcePackageModel::where('id', $resourcePackageIdArr[ $v['id'] ])->update($update);
                            $newId[] = $resourcePackageIdArr[ $v['id'] ];
                        }else{
                            $r = ResourcePackageModel::create([
                                'product_id'    => $productId,
                                'name'          => $v['name'],
                                'rid'           => 0, // 隐藏
                                'upstream_id'   => $v['id'],
                            ]);
                            $newId[] = $r->id;
                        }
                    }
                    $deleteId = array_diff($oldId, $newId);
                    if(!empty($deleteId)){
                        ResourcePackageModel::whereIn('id', $deleteId)->delete();
                    }
                }
            }
        }

        return ['status'=>200];
    }

    public function exchangeParams($productId, $param, $sence, $host)
    {
        // 参数转换
        $exchangeParams = $param;
        switch ($sence){
            case 'create_account':
                if(isset($param['duration_id'])){
                    $exchangeParams['duration_id'] = DurationModel::where('id', $param['duration_id'])->value('upstream_id');
                }
                if(isset($param['recommend_config_id'])){
                    $exchangeParams['recommend_config_id'] = RecommendConfigModel::where('id', $param['recommend_config_id'])->value('upstream_id');
                }
                if(isset($param['data_center_id'])){
                    $exchangeParams['data_center_id'] = DataCenterModel::where('id', $param['data_center_id'])->value('upstream_id');
                }
                if(isset($param['line_id'])){
                    $exchangeParams['line_id'] = LineModel::where('id', $param['line_id'])->value('upstream_id');
                }
                if(isset($param['image_id'])){
                    $exchangeParams['image_id'] = ImageModel::where('id', $param['image_id'])->value('upstream_id');
                }
                if(isset($param['resource_package_id'])){
                    $exchangeParams['resource_package_id'] = ResourcePackageModel::where('id', $param['resource_package_id'])->value('upstream_id');
                }

                // 不支持的参数
                $exchangeParams['ssh_key_id'] = 0;
                $exchangeParams['security_group_protocol'] = [];
                $exchangeParams['auto_renew'] = 0;

                // 自动创建VPC时，转为先创建VPC
                if($exchangeParams['network_type'] == 'vpc'){
                    $vpcNetworkId = $this->where('host_id', $host['id'])->value('vpc_network_id');
                    if(!empty($vpcNetworkId)){
                        $vpcNetwork = VpcNetworkModel::find($vpcNetworkId);

                        // 只能是指定VPC开通
                        $exchangeParams['vpc'] = [
                            'id'    => $vpcNetwork['upstream_id'] ?? -1,
                        ];
                    }
                }

                // 使用下游指定端口
                $HostAdditionModel = new HostAdditionModel();
                $hostAddition = $HostAdditionModel->where('host_id', $host['id'])->find();
                if(!empty($hostAddition['port'])){
                    $exchangeParams['rand_port'] = 0;
                    $exchangeParams['port'] = $hostAddition['port'];
                }

                break;
            default:
                break;
        }
        return $exchangeParams;
    }

    /**
     * @时间 2024-08-20
     * @title 获取产品所有配置
     * @desc  获取产品所有配置,用于下游同步
     * @author hh
     * @version v1 
     * @param   HostModel host - 当前产品实例 require
     * @return  int duration_id - 周期ID
     * @return  int data_center_id - 数据中心ID
     */
    public function hostOtherParams($host): array
    {
        $data = [];
        // 获取当前实例所有配置
        $hostLink = $this->where('host_id', $host['id'])->find();
        if(empty($hostLink)){
            return $data;
        }
        $configData = json_decode($hostLink['config_data'], true);
        if(!empty($configData['network_type']) && $configData['network_type'] == 'vpc'){
            $data['vpc_private_ip'] = $hostLink['vpc_private_ip'];
        }else{
            $data['vpc_private_ip'] = '';
        }
        // $data['duration_id'] = $configData['duration']['id'] ?? 0;
        // $data['data_center_id'] = $hostLink['data_center_id'];
        // $data['line_id'] = $configData['line']['id'];
        // $data['image_id'] = $hostLink['image_id'];
        // $data['backup_num'] = $hostLink['backup_num'];
        // $data['snap_num'] = $hostLink['snap_num'];
        // $data['recommend_config_id'] = $hostLink['recommend_config_id'];

        // $data['cpu'] = $configData['cpu'];
        // $data['memory'] = $configData['memory'];
        // $data['memory_unit'] = $configData['memory_unit'] ?? 'GB';
        // $data['flow'] = $configData['flow'] ?? NULL;
        // $data['bw'] = $configData['bw'] ?? NULL;
        // $data['defence'] = $configData['defence'] ?? NULL;
        // $data['ip'] = $configData['ip'] ?? [];
        // $data['ipv6_num'] = $configData['ipv6_num'] ?? 0;
        // $data['network_type'] = $configData['network_type'];

        // // vpc信息
        // $data['vpc'] = [];
        // if(!empty($hostLink['vpc_network_id'])){
        //     $data['vpc'] = VpcNetworkModel::field('id,data_center_id,name,ips,vpc_name')->where('id', $hostLink['vpc_network_id'])->find();
        // }
        // // 磁盘信息
        // $data['disk'] = DiskModel::field('id,name,size,type,is_free,type2,status')->where('host_id', $host['id'])->select()->toArray();

        return $data;
    }

    /**
     * @时间 2024-08-20
     * @title 同步上游产品配置
     * @desc  同步上游产品配置
     * @author hh
     * @version v1
     * @param   HostModel $host - HostModel实例 require
     * @param   array otherParams - 其他参数,hostOtherParams的返回值内容 require
     */
    public function syncHostOtherParams($host, $otherParams)
    {
        $productId = $host['product_id'];
        // 获取当前实例所有配置
        $hostLink = $this->where('host_id', $host['id'])->find();
        if(empty($hostLink) || empty($otherParams)){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $configData = json_decode($hostLink['config_data'], true);
        if(!empty($configData['network_type']) && $configData['network_type'] == 'vpc'){
            if(isset($otherParams['vpc_private_ip'])){
                $this->where('id', $hostLink['id'])->update([
                    'vpc_private_ip'=>$otherParams['vpc_private_ip']
                ]);
            }
        }

        // // 日志变更详情
        // $detail = '';

        // $update = [];
        // $duration = DurationModel::where('product_id', $productId)->where('upstream_id', $otherParams['duration_id'])->find();
        // if(!empty($duration) && $duration['id'] != $configData['duration']['id']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('mf_cloud_duration'),
        //         '{old}'   => $configData['duration']['name'],
        //         '{new}'   => $duration['name'],
        //     ]);

        //     $configData['duration'] = $duration->toArray();
        // }
        // $dataCenter = DataCenterModel::where('product_id', $productId)->where('upstream_id', $otherParams['data_center_id'])->find();
        // if(!empty($dataCenter) && $hostLink['data_center_id'] != $dataCenter['id']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('data_center'),
        //         '{old}'   => $configData['data_center']['name'] ?? '',
        //         '{new}'   => $dataCenter['name'],
        //     ]);

        //     $update['data_center_id'] = $dataCenter['id'];
        //     $configData['data_center'] = $dataCenter->toArray();
        // }
        // $line = LineModel::alias('l')
        //         ->field('l.*')
        //         ->join('module_mf_cloud_data_center dc', 'l.data_center_id=dc.id')
        //         ->where('dc.product_id', $productId)
        //         ->where('l.upstream_id', $otherParams['line_id'])
        //         ->find();
        // if(!empty($line) && $line['id'] != $configData['line']['id']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('mf_cloud_line'),
        //         '{old}'   => $configData['line']['name'] ?? '',
        //         '{new}'   => $line['name'],
        //     ]);

        //     $configData['line'] = $line->toArray();
        // }
        // // 镜像
        // $image = ImageModel::where('product_id', $productId)->where('upstream_id', $otherParams['image_id'])->find();
        // if(!empty($image) && $image['id'] != $hostLink['image_id']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('mf_cloud_os'),
        //         '{old}'   => $configData['image']['name'] ?? ImageModel::where('id', $hostLink['image_id'])->value('name'),
        //         '{new}'   => $image['name'],
        //     ]);

        //     $update['image_id'] = $image['id'];
        //     $configData['image'] = $image->toArray();
        // }
        // // 备份/快照数量
        // if($hostLink['backup_num'] != $otherParams['backup_num']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('mf_cloud_backup_num'),
        //         '{old}'   => $hostLink['backup_num'],
        //         '{new}'   => $otherParams['backup_num'],
        //     ]);

        //     $update['backup_num'] = $otherParams['backup_num'];
        // }
        // if($hostLink['snap_num'] != $otherParams['snap_num']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('mf_cloud_snap_num'),
        //         '{old}'   => $hostLink['snap_num'],
        //         '{new}'   => $otherParams['snap_num'],
        //     ]);

        //     $update['snap_num'] = $otherParams['snap_num'];
        // }
        // // 套餐
        // if(!empty($otherParams['recommend_config_id'])){
        //     $recommendConfig = RecommendConfigModel::where('product_id', $productId)->where('upstream_id', $otherParams['recommend_config_id'])->find();
        //     if(!empty($recommendConfig) && $hostLink['recommend_config_id'] != $recommendConfig['id']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('package'),
        //             '{old}'   => RecommendConfigModel::where('id', $hostLink['recommend_config_id'])->value('name') ?: '套餐#'.$hostLink['recommend_config_id'],
        //             '{new}'   => $recommendConfig['name'],
        //         ]);

        //         $update['recommend_config_id'] = $recommendConfig['id'];
        //     }
        // }
        // // cpu
        // if($configData['cpu']['value'] != $otherParams['cpu']['value']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => 'CPU',
        //         '{old}'   => $configData['cpu']['value'],
        //         '{new}'   => $otherParams['cpu']['value'],
        //     ]);

        //     $configData['cpu'] = $otherParams['cpu'];
        // }
        // // 内存
        // $memoryUnit = $configData['memory_unit'] ?? 'GB';
        // if($configData['memory']['value'] != $otherParams['memory']['value'] || $memoryUnit != $otherParams['memory_unit']){
        //     $detail .= lang_plugins('log_common_modify', [
        //         '{name}'  => lang_plugins('memory'),
        //         '{old}'   => $configData['memory']['value'] . $memoryUnit,
        //         '{new}'   => $otherParams['memory']['value'] . $otherParams['memory_unit'],
        //     ]);

        //     $configData['memory'] = $otherParams['memory'];
        //     $configData['memory_unit'] = $otherParams['memory_unit'];
        // }
        // // 流量/带宽
        // if(isset($otherParams['flow']['value'])){
        //     if(!isset($configData['flow']['value']) || $configData['flow']['value'] != $otherParams['flow']['value']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('flow'),
        //             '{old}'   => $configData['flow']['value'] ?? lang_plugins('null'),
        //             '{new}'   => $otherParams['flow']['value'],
        //         ]);

        //         $configData['flow'] = $otherParams['flow'];
        //     }
        // }else{
        //     if(isset($configData['flow']['value'])){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('flow'),
        //             '{old}'   => $configData['flow']['value'],
        //             '{new}'   => lang_plugins('null'),
        //         ]);

        //         // 清掉流量
        //         unset($configData['flow']);
        //     }
        // }
        // // 带宽
        // if(isset($otherParams['bw']['value'])){
        //     if(!isset($configData['bw']['value']) || $configData['bw']['value'] != $otherParams['bw']['value']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('bw'),
        //             '{old}'   => $configData['bw']['value'] ?? lang_plugins('null'),
        //             '{new}'   => $otherParams['bw']['value'],
        //         ]);

        //         $configData['bw'] = $otherParams['bw'];
        //     }
        // }else{
        //     if(isset($configData['bw']['value'])){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('bw'),
        //             '{old}'   => $configData['bw']['value'],
        //             '{new}'   => lang_plugins('null'),
        //         ]);

        //         // 清掉流量
        //         unset($configData['bw']);
        //     }
        // }
        //  // 防御
        // if(isset($otherParams['defence']['value'])){
        //     if(!isset($configData['defence']['value']) || $configData['defence']['value'] != $otherParams['defence']['value']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
        //             '{old}'   => $configData['defence']['value'] ?? 0,
        //             '{new}'   => $otherParams['defence']['value'],
        //         ]);

        //         $configData['defence'] = $otherParams['defence'];
        //     }
        // }else{
        //     if(isset($configData['defence']['value'])){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('defence'),
        //             '{old}'   => $configData['defence']['value'],
        //             '{new}'   => lang_plugins('null'),
        //         ]);

        //         // 清掉流量
        //         unset($configData['defence']);
        //     }
        // }
        // // IP
        // if(isset($otherParams['ip']['value'])){
        //     if(!isset($configData['ip']['value']) || $configData['ip']['value'] != $otherParams['ip']['value']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_ipv4_num'),
        //             '{old}'   => $configData['ip']['value'] ?? lang_plugins('null'),
        //             '{new}'   => $otherParams['ip']['value'],
        //         ]);

        //         $configData['ip'] = $otherParams['ip'];
        //     }
        // }else{
        //     if(isset($configData['ip']['value'])){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_ipv4_num'),
        //             '{old}'   => $configData['ip']['value'],
        //             '{new}'   => lang_plugins('null'),
        //         ]);

        //         // 清掉流量
        //         unset($configData['ip']);
        //     }
        // }
        // // IPv6数量
        // if(isset($otherParams['ipv6_num'])){
        //     if(!isset($configData['ipv6_num']) || $configData['ipv6_num'] != $otherParams['ipv6_num']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_ipv6_num'),
        //             '{old}'   => $configData['ipv6_num'] ?? lang_plugins('null'),
        //             '{new}'   => $otherParams['ipv6_num'],
        //         ]);

        //         $configData['ipv6_num'] = $otherParams['ipv6_num'];
        //     }
        // }else{
        //     if(isset($configData['ipv6_num'])){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_ipv6_num'),
        //             '{old}'   => $configData['ipv6_num'],
        //             '{new}'   => 0,//lang_plugins('null'),
        //         ]);

        //         // 清掉流量
        //         $configData['ipv6_num'] = 0;
        //     }
        // }
        // // VPC信息
        // if(!empty($otherParams['vpc'])){
        //     $vpc = VpcNetworkModel::where('product_id', $productId)->where('upstream_id', $otherParams['vpc']['id'])->find();
        //     if(!empty($vpc)){
        //         if($hostLink['vpc_network_id'] != $vpc['id']){
        //             $detail .= lang_plugins('log_common_modify', [
        //                 '{name}'  => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        //                 '{old}'   => VpcNetworkModel::where('id', $hostLink['vpc_network_id'])->value('name') ?? '',
        //                 '{new}'   => $vpc['name'],
        //             ]);

        //             $update['vpc_network_id'] = $vpc['id'];
        //         }
        //     }else{
        //         // 下游都找不到该vpc,有区域自动创建
        //         $vpcDataCenter = DataCenterModel::where('product_id', $productId)->where('upstream_id', $otherParams['vpc']['data_center_id'])->find();
        //         if(!empty($vpcDataCenter)){
        //             $detail .= lang_plugins('log_common_modify', [
        //                 '{name}'  => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        //                 '{old}'   => VpcNetworkModel::where('id', $hostLink['vpc_network_id'])->value('name') ?? '',
        //                 '{new}'   => $otherParams['vpc']['name'],
        //             ]);

        //             // 自动创建
        //             $vpc = VpcNetworkModel::create([
        //                 'product_id'        => $productId,
        //                 'data_center_id'    => $vpcDataCenter['id'],
        //                 'name'              => $otherParams['vpc']['name'],
        //                 'client_id'         => $host['client_id'],
        //                 'ips'               => $otherParams['vpc']['ips'],
        //                 'vpc_name'          => $otherParams['vpc']['vpc_name'],
        //                 'create_time'       => time(),
        //                 'upstream_id'       => $otherParams['vpc']['id'],
        //             ]);
        //             $update['vpc_network_id'] = $vpc->id;
        //         }
        //     }
        // }
        // // 磁盘,id,name,size,type,is_free,type2,status
        // foreach($otherParams['disk'] as $v){
        //     $disk = DiskModel::where('host_id', $host['id'])->where('upstream_id', $v['id'])->find();
        //     if(empty($disk)){
        //         // TODO 下游找不到上游磁盘
        //         continue;
        //     }
        //     // 大小不同才记录日志
        //     if($v['size'] != $disk['size']){
        //         $detail .= lang_plugins('log_common_modify', [
        //             '{name}'  => lang_plugins('mf_cloud_disk') . $v['name'],
        //             '{old}'   => $disk['size'] . 'G',
        //             '{new}'   => $v['size'] . 'G',
        //         ]);
        //     }
        //     DiskModel::where('id', $disk['id'])->update([
        //         'name'      => $v['name'],
        //         'size'      => $v['size'],
        //         'type'      => $v['type'],
        //         'is_free'   => $v['is_free'],
        //         'type2'     => $v['type2'],
        //         'status'    => $v['status'],
        //     ]);
        // }

        // if(!empty($detail)){
        //     $update['config_data'] = json_encode($configData);

        //     $this->where('id', $hostLink['id'])->update($update);

        //     $description = lang_plugins('log_mf_cloud_sync_upstream_host_success', [
        //         '{host}'    => 'host#'.$host['id'].'#'.$host['name'].'#',
        //         '{detail}'  => $detail,
        //     ]);
        //     active_log($description, 'host', $host['id']);
        // }
        return ['status'=>200, 'msg'=>lang_plugins('success_message') ];
    }

    public function changeBillingCycle($param)
    {
        $hostId = $param['host']['id'];
        $custom = $param['custom'];

        $hostLink = $this
                ->where('host_id', $hostId)
                ->find();
        if(empty($hostLink)){
            return false;
        }
        $relId = $hostLink['rel_id'];
        $configData = json_decode($hostLink['config_data'], true);
        $IdcsmartCloud = new IdcsmartCloud($param['server']);

        // 转包年包月
        if($custom['new_billing_cycle'] == 'recurring_prepayment'){
            $durationId = $custom['duration_id'];
            $duration = DurationModel::find($durationId);
            if(empty($duration)){
                return false;
            }
            // 目前只有带宽方式可以转,修改流量计费方式
            $configData['duration'] = $duration->toArray();

            $hostLink->save([
                'config_data' => json_encode($configData),
            ]);
        }else if($custom['new_billing_cycle'] == 'on_demand'){
            $configData['duration'] = [
                'id'        => 'on_demand',
                'name'      => '小时',
                'price'     => 0,
                'discount'  => 0,
                'num'       => 1,
                'unit'      => 'hour',
                'price_factor' => 1,
            ];
            $hostLink->save([
                'config_data' => json_encode($configData),
            ]);

            // 转按需,修改流量计费方式
            if(!empty($hostLink['rel_id'])){
                if(!empty($configData['line']) && $configData['line']['bill_type'] == 'bw'){
                    $IdcsmartCloud->updateFlowSetting($hostLink['rel_id'], [
                        'flow_strategy' => 'statistics',
                        'traffic_type'  => 3,
                    ]);
                }
            }
        }

    }




    /**
     * @时间 2024-08-16
     * @title 获取VPC可分配产品列表
     * @desc  获取VPC可分配产品列表
     * @author hh
     * @param   int param.page 1 页数
     * @param   int param.limit 20 每页条数
     * @param   int param.data_center_id - 数据中心ID
     * @param   string param.keywords - 关键字搜索
     * @return  int list[].id - 产品ID
     * @return  int list[].name - 产品ID
     * @return  int count - 总条数
     */
    public function enableVpcHost($param)
    {
        $result = [
            'list'  => [],
            'count' => 0,
        ];

        $clientId = get_client_id();

        if(empty($clientId)){
            return $result;
        }
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');

        $where = [];
        $whereOr = [];

        $where[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', 'IN', ['Active','Grace']];
        $where[] = ['h.is_delete', '=', 0];
        $where[] = ['hl.vpc_network_id', '>', 0];

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['hl.data_center_id', '=', $param['data_center_id']];
        }
        if(isset($param['keywords']) && $param['keywords'] !== ''){
            $where[] = ['hl.name', 'LIKE', '%'.$param['keywords'].'%'];
        }

        $list = $this
                ->alias('hl')
                ->field('h.id,h.name')
                ->join('host h', 'hl.host_id=h.id')
                ->where($where)
                ->select()
                ->toArray();

        $count = $this
                ->alias('hl')
                ->join('host h', 'hl.host_id=h.id')
                ->where($where)
                ->count();

        $result['list'] = $list;
        $result['count'] = $count;

        return $result;
    }



    /* hook */

    /**
     * 时间 2023-11-16
     * @title 获取产品转移信息
     * @desc  获取产品转移信息
     * @author hh
     * @version v1
     */
    public function hostTransferInfo($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(!empty($hostLink) && !empty($hostLink['rel_id'])){
            $hostId = $param['host']['id'];
            $hasMfCloudIpServer = ServerModel::where('module', 'mf_cloud_ip')->value('id');
            $hasMfCloudDiskServer = ServerModel::where('module', 'mf_cloud_disk')->value('id');

            $where = [];
            $where[] = ['hl.rel_host_id', '=', $hostId];
            $where[] = ['h.is_delete', '=', 0];
            
            $linkHost = [];
            // 是否有弹性IP
            if($hasMfCloudIpServer && class_exists('server\mf_cloud_ip\model\HostLinkModel')){
                $linkMfCloudIpHost = \server\mf_cloud_ip\model\HostLinkModel::alias('hl')
                                    ->field('h.id,h.name,h.notes,p.name product_name')
                                    ->join('host h', 'hl.host_id=h.id')
                                    ->leftJoin('product p', 'h.product_id=p.id')
                                    ->where($where)
                                    ->select()
                                    ->toArray();
                $linkHost = array_merge($linkHost, $linkMfCloudIpHost);
            }
            // 是否有独立磁盘
            if($hasMfCloudDiskServer && class_exists('server\mf_cloud_disk\model\HostLinkModel')){
                $linkMfCloudDiskHost = \server\mf_cloud_disk\model\HostLinkModel::alias('hl')
                                    ->field('h.id,h.name,h.notes,p.name product_name')
                                    ->join('host h', 'hl.host_id=h.id')
                                    ->leftJoin('product p', 'h.product_id=p.id')
                                    ->where($where)
                                    ->select()
                                    ->toArray();
                $linkHost = array_merge($linkHost, $linkMfCloudDiskHost);
            }

            if(!empty($linkHost)){
                $data = [
                    'link_host' => $linkHost,
                    'transfer'  => true,
                    'tip'       => lang_plugins('host_transfer_host_link_other_host_transfer_will_transfer_all'),
                ];
                return ['status'=>200, 'data'=>$data ];
            }
        }
    }

    /**
     * 时间 2023-11-16
     * @title 产品转移
     * @desc  产品转移
     * @author hh
     * @version v1
     */
    public function hostTransfer($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $vpcIps = '';
        $vpcName = '';
        $oldVpcNetwork = [];
        $newVpcNetwork = [];
        $oldSecurityGroup = [];
        $newSecurityGroup = [];

        $DownstreamCloudLogic = new DownstreamCloudLogic($param['module_param']['host']);
        if($DownstreamCloudLogic->isDownstream()){
            // 非同步方式不走这里
            if(!$DownstreamCloudLogic->isDownstreamSync()){
                return [];
            }

            // 目标用户增加相同vpc
            if($hostLink['vpc_network_id'] > 0){
                $oldVpcNetwork = VpcNetworkModel::find($hostLink['vpc_network_id']);
                if(!empty($oldVpcNetwork)){
                    $vpcIps = $oldVpcNetwork['ips'];
                    $vpcName = 'VPC-'.rand_str(8);

                    $vpcData = [
                        'product_id'        => $oldVpcNetwork['product_id'],
                        'data_center_id'    => $oldVpcNetwork['data_center_id'],
                        'name'              => $oldVpcNetwork['name'],
                        'vpc_name'          => $vpcName,
                        'ips'               => $oldVpcNetwork['ips'],
                        'client_id'         => $param['target_client']['id'],
                        'create_time'       => time(),
                        'upstream_id'       => 0,
                    ];

                    $dataCenter = DataCenterModel::find($oldVpcNetwork['data_center_id']);
                    if(empty($dataCenter) || $dataCenter['product_id'] != $oldVpcNetwork['product_id']){
                        return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
                    }

                    $DownstreamProductLogic = new DownstreamProductLogic($param['module_param']['product']);
                    $path = sprintf('console/v1/product/%d/mf_cloud/vpc_network', $DownstreamProductLogic->upstreamProductId);
                    $post = [
                        'data_center_id'    => $dataCenter['upstream_id'],
                        'name'              => $vpcData['name'],
                        'ips'               => $vpcData['ips'],
                    ];
                    $res = $DownstreamProductLogic->curl($path, $post, 'POST');
                    if($res['status'] != 200){
                        return $res;
                    }
                    $vpcData['upstream_id'] = $res['data']['id'];
                    
                    $vpc = VpcNetworkModel::create($vpcData);

                    // 切换VPC
                    $DownstreamCloudLogic->setTimeout(600);
                    $res = $DownstreamCloudLogic->changeVpcNetwork([
                        'vpc_network_id'    => $vpcData['upstream_id'],
                    ]);

                    if($res['status'] != 200){
                        return $res;
                    }
                    
                    hostLinkModel::where('host_id', $hostLink['host_id'])->update(['vpc_network_id'=>$vpc['id']]);
                }
            }
        }else{
            // 目标用户增加相同vpc
            if($hostLink['vpc_network_id'] > 0){
                $oldVpcNetwork = VpcNetworkModel::find($hostLink['vpc_network_id']);
                if(!empty($oldVpcNetwork)){
                    $vpcIps = $oldVpcNetwork['ips'];
                    $vpcName = 'VPC-'.rand_str(8);

                    $newVpcNetwork = VpcNetworkModel::create([
                        'product_id'    => $oldVpcNetwork['product_id'],
                        'data_center_id'=> $oldVpcNetwork['data_center_id'],
                        'name'          => $oldVpcNetwork['name'],
                        'client_id'     => $param['target_client']['id'],
                        'ips'           => $vpcIps,
                        'rel_id'        => 0,
                        'vpc_name'      => $vpcName,
                        'create_time'   => time(),
                    ]);

                    $this->where('id', $hostLink['id'])->update(['vpc_network_id'=>$newVpcNetwork->id]);
                }
            }
            // 目标用户增加相同安全组
            $addon = PluginModel::where('name', 'IdcsmartCloud')->where('module', 'addon')->where('status',1)->find();
            if(!empty($addon) && class_exists('addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel')){
                $securityGroupHostLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $param['host']['id'])->find();
                if(!empty($securityGroupHostLink)){
                    $oldSecurityGroup = IdcsmartSecurityGroupModel::find($securityGroupHostLink['addon_idcsmart_security_group_id']);
                    if(!empty($oldSecurityGroup)){
                        $newSecurityGroup = IdcsmartSecurityGroupModel::create([
                            'client_id'     => $param['target_client']['id'],
                            'type'          => $oldSecurityGroup['type'],
                            'name'          => $oldSecurityGroup['name'],
                            'description'   => $oldSecurityGroup['description'],
                            'create_time'   => time(),
                        ]);

                        $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $securityGroupHostLink['addon_idcsmart_security_group_id'])->select()->toArray();
                        $securityGroupRuleArr = [];
                        foreach($securityGroupRule as $v){
                            $v['addon_idcsmart_security_group_id'] = $newSecurityGroup->id;
                            $v['create_time'] = time();
                            unset($v['id'], $v['update_time']);
                            $securityGroupRuleArr[] = $v;
                        }

                        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();
                        if(!empty($securityGroupRuleArr)){
                            $IdcsmartSecurityGroupRuleModel->insertAll($securityGroupRuleArr);
                        }
                        IdcsmartSecurityGroupHostLinkModel::where('host_id', $param['host']['id'])->update(['addon_idcsmart_security_group_id'=>$newSecurityGroup->id]);
                    }
                }
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>200, 'msg'=>lang_plugins('success_message')];
            }
            $IdcsmartCloud = new IdcsmartCloud($param['module_param']['server']);
            $serverHash = ToolLogic::formatParam($param['module_param']['server']['hash']);

            $prefix = $serverHash['user_prefix'] ?? '';
            $username = $prefix.$param['target_client']['id'];
            
            $userData = [
                'username'  => $username,
                'email'     => $param['target_client']['email'] ?: '',
                'status'    => 1,
                'real_name' => $param['target_client']['username'] ?: '',
                'password'  => rand_str()
            ];
            $IdcsmartCloud->userCreate($userData);
            $userCheck = $IdcsmartCloud->userCheck($username);
            if($userCheck['status'] != 200){
                return $userCheck;
            }
            $res = $IdcsmartCloud->cloudChangeUser($hostLink['rel_id'], [
                'uid'       => $userCheck['data']['id'],
                'vpc_ips'   => $vpcIps,
            ]);
            if($res['status'] != 200){
                return $res;
            }
            set_time_limit(300);
            $taskid = $res['data']['taskid'];
            // wait for task complete 3min
            for($i = 0; $i<36; $i++){
                $res = $IdcsmartCloud->taskDetail($taskid);
                if($res['status'] == 200){
                    if(in_array($res['data']['status'], [0,1])){
                        
                    }else if($res['data']['status'] == 2){
                        break;
                    }else{
                        // 失败了
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_remote_transfer_fail_please_read_log_on_zjmf_cloud')];
                    }
                }
                sleep(5);
            }
            // 当有安全组/VPC时需要获取详情保存关联关系
            if((!empty($newVpcNetwork) && isset($newVpcNetwork['id'])) || (!empty($newSecurityGroup) && isset($newSecurityGroup['id']))){
                $detail = $IdcsmartCloud->cloudDetail($hostLink['rel_id']);
                if($detail['status'] == 200 && $detail['data']['user_id'] == $userCheck['data']['id']){
                    $vpcId = $detail['data']['network'][0]['vpc'];
                    if(!empty($vpcId)){
                        VpcNetworkModel::where('id', $newVpcNetwork['id'])->update(['rel_id'=>$vpcId]);

                        // 魔方云VPC不使用时会删除,检查是否还在使用
                        if($oldVpcNetwork['rel_id'] > 0){
                            $remoteOldVpc = $IdcsmartCloud->vpcNetworkDetail($oldVpcNetwork['rel_id']);
                            if($remoteOldVpc['status'] != 200){
                                VpcNetworkModel::where('id', $oldVpcNetwork['id'])->update(['rel_id'=>0]);
                            }
                        }
                    }
                    $securityId = $detail['data']['security'];
                    if(!empty($newSecurityGroup) && isset($newSecurityGroup['id']) && !empty($securityId)){
                        IdcsmartSecurityGroupLinkModel::where('server_id', $param['module_param']['server']['id'])->where('security_id', $securityId)->delete();
                        IdcsmartSecurityGroupLinkModel::create([
                            'addon_idcsmart_security_group_id'  => $newSecurityGroup['id'],
                            'server_id'                         => $param['module_param']['server']['id'],
                            'security_id'                       => $securityId,
                            'type'                              => $hostLink['type'],
                        ]);

                        // 获取安全组规则
                        $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $newSecurityGroup['id'])->column('id');
                        if(!empty($securityGroupRule)){
                            $remoteSecurityGroup = $IdcsmartCloud->securityGroupDetail($securityId);
                            if($remoteSecurityGroup['status'] == 200){
                                foreach($remoteSecurityGroup['data']['rule'] as $k=>$v){
                                    if(isset($securityGroupRule[$k])){
                                        IdcsmartSecurityGroupRuleLinkModel::where('server_id', $param['module_param']['server']['id'])->where('security_rule_id', $v['id'])->delete();
                                        IdcsmartSecurityGroupRuleLinkModel::create([
                                            'addon_idcsmart_security_group_rule_id' => $securityGroupRule[$k],
                                            'server_id'                             => $param['module_param']['server']['id'],
                                            'security_rule_id'                      => $v['id'],
                                            'type'                                  => $hostLink['type'],
                                        ]);
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
        if(!empty($param['link_host'])){
            $HostTransferLogModel = new \addon\host_transfer\model\HostTransferLogModel();
            $HostTransferLogModel->transferLinkHost($param['link_host'], $param['module_param']['client'], $param['target_client']);
        }

        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2023-11-16
     * @title 产品转移之前
     * @desc  产品转移之前
     * @author hh
     * @version v1
     */
    public function beforeHostTransfer($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink) || empty($hostLink['rel_id'])){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $IdcsmartCloud = new IdcsmartCloud($param['module_param']['server']);
        // 检查实例状态
        $res = $IdcsmartCloud->taskList([
            'page'      => 1,
            'per_page'  => 1,
            'status'    => 1,
            'cloud'     => $hostLink['rel_id'],
            'rel_type'  => 'cloud',
        ]);
        if($res['status'] != 200){
            return $res;
        }
        if(!empty($res['data'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_operate_cannot_transfer')];
        }
    }

    /**
     * 时间 2023-09-26
     * @title 是否启用手动资源插件
     * @desc 是否启用手动资源插件
     * @author theworld
     * @version v1
     * @return  bool
     */
    public function isEnableManualResource()
    {
        $plugin = PluginModel::where('name', 'ManualResource')->where('status', 1)->where('module', 'addon')->find();
        return !empty($plugin);
    }

    /**
     * @时间 2024-08-19
     * @title 获取产品基础信息
     * @desc  获取产品基础信息
     * @author hh
     * @version v1
     * @param   array configData - config_data储存内容 require
     * @return  string
     */
    public function formatBaseInfo($configData){
        $data = [
            'cpu'               => $configData['cpu']['value'] . lang_plugins('mf_cloud_core'),
            'memory'            => $configData['memory']['value'] . str_replace('B', '', $configData['memory_unit'] ?? 'G'),
            'system_disk_size'  => $configData['system_disk']['value'] . 'G',
        ];
        if (isset($configData['gpu_name']) && isset($configData['gpu_num'])){
            $data['gpu'] = $configData['gpu_name'] . '*' . $configData['gpu_num'];
        }
        return implode('-', $data);
    }

    /**
     * @时间 2025-01-20
     * @title 在代理防火墙IP同步后
     * @desc  在代理防火墙IP同步后
     * @author hh
     * @version v1
     */
    public function afterCreateFirewallAgentHostIp($param)
    {
        $hostLink = $this->where('host_id', $param['host_id'])->find();
        if(empty($hostLink)){
            return false;
        }
        $data = [];
        foreach($param['host_ip'] as $v){
            $data[] = [
                'host_id'   => $param['host_id'],
                'ip'        => $v['ip'],
                'defence'   => $param['firewall_type'] . '_' . $v['defence_rule_id'],
            ];
        }

        $IpDefenceModel = new IpDefenceModel();
        $IpDefenceModel->where('host_id', $param['host_id'])->delete();
        $IpDefenceModel->insertAll($data);
    }

}
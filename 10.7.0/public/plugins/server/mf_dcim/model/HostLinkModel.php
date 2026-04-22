<?php 
namespace server\mf_dcim\model;

use app\common\model\UpgradeModel;
use server\mf_dcim\logic\CloudLogic;
use think\Model;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\idcsmart_dcim\Dcim;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;
use app\common\model\HostIpModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\HostAdditionModel;
use server\mf_dcim\logic\DownstreamCloudLogic;

/**
 * @title 产品关联模型
 * @use server\mf_dcim\model\HostLinkModel
 */
class HostLinkModel extends Model
{
	protected $name = 'module_mf_dcim_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'host_id'           => 'int',
        'rel_id'            => 'int',
        'data_center_id'    => 'int',
        'image_id'          => 'int',
        'package_id'        => 'int',
        'power_status'      => 'string',
        'ip'                => 'string',
        'additional_ip'     => 'string',
        'password'          => 'string',
        'config_data'       => 'string',
        'reset_flow_time'   => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
        'parent_host_id'    => 'int',
    ];

    /**
     * 时间 2022-06-30
     * @title 详情
     * @desc 详情
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.order_id - 订单ID
     * @return  string data.ip - IP地址
     * @return  string data.additional_ip - 附加IP(英文逗号分割)
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.model_config.id - 型号配置ID
     * @return  string data.model_config.name - 型号配置名称
     * @return  string data.model_config.cpu - 处理器
     * @return  string data.model_config.cpu_param - 处理器参数
     * @return  string data.model_config.memory - 内存
     * @return  string data.model_config.disk - 硬盘
     * @return  string data.model_config.gpu - 显卡
     * @return  int data.model_config.optional_memory[].id - 可选配内存配置ID
     * @return  string data.model_config.optional_memory[].value - 名称
     * @return  int data.model_config.optional_memory[].other_config.memory_slot - 槽位
     * @return  int data.model_config.optional_memory[].other_config.memory - 内存大小(GB)
     * @return  int data.model_config.optional_disk[].id - 可选配硬盘配置ID
     * @return  string data.model_config.optional_disk[].value - 名称
     * @return  int data.model_config.optional_gpu[].id - 可选配显卡配置ID
     * @return  string data.model_config.optional_gpu[].value - 名称
     * @return  int data.model_config.leave_memory - 当前机型剩余内存大小(GB)
     * @return  int data.model_config.max_memory_num - 当前机型可增加内存数量
     * @return  int data.model_config.max_disk_num - 当前机型可增加硬盘数量
     * @return  int data.model_config.max_gpu_num - 当前机型可增加显卡数量
     * @return  int data.line.id - 线路
     * @return  string data.line.name - 线路名称
     * @return  string data.line.bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int data.line.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string data.bw - 带宽(0表示没有)
     * @return  string data.bw_show - 带宽自定义显示
     * @return  string data.ip_num - IP数量
     * @return  string data.peak_defence - 防御峰值
     * @return  string data.username - 用户名
     * @return  string data.password - 密码
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.area - 区域
     * @return  string data.data_center.country - 国家
     * @return  string data.data_center.iso - 国家代码
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  int data.image.image_group_id - 镜像分类ID
     * @return  string data.image.image_group_name - 镜像分类
     * @return  string data.image.icon - 图标
     * @return  int data.config.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int data.config.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int data.config.manual_resource - 是否手动资源(0=不启用,1=启用)
     * @return  string data.config.manual_resource_control_mode - 手动资源控制方式not_support不支持,ipmi,dcim_client客户端
     * @return  array data.optional_memory - 当前机器已添加内存配置(["5"=>1],5是ID,1是数量)
     * @return  array data.optional_disk - 当前机器已添加硬盘配置(["5"=>1],5是ID,1是数量)
     * @return  array data.optional_gpu - 当前机器已添加显卡配置(["5"=>1],5是ID,1是数量)
     * @return  array data.custom_show - 自定义展示字段
     * @return  string data.custom_show[].name - 字段名称
     * @return  string data.custom_show[].type - 字段类型(text=文本,password=密码,date=日期)
     * @return  string data.custom_show[].value - 值
     */
    public function detail($hostId)
    {
        $res = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => (object)[],
        ];
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete'] ){
            return $res;
        }
        if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
            return $res;
        }
        $hostLink = $this->where('host_id', $hostId)->find();

        if (!empty($hostLink['parent_host_id'])){
            return $res;
        }

        if(!empty($hostLink)){
            $HostIpModel = new HostIpModel();
            $hostIp = $HostIpModel->getHostIp([
                'host_id'   => $hostId,
            ]);

            $HostAdditionModel = new HostAdditionModel();
            $hostAddition = $HostAdditionModel->where('host_id', $hostId)->find();

            $configData = json_decode($hostLink['config_data'], true);
            $adminField = $this->getAdminField($configData);
            // if(!empty($hostLink['package_id'])){
                // $adminField['cpu'] = ToolLogic::packageConfigLanguage($adminField['cpu']);
                $adminField['memory'] = ToolLogic::packageConfigLanguage($adminField['memory']);
                $adminField['disk'] = ToolLogic::packageConfigLanguage($adminField['disk']);
                $adminField['gpu'] = ToolLogic::packageConfigLanguage($adminField['gpu'] ?? '');
            // }
            $modelConfig = ModelConfigModel::find($configData['model_config']['id'] ?? 0);

            $data = [];
            $data['order_id'] = $host['order_id'];
            $data['ip'] = $hostIp['dedicate_ip'];
            $data['additional_ip'] = $hostIp['assign_ip'];
            $data['power_status'] = $hostAddition['power_status'] ?? '';
            
            $data['model_config'] = [
                'id'                => $configData['model_config']['id'] ?? 0,
                'name'              => $adminField['model_name'],
                'cpu'               => $adminField['cpu'],
                'cpu_param'         => $adminField['cpu_param'],
                'memory'            => $adminField['memory'],
                'disk'              => $adminField['disk'],
                'gpu'               => $adminField['gpu'],
                'optional_memory'   => [],
                'optional_disk'     => [],
                'optional_gpu'      => [],
                'leave_memory'      => $modelConfig['leave_memory'] ?? 0,
                'max_memory_num'    => $modelConfig['max_memory_num'] ?? 0,
                'max_disk_num'      => $modelConfig['max_disk_num'] ?? 0,
                'max_gpu_num'       => $modelConfig['max_gpu_num'] ?? 0,
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

            $data['bw'] = $adminField['bw'];
            $data['bw_show'] = $data['bw'].'Mbps';  // 自定义显示
            if($data['bw'] == 'NC'){
                $data['bw_show'] = OptionModel::where('product_id', $host['product_id'])->where('rel_type', OptionModel::LINE_BW)->where('rel_id', $data['line']['id'])->where('value', 'NC')->value('value_show');
                if($data['bw_show'] === ''){
                    $data['bw_show'] = lang_plugins('mf_dcim_real_bw');
                }
            }
            if(isset($configData['flow'])){
                $data['flow'] = $adminField['flow'];
            }
            $data['ip_num'] = $adminField['ip_num'];

            if($data['line']['sync_firewall_rule']!=1){
                $data['peak_defence'] = $adminField['defence'];
            }
            
            $image = ImageModel::alias('i')
                    ->field('i.id,i.name,i.image_group_id,ig.name image_group_name,ig.icon')
                    ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
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
                'id'    => $dataCenter['id'],
                'city'  => $dataCenter['city'],
                'area'  => $dataCenter['area'],
            ];
            $country = CountryModel::find($dataCenter['country_id']);

            $language = get_client_lang();
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            $data['data_center']['country'] = $country[ $countryName ];
            $data['data_center']['iso'] = $country['iso'];
            
            $data['image'] = $image ?? (object)[];
            $data['config'] = ConfigModel::field('reinstall_sms_verify,reset_password_sms_verify,manual_resource,custom_rand_password_rule,default_password_length')->where('product_id', $host['product_id'])->find() ?? (object)[];

            $data['custom_show'] = [];
            if(isset($data['config']['manual_resource'])){
                $data['config']['manual_resource_control_mode'] = '';
                if($this->isEnableManualResource() && $data['config']['manual_resource']==1){
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

            // if(!empty($data['package']['id'])){
                $HostOptionLinkModel = new HostOptionLinkModel();
                $hostOption = $HostOptionLinkModel->getHostOptional($hostId);

                foreach($hostOption['optional_memory'] as $v){
                    $data['optional_memory'][ $v['option_id'] ] = $v['num'];
                }
                foreach($hostOption['optional_disk'] as $v){
                    $data['optional_disk'][ $v['option_id'] ] = $v['num'];
                }
                foreach($hostOption['optional_gpu'] as $v){
                    $data['optional_gpu'][ $v['option_id'] ] = $v['num'];
                }
                $data['optional_memory'] = $data['optional_memory'] ?? (object)[];
                $data['optional_disk'] = $data['optional_disk'] ?? (object)[];
                $data['optional_gpu'] = $data['optional_gpu'] ?? (object)[];
            // }

            if(!empty($modelConfig) && $modelConfig['support_optional'] == 1){
                if($data['model_config']['leave_memory'] > 0 && $data['model_config']['max_memory_num'] > 0){
                    $data['model_config']['optional_memory'] = ModelConfigOptionLinkModel::alias('mcol')
                        ->field('mcol.option_id id,o.value,o.other_config')
                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                        ->where('mcol.model_config_id', $modelConfig['id'])
                        ->where('mcol.option_rel_type', OptionModel::MEMORY)
                        ->withAttr('value', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'value' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['value'])){
                                $value = $multiLanguage['value'];
                            }
                            return $value;
                        })
                        ->withAttr('other_config', function($val){
                            return json_decode($val, true);
                        })
                        ->order('o.order,o.id', 'asc')
                        ->select()
                        ->toArray();
                }
                if($data['model_config']['max_disk_num'] > 0){
                    $data['model_config']['optional_disk'] = ModelConfigOptionLinkModel::alias('mcol')
                        ->field('mcol.option_id id,o.value')
                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                        ->where('mcol.model_config_id', $modelConfig['id'])
                        ->where('mcol.option_rel_type', OptionModel::DISK)
                        ->withAttr('value', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'value' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['value'])){
                                $value = $multiLanguage['value'];
                            }
                            return $value;
                        })
                        ->order('o.order,o.id', 'asc')
                        ->select()
                        ->toArray();
                }
                if($data['model_config']['max_gpu_num'] > 0){
                    $data['model_config']['optional_gpu'] = ModelConfigOptionLinkModel::alias('mcol')
                        ->field('mcol.option_id id,o.value')
                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                        ->where('mcol.model_config_id', $modelConfig['id'])
                        ->where('mcol.option_rel_type', OptionModel::GPU)
                        ->withAttr('value', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'value' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['value'])){
                                $value = $multiLanguage['value'];
                            }
                            return $value;
                        })
                        ->order('o.order,o.id', 'asc')
                        ->select()
                        ->toArray();
                }
            }

            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'model_config_name' => $data['model_config']['name'],
                    'cpu'               => $data['model_config']['cpu'],
                    'cpu_param'         => $data['model_config']['cpu_param'],
                    // 'memory'            => $data['model_config']['memory'],
                    // 'disk'              => $data['model_config']['disk'],
                    'line_name'         => $data['line']['name'],
                    'city'              => $data['data_center']['city'],
                    'area'              => $data['data_center']['area'],
                    // 'package_name'      => $data['package']['name'],
                ],
            ]);
            $data['model_config']['name'] = $multiLanguage['name'] ?? $data['model_config']['name'];
            $data['model_config']['cpu'] = $multiLanguage['cpu'] ?? $data['model_config']['cpu'];
            $data['model_config']['cpu_param'] = $multiLanguage['cpu_param'] ?? $data['model_config']['cpu_param'];
            // $data['model_config']['memory'] = $multiLanguage['memory'] ?? $data['model_config']['memory'];
            // $data['model_config']['disk'] = $multiLanguage['disk'] ?? $data['model_config']['disk'];
            $data['line']['name'] = $multiLanguage['line_name'] ?? $data['line']['name'];
            $data['data_center']['city'] = $multiLanguage['city'] ?? $data['data_center']['city'];
            $data['data_center']['area'] = $multiLanguage['area'] ?? $data['data_center']['area'];
            // $data['package']['name'] = $multiLanguage['package_name'] ?? $data['package']['name'];

            $res['data'] = $data;
        }
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
        if(empty($host) || $host['is_delete'] ){
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
                ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
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
     * @return  int line.bill_type - 线路类型(bw=带宽计费,flow=流量计费)
     */
    public function adminDetail($id)
    {
        $productId = HostModel::where('id', $id)->value('product_id');

        $data = [
            'image'     => [],
        ];
        // 获取镜像列表
        $ImageModel = new ImageModel();
        $image = $ImageModel->homeImageList([
            'product_id' => $productId,
        ]);
        $hostLink = HostLinkModel::where('host_id', $id)->find();
        $configData = json_decode($hostLink['config_data'] ?? '', true);

        $data['image'] = $image['data']['list'] ?? [];

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
     * @param   ServerModel $param.server - ServerModel实例
     * @param   HostModel $param.host - HostModel实例
     * @param   ProductModel $param.product - ProductModel实例
     */
    public function createAccount($param)
    {
        $Dcim = new Dcim($param['server']);

        $serverHash = ToolLogic::formatParam($param['server']['hash']);
        $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

        $hostId = $param['host']['id'];
        $productId = $param['product']['id'];

        $parentHost = $param['host'];

        // 开通参数
        $post = [];
        $post['user_id'] = $prefix . $param['client']['id'];
        
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_host_create_success')
            ];
        }
        if($config['data']['optional_host_auto_create'] == 0){
            $HostOptionLinkModel = new HostOptionLinkModel();
            $optional = $HostOptionLinkModel->hostHaveOptional($hostId);
            if($optional){
                return [
                    'status'=>400,
                    'msg'   =>lang_plugins('mf_dcim_optional_host_cannot_auto_create'),
                ];
            }
        }
        // 获取当前配置
        $hostLink = $this->where('host_id', $hostId)->find();
        if(!empty($hostLink) && $hostLink['rel_id'] > 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_already_created')];
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
                    $HostIpModel = new HostIpModel();
                    $hostIp = $HostIpModel->where('host_id',$hostLink['parent_host_id'])->find();
                    if(!empty($hostIp)){
                        $ips = explode(',', $hostIp['assign_ip']);
                        $ips[] = $hostIp['dedicate_ip'];
                        $ips = array_filter(array_unique($ips));
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
        }else{
            $adminField = $this->getAdminField($configData);

            $line = LineModel::find($configData['line']['id']);
            if(!empty($line)){
                $configData['line'] = $line->toArray();
            }

            if(!empty($hostLink['package_id'])){
                $package = PackageModel::find($hostLink['package_id']);
                if(empty($package)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
                }
                $post['server_group'] = $package['group_id'];
                $post['out_bw'] = $adminField['bw'] ?? $package['bw'];
                $post['in_bw'] = $adminField['in_bw'] ?? $package['bw'];
                $post['limit_traffic'] = 0;

            }else{
                // 线路带宽
                if($configData['line']['bill_type'] == 'bw' && isset($configData['bw'])){
                    $optionBw = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::LINE_BW)->where('rel_id', $configData['line']['id'])->where(function($query) use ($configData) {
                        $query->whereOr('value', $configData['bw']['value'])
                            ->whereOr('(min_value<="'.$configData['bw']['value'].'" AND max_value>="'.$configData['bw']['value'].'")');
                    })->find();
                    if(!empty($optionBw)){
                        $configData['bw']['other_config'] = json_decode($optionBw['other_config'], true);
                    }
                }
                $modelConfig = ModelConfigModel::find($configData['model_config']['id']);
                if(!empty($modelConfig)){
                    $configData['model_config'] = $modelConfig->toArray();
                }
                $post['server_group'] = $configData['model_config']['group_id'];

                $post['in_bw'] = $adminField['in_bw'] == '' ? $adminField['bw'] : $adminField['in_bw'];
                $post['out_bw'] = $adminField['bw'];
                $post['limit_traffic'] = $adminField['flow'] ?? 0;
            }
            // 带宽NO_CHANGE判断
            if($post['in_bw'] == 'NC' || $post['in_bw'] == 'NO_CHANGE'){
                $post['in_bw'] = 'NO_CHANGE';
            }
            if($post['out_bw'] == 'NC' || $post['out_bw'] == 'NO_CHANGE'){
                $post['out_bw'] = 'NO_CHANGE';
            }
            $ipNum = $adminField['ip_num'];
            if(is_numeric($ipNum)){
                $post['ip_num'] = $ipNum;
            }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
                $post['ip_num'] = 'NO_CHANGE';
            }else{  //分组形式2_2,1_1  数量_分组id
                $ipNum = ToolLogic::formatDcimIpNum($ipNum);
                if($ipNum === false){
                    $result['status'] = 400;
                    $result['msg'] = lang_plugins('mf_dcim_custom_ip_num_format_error');
                    return $result;
                }
                $post['ip_num'] = $ipNum;
            }
            // 可以使用设置的IP分组
            if(is_numeric($post['ip_num'])){
                if($configData['line']['defence_enable'] == 1 && is_numeric($configData['line']['defence_ip_group']) && isset($configData['defence'])){
                    $ipGroup = $configData['line']['defence_ip_group'];
                }else if(is_numeric($configData['line']['bw_ip_group'])){
                    $ipGroup = $configData['line']['bw_ip_group'];
                }
                if(isset($ipGroup) && !empty($ipGroup)){
                    $post['ip_num'] = [$ipGroup => $post['ip_num']];
                }
            }
            $image = ImageModel::find($configData['image']['id']);
            if(!empty($image)){
                $configData['image'] = $image->toArray();
            }
            $post['os'] = $configData['image']['rel_image_id'];
            $post['hostid'] = $hostId;

            if($config['data']['rand_ssh_port'] == 1){
                $post['port'] = mt_rand(100, 65535);
            }

            $res = $Dcim->create($post);
            if($res['status'] == 200){
                $result = [
                    'status'=>200,
                    'msg'   =>lang_plugins('mf_dcim_host_create_success')
                ];

                $update = [];
                $update['rel_id'] = $res['data']['id'];

                $this->where('id', $hostLink['id'])->update($update);

                $dedicateIp = $res['data']['zhuip'] ?? '';

                $ips = explode("\r\n", $res['data']['ips']);
                foreach($ips as $k=>$v){
                    if($v == $dedicateIp){
                        unset($ips[$k]);
                    }else{
                        $ips[$k] = str_replace(',', '，', $v);
                    }
                }

                $assignIp = trim(implode(',', $ips), ',');

                // 保存IP信息
                $HostIpModel = new HostIpModel();
                $HostIpModel->hostIpSave([
                    'host_id'       => $param['host']['id'],
                    'dedicate_ip'   => $dedicateIp,
                    'assign_ip'     => $assignIp,
                    'write_log'     => false,
                ]);

                if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 1 && !empty($configData['defence']['defence']['firewall_type'])){

                    $subHosts = $this->where('parent_host_id',$param['host']['id'])->select()->toArray();
                    foreach ($subHosts as $subHost){
                        $host = HostModel::where('id',$subHost['host_id'])->find();
                        $param['host'] = $host;
                        $this->createAccount($param);
                    }
                    $param['host'] = $parentHost;

//                $hostIps = [];
//                $ips = explode(',', $assignIp);
//                $ips[] = $dedicateIp;
//                $ips = array_filter(array_unique($ips));
//                foreach ($ips as $v) {
//                    $hostIps[$v] = '';
//                }
//                $IpDefenceModel = new IpDefenceModel();
//                $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'], 'defence' => $configData['defence']['value'], 'ip' => $ips]);
//
//                hook('firewall_set_meal_modify', ['type' => $configData['defence']['firewall_type'], 'set_meal_id' => $configData['defence']['defence_rule_id'], 'host_ips' => $hostIps]);
                }

                $HostAdditionModel = new HostAdditionModel();
                $HostAdditionModel->hostAdditionSave($param['host']['id'], [
                    'power_status'    => 'on',
                    'password'        => $res['data']['password'],
                    'port'            => $res['data']['port'] ?? $post['port'] ?? '',
                ]);

                $ModelConfigModel = new ModelConfigModel();
                $ModelConfigModel->syncDcimStock();
            }else{
                $result = [
                    'status'=>400,
                    'msg'=>$res['msg'] ?: lang_plugins('mf_dcim_host_create_fail'),
                ];

                $HostAdditionModel = new HostAdditionModel();
                $HostAdditionModel->hostAdditionSave($param['host']['id'], [
                    'power_status'    => 'fault',
                    'port'            => $post['port'] ?? '',
                ]);
            }
            return $result;
        }
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_suspend_success')
            ];
        }
        $hostLink = HostLinkModel::where('host_id', $param['host']['id'])->find();

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

//            hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'default_defence_id'=>$orderDefaultDefenceId]);
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
            return ['status'=>200, 'msg'=>lang_plugins('mf_dcim_suspend_success')];
        }

        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->suspend(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('mf_dcim_suspend_success'),
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=> $res['msg'] ?? lang_plugins('mf_dcim_suspend_fail'),
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_unsuspend_success')
            ];
        }

        $hostLink = HostLinkModel::where('host_id', $param['host']['id'])->find();
        // 子产品解除暂停后，添加防御
        if (!empty($hostLink['parent_host_id'])){
            $defence = json_decode($hostLink['config_data'], true)['defence']??[];
            $firewallType = $defence['firewall_type']??'';
            hook('firewall_set_meal_modify',['type'=>$firewallType,'host_ips'=>[$hostLink['ip']=>''],'set_meal_id'=>$defence['defence_rule_id']??0]);
            HostModel::where('id',$param['host']['id'])->update([
                'status'         => 'Active',
                'suspend_reason' => '',
                'suspend_time'   => 0,
                'update_time'    => time(),
            ]);
            return ['status'=>200, 'msg'=>lang_plugins('mf_dcim_unsuspend_success')];
        }
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->unsuspend(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('mf_dcim_unsuspend_success'),
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('mf_dcim_unsuspend_fail'),
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
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
            return ['status'=>200, 'msg'=>lang_plugins('delete_success')];
        }
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            $result = [
                'status'    => 200,
                'msg'       => lang_plugins('delete_success'),
            ];
            return $result;
            // return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->delete(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            // 删除子产品防御
            $subHostIds = $this->where('parent_host_id',$param['host']['id'])->column('host_id');
            foreach ($subHostIds as $subHostId){
                $host = HostModel::find($subHostId);
                $subParam = $param;
                $subParam['host'] = $host;
                $this->terminateAccount($subParam);
            }
            $configData = json_decode($hostLink['config_data'], true);

            $HostIpModel = new HostIpModel();
            $hostIp = $HostIpModel->getHostIp([
                'host_id'   => $param['host']['id'],
            ]);

            $notes = [
                '产品标识：'.$param['host']['name'],
                'IP地址：'.$hostIp['dedicate_ip'],
                '操作系统：'.$configData['image']['name'],
                'ID：'.$hostLink['rel_id']
            ];
            $this->where('host_id', $param['host']['id'])->update(['rel_id'=>0]);

            HostModel::where('id', $param['host']['id'])->update(['notes'=>$param['host']['notes'] . "\r\n" . implode("\r\n", $notes)]);

            $result = [
                'status'=>200,
                'msg'=>lang_plugins('delete_success'),
            ];

        }else{
            $result = [
                'status'=>400,
                'msg'=> $res['msg'] ?? lang_plugins('delete_fail'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2024-02-18
     * @title 续费订单支付后调用
     * @desc 续费订单支付后调用
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
            }else{
                $this->syncAccount($param);

                $configData = json_decode($hostLink['config_data'], true);

                // 获取当前周期
                $duration = DurationModel::where('product_id', $productId)->where('name', $param['host']['billing_cycle_name'])->find();
                if(!empty($duration)){
                    $configData['duration'] = $duration;
                    $this->where('host_id', $hostId)->update(['config_data'=>json_encode($configData)]);
                }

                $ConfigModel = new ConfigModel();
                $config = $ConfigModel->indexConfig(['product_id'=>$productId ]);
                // 手动资源
                if(isset($config['data']['manual_resource']) && $config['data']['manual_resource'] == 1 && $this->isEnableManualResource()){
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
            }
        }
    }

    /**
     * 时间 2022-06-28
     * @title 升降级后调用
     * @author hh
     * @version v1
     */
    public function changePackage($param)
    {
        try {
            // 判断是什么类型
            if(!isset($param['custom']['type'])){
                return ['status'=>400, 'msg'=>lang_plugins('param_error')];
            }
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

                    $description = lang_plugins('mf_dcim_log_downstream_upgrade_config_complete', [
                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                        '{act}'     => lang_plugins('mf_dcim_upgrade_buy_image'),
                        '{param}'   => json_encode($post),
                        '{msg}'     => $result['msg'] ?? '',
                    ]);

                    active_log($description, 'host', $param['host']['id']);
                }else if($custom['type'] == 'upgrade_common_config'){
                    $hostLink = $this->where('host_id', $hostId)->find();

                    $configData = json_decode($hostLink['config_data'], true);
                    $oldAdminField = $this->getAdminField($configData);
                    $adminField = $oldAdminField;

                    $oldConfigData = $configData;
                    $newConfigData = $custom['new_config_data'];
                    foreach($newConfigData as $k=>$v){
                        $configData[$k] = $v;
                    }
                    $newAdminField = $custom['new_admin_field'] ?? [];
                    foreach($newAdminField as $k=>$v){
                        $adminField[$k] = $v;
                    }
                    $configData['admin_field'] = $adminField;

                    // 保存新的配置
                    $update = [
                        'config_data' => json_encode($configData),
                    ];
                    $this->update($update, ['host_id'=>$hostId]);

                    HostModel::where('id', $hostId)->update([
                        'base_info'     => $this->formatBaseInfo($configData),
                    ]);

                    if(isset($custom['optional'])){
                        HostOptionLinkModel::where('host_id', $hostId)->delete();

                        if(!empty($custom['optional'])){
                            $HostOptionLinkModel = new HostOptionLinkModel();
                            $HostOptionLinkModel->insertAll($custom['optional']);
                        }
                    }

                    $post = array_filter([
                        'ip_num'       => $configData['admin_field']['ip_num'] ?? $configData['ip']['value'] ?? NULL,
                        'bw'           => $configData['admin_field']['bw'] ?? $configData['bw']['value'] ?? NULL,
                        'flow'         => $configData['admin_field']['flow'] ?? $configData['flow']['value'] ?? NULL,
                        'peak_defence' => $configData['admin_field']['defence'] ?? $configData['defence']['value'] ?? NULL,
                    ]);
                    if(isset($custom['optional'])){
                        $post['optional_memory'] = [];
                        $post['optional_disk'] = [];
                        $post['optional_gpu'] = [];

                        if(!empty($custom['optional'])){
                            foreach($custom['optional'] as $v){
                                $option = OptionModel::where('id', $v['option_id'])->find();
                                if(!empty($option)){
                                    if($option['rel_type'] == OptionModel::MEMORY){
                                        $post['optional_memory'][ $option['upstream_id'] ] = $v['num'];
                                    }else if($option['rel_type'] == OptionModel::DISK){
                                        $post['optional_disk'][ $option['upstream_id'] ] = $v['num'];
                                    }else if($option['rel_type'] == OptionModel::GPU){
                                        $post['optional_gpu'][ $option['upstream_id'] ] = $v['num'];
                                    }
                                }
                            }
                        }
                    }

                    $result = $DownstreamCloudLogic->upgradeCommonConfig($post,$orderId);

                    $description = lang_plugins('mf_dcim_log_downstream_upgrade_config_complete', [
                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                        '{act}'     => lang_plugins('mf_dcim_upgrade_common_config'),
                        '{param}'   => json_encode($post),
                        '{msg}'     => $result['msg'] ?? '',
                    ]);
                    active_log($description, 'host', $param['host']['id']);
                }else if($custom['type'] == 'upgrade_defence'){
                    // 升级防御
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

                    $description = lang_plugins('log_mf_dcim_downstream_upgrade_config_complete', [
                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                        '{act}'     => lang_plugins('mf_dcim_host_upgrade_ip_defence'),
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
            }
            else if($custom['type'] == 'upgrade_common_config'){
                $hostLink = $this->where('host_id', $hostId)->find();

                $configData = json_decode($hostLink['config_data'], true);
                $oldAdminField = $this->getAdminField($configData);
                $adminField = $oldAdminField;

                $oldConfigData = $configData;
                $newConfigData = $custom['new_config_data'];
                foreach($newConfigData as $k=>$v){
                    $configData[$k] = $v;
                }
                $newAdminField = $custom['new_admin_field'] ?? [];
                foreach($newAdminField as $k=>$v){
                    $adminField[$k] = $v;
                }
                $configData['admin_field'] = $adminField;

                // 保存新的配置
                $update = [
                    'config_data' => json_encode($configData),
                ];
                $this->update($update, ['host_id'=>$hostId]);

                HostModel::where('id', $hostId)->update([
                    'base_info'     => $this->formatBaseInfo($configData),
                ]);

                if(isset($custom['optional'])){
                    HostOptionLinkModel::where('host_id', $hostId)->delete();

                    if(!empty($custom['optional'])){
                        $HostOptionLinkModel = new HostOptionLinkModel();
                        $HostOptionLinkModel->insertAll($custom['optional']);
                    }
                }

                $ConfigModel = new ConfigModel();
                $config = $ConfigModel->indexConfig(['product_id'=>$productId ]);

                if($config['data']['manual_resource']==1){
                    return ['status'=>200];
                }

                $id = $hostLink['rel_id'] ?? 0;
                if(empty($id)){
                    $description = lang_plugins('mf_dcim_log_upgrade_config_fail_for_no_dcim_id');
                    active_log($description, 'host', $hostId);
                    return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
                }
                $Dcim = new Dcim($param['server']);

                $description = [];
                // 有升降级IP
                if(isset($newConfigData['ip'])){
                    $ipGroup = 0;
                    // 获取下线路信息
                    $line = LineModel::find($configData['line']['id']);
                    if(!empty($line)){
                        if($line['defence_enable'] == 1 && !empty($adminField['defence'])){
                            $ipGroup = $line['defence_ip_group'];
                        }else if($line['bill_type'] == 'bw'){
                            $ipGroup = $line['bw_ip_group'];
                        }
                    }

                    $post = [];
                    $post['id'] = $id;

                    $ipNum = $newConfigData['ip']['value'];
                    if(is_numeric($ipNum)){
                        if(!empty($ipGroup)){
                            $post['ip_num'][ $ipGroup ] = $ipNum;
                        }else{
                            $post['ip_num'] = $ipNum;
                        }
                    }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
                        $post['ip_num'] = 'NO_CHANGE';
                    }else{  //分组形式2_2,1_1  数量_分组id
                        $ipNum = ToolLogic::formatDcimIpNum($ipNum);
                        // if($ipNum === false){
                        //     $result['status'] = 400;
                        //     $result['msg'] = 'IP数量格式有误';
                        //     return $result;
                        // }
                        $post['ip_num'] = $ipNum;
                    }
                    $post['ip'] = $custom['ip'] ?? [];
                    $res = $Dcim->modifyIpNum($post);
                    if($res['status'] == 200){
                        // 重新获取IP
                        $detail = $Dcim->detail(['id'=>$id]);
                        if($detail['status'] == 200){
                            // 保存IP信息
                            $HostIpModel = new HostIpModel();
                            $HostIpModel->hostIpSave([
                                'host_id'       => $hostId,
                                'dedicate_ip'   => $detail['server']['zhuip'] ?? '',
                                'assign_ip'     => trim(implode(',', $detail['ip']['ipaddress'] ?? []), ','),
                            ]);

                            $ips = $detail['ip']['ipaddress'] ?? [];
                            $ips[] = $detail['server']['zhuip'] ?? '';
                            $ips = array_unique($ips);

                            // 移出不存在的IP
                            $IpDefenceModel = new IpDefenceModel();
                            $IpDefenceModel->where('host_id', $param['host']['id'])->whereNotIn('ip', $ips)->delete();
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

//                            if(isset($custom['new_config_data']['default_defence']) && !empty($custom['new_config_data']['default_defence'])){
//                                $hostIps = [];
//                                $exist = $IpDefenceModel->where('host_id', $param['host']['id'])->column('ip');
//                                foreach ($ips as $v) {
//                                    if(!in_array($v, $exist)){
//                                        $hostIps[$v] = '';
//                                    }
//                                }
//
//                                $IpDefenceModel->saveDefence(['host_id' => $param['host']['id'],'defence' => $custom['new_config_data']['default_defence']['value'], 'ip' => array_keys($hostIps)]);
//
//                                hook('firewall_set_meal_modify', ['type' => $custom['new_config_data']['default_defence']['firewall_type'], 'set_meal_id' => $custom['new_config_data']['default_defence']['defence_rule_id'], 'host_ips' => $hostIps]);
//                            }
//                            if(isset($custom['ip']) && !empty($custom['ip'])){
//                                $hostIps = [];
//                                foreach ($custom['ip'] as $v) {
//                                    $hostIps[$v] = '';
//                                }
//                                hook('firewall_set_meal_modify', ['type' => $custom['new_config_data']['default_defence']['firewall_type'], 'set_meal_id' => $custom['new_config_data']['default_defence']['defence_rule_id'], 'host_ips' => $hostIps]);
//                            }
                        }
                        $description[] = lang_plugins('mf_dcim_upgrade_ip_num_success');
                    }else{
                        $description[] = lang_plugins('mf_dcim_upgrade_ip_num_fail') . $res['msg'];
                    }
                }
                // 带宽型,只变更带宽
                if($configData['line']['bill_type'] == 'bw'){
                    if(isset($newConfigData['bw'])){
                        $oldInBw = is_numeric($oldAdminField['in_bw']) ? $oldAdminField['in_bw'] : $oldAdminField['bw'];
                        $oldOutBw = $oldAdminField['bw'];

                        $newInBw = $configData['bw']['value'];
                        $newOutBw = $configData['bw']['value'];

                        if(is_numeric($configData['bw']['other_config']['in_bw'])){
                            $newInBw = $configData['bw']['other_config']['in_bw'];
                        }
                        // 修改带宽
                        if($oldInBw != $newInBw){
                            $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                            if($res['status'] == 200){
                                $description[] = lang_plugins('mf_dcim_upgrade_in_bw_success');
                            }else{
                                $description[] = lang_plugins('mf_dcim_upgrade_in_bw_fail') . $res['msg'];
                            }
                        }
                        if($oldOutBw != $newOutBw){
                            $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                            if($res['status'] == 200){
                                $description[] = lang_plugins('mf_dcim_upgrade_out_bw_success');
                            }else{
                                $description[] = lang_plugins('mf_dcim_upgrade_out_bw_fail') . $res['msg'];
                            }
                        }
                    }
                }else{
                    if(isset($newConfigData['flow'])){
                        // 流量型
                        $oldFlow = $oldAdminField['flow'];
                        $newFlow = $configData['flow']['value'];

                        if($oldFlow != $newFlow){
                            $post['id'] = $id;
                            $post['traffic'] = $newFlow;

                            $res = $Dcim->modifyFlowLimit($post);
                            if($res['status'] == 200){
                                $description[] = lang_plugins('mf_dcim_upgrade_flow_success');
                            }else{
                                $description[] = lang_plugins('mf_dcim_upgrade_flow_fail').$res['msg'];
                            }
                        }

                        $oldInBw = $oldAdminField['in_bw'];
                        $oldOutBw = $oldAdminField['bw'];

                        $newInBw = $configData['flow']['other_config']['in_bw'];
                        $newOutBw = $configData['flow']['other_config']['out_bw'];

                        // 修改带宽
                        if($oldInBw != $newInBw){
                            $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                            if($res['status'] == 200){
                                $description[] = lang_plugins('mf_dcim_upgrade_in_bw_success');
                            }else{
                                $description[] = lang_plugins('mf_dcim_upgrade_in_bw_fail').$res['msg'];
                            }
                        }
                        if($oldOutBw != $newOutBw){
                            $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                            if($res['status'] == 200){
                                $description[] = lang_plugins('mf_dcim_upgrade_out_bw_success');
                            }else{
                                $description[] = lang_plugins('mf_dcim_upgrade_out_bw_fail') . $res['msg'];
                            }
                        }

                        // 检查当前是否还超额
                        if($param['host']['status'] == 'Suspended' && $param['host']['suspend_type'] == 'overtraffic'){
                            $post = [];
                            $post['id'] = $id;
                            $post['hostid'] = $hostId;
                            $post['unit'] = 'GB';

                            $flow = $Dcim->flow($post);
                            if($flow['status'] == 200){
                                $data = $flow['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month' ];

                                $percent = str_replace('%', '', $data['used_percent']);

                                $total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
                                $used = round($total * $percent / 100, 2);
                                if($percent < 100){
                                    $unsuspendRes = $param['host']->unsuspendAccount($param['host']['id']);
                                    if($unsuspendRes['status'] == 200){
                                        $descrition[] = lang_plugins('mf_dcim_upgrade_flow_unsuspend_success', [
                                            '{total}'   => $total,
                                            '{used}'    => $used,
                                        ]);
                                    }else{
                                        $descrition[] = lang_plugins('mf_dcim_upgrade_flow_unsuspend_success', [
                                            '{total}'   => $total,
                                            '{used}'    => $used,
                                            '{reason}'  => $unsuspendRes['msg'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                $description = lang_plugins('mf_dcim_upgrade_config_complete') . implode(',', $description);
                active_log($description, 'host', $hostId);
            }
            else if($custom['type'] == 'upgrade_defence'){
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

                $description = lang_plugins('mf_dcim_log_upgrade_ip_defence_success', ['{host}' => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#']);
                active_log($description, 'host', $hostId);
            }
        }catch (\Exception $e){
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
        $clientId = !empty(get_admin_id()) ? HostModel::where('id', $param['host_id'])->value('client_id') : get_client_id();
        $hostId = $param['host_id'];
        
        $position = $param['position'] ?? 0;
        $configData = DurationModel::$configData[$position];
        // 子产品
        if (!empty($custom['parent_host_id'])){
            $parentHost = HostModel::where('client_id', $clientId)->find($custom['parent_host_id']);
            if(empty($parentHost)){
                throw new \Exception(lang_plugins('mf_dcim_parent_host_not_found'));
            }
            $parentHostLink = $this->where('host_id', $custom['parent_host_id'])->find();
            if(empty($parentHostLink)){
                throw new \Exception(lang_plugins('mf_dcim_parent_host_not_found'));
            }
            $position = $param['position'] ?? 0;
            $configData = DurationModel::$configData[$position];
            $configData = $configData['defence'] ?? $configData;
            $data = [
                'host_id'           => $param['host_id'],
                'data_center_id'    => $parentHostLink['data_center_id'] ?? 0,
                'image_id'          => $parentHostLink['image_id'],
                // 'power_status'      => 'on',
                'config_data'       => json_encode($configData),
                'create_time'       => time(),
                'package_id'        => 0,
                'additional_ip'     => '',
                'parent_host_id'    => $custom['parent_host_id'],
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
        }else{
            // 减少套餐试用库存
            if ($custom['duration_id']==config('idcsmart.pay_ontrial') && !empty($custom['model_config_id'])){
                ModelConfigModel::where('id', $custom['model_config_id'])
                    ->where('ontrial_stock_control',1)
                    ->dec('ontrial_qty',1)->update();
            }

            $data = [
                'host_id'           => $param['host_id'],
                'data_center_id'    => $custom['data_center_id'] ?? 0,
                'image_id'          => $custom['image_id'],
                // 'power_status'      => 'on',
                'config_data'       => json_encode($configData),
                'create_time'       => time(),
                'package_id'        => 0,
                'additional_ip'     => '',
            ];
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
            if(isset($configData['optional']) && !empty($configData['optional'])){
                $hostOption = [];
                foreach($configData['optional'] as $v){
                    $hostOption[] = [
                        'host_id'   => $param['host_id'],
                        'option_id' => $v['id'],
                        'num'       => $v['num'],
                    ];
                }
                $HostOptionLinkModel = new HostOptionLinkModel();
                $HostOptionLinkModel->insertAll($hostOption);
            }

            // 镜像是否收费
            if($configData['image']['charge'] == 1){
                $HostImageLinkModel = new HostImageLinkModel();
                $HostImageLinkModel->saveLink($param['host_id'], $configData['image']['id']);
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
                    ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
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
                ]);
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
        if (empty($host) || $host['is_delete'] ){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $productId = $host['product_id'];

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

                            $durationItem = [
                                'id' => $ratio2['id'],
                                'duration' => $ratio2['duration'],
                                'price' => $price,
                                'billing_cycle' => $ratio2['billing_cycle'],
                                'name_show' => $ratio2['name_show'],
                                'base_price' => $price,
                                'prr' => $ratio2['ratio']/$currentDurationRatio,
                                'prr_numerator' => $ratio2['ratio'],
                                'prr_denominator' => $currentDurationRatio,
                            ];
                            if($host['renew_use_current_client_level'] == 1){
                                $durationItem['client_level_discount'] = bcmul(1,round($currentDiscount*$priceFactorRatio*$ratio2['ratio']/$currentDurationRatio,2),2);
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

        $cycle = null;
        $price = null;
        if($ProductModel['pay_type'] == 'free'){
            $price = 0;
        }else if($ProductModel['pay_type'] == 'onetime'){
            $price = 0;
        }else{
            // 默认配置计算
            $DataCenterModel = new DataCenterModel();
            $orderPage = $DataCenterModel->orderPage([
                'product_id' => $productId,
            ]);

            // 获取镜像
            $ImageModel = new ImageModel();
            $homeImageList = $ImageModel->homeImageList([
                'product_id' => $productId,
            ]);
            
            $buyParam = [
                'id'                => $productId,
                'data_center_id'    => $orderPage['data_center'][0]['city'][0]['area'][0]['id'] ?? 0,
                'model_config_id'   => $orderPage['model_config'][0]['id'] ?? 0,
                'image_id'          => $homeImageList['data']['list'][0]['image'][0]['id'] ?? 0,
                'line_id'           => $orderPage['data_center'][0]['city'][0]['area'][0]['line'][0]['id'] ?? 0,
                'line_type'         => $orderPage['data_center'][0]['city'][0]['area'][0]['line'][0]['bill_type'] ?? '',
                'bw'                => '',
                'flow'              => '',
                'ip_num'            => '',
                'peak_defence'      => '',
            ];

            // 线路类型
            $lineType = '';
            if(!empty($buyParam['line_id'])){
                // 获取线路详情
                $LineModel = new LineModel();
                $homeLineConfig = $LineModel->homeLineConfig($buyParam['line_id']);

                $buyParam['line_type'] = $homeLineConfig['bill_type'];

                $lineType = $homeLineConfig['bill_type'];
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
                        if(isset($limitResult['model_config'])){
                            $match = $LimitRuleModel->limitRuleResultMatch(['model_config'=>$limitResult['model_config']], $buyParam, ['model_config']);
                            // 不可用,在寻找可用的
                            if(!$match){
                                foreach($orderPage['model_config'] as $vv){
                                    $match = $LimitRuleModel->limitRuleResultMatch(['model_config'=>$limitResult['model_config']], ['model_config_id'=>$vv['id']], ['model_config']);
                                    if($match){
                                        $buyParam['model_config_id'] = $vv['id'];
                                        $changeParam = true;
                                        break;
                                    }
                                }
                            }                            
                        }
                        if($lineType == 'bw' && isset($limitResult['bw'])){
                            $match = $LimitRuleModel->limitRuleResultMatch(['bw'=>$limitResult['bw']], $buyParam, ['bw']);
                            // 不可用,在寻找可用的
                            if(!$match){
                                if(isset($homeLineConfig['bw'])){
                                    foreach($homeLineConfig['bw'] as $vv){
                                        if($vv['type'] == 'radio'){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['bw'=>$limitResult['bw']], ['bw'=>$vv['value'],'line_type'=>'bw'], ['bw']);
                                            if($match){
                                                $buyParam['bw'] = $vv['value'];
                                                $changeParam = true;
                                                break;
                                            }
                                        }else{
                                            $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['bw'], $vv);
                                            if(!is_null($find['min_value'])){
                                                $buyParam['bw'] = $find['min_value'];
                                                $changeParam = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if($lineType == 'flow' && isset($limitResult['flow'])){
                            $match = $LimitRuleModel->limitRuleResultMatch(['flow'=>$limitResult['flow']], $buyParam, ['flow']);
                            // 不可用,在寻找可用的
                            if(!$match){
                                if(isset($homeLineConfig['flow'])){
                                    foreach($homeLineConfig['flow'] as $vv){
                                        if($vv['type'] == 'radio'){
                                            $match = $LimitRuleModel->limitRuleResultMatch(['flow'=>$limitResult['flow']], ['flow'=>$vv['value'],'line_type'=>'flow'], ['flow']);
                                            if($match){
                                                $buyParam['flow'] = $vv['value'];
                                                $changeParam = true;
                                                break;
                                            }
                                        }else{
                                            $find = $LimitRuleModel->getRuleResultUnionIntersectMin($limitResult['flow'], $vv);
                                            if(!is_null($find['min_value'])){
                                                $buyParam['flow'] = $find['min_value'];
                                                $changeParam = true;
                                                break;
                                            }
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
                        if(!empty($homeLineConfig)){
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
            $buyParam['set_price'] = 1;
            $duration = $DurationModel->getAllDurationPrice($buyParam);
            if($duration['status'] == 200 && isset($duration['data'])){
                foreach($duration['data'] as $v){
                    $price = $v['price'];
                    $cycle = $v['name_show'] ?? $v['name'];
                    break;
                }
            }
        }
        return ['price'=>$price, 'cycle'=>$cycle, 'product'=>$ProductModel];
    }

    /**
     * 时间 2024-02-18
     * @title 产品内页模块配置信息输出
     * @desc 产品内页模块配置信息输出
     * @author hh
     * @version v1
     */
    public function adminField($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return [];
        }

        $configData = json_decode($hostLink['config_data'], true);
        $adminField = $this->getAdminField($configData);

        $dataCenter = DataCenterModel::find($configData['data_center']['id'] ?? 0);
        if(!empty($dataCenter)){
            $configData['data_center'] = $dataCenter->toArray();
        }
        $line = LineModel::find($configData['line']['id'] ?? 0);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
            $syncFirewallRule = $line['sync_firewall_rule'];
        }else{
            $syncFirewallRule = $configData['line']['sync_firewall_rule'] ?? 0;
        }
        $image = ImageModel::find($hostLink['image_id']);
        $modelConfig = ModelConfigModel::find($configData['model_config']['id'] ?? 0);
        if(!empty($modelConfig)){
            $configData['model_config'] = $modelConfig->toArray();
        }

        $DataCenterModel = new DataCenterModel();

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        $HostAdditionModel = new HostAdditionModel();
        $hostAddition = HostAdditionModel::where('host_id', $param['host']['id'])->find();

        $data = [];
        
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
            $hostLink['config_data'] = !empty($hostLink) ? json_decode($hostLink['config_data'], true) : [];
            $configData['model_config'] = $hostLink['config_data']['model_config'];
            $image = $hostLink['config_data']['image'] ?? '';

            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_dcim_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('mf_dcim_data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'      => lang_plugins('mf_dcim_manual_resource'),
                        'key'       => 'manual_resource',
                        'value'     => !empty($manual_resource) ? ($manual_resource['dedicated_ip'].'('.$manual_resource['id'].')') : '',
                        'disable'   => true,
                    ],
                    [
                        'name'  => lang_plugins('mf_dcim_instance_username'),
                        'key'   => 'username',
                        'value' => $hostAddition['username'] ?? '',
                    ],
                    [
                        'name'  => lang_plugins('mf_dcim_instance_password'),
                        'key'   => 'password',
                        'value' => $hostAddition['password'] ?? '',
                    ],
                ],
            ];
        }else{
            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_dcim_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('mf_dcim_data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'              => lang_plugins('mf_dcim_server_id'),
                        'key'               => 'zjmf_dcim_id',
                        'value'             => $hostLink['rel_id'],
                        'url'               => !empty($hostLink['rel_id']) ? $param['server']['url'] . '/index.php?m=server&a=detailed&id='. $hostLink['rel_id'] : '',
                        'server_group_id'   => $configData['model_config']['group_id'] ?? 0,
                    ],
                    [
                        'name'  => lang_plugins('mf_dcim_instance_username'),
                        'key'   => 'username',
                        'value' => $hostAddition['username'] ?? '',
                    ],
                    [
                        'name'  => lang_plugins('mf_dcim_instance_password'),
                        'key'   => 'password',
                        'value' => $hostAddition['password'] ?? '',
                    ],
                ],
            ];
        }
        // 机型规格
        $data[] = [
            'name' => lang_plugins('mf_dcim_model_specification'),
            'field'=> [
                [
                    'name'      => lang_plugins('mf_dcim_model'),
                    'key'       => 'model_config_name',
                    'value'     => $adminField['model_name'],
                    'disable'   => false,
                ],
                [
                    'name'      => lang_plugins('mf_dcim_model_config_cpu'),
                    'key'       => 'model_config_cpu',
                    'value'     => $adminField['cpu'],
                    'disable'   => false,
                ],
            ],
        ];
        if(empty($hostLink['package_id'])){
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_model_config_cpu_param'),
                'key'       => 'model_config_cpu_param',
                'value'     => $adminField['cpu_param'],
                'disable'   => false,
            ];
        }
        $data[1]['field'][] = [
            'name'      => lang_plugins('mf_dcim_model_config_memory'),
            'key'       => 'model_config_memory',
            'value'     => $adminField['memory'],
            'disable'   => false,
        ];
        $data[1]['field'][] = [
            'name'      => lang_plugins('mf_dcim_model_config_disk'),
            'key'       => 'model_config_disk',
            'value'     => $adminField['disk'],
            'disable'   => false,
        ];
        $data[1]['field'][] = [
            'name'      => lang_plugins('mf_dcim_gpu'),
            'key'       => 'model_config_gpu',
            'value'     => $adminField['gpu'] ?? '',
            'disable'   => false,
        ];
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $images = ImageModel::where('product_id', $param['product']['id'])->select()->toArray();
            
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_image'),
                'key'       => 'image',
                'value'     => intval($image['id'] ?? 0),
                'disable'   => false,
                'options'   => $images,
            ];
        }else{
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_image'),
                'key'       => 'image',
                'value'     => $image['name'] ?? '',
                'disable'   => true,
            ];
        }
        // 网络配置
        $data[] = [
            'name' => lang_plugins('mf_dcim_network_config'),
            'field'=> [
                [
                    'name'      => lang_plugins('mf_dcim_line'),
                    'key'       => 'line',
                    'value'     => $configData['line']['name'] ?? '',
                    'disable'   => true,
                ],
            ],
        ];

        $data[2]['field'][] = [
            'name'      => lang_plugins('bw'),
            'key'       => 'bw',
            'value'     => $adminField['bw'],
        ];
        $data[2]['field'][] = [
            'name'      => lang_plugins('mf_dcim_line_bw_in_bw'),
            'key'       => 'in_bw',
            'value'     => $adminField['in_bw'],
        ];
        if(isset($configData['flow'])){
            $data[2]['field'][] = [
                'name'      => lang_plugins('mf_dcim_option_value_3'),
                'key'       => 'flow',
                'value'     => $adminField['flow'],
            ];
        }
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $assigned_ips = $manual_resource['assigned_ips'] ?? '';
            $assigned_ips = array_unique(explode("\n", $assigned_ips));
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_dcim_ip_num'),
                'key'  => 'ip_num',
                'value'  => count($assigned_ips)>0 ? count($assigned_ips) : '',
                'disable'   => $syncFirewallRule == 1,
                'sync_firewall_rule'   => $configData['line']['sync_firewall_rule'] ?? 0,
            ];
        }else{
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_dcim_ip_num'),
                'key'  => 'ip_num',
                'value'  => $adminField['ip_num'],
                'disable'   => $syncFirewallRule == 1,
                'sync_firewall_rule'   => $configData['line']['sync_firewall_rule'] ?? 0,
            ];
        }
        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp([
            'host_id'   => $param['host']['id'],
        ]);

        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_ip'),
            'key'   => 'ip',
            'value' => $hostIp['dedicate_ip'],
        ];
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_additional_ip'),
            'key'   => 'additional_ip',
            'value' => $hostIp['assign_ip'],
        ];
        if($syncFirewallRule!=1){
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_dcim_option_value_4'),
                'key'   => 'defence',
                'value' => $adminField['defence'],
            ];
        }
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_port'),
            'key'   => 'port',
            'value' => $hostAddition['port'] ?? '',
        ];
        return $data;
    }

    /**
     * 时间 2024-02-18
     * @title 产品保存后
     * @desc 产品保存后
     * @author hh
     * @version v1
     * @param  string param.module_admin_field.model_config_name - 型号配置名称
     * @param  string param.module_admin_field.model_config_cpu - 处理器
     * @param  string param.module_admin_field.model_config_cpu_param - 处理器参数
     * @param  string param.module_admin_field.model_config_memory - 内存
     * @param  string param.module_admin_field.model_config_disk - 硬盘
     * @param  string param.module_admin_field.model_config_gpu - 显卡
     * @param  int param.module_admin_field.image - 镜像ID
     * @param  string param.module_admin_field.bw - 带宽
     * @param  int param.module_admin_field.in_bw - 进带宽
     * @param  string param.module_admin_field.flow - 流量
     * @param  string param.module_admin_field.defence - 防御峰值
     * @param  string param.module_admin_field.ip_num - IP数量
     * @param  string param.module_admin_field.ip - 主IP
     * @param  string param.module_admin_field.additional_ip - 附加IP
     * @param  int param.module_admin_field.zjmf_dcim_id - DCIMID
     * @param string param.module_admin_field.username - 用户名
     * @param string param.module_admin_field.password - 密码
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

            $configData = json_decode($hostLink['config_data'], true);
            $configData['admin_field'] = $configData['admin_field'] ?? [];
            
            $update = [];           // 修改的参数
            $postFlow = [];         // 流量修改参数
            $bw = [];               // 带宽参数
            $ip_change = false;     // IP数量是否变更
            $input_ip = false;
            $hostIpSave = [];       // 要保存的IP数据

            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

            if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
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

            $configData['admin_field']['model_name'] = $moduleAdminField['model_config_name'];
            $configData['admin_field']['cpu'] = $moduleAdminField['model_config_cpu'];
            $configData['admin_field']['cpu_param'] = $moduleAdminField['model_config_cpu_param'] ?? '';
            $configData['admin_field']['memory'] = $moduleAdminField['model_config_memory'];
            $configData['admin_field']['disk'] = $moduleAdminField['model_config_disk'];
            $configData['admin_field']['gpu'] = $moduleAdminField['model_config_gpu'] ?? '';

            if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();

                $image = ImageModel::find($moduleAdminField['image']);
                $configData['image'] = $image;
                $update['image_id'] = $image['id'];
            }
            // 带宽
            if(isset($moduleAdminField['bw']) && is_numeric($moduleAdminField['bw']) && $moduleAdminField['bw'] != $adminField['bw']){
                $configData['admin_field']['bw'] = $moduleAdminField['bw'];

                $bw['in_bw'] = $moduleAdminField['bw'];
                $bw['out_bw'] = $moduleAdminField['bw'];
            }
            if(isset($moduleAdminField['in_bw']) && is_numeric($moduleAdminField['in_bw']) && $moduleAdminField['in_bw'] != $adminField['in_bw']){
                $configData['admin_field']['in_bw'] = $moduleAdminField['in_bw'];

                $bw['in_bw'] = $moduleAdminField['in_bw'];
            }
            // 流量
            if(isset($moduleAdminField['flow']) && $moduleAdminField['flow'] != $adminField['flow']){
                $configData['admin_field']['flow'] = $moduleAdminField['flow'];

                $postFlow['id'] = $hostLink['rel_id'] ?? 0;
                $postFlow['traffic'] = (int)$moduleAdminField['flow'];
            }
            if(isset($moduleAdminField['defence']) && $moduleAdminField['defence'] != $adminField['defence']){
                $configData['admin_field']['defence'] = (int)$moduleAdminField['defence'];
            }
            if(isset($moduleAdminField['ip_num']) && $moduleAdminField['ip_num'] != $adminField['ip_num']){
                if($moduleAdminField['ip_num'] == 'NC'){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_cannot_modify_to_nc')];
                }
                $configData['admin_field']['ip_num'] = $moduleAdminField['ip_num'];

                $ip_change = true;
            }
            if(isset($moduleAdminField['ip']) && $moduleAdminField['ip'] != $adminField['ip']){
                $hostIpSave['dedicate_ip'] = $moduleAdminField['ip'];
                $input_ip = true;
            }
            if(isset($moduleAdminField['additional_ip']) && $moduleAdminField['additional_ip'] != $adminField['additional_ip']){
                $hostIpSave['assign_ip'] = $moduleAdminField['additional_ip'];
                $input_ip = true;
            }
            if($input_ip){
                $HostIpModel = new HostIpModel();
                $HostIpModel->hostIpSave([
                    'host_id'       => $param['host']['id'],
                    'dedicate_ip'   => $moduleAdminField['ip'] ?? $adminField['ip'],
                    'assign_ip'     => $moduleAdminField['additional_ip'] ?? $adminField['additional_ip'],
                ]);
            }
            $Dcim = new Dcim($param['server']);

            $serverHash = ToolLogic::formatParam($param['server']['hash']);
            $prefix = $serverHash['user_prefix'] ?? '';

            $detail = '';
            if(isset($adminField['zjmf_dcim_id']) && isset($moduleAdminField['zjmf_dcim_id']) && is_numeric($moduleAdminField['zjmf_dcim_id']) && $adminField['zjmf_dcim_id'] != $moduleAdminField['zjmf_dcim_id']){
                $update['rel_id'] = (int)$moduleAdminField['zjmf_dcim_id'];
                $hostLink['rel_id'] = $update['rel_id'];

                if(!empty($update['rel_id'])){
                    // 获取服务器是否不是空闲
                    $dcimDetail = $Dcim->detail(['id'=>$update['rel_id']]);
                    if($dcimDetail['status'] != 200){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').$dcimDetail['msg'] ];
                    }
                    if($dcimDetail['server']['status'] != 1){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').lang_plugins('mf_dcim_server_is_not_free')];
                    }
                    // 尝试分配为该机器,调用同步接口
                    $postData = [
                        'id'            => $update['rel_id'],
                        'hostid'        => $param['host']['id'],
                        'user_id'       => $prefix . $param['host']['client_id'],
                        'remote_user_id'=> $param['host']['client_id'],
                        'domainstatus'  => 'Active',
                        'starttime'     => date('Y-m-d H:i:s', $param['host']['create_time']),
                        // 'token'         => defined('AUTHCODE') ? AUTHCODE : configuration('system_license'),
                    ];
                    if($param['host']['due_time'] > 0){
                        $postData['expiretime'] = date('Y-m-d H:i:s', $param['host']['due_time']);
                    }
                    $assign = $Dcim->ipmiSync($postData);
                    if($assign['status'] == 200){
                        $detail .= ','.lang_plugins('mf_dcim_assign_dcimid_success').': '.$update['rel_id'];

                        // 不手动修改IP/附加IP
                        if(!$input_ip){
                            $assign['ips'] = array_filter(explode("\r\n", $assign['ips']), function($value) use ($assign) {
                                return $value != $assign['zhuip'];
                            });

                            $hostIpSave['dedicate_ip'] = $assign['zhuip'] ?? '';
                            $hostIpSave['assign_ip'] = trim(implode(',', $assign['ips']), ',');
                        }
                    }else{
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').$assign['msg'] ];
                    }
                }
                // 空闲原机器
                if(!empty($adminField['zjmf_dcim_id'])){
                    $postData = [
                        'id'            => $adminField['zjmf_dcim_id'],
                        'hostid'        => $param['host']['id'],
                        'user_id'       => $prefix . $param['host']['client_id'],
                        'remote_user_id'=> $param['host']['client_id'],
                        'domainstatus'  => 'Free',
                        'starttime'     => '',
                        // 'token'         => '',
                    ];
                    $free = $Dcim->ipmiSync($postData);
                    if($free['status'] == 200){
                        $detail .= ','.lang_plugins('mf_dcim_free_dcimid_success').': '.$adminField['zjmf_dcim_id'];
                    }else{
                        $detail .= lang_plugins('mf_dcim_free_dcimid_fail', [
                            '{dcimid}' => $adminField['zjmf_dcim_id'],
                            '{reason}' => $free['msg'],
                        ]);
                    }
                }
            }

            $update['config_data'] = json_encode($configData);
            HostLinkModel::update($update, ['host_id'=>$hostId]);

            HostModel::where('id', $hostId)->update([
                'base_info'     => $this->formatBaseInfo($configData),
            ]);
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                if(!empty($detail)){
                    $description = lang_plugins('mf_dcim_log_host_update_complete', [
                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                        '{detail}'  => $detail,
                    ]);
                    active_log($description, 'host', $param['host']['id']);
                }
                return ['status'=>200, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }

            // 有升降级IP
            if($ip_change){
                $ipGroup = 0;
                // 获取下线路信息
                $line = LineModel::find($configData['line']['id']);
                if(!empty($line)){
                    if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value'])){
                        $ipGroup = $line['defence_ip_group'];
                    }else{
                        $ipGroup = $line['bw_ip_group'];
                    }
                }

                $post = [];
                $post['id'] = $id;
                $ip_num = $configData['admin_field']['ip_num'];

                if(is_numeric($ip_num)){
                    if(!empty($ipGroup)){
                        $post['ip_num'][$ipGroup] = $ip_num;
                    }else{
                        $post['ip_num'] = $ip_num;
                    }
                }else if($ip_num == 'NO_CHANGE'){
                    $post['ip_num'] = $ip_num;
                }else{  //分组形式2_2,1_1  数量_分组id
                    $ip_num = ToolLogic::formatDcimIpNum($ip_num);
                    if($ip_num === false){
                        // $result['status'] = 400;
                        // $result['msg'] = 'IP数量格式有误';
                        // return $result;
                    }else{
                        $post['ip_num'] = $ip_num;
                    }
                }
                // if(!empty($ipGroup)){
                //     $post['ip_num'][ $ipGroup ] = $configData['ip']['value'];
                // }else{
                //     $post['ip_num'] = $configData['ip']['value'];
                // }
                $res = $Dcim->modifyIpNum($post);
                if($res['status'] == 200){
                    // 重新获取IP
                    $detailRes = $Dcim->detail(['id'=>$id]);
                    if($detailRes['status'] == 200){
                        // 不手动修改IP/附加IP
                        if(!$input_ip){
                            $hostIpSave['dedicate_ip'] = $detailRes['server']['zhuip'] ?? '';
                            $hostIpSave['assign_ip'] = trim(implode(',', $detailRes['ip']['ipaddress'] ?? []), ',');
                        }
                        $ips = $detailRes['ip']['ipaddress'] ?? [];
                        $ips[] = $detailRes['server']['zhuip'] ?? '';
                        $ips = array_unique($ips);
                
                        // 移出不存在的IP
                        $IpDefenceModel = new IpDefenceModel();
                        $IpDefenceModel->where('host_id', $param['host']['id'])->whereNotIn('ip', $ips)->delete();
                    }
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_ip_num_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_ip_num_fail').$res['msg'];
                }
            }
            if(!empty($postFlow)){
                $postFlow['id'] = $id;

                $res = $Dcim->modifyFlowLimit($postFlow);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_flow_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_flow_fail').$res['msg'];
                }
            }
            // 修改带宽
            if(isset($bw['in_bw'])){
                $res = $Dcim->modifyInBw(['num'=>$bw['in_bw'], 'server_id'=>$id]);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_in_bw_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_in_bw_fail').$res['msg'];
                }
            }
            if(isset($bw['out_bw'])){
                $res = $Dcim->modifyOutBw(['num'=>$bw['out_bw'], 'server_id'=>$id]);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_out_bw_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_out_bw_fail').$res['msg'];
                }
            }

            if(!empty($hostIpSave)){
                $hostIpSave['host_id'] = $hostId;
                $hostIpSave['assign_ip'] = $hostIpSave['assign_ip'] ?? '';

                // 保存IP信息
                $HostIpModel = new HostIpModel();
                $HostIpModel->hostIpSave($hostIpSave);
            }
            // 检查当前是否还超额
            // if($param['host']['status'] == 'Suspended' && $param['host']['suspend_type'] == 'overtraffic'){
            //     $post = [];
            //     $post['id'] = $id;
            //     $post['hostid'] = $hostId;
            //     $post['unit'] = 'GB';

            //     $flow = $Dcim->flow($post);
            //     if($flow['status'] == 200){
            //         $data = $flow['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month' ];

            //         $percent = str_replace('%', '', $data['used_percent']);

            //         $total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
            //         $used = round($total * $percent / 100, 2);
            //         if($percent < 100){
            //             $unsuspendRes = $param['host']->unsuspendAccount($param['host']['id']);
            //             if($unsuspendRes['status'] == 200){
            //                 $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停成功', $total, $used);
            //             }else{
            //                 $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停失败,原因:%s', $total, $used, $unsuspendRes['msg']);
            //             }
            //         }
            //     }
            // }

            $this->syncAccount($param);
            
            if(!empty($detail)){
                $description = lang_plugins('mf_dcim_log_host_update_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{detail}'  => $detail,
                ]);
                active_log($description, 'host', $param['host']['id']);
            }
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
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
     * 时间 2024-02-18
     * @title 格式化后台字段
     * @desc 格式化后台字段,方便处理和使用
     * @author hh
     * @version v1
     * @param   array $configData - 产品缓存配置数据 require
     * @return  string model_name - 型号配置名称
     * @return  string cpu - 处理器
     * @return  string cpu_param - 处理器参数
     * @return  string memory - 内存
     * @return  string disk - 硬盘
     * @return  string gpu - 显卡
     * @return  int memory_used - 内存使用
     * @return  int memory_num_used - 内存数量使用
     * @return  int disk_num_used - 硬盘数量使用
     * @return  string bw - 带宽
     * @return  string in_bw - 进带宽
     * @return  string ip_num - 公网IP数量
     * @return  string flow - 流量
     * @return  string defence - 防御峰值(G)
     */
    public function getAdminField($configData = [])
    {
        $adminField = $configData['admin_field'] ?? [];
        // 以前没有admin_field的转换
        if(empty($adminField)){
            $adminField['model_name'] = $configData['model_config']['name'] ?? '';
            $adminField['cpu'] = $configData['model_config']['cpu'] ?? '';
            $adminField['cpu_param'] = $configData['model_config']['cpu_param'] ?? '';
            $adminField['memory'] = $configData['model_config']['memory'] ?? '';
            $adminField['disk'] = $configData['model_config']['disk'] ?? '';
            $adminField['gpu'] = '';
            $adminField['memory_used'] = 0;
            $adminField['memory_num_used'] = 0;
            $adminField['disk_num_used'] = 0;

            $in_bw = '';
            $out_bw = '';
            if(isset($configData['bw'])){
                $in_bw = $configData['bw']['other_config']['in_bw'] ?: $configData['bw']['value'];
                $out_bw = $configData['bw']['value'];
            }else if(isset($configData['flow'])){
                $in_bw = $configData['flow']['other_config']['in_bw'];
                $out_bw = $configData['flow']['other_config']['out_bw'];
            }
            $adminField['bw'] = (string)$out_bw;
            $adminField['in_bw'] = $in_bw;
            $adminField['ip_num'] = $configData['ip']['value'] ?? '';
            $adminField['flow'] = $configData['flow']['value'] ?? '';
            $adminField['defence'] = $configData['defence']['value'] ?? '';
        }else{
            // 强转下
            $adminField['bw'] = isset($adminField['bw']) ? (string)$adminField['bw'] : ($configData['bw']['value'] ?? '');
            $adminField['in_bw'] = isset($adminField['in_bw']) ? (string)$adminField['in_bw'] : ($configData['bw']['other_config']['in_bw'] ?? '');
            $adminField['ip_num'] = isset($adminField['ip_num']) ? (string)$adminField['ip_num'] : ($configData['ip']['value'] ?? '');
            $adminField['flow'] = isset($adminField['flow']) ? (string)$adminField['flow'] : ($configData['flow']['value'] ?? '');
            $adminField['defence'] = isset($adminField['defence']) ? (string)$adminField['defence'] : ($configData['defence']['value'] ?? '');
        }
        $adminField['ip_num'] = (string)$adminField['ip_num'];
        return $adminField;
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
                ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
                ->where('i.id', $hostLink['image_id'])
                ->find();
        $ImageGroupModel = new ImageGroupModel();

        if(!empty($image)){
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

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);
        // 手动资源的时候
        if($config['data']['manual_resource']==1){
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
                    $result['data']['port'] = $manual_resource['port'];
                }
            }
        }else{
            // 获取IP,镜像密码
            if($hostLink['rel_id'] > 0){
                $serverHash = ToolLogic::formatParam($param['server']['hash']);
                $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

                $Dcim = new Dcim($param['server']);
                $postData = [
                    'id'            => $hostLink['rel_id'],
                    'hostid'        => $param['host']['id'],
                    'user_id'       => $prefix . $param['host']['client_id'],
                    'remote_user_id'=> $param['host']['client_id'],
                    'domainstatus'  => 'Active',
                    'starttime'     => date('Y-m-d H:i:s', $param['host']['create_time']),
                ];
                if($param['host']['due_time'] > 0){
                    $postData['expiretime'] = date('Y-m-d H:i:s', $param['host']['due_time']);
                }
                $res = $Dcim->ipmiSync($postData);
                if($res['status'] == 200){
                    
                    $updateHostLink = [
                        // 'password'      => aes_password_encode($res['password']),
                        'update_time'   => time(),
                    ];

                    $result['data']['password'] = $res['password'];
                    $result['data']['port'] = $res['port'];

                    // 反向查找下镜像
                    if(!empty($res['os_id'])){
                        $image = ImageModel::where('product_id', $param['product']['id'])->where('rel_image_id', $res['os_id'])->find();
                        if(!empty($image)){
                            $updateHostLink['image_id'] = $image['id'];

                            $imageGroup = ImageGroupModel::find($image['image_group_id']);

                            $result['data']['image_icon'] = $imageGroup['icon'] ?? '';
                            $result['data']['image_name'] = $image['name'];
                            $result['data']['username'] = $ImageGroupModel->isWindows($imageGroup) ? 'administrator' : 'root';

                            $this->where('host_id', $param['host']['id'])->update($updateHostLink);
                        }
                    }
                    $assignIp = array_filter(explode("\r\n", $res['ips']), function($value) use ($res) {
                        return $value != $res['zhuip'];
                    });

                    $result['data']['dedicate_ip'] = $res['zhuip'] ?: '';
                    $result['data']['assign_ip'] = trim(implode(',', $assignIp), ',');

                    $ips = $assignIp;
                    $ips[] = $res['zhuip'] ?? '';
                    $ips = array_unique($ips);
            
                    // 移出不存在的IP
                    $IpDefenceModel = new IpDefenceModel();
                    $IpDefenceModel->where('host_id', $param['host']['id'])->whereNotIn('ip', $ips)->delete();

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
                }
            }
        }
        $configData = json_decode($hostLink['config_data'], true);
        $result['data']['base_info'] = $this->formatBaseInfo($configData);
        
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
        // 配置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];
        // 价格
        $price = PriceModel::where('product_id', $productId)->select()->toArray();
        // 型号配置
        // $modelConfig = ModelConfigModel::where('product_id', $productId)->where('hidden', 0)->select()->toArray();
        $modelConfig = ModelConfigModel::where('product_id', $productId)->select()->toArray();
        $modelConfigOptionLink = [];
        // 型号配置可选配置
        if(!empty($modelConfig)){
            $modelConfigOptionLink = ModelConfigOptionLinkModel::whereIn('model_config_id', array_column($modelConfig, 'id'))->select()->toArray();
        }

        return [
            'duration'                      => $duration,
            'data_center'                   => $dataCenter,
            'line'                          => $line,
            'option'                        => $option,
            'limit_rule'                    => $limitRule,
            'image_group'                   => $imageGroup,
            'image'                         => $image,
            'config'                        => $config,
            'price'                         => $price,
            'model_config'                  => $modelConfig,
            'model_config_option_link'      => $modelConfigOptionLink,
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
                        // 'order'             => $v['order'],
                    ];
                    DataCenterModel::where('id', $dataCenterIdArr[ $v['id'] ])->update($update);
                    $newId[] = $dataCenterIdArr[ $v['id'] ];
                }else{
                    $dataCenter = DataCenterModel::create([
                        'product_id'        => $productId,
                        'country_id'        => $v['country_id'],
                        'city'              => $v['city'],
                        'area'              => $v['area'],
                        'order'             => $v['order'],
                        'create_time'       => $time,
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
                        // 'name'              => $v['name'],
                        'bill_type'         => $v['bill_type'],
                        'bw_ip_group'       => $v['bw_ip_group'],
                        'defence_enable'    => $v['defence_enable'],
                        'defence_ip_group'  => $v['defence_ip_group'],
                        // 'order'             => $v['order'],
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
                    // 修改
                    $update = [
                        'rel_type'      => $v['rel_type'],
                        'type'          => $v['type'],
                        'value'         => $v['value'],
                        'min_value'     => $v['min_value'],
                        'max_value'     => $v['max_value'],
                        'step'          => $v['step'],
                        'other_config'  => $v['other_config'],
                        'order'         => $v['order'],
                        'value_show'    => $v['value_show'],
                        'firewall_type'  => $v['firewall_type'] ?? '',
                        'defence_rule_id'  => $v['defence_rule_id'] ?? 0,
                    ];
                    // 线路
                    if(in_array($v['rel_type'], [2,3,4,5])){
                        $update['rel_id'] = $lineIdArr[ $v['rel_id'] ] ?? 0;
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
                    if(in_array($v['rel_type'], [2,3,4,5])){
                        $v['rel_id'] = $lineIdArr[ $v['rel_id'] ] ?? 0;
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
            
            // 型号配置
            $modelConfig = ModelConfigModel::where('product_id', $productId)->select()->toArray();
            $modelConfigIdArr = array_column($modelConfig, 'id', 'upstream_id');
            $oldId = array_column($modelConfig, 'id');
            $newId = [];
            
            foreach($otherParams['model_config'] as $v){
                if(isset($modelConfigIdArr[ $v['id'] ])){
                    // 修改
                    $update = [
                        'name'                      => $v['name'],
                        'group_id'                  => $v['group_id'],
                        'cpu'                       => $v['cpu'],
                        'cpu_param'                 => $v['cpu_param'],
                        'memory'                    => $v['memory'],
                        'disk'                      => $v['disk'],
                        'update_time'               => $time,
                        // 'order'                     => $v['order'],
                        'support_optional'          => $v['support_optional'],
                        'leave_memory'              => $v['leave_memory'],
                        'max_memory_num'            => $v['max_memory_num'],
                        'max_disk_num'              => $v['max_disk_num'],
                        'gpu'                       => $v['gpu'],
                        'max_gpu_num'               => $v['max_gpu_num'],
                        'optional_only_for_upgrade' => $v['optional_only_for_upgrade'],
                        'hidden'                    => $v['hidden'],
                        'qty'                       => $v['qty'],
                    ];
                    ModelConfigModel::where('id', $modelConfigIdArr[ $v['id'] ])->update($update);
                    $newId[] = $modelConfigIdArr[ $v['id'] ];
                }else{
                    $upstreamId = $v['id'];
                    unset($v['id']);

                    $v['product_id'] = $productId;
                    $v['create_time'] = $time;
                    $v['upstream_id'] = $upstreamId;

                    $modelConfig = ModelConfigModel::create($v);
                    $modelConfigIdArr[ $upstreamId ] = $modelConfig->id;
                    $newId[] = $modelConfig->id;
                }
            }
            $deleteModelConfigId = array_diff($oldId, $newId);
            if(!empty($deleteModelConfigId)){
                ModelConfigModel::whereIn('id', $deleteModelConfigId)->delete();
            }
            
            // 直接删除老的
            ModelConfigOptionLinkModel::whereIn('model_config_id', $oldId)->delete();
            foreach($otherParams['model_config_option_link'] as $v){
                $v['model_config_id'] = $modelConfigIdArr[ $v['model_config_id'] ] ?? 0;
                $v['option_id'] = $optionIdArr[ $v['option_id'] ] ?? 0;
                if(empty($v['model_config_id']) || empty($v['option_id'])){
                    continue;
                }
                ModelConfigOptionLinkModel::create($v);
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
                if(isset($v['rule']['model_config']['id'])){
                    foreach($v['rule']['model_config']['id'] as $kk=>$vv){
                        if(isset($modelConfigIdArr[$vv])){
                            $v['rule']['model_config']['id'][$kk] = (int)$modelConfigIdArr[$vv];
                        }else{
                            unset($v['rule']['model_config']['id'][$kk]);
                        }
                    }
                    $v['rule']['model_config']['id'] = array_values($v['rule']['model_config']['id']);
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
                if(isset($v['result']['model_config'])){
                    foreach($v['result']['model_config'] as $kk=>$resultItem){
                        foreach($resultItem['id'] as $kkk=>$vvv){
                            if(isset($modelConfigIdArr[$vvv])){
                                $v['result']['model_config'][$kk]['id'][$kkk] = (int)$modelConfigIdArr[$vvv];
                            }else{
                                unset($v['result']['model_config'][$kk]['id'][$kkk]);
                            }
                        }
                        $v['result']['model_config'][$kk]['id'] = array_values($v['result']['model_config'][$kk]['id']);
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
                    $limitRule = LimitRuleModel::create($v);
                    $newId[] = $limitRule->id;
                }
            }
            $deleteLimitRuleId = array_diff($oldId, $newId);
            if(!empty($deleteLimitRuleId)){
                LimitRuleModel::whereIn('id', $deleteLimitRuleId)->delete();
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
                if($v['rel_type'] == PriceModel::TYPE_OPTION){
                    $v['rel_id'] = $optionIdArr[ $v['rel_id'] ] ?? 0;
                }else if($v['rel_type'] == PriceModel::TYPE_MODEL_CONFIG){
                    $v['rel_id'] = $modelConfigIdArr[ $v['rel_id'] ] ?? 0;
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
                    'product_id'                    => $productId,
                    'rand_ssh_port'                 => $otherParams['config']['rand_ssh_port'],
                    'manual_resource'               => 0,
                    'level_discount_memory_order'   => $otherParams['config']['level_discount_memory_order'],
                    'level_discount_memory_upgrade' => $otherParams['config']['level_discount_memory_upgrade'],
                    'level_discount_disk_order'     => $otherParams['config']['level_discount_disk_order'],
                    'level_discount_disk_upgrade'   => $otherParams['config']['level_discount_disk_upgrade'],
                    'level_discount_bw_upgrade'     => $otherParams['config']['level_discount_bw_upgrade'],
                    'level_discount_ip_num_upgrade' => $otherParams['config']['level_discount_ip_num_upgrade'],
                    'optional_host_auto_create'     => 1,//$otherParams['config']['optional_host_auto_create'],
                    'level_discount_gpu_order'      => $otherParams['config']['level_discount_gpu_order'],
                    'level_discount_gpu_upgrade'    => $otherParams['config']['level_discount_gpu_upgrade'],
                    'sync_firewall_rule'            => $otherParams['config']['sync_firewall_rule'] ?? 0,
                    'order_default_defence'         => $otherParams['config']['order_default_defence'] ?? '',
                    'level_discount_bw_order'       => $otherParams['config']['level_discount_bw_order'],
                    'level_discount_ip_num_order'   => $otherParams['config']['level_discount_ip_num_order'],
                    'auto_sync_dcim_stock'          => $otherParams['config']['auto_sync_dcim_stock'],
                    'custom_rand_password_rule'     => $otherParams['config']['custom_rand_password_rule'],
                    'default_password_length'       => $otherParams['config']['default_password_length'],
                ];
                if(isset($otherParams['config']['level_discount_memory_renew'])){
                    $configData['level_discount_memory_renew'] = $otherParams['config']['level_discount_memory_renew'];
                    $configData['level_discount_disk_renew'] = $otherParams['config']['level_discount_disk_renew'];
                    $configData['level_discount_gpu_renew'] = $otherParams['config']['level_discount_gpu_renew'];
                    $configData['level_discount_ip_num_renew'] = $otherParams['config']['level_discount_ip_num_renew'];
                    $configData['level_discount_bw_renew'] = $otherParams['config']['level_discount_bw_renew'];
                }

                $config = ConfigModel::where('product_id', $productId)->find();
                if(!empty($config)){
                    ConfigModel::where('product_id', $productId)->update($configData);
                }else{
                    ConfigModel::create($configData);
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
                if(isset($param['model_config_id'])){
                    $exchangeParams['model_config_id'] = ModelConfigModel::where('id', $param['model_config_id'])->value('upstream_id');
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
                if(isset($param['optional_memory']) && !empty($param['optional_memory'])){
                    $exchangeParams['optional_memory'] = [];
                    foreach($param['optional_memory'] as $k=>$v){
                        $upstreamId = OptionModel::where('id', $k)->where('rel_type', OptionModel::MEMORY)->value('upstream_id');
                        if(!empty($upstreamId)){
                            $exchangeParams['optional_memory'][ $upstreamId ] = $v;
                        }else{
                            $exchangeParams['optional_memory'][ -1 ] = $v;
                        }
                    }
                }
                if(isset($param['optional_disk']) && !empty($param['optional_disk'])){
                    $exchangeParams['optional_disk'] = [];
                    foreach($param['optional_disk'] as $k=>$v){
                        $upstreamId = OptionModel::where('id', $k)->where('rel_type', OptionModel::DISK)->value('upstream_id');
                        if(!empty($upstreamId)){
                            $exchangeParams['optional_disk'][ $upstreamId ] = $v;
                        }else{
                            $exchangeParams['optional_disk'][ -1 ] = $v;
                        }
                    }
                }
                if(isset($param['optional_gpu']) && !empty($param['optional_gpu'])){
                    $exchangeParams['optional_gpu'] = [];
                    foreach($param['optional_gpu'] as $k=>$v){
                        $upstreamId = OptionModel::where('id', $k)->where('rel_type', OptionModel::GPU)->value('upstream_id');
                        if(!empty($upstreamId)){
                            $exchangeParams['optional_gpu'][ $upstreamId ] = $v;
                        }else{
                            $exchangeParams['optional_gpu'][ -1 ] = $v;
                        }
                    }
                }

                // 不支持的参数
                $exchangeParams['auto_renew'] = 0;
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
    public function hostOtherParams($host)
    {
        $data = [];
        // 获取当前实例所有配置
        $hostLink = $this->where('host_id', $host['id'])->find();
        if(empty($hostLink)){
            return $data;
        }
        $configData = json_decode($hostLink['config_data'], true);

        $data['duration_id'] = $configData['duration']['id'] ?? 0;
        $data['data_center_id'] = $hostLink['data_center_id'];
        $data['line_id'] = $configData['line']['id'];
        $data['image_id'] = $hostLink['image_id'];
        $data['model_config_id'] = $configData['model_config']['id'];

        $data['flow'] = $configData['flow'] ?? NULL;
        $data['bw'] = $configData['bw'] ?? NULL;
        $data['defence'] = $configData['defence'] ?? NULL;
        $data['ip'] = $configData['ip'] ?? [];

        $data['admin_field'] = $configData['admin_field'];

        // 获取选配项
        $data['optional'] = HostOptionLinkModel::field('option_id,num')->where('host_id', $host['id'])->select()->toArray();

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
        
    }


    /**
     * 时间 2023-11-17
     * @title 产品转移
     * @desc  产品转移
     * @author hh
     * @version v1
     */
    public function hostTransfer($param)
    {
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink) || empty($hostLink['rel_id'])){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $Dcim = new Dcim($param['module_param']['server']);

        $serverHash = ToolLogic::formatParam($param['module_param']['server']['hash']);
        $prefix = $serverHash['user_prefix'] ?? '';

        // 尝试分配为该机器,调用同步接口
        $postData = [
            'id'            => $hostLink['rel_id'],
            'hostid'        => $param['host']['id'],
            'user_id'       => $prefix . $param['target_client']['id'],
            'remote_user_id'=> $param['target_client']['id'],
            'domainstatus'  => 'Active',
            // 'starttime'     => date('Y-m-d H:i:s', $param['host']['create_time']),
            // 'token'         => defined('AUTHCODE') ? AUTHCODE : configuration('system_license'),
        ];
        if($param['module_param']['host']['due_time'] > 0){
            $postData['expiretime'] = date('Y-m-d H:i:s', $param['module_param']['host']['due_time']);
        }
        $res = $Dcim->ipmiSync($postData);
        return $res;
    }

    /**
     * 时间 2024-01-18
     * @title DCIM租用列表
     * @desc  DCIM租用列表
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @param   int param.server_group_id - 搜索:DCIM服务器分组ID
     * @param   string param.ip - 搜索:IP
     * @return  int list[].id - DCIMID
     * @return  string list[].wltag - 标签
     * @return  string list[].typename - 型号
     * @return  string list[].group_name - 分组名称
     * @return  string list[].mainip - 主IP
     * @return  int list[].ip_num - IP数量
     * @return  int list[].ip[].id - IPID
     * @return  string list[].ip[].ipaddress - IP地址
     * @return  string list[].ip[].server_mainip - 是否主IP(true=是,false=否)
     * @return  string list[].in_bw - 进带宽
     * @return  string list[].out_bw - 出带宽
     * @return  string list[].remarks - 备注
     * @return  string list[].status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @return  int list[].host_id - 产品ID
     * @return  int list[].client_id - 所属用户
     * @return  string list[].type - 类型(rent=租用,trust=托管)
     * @return  string list[].dcim_url - dcim链接
     * @return  int count - 总条数
     * @return  string server_group[].id - 服务器分组ID
     * @return  string server_group[].name - 服务器分组名称
     * @return  string server_group[].config - 服务器分组配置
     */
    public function dcimSalesList($param)
    {
        $result = [
            'list'              => [],
            'count'             => 0,
            'server_group_id'   => 0,
            'server_group'      => [],
        ];

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete']){
            return $result;
        }
        $moduleParam = $HostModel->getModuleParams();
        if(empty($moduleParam['server']) || $moduleParam['server']['module'] != 'mf_dcim'){
            return $result;
        }
        $Dcim = new Dcim($moduleParam['server']);
        $moduleParam['server']['url'] = rtrim($moduleParam['server']['url'], '/');

        // 接口参数
        $postData['search'] = 'highgrade';
        $postData['listpages'] = (int)$param['limit'];
        $postData['offset'] = max((int)$param['page'] - 1, 0);
        $postData['sales'] = 'all';
        if(isset($param['status']) && in_array($param['status'], [1,2,3,4,5,6,7])){
            $postData['status'] = [$param['status']];
        }else{
            $postData['status'] = [1,2,3,4,5,6,7];
        }
        if(isset($param['server_group_id']) && !empty($param['server_group_id'])){
            $postData['group_id'][] = $param['server_group_id'];
        }else{
            $postData['group_id'] = [];
        }
        if(isset($param['ip']) && $param['ip'] !== ''){
            $postData['ip'] = $param['ip'];
        }
        $res = $Dcim->overview($postData);
        if($res['status'] == 200){
            $result['count'] = (int)$res['sum'];

            foreach($res['listing'] as $v){
                $one = [
                    'id'            => $v['id'],
                    'wltag'         => $v['wltag'],
                    'typename'      => $v['typename'] ?? '',
                    'group_name'    => $v['group_name'] ?? '',
                    'mainip'        => $v['zhuip'],
                    'ip_num'        => count($v['ip']),
                    'ip'            => $v['ip'] ?: [],
                    'in_bw'         => $v['out_bw'] ?? '',
                    'out_bw'        => $v['in_bw'] ?? '',
                    'remarks'       => $v['remarks'],
                    'status'        => $v['status'],
                    'host_id'       => 0,
                    'client_id'     => 0,
                ];
                if($v['type'] == 1 || $v['type'] == 9){
                    $one['type'] = 'rent';
                }else{
                    $one['type'] = 'trust';
                }
                // 当正常时匹配V10用户地址
                if($v['status'] == 3 && !empty($v['productid'])){
                    $match = $this
                            ->alias('hl')
                            ->field('h.id,h.client_id')
                            ->join('host h', 'hl.host_id=h.id')
                            ->where('hl.host_id', $v['productid'])
                            ->where('hl.rel_id', $v['id'])
                            ->where('h.status', 'Active')
                            ->where('h.is_delete', 0)
                            ->find();
                    if(!empty($match)){
                        $one['client_id'] = $match['client_id'];
                        $one['host_id'] = $match['id'];
                    }
                }
                $one['dcim_url'] = $moduleParam['server']['url'] . '/index.php?m=server&a=detailed&id='. $v['id'];

                $result['list'][] = $one;
            }
            $result['server_group'] = $res['server_group'] ?? [];
        }
        return $result;
    }

    /**
     * 时间 2024-01-19
     * @title 分配服务器
     * @desc  分配服务器
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.dcim_id - DCIMID require
     * @param   string param.status - 状态Free=空闲Fault=故障 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function assignDcimServer($param)
    {
        $param['dcim_id'] = $param['dcim_id'] ?? 0;
        $param['dcim_id'] = (int)$param['dcim_id'];
        if(empty($param['dcim_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_dcim_server')];
        }
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_not_found') ];
        }
        $moduleParam = $HostModel->getModuleParams();
        if(empty($moduleParam['server']) || $moduleParam['server']['module'] != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module') ];
        }
        $isAssign = $this
                    ->alias('hl')
                    ->field('h.id,h.name')
                    ->join('host h', 'hl.host_id=h.id')
                    ->join('product p', 'h.product_id=p.id')
                    ->where('h.server_id', $moduleParam['server']['id'])
                    ->where('hl.rel_id', $param['dcim_id'])
                    ->where('h.is_delete', 0)
                    ->find();
        if(!empty($isAssign)){
            $result['status'] = 400;
            $result['msg'] = lang_plugins('mf_dcim_assign_server_but_already_assigned', [
                '{dcim_id}' => $param['dcim_id'],
                '{host}'    => $isAssign['name'].'#'.$isAssign['id'],
            ]);
            return $result;
        }
        if(method_exists($HostModel, 'getCreateAccountLock')){
            $lock = $HostModel->getCreateAccountLock($param['id']);
            if($lock['status'] == 400){
                return $lock;
            }
        }

        $hostLink = $this->where('host_id', $param['id'])->find();
        if(!empty($hostLink['rel_id'])){
            $result = $this->freeDcimServer($param);
            if($result['status'] == 400){
                return $result;
            }
        }

        $Dcim = new Dcim($moduleParam['server']);

        $serverHash = ToolLogic::formatParam($moduleParam['server']['hash']);
        $userPrefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

        $postData['id'] = $param['dcim_id'];
        $postData['hostid'] = $param['id'];
        $postData['user_id'] = $userPrefix . $HostModel['client_id'];
        $postData['remote_user_id'] = $HostModel['client_id'];
        $postData['domainstatus'] = 'Active';
        if($HostModel['due_time'] > 0){
            $postData['expiretime'] = date('Y-m-d H:i:s', $HostModel['due_time']);
        }
        $postData['starttime'] = date('Y-m-d H:i:s', time());

        $res = $Dcim->ipmiSync($postData);
        if($res['status'] == 200){
            $updateHost = [
                'status'        => 'Active',
                'active_time'   => time(),
            ];
            $updateHostLink = [
                'rel_id'        => $param['dcim_id'],
                'update_time'   => time(),
            ];
            $hostAddition = [
                'username'  => $res['username'],
                'password'  => $res['password'],
                'port'      => $res['port'] ?? '',
            ];

            // 反向查找下镜像
            if(!empty($res['os_id'])){
                $image = ImageModel::where('product_id', $HostModel['product_id'])->where('rel_image_id', $res['os_id'])->find();
                if(!empty($image)){
                    $updateHostLink['image_id'] = $image['id'];

                    $imageGroup = ImageGroupModel::find($image['image_group_id']);

                    $hostAddition['image_icon'] = $imageGroup['icon'] ?? '';
                    $hostAddition['image_name'] = $image['name'];
                }
            }
            
            HostModel::where('id', $HostModel->id)->update($updateHost);
            $this->where('host_id', $HostModel->id)->update($updateHostLink);
            
            $assignIp = array_filter(explode("\r\n", $res['ips']), function($value) use ($res) {
                return $value != $res['zhuip'];
            });

            // 保存IP信息
            $HostIpModel = new HostIpModel();
            $HostIpModel->hostIpSave([
                'host_id'       => $HostModel->id,
                'dedicate_ip'   => $res['zhuip'] ?: '',
                'assign_ip'     => trim(implode(',', $assignIp), ','),
            ]);      

            $ips = $assignIp;
            $ips[] = $res['zhuip'] ?? '';
            $ips = array_unique($ips);
    
            // 移出不存在的IP
            $IpDefenceModel = new IpDefenceModel();
            $IpDefenceModel->where('host_id', $HostModel->id)->whereNotIn('ip', $ips)->delete();      

            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($HostModel->id, $hostAddition);

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('mf_dcim_assign_success'),
            ];

            $description = lang_plugins('mf_dcim_log_assign_dcim_server_success', [
                '{host}'    => 'host#'.$HostModel->id.'#'.$HostModel->name.'#',
                '{dcim_id}' => $param['dcim_id'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => $res['msg'] ?: lang_plugins('mf_dcim_assign_fail'),
            ];

            $description = lang_plugins('mf_dcim_log_assign_dcim_server_fail', [
                '{host}'    => 'host#'.$HostModel->id.'#'.$HostModel->name.'#',
                '{reason}'  => $res['msg'],
            ]);
        }
        if(method_exists($HostModel, 'clearCreateAccountLock')){
            $HostModel->clearCreateAccountLock($param['id']);
        }
        active_log($description, 'host', $param['id']);

        return $result;
    }

    /**
     * 时间 2024-01-22
     * @title 空闲DCIM服务器
     * @desc  空闲DCIM服务器
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   string param.status - 状态Free=空闲Fault=故障 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function freeDcimServer($param)
    {
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_not_found') ];
        }
        $moduleParam = $HostModel->getModuleParams();
        if(empty($moduleParam['server']) || $moduleParam['server']['module'] != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module') ];
        }
        $hostLink = $this->where('host_id', $param['id'])->find();
        if(empty($hostLink) || empty($hostLink['rel_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_already_free')];
        }
        $serverHash = ToolLogic::formatParam($moduleParam['server']['hash']);
        $userPrefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

        $Dcim = new Dcim($moduleParam['server']);

        $postData = [
            'id'            => $hostLink['rel_id'],
            'hostid'        => $param['id'],
            'user_id'       => $userPrefix . $moduleParam['host']['client_id'],
            'remote_user_id'=> $moduleParam['host']['client_id'],
            'domainstatus'  => $param['status'] ?? 'Free',
            'starttime'     => '',
        ];
        $res = $Dcim->ipmiSync($postData);
        if($res['status'] == 200){
            $updateHostLink = [
                'rel_id'        => 0,
                'update_time'   => time(),
            ];
            
            $this->where('host_id', $HostModel->id)->update($updateHostLink);

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('mf_dcim_free_success'),
            ];

            $description = lang_plugins('mf_dcim_log_free_dcim_server_success', [
                '{host}'    => 'host#'.$HostModel->id.'#'.$HostModel->name.'#',
                '{dcim_id}' => $hostLink['rel_id'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => $res['msg'] ?: lang_plugins('mf_dcim_free_fail'),
            ];

            $description = lang_plugins('mf_dcim_log_free_dcim_server_fail', [
                '{host}'    => 'host#'.$HostModel->id.'#'.$HostModel->name.'#',
                '{reason}'  => $res['msg'],
            ]);
        }
        active_log($description, 'host', $param['id']);

        return $result;
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
     * @return  int model_config_id - 机型配置ID
     * @return  int image_id - 操作系统ID
     * @return  string bw - 带宽
     * @return  string flow - 流量
     * @return  string peak_defence - 防御
     * @return  string ip_num - 公网IP数量
     */
    public function currentConfig($hostId)
    {
        $hostLink = $this->where('host_id', $hostId)->find();
        if(empty($hostLink)){
            return [];
        }
        $configData = json_decode($hostLink['config_data'], true);

        $productId = HostModel::where('id', $hostId)->value('product_id');

        $data = [];
        // 匹配周期
        if(isset($configData['duration']['id'])){
            $durationId = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->value('id') ?? 0;
            $data['duration_id'] = $durationId ?: $configData['duration']['id'];
        }
        $data['data_center_id'] = $hostLink['data_center_id'];
        $data['line_id'] = $configData['line']['id'] ?? 0;
        $data['image_id'] = $hostLink['image_id'];
        $data['model_config_id'] = $configData['model_config']['id'] ?? 0;
        $data['bw'] = $configData['bw']['value'] ?? '';
        $data['flow'] = $configData['flow']['value'] ?? '';
        $data['peak_defence'] = $configData['defence']['value'] ?? '';
        $data['ip_num'] = $configData['ip']['value'] ?? '';
        
        return $data;
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
        $adminField = $configData['admin_field'] ?? $configData['model_config'] ?? [];
        if(empty($adminField)){
            return '';
        }
        $data = [
            'cpu'               => ToolLogic::packageConfigLanguage($adminField['cpu']),
            'memory'            => ToolLogic::packageConfigLanguage($adminField['memory']),
            'disk'              => ToolLogic::packageConfigLanguage($adminField['disk']),
        ];
        if(isset($adminField['gpu']) && !empty($adminField['gpu'])){
            $data['gpu'] = ToolLogic::packageConfigLanguage($adminField['gpu']);
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
<?php 
namespace server\mf_cloud\logic;

use addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel;
use app\admin\model\PluginModel;
use app\common\model\OrderItemModel;
use app\common\model\ProductGroupModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\UpgradeModel;
use app\common\model\UpstreamHostModel;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\HostImageLinkModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\DiskModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\LineModel;
use server\mf_cloud\model\VpcNetworkModel;
use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\model\RecommendConfigUpgradeRangeModel;
use server\mf_cloud\model\PriceModel;
use server\mf_cloud\model\ImageGroupModel;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\IpDefenceModel;
use server\mf_cloud\model\BackupConfigModel;
use server\mf_cloud\model\DurationRatioModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ClientModel;
use think\facade\Cache;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use app\common\model\HostIpModel;
use server\mf_cloud\model\LimitRuleModel;
use app\common\model\HostAdditionModel;
use app\common\model\ProductOnDemandModel;

class CloudLogic
{
    protected $id = 0;   				// 魔方云ID
    protected $idcsmartCloud = null;	// 魔方云操作类型
    protected $hostModel = [];			// 产品模型
    protected $isClient = false;        // 是否是客户操作
    protected $downstreamHostLogic = null; // 下游操作类

    public function __construct($hostId,$agent=false){
        if ($agent){
            $HostLinkModel = HostLinkModel::where('host_id', $hostId)->find();
            $this->id = $HostLinkModel['rel_id'] ?? 0;
            $HostModel = HostModel::find($hostId);
            if(empty($HostModel) || $HostModel['is_delete']){
                throw new \Exception(lang_plugins('host_is_not_exist'));
            }
            $this->hostModel = $HostModel;
            $this->hostLinkModel = $HostLinkModel;
        }else{
            $HostLinkModel = HostLinkModel::where('host_id', $hostId)->find();
            $this->id = $HostLinkModel['rel_id'] ?? 0;

            $HostModel = HostModel::find($hostId);
            if(empty($HostModel) || $HostModel['is_delete']){
                throw new \Exception(lang_plugins('host_is_not_exist'));
            }
            // 是否是魔方云模块
            if($HostModel->getModule() != 'mf_cloud'){
                throw new \Exception(lang_plugins('can_not_do_this'));
            }
            // 获取模块通用参数,
            $params = $HostModel->getModuleParams();
            if(empty($params['server'])){
                throw new \Exception(lang_plugins('host_not_link_server'));
            }

            $hash = ToolLogic::formatParam($params['server']['hash']);

            $this->idcsmartCloud = new IdcsmartCloud($params['server']);
            $this->idcsmartCloud->setIsAgent(isset($hash['account_type']) && $hash['account_type'] == 'agent');

            $this->hostModel = $params['host'];
            $this->server = $params['server'];
            $this->hostLinkModel = $HostLinkModel;

            $this->downstreamHostLogic = new DownstreamCloudLogic($params['host']);

            // 前台用户验证
            $app = app('http')->getName();
            if($app == 'home'){
                if($HostModel['client_id'] != get_client_id()){
                    throw new \Exception(lang_plugins('host_is_not_exist'));
                }
            }
            $this->isClient = $app == 'home';
        }
    }

    /**
     * 时间 2022-06-22
     * @title 获取电源状态
     * @desc 获取电源状态
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.status - 实例状态(pending=开通中,on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
     * @return  string data.desc - 实例状态描述
     */
    public function status()
    {
        if(in_array($this->hostModel['status'], ['Pending','Failed'])){
            $status = [
                'status' => 'pending',
                'desc'   => lang_plugins('power_status_pending'),
            ];
        }else{
            // 获取所有设置
            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
            $config = $config['data'];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
                if(!empty($manual_resource)){
                    $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                    $res = $ManualResourceLogic->status($manual_resource['id']);
                    if($res['status'] == 200){
                        if($res['data']['status'] == 'on'){
                            $status = [
                                'status' => 'on',
                                'desc'   => lang_plugins('on'),
                            ];
                        }else if($res['data']['status'] == 'off'){
                            $status = [
                                'status' => 'off',
                                'desc'   => lang_plugins('off')
                            ];
                        }else if($res['data']['status'] == 'suspend'){
                            $status = [
                                'status' => 'suspend',
                                'desc'   => lang_plugins('suspend'),
                            ];
                        }else if($res['data']['status'] == 'operating'){
                            $status = [
                                'status' => 'operating',
                                'desc'   => lang_plugins('operating')
                            ];
                        }else{
                            $status = [
                                'status' => 'fault',
                                'desc'   => lang_plugins('fault'),
                            ];
                        }
                    }else{
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('fault'),
                        ];
                    }
                }else{
                    $status = [
                        'status' => 'on',
                        'desc'   => lang_plugins('on'),
                    ];
                    // $HostAdditionModel = new HostAdditionModel();
                    // $hostAddition = $HostAdditionModel->where('host_id', $this->hostModel['id'])->find();
                    // $status = [
                    //     'status' => $hostAddition['power_status'] ?: 'on',
                    //     'desc'   => lang_plugins($hostAddition['power_status'] ?: 'on'),
                    // ];
                }
            }else{
                if($this->downstreamHostLogic->isDownstream()){
                    $res = $this->downstreamHostLogic->status();
                }else{
                    $res = $this->idcsmartCloud->cloudStatus($this->id);
                }
                if($res['status'] == 200){
                    if(in_array($res['data']['status'], ['on','wait_reboot','paused'])){
                        $status = [
                            'status' => 'on',
                            'desc'   => lang_plugins('on'),
                        ];
                    }else if($res['data']['status'] == 'off'){
                        $status = [
                            'status' => 'off',
                            'desc'   => lang_plugins('off')
                        ];
                    }else if($res['data']['status'] == 'suspend'){
                        $status = [
                            'status' => 'suspend',
                            'desc'   => lang_plugins('suspend'),
                        ];
                    }else if(in_array($res['data']['status'], ['task','cold_migrate','hot_migrate','operating'])){
                        $status = [
                            'status' => 'operating',
                            'desc'   => lang_plugins('operating')
                        ];
                    }else if($res['data']['status'] == 'pending'){
                        $status = [
                            'status' => 'pending',
                            'desc'   => lang_plugins('power_status_pending'),
                        ];
                    }else{
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('fault'),
                        ];
                    }
                }else{
                    $status = [
                        'status' => 'fault',
                        'desc'   => lang_plugins('fault'),
                    ];
                }
            }
        }
        
        $HostAdditionModel = new HostAdditionModel();
        $HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
            'power_status'    => $status['status'],
        ]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $status,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 开机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function on()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->on($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_boot_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->on();
            }else{
                $res = $this->idcsmartCloud->cloudOn($this->id);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_boot_success')
            ];

            $description = lang_plugins('log_host_start_boot_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_boot_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('on'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'on',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_boot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_boot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('on')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_boot_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_boot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 关机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function off()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->off($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_off_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->off();
            }else{
                $res = $this->idcsmartCloud->cloudOff($this->id);
            }
        }
        
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_off_success')
            ];

            $description = lang_plugins('log_host_start_off_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_off_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('off'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'off',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('off')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_off_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_off_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 强制关机
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function hardOff()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->hardOff($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_hard_off_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->hardOff();
            }else{
                $res = $this->idcsmartCloud->cloudHardOff($this->id);
            }
        }
        
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_hard_off_success')
            ];

            $description = lang_plugins('log_host_start_hard_off_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_hard_off_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('hard_off'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'hard_off',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_hard_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_hard_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('hard_off')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_hard_off_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_hard_off_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 重启
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function reboot()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->reboot($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_reboot_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->reboot();
            }else{
                $res = $this->idcsmartCloud->cloudReboot($this->id);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_reboot_success')
            ];

            $description = lang_plugins('log_host_start_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_reboot_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('reboot'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'reboot',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('reboot')])
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_reboot_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reboot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 强制重启
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function hardReboot()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->hardReboot($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_hard_reboot_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->hardReboot();
            }else{
                $res = $this->idcsmartCloud->cloudHardReboot($this->id);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_hard_reboot_success')
            ];

            $description = lang_plugins('log_host_start_hard_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_hard_reboot_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('hard_reboot'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'hard_reboot',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_hard_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_hard_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('hard_reboot')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_hard_reboot_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_hard_reboot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-07-01
     * @title 获取控制台地址
     * @desc 获取控制台地址
     * @author hh
     * @version v1
     * @param   int param.more 0 是否获取更多返回(0=否,1=是)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.url - 控制台地址
     * @return  string data.vnc_url - vncwebsocket地址
     * @return  string data.vnc_pass - VNC密码
     * @return  string data.password - 实例密码
     * @return  string data.token - 临时令牌
     */
    public function vnc($param)
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->vnc($manual_resource['id']);
                if($res['status']==200){
                    $result = [
                        'status' => 200,
                        'msg'    => lang_plugins('success_message'),
                        'data'	 => [],
                    ];
                    $cache = Cache::get('manual_resource_vnc_'.$manual_resource['id']);
                    if(!empty($cache)){
                        if($this->isClient){
                            $result['data']['url'] = request()->domain().'/console/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                        }else{
                            $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                        }
                        // 生成一个临时token
                        $token = md5(rand_str(16));
                        $cache['token'] = $token;

                        Cache::set('mf_cloud_vnc_'.$this->hostModel['id'], $cache, 30*60);
                        if(strpos($result['data']['url'], '?') !== false){
                            $result['data']['url'] .= '&tmp_token='.$token;
                        }else{
                            $result['data']['url'] .= '?tmp_token='.$token;
                        }

                    }else{
                        $result['data']['url'] = $res['data']['vnc_url'];
                    }
                }else{
                    /*$result = $res;*/
                    $result = [
                        'status' => 400,
                        'msg'   => lang_plugins('vnc_start_failed'),
                    ];
                }
            }else{
                $result = [
                    'status' => 400,
                    'msg'    => lang_plugins('vnc_start_failed')
                ];
            }
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->vnc();
            }else{
                $res = $this->idcsmartCloud->cloudVnc($this->id);
            }

            if($res['status'] == 200){
                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('success_message'),
                    'data'	 => [],
                ];

                if(!empty($res['data']['vnc_url_http']) && !empty($res['data']['vnc_url_https'])){
                    // 外部vnc地址
                    if(request()->scheme() == 'https'){
                        $result['data']['url'] = $res['data']['vnc_url_https'];
                    }else{
                        $result['data']['url'] = $res['data']['vnc_url_http'];
                    }
                    $result['data']['vnc_url_https'] = $res['data']['vnc_url_https'];
                    $result['data']['vnc_url_http'] = $res['data']['vnc_url_http'];
                }else{
                    if(strpos($res['data']['vnc_url'], 'wss://') === 0 || strpos($res['data']['vnc_url'], 'ws://') === 0){
                        $link_url = $res['data']['vnc_url'];
                    }else{
                        if(strpos($this->server['url'], 'https://') !== false){
                            $link_url = str_replace('https://', 'wss://', $this->server['url']);
                        }else{
                            $link_url = str_replace('http://', 'ws://', $this->server['url']);
                        }
                        // vnc不能包含管理员路径
                        $link_url = rtrim($link_url, '/');
                        if(substr_count($link_url, '/') > 2){
                            $link_url = substr($link_url, 0, strrpos($link_url, '/'));
                        }
                        $link_url .= '/cloud_ws'.$res['data']['path'].'?token='.$res['data']['token'];
                    }
                    // 获取的东西放入缓存
                    $cache = [
                        'vnc_url' => $link_url,
                        'vnc_pass'=>$res['data']['vnc_pass'],
                        'password'=>$res['data']['password'],
                    ];
                    if($this->isClient){
                        $result['data']['url'] = request()->domain().'/console/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                    }else{
                        $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                    }

                    // 生成一个临时token
                    $token = md5(rand_str(16));
                    $cache['token'] = $token;

                    Cache::set('mf_cloud_vnc_'.$this->hostModel['id'], $cache, 30*60);
                    if(strpos($result['data']['url'], '?') !== false){
                        $result['data']['url'] .= '&tmp_token='.$token;
                    }else{
                        $result['data']['url'] .= '?tmp_token='.$token;
                    }
                    if(isset($param['more']) && $param['more'] == 1){
                        $result['data'] = array_merge($result['data'], $cache);
                    }
                }
            }else{
                $result = [
                    'status' => 400,
                    'msg'    => lang_plugins('vnc_start_failed')
                ];
            }
        }
        return $result;
    }

    /**
     * 时间 2022-06-24
     * @title 重置密码
     * @desc 重置密码
     * @author hh
     * @version v1
     * @param   string param.password - 新密码 require
     * @param   string param.code - 二次验证验证码
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function resetPassword($param)
    {
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        // 非代理时验证手机号
        if($this->isClient && isset($config['data']['reset_password_sms_verify']) && $config['data']['reset_password_sms_verify'] && !request()->is_api){
            $ClientModel = new ClientModel();
            $res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
            if($res['status'] == 400){
                return $res;
            }
        }

        if($config['data']['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->resetPassword(['id' => $manual_resource['id'], 'password' => $param['password']]);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_reset_password_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->resetPassword(['password'=>$param['password'] ]);
            }else{
                $res = $this->idcsmartCloud->cloudResetPassword($this->id, $param['password']);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_reset_password_success'),
            ];

            HostLinkModel::update(['ssh_key_id'=>0], ['host_id'=>$this->hostModel['id']]);

            $HostAdditionModel = new HostAdditionModel();
            $HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
                'password'	=> $param['password'],
            ]);

            $description = lang_plugins('log_host_start_reset_password_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_reset_password_failed')
            ];

            if($config['data']['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('reset_password'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'reset_password',
                    'client_id'                 => $this->hostModel['client_id'],
                    'data'						=> [
                        'password' => $param['password']
                    ]
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_reset_password_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reset_password_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('reset_password')])
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_reset_password_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reset_password_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-24
     * @title 救援模式
     * @desc 救援模式
     * @author hh
     * @version v1
     * @param   int param.type - 指定救援系统类型(1=windows,2=linux) require
     * @param   string param.password - 救援系统临时密码 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function rescue($param)
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->rescue(['id' => $manual_resource['id'], 'system'=>$param['type']==1 ? 2 : 1, 'temp_pass' => $param['password']]);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_rescue_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->rescue($param);
            }else{
                $res = $this->idcsmartCloud->cloudRescue($this->id, ['type'=>$param['type'], 'temp_pass'=>$param['password']]);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_rescue_success')
            ];

            $description = lang_plugins('log_host_start_rescue_success', ['{hostname}'=>$this->hostModel['name']]);

        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_rescue_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('rescue'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'rescue',
                    'client_id'                 => $this->hostModel['client_id'],
                    'data'						=> [
                        'system'   => intval($param['type']==1 ? 2 : 1),
                        'password' => $param['password']
                    ]
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('rescue')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_rescue_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_rescue_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-24
     * @title 退出救援模式
     * @desc 退出救援模式
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function exitRescue()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->exitRescue(['id' => $manual_resource['id']]);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_exit_rescue_failed')
                ];
            }		
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->exitRescue();
            }else{
                $res = $this->idcsmartCloud->cloudExitRescue($this->id);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_exit_rescue_success')
            ];

            $description = lang_plugins('log_host_start_exit_rescue_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_exit_rescue_failed')
            ];

            if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('exit_rescue'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'exit_rescue',
                    'client_id'                 => $this->hostModel['client_id'],
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_exit_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_exit_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('exit_rescue')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('log_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-30
     * @title 重装系统
     * @desc 重装系统
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.image_id - 镜像ID require
     * @param   int param.password - 密码 密码和ssh密钥ID,必须选择一种
     * @param   int param.ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
     * @param   int param.port - 端口 require
     * @param   int param.format_data_disk 0 是否格式化数据盘(0=不格式,1=格式化)
     * @param   string param.code - 二次验证验证码
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function reinstall($param)
    {
        $param['format_data_disk'] = $param['format_data_disk'] ?? 0;
        $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
        // 仅当变更时验证
        if($currentConfig['image_id'] != $param['image_id']){
            $currentConfig['image_id'] = $param['image_id'];
        
            $LimitRuleModel = new LimitRuleModel();
            $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig, ['image']);
            if($checkLimitRule['status'] == 400){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_reinstall_for_limit_rule')];
            }
        }
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

        if($config['data']['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->reinstall(['id' => $manual_resource['id'], 'os' => $param['image_id'], 'password' => $param['password'], 'port' => $param['port'], 'format_data_disk'=>$param['format_data_disk']]);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('start_reinstall_failed')
                ];
            }	

            if($res['status']==400 && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){	
                $image = ImageModel::find($param['image_id']);
                if(empty($image) || $this->hostModel['product_id'] != $image['product_id']){
                    return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
                }

                // 获取镜像分组
                $imageGroup = ImageGroupModel::find($image['image_group_id']);
                $isWindows = !empty($imageGroup) ? $imageGroup->isWindows($imageGroup) : false;

                // 前台
                if($this->isClient){
                    if($image['enable'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
                    }
                    if($image['charge'] == 1 && $image['price']>0){
                        $hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
                        if(empty($hostImageLink)){
                            return ['status'=>400, 'msg'=>lang_plugins('image_is_charge_please_buy')];
                        }
                    }
                    // 非代理时验证手机号
                    if(isset($config['data']['reinstall_sms_verify']) && $config['data']['reinstall_sms_verify'] && !request()->is_api){
                        $ClientModel = new ClientModel();
                        $res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
                        if($res['status'] == 400){
                            return $res;
                        }
                    }
                }

                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'	=> $this->hostModel['id'],
                        'template_param'=>[
                            'module_action' => lang_plugins('reinstall'),
                        ],
                    ],
                ]);

                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
                $ManualResourceLogModel->createLog([
                    'host_id'                   => $this->hostModel['id'],
                    'type'                      => 'reinstall',
                    'client_id'                 => $this->hostModel['client_id'],
                    'data'						=> [
                        'os' 			=> intval($param['image_id']),
                        'image_icon'    => $imageGroup['icon'] ?? '',
                        'image_name'    => $image['name'],
                        'username'		=> $isWindows ? 'administrator' : 'root',
                        'password'		=> $param['password'],
                        'port' 			=> intval($param['port']),
                    ]
                ]);

                if($this->isClient){
                    $description = lang_plugins('log_host_start_reinstall_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reinstall_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_cloud_manual_manage_action_in_progress', ['{action}' => lang_plugins('reinstall')]),
                ];
            }else{
                if($res['status'] == 200){
                    $result = [
                        'status'=>200,
                        'msg'=>lang_plugins('start_reinstall_success'),
                    ];

                    $description = lang_plugins('log_host_start_reinstall_success', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $result = [
                        'status'=>400,
                        'msg'=>lang_plugins('start_reinstall_failed'),
                    ];

                    if($this->isClient){
                        $description = lang_plugins('log_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name']]);
                    }else{
                        $description = lang_plugins('log_admin_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                        $result['msg'] = $res['msg'];
                    }
                }
            }
            active_log($description, 'host', $this->hostModel['id']);
        }else{
            $image = ImageModel::find($param['image_id']);
            if(empty($image) || $this->hostModel['product_id'] != $image['product_id']){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }

            // 获取镜像分组
            $imageGroup = ImageGroupModel::find($image['image_group_id']);
            $isWindows = !empty($imageGroup) ? $imageGroup->isWindows($imageGroup) : false;

            // 端口配置
            if($config['data']['rand_ssh_port'] == 2){
                // 指定端口
                if($isWindows){
                    $port = $config['data']['rand_ssh_port_windows'] ?: 3389;
                }else{
                    $port = $config['data']['rand_ssh_port_linux'] ?: 22;
                }
            }else{
                $port = $param['port'];
            }
            // 前台
            if($this->isClient){
                if($image['enable'] == 0){
                    return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
                }
                if($image['charge'] == 1 && $image['price']>0){
                    $hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
                    if(empty($hostImageLink)){
                        return ['status'=>400, 'msg'=>lang_plugins('image_is_charge_please_buy')];
                    }
                }
                // 非代理时验证手机号
                if(isset($config['data']['reinstall_sms_verify']) && $config['data']['reinstall_sms_verify'] && !request()->is_api){
                    $ClientModel = new ClientModel();
                    $res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
                    if($res['status'] == 400){
                        return $res;
                    }
                }
            }

            // 更新参数
            $update = [];
            $update['image_id'] = $param['image_id'];

            if($this->downstreamHostLogic->isDownstream()){
                if(isset($param['password']) && !empty($param['password'])){
                    
                }else{
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_ssh_key')];
                }

                $post = [];
                // 替换参数
                $post['image_id'] = $image['upstream_id'];
                $post['port'] = $port;
                $post['password'] = $param['password'];
                $post['format_data_disk'] = $param['format_data_disk'];

                $update['ssh_key_id'] = 0;

                $res = $this->downstreamHostLogic->reinstall($post);
            }else{
                // 请求数据
                $post = [];
                // 更新数据
                $post['os'] = $image['rel_image_id'];
                $post['format_data_disk'] = $param['format_data_disk'];
                
                if(isset($param['password']) && !empty($param['password'])){
                    $post['password'] = $param['password'];
                    $post['password_type'] = 0;

                    // $update['password'] = aes_password_encode($param['password']);
                    $update['ssh_key_id'] = 0;
                }else{
                    if($config['data']['support_ssh_key'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_ssh_key')];
                    }
                    if(stripos($image['name'], 'win') !== false){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_windows_cannot_use_ssh_key')];
                    }
                    // 先获取当前实例详情
                    $detail = $this->idcsmartCloud->cloudDetail($this->id);
                    if($detail['status'] != 200){
                        return ['status'=>400, 'msg'=>$detail['msg'] ?: lang_plugins('start_reinstall_failed')];
                    }
                    // 使用密钥
                    $sshKey = IdcsmartSshKeyModel::where('id', $param['ssh_key_id'] ?? 0)->where('client_id', $this->hostModel['client_id'])->find();
                    if(empty($sshKey)){
                        return ['status'=>400, 'msg'=>lang_plugins('ssh_key_not_found')];
                    }
                    $sshKeyRes = $this->idcsmartCloud->sshKeyCreate([
                        'type' 		=> 1,
                        'uid'  		=> $detail['data']['user_id'],
                        'name'		=> 'skey_'.rand_str(),
                        'public_key'=> $sshKey['public_key'],
                    ]);
                    if($sshKeyRes['status'] != 200){
                        return ['status'=>400, 'msg'=>lang_plugins('ssh_key_create_failed')];
                    }
                    $post['ssh_key'] = $sshKeyRes['data']['id'];
                    $post['password_type'] = 1;

                    $param['password'] = '';
                    // $update['password'] = aes_password_encode('');
                    $update['ssh_key_id'] = $param['ssh_key_id'];
                }
                $post['port'] = $port;

                $res = $this->idcsmartCloud->cloudReinstall($this->id, $post);
            }
            if($res['status'] == 200){
                $result = [
                    'status'=>200,
                    'msg'=>lang_plugins('start_reinstall_success'),
                ];

                $this->hostLinkModel->update($update, ['host_id'=>$this->hostModel['id']]);

                // 重装后保存配置到附加表
                if(class_exists('app\common\model\HostAdditionModel')){
                    $HostAdditionModel = new HostAdditionModel();
                    $HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
                        'image_icon'    => $imageGroup['icon'] ?? '',
                        'image_name'    => $image['name'],
                        'username'		=> $isWindows ? 'administrator' : 'root',
                        'password'		=> $param['password'],
                        'port'			=> $res['data']['port'] ?? $port,
                    ]);
                }

                $description = lang_plugins('log_host_start_reinstall_success', ['{hostname}'=>$this->hostModel['name']]);
            }else{
                $result = [
                    'status'=>400,
                    'msg'=>lang_plugins('start_reinstall_failed'),
                ];

                if($this->isClient){
                    $description = lang_plugins('log_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('log_admin_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
                    $result['msg'] = $res['msg'];
                }
            }
            active_log($description, 'host', $this->hostModel['id']);
        }
        return $result;
    }

    /**
     * 时间 2022-06-24
     * @title 获取图表数据
     * @desc 获取图表数据
     * @author hh
     * @version v1
     * @param   int param.start_time - 开始秒级时间
     * @param   string param.type - 图表类型(cpu=CPU,memory=内存,disk_io=硬盘IO,bw=带宽)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 图表数据
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
        // 验证type
        if(!isset($param['type']) || !is_string($param['type']) || !in_array($param['type'], ['cpu','memory','disk_io','bw'])){
            return ['status'=>400, 'msg'=>lang_plugins('chart_type_error')];
        }
        if($this->downstreamHostLogic->isDownstream()){
            return $this->downstreamHostLogic->chart($param);
        }

        $result = [
            'status' => 200,
            'msg'	 => lang_plugins('success_message'),
            'data'	 => [],
        ];

        $detail = $this->idcsmartCloud->cloudDetail($this->id);
        if($detail['status'] != 200){
            return $result;
        }
        $data = [];
        $data['node_id'] = $detail['data']['node_id'];
        $data['kvm'] = $detail['data']['kvmid'];

        // 时间选择,起始结束
        $data['st'] = $this->hostModel['active_time'] ?: $this->hostModel['create_time'];
        if(isset($param['start_time']) && !empty($param['start_time'])){
            if($param['start_time'] >= $data['st']){
                $data['st'] = $param['start_time'];
            }
        }
        $data['st'] .= '000';

        // 类型转换
        if($param['type'] == 'cpu'){
            $data['type'] = 'kvm_info';
        }else if($param['type'] == 'memory'){
            $data['type'] = 'kvm_info';
        }else if($param['type'] == 'disk_io'){
            $data['type'] = 'disk_io';
            $data['dev_name'] = $param['dev'] ?? 'vda';  // 选择的磁盘
        }else if($param['type'] == 'bw'){
            $data['type'] = 'net_adapter';
            $data['kvm_ifname'] = $detail['data']['kvmid'] . '.0'; // 第一个网卡
        }else{

        }
        $res = $this->idcsmartCloud->chart($data);

        if(isset($res['data']) && !empty($res['data'])){
            // 转换格式
            if($param['type'] == 'cpu'){
                foreach($res['data'] as $v){
                    $result['data']['list'][] = [
                        'time'	=> strtotime($v[0]),
                        'value'	=> $v[1] ?? 0,
                    ];
                }
            }else if($param['type'] == 'memory'){
                foreach($res['data'] as $v){
                    $result['data']['list'][] = [
                        'time'	=> strtotime($v[0]),
                        'total'	=> $v[2] ?? 0,
                        'used'	=> $v[3] ?? 0,
                    ];
                }
            }else if($param['type'] == 'disk_io'){
                foreach($res['data'] as $v){
                    $result['data']['list'][] = [
                        'time'		  => strtotime($v[0]),
                        'read_bytes'  => $v[1] ?? 0,
                        'write_bytes' => $v[2] ?? 0,
                        'read_iops'   => $v[3] ?? 0,
                        'write_iops'  => $v[4] ?? 0,
                    ];
                }
            }else if($param['type'] == 'bw'){
                foreach($res['data'] as $v){
                    $result['data']['list'][] = [
                        'time'	 => strtotime($v[0]),
                        'in_bw'  => $v[1] ?? 0,
                        'out_bw' => $v[2] ?? 0,
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * 时间 2022-06-30
     * @title 网络流量
     * @desc 网络流量
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
     * @return  float data.base_flow - 基础流量(0=不限)
     * @return  float data.temp_flow - 临时流量
     * @return  float data.flow_packet.leave_size - 流量包剩余流量大小(GB)
     * @return  int data.flow_packet.total_size - 流量包总大小(GB)
     * @return  float data.flow_packet.used_size - 流量包已用大小(GB)
     */
    public function flowDetail()
    {
        if($this->downstreamHostLogic->isDownstream()){
            return $this->downstreamHostLogic->flowDetail();
        }

        $res = $this->idcsmartCloud->netInfo($this->id);

        if($res['status'] == 200 && !empty($res['data'])){
            $total = $res['data']['meta']['traffic_quota'] > 0 ? $res['data']['meta']['traffic_quota'] + $res['data']['meta']['tmp_traffic'] : 0;
            if($res['data']['meta']['traffic_type'] == 1){
                $used = round($res['data']['info']['30_day']['accept']/1024/1024/1024, 2);
            }else if($res['data']['meta']['traffic_type'] == 2){
                $used = round($res['data']['info']['30_day']['send']/1024/1024/1024, 2);
            }else{
                $used = round($res['data']['info']['30_day']['total']/1024/1024/1024, 2);
            }

            $resetFlowDay = $res['data']['meta']['reset_flow_day'] ?? 1;

            $time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00'));
            if(time() > $time){
                $time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00') .' +1 month');
            }

            // 新流量包部分
            if($total > 0 && !empty($res['data']['meta']['traffic_package'])){
                $traffic_package = [
                    'total_size' => $res['data']['meta']['traffic_package']['total_size'],
                    'used_size' => $res['data']['meta']['traffic_package']['used_size'],
                    'leave_size' => $res['data']['meta']['traffic_package']['leave_size'],
                ];
                $total += $traffic_package['total_size'];
            }else{
                $traffic_package = [
                    'total_size' => 0,
                    'used_size' => 0,
                    'leave_size' => 0,
                ];
            }
            $leave = max(round($total - $used, 2), 0);

            $result = [
                'status'=>200,
                'msg'=>lang_plugins('success_message'),
                'data'=>[
                    'total'=>$total == 0 ? lang_plugins('not_limited') : $total.'GB',
                    'used'=>$used.'GB',
                    'leave'=>$total == 0 ? lang_plugins('not_limited') : $leave.'GB',
                    'reset_flow_date'=>date('Y-m-d', $time),
                    'total_num' => $total,
                    'used_num'  => $used,
                    'base_flow' => $res['data']['meta']['traffic_quota'],
                    'temp_flow' => $res['data']['meta']['tmp_traffic'],
                    'flow_packet' => $traffic_package,
                ],
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('flow_info_get_failed')
            ];
        }
        return $result;
    }

    /**
     * 时间 2022-07-11
     * @title 获取实例磁盘
     * @desc 获取实例磁盘
     * @author theworld
     * @version v1
     * @return  int list[].id - 磁盘ID
     * @return  string list[].name - 名称
     * @return  int list[].size - 磁盘大小(GB)
     * @return  int list[].create_time - 创建时间
     * @return  string list[].type - 磁盘类型
     * @return  string list[].type2 - 类型(system=系统盘,data=数据盘)
     * @return  int list[].is_free - 是否免费盘(0=否,1=是),免费盘不能扩容
     * @return  int list[].status - 磁盘状态(0=卸载,1=挂载,2=正在挂载,3=创建中)
     * @return  string list[].type2 - 类型(system=系统盘,data=数据盘)
     */
    public function diskList()
    {
        $DiskModel = new DiskModel();
        $diskList = $DiskModel->diskList($this->hostModel['id']);
        $diskStatus = [];

        // 进行磁盘状态同步
        // if(!empty($diskList)){
            $sync = false;
            $hasSystemDisk = false;

            foreach($diskList as $v){
                if($v['type2'] == 'system'){
                    $hasSystemDisk = true;
                }
                if($v['status'] == 3){
                    $sync = true;
                }
            }
            if(!$hasSystemDisk){
                $sync = true;
            }
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->diskList();
                
                if($res['status'] == 200){
                    $diskStatus = array_column($res['data']['list'], 'status', 'id');
                    if($sync){
                        // 上游ID
                        $upstreamId = $DiskModel->where('host_id', $this->hostModel['id'])->where('upstream_id', '>', 0)->column('upstream_id');

                        foreach($res['data']['list'] as $v){
                            if($v['type2'] == 'system'){
                                if(!$hasSystemDisk){
                                    $DiskModel->insert([
                                        'name'          => $v['name'],
                                        'size'          => $v['size'],
                                        'host_id'       => $this->hostModel['id'],
                                        'type'          => $v['type'],
                                        'price'         => '0.00',
                                        'create_time'   => $v['create_time'],
                                        'is_free'       => 1,
                                        'status'        => 1,
                                        'type2'         => 'system',
                                        'upstream_id'   => $v['id'],
                                    ]);
                                }else{
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('type2', 'system')->update([
                                        'name'      	=> $v['name'],
                                        'size'      	=> $v['size'],
                                        'type'			=> $v['type'],
                                        'upstream_id'   => $v['id'],
                                    ]);
                                }
                            }else{
                                if(in_array($v['id'], $upstreamId)){
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('upstream_id', $v['id'])->update([
                                        'name'      => $v['name'],
                                        'size'      => $v['size'],
                                        'type'		=> $v['type'],
                                        'status'    => $v['status'],
                                    ]);
                                }else{
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('upstream_id', 0)->where('size', $v['size'])->where('type', $v['type'])->limit(1)->update([
                                        'name'      	=> $v['name'],
                                        'upstream_id'   => $v['id'],
                                        'status'    	=> $v['status'],
                                    ]);
                                }
                            }
                        }
                        // 同步后重新获取
                        $diskList = $DiskModel->diskList($this->hostModel['id']);
                    }
                    // 使用远程的状态
                    foreach($diskList as $k=>$v){
                        if($v['type2'] == 'data' && isset($diskStatus[ $v['upstream_id'] ])){
                            $diskList[$k]['status'] = $diskStatus[ $v['upstream_id'] ];
                        }
                        unset($diskList[$k]['rel_id'], $diskList[$k]['upstream_id']);
                    }
                }
            }else{
                // 获取磁盘列表
                $res = $this->idcsmartCloud->cloudDetail($this->id);
                if($res['status'] == 200){
                    $diskStatus = array_column($res['data']['disk'], 'status', 'id');

                    if($sync){
                        // 已关联的ID
                        $relId = $DiskModel->where('host_id', $this->hostModel['id'])->where('rel_id', '>', 0)->column('rel_id');

                        foreach($res['data']['disk'] as $v){
                            if($v['type'] == 'system'){
                                if(!$hasSystemDisk){
                                    $DiskModel->insert([
                                        'name'          => $v['name'],
                                        'size'          => $v['size'],
                                        'host_id'       => $this->hostModel['id'],
                                        'type'          => '',
                                        'price'         => '0.00',
                                        'create_time'   => $this->hostModel['create_time'],
                                        'is_free'       => 1,
                                        'status'        => 1,
                                        'type2'         => 'system',
                                        'rel_id'        => $v['id'],
                                    ]);
                                }else{
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('type2', 'system')->update([
                                        'name'      => $v['name'],
                                        'size'      => $v['size'],
                                        'rel_id'    => $v['id'],
                                    ]);
                                }
                            }else{
                                if(in_array($v['id'], $relId)){
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('rel_id', $v['id'])->update([
                                        'name'      => $v['name'],
                                        'size'      => $v['size'],
                                        'status'    => $v['status'],
                                    ]);
                                }else{
                                    $DiskModel->where('host_id', $this->hostModel['id'])->where('size', $v['size'])->where('rel_id', 0)->limit(1)->update([
                                        'name'      => $v['name'],
                                        'rel_id'    => $v['id'],
                                        'status'    => $v['status'],
                                    ]);
                                }
                            }
                        }
                        // 同步后重新获取
                        $diskList = $DiskModel->diskList($this->hostModel['id']);
                    }

                    // 使用远程的状态
                    foreach($diskList as $k=>$v){
                        if($v['type2'] == 'data' && isset($diskStatus[ $v['rel_id'] ])){
                            $diskList[$k]['status'] = $diskStatus[ $v['rel_id'] ];
                        }
                        unset($diskList[$k]['rel_id'], $diskList[$k]['upstream_id']);
                    }
                }
            }
        // }
        return ['list' => $diskList];
    }

    /**
     * 时间 2023-02-10
     * @title 卸载磁盘
     * @desc 卸载磁盘
     * @author hh
     * @version v1
     * @param   int disk_id - 磁盘ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 磁盘名称
     */
    public function diskUnmount($disk_id)
    {
        $disk = DiskModel::where('host_id', $this->hostModel['id'])->where('id', $disk_id)->find();
        if(empty($disk) || $disk['type2'] != 'data'){
            return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->diskUnmount([
                'disk_id'	=> $disk['upstream_id'],
            ]);
        }else{
            $res = $this->idcsmartCloud->cloudUmountDisk($this->id, $disk['rel_id']);
        }
        if($res['status'] == 200){
            // 创建成功
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('mf_cloud_unmount_disk_success')
            ];

            $description = lang_plugins('log_mf_cloud_host_unmount_disk_success', [
                '{hostname}'    => $this->hostModel['name'],
                '{name}'        => $disk['name'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_cloud_unmount_disk_fail')
            ];

            $description = lang_plugins('log_mf_cloud_host_unmount_disk_fail', [
                '{hostname}'    => $this->hostModel['name'],
                '{name}'        => $disk['name'],
            ]);
        }
        $result['data']['name'] = $disk['name'];

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-02-10
     * @title 挂载磁盘
     * @desc 挂载磁盘
     * @author hh
     * @version v1
     * @param   int disk_id - 魔方云磁盘ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 磁盘名称
     */
    public function diskMount($disk_id)
    {
        $disk = DiskModel::where('host_id', $this->hostModel['id'])->where('id', $disk_id)->find();
        if(empty($disk) || $disk['type2'] != 'data'){
            return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->diskMount([
                'disk_id'	=> $disk['upstream_id'],
            ]);
        }else{
            $res = $this->idcsmartCloud->diskMount($disk['rel_id']);
        }
        if($res['status'] == 200){
            // 创建成功
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('mf_cloud_mount_disk_success')
            ];

            $description = lang_plugins('log_mf_cloud_host_mount_disk_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$disk['name']
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_cloud_mount_disk_fail')
            ];

            $description = lang_plugins('log_mf_cloud_host_mount_disk_fail', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$disk['name']
            ]);
        }
        $result['data']['name'] = $disk['name'];

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 快照列表
     * @desc 快照列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.list[].id - 快照ID
     * @return  string data.list[].name - 快照名称
     * @return  int data.list[].create_time - 创建时间
     * @return  string data.list[].notes - 备注
     * @return  int data.list[].status - 状态(0=创建中,1=创建完成)
     * @return  int data.count - 总条数
     */
    public function snapshotList($param)
    {
        $param['page'] = $param['page'] ?? 1;
        $param['per_page'] = $param['limit'] ?? config('idcsmart.limit');
        // $param['sort'] = $param['sort'] ?? config('idcsmart.sort');
        $param['type'] = 'snap';
        
        if($this->downstreamHostLogic->isDownstream()){
            $list = $this->downstreamHostLogic->snapshotList($param);

            $data = $list['data']['list'] ?? [];
            $count = $list['data']['count'] ?? 0;
        }else{
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, $param);

            $data = [];
            if(isset($res['data']['data'])){
                foreach($res['data']['data'] as $v){
                    $data[] = [
                        'id'=>$v['id'],
                        'name'=>$v['name'],
                        'create_time'=>strtotime($v['create_time']),
                        'notes'=>$v['remarks'],
                        'status'=>$v['status'],
                    ];
                }
            }
            $count = $res['data']['meta']['total'] ?? 0;
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'	=> $data,
                'count'	=> $count,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-11
     * @title 创建快照
     * @desc 创建快照
     * @author theworld
     * @version v1
     * @param   int param.name - 快照名称 require
     * @param   int param.disk_id - 磁盘ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function snapshotCreate($param)
    {
        // 验证磁盘
        $disk = DiskModel::find($param['disk_id']);
        if(empty($disk) || $disk['host_id'] != $this->hostModel['id']){
            return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
        }
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->snapshotCreate([
                'name'		=> $param['name'],
                'disk_id'	=> $disk['upstream_id'],
            ]);
        }else{
            // 获取磁盘列表
            // $res = $this->idcsmartCloud->cloudDetail($this->id);
            // if($res['status'] == 400){
            // 	return $res;
            // }
            // $diskId = array_column($res['data']['disk'], 'id');
            // if(!in_array($param['disk_id'] ?? 0, $diskId)){
            // 	return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
            // }
            $res = $this->idcsmartCloud->snapshotCreate($disk['rel_id'], ['type' => 'snap', 'name' => $param['name']]);
        }
        if($res['status'] == 200){
            // 创建成功
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_create_snapshot_success')
            ];

            $description = lang_plugins('log_host_start_create_snap_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$param['name']
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_create_snapshot_failed')
            ];

            $description = lang_plugins('log_host_start_create_snap_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$param['name']
            ]);
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 快照还原
     * @desc 快照还原
     * @author hh
     * @version v1
     * @param   int param.snapshot_id - 快照ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 快照名称
     */
    public function snapshotRestore($param)
    {
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->snapshotRestore($param);

            $snapshot = $res['data']['name'] ?? 'ID-'.(int)$param['snapshot_id'];
        }else{
            // 获取快照列表
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
            if($res['status'] == 400){
                return $res;
            }
            $res['data']['data'] = $res['data']['data'] ?? [];

            $snapshot = null;
            foreach($res['data']['data'] as $v){
                if($v['id'] == $param['snapshot_id']){
                    $snapshot = $v['remarks'];
                    if($v['status'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_snap_creating_wait_to_retry')];
                    }
                    break;
                }
            }
            if(is_null($snapshot)){
                return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
            }
            $res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['snapshot_id']);
        }
        if($res['status'] == 200){
            // 还原成功,更新密码,端口信息
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_snapshot_restore_success')
            ];

            if(isset($res['data']['os'])){
                // 传递
                $result['data']['os'] = $res['data']['os'];

                $hostAddition = [
                    'username'	=> $res['data']['os']['user'],
                    'password'	=> $res['data']['os']['password'],
                    'port'		=> $res['data']['os']['port'],
                ];

                if($this->downstreamHostLogic->isDownstream()){
                    $image = ImageModel::where('upstream_id', $res['data']['image_id'] ?? 0)->where('product_id', $this->hostModel['product_id'])->find();
                }else{
                    $image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
                }
                if(!empty($image)){
                    $result['data']['image_id'] = $image['id'];

                    $this->hostLinkModel->update(['image_id'=>$image['id']], ['id'=>$this->hostLinkModel['id']]);

                    $imageGroup = ImageGroupModel::find($image['image_group_id']);

                    $hostAddition['image_icon'] = $imageGroup['icon'] ?? '';
                    $hostAddition['image_name'] = $image['name'];
                }

                $HostAdditionModel = new HostAdditionModel();
                $HostAdditionModel->hostAdditionSave($this->hostModel['id'], $hostAddition);
            }

            $description = lang_plugins('log_host_start_snap_restore_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$snapshot
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_snapshot_restore_failed')
            ];

            $description = lang_plugins('log_host_start_snap_restore_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$snapshot
            ]);
        }
        $result['data']['name'] = $snapshot;

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 删除快照
     * @author hh
     * @version v1
     * @param   int id - 快照ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 快照名称
     */
    public function snapshotDelete($id)
    {
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->snapshotDelete([
                'snapshot_id'	=> $id,
            ]);

            $snapshot = $res['data']['name'] ?? 'ID-'.(int)$id;
        }else{
            // 获取快照列表
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
            if($res['status'] == 400){
                return $res;
            }
            $res['data']['data'] = $res['data']['data'] ?? [];

            $snapshot = null;
            foreach($res['data']['data'] as $v){
                if($v['id'] == $id){
                    $snapshot = $v['remarks'];
                    if($v['status'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_snap_creating_wait_to_retry')];
                    }
                    break;
                }
            }
            if(is_null($snapshot)){
                return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
            }
            // $snapshot = array_column($res['data']['data'] ?? [], 'remarks', 'id');
            // if(!isset($snapshot[$id])){
            // 	return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
            // }
            $res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
        }
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('delete_snapshot_success')
            ];

            $description = lang_plugins('log_host_delete_snap_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$snapshot
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('delete_snapshot_failed')
            ];

            $description = lang_plugins('log_host_delete_snap_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$snapshot
            ]);
        }
        $result['data']['name'] = $snapshot;

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 备份列表
     * @desc 备份列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.list[].id - 备份ID
     * @return  string data.list[].name - 备份名称
     * @return  int data.list[].create_time - 创建时间
     * @return  string data.list[].notes - 备注
     * @return  int data.list[].status - 状态(0=创建中,1=创建成功)
     * @return  int data.count - 总条数
     */
    public function backupList($param)
    {
        $param['page'] = $param['page'] ?? 1;
        $param['per_page'] = $param['limit'] ?? config('idcsmart.limit');
        $param['type'] = 'backup';

        if($this->downstreamHostLogic->isDownstream()){
            $list = $this->downstreamHostLogic->backupList($param); 

            $data = $list['data']['list'] ?? [];
            $count = $list['data']['count'] ?? 0;
        }else{
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, $param);

            $data = [];
            $count = $res['data']['meta']['total'] ?? 0;

            if(isset($res['data']['data'])){
                foreach($res['data']['data'] as $v){
                    $data[] = [
                        'id'=>$v['id'],
                        'name'=>$v['name'],
                        'create_time'=>strtotime($v['create_time']),
                        'notes'=>$v['remarks'],
                        'status'=>$v['status'],
                    ];
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'	=> $data,
                'count'	=> $count,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-11
     * @title 创建备份
     * @desc 创建备份
     * @author theworld
     * @version v1
     * @param   int param.name - 备份名称 require
     * @param   int param.disk_id - 磁盘ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function backupCreate($param)
    {
        $disk = DiskModel::find($param['disk_id']);
        if(empty($disk) || $disk['host_id'] != $this->hostModel['id']){
            return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
        }
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->backupCreate([
                'name'		=> $param['name'],
                'disk_id'	=> $disk['upstream_id'],
            ]);
        }else{
            // 获取磁盘列表
            // $res = $this->idcsmartCloud->cloudDetail($this->id);
            // if($res['status'] == 400){
            // 	return $res;
            // }
            // $disk = array_column($res['data']['disk'], 'name', 'id');
            // if(!isset($disk[$param['disk_id']])){
            // 	return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
            // }
            $res = $this->idcsmartCloud->snapshotCreate($disk['rel_id'], ['type' => 'backup', 'name' => $param['name']]);
        }
        if($res['status'] == 200){
            // 创建成功
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_create_backup_success')
            ];

            $description = lang_plugins('log_host_start_create_backup_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$param['name'] ?? '',
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_create_backup_failed')
            ];

            $description = lang_plugins('log_host_start_create_backup_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$param['name'] ?? '',
            ]);
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 备份还原
     * @desc 备份还原
     * @author hh
     * @version v1
     * @param   int param.backup_id - 备份ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 备份名称
     */
    public function backupRestore($param)
    {
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->backupRestore($param);

            $backup = $res['data']['name'] ?? 'ID-' . (int)$param['backup_id'];
        }else{
            // 获取备份列表
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
            if($res['status'] == 400){
                return $res;
            }
            $res['data']['data'] = $res['data']['data'] ?? [];

            $backup = null;
            foreach($res['data']['data'] as $v){
                if($v['id'] == $param['backup_id']){
                    $backup = $v['remarks'];
                    if($v['status'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_backup_creating_wait_to_retry')];
                    }
                    break;
                }
            }
            if(is_null($backup)){
                return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
            }
            // $backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
            // if(!isset($backup[$param['backup_id']])){
            // 	return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
            // }
            $res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['backup_id']);
        }
        if($res['status'] == 200){
            // 还原成功,更新密码,端口信息
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('start_backup_restore_success')
            ];

            if(isset($res['data']['os'])){
                // os传递下去
                $result['data']['os'] = $res['data']['os'];

                $hostAddition = [
                    'username'	=> $res['data']['os']['user'],
                    'password'	=> $res['data']['os']['password'],
                    'port'		=> $res['data']['os']['port'],
                ];

                if($this->downstreamHostLogic->isDownstream()){
                    $image = ImageModel::where('upstream_id', $res['data']['image_id'] ?? 0)->where('product_id', $this->hostModel['product_id'])->find();
                }else{
                    $image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
                }
                if(!empty($image)){
                    // 还原镜像ID传递
                    $result['data']['image_id'] = $image['id'];

                    $this->hostLinkModel->update(['image_id'=>$image['id']], ['id'=>$this->hostLinkModel['id']]);

                    $imageGroup = ImageGroupModel::find($image['image_group_id']);

                    $hostAddition['image_icon'] = $imageGroup['icon'] ?? '';
                    $hostAddition['image_name'] = $image['name'];
                }

                $HostAdditionModel = new HostAdditionModel();
                $HostAdditionModel->hostAdditionSave($this->hostModel['id'], $hostAddition);
            }

            $description = lang_plugins('log_host_start_backup_restore_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$backup
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('start_backup_restore_failed')
            ];

            $description = lang_plugins('log_host_start_backup_restore_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$backup
            ]);
        }
        $result['data']['name'] = $backup;

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 删除备份
     * @author hh
     * @version v1
     * @param   int id - 备份ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.name - 备份名称
     */
    public function backupDelete($id)
    {
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->backupDelete([
                'backup_id'	=> $id,
            ]);

            $backup = $res['data']['name'] ?? 'ID-' . (int)$id;
        }else{
            // 获取备份列表
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
            if($res['status'] == 400){
                return $res;
            }
            $res['data']['data'] = $res['data']['data'] ?? [];

            $backup = null;
            foreach($res['data']['data'] as $v){
                if($v['id'] == $id){
                    $backup = $v['remarks'];
                    if($v['status'] == 0){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_backup_creating_wait_to_retry')];
                    }
                    break;
                }
            }
            if(is_null($backup)){
                return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
            }
            // $backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
            // if(!isset($backup[$id])){
            // 	return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
            // }
            $res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
        }
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('delete_backup_success')
            ];

            $description = lang_plugins('log_host_delete_backup_success', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$backup
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('delete_backup_failed')
            ];

            $description = lang_plugins('log_host_delete_backup_failed', [
                '{hostname}'=>$this->hostModel['name'],
                '{name}'=>$backup
            ]);
        }
        $result['data']['name'] = $backup;

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 获取魔方云真实详情
     * @desc 获取魔方云真实详情
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
     * @return  float data.system_disk_real_size - 系统盘实际占用大小(单位:G)
     * @return  string data.panel_pass - 面板管理密码
     * @return  string data.vpc_private_ip - VPC内网IP
     */
    public function detail()
    {
        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->remoteInfo(['id' => $manual_resource['id']]);
            }
            if(isset($res) && $res['status']==200){
                $result = $res;
            }else{
                $hostAddition = HostAdditionModel::where('host_id', $this->hostModel['id'])->find();

                $data = [
                    'rescue'	=> 0,
                    'username'	=> $hostAddition['username'] ?? '',
                    'password'	=> $hostAddition['password'] ?? '',
                    'port'		=> $hostAddition['port'] ?? 0,
                    'ip_num'	=> 1,
                    'simulate_physical_machine' => 0,
                    'system_disk_real_size' => 0.00,
                    'panel_pass' => '',
                    'vpc_private_ip' => '',
                ];

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('success_message'),
                    'data'   => $data
                ];
            }
            if(isset($result['data']['vpc_private_ip'])){
                $this->hostLinkModel->where('id', $this->hostLinkModel['id'])->update([
                    'vpc_private_ip' => $result['data']['vpc_private_ip'],
                ]);
            }
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                // 上游信息,只使用部分
                $remoteInfo = $this->downstreamHostLogic->remoteInfo();

                $hostAddition = HostAdditionModel::where('host_id', $this->hostModel['id'])->find();

                $data = [
                    'rescue'	=> $remoteInfo['data']['rescue'] ?? 0,
                    'username'	=> $hostAddition['username'] ?? $remoteInfo['data']['username'],
                    'password'	=> $hostAddition['password'] ?? $remoteInfo['data']['password'],
                    'port'		=> $hostAddition['port'] ?? $remoteInfo['data']['port'],
                    'ip_num'	=> $remoteInfo['data']['ip_num'] ?? 1,
                    'simulate_physical_machine' => $remoteInfo['data']['simulate_physical_machine'] ?? 0,
                    'system_disk_real_size' => $remoteInfo['data']['system_disk_real_size'] ?? 0.00,
                    'panel_pass'=> $remoteInfo['data']['panel_pass'] ?? '',
                    'vpc_private_ip' => $remoteInfo['data']['vpc_private_ip'] ?? '',
                ];

                $this->hostLinkModel->where('host_id', $this->hostModel['id'])->update([
                    'vpc_private_ip'    => $data['vpc_private_ip'],
                ]);
            }else{
                $detail = $this->idcsmartCloud->cloudDetail($this->id);

                // 获取下当前镜像
                // $ImageModel = ImageModel::find($this->hostLinkModel['image_id']);
                // $info = $ImageModel->getDefaultUserInfo();

                $hostAddition = HostAdditionModel::where('host_id', $this->hostModel['id'])->find();

                $data = [
                    'rescue'	=> 0,
                    'username'	=> $hostAddition['username'] ?? '',
                    'password'	=> $hostAddition['password'] ?? '',
                    'port'		=> $hostAddition['port'] ?? 0,
                    'ip_num'	=> 1,
                    'simulate_physical_machine' => 0,
                    'system_disk_real_size' => 0.00,
                    'panel_pass'=> $detail['data']['panel_pass'] ?? '',
                    'vpc_private_ip' => '',
                ];
                $data['port'] = $data['port'] > 0 ? $data['port'] : 0;
                if(isset($detail['data'])){
                    $update = [];
                    if(!empty($this->hostLinkModel['ssh_key_id']) && (!isset($detail['data']['ssh_key']['id']) || empty($detail['data']['ssh_key']['id']))){
                        $update['ssh_key_id'] = 0;
                    }

                    $data['rescue'] = $detail['data']['rescue'];
                    // $data['username'] = $detail['data']['osuser'];
                    // $data['password'] = $detail['data']['rootpassword'];
                    if($data['port'] == 0){
                        $data['port'] = $detail['data']['port'] > 0 ? $detail['data']['port'] : ($detail['data']['image_group_id'] == 1 ? 3389 : 22);
                    }
                    // $data['port'] = $detail['data']['port'] > 0 ? $detail['data']['port'] : ($detail['data']['image_group_id'] == 1 ? 3389 : 22);
                    $data['ip_num'] = $detail['data']['ip_num'];
                    $data['simulate_physical_machine'] = $detail['data']['simulate_physical_machine'];
                    $data['system_disk_real_size'] = round(($detail['data']['disk'][0]['real_size'] ?? 0)/1024/1024/1024, 2);
                    
                    if(!empty($detail['data']['network_type']) && $detail['data']['network_type'] == 'vpc'){
                        if(!empty($detail['data']['network'][0]['ipaddress'][0]['ipaddress'])){
                            $data['vpc_private_ip'] = $detail['data']['network'][0]['ipaddress'][0]['ipaddress'];
                        }
                    }

                    if(!empty($update)){
                        HostLinkModel::update($update, ['host_id'=>$this->hostModel['id']]);
                    }
                    // 同步IP
                    $this->hostLinkModel->syncIp(['host_id'=>$this->hostModel['id'], 'id'=>$this->id], $this->idcsmartCloud, $detail, false);
                }
            }
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
                'data'   => $data
            ];
        }
        return $result;
    }

    /**
     * 时间 2022-06-27
     * @title 获取IP列表
     * @desc 获取IP列表
     * @author hh
     * @version v1
     * @param int param.page 1 页数
     * @param int param.limit 20 每页条数
     * @return string list[].ip - IP地址
     * @return string list[].subnet_mask - 掩码
     * @return string list[].gateway - 网关
     * @return int count - 总条数
     */
    public function ipList($param)
    {
        $param['page'] = $param['page']>0 ? $param['page'] : 1;
        $param['limit'] = $param['limit']>0 ? $param['limit'] : 20;

        $data = [];
        $count = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $ipList = $this->downstreamHostLogic->ipList($param);

            $data = $ipList['data']['list'] ?? [];
            $count = $ipList['data']['count'] ?: 0;
        }else{
            if($this->id > 0){
                // 获取当前所有IP
                $bw = $this->idcsmartCloud->bwList(['per_page'=>999, 'cloud'=>$this->id, 'include_multi_interface'=>1]);
                if(isset($bw['data']['data'])){
                    foreach($bw['data']['data'] as $v){
                        foreach($v['ip'] as $vv){
                            $data[] = [
                                'ip' => $vv['ip'],
                                'subnet_mask'=>$vv['subnet_mask'],
                                'gateway'=>$vv['gateway'],
                            ];
                        }
                    }
                }
                
                $count = count($data);
                $data  = array_slice($data, ($param['page']-1)*$param['limit'], $param['limit']);
            }
        }
        return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2022-09-25
     * @title 计算磁盘价格
     * @desc 计算磁盘价格
     * @author hh
     * @version v1
     * @param   array param.remove_disk_id - 要取消订购的磁盘ID
     * @param   array param.add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
     * @param   int param.add_disk[].size - 磁盘大小
     * @param   string param.add_disk[].type - 磁盘类型
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  string renew_price - 续费价格
     */
    public function calDiskPrice(&$param): array
    {
        // 套餐不能单独购买磁盘
        if(!empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        $productId = $this->hostModel['product_id'];
        $diffTime = $this->hostModel['due_time'] - time();
        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 看下当前周期还存在不
        if($isOnDemand){
            $duration = [
                'id'	=> 'on_demand',
            ];
        }else{
            $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
            }
        }

        // 匹配下之前的周期
        $price = 0;
        $oldPrice = 0;
        $add_size = [];
        $del_size = [];
        // $diskNum = 0;
        $diskNum = DiskModel::where('host_id', $this->hostModel['id'])->where('type2', 'data')->count();
        
        // 获取产品等级优惠配置
        $config = ConfigModel::where('product_id', $productId)->find();

        $OptionModel = new OptionModel();
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0; 		// 实际优惠价格
        $renewDiscountPrice = 0; // 续费可以优惠的总金额
        $orderItem = [];	// 要添加的用户等级子项
        // 有要取消的磁盘
        if(isset($param['remove_disk_id']) && !empty(array_filter($param['remove_disk_id']))){
            $removeDisk = DiskModel::where('host_id', $this->hostModel['id'])
                        ->whereIn('id', $param['remove_disk_id'])
                        ->where('type2', 'data')
                        ->where('is_free', 0)
                        ->select()
                        ->toArray();
            if(count($removeDisk) != count($param['remove_disk_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
            }
            foreach($removeDisk as $v){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $v['type'] ?? '');
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_data_disk', ['{data_disk}'=>$v['type'].$v['size']])];
                }
                
                // 应用数据盘等级优惠（取消时需要减去原价格）
                $diskOldPrice = $optionDurationPrice['price'] ?? 0;
                
                $oldPrice = bcadd($oldPrice, $diskOldPrice, 4);

                // 累计用户等级折扣金额（非按需计费）
                if(!empty($config) && $config['level_discount_data_disk_upgrade'] == 1){
                    $discountPrice = bcadd($discountPrice, -$diskOldPrice, 4);
                }

                // 累计续费用户等级折扣金额
                if(!empty($config['level_discount_data_disk_renew']) && $config['level_discount_data_disk_renew'] == 1){
                    $renewDiscountPrice = bcadd($renewDiscountPrice, -$diskOldPrice, 4);
                }

                $del_size[] = $v['size'];
            }
            $diskNum -= count($removeDisk);
        }
        // 新购磁盘
        if(isset($param['add_disk']) && !empty(array_filter($param['add_disk']))){
            $param['add_disk'] = array_filter($param['add_disk']);

            $ConfigModel = new ConfigModel();
            $dataDiskLimit = $ConfigModel->getDataDiskLimitNum($productId);

            if($diskNum + count($param['add_disk']) > $dataDiskLimit){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_over_max_disk_num', ['{num}'=>$dataDiskLimit])];
            }

            foreach($param['add_disk'] as $k=>$v){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $v['type'] ?? '');
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_data_disk', ['{data_disk}'=>$v['type'].$v['size']])];
                }
                
                // 应用数据盘等级优惠
                $diskNewPrice = $optionDurationPrice['price'] ?? 0;
                
                // 累计用户等级折扣金额（非按需计费）
                if(!empty($config) && $config['level_discount_data_disk_upgrade'] == 1){
                    $discountPrice = bcadd($discountPrice, $diskNewPrice, 4);
                }

                // 累计续费用户等级折扣金额
                if(!empty($config['level_discount_data_disk_renew']) && $config['level_discount_data_disk_renew'] == 1){
                    $renewDiscountPrice = bcadd($renewDiscountPrice, $diskNewPrice, 4);
                }
                
                $param['add_disk'][$k]['price'] = $diskNewPrice;
                $param['add_disk'][$k]['store_id'] = $optionDurationPrice['option']['other_config']['store_id'] ?? 0;

                $price = bcadd($price, $diskNewPrice, 4);
                   $add_size[] = $v['size'];
            }
        }
        if(!empty($add_size) && !empty($del_size) ){
            $description = lang_plugins('upgrade_buy_and_cancel_data_disk', [
                '{del}'=>implode(',', $del_size),
                '{add}'=>implode(',', $add_size),
            ]);
        }else if(!empty($add_size)){
            $description = lang_plugins('upgrade_buy_data_disk', [
                '{add}'=>implode(',', $add_size)
            ]);
        }else if(!empty($del_size)){
            $description = lang_plugins('upgrade_cancel_data_disk', [
                '{del}'=>implode(',', $del_size)
            ]);
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        // 按需
        if($isOnDemand){
            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice, 4);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            $basePrice = 0;
            $price = 0;
            $priceDifference = 0;
            $renewPriceClientLevelDiscount = '0.0000';
            
            $productOnDemand = ProductOnDemandModel::getProductOnDemand($productId);
            if(in_array('data_disk', $productOnDemand['keep_time_billing_item'])){
                $keepTimePriceDifference = $upgradePriceDifference;
            }

            // 统一计算续费折扣，使用续费差价和续费折扣配置
            $renewShowPrice = $this->calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice);
            $renewPrice = $renewShowPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $renewShowPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $renewShowPrice['on_demand_renew_price_difference_client_level_discount'];

            // 添加一条用户等级折扣防止系统内再次计算
            // $clientLevel = $this->getClientLevel([
            //     'product_id'    => $productId,
            //     'client_id'     => get_client_id(),
            // ]);
            // if(!empty($clientLevel)){
            //     $orderItem[] = [
            //         'type'          => 'addon_idcsmart_client_level',
            //         'rel_id'        => $clientLevel['id'],
            //         'amount'        => -0.0,
            //         'description'   => lang_plugins('mf_cloud_client_level', [
            //             '{name}'    => $clientLevel['name'],
            //             '{value}'   => $clientLevel['discount_percent'],
            //         ]),
            //     ];
            // }
        }else{
            $price = bcmul($price, $duration['price_factor'], 4);
            $oldPrice = bcmul($oldPrice, $duration['price_factor'], 4);

            // 升级差价（用于计算升级价格，应用升级折扣）
            $upgradePriceDifference = bcsub($price, $oldPrice, 2);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            if($this->hostModel['billing_cycle_time']>0){
                $price = $upgradePriceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $upgradePriceDifference;
            }

            // base_price 使用续费差价
            $basePrice = bcadd($this->hostModel['base_price'], $renewPriceDifference, 2);
            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            // if($isDownstream){
            // 	$DurationModel = new DurationModel();
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$priceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $priceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }

            // 计算用户等级折扣（仅非按需计费）
            $clientLevel = $this->getClientLevel([
                'product_id'    => $productId,
                'client_id'     => get_client_id(),
            ]);
            if (!$priceAgent){
                $clientLevel = [];
            }
            if(!empty($clientLevel)){
                // 应用价格因子
                if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
                    $discountPrice = bcmul($discountPrice, $duration['price_factor'], 4);
                    $renewDiscountPrice = bcmul($renewDiscountPrice, $duration['price_factor'], 4);
                }
                
                // 按时间比例计算升级折扣价格
                if($this->hostModel['billing_cycle_time']>0){
                    $discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
                }
                
                // 计算实际升级/续费折扣金额
                $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                $renewDiscount = bcdiv($renewDiscountPrice*$clientLevel['discount_percent'], 100, 2);
                
                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => min(-$discount, 0),
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        // '{host_id}' => $this->hostModel['id'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
                
                // 升级价格应用升级折扣
                $price = bcsub($price, $discount);
                
                // 下游的 base_price 需要应用折扣
                if (isset($param['is_downstream']) && $param['is_downstream']==1){
                    $basePrice = bcsub($basePrice, $renewDiscount, 2);
                }
            }

            // price_difference 使用升降级差价
            $priceDifference = $price;

            if($this->hostModel['billing_cycle'] == 'free'){
                $price = '0.00';
                $priceDifference = 0;
            }else{
                $price = max(0, $price);
            }
        
            // 计算续费价格,根据配置决定是否应用用户等级折扣
            $renewPriceDifferenceClientLevelDiscount = 0;
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            if(!empty($clientLevel) && !$isDownstream){
                // 计算续费折扣
                $renewPriceDifferenceClientLevelDiscount = bcdiv($renewDiscountPrice * $clientLevel['discount_percent'], 100, 2);
                
                // 续费差价 = 原始续费差价 - 续费折扣
                // $renewPriceDifference = bcsub($renewPriceDifference, $renewPriceClientLevelDiscount, 2);
            }
            
            $renewPrice = bcadd($this->hostModel['renew_amount'], bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 2), 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }
        $price = amount_format($price);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $renewPriceDifference,
                'base_price' => $basePrice,
                'renew_price' => $renewPrice,
                'keep_time_price_difference' => $keepTimePriceDifference ?? -1,
                'renew_price_client_level_discount' => $renewPriceClientLevelDiscount ?? '0.0000',
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.0000',
                'discount'					=> max($discount ?? 0, 0),
                'order_item'				=> $orderItem,
                'renew_discount_price_difference' => $renewDiscountPrice ?? '0.0000',
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成订购磁盘订单
     * @desc 生成订购磁盘订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   array param.remove_disk_id - 要取消订购的磁盘ID
     * @param   array param.add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
     * @param   int param.add_disk[].size - 磁盘大小
     * @param   string param.add_disk[].type - 磁盘类型
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBuyDiskOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $this->hostModel['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_buy_disk'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $res = $this->calDiskPrice($param);
        if($res['status'] == 400){
            return $res;
        }
        // $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
        // if(isset($param['add_disk']) && !empty($param['add_disk'])){
        // 	// 仅验证要新增的数据盘
        // 	$currentConfig['data_disk'] = $param['add_disk'];

        // 	$LimitRuleModel = new LimitRuleModel();
           //  $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig);
           //  if($checkLimitRule['status'] == 400){
           //  	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_buy_disk_for_limit_rule')];
           //  }
        // }

        $data = [
            'host_id'     => $this->hostModel['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'buy_disk',
                'remove_disk_id' => array_filter($param['remove_disk_id'] ?? []),
                'add_disk' => array_filter($param['add_disk'] ?? []),
            ],
            'customfield' => $param['customfield'] ?? [],
            'keep_time_price_difference' => $res['data']['keep_time_price_difference'],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
            'renew_price_difference_client_level_discount'	=> $res['data']['renew_price_difference_client_level_discount'],
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-09-25
     * @title 计算磁盘扩容价格
     * @desc 计算磁盘扩容价格
     * @author hh
     * @version v1
     * @param   int param.resize_data_disk[].id - 磁盘ID
     * @param   int param.resize_data_disk[].size - 磁盘大小
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  int resize_disk[].id - 变更磁盘ID
     * @return  int resize_disk[].size - 磁盘大小
     * @return  string resize_disk[].price - 价格
     */
    public function calResizeDiskPrice($param)
    {
        // 套餐不能单独购买磁盘
        if(!empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        $productId = $this->hostModel['product_id'];
        $hostId    = $this->hostModel['id'];
        $diffTime = $this->hostModel['due_time'] - time();
        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';
        $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        if($isOnDemand){
            $duration = [
                'id'	=> 'on_demand',
            ];
        }else{
            $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
            }
        }

        $price = 0;
        $oldPrice = 0;
        $description = '';

        $resizeDisk = [];
        $OptionModel = new OptionModel();
        
        // 获取产品等级优惠配置
        $config = ConfigModel::where('product_id', $productId)->find();
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0; 		// 实际优惠价格
        $renewDiscountPrice = 0; // 可以优惠的续费金额
        $orderItem = [];	// 要添加的用户等级子项
        foreach($param['resize_data_disk'] as $k=>$v){
            if(!isset($v['id']) || !isset($v['size'])){
                return ['status'=>400, 'msg'=>lang_plugins('param_error')];
            }
            $disk = DiskModel::where('host_id', $hostId)->where('id', $v['id'])->find();
            if(empty($disk) || $disk['type2'] != 'data'){
                return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
            }
            // 免费盘不能扩容
            // if($disk['is_free'] == 1){
            // 	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_free_disk_cannot_resize') ];
            // }
            // 大小没改
            if($v['size'] == $disk['size']){
                continue;
            }
            if($v['size'] < $disk['size']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_data_disk_cannot_down_size')];
            }

            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $disk['type'], $disk['is_free'] == 1 ? $disk['free_size'] : NULL);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_data_disk', ['{data_disk}'=>$v['size']])];
            }

            $resizeDisk[] = [
                'id' 	=> $v['id'],
                'size' 	=> $v['size'],
                'price' => $optionDurationPrice['price'] ?? 0
            ];
            
            $description .= lang_plugins('upgrade_data_disk_size', [
                '{name}'	=> $disk['name'],
                '{old}'		=> $disk['size'],
                '{new}'		=> $v['size']
            ]);

            // 获取当前配置价格
            $diskOldPrice = 0;
            if($disk['is_free'] == 1 && $disk['size'] == $disk['free_size'] ){
                
            }else{
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $disk['size'], $duration['id'], $disk['type'], $disk['is_free'] == 1 ? $disk['free_size'] : NULL);
                if($currentOptionDurationPrice['match']){
                    $diskOldPrice = $currentOptionDurationPrice['price'];
                    $oldPrice = bcadd($oldPrice, $diskOldPrice, 4);
                }
            }
            $diskNewPrice = $optionDurationPrice['price'] ?? 0;
            $diskDiffPrice = bcsub($diskNewPrice, $diskOldPrice, 4);

            $price = bcadd($price, $diskNewPrice, 4);
            
            // 累计用户等级折扣金额（非按需计费）
            if(!empty($config) && $config['level_discount_data_disk_upgrade'] == 1){
                $discountPrice = bcadd($discountPrice, $diskDiffPrice, 4);
            }

            // 累计续费用户等级折扣金额（非按需计费
            if(!empty($config) && $config['level_discount_data_disk_renew'] == 1){
                $renewDiscountPrice = bcadd($renewDiscountPrice, $diskDiffPrice, 4);
            }
        }
        if(empty($resizeDisk)){
            return ['status'=>400, 'msg'=>lang_plugins('disk_not_resize')];
        }

        // 按需
        if($isOnDemand){
            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice, 4);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            $basePrice = 0;
            $price = 0;
            $priceDifference = 0;
            $renewPriceClientLevelDiscount = '0.0000';

            $productOnDemand = ProductOnDemandModel::getProductOnDemand($productId);
            if(in_array('data_disk', $productOnDemand['keep_time_billing_item'])){
                $keepTimePriceDifference = $upgradePriceDifference;
            }

            // 统一计算续费折扣，使用续费差价和续费折扣配置
            $renewShowPrice = $this->calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice);
            $renewPrice = $renewShowPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $renewShowPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $renewShowPrice['on_demand_renew_price_difference_client_level_discount'];
        }else{
            $oldPrice = bcmul($oldPrice, $duration['price_factor'], 4);
            $price = bcmul($price, $duration['price_factor'], 4);

            // 升级差价（用于计算升级价格，应用升级折扣）
            $upgradePriceDifference = bcsub($price, $oldPrice, 2);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            if($this->hostModel['billing_cycle_time']>0){
                $price = $upgradePriceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $upgradePriceDifference;
            }

            // base_price 使用续费差价
            $basePrice = bcadd($this->hostModel['base_price'], $renewPriceDifference, 2);
            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            // if($isDownstream){
            // 	$DurationModel = new DurationModel();
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$priceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $priceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }

            // 计算用户等级折扣（仅非按需计费）
            $clientLevel = $this->getClientLevel([
                'product_id'    => $productId,
                'client_id'     => get_client_id(),
            ]);
            if (!$priceAgent){
                $clientLevel = [];
            }
            if(!empty($clientLevel)){
                // 应用价格因子
                if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
                    $discountPrice = bcmul($discountPrice, $duration['price_factor'], 4);
                    $renewDiscountPrice = bcmul($renewDiscountPrice, $duration['price_factor'], 4);
                }
                
                // 按时间比例计算升级折扣价格
                if($this->hostModel['billing_cycle_time']>0){
                    $discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
                }
                
                // 计算实际升级/续费折扣金额
                $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                $renewDiscount = bcdiv($renewDiscountPrice*$clientLevel['discount_percent'], 100, 2);
                
                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => min(-$discount, 0),
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        // '{host_id}' => $this->hostModel['id'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
                
                // 升级价格应用升级折扣
                $price = bcsub($price, $discount);
                
                // 下游的 base_price 需要应用折扣
                if (isset($param['is_downstream']) && $param['is_downstream']==1){
                    $basePrice = bcsub($basePrice, $renewDiscount, 2);
                }
            }

            // price_difference 使用升降级差价
            $priceDifference = $price;

            $price = max(0, $price);

            // 计算续费价格,根据配置决定是否应用用户等级折扣
            $renewPriceDifferenceClientLevelDiscount = 0;
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            if(!empty($clientLevel) && !$isDownstream){
                // 计算续费折扣
                $renewPriceDifferenceClientLevelDiscount = bcdiv($renewDiscountPrice * $clientLevel['discount_percent'], 100, 2);
                
                // 续费差价 = 原始续费差价 - 续费折扣
                // $renewPriceDifference = bcsub($renewPriceDifference, $renewPriceClientLevelDiscount, 2);
            }
            
            $renewPrice = bcadd($this->hostModel['renew_amount'], bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 2), 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }
        $price = amount_format($price);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $renewPriceDifference,
                'base_price' => $basePrice,
                'renew_price' => $renewPrice,
                'keep_time_price_difference' => $keepTimePriceDifference ?? -1,
                'resize_disk' => $resizeDisk,
                'renew_price_client_level_discount' => $renewPriceClientLevelDiscount ?? '0.0000',
                'discount'					=> max($discount ?? 0, 0),
                'order_item'				=> $orderItem,
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.0000', // 续费用户等级差价
                'renew_discount_price_difference' => $renewDiscountPrice ?? '0.0000',
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成磁盘扩容订单
     * @desc 生成磁盘扩容订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.resize_data_disk[].id - 魔方云磁盘ID
     * @param   int param.resize_data_disk[].size - 磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createResizeDiskOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $this->hostModel['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_resize_disk'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $res = $this->calResizeDiskPrice($param);
        if($res['status'] == 400){
            return $res;
        }
        // $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
        // if(isset($param['resize_data_disk']) && !empty($param['resize_data_disk'])){
        // 	// 仅验证要扩容后数据盘
        // 	$currentConfig['data_disk'] = $param['resize_data_disk'];

        // 	$LimitRuleModel = new LimitRuleModel();
           //  $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig);
           //  if($checkLimitRule['status'] == 400){
           //  	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_buy_disk_for_limit_rule')];
           //  }
        // }

        $data = [
            'host_id'     => $this->hostModel['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'resize_disk',
                'resize_disk' => $res['data']['resize_disk'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'keep_time_price_difference' => $res['data']['keep_time_price_difference'],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
            'renew_price_difference_client_level_discount' => $res['data']['renew_price_difference_client_level_discount'],
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-09-25
     * @title 计算IP数量价格
     * @desc 计算IP数量价格
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.ip_num - 附加IP数量 require
     * @param   int param.ipv6_num - 附加IPv6数量
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  int data.ip_data.value - 附加IP数量
     * @return  string data.ip_data.price - 价格
     */
    public function calIpNumPrice($param)
    {
        if(!empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        $productId = $this->hostModel['product_id'];
        $hostId    = $this->hostModel['id'];
        $diffTime  = $this->hostModel['due_time'] - time();
        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';
        $description = []; // 升降级详情

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        if($isOnDemand){
            $duration = [
                'id'	=> 'on_demand',
            ];
        }else{
            $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
            }
        }
        // 检查之前的线路是否还存在
        $line = LineModel::where('id', $configData['line']['id'])->find();
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_not_found_to_upgrade_ip_num')];
        }
        $OptionModel = new OptionModel();
        
        // 获取产品等级优惠配置
        $config = ConfigModel::where('product_id', $productId)->find();
        
        $oldPrice = '0';
        $price = '0';
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0; 		// 实际优惠价格
        $orderItem = [];	// 要添加的用户等级子项
        
        // IPv4 和 IPv6 的原始差价（用于续费折扣计算）
        $ipv4OriPriceDiff = '0.0000';
        $ipv6OriPriceDiff = '0.0000';
        $renewDiscountPrice = 0; // 可以续费折扣的总金额

        $newConfigData = [];

        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp([
            'host_id'	=> $hostId,
        ]);
        $enableIpv4 = [];
        $enableIpv6 = [];
        if(!empty($hostIp['dedicate_ip'])){
            if(filter_var($hostIp['dedicate_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                $enableIpv4[] = $hostIp['dedicate_ip'];
            }else if(filter_var($hostIp['dedicate_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
                $enableIpv6[] = $hostIp['dedicate_ip'];
            }
        }
        $hostIp['assign_ip'] = explode(',', $hostIp['assign_ip']);
        foreach($hostIp['assign_ip'] as $ip){
            if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                $enableIpv4[] = $ip;
            }
            if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
                $enableIpv6[] = $ip;
            }
        }
        $productOnDemand = ProductOnDemandModel::getProductOnDemand($productId);
        if($line['ip_enable'] == 1){
            // IP变动才计算
            $oldIpNum = $configData['ip']['value'] ?? 0;
            if(isset($param['ip_num']) && $oldIpNum != $param['ip_num']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ip_num_error') ];
                }
                $ipData = [
                    'value' => $param['ip_num'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                // 匹配当前价格
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $oldIpNum, $duration['id']);

                // 应用IPv4等级优惠
                $ipv4OldPrice = $currentOptionDurationPrice['price'] ?? '0';
                $ipv4NewPrice = $optionDurationPrice['price'];
                
                // 保存 IPv4 原始差价（用于续费折扣）
                $ipv4OriPriceDiff = bcsub($ipv4NewPrice, $ipv4OldPrice, 4);
                
                // 累计用户等级折扣金额（非按需计费）
                if(!empty($config) && $config['level_discount_ipv4_upgrade'] == 1){
                    $discountPrice = bcadd($discountPrice, $ipv4OriPriceDiff, 4);
                }
                
                // 累计续费折扣金额
                if(!empty($config) && $config['level_discount_ipv4_renew'] == 1){
                    $renewDiscountPrice = bcadd($renewDiscountPrice, $ipv4OriPriceDiff, 4);
                }

                $oldPrice = bcadd($oldPrice, $ipv4OldPrice, 4);
                $price = bcadd($price, $ipv4NewPrice, 4);

                if(in_array('ip_num', $productOnDemand['keep_time_billing_item'])){
                    $keepTimePriceDifference = bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? '0', 4);
                }

                $description[] = lang_plugins('mf_cloud_upgrade_ip_num', [
                    '{old}' => $oldIpNum,
                    '{new}' => $param['ip_num'],
                ]);

                // 当降级时,可以指定移除的IP地址
                if($oldIpNum > $param['ip_num']){
                    if(!empty($param['ip'])){
                        if(count($param['ip']) != $oldIpNum - $param['ip_num']){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_select_ip_downgrade_not_enough') ];
                        }
                        $removeIpv4 = array_values(array_intersect($enableIpv4, $param['ip']));
                        if(count($removeIpv4) != count($param['ip'])){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_select_ip_downgrade_not_enough') ];
                        }
                    }
                }
            }
        }
        // 仅当经典网络可以操作IPv6
        if($line['ipv6_enable'] == 1 && $configData['network_type'] == 'normal'){
            $oldIpv6Num = $configData['ipv6_num'] ?? 0;
            if(isset($param['ipv6_num']) && $oldIpv6Num != $param['ipv6_num']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $param['ipv6_num'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ipv6_num_not_found') ];
                }
                $ipv6Data = [
                    'value' => $param['ipv6_num'],
                ];

                // 匹配当前价格
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $oldIpv6Num, $duration['id']);

                // 应用IPv6等级优惠
                $ipv6OldPrice = $currentOptionDurationPrice['price'] ?? '0';
                $ipv6NewPrice = $optionDurationPrice['price'] ?? '0';
                
                // 保存 IPv6 原始差价（用于续费折扣）
                $ipv6OriPriceDiff = bcsub($ipv6NewPrice, $ipv6OldPrice, 4);
                
                // 累计用户等级折扣金额（非按需计费）
                if(!empty($config) && $config['level_discount_ipv6_upgrade'] == 1){
                    $discountPrice = bcadd($discountPrice, $ipv6OriPriceDiff, 4);
                }
                
                // 累计续费折扣金额
                if(!empty($config) && $config['level_discount_ipv6_renew'] == 1){
                    $renewDiscountPrice = bcadd($renewDiscountPrice, $ipv6OriPriceDiff, 4);
                }

                $oldPrice = bcadd($oldPrice, $ipv6OldPrice, 4);
                $price = bcadd($price, $ipv6NewPrice, 4);

                if(in_array('ipv6_num', $productOnDemand['keep_time_billing_item'])){
                    $keepTimePriceDifference = bcadd($keepTimePriceDifference ?? '0', bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? '0', 4), 4);
                }

                $description[] = lang_plugins('mf_cloud_upgrade_ipv6_num', [
                    '{old}' => $oldIpv6Num,
                    '{new}' => $param['ipv6_num'],
                ]);

                // 当降级时,可以指定移除的IP地址
                if($oldIpv6Num > $param['ipv6_num']){
                    if(!empty($param['ipv6'])){
                        if(count($param['ipv6']) != $oldIpv6Num - $param['ipv6_num']){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_select_ipv6_downgrade_not_enough') ];
                        }
                        $removeIpv6 = array_values(array_intersect($enableIpv6, $param['ipv6']));
                        if(count($removeIpv6) != count($param['ipv6'])){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_select_ipv6_downgrade_not_enough') ];
                        }
                    }
                }
            }
        }
        // 没有变更
        if(empty($description)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ip_num_not_change')];
        }

        // 按需
        if($isOnDemand){
            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice, 4);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            $basePrice = 0;
            $price = 0;
            $priceDifference = 0;
            $renewPriceClientLevelDiscount = '0.0000';
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            $renewShowPrice = $this->calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice);
            $renewPrice = $renewShowPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $renewShowPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $renewShowPrice['on_demand_renew_price_difference_client_level_discount'];
        }else{
            $oldPrice = bcmul($oldPrice, $duration['price_factor'], 4);
            $price = bcmul($price, $duration['price_factor'], 4);

            // 升级差价（用于计算升级价格，应用升级折扣）
            $upgradePriceDifference = bcsub($price, $oldPrice, 2);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            if($this->hostModel['billing_cycle_time']>0){
                $price = $upgradePriceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $upgradePriceDifference;
            }

            // base_price 使用续费差价
            $basePrice = bcadd($this->hostModel['base_price'], $renewPriceDifference, 2);
            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            // if($isDownstream){
            // 	$DurationModel = new DurationModel();
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$priceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $priceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }

            // 计算用户等级折扣（仅非按需计费）
            $clientLevel = $this->getClientLevel([
                'product_id'    => $productId,
                'client_id'     => get_client_id(),
            ]);
            if (!$priceAgent){
                $clientLevel = [];
            }
            if(!empty($clientLevel)){
                // 应用价格因子
                if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
                    $discountPrice = bcmul($discountPrice, $duration['price_factor'], 4);
                    $renewDiscountPrice = bcmul($renewDiscountPrice, $duration['price_factor'], 4);
                }
                
                // 按时间比例计算升级折扣价格
                if($this->hostModel['billing_cycle_time']>0){
                    $discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
                }
                
                // 计算实际升级/续费折扣金额
                $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                $renewDiscount = bcdiv($renewDiscountPrice*$clientLevel['discount_percent'], 100, 2);
                
                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => min(-$discount, 0),
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        // '{host_id}' => $this->hostModel['id'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
                
                // 升级价格应用升级折扣
                $price = bcsub($price, $discount);
                
                // 下游的 base_price 需要应用折扣
                if (isset($param['is_downstream']) && $param['is_downstream']==1){
                    $basePrice = bcsub($basePrice, $renewDiscount, 2);
                }
            }

            // price_difference 使用升降级差价
            $priceDifference = $price;

            $price = max(0, $price);

            // 计算续费价格,根据配置决定是否应用用户等级折扣
            $renewPriceDifferenceClientLevelDiscount = 0;
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            if(!empty($clientLevel) && !$isDownstream){
                // 计算续费折扣
                $renewPriceDifferenceClientLevelDiscount = bcdiv($renewDiscountPrice * $clientLevel['discount_percent'], 100, 2);
                
                // 续费差价 = 原始续费差价 - 续费折扣
                // $renewPriceDifference = bcsub($renewPriceDifference, $renewPriceClientLevelDiscount, 2);
            }
            
            $renewPrice = bcadd($this->hostModel['renew_amount'], bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 2), 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }
        $price = amount_format($price);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 				=> $price,
                'description' 		    => implode(',', $description),
                'price_difference' 	    => $priceDifference,
                'renew_price_difference'=> $renewPriceDifference ?? $priceDifference,
                'ip_data'               => $ipData ?? [],
                'ipv6_data'             => $ipv6Data ?? [],
                'new_config_data'       => $newConfigData ?? [],
                'remove_ipv4'			=> $removeIpv4 ?? [],
                'remove_ipv6'			=> $removeIpv6 ?? [],
                'base_price'            => $basePrice,
                'renew_price'			=> $renewPrice,
                'keep_time_price_difference' => $keepTimePriceDifference ?? -1,
                'renew_price_client_level_discount' => $renewPriceClientLevelDiscount ?? '0.0000',
                'discount'				=> max($discount ?? 0, 0),
                'order_item'			=> $orderItem,
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.0000',
                'renew_discount_price_difference' => $renewDiscountPrice ?? '0.0000',
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成IP数量订单
     * @desc 生成IP数量订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.ip_num - 附加IP数量 require
     * @param   int param.ipv6_num - 附加IPv6数量
     * @param   array param.ipv6 - 移除的IPv6地址
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createIpNumOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $this->hostModel['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_ip_num'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $res = $this->calIpNumPrice($param);
        if($res['status'] == 400){
            return $res;
        }
        $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
        $currentConfig['ip_num'] = $param['ip_num'] ?? 0;
        $currentConfig['ipv6_num'] = $param['ipv6_num'] ?? NULL;

        $LimitRuleModel = new LimitRuleModel();
        $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig, ['ipv4_num']);
        if($checkLimitRule['status'] == 400){
        	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_upgrade_ip_for_limit_rule')];
        }

        $data = [
            'host_id'     => $this->hostModel['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'upgrade_ip_num',
                'ip_data'    => $res['data']['ip_data'],
                'ipv6_data'	 => $res['data']['ipv6_data'],
                'new_config_data' => $res['data']['new_config_data'],
                'remove_ipv4'=> $res['data']['remove_ipv4'],
                'remove_ipv6'=> $res['data']['remove_ipv6'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'keep_time_price_difference' => $res['data']['keep_time_price_difference'],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
            'renew_price_difference_client_level_discount' => $res['data']['renew_price_difference_client_level_discount'],
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2023-02-13
     * @title 创建VPC网络
     * @desc 创建VPC网络
     * @author hh
     * @version v1
     * @param   string param.name - VPC网络名称 require
     * @param   string param.ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - VPC网络ID
     */
    public function vpcNetworkCreate($param)
    {
        // 商品是否支持VPC
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];
        if($config['support_vpc_network'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_product_not_support_vpc_network')];
        }

        $param['product_id'] = $this->hostModel['product_id'];
        $param['data_center_id'] = $this->hostLinkModel['data_center_id'];
        $param['client_id'] = get_client_id();
        $param['upstream_id'] = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->vpcNetworkCreate([
                'name'	=> $param['name'],
                'ips'	=> $param['ips'],
            ]);
            if($res['status'] != 200){
                return $res;
            }
            $param['upstream_id'] = $res['data']['id'];
        }

        $VpcNetworkModel = new VpcNetworkModel();
        return $VpcNetworkModel->vpcNetworkCreate($param);
    }

    /**
     * 时间 2023-02-14
     * @title 切换VPC网络
     * @desc 切换VPC网络
     * @author hh
     * @version v1
     * @param   int param.vpc_network_id - VPC网络ID require
     * @param   int param.downstream_client_id - 下游用户ID(api时可用)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - 变更后VPC网络名称
     */
    public function changeVpcNetwork($param)
    {
        $vpcNetwork = VpcNetworkModel::find($param['vpc_network_id'] ?? 0);
        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $this->hostModel['client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if(!$vpcNetwork->checkVpcIsEnable($vpcNetwork, $this->hostModel['product_id'], $this->hostLinkModel['data_center_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if($vpcNetwork['id'] == $this->hostLinkModel['vpc_network_id']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vpc_network_not_change')];
        }
        if(request()->is_api && !empty($vpcNetwork['downstream_client_id']) && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        $configData = json_decode($this->hostLinkModel['config_data'], true);
        if($configData['network_type'] != 'vpc'){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_normal_network_cannot_change_to_vpc')];
        }
        if(isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_nat_host_cannot_change_vpc')];
        }

        if($this->downstreamHostLogic->isDownstream()){
            $this->downstreamHostLogic->setTimeout(600);
            
            $result = $this->downstreamHostLogic->changeVpcNetwork([
                'vpc_network_id'	=> $vpcNetwork['upstream_id'],
            ]);

            if($result['status'] == 200){
                hostLinkModel::where('host_id', $this->hostModel['id'])->update(['vpc_network_id'=>$vpcNetwork['id']]);

                $description = lang_plugins('log_mf_cloud_start_change_vpc_network_success', [
                    '{hostname}' => $this->hostModel['name'],
                    '{name}' 	 => $vpcNetwork['name'],
                ]);
            }else{
                $description = lang_plugins('log_mf_cloud_start_change_vpc_network_fail', [
                    '{hostname}' => $this->hostModel['name'],
                    '{name}' 	 => $vpcNetwork['name'],
                ]);
            }
            $result['data']['name'] = $vpcNetwork['name'];

            active_log($description, 'host', $this->hostModel['id']);
            return $result;
        }

        $post = [];
        // 检查下VPC在魔方云是否还存在
        if(!empty($vpcNetwork['rel_id'])){
            $remoteVpc = $this->idcsmartCloud->vpcNetworkDetail($vpcNetwork['rel_id']);
            if($remoteVpc['status'] == 200){
                $post['vpc'] = $vpcNetwork['rel_id'];
            }else{
                $post['vpc_ips'] = $vpcNetwork['ips'];
            }
        }else{
            $post['vpc_ips'] = $vpcNetwork['ips'];
        }
        $res = $this->idcsmartCloud->cloudChangeVpcNetwork($this->id, $post);
        if($res['status'] == 200){
            // 新创建的VPC,等待完成后获取vpc保存
            if(!isset($post['vpc'])){
                // 等待10分钟
                for($i = 0; $i<100; $i++){
                    $taskRes = $this->idcsmartCloud->taskDetail($res['data']['taskid']);
                    if($taskRes['status'] == 200 && !in_array($taskRes['data']['status'], [0,1])){
                        break;
                    }
                    sleep(6);
                }
                // 任务成功
                if($taskRes['status'] == 200 && $taskRes['data']['status'] == 2){
                    $result = [
                        'status' => 200,
                        'msg'	 => lang_plugins('mf_cloud_change_vpc_network_success'),
                    ];
                    hostLinkModel::where('host_id', $this->hostModel['id'])->update(['vpc_network_id'=>$vpcNetwork['id']]);

                    $detail = $this->idcsmartCloud->cloudDetail($this->id);
                    if($detail['status'] == 200){
                        $result = [
                            'status' => 200,
                            'msg'	 => lang_plugins('mf_cloud_change_vpc_network_success'),
                        ];
                        VpcNetworkModel::where('id', $vpcNetwork['id'])->update(['rel_id'=>$detail['data']['network'][0]['vpc'] ?? 0, 'vpc_name'=>$detail['data']['vpc_name'] ?? 'VPC-'.rand_str(8)]);
                    }else{
                        // 获取不到详情,没法保存关联ID

                    }
                    $description = lang_plugins('log_mf_cloud_change_vpc_network_success', [
                        '{hostname}' => $this->hostModel['name'],
                        '{name}' => $vpcNetwork['name'],
                    ]);
                }else{
                    $result = [
                        'status' => 400,
                        'msg'	 => lang_plugins('mf_cloud_change_vpc_network_fail'),
                    ];
                    $description = lang_plugins('log_mf_cloud_change_vpc_network_fail', [
                        '{hostname}' => $this->hostModel['name'],
                        '{name}' => $vpcNetwork['name'],
                    ]);
                }
            }else{
                $result = [
                    'status' => 200,
                    'msg'	 => lang_plugins('mf_cloud_start_change_vpc_network_success'),
                ];
                hostLinkModel::where('host_id', $this->hostModel['id'])->update(['vpc_network_id'=>$vpcNetwork['id']]);

                $description = lang_plugins('log_mf_cloud_start_change_vpc_network_success', [
                    '{hostname}' => $this->hostModel['name'],
                    '{name}' => $vpcNetwork['name'],
                ]);
            }
        }else{
            $result = [
                'status' => 400,
                'msg'	 => lang_plugins('mf_cloud_change_vpc_network_fail'),
            ];

            $description = lang_plugins('log_mf_cloud_start_change_vpc_network_fail', [
                '{hostname}' => $this->hostModel['name'],
                '{name}' => $vpcNetwork['name'],
            ]);
        }
        if($result['status'] == 200){
            $this->hostLinkModel->syncIp(['host_id'=>$this->hostModel['id'], 'id'=>$this->id ], $this->idcsmartCloud, $detail ?? NULL, false);
        }

        $result['data']['name'] = $vpcNetwork['name'];

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-02-14
     * @title 获取cpu/内存使用信息
     * @author hh
     * @version v1
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.cpu_usage - CPU使用率
     * @return  string data.memory_total - 内存总量(‘-’代表获取不到)
     * @return  string data.memory_usable - 可用内存(‘-’代表获取不到)
     * @return  string data.memory_usage - 内存使用率(‘-1’代表获取不到)
     */
    public function cloudRealData()
    {
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'cpu_usage' => '0' ,
                'memory_total' => '-',
                'memory_usable' => '-',
                'memory_usage' => '-1',
            ]
        ];

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->realData();

            if($res['status'] == 200){
                $result['data'] = $res['data'];
            }
        }else{
            $res = $this->idcsmartCloud->cloudList(['id'=>$this->id]);
            if($res['status'] == 200){
                $result['data']['cpu_usage'] = $res['data']['data'][0]['cpu_usage'] ?? '0';
                $result['data']['memory_total'] = $res['data']['data'][0]['memory_total'] ?? '-';
                $result['data']['memory_usable'] = $res['data']['data'][0]['memory_usable'] ?? '-';
                $result['data']['memory_usage'] = $res['data']['data'][0]['memory_usage'] ?? '-1';
            }
        }
        return $result;
    }

    /**
     * 时间 2022-09-25
     * @title 计算产品配置升级价格
     * @desc 计算产品配置升级价格
     * @author hh
     * @version v1
     * @param   int param.cpu - 核心数 require
     * @param   int param.memory - 内存 require
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @param   int param.check_limit_rule 0 是否验证限制规则(0=否,1=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价,没有折扣
     * @return  string data.renew_price_difference - 续费差价,没有折扣
     * @return  array data.new_config_data - 新的配置记录
     * @return  int data.new_config_data.cpu.value - CPU
     * @return  string data.new_config_data.cpu.price - 价格
     * @return  string data.new_config_data.cpu.other_config.advanced_cpu - 智能CPU规则ID
     * @return  string data.new_config_data.cpu.other_config.cpu_limit - CPU限制
     * @return  int data.new_config_data.memory.value - 内存
     * @return  string data.new_config_data.memory.price - 价格
     * @return  int data.new_config_data.bw.value - 带宽
     * @return  string data.new_config_data.bw.price - 价格
     * @return  string data.new_config_data.bw.other_config.in_bw - 流入带宽
     * @return  string data.new_config_data.bw.other_config.advanced_bw - 智能带宽规则ID
     * @return  int data.new_config_data.flow.value - 流量
     * @return  string data.new_config_data.flow.price - 价格
     * @return  int data.new_config_data.flow.other_config.in_bw - 入站带宽
     * @return  int data.new_config_data.flow.other_config.out_bw - 出站带宽
     * @return  int data.new_config_data.flow.other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string data.new_config_data.flow.other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     * @return  int data.new_config_data.defence.value - 防御峰值
     * @return  string data.new_config_data.defence.price - 价格
     */
    public function calCommonConfigPrice($param)
    {
        // 套餐不能单独购买磁盘
        if(!empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        $productId = $this->hostModel['product_id'];
        $diffTime  = $this->hostModel['due_time'] - time();
        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        $newConfigData = [];

        // 获取之前的周期
        if($isOnDemand){
            $duration = [
                'id'	=> 'on_demand',
            ];
            $productOnDemand = ProductOnDemandModel::getProductOnDemand($productId);
        }else{
            $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
            }
        }
        $OptionModel = new OptionModel();
        
        // 获取产品等级优惠配置
        $config = ConfigModel::where('product_id', $productId)->find();

        $oldPrice = 0;  // 老价格
        $price = 0;     // 新价格
        $keepTimePriceDifference = '0'; // 保留期价格
        $description = []; // 描述
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0; 		// 实际优惠价格
        $orderItem = [];	// 要添加的用户等级子项
        
        // 各配置项的原始差价（用于续费折扣计算）
        $cpuOriPriceDiff = '0.0000';
        $memoryOriPriceDiff = '0.0000';
        $bwOriPriceDiff = '0.0000';
        $flowOriPriceDiff = '0.0000';
        $defenceOriPriceDiff = '0.0000';
        $renewDiscountPrice = 0; // 可以续费折扣的总金额
        
        if($param['cpu'] != $configData['cpu']['value']){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $param['cpu'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
            }
            $newConfigData['cpu'] = [
                'value' => $param['cpu'],
                'price' => $optionDurationPrice['price'] ?? 0,
                'other_config' => $optionDurationPrice['option']['other_config'],
            ];

            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $configData['cpu']['value'], $duration['id']);

            // 应用CPU等级优惠
            $cpuOldPrice = $currentOptionDurationPrice['price'] ?? 0;
            $cpuNewPrice = $optionDurationPrice['price'] ?? 0;
            
            // 保存 CPU 原始差价（用于续费折扣）
            $cpuOriPriceDiff = bcsub($cpuNewPrice, $cpuOldPrice, 4);
            
            // 累计用户等级折扣金额
            if(!empty($config) && $config['level_discount_cpu_upgrade'] == 1){
                $discountPrice = bcadd($discountPrice, $cpuOriPriceDiff, 4);
            }
            
            // 累计续费折扣金额
            if(!empty($config) && $config['level_discount_cpu_renew'] == 1){
                $renewDiscountPrice = bcadd($renewDiscountPrice, $cpuOriPriceDiff, 4);
            }

            $oldPrice = bcadd($oldPrice, $cpuOldPrice, 4);
            $price    = bcadd($price, $cpuNewPrice, 4);
            
            // 单个配置差价
            $diffPrice = bcsub($cpuNewPrice, $cpuOldPrice, 4);

            if($isOnDemand && in_array('cpu', $productOnDemand['keep_time_billing_item'])){
                $keepTimePriceDifference = bcadd($keepTimePriceDifference, $diffPrice, 4);
            }

            $description[] = sprintf("CPU: %d => %d", $configData['cpu']['value'], $param['cpu']);
        }
        // 获取内存周期价格
        if($param['memory'] != $configData['memory']['value']){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
            }
            // 获取单位
            $memoryUnit = ConfigModel::where('product_id', $productId)->value('memory_unit') ?? 'GB';

            $newConfigData['memory'] = [
                'value' => $param['memory'],
                'price' => $optionDurationPrice['price'] ?? 0
            ];
            $newConfigData['memory_unit'] = $memoryUnit;

            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $configData['memory']['value'], $duration['id']);

            // 应用内存等级优惠
            $memoryOldPrice = $currentOptionDurationPrice['price'] ?? 0;
            $memoryNewPrice = $optionDurationPrice['price'] ?? 0;
            
            // 保存内存原始差价（用于续费折扣）
            $memoryOriPriceDiff = bcsub($memoryNewPrice, $memoryOldPrice, 4);
            
            // 累计用户等级折扣金额
            if(!empty($config) && $config['level_discount_memory_upgrade'] == 1){
                $discountPrice = bcadd($discountPrice, $memoryOriPriceDiff, 4);
            }
            
            // 累计续费折扣金额
            if(!empty($config) && $config['level_discount_memory_renew'] == 1){
                $renewDiscountPrice = bcadd($renewDiscountPrice, $memoryOriPriceDiff, 4);
            }

            $oldPrice = bcadd($oldPrice, $memoryOldPrice, 4);
            $price    = bcadd($price, $memoryNewPrice, 4);
            
            // 单个配置差价
            $diffPrice = bcsub($memoryNewPrice, $memoryOldPrice, 4);

            if($isOnDemand && in_array('memory', $productOnDemand['keep_time_billing_item'])){
                $keepTimePriceDifference = bcadd($keepTimePriceDifference, $diffPrice, 4);
            }

            $description[] = sprintf("%s: %d => %d", lang_plugins('memory'), $configData['memory']['value'], $param['memory']);
        }
        // 检查之前的线路是否还存在
        $line = LineModel::where('id', $configData['line']['id'])->find();
        if(empty($line)){
            // 不支持bw/flow/peak_defence升降机
            if($configData['line']['bill_type'] == 'bw' && isset($param['bw']) && is_numeric($param['bw']) && $param['bw'] != $configData['bw']['value']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_bw_upgrade')];
            }
            if($configData['line']['bill_type'] == 'flow' && isset($param['flow']) && is_numeric($param['flow']) && $param['flow'] != $configData['flow']['value']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_flow_upgrade')];
            }
            if(isset($param['peak_defence']) && isset($configData['defence']['value']) && is_numeric($param['peak_defence']) && $param['peak_defence'] != $configData['defence']['value']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_defence_upgrade')];
            }

            // 线路的都不能升级
            $param['bw'] = null;
            $param['flow'] = null;
            $param['peak_defence'] = null;
        }else{
            // 线路存在的情况
            if($line['bill_type'] == 'bw'){
                $param['flow'] = null;
                // 获取带宽周期价格
                if(isset($param['bw']) && !empty($param['bw']) && $param['bw'] != $configData['bw']['value']){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('bw_error') ];
                    }
                    $newConfigData['bw'] = [
                        'value' => $param['bw'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                        'other_config' => $optionDurationPrice['option']['other_config'],
                    ];

                    $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $configData['bw']['value'] ?? -1, $duration['id']);

                    // 应用带宽等级优惠
                    $bwOldPrice = $currentOptionDurationPrice['price'] ?? 0;
                    $bwNewPrice = $optionDurationPrice['price'] ?? 0;
                    
                    // 保存带宽原始差价（用于续费折扣）
                    $bwOriPriceDiff = bcsub($bwNewPrice, $bwOldPrice, 4);
                    
                    // 累计用户等级折扣金额
                    if(!empty($config) && $config['level_discount_bw_upgrade'] == 1){
                        $discountPrice = bcadd($discountPrice, $bwOriPriceDiff, 4);
                    }
                    
                    // 累计续费折扣金额
                    if(!empty($config) && $config['level_discount_bw_renew'] == 1){
                        $renewDiscountPrice = bcadd($renewDiscountPrice, $bwOriPriceDiff, 4);
                    }

                    $oldPrice = bcadd($oldPrice, $bwOldPrice, 4);
                    $price    = bcadd($price, $bwNewPrice, 4);

                    // 单个配置差价
                    // $diffPrice = bcsub($price, $oldPrice, 4);

                    // if($isOnDemand && in_array('bw', $productOnDemand['keep_time_billing_item'])){
                    // 	$keepTimePriceDifference = bcadd($keepTimePriceDifference, $diffPrice, 4);
                    // }

                    $description[] = sprintf("%s: %d => %d", lang_plugins('bw'), $configData['bw']['value'], $param['bw']);
                }
            }else if($line['bill_type'] == 'flow'){
                // 获取流量周期价格
                $flowChange = !$isOnDemand && isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0 && $param['flow'] != $configData['flow']['value'];
                $bwChange = isset($param['bw']) && is_numeric($param['bw']) && $param['bw']>=0 && $param['bw'] != $configData['flow']['other_config']['out_bw'];
                if($flowChange || $bwChange){
                    if($isOnDemand){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW_ON_DEMAND, $line['id'], 0, $duration['id'], $param['bw']);
                    }else{
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $duration['id'], $param['bw']);
                    }
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                    }
                    $newConfigData['flow'] = [
                        'value' => $param['flow'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                        'other_config' => $optionDurationPrice['option']['other_config'],
                    ];

                    // 按需流量单独计费
                    if($isOnDemand){
                        $onDemandFlowPrice = $optionDurationPrice['price'];
                    }else{
                        $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $configData['flow']['value'] ?? -1, $duration['id'], $configData['flow']['other_config']['out_bw'] ?? -1);

                        // 应用流量等级优惠
                        $flowOldPrice = $currentOptionDurationPrice['price'] ?? 0;
                        $flowNewPrice = $optionDurationPrice['price'] ?? 0;
                        
                        // 保存流量原始差价（用于续费折扣）
                        $flowOriPriceDiff = bcsub($flowNewPrice, $flowOldPrice, 4);
                        
                        // 累计用户等级折扣金额
                        $discountPrice = bcadd($discountPrice, $flowOriPriceDiff, 4);
                        
                        // 累计续费折扣金额（流量直接计算，无配置开关）
                        $renewDiscountPrice = bcadd($renewDiscountPrice, $flowOriPriceDiff, 4);

                        $oldPrice = bcadd($oldPrice, $flowOldPrice, 4);
                        $price    = bcadd($price, $flowNewPrice, 4);
                    }

                    if($flowChange){
                        $description[] = sprintf("%s: %d => %d", lang_plugins('flow'), $configData['flow']['value'], $param['flow']);
                    }
                    if($bwChange){
                        $description[] = sprintf("%s: %d => %d", lang_plugins('bw'), $configData['flow']['other_config']['out_bw'], $param['bw']);
                    }
                }
            }
            // 防护
            if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 0 && isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0 && $param['peak_defence'] != ($configData['defence']['value'] ?? 0)){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                }
                $newConfigData['defence'] = [
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $configData['defence']['value'] ?? 0, $duration['id']);

                // 应用防御等级优惠
                $defenceOldPrice = $currentOptionDurationPrice['price'] ?? 0;
                $defenceNewPrice = $optionDurationPrice['price'] ?? 0;
                
                // 保存防御原始差价（用于续费折扣）
                $defenceOriPriceDiff = bcsub($defenceNewPrice, $defenceOldPrice, 4);
                
                // 累计用户等级折扣金额
                $discountPrice = bcadd($discountPrice, $defenceOriPriceDiff, 4);
                
                // 累计续费折扣金额（防御直接计算，无配置开关）
                $renewDiscountPrice = bcadd($renewDiscountPrice, $defenceOriPriceDiff, 4);

                $oldPrice = bcadd($oldPrice, $defenceOldPrice, 4);
                $price    = bcadd($price, $defenceNewPrice, 4);

                // 单个配置差价
                // $diffPrice = bcsub($price, $oldPrice, 4);

                // if($isOnDemand && in_array('peak_defence', $productOnDemand['keep_time_billing_item'])){
                // 	$keepTimePriceDifference = bcadd($keepTimePriceDifference, $diffPrice, 4);
                // }

                $description[] = sprintf("%s: %d => %d", lang_plugins('mf_cloud_recommend_config_peak_defence'), $configData['defence']['value'] ?? 0, $param['peak_defence']);
            }
        }
        if(empty($newConfigData)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_change_config')];
        }
        if(isset($param['check_limit_rule']) && $param['check_limit_rule'] == 1){
            $configData = json_decode($this->hostLinkModel['config_data'], true);

            $param['data_center_id'] = $this->hostLinkModel['data_center_id'];
            $param['line_id'] = $configData['line']['id'] ?? 0;

            $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
            $currentConfig['cpu'] = $param['cpu'];
            $currentConfig['memory'] = $param['memory'];
            $currentConfig['bw'] = $param['bw'] ?? NULL;
            $currentConfig['flow'] = $param['flow'] ?? NULL;
            // $currentConfig['peak_defence'] = $param['peak_defence'] ?? NULL;

            $LimitRuleModel = new LimitRuleModel();
            $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig, ['cpu','memory','bw','flow']);
            if($checkLimitRule['status'] == 400){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_upgrade_common_config_for_limit_rule')];
            }
        }

        // 计算价格系数
        if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
            $oldPrice = bcmul($oldPrice, $duration['price_factor']);
            $price = bcmul($price, $duration['price_factor']);
            
            foreach($newConfigData as $k=>$v){
                if(isset($v['price'])){
                    $newConfigData[$k]['price'] = bcmul($v['price'], $duration['price_factor']);
                }
            }
        }

        $description = implode("\r\n", $description);

        // 按需
        if($isOnDemand){
            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice, 4);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            $basePrice = 0;
            $price = 0;
            $priceDifference = 0;
            $renewPriceClientLevelDiscount = '0.0000';
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            $renewShowPrice = $this->calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice);
            $renewPrice = $renewShowPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $renewShowPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $renewShowPrice['on_demand_renew_price_difference_client_level_discount'];
            
            // 计算流量价格用户等级折扣
            if(isset($onDemandFlowPrice) && is_numeric($onDemandFlowPrice)){
                $hookDiscountResults = hook('client_discount_by_amount', [
                    'client_id'		=> $this->hostModel['client_id'],
                    'product_id'	=> $this->hostModel['product_id'],
                    'amount'		=> $onDemandFlowPrice,
                    'scale'			=> 4,
                ]);
                foreach ($hookDiscountResults as $hookDiscountResult){
                    if ($hookDiscountResult['status'] == 200){
                        $onDemandFlowPriceClientLevelDiscount = $hookDiscountResult['data']['discount'] ?? 0;
                    }
                }
            }
        }else{
            // 升级差价（用于计算升级价格，应用升级折扣）
            $upgradePriceDifference = bcsub($price, $oldPrice, 2);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            
            if($this->hostModel['billing_cycle_time']>0){
                $price = $upgradePriceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $upgradePriceDifference;
            }

            // base_price 使用续费差价
            $basePrice = bcadd($this->hostModel['base_price'], $renewPriceDifference, 2);
            
            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            // if($isDownstream){
            // 	$DurationModel = new DurationModel();
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$priceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $priceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }

            // 计算用户等级折扣（仅非按需计费）
            $clientLevel = $this->getClientLevel([
                'product_id'    => $productId,
                'client_id'     => get_client_id(),
            ]);
            if (!$priceAgent){
                $clientLevel = [];
            }
            if(!empty($clientLevel)){
                // 应用价格因子
                if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
                    $discountPrice = bcmul($discountPrice, $duration['price_factor'], 4);
                    $renewDiscountPrice = bcmul($renewDiscountPrice, $duration['price_factor'], 4);
                }
                
                // 按时间比例计算升级折扣价格
                if($this->hostModel['billing_cycle_time']>0){
                    $discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
                }
                
                // 计算实际升级/续费折扣金额
                $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                $renewDiscount = bcdiv($renewDiscountPrice*$clientLevel['discount_percent'], 100, 2);
                
                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => min(-$discount, 0),
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        // '{host_id}' => $this->hostModel['id'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
                
                // 升级价格应用升级折扣
                $price = bcsub($price, $discount);
                
                // 下游的 base_price 需要应用折扣
                if (isset($param['is_downstream']) && $param['is_downstream']==1){
                    // $basePriceDiscount = bcdiv($basePrice*$clientLevel['discount_percent'], 100, 2);
                    $basePrice = bcsub($basePrice, $renewDiscount, 2);
                }
            }

            // price_difference 使用升降级差价
            $priceDifference = $price;

            $price = max(0, $price);
            $price = amount_format($price);

            // 计算续费价格,根据配置决定是否应用用户等级折扣
            $renewPriceDifferenceClientLevelDiscount = 0;
            
            // 统一计算续费折扣，使用续费差价和续费折扣配置
            if(!empty($clientLevel) && !$isDownstream){
                // 计算续费折扣
                $renewPriceDifferenceClientLevelDiscount = bcdiv($renewDiscountPrice * $clientLevel['discount_percent'], 100, 2);
                
                // 续费差价 = 原始续费差价 - 续费折扣
                // $renewPriceDifference = bcsub($renewPriceDifference, $renewPriceClientLevelDiscount, 2);
            }

            $renewPrice = bcadd($this->hostModel['renew_amount'], bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 2), 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 					=> $price,  // 升级价格（应用升级折扣）
                'description' 				=> $description,
                'price_difference' 			=> $priceDifference,  // 升降级差价（应用续费折扣）
                'renew_price_difference' 	=> $renewPriceDifference,  // 续费差价,不能应该折扣
                'base_price'                => $basePrice,  // 基础价格（使用续费差价）
                'renew_price'				=> $renewPrice,  // 非按需续费价格（应用续费折扣）| 按需原价
                'renew_price_client_level_discount' => $renewPriceClientLevelDiscount ?? '0.0000',  // 续费用户等级折扣
                'on_demand_flow_price'		=> $onDemandFlowPrice ?? -1,    // 按需流量原价
                'on_demand_flow_price_client_level_discount' => $onDemandFlowPriceClientLevelDiscount ?? '0.0000', // 按需流量等级折扣
                'keep_time_price_difference'=> $keepTimePriceDifference,
                'new_config_data'			=> $newConfigData,
                'discount'					=> max($discount ?? 0, 0),  // 升级折扣
                'order_item'				=> $orderItem,        
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.0000',
                'renew_discount_price_difference' => $renewDiscountPrice ?? '0.0000',
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成产品配置升级订单
     * @desc 生成产品配置升级订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.cpu - 核心数 require
     * @param   int param.memory - 内存 require
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createCommonConfigOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $this->hostModel['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_config'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $param['check_limit_rule'] = 1;
        $res = $this->calCommonConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $data = [
            'host_id'     => $this->hostModel['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'on_demand_flow_price'		=> $res['data']['on_demand_flow_price'],
            'keep_time_price_difference'=> $res['data']['keep_time_price_difference'],
            'config_options' => [
                'type'       		=> 'upgrade_common_config',
                'new_config_data'   => $res['data']['new_config_data'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
            'renew_price_difference_client_level_discount' => $res['data']['renew_price_difference_client_level_discount'],
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2023-09-20
     * @title NAT转发列表
     * @desc  NAT转发列表
     * @author hh
     * @version v1
     * @return  int list[].id - 转发ID
     * @return  string list[].name - 名称
     * @return  string list[].ip - IP端口
     * @return  int list[].int_port - 内部端口
     * @return  int list[].protocol - 协议(1=tcp,2=udp,3=tcp+udp)
     * @return  int count - 总条数
     */
    public function natAclList()
    {
        $data = [];
        $count = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $list = $this->downstreamHostLogic->natAclList();

            $data = $list['data']['list'] ?? [];
            $count = $list['data']['count'] ?? 0;
        }else{
            // 获取当前所有IP
            $list = $this->idcsmartCloud->natAclList($this->id, ['page'=>1, 'per_page'=>999]);
            if(isset($list['data']['data'])){
                foreach($list['data']['data'] as $v){
                    $data[] = [
                        'id' 		=> $v['id'],
                        'name' 		=> $v['name'],
                        'ip'		=> $list['data']['nat_host_ip'].':'.$v['ext_port'],
                        'int_port' 	=> $v['int_port'],
                        'protocol'	=> $v['protocol'],
                    ];
                }
            }

            $count = count($data);
        }

        return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2023-09-20
     * @title 创建NAT转发
     * @desc 创建NAT转发
     * @author hh
     * @version v1
     * @param   string param.name - 名称 require
     * @param   int param.int_port - 内部端口 require
     * @param   int param.protocol - 协议(1=tcp,2=udp,3=tcp+udp) require
     * @param   int param.ext_port - 外部端口(0-65535除开80/443/22)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function natAclCreate($param)
    {
        $natAclList = $this->natAclList();
        $total = $natAclList['count'];

        $configData = json_decode($this->hostLinkModel['config_data'], true);
        if($total >= $configData['nat_acl_limit']){
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_cloud_nat_acl_be_limited'),
            ];
            return $result;
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->natAclCreate([
                'name'      => $param['name'],
                'int_port'  => $param['int_port'],
                'protocol'  => $param['protocol'],
                'ext_port'  => $param['ext_port'] ?? '',
            ]);
        }else{
            $res = $this->idcsmartCloud->natAclCreate($this->id, [
                'name'      => $param['name'],
                'int_port'  => $param['int_port'],
                'protocol'  => $param['protocol'],
                'ext_port'  => $param['ext_port'] ?? '',
            ]);
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('create_success')
            ];

            $description = lang_plugins('log_mf_cloud_nat_acl_create_success', [
                '{hostname}' => $this->hostModel['name'],
                '{name}' 	 => $param['name'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => !empty($param['ext_port']) ? $res['msg'] : lang_plugins('create_failed'),
            ];

            $description = lang_plugins('log_mf_cloud_nat_acl_create_fail', [
                '{hostname}' => $this->hostModel['name'],
                '{name}' 	 => $param['name'],
            ]);			
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-09-20
     * @title 删除NAT转发
     * @desc 删除NAT转发
     * @author hh
     * @version v1
     * @param   int nat_acl_id - NAT转发ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - NAT转发名称
     */
    public function natAclDelete($nat_acl_id)
    {
        $data = [];

        $natAclList = $this->natAclList();
        foreach($natAclList['list'] as $v){
            if($v['id'] == $nat_acl_id){
                $data = $v;
                break;
            }
        }

        if(!empty($data)){
            $defaultId = min(array_column($natAclList['list'], 'id'));
            if($data['id'] == $defaultId){
                $result = [
                    'status' => 400,
                    'msg'    => lang_plugins('mf_cloud_default_nat_acl_cannot_delete'),
                ];
                return $result;
            }
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_cloud_nat_acl_not_found'),
            ];
            return $result;
        }
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->natAclDelete([
                'nat_acl_id'    => $nat_acl_id,
            ]);
        }else{
            $res = $this->idcsmartCloud->natAclDelete($this->id, $nat_acl_id);
        }
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('delete_success'),
                'data'	 => [
                    'name' => $data['name'],
                ]
            ];

            $description = lang_plugins('log_mf_cloud_nat_acl_delete_success', [
                '{hostname}' => $this->hostModel['name'],
                '{name}' 	 => $data['name'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('delete_failed'),
                'data'	 => [
                    'name' => $data['name'],
                ],
            ];

            $description = lang_plugins('log_mf_cloud_nat_acl_delete_fail', [
                '{hostname}' => $this->hostModel['name'],
                '{name}' 	 => $data['name'],
            ]);			
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-09-20
     * @title NAT建站列表
     * @desc  NAT建站列表
     * @author hh
     * @version v1
     * @return  int list[].id - 建站ID
     * @return  string list[].domain - 域名
     * @return  int list[].ext_port - 外部端口
     * @return  int list[].int_port - 内部端口
     * @return  int count - 总条数
     */
    public function natWebList()
    {
        $data = [];
        $count = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $natWebList = $this->downstreamHostLogic->natWebList();

            $data = $natWebList['data']['list'] ?? [];
            $count = $natWebList['data']['count'] ?? 0;
        }else{
            // 获取当前所有IP
            $list = $this->idcsmartCloud->natWebList($this->id, ['page'=>1, 'per_page'=>999]);
            $data = $list['data']['data'] ?? [];
            
            $count = count($data);
        }

        return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2023-09-20
     * @title 创建NAT建站
     * @desc 创建NAT建站
     * @author hh
     * @version v1
     * @param   string param.domain - 域名 require
     * @param   int param.int_port - 内部端口 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function natWebCreate($param)
    {
        $natWebList = $this->natWebList();
        $total = $natWebList['count'];

        $configData = json_decode($this->hostLinkModel['config_data'], true);
        if($total >= $configData['nat_web_limit']){
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_cloud_nat_web_be_limited'),
            ];
            return $result;
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->natWebCreate([
                'domain'    => $param['domain'],
                'int_port'  => $param['int_port'],
            ]);
        }else{
            $res = $this->idcsmartCloud->natWebCreate($this->id, [
                'domain'    => $param['domain'],
                'ext_port'  => 80,
                'int_port'  => $param['int_port'],
            ]);
        }
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('create_success')
            ];

            $description = lang_plugins('log_mf_cloud_nat_web_create_success', [
                '{hostname}' => $this->hostModel['name'],
                '{domain}' 	 => $param['domain'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('create_failed')
            ];

            $description = lang_plugins('log_mf_cloud_nat_web_create_fail', [
                '{hostname}' => $this->hostModel['name'],
                '{domain}' 	 => $param['domain'],
            ]);			
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-09-20
     * @title 删除NAT建站
     * @desc 删除NAT建站
     * @author hh
     * @version v1
     * @param   int nat_web_id - NAT建站ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.domain - 域名
     */
    public function natWebDelete($nat_web_id)
    {
        $data = [];

        $natWebList = $this->natWebList();
        foreach($natWebList['list'] as $v){
            if($v['id'] == $nat_web_id){
                $data = $v;
                break;
            }
        }
        if(empty($data)){
            $result = [
                'status' => 400,
                'msg'	 => lang_plugins('mf_cloud_nat_web_not_found'),
            ];
            return $result;
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->natWebDelete([
                'nat_web_id' => $nat_web_id,
            ]);
        }else{
            $res = $this->idcsmartCloud->natWebDelete($this->id, $nat_web_id);
        }
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('delete_success'),
                'data'	 => [
                    'domain' => $data['domain'],
                ],
            ];

            $description = lang_plugins('log_mf_cloud_nat_web_delete_success', [
                '{hostname}' => $this->hostModel['name'],
                '{domain}' 	 => $data['domain'],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('delete_failed'),
                'data'	 => [
                    'domain' => $data['domain'],
                ],
            ];

            $description = lang_plugins('log_mf_cloud_nat_web_delete_fail', [
                '{hostname}' => $this->hostModel['name'],
                '{domain}' 	 => $data['domain'],
            ]);			
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2023-10-24
     * @title 获取可升降级套餐
     * @desc 获取可升降级套餐
     * @author hh
     * @version v1
     * @return  int list[].id - 套餐ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  int list[].order - 排序ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].cpu - CPU
     * @return  int list[].memory - 内存(GB)
     * @return  int list[].system_disk_size - 系统盘大小(GB)
     * @return  int list[].data_disk_size - 数据盘大小(GB)
     * @return  int list[].bw - 带宽(Mbps)
     * @return  int list[].peak_defence - 防御峰值(G)
     * @return  string list[].system_disk_type - 系统盘类型
     * @return  string list[].data_disk_type - 数据盘类型
     * @return  int list[].flow - 流量
     * @return  int list[].line_id - 线路ID
     * @return  int list[].create_time - 创建时间
     * @return  int list[].ip_num - IP数量
     * @return  int list[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int list[].hidden - 是否隐藏(0=否,1=是)
     * @return  int list[].gpu_num - 显卡数量
     * @return  int list[].ipv6_num - IPv6数量
     * @return  string list[].gpu_name - 显卡名称
     * @return  string list[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int count - 总条数
     */
       public function getUpgradeRecommendConfig()
       {
           if(empty($this->hostLinkModel['recommend_config_id'])){
               return ['list'=> [], 'count'=>0];
           }

           $recommendConfig = RecommendConfigModel::find($this->hostLinkModel['recommend_config_id']);
           if(empty($recommendConfig)){
            return ['list'=> [], 'count'=>0];
           }
           $configData = json_decode($this->hostLinkModel['config_data'], true);

        if(!$this->supportRecommendConfigUpgrade($recommendConfig)){
            return ['list'=> [], 'count'=>0];
        }

           $RecommendConfigModel = new RecommendConfigModel();
           return $RecommendConfigModel->getUpgradeRecommendConfig($recommendConfig, $configData['network_type'] == 'vpc', $this->hostModel['billing_cycle'] == 'on_demand');
       }

       /**
        * 时间 2023-10-25
        * @title 计算升降级套餐价格
        * @desc  计算升降级套餐价格
        * @author hh
        * @version v1
        * @param   int param.recommend_config_id - 套餐ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价/按需差价
     * @return  string data.base_price - 基础价格
     * @return  int data.settle_time - 按需结算时间
     * @return  array data.new_config_data.recommend_config - 新套餐数据
     * @return  array data.new_config_data.line - 新套餐线路数据
     * @return  string data.discount - 用户等级折扣金额
     * @return  array data.order_item - 订单项目列表
        */
       public function calUpgradeRecommendConfig($param): array
       {
        if(empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        $targetRecommendConfig = RecommendConfigModel::find($param['recommend_config_id'] ?? 0);
        if(empty($targetRecommendConfig) || $this->hostLinkModel['recommend_config_id'] == $targetRecommendConfig['id'] || $targetRecommendConfig['upgrade_show'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }

        bcscale(2);
        $productId = $this->hostModel['product_id'];
        $hostId    = $this->hostModel['id'];
        $diffTime  = $this->hostModel['due_time'] - time();

        $configData = json_decode($this->hostLinkModel['config_data'], true);
        $recommendConfig = RecommendConfigModel::find($this->hostLinkModel['recommend_config_id']);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_DISABLE){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }else if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_ALL){
            
        }else if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_CUSTOM){
            $recommendConfigUpgradeRange = RecommendConfigUpgradeRangeModel::where('recommend_config_id', $recommendConfig['id'])->where('rel_recommend_config_id', $param['recommend_config_id'])->find();
               if(empty($recommendConfigUpgradeRange)){
                   return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
               }
        }
        if($targetRecommendConfig['product_id'] != $productId || $targetRecommendConfig['data_center_id'] != $recommendConfig['data_center_id']){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        // VPC不能选择有IPv6的套餐
        if($configData['network_type'] == 'vpc' && !empty($targetRecommendConfig['ipv6_num'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vpc_network_cannot_use_ipv6')];
        }
        // 是否可以套餐升降级
        if(!$this->supportRecommendConfigUpgrade($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }

        $newConfigData = [];
        // 试用
        if ($configData['duration']['id'] == config('idcsmart.pay_ontrial')){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
        }
        $newConfigData['recommend_config'] = $targetRecommendConfig;
        $newConfigData['line'] = LineModel::find($targetRecommendConfig['line_id']);

        $description = sprintf("%s: %s => %s", lang_plugins('mf_cloud_recommend_config'), $recommendConfig['name'], $targetRecommendConfig['name']);

        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';
        
        // 初始化用户等级折扣相关变量
        $discount = 0;
        $orderItem = [];
        $renewDiscountPrice = 0;

        // 按需
        if($isOnDemand){
            $duration = [
                'id'	=> 'on_demand',
            ];

            $oldPrice = $recommendConfig['on_demand_price'];
            $price = $targetRecommendConfig['on_demand_price'];

            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice, 4);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;

            // 续费可折扣金额
            $renewDiscountPrice = $renewPriceDifference;

            $basePrice = 0;
            $price = 0;
            $priceDifference = 0;
            $renewPriceClientLevelDiscount = '0.0000';

            $onDemandFlowPrice = $targetRecommendConfig['on_demand_flow_price'];

            $showPrice = $this->calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice);
            $renewPrice = $showPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $showPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $showPrice['on_demand_renew_price_difference_client_level_discount'];

            // 计算续费用户等级折扣
            $hookDiscountResults = hook('client_discount_by_amount', [
                'client_id'		=> $this->hostModel['client_id'],
                'product_id'	=> $this->hostModel['product_id'],
                'amount'		=> $onDemandFlowPrice,
                'scale'			=> 4,
            ]);
            foreach ($hookDiscountResults as $hookDiscountResult){
                if ($hookDiscountResult['status'] == 200){
                    $onDemandFlowPriceClientLevelDiscount = $hookDiscountResult['data']['discount'] ?? 0;
                }
            }
        }else{
            // 获取之前的周期
            $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
            }

            $oldPrice = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $recommendConfig['id'])->where('duration_id', $duration['id'])->value('price');
            $price = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $targetRecommendConfig['id'])->where('duration_id', $duration['id'])->value('price');

            $discountPrice = bcsub($price, $oldPrice, 2);

            // 计算价格系数
            if($duration['price_factor'] != 1){
                $oldPrice = bcmul($oldPrice, $duration['price_factor']);
                $price = bcmul($price, $duration['price_factor']);
            }

            // 升级差价（用于计算升级价格）
            $upgradePriceDifference = bcsub($price, $oldPrice);
            
            // 续费差价（使用原始价格，不受升级折扣影响）
            $renewPriceDifference = $upgradePriceDifference;
            $renewDiscountPrice = $upgradePriceDifference;
            
            if($this->hostModel['billing_cycle_time']>0){
                $price = $upgradePriceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $upgradePriceDifference;
            }

            // base_price 使用续费差价
            $basePrice = bcadd($this->hostModel['base_price'], $renewPriceDifference, 2);

            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            
            // 计算用户等级折扣（仅非下游）
            if(!$isDownstream){
                $clientLevel = $this->getClientLevel([
                    'product_id'    => $productId,
                    'client_id'     => get_client_id(),
                ]);
                if (!$priceAgent){
                    $clientLevel = [];
                }
                if(!empty($clientLevel)){
                    // 应用价格因子
                    if(!empty($duration['price_factor']) && $duration['price_factor'] != 1){
                        $discountPrice = bcmul($discountPrice, $duration['price_factor'], 4);
                    }
                    
                    // 保存原始折扣价格用于续费计算
                    $oriDiscountPrice = $discountPrice;
                    
                    // 按时间比例计算折扣价格
                    if($this->hostModel['billing_cycle_time']>0){
                        $discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
                    }
                    
                    // 计算实际折扣金额
                    $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                    $oriDiscount = bcdiv($oriDiscountPrice*$clientLevel['discount_percent'], 100, 2);
                    
                    $orderItem[] = [
                        'type'          => 'addon_idcsmart_client_level',
                        'rel_id'        => $clientLevel['id'],
                        'amount'        => min(-$discount, 0),
                        'description'   => lang_plugins('mf_cloud_client_level', [
                            '{name}'    => $clientLevel['name'],
                            '{value}'   => $clientLevel['discount_percent'],
                        ]),
                    ];
                    
                    // 升级价格应用升级折扣
                    $price = bcsub($price, $discount);
                    
                    // 下游的 base_price 需要应用折扣
                    if($isDownstream){
                        $basePrice = bcsub($basePrice, $oriDiscount, 2);
                    }
                }
            }
            
            // if($isDownstream){
            // 	$DurationModel = new DurationModel();
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$renewPriceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $renewPriceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }
            
            // price_difference 使用升降级差价（应用了升级折扣）
            $priceDifference = $price;
            $renewPriceDifferenceClientLevelDiscount = $oriDiscount ?? '0.00';

            // 续费价格 = 当前续费价格 + 续费差价 - 续费折扣
            $renewPrice = bcadd($this->hostModel['renew_amount'], bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 2), 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }

        $price = max(0, $price);
        $price = amount_format($price, 2);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 					=> $price,
                'description' 				=> $description,
                'price_difference' 			=> $priceDifference,
                'renew_price_difference' 	=> $renewPriceDifference,
                'base_price'                => $basePrice,
                'renew_price'				=> $renewPrice,
                'on_demand_flow_price'		=> $onDemandFlowPrice ?? 0,
                'new_config_data'			=> $newConfigData,
                'renew_price_client_level_discount'	=> $renewPriceClientLevelDiscount ?? '0.0000',
                'on_demand_flow_price_client_level_discount' => $onDemandFlowPriceClientLevelDiscount ?? '0.0000',
                'discount'					=> max($discount, 0),
                'order_item'				=> $orderItem,
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.0000',
                'renew_discount_price_difference' => $renewDiscountPrice ?? '0.0000',
            ]
        ];
        return $result;
    }

       /**
     * 时间 2023-10-25
     * @title 生成升降级套餐订单
     * @desc 生成升降级套餐订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.recommend_config_id - 套餐ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.id - 订单ID
     */
    public function createUpgradeRecommendConfigOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $param['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_recommend_config'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $res = $this->calUpgradeRecommendConfig($param);
        if($res['status'] == 400){
            return $res;
        }
        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       			=> 'upgrade_recommend_config',
                'new_config_data'   	=> $res['data']['new_config_data'],
                'recommend_config_id' 	=> $param['recommend_config_id'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'on_demand_flow_price' => $res['data']['on_demand_flow_price'],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
            'renew_price_difference_client_level_discount' => $res['data']['renew_price_difference_client_level_discount'],
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2024-04-30
     * @title 模拟物理机运行
     * @desc  模拟物理机运行
     * @author hh
     * @version v1
     * @param   int param.simulate_physical_machine - 模拟物理机运行(0=关闭,1=开启)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function simulatePhysicalMachine($param)
    {
        if($this->isClient){
            // 检查是否启用模拟物理机功能
            $config = ConfigModel::where('product_id', $this->hostModel['product_id'])->find();
            if(empty($config) || $config['simulate_physical_machine_enable'] != 1){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_simulate_physical_machine_not_enabled')];
            }
        }

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->simulatePhysicalMachine($param);
        }else{
            $res = $this->idcsmartCloud->cloudModify($this->id, [
                'simulate_physical_machine' => $param['simulate_physical_machine'],
            ]);
        }
        $switch = [
            lang_plugins('switch_off'),
            lang_plugins('switch_on'),
        ];
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
            ];

            $description = lang_plugins('log_mf_cloud_simulate_physical_machine_success', [
                '{hostname}'=> $this->hostModel['name'],
                '{switch}'	=> $switch[$param['simulate_physical_machine']],
            ]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('update_failed'),
            ];
            
            if($this->isClient){
                $description = lang_plugins('log_mf_cloud_simulate_physical_machine_fail', [
                    '{hostname}'=> $this->hostModel['name'],
                    '{switch}'	=> $switch[$param['simulate_physical_machine']],
                ]);
            }else{
                $description = lang_plugins('log_mf_cloud_simulate_physical_machine_fail_with_reason', [
                    '{hostname}'=> $this->hostModel['name'],
                    '{switch}'  => $switch[$param['simulate_physical_machine']],
                    '{reason}'  => $res['msg'],
                ]);
                $result['msg'] = $res['msg'];
            }
        }
        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
     * 时间 2024-05-11
     * @title IPv6列表
     * @desc  IPv6列表
     * @author hh
     * @version v1
     * @param int param.page 1 页数
     * @param int param.limit 20 每页条数
     * @return string list[].ipv6 - IPv6地址
     * @return int count - 总条数
     */
    public function ipv6List($param)
    {
        $param['page'] = $param['page']>0 ? $param['page'] : 1;
        $param['limit'] = $param['limit']>0 ? $param['limit'] : 20;

        $data = [];
        $count = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $list = $this->downstreamHostLogic->ipv6List($param);

            $data = $list['data']['list'] ?? [];
            $count = $list['data']['count'] ?? 0;
        }else{
            if($this->id > 0){
                // 获取当前所有IP
                $list = $this->idcsmartCloud->cloudIpv6($this->id, ['page'=>$param['page'], 'per_page'=>$param['limit']]);
                if(isset($list['data']['data'])){
                    foreach($list['data']['data'] as $v){
                        $data[] = [
                            'ipv6' => $v['ipv6'],
                        ];
                    }
                }
                
                $count = $list['data']['meta']['total'] ?? 0;
            }
        }
        return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2024-12-20
     * @title 下载RDP
     * @desc  下载RDP
     * @author  hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.content - 下载RDP内容
     * @return  string data.name - 下载文件名
     */
    public function downloadRdp()
    {
        $imageGroupId = ImageModel::where('id', $this->hostLinkModel['image_id'])->value('image_group_id');
        if(!empty($imageGroupId)){
            $ImageGroupModel = new ImageGroupModel();
            $imageGroup = $ImageGroupModel->find($imageGroupId);

            $isWindows = $ImageGroupModel->isWindows($imageGroup);
            if(!$isWindows){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_only_windows_can_do_this') ];
            }
        }

        // 获取所有设置
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];

        if($config['manual_manage']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $HostIpModel = new HostIpModel();
                $hostIp = $HostIpModel->getHostIp($this->hostModel['id']);
                $ip = $hostIp['dedicate_ip'];

                $port = HostAdditionModel::where('host_id', $this->hostModel['id'])->value('port');
                if(!empty($port) && $port != 3306){
                    $ip = $ip.':'.$port;
                }

                $template = 'screen mode id:i:1
use multimon:i:0
desktopwidth:i:1920
desktopheight:i:1200
session bpp:i:32
winposstr:s:0,1,1082,451,3024,1707
compression:i:1
keyboardhook:i:2
audiocapturemode:i:0
videoplaybackmode:i:1
connection type:i:7
networkautodetect:i:1
bandwidthautodetect:i:1
displayconnectionbar:i:1
enableworkspacereconnect:i:0
disable wallpaper:i:0
allow font smoothing:i:0
allow desktop composition:i:0
disable full window drag:i:1
disable menu anims:i:1
disable themes:i:0
disable cursor setting:i:0
bitmapcachepersistenable:i:1
username:s:administrator
full address:s:{ip}
audiomode:i:1
redirectprinters:i:1
redirectcomports:i:0
redirectsmartcards:i:1
redirectclipboard:i:1
redirectposdevices:i:0
autoreconnection enabled:i:1
authentication level:i:0
prompt for credentials:i:0
negotiate security layer:i:1
remoteapplicationmode:i:0
alternate shell:s:
shell working directory:s:
gatewayhostname:s:
gatewayusagemethod:i:4
gatewaycredentialssource:i:4
gatewayprofileusagemethod:i:0
promptcredentialonce:i:0
gatewaybrokeringtype:i:0
use redirection server name:i:0
rdgiskdcproxy:i:0
kdcproxyname:s:
drivestoredirect:s:';
                $content = str_replace('{ip}', $ip, $template);
            }else{
                $res = [
                    'status' => 400,
                    'msg'    => lang_plugins('fail_message')
                ];
            }       
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->downloadRdp();

                $content = $res['data']['content'] ?? '';
            }else{
                $res = $this->idcsmartCloud->downloadRdp($this->id);

                $content = $res['content'] ?? '';
            }
        }
        
        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
                'data'   => [
                    'content' => $content,
                    'name'    => $this->hostModel['name'].'.rdp',
                ],
            ];
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('fail_message'),
            ];
        }
        return $result;
    }

    /**
     * @时间 2025-01-08
     * @title 是否可以续费
     * @desc  是否可以续费,检查是否还有空闲显卡
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function whetherRenew()
    {
        $result = ['status'=>200, 'msg'=>lang_plugins('success_message') ];
        if($this->hostModel['status'] != 'Suspended'){
            return $result;
        }
        $configData = json_decode($this->hostLinkModel['config_data'], true);
        $configData['due_not_free_gpu'] = $configData['due_not_free_gpu'] ?? 0;

        // 没有GPU/不自动释放不检查
        if(empty($configData['gpu_num']) || $configData['due_not_free_gpu'] == 1){
            return $result;
        }
        if($this->downstreamHostLogic->isDownstream()){
            $result = $this->downstreamHostLogic->whetherRenew();
        }else{
            $res = $this->idcsmartCloud->cloudHardwareThrough($this->id);
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
                    $dataCenter = DataCenterModel::find($this->hostLinkModel['data_center_id']);
                    if(empty($dataCenter)){
                        return $result;
                    }
                    $res = $this->idcsmartCloud->getFreeGpu([
                        'type'  => $dataCenter['cloud_config'],
                        'id'    => $dataCenter['cloud_config_id'],
                        'num'   => $configData['gpu_num'],
                    ]);
                    if(empty($res['data']['data'])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_data_center_gpu_not_enough') ];
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * @时间 2025-01-10
     * @title 检查迁移任务是否完成
     * @desc  检查迁移任务是否完成
     * @author hh
     * @version v1
     */
    public function checkMigrateTask()
    {
        $taskId = $this->hostLinkModel['migrate_task_id'];
        if(!empty($taskId)){
            $taskDetail = $this->idcsmartCloud->taskDetail($taskId);

            // 可以同步IP
            if($taskDetail['status'] == 400 || !in_array($taskDetail['data']['status'], [0,1])){
                $this->hostLinkModel->where('host_id', $this->hostModel['id'])->update(['migrate_task_id'=>0]);

                $param = [
                    'host_id' => $this->hostModel['id'],
                    'id'      => $this->hostLinkModel['rel_id'],
                ];

                $this->hostLinkModel->syncIp($param, $this->idcsmartCloud);

                upstream_sync_host($this->hostModel['id'], 'update_host');
            }
        }
    }



    /**
     * 时间 2022-09-25
     * @title 计算套餐产品配置升级价格
     * @desc  计算套餐产品配置升级价格
     * @author hh
     * @version v1
     * @param   int param.ip_num - IPv4数量
     * @param   int param.ipv6_num - IPv6数量
     * @param   int param.flow - 流量
     * @param   int param.bw - 带宽
     * @param   int param.peak_defence - 防御峰值
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  array data.new_config_data - 新的配置记录
     * @return  int data.new_config_data.cpu.value - CPU
     * @return  string data.new_config_data.cpu.price - 价格
     * @return  string data.new_config_data.cpu.other_config.advanced_cpu - 智能CPU规则ID
     * @return  string data.new_config_data.cpu.other_config.cpu_limit - CPU限制
     * @return  int data.new_config_data.memory.value - 内存
     * @return  string data.new_config_data.memory.price - 价格
     * @return  int data.new_config_data.bw.value - 带宽
     * @return  string data.new_config_data.bw.price - 价格
     * @return  string data.new_config_data.bw.other_config.in_bw - 流入带宽
     * @return  string data.new_config_data.bw.other_config.advanced_bw - 智能带宽规则ID
     * @return  int data.new_config_data.flow.value - 流量
     * @return  string data.new_config_data.flow.price - 价格
     * @return  int data.new_config_data.flow.other_config.in_bw - 入站带宽
     * @return  int data.new_config_data.flow.other_config.out_bw - 出站带宽
     * @return  int data.new_config_data.flow.other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string data.new_config_data.flow.other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     * @return  int data.new_config_data.defence.value - 防御峰值
     * @return  string data.new_config_data.defence.price - 价格
     */
    public function calPackageConfigPrice($param)
    {
        // 套餐才能使用该接口
        if(empty($this->hostLinkModel['recommend_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }
        bcscale(2);
        $productId = $this->hostModel['product_id'];
        $hostId    = $this->hostModel['id'];
        $diffTime  = $this->hostModel['due_time'] - time();

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        $newConfigData = [];

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
        if(empty($duration)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
        }
        $OptionModel = new OptionModel();

        $oldPrice = 0;  // 老价格
        $price = 0;     // 新价格
        $description = []; // 描述

        // 检查之前的线路是否还存在
        $line = LineModel::where('id', $configData['line']['id'])->find();
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_cannot_upgrade_line_not_found')];
        }
        $recommendConfig = RecommendConfigModel::find($this->hostLinkModel['recommend_config_id']);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_cannot_upgrade_recommend_config_not_found') ];
        }
        if($line['ip_enable'] == 1 && $recommendConfig['ipv4_num_upgrade'] == 1 && isset($param['ip_num']) && is_numeric($param['ip_num'])){
            // IP变动才计算
            $oldIpNum = isset($configData['ip']['value']) ? $configData['ip']['value']+1 : $configData['recommend_config']['ip_num'];
            if($oldIpNum != $param['ip_num']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ip_num_error') ];
                }

                $newConfigData['ip'] = [
                    'value' => $param['ip_num'] - 1,
                ];

                // 匹配当前价格
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $oldIpNum, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? '0');
                $price = bcadd($price, $optionDurationPrice['price'] ?? '0');

                $description[] = lang_plugins('mf_cloud_upgrade_ip_num', [
                    '{old}' => $oldIpNum,
                    '{new}' => $param['ip_num'],
                ]);
            }
        }
        // 仅当经典网络可以操作IPv6
        if($line['ipv6_enable'] == 1 && $configData['network_type'] == 'normal' && $recommendConfig['ipv6_num_upgrade'] == 1 && isset($param['ipv6_num']) && is_numeric($param['ipv6_num'])){
            $oldIpv6Num = $configData['ipv6_num'] ?? 0;
            if($oldIpv6Num != $param['ipv6_num']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $param['ipv6_num'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ipv6_num_not_found') ];
                }

                $newConfigData['ipv6_num'] = $param['ipv6_num'];

                // 匹配当前价格
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $oldIpv6Num, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? '0');
                $price = bcadd($price, $optionDurationPrice['price'] ?? '0');

                $description[] = lang_plugins('mf_cloud_upgrade_ipv6_num', [
                    '{old}' => $oldIpv6Num,
                    '{new}' => $param['ipv6_num'],
                ]);
            }
        }
        // 线路存在的情况
        if($line['bill_type'] == 'bw'){
            // 获取带宽周期价格
            if($recommendConfig['bw_upgrade'] == 1 && isset($param['bw']) && !empty($param['bw']) && $param['bw'] != $configData['bw']['value']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('bw_error') ];
                }

                $newConfigData['bw'] = [
                    'value' => $param['bw'],
                    'other_config' => $optionDurationPrice['option']['other_config'],
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $configData['bw']['value'] ?? -1, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
                $price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

                $description[] = sprintf("%s: %d => %d", lang_plugins('bw'), $configData['bw']['value'], $param['bw']);
            }
        }else if($line['bill_type'] == 'flow'){
            // 获取流量周期价格
            if($recommendConfig['flow_upgrade'] == 1 && isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0 && $param['flow'] != $configData['flow']['value']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                }
                
                $newConfigData['flow'] = [
                    'value' => $param['flow'],
                    'other_config' => $optionDurationPrice['option']['other_config'],
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $configData['flow']['value'] ?? -1, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
                $price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

                $description[] = sprintf("%s: %d => %d", lang_plugins('flow'), $configData['flow']['value'], $param['flow']);
            }
        }
        // 防护
        if($recommendConfig['defence_upgrade'] == 1 && $line['defence_enable'] == 1 && isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0 && $param['peak_defence'] != ($configData['defence']['value'] ?? 0)){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
            }

            $newConfigData['defence'] = [
                'value' => $param['peak_defence'],
            ];

            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $configData['defence']['value'] ?? 0, $duration['id']);

            $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            $price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            $description[] = sprintf("%s: %d => %d", lang_plugins('mf_cloud_recommend_config_peak_defence'), $configData['defence']['value'] ?? 0, $param['peak_defence']);
        }
        
        if(empty($newConfigData)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_change_config')];
        }
        
        // 计算价格系数
        if($duration['price_factor'] != 1){
            $oldPrice = bcmul($oldPrice, $duration['price_factor']);
            $price = bcmul($price, $duration['price_factor']);
        }

        $description = implode("\r\n", $description);
        $priceDifference = bcsub($price, $oldPrice);
        $basePrice = bcadd($this->hostModel['base_price'],$priceDifference,2);

        // 验证价格不能低于当前套餐价格
        $recommendConfigPrice = PriceModel::where('product_id', $productId)
                            ->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)
                            ->where('rel_id', $this->hostLinkModel['recommend_config_id'])
                            ->where('duration_id', $duration['id'])
                            ->value('price');
        $recommendConfigPrice = bcmul($recommendConfigPrice, $duration['price_factor']);
        
        if($basePrice < $recommendConfigPrice){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_cannot_change_to_this_config') ];
        }
        if($this->hostModel['billing_cycle_time']>0){
            $price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
        }else{
            $price = $priceDifference;
        }

        // 下游
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        // if($isDownstream){
        //     $DurationModel = new DurationModel();
        //     $price = $DurationModel->downstreamSubClientLevelPrice([
        //         'product_id' => $productId,
        //         'client_id'  => $this->hostModel['client_id'],
        //         'price'      => $price,
        //     ]);
        //     $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
        //         'product_id' => $productId,
        //         'client_id'  => $this->hostModel['client_id'],
        //         'price'      => $priceDifference,
        //     ]);
        //     // 返给下游的基础价格
        //     $basePrice = $DurationModel->downstreamSubClientLevelPrice([
        //         'product_id' => $productId,
        //         'client_id'  => $this->hostModel['client_id'],
        //         'price'      => $basePrice,
        //     ]);
        // }

        // 计算用户等级折扣（仅非下游）
        $clientLevelDiscount = '0.00';
        $renewPriceDifferenceClientLevelDiscount = '0.00';
        $orderItem = [];
        if(!$isDownstream){
            $clientLevel = $this->getClientLevel([
                'product_id'    => $productId,
                'client_id'     => $this->hostModel['client_id'],
            ]);
            if(!empty($clientLevel)){
                // 计算实际折扣金额
                $discount = bcdiv($price*$clientLevel['discount_percent'], 100, 2);
                $renewDiscount = bcdiv($priceDifference*$clientLevel['discount_percent'], 100, 2);
                
                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => min(-$discount, 0),
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        // '{host_id}' => $this->hostModel['id'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
                
                $clientLevelDiscount = amount_format($discount, 2);
                $renewPriceDifferenceClientLevelDiscount = amount_format($renewDiscount, 2);
                
                $price = bcsub($price, $discount, 2);
                $priceDifference = bcsub($priceDifference, $renewDiscount, 2);
            }
        }

        $realPriceDifference = $price;

        $price = max(0, $price);
        $price = amount_format($price);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price'                                     => $price,
                'description'                               => $description,
                'price_difference'                          => $realPriceDifference,
                'renew_price_difference'                    => $priceDifference,
                'new_config_data'                           => $newConfigData,
                'base_price'                                => $basePrice,
                'client_level_discount'                     => $clientLevelDiscount,
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount,
                'discount'                                  => $clientLevelDiscount,
                'order_item'                                => $orderItem
            ]
        ];
        return $result;
    }

    /**
     * 时间 2025-01-13
     * @title 生成套餐产品配置升级订单
     * @desc  生成套餐产品配置升级订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.ip_num - IPv4数量
     * @param   int param.ipv6_num - IPv6数量
     * @param   int param.flow - 流量
     * @param   int param.bw - 带宽
     * @param   int param.peak_defence - 防御峰值
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     * @return  string data.amount - 金额
     */
    public function createPackageConfigOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $param['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_config'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        // $param['check_limit_rule'] = 1;
        $res = $this->calPackageConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'              => 'upgrade_package_config',
                'new_config_data'   => $res['data']['new_config_data'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'order_item'  => $res['data']['order_item'] ?? [],
            'discount'    => $res['data']['discount'] ?? '0.00',
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * @时间 2025-01-13
     * @title 是否支持套餐升降级之间升降级
     * @desc  是否支持套餐升降级之间升降级
     * @author hh
     * @version v1
     * @param   RecommendConfigModel recommendConfig - 套餐模型数据 require
     * @return  bool
     */
    public function supportRecommendConfigUpgrade($recommendConfig)
    {
        $line = LineModel::find($recommendConfig['line_id']);
        if(empty($line)){
            return false;
        }
        // 开启同步后不能套餐升降级
        if($line['sync_firewall_rule'] == 1){
            return false;
        }
        // $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 检查套餐配置是否变更
        // $ipNum = isset($configData['ip']['value']) ? $configData['ip']['value']+1 : $recommendConfig['ip_num'];
        // $ipv6Num = isset($configData['ipv6_num']) ? $configData['ipv6_num'] : $recommendConfig['ipv6_num'];
        // $defence = $configData['defence']['value'] ?? 0;
        // if($line['bill_type'] == 'bw'){
        //     $bw = $configData['bw']['value'] ?? $recommendConfig['bw'];

        //     if($ipNum != $recommendConfig['ip_num'] || $ipv6Num != $recommendConfig['ipv6_num'] || $defence != $recommendConfig['peak_defence'] || $bw != $recommendConfig['bw'] ){
        //         return false;
        //     }
        // }else{
        //     $flow = $configData['flow']['value'] ?? $recommendConfig['flow'];

        //     if($ipNum != $recommendConfig['ip_num'] || $ipv6Num != $recommendConfig['ipv6_num'] || $defence != $recommendConfig['peak_defence'] || $flow != $recommendConfig['flow'] ){
        //         return false;
        //     }
        // }
        return true;
    }

    /**
     * 时间 2025-01-15
     * @title 计算升级防御价格
     * @desc  计算升级防御价格
     * @author hh
     * @version v1
     * @param   string param.ip - IP
     * @param   string param.peak_defence - 防御峰值
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     */
    public function calDefencePrice($param)
    {
        bcscale(2);
        $productId = $this->hostModel['product_id'];
        $hostId    = $this->hostModel['id'];
        $diffTime  = $this->hostModel['due_time'] - time();

        $parentHostId = $this->hostLinkModel['parent_host_id'];

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
        if(empty($duration)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
        }
        $OptionModel = new OptionModel();

        $oldPrice = 0;
        $price = 0;
        $defence = [];

        // 检查之前的线路是否还存在
        $line = LineModel::where('id', $configData['line']['id'])->find();
        if(empty($line) || $line['defence_enable'] == 0 || $line['sync_firewall_rule'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_host_ip_defence_upgrade')];
        }
        // IP段
        if(!empty($param['ip']) && !empty($param['peak_defence'])){
            $HostIpModel = new HostIpModel();
            // 父产品ID
            if (!empty($parentHostId)){
                $hostIp = $HostIpModel->where('host_id', $parentHostId)->find();
            }else{
                $hostIp = $HostIpModel->where('host_id', $this->hostModel['id'])->find();
            }

            if(empty($hostIp)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_host_ip_defence_upgrade')];
            }
            $dedicateIp = $hostIp['dedicate_ip'];
            $assignIp = array_filter(explode(',', $hostIp['assign_ip']));
            if($dedicateIp!=$param['ip'] && !in_array($param['ip'], $assignIp)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_host_ip_defence_upgrade')];
            }

            $oldIpDefence = IpDefenceModel::where('host_id', $hostId)->where('ip', $param['ip'])->find();
            if (class_exists('\addon\aodun_firewall\model\AodunFirewallHostIpModel')){
                $AodunFirewallHostIpModel = new \addon\aodun_firewall\model\AodunFirewallHostIpModel();
                $tmp = $AodunFirewallHostIpModel->where('host_ip', $param['ip'])->find();
                $oldIpDefence['defence'] = 'aodun_firewall_'.$tmp['set_meal_id'];
            }else{
                $AodunFirewallAgentHostIpModel = new \addon\aodun_firewall_agent\model\AodunFirewallAgentHostIpModel();
                $tmp = $AodunFirewallAgentHostIpModel->where('host_ip', $param['ip'])->find();
                $oldIpDefence['defence'] = 'aodun_firewall_'.$tmp['set_meal_id'];
            }
            if(!empty($oldIpDefence) && $oldIpDefence['defence'] == $param['peak_defence']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_change_config')];
            }
            // TODO 传了周期
            if (!empty($oldIpDefence['defence']) && $oldIpDefence['defence']==$line['order_default_defence']){
                if (!empty($param['duration_id'])){
                    $duration = DurationModel::where('product_id', $productId)->where('id',$param['duration_id'])->find();
                    if (empty($duration)){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
                    }
                }
            }

            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
            }

            $ConfigModel = new ConfigModel();
            $rule = $ConfigModel->getFirewallDefenceRule([
                'product_id'        => $productId,
                'firewall_type'     => $optionDurationPrice['option']['firewall_type'],
                'defence_rule_id'   => $optionDurationPrice['option']['defence_rule_id'],
            ]);
            if(empty($rule)){
                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
            }
            $option = $optionDurationPrice['option'];

            $old = '';
            if(!empty($oldIpDefence)){
                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $oldIpDefence['defence'], $duration['id']);
                if(!empty($currentOptionDurationPrice['option'])){
                    $oldRule = $ConfigModel->getFirewallDefenceRule([
                        'product_id'        => $productId,
                        'firewall_type'     => $currentOptionDurationPrice['option']['firewall_type'],
                        'defence_rule_id'   => $currentOptionDurationPrice['option']['defence_rule_id'],
                    ]);

                    $old = $oldRule['defense_peak'] ?? '';
                }

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            }

            $price = bcadd(strval($price), $optionDurationPrice['price']);

            $description = lang_plugins('mf_cloud_host_ip_defence_upgrade_desc', [
                '{ip}'  => $param['ip'],
                '{old}' => $old,
                '{new}' => $rule['defense_peak'],
            ]);

            $defence = ['value' => $option['value'], 'firewall_type' => $option['firewall_type'], 'defence_rule_id' => $option['defence_rule_id']];
        }
        if(empty($defence)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_change_config')];
        }

        // 若当前ip为默认防御，需返回周期，以及周期原价
        $upgradeWithDuration = false;
        $isDefaultDefence = $param['peak_defence']==$line['order_default_defence'];
        $durationTime = 0;
        if (!empty($oldIpDefence['defence']) && $oldIpDefence['defence']==$line['order_default_defence']){
            $upgradeWithDuration = true;
            if($duration['unit'] == 'month'){
                $durationTime = strtotime('+ '.$duration['num'].' month') - time();
            }else if($duration['unit'] == 'day'){
                $durationTime = $duration['num'] * 3600 * 24;
            }else if($duration['unit'] == 'hour'){
                $durationTime = $duration['num'] * 3600;
            }
            $DurationModel = new DurationModel();
            $result = $DurationModel->getAllDurationPrice([
                'id' => $this->hostModel['product_id'],
                'line_id' => $line['id'],
                'peak_defence' => $param['peak_defence'],
                'ip_num' => 0,
            ],false,true);
            // 计算价格系数
            $oldPrice = 0;
            if($duration['price_factor'] != 1){
                $oldPrice = bcmul($oldPrice, $duration['price_factor']);
                $price = bcmul($price, $duration['price_factor']);
            }
            $priceDifference = bcsub($price, $oldPrice);
            $price = $priceDifference;
            $basePrice = bcadd($this->hostModel['base_price'],$priceDifference,2);
        }else{
            // 计算价格系数
            if($duration['price_factor'] != 1){
                $oldPrice = bcmul($oldPrice, $duration['price_factor']);
                $price = bcmul($price, $duration['price_factor']);
            }

            $priceDifference = bcsub($price, $oldPrice);
            if($this->hostModel['billing_cycle_time']>0){
                $price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }else{
                $price = $priceDifference;
            }

            $basePrice = bcadd($this->hostModel['base_price'],$priceDifference,2);
        }

        // 下游
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        if($isDownstream && $priceAgent){
            $DurationModel = new DurationModel();
            $price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
            // 返给下游的基础价格
            $basePrice = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $basePrice,
            ]);
        }

        $realPriceDifference = $price;

        $price = max(0, $price);
        $price = amount_format($price);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price'                     => $price,
                'description'               => $description ?? '',
                'price_difference'          => $realPriceDifference,
                'renew_price_difference'    => $priceDifference,
                'base_price'                => $basePrice,
                'defence'                   => $defence,
                'durations'                 => $result['data']??[],
                'upgrade_with_duration'     => $upgradeWithDuration,
                'due_time'                  => $upgradeWithDuration?$durationTime:0,
                'duration_id'               => $upgradeWithDuration?$duration['id']:0,
                'duration'                  => $upgradeWithDuration?$duration:[],
                'is_default_defence'        => $isDefaultDefence,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2025-01-15
     * @title 生成升级防御订单
     * @desc  生成升级防御订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   string param.ip - IP
     * @param   string param.peak_defence - 防御峰值
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.id - 订单ID
     */
    public function createDefenceOrder($param)
    {
        $parentHostId = $this->hostLinkModel['parent_host_id'];
        // 触发父产品钩子
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $parentHostId,
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_config'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        
        $param['is_downstream'] = 0;
        $res = $this->calDefencePrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'               => $param['id'],
            'client_id'             => get_client_id(),
            'type'                  => 'upgrade_config',
            'amount'                => $res['data']['price'],
            'description'           => $res['data']['description'],
            'price_difference'      => $res['data']['price_difference'],
            'renew_price_difference'=> $res['data']['renew_price_difference'],
            'base_price'            => $res['data']['base_price'],
            'upgrade_refund'        => 0,
            'config_options'        => [
                'type'              => 'upgrade_defence',
                'ip'                => $param['ip'],
                'peak_defence'      => $param['peak_defence'],
                'defence'           => $res['data']['defence'],
                'upgrade_with_duration'     => $res['data']['upgrade_with_duration'],
                'due_time'                  => $res['data']['due_time'],
                'duration'                  => $res['data']['duration'],
                'is_default_defence'        => $res['data']['is_default_defence'],
            ],
            'customfield'           => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2025-03-18
     * @title 获取默认带宽分组IP
     * @desc  获取默认带宽分组IP,用于删除IP
     * @author hh
     * @version v1
     * @return  int list[].id - IP段ID
     * @return  string list[].ip_name - IP段名称
     * @return  array list[].ip - IP段IP列表
     * @return  int list[].ip[].id - IP段IPID
     * @return  string list[].ip[].ip - IP段IP地址
     * @return  int count - 总条数
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     */
    public function getDefaultBwGroupIp()
    {
        $cloudDetail = $this->idcsmartCloud->cloudDetail($this->id);
        if($cloudDetail['status'] != 200){
            return ['list'=>[], 'count'=>0, 'network_type'=>'normal' ];
        }
        $bwGroupIp = $cloudDetail['data']['default_bw_group']['id'];

        $bwList = $this->idcsmartCloud->bwList([
            'page'		=> 1,
            'per_page'	=> 50,
            'cloud'		=> $this->id,
        ]);

        $list = [];
        if($bwList['status'] == 200){
            foreach($bwList['data']['data'] as $v){
                if($v['id'] == $bwGroupIp){
                    foreach($v['ip'] as $vv){
                        if(!isset($list[ $vv['ip_segment_id'] ])){
                            $list[ $vv['ip_segment_id'] ] = [
                                'id'		=> $vv['ip_segment_id'],
                                'ip_name'	=> $vv['ip_name'],
                                'ip'		=> [],
                            ];
                        }
                        $list[ $vv['ip_segment_id'] ]['ip'][] = [
                            'id'	=> $vv['id'],
                            'ip'	=> $vv['ip'],
                        ];
                    }
                }
            }
        }
        $list = array_values($list);
        $count = count($list);

        return ['list'=>$list, 'count'=>$count, 'network_type'=>$cloudDetail['data']['network_type'] ];
    }

    /**
     * 时间 2025-03-18
     * @title 删除IP
     * @desc  删除IP
     * @author hh
     * @version v1
     * @param   array param.ip_id - IPID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function deleteIp($param)
    {
        $result = $this->idcsmartCloud->floatIpDelete($this->id, [
            'ip'	=> $param['ip_id'],
            'type'	=> 'id',
        ]);
        if($result['status'] == 200){
            // 不影响数量
            // $configData = json_decode($this->hostLinkModel['config_data'], true);
            // if(!empty($configData['ip'])){
            // 	$configData['ip'] = [
            // 		'value' => max($configData['ip']['value'] - count($param['ip_id']), 0),
            // 		'price'	=> 0,
            // 	];
            // 	$this->hostLinkModel->where('id', $this->hostLinkModel['id'])->update([
            // 		'config_data'	=> json_encode($configData),
            // 	]);
            // }

            // 同步IP
            $ip = $this->hostLinkModel->syncIp([
                'host_id'	=> $this->hostModel['id'],
                'id'		=> $this->id
            ], $this->idcsmartCloud, NULL, false);
            
            if(!empty($result['data']['ip'])){
                $deleteIp = array_column($result['data']['ip'], 'ip');
            }else{
                $deleteIP = $param['ip_id'];
            }
            $description = lang_plugins('log_mf_cloud_delete_ip_success', [
                '{hostname}'	=> 'host#'.$this->hostModel['id'].'#'.$this->hostModel['name'].'#',
                '{ip}'			=> implode(',', $deleteIp),
            ]);
            active_log($description, 'host', $this->hostModel['id']);

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
                $this->ipChange([
                    'ips'	=> $ips,
                ]);
                upstream_sync_host($this->hostModel['id'], 'update_host','upgrade_ip_num');
            }

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('delete_success'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2025-03-18
     * @title 获取空闲IP
     * @desc  获取空闲IP,用于添加IP
     * @author hh
     * @version v1
     * @return  int list[].id - IP段ID
     * @return  string list[].ip_name - IP段名称
     * @return  array list[].ip - IP段IP列表
     * @return  int list[].ip[].id - IP段IPID
     * @return  string list[].ip[].ip - IP段IP地址
     * @return  int count - 总条数
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     */
    public function getFreeIp()
    {
        $cloudDetail = $this->idcsmartCloud->cloudDetail($this->id);
        if($cloudDetail['status'] != 200){
            return ['list'=>[], 'count'=>0, 'network_type'=>'normal'];
        }
        $freeIp = $this->idcsmartCloud->getFreeIp([
            'node'		=> $cloudDetail['data']['node_id'],
            'hostid'	=> $this->id,
            'type'		=> 'cloud',
            'vlan'		=> $cloudDetail['data']['vlan'],
        ]);

        $list = $freeIp['data'] ?? [];
        $count = count($list);
        return ['list'=>$list, 'count'=>$count, 'network_type'=>$cloudDetail['data']['network_type'] ];
    }

    /**
     * 时间 2025-03-18
     * @title 添加IP
     * @desc  添加IP
     * @author hh
     * @version v1
     * @param   array param.ip_id - IPID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function addIp($param)
    {
        $cloudDetail = $this->idcsmartCloud->cloudDetail($this->id);
        if($cloudDetail['status'] != 200){
            return $cloudDetail;
        }
        $bwGroupIp = $cloudDetail['data']['default_bw_group']['id'];

        $result = $this->idcsmartCloud->floatIpAdd($this->id, [
            'bw_group'	=> $bwGroupIp,
            'ip'		=> $param['ip_id'],
            'ip_type'	=> 'normal',
        ]);
        if($result['status'] == 200){
            // 不影响数量
            // $configData = json_decode($this->hostLinkModel['config_data'], true);
            // $configData['ip'] = [
            // 	'value' => ($configData['ip']['value'] ?? 0) + count($param['ip_id']),
            // 	'price'	=> 0,
            // ];
            // $this->hostLinkModel->where('id', $this->hostLinkModel['id'])->update([
            // 	'config_data'	=> json_encode($configData),
            // ]);

            // 同步IP
            $ip = $this->hostLinkModel->syncIp([
                'host_id'	=> $this->hostModel['id'],
                'id'		=> $this->id
            ], $this->idcsmartCloud, NULL, false);

            if(!empty($result['data']['ip'])){
                $addIp = array_column($result['data']['ip'], 'ip');
            }else{
                $addIp = $param['ip_id'];
            }

            $description = lang_plugins('log_mf_cloud_add_ip_success', [
                '{hostname}'	=> 'host#'.$this->hostModel['id'].'#'.$this->hostModel['name'].'#',
                '{ip}'			=> implode(',', $addIp),
            ]);
            active_log($description, 'host', $this->hostModel['id']);

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
                $this->ipChange([
                    'ips'	=> $ips,
                ]);
                upstream_sync_host($this->hostModel['id'], 'update_host','upgrade_ip_num');
            }

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2025-03-18
     * @title 获取可用IP
     * @desc  获取可用IP,用于变更IP
     * @author hh
     * @version v1
     * @return  int list[].id - IP段ID
     * @return  string list[].ip_name - IP段名称
     * @return  array list[].ip - IP段IP列表
     * @return  int list[].ip[].id - IP段IPID
     * @return  string list[].ip[].ip - IP段IP地址
     * @return  int list[].ip[].use - 是否使用(1=当前使用IP)
     * @return  int count - 总条数
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     */
    public function getEnableIp()
    {
        $cloudDetail = $this->idcsmartCloud->cloudDetail($this->id);
        if($cloudDetail['status'] != 200){
            return ['list'=>[], 'count'=>0, 'network_type'=>'normal'];
        }
        $bwGroupIp = $cloudDetail['data']['default_bw_group']['id'];

        $freeIp = $this->idcsmartCloud->getFreeIp([
            'node'		=> $cloudDetail['data']['node_id'],
            'hostid'	=> $this->id,
            'type'		=> 'cloud',
            'vlan'		=> $cloudDetail['data']['vlan'],
        ]);

        $list = $freeIp['data'] ?? [];

        $bwList = $this->idcsmartCloud->bwList([
            'page'		=> 1,
            'per_page'	=> 50,
            'cloud'		=> $this->id,
        ]);
        $nowIpList = [];
        if($bwList['status'] == 200){
            foreach($bwList['data']['data'] as $v){
                if($v['id'] == $bwGroupIp){
                    foreach($v['ip'] as $vv){
                        // 排除弹性IP
                        if(isset($vv['uid']) && $vv['uid'] > 0){
                            continue;
                        }
                        if(!isset($nowIpList[ $vv['ip_segment_id'] ])){
                            $nowIpList[ $vv['ip_segment_id'] ] = [
                                'id'		=> $vv['ip_segment_id'],
                                'ip_name'	=> $vv['ip_name'],
                                'ip'		=> [],
                            ];
                        }
                        $nowIpList[ $vv['ip_segment_id'] ]['ip'][] = [
                            'id'	=> $vv['id'],
                            'ip'	=> $vv['ip'],
                            'use'	=> 1,
                        ];
                    }
                }
            }
        }
        
        foreach($list as $k=>$v){
            if(!empty($nowIpList[ $v['id'] ])){
                $list[$k]['ip'] = array_merge($nowIpList[ $v['id'] ]['ip'], $v['ip']);
                unset($nowIpList[ $v['id'] ]);
            }
        }
        if(!empty($nowIpList)){
            $list = array_merge(array_values($nowIpList), $list);
        }
        $count = count($list);

        return ['list'=>$list, 'count'=>$count, 'network_type'=>$cloudDetail['data']['network_type'] ];
    }

    /**
     * 时间 2025-03-18
     * @title ip变更后创建子产品，以及其他关联
     * @desc  ip变更后创建子产品，以及其他关联
     * @author wyh
     * @version v1
     * @param array param.ips - 变更后ip数组 require
     * @return array
     */
    public function ipChange($param)
    {
        $hostId = $this->hostModel['id'];
        $ips = $param['ips']??[];
        $agent = $param['agent']??false;
        $onlyCreate = $param['only_create']??false;
        $HostLinkModel = new HostLinkModel();
        $LineModel = new LineModel();
        $IpDefenceModel = new IpDefenceModel();
        $HostModel = new HostModel();
        $hostLink = $HostLinkModel->where('host_id',$hostId)->find();
        $configData = json_decode($hostLink['config_data'],true);
        $init = $param['init']??false; // 初始化
        if ($agent){
            $firewallType = $param['firewall_type']??'';
            $orderDefaultDefenceId = $param['default_defence_id']??0;
            $defaultDefence = $firewallType . '_' . $orderDefaultDefenceId;
        }elseif($init){
            $firewallType = $param['firewall_type']??'';
            $orderDefaultDefenceId = $param['default_defence_id']??0;
            $defaultDefence = $firewallType . '_' . $orderDefaultDefenceId;
        }else{
            // 查找线路默认防御
            $firewallType = 'aodun_firewall';
            $lineId = $configData['line']['id']??0;
            $line = $LineModel->where('id',$lineId)->find();
            if (empty($line)){
                return ['status'=>400,'msg'=>lang_plugins('line_not_found')];
            }
            if ($line['sync_firewall_rule']==0){
                return ['status'=>200,'msg'=>lang_plugins('success_message')];
            }
            $defaultDefence = $line['order_default_defence'];
            $orderDefaultDefenceId = str_replace($firewallType.'_','',$defaultDefence);
        }
        if (empty($firewallType)){
            return ['status'=>400,'msg'=>lang_plugins('line_not_found')];
        }
        // 获取已存在的子产品，以及对应的ip
        $subHostLinks = $HostLinkModel->where('parent_host_id',$hostId)
            ->select()
            ->toArray();
        $subHostIps = array_column($subHostLinks,'ip');
        $subHostIpMapId = array_column($subHostLinks,'host_id','ip');
        // 多的ip
        $externalIps = array_values(array_diff($ips,$subHostIps));
        // 少的ip
        if ($onlyCreate){
            $internalIps = [];
        }else{
            $internalIps = array_values(array_diff($subHostIps,$ips));
        }
        // 多的ip，创建或者替换原有子产品
        if (!empty($externalIps)){
            if (!empty($internalIps)){ // 少的ip非空
                // 当 多的ip数量 大于 少的ip数量，则替换原有子产品，并创建新的子产品
                if (count($externalIps)>count($internalIps)){
                    // 替换原有子产品
                    $hostIps = [];
                    $externalHostIps = [];
                    foreach ($internalIps as $i=>$internalIp){
                        // 更改为主产品周期
                        $subHostLink = $HostLinkModel->where('parent_host_id',$hostId)
                            ->where('ip',$internalIp)
                            ->find();
                        HostModel::where('id',$subHostLink['host_id']??0)->update([
                            'due_time' => $this->hostModel['due_time'],
                            'first_payment_amount' => 0,
                            'renew_amount' => 0,
                            'base_price' => 0,
                        ]);
                        $HostLinkModel->where('parent_host_id',$hostId)
                            ->where('ip',$internalIp)
                            ->update([
                                'ip' => $externalIps[$i],
                            ]);
                        $hostIps[$internalIp] = '';
                        // 更新防御ip
                        $IpDefenceModel->where('host_id', $subHostIpMapId[$internalIp])->update([
                            'ip' =>  $externalIps[$i],
                            'defence' => $defaultDefence,
                        ]);
                    }
                    if ($agent){
                        hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $orderDefaultDefenceId, 'host_ips' => $hostIps]);
                    }else{
                        hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>$hostIps,'default_defence_id'=>$orderDefaultDefenceId]);
                    }

                    // 未替换的ip
                    $externalIpsFilter = [];
                    foreach ($externalIps as $j=>$externalIp){
                        if ($j>=count($internalIps)){
                            $externalIpsFilter[] = $externalIp;
                        }
                        $externalHostIps[$externalIp] = '';
                    }
                    // 创建新的子产品
                    for ($k=0;$k<count($externalIpsFilter);$k++){
                        $this->createIpDefenceHost([
                            'ip' => $externalIpsFilter[$k],
                            'agent' => $agent,
                            'init' => $init,
                            'config_data' => $param['config_data']??[],
                            'firewall_type' => $firewallType,
                            'default_defence_id' => $orderDefaultDefenceId,
                        ]);
                    }
                    if ($agent){
                        hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $orderDefaultDefenceId, 'host_ips' => $externalHostIps]);
                    }else{
                        hook('firewall_set_meal_modify', ['type'=>$firewallType, 'set_meal_id'=>$orderDefaultDefenceId, 'host_ips'=>$externalHostIps]);
                    }
                }else{ // 当 多的ip数量 小于 少的ip数量，则替换原有子产品，并删除多余的子产品
                    $hostIps = [];
                    $externalHostIps = [];
                    foreach ($internalIps as $i=>$internalIp){
                        $hostIps[$internalIp] = '';
                        if (!empty($externalIps[$i])){
                            // 更改为主产品周期
                            $subHostLink = $HostLinkModel->where('parent_host_id',$hostId)
                                ->where('ip',$internalIp)
                                ->find();
                            HostModel::where('id',$subHostLink['host_id']??0)->update([
                                'due_time' => $this->hostModel['due_time'],
                                'first_payment_amount' => 0,
                                'renew_amount' => 0,
                                'base_price' => 0,
                            ]);
                            $HostLinkModel->where('parent_host_id',$hostId)
                                ->where('ip',$internalIp)
                                ->update([
                                    'ip' => $externalIps[$i],
                                ]);
                            $IpDefenceModel->where('host_id', $subHostIpMapId[$internalIp])->update([
                                'ip' =>  $externalIps[$i],
                                'defence' => $defaultDefence,
                            ]);
                            $externalHostIps[$externalIps[$i]] = '';
                        }else{
                            $hostIds = $HostLinkModel->where('parent_host_id',$hostId)
                                ->where('ip',$internalIp)
                                ->column('host_id');
                            $HostModel->whereIn('id',$hostIds)->delete();
                            UpstreamHostModel::whereIn('host_id',$hostIds)->delete();
                            $HostLinkModel->where('parent_host_id',$hostId)
                                ->where('ip',$internalIp)
                                ->delete();
                            $IpDefenceModel->where('host_id', $subHostIpMapId[$internalIp])->delete();
                        }
                    }
                    if ($agent){
                        hook('firewall_agent_set_meal_modify',['type'=>$firewallType,'host_ips'=>$hostIps,'set_meal_id'=>$orderDefaultDefenceId]);
                        hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $orderDefaultDefenceId, 'host_ips' => $externalHostIps]);
                    }else{
                        hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>$hostIps,'default_defence_id'=>$orderDefaultDefenceId]);
                        hook('firewall_set_meal_modify', ['type'=>$firewallType, 'set_meal_id'=>$orderDefaultDefenceId, 'host_ips'=>$externalHostIps]);
                    }
                }
            }else{
                $externalHostIps = [];
                // 创建新的子产品
                for ($k=0;$k<count($externalIps);$k++){
                    $this->createIpDefenceHost([
                        'ip' => $externalIps[$k],
                        'agent' => $agent,
                        'init' => $init,
                        'config_data' => $param['config_data']??[],
                        'firewall_type' => $firewallType,
                        'default_defence_id' => $orderDefaultDefenceId,
                    ]);
                }
                foreach ($externalIps as $externalIp){
                    $externalHostIps[$externalIp] = '';
                }
                if ($agent){
                    hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $orderDefaultDefenceId, 'host_ips' => $externalHostIps]);
                }else{
                    hook('firewall_set_meal_modify', ['type'=>$firewallType, 'set_meal_id'=>$orderDefaultDefenceId, 'host_ips'=>$externalHostIps]);
                }
            }
        }else{ // 无多的ip
            if (!empty($internalIps)){ // ip降级或者更换机器id：有少的ip，删除多余的子产品，并移动ip至默认防御
                $hostIds = $HostLinkModel->where('parent_host_id',$hostId)
                    ->whereIn('ip',$internalIps)
                    ->column('host_id');
                $HostModel->whereIn('id',$hostIds)->delete();
                UpstreamHostModel::whereIn('host_id',$hostIds)->delete();
                $HostLinkModel->where('parent_host_id',$hostId)
                    ->whereIn('ip',$internalIps)
                    ->delete();
                $hostIps = [];
                foreach ($internalIps as $internalIp){
                    $IpDefenceModel->where('host_id', $subHostIpMapId[$internalIp])->delete();
                    $hostIps[$internalIp] = '';
                }
                if ($agent){
                    hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $orderDefaultDefenceId, 'host_ips' => $hostIps]);
                }else{
                    hook('firewall_set_meal_delete',['type'=>$firewallType,'host_ips'=>$hostIps,'default_defence_id'=>$orderDefaultDefenceId]);
                }
            }
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function getClientLevel($param)
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
        $discount = [];
        if(!empty($plugin) && class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')){
            try{
                if(class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelProductGroupModel')){
                    $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                    $discount = $IdcsmartClientLevelModel->clientDiscount(['client_id' => $param['client_id'], 'product_id' => $param['product_id']]);
                }else{
                    $discount = IdcsmartClientLevelClientLinkModel::alias('aiclcl')
                        ->field('aicl.id,aicl.name,aiclpl.product_id,aiclpl.discount_percent')
                        ->leftJoin('addon_idcsmart_client_level aicl', 'aiclcl.addon_idcsmart_client_level_id=aicl.id')
                        ->leftJoin('addon_idcsmart_client_level_product_link aiclpl', 'aiclpl.addon_idcsmart_client_level_id=aicl.id')
                        ->where('aiclcl.client_id', $param['client_id'])
                        ->where('aiclpl.product_id', $param['product_id'])
                        ->where('aicl.discount_status', 1)
                        ->find();
                }
            }catch(\Exception $e){

            }
        }
        return $discount;
    }

    /**
     * 创建ip默认防御子产品
     * */
    private function createIpDefenceHost($param)
    {
        $agent = $param['agent']??false;
        $init = $param['init']??false;
        $serverId = $this->server['id']??0;
        $HostLinkModel = new HostLinkModel();
        $LineModel = new LineModel();
        $IpDefenceModel = new IpDefenceModel();
        $parentHostLink = $HostLinkModel->where('host_id',$this->hostModel['id'])->find();
        $configData = json_decode($parentHostLink['config_data'],true);
        // 查找线路默认防御
        if ($agent){
            $subConfigData = [];
            $baseConfigOptions = [];
            $price = 0;
            $defaultDefence = '';
            $basePrice = $price;
        }elseif ($init){
            $configData = $param['config_data']??[];
            $line = $configData['line']??[];
            $defaultDefence = $param['firewall_type'] . '_' . $param['default_defence_id'];
            $subConfigData = [];
            $subConfigData['duration'] = $configData['duration']??[];
            $subConfigData['line'] = $configData['line']??[];
            $subConfigData['defence'] = [
                'value' => $defaultDefence,
                'firewall_type' => $param['firewall_type'],
                'defence_rule_id' => $param['default_defence_id'],
            ];
            // 计算默认防御价格
            $duration = $configData['duration']??[];
            $OptionModel = new OptionModel();
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($this->hostModel['product_id'],
                OptionModel::LINE_DEFENCE,
                $line['id'],
                $defaultDefence,
                $duration['id']??0
            );
            $baseConfigOptions = [
                'peak_defence' => $defaultDefence,
            ];
            $price = bcmul($optionDurationPrice['price']??0,$duration['price_factor'],2);
            // 计算用户折扣
            $clientLevel = $this->getClientLevel([
                'product_id'    => $this->hostModel['product_id'],
                'client_id'     => $this->hostModel['client_id'],
            ]);
            $basePrice = $price;
            if (!empty($clientLevel)){
                $discount = bcdiv($price*$clientLevel['discount_percent'], 100, 2);
                $price = bcsub($price,$discount,2);
            }

        }else{
            $firewallType = $configData['defence']['defence']['firewall_type']??'';
            $lineId = $configData['line']['id']??0;
            $line = $LineModel->where('id',$lineId)->find();
            $defaultDefence = $line['order_default_defence'];
            $orderDefaultDefenceId = str_replace($firewallType.'_','',$defaultDefence);
            $subConfigData = [];
            $subConfigData['duration'] = $configData['duration']??[];
            $subConfigData['line'] = $configData['line']??[];
            $subConfigData['defence'] = [
                'value' => $defaultDefence,
                'firewall_type' => $firewallType,
                'defence_rule_id' => $orderDefaultDefenceId,
            ];
            // 计算默认防御价格
            $duration = $configData['duration']??[];
            $OptionModel = new OptionModel();
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($this->hostModel['product_id'],
                OptionModel::LINE_DEFENCE,
                $line['id'],
                $defaultDefence,
                $duration['id']??0
            );
            $baseConfigOptions = [
                'peak_defence' => $defaultDefence,
            ];
            $price = bcmul($optionDurationPrice['price']??0,$duration['price_factor'],2);
            $basePrice = $price;
        }

        $product = ProductModel::find($this->hostModel['product_id']);
        $time = time();
        $name = generate_host_name($this->hostModel['product_id']);
        $host = HostModel::create([
            'client_id' => $this->hostModel['client_id'],
            'order_id' => $this->hostModel['order_id'],
            'product_id' => $this->hostModel['product_id'],
            'server_id' => $this->hostModel['server_id']??$serverId,
            'name' => $name,
            'status' => 'Active',
            'first_payment_amount' => $price,
            'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $price : 0,
            'billing_cycle' => $product['pay_type'],
            'billing_cycle_name' => $this->hostModel['billing_cycle_name'],
            'billing_cycle_time' => $this->hostModel['billing_cycle_time'],
            'active_time' => $time,
            'due_time' => $this->hostModel['due_time'], // 跟随主产品到期时间
            'create_time' => $time,
            'downstream_info' => $downstreamInfo ?? '',
            'downstream_host_id' => $downstreamHostId ?? 0,
            'base_price' => $basePrice,
            'ratio_renew' => ProductModel::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
            'base_renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $price : 0,
            'base_config_options' => json_encode($baseConfigOptions),
            'is_ontrial' => 0,
            'first_payment_ontrial' => 0,
            'is_sub' => 1,
        ]);
        if ($agent){
            $upstreamHost = UpstreamHostModel::where('host_id',$this->hostModel['id'])->find();
            if (!empty($upstreamHost)){
                UpstreamHostModel::create([
                    'supplier_id' => $upstreamHost['supplier_id'],
                    'host_id' => $host['id'],
                    'upstream_configoption' => '',
                    'create_time' => $time
                ]);
            }
        }
        $data = [
            'host_id'               => $host['id'],
            'data_center_id'        => $parentHostLink['data_center_id'],
            'image_id'              => $parentHostLink['image_id'],
            'backup_num'            => $parentHostLink['backup_num'],
            'snap_num'              => $parentHostLink['snap_num'],
            // 'power_status'          => 'on',
            'password'              => $parentHostLink['password'],
            'config_data'           => json_encode($subConfigData),
            'create_time'           => time(),
            'type'                  => $parentHostLink['type'],
            'recommend_config_id'   => $parentHostLink['recommend_config_id'],
            'default_ipv4'          => $parentHostLink['default_ipv4'],
            'ssh_key_id'            => $parentHostLink['ssh_key_id'],
            'vpc_network_id'        => $parentHostLink['vpc_network_id'],
            'parent_host_id'        => $this->hostModel['id'],
            'ip'                    => $param['ip']??'',
        ];
        $res = $HostLinkModel->where('host_id', $host['id'])->find();
        if (empty($res)) {
            $HostLinkModel->create($data);
        } else {
            $HostLinkModel->update($data, ['host_id' => $host['id']]);
        }
        $IpDefenceModel->insert([
            'host_id' => $host['id'],
            'ip' => $param['ip']??'',
            'defence' => $defaultDefence,
        ]);
//        $IpDefenceModel->saveDefence([
//            'host_id' => $host['id'],
//            'ip' => $param['ip']??'',
//            'defence' => $defaultDefence,
//        ]);
        return true;
    }

    /**
     * 时间 2025-03-19
     * @title 更换IP
     * @desc  更换IP
     * @author hh
     * @version v1
     * @param   array param.ip_id - IPID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function changeIp($param)
    {
        $cloudDetail = $this->idcsmartCloud->cloudDetail($this->id);
        if($cloudDetail['status'] != 200){
            return $cloudDetail;
        }
        $bwGroupIp = $cloudDetail['data']['default_bw_group']['id'];

        $result = $this->idcsmartCloud->floatIpUpdate($this->id, [
            'bw_group'	=> $bwGroupIp,
            'ip'		=> $param['ip_id'],
        ]);
        if($result['status'] == 200){
            // 同步IP
            $ip = $this->hostLinkModel->syncIp([
                'host_id'	=> $this->hostModel['id'],
                'id'		=> $this->id
            ], $this->idcsmartCloud, NULL, false);

            $addIp = array_column($result['data']['add_ip'], 'ip');
            $delIp = array_column($result['data']['del_ip'], 'ip');

            $description = lang_plugins('log_mf_cloud_change_ip_success', [
                '{hostname}'	=> 'host#'.$this->hostModel['id'].'#'.$this->hostModel['name'].'#',
                '{add_ip}'		=> implode(',', $addIp),
                '{del_ip}'		=> implode(',', $delIp),
            ]);
            active_log($description, 'host', $this->hostModel['id']);

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
                $this->ipChange([
                    'ips'	=> $ips,
                ]);
                upstream_sync_host($this->hostModel['id'], 'update_host','upgrade_ip_num');
            }

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2022-06-30
     * @title 获取实例流量
     * @desc  获取实例流量
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  float data.flow - 流量(单位:GB)
     * @return  bool data.support - 是否支持按流量计费(true=支持,false=不支持)
     */
    public function usageFlow($param): array
    {
        $configData = json_decode($this->hostLinkModel['config_data'], true);
        if(empty($configData['line']['bill_type']) || $configData['line']['bill_type'] != 'flow'){
            $result = [
                'status' => 200,
                'msg'	 => lang_plugins('success_message'),
                'data'	 => [
                    'flow'		=> 0.000000,
                    'support'	=> false,
                ],
            ];
            return $result;
        }

        $param = [
            'start_time'	=> $param['start_time'],
            'end_time'		=> $param['end_time'],
            'start_time_add_pick_limit' => 1,
        ];
        // if($this->downstreamHostLogic->isDownstream()){
        // 	return $this->downstreamHostLogic->usageFlow($param);
        // }

        $res = $this->idcsmartCloud->cloudFlow($this->id, $param);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('success_message'),
                'data'=>[
                    'flow'		=> $res['data']['gb_flow'],
                    'support'	=> true,
                ],
            ];
        }else{
            $result = [
                'status' => 400,
                'msg'	 => lang_plugins('flow_info_get_failed'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2022-09-25
     * @title 计算备份配置价格
     * @desc  计算备份配置价格
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   string param.type - 类型(snap=快照,backup=备份) require
     * @param   int param.num - 数量 require
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.backup_config.type - 类型(snap=快照,backup=备份)
     * @return  int data.backup_config.num - 数量
     * @return  string data.backup_config.price - 价格
     * @return  string data.base_price - 基础价格
     */
    public function calConfigPrice($param): array
    {
        if(!in_array($this->hostModel['status'], ['Active'])){
            return ['status'=>400, 'msg'=>lang_plugins('产品仅正常状态可升降级')];
        }
        if( $this->hostLinkModel[ $param['type'].'_num' ] == $param['num']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_num_not_change')];
        }
        $productId = $this->hostModel['product_id'];
        $configData = json_decode($this->hostLinkModel['config_data'], true);
        $isOnDemand = $this->hostModel['billing_cycle'] == 'on_demand';

        // 试用
        if ($configData['duration']['id'] == config('idcsmart.pay_ontrial')){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
        }

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->where('product_id', $productId)->find();

        $type = ['backup'=>lang_plugins('backup'), 'snap'=>lang_plugins('snap')];

        // 已用数量
        $used = 0;

        if($this->downstreamHostLogic->isDownstream()){
            if($param['type'] == 'backup'){
                $res = $this->downstreamHostLogic->backupList([
                    'page'  => 1,
                    'limit' => 999,
                ]);
            }else{
                $res = $this->downstreamHostLogic->snapshotList([
                    'page'  => 1,
                    'limit' => 999,
                ]);
            }
            if($res['status'] != 200){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_status_except_please_wait_and_retry')];
            }
            $used = $res['data']['count'] ?? 0;
        }else{
            // 当前已用数量
            $res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>$param['type']]);
            if($res['status'] != 200){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_host_status_except_please_wait_and_retry')];
            }
            $used = $res['data']['meta']['total'] ?? 0;
        }
        if($param['num'] < $used){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_downgrade_to_this_num', ['{num}'=>$param['num']]) ];
        }
        if(!isset($config[$param['type'].'_enable']) || $config[$param['type'].'_enable'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_buy_backup', ['{type}'=>$type[$param['type']] ]) ];
        }
        $arr = BackupConfigModel::where('product_id', $productId)
            ->where('type', $param['type'])
            ->select()
            ->toArray();
        if($isOnDemand){
            $arr = array_column($arr, 'on_demand_price', 'num');
        }else{
            $arr = array_column($arr, 'price', 'num');
        }
        if(!isset($arr[ $param['num'] ])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_buy_this_num')];
        }
        $price = 0;
        $priceDifference = 0;
        $description = $type[$param['type']]. lang_plugins('mf_cloud_num') . '：' . $this->hostLinkModel[ $param['type'].'_num' ].' => '.$param['num'];
        $oldPrice = $arr[ $this->hostLinkModel[ $param['type'].'_num' ] ] ?? '0';
        $price = $arr[ $param['num'] ];
        
        // 初始化用户等级折扣相关变量
        $clientLevelDiscount = '0.00';
        $renewPriceDifferenceClientLevelDiscount = '0.00';
        $renewDiscountPrice = '0.00';
        $renewPriceDifference = '0.00'; // 续费差价
        $orderItem = [];

        // 匹配周期
        if($isOnDemand){
            $priceDifference = bcsub($price, $oldPrice, 4);
            $renewPriceDifference = $priceDifference;

            $basePrice = 0;
            $realPriceDifference = 0;
            $price = 0;
            
            $showPrice = $this->calOnDemadShowPrice($priceDifference, $priceDifference);
            $renewPrice = $showPrice['base_renew_price'];
            $renewPriceClientLevelDiscount = $showPrice['renew_price_client_level_discount'];
            $renewPriceDifferenceClientLevelDiscount = $showPrice['on_demand_renew_price_difference_client_level_discount'];
        }else{
            $DurationModel = new DurationModel();
            $duration = $DurationModel->where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_upgrade')];
            }
            $firstDuration = $DurationModel->firstDuration($productId);

            $multiplier = 1;
        
            $diffTime = $this->hostModel['due_time'] - time();

            $ProductDurationRatioModel = new DurationRatioModel();
            $firstRatio = $ProductDurationRatioModel->where('product_id',$productId)->where('duration_id',$firstDuration['id'])->value('ratio');
            $ratio = $ProductDurationRatioModel->where('product_id',$productId)->where('duration_id',$duration['id'])->value("ratio");
            if ($firstRatio>0 && $ratio>0){
                $multiplier = $ratio/$firstRatio;
            }

            // 原价,找不到数量就当成0
            $oldPrice = bcmul($oldPrice, $multiplier, 2);
            $price = bcmul($price, $multiplier, 2);

            // 增加价格系数
            $oldPrice = bcmul($oldPrice, $duration['price_factor'], 2);
            $price = bcmul($price, $duration['price_factor'], 2);
        
            if($this->hostModel['billing_cycle'] == 'free'){
                $price = 0;
                $priceDifference = 0;
            }else{
                // 周期
                $priceDifference = bcsub($price, $oldPrice);
                $renewPriceDifference = $priceDifference;
                $price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
            }

            $basePrice = bcadd($this->hostModel['base_price'],$priceDifference,2);
            
            // 下游
            $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
            $priceBasis = $param['price_basis'] ?? 'agent';
            $priceAgent = $priceBasis=='agent';
            // if($isDownstream){
            // 	$price = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $price,
            // 	]);
            // 	$priceDifference = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $priceDifference,
            // 	]);
            // 	// 返给下游的基础价格
            // 	$basePrice = $DurationModel->downstreamSubClientLevelPrice([
            // 		'product_id' => $productId,
            // 		'client_id'  => $this->hostModel['client_id'],
            // 		'price'      => $basePrice,
            // 	]);
            // }

            // 计算用户等级折扣（仅非下游）
            // if(!$isDownstream){
                $clientLevel = $this->getClientLevel([
                    'product_id'    => $productId,
                    'client_id'     => $this->hostModel['client_id'],
                ]);
                if (!$priceAgent){
                    $clientLevel = [];
                }
                if(!empty($clientLevel)){
                    // 计算实际折扣金额
                    $discount = bcdiv($price*$clientLevel['discount_percent'], 100, 2);
                    $renewDiscount = bcdiv($priceDifference*$clientLevel['discount_percent'], 100, 2);
                    
                    $orderItem[] = [
                        'type'          => 'addon_idcsmart_client_level',
                        'rel_id'        => $clientLevel['id'],
                        'amount'        => min(-$discount, 0),
                        'description'   => lang_plugins('mf_cloud_client_level', [
                            '{name}'    => $clientLevel['name'],
                            // '{host_id}' => $this->hostModel['id'],
                            '{value}'   => $clientLevel['discount_percent'],
                        ]),
                    ];
                    
                    $clientLevelDiscount = amount_format($discount, 2);
                    $renewPriceDifferenceClientLevelDiscount = amount_format($renewDiscount, 2);
                    
                    $price = bcsub($price, $discount, 2);
                    $priceDifference = bcsub($priceDifference, $renewDiscount, 2);
                }
            // }

            $realPriceDifference = $price;

            $renewPrice = bcadd($this->hostModel['renew_amount'], $priceDifference, 2);
            $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
            $renewPrice = amount_format($renewPrice, 2);
        }
        $price = max(0, $price);
        $price = amount_format($price);
        
        $backupConfigData = [
            'type'  => $param['type'],
            'num'   => $param['num'],
            'price' => $price,
        ];

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $realPriceDifference,
                'renew_price_difference' => $renewPriceDifference,
                'backup_config' => $backupConfigData,
                'base_price' => $basePrice,
                'renew_price' => $renewPrice,
                'renew_price_client_level_discount' => $renewPriceClientLevelDiscount ?? '0.0000',
                'client_level_discount' => $clientLevelDiscount ?? '0.00',
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount ?? '0.00',
                'discount' => $clientLevelDiscount ?? '0.00',
                'order_item' => $orderItem ?? [],
                'renew_discount_price_difference' => $renewPriceDifference ?? '0.0000',
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成备份/快照数量订单
     * @desc  生成备份/快照数量订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   string param.type - 类型(snap=快照,backup=备份) require
     * @param   int param.num - 数量 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBackupConfigOrder($param)
    {
        if(isset($param['is_downstream'])){
            unset($param['is_downstream']);
        }
        if($this->hostModel['change_billing_cycle_id'] > 0){
            return ['status'=>400, 'msg'=>lang('host_request_due_to_on_demand_now_cannot_do_this') ];
        }
        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidChangeBillingCycleOrder($this->hostModel['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $this->hostModel['id'],
            'scene_desc'=> lang_plugins('mf_cloud_upgrade_scene_change_'.$param['type'].'_num'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $res = $this->calConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $data = [
            'host_id'     => $this->hostModel['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'modify_backup',
                'backup_type' => $param['type'],
                'num' => $param['num'],
                'backup_config' => $res['data']['backup_config'],
            ],
            'customfield' => $param['customfield'] ?? [],
            'order_item'  => $res['data']['order_item'] ?? [],
            'discount'    => $res['data']['discount'] ?? '0.00',
            // 'ondemand_renew_price_difference_client_level_discount' => $ondemandRenewPriceDifferenceClientLevelDiscount ?? '0.0000',
            'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2025-04-17
     * @title 实例流量数据
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.start_time - 开始时间 require
     * @param   int param.end_time - 结束时间
     * @return  string list[].time - 时间
     * @return  float list[].in - 进流量
     * @return  float list[].out - 出流量
     * @return  string unit - 当前单位
     */
    public function flowData(array $param): array
    {
        $list = [];
        $unit = 'B';

        // 获取时间,最多获取2个月前
        $minTime = max(strtotime('-2 month'), $this->hostModel['active_time']);

        $param['start_time'] = $param['start_time'] ?? 0;
        $param['start_time'] = max($param['start_time'], $minTime);
        if(empty($param['end_time'])){
            $param['end_time'] = time();
        }
        if($param['start_time'] > $param['end_time']){
            return ['list'=>$list, 'unit'=>$unit ];
        }

        $query = [
            'start_time'	=> $param['start_time'],
            'end_time'		=> $param['end_time'],
        ];
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->flowData($query);

            if($res['status'] == 200){
                $list = $res['data']['list'];
                $unit = $res['data']['unit'];
            }
        }else{
            $query['start_time'] = $query['start_time'].'000';
            $query['end_time'] = $query['end_time'].'000';

            $res = $this->idcsmartCloud->flowData($this->id, $query);
            if($res['status'] == 200){
                $list = $res['data']['data'];
                $unit = $res['data']['unit'];
            }
        }
        return ['list'=>$list, 'unit'=>$unit ];
    }

    /**
     * 时间 2025-04-17
     * @title 计算按需升降级显示价格
     * @author hh
     * @version v1
     * @param   string renewPriceDifference - 续费原始差价 require
     * @param   string renewDiscountPrice 0 续费可折扣价格部分
     * @return  string base_renew_price - 基础续费价格(折扣前)
     * @return  string renew_price - 实际续费价格(折扣后)
     * @return  string renew_price_client_level_discount - 续费价格用户等级折扣
     */
    public function calOnDemadShowPrice($renewPriceDifference, $renewDiscountPrice = 0): array
    {
        // 原续费价格
        $baseRenewPrice = bcadd($this->hostModel['base_renew_amount'], $renewPriceDifference, 4);
        $baseRenewPrice = $baseRenewPrice >= 0 ? $baseRenewPrice : 0;
        $baseRenewPrice = amount_format($baseRenewPrice, 4);

        $renewPrice = bcadd($this->hostModel['renew_amount'], $renewPriceDifference, 4);
        $renewPrice = $renewPrice >= 0 ? $renewPrice : 0;
        $renewPrice = amount_format($renewPrice, 4);

        // 计算实际用户等级折扣
        $renewPriceClientLevelDiscount = '0.0000';
        // 可折扣部分差价折扣
        $ondemandRenewPriceDifferenceClientLevelDiscount = '0.0000';
        if($renewDiscountPrice != 0){
            $hookDiscountResults = hook('client_discount_by_amount', [
                'client_id'		=> $this->hostModel['client_id'],
                'product_id'	=> $this->hostModel['product_id'],
                'amount'		=> $renewDiscountPrice,
                'scale'			=> 4,
            ]);
            foreach ($hookDiscountResults as $hookDiscountResult){
                if ($hookDiscountResult['status'] == 200){
                    $ondemandRenewPriceDifferenceClientLevelDiscount = $hookDiscountResult['data']['discount'] ?? 0;
                    $renewPrice = bcsub($renewPrice, $ondemandRenewPriceDifferenceClientLevelDiscount, 4);
                }
            }
        }
        // 计算续费差价折扣
        // $hookDiscountResults = hook('client_discount_by_amount', [
        //     'client_id'		=> $this->hostModel['client_id'],
        //     'product_id'	=> $this->hostModel['product_id'],
        //     'amount'		=> $renewPriceDifference,
        //     'scale'			=> 4,
        // ]);
        // foreach ($hookDiscountResults as $hookDiscountResult){
        //     if ($hookDiscountResult['status'] == 200){
        //         $renewPriceDifferenceClientLevelDiscount = $hookDiscountResult['data']['discount'] ?? 0;
        //         $renewPriceDifference = bcsub($renewPriceDifference, $renewPriceDifferenceClientLevelDiscount, 4);
        //     }
        // }
        // 重新计算折扣,这里折扣不会有负数,该折扣只用于前端显示
        if($baseRenewPrice > $renewPrice){
            $renewPriceClientLevelDiscount = bcsub($baseRenewPrice, $renewPrice, 4);
        }else{
            $baseRenewPrice = $renewPrice;
            $renewPriceClientLevelDiscount = '0.0000';
        }
        $result = [
            'base_renew_price'  => $baseRenewPrice,
            'renew_price'       => $renewPrice,
            'renew_price_client_level_discount'=> $renewPriceClientLevelDiscount,
            'on_demand_renew_price_difference_client_level_discount'=> $ondemandRenewPriceDifferenceClientLevelDiscount,
            // 'renew_price_difference' => $renewPriceDifference,
        ];
        return $result;
    }

    /**
     * 时间 2025-09-17
     * @title 流量包列表
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return  array list - 流量包列表
     * @return  int data[].id - 流量包ID
     * @return  string data[].name - 流量包名称
     * @return  int data[].size - 流量包大小(GB)
     * @return  float data[].used - 已使用(GB)
     * @return  int data[].expire_time - 到期时间(秒级时间戳,0表示不到期)
     * @return  int data[].expire_with_reset - 是否随重置过期(0=否,1=是)
     * @return  int data[].status - 状态(0=失效,1=有效)
     * @return  int data[].create_time - 创建时间(秒级时间戳)
     * @return  int count - 总条数
     */
    public function trafficPackageList(array $param){
        $list = [];
        $count = 0;

        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->trafficPackageList($param);

            if($res['status'] == 200){
                $list = $res['data']['list'] ?? [];
                $count = $res['data']['count'] ?? 0;
            }
        }else{
            $res = $this->idcsmartCloud->trafficPackageList($this->id, [
                'page' => $param['page'],
                'per_page' => $param['limit'],
            ]);

            if($res['status'] == 200){
                $list = $res['data']['data'] ?? [];
                $count = $res['data']['meta']['total'] ?? 0;
            }
        }
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2025-12-26
     * @title 下游退款前验证
     * @desc  下游退款前验证,检查当前周期是否支持申请停用
     * @author hh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function checkRefund(): array
    {
        $configData = json_decode($this->hostLinkModel['config_data'], true);

        if(!empty($configData['duration'])){
            // 获取当前周期
            $duration = DurationModel::where('product_id', $this->hostModel['product_id'])
                ->where('num', $configData['duration']['num'])
                ->where('unit', $configData['duration']['unit'])
                ->find();

            if(!empty($duration) && $duration['support_apply_for_suspend'] == 0){
                return ['status' => 400, 'msg' => lang_plugins('mf_cloud_host_duration_not_support_apply_for_suspend')];
            }
        }

        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }



}


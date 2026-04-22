<?php 
namespace server\mf_dcim\logic;

use addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel;
use app\admin\model\PluginModel;
use app\common\model\ProductModel;
use app\common\model\UpstreamHostModel;
use server\mf_dcim\idcsmart_dcim\Dcim;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\model\ImageModel;
use server\mf_dcim\model\HostImageLinkModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\LineModel;
use server\mf_dcim\model\OptionModel;
use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\model\HostOptionLinkModel;
use server\mf_dcim\model\PriceModel;
use server\mf_dcim\model\ModelConfigModel;
use server\mf_dcim\model\ModelConfigOptionLinkModel;
use server\mf_dcim\model\LimitRuleModel;
use server\mf_dcim\model\ImageGroupModel;
use server\mf_dcim\model\IpDefenceModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ClientModel;
use app\common\model\HostIpModel;
use think\facade\Cache;
use app\common\model\HostAdditionModel;

class CloudLogic
{
	protected $id = 0;   				// DCIM ID
	protected $idcsmartCloud = null;	// DCIM操作类型
	protected $hostModel = [];			// 产品模型
	protected $isClient = false;        // 是否是客户操作

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
                throw new \Exception(lang_plugins('mf_dcim_host_not_found'));
            }
            // 是否是魔方云模块
            if($HostModel->getModule() != 'mf_dcim'){
                throw new \Exception(lang_plugins('mf_dcim_can_not_do_this'));
            }
            // 获取模块通用参数
            $params = $HostModel->getModuleParams();
            if(empty($params['server'])){
                throw new \Exception(lang_plugins('mf_dcim_host_not_link_server'));
            }
            $this->idcsmartCloud = new Dcim($params['server']);
            $this->hostModel = $params['host'];
            $this->server = $params['server'];
            $this->hostLinkModel = $HostLinkModel;

            $this->downstreamHostLogic = new DownstreamCloudLogic($params['host']);

            // 前台用户验证
            $app = app('http')->getName();
            if($app == 'home'){
                if($HostModel['client_id'] != get_client_id()){
                    throw new \Exception(lang_plugins('mf_dcim_host_not_found'));
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
    		$ConfigModel = new ConfigModel();
    		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

    		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
    			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
                if(!empty($manual_resource)){
                	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                	$res = $ManualResourceLogic->taskStatus($manual_resource['id']);
                	if($res['status'] == 200 && isset($res['data']['task_type'])){
                		$status = [
    						'status' => 'operating',
    						'desc'   => lang_plugins('mf_dcim_operating')
    					];
                	}else{
                		$res = $ManualResourceLogic->status($manual_resource['id']);
    	            	if($res['status'] == 200){
    						if($res['data']['status'] == 'nonsupport'){
    							$status = [
    								'status' => 'fault',
    								'desc'   => lang_plugins('mf_dcim_fault'),
    							];
    						}else if($res['data']['status'] == 'on'){
    			                $status = [
    								'status' => 'on',
    								'desc'   => lang_plugins('mf_dcim_on'),
    							];
    			            }else if($res['data']['status'] == 'off'){
    			                $status = [
    								'status' => 'off',
    								'desc'   => lang_plugins('mf_dcim_off')
    							];
                            }else{
    			                $status = [
    								'status' => 'fault',
    								'desc'   => lang_plugins('mf_dcim_fault'),
    							];
    			            }
    					}else{
    						$status = [
    							'status' => 'fault',
    							'desc'   => lang_plugins('mf_dcim_fault'),
    						];
    					}
                	}

                }else{
					$status = [
						'status' => 'on',
						'desc'   => lang_plugins('mf_dcim_on'),
					];
                	// $HostAdditionModel = new HostAdditionModel();
                	// $hostAddition = $HostAdditionModel->where('host_id', $this->hostModel['id'])->find();
                	// $status = [
    				// 	'status' => $hostAddition['power_status'] ?: 'on',
    				// 	'desc'   => lang_plugins('mf_dcim_'.($hostAddition['power_status'] ?: 'on')),
    				// ];
                }
    		}else{
    			if($this->downstreamHostLogic->isDownstream()){
            		$res = $this->downstreamHostLogic->status();

            		if($res['status'] == 200){
            			$HostAdditionModel = new HostAdditionModel();
    		            $HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
    		                'power_status'    => $res['data']['status'],
    		            ]);
    		            return $res;
            		}
            	}else{
    				$res = $this->idcsmartCloud->getReinstallStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
    			}
    			if($res['status'] == 200){
    				if(isset($res['data']['task_type'])){
    					$status = [
    						'status' => 'operating',
    						'desc'   => lang_plugins('mf_dcim_operating')
    					];
    				}else{
    					$res = $this->idcsmartCloud->powerStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
    					if($res['status'] == 200){
    						if($res['msg'] == 'nonsupport'){
    							$status = [
    								'status' => 'fault',
    								'desc'   => lang_plugins('mf_dcim_fault'),
    							];
    						}else if($res['msg'] == 'on'){
    			                $status = [
    								'status' => 'on',
    								'desc'   => lang_plugins('mf_dcim_on'),
    							];
    			            }else if($res['msg'] == 'off'){
    			                $status = [
    								'status' => 'off',
    								'desc'   => lang_plugins('mf_dcim_off')
    							];
    			            }else{
    			                $status = [
    								'status' => 'fault',
    								'desc'   => lang_plugins('mf_dcim_fault'),
    							];
    			            }
    					}else{
    						$status = [
    							'status' => 'fault',
    							'desc'   => lang_plugins('mf_dcim_fault'),
    						];
    					}
    				}
    			}else{
    				$status = [
    					'status' => 'fault',
    					'desc'   => lang_plugins('mf_dcim_fault'),
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
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function on()
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->on($manual_resource['id']);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_boot_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->on();
        	}else{
				$res = $this->idcsmartCloud->on(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
			}
		}
		
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_boot_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_boot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
				system_notice([
					'name'                  => 'host_module_action',
					'email_description'     => lang('host_module_action'),
					'sms_description'       => lang('host_module_action'),
					'task_data' => [
						'client_id' => $this->hostModel['client_id'],
						'host_id'	=> $this->hostModel['id'],
						'template_param'=>[
                        	'module_action' => lang_plugins('on')
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
					$description = lang_plugins('mf_dcim_log_host_start_boot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_boot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('on')])
				];
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_start_boot_fail')
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_boot_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_boot_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function off()
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->off($manual_resource['id']);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_off_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->off();
        	}else{
				$res = $this->idcsmartCloud->off(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
			}
		}

		
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_off_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_off_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
				system_notice([
					'name'                  => 'host_module_action',
					'email_description'     => lang('host_module_action'),
					'sms_description'       => lang('host_module_action'),
					'task_data' => [
						'client_id' => $this->hostModel['client_id'],
						'host_id'	=> $this->hostModel['id'],
						'template_param'=>[
                        	'module_action' => lang_plugins('off')
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
					$description = lang_plugins('mf_dcim_log_host_start_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_off_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('off')])
				];
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_start_off_fail')
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_off_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_off_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function reboot()
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->reboot($manual_resource['id']);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_reboot_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->reboot();
        	}else{
				$res = $this->idcsmartCloud->reboot(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
			}
		}

		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_reboot_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
				system_notice([
					'name'                  => 'host_module_action',
					'email_description'     => lang('host_module_action'),
					'sms_description'       => lang('host_module_action'),
					'task_data' => [
						'client_id' => $this->hostModel['client_id'],
						'host_id'	=> $this->hostModel['id'],
						'template_param'=>[
                        	'module_action' => lang_plugins('reboot')
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
					$description = lang_plugins('mf_dcim_log_host_start_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reboot_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('reboot')])
				];
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_start_reboot_fail')
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_reboot_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reboot_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @param   int param.more 0 获取更多信息(0=否,1=是)
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 * @return  string data.url - 控制台地址
	 * @return  string data.vnc_url - 控制台websocket地址(more=1返回)
	 * @return  string data.vnc_pass - vnc密码(more=1返回)
	 * @return  string data.password - 机器密码(more=1返回)
	 * @return  string data.token - VNC页面临时令牌(more=1返回)
	 */
	public function vnc($param)
	{
		// 前台用户在重装时不允许打开VNC
		if($this->isClient){
			// 检查服务器是否正在重装（getReinstallStatus内部已处理手动资源模式）
			$reinstallStatusRes = $this->getReinstallStatus();
			
			// 状态检查失败或task_type=0时禁用VNC
			if($reinstallStatusRes['status'] != 200 || 
				(isset($reinstallStatusRes['data']['task_type']) && $reinstallStatusRes['data']['task_type'] == 0)){
				return [
					'status' => 400,
					'msg' => lang_plugins('mf_dcim_vnc_disable_during_reinstall'),
				];
			}
		}

		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
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
			                $result['data']['url'] = request()->domain().'/console/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
			            }else{
			                $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
			            }
			            // 生成一个临时token
			            $token = md5(rand_str(16));
			            $cache['token'] = $token;

			            Cache::set('mf_dcim_vnc_'.$this->hostModel['id'], $cache, 30*60);
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
						'msg'   => lang_plugins('mf_dcim_vnc_start_fail'),
					];
            	}
            }else{
            	$result = [
					'status' => 400,
					'msg'   => lang_plugins('mf_dcim_vnc_start_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->vnc();
        	}else{
				$res = $this->idcsmartCloud->vnc(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
			}

			if($res['status'] == 200){
				$result = [
					'status' => 200,
					'msg'    => lang_plugins('success_message'),
					'data'	 => [],
				];

                if(isset($res['data']['vnc_url'])){
                    $link_url = $res['data']['vnc_url'];
                }else if(isset($res['vnc_link_url']) && !empty($res['vnc_link_url'])){
	                // 外部vnc地址
	                if(request()->scheme() == 'https'){
	                    $link_url = 'wss://'.$res['vnc_link_url'];
	                }else{
	                    $link_url = 'ws://'.$res['vnc_link_url'];
	                }
	            }else{
	            	if(strpos($this->server['url'], 'https://') !== false){
		                $link_url = str_replace('https://', 'wss://', $this->server['url']);
		            }else{
		                $link_url = str_replace('http://', 'ws://', $this->server['url']);
		            }
		            // vnc不能包含管理员路径
		            // $link_url = rtrim($link_url, '/');
		            // if(substr_count($link_url, '/') > 2){
		            //     $link_url = substr($link_url, 0, strrpos($link_url, '/'));
		            // }
		            $link_url .= '/websockify_'.$res['house_id'].'?token='.$res['token'];
	            }
	            
	            $hostAddition = HostAdditionModel::where('host_id', $this->hostModel['id'])->find();

	            // 获取的东西放入缓存
	            $cache = [
	            	'vnc_url' => $link_url,
	            	'vnc_pass'=> $res['pass'] ?? $res['data']['vnc_pass'],
	            	'password'=> $hostAddition['password'] ?? '',
	            ];
	            if($this->isClient){
	                $result['data']['url'] = request()->domain().'/console/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
	            }else{
	                $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
	            }
	            
	            // 生成一个临时token
	            $token = md5(rand_str(16));
	            $cache['token'] = $token;

	            Cache::set('mf_dcim_vnc_'.$this->hostModel['id'], $cache, 30*60);
	        	if(strpos($result['data']['url'], '?') !== false){
	        		$result['data']['url'] .= '&tmp_token='.$token;
	        	}else{
	        		$result['data']['url'] .= '?tmp_token='.$token;
	        	}
	        	if(isset($param['more']) && $param['more'] == 1){
	                $result['data'] = array_merge($result['data'], $cache);
	            }
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_vnc_start_fail'),
				];
			}
		}
		return $result;
	}

	/**
	 * 时间 2024-08-25
	 * @title 重启VNC
	 * @desc 重启VNC
	 * @author hh
	 * @version v1
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function restartVnc()
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	// 手动资源模式下可能没有restartVnc方法，这里可以先返回成功
            	$res = [
					'status' => 200,
					'msg'   => 'VNC restart request submitted',
				];
            }else{
            	$res = [
					'status' => 400,
					'msg'   => lang_plugins('mf_dcim_start_restart_vnc_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		// 下游模式下调用下游的restartVnc方法
        		$res = $this->downstreamHostLogic->restartVnc();
        	}else{
				$res = $this->idcsmartCloud->restartVnc(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
			}
		}
		
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => 'VNC重启成功'
			];

			$description = lang_plugins('mf_dcim_log_host_restart_vnc_success', [
				'{hostname}' => $this->hostModel['name'],
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => 'VNC重启失败'
			];

			if($this->isClient){
			
			}else{
				$result['msg'] = $res['msg'] ?? 'VNC重启失败';
			}
			$description = lang_plugins('mf_dcim_log_host_restart_vnc_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{reason}'	 => $result['msg'],
			]);
		}
		
		active_log($description, 'host', $this->hostModel['id']);
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
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
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

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->resetPassword(['id' => $manual_resource['id'], 'other_user' => 0, 'user' => '', 'password'=>$param['password']]);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_reset_password_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->resetPassword(['password'=>$param['password'] ]);
        	}else{
				$res = $this->idcsmartCloud->resetPassword(['id'=>$this->id, 'hostid'=>$this->hostModel['id'], 'crack_password'=>$param['password'] ]);
			}
		}
		
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_reset_password_success')
			];

			$HostAdditionModel = new HostAdditionModel();
			$HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
				'password'	=> $param['password'],
			]);

			$description = lang_plugins('mf_dcim_log_host_start_reset_password_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']!='dcim_client')){
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
					$description = lang_plugins('mf_dcim_log_host_start_reset_password_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reset_password_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('reset_password')])
				];
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_start_reset_password_fail')
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_reset_password_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reset_password_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function rescue($param)
	{
		$type = ['',2,1];

		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->rescue(['id' => $manual_resource['id'], 'system'=>$type[$param['type']]]);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_rescue_fail'),
				];
            }
		}else{
			if($this->downstreamHostLogic->isDownstream()){
        		$res = $this->downstreamHostLogic->rescue(['type'=>$param['type']]);
        	}else{
				$res = $this->idcsmartCloud->rescue(['id'=>$this->id, 'hostid'=>$this->hostModel['id'], 'system'=>$type[$param['type']], 'rescue'=>0 ]);
			}
		}
		
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_rescue_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_rescue_success', ['{hostname}'=>$this->hostModel['name']]);

		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']!='dcim_client')){
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
                    	'system' => intval($type[$param['type']])
                    ]
                ]);

                if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('rescue')])
				];
			}else{
				$result = [
					'status' => 400,
					'msg'    => lang_plugins('mf_dcim_start_rescue_fail')
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_rescue_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_rescue_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

        if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->cancelTask(['id' => $manual_resource['id']]);
            }else{
                $res = [
                    'status' => 400,
                    'desc'   => lang_plugins('mf_dcim_start_exit_rescue_failed'),
                ];
            }
        }else{
            if($this->downstreamHostLogic->isDownstream()){
                $res = $this->downstreamHostLogic->exitRescue();
            }else{
                $res = $this->idcsmartCloud->exitRescue(['id'=>$this->id]);
            }
        }

        if($res['status'] == 200){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('mf_dcim_start_exit_rescue_success')
            ];

            $description = lang_plugins('mf_dcim_log_host_start_exit_rescue_success', ['{hostname}'=>$this->hostModel['name']]);
        }else{
            $result = [
                'status' => 400,
                'msg'    => lang_plugins('mf_dcim_start_exit_rescue_failed')
            ];

            if($config['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']=='not_support')){
                system_notice([
                    'name'                  => 'host_module_action',
                    'email_description'     => lang('host_module_action'),
                    'sms_description'       => lang('host_module_action'),
                    'task_data' => [
                        'client_id' => $this->hostModel['client_id'],
                        'host_id'   => $this->hostModel['id'],
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
                    $description = lang_plugins('mf_dcim_log_host_start_exit_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('mf_dcim_log_admin_host_start_exit_rescue_in_progress', ['{hostname}'=>$this->hostModel['name']]);
                }

                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('exit_rescue')]),
                ];
            }else{
                if($this->isClient){
                    $description = lang_plugins('mf_dcim_log_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name']]);
                }else{
                    $description = lang_plugins('mf_dcim_log_admin_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @param   int param.image_id - 镜像ID require
	 * @param   int param.password - 密码 require
	 * @param   int param.port - 端口 require
	 * @param   int param.part_type - 分区类型0全盘格式化1第一分区格式化 require
	 * @param   string param.code - 二次验证验证码
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public function reinstall($param)
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
            	$res = $ManualResourceLogic->reinstall(['id' => $manual_resource['id'], 'password' => $param['password'], 'os' => $param['image_id'], 'port' => $param['port'], 'part_type' => $param['part_type'] ?? 0]);
            }else{
            	$res = [
					'status' => 400,
					'desc'   => lang_plugins('mf_dcim_start_rescue_fail'),
				];
            }
		}else{
			$image = ImageModel::find($param['image_id']);
			if(empty($image)){
				return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
			}
			$currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
			// 仅当变更时验证
			if($currentConfig['image_id'] != $param['image_id']){
				$currentConfig['image_id'] = $param['image_id'];
	        
		    	$LimitRuleModel = new LimitRuleModel();
		        $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig, ['image']);
		        if($checkLimitRule['status'] == 400){
		        	return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_reinstall_for_limit_rule')];
		        }
			}
			// 获取镜像分组
	        $imageGroup = ImageGroupModel::find($image['image_group_id']);
	        $isWindows = !empty($imageGroup) ? $imageGroup->isWindows($imageGroup) : false;
			
			// 前台
			if($this->isClient){
				if($image['enable'] == 0){
					return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
				}
				if($image['charge'] == 1 && $image['price']>0){
					$hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
					if(empty($hostImageLink)){
						return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_is_charge_please_buy')];
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

			if($this->downstreamHostLogic->isDownstream()){
        		$post = $param;
        		// 替换参数
        		$post['image_id'] = $image['upstream_id'];

		        $res = $this->downstreamHostLogic->reinstall($post);
        	}else{

				$post = [];
				$post['id'] = $this->id;
				$post['hostid'] = $this->hostModel['id'];
				$post['mos'] = $image['rel_image_id'];
				$post['rootpass'] = $param['password'];
				$post['port'] = $param['port'];
				$post['part_type'] = $param['part_type'] ?? 0;
				
				$res = $this->idcsmartCloud->reinstall($post);
			}
		}
		
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'=>lang_plugins('mf_dcim_start_reinstall_success'),
			];

			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){

			}else{
				$update['image_id'] = $param['image_id'];

				$this->hostLinkModel->update($update, ['host_id'=>$this->hostModel['id']]);

				// 重装后保存配置到附加表
		        if(class_exists('app\common\model\HostAdditionModel')){
		            $HostAdditionModel = new HostAdditionModel();
		            $HostAdditionModel->hostAdditionSave($this->hostModel['id'], [
		                'image_icon'    => $imageGroup['icon'] ?? '',
		                'image_name'    => $image['name'],
		                'username'		=> $isWindows ? 'administrator' : 'root',
		                'password'		=> $res['ospassword'] ?? $param['password'],
		                'port'			=> $res['port'] ?? $param['port'],
		            ]);
		        }
			}

			

			$description = lang_plugins('mf_dcim_log_host_start_reinstall_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource() && (empty($manual_resource) || $manual_resource['control_mode']!='dcim_client')){
				$image = ImageModel::find($param['image_id']);
				if(empty($image)){
					return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
				}

				// 获取镜像分组
	        	$imageGroup = ImageGroupModel::find($image['image_group_id']);
	        	$isWindows = !empty($imageGroup) ? $imageGroup->isWindows($imageGroup) : false;

	        	// 前台
				if($this->isClient){
					if($image['enable'] == 0){
						return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
					}
					if($image['charge'] == 1 && $image['price']>0){
						$hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
						if(empty($hostImageLink)){
							return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_is_charge_please_buy')];
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
                    'data' 						=> [
                    	'os' 			=> intval($param['image_id']),
                    	'image_icon'    => $imageGroup['icon'] ?? '',
		                'image_name'    => $image['name'],
		                'username'		=> $isWindows ? 'administrator' : 'root',
		                'password'		=> $param['password'],
		                'port' 			=> intval($param['port']),
		                'part_type' 	=> intval($param['part_type'] ?? 0)
                    ],
                ]);

                if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_reinstall_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reinstall_in_progress', ['{hostname}'=>$this->hostModel['name']]);
				}

				$result = [
					'status' => 200,
					'msg'    => lang_plugins('mf_dcim_manual_manage_action_in_progress', ['{action}' => lang_plugins('reinstall')])
				];
			}else{
				$result = [
					'status'=>400,
					'msg'=>lang_plugins('mf_dcim_start_reinstall_fail'),
				];

				if($this->isClient){
					$description = lang_plugins('mf_dcim_log_host_start_reinstall_fail', ['{hostname}'=>$this->hostModel['name']]);
				}else{
					$description = lang_plugins('mf_dcim_log_admin_host_start_reinstall_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
					$result['msg'] = $res['msg'];
				}
			}
			
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @author hh
	 * @version v1
	 * @param   int param.start_time - 开始秒级时间
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 * @return  int data.list[].time - 时间(秒级时间戳)
	 * @return  float data.list[].in_bw - 进带宽
	 * @return  float data.list[].out_bw - 出带宽
	 * @return  string data.unit - 当前单位
	 */
	public function chart($param)
	{
		if($this->downstreamHostLogic->isDownstream()){
			return $this->downstreamHostLogic->chart($param);
		}

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'list' => [],
				'unit' => 'bps'
			],
		];

		$post = [];
		$post['id'] = $this->id;
		$post['hostid'] = $this->hostModel['id'];
		$post['reverse'] = 1;
		$post['type'] = 'server';
		$post['switch_id'] = $this->id;
		
		// 时间选择,起始结束
		$post['start_time'] = $this->hostModel['active_time'] ?: $this->hostModel['create_time'];
		if(isset($param['start_time']) && !empty($param['start_time'])){
			if($param['start_time'] >= $this->hostModel['create_time']){
				$post['start_time'] = $param['start_time'];
			}else{
				$post['start_time'] = $this->hostModel['create_time'];
			}
		}
		$post['start_time'] .= '000';

		$res = $this->idcsmartCloud->traffic($post);
		if(isset($res['y_unit'])){
			$result['data']['unit'] = $res['y_unit'];
		}
		if(isset($res['in'])){
			foreach($res['in'] as $k=>$v){
				$result['data']['list'][$k] = [
					'time'	 => $k/1000,
					'in_bw'  => round($v, 2),
					'out_bw' => 0,
				];
            }
		}
		if(isset($res['out'])){
			foreach($res['out'] as $k=>$v){
				if(!isset($result['data']['list'][$k])){
					$result['data']['list'][$k] = [
						'time'	 => $k/1000,
						'in_bw'  => 0,
						'out_bw' => round($v, 2),
					];
				}else{
					$result['data']['list'][$k]['out_bw'] = round($v, 2);
				}
            }
		}
		$result['data']['list'] = array_values($result['data']['list']);
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
     * @return  int data.base_flow - 基础流量(0=不限)
     * @return  int data.temp_flow - 临时流量
	 */
	public function flowDetail()
	{
		if($this->downstreamHostLogic->isDownstream()){
			return $this->downstreamHostLogic->flowDetail();
		}

		$post = [];
		$post['id'] = $this->id;
		$post['hostid'] = $this->hostModel['id'];
		$post['unit'] = 'GB';

		$res = $this->idcsmartCloud->flow($post);
		if($res['status'] == 200){
			$configData = json_decode($this->hostLinkModel['config_data'], true);

			$data = $res['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month'];
			$percent = str_replace('%', '', $data['used_percent']);
			
			$total = $res['limit'] > 0 ? $res['limit'] + $res['temp_traffic'] : 0;
			$used =  round($total * $percent / 100, 2);
			$leave = max(round($total - $used, 2), 0);

			if(isset($configData['flow']['other_config']['bill_cycle']) && $configData['flow']['other_config']['bill_cycle'] == 'last_30days'){
				$resetFlowDay = date('d', $this->hostModel['active_time']) ?: 1;
			}else{
				$resetFlowDay = 1;
			}
			$time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00'));
			if(time() > $time){
				$time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00') .' +1 month');
			}

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
                    'base_flow' => (int)$res['limit'],
                    'temp_flow' => (int)$res['temp_traffic'],
				]
			];
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('mf_dcim_flow_info_get_failed')
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
     * @param int param.limit - 每页条数
     * @return int list[].ip - IP
     * @return string list[].subnet_mask - 掩码
     * @return string list[].gateway - 网关
     * @return int count - 总数
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
			$ConfigModel = new ConfigModel();
			$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

			if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
				$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
	            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
	            if(!empty($manual_resource)){
	            	if(!empty($manual_resource['dedicated_ip'])){
	            		$data[] = [
							'ip' => $manual_resource['dedicated_ip'],
							'subnet_mask'=>'',
							'gateway'=>'',
						];
	            	}
	            	if(!empty($manual_resource['assigned_ips'])){
	            		$manual_resource['assigned_ips'] = array_unique(explode("\n", $manual_resource['assigned_ips']));
	            		foreach ($manual_resource['assigned_ips'] as $key => $value) {
	            			$data[] = [
								'ip' => $value,
								'subnet_mask'=>'',
								'gateway'=>'',
							];
	            		}
	            		
	            	}
	            }
			}else{
				$post = [];
				$post['id'] = $this->id;

				// 获取当前所有IP
				$res = $this->idcsmartCloud->detail($post);
				if($res['status'] == 200 && isset($res['ip'])){
					if(isset($res['ip']['subnet_ip'])){
						foreach($res['ip']['subnet_ip'] as $v){
							$data[] = [
								'ip' => $v['ipaddress'],
								'subnet_mask'=>$v['subnetmask'] ?? '',
								'gateway'=>$v['gateway'] ?? '',
							];
						}
					}else if(isset($res['ip']['ip'])){
						$data[] = [
							'ip'			=> $res['server']['zhuip'],
							'subnet_mask'	=> $res['ip']['subnetmask'],
							'gateway'		=> $res['ip']['gateway'],
						];
						foreach($res['ip']['ip'] as $v){
							$data[] = [
								'ip' => $v['ipaddress'],
								'subnet_mask'=>$v['subnetmask'] ?? '',
								'gateway'=>$v['gateway'] ?? '',
							];
						}
					}
				}
			}
			
			
			$count = count($data);
			$data  = array_slice($data, ($param['page']-1)*$param['limit'], $param['limit']);
		}
		return ['list'=>$data, 'count'=>$count];
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取DCIM远程信息
	 * @desc 获取DCIM远程信息
	 * @author hh
	 * @version v1
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 * @return  string data.username - 远程用户名
	 * @return  string data.password - 远程密码
	 * @return  string data.port - 远程端口
	 * @return  int data.ip_num - IP数量
	 */
	public function detail()
	{
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		$hostAddition = HostAdditionModel::where('host_id', $this->hostModel['id'])->find();

		if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
			$ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
            	$data = [
					'username' => $hostAddition['username'] ?? $manual_resource['username'],
					'password' => $hostAddition['password'] ?? aes_password_decode($manual_resource['password']),
					'port'     => $hostAddition['port'] ?? $manual_resource['port'],
					'ip_num'   => count(array_filter(explode("\n", $manual_resource['assigned_ips'])))+1,
				];
            }else{
            	$data = [
					'username' => $hostAddition['username'] ?? '',
					'password' => $hostAddition['password'] ?? '',
					'port'     => $hostAddition['port'] ?? '',
					'ip_num'   => 0,
				];
            }
		}else{
			$data = [
				'username'	=> $hostAddition['username'] ?? '',
				'password'	=> $hostAddition['password'] ?? '',
				'port'		=> $hostAddition['port'] ?? '',
				'ip_num'	=> !empty($this->hostModel['ip']) ? 1 : 0,
			];

			if($this->downstreamHostLogic->isDownstream()){
				$res = $this->downstreamHostLogic->remoteInfo();

				if($res['status'] == 200){
					$data['username'] = $res['data']['username'];
					$data['password'] = $res['data']['password'];
					$data['port'] = $res['data']['port'];
					$data['ip_num'] = $res['data']['ip_num'];
				}
			}else{
				$post = [];
				$post['id'] = $this->id;

				// 获取当前所有IP
				$res = $this->idcsmartCloud->detail($post);

				if($res['status'] == 200){
					// $data['username'] = $res['server']['osusername'];
					// $data['password'] = $res['server']['ospassword'];
					if(empty($data['port'])){
						$data['port'] = $res['server']['port'];
					}
					// $data['port'] = $res['server']['port'];
					$data['ip_num'] = $res['ip']['ipcount']+1;
				}
			}
		}
        
        // 手动资源模式
        if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $this->hostModel['id'])->find();
            if(!empty($manual_resource)){
                $ManualResourceLogic = new \addon\manual_resource\logic\ManualResourceLogic();
                $res = $ManualResourceLogic->taskStatus($manual_resource['id']);
            }else{
                $res = [
                    'status' => 400,
                ];
            }
        }
        
        // 下游模式
        if($this->downstreamHostLogic->isDownstream()){
            $res = $this->downstreamHostLogic->getReinstallStatus();
        }else{
           // 直连模式
            $res = $this->idcsmartCloud->getReinstallStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
        }

        if(isset($res['data']['task_type']) && $res['data']['task_type']==1){
            $data['rescue'] = 1;
        }else{
            $data['rescue'] = 0;
        }

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return $result;
	}
	
    /**
     * 时间 2022-09-25
     * @title 计算产品配置升级价格
     * @desc 计算产品配置升级价格
     * @author hh
     * @version v1
	 * @param   string param.ip_num - 公网IP数量
     * @param   string param.bw - 带宽
     * @param   int param.flow - 流量包
     * @param   int param.peak_defence - 防御峰值
     * @param   array param.optional_memory - 变更后的内存(["5"=>1],5是ID,1是数量)
     * @param   array param.optional_disk - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   array param.optional_gpu - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.discount - 用户等级折扣
     * @return  array data.new_config_data - 用于缓存变更后的数据
     * @return  array data.new_admin_field - 用于缓存变更后的数据,用于后台显示
     * @return  int data.optional[].host_id - 产品ID
     * @return  int data.optional[].option_id - 变更后的可选配配置ID
     * @return  int data.optional[].num - 数量
     * @return  string data.order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int data.order_item[].rel_id - 关联ID
     * @return  float data.order_item[].amount - 子项金额
     * @return  string data.order_item[].description - 子项描述
     */
    public function calCommonConfigPrice($param)
    {
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	$adminField = $this->hostLinkModel->getAdminField($configData);

    	$newConfigData = [];
    	$newAdminField = [];

        // 试用
        if ($configData['duration']['id'] == config('idcsmart.pay_ontrial')){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
        }

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
    	}
    	$OptionModel = new OptionModel();
    	$ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];

    	$oldPrice = 0;  	// 老价格
    	$price = 0;     	// 新价格
    	$discountPrice = 0; // 可以优惠的总金额
    	$discount = 0; 		// 实际优惠价格
    	$description = []; 	// 描述
    	$orderItem = [];	// 要添加的用户等级子项
        $optional = null;     // 变更后的关联
		$renewDiscountPrice = 0; // 可以优惠的续费价格
		$renewDiscount = 0; // 实际续费优惠价格

        // 检查之前的线路是否还存在
    	$line = LineModel::where('id', $configData['line']['id'])->find();
    	if(empty($line)){
    		// 不支持bw/flow/peak_defence升降机
    		if($configData['line']['bill_type'] == 'bw' && isset($param['bw']) && is_numeric($param['bw']) && $param['bw'] != $adminField['bw']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_bw_upgrade')];
    		}
    		if($configData['line']['bill_type'] == 'flow' && isset($param['flow']) && is_numeric($param['flow']) && $param['flow'] != $adminField['flow']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_flow_upgrade')];
    		}
    		if(isset($param['peak_defence']) && isset($configData['defence']['value']) && is_numeric($param['peak_defence']) && $param['peak_defence'] != $adminField['defence']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_defence_upgrade')];
    		}

    		// 线路的都不能升级
    		$param['bw'] = null;
    		$param['flow'] = null;
    		$param['peak_defence'] = null;
    	}else{
			// 固定机型
			$modelConfig = ModelConfigModel::find($configData['model_config']['id'] ?? 0);
			if(!empty($modelConfig) && $modelConfig['support_optional'] == 1){
                $memoryUsed = 0;
                $memorySlotUsed = 0;
                $diskUsed = 0;
                $gpuUsed = 0;

				$HostOptionLinkModel = new HostOptionLinkModel();
				$oldOptional = $HostOptionLinkModel->getHostOptional($hostId);

				$oldMemoryPrice = 0;
				$newMemoryPrice = 0;
				$oldMemoryDesc = [];
				$newMemoryDesc = [];
				$oldOptionalMemory = [];
				$newOptionalMemory = [];
                $adminFieldMemory = [
                	$modelConfig['memory'],
                ];
                $adminFieldDisk = [
                	$modelConfig['disk'],
                ];
                $adminFieldGpu = [];
                if(!empty($modelConfig['gpu'])){
                	$adminFieldGpu[] = $modelConfig['gpu'];
                }
				if(!empty($oldOptional['optional_memory'])){
					foreach($oldOptional['optional_memory'] as $v){
						$num = $v['num'];
						$oldOptionalMemory[ $v['option_id'] ] = $num;
						$memoryPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['option_id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $oldMemoryPrice = bcadd($oldMemoryPrice, bcmul($memoryPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];

                        $oldMemoryDesc[] = sprintf('%s_%d', $langValue, $num);
					}
				}
				// 是否选配了内存
                if(isset($param['optional_memory']) && !empty($param['optional_memory']) && is_array($param['optional_memory'])){
                    $optionalMemoryId = array_keys($param['optional_memory']);

                    $optionalMemory = ModelConfigOptionLinkModel::alias('mcol')
                                    ->field('o.id,o.value,o.other_config')
                                    ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                    ->where('mcol.model_config_id', $modelConfig['id'])
                                    ->whereIn('mcol.option_id', $optionalMemoryId)
                                    ->where('mcol.option_rel_type', OptionModel::MEMORY)
                                    ->order('o.order,o.id', 'asc')
                                    ->select()
                                    ->toArray();
                    if(count($optionalMemoryId) != count($optionalMemory)){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
                    }

                    $optional = $optional ?? [];
                    foreach($optionalMemory as $v){
                        $v['other_config'] = json_decode($v['other_config'], true);
                        $num = (int)$param['optional_memory'][ $v['id'] ];
                        if($num <= 0){
                            continue;
                        }
                        $newOptionalMemory[ $v['id'] ] = $num;

                        $optional[] = [
                            'host_id'   => $hostId,
                            'option_id' => $v['id'],
                            'num'       => $num,
                        ];

                        $memoryUsed += $v['other_config']['memory'] * $num;
                        $memorySlotUsed += $v['other_config']['memory_slot'] * $num;

                        $memoryPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $newMemoryPrice = bcadd($newMemoryPrice, bcmul($memoryPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];
                        $newMemoryDesc[] = sprintf('%s_%d', $langValue, $num);
                        $adminFieldMemory[] = sprintf('%s_%d', $v['value'], $num);
                    }
                    if($memoryUsed > $modelConfig['leave_memory']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_max')];
                    }
                    if($memorySlotUsed > $modelConfig['max_memory_num']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_num_max')];
                    }
                }
                // 内存是否变更
                ksort($oldOptionalMemory);
                ksort($newOptionalMemory);
                if(json_encode($oldOptionalMemory) != json_encode($newOptionalMemory)){
                	$oldPrice = bcadd($oldPrice, $oldMemoryPrice);
            		$price    = bcadd($price, $newMemoryPrice);
            		if($config['level_discount_memory_upgrade'] == 1){
            			$discountPrice = bcadd($discountPrice, bcsub($newMemoryPrice, $oldMemoryPrice));
            		}
					if($config['level_discount_memory_renew'] == 1){
						$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($newMemoryPrice, $oldMemoryPrice));
					}

            		$description[] = sprintf("%s: %s => %s", lang_plugins('mf_dcim_addition_memory'), implode(';', $oldMemoryDesc) ?: lang_plugins('null'), implode(';', $newMemoryDesc) ?: lang_plugins('null'));

                    $newAdminField['memory'] = implode(';', $adminFieldMemory);
                }
                // 硬盘不能减少
                $oldDiskPrice = 0;
				$newDiskPrice = 0;
				$oldDiskDesc = [];
				$newDiskDesc = [];
				$oldOptionalDisk = [];
				$newOptionalDisk = [];

				if(!empty($oldOptional['optional_disk'])){
					foreach($oldOptional['optional_disk'] as $v){
						$num = $v['num'];
						$oldOptionalDisk[ $v['option_id'] ] = $num;
						$diskPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['option_id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $oldDiskPrice = bcadd($oldDiskPrice, bcmul($diskPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];

                        $oldDiskDesc[] = sprintf('%s_%d', $langValue, $num);
					}
				}
                if(isset($param['optional_disk']) && !empty($param['optional_disk']) && is_array($param['optional_disk'])){
                    $optionalDiskId = array_keys($param['optional_disk']);

                    $optionalDisk = ModelConfigOptionLinkModel::alias('mcol')
                                    ->field('o.id,o.value,o.other_config')
                                    ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                    ->where('mcol.model_config_id', $modelConfig['id'])
                                    ->whereIn('mcol.option_id', $optionalDiskId)
                                    ->where('mcol.option_rel_type', OptionModel::DISK)
                                    ->order('o.order,o.id', 'asc')
                                    ->select()
                                    ->toArray();
                    if(count($optionalDiskId) != count($optionalDisk)){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
                    }

                    $optional = $optional ?? [];
                    foreach($optionalDisk as $v){
                        $v['other_config'] = json_decode($v['other_config'], true);
                        $num = (int)$param['optional_disk'][ $v['id'] ];
                        if($num <= 0){
                            continue;
                        }
                        $newOptionalDisk[ $v['id'] ] = $num;

                        $optional[] = [
                            'host_id'   => $hostId,
                            'option_id' => $v['id'],
                            'num'       => $num,
                        ];

                        $diskUsed += $num;

                        $diskPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $newDiskPrice = bcadd($newDiskPrice, bcmul($diskPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];

                        $newDiskDesc[] = sprintf('%s_%d', $langValue, $num);
                        $adminFieldDisk[] = sprintf('%s_%d', $v['value'], $num);
                    }
                    if($diskUsed > $modelConfig['max_disk_num']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_disk_num_max')];
                    }
                }
                // 硬盘是否变更
                ksort($oldOptionalDisk);
                ksort($newOptionalDisk);
                if(json_encode($oldOptionalDisk) != json_encode($newOptionalDisk)){
                	// 硬盘不能减少
                	foreach($oldOptionalDisk as $optionId=>$num){
                		if(!isset($newOptionalDisk[$optionId]) || $num > $newOptionalDisk[$optionId]){
                			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_cannot_reduce')];
                		}
                	}

                	$oldPrice = bcadd($oldPrice, $oldDiskPrice);
            		$price    = bcadd($price, $newDiskPrice);
            		if($config['level_discount_disk_upgrade'] == 1){
            			$discountPrice = bcadd($discountPrice, bcsub($newDiskPrice, $oldDiskPrice));
            		}
					if($config['level_discount_disk_renew'] == 1){
						$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($newDiskPrice, $oldDiskPrice));
					}
            		$description[] = sprintf("%s: %s => %s", lang_plugins('mf_dcim_addition_disk'), implode(';', $oldDiskDesc) ?: lang_plugins('null'), implode(';', $newDiskDesc) ?: lang_plugins('null'));

                    $newAdminField['disk'] = implode(';', $adminFieldDisk);
                }
                // 显卡
                $oldGpuPrice = 0;
				$newGpuPrice = 0;
				$oldGpuDesc = [];
				$newGpuDesc = [];
				$oldOptionalGpu = [];
				$newOptionalGpu = [];

				if(!empty($oldOptional['optional_gpu'])){
					foreach($oldOptional['optional_gpu'] as $v){
						$num = $v['num'];
						$oldOptionalGpu[ $v['option_id'] ] = $num;
						$gpuPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['option_id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $oldGpuPrice = bcadd($oldGpuPrice, bcmul($gpuPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];

                        $oldGpuDesc[] = sprintf('%s_%d', $langValue, $num);
					}
				}
                if(isset($param['optional_gpu']) && !empty($param['optional_gpu']) && is_array($param['optional_gpu'])){
                    $optionalGpuId = array_keys($param['optional_gpu']);

                    $optionalGpu = ModelConfigOptionLinkModel::alias('mcol')
                                    ->field('o.id,o.value,o.other_config')
                                    ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                    ->where('mcol.model_config_id', $modelConfig['id'])
                                    ->whereIn('mcol.option_id', $optionalGpuId)
                                    ->where('mcol.option_rel_type', OptionModel::GPU)
                                    ->order('o.order,o.id', 'asc')
                                    ->select()
                                    ->toArray();
                    if(count($optionalGpuId) != count($optionalGpu)){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_gpu_optional_not_found')];
                    }

                    $optional = $optional ?? [];
                    foreach($optionalGpu as $v){
                        $v['other_config'] = json_decode($v['other_config'], true);
                        $num = (int)$param['optional_gpu'][ $v['id'] ];
                        if($num <= 0){
                            continue;
                        }
                        $newOptionalGpu[ $v['id'] ] = $num;

                        $optional[] = [
                            'host_id'   => $hostId,
                            'option_id' => $v['id'],
                            'num'       => $num,
                        ];

                        $gpuUsed += $num;

                        $gpuPrice = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                        $newGpuPrice = bcadd($newGpuPrice, bcmul($gpuPrice, $num));

                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $v['value'],
                            ],
                        ]);
                        $langValue = $multiLanguage['value'] ?? $v['value'];

                        $newGpuDesc[] = sprintf('%s_%d', $langValue, $num);
                        $adminFieldGpu[] = sprintf('%s_%d', $v['value'], $num);
                    }
                    if($gpuUsed > $modelConfig['max_gpu_num']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_gpu_num_max')];
                    }
                }
                // 是否变更
                ksort($oldOptionalGpu);
                ksort($newOptionalGpu);
                if(json_encode($oldOptionalGpu) != json_encode($newOptionalGpu)){
                	$oldPrice = bcadd($oldPrice, $oldGpuPrice);
            		$price    = bcadd($price, $newGpuPrice);
            		if($config['level_discount_gpu_upgrade'] == 1){
            			$discountPrice = bcadd($discountPrice, bcsub($newGpuPrice, $oldGpuPrice));
            		}
					if($config['level_discount_gpu_renew'] == 1){
						$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($newGpuPrice, $oldGpuPrice));
					}
            		$description[] = sprintf("%s: %s => %s", lang_plugins('mf_dcim_addition_gpu'), implode(';', $oldGpuDesc) ?: lang_plugins('null'), implode(';', $newGpuDesc) ?: lang_plugins('null'));

                    $newAdminField['gpu'] = implode(';', $adminFieldGpu);
                }

			}
    		
    		// 线路存在的情况
    		if($line['bill_type'] == 'bw'){
    			$param['flow'] = null;
                // 获取带宽周期价格
                if(isset($param['bw']) && !empty($param['bw']) && $param['bw'] != $adminField['bw']){
                	$calBw = true;
                	// 灵活机型逻辑
	            	// if(!empty($this->hostLinkModel['package_id'])){
	            	// 	// 当前配置是否存在
	            	// 	$currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $adminField['bw'], $duration['id']);
	            	// 	if($currentOptionDurationPrice['match']){
	            	// 		if(!empty($package) && (int)$param['bw'] < $package['bw']){
	            	// 			$calBw = false;
	            	// 			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_upgrade_bw_range_error', ['{bw}'=>$package['bw']] )];
	            	// 		}
	            	// 	}else{
	            	// 		$calBw = false;
	            	// 	}
	            	// }
	            	if($calBw){
	            		$optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $duration['id']);
	                    if(!$optionDurationPrice['match']){
	                    	return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_bw_error') ];
	                    }else{
	                    	$optionDurationPrice['price'] = $optionDurationPrice['price'] ?? 0;

	                    	$preview[] = [
		                        'name'  => lang_plugins('mf_dcim_bw'),
		                        'value' => $param['bw'],
		                        'price' => $optionDurationPrice['price'],
		                    ];

		                    $newConfigData['bw'] = [
		                        'value' => $param['bw'],
		                        'price' => $optionDurationPrice['price'],
		                        'other_config' => $optionDurationPrice['option']['other_config'],
		                    ];
		                    $newAdminField['bw'] = $param['bw'];
		                    $newAdminField['in_bw'] = $optionDurationPrice['option']['other_config']['in_bw'] ?? '';

		                    $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $adminField['bw'], $duration['id']);

		                    $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
		            		$price    = bcadd($price, $optionDurationPrice['price']);
		            		if($config['level_discount_bw_upgrade'] == 1){
		            			$discountPrice = bcadd($discountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
		            		}
							if($config['level_discount_bw_renew'] == 1){
								$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
							}

		            		$description[] = sprintf("%s: %d => %d", lang_plugins('mf_dcim_bw'), $adminField['bw'], $param['bw']);
	                    }
	            	}
                }
            }else if($line['bill_type'] == 'flow'){
            	$param['bw'] = null;
                // 获取流量周期价格
                if(empty($this->hostLinkModel['package_id']) && isset($param['flow']) && is_numeric($param['flow']) && $param['flow'] >= 0 && $param['flow'] != $adminField['flow']){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_flow_not_found') ];
                    }
                    $optionDurationPrice['price'] = $optionDurationPrice['price'] ?? 0;
                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_flow'),
                        'value' => $param['flow'],
                        'price' => $optionDurationPrice['price'],
                    ];

                    $newConfigData['flow'] = [
                        'value' => $param['flow'],
                        'price' => $optionDurationPrice['price'],
                        'other_config' => $optionDurationPrice['option']['other_config'],
                    ];

                    $newAdminField['flow'] = $param['flow'];
                    $newAdminField['bw'] = $optionDurationPrice['option']['other_config']['out_bw'];
                    $newAdminField['in_bw'] = $optionDurationPrice['option']['other_config']['in_bw'];

                    $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $adminField['flow'], $duration['id']);

                    $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            		$price    = bcadd($price, $optionDurationPrice['price']);
	            	$discountPrice = bcadd($discountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
					$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));

            		$description[] = sprintf("%s: %d => %d", lang_plugins('mf_dcim_flow'), $adminField['flow'], $param['flow']);
                }
            }
            // 防护
            if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] != 1 && isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0 && $param['peak_defence'] != $adminField['defence']){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
                }
                $optionDurationPrice['price'] = $optionDurationPrice['price'] ?? 0;
                $preview[] = [
                    'name'  => lang_plugins('mf_dcim_peak_defence'),
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'],
                ];

                $newConfigData['defence'] = [
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'],
                ];
                $newAdminField['defence'] = $param['peak_defence'];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $adminField['defence'], $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            	$price    = bcadd($price, $optionDurationPrice['price']);
            	$discountPrice = bcadd($discountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
				$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));

            	$description[] = sprintf("%s: %d => %d", lang_plugins('mf_dcim_peak_defence'), $adminField['defence'], $param['peak_defence']);
            }
            // 公网IP
            if(isset($param['ip_num']) && !empty($param['ip_num']) && $param['ip_num'] != $adminField['ip_num']){
            	$calIpNum = true;
            	// 灵活机型逻辑
            	// if(!empty($this->hostLinkModel['package_id'])){
            	// 	// 当前配置是否存在
            	// 	$currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $adminField['ip_num'], $duration['id']);
            	// 	if($currentOptionDurationPrice['match']){
            	// 		$ipNum = 0;
            	// 		if(is_numeric($param['ip_num'])){
            	// 			$ipNum = $param['ip_num'];
            	// 		}else if($param['ip_num'] == 'NC'){

            	// 		}else{
            	// 			$ipNumArr = explode(',', $param['ip_num']);
            	// 			foreach($ipNumArr as $v){
            	// 				$ipNum += (int)$v;
            	// 			}
            	// 		}
            	// 		if(!empty($package) && $ipNum < $package['ip_num']){
            	// 			$calIpNum = false;
            	// 			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_upgrade_ip_num_range_error', ['{ip_num}'=>$package['ip_num']])];
            	// 		}
            	// 	}else{
            	// 		$calIpNum = false;
            	// 	}
            	// }
            	if($calIpNum){
            		$optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
	                if(!$optionDurationPrice['match']){
	                	return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_not_found') ];
	                }else{
                        if(ToolLogic::getIpNum($param['ip_num'])<ToolLogic::getIpNum($adminField['ip_num'])){
                            if(!empty($param['ip'])){
                                if(count($param['ip'])!=(ToolLogic::getIpNum($adminField['ip_num'])-ToolLogic::getIpNum($param['ip_num']))){
                                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_select_ip_downgrade_not_enough') ];
                                }
                                $HostIpModel = new HostIpModel();
                                $hostIp = $HostIpModel->getHostIp([
                                    'host_id'	=> $hostId,
                                ]);
                                $enableIp = [];
                                if(!empty($hostIp['dedicate_ip']) && filter_var($hostIp['dedicate_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                                    $enableIp[] = $hostIp['dedicate_ip'];
                                }
                                $hostIp['assign_ip'] = explode(',', $hostIp['assign_ip']);
                                foreach($hostIp['assign_ip'] as $ip){
                                    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                                        $enableIp[] = $ip;
                                    }
                                }
                                $removeIp = array_intersect($enableIp, $param['ip']);
                                if(count($removeIp) != count($param['ip'])){
                                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_select_ip_downgrade_not_enough') ];
                                }

                            }
                        }

	                	$optionDurationPrice['price'] = $optionDurationPrice['price'] ?? 0;
	                	$preview[] = [
		                    'name'  => lang_plugins('mf_dcim_public_ip_num'),
		                    'value' => $param['ip_num'] . lang_plugins('mf_dcim_indivual'),
		                    'price' => $optionDurationPrice['price'],
		                ];

		                $newConfigData['ip'] = [
		                    'value' => $param['ip_num'],
		                    'price' => $optionDurationPrice['price'],
		                ];
		                $newAdminField['ip_num'] = $param['ip_num'];

		                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $adminField['ip_num'], $duration['id']);

                        // 升级IP默认订购防御
//                        if($line['defence_enable'] == 1 && $line['sync_firewall_rule']==1){
//                            if(strpos($param['ip_num'], '_') !== false && strpos($adminField['ip_num'], '_') !== false){
//                                $ipNum = explode(',', $param['ip_num']);
//                                $newIpGroup = [];
//                                foreach($ipNum as $vv){
//                                    $vv = explode('_', $vv);
//                                    $newIpGroup[$vv[1]] = $vv[0];
//                                }
//                                $ipNum = explode(',', $adminField['ip_num']);
//                                $oldIpGroup = [];
//                                foreach($ipNum as $vv){
//                                    $vv = explode('_', $vv);
//                                    $oldIpGroup[$vv[1]] = $vv[0];
//                                }
//
//                                foreach ($oldIpGroup as $k => $v) {
//                                    if(!isset($newIpGroup[$k])){
//                                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num') ];
//                                    }
//                                    if($newIpGroup[$k]<$oldIpGroup[$k]){
//                                        if(count($oldIpGroup)==1 && count($newIpGroup)==1){
//                                            if(!empty($param['ip'])){
//                                                $IpDefenceModel = new IpDefenceModel();
//                                                $ipDefence = $IpDefenceModel->whereIn('ip', $param['ip'])->select()->toArray();
//                                                foreach ($ipDefence as $v1){
//                                                    $firewallOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $v1['defence'], $duration['id']);
//                                                    if(!$firewallOptionDurationPrice['match']){
//                                                        continue;
//                                                    }
//                                                    $price = bcsub($price, $firewallOptionDurationPrice['price']);
//                                                }
//                                            }else{
//                                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num') ];
//                                            }
//                                        }else{
//                                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num') ];
//                                        }
//                                    }
//                                }
//
//                            }else if(strpos($param['ip_num'], '_') !== false && strpos($adminField['ip_num'], '_') === false){
//                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num') ];
//                            }else{
//                                if(ToolLogic::getIpNum($param['ip_num'])<ToolLogic::getIpNum($adminField['ip_num'])){
//                                    if(!empty($param['ip'])){
//                                        $IpDefenceModel = new IpDefenceModel();
//                                        $ipDefence = $IpDefenceModel->whereIn('ip', $param['ip'])->select()->toArray();
//                                        foreach ($ipDefence as $v){
//                                            $firewallOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $v['defence'], $duration['id']);
//                                            if(!$firewallOptionDurationPrice['match']){
//                                                continue;
//                                            }
//                                            $price = bcsub($price, $firewallOptionDurationPrice['price']);
//                                        }
//                                    }else{
//                                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num') ];
//                                    }
//                                }
//                            }
//
//                            if(ToolLogic::getIpNum($param['ip_num'])>ToolLogic::getIpNum($adminField['ip_num'])){
//                                $firewallOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $line['order_default_defence'], $duration['id']);
//                                if(!$firewallOptionDurationPrice['match']){
//                                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
//                                }
//                                $price = bcadd($price, bcmul($firewallOptionDurationPrice['price'], ToolLogic::getIpNum($param['ip_num'])-ToolLogic::getIpNum($adminField['ip_num'])));
//
//                                $newConfigData['default_defence'] = [
//                                    'value'             => $firewallOptionDurationPrice['option']['value'],
//                                    'firewall_type'     => $firewallOptionDurationPrice['option']['firewall_type'],
//                                    'defence_rule_id'   => $firewallOptionDurationPrice['option']['defence_rule_id'],
//                                ];
//                            }
//                        }

		                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
		            	$price    = bcadd($price, $optionDurationPrice['price']);

		            	if($config['level_discount_ip_num_upgrade'] == 1){
		            		$discountPrice = bcadd($discountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
		            	}
						if($config['level_discount_ip_num_renew'] == 1){
							$renewDiscountPrice = bcadd($renewDiscountPrice, bcsub($optionDurationPrice['price'], $currentOptionDurationPrice['price'] ?? 0));
						}

		            	$description[] = sprintf("%s: %s => %s", lang_plugins('mf_dcim_public_ip_num'), ToolLogic::getIpNumShow($adminField['ip_num'], $currentOptionDurationPrice['option']['value_show'] ?? ''), ToolLogic::getIpNumShow($param['ip_num'], $optionDurationPrice['option']['value_show'] ?? ''));
	                }
            	}
            }
    	}
    	if(empty($newConfigData) && empty($newAdminField)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_change_config')];
    	}

    	// 需要验证限制规则
    	if(isset($param['check_limit_rule']) && $param['check_limit_rule'] == 1){
    		$configData = json_decode($this->hostLinkModel['config_data'], true);

	        $currentConfig = $this->hostLinkModel->currentConfig($this->hostModel['id']);
			$currentConfig['ip_num'] = $param['ip_num'] ?? '';
			$currentConfig['bw'] = $param['bw'] ?? '';
			$currentConfig['flow'] = $param['flow'] ?? '';
			$currentConfig['peak_defence'] = $param['peak_defence'] ?? '';
	        
	    	$LimitRuleModel = new LimitRuleModel();
	        $checkLimitRule = $LimitRuleModel->checkLimitRule($this->hostModel['product_id'], $currentConfig, ['bw','flow','ipv4_num']);
	        if($checkLimitRule['status'] == 400){
	        	return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_upgrade_common_config_for_limit_rule')];
	        }
    	}

    	// 计算价格系数
    	if($duration['price_factor'] != 1){
    		$oldPrice = bcmul($oldPrice, $duration['price_factor']);
	    	$price = bcmul($price, $duration['price_factor']);
	    	$discountPrice = bcmul($discountPrice, $duration['price_factor']);
	    	$renewDiscountPrice = bcmul($renewDiscountPrice, $duration['price_factor']);

	    	foreach($newConfigData as $k=>$v){
	    		if(isset($v['price'])){
	    			$newConfigData[$k]['price'] = bcmul($v['price'], $duration['price_factor']);
	    		}
	    	}
    	}

    	$oriDiscountPrice = $discountPrice;
        $description = implode("\r\n", $description);
        $priceDifference = bcsub($price, $oldPrice, 2);
        $renewPriceDifference = $priceDifference;
        if($this->hostModel['billing_cycle_time']>0){
        	$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
        	$discountPrice = $discountPrice * $diffTime/$this->hostModel['billing_cycle_time'];
        }else{
        	$price = $priceDifference;
        }
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        $basePrice = bcadd($this->hostModel['base_price'],$priceDifference,2);
		
		$DurationModel = new DurationModel();
		$clientLevel = $DurationModel->getClientLevel([
            'product_id'    => $productId,
            'client_id'     => get_client_id(),
        ]);
        if (!$priceAgent){
            $clientLevel = [];
        }
        if(!empty($clientLevel)){
        	$discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
        	$oriDiscount = bcdiv($oriDiscountPrice*$clientLevel['discount_percent'], 100, 2);
        	$renewDiscount = bcdiv($renewDiscountPrice*$clientLevel['discount_percent'], 100, 2);
            $basePriceDiscount = bcdiv($basePrice*$clientLevel['discount_percent'], 100, 2);

        	$orderItem[] = [
                'type'          => 'addon_idcsmart_client_level',
                'rel_id'        => $clientLevel['id'],
                'amount'        => min(-$discount, 0),
                'description'   => lang_plugins('mf_dcim_client_level', [
                    '{name}'    => $clientLevel['name'],
                    '{host_id}' => $hostId,
                    '{value}'   => $clientLevel['discount_percent'],
                ]),
            ];

            $price = bcsub($price, $discount);

            // wyh 20240530 记录，续费差价根据升降级处理（需要等反馈）
            // $renewPriceDifference = bcsub($renewPriceDifference, $renewDiscount);

            if (isset($param['is_downstream']) && $param['is_downstream']==1){
                $basePrice = bcsub($basePrice,$basePriceDiscount,2);
            }
        }

        $realPriceDifference = $price;

        $price = max(0, $price);
        $price = amount_format($price);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 				=> $price,
                'description' 			=> $description,
                'price_difference' 		=> $realPriceDifference,
                'renew_price_difference'=> $renewPriceDifference,
                'new_config_data'		=> $newConfigData,
                'new_admin_field'		=> $newAdminField,
                'optional'              => $optional ?? [],
                'discount'				=> max($discount, 0),
                'order_item'			=> $orderItem,
                'base_price'            => $basePrice,
                'ip'                    => $param['ip'] ?? [],
				'renew_price_difference_client_level_discount' => $renewDiscount,
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
	 * @param   int param.ip_num - 公网IP数量
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量包
     * @param   int param.peak_defence - 防御峰值
     * @param   array param.optional_memory - 变更后的内存(["5"=>1],5是ID,1是数量)
     * @param   array param.optional_disk - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   array param.optional_gpu - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
	 * @return  string data.id - 订单ID
     */
    public function createCommonConfigOrder($param)
    {
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $param['id'],
            'scene_desc'=> lang_plugins('mf_dcim_upgrade_scene_change_config'),
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

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     			=> $param['id'],
            'client_id'   			=> get_client_id(),
            'type'        			=> 'upgrade_config',
            'amount'      			=> $res['data']['price'],
            'description' 			=> $res['data']['description'],
            'price_difference' 		=> $res['data']['price_difference'],
            'renew_price_difference'=> $res['data']['renew_price_difference'],
            'base_price'            => $res['data']['base_price'],
            'upgrade_refund' 		=> 0,
            'config_options' 		=> [
                'type'       		=> 'upgrade_common_config',
                'new_config_data'   => $res['data']['new_config_data'],
                'new_admin_field'   => $res['data']['new_admin_field'],
                'optional'          => $res['data']['optional'],
                'ip'                => $res['data']['ip'] ?? [],
            ],
            'customfield'           => $param['customfield'] ?? [],
            'order_item'	        => $res['data']['order_item'],
            'discount'				=> $res['data']['discount'],
			'renew_discount_price_difference' => $res['data']['renew_discount_price_difference'],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2025-01-15
     * @title 计算升级防御价格
     * @desc  计算升级防御价格
     * @author theworld
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

        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';

        $parentHostId = $this->hostLinkModel['parent_host_id'];

        $configData = json_decode($this->hostLinkModel['config_data'], true);

        // 试用
        if ($configData['duration']['id'] == config('idcsmart.pay_ontrial')){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
        }

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
        if(empty($duration)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
        }
        $OptionModel = new OptionModel();

        $oldPrice = 0;
        $price = 0;
        $defence = [];

        // 检查之前的线路是否还存在
        $line = LineModel::where('id', $configData['line']['id'])->find();
        if(empty($line) || $line['defence_enable'] == 0 || $line['sync_firewall_rule'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_host_ip_defence_upgrade')];
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
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_host_ip_defence_upgrade')];
            }
            $dedicateIp = $hostIp['dedicate_ip'];
            $assignIp = array_filter(explode(',', $hostIp['assign_ip']));
            if($dedicateIp!=$param['ip'] && !in_array($param['ip'], $assignIp)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_host_ip_defence_upgrade')];
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
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_change_config')];
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
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
            }

            $ConfigModel = new ConfigModel();
            $rule = $ConfigModel->getFirewallDefenceRule([
                'product_id'        => $productId,
                'firewall_type'     => $optionDurationPrice['option']['firewall_type'],
                'defence_rule_id'   => $optionDurationPrice['option']['defence_rule_id'],
            ]);
            if(empty($rule)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
            }

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

            $description = lang_plugins('mf_dcim_upgrade_defence', [
                '{ip}'  => $param['ip'],
                '{old}' => $old,
                '{new}' => $rule['defense_peak'],
            ]);

            $defence = ['value' => $optionDurationPrice['option']['value'], 'firewall_type' => $optionDurationPrice['option']['firewall_type'], 'defence_rule_id' => $optionDurationPrice['option']['defence_rule_id']];
        }
        if(empty($defence)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_change_config')];
        }

        // 若当前ip为默认防御，需返回周期，以及周期原价
        $upgradeWithDuration = false;
        $isDefaultDefence = $param['peak_defence']==$line['order_default_defence'];
//        $durationTime = 0;
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
            ],false,0,true);
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
     * @author theworld
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
            'scene_desc'=> lang_plugins('mf_dcim_upgrade_scene_change_config'),
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
            'host_id'           => $host['id'],
            'data_center_id'    => $parentHostLink['data_center_id'] ?? 0,
            'image_id'          => $parentHostLink['image_id'],
            // 'power_status'      => 'on',
            'config_data'       => json_encode($subConfigData),
            'create_time'       => time(),
            'package_id'        => 0,
            'additional_ip'     => '',
            'parent_host_id'    => $this->hostModel['id'],
            'ip'                => $param['ip']??'',
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        
        // 手动资源模式
        if($config['data']['manual_resource']==1 && $this->hostLinkModel->isEnableManualResource()){
            // 手动资源模式下返回非重装状态
            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => []
            ];
        }
        
        // 下游模式
        if($this->downstreamHostLogic->isDownstream()){
            return $this->downstreamHostLogic->getReinstallStatus();
        }
        
        // 直连模式
        return $this->idcsmartCloud->getReinstallStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
    }
}

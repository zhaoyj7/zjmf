<?php

use app\common\model\HostModel;
use app\common\model\ServerModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\idcsmart_dcim\Dcim;
use think\db\exception\PDOException;
use server\mf_dcim\model\DataCenterModel;
use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\model\ImageGroupModel;
use server\mf_dcim\model\ImageModel;
use server\mf_dcim\model\LineModel;
use server\mf_dcim\model\ModelConfigModel;
use server\mf_dcim\model\OptionModel;
use server\mf_dcim\model\PriceModel;
use server\mf_dcim\model\DurationRatioModel;
use server\mf_dcim\model\HostImageLinkModel;
use server\mf_dcim\model\ModelConfigOptionLinkModel;
use server\mf_dcim\model\LimitRuleModel;
use server\mf_dcim\model\IpDefenceModel;
use app\common\model\OrderItemModel;
use app\common\model\HostNoticeModel;

use server\mf_dcim\logic\CloudLogic;

// 商品删除后
add_hook('after_product_delete', function($param){
	if(!isset($param['module']) || $param['module'] != 'mf_dcim'){
		return false;
	}
	try{
		$dataCenterId = DataCenterModel::where('product_id', $param['id'])->column('id');
        $imageId = ImageModel::where('product_id', $param['id'])->column('id');
		$modelConfigId = ModelConfigModel::where('product_id', $param['id'])->column('id');

		ConfigModel::where('product_id', $param['id'])->delete();
		DataCenterModel::where('product_id', $param['id'])->delete();
		DurationModel::where('product_id', $param['id'])->delete();
		ImageModel::where('product_id', $param['id'])->delete();
		ImageGroupModel::where('product_id', $param['id'])->delete();
		if(!empty($dataCenterId)){
			LineModel::whereIn('data_center_id', $dataCenterId)->delete();
		}
		if(!empty($imageId)){
			HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		}
		ModelConfigModel::where('product_id', $param['id'])->delete();
		OptionModel::where('product_id', $param['id'])->delete();
		PriceModel::where('product_id', $param['id'])->delete();
		DurationRatioModel::where('product_id', $param['id'])->delete();
		LimitRuleModel::where('product_id', $param['id'])->delete();
        if(!empty($modelConfigId)){
        	ModelConfigOptionLinkModel::whereIn('model_config_id', $modelConfigId)->delete();
        }
	}catch(\PDOException $e){
		
	}catch(\Exception $e){

	}
});

// 产品删除后
add_hook('after_host_delete', function($param){
	if(isset($param['module']) && $param['module'] == 'mf_dcim'){
		HostLinkModel::where('host_id', $param['id'])->delete();
		IpDefenceModel::where('host_id', $param['id'])->delete();
	}
});

// 商品复制后
add_hook('after_product_copy', function($param){
	try{
		$DurationModel = new DurationModel();
		$duration = $DurationModel->where('product_id', $param['product_id'])->select()->toArray();
		if(!empty($duration)){
			$durationIdArr = [];
			foreach ($duration as $key => $value) {
				$id = $value['id'];
				$durationIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $DurationModel->create($value);
				$durationIdArr[$id] = $r->id;
			}

			$DataCenterModel = new DataCenterModel();
			$dataCenter = $DataCenterModel->where('product_id', $param['product_id'])->select()->toArray();
			$dataCenterIdArr = [];
			foreach ($dataCenter as $key => $value) {
				$id = $value['id'];
				$dataCenterIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $DataCenterModel->create($value);
				$dataCenterIdArr[$id] = $r->id;
			}

			$ConfigModel = new ConfigModel();
			$config = $ConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$configIdArr = [];
			foreach ($config as $key => $value) {
				$id = $value['id'];
				$configIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ConfigModel->create($value);
				$configIdArr[$id] = $r->id;
			}

			$ImageGroupModel = new ImageGroupModel();
			$imageGroup = $ImageGroupModel->where('product_id', $param['product_id'])->select()->toArray();
			$imageGroupIdArr = [];
			foreach ($imageGroup as $key => $value) {
				$id = $value['id'];
				$imageGroupIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ImageGroupModel->create($value);
				$imageGroupIdArr[$id] = $r->id;
			}

			$ImageModel = new ImageModel();
			$image = $ImageModel->where('product_id', $param['product_id'])->select()->toArray();
			$imageIdArr = [];
			foreach ($image as $key => $value) {
				$id = $value['id'];
				$imageIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$value['image_group_id'] = $imageGroupIdArr[$value['image_group_id']] ?? 0;
				$r = $ImageModel->create($value);
				$imageIdArr[$id] = $r->id;
			}

			$LineModel = new LineModel();
			$line = $LineModel->whereIn('data_center_id', array_keys($dataCenterIdArr))->select()->toArray();
			$lineIdArr = [];
			foreach ($line as $key => $value) {
				$id = $value['id'];
				$lineIdArr[$id] = 0;
				unset($value['id']);
				//$value['product_id'] = $param['id'];
				$value['data_center_id'] = $dataCenterIdArr[$value['data_center_id']] ?? 0;
				$r = $LineModel->create($value);
				$lineIdArr[$id] = $r->id;
			}

			$ModelConfigModel = new ModelConfigModel();
			$modelConfig = $ModelConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$modelConfigIdArr = [];
			foreach ($modelConfig as $key => $value) {
				$id = $value['id'];
				$modelConfigIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ModelConfigModel->create($value);
				$modelConfigIdArr[$id] = $r->id;
			}

			$OptionModel = new OptionModel();
			$option = $OptionModel->where('product_id', $param['product_id'])->select()->toArray();
			$optionIdArr = [];
			foreach ($option as $key => $value) {
				$id = $value['id'];
				$optionIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				if(in_array($value['rel_type'], [2, 3, 4, 5])){
					$value['rel_id'] = $lineIdArr[$value['rel_id']] ?? 0;
				}
				$r = $OptionModel->create($value);
				$optionIdArr[$id] = $r->id;
			}

			// 获取周期比例
			$DurationRatioModel = new DurationRatioModel();
			$durationRatio = $DurationRatioModel->where('product_id', $param['product_id'])->select()->toArray();
			foreach ($durationRatio as $key => $value) {
				if(isset($durationIdArr[$value['duration_id']])){
					$value['product_id'] = $param['id'];
					$value['duration_id'] = $durationIdArr[$value['duration_id']];
					$DurationRatioModel->create($value);
				}
			}

			if(!empty($modelConfigIdArr)){
				$ModelConfigOptionLinkModel = new ModelConfigOptionLinkModel();
				$modelConfigOptionLink = $ModelConfigOptionLinkModel->whereIn('model_config_id', array_keys($modelConfigIdArr))->select()->toArray();
				foreach ($modelConfigOptionLink as $key => $value) {
                    $value['model_config_id'] = $modelConfigIdArr[$value['model_config_id']] ?? 0;
					$value['option_id'] = $optionIdArr[$value['option_id']] ?? 0;
					$ModelConfigOptionLinkModel->create($value);
				}
			}

            $PriceModel = new PriceModel();
            $price = $PriceModel->where('product_id', $param['product_id'])->select()->toArray();
            $priceIdArr = [];
            foreach ($price as $key => $value) {
                $id = $value['id'];
                $priceIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                if($value['rel_type']=='option'){
                    $value['rel_id'] = $optionIdArr[$value['rel_id']] ?? 0;
                }else if($value['rel_type']=='model_config'){
                    $value['rel_id'] = $modelConfigIdArr[$value['rel_id']] ?? 0;
                }else if($value['rel_type']=='package'){
                	continue;
                }
                $value['duration_id'] = $durationIdArr[$value['duration_id']] ?? 0;
                $r = $PriceModel->create($value);
                $priceIdArr[$id] = $r->id;
            }

            // 限制规则
			$LimitRuleModel = new LimitRuleModel();
			$limitRule = $LimitRuleModel->where('product_id', $param['product_id'])->select()->toArray();
			foreach ($limitRule as $key => $value) {
				$id = $value['id'];
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$value['rule'] = json_decode($value['rule'], true);
				$value['result'] = json_decode($value['result'], true);
                if(isset($value['rule']['data_center']['id'])){
                    foreach($value['rule']['data_center']['id'] as $kk=>$vv){
                        if(isset($dataCenterIdArr[$vv])){
                            $value['rule']['data_center']['id'][$kk] = (int)$dataCenterIdArr[$vv];
                        }else{
                            unset($value['rule']['data_center']['id'][$kk]);
                        }
                    }
                    $value['rule']['data_center']['id'] = array_values($value['rule']['data_center']['id']);
                }
                if(isset($value['rule']['image']['id'])){
                    foreach($value['rule']['image']['id'] as $kk=>$vv){
                        if(isset($imageIdArr[$vv])){
                            $value['rule']['image']['id'][$kk] = (int)$imageIdArr[$vv];
                        }else{
                            unset($value['rule']['image']['id'][$kk]);
                        }
                    }
                    $value['rule']['image']['id'] = array_values($value['rule']['image']['id']);
                }
                if(isset($value['rule']['model_config']['id'])){
                    foreach($value['rule']['model_config']['id'] as $kk=>$vv){
                        if(isset($modelConfigIdArr[$vv])){
                            $value['rule']['model_config']['id'][$kk] = (int)$modelConfigIdArr[$vv];
                        }else{
                            unset($value['rule']['model_config']['id'][$kk]);
                        }
                    }
                    $value['rule']['model_config']['id'] = array_values($value['rule']['model_config']['id']);
                }
                if(isset($value['result']['image'])){
                	foreach($value['result']['image'] as $kk=>$resultItem){
                		foreach($resultItem['id'] as $kkk=>$vvv){
	                        if(isset($imageIdArr[$vvv])){
	                            $value['result']['image'][$kk]['id'][$kkk] = (int)$imageIdArr[$vvv];
	                        }else{
	                            unset($value['result']['image'][$kk]['id'][$kkk]);
	                        }
	                    }
	                    $value['result']['image'][$kk]['id'] = array_values($value['result']['image'][$kk]['id']);
                	}
                }
                if(isset($value['result']['model_config'])){
                	foreach($value['result']['model_config'] as $kk=>$resultItem){
                		foreach($resultItem['id'] as $kkk=>$vvv){
	                        if(isset($modelConfigIdArr[$vvv])){
	                            $value['result']['model_config'][$kk]['id'][$kkk] = (int)$modelConfigIdArr[$vvv];
	                        }else{
	                            unset($value['result']['model_config'][$kk]['id'][$kkk]);
	                        }
	                    }
	                    $value['result']['model_config'][$kk]['id'] = array_values($value['result']['model_config'][$kk]['id']);
                	}
                }
                // if(isset($value['rule']['duration']['id'])){
                //     foreach($value['rule']['duration']['id'] as $kk=>$vv){
                //         if(isset($durationIdArr[$vv])){
                //             $value['rule']['duration']['id'][$kk] = $durationIdArr[$vv];
                //         }else{
                //             unset($value['rule']['duration']['id'][$kk]);
                //         }
                //     }
                //     $value['rule']['duration']['id'] = array_values($value['rule']['duration']['id']);
                // }
                $value['rule'] = json_encode($value['rule']);
                $value['result'] = json_encode($value['result']);
                $value['rule_md5'] = md5($value['rule']);
				$r = $LimitRuleModel->create($value);
			}
		}
	}catch(\Exception $e){
		return $e->getMessage();
	}
});

// 5分钟定时任务,用于处理流量暂停/清零
add_hook('five_minute_cron', function($param){
    // 增加一个锁
    $cacheKey = 'MF_DCIM_FIVE_MINUTE_CRON_LOCK';
    $lock = cache($cacheKey);
    if(!empty($lock)){
        return false;
    }
    cache($cacheKey, 1, 3600);

	try{
		// 处理清零,超额暂停
		$host = HostLinkModel::alias('hl')
			->field('h.id,h.name,h.active_time,h.create_time,h.server_id,h.status,h.suspend_type,hl.rel_id,hl.config_data,hl.reset_flow_time')
			->join('host h', 'hl.host_id=h.id')
			->whereIn('h.status', 'Active,Suspended')
			->where('hl.rel_id', '>', 0)
			->where('h.is_delete', 0)
			->select()
			->toArray();
	}catch(\Exception $e){
        $host = [];
		// 异常可能是表不存在
	}
    if(empty($host)){
        cache($cacheKey, NULL);
        return false;
    }

    echo '处理DCIM流量暂停/清零开始:',date('Y-m-d H:i:s'),PHP_EOL;

	$HostModel = new HostModel();
	$dcim = [];
	$date = date('d');

	foreach($host as $v){
		$configData = json_decode($v['config_data'], true);
		if(isset($configData['line']['bill_type']) && $configData['line']['bill_type'] != 'flow'){
			continue;
		}
		// 流量周期
		$billCycle = $configData['flow']['other_config']['bill_cycle'] ?? 'month';

		if(!isset($dcim[$v['server_id']])){
			$ServerModel = ServerModel::find($v['server_id']);
            if(empty($ServerModel)){
                continue;
            }

			$ServerModel['password'] = aes_password_decode($ServerModel['password']);

			$Dcim = new Dcim($ServerModel);
			$dcim[$v['server_id']] = $Dcim;
		}else{
			$Dcim = $dcim[ $v['server_id'] ];
		}
		// 是否是开通日,开通日不清零流量
		$isCreateDay = false;
		$shouldResetFlow = false;
		$activeTime = $v['active_time'] ?: $v['create_time'];
		
		// 判断是否是开通日
		if(date('Ymd') == date('Ymd', $activeTime)){
			$isCreateDay = true;
		}
		
		if($billCycle == 'last_30days'){
			// 计算下次清零时间（开通时间 +1 month）
			$lastResetTime = $v['reset_flow_time'] ?: $activeTime;
			$nextResetTime = strtotime(date('Y-m-d H:i:s', $lastResetTime) . ' +1 month');
			
			// 判断是否到达清零时间（当前时间 >= 下次清零时间）
			if(time() >= $nextResetTime && !$isCreateDay){
				$shouldResetFlow = true;
			}
        }else{
        	// 固定每月1号清零（带上开通时的具体时间）
        	$lastResetTime = $v['reset_flow_time'] ?: $activeTime;
        	
        	// 计算下次清零时间：取上次清零时间（或开通时间）的下个月1号，保持时分秒不变
        	$nextResetDate = date('Y-m-01 H:i:s', strtotime(date('Y-m-d H:i:s', $lastResetTime) . ' +1 month'));
        	$nextResetTime = strtotime($nextResetDate);
        	
        	// 判断是否到达清零时间（当前时间 >= 下次清零时间）
        	if(time() >= $nextResetTime && !$isCreateDay){
        		$shouldResetFlow = true;
        	}
        }
        
        // 流量清零
        if($shouldResetFlow){
            // 上次清零时间不是今天，避免重复清零
            if(empty($v['reset_flow_time']) || date('Ymd') != date('Ymd', $v['reset_flow_time'])){
                $res = $Dcim->resetFlow(['id'=>$v['rel_id'], 'hostid'=>$v['id']]);

                if($res['status'] == 200){
                    // 计算应该记录的重置时间（最接近但不超过当前时间的理论清零时间点）
                    $recordResetTime = $nextResetTime;
                    
                    // 如果有多个周期未清零，需要追赶到最近的一次（但不超过当前时间）
                    $currentTime = time();
                    while(true){
                        if($billCycle == 'last_30days'){
                            $nextPeriodTime = strtotime(date('Y-m-d H:i:s', $recordResetTime) . ' +1 month');
                        }else{
                            $nextPeriodTime = strtotime(date('Y-m-01 H:i:s', strtotime(date('Y-m-d H:i:s', $recordResetTime) . ' +1 month')));
                        }
                        
                        // 如果下一个周期时间已经超过当前时间，停止循环
                        if($nextPeriodTime > $currentTime){
                            break;
                        }
                        
                        $recordResetTime = $nextPeriodTime;
                    }
                    
                    HostLinkModel::where('host_id', $v['id'])->update([
                        'reset_flow_time'   => $recordResetTime,
                    ]);

                    if($v['status'] == 'Suspended' && $v['suspend_type'] == 'overtraffic'){
                        $unsuspendRes = $HostModel->unsuspendAccount($v['id']);
                        if($unsuspendRes['status'] == 200){
                            $description = lang_plugins('mf_dcim_log_host_flow_clear_and_unsuspend_success', [
                                '{host}' => $v['name'],
                            ]);
                        }else{
                            $description = lang_plugins('mf_dcim_log_host_flow_clear_but_unsuspend_fail', [
                                '{host}'    => $v['name'],
                                '{reason}'  => $unsuspendRes['msg'],
                            ]);
                        }
                    }else{
                        $description = lang_plugins('mf_dcim_log_host_flow_clear_success', [
                            '{host}' => $v['name'],
                        ]);
                    }
                }else{
                    $description = lang_plugins('mf_dcim_log_host_flow_clear_fail', [
                        '{host}'    => $v['name'],
                        '{reason}'  => $res['msg'],
                    ]);
                }
                active_log($description, 'host', $v['id']);

                // 清零了,无需执行检查超额
                continue;
            }
        }
    	// 只有已激活的才检查
    	if($v['status'] == 'Suspended'){
    		continue;
    	}

    	// 检查是否超额
    	$post = [];
		$post['id'] = $v['rel_id'];
		$post['hostid'] = $v['id'];
		$post['unit'] = 'GB';

		$flow = $Dcim->flow($post);
		if($flow['status'] == 200){
			$data = $flow['data'][ $billCycle ];
			$percent = str_replace('%', '', $data['used_percent']);

			$total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
			$used = round($total * $percent / 100, 2);
			if($percent >= 100){
				// 执行超额
				$post = [];
				$post['id'] = $v['rel_id'];
		        $post['type'] = $billCycle;
		        $post['hostid'] = $v['id'];

		        $overFlow = $Dcim->overFlow($post);
		        if($overFlow['status'] == 200){
		        	// 超额后执行超额暂停
		        	if($overFlow['act'] == 1){
		                //执行暂停
		                $suspendRes = $HostModel->suspendAccount([
		                	'id'			=> $v['id'],
		                	'suspend_type'	=> 'overtraffic',
		                	'suspend_reason'=> lang_plugins('mf_dcim_flow_limit_desc', ['{total}'=>$total, '{used}'=>$used]),
		                ]);
		                if($suspendRes['status'] == 200){
		                	$description = lang_plugins('mf_dcim_log_host_over_flow_suspend_success', [
		                		'{host}' 	=> $v['name'],
		                		'{total}' 	=> $total,
		                		'{used}' 	=> $used,
		                	]);
		                }else{
		                	$description = lang_plugins('mf_dcim_log_host_over_flow_success_but_suspend_fail', [
		                		'{host}' 	=> $v['name'],
		                		'{total}' 	=> $total,
		                		'{used}' 	=> $used,
		                		'{reason}' 	=> $suspendRes['msg'],
		                	]);
		                }
		            }else if($overFlow['act'] == 2){
		            	$description = lang_plugins('mf_dcim_log_host_over_flow_limit_bw_success', [
	                		'{host}' 	=> $v['name'],
	                		'{total}' 	=> $total,
	                		'{used}' 	=> $used,
	                	]);
		            }else if($overFlow['act'] == 3){
		            	$description = lang_plugins('mf_dcim_log_host_over_flow_close_port_success', [
	                		'{host}' 	=> $v['name'],
	                		'{total}' 	=> $total,
	                		'{used}' 	=> $used,
	                	]);
		            }else{
		            	$description = '';
		            }
		        }else if($overFlow['msg'] == '已超额并执行过了相应操作'){
                    // 已经触发过了的
                    $description = '';
                }else{
		        	$description = lang_plugins('mf_dcim_log_host_over_flow_fail', [
                		'{host}' 	=> $v['name'],
                		'{total}' 	=> $total,
                		'{used}' 	=> $used,
                		'{reason}' 	=> $overFlow['msg'],
                	]);
		        }
		        if(!empty($description)){
	            	active_log($description, 'host', $v['id']);
	            }
			}
		}
        // 防止请求过快
        sleep(1);
	}
    cache($cacheKey, NULL);
    echo '处理DCIM流量暂停/清零完成:',date('Y-m-d H:i:s'),PHP_EOL;
});

// 15分钟定时任务,用于同步库存
add_hook('fifteen_minute_cron', function($param){
    // 增加一个锁
    $cacheKey = 'MF_DCIM_DAILY_CRON_LOCK';
    $lock = cache($cacheKey);
    if(!empty($lock)){
        return false;
    }
    cache($cacheKey, 1, 3600);

    echo '同步DCIM库存开始:',date('Y-m-d H:i:s'),PHP_EOL;

	$ModelConfigModel = new ModelConfigModel();
	$ModelConfigModel->syncDcimStock();

    cache($cacheKey, NULL);
    echo '同步DCIM库存开始:',date('Y-m-d H:i:s'),PHP_EOL;
});

// 购买流量包后
add_hook('flow_packet_order_paid', function($param){
	$hostId = $param['host_id'];
	$flow = $param['flow_packet']['capacity'];
	$moduleParam = $param['module_param'];

	if(!empty($moduleParam['server']) && $moduleParam['server']['module'] == 'mf_dcim'){
		$Dcim = new Dcim($moduleParam['server']);

		$hostLink = HostLinkModel::where('host_id', $hostId)->find();
		$billCycle = 'month';
		if(!empty($hostLink)){
			$configData = json_decode($hostLink['config_data'], true);
			$billCycle = $configData['flow']['other_config']['bill_cycle'] ?? 'month';
		}

		$res = $Dcim->addTempTraffic([
			'id'		=> $hostLink['rel_id'] ?? 0,
			'type'		=> $billCycle,
			'traffic'	=> $flow,
			'hostid'	=> $hostId,
		]);
		if($res['status'] == 200){
			$description = lang_plugins('mf_dcim_log_buy_flow_packet_success', [
				'{host}'	=> 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#',
				'{order}'	=> '#'.$param['order_id'],
				'{flow}'	=> $flow.'G',
			]);

            // 如果是流量暂停在检查流量
	        if(isset($res['act']) && $res['act'] == 1 && $moduleParam['host']['status'] == 'Suspended' && $moduleParam['host']['suspend_type'] == 'overtraffic'){
		        if($moduleParam['host']['due_time'] == 0 || time() < $moduleParam['host']['due_time']){
		        	$result = $moduleParam['host']->unsuspendAccount($hostId);
	                if ($result['status'] == 200){
                        $description .= lang_plugins('mf_dcim_log_buy_flow_packet_and_unsuspend_success');
                    }else{
                        $description .= lang_plugins('mf_dcim_log_buy_flow_packet_and_unsuspend_fail', ['{reason}'=>$result['msg']]);
                    }
		        }
		    }
        }else{
        	$description = lang_plugins('mf_dcim_log_buy_flow_packet_remote_fail', [
				'{host}'	=> 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#',
				'{order}'	=> '#'.$param['order_id'],
				'{flow}'	=> $flow.'G',
			]);
        }
	    // 记录日志
	    active_log($description, 'host', $hostId);
	}
});

// 在购买流量包之前
add_hook('flow_packet_before_order', function($param){
	try{
		$hostLink = hostLinkModel::where('host_id', $param['host']['id'])->find();
		if(!empty($hostLink)){
			$configData = json_decode($hostLink['config_data'], true);
			if(isset($configData['line']['bill_type']) && $configData['line']['bill_type'] !== 'flow'){
				// 不是流量线路,不能购买
				return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_buy_flow_packet')];
			}
		}
	}catch(PDOException $e){
		
	}
});

// 获取产品转移信息
add_hook('host_transfer_info', function($param){
	if($param['module'] == 'mf_dcim'){
		
	}
});

// 产品转移
add_hook('host_transfer', function($param){
	if($param['module'] == 'mf_dcim'){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->hostTransfer($param);
	}
});

// 产品转移
add_hook('order_paid', function($param){
	try{
		$OrderItemModel = new OrderItemModel();
		$orderItem = $OrderItemModel->where('order_id', $param['id'])->where('type', 'upgrade')->find();
		if(!empty($orderItem) && isset($orderItem['host_id']) && isset($orderItem['product_id'])){
			$hostLink = HostLinkModel::where('host_id', $orderItem['host_id'])->find();
			if(!empty($hostLink)){
				$ConfigModel = new ConfigModel();
	            $config = $ConfigModel->indexConfig(['product_id' => $orderItem['product_id']]);

	            if(isset($config['data']['manual_resource']) && $config['data']['manual_resource']==1){
					system_notice([
						'name'                  => 'host_module_action',
						'email_description'     => lang('host_module_action'),
						'sms_description'       => lang('host_module_action'),
						'task_data' => [
							'client_id' => $orderItem['client_id'],
							'host_id'	=> $orderItem['host_id'],
							'template_param'=>[
								'module_action' => lang_plugins('upgrade'),
							],
						],
					]);

	                $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
	                $ManualResourceLogModel->createLog([
	                    'host_id'                   => $orderItem['host_id'],
	                    'type'                      => 'upgrade',
	                    'client_id'                 => $orderItem['client_id'],
	                    'data'						=> [
	                    	'desc' => $orderItem['description'],
	                    ]
	                ]);

	                $host = HostModel::find($orderItem['host_id']);

	                $description = lang_plugins('mf_dcim_log_host_start_upgrade_in_progress', ['{hostname}'=>$host['name']]);

	                active_log($description, 'host', $orderItem['host_id']);
	            }
			}
		}
	}catch(PDOException $e){
		
	}
});

// 在代理防火墙IP同步后
add_hook('after_create_firewall_agent_host_ip', function($param){
	try{
		$HostLinkModel = new HostLinkModel();
		$HostLinkModel->afterCreateFirewallAgentHostIp($param);
	}catch(\Exception $e){

	}
});

// 需求同步至下游的模块相关信息
add_hook('push_downstream_module_info',function ($param){
    $id = $param['id']??0;
    $host = HostModel::find($id);
    $upstreamHost = \app\common\model\UpstreamHostModel::where('host_id',$id)->find();
    if (!empty($upstreamHost)){
        $UpstreamProductModel = new \app\common\model\UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id'])->find();
        if (!empty($upstreamProduct)){
            if ($upstreamProduct['mode']=='sync'){
                $product = \app\common\model\ProductModel::where('id',$host['product_id']??0)->find();
                $module = $product->getModule();
            }else{
                $module = $upstreamProduct['res_module'];
            }
            if ($module=='mf_dcim'){
                $param['module_info'] = HostLinkModel::where('parent_host_id',$id)
                    ->field('host_id,ip,config_data')
                    ->select()
                    ->toArray();
                // 若产品是子产品，则替换为主产品的host_ip
                if (!empty($host['is_sub'])){
                    $parentHostId = HostLinkModel::where('host_id',$id)->value('parent_host_id');
                    if (!empty($parentHostId)){
                        $HostIpModel = new \app\common\model\HostIpModel();
                        $param['host_ip'] = $HostIpModel->getHostIp(['host_id'=>$parentHostId,'client_id'=>$host['client_id']]);
                    }
                }
            }
        }
    }else{
        if($host->getModule() == 'mf_dcim'){
            $param['module_info'] = HostLinkModel::where('parent_host_id',$id)
                ->field('host_id,ip,config_data')
                ->select()
                ->toArray();
            // 若产品是子产品，则替换为主产品的host_ip
            if (!empty($host['is_sub'])){
                $parentHostId = HostLinkModel::where('host_id',$id)->value('parent_host_id');
                if (!empty($parentHostId)){
                    $HostIpModel = new \app\common\model\HostIpModel();
                    $param['host_ip'] = $HostIpModel->getHostIp(['host_id'=>$parentHostId,'client_id'=>$host['client_id']]);

                    // TODO 旧数据处理
                    $parentHost = HostModel::where('id',$parentHostId)->find();
                    $subHostLink = HostLinkModel::where('host_id',$id)->find();
                    $ipDefence = IpDefenceModel::where('host_id',$id)
                        ->where('ip',$subHostLink['ip']??'')
                        ->find();
                    if ($ipDefence){
                        $tmp = explode('_',$ipDefence['defence']);
                        $defaultDefenceId = $tmp[2];
                        $firewallType = $tmp[0] . '_' . $tmp[1];
                    }
                    $param['down_parent_host_id'] = $parentHost['downstream_host_id']??0;
                    $param['parent_host_id'] = $parentHostId;
                    $param['other_info'] = [
                        'ip' => $subHostLink['ip']??'',
                        'firewall_type' => $firewallType??'',
                        'default_defence_id' => $defaultDefenceId??0,
                        'due_time' => $host['due_time']??0,
                    ];
                }
            }
        }
    }
});

// 15分钟定时任务,检查流量
add_hook('fifteen_minute_cron', function(){
	echo '处理DCIM流量通知检查开始:',date('Y-m-d H:i:s'),PHP_EOL;

	$where = function($query){
		// $query->where('hl.rel_id', '>', 0);
		$query->where('hl.parent_host_id', '=', 0);

		$query->where('h.status', '=', 'Active');
		$query->where('h.is_delete', '=', 0);
		$query->where('h.billing_cycle', '<>', 'on_demand');

		$query->where('ctw.warning_switch', '=', 1);
	};

	try{
		$HostLinkModel = new HostLinkModel();
		$host = $HostLinkModel
				->field('hl.host_id,hl.config_data,ctw.leave_percent,h.client_id')
				->alias('hl')
				->join('host h', 'hl.host_id=h.id')
				->join('client_traffic_warning ctw', 'h.client_id=ctw.client_id AND ctw.module="mf_dcim"')
				->where($where)
				->select();
	}catch(\Exception $e){
		$host = [];
	}
	$HostNoticeModel = new HostNoticeModel();
	foreach($host as $v){
		$v['config_data'] = json_decode($v['config_data'], true);
		if(empty($v['config_data']['line']) || $v['config_data']['line']['bill_type'] != 'flow' || empty($v['config_data']['flow']['value']) ){
			continue;
		}
		// 获取当前流量
		try{
			$CloudLogic = new CloudLogic($v['host_id']);
			$flowDetail = $CloudLogic->flowDetail();

			if($flowDetail['status'] == 200){
				// 无限流量
				if(empty($flowDetail['data']['total_num'])){
					continue;
				}
				$hostNotice = $HostNoticeModel->hostNoticeIndex($v['host_id']);
				// 流量超额发送通知
				if($flowDetail['data']['used_num'] >= $flowDetail['data']['total_num']){
					if($hostNotice['traffic_limit_exceed'] == 0){
						system_notice([
							'name'                  => 'system_traffic_limit_exceed',
							'email_description'     => '流量超限通知,发送邮件',
							'sms_description'       => '流量超限通知,发送短信',
							'task_data' => [
								'client_id' => $v['client_id'],
								'host_id'	=> $v['host_id'],
							],
						]);
						$hostNotice->trafficLimitExceed($v['host_id']);
					}
				}else{
					if($hostNotice['traffic_limit_exceed'] == 1){
						$hostNotice->trafficLimitExceedRecover($v['host_id']);
					}

					// 是否发送不足通知
					$limit = $flowDetail['data']['base_flow'] * $v['leave_percent'] / 100;
					$leave = $flowDetail['data']['total_num'] - $flowDetail['data']['used_num'];
					if($leave < $limit){
						if($hostNotice['traffic_enough'] == 1){
							system_notice([
								'name'                  => 'system_traffic_not_enough',
								'email_description'     => '流量不足通知,发送邮件',
								'sms_description'       => '流量不足通知,发送短信',
								'task_data' => [
									'client_id' => $v['client_id'],
									'host_id'	=> $v['host_id'],
									'template_param'=>[
										'RemainingTraffic' => amount_format($leave).'GB',
										'AlertThreshold'   => amount_format($limit).'GB',
									],
								]
							]);
							$hostNotice->trafficNotEnough($v['host_id']);
						}
					}else{
						if($hostNotice['traffic_enough'] == 0){
							$hostNotice->trafficNotEnoughRecover($v['host_id']);
						}
					}
				}
			}
		}catch(\Exception $e){
			continue;
		}
	}
	echo '处理DCIM流量通知检查完成:',date('Y-m-d H:i:s'),PHP_EOL;
});
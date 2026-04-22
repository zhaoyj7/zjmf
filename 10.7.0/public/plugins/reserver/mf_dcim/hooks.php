<?php 

use app\common\model\ProductModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use app\common\model\HostNoticeModel;

use reserver\mf_dcim\logic\RouteLogic;


// 创建host link 关联ip
add_hook("upstream_push_after_module_create",function ($param){
    $HostModel = new \app\common\model\HostModel();
    $host = $HostModel->where('id',$param['host_id'])->find();
    $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
    $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
    $IpDefenceModel = new \server\mf_dcim\model\IpDefenceModel();
    if (!empty($upstreamProduct)){
        if ($upstreamProduct['mode']=='sync'){
            $product = ProductModel::where('id',$host['product_id']??0)->find();
            $module = $product->getModule();
        }else{
            $module = $upstreamProduct['res_module'];
        }
        if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel')){
            $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
            $hostLInks = $HostLinkModel->where('parent_host_id',$host['id'])
                ->select()
                ->toArray();
            if (!empty($ip = $param['data']['host_ip'])){
                if(!empty($ip['assign_ip']) && !empty($ip['dedicate_ip']) ){
                    $ips = explode(',', $ip['assign_ip']);
                    $ips[] = $ip['dedicate_ip'];
                }else if(!empty($ip['dedicate_ip'])){
                    $ips = [ $ip['dedicate_ip'] ];
                }else{
                    $ips = [];
                }
                $time = time();
                $moduleInfo = $param['data']['module_info']??[];
                $map = [];
                $mapConfigData = [];
                foreach ($moduleInfo as $item){
                    $map[$item['ip']??''] = $item['host_id']??0;
                    $mapConfigData[$item['ip']??''] = $item['config_data']??[];
                }
                foreach ($hostLInks as $k=>$v){
                    $HostLinkModel->where('id',$v['id'])
                        ->update([
                            'ip' => $ips[$k]??'',
                            'update_time' => $time,
                        ]);
                    // 开通子产品
                    $HostModel->where('id',$v['host_id'])->update([
                        'status'      => 'Active',
                    ]);
                    // 更新上下游产品关联
                    $UpstreamHostModel->where('host_id',$v['host_id'])
                        ->update([
                            'upstream_host_id' => $map[$ips[$k]??'']??0,
                            'update_time' => $time,
                        ]);
                    $configData = json_decode($mapConfigData[$ips[$k]??'']??'',true);
                    $IpDefenceModel->insert([
                        'host_id' => $v['host_id'],
                        'ip' => $ips[$k]??'',
                        'defence' => $configData['defence']['value']??''
                    ]);
                }
            }
        }
    }
});

// 升降级附加ip才调用此钩子，升降级防御时不处理
add_hook('upstream_push_after_update_host',function ($param){
    if (isset($param['data']['type'])){
        $HostModel = new \app\common\model\HostModel();
        $host = $HostModel->where('id',$param['host_id'])->find();
        $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
        if (!empty($upstreamProduct)){
            if ($upstreamProduct['mode']=='sync'){
                $product = ProductModel::where('id',$host['product_id']??0)->find();
                $module = $product->getModule();
            }else{
                $module = $upstreamProduct['res_module'];
            }
            if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel') && $param['data']['type']=='upgrade_common_config'){
                $upstreamHost = $UpstreamHostModel->where('host_id',$param['host_id'])->find();
                $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
                $moduleInfo = $param['data']['module_info']??[];
                $map = [];
                foreach ($moduleInfo as $item){
                    $map[$item['ip']??''] = $item['host_id']??0;
                }
                $time = time();
                if (!empty($moduleInfo[0]['config_data'])){
                    $configData = json_decode($moduleInfo[0]['config_data'],true);
                    $firewallType = $configData['defence']['firewall_type'];
                    $defaultDefenceId = str_replace($firewallType.'_','', $configData['line']['order_default_defence']??'');
                }
                try {
                    $CloudLogic = new \server\mf_dcim\logic\CloudLogic($param['host_id'],true);
                    $CloudLogic->ipChange([
                        'ips' => array_keys($map),
                        'agent' => !($upstreamProduct['mode'] == 'sync'),
                        'firewall_type' => $firewallType??'aodun_firewall',
                        'default_defence_id' => $defaultDefenceId??0,
                    ]);
                    // 获取本地数据
                    $hostLInks = $HostLinkModel->where('parent_host_id',$host['id'])
                        ->select()
                        ->toArray();
                    $ips = array_keys($map);
                    foreach ($hostLInks as $k=>$v){
                        // 更新上下游产品关联
                        $exist = $UpstreamHostModel->where('host_id',$v['host_id'])->find();
                        if (!empty($exist)){
                            $UpstreamHostModel->where('host_id',$v['host_id'])
                                ->update([
                                    'upstream_host_id' => $map[$ips[$k]??'']??0,
                                    'update_time' => $time,
                                ]);
                        }else{
                            $UpstreamHostModel->insert([
                                'supplier_id' => $upstreamHost['supplier_id'],
                                'host_id' => $v['host_id'],
                                'upstream_host_id' => $map[$ips[$k]??'']??0,
                                'upstream_configoption' => '',
                                'create_time' => $time,
                            ]);
                        }
                    }
                }catch (\Exception $e){
                }

            }
        }
    }
    if (isset($param['data']['type']) && $param['data']['type']=='upgrade_defence'){
        file_put_contents(IDCSMART_ROOT.'WYHADASDF.log',json_encode($param));
        $HostModel = new \app\common\model\HostModel();
        $host = $HostModel->where('id',$param['host_id'])->find();
        $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
        if (!empty($upstreamProduct)){
            if ($upstreamProduct['mode']=='sync'){
                $product = ProductModel::where('id',$host['product_id']??0)->find();
                $module = $product->getModule();
            }else{
                $module = $upstreamProduct['res_module'];
            }
            if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel')){
                $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
                $subHostIds = $HostLinkModel->where('parent_host_id',$host['id'])->column('host_id');
                if (!empty($subHostIds)){
                    $upstreamHost = $UpstreamHostModel->whereIn('host_id',$subHostIds)
                        ->select()
                        ->toArray();
                    $map = array_column($upstreamHost,'host_id','upstream_host_id');
                    $currentSubHostId = $map[$param['data']['host']['id']??0]??0;
                    $HostModel->update([
                        'base_info'     => $param['data']['host']['base_info'] ?? '',
                        'status' => $param['data']['host']['status'],
                        'due_time' => $param['data']['host']['due_time'],
                        'update_time' => time()
                    ], ['id' => $currentSubHostId]);
                }else{ // 处理旧数据

                }
            }
        }

    }
});

// 续费
add_hook('upstream_push_after_host_renew',function ($param){
    $HostModel = new \app\common\model\HostModel();
    $host = $HostModel->where('id',$param['host_id'])->find();
    $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
    $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
    if (!empty($upstreamProduct)){
        if ($upstreamProduct['mode']=='sync'){
            $product = ProductModel::where('id',$host['product_id']??0)->find();
            $module = $product->getModule();
        }else{
            $module = $upstreamProduct['res_module'];
        }
        if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel')){
            $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
            $subHostIds = $HostLinkModel->where('parent_host_id',$host['id'])->column('host_id');
            $upstreamHost = $UpstreamHostModel->whereIn('host_id',$subHostIds)
                ->select()
                ->toArray();
            $map = array_column($upstreamHost,'host_id','upstream_host_id');
            $currentSubHostId = $map[$param['data']['host']['id']??0]??0;
            $HostModel->update([
                'base_info'     => $param['data']['host']['base_info'] ?? '',
                'status' => $param['data']['host']['status'],
                'due_time' => $param['data']['host']['due_time'],
                'update_time' => time()
            ], ['id' => $currentSubHostId]);
        }
    }
});

add_hook('before_module_host_terminate',function ($param){
    $HostModel = new \app\common\model\HostModel();
    $host = $HostModel->where('id',$param['id'])->find();
    $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
    $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
    if (!empty($upstreamProduct)){
        if ($upstreamProduct['mode']=='sync'){
            $product = ProductModel::where('id',$host['product_id']??0)->find();
            $module = $product->getModule();
        }else{
            $module = $upstreamProduct['res_module'];
        }
        if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel')){
            $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
            $subHostIds = $HostLinkModel->where('parent_host_id',$host['id'])->column('host_id');
            $HostModel->whereIn('id',$subHostIds)->update([
                'status' => 'Deleted',
            ]);
            if (!empty($host['is_sub'])){
                $HostModel->where('id',$host['id'])->update([
                    'status' => 'Deleted',
                ]);
            }
        }
    }
});

// TODO 处理飞讯旧数据
add_hook('upstream_sync_host',function ($param){
    if (!empty($param['down_parent_host_id'])){
        $HostModel = new \app\common\model\HostModel();
        $host = $HostModel->where('id',$param['down_parent_host_id'])->find();
        $UpstreamHostModel = new \app\common\model\UpstreamHostModel();
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
        if (!empty($upstreamProduct)){
            // 仅处理接口代理
            $module = $upstreamProduct['res_module'];
            if ($module=='mf_dcim'){
                $upstreamHost = $UpstreamHostModel->where('host_id', $param['down_parent_host_id'])->find();
                if (!empty($upstreamHost)){
                    $SupplierModel = new SupplierModel();
                    $supplier = $SupplierModel->where('id', $upstreamProduct['supplier_id'])->find();
                    if (!empty($supplier)){
                        $param['data'] = (new \app\common\logic\UpstreamLogic())->rsaDecrypt($param['data'], aes_password_decode($supplier['secret']));
                        $data = json_decode($param['data'], true);
                        $ip = $data['other_info']['ip']??'';
                        if (!empty($ip)){
                            try {
                                $CloudLogic = new \server\mf_dcim\logic\CloudLogic($param['down_parent_host_id'],true);
                                $CloudLogic->ipChange([
                                    'ips' => [$ip],
                                    'only_create' => true,
                                    'agent' => true,
                                    'firewall_type' => $data['other_info']['firewall_type']??'',
                                    'default_defence_id' => $data['other_info']['default_defence_id']??'',
                                ]);
                                $subHostLink = \server\mf_dcim\model\HostLinkModel::where('ip',$ip)
                                    ->where('parent_host_id',$param['down_parent_host_id'])
                                    ->find();
                                if (!empty($subHostLink)){
                                    $UpstreamHostModel->where('host_id',$subHostLink['host_id'])->update([
                                        'upstream_host_id' => $data['host']['id']??0
                                    ]);
                                    $IpDefenceModel = new \server\mf_dcim\model\IpDefenceModel();
                                    $ipDefence = $IpDefenceModel->where('host_id',$subHostLink['host_id'])
                                        ->where('ip',$ip)
                                        ->find();
                                    if (!empty($ipDefence)){
                                        $ipDefence->save([
                                            'defence' => $data['other_info']['firewall_type'].'_'.$data['other_info']['default_defence_id']
                                        ]);
                                    }else{
                                        $IpDefenceModel->insert([
                                            'host_id' => $subHostLink['host_id'],
                                            'ip' => $ip,
                                            'defence' => $data['other_info']['firewall_type'].'_'.$data['other_info']['default_defence_id']
                                        ]);
                                    }
                                    $renewPrice = $data['renew_price']??0;
                                    $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->where('mode','only_api')->find();
                                    if (!empty($upstreamProduct)){
                                        if ($upstreamProduct['renew_profit_type']==1){
                                            $renewPrice = bcadd($renewPrice, $upstreamProduct['renew_profit_percent'],2);
                                        }else{
                                            $renewPrice = bcmul($renewPrice, ($upstreamProduct['renew_profit_percent']+100)/100,2);
                                        }
                                    }
                                    $clientLevel = $CloudLogic->getClientLevel([
                                        'client_id' => $host['client_id'],
                                        'product_id' => $host['product_id'],
                                    ]);
                                    if (!empty($clientLevel)){
                                        $discount = bcdiv($renewPrice*$clientLevel['discount_percent'], 100, 2);
                                        $renewPrice = bcsub($renewPrice,$discount,2);
                                    }
                                    $update = [
                                        'renew_amount' => $renewPrice,
                                    ];
                                    if (!empty($data['other_info']['due_time'])){
                                        $update['due_time'] = $data['other_info']['due_time'];
                                    }
                                    $HostModel->where('id',$subHostLink['host_id'])->update($update);
                                }

                            }catch (\Exception $e){

                            }
                        }
                    }
                }
            }
        }
    }
});

add_hook('after_host_suspend_success',function ($param){
    if (!empty($param['id'])){
        $HostModel = new \app\common\model\HostModel();
        $host = $HostModel->where('id',$param['id'])->find();
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id']??0)->find();
        if (!empty($upstreamProduct)){
            if ($upstreamProduct['mode']=='sync'){
                $product = ProductModel::where('id',$host['product_id']??0)->find();
                $module = $product->getModule();
            }else{
                $module = $upstreamProduct['res_module'];
            }
            if ($module=='mf_dcim' && class_exists('\\server\\mf_dcim\\model\\HostLinkModel')){
                $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
                $hostLink = $HostLinkModel->where('host_id',$param['id'])->find();
                if (!empty($hostLink['parent_host_id'])){
                    $parentHost = $HostModel->where('id',$hostLink['parent_host_id'])->find();
                    if (!empty($parentHost)){
                        $host->save([
                            'due_time' => $parentHost['due_time']??0,
                        ]);
                    }
                }
            }
        }
    }
});

// 15分钟定时任务,检查流量
add_hook('fifteen_minute_cron', function(){
    if(!class_exists('app\common\model\HostNoticeModel')){
        return ;
    }
    echo '处理DCIM代理流量通知检查开始:',date('Y-m-d H:i:s'),PHP_EOL;

	$where = function($query){
		$query->where('up.res_module', '=', 'mf_dcim');
		$query->where('up.mode', '=', 'only_api');

		$query->where('h.status', '=', 'Active');
		$query->where('h.is_delete', '=', 0);
		$query->where('h.billing_cycle', '<>', 'on_demand');

		$query->where('ctw.warning_switch', '=', 1);
	};

	try{
		$UpstreamHostModel = new \app\common\model\UpstreamHostModel();
		$host = $UpstreamHostModel
                    ->field('uh.host_id,ctw.leave_percent,h.client_id')
                    ->alias('uh')
                    ->join('host h', 'uh.host_id=h.id')
                    ->join('upstream_product up', 'h.product_id=up.product_id')
                    ->join('client_traffic_warning ctw', 'h.client_id=ctw.client_id AND ctw.module="mf_dcim"')
                    ->where($where)
                    ->select();
	}catch(\Exception $e){
		$host = [];
	}
    $HostNoticeModel = new HostNoticeModel();
	foreach($host as $v){
		// 获取当前流量
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($v['host_id']);

			$flowDetail = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/flow', $RouteLogic->upstream_host_id), [], 'GET');

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
    echo '处理DCIM代理流量通知检查完成:',date('Y-m-d H:i:s'),PHP_EOL;
});
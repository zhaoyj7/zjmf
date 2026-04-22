<?php 
namespace reserver\mf_cloud;

use app\common\model\UpgradeModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use server\mf_cloud\model\HostLinkModel;
use think\facade\Db;
use reserver\mf_cloud\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use app\common\model\ConfigurationModel;

use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;

/**
 * 魔方云reserver
 */
class MfCloud
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData()
	{
		return ['display_name'=>'魔方云代理(自定义配置)', 'version'=>'4.0.0'];
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params)
	{
		$hostId = $params['host']['id'];
		$custom = $params['custom'];
		$orderId = $params['order_id']??0;

		// 去掉代金券/优惠码参数
		if(isset($custom['param']['customfield'])){
			unset($custom['param']['customfield']);
		}

		if($custom['type'] == 'buy_disk'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/disk/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);

					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'resize_disk'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/disk/resize/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);

					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'buy_image'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/image/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'buy_ip'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/ip_num/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'upgrade_common_config'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/common_config/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');

                if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'upgrade_backup'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/backup_config/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'upgrade_recommend_config'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/recommend_config/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
                    $UpstreamOrderModel = new UpstreamOrderModel();
                    $UpstreamOrderModel->where('order_id',$orderId)
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'upgrade_defence'){
            // 先在上游创建订单
            try{
                $subHostId = $hostId;
                $hostLink = HostLinkModel::where('host_id',$hostId)->where('ip',$custom['param']['ip'])->find();
                if (!empty($hostLink)){
                    $hostId = $hostLink['parent_host_id'];
                }
                $RouteLogic = new RouteLogic();
                $RouteLogic->routeByHost($hostId);
                unset($custom['param']['id']);
                $result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/upgrade_defence/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');

                if($result['status'] == 200){
                    if (isset($result['data']['amount']) && $result['data']['amount'] == 0){
                        $hostIps[ $custom['param']['ip'] ] = '';

                        $defence = explode('_', $custom['param']['peak_defence']);

                        $defenceRuleId = array_pop($defence);
                        $firewallType = implode('_', $defence);
                        hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $defenceRuleId, 'host_ips' => $hostIps]);

                        // 更改ip对应子产品到期时间以及续费金额
                        if (!empty($hostLink)){
                            $upgrade = UpgradeModel::where('order_id',$orderId)
                                ->where('host_id',$subHostId)
                                ->where('type','config_option')
                                ->find();
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
                        ->where('host_id',$hostId)
                        ->update([
                            'profit' => Db::raw('amount-'.($result['data']['amount']??0)),
                        ]);
                    $creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result1 = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result1['status'] == 200 || ($result1['status']==400 && !empty($result1['data']['no_credit']))){
                        // 使用平台币支付
                        if (!empty($result1['data']['customfields']['agent_use_coin'])){
                            $resultCoin = $RouteLogic->curl('/console/v1/coin/pay',[
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
                        $checkResult = checkLossTrade($orderId, $result['data']['amount']??0, 'upgrade', '', $hostId);
                        if (!$checkResult['pass']) {
                            return ['status'=>400, 'msg'=>$checkResult['msg']];
                        }
                        
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付(余额支付失败后回退信用额支付)
                        $result = $RouteLogic->payWithFallback($payData);
                        if($result['status'] == 200){
                            $hostIps[ $custom['param']['ip'] ] = '';

                            $defence = explode('_', $custom['param']['peak_defence']);

                            $defenceRuleId = array_pop($defence);
                            $firewallType = implode('_', $defence);
                            hook('firewall_agent_set_meal_modify', ['type' => $firewallType, 'set_meal_id' => $defenceRuleId, 'host_ips' => $hostIps]);

                            // 更改ip对应子产品到期时间以及续费金额
                            if (!empty($hostLink)){
                                $upgrade = UpgradeModel::where('order_id',$orderId)
                                    ->where('host_id',$subHostId)
                                    ->where('type','config_option')
                                    ->find();
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
                        return $result;
                    }
                    return $result;
                }else{
                    // 记录失败日志
                    return $result;
                }
            }catch(\Exception $e){
                return ['status'=>400, 'msg'=>$e->getMessage()];
            }
        }
		return ['status'=>200];

	}

    /**
    * 时间 2024-05-20
    * @title 后台产品内页实例操作输出
    * @author hh
    * @version v1
    */
    public function adminAreaModuleOperate($param)
    {
        $res = [
        'template'=>'template/admin/module_operate.html',
        ];
        return $res;
    }

    /**
     * 时间 2022-06-29
     * @title 前台产品内页输出
     * @author hh
     * @version v1
     */
    public function clientArea()
    {
        if (use_mobile()){ // 手机端
            $homeHostThemeMobile = configuration('home_host_theme_mobile');
            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
            $mobileTheme = $homeHostThemeMobile['mf_cloud'] ?? 'default';
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_detail.html"
            ];
        }else{ // pc端
            $homeHostTheme = configuration('home_host_theme');
            $homeHostTheme = json_decode($homeHostTheme ?? '{}', true) ?: [];
            $clientareaTheme = $homeHostTheme['mf_cloud'] ?? 'default';
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_detail.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_detail.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 产品列表
     * @author hh
     * @version v1
     */
    public function hostList($param)
    {
        if (use_mobile()){ // 手机端
            $homeHostThemeMobile = configuration('home_host_theme_mobile');
            $homeHostThemeMobile = json_decode($homeHostThemeMobile ?? '{}', true) ?: [];
            $mobileTheme = $homeHostThemeMobile['mf_cloud'] ?? 'default';
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_list.html"
            ];
        }else{ // pc端
            $homeHostTheme = configuration('home_host_theme');
            $homeHostTheme = json_decode($homeHostTheme ?? '{}', true) ?: [];
            $clientareaTheme = $homeHostTheme['mf_cloud'] ?? 'default';
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_list.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_list.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 前台商品购买页面输出
     * @author hh
     * @version v1
     */
    public function clientProductConfigOption($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('cart_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/cart/mobile/{$mobileTheme}/goods.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/cart/{$type}/{$mobileTheme}/goods.html"
            ];
        }else{ // pc端
            $cartTheme = configuration('cart_theme');
            if (!file_exists(__DIR__."/template/cart/pc/{$cartTheme}/goods.html")){
                $cartTheme = "default";
            }
            $res = [
                'template' => "template/cart/pc/{$cartTheme}/goods.html"
            ];
        }

        return $res;
    }

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,增加验证
	 * @author hh
	 * @version v1
	 * @throws \Exception
	 */
	public function afterSettle($params)
	{
		$custom = $params['custom'] ?? [];
        $hostId = $params['host_id'];
        $productId = $params['product']['id'] ?? 0;
        $clientId = !empty(get_admin_id()) ? HostModel::where('id', $hostId)->value('client_id') : get_client_id();
        $time = time();

        $modify = false;
        if(isset($custom['ssh_key_id'])){
			unset($params['custom']['ssh_key_id']);
			$modify = true;
		}
		// if(isset($custom['security_group_id'])){
		// 	unset($params['custom']['security_group_id']);
		// 	$modify = true;
		// }
		// if(isset($custom['security_group_protocol'])){
		// 	unset($params['custom']['security_group_protocol']);
		// 	$modify = true;
		// }
        
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
                // 如果有remote_port，则下游需要根据上游配置来处理，并手动传入端口
                $port = 0;
                // 上游安全组配置
                $upstreamSecurityGroupConfig = []; 
                if(in_array('remote_port', $custom['security_group_protocol'])){
                    // 验证是否是专业版
                    $ConfigurationModel = new ConfigurationModel();
                    $configuration = $ConfigurationModel->systemList();
                    if(!empty($configuration['edition'])){
                        try{
                            $RouteLogic = new RouteLogic();
                            $RouteLogic->routeByProduct($productId);

                            $result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_cloud/order_page', $RouteLogic->upstream_product_id), ['scene'=>'custom'], 'GET');
                            if($result['status'] == 200){
                                $config = $result['data']['config'];
                                $upstreamSecurityGroupConfig = $result['data']['security_group_config'] ?? [];

                                // 获取上游镜像来判断镜像类型
                                $isWindows = false;
                                $result = $RouteLogic->curl( sprintf('console/v1/product/%s/remf_cloud/image', $RouteLogic->upstream_product_id), [], 'GET');
                                if($result['status'] == 200){
                                    foreach($result['data']['list'] as $v){
                                        foreach($v['image'] as $vv){
                                            if($vv['id'] == $custom['image_id']){
                                                if($v['icon'] == 'Windows'){
                                                    $isWindows = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                                if($config['rand_ssh_port'] == 0){
                                    if($isWindows){
                                        $port = 3389;
                                    }else{
                                        $port = 22;
                                    }
                                }else if($config['rand_ssh_port'] == 1){
                                    $port = $custom['port'] ?? 0;
                                }else if($config['rand_ssh_port'] == 2){
                                    // 指定端口,获取镜像分组
                                    if($isWindows){
                                        $port = $config['rand_ssh_port_windows'] ?: 3389;
                                    }else{
                                        $port = $config['rand_ssh_port_linux'] ?: 22;
                                    }
                                }else if($config['rand_ssh_port'] == 3){
                                    if(!empty($custom['rand_port'])){
                                        $port = mt_rand(20000, 65535);
                                    }else{
                                        $port = $custom['port'] ?? mt_rand(20000, 65535);
                                    }
                                }
                            }
                        }catch(\Exception $e){
                            throw new \Exception($e->getMessage());
                        }
                    }
                }

                // 传了安全组规则过来
                $securityGroup = IdcsmartSecurityGroupModel::create([
                    'client_id'     => $clientId,
                    'type'          => 'host',
                    'name'          => 'security-'.rand_str(),
                    'create_time'   => $time,
                ]);

                $protocol = [];
                if(!empty($upstreamSecurityGroupConfig)){
                    foreach($upstreamSecurityGroupConfig as $v){
                        $protocol[ $v['id'] ] = [
                            'port'          => $v['port'],
                            'description'   => $v['description'],
                            'direction'     => $v['direction'],
                            'protocol'      => $v['protocol'],
                        ];
                    }
                }else{
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
                        if($port == 0){
                            continue;
                        }
                        if($port == 22 && in_array('ssh', $custom['security_group_protocol'])){
                            continue;
                        }
                        if($port == 3389 && in_array('rdp', $custom['security_group_protocol'])){
                            continue;
                        }
                        // 说明增加了远程端口安全组规则,修改上传参数
                        $modify = true;
                        $params['custom']['port'] = $port;
                        $params['custom']['rand_port'] = 0;
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
                // 获取当前商品对应代理商
                $supplierId = UpstreamProductModel::where('product_id', $params['product']['id'])->value('supplier_id');
                if(!empty($supplierId)){
                    // 当前产品是否是下游
                    $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();
                    $syncSecurityGroupToSupplier = $IdcsmartSecurityGroupHostLinkModel->syncSecurityGroupToSupplier([
                        'security_group_id' => $custom['security_group_id'],
                        'supplier_id'       => $supplierId,
                    ]);
                    if($syncSecurityGroupToSupplier['status'] != 200){
                        throw new \Exception( lang_plugins('res_mf_cloud_sync_security_group_fail') );
                    }
                    $IdcsmartSecurityGroupHostLinkModel->saveSecurityGroupHostLink($custom['security_group_id'], $hostId);
                }
            }
        }

		$RouteLogic = new RouteLogic();
		$RouteLogic->routeByProduct($params['product']['id']);
		// 后台下单
		if(!empty(get_admin_id())){
			$RouteLogic->clientId = HostModel::where('id', $hostId)->value('client_id');
		}
        // wyh 20250321 子产品不验证，
        if (empty($params['custom']['parent_host_id'])){
            $result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_cloud/validate_settle', $RouteLogic->upstream_product_id), ['custom'=>$params['custom']], 'POST');
            if($result['status'] != 200){
                throw new \Exception($result['msg']);
            }
        }
		$hostData = [
            'client_notes' => $custom['notes'] ?? '',
        ];
        HostModel::where('id', $params['host_id'])->update($hostData);

		// 处理custom下的参数,有些参数不能带到上游
		if(isset($custom['auto_renew']) && $custom['auto_renew'] == 1){
			$enableIdcsmartRenewAddon = PluginModel::where('name', 'IdcsmartRenew')->where('module', 'addon')->where('status',1)->find();
            if($enableIdcsmartRenewAddon && class_exists('addon\idcsmart_renew\model\IdcsmartRenewAutoModel')){
                IdcsmartRenewAutoModel::where('host_id', $hostId)->delete();
                IdcsmartRenewAutoModel::create([
                    'host_id' => $hostId,
                    'status'  => 1,
                ]);
            }
            unset($params['custom']['auto_renew']);
            $modify = true;
		}
		// 修改参数
		if($modify){
			UpstreamHostModel::where('host_id', $hostId)->update(['upstream_configoption'=>json_encode($params['custom'])]);
		}

        // TODO wyh 新增子产品，保存关联
        $HostLinkModel = new HostLinkModel();
        if (!empty($custom['parent_host_id'])){
            $HostLinkModel->insert([
                'host_id' => $hostId,
                'parent_host_id' => $custom['parent_host_id'],
                'config_data' => json_encode([]),
                'create_time' => time(),
            ]);
        }else{
            $HostLinkModel->insert([
                'host_id' => $hostId,
                'config_data' => json_encode([]),
                'create_time' => time(),
            ]);
        }
	}


}

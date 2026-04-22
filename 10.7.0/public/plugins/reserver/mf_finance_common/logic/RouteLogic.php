<?php 

namespace reserver\mf_finance_common\logic;

use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;
use app\admin\model\PluginModel;
use app\common\model\ClientModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;

/**
 * @title 路由类
 * @use   reserver\mf_finance_dcim\logic\RouteLogic
 */
class RouteLogic{

	protected $timeout = 60;

	// 是否是代理商品
	public $isUpstream = true;

	/**
	 * 时间 2023-02-16
	 * @title 获取登录成功标识
	 * @desc 获取登录成功标识
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 */
	public function routeByProduct($id){
		$upstreamProduct = UpstreamProductModel::where('product_id', $id)->find();
		if(empty($upstreamProduct)){
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		//bcscale(2);

		$this->supplier_id = $upstreamProduct['supplier_id'];
		$this->upstream_product_id = $upstreamProduct['upstream_product_id'];
		$this->price_multiple = bcdiv(bcadd(100, $upstreamProduct['profit_percent'],4), 100,4);
		$this->profit_percent = bcdiv($upstreamProduct['profit_percent'], 100,4);
        $this->profit_type = $upstreamProduct['profit_type'];
        $this->renew_profit_type = $upstreamProduct['renew_profit_type'];
        $this->renew_profit_percent = $upstreamProduct['renew_profit_percent'];
        $this->upgrade_profit_type = $upstreamProduct['upgrade_profit_type'];
        $this->upgrade_profit_percent = $upstreamProduct['upgrade_profit_percent'];
        $this->price_basis = $upstreamProduct['price_basis']??'agent';
	}

	/**
	 * 时间 2023-02-16
	 * @title 获取登录成功标识
	 * @desc 获取登录成功标识
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 */
	public function routeByHost($id){
		$upstreamHost = UpstreamHostModel::where('host_id', $id)->find();
		if(empty($upstreamHost)){
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		bcscale(2);
		$this->host_id = $id;
		$this->supplier_id = $upstreamHost['supplier_id'];
		$this->upstream_host_id = $upstreamHost['upstream_host_id'];

        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id']??0)->find();
        $this->profit_type = $upstreamProduct['profit_type']??0;
        $this->profit_percent = $upstreamProduct['profit_percent']??0;
        $this->upstream_product_id = $upstreamProduct['upstream_product_id'];
        $this->renew_profit_type = $upstreamProduct['renew_profit_type']??0;
        $this->renew_profit_percent = $upstreamProduct['renew_profit_percent']??0;
        $this->upgrade_profit_type = $upstreamProduct['upgrade_profit_type']??0;
        $this->upgrade_profit_percent = $upstreamProduct['upgrade_profit_percent']??0;
        $this->price_basis = $upstreamProduct['price_basis']??'agent';
	}

	public function getPriceMultiple(){
		if(isset($this->price_multiple)){
			return $this->price_multiple;
		}else if(isset($this->host_id)){
			$productId = HostModel::where('id', $this->host_id)->value('product_id');
			// 获取倍率
			$upstreamProduct = UpstreamProductModel::where('product_id', $productId)->find();
			if(empty($upstreamProduct)){
				$this->isUpstream = false;
				throw new \Exception("not upstream");
			}
			$this->upstream_product_id = $upstreamProduct['upstream_product_id'];
			$this->price_multiple = bcdiv(bcadd(100, $upstreamProduct['profit_percent']), 100);
			$this->profit_percent = bcdiv($upstreamProduct['profit_percent'], 100);
		}else{
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		return $this->price_multiple;
	}

	public function getProfitPercent(){
		$this->getPriceMultiple();
		return $this->profit_percent;
	}

	// 获取下游用户ID
	public function getDownstreamClientId(){
		$clientId = get_client_id();
		if (request()->is_api){
			$param = request()->param();
			if(isset($param['downstream_client_id']) && $param['downstream_client_id']>0){
				$enable = PluginModel::where('name', 'IdcsmartSubAccount')->where('module', 'addon')->where('status',1)->find();
				if(!empty($enable) && class_exists('addon\idcsmart_sub_account\model\IdcsmartSubAccountModel')){
					// 转换为当前下游用户ID
					$IdcsmartSubAccountModel = new IdcsmartSubAccountModel();
					
					$idcsmartSubAccount = IdcsmartSubAccountModel::where('parent_id', $clientId)->where('downstream_client_id', $param['downstream_client_id'])->find();
	                if(empty($idcsmartSubAccount)){
	                	if(isset($param['create_sub_account']) && $param['create_sub_account'] == 1){
	                		$client = ClientModel::create([
		                        'username' => '下游账户'.$param['downstream_client_id'],
		                        'email' => '',
		                        'phone_code' => 44,
		                        'phone' => '',
		                        'password' => idcsmart_password('12345678'), // 密码加密
		                        'language' => configuration('lang_home')??'zh-cn',
		                        'create_time' => time(),
		                    ]);

		                    $idcsmartSubAccount = IdcsmartSubAccountModel::create([
		                        'parent_id' => $clientId,
		                        'client_id' => $client->id,
		                        'auth' => json_encode([]),
		                        'notice' => json_encode([]),
		                        'visible_product' => 'module',
		                        'module' => json_encode([]),
		                        'host_id' => json_encode([]),
		                        'create_time' => time(),
		                        'downstream_client_id' => $param['downstream_client_id'],
		                    ]);

	                		$clientId = $client->id;
	                	}else{
	                		$clientId = -1; // 找不到
	                	}
	                }else{
	                	$clientId = $idcsmartSubAccount['client_id'];
	                }
				}
			}else{
				$clientId = -1;
			}
		}
		return $clientId;
	}

	public function setTimeout($timeout){
		$this->timeout = $timeout;
	}

	/**
	 * 时间 2023-02-16
	 * @title 
	 * @desc 
	 * @author hh
	 * @version v1
	 * @param   string $path    
	 * @param   array  $data    所有参数
	 * @param   [type] $request [description]
	 * @return  [type]          [description]
	 */
	public function curl($path, $data = [], $request = 'POST', $response_type='json'){
		$downstreamClientId = $this->getDownstreamClientId();
		$data['downstream_client_id'] = $downstreamClientId;
		return idcsmart_api_curl($this->supplier_id, $path, $data, $this->timeout, $request,$response_type);
	}

	/**
	 * 时间 2025-03-12
	 * @title 支付(余额支付失败后回退信用额支付)
	 * @desc  先尝试余额支付,失败后自动尝试信用额支付
	 * @author wyh
	 * @version v1
	 * @param   array payData - 支付参数(id,gateway) require
	 * @return  array
	 */
	public function payWithFallback($payData)
	{
		// 余额支付
		$result = $this->curl('/console/v1/pay', $payData, 'POST');
		// 余额支付失败，尝试信用额支付
		if ($result['status'] != 200) {
			// 先取消余额占用
			$this->curl('/console/v1/credit', [
				'id' => $payData['id'] ?? 0,
				'use' => 0,
			], 'POST');
			// 再尝试信用额支付
			$creditLimitPayData = [
				'id' => $payData['id'] ?? 0,
			];
			$creditLimitResult = $this->curl('/console/v1/credit_limit/pay', $creditLimitPayData, 'POST');
			if ($creditLimitResult['status'] == 200) {
				return $creditLimitResult;
			}
		}
		return $result;
	}

	
	
	
	












































}





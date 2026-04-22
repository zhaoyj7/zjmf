<?php
namespace reserver\mf_dcim\controller\home;

use app\admin\model\PluginModel;
use think\facade\Cache;
use think\facade\View;
use reserver\mf_dcim\validate\HostValidate;
use reserver\mf_dcim\logic\RouteLogic;
use app\common\model\OrderModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\common\model\SystemLogModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\SupplierModel;
use app\common\model\HostAdditionModel;

/**
 * @title 魔方DCIM代理(自定义配置)-前台
 * @desc 魔方DCIM代理(自定义配置)-前台
 * @use reserver\mf_dcim\controller\home\CloudController
 */
class CloudController
{
	/**
	 * 时间 2023-02-06
	 * @title 获取订购页面配置
	 * @desc 获取订购页面配置
	 * @url /console/v1/product/:id/remf_dcim/order_page
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int data_center[].id - 国家ID
	 * @return  string data_center[].iso - 图标
	 * @return  string data_center[].name - 名称
	 * @return  string data_center[].city[].name - 城市
	 * @return  int data_center[].city[].area[].id - 数据中心ID
	 * @return  string data_center[].city[].area[].name - 区域
	 * @return  int data_center[].city[].area[].line[].id - 线路ID
	 * @return  string data_center[].city[].area[].line[].name - 线路名称
	 * @return  int data_center[].city[].area[].line[].data_center_id - 数据中心ID
	 * @return  string data_center[].city[].area[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
	 * @return  int model_config[].id - 型号配置ID
	 * @return  string model_config[].name - 型号配置名称
	 * @return  string model_config[].cpu - 处理器
	 * @return  string model_config[].cpu_param - 处理器参数
	 * @return  string model_config[].memory - 内存
	 * @return  string model_config[].disk - 硬盘
	 * @return  int model_config[].support_optional - 允许增值选配(0=不允许,1=允许)
	 * @return  int model_config[].optional_only_for_upgrade - 增值仅用于升降级(0=关闭,1=开启)
	 * @return  int model_config[].leave_memory - 剩余内存
	 * @return  int model_config[].max_memory_num - 可增加内存数量
	 * @return  int model_config[].max_disk_num - 可增加硬盘数量
	 * @return  string model_config[].gpu - 显卡
	 * @return  int model_config[].max_gpu_num - 可增加显卡数量
	 * @return  int model_config[].optional_memory[].id - 选配内存配置ID
	 * @return  string model_config[].optional_memory[].value - 选配内存配置名称
	 * @return  int model_config[].optional_memory[].other_config.memory - 选配内存大小
	 * @return  int model_config[].optional_memory[].other_config.memory_slot - 选配内存插槽
	 * @return  int model_config[].optional_disk[].id - 选配硬盘配置ID
	 * @return  string model_config[].optional_disk[].value - 选配硬盘配置名称
	 * @return  int model_config[].optional_gpu[].id - 选配显卡配置ID
	 * @return  string model_config[].optional_gpu[].value - 选配显卡配置名称
	 * @return  int config_limit[].data_center_id - 数据中心ID
	 * @return  int config_limit[].line_id - 线路ID
	 * @return  string config_limit[].min_bw - 带宽最小值
	 * @return  string config_limit[].max_bw - 带宽最大值
	 * @return  string config_limit[].min_flow - 流量最小值
	 * @return  string config_limit[].max_flow - 流量最大值
	 * @return  array config_limit[].model_config_id - 型号配置ID
	 */
	public function orderPage()
	{
		$param = request()->param();

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_dcim/order_page', $RouteLogic->upstream_product_id), $param, 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->orderPage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /console/v1/product/:id/remf_dcim/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int list[].id - 操作系统分类ID
	 * @return  string list[].name - 操作系统分类名称
	 * @return  string list[].icon - 操作系统分类图标
	 * @return  int list[].image[].id - 操作系统ID
	 * @return  int list[].image[].image_group_id - 操作系统分类ID
	 * @return  string list[].image[].name - 操作系统名称
	 * @return  int list[].image[].charge - 是否收费(0=否,1=是)
	 * @return  string list[].image[].price - 价格
	 * @return  string list[].image[].price_client_level_discount - 价格等级折扣
	 */
	public function imageList()
	{
		$param = request()->param();
        $productId = $param['id'];
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			$SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);

			$param['is_downstream'] = 1;
            $param['price_basis'] = $RouteLogic->price_basis??'agent';
            $priceAgent = $param['price_basis']=='agent';
			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/product/%s/remf_dcim/image', $RouteLogic->upstream_product_id), $param, 'GET');
			if($result['status'] == 200){
                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
				// 计算价格倍率
				foreach($result['data']['list'] as $k=>$v){
					foreach($v['image'] as $kk=>$vv){
                        if (isset($vv['price_client_level_discount']) && $priceAgent){
                            $vv['price'] = bcsub($vv['price'],$vv['price_client_level_discount'],2);
                        }
                        // 计算汇率
                        $vv['price'] = $vv['price'] * $supplier['rate'];

                        if($vv['charge'] == 1){
							$result['data']['list'][$k]['image'][$kk]['price'] = $RouteLogic->upgrade_profit_type==1?bcadd($vv['price'],$RouteLogic->upgrade_profit_percent,2):bcmul($vv['price'], 1+$RouteLogic->upgrade_profit_percent/100,2);
						}
                        $baseParam = request()->param();
                        $priceBasis = $baseParam['price_basis']??'agent';
                        $priceBasisAgent = $priceBasis=='agent';
                        if (!empty($plugin) && $priceBasisAgent){
                            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                            // 获取商品折扣金额
                            $clientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                                'id' => $productId,
                                'amount' => $result['data']['list'][$k]['image'][$kk]['price']
                            ]);
                            // 二级代理及以下给下游的客户等级折扣数据
                            $result['data']['list'][$k]['image'][$kk]['price_client_level_discount'] = $clientLevelDiscount??0;
                        }
					}
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->imageList();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取商品配置所有周期价格
	 * @desc 获取商品配置所有周期价格
	 * @url /console/v1/product/:id/remf_dcim/duration
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int model_config_id - 型号配置ID
	 * @param   object optional_memory - 选配内存(如{"5":"12"},5是选配内存配置ID,12是数量)
	 * @param   object optional_disk - 选配硬盘(如{"5":"12"},5是选配硬盘配置ID,12是数量)
	 * @param   object optional_gpu - 选配显卡(如{"5":"12"},5是选配显卡配置ID,12是数量)
     * @param   int image_id - 镜像ID
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽(带宽线路)
     * @param   int flow - 流量(流量线路)
     * @param   int ip_num - 公网IP数量
     * @param   int peak_defence - 防御峰值
     * @return  int [].id - 周期ID
     * @return  string [].name - 周期名称
     * @return  string [].price - 周期总价
     * @return  float [].discount - 折扣(0=没有折扣)
     * @return  int [].num - 周期时长
     * @return  string [].unit - 单位(hour=小时,day=天,month=月)
     * @return  string [].client_level_discount - 用户等级折扣
     * @return  string [].price_client_level_discount - 价格用户等级折扣
	 */
	public function getAllDurationPrice()
	{
		$param = request()->param();
        $productId = $param['id'];
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			$SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);

			$param['is_downstream'] = 1;
            $param['price_basis'] = $RouteLogic->price_basis??'agent';
            $priceAgent = $param['price_basis']=='agent';
			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_dcim/duration', $RouteLogic->upstream_product_id), $param, 'POST');
			if($result['status'] == 200){
                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
				// 计算价格倍率
				foreach($result['data'] as $k=>$v){
                    if (isset($v['price_client_level_discount']) && $priceAgent){
                        $v['price'] = bcsub($v['price'],$v['price_client_level_discount'],2);
                    }
                    // 计算汇率
                    $v['price'] = $v['price'] * $supplier['rate'];
					if($v['price'] > 0){
						$result['data'][$k]['price'] = $RouteLogic->profit_type==1?bcadd($v['price'],$RouteLogic->getProfitPercent()*100):bcmul($v['price'], $RouteLogic->price_multiple);
					}
                    $baseParam = request()->param();
                    $priceBasis = $baseParam['price_basis']??'agent';
                    $priceBasisAgent = $priceBasis=='agent';
                    if (!empty($plugin) && $priceBasisAgent){
                        $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                        // 获取商品折扣金额
                        $clientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                            'id' => $productId,
                            'amount' => $result['data'][$k]['price']
                        ]);
                        // 二级代理及以下给下游的客户等级折扣数据
                        $result['data'][$k]['price_client_level_discount'] = $clientLevelDiscount??0;
                    }
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->getAllDurationPrice();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-14
	 * @title 获取线路配置
	 * @desc 获取线路配置
	 * @url /console/v1/product/:id/remf_dcim/line/:line_id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int line_id - 线路ID require
	 * @return  string bill_type - 计费类型(bw=带宽,flow=流量)
	 * @return  string bw[].type - 配置方式(radio=单选,step=阶梯,total=总量)
	 * @return  string bw[].value - 带宽
	 * @return  int bw[].min_value - 最小值
	 * @return  int bw[].max_value - 最大值
	 * @return  int bw[].step - 步长
	 * @return  string flow[].value - 流量(流量线路)
	 * @return  string defence[].value - 防御
	 * @return  string ip[].value - 公网IP值
	 * @return  string ip[].desc - 公网IP显示
	 */
	public function lineConfig()
	{
		$param = request()->param();

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_dcim/line/%d', $RouteLogic->upstream_product_id, $param['line_id']), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->lineConfig();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	* 时间 2023-02-09
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/remf_dcim
	* @method  GET
	* @author hh
	* @version v1
     * @param   int page 1 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,due_time,status)
     * @param   string sort - 升/降序
     * @param   string keywords - 关键字搜索:商品名称/产品名称/IP
     * @param   int country_id - 搜索:国家ID
     * @param   string city - 搜索:城市
     * @param   string area - 搜索:区域
     * @param   string status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @param   string tab - 状态using使用中expiring即将到期overdue已逾期deleted已删除
     * @return  array list - 列表数据
     * @return  int list[].id - 产品ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].name - 产品标识
     * @return  string list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return  int list[].active_time - 开通时间
     * @return  int list[].due_time - 到期时间
     * @return  string list[].client_notes - 用户备注
     * @return  string list[].product_name - 商品名称
     * @return  string list[].country - 国家
     * @return  string list[].country_code - 国家代码
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  string list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  string list[].image_name - 镜像名称
     * @return  string list[].image_icon - 镜像图标(Windows,CentOS,Ubuntu,Debian,ESXi,XenServer,FreeBSD,Fedora,ArchLinux,Rocky,AlmaLinux,OpenEuler,RedHat,其他)
     * @return  int list[].ip_num - IP数量
     * @return  string list[].dedicate_ip - 主IP
     * @return  string list[].assign_ip - 附加IP(英文逗号分隔)
     * @return  object list[].self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容)
     * @return  int list[].is_auto_renew - 是否自动续费(0=否,1=是)
     * @return  int count - 总条数
     * @return  int using_count - 使用中产品数量
     * @return  int expiring_count - 即将到期产品数量
     * @return  int overdue_count - 已逾期产品数量
     * @return  int deleted_count - 已删除产品数量
     * @return  int all_count - 全部产品数量
     * @return  int data_center[].country_id - 国家ID
     * @return  string data_center[].city - 城市
     * @return  string data_center[].area - 区域
     * @return  string data_center[].country_name - 国家
     * @return  string data_center[].country_code - 国家代码
     * @return  int self_defined_field[].id - 自定义字段ID
     * @return  string self_defined_field[].field_name - 自定义字段名称
     * @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
	*/
	public function list()
	{
		$param = request()->param();

		$HostModel = new HostModel();
		$result = $HostModel->homeHostList($param);

		return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取DCIM产品详情
	 * @desc 获取DCIM产品详情
	 * @url /console/v1/remf_dcim/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @return  int order_id - 订单ID
     * @return  string ip - IP地址
     * @return  string additional_ip - 附加IP(英文分号分割)
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int model_config.id - 型号配置ID
     * @return  string model_config.name - 型号配置名称
     * @return  string model_config.cpu - 处理器
     * @return  string model_config.cpu_param - 处理器参数
     * @return  string model_config.memory - 内存
     * @return  string model_config.disk - 硬盘
     * @return  string model_config.gpu - 显卡
     * @return  int model_config.optional_memory[].id - 可选配内存配置ID
     * @return  string model_config.optional_memory[].value - 名称
     * @return  int model_config.optional_memory[].other_config.memory_slot - 槽位
     * @return  int model_config.optional_memory[].other_config.memory - 内存大小(GB)
     * @return  int model_config.optional_disk[].id - 可选配硬盘配置ID
     * @return  string model_config.optional_disk[].value - 名称
     * @return  int model_config.optional_gpu[].id - 可选配显卡配置ID
     * @return  string model_config.optional_gpu[].value - 名称
     * @return  int model_config.leave_memory - 当前机型剩余内存大小(GB)
     * @return  int model_config.max_memory_num - 当前机型可增加内存数量
     * @return  int model_config.max_disk_num - 当前机型可增加硬盘数量
     * @return  int model_config.max_gpu_num - 当前机型可增加显卡数量
     * @return  int line.id - 线路
     * @return  string line.name - 线路名称
     * @return  string line.bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  string bw - 带宽(0表示没有)
     * @return  string ip_num - IP数量
     * @return  string peak_defence - 防御峰值
     * @return  string username - 用户名
     * @return  string password - 密码
     * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  string data_center.country - 国家
     * @return  string data_center.iso - 国家代码
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分类
     * @return  string image.icon - 图标
     * @return  int config.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int config.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int config.manual_resource - 是否手动资源(0=不启用,1=启用)
     * @return  object optional_memory - 当前机器已添加内存配置({"5":1},5是ID,1是数量)
     * @return  object optional_disk - 当前机器已添加硬盘配置({"5":1},5是ID,1是数量)
     * @return  object optional_gpu - 当前机器已添加显卡配置({"5":1},5是ID,1是数量)
	 */
	public function detail()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->detail();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-27
	 * @title 获取部分详情
	 * @desc 获取部分详情
	 * @url /console/v1/remf_dcim/:id/part
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  string data_center.country - 国家
     * @return  string data_center.iso - 图标
     * @return  string ip - IP地址
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分类
     * @return  string image.icon - 图标
	 */
	public function detailPart()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/part', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->detailPart();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 开机
	 * @desc 开机
	 * @url /console/v1/remf_dcim/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function on()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/on', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name'                  => 'updownstream_action_failed_notice',
					'email_description'     => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description'       => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param'=>[
                            'action' => lang_plugins('res_mf_dcim_on'),
                        ],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->on();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 关机
	 * @desc 关机
	 * @url /console/v1/remf_dcim/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function off()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/off', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name'                  => 'updownstream_action_failed_notice',
					'email_description'     => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description'       => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param'=>[
                            'action' => lang_plugins('res_mf_dcim_off'),
                        ],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->off();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /console/v1/remf_dcim/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function reboot()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reboot', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name' => 'updownstream_action_failed_notice',
					'email_description' => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param' => [
							'action' => lang_plugins('res_mf_dcim_reboot'),
						],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->reboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
     * 时间 2024-05-08
     * @title 批量操作
     * @desc 批量操作
     * @url /console/v1/remf_dcim/batch_operate
     * @method  POST
     * @author theworld
     * @version v1
     * @param   array id - 产品ID require
     * @param   string action - 动作on开机off关机reboot重启 require
     */
    public function batchOperate()
    {
        $param = request()->param();

        $HostValidate = new HostValidate();
        if (!$HostValidate->scene('batch')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

        $id = $param['id'] ?? [];
        $id = array_unique(array_filter($id, function ($x) {
            return is_numeric($x) && $x > 0;
        }));

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [],
        ];

        $action = [
            'on' => 'on',
            'off' => 'off',
            'reboot' => 'reboot',
        ];

        foreach ($id as $v) {
        	$res = reserver_api('MfDcim', 'cloud', $action[$param['action']], ['id' => (int)$v]);

        	$result['data'][] = ['id' => (int)$v, 'status' => $res['status'], 'msg' => $res['msg']];
        }

        return json($result);
    }

	/**
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/remf_dcim/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int more 0 获取更多信息(0=否,1=是)
	 * @return  string url - 控制台地址
	 * @return  string vnc_url - 控制台websocket地址(more=1返回)
	 * @return  string vnc_pass - vnc密码(more=1返回)
	 * @return  string password - 机器密码(more=1返回)
	 * @return  string token - 控制台页面令牌(more=1返回)
	 */
	public function vnc()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/vnc?more=1', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$cache = $result['data'];
				unset($cache['url']);
 
				if(isset($cache['token'])){
					Cache::set('remf_dcim_vnc_'.$param['id'], $cache, 30*60);
					if(!isset($param['more']) || $param['more'] != 1){
						// 不获取更多信息
						$result['data'] = [];
					}
					// 转到当前res模块
					$result['data']['url'] = request()->domain().'/console/v1/remf_dcim/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->vnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /console/v1/remf_dcim/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string tmp_token - 控制台页面令牌 require
	 */
	public function vncPage()
	{
		$param = request()->param();

		$cache = Cache::get('remf_dcim_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			// 缓存Authorization信息
			// $jwt = get_header_jwt();
			// if(!empty($jwt)){
			// 	$cache['authorization'] = $jwt;
			// 	Cache::set('remf_dcim_vnc_'.$param['id'], $cache, 30*60);
			// }
			
			$cache['host_id'] = $param['id']; // 添加产品ID到模板变量
			$cache['is_admin'] = 0;
			$cache['admin_dir'] = "";

			View::assign($cache);
		}else{
			return lang_plugins('res_mf_dcim_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/mf_dcim/view/vnc_page.html');
	}

	/**
	 * 时间 2024-08-25
	 * @title 重启VNC
	 * @desc 重启VNC
	 * @url /console/v1/remf_dcim/:id/vnc/restart
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param int id - 产品ID require
	 */
	public function restartVnc()
	{
		$param = request()->param();

		// 使用Authorization验证
		// $jwt = get_header_jwt();
		// if(empty($jwt)){
		// 	return json(['status'=>401, 'msg'=>'Unauthorized']);
		// }
		
		// // 验证缓存中的Authorization是否匹配
		// $cache = Cache::get('remf_dcim_vnc_'.$param['id']);
		// if(empty($cache) || !isset($cache['authorization']) || $cache['authorization'] !== $jwt){
		// 	return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_vnc_token_expired_please_reopen')]);
		// }

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/vnc/restart', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_restart_vnc_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_restart_vnc_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				// system_notice([
				// 	'name' => 'updownstream_action_failed_notice',
				// 	'email_description' => lang('updownstream_action_failed_notice_send_mail'),
				// 	'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
				// 	'task_data' => [
				// 		'client_id' => $HostModel['client_id'],
				// 		'host_id' 	=> $HostModel['id'],
				// 		'template_param' => [
				// 			'action' => lang_plugins('res_mf_dcim_restart_vnc'),
				// 		],
				// 	],
				// ]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->restartVnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /console/v1/remf_dcim/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

        if(in_array($HostModel['status'], ['Pending','Failed'])){
            $status = [
                'status' => 'pending',
                'desc'   => lang_plugins('power_status_pending'),
            ];

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
                'data'   => $status,
            ];

            return json($result);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/status', $RouteLogic->upstream_host_id), [], 'GET');
			if($result['status'] == 200){
                if(class_exists('app\common\model\HostAdditionModel')){
                    $HostAdditionModel = new HostAdditionModel();
                    $HostAdditionModel->hostAdditionSave($HostModel['id'], [
                        'power_status'    => $result['data']['status'],
                    ]);
                }
            }
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->status();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /console/v1/remf_dcim/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 */
	public function resetPassword()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reset_password', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name' => 'updownstream_action_failed_notice',
					'email_description' => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param' => [
							'action' => lang_plugins('res_mf_dcim_reset_password'),
						],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->resetPassword();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 救援模式
	 * @desc 救援模式
	 * @url /console/v1/remf_dcim/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
	 */
	public function rescue()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/rescue', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name' => 'updownstream_action_failed_notice',
					'email_description' => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param' => [
							'action' => lang_plugins('res_mf_dcim_rescue'),
						],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->rescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 退出救援模式
	 * @desc 退出救援模式
	 * @url /console/v1/remf_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string client_operate_password - 操作密码,需要验证时传
	 */
	public function exitRescue()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/rescue/exit', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_exit_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_exit_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name' => 'updownstream_action_failed_notice',
					'email_description' => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param' => [
							'action' => lang_plugins('res_mf_dcim_exit_rescue'),
						],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->exitRescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/remf_dcim/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   string password - 密码 require
	 * @param   int port - 端口 require
	 * @param   int part_type - 分区类型0全盘格式化1第一分区格式化 require
	 */
	public function reinstall()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reinstall', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);

				// 发起成功后,直接同步信息
                $HostModel->syncAccount($HostModel['id']);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);

				system_notice([
					'name' => 'updownstream_action_failed_notice',
					'email_description' => lang('updownstream_action_failed_notice_send_mail'),
					'sms_description' => lang('updownstream_action_failed_notice_send_sms'),
					'task_data' => [
						'client_id' => $HostModel['client_id'],
						'host_id' 	=> $HostModel['id'],
						'template_param' => [
							'action' => lang_plugins('res_mf_dcim_reinstall'),
						],
					],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->reinstall();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @url /console/v1/remf_dcim/:id/chart
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int start_time - 开始秒级时间
	 * @return  array list - 图表数据
	 * @return  int list[].time - 时间(秒级时间戳)
	 * @return  float list[].in_bw - 进带宽
	 * @return  float list[].out_bw - 出带宽
	 * @return  string unit - 当前单位
	 */
	public function chart()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/chart', $RouteLogic->upstream_host_id), $param, 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->chart();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-30
	 * @title 获取网络流量
	 * @desc 获取网络流量
	 * @url /console/v1/remf_dcim/:id/flow
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string total -总流量
	 * @return  string used -已用流量
	 * @return  string leave - 剩余流量
	 * @return  string reset_flow_date - 流量归零时间
	 */
	public function flowDetail()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/flow', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->flowDetail();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 日志
	 * @desc 日志
	 * @url /console/v1/remf_dcim/:id/log
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param int id - 产品ID
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,create_time,ip
     * @param string sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID
     * @return string list[].description - 描述
     * @return string list[].create_time - 时间
     * @return int list[].ip - IP
     * @return int count - 系统日志总数
	 */
	public function log()
	{
		$param = request()->param();
		$param['type'] = 'host';
		$param['rel_id'] = $param['id'];

		$SystemLogModel = new SystemLogModel();
	 	$data = $SystemLogModel->systemLogList($param);

	 	$result = [
	 		'status' => 200,
	 		'msg'	 => lang_plugins('success_message'),
	 		'data'	 => $data,
	 	];
	 	return json($result);
	}

	/**
	 * 时间 2022-09-14
	 * @title 获取DCIM远程信息
	 * @desc 获取DCIM远程信息
	 * @url console/v1/remf_dcim/:id/remote_info
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string username - 远程用户名
	 * @return  string password - 远程密码
	 * @return  string port - 远程端口
	 * @return  int ip_num - IP数量
	 */
	public function remoteInfo()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/remote_info', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->remoteInfo();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 获取IP列表
	 * @desc 获取IP列表
	 * @url /console/v1/remf_dcim/:id/ip
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param int id - 产品ID
     * @param int page 1 页数
     * @param int limit - 每页条数
     * @return int list[].ip - IP
     * @return string list[].subnet_mask - 掩码
     * @return string list[].gateway - 网关
     * @return int count - 总数
	 */
	public function ipList()
	{
		$param = array_merge(request()->param(), ['page' => request()->page, 'limit' => request()->limit, 'sort' => request()->sort]);

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/ip', $RouteLogic->upstream_host_id), $param, 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->ipList();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 检查产品是够购买过镜像
	 * @desc 检查产品是够购买过镜像
	 * @url /console/v1/remf_dcim/:id/image/check
	 * @method GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string price - 需要支付的金额(0.00表示镜像免费或已购买)
	 * @return  string description - 描述
	 * @return  string price_client_level_discount - 价格等级折扣
	 */
	public function checkHostImage($return=0)
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('buy_image')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }
        $hostId = $param['id'];
        $host = HostModel::find($hostId);
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);


			unset($param['id']);
            $data = $param;
            $data['is_downstream'] = 1;
            $data['price_basis'] = $RouteLogic->price_basis??'agent';
            if($return == 1){
            	// 验证限制规则
            	$data['check_limit_rule'] = 1;
            }
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/image/check', $RouteLogic->upstream_host_id), $data, 'GET');
            $param['RouteLogic'] = $RouteLogic;
            $param['supplier'] = $supplier;
            $param['host'] = $host;
            if (isset($result['data']['price']) && $result['data']['price']==0){
                $param['image_price_zero'] = 1;
            }
            $result = upstream_upgrade_result_deal($param,$result);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->checkHostImage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
        if ($return==1){
            return $result;
        }
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 生成购买镜像订单
	 * @desc 生成购买镜像订单
	 * @url /console/v1/remf_dcim/:id/image/order
	 * @method POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string id - 订单ID
	 */
	public function createImageOrder()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('buy_image')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

        $hostId = $param['id'];
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

            $result = $this->checkHostImage(1);
            if($result['status'] == 400){
				return json($result);
			}
            $profit = $result['data']['profit']??0;

            $OrderModel = new OrderModel();

            $data = [
                'host_id'     => $hostId,
                'client_id'   => get_client_id(),
                'type'        => 'upgrade_config',
                'amount'      => $result['data']['price'],
                'description' => $result['data']['description'],
                'price_difference' => $result['data']['price_difference'],
                'renew_price_difference' => $result['data']['renew_price_difference'],
                'base_price' => $result['data']['base_price'],
                'upgrade_refund' => 0,
                'config_options' => [
                    'type'       => 'buy_image',
                    'param'		 => $param,
                ],
                'customfield' => $param['customfield'] ?? [],
            ];
            $result = $OrderModel->createOrder($data);
            if($result['status'] == 200){
                UpstreamOrderModel::create([
                    'supplier_id' 	=> $RouteLogic->supplier_id,
                    'order_id' 		=> $result['data']['id'],
                    'host_id' 		=> $hostId,
                    'amount' 		=> $data['amount'],
                    'profit' 		=> $profit,
                    'create_time' 	=> time(),
                ]);
            }
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->createImageOrder();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 计算产品配置升级价格
	 * @desc 计算产品配置升级价格
	 * @url /console/v1/remf_dcim/:id/common_config
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int ip_num - 公网IP数量
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
     * @param   object optional_memory - 变更后的内存({"5":1},5是ID,1是数量)
     * @param   object optional_disk - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @param   object optional_gpu - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @return  string price - 价格
     * @return  string description - 生成的订单描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string discount - 用户等级折扣
     * @return  string price_client_level_discount - 价格等级折扣
     * @return  string price_difference_client_level_discount - 差价等级折扣
     * @return  string renew_price_difference_client_level_discount - 续费差价等级折扣
	 */
	public function calCommonConfigPrice($return=0)
	{
		$param = request()->param();
		
		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

        $hostId = $param['id'];
        $host = HostModel::find($hostId);
 		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);

			unset($param['id']);
            $data = $param;
            $data['is_downstream'] = 1;
            $data['price_basis'] = $RouteLogic->price_basis??'agent';
            if($return == 1){
            	// 验证限制规则
            	$data['check_limit_rule'] = 1;
            }
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/common_config', $RouteLogic->upstream_host_id), $data, 'GET');
            $param['RouteLogic'] = $RouteLogic;
            $param['supplier'] = $supplier;
            $param['host'] = $host;
            $result = upstream_upgrade_result_deal($param,$result);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->calCommonConfigPrice();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
        if ($return==1){
            return $result;
        }
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 生成产品配置升级订单
	 * @desc 生成产品配置升级订单
	 * @url /console/v1/remf_dcim/:id/common_config/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int ip_num - 公网IP数量
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
     * @param   object optional_memory - 变更后的内存({"5":1},5是ID,1是数量)
     * @param   object optional_disk - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @param   object optional_gpu - 变更后的硬盘({"5":1},5是ID,1是数量)
	 * @return  string id - 订单ID
	 */
	public function createCommonConfigOrder()
	{
		$param = request()->param();
		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

        $hostId = $param['id'];
 		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $this->calCommonConfigPrice(1);
			if($result['status'] == 400){
				return json($result);
			}

			$profit = $result['data']['profit']??0;

            $OrderModel = new OrderModel();

            $data = [
                'host_id'     => $hostId,
                'client_id'   => get_client_id(),
                'type'        => 'upgrade_config',
                'amount'      => $result['data']['price'],
                'description' => $result['data']['description'],
                'price_difference' => $result['data']['price_difference'],
                'renew_price_difference' => $result['data']['renew_price_difference'],
                'base_price' => $result['data']['base_price'],
                'upgrade_refund' => 0,
                'config_options' => [
                    'type'       => 'upgrade_common_config',
                    'param'		 => $param,
                ],
                'customfield' => $param['customfield'] ?? [],
            ];
            $result = $OrderModel->createOrder($data);
            if($result['status'] == 200){
                UpstreamOrderModel::create([
                    'supplier_id' 	=> $RouteLogic->supplier_id,
                    'order_id' 		=> $result['data']['id'],
                    'host_id' 		=> $hostId,
                    'amount' 		=> $data['amount'],
                    'profit'        => $profit,
                    'create_time' 	=> time(),
                ]);
            }
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->createCommonConfigOrder();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
     * 时间 2023-02-14
     * @title 获取升级防御配置
     * @desc  获取升级防御配置
     * @url /console/v1/remf_dcim/:id/upgrade_defence_config
     * @method  GET
     * @author theworld
     * @version v1
     * @param   int id - 产品ID  require
     * @param   string ip - IP require
     * @return  string defence[].value - 防御
     * @return  string defence[].desc - 防御显示
     * @return  string current_defence - IP当前防御
     */
    public function defenceConfig()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            unset($param['id']);
            $result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/upgrade_defence_config', $RouteLogic->upstream_host_id), $param, 'GET');
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\mf_dcim\controller\home\CloudController')){
                    return (new \server\mf_dcim\controller\home\CloudController())->defenceConfig();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2025-01-13
     * @title 计算升级防御价格
     * @desc  计算升级防御价格
     * @url  /console/v1/remf_dcim/:id/upgrade_defence/price
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string ip - IP require
     * @param   string peak_defence - 防御峰值 require
     * @return  string price - 价格
     * @return  string description - 描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
     * @return  string price_client_level_discount - 价格等级折扣
     * @return  string price_difference_client_level_discount - 差价等级折扣
     * @return  string renew_price_difference_client_level_discount - 续费差价等级折扣
     */
    public function calDefencePrice($return=0)
    {
        $param = request()->param();
        $HostValidate = new HostValidate();
        if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }
        $hostId = $param['id'];
        $host = HostModel::find($hostId);
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);

            // 标志为下游
            unset($param['id']);
            $data = $param;
            $data['is_downstream'] = 1;
            $data['price_basis'] = $RouteLogic->price_basis??'agent';
            $result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/upgrade_defence/price', $RouteLogic->upstream_host_id), $data, 'GET');

            $param['RouteLogic'] = $RouteLogic;
            $param['supplier'] = $supplier;
            $param['host'] = $host;
            $result = upstream_upgrade_result_deal($param,$result);
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\mf_dcim\controller\home\CloudController')){
                    return (new \server\mf_dcim\controller\home\CloudController())->calDefencePrice();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
            }
        }
        if ($return==1){
            return $result;
        }
        return json($result);
    }

    /**
     * @时间 2025-01-13
     * @title 生成升级防御订单
     * @desc  生成升级防御订单
     * @author hh
     * @version v1
     * @url  /console/v1/remf_dcim/:id/upgrade_defence/order
     * @method  POST
     * @param   int id - 产品ID require
     * @param   string ip - IP require
     * @param   string peak_defence - 防御峰值 require
     * @return  int id - 订单ID
     * @return  string amount - 订单价格
     */
    public function createDefenceOrder()
    {
        $param = request()->param();

        $HostValidate = new HostValidate();
        if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

        $hostId = $param['id'];
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // 计算汇率
            $result = $this->calDefencePrice(1);
            $profit = $result['data']['profit']??0;

            $OrderModel = new OrderModel();

            $data = [
                'host_id'     => $hostId,
                'client_id'   => get_client_id(),
                'type'        => 'upgrade_config',
                'amount'      => $result['data']['price'],
                'description' => $result['data']['description'],
                'price_difference' => $result['data']['price_difference'],
                'renew_price_difference' => $result['data']['renew_price_difference'],
                'base_price' => $result['data']['base_price'],
                'upgrade_refund' => 0,
                'config_options' => [
                    'type'       => 'upgrade_defence',
                    'param'      => $param,
                ],
                'customfield' => $param['customfield'] ?? [],
            ];
            $result = $OrderModel->createOrder($data);
            if($result['status'] == 200){
                UpstreamOrderModel::create([
                    'supplier_id'   => $RouteLogic->supplier_id,
                    'order_id'      => $result['data']['id'],
                    'host_id'       => $hostId,
                    'amount'        => $data['amount'],
                    'profit'        => $profit,
                    'create_time'   => time(),
                ]);
            }

        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\mf_dcim\controller\home\CloudController')){
                    return (new \server\mf_dcim\controller\home\CloudController())->createDefenceOrder();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
            }
        }
        return json($result);
    }

	/**
	 * 时间 2025-01-13
	 * @title 获取重装状态
	 * @desc 获取重装状态
	 * @url /console/v1/remf_dcim/:id/reinstall_status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data - 状态数据
	 * @return  int data.task_type - 任务类型(0=重装中)
	 */
	public function getReinstallStatus()
	{
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reinstall_status', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->getReinstallStatus();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-03-01
	 * @title 验证下单
	 * @desc 验证下单
	 * @url /console/v1/product/:id/remf_dcim/validate_settle
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
     * @param   int custom.duration_id - 周期ID require
     * @param   int custom.data_center_id - 数据中心ID require
     * @param   int custom.model_config_id - 型号配置ID require
     * @param   object custom.optional_memory - 变更后的内存({"5":1},5是ID,1是数量)
     * @param   object custom.optional_disk - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @param   object custom.optional_gpu - 变更后的硬盘({"5":1},5是ID,1是数量)
     * @param   int custom.image_id - 镜像ID
     * @param   string custom.bw - 带宽
     * @param   string custom.flow - 流量
     * @param   string custom.ip_num - 公网IP数量
     * @param   int custom.peak_defence - 防御峰值(G)
     * @return  string price - 价格 
     * @return  string renew_price - 续费价格 
     * @return  string billing_cycle - 周期 
     * @return  int duration - 周期时长
     * @return  string description - 订单子项描述
     * @return  string base_price - 基础价格
     * @return  string preview[].name - 配置项名称
     * @return  string preview[].value - 配置项值
     * @return  string preview[].price - 配置项价格
     * @return  string discount - 用户等级折扣
     * @return  string order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int order_item[].rel_id - 关联ID
     * @return  float order_item[].amount - 子项金额
     * @return  string order_item[].description - 子项描述
	 */
	public function validateSettle()
	{
		$param = request()->param();

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_dcim/validate_settle', $RouteLogic->upstream_product_id), $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->validateSettle();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

}

<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\validate\RecommendConfigValidate;

/**
 * @title 魔方云(自定义配置)-套餐配置
 * @desc  魔方云(自定义配置)-套餐配置
 * @use server\mf_cloud\controller\admin\RecommendConfigController
 */
class RecommendConfigController
{
	/**
	 * 时间 2023-02-03
	 * @title 添加套餐
	 * @desc 添加套餐
	 * @url /admin/v1/mf_cloud/recommend_config
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   string name - 名称 require
     * @param   string description - 描述
     * @param   int order 0 排序ID
     * @param   int data_center_id - 数据中心ID require
     * @param   int line_id - 线路ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小(GB) require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值(G)
     * @param   int ip_num - IP数量
     * @param   int gpu_num 0 显卡数量
     * @param   int ipv6_num - IPv6数量
     * @param   int in_bw - 流入带宽
     * @param   int flow - 流量(GB)
     * @param   int traffic_type - 流量计费方向(1=进,2=出,3=进+出)
     * @param   int due_not_free_gpu - 计费到期不自动释放GPU(0=否,1=是) require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int ontrial - 是否开启试用：0否默认，1是
     * @param   float ontrial_price - 试用价格
     * @param   int ontrial_stock_control - 试用库存开关：0否，1是
     * @param   int ontrial_qty - 试用库存
	 * @param   float on_demand_price - 按需价格
	 * @param   float on_demand_flow_price - 流量按需价格
     * @return  int id - 套餐ID
	 */
	public function create()
	{
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 套餐配置列表
	 * @desc 套餐配置列表
	 * @url /admin/v1/mf_cloud/recommend_config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby id 排序(id,order)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @param   int data_center_id - 数据中心ID
     * @return  int list[].id - 套餐ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  int list[].order - 排序ID
     * @return  int list[].product_id - 商品ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int list[].hidden - 是否隐藏(0=否,1=是)
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  array list[].rel_id - 升降级范围自选套餐ID
     * @return  int list[].upgrade_show - 升降是否显示(0=否,1=是)
     * @return  int list[].ontrial - 是否开启试用：0否默认，1是
     * @return  float list[].ontrial_price - 试用价格
     * @return  int list[].ontrial_stock_control - 试用库存开关：0否，1是
     * @return  int list[].ontrial_qty - 试用库存
	 * @return  string list[].on_demand_price - 按需价格
	 * @return  string list[].on_demand_flow_price - 流量按需价格
     * @return  int count - 总条数
	 */
	public function list()
	{
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$data = $RecommendConfigModel->recommendConfigList($param);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 修改套餐配置
	 * @desc 修改套餐配置
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  PUT
	 * @param   int id - 套餐ID require
     * @param   string name - 名称 require
     * @param   string description - 描述
     * @param   int order - 排序ID
     * @param   int data_center_id - 数据中心ID require
     * @param   int line_id - 线路ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小(GB) require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值(G)
     * @param   int ip_num - IP数量
     * @param   int gpu_num - 显卡数量
     * @param   int ipv6_num - IPv6数量
     * @param   int in_bw - 流入带宽 require
     * @param   int flow - 流量(GB)
     * @param   int traffic_type - 流量计费方向(1=进,2=出,3=进+出)
     * @param   int due_not_free_gpu - 计费到期不自动释放GPU(0=否,1=是)
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int ontrial - 是否开启试用：0否默认，1是
     * @param   float ontrial_price - 试用价格
     * @param   int ontrial_stock_control - 试用库存开关：0否，1是
     * @param   int ontrial_qty - 试用库存
	 * @param   float on_demand_price - 按需价格
	 * @param   float on_demand_flow_price - 流量按需价格
     * @author hh
     * @version v1
     */
	public function update()
	{
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }        
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除套餐配置
	 * @desc 删除套餐配置
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-10-24
	 * @title 套餐配置详情
	 * @desc  套餐配置详情
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  GET
	 * @param   int id - 套餐ID require
     * @return  int id - 套餐ID
     * @return  int product_id - 商品ID
     * @return  string name - 名称
     * @return  string description - 描述
     * @return  int order - 排序ID
     * @return  int data_center_id - 数据中心ID
     * @return  int cpu - 核心数
     * @return  int memory - 内存大小(GB)
     * @return  int system_disk_size - 系统盘大小(G)
     * @return  int data_disk_size - 数据盘大小(G)
     * @return  int bw - 带宽(Mbps)
     * @return  int peak_defence - 防御峰值(G)
     * @return  string system_disk_type - 系统盘类型
     * @return  string data_disk_type - 数据盘类型
     * @return  int flow - 流量
     * @return  int line_id - 线路ID
     * @return  int create_time - 创建时间
     * @return  int ip_num - IP数量
     * @return  int upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int hidden - 是否隐藏(0=否,1=是)
     * @return  int gpu_num - GPU数量
     * @return  int ipv6_num - IPv6数量
     * @return  int country_id - 国家ID
     * @return  string city - 城市
     * @return  int upgrade_show - 升降是否显示(0=否,1=是)
     * @return  int in_bw - 流入带宽
     * @return  int traffic_type - 流量计费方向(1=进,2=出,3=进+出)
     * @return  int due_not_free_gpu - 计费到期不自动释放GPU(0=否,1=是)
     * @return  int ipv4_num_upgrade - 是否支持IPv4数量升降级(0=否,1=是)
     * @return  int ipv6_num_upgrade - 是否支持IPv6数量升降级(0=否,1=是)
     * @return  int flow_upgrade - 是否支持流量升降级(0=否,1=是)
     * @return  int bw_upgrade - 是否支持带宽升降级(0=否,1=是)
     * @return  int defence_upgrade - 是否支持防御峰值升降级(0=否,1=是)
     * @return  int ontrial - 是否开启试用：0否默认，1是
     * @return  float ontrial_price - 试用价格
     * @return  int ontrial_stock_control - 试用库存开关：0否，1是
     * @return  int ontrial_qty - 试用库存
	 * @return  string on_demand_price - 按需价格
	 * @return  string on_demand_flow_price - 按需价格
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 名称
     * @return  string duration[].price - 价格
	 * @author hh
	 * @version v1
     */
	public function index()
	{
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$data = $RecommendConfigModel->recommendConfigIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => (object)$data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-10-24
	 * @title 保存套餐升降级范围
	 * @desc 保存套餐升降级范围
	 * @url /admin/v1/mf_cloud/recommend_config/upgrade_range
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   object recommend_config - 升降级范围(如{"5":{"upgrade_range":0, "rel_id": []}},5是套餐ID,upgrade_range:0=不可升降级,1=所有套餐,2=自选套餐,2的时候需要传入rel_id是所选套餐ID) require
	 */
	public function saveUpgradeRange()
	{
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->saveUpgradeRange($param);
		return json($result);
	}

	/**
	 * 时间 2023-10-26
	 * @title 切换订购是否显示
	 * @desc 切换订购是否显示
	 * @url admin/v1/mf_cloud/recommend_config/:id/hidden
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @param   int hidden - 是否隐藏(0=否,1=是) require
	 */
	public function updateHidden()
	{
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('update_hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }        
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->updateHidden($param);
		return json($result);
	}

	/**
	 * 时间 2024-06-14
	 * @title 切换升降级是否显示
	 * @desc  切换升降级是否显示
	 * @url  /admin/v1/mf_cloud/recommend_config/:id/upgrade_show
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @param   int upgrade_show - 升降级是否显示(0=否,1=是) require
	 */
	public function updateUpgradeShow()
	{
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('update_upgrade_show')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }        
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->updateUpgradeShow($param);
		return json($result);
	}

    /**
     * 时间 2025-02-14
     * @title 修改试用状态
     * @desc  修改试用状态
     * @url  /admin/v1/mf_cloud/recommend_config/:id/ontrial
     * @method  PUT
     * @author wyh
     * @version v1
     * @param   int id - 套餐ID require
     * @param   int ontrial - 是否试用(0=否,1=是) require
     */
    public function updateOntrial()
    {
        $param = request()->param();

        $RecommendConfigValidate = new RecommendConfigValidate();

        if (!$RecommendConfigValidate->scene('update_ontrial')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }

        $RecommendConfigModel = new RecommendConfigModel();

        $result = $RecommendConfigModel->updateOntrial($param);

        return json($result);
    }


}
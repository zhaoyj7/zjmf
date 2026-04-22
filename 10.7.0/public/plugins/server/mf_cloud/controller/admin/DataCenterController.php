<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\validate\DataCenterValidate;
use server\mf_cloud\validate\LineGpuValidate;

/**
 * @title 魔方云(自定义配置)-数据中心
 * @desc 魔方云(自定义配置)-数据中心
 * @use server\mf_cloud\controller\admin\DataCenterController
 */
class DataCenterController
{
	/**
	 * 时间 2022-06-15
	 * @title 创建数据中心
	 * @desc 创建数据中心
	 * @url /admin/v1/mf_cloud/data_center
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int country_id - 国家ID require
     * @param   string city - 城市 require
     * @param   string area - 区域 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int cloud_config_id - 魔方云配置ID require
     * @param   int order - 排序
     * @return  int id - 数据中心ID
	 */
	public function create()
	{
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->createDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 数据中心列表
	 * @desc 数据中心列表
	 * @url /admin/v1/mf_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  int list[].id - 数据中心ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  int list[].country_id - 国家ID
     * @return  string list[].cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return  int list[].cloud_config_id - 魔方云配置ID
     * @return  int list[].order - 排序
     * @return  string list[].gpu_name - 显卡名称
     * @return  string list[].country_name - 国家
     * @return  int list[].line[].id - 线路ID
     * @return  int list[].line[].data_center_id - 数据中心ID
     * @return  string list[].line[].name - 线路名称
     * @return  string list[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int list[].line[].hidden - 是否隐藏(0=否,1=是)
     * @return  string list[].line[].price - 价格
     * @return  string list[].line[].duration - 周期
     * @return  int count - 总条数
	 */
	public function list()
	{
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->dataCenterList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 修改数据中心
	 * @desc 修改数据中心
	 * @url /admin/v1/mf_cloud/data_center/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 数据中心ID require
     * @param   int country_id - 国家ID require
     * @param   string city - 城市 require
     * @param   string area - 区域 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int cloud_config_id - 魔方云配置ID require
     * @param   int order - 排序
	 */
	public function update()
	{
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }        
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->updateDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 删除数据中心
	 * @desc 删除数据中心
	 * @url /admin/v1/mf_cloud/data_center/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->deleteDataCenter((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 数据中心选择
	 * @desc 数据中心选择
	 * @url /admin/v1/mf_cloud/data_center/select
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 国家ID
     * @return  string list[].iso - 国家图标
     * @return  string list[].name - 国家名称
     * @return  string list[].city[].name - 城市名称
     * @return  int list[].city[].area[].id - 数据中心ID
     * @return  string list[].city[].area[].name - 区域名称
     * @return  int list[].city[].area[].line[].id - 线路ID
     * @return  string list[].city[].area[].line[].name - 线路名称
     * @return  string list[].city[].area[].line[].bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int list[].city[].area[].line[].defence_enable - 是否启用防护(0=未启用,1=启用)
     * @return  int count - 总条数
	 */
	public function dataCenterSelect()
	{
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->dataCenterSelect($param);
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 数据中心详情
	 * @desc  数据中心详情
	 * @url /admin/v1/mf_cloud/data_center/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
     * @return  int id - 数据中心ID
     * @return  int country_id - 国家ID
     * @return  string city - 城市
     * @return  string area - 区域
     * @return   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return   int cloud_config_id - 魔方云配置ID
     * @return   int order - 排序
     * @return   string gpu_name - GPU型号名称
     * @return   array gpu_data - GPU价格配置数据
     * @return   int gpu_data[].id - 通用配置ID
     * @return   string gpu_data[].type - 配置方式(radio=单选)
     * @return   int gpu_data[].value - 显卡数量
     * @return   int gpu_data[].product_id - 商品ID
     * @return   string gpu_data[].price - 价格
     * @return   string gpu_data[].duration - 周期
	 */
	public function dataCenterDetail()
	{
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$data = $DataCenterModel->dataCenterDetail($param['id']);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 修改数据中心GPU型号名称
	 * @desc  修改数据中心GPU型号名称
	 * @url /admin/v1/mf_cloud/data_center/:id/gpu_name
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 数据中心ID require
     * @param   string gpu_name - GPU型号名称 require
	 */
	public function updateGpuName()
	{
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('update_gpu_name')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }        
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->updateGpuName($param);
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 数据中心显卡配置详情
	 * @desc  数据中心显卡配置详情
	 * @url /admin/v1/mf_cloud/data_center/gpu/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 显卡数量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 * @return  string on_demand_price - 按需价格
	 */
	public function dataCenterGpuIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->dataCenterGpuIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 添加数据中心显卡配置
	 * @desc  添加数据中心显卡配置
	 * @url /admin/v1/mf_cloud/data_center/:id/gpu
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
     * @param   int value - 显卡数量 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function dataCenterGpuCreate()
	{
		$param = request()->param();

		$LineGpuValidate = new LineGpuValidate();
		if (!$LineGpuValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineGpuValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::DATA_CENTER_GPU;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 修改数据中心显卡配置
	 * @desc  修改数据中心显卡配置
	 * @url /admin/v1/mf_cloud/data_center/gpu/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 显卡数量 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function dataCenterGpuUpdate()
	{
		$param = request()->param();

		$LineGpuValidate = new LineGpuValidate();
		if (!$LineGpuValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineGpuValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 删除数据中心显卡配置
	 * @desc  删除数据中心显卡配置
	 * @url /admin/v1/mf_cloud/data_center/gpu/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function dataCenterGpuOptionDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::DATA_CENTER_GPU);
		return json($result);
	}

	/**
	 * 时间 2024-06-24
	 * @title 删除数据中心显卡
	 * @desc  删除数据中心显卡
	 * @url /admin/v1/mf_cloud/data_center/:id/gpu
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
	 */
	public function dataCenterGpuDelete()
	{
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->dataCenterGpuDelete($param);
		return json($result);
	}


}



<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\LineModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\validate\LineValidate;
use server\mf_cloud\validate\LineBwValidate;
use server\mf_cloud\validate\LineFlowValidate;
use server\mf_cloud\validate\LineDefenceValidate;
use server\mf_cloud\validate\LineIpValidate;
use server\mf_cloud\validate\LineGpuValidate;
use server\mf_cloud\validate\LineIpv6Validate;
use server\mf_cloud\validate\LineFlowOnDemandValidate;

/**
 * @title 魔方云(自定义配置)-线路
 * @desc  魔方云(自定义配置)-线路
 * @use server\mf_cloud\controller\admin\LineController
 */
class LineController
{
	/**
	 * 时间 2023-02-02
	 * @title 添加线路
	 * @desc 添加线路
	 * @url /admin/v1/mf_cloud/line
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int data_center_id - 数据中心ID require
     * @param   string name - 名称 require
     * @param   string bill_type - 计费类型(bw=带宽计费,flow=流量计费) require
     * @param   string bw_ip_group - 计费IP分组
     * @param   int defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string defence_ip_group - 防护IP分组
     * @param   int ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @param   string order_default_defence - 新订购默认防御(传防御规则value)
     * @param   int order 0 排序
     * @param   array bw_data - 带宽计费数据 requireIf,bill_type=bw
     * @param   string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @param   int bw_data[].value - 带宽
     * @param   int bw_data[].min_value - 最小值
     * @param   int bw_data[].max_value - 最大值
     * @param   object bw_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string bw_data[].other_config.in_bw - 进带宽
     * @param   string bw_data[].other_config.advanced_bw - 智能带宽规则ID
	 * @param   float bw_data[].on_demand_price - 按需价格
     * @param   array flow_data - 流量计费数据 requireIf,bill_type=flow
     * @param   int flow_data[].value - 流量(GB,0=无限流量) require
     * @param   object flow_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int flow_data[].other_config.in_bw - 进带宽 require
     * @param   int flow_data[].other_config.out_bw - 出带宽 require
     * @param   int flow_data[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
     * @param   string flow_data[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
	 * @param   array flow_data_on_demand - 按需流量计费数据
     * @param   int flow_data_on_demand[].other_config.in_bw - 进带宽 require
     * @param   int flow_data_on_demand[].other_config.out_bw - 出带宽 require
     * @param   int flow_data_on_demand[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
	 * @param   float flow_data_on_demand[].on_demand_price - 按需价格
     * @param   array defence_data - 防护数据
     * @param   string defence_data[].value - 防御峰值(G) require
     * @param   object defence_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string defence_data[].firewall_type - 防火墙类型
     * @param   int defence_data[].defence_rule_id - 防御规则ID
	 * @param   float defence_data[].on_demand_price - 按需价格
     * @param   array ip_data - 附加IP数据
     * @param   string ip_data[].type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int ip_data[].value - IP数量
     * @param   int ip_data[].min_value - 最小值
     * @param   int ip_data[].max_value - 最大值
     * @param   object ip_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float ip_data[].on_demand_price - 按需价格
     * @param   int ipv6_enable - 启用IPv6(0=关闭,1=开启)
     * @param   string ipv6_group_id - IPv6分组ID
     * @param   string ipv6_data[].type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int ipv6_data[].value - IPv6数量
     * @param   int ipv6_data[].min_value - 最小值
     * @param   int ipv6_data[].max_value - 最大值
     * @param   object ipv6_data[].price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float ipv6_data[].on_demand_price - 按需价格
     * @return  int id - 线路ID
	 */
	public function create()
	{
		$param = request()->param();

		$LineValidate = new LineValidate();
		if (!$LineValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineValidate->getError())]);
        }
		$LineModel = new LineModel();

		$result = $LineModel->lineCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 修改线路
	 * @desc 修改线路
	 * @url /admin/v1/mf_cloud/line/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 线路ID require
     * @param   string name - 线路名称 require
     * @param   string bw_ip_group - 计费IP分组
     * @param   int defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string defence_ip_group - 防护IP分组
     * @param   int ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int order - 排序
     * @param   int ipv6_enable - 启用IPv6(0=关闭,1=开启) require
     * @param   string ipv6_group_id - IPv6分组ID
     * @param   int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @param   string order_default_defence - 新订购默认防御(传防御规则ID)
	 */
	public function update()
	{
		$param = request()->param();

		$LineValidate = new LineValidate();
		if (!$LineValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineValidate->getError())]);
        }        
		$LineModel = new LineModel();

		$result = $LineModel->lineUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路
	 * @desc 删除线路
	 * @url /admin/v1/mf_cloud/line/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$LineModel = new LineModel();

		$result = $LineModel->lineDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路详情
	 * @desc 线路详情
	 * @url /admin/v1/mf_cloud/line/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 线路ID require
     * @return  int id - 线路ID
     * @return  string name - 线路名称
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string bw_ip_group - 计费IP分组
     * @return  int defence_enable - 启用防护价格配置(0=关闭,1=开启)
     * @return  string defence_ip_group - 防护IP分组
     * @return  int ip_enable - 启用附加IP(0=关闭,1=开启)
     * @return  int link_clone - 链接创建(0=关闭,1=开启)
     * @return  int order - 排序
     * @return  string ipv6_group_id - IPv6分组ID
     * @return  int bw_data[].id - 配置ID
     * @return  string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw_data[].value - 带宽
     * @return  int bw_data[].min_value - 最小值
     * @return  int bw_data[].max_value - 最大值
     * @return  int bw_data[].product_id - 商品ID
     * @return  string bw_data[].price - 价格
     * @return  string bw_data[].duration - 周期
	 * @return  string bw_data[].on_demand_price - 按需价格
     * @return  int flow_data[].id - 配置ID
     * @return  string flow_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int flow_data[].value - 流量
     * @return  int flow_data[].product_id - 商品ID
     * @return  string flow_data[].price - 价格
     * @return  string flow_data[].duration - 周期
	 * @return  int flow_data_on_demand[].id - 配置ID
     * @return  string flow_data_on_demand[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int flow_data_on_demand[].value - 流量
     * @return  int flow_data_on_demand[].product_id - 商品ID
     * @return  string flow_data_on_demand[].on_demand_price - 按需价格
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int defence_data[].value - 防御峰值(G)
     * @return  int defence_data[].product_id - 商品ID
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
	 * @return  string defence_data[].on_demand_price - 按需价格
     * @return  int ip_data[].id - 配置ID
     * @return  string ip_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ip_data[].value - IP数量
     * @return  int ip_data[].min_value - 最小值
     * @return  int ip_data[].max_value - 最大值
     * @return  int ip_data[].product_id - 商品ID
     * @return  string ip_data[].price - 价格
     * @return  string ip_data[].duration - 周期
	 * @return  string ip_data[].on_demand_price - 按需价格
     * @return  int ipv6_data[].id - 配置ID
     * @return  string ipv6_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ipv6_data[].value - IP数量
     * @return  int ipv6_data[].product_id - 商品ID
     * @return  string ipv6_data[].price - 价格
     * @return  string ipv6_data[].duration - 周期
	 * @return  string ipv6_data[].on_demand_price - 按需价格
	 */
	public function index()
	{
		$param = request()->param();

		$LineModel = new LineModel();

		$data = $LineModel->lineIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路带宽配置详情
	 * @desc  线路带宽配置详情
	 * @url /admin/v1/mf_cloud/line_bw/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 带宽
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.in_bw - 流入带宽
     * @return  string other_config.advanced_bw - 智能带宽规则ID
	 * @return  string on_demand_price - 按需价格
	 */
	public function lineBwIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineBwIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路带宽配置
	 * @desc  添加线路带宽配置
	 * @url /admin/v1/mf_cloud/line/:id/line_bw
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int value - 带宽 requireIf,type=radio
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string other_config.in_bw - 进带宽
     * @param   string other_config.advanced_bw - 智能带宽规则ID
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineBwCreate()
	{
		$param = request()->param();

		$LineBwValidate = new LineBwValidate();
		if (!$LineBwValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineBwValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_BW;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路带宽配置
	 * @desc  修改线路带宽配置
	 * @url /admin/v1/mf_cloud/line_bw/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 带宽
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   string other_config.in_bw - 进带宽
     * @param   string other_config.advanced_bw - 智能带宽规则ID
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineBwUpdate()
	{
		$param = request()->param();

		$LineBwValidate = new LineBwValidate();
		if (!$LineBwValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineBwValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路带宽配置
	 * @desc 删除线路带宽配置
	 * @url /admin/v1/mf_cloud/line_bw/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineBwDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_BW);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路流量配置详情
	 * @desc 线路流量配置详情
	 * @url /admin/v1/mf_cloud/line_flow/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 流量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  int other_config.in_bw - 入站带宽
     * @return  int other_config.out_bw - 出站带宽
     * @return  int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
	 */
	public function lineFlowIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineFlowIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路流量配置
	 * @desc 添加线路流量配置
	 * @url /admin/v1/mf_cloud/line/:id/line_flow
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   int value - 流量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
     * @param  	string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
	 */
	public function lineFlowCreate()
	{
		$param = request()->param();

		$LineFlowValidate = new LineFlowValidate();
		if (!$LineFlowValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_FLOW;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路流量配置
	 * @desc 修改线路流量配置
	 * @url /admin/v1/mf_cloud/line_flow/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - 流量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
     * @param  	string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
	 */
	public function lineFlowUpdate()
	{
		$param = request()->param();

		$LineFlowValidate = new LineFlowValidate();
		if (!$LineFlowValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路流量配置
	 * @desc 删除线路流量配置
	 * @url /admin/v1/mf_cloud/line_flow/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineFlowDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_FLOW);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路防护配置详情
	 * @desc 线路防护配置详情
	 * @url /admin/v1/mf_cloud/line_defence/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 防御峰值
     * @return  string firewall_type - 防火墙类型
     * @return  int defence_rule_id - 防御规则ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 * @return  string on_demand_price - 按需价格
	 */
	public function lineDefenceIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineDefenceIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路防护配置
	 * @desc  添加线路防护配置
	 * @url /admin/v1/mf_cloud/line/:id/line_defence
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param   int value - 防御峰值(G)
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineDefenceCreate()
	{
		$param = request()->param();

		$LineDefenceValidate = new LineDefenceValidate();
		if (!$LineDefenceValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineDefenceValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_DEFENCE;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路防护配置
	 * @desc  修改线路防护配置
	 * @url /admin/v1/mf_cloud/line_defence/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   string value - 防御峰值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineDefenceUpdate()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

    /**
     * 时间 2025-03-13
     * @title 线路防护配置拖动排序
     * @desc  线路防护配置拖动排序
     * @url /admin/v1/mf_cloud/line_defence/:id/drag_sort
     * @method  PUT
     * @author wyh
     * @version v1
     * @param   int param.prev_id - 前一个防御ID(0=表示置顶) require
     * @param   int param.id - 当前防御ID require
     */
    public function lineDefenceDragSort()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $result = $OptionModel->lineDefenceDragSort($param);

        return json($result);
    }

	/**
	 * 时间 2023-02-03
	 * @title 删除线路防护配置
	 * @desc  删除线路防护配置
	 * @url /admin/v1/mf_cloud/line_defence/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineDefenceDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_DEFENCE);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 线路IP配置详情
	 * @desc 线路IP配置详情
	 * @url /admin/v1/mf_cloud/line_ip/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - IP数量
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 * @return  string on_demand_price - 按需价格
	 */
	public function lineIpIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineIpIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 添加线路IP配置
	 * @desc 添加线路IP配置
	 * @url /admin/v1/mf_cloud/line/:id/line_ip
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
	 * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int value - IP数量
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
     * @return  int id - 通用配置ID
	 */
	public function lineIpCreate()
	{
		$param = request()->param();

		$LineIpValidate = new LineIpValidate();
		if (!$LineIpValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_IP;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-02
	 * @title 修改线路IP配置
	 * @desc 修改线路IP配置
	 * @url /admin/v1/mf_cloud/line_ip/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - IP数量
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineIpUpdate()
	{
		$param = request()->param();

		$LineIpValidate = new LineIpValidate();
		if (!$LineIpValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除线路IP配置
	 * @desc 删除线路IP配置
	 * @url /admin/v1/mf_cloud/line_ip/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineIpDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_IP);
		return json($result);
	}

	/**
	 * 时间 2024-05-08
	 * @title 线路IPv6配置详情
	 * @desc  线路IPv6配置详情
	 * @url /admin/v1/mf_cloud/line_ipv6/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - IPv6数量
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 * @return  string on_demand_price - 按需价格
	 */
	public function lineIpv6Index()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineIpv6Index((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2024-05-08
	 * @title 添加线路IPv6配置
	 * @desc  添加线路IPv6配置
	 * @url /admin/v1/mf_cloud/line/:id/line_ipv6
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
	 * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int value - IPv6数量
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
     * @return  int id - 通用配置ID
	 */
	public function lineIpv6Create()
	{
		$param = request()->param();

		$LineIpv6Validate = new LineIpv6Validate();
		if (!$LineIpv6Validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpv6Validate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_IPV6;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2024-05-08
	 * @title 修改线路IPv6配置
	 * @desc  修改线路IPv6配置
	 * @url /admin/v1/mf_cloud/line_ipv6/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   int value - IPv6数量
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineIpv6Update()
	{
		$param = request()->param();

		$LineIpv6Validate = new LineIpv6Validate();
		if (!$LineIpv6Validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineIpv6Validate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2024-05-08
	 * @title 删除线路IPv6配置
	 * @desc  删除线路IPv6配置
	 * @url /admin/v1/mf_cloud/line_ipv6/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineIpv6Delete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_IPV6);
		return json($result);
	}

	/**
	 * 时间 2024-06-21
	 * @title 切换订购是否显示
	 * @desc  切换订购是否显示
	 * @url /admin/v1/mf_cloud/line/:id/hidden
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
	 * @param   int hidden - 是否隐藏(0=否,1=是) require
	 */
	public function updateHidden()
	{
		$param = request()->param();

		$LineValidate = new LineValidate();
		if (!$LineValidate->scene('update_hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineValidate->getError())]);
        }        
		$LineModel = new LineModel();

		$result = $LineModel->updateHidden($param);
		return json($result);
	}

    /**
     * 时间 2023-02-02
     * @title 线路导入防火墙防御规则
     * @desc 线路导入防火墙防御规则
     * @url /admin/v1/mf_cloud/line/:id/firewall_defence_rule
     * @method  POST
     * @author theworld
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string firewall_type - 防火墙类型
     * @param   array defence_rule_id - 防御规则ID
     */
    public function importDefenceRule()
    {
        $param = request()->param();

        $LineDefenceValidate = new LineDefenceValidate();
        if (!$LineDefenceValidate->scene('import')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineDefenceValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_DEFENCE;
        $param['rel_id'] = $param['id'];

        $OptionModel = new OptionModel();

        $result = $OptionModel->importDefenceRule($param);
        return json($result);
    }

	/**
	 * 时间 2025-03-24
	 * @title 线路流量按需配置详情
	 * @desc  线路流量按需配置详情
	 * @url /admin/v1/mf_cloud/line_flow_on_demand/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int other_config.in_bw - 入站带宽
     * @return  int other_config.out_bw - 出站带宽
     * @return  int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
	 * @return  string on_demand_price - 按需价格
	 */
	public function lineFlowOnDemandIndex()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->lineFlowOnDemandIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => (object)$data,
		];
		return json($result);
	}

	/**
	 * 时间 2025-03-24
	 * @title 添加线路流量按需配置
	 * @desc  添加线路流量按需配置
	 * @url /admin/v1/mf_cloud/line/:id/line_flow_on_demand
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 线路ID require
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineFlowOnDemandCreate()
	{
		$param = request()->param();

		$LineFlowValidate = new LineFlowOnDemandValidate();
		if (!$LineFlowValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::LINE_FLOW_ON_DEMAND;
        $param['rel_id'] = $param['id'];

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2025-03-24
	 * @title 修改线路流量按需配置
	 * @desc  修改线路流量按需配置
	 * @url /admin/v1/mf_cloud/line_flow_on_demand/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param  	int other_config.in_bw - 入站带宽 require
     * @param  	int other_config.out_bw - 出站带宽 require
     * @param  	int other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
	 * @param   float on_demand_price - 按需价格
	 */
	public function lineFlowOnDemandUpdate()
	{
		$param = request()->param();

		$LineFlowValidate = new LineFlowOnDemandValidate();
		if (!$LineFlowValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LineFlowValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2025-03-24
	 * @title 删除线路流量按需配置
	 * @desc  删除线路流量按需配置
	 * @url /admin/v1/mf_cloud/line_flow_on_demand/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function lineFlowOnDemandDelete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::LINE_FLOW_ON_DEMAND);
		return json($result);
	}


}
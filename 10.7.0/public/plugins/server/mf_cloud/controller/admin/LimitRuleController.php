<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\LimitRuleModel;
use server\mf_cloud\validate\LimitRuleValidate;

/**
 * @title 魔方云(自定义配置)-限制规则
 * @desc  魔方云(自定义配置)-限制规则
 * @use server\mf_cloud\controller\admin\LimitRuleController
 */
class LimitRuleController
{
	/**
	 * 时间 2024-05-11
	 * @title 添加限制规则
	 * @desc  添加限制规则
	 * @url /admin/v1/mf_cloud/limit_rule
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   object rule - 条件数据 require
     * @param   array rule.cpu.value - CPU
     * @param   string rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.memory.min - 内存最小值
     * @param   string rule.memory.max - 内存最大值
     * @param   string rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.memory.value - 内存值(当内存是单选时)
     * @param   array rule.data_center.id - 数据中心ID
     * @param   string rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.image.id - 操作系统ID
     * @param   string rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.ipv4_num.min - IPv4数量最小值
     * @param   string rule.ipv4_num.max - IPv4数量最大值
     * @param   string rule.ipv4_num.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.bw.min - 带宽最小值
     * @param   string rule.bw.max - 带宽最大值
     * @param   string rule.bw.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.flow.min - 流量最小值
     * @param   string rule.flow.max - 流量最大值
     * @param   string rule.flow.opt - 运算符(eq=等于,neq=不等于)
     * @param   object result - 结果数据 require
     * @param   array result.cpu[].value - CPU
     * @param   string result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.memory[].min - 内存最小值
     * @param   string result.memory[].max - 内存最大值
     * @param   string result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array result.memory[].value - 内存值(当内存是单选时)
     * @param   array result.image[].id - 操作系统ID
     * @param   string result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.system_disk[].min - 系统盘最小值
     * @param   string result.system_disk[].max - 系统盘最大值
     * @param   string result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.ipv4_num[].min - IPv4数量最小值
     * @param   string result.ipv4_num[].max - IPv4数量最大值
     * @param   string result.ipv4_num[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.bw[].min - 带宽最小值
     * @param   string result.bw[].max - 带宽最大值
     * @param   string result.bw[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.flow[].min - 流量最小值
     * @param   string result.flow[].max - 流量最大值
     * @param   string result.flow[].opt - 运算符(eq=等于,neq=不等于)
     * @return  int data.id - 限制规则ID
	 */
	public function create()
	{
		$param = request()->param();

		$LimitRuleValidate = new LimitRuleValidate();
		if (!$LimitRuleValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LimitRuleValidate->getError())]);
        }
		$LimitRuleModel = new LimitRuleModel();

		$result = $LimitRuleModel->limitRuleCreate($param);
		return json($result);
	}

	/**
	 * 时间 2024-05-11
	 * @title 修改限制规则
	 * @desc  修改限制规则
	 * @url /admin/v1/mf_cloud/limit_rule/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 限制规则ID require
     * @param   array rule - 条件数据 require
     * @param   array rule.cpu.value - CPU
     * @param   string rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.memory.min - 内存最小值
     * @param   string rule.memory.max - 内存最大值
     * @param   string rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.memory.value - 内存值(当内存是单选时)
     * @param   array rule.data_center.id - 数据中心ID
     * @param   string rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.image.id - 操作系统ID
     * @param   string rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.ipv4_num.min - IPv4数量最小值
     * @param   string rule.ipv4_num.max - IPv4数量最大值
     * @param   string rule.ipv4_num.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.bw.min - 带宽最小值
     * @param   string rule.bw.max - 带宽最大值
     * @param   string rule.bw.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.flow.min - 流量最小值
     * @param   string rule.flow.max - 流量最大值
     * @param   string rule.flow.opt - 运算符(eq=等于,neq=不等于)
     * @param   array result - 结果数据 require
     * @param   array result.cpu[].value - CPU
     * @param   string result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.memory[].min - 内存最小值
     * @param   string result.memory[].max - 内存最大值
     * @param   string result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array result.memory[].value - 内存值(当内存是单选时)
     * @param   array result.image[].id - 操作系统ID
     * @param   string result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.system_disk[].min - 系统盘最小值
     * @param   string result.system_disk[].max - 系统盘最大值
     * @param   string result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.ipv4_num[].min - IPv4数量最小值
     * @param   string result.ipv4_num[].max - IPv4数量最大值
     * @param   string result.ipv4_num[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.bw[].min - 带宽最小值
     * @param   string result.bw[].max - 带宽最大值
     * @param   string result.bw[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string result.flow[].min - 流量最小值
     * @param   string result.flow[].max - 流量最大值
     * @param   string result.flow[].opt - 运算符(eq=等于,neq=不等于)
	 */
	public function update()
	{
		$param = request()->param();
        // 获取下商品ID
        $param['product_id'] = LimitRuleModel::where('id', $param['id'])->value('product_id');

		$LimitRuleValidate = new LimitRuleValidate();
		if (!$LimitRuleValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($LimitRuleValidate->getError())]);
        }        
		$LimitRuleModel = new LimitRuleModel();

		$result = $LimitRuleModel->limitRuleUpdate($param);
		return json($result);
	}

    /**
     * 时间 2024-05-11
     * @title 限制规则列表
     * @desc  限制规则列表
     * @url /admin/v1/mf_cloud/limit_rule
     * @method  GET
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int list[].id - 限制规则ID
     * @return  array list[].rule.cpu.value - CPU
     * @return  string list[].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.memory.min - 内存最小值
     * @return  string list[].rule.memory.max - 内存最大值
     * @return  string list[].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.memory.value - 内存值(当内存是单选时)
     * @return  array list[].rule.data_center.id - 数据中心ID
     * @return  array list[].rule.data_center.name - 数据中心名称
     * @return  string list[].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.image.id - 操作系统ID
     * @return  array list[].rule.image.name - 操作系统名称
     * @return  string list[].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.ipv4_num.min - IPv4数量最小值
     * @return  string list[].rule.ipv4_num.max - IPv4数量最大值
     * @return  string list[].rule.ipv4_num.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.bw.min - 带宽最小值
     * @return  string list[].rule.bw.max - 带宽最大值
     * @return  string list[].rule.bw.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.flow.min - 流量最小值
     * @return  string list[].rule.flow.max - 流量最大值
     * @return  string list[].rule.flow.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.cpu[].value - CPU
     * @return  string list[].result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.memory[].min - 内存最小值
     * @return  string list[].result.memory[].max - 内存最大值
     * @return  string list[].result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.memory[].value - 内存值(当内存是单选时)
     * @return  array list[].result.image[].id - 操作系统ID
     * @return  array list[].result.image[].name - 操作系统名称
     * @return  string list[].result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.system_disk[].min - 系统盘最小值
     * @return  string list[].result.system_disk[].max - 系统盘最大值
     * @return  string list[].result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.ipv4_num[].min - IPv4数量最小值
     * @return  string list[].result.ipv4_num[].max - IPv4数量最大值
     * @return  string list[].result.ipv4_num[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.bw[].min - 带宽最小值
     * @return  string list[].result.bw[].max - 带宽最大值
     * @return  string list[].result.bw[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.flow[].min - 流量最小值
     * @return  string list[].result.flow[].max - 流量最大值
     * @return  string list[].result.flow[].opt - 运算符(eq=等于,neq=不等于)
     */
    public function list()
    {
        $param = request()->param();

        $LimitRuleModel = new LimitRuleModel();

        $data = $LimitRuleModel->limitRuleList($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

	/**
     * 时间 2024-05-11
     * @title 删除限制规则
     * @desc  删除限制规则
     * @url /admin/v1/mf_cloud/limit_rule/:id
     * @method  DELETE
     * @author hh
     * @version v1
     * @param   int id - 限制规则ID require
     */
	public function delete()
	{
		$param = request()->param();

		$LimitRuleModel = new LimitRuleModel();

		$result = $LimitRuleModel->limitRuleDelete($param);
		return json($result);
	}


}
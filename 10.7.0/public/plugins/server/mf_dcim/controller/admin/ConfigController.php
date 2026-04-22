<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\validate\ConfigValidate;
use server\mf_dcim\validate\GlobalDefenceValidate;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\model\OptionModel;

/**
 * @title DCIM(自定义配置)-其他设置
 * @desc DCIM(自定义配置)-其他设置
 * @use server\mf_dcim\controller\admin\ConfigController
 */
class ConfigController
{
	/**
	 * 时间 2022-06-20
	 * @title 获取设置
	 * @desc 获取设置
	 * @url /admin/v1/mf_dcim/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int manual_resource - 手动资源(0=不启用,1=启用)
     * @return  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_disk_renew - 硬盘是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_ip_num_renew - IP是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用)
     * @return  int level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_gpu_renew - 显卡是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string order_default_defence - 订购默认防御
     * @return  int auto_sync_dcim_stock - 自动同步DCIM库存(0=不启用,1=启用)
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_ip_num_order - IP是否应用等级优惠订购(0=不启用,1=启用)
	 */
	public function index()
    {
		$param = request()->param();

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->indexConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存设置
	 * @desc 保存设置
	 * @url /admin/v1/mf_dcim/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启) require
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用) require
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用) require
     * @param  int manual_resource - 手动资源(0=不启用,1=启用) require
     * @param  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_disk_renew - 硬盘是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_ip_num_renew - IP是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用) require
     * @param  int level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_gpu_renew - 显卡是否应用等级优惠续费(0=不启用,1=启用) require
     * @param  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启) require
     * @param  string order_default_defence - 订购默认防御 require
     * @param  int auto_sync_dcim_stock - 自动同步DCIM库存(0=不启用,1=启用) require
     * @param  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启) require
     * @param  string default_password_length - 默认密码长度 require
     * @param  int level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_ip_num_order - IP是否应用等级优惠订购(0=不启用,1=启用) require
	 */
	public function save()
    {
		$param = request()->param();

		$ConfigValidate = new ConfigValidate();
		if (!$ConfigValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->saveConfig($param);
		return json($result);
	}

	/**
	 * 时间 2024-01-19
	 * @title 获取DCIM分配服务器列表
	 * @desc  获取DCIM分配服务器列表
	 * @url /admin/v1/mf_dcim/host/:id/sales
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 产品ID require
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @param   int server_group_id - 搜索:DCIM服务器分组ID
     * @param   string ip - 搜索:IP
     * @return  int list[].id - DCIMID
     * @return  string list[].wltag - 标签
     * @return  string list[].typename - 型号
     * @return  string list[].group_name - 分组名称
     * @return  string list[].mainip - 主IP
     * @return  int list[].ip_num - IP数量
     * @return  int list[].ip[].id - IPID
     * @return  string list[].ip[].ipaddress - IP地址
     * @return  string list[].ip[].server_mainip - 是否主IP(true=是,false=否)
     * @return  string list[].in_bw - 进带宽
     * @return  string list[].out_bw - 出带宽
     * @return  string list[].remarks - 备注
     * @return  string list[].status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @return  int list[].host_id - 产品ID
     * @return  int list[].client_id - 所属用户
     * @return  string list[].type - 类型(rent=租用,trust=托管)
     * @return  string list[].dcim_url - dcim链接
     * @return  int count - 总条数
     * @return  string server_group[].id - 服务器分组ID
     * @return  string server_group[].name - 服务器分组名称
     * @return  string server_group[].config - 服务器分组配置
	 */
	public function dcimSalesList()
    {
        $param = request()->param();
        $param = array_merge($param, ['page' => $param['page'] ?? 1, 'limit' => $param['limit'] ?? 20]);

        $HostLinkModel = new HostLinkModel();
        $data = $HostLinkModel->dcimSalesList($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
	}

    /**
     * 时间 2024-01-19
     * @title 分配DCIM服务器
     * @desc  分配DCIM服务器
     * @url /admin/v1/mf_dcim/host/:id/assign
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int dcim_id - DCIMID require
     * @param   string status - 状态Free=空闲Fault=故障 当前已分配服务器空闲后要修改的状态
     */
    public function assignDcimServer()
    {
        $param = request()->param();
        
        $HostLinkModel = new HostLinkModel();
        $result = $HostLinkModel->assignDcimServer($param);
        return json($result);
    }

    /**
     * 时间 2024-01-23
     * @title 空闲DCIM服务器
     * @desc  空闲DCIM服务器
     * @url /admin/v1/mf_dcim/host/:id/free
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string status - 状态Free=空闲Fault=故障 require
     */
    public function freeDcimServer()
    {
        $param = request()->param();
        
        $HostLinkModel = new HostLinkModel();
        $result = $HostLinkModel->freeDcimServer($param);
        return json($result);
    }

    /**
     * 时间 2024-05-13
     * @title 获取防火墙防御规则
     * @desc 获取防火墙防御规则
     * @url /admin/v1/mf_dcim/firewall_defence_rule
     * @method  GET
     * @author theworld
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array rule - 防火墙规则
     * @return  string rule[].name - 防火墙类型名称
     * @return  string rule[].type - 防火墙类型
     * @return  array rule[].list - 防御规则
     * @return  int rule[].list[].id - 防御规则ID
     * @return  string rule[].list[].name - 名称
     * @return  string rule[].list[].defense_peak - 防御峰值,单位Gbps
     * @return  int rule[].list[].enabled - 是否可用(0=否1=是)
     * @return  int rule[].list[].create_time - 创建时间
     * @return  int rule[].list[].update_time - 更新时间
     */
    public function firewallDefenceRule()
    {
        $param = request()->param();

        $ConfigModel = new ConfigModel();
        $data = $ConfigModel->firewallDefenceRule($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-02
     * @title 导入防火墙防御规则
     * @desc 导入防火墙防御规则
     * @url /admin/v1/mf_dcim/firewall_defence_rule
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

        $GlobalDefenceValidate = new GlobalDefenceValidate();
        if (!$GlobalDefenceValidate->scene('import')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($GlobalDefenceValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::GLOBAL_DEFENCE;

        $OptionModel = new OptionModel();

        $result = $OptionModel->importDefenceRule($param);
        return json($result);
    }

    /**
     * 时间 2023-02-02
     * @title 全局防护配置列表
     * @desc 全局防护配置列表
     * @url /admin/v1/mf_dcim/global_defence
     * @method  GET
     * @author theworld
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].value - 防御峰值(G)
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  string defence_data[].firewall_type - 防火墙类型
     * @return  int defence_data[].defence_rule_id - 防御规则ID
     * @return  string defence_data[].defence_rule_name - 防御规则名称
     * @return  string defence_data[].defense_peak - 防御峰值
     * @return  int defence_data[].duration_price[].id - 周期ID
     * @return  string defence_data[].duration_price[].name - 周期名称
     * @return  string defence_data[].duration_price[].price - 价格
     */
    public function globalDefenceList()
    {
        $param = request()->param();
        $param['rel_type'] = OptionModel::GLOBAL_DEFENCE;
        $param['rel_id'] = 0;

        $OptionModel = new OptionModel();
        $result = $OptionModel->globalDefenceList($param);

        return json($result);
    }

    /**
     * 时间 2023-02-02
     * @title 全局防护配置详情
     * @desc 全局防护配置详情
     * @url /admin/v1/mf_dcim/global_defence/:id
     * @method  GET
     * @author theworld
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 防御峰值(G)
     * @return  string firewall_type - 防火墙类型
     * @return  int defence_rule_id - 防御规则ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function globalDefenceIndex()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $data = $OptionModel->globalDefenceIndex((int)$param['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('message_success'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-02
     * @title 修改全局防护配置
     * @desc 修改全局防护配置
     * @url /admin/v1/mf_dcim/global_defence/:id
     * @method  PUT
     * @author theworld
     * @version v1
     * @param   int id - 通用配置ID require
     * @param   int value - 防御峰值(G)
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     */
    public function globalDefenceUpdate()
    {
        $param = request()->param();

        $GlobalDefenceValidate = new GlobalDefenceValidate();
        if (!$GlobalDefenceValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($GlobalDefenceValidate->getError())]);
        }

        $OptionModel = new OptionModel();

        $result = $OptionModel->optionUpdate($param);
        return json($result);
    }

    /**
     * 时间 2025-03-20
     * @title 全局防护拖动排序
     * @desc  全局防护拖动排序
     * @url /admin/v1/mf_dcim/global_defence/:id/drag_sort
     * @method  PUT
     * @author wyh
     * @version v1
     * @param   int param.prev_id - 前一个防御ID(0=表示置顶) require
     * @param   int param.id - 当前防御ID require
     */
    public function globalDefenceDragSort()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $result = $OptionModel->globalDefenceDragSort($param);

        return json($result);
    }

    /**
     * 时间 2023-02-03
     * @title 删除全局防护配置
     * @desc 删除全局防护配置
     * @url /admin/v1/mf_dcim/global_defence/:id
     * @method  DELETE
     * @author theworld
     * @version v1
     * @param   int id - 通用配置ID require
     */
    public function globalDefenceDelete()
    {
        $param = request()->param();

        $OptionModel = new OptionModel();

        $result = $OptionModel->optionDelete((int)$param['id'], OptionModel::GLOBAL_DEFENCE);
        return json($result);
    }
}
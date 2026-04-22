<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 安全组规则外部关联表模型
 * @desc 安全组规则外部关联表模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel
 */
class IdcsmartSecurityGroupRuleLinkModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_rule_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_security_group_rule_id' => 'int',
        'server_id'         					=> 'int',       // 本地接口ID
        'security_rule_id'                      => 'string',    // 上游安全组ID/远程安全组ID
        'type'         				            => 'string',    
        'supplier_id'                           => 'int',       // 代理商ID
    ];


    /**
     * 时间 2022-06-29
     * @title 保存关联关系
     * @desc 保存关联关系
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.addon_idcsmart_security_group_rule_id - 安全组ID require
     * @param   int param.server_id - 接口ID
     * @param   int param.supplier_id - 供应商ID
     * @param   string param.security_rule_id - 魔方云安全组规则ID require
     * @param   string param.type host - 类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)
     */
    public function saveSecurityGroupRuleLink($param)
    {
    	$where = [];
    	$where[] = ['addon_idcsmart_security_group_rule_id', '=', $param['addon_idcsmart_security_group_rule_id']];
    	$where[] = ['type', '=', $param['type'] ?? ''];
    	
    	// 根据是本地服务器还是代理商来区分
    	if(!empty($param['supplier_id'])){
            $param['server_id'] = 0;
    	    $where[] = ['supplier_id', '=', $param['supplier_id']];
    	}else{
            $param['supplier_id'] = 0;
    	    $where[] = ['server_id', '=', $param['server_id'] ?? 0];
    	}

    	$securityGroupRuleLink = $this->where($where)->find();
    	if(!empty($securityGroupRuleLink)){
    		$this->where($where)->update(['security_rule_id'=>$param['security_rule_id']]);
    	}else{
    		$this->create($param, ['addon_idcsmart_security_group_rule_id','server_id','security_rule_id','type','supplier_id']);
    	}
    	return true;
    }

}
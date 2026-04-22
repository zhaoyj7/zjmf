<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路验证
 * @use  server\mf_cloud\validate\LineValidate
 */
class LineValidate extends Validate
{
	protected $rule = [
        'id'                    => 'require|integer',
        'data_center_id'        => 'require|integer',
        'name'                  => 'require|length:1,50',
        'bill_type'             => 'require|in:bw,flow',
        'bw_ip_group'           => 'integer',
        'defence_enable'        => 'require|in:0,1',
        'defence_ip_group'      => 'integer',
        'ip_enable'             => 'require|in:0,1',
        'link_clone'            => 'require|in:0,1',
        'bw_data'               => 'requireIf:bill_type,bw|array|checkBwData:thinkphp',
        'flow_data'             => 'requireCallback:checkRequireFlowData|array|checkFlowData:thinkphp',
        'defence_data'          => 'requireIf:defence_enable,1|array|checkDefenceData:thinkphp',
        'ip_data'               => 'requireIf:ip_enable,1|array|checkIpData:thinkphp',
        'order'                 => 'integer|between:0,999',
        'ipv6_enable'           => 'require|in:0,1',
        'ipv6_group_id'         => 'integer|max:10',
        'ipv6_data'             => 'requireIf:ipv6_enable,1|array|checkIpv6Data:thinkphp',
        'hidden'                => 'require|in:0,1',
        'sync_firewall_rule'    => 'requireIf:defence_enable,1|in:0,1',
        'order_default_defence' => 'max:100',
        'flow_data_on_demand'   => 'requireCallback:checkRequireFlowDataOnDemand|array|checkFlowDataOnDemand:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'data_center_id.require'        => 'please_select_data_center',
        'data_center_id.integer'        => 'please_select_data_center',
        'name.require'                  => 'please_input_line_name',
        'name.length'                   => 'line_name_length_error',
        'bill_type.require'             => 'please_select_line_bill_type',
        'bill_type.in'                  => 'please_select_line_bill_type',
        'bw_ip_group.integer'           => 'line_bw_ip_group_must_int',
        'defence_enable.require'        => 'line_defence_enable_param_error',
        'defence_enable.in'             => 'line_defence_enable_param_error',
        'defence_ip_group.integer'      => 'line_defence_ip_group_must_int',
        'ip_enable.require'             => 'line_ip_enable_param_error',
        'ip_enable.in'                  => 'line_ip_enable_param_error',
        'link_clone.require'            => 'mf_cloud_link_clone_param_error',
        'link_clone.in'                 => 'mf_cloud_link_clone_param_error',
        'bw_data.requireIf'             => 'please_add_at_lease_one_bw_data',
        'bw_data.array'                 => 'please_add_at_lease_one_bw_data',
        'flow_data.requireCallback'     => 'please_add_at_lease_one_flow_data',
        'flow_data.array'               => 'please_add_at_lease_one_flow_data',
        'defence_data.requireIf'        => 'please_add_at_lease_one_defence_data',
        'defence_data.array'            => 'please_add_at_lease_one_defence_data',
        'ip_data.requireIf'             => 'please_add_at_lease_one_ip_data',
        'ip_data.array'                 => 'please_add_at_lease_one_ip_data',
        'order.integer'                 => 'mf_cloud_order_format_error',
        'order.between'                 => 'mf_cloud_order_format_error',
        'ipv6_enable.require'           => 'mf_cloud_line_ipv6_enable_param_error',
        'ipv6_enable.in'                => 'mf_cloud_line_ipv6_enable_param_error',
        'ipv6_group_id.integer'         => 'mf_cloud_line_ipv6_group_id_integer',
        'ipv6_group_id.max'             => 'mf_cloud_line_ipv6_group_id_integer',
        'ipv6_data.requireIf'           => 'mf_cloud_line_ipv6_data_require',
        'ipv6_data.array'               => 'mf_cloud_line_ipv6_data_require',
        'sync_firewall_rule.requireIf'  => 'param_error',
        'sync_firewall_rule.in'         => 'param_error',
        'order_default_defence.max'     => 'param_error',
        'flow_data_on_demand.array'     => 'param_error',
        'flow_data_on_demand.requireCallback'      => 'please_add_at_lease_one_flow_data',
    ];

    protected $scene = [
        'create' => ['data_center_id','name','bill_type','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','bw_data','flow_data','defence_data','ip_data','order','ipv6_enable','ipv6_group_id','sync_firewall_rule','flow_data_on_demand'],
        'update' => ['id','name','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','order','ipv6_enable','ipv6_group_id','sync_firewall_rule','order_default_defence'],
        'update_hidden' => ['id','hidden'],
    ];

    public function checkBwData($value, $t, $param){
        if($param['bill_type'] == 'flow'){
            return true;
        }
        $type = null;
        $LineBwValidate = new LineBwValidate();
        foreach($value as $k=>$v){
            if (!$LineBwValidate->scene('line_create')->check($v)){
                return $LineBwValidate->getError();
            }
            // 验证类型是否一致
            if(!isset($type)){
                $type = $v['type'];
            }else{
                if($type != $v['type']){
                    return 'option_type_must_only_one_type';
                }
            }
            // 验证范围数字是否有交集
            if($type == 'radio'){
                if (!$LineBwValidate->scene('radio')->check($v)){
                    return $LineBwValidate->getError();
                }
            }else{
                if (!$LineBwValidate->scene('step')->check($v)){
                    return $LineBwValidate->getError();
                }
                foreach($value as $kk=>$vv){
                    if($k != $kk){
                        // 有交集
                        if(!($v['max_value']<$vv['min_value'] || $v['min_value']>$vv['max_value'])){
                            return 'line_bw_range_intersect';
                        }
                    }
                }
            }
        }
        if($type == 'radio'){
            $optionValue = array_column($value, 'value');
            if( count($optionValue) != count( array_unique($optionValue) )){
                return 'line_bw_already_exist';
            }
        }
        return true;
    }
    
    public function checkFlowData($value, $t, $param){
        if($param['bill_type'] == 'bw'){
            return true;
        }
        $exist = [];
        $LineFlowValidate = new LineFlowValidate();
        foreach($value as $v){
            if (!$LineFlowValidate->scene('line_create')->check($v)){
                return $LineFlowValidate->getError();
            }
            if(!isset($exist[ $v['value'] ][ $v['other_config']['out_bw'] ])){
                $exist[ $v['value'] ][ $v['other_config']['out_bw'] ] = 1;
            }else{
                return 'line_flow_already_exist';
            }
        }
        return true;
    }

    // 验证流量线路按需
    public function checkFlowDataOnDemand($value, $t, $param)
    {
        // 带宽线路不验证
        if($param['bill_type'] == 'bw'){
            return true;
        }
        $exist = [];
        $LineFlowOnDemandValidate = new LineFlowOnDemandValidate();
        foreach($value as $v){
            if (!$LineFlowOnDemandValidate->scene('line_create')->check($v)){
                return $LineFlowOnDemandValidate->getError();
            }
            if(!isset($exist[ $v['other_config']['out_bw'] ])){
                $exist[ $v['other_config']['out_bw'] ] = 1;
            }else{
                return 'line_flow_already_exist';
            }
        }
        return true;
    }

    public function checkDefenceData($value, $t, $param){
        $firewallType = '';
        $LineDefenceValidate = new LineDefenceValidate();
        foreach($value as $k=>$v){
            if(!empty($param['sync_firewall_rule'])){
                if (!$LineDefenceValidate->scene('lineCreateFirewall')->check($v)){
                    return $LineDefenceValidate->getError();
                }
                if(empty($firewallType)){
                    $firewallType = $v['firewall_type'];
                }else if($firewallType != $v['firewall_type']){
                    return 'mf_cloud_sync_firewall_type_error';
                }
                $value[$k]['value'] = $v['firewall_type'] . '_' . $v['defence_rule_id'];
            }else{
                if (!$LineDefenceValidate->scene('line_create')->check($v)){
                    return $LineDefenceValidate->getError();
                }
            }
        }
        $optionValue = array_column($value, 'value');
        if( count($optionValue) != count( array_unique($optionValue) )){
            return 'line_defence_already_exist';
        }
        return true;
    }

    public function checkIpData($value){
        $type = null;
        $LineIpValidate = new LineIpValidate();
        foreach($value as $k=>$v){
            if (!$LineIpValidate->scene('line_create')->check($v)){
                return $LineIpValidate->getError();
            }
            // 验证类型是否一致
            if(!isset($type)){
                $type = $v['type'];
            }else{
                if($type != $v['type']){
                    return 'option_type_must_only_one_type';
                }
            }
            // 验证范围数字是否有交集
            if($type == 'radio'){
                if (!$LineIpValidate->scene('radio')->check($v)){
                    return $LineIpValidate->getError();
                }
            }else{
                if (!$LineIpValidate->scene('step')->check($v)){
                    return $LineIpValidate->getError();
                }
                foreach($value as $kk=>$vv){
                    if($k != $kk){
                        // 有交集
                        if(!($v['max_value']<$vv['min_value'] || $v['min_value']>$vv['max_value'])){
                            return 'line_ip_already_exist';
                        }
                    }
                }
            }
        }
        if($type == 'radio'){
            $optionValue = array_column($value, 'value');
            if( count($optionValue) != count( array_unique($optionValue) )){
                return 'line_ip_already_exist';
            }
        }
        return true;
    }

    public function checkIpv6Data($value){
        $type = null;
        $LineIpv6Validate = new LineIpv6Validate();
        foreach($value as $k=>$v){
            if (!$LineIpv6Validate->scene('line_create')->check($v)){
                return $LineIpv6Validate->getError();
            }
            // 验证类型是否一致
            if(!isset($type)){
                $type = $v['type'];
            }else{
                if($type != $v['type']){
                    return 'option_type_must_only_one_type';
                }
            }
            // 验证范围数字是否有交集
            if($type == 'radio'){
                if (!$LineIpv6Validate->scene('radio')->check($v)){
                    return $LineIpv6Validate->getError();
                }
            }else{
                if (!$LineIpv6Validate->scene('step')->check($v)){
                    return $LineIpv6Validate->getError();
                }
                foreach($value as $kk=>$vv){
                    if($k != $kk){
                        // 有交集
                        if(!($v['max_value']<$vv['min_value'] || $v['min_value']>$vv['max_value'])){
                            return 'mf_cloud_line_ipv6_num_duplicated';
                        }
                    }
                }
            }
        }
        if($type == 'radio'){
            $optionValue = array_column($value, 'value');
            if( count($optionValue) != count( array_unique($optionValue) )){
                return 'mf_cloud_line_ipv6_num_duplicated';
            }
        }
        return true;
    }

    // 验证流量数据是否必须
    public function checkRequireFlowData($value, $data)
    {
        if($data['bill_type'] == 'flow' && empty($data['flow_data_on_demand'])){
            return true;
        }
    }

    // 验证流量数据是否必须
    public function checkRequireFlowDataOnDemand($value, $data)
    {
        if($data['bill_type'] == 'flow' && empty($data['flow_data'])){
            return true;
        }
    }



}
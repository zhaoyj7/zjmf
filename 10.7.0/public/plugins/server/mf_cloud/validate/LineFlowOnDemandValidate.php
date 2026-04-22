<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路流量按需验证
 * @use  server\mf_cloud\validate\LineFlowOnDemandValidate
 */
class LineFlowOnDemandValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'other_config'      => 'require|array|checkOtherConfig:thinkphp',
        'in_bw'             => 'require|integer|between:0,30000',
        'out_bw'            => 'require|integer|between:0,30000',
        'traffic_type'      => 'require|in:1,2,3',
        'on_demand_price'   => 'float|between:0,9999999',
    ];

    protected $message = [
        'id.require'                        => 'id_error',
        'id.integer'                        => 'id_error',
        'other_config.require'              => 'option_other_config_param_error',
        'other_config.array'                => 'option_other_config_param_error',
        'other_config.checkOtherConfig'     => 'option_other_config_param_error',
        'in_bw.require'                     => 'please_input_flow_in_bw',
        'in_bw.integer'                     => 'flow_in_bw_format_error',
        'in_bw.between'                     => 'flow_in_bw_format_error',
        'out_bw.require'                    => 'please_input_flow_out_bw',
        'out_bw.integer'                    => 'flow_out_bw_format_error',
        'out_bw.between'                    => 'flow_out_bw_format_error',
        'traffic_type.require'              => 'please_select_flow_traffic_type',
        'traffic_type.in'                   => 'please_select_flow_traffic_type',
        'on_demand_price.float'             => 'price_must_between_0_999999',
        'on_demand_price.between'           => 'price_must_between_0_999999',
    ];

    protected $scene = [
        'create'        => ['id','other_config','on_demand_price'],
        'update'        => ['id','other_config','on_demand_price'],
        'other_config'  => ['in_bw','out_bw','traffic_type'],
        'line_create'   => ['other_config','on_demand_price'],
    ];

    public function checkOtherConfig($value){
        $LineFlowOnDemandValidate = new LineFlowOnDemandValidate();
        if(!$LineFlowOnDemandValidate->scene('other_config')->check($value)){
            return $LineFlowOnDemandValidate->getError();
        }
        return true;
    }


}
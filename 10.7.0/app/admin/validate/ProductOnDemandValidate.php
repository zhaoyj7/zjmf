<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 商品按需计费配置验证
 */
class ProductOnDemandValidate extends Validate
{
	protected $rule = [
		'id'                        => 'require|integer|gt:0',
        'billing_cycle_unit'        => 'require|in:hour,day,month',
        'billing_cycle_day'         => 'requireIf:billing_cycle_unit,month|integer|between:1,31',
        'billing_cycle_point'       => 'checkBillingCyclePoint:thinkphp',
        'duration_id'               => 'integer',
        'duration_ratio'            => 'requireCallback:durationRatioRequire|float|between:0,99999999',
        'min_credit'                => 'require|float|between:0,99999999',
        'min_usage_time'            => 'require|integer|between:0,99999999',
        'min_usage_time_unit'       => 'require|in:second,minute,hour',
        'upgrade_min_billing_time'  => 'require|integer|between:0,99999999',
        'upgrade_min_billing_time_unit' => 'require|in:second,minute,hour',
        'grace_time'                => 'require|integer|between:0,99999999',
        'grace_time_unit'           => 'require|in:hour,day',
        'keep_time'                 => 'require|integer|between:0,99999999',
        'keep_time_unit'            => 'require|in:hour,day',
        'keep_time_billing_item'    => 'array',
        'initial_fee'               => 'float|between:0,99999999',
        'client_auto_delete'        => 'require|in:0,1',
        'on_demand_to_duration'     => 'require|in:0,1',
        'duration_to_on_demand'     => 'require|in:0,1',
        'credit_limit_pay'          => 'require|in:0,1',
    ];

    protected $message  =   [
    	'id.require'                    => 'id_error',
    	'id.integer'                    => 'id_error',
    	'id.gt'                         => 'id_error',
        'billing_cycle_unit.require'    => 'product_on_demand_billing_cycle_require',
        'billing_cycle_unit.in'         => 'product_on_demand_billing_cycle_require',
        'billing_cycle_day.requireIf'   => 'product_on_demand_billing_cycle_require',
        'billing_cycle_day.integer'     => 'product_on_demand_billing_cycle_error',
        'billing_cycle_day.between'     => 'product_on_demand_billing_cycle_error',
        'duration_id.integer'           => 'product_on_demand_duration_id_require',
        'duration_ratio.requireCallback'      => 'product_on_demand_duration_ratio_require',
        'duration_ratio.float'          => 'product_on_demand_duration_ratio_format_error',
        'duration_ratio.between'        => 'product_on_demand_duration_ratio_format_error',
        'min_credit.require'            => 'product_on_demand_min_credit_require',
        'min_credit.float'              => 'product_on_demand_min_credit_format_error',
        'min_credit.between'            => 'product_on_demand_min_credit_format_error',
        'min_usage_time.require'        => 'product_on_demand_min_usage_time_require',
        'min_usage_time.integer'        => 'product_on_demand_min_usage_time_format_error',
        'min_usage_time.between'        => 'product_on_demand_min_usage_time_format_error',
        'min_usage_time_unit.require'   => 'product_on_demand_min_usage_time_unit_require',
        'min_usage_time_unit.in'        => 'product_on_demand_min_usage_time_unit_format_error',
        'upgrade_min_billing_time.require'        => 'product_on_demand_upgrade_min_billing_time_require',
        'upgrade_min_billing_time.integer'        => 'product_on_demand_upgrade_min_billing_time_format_error',
        'upgrade_min_billing_time.between'        => 'product_on_demand_upgrade_min_billing_time_format_error',
        'upgrade_min_billing_time_unit.require'   => 'product_on_demand_upgrade_min_billing_time_unit_require',
        'upgrade_min_billing_time_unit.in'        => 'product_on_demand_upgrade_min_billing_time_unit_format_error',
        'grace_time.require'            => 'product_on_demand_grace_time_require',
        'grace_time.integer'            => 'product_on_demand_grace_time_format_error',
        'grace_time.between'            => 'product_on_demand_grace_time_format_error',
        'grace_time_unit.require'       => 'product_on_demand_grace_time_unit_require',
        'grace_time_unit.in'            => 'product_on_demand_grace_time_unit_error',
        'keep_time.require'             => 'product_on_demand_keep_time_require',
        'keep_time.integer'             => 'product_on_demand_keep_time_format_error',
        'keep_time.between'             => 'product_on_demand_keep_time_format_error',
        'keep_time_unit.require'        => 'product_on_demand_keep_time_unit_require',
        'keep_time_unit.in'             => 'product_on_demand_keep_time_unit_error',
        'keep_time_billing_item.array'  => 'param_error',
        'initial_fee.float'             => 'product_on_demand_initial_fee_format_error',
        'initial_fee.between'           => 'product_on_demand_initial_fee_format_error',
        'client_auto_delete.require'    => 'product_on_demand_client_auto_delete_error',
        'client_auto_delete.in'         => 'product_on_demand_client_auto_delete_error',
        'on_demand_to_duration.require' => 'product_on_demand_on_demand_to_duration_error',
        'on_demand_to_duration.in'      => 'product_on_demand_on_demand_to_duration_error',
        'duration_to_on_demand.require' => 'product_on_demand_duration_to_on_demand_error',
        'duration_to_on_demand.in'      => 'product_on_demand_duration_to_on_demand_error',
        'credit_limit_pay.require'      => 'product_on_demand_credit_limit_pay_error',
        'credit_limit_pay.in'           => 'product_on_demand_credit_limit_pay_error',
    ];

    protected $scene = [
        'update' => ['id','billing_cycle_unit','billing_cycle_day','billing_cycle_point','duration_id','duration_ratio','min_credit','min_usage_time','min_usage_time_unit','upgrade_min_billing_time','upgrade_min_billing_time_unit','grace_time','grace_time_unit','keep_time','keep_time_unit','keep_time_billing_item','initial_fee','client_auto_delete','on_demand_to_duration','duration_to_on_demand','credit_limit_pay'],
    ];

    public function durationRatioRequire($value, $data)
    {
        if(!empty($data['duration_id'])){
            return true;
        }
    }

    // 验证出账周期
    public function checkBillingCyclePoint($value, $rule, $data)
    {
        if(in_array($data['billing_cycle_unit'], ['day','month'])){
            if(!empty($value)){
                if(!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value)){
                    return 'product_on_demand_billing_cycle_error';
                }
            }
        }
        return true;
    }

}
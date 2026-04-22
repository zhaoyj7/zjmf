<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 产品管理验证
 */
class HostValidate extends Validate
{
	protected $rule = [
		'id' 						    => 'require|integer|gt:0',
		'product_id' 				    => 'require|integer|gt:0',
		'server_id' 				    => 'integer|egt:0',
        'name' 						    => 'max:100',
        'first_payment_amount' 		    => 'require|float|egt:0',
        'renew_amount' 				    => 'require|float|egt:0',
        'billing_cycle' 			    => 'require|in:free,onetime,recurring_prepayment,recurring_postpaid,on_demand',
        'status' 					    => 'require|in:Unpaid,Pending,Active,Suspended,Cancelled,Deleted,Failed,Grace,Keep',
        'active_time'       		    => 'date',
        'due_time'       			    => 'date',
        'suspend_type'                  => 'require|in:overdue,overtraffic,certification_not_complete,other',
        'suspend_reason'                => 'length:0,1000',
        'base_price'                    => 'float|egt:0',
        'batch_sync_host_status'        => 'require|array|checkBatchSyncHostStatus:thinkphp',
        'batch_sync_product_id'         => 'require|array',
        'keep_time_price'               => 'float|between:0,99999999',
        'on_demand_flow_price'          => 'float|between:0,99999999',
        'on_demand_billing_cycle_unit'  => 'require|in:hour,day,month',
        'on_demand_billing_cycle_day'   => 'requireIf:billing_cycle_unit,month|integer|between:1,31',
        'on_demand_billing_cycle_point' => 'checkBillingCyclePoint:thinkphp',
        'auto_unsuspend_time'           => 'integer|egt:0',
    ];

    protected $message  =   [
    	'id.require'     				        => 'id_error',
    	'id.integer'     				        => 'id_error',
        'id.gt'                                 => 'id_error',
    	'product_id.require'     		        => 'please_select_product',
    	'product_id.integer'     		        => 'product_id_error',
        'product_id.gt'                         => 'product_id_error',
    	'server_id.integer'     		        => 'server_id_error',
        'server_id.egt'                         => 'server_id_error',
    	'name.max'     					        => 'host_name_cannot_exceed_100_chars',
        'first_payment_amount.require'          => 'please_enter_first_payment_amount',
        'first_payment_amount.float'            => 'first_payment_amount_formatted_incorrectly',
        'first_payment_amount.egt'              => 'first_payment_amount_formatted_incorrectly',
        'renew_amount.require'                  => 'please_enter_renew_amount',
        'renew_amount.float'                    => 'renew_amount_formatted_incorrectly',    
        'renew_amount.egt'                      => 'renew_amount_formatted_incorrectly', 
        'billing_cycle.require'                 => 'please_select_billing_cycle',
        'billing_cycle.in'                      => 'billing_cycle_error',
        'status.require'                        => 'please_select_host_status', 
        'status.in'                             => 'host_status_error', 
        'active_time.date'                      => 'active_time_formatted_incorrectly', 
        'due_time.date'                         => 'due_time_formatted_incorrectly',
        'suspend_type.require'                  => 'please_select_suspend_type',
        'suspend_type.in'                       => 'please_select_suspend_type',
        'suspend_reason.length'                 => 'suspend_reason_length_cannot_exceed_1000_words',
        'base_price.float'                      => 'base_price_float',
        'base_price.egt'                        => 'base_price_egt',
        'batch_sync_host_status.require'        => 'param_error',
        'batch_sync_host_status.array'          => 'param_error',
        'batch_sync_product_id.require'         => 'please_select_product',
        'batch_sync_product_id.array'           => 'please_select_product',
        'keep_time_price.float'                 => 'keep_time_price_error',
        'keep_time_price.between'               => 'keep_time_price_error',
        'on_demand_flow_price.float'            => 'on_demand_flow_price_error',
        'on_demand_flow_price.between'          => 'on_demand_flow_price_error',
        'on_demand_billing_cycle_unit.require'  => 'param_error',
        'on_demand_billing_cycle_unit.in'       => 'param_error',
        'on_demand_billing_cycle_day.requireIf' => 'param_error',
        'on_demand_billing_cycle_day.integer'   => 'param_error',
        'on_demand_billing_cycle_day.between'   => 'param_error',
        'auto_unsuspend_time.integer'           => 'param_error',
        'auto_unsuspend_time.egt'               => 'param_error',
    ];

    protected $scene = [
        'update'    => ['id', 'product_id', 'server_id', 'name', 'first_payment_amount', 'renew_amount', 'billing_cycle', 'status', 'active_time', 'due_time', 'base_price','keep_time_price','on_demand_flow_price','on_demand_billing_cycle_unit','on_demand_billing_cycle_day','on_demand_billing_cycle_point'],
        'suspend'   => ['id', 'suspend_type', 'suspend_reason','auto_unsuspend_time'],
        'batch_sync'=> ['batch_sync_product_id','batch_sync_host_status'],
    ];

    /**
     * @时间 2025-01-23
     * @title 验证批量同步产品状态
     * @desc  验证批量同步产品状态
     * @author hh
     * @version v1
     * @param   array value - 产品状态(Active已开通Suspended已暂停)
     * @return  bool|string
     */
    public function checkBatchSyncHostStatus($value)
    {
        $allow = ['Active','Suspended'];
        $other = array_diff($value, $allow);
        if(!empty($other)){
            return 'param_error';
        }
        return true;
    }

    // 验证出账周期
    public function checkBillingCyclePoint($value, $rule, $data)
    {
        if(in_array($data['on_demand_billing_cycle_unit'], ['day','month'])){
            if(!empty($value)){
                if(!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value)){
                    return 'product_on_demand_billing_cycle_error';
                }
            }
        }
        return true;
    }

}
<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 全局防护验证
 * @use  server\mf_dcim\validate\GlobalDefenceValidate
 */
class GlobalDefenceValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'value'             => 'require',
        'price'             => 'checkPrice:thinkphp',
        'firewall_type'     => 'require',
        'defence_rule_id'   => 'require|array',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_please_input_peak_defence',
        //'value.integer'                 => 'mf_dcim_defence_format_error_for_update',
        //'value.between'                 => 'mf_dcim_defence_format_error_for_update',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
        'firewall_type.require'         => 'mf_dcim_firewall_type_require',
        'defence_rule_id.require'       => 'mf_dcim_defence_rule_id_require',
        'defence_rule_id.array'         => 'mf_dcim_defence_rule_id_error',
    ];

    protected $scene = [
        'import'        => ['product_id','firewall_type','defence_rule_id'],
        'update'        => ['id','value','price'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>9999999){
                return 'mf_dcim_price_must_between_0_999999';
            }
        }
        return true;
    }


}
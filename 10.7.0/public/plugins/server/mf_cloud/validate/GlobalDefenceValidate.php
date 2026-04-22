<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 全局防护验证
 * @use  server\mf_cloud\validate\GlobalDefenceValidate
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
        'value.require'                 => 'mf_cloud_please_input_peak_defence',
        'price.checkPrice'              => 'mf_cloud_price_cannot_lt_zero',
        'firewall_type.require'         => 'mf_cloud_firewall_type_require',
        'defence_rule_id.require'       => 'mf_cloud_defence_rule_id_require',
        'defence_rule_id.array'         => 'mf_cloud_defence_rule_id_error',
    ];

    protected $scene = [
        'import'        => ['product_id','firewall_type','defence_rule_id'],
        'update'        => ['id','price'],
        'line_create'   => ['value','price'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>9999999){
                return 'mf_cloud_price_must_between_0_999999';
            }
        }
        return true;
    }


}
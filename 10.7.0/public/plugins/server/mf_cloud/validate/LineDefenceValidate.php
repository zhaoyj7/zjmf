<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路防护验证
 * @use  server\mf_cloud\validate\LineDefenceValidate
 */
class LineDefenceValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'value'             => 'require|integer|between:0,999999',
        'price'             => 'checkPrice:thinkphp',
        'firewall_type'     => 'require',
        'defence_rule_id'   => 'require|array',
        'on_demand_price'   => 'float|between:0,9999999',
    ];

    protected $message = [
        'id.require'                   => 'id_error',
        'id.integer'                   => 'id_error',
        'value.require'                => 'please_input_peak_defence',
        'value.integer'                => 'mf_cloud_update_defence_format_error',
        'value.between'                => 'mf_cloud_update_defence_format_error',
        'price.checkPrice'             => 'price_cannot_lt_zero',

        'firewall_type.require'         => 'mf_cloud_firewall_type_require',
        'defence_rule_id.require'       => 'mf_cloud_defence_rule_id_require',
        'defence_rule_id.array'         => 'mf_cloud_defence_rule_id_error',
        'defence_rule_id.integer'       => 'mf_cloud_defence_rule_id_error',
        'on_demand_price.float'         => 'price_must_between_0_999999',
        'on_demand_price.between'       => 'price_must_between_0_999999',
    ];

    protected $scene = [
        'import'            => ['id','firewall_type','defence_rule_id'],
        'create'            => ['id','value','price','on_demand_price'],
        'update'            => ['id','value','price','on_demand_price'],
        'firewall_update'   => ['price'],
        'line_create'       => ['value','price','on_demand_price'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>9999999){
                return 'price_must_between_0_999999';
            }
        }
        return true;
    }

    /**
     * @时间 2025-01-14
     * @title 创建线路防火墙验证
     * @desc  创建线路防火墙验证
     * @author hh
     * @version v1
     */
    public function sceneLineCreateFirewall()
    {
        return $this->only(['price','firewall_type','defence_rule_id'])
                    ->remove('defence_rule_id', 'array')
                    ->append('defence_rule_id', 'integer');
    }


}
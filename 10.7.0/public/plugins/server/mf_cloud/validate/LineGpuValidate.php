<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路GPU验证
 * @use  server\mf_cloud\validate\LineGpuValidate
 */
class LineGpuValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'value'             => 'require|integer|between:1,100',
        'price'             => 'checkPrice:thinkphp',
        'on_demand_price'   => 'float|between:0,9999999',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'value.require'                 => 'mf_cloud_line_gpu_num_require',
        'value.integer'                 => 'mf_cloud_line_gpu_num_format_error',
        'value.between'                 => 'mf_cloud_line_gpu_num_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
        'on_demand_price.float'         => 'price_must_between_0_999999',
        'on_demand_price.between'       => 'price_must_between_0_999999',
    ];

    protected $scene = [
        'create'        => ['id','value','price','on_demand_price'],
        'update'        => ['id','value','price','on_demand_price'],
        'line_create'   => ['value','price'],
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


}
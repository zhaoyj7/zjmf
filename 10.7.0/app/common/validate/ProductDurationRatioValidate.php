<?php
namespace app\common\validate;

use think\Validate;

/**
 * @title 商品周期比例验证类
 * @description 接口说明:商品周期比例验证类
 */
class ProductDurationRatioValidate extends Validate
{
    protected $rule = [
        'product_id'        => 'require|integer',
        'ratio'             => 'require|checkRatio:thinkphp',
        'price'             => 'requireWithout:on_demand_price|checkPrice:thinkphp',
        'on_demand_price'   => 'requireWithout:price|float|between:0,99999999',
    ];

    protected $message = [
        'product_id.require'        => 'product_id_error',
        'product_id.integer'        => 'product_id_error',
        'ratio.require'             => 'validate_product_duration_ratio_ratio_require',
        'price.requireWithout'      => 'validate_product_duration_ratio_price_require',
        'on_demand_price.requireWithout' => 'validate_product_duration_ratio_price_require',
        'on_demand_price.float'     => 'validate_product_duration_ratio_price_format_error',
        'on_demand_price.between'   => 'validate_product_duration_ratio_price_format_error',
    ];

    protected $scene = [
        'save'          => ['product_id','ratio'],
        'fill'          => ['product_id','price','on_demand_price'],
    ];

    protected function checkRatio($value){
        if(!is_array($value)){
            return 'param_error';
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v <= 0 || $v >= 10000){
                return 'validate_product_duration_ratio_ratio_format_error';
            }
        }
        return true;
    }

    protected function checkPrice($value){
        if(!is_array($value)){
            return 'param_error';
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v < 0 || $v > 99999999){
                return 'validate_product_duration_ratio_price_format_error';
            }
        }
        return true;
    }

}
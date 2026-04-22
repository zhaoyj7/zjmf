<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 商品周期预设验证器
 */
class ProductDurationPresetsValidate extends Validate
{
	protected $rule = [
        'name' 		        => 'require|min:1|max:25',
        'num' 		        => 'require|integer|min:1',
        'unit'              => 'require|in:hour,day,month',
        'ratio'             => 'require|float|egt:0',
    ];

    protected $message  =   [
        'name.require'     			=> 'product_duration_presets_name_require',
        'name.min'                  => 'product_duration_presets_name_min',
        'name.max'                  => 'product_duration_presets_name_max',
        'num.require'     			=> 'product_duration_presets_num_require',
        'num.integer'     			=> 'product_duration_presets_num_integer',
        'num.min'                   => 'product_duration_presets_num_min',
        'unit.require'     			=> 'product_duration_presets_unit_require',
        'unit.in'     				=> 'product_duration_presets_unit_in',
        'ratio.require'     		=> 'product_duration_presets_ratio_require',
        'ratio.float'     			=> 'product_duration_presets_ratio_float',
        'ratio.egt'     			=> 'product_duration_presets_ratio_egt',
    ];

    protected $scene = [
        'ratio_open' => ['name', 'num', 'unit','ratio'],
        'ratio_close' => ['name', 'num', 'unit'],
        'update' => ['name', 'num', 'unit','ratio'],
    ];
}
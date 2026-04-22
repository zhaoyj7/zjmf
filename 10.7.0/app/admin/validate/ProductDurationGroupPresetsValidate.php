<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 商品周期预设验证器
 */
class ProductDurationGroupPresetsValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer|checkId:thinkphp',
        'name' 		        => 'require|min:1|max:100',
        'ratio_open' 		=> 'require|in:0,1',
        'durations'         => 'array|checkDurations:thinkphp',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'product_duration_group_presets_name_require',
        'name.min'                  => 'product_duration_group_presets_name_min',
        'name.max'                  => 'product_duration_group_presets_name_max',
        'ratio_open.require'     	=> 'product_duration_group_presets_ratio_open_require',
        'ratio_open.in'     		=> 'product_duration_group_presets_ratio_open_in',
        'durations.array'     		=> 'product_duration_group_presets_durations_array',
    ];

    protected $scene = [
        'create' => ['name', 'ratio_open', 'durations'],
        'update' => ['id', 'name', 'ratio_open', 'durations'],
        'copy' => ['id'],
        'index' => ['id'],
        'delete' => ['id'],
    ];

    protected function checkDurations($value, $rule, $data)
    {
        $ProductDurationPresetsValidate = new ProductDurationPresetsValidate();
        // 验证周期比例
        if ($data['ratio_open']){
            foreach ($data['durations'] as $duration){
                if (!$ProductDurationPresetsValidate->scene('ratio_open')->check($duration)){
                    return $ProductDurationPresetsValidate->getError();
                }
            }
        }else{ // 关闭
            foreach ($data['durations'] as $duration){
                if (!$ProductDurationPresetsValidate->scene('ratio_close')->check($duration)){
                    return $ProductDurationPresetsValidate->getError();
                }
            }
        }
        return true;
    }

    protected function checkId($value, $rule, $data)
    {
        return \app\admin\model\ProductDurationGroupPresetsModel::where('id', $value)->find() ? true : 'id_error';
    }
}
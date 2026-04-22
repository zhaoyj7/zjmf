<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 魔方云数据中心组验证
 * @use   app\admin\validate\MfCloudDataCenterMapGroupValidate
 */
class MfCloudDataCenterMapGroupValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer|gt:0',
        'name'              => 'require|length:1,100',
        'description'       => 'length:0,1000',
        'data_center'       => 'require|array|checkDataCenter:thinkphp',
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'name.require'              => 'mf_cloud_data_center_map_group_name_require',
        'name.length'               => 'mf_cloud_data_center_map_group_name_length_error',
        'description.length'        => 'mf_cloud_data_center_map_group_description_length_error',
        'data_center.require'       => 'mf_cloud_data_center_map_group_data_center_require',
        'data_center.array'         => 'mf_cloud_data_center_map_group_data_center_require',
    ];

    protected $scene = [
        'create' => ['name', 'description', 'data_center'],
        'update' => ['id', 'name', 'description', 'data_center'],
        'delete' => ['id'],
    ];

    /**
     * 验证数据中心配置格式
     */
    protected function checkDataCenter($value, $rule, $data)
    {
        if (!is_array($value) || empty($value)) {
            return 'mf_cloud_data_center_map_group_data_center_require';
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return 'param_error';
            }
            
            if (!isset($item['product_id']) || !is_numeric($item['product_id']) || $item['product_id'] <= 0) {
                return 'mf_cloud_data_center_map_group_data_center_require';
            }
            
            if (!isset($item['data_center_id']) || !is_array($item['data_center_id']) || empty($item['data_center_id'])) {
                return 'mf_cloud_data_center_map_group_data_center_require';
            }
            
            foreach ($item['data_center_id'] as $dcId) {
                if (!is_numeric($dcId) || $dcId <= 0) {
                    return 'mf_cloud_data_center_map_group_data_center_require';
                }
            }
        }
        
        return true;
    }
}

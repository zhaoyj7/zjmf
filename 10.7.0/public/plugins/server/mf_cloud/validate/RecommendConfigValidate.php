<?php
namespace server\mf_cloud\validate;

use app\common\model\ProductModel;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\RecommendConfigModel;
use think\Validate;
use server\mf_cloud\model\OptionModel;

/**
 * @title 套餐验证
 * @use   server\mf_cloud\validate\RecommendConfigValidate
 */
class RecommendConfigValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'name'              => 'require|length:1,50',
        'description'       => 'length:0,65535',
        'order'             => 'integer|between:0,999',
        'data_center_id'    => 'require|integer',
        'line_id'           => 'require|integer',
        'cpu'               => 'require|integer|gt:0',
        'memory'            => 'require|integer|between:1,512',
        'system_disk_size'  => 'require|integer|gt:0',
        'system_disk_type'  => 'length:0,50',
        'data_disk_size'    => 'integer|egt:0',
        'data_disk_type'    => 'length:0,50',
        'bw'                => 'require|integer|between:1,30000',
        'flow'              => 'integer|between:0,999999',
        'peak_defence'      => 'integer|between:0,999999',
        'ip_num'            => 'require|integer|between:0,2000',
        'price'             => 'checkPrice:thinkphp',
        'hidden'            => 'require|in:0,1',
        'gpu_num'           => 'integer|between:0,100',
        'ipv6_num'          => 'require|integer|between:0,1000',
        'upgrade_show'      => 'require|in:0,1',
        'trial_status'      => 'require|in:0,1',
        'in_bw'             => 'require|integer|between:1,30000',
        'traffic_type'      => 'integer|in:1,2,3',
        'due_not_free_gpu'  => 'in:0,1',
        'ipv4_num_upgrade'  => 'in:0,1',
        'ipv6_num_upgrade'  => 'in:0,1',
        'flow_upgrade'      => 'in:0,1',
        'bw_upgrade'        => 'in:0,1',
        'defence_upgrade'   => 'in:0,1',
        'ontrial'           => 'in:0,1',
        'ontrial_price'     => 'egt:0',
        'ontrial_stock_control'     => 'in:0,1',
        'ontrial_qty'       => 'egt:0|checkOntrialQty:thinkphp',
        'on_demand_price'   => 'float|between:0,9999999',
        'on_demand_flow_price'   => 'float|between:0,9999999',
    ];

    protected $message  =   [
    	'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'name.require'                          => 'please_input_recommend_config_name',
        'name.length'                           => 'recommend_config_name_length_error',
        'description.length'                    => 'recommend_config_description_length_error',
        'order.integer'                         => 'order_id_format_error',
        'order.between'                         => 'order_id_format_error',
        'data_center_id.require'                => 'please_select_data_center',
        'data_center_id.integer'                => 'please_select_data_center',
        'line_id.require'                       => 'please_select_line',
        'line_id.integer'                       => 'please_select_line',
        'cpu.require'                           => 'please_input_recommend_config_cpu',
        'cpu.integer'                           => 'recommend_config_cpu_foramt_error',
        'cpu.gt'                                => 'recommend_config_cpu_foramt_error',
        'memory.require'                        => 'please_input_recommend_config_memory',
        'memory.integer'                        => 'memory_value_format_error',
        'memory.between'                        => 'memory_value_format_error',
        'system_disk_size.require'              => 'please_input_recommend_config_system_disk_size',
        'system_disk_size.integer'              => 'recommend_config_system_disk_size_format_error',
        'system_disk_size.gt'                   => 'recommend_config_system_disk_size_format_error',
        'system_disk_type.length'               => 'disk_type_format_error',
        'data_disk_size.integer'                => 'recommend_config_data_disk_size_format_error',
        'data_disk_size.gt'                     => 'recommend_config_data_disk_size_format_error',
        'data_disk_type.length'                 => 'disk_type_format_error',
        'bw.require'                            => 'please_input_bw',
        'bw.integer'                            => 'line_bw_format_error',
        'bw.between'                            => 'line_bw_format_error',
        'flow.integer'                          => 'line_flow_format_error',
        'flow.between'                          => 'line_flow_format_error',
        'peak_defence.integer'                  => 'recommend_config_peak_defence_format_error',
        'peak_defence.between'                  => 'recommend_config_peak_defence_format_error',
        'ip_num.require'                        => 'please_input_line_ip_num',
        'ip_num.integer'                        => 'mf_cloud_recommend_config_ip_num_format_error',
        'ip_num.between'                        => 'mf_cloud_recommend_config_ip_num_format_error',
        'price.checkPrice'                      => 'price_cannot_lt_zero',
        'gpu_num.integer'                       => 'mf_cloud_recommend_config_gpu_num_format_error',
        'gpu_num.between'                       => 'mf_cloud_recommend_config_gpu_num_format_error',
        'ipv6_num.require'                      => 'mf_cloud_ipv6_num_require',
        'ipv6_num.integer'                      => 'mf_cloud_ipv6_num_format_error',
        'ipv6_num.between'                      => 'mf_cloud_ipv6_num_format_error',
        'in_bw.require'                         => 'mf_cloud_in_bw_require',
        'in_bw.integer'                         => 'mf_cloud_in_bw_format_error',
        'in_bw.between'                         => 'mf_cloud_in_bw_format_error',
        'traffic_type.integer'                  => 'please_select_flow_traffic_type',
        'traffic_type.in'                       => 'please_select_flow_traffic_type',
        'due_not_free_gpu.require'              => 'param_error',
        'due_not_free_gpu.in'                   => 'param_error',
        'ipv4_num_upgrade.in'                   => 'param_error',
        'ipv6_num_upgrade.in'                   => 'param_error',
        'flow_upgrade.in'                       => 'param_error',
        'bw_upgrade.in'                         => 'param_error',
        'defence_upgrade.in'                    => 'param_error',
        'ontrial.in'                            => 'mf_cloud_recommend_config_ontrial_in',
        'ontrial_price.egt'                     => 'mf_cloud_recommend_config_ontrial_price_egt',
        'ontrial_stock_control.in'              => 'mf_cloud_recommend_config_ontrial_stock_control_in',
        'ontrial_qty.egt'                       => 'mf_cloud_recommend_config_ontrial_qty_egt',
        'on_demand_price.float'                 => 'price_must_between_0_999999',
        'on_demand_price.between'               => 'price_must_between_0_999999',
        'on_demand_flow_price.float'            => 'price_must_between_0_999999',
        'on_demand_flow_price.between'          => 'price_must_between_0_999999',
    ];

    protected $scene = [
        'create'  => ['name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','bw','flow','peak_defence','ip_num','price','gpu_num','ipv6_num','in_bw','traffic_type','due_not_free_gpu','ontrial','ontrial_price','ontrial_stock_control','ontrial_qty','on_demand_price','on_demand_flow_price'],
        'update'  => ['id','name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','bw','flow','peak_defence','ip_num','price','gpu_num','ipv6_num','in_bw','traffic_type','due_not_free_gpu','ontrial','ontrial_price','ontrial_stock_control','ontrial_qty','on_demand_price','on_demand_flow_price'],
        'update_hidden' => ['id','hidden'],
        'update_upgrade_show' => ['id','upgrade_show'],
        'update_ontrial' => ['id','ontrial'],
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

    protected function checkOntrialQty($value,$rule,$data)
    {
        // 开启套餐库存控制
        if (!empty($data['ontrial_stock_control'])){
            $DataCenterModel = DataCenterModel::find($data['data_center_id']);
            $productId = $DataCenterModel['product_id']??0;
            $product = ProductModel::find($productId);
            if (!empty($product) && $product['stock_control']){
                $RecommendConfigModel = new RecommendConfigModel();
                $ontrialQty = $RecommendConfigModel->where('product_id',$productId)
                    ->where('id','<>',$data['id']??0) // 兼容编辑
                    //->where('ontrial',1)
                    ->where('ontrial_stock_control',1)
                    ->sum('ontrial_qty');
                if (($ontrialQty+$value)>$product['qty']){
                    return 'mf_cloud_recommend_config_ontrial_qty_egt_product_qty';
                }
            }
        }

        return true;
    }

}
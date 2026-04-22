<?php
namespace app\admin\validate;

use think\Validate;
use app\common\model\ProductModel;
use app\common\model\NoticeSettingModel;

/**
 * 商品验证
 */
class ProductValidate extends Validate
{
	protected $rule = [
		'id'                                => 'require|integer',
        'name'                              => 'require|max:100',
        'hidden'                            => 'in:0,1',
        'stock_control'                     => 'in:0,1',
        'sync_stock'                        => 'in:0,1',
        'qty' 		                        => 'number',
        'description'                       => 'max:10000',
        'pre_product_id'                    => 'require|integer',
        'product_group_id'                  => 'require|integer',
        'pay_type'                          => 'require|in:free,onetime,recurring_prepayment,recurring_postpaid,on_demand,recurring_prepayment_on_demand',
        'auto_setup'                        => 'require|in:0,1',
        'type'                              => 'require|in:server,server_group',
        'rel_id'                            => 'require|integer',
        'product_id'                        => 'integer|egt:0',
        'show'                              => 'require|in:0,1',
        'price'                             => 'float|between:0,99999999',
        'custom_host_name'                  => 'require|in:0,1',
        'custom_host_name_prefix'           => 'requireIf:custom_host_name,1|regex:/^[a-zA-Z][a-zA-Z0-9_\-\.]{1,10}$/|length:1,10',
        'custom_host_name_string_allow'     => 'requireIf:custom_host_name,1|array|checkAllow:thinkphp',
        'custom_host_name_string_length'    => 'requireIf:custom_host_name,1|integer|between:5,50',
        'notice_setting'                    => 'require|checkNoticeSetting:thinkphp',
        'renew_rule'                        => 'in:due,current',
        'show_base_info'                    => 'in:0,1',
        'pay_ontrial'                       => 'checkPayOntrial:thinkphp',
        'auto_renew_in_advance'             => 'require|in:0,1',
        'auto_renew_in_advance_num' 	    => 'require|integer|gt:0',
        'auto_renew_in_advance_unit'	    => 'require|in:minute,hour,day',
        'natural_month_prepaid'             => 'require|in:0,1',
    ];

    protected $message  =   [
    	'id.require'     			                => 'id_error',
    	'id.integer'     			                => 'id_error',
        'name.require'     			                => 'please_enter_product_name',
        'name.max'     			                    => 'product_name_cannot_exceed_100_chars',
        'hidden.in'     			                => 'product_hidden',
        'stock_control.in'     		                => 'product_stock_control',
        'qty.number'     		                    => 'product_qty_num',
        'description.max'     		                => 'product_description_max',
        'pre_product_id.require'                    => 'pre_product_id_require',
        'pre_product_id.integer'                    => 'pre_product_id_integer',
        'product_group_id.require'                  => 'product_group_id_require',
        'product_group_id.integer'                  => 'product_group_id_integer',
        'pay_type.require'                          => 'product_pay_type_require',
        'pay_type.in'                               => 'product_pay_type_in',
        'auto_setup.require'                        => 'product_auto_setup_require',
        'auto_setup.in'                             => 'product_auto_setup_in',
        'type.require'                              => 'product_type_require',
        'type.in'                                   => 'product_type_in',
        'rel_id.require'                            => 'product_rel_id_require',
        'rel_id.integer'                            => 'product_rel_id_integer',
        'product_id.integer'                        => 'parent_product_id_integer',
        'product_id.egt'                            => 'parent_product_id_integer',
        'show.require'                              => 'product_show_require',
        'show.in'                                   => 'product_show_in',
        'price.float'                               => 'product_price_format_error',
        'price.between'                             => 'product_price_format_error',
        'custom_host_name.require'                  => 'param_error',
        'custom_host_name.in'                       => 'param_error',
        'custom_host_name_prefix.requireIf'         => 'custom_host_name_prefix_require',
        'custom_host_name_prefix.regex'             => 'custom_host_name_prefix_error',
        'custom_host_name_prefix.length'            => 'custom_host_name_prefix_error',
        'custom_host_name_string_allow.requireIf'   => 'custom_host_name_string_allow_require',
        'custom_host_name_string_allow.array'       => 'param_error', 
        'custom_host_name_string_length.requireIf'  => 'custom_host_name_string_length_require',
        'custom_host_name_string_length.integer'    => 'custom_host_name_string_length_error',
        'custom_host_name_string_length.between'    => 'custom_host_name_string_length_error',
        'auto_renew_in_advance.require'             => 'param_error',
        'auto_renew_in_advance.in'                  => 'param_error',
        'auto_renew_in_advance_num.require'         => 'configuration_auto_renew_in_advance_num_require',
        'auto_renew_in_advance_num.integer'         => 'configuration_auto_renew_in_advance_num_error',
        'auto_renew_in_advance_num.gt'              => 'configuration_auto_renew_in_advance_num_error',
        'auto_renew_in_advance_unit.require'        => 'param_error',
        'auto_renew_in_advance_unit.in'             => 'param_error',
        'natural_month_prepaid.require'             => 'param_error',
        'natural_month_prepaid.in'                  => 'param_error',
    ];

    protected $scene = [
        'create' => ['name','renew_rule'],
        'edit' => ['id','name','hidden','stock_control','qty','description','pay_type','product_id','price','renew_rule','auto_renew_in_advance','auto_renew_in_advance_num','auto_renew_in_advance_unit'],
        'edit_server' => ['id','auto_setup','type','rel_id','show','show_base_info'],
        'order' => ['pre_product_id','product_group_id'],
        'module_server_config_option' => ['id', 'type', 'rel_id'],
        'custom_host_name' => ['id', 'custom_host_name', 'custom_host_name_prefix', 'custom_host_name_string_allow', 'custom_host_name_string_length'],
        'notice_setting' => ['notice_setting'],
        'pay_ontrial' => ['id','pay_ontrial'],
        'natural_month_prepaid' => ['id','natural_month_prepaid'],
    ];

    // 验证选中字段
    public function checkAllow($value, $type, $data)
    {
        if(!is_array($value)){
            return 'param_error';
        }

        if(empty($value)){
            return 'param_error';
        }

        if(count(array_unique($value))!=count($value)){
            return 'param_error';
        }

        foreach ($value as $v) {
            if(!in_array($v, ['number', 'upper', 'lower'])){
                return 'param_error';
            }
        }

        return true;
    }

    // 验证选中字段
    public function checkNoticeSetting($value, $type, $data)
    {
        if(!is_array($value)){
            return 'param_error';
        }

        if(empty($value)){
            return 'param_error';
        }        

        $settingList = config('idcsmart.product_notice_group_action');

        foreach ($value as $v) {
            if(!is_array($v)){
                return 'param_error';
            }
            if(empty($v)){
                return 'param_error';
            }
            if(count(array_intersect(array_keys($v), $settingList))!=count($settingList) || !in_array('id', array_keys($v)) || count(array_diff(array_keys($v), $settingList))!=1){
                return 'param_error';
            }
            foreach ($v as $kk => $vv) {
                if($kk=='id'){
                    if(!is_integer($vv) || $vv<=0){
                        return 'id_error';
                    }
                }else{
                    if(!in_array($vv, [0, 1])){
                        return 'param_error';
                    }
                }
            }
        }

        return true;
    }

    protected function checkPayOntrial($value, $type, $data)
    {
        $ProductPayOntrialValidate = new ProductPayOntrialValidate();
        if(!$ProductPayOntrialValidate->scene('pay_ontrial')->check($value)){
            return $ProductPayOntrialValidate->getError();
        }else{
            return true;
        }
    }
}
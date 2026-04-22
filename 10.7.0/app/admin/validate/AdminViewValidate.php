<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\AdminViewModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\CountryModel;
use app\common\model\SelfDefinedFieldModel;
use addon\client_custom_field\model\ClientCustomFieldModel;
use addon\idcsmart_client_level\model\IdcsmartClientLevelModel;

/**
 * @title 管理员视图验证
 * @use   app\admin\validate\AdminViewValidate
 */
class AdminViewValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer|gt:0',
		'view'              => 'require|in:client,order,host,transaction',
        'name'              => 'require|max:20|unique:admin_view,view^name^admin_id',
        'select_field'      => 'require|array|checkSelectField:thinkphp',
        'data_range_switch' => 'require|in:0,1',
        'select_data_range' => 'array|checkSelectDataRange:thinkphp',
        'choose'            => 'require|integer|egt:0',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'id.gt'                         => 'id_error',
    	'view.require'                 => 'admin_view_require',
        'view.in'                       => 'admin_view_error',
        'name.require'                  => 'admin_view_name_require',
        'name.max'                      => 'admin_view_name_error',
        'name.unique'                   => 'admin_view_name_unique',
        'select_field.require'          => 'admin_view_select_field_require',
        'select_field.array'            => 'admin_view_select_field_require',
        'data_range_switch.require'     => 'param_error',
        'data_range_switch.in'          => 'param_error',
        'select_data_range.array'       => 'admin_view_select_data_range_require',
        'choose.require'                => 'admin_view_choose_require',
        'choose.integer'                => 'admin_view_choose_error',
        'choose.egt'                    => 'admin_view_choose_error',       
    ];

    protected $scene = [
        'view' => ['view'],
        'create' => ['view','name','select_field','data_range_switch','select_data_range'],
        'update' => ['id','view','name','select_field','data_range_switch','select_data_range'],
        'status' => ['id', 'status'],
        'select_field' => ['id', 'select_field'],
        'select_data_range' => ['id', 'data_range_switch', 'select_data_range'],
        'order' => ['view'],
        'choose' => ['view', 'choose'],
    ];

    // 验证选中字段
    public function checkSelectField($value, $type, $data)
    {
        // 当前id必须选中
        if(!in_array('id', $value)){
            return 'admin_field_validate_id_cannot_cancel';
        }

        $AdminViewModel = new AdminViewModel();
        if(isset($data['id']) && !isset($data['view'])){
            $adminView = $AdminViewModel->find($data['id']);
            if(empty($adminView)){
                return 'admin_view_is_not_exist';
            }
            $data['view'] = $adminView['view'];
        }
        $enableField = $AdminViewModel->enableField($data['view']);

        $filed = [];
        foreach($enableField['field'] as $v){
            $filed = array_merge($filed, $v['field']);
        }
        $filed = array_column($filed, 'key');

        foreach ($value as $v) {
            if(!in_array($v, $filed)){
                return 'admin_view_field_is_not_exist';
            }
        }

        return true;
    }
    
    public function checkSelectDataRange($value, $type, $data)
    {
        $AdminViewModel = new AdminViewModel();
        if(isset($data['id']) && !isset($data['view'])){
            $adminView = $AdminViewModel->find($data['id']);
            if(empty($adminView)){
                return 'admin_view_is_not_exist';
            }
            $data['view'] = $adminView['view'];
        }
        $enableField = $AdminViewModel->enableField($data['view'], true);

        $dataRange = [];
        foreach($enableField['data_range'] as $v){
            foreach ($v['field'] as $vv) {
                $dataRange[$vv['key']] = $vv;
            }
        }

        if(count($value)!=count(array_unique(array_column($value, 'key')))){
            return 'admin_view_data_range_key_error';
        }

        $lang = lang('admin_view_data_range_value_error');
        foreach ($value as $v) {
            if(!isset($dataRange[$v['key']])){
                continue;
                //return 'admin_view_data_range_is_not_exist';
            }
            if(!isset($v['rule']) || !in_array($v['rule'], $dataRange[$v['key']]['rule'])){
                return 'admin_view_data_range_rule_error';
            }
            if(!in_array($v['rule'], ['empty', 'not_empty'])){
                if(!isset($v['value'])){
                    return 'admin_view_data_range_value_require';
                }
                if(in_array($v['key'], ['id', 'client_id','order_id'])){
                    if(!is_integer($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    if(strlen((string)$v['value'])>10){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['product_name'])){
                    if(!is_array($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    $ProductModel = new ProductModel();
                    $count = $ProductModel->whereIn('id', $v['value'])->count();
                    if($count!=count($v['value'])){
                        return 'product_is_not_exist';
                    }
                }else if(in_array($v['key'], ['host_status', 'billing_cycle', 'certification', 'language', 'gateway', 'order_status', 'order_type'])){
                    if(!is_array($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    $option = $dataRange[$v['key']]['option'];
                    foreach ($v['value'] as $vv) {
                        if(!in_array($vv, array_column($option, 'id'))){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                    }
                }else if(in_array($v['key'], ['client_status'])){
                    if(!in_array($v['value'], [0, 1])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['ip'])){
                    if(!is_string($v['value']) || strlen($v['value'])>150){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['host_name', 'phone'])){
                    if(!is_string($v['value']) || strlen($v['value'])>20){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['renew_amount', 'first_payment_amount', 'base_price', 'order_amount', 'order_use_credit', 'order_refund_amount'])){
                    if((!is_float($v['value']) && !is_integer($v['value'])) || strlen((string)$v['value'])>10){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['due_time', 'active_time', 'reg_time', 'order_time'])){
                    if($v['rule']=='equal'){
                        if(strtotime($v['value'])===false){
                            return 'admin_view_data_range_value_date_error';
                        }
                    }else if($v['rule']=='interval'){
                        if(!isset($v['value']['start']) || strtotime($v['value']['start'])===false){
                            return 'admin_view_data_range_value_date_error';
                        }
                        if(!isset($v['value']['end']) || strtotime($v['value']['end'])===false){
                            return 'admin_view_data_range_value_date_error';
                        }
                        if(strtotime($v['value']['start'])>strtotime($v['value']['end'])){
                            return 'admin_view_data_range_value_start_end_error';
                        }
                    }else if($v['rule']=='dynamic'){
                        if(!isset($v['value']['condition1']) || !in_array($v['value']['condition1'], ['ago','now','later'])){
                            return 'admin_view_data_range_value_dynamic_condition_error';
                        }
                        if($v['value']['condition1']!='now'){
                            if(!isset($v['value']['day1']) || !is_numeric($v['value']['day1'])){
                                return 'admin_view_data_range_value_dynamic_day_error';
                            }
                        }
                        if(!isset($v['value']['condition2']) || !in_array($v['value']['condition2'], ['ago','now','later'])){
                            return 'admin_view_data_range_value_dynamic_condition_error';
                        }
                        if($v['value']['condition2']!='now'){
                            if(!isset($v['value']['day2']) || !is_numeric($v['value']['day2'])){
                                return 'admin_view_data_range_value_dynamic_day_error';
                            }
                        }
                    }
                }else if(in_array($v['key'], ['server_name'])){
                    if(!is_array($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    $ServerModel = new ServerModel();
                    $count = $ServerModel->whereIn('id', $v['value'])->count();
                    if($count!=count($v['value'])){
                        return 'server_is_not_exist';
                    }
                }else if(in_array($v['key'], ['admin_notes', 'billing_cycle_name', 'username', 'company', 'email', 'address', 'notes'])){
                    if(!is_string($v['value']) || strlen($v['value'])>30){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['client_status'])){
                    if(!in_array($v['value'], [0, 1])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['client_level'])){
                    if(!is_array($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    $IdcsmartClientLevelModel = new IdcsmartClientLevelModel();
                    $count = $IdcsmartClientLevelModel->whereIn('id', $v['value'])->count();
                    if($count!=count($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(in_array($v['key'], ['country'])){
                    if(!is_array($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    $CountryModel = new CountryModel();
                    $count = $CountryModel->whereIn('id', $v['value'])->count();
                    if($count!=count($v['value'])){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                }else if(strpos($v['key'], 'self_defined_field_')===0){
                    $id = intval(str_replace('self_defined_field_', '', $v['key']));
                    $SelfDefinedFieldModel = new SelfDefinedFieldModel();
                    $selfDefinedField = $SelfDefinedFieldModel->find($id);
                    if(empty($selfDefinedField)){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    if($dataRange[$v['key']]['type']=='multi_select'){
                        if(!is_array($v['value'])){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                        $option = $dataRange[$v['key']]['option'];
                        foreach ($v['value'] as $vv) {
                            if(!in_array($vv, array_column($option, 'id'))){
                                return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                            }
                        }
                    }else if($dataRange[$v['key']]['type']=='select'){
                        if(!in_array($v['value'], [0, 1])){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                    }else{
                        if(!is_string($v['value']) || strlen($v['value'])>30){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                    }
                }else if(strpos($v['key'], 'addon_client_custom_field_')===0){
                    $id = intval(str_replace('addon_client_custom_field_', '', $v['key']));
                    $ClientCustomFieldModel = new ClientCustomFieldModel();
                    $clientCustomField = $ClientCustomFieldModel->find($id);
                    if(empty($clientCustomField)){
                        return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                    }
                    if($dataRange[$v['key']]['type']=='multi_select'){
                        if(!is_array($v['value'])){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                        $option = $dataRange[$v['key']]['option'];
                        foreach ($v['value'] as $vv) {
                            if(!in_array($vv, array_column($option, 'id'))){
                                return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                            }
                        }
                    }else if($dataRange[$v['key']]['type']=='select'){
                        if(!in_array($v['value'], [0, 1])){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                    }else{
                        if(!is_string($v['value']) || strlen($v['value'])>30){
                            return str_replace('{name}', $dataRange[$v['key']]['name'], $lang);
                        }
                    }
                }
            }
        }

        return true;
    }


}
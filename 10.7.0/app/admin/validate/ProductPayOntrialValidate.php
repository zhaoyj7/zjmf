<?php
namespace app\admin\validate;

use app\common\model\ProductModel;
use think\Validate;

/**
 * 商品试用验证
 */
class ProductPayOntrialValidate extends Validate
{
	protected $rule = [
		'status'                            => 'in:0,1',
		'cycle_type'                        => 'require|in:hour,day,month',
        'cycle_num'                         => 'require|number|egt:0',
        'client_limit'                      => 'in:no,new,host',
        'account_limit'                     => 'checkAccountLimit:thinkphp',
        'old_client_exclusive'              => 'checkOldClientExclusive:thinkphp',
        'max'                               => 'egt:0',
    ];

    protected $message  =   [
    	'status.in'     			                 => 'product_pay_ontrial_status_in',
        'cycle_type.require'                         => 'product_pay_ontrial_cycle_type_require',
        'cycle_type.in'                              => 'product_pay_ontrial_cycle_type_in',
        'cycle_num.require'                          => 'product_pay_ontrial_cycle_num_require',
        'cycle_num.number'                           => 'product_pay_ontrial_cycle_num_number',
        'cycle_num.egt'                              => 'product_pay_ontrial_cycle_num_egt',
        'client_limit.in'                            => 'product_pay_ontrial_client_limit_in',
        'account_limit.in'                           => 'product_pay_ontrial_account_limit_in',
        'max.egt'                                    => 'product_pay_ontrial_max_egt',
    ];

    protected $scene = [
        'pay_ontrial' => ['status', 'cycle_type', 'cycle_num', 'client_limit', 'account_limit', 'max'],
    ];

    protected function checkAccountLimit($value, $rule, $data)
    {
        if (!is_array($value)){
            return 'param_error';
        }
        foreach ($value as $v) {
            if (!in_array($v, ['email', 'phone','certification'])){
                return 'product_pay_ontrial_account_limit_in';
            }
        }
        return true;
    }

    protected function checkOldClientExclusive($value, $rule, $data)
    {
        if (!is_array($value)){
            return 'param_error';
        }
        $ProductModel = new ProductModel();
        foreach ($value as $v) {
            $exist = $ProductModel->find($v);
            if (!$exist){
                return 'product_pay_ontrial_old_client_exclusive_in';
            }
        }
        return true;
    }

}
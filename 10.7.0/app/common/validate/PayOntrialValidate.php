<?php
namespace app\common\validate;

use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use think\Validate;

/**
 * @title 商品试用通用验证类
 * @description 接口说明:商品试用通用验证类
 */
class PayOntrialValidate extends Validate
{
    protected $rule = [
        'client_id'                        => 'require',
        'product_id'                       => 'require|checkProductId:thinkphp',
    ];

    protected $message = [
        'client_id.require'                => 'param_error',
        'product_id.require'               => 'param_error',
    ];

    protected $scene = [
        'pay_ontrial'    => ['client_id','product_id'],
    ];

    protected function checkProductId($value,$rule,$data)
    {
        $product = ProductModel::find($value);

        $clientId = $data['client_id']??get_client_id();

        $qty = $data['qty']??1;

        $payOntrial = json_decode($product['pay_ontrial']??[],true);

        if (empty($payOntrial) || empty($payOntrial['status'])){
            return lang('product_pay_ontrial_not_open');
        }
        // 用户限制
        if ($payOntrial['client_limit']=='new' && !new_client($clientId)){
            return lang('pay_ontrial_client_limit_new');
        }
        if ($payOntrial['client_limit']=='host'){
            $activeHostCount = HostModel::where('status', 'Active')
                ->where('client_id',$clientId)
                ->count();
            if ($activeHostCount==0){
                return lang('pay_ontrial_client_limit_host');
            }
        }
        // 账户限制
        if (!empty($payOntrial['account_limit'])){
            $client = ClientModel::where('id',$clientId)->find();
            if (in_array('email',$payOntrial['account_limit']) && empty($client['email'])){
                return lang('pay_ontrial_account_limit_email');
            }
            if (in_array('phone',$payOntrial['account_limit']) && empty($client['phone'])){
                return lang('pay_ontrial_account_limit_phone');
            }
            if (in_array('certification',$payOntrial['account_limit']) && !check_certification($clientId)){
                return lang('pay_ontrial_account_limit_certification');
            }
        }
        // 老用户专项
        if (!empty($payOntrial['old_client_exclusive'])){
            $nameArr = [];
            foreach ($payOntrial['old_client_exclusive'] as $exclusiveProductId){
                if (HostModel::where('client_id',$clientId)->where('status', 'Active')->where('product_id',$exclusiveProductId)->count()==0){
                    $product = ProductModel::find($exclusiveProductId);
                    $nameArr[] = $product['name']??'';
                }
            }
            if (!empty($nameArr)){
                return lang('pay_ontrial_old_client_exclusive',['{name}'=>implode(',',$nameArr)]);
            }
        }
        // 单账户最大可试用数量,0不限制
        if (!empty($payOntrial['max'])){
            $ontrialHostCount = HostModel::where('client_id',$clientId)->where('product_id',$value)->where('first_payment_ontrial',1)->count();
            if ($ontrialHostCount+$qty > $payOntrial['max']){
                return lang('pay_ontrial_max',['{max}'=>$payOntrial['max']]);
            }
        }

        return true;
    }

}
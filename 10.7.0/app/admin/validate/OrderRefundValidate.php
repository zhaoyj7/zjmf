<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 订单退款验证
 * @use app\admin\validate\OrderRefundValidate
 */
class OrderRefundValidate extends Validate
{
    protected $rule = [
        'id'                   => 'require|integer',
        'host_id'              => 'integer|egt:0',
        'amount'               => 'require|float|gt:0',
        'type'                 => 'require|in:credit_first,gateway_first,credit,transaction',
        'notes'                => 'max:1000',
        'gateway'              => 'requireIf:type,transaction|max:255',
    ];

    protected $message  =   [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'host_id.integer'           => 'id_error',
        'host_id.egt'               => 'id_error',
        'amount.require'            => '请输入退款金额',
        'amount.float'              => '退款金额只能是大于0的数字',
        'amount.gt'                 => '退款金额只能是大于0的数字',
        'type.require'              => 'param_error',
        'type.in'                   => 'param_error',
        'notes.max'                 => '备注不能超过1000个字',
        'gateway.requireIf'         => '请选择支付接口',
        'gateway.max'               => '请选择支付接口',
    ];

    protected $scene = [
        'refund' => ['id','host_id','amount','type','notes','gateway'],
    ];

}
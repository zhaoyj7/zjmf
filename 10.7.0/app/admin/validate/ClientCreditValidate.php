<?php
namespace app\admin\validate;

use app\common\model\ClientCreditModel;
use app\common\model\ClientModel;
use think\Validate;

/**
 * 用户余额管理验证
 */
class ClientCreditValidate extends Validate
{
	protected $rule = [
		'id' 			=> 'require|integer|gt:0',
        'type' 		    => 'require|in:recharge,deduction',
        'amount' 		=> 'require|float|gt:0',
        'notes' 	    => 'max:1000',
        'client_id' 	=> 'require',
        'freeze_amount' => 'require|float|gt:0|checkFreezeAmount:thinkphp',
        'client_notes'  => 'require|max:1000',
        'credit_ids'    => 'require|array',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'id.gt'                     => 'id_error',
        'type.require' 			    => 'param_error',
        'type.in'        		    => 'param_error',    
        'amount.require'            => 'please_enter_amount', 
        'amount.float'        		=> 'amount_formatted_incorrectly', 
        'amount.gt'                 => 'amount_formatted_incorrectly', 
        'notes.max'                 => 'notes_cannot_exceed_1000_chars',
        'client_id.require'         => 'param_error',
        'freeze_amount.require'     => 'freeze_amount_required',
        'freeze_amount.float'       => 'freeze_amount_formatted_incorrectly',
        'freeze_amount.gt'          => 'freeze_amount_formatted_incorrectly',
        'client_notes.require'      => 'clients_notes_required',
        'client_notes.max'          => 'clients_notes_cannot_exceed_1000_chars',
        'credit_ids.require'        => 'credit_ids_required',
        'credit_ids.array'          => 'credit_ids_array',
    ];

    protected $scene = [
        'update' => ['id', 'type', 'amount', 'notes'],
        'freeze' => ['client_id', 'freeze_amount', 'client_notes', 'notes'],
        'unfreeze' => ['client_id', 'credit_ids', 'notes'],
    ];

    protected function checkFreezeAmount($value, $rule, $data)
    {
        $clientId = $data['client_id']??0;
        $clientCredit = ClientModel::where('id',$clientId)->value('credit');
        if ($clientCredit<$value){
            return 'freeze_amount_exceeds_balance';
        }
        return true;
    }
}
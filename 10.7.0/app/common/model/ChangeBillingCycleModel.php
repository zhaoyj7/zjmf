<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 产品变更计费方式模型
 * @desc  产品变更计费方式模型
 * @use app\common\model\ChangeBillingCycleModel
 */
class ChangeBillingCycleModel extends Model
{
	protected $name = 'change_billing_cycle';
    
    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'client_id'         => 'int',
        'host_id'           => 'int',
        'order_id'          => 'int',
        'old_billing_cycle' => 'string',
        'new_billing_cycle' => 'string',
        'host_data'         => 'string',
        'data'              => 'string',
        'status'            => 'string',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

}
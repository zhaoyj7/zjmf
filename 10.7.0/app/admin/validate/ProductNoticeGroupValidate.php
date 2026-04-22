<?php
namespace app\admin\validate;

use think\Validate;
use app\common\model\ProductNoticeGroupModel;

/**
 * @title 触发动作组验证
 */
class ProductNoticeGroupValidate extends Validate
{
	protected $rule = [
		'id' 		                => 'require|integer',
        'type' 		                => 'require|max:100',
        'name'                      => 'require|max:100',
        'notice_setting'            => 'require|array|checkNoticeSetting:thinkphp',
        'product_id'                => 'array',
        'act'                       => 'require|checkAct:thinkphp',
        'status'                    => 'require|in:0,1',
    ];

    protected $message = [
    	'id.require'                        => 'id_error',
    	'id.integer'                        => 'id_error',
        'type.require'                      => 'param_error',
        'type.max'                          => 'param_error',
        'name.require'                      => 'product_notice_group_name_require',
        'name.max'                          => 'product_notice_group_name_max',
        'notice_setting.require'            => 'param_error',
        'notice_setting.array'              => 'param_error',
        'notice_setting.checkNoticeSetting' => 'param_error',
        'product_id.array'                  => 'param_error',
        'act.require'                       => 'param_error',
        'act.checkAct'                      => 'param_error',
        'status.require'                    => 'param_error',
        'status.in'                         => 'param_error',
    ];

    protected $scene = [
        'create'    => ['type','name','notice_setting','product_id'],
        'update'    => ['id','name','notice_setting','product_id'],
        'delete'    => ['id'],
        'update_act'=> ['id','act','status'],
    ];

    /**
     * @时间 2025-03-07
     * @title 创建触发动作组
     * @desc  创建触发动作组
     * @author hh
     * @version v1
     * @param   array value - 参数 require
     * @return  bool
     */
    public function checkNoticeSetting($value)
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        foreach($productNoticeGroupAction as $v){
            if(isset($value[$v]) && !in_array($value[$v], [0,1])){
                return false;
            }
        }
        return true;
    }

    /**
     * @时间 2025-03-07
     * @title 验证触发动作
     * @desc  验证触发动作
     * @author hh
     * @version v1
     * @param   array value - 参数 require
     * @return  bool
     */
    public function checkAct($value)
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        
        return in_array($value, $productNoticeGroupAction);
    }

}
<?php
namespace app\admin\validate;

use app\admin\model\ProductDurationGroupPresetsLinkModel;
use app\admin\model\ProductDurationGroupPresetsModel;
use app\common\model\ServerModel;
use think\Validate;

/**
 * 商品周期预设验证器
 */
class ProductDurationGroupPresetsLinkValidate extends Validate
{
	protected $rule = [
        'server_ids' 		=> 'require|array|checkServerIds:thinkphp',
        'gid' 		        => 'require|checkGid:thinkphp',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'server_ids.require'     	=> 'server_id_error',
        'gid.require'     			=> 'product_duration_group_presets_id_error',
    ];

    protected $scene = [
        'create' => ['server_ids', 'gid'],
        'update' => ['server_ids', 'gid'],
    ];

    protected function checkServerIds($value, $rule, $data)
    {
        foreach ($value as $item){
            $server = ServerModel::where('id',$item)->find();
            if (empty($server)){
                return 'server_id_error';
            }
        }
        return true;
    }

    protected function checkGid($value, $rule, $data)
    {
        $group = ProductDurationGroupPresetsModel::where('id',$value)->find();
        if (empty($group)){
            return 'product_duration_group_presets_id_error';
        }
        return true;
    }

}
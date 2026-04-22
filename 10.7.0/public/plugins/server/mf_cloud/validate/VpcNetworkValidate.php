<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title VPC网络验证
 * @use  server\mf_cloud\validate\VpcNetworkValidate
 */
class VpcNetworkValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'name'              => 'require|max:255',
        'ips'               => 'checkVpcIps:thinkphp',
        'data_center_id'    => 'require|integer',
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'name.require'              => 'please_input_vpc_network_name',
        'name.max'                  => 'vpc_network_name_format_error',
        'ips.checkVpcIps'           => 'vpc_network_ips_format_error',
        'data_center_id.require'    => '数据中心不存在',
        'data_center_id.integer'    => '数据中心不存在',
    ];

    protected $scene = [
        'create'        => ['name','ips'],
        'update'        => ['id','name'],
        'ips'           => ['ips'],
        'create_new'    => ['data_center_id','name','ips'],
    ];

    public function checkVpcIps($value){
        return ToolLogic::checkVpcIps($value);
    }


}
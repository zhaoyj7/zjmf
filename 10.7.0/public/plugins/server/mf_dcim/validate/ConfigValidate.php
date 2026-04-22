<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\model\OptionModel;

/**
 * @title 设置参数验证
 * @use server\mf_dcim\validate\ConfigValidate
 */
class ConfigValidate extends Validate
{
	protected $rule = [
        'product_id'                    => 'require|integer',
        'rand_ssh_port'                 => 'require|in:0,1',
        'reinstall_sms_verify'          => 'require|in:0,1',
        'reset_password_sms_verify'     => 'require|in:0,1',
        'manual_resource'               => 'require|in:0,1',
        'level_discount_memory_order'   => 'require|in:0,1',
        'level_discount_memory_upgrade' => 'require|in:0,1',
        'level_discount_memory_renew'   => 'require|in:0,1',
        'level_discount_disk_order'     => 'require|in:0,1',
        'level_discount_disk_upgrade'   => 'require|in:0,1',
        'level_discount_disk_renew'     => 'require|in:0,1',
        'level_discount_bw_upgrade'     => 'require|in:0,1',
        'level_discount_bw_renew'       => 'require|in:0,1',
        'level_discount_ip_num_upgrade' => 'require|in:0,1',
        'level_discount_ip_num_renew'   => 'require|in:0,1',
        'optional_host_auto_create'     => 'require|in:0,1',
        'level_discount_gpu_order'      => 'require|in:0,1',
        'level_discount_gpu_upgrade'    => 'require|in:0,1',
        'level_discount_gpu_renew'      => 'require|in:0,1',
        'sync_firewall_rule'            => 'require|in:0,1',
        'order_default_defence'         => 'checkDefence:thinkphp',
        'auto_sync_dcim_stock'          => 'require|in:0,1',
        'custom_rand_password_rule'     => 'require|in:0,1',
        'default_password_length'       => 'requireIf:custom_rand_password_rule,1|integer|between:6,20',
        'level_discount_bw_order'       => 'require|in:0,1',
        'level_discount_ip_num_order'   => 'require|in:0,1',
    ];

    protected $message = [
        'product_id.require'                    => 'product_id_error',
        'product_id.integer'                    => 'product_id_error',
        'rand_ssh_port.require'                 => 'mf_dcim_rand_ssh_port_param_error',
        'rand_ssh_port.in'                      => 'mf_dcim_rand_ssh_port_param_error',
        'reinstall_sms_verify.require'          => 'mf_dcim_reinstall_sms_verify_param_error',
        'reinstall_sms_verify.in'               => 'mf_dcim_reinstall_sms_verify_param_error',
        'reset_password_sms_verify.require'     => 'mf_dcim_reset_password_sms_verify_param_error',
        'reset_password_sms_verify.in'          => 'mf_dcim_reset_password_sms_verify_param_error',
        'manual_resource.require'               => 'param_error',
        'manual_resource.in'                    => 'param_error',
        'level_discount_memory_order.require'   => 'param_error',
        'level_discount_memory_order.in'        => 'param_error',
        'level_discount_memory_upgrade.require' => 'param_error',
        'level_discount_memory_upgrade.in'      => 'param_error',
        'level_discount_memory_renew.require'   => 'param_error',
        'level_discount_memory_renew.in'        => 'param_error',
        'level_discount_disk_order.require'     => 'param_error',
        'level_discount_disk_order.in'          => 'param_error',
        'level_discount_disk_upgrade.require'   => 'param_error',
        'level_discount_disk_upgrade.in'        => 'param_error',
        'level_discount_disk_renew.require'     => 'param_error',
        'level_discount_disk_renew.in'          => 'param_error',
        'level_discount_bw_upgrade.require'     => 'param_error',
        'level_discount_bw_upgrade.in'          => 'param_error',
        'level_discount_bw_renew.require'       => 'param_error',
        'level_discount_bw_renew.in'            => 'param_error',
        'level_discount_ip_num_upgrade.require' => 'param_error',
        'level_discount_ip_num_upgrade.in'      => 'param_error',
        'level_discount_ip_num_renew.require'   => 'param_error',
        'level_discount_ip_num_renew.in'        => 'param_error',
        'optional_host_auto_create.require'     => 'param_error',
        'optional_host_auto_create.in'          => 'param_error',
        'level_discount_gpu_order.require'      => 'param_error',
        'level_discount_gpu_order.in'           => 'param_error',
        'level_discount_gpu_upgrade.require'    => 'param_error',
        'level_discount_gpu_upgrade.in'         => 'param_error',
        'level_discount_gpu_renew.require'      => 'param_error',
        'level_discount_gpu_renew.in'           => 'param_error',
        'sync_firewall_rule.require'            => 'param_error',
        'sync_firewall_rule.in'                 => 'param_error',
        'auto_sync_dcim_stock.require'          => 'param_error',
        'auto_sync_dcim_stock.in'               => 'param_error',
        'custom_rand_password_rule.require'     => 'param_error',
        'custom_rand_password_rule.in'          => 'param_error',
        'default_password_length.requireIf'     => 'mf_dcim_default_password_length_require',
        'default_password_length.integer'       => 'mf_dcim_default_password_length_error',
        'default_password_length.between'       => 'mf_dcim_default_password_length_error',
        'level_discount_bw_order.require'       => 'param_error',
        'level_discount_bw_order.in'            => 'param_error',
        'level_discount_ip_num_order.require'   => 'param_error',
        'level_discount_ip_num_order.in'        => 'param_error',
    ];

    protected $scene = [
        'save'  => ['product_id','rand_ssh_port','reinstall_sms_verify','reset_password_sms_verify','manual_resource','level_discount_memory_order','level_discount_memory_upgrade','level_discount_memory_renew','level_discount_disk_order','level_discount_disk_upgrade','level_discount_disk_renew','level_discount_bw_upgrade','level_discount_bw_renew','level_discount_ip_num_upgrade','level_discount_ip_num_renew','optional_host_auto_create','level_discount_gpu_order','level_discount_gpu_upgrade','level_discount_gpu_renew','sync_firewall_rule','order_default_defence','auto_sync_dcim_stock','custom_rand_password_rule','default_password_length','level_discount_bw_order','level_discount_ip_num_order'],
    ];

    public function checkDefence($value, $t, $param)
    {
        if(!empty($value)){
            $option = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::GLOBAL_DEFENCE)->where('value', $value)->find();
            if(empty($option)){
                return 'mf_dcim_order_default_defence_not_exist';
            }
        }
        
        return true;
    }

}
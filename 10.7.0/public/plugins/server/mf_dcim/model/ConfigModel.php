<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;

class ConfigModel extends Model
{
	protected $name = 'module_mf_dcim_config';

    // 设置字段信息
    protected $schema = [
        'id'                            => 'int',
        'product_id'                    => 'int',
        'rand_ssh_port'                 => 'int',
        'reinstall_sms_verify'          => 'int',
        'reset_password_sms_verify'     => 'int',
        'manual_resource'               => 'int',
        'level_discount_memory_order'   => 'int',
        'level_discount_memory_upgrade' => 'int',
        'level_discount_memory_renew'   => 'int',
        'level_discount_disk_order'     => 'int',
        'level_discount_disk_upgrade'   => 'int',
        'level_discount_disk_renew'     => 'int',
        'level_discount_bw_upgrade'     => 'int',
        'level_discount_bw_renew'       => 'int',
        'level_discount_ip_num_upgrade' => 'int',
        'level_discount_ip_num_renew'   => 'int',
        'optional_host_auto_create'     => 'int',
        'level_discount_gpu_order'      => 'int',
        'level_discount_gpu_upgrade'    => 'int',
        'level_discount_gpu_renew'      => 'int',
        'sync_firewall_rule'            => 'int',
        'order_default_defence'         => 'string',
        'auto_sync_dcim_stock'          => 'int',
        'custom_rand_password_rule'     => 'int',
        'default_password_length'       => 'int',
        'level_discount_bw_order'       => 'int',
        'level_discount_ip_num_order'   => 'int',
    ];

    // 缓存
    protected $firewallDefenceRule = [];

    /**
     * 时间 2022-06-20
     * @title 获取设置
     * @desc 获取设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int data.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int data.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int data.manual_resource - 手动资源(0=不启用,1=启用)
     * @return  int data.level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int data.level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_disk_renew - 硬盘是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int data.level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int data.level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_ip_num_renew - IP是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int data.optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用)
     * @return  int data.level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int data.level_discount_gpu_renew - 显卡是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int data.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string data.order_default_defence - 订购默认防御峰值
     * @return  int auto_sync_dcim_stock - 自动同步DCIM库存(0=不启用,1=启用)
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int data.level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int data.level_discount_ip_num_order - IP是否应用等级优惠订购(0=不启用,1=启用)
     */
    public function indexConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $ProductModel->id;
            $this->insert($insert);
        }else{
            unset($config['id'], $config['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 保存其他设置
     * @desc 保存其他设置
     * @author hh
     * @version v1
     * @param  int param.product_id - 商品ID require
     * @param  int param.rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @param  int param.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @param  int param.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @param  int param.manual_resource - 手动资源(0=不启用,1=启用)
     * @param  int param.level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int param.level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用)
     * @param  int param.level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int param.level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.level_discount_disk_renew - 硬盘是否应用等级优惠续费(0=不启用,1=启用)
     * @param  int param.level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用)
     * @param  int param.level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.level_discount_ip_num_renew - IP是否应用等级优惠续费(0=不启用,1=启用)
     * @param  int param.optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用)
     * @param  int param.level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int param.level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
     * @param  int param.level_discount_gpu_renew - 显卡是否应用等级优惠续费(0=不启用,1=启用)
     * @param  int param.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启) require
     * @param  string param.order_default_defence - 订购默认防御峰值 require
     * @param  int param.auto_sync_dcim_stock - 自动同步DCIM库存(0=不启用,1=启用) require
     * @param  int param.custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=启用) require
     * @param  string param.default_password_length - 默认密码长度 require
     * @param  int param.level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int param.level_discount_ip_num_order - IP是否应用等级优惠订购(0=不启用,1=启用)
     */
    public function saveConfig($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update($param, ['product_id'=>$param['product_id']], ['rand_ssh_port','reinstall_sms_verify','reset_password_sms_verify','manual_resource','level_discount_memory_order','level_discount_memory_upgrade','level_discount_memory_renew','level_discount_disk_order','level_discount_disk_upgrade','level_discount_disk_renew','level_discount_bw_upgrade','level_discount_bw_renew','level_discount_ip_num_upgrade','level_discount_ip_num_renew','optional_host_auto_create','level_discount_gpu_order','level_discount_gpu_upgrade','level_discount_gpu_renew','sync_firewall_rule','order_default_defence','auto_sync_dcim_stock','custom_rand_password_rule','default_password_length','level_discount_bw_order','level_discount_ip_num_order']);

        // 切换同步防火墙规则时删除防御配置项
        if($config['sync_firewall_rule']!=$param['sync_firewall_rule']){
            $optionIds = OptionModel::where('rel_type', OptionModel::GLOBAL_DEFENCE)->where('product_id', $param['product_id'])->column('id');
            OptionModel::whereIn('id', $optionIds)->delete();
            PriceModel::where('rel_type', 'option')->whereIn('rel_id', $optionIds)->delete();
        }

        if($config['auto_sync_dcim_stock']!=$param['auto_sync_dcim_stock'] && $param['auto_sync_dcim_stock']==1){
            $ModelConfigModel = new ModelConfigModel();
            $ModelConfigModel->syncDcimStock($param['product_id']);
        }

        $switch = [lang_plugins('mf_dcim_switch_off'), lang_plugins('mf_dcim_switch_on')];

        $des = [
            'rand_ssh_port'                 => lang_plugins('mf_dcim_rand_ssh_port'),
            'reinstall_sms_verify'          => lang_plugins('mf_dcim_reinstall_sms_verify'),
            'reset_password_sms_verify'     => lang_plugins('mf_dcim_reset_password_sms_verify'),
            'manual_resource'               => lang_plugins('mf_dcim_manual_resource'),
            'level_discount_memory_order'   => lang_plugins('mf_dcim_level_discount_memory_order'),
            'level_discount_memory_upgrade' => lang_plugins('mf_dcim_level_discount_memory_upgrade'),
            'level_discount_memory_renew'   => lang_plugins('mf_dcim_level_discount_memory_renew'),
            'level_discount_disk_order'     => lang_plugins('mf_dcim_level_discount_disk_order'),
            'level_discount_disk_upgrade'   => lang_plugins('mf_dcim_level_discount_disk_upgrade'),
            'level_discount_disk_renew'     => lang_plugins('mf_dcim_level_discount_disk_renew'),
            'level_discount_bw_upgrade'     => lang_plugins('mf_dcim_level_discount_bw_upgrade'),
            'level_discount_bw_renew'       => lang_plugins('mf_dcim_level_discount_bw_renew'),
            'level_discount_ip_num_upgrade' => lang_plugins('mf_dcim_level_discount_ip_num_upgrade'),
            'level_discount_ip_num_renew'   => lang_plugins('mf_dcim_level_discount_ip_num_renew'),
            'optional_host_auto_create'     => lang_plugins('mf_dcim_optional_host_auto_create'),
            'level_discount_gpu_order'      => lang_plugins('mf_dcim_level_discount_gpu_order'),
            'level_discount_gpu_upgrade'    => lang_plugins('mf_dcim_level_discount_gpu_upgrade'),
            'level_discount_gpu_renew'      => lang_plugins('mf_dcim_level_discount_gpu_renew'),
            'sync_firewall_rule'            => lang_plugins('mf_dcim_sync_firewall_rule'),
            'order_default_defence'         => lang_plugins('mf_dcim_order_default_defence'),
            'auto_sync_dcim_stock'          => lang_plugins('mf_dcim_auto_sync_dcim_stock'),
            'custom_rand_password_rule'     => lang_plugins('mf_dcim_custom_rand_password_rule'),
            'default_password_length'       => lang_plugins('mf_dcim_default_password_length'),
            'level_discount_bw_order'       => lang_plugins('mf_dcim_level_discount_bw_order'),
            'level_discount_ip_num_order'   => lang_plugins('mf_dcim_level_discount_ip_num_order'),
        ];

        foreach($des as $k=>$v){
            if(in_array($k, ['order_default_defence', 'default_password_length'])){

            }else{
                if(isset($config[$k])){
                    $config[$k] = $switch[ $config[$k] ];
                }
                if(isset($param[$k])){
                    $param[$k] = $switch[ $param[$k] ];
                }
            }
        }
        
        $description = ToolLogic::createEditLog($config, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_config_success', [
                '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-02-01
     * @title 获取默认其他设置
     * @desc 获取默认其他设置
     * @author hh
     * @version v1
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int manual_resource - 手动资源(0=不启用,1=启用)
     * @return  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_memory_renew - 内存是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_disk_renew - 硬盘是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_bw_renew - 带宽是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_ip_num_renew - IP是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用)
     * @return  int level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_gpu_renew - 显卡是否应用等级优惠续费(0=不启用,1=启用)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string order_default_defence - 订购默认防御峰值
     * @return  int auto_sync_dcim_stock - 自动同步DCIM库存(0=不启用,1=启用)
     * @return  string custom_rand_password_rule - 自定义随机密码位数(0=关闭,1=开启)
     * @return  string default_password_length - 默认密码长度
     * @return  int level_discount_bw_order - 带宽是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_ip_num_order - IP是否应用等级优惠订购(0=不启用,1=启用)
     */
    protected function getDefaultConfig()
    {
        $defaultConfig = [
            'rand_ssh_port'                 => 0,
            'reinstall_sms_verify'          => 0,
            'reset_password_sms_verify'     => 0,
            'manual_resource'               => 0,
            'level_discount_memory_order'   => 1,
            'level_discount_memory_upgrade' => 1,
            'level_discount_memory_renew'   => 1,
            'level_discount_disk_order'     => 1,
            'level_discount_disk_upgrade'   => 1,
            'level_discount_disk_renew'     => 1,
            'level_discount_bw_upgrade'     => 1,
            'level_discount_bw_renew'       => 1,
            'level_discount_ip_num_upgrade' => 1,
            'level_discount_ip_num_renew'   => 1,
            'optional_host_auto_create'     => 0,
            'level_discount_gpu_order'      => 1,
            'level_discount_gpu_upgrade'    => 1,
            'level_discount_gpu_renew'      => 1,
            'sync_firewall_rule'            => 0,
            'order_default_defence'         => '',
            'auto_sync_dcim_stock'          => 0,
            'custom_rand_password_rule'     => 1,
            'default_password_length'       => 12,
            'level_discount_bw_order'       => 1,
            'level_discount_ip_num_order'   => 1,

        ];
        return $defaultConfig;
    }

    /**
     * 时间 2025-01-14
     * @title 获取防火墙防御规则
     * @desc  获取防火墙防御规则
     * @author theworld
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  array rule - 防火墙规则
     * @return  string rule[].type - 防火墙类型
     * @return  array rule[].list - 防御规则
     * @return  int rule[].list[].id - 防御规则ID
     * @return  string rule[].list[].name - 名称
     * @return  string rule[].list[].defense_peak - 防御峰值,单位Gbps
     * @return  int rule[].list[].enabled - 是否可用(0=否1=是)
     * @return  int rule[].list[].create_time - 创建时间
     * @return  int rule[].list[].update_time - 更新时间
     * @return  string rule[].type - 防火墙类型
     * @return  string rule[].name - 防火墙名称
     */
    public function firewallDefenceRule($param)
    {
        $rule = [];
        $hookRes = hook('firewall_set_meal_list', ['product_id' => intval($param['product_id'] ?? 0)]);
        foreach ($hookRes as $key => $value) {
            if(isset($value['type']) && !empty($value['list']) ){
                $rule[] = $value;
            }
        }
        $result = [
            'rule' => $rule,
        ];
        return $result;
    }

    /**
     * @时间 2025-01-15
     * @title 获取防火墙规则
     * @desc  获取防火墙规则
     * @author theworld
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.firewall_type - 防火墙类型 require
     * @param   int param.defence_rule_id - 防御规则ID require
     */
    public function getFirewallDefenceRule($param)
    {
        $data = [];
        if(!isset($this->firewallDefenceRule[ $param['product_id'] ])){
            $result = $this->firewallDefenceRule($param);
            
            $this->firewallDefenceRule[ $param['product_id'] ] = $result;
        }else{
            $result = $this->firewallDefenceRule[ $param['product_id'] ];
        }
        foreach($result['rule'] as $v){
            if($param['firewall_type'] == $v['type']){
                foreach($v['list'] as $vv){
                    if($param['defence_rule_id'] == $vv['id']){
                        $data = $vv;
                        break;
                    }
                }
            }
        }
        return $data;
    }
}
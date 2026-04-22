<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * 安全组配置验证器
 * @author hh
 */
class SecurityGroupConfigValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'product_id'    => 'require|integer',
        'description'   => 'require|max:1000',
        'protocol'      => 'require|in:all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis',
        'port'          => 'require|checkPort:thinkphp',
        'status'        => 'require|in:0,1',
        'ids'           => 'require|array|checkIds:thinkphp',
    ];

    protected $message = [
        'id.require'            => 'param_error',
        'id.integer'            => 'param_error',
        'product_id.require'    => 'param_error',
        'product_id.integer'    => 'param_error',
        'name.require'          => 'mf_cloud_config_name_require',
        'name.max'              => 'mf_cloud_config_name_max',
        'description.require'   => 'mf_cloud_config_description_require',
        'description.max'       => 'mf_cloud_config_description_max',
        'protocol.require'      => 'mf_cloud_config_type_require',
        'protocol.in'           => 'mf_cloud_config_type_error',
        'port.require'          => 'mf_cloud_config_port_require',
        'status.in'             => 'param_error',
        'sort.integer'          => 'param_error',
        'ids.require'           => 'param_error',
        'ids.array'             => 'param_error',
    ];

    protected $scene = [
        'create'        => ['product_id', 'description', 'protocol', 'port'],
        'update'        => ['id', 'description', 'protocol', 'port'],
        'delete'        => ['id'],
        'status'        => ['id', 'status'],
        'reset'         => ['product_id'],
        'sort'          => ['ids'],
    ];

    /**
     * 验证端口格式
     * @param string $value
     * @param mixed $rule
     * @param array $data
     * @return bool|string
     */
    protected function checkPort($value, $rule, $data)
    {
        // icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis协议固定端口
        if (in_array($data['protocol'], ['all','all_tcp','all_udp','icmp','ssh','telnet','http','https','mssql','oracle','mysql','rdp','postgresql','redis'])) {
            return true;
        }

        // 支持单个端口、端口范围、多个端口
        // 例如: 22, 80-443, 22,80,443
        
        // 端口范围格式: 1-65535
        if (preg_match('/^\d+-\d+$/', $value)) {
            list($start, $end) = explode('-', $value);
            if ($start < 1 || $start > 65535 || $end < 1 || $end > 65535 || $start > $end) {
                return lang_plugins('mf_cloud_config_port_format_error');
            }
            return true;
        }
        
        // 单个端口: 22
        $port = $value;
        $port = trim($port);
        if (!is_numeric($port) || $port < 1 || $port > 65535) {
            return lang_plugins('mf_cloud_config_port_format_error');
        }
        
        return true;
    }

    /**
     * 验证ID数组
     * @param array $value
     * @param mixed $rule
     * @param array $data
     * @return bool|string
     */
    protected function checkIds($value, $rule, $data)
    {
        if (empty($value) || !is_array($value)) {
            return lang_plugins('param_error');
        }

        // 验证数组中的每个元素都是整数
        foreach ($value as $id) {
            if (!is_numeric($id) || $id <= 0) {
                return lang_plugins('param_error');
            }
        }

        return true;
    }
}

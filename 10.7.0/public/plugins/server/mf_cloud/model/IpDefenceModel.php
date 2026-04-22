<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title IP防御模型
 * @use server\mf_cloud\model\IpDefenceModel
 */
class IpDefenceModel extends Model
{
    protected $name = 'module_mf_cloud_ip_defence';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'ip'        => 'string',
        'defence'   => 'string',
    ];

    public function saveDefence($param)
    {
        $this->where('host_id', $param['host_id'])->whereIn('ip', $param['ip'])->delete();
        $arr = [];
        foreach ($param['ip'] as $v) {
            $arr[] = ['host_id' => $param['host_id'], 'ip' => $v, 'defence' => $param['defence']];
        }
        $this->saveAll($arr);

    }
    

}
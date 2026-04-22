<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title 磁盘模型
 * @use server\mf_cloud\model\DiskModel
 */
class DiskModel extends Model
{
	protected $name = 'module_mf_cloud_disk';

    // 设置字段信息
    protected $schema = [
        'id'            	=> 'int',
        'name'            	=> 'string',
        'size'            	=> 'int',
        'rel_id'            => 'int',
        'host_id'           => 'int',
        'create_time'       => 'int',
        'type'            	=> 'string',
        'price'             => 'float',
        'is_free'           => 'int',
        'status'            => 'int',
        'type2'             => 'string',
        'upstream_id'       => 'int',
        'free_size'         => 'int',
    ];

    /**
     * 时间 2024-02-18
     * @title 磁盘列表
     * @desc  磁盘列表
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  int [].id - 磁盘ID
     * @return  int [].rel_id - 关联魔方云ID
     * @return  string [].name - 名称
     * @return  int [].size - 磁盘大小(GB)
     * @return  int [].create_time - 创建时间
     * @return  string [].type - 磁盘类型
     * @return  int [].is_free - 是否免费盘(0=否,1=是)
     * @return  int [].status - 磁盘状态(0=卸载,1=挂载,2=正在挂载,3=创建中)
     * @return  string [].type2 - 类型(system=系统盘,data=数据盘)
     * @return  int [].upstream_id - 上游ID
     */
    public function diskList($hostId)
    {
    	$data = $this
    			->field('id,rel_id,name,size,create_time,type,is_free,status,type2,upstream_id')
    			->where('host_id', $hostId)
                ->orderRaw("field(type2, 'system', 'data')")
                ->order('id', 'asc')
    			->select()
    			->toArray();
    	return $data;
    }

    /**
     * 时间 2024-08-14
     * @title 创建一个数据盘数据
     * @desc  创建一个数据盘数据
     * @author hh
     * @version v1
     * @param   int param.size - 磁盘大小 require
     * @param   int param.host_id - 产品ID require
     * @param   int param.rel_id 0 关联云磁盘ID
     * @param   string param.type - 硬盘类型
     * @param   string param.price 0 价格
     * @param   int param.status 3 磁盘状态(0=卸载,1=挂载,2=正在挂载,3=创建中)
     * @return  int - - 磁盘ID
     */
    public function createDataDisk($param)
    {
        $data = [
            'name'          => lang_plugins('mf_cloud_disk') . rand_str(8, 'NUMBER'),
            'size'          => $param['size'],
            'rel_id'        => $param['rel_id'] ?? 0,
            'host_id'       => $param['host_id'],
            'create_time'   => time(),
            'type'          => $param['type'] ?? '',
            'price'         => $param['price'] ?? '0.00',
            'status'        => $param['status'] ?? 3,
            'type2'         => 'data',
        ];
        $disk = $this->create($data);
        return (int)$disk->id;
    }










}
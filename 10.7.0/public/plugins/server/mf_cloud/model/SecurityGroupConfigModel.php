<?php
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use app\common\model\ConfigurationModel;

/**
 * 安全组配置模型
 * @author hh
 */
class SecurityGroupConfigModel extends Model
{
    protected $name = 'module_mf_cloud_security_group_config';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'description'   => 'string',
        'protocol'      => 'string',
        'port'          => 'string',
        'direction'     => 'string',
        'status'        => 'int',
        'sort'          => 'int',
        'upstream_id'   => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 获取商品的安全组配置列表
     * @param int $productId 商品ID
     * @param bool $onlyEnabled 是否只获取启用的
     * @return array
     */
    public function getConfigList($productId, $onlyEnabled = false): array
    {
        $where = [
            ['product_id', '=', $productId]
        ];

        if ($onlyEnabled) {
            $where[] = ['status', '=', 1];
        }

        $list = $this
            ->field('id,description,protocol,port,direction,status')
            ->where($where)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 如果没有配置，检查是否是魔方云商品，自动初始化
        if (empty($list)) {
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            
            if ($product && $product->getModule() == 'mf_cloud') {
                // 自动初始化默认配置
                $this->initDefaultConfigs($productId);
                
                // 重新查询
                $list = $this
                    ->field('id,description,protocol,port,direction,status')
                    ->where($where)
                    ->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->select()
                    ->toArray();
            }
        }

        return ['list'=>$list, 'count'=>count($list) ];
    }

    /**
     * 初始化默认安全组配置
     * @param int $productId 商品ID
     * @return bool
     */
    public function initDefaultConfigs($productId)
    {
        $time = time();
        
        $defaultConfigs = [
            [
                'product_id'    => $productId,
                'description'   => '公网Ping云服务器',
                'protocol'      => 'icmp',
                'port'          => '1-65535',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 1,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => 'SSH远程连接Linux实例',
                'protocol'      => 'ssh',
                'port'          => '22',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 2,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => 'RDP远程连接Windows实例',
                'protocol'      => 'rdp',
                'port'          => '3389',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 3,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => '云服务器作Web服务器（HTTP）',
                'protocol'      => 'http',
                'port'          => '80',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 4,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => '云服务器作Web服务器（HTTPS）',
                'protocol'      => 'https',
                'port'          => '443',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 5,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => 'Telnet远程控制网络设备',
                'protocol'      => 'telnet',
                'port'          => '23',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 6,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'description'   => 'DNS域名解析',
                'protocol'      => 'udp',
                'port'          => '53',
                'direction'     => 'in',
                'status'        => 1,
                'sort'          => 7,
                'create_time'   => $time,
            ],
        ];

        $this->insertAll($defaultConfigs);
        
        return true;
    }

    /**
     * 验证商品是否是魔方云商品
     * @param int $productId - 商品ID require
     * @return array
     */
    public function checkProduct($productId): array
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($productId);
        
        if ($product && $product->getModule() == 'mf_cloud'){
            return ['status'=>200, 'msg'=>lang_plugins('success_message') ];
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found') ];
        }
    }

    /**
     * 添加配置
     * @param array $param
     * @return array
     */
    public function createConfig($param): array
    {
        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->systemList();
        if(empty($configuration['edition'])){
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_edition_error')];
        }

        $check = $this->checkProduct($param['product_id']);
        if ($check['status'] != 200) {
            return $check;
        }

        $param['port'] = $this->getConfigPort($param);

        $maxSort = $this->where('product_id', $param['product_id'])->max('sort');

        $data = [
            'product_id'    => $param['product_id'],
            'description'   => $param['description'],
            'protocol'      => $param['protocol'],
            'port'          => $param['port'],
            'direction'     => 'in',
            'status'        => 1,
            'sort'          => $maxSort+1,
            'create_time'   => time(),
        ];

        // 是否已添加过
        $exists = $this->where([
            ['product_id', '=', $param['product_id']],
            ['protocol', '=', $param['protocol']],
            ['port', '=', $param['port']],
        ])->find();
        if ($exists) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_exists')];
        }
        
        $securityGroupConfig = $this->create($data);
        
        $result = [
            'status' => 200,
            'msg'  => lang_plugins('create_success'),
            'data' => [
                'id' => (int)$securityGroupConfig->id
            ],
        ];
        return $result;
    }

    /**
     * 更新配置
     * @param array $param
     * @return array
     */
    public function updateConfig($param): array
    {
        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->systemList();
        if(empty($configuration['edition'])){
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_edition_error')];
        }

        $id = $param['id'] ?? 0;
        $config = $this->find($id);
        
        if (empty($config)) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_not_found')];
        }

        $param['port'] = $this->getConfigPort($param);

        // 检查是否与其他记录重复（排除自身）
        $exists = $this->where([
            ['id', '<>', $id],
            ['product_id', '=', $config->product_id],
            ['protocol', '=', $param['protocol']],
            ['port', '=', $param['port']],
        ])->find();
        if ($exists) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_exists')];
        }

        $data = [
            'description'   => $param['description'],
            'protocol'      => $param['protocol'],
            'port'          => $param['port'],
            'update_time'   => time(),
        ];
        
        $this->where('id', $config['id'])->update($data);
        
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    /**
     * 删除配置
     * @param int $id
     * @return array
     */
    public function deleteConfig($id): array
    {
        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->systemList();
        if(empty($configuration['edition'])){
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_edition_error')];
        }

        $config = $this->find($id);
        
        if (empty($config)) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_not_found')];
        }
        
        // 检查删除后是否还有其他规则
        $count = $this->where('product_id', $config['product_id'])
            ->where('id', '<>', $id)
            ->count();
        
        if ($count == 0) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_delete_last_error')];
        }
        
        $config->delete();
        
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    /**
     * 更新配置状态
     * @param array $param
     * @return array
     */
    public function updateStatus($param): array
    {
        $id = $param['id'];
        $status = $param['status'];

        $config = $this->find($id);
        
        if (empty($config)) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_not_found')];
        }
        
        // 如果是禁用操作，检查是否还有其他启用的规则
        if ($status == 0) {
            $count = $this->where('product_id', $config['product_id'])
                ->where('id', '<>', $id)
                ->where('status', 1)
                ->count();
            
            if ($count == 0) {
                return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_disable_last_error')];
            }
        }
        
        $this->where('id', $id)->update(['status' => $status]);
        
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    /**
     * 获取配置端口
     * @param array $param
     * @return string
     */
    public function getConfigPort($param)
    {
        // all,all_tcp,all_udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis自动端口
        if (in_array($param['protocol'], ['all','all_tcp','all_udp','icmp','ssh','telnet','http','https','mssql','oracle','mysql','rdp','postgresql','redis'])) {
            switch ($param['protocol']) {
                case 'all':
                case 'all_tcp':
                case 'all_udp':
                case 'icmp':
                    $param['port'] = '1-65535';
                    break;
                case 'ssh':
                    $param['port'] = '22';
                    break;
                case 'telnet':
                    $param['port'] = '23';
                    break;
                case 'http':
                    $param['port'] = '80';
                    break;
                case 'https':
                    $param['port'] = '443';
                    break;
                case 'mssql':
                    $param['port'] = '1433';
                    break;
                case 'oracle':
                    $param['port'] = '1521';
                    break;
                case 'mysql':
                    $param['port'] = '3306';
                    break;
                case 'rdp':
                    $param['port'] = '3389';
                    break;
                case 'postgresql':
                    $param['port'] = '5432';
                    break;
                case 'redis':
                    $param['port'] = '6379';
                    break;
            }
        }
        return $param['port'] ?? '1-65535';
    }

    public function homeConfigList($productId): array
    {
        $where = [
            ['product_id', '=', $productId],
            ['status', '=', 1]
        ];

        $list = $this
            ->field('id,description,protocol,port,direction')
            ->where($where)
            ->withAttr('id', function($val){
                return 'id_'.$val;
            })
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 如果没有配置，检查是否是魔方云商品，自动初始化
        if (empty($list)) {
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            
            if ($product && $product->getModule() == 'mf_cloud') {
                // 自动初始化默认配置
                $this->initDefaultConfigs($productId);
                
                // 重新查询
                $list = $this
                    ->field('id,description,protocol,port,direction')
                    ->where($where)
                    ->withAttr('id', function($val){
                        return 'id_'.$val;
                    })
                    ->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->select()
                    ->toArray();
            }
        }
        // if(!empty($list)){
        //     $ConfigurationModel = new ConfigurationModel();
        //     $configuration = $ConfigurationModel->systemList();
        //     if(!empty($configuration['edition'])){
        //         $list[] = [
        //             'id'            => 'remote_port',
        //             'port'          => '',
        //             'description'   => '根据SSH端口自动配置',
        //             'direction'     => 'in',
        //             'protocol'      => 'tcp',
        //         ];
        //     }
        // }

        return $list;
    }

    /**
     * 重置为默认配置
     * @param int $productId 商品ID
     * @return array
     */
    public function resetConfigs($productId): array
    {
        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->systemList();
        if(empty($configuration['edition'])){
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_edition_error')];
        }

        $check = $this->checkProduct($productId);
        if ($check['status'] != 200) {
            return $check;
        }

        // 使用事务确保数据一致性
        $this->startTrans();
        try {
            // 删除该商品的所有配置
            $this->where('product_id', $productId)->delete();
            
            // 重新初始化默认配置
            $this->initDefaultConfigs($productId);
            
            $this->commit();
            
            return ['status' => 200, 'msg' => lang_plugins('mf_cloud_config_reset_success')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_reset_error')];
        }
    }

    /**
     * 配置排序
     * @param array $ids 配置ID数组
     * @return array
     */
    public function sortConfigs($ids): array
    {
        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->systemList();
        if(empty($configuration['edition'])){
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_edition_error')];
        }

        if (empty($ids) || !is_array($ids)) {
            return ['status' => 400, 'msg' => lang_plugins('param_error')];
        }

        // 查询所有配置,验证是否属于同一个商品
        $configs = $this->whereIn('id', $ids)->select();
        
        if ($configs->isEmpty()) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_not_found')];
        }

        if (count($configs) != count($ids)) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_not_found')];
        }

        // 验证所有配置是否属于同一个商品
        $productIds = array_unique(array_column($configs->toArray(), 'product_id'));
        if (count($productIds) > 1) {
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_product_not_same')];
        }

        // 使用事务确保数据一致性
        $this->startTrans();
        try {
            // 遍历ID数组,更新排序
            foreach ($ids as $index => $id) {
                $this->where('id', $id)->update(['sort' => $index + 1]);
            }
            
            $this->commit();
            
            return ['status' => 200, 'msg' => lang_plugins('mf_cloud_config_sort_success')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('mf_cloud_config_sort_error')];
        }
    }

}

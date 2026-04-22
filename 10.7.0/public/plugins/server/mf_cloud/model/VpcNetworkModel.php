<?php 
namespace server\mf_cloud\model;

use think\facade\Db;
use think\Model;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\MfCloudDataCenterMapGroupLinkModel;
use app\common\logic\DownstreamProductLogic;

use server\mf_cloud_ip\model\HostLinkModel AS IPHL;
use server\mf_cloud_mysql\model\HostLinkModel AS MYSQLHL;

/**
 * @title VPC网络模型
 * @use server\mf_cloud\model\VpcNetworkModel
 */
class VpcNetworkModel extends Model
{
	protected $name = 'module_mf_cloud_vpc_network';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'product_id'            => 'int',
        'data_center_id'        => 'int',
        'name'                  => 'string',
        'client_id'             => 'int',
        'ips'                   => 'string',
        'rel_id'                => 'int',
        'vpc_name'              => 'string',
        'create_time'           => 'int',
        'downstream_client_id'  => 'int',
        'upstream_id'           => 'int',
    ];

    /**
     * 时间 2023-02-13
     * @title 搜索VPC网络
     * @desc 搜索VPC网络
     * @author hh
     * @version v1
     * @param  array param - 参数 require
     * @param  int param.id - 商品ID require
     * @param  int param.data_center_id - 数据中心ID require
     * @param  int param.downstream_client_id - 下游用户ID(api对接可用)
     * @return int list[].id - VPC网络ID
     * @return string list[].name - VPC网络名称
     */
    public function vpcNetworkSearch($param): array
    {
        $clientId = get_client_id() ?? 0;

        $list = [];
        $groupId = 0;
        if($this->supportDataCenterMap()){
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $groupId = $MfCloudDataCenterMapGroupLinkModel->getGroupId($param['id'], $param['data_center_id'] ?? 0);
        }
        
        
        if(!empty($groupId)){
            $where = [];
            $where[] = ['v.client_id', '=', $clientId];
            $where[] = ['gl.mf_cloud_data_center_map_group_id', '=', $groupId];

            if(request()->is_api && isset($param['downstream_client_id'])){
                $where[] = ['v.downstream_client_id', '=', $param['downstream_client_id']];
            }

            $list = $this
                ->alias('v')
                ->field('v.id,v.name')
                ->join('mf_cloud_data_center_map_group_link gl', 'v.product_id = gl.product_id AND v.data_center_id = gl.data_center_id')
                ->where($where)
                ->select()
                ->toArray();
        }else{
            $where = [];
            $where[] = ['client_id', '=', $clientId];
            $where[] = ['product_id', '=', $param['id']];
            $where[] = ['data_center_id', '=', $param['data_center_id'] ?? 0];
            

            if(request()->is_api && isset($param['downstream_client_id'])){
                $where[] = ['downstream_client_id', '=', $param['downstream_client_id']];
            }

            $list = $this
                    ->field('id,name')
                    ->where($where)
                    ->select()
                    ->toArray();
        }

        if(!empty($list)){
            $vpcNetworkId = array_column($list, 'id');

            $hostArr = $this->vpcHostList($vpcNetworkId);

            foreach($list as $k=>$v){
                $list[$k]['host'] = $hostArr[$v['id']] ?? [];
            }
        }
        return ['list'=>$list];
    }

    /**
     * 时间 2023-02-13
     * @title 创建VPC网络
     * @desc 创建VPC网络
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   string param.ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
     * @param   string param.name - VPC网络名称 require
     * @param   int param.product_id - 商品ID require
     * @param   int param.data_center_id - 数据中心ID require
     * @param   int param.client_id - 用户ID require
     * @param   int param.downstream_client_id - 下游用户ID(api时可以使用)
     * @param   int param.upstream_id - 上游ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - VPC网络ID
     */
    public function vpcNetworkCreate($param): array
    {
        $param['ips'] = isset($param['ips']) && !empty($param['ips']) ? $param['ips'] : '10.0.0.0/16';
        $param['create_time'] = time();
        $param['vpc_name'] = 'VPC-'.rand_str(8);
        if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id']>0){
            // $param['downstream_client_id'] = $param['downstream_client_id'];
        }else{
            $param['downstream_client_id'] = 0;
        }

        $vpc = $this->create($param, ['product_id','data_center_id','name','vpc_name','client_id','ips','create_time','downstream_client_id','upstream_id']);

        $description = lang_plugins('log_mf_cloud_add_vpc_network_success', [
            '{name}' => $param['name'],
            '{ips}' => $param['ips'],
        ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$vpc->id
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title VPC网络列表
     * @desc VPC网络列表
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 产品ID require
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序(id,name)
     * @param   string param.sort - 升降序(asc,desc)
     * @param   int param.downstream_client_id - 下游用户ID(api时可用)
     * @return  int list[].id - VPC网络ID
     * @return  string list[].name - VPC网络名称
     * @return  string list[].ips - VPC网络网段
     * @return  int count - 总条数
     * @return  int list[].host[].id - 主机产品ID
     * @return  string list[].host[].name - 主机标识
     * @return  array host - 可用产品ID(api时返回)
     */
    public function vpcNetworkList($param): array
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','name'])){
            $param['orderby'] = 'id';
        }
        $clientId = get_client_id();

        $list = [];
        $count = 0;

        $host = HostModel::find($param['id']);
        if(empty($host) || $host['client_id'] != $clientId || $host['is_delete']){
            return ['list'=>$list, 'count'=>$count];
        }
        // 这里需要判断模块获取对应data_center_id
        $module = $host->getModule();
        $dataCenterId = 0;
        if($module == 'mf_cloud'){
            $dataCenterId = HostLinkModel::where('host_id', $param['id'])->value('data_center_id');
        }else if($module == 'mf_cloud_ip'){
            $dataCenterId = IPHL::where('host_id', $param['id'])->value('data_center_id');
        }else if($module == 'mf_cloud_mysql'){
            $dataCenterId = MYSQLHL::where('host_id', $param['id'])->value('data_center_id');
        }
        if(empty($dataCenterId)){
            return ['list'=>$list, 'count'=>$count];
        }
        $groupId = 0;
        if($this->supportDataCenterMap()){
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $groupId = $MfCloudDataCenterMapGroupLinkModel->getGroupId($host['product_id'], $dataCenterId);
        }
        if(!empty($groupId)){
            $where = [];
            $where[] = ['v.client_id', '=', $clientId];
            $where[] = ['gl.mf_cloud_data_center_map_group_id', '=', $groupId];

            if(request()->is_api && isset($param['downstream_client_id'])){
                $where[] = ['v.downstream_client_id', '=', $param['downstream_client_id']];
            }

            $list = $this
                ->alias('v')
                ->field('v.id,v.name,v.ips')
                ->join('mf_cloud_data_center_map_group_link gl', 'v.product_id = gl.product_id AND v.data_center_id = gl.data_center_id')
                ->where($where)
                ->select()
                ->toArray();

            $count = $this
                ->alias('v')
                ->join('mf_cloud_data_center_map_group_link gl', 'v.product_id = gl.product_id AND v.data_center_id = gl.data_center_id')
                ->where($where)
                ->count();
            
        }else{
            $where = [];
            $where[] = ['product_id', '=', $host['product_id']];
            $where[] = ['data_center_id', '=', $dataCenterId];
            $where[] = ['client_id', '=', $clientId];
            if(request()->is_api && isset($param['downstream_client_id'])){
                $where[] = ['downstream_client_id', '=', $param['downstream_client_id'] ];
            }

            $list = $this
                ->field('id,name,ips')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();

            $count = $this
                ->where($where)
                ->count();
        }
        if(!empty($list)){
            $vpcNetworkId = array_column($list, 'id');

            $hostArr = $this->vpcHostList($vpcNetworkId);

            foreach($list as $k=>$v){
                $list[$k]['host'] = $hostArr[$v['id']] ?? [];
            }
        }

        $result = ['list'=>$list, 'count'=>$count];
        if(request()->is_api){
            $result['host'] = !empty($list) ? array_column($list, 'id') : [];
        }
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title 修改VPC网络
     * @desc 修改VPC网络
     * @author hh
     * @version v1
     * @param   int param.vpc_network_id - VPC网络ID require
     * @param   string param.name - VPC网络名称 require
     * @param   int param.downstream_client_id - 下游用户ID(api时可用)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - 原VPC名称
     */
    // public function vpcNetworkUpdate($param): array
    // {
    //     $clientId = get_client_id();
    //     $vpcNetwork = $this->find($param['vpc_network_id']);
    //     if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId){
    //         return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
    //     }
    //     if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
    //         return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
    //     }
    //     $this->update($param, ['id'=>$param['vpc_network_id']], ['name']);

    //     if($param['name'] != $vpcNetwork['name']){
    //         $description = lang_plugins('log_mf_cloud_modify_vpc_network_success', [
    //             '{name}' => $vpcNetwork['name'],
    //             '{new_name}' => $param['name'],
    //         ]);
    //         active_log($description, 'product', $vpcNetwork['product_id']);
    //     }

    //     $result = [
    //         'status' => 200,
    //         'msg'    => lang_plugins('update_success'),
    //         'data'   => [
    //             'name' => $vpcNetwork['name'],
    //         ]
    //     ];
    //     return $result;
    // }

    /**
     * 时间 2023-02-13
     * @title 删除VPC网络
     * @desc 删除VPC网络
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.vpc_network_id - VPC网络ID require
     * @param   int param.downstream_client_id - 下游用户ID(api时可用)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - VPC名称
     * @return  string data.ips - VPCIP段
     */
    public function vpcNetworkDelete($param): array
    {
        $clientId = get_client_id();
        $vpcNetwork = $this->find($param['vpc_network_id']);
        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if(request()->is_api && !empty($vpcNetwork['downstream_client_id']) && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        // 是否还有主机正在使用
        $hookRes = hook('before_mf_cloud_vpc_network_delete', ['vpc_network_id'=>$param['vpc_network_id']]);
        foreach($hookRes as $v){
            if(!empty($v) && is_array($v) && !empty($v['status']) && $v['status'] != 200){
                return $v;
            }
        }

        $DownstreamProductLogic = new DownstreamProductLogic($vpcNetwork['product_id']);
        if($DownstreamProductLogic->isDownstreamSync){
            $path = sprintf('console/v1/product/%d/mf_cloud/vpc_network', $DownstreamProductLogic->upstreamProductId);
            $post = [
                'vpc_network_id'    => $vpcNetwork['upstream_id'],
            ];
            $res = $DownstreamProductLogic->curl($path, $post, 'DELETE');
            if($res['status'] != 200){
                return $res;
            }
        }

        $this->where('id', $param['vpc_network_id'])->delete();

        $description = lang_plugins('log_mf_cloud_delete_vpc_network_success', [
            '{name}'=>$vpcNetwork['name'],
            '{ips}' => $vpcNetwork['ips'],
        ]);
        active_log($description, 'product', $vpcNetwork['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
            'data'   => [
                'name' => $vpcNetwork['name'],
                'ips' => $vpcNetwork['ips'],
            ]
        ];
        return $result;
    }

    /**
     * 时间 2024-08-14
     * @title 创建VPC网络
     * @desc  创建VPC网络
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   string name - VPC网络名称
     * @param   string ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - VPC网络ID
     */
    public function vpcNetworkCreateNew($param, $clientId = NULL): array
    {
        $product = ProductModel::find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($product->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter) || $dataCenter['product_id'] != $product['id']){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $vpcName = 'VPC-'.rand_str(8);
        $clientId = $clientId ?? get_client_id();

        $vpcData = [
            'product_id'        => $param['id'],
            'data_center_id'    => $param['data_center_id'],
            'name'              => $param['name'] ?? $vpcName,
            'vpc_name'          => $vpcName,
            'ips'               => isset($param['ips']) && !empty($param['ips']) ? $param['ips'] : '10.0.0.0/16',
            'client_id'         => $clientId,
            'create_time'       => time(),
            'upstream_id'       => 0,
        ];

        $DownstreamProductLogic = new DownstreamProductLogic($product);

        if($DownstreamProductLogic->downstreamRequest && isset($param['downstream_client_id']) && !empty($param['downstream_client_id'])){
            $vpcData['downstream_client_id'] = $param['downstream_client_id'];
        }
        if($DownstreamProductLogic->isDownstreamSync){
            $path = sprintf('console/v1/product/%d/mf_cloud/vpc_network', $DownstreamProductLogic->upstreamProductId);
            $post = [
                'data_center_id'    => $dataCenter['upstream_id'],
                'name'              => $vpcData['name'],
                'ips'               => $vpcData['ips'],
            ];
            $res = $DownstreamProductLogic->curl($path, $post, 'POST');
            if($res['status'] != 200){
                return $res;
            }
            $vpcData['upstream_id'] = $res['data']['id'];
        }

        $vpc = $this->create($vpcData);
        
        $description = lang_plugins('log_mf_cloud_add_vpc_network_success', [
            '{name}' => $vpcData['name'],
            '{ips}'  => $vpcData['ips'],
        ]);
        active_log($description, 'product', $product->id);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$vpc->id
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title 删除VPC网络
     * @desc 删除VPC网络
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @param   int param.vpc_network_id - VPC网络ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - VPC名称
     * @return  string data.ips - VPCIP段
     */
    public function vpcNetworkDeleteNew($param): array
    {
        $product = ProductModel::find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($product->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $clientId = get_client_id();
        $vpcNetwork = $this->find($param['vpc_network_id'] ?? 0);
        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId || $vpcNetwork['product_id'] != $product['id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if(request()->is_api && !empty($vpcNetwork['downstream_client_id']) && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        // 是否还有主机正在使用
        $hookRes = hook('before_mf_cloud_vpc_network_delete', ['id'=>$param['vpc_network_id']]);
        foreach($hookRes as $v){
            if(!empty($v) && is_array($v) && !empty($v['status']) && $v['status'] != 200){
                return $v;
            }
        }

        $DownstreamProductLogic = new DownstreamProductLogic($product);
        if($DownstreamProductLogic->isDownstreamSync){
            $path = sprintf('console/v1/product/%d/mf_cloud/vpc_network', $DownstreamProductLogic->upstreamProductId);
            $post = [
                'vpc_network_id'    => $vpcNetwork['upstream_id'],
            ];
            $res = $DownstreamProductLogic->curl($path, $post, 'DELETE');
            if($res['status'] != 200){
                return $res;
            }
        }

        $this->where('id', $param['vpc_network_id'])->delete();

        $description = lang_plugins('log_mf_cloud_delete_vpc_network_success', [
            '{name}'=>$vpcNetwork['name'],
            '{ips}' => $vpcNetwork['ips'],
        ]);
        active_log($description, 'product', $vpcNetwork['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
            'data'   => [
                'name' => $vpcNetwork['name'],
                'ips' => $vpcNetwork['ips'],
            ]
        ];
        return $result;
    }

    /**
     * 时间 2025-11-19
     * @title 验证VPC是否可用于商品数据中心
     * @desc  验证VPC是否可用于商品数据中心
     * @author hh
     * @version v1
     * @param   int vpc.id - VPC网络ID require
     * @param   int vpc.data_center_id - 数据中心ID require
     * @param   int vpc.product_id - 商品ID require
     * @param   int productId - 商品ID require
     * @param   int dataCenterId - 数据中心ID require
     * @return  bool - - 检查结果
     */
    public function checkVpcIsEnable($vpc, $productId, $dataCenterId): bool
    {
        $groupId = 0;
        if($this->supportDataCenterMap()){
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $groupId = $MfCloudDataCenterMapGroupLinkModel->getGroupId($productId, $dataCenterId);
        }
        if(!empty($groupId)){
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $groupId2 = $MfCloudDataCenterMapGroupLinkModel->getGroupId($vpc['product_id'], $vpc['data_center_id']);
            
            return $groupId == $groupId2;
        }else{
            return $vpc['product_id'] == $productId && $vpc['data_center_id'] == $dataCenterId;
        }
    }

    /**
     * 时间 2025-11-18
     * @title 是否支持数据中心映射
     * @desc  是否支持数据中心映射
     * @author hh
     * @version v1
     * @return bool
     */
    public function supportDataCenterMap(): bool
    {
        return class_exists('app\common\model\MfCloudDataCenterMapGroupLinkModel');
    }

    /**
     * @时间 2024-08-16
     * @title 获取VPC可分配产品列表
     * @desc  获取VPC可分配产品列表
     * @author hh
     * @param   array param - 参数 require
     * @param   int param.page 1 页数
     * @param   int param.limit 20 每页条数
     * @param   int param.product_id - 商品ID
     * @param   int param.data_center_id - 数据中心ID
     * @param   string param.keywords - 关键字搜索
     * @return  int list[].id - 产品ID
     * @return  int list[].name - 产品ID
     * @return  int count - 总条数
     */
    public function enableVpcHost($param): array
    {
        $result = [
            'list'  => [],
            'count' => 0,
        ];

        $clientId = get_client_id();

        if(empty($clientId) || empty($param['data_center_id'])){
            return $result;
        }
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');

        $where = [];
        $where[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', 'IN', ['Active','Grace']];
        $where[] = ['h.is_delete', '=', 0];
        $where[] = ['hl.vpc_network_id', '>', 0];
        if(isset($param['keywords']) && $param['keywords'] !== ''){
            $where[] = ['h.name', 'LIKE', '%'.$param['keywords'].'%'];
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(empty($param['product_id'])){
            $dataCenter = DataCenterModel::find($param['data_center_id']);
            if(empty($dataCenter)){
                return $result;
            }
            $param['product_id'] = $dataCenter['product_id'];
        }
        $groupId = 0;
        if($this->supportDataCenterMap()){
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $groupId = $MfCloudDataCenterMapGroupLinkModel->getGroupId($param['product_id'], $param['data_center_id']);
        }
        // var_dump($groupId);
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }
        // 有组,需要获取组中所有的主机
        if(!empty($groupId)){
            $where[] = ['g.mf_cloud_data_center_map_group_id', '=', $groupId];

            // 是否支持
            $list = HostLinkModel::alias('hl')
                ->field('h.id,h.name')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('mf_cloud_data_center_map_group_link g', 'h.product_id=g.product_id AND hl.data_center_id=g.data_center_id')
                ->where($where);

            $count = HostLinkModel::alias('hl')
                    ->join('host h', 'hl.host_id=h.id')
                    ->leftJoin('mf_cloud_data_center_map_group_link g', 'h.product_id=g.product_id AND hl.data_center_id=g.data_center_id')
                    ->where($where)
                    ->count();

            hook('mf_cloud_enable_vpc_host', ['list'=>$list, 'count'=>&$count, 'where'=>$where]);

            $list = $list
                ->select()
                ->toArray();

            $result['list'] = $list;
            $result['count'] = $count;
        }else{
            if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
                $where[] = ['hl.data_center_id', '=', $param['data_center_id']];
            }

            $list = $this
                ->alias('hl')
                ->field('h.id,h.name')
                ->join('host h', 'hl.host_id=h.id')
                ->where($where)
                ->select()
                ->toArray();

            $count = $this
                    ->alias('hl')
                    ->join('host h', 'hl.host_id=h.id')
                    ->where($where)
                    ->count();
        }
        $result['list'] = $list;
        $result['count'] = $count;

        return $result;
    }

    /**
     * @时间 2024-08-16
     * @title 获取VPC已关联产品列表
     * @desc  获取VPC已关联产品列表
     * @author hh
     * @param   array vpcNetworkId - VPC网络ID require
     * @return  array
     */
    public function vpcHostList(array $vpcNetworkId): array
    {
        $host = HostLinkModel::alias('hl')
                ->field('h.id,h.name,hl.vpc_network_id,hi.dedicate_ip,hi.assign_ip')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('host_ip hi', 'h.id=hi.host_id')
                ->whereIn('hl.vpc_network_id', $vpcNetworkId)
                ->where('h.status', 'IN', ['Active','Suspended','Grace'])
                ->where('h.is_delete', 0)
                ->select()
                ->toArray();
        
        $result = hook('mf_cloud_vpc_network_list_host', ['id'=>$vpcNetworkId ]);
        foreach($result as $v){
            if(is_array($v) && !empty($v)){
                $host = array_merge($host, $v);
            }
        }

        $hostArr = [];
        foreach($host as $v){
            $hostArr[ $v['vpc_network_id'] ][] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'dedicate_ip' => $v['dedicate_ip'],
                'assign_ip' => $v['assign_ip'],
            ];
        }
        return $hostArr;
    }

}
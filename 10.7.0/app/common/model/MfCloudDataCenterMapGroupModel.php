<?php

namespace app\common\model;

use think\Model;
use think\facade\Db;
// use app\common\model\ServerModel;
// use app\common\model\ProductModel;
// use app\common\model\ProductGroupModel;

use server\mf_cloud\model\DataCenterModel AS DC1;
use server\mf_cloud_mysql\model\DataCenterModel AS DC2;
use server\mf_cloud_ip\model\DataCenterModel AS DC3;

/**
 * @title 魔方云区域组模型
 * @desc  魔方云区域组模型
 * @use app\common\model\MfCloudDataCenterMapGroupModel
 */
class MfCloudDataCenterMapGroupModel extends Model
{
    protected $name = 'mf_cloud_data_center_map_group';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'name'              => 'string',
        'description'       => 'string',
        'supplier_id'       => 'int',
        'upstream_id'       => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    /**
     * 时间 2025-11-17
     * @title 魔方云区域组列表
     * @desc  魔方云区域组列表
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.page - 页数 require
     * @param   int param.limit - 每页条数 require
     * @param   string param.name - 搜索:名称
     * @param   string param.keywords - 搜索:描述/商品名称/区域名称
     * @param   string param.type - 类型(local=本地,upstream=代理)
     */
    public function groupList($param): array
    {
        $where = [];
        
        // 名称搜索
        if (!empty($param['name']) && is_string($param['name'])) {
            $where[] = ['g.name', 'like', '%' . $param['name'] . '%'];
        }

        // 只返回本地的
        if(!empty($param['type']) && $param['type'] == 'upstream'){
            $where[] = ['g.supplier_id', '>', 0];
        }else{
            $where[] = ['g.supplier_id', '=', 0];
        }

        // 获取基础列表
        $query = $this->alias('g');

        $module = $this->getEnableModel();
        $whereOr = [];
        // 如果有关键词搜索，需要关联相关表
        if (!empty($param['keywords']) && is_string($param['keywords'])) {
            $keywords = '%' . $param['keywords'] . '%';
            $query = $query
                ->leftJoin('mf_cloud_data_center_map_group_link gl', 'g.id = gl.mf_cloud_data_center_map_group_id')
                ->leftJoin('product p', 'gl.product_id = p.id');

            $whereOr = [
                ['g.description', 'like', $keywords],
                ['p.name', 'like', $keywords],
            ];
            if(in_array('mf_cloud', $module)){
                $query->leftJoin('module_mf_cloud_data_center dc1', 'p.id=dc1.product_id AND gl.data_center_id=dc1.id');
                $whereOr[] = ['dc1.city', 'like', $keywords];
                $whereOr[] = ['dc1.area', 'like', $keywords];
            }
            if(in_array('mf_cloud_mysql', $module)){
                $query->leftJoin('module_mf_cloud_mysql_data_center dc2', 'p.id=dc2.product_id AND gl.data_center_id=dc2.id');
                $whereOr[] = ['dc2.city', 'like', $keywords];
                $whereOr[] = ['dc2.area', 'like', $keywords];
            }
            if(in_array('mf_cloud_ip', $module)){
                $query->leftJoin('module_mf_cloud_ip_data_center dc3', 'p.id=dc3.product_id AND gl.data_center_id=dc3.id');
                $whereOr[] = ['dc3.city', 'like', $keywords];
                $whereOr[] = ['dc3.area', 'like', $keywords];
            }

            $query->where(function($query) use ($whereOr) {
                $query->whereOr($whereOr);
            })
            ->group('g.id');
        }

        $list = $query
                ->field('g.id,g.name,g.description')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order('g.id', 'desc')
                ->select()
                ->toArray();

        // 计算总数时使用相同的查询条件
        $countQuery = $this->alias('g');
        if (!empty($param['keywords'])) {
            $keywords = '%' . $param['keywords'] . '%';
            $countQuery = $countQuery
                ->leftJoin('mf_cloud_data_center_map_group_link gl', 'g.id = gl.mf_cloud_data_center_map_group_id')
                ->leftJoin('product p', 'gl.product_id = p.id');

            if(in_array('mf_cloud', $module)){
                $countQuery->leftJoin('module_mf_cloud_data_center dc1', 'p.id=dc1.product_id AND gl.data_center_id=dc1.id');
            }
            if(in_array('mf_cloud_mysql', $module)){
                $countQuery->leftJoin('module_mf_cloud_mysql_data_center dc2', 'p.id=dc2.product_id AND gl.data_center_id=dc2.id');
            }
            if(in_array('mf_cloud_ip', $module)){
                $countQuery->leftJoin('module_mf_cloud_ip_data_center dc3', 'p.id=dc3.product_id AND gl.data_center_id=dc3.id');
            }

            $countQuery->where(function($query) use ($whereOr) {
                $query->whereOr($whereOr);
            })
            ->group('g.id');
        }
        
        $count = $countQuery->where($where)->count();

        // 如果有数据，获取关联的商品和数据中心信息
        if (!empty($list)) {
            $groupIds = array_column($list, 'id');
            $this->attachProductAndDataCenter($list, $groupIds);
        }
        
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2025-11-17
     * @title 为区域组附加商品和数据中心信息
     * @desc  为区域组附加商品和数据中心信息
     * @author hh
     * @version v1
     * @param   array &$list - 区域组列表
     * @param   array $groupIds - 区域组ID数组
     */
    private function attachProductAndDataCenter(&$list, $groupIds): void
    {
        $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
        $ProductModel = new ProductModel();
        
        // 获取所有关联数据
        $links = $GroupLinkModel
            ->alias('gl')
            ->field('gl.mf_cloud_data_center_map_group_id,gl.product_id,gl.data_center_id,p.name as product_name,s.module')
            ->leftJoin('product p', 'gl.product_id = p.id')
            ->leftJoin('server s', 'p.type=\'server\' AND p.rel_id=s.id')
            ->where('gl.mf_cloud_data_center_map_group_id', 'in', $groupIds)
            ->select()
            ->toArray();
        
        // 按模块分组获取数据中心信息
        $dataCenterInfo = $this->getDataCenterInfo($links);
        
        // 组织数据结构
        $groupProducts = [];
        foreach ($links as $link) {
            $groupId = $link['mf_cloud_data_center_map_group_id'];
            $productId = $link['product_id'];
            $dataCenterId = $link['data_center_id'];
            $module = $link['module'];
            
            // 初始化商品信息
            if (!isset($groupProducts[$groupId][$productId])) {
                $groupProducts[$groupId][$productId] = [
                    'id' => $productId,
                    'name' => $link['product_name'],
                    'data_center' => []
                ];
            }
            
            // 添加数据中心信息
            if (isset($dataCenterInfo[$module][$dataCenterId])) {
                $groupProducts[$groupId][$productId]['data_center'][] = [
                    'id' => $dataCenterId,
                    'name' => $dataCenterInfo[$module][$dataCenterId]
                ];
            }
        }
        
        // 将商品信息附加到列表中
        foreach ($list as &$item) {
            $item['product'] = isset($groupProducts[$item['id']]) 
                ? array_values($groupProducts[$item['id']]) 
                : [];
        }
    }

    /**
     * 时间 2025-11-17
     * @title 获取数据中心信息
     * @desc  获取数据中心信息
     * @author hh
     * @version v1
     * @param   array $links - 关联数据
     * @return  array
     */
    private function getDataCenterInfo($links): array
    {
        $dataCenterInfo = [];
        $moduleDataCenters = [];
        
        // 按模块分组数据中心ID
        foreach ($links as $link) {
            $module = $link['module'];
            $dataCenterId = $link['data_center_id'];
            if (!in_array($dataCenterId, $moduleDataCenters[$module] ?? [])) {
                $moduleDataCenters[$module][] = $dataCenterId;
            }
        }
        
        // 获取各模块的数据中心信息
        foreach ($moduleDataCenters as $module => $dcIds) {
            // $result = hook('mf_cloud_data_center_info', ['module'=>$module, 'id'=>$dcIds ]);
            if(!in_array($module, ['mf_cloud','mf_cloud_mysql','mf_cloud_ip'])){
                continue;
            }
            if($module == 'mf_cloud'){
                $DC = new DC1();
            }else if($module == 'mf_cloud_mysql'){
                $DC = new DC2();
            }else if($module == 'mf_cloud_ip'){
                $DC = new DC3();
            }
            $dcList = $DC
                ->alias('dc')
                ->field('dc.id,c.name_zh,dc.city,dc.area')
                ->leftJoin('country c', 'dc.country_id = c.id')
                ->where('dc.id', 'in', $dcIds)
                ->select()
                ->toArray();
            foreach ($dcList as &$dc) {
                $dc['name'] = $dc['name_zh'].'-'.$dc['city'].'-'.$dc['area'];
            }
            $dcList = array_column($dcList, 'name', 'id');
            $dataCenterInfo[$module] = $dcList;
        }
        return $dataCenterInfo;
    }

    /**
     * 时间 2025-11-17
     * @title 创建魔方云区域组
     * @desc  创建魔方云区域组
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   string param.name - 区域组名称 require
     * @param   string param.description - 区域组描述
     * @param   array param.data_center - 数据中心配置 require
     * @return  array
     */
    public function groupCreate($param): array
    {
        // 验证商品和数据中心
        $validateResult = $this->validateProductAndDataCenter($param['data_center']);
        if ($validateResult['status'] != 200) {
            return $validateResult;
        }

        Db::startTrans();
        try {
            // 创建区域组
            $groupData = [
                'name' => $param['name'],
                'description' => $param['description'] ?? '',
                'create_time' => time(),
            ];
            
            $groupId = $this->insertGetId($groupData);
            if (!$groupId) {
                throw new \Exception( lang('create_failed') );
            }

            // 批量插入关联数据
            $linkData = [];
            foreach ($param['data_center'] as $item) {
                foreach ($item['data_center_id'] as $dcId) {
                    $linkData[] = [
                        'mf_cloud_data_center_map_group_id' => $groupId,
                        'product_id' => $item['product_id'],
                        'data_center_id' => $dcId,
                    ];
                }
            }

            if (!empty($linkData)) {
                $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
                $GroupLinkModel->insertAll($linkData);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => lang($e->getMessage())];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => (int)$groupId]];
    }

    /**
     * 时间 2024-11-17
     * @title 修改VPC区域组
     * @desc  修改VPC区域组
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 区域组ID require
     * @param   string param.name - 区域组名称 require
     * @param   string param.description - 区域组描述
     * @param   array param.data_center - 数据中心配置 require
     * @return  array
     */
    public function groupUpdate($param): array
    {
        $groupId = $param['id'];
        
        // 检查区域组是否存在
        $group = $this->find($groupId);
        if (!$group || $group['supplier_id'] > 0 ) {
            return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_not_found')];
        }

        // 验证商品和数据中心
        $validateResult = $this->validateProductAndDataCenter($param['data_center'], $groupId);
        if ($validateResult['status'] != 200) {
            return $validateResult;
        }

        Db::startTrans();
        try {
            // 更新区域组基本信息
            $groupData = [
                'name' => $param['name'],
                'description' => $param['description'] ?? '',
                'update_time' => time(),
            ];
            
            $updateResult = $this->where('id', $groupId)->update($groupData);
            if ($updateResult === false) {
                throw new \Exception( lang('update_failed') );
            }

            // 删除原有关联数据
            $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $GroupLinkModel->where('mf_cloud_data_center_map_group_id', $groupId)->delete();

            // 批量插入新的关联数据
            $linkData = [];
            foreach ($param['data_center'] as $item) {
                foreach ($item['data_center_id'] as $dcId) {
                    $linkData[] = [
                        'mf_cloud_data_center_map_group_id' => $groupId,
                        'product_id' => $item['product_id'],
                        'data_center_id' => $dcId,
                    ];
                }
            }

            if (!empty($linkData)) {
                $GroupLinkModel->insertAll($linkData);
            }

            Db::commit();
            return ['status' => 200, 'msg' => lang('success_message')];
            
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-11-17
     * @title 删除VPC区域组
     * @desc  删除VPC区域组
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 区域组ID require
     * @return  array
     */
    public function groupDelete($param): array
    {
        $groupId = $param['id'];
        
        // 检查区域组是否存在
        $group = $this->find($groupId);
        if (!$group || $group['supplier_id'] > 0 ) {
            return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_not_found')];
        }

        Db::startTrans();
        try {
            // 删除关联数据
            $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $GroupLinkModel->where('mf_cloud_data_center_map_group_id', $groupId)->delete();

            // 删除区域组
            $deleteResult = $this->where('id', $groupId)->delete();
            if ($deleteResult === false) {
                throw new \Exception( lang('delete_failed') );
            }

            Db::commit();
            return ['status' => 200, 'msg' => lang('success_message')];
            
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-03-08
     * @title 获取可用商品
     * @desc  获取可用商品
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.page - 页数 require
     * @param   int param.limit - 每页条数 require
     */
    public function getEnableProduct($param): array
    {
        $module = $this->getEnableModel();
        if(empty($module)){
            return ['list'=>[]];
        }

        $where = function ($query) use($param, $module) {
            //$query->where('p.hidden', 0);
            if(!empty($param['module'])){
                $query->whereIn('s.module', $param['module']);
            }
            $query->whereRaw('up.id IS NULL');
        };

        $ProductGroupModel = new ProductGroupModel();
        $firstGroup = $ProductGroupModel->productGroupFirstList();
        $firstGroup = $firstGroup['list'];

        $secondGroup = $ProductGroupModel->productGroupSecondList([]);
        $secondGroup = $secondGroup['list'];

        $products = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('upstream_product up', 'p.id=up.product_id')
            ->where($where)
            ->order('p.order','desc')
            ->select()
            ->toArray();
        $productArr = [];
        foreach ($products as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $productArr[$value['id']]];
            }
        }
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $secondGroupArr[$value['id']]];
            }
        }
        return ['list'=>$list];
    }

    /**
     * 时间 2024-03-08
     * @title 获取可用商品
     * @desc  获取可用商品
     * @author hh
     * @version v1
     * @return  array - - 支持的模型
     */
    public function getEnableModel(): array
    {
        $module = [
            'mf_cloud',
            'mf_cloud_ip',
            'mf_cloud_mysql',
        ];

        $ServerModel = new ServerModel();
        $module = $ServerModel->where('module', 'IN', $module)->column('module');
        $module = array_values(array_unique($module));
        return $module;
    }

    /**
     * 时间 2024-11-17
     * @title 验证商品和数据中心
     * @desc  验证商品和数据中心的有效性
     * @author hh
     * @version v1
     * @param   array dataCenterConfig - 数据中心配置
     * @return  array
     */
    public function validateProductAndDataCenter($dataCenterConfig, $groupId = 0): array
    {
        $enableModules = $this->getEnableModel();
        if (empty($enableModules)) {
            return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_no_enable_module')];
        }

        $ProductModel = new ProductModel();
        $ServerModel = new ServerModel();

        // 批量获取所有商品ID
        $productIds = array_column($dataCenterConfig, 'product_id');
        $productIds = array_unique($productIds);

        // 验证商品是否存在且属于支持的模块
        $products = $ProductModel->alias('p')
            ->field('p.id,p.rel_id,s.module')
            ->leftJoin('server s', 'p.type=\'server\' AND p.rel_id=s.id')
            ->where('p.id', 'in', $productIds)
            ->whereIn('s.module', $enableModules)
            ->select()
            ->toArray();

        if (count($products) !== count($productIds)) {
            return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_product_id_error')];
        }

        // 按模块分组验证数据中心
        $moduleDataCenters = [];
        foreach ($products as $product) {
            $moduleDataCenters[$product['module']][] = $product['id'];
        }

        // 验证每个模块的数据中心
        foreach ($dataCenterConfig as $item) {
            $productId = $item['product_id'];
            $dataCenterIds = $item['data_center_id'];

            // 找到商品对应的模块
            $productModule = null;
            foreach ($products as $product) {
                if ($product['id'] == $productId) {
                    $productModule = $product['module'];
                    break;
                }
            }

            if (!$productModule) {
                return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_product_id_error')];
            }

            // 根据模块验证数据中心
            $validDataCenters = $this->getDataCentersByModule($productModule, $dataCenterIds);
            if (count($validDataCenters) !== count($dataCenterIds)) {
                return ['status' => 400, 'msg' => lang('mf_cloud_data_center_map_group_data_center_id_error')];    
            }

            // 是否添加过了
            $exist = MfCloudDataCenterMapGroupLinkModel::where('product_id', $productId)
                ->where('data_center_id', 'IN', $dataCenterIds)
                ->where('mf_cloud_data_center_map_group_id', '<>', $groupId)
                ->find();
            if(!empty($exist)) {
                return ['status'=>400, 'msg'=>lang('mf_cloud_data_center_map_group_data_center_already_add') ];
            }
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-11-17
     * @title 根据模块获取数据中心
     * @desc  根据模块获取数据中心
     * @author hh
     * @version v1
     * @param   string module - 模块名
     * @param   array dataCenterIds - 数据中心ID数组
     * @return  array
     */
    private function getDataCentersByModule($module, $dataCenterIds): array
    {
        switch ($module) {
            case 'mf_cloud':
                $DC = new DC1();
                break;
            case 'mf_cloud_mysql':
                $DC = new DC2();
                break;
            case 'mf_cloud_ip': 
                $DC = new DC3();
                break;
            default:
                return [];
        }

        return $DC->where('id', 'in', $dataCenterIds)->column('id');
    }

    /**
     * 时间 2024-11-18
     * @title 根据商品ID获取数据中心
     * @desc  根据商品ID获取数据中心
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 商品ID require
     * @return  array
     */
    public function getProductDataCenter($param): array
    {
        $productId = $param['id'];
        
        // 验证商品是否存在
        $ProductModel = new ProductModel();
        $product = $ProductModel->alias('p')
            ->field('p.id,p.type,p.rel_id,s.module')
            ->leftJoin('server s', 'p.type=\'server\' AND p.rel_id=s.id')
            ->where('p.id', $productId)
            ->find();
        
        if (!$product) {
            return ['list'=>[]];
        }
        
        // 验证商品是否属于支持的模块
        $enableModules = $this->getEnableModel();
        if (!in_array($product['module'], $enableModules)) {
            return ['list'=>[]];
        }
        
        // 根据模块获取数据中心
        $module = $product['module'];
        $dataCenterList = [];
        
        switch ($module) {
            case 'mf_cloud':
                $DC = new DC1();
                break;
            case 'mf_cloud_mysql':
                $DC = new DC2();
                break;
            case 'mf_cloud_ip':
                $DC = new DC3();
                break;
            default:
                return ['list'=>[]];
        }
        
        // 获取数据中心列表,按order和id正序排序
        $dataCenterList = $DC
            ->alias('dc')
            ->field('dc.id,dc.order,c.name_zh,dc.city,dc.area')
            ->leftJoin('country c', 'dc.country_id = c.id')
            ->where('dc.product_id', $productId)
            ->order('dc.order', 'asc')
            ->order('dc.id', 'asc')
            ->select()
            ->toArray();
        
        // 格式化数据中心名称
        $list = [];
        foreach ($dataCenterList as $dc) {
            $list[] = [
                'id' => $dc['id'],
                'name' => $dc['name_zh'] . '-' . $dc['city'] . '-' . $dc['area']
            ];
        }
        
        return ['list'=>$list];
    }


    /**
     * 时间 2025-12-02
     * @title 获取商品的区域组列表（用于API返回，包含数据中心关联）
     * @desc  获取商品的区域组列表及其关联的数据中心信息
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.product_id - 商品ID require
     * @return  array
     */
    public function getProductDataCenterMapGroupWithLinks($param): array
    {
        $productId = $param['product_id'];
        
        // 验证商品是否存在且属于魔方云相关模块
        // $ProductModel = new ProductModel();
        // $product = $ProductModel->alias('p')
        //     ->field('p.id,s.module')
        //     ->leftJoin('server s', 'p.type=\'server\' AND p.rel_id=s.id')
        //     ->where('p.id', $productId)
        //     ->find();
        
        // if (!$product) {
        //     return ['list' => []];
        // }
        
        // // 检查是否为支持的模块
        // $enableModules = $this->getEnableModel();
        // if (!in_array($product['module'], $enableModules)) {
        //     return ['list' => []];
        // }
        
        // 获取该商品关联的区域组
        $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
        $groupIds = $GroupLinkModel
            ->where('product_id', $productId)
            ->column('mf_cloud_data_center_map_group_id');
        
        if (empty($groupIds)) {
            return ['list' => []];
        }
        
        $groupIds = array_unique($groupIds);
        
        // 获取区域组信息
        $list = $this->field('id,name,description,upstream_id')
            ->where('id', 'in', $groupIds)
            ->order('id', 'desc')
            ->select()
            ->toArray();
        
        // 获取每个区域组的数据中心关联
        foreach ($list as &$group) {
            $links = $GroupLinkModel
                ->field('product_id,data_center_id')
                ->where('mf_cloud_data_center_map_group_id', $group['id'])
                ->select()
                ->toArray();
            
            // 按商品分组数据中心
            $dataCenterByProduct = [];
            foreach ($links as $link) {
                if (!isset($dataCenterByProduct[$link['product_id']])) {
                    $dataCenterByProduct[$link['product_id']] = [];
                }
                $dataCenterByProduct[$link['product_id']][] = $link['data_center_id'];
            }
            
            // 转换为数组格式
            $group['data_center'] = [];
            foreach ($dataCenterByProduct as $pid => $dcIds) {
                $group['data_center'][] = [
                    'product_id' => $pid,
                    'data_center_id' => $dcIds,
                ];
            }
        }
        
        return ['list' => $list];
    }

    /**
     * 时间 2025-12-02
     * @title 同步上游区域组
     * @desc  从上游接口同步区域组信息及数据中心关联到本地
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.supplier_id - 供应商ID require
     * @param   int param.product_id - 本地商品ID require
     * @return  array
     */
    public function syncDataCenterMapGroup($param): array
    {
        $supplierId = $param['supplier_id'];
        $productId = $param['product_id'];
        
        // 获取供应商信息
        $supplier = SupplierModel::find($supplierId);
        if (empty($supplier)) {
            return ['status' => 400, 'msg' => lang('supplier_is_not_exist')];
        }
        
        // 获取上游商品信息
        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $productId)
            ->where('supplier_id', $supplierId)
            ->where('mode', 'sync')
            ->find();
        
        if (empty($upstreamProduct)) {
            return ['status' => 400, 'msg' => lang('upstream_product_is_not_exist')];
        }
        
        // 调用上游接口获取商品详情（包含区域组信息）
        $UpstreamLogic = new \app\common\logic\UpstreamLogic();
        $res = $UpstreamLogic->upstreamProductDetail([
            'type' => $supplier['type'],
            'url' => $supplier['url'],
            'id' => $upstreamProduct['upstream_product_id'],
            'supplier_id' => $supplierId,
        ]);

        if (empty($res['data'])) {
            return ['status' => 400, 'msg' => lang('upstream_product_is_not_exist')];
        }
        
        // 获取区域组数据（上游返回的是上游本地的ID）
        $upstreamGroups = $res['data_center_map_group'] ?? [];
        // if (empty($upstreamGroups)) {
        //     return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['synced' => 0]];
        // }
        
        Db::startTrans();
        try {
            $syncedCount = 0;
            $GroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            
            if(!empty($upstreamGroups)){
                foreach ($upstreamGroups as $upstreamGroup) {
                    $upstreamId = $upstreamGroup['id'];
                    $name = $upstreamGroup['name'];
                    $description = $upstreamGroup['description'] ?? '';
                    $dataCenterLinks = $upstreamGroup['data_center'] ?? [];
                    
                    // 同步数据中心关联（上游返回的是上游本地的商品ID和数据中心ID）
                    if (!empty($dataCenterLinks)) {
                        $linkData = [];
                        foreach ($dataCenterLinks as $link) {
                            $upstreamProductId = $link['product_id'];
                            $upstreamDataCenterIds = $link['data_center_id'];

                            // 只同步当前商品
                            if($upstreamProductId != $upstreamProduct['upstream_product_id']){
                                continue;
                            }
                            
                            // 通过上游商品ID找到本地商品ID
                            // $localProductId = $this->getLocalProductId($upstreamProductId, $supplierId);
                            // if (!$localProductId) {
                            //     continue;
                            // }
                            $localProductId = $productId;
                            
                            // 通过上游数据中心ID找到本地数据中心ID
                            $localDcIds = $this->getLocalDataCenterIdByUpstreamId($upstreamDataCenterIds, $localProductId);
                            if ($localDcIds) {
                                // 检查是否已存在（通过 supplier_id + upstream_id 唯一确定）
                                $existGroup = $this->where('supplier_id', $supplierId)
                                    ->where('upstream_id', $upstreamId)
                                    ->find();
                                
                                if ($existGroup) {
                                    // 更新现有区域组
                                    $groupId = $existGroup['id'];
                                    $this->where('id', $groupId)->update([
                                        'name' => $name,
                                        'description' => $description,
                                        'update_time' => time(),
                                    ]);
                                } else {
                                    // 创建新区域组
                                    $groupId = $this->insertGetId([
                                        'name' => $name,
                                        'description' => $description,
                                        'supplier_id' => $supplierId,
                                        'upstream_id' => $upstreamId,
                                        'create_time' => time(),
                                    ]);
                                    $syncedCount++;
                                }

                                // 删除旧的数据中心关联
                                $GroupLinkModel->where('mf_cloud_data_center_map_group_id', $groupId)->where('product_id', $productId)->delete();

                                foreach ($localDcIds as $localDcId) {
                                    $linkData[] = [
                                        'mf_cloud_data_center_map_group_id' => $groupId,
                                        'product_id' => $localProductId,
                                        'data_center_id' => $localDcId,
                                    ];
                                }
                            }
                        }
                        
                        if (!empty($linkData)) {
                            $GroupLinkModel->insertAll($linkData);
                        }
                    }
                }
            }else{
                // 删除旧的数据中心关联
                $GroupLinkModel->where('product_id', $productId)->delete();
            }

            // 删除当前供应商下没有数据中心关联的空分组
            $deletedCount = 0;
            $allGroups = $this->where('supplier_id', $supplierId)->select();
            foreach ($allGroups as $group) {
                $linkCount = $GroupLinkModel->where('mf_cloud_data_center_map_group_id', $group['id'])->count();
                if ($linkCount == 0) {
                    $this->where('id', $group['id'])->delete();
                    $deletedCount++;
                }
            }

            Db::commit();
            return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['synced' => $syncedCount, 'deleted' => $deletedCount]];
            
        } catch (\Exception $e) {
            Db::rollback();
            return ['status' => 400, 'msg' => lang('sync_failed') . ': ' . $e->getMessage()];
        }
    }

    /**
     * 时间 2025-12-02
     * @title 获取本地商品ID
     * @desc  根据上游商品ID和供应商ID获取本地商品ID
     * @author hh
     * @version v1
     * @param   int upstreamProductId - 上游商品ID
     * @param   int supplierId - 供应商ID
     * @return  int|null
     */
    private function getLocalProductId($upstreamProductId, $supplierId)
    {
        $UpstreamProductModel = new UpstreamProductModel();
        $localProduct = $UpstreamProductModel
            ->where('upstream_product_id', $upstreamProductId)
            ->where('supplier_id', $supplierId)
            ->find();
        
        return $localProduct ? $localProduct['product_id'] : null;
    }

    /**
     * 时间 2025-12-02
     * @title 根据上游数据中心ID获取本地数据中心ID
     * @desc  根据上游数据中心ID和本地商品ID获取本地数据中心ID
     * @author hh
     * @version v1
     * @param   int upstreamDataCenterId - 上游数据中心ID
     * @param   int localProductId - 本地商品ID
     * @return  int|null
     */
    private function getLocalDataCenterIdByUpstreamId($upstreamDataCenterId, $localProductId)
    {
        // 获取商品模块
        $module = UpstreamProductModel::where('product_id', $localProductId)->value('res_module');
        // $ProductModel = new ProductModel();
        // $product = $ProductModel->alias('p')
        //     ->field('p.id,s.module')
        //     ->leftJoin('server s', 'p.type=\'server\' AND p.rel_id=s.id')
        //     ->where('p.id', $localProductId)
        //     ->find();
        
        // if (!$product) {
        //     return null;
        // }
        
        // 根据模块获取对应的数据中心模型
        switch ($module) {
            case 'mf_cloud':
                $DC = new DC1();
                break;
            case 'mf_cloud_mysql':
                $DC = new DC2();
                break;
            case 'mf_cloud_ip':
                $DC = new DC3();
                break;
            default:
                return null;
        }
        
        // 通过 upstream_id 查找本地数据中心ID
        $localDcs = $DC
            ->where('product_id', $localProductId)
            ->where('upstream_id', 'IN', $upstreamDataCenterId)
            ->column('id');
        
        return $localDcs;
    }

}
<?php 
namespace server\idcsmart_common\model;

use think\Model;

/**
 * 级联配置项模型
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeItemModel extends Model
{
    protected $name = 'module_idcsmart_common_cascade_item';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'cascade_group_id'  => 'int',
        'parent_item_id'    => 'int',
        'item_name'         => 'string',
        'fee_type'          => 'string',
        'is_leaf'           => 'int',
        'order'             => 'int',
        'hidden'            => 'int',
        'upstream_id'       => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    /**
     * 检查商品是否为下游代理商品
     * @param int $productId
     * @return bool
     */
    private function isDownstreamProduct($productId)
    {
        $UpstreamProductModel = new \app\common\model\UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $productId)->find();
        return !empty($upstreamProduct);
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联项详情
     * @desc 获取级联项详情
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @return object item - 级联项信息
     */
    public function indexCascadeItem($param)
    {
        $id = $param['id'] ?? 0;

        $item = $this->find($id);
        if (empty($item)) {
            return ['status' => 400, 'msg' => lang_plugins('idcsmart_common_cascade_item_not_exist')];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'item' => $item
            ]
        ];
    }

    /**
     * 时间 2024-12-20
     * @title 创建级联项
     * @desc 创建新的级联项，自动确定所属级联组
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID require
     * @param string item_name - 级联项名称 require
     * @param int parent_item_id - 父级联项ID（可选，默认0表示顶级）
     * @param int prev_item_id - 排在该级联项之后（可选，不传则追加到末尾，必须与新建项同父级）
     * @param int order - 排序（可选）
     * @param int hidden - 是否隐藏（可选，0或1）
     * @return int id - 级联项ID
     */
    public function createCascadeItem($param)
    {
        $this->startTrans();

        try {
            $configoptionId = $param['configoption_id'] ?? 0;
            
            // 检查是否为下游代理商品
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $configoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);
            if ($configoption && $this->isDownstreamProduct($configoption['product_id'])) {
                throw new \Exception(lang_plugins('idcsmart_common_downstream_cannot_create_cascade_item'));
            }
            
            $itemName = $param['item_name'] ?? '';
            $parentItemId = $param['parent_item_id'] ?? 0;

            if (empty($itemName)) {
                throw new \Exception(lang_plugins('param_error'));
            }

            if (empty($configoptionId)) {
                throw new \Exception(lang_plugins('param_error'));
            }

            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            
            // 确定级联组
            $cascadeGroupId = 0;
            $group = null;
            
            if ($parentItemId > 0) {
                // 有父级联项，创建为子级联项
                $parentItem = $this->find($parentItemId);
                if (empty($parentItem)) {
                    throw new \Exception(lang_plugins('idcsmart_common_cascade_parent_item_not_exist'));
                }

                // 获取父级联项的级联组
                $parentGroup = $IdcsmartCommonCascadeGroupModel->find($parentItem['cascade_group_id']);
                if (empty($parentGroup)) {
                    throw new \Exception(lang_plugins('idcsmart_common_cascade_group_not_exist'));
                }

                // 更新父项的末端状态为非末端
                $parentItem->save([
                    'is_leaf' => 0,
                    'update_time' => time(),
                ]);

                // 删除父项的价格配置
                $this->deleteCascadeItemPrice($parentItemId);

                // 自动创建或获取下级级联组
                $nextLevel = $parentGroup['level'] + 1;
                $nextGroup = $IdcsmartCommonCascadeGroupModel->getGroupByLevel($configoptionId, $nextLevel);
                
                if (empty($nextGroup)) {
                    // 创建下级级联组
                    $result = $IdcsmartCommonCascadeGroupModel->createCascadeGroup([
                        'configoption_id' => $configoptionId,
                        'group_name' => '级联组 ' . $nextLevel,
                        'level' => $nextLevel,
                    ]);
                    
                    if ($result['status'] != 200) {
                        throw new \Exception($result['msg']);
                    }
                    
                    $cascadeGroupId = $result['data']['id'];
                    $group = $IdcsmartCommonCascadeGroupModel->find($cascadeGroupId);
                } else {
                    $cascadeGroupId = $nextGroup['id'];
                    $group = $nextGroup;
                }
            } else {
                // 没有父级联项，创建为顶级级联项，归属于1级级联组
                $group = $IdcsmartCommonCascadeGroupModel->getGroupByLevel($configoptionId, 1);
                
                if (empty($group)) {
                    throw new \Exception(lang_plugins('idcsmart_common_cascade_group_not_exist'));
                }
                
                $cascadeGroupId = $group['id'];
            }

            // 验证级联项名称在同父级联项内的唯一性
            $existItem = $this->where('cascade_group_id', $cascadeGroupId)
                ->where('parent_item_id', $parentItemId)
                ->where('item_name', $itemName)
                ->find();
            if (!empty($existItem)) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_item_name_duplicate'));
            }

            // 确定排序值
            $prevItemId = $param['prev_item_id'] ?? 0;
            if ($prevItemId > 0) {
                // 验证 prev_item_id 对应项存在且与新建项同父级
                $prevItem = $this->where('id', $prevItemId)
                    ->where('cascade_group_id', $cascadeGroupId)
                    ->where('parent_item_id', $parentItemId)
                    ->find();
                if (empty($prevItem)) {
                    throw new \Exception(lang_plugins('param_error'));
                }
                $order = $prevItem['order'] + 1;
                // 将同父级、order >= 新 order 的已有项全部后移一位
                $this->where('cascade_group_id', $cascadeGroupId)
                    ->where('parent_item_id', $parentItemId)
                    ->where('order', '>=', $order)
                    ->inc('order', 1)
                    ->update();
            } else {
                $maxOrder = $this->where('cascade_group_id', $cascadeGroupId)
                    ->where('parent_item_id', $parentItemId)
                    ->max('order');
                $order = isset($param['order']) ? $param['order'] : ($maxOrder + 1);
            }

            $time = time();
            $itemId = $this->insertGetId([
                'cascade_group_id' => $cascadeGroupId,
                'parent_item_id' => $parentItemId,
                'item_name' => $itemName,
                'is_leaf' => 1, // 默认为末端
                'order' => $order,
                'hidden' => $param['hidden'] ?? 0,
                'create_time' => $time,
                'update_time' => $time,
            ]);

            $this->commit();

            return [
                'status' => 200,
                'msg' => lang_plugins('create_success'),
                'data' => [
                    'id' => $itemId
                ]
            ];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 更新级联项
     * @desc 更新级联项信息
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @param string item_name - 级联项名称
     * @param int order - 排序
     * @param int hidden - 是否隐藏（0或1）
     * @return bool
     */
    public function updateCascadeItem($param)
    {
        $this->startTrans();

        try {
            $id = $param['id'] ?? 0;

            $item = $this->find($id);
            if (empty($item)) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_item_not_exist'));
            }

            $updateData = ['update_time' => time()];

            // 如果更新名称，验证唯一性
            if (isset($param['item_name']) && !empty($param['item_name'])) {
                $existItem = $this->where('cascade_group_id', $item['cascade_group_id'])
                    ->where('parent_item_id', $item['parent_item_id'])
                    ->where('item_name', $param['item_name'])
                    ->where('id', '<>', $id)
                    ->find();
                if (!empty($existItem)) {
                    throw new \Exception(lang_plugins('idcsmart_common_cascade_item_name_duplicate'));
                }
                $updateData['item_name'] = $param['item_name'];
            }

            if (isset($param['order'])) {
                $updateData['order'] = $param['order'];
            }

            if (isset($param['hidden'])) {
                $updateData['hidden'] = $param['hidden'];
            }

            $item->save($updateData);

            $this->commit();

            return ['status' => 200, 'msg' => lang_plugins('update_success')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联项
     * @desc 删除级联项，如果有子级联项则提升到上级
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @param bool promote_children - 是否提升子级联项（默认true）
     * @return bool
     */
    public function deleteCascadeItem($param)
    {
        $this->startTrans();

        try {
            $id = $param['id'] ?? 0;

            $item = $this->find($id);
            if (empty($item)) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_item_not_exist'));
            }

            // 获取配置项ID并检查是否为下游代理商品
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $group = $IdcsmartCommonCascadeGroupModel->find($item['cascade_group_id']);
            $configoptionId = $group['configoption_id'] ?? 0;
            
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $configoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);
            if ($configoption && $this->isDownstreamProduct($configoption['product_id'])) {
                throw new \Exception(lang_plugins('idcsmart_common_downstream_cannot_delete_cascade_item'));
            }

            // 检查该级联项是否已被主机使用
            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();
            $usedCount = $IdcsmartCommonHostConfigoptionModel->where('cascade_item_id', $id)->count();
            if ($usedCount > 0) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_item_in_use'));
            }

            // 获取配置项ID用于后续清理
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $group = $IdcsmartCommonCascadeGroupModel->find($item['cascade_group_id']);
            $configoptionId = $group['configoption_id'] ?? 0;

            // 递归删除所有子级联项(包括子项的子项)
            $childItems = $this->where('parent_item_id', $id)->select();
            foreach ($childItems as $childItem) {
                $this->deleteCascadeItem(['id' => $childItem['id']]);
            }

            // 删除该级联项的所有配置子项和价格信息
            $this->deleteCascadeItemPrice($id);

            // 删除级联项
            $item->delete();

            // 如果有父项,检查父项是否还有其他子项
            if ($item['parent_item_id'] > 0) {
                $siblingCount = $this->where('parent_item_id', $item['parent_item_id'])->count();
                if ($siblingCount == 0) {
                    // 没有其他子项,将父项设为末端
                    $parentItem = $this->find($item['parent_item_id']);
                    if (!empty($parentItem)) {
                        $parentItem->save([
                            'is_leaf' => 1,
                            'update_time' => time(),
                        ]);
                    }
                }
            }

            // 清理空级联组
            if ($configoptionId > 0) {
                $IdcsmartCommonCascadeLogic = new \server\idcsmart_common\logic\IdcsmartCommonCascadeLogic();
                $IdcsmartCommonCascadeLogic->cleanEmptyGroups($configoptionId);
            }

            $this->commit();

            return ['status' => 200, 'msg' => lang_plugins('delete_success')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联项的价格配置
     * @desc 删除级联项关联的所有配置子项和价格信息
     * @author theworld
     * @version v1
     * @param int item_id - 级联项ID require
     * @return bool
     */
    public function deleteCascadeItemPrice($itemId)
    {
        try {
            // 查询该级联项的所有配置子项
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel
                ->where('cascade_item_id', $itemId)
                ->select();

            foreach ($configoptionSubs as $sub) {
                // 删除价格
                $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
                $IdcsmartCommonPricingModel->where('type', 'configoption')
                    ->where('rel_id', $sub['id'])
                    ->delete();

                // 删除自定义周期价格
                $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
                $IdcsmartCommonCustomCyclePricingModel->where('rel_id', $sub['id'])
                    ->where('type', 'configoption')
                    ->delete();

                // 删除配置子项
                $sub->delete();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联项的所有子项
     * @desc 获取指定级联项的所有直接子项
     * @author theworld
     * @version v1
     * @param int parent_item_id - 父级联项ID require
     * @return array list - 子项列表
     */
    public function getChildItems($parentItemId)
    {
        return $this->where('parent_item_id', $parentItemId)
            ->order('order', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联项的父项
     * @desc 获取指定级联项的父级联项
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @return object|null parent - 父级联项信息
     */
    public function getParentItem($id)
    {
        $item = $this->find($id);
        if (empty($item) || $item['parent_item_id'] == 0) {
            return null;
        }

        return $this->find($item['parent_item_id']);
    }

    /**
     * 时间 2024-12-20
     * @title 检查是否为末端级联项
     * @desc 检查指定级联项是否为末端（没有子项）
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @return bool
     */
    public function isLeafItem($id)
    {
        $childCount = $this->where('parent_item_id', $id)->count();
        return $childCount == 0;
    }

    /**
     * 时间 2024-12-20
     * @title 更新级联项的末端状态
     * @desc 根据是否有子项自动更新级联项的is_leaf状态
     * @author theworld
     * @version v1
     * @param int id - 级联项ID require
     * @return bool
     */
    public function updateLeafStatus($id)
    {
        try {
            $item = $this->find($id);
            if (empty($item)) {
                return false;
            }

            $isLeaf = $this->isLeafItem($id) ? 1 : 0;
            
            if ($item['is_leaf'] != $isLeaf) {
                $item->save([
                    'is_leaf' => $isLeaf,
                    'update_time' => time(),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 时间 2024-12-20
     * @title 获取所有末端级联项
     * @desc 获取指定级联组或配置项的所有末端级联项
     * @author theworld
     * @version v1
     * @param int cascade_group_id - 级联组ID（可选）
     * @param int configoption_id - 配置项ID（可选）
     * @return array list - 末端级联项列表
     */
    public function getLeafItems($param)
    {
        $where = [['is_leaf', '=', 1]];

        if (isset($param['cascade_group_id']) && $param['cascade_group_id'] > 0) {
            $where[] = ['cascade_group_id', '=', $param['cascade_group_id']];
        }

        if (isset($param['configoption_id']) && $param['configoption_id'] > 0) {
            // 通过级联组关联查询
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $groupIds = $IdcsmartCommonCascadeGroupModel
                ->where('configoption_id', $param['configoption_id'])
                ->column('id');
            
            if (!empty($groupIds)) {
                $where[] = ['cascade_group_id', 'in', $groupIds];
            }
        }

        $list = $this->where($where)
            ->order('cascade_group_id', 'asc')
            ->order('order', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $list;
    }

    /**
     * 获取级联项的完整路径名称
     * @param int $itemId - 级联项ID
     * @param string $separator - 分隔符，默认 ' => '
     * @return string 完整路径，如 级联组1: A => 级联组2: A-1 => 级联组3: A-1-1
     */
    public function getFullName($itemId, $separator = '/')
    {
        $itemId = intval($itemId);
        if ($itemId <= 0) {
            return '';
        }
        
        // 获取当前级联项
        $currentItem = $this->find($itemId);
        if (empty($currentItem)) {
            return '';
        }
        
        // 一次性查询该配置项下的所有级联项和级联组（用于构建路径）
        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
        $group = $IdcsmartCommonCascadeGroupModel->find($currentItem['cascade_group_id']);
        if (empty($group)) {
            return '';
        }
        
        $configoptionId = $group['configoption_id'];
        
        // 查询该配置项下所有级联项（带级联组信息）
        $allItems = $this->alias('i')
            ->field('i.id, i.item_name, i.parent_item_id, i.cascade_group_id, g.group_name')
            ->leftJoin('module_idcsmart_common_cascade_group g', 'i.cascade_group_id = g.id')
            ->where('g.configoption_id', $configoptionId)
            ->select()
            ->toArray();
        
        if (empty($allItems)) {
            return '';
        }
        
        // 构建索引映射
        $itemMap = [];
        foreach ($allItems as $item) {
            $itemMap[$item['id']] = $item;
        }
        
        // 从当前项向上追溯构建路径
        $parts = [];
        $currentId = $itemId;
        
        while ($currentId > 0 && isset($itemMap[$currentId])) {
            $item = $itemMap[$currentId];
            $groupName = $item['group_name'] ?? '';
            $itemName = $item['item_name'] ?? '';
            array_unshift($parts, $groupName . ': ' . $itemName);
            $currentId = intval($item['parent_item_id']);
        }
        
        return implode($separator, $parts);
    }
}

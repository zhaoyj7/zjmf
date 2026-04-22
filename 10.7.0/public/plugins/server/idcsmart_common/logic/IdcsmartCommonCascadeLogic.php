<?php 
namespace server\idcsmart_common\logic;

use server\idcsmart_common\model\IdcsmartCommonCascadeGroupModel;
use server\idcsmart_common\model\IdcsmartCommonCascadeItemModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;

/**
 * 级联配置项业务逻辑类
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeLogic
{
    /**
     * 时间 2024-12-20
     * @title 检查是否为末端级联项
     * @desc 检查级联项是否为末端（没有子项）
     * @author theworld
     * @version v1
     * @param int $itemId - 级联项ID
     * @return bool
     */
    public function isLeafItem($itemId)
    {
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        return $IdcsmartCommonCascadeItemModel->isLeafItem($itemId);
    }

    /**
     * 时间 2024-12-20
     * @title 更新级联项的末端状态
     * @desc 根据是否有子项自动更新级联项的is_leaf状态
     * @author theworld
     * @version v1
     * @param int $itemId - 级联项ID
     * @return bool
     */
    public function updateLeafStatus($itemId)
    {
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        return $IdcsmartCommonCascadeItemModel->updateLeafStatus($itemId);
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联项的所有子项
     * @desc 获取指定级联项的所有直接子项
     * @author theworld
     * @version v1
     * @param int $itemId - 级联项ID
     * @return array
     */
    public function getChildItems($itemId)
    {
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        return $IdcsmartCommonCascadeItemModel->getChildItems($itemId);
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联项的价格配置
     * @desc 删除级联项关联的所有配置子项和价格信息
     * @author theworld
     * @version v1
     * @param int $itemId - 级联项ID
     * @return bool
     */
    public function deleteCascadeItemPrice($itemId)
    {
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        return $IdcsmartCommonCascadeItemModel->deleteCascadeItemPrice($itemId);
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联树形结构
     * @desc 获取完整的级联树形结构，包括所有级联组和级联项
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @return array
     */
    public function getCascadeTree($configoptionId)
    {
        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        // 获取所有级联组
        $groups = $IdcsmartCommonCascadeGroupModel
            ->where('configoption_id', $configoptionId)
            ->order('level', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        if (empty($groups)) {
            return [];
        }

        // 获取所有级联项
        $groupIds = array_column($groups, 'id');
        $items = $IdcsmartCommonCascadeItemModel
            ->whereIn('cascade_group_id', $groupIds)
            ->order('cascade_group_id', 'asc')
            ->order('order', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 构建级联项的映射关系
        $itemsByGroup = [];
        $itemsById = [];
        foreach ($items as $item) {
            $itemsByGroup[$item['cascade_group_id']][] = $item;
            $itemsById[$item['id']] = $item;
        }

        // 递归构建树形结构（从顶级项开始，parent_item_id = 0）
        $tree = $this->buildTree($itemsByGroup, $IdcsmartCommonProductConfigoptionSubModel, 0);

        return $tree;
    }
    /**
     * 时间 2024-12-20
     * @title 获取级联组列表
     * @desc 获取指定配置项的所有级联组信息
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID
     * @return array groups - 级联组列表
     */
    public function getCascadeGroups($configoptionId)
    {
        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();

        $groups = $IdcsmartCommonCascadeGroupModel
            ->where('configoption_id', $configoptionId)
            ->order('level', 'asc')
            ->order('id', 'asc')
            ->field('id,group_name')
            ->select()
            ->toArray();

        return $groups;
    }


    /**
     * 时间 2024-12-20
     * @title 递归构建树形结构
     * @desc 递归构建级联树形结构，返回扁平化的项目树
     * @author theworld
     * @version v1
     * @param array $itemsByGroup - 按组ID分组的级联项
     * @param object $configoptionSubModel - 配置子项模型
     * @param int $parentItemId - 父级联项ID（默认0）
     * @return array
     */
    private function buildTree($itemsByGroup, $configoptionSubModel, $parentItemId = 0)
    {
        $tree = [];

        // 遍历所有级联项，找到父级为当前项的子项
        foreach ($itemsByGroup as $groupId => $groupItems) {
            foreach ($groupItems as $item) {
                if ($item['parent_item_id'] == $parentItemId) {
                    $itemNode = [
                        'item_id' => $item['id'],
                        'item_name' => $item['item_name'],
                        'fee_type' => $item['fee_type'],
                        'group_id' => $item['cascade_group_id'],
                        'is_leaf' => $item['is_leaf'],
                        'order' => $item['order'],
                        'hidden' => $item['hidden'],
                    ];

                    // 如果是末端级联项，添加价格信息
                    if ($item['is_leaf'] == 1) {
                        $subs = $configoptionSubModel->getSubsWithPriceByCascadeItemId($item['id']);
                        $itemNode['price'] = $subs;
                    } else {
                        // 如果不是末端，递归获取子级联项
                        $children = $this->buildTree($itemsByGroup, $configoptionSubModel, $item['id']);
                        if (!empty($children)) {
                            $itemNode['children'] = $children;
                        }
                    }

                    $tree[] = $itemNode;
                }
            }
        }

        // 按 order 字段排序
        usort($tree, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $tree;
    }

    /**
     * 时间 2024-12-20
     * @title 验证级联结构完整性
     * @desc 验证级联结构的完整性，包括循环引用检测、名称唯一性等
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @return array
     */
    public function validateCascadeStructure($configoptionId)
    {
        $errors = [];

        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        // 获取所有级联组
        $groups = $IdcsmartCommonCascadeGroupModel
            ->where('configoption_id', $configoptionId)
            ->select()
            ->toArray();

        if (empty($groups)) {
            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => [
                    'valid' => true,
                    'errors' => []
                ]
            ];
        }

        // 获取所有级联项
        $groupIds = array_column($groups, 'id');
        $items = $IdcsmartCommonCascadeItemModel
            ->whereIn('cascade_group_id', $groupIds)
            ->select()
            ->toArray();

        // 1. 检查级联项名称唯一性（同级联组内）
        $namesByGroup = [];
        foreach ($items as $item) {
            $groupId = $item['cascade_group_id'];
            $itemName = $item['item_name'];
            
            if (!isset($namesByGroup[$groupId])) {
                $namesByGroup[$groupId] = [];
            }
            
            if (in_array($itemName, $namesByGroup[$groupId])) {
                $errors[] = lang_plugins('idcsmart_common_cascade_item_name_duplicate') . ': ' . $itemName;
            } else {
                $namesByGroup[$groupId][] = $itemName;
            }
        }

        // 2. 检查循环引用
        foreach ($items as $item) {
            if ($this->hasCircularReference($item['id'], $items)) {
                $errors[] = lang_plugins('idcsmart_common_cascade_circular_reference') . ': ' . $item['item_name'];
            }
        }

        // 3. 检查末端级联项是否有价格配置（可选检查）
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        foreach ($items as $item) {
            if ($item['is_leaf'] == 1) {
                $subs = $IdcsmartCommonProductConfigoptionSubModel->getSubsByCascadeItemId($item['id']);
                // 注释掉强制要求价格配置的检查，因为可能还未配置
                // if (empty($subs)) {
                //     $errors[] = '末端级联项未配置价格: ' . $item['item_name'];
                // }
            }
        }

        // 4. 检查区间定价是否重叠
        foreach ($items as $item) {
            if ($item['is_leaf'] == 1) {
                $subs = $IdcsmartCommonProductConfigoptionSubModel->getSubsByCascadeItemId($item['id']);
                if (count($subs) > 1) {
                    // 检查是否有区间定价
                    $hasRange = false;
                    foreach ($subs as $sub) {
                        if ($sub['qty_min'] > 0 || $sub['qty_max'] > 0) {
                            $hasRange = true;
                            break;
                        }
                    }
                    
                    if ($hasRange) {
                        // 检查区间是否重叠
                        $ranges = [];
                        foreach ($subs as $sub) {
                            $ranges[] = [
                                'min' => $sub['qty_min'],
                                'max' => $sub['qty_max'],
                                'name' => $sub['option_name']
                            ];
                        }
                        
                        if ($this->hasRangeOverlap($ranges)) {
                            $errors[] = lang_plugins('idcsmart_common_cascade_quantity_range_overlap') . ': ' . $item['item_name'];
                        }
                    }
                }
            }
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'valid' => empty($errors),
                'errors' => $errors
            ]
        ];
    }

    /**
     * 时间 2024-12-20
     * @title 检查循环引用
     * @desc 检查级联项是否存在循环引用
     * @author theworld
     * @version v1
     * @param int $itemId - 级联项ID
     * @param array $allItems - 所有级联项
     * @return bool
     */
    private function hasCircularReference($itemId, $allItems)
    {
        $visited = [];
        $itemsById = [];
        
        foreach ($allItems as $item) {
            $itemsById[$item['id']] = $item;
        }

        $currentId = $itemId;
        
        while ($currentId != 0) {
            if (in_array($currentId, $visited)) {
                return true; // 发现循环引用
            }
            
            $visited[] = $currentId;
            
            if (!isset($itemsById[$currentId])) {
                break;
            }
            
            $currentId = $itemsById[$currentId]['parent_item_id'];
        }

        return false;
    }

    /**
     * 时间 2024-12-20
     * @title 检查区间重叠
     * @desc 检查数量区间是否存在重叠
     * @author theworld
     * @version v1
     * @param array $ranges - 区间数组
     * @return bool
     */
    private function hasRangeOverlap($ranges)
    {
        // 按 min 排序
        usort($ranges, function($a, $b) {
            return $a['min'] - $b['min'];
        });

        for ($i = 0; $i < count($ranges) - 1; $i++) {
            if ($ranges[$i]['max'] >= $ranges[$i + 1]['min']) {
                return true; // 发现重叠
            }
        }

        return false;
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联配置项的所有末端项
     * @desc 获取指定配置项的所有末端级联项
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @return array
     */
    public function getLeafItems($configoptionId)
    {
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        return $IdcsmartCommonCascadeItemModel->getLeafItems(['configoption_id' => $configoptionId]);
    }

    /**
     * 时间 2024-12-20
     * @title 初始化级联配置项
     * @desc 为新创建的级联配置项创建默认级联组和级联项
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @return array
     */
    public function initializeCascadeConfigoption($configoptionId)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

            // 创建默认一级级联组
            $groupResult = $IdcsmartCommonCascadeGroupModel->createCascadeGroup([
                'configoption_id' => $configoptionId,
                'group_name' => '级联组 1',
                'level' => 1,
            ]);

            if ($groupResult['status'] != 200) {
                return $groupResult;
            }

            $groupId = $groupResult['data']['id'];

            // 在默认级联组中创建默认级联项
            $itemResult = $IdcsmartCommonCascadeItemModel->createCascadeItem([
                'configoption_id' => $configoptionId,
                'item_name' => '默认选项',
                'parent_item_id' => 0,
            ]);

            if ($itemResult['status'] != 200) {
                return $itemResult;
            }

            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => [
                    'group_id' => $groupId,
                    'item_id' => $itemResult['data']['id']
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 自动创建或获取下级级联组
     * @desc 当为级联项添加子项时，自动创建下级级联组（如果不存在）
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @param int $currentLevel - 当前级联组层级
     * @return array 下级级联组信息
     */
    public function getOrCreateNextLevelGroup($configoptionId, $currentLevel)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            
            $nextLevel = $currentLevel + 1;
            
            // 检查下级级联组是否已存在
            $nextGroup = $IdcsmartCommonCascadeGroupModel->getGroupByLevel($configoptionId, $nextLevel);
            
            if (!empty($nextGroup)) {
                return [
                    'status' => 200,
                    'msg' => lang_plugins('success_message'),
                    'data' => $nextGroup
                ];
            }
            
            // 不存在则创建
            $result = $IdcsmartCommonCascadeGroupModel->createCascadeGroup([
                'configoption_id' => $configoptionId,
                'group_name' => '级联组 ' . $nextLevel,
                'level' => $nextLevel,
            ]);
            
            if ($result['status'] != 200) {
                throw new \Exception($result['msg']);
            }
            
            $nextGroup = $IdcsmartCommonCascadeGroupModel->find($result['data']['id']);
            
            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => $nextGroup
            ];
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 提升级联项到上级级联组
     * @desc 当删除级联项时，将其子级联项提升到父级联项的位置
     * @author theworld
     * @version v1
     * @param int $itemId - 被删除的级联项ID
     * @return bool
     */
    public function promoteChildItems($itemId)
    {
        try {
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            
            // 获取被删除的级联项信息
            $deletedItem = $IdcsmartCommonCascadeItemModel->find($itemId);
            if (empty($deletedItem)) {
                return true;
            }
            
            // 获取该级联项的所有子级联项
            $childItems = $IdcsmartCommonCascadeItemModel->where('parent_item_id', $itemId)->select();
            
            if (empty($childItems) || count($childItems) == 0) {
                return true;
            }
            
            // 获取父级联项的级联组
            $parentGroup = $IdcsmartCommonCascadeGroupModel->find($deletedItem['cascade_group_id']);
            if (empty($parentGroup)) {
                return false;
            }
            
            // 子级联项提升到父级位置
            foreach ($childItems as $childItem) {
                $childItem->save([
                    'cascade_group_id' => $deletedItem['cascade_group_id'],
                    'parent_item_id' => $deletedItem['parent_item_id'],
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
     * @title 提升级联组层级
     * @desc 当删除级联组时，将下级级联组提升一级
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @param int $deletedLevel - 被删除的级联组层级
     * @return bool
     */
    public function promoteCascadeGroups($configoptionId, $deletedLevel)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            
            // 获取所有大于被删除层级的级联组
            $groupsToPromote = $IdcsmartCommonCascadeGroupModel
                ->where('configoption_id', $configoptionId)
                ->where('level', '>', $deletedLevel)
                ->order('level', 'asc')
                ->select();
            
            if (empty($groupsToPromote) || count($groupsToPromote) == 0) {
                return true;
            }
            
            // 将所有下级级联组的层级减1
            foreach ($groupsToPromote as $group) {
                $group->save([
                    'level' => $group['level'] - 1,
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
     * @title 清理空级联组
     * @desc 自动删除没有级联项的级联组
     * @author theworld
     * @version v1
     * @param int $configoptionId - 配置项ID
     * @return bool
     */
    public function cleanEmptyGroups($configoptionId)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            
            // 获取所有级联组
            $groups = $IdcsmartCommonCascadeGroupModel
                ->where('configoption_id', $configoptionId)
                ->where('level', '>', 1) // 不删除顶级级联组
                ->order('level', 'desc') // 从最高层级开始删除
                ->select();
            
            foreach ($groups as $group) {
                // 检查该级联组是否有级联项
                $itemCount = $IdcsmartCommonCascadeItemModel
                    ->where('cascade_group_id', $group['id'])
                    ->count();
                
                if ($itemCount == 0) {
                    // 删除空级联组
                    $group->delete();
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 时间 2024-12-20
     * @title 重新分配级联项的父子关系
     * @desc 当级联组被删除时，重新分配级联项的父级联项
     * @author theworld
     * @version v1
     * @param int $groupId - 级联组ID
     * @param int $targetGroupId - 目标级联组ID
     * @return bool
     */
    public function reassignParentItems($groupId, $targetGroupId)
    {
        try {
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            
            // 获取该级联组的所有级联项
            $items = $IdcsmartCommonCascadeItemModel
                ->where('cascade_group_id', $groupId)
                ->select();
            
            if (empty($items) || count($items) == 0) {
                return true;
            }
            
            foreach ($items as $item) {
                // 获取父级联项
                $parentItem = null;
                if ($item['parent_item_id'] > 0) {
                    $parentItem = $IdcsmartCommonCascadeItemModel->find($item['parent_item_id']);
                }
                
                // 确定新的父级联项ID
                $newParentId = 0;
                if (!empty($parentItem)) {
                    // 如果父级联项存在，使用父级联项的父级（祖父级）
                    $newParentId = $parentItem['parent_item_id'];
                }
                
                // 更新级联项的级联组和父级联项
                $item->save([
                    'cascade_group_id' => $targetGroupId,
                    'parent_item_id' => $newParentId,
                    'update_time' => time(),
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

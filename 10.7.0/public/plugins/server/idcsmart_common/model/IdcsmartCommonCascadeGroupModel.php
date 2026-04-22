<?php 
namespace server\idcsmart_common\model;

use think\Model;

/**
 * 级联配置项组模型
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeGroupModel extends Model
{
    protected $name = 'module_idcsmart_common_cascade_group';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'configoption_id'   => 'int',
        'group_name'        => 'string',
        'level'             => 'int',
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
     * @title 创建级联组
     * @desc 创建新的级联组
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID require
     * @param string group_name - 级联组名称 require
     * @param int level - 层级编号 require
     * @return array result - 操作结果
     */
    public function createCascadeGroup($param)
    {
        $this->startTrans();

        try {
            $configoptionId = $param['configoption_id'] ?? 0;
            $groupName = $param['group_name'] ?? '';
            $level = $param['level'] ?? 1;

            if (empty($groupName)) {
                throw new \Exception(lang_plugins('param_error'));
            }

            $time = time();
            $groupId = $this->insertGetId([
                'configoption_id' => $configoptionId,
                'group_name' => $groupName,
                'level' => $level,
                'create_time' => $time,
                'update_time' => $time,
            ]);

            $this->commit();

            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => [
                    'id' => $groupId
                ]
            ];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 更新级联组
     * @desc 更新级联组名称
     * @author theworld
     * @version v1
     * @param int id - 级联组ID require
     * @param string group_name - 级联组名称 require
     * @return array result - 操作结果
     */
    public function updateCascadeGroup($param)
    {
        $this->startTrans();

        try {
            $id = $param['id'] ?? 0;
            $groupName = $param['group_name'] ?? '';

            $group = $this->find($id);
            if (empty($group)) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_group_not_exist'));
            }

            if (empty($groupName)) {
                throw new \Exception(lang_plugins('param_error'));
            }

            $group->save([
                'group_name' => $groupName,
                'update_time' => time(),
            ]);

            $this->commit();

            return ['status' => 200, 'msg' => lang_plugins('success_message')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联组
     * @desc 删除级联组，自动删除后续所有级联组和相关数据
     * @author theworld
     * @version v1
     * @param int id - 级联组ID require
     * @return array result - 操作结果
     */
    public function deleteCascadeGroup($param)
    {
        $this->startTrans();

        try {
            $id = $param['id'] ?? 0;

            $group = $this->find($id);
            if (empty($group)) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_group_not_exist'));
            }

            // 不允许删除顶级级联组（level=1）
            if ($group['level'] == 1) {
                throw new \Exception(lang_plugins('idcsmart_common_cannot_delete_top_level_group'));
            }

            $configoptionId = $group['configoption_id'];
            
            // 检查是否为下游代理商品
            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
            $configoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);
            if ($configoption && $this->isDownstreamProduct($configoption['product_id'])) {
                throw new \Exception(lang_plugins('idcsmart_common_downstream_cannot_delete_cascade_group'));
            }
            $deletedLevel = $group['level'];

            // 获取所有需要删除的级联组（当前级别及后续所有级别）
            $groupsToDelete = $this->where('configoption_id', $configoptionId)
                ->where('level', '>=', $deletedLevel)
                ->order('level', 'desc') // 从高级别开始删除
                ->select();

            if (empty($groupsToDelete) || count($groupsToDelete) == 0) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_group_not_exist'));
            }

            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonProductConfigoptionSubModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel();
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();

            // 检查所有待删除级联组下的级联项是否已被主机使用
            $groupIdsToDelete = array_column($groupsToDelete->toArray(), 'id');
            $usedCount = $IdcsmartCommonHostConfigoptionModel->alias('hc')
                ->leftJoin('module_idcsmart_common_cascade_item ci', 'ci.id=hc.cascade_item_id')
                ->whereIn('ci.cascade_group_id', $groupIdsToDelete)
                ->count();
            
            if ($usedCount > 0) {
                throw new \Exception(lang_plugins('idcsmart_common_cascade_group_in_use'));
            }

            // 逐个删除级联组及其相关数据
            foreach ($groupsToDelete as $groupToDelete) {
                // 获取该级联组的所有级联项
                $items = $IdcsmartCommonCascadeItemModel->where('cascade_group_id', $groupToDelete['id'])->select();

                foreach ($items as $item) {
                    // 删除级联项相关的配置子项
                    $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $item['id'])->select();

                    foreach ($configoptionSubs as $sub) {
                        // 删除配置子项的价格配置
                        $IdcsmartCommonPricingModel->where('type', 'configoption_sub')
                            ->where('rel_id', $sub['id'])
                            ->delete();
                    }

                    // 删除配置子项
                    $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $item['id'])->delete();

                    // 删除级联项的价格配置
                    $IdcsmartCommonPricingModel->where('type', 'cascade_item')
                        ->where('rel_id', $item['id'])
                        ->delete();
                }

                // 删除该级联组的所有级联项
                $IdcsmartCommonCascadeItemModel->where('cascade_group_id', $groupToDelete['id'])->delete();

                // 删除级联组
                $groupToDelete->delete();
            }

            // 重新设置剩余级联项的末端状态
            $this->updateRemainingLeafStatus($configoptionId, $deletedLevel);

            $this->commit();

            return ['status' => 200, 'msg' => lang_plugins('success_message')];
        } catch (\Exception $e) {
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }


    /**
     * 时间 2024-12-20
     * @title 获取指定层级的级联组
     * @desc 获取指定配置项和层级的级联组
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID require
     * @param int level - 层级编号 require
     * @return object|null group - 级联组信息
     */
    public function getGroupByLevel($configoptionId, $level)
    {
        return $this->where('configoption_id', $configoptionId)
            ->where('level', $level)
            ->find();
    }

    /**
     * 时间 2024-12-20
     * @title 获取下一个层级编号
     * @desc 获取指定配置项的下一个可用层级编号
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID require
     * @return int level - 下一个层级编号
     */
    public function getNextLevel($configoptionId)
    {
        $maxLevel = $this->where('configoption_id', $configoptionId)->max('level');
        return $maxLevel ? $maxLevel + 1 : 1;
    }

    /**
     * 时间 2024-12-20
     * @title 更新剩余级联项的末端状态
     * @desc 删除级联组后，重新设置剩余级联项的末端状态
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID require
     * @param int deleted_level - 被删除的级别 require
     * @return bool
     */
    private function updateRemainingLeafStatus($configoptionId, $deletedLevel)
    {
        try {
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCascadeLogic = new \server\idcsmart_common\logic\IdcsmartCommonCascadeLogic();

            // 获取删除级别前一级的所有级联组
            $prevLevelGroups = $this->where('configoption_id', $configoptionId)
                ->where('level', $deletedLevel - 1)
                ->select();

            if (empty($prevLevelGroups)) {
                return true;
            }

            // 更新前一级所有级联项的末端状态
            foreach ($prevLevelGroups as $group) {
                $items = $IdcsmartCommonCascadeItemModel->where('cascade_group_id', $group['id'])->select();
                
                foreach ($items as $item) {
                    // 使用级联逻辑类的方法更新末端状态
                    $IdcsmartCommonCascadeLogic->updateLeafStatus($item['id']);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

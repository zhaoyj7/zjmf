<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\logic\IdcsmartCommonCascadeLogic;
use server\idcsmart_common\model\IdcsmartCommonCascadeItemModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
use server\idcsmart_common\validate\IdcsmartCommonCascadeItemValidate;
use server\idcsmart_common\validate\IdcsmartCommonCascadeItemPriceValidate;

/**
 * @title 通用商品-级联项管理
 * @desc 通用商品-级联项管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonCascadeItemController
 */
class IdcsmartCommonCascadeItemController extends BaseController
{
    public $validate;

    /**
     * 初始化验证
     */
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonCascadeItemValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $IdcsmartCommonLogic->validateConfigoption($param);
    }

    /**
     * 时间 2024-12-20
     * @title 获取级联树形结构
     * @desc 获取完整的级联树形结构 以扁平化的项目树形式返回
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/tree
     * @method GET
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @return array tree - desc:树形结构数据
     * @return int tree[].item_id - desc:级联项ID
     * @return string tree[].item_name - desc:级联项名称
     * @return int tree[].group_id - desc:所属级联组ID
     * @return int tree[].is_leaf - desc:是否为末端项 1是 0否
     * @return int tree[].order - desc:排序値
     * @return int tree[].hidden - desc:是否隐藏 1是 0否
     * @return array tree[].price - desc:价格配置 仅末端项有此字段
     * @return int tree[].price[].id - desc:配置子项ID
     * @return string tree[].price[].option_name - desc:配置子项名称
     * @return int tree[].price[].qty_min - desc:最小数量
     * @return int tree[].price[].qty_max - desc:最大数量
     * @return array tree[].price[].pricing - desc:价格信息
     * @return array tree[].children - desc:子级联项 仅非末端项有此字段
     * @return int tree[].children[].item_id - desc:级联项ID
     * @return string tree[].children[].item_name - desc:级联项名称
     * @return int tree[].children[].group_id - desc:所属级联组ID
     * @return int tree[].children[].is_leaf - desc:是否为末端项 1是 0否
     * @return int tree[].children[].order - desc:排序値
     * @return int tree[].children[].hidden - desc:是否隐藏 1是 0否
     * @return array tree[].children[].price - desc:价格配置 仅末端项有此字段
     * @return int tree[].children[].price[].id - desc:配置子项ID
     * @return string tree[].children[].price[].option_name - desc:配置子项名称
     * @return int tree[].children[].price[].qty_min - desc:最小数量
     * @return int tree[].children[].price[].qty_max - desc:最大数量
     * @return array tree[].children[].price[].pricing - desc:价格信息
     * @return array group - desc:级联组信息
     * @return int group[].id - desc:级联组ID
     * @return string group[].group_name - desc:级联组名称
     */
    public function tree()
    {
        $param = $this->request->param();

        $IdcsmartCommonCascadeLogic = new IdcsmartCommonCascadeLogic();

        $tree = $IdcsmartCommonCascadeLogic->getCascadeTree($param['configoption_id']);
        $groups = $IdcsmartCommonCascadeLogic->getCascadeGroups($param['configoption_id']);

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'tree' => $tree,
                'group' => $groups
            ]
        ]);
    }

    /**
     * 时间 2024-12-20
     * @title 创建级联项
     * @desc 创建新的级联项
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item
     * @method POST
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param string item_name - desc:级联项名称 validate:required
     * @param int parent_item_id - desc:父级联项ID 默认0 validate:optional
     * @param int prev_item_id - desc:排在该级联项之后，不传则追加到末尾，必须与新建项同父级 validate:optional
     * @param int order - desc:排序 validate:optional
     * @param int hidden - desc:是否隐藏 0否 1是 validate:optional
     * @return int id - desc:级联项ID
     */
    public function create()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        $result = $IdcsmartCommonCascadeItemModel->createCascadeItem($param);

        return json($result);
    }


    /**
     * 时间 2024-12-20
     * @title 更新级联项
     * @desc 更新级联项信息
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int id - desc:级联项ID validate:required
     * @param string item_name - desc:级联项名称 validate:optional
     * @param int order - desc:排序 validate:optional
     * @param int hidden - desc:是否隐藏 0否 1是 validate:optional
     */
    public function update()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        $result = $IdcsmartCommonCascadeItemModel->updateCascadeItem($param);

        return json($result);
    }

    /**
     * 时间 2024-12-20
     * @title 删除级联项
     * @desc 删除级联项及其所有子项和价格配置
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int id - desc:级联项ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        $result = $IdcsmartCommonCascadeItemModel->deleteCascadeItem($param);

        return json($result);
    }

    /**
     * 时间 2024-12-20
     * @title 级联项排序
     * @desc 调整级联项排序
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item/:id/order
     * @method PUT
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int id - desc:级联项ID validate:required
     * @param int order - desc:排序値 validate:required
     */
    // public function itemOrder()
    // {
    //     $param = $this->request->param();

    //     // 参数验证
    //     if (!isset($param['order']) || !is_numeric($param['order'])) {
    //         return json(['status' => 400, 'msg' => lang_plugins('param_error')]);
    //     }

    //     $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

    //     $result = $IdcsmartCommonCascadeItemModel->updateCascadeItem($param);

    //     return json($result);
    // }


    /**
     * 时间 2024-12-20
     * @title 获取末端级联项价格
     * @desc 获取末端级联项的价格配置
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item/:item_id/price
     * @method GET
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int item_id - desc:级联项ID validate:required
     * @return array price - desc:价格配置列表
     * @return int price[].id - desc:配置子项ID
     * @return int price[].product_configoption_id - desc:商品配置项ID
     * @return int price[].cascade_item_id - desc:级联项ID
     * @return string price[].option_name - desc:配置子项名称
     * @return string price[].option_param - desc:配置子项参数
     * @return int price[].qty_min - desc:最小数量 0表示不限制
     * @return int price[].qty_max - desc:最大数量 0表示不限制
     * @return int price[].order - desc:排序値
     * @return int price[].hidden - desc:是否隐藏 1是 0否
     * @return string price[].country - desc:国家代码
     * @return int price[].upstream_id - desc:上游ID
     * @return float price[].onetime - desc:一次性价格
     * @return array price[].custom_cycle - desc:自定义周期价格列表
     * @return int price[].custom_cycle[].custom_cycle_id - desc:自定义周期ID
     * @return float price[].custom_cycle[].amount - desc:自定义周期价格
     * @return string price[].custom_cycle[].name - desc:自定义周期名称
     */
    // public function getPrice()
    // {
    //     $param = $this->request->param();
    //     $itemId = $param['item_id'] ?? 0;

    //     $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        
    //     // 验证级联项是否存在
    //     $item = $IdcsmartCommonCascadeItemModel->find($itemId);
    //     if (empty($item)) {
    //         return json(['status' => 400, 'msg' => lang_plugins('idcsmart_common_cascade_item_not_exist')]);
    //     }

    //     // 验证是否为末端级联项
    //     if ($item['is_leaf'] != 1) {
    //         return json(['status' => 400, 'msg' => lang_plugins('idcsmart_common_cascade_item_not_leaf')]);
    //     }

    //     // 获取价格配置
    //     $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
    //     $subs = $IdcsmartCommonProductConfigoptionSubModel->getSubsWithPriceByCascadeItemId($itemId);

    //     return json([
    //         'status' => 200,
    //         'msg' => lang_plugins('success_message'),
    //         'data' => [
    //             'fee_type' => $item['fee_type'] ?? 'fixed',
    //             'price' => $subs
    //         ]
    //     ]);
    // }

    /**
     * 时间 2024-12-20
     * @title 设置末端级联项价格
     * @desc 为末端级联项设置价格 固定价格或区间定价
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/cascade/item/:item_id/price
     * @method POST
     * @author theworld
     * @version v1
     * @param int configoption_id - desc:配置项ID validate:required
     * @param int item_id - desc:级联项ID validate:required
     * @param string fee_type - desc:计费类型 fixed固定价格 qty数量计费 stage阶梯计费 validate:required
     * @param float onetime - desc:一次性价格 固定价格时使用 validate:optional
     * @param string option_param - desc:参数请求接口 固定价格时使用 validate:optional
     * @param object custom_cycle - desc:自定义周期及价格 固定价格时使用 validate:optional
     * @param array subs - desc:配置子项数组 数量计费和阶梯计费时使用 validate:optional
     * @param int subs[].qty_min - desc:最小数量 0表示不限制 validate:required
     * @param int subs[].qty_max - desc:最大数量 0表示不限制 validate:required
     * @param float subs[].onetime - desc:一次性价格 validate:optional
     * @param array subs[].custom_cycle - desc:自定义周期及价格 validate:optional
     */
    public function setPrice()
    {
        $param = $this->request->param();
        
        // 参数验证
        $IdcsmartCommonCascadeItemPriceValidate = new IdcsmartCommonCascadeItemPriceValidate();
        if (!$IdcsmartCommonCascadeItemPriceValidate->scene('set')->check($param)) {
            return json(['status' => 400, 'msg' => lang_plugins($IdcsmartCommonCascadeItemPriceValidate->getError())]);
        }
        
        $itemId = $param['item_id'] ?? 0;
        $feeType = $param['fee_type'] ?? 'fixed';

        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        
        // 验证级联项是否存在
        $item = $IdcsmartCommonCascadeItemModel->find($itemId);
        if (empty($item)) {
            return json(['status' => 400, 'msg' => lang_plugins('idcsmart_common_cascade_item_not_exist')]);
        }

        // 验证是否为末端级联项
        if ($item['is_leaf'] != 1) {
            return json(['status' => 400, 'msg' => lang_plugins('idcsmart_common_cascade_item_not_leaf')]);
        }
        
        // 检查是否为下游代理商品
        $IdcsmartCommonCascadeGroupModel = new \server\idcsmart_common\model\IdcsmartCommonCascadeGroupModel();
        $group = $IdcsmartCommonCascadeGroupModel->find($item['cascade_group_id']);
        $configoptionId = $group['configoption_id'] ?? 0;
        
        $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
        $configoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);
        
        $UpstreamProductModel = new \app\common\model\UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $configoption['product_id'])->find();
        $isDownstream = !empty($upstreamProduct);

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        
        $IdcsmartCommonCascadeItemModel->startTrans();
        
        try {
            // 获取现有的配置子项
            $existingSubs = $IdcsmartCommonProductConfigoptionSubModel->getSubsByCascadeItemId($itemId);
            
            // 下游代理商品不能修改fee_type
            if ($isDownstream && $item['fee_type'] != $feeType) {
                throw new \Exception(lang_plugins('idcsmart_common_downstream_can_only_modify_price'));
            }
            
            // 下游代理商品不能改变配置子项的数量
            if ($isDownstream && $feeType != 'fixed') {
                $newSubsCount = count($param['subs'] ?? []);
                $existingSubsCount = count($existingSubs);
                if ($newSubsCount != $existingSubsCount) {
                    throw new \Exception(lang_plugins('idcsmart_common_downstream_can_only_modify_price'));
                }
            }
            
            // 更新级联项的计费类型
            $item->save([
                'fee_type' => $feeType,
                'update_time' => time(),
            ]);
            $existingSubIds = array_column($existingSubs, 'id');

            if ($feeType == 'fixed') {
                // 固定价格：只需要一个配置子项
                if (empty($existingSubs)) {
                    // 创建新的配置子项
                    $subParam = [
                        'configoption_id' => $param['configoption_id'],
                        'cascade_item_id' => $itemId,
                        'option_name' => $item['item_name'],
                        'option_param' => $param['option_param'] ?? '',
                        'onetime' => $param['onetime'] ?? 0,
                        'custom_cycle' => $param['custom_cycle'] ?? [],
                    ];
                    $IdcsmartCommonProductConfigoptionSubModel->createConfigoptionSub($subParam);
                } else {
                    // 更新第一个配置子项，删除其他的
                    $firstSub = $existingSubs[0];
                    $updateParam = [
                        'id' => $firstSub['id'],
                        'configoption_id' => $param['configoption_id'],
                        'option_name' => $item['item_name'],
                        'option_param' => $param['option_param'] ?? '',
                        'onetime' => $param['onetime'] ?? 0,
                        'custom_cycle' => $param['custom_cycle'] ?? [],
                    ];
                    // 下游代理商品只更新价格字段
                    if ($isDownstream) {
                        unset($updateParam['option_name']);
                        unset($updateParam['option_param']);
                    }
                    $IdcsmartCommonProductConfigoptionSubModel->updateConfigoptionSub($updateParam);

                    // 删除多余的配置子项
                    for ($i = 1; $i < count($existingSubs); $i++) {
                        $IdcsmartCommonProductConfigoptionSubModel->deleteConfigoptionSub(['id' => $existingSubs[$i]['id']]);
                    }
                }
            } else {
                // 数量计费或阶梯计费：处理多个配置子项
                $newSubs = $param['subs'] ?? [];

                $processedSubIds = [];

                foreach ($newSubs as $index => $newSub) {
                    if ($index < count($existingSubs)) {
                        // 更新现有的配置子项
                        $existingSub = $existingSubs[$index];
                        $updateParam = [
                            'id' => $existingSub['id'],
                            'configoption_id' => $param['configoption_id'],
                            'option_name' => '',
                            'option_param' => '',
                            'qty_min' => $newSub['qty_min'] ?? 0,
                            'qty_max' => $newSub['qty_max'] ?? 0,
                            'onetime' => $newSub['onetime'] ?? 0,
                            'custom_cycle' => $newSub['custom_cycle'] ?? [],
                        ];
                        // 下游代理商品只更新价格字段
                        if ($isDownstream) {
                            unset($updateParam['option_name']);
                            unset($updateParam['option_param']);
                            unset($updateParam['qty_min']);
                            unset($updateParam['qty_max']);
                        }
                        $IdcsmartCommonProductConfigoptionSubModel->updateConfigoptionSub($updateParam);
                        // 将已更新的配置子项ID添加到已处理列表
                        $processedSubIds[] = $existingSub['id'];
                    } else {
                        // 创建新的配置子项
                        $subParam = [
                            'configoption_id' => $param['configoption_id'],
                            'cascade_item_id' => $itemId,
                            'option_name' => '',
                            'option_param' => '',
                            'qty_min' => $newSub['qty_min'] ?? 0,
                            'qty_max' => $newSub['qty_max'] ?? 0,
                            'onetime' => $newSub['onetime'] ?? 0,
                            'custom_cycle' => $newSub['custom_cycle'] ?? [],
                        ];
                        $result = $IdcsmartCommonProductConfigoptionSubModel->createConfigoptionSub($subParam);
                        if (isset($result['data']['id'])) {
                            $processedSubIds[] = $result['data']['id'];
                        }
                    }
                }

                // 删除多余的配置子项（未在processedSubIds中的配置子项）
                // 例如：原有3个配置子项，现在只传了2个，则删除第3个
                foreach ($existingSubs as $existingSub) {
                    if (!in_array($existingSub['id'], $processedSubIds)) {
                        $IdcsmartCommonProductConfigoptionSubModel->deleteConfigoptionSub([
                            'id' => $existingSub['id'],
                            'configoption_id' => $param['configoption_id']
                        ]);
                    }
                }
            }

            $IdcsmartCommonCascadeItemModel->commit();

            return json([
                'status' => 200,
                'msg' => lang_plugins('success_message')
            ]);
        } catch (\Exception $e) {
            $IdcsmartCommonCascadeItemModel->rollback();
            return json([
                'status' => 400,
                'msg' => $e->getMessage()
            ]);
        }
    }
}

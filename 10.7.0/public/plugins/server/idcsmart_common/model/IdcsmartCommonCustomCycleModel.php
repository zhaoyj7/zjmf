<?php 
namespace server\idcsmart_common\model;

use think\facade\Db;
use think\Model;

class IdcsmartCommonCustomCycleModel extends Model
{
    protected $name = 'module_idcsmart_common_custom_cycle';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'name'                   => 'string',
        'cycle_time'             => 'int',
        'cycle_unit'             => 'string',
        'cycle_type'             => 'int',
        'status'                 => 'int',
        'create_time'            => 'int',
        'update_time'            => 'int',
        'upstream_id'            => 'int',
    ];

    # 预设周期
    public $preSetCycle = [
        '月' => 1,
        '季' => 3,
        '半年' => 6,
//        '一年' => 12,
//        '两年' => 24,
//        '三年' => 36,
    ];

    # 自然月预设周期（固定3个）
    public $naturalMonthCycles = [
        '月' => ['time' => 1, 'unit' => 'month'],
        '季' => ['time' => 3, 'unit' => 'month'],
        '年' => ['time' => 12, 'unit' => 'month'],
    ];

    # 预设周期
    public function preSetCycle($productId)
    {
        foreach ($this->preSetCycle as $key=>$value){
            $this->insertCyclePrice([
                'product_id' => $productId,
                'name' => $key,
                'cycle_time' => $value,
                'cycle_unit' => 'month',
                'cycle_type' => 0,
                'status' => 1,
                'rel_id' => $productId,
                'type' => 'product',
                'amount' => 0,
            ]);
        }
    }

    # 插入周期及价格
    public function insertCyclePrice($param)
    {
        $id = $this->insertGetId([
            'product_id' => $param['product_id'],
            'name' => $param['name'],
            'cycle_time' => $param['cycle_time'],
            'cycle_unit' => $param['cycle_unit'],
            'cycle_type' => $param['cycle_type'] ?? 0,
            'status' => $param['status'] ?? 1,
            'create_time' => time(),
        ]);

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

        $IdcsmartCommonCustomCyclePricingModel->insert([
            'custom_cycle_id' => $id,
            'rel_id' => $param['rel_id'],
            'type' => $param['type']??'product',
            'amount' => $param['amount']
        ]);

        return $id;
    }

    public function deleteCyclePrice($productId)
    {
        $ids = $this->where('product_id',$productId)->column('id');

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

        $IdcsmartCommonCustomCyclePricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->whereIn('custom_cycle_id',$ids)
            ->delete();

        $this->where('product_id',$productId)->delete();

        return true;
    }

    /**
     * 时间 2026-01-06
     * @title 开启自然月预付费周期
     * @desc 开启自然月预付费周期
     * @author hh
     * @version v1
     * @param int $productId - 商品ID
     * @return bool
     */
    public function enableNaturalMonthCycles($productId)
    {
        // 1. 禁用所有普通周期（后台不可见，前台不可用）
        $this->where('product_id', $productId)
            ->where('cycle_type', 0)
            ->update(array('status' => 0));
        
        // 2. 检查是否已存在自然月周期
        $existNaturalCycles = $this->where('product_id', $productId)
            ->where('cycle_type', 1)
            ->column('name');
        
        // 3. 创建自然月周期（默认全部启用，管理员后续可以选择性禁用）
        foreach ($this->naturalMonthCycles as $name => $config) {
            if (!in_array($name, $existNaturalCycles)) {
                $this->insertCyclePrice(array(
                    'product_id' => $productId,
                    'name' => $name,
                    'cycle_time' => $config['time'],
                    'cycle_unit' => $config['unit'],
                    'cycle_type' => 1,
                    'status' => 1,
                    'rel_id' => $productId,
                    'type' => 'product',
                    'amount' => 0,
                ));
            }
            // 注意：如果已存在，保持原有的 status 状态（管理员之前的选择）
        }
        
        return true;
    }

    /**
     * 时间 2026-01-06
     * @title 关闭自然月预付费周期
     * @desc 关闭自然月预付费周期
     * @author hh
     * @version v1
     * @param int $productId - 商品ID
     * @return bool
     */
    public function disableNaturalMonthCycles($productId)
    {
        // 1. 禁用所有自然月周期（后台不可见，前台不可用）
        $this->where('product_id', $productId)
            ->where('cycle_type', 1)
            ->update(array('status' => 0));
        
        // 2. 启用所有普通周期
        $this->where('product_id', $productId)
            ->where('cycle_type', 0)
            ->update(array('status' => 1));
        
        return true;
    }

    /**
     * 时间 2026-01-06
     * @title 启用/禁用单个周期
     * @desc 启用/禁用单个周期（管理员操作）
     * @author hh
     * @version v1
     * @param int $cycleId - 周期ID
     * @param int $status - 状态(0=禁用,1=启用)
     * @return array
     */
    public function updateCycleStatus($cycleId, $status)
    {
        $cycle = $this->find($cycleId);
        if (empty($cycle)) {
            return array('status' => 400, 'msg' => '周期不存在');
        }
        
        // 获取商品信息
        $product = Db::name('product')->where('id', $cycle['product_id'])->find();
        $isNaturalMonth = $product['natural_month_prepaid'] == 1;
        
        // 验证：只能操作当前模式下的周期
        if ($isNaturalMonth && $cycle['cycle_type'] != 1) {
            return array('status' => 400, 'msg' => '当前商品已开启自然月预付费，无法操作普通周期');
        }
        
        if (!$isNaturalMonth && $cycle['cycle_type'] != 0) {
            return array('status' => 400, 'msg' => '当前商品未开启自然月预付费，无法操作自然月周期');
        }
        
        // 检查：至少保留一个启用的周期
        if ($status == 0) {
            $enabledCount = $this->where('product_id', $cycle['product_id'])
                ->where('cycle_type', $cycle['cycle_type'])
                ->where('status', 1)
                ->count();
            
            if ($enabledCount <= 1) {
                return array('status' => 400, 'msg' => '至少需要保留一个启用的周期');
            }
        }
        
        $result = $this->where('id', $cycleId)->update(array(
            'status' => $status,
            'update_time' => time()
        ));
        
        if ($result !== false) {
            return array('status' => 200, 'msg' => '操作成功');
        }
        
        return array('status' => 400, 'msg' => '操作失败');
    }

}
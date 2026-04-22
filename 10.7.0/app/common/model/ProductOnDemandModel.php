<?php
namespace app\common\model;

use app\common\logic\ModuleLogic;
use think\Model;

/**
 * @title 商品按需计费配置模型
 * @desc  商品按需计费配置模型
 * @use app\common\model\ProductOnDemandModel
 */
class ProductOnDemandModel extends Model
{
    protected $name = 'product_on_demand';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'product_id'            => 'int',
        'billing_cycle_unit'    => 'string',
        'billing_cycle_day'     => 'int',
        'billing_cycle_point'   => 'string',
        'duration_id'           => 'int',
        'duration_ratio'        => 'float',
        'billing_granularity'   => 'string',
        'min_credit'            => 'string',
        'min_usage_time'        => 'int',
        'min_usage_time_unit'   => 'string',
        'upgrade_min_billing_time' => 'int',
        'upgrade_min_billing_time_unit'   => 'string',
        'grace_time'            => 'int',
        'grace_time_unit'       => 'string',
        'keep_time'             => 'int',
        'keep_time_unit'        => 'string',
        'keep_time_billing_item'=> 'string',
        'initial_fee'           => 'float',
        'client_auto_delete'    => 'int',
        'on_demand_to_duration' => 'int',
        'duration_to_on_demand' => 'int',
        'credit_limit_pay'      => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    // 缓存当前获取过的商品按需配置
    protected static $productOnDemand = [];

    /**
     * 时间 2025-03-20
     * @title 商品按需计费配置详情
     * @desc  商品按需计费配置详情
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @return  ProductOnDemandModel - - 按需计费模型实例
     */
    public function productOnDemandIndex($productId)
    {
        $productOnDemand = $this
                        ->where('product_id', $productId)
                        ->find();
        if(empty($productOnDemand)){
            try{
                $ConfigurationModel = new ConfigurationModel();
                $globalOnDemand = $ConfigurationModel->globalOnDemand();

                // 默认数据
                $data = [
                    'product_id' => $productId,
                    'billing_cycle_unit' => 'hour',
                    'billing_cycle_day'  => 1,
                    'billing_cycle_point'=> '00:00',
                    'duration_id'        => 0,
                    'duration_ratio'     => 1,
                    'billing_granularity'=> 'minute',
                    'min_credit'         => '',
                    'min_usage_time'     => 0,
                    'min_usage_time_unit'=> 'hour',
                    'upgrade_min_billing_time' => 0,
                    'upgrade_min_billing_time_unit' => 'hour',
                    'grace_time'         => (int)$globalOnDemand['grace_time'],
                    'grace_time_unit'    => $globalOnDemand['grace_time_unit'],
                    'keep_time'          => (int)$globalOnDemand['keep_time'],
                    'keep_time_unit'     => $globalOnDemand['keep_time_unit'],
                    'keep_time_billing_item' => '[]',
                    'initial_fee' => '0.00',
                    'client_auto_delete' => 0,
                    'on_demand_to_duration' => 0,
                    'duration_to_on_demand' => 0,
                    'credit_limit_pay' => 0,
                    'create_time' => time(),
                ];
                $productOnDemand = $this->create($data);
            }catch(\Exception $e){
                $productOnDemand = $this
                        ->where('product_id', $productId)
                        ->find();
            }
        }

        $productOnDemand['keep_time_billing_item'] = json_decode($productOnDemand['keep_time_billing_item'], true);
        return $productOnDemand;
    }

    /**
     * 时间 2025-03-20
     * @title 修改商品按需计费配置
     * @desc  修改商品按需计费配置
     * @author hh
     * @version v1
     * @param   array param - 参数
     * @param   int param.product_id - 商品ID require
     * @param   string param.billing_cycle_unit - 出账周期单位(hour=每小时,day=每天,month=每月) require
     * @param   int param.billing_cycle_day - 出账周期号数 requireIf,billing_cycle_unit=month
     * @param   string param.billing_cycle_point - 出账周期时间点(如00:00) requireIf,billing_cycle_unit=day/month
     * @param   int param.duration_id - 周期ID
     * @param   float param.duration_ratio - 周期比例 requireIf,duration_id>0
     * @param   float param.min_credit - 购买时用户最低余额 require
     * @param   int param.min_usage_time - 最低使用时长 require
     * @param   string param.min_usage_time_unit - 最低使用时长单位(second=秒,minute=分,hour=小时) require
     * @param   int param.upgrade_min_billing_time - 升降级最低计费时长 require
     * @param   string param.upgrade_min_billing_time_unit - 升降级最低计费时长单位(second=秒,minute=分,hour=小时) require
     * @param   int param.grace_time - 宽限期 require
     * @param   string param.grace_time_unit - 宽限期单位(hour=小时,day=天) require
     * @param   int param.keep_time - 保留期 require
     * @param   string param.keep_time_unit - 保留期单位(hour=小时,day=天) require
     * @param   array param.keep_time_billing_item - 保留计费项目标识
     * @param   float param.initial_fee - 初装费
     * @param   int param.client_auto_delete - 允许用户设置自动释放(0=否,1=是)
     * @param   int param.on_demand_to_duration - 允许按需转包年包月(0=否,1=是)
     * @param   int param.duration_to_on_demand - 允许包年包月/试用转按需(0=否,1=是)
     * @param   int param.credit_limit_pay - 允许信用额支付(0=否,1=是)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function productOnDemandUpdate(array $param): array
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        // 商品是否支持按需计费
        if(!in_array($product['pay_type'], ['recurring_prepayment_on_demand','on_demand'])){
            return ['status'=>400, 'msg'=>lang('product_not_support_on_demand')];
        }
        // 代理商品不支持按需
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();
        if(!empty($upstreamProduct)){
            return ['status'=>400, 'msg'=>lang('product_not_support_on_demand')];
        }

        $ModuleLogic = new ModuleLogic();
        $billingItem = $ModuleLogic->billingItem($product);
        $billingItem = array_column($billingItem, 'value', 'name');
        if(!empty($param['keep_time_billing_item'])){
            $param['keep_time_billing_item'] = array_intersect($param['keep_time_billing_item'], array_keys($billingItem));
            if(empty($param['keep_time_billing_item'])){
                return ['status'=>400, 'msg'=>lang('product_keep_time_billing_item_require') ];
            }
            $param['keep_time_billing_item'] = array_values($param['keep_time_billing_item']);
        }
        if($param['billing_cycle_unit'] == 'hour'){
            $param['billing_cycle_day'] = 1;
            $param['billing_cycle_point'] = '00:00';
        }else if($param['billing_cycle_unit'] == 'day'){
            $param['billing_cycle_day'] = 1;
            $param['billing_cycle_point'] = !empty($param['billing_cycle_point']) ? $param['billing_cycle_point'] : '00:00';
        }else{
            $param['billing_cycle_point'] = !empty($param['billing_cycle_point']) ? $param['billing_cycle_point'] : '00:00';
        }

        $param['duration_id'] = $param['duration_id'] ?? 0;
        $param['billing_granularity'] = 'minute';
        $param['min_credit'] = amount_format($param['min_credit'] ?? 0);
        $param['update_time'] = time();

        // 商品模块
        $module = $product->getModule();
        $hookRes = hook('before_product_on_demand_update', ['param'=>$param, 'module'=>$module]);
        foreach($hookRes as $v){
            if(is_array($v) && isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        $productOnDemand = $this->productOnDemandIndex($product->id);

        $param['keep_time_billing_item'] = json_encode($param['keep_time_billing_item']);
        $this->update($param, ['id'=>$productOnDemand->id], ['billing_cycle_unit','billing_cycle_day','billing_cycle_point','duration_id','duration_ratio','billing_granularity','min_credit','min_usage_time','min_usage_time_unit','upgrade_min_billing_time','upgrade_min_billing_time_unit','grace_time','grace_time_unit','keep_time','keep_time_unit','keep_time_billing_item','initial_fee','client_auto_delete','on_demand_to_duration','duration_to_on_demand','credit_limit_pay','update_time']);

        $newProductOnDemand = $this->productOnDemandIndex($product->id);
        
        $old = $this->formatForLog($productOnDemand, $billingItem);
        $new = $this->formatForLog($newProductOnDemand, $billingItem);
        $description = log_description($old, $new, 'product_on_demand');
        if(!empty($description)){
            $description = lang('log_update_product_on_demand_success', [
                '{product}' => 'product#'.$product->id.'#'.$product['name'].'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $product->id);
        }

        hook('after_product_on_demand_update', ['param'=>$param, 'module'=>$module]);

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
	 * 时间 2025-03-20
	 * @title 获取商品计费项目
	 * @desc  获取商品计费项目
	 * @author hh
	 * @version v1
     * @param   array param - 参数 require
	 * @param   int param.product_id - 商品ID require
     * @return  string list[].name - 配置项标识
     * @return  string list[].value - 配置项名称
	 */
    public function billingItem(array $param): array
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($param['product_id']);
        if(empty($product)){
            return ['list'=>[] ];
        }
        $ModuleLogic = new ModuleLogic();
        $billingItem = $ModuleLogic->billingItem($product);

        return ['list'=>$billingItem ];
    }

    /**
     * 时间 2025-03-20
     * @title 商品按需计费配置详情
     * @desc  商品按需计费配置详情
     * @author hh
     * @version v1
     * @param   ProductOnDemandModel productOnDemand - 按需计费模型实例 require
     * @param   array billingItem - 保留期计费项目(如['memory'=>'内存']) require
     * @return  array
     */
    protected function formatForLog($productOnDemand, $billingItem): array
    {
        $data = [
            'billing_cycle'         => '',
            'duration_ratio'        => $productOnDemand['duration_ratio'],
            'min_credit'            => $productOnDemand['min_credit'],
            'min_usage_time'        => $productOnDemand['min_usage_time'].lang('min_usage_time_unit_' . $productOnDemand['min_usage_time_unit']),
            'upgrade_min_billing_time' => $productOnDemand['upgrade_min_billing_time'].lang('min_usage_time_unit_' . $productOnDemand['upgrade_min_billing_time_unit']),
            'grace_time'            => $productOnDemand['grace_time'] . lang('grace_time_unit_' . $productOnDemand['grace_time_unit']),
            'keep_time'             => $productOnDemand['keep_time'] . lang('keep_time_unit_' . $productOnDemand['keep_time_unit']),
            'initial_fee'           => amount_format($productOnDemand['initial_fee']),
            'client_auto_delete'    => lang('switch_' . $productOnDemand['client_auto_delete'] ),
            'on_demand_to_duration' => lang('switch_' . $productOnDemand['on_demand_to_duration'] ),
            'duration_to_on_demand' => lang('switch_' . $productOnDemand['duration_to_on_demand'] ),
            'credit_limit_pay'      => lang('switch_' . $productOnDemand['credit_limit_pay'] ),
        ];
        $data['billing_cycle'] = lang('billing_cycle_unit_'.$productOnDemand['billing_cycle_unit'], [
            '{day}'     => $productOnDemand['billing_cycle_day'],
            '{point}'   => $productOnDemand['billing_cycle_point'],
        ]);

        // 保留期计费项目
        $keepTimeBillingItem = $productOnDemand['keep_time_billing_item'];
        if(empty($keepTimeBillingItem)){
            $data['keep_time_billing_item'] = '';
        }else{
            foreach($keepTimeBillingItem as $k=>$v){
                $v = $billingItem[ $v ] ?? '';
                if(!empty($v)){
                    $keepTimeBillingItem[ $k ] = $v;
                }else{
                    unset($keepTimeBillingItem[ $k ]);
                }
            }
            $data['keep_time_billing_item'] = implode(',',$keepTimeBillingItem);
        }

        return $data;
    }

    /**
     * 时间 2025-03-28
     * @title 商品按需计费配置详情
     * @desc  商品按需计费配置详情,该方法同商品只会查询一次
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @return  ProductOnDemandModel - - 按需计费模型实例
     */
    public static function getProductOnDemand($productId)
    {
        if(!empty(self::$productOnDemand[$productId])){
            return self::$productOnDemand[$productId];
        }
        $ProductOnDemandModel = new ProductOnDemandModel();
        $productOnDemand = $ProductOnDemandModel->productOnDemandIndex($productId);
        
        self::$productOnDemand[$productId] = $productOnDemand;
        return $productOnDemand;
    }

    /**
     * 时间 2025-03-28
     * @title 获取商品按需最低使用时长,仅退款使用
     * @desc  获取商品按需最低使用时长,秒数
     * @author hh
     * @version v1
     * @param   ProductOnDemandModel productOnDemand - 商品按需计费配置模型实例 require
     * @return  int
     */
    public function getMinUsageTime($productOnDemand): int
    {
        $multiple = [
            'second'    => 1,
            'minute'    => 60,
            'hour'      => 3600,
        ];
        $minUsageTime = $productOnDemand['min_usage_time'] * $multiple[ $productOnDemand['min_usage_time_unit'] ];
        return $minUsageTime;
    }

    /**
     * @时间 2025-04-02
     * @title 获取按需支持支付方式
     * @desc  获取按需支持支付方式
     * @author hh
     * @version v1
     * @param   ProductOnDemandModel productOnDemand - 商品按需计费配置模型实例 require
     * @return  array
     */
    public function getGateway($productOnDemand): array
    {
        $gateway = [];
        if($productOnDemand['credit_limit_pay'] == 1){
            $gateway[] = 'credit_limit';
        }
        $gateway[] = 'credit';
        return $gateway;
    }

    /**
     * 时间 2025-03-28
     * @title 获取商品升降级最低计费时长
     * @desc  获取商品升降级最低计费时长,秒数
     * @author hh
     * @version v1
     * @param   ProductOnDemandModel productOnDemand - 商品按需计费配置模型实例 require
     * @return  int
     */
    public function getUpgradeMinBillingTime($productOnDemand): int
    {
        $multiple = [
            'second'    => 1,
            'minute'    => 60,
            'hour'      => 3600,
        ];
        $minUsageTime = $productOnDemand['upgrade_min_billing_time'] * $multiple[ $productOnDemand['upgrade_min_billing_time_unit'] ];
        return $minUsageTime;
    }

}
<?php 
namespace server\idcsmart_common\logic;

# 逻辑类
use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use app\common\model\HostModel;
use app\common\model\ProductDurationRatioModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use server\idcsmart_common\model\IdcsmartCommonCustomCycleModel;
use server\idcsmart_common\model\IdcsmartCommonCustomCyclePricingModel;
use server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonPricingModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
use server\idcsmart_common\model\IdcsmartCommonCascadeItemModel;

class IdcsmartCommonLogic
{
    public $systemCycles = [
        'onetime' => '一次性',
    ];

    # 初始化验证
    public function validate($param)
    {
        $productId = $param['product_id']??0;

        $ProductModel = new ProductModel();

        $product = $ProductModel->find($productId);

        if (empty($product)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('product_not_found')]);die;
        }
        $ServerModel = new ServerModel();

        if ($product['type'] == 'server'){
            $server = $ServerModel->where('id',$product['rel_id'])
                ->where('module','idcsmart_common')
                ->find();
        }else{
            $server = $ServerModel->where('server_group_id',$product['rel_id'])
                ->where('module','idcsmart_common')
                ->find();
        }
        if (empty($server)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('product_not_link_idcsmart_common_module')]);die;
        }
    }

    # 配置子项初始化验证
    public function validateConfigoption($param)
    {
        $configoptionId = $param['configoption_id']??0;

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $productConfigoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);

        if (empty($productConfigoption)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('idcsmart_common_configoption_not_exist')]);die;
        }
    }

    public function checkQuantity($option_type)
    {
        if (in_array($option_type,['quantity','quantity_range'])){
            return true;
        }

        return false;
    }

    public function checkMultiSelect($option_type)
    {
        if ($option_type == 'multi_select'){
            return true;
        }

        return false;
    }

    public function checkYesNo($option_type)
    {
        if ($option_type == 'yes_no'){
            return true;
        }

        return false;
    }

    public function checkOs($option_type)
    {
        if ($option_type == 'os'){
            return true;
        }

        return false;
    }

    # 自定义周期时长s
    public function customCycleTime($cycle_time,$cycle_unit='hour',$begin_time=0)
    {
        if ($cycle_unit == 'hour'){
            $time = $cycle_time * 3600;
        }elseif ($cycle_unit == 'day'){
            $time = $cycle_time * 3600 * 24;
        }elseif ($cycle_unit == 'month'){
            # 换算为天数
            /*$totalDay = 0;
            for ($i=1;$i<=$cycle_time;$i++){
                $day = date("t",strtotime(date('Y-m-d H:i:s',$begin_time+$totalDay*3600*24)));
                $totalDay += $day;
            }
            $time = 3600*24*$totalDay;*/
            $newDateTimestamp = strtotime(date('Y-m-d H:i:s',$begin_time) . " +" . $cycle_time . " month");
            $time = $newDateTimestamp-$begin_time;
        }else{
            $time = 0;
        }

        return $time;
    }

    # 系统周期时长s
    public function systemCycleTime($cycle)
    {
        if ($cycle == 'onetime'){
            $time = 0;
        }else{
            $time = 0;
        }

        return $time;
    }

    # 阶梯计费
    public function quantityStagePrice($configoptionId,$quantity,$cycle,$last_price=0,$is_custom=false)
    {
        if ($quantity == 0){
            return 0;
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
            ->field('pcs.qty_min,pcs.qty_max')
            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
            ->where('pcs.product_configoption_id',$configoptionId)
            ->where('pcs.hidden',0)
            ->select()
            ->toArray();

        array_multisort($subs,array_column($subs,'qty_max'));

        foreach ($subs as $k=>$v){
            if ($v['qty_max']>=$quantity && $quantity>=$v['qty_min']){
                $min = $k;
                break;
            }
        }
        if ($is_custom){
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoptionId)
                ->where('pcs.hidden',0)
                ->where('pcs.qty_min','<=',$quantity)
                ->where('pcs.qty_max','>=',$quantity)
                ->order('pcs.id','acs')
                ->where('ccp.custom_cycle_id',$cycle)
                ->find();

            $amount = $pricing['amount']??0;
        }else{
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoptionId)
                ->where('pcs.hidden',0)
                ->where('pcs.qty_min','<=',$quantity)
                ->where('pcs.qty_max','>=',$quantity)
                ->order('pcs.id','acs')
                ->find();
            $amount = $pricing[$cycle];
        }

        if ($pricing['qty_min'] != 0){
            $quantity = $quantity-$pricing['qty_min']+1;
        }

        if (!empty($pricing)){
            $price = $amount * $quantity;
        }else{
            $price = $last_price * $quantity;
        }
        if ($quantity > 0 && $min!=0){
            if ($pricing['qty_min']>1){
                $sum = $this->quantityStagePrice($configoptionId,intval($subs[$min-1]['qty_max']),$cycle,floatval($amount),$is_custom);
            }else{
                $sum = $this->quantityStagePrice($configoptionId,intval($subs[$min-1]['qty_max']),$cycle,0,$is_custom);
            }
            $price = $sum + $price;
        }
        return bcsub($price,0,2);
    }

    /**
     * 级联配置项阶梯计费价格计算
     * @param int $cascadeItemId 级联项ID
     * @param int $quantity 数量
     * @param string $cycle 周期
     * @param float $last_price 上一阶梯价格
     * @param bool $is_custom 是否自定义周期
     * @return string
     */
    public function cascadeQuantityStagePrice($cascadeItemId, $quantity, $cycle, $is_custom = false, $last_price = 0)
    {
        if ($quantity == 0) {
            return 0;
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        
        // 获取该级联项的所有数量区间
        $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
            ->field('pcs.qty_min,pcs.qty_max')
            ->where('pcs.cascade_item_id', $cascadeItemId)
            ->where('pcs.hidden', 0)
            ->select()
            ->toArray();

        if (empty($subs)) {
            return 0;
        }

        // 按qty_max排序
        array_multisort($subs, array_column($subs, 'qty_max'));

        // 找到当前数量所在的区间索引
        $min = 0;
        foreach ($subs as $k => $v) {
            if ($v['qty_max'] >= $quantity && $quantity >= $v['qty_min']) {
                $min = $k;
                break;
            }
        }

        // 获取当前区间的价格信息
        if ($is_custom) {
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp', 'ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                ->where('pcs.cascade_item_id', $cascadeItemId)
                ->where('pcs.hidden', 0)
                ->where('pcs.qty_min', '<=', $quantity)
                ->where('pcs.qty_max', '>=', $quantity)
                ->where('ccp.custom_cycle_id', $cycle)
                ->order('pcs.id', 'asc')
                ->find();

            $amount = $pricing['amount'] ?? 0;
        } else {
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_pricing p', 'p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.cascade_item_id', $cascadeItemId)
                ->where('pcs.hidden', 0)
                ->where('pcs.qty_min', '<=', $quantity)
                ->where('pcs.qty_max', '>=', $quantity)
                ->order('pcs.id', 'asc')
                ->find();
            
            $amount = $pricing[$cycle] ?? 0;
        }

        if (empty($pricing)) {
            return bcsub($last_price * $quantity, 0, 2);
        }

        // 计算当前区间的数量
        $currentQuantity = $quantity;
        if ($pricing['qty_min'] != 0) {
            $currentQuantity = $quantity - $pricing['qty_min'] + 1;
        }

        // 计算当前区间的价格
        $price = $amount * $currentQuantity;

        // 如果不是第一个区间，需要加上前面区间的价格
        if ($currentQuantity > 0 && $min != 0) {
            if ($pricing['qty_min'] > 1) {
                $sum = $this->cascadeQuantityStagePrice($cascadeItemId, intval($subs[$min - 1]['qty_max']), $cycle, $is_custom, floatval($amount));
            } else {
                $sum = $this->cascadeQuantityStagePrice($cascadeItemId, intval($subs[$min - 1]['qty_max']), $cycle, $is_custom, 0);
            }
            $price = $sum + $price;
        }

        return bcsub($price, 0, 2);
    }

    /*
     * 购物车计算价格{"configoption":{"1"：2,"2":3,"4":[1,2,3]},"cycle":"monthly","product_id":104},配置类型为数量时,值取数量;为其他类型时,值取子项ID
     * 参数传递规则:
     * config_options:{
   "configoption":{"1":2,"2":3,"4":[1,2,3]},   这里是：配置项ID=>子项ID ，当配置项类型为数量时传 数量数组，为多选时，传子项ID的数组。，，其他的话就传子项ID，，
   "cycle": "monthly"或者 “自定义周期ID”,   这里 系统默认周期 就这样传，，，如果是 自定义周期，，，传自定义周期ID
}
     *
     * */
    public function cartCalculatePrice($param)
    {
        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $configoptions = $param['configoption']??[];
        // 新增级联配置项参数处理
        $cascadeConfigoptions = $param['cascade_configoption']??[];

        $productId = $param['product_id'];

        // wyh 20250827 新增
        $baseParam = request()->param();
        $priceBasis = $baseParam['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';

        $product = ProductModel::find($productId);
        
        // 初始化自然月预付费标识，默认为否
        $isNaturalMonth = false;

        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,cc.cycle_type,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0)
            ->where('cc.id',intval($param['cycle']))
            ->find();

        # 总价
        $price = 0;
        # 标准周期时长
        $standardCycleTime = 0;

        $description = $preview = [];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        # 过滤配置项
        $configoptions = $IdcsmartCommonProductConfigoptionModel->filterConfigoption($productId,$configoptions);

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        if (!empty($customCycle)){ # 自定义周期
            $cycleName = $customCycle['name']??'';
            
            # 判断是否为自然月预付费
            $isNaturalMonth = isset($customCycle['cycle_type']) && $customCycle['cycle_type'] == 1;
            
            // 计算标准周期时长(用于返回 duration 字段)
            $standardCycleTime = $this->customCycleTime($customCycle['cycle_time'], $customCycle['cycle_unit'], time());
            
            if ($isNaturalMonth) {
                // 自然月模式：计算到下个周期1号零点的时长(用于价格折算)
                $startTime = time();
                $dueTime = calculate_natural_month_due_time($startTime, $customCycle['cycle_time']);
                $cycleTime = $dueTime - $startTime;
            } else {
                // 普通模式：按原有逻辑计算
                $cycleTime = $this->customCycleTime($customCycle['cycle_time'],$customCycle['cycle_unit'],time());
            }

            # 配置项价格
            foreach ($configoptions as $key=>$value){
                $tmp = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,fee_type,allow_repeat,max_repeat,unit')
                    ->where('id',$key)
                    ->withAttr('option_name', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->withAttr('unit', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                // 跳过级联配置项，级联配置项单独处理
                if ($optionType == 'cascade') {
                    continue;
                }

                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->where('ccp.custom_cycle_id',$param['cycle'])
                            ->find();

                        if (!empty($quantityType)){
                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $subPrice = $this->quantityStagePrice($key,$item,$param['cycle'],0,true);
                                $price = bcadd($price,$subPrice,2);
                            }else{ # 数量计费
                                $subPrice = bcmul($quantityType['amount'],$item,2);
                                $price = bcadd($price,$subPrice,2);
                            }
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'] . $k,
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'],
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }
                        }
                    }

                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->withAttr('option_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('sub_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('unit', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $subPrice = isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0;
                        $price = bcadd($price,$subPrice,2);
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }else{
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount,pc.unit,pcs.country')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->withAttr('option_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('sub_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('unit', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->find();
                    $subPrice = isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0;
                    $price = bcadd($price,$subPrice,2);
                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;

                        if ($optionType=='area'){
                            $CountryModel = new CountryModel();
                            $country = $CountryModel->where('iso',$configoptionPrice['country'])->find();

                            $countryField = ['en-us'=> 'nicename'];
                            $countryName = $countryField[ $language ] ?? 'name_zh';
                        }

                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $optionType=='area'?$country[ $countryName ] ." ".$configoptionPrice['sub_name']:$configoptionPrice['sub_name'] . $configoptionPrice['unit'],
                            'price' => $subPrice
                        ];
                    }

                }
            }

            # 级联配置项价格处理
            foreach ($cascadeConfigoptions as $configoptionId => $cascadeData) {
                // 获取配置项信息
                $configoptionInfo = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,unit')
                    ->where('id', $configoptionId)
                    ->where('option_type', 'cascade')
                    ->withAttr('option_name', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->withAttr('unit', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->find();
                
                if (empty($configoptionInfo)) {
                    continue;
                }
                
                $itemId = $cascadeData['item_id'] ?? 0;
                $quantity = $cascadeData['quantity'] ?? 1;
                
                if (empty($itemId)) {
                    continue;
                }
                
                // 获取级联项信息
                $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($itemId);
                
                if (empty($cascadeItem) || $cascadeItem['is_leaf'] != 1) {
                    continue; // 只处理末端级联项
                }
                
                // 获取级联项对应的配置子项信息（自定义周期）
                $cascadeSubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                    ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                    ->where('pcs.cascade_item_id', $itemId)
                    ->where('pcs.hidden', 0)
                    ->where('ccp.custom_cycle_id', $param['cycle'])
                    ->find();
                
                if (!empty($cascadeSubPrice)) {
                    $feeType = $cascadeItem['fee_type'] ?? 'qty';
                    
                    // 检查是否为数量类型的级联配置项（通过级联项的fee_type判断）
                    if ($feeType == 'qty' || $feeType == 'stage') {
                        // 查找匹配数量区间的配置子项
                        $quantitySubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                            ->where('pcs.cascade_item_id', $itemId)
                            ->where('pcs.hidden', 0)
                            ->where('pcs.qty_min', '<=', $quantity)
                            ->where('pcs.qty_max', '>=', $quantity)
                            ->where('ccp.custom_cycle_id', $param['cycle'])
                            ->order('pcs.id', 'asc')
                            ->find();
                        
                        if (!empty($quantitySubPrice)) {
                            if ($feeType == 'stage') {
                                // 阶梯计费：需要创建一个临时的配置项ID来使用现有的阶梯计费逻辑
                                // 由于级联项的特殊性，我们需要单独处理阶梯计费
                                $subPrice = $this->cascadeQuantityStagePrice($itemId, $quantity, $param['cycle'], true);
                            } else {
                                // 数量计费
                                $subPrice = bcmul($quantitySubPrice['amount'] ?? 0, $quantity, 2);
                            }
                        } else {
                            $subPrice = 0;
                        }
                    } else {
                        // 非数量类型，按原有逻辑处理
                        $subPrice = bcmul($cascadeSubPrice['amount'] ?? 0, $quantity, 2);
                    }
                    
                    $price = bcadd($price, $subPrice, 2);
                    $cascadeFullName = $IdcsmartCommonCascadeItemModel->getFullName($itemId);
                    $description[] = $configoptionInfo['option_name'] . '=>' . $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . '=>' . $configoptionInfo['unit'] . '=>' . $subPrice;
                    $preview[] = [
                        'name' => $configoptionInfo['option_name'],
                        'value' => $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . $configoptionInfo['unit'],
                        'price' => $subPrice
                    ];
                }
            }

            # 基础价格
            $basePrice = $customCycle['amount'];

            # 商品价格（完整周期价格）
            $fullPrice = bcadd($price,$basePrice,2);

            $description[] = '商品' . '=>' . $product['name'] . '=>=>' . $basePrice;

            # 自然月预付费价格折算
            $isNaturalMonth = isset($customCycle['cycle_type']) && $customCycle['cycle_type'] == 1;
            $naturalMonthDiscount = 0;
            $firstPaymentPrice = $fullPrice; // 首次支付价格，默认等于完整价格
            
            if ($isNaturalMonth) {
                // 计算自然月到期时间
                $startTime = time();
                $dueTime = calculate_natural_month_due_time($startTime, $customCycle['cycle_time']);
                
                // 调用系统逻辑进行价格折算
                $discountedPrice = calculate_natural_month_price(
                    $fullPrice, 
                    $startTime, 
                    $dueTime, 
                    $customCycle['cycle_time']
                );
                
                // 计算折扣金额
                $naturalMonthDiscount = bcsub($fullPrice, $discountedPrice, 2);
                
                // 首次支付价格为折算后的价格
                $firstPaymentPrice = $discountedPrice;
            }

        }
        else{ # 系统周期(一次性)
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            if (!empty($product) && $product['pay_type']!='onetime'){
                return ['status'=>400,'msg'=>lang_plugins('cycle_error')];
            }
            $cycleName = $this->systemCycles[$param['cycle']]??'';
            $cycleTime = $this->systemCycleTime($param['cycle']);

            # 配置项价格
            foreach ($configoptions as $key=>$value){

                $tmp = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,fee_type,allow_repeat,max_repeat,unit')
                    ->where('id',$key)
                    ->withAttr('option_name', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->withAttr('unit', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                // 跳过级联配置项，级联配置项单独处理
                if ($optionType == 'cascade') {
                    continue;
                }

                # 数量类型
                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->find();
                        if (!empty($quantityType)){
                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $subPrice = $this->quantityStagePrice($key,$item,$param['cycle']);
                                $price = bcadd($price,$subPrice,2);
                            }else{ # 数量计费
                                $subPrice = bcmul($quantityType[$param['cycle']],$item,2);
                                $price = bcadd($price,$subPrice,2);
                            }
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'] . $k,
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'],
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }
                        }
                    }
                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->withAttr('option_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('sub_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('unit', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $subPrice = isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0;
                        $price = bcadd($price,$subPrice,2);
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }else{ # 非数量类型
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->withAttr('option_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('sub_name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->withAttr('unit', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->find();
                    $subPrice = isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0;
                    $price = bcadd($price,$subPrice,2);
                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }
            }

            # 级联配置项价格处理（系统周期）
            foreach ($cascadeConfigoptions as $configoptionId => $cascadeData) {
                // 获取配置项信息
                $configoptionInfo = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,unit')
                    ->where('id', $configoptionId)
                    ->where('option_type', 'cascade')
                    ->withAttr('option_name', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->withAttr('unit', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return $value;
                    })
                    ->find();
                
                if (empty($configoptionInfo)) {
                    continue;
                }
                
                $itemId = $cascadeData['item_id'] ?? 0;
                $quantity = $cascadeData['quantity'] ?? 1;
                
                if (empty($itemId)) {
                    continue;
                }
                
                // 获取级联项信息
                $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($itemId);
                
                if (empty($cascadeItem) || $cascadeItem['is_leaf'] != 1) {
                    continue; // 只处理末端级联项
                }
                
                // 获取级联项对应的配置子项信息（系统周期）
                $cascadeSubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                    ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                    ->where('pcs.cascade_item_id', $itemId)
                    ->where('pcs.hidden', 0)
                    ->find();
                
                if (!empty($cascadeSubPrice)) {
                    $feeType = $cascadeItem['fee_type'] ?? 'qty';
                    
                    // 检查是否为数量类型的级联配置项（通过级联项的fee_type判断）
                    if ($feeType == 'qty' || $feeType == 'stage') {
                        // 查找匹配数量区间的配置子项
                        $quantitySubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                            ->where('pcs.cascade_item_id', $itemId)
                            ->where('pcs.hidden', 0)
                            ->where('pcs.qty_min', '<=', $quantity)
                            ->where('pcs.qty_max', '>=', $quantity)
                            ->order('pcs.id', 'asc')
                            ->find();
                        
                        if (!empty($quantitySubPrice)) {
                            if ($feeType == 'stage') {
                                // 阶梯计费：需要创建一个临时的配置项ID来使用现有的阶梯计费逻辑
                                $subPrice = $this->cascadeQuantityStagePrice($itemId, $quantity, $param['cycle'], false);
                            } else {
                                // 数量计费
                                $subPrice = bcmul($quantitySubPrice[$param['cycle']] ?? 0, $quantity, 2);
                            }
                        } else {
                            $subPrice = 0;
                        }
                    } else {
                        // 非数量类型，按原有逻辑处理
                        $subPrice = bcmul($cascadeSubPrice[$param['cycle']] ?? 0, $quantity, 2);
                    }
                    
                    $price = bcadd($price, $subPrice, 2);
                    $cascadeFullName = $IdcsmartCommonCascadeItemModel->getFullName($itemId);
                    $description[] = $configoptionInfo['option_name'] . '=>' . $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . '=>' . $configoptionInfo['unit'] . '=>' . $subPrice;
                    $preview[] = [
                        'name' => $configoptionInfo['option_name'],
                        'value' => $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . $configoptionInfo['unit'],
                        'price' => $subPrice
                    ];
                }
            }

            # 商品价格
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $productPricing = $IdcsmartCommonPricingModel->where('type','product')
                ->where('rel_id',$productId)
                ->find();
            $basePrice = isset($productPricing[$param['cycle']]) && $productPricing[$param['cycle']]>0?$productPricing[$param['cycle']]:0;
            $price = bcadd($price,$basePrice,2);
            
            # 系统周期不支持自然月预付费，首次支付价格等于完整价格
            $isNaturalMonth = false;
            $fullPrice = $price;
            $firstPaymentPrice = $price;
        }

        $preview[] = [
            'name' => lang_plugins("idcsmart_common_time_duration"),
            'value' => $param['cycle']=='free'?lang_plugins('free'): multi_language_replace($cycleName),
            "price" => $basePrice
        ];

        // wyh 20240522 上下游时，直接返回折扣后金额
        $description = '';
        $discount = 0;
        $paramRequest = request()->param();
        $isDownstream = isset($paramRequest['is_downstream']) && $paramRequest['is_downstream']==1;
        
        // 确定最终的首次支付价格和续费价格
        $finalFirstPaymentPrice = isset($firstPaymentPrice) ? $firstPaymentPrice : (isset($fullPrice) ? $fullPrice : $price);
        $finalRenewPrice = isset($fullPrice) ? $fullPrice : $price;
        
        foreach ($preview as &$item){
            if ($isDownstream && $priceAgent){
                $hookClientLevelResultsOrgins = hook("client_discount_by_amount",[
                    'client_id'=>get_client_id(),
                    'product_id'=>$param['product_id'],
                    'amount'=>$item['price']
                ]);
                foreach ($hookClientLevelResultsOrgins as $hookClientLevelResultsOrgin){
                    if ($hookClientLevelResultsOrgin['status']==200){
                        $clientLevelDiscount = $hookClientLevelResultsOrgin['data']['discount']??0;
                        // 直接减去折扣
                        $item['price'] = bcsub($item['price'],$clientLevelDiscount,2)>0?bcsub($item['price'],$clientLevelDiscount,2):0;
                        $discount = bcadd($discount,$clientLevelDiscount,2);
                    }
                }
            }
            $description .= $item['name'] . ":" . $item['value'] . ',' . lang('price').':'.$item['price']."\r\n";
        }
        if ($isDownstream){
            $finalFirstPaymentPrice = bcsub($finalFirstPaymentPrice,$discount,2)>0?bcsub($finalFirstPaymentPrice,$discount,2):0;
            $finalRenewPrice = bcsub($finalRenewPrice,$discount,2)>0?bcsub($finalRenewPrice,$discount,2):0;
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$param['cycle']=='free'?0:$finalFirstPaymentPrice,
                'renew_price'=>$param['cycle']=='free'?0:$finalRenewPrice,
                'billing_cycle'=>$param['cycle']=='free'?lang_plugins('free'):$cycleName,
                'duration'=>$standardCycleTime, // 使用标准周期时长
                'description'=>$description,//implode("\n",$description),
                'preview'=>$preview,
                'base_price'=>$param['cycle']=='free'?0:$finalRenewPrice,
                'order_item'=>[],
                'is_natural_month_prepaid'=>$isNaturalMonth ? 1 : 0, // 是否为自然月预付费
                'due_time'  => $dueTime ?? 0,                        // 返回到期时间，用于自然月预付费
                // 不返回此数据，魔方云和DCIM特有
                // 'discount'=>0,
            ]
        ];

        // 添加自然月折扣信息
        if (isset($naturalMonthDiscount) && $naturalMonthDiscount > 0) {
            $result['data']['natural_month_discount'] = $naturalMonthDiscount;
        }

        return $result;
    }

    # 结算后调用,保存下单的配置项{"custom":{"configoption":{"1"：2,"2":3},"cascade_configoption":{"169":{"item_id":12,"quantity":2}}},"product":{},"host_id":1},配置类型为数量时,值取数量;为其他类型时,值取子项ID
    public function afterSettle($param)
    {
        $product = $param['product'];

        $productId = $product['id'];

        $hostId = $param['host_id'];

        $configoptions = $param['custom']['configoption']??[];
        // 获取级联配置项参数
        $cascadeConfigoptions = $param['custom']['cascade_configoption']??[];

        $IdcsmartCommonHostConfigoptionModel =new IdcsmartCommonHostConfigoptionModel();
        // 删除旧的产品关联配置数据
        $IdcsmartCommonHostConfigoptionModel->where('host_id',$hostId)->delete();
        // 删除旧的产品关联的子产品数据
        /*$IdcsmartCommonSonHost = new IdcsmartCommonSonHost();
        $IdcsmartCommonSonHost->where('host_id',$hostId)->delete();*/

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $configoptions = $IdcsmartCommonProductConfigoptionModel->filterConfigoption($productId,$configoptions,1);

        $insert = [];

        foreach ($configoptions as $key=>$value){
            $configoption = $IdcsmartCommonProductConfigoptionModel->where('id',$key)->find();
            $optionType = $configoption['option_type']??'';
            
            // 跳过级联配置项,级联配置项单独处理
            if ($optionType == 'cascade') {
                continue;
            }
            
            if ($this->checkQuantity($optionType)){
                if (!is_array($value)){
                    $value = [$value];
                }
                foreach ($value as $k=>$item){
                    $insert[] = [
                        'host_id' => $hostId,
                        'configoption_id' => $key,
                        'configoption_sub_id' => 0,
                        'qty' => $item,
                        'repeat' => $k,
                        'cascade_item_id' => 0,
                    ];
                }

            }elseif ($this->checkMultiSelect($optionType)){
                if (!is_array($value)){
                    $value = [$value];
                }
                foreach ($value as $item){
                    $insert[] = [
                        'host_id' => $hostId,
                        'configoption_id' => $key,
                        'configoption_sub_id' => $item,
                        'qty' => 0,
                        'repeat' => 0,
                        'cascade_item_id' => 0,
                    ];
                }
            }
            else{
                $insert[] = [
                    'host_id' => $hostId,
                    'configoption_id' => $key,
                    'configoption_sub_id' => $value,
                    'qty' => 0,
                    'repeat' => 0,
                    'cascade_item_id' => 0,
                ];
            }
        }

        // 处理级联配置项
        if (!empty($cascadeConfigoptions)) {
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            
            foreach ($cascadeConfigoptions as $configoptionId => $cascadeData) {
                // 验证配置项是否为级联类型
                $configoption = $IdcsmartCommonProductConfigoptionModel
                    ->where('id', $configoptionId)
                    ->where('product_id', $productId)
                    ->where('option_type', 'cascade')
                    ->find();
                
                if (empty($configoption)) {
                    continue;
                }
                
                $itemId = $cascadeData['item_id'] ?? 0;
                $quantity = $cascadeData['quantity'] ?? 1;
                
                if (empty($itemId)) {
                    continue;
                }
                
                // 验证级联项是否存在且为末端项
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($itemId);
                if (empty($cascadeItem) || $cascadeItem['is_leaf'] != 1) {
                    continue;
                }
                
                // 直接存储级联项ID和数量,不需要查找配置子项
                // 因为级联配置项可能是数量类型(阶梯计费),没有固定的配置子项ID
                $insert[] = [
                    'host_id' => $hostId,
                    'configoption_id' => $configoptionId,
                    'configoption_sub_id' => 0, // 级联配置项的子项ID设为0
                    'qty' => $quantity,
                    'repeat' => 0,
                    'cascade_item_id' => $itemId, // 存储级联项ID
                ];
            }
        }

        if (!empty($insert)) {
            $IdcsmartCommonHostConfigoptionModel->insertAll($insert);
        }

        return true;
    }

    # 获取可用续费周期
    public function currentDurationPrice($host_id)
    {
        $HostModel = new HostModel();

        $host = $HostModel->find($host_id);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $productId = $host['product_id'];

        // 获取商品信息，判断是否开启自然月预付费
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($productId);
        $isNaturalMonth = !empty($product) && $product['natural_month_prepaid'] == 1;
        $cycleType = $isNaturalMonth ? 1 : 0;

        // TODO wyh 20231219 续费使用比例，根据自然月开关过滤周期类型，只显示启用的周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $ratios = $IdcsmartCommonCustomCycleModel->alias('d')
            ->field('d.id,d.name,d.cycle_time as num,d.cycle_unit as unit,d.cycle_type,pdr.ratio')
            ->leftJoin('product_duration_ratio pdr', 'd.id=pdr.duration_id AND pdr.product_id='.$productId)
            ->where('d.product_id', $productId)
            ->where('d.cycle_type', $cycleType)
            ->where('d.status', 1)
            ->withAttr('ratio', function($val){
                return $val ?? '';
            })
            ->group('d.id')
            ->orderRaw('field(d.cycle_unit, "hour","day","month")')
            ->order('d.cycle_time','asc')
            ->select()
            ->toArray();
        if (empty($ratios)){
            return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>[]];
        }else{
            $duration = [];
            $currentDurationRatio = 0;
            foreach ($ratios as &$ratio){
                $durationName = $ratio['name'];
                if(app('http')->getName() == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $ratio['name'],
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $durationName = $multiLanguage['name'];
                    }
                }
                $cycleTime = $this->customCycleTime($ratio['num'],$ratio['unit'],$host['due_time']);

                if ($host['billing_cycle_time']==$cycleTime || $host['billing_cycle_name']==$ratio['name']){
                    $currentDurationRatio = $ratio['ratio'];
                }
                $ratio['duration'] = $cycleTime;
                $ratio['price'] = 0;
                $ratio['billing_cycle'] = $ratio['name'];
                $ratio['name_show'] = $durationName;
            }
            // 产品当前周期比例>0
            if ($currentDurationRatio>0){
                foreach ($ratios as $ratio2){
                    // 周期比例>0
                    if ($ratio2['ratio']>0){
                        // 计算续费价格
                        $dueTime = 0; // 初始化到期时间
                        if ($isNaturalMonth) {
                            // 自然月预付费：从到期时间开始计算到对应周期后的自然月1号零点
                            $startTime = $host['due_time'];
                            $dueTime = calculate_natural_month_due_time($startTime, $ratio2['num']);
                            $fullPrice = bcmul(1, round($host['base_price']*$ratio2['ratio']/$currentDurationRatio, 2), 2);
                            $renewPrice = calculate_natural_month_price($fullPrice, $startTime, $dueTime, $ratio2['num']);
                        } else {
                            // 普通周期：按比例计算
                            $renewPrice = bcmul(1, round($host['base_price']*$ratio2['ratio']/$currentDurationRatio, 2), 2);
                        }
                        
                        $duration[] = [
                            'id' => $ratio2['id'],
                            'duration' => $ratio2['duration'],
                            'price' => $renewPrice,
                            'billing_cycle' => $ratio2['billing_cycle'],
                            'name_show' => $ratio2['name_show'],
                            'base_price' => bcmul(1, round($host['base_price']*$ratio2['ratio']/$currentDurationRatio, 2), 2),
                            'prr' => $ratio2['ratio']/$currentDurationRatio,
                            'prr_numerator' => $ratio2['ratio'],
                            'prr_denominator' => $currentDurationRatio,
                            'is_natural_month_prepaid' => $isNaturalMonth ? 1 : 0,
                            'due_time' => $dueTime, // 添加到期时间
                        ];
                    }
                }
            }

            $result = [
                'status'=>200,
                'msg'=>lang_plugins('success_message'),
                'data'=>$duration
            ];
            return $result;
        }

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('pc.id,pc.option_type,hc.configoption_sub_id,hc.qty,pc.fee_type')
            ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=hc.configoption_id')
            ->where('hc.host_id',$host_id)
            ->where('pc.hidden',0)
            ->select()
            ->toArray();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        // TODO wyh 20231218 设置了比例，不做验证
        $ProductDurationRatioModel = new ProductDurationRatioModel();
        $ratios = $ProductDurationRatioModel->where('product_id',$productId)
            ->select()
            ->toArray();
        if (empty($ratios)){
            // TODO wyh 20231124 检查商品是否被删了配置
            $hostConfigoptions = $IdcsmartCommonHostConfigoptionModel->field('configoption_id,configoption_sub_id,qty')
                ->where('host_id',$host_id)
                ->select()->toArray();
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            foreach ($hostConfigoptions as $hostConfigoption){
                $optionExist = $IdcsmartCommonProductConfigoptionModel->where('id',$hostConfigoption['configoption_id'])->find();
                if (empty($optionExist)){
                    return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>[]];
                }
                if (!$this->checkQuantity($optionExist['option_type'])){
                    $subExist = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$hostConfigoption['configoption_id'])
                        ->where('id',$hostConfigoption['configoption_sub_id'])
                        ->find();
                    if (empty($subExist)){
                        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>[]];
                    }
                }else{
                    // 有配置(可能购买时没有数量配置，默认数量为0)
                    $subExist = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$hostConfigoption['configoption_id'])->find();
                    if (!empty($subExist)){
                        // 数量找不到范围
                        $qtyExist = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$hostConfigoption['configoption_id'])
                            ->where('qty_min','<=',$hostConfigoption['qty'])
                            ->where('qty_max','>=',$hostConfigoption['qty'])
                            ->find();
                        if (empty($qtyExist)){
                            return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>[]];
                        }
                    }
                }
            }
        }

        # 自定义周期及价格
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0) # 可显示出得周期
            ->select()
            ->toArray();
        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        foreach ($customCycles as &$customCycle){

            $customCycleAmount = $customCycle['amount']??0;

            # 配置子项的自定义价格
            foreach ($configoptions as $configoption){
                if ($this->checkQuantity($configoption['option_type'])){
                    # 找子项
                    $qtySub = $IdcsmartCommonProductConfigoptionSubModel
                        ->where('product_configoption_id',$configoption['id'])
                        ->where('qty_min','<=',$configoption['qty'])
                        ->where('qty_max','>=',$configoption['qty'])
                        ->order('order','asc')
                        ->find();
                    if (!empty($qtySub)){
                        # 阶梯计费
                        if ($configoption['fee_type'] == 'stage'){
                            $customCycleAmount = bcadd($customCycleAmount,$this->quantityStagePrice($configoption['id'],$configoption['qty'],$customCycle['id'],0,true),2);
                        }else{ # 数量计费
                            # 当前子项的价格 * 数量
                            $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                                ->where('rel_id',$qtySub['id'])
                                ->where('type','configoption')
                                ->value('amount')??0;
                            $customCycleAmount = bcadd($customCycleAmount,$amount * $configoption['qty'],2);
                        }

                    }
                }else{
                    $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                        ->where('rel_id',$configoption['configoption_sub_id'])
                        ->where('type','configoption')
                        ->value('amount');
                    $customCycleAmount = bcadd($customCycleAmount,!is_null($amount) && $amount>=0?$amount:0,2);
                }
            }
            $customCycle['cycle_amount'] = $customCycleAmount;
        }

        $duration = [];

        $currentDurationId = 0;

        foreach ($customCycles as $item1){
            $durationName = $item1['name'];
            if(app('http')->getName() == 'home'){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'name' => $item1['name'],
                    ],
                ]);
                if(isset($multiLanguage['name'])){
                    $durationName = $multiLanguage['name'];
                }
            }

            $cycleTime = $this->customCycleTime($item1['cycle_time'],$item1['cycle_unit'],$host['due_time']);

            if ($host['billing_cycle_time']==$cycleTime || $host['billing_cycle_name']==$item1['name']){
                $currentDurationId = $item1['id'];
            }

            $duration[] = [
                'id' => $item1['id'],
                'duration' => $cycleTime,
                'price' => $item1['cycle_amount'],
                'billing_cycle' => $item1['name'],
                'name_show' => $durationName,
            ];
        }

        /// 以周期比例为主
        if (!empty($ratios)){
            $ratiosFilter = [];
            foreach ($ratios as $ratio){
                $ratiosFilter[$ratio['duration_id']] = $ratio['ratio'];
            }
            $duration = array_map(function ($value) use ($ratiosFilter,$host,$currentDurationId){
                // 获取产品当前周期ID的周期比例存在且>0
                if (isset($ratiosFilter[$value['id']]) && isset($ratiosFilter[$currentDurationId]) && $ratiosFilter[$currentDurationId]>0){
                    $value['price'] = bcmul(1,round($host['renew_amount']*$ratiosFilter[$value['id']]/$ratiosFilter[$currentDurationId],2),2);
                }
                return $value;
            },$duration);
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$duration
        ];

        return $result;
    }

    # 获取所有配置
    public function allConfigOption($product_id)
    {
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$product_id)
            ->order('order','asc')
            ->select()
            ->toArray();

        $data = [];

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        foreach ($configoptions as $configoption){
            $subArr = [];
            # TODO 排除数量和多选类型
            if (!$this->checkQuantity($configoption['option_type']) && !$this->checkMultiSelect($configoption['option_type'])){
                $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption['id'])
                    ->select()
                    ->toArray();

                foreach ($subs as $sub){
                    $subArr[] = [
                        'name' => $sub['option_name'],
                        'value' => $sub['id']
                    ];
                }
                $data[] = [
                    'name' => $configoption['option_name'],
                    'field' => "configoption[{$configoption['id']}]",
                    'type' => 'dropdown',
                    'option' => $subArr
                ];
            }
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$data,
        ];

        return $result;
    }

    # TODO 当前所选配置项,排除数量类型和多选类型,未处理(优惠码使用)
    public function currentConfigOption($host_id)
    {
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('hc.configoption_id,hc.configoption_sub_id')
            ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=hc.configoption_id AND pc.type not in (\'multi_select\',\'quantity\',\'quantity_range\')')
            ->where('hc.host_id',$host_id)
            ->select()
            ->toArray();

        $data = [];
        foreach ($configoptions as $configoption){
            $data[$configoption['configoption_id']] = $configoption['configoption_sub_id'];
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$data,
        ];

        return $result;
    }

    public function configPrice($param)
    {
        $hostId = $param['host_id']??0;

        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        
        $param['cycle'] = $this->getCurrentHostCycle($host);

        $configoptions = $param['configoption']??[];
        // 新增级联配置项参数处理
        $cascadeConfigoptions = $param['cascade_configoption']??[];

        $productId = $host['product_id'];

        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0)
            ->where('cc.id',intval($param['cycle']))
            ->find();

        # 总价
        $price = $oldPrice = 0;

        $description = $preview = [];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        if (!empty($customCycle)){ # 自定义周期

            # 配置项价格
            foreach ($configoptions as $key=>$value){
                $tmp = $IdcsmartCommonProductConfigoptionModel->field('option_name,option_type,fee_type,allow_repeat,max_repeat,unit')->where('id',$key)->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                $tmp['option_name'] = multi_language_replace($tmp['option_name']);
                $tmp['unit'] = multi_language_replace($tmp['unit']);

                // 跳过级联配置项，级联配置项单独处理
                if ($optionType == 'cascade') {
                    continue;
                }

                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->where('ccp.custom_cycle_id',$param['cycle'])
                            ->find();

                        if (!empty($quantityType)){
                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $subPrice = $this->quantityStagePrice($key,$item,$param['cycle'],0,true);
                                $price = bcadd($price,$subPrice,2);
                            }else{ # 数量计费
                                $subPrice = bcmul($quantityType['amount'],$item,2);
                                $price = bcadd($price,$subPrice,2);
                            }
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'] . $k,
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'],
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }
                        }
                    }


                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->withAttr('option_name', function($val){
                            return multi_language_replace($val);
                        })
                        ->withAttr('sub_name', function($val){
                            return multi_language_replace($val);
                        })
                        ->withAttr('unit', function($val){
                            return multi_language_replace($val);
                        })
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $subPrice = isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0;
                        $price = bcadd($price,$subPrice,2);
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }else{
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount,pc.unit,pcs.country')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->withAttr('option_name', function($val){
                            return multi_language_replace($val);
                        })
                        ->withAttr('sub_name', function($val){
                            return multi_language_replace($val);
                        })
                        ->withAttr('unit', function($val){
                            return multi_language_replace($val);
                        })
                        ->find();
                    $subPrice = isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0;
                    $price = bcadd($price,$subPrice,2);

                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;

                        if ($optionType=='area'){
                            $CountryModel = new CountryModel();
                            $country = $CountryModel->where('iso',$configoptionPrice['country'])->find();
                        }

                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $optionType=='area'?$country['name_zh'] ." ".$configoptionPrice['sub_name']:$configoptionPrice['sub_name'] . $configoptionPrice['unit'],
                            'price' => $subPrice
                        ];
                    }

                }
            }

            # 级联配置项价格处理（自定义周期）
            foreach ($cascadeConfigoptions as $configoptionId => $cascadeData) {
                // 获取配置项信息
                $configoptionInfo = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,unit')
                    ->where('id', $configoptionId)
                    ->where('option_type', 'cascade')
                    ->withAttr('option_name', function($val){
                        return multi_language_replace($val);
                    })
                    ->withAttr('unit', function($val){
                        return multi_language_replace($val);
                    })
                    ->find();
                
                if (empty($configoptionInfo)) {
                    continue;
                }
                
                $itemId = $cascadeData['item_id'] ?? 0;
                $quantity = $cascadeData['quantity'] ?? 1;
                
                if (empty($itemId)) {
                    continue;
                }
                
                // 获取级联项信息
                $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($itemId);
                
                if (empty($cascadeItem) || $cascadeItem['is_leaf'] != 1) {
                    continue; // 只处理末端级联项
                }
                
                $feeType = $cascadeItem['fee_type'] ?? 'qty';

                // 获取级联项对应的配置子项价格（自定义周期）
                $cascadeSubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                    ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                    ->where('pcs.cascade_item_id', $itemId)
                    ->where('pcs.hidden', 0)
                    ->where('ccp.custom_cycle_id', $param['cycle'])
                    ->find();

                if (!empty($cascadeSubPrice)) {
                    if ($feeType == 'qty' || $feeType == 'stage') {
                        $quantitySubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                            ->where('pcs.cascade_item_id', $itemId)
                            ->where('pcs.hidden', 0)
                            ->where('pcs.qty_min', '<=', $quantity)
                            ->where('pcs.qty_max', '>=', $quantity)
                            ->where('ccp.custom_cycle_id', $param['cycle'])
                            ->order('pcs.id', 'asc')
                            ->find();

                        if (!empty($quantitySubPrice)) {
                            if ($feeType == 'stage') {
                                $subPrice = $this->cascadeQuantityStagePrice($itemId, $quantity, $param['cycle'], true);
                            } else {
                                $subPrice = bcmul($quantitySubPrice['amount'] ?? 0, $quantity, 2);
                            }
                        } else {
                            $subPrice = 0;
                        }
                    } else {
                        $subPrice = $cascadeSubPrice['amount'] ?? 0;
                    }

                    $price = bcadd($price, $subPrice, 2);
                    $cascadeFullName = $IdcsmartCommonCascadeItemModel->getFullName($itemId);
                    $description[] = $configoptionInfo['option_name'] . '=>' . $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . '=>' . $configoptionInfo['unit'] . '=>' . $subPrice;
                    $preview[] = [
                        'name' => $configoptionInfo['option_name'],
                        'value' => $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . $configoptionInfo['unit'],
                        'price' => $subPrice
                    ];
                }
            }

        }
        else{ # 系统周期(一次性)
            $cycleName = $this->systemCycles[$param['cycle']]??'';
            $cycleTime = $this->systemCycleTime($param['cycle']);
            # 配置项价格
            foreach ($configoptions as $key=>$value){

                $tmp = $IdcsmartCommonProductConfigoptionModel->field('option_name,option_type,fee_type,allow_repeat,max_repeat,unit')->where('id',$key)->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                // 跳过级联配置项，级联配置项单独处理
                if ($optionType == 'cascade') {
                    continue;
                }

                # 数量类型
                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->find();

                        if (!empty($quantityType)){
                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $subPrice = $this->quantityStagePrice($key,$item,$param['cycle']);
                                $price = bcadd($price,$subPrice,2);
                            }else{ # 数量计费
                                $subPrice = bcmul($quantityType[$param['cycle']],$item,2);
                                $price = bcadd($price,$subPrice,2);
                            }
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'] . $k,
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item . '=>' . $tmp['unit'] . '=>' . $subPrice;
                                $preview[] = [
                                    'name' => $tmp['option_name'],
                                    'value' => $item . $tmp['unit'],
                                    'price' => $subPrice
                                ];
                            }
                        }
                    }
                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $subPrice = isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0;
                        $price = bcadd($price,$subPrice,2);
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }else{ # 非数量类型
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,pc.unit')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->find();
                    $subPrice = isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0;
                    $price = bcadd($price,$subPrice,2);

                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'] . '=>' . $configoptionPrice['unit'] . '=>' . $subPrice;
                        $preview[] = [
                            'name' => $configoptionPrice['option_name'],
                            'value' => $configoptionPrice['sub_name'] . $tmp['unit'],
                            'price' => $subPrice
                        ];
                    }
                }
            }

            # 级联配置项价格处理（系统周期）
            foreach ($cascadeConfigoptions as $configoptionId => $cascadeData) {
                // 获取配置项信息
                $configoptionInfo = $IdcsmartCommonProductConfigoptionModel
                    ->field('option_name,option_type,unit')
                    ->where('id', $configoptionId)
                    ->where('option_type', 'cascade')
                    ->withAttr('option_name', function($val){
                        return multi_language_replace($val);
                    })
                    ->withAttr('unit', function($val){
                        return multi_language_replace($val);
                    })
                    ->find();
                
                if (empty($configoptionInfo)) {
                    continue;
                }
                
                $itemId = $cascadeData['item_id'] ?? 0;
                $quantity = $cascadeData['quantity'] ?? 1;
                
                if (empty($itemId)) {
                    continue;
                }
                
                // 获取级联项信息
                $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($itemId);
                
                if (empty($cascadeItem) || $cascadeItem['is_leaf'] != 1) {
                    continue; // 只处理末端级联项
                }
                
                $feeType = $cascadeItem['fee_type'] ?? 'qty';

                // 获取级联项对应的配置子项价格（系统周期）
                $cascadeSubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                    ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                    ->where('pcs.cascade_item_id', $itemId)
                    ->where('pcs.hidden', 0)
                    ->find();

                if (!empty($cascadeSubPrice)) {
                    if ($feeType == 'qty' || $feeType == 'stage') {
                        $quantitySubPrice = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                            ->where('pcs.cascade_item_id', $itemId)
                            ->where('pcs.hidden', 0)
                            ->where('pcs.qty_min', '<=', $quantity)
                            ->where('pcs.qty_max', '>=', $quantity)
                            ->order('pcs.id', 'asc')
                            ->find();

                        if (!empty($quantitySubPrice)) {
                            if ($feeType == 'stage') {
                                $subPrice = $this->cascadeQuantityStagePrice($itemId, $quantity, $param['cycle'], false);
                            } else {
                                $subPrice = bcmul($quantitySubPrice[$param['cycle']] ?? 0, $quantity, 2);
                            }
                        } else {
                            $subPrice = 0;
                        }
                    } else {
                        $subPrice = $cascadeSubPrice[$param['cycle']] ?? 0;
                    }

                    $price = bcadd($price, $subPrice, 2);
                    $cascadeFullName = $IdcsmartCommonCascadeItemModel->getFullName($itemId);
                    $description[] = $configoptionInfo['option_name'] . '=>' . $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . '=>' . $configoptionInfo['unit'] . '=>' . $subPrice;
                    $preview[] = [
                        'name' => $configoptionInfo['option_name'],
                        'value' => $cascadeFullName . (($feeType == 'qty' || $feeType == 'stage') ? ' x' . $quantity : '') . $configoptionInfo['unit'],
                        'price' => $subPrice
                    ];
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $param['cycle']=='free'?0:$price,
                'description' => $description,
                'preview' => $preview
            ]
        ];
        return $result;
    }

    // 计算升降级配置价格
    public function upgradeConfigPrice($param)
    {
        $hostId = $param['host_id'];
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        
        $configoptions = $param['configoption']??[];
        // 新增级联配置项参数处理
        $cascadeConfigoptions = $param['cascade_configoption']??[];
        
        $newPrice = $oldPrice = 0;
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $param['configoption'] = $configoptions;
        // 传递级联配置项参数
        if (!empty($cascadeConfigoptions)) {
            $param['cascade_configoption'] = $cascadeConfigoptions;
        }
        // 获取新的配置的价格
        $new = $this->configPrice($param);
        $newPrice = bcadd($newPrice,$new['data']['price']??0,2);

        // TODO wyh 20231219 商品此配置下原价(需要加上商品价格)
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        
        $param['cycle'] = $this->getCurrentHostCycle($host);

        $productId = $host['product_id'];
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0)
            ->where('cc.id',intval($param['cycle']))
            ->find();


        $description = $new['data']['description']??'';
        // 处理旧配置(仅处理升级时选择的配置项)

        $fun = function($configoptions,$cascadeConfigoptions,$hostId){
            $configoptionsFilter = [];
            $cascadeConfigoptionsFilter = [];
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
            
            // 获取主机上所有配置项
            $allHostConfigoptions = $IdcsmartCommonHostConfigoptionModel->alias('a')
                ->field('a.configoption_id,a.configoption_sub_id,a.cascade_item_id,a.qty,b.option_type')
                ->leftjoin('module_idcsmart_common_product_configoption b','a.configoption_id=b.id')
                ->where('a.host_id',$hostId)
                ->select()->toArray();
            
            // 遍历所有主机配置项
            foreach ($allHostConfigoptions as $hostConfig) {
                $key = $hostConfig['configoption_id'];
                $optionType = $hostConfig['option_type'];
                
                // 如果这个配置项在$configoptions中，处理它
                if (array_key_exists($key, $configoptions)) {
                    if ($this->checkQuantity($optionType)){
                        $multiArr = [];
                        if (!empty($hostConfig)){
                            $multiArr[] = $hostConfig['qty']??0;
                            $configoptionsFilter[$key] = $multiArr;
                        }
                    }elseif ($this->checkMultiSelect($optionType)){
                        // 多选需要查询所有记录
                        $hostLinks = $IdcsmartCommonHostConfigoptionModel->where('host_id',$hostId)
                            ->where('configoption_id',$key)
                            ->select()
                            ->toArray();
                        $multiArr= [];
                        foreach ($hostLinks as $item){
                            $multiArr[] = $item['configoption_sub_id'];
                        }
                        $configoptionsFilter[$key] = $multiArr;
                    }else{
                        if (!empty($hostConfig)){
                            $configoptionsFilter[$key] = $hostConfig['configoption_sub_id'];
                        }
                    }
                }
                
                // 级联配置项单独处理，只返回$cascadeConfigoptions中指定的级联配置项
                if ($optionType == 'cascade' && array_key_exists($key, $cascadeConfigoptions) && !empty($hostConfig['cascade_item_id'])) {
                    $cascadeConfigoptionsFilter[$key] = ['item_id' => $hostConfig['cascade_item_id'], 'quantity' => $hostConfig['qty'] ?? 1];
                }
            }
            
            return ['configoption' => $configoptionsFilter, 'cascade_configoption' => $cascadeConfigoptionsFilter];
        };

        // 获取旧配置的价格
        $oldFunResult = $fun($configoptions,$cascadeConfigoptions,$hostId);
        $old = $this->configPrice([
            'host_id' => $param['host_id'],
            'configoption' => $oldFunResult['configoption'],
            'cascade_configoption' => $oldFunResult['cascade_configoption'],
        ]);

        $oldPrice = bcadd($oldPrice,$old['data']['price']??0,2);

        // wyh 20240523 加上前端未提交的配置价格，隐藏配置项不计算
        $postConfigoptionIds = array_keys($configoptions);
        $postCascadeConfigoptionIds = array_keys($cascadeConfigoptions);
        $allPostConfigoptionIds = array_merge($postConfigoptionIds, $postCascadeConfigoptionIds);
        
        $oldConfigoptionsRaw = $IdcsmartCommonHostConfigoptionModel->alias('a')
            ->field('a.configoption_id,a.configoption_sub_id,a.cascade_item_id,a.qty,b.option_type')
            ->leftjoin('module_idcsmart_common_product_configoption b','a.configoption_id=b.id')
            ->where('a.host_id',$hostId)
            ->whereNotIn('a.configoption_id',$allPostConfigoptionIds)
            ->where('b.hidden',0)
            ->select()->toArray();
        
        // 分离普通配置项和级联配置项
        $oldConfigoptions = [];
        $unpostCascadeConfigoptions = [];
        foreach ($oldConfigoptionsRaw as $item) {
            if ($item['option_type'] == 'cascade') {
                if (!empty($item['cascade_item_id'])) {
                    $unpostCascadeConfigoptions[$item['configoption_id']] = [
                        'item_id' => $item['cascade_item_id'],
                        'quantity' => $item['qty'] ?? 1
                    ];
                }
            } else {
                $oldConfigoptions[$item['configoption_id']] = $item['configoption_sub_id'];
            }
        }
        
        $oldUnpostFunResult = $fun($oldConfigoptions,$unpostCascadeConfigoptions,$hostId);
        $oldUnpostConfigoptionsPrice = $this->configPrice([
            'host_id' => $param['host_id'],
            'configoption' => $oldUnpostFunResult['configoption'],
            'cascade_configoption' => $unpostCascadeConfigoptions,
        ]);
        $newPrice = bcadd($newPrice,$oldUnpostConfigoptionsPrice['data']['price']??0,2);
        $oldPrice = bcadd($oldPrice,$oldUnpostConfigoptionsPrice['data']['price']??0,2);
        $basePrice = bcadd($newPrice,$customCycle['amount']??0,2);

        if ($host['billing_cycle']=='recurring_prepayment' || $host['billing_cycle']=='recurring_postpaid'){
            $product = ProductModel::find($host['product_id']);
            $isNaturalMonthPrepaid = $product['natural_month_prepaid'] ?? 0;

            // 续费差价
            $renewPriceDifference = bcsub($newPrice,$oldPrice,2);

            if($isNaturalMonthPrepaid){
                $idcsmartCommonCustomCycle = IdcsmartCommonCustomCycleModel::find($param['cycle']);

                $priceDifference = calculate_natural_month_price($renewPriceDifference, time(), $host['due_time'], $idcsmartCommonCustomCycle['cycle_time'] ?? 0);
            }else{
                $oldPrice = $oldPrice / $host['billing_cycle_time'] * ($host['due_time']-time()); # 旧配置剩余时间费用
                $newPrice = bcsub($newPrice / $host['billing_cycle_time'] * ($host['due_time']-time()),0,2); # 新配置剩余时间所需费用

                $priceDifference = bcsub($newPrice>0?$newPrice:0,$oldPrice>0?$oldPrice:0,2);
            }
        }elseif($host['billing_cycle']=='onetime'){ // 一次性直接计算差价
            // 续费差价
            $renewPriceDifference = bcsub($newPrice,$oldPrice,2);

            $priceDifference = bcsub($newPrice,$oldPrice,2);
        }else{
            // 续费差价
            $renewPriceDifference = 0;

            $priceDifference = 0;
        }

        $preview = $new['data']['preview']??[];
        $priceDifferenceClientLevelResults = hook("client_discount_by_amount",[
            'client_id'=>get_client_id(),
            'product_id'=>$productId,
            'amount'=>$priceDifference
        ]);
        foreach ($priceDifferenceClientLevelResults as $priceDifferenceClientLevelResult){
            if ($priceDifferenceClientLevelResult['status']==200){
                $priceDifferenceClientLevelDiscount = $priceDifferenceClientLevelResult['data']['discount']??0;
            }
        }
        $renewPriceDifferenceClientLevelResults = hook("client_discount_by_amount",[
            'client_id'=>get_client_id(),
            'product_id'=>$productId,
            'amount'=>$renewPriceDifference
        ]);
        foreach ($renewPriceDifferenceClientLevelResults as $renewPriceDifferenceClientLevelResult){
            if ($renewPriceDifferenceClientLevelResult['status']==200){
                $renewPriceDifferenceClientLevelDiscount = $renewPriceDifferenceClientLevelResult['data']['discount']??0;
            }
        }
        $basePriceDifferenceClientLevelResults = hook("client_discount_by_amount",[
            'client_id'=>get_client_id(),
            'product_id'=>$productId,
            'amount'=>$basePrice
        ]);
        foreach ($basePriceDifferenceClientLevelResults as $basePriceDifferenceClientLevelResult) {
            if ($basePriceDifferenceClientLevelResult['status'] == 200) {
                $basePriceDifferenceClientLevelDiscount = $basePriceDifferenceClientLevelResult['data']['discount'] ?? 0;
            }
        }
        // wyh 20250827 新增
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        // 是下游请求过来
        if (isset($param['is_downstream']) && $param['is_downstream']==1 && $priceAgent){
            // wyh 20240517 等级折扣（注意：升降级采取的是在差价上进行折扣）
            // 1、升降级金额折扣
            $priceDifference = bcsub($priceDifference,$priceDifferenceClientLevelDiscount??0,2);
            // 2、续费金额差价折扣
            $renewPriceDifference = bcsub($renewPriceDifference,$renewPriceDifferenceClientLevelDiscount??0,2);
            // 3、升降级后原价折扣
            $basePrice = bcsub($basePrice,$basePriceDifferenceClientLevelDiscount??0,2);
            // 4、配置显示问题
            $description = [];
            foreach ($preview as &$item){
                $previewPriceClientLevelResults = hook("client_discount_by_amount",[
                    'client_id'=>get_client_id(),
                    'product_id'=>$productId,
                    'amount'=>$item['price']
                ]);
                foreach ($previewPriceClientLevelResults as $previewPriceClientLevelResult){
                    if ($previewPriceClientLevelResult['status']==200){
                        $item['price'] = bcsub($item['price'],$previewPriceClientLevelResult['data']['discount']??0,2);
                    }
                }
                $description[] = $item['name'].'=>'.$item['value'].'=>'.$item['price'];
            }

        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                //'price' => $newPrice,
                //'price_client_level_discount' => $price_difference_client_level_discount??0,
                // 信息
                'description' => $description,
                'preview' => $preview,
                // 基础
                'price_difference' => $priceDifference,
                'renew_price_difference' => $renewPriceDifference,
                'new_first_payment_amount' => $renewPriceDifference,
                'base_price' => $basePrice,
                // 折扣
                'price_difference_client_level_discount' => $priceDifferenceClientLevelDiscount??0,
                'renew_price_difference_client_level_discount' => $renewPriceDifferenceClientLevelDiscount??0,
                'new_first_payment_amount_client_level_discount' => $renewPriceDifferenceClientLevelDiscount??0,
                'base_price_client_level_discount' => $basePriceDifferenceClientLevelDiscount??0,
            ]
        ];
        return $result;
    }

    // 获取产品当前周期标识
    public function getCurrentHostCycle($host)
    {
        $product = ProductModel::find($host['product_id']);

        if ($host['billing_cycle']=='recurring_prepayment' || $host['billing_cycle']=='recurring_postpaid'){
            $isNaturalMonthPrepaid = $product['natural_month_prepaid'] ?? 0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            if($isNaturalMonthPrepaid){
                $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$host['product_id'])
                    ->where('name',$host['billing_cycle_name'])
                    ->where('cycle_type', 1)
                    ->find();
            }else{
                $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$host['product_id'])
                    ->where('name',$host['billing_cycle_name'])
                    ->where('cycle_type', 0)
                    ->find();
            }
            $cycle = $customCycle['id'] ?? 0;
        }elseif($host['billing_cycle']=='onetime'){
            $cycle='onetime';
        }else{
            $cycle='free';
        }
        return $cycle;
    }

}


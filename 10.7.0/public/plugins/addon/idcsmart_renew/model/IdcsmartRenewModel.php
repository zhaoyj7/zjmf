<?php
namespace addon\idcsmart_renew\model;

use addon\promo_code\model\PromoCodeModel;
use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductDurationRatioModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use app\common\model\ClientModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use app\common\model\HostIpModel;
use PayPal\Api\Amount;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-02
 */
class IdcsmartRenewModel extends Model
{
    protected $name = 'addon_idcsmart_renew';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'client_id'                        => 'int',
        'host_id'                          => 'int',
        'new_billing_cycle'                => 'string',
        'new_billing_cycle_time'           => 'int',
        'new_billing_cycle_amount'         => 'float',
        'status'                           => 'string',
        'create_time'                      => 'int',
        'base_price'                       => 'float',
        'client_level_discount'            => 'float',
        'is_continuous_renew'              => 'int',
        'natural_month_due_time'           => 'int', // 自然月预付费精确到期时间
    ];

    public $isAdmin = false;

    /**
     * 处理可续费周期(过滤规则)
     * @param HostModel $host 产品实例
     * @param array $cycles 周期列表
     * @param string $promoCode 优惠码
     * @param string $duration 周期时长
     * @param string $scene 场景(batch_renew等)
     * @param bool $isRenew 是否为续费操作(true=是,false=续费页面)
     * @param array $usePromoCodeArr 已使用的优惠码数组(引用传递)
     * @param int $used 已使用次数(引用传递)
     * @return array 处理后的周期列表
     * @author hh
     */
    public function cyclesFilter(HostModel $host,$cycles,$promoCode='',$duration='',$scene='',$isRenew=false,&$usePromoCodeArr=[],&$used=0): array
    {
        // 1. 验证并清理周期数据
        $cycles = $this->validateCycles($cycles);
        if (empty($cycles)) {
            return [];
        }
        
        // 2. 初始化上下文数据
        $PromoCodeModel = new PromoCodeModel();
        $promoCodeInfo = $PromoCodeModel->where('code',$promoCode)->find();
        $promoCodeInfo = $promoCodeInfo ? $promoCodeInfo->toArray() : [];
        
        // 标记是否成功应用了手动输入的优惠码
        $isPromoCodeApplied = false;
        $hasCustomPromocode = false;
        $loop = false;
        
        // 用于过滤小于最小周期的周期
        $maxDuration = null;
        
        // 用于比例续费
        $currentRenewAmount = null;
        
        // 获取价格基准参数
        $baseParam = request()->param();
        $priceBasis = $baseParam['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis == 'agent';
        
        // 订单子项模型,用于应用了
        $OrderItemModel = new OrderItemModel();

        // 3. 处理每个周期
        foreach ($cycles as $k2 => $item2) {
            // 3.1 初始化周期价格（包含子产品）
            $cycles[$k2] = $this->initializeCyclePrices($item2, $cycles[$k2], $host);
            
            // 3.2 计算 renewAmount（包含子产品）
            $renewAmount = $host->renew_amount;
            if (isset($cycles[$k2]['son_host_instance'])) {
                $renewAmount += $cycles[$k2]['son_host_instance']['renew_amount'];
            }
            
            // 3.3 确定折扣计算的基数
            if (!empty($item2['is_natural_month_prepaid'])) {
                $discountBase = $cycles[$k2]['price'];
            } else {
                $discountBase = $cycles[$k2]['base_price'];
            }
            
            // 3.4 设置 current_base_price（格式化后的原价）
            $cycles[$k2]['current_base_price'] = amount_format($discountBase);
            
            // 3.5 处理连续续费
            if (!empty($item2['is_continuous_renew'])) {
                $cycles[$k2]['price'] = $item2['price'];
            }
            
            // 3.6 判断是否当前周期
            $isCurrentCycle = $this->isCurrentCycle($host, $item2);
            // 保存原始的当前周期判断结果（用于 is_current_cycle 字段）
            $originalIsCurrentCycle = $isCurrentCycle;
            
            // 3.7 处理当前周期的最大周期过滤
            if ($isCurrentCycle) {
                if (empty($item2['is_natural_month_prepaid']) && $renewAmount > $item2['price']) {
                    $maxDuration = $item2['duration'];
                }
            }
            
            // 3.8 获取优惠码信息
            $hasManualPromoCode = !empty($promoCode);
            
            if ($hasManualPromoCode) {
                // 检查是否应该跳过优惠码
                if ($this->shouldSkipPromoCode($promoCodeInfo, $scene, $usePromoCodeArr, $used)) {
                    $hasManualPromoCode = false;
                    $manualPromoCodeInfo = [
                        'success' => false,
                        'is_loop' => false,
                        'exclude' => false,
                        'current_discount' => 0,
                        'renew_discount' => 0,
                        'promo_code_id' => 0,
                    ];
                } else {
                    // 手动优惠码
                    $manualPromoCodeInfo = $this->getManualPromoCodeInfo(
                        $host,
                        $promoCode,
                        $discountBase,
                        $cycles[$k2]['base_price'],
                        $duration
                    );
                    
                    if ($manualPromoCodeInfo['success']) {
                        $isPromoCodeApplied = true;
                        $hasCustomPromocode = true;
                        $loop = $manualPromoCodeInfo['is_loop'];
                        $isCurrentCycle = false; // 使用优惠后金额
                    }
                }
                
                $autoPromoCodeInfo = [
                    'is_loop' => false,
                    'exclude' => false,
                    'current_discount' => 0,
                    'renew_discount' => 0,
                ];
            } else {
                // 自动优惠码（只在非当前周期时获取）
                $manualPromoCodeInfo = [
                    'success' => false,
                    'is_loop' => false,
                    'exclude' => false,
                    'current_discount' => 0,
                    'renew_discount' => 0,
                    'promo_code_id' => 0,
                ];
                
                if (!$isCurrentCycle) {
                    $autoPromoCodeInfo = $this->getAutoPromoCodeInfo(
                        $host,
                        $item2,
                        $cycles[$k2]['price'],
                        $cycles[$k2]['base_price']
                    );
                } else {
                    $autoPromoCodeInfo = [
                        'is_loop' => false,
                        'exclude' => false,
                        'current_discount' => 0,
                        'renew_discount' => 0,
                    ];
                }
            }
            
            // 3.9 计算用户等级折扣
            $promoCodeInfo = $hasManualPromoCode ? $manualPromoCodeInfo : $autoPromoCodeInfo;
            
            $clientLevelDiscount = $this->calculateClientLevelDiscount(
                $host,
                $cycles[$k2],
                $discountBase,
                $promoCodeInfo,
                $scene,
                $priceAgent
            );
            
            // 3.10 统一计算价格（包含子产品）
            $cycles[$k2] = $this->calculateCyclePrice(
                $host,
                $cycles[$k2],
                $isCurrentCycle,
                $hasManualPromoCode,
                $manualPromoCodeInfo,
                $autoPromoCodeInfo,
                $clientLevelDiscount,
                $scene,
                $priceAgent
            );
            
            // 3.11 设置下游产品的基础价格折扣
            if (isset($host['downstream_host_id']) && $host['downstream_host_id'] > 0) {
                $cycles[$k2]['base_price_discount'] = $cycles[$k2]['client_level_discount'];
            }
            
            // 3.12 处理当前周期的价格（使用 renew_amount）
            if ($isCurrentCycle) {
                // 计算 priceSave
                if (isset($cycles[$k2]['son_host_instance'])) {
                    $priceSave = bcsub($renewAmount, $cycles[$k2]['son_host_instance']['renew_amount'] ?? 0, 2);
                } else {
                    $priceSave = bcsub($renewAmount, 0, 2);
                }
                $renewAmount = amount_format($renewAmount);
                
                // 当前周期使用 renew_amount
                $cycles[$k2]['price'] = $renewAmount;
                $cycles[$k2]['price_save'] = $priceSave;
                $cycles[$k2]['renew_amount'] = $renewAmount;
            } else {
                // 其他周期使用计算后的价格
                $cycles[$k2]['renew_amount'] = $cycles[$k2]['base_price'];
            }
            
            // 3.13 设置标识字段
            // max_renew: 当前周期且未使用手动优惠码时为true，表示续费金额已经减了客户等级折扣，不需要再减一次
            // 使用优惠码后为false，因为需要重新计算价格
            $cycles[$k2]['max_renew'] = $isCurrentCycle;
            
            // is_current_cycle: 标识是否是产品当前使用的周期（用于订单子项金额判断）
            // 始终使用原始判断结果，不受手动优惠码影响
            $cycles[$k2]['is_current_cycle'] = $originalIsCurrentCycle ? 1 : 0;

            // 手动优惠码标识：用于前端判断是否传入了优惠码及是否应用成功
            $cycles[$k2]['has_manual_promo_code'] = !empty($promoCode) ? 1 : 0;
            $cycles[$k2]['manual_promo_code_success'] = (!empty($promoCode) && !empty($manualPromoCodeInfo['success'])) ? 1 : 0;
            
            // 3.14 保存 currentRenewAmount（用于比例续费）
            if ($isCurrentCycle) {
                if ($host['ratio_renew'] == 1) {
                    $currentRenewAmount = $renewAmount;
                } else {
                    $currentRenewAmount = $cycles[$k2]['renew_amount'];
                }
            }

            // 保留原来续费逻辑，创建续费订单的时候就影响
            // 只要续费手动输入了 可用优惠码，就会覆盖原来的产品优惠码以及续费或者升降级使用的！，下次续费 方便其他周期计算
            if($isRenew && $manualPromoCodeInfo['success']){
                // 也就是说，这里可能会更新多条数据！最终产品的所有使用优惠码的子项都会更新为当前优惠码！使用其中一个继续计算就行
                $OrderItemModel->where('host_id',$host['id'])
                    ->where('type','addon_promo_code')
                    ->update([
                        'rel_id' => $manualPromoCodeInfo['promo_code_id']
                    ]);
            }
        }
        
        // 4. 过滤小于最小周期的周期 wyh 20260303 注释
//        if (isset($maxDuration)) {
//            foreach ($cycles as $k3 => $item3) {
//                if ($item3['duration'] < $maxDuration) {
//                    unset($cycles[$k3]);
//                }
//            }
//        }
        
        $cycles = array_values($cycles);
        
        // 5. 处理比例续费
        if (!($hasCustomPromocode && $loop)) {
            if ($host['ratio_renew'] == 1 && isset($currentRenewAmount)) {
                foreach ($cycles as &$item4) {
                    // 按原价续费
                    if (isset($item4['renew_cal_price']) && $item4['renew_cal_price'] == 1) {
                        continue;
                    }
                    
                    if (empty($hasCustomPromocode)) {
                        if (isset($item4['prr_numerator']) && isset($item4['prr_denominator'])) {
                            $item4['price'] = bcdiv($currentRenewAmount * $item4['prr_numerator'], $item4['prr_denominator'], 2);
                        } else {
                            $item4['price'] = bcmul($currentRenewAmount, $item4['prr'] ?? 1, 2);
                        }
                    }
                    
                    // 保存金额也要变化
                    if (isset($item4['prr_numerator']) && isset($item4['prr_denominator'])) {
                        $item4['price_save'] = bcdiv($currentRenewAmount * $item4['prr_numerator'], $item4['prr_denominator'], 2);
                    } else {
                        $item4['price_save'] = bcmul($currentRenewAmount, $item4['prr'] ?? 1, 2);
                    }
                }
            }
        }
        
        // 6. 记录已成功应用的优惠码
        if ($isPromoCodeApplied && $promoCode) {
            $usePromoCodeArr[] = $promoCode;
            $used++;
        }
        
        return $cycles ?: [];
    }

    /**
     * 时间 2022-06-02
     * @title 续费页面
     * @desc 续费页面
     * @author wyh
     * @version v1
     * @param int id - 产品ID required
     * @return array host -
     * @return float host[].price 0.01 实际支付的金额
     * @return string host[].billing_cycle 小时 周期
     * @return int host[].duration 3600 周期时间
     * @return float host[].base_price - 基础原价(不包括优惠码，客户等级等折扣)
     * @return float host[].current_base_price - 当前原价(自然月预付费为折算后的原价，普通周期为完整周期原价，不包括优惠码和客户等级折扣)
     * @return int host[].id - 周期比例ID
     * @return string host[].name_show - 周期名字显示
     * @return float host[].prr - 与产品当前周期比例的比值（后台产品内页开启按比例续费的功能会使用）
     * @return float host[].price_save - 保存至数据库的续费金额
     * @return float host[].renew_amount - 续费金额(自有软件使用)
     * @return float host[].client_level_discount - 当前价格的用户等级折扣金额
     * @return float host[].renew_client_level_discount - 续费价格的用户等级折扣金额(用于下次续费计算和问题排查)
     * @return boolean host[].max_renew - 当前周期，续费金额已经减了客户等级折扣金额，所以不需要再减一次(当前周期为true，其他周期为false，手动输入优惠码时，也为false)
     */
    public function renewPage($param)
    {
        $id = $param['id'];
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $clientId = $this->isAdmin?$host->client_id:get_client_id();
        if ($host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 产品周期一次性/按需不可续费
        if (in_array($host->billing_cycle, ['onetime','on_demand'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 申请了转按需不能续费
        if($host['change_billing_cycle_id'] > 0){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        $ModuleLogic = new ModuleLogic();
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])
            ->where('mode','only_api')
            ->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->durationPrice($host);
        }else{
            $result = $ModuleLogic->durationPrice($host);
        }
        if ($result['status'] != 200){
            return ['status'=>400,'msg'=>$result['msg']?:lang_plugins('get_fail')];
        }

        # 处理可续费周期
        $cycles = $result['data']?:[];
        # TODO wyh 20231124 可续费周期
        if (empty($cycles)){
            $cycles = [
               [
                   'billing_cycle' => $host['billing_cycle_name'],
                   'price' => $host['base_price'],//$host['renew_amount'],
                   'duration' => $host['billing_cycle_time'],
                   'base_price' => $host['base_price']
               ]
            ];
        }

        $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['host'=>$cycles]];
    }

    /**
     * 时间 2022-06-02
     * @title 续费
     * @desc 续费
     * @author wyh
     * @version v1
     * @param int id - 产品ID required
     * @param string billing_cycle - 周期(通用产品是中文，云产品是英文;这里要注意，根据续费页面返回的周期来传，不停的模块可能传的不一样) required
     * @param object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     */
    public function renew($param)
    {
        $id = $param['id'];
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $clientId = $this->isAdmin?$host->client_id:get_client_id();
        if ($host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidOnDemandOrder($clientId)){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_order_cannot_do_this') ];
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 产品周期一次性/按需不可续费
        if (in_array($host->billing_cycle, ['onetime','on_demand'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 申请了转按需不能续费
        if($host['change_billing_cycle_id'] > 0){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 判断周期
        if (!isset($param['billing_cycle']) || empty($param['billing_cycle'])){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }
        $billingCycle = $param['billing_cycle'];

        $ModuleLogic = new ModuleLogic();
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if($upstreamProduct){
            if ($upstreamProduct['mode']=='only_api'){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->durationPrice($host);
            }else{
                $result = $ModuleLogic->durationPrice($host);
            }

        }else{
            $result = $ModuleLogic->durationPrice($host);
        }
        $cycles = $result['status'] == 200 ? $result['data'] :[];
        # TODO wyh 20231124 可续费周期
        if (empty($cycles)){
            $cycles = [
                [
                    'billing_cycle' => $host['billing_cycle_name'],
                    'price' => $host['base_price'],//$host['renew_amount'],
                    'duration' => $host['billing_cycle_time'], // 当模块返回周期为空时，只能取产品续费周期，所以无法按自然月处理
                    'base_price' => $host['base_price']
                ]
            ];
        }
        foreach ($cycles as $cycle){
            if ($billingCycle == $cycle['billing_cycle']){
                $duration = $cycle['duration'];
            }
        }
        $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'',$duration??'','',true);

        $billingCycleAllow = array_column($cycles,'billing_cycle');

        if (empty($billingCycleAllow)){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }
        if (!in_array($billingCycle,$billingCycleAllow)){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }

        $result = hook('before_host_renew', ['host_id'=>$id]);

        foreach ($result as $value){
            if (isset($value['status']) && $value['status']==400){
                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message')];
            }
        }

        # 获取金额
        $maxRenew = false;
        $discountRenewPrice = '0.00';
        $naturalMonthDueTime = 0; // 自然月预付费精确到期时间
        $clientLevelDiscount = '0.00'; // 用户等级折扣金额
        // $clientLevel = [];
        foreach ($cycles as $value){
            if ($billingCycle == $value['billing_cycle']){
                $amount = $value['price']; // 实际支付
                if (isset($value['son_host_id']) && $value['son_host_id']){
                    $basePrice = $value['base_price']-$value['son_base_price'];
                }else{
                    $basePrice = $value['base_price']; // 原价
                }
                $amountSave = $value['price_save']; // 保存至host表的amount字段
                // 自然月预付费 + 换周期：订单子项需要使用折算后但未减用户等级折扣的价格
                // 当前周期或普通周期：使用 renew_amount
                if(!empty($value['is_natural_month_prepaid']) && empty($value['is_current_cycle'])){
                    // 自然月预付费 + 换周期：price 是最终支付价格（已减用户等级折扣和优惠码折扣），需要加回所有折扣得到折算后原价
                    $renewAmount = bcadd($value['price'], $value['client_level_discount']??0, 2);
                    $renewAmount = bcadd($renewAmount, $value['promo_code_discount']??0, 2);
                }else{
                    // 当前周期或普通周期：使用 renew_amount（可手动设置的续费价格）
                    $renewAmount = $value['renew_amount'];
                }

                $maxRenew = $value['max_renew'];
                $profit = $value['profit'] ?? 0;
                $dueTime = $value['duration'];
                $isContinuousRenew = $value['is_continuous_renew_tag']??0;
                $totalNotes = $value['total_notes'] ?? 0;
                $discountRenewPrice = $value['discount_renew_price'] ?? '0.00';
                // 如果是自然月预付费，保存模块返回的精确到期时间
                if(!empty($value['is_natural_month_prepaid']) && !empty($value['due_time'])){
                    $naturalMonthDueTime = $value['due_time'];
                }
//                $clientLevelDiscount = $value['client_level_discount'];
                // if(!empty($value['client_level_discount'])){
                //     $clientLevelDiscount = $value['client_level_discount'];

                //     $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                //     $clientLevel = $IdcsmartClientLevelModel->clientDiscount([
                //         'client_id'     => $clientId,
                //         'product_id'    => $host['product_id'],
                //     ]);
                // }
                break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
            }
        }

        // 自定义续费金额
        if ($this->isAdmin && isset($param['custom_amount']) && $param['custom_amount']>=0){
            $amountSave = $amount = $basePrice = $param['custom_amount'];
            $renewAmount = $param['custom_amount'];
        }

        # 订单子项
        $orderItems = [];

        $this->startTrans();

        try{
            $this->deleteHostUnpaidUpgradeOrder($id);
            $this->deleteUnpaidRenewOrder($id);
            # 续费记录
            $renew = $this->create([
                'client_id' => $clientId,
                'host_id' => $id,
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime,
                'new_billing_cycle_amount' => $amountSave,// $amount,
                'status' => 'Pending',
                'create_time' => time(),
                'base_price' => $basePrice,
                'client_level_discount' => $discountRenewPrice > 0 ? $discountRenewPrice : 0,
                'is_continuous_renew' => $isContinuousRenew??0,
                'natural_month_due_time' => $naturalMonthDueTime ?? 0, // 自然月预付费精确到期时间
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($host['product_id']);

            if (isset($product['renew_rule'])){
                if ($product['renew_rule']=='due'){
                    $beginTime = date('Y/m/d',$host->due_time);
                    $endTime = date('Y/m/d',$host->due_time+$dueTime);
                }else{
                    # 到期时间描述,应该和实际的有差异 TODO
                    if ($host->status == 'Suspended' || time() >= $host->due_time){
                        $beginTime = date('Y/m/d',time());
                        $endTime = date('Y/m/d',time()+$dueTime);
                    }else{
                        $beginTime = date('Y/m/d',$host->due_time);
                        $endTime = date('Y/m/d',$host->due_time+$dueTime);
                    }
                }
            }else{
                # 到期时间描述,应该和实际的有差异 TODO
                if ($host->status == 'Suspended' || time() >= $host->due_time){
                    $beginTime = date('Y/m/d',time());
                    $endTime = date('Y/m/d',time()+$dueTime);
                }else{
                    $beginTime = date('Y/m/d',$host->due_time);
                    $endTime = date('Y/m/d',$host->due_time+$dueTime);
                }
            }

            $HostIpModel = new HostIpModel();
            $hostIp = $HostIpModel->where('host_id', $id)->find();

            $hostName = $host['name'];
            if(!empty($hostIp) && !empty($hostIp['dedicate_ip'])){
                $hostName = $host['name'].', IP: '.$hostIp['dedicate_ip'];
            }
            if (!empty($totalNotes) && $totalNotes>0){
                $hostName .= ',节点数量:'.$totalNotes;
            }

            // 自然月预付费使用精确到期时间
            if($naturalMonthDueTime > 0){
                $endTime = date('Y/m/d', $naturalMonthDueTime);
            }

            $orderItems[] = [
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'type' => 'renew',
                'rel_id' => $renew->id,
                'amount' => $renewAmount,
                //  'amount' => $amount,
                'description' => lang_plugins('host_renew_description',['{product_name}'=>$product['name'],'{name}'=>$hostName,'{billing_cycle_name}'=>$billingCycle,'{time}'=>$beginTime. '-' . $endTime]),
            ];
            
            // 添加用户等级折扣子项
            // if ($maxRenew) {
            //     // 计算实际差额（原价 - 续费金额）
            //     $actualDiff = bcsub($basePrice, $renewAmount, 2);
                
            //     // 只有当原价 > 续费价格时，才显示折扣
            //     if (bccomp($actualDiff, 0, 2) > 0 && bccomp($actualDiff, $clientLevelDiscount, 2) == 0) {
            //         // 获取用户等级信息
            //         $clientLevelName = '';
            //         $clientLevelId = 0;
            //         $PluginModel = new PluginModel();
            //         $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
            //         if (!empty($plugin) && class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')) {
            //             $IdcsmartClientLevelClientLinkModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel();
            //             $clientLink = $IdcsmartClientLevelClientLinkModel->alias('a')
            //                     ->field('b.id,b.name,c.product_id,d.discount_percent')
            //                     ->leftJoin('addon_idcsmart_client_level b', 'a.addon_idcsmart_client_level_id=b.id')
            //                     ->leftJoin('addon_idcsmart_client_level_product_group_link d', 'a.addon_idcsmart_client_level_id=d.addon_idcsmart_client_level_id')
            //                     ->leftJoin('addon_idcsmart_client_level_product_link c', 'c.addon_idcsmart_client_level_product_group_id=d.addon_idcsmart_client_level_product_group_id')
            //                     ->where('a.client_id', $clientId)
            //                     ->where('c.product_id', $host['product_id'])
            //                     //->where('b.discount_status', 1)
            //                     ->find();
            //             if (!empty($clientLink)) {
            //                 $clientLevelName = $clientLink['name'];
            //                 $clientLevelId = $clientLink['id'];

            //                 $orderItems[] = [
            //                     'host_id' => $id,
            //                     'product_id' => $host['product_id'],
            //                     'type' => 'addon_idcsmart_client_level',
            //                     'rel_id' => $clientLevelId,
            //                     'amount' => -$actualDiff,
            //                     'description' => lang_plugins('client_level_use_success_percent_description', ['{name}' => $clientLevelName, '{host_id}'=>$id, '{value}'=>$clientLink['discount_percent']]),
            //                 ];
            //                 // 修改第一个子项为原价
            //                 $orderItems[0]['amount'] = $basePrice;
            //             }
            //         }
            //     }
            // }

            # 创建订单
            $data = [
                'type' => 'renew',
                'amount' => $amount,
                'gateway' => '',
                'client_id' => $clientId,
                'items' => $orderItems
            ];

            $OrderModel = new OrderModel();
            $orderId = $OrderModel->createOrderBase($data);
            if($upstreamProduct){
                UpstreamOrderModel::create([
                    'supplier_id' => $upstreamProduct['supplier_id'],
                    'order_id' => $orderId,
                    'host_id' => $host->id,
                    'amount' => $amount,
                    'profit' => $profit,
                    'create_time' => time()
                ]);
            }

            // 20230509 wyh
            if (!$maxRenew){
                // 若不换周期，使用新的优惠码(已经变成更新后的优惠码了)
                // 若换周期，使用旧的循环优惠码或者新的优惠码
                $OrderItemModel = new OrderItemModel();
                $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
                    ->where('host_id',$host['id'])
                    ->where('type','addon_promo_code')
                    ->find();
                $PromoCodeModel = new PromoCodeModel();
                if (!empty($orderItem)){
                    $promoCode = $PromoCodeModel->find($orderItem['rel_id']);
                }
                $param['customfield']['promo_code'] = (isset($param['customfield']['promo_code']) && !empty($param['customfield']['promo_code']))?$param['customfield']['promo_code']:($promoCode['code']??'');

            }

            $param['customfield']['max_renew'] = $maxRenew??false;
            $param['customfield']['renew_due_time'] = $dueTime;
            
            // 如果是自然月预付费，保存模块返回的精确到期时间
            foreach ($cycles as $value){
                if ($billingCycle == $value['billing_cycle']){
                    if(!empty($value['is_natural_month_prepaid']) && !empty($value['due_time'])){
                        $param['customfield']['renew_due_time'] = $value['due_time'];
                    }
                    break;
                }
            }

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($orderId);

            // 自动续费
            if(isset($param['auto_renew'])){
                # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
                $amount = $OrderModel->where('id',$orderId)->value('amount');

                if($amount>0){
                    $autoRenew = false;

                    // 1、先余额支付
                    $client = ClientModel::find($clientId);
                    if($client['credit']>=$amount){
                        $res = update_credit([
                            'type' => 'Applied',
                            'amount' => -$amount,
                            'notes' => lang('order_apply_credit')."#{$orderId}",
                            'client_id' => $clientId,
                            'order_id' => $orderId,
                            'host_id' => 0,
                        ]);
                        if($res){
                            $OrderModel->update([
                                'status' => 'Paid',
                                'credit' => $amount,
                                'amount_unpaid'=>0,
                                'pay_time' => time(),
                                'update_time' => time(),
                                'gateway' => 'credit',
                                'gateway_name' => lang('credit_payment'),
                            ], ['id' => $orderId]);
                            $autoRenew = true;
                        }
                    }
                    
                    // 2. 如果余额支付失败，降级到信用额支付
                    if (!$autoRenew){
                        $creditLimitAutoRenewEnable = configuration('addon_credit_limit_auto_renew_enable');
                        if($creditLimitAutoRenewEnable == 1){
                            // 检查信用额插件是否存在并启用
                            $PluginModel = new PluginModel();
                            $creditLimitPlugin = $PluginModel->where('name', 'CreditLimit')->where('module', 'addon')->where('status', 1)->find();

                            if(!empty($creditLimitPlugin) && class_exists('addon\credit_limit\model\CreditLimitModel')){
                                try {
                                    $CreditLimitModel = new \addon\credit_limit\model\CreditLimitModel();
                                    $creditLimitResult = $CreditLimitModel->pay(['id' => $orderId], true);

                                    if($creditLimitResult['status'] == 200){
                                        $autoRenew = true;
                                        // 记录信用额自动续费日志
                                        active_log(lang_plugins('renew_credit_limit_auto_renew_success', [
                                            '{host_id}' => $id,
                                            '{order_id}' => $orderId,
                                            '{amount}' => configuration('currency_prefix').$amount.configuration('currency_suffix')
                                        ]), 'addon_idcsmart_renew', $renew->id);
                                    }else{
                                        // 记录信用额支付失败日志，但不中断流程
                                        active_log(lang_plugins('renew_credit_limit_auto_renew_fail', [
                                            '{host_id}' => $id,
                                            '{order_id}' => $orderId,
                                            '{msg}' => $creditLimitResult['msg'] ?? ''
                                        ]), 'addon_idcsmart_renew', $renew->id);
                                    }
                                } catch (\Exception $e) {
                                    // 记录异常日志，但不中断流程
                                    active_log(lang_plugins('renew_credit_limit_auto_renew_exception', [
                                        '{host_id}' => $id,
                                        '{order_id}' => $orderId,
                                        '{msg}' => $e->getMessage()
                                    ]), 'addon_idcsmart_renew', $renew->id);
                                }
                            }
                        }
                    }
                }
            }

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            $returnUrl = "{$domain}/productdetail.htm?id=".$id;
            
            // 如果是自然月预付费，设置10分钟未支付超时
            $orderUpdate = ['return_url' => $returnUrl];
            foreach ($cycles as $value){
                if ($billingCycle == $value['billing_cycle']){
                    if(!empty($value['is_natural_month_prepaid'])){
                        $orderUpdate['unpaid_timeout'] = time() + 600;
                    }
                    break;
                }
            }
            $OrderModel->update($orderUpdate, ['id'=>$orderId]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
        $amount = $OrderModel->where('id',$orderId)->value('amount');

        # 记录日志
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($host['product_id']);
        if ($this->isAdmin){
            active_log(lang_plugins('renew_admin_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name . '#', '{host}'=>'host#'.$id.'#'.$product['name'].'#', '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }else{
            active_log(lang_plugins('renew_client_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name . '#' , '{host}'=>'host#'.$id.'#'.$product['name'].'#', '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }

        // 如果自动支付失败，发送通知
        if (isset($autoRenew) && !$autoRenew){
            // 增加缓存，每天每个产品只能通知一次
            $cacheKey = 'host_auto_renew_fail_'.$id;
            $cache = cache($cacheKey);
            if(empty($cache)){
                system_notice([
                    'name' => 'host_auto_renew_fail',
                    'email_description' => lang_plugins('host_auto_renew_fail_send_email'),
                    'sms_description' => lang_plugins('host_auto_renew_fail_send_sms'),
                    'task_data' => [
                        'client_id' => $clientId,
                        'host_id' => $id,
                        'order_id' => $orderId,
                    ],
                ]);
                cache($cacheKey, 1, 86400);
            }
        }

        if ($amount>0){
            if(isset($autoRenew) && $autoRenew){ # 自动续费
                $this->renewHandle($renew->id);
                return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
            }else if ($this->isAdmin && isset($param['pay']) && intval($param['pay'])){ # 后台直接标记支付
                $result = $OrderModel->orderPaid(['id'=>$orderId]);
                if ($result['status'] == 200){
                    return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
                }else{
                    return ['status'=>400,'msg'=>lang_plugins('renew_fail')];
                }
            }
        }else{
            $this->renewHandle($renew->id);

            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
        }

        // 返回自定义字段
        $hookResult = hook('order_create_return_customfield',['order_id'=>$orderId]);
        $customfields = [];
        foreach ($hookResult as $value){
            $customfields = array_merge($customfields,$value);
        }

        return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Unpaid','data'=>['id'=>$orderId,'amount'=>$amount??0,'customfields'=>$customfields]];
    }

    /**
     * 时间 2022-06-02
     * @title 批量续费页面
     * @desc 批量续费页面
     * @author wyh
     * @version v1
     * @param array ids - 产品ID,数组 required
     * @return array list - 产品
     * @return int list[].id - 产品ID
     * @return int list[].product_id - 商品ID
     * @return string list[].product_name - 商品名称
     * @return string list[].name - 标识
     * @return int list[].active_time - 开通时间
     * @return int list[].due_time - 到期时间
     * @return string list[].first_payment_amount - 金额
     * @return string list[].billing_cycle - 周期
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string list[].billing_cycles - 可续费周期
     * @return string list[].billing_cycles.price - 价格
     * @return string list[].billing_cycles.billing_cycle - 周期
     * @return string list[].billing_cycles.duration - 周期时间
     * @return string list[].billing_cycles.base_price - 基础原价(不包括优惠码，客户等级等折扣)
     * @return string list[].billing_cycles.id - 周期比例ID
     * @return string list[].billing_cycles.name_show - 周期名字显示
     * @return string list[].billing_cycles.prr - 与产品当前周期比例的比值（后台产品内页开启按比例续费的功能会使用）
     * @return string list[].billing_cycles.price_save - 保存至数据库的续费金额
     * @return string list[].billing_cycles.renew_amount - 续费金额(自有软件使用)
     * @return string list[].billing_cycles.max_renew - 当前周期，续费金额已经减了客户等级折扣金额，所以不需要再减一次(当前周期为true，其他周期为false，手动输入优惠码时，也为false)
     */
    public function renewBatchPage($param)
    {
        if (!isset($param['ids']) || !is_array($param['ids']) || empty($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }
        // hh 20240319 先做兼容处理,后续稳定后不用判断
        $supportOrderRecycleBin = is_numeric(configuration('order_recycle_bin'));

        $HostModel = new HostModel();

        $hosts = $HostModel->alias('h')
            ->field('h.*,p.name product_name') //,h.id,h.product_id,p.name product_name,h.name,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.status,h.billing_cycle_time,h.renew_amount
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where(function (Query $query) use($param, $supportOrderRecycleBin) {

                $clientId = $this->isAdmin?intval($param['client_id']):get_client_id();

                $query->where('h.client_id', $clientId);

                $query->whereIn('h.id',$param['ids']);
                if($supportOrderRecycleBin){
                    $query->where('h.is_delete', 0);
                }
            })
            ->withAttr('product_name', function($val){
                if(!empty($val)){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
            ->where('billing_cycle', 'NOT IN', ['onetime','on_demand']) // 排序一次性/按需
            ->select();

        $ModuleLogic = new ModuleLogic();
        # 过滤不可续费产品
        $hostsFilter = [];
        foreach ($hosts as $host) {
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])
                ->where('mode','only_api')
                ->find();
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->durationPrice($host);
            }else{
                $result = $ModuleLogic->durationPrice($host);
            }

            $cycles = isset($result['status']) && $result['status'] == 200 ? $result['data'] :[];
            # TODO wyh 20231124 可续费周期
            if (empty($cycles)){
                $cycles = [
                    [
                        'billing_cycle' => $host['billing_cycle_name'],
                        'price' => $host['base_price'],//$host['renew_amount'],
                        'duration' => $host['billing_cycle_time'],
                        'base_price' => $host['base_price']
                    ]
                ];
            }
            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'','','batch_renew');

            $host['billing_cycles'] = $cycles;

            # 处理金额格式
            $host['first_payment_amount'] = amount_format($host['first_payment_amount']);
            # 产品已开通/已到期且非一次性才可续费
            if (in_array($host['status'],['Active','Suspended']) && $host->billing_cycle != 'onetime'){
                $hostsFilter[] = $host->toArray();
            }
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$hostsFilter]];
    }

    /**
     * 时间 2022-06-02
     * @title 批量续费
     * @desc 批量续费
     * @author wyh
     * @version v1
     * @param array ids - 产品ID,数组 required
     * @param object billing_cycles - 周期,对象{"id":"小时"} required
     * @param object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     */
    public function renewBatch($param)
    {
        if (!isset($param['ids']) || !is_array($param['ids']) || empty($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        if (!isset($param['billing_cycles']) || !is_array($param['billing_cycles']) || empty($param['billing_cycles'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        if ($this->isAdmin){
//            if (!isset($param['amount_custom']) || !is_array($param['amount_custom']) || empty($param['amount_custom'])){
//                return ['status'=>400,'msg'=>lang_plugins('param_error')];
//            }
        }

        $ids = $param['ids'];

        $billingCycles = $param['billing_cycles'];

        $amountCustom = $param['amount_custom']??[];

        $HostModel = new HostModel();

        $ModuleLogic = new ModuleLogic();

        $clientId = $this->isAdmin?intval($param['client_id']):get_client_id();

        $OrderModel = new OrderModel();
        if(!$this->isAdmin && $OrderModel->haveUnpaidOnDemandOrder($clientId)){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_order_cannot_do_this') ];
        }

        $renewDatas = [];

        $orderItems = [];

        $total = 0;

        $productIds = [];

        $upstreamOrders = [];

        $usePromoCodeArr = [];

        if (!empty($param['customfield']['promo_code'])){
            $PromoCodeModel = new PromoCodeModel();
            $tmp1 = $PromoCodeModel->where('code',$param['customfield']['promo_code'])->find();
            $used = $tmp1['used']??0;
        }else{
            $used = 0;
        }
        foreach ($ids as $id){
            $host = $HostModel->find($id);
            if (empty($host) || $host['is_delete']){
                return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
            }

            if ($host->client_id != $clientId){
                return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
            }
            # 产品已开通/已到期才可续费
            if (!in_array($host['status'],['Active','Suspended'])){
                return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
            }

            # 产品周期一次性不可续费
            if (in_array($host->billing_cycle, ['onetime','on_demand'])){
                return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
            }

            # 申请了转按需不能续费
            if($host['change_billing_cycle_id'] > 0){
                return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
            }

            if (!isset($billingCycles[$id])){
                return ['status'=>400,'msg'=>lang_plugins('param_error')];
            }

            # 判断周期
            $billingCycle = $billingCycles[$id];
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])
                ->where('mode','only_api')
                ->find();
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->durationPrice($host);
            }else{
                $result = $ModuleLogic->durationPrice($host);
            }

            $cycles = $result['status'] == 200 ? $result['data'] :[];
            # TODO wyh 20231124 可续费周期
            if (empty($cycles)){
                $cycles = [
                    [
                        'billing_cycle' => $host['billing_cycle_name'],
                        'price' => $host['base_price'],//$host['renew_amount'],
                        'duration' => $host['billing_cycle_time'],
                        'base_price' => $host['base_price']
                    ]
                ];
            }
            foreach ($cycles as $cycle){
                if ($billingCycle == $cycle['billing_cycle']){
                    $duration = $cycle['duration'];
                }
            }
            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'',$duration??'','batch_renew',true,$usePromoCodeArr,$used);

            $billingCycleAllow = array_column($cycles,'billing_cycle');

            if (empty($billingCycleAllow)){
                return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
            }
            if (!in_array($billingCycle,$billingCycleAllow)){
                return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
            }

            # 获取金额
            $maxRenew = false;
            $clientLevelDiscount = '0.00'; // 客户等级折扣
            foreach ($cycles as $value){
                if ($billingCycle == $value['billing_cycle']){
                    $amount = $value['price']; // 实际支付
                    if (isset($value['son_host_id']) && $value['son_host_id']){
                        $basePrice = $value['base_price']-$value['son_base_price'];
                    }else{
                        $basePrice = $value['base_price']; // 原价
                    }
                    $amountSave = $value['price_save']; // 保存至host表的amount字段
                    // 自然月预付费 + 换周期：订单子项需要使用折算后但未减用户等级折扣的价格
                    // 当前周期或普通周期：使用 renew_amount
                    if(!empty($value['is_natural_month_prepaid']) && empty($value['is_current_cycle'])){
                        // 自然月预付费 + 换周期：price 是最终支付价格（已减用户等级折扣和优惠码折扣），需要加回所有折扣得到折算后原价
                        $renewAmount = bcadd($value['price'], $value['client_level_discount']??0, 2);
                        $renewAmount = bcadd($renewAmount, $value['promo_code_discount']??0, 2);
                    }else{
                        // 当前周期或普通周期：使用 renew_amount（可手动设置的续费价格）
                        $renewAmount = $value['renew_amount'];
                    }
                    // $clientLevelDiscount = $value['client_level_discount'];
                    $maxRenew = $value['max_renew'];
                    $profit = $value['profit'] ?? 0;
                    $dueTime = $value['duration'];
                    break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                }
            }

            # 获取自定义的金额
            if ($this->isAdmin && isset($amountCustom[$id]) && $amountCustom[$id]>=0){
                $amountSave = $amount = $basePrice = $amountCustom[$id];
                $renewAmount = $amountCustom[$id];
            }

            $total = bcadd($total,$amount,2);

            $renewData = [
                'client_id' => $clientId,
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime??0,
                'new_billing_cycle_amount' => $amountSave,//$amount,
                'status' => 'Pending',
                'create_time' => time(),
                'host_name' => $host['name'],
                'base_price' => $basePrice,
                'max_renew' => $maxRenew,
                'host' => $host,
                'renew_amount' => $renewAmount,
                // 'client_level_discount' => $clientLevelDiscount,
            ];
            $renewDatas[] = $renewData;

            # 默认取第一个产品的支付方式
            if (!isset($gateway)){
                $gateway = $host['gateway'];
            }

            $productIds[$id] = $host['product_id'];

            if($upstreamProduct){
                $upstreamOrders[] = [
                    'supplier_id' => $upstreamProduct['supplier_id'],
                    'order_id' => 0,
                    'host_id' => $id,
                    'amount' => $amount,
                    'profit' => $profit,
                    'create_time' => time()
                ];
            }

        }

        $result = hook('before_host_renew', ['host_id'=>$ids]);

        foreach ($result as $value){
            if (isset($value['status']) && $value['status']==400){
                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message')];
            }
        }

        $this->startTrans();

        try{
            # 续费记录
            $renewIds = [];

            $ProductModel = new ProductModel();

            $param['customfield']['max_renew_array'] = [];
            $param['customfield']['renew_due_time_array'] = [];

            foreach ($renewDatas as $renewData){

                $this->deleteHostUnpaidUpgradeOrder($renewData['host_id']);
                $this->deleteUnpaidRenewOrder($renewData['host_id']);

                $productId = $renewData['product_id'];

                $hostName = $renewData['host_name'];

                $host = $renewData['host'];

                $dueTime = $renewData['new_billing_cycle_time'];

                $billingCycle = $renewData['new_billing_cycle'];

                $maxRenew = $renewData['max_renew'];

                $insertRenewData = [
                    'client_id' => $renewData['client_id'],
                    'host_id' => $renewData['host_id'],
                    'new_billing_cycle' => $renewData['new_billing_cycle'],
                    'new_billing_cycle_time' => $renewData['new_billing_cycle_time'],
                    'new_billing_cycle_amount' => $renewData['new_billing_cycle_amount'],
                    'status' => 'Pending',
                    'create_time' => time(),
                    'base_price' => $renewData['base_price'],
                ];

                $renew = $this->create($insertRenewData);

                $product = $ProductModel->find($productId);

                if (isset($product['renew_rule'])){
                    if ($product['renew_rule']=='due'){
                        $beginTime = date('Y/m/d',$host->due_time);
                        $endTime = date('Y/m/d',$host->due_time+$dueTime);
                    }else{
                        # 到期时间描述,应该和实际的有差异 TODO
                        if ($host->status == 'Suspended' || time() >= $host->due_time){
                            $beginTime = date('Y/m/d',time());
                            $endTime = date('Y/m/d',time()+$dueTime);
                        }else{
                            $beginTime = date('Y/m/d',$host->due_time);
                            $endTime = date('Y/m/d',$host->due_time+$dueTime);
                        }
                    }
                }else{
                    # 到期时间描述,应该和实际的有差异 TODO
                    if ($host->status == 'Suspended' || time() >= $host->due_time){
                        $beginTime = date('Y/m/d',time());
                        $endTime = date('Y/m/d',time()+$dueTime);
                    }else{
                        $beginTime = date('Y/m/d',$host->due_time);
                        $endTime = date('Y/m/d',$host->due_time+$dueTime);
                    }
                }

                $HostIpModel = new HostIpModel();
                $hostIp = $HostIpModel->where('host_id', $renewData['host_id'])->find();

                if(!empty($hostIp) && !empty($hostIp['dedicate_ip'])){
                    $hostName = $hostName.', IP: '.$hostIp['dedicate_ip'];
                }

                $orderItemData = [
                    'host_id' => $renewData['host_id'],
                    'product_id' => $productId,
                    'type' => 'renew',
                    'rel_id' => $renew->id,
                    'amount' => $renewData['renew_amount'],
                    'description' => lang_plugins('host_renew_description',['{product_name}'=>$product['name'],'{name}'=>$hostName,'{billing_cycle_name}'=>$billingCycle,'{time}'=>$beginTime . '-' . $endTime]),
                ];
                $orderItems[] = $orderItemData;
                
                // 添加用户等级折扣子项
                // if ($maxRenew) {
                //     // 计算实际差额（原价 - 续费金额）
                //     $actualDiff = bcsub($renewData['base_price'], $renewData['renew_amount'], 2);
                    
                //     // 只有当原价 > 续费价格时，才显示折扣
                //     if (bccomp($actualDiff, 0, 2) > 0 && bccomp($actualDiff, $clientLevelDiscount, 2) == 0 ) {
                //         // 获取用户等级信息（需要关联商品）
                //         $PluginModel = new PluginModel();
                //         $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
                //         if (!empty($plugin) && class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')) {
                //             $IdcsmartClientLevelClientLinkModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel();
                //             $clientLink = $IdcsmartClientLevelClientLinkModel->alias('a')
                //                     ->field('b.id,b.name,c.product_id,d.discount_percent')
                //                     ->leftJoin('addon_idcsmart_client_level b', 'a.addon_idcsmart_client_level_id=b.id')
                //                     ->leftJoin('addon_idcsmart_client_level_product_group_link d', 'a.addon_idcsmart_client_level_id=d.addon_idcsmart_client_level_id')
                //                     ->leftJoin('addon_idcsmart_client_level_product_link c', 'c.addon_idcsmart_client_level_product_group_id=d.addon_idcsmart_client_level_product_group_id')
                //                     ->where('a.client_id', $clientId)
                //                     ->where('c.product_id', $productId)
                //                     //->where('b.discount_status', 1)
                //                     ->find();
                //             if (!empty($clientLink)) {
                //                 $clientLevelName = $clientLink['name'];
                //                 $clientLevelId = $clientLink['id'];

                //                 $orderItems[] = [
                //                     'host_id' => $renewData['host_id'],
                //                     'product_id' => $productId,
                //                     'type' => 'addon_idcsmart_client_level',
                //                     'rel_id' => $clientLevelId,
                //                     'amount' => -$actualDiff,
                //                     'description' => lang_plugins('client_level_use_success_percent_description', ['{name}' => $clientLevelName, '{host_id}'=>$renewData['host_id'], '{value}'=>$clientLink['discount_percent']]),
                //                 ];
                //                 // 修改刚添加的主订单子项为原价（注意：批量续费时orderItems是累加的，所以要找到对应的索引）
                //                 $lastMainItemIndex = count($orderItems) - 2; // 倒数第二个是刚添加的主订单子项
                //                 $orderItems[$lastMainItemIndex]['amount'] = $renewData['base_price'];
                //             }
                //         }
                //     }
                // }

                $renewIds[] = $renew->id;

                // 20240425 wyh 给优惠码使用
                if (!$maxRenew){
                    $OrderItemModel = new OrderItemModel();
                    $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
                        ->where('host_id',$host['id'])
                        ->where('type','addon_promo_code')
                        ->find();
                    $PromoCodeModel = new PromoCodeModel();
                    if (!empty($orderItem)){
                        $promoCode = $PromoCodeModel->find($orderItem['rel_id']??0);
                    }
                }
                $param['customfield']['host_customfield'][] = [
                    'id' => $host['id'],
                    'customfield' => [
                        'promo_code' => (!empty($param['customfield']['promo_code']))?$param['customfield']['promo_code']:($promoCode['code']??'')
                    ]
                ];

                // 20240425 wyh 给客户等级折扣使用
                $param['customfield']['max_renew_array'][$host['id']] = $renewData['max_renew']??false;
                $param['customfield']['renew_due_time_array'][$host['id']] = $dueTime;
            }

            # 创建订单
            $data = [
                'type' => 'renew',
                'amount' => $total,
                'gateway' => $gateway,
                'client_id' => $clientId,
                'items' => $orderItems
            ];
            $OrderModel = new OrderModel();
            $orderId = $OrderModel->createOrderBase($data);

            if(!empty($upstreamOrders)){
                foreach ($upstreamOrders as $key => $value) {
                    $upstreamOrders[$key]['order_id'] = $orderId;
                }
                $UpstreamOrderModel = new UpstreamOrderModel();
                $UpstreamOrderModel->saveAll($upstreamOrders);
            }
//            $param['customfield']['batch_renew'] = true;
            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($orderId);

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            $returnUrl = "{$domain}/finance.htm";
            
            // 检查是否有自然月预付费商品，如果有则设置10分钟未支付超时
            $orderUpdate = ['return_url' => $returnUrl];
            $hasNaturalMonth = false;
            foreach ($renewDatas as $renewData){
                $product = $ProductModel->find($renewData['product_id']);
                if(!empty($product['natural_month_prepaid']) && $product['natural_month_prepaid'] == 1){
                    $hasNaturalMonth = true;
                    break;
                }
            }
            if($hasNaturalMonth){
                $orderUpdate['unpaid_timeout'] = time() + 600;
            }
            $OrderModel->update($orderUpdate, ['id'=>$orderId]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
        $amount = $OrderModel->where('id',$orderId)->value('amount');

        # 记录日志
        $ProductModel = new ProductModel();
        $productDes = '';
        foreach ($productIds as $hid=>$pid){
            $product = $ProductModel->find($pid);
            $productDes .= "host#{$hid}#{$product['name']}#,";
        }
        if ($this->isAdmin){
            active_log(lang_plugins('renew_admin_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{host}'=>rtrim($productDes,','), '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }else{
            active_log(lang_plugins('renew_client_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name.'#', '{host}'=>rtrim($productDes,','), '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }

        if ($amount>0){
            # 后台直接标记支付
            if ($this->isAdmin && isset($param['pay']) && intval($param['pay'])){
                $OrderModel->orderPaid(['id'=>$orderId]);
                return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
            }
        }else{

            foreach ($renewIds as $renewId){
                $this->renewHandle($renewId);
            }

            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
        }

        return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Unpaid','data'=>['id'=>$orderId]];
    }

    # 支付后续费处理
    public function renewHandle($id,$force=false)
    {
        $renew = $this->find($id);

        if (empty($renew)){
            return false;
        }

        if ($renew->status == 'Completed'){
            return false;
        }

        $amount = $renew->new_billing_cycle_amount;

        $dueTime = $renew->new_billing_cycle_time;

        $billingCycle = $renew->new_billing_cycle;

        $basePrice = $renew->base_price;

        $HostModel = new HostModel();
        $host = $HostModel->find($renew->host_id);

        $ProductModel = new ProductModel();
        $product = $ProductModel->find($host['product_id']);

        // 判断产品是否手动资源
        $manualResource = false;
        if ($product->getModule()=='mf_cloud' && class_exists("server\\mf_cloud\\model\\ConfigModel")){
            $ConfigModel = new \server\mf_cloud\model\ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$host['product_id'] ]);
            $plugin = PluginModel::where('name', 'ManualResource')->where('status', 1)->where('module', 'addon')->find();
            if(isset($config['data']['manual_manage']) && $config['data']['manual_manage'] == 1 && !empty($plugin)){
                $manualResource = true;
            }
        }elseif ($product->getModule()=='mf_dcim' && class_exists("server\\mf_dcim\\model\\ConfigModel")){
            $ConfigModel = new \server\mf_dcim\model\ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$host['product_id'] ]);
            $plugin = PluginModel::where('name', 'ManualResource')->where('status', 1)->where('module', 'addon')->find();
            if(isset($config['data']['manual_resource']) && $config['data']['manual_resource'] == 1 && !empty($plugin)){
                $manualResource = true;
            }
        }

        $ModuleLogic = new ModuleLogic();

        // 手动资源
        if ($manualResource && !$force){
            system_notice([
                'name'                  => 'host_module_action',
                'email_description'     => lang('host_module_action'),
                'sms_description'       => lang('host_module_action'),
                'task_data' => [
                    'client_id' => $host['client_id'],
                    'host_id'	=> $host['id'],
                    'template_param'=>[
                        'module_action' => lang_plugins('renew'),
                    ],
                ],
            ]);
            $ManualResourceLogModel = new \addon\manual_resource\model\ManualResourceLogModel();
            $ManualResourceLogModel->createLog([
                'host_id'                   => $host['id'],
                'type'                      => 'renew',
                'client_id'                 => $host['client_id'],
                'data'                      => ['id'=>$renew['id']],
            ]);
//            return true;
        }

        $this->startTrans();

        try{

            $upData = [
                'renew_amount' => $amount,
                'billing_cycle_name' => $billingCycle,
                'billing_cycle_time' => $dueTime,
                'update_time' => time(),
                'base_price' => $basePrice,
                'is_ontrial' => 0,
                'base_renew_price' => $basePrice,
            ];

            // 判断是否为自然月预付费（通过商品的 natural_month_prepaid 字段判断）
            $isNaturalMonth = ($product['natural_month_prepaid'] == 1);
            
            if($isNaturalMonth && !empty($renew['natural_month_due_time'])){
                // 自然月预付费：使用续费记录中保存的精确到期时间
                $upData['due_time'] = $renew['natural_month_due_time'];
            }else{
                // 普通周期：按原逻辑处理
                if (isset($product['renew_rule'])){
                    if ($product['renew_rule']=='due'){
                        $upData['due_time'] = $host->due_time+$dueTime;
                    }else{
                        # 更改到期时间
                        if ($host->status == 'Suspended' || time() >= $host->due_time){
                            $upData['due_time'] = time()+$dueTime;
                        }else{
                            $upData['due_time'] = $host->due_time+$dueTime;
                        }
                    }
                }else{
                    # 更改到期时间
                    if ($host->status == 'Suspended' || time() >= $host->due_time){
                        $upData['due_time'] = time()+$dueTime;
                    }else{
                        $upData['due_time'] = $host->due_time+$dueTime;
                    }
                }
            }

            // 更改用户等级续费折扣
            if($host['renew_use_current_client_level'] == 1){
                $upData['discount_renew_price'] = $renew['client_level_discount'];
            }

            $host->save($upData);

            $renew->save([
                'status' => 'Completed'
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('log_addon_renew_paid_fail',['{host}'=>$renew->host_id,'{id}'=>$id,'{msg}'=>$e->getMessage()]),'addon_idcsmart_renew',$id);
            return false;
        }

        # 调模块
        # 获取订单ID
        $OrderItemModel = new OrderItemModel();
        $orderItem = $OrderItemModel->where('type','renew')
            ->where('rel_id',$id)
            ->where('host_id',$renew['host_id'])
            ->where('client_id',$renew['client_id'])
            ->find();
        $orderId = $orderItem['order_id']??0;

        # 解除产品暂停
        if ($host->status == 'Suspended'){
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])
                ->where('mode','only_api')
                ->find();
            if($upstreamProduct){
//                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
//                $result = $ResModuleLogic->unsuspendAccount($host);
                $result = ['status' => 200];
            }else{
                $result = $ModuleLogic->unsuspendAccount($host, ['is_renew'=>1]);
            }

            if ($result['status']==200){
                $host->save([
                    'status' => 'Active'
                ]);
            }else{
                active_log(lang('log_renew_unsuspended_host_fail',['{host_id}'=>$renew->host_id,'{msg}'=>$result['msg']??'']),'addon_idcsmart_renew',$id);
            }
        }

        system_notice([
            'name' => 'host_renew',
            'email_description' => lang_plugins('host_renew_send_mail'),
            'sms_description' => lang_plugins('host_renew_send_sms'),
            'task_data' => [
                'client_id' => $host['client_id'],
                'host_id'=>$renew->host_id,//产品ID
                'order_id'=>$orderId,
                'template_param'=>[
                    'id' => $renew->host_id,//产品ID
                ],
            ],
        ]);

        # 记录日志
        if (!$manualResource){
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if ($upstreamProduct){
                if ($upstreamProduct['mode']=='sync'){
                    $ModuleLogic->renew($host);
                }
                // 执行速度慢，使用任务队列执行
                add_task([
                    'type' => 'addon_renew_batch_renew',
                    'description' => lang_plugins('addon_renew_batch_renew',['{order_id}'=>$orderId,'{host_id}'=>$host['id']]),
                    'task_data' => [
                        'host_id'=>$host['id'],//主机ID
                        'order_id'=>$orderId,//订单ID
                    ],
                ]);
//            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
//            $ResModuleLogic->renew($host,$orderId);
            }else{
                $ModuleLogic->renew($host);
            }
        }

        upstream_sync_host($host['id'], 'host_renew');


        # 任务队列

        return true;
    }

    # 实现产品列表后按钮模板钩子
    public function templateClientAfterHostListButton($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host) || $host['is_delete']){
            return '';
        }
        $clientId = get_client_id();
        if ($host->client_id != $clientId){
            return '';
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return '';
        }

        # 产品周期一次性不可续费
        if ($host->billing_cycle == 'onetime'){
            return '';
        }

        $url = "console/v1/{$id}/renew";

        $button = lang_plugins('renew');
        # 续费按钮
        return "<a href=\"{$url}\" class=\"btn btn-primary h-100 custom-button text-white\">{$button}</a>";
    }

    # 删除产品未付款升降级订单
    public function deleteHostUnpaidUpgradeOrder($id)
    {
        $OrderModel = new OrderModel();
        return $OrderModel->deleteHostUnpaidUpgradeOrder($id);
    }

    public function beforeHostRenewalFirst()
    {
        $host = HostModel::alias('a')
            ->field('a.id,a.client_id,a.due_time,a.billing_cycle_name,b.auto_renew_in_advance,b.auto_renew_in_advance_num,b.auto_renew_in_advance_unit')
            ->leftjoin('product b','b.id=a.product_id')
            ->leftjoin('addon_idcsmart_renew_auto c','c.host_id=a.id')
            ->where('a.status','Active')
            ->where('a.is_delete', 0)
            ->where('a.billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
            ->where('a.change_billing_cycle_id', 0)
            ->where('c.status', 1)
            ->select()
            ->toArray();
        foreach ($host as $k=>$v){
            if($v['auto_renew_in_advance']){
                if($v['auto_renew_in_advance_unit']=='day'){
                    if($v['due_time']>(time()+$v['auto_renew_in_advance_num']*24*3600)){
                        continue;
                    }
                }else if($v['auto_renew_in_advance_unit']=='hour'){
                    if($v['due_time']>(time()+$v['auto_renew_in_advance_num']*3600)){
                        continue;
                    }
                }else if($v['auto_renew_in_advance_unit']=='minute'){
                    if($v['due_time']>(time()+$v['auto_renew_in_advance_num']*60)){
                        continue;
                    }
                }
            }else{
                if($v['due_time']>(time()+10*60)){
                    continue;
                }
            }
            // 【新增】检查是否有未支付的续费订单，优先复用
            $existingOrder = $this->getUnpaidRenewOrder($v['id']);
            
            if (!empty($existingOrder)){
                // 找到未支付订单，验证是否可复用
                $host = HostModel::find($v['id']);
                $canReuse = $this->validateOrderReusable($existingOrder, $host);
                
                if ($canReuse){
                    // 订单可复用，尝试支付
                    $payResult = $this->payExistingRenewOrder(
                        $existingOrder['id'], 
                        $v['id'], 
                        $v['client_id'], 
                        $existingOrder['renew_id']
                    );
                    // 无论是否成功，都跳过，前提是获取的最近24小时内生成的订单
                    continue;

                    // 支付失败，继续生成新订单（会删除旧订单）
                }
                // 订单不可复用，继续生成新订单（会删除旧订单）
            }
            
            // 没有未支付订单或订单不可复用，生成新订单
            $param = [
                'id' => $v['id'],
                'billing_cycle' => $v['billing_cycle_name'],
                'auto_renew' => 1
            ];

            $this->renew($param);
        }

//        $HostModel = new HostModel();
//        $host = $HostModel->find($id);
//        if (empty($host) || $host['is_delete']){
//            return false;
//        }
//        // 如果是按需/申请了转按需
//        if(in_array($host['billing_cycle'], ['on_demand']) || $host['change_billing_cycle_id'] > 0){
//            IdcsmartRenewAutoModel::where('host_id', $id)->delete();
//            return false;
//        }
//        $renewAuto = IdcsmartRenewAutoModel::where('host_id', $id)->find();
//        if(empty($renewAuto)){
//            return false;
//        }
//        if($renewAuto['status']!=1){
//            return false;
//        }

//        $param = [
//            'id' => $id,
//            'billing_cycle' => $host['billing_cycle_name'],
//            'auto_renew' => 1
//        ];
//
//        $res = $this->renew($param);
//        if($res['status']==200){
//            return ['status' => 200, 'msg' => lang_plugins('success_message'), 'data' => ['action' => 'auto_renew']];
//        }else{
//            return false;
//        }
        
    }

    # 删除产品未支付的续费订单
    public function deleteUnpaidRenewOrder($id,$orderId=0){
        if (empty($id)){
            return false;
        }

        $OrderModel = new OrderModel();

        $unpaidRenewOrders = $OrderModel->alias('o')
            ->field('oi.order_id')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.type','renew')
            ->where('oi.host_id',$id)
            ->whereIn('o.status',['Unpaid','WaitUpload','WaitReview','ReviewFail'])
            ->where('o.id','<>',$orderId)
            ->select()->toArray();
        if (!empty($unpaidRenewOrders)){
            // 删除 未支付续费订单日志
            /*foreach ($unpaidRenewOrders as $unpaidRenewOrder){
                active_log("删除未支付续费订单#".$unpaidRenewOrder['order_id'],'order',$unpaidRenewOrder['order_id']);
            }*/
            $orderIds = array_column($unpaidRenewOrders,'order_id');

            // 这个hook会抛出异常
            hook('before_delete_unpaid_renew_order', ['id'=>$orderIds]);

            $OrderModel->cancelUserCustomOrder($orderIds);

            $OrderItemModel = new OrderItemModel();
            $renewIds = $OrderItemModel->whereIn('order_id',$orderIds)
                ->where('type','renew')
                ->column('rel_id');
            $OrderItemModel->whereIn('order_id',$orderIds)->delete();
            $OrderModel->whereIn('id',$orderIds)->delete();
            // 问题所在
            $this->whereIn('id',$renewIds)->delete();

            $UpstreamOrderModel = new UpstreamOrderModel();
            $UpstreamOrderModel->whereIn('order_id',$orderIds)->delete();
        }

        return true;

//        $OrderModel = new OrderModel();
//        return $OrderModel->deleteUnpaidRenewOrder($id,$orderId);
    }

    /**
     * 时间 2026-01-08
     * @title 获取产品的未支付续费订单
     * @desc 获取产品的未支付续费订单（用于自动续费复用）
     * @author cascade
     * @version v1
     * @param int $hostId - 产品ID
     * @return array|null - 订单信息或null
     */
    public function getUnpaidRenewOrder($hostId)
    {
        if (empty($hostId)){
            return null;
        }

        $OrderModel = new OrderModel();
        
        // 查询未支付的续费订单
        $order = $OrderModel->alias('o')
            ->field('o.id,o.amount,o.create_time,oi.rel_id as renew_id')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.type','renew')
            ->where('oi.host_id',$hostId)
            ->where('o.status','Unpaid')  // 仅Unpaid状态
            ->where('o.create_time','>=',time()-86400)  // 24小时内创建
            ->order('o.id','desc')  // 最新的订单
            ->find();

        if (empty($order)){
            return null;
        }

        return $order->toArray();
    }

    /**
     * 时间 2026-01-08
     * @title 支付已存在的续费订单
     * @desc 自动续费时支付已存在的未支付订单
     * @author cascade
     * @version v1
     * @param int $orderId - 订单ID
     * @param int $hostId - 产品ID
     * @param int $clientId - 用户ID
     * @param int $renewId - 续费记录ID
     * @return array
     */
    public function payExistingRenewOrder($orderId, $hostId, $clientId, $renewId)
    {
        if (empty($orderId) || empty($hostId)){
            return ['status'=>400,'msg'=>lang_plugins('order_not_exist')];
        }

        $OrderModel = new OrderModel();
        
        // 重新获取订单金额（可能被hook修改）
        $order = $OrderModel->find($orderId);
        if (empty($order) || $order['status'] != 'Unpaid'){
            return ['status'=>400,'msg'=>lang_plugins('order_not_exist')];
        }

        $amount = $order['amount'];
        
        if($amount <= 0){
            // 金额为0，直接标记为已支付
            $this->renewHandle($renewId);
            
            // 记录日志
            active_log(lang_plugins('renew_auto_renew_reuse_order_success', [
                '{host_id}' => $hostId,
                '{order_id}' => $orderId,
                '{amount}' => '0.00'
            ]), 'addon_idcsmart_renew', $renewId);
            
            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid','reused'=>true];
        }

        $autoRenew = false;

        // 1、尝试余额支付
        $client = ClientModel::find($clientId);
        if($client['credit'] >= $amount){
            $res = update_credit([
                'type' => 'Applied',
                'amount' => -$amount,
                'notes' => lang('order_apply_credit')."#{$orderId}",
                'client_id' => $clientId,
                'order_id' => $orderId,
                'host_id' => 0,
            ]);
            if($res){
                $OrderModel->update([
                    'status' => 'Paid',
                    'credit' => $amount,
                    'amount_unpaid'=>0,
                    'pay_time' => time(),
                    'update_time' => time(),
                    'gateway' => 'credit',
                    'gateway_name' => lang('credit_payment'),
                ], ['id' => $orderId]);
                $autoRenew = true;
            }
        }
        
        // 2. 如果余额支付失败，尝试信用额支付
        if (!$autoRenew){
            $creditLimitAutoRenewEnable = configuration('addon_credit_limit_auto_renew_enable');
            if($creditLimitAutoRenewEnable == 1){
                // 检查信用额插件是否存在并启用
                $PluginModel = new \app\admin\model\PluginModel();
                $creditLimitPlugin = $PluginModel->where('name', 'CreditLimit')->where('module', 'addon')->where('status', 1)->find();

                if(!empty($creditLimitPlugin) && class_exists('addon\\credit_limit\\model\\CreditLimitModel')){
                    try {
                        $CreditLimitModel = new \addon\credit_limit\model\CreditLimitModel();
                        $creditLimitResult = $CreditLimitModel->pay(['id' => $orderId], true);

                        if($creditLimitResult['status'] == 200){
                            $autoRenew = true;
                        }
                    } catch (\Exception $e) {
                        // 忽略异常，继续后续流程
                    }
                }
            }
        }

        if ($autoRenew){
            // 支付成功，执行续费处理
            $this->renewHandle($renewId);
            
            // 记录日志
            active_log(lang_plugins('renew_auto_renew_reuse_order_success', [
                '{host_id}' => $hostId,
                '{order_id}' => $orderId,
                '{amount}' => configuration('currency_prefix').$amount.configuration('currency_suffix')
            ]), 'addon_idcsmart_renew', $renewId);
            
            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid','reused'=>true];
        }else{
            // 支付失败，返回失败信息，但不删除订单
            active_log(lang_plugins('renew_auto_renew_reuse_order_fail', [
                '{host_id}' => $hostId,
                '{order_id}' => $orderId,
                '{amount}' => configuration('currency_prefix').$amount.configuration('currency_suffix')
            ]), 'addon_idcsmart_renew', $renewId);
            
            return ['status'=>400,'msg'=>lang_plugins('renew_auto_renew_fail_insufficient_balance'),'reused'=>false];
        }
    }

    /**
     * 时间 2026-01-08
     * @title 验证订单是否可复用
     * @desc 检查订单的周期、金额等是否符合复用条件
     * @author cascade
     * @version v1
     * @param array $order - 订单信息
     * @param object $host - 产品信息
     * @return bool
     */
    public function validateOrderReusable($order, $host)
    {
        if (empty($order) || empty($host)){
            return false;
        }

        // 1. 检查订单是否是24小时内创建的
        if ($order['create_time'] < time() - 86400){
            return false;
        }

        // 2. 检查续费记录是否存在
        if (empty($order['renew_id'])){
            return false;
        }

        $renewRecord = $this->find($order['renew_id']);
        if (empty($renewRecord)){
            return false;
        }

        // 3. 检查周期是否与当前产品周期一致
        if ($renewRecord['new_billing_cycle'] != $host['billing_cycle_name']){
            return false;
        }

        // 4. 检查金额变化（允许5%的误差）
        $currentPrice = $host['renew_amount'];
        if ($currentPrice > 0){
            $priceDiff = abs($renewRecord['new_billing_cycle_amount'] - $currentPrice) / $currentPrice;
            if ($priceDiff > 0.05){  // 超过5%差异
                return false;
            }
        }

        return true;
    }

    /**
     * 验证并清理周期数据
     * @param array $cycles 周期列表
     * @return array 清理后的周期列表
     * @author hh
     */
    private function validateCycles(array $cycles): array
    {
        foreach ($cycles as $k => $cycle) {
            // 未设置必要参数，清除此周期
            if (!isset($cycle['duration']) || !isset($cycle['billing_cycle']) || !isset($cycle['price'])) {
                unset($cycles[$k]);
            }
        }
        return $cycles;
    }

    /**
     * 初始化周期价格（包含子产品处理）
     * @param array $item2 原始周期数据
     * @param array $cycle 当前周期数据
     * @param HostModel $host 产品实例
     * @return array 初始化后的周期数据
     * @author hh
     */
    private function initializeCyclePrices(array $item2, array $cycle, HostModel $host): array
    {
        // 自然月预付费：保留折算后的 price，所有折扣基于 price 计算
        // 普通周期：使用 base_price
        if (!empty($item2['is_natural_month_prepaid'])) {
            // 自然月预付费：本地price 是折算后的价格，直接使用，如果是接口代理需要使用current_base_price
            $cycle['price'] = !empty($item2['current_base_price']) ? floatval($item2['current_base_price']) : floatval($item2['price']);
            $cycle['base_price'] = floatval($item2['base_price']); // 仅用于显示完整周期价格
            // 保留自然月预付费标识和精确到期时间
            $cycle['is_natural_month_prepaid'] = $item2['is_natural_month_prepaid'];
            $cycle['due_time'] = $item2['due_time'] ?? 0;
            // 保留标准周期时长
            $cycle['duration'] = $item2['duration'];
        } else {
            // 普通周期：使用 base_price
            $cycle['price'] = isset($item2['base_price']) ? floatval(bcsub($item2['base_price'] ?? 0, 0, 2)) : $item2['price'];
            $cycle['base_price'] = isset($item2['base_price']) ? floatval(bcsub($item2['base_price'] ?? 0, 0, 2)) : $item2['price'];
            // 保留周期时长
            $cycle['duration'] = $item2['duration'];
        }
        
        // 处理子产品价格
        if (isset($item2['son_host_id']) && $item2['son_host_id']) {
            // 获取子产品实例
            $sonHost = (new HostModel())->find($item2['son_host_id']);
            
            // 子产品价格加到父产品上
            $cycle['price'] += $item2['son_price'];
            $cycle['base_price'] += $item2['son_base_price'];
            
            // 保存子产品信息（用于后续处理）
            $cycle['son_host_id'] = $item2['son_host_id'];
            $cycle['son_price'] = $item2['son_price'];
            $cycle['son_base_price'] = $item2['son_base_price'];
            $cycle['son_host_instance'] = $sonHost;
        }
        
        return $cycle;
    }

    /**
     * 获取自动循环优惠码信息（仅在没有手动优惠码时使用）
     * @param HostModel $host 产品实例
     * @param array $cycle 周期数据
     * @param float $currentPrice 当前价格（折算后）
     * @param float $basePrice 完整周期价格
     * @return array 优惠码信息 ['is_loop'=>bool, 'exclude'=>bool, 'current_discount'=>float, 'renew_discount'=>float]
     * @author hh
     */
    private function getAutoPromoCodeInfo(HostModel $host, array $cycle, float $currentPrice, float $basePrice): array
    {
        $result = [
            'is_loop' => false,           // 是否循环优惠码
            'exclude' => false,           // 是否与用户等级互斥
            'current_discount' => 0,      // 当前价格的优惠码折扣
            'renew_discount' => 0,        // 续费价格的优惠码折扣（基于完整周期）
        ];
        
        // 获取基于完整周期价格的优惠码信息（用于判断互斥和计算续费折扣）
        $hookResults = hook('apply_promo_code', [
            'host_id' => $host->id,
            'price' => $basePrice,
            'scene' => 'renew',
            'duration' => $cycle['duration']
        ]);
        
        foreach ($hookResults as $hookResult) {
            if ($hookResult['status'] == 200) {
                // 检查是否是循环优惠码
                if (isset($hookResult['data']['loop']) && $hookResult['data']['loop']) {
                    // 自动必须是循环才生效,检查互斥标识
                    $result['exclude'] = !empty($hookResult['data']['exclude_with_client_level']);

                    $result['is_loop'] = true;
                    $result['renew_discount'] = $hookResult['data']['discount'] ?? 0;
                }
                break;
            }
        }
        
        // 如果是循环优惠码，获取基于当前价格的折扣（用于当前价格计算）
        if ($result['is_loop']) {
            $hookResults = hook('apply_promo_code', [
                'host_id' => $host->id,
                'price' => $currentPrice,
                'scene' => 'renew',
                'duration' => $cycle['duration']
            ]);
            
            foreach ($hookResults as $hookResult) {
                if ($hookResult['status'] == 200 && !empty($hookResult['data']['loop'])) {
                    $result['current_discount'] = $hookResult['data']['discount'] ?? 0;
                    break;
                }
            }
        }
        
        return $result;
    }

    /**
     * 计算用户等级折扣（考虑优惠码互斥）
     * @param HostModel $host 产品实例
     * @param array $cycle 周期数据
     * @param float $discountBase 折扣计算基数
     * @param array $promoCodeInfo 优惠码信息
     * @param string $scene 场景
     * @param bool $priceAgent 是否代理价格
     * @return array 折扣信息 ['current'=>float, 'renew'=>float, 'original'=>float]
     * @author hh
     */
    private function calculateClientLevelDiscount(
        HostModel $host, 
        array $cycle, 
        float $discountBase, 
        array $promoCodeInfo,
        string $scene,
        bool $priceAgent
    ): array {
        $result = [
            'current' => 0,      // 当前价格的用户等级折扣
            'renew' => 0,        // 续费价格的用户等级折扣
            'original' => 0,     // 原始用户等级折扣（用于后续处理）
        ];
        
        // 检查是否启用用户等级插件
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('status', 1)->where('name', 'IdcsmartClientLevel')->find();
        
        if (empty($plugin) || (!$priceAgent && $scene != 'batch_renew')) {
            return $result;
        }
        
        $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
        
        // 计算原始用户等级折扣
        if (isset($cycle['client_level_discount']) && is_numeric($cycle['client_level_discount'])) {
            $result['original'] = $cycle['client_level_discount'];
        } else {
            $result['original'] = $IdcsmartClientLevelModel->productDiscount([
                'client_id' => $host['client_id'],
                'id' => $host['product_id'],
                'amount' => $discountBase,
            ]);
        }
        
        // 根据优惠码互斥情况决定是否应用用户等级折扣
        if ($promoCodeInfo['exclude']) {
            // 优惠码互斥：当前价格不使用用户等级折扣
            $result['current'] = 0;
            
            // 续费价格：只有循环优惠码且互斥时才不使用
            if ($promoCodeInfo['is_loop']) {
                $result['renew'] = 0;
            } else {
                // 非循环优惠码互斥：续费价格仍使用用户等级折扣
                if (!empty($cycle['is_natural_month_prepaid'])) {
                    $result['renew'] = $this->getClientLevelDiscountValue(
                        $IdcsmartClientLevelModel,
                        $host,
                        $cycle,
                        $cycle['base_price'],
                        'renew_client_level_discount'
                    );
                }
            }
        } else {
            // 无互斥：正常使用用户等级折扣
            $result['current'] = $result['original'];
            
            // 自然月预付费：计算续费用户等级折扣
            if (!empty($cycle['is_natural_month_prepaid'])) {
                $result['renew'] = $this->getClientLevelDiscountValue(
                    $IdcsmartClientLevelModel,
                    $host,
                    $cycle,
                    $cycle['base_price'],
                    'renew_client_level_discount'
                );
            }
        }
        
        return $result;
    }

    /**
     * 获取用户等级折扣值
     * @param object $model 用户等级模型实例
     * @param HostModel $host 产品实例
     * @param array $cycle 周期数据
     * @param float $amount 金额
     * @param string $key 缓存键名
     * @return float 折扣金额
     * @author hh
     */
    private function getClientLevelDiscountValue($model, HostModel $host, array $cycle, float $amount, string $key): float
    {
        if (isset($cycle[$key]) && is_numeric($cycle[$key])) {
            return $cycle[$key];
        }
        
        return $model->productDiscount([
            'client_id' => $host['client_id'],
            'id' => $host['product_id'],
            'amount' => $amount,
        ]);
    }

    /**
     * 获取手动优惠码信息
     * @param HostModel $host 产品实例
     * @param string $promoCode 优惠码
     * @param float $discountBase 折扣基数
     * @param float $basePrice 完整周期价格
     * @param string $duration 周期时长
     * @return array 优惠码信息 ['id'=>int,'success'=>bool, 'is_loop'=>bool, 'exclude'=>bool, 'current_discount'=>float, 'renew_discount'=>float]
     * @author hh
     */
    private function getManualPromoCodeInfo(
        HostModel $host,
        string $promoCode,
        float $discountBase,
        float $basePrice,
        string $duration
    ): array {
        $result = [
            'success' => false,
            'is_loop' => false,
            'exclude' => false,
            'current_discount' => 0,
            'renew_discount' => 0,
            'promo_code_id' => 0,
        ];
        
        $PromoCodeModel = new PromoCodeModel();
        
        // 应用优惠码（基于折扣基数）
        $res = $PromoCodeModel->apply([
            'promo_code' => $promoCode,
            'scene' => 'renew',
            'host_id' => $host['id'],
            'product_id' => $host['product_id'],
            'amount' => $discountBase,
            'billing_cycle_time' => $duration
        ]);
        
        if ($res['status'] != 200) {
            return $result;
        }
        
        $result['success'] = true;
        $result['current_discount'] = $res['data']['discount'] ?? 0;
        $result['is_loop'] = $res['data']['loop'] ?? false;
        $result['exclude'] = !empty($res['data']['exclude_with_client_level']);
        $result['promo_code_id'] = $res['data']['id'] ?? 0;
        
        // 如果是循环优惠码，计算基于完整周期价格的折扣（用于 price_save）
        if ($result['is_loop']) {
            $resForSave = $PromoCodeModel->apply([
                'promo_code' => $promoCode,
                'scene' => 'renew',
                'host_id' => $host['id'],
                'product_id' => $host['product_id'],
                'amount' => $basePrice,
                'billing_cycle_time' => $duration
            ]);
            
            if ($resForSave['status'] == 200 && !empty($resForSave['data']['loop'])) {
                $result['renew_discount'] = $resForSave['data']['discount'] ?? 0;
            }
        }
        
        return $result;
    }

    /**
     * 计算周期价格（统一处理手动和自动优惠码，包含子产品）
     * @param HostModel $host 产品实例
     * @param array $cycle 周期数据
     * @param bool $isCurrentCycle 是否当前周期
     * @param bool $hasManualPromoCode 是否有手动优惠码
     * @param array $manualPromoCodeInfo 手动优惠码信息
     * @param array $autoPromoCodeInfo 自动优惠码信息
     * @param array $clientLevelDiscount 用户等级折扣信息
     * @param string $scene 场景
     * @param bool $priceAgent 是否代理价格
     * @return array 计算后的周期数据
     * @author hh
     */
    private function calculateCyclePrice(
        HostModel $host,
        array $cycle,
        bool $isCurrentCycle,
        bool $hasManualPromoCode,
        array $manualPromoCodeInfo,
        array $autoPromoCodeInfo,
        array $clientLevelDiscount,
        string $scene,
        bool $priceAgent
    ): array {
        // 决定使用哪个优惠码信息
        $promoCodeInfo = $hasManualPromoCode ? $manualPromoCodeInfo : $autoPromoCodeInfo;
        
        // 处理子产品
        $sonProductInfo = $this->processSonProduct(
            $cycle,
            $host,
            $promoCodeInfo,
            $clientLevelDiscount['current']
        );
        
        // 从正确的价格开始计算（不需要加回逻辑）
        // 自然月预付费：使用折算后的 price（已经在 initializeCyclePrices 中设置）
        // 普通周期：使用 base_price
        // 注意：如果有子产品，价格已经包含了子产品价格
        if (!empty($cycle['is_natural_month_prepaid'])) {
            // 自然月预付费：从折算后的价格开始计算
            $currentPrice = $cycle['price'];
            // price_save 使用完整周期价格（用于下次续费）
            $priceSave = $cycle['base_price'];
        } else {
            // 普通周期：从 base_price 开始计算
            $currentPrice = $cycle['base_price'];
            $priceSave = $cycle['base_price'];
        }
        
        // 初始化优惠码折扣字段（默认为0）
        if (!isset($cycle['promo_code_discount'])) {
            $cycle['promo_code_discount'] = 0;
        }
        
        // 1. 应用优惠码折扣
        if ($hasManualPromoCode) {
            // 手动优惠码：所有周期都使用
            $currentPrice = bcsub($currentPrice, $promoCodeInfo['current_discount'], 2);
            $cycle['promo_code_discount'] = $promoCodeInfo['current_discount'];
            
            // 循环优惠码：price_save 也使用优惠码
            if ($promoCodeInfo['is_loop']) {
                $priceSave = bcsub($priceSave, $promoCodeInfo['renew_discount'], 2);
                
                // 子产品也减去优惠码折扣
                if ($sonProductInfo['son_host']) {
                    $priceSave = bcsub($priceSave, $sonProductInfo['son_promo_discount'], 2);
                }
            }
        } else {
            // 自动优惠码：只有非当前周期且是循环优惠码才使用
            if (!$isCurrentCycle && $autoPromoCodeInfo['is_loop']) {
                $currentPrice = bcsub($currentPrice, $autoPromoCodeInfo['current_discount'], 2);
                $priceSave = bcsub($priceSave, $autoPromoCodeInfo['renew_discount'], 2);
                // 设置优惠码折扣字段
                $cycle['promo_code_discount'] = $autoPromoCodeInfo['current_discount'];
            }
        }
        
        // 2. 应用用户等级折扣（根据互斥情况）
        if (!$promoCodeInfo['exclude']) {
            // 不互斥：叠加用户等级折扣
            $currentPrice = bcsub($currentPrice, $clientLevelDiscount['current'], 2);
            
            // 子产品用户等级折扣
            if ($sonProductInfo['son_host']) {
                $currentPrice = bcsub($currentPrice, $sonProductInfo['son_client_level_discount'], 2);
            }
            
            // 自然月预付费：price_save 使用续费折扣
            if (!empty($cycle['is_natural_month_prepaid'])) {
                $priceSave = bcsub($priceSave, $clientLevelDiscount['renew'], 2);
            } else {
                $priceSave = bcsub($priceSave, $clientLevelDiscount['current'], 2);
            }
            
            // 子产品 price_save 也减去用户等级折扣
            if ($sonProductInfo['son_host']) {
                $priceSave = bcsub($priceSave, $sonProductInfo['son_client_level_discount'], 2);
            }
        } else {
            // 互斥：不使用用户等级折扣
            // 但非循环优惠码互斥时，price_save 仍使用用户等级折扣（因为下次续费不会有优惠码）
            if (!$promoCodeInfo['is_loop']) {
                if (!empty($cycle['is_natural_month_prepaid'])) {
                    $priceSave = bcsub($priceSave, $clientLevelDiscount['renew'], 2);
                } else {
                    // 普通周期：使用原始用户等级折扣
                    $priceSave = bcsub($priceSave, $clientLevelDiscount['original'], 2);
                }
                
                // 子产品 price_save 也减去用户等级折扣
                if ($sonProductInfo['son_host']) {
                    $priceSave = bcsub($priceSave, $sonProductInfo['son_client_level_discount'], 2);
                }
            }
        }
        
        // 3. 如果有子产品，调整 price_save（减去子产品价格，加回子产品折扣）
        if ($sonProductInfo['son_host']) {
            // price_save 需要减去子产品价格
            $priceSave = bcsub($priceSave, $cycle['son_price'], 2);
            
            // 加回子产品的优惠码折扣
            if ($sonProductInfo['son_promo_discount'] > 0) {
                $priceSave = bcadd($priceSave, $sonProductInfo['son_promo_discount'], 2);
            }
            
            // 加回子产品的用户等级折扣
            if ($sonProductInfo['son_client_level_discount'] > 0) {
                $priceSave = bcadd($priceSave, $sonProductInfo['son_client_level_discount'], 2);
            }
        }
        
        // 4. 更新周期数据
        $cycle['price'] = max(0, $currentPrice);
        $cycle['price_save'] = max(0, $priceSave);
        
        // 5. 设置折扣信息
        $cycle['client_level_discount'] = $promoCodeInfo['exclude'] ? 0 : $clientLevelDiscount['current'];
        
        // 设置续费用户等级折扣（用于排查问题和下次续费计算）
        if (!empty($cycle['is_natural_month_prepaid'])) {
            // 自然月预付费：使用基于完整周期价格计算的续费折扣
            if ($promoCodeInfo['is_loop'] && $promoCodeInfo['exclude']) {
                $cycle['renew_client_level_discount'] = 0;
            } else {
                $cycle['renew_client_level_discount'] = $clientLevelDiscount['renew'];
            }
        } else {
            // 普通周期：续费折扣与当前折扣相同
            // 如果优惠码互斥，续费折扣为0（循环优惠码）或使用原始用户等级折扣（非循环优惠码）
            if ($promoCodeInfo['exclude']) {
                if ($promoCodeInfo['is_loop']) {
                    // 循环优惠码互斥：续费折扣为0
                    $cycle['renew_client_level_discount'] = 0;
                } else {
                    // 非循环优惠码互斥：续费折扣使用原始用户等级折扣（因为下次续费不会有优惠码）
                    $cycle['renew_client_level_discount'] = $clientLevelDiscount['original'];
                }
            } else {
                // 不互斥：续费折扣与当前折扣相同
                $cycle['renew_client_level_discount'] = $clientLevelDiscount['current'];
            }
        }
        
        // 6. 设置返回字段
        $cycle['promo_code_exclude_client_level'] = $promoCodeInfo['exclude'];
        $cycle['renew_promo_code_discount'] = $promoCodeInfo['is_loop'] ? $promoCodeInfo['renew_discount'] : 0;
        $cycle['promo_code_exclude_client_level_renew'] = $promoCodeInfo['is_loop'] && $promoCodeInfo['exclude'];
        
        if ($promoCodeInfo['is_loop']) {
            $cycle['loop'] = true;
        }
        
        return $cycle;
    }

    /**
     * 判断是否是当前周期
     * @param HostModel $host 产品实例
     * @param array $cycle 周期数据
     * @return bool 是否是当前周期
     * @author hh
     */
    private function isCurrentCycle(HostModel $host, array $cycle): bool
    {
        if (!empty($host->is_ontrial) || !empty($cycle['is_continuous_renew'])) {
            return false;
        }
        
        return $host->billing_cycle_time == $cycle['duration'] 
            || $host->billing_cycle_name == $cycle['billing_cycle'];
    }

    /**
     * 处理子产品价格和折扣
     * @param array $cycle 周期数据
     * @param HostModel $host 父产品实例
     * @param array $promoCodeInfo 优惠码信息
     * @param float $clientLevelDiscount 父产品用户等级折扣
     * @return array 处理结果 ['son_host'=>object, 'son_promo_discount'=>float, 'son_client_level_discount'=>float]
     * @author hh
     */
    private function processSonProduct(
        array $cycle,
        HostModel $host,
        array $promoCodeInfo,
        float $clientLevelDiscount
    ): array {
        $result = [
            'son_host' => null,
            'son_promo_discount' => 0,
            'son_client_level_discount' => 0,
        ];
        
        // 检查是否有子产品
        if (empty($cycle['son_host_id'])) {
            return $result;
        }
        
        // 获取子产品实例
        $result['son_host'] = (new HostModel())->find($cycle['son_host_id']);
        
        // 子产品优惠码折扣（使用父产品优惠码）
        if ($promoCodeInfo['is_loop']) {
            $hookResults = hook('apply_promo_code', [
                'host_id' => $host->id,
                'price' => $cycle['son_price'],
                'scene' => 'renew',
                'duration' => $cycle['duration']
            ]);
            
            foreach ($hookResults as $hookResult) {
                if ($hookResult['status'] == 200 && !empty($hookResult['data']['loop'])) {
                    $result['son_promo_discount'] = $hookResult['data']['discount'] ?? 0;
                    break;
                }
            }
        }
        
        // 子产品用户等级折扣
        if ($clientLevelDiscount > 0) {
            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
            $result['son_client_level_discount'] = $IdcsmartClientLevelModel->productDiscount([
                'client_id' => $host['client_id'],
                'id' => $host['product_id'],
                'amount' => $cycle['son_price']
            ]);
        }
        
        return $result;
    }

    /**
     * 检查是否应该跳过优惠码应用
     * @param array $promoCodeInfo 优惠码信息
     * @param string $scene 场景
     * @param array $usePromoCodeArr 已使用的优惠码数组
     * @param int $used 已使用次数
     * @return bool 是否跳过
     * @author hh
     */
    private function shouldSkipPromoCode(array $promoCodeInfo, string $scene, array $usePromoCodeArr, int $used): bool
    {
        if (empty($promoCodeInfo)) {
            return true;
        }
        
        // 批量续费场景：检查单用户单次限制
        if ($scene == 'batch_renew' 
            && !empty($promoCodeInfo['single_user_once']) 
            && in_array($promoCodeInfo['code'], $usePromoCodeArr)) {
            return true;
        }
        
        // 检查最大使用次数
        if (!empty($promoCodeInfo['max_times']) && $used >= $promoCodeInfo['max_times']) {
            return true;
        }
        
        return false;
    }
}

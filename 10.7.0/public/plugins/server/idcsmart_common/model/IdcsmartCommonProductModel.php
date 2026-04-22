<?php
namespace server\idcsmart_common\model;

use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ProductDurationRatioModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\common\model\ProductUpgradeProductModel;
use app\common\model\ServerModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamProductModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\logic\IdcsmartCommonCascadeLogic;
use server\idcsmart_common\logic\ProvisionLogic;
use think\Db;
use think\db\Query;
use think\Model;
use app\common\model\SelfDefinedFieldModel;

class IdcsmartCommonProductModel extends Model
{
    protected $name = 'module_idcsmart_common_product';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'order_page_description' => 'string',
        'allow_qty'              => 'int',
        'auto_support'           => 'int',
        'create_time'            => 'int',
        'update_time'            => 'int',
        'type'                   => 'string',
        'rel_id'                 => 'int',
        'config_option1'         => 'string',
        'config_option2'         => 'string',
        'config_option3'         => 'string',
        'config_option4'         => 'string',
        'config_option5'         => 'string',
        'config_option6'         => 'string',
        'config_option7'         => 'string',
        'config_option8'         => 'string',
        'config_option9'         => 'string',
        'config_option10'         => 'string',
        'config_option11'         => 'string',
        'config_option12'         => 'string',
        'config_option13'         => 'string',
        'config_option14'         => 'string',
        'config_option15'         => 'string',
        'config_option16'         => 'string',
        'config_option17'         => 'string',
        'config_option18'         => 'string',
        'config_option19'         => 'string',
        'config_option20'         => 'string',
        'config_option21'         => 'string',
        'config_option22'         => 'string',
        'config_option23'         => 'string',
        'config_option24'         => 'string'
    ];

    /**
     * 时间 2022-09-26
     * @title 商品基础信息
     * @desc 商品基础信息,插入默认价格信息
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return array
     * @return string pay_type - 付款类型：付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return object common_product - 商品信息
     * @return int common_product - 商品信息
     * @return int common_product.product_id - 商品ID
     * @return string common_product.order_page_description - 订购页面html
     * @return int common_product.allow_qty - 是否允许选择数量:1是，0否
     * @return int common_product.auto_support - 是否自动化支持:1是，0否
     * @return  object pricing - 周期信息(注意显示)
     * @return  float pricing.onetime - 一次性,价格(当pay_type=='onetime'时,只显示此价格)
     * @return object custom_cycle - 自定义周期
     * @return int custom_cycle.id - 自定义周期ID
     * @return string custom_cycle.name - 名称
     * @return int custom_cycle.cycle_time - 时长
     * @return string custom_cycle.cycle_unit - 时长单位
     * @return float custom_cycle.amount - 金额
     */
    public function indexProduct($param)
    {
        $productId = $param['product_id']??0;

        $ProductModel = new ProductModel();

        $product = $ProductModel->find($productId); # pay_type

        $configoptionFields = "";
        for ($i=1;$i<=24;$i++){
            $configoptionFields .= ',config_option'.$i;
        }

        $commonProduct = $this->field('product_id,order_page_description,allow_qty,auto_support,type,rel_id server_id'.$configoptionFields)
            ->where('product_id',$productId)
            ->find();

        # 插入默认数据
        if (empty($commonProduct)){
            $this->insert([
                'product_id' => $productId,
                'order_page_description' => '',
                'allow_qty' => 0,
                'auto_support' => 0,
                'create_time' => time()
            ]);
            $commonProduct = $this->field('product_id,order_page_description,allow_qty,auto_support'.$configoptionFields)
                ->where('product_id',$productId)
                ->find();
        }

        if (!empty($commonProduct)){
            $commonProduct['order_page_description'] = htmlspecialchars_decode($commonProduct['order_page_description']);
        }

        # 一次性价格
        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();
        if (empty($pricing)){
            $IdcsmartCommonPricingModel->commonInsert([],$productId,'product');
        }

        $pricing = $IdcsmartCommonPricingModel
            ->withoutField('id,type,rel_id')
            ->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        # 自定义周期及价格（根据自然月开关过滤）
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        
        // 判断是否开启自然月
        $isNaturalMonth = $product['natural_month_prepaid'] == 1;
        $cycleType = $isNaturalMonth ? 1 : 0;
        
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,cc.cycle_type,cc.status,ccp.amount,pdr.ratio')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->leftJoin('product_duration_ratio pdr','pdr.duration_id=cc.id AND pdr.product_id=cc.product_id')
            ->where('ccp.rel_id',$productId)
            ->where('cc.cycle_type', $cycleType)
            ->select()
            ->toArray();
            
        # 自定义周期为空,预设月-三年的周期
        if (empty($customCycle) && !in_array($product['pay_type'],['onetime','free'])){
            $IdcsmartCommonCustomCycleModel->preSetCycle($productId);

            $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
                ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,cc.cycle_type,cc.status,ccp.amount')
                ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
                ->where('ccp.rel_id',$productId)
                ->where('cc.cycle_type', $cycleType)
                ->select()
                ->toArray();
        }

        $config_option = [];
        for ($i=1;$i<=24;$i++){
            $config_option['config_option'.$i] = $commonProduct['config_option'.$i];
        }

        $data = [
            'pay_type' => $product['pay_type'],
            'common_product' => $commonProduct,
            'pricing' => $pricing??[],
            'custom_cycle' => $customCycle??[],
            'config_option' => $config_option
        ];

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];

        return $result;
    }

    /**
     * 时间 2022-09-26
     * @title 保存商品基础信息
     * @desc 保存商品基础信息
     * @author wyh
     * @version v1
     * @param int product_id - 商品ID require
     * @param string order_page_description - 订购页描述
     * @param int allow_qty - 是否允许选择数量:1是，0否默认
     * @param int auto_support - 自动化支持:开启后所有配置选项都可输入参数
     * @param object pricing - 周期价格,格式:{"onetime":0.1}
     * @param float pricing.onetime - 一次性价格
     * @param array configoption - 自定义配置值数组
     */
    public function createProduct($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $commonProduct = $this->where('product_id',$productId)->find();

            // 保存自定义配置
            $configoptionData = [];
            if (isset($param['configoption']) && is_array($param['configoption'])){
                foreach ($param['configoption'] as $key=>$value){
                    $configoptionData['config_option'.($key+1)] = $value;
                }
            }

            if (!empty($commonProduct)){
                $data = [
                    'order_page_description' => htmlspecialchars($param['order_page_description']),
                    'allow_qty' => intval($param['allow_qty']),
                    'auto_support' => intval($param['auto_support']),
                    'type' => 'server',
                    'rel_id' => $param['server_id']??0,
                    'update_time' => time()
                ];
                $data = array_merge($data,$configoptionData);
                $commonProduct->save($data);
            }else{
                $data = [
                    'product_id' => $productId,
                    'order_page_description' => htmlspecialchars($param['order_page_description']),
                    'allow_qty' => intval($param['allow_qty']),
                    'auto_support' => intval($param['auto_support']),
                    'type' => 'server',
                    'rel_id' => $param['server_id']??0,
                    'create_time' => time()
                ];
                $data = array_merge($data,$configoptionData);
                $this->insert($data);
            }

            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();

            $IdcsmartCommonPricingModel->commonInsert($param['pricing']??[],$productId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2023-12-18
     * @title 获取周期比例
     * @desc 获取周期比例
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int list[].id - 周期ID
     * @return  string list[].name - 周期名称
     * @return  int list[].num - 周期时长
     * @return  string list[].unit - 单位(hour=小时,day=天,month=月)
     * @return  string list[].ratio - 比例
     */
    public function indexRatio($product_id){
        try{
            // 获取商品信息，判断是否开启自然月
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($product_id);
            
            $cycleType = 0; // 默认普通周期
            if (!empty($product) && $product['natural_month_prepaid'] == 1) {
                $cycleType = 1; // 自然月周期
            }
            
            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $data = $IdcsmartCommonCustomCycleModel->alias('d')
                ->field('d.id,d.name,d.cycle_time as num,d.cycle_unit as unit,pdr.ratio')
                ->leftJoin('product_duration_ratio pdr', 'd.id=pdr.duration_id AND pdr.product_id='.$product_id)
                ->where('d.product_id', $product_id)
                ->where('d.cycle_type', $cycleType)
                ->orderRaw('field(d.cycle_unit, "hour","day","month")')
                ->order('d.cycle_time', 'asc')
                ->withAttr('ratio', function($val){
                    return $val ?? '';
                })
                ->group('d.id')
                ->select()
                ->toArray();
        }catch(\Exception $e){
            $data = [];
        }
        return $data;
    }

    /**
     * 时间 2023-12-18
     * @title 保存周期比例
     * @desc  保存周期比例
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   object ratio - 比例(如{"2":"1.5"},键是周期ID,值是比例) require
     */
    public function saveRatio($param){
        $productId = $param['product_id'] ?? 0;
        $product = ProductModel::find($productId);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist') ];
        }
        $old = $this->indexRatio($productId);

        $data = [];
        $detail = '';
        foreach($old as $v){
            if(isset($param['ratio'][$v['id']]) && $param['ratio'][$v['id']] > 0){
                $data[] = [
                    'product_id'    => $productId,
                    'duration_id'   => $v['id'],
                    'ratio'         => $param['ratio'][$v['id']],
                ];
                if($v['ratio'] != $param['ratio'][$v['id']]){
                    $detail .= lang('log_product_duration_ratio_change', [
                        '{name}' => $v['name'],
                        '{old}'  => $v['ratio'] ?? lang('null'),
                        '{new}'  => $param['ratio'][$v['id']],
                    ]);
                }
            }
        }
        if(empty($data) || count($old) != count($data)){
            return ['status'=>400, 'msg'=>lang('please_input_all_duration_ratio')];
        }

        $ProductDurationRatioModel = new ProductDurationRatioModel();

        $ProductDurationRatioModel->startTrans();
        try{
            $ProductDurationRatioModel->where('product_id', $param['product_id'])->delete();
            $ProductDurationRatioModel->insertAll($data);

            $ProductDurationRatioModel->commit();
        }catch(\Exception $e){
            $ProductDurationRatioModel->rollback();

            $result = [
                'status' => 400,
                'msg'    => $e->getMessage(),
            ];
            return $result;
        }

        if(!empty($detail)){
            $description = lang('log_save_product_duration_ratio', [
                '{product}' => 'product#'.$productId.'#'.$product['name'].'#',
                '{detail}'  => $detail,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('save_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-12-18
     * @title 计算自动填充
     * @desc  计算自动填充
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   object price - 价格(如{"2":"1.5"},键是周期ID,值是价格) require
     */
    public function autoFill($param){
        bcscale(2);

        $productId = $param['product_id'] ?? 0;
        $product = ProductModel::find($productId);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $data = $this->indexRatio($productId);
        if(empty($data)){
            return ['status'=>400, 'msg'=>lang('please_set_duration_ratio_first')];
        }
        $baseDuration = null;

        $res = [];
        foreach($data as $k=>$v){
            // 最小的周期作为基准
            if(isset($param['price'][$v['id']]) && $param['price'][$v['id']] >= 0){
                $baseDuration = $v;
                $baseDuration['price'] = $param['price'][$v['id']];
                break;
            }
        }
        if(is_null($baseDuration)){
            return ['status'=>400, 'msg'=>lang('please_set_at_lease_one_price')];
        }
        foreach($data as $v){
            if(empty($v['ratio'])){
                return ['status'=>400, 'msg'=>lang('please_set_duration_ratio_first')];
            }
            if(!isset($res[$v['id']])){
                if($v['id'] == $baseDuration['id']){
                    $res[$v['id']] = amount_format($baseDuration['price']);
                }else{
                    $res[$v['id']] = amount_format(bcdiv(bcmul($baseDuration['price'], $v['ratio']), $baseDuration['ratio']));
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'list'  => $res,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-09-26
     * @title 获取自定义周期详情
     * @desc 获取自定义周期详情
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义字段ID require
     * @return object custom_cycle
     * @return string custom_cycle.name - 名称
     * @return string custom_cycle.cycle_time - 周期时长
     * @return string custom_cycle.cycle_unit - 周期单位:day天,month月
     * @return string custom_cycle.amout - 金额
     */
    public function customCycle($param)
    {
        $productId = $param['product_id']??0;

        $id = $param['id']??0;

        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

        $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
        if (empty($customCycle)){
            return ['status'=>400,'msg'=>lang_plugins('idcsmart_common_custom_cycle_not_exist')];
        }

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        $customCyclePricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
            ->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        $customCycle['amount'] = $customCyclePricing['amount']??0;

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['custom_cycle'=>$customCycle??(object)[]]];
    }

    /**
     * 时间 2022-09-26
     * @title 添加自定义周期
     * @desc 添加自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int name - 名称 require
     * @param   int cycle_time - 周期时长 require
     * @param   int cycle_unit - 周期单位:day天,month月 require
     */
    public function createCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            // 获取商品信息，检查自然月状态
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            
            if (empty($product)) {
                throw new \Exception(lang_plugins('product_is_not_exist'));
            }
            
            // 如果开启了自然月预付费，不允许添加普通周期
            if ($product['natural_month_prepaid'] == 1) {
                throw new \Exception('商品已开启自然月预付费，不允许添加自定义周期');
            }

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $customCycleId = $IdcsmartCommonCustomCycleModel->insertGetId([
                'product_id' => $productId,
                'name' => $param['name']??'',
                'cycle_time' => $param['cycle_time']??0,
                'cycle_unit' => $param['cycle_unit']??'',
                'cycle_type' => 0, // 普通周期
                'status' => 1, // 默认启用
                'create_time' => time(),
            ]);

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonCustomCyclePricingModel->insert([
                'custom_cycle_id' => $customCycleId,
                'rel_id' => $productId,
                'type' => 'product',
                'amount' => $param['amount']??0,
            ]);

            # 默认增加配置子项价格
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

            $configoptionsId = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)->column('id');

            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

            $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->whereIn('product_configoption_id',$configoptionsId)
                ->select()
                ->toArray();
            $customCyclePricingArray = [];
            foreach ($configoptionSubs as $configoptionSub){
                $customCyclePricingArray[] = [
                    'custom_cycle_id' => $customCycleId,
                    'rel_id' => $configoptionSub['id'],
                    'type' => 'configoption',
                    'amount' => 0,
                ];
            }
            $IdcsmartCommonCustomCyclePricingModel->insertAll($customCyclePricingArray);

            # 更新商品最低价格
            $this->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 修改自定义周期
     * @desc 修改自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义周期ID require
     * @param   int product_id - 商品ID require
     * @param   string name - 名称 require
     * @param   int cycle_time - 周期时长 require
     * @param   string cycle_unit - 周期单位:day天,month月 require
     * @param   float amout - 金额 require
     */
    public function updateCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

            $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
            if (empty($customCycle)){
                throw new \Exception(lang_plugins('idcsmart_common_custom_cycle_not_exist'));
            }

            // 获取商品信息，检查自然月状态
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            $isNaturalMonth = $product['natural_month_prepaid'] == 1;
            
            // 验证：只能操作当前模式下的周期
            if ($isNaturalMonth && $customCycle['cycle_type'] != 1) {
                throw new \Exception('当前商品已开启自然月预付费，无法修改普通周期');
            }
            
            if (!$isNaturalMonth && $customCycle['cycle_type'] != 0) {
                throw new \Exception('当前商品未开启自然月预付费，无法修改自然月周期');
            }
            
            // 自然月周期只允许修改名称，不允许修改时长和单位
            if ($customCycle['cycle_type'] == 1) {
                $customCycle->save([
                    'name' => $param['name']??'',
                    'update_time' => time(),
                ]);
            } else {
                // 普通周期可以修改所有字段
                $customCycle->save([
                    'product_id' => $productId,
                    'name' => $param['name']??'',
                    'cycle_time' => $param['cycle_time']??0,
                    'cycle_unit' => $param['cycle_unit']??'',
                    'update_time' => time(),
                ]);
            }

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $customCyclePricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
                ->where('type','product')
                ->where('rel_id',$productId)
                ->find();
            if (!empty($customCyclePricing)){
                $customCyclePricing->save([
                    'amount' => $param['amount']??0,
                ]);
            }else{
                $IdcsmartCommonCustomCyclePricingModel->insert([
                    'custom_cycle_id' => $id,
                    'rel_id' => $productId,
                    'type' => 'product',
                    'amount' => $param['amount']??0,
                ]);
            }

            # 更新商品最低价格
            $this->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 删除自定义周期
     * @desc 删除自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义周期ID require
     */
    public function deleteCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

            $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
            if (empty($customCycle)){
                throw new \Exception(lang_plugins('idcsmart_common_custom_cycle_not_exist'));
            }

            // 获取商品信息，检查自然月状态
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            $isNaturalMonth = $product['natural_month_prepaid'] == 1;
            
            // 验证：只能操作当前模式下的周期
            if ($isNaturalMonth && $customCycle['cycle_type'] != 1) {
                throw new \Exception('当前商品已开启自然月预付费，无法删除普通周期');
            }
            
            if (!$isNaturalMonth && $customCycle['cycle_type'] != 0) {
                throw new \Exception('当前商品未开启自然月预付费，无法删除自然月周期');
            }
            
            // 自然月周期不允许删除
            if ($customCycle['cycle_type'] == 1) {
                throw new \Exception('自然月周期不允许删除，只能通过启用/禁用来控制');
            }
            
            // 检查：至少保留一个启用的周期
            $enabledCount = $IdcsmartCommonCustomCycleModel->where('product_id', $productId)
                ->where('cycle_type', $customCycle['cycle_type'])
                ->where('status', 1)
                ->count();
            
            if ($enabledCount <= 1 && $customCycle['status'] == 1) {
                throw new \Exception('至少需要保留一个启用的周期');
            }

            $customCycle->delete();

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
                ->where('type','product')
                ->where('rel_id',$productId)
                ->delete();

            # 删除配置子项价格
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $configoptionSubsId = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                ->where('pc.product_id',$productId)
                ->column('pcs.id');
            $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
                ->whereIn('rel_id',$configoptionSubsId)
                ->where('type','configoption')
                ->delete();

            # 更新商品最低价格
            $this->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 前台商品配置信息
     * @desc 前台商品配置信息
     * @url /console/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  object common_product - 商品基础信息
     * @return  string common_product.name - 商品名称
     * @return  string common_product.order_page_description - 订购页面html
     * @return  int common_product.allow_qty - 是否允许选择数量:1是，0否默认
     * @return  string common_product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  array configoptions - 配置项信息
     * @return  int configoptions[].id - 配置项ID
     * @return  string configoptions[].option_name - 配置项名称
     * @return  string configoptions[].option_type -  配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoptions[].qty_min - 数量时最小值
     * @return  int configoptions[].qty_max - 数量时最大值
     * @return  string configoptions[].unit - 单位
     * @return  int configoptions[].allow_repeat - 数量类型时：是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoptions[].max_repeat - 最大允许重复数量
     * @return  string configoptions[].description - 说明
     * @return  int configoptions[].qty_change - 数量变化值
     * @return array configoptions[].subs - 子项信息
     * @return  int configoptions[].subs[].id - 子项ID
     * @return  string configoptions[].subs[].option_name - 子项名称
     * @return  int configoptions[].subs[].qty_min - 子项最小值
     * @return  int configoptions[].subs[].qty_max - 子项最大值
     * @return object cycles - 周期({"onetime":1.00})
     * @return array custom_cycles - 自定义周期
     * @return int custom_cycles[].id - 自定义周期ID
     * @return string custom_cycles[].name - 自定义周期名称
     * @return int custom_cycles[].cycle_time - 自定义周期时长
     * @return string custom_cycles[].cycle_unit - 自定义周期单位
     * @return float custom_cycles[].amount - 商品自定义周期金额
     * @return float custom_cycles[].cycle_amount - (商品+配置项)自定义周期金额
     */
    public function cartConfigoption($param)
    {
        $productId = $param['product_id']??0;

        $commonProduct = $this->alias('cp')
            ->field('p.id,p.name,cp.order_page_description,cp.allow_qty,p.pay_type,p.product_id,p.natural_month_prepaid')
            ->leftJoin('product p','p.id=cp.product_id')
            ->where('cp.product_id',$productId)
            ->withAttr('name', function($value){
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
            ->withAttr('order_page_description', function($value){
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

        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $systemCycles = array_keys($IdcsmartCommonLogic->systemCycles);

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->field('id,product_id,option_name,option_type,qty_min,qty_max,unit,allow_repeat,max_repeat,description,configoption_id,qty_change')
            ->where('product_id',$productId)
            ->where('hidden',0)
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
                if(!empty($value)){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $value,
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $value = $multiLanguage['name'];
                    }
                }
                return $value;
            })
            ->withAttr('description', function($value){
                if(!empty($value)){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $value,
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $value = $multiLanguage['name'];
                    }
                }
                return $value;
            })
            ->order('order','asc') # 升序
            ->order('id','asc')
            ->select()
            ->toArray();
        # 配置子项价格(取第一个)
        $minSubPricings = [];
        // 引入级联逻辑类
        $IdcsmartCommonCascadeLogic = new IdcsmartCommonCascadeLogic();
        
        foreach ($configoptions as &$configoption){
            $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->field('pcs.id,pcs.option_name,pcs.qty_min,pcs.qty_max,pcs.country,pc.option_type,pc.fee_type,pcs.product_configoption_id')
                ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=pcs.product_configoption_id')
                ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoption['id'])
                ->where('pcs.hidden',0)
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
                ->order('pcs.order','asc')
                ->order('pcs.id','asc')
                ->select()
                ->toArray();
            
            // 处理级联类型配置项，添加树形结构数据
            if ($configoption['option_type'] == 'cascade') {
                $configoption['tree'] = $IdcsmartCommonCascadeLogic->getCascadeTree($configoption['id']);
                $configoption['cascade_group'] = $IdcsmartCommonCascadeLogic->getCascadeGroups($configoption['id']);
            }
            
            // 处理操作系统
            if ($configoption['option_type']=='os'){
                $osArray = [];
                foreach ($subs as $sub){
                    $optionNameArray = explode("^",$sub['option_name']);
                    if (count($optionNameArray)>=2){
                        $sub['option_name'] = $optionNameArray[1];
                        $osArray[$optionNameArray[0]][] = $sub;
                    }
                }
                $osArrayFilter = [];
                foreach ($osArray as $k=>$value){
                    $osArrayFilter[] = [
                        'os' => $k,
                        'version' => $value
                    ];
                }
                $configoption['subs'] = $osArrayFilter;
            }else{
                $configoption['subs'] = $subs??[];
            }

            if (!empty($subs[0])){
                $minSubPricings[] = $subs[0];
            }
        }

        $cycles = [];
        foreach ($systemCycles as $systemCycle){
            if ($pricing[$systemCycle]<0){
                unset($pricing[$systemCycle]);
            }else{
                $cycleFee = $pricing[$systemCycle]??0;

                foreach ($minSubPricings as $minSubPricing){
                    $cycleFee = bcadd($cycleFee,$minSubPricing[$systemCycle]??0,2);
                }

                $cycles[$systemCycle] = $cycleFee;
            }
        }

        # 自定义周期及价格，排除没有周期比例的，根据自然月开关过滤周期类型，只显示启用的周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        
        // 判断是否开启自然月
        $isNaturalMonth = $commonProduct['natural_month_prepaid'] == 1;
        $cycleType = $isNaturalMonth ? 1 : 0;
        
        $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->leftJoin('product_duration_ratio pdr','pdr.product_id=cc.product_id AND cc.id=pdr.duration_id')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0) # 可显示出得周期
            ->where('pdr.ratio','>',0)
            ->where('cc.cycle_type', $cycleType) # 根据自然月开关过滤周期类型
            ->where('cc.status', 1) # 只显示启用的周期
            ->group('cc.id')
            ->orderRaw('field(cc.cycle_unit, "hour","day","month")')
            ->order('cc.cycle_time','asc')
            ->withAttr('name', function($value){
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
        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        foreach ($customCycles as $key=>$customCycle){
            $customCycleAmount = $customCycle['amount']??0;

            # 配置子项的自定义价格
            foreach ($minSubPricings as $minSubPricing){
                if ($IdcsmartCommonLogic->checkQuantity($minSubPricing['option_type'])){
                    # 阶梯计费
                    if ($minSubPricing['fee_type'] == 'stage'){
                        $amount = $IdcsmartCommonLogic->quantityStagePrice($minSubPricing['product_configoption_id'],$minSubPricing['qty_min'],$customCycle['id'],0,true);
                    }else{ # 数量计费
                        $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                            ->where('rel_id',$minSubPricing['id'])
                            ->where('type','configoption')
                            ->value('amount');
                        $amount = $amount * $minSubPricing['qty_min'];
                    }

                }else{
                    $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                        ->where('rel_id',$minSubPricing['id'])
                        ->where('type','configoption')
                        ->value('amount');
                }
                $customCycleAmount = bcadd($customCycleAmount,$amount??0);
            }
            $customCycles[$key]['cycle_amount'] = $customCycleAmount;
        }

        if (empty($commonProduct) || (!empty($commonProduct) && $commonProduct['pay_type'] == 'free')){
            $cycles = [];
            $cycles['free'] = 0;
        }

        $data = [
            'common_product' => $commonProduct??(object)[],
            'configoptions' => $configoptions??[],
            'cycles' => $cycles??(object)[],
            'custom_cycles' => $customCycles??[]
        ];

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 前台商品配置信息计算价格
     * @desc 前台商品配置信息计算价格
     * @author wyh
     * @version v1
     * @param   object configoption - 配置信息{168:1,514:53} require
     * @return object cycles - 周期({"onetime":1.00})
     * @return array custom_cycles - 自定义周期
     * @return int custom_cycles[].id - 自定义周期ID
     * @return string custom_cycles[].name - 自定义周期名称
     * @return int custom_cycles[].cycle_time - 自定义周期时长
     * @return string custom_cycles[].cycle_unit - 自定义周期单位
     * @return float custom_cycles[].cycle_amount - 自定义周期金额
     */
    public function cartConfigoptionCalculate($param)
    {
        $param['configoption'] = $param['config_options']['configoption']??[];
        $param['cascade_configoption'] = $param['config_options']['cascade_configoption']??[];

        $productId = $param['product_id']??0;

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        # 自定义周期及价格，根据自然月开关过滤周期类型，只显示启用的周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        
        // 获取商品信息，判断是否开启自然月
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($productId);
        
        $cycleType = 0; // 默认普通周期
        if (!empty($product) && $product['natural_month_prepaid'] == 1) {
            $cycleType = 1; // 自然月周期
        }
        
        $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->leftJoin('product_duration_ratio pdr','pdr.product_id=cc.product_id AND cc.id=pdr.duration_id')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0) # 可显示出得周期
            ->where('pdr.ratio','>',0)
            ->where('cc.cycle_type', $cycleType) # 根据自然月开关过滤周期类型
            ->where('cc.status', 1) # 只显示启用的周期
            ->group('cc.id')
            ->orderRaw('field(cc.cycle_unit, "hour","day","month")')
            ->order('cc.cycle_time','asc')
            ->withAttr('name', function($value){
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
        foreach ($customCycles as &$customCycle){
            $param['cycle'] = $customCycle['id'];
            $result = $IdcsmartCommonLogic->cartCalculatePrice($param);
            $customCycle['cycle_amount'] = $result['data']['price']??bcsub(0,0,2);
        }

        $cycles = [];
        $systemCycles = array_keys($IdcsmartCommonLogic->systemCycles);
        foreach ($systemCycles as $systemCycle){
            $param['cycle'] = $systemCycle;
            $result = $IdcsmartCommonLogic->cartCalculatePrice($param);
            $cycles[$systemCycle] = $result['data']['price']??bcsub(0,0,2);
        }

        $data = [
            'custom_cycles' => $customCycles,
            'cycles' => $cycles
        ];

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 前台产品内页
     * @desc 前台产品内页
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host - 财务信息
     * @return  int host.create_time - 订购时间
     * @return  int host.due_time - 到期时间
     * @return  string host.billing_cycle - 计费方式:计费周期免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  string host.billing_cycle_name - 模块计费周期名称
     * @return  int host.billing_cycle_time - 模块计费周期时间,秒
     * @return  float host.renew_amount - 续费金额
     * @return  float host.first_payment_amount - 首付金额
     * @return  string host.dedicatedip - 独立ip
     * @return  string host.username - 用户名
     * @return  string host.password - 密码
     * @return  string host.os - 操作系统，后台未配置时显示远程操作系统模板ID
     * @return  string host.assignedips - 分配ip，逗号分隔
     * @return  int host.bwlimit - 流量限制
     * @return  float host.bwusage - 流量使用
     * @return  array configoptions - 配置项信息
     * @return  int configoptions[].id - 配置项ID
     * @return  string configoptions[].option_name - 配置项名称
     * @return  string configoptions[].option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域，os操作系统，cascade级联
     * @return  string configoptions[].unit - 单位
     * @return  array configoptions[].subs -
     * @return  string configoptions[].subs[].option_name - 子项名称
     * @return  int configoptions[].qty - 数量(当类型为数量时,显示此值)
     * @return  string configoptions[].cascade_path - 级联路径(当类型为cascade时,显示完整路径,如:地域 > 华北 > 北京)
     * @return array chart - 图表tab
     * @return string chart[].title - 标题
     * @return string chart[].type - 类型
     * @return array chart[].select - 下拉选择
     * @return string chart[].select[].name - 名称
     * @return string chart[].select[].value - 值
     * @return array client_area - 客户自定义tab区域
     * @return string client_area[].key - 键
     * @return string client_area[].name - 名称标题
     * @return array client_button - 管理按钮区域(默认模块操作)
     * @return array client_button.console - 控制台
     * @return string client_button.console[].func - 模块(调模块动作传此值)
     * @return string client_button.console[].name - 模块名称
     * @return string client_button.console[].type - 类型
     * @return array client_button.control - 下拉管理
     * @return string client_button.control[].func - 模块(调模块动作传此值)
     * @return string client_button.control[].name - 模块名称
     * @return string client_button.control[].type - 类型
     * @return array os - 操作系统
     * @return int os[].id - 配置项ID
     * @return string os[].option_name - 配置项名称
     * @return string os[].option_type - 配置项类型
     * @return array os[].subs - 子项
     * @return string os[].subs[].os - 操作系统
     * @return array os[].subs[].version - 操作系统详细版本
     * @return int os[].subs[].version[].id - 子项ID
     * @return string os[].subs[].version[].option_name - 名称
     */
    public function hostConfigotpion($param)
    {
        $hostId = $param['host_id']??0;

        $where = [];
        $where[] = ['h.id', '=', $hostId];
        $where[] = ['h.is_delete', '=', 0];
        
        $HostModel = new HostModel();

        $host = $HostModel->alias('h')
            ->field('h.id,h.order_id,h.product_id,h.create_time,h.due_time,h.billing_cycle,h.billing_cycle_name,
            h.billing_cycle_time,h.renew_amount,h.first_payment_amount,p.name,h.status,h.name as host_name,h.product_id,
            h.client_notes,hl.dedicatedip,hl.assignedips,hl.username,hl.password,hl.bwlimit,hl.os,hl.bwusage')
            ->leftJoin('product p','p.id=h.product_id')
            ->leftJoin('module_idcsmart_common_server_host_link hl','h.id=hl.host_id')
            ->where($where)
            ->withAttr('name', function($value){
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
            ->withAttr('password',function ($value){
                if (!empty($value)){
                    return password_decrypt($value);
                }
                return $value;
            })
            ->find();
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $host['status'] = $host['status'] != 'Failed' ? $host['status'] : 'Pending';
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->alias('pc')
            ->field('pc.id,pc.option_name,pc.option_type,pc.unit,hc.qty,hc.repeat,hc.cascade_item_id')
            ->leftJoin('module_idcsmart_common_host_configoption hc','hc.configoption_id=pc.id ')
            ->where('hc.host_id',$hostId)
            ->order("pc.order",'asc')
            ->withAttr('option_name',function ($value,$data){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'name' => $value,
                    ],
                ]);
                if(isset($multiLanguage['name'])){
                    $value = $multiLanguage['name'];
                }
                if ($data['repeat']>0){
                    return $value.$data['repeat'];
                }
                return $value;
            })
            ->withAttr('unit',function ($value){
                if(!empty($value)){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $value,
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $value = $multiLanguage['name'];
                    }
                }
                return $value;
            })
            ->select()
            ->toArray();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $configoptionMultiSelect = $configoptionOther = [];

        foreach ($configoptions as $key=>$configoption){
            // 处理级联配置项
            if ($configoption['option_type'] == 'cascade' && !empty($configoption['cascade_item_id'])) {
                // 使用getFullName方法获取级联路径
                $cascadePath = $IdcsmartCommonCascadeItemModel->getFullName($configoption['cascade_item_id']);
                
                // 获取级联项的fee_type来判断是否为数量类型
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($configoption['cascade_item_id']);
                if (!empty($cascadeItem) && in_array($cascadeItem['fee_type'], ['qty', 'stage']) && !empty($configoption['qty'])) {
                    $cascadePath .= ' x ' . $configoption['qty'];
                }
                
                $configoption['cascade_path'] = $cascadePath;
                $configoption['subs'] = [];
            } else {
                // 处理普通配置项
                $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                    ->field('pcs.id,pcs.option_name,pcs.country')
                    ->leftJoin('module_idcsmart_common_host_configoption hc','hc.configoption_sub_id=pcs.id')
                    ->where('hc.host_id',$hostId)
                    ->where('hc.configoption_id',$configoption['id'])
                    ->withAttr('option_name', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'name' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['name'])){
                            $value = $multiLanguage['name'];
                        }
                        return explode("^",$value)[1]??$value;
                    })
                    ->select()
                    ->toArray();
                $configoption['subs'] = $subs??[];
            }

            if ($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
                $configoptionMultiSelect[$configoption['id']] = $configoption;
            }else{
                $configoptionOther[] = $configoption;
            }

        }

        $configoptionFilter = array_merge($configoptionOther,array_values($configoptionMultiSelect));

        // TODO 内页其他自定义数据
        $ProvisionLogic = new ProvisionLogic();
        $chart = $ProvisionLogic->chart($hostId);
        $clientArea = $ProvisionLogic->clientArea($hostId);
        $clientButtonOutput = $ProvisionLogic->clientButtonOutput($hostId);
        $os = $IdcsmartCommonProductConfigoptionModel->field('id,option_name,option_type')
            ->where('product_id',$host['product_id'])
            ->where('hidden',0)
            ->where('option_type','os')
            ->find();
        if (!empty($os)){
            $osSubs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->field('pcs.id,pcs.option_name,pcs.option_param')
                ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=pcs.product_configoption_id')
                ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$os['id'])
                ->where('pcs.hidden',0)
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
                ->order('pcs.order','asc')
                ->order('pcs.id','asc')
                ->select()
                ->toArray();
        }else{
            $osSubs = [];
        }

        $osArray = [];
        // 处理操作系统
        foreach ($osSubs as $sub){
            $optionNameArray = explode("^",$sub['option_name']);
            if (count($optionNameArray)>=2){
                $sub['option_name'] = $optionNameArray[1];
                $osArray[$optionNameArray[0]][] = $sub;
            }
        }
        $osArrayFilter = [];
        foreach ($osArray as $k=>$value){
            $osArrayFilter[] = [
                'os' => $k,
                'version' => $value
            ];
        }
        $os['subs'] = $osArrayFilter;

        $data = [
            'host' => $host,
            'configoptions' => $configoptionFilter??[],
            'chart' => $chart,
            'client_area' => $clientArea,
            'client_button' => $clientButtonOutput,
            'os' => $os??(object)[]
        ];

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];

        return $result;
    }

    /**
     * 时间 2023-11-21
     * @title 前台产品内页自定义页面输出
     * @desc 前台产品内页自定义页面输出
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string key - snapshot快照等 require
     * @param   string api_url - 替换原来模板内的接口地址
     */
    public function clientAreaOutput($param)
    {
        $hostId = $param['host_id']??0;
        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);

        if ($host['status']!='Active' || get_client_id()!=$host['client_id']){
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        // sync模式的上下游，调接口
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id'])
            ->where('mode','sync')
            ->find();
        if (!empty($upstreamProduct)){
            $upstreamHost = UpstreamHostModel::where('host_id',$hostId)->find();
            unset($param['host_id']);
            $param['api_url'] = request()->domain().request()->rootUrl()."/console/v1/idcsmart_common/host/{$hostId}/custom/provision";
            return idcsmart_api_curl($upstreamProduct['supplier_id'],"/console/v1/idcsmart_common/host/{$upstreamHost['upstream_host_id']}/configoption/area",$param,30,'GET');
        }else{
            $ProvisionLogic = new ProvisionLogic();
            $html = $ProvisionLogic->clientAreaDetail($hostId,$param['key']??"",$param['api_url']??'');
            return [
                'status' => 200,
                'msg' => lang_plugins('success_message'),
                'data' => [
                    'html' => $html
                ]
            ];
        }
    }

    /**
     * 时间 2023-11-21
     * @title 前台产品内页图表页面
     * @desc 前台产品内页图表页面
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   array chart - 图表数据 require
     * @param   int chart[].start - 开始时间 require
     * @param   int chart[].type - 类型：cpu/disk/flow require
     * @param   array chart[].select[] - 传select下得value组合成的数组 require
     */
    public function chartData($param)
    {
        $hostId = $param['host_id']??0;
        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);

        if ($host['status']!='Active' || get_client_id()!=$host['client_id']){
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        // sync模式的上下游，调接口
        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id'])
            ->where('mode','sync')
            ->find();
        if (!empty($upstreamProduct)){
            $upstreamHost = UpstreamHostModel::where('host_id',$hostId)->find();
            unset($param['host_id']);
            return idcsmart_api_curl($upstreamProduct['supplier_id'],"/console/v1/idcsmart_common/host/{$upstreamHost['upstream_host_id']}/configoption/chart",$param);
        }else{
            $ProvisionLogic = new ProvisionLogic();
            $res = $ProvisionLogic->getChartData($hostId,$param['chart']);
            if ($res['status']=='success'){
                return [
                    'status' => 200,
                    'msg' => lang_plugins('success_message'),
                    'data' => $res['data']
                ];
            }else{
                return [
                    'status' => 400,
                    'msg' => $res['msg']
                ];
            }
        }
    }

    /**
     * 时间 2023-11-21
     * @title 执行子模块方法
     * @desc 执行子模块方法
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string func - 模块方法:如on开机/off关机等 require
     */
    public function provisionFunc($param)
    {
        $func = $param['func'];

        $hostId = $param['host_id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);

        if ($host['status']!='Active' || get_client_id()!=$host['client_id']){
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id'])
            ->where('mode','sync')
            ->find();

        // 调上游接口
        if (!empty($upstreamProduct)){
            $upstreamHost = UpstreamHostModel::where('host_id',$hostId)->find();
            unset($param['host_id']);
            //$param['api_url'] = request()->domain().request()->rootUrl()."/console/v1/idcsmart_common/host/{$hostId}/provision/{$func}";
            $result = idcsmart_api_curl($upstreamProduct['supplier_id'],"/console/v1/idcsmart_common/host/{$upstreamHost['upstream_host_id']}/provision/{$func}",$param);
        }else{
            $ProvisionLogic = new ProvisionLogic();

            // 特殊处理两个方法
            switch ($func){
                case "crack_pass":
                    $password = $param['password']??"";
                    $result = $ProvisionLogic->crackPassword($hostId,$password);
                    if ($result['status']=='success' || $result['status']==200){
                        $IdcsmartCommonServerHostLinkModel = new IdcsmartCommonServerHostLinkModel();
                        $IdcsmartCommonServerHostLinkModel->where('host_id',$hostId)->update(['password'=>password_encrypt($password)]);
                    }
                    break;
                case "reinstall":
                    $os = $param['os']??0;
                    $port = $param['os_name']??"";
                    $subId = $param['sub_id']??0;
                    $optionId = $param['option_id']??0;
                    $result = $ProvisionLogic->reinstall($hostId,$os,$port,$subId,$optionId);
                    if ($result['status']=='success' || $result['status']==200){
                        // 修改关联
                        if ($subId){
                            $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
                            $IdcsmartCommonHostConfigoptionModel->where('host_id',$hostId)->where('configoption_id',$optionId)
                                ->update(['configoption_sub_id'=>$subId]);
                        }
                    }
                    break;
                default:
                    $funcJava = parse_name($func,1,false);
                    $result = $ProvisionLogic->$funcJava($hostId);
                    break;
            }
        }

        if ($result['status']=='success' || $result['status']==200){
            $result['status'] = 200;
            $result['msg'] = lang_plugins('success_message');
            if (!in_array($func,['status'])){
                $description = lang_plugins('log_idcsmart_common_success_'.$func, [
                    '{host_id}' => $hostId,
                ]);
                active_log($description, 'host', $hostId);
            }
        }else{
            $result['status'] = 400;
            if (!in_array($func,['status'])){
                $description = lang_plugins('log_idcsmart_common_fail_'.$func, [
                    '{host_id}' => $hostId,
                    '{fail}' => $result['msg']
                ]);
                active_log($description, 'host', $hostId);
            }
        }

        return $result;
    }

    /**
     * 时间 2023-11-21
     * @title 执行子模块自定义方法
     * @desc 执行子模块自定义方法
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string func - 模块方法:如on开机/off关机等 require
     * @param   array custom_fields - 自定义字段
     */
    public function provisionFuncCustom($param)
    {
        $hostId = $param['host_id']??0;

        $ProvisionLogic = new ProvisionLogic();

        $func = $param['func'];

        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);

        if ($host['status']!='Active' || get_client_id()!=$host['client_id']){
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        $upstreamProduct = UpstreamProductModel::where('product_id',$host['product_id'])
            ->where('mode','sync')
            ->find();

        // 调上游接口
        if (!empty($upstreamProduct)){
            $upstreamHost = UpstreamHostModel::where('host_id',$hostId)->find();
            unset($param['host_id']);
            //$param['api_url'] = request()->domain().request()->rootUrl()."/console/v1/idcsmart_common/host/{$hostId}/custom/provision";
            $result = idcsmart_api_curl($upstreamProduct['supplier_id'],"/console/v1/idcsmart_common/host/{$upstreamHost['upstream_host_id']}/custom/provision",$param);
        }else{
            $result = $ProvisionLogic->execCustomFunc($func,$hostId,'client',$param['custom_fields']??[]);
        }

        $func = strtolower($func);
        if ($result['status']=='success' || $result['status']==200){
            $result['status'] = 200;
            $result['msg'] = lang_plugins("success_message");
            $description = lang_plugins('log_idcsmart_common_success_'.$func, [
                '{host_id}' => $hostId,
            ]);
            active_log($description, 'host', $hostId);
        }else{
            $result['status'] = 400;
            $description = lang_plugins('log_idcsmart_common_fail_'.$func, [
                '{host_id}' => $hostId,
                '{fail}' => $result['msg']
            ]);
            active_log($description, 'host', $hostId);
        }

        return $result;
    }

    /**
     * 时间 2022-09-28
     * @title 产品列表
     * @desc 产品列表
     * @author wyh
     * @version v1
     * @param int m - 菜单ID
     * @param int client_id - 客户ID
     * @param string keywords - 关键字,搜索范围:产品ID,商品名称,标识
     * @param string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param string tab - 状态using使用中expiring即将到期overdue已逾期deleted已删除
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,active_time,due_time
     * @param string sort - 升/降序 asc,desc
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
     * @return object list[].self_defined_field - 产品自定义字段自定义字段，格式{"自定义字段ID":"值"}
     * @return int list[].is_auto_renew - 是否自动续费(0=否,1=是)
     * @return int count - 产品总数
     * @return int using_count - 使用中产品数量
     * @return int expiring_count - 即将到期产品数量
     * @return int overdue_count - 已逾期产品数量
     * @return int deleted_count - 已删除产品数量
     * @return int all_count - 全部产品数量
     */
    public function hostList($param)
    {
        $param['m'] = $param['m'] ?? 0;
        $param['client_id'] = get_client_id();
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'client_id', 'product_name', 'name', 'active_time', 'due_time', 'first_payment_amount', 'status']) ? $param['orderby'] : 'id';
        if($param['orderby']=='product_name'){
            $param['orderby'] = 'p.name';
        }else{
            $param['orderby'] = 'h.'.$param['orderby'];
        }

        $menu = MenuModel::find($param['m']);
        if(!empty($menu)){
            $param['product_id'] = json_decode($menu['product_id'], true);
        }else{
            $param['product_id'] = [];
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        $param['host_id'] = $hostId ?? [];
        $param['tab'] = $param['tab'] ?? '';

        $where = function (Query $query) use($param) {
            if(!empty($param['host_id'])){
                $query->whereIn('h.id', $param['host_id']);
            }
            if(!empty($param['product_id'])){
                $query->whereIn('h.product_id', $param['product_id']);
            }
            if(!empty($param['client_id'])){
                $query->where('h.client_id', $param['client_id'])->where('h.status', '<>', 'Cancelled');
            }
            // 前台是否展示已删除产品
            $homeShowDeletedHost = configuration('home_show_deleted_host');
            if($homeShowDeletedHost!=1){
                $query->where('h.status', '<>', 'Deleted');
            }
            if(!empty($param['keywords'])){
                try{
                    $language = get_client_lang();

                    $filterProductId = ProductModel::alias('p')
                        ->leftJoin('addon_multi_language ml', 'p.name=ml.name')
                        ->leftJoin('addon_multi_language_value mlv', 'ml.id=mlv.language_id AND mlv.language="'.$language.'"')
                        ->whereLike('p.name|mlv.value', '%'.$param['keywords'].'%')
                        ->limit(200)
                        ->column('p.id');
                    if(!empty($filterProductId)){
                        $query->where(function($query) use ($param, $filterProductId) {
                            $query->whereOr('h.id|h.name|c.username|c.email|c.phone|h.client_notes', 'like', "%{$param['keywords']}%")
                                ->whereOr('p.id', 'IN', $filterProductId);
                        });
                    }else{
                        $query->where('h.id|p.name|h.name|c.username|c.email|c.phone|h.client_notes', 'like', "%{$param['keywords']}%");   
                    }
                }catch(\Exception $e){
                    $query->where('h.id|p.name|h.name|c.username|c.email|c.phone|h.client_notes', 'like', "%{$param['keywords']}%");
                }
            }
            if(!empty($param['status'])){
                if($param['status'] == 'Pending'){
                    $query->whereIn('h.status',['Pending','Failed']);
                }else{
                    $query->where('h.status', $param['status']);
                }
            }
            if(!empty($param['tab'])){
                if($param['tab']=='using'){
                    $query->whereIn('h.status', ['Pending', 'Active']);
                }else if($param['tab']=='expiring'){
                    $time = time();
                    $renewalFirstDay = configuration('cron_due_renewal_first_day');
                    $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));
                    $query->whereIn('h.status', ['Pending', 'Active'])->where('h.due_time', '>', $time)->where('h.due_time', '<=', $timeRenewalFirst)->where('h.billing_cycle', '<>', 'free')->where('h.billing_cycle', '<>', 'onetime');
                }else if($param['tab']=='overdue'){
                    $time = time();
                    $query->whereIn('h.status', ['Pending', 'Active', 'Suspended', 'Failed'])->where('h.due_time', '<=', $time)->where('h.billing_cycle', '<>', 'free')->where('h.billing_cycle', '<>', 'onetime');
                }else if($param['tab']=='deleted'){
                    $time = time();
                    $query->where('h.status', 'Deleted');
                }
            }
            $query->where('s.module','idcsmart_common');

            $query->where('p.product_id',0);
            $query->where('h.is_delete', 0);
        };
        $HostModel = new HostModel();
        $count = $HostModel->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->where($where)
            ->count();
        $hosts = $HostModel->alias('h')
            ->field('h.id,h.product_id,h.client_id,c.username client_name,c.email,c.phone_code,c.phone,c.company,h.product_id,p.name product_name,h.name,h.create_time,h.active_time,h.due_time,h.first_payment_amount,h.renew_amount,h.billing_cycle,h.billing_cycle_name,h.status,o.pay_time,h.client_notes')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftjoin('order o', 'o.id=h.order_id')
            ->where($where)
            ->withAttr('status',function ($value){
                if ($value=='Failed'){
                    return 'Pending';
                }
                return $value;
            })
            ->withAttr('product_name', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'product_name' => $val,
                    ],
                ]);
                if(isset($multiLanguage['product_name'])){
                    $val = $multiLanguage['product_name'];
                }
                return $val;
            })
            ->withAttr('billing_cycle_name', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'billing_cycle_name' => $val,
                    ],
                ]);
                if(isset($multiLanguage['billing_cycle_name'])){
                    $val = $multiLanguage['billing_cycle_name'];
                }
                return $val;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        if(!empty($hosts)){
            $hostId = array_column($hosts, 'id');
            $productId = array_column($hosts, 'product_id');

            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $selfDefinedField = $SelfDefinedFieldModel->getHostListSelfDefinedFieldValue([
                'product_id' => $productId,
                'host_id'    => $hostId,
            ]);

            $autoRenewHostId = hook_one('get_auto_renew_host_id', ['host_id'=>$hostId]);
            $autoRenewHostId = $autoRenewHostId ? array_flip($autoRenewHostId) : [];
        }
        foreach ($hosts as $key => $host) {
            $hosts[$key]['first_payment_amount'] = amount_format($host['first_payment_amount']); // 处理金额格式
            $hosts[$key]['billing_cycle'] = $host['billing_cycle']!='onetime' ? $host['billing_cycle_name'] : '';
            $hosts[$key]['self_defined_field'] = $selfDefinedField['self_defined_field_value'][ $host['id'] ] ?? (object)[];
            $hosts[$key]['is_auto_renew'] = isset($autoRenewHostId[ $host['id'] ]) ? 1 : 0;

            unset($hosts[$key]['client_id'], $hosts[$key]['client_name'], $hosts[$key]['email'], $hosts[$key]['phone_code'], $hosts[$key]['phone'], $hosts[$key]['company']);

            unset($hosts[$key]['billing_cycle_name'], $hosts[$key]['create_time'], $hosts[$key]['pay_time']);
        }

        $where = function (Query $query) use ($param){
            if(!empty($param['host_id'])){
                $query->whereIn('h.id', $param['host_id']);
            }
            if(!empty($param['product_id'])){
                $query->whereIn('h.product_id', $param['product_id']);
            }
            if(!empty($param['client_id'])){
                $query->where('h.client_id', $param['client_id'])->where('h.status', '<>', 'Cancelled');
            }
            // 前台是否展示已删除产品
            $homeShowDeletedHost = configuration('home_show_deleted_host');
            if($homeShowDeletedHost!=1){
                $query->where('h.status', '<>', 'Deleted');
            }

            $query->where('s.module','idcsmart_common');

            $query->where('p.product_id',0);
            $query->where('h.is_delete', 0);
        };
        $usingCount = $HostModel->usingCount($where);
        $expiringCount = $HostModel->expiringCount($where);
        $overdueCount = $HostModel->overdueCount($where);
        $deletedCount = $HostModel->deletedCount($where);
        $allCount = $HostModel->allCount($where);

        return ['list' => $hosts, 'count' => $count, 'using_count' => $usingCount, 'expiring_count' => $expiringCount, 'overdue_count' => $overdueCount, 'deleted_count' => $deletedCount, 'all_count' => $allCount, 'self_defined_field' => $selfDefinedField['self_defined_field'] ?? []];
    }

    # 删除商品时实现钩子
    public function deleteProduct($param)
    {
        $productId = $param['id']??0;

        $this->startTrans();

        try{
            $this->where('product_id',$productId)->delete();

            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $IdcsmartCommonPricingModel->where('type','product')->where('rel_id',$productId)->delete();

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->delete();

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonCustomCyclePricingModel->where('type','product')->where('rel_id',$productId)->delete();

            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $configoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                ->select()
                ->toArray();
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            foreach ($configoptions as $configoption){
                $configoptionSubsId = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption['id'])
                    ->column('id');
                $IdcsmartCommonCustomCyclePricingModel->whereIn('rel_id',$configoptionSubsId)
                    ->where('type','configoption')
                    ->delete();

                $IdcsmartCommonPricingModel->whereIn('rel_id',$configoptionSubsId)
                    ->where('type','configoption')
                    ->delete();

                $IdcsmartCommonProductConfigoptionSubModel->whereIn('id',$configoptionSubsId)->delete();
                $IdcsmartCommonProductConfigoptionModel->where('id',$configoption['id'])->delete();
            }
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return false;
        }

        return true;
    }

    /**
     * 递归查找树形结构中的第一个末端级联项
     * @param array $tree - 级联树形结构
     * @return array|null - 返回第一个末端级联项，如果没有则返回null
     */
    private function findFirstLeafItem($tree)
    {
        foreach ($tree as $item) {
            // 如果是末端项（is_leaf == 1），直接返回
            if (isset($item['is_leaf']) && $item['is_leaf'] == 1) {
                return $item;
            }
            // 如果有子节点，递归查找
            if (isset($item['children']) && !empty($item['children'])) {
                $result = $this->findFirstLeafItem($item['children']);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    # 更新商品最低配置价格数据
    public function updateProductMinPrice($product_id)
    {
        $res = $this->productMinPrice($product_id);

        $ProductModel = new ProductModel();

        $ProductModel->setPriceCycle($product_id, $res['price'], $res['cycle']);
        return true;
    }

    # 获取商品最低配置价格数据
    public function productMinPrice($product_id)
    {
        $product = ProductModel::find($product_id);

        $cartConfigoption = $this->cartConfigoption([
            'product_id' => $product_id,
        ]);
        $cartConfigoption = $cartConfigoption['data'] ?? [];

        $configoptions = [];
        $cascadeConfigoptions = [];
        if(isset($cartConfigoption['configoptions']) && is_array($cartConfigoption['configoptions'])){
            foreach($cartConfigoption['configoptions'] as $v){
                // 区域/下拉单选/单选/是否/操作系统
                if($v['option_type'] == 'area' || $v['option_type'] == 'select' || $v['option_type'] == 'radio' || $v['option_type'] == 'yes_no' || $v['option_type'] == 'os' ){
                    if(isset($v['subs'][0])){
                        if ($v['option_type'] == 'os'){
                            $configoptions[ $v['id'] ] = $v['subs'][0]['version'][0]['id']??$v['subs'][0]['id'];
                        }else{
                            $configoptions[ $v['id'] ] = $v['subs'][0]['id'];
                        }
                    }
                }else if($v['option_type'] == 'multi_select'){
                    //下拉多选
                    if(isset($v['subs'][0])){
                        $configoptions[ $v['id'] ] = [$v['subs'][0]['id']];
                    }
                }else if($v['option_type'] == 'quantity' || $v['option_type'] == 'quantity_range'){
                    // 数量单选/数量拖动
                    if(isset($v['subs'][0])){
                        $configoptions[ $v['id'] ] = [$v['subs'][0]['qty_min']];
                    }
                }else if($v['option_type'] == 'cascade'){
                    // 级联配置项 - 使用cascade_configoption参数
                    // 数据结构: {"配置项ID": {"item_id": 级联项ID, "quantity": 数量}}
                    // 从tree中递归查找第一个末端级联项
                    if(isset($v['tree']) && !empty($v['tree'])){
                        $firstLeafItem = $this->findFirstLeafItem($v['tree']);
                        if($firstLeafItem){
                            $cascadeConfigoptions[ $v['id'] ] = [
                                'item_id' => $firstLeafItem['item_id'],
                            ];
                            // 如果是数量类型，添加最小数量
                            if(in_array($firstLeafItem['fee_type'], ['qty', 'stage'])){
                                // 从price数组中获取最小数量
                                if(isset($firstLeafItem['price'][0]['qty_min'])){
                                    $cascadeConfigoptions[ $v['id'] ]['quantity'] = $firstLeafItem['price'][0]['qty_min'];
                                }else{
                                    $cascadeConfigoptions[ $v['id'] ]['quantity'] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        $cycle = null;
        if ($product['pay_type']=='free'){
            $price = 0;
        }elseif ($product['pay_type']=='onetime'){
            $IdcsmartCommonLogic = new IdcsmartCommonLogic();
            $res = $IdcsmartCommonLogic->cartCalculatePrice([
                'product_id'           => $product_id,
                'configoption'         => $configoptions,
                'cascade_configoption' => $cascadeConfigoptions,
                'cycle'                => 'onetime',
            ]);

            if($res['status'] == 200){
                $cycle = $res['data']['billing_cycle'];
                $price = $res['data']['price'];
            }
        }else{
            if (!isset($cartConfigoption['custom_cycles'][0]['id'])){
                return ['price'=>0, 'cycle'=>null];
            }
            $IdcsmartCommonLogic = new IdcsmartCommonLogic();
            $res = $IdcsmartCommonLogic->cartCalculatePrice([
                'product_id'           => $product_id,
                'configoption'         => $configoptions,
                'cascade_configoption' => $cascadeConfigoptions,
                'cycle'                => $cartConfigoption['custom_cycles'][0]['id'] ?? 0,
            ]);

            if($res['status'] == 200){
                $cycle = $res['data']['billing_cycle'];

                if(!empty($res['data']['is_natural_month_prepaid'])){
                    $price = $res['data']['renew_price'];
                }else{
                    $price = $res['data']['price'];
                }
            }
        }

        return ['price'=>$price, 'cycle'=>$cycle];
    }

    /**
     * 时间 2022-09-26
     * @title 产品升降级页面
     * @desc 产品升降级页面
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host -
     * @return  int host.product_id - 商品ID
     * @return  string host.name - 名称
     * @return  float host.first_payment_amount - 金额
     * @return  string host.billing_cycle_name - 周期
     * @return  array configoptions - 配置
     * @return  string configoptions[].option_type - 配置类型
     * @return  string configoptions[].option_name - 名称
     * @return  string configoptions[].sub_name - 子项名称
     * @return  int configoptions[].qty - 数量(类型为数量时,显示此值)
     * @return  int configoptions[].configoption_sub_id - 子项ID
     * @return  array son_host - 子产品
     * @return  int son_host[].id - 子产品ID
     * @return  string son_host[].name - 名称
     * @return  float son_host[].first_payment_amount - 金额
     * @return  string son_host[].billing_cycle_name - 周期
     * @return  array upgrade - 可升降级商品(参考购物车配置那块数据)
     */
    public function upgradePage($param)
    {
        $hostId = $param['host_id'];

        $where = [];
        $where[] = ['h.id', '=', $hostId];
        $where[] = ['h.is_delete', '=', 0];
        
        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.product_id,p.name,h.first_payment_amount,h.billing_cycle_name')
            ->leftJoin('product p','p.id=h.product_id')
            ->where($where)
            ->withAttr('name', function($val){
                return multi_language_replace($val);
            })
            ->find();
        if (empty($host)){
            return [
                'status' => 400,
                'msg' => lang_plugins('error_message')
            ];
        }

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('dpc.id as configoption_id,dpc.option_type,dpc.option_name,dpcs.option_name as sub_name,hc.qty,hc.configoption_sub_id,hc.cascade_item_id')
            ->leftJoin('module_idcsmart_common_product_configoption dpc','dpc.id=hc.configoption_id')
            ->leftJoin('module_idcsmart_common_product_configoption_sub dpcs','dpcs.id=hc.configoption_sub_id')
            ->where('hc.host_id',$hostId)
            ->where('dpc.son_product_id',0) # 没有子商品
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
                return explode('^', $value)[1] ?? $value;
            })
            ->select()
            ->toArray();

        // 为级联配置项添加树形数据
        $IdcsmartCommonCascadeLogic = new IdcsmartCommonCascadeLogic();
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        foreach ($configoptions as &$configoption) {
            if ($configoption['option_type'] == 'cascade') {
                $configoption['tree'] = $IdcsmartCommonCascadeLogic->getCascadeTree($configoption['configoption_id']);
                $configoption['cascade_group'] = $IdcsmartCommonCascadeLogic->getCascadeGroups($configoption['configoption_id']);
                // 标记当前选择的级联项
                if (!empty($configoption['cascade_item_id'])) {
                    $configoption['current_cascade_item_id'] = $configoption['cascade_item_id'];
                    
                    // 使用getFullName方法获取级联路径
                    $cascadePath = $IdcsmartCommonCascadeItemModel->getFullName($configoption['cascade_item_id']);
                    
                    // 获取级联项的fee_type来判断是否为数量类型
                    $cascadeItem = $IdcsmartCommonCascadeItemModel->find($configoption['cascade_item_id']);
                    if (!empty($cascadeItem) && in_array($cascadeItem['fee_type'], ['qty', 'stage']) && !empty($configoption['qty'])) {
                        $cascadePath .= ' x ' . $configoption['qty'];
                    }
                    
                    $configoption['sub_name'] = $cascadePath;
                }
            }
        }

        $productId = $host['product_id'];

        $product = ProductModel::find($productId);

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();

        $upgradeProductIds = $ProductUpgradeProductModel
                        ->alias('pup')
                        ->join('product p', 'pup.upgrade_product_id=p.id')
                        ->where('pup.product_id',$productId)
                        ->where('p.natural_month_prepaid', $product['natural_month_prepaid'] ?? 0)
                        ->column('pup.upgrade_product_id');

        $upgrade = [];
        // 普通周期才支持商品升降级
        if($product['natural_month_prepaid'] == 0){
            foreach ($upgradeProductIds as $upgradeProductId){
                $result = $this->cartConfigoption(['product_id'=>$upgradeProductId]);
                $upgrade[] = $result['data']??[];
            }
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'host' => $host,
                'configoptions' => $configoptions,
                'upgrade' => $upgrade
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 产品升降级异步获取升降级价格
     * @desc 产品升降级异步获取升降级价格
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param  object  - 与购物车计算价格参数一致:{"configoption":{"1"：2,"2":3,"4":[1,2,3]},"cycle":"monthly","product_id":104,son:{}}
     */
    public function syncUpgradePrice($param)
    {
        $time = time();

        $hostId = $param['host_id'];

        $where = [];
        $where[] = ['h.id', '=', $hostId];
        $where[] = ['h.is_delete', '=', 0];

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.product_id,p.name,h.first_payment_amount,h.billing_cycle,h.billing_cycle_time,h.billing_cycle_name,h.due_time,h.active_time')
            ->leftJoin('product p','p.id=h.product_id')
            ->where($where)
            ->find();
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('success_message')];
        }

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $result = $IdcsmartCommonLogic->cartCalculatePrice([
            'configoption' => $param['configoption']??[],
            'cascade_configoption' => $param['cascade_configoption']??[],
            'product_id' => $param['product_id']??0,
            'cycle' => $param['cycle']??'',
            'son' => $param['son']??[]
        ]);

        // 计算退款金额
        if($host['billing_cycle']=='onetime'){
            $refund = $host['first_payment_amount'];
        }else if($host['billing_cycle']=='free'){
            $refund = 0;
        }else{
            if($host['billing_cycle_time']>0){
                if(($host['due_time']-$time)>0){ // 以自然年计算,周期时间不固定,闰年多一天,导致金额有误差
                    $hookResult = hook_one('renew_host_refund_amount',['id'=>$hostId]);
                    $renewRefundTotal = $hookResult[0]??0; // 总续费退款
                    $renewCycleTotal = $hookResult[1]??0; // 总续费周期
                    if (isset($hookResult[2]) && $hookResult[2]){
                        $refund = $renewRefundTotal;
                    }else{
                        $hostBillingCycleTime = $host['due_time']-$renewCycleTotal-$host['active_time']; // 产品购买周期=(总到期时间-续费周期-开通时间)
                        $refund = bcdiv(bcdiv($host['first_payment_amount'],$hostBillingCycleTime,20)*($host['due_time']-$renewCycleTotal-$time), 1, 2);
                        $refund = bcadd($refund,$renewRefundTotal,2);
                    }
                    //$refund = bcdiv(bcdiv($host['first_payment_amount'],$host['billing_cycle_time'],20)*($host['due_time']-$time), 1, 2);
                }else{
                    $refund = $host['first_payment_amount'];
                }
            }else{
                $refund = $host['first_payment_amount'];
            }
        }

        $ProductModel = new ProductModel();
        $product = $ProductModel->find($param['product_id']??0);

        $basePrice = $result['data']['price'];

        if($product['pay_type']=='onetime'){
            $pay = $basePrice;
        }else if($product['pay_type']=='free'){
            $pay = 0;
        }else if($host['billing_cycle'] == 'on_demand'){
            $pay = $basePrice;
        }else{
            if($result['data']['duration']>0){
                if(($host['due_time']-$time)>0){
                    $pay = $basePrice;
                }else{
                    $pay = $basePrice;
                }
            }else{
                $pay = $basePrice;
            }
        }

        $upgradePrice = bcsub($pay,$refund,2);

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'upgrade_price' => $upgradePrice>0?$upgradePrice:bcsub(0,0,2),
                'base_price' => $product['pay_type']=='free'?0:$basePrice
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 产品升降级
     * @desc 产品升降级
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   int product_id - 商品ID require
     * @param   object config_options - 与购物车结算的一样:{"configoption":{"1"：2,"2":3,"4":[1,2,3]},"cycle":"monthly","product_id":104,son:{}} require
     */
    public function upgrade($param)
    {
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $param['host_id'] ?? 0,
            'scene_desc'=> lang_plugins('idcsmart_common_upgrade_scene_upgrade'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $OrderModel = new OrderModel();

        $result = $OrderModel->createUpgradeOrder([
            'host_id' => $param['host_id'],
            'client_id' => get_client_id(),
            'upgrade_refund' => 0, # 不支持退款
            'product' => [
                'product_id' => $param['product_id'],
                'config_options' => $param['config_options'],
            ]
        ]);

        return $result;
    }

    /**
     * 时间 2022-09-26
     * @title 产品配置升降级页面
     * @desc 产品配置升降级页面
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host -
     * @return  int host.product_id - 商品ID
     * @return  string host.name - 名称
     * @return  float host.first_payment_amount - 金额
     * @return  string host.billing_cycle_name - 周期
     * @return  object configoptions - 配置
     * @return  array configoptions - 配置
     * @return  string configoptions[].option_type - 配置类型
     * @return  string configoptions[].option_name - 名称
     * @return  string configoptions[].sub_name - 子项名称
     * @return  int configoptions[].qty - 数量(类型为数量时,显示此值)
     * @return  array son_host - 子产品
     * @return  int son_host[].id - 子产品ID
     * @return  string son_host[].name - 名称
     * @return  float son_host[].first_payment_amount - 金额
     * @return  string son_host[].billing_cycle_name - 周期
     * @return  array upgrade_configoptions - 可升降级配置项
     * @return  int upgrade_configoptions[].id - 配置项ID
     * @return  int upgrade_configoptions[].option_name - 配置项名称
     * @return  array upgrade_configoptions[].subs - 配置子项数据
     */
    public function upgradeConfigPage($param)
    {
        $hostId = $param['host_id'];

        $where = [];
        $where[] = ['h.id', '=', $hostId];
        $where[] = ['h.is_delete', '=', 0];

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.product_id,p.name,h.first_payment_amount,h.billing_cycle_name')
            ->leftJoin('product p','p.id=h.product_id')
            ->withAttr('name', function($val){
                return multi_language_replace($val);
            })
            ->where($where)
            ->find();
        if (empty($host)){
            return [
                'status' => 400,
                'msg' => lang_plugins('error_message')
            ];
        }
        $productId = $host['product_id'];

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('dpc.id,dpc.option_type,dpc.option_name,dpcs.option_name as sub_name,hc.qty,hc.configoption_sub_id,hc.cascade_item_id')
            ->leftJoin('module_idcsmart_common_product_configoption dpc','dpc.id=hc.configoption_id')
            ->leftJoin('idcsmart_module_idcsmart_common_product_configoption_sub dpcs','dpcs.id=hc.configoption_sub_id')
            ->where('hc.host_id',$hostId)
            ->where('dpc.option_type','<>','os')
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
            ->order('dpc.order','asc')
            ->select()
            ->toArray();

        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
        foreach ($configoptions as &$configoption) {
            if ($configoption['option_type'] == 'cascade' && !empty($configoption['cascade_item_id'])) {
                // 使用getFullName方法获取级联路径
                $cascadePath = $IdcsmartCommonCascadeItemModel->getFullName($configoption['cascade_item_id']);
                
                // 获取级联项的fee_type来判断是否为数量类型
                $cascadeItem = $IdcsmartCommonCascadeItemModel->find($configoption['cascade_item_id']);
                if (!empty($cascadeItem) && in_array($cascadeItem['fee_type'], ['qty', 'stage']) && !empty($configoption['qty'])) {
                    $cascadePath .= ' x ' . $configoption['qty'];
                }
                
                $configoption['sub_name'] = $cascadePath;
            }
        }
        unset($configoption);

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $upgradeConfigoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
            ->where('hidden',0)
            ->where('upgrade',1)
            ->where('option_type','<>','os')
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
            ->withAttr('description', function($value){
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
            ->order('order','asc')
            ->select()
            ->toArray();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $IdcsmartCommonCascadeLogic = new IdcsmartCommonCascadeLogic();
        $upgradeConfigoptionsFilter = [];
        foreach ($upgradeConfigoptions as $upgradeConfigoption){
            if ($upgradeConfigoption['option_type'] == 'cascade') {
                $upgradeConfigoption['tree'] = $IdcsmartCommonCascadeLogic->getCascadeTree($upgradeConfigoption['id']);
                $upgradeConfigoption['cascade_group'] = $IdcsmartCommonCascadeLogic->getCascadeGroups($upgradeConfigoption['id']);
                $upgradeConfigoption['subs'] = [];
            } else {
                $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$upgradeConfigoption['id'])
                    ->where('hidden',0)
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
                    ->order('order','asc')
                    ->select()
                    ->toArray();
                $upgradeConfigoption['subs'] = $subs;
            }
            $upgradeConfigoptionsFilter[] = $upgradeConfigoption;
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'host' => $host,
                'configoptions' => $configoptions,
                'upgrade_configoptions' => $upgradeConfigoptionsFilter
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 产品配置升降级异步获取升降级价格
     * @desc 产品配置升降级异步获取升降级价格
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param  object configoption - "configoption":{"1"：2,"2":3,"4":[1,2,3]}
     */
    public function syncUpgradeConfigPrice($param)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $res = $IdcsmartCommonLogic->upgradeConfigPrice($param);

        $formatZero = bcsub(0,0,2);

        return [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data' => [
                // 前端显示用
                'price' => $res['data']['price_difference']>0?$res['data']['price_difference']:$formatZero,
                // 代理使用
                'description' => $res['data']['description']??[],
                'preview' => $res['data']['preview']??[],

                'price_difference' => $res['data']['price_difference']??0,
                'renew_price_difference' => $res['data']['renew_price_difference']??0,
                'new_first_payment_amount' => $res['data']['new_first_payment_amount']??0,
                'base_price' => $res['data']['base_price']??0,

                'price_difference_client_level_discount' => $res['data']['price_difference_client_level_discount']??0,
                'renew_price_difference_client_level_discount' => $res['data']['renew_price_difference_client_level_discount']??0,
                'new_first_payment_amount_client_level_discount' => $res['data']['new_first_payment_amount_client_level_discount']??0,
                'base_price_client_level_discount' => $res['data']['base_price_client_level_discount']??0,
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 产品配置升降级
     * @desc 产品配置升降级
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param  object configoption - "configoption":{"1"：2,"2":3,"4":[1,2,3]}
     * @return int id - 订单ID
     */
    public function upgradeConfig($param)
    {
        $hookRes = hook('before_host_upgrade', [
            'host_id'   => $param['host_id'] ?? 0,
            'scene_desc'=> lang_plugins('idcsmart_common_upgrade_scene_upgrade'),
        ]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }
        
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $res = $IdcsmartCommonLogic->upgradeConfigPrice($param);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['host_id']??0,
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price_difference'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['new_first_payment_amount'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'configoption' => $param['configoption']??[],
                'cascade_configoption' => $param['cascade_configoption']??[],
                'buy' => $param['buy']??false,
            ]
        ];

        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2023-06-01
     * @title 获取模块列表
     * @desc 获取模块列表
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return array list -
     * @return string list[].name - 名称
     * @return string list[].value - 值
     */
    public function getModules($param){
        $ProvisionLogic  = new ProvisionLogic();
        $data = $ProvisionLogic->getModules();
        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $data
            ]
        ];
    }

    /**
     * 时间 2023-06-01
     * @title 获取模块自定义参数
     * @desc 获取模块自定义参数
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int server_id - 服务器ID require
     * @return array configoption -
     * @return string configoption[].name - 名称
     * @return string configoption[].placeholder - 填充
     * @return string configoption[].description - 描述
     * @return string configoption[].default - 默认值
     * @return string configoption[].type - 类型text,password,yesno(值 on|off),radio,dropdown,textarea,
     * @return string configoption[].options - 选项,单选和下拉才有
     * @return string configoption[].rows - 文本域属性rows
     * @return string configoption[].cols - 文本域属性cols
     * @return object module_meta -
     * @return string module_meta.APIVersion - 版本
     * @return string module_meta.HelpDoc - 帮助文档地址
     */
    public function getModuleConfig($param)
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->where('id',$param['product_id']??0)->find();
        if (empty($product)){
            return ['status'=>400,'msg'=>lang_plugins('product_not_found')];
        }

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
        $server = $IdcsmartCommonServerModel->where('id',$param['server_id']??0)->find();
        if (empty($server)){
            return ['status'=>400,'msg'=>lang_plugins('idcsmart_common_server_not_exist')];
        }

        $data = [];

        if ($server['serer_type']=='dicm'){
            $data['configoption'] = [
                [
                    'default'=>'rent',
                    'description'=>'',
                    'name'=>'产品类型',
                    'type'=>'dropdown',
                    'options'=>[
                        ['value'=>'rent', 'name'=>'租用/托管'],
                        ['value'=>'cabinet', 'name'=>'机柜/带宽/IP'],
                        ['value'=>'bms', 'name'=>'裸金属'],
                    ]
                ]
            ];
            $data['module_meta']['HelpDoc'] = 'https://www.idcsmart.com/wiki_list/338.html#2.1.5';
        }else if($server['system_type'] == 'dcimcloud'){
            $data['configoption'] = [];
            $result['module_meta']['HelpDoc'] = 'https://www.idcsmart.com/wiki_list/358.html#2.1.3';
        }else{
            $module = $server['type'];
            $ProvisionLogic = new ProvisionLogic();
            $data['configoption'] = $ProvisionLogic->getModuleConfigOptions($module);
            $data['module_meta'] = $ProvisionLogic->getModuleMetaData($module);
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
    }

    /*
     * 同步数据获取
     * */
    /*
     * 同步数据获取
     * */
    public function otherParams($productId)
    {
        $product = $this->alias('p')
            ->field('p.*,cp.onetime,s.type module_type')
            ->leftJoin('module_idcsmart_common_pricing cp','p.product_id=cp.rel_id and cp.type=\'product\'')
            ->leftJoin('module_idcsmart_common_server s','s.id=p.rel_id and p.type=\'server\'')
            ->where('p.product_id',$productId)
            ->find();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $configoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
            ->select()
            ->toArray();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        // 配置子项及其一次性金额
        $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
            ->field('pcs.*,cp.onetime')
            ->leftJoin('module_idcsmart_common_pricing cp','pcs.id=cp.rel_id and cp.type=\'configoption\'')
            ->whereIn('pcs.product_configoption_id',array_column($configoptions,'id'))
            ->group('pcs.id')
            ->select()
            ->toArray();

        // 先获取自定义周期数据，后续级联配置项需要使用
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycles = $IdcsmartCommonCustomCycleModel->alias('c')
            ->field('c.*,pdr.ratio')
            ->leftJoin('product_duration_ratio pdr','pdr.duration_id=c.id AND c.product_id=pdr.product_id')
            ->where('c.product_id',$productId)
            ->where('pdr.ratio','>',0)
            ->group('c.id')
            ->select()
            ->toArray();

        // 级联配置项相关数据
        $cascadeGroups = [];
        $cascadeItems = [];
        $cascadeConfigoptions = array_filter($configoptions, function($item) {
            return $item['option_type'] == 'cascade';
        });

        if (!empty($cascadeConfigoptions)) {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

            foreach ($cascadeConfigoptions as $cascadeConfigoption) {
                // 获取级联组
                $groups = $IdcsmartCommonCascadeGroupModel->where('configoption_id', $cascadeConfigoption['id'])
                    ->order('level', 'asc')
                    ->select()
                    ->toArray();
                $cascadeGroups = array_merge($cascadeGroups, $groups);

                // 获取级联项
                $groupIds = array_column($groups, 'id');
                if (!empty($groupIds)) {
                    $items = $IdcsmartCommonCascadeItemModel->whereIn('cascade_group_id', $groupIds)
                        ->order('cascade_group_id', 'asc')
                        ->order('order', 'asc')
                        ->select()
                        ->toArray();
                    
                    // 为每个级联项获取其关联的配置子项及价格
                    foreach ($items as &$item) {
                        $itemSubs = [];
                        // 获取该级联项的配置子项
                        $cascadeItemSubs = $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $item['id'])
                            ->order('order', 'asc')
                            ->select()
                            ->toArray();
                        
                        // 为每个配置子项获取价格
                        foreach ($cascadeItemSubs as &$cascadeItemSub) {
                            $cascadeItemSubPricings = $IdcsmartCommonCustomCyclePricingModel->whereIn('custom_cycle_id', array_column($customCycles, 'id'))
                                ->where('type', 'configoption')
                                ->where('rel_id', $cascadeItemSub['id'])
                                ->select()
                                ->toArray();
                            $cascadeItemSub['custom_cycle_pricings'] = $cascadeItemSubPricings;
                        }
                        
                        $item['configoption_subs'] = $cascadeItemSubs;
                    }
                    
                    $cascadeItems = array_merge($cascadeItems, $items);
                }
            }
        }

        // 重新实例化价格模型（如果之前没有级联配置项）
        if (empty($cascadeConfigoptions)) {
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        }
        // 商品自定义周期价格
        $productCustomCyclePricings = $IdcsmartCommonCustomCyclePricingModel->whereIn('custom_cycle_id',array_column($customCycles,'id'))
            ->where('type','product')
            ->select()
            ->toArray();
        $product['custom_cycle_pricings'] = $productCustomCyclePricings;
        // 子项自定义周期价格
        foreach ($configoptionSubs as &$configoptionSub){
            $configoptionSubCustomCyclePricings = $IdcsmartCommonCustomCyclePricingModel->whereIn('custom_cycle_id',array_column($customCycles,'id'))
                ->where('type','configoption')
                ->where('rel_id',$configoptionSub['id'])
                ->select()
                ->toArray();
            $configoptionSub['custom_cycle_pricings'] = $configoptionSubCustomCyclePricings;
        }

        foreach ($configoptions as &$configoption){
            $subs = [];
            foreach ($configoptionSubs as $configoptionSub1){
                if ($configoption['id']==$configoptionSub1['product_configoption_id']){
                    $subs[] = $configoptionSub1;
                }
            }
            $configoption['subs'] = $subs;

            // 如果是级联配置项，添加级联数据
            if ($configoption['option_type'] == 'cascade') {
                $configoption['cascade_groups'] = array_filter($cascadeGroups, function($group) use ($configoption) {
                    return $group['configoption_id'] == $configoption['id'];
                });
                $configoption['cascade_items'] = array_filter($cascadeItems, function($item) use ($cascadeGroups, $configoption) {
                    $groupIds = array_column(array_filter($cascadeGroups, function($group) use ($configoption) {
                        return $group['configoption_id'] == $configoption['id'];
                    }), 'id');
                    return in_array($item['cascade_group_id'], $groupIds);
                });
            }
        }

        return [
            'product' => $product,
            'configoptions' => $configoptions,
            'custom_cycles' => $customCycles,
            'cascade_groups' => $cascadeGroups,
            'cascade_items' => $cascadeItems
        ];
    }


    /*
     * 同步
     * */
    public function syncOtherParams($productId, $param, $otherParams, UpstreamProductModel $upstreamProductModel)
    {
        $upstreamProduct = $otherParams['product']??[];
        
        $configoptions = $otherParams['configoptions']??[];

        $customCycles = $otherParams['custom_cycles']??[];

        // 汇率
        $rate = $param['supplier']['rate']??1;

        // 是否 // 百分比
        $profitTypePercent = $param['profit_type']==0;

        //if ($profitTypePercent){
            $rate = bcdiv($rate*$param['profit_percent'], 100, 2);
        //}

        // 原来为百分比，修改为自定义时；或者强制同步：需要拉取价格
        if (($upstreamProductModel['profit_type']==0 && $param['profit_type']==1) || (isset($param['force']) && $param['force'])){
            $profitTypePercent = true;
        }

        // 1、创建子模块接口,可能没有子模块
        if(!empty($upstreamProduct['module_type'])){
            $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
            $server = $IdcsmartCommonServerModel->where('type',$upstreamProduct['module_type'])
                ->where('upstream_use',1)
                ->find();
            if (empty($server)){
                $server = $IdcsmartCommonServerModel->create([
                    'name' => $upstreamProduct['module_type'].'代理接口(勿删)',
                    'type' => $upstreamProduct['module_type'],
                    'upstream_use' => 1,
                ]);
            }
        }

        $product = $this->where('product_id',$productId)->find();

        // 2、商品基础信息
        if (!empty($product)){
            $product->save([
                'update_time' => time(),
                'type' => 'server',
                'rel_id' => $server['id']??0,
                'config_option1' => $upstreamProduct['config_option1'],
                'config_option2' => $upstreamProduct['config_option2'],
                'config_option3' => $upstreamProduct['config_option3'],
                'config_option4' => $upstreamProduct['config_option4'],
                'config_option5' => $upstreamProduct['config_option5'],
                'config_option6' => $upstreamProduct['config_option6'],
                'config_option7' => $upstreamProduct['config_option7'],
                'config_option8' => $upstreamProduct['config_option8'],
                'config_option9' => $upstreamProduct['config_option9'],
                'config_option10' => $upstreamProduct['config_option10'],
                'config_option11' => $upstreamProduct['config_option11'],
                'config_option12' => $upstreamProduct['config_option12'],
                'config_option13' => $upstreamProduct['config_option13'],
                'config_option14' => $upstreamProduct['config_option14'],
                'config_option15' => $upstreamProduct['config_option15'],
                'config_option16' => $upstreamProduct['config_option16'],
                'config_option17' => $upstreamProduct['config_option17'],
                'config_option18' => $upstreamProduct['config_option18'],
                'config_option19' => $upstreamProduct['config_option19'],
                'config_option20' => $upstreamProduct['config_option20'],
                'config_option21' => $upstreamProduct['config_option21'],
                'config_option22' => $upstreamProduct['config_option22'],
                'config_option23' => $upstreamProduct['config_option23'],
                'config_option24' => $upstreamProduct['config_option24'],
            ]);
        }else{
            $this->create([
                'product_id' => $productId,
                'order_page_description' => $upstreamProduct['order_page_description'],
                'allow_qty' => $upstreamProduct['allow_qty'],
                'auto_support' => $upstreamProduct['auto_support'],
                'create_time' => time(),
                'type' => 'server',
                'rel_id' => $server['id']??0,
                'config_option1' => $upstreamProduct['config_option1'],
                'config_option2' => $upstreamProduct['config_option2'],
                'config_option3' => $upstreamProduct['config_option3'],
                'config_option4' => $upstreamProduct['config_option4'],
                'config_option5' => $upstreamProduct['config_option5'],
                'config_option6' => $upstreamProduct['config_option6'],
                'config_option7' => $upstreamProduct['config_option7'],
                'config_option8' => $upstreamProduct['config_option8'],
                'config_option9' => $upstreamProduct['config_option9'],
                'config_option10' => $upstreamProduct['config_option10'],
                'config_option11' => $upstreamProduct['config_option11'],
                'config_option12' => $upstreamProduct['config_option12'],
                'config_option13' => $upstreamProduct['config_option13'],
                'config_option14' => $upstreamProduct['config_option14'],
                'config_option15' => $upstreamProduct['config_option15'],
                'config_option16' => $upstreamProduct['config_option16'],
                'config_option17' => $upstreamProduct['config_option17'],
                'config_option18' => $upstreamProduct['config_option18'],
                'config_option19' => $upstreamProduct['config_option19'],
                'config_option20' => $upstreamProduct['config_option20'],
                'config_option21' => $upstreamProduct['config_option21'],
                'config_option22' => $upstreamProduct['config_option22'],
                'config_option23' => $upstreamProduct['config_option23'],
                'config_option24' => $upstreamProduct['config_option24'],
            ]);
        }

        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();

        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();
        if (!empty($pricing)){
            if ($profitTypePercent){
                $pricing->save([
                    'onetime' => bcmul($upstreamProduct['onetime'],$rate,2),
                ]);
            }
        }else{
            $IdcsmartCommonPricingModel->insert([
                'type' => 'product',
                'rel_id' => $productId,
                'onetime' => bcmul($upstreamProduct['onetime'],$rate,2),
            ]);
        }


        // 3、自定义周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        // 字段映射
        $customCycleIds = [];

        $existCustomCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
            ->select()
            ->toArray();
        if (!empty($existCustomCycles)){
            $existCCUpstreamIds = array_column($existCustomCycles,'upstream_id');
            $existCCUpstreamIdsMap = array_column($existCustomCycles,'id','upstream_id');
            $cCUpstreamIds = array_column($customCycles,'id');
            // 额外的删除
            $extraCCUpstreamIds = array_diff($existCCUpstreamIds,$cCUpstreamIds);
            if (!empty($extraCCUpstreamIds)){
                $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                    ->whereIn('upstream_id',$extraCCUpstreamIds)
                    ->delete();
            }
            // 少的添加
            $missCCUpstreamIds = array_diff($cCUpstreamIds,$existCCUpstreamIds);
            if (!empty($missCCUpstreamIds)){
                foreach ($customCycles as $customCycle){
                    if (in_array($customCycle['id'],$missCCUpstreamIds)){
                        $customCycleId = $IdcsmartCommonCustomCycleModel->insertGetId([
                            'product_id' => $productId,
                            'name' =>  $customCycle['name'],
                            'cycle_time' =>  $customCycle['cycle_time'],
                            'cycle_unit' =>  $customCycle['cycle_unit'],
                            'cycle_type' =>  $customCycle['cycle_type'] ?? 0,
                            'status'     =>  $customCycle['status'] ?? 1,
                            'create_time' =>  time(),
                            'upstream_id' => $customCycle['id'], // 上游周期ID
                        ]);
                        $customCycleIds[$customCycle['id']] = $customCycleId;
                    }
                }
            }
            // 已有的进行更新
            $intersectCCUpstreamIds = array_intersect($existCCUpstreamIds,$cCUpstreamIds);
            if (!empty($intersectCCUpstreamIds)){
                foreach ($customCycles as $customCycle1){
                    if (in_array($customCycle1['id'],$intersectCCUpstreamIds)){
                        $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                            ->where('upstream_id',$customCycle1['id'])
                            ->update([
                                'name' =>  $customCycle1['name'],
                                'cycle_time' =>  $customCycle1['cycle_time'],
                                'cycle_unit' =>  $customCycle1['cycle_unit'],
                                'cycle_type' =>  $customCycle1['cycle_type'] ?? 0,
                                'status'     =>  $customCycle1['status'] ?? 1,
                                'update_time' =>  time(),
                                'upstream_id' => $customCycle1['id'], // 上游周期ID
                            ]);
                        $customCycleIds[$customCycle1['id']] = $existCCUpstreamIdsMap[$customCycle1['id']];
                    }
                }
            }
        }else{
            foreach ($customCycles as $customCycle){
                $customCycleId = $IdcsmartCommonCustomCycleModel->insertGetId([
                    'product_id' => $productId,
                    'name' =>  $customCycle['name'],
                    'cycle_time' =>  $customCycle['cycle_time'],
                    'cycle_unit' =>  $customCycle['cycle_unit'],
                    'cycle_type' =>  $customCycle['cycle_type'] ?? 0,
                    'status'     =>  $customCycle['status'] ?? 1,
                    'create_time' =>  time(),
                    'upstream_id' => $customCycle['id'], // 上游周期ID
                ]);
                // 上游=>本地
                $customCycleIds[$customCycle['id']] = $customCycleId;
            }
        }

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

        $existProductCCPricings = $IdcsmartCommonCustomCyclePricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->select()
            ->toArray();
        if (!empty($existProductCCPricings)){
            $localCycleIds = array_column($existProductCCPricings,'custom_cycle_id');
            foreach ($upstreamProduct['custom_cycle_pricings'] as $item){
                // 本地有数据，仅对数据进行更新
                if (in_array($customCycleIds[$item['custom_cycle_id']],$localCycleIds)){
                    // 仅百分比方案时进行更新
                    if ($profitTypePercent){
                        $IdcsmartCommonCustomCyclePricingModel->where('type','product')
                            ->where('rel_id',$productId)
                            ->where('custom_cycle_id',$customCycleIds[$item['custom_cycle_id']])
                            ->update([
                                'amount' => bcmul($item['amount'],$rate,2)
                            ]);
                    }
                }else{
                    // 本地没有此周期价格数据，插入
                    $IdcsmartCommonCustomCyclePricingModel->insert([
                        'custom_cycle_id' => $customCycleIds[$item['custom_cycle_id']],
                        'rel_id' => $productId,
                        'type' => 'product',
                        'amount' => bcmul($item['amount'],$rate,2)
                    ]);
                }
            }
            // 多余的数据删除
            foreach ($localCycleIds as $localCycleId){
                if (!in_array($localCycleId,array_values($customCycleIds))){
                    $IdcsmartCommonCustomCyclePricingModel->where('type','product')
                        ->where('rel_id',$productId)
                        ->where('custom_cycle_id',$localCycleId)
                        ->delete();
                }
            }

        }else{
            // 商品自定义周期价格
            $customCycleProductPricingAll = [];
            foreach ($upstreamProduct['custom_cycle_pricings'] as $item){
                $customCycleProductPricingAll[] = [
                    'custom_cycle_id' => $customCycleIds[$item['custom_cycle_id']],
                    'rel_id' => $productId,
                    'type' => 'product',
                    'amount' => bcmul($item['amount'],$rate,2)
                ];
            }
            if (!empty($customCycleProductPricingAll)){
                $IdcsmartCommonCustomCyclePricingModel->insertAll($customCycleProductPricingAll);
            }
        }

        // 配置项
        $customCyclePricingAll = [];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        // 级联配置项相关模型
        $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
        $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();

        // 获取上游级联数据
        $upstreamCascadeGroups = $otherParams['cascade_groups'] ?? [];
        $upstreamCascadeItems = $otherParams['cascade_items'] ?? [];

        foreach ($configoptions as $configoption){
            $existConfigoption = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                ->where('upstream_id',$configoption['id'])
                ->find();
            // 本地已存在此配置
            if (!empty($existConfigoption)){
                // 更新配置
                $existConfigoption->save([
                    'option_name' => $configoption['option_name'],
                    'option_type' => $configoption['option_type'],
                    'option_param' => $configoption['option_param'],
                    'qty_min' => $configoption['qty_min'],
                    'qty_max' => $configoption['qty_max'],
                    'order' => $configoption['order'],
                    'hidden' => $configoption['hidden'],
                    'unit' => $configoption['unit'],
                    'allow_repeat' => $configoption['allow_repeat'],
                    'max_repeat' => $configoption['max_repeat'],
                    'fee_type' => $configoption['fee_type'],
                    'description' => $configoption['description'],
                    'configoption_id' => 0,
                    'son_product_id' => 0,
                    'free' => 0,
                    'qty_change' => $configoption['qty_change'],
                    'upgrade' => $configoption['upgrade'],
                ]);

                $subs = $configoption['subs'];

                // 如果是级联配置项，同步级联数据
                if ($configoption['option_type'] == 'cascade') {
                    $this->syncCascadeData($existConfigoption['id'], $configoption, $upstreamCascadeGroups, $upstreamCascadeItems, $customCycleIds, $rate, $profitTypePercent);
                }

                foreach ($subs as $sub){
                    $existConfigoptionSub = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$existConfigoption['id'])
                        ->where('upstream_id',$sub['id'])
                        ->find();
                    // 已存在子项
                    if (!empty($existConfigoptionSub)){
                        $existConfigoptionSub->save([
                            'option_name' => $sub['option_name'],
                            'option_param' => $sub['option_param'],
                            'qty_min' => $sub['qty_min'],
                            'qty_max' => $sub['qty_max'],
                            'order' => $sub['order'],
                            'hidden' => $sub['hidden'],
                            'country' => $sub['country'],
                        ]);
                        // 处理子项价格
                        $existSubCCPricings = $IdcsmartCommonCustomCyclePricingModel->where('type','configoption')
                            ->where('rel_id',$existConfigoptionSub['id'])
                            ->select()
                            ->toArray();
                        if (!empty($existSubCCPricings)){
                            $localSubCycleIds = array_column($existSubCCPricings,'custom_cycle_id');
                            foreach ($sub['custom_cycle_pricings'] as $item){
                                // 本地有数据，仅对数据进行更新
                                if (in_array($customCycleIds[$item['custom_cycle_id']],$localSubCycleIds)){
                                    // 仅百分比方案时进行更新
                                    if ($profitTypePercent){
                                        $IdcsmartCommonCustomCyclePricingModel->where('type','configoption')
                                            ->where('rel_id',$existConfigoptionSub['id'])
                                            ->where('custom_cycle_id',$customCycleIds[$item['custom_cycle_id']])
                                            ->update([
                                                'amount' => bcmul($item['amount'],$rate,2)
                                            ]);
                                    }
                                }else{
                                    // 本地没有此周期价格数据，插入
                                    $IdcsmartCommonCustomCyclePricingModel->insert([
                                        'custom_cycle_id' => $customCycleIds[$item['custom_cycle_id']],
                                        'rel_id' => $existConfigoptionSub['id'],
                                        'type' => 'configoption',
                                        'amount' => bcmul($item['amount'],$rate,2)
                                    ]);
                                }
                            }
                            // 多余的数据删除
                            $diffSubCycleIds = array_diff($localSubCycleIds,array_values($customCycleIds));
                            if (!empty($diffSubCycleIds)){
                                $IdcsmartCommonCustomCyclePricingModel->where('type','configoption')
                                    ->where('rel_id',$existConfigoptionSub['id'])
                                    ->whereIn('custom_cycle_id',$diffSubCycleIds)
                                    ->delete();
                            }

                        }else{
                            $customCyclePricings = $sub['custom_cycle_pricings'];

                            foreach ($customCyclePricings as $customCyclePricing){
                                $customCyclePricingAll[] = [
                                    'custom_cycle_id' => $customCycleIds[$customCyclePricing['custom_cycle_id']],
                                    'rel_id' => $existConfigoptionSub['id'],
                                    'type' => 'configoption',
                                    'amount' => bcmul($customCyclePricing['amount'],$rate,2)
                                ];
                            }
                        }

                    } else{
                        $subId = $IdcsmartCommonProductConfigoptionSubModel->insertGetId([
                            'product_configoption_id' => $existConfigoption['id'],
                            'option_name' => $sub['option_name'],
                            'option_param' => $sub['option_param'],
                            'qty_min' => $sub['qty_min'],
                            'qty_max' => $sub['qty_max'],
                            'order' => $sub['order'],
                            'hidden' => $sub['hidden'],
                            'country' => $sub['country'],
                            'upstream_id' => $sub['id'],
                        ]);

                        $customCyclePricings = $sub['custom_cycle_pricings'];

                        foreach ($customCyclePricings as $customCyclePricing){
                            $customCyclePricingAll[] = [
                                'custom_cycle_id' => $customCycleIds[$customCyclePricing['custom_cycle_id']],
                                'rel_id' => $subId,
                                'type' => 'configoption',
                                'amount' => bcmul($customCyclePricing['amount'],$rate,2)
                            ];
                        }
                    }
                }

                // 删除多余的配置子项以及对应周期价格
                $existSubs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$existConfigoption['id'])
                    ->select()
                    ->toArray();
                if (!empty($existSubs)){
                    $subDiffIds = array_diff(array_column($existSubs,'upstream_id'),array_column($subs,'id'));
                    $deleteSubIds = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$existConfigoption['id'])
                        ->whereIn('upstream_id',$subDiffIds)
                        ->column('id');
                    // 删除子项
                    $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$existConfigoption['id'])
                        ->whereIn('upstream_id',$subDiffIds)
                        ->delete();
                    // 删除子项价格
                    $IdcsmartCommonCustomCyclePricingModel->where('type','configoption')
                        ->whereIn('rel_id',$deleteSubIds)
                        ->delete();
                }
            }
            else{
                $configoptionId = $IdcsmartCommonProductConfigoptionModel->insertGetId([
                    'product_id' => $productId,
                    'option_name' => $configoption['option_name'],
                    'option_type' => $configoption['option_type'],
                    'option_param' => $configoption['option_param'],
                    'qty_min' => $configoption['qty_min'],
                    'qty_max' => $configoption['qty_max'],
                    'order' => $configoption['order'],
                    'hidden' => $configoption['hidden'],
                    'unit' => $configoption['unit'],
                    'allow_repeat' => $configoption['allow_repeat'],
                    'max_repeat' => $configoption['max_repeat'],
                    'fee_type' => $configoption['fee_type'],
                    'description' => $configoption['description'],
                    'configoption_id' => 0,
                    'son_product_id' => 0,
                    'free' => 0,
                    'upstream_id' => $configoption['id'],
                    'qty_change' => $configoption['qty_change'],
                    'upgrade' => $configoption['upgrade'],
                ]);

                $subs = $configoption['subs'];

                // 如果是级联配置项，同步级联数据
                if ($configoption['option_type'] == 'cascade') {
                    $this->syncCascadeData($configoptionId, $configoption, $upstreamCascadeGroups, $upstreamCascadeItems, $customCycleIds, $rate, $profitTypePercent);
                }

                foreach ($subs as $sub){
                    $subId = $IdcsmartCommonProductConfigoptionSubModel->insertGetId([
                        'product_configoption_id' => $configoptionId,
                        'option_name' => $sub['option_name'],
                        'option_param' => $sub['option_param'],
                        'qty_min' => $sub['qty_min'],
                        'qty_max' => $sub['qty_max'],
                        'order' => $sub['order'],
                        'hidden' => $sub['hidden'],
                        'country' => $sub['country'],
                        'upstream_id' => $sub['id'],
                    ]);

                    $customCyclePricings = $sub['custom_cycle_pricings'];

                    foreach ($customCyclePricings as $customCyclePricing){
                        $customCyclePricingAll[] = [
                            'custom_cycle_id' => $customCycleIds[$customCyclePricing['custom_cycle_id']],
                            'rel_id' => $subId,
                            'type' => 'configoption',
                            'amount' => bcmul($customCyclePricing['amount'],$rate,2)
                        ];
                    }
                }
            }
        }

        // 多余的配置项 删除
        $existConfigoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
            ->select()
            ->toArray();
        if (!empty($existConfigoptions)){
            $extraConfigoptionUpstreamIds = array_diff(array_column($existConfigoptions,'upstream_id'),array_column($configoptions,'id'));
            if (!empty($extraConfigoptionUpstreamIds)){
                $extraConfigoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                    ->whereIn('upstream_id',$extraConfigoptionUpstreamIds)
                    ->select()
                    ->toArray();
                foreach ($extraConfigoptions as $extraConfigoption){
                    $extraConfigoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$extraConfigoption['id'])
                        ->select()
                        ->toArray();
                    // 删除价格
                    $IdcsmartCommonCustomCyclePricingModel->whereIn('rel_id',array_column($extraConfigoptionSubs,'id'))
                        ->where('type','configoption')
                        ->whereIn('custom_cycle_id',array_column($existCustomCycles,'id'))
                        ->delete();
                    $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$extraConfigoption['id'])->delete();
                }
                $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                    ->whereIn('upstream_id',$extraConfigoptionUpstreamIds)
                    ->delete();
            }
        }

        if (!empty($customCyclePricingAll)){
            $IdcsmartCommonCustomCyclePricingModel->insertAll($customCyclePricingAll);
        }

        // 同步周期比例
        $ProductDurationRatioModel = new ProductDurationRatioModel();

        $existRatio = $ProductDurationRatioModel->where('product_id',$productId)->find();

        if (empty($existRatio) || $profitTypePercent){
            // 删除原周期比例
            $ProductDurationRatioModel->where('product_id',$productId)->delete();

            $durationRatios = [];
            foreach ($customCycles as $customCycle1){
                $durationRatios[] = [
                    'duration_id' => $customCycleIds[$customCycle1['id']],
                    'product_id' => $productId,
                    'ratio' => $customCycle1['ratio']??0, // 上游周期比例可能为空
                ];
            }
            $ProductDurationRatioModel->insertAll($durationRatios);
        }

        return ['status'=>200];
    }

    /**
     * 时间 2024-12-20
     * @title 同步级联配置数据
     * @desc 同步上游级联配置项的级联组和级联项数据
     * @author theworld
     * @version v1
     * @param int configoption_id - 本地配置项ID
     * @param array configoption - 上游配置项数据
     * @param array upstream_cascade_groups - 上游级联组数据
     * @param array upstream_cascade_items - 上游级联项数据
     * @param array custom_cycle_ids - 自定义周期ID映射
     * @param float rate - 汇率
     * @param bool profit_type_percent - 是否百分比利润
     * @return bool
     */
    private function syncCascadeData($configoptionId, $configoption, $upstreamCascadeGroups, $upstreamCascadeItems, $customCycleIds, $rate, $profitTypePercent)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

            // 获取该配置项的上游级联组
            $configoptionCascadeGroups = array_filter($upstreamCascadeGroups, function($group) use ($configoption) {
                return $group['configoption_id'] == $configoption['id'];
            });

            // 获取该配置项的上游级联项
            $upstreamGroupIds = array_column($configoptionCascadeGroups, 'id');
            $configoptionCascadeItems = array_filter($upstreamCascadeItems, function($item) use ($upstreamGroupIds) {
                return in_array($item['cascade_group_id'], $upstreamGroupIds);
            });

            // 同步级联组
            $groupIdMapping = []; // 上游组ID => 本地组ID
            foreach ($configoptionCascadeGroups as $upstreamGroup) {
                $existGroup = $IdcsmartCommonCascadeGroupModel->where('configoption_id', $configoptionId)
                    ->where('upstream_id', $upstreamGroup['id'])
                    ->find();

                if (!empty($existGroup)) {
                    // 更新现有级联组
                    $existGroup->save([
                        'group_name' => $upstreamGroup['group_name'],
                        'level' => $upstreamGroup['level'],
                        'update_time' => time(),
                    ]);
                    $groupIdMapping[$upstreamGroup['id']] = $existGroup['id'];
                } else {
                    // 创建新级联组
                    $localGroupId = $IdcsmartCommonCascadeGroupModel->insertGetId([
                        'configoption_id' => $configoptionId,
                        'group_name' => $upstreamGroup['group_name'],
                        'level' => $upstreamGroup['level'],
                        'upstream_id' => $upstreamGroup['id'],
                        'create_time' => time(),
                        'update_time' => time(),
                    ]);
                    $groupIdMapping[$upstreamGroup['id']] = $localGroupId;
                }
            }

            // 同步级联项
            $itemIdMapping = []; // 上游项ID => 本地项ID
            foreach ($configoptionCascadeItems as $upstreamItem) {
                $localGroupId = $groupIdMapping[$upstreamItem['cascade_group_id']] ?? 0;
                if ($localGroupId == 0) continue;

                $existItem = $IdcsmartCommonCascadeItemModel->where('cascade_group_id', $localGroupId)
                    ->where('upstream_id', $upstreamItem['id'])
                    ->find();

                if (!empty($existItem)) {
                    // 更新现有级联项
                    $existItem->save([
                        'item_name' => $upstreamItem['item_name'],
                        'fee_type' => $upstreamItem['fee_type'] ?? '',
                        'is_leaf' => $upstreamItem['is_leaf'],
                        'order' => $upstreamItem['order'],
                        'hidden' => $upstreamItem['hidden'],
                        'update_time' => time(),
                    ]);
                    $itemIdMapping[$upstreamItem['id']] = $existItem['id'];
                } else {
                    // 创建新级联项
                    $localItemId = $IdcsmartCommonCascadeItemModel->insertGetId([
                        'cascade_group_id' => $localGroupId,
                        'parent_item_id' => 0, // 暂时设为0，后续处理父级关系
                        'item_name' => $upstreamItem['item_name'],
                        'fee_type' => $upstreamItem['fee_type'] ?? '',
                        'is_leaf' => $upstreamItem['is_leaf'],
                        'order' => $upstreamItem['order'],
                        'hidden' => $upstreamItem['hidden'],
                        'upstream_id' => $upstreamItem['id'],
                        'create_time' => time(),
                        'update_time' => time(),
                    ]);
                    $itemIdMapping[$upstreamItem['id']] = $localItemId;
                }
            }

            // 处理级联项的父级关系
            foreach ($configoptionCascadeItems as $upstreamItem) {
                if ($upstreamItem['parent_item_id'] > 0) {
                    $localItemId = $itemIdMapping[$upstreamItem['id']] ?? 0;
                    $localParentItemId = $itemIdMapping[$upstreamItem['parent_item_id']] ?? 0;
                    
                    if ($localItemId > 0 && $localParentItemId > 0) {
                        $IdcsmartCommonCascadeItemModel->where('id', $localItemId)
                            ->update(['parent_item_id' => $localParentItemId]);
                    }
                }
            }

            // 同步级联项的配置子项及价格
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            
            foreach ($configoptionCascadeItems as $upstreamItem) {
                $localItemId = $itemIdMapping[$upstreamItem['id']] ?? 0;
                if ($localItemId == 0) continue;

                // 获取上游级联项的配置子项
                $upstreamItemSubs = $upstreamItem['configoption_subs'] ?? [];
                
                foreach ($upstreamItemSubs as $upstreamSub) {
                    // 查找本地是否已存在该配置子项
                    $existSub = $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $localItemId)
                        ->where('upstream_id', $upstreamSub['id'])
                        ->find();
                    
                    if (!empty($existSub)) {
                        // 更新现有配置子项
                        $existSub->save([
                            'option_name' => $upstreamSub['option_name'],
                            'option_param' => $upstreamSub['option_param'],
                            'qty_min' => $upstreamSub['qty_min'],
                            'qty_max' => $upstreamSub['qty_max'],
                            'order' => $upstreamSub['order'],
                            'hidden' => $upstreamSub['hidden'],
                            'country' => $upstreamSub['country'] ?? '',
                        ]);
                        $localSubId = $existSub['id'];
                    } else {
                        // 创建新配置子项
                        $localSubId = $IdcsmartCommonProductConfigoptionSubModel->insertGetId([
                            'product_configoption_id' => $configoptionId,
                            'cascade_item_id' => $localItemId,
                            'option_name' => $upstreamSub['option_name'],
                            'option_param' => $upstreamSub['option_param'],
                            'qty_min' => $upstreamSub['qty_min'],
                            'qty_max' => $upstreamSub['qty_max'],
                            'order' => $upstreamSub['order'],
                            'hidden' => $upstreamSub['hidden'],
                            'country' => $upstreamSub['country'] ?? '',
                            'upstream_id' => $upstreamSub['id'],
                        ]);
                    }
                    
                    // 同步配置子项的价格
                    if (!empty($customCycleIds) && $profitTypePercent) {
                        $upstreamSubPricings = $upstreamSub['custom_cycle_pricings'] ?? [];
                        
                        foreach ($upstreamSubPricings as $upstreamPricing) {
                            $localCycleId = $customCycleIds[$upstreamPricing['custom_cycle_id']] ?? 0;
                            if ($localCycleId == 0) continue;

                            $existPricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id', $localCycleId)
                                ->where('rel_id', $localSubId)
                                ->where('type', 'configoption')
                                ->find();

                            $amount = bcmul($upstreamPricing['amount'], $rate, 2);

                            if (!empty($existPricing)) {
                                $existPricing->save(['amount' => $amount]);
                            } else {
                                $IdcsmartCommonCustomCyclePricingModel->insert([
                                    'custom_cycle_id' => $localCycleId,
                                    'rel_id' => $localSubId,
                                    'type' => 'configoption',
                                    'amount' => $amount
                                ]);
                            }
                        }
                        
                        // 删除多余的价格数据
                        $existSubPricings = $IdcsmartCommonCustomCyclePricingModel->where('type', 'configoption')
                            ->where('rel_id', $localSubId)
                            ->select()
                            ->toArray();
                        
                        if (!empty($existSubPricings)) {
                            $localSubCycleIds = array_column($existSubPricings, 'custom_cycle_id');
                            $upstreamSubCycleIds = [];
                            foreach ($upstreamSubPricings as $p) {
                                if (isset($customCycleIds[$p['custom_cycle_id']])) {
                                    $upstreamSubCycleIds[] = $customCycleIds[$p['custom_cycle_id']];
                                }
                            }
                            $diffSubCycleIds = array_diff($localSubCycleIds, $upstreamSubCycleIds);
                            if (!empty($diffSubCycleIds)) {
                                $IdcsmartCommonCustomCyclePricingModel->where('type', 'configoption')
                                    ->where('rel_id', $localSubId)
                                    ->whereIn('custom_cycle_id', $diffSubCycleIds)
                                    ->delete();
                            }
                        }
                    }
                }
                
                // 删除多余的配置子项
                $existItemSubs = $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $localItemId)->select();
                $upstreamSubIds = array_column($upstreamItemSubs, 'id');
                
                foreach ($existItemSubs as $existItemSub) {
                    if (!in_array($existItemSub['upstream_id'], $upstreamSubIds)) {
                        // 删除配置子项的价格
                        $IdcsmartCommonCustomCyclePricingModel->where('type', 'configoption')
                            ->where('rel_id', $existItemSub['id'])
                            ->delete();
                        
                        // 删除配置子项
                        $existItemSub->delete();
                    }
                }
            }

            // 删除多余的级联组和级联项
            $this->cleanupExtraCascadeData($configoptionId, $configoptionCascadeGroups, $configoptionCascadeItems);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 时间 2024-12-20
     * @title 清理多余的级联数据
     * @desc 删除本地存在但上游不存在的级联组和级联项
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID
     * @param array upstream_groups - 上游级联组数据
     * @param array upstream_items - 上游级联项数据
     * @return bool
     */
    private function cleanupExtraCascadeData($configoptionId, $upstreamGroups, $upstreamItems)
    {
        try {
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

            // 获取本地级联组
            $localGroups = $IdcsmartCommonCascadeGroupModel->where('configoption_id', $configoptionId)->select();
            $upstreamGroupIds = array_column($upstreamGroups, 'id');

            foreach ($localGroups as $localGroup) {
                if (!in_array($localGroup['upstream_id'], $upstreamGroupIds)) {
                    // 删除多余的级联组及其级联项
                    $localItems = $IdcsmartCommonCascadeItemModel->where('cascade_group_id', $localGroup['id'])->select();
                    
                    foreach ($localItems as $localItem) {
                        // 删除级联项的价格配置
                        $IdcsmartCommonCustomCyclePricingModel->where('type', 'cascade_item')
                            ->where('rel_id', $localItem['id'])
                            ->delete();
                        
                        // 删除级联项
                        $localItem->delete();
                    }
                    
                    // 删除级联组
                    $localGroup->delete();
                }
            }

            // 获取本地级联项
            $localGroupIds = $IdcsmartCommonCascadeGroupModel->where('configoption_id', $configoptionId)->column('id');
            if (!empty($localGroupIds)) {
                $localItems = $IdcsmartCommonCascadeItemModel->whereIn('cascade_group_id', $localGroupIds)->select();
                $upstreamItemIds = array_column($upstreamItems, 'id');

                foreach ($localItems as $localItem) {
                    if (!in_array($localItem['upstream_id'], $upstreamItemIds)) {
                        // 删除级联项的配置子项及价格
                        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
                        $itemSubs = $IdcsmartCommonProductConfigoptionSubModel->where('cascade_item_id', $localItem['id'])->select();
                        
                        foreach ($itemSubs as $itemSub) {
                            // 删除配置子项的价格
                            $IdcsmartCommonCustomCyclePricingModel->where('type', 'configoption')
                                ->where('rel_id', $itemSub['id'])
                                ->delete();
                            
                            // 删除配置子项
                            $itemSub->delete();
                        }
                        
                        // 删除级联项
                        $localItem->delete();
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exchangeParams($productId, $param, $sence)
    {
        $exchangeParams = [];
        switch ($sence){
            case 'create_account':
                $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
                $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
                $IdcsmartCommonLogic = new IdcsmartCommonLogic();
                $exchange = [];
                foreach ($param['configoption'] as $key=>$value){
                    $configoption = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                        ->where('id',$key)
                        ->find();

                    if (empty($configoption)){
                        continue;
                    }
                    if($configoption['option_type']=='cascade'){
                        continue;
                    }

                    if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
                        $exchange[$configoption['upstream_id']] = $value;
                    } elseif ($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
                        $multi = [];
                        foreach ($value as $item){
                            $sub = $IdcsmartCommonProductConfigoptionSubModel->where('id',$item)
                                ->where('product_configoption_id',$key)
                                ->find();
                            $multi[] = $sub['upstream_id']??0;
                        }
                        $exchange[$configoption['upstream_id']] = $multi;
                    } else {
                        $sub = $IdcsmartCommonProductConfigoptionSubModel->where('id',$value)
                            ->where('product_configoption_id',$key)
                            ->find();
                        $exchange[$configoption['upstream_id']] = $sub['upstream_id']??0;
                    }
                }
                $exchangeParams['configoption'] = $exchange;

                // 处理级联配置项参数
                if (isset($param['cascade_configoption']) && !empty($param['cascade_configoption'])) {
                    $cascadeExchange = [];
                    foreach ($param['cascade_configoption'] as $configoptionId => $cascadeData) {
                        $configoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $productId)
                            ->where('id', $configoptionId)
                            ->find();

                        if (!empty($configoption) && $configoption['option_type'] == 'cascade') {
                            $cascadeValue = $this->exchangeCascadeParams($configoptionId, $cascadeData);
                            $cascadeExchange[$configoption['upstream_id']] = $cascadeValue;
                        }
                    }
                    $exchangeParams['cascade_configoption'] = $cascadeExchange;
                }

                $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

                $cycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                    ->where('id',$param['cycle'])
                    ->find();

                $exchangeParams['cycle'] = $cycle['upstream_id']??0;

                break;
            case "upgrade_config":
                $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
                $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
                $IdcsmartCommonLogic = new IdcsmartCommonLogic();
                $exchange = [];
                foreach ($param['configoption'] as $key=>$value){
                    $configoption = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
                        ->where('id',$key)
                        ->find();

                    if (empty($configoption)){
                        continue;
                    }
                    if($configoption['option_type']=='cascade'){
                        continue;
                    }

                    if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
                        $exchange[$configoption['upstream_id']] = $value;
                    } elseif ($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
                        $multi = [];
                        foreach ($value as $item){
                            $sub = $IdcsmartCommonProductConfigoptionSubModel->where('id',$item)
                                ->where('product_configoption_id',$key)
                                ->find();
                            $multi[] = $sub['upstream_id']??0;
                        }
                        $exchange[$configoption['upstream_id']] = $multi;
                    } else {
                        $sub = $IdcsmartCommonProductConfigoptionSubModel->where('id',$value)
                            ->where('product_configoption_id',$key)
                            ->find();
                        $exchange[$configoption['upstream_id']] = $sub['upstream_id']??0;
                    }
                }
                $exchangeParams['configoption'] = $exchange;

                // 处理级联配置项参数
                if (isset($param['cascade_configoption']) && !empty($param['cascade_configoption'])) {
                    $cascadeExchange = [];
                    foreach ($param['cascade_configoption'] as $configoptionId => $cascadeData) {
                        $configoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $productId)
                            ->where('id', $configoptionId)
                            ->find();

                        if (!empty($configoption) && $configoption['option_type'] == 'cascade') {
                            $cascadeValue = $this->exchangeCascadeParams($configoptionId, $cascadeData);
                            $cascadeExchange[$configoption['upstream_id']] = $cascadeValue;
                        }
                    }
                    $exchangeParams['cascade_configoption'] = $cascadeExchange;
                }

                $exchangeParams['buy'] = $param['buy'];
                break;
            default:
                break;
        }

        return $exchangeParams;
    }


    /**
     * 时间 2024-12-20
     * @title 级联配置项参数转换
     * @desc 将本地级联项ID转换为上游级联项ID
     * @author theworld
     * @version v1
     * @param int configoption_id - 配置项ID
     * @param mixed value - 级联项值（可能包含item_id和quantity等字段）
     * @return mixed - 转换后的上游级联项值
     */
    private function exchangeCascadeParams($configoptionId, $value)
    {
        try {
            $IdcsmartCommonCascadeItemModel = new IdcsmartCommonCascadeItemModel();
            $IdcsmartCommonCascadeGroupModel = new IdcsmartCommonCascadeGroupModel();

            // 获取该配置项的所有级联组
            $groups = $IdcsmartCommonCascadeGroupModel->where('configoption_id', $configoptionId)->select()->toArray();
            $groupIds = array_column($groups, 'id');

            if (empty($groupIds)) {
                return $value;
            }

            // 处理级联配置项的数据结构 {item_id: 12, quantity: 2}
            if (is_array($value) && isset($value['item_id'])) {
                // 单个级联项选择，包含item_id和其他属性
                $item = $IdcsmartCommonCascadeItemModel->whereIn('cascade_group_id', $groupIds)
                    ->where('id', $value['item_id'])
                    ->find();
                
                if (!empty($item)) {
                    $exchangedValue = $value; // 保持原有结构
                    $exchangedValue['item_id'] = $item['upstream_id'] ?? 0; // 转换item_id
                    return $exchangedValue;
                }
                return $value;
            } else {
                return $value;
            }
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function hostOtherParams(HostModel $HostModel)
    {
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();

        $links = $IdcsmartCommonHostConfigoptionModel->field('configoption_id,configoption_sub_id,qty')
            ->where('host_id',$HostModel['id'])
            ->select()
            ->toArray();

        return ['links'=>$links];
    }

    public function batchOperate($param)
    {
        $allowAction = [
            'on' => 'on',
            'off' => 'off',
            'reboot' => 'reboot',
            'hard_off' => 'hardOff',
            'hard_reboot' => 'hardReboot',
            'crack_pass' => 'CrackPassword',
            'reinstall' => 'Reinstall',
        ];
        if (empty($param['id']) || !is_array($param['id']) || empty($param['action']) || !in_array($param['action'],array_keys($allowAction))){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }
        if ($param['action']=='crack_pass' && empty($param['password'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }
        $HostModel = new HostModel();
        $count = $HostModel->where('client_id',get_client_id())
            ->whereIn('id',$param['id'])
            ->count();
        if ($count != count($param['id'])){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $resultData = [];
        foreach ($param['id'] as $id){
            $data = [
                'host_id' => $id,
                'func' => $param['action']
            ];
            if ($param['action']=='reinstall'){
                $data = array_merge($data,$param['reinstall'][$id]??[]);
            }elseif ($param['action']=='crack_pass'){
                $data['password'] = $param['password']??'';
            }
            $res = $this->provisionFunc($data);
            $resultData[] = ['id'=>$id,'msg'=>$res['msg'], 'status' => $res['status']];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$resultData];
    }

    public function batchReinstallPage($param)
    {
        if (empty($param['ids']) || !is_array($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }
        $HostModel = new HostModel();
        $count = $HostModel->where('client_id',get_client_id())
            ->whereIn('id',$param['ids'])
            ->count();
        if ($count != count($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $data = [];
        foreach ($param['ids'] as $id){
            $result = $this->hostConfigotpion([
                'host_id' => $id,
            ]);
            // 有操作系统选项
            if ($result['status']==200 && !empty($result['data']['os']['subs'])){
                $result['data']['id'] = $id;
                $data[] = $result['data'];
            }
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

}

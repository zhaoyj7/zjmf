<?php
namespace app\home\model;

use app\common\model\ProductGroupModel;
use think\Db;
use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use app\common\model\OrderItemModel;
use app\common\model\HostModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\ServerModel;
use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\SelfDefinedFieldValueModel;
use app\common\model\ClientModel;
use app\common\model\ProductOnDemandModel;

/**
 * @title 购物车模型
 * @desc 购物车模型
 * @use app\home\model\CartModel
 */
class CartModel extends Model
{
    protected $name = 'cart';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'client_id'     => 'int',
        'data'     		=> 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    private static $cartData = [];

    # 初始化购物车
    protected static function init()
    {
        $clientId = get_client_id();
        $cartCookie = cookie("cart_cookie");
        $cartCookieArray = [];
        if(!empty($cartCookie)){
            $cartCookieArray = json_decode($cartCookie, true);
        }

        if(!empty($clientId)) {
            $cart = self::where("client_id", $clientId)->find();
            if(!empty($cart)) {
                $cartData = json_decode($cart['data'], true);
                # cookie中存在产品数据
                if(!empty($cartCookieArray)){
                    # 数据库中存在数据，合并
                    if(!empty($cartData)){
                        foreach($cartCookieArray as $key => $value){
                            $cartData[] = $value; 
                        }
                    }else{
                        # 不存在产品数据，写入
                        $cartData = $cartCookieArray;
                    }
                }

            }else{
                $cartData = [];
                $data = [
                    'client_id' => $clientId,
                    'data' => [],
                    'create_time' => time()
                ];
                # 如果存在cookie数据
                if(!empty($cartCookieArray)) {
                    $cartData = $cartCookieArray;
                }
                $data['data'] = json_encode($cartData);
                self::create($data);
            }
            // 删除cookie
            cookie("cart_cookie", null);
            self::$cartData = $cartData;
            self::saveCart();
        }else{
            # 用户未登录情况
            if(!empty($cartCookieArray)){
                self::$cartData = $cartCookieArray;
            }else{
                self::$cartData = [];
            }
        }
    }

    /**
     * 时间 2022-05-30
     * @title 获取购物车
     * @desc 获取购物车
     * @author theworld
     * @version v1
     * @return  array list - 计算后数据
     * @return  int list[].product_id - 商品ID
     * @return  object list[].config_options - 自定义配置
     * @return  int list[].qty - 数量
     * @return  object list[].customfield - 自定义参数
     * @return  string list[].name - 商品名称
     * @return  string list[].description - 商品描述
     * @return  int list[].stock_control - 库存控制0:关闭1:启用
     * @return  int list[].stock_qty - 库存数量
     * @return  object list[].self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     */
    public function indexCart()
    {
        $cartData = [];
        if(!empty(self::$cartData)){
            $cartData = self::$cartData;
            $product = ProductModel::select(array_column($cartData, 'product_id'))->toArray();
            $productName = array_column($product, 'name', 'id');
            $productDesc = array_column($product, 'description', 'id');
            $productStock = array_column($product, 'stock_control', 'id');
            $productQty = array_column($product, 'qty', 'id');
            // $productSelfDefinedField = array_column($product, 'self_defined_field', 'id');
            foreach ($cartData as $key => $value) {
                $cartData[$key]['config_options'] = !empty($value['config_options']) ? $value['config_options'] : [];
                $cartData[$key]['customfield'] = !empty($value['customfield']) ? $value['customfield'] : [];
                $cartData[$key]['name'] = $productName[$value['product_id']] ?? '';
                $cartData[$key]['description'] = $productDesc[$value['product_id']] ?? '';
                $cartData[$key]['stock_control'] = $productStock[$value['product_id']] ?? 0;
                $cartData[$key]['stock_qty'] = $productQty[$value['product_id']] ?? 0;
                $cartData[$key]['self_defined_field'] = (isset($value['self_defined_field']) && !empty($value['self_defined_field']))?$value['self_defined_field']: [];
            }
        }

        $hookRes = hook('home_cart_index', ['cart'=>$cartData]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 200){
                foreach ($cartData as $key => $value) {
                    $cartData[$key]['customfield'] = array_merge($value['customfield'], $v['data'][$value['product_id']] ?? []);
                }
            }
        }

        foreach ($cartData as $key => $value) {
            $cartData[$key]['config_options'] = !empty($value['config_options']) ? $value['config_options'] : (object)[];
            $cartData[$key]['customfield'] = !empty($value['customfield']) ? $value['customfield'] : (object)[];
            $cartData[$key]['self_defined_field'] = (isset($value['self_defined_field']) && !empty($value['self_defined_field']))?$value['self_defined_field']: (object)[];
        }
        
        return ['list' => $cartData];
    }

    /**
     * 时间 2022-05-30
     * @title 加入购物车
     * @desc 加入购物车
     * @author theworld
     * @version v1
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  int param.qty - 数量 required
     * @param  object param.customfield - 自定义参数
     * @param  object param.self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @param  array param.products - 商品 批量加入购物车必传
     * @param  int param.products[].product_id - 商品ID
     * @param  object param.products[].config_options - 自定义配置
     * @param  int param.products[].qty - 数量
     * @param  object param.products[].customfield - 自定义参数
     * @param  object param.products[].self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createCart($param)
    {
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        if(isset($param['products']) && !empty($param['products'])){
            foreach ($param['products'] as $key => $value) {
                $product = ProductModel::find($value['product_id']);
                if(empty($product)){
                    return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
                }
                if($product['hidden']==1){
                    return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
                }
                if($product['stock_control']==1){
                    if($product['qty']<$value['qty']){
                        return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
                    }
                }
                $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
                    'product_id'          => $product['id'],
                    'self_defined_field'  => $value['self_defined_field'] ?? [],
                ]);
                if($checkSelfDefinedField['status'] != 200){
                    return $checkSelfDefinedField;
                }
                $value['self_defined_field'] = $checkSelfDefinedField['data'];
                $value['config_options'] = $value['config_options'] ?? [];

                //$value['config_options']['self_defined_field'] = $value['self_defined_field']??[];
                
                $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->where('mode','only_api')->find();

                if($upstreamProduct){
                    $value['config_options']['customfield'] = $value['config_options']['self_defined_field'] = $SelfDefinedFieldModel->toUpstreamId([
                        'product_id'          => $product['id'],
                        'self_defined_field'  => $value['self_defined_field'],
                    ]);

                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty'],'',true);
                }else{
                    $ModuleLogic = new ModuleLogic();
                    $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty']);
                }

                if($result['status']!=200){
                    return $result;
                }

                $data = [
                    'product_id' => $value['product_id'],
                    'config_options' => $value['config_options'] ?? [],
                    'qty' => $value['qty'],
                    'customfield' => $value['customfield'] ?? [],
                    'self_defined_field' => $value['self_defined_field'],
                ];
                //self::$cartData[] = $data;
                array_unshift(self::$cartData, $data);
            }
            self::saveCart();
            return ['status'=>200, 'msg'=>lang('add_success')];
        }else{
            $product = ProductModel::find($param['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            if($product['hidden']==1){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
                }
            }
            $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
                'product_id'          => $product['id'],
                'self_defined_field'  => $param['self_defined_field'] ?? [],
            ]);
            if($checkSelfDefinedField['status'] != 200){
                return $checkSelfDefinedField;
            }
            $param['self_defined_field'] = $checkSelfDefinedField['data'];
            $param['config_options'] = $param['config_options'] ?? [];

            //$value['config_options']['self_defined_field'] = $value['self_defined_field']??[];
            
            $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->where('mode','only_api')->find();

            if($upstreamProduct){
                $param['config_options']['customfield'] = $param['config_options']['self_defined_field'] = $SelfDefinedFieldModel->toUpstreamId([
                    'product_id'          => $product['id'],
                    'self_defined_field'  => $param['self_defined_field'],
                ]);
                
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty'],'',true);
            }else{
                $ModuleLogic = new ModuleLogic();
                $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty']);
            }

            if($result['status']!=200){
                return $result;
            }

            $data = [
                'product_id' => $param['product_id'],
                'config_options' => $param['config_options'] ?? [],
                'qty' => $param['qty'],
                'customfield' => $param['customfield'] ?? [],
                'self_defined_field' => $param['self_defined_field'] ?? [],
            ];
            //self::$cartData[] = $data;
            array_unshift(self::$cartData, $data);
            self::saveCart();
            return ['status'=>200, 'msg'=>lang('add_success')];
        }
        
    }

    /**
     * 时间 2022-05-30
     * @title 编辑购物车商品
     * @desc 编辑购物车商品
     * @author theworld
     * @version v1
     * @param  int param.position - 位置 required
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  int param.qty - 数量 required
     * @param  object param.customfield - 自定义参数
     * @param  object param.self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateCart($param)
    {
        $product = ProductModel::find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['hidden']==1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['stock_control']==1){
            if($product['qty']<$param['qty']){
                return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
            }
        }
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
            'product_id'          => $product['id'],
            'self_defined_field'  => $param['self_defined_field'] ?? [],
        ]);
        if($checkSelfDefinedField['status'] != 200){
            return $checkSelfDefinedField;
        }
        $param['self_defined_field'] = $checkSelfDefinedField['data'];
        $param['config_options'] = $param['config_options'] ?? [];

        $value['config_options']['self_defined_field'] = $param['self_defined_field']??[];
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->where('mode','only_api')->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['config_options']);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options']);
        }
        if($result['status']!=200){
            return $result;
        }

        $position = $param['position'];
        $data = [
            'product_id' => $param['product_id'],
            'config_options' => $param['config_options'] ?? [],
            'qty' => $param['qty'],
            'customfield' => $param['customfield'] ?? [],
            'self_defined_field' => $param['self_defined_field'] ?? [],
        ];
        if(isset(self::$cartData[$position])){
            self::$cartData[$position] = $data;
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 修改购物车商品数量
     * @desc 修改购物车商品数量
     * @author theworld
     * @version v1
     * @param  int param.position - 位置 required
     * @param  int param.qty - 数量 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateCartQty($param)
    {
        $position = $param['position'];
        unset($param['position']);
        if(isset(self::$cartData[$position])){
            $product = ProductModel::find(self::$cartData[$position]['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
                }
            }
            self::$cartData[$position]['qty'] = $param['qty'];
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 删除购物车商品
     * @desc 删除购物车商品
     * @author theworld
     * @version v1
     * @param  int position - 位置 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteCart($position)
    {
        if(isset(self::$cartData[$position])){
            unset(self::$cartData[$position]);
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 批量删除购物车商品
     * @desc 批量删除购物车商品
     * @author theworld
     * @version v1
     * @param  array positions - 位置 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteCart($positions)
    {
        foreach ($positions as $key => $value) {
            if(isset(self::$cartData[$value])){
                unset(self::$cartData[$value]);
            }else{
                return ['status'=>400, 'msg'=>lang('param_error')];
            }
        }
        
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 清空购物车
     * @desc 清空购物车
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function clearCart($param)
    {
        self::$cartData = [];
        self::saveCart();

        # 20230216 wyh
        if (request()->is_api){
            $HostModel = new HostModel();
            $host = $HostModel->whereLike('downstream_info', '%'.$param['downstream_token'].'%')->where('downstream_host_id',$param['downstream_host_id']??0)->find();
            if (!empty($host) && $host['is_delete'] == 0){
                $OrderModel = new OrderModel();
                $order = $OrderModel->find($host['order_id']);
                if (!empty($order) && $order['is_recycle'] == 0){
                    if ($order['status']!='Paid'){
                        // 返回自定义字段
                        $hookResult = hook('order_create_return_customfield',['order_id'=>$order->id]);
                        $customfields = [];
                        foreach ($hookResult as $value){
                            $customfields = array_merge($customfields,$value);
                        }
                        return ['status'=>200, 'msg'=>lang('clear_cart_success'),'data'=>['order_id'=>$order['id'],'customfields'=>$customfields]];
                    }else{
                        return ['status'=>400,'msg'=>'订单已开通,请勿重新开通'];
                    }
                }
            }
        }

        return ['status'=>200, 'msg'=>lang('clear_cart_success')];
    }

    /**
     * 时间 2022-05-31
     * @title 结算购物车
     * @desc 结算购物车
     * @author theworld
     * @version v1
     * @param  array positions - 商品位置数组 required
     * @param  object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @param  int param.downstream_host_id - 下游产品ID
     * @param  string param.downstream_url - 下游地址
     * @param  string param.downstream_token - 下游产品token
     * @param  string param.downstream_system_type - 下游系统类型
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function settle($position,$customfield=[],$param=[])
    {
        $amount = 0;
        $cartData = [];
        if(empty(self::$cartData)){
            return ['status'=>400, 'msg'=>lang('there_are_no_items_in_the_cart')];
        }
        $appendOrderItem = [];

        $clientId = get_client_id();
        $credit = ClientModel::where('id', $clientId)->value('credit');

        $OrderModel = new OrderModel();
        if($OrderModel->haveUnpaidOnDemandOrder($clientId)){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_order_cannot_do_this') ];
        }

        $certification = check_certification($clientId);
        $ModuleLogic = new ModuleLogic();
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
        $ProductOnDemandModel = new ProductOnDemandModel();
        foreach (self::$cartData as $key => $value) {
            if(in_array($key, $position)){
                $product = ProductModel::where('hidden', 0)->find($value['product_id']);
                if(empty($product)){
                    return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
                }
                if(!empty($product['product_id'])){
                    return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
                }
                $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
                    'product_id'          => $product['id'],
                    'self_defined_field'  => $value['self_defined_field'] ?? [],
                ]);
                if($checkSelfDefinedField['status'] != 200){
                    return $checkSelfDefinedField;
                }
                $value['self_defined_field'] = $checkSelfDefinedField['data'];
                $value['config_options'] = $value['config_options'] ?? [];

                $self_defined_field = $value['config_options']['customfield']??[];

                // wyh 20230719 加入自定义字段
                $value['config_options']['customfield'] = $customfield;

                $value['config_options']['self_defined_field'] = $self_defined_field;

                $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

                if($upstreamProduct && $upstreamProduct['mode']=='only_api'){
                    if($upstreamProduct['certification']==1 && !$certification){
                        return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product'),'data'=>['certification'=>0]];
                    }
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty'],'cal_price',true,true);
                }else{
                    if (!empty($upstreamProduct)){
                        if($upstreamProduct['certification']==1 && !$certification){
                            return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product'),'data'=>['certification'=>0]];
                        }
                    }
                    $value['config_options']['settle_qty'] = $value['qty'];
                    $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty'],'buy',$key);
                }
                if($result['status']!=200){
                    return $result;
                }
                if($product['pay_type']=='free'){
                    $result['data']['price'] = 0;
                }
                // wyh 20240226 上下游商品，价格已算上数量
                $result['data']['price'] = $upstreamProduct && $upstreamProduct['mode'] == 'only_api' ?bcdiv($result['data']['price'],$value['qty'],2):$result['data']['price'];
                //$amount = $upstreamProduct?bcadd($amount,$result['data']['price'],2):bcadd($amount,$result['data']['price']*$value['qty'],2);

                $amount += $result['data']['price']*$value['qty'];
                if(isset($result['data']['sub_host'])){
                    foreach ($result['data']['sub_host'] as $subHost){
                        if (!empty($upstreamProduct)){
                            if ($upstreamProduct['profit_type']==1){ // 固定利润不处理
                                $amount += $subHost['price']*$value['qty'];
                            }else{
                                $profitAndBasePercent = bcadd(1,$upstreamProduct['profit_percent']/100,4);
                                $amount += $subHost['price']*$profitAndBasePercent*$value['qty'];
                            }
                        }else{
                            $amount += $subHost['price']*$value['qty'];
                        }
                    }
                }

                $cartData[$key] = $value;
                // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
                $cartData[$key]['order_item'] = $result['data']['order_item'] ?? [];
                $cartData[$key]['price'] = $result['data']['price'];
                $cartData[$key]['discount'] = $result['data']['discount'] ?? 0;
                $cartData[$key]['renew_price'] = $result['data']['renew_price'] ?? $cartData[$key]['price'];
                $cartData[$key]['billing_cycle'] = $result['data']['billing_cycle'];
                $cartData[$key]['duration'] = $result['data']['duration'];
                $cartData[$key]['description'] = $result['data']['description'];
                $cartData[$key]['base_price'] = $result['data']['base_price']??$result['data']['price'];
                $cartData[$key]['ontrial'] = $result['data']['ontrial']??false;
                $cartData[$key]['sub_host'] = $result['data']['sub_host'] ?? [];
                $cartData[$key]['base_renew_price'] = $result['data']['base_renew_price'] ?? $cartData[$key]['renew_price'];
                if($upstreamProduct){
                    $cartData[$key]['profit'] = $result['data']['profit']??0;
                }
                // 产品费用类型
                $cartData[$key]['host_billing_cycle'] = $product['pay_type'] == 'recurring_prepayment_on_demand' ? ($result['data']['host_billing_cycle'] ?? 'recurring_prepayment') : $product['pay_type'];
                // 按需时获取出账周期,验证余额
                if($cartData[$key]['host_billing_cycle'] == 'on_demand'){
                    $productOnDemand = ProductOnDemandModel::getProductOnDemand($value['product_id']);
                    if(!empty($productOnDemand['min_credit']) && $productOnDemand['min_credit'] > $credit){
                        return ['status'=>400, 'msg'=>lang('product_on_demand_buy_need_min_credit', ['{product}'=>$product['name'],'{credit}'=>$credit]) ];
                    }
                    $cartData[$key]['keep_time_price'] = $result['data']['keep_time_price'] ?? '0.0000';
                    $cartData[$key]['on_demand_flow_price'] = $result['data']['on_demand_flow_price'] ?? '0.0000';
                    $cartData[$key]['on_demand_billing_cycle_unit'] = $productOnDemand['billing_cycle_unit'];
                    $cartData[$key]['on_demand_billing_cycle_day'] = $productOnDemand['billing_cycle_day'];
                    $cartData[$key]['on_demand_billing_cycle_point'] = $productOnDemand['billing_cycle_point'];
                    $cartData[$key]['base_price'] = 0;
                }else{
                    // 设定默认值
                    $cartData[$key]['keep_time_price'] = '0.0000';
                    $cartData[$key]['on_demand_flow_price'] = '0.0000';
                    $cartData[$key]['on_demand_billing_cycle_unit'] = 'hour';
                    $cartData[$key]['on_demand_billing_cycle_day'] = 1;
                    $cartData[$key]['on_demand_billing_cycle_point'] = '00:00';
                }
                $cartData[$key]['discount_renew_price'] = 0;
                $cartData[$key]['renew_use_current_client_level'] = 0;
                if(isset($result['data']['discount_renew_price']) && is_numeric($result['data']['discount_renew_price']) ){
                    $cartData[$key]['discount_renew_price'] = $result['data']['discount_renew_price'];
                    $cartData[$key]['renew_use_current_client_level'] = 1;
                }
                $cartData[$key]['is_natural_month_prepaid'] = $result['data']['is_natural_month_prepaid'] ?? false;
                $cartData[$key]['due_time'] = $result['data']['due_time'] ?? 0;

                unset(self::$cartData[$key]);
            }
        }
        if(empty($cartData)){
            return ['status'=>400, 'msg'=>lang('please_select_products_in_the_cart')];
        }
        

        $result = hook('before_order_create', ['client_id'=>$clientId, 'cart' => $cartData]);

        foreach ($result as $value){
            if (isset($value['status']) && $value['status']==400){
                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message'),'data'=>$value['data']??[]];
            }
        }

        $this->startTrans();
        try {
            // 创建订单
            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/
            
            $time = time();
            $order = OrderModel::create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' :'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);
            
            // 创建产品
            $orderItem = [];
            $productLog = [];
            $hostIds = [];
            foreach ($cartData as $key => $value) {
                $product = ProductModel::find($value['product_id']);
                if($product['stock_control']==1){
                    if($product['qty']<$value['qty']){
                        throw new \Exception(lang('product_inventory_shortage'));
                    }
                    ProductModel::where('id', $value['product_id'])->dec('qty', $value['qty'])->update();
                }
                if(empty($value['description'])){
                    if($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment'){
                        $value['description'] = $product['name'].'('.date("Y-m-d H:i:s").'-'.date("Y-m-d H:i:s",time()+$value['duration']).')';
                    }else{
                        $value['description'] = $product['name'];
                    }
                }
                $productLog[] = 'product#'.$product['id'].'#'.$product['name'].'#';

                if($product['type']=='server_group'){
                    // 域名相关
                    $ProductGroupModel = new ProductGroupModel();
                    $productGroupType = $ProductGroupModel->where('id',$product['product_group_id'])->value("type");
                    if ($productGroupType=="domain"){
                        $customGetModul = hook_one("custom_get_module",['domain'=>$value['config_options']['domain']??""]);
                        $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->where("module",$customGetModul)->find();
                        $serverId = $server['id'] ?? 0;
                    }else{
                        if($product['rel_id'] > 0){
                            $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                            $serverId = $server['id'] ?? 0;
                        }else{
                            $serverId = 0;
                        }
                    }
                }else{
                    $serverId = $product['rel_id'];
                }
                // 这里不改，两种代理模式都存一份
                $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();
                for ($i=1; $i<=$value['qty']; $i++) {
                    if (request()->is_api){
                        $downstreamHostId = intval($param['downstream_host_id'] ?? 0);
                        if(!empty($downstreamHostId)){
                            $downstreamInfo = json_encode(['url' => $param['downstream_url']??'', 'token'=>$param['downstream_token']??'', 'api'=>request()->api_id,'type'=>$param['downstream_system_type']??""]);
                        }
                    }

                    $name = generate_host_name($value['product_id']);

                    // 计算到期时间
                    $dueTime = !in_array($value['host_billing_cycle'], ['on_demand','onetime']) ? $time : 0;
                    // 如果是自然月预付费，使用模块返回的到期时间
                    if(!empty($value['is_natural_month_prepaid']) && !empty($value['due_time'])){
                        $dueTime = $value['due_time'];
                    }

                    $host = HostModel::create([
                        'client_id' => $clientId,
                        'order_id' => $order->id,
                        'product_id' => $value['product_id'],
                        'server_id' => $serverId,
                        'name' => $name,
                        'status' => 'Unpaid',
                        'first_payment_amount' => $value['price'],
                        'renew_amount' => in_array($value['host_billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $value['renew_price'] : 0,
                        'billing_cycle' => $value['host_billing_cycle'],
                        'billing_cycle_name' => $value['billing_cycle'],
                        'billing_cycle_time' => $value['duration'],
                        'active_time' => $time,
                        'due_time' => $dueTime,
                        'create_time' => $time,
                        'downstream_info' => $downstreamInfo ?? '',
                        'downstream_host_id' => $downstreamHostId ?? 0,
                        'base_price' => $value['base_price'],
                        'ratio_renew' => ProductModel::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
                        'base_renew_amount' => in_array($value['host_billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $value['base_renew_price'] : 0,
                        'base_config_options' => json_encode($value['config_options']),
                        'is_ontrial' => $value['ontrial'],
                        'first_payment_ontrial' => $value['ontrial'],
                        'keep_time_price' => $value['keep_time_price'],
                        'on_demand_flow_price' => $value['on_demand_flow_price'],
                        'on_demand_billing_cycle_unit' => $value['on_demand_billing_cycle_unit'],
                        'on_demand_billing_cycle_day' => $value['on_demand_billing_cycle_day'],
                        'on_demand_billing_cycle_point' => $value['on_demand_billing_cycle_point'],
                        'discount_renew_price' => $value['discount_renew_price'],
                        'renew_use_current_client_level' => $value['renew_use_current_client_level'],
                    ]);

                    hook('after_host_create',['id'=>$host->id, 'param'=>$param,'customfield'=>$customfield]);

                    $hostIds[] = $host->id;

                    if($upstreamProduct){
                        // wyh 20231211 改
                        $value['config_options']['configoption']['host'] = $name;

                        UpstreamHostModel::create([
                            'supplier_id' => $upstreamProduct['supplier_id'],
                            'host_id' => $host->id,
                            'upstream_configoption' => json_encode($value['config_options']),
                            'create_time' => $time
                        ]);
                        UpstreamOrderModel::create([
                            'supplier_id' => $upstreamProduct['supplier_id'],
                            'order_id' => $order->id,
                            'host_id' => $host->id,
                            'amount' => $value['price'],
                            'profit' => $value['profit']??0,
                            'create_time' => $time
                        ]);
                        if ($upstreamProduct['mode']=='only_api'){
                            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                            $result = $ResModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
                        }else{
                            $value['config_options']['customfield'] = $value['customfield'];
                            $ModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
                        }
                    }else{
                        $value['config_options']['customfield'] = $value['customfield'];
                        $ModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
                    }

                    // 产品和对应自定义字段
                    $customfield['host_customfield'][] = ['id'=>$host->id, 'customfield' => $value['customfield'] ?? []];

                    //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
                    if (in_array($host['billing_cycle'],['onetime','free'])){
                        $desDueTime = '∞';
                    }else{
                        $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
                        //$desDueTime = date('Y/m/d',$host['active_time']);
                    }
                    $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
                    if (is_array($value['description'])){
                        $value['description'] = implode("\n",$value['description']);
                    }

                    $orderItem[] = [
                        'order_id' => $order->id,
                        'client_id' => $clientId,
                        'host_id' => $host->id,
                        'product_id' => $value['product_id'],
                        'type' => 'host',
                        'rel_id' => $host->id,
                        'amount' => bcadd($value['price'], $value['discount'],2),
                        'description' => $value['description'] . "\n" . $des,
                        'create_time' => $time,
                    ];

                    // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
                    if (!empty($value['order_item'])){
                        foreach($value['order_item'] as $v){
                            $v['order_id'] = $order->id;
                            $v['client_id'] = $clientId;
                            $v['host_id'] = $host->id;
                            $v['product_id'] = $value['product_id'];
                            $v['create_time'] = $time;
                            $orderItem[] = $v;
                        }
                    }

                    // 保存自定义字段
                    $selfDefinedFieldValue = [];
                    foreach($value['self_defined_field'] as $k=>$v){
                        $selfDefinedFieldValue[] = [
                            'self_defined_field_id' => $k,
                            'relid'                 => $host->id,
                            'value'                 => (string)$v,
                            'order_id'              => $order->id,
                            'create_time'           => $time,
                        ];
                    }
                    $SelfDefinedFieldValueModel->insertAll($selfDefinedFieldValue);

                    $parentHostId = $host->id;
                    foreach ($value['sub_host'] as $subHost)
                    {
                        $name = generate_host_name($value['product_id']);

                        // 计算到期时间
                        $dueTime = !in_array($value['host_billing_cycle'], ['onetime','on_demand']) ? $time : 0;

                        $host = HostModel::create([
                            'client_id' => $clientId,
                            'order_id' => $order->id,
                            'product_id' => $value['product_id'],
                            'server_id' => $serverId,
                            'name' => $name,
                            'status' => 'Unpaid',
                            'first_payment_amount' => $subHost['price'],
                            'renew_amount' => ($value['host_billing_cycle']=='recurring_postpaid' || $value['host_billing_cycle']=='recurring_prepayment') ? $subHost['renew_price'] : 0,
                            'billing_cycle' => $value['host_billing_cycle'],
                            'billing_cycle_name' => $subHost['billing_cycle'],
                            'billing_cycle_time' => $subHost['duration'],
                            'active_time' => $time,
                            'due_time' => $dueTime,
                            'create_time' => $time,
                            'downstream_info' => $downstreamInfo ?? '',
                            'downstream_host_id' => $downstreamHostId ?? 0,
                            'base_price' => $subHost['base_price'],
                            'ratio_renew' => ProductModel::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
                            'base_renew_amount' => ($value['host_billing_cycle']=='recurring_postpaid' || $value['host_billing_cycle']=='recurring_prepayment') ? $subHost['renew_price'] : 0,
                            'base_config_options' => json_encode($subHost['config_options']),
                            'is_ontrial' => $value['ontrial'],
                            'first_payment_ontrial' => $value['ontrial'],
                            'is_sub' => 1,
                        ]);

                        hook('after_host_create',['id'=>$host->id, 'param'=>$param,'customfield'=>$customfield]);

                        $hostIds[] = $host->id;

                        if($upstreamProduct){
                            // wyh 20231211 改
                            $subHost['config_options']['configoption']['host'] = $name;

                            UpstreamHostModel::create([
                                'supplier_id' => $upstreamProduct['supplier_id'],
                                'host_id' => $host->id,
                                'upstream_configoption' => json_encode($subHost['config_options']),
                                'create_time' => $time
                            ]);
                            UpstreamOrderModel::create([
                                'supplier_id' => $upstreamProduct['supplier_id'],
                                'order_id' => $order->id,
                                'host_id' => $host->id,
                                'amount' => $subHost['price'],
                                'profit' => $subHost['profit']??0,
                                'create_time' => $time
                            ]);
                            if ($upstreamProduct['mode']=='only_api'){
                                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                                $subHost['config_options']['parent_host_id'] = $parentHostId;
                                $result = $ResModuleLogic->afterSettle($product, $host->id, $subHost['config_options'],$customfield, $key);
                            }else{
                                $subHost['config_options']['customfield'] = $value['customfield'];
                                $subHost['config_options']['parent_host_id'] = $parentHostId;
                                $ModuleLogic->afterSettle($product, $host->id, $subHost['config_options'],$customfield, $key);
                            }
                        }else{
                            $subHost['config_options']['customfield'] = $value['customfield'];
                            $subHost['config_options']['parent_host_id'] = $parentHostId;
                            $ModuleLogic->afterSettle($product, $host->id, $subHost['config_options'],$customfield, $key);
                        }

                        // 产品和对应自定义字段
                        $customfield['host_customfield'][] = ['id'=>$host->id, 'customfield' => $value['customfield'] ?? []];

                        //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
                        if (in_array($host['billing_cycle'],['onetime','free'])){
                            $desDueTime = '∞';
                        }else{
                            $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
                            //$desDueTime = date('Y/m/d',$host['active_time']);
                        }
                        $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
                        if (is_array($subHost['description'])){
                            $subHost['description'] = implode("\n",$subHost['description']);
                        }

                        $orderItem[] = [
                            'order_id' => $order->id,
                            'client_id' => $clientId,
                            'host_id' => $host->id,
                            'product_id' => $value['product_id'],
                            'type' => 'host',
                            'rel_id' => $host->id,
                            'amount' => bcadd($subHost['price'], $subHost['discount'] ?? 0),
                            'description' => $subHost['description'] . "\n" . $des,
                            'create_time' => $time,
                        ];

                        // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
                        if (isset($subHost['order_item']) && !empty($subHost['order_item'])){
                            foreach($subHost['order_item'] as $v){
                                $v['order_id'] = $order->id;
                                $v['client_id'] = $clientId;
                                $v['host_id'] = $host->id;
                                $v['product_id'] = $value['product_id'];
                                $v['create_time'] = $time;
                                $orderItem[] = $v;
                            }
                        }

                        // 保存自定义字段
                        $selfDefinedFieldValue = [];
                        foreach($value['self_defined_field'] as $k=>$v){
                            $selfDefinedFieldValue[] = [
                                'self_defined_field_id' => $k,
                                'relid'                 => $host->id,
                                'value'                 => (string)$v,
                                'order_id'              => $order->id,
                                'create_time'           => $time,
                            ];
                        }
                        $SelfDefinedFieldValueModel->insertAll($selfDefinedFieldValue);
                    }
                }
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            # 记录日志
            active_log(lang('submit_order', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#', '{order}'=>$order->id, '{product}'=>implode(',', $productLog)]), 'order', $order->id);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$customfield]);

            update_upstream_order_profit($order->id);

            self::saveCart();

            $OrderModel = new OrderModel();
            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $OrderModel->where('id',$order->id)->value('amount');

            if($amount<=0){
                $OrderModel->processPaidOrder($order->id);
            }

            // wyh 20240402 新增 支付后跳转地址
            $domain = configuration('website_url');
            if (count($hostIds)>1){
                $returnUrl = "{$domain}/finance.htm";
            }else{
                if (isset($hostIds[0]) && !empty($hostIds[0])){
                    $returnUrl = "{$domain}/productdetail.htm?id=".$hostIds[0];
                }else{
                    $returnUrl = "{$domain}/finance.htm";
                }
            }
            
            // 如果是自然月预付费，设置10分钟未支付超时
            $orderUpdate = ['return_url' => $returnUrl];
            $hasNaturalMonthPrepaid = false;
            foreach ($cartData as $value) {
                if(!empty($value['is_natural_month_prepaid'])){
                    $hasNaturalMonthPrepaid = true;
                    break;
                }
            }
            if($hasNaturalMonthPrepaid){
                $orderUpdate['unpaid_timeout'] = time() + 600;
            }
            $order->save($orderUpdate);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        system_notice([
            'name'                  => 'order_create',
            'email_description'     => lang('order_create_send_mail'),
            'sms_description'       => lang('order_create_send_sms'),
            'task_data' => [
                'client_id' => $clientId,
                'order_id'  => $order->id,
            ],
        ]);

        // 返回自定义字段
        $hookResult = hook('order_create_return_customfield',['order_id'=>$order->id]);
        $customfields = [];
        foreach ($hookResult as $value){
            $customfields = array_merge($customfields,$value);
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id, 'amount' => $amount,'host_ids'=>$hostIds,'customfields'=>$customfields]];
    }

    # 保存购物车
    private static function saveCart(){
        $clientId = get_client_id();
        $cartData = [];
        foreach (self::$cartData as $key => $value) {
            if(!isset($cartData[$value['product_id'].'_'.json_encode($value['config_options'] ?? []).'_'.json_encode($value['customfield'] ?? []).'_'.json_encode($value['self_defined_field'] ?? [])])){
                $cartData[$value['product_id'].'_'.json_encode($value['config_options'] ?? []).'_'.json_encode($value['customfield'] ?? []).'_'.json_encode($value['self_defined_field'] ?? [])] = $value;
            }else{
                $cartData[$value['product_id'].'_'.json_encode($value['config_options'] ?? []).'_'.json_encode($value['customfield'] ?? []).'_'.json_encode($value['self_defined_field'] ?? [])]['qty'] += $value['qty'];
            }
            
        }
        self::$cartData = array_values($cartData);
        $cartJson = json_encode(self::$cartData);
        if(!empty($clientId)){
            $data = [
                'data' => $cartJson,
                'update_time' => time(),
            ];
            self::update($data, ['client_id' => $clientId]);
        }else{
            # 未登录保存到本地
            cookie("cart_cookie", $cartJson, 30 * 24 * 3600);
        }
    }


}
<?php
namespace app\api\controller;

use app\common\logic\ModuleLogic;
use app\common\model\ProductGroupModel;
use app\common\model\ProductModel;
use app\common\model\ServerGroupModel;
use app\common\model\ServerModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\UpstreamProductModel;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\api\controller\ProductController
 */
class ProductController
{
    /**
     * @title 所有商品列表(含分组)
     * @desc 所有商品列表(含分组)
     * @author wyh
     * @version v1
     * @url /api/v1/group/product
     * @method GET
     * @return array products - desc:商品列表 包括商品分组
     * @return int products[].id - desc:商品分组ID
     * @return string products[].name - desc:商品分组名称
     * @return array products[].products - desc:分组下的商品列表
     * @return int products[].products[].id - desc:商品ID
     * @return string products[].products[].name - desc:名称
     * @return string products[].products[].description - desc:描述
     * @return string products[].products[].module - desc:服务器关联模块
     * @return string products[].products[].sgs_module - desc:服务器分组关联模块
     */
    public function groupProduct()
    {
        $ProductGroupModel = new ProductGroupModel();
        $groups = $ProductGroupModel->field('id,name')
            ->where('hidden',0)
            ->where('parent_id','>',0)
            ->order('order','asc')
            ->select()
            ->toArray();
        $ProductModel = new ProductModel();
        foreach ($groups as &$group){
            $group['products'] = $ProductModel->alias('p')
                ->field("p.id,p.name,p.description,s.module,sgs.module as sgs_module")
                ->withAttr("module",function ($value,$data){
                    return $value??$data['sgs_module'];
                })
                ->whereIn('s.module|sgs.module',['mf_dcim','mf_cloud','idcsmart_common','chinac_cloud_phone','chinac_network'])
                ->where('p.product_group_id',$group['id'])
                ->where('p.hidden',0)
                ->where('p.agentable',1)
                ->leftJoin('server s','p.type=\'server\' and s.id=p.rel_id')
                ->leftJoin('server_group sg','p.type=\'server_group\' and sg.id=p.rel_id')
                ->leftJoin('server sgs','sgs.server_group_id=sg.id')
                ->select()
                ->toArray();
        }

        $groupsFilter = [];
        foreach ($groups as $item){
            if (!empty($item['products'])){
                $groupsFilter[] = $item;
            }
        }

        return json([
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'products' => $groupsFilter,
                'currency' => configuration("currency_code")
            ]
        ]);
    }

    /**
     * @title 所有商品列表
     * @desc 所有商品列表
     * @author wyh
     * @version v1
     * @url /api/v1/product
     * @method GET
     * @return array list - desc:商品列表
     * @return int list[].id - desc:商品ID
     * @return string list[].name - desc:名称
     * @return string list[].description - desc:描述
     * @return string list[].pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return float list[].price - desc:价格
     * @return string list[].cycle - desc:周期
     * @return string list[].mode - desc:代理模式 only_api仅调用接口 sync同步商品 空表示非代理商品
     * @return int list[].stock_control - desc:是否库存控制 1是 0否
     * @return int list[].qty - desc:库存
     */
    public function product()
    {
        if ($list = idcsmart_cache('product:list')){
            $list = json_decode($list,true);
        }else{
            $ProductModel = new ProductModel();
            $list = $ProductModel->alias('p')
                ->field('p.id,p.name,p.description,p.pay_type,p.price,p.cycle,up.mode,p.stock_control,p.qty')
                ->withAttr("mode",function ($value,$data){
                    if (is_null($value)){
                        return '';
                    }
                    return $value;
                })
                ->leftJoin('upstream_product up','p.id=up.product_id')
                ->where('p.hidden',0)
                ->where('p.agentable',1)
                ->order('p.id','desc')
                ->order('p.order','asc')
                ->select()
                ->toArray();
            idcsmart_cache('product:list',json_encode($list),30*24*3600);
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'list' => $list,
                'currency_code' => configuration('currency_code'),
            ]
        ];

        return json($result);
    }

    /**
     * @title 商品详情
     * @desc 商品详情
     * @author wyh
     * @version v1
     * @url /api/v1/product/:id
     * @method GET
     * @param string price_basis - desc:价格基础 standard标准价 agent代理价 validate:optional
     * @return int id - desc:商品ID
     * @return string name - desc:名称
     * @return string pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return float price - desc:价格
     * @return string cycle - desc:周期
     * @return int auto_setup - desc:是否自动开通 1是 0否
     * @return string description - desc:描述
     * @return int cancel_control - desc:取消控制
     * @return array self_defined_field - desc:自定义字段
     * @return int self_defined_field[].id - desc:ID
     * @return string self_defined_field[].field_name - desc:字段名称
     * @return string self_defined_field[].field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 tickbox勾选框 textarea文本区
     * @return string self_defined_field[].description - desc:字段描述
     * @return string self_defined_field[].regexpr - desc:验证规则
     * @return string self_defined_field[].field_option - desc:下拉选项
     * @return string self_defined_field[].is_required - desc:是否必填 0否 1是
     * @return string self_defined_field[].show_client_host_list - desc:前台列表可见 0否 1是
     * @return string self_defined_field[].upstream_id - desc:上下游ID 需要的need_upstream_id才返回
     * @return array data_center_map_group - desc:区域组列表
     * @return int data_center_map_group[].id - desc:区域组ID
     * @return string data_center_map_group[].name - desc:区域组名称
     * @return string data_center_map_group[].description - desc:区域组描述
     * @return int data_center_map_group[].upstream_id - desc:上游区域组ID
     * @return array data_center_map_group[].data_center - desc:数据中心关联
     * @return int data_center_map_group[].data_center[].product_id - desc:商品ID
     * @return array data_center_map_group[].data_center[].data_center_id - desc:数据中心ID列表
     */
    public function index()
    {
        $param = request()->param();
        $id = intval($param['id'] ?? 0);
        
        // 获取价格基础参数，默认为agent（保持向后兼容）
        $priceBasis = $param['price_basis'] ?? 'agent';
        
        // 参数验证
        if (!in_array($priceBasis, ['standard', 'agent'])) {
            return json(['status' => 400, 'msg' => 'Invalid price_basis parameter']);
        }
        
        $ProductModel = new ProductModel();
        $product = $ProductModel->field('id,name,pay_type,price,cycle,auto_setup,description,custom_host_name,custom_host_name_prefix,
        custom_host_name_string_allow,custom_host_name_string_length,renew_rule,stock_control,qty,natural_month_prepaid')
            ->where('hidden', 0)
            ->where('agentable', 1)
            ->where('id', $id)
            ->find();
        if(empty($product)){
            $product = (object)[]; 
        }
        if (!empty($product)){
            $product['cancel_control'] = 0;
            if (class_exists("addon\idcsmart_refund\IdcsmartRefund")){
                $product['cancel_control'] = 1;
            }
        }

        $sync = isset($param['mode']) && $param['mode']=='sync';

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$id)->find();
        if (empty($upstreamProduct) && !$sync){
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->getPriceCycle($id);
        }else{
            // 若是上游商品，则使用商品price（处理多级代理）
            $res['price'] = $product['price'];
        }

        // 根据价格基础决定是否应用代理商折扣
        if ($priceBasis === 'agent') {
            // 代理价：应用用户等级折扣
            $hookDiscountResultsOrgins = hook("client_discount_by_amount",['client_id'=>get_client_id(),'product_id'=>$product['id'],'amount'=>$res['price']]);
            foreach ($hookDiscountResultsOrgins as $hookDiscountResultsOrgin){
                if ($hookDiscountResultsOrgin['status']==200){
                    $res['price'] = bcsub($res['price'], $hookDiscountResultsOrgin['data']['discount']??0, 2);
                    $res['price'] = $res['price']>0 ? $res['price'] : 0;
                }
            }
        }
        // 标准价：不应用折扣，直接使用原价
        
        $product['price'] = $res['price'];
        $product['cycle'] = $res['cycle']??$product['cycle'];

        // 自定义字段
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $selfDefinedField = $SelfDefinedFieldModel->showOrderPageField([
            'id' => $id,
        ]);

        // 下游代理模式为：同步商品
        if ($sync){
            $otherParams = (new ModuleLogic())->otherParams($id);
        }else{
            $otherParams = [];
        }

        // 获取区域组信息（仅魔方云相关商品）
        $dataCenterMapGroup = [];
        if (!empty($product)) {
            $MfCloudDataCenterMapGroupModel = new \app\common\model\MfCloudDataCenterMapGroupModel();
            $dataCenterMapGroupResult = $MfCloudDataCenterMapGroupModel->getProductDataCenterMapGroupWithLinks(['product_id' => $id]);
            $dataCenterMapGroup = $dataCenterMapGroupResult['list'] ?? [];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'product'                => $product,
                'self_defined_field'     => $selfDefinedField['data'],
                'other_params'           => $otherParams['data']??[],
                'data_center_map_group'  => $dataCenterMapGroup,
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title 获取上游商品模块和资源
     * @desc 获取上游商品模块和资源
     * @url /api/v1/product/:id/resource
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return string module - desc:resmodule名称
     * @return string url - desc:zip包完整下载路径
     * @return string version - desc:版本号
     */
    public function downloadResource()
    {
        $param = request()->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->downloadResource($param);
        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title 获取上游商品模块和资源
     * @desc 获取上游商品模块和资源
     * @url /api/v1/product/:id/plugin_resource
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return array plugin - desc:插件列表
     * @return string plugin[].module - desc:插件模块
     * @return string plugin[].name - desc:插件标识
     * @return string plugin[].url - desc:zip包完整下载路径
     * @return string plugin[].version - desc:版本号
     */
    public function downloadPluginResource()
    {
        $param = request()->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->downloadPluginResource($param);
        return json($result);
    }

}
<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use app\admin\model\ProductDurationGroupPresetsLinkModel;
use app\admin\model\ProductDurationGroupPresetsModel;
use app\admin\model\ProductDurationPresetsModel;
use think\db\Query;
use think\facade\Db;
use think\Model;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;

/**
 * @title 商品模型
 * @desc 商品模型
 * @use app\common\model\ProductModel
 */
class ProductModel extends Model
{
    protected $name = 'product';

    // 设置字段信息
    protected $schema = [
        'id'                                => 'int',
        'name'                              => 'string',
        'product_group_id'                  => 'int',
        'description'                       => 'string',
        'hidden'                            => 'int',
        'stock_control'                     => 'int',
        'qty'                               => 'int',
        'pay_type'                          => 'string',
        'auto_setup'                        => 'int',
        'type'                              => 'string',
        'rel_id'                            => 'int',
        'order'                             => 'int',
        // 'creating_notice_sms'               => 'int',
        // 'creating_notice_sms_api'           => 'int',
        // 'creating_notice_sms_api_template'  => 'int',
        // 'created_notice_sms'                => 'int',
        // 'created_notice_sms_api'            => 'int',
        // 'created_notice_sms_api_template'   => 'int',
        // 'creating_notice_mail'              => 'int',
        // 'creating_notice_mail_api'          => 'int',
        // 'creating_notice_mail_template'     => 'int',
        // 'created_notice_mail'               => 'int',
        // 'created_notice_mail_api'           => 'int',
        // 'created_notice_mail_template'      => 'int',
        'product_id'                        => 'int',
        'create_time'                       => 'int',
        'update_time'                       => 'int',
        'price'                             => 'float',
        'cycle'                             => 'string',
        'agentable'                         => 'int',
        'custom_host_name'                  => 'int',
        'custom_host_name_prefix'           => 'string',
        'custom_host_name_string_allow'     => 'string',
        'custom_host_name_string_length'    => 'int',
        'email_notice_setting'              => 'string',
        'sms_notice_setting'                => 'string',
        'renew_rule'                        => 'string',
        'show_base_info'                    => 'int',
        'pay_ontrial'                       => 'string',
        'auto_renew_in_advance'             => 'int',
        'auto_renew_in_advance_num'         => 'string',
        'auto_renew_in_advance_unit'        => 'string',
        'sync_stock'                        => 'int',
        'natural_month_prepaid'             => 'int',
    ];

    public $isAdmin = false;

    /**
     * 时间 2022-5-17
     * @title 商品列表
     * @desc 商品列表
     * @author wyh
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品ID,商品名,描述
     * @param int param.product_group_id - 商品分组ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name,description
     * @param string param.sort - 升/降序 asc,desc
     * @param bool param.exclusive - 是否只返回专属商品
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return int list[].name - 商品名
     * @return int list[].description - 描述
     * @return string list[].pay_type - 付款类型免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].qty - 库存
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return string list[].price - 商品最低价格
     * @return string list[].mode - 代理模式：only_api仅调用接口，sync同步商品
     * @return string list[].client_level_name - 用户等级名称，这个字段在没有用户等级插件时不存在，所以需要注意判断
     * @return int count - 商品总数
     */
    public function productList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','description'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['id'])){
                $query->where('p.product_group_id', $param['id']);
            }
            if($app=='home'){
                $query->where('p.hidden', 0);
                $query->where("pg.hidden",0);
                $query->where("pgf.hidden",0);
//                $ids = configuration('tourist_visible_product_ids');
//                // 游客可见商品
//                if(!empty($ids) && empty(get_client_id())){
//                    $query->whereIn('p.id', explode(',',$ids)?:[]);
//                }
            }
            $query->where('p.product_id',0);
            if (!empty($param['product_ids']) && is_array($param['product_ids'])){
                $query->whereIn('p.id', $param['product_ids']);
            }
        };

        $field = 'p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,p.price,p.cycle,s.module,ss.module module1,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first,pr.name as parent_name,pr.id as parent_id,up.res_module,up.mode,p.pay_ontrial';

        $productObject = $this->alias('p')
            ->leftJoin('product pr','pr.id=p.product_id')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->leftjoin('upstream_product up','up.product_id=p.id');
        hook('product_left_join_append',['product_object'=>$productObject,'field'=>&$field,'param'=>$param,'clientarea'=>$app=='home']);
        $products = $productObject->field($field)
            ->withAttr('description',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode(multi_language_replace($value));
                }
                return $value;
            })
            ->withAttr('res_module',function ($value){
                if (is_null($value)){
                    return "";
                }
                return $value;
            })
            ->withAttr('pay_ontrial',function ($value){
                if (!empty($value)){
                    return json_decode($value,true);
                }
                return [];
            })
            ->withAttr('cycle', function($value){
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
            /*->whereIn('s.module|ss.module',['idcsmart_common','common_cloud','idcsmart_dcim','baidu_cloud','room_box','idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business','idcsmart_cert','idcsmart_email','idcsmart_sms','zjmfapp','dcimapp','mf_cloud','mf_dcim'])*/
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->group('p.id')
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            $products[$key]['price'] = amount_format($value['price']);
            if ($value['parent_id'] && $value['parent_id']>0){
                $products[$key]['id'] = $value['parent_id'];
            }
            if($app=='home'){
                unset($products[$key]['hidden'], $products[$key]['parent_name'], $products[$key]['parent_id']);
            }
        }

        $count = $productObject
            /*->whereIn('s.module|ss.module',['idcsmart_common','common_cloud','idcsmart_dcim','baidu_cloud','room_box','idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business','idcsmart_cert','idcsmart_email','idcsmart_sms','zjmfapp','dcimapp','mf_cloud','mf_dcim'])*/
            ->where($where)
            ->group('p.id')
            ->count(); 

        return ['list'=>$products,'count'=>$count];
    }

    public function productList1($param)
    {
        // 获取当前应用
        $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','description'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['id'])){
                $query->where('p.product_group_id', $param['id']);
            }
            if($app=='home'){
                $query->where('p.hidden', 0);
            }/*else{
                $query->where('pgf.name','<>','应用商店');
            }*/
        };

        $products = $this->alias('p')
            ->field('p.id,p.name,p.description,p.stock_control,p.qty,p.hidden,p.pay_type,
            pg.name as product_group_name_second,pg.id as product_group_id_second,
            pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->withAttr('description',function ($value){
                if (!empty($value)){
                    return htmlspecialchars_decode($value);
                }
                return $value;
            })
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            if($app=='home'){
                unset($products[$key]['stock_control'], $products[$key]['qty'], $products[$key]['hidden'], $products[$key]['product_group_name_second'], $products[$key]['product_group_id_second'], $products[$key]['product_group_name_first'], $products[$key]['product_group_id_first']);
            }
        }

        $count = $this->alias('p')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->where($where)
            ->count();

        return ['list'=>$products,'count'=>$count];
    }

    /**
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @author theworld
     * @version v1
     * @param  string param.module - 模块名称
     * @param  int type - 类型(0=本地模块,1=同步代理)
     * @return array list - 一级分组列表
     * @return int list[].id - 一级分组ID
     * @return string list[].name - 一级分组名称
     * @return array list[].child - 二级分组
     * @return int list[].child[].id - 二级分组ID
     * @return string list[].child[].name - 二级分组名称
     * @return array list[].child[].child - 商品
     * @return int list[].child[].child[].id - 商品ID
     * @return string list[].child[].child[].name - 商品名称
     */
    public function moduleProductList($param)
    {
        $where = function (Query $query) use($param) {
            //$query->where('p.hidden', 0);
            if(!empty($param['module'])){
                if(is_array($param['module'])){
                    $query->whereIn('s.module|ss.module|up.res_module', $param['module']);
                }else{
                    $query->where('s.module|ss.module|up.res_module', $param['module']);
                }
            }
            if(isset($param['type']) && is_numeric($param['type'])){
                if($param['type'] == 0){
                    $query->whereRaw('up.id IS NULL');
                }else if($param['type'] == 1){
                    $query->where('up.id', '>', 0);
                }
            }
            // 先直接排除同步的下游商品
            // $query->whereNotIn('p.id', function($q){
            //     $q->name('upstream_product')->field('product_id')->where('mode', 'sync')->select();
            // });
        };

        $ProductGroupModel = new ProductGroupModel();
        $firstGroup = $ProductGroupModel->productGroupFirstList();
        $firstGroup = $firstGroup['list'];

        $secondGroup = $ProductGroupModel->productGroupSecondList([]);
        $secondGroup = $secondGroup['list'];

        $products = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->leftjoin('upstream_product up', 'p.id=up.product_id')
            ->where($where)
            ->order('p.order','desc')
            ->group('p.id')
            ->select()
            ->toArray();
        $productArr = [];
        foreach ($products as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $productArr[$value['id']]];
            }
        }
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $secondGroupArr[$value['id']]];
            }
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @author wyh
     * @version v1
     * @param int id - 商品ID required
     * @return int id - ID
     * @return string name - 商品名称
     * @return int product_group_id - 所属商品组ID
     * @return string description - 商品描述
     * @return int hidden - 0显示默认，1隐藏
     * @return int stock_control - 库存控制(1:启用)默认0
     * @return int qty - 库存数量(与stock_control有关)
     * @return int pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int auto_setup - 是否自动开通:1是默认,0否
     * @return int type - 关联类型:server,server_group
     * @return int rel_id - 关联ID
     * @return array upgrade - 可升降级商品ID,数组
     * @return int product_id - 父商品ID
     * @return array plugin_custom_fields - 自定义字段{is_link:是否已有子商品,是,置灰}
     * @return int show - 是否将商品展示在会员中心对应模块的列表中:0否1是
     * @return string renew_rule - 续费规则：due到期日，current当前时间
     * @return int auto_renew_in_advance - 自动续费提前开关(0=关闭1=开启)
     * @return int auto_renew_in_advance_num - 自动续费提前时间数
     * @return string auto_renew_in_advance_unit - 自动续费提前时间单位(minute=分钟,hour=小时,day=天)
     * @return string mode - 代理模式：only_api仅调用接口，sync同步商品
     * @return int supplier_id - 供应商ID
     * @return string supplier_name - 供应商名称
     * @return int profit_type - 0表示百分比价格方案，1表示自定义金额
     * @return int show_base_info - 产品列表是否展示基础信息：1是默认，0否
     * @return string module - 商品对应模块
     * @return object plugin_custom_fields - 插件钩子返回的自定义字段{"k1":"v1"}
     * @return object pay_ontrial - 试用配置
     * @return int pay_ontrial.status - 是否开启
     * @return string pay_ontrial.cycle_type - 时长单位(hour/day/month)
     * @return int pay_ontrial.cycle_num - 时长
     * @return string pay_ontrial.client_limit - no不限制/new新用户/host用户必须存在激活中的产品
     * @return string pay_ontrial.account_limit - 账户限制(email绑定邮件/phone绑定手机/certification)
     * @return string pay_ontrial.old_client_exclusive - 老用户专享(商品ID多选，逗号分隔)
     * @return int pay_ontrial.max - 单用户最大试用数量
     * @return string on_demand.min_credit - 购买时用户最低余额
     * @return int on_demand.min_usage_time - 最低使用时长
     * @return string on_demand.min_usage_time_unit - 最低使用时长单位(second=秒,minute=分,hour=小时)
     * @return int on_demand.credit_limit_pay - 允许信用额支付(0=否,1=是)
     */
    public function indexProduct($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $product = $this->field('id,name,product_group_id,description,hidden,stock_control,qty,sync_stock,
        pay_type,auto_setup,type,rel_id,product_id,price,cycle,renew_rule,auto_renew_in_advance,auto_renew_in_advance_num,auto_renew_in_advance_unit,show_base_info,product_group_id,pay_ontrial,natural_month_prepaid')
            ->find($id);

        if (!empty($product->description)){
            $product->description = htmlspecialchars_decode($product->description,ENT_QUOTES);
        }

        if (!empty($product->pay_ontrial)){
            $product->pay_ontrial = json_decode($product->pay_ontrial,true);
        }else{
            $product->pay_ontrial = [];
        }

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();
        $upgrades = $ProductUpgradeProductModel->where('product_id',$id)->select()->toArray();
        $upgradeProducts = array_column($upgrades?:[],'upgrade_product_id');
        if (!empty($product)){
            $product['upgrade'] = $upgradeProducts;
            if($app=='home'){
                $product = ['id' => $product['id'], 'name' => $product['name'], 'pay_type' => $product['pay_type'], 'price' => $product['price'], 'cycle' => $product['cycle'],'product_group_id'=>$product['product_group_id']];
            }
            $ProductGroupModel = new ProductGroupModel();
            $productGroup = $ProductGroupModel->where('id',$product['product_group_id'])->find();
            if (!empty($productGroup)){
                $product['product_group_id_first'] = $productGroup['parent_id']??0;
            }
        }

        # 自定义字段
        $result = hook('product_detail_custom_fields',['id'=>$id]);
        foreach ($result as $item){
            if (is_array($item)){
                foreach ($item as $key=>$value){
                    $customFields[$key] = $value;
                }
            }
        }
        if (!empty($product)){
            $product['plugin_custom_fields'] = $customFields??[];
        }

        $product['show'] = 0;

        $module = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id,s.module,ss.module module2')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where('p.id', $id)
            ->find();

        $module = !empty($module['module']) ? $module['module'] : $module['module2'];
        $product['module'] = $module;
        $menu = MenuModel::where('type', 'home')->where('menu_type', 'module')->where('module', $module)->find();
        if(!empty($menu)){
            $menu['product_id'] = json_decode($menu['product_id'], true);
            if(in_array($id, $menu['product_id'])){
                $product['show'] = 1;
            }
        }

        if($app=='home'){
            $product['customfield'] = [];
            $hookRes = hook('home_product_index', ['id'=>$id]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 200){
                    $product['customfield'] = array_merge($product['customfield'], $v['data'] ?? []);
                }
            }
        }

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$product['id']??0)->find();
        if (!empty($upstreamProduct)){
            $product['mode'] = $upstreamProduct['mode'];
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($upstreamProduct['supplier_id']);
            $product['supplier_id'] = $upstreamProduct['supplier_id'];
            $product['supplier_name'] = $supplier['name']??"";
            $product['profit_type'] = $upstreamProduct['profit_type']??0;
            $product['profit_percent'] = $upstreamProduct['profit_percent']??0;
        }

        // 获取是否支持按需
        $productOnDemand = $this->productOnDemand($product);
        if($productOnDemand['on_demand']){
            $product['on_demand'] = [
                'min_credit' => $productOnDemand['product_on_demand']['min_credit'],
                'min_usage_time' => $productOnDemand['product_on_demand']['min_usage_time'],
                'min_usage_time_unit' => $productOnDemand['product_on_demand']['min_usage_time_unit'],
                'credit_limit_pay' => $productOnDemand['product_on_demand']['credit_limit_pay'],
            ];
        }

        return $product?:(object)[];
    }

    /**
     * 时间 2022-07-22
     * @title 搜索商品
     * @desc 搜索商品
     * @author theworld
     * @version v1
     * @param string keywords - 关键字,搜索范围:商品名称,商品一级分组,商品二级分组
     * @return array list - 商品
     * @return int list[].id - 商品ID 
     * @return string list[].name - 商品名称
     * @return string list[].product_group_name_first - 商品一级分组名称
     * @return string list[].product_group_name_second - 商品二级分组名称
     */
    public function searchProduct($keywords)
    {   
        //全局搜索
        $products = $this->alias('p')
            ->field('p.id,p.name,pgf.name as product_group_name_first,pg.name as product_group_name_second')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->where(function ($query) use($keywords) {
                if(!empty($keywords)){
                    $query->where('p.name|pg.name|pgf.name', 'like', "%{$keywords}%");
                }
            })
            ->order('p.order','desc')
            ->select()
            ->toArray();

        return ['list' => $products];
    }

    /**
     * 时间 2022-5-17
     * @title 新建商品
     * @desc 新建商品
     * @param string param.name 测试商品 商品名称 required
     * @param int param.product_group_id 1 分组ID(只传二级分组ID) required
     * @param string param.renew_rule - 续费规则：due到期日，current当前时间
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.product_id - 商品ID,成功时返回
     *@author wyh
     * @version v1
     */
    public function createProduct($param)
    {
        $productGroupId = intval($param['product_group_id']);

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('please_select_product_group_second')];
        }

        $maxOrder = $this->max('order');

        // 双删
        idcsmart_cache('product:list',null);

        $this->startTrans();

        try{
            $product=$this->create([
                'name' => $param['name']??'',
                'order' => $maxOrder+1,
                'product_group_id' => $productGroupId,
                'description' => '',
                'pay_type' => 'recurring_prepayment',
                'sms_notice_setting' => json_encode([]),
                'email_notice_setting' => json_encode([]),
                'create_time' => time(),
                'renew_rule' => configuration('product_global_renew_rule')?'due':'current',
                'auto_renew_in_advance' => configuration('auto_renew_in_advance'),
                'auto_renew_in_advance_num' => configuration('auto_renew_in_advance_num'),
                'auto_renew_in_advance_unit' => configuration('auto_renew_in_advance_unit'),
            ]);

            if(configuration('self_defined_field_apply_range')==1){
                $SelfDefinedFieldModel = new SelfDefinedFieldModel();
                $selfDefinedField = $SelfDefinedFieldModel->alias('a')
                    ->field('a.*')
                    ->leftjoin('self_defined_field_link b', 'b.self_defined_field_id=a.id')
                    ->where('a.type', 'product_group')
                    ->where('b.product_group_id', $productGroupId)
                    ->where('is_global', 0)
                    ->select()
                    ->toArray();
                if(!empty($selfDefinedField)){
                    foreach ($selfDefinedField as $key => $value) {
                        $value['type'] = 'product';
                        $value['relid'] = $product->id;
                        $value['create_time'] = time();
                        $SelfDefinedFieldModel->create($value, ['type','relid','field_name','is_required','field_type','description','regexpr','field_option','show_order_page','show_order_detail','show_client_host_detail','show_admin_host_detail','show_client_host_list','show_admin_host_list','create_time','explain_content']);
                    }
                }
            }
            if(configuration('custom_host_name_apply_range')==1){
                $CustomHostNameModel = new CustomHostNameModel();
                $customHostName = $CustomHostNameModel->alias('a')
                    ->field('a.*')
                    ->leftjoin('custom_host_name_link b', 'b.custom_host_name_id=a.id')
                    ->where('b.product_group_id', $productGroupId)
                    ->find();
                if(!empty($customHostName)){
                    $this->update([
                        'custom_host_name' => 1,
                        'custom_host_name_prefix' => $customHostName['custom_host_name_prefix'],
                        'custom_host_name_string_allow' => $customHostName['custom_host_name_string_allow'],
                        'custom_host_name_string_length' => $customHostName['custom_host_name_string_length'],
                        'update_time' => time()
                    ], ['id' => $product->id]);
                }
            }

            $ProductNoticeGroupModel = new ProductNoticeGroupModel();
            $ProductNoticeGroupModel->addProductToDefaultProductNoticeGroup($product->id);

            # 记录日志
            active_log(lang('log_admin_create_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        idcsmart_cache('product:list',null);

        hook('after_product_create',['id'=>$product->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('create_success'),'data' => ['product_id' => $product->id]];
    }

    /**
     * 时间 2022-5-17
     * @title 编辑商品
     * @desc 编辑商品
     * @param string name 测试商品 商品名称 required
     * @param int product_group_id 1 分组ID(只传二级分组ID) required
     * @param string description 1 描述 required
     * @param int hidden 1 是否隐藏:1隐藏默认,0显示 required
     * @param int stock_control 1 库存控制(1:启用)默认0 required
     * @param int qty 1 库存数量(与stock_control有关) required
     * @param string pay_type recurring_prepayment 付款类型(免费free，一次onetime，周期先付recurring_prepayment(默认),周期后付recurring_postpaid required
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     * @param array upgrade [1,3,4] 可升降级商品ID,数组
     * @param int product_id 1 父级商品ID
     * @param string price - 商品起售价格
     * @param string renew_rule - 续费规则：due到期日，current当前时间
     * @param int show_base_info - 产品列表是否展示基础信息：1是默认，0否
     * @param int auto_renew_in_advance - 自动续费提前开关(0=关闭1=开启)
     * @param int auto_renew_in_advance_num - 自动续费提前时间数
     * @param string auto_renew_in_advance_unit - 自动续费提前时间单位(minute=分钟,hour=小时,day=天)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     *@author wyh
     * @version v1
     */
    public function updateProduct($param)
    {
        $id = intval($param['id']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',intval($param['product_group_id']))
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('product_group_is_not_exist')];
        }

        // $PluginModel = new PluginModel();

        // $SmsTemplateModel = new SmsTemplateModel();

        // $EmailTemplateModel = new EmailTemplateModel();
        // # 开通中短信通知接口验证
        // if (!empty($param['creating_notice_sms_api'])){
        //     $smsApiCreating = $PluginModel->where('id',intval($param['creating_notice_sms_api']))
        //         ->where('status',1)
        //         ->where('module','sms')
        //         ->find();
        //     if (empty($smsApiCreating)){
        //         return ['status'=>400,'msg'=>lang('product_creating_notice_sms_cannot_use')];
        //     }
        // }else{
        //     $param['creating_notice_sms_api'] = 0;
        // }
        // # 已开通短信通知接口验证
        // if (!empty($param['created_notice_sms_api'])){
        //     $smsApiCreated = $PluginModel->where('id',intval($param['created_notice_sms_api']))
        //         ->where('status',1)
        //         ->where('module','sms')
        //         ->find();
        //     if (empty($smsApiCreated)){
        //         return ['status'=>400,'msg'=>lang('product_created_notice_sms_cannot_use')];
        //     }
        // }else{
        //     $param['created_notice_sms_api'] = 0;
        // }
        // # 开通中短信通知模板验证
        // if (!empty($param['creating_notice_sms_api_template'])){
        //     if (empty($smsApiCreating)){
        //         return ['status'=>400,'msg'=>lang('product_creating_notice_sms_cannot_use')];
        //     }else{
        //         $creatingTemplate = $SmsTemplateModel->where('id',$param['creating_notice_sms_api_template'])
        //             ->where('sms_name',$smsApiCreating['name'])
        //             ->find();
        //         if (empty($creatingTemplate)){
        //             return ['status'=>400,'msg'=>lang('product_creating_notice_sms_api_template_is_not_exist')];
        //         }
        //     }
        // }else{
        //     $param['creating_notice_sms_api_template'] = 0;
        // }
        // # 已开通短信通知模板验证
        // if (!empty($param['created_notice_sms_api_template'])){
        //     if (empty($smsApiCreated)){
        //         return ['status'=>400,'msg'=>lang('product_created_notice_sms_cannot_use')];
        //     }else{
        //         $createdTemplate = $SmsTemplateModel->where('id',$param['created_notice_sms_api_template'])
        //             ->where('sms_name',$smsApiCreated['name'])
        //             ->find();
        //         if (empty($createdTemplate)){
        //             return ['status'=>400,'msg'=>lang('product_created_notice_sms_api_template_is_not_exist')];
        //         }
        //     }
        // }else{
        //     $param['created_notice_sms_api_template'] = 0;
        // }
        // # 开通中通知邮件接口验证
        // if (!empty($param['creating_notice_mail_api'])){
        //     $mailApiCreating = $PluginModel->where('id',intval($param['creating_notice_mail_api']))
        //         ->where('status',1)
        //         ->where('module','mail')
        //         ->find();
        //     if (empty($mailApiCreating)){
        //         return ['status'=>400,'msg'=>lang('product_creating_notice_mail_cannot_use')];
        //     }
        // }else{
        //     $param['creating_notice_mail_api'] = 0;
        // }
        // # 开通中通知邮件模板验证
        // if (!empty($param['creating_notice_mail_template'])){
        //     $creatingEmailTemplate = $EmailTemplateModel->where('id',$param['creating_notice_mail_template'])
        //         ->find();
        //     if (empty($creatingEmailTemplate)){
        //         return ['status'=>400,'msg'=>lang('product_creating_notice_mail_template_is_not_exist')];
        //     }
        // }else{
        //     $param['creating_notice_mail_template'] = 0;
        // }
        // # 已开通通知邮件接口验证
        // if (!empty($param['created_notice_mail_api'])){
        //     $mailApiCreated = $PluginModel->where('id',intval($param['created_notice_mail_api']))
        //         ->where('status',1)
        //         ->where('module','mail')
        //         ->find();
        //     if (empty($mailApiCreated)){
        //         return ['status'=>400,'msg'=>lang('product_created_notice_mail_cannot_use')];
        //     }
        // }else{
        //     $param['created_notice_mail_api'] = 0;
        // }
        // # 已开通通知邮件模板验证
        // if (!empty($param['created_notice_mail_template'])){
        //     $createdEmailTemplate = $EmailTemplateModel->where('id',$param['created_notice_mail_template'])
        //         ->find();
        //     if (empty($createdEmailTemplate)){
        //         return ['status'=>400,'msg'=>lang('product_created_notice_mail_template_is_not_exist')];
        //     }
        // }else{
        //     $param['created_notice_mail_template'] = 0;
        // }
        # 验证升降级商品ID
        if (isset($param['upgrade']) && !is_array($param['upgrade'])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        $upgradeIds = $param['upgrade']??[];
        if (!empty($upgradeIds)){
            foreach ($upgradeIds as $upgradeId){
                $upgradeProduct = $this->find($upgradeId);
                if (empty($upgradeProduct)){
                    return ['status'=>400,'msg'=>lang('product_upgrade_product_is_not_exist')];
                }
                if ($upgradeId == $id){
                    return ['status'=>400,'msg'=>lang('product_upgrade_product_cannot_self')];
                }
            }
        }
        $param['product_id'] = $param['product_id']??0;
        if(!empty($param['product_id'])){
            $parentProduct = $this->find($param['product_id']);
            if (empty($parentProduct)){
                return ['status'=>400,'msg'=>lang('parent_product_is_not_exist')];
            }
        }

        # 如果商品已开启自然月预付费，验证付款类型
        if ($product['natural_month_prepaid'] == 1){
            $allowedPayTypes = ['recurring_prepayment', 'recurring_prepayment_on_demand'];
            if (!in_array($param['pay_type'], $allowedPayTypes)){
                return ['status'=>400,'msg'=>lang('natural_month_prepaid_product_pay_type_limit')];
            }
        }
        

        # 日志描述
        if (isset($product['pay_ontrial'])){
            unset($product['pay_ontrial']);
        }
        if (isset($param['pay_ontrial'])){
            unset($param['pay_ontrial']);
        }
        $logDescription = log_description($product->toArray(),$param,'product');

        $ProductUpgradeProductModel = new ProductUpgradeProductModel();
        $old = $ProductUpgradeProductModel->where('product_id',$id)->select()->toArray();
        if (count(array_diff($upgradeIds,array_column($old,'upgrade_product_id')))>0 || count(array_diff(array_column($old,'upgrade_product_id'),$upgradeIds))>0){
            $logDescription .= ',' . lang('log_admin_update_product_upgrade_product',['{old}'=>implode(',',array_column($old,'upgrade_product_id')),'{new}'=>implode(',',$upgradeIds)]);
        }

        $moduleSetPrice = true;
        $price = $product['price'];
        if($param['pay_type'] == 'free'){
            $price = 0;
            $moduleSetPrice = false;
        }else if(isset($param['price'])){
            if(is_numeric($param['price'])){
                $price = $param['price'];
                $moduleSetPrice = false;
            }
        }

        // 双删
        idcsmart_cache('product:list',null);

        $this->startTrans();

        try{
            $this->update([
                'name' => $param['name']??'',
                'product_group_id' => $param['product_group_id'],
                'description' => isset($param['description'])?htmlspecialchars($param['description']):'',
                'hidden' => $param['hidden'],
                'stock_control' => $param['stock_control'],
                'qty' => $param['qty'],
                // 'creating_notice_sms' => $param['creating_notice_sms'],
                // 'creating_notice_sms_api' => $param['creating_notice_sms_api'],
                // 'creating_notice_sms_api_template' => $param['creating_notice_sms_api_template'],
                // 'created_notice_sms' => $param['created_notice_sms'],
                // 'created_notice_sms_api' => $param['created_notice_sms_api'],
                // 'created_notice_sms_api_template' => $param['created_notice_sms_api_template'],
                // 'creating_notice_mail' => $param['creating_notice_mail'],
                // 'creating_notice_mail_api' => $param['creating_notice_mail_api'],
                // 'creating_notice_mail_template' => $param['creating_notice_mail_template'],
                // 'created_notice_mail_template' => $param['created_notice_mail_template'],
                // 'created_notice_mail' => $param['created_notice_mail'],
                // 'created_notice_mail_api' => $param['created_notice_mail_api'],
                'pay_type' => $param['pay_type'],
                'product_id' => $param['product_id'],
                'update_time' => time(),
                'price' => $price,
                'renew_rule' => $param['renew_rule']??"due",
                'agentable' => $param['pay_type'] == 'on_demand' ? 0 : $product['agentable'],
                //'show_base_info' => $param['show_base_info']??1,
                'auto_renew_in_advance' => $param['auto_renew_in_advance']??0,
                'auto_renew_in_advance_num' => $param['auto_renew_in_advance_num']??10,
                'auto_renew_in_advance_unit' => $param['auto_renew_in_advance_unit']??'minute',
                'sync_stock' => $param['sync_stock']??1,
            ],['id'=>$id]);

            # 升级关联
            $ProductUpgradeProductModel->where('product_id',$id)->delete();
            if (!empty($upgradeIds)){
                $insert = [];
                foreach ($upgradeIds as $upgradeId){
                    $insert[] = [
                        'product_id' => $id,
                        'upgrade_product_id' => $upgradeId
                    ];
                }
                $ProductUpgradeProductModel->saveAll($insert);
            }

            # 记录日志
            if (!empty($logDescription)){
                active_log(lang('log_admin_update_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#','{description}'=>$logDescription]),'product',$product->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        $upstreamProduct = UpstreamProductModel::where('product_id', $id)->where('mode','only_api')->find();
        if(empty($upstreamProduct)){
            $ModuleLogic = new ModuleLogic();
            $priceCycle = $ModuleLogic->getPriceCycle($product->id);
            $this->setPriceCycle($priceCycle['product'], $moduleSetPrice ? $priceCycle['price'] : NULL, $priceCycle['cycle']);
        }
        idcsmart_cache('product:list',null);

        hook('after_product_edit',['id'=>$product->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-6-10
     * @title 编辑商品接口
     * @desc 编辑商品接口
     * @author wyh
     * @version v1
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateServer($param)
    {
        $id = intval($param['id']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }
        # 验证接口
        if ($param['type'] == 'server_group'){
            $ServerGroupModel = new ServerGroupModel();
            $ServerGroup = $ServerGroupModel->find(intval($param['rel_id']));
            if (empty($ServerGroup)){
                return ['status'=>400,'msg'=>lang('server_group_not_found')];
            }
        }else{
            $ServerModel = new ServerModel();
            $Server = $ServerModel->find(intval($param['rel_id']));
            if (empty($Server)){
                return ['status'=>400,'msg'=>lang('server_is_not_exist')];
            }
        }

        # 日志描述
        $logDescription = log_description($product->toArray(),$param,'product');

        $this->startTrans();

        try{
            $this->update([
                'auto_setup' => $param['auto_setup'],
                'type' => $param['type'],
                'rel_id' => $param['rel_id'],
                'update_time' => time(),
                'show_base_info' => $param['show_base_info']??1,
            ],['id'=>$id]);

            # 记录日志
            if (!empty($logDescription)){
                active_log(lang('log_admin_update_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#','{description}'=>$logDescription]),'product',$product->id);
            }
            if($param['show']==1){
                # 创建导航
                $MenuModel = new MenuModel();
                $MenuModel->createHomeModuleMenu($id);
            }else{
                # 创建导航
                $MenuModel = new MenuModel();
                $MenuModel->deleteHomeModuleMenu($id);
            }

            $param['oldProduct'] = $product;
            $this->durationPresets($param);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-5-17
     * @title 删除商品
     * @desc 删除商品
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteProduct($id)
    {
        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $HostModel = new HostModel();
        $hostCount = $HostModel->where('product_id',$id)->where('is_delete', 0)->count();
        if ($hostCount>0){
            return ['status'=>400,'msg'=>lang('product_has_host')];
        }

        idcsmart_cache('product:list',null);

        $module = $product->getModule();

        $this->startTrans();
        try{
            # 删除商品
            $product->delete();
            # 删除配置以及子项
            $ConfigOptionModel = new ConfigOptionModel();
            $configoption = $ConfigOptionModel->field('id')->where('product_id',$id)->select()->toArray();
            if (!empty($configoption)){
                $ConfigOptionSubModel = new ConfigOptionSubModel();
                $ConfigOptionSubModel->where('config_option_id',array_column($configoption,'id'))->delete();
                $ConfigOptionModel->where('product_id',$id)->delete();
            }
            # 删除商品关联的升降级ID
            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $ProductUpgradeProductModel->where('product_id',$id)->delete();

            # 删除其他商品升降级至此商品的ID
            $ProductUpgradeProductModel->where('upgrade_product_id',$id)->delete();

            # 删除导航
            $MenuModel = new MenuModel();
            $MenuModel->deleteHomeModuleMenu($id);

            UpstreamProductModel::where('product_id', $id)->delete();

            # 删除自定义字段
            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $SelfDefinedFieldModel->withDelete('product', $id);

            # 移除通知组
            $ProductNoticeGroupModel = new ProductNoticeGroupModel();
            $ProductNoticeGroupModel->afterProductDelete($id);

            # 删除区域组
            $MfCloudDataCenterMapGroupLinkModel = new MfCloudDataCenterMapGroupLinkModel();
            $MfCloudDataCenterMapGroupLinkModel->where('product_id', $id)->delete();

            # 记录日志
            active_log(lang('log_admin_delete_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$id.'#'.$product['name'].'#']),'product',$id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail') . $e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        hook('after_product_delete', ['id'=>$id, 'module'=>$module]);

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 批量删除商品
     * @desc 批量删除商品
     * @author theworld
     * @version v1
     * @param array id - 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteProduct($param)
    {
        $id = $param['id']??[];

        $product = $this->whereIn('id', $id)->select()->toArray();
        
        if (empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if(count($product)!=count($id)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $hosts = HostModel::where('is_delete', 0)->whereIn('product_id', $id)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('product_has_host')];
        }
        
        idcsmart_cache('product:list',null);

        $this->startTrans();
        try {
            $ConfigOptionModel = new ConfigOptionModel();
            $ConfigOptionSubModel = new ConfigOptionSubModel();
            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $MenuModel = new MenuModel();
            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $ProductNoticeGroupModel = new ProductNoticeGroupModel();

            foreach ($product as $key => $value) {
                $ProductModel = ProductModel::find($value['id']);
                $product[$key]['module'] = $ProductModel->getModule();

                # 删除商品
                $ProductModel->delete();
                # 删除配置以及子项
                $configoption = $ConfigOptionModel->field('id')->where('product_id',$value['id'])->select()->toArray();
                if (!empty($configoption)){
                    $ConfigOptionSubModel->where('config_option_id',array_column($configoption,'id'))->delete();
                    $ConfigOptionModel->where('product_id',$value['id'])->delete();
                }
                # 删除商品关联的升降级ID
                $ProductUpgradeProductModel->where('product_id',$value['id'])->delete();

                # 删除其他商品升降级至此商品的ID
                $ProductUpgradeProductModel->where('upgrade_product_id',$value['id'])->delete();

                # 删除导航
                $MenuModel->deleteHomeModuleMenu($value['id']);

                UpstreamProductModel::where('product_id', $value['id'])->delete();

                # 删除自定义字段
                $SelfDefinedFieldModel->withDelete('product', $value['id']);

                # 移除通知组
                $ProductNoticeGroupModel->afterProductDelete($value['id']);

                # 记录日志
                active_log(lang('log_admin_delete_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$value['id'].'#'.$value['name'].'#']),'product',$value['id']);
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        foreach ($product as $value) {
            hook('after_product_delete', ['id'=>$value['id'], 'module'=>$value['module']]);
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-5-17
     * @title 隐藏/显示商品
     * @desc 隐藏/显示商品
     * @url /admin/v1/product/:id/:hidden
     * @method  put
     * @author wyh
     * @version v1
     * @param int param.id 1 商品ID required
     * @param int param.hidden 1 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function hiddenProduct($param)
    {
        $product = $this->find(intval($param['id']));
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $hidden = intval($param['hidden']);

        if ($product['hidden'] == $hidden){
            return ['status'=>400,'msg'=>lang('cannot_repeat_opreate')];
        }

        idcsmart_cache('product:list',null);

        $this->startTrans();
        try{
            $this->update([
                'hidden' => $hidden,
                'update_time' => time(),
            ],['id'=>intval($param['id'])]);

            # 记录日志
            if ($hidden == 1){
                active_log(lang('log_admin_hidden_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#']),'product',$product->id);
            }else{
                active_log(lang('log_admin_show_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#']),'product',$product->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        idcsmart_cache('product:list',null);

        return ['status'=>200,'msg'=>lang('success_message')];
    }

    /**
     * 时间 2022-5-18
     * @title 商品拖动排序
     * @desc 商品拖动排序
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @param int pre_product_id 1 移动后前一个商品ID(没有则传0) required
     * @param int product_group_id 1 移动后的商品组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderProduct($param)
    {
        $id = $param['id'];

        $preProductId = intval($param['pre_product_id']);

        $productGroupId = intval($param['product_group_id']);

        # 基础验证
        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        if ($preProductId){
            $preProduct = $this->find($preProductId);
            if (empty($preProduct)){
                return ['status'=>400,'msg'=>lang('product_is_not_exist')];
            }

            if (!empty($preProduct) && $preProduct->product_group_id != $productGroupId){
                return ['status'=>400,'msg'=>lang('product_is_not_in_product_group')];
            }
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('product_group_is_not_exist')];
        }

        idcsmart_cache('product:list',null);

        # 排序处理
        $this->startTrans();
        try{
            if ($preProductId){
                $preOrder = $preProduct['order'];

                if (isset($param['backward']) && $param['backward']){
                    $products = $this->where('product_group_id',$productGroupId)
                        ->where('order','>=',$preOrder)
                        ->where('id','<>',$id)
                        ->select();
                    foreach ($products as $v){
                        $v->save([
                            'order' => $v['order']+1,
                            'update_time' => time()
                        ]);
                    }

                    $product->save([
                        'order' => $preOrder,
                        'product_group_id' => $productGroupId,
                        'update_time' => time()
                    ]);
                }else{
                    $products = $this->where('product_group_id',$productGroupId)
                        ->where('order','<=',$preOrder)
                        ->select();
                    foreach ($products as $v){
                        $v->save([
                            'order' => $v['order']-1,
                            'update_time' => time()
                        ]);
                    }

                    $product->save([
                        'order' => $preOrder+1,
                        'product_group_id' => $productGroupId,
                        'update_time' => time()
                    ]);
                }


            }else{
                $minOrder = $this->where('product_group_id',$productGroupId)->min('order');

                $product->save([
                    'order' => $minOrder-1,
                    'product_group_id' => $productGroupId,
                    'update_time' => time()
                ]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang('move_fail') . ':' . $e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        return ['status'=>200,'msg'=>lang('move_success')];
    }

    /**
     * 时间 2022-5-31
     * @title 获取商品关联的升降级商品
     * @desc 获取商品关联的升降级商品
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return string list[].name - 商品名
     */
    public function upgradeProduct($id)
    {
        $products = $this->alias('p')
            ->field('p.id,p.name')
            ->leftJoin('product_upgrade_product pup','p.id=pup.upgrade_product_id')
            ->where('pup.product_id',$id)
            ->select()->toArray();
        return ['status'=>200,'msg'=>lang('success_message'),'data'=>['list'=>$products]];
    }

    /**
     * 时间 2022-05-27
     * @title 获取当前商品关联模块类型(需要先实例化)
     * @desc 获取当前商品关联模块类型
     * @author hh
     * @version v1
     * @param array param.domain - 域名
     * @param int relId 当前关联ID 关联ID
     * @param string type 当前关联类型 关联类型(server=接口,server_group=接口分组)
     * @return  string
     */
    public function getModule($param=[], $relId = 0, $type = 'server')
    {
        // 自定义获取模块
        $ProductGroupModel = new ProductGroupModel();
        $productGroupType = $ProductGroupModel->where('id',$this->getAttr("product_group_id"))->value("type");
        if ($productGroupType=="domain" && $customGetModule = hook_one("custom_get_module",['domain'=>$param['domain']??""])){
            return $customGetModule;
        }

        if(empty($relId)){
            $relId = $this->getAttr('rel_id');
            $type = $this->getAttr('type');
        }
        $module = '';
        if(empty($relId)){
            return $module;
        }
        if($type == 'server_group'){
            $server = ServerModel::where('server_group_id', $relId)->find();
            $module = $server['module'] ?? '';
        }else if($type == 'server'){
            $server = ServerModel::find($relId);
            $module = $server['module'] ?? '';
        }
        return $module;
    }

    /**
     * 时间 2022-05-31
     * @title 选择接口获取配置
     * @desc 选择接口获取配置
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @param   string param.type - 关联类型(server=接口,server_group=接口分组) require
     * @param   int param.rel_id - 关联ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.content - 模块输出内容
     */
    public function moduleServerConfigOption($param)
    {
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $content = '';
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['product_id'])->find();

        if(empty($upstreamProduct)){
            // 商品
            // $oldModule = $ProductModel->getModule();
            $newModule = $this->getModule([],$param['rel_id'], $param['type']);

            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->serverConfigOption($newModule, $ProductModel);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content'       => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-30
     * @title 前台商品配置页面
     * @desc 前台商品配置页面模块输出
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @param   bool param.flag - 是否获取隐藏隐藏商品的模块内容(true=是,false=否)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.product_name - 商品名称
     * @return  string data.content - 模块输出内容
     */
    public function moduleClientConfigOption($param)
    {
        $id = (int)$param['id'];
        $ProductModel = $this->find($id);
        // flag用于后台创建订单,可以显示隐藏商品的模块内容
        if (isset($param['flag']) && $param['flag']=='true'){

        }else{
            if(empty($ProductModel) || $ProductModel['hidden'] == 1){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
        }

        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])
            ->where('mode','only_api')
            ->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->clientProductConfigOption($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->clientProductConfigOption($ProductModel);
        }

        $result = [
            'status'=> 200,
            'msg'   => lang('success_message'),
            'data'  => [
                'product_name'  => $ProductModel['name'],
                'content'       => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @author theworld
     * @version v1
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  object param.customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @param  int param.qty - 数量 required
     * @param  object param.self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function settle($param,$isAdmin=false)
    {
        $product = $this->find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        // 非后台
        if (!$isAdmin){
            if($product['hidden']==1){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
        }
        if(!empty($product['product_id'])){
            if(!isset($param['config_options']['host_id'])){
                return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
            }
            $host = HostModel::find($param['config_options']['host_id']);
            if($host['product_id']!=$product['product_id']){
                return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
            }
        }
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
        $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
            'product_id'          => $product['id'], 
            'self_defined_field'  => $param['self_defined_field'] ?? [],
        ]);
        if($checkSelfDefinedField['status'] != 200){
            return $checkSelfDefinedField;
        }
        $param['self_defined_field'] = $checkSelfDefinedField['data'];

        $appendOrderItem = [];
        $param['config_options'] = $param['config_options'] ?? [];
        // 后台
        if ($isAdmin){
            $clientId = $param['client_id']??0;
        }else{
            $clientId = get_client_id();

            $OrderModel = new OrderModel();
            if($OrderModel->haveUnpaidOnDemandOrder($clientId)){
                return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_order_cannot_do_this') ];
            }
        }

        $certification = check_certification($clientId);

        $result = hook('before_order_create', ['client_id'=>$clientId, 'param' => $param]);

        foreach ($result as $value){
            if (isset($value['status']) && $value['status']==400){
                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message'),'data'=>$value['data']??[]];
            }
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();
        $ModuleLogic = new ModuleLogic();
        if($upstreamProduct && $upstreamProduct['mode']=='only_api'){
            // 非后台
            if (!$isAdmin){
                if($upstreamProduct['certification']==1 && !$certification){
                    return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product'),'data'=>['certification'=>0]];
                }
            }
            $param['config_options']['customfield'] = $param['config_options']['self_defined_field'] = $SelfDefinedFieldModel->toUpstreamId([
                'product_id'          => $product['id'],
                'self_defined_field'  => $param['self_defined_field'],
            ]);

            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty'],'',true);
        }else{
            if (!empty($upstreamProduct) && !$isAdmin){
                if($upstreamProduct['certification']==1 && !$certification){
                    return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product'),'data'=>['certification'=>0]];
                }
            }
            $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty'],'buy',0,$clientId);
        }

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }
        $appendOrderItem = $result['data']['order_item'] ?? [];
        // wyh 20240226 上下游商品，价格已算上数量
        $amount = $upstreamProduct && $upstreamProduct['mode'] == 'only_api' ?$result['data']['price']:$result['data']['price']*$param['qty'];
        if(isset($result['data']['sub_host'])){
            foreach ($result['data']['sub_host'] as $subHost){
                if ($upstreamProduct && $upstreamProduct['mode'] == 'only_api'){
                    if ($upstreamProduct['profit_type']==1){ // 固定利润不处理
                        $result['data']['price'] = bcadd($result['data']['price'],$subHost['price'],2);
                    }else{
                        $profitAndBasePercent = bcadd(1,$upstreamProduct['profit_percent']/100,4);
                        $result['data']['price'] = bcadd($result['data']['price'],$subHost['price']*$profitAndBasePercent,2);
                        $subHost['price'] = $subHost['price']*$profitAndBasePercent;
                    }

                }
                $amount += $subHost['price']*($upstreamProduct && $upstreamProduct['mode'] == 'only_api' ?1:$param['qty']);
            }
        }

        $param['price'] = $upstreamProduct && $upstreamProduct['mode'] == 'only_api' ?bcdiv($result['data']['price'],$param['qty'],2):$result['data']['price'];
        // wyh 20250321 子产品
        if($upstreamProduct && $upstreamProduct['mode'] == 'only_api' && isset($result['data']['sub_host'])){
            $param['price'] = bcsub($param['price'],$result['data']['sub_host'][0]['price']??0,2);
        }


        $param['discount'] = $result['data']['discount'] ?? 0;
        $param['renew_price'] = $result['data']['renew_price'] ?? $param['price'];
        $param['billing_cycle'] = $result['data']['billing_cycle'];
        $param['duration'] = $result['data']['duration'];
        $param['description'] = $result['data']['description'];
        $param['config_options'] = $param['config_options'] ?? [];
        $param['base_price'] = $result['data']['base_price']??$param['price'];
        $param['ontrial'] = $result['data']['ontrial']??false;
        $param['sub_host'] = $result['data']['sub_host'] ?? [];
        $param['base_renew_price'] = $result['data']['base_renew_price'] ?? $param['renew_price'];

        if($upstreamProduct){
            if ($upstreamProduct['mode']=='only_api'){
                $param['profit'] = $result['data']['profit'] ?? 0;
            }else{
                $param['profit'] = 0;
            }
        }
        $ProductOnDemandModel = new ProductOnDemandModel();
        // 产品费用类型
        $param['host_billing_cycle'] = $product['pay_type'] == 'recurring_prepayment_on_demand' ? ($result['data']['host_billing_cycle'] ?? 'recurring_prepayment') : $product['pay_type'];
        // 按需时获取出账周期,验证余额
        if($param['host_billing_cycle'] == 'on_demand'){
            $credit = ClientModel::where('id', $clientId)->value('credit');
            $productOnDemand = $ProductOnDemandModel->productOnDemandIndex($product['id']);
            if(!empty($productOnDemand['min_credit']) && $productOnDemand['min_credit'] > $credit){
                return ['status'=>400, 'msg'=>lang('product_on_demand_buy_need_min_credit', ['{product}'=>$product['name'], '{credit}'=>$productOnDemand['min_credit'] ]) ];
            }
            $param['keep_time_price'] = $result['data']['keep_time_price'] ?? '0.0000';
            $param['on_demand_flow_price'] = $result['data']['on_demand_flow_price'] ?? '0.0000';
            $param['on_demand_billing_cycle_unit'] = $productOnDemand['billing_cycle_unit'];
            $param['on_demand_billing_cycle_day'] = $productOnDemand['billing_cycle_day'];
            $param['on_demand_billing_cycle_point'] = $productOnDemand['billing_cycle_point'];
            // 按需没有基础价
            $param['base_price'] = 0;
        }else{
            // 设定默认值
            $param['keep_time_price'] = '0.0000';
            $param['on_demand_flow_price'] = '0.0000';
            $param['on_demand_billing_cycle_unit'] = 'hour';
            $param['on_demand_billing_cycle_day'] = 1;
            $param['on_demand_billing_cycle_point'] = '00:00';
        }
        // 代理商品不支持按需购买
        if($upstreamProduct && $param['host_billing_cycle'] == 'on_demand'){
            return ['status'=>400, 'msg'=>lang('upstream_product_cannot_on_demand_buy', ['{product}'=>$product['name']]) ];
        }
        $param['discount_renew_price'] = 0;
        $param['renew_use_current_client_level'] = 0;
        if(isset($result['data']['discount_renew_price']) && is_numeric($result['data']['discount_renew_price']) ){
            $param['discount_renew_price'] = $result['data']['discount_renew_price'];
            $param['renew_use_current_client_level'] = 1;
        }

        if(empty($param['description'])){
            if($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment'){
                $param['description'] = $product['name'].'('.date("Y-m-d H:i:s").'-'.date("Y-m-d H:i:s",time()+$param['duration']).')';
            }else{
                $param['description'] = $product['name'];
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
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
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
            $hostIds = [];
            $product = $this->find($param['product_id']);
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    throw new \Exception(lang('product_inventory_shortage'));
                }
                $this->where('id', $param['product_id'])->dec('qty', $param['qty'])->update();
            }
            if($product['type']=='server_group'){
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

            $upstreamProduct = UpstreamProductModel::where('product_id', $param['product_id'])->find();
            for ($i=1; $i<=$param['qty']; $i++) {
                // 计算到期时间
                $dueTime = !in_array($param['host_billing_cycle'], ['on_demand','onetime']) ? $time : 0;
                // 如果是自然月预付费，使用模块返回的到期时间
                if(!empty($result['data']['is_natural_month_prepaid']) && !empty($result['data']['due_time'])){
                    $dueTime = $result['data']['due_time'];
                }
                
                $host = HostModel::create([
                    'client_id' => $clientId,
                    'order_id' => $order->id,
                    'product_id' => $param['product_id'],
                    'server_id' => $serverId,
                    'name' => generate_host_name($param['product_id']),
                    'status' => 'Unpaid',
                    'first_payment_amount' => $param['price'],
                    'renew_amount' => in_array($param['host_billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $param['renew_price'] : 0,
                    'billing_cycle' => $param['host_billing_cycle'],
                    'billing_cycle_name' => $param['billing_cycle'],
                    'billing_cycle_time' => $param['duration'],
                    'active_time' => $time,
                    'due_time' => $dueTime,
                    'create_time' => $time,
                    'base_price' => $param['base_price'],
                    'ratio_renew' => self::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
                    'base_renew_amount' => in_array($param['host_billing_cycle'], ['recurring_postpaid','recurring_prepayment','on_demand']) ? $param['base_renew_price'] : 0,
                    'base_config_options' => json_encode($param['config_options']),
                    'is_ontrial' => $param['ontrial'],
                    'first_payment_ontrial' => $param['ontrial'],
                    'keep_time_price' => $param['keep_time_price'],
                    'on_demand_flow_price' => $param['on_demand_flow_price'],
                    'on_demand_billing_cycle_unit' => $param['on_demand_billing_cycle_unit'],
                    'on_demand_billing_cycle_day' => $param['on_demand_billing_cycle_day'],
                    'on_demand_billing_cycle_point' => $param['on_demand_billing_cycle_point'],
                    'discount_renew_price' => $param['discount_renew_price'],
                    'renew_use_current_client_level' => $param['renew_use_current_client_level'],
                ]);

                if($upstreamProduct){
                    UpstreamHostModel::create([
                        'supplier_id' => $upstreamProduct['supplier_id'],
                        'host_id' => $host->id,
                        'upstream_configoption' => json_encode($param['config_options']),
                        'create_time' => $time
                    ]);
                    UpstreamOrderModel::create([
                        'supplier_id' => $upstreamProduct['supplier_id'],
                        'order_id' => $order->id,
                        'host_id' => $host->id,
                        'amount' => $param['price'],
                        'profit' => $param['profit'],
                        'create_time' => $time
                    ]);
                    if ($upstreamProduct['mode']=='only_api'){
                        $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                        $ResModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                    }else{
                        $param['config_options']['customfield'] = $param['customfield']??[];
                        $ModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                    }
                }else{
                    $param['config_options']['customfield'] = $param['customfield']??[];
                    $ModuleLogic->afterSettle($product, $host->id, $param['config_options']);
                }
                $hostIds[] = $host->id;
                //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
                if (in_array($host['billing_cycle'],['onetime','free'])){
                    $desDueTime = '∞';
                }else{
                    // 如果是自然月预付费，使用精确的到期时间
                    if(!empty($result['data']['is_natural_month_prepaid']) && !empty($result['data']['due_time']) ){
                        $desDueTime = date('Y/m/d', $host['due_time']);
                    }else{
                        $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
                    }
                }

                $billingCycleName = $host['billing_cycle_name'];
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'name' => $billingCycleName,
                    ],
                ]);
                if(isset($multiLanguage['name'])){
                    $billingCycleName = $multiLanguage['name'];
                }

                $des = lang('order_description_append',['{product_name}'=>$product->name,'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycleName,'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
                if (is_array($param['description'])){
                    $param['description'] = implode("\n",$param['description']);
                }

                $orderItem[] = [
                    'order_id' => $order->id,
                    'client_id' => $clientId,
                    'host_id' => $host->id,
                    'product_id' => $param['product_id'],
                    'type' => 'host',
                    'rel_id' => $host->id,
                    'amount' => bcadd($param['price'], $param['discount'], 2),
                    'description' => $param['description'] . "\n" . $des,
                    'create_time' => $time,
                ];

                foreach($appendOrderItem as $v){
                    $v['order_id'] = $order->id;
                    $v['client_id'] = $clientId;
                    $v['host_id'] = $host->id;
                    $v['product_id'] = $param['product_id'];
                    $v['create_time'] = $time;
                    $orderItem[] = $v;
                }

                // 保存自定义字段
                $selfDefinedFieldValue = [];
                foreach($param['self_defined_field'] as $k=>$v){
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
                foreach ($param['sub_host'] as $subHost){
                    $host = HostModel::create([
                        'client_id' => $clientId,
                        'order_id' => $order->id,
                        'product_id' => $param['product_id'],
                        'server_id' => $serverId,
                        'name' => generate_host_name($param['product_id']),
                        'status' => 'Unpaid',
                        'first_payment_amount' => $subHost['price'],
                        'renew_amount' => ($param['host_billing_cycle']=='recurring_postpaid' || $param['host_billing_cycle']=='recurring_prepayment') ? $subHost['renew_price'] : 0,
                        'billing_cycle' => $param['host_billing_cycle'],
                        'billing_cycle_name' => $subHost['billing_cycle'],
                        'billing_cycle_time' => $subHost['duration'],
                        'active_time' => $time,
                        'due_time' => !in_array($param['host_billing_cycle'], ['onetime','on_demand']) ? $time : 0,
                        'create_time' => $time,
                        'base_price' => $subHost['base_price'],
                        'ratio_renew' => self::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
                        'base_renew_amount' => ($param['host_billing_cycle']=='recurring_postpaid' || $param['host_billing_cycle']=='recurring_prepayment') ? $subHost['renew_price'] : 0,
                        'base_config_options' => json_encode($subHost['config_options']),
                        'is_ontrial' => $param['ontrial'],
                        'first_payment_ontrial' => $param['ontrial'],
                        'is_sub' => 1,
                    ]);

                    if($upstreamProduct){
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
                            'profit' => $subHost['profit'] ?? 0,
                            'create_time' => $time
                        ]);
                        if ($upstreamProduct['mode']=='only_api'){
                            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                            $subHost['config_options']['parent_host_id'] = $parentHostId;
                            $ResModuleLogic->afterSettle($product, $host->id, $subHost['config_options']);
                        }else{
                            $subHost['config_options']['customfield'] = $param['customfield']??[];
                            $subHost['config_options']['parent_host_id'] = $parentHostId;
                            $ModuleLogic->afterSettle($product, $host->id, $subHost['config_options']);
                        }
                    }else{
                        $subHost['config_options']['customfield'] = $param['customfield']??[];
                        $subHost['config_options']['parent_host_id'] = $parentHostId;
                        $ModuleLogic->afterSettle($product, $host->id, $subHost['config_options']);
                    }
                    $hostIds[] = $host->id;
                    //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
                    if (in_array($host['billing_cycle'],['onetime','free'])){
                        $desDueTime = '∞';
                    }else{
                        $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
                    }

                    $billingCycleName = $host['billing_cycle_name'];
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $billingCycleName,
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $billingCycleName = $multiLanguage['name'];
                    }

                    $des = lang('order_description_append',['{product_name}'=>$product->name,'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycleName,'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
                    if (is_array($subHost['description'])){
                        $subHost['description'] = implode("\n",$subHost['description']);
                    }

                    $orderItem[] = [
                        'order_id' => $order->id,
                        'client_id' => $clientId,
                        'host_id' => $host->id,
                        'product_id' => $param['product_id'],
                        'type' => 'host',
                        'rel_id' => $host->id,
                        'amount' => bcadd($subHost['price'], $subHost['discount'] ?? 0, 2),
                        'description' => $subHost['description'] . "\n" . $des,
                        'create_time' => $time,
                    ];

                    if (isset($subHost['order_item']) && !empty($subHost['order_item'])){
                        foreach($subHost['order_item'] as $v){
                            $v['order_id'] = $order->id;
                            $v['client_id'] = $clientId;
                            $v['host_id'] = $host->id;
                            $v['product_id'] = $param['product_id'];
                            $v['create_time'] = $time;
                            $orderItem[] = $v;
                        }
                    }

                    // 保存自定义字段
                    $selfDefinedFieldValue = [];
                    foreach($param['self_defined_field'] as $k=>$v){
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

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            $param['customfield'] = $param['customfield']??[];
            $param['customfield']['is_admin'] = $isAdmin;
            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']]);

            update_upstream_order_profit($order->id);

            $OrderModel = new OrderModel();
            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $OrderModel->where('id',$order->id)->value('amount');

            if($amount<=0){
                $OrderModel = new OrderModel();
                $OrderModel->processPaidOrder($order->id);
            }

            # 记录日志
            active_log(lang('submit_order', ['{client}'=>'#'.$clientId.request()->client_name, '{order}'=>$order->id, '{product}'=>'product#'.$product['id'].'#'.$product['name'].'#']), 'order', $order->id);

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
            if(!empty($result['data']['is_natural_month_prepaid'])){
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

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id, 'amount' => $amount]];
    }

    /**
     * 时间 2022-05-31
     * @title 批量结算商品
     * @desc 批量结算商品
     * @author theworld
     * @version v1
     * @param  array param.products - 商品 required
     * @param  int param.products[].product_id - 商品ID required
     * @param  object param.products[].config_options - 自定义配置
     * @param  object param.products[].customfield - 自定义参数
     * @param  int param.products[].qty - 数量 required
     * @param  object param.products[].self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @param  object param.customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
//    public function batchSettle($param,$isAdmin=false)
//    {
//        $customfield = $param['customfield'] ?? [];
//
//        $amount = 0;
//        $settleData = [];
//        $appendOrderItem = [];
//
//        $clientId = get_client_id();
//
//        $certification = check_certification($clientId);
//        $ModuleLogic = new ModuleLogic();
//        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
//        $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
//        foreach ($param['products'] as $key => $value) {
//            $product = ProductModel::where('hidden', 0)->find($value['product_id']);
//            if(empty($product)){
//                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
//            }
//            if(!empty($product['product_id'])){
//                return ['status'=>400, 'msg'=>lang('cannot_only_buy_son_product')];
//            }
//            $checkSelfDefinedField = $SelfDefinedFieldModel->checkAndFilter([
//                'product_id'          => $product['id'],
//                'self_defined_field'  => $value['self_defined_field'] ?? [],
//            ]);
//            if($checkSelfDefinedField['status'] != 200){
//                return $checkSelfDefinedField;
//            }
//            $value['self_defined_field'] = $checkSelfDefinedField['data'];
//            $value['config_options'] = $value['config_options'] ?? [];
//
//            $self_defined_field = $value['config_options']['customfield']??[];
//
//            // wyh 20230719 加入自定义字段
//            $value['config_options']['customfield'] = $customfield;
//
//            $value['config_options']['self_defined_field'] = $self_defined_field;
//
//            $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();
//
//            if($upstreamProduct && $upstreamProduct['mode']=='only_api'){
//                if($upstreamProduct['certification']==1 && !$certification){
//                    return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product')];
//                }
//                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
//                $result = $ResModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty'],'cal_price',true);
//            }else{
//                if (!empty($upstreamProduct)){
//                    if($upstreamProduct['certification']==1 && !$certification){
//                        return ['status'=>400, 'msg'=>lang('certification_uncertified_cannot_buy_product')];
//                    }
//                }
//                $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty'],'buy',$key);
//            }
//            if($result['status']!=200){
//                return $result;
//            }
//            if($product['pay_type']=='free'){
//                $result['data']['price'] = 0;
//            }
//            // wyh 20240226 上下游商品，价格已算上数量
//            $result['data']['price'] = $upstreamProduct?bcdiv($result['data']['price'],$value['qty'],2):$result['data']['price'];
//            //$amount = $upstreamProduct?bcadd($amount,$result['data']['price'],2):bcadd($amount,$result['data']['price']*$value['qty'],2);
//
//            $amount += $result['data']['price']*$value['qty'];
//            $settleData[$key] = $value;
//            // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
//            $settleData[$key]['order_item'] = $result['data']['order_item'] ?? [];
//            $settleData[$key]['price'] = $result['data']['price'];
//            $settleData[$key]['discount'] = $result['data']['discount'] ?? 0;
//            $settleData[$key]['renew_price'] = $result['data']['renew_price'] ?? $settleData[$key]['price'];
//            $settleData[$key]['billing_cycle'] = $result['data']['billing_cycle'];
//            $settleData[$key]['duration'] = $result['data']['duration'];
//            $settleData[$key]['description'] = $result['data']['description'];
//            $settleData[$key]['base_price'] = $result['data']['base_price']??$result['data']['price'];
//            if($upstreamProduct){
//                $settleData[$key]['profit'] = $result['data']['profit']??0;
//            }
//        }
//        if(empty($settleData)){
//            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
//        }
//
//
//        $result = hook('before_order_create', ['client_id'=>$clientId, 'cart' => $settleData]);
//
//        foreach ($result as $value){
//            if (isset($value['status']) && $value['status']==400){
//                return ['status'=>400, 'msg'=>$value['msg'] ?? lang('fail_message')];
//            }
//        }
//
//        $this->startTrans();
//        try {
//            // 创建订单
//            /*$gateway = gateway_list();
//            $gateway = $gateway['list'][0]??[];*/
//
//            $time = time();
//            $order = OrderModel::create([
//                'client_id' => $clientId,
//                'type' => 'new',
//                'status' => $amount>0 ? 'Unpaid' :'Paid',
//                'amount' => $amount,
//                'credit' => 0,
//                'amount_unpaid' => $amount,
//                //'gateway' => $gateway['name'] ?? '',
//                //'gateway_name' => $gateway['title'] ?? '',
//                'pay_time' => $amount>0 ? 0 : $time,
//                'create_time' => $time
//            ]);
//
//            // 创建产品
//            $orderItem = [];
//            $productLog = [];
//            $hostIds = [];
//            foreach ($settleData as $key => $value) {
//                $product = ProductModel::find($value['product_id']);
//                if($product['stock_control']==1){
//                    if($product['qty']<$value['qty']){
//                        throw new \Exception(lang('product_inventory_shortage'));
//                    }
//                    ProductModel::where('id', $value['product_id'])->dec('qty', $value['qty'])->update();
//                }
//                if(empty($value['description'])){
//                    if($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment'){
//                        $value['description'] = $product['name'].'('.date("Y-m-d H:i:s").'-'.date("Y-m-d H:i:s",time()+$value['duration']).')';
//                    }else{
//                        $value['description'] = $product['name'];
//                    }
//                }
//                $productLog[] = 'product#'.$product['id'].'#'.$product['name'].'#';
//
//                if($product['type']=='server_group'){
//                    // 域名相关
//                    $ProductGroupModel = new ProductGroupModel();
//                    $productGroupType = $ProductGroupModel->where('id',$product['product_group_id'])->value("type");
//                    if ($productGroupType=="domain"){
//                        $customGetModul = hook_one("custom_get_module",['domain'=>$value['config_options']['domain']??""]);
//                        $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->where("module",$customGetModul)->find();
//                        $serverId = $server['id'] ?? 0;
//                    }else{
//                        if($product['rel_id'] > 0){
//                            $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
//                            $serverId = $server['id'] ?? 0;
//                        }else{
//                            $serverId = 0;
//                        }
//                    }
//                }else{
//                    $serverId = $product['rel_id'];
//                }
//                // 这里不改，两种代理模式都存一份
//                $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();
//                for ($i=1; $i<=$value['qty']; $i++) {
//                    if (request()->is_api){
//                        $downstreamHostId = intval($param['downstream_host_id'] ?? 0);
//                        if(!empty($downstreamHostId)){
//                            $downstreamInfo = json_encode(['url' => $param['downstream_url']??'', 'token'=>$param['downstream_token']??'', 'api'=>request()->api_id,'type'=>$param['downstream_system_type']??""]);
//                        }
//                    }
//
//                    $name = generate_host_name($value['product_id']);
//
//                    $host = HostModel::create([
//                        'client_id' => $clientId,
//                        'order_id' => $order->id,
//                        'product_id' => $value['product_id'],
//                        'server_id' => $serverId,
//                        'name' => $name,
//                        'status' => 'Unpaid',
//                        'first_payment_amount' => $value['price'],
//                        'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $value['renew_price'] : 0,
//                        'billing_cycle' => $product['pay_type'],
//                        'billing_cycle_name' => $value['billing_cycle'],
//                        'billing_cycle_time' => $value['duration'],
//                        'active_time' => $time,
//                        'due_time' => $product['pay_type']!='onetime' ? $time : 0,
//                        'create_time' => $time,
//                        'downstream_info' => $downstreamInfo ?? '',
//                        'downstream_host_id' => $downstreamHostId ?? 0,
//                        'base_price' => $value['base_price'],
//                        'ratio_renew' => ProductModel::isRenewByRatio(['product_group_id'=>$product['product_group_id'],'server_id'=>$serverId]),
//                        'base_renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $value['renew_price'] : 0,
//                    ]);
//
//                    hook('after_host_create',['id'=>$host->id, 'param'=>$param,'customfield'=>$customfield]);
//
//                    $hostIds[] = $host->id;
//
//                    if($upstreamProduct){
//                        // wyh 20231211 改
//                        $value['config_options']['configoption']['host'] = $name;
//
//                        UpstreamHostModel::create([
//                            'supplier_id' => $upstreamProduct['supplier_id'],
//                            'host_id' => $host->id,
//                            'upstream_configoption' => json_encode($value['config_options']),
//                            'create_time' => $time
//                        ]);
//                        UpstreamOrderModel::create([
//                            'supplier_id' => $upstreamProduct['supplier_id'],
//                            'order_id' => $order->id,
//                            'host_id' => $host->id,
//                            'amount' => $value['price'],
//                            'profit' => $value['profit']??0,
//                            'create_time' => $time
//                        ]);
//                        if ($upstreamProduct['mode']=='only_api'){
//                            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
//                            $result = $ResModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
//                        }else{
//                            $value['config_options']['customfield'] = $value['customfield'] ?? [];
//                            $ModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
//                        }
//                    }else{
//                        $value['config_options']['customfield'] = $value['customfield'] ?? [];
//                        $ModuleLogic->afterSettle($product, $host->id, $value['config_options'],$customfield, $key);
//                    }
//
//                    // 产品和对应自定义字段
//                    $customfield['host_customfield'][] = ['id'=>$host->id, 'customfield' => $value['customfield'] ?? []];
//
//                    //$des = $product['name'] . '(' .$host['name']. '),购买时长:'.$host['billing_cycle_name'] .'(' . date('Y/m/d H',$host['active_time']) . '-'. date('Y/m/d H',$host['active_time']) .')';
//                    if (in_array($host['billing_cycle'],['onetime','free'])){
//                        $desDueTime = '∞';
//                    }else{
//                        $desDueTime = date('Y/m/d',time() + intval($host['billing_cycle_time']));
//                        //$desDueTime = date('Y/m/d',$host['active_time']);
//                    }
//                    $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
//                    if (is_array($value['description'])){
//                        $value['description'] = implode("\n",$value['description']);
//                    }
//
//                    $orderItem[] = [
//                        'order_id' => $order->id,
//                        'client_id' => $clientId,
//                        'host_id' => $host->id,
//                        'product_id' => $value['product_id'],
//                        'type' => 'host',
//                        'rel_id' => $host->id,
//                        'amount' => bcadd($value['price'], $value['discount']),
//                        'description' => $value['description'] . "\n" . $des,
//                        'create_time' => $time,
//                    ];
//
//                    // wyh 20240428 修改bug，追加子项会被最后一个产品子项覆盖
//                    if (!empty($value['order_item'])){
//                        foreach($value['order_item'] as $v){
//                            $v['order_id'] = $order->id;
//                            $v['client_id'] = $clientId;
//                            $v['host_id'] = $host->id;
//                            $v['product_id'] = $value['product_id'];
//                            $v['create_time'] = $time;
//                            $orderItem[] = $v;
//                        }
//                    }
//
//                    // 保存自定义字段
//                    $selfDefinedFieldValue = [];
//                    foreach($value['self_defined_field'] as $k=>$v){
//                        $selfDefinedFieldValue[] = [
//                            'self_defined_field_id' => $k,
//                            'relid'                 => $host->id,
//                            'value'                 => (string)$v,
//                            'order_id'              => $order->id,
//                            'create_time'           => $time,
//                        ];
//                    }
//                    $SelfDefinedFieldValueModel->insertAll($selfDefinedFieldValue);
//                }
//            }
//
//            // 创建订单子项
//            $OrderItemModel = new OrderItemModel();
//            $OrderItemModel->saveAll($orderItem);
//
//            # 记录日志
//            active_log(lang('submit_order', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#', '{order}'=>$order->id, '{product}'=>implode(',', $productLog)]), 'order', $order->id);
//
//            add_task([
//                'type' => 'email',
//                'description' => lang('order_create_send_mail'),
//                'task_data' => [
//                    'name'=>'order_create',//发送动作名称
//                    'order_id'=>$order->id,//订单ID
//                ],
//            ]);
//            add_task([
//                'type' => 'sms',
//                'description' => lang('order_create_send_sms'),
//                'task_data' => [
//                    'name'=>'order_create',//发送动作名称
//                    'order_id'=>$order->id,//订单ID
//                ],
//            ]);
//
//
//            hook('after_order_create',['id'=>$order->id,'customfield'=>$customfield]);
//
//            update_upstream_order_profit($order->id);
//
//            $OrderModel = new OrderModel();
//            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
//            $amount = $OrderModel->where('id',$order->id)->value('amount');
//
//            if($amount<=0){
//                $OrderModel->processPaidOrder($order->id);
//            }
//
//            // wyh 20240402 新增 支付后跳转地址
//            $domain = configuration('website_url');
//            if (count($hostIds)>1){
//                $returnUrl = "{$domain}/finance.htm";
//            }else{
//                if (isset($hostIds[0]) && !empty($hostIds[0])){
//                    $returnUrl = "{$domain}/productdetail.htm?id=".$hostIds[0];
//                }else{
//                    $returnUrl = "{$domain}/finance.htm";
//                }
//            }
//            $order->save([
//                'return_url' => $returnUrl,
//            ]);
//
//            $this->commit();
//        } catch (\Exception $e) {
//            // 回滚事务
//            $this->rollback();
//            return ['status' => 400, 'msg' => $e->getMessage()];
//        }
//
//        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id, 'amount' => $amount,'host_ids'=>$hostIds]];
//    }

    /**
     * 时间 2022-05-28
     * @title 商品价格计算
     * @desc 商品价格计算
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID required
     * @param   int param.qty - 数量 required
     * @param   array config_options - 模块自定义配置参数,格式{"configoption":{1:1,2:[2]},"cycle":2,"promo_code":"Af13S1ACj","event_promotion":12,"qty":1}
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格(订购原价，计算数量)
     * @return  string data.renew_price - 续费价格(计算折扣，不计算数量)
     * @return  string data.billing_cycle - 周期名称
     * @return  int data.duration - 周期时长(秒)
     * @return  string data.description - 订单子项描述
     * @return  string data.base_price - 基础价格
     * @return  float data.price_total - 折扣后金额（各种优惠折扣处理后的金额，没有就是price价格），并且计算数量
     * @return  float data.renew_price_total - 续费总价(不计算折扣,计算数量)
     * @return  float data.price_promo_code_discount - 优惠码折扣金额（当使用优惠码，且有效时，才返回此字段）
     * @return  float data.price_client_level_discount - 客户等级折扣金额（当客户等级有效时，才返回此字段）
     * @return  float data.price_event_promotion_discount - 活动促销折扣金额（当活动促销有效时，才返回此字段）
     * @return  bool data.event_promotion_exclude_client_level - 活动促销是否和用户等级互斥(当活动促销有效时，才返回此字段)
     * @return  bool data.event_promotion_exclude_client_level_renew - 续费金额活动促销是否和用户等级互斥(当活动促销有效时，才返回此字段)
     */
    public function productCalculatePrice($param)
    {
        $ProductModel = $this->find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        /*if(app('http')->getName() == 'home' && $ProductModel['hidden'] == 1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }*/
        $param['config_options'] = $param['config_options'] ?? [];
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])
            ->where('mode','only_api')
            ->find();

        $qty = $param['qty']??1;

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($ProductModel, $param['config_options'], $qty, 'cal_price');
            // var_dump($result);
            if ($result['status']==200){
                if (!empty($result['data']['sub_host'])){
                    foreach ($result['data']['sub_host'] as &$v){
                        if ($upstreamProduct['profit_type']==1){ // 固定利润不处理
                            $result['data']['price'] = bcadd($result['data']['price'],$v['price'],2);
                        }else{
                            $profitAndBasePercent = bcadd(1,$upstreamProduct['profit_percent']/100,4);
                            $result['data']['price'] = bcadd($result['data']['price'],$v['price']*$profitAndBasePercent,2);
                        }
                    }
                }
            }
        }else{
            $ModuleLogic = new ModuleLogic();
            $param['config_options']['settle_qty'] = $qty;
            $result = $ModuleLogic->cartCalculatePrice($ProductModel, $param['config_options'], 1, 'cal_price');
        }

        if(isset($result['data']['profit'])) unset($result['data']['profit']);
        if(isset($result['data']['order_item'])) unset($result['data']['order_item']);

        // wyh 20240220 后端处理价格数据返回显示
        if ($result['status']==200){
            $paramRequest = request()->param();
            $isDownstream = isset($paramRequest['is_downstream']) && $paramRequest['is_downstream']==1;
            if ($isDownstream){
                $price = $result['data']['price']??0;
            }else{ // 返会给下游时，不做处理
                // price计算过用户折扣了
                if(isset($result['data']['discount'])){
                    $price = bcadd($result['data']['price']??0, $result['data']['discount']??0,2);
                }else{
                    $price = $result['data']['price'] ?? 0;
                }
                // 这里续费先变为续费原价
                if(isset($result['data']['base_renew_price'])){
                    $result['data']['renew_price'] = $result['data']['base_renew_price'];
                }else if(isset($result['data']['renew_price_client_level_discount'])){
                    $result['data']['renew_price'] = bcadd($result['data']['renew_price'], $result['data']['renew_price_client_level_discount'], 2);
                }
            }
            $discountOrderPrice = $result['data']['discount_order_price'] ?? $price; // 单个商品订购可应用用户等级部分
            $discountRenewPrice = $result['data']['discount_renew_price'] ?? $result['data']['renew_price']; // 单个商品续费可应用用户等级部分

            $hostBillingCycle = $ProductModel['pay_type'] == 'recurring_prepayment_on_demand' ? ($result['data']['host_billing_cycle'] ?? 'recurring_prepayment') : $ProductModel['pay_type'];
            
            // 上下游已处理数量
            if ($upstreamProduct){
                $result['data']['price'] = $price;
            }else{
                $result['data']['price'] = bcmul($price,$qty,2);
            }

            $result['data']['price_total'] = $result['data']['price'];
            $baseRenewPrice = $result['data']['renew_price'];
            // 续费基础价计算数量价格
            $result['data']['renew_price_total'] = $result['data']['base_renew_price'] ?? $result['data']['renew_price'];
            $scale = 2;
            if($hostBillingCycle == 'on_demand'){
                $scale = 4;
            }
            $result['data']['renew_price_total'] = bcmul($result['data']['renew_price_total'], $qty, $scale);

//            if(isset($result['data']['sub_host'])){
//                foreach ($result['data']['sub_host'] as $subHost)
//                {
//                    $result['data']['price_total'] = bcadd($result['data']['price_total'],$subHost['price'],2);
//                    $baseRenewPrice = bcadd($baseRenewPrice,$subHost['renew_price'],2);
//                }
//            }

            // 1、代理老财务的上下游时，不做任何处理，ResModuleLogic会处理
            if ($isDownstream){

            }
            else{
                // 活动是否和用户等级互斥
                $eventPromotionExcludeClientLevel = false;
                // 续费是否和用户等级互斥
                $eventPromotionExcludeClientLevelRenew = false;
                // hh 20250205 优惠码是否和用户等级互斥
                $promoCodeExcludeClientLevel = false;
                // 优惠码续费是否和用户等级互斥
                $promoCodeExcludeClientLevelRenew = false;
                
                // hh 20250202 检查模块是否已经计算了用户等级折扣
                // 如果模块返回了discount并且不是代理，说明已经在模块中计算并扣除了用户等级折扣
                $moduleHasClientLevelDiscount = isset($result['data']['discount']) && !$upstreamProduct;

                // 1、优惠码
                if (isset($param['config_options']['promo_code']) && !empty($param['config_options']['promo_code'])){
                    // 优惠码是否计算续费折扣
                    $promoCodeRenewEnable = false;
                    $hookPromoCodeResultsOrgins = hook('apply_promo_code', [
                            'host_id'=>0,
                            'price'=>bcdiv($result['data']['price'],$qty,2),
                            'scene'=>'new',
                            'qty'=>$qty,
                            'duration'=>$result['data']['duration']??0,
                            'product_id'=>$param['product_id'],
                            'promo_code'=>$param['config_options']['promo_code']
                        ]
                    );
                    foreach ($hookPromoCodeResultsOrgins as $hookPromoCodeResultsOrgin){
                        if ($hookPromoCodeResultsOrgin['status']==200){
                            $promocodeDiscount = $hookPromoCodeResultsOrgin['data']['discount']??0;
                            $result['data']['price_promo_code_discount'] = $promocodeDiscount;
                            $result['data']['price_total'] = bcsub($result['data']['price_total'],$result['data']['price_promo_code_discount'],2);
                            
                            // hh 20250205 检查优惠码是否开启"不与用户等级同享"
                            $excludeWithClientLevel = $hookPromoCodeResultsOrgin['data']['exclude_with_client_level'] ?? 0;
                            $loop = $hookPromoCodeResultsOrgin['data']['loop'] ?? 0;
                            $renew = $hookPromoCodeResultsOrgin['data']['renew'] ?? 0;
                            $singleUserOnce = $hookPromoCodeResultsOrgin['data']['single_user_once'] ?? 0;
                            $promoCodeRenewEnable = $renew == 1 && $loop == 1;
                            
                            if($excludeWithClientLevel == 1){
                                $promoCodeExcludeClientLevel = true;
                                // 如果有"单用户一次"限制，只有第一个商品排斥用户等级
                                if($singleUserOnce == 1 && $qty > 1){
                                    // 第一个用优惠码，剩余的用用户等级
                                    $promoCodeExcludeClientLevel = 'partial'; // 部分排斥
                                }
                                
                                // 如果开启续费优惠且开启续费循环，续费也排斥用户等级
                                if($promoCodeRenewEnable && $hostBillingCycle != 'on_demand'){
                                    $promoCodeExcludeClientLevelRenew = true;
                                }
                            }
                        }
                    }
                    // 续费单独处理,非按需才处理
                    if($hostBillingCycle != 'on_demand' && $promoCodeRenewEnable){
                        $hookPromoCodeResultsOrginsRenew = hook('apply_promo_code', [
                            'host_id'=>0,
                            'price'=>$result['data']['renew_price'],
                            'scene'=>'new',
                            'qty'=>1,
                            'duration'=>$result['data']['duration']??0,
                            'product_id'=>$param['product_id'],
                            'promo_code'=>$param['config_options']['promo_code']
                        ]);
                        foreach ($hookPromoCodeResultsOrginsRenew as $hookPromoCodeResultsOrginRenew){
                            if ($hookPromoCodeResultsOrginRenew['status']==200){
                                //$result['data']['price_promo_code_discount'] = $hookPromoCodeResultsOrginRenew['data']['discount']??0;
                                $baseRenewPrice = bcsub($baseRenewPrice,$hookPromoCodeResultsOrginRenew['data']['discount']??0,2);
                            }
                        }
                    }
                }

                // 2、活动
                if (isset($param['config_options']['event_promotion']) && !empty($param['config_options']['event_promotion'])){
                    // 活动续费折扣
                    $eventPromotionRenewPercent = 0;
                    // hh 20250202 现在price已经是未打折的原价，直接使用
                    $hookEventPromotionResultsOrgins = hook("event_promotion_by_amount",[
                        'event_promotion' => $param['config_options']['event_promotion'],
                        'product_id' => $param['product_id'],
                        'qty' => $qty,
                        'amount' => bcdiv($result['data']['price'], $qty, 2),
                        'billing_cycle_time' => $result['data']['duration']??0,
                    ]);
                    foreach ($hookEventPromotionResultsOrgins as $hookEventPromotionResultsOrgin){
                        if ($hookEventPromotionResultsOrgin['status']==200){
                            // 活动促销已计算数量
                            $eventPromotionDiscount = $hookEventPromotionResultsOrgin['data']['discount']??0;
                            $result['data']['price_event_promotion_discount'] = $eventPromotionDiscount;
                            $result['data']['price_total'] = bcsub($result['data']['price_total'],$result['data']['price_event_promotion_discount'],2);
                            
                            // hh 20250130 检查活动是否开启"不与用户等级同享"
                            $excludeWithClientLevel = $hookEventPromotionResultsOrgin['data']['exclude_with_client_level'] ?? 0;
                            $singleUserOnce = $hookEventPromotionResultsOrgin['data']['single_user_once'] ?? 0;
                            $eventPromotionRenewPercent = $hookEventPromotionResultsOrgin['data']['renew_percent'] ?? 0;
                            
                            if($excludeWithClientLevel == 1){
                                $eventPromotionExcludeClientLevel = true;
                                // 如果有"单用户一次"限制，只有第一个商品排斥用户等级
                                if($singleUserOnce == 1 && $qty > 1){
                                    // 第一个用活动，剩余的用用户等级
                                    $eventPromotionExcludeClientLevel = 'partial'; // 部分排斥
                                }
                            }

                            // 续费单独处理
                            if($hostBillingCycle != 'on_demand'){
                                if(!empty($eventPromotionRenewPercent)){
                                    $eventPromotionExcludeClientLevelRenew = true;
                                    // 计算活动续费折扣
                                    $eventPromotionRenewDiscount = bcdiv($result['data']['renew_price']*$eventPromotionRenewPercent, 100, 2);
                                    $baseRenewPrice = bcsub($baseRenewPrice, $eventPromotionRenewDiscount, 2);
                                }
                            }
                        }
                    }
                }

                // 3、客户等级，考虑到DCIM配置设置客户等级，需要模块里面返回折扣金额
                // hh 20250130 处理与活动促销的互斥逻辑
                // hh 20250205 处理与优惠码的互斥逻辑
                // hh 20250202 统一处理：无论模块是否计算了用户等级，现在price都是原价
                // 使用discount_order_price（可折扣金额）来计算用户等级折扣
                
                // 优先判断优惠码互斥，其次判断活动促销互斥
                $finalExcludeClientLevel = false;
                $finalExcludeClientLevelRenew = false;
                
                if($promoCodeExcludeClientLevel === true || $promoCodeExcludeClientLevel === 'partial'){
                    $finalExcludeClientLevel = $promoCodeExcludeClientLevel;
                }else if($eventPromotionExcludeClientLevel === true || $eventPromotionExcludeClientLevel === 'partial'){
                    $finalExcludeClientLevel = $eventPromotionExcludeClientLevel;
                }
                
                if($promoCodeExcludeClientLevelRenew === true){
                    $finalExcludeClientLevelRenew = true;
                }else if($eventPromotionExcludeClientLevelRenew === true){
                    $finalExcludeClientLevelRenew = true;
                }
                
                if($finalExcludeClientLevel === true){
                    // 完全排斥用户等级折扣
                    if($moduleHasClientLevelDiscount){
                        // 模块已经计算了，清零相关字段
                        $result['data']['discount'] = 0;
                        $result['data']['price_client_level_discount'] = 0;

                        if($finalExcludeClientLevelRenew){
                            $result['data']['renew_price_client_level_discount'] = 0;
                        }else{
                            $baseRenewPrice = bcsub($baseRenewPrice, $result['data']['renew_price_client_level_discount'] ?? 0, 2);
                        }
                    }else{
                        if(!$finalExcludeClientLevelRenew){
                            $hookClientLevelResultsOrginsRenew = hook("client_discount_by_amount",[
                                'client_id'=>get_client_id(),
                                'product_id'=>$param['product_id'],
                                'amount'=>$discountRenewPrice
                            ]);
                            foreach ($hookClientLevelResultsOrginsRenew as $hookClientLevelResultsOrginRenew){
                                if ($hookClientLevelResultsOrginRenew['status']==200){
                                    $result['data']['renew_price_client_level_discount'] = $hookClientLevelResultsOrginRenew['data']['discount'] ?? 0;
                                    $baseRenewPrice = bcsub($baseRenewPrice,$hookClientLevelResultsOrginRenew['data']['discount']??0,2);
                                }
                            }
                        }
                    }
                }else if($finalExcludeClientLevel === 'partial'){
                    // 只互斥一部分，比如活动单户一次，其他数量依然可以应用用户等级
                    if($moduleHasClientLevelDiscount && !isset($result['data']['discount_order_price'])){
                        // 这个判断用来兼容没有返回可折扣部分的模块
                        $clientLevelDiscount = $result['data']['discount'];
                        $clientLevelQty = $qty - 1;
                        $clientLevelDiscountTotal = bcmul($clientLevelDiscount, $clientLevelQty, 2);
                        
                        $result['data']['discount'] = $clientLevelDiscount;
                        $result['data']['price_client_level_discount'] = $clientLevelDiscountTotal;
                        $result['data']['price_total'] = bcsub($result['data']['price_total'],$clientLevelDiscountTotal,2);
                    }else{
                        $hookClientLevelResultsOrgins = hook("client_discount_by_amount",[
                            'client_id'=>get_client_id(),
                            'product_id'=>$param['product_id'],
                            'amount'=>$discountOrderPrice
                        ]);
                        foreach ($hookClientLevelResultsOrgins as $hookClientLevelResultsOrgin){
                            if ($hookClientLevelResultsOrgin['status']==200){
                                $clientLevelDiscount = $hookClientLevelResultsOrgin['data']['discount']??0;

                                $clientLevelQty = $qty - 1;
                                $clientLevelDiscountTotal = bcmul($clientLevelDiscount, $clientLevelQty, 2);
                                
                                $result['data']['discount'] = $clientLevelDiscount;
                                $result['data']['price_client_level_discount'] = $clientLevelDiscountTotal;
                                $result['data']['price_total'] = bcsub($result['data']['price_total'],$clientLevelDiscountTotal,2);
                            }
                        }
                    }
                    if(!$finalExcludeClientLevelRenew){
                        $hookClientLevelResultsOrginsRenew = hook("client_discount_by_amount",[
                            'client_id'=>get_client_id(),
                            'product_id'=>$param['product_id'],
                            'amount'=>$discountRenewPrice
                        ]);
                        foreach ($hookClientLevelResultsOrginsRenew as $hookClientLevelResultsOrginRenew){
                            if ($hookClientLevelResultsOrginRenew['status']==200){
                                $result['data']['renew_price_client_level_discount'] = $hookClientLevelResultsOrginRenew['data']['discount'] ?? 0;
                                $baseRenewPrice = bcsub($baseRenewPrice,$hookClientLevelResultsOrginRenew['data']['discount']??0,2);
                            }
                        }
                    }
                }else{
                    // 使用用户等级
                    if($moduleHasClientLevelDiscount && !isset($result['data']['discount_order_price'])){
                        // 这个判断用来兼容没有返回可折扣部分的模块
                        $clientLevelDiscount = $result['data']['discount'];
                        $clientLevelDiscountTotal = bcmul($clientLevelDiscount, $qty, 2);

                        $result['data']['discount'] = $clientLevelDiscount;
                        $result['data']['price_client_level_discount'] = $clientLevelDiscountTotal;
                        $result['data']['price_total'] = bcsub($result['data']['price_total'],$clientLevelDiscountTotal,2);
                    }else{
                        // 通过钩子获取折扣
                        $hookClientLevelResultsOrgins = hook("client_discount_by_amount",[
                            'client_id'=>get_client_id(),
                            'product_id'=>$param['product_id'],
                            'amount'=>$discountOrderPrice
                        ]);
                        foreach ($hookClientLevelResultsOrgins as $hookClientLevelResultsOrgin){
                            if ($hookClientLevelResultsOrgin['status']==200){
                                $clientLevelDiscount = $hookClientLevelResultsOrgin['data']['discount']??0;
                                $clientLevelDiscountTotal = bcmul($clientLevelDiscount, $qty, 2);

                                $result['data']['discount'] = $clientLevelDiscount;
                                $result['data']['price_client_level_discount'] = $clientLevelDiscountTotal;
                                $result['data']['price_total'] = bcsub($result['data']['price_total'],$clientLevelDiscountTotal,2);
                            }
                        }
                    }
                    // wyh 20240521 续费单独处理
                    $hookClientLevelResultsOrginsRenew = hook("client_discount_by_amount",[
                        'client_id'=>get_client_id(),
                        'product_id'=>$param['product_id'],
                        'amount'=>$discountRenewPrice
                    ]);
                    foreach ($hookClientLevelResultsOrginsRenew as $hookClientLevelResultsOrginRenew){
                        if ($hookClientLevelResultsOrginRenew['status']==200){
                            $result['data']['renew_price_client_level_discount'] = $hookClientLevelResultsOrginRenew['data']['discount'] ?? 0;
                            $baseRenewPrice = bcsub($baseRenewPrice,$hookClientLevelResultsOrginRenew['data']['discount']??0,2);
                        }
                    }
                }
                // hh 20250205 独立返回优惠码和活动促销的互斥状态
                $result['data']['promo_code_exclude_client_level'] = $promoCodeExcludeClientLevel === false ? false : true;
                $result['data']['promo_code_exclude_client_level_renew'] = $promoCodeExcludeClientLevelRenew;
                $result['data']['event_promotion_exclude_client_level'] = $eventPromotionExcludeClientLevel === false ? false : true;
                $result['data']['event_promotion_exclude_client_level_renew'] = $eventPromotionExcludeClientLevelRenew;
            }

            $result['data']['renew_price'] = $baseRenewPrice;
        }

        return $result;
    }

    /**
     * 时间 2022-07-25
     * @title 获取商品所有配置项
     * @desc 获取商品所有配置项
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data[].name - 配置名称
     * @return  string data[].field - 配置标识
     * @return  string data[].type - 配置形式(dropdown=下拉,目前只有这个)
     * @return  string data[].option[].name - 选项名称
     * @return  mixed data[].option[].value - 选项值
     */
    public function productAllConfigOption($id)
    {
        $ProductModel = $this->find($id ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->allConfigOption($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->allConfigOption($ProductModel);
        }
        return $result;
    }

    /**
     * 时间 2022-10-11
     * @title 获取商品库存
     * @desc 获取商品库存
     * @author theworld
     * @version v1
     * @param int id - 商品ID
     * @return int id - ID
     * @return int stock_control - 库存控制0:关闭1:启用
     * @return int qty - 库存数量
     */
    public function productStock($id)
    {
        $product = $this->field('id,stock_control,qty')
            ->where('hidden', 0)
            ->where('id', $id)
            ->find();

        return $product?:(object)[];
    }

    /**
     * 时间 2023-01-29
     * @title 商品搜索
     * @desc 商品搜索
     * @author hh
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品ID,商品名,描述
     * @param int param.product_group_id - 商品分组ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @param  int param.exclude_domain - 是否排除域名(0=否,1=是)
     * @return array list - 商品列表
     * @return int list[].id - 商品ID
     * @return string list[].name - 商品名
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return int list[].qty - 库存
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int list[].agentable - 是否可代理商品0否1是
     * @return int list[].agent - 代理商品0否1是
     * @return int list[].host_num - 产品数量
     * @return string list[].mode - 代理模式:only_api仅调用接口，sync同步数据，这里判断一下如果为sync，不需要显示接口类型和即可，并直接拉取所有信息
     * @return int count - 商品总数
     */
    public function productListSearch($param)
    {
        // 获取当前应用
       $app = app('http')->getName();

        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description|s.id|s.name|sgs.name|sgs.id', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['id'])){
                $query->where('p.product_group_id', $param['id']);
            }
            if(!empty($param['type']) && !empty($param['rel_id'])){
                $query->where('p.type', $param['type'])->where('p.rel_id', $param['rel_id']);
            }
            if(isset($param['exclude_domain']) && $param['exclude_domain'] == 1){
                $query->where('pg.type', '<>', 'domain');
            }

            if($app=='home'){
                $query->where('p.hidden', 0);
            }/*else{
                $query->where('pgf.name','<>','应用商店');
            }*/
        };
        if($app=='home'){
            $field = 'p.id,p.name';
        }else{
            $field = 'p.id,p.name,p.stock_control,p.qty,p.hidden,p.pay_type,pg.name as product_group_name_second,
            pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first,
            up.id upstream_product_id,p.agentable,up.mode,s.name server_name,s.id server_id,sgs.name server_name_other,
            sgs.id server_id_other';
            $hostNum =  HostModel::field('COUNT(id) host_num,product_id')
                ->where('is_delete', 0)
                ->group('product_id')
                ->select()
                ->toArray();
            $hostNum = array_column($hostNum, 'host_num', 'product_id');
        }
        
        $products = $this->alias('p')
            ->field($field)
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('upstream_product up','up.product_id=p.id')
            ->leftJoin('server s','p.type=\'server\' and s.id=p.rel_id')
            ->leftJoin('server_group sg','p.type=\'server_group\' and sg.id=p.rel_id')
            ->leftJoin('server sgs','sgs.server_group_id=sg.id')
            ->where($where)
            ->limit((isset($param['limit']) && !empty($param['limit']))?intval($param['limit']):1000000)
            ->page((isset($param['page']) && !empty($param['page']))?intval($param['page']):1)
            #->order($param['orderby'], (isset($param['sort']) && !empty($param['sort']))?$param['sort']:"desc")
            ->order('p.order','desc')
            ->group('p.id')
            ->select()
            ->toArray();

        $count = $this
            ->alias('p')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('upstream_product up','up.product_id=p.id')
            ->leftJoin('server s','p.type=\'server\' and s.id=p.rel_id')
            ->leftJoin('server_group sg','p.type=\'server_group\' and sg.id=p.rel_id')
            ->leftJoin('server sgs','sgs.server_group_id=sg.id')
            ->where($where)
            ->group('p.id')
            ->count();
        if($app!='home'){
            foreach ($products as $key => $value) {
                $products[$key]['agent'] = !empty($value['upstream_product_id']) ? 1 : 0;
                $products[$key]['host_num'] = $hostNum[$value['id']] ?? 0;
                unset($products[$key]['upstream_product_id']);
            }
        }

        return ['list'=>$products, 'count'=>$count];
    }

    /**
     * 时间 2023-01-29
     * @title 设置商品最低周期价格
     * @desc 设置商品最低周期价格
     * @author hh
     * @version v1
     * @param   int|ProductModel id - 商品ID|ProductModel实例
     * @param   float price - 价格
     * @param   string cycle - 周期
     * @return  bool|array - false=未修改,array=修改的数据
     * @return  float price - 价格
     * @return  string cycle - 周期
     */
    public function setPriceCycle($id = null, $price = null, $cycle = null)
    {
        if(is_numeric($id)){
            $ProductModel = ProductModel::find($id);
        }else if($id instanceof ProductModel){
            $ProductModel = $id;
        }else{
            $ProductModel = $this;
        }
        if(!isset($ProductModel->id) || empty($ProductModel)){
            return false;
        }
        $update = [];
        if($ProductModel['pay_type'] == 'free'){
            $update['price'] = 0;
            $update['cycle'] = '免费';
        }else if($ProductModel['pay_type'] == 'onetime'){
            if(is_numeric($price)){
                $update['price'] = $price;
            }
            $update['cycle'] = '一次性';
        }else{
            if(is_numeric($price)){
                $update['price'] = $price;
            }
            if(!is_null($cycle)){
                $update['cycle'] = $cycle;
            }
        }
        if(empty($update)){
            return false;
        }
        $this->where('id', $ProductModel->id)->update($update);
        return $update;
    }

    /**
     * 时间 2023-02-16
     * @title 获取上游模块资源
     * @desc 获取上游模块资源
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.module - resmodule名称
     * @return  string data.url - zip包完整下载路径
     * @return  string data.version - 版本号
     */
    public function downloadResource($param)
    {
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->downloadResource($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->downloadResource($ProductModel);
        }
        return $result;
    }

    /**
     * 时间 2024-11-12
     * @title 获取上游插件资源
     * @desc 获取上游插件资源
     * @author theworld
     * @version v1
     * @param   int param.id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data.plugin - 插件列表
     * @return  string data.plugin[].module - 插件模块
     * @return  string data.plugin[].name - 插件标识
     * @return  string data.plugin[].url - zip包完整下载路径
     * @return  string data.plugin[].version - 版本号
     */
    public function downloadPluginResource($param)
    {
        $ProductModel = $this->find((int)$param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $ProductModel['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->downloadPluginResource($ProductModel);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->downloadPluginResource($ProductModel);
        }
        return $result;
    }

    /**
     * 时间 2023-02-20
     * @title 保存可代理商品
     * @desc 保存可代理商品
     * @author theworld
     * @version v1
     * @param array param.id - 商品ID require
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */ 
    public function saveAgentableProduct($param)
    {
        $param['id'] = $param['id'] ?? [];
        if(!is_array($param['id'])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        $count = $this->whereIn('id', $param['id'])->count();
        if (count($param['id'])!=$count){
            return ['status'=>400,'msg'=>lang('param_error')];
        }

        idcsmart_cache('product:list',null);

        $this->startTrans();
        try{
            $this->where('agentable', 1)->update(['agentable' => 0]);
            $this->whereIn('id', $param['id'])->where('pay_type', '<>', 'on_demand')->update(['agentable' => 1]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('fail_message') . $e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        return ['status'=>200,'msg'=>lang('success_message')];
    }


    /**
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @author theworld
     * @version v1
     * @param string param.module - 模块名称
     * @return array list - 一级分组列表
     * @return int list[].id - 一级分组ID
     * @return string list[].name - 一级分组名称
     * @return array list[].child - 二级分组
     * @return int list[].child[].id - 二级分组ID
     * @return string list[].child[].name - 二级分组名称
     * @return array list[].child[].child - 商品
     * @return int list[].child[].child[].id - 商品ID
     * @return string list[].child[].child[].name - 商品名称
     */
    public function resModuleProductList($param)
    {
        $where = function (Query $query) use($param) {
            $query->where('p.hidden', 0)->where('up.res_module', '<>', '');
            if(!empty($param['module'])){
                $query->where('up.res_module', $param['module']);
            }
        };

        $ProductGroupModel = new ProductGroupModel();
        $firstGroup = $ProductGroupModel->productGroupFirstList();
        $firstGroup = $firstGroup['list'];

        $secondGroup = $ProductGroupModel->productGroupSecondList([]);
        $secondGroup = $secondGroup['list'];

        $products = $this->alias('p')
            ->field('p.id,p.name,p.product_group_id')
            ->leftjoin('upstream_product up','up.product_id=p.id')
            ->where($where)
            ->order('p.order','desc')
            ->select()
            ->toArray();
        $productArr = [];
        foreach ($products as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $productArr[$value['id']]];
            }
        }
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => $value['id'], 'name' => $value['name'], 'child' => $secondGroupArr[$value['id']]];
            }
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2023-10-16
     * @title 复制商品
     * @desc  复制商品
     * @author theworld
     * @version v1
     * @param   int param.id - 商品ID require
     * @param   int  param.product_group_id - 二级分组ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function copyProduct($param)
    {
        $id = intval($param['id']);
        $param['product_group_id'] = intval($param['product_group_id'] ?? 0);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        if (!empty($product['product_id'])){
            return ['status'=>400,'msg'=>lang('son_product_cannot_copy')];
        }

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $id)->find();
        if (!empty($upstreamProduct)){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_copy')];
        }
        
        if(!empty($param['product_group_id'])){
            $ProductGroupModel = new ProductGroupModel();
            $productGroup = $ProductGroupModel->where('parent_id', '>', 0)->find($param['product_group_id']);
            if(empty($productGroup)){
                return ['status'=>400,'msg'=>lang('product_group_is_not_exist')];
            }
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $selfDefinedField = SelfDefinedFieldModel::where('relid', $id)->where('type', 'product')->select()->toArray();

        // 双删
        idcsmart_cache('product:list',null);

        $this->startTrans();

        try{

            $data = $product->toArray();
            unset($data['id']);
            $data['name'] = $data['name'].'(1)';
            $data['product_group_id'] = !empty($param['product_group_id']) ? $param['product_group_id'] : $data['product_group_id'];

            $product = $this->create($data);

            $sonProduct = $this->where('product_id', $id)->select()->toArray();
            $sonProductIdArr = [];
            foreach ($sonProduct as $key => $value) {
                $sonProductId = $value['id'];
                $sonProductIdArr[$sonProductId] = 0;
                unset($value['id']);
                $value['product_id'] = $product->id;
                $value['product_group_id'] = !empty($param['product_group_id']) ? $param['product_group_id'] : $value['product_group_id'];
                $r = $this->create($value);
                $sonProductIdArr[$sonProductId] = $r->id;
            }

            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $upgrade = $ProductUpgradeProductModel->where('product_id', $id)->select()->toArray();
            $upgradeIds = [];
            foreach ($upgrade as $key => $value) {
                $upgradeIds[] = $value['upgrade_product_id'];
            }
            if(!empty($upgradeIds)){
                foreach ($upgradeIds as $upgradeId){
                    $insert[] = [
                        'product_id' => $product->id,
                        'upgrade_product_id' => $upgradeId
                    ];
                }
                $ProductUpgradeProductModel->saveAll($insert);
            }
            
            // 自定义字段
            if(!empty($selfDefinedField)){
                $selfDefinedFieldArr = [];
                foreach($selfDefinedField as $key=>$value){
                    unset($value['id']);
                    $value['relid'] = $product->id;
                    $selfDefinedFieldArr[] = $value;
                }
                $SelfDefinedFieldModel->insertAll($selfDefinedFieldArr);
            }
            
            $ProductNoticeGroupModel = new ProductNoticeGroupModel();
            $ProductNoticeGroupModel->addProductToDefaultProductNoticeGroup($product->id);

            # 记录日志
            active_log(lang('log_admin_copy_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$data['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        idcsmart_cache('product:list',null);

        $res = hook('after_product_copy',['id'=>$product->id, 'product_id' => $param['id'], 'son_product_id' => $sonProductIdArr, 'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('success_message')];

    }

    /**
     * 时间 2024-02-02
     * @title 商品名称获取器
     * @desc  商品名称name字段获取器,输出时可通过多语言插件修改
     * @author hh
     * @version v1
     * @param   string $value - 获取的商品名称 require
     * @return  string
     */
    public function getNameAttr($value)
    {
        if(app('http')->getName() == 'home'){
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
    }

    /**
     * 时间 2024-03-20
     * @title 获取所有商品
     * @desc  获取所有商品，树形图结构
     * @author theworld
     * @version v1
     * @return array [] - 商品一级分组
     * @return string [].name - 商品一级分组名称
     * @return array [].child - 商品二级分组
     * @return string [].child[].name - 商品二级分组名称
     * @return array [].child[].product - 商品
     * @return int [].child[].product[].id - 商品ID
     * @return string [].child[].product[].name - 商品名称
     */
    public function getProductList()
    {
        $ProductGroupModel = new ProductGroupModel();
        $firstGroup =  $ProductGroupModel->field('id,name')
            ->where('parent_id', 0)
            ->select()
            ->toArray();
        $secondGroup =  $ProductGroupModel->field('id,name,parent_id')
            ->where('parent_id', '>', 0)
            ->select()
            ->toArray();
        $product =  $this->field('id,name,product_group_id')
            ->select()
            ->toArray();
        
        $productArr = [];
        foreach ($product as $key => $value) {
            $productArr[$value['product_group_id']][] = ['id' => $value['id'], 'name' => $value['name']];
        }

        $secondGroupArr = [];
        foreach ($secondGroup as $key => $value) {
            if(isset($productArr[$value['id']])){
                $secondGroupArr[$value['parent_id']][] = ['id' => 'sg'.$value['id'], 'name' => $value['name'], 'children' => $productArr[$value['id']]];
            }
        }    
        
        $list = [];
        foreach ($firstGroup as $key => $value) {
            if(isset($secondGroupArr[$value['id']])){
                $list[] = ['id' => 'fg'.$value['id'], 'name' => $value['name'], 'children' => $secondGroupArr[$value['id']]];
            }
        }   
        return $list;     
    }

    /**
     * 时间 2024-07-02
     * @title 获取商品自定义标识配置
     * @desc 获取商品自定义标识配置
     * @author theworld
     * @version v1
     * @param int id - 商品ID require
     * @return int custom_host_name - 自定义主机标识开关(0=关闭,1=开启)
     * @return string custom_host_name_prefix - 自定义主机标识前缀
     * @return array custom_host_name_string_allow - 允许的字符串(number=数字,upper=大写字母,lower=小写字母)
     * @return int custom_host_name_string_length - 字符串长度
     */
    public function getCustomHostName($id)
    {
        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        $data = [
            'custom_host_name' => $product['custom_host_name'],
            'custom_host_name_prefix' => $product['custom_host_name_prefix'],
            'custom_host_name_string_allow' => array_filter(explode(',', $product['custom_host_name_string_allow'])),
            'custom_host_name_string_length' => $product['custom_host_name_string_length'],
        ];

        return $data;
    }

    /**
     * 时间 2024-07-02
     * @title 获取商品自定义标识配置
     * @desc 获取商品自定义标识配置
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID require
     * @param int param.custom_host_name - 自定义主机标识开关(0=关闭,1=开启) require
     * @param string param.custom_host_name_prefix - 自定义主机标识前缀 require
     * @param array param.custom_host_name_string_allow - 允许的字符串(number=数字,upper=大写字母,lower=小写字母) require
     * @param int param.custom_host_name_string_length - 字符串长度 require
     */
    public function saveCustomHostName($param)
    {
        $product = $this->find($param['id']);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        if(empty($param['custom_host_name'])){
            $param['custom_host_name_prefix'] = $product['custom_host_name_prefix'];
            $param['custom_host_name_string_allow'] = $product['custom_host_name_string_allow'];
            $param['custom_host_name_string_length'] = $product['custom_host_name_string_length'];
        }else{
            $param['custom_host_name_string_allow'] = implode(',', $param['custom_host_name_string_allow']);
        }

        # 日志描述
        $logDescription = log_description($product->toArray(),$param,'product');

        $this->startTrans();

        try{
            $this->update([
                'custom_host_name' => $param['custom_host_name'],
                'custom_host_name_prefix' => $param['custom_host_name_prefix'],
                'custom_host_name_string_allow' => $param['custom_host_name_string_allow'],
                'custom_host_name_string_length' => $param['custom_host_name_string_length'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            if (!empty($logDescription)){
                active_log(lang('log_admin_update_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$product['name'].'#','{description}'=>$logDescription]),'product',$product->id);
            }
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    // 预设周期
    public function durationPresets($param)
    {
        $product = $this->find($param['id']);
        // 是否切换接口
        $changeServer = false;
        if ($param['type']==$param['oldProduct']['type']){
            if ($param['oldProduct']['rel_id']!=$param['rel_id']){
                $changeServer = true;
            }
        }else{
            $changeServer = true;
        }

        // 切换了接口，删除旧数据
        if ($changeServer){
            $module = $this->getModule([],$param['oldProduct']['rel_id'], $param['oldProduct']['type']);
            if (!empty($module)){
                // 删除预设周期
                $ModuleLogic = new ModuleLogic();
                $ModuleLogic->durationPresetsDelete($module, $product);
                $ProductDurationRatioModel = new ProductDurationRatioModel();
                // 删除周期比例
                $ProductDurationRatioModel->where('product_id',$product['id'])->delete();
            }
        }

        // 预设周期开启 且 切换接口
        if (configuration('product_duration_group_presets_open') && $changeServer){
            $config = configuration(['product_duration_group_presets_apply_range', 'product_duration_group_presets_default_id']);
            $ProductDurationGroupPresetsModel = new ProductDurationGroupPresetsModel();
            $ProductDurationPresetsModel = new ProductDurationPresetsModel();
            // 全局设置
            if ($config['product_duration_group_presets_apply_range']==0){
                $gid = $config['product_duration_group_presets_default_id'];
            }else{ // 按接口设置
                if($param['type'] == 'server_group'){
                    $server = ServerModel::where('server_group_id', $param['rel_id'])->find();
                }else{
                    $server = ServerModel::find($param['rel_id']);
                }
                $ProductDurationGroupPresetsLinkModel = new ProductDurationGroupPresetsLinkModel();
                $gid = $ProductDurationGroupPresetsLinkModel->where('server_id',$server['id'])->value('gid');
            }
            $groupPresets = $ProductDurationGroupPresetsModel->where('id',$gid)->find();
            $durations = $ProductDurationPresetsModel->where('gid',$gid)
                ->select()
                ->toArray();
            if (!empty($groupPresets) && !empty($durations)){
                $module = $this->getModule([],$param['rel_id'], $param['type']);
                $ModuleLogic = new ModuleLogic();
                $result = $ModuleLogic->durationPresets($module, $product, $durations);
                if ($result['status']==200){
                    // 插入周期比例
                    if ($groupPresets['ratio_open']){
                        $ProductDurationRatioModel = new ProductDurationRatioModel();
                        foreach ($result['data'] as $k=>$v){
                            foreach ($durations as $duration){
                                if ($k==$duration['id']){
                                    $ProductDurationRatioModel->create([
                                        'product_id' => $product['id'],
                                        'duration_id' => $v,
                                        'ratio' => $duration['ratio']
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    // 是否按周期比例续费
    public static function isRenewByRatio($param)
    {
        $productGroupId = $param['product_group_id']??0;
        $serverId = $param['server_id']??0;
        $config = configuration([
            'product_new_host_renew_with_ratio_open',
            'product_new_host_renew_with_ratio_apply_range',
            'product_new_host_renew_with_ratio_apply_range_2',
            'product_new_host_renew_with_ratio_apply_range_1',
        ]);
        if ($config['product_new_host_renew_with_ratio_open']){
            if ($config['product_new_host_renew_with_ratio_apply_range']==0){
                return true;
            }elseif ($config['product_new_host_renew_with_ratio_apply_range']==1){
                $serverIds = explode(',',$config['product_new_host_renew_with_ratio_apply_range_1']);
                if (in_array($serverId,$serverIds)){
                    return true;
                }
            }else{
                $productGroupIds = explode(',',$config['product_new_host_renew_with_ratio_apply_range_2']);
                if (in_array($productGroupId,$productGroupIds)){
                    return true;
                }
            }
        }
        return false;
    }

    // 全局设置
    public function globalSetting($param)
    {
        $config = configuration([
            'product_global_renew_rule',
            'product_global_show_base_info',
        ]);
        // 全局设置未修改
        if (/*$config['product_global_renew_rule']==$param['product_global_renew_rule'] && */$config['product_global_show_base_info']==$param['product_global_show_base_info']){
            return false;
        }

        if ($param['product_global_renew_rule']==1){
            $renewRule = 'due';
        }else{
            $renewRule = 'current';
        }
        $this->where('id','>',0)->update([/*'renew_rule'=>$renewRule,*/'show_base_info'=>$param['product_global_show_base_info']??0]);
        return true;
    }

    public function payOntrial($param)
    {
        $this->startTrans();

        try{
            $product = $this->find($param['id']);

            if (empty($product)){
                throw new \Exception('product_is_not_exist');
            }

            $payOntrial = $param['pay_ontrial']??[];

            $payOntrialFilter = [
                'status' => $payOntrial['status']??0,
                'cycle_type' => $payOntrial['cycle_type']??'hour',
                'cycle_num' => $payOntrial['cycle_num']??0,
                'client_limit' => $payOntrial['client_limit']??'no',
                'account_limit' => $payOntrial['account_limit']??[],
                'old_client_exclusive' => $payOntrial['old_client_exclusive']??[],
                'max' => $payOntrial['max']??0,
            ];

            $product->save([
                'pay_ontrial'=>json_encode($payOntrialFilter),
                'update_time'=>time(),
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang($e->getMessage())];
        }

        return ['status'=>200,'msg'=>lang('success_message')];
    }

    /**
     * @时间 2025-03-21
     * @title 获取商品按需计费配置
     * @desc  获取商品按需计费配置
     * @author hh
     * @version v1
     * @param   int|ProductModel product - 商品ID|商品模型实例 require
     * @return  array
     * @return  bool on_demand - 是否支持按需计费
     * @return  ProductOnDemandModel|null product_on_demand - 商品按需计费配置模型实例
     */
    public function productOnDemand($product): array
    {
        $data = [
            'on_demand'         => false,
            'product_on_demand' => NULL,
        ];
        if(is_numeric($product)){
            $product = $this->find($product);
        }
        if(empty($product)){
            return $data;
        }
        $support = in_array($product['pay_type'], ['on_demand','recurring_prepayment_on_demand']);
        if($support){
            // 获取按需配置设置
            $ProductOnDemandModel = new ProductOnDemandModel();
            $productOnDemand = $ProductOnDemandModel->productOnDemandIndex($product['id']);
            // 保存过了,认为可以使用
            if($productOnDemand['update_time'] > 0){
                $data['on_demand'] = true;
                $data['product_on_demand'] = $productOnDemand;
            }
        }
        return $data;
    }

    /**
     * 时间 2026-01-05
     * @title 修改商品自然月预付费开关
     * @desc 修改商品自然月预付费开关
     * @author hh
     * @version v1
     * @param int param.id - 商品ID require
     * @param int param.natural_month_prepaid - 自然月预付费开关(0=关闭,1=开启) require
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateNaturalMonthPrepaid($param): array
    {
        $id = intval($param['id']);
        $naturalMonthPrepaid = intval($param['natural_month_prepaid']);

        $product = $this->find($id);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

        // 检查是否为下游商品
        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id', $id)->find();
        if (!empty($upstreamProduct)){
            return ['status'=>400,'msg'=>lang('downstream_product_cannot_update_natural_month_prepaid')];
        }

        // 如果是开启自然月预付费，验证付款类型
        if ($naturalMonthPrepaid == 1){
            $allowedPayTypes = ['recurring_prepayment', 'recurring_prepayment_on_demand'];
            if (!in_array($product['pay_type'], $allowedPayTypes)){
                return ['status'=>400,'msg'=>lang('natural_month_prepaid_only_support_recurring_prepayment')];
            }
        }

        // 检查状态是否变更
        $oldStatus = intval($product['natural_month_prepaid']);
        if ($oldStatus == $naturalMonthPrepaid){
            return ['status'=>200,'msg'=>lang('success_message')];
        }

        // 检查是否有激活或暂停中的产品
        $HostModel = new HostModel();
        $activeHostCount = $HostModel
            ->where('product_id', $id)
            ->whereIn('status', ['Active', 'Suspended'])
            ->where('is_delete', 0)
            ->count();
        
        if ($activeHostCount > 0){
            return ['status'=>400,'msg'=>lang('product_has_active_or_suspended_host_cannot_update_natural_month_prepaid')];
        }

        // 获取模块
        $module = $product->getModule();

        $this->startTrans();

        try{
            // 更新商品
            $update = $this
                    ->where('id', $id)
                    ->where('natural_month_prepaid', '<>', $naturalMonthPrepaid)
                    ->update([
                        'natural_month_prepaid' => $naturalMonthPrepaid,
                        'update_time' => time(),
                    ]);
            
            if($update){
                // 触发钩子
                hook('product_natural_month_prepaid_change', [
                    'product_id' => $id,
                    'module' => $module,
                    'status' => $naturalMonthPrepaid,
                ]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        if($update){
            // 记录日志
            $statusText = $naturalMonthPrepaid == 1 ? lang('switch_1') : lang('switch_0');
            active_log(lang('log_admin_update_product_natural_month_prepaid', [
                '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                '{product}' => 'product#'.$id.'#'.$product['name'].'#',
                '{status}' => $statusText
            ]), 'product', $id);
        }

        return ['status'=>200,'msg'=>lang('success_message')];
    }

}

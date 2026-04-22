<?php
namespace app\home\controller;

use app\common\model\ProductModel;
use app\common\model\ProductGroupModel;
use app\home\validate\ProductValidate;
use app\common\model\SelfDefinedFieldModel;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\home\controller\ProductController
 */
class ProductController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductValidate();
    }

	/**
     * 时间 2022-5-30
     * @title 获取商品一级分组
     * @desc 获取商品一级分组
     * @author theworld
     * @version v1
     * @url /console/v1/product/group/first
     * @method GET
     * @return array list - desc:商品一级分组
     * @return int list[].id - desc:商品一级分组ID
     * @return int list[].name - desc:商品一级分组名称
     * @return int list[].type - desc:分组类型 type为domain表示域名
     * @return int count - desc:商品一级分组总数
     */
    public function productGroupFirstList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductGroupModel())->productGroupFirstList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-30
     * @title 获取商品二级分组
     * @desc 获取商品二级分组
     * @author theworld
     * @version v1
     * @url /console/v1/product/group/second
     * @method GET
     * @param int id - desc:一级分组ID validate:optional
     * @return array list - desc:商品二级分组
     * @return int list[].id - desc:商品二级分组ID
     * @return int list[].name - desc:商品二级分组名称
     * @return int list[].parent_id - desc:商品一级分组ID
     * @return int list[].type - desc:分组类型 type为domain表示域名
     * @return string list[].description - desc:描述
     * @return int count - desc:商品二级分组总数
     */
    public function productGroupSecondList()
    {
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductGroupModel())->productGroupSecondList($param)
        ];
        return json($result);
    }

     /**
     * 时间 2022-5-30
     * @title 商品列表
     * @desc 商品列表
     * @author theworld
     * @version v1
     * @url /console/v1/product
     * @method GET
     * @param string keywords - desc:关键字 搜索范围商品ID商品名描述 validate:optional
     * @param int id - desc:二级分组ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param bool exclusive - desc:是否只返回专属商品 validate:optional
     * @param array product_ids - desc:筛选哪些商品 validate:optional
     * @return array list - desc:商品列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:商品名
     * @return string list[].description - desc:描述
     * @return string list[].pay_type - desc:付款类型 免费free 一次onetime 周期先付recurring_prepayment 周期后付recurring_postpaid 按需计费on_demand 周期先付+按需计费recurring_prepayment_on_demand
     * @return string list[].price - desc:商品最低价格
     * @return string list[].cycle - desc:商品最低周期
     * @return string list[].mode - desc:代理模式 only_api仅调用接口 sync同步商品
     * @return string list[].client_level_name - desc:用户等级名称 这个字段在没有用户等级插件时不存在所以需要注意判断
     * @return int list[].stock_control - desc:是否开启库存
     * @return int list[].qty - desc:库存数量 当开启库存该字段才有意义
     * @return object list[].pay_ontrial - desc:试用配置 status是否开启 cycle_type时长单位hour/day/month cycle_num时长 client_limit用户限制no不限制/new新用户/host用户必须存在激活中的产品 account_limit账户限制email绑定邮件/phone绑定手机/certification old_client_exclusive老用户专享商品ID多选逗号分隔 max单用户最大试用数量
     * @return int count - desc:商品总数
     */
    public function list()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductModel())->productList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @url /console/v1/product/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return object product - desc:商品
     * @return int product.id - desc:ID
     * @return string product.name - desc:商品名称
     * @return int product.product_group_id - desc:所属商品组ID
     * @return string product.description - desc:商品描述
     * @return int product.hidden - desc:0显示默认 1隐藏
     * @return int product.stock_control - desc:库存控制 1启用 默认0
     * @return int product.qty - desc:库存数量 与stock_control有关
     * @return int product.pay_type - desc:付款类型 免费free 一次onetime 周期先付recurring_prepayment 周期后付recurring_postpaid 按需计费on_demand 周期先付+按需计费recurring_prepayment_on_demand
     * @return int product.auto_setup - desc:是否自动开通 1是 默认0否
     * @return int product.type - desc:关联类型 server server_group
     * @return int product.rel_id - desc:关联ID
     * @return array upgrade - desc:可升降级商品ID 数组
     * @return int product_id - desc:父商品ID
     * @return array plugin_custom_fields - desc:自定义字段{is_link是否已有子商品 是 置灰}
     * @return int show - desc:是否将商品展示在会员中心对应模块的列表中 0否 1是
     * @return string on_demand.min_credit - desc:购买时用户最低余额
     * @return int on_demand.min_usage_time - desc:最低使用时长
     * @return string on_demand.min_usage_time_unit - desc:最低使用时长单位 second秒 minute分 hour小时
     * @return int on_demand.credit_limit_pay - desc:允许信用额支付 0否 1是
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'product' => (new ProductModel())->indexProduct(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @author theworld
     * @version v1
     * @url /console/v1/product/settle
     * @method POST
     * @param int product_id - desc:商品ID validate:required
     * @param object config_options - desc:自定义配置 validate:required
     * @param object customfield - desc:自定义参数 比如优惠码参数传{"promo_code":["pr8nRQOGbmv5"]} validate:optional
     * @param int qty - desc:数量 validate:required
     * @param object self_defined_field - desc:自定义字段 {"5":"123"}5是自定义字段ID123是填写的内容 validate:optional
     * @return int order_id - desc:订单ID
     */
    public function settle()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ProductModel = new ProductModel();
        
        // 结算商品
        $result = $ProductModel->settle($param);

        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 批量结算商品
     * @desc 批量结算商品
     * @author theworld
     * @version v1
     * @url /console/v1/product/batch_settle
     * @method  POST
     * @param  array products - desc:商品 required
     * @param  int products[].product_id - desc:商品ID required
     * @param  object products[].config_options - desc:自定义配置
     * @param  object products[].customfield - desc:自定义参数
     * @param  int products[].qty - desc:数量 required
     * @param  object products[].self_defined_field - desc:自定义字段 {"5":"123"}5是自定义字段ID123是填写的内容
     * @param  object customfield - desc:自定义参数 比如优惠码参数传{"promo_code":["pr8nRQOGbmv5"]}
     * @return int order_id - desc:订单ID
     */
//    public function batchSettle()
//    {
//        // 接收参数
//        $param = $this->request->param();
//
//        // 参数验证
//        if (!$this->validate->scene('batch_settle')->check($param)){
//            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
//        }
//
//        // 实例化模型类
//        $ProductModel = new ProductModel();
//
//        // 结算商品
//        $result = $ProductModel->batchSettle($param);
//
//        return json($result);
//    }

    /**
     * 时间 2022-05-30
     * @title 商品配置页面
     * @desc 商品配置页面
     * @url /console/v1/product/:id/config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - desc:商品ID validate:required
     * @param   bool flag - desc:是否获取隐藏商品的模块内容 true是 false否 validate:optional
     * @return  string product_name - desc:商品名称
     * @return  string content - desc:模块输出内容
     */
    public function moduleClientConfigOption()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();

        $result = $ProductModel->moduleClientConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 修改配置计算价格
     * @desc 修改配置计算价格
     * @url /console/v1/product/:id/config_option
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - desc:商品ID validate:required
     * @param   int qty - desc:数量 validate:required
     * @param   array config_options - desc:模块自定义配置参数 格式{"configoption":{1:1,2:[2]},"cycle":2,"promo_code":"Af13S1ACj","event_promotion":12,"qty":1} validate:optional
     * @return  string price - desc:价格
     * @return  string renew_price - desc:续费价格
     * @return  string billing_cycle - desc:周期名称
     * @return  int duration - desc:周期时长 秒
     * @return  string description - desc:订单子项描述
     * @return  string base_price - desc:基础价格
     * @return  float price_total - desc:折扣后金额 各种优惠折扣处理后的金额没有就是price价格
     * @return  float price_promo_code_discount - desc:优惠码折扣金额 当使用优惠码且有效时才返回此字段
     * @return  float price_client_level_discount - desc:客户等级折扣金额 当客户等级有效时才返回此字段
     * @return  float price_event_promotion_discount - desc:活动促销折扣金额 当活动促销有效时才返回此字段
     */
    public function moduleCalculatePrice()
    {
        $param = $this->request->param();
        $param['product_id'] = $param['id'] ?? 0;

        // 参数验证
        if (!$this->validate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        $ProductModel = new ProductModel();

        $result = $ProductModel->productCalculatePrice($param);
        return json($result);
    }

    /**
     * 时间 2022-10-11
     * @title 获取商品库存
     * @desc 获取商品库存
     * @author theworld
     * @version v1
     * @url /console/v1/product/:id/stock
     * @method  GET
     * @param int id - desc:商品ID validate:required
     * @return object product - desc:商品
     * @return int product.id - desc:ID
     * @return int product.stock_control - desc:库存控制 0关闭 1启用
     * @return int product.qty - desc:库存数量
     */
    public function productStock()
    {
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' =>[
                'product' => (new ProductModel())->productStock($param['id'])
            ] 
        ];
        return json($result);
    }

    /**
     * 时间 2024-01-02
     * @title 商品订单页自定义字段
     * @desc  商品订单页自定义字段
     * @url /console/v1/product/:id/self_defined_field/order_page
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return int data[].id - desc:自定义字段ID
     * @return string data[].field_name - desc:字段名称
     * @return string data[].field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 checkbox勾选框 textarea文本区 explain说明
     * @return string data[].description - desc:字段描述
     * @return string data[].regexpr - desc:验证规则
     * @return string data[].field_option - desc:下拉选项
     * @return int data[].is_required - desc:是否必填 0否 1是
     * @return int data[].show_client_host_list - desc:会员中心列表显示 0否 1是
     * @return string data[].explain_content - desc:说明内容
     */
    public function orderPageSelfDefinedField()
    {
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new SelfDefinedFieldModel())->showOrderPageField($param),
        ];
        return json($result);
    }


}
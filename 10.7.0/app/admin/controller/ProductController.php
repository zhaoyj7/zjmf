<?php
namespace app\admin\controller;

use app\admin\validate\ProductValidate;
use app\admin\validate\ProductNoticeGroupValidate;
use app\admin\validate\ProductOnDemandValidate;
use app\common\model\ProductModel;
use app\common\model\SyncImageLogModel;
use app\common\model\ProductNoticeGroupModel;
use app\common\model\ProductOnDemandModel;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\admin\controller\ProductController
 */
class ProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductValidate();
    }

    /**
     * 时间 2022-5-17
     * @title 商品列表
     * @desc 商品列表
     * @url /admin/v1/product
     * @method GET
     * @author wyh
     * @version v1
     * @param string type - desc:关联类型 validate:optional
     * @param string rel_id - desc:关联ID validate:optional
     * @param string keywords - desc:关键字 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id name description validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @param int exclude_domain - desc:是否排除域名 0否 1是 validate:optional
     * @return array list - desc:商品列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:商品名
     * @return string list[].description - desc:描述
     * @return int list[].stock_control - desc:是否开启库存控制 1开启 0关闭
     * @return int list[].qty - desc:库存
     * @return string list[].pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付 on_demand按需计费 recurring_prepayment_on_demand周期先付+按需计费
     * @return int list[].hidden - desc:是否隐藏 1隐藏 0显示
     * @return string list[].product_group_name_second - desc:二级分组名称
     * @return int list[].product_group_id_second - desc:二级分组ID
     * @return string list[].product_group_name_first - desc:一级分组名称
     * @return int list[].product_group_id_first - desc:一级分组ID
     * @return int list[].agentable - desc:是否可代理商品 0否 1是
     * @return int list[].agent - desc:代理商品 0否 1是
     * @return int list[].host_num - desc:产品数量
     * @return int count - desc:商品总数
     */
    public function productList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),[]);//['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->productListSearch($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @url /admin/v1/module/:module/product
     * @method GET
     * @author theworld
     * @version v1
     * @param string module - desc:模块名称 validate:required
     * @return array list - desc:一级分组列表
     * @return int list[].id - desc:一级分组ID
     * @return string list[].name - desc:一级分组名称
     * @return array list[].child - desc:二级分组
     * @return int list[].child[].id - desc:二级分组ID
     * @return string list[].child[].name - desc:二级分组名称
     * @return array list[].child[].child - desc:商品
     * @return int list[].child[].child[].id - desc:商品ID
     * @return string list[].child[].child[].name - desc:商品名称
     */
    public function moduleProductList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->moduleProductList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @url /admin/v1/product/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return object product - desc:商品
     * @return int product.id - desc:ID
     * @return string product.name - desc:商品名称
     * @return int product.product_group_id - desc:所属商品组ID
     * @return string product.description - desc:商品描述
     * @return int product.hidden - desc:是否隐藏 0显示 1隐藏
     * @return int product.stock_control - desc:库存控制 1启用 0关闭
     * @return int product.qty - desc:库存数量
     * @return int product.product_id - desc:父商品ID
     * @return array product.plugin_custom_fields - desc:自定义字段
     * @return int product.show - desc:是否展示在会员中心 0否 1是
     * @return string product.renew_rule - desc:续费规则 due到期日 current当前时间
     * @return int product.auto_renew_in_advance - desc:自动续费提前开关 0关闭 1开启
     * @return int product.auto_renew_in_advance_num - desc:自动续费提前时间数
     * @return string product.auto_renew_in_advance_unit - desc:自动续费提前时间单位 minute分钟 hour小时 day天
     * @return int product.natural_month_prepaid - desc:自然月预付费开关 0关闭 1开启
     * @return string mode - desc:代理模式 only_api仅调用接口 sync同步商品
     * @return string supplier_name - desc:供应商名称
     * @return int profit_type - desc:利润类型 0百分比价格方案 1自定义金额
     * @return int show_base_info - desc:产品列表是否展示基础信息 1是 0否
     * @return string module - desc:商品对应模块
     * @return object plugin_custom_fields - desc:插件钩子返回的自定义字段
     * @return object pay_ontrial - desc:试用配置
     * @return int pay_ontrial.status - desc:是否开启
     * @return string pay_ontrial.cycle_type - desc:时长单位 hour day month
     * @return int pay_ontrial.cycle_num - desc:时长
     * @return string pay_ontrial.client_limit - desc:用户限制 no不限制 new新用户 host用户必须存在激活中的产品
     * @return string pay_ontrial.account_limit - desc:账户限制 email绑定邮件 phone绑定手机 certification实名
     * @return string pay_ontrial.old_client_exclusive - desc:老用户专享商品ID 逗号分隔
     * @return int pay_ontrial.max - desc:单用户最大试用数量
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
     * 时间 2022-5-17
     * @title 新建商品
     * @desc 新建商品
     * @url /admin/v1/product
     * @method POST
     * @author wyh
     * @version v1
     * @param string name - desc:商品名称 validate:required
     * @param int product_group_id - desc:分组ID 只传二级分组ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     * @param string renew_rule - desc:续费规则 due到期日 current当前时间 validate:optional
     * @return int product_id - desc:商品ID
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 编辑商品
     * @desc 编辑商品
     * @url /admin/v1/product/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param string name - desc:商品名称 validate:required
     * @param int product_group_id - desc:分组ID 只传二级分组ID validate:required
     * @param string description - desc:描述 validate:required
     * @param int hidden - desc:是否隐藏 1隐藏 0显示 validate:required
     * @param int stock_control - desc:库存控制 1启用 0关闭 validate:required
     * @param int qty - desc:库存数量 validate:required
     * @param string pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付 on_demand按需计费 recurring_prepayment_on_demand周期先付+按需计费 validate:required
     * @param int auto_setup - desc:是否自动开通 1是 0否 validate:required
     * @param string type - desc:关联类型 server server_group validate:required
     * @param int rel_id - desc:关联ID validate:required
     * @param array upgrade - desc:可升降级商品ID数组 validate:optional
     * @param int product_id - desc:父级商品ID validate:optional
     * @param string price - desc:商品起售价格 validate:optional
     * @param string renew_rule - desc:续费规则 due到期日 current当前时间 validate:optional
     * @param int show_base_info - desc:产品列表是否展示基础信息 1是 0否 validate:optional
     * @param int auto_renew_in_advance - desc:自动续费提前开关 0关闭 1开启 validate:optional
     * @param int auto_renew_in_advance_num - desc:自动续费提前时间数 validate:optional
     * @param string auto_renew_in_advance_unit - desc:自动续费提前时间单位 minute分钟 hour小时 day天 validate:optional
     * @param string sync_stock - desc:是否同步库存 1是 0否 validate:optional
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-6-10
     * @title 编辑商品接口
     * @desc 编辑商品接口
     * @url /admin/v1/product/:id/server
     * @method PUT
     * @author wyh
     * @version v1
     * @param int auto_setup - desc:是否自动开通 1是 0否 validate:required
     * @param string type - desc:关联类型 server server_group validate:required
     * @param int rel_id - desc:关联ID validate:required
     * @param int show - desc:是否展示在会员中心 0否 1是 validate:required
     * @param int show_base_info - desc:产品列表是否展示基础信息 1是 0否 validate:optional
     */
    public function updateServer()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('edit_server')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->updateServer($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 删除商品
     * @desc 删除商品
     * @url /admin/v1/product/:id
     * @method DELETE
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->deleteProduct(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 批量删除商品
     * @desc 批量删除商品
     * @url /admin/v1/product
     * @method DELETE
     * @author wyh
     * @version v1
     * @param array id - desc:商品ID validate:required
     */
    public function batchDelete()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->batchDeleteProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 隐藏/显示商品
     * @desc 隐藏/显示商品
     * @url /admin/v1/product/:id/:hidden
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param int hidden - desc:是否隐藏 1隐藏 0显示 validate:required
     */
    public function hidden()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->hiddenProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-18
     * @title 商品拖动排序
     * @desc 商品拖动排序
     * @url /admin/v1/product/order/:id
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param int pre_product_id - desc:移动后前一个商品ID 没有则传0 validate:required
     * @param int product_group_id - desc:移动后的商品组ID validate:required
     * @param int backward - desc:是否向后移动 1是 0否 validate:required
     */
    public function order()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->orderProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-31
     * @title 获取商品关联的升降级商品
     * @desc 获取商品关联的升降级商品
     * @url /admin/v1/product/:id/upgrade
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return array list - desc:商品列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:商品名
     */
    public function upgrade()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->upgradeProduct(intval($param['id']));

        return json($result);
    }
    
    /**
     * 时间 2022-05-30
     * @title 选择接口获取配置
     * @desc 选择接口获取配置
     * @url /admin/v1/product/:id/server/config_option
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param string type - desc:关联类型 server接口 server_group接口分组 validate:required
     * @param int rel_id - desc:关联ID validate:required
     * @return string content - desc:模块输出内容
     */
    public function moduleServerConfigOption()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('module_server_config_option')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        $ProductModel = new ProductModel();
        $result = $ProductModel->moduleServerConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 修改配置计算价格
     * @desc 修改配置计算价格
     * @url /admin/v1/product/:id/config_option
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param array config_options - desc:模块自定义配置参数 validate:optional
     * @return string price - desc:价格
     * @return string renew_price - desc:续费价格
     * @return string billing_cycle - desc:周期名称
     * @return int duration - desc:周期时长 秒
     * @return string description - desc:订单子项描述
     * @return string base_price - desc:基础价格
     */
    public function moduleCalculatePrice()
    {
        $param = $this->request->param();
        $param['product_id'] = $param['id'] ?? 0;

        $ProductModel = new ProductModel();

        $result = $ProductModel->productCalculatePrice($param);
        return json($result);
    }

    /**
     * 时间 2023-02-20
     * @title 保存可代理商品
     * @desc 保存可代理商品
     * @url /admin/v1/product/agentable
     * @method PUT
     * @author theworld
     * @version v1
     * @param array id - desc:商品ID validate:required
     */ 
    public function saveAgentableProduct()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->saveAgentableProduct($param);

        return json($result);
    }

    /**
     * 时间 2023-03-01
     * @title 根据上游模块获取商品列表
     * @desc 根据上游模块获取商品列表
     * @url /admin/v1/res_module/:module/product
     * @method GET
     * @author theworld
     * @version v1
     * @param string module - desc:模块名称 validate:required
     * @return array list - desc:一级分组列表
     * @return int list[].id - desc:一级分组ID
     * @return string list[].name - desc:一级分组名称
     * @return array list[].child - desc:二级分组
     * @return int list[].child[].id - desc:二级分组ID
     * @return string list[].child[].name - desc:二级分组名称
     * @return array list[].child[].child - desc:商品
     * @return int list[].child[].child[].id - desc:商品ID
     * @return string list[].child[].child[].name - desc:商品名称
     */
    public function resModuleProductList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->resModuleProductList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-12
     * @title 根据模块获取商品列表
     * @desc 根据模块获取商品列表
     * @url /admin/v1/module/product
     * @method GET
     * @author theworld
     * @version v1
     * @param array|string module - desc:模块名称 validate:optional
     * @param int type - desc:类型 0本地模块 1同步代理 validate:optional
     * @return array list - desc:一级分组列表
     * @return int list[].id - desc:一级分组ID
     * @return string list[].name - desc:一级分组名称
     * @return array list[].child - desc:二级分组
     * @return int list[].child[].id - desc:二级分组ID
     * @return string list[].child[].name - desc:二级分组名称
     * @return array list[].child[].child - desc:商品
     * @return int list[].child[].child[].id - desc:商品ID
     * @return string list[].child[].child[].name - desc:商品名称
     */
    public function modulesProductList()
    {
        $param = $this->request->param();
        $param['module'] = $param['module'] ?? [];

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->moduleProductList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2023-10-16
     * @title 复制商品
     * @desc 复制商品
     * @url /admin/v1/product/:id/copy
     * @method POST
     * @author theworld
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param int product_group_id - desc:二级分组ID validate:optional
     */
    public function copy()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->copyProduct($param);
        return json($result);
    }

    /**
     * 时间 2024-07-02
     * @title 获取商品自定义标识配置
     * @desc 获取商品自定义标识配置
     * @url /admin/v1/product/:id/custom_host_name
     * @method GET
     * @author theworld
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return int custom_host_name - desc:自定义主机标识开关 0关闭 1开启
     * @return string custom_host_name_prefix - desc:自定义主机标识前缀
     * @return array custom_host_name_string_allow - desc:允许的字符串 number数字 upper大写字母 lower小写字母
     * @return int custom_host_name_string_length - desc:字符串长度
     */
    public function getCustomHostName()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->getCustomHostName($param['id'])
        ];
        return json($result);
    }

    /**
     * 时间 2024-07-02
     * @title 保存商品自定义标识配置
     * @desc 保存商品自定义标识配置
     * @url /admin/v1/product/:id/custom_host_name
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param int custom_host_name - desc:自定义主机标识开关 0关闭 1开启 validate:required
     * @param string custom_host_name_prefix - desc:自定义主机标识前缀 validate:required
     * @param array custom_host_name_string_allow - desc:允许的字符串 number数字 upper大写字母 lower小写字母 validate:required
     * @param int custom_host_name_string_length - desc:字符串长度 validate:required
     */
    public function saveCustomHostName()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('custom_host_name')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ProductModel = new ProductModel();
        $result = $ProductModel->saveCustomHostName($param);
        return json($result);
    }

     /**
     * 时间 2024-10-24
     * @title 同步镜像日志列表
     * @desc 同步镜像日志列表
     * @url /admin/v1/product/sync_image_log
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id create_time validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:同步镜像日志
     * @return int list[].id - desc:同步镜像日志ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].name - desc:商品名称
     * @return string list[].result - desc:同步结果
     * @return int list[].create_time - desc:同步时间
     * @return int count - desc:同步镜像日志总数
     */
    public function syncImageLogList()
    {
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new SyncImageLogModel())->logList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2024-10-24
     * @title 同步镜像
     * @desc 同步镜像
     * @url /admin/v1/product/sync_image
     * @method POST
     * @author theworld
     * @version v1
     * @param array product_id - desc:商品ID validate:optional
     */
    public function syncImage()
    {
        $param = $this->request->param();

        $SyncImageLogModel = new SyncImageLogModel();
        $result = $SyncImageLogModel->syncImage($param);
        return json($result);
    }

    /**
     * 时间 2025-02-12
     * @title 试用配置
     * @desc 试用配置
     * @url /admin/v1/product/:id/pay_ontrial
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param object pay_ontrial - desc:试用配置 validate:required
     * @param int pay_ontrial.status - desc:是否开启 validate:required
     * @param string pay_ontrial.cycle_type - desc:时长单位 hour day month validate:required
     * @param int pay_ontrial.cycle_num - desc:时长 validate:required
     * @param string pay_ontrial.client_limit - desc:用户限制 no不限制 new新用户 host用户必须存在激活中的产品 validate:required
     * @param array pay_ontrial.account_limit - desc:账户限制 多选 email绑定邮件 phone绑定手机 certification实名 validate:optional
     * @param array pay_ontrial.old_client_exclusive - desc:老用户专享 商品ID数组 validate:optional
     * @param int pay_ontrial.max - desc:单用户最大试用数量 validate:required
     */
    public function payOntrial()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('pay_ontrial')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ProductModel = new ProductModel();

        $result = $ProductModel->payOntrial($param);

        return json($result);
    }

    /**
     * 时间 2025-03-07
     * @title 全局通知管理列表
     * @desc 全局通知管理列表
     * @url /admin/v1/product/notice_group
     * @method GET
     * @author hh
     * @version v1
     * @param string type - desc:通知类型标识 validate:required
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string name - desc:搜索分组名称 validate:optional
     * @param string product_name - desc:搜索商品名称 validate:optional
     * @return int list[].id - desc:触发动作组ID
     * @return string list[].name - desc:触发动作组名称
     * @return int list[].is_default - desc:是否默认组 0否 1是
     * @return int list[].notice_setting.xxx - desc:对应触发动作状态 xxx动作标识 0关闭 1开启
     * @return int list[].product[].id - desc:商品ID
     * @return string list[].product[].name - desc:商品名称
     * @return int count - desc:总条数
     * @return string notice_type[].type - desc:通知类型标识
     * @return string notice_type[].name - desc:通知类型名称
     * @return string notice_setting[].name - desc:通知标识
     * @return string notice_setting[].name_lang - desc:通知名称
     */
    public function productNoticeGroupList()
    {
        $param = array_merge($this->request->param(), ['page'=>$this->request->page,'limit'=>$this->request->limit]);

        $ProductNoticeGroupModel = new ProductNoticeGroupModel();
        $data = $ProductNoticeGroupModel->productNoticeGroupList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-03-07
     * @title 创建触发动作组
     * @desc 创建触发动作组
     * @url /admin/v1/product/notice_group
     * @method POST
     * @author hh
     * @version v1
     * @param string type - desc:通知类型标识 validate:required
     * @param string name - desc:触发动作组名称 validate:required
     * @param int notice_setting.xxx - desc:对应触发动作状态 xxx动作标识 0关闭 1开启 validate:required
     * @param array product_id - desc:商品ID validate:optional
     * @return int id - desc:触发动作组ID
     */
    public function productNoticeGroupCreate()
    {
        $param = $this->request->param();

        $ProductNoticeGroupValidate = new ProductNoticeGroupValidate();
        if (!$ProductNoticeGroupValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductNoticeGroupValidate->getError())]);
        }

        $ProductNoticeGroupModel = new ProductNoticeGroupModel();
        $result = $ProductNoticeGroupModel->productNoticeGroupCreate($param);

        return json($result);
    }

    /**
     * 时间 2025-03-07
     * @title 修改触发动作组
     * @desc 修改触发动作组
     * @url /admin/v1/product/notice_group/:id
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:触发动作组ID validate:required
     * @param string name - desc:触发动作组名称 validate:required
     * @param int notice_setting.xxx - desc:对应触发动作状态 xxx动作标识 0关闭 1开启 validate:required
     * @param array product_id - desc:商品ID validate:optional
     */
    public function productNoticeGroupUpdate()
    {
        $param = $this->request->param();

        $ProductNoticeGroupValidate = new ProductNoticeGroupValidate();
        if (!$ProductNoticeGroupValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductNoticeGroupValidate->getError())]);
        }

        $ProductNoticeGroupModel = new ProductNoticeGroupModel();
        $result = $ProductNoticeGroupModel->productNoticeGroupUpdate($param);

        return json($result);
    }

    /**
     * 时间 2025-03-07
     * @title 删除触发动作组
     * @desc 删除触发动作组
     * @url /admin/v1/product/notice_group/:id
     * @method DELETE
     * @author hh
     * @version v1
     * @param int id - desc:触发动作组ID validate:required
     */
    public function productNoticeGroupDelete()
    {
        $param = $this->request->param();

        $ProductNoticeGroupValidate = new ProductNoticeGroupValidate();
        if (!$ProductNoticeGroupValidate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductNoticeGroupValidate->getError())]);
        }

        $ProductNoticeGroupModel = new ProductNoticeGroupModel();
        $result = $ProductNoticeGroupModel->productNoticeGroupDelete($param);

        return json($result);
    }

    /**
     * 时间 2025-03-07
     * @title 修改触发动作组动作状态
     * @desc 修改触发动作组动作状态
     * @url /admin/v1/product/notice_group/:id/act/status
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:触发动作组ID validate:required
     * @param string act - desc:动作标识 validate:required
     * @param int status - desc:状态 0否 1是 validate:required
     */
    public function productNoticeGroupUpdateActStatus()
    {
        $param = $this->request->param();

        $ProductNoticeGroupValidate = new ProductNoticeGroupValidate();
        if (!$ProductNoticeGroupValidate->scene('update_act')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductNoticeGroupValidate->getError())]);
        }

        $ProductNoticeGroupModel = new ProductNoticeGroupModel();
        $result = $ProductNoticeGroupModel->productNoticeGroupUpdateActStatus($param);

        return json($result);
    }

    /**
     * 时间 2025-04-01
     * @title 获取商品按需计费项目
     * @desc 获取商品按需计费项目
     * @url /admin/v1/product/:id/on_demand/billing_item
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return string list[].name - desc:配置项标识
     * @return string list[].value - desc:配置项名称
     */
    public function productOnDemandbillingItem()
    {
        $param = $this->request->param();
        $param['product_id'] = $param['id'];

        $ProductOnDemandModel = new ProductOnDemandModel();
        $data = $ProductOnDemandModel->billingItem($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-04-01
     * @title 商品按需计费配置详情
     * @desc 商品按需计费配置详情
     * @url /admin/v1/product/:id/on_demand
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return int product_id - desc:商品ID
     * @return string billing_cycle_unit - desc:出账周期单位 hour每小时 day每天 month每月
     * @return int billing_cycle_day - desc:出账周期号数
     * @return string billing_cycle_point - desc:出账周期时间点 如00:00
     * @return int duration_id - desc:周期ID
     * @return string duration_ratio - desc:周期比例
     * @return string min_credit - desc:购买时用户最低余额
     * @return int min_usage_time - desc:最低使用时长
     * @return string min_usage_time_unit - desc:最低使用时长单位 second秒 minute分 hour小时
     * @return int upgrade_min_billing_time - desc:升降级最低计费时长
     * @return string upgrade_min_billing_time_unit - desc:升降级最低计费时长单位 second秒 minute分 hour小时
     * @return int grace_time - desc:宽限期
     * @return string grace_time_unit - desc:宽限期单位 hour小时 day天
     * @return int keep_time - desc:保留期
     * @return string keep_time_unit - desc:保留期单位 hour小时 day天
     * @return array keep_time_billing_item - desc:保留计费项目标识
     * @return string initial_fee - desc:初装费
     * @return int client_auto_delete - desc:允许用户设置自动释放 0否 1是
     * @return int on_demand_to_duration - desc:允许按需转包年包月 0否 1是
     * @return int duration_to_on_demand - desc:允许包年包月/试用转按需 0否 1是
     * @return int credit_limit_pay - desc:允许信用额支付 0否 1是
     */
    public function productOnDemandIndex()
    {
        $param = $this->request->param();

        $ProductOnDemandModel = new ProductOnDemandModel();
        $data = $ProductOnDemandModel->productOnDemandIndex($param['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2025-03-27
     * @title 修改商品按需计费配置
     * @desc 修改商品按需计费配置
     * @url /admin/v1/product/:id/on_demand
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param string billing_cycle_unit - desc:出账周期单位 hour每小时 day每天 month每月 validate:required
     * @param int billing_cycle_day - desc:出账周期号数 billing_cycle_unit=month时必填 validate:optional
     * @param string billing_cycle_point - desc:出账周期时间点 如00:00 billing_cycle_unit=day/month时必填 validate:optional
     * @param int duration_id - desc:周期ID validate:optional
     * @param float duration_ratio - desc:周期比例 duration_id>0时必填 validate:optional
     * @param float min_credit - desc:购买时用户最低余额 validate:required
     * @param int min_usage_time - desc:最低使用时长 validate:required
     * @param string min_usage_time_unit - desc:最低使用时长单位 second秒 minute分 hour小时 validate:required
     * @param int upgrade_min_billing_time - desc:升降级最低计费时长 validate:optional
     * @param string upgrade_min_billing_time_unit - desc:升降级最低计费时长单位 second秒 minute分 hour小时 validate:optional
     * @param int grace_time - desc:宽限期 validate:required
     * @param string grace_time_unit - desc:宽限期单位 hour小时 day天 validate:required
     * @param int keep_time - desc:保留期 validate:required
     * @param string keep_time_unit - desc:保留期单位 hour小时 day天 validate:required
     * @param array keep_time_billing_item - desc:保留计费项目标识 validate:optional
     * @param float initial_fee - desc:初装费 validate:optional
     * @param int client_auto_delete - desc:允许用户设置自动释放 0否 1是 validate:required
     * @param int on_demand_to_duration - desc:允许按需转包年包月 0否 1是 validate:required
     * @param int duration_to_on_demand - desc:允许包年包月/试用转按需 0否 1是 validate:required
     * @param int credit_limit_pay - desc:允许信用额支付 0否 1是 validate:required
     */
    public function productOnDemandUpdate()
    {
        $param = $this->request->param();

        $ProductOnDemandValidate = new ProductOnDemandValidate();
        if (!$ProductOnDemandValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductOnDemandValidate->getError())]);
        }
        $param['product_id'] = $param['id'];

        $ProductOnDemandModel = new ProductOnDemandModel();
        $result = $ProductOnDemandModel->productOnDemandUpdate($param);

        return json($result);
    }

    /**
     * 时间 2026-01-05
     * @title 修改商品自然月预付费开关
     * @desc 修改商品自然月预付费开关
     * @url /admin/v1/product/:id/natural_month_prepaid
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param int natural_month_prepaid - desc:自然月预付费开关 0关闭 1开启 validate:required
     */
    public function naturalMonthPrepaid()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('natural_month_prepaid')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ProductModel = new ProductModel();
        $result = $ProductModel->updateNaturalMonthPrepaid($param);

        return json($result);
    }

}


<?php
namespace app\admin\controller;

use app\common\model\UpstreamProductModel;
use app\admin\validate\UpstreamProductValidate;
use app\common\logic\UpstreamLogic;

/**
 * @title 上下游商品(后台)
 * @desc 上下游商品(后台)
 * @use app\admin\controller\UpstreamProductController
 */
class UpstreamProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new UpstreamProductValidate();
    }

    /**
     * 时间 2023-02-13
     * @title 商品列表
     * @desc 商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:商品名称 validate:optional
     * @param int supplier_id - desc:供应商ID validate:optional
     * @param string mode - desc:代理模式 only_api仅调用接口 sync同步商品 validate:optional
     * @param int need_manual_sync - desc:需要手动同步资源 0否 1是 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:商品列表
     * @return int list[].id - desc:商品ID
     * @return string list[].name - desc:商品名称
     * @return string list[].description - desc:商品描述
     * @return int list[].supplier_id - desc:供应商ID
     * @return string list[].supplier_name - desc:供应商名称
     * @return int list[].profit_type - desc:利润方式 0百分比 1固定金额
     * @return string list[].profit_percent - desc:利润百分比
     * @return int list[].auto_setup - desc:是否自动开通 1是 0否
     * @return int list[].hidden - desc:是否隐藏 0显示 1隐藏
     * @return string list[].pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return string list[].price - desc:商品最低价格
     * @return string list[].upstream_price - desc:上游原价 已转换汇率
     * @return string list[].cycle - desc:商品最低周期
     * @return int list[].upstream_product_id - desc:上游商品ID
     * @return int list[].certification - desc:本地实名购买 0关闭 1开启
     * @return string list[].product_group_name_second - desc:二级分组名称
     * @return int list[].product_group_id_second - desc:二级分组ID
     * @return string list[].product_group_name_first - desc:一级分组名称
     * @return int list[].product_group_id_first - desc:一级分组ID
     * @return int list[].renew_profit_percent - desc:续费利润百分比或固定金额
     * @return int list[].renew_profit_type - desc:续费利润方式 0百分比 1固定金额
     * @return int list[].upgrade_profit_percent - desc:升降级利润百分比或固定金额
     * @return int list[].upgrade_profit_type - desc:升降级利润方式 0百分比 1固定金额
     * @return int list[].sync - desc:是否同步商品的可升降级商品
     * @return string list[].mode - desc:代理模式 only_api仅调用接口 sync同步商品
     * @return int list[].need_manual_sync - desc:需要手动同步资源 0否 1是
     * @return int count - desc:商品总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 获取上游商品列表
        $data = $UpstreamProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 商品详情
     * @desc 商品详情
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product/:id
     * @method GET
     * @param int id - desc:商品ID validate:required
     * @return object product - desc:商品
     * @return int product.id - desc:商品ID
     * @return string product.name - desc:商品名称
     * @return string product.description - desc:商品描述
     * @return int product.supplier_id - desc:供应商ID
     * @return string product.supplier_name - desc:供应商名称
     * @return string product.profit_percent - desc:利润百分比
     * @return int product.profit_type - desc:利润方式 0百分比 1固定金额
     * @return int product.auto_setup - desc:是否自动开通 1是 0否
     * @return int product.hidden - desc:是否隐藏 0显示 1隐藏
     * @return string product.pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return string product.price - desc:商品最低价格
     * @return string product.upstream_price - desc:上游原价 已转换汇率
     * @return string product.cycle - desc:商品最低周期
     * @return int product.upstream_product_id - desc:上游商品ID
     * @return int product.certification - desc:本地实名购买 0关闭 1开启
     * @return string product.product_group_name_second - desc:二级分组名称
     * @return int product.product_group_id_second - desc:二级分组ID
     * @return string product.product_group_name_first - desc:一级分组名称
     * @return int product.product_group_id_first - desc:一级分组ID
     * @return string product.renew_profit_percent - desc:续费利润百分比
     * @return int product.renew_profit_type - desc:续费利润方式 0百分比 1固定金额
     * @return string product.upgrade_profit_percent - desc:升降级利润百分比
     * @return int product.upgrade_profit_type - desc:升降级利润方式 0百分比 1固定金额
     * @return int need_manual_sync - desc:需要手动同步资源 0否 1是
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 获取商品
        $product = $UpstreamProductModel->indexProduct($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'product' => $product,
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 添加商品
     * @desc 添加商品
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product
     * @method POST
     * @param int supplier_id - desc:供应商ID validate:required
     * @param int upstream_product_id - desc:上游商品ID validate:required
     * @param string name - desc:商品名称 validate:required
     * @param string description - desc:商品描述 validate:optional
     * @param float profit_percent - desc:利润百分比 validate:required
     * @param int profit_type - desc:利润方式 0百分比 1固定金额 validate:required
     * @param int auto_setup - desc:是否自动开通 1是 0否 validate:required
     * @param int certification - desc:本地实名购买 0关闭 1开启 validate:required
     * @param int product_group_id - desc:二级分组ID validate:required
     * @param boolean sync - desc:是否代理升降级商品 0否 1是 validate:required
     * @param float renew_profit_percent - desc:续费利润百分比 validate:required
     * @param int renew_profit_type - desc:续费利润方式 0百分比 1固定金额 validate:required
     * @param float upgrade_profit_percent - desc:升降级利润百分比 validate:required
     * @param int upgrade_profit_type - desc:升降级利润方式 0百分比 1固定金额 validate:required
     * @param string mode - desc:代理模式 only_api仅调用接口 sync同步商品 validate:required
     * @param string price_basis - desc:价格基础 standard标准价 agent代理价 默认standard validate:required
     * @return int need_manual_sync - desc:需要手动同步模块/插件 1是 0否
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 新建商品
        $result = $UpstreamProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 编辑商品
     * @desc 编辑商品
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product/:id
     * @method PUT
     * @param int id - desc:商品ID validate:required
     * @param int supplier_id - desc:供应商ID validate:required
     * @param int upstream_product_id - desc:上游商品ID validate:required
     * @param string name - desc:商品名称 validate:required
     * @param string description - desc:商品描述 validate:optional
     * @param float profit_percent - desc:利润百分比 validate:required
     * @param int profit_type - desc:利润方式 0百分比 1固定金额 validate:required
     * @param int auto_setup - desc:是否自动开通 1是 0否 validate:required
     * @param int certification - desc:本地实名购买 0关闭 1开启 validate:required
     * @param int product_group_id - desc:二级分组ID validate:required
     * @param boolean sync - desc:是否代理升降级商品 0否 1是 validate:required
     * @param float renew_profit_percent - desc:续费利润百分比 validate:required
     * @param int renew_profit_type - desc:续费利润方式 0百分比 1固定金额 validate:required
     * @param float upgrade_profit_percent - desc:升降级利润百分比 validate:required
     * @param int upgrade_profit_type - desc:升降级利润方式 0百分比 1固定金额 validate:required
     * @param string mode - desc:代理模式 only_api仅调用接口 sync同步商品 validate:required
     * @param string price_basis - desc:价格基础 standard标准价 agent代理价 默认standard validate:required
     * @return int need_manual_sync - desc:需要手动同步模块/插件 1是 0否
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 修改商品
        $result = $UpstreamProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 推荐代理商品列表
     * @desc 推荐代理商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/recommend/product
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:商品名称 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:推荐商品列表
     * @return int list[].id - desc:推荐商品ID
     * @return int list[].upstream_product_id - desc:上游商品ID
     * @return string list[].name - desc:商品名称
     * @return string list[].type - desc:供应商类型 default默认业务系统 whmcs财务系统 finance魔方财务
     * @return string list[].supplier_name - desc:供应商名称
     * @return string list[].login_url - desc:前台网站地址
     * @return string list[].url - desc:接口地址
     * @return string list[].pay_type - desc:付款类型 free免费 onetime一次 recurring_prepayment周期先付 recurring_postpaid周期后付
     * @return string list[].price - desc:商品最低价格
     * @return string list[].cycle - desc:商品最低周期
     * @return int list[].cpu_min - desc:CPU(核)最小值
     * @return int list[].cpu_max - desc:CPU(核)最大值
     * @return int list[].memory_min - desc:内存(GB)最小值
     * @return int list[].memory_max - desc:内存(GB)最大值
     * @return int list[].disk_min - desc:硬盘(GB)最小值
     * @return int list[].disk_max - desc:硬盘(GB)最大值
     * @return int list[].bandwidth_min - desc:带宽(Mbps)最小值
     * @return int list[].bandwidth_max - desc:带宽(Mbps)最大值
     * @return int list[].flow_min - desc:流量(G)最小值
     * @return int list[].flow_max - desc:流量(G)最大值
     * @return string list[].description - desc:简介
     * @return int list[].agent - desc:是否已代理 0否 1是
     * @return object list[].supplier - desc:供应商 已添加时有数据
     * @return object list[].supplier.id - desc:供应商ID
     * @return object list[].supplier.username - desc:上游账户名
     * @return object list[].supplier.token - desc:API密钥
     * @return object list[].supplier.secret - desc:API私钥
     * @return int count - desc:推荐商品总数
     */
    public function recommendProductList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamLogic = new UpstreamLogic();

        // 获取推荐代理商品列表
        $data = $UpstreamLogic->recommendProductList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 代理推荐商品添加供应商
     * @desc 代理推荐商品添加供应商
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/recommend/product
     * @method POST
     * @param int id - desc:推荐代理商品ID validate:required
     * @param string username - desc:用户名 validate:required
     * @param string token - desc:API密钥 validate:required
     * @param string secret - desc:API私钥 validate:required
     * @return int supplier_id - desc:供应商ID
     */
    public function agentRecommendProduct()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('agent')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 代理推荐商品
        $result = $UpstreamProductModel->agentRecommendProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-08-15
     * @title 代理模式为同步时，手动同步
     * @desc 代理模式为同步时，手动同步
     * @author wyh
     * @version v1
     * @url /admin/v1/upstream/product/:id/sync
     * @method GET
     * @param int id - desc:商品ID validate:required
     */
    public function manualSync()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 强制同步
        $param['force'] = 1;

        $result = $UpstreamProductModel->manualSync($param);

        return json($result);
    }

    /**
     * 时间 2025-12-19
     * @title 手动同步代理资源
     * @desc 手动同步代理资源
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product/:id/sync_resource
     * @method GET
     * @param int id - desc:商品ID validate:required
     */
    public function manualSyncResource()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();


        $result = $UpstreamProductModel->manualSyncResource(intval($param['id']));

        return json($result);
    }
}
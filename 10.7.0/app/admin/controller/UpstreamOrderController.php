<?php
namespace app\admin\controller;

use app\common\model\UpstreamOrderModel;

/**
 * @title 上下游(后台)
 * @desc 上下游(后台)
 * @use app\admin\controller\UpstreamOrderController
 */
class UpstreamOrderController extends AdminBaseController
{
    /**
     * 时间 2023-02-13
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/order
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:ID 用户名称 邮箱 手机号 商品名称 产品标识 validate:optional
     * @param int supplier_id - desc:供应商ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:订单列表
     * @return int list[].id - desc:订单ID
     * @return string list[].type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return int list[].create_time - desc:创建时间
     * @return string list[].amount - desc:金额
     * @return string list[].profit - desc:利润
     * @return string list[].status - desc:状态 Unpaid未付款 Paid已付款 Cancelled已取消 Refunded已退款
     * @return string list[].gateway - desc:支付方式
     * @return string list[].credit - desc:使用余额 大于0代表订单使用了余额 和金额相同代表订单支付方式为余额
     * @return string list[].description - desc:描述
     * @return int list[].client_id - desc:用户ID
     * @return string list[].client_name - desc:用户名称
     * @return string list[].email - desc:邮箱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return string list[].company - desc:公司
     * @return string list[].product_name - desc:商品名称
     * @return string list[].host_name - desc:产品标识
     * @return array list[].product_names - desc:订单下所有产品的商品名称
     * @return int list[].host_id - desc:产品ID
     * @return int list[].order_item_count - desc:订单子项数量
     * @return string list[].gateway_sign - desc:支付接口标识 credit余额 credit_limit信用额
     * @return int count - desc:订单总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamOrderModel = new UpstreamOrderModel();

        // 获取上游订单列表
        $data = $UpstreamOrderModel->orderList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 销售信息
     * @desc 销售信息
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/sell_info
     * @method GET
     * @param int supplier_id - desc:供应商ID validate:optional
     * @return string total - desc:总销售额
     * @return string profit - desc:总利润
     * @return int product_count - desc:商品总数
     * @return int host_count - desc:产品总数
     */
    public function sellInfo()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamOrderModel = new UpstreamOrderModel();

        // 获取销售信息
        $data = $UpstreamOrderModel->sellInfo($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}
<?php
namespace app\home\controller;

use app\common\model\OrderModel;
use app\common\model\SelfDefinedFieldModel;
use app\admin\validate\OrderValidate;

/**
 * @title 订单管理
 * @desc 订单管理
 * @use app\home\controller\OrderController
 */
class OrderController extends HomeBaseController
{
    /**
     * 时间 2022-05-19
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @url /console/v1/order
     * @method GET
     * @param string keywords - desc:关键字 搜索范围订单ID validate:optional
     * @param string type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Paid已付款 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id type create_time amount status validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:订单列表
     * @return int list[].id - desc:订单ID
     * @return string list[].type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return int list[].create_time - desc:创建时间
     * @return string list[].amount - desc:金额
     * @return string list[].status - desc:状态 Unpaid未付款 Paid已付款 WaitUpload待上传 WaitReview待审核 ReviewFail审核失败
     * @return string list[].gateway - desc:支付方式
     * @return float list[].credit - desc:使用余额 大于0代表订单使用了余额和金额相同代表订单支付方式为余额
     * @return string list[].host_name - desc:产品标识
     * @return string list[].description - desc:描述
     * @return array list[].product_names - desc:订单下所有产品的商品名称
     * @return int list[].host_id - desc:产品ID
     * @return int list[].order_item_count - desc:订单子项数量
     * @return array list[].voucher - desc:上传的凭证
     * @return string list[].review_fail_reason - desc:审核失败原因
     * @return int count - desc:订单总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $OrderModel = new OrderModel();

        // 获取订单列表
        $data = $OrderModel->orderList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-19
     * @title 订单详情
     * @desc 订单详情
     * @author theworld
     * @version v1
     * @url /console/v1/order/:id
     * @method GET
     * @param int id - desc:订单ID validate:required
     * @return object order - desc:产品
     * @return int order.id - desc:订单ID
     * @return string order.type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return string order.amount - desc:金额
     * @return int order.create_time - desc:创建时间
     * @return int order.pay_time - desc:支付时间
     * @return string order.status - desc:状态 Unpaid未付款 Paid已付款 WaitUpload待上传 WaitReview待审核 ReviewFail审核失败
     * @return string order.gateway - desc:支付方式
     * @return string order.credit - desc:使用余额 大于0代表订单使用了余额和金额相同代表订单支付方式为余额
     * @return string order.notes - desc:备注
     * @return string order.refund_amount - desc:订单已退款金额
     * @return string order.amount_unpaid - desc:未支付金额
     * @return array order.voucher - desc:上传的凭证
     * @return string order.review_fail_reason - desc:审核失败原因
     * @return int order.unpaid_timeout - desc:未支付超时时间 0表示不限制
     * @return int order.remain_pay_time - desc:剩余支付时间 配合unpaid_timeout使用
     * @return array order.items - desc:订单子项
     * @return int order.items[].id - desc:订单子项ID
     * @return string order.items[].description - desc:描述
     * @return string order.items[].amount - desc:金额
     * @return int order.items[].host_id - desc:产品ID
     * @return string order.items[].product_name - desc:商品名称
     * @return string order.items[].host_name - desc:产品标识
     * @return string order.items[].billing_cycle - desc:计费周期
     * @return string order.items[].host_status - desc:产品状态 Unpaid未付款 Pending开通中 Active使用中 Suspended暂停 Deleted删除 Failed开通失败
     * @return int self_defined_field[].id - desc:自定义字段ID
     * @return string self_defined_field[].field_name - desc:字段名称
     * @return string self_defined_field[].field_type - desc:字段类型 text文本框 link链接 password密码 dropdown下拉 checkbox勾选框 textarea文本区
     * @return string self_defined_field[].value - desc:当前值
     */
	public function index()
    {
		// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $OrderModel = new OrderModel();
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        // 获取订单
        $order = $OrderModel->indexOrder($param['id']);
        if(isset($order->id)){
            $selfDefinedField = $SelfDefinedFieldModel->showOrderDetailField(['order_id'=>$param['id']]);
        }

        // 返回自定义字段
        $hookResult = hook('order_create_return_customfield',['order_id'=>$param['id']]);
        $customfields = [];
        foreach ($hookResult as $value){
            $customfields = array_merge($customfields,$value);
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'order'             => $order,
                'self_defined_field'=> $selfDefinedField ?? [],
                'customfields' => $customfields,
            ]
        ];
        return json($result);
	}

    /**
     * 时间 2022-10-18
     * @title 删除订单
     * @desc 删除订单
     * @author theworld
     * @version v1
     * @url /console/v1/order/:id
     * @method DELETE
     * @param int id - desc:订单ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 取消订单
        $result = $OrderModel->cancelOrder($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-10-18
     * @title 批量删除订单
     * @desc 批量删除订单
     * @author theworld
     * @version v1
     * @url /console/v1/order
     * @method DELETE
     * @param array id - desc:订单ID validate:required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();

        // 取消订单
        $result = $OrderModel->batchCancelOrder($param);

        return json($result);
    }

    /**
     * 时间 2023-06-08
     * @title 订单列表导出EXCEL
     * @desc 订单列表导出EXCEL
     * @author theworld
     * @version v1
     * @url /console/v1/order/export_excel
     * @method GET
     * @param string keywords - desc:关键字 搜索范围订单ID validate:optional
     * @param string type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Paid已付款 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id type create_time amount status validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     */
    public function exportExcel()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $OrderModel = new OrderModel();

        // 订单列表导出EXCEL
        return $OrderModel->exportExcel($param);
    }

    /**
     * @时间 2024-07-19
     * @title 银行转账提交申请
     * @desc  银行转账提交申请
     * @author hh
     * @version v1
     * @url /console/v1/order/:id/submit_application
     * @method POST
     * @param int id - desc:订单ID validate:required
     */
    public function submitApplication()
    {
        $param = $this->request->param();
        
        $OrderModel = new OrderModel();

        $result = $OrderModel->submitApplication($param);
        return json($result);
    }

    /**
     * @时间 2024-07-19
     * @title 上传凭证
     * @desc  上传凭证
     * @author hh
     * @version v1
     * @url /console/v1/order/:id/voucher
     * @method PUT
     * @param int id - desc:订单ID validate:required
     * @param array voucher - desc:上传的凭证 上传后的文件名 validate:required
     */
    public function uploadOrderVoucher()
    {
        $param = $this->request->param();
        
        // 参数验证
        $OrderValidate = new OrderValidate();
        if (!$OrderValidate->scene('upload_voucher')->check($param)){
            return json(['status' => 400 , 'msg' => lang($OrderValidate->getError())]);
        }

        $OrderModel = new OrderModel();

        $result = $OrderModel->uploadOrderVoucher($param);
        return json($result);
    }

    /**
     * @时间 2024-07-22
     * @title 变更支付方式
     * @desc  变更支付方式
     * @author hh
     * @version v1
     * @url /console/v1/order/:id/gateway
     * @method PUT
     * @param int id - desc:订单ID validate:required
     */
    public function changeGateway()
    {
        $param = $this->request->param();
        
        $OrderModel = new OrderModel();

        $result = $OrderModel->changeGateway($param);
        return json($result);
    }

    /**
     * @时间 2024-12-03
     * @title 订单交易记录
     * @desc  订单交易记录
     * @author hh
     * @version v1
     * @url /console/v1/order/:id/transaction_record
     * @method GET
     * @param int id - desc:订单ID validate:required
     * @return int list[].create_time - desc:交易时间
     * @return int list[].host_id - desc:产品ID
     * @return string list[].host_name - desc:产品标识
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].description - desc:描述
     * @return string list[].amount - desc:金额
     */
    public function orderTransactionRecord()
    {
        $param = $this->request->param();
        
        $OrderModel = new OrderModel();

        $data = $OrderModel->orderTransactionRecord($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data,
        ];

        return json($result);
    }

    /**
     * @时间 2025-04-11
     * @title 合并按需订单
     * @desc  合并按需订单
     * @author hh
     * @version v1
     * @url /console/v1/order/on_demand/combine
     * @method POST
     * @param array ids - desc:订单ID 数组 validate:required
     * @return int id - desc:合并后的订单ID
     * @return string amount - desc:合并后的订单金额
     */
    public function combineOnDemandOrder()
    {
        $param = $this->request->param();
        
        // 参数验证
        $OrderValidate = new OrderValidate();
        if (!$OrderValidate->scene('combine')->check($param)){
            return json(['status' => 400 , 'msg' => lang($OrderValidate->getError())]);
        }

        $OrderModel = new OrderModel();

        $result = $OrderModel->combineOnDemandOrder($param);
        return json($result);
    }

}
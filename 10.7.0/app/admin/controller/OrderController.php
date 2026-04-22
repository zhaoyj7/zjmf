<?php
namespace app\admin\controller;

use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\RefundRecordModel;
use app\admin\validate\OrderValidate;
use app\admin\validate\ConfigurationValidate;
use app\home\validate\ProductValidate;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\ConfigurationModel;
use app\admin\validate\OrderRefundValidate;

/**
 * @title 订单管理
 * @desc 订单管理
 * @use app\admin\controller\OrderController
 */
class OrderController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new OrderValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 订单列表
     * @desc 订单列表
     * @url /admin/v1/order
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字,搜索范围:订单ID,商品名称,用户名称,邮箱,手机号 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param string type - desc:类型,new新订单,renew续费订单,upgrade升降级订单,artificial人工订单 validate:optional
     * @param string status - desc:状态,Unpaid未付款,Paid已付款 validate:optional
     * @param string amount - desc:金额 validate:optional
     * @param array gateway - desc:支付方式 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int order_id - desc:订单ID validate:optional
     * @param int product_id - desc:商品ID validate:optional
     * @param string username - desc:用户名称 validate:optional
     * @param string email - desc:邮箱 validate:optional
     * @param string phone - desc:手机号 validate:optional
     * @param int pay_time - desc:支付时间 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段,id,amount,client_id,reg_time validate:optional
     * @param string sort - desc:升降序,asc,desc validate:optional
     * @param int start_pay_time - desc:搜索:开始支付时间 validate:optional
     * @param int end_pay_time - desc:搜索:结束支付时间 validate:optional
     * @param string addon_client_custom_field_[num] - desc:搜索:用户自定义字段,[num]为自定义字段ID validate:optional
     * @return array list - desc:订单列表
     * @return int list[].id - desc:订单ID
     * @return string list[].type - desc:类型,new新订单,renew续费订单,upgrade升降级订单,artificial人工订单
     * @return int list[].create_time - desc:创建时间
     * @return string list[].amount - desc:金额
     * @return string list[].status - desc:状态,Unpaid未付款,Paid已付款,Cancelled已取消,Refunded已退款,WaitUpload待上传,WaitReview待审核,ReviewFail审核失败
     * @return string list[].gateway - desc:支付方式
     * @return float list[].credit - desc:使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额
     * @return int list[].client_id - desc:用户ID
     * @return string list[].client_name - desc:用户名称
     * @return string list[].client_credit - desc:用户余额
     * @return string list[].email - desc:邮箱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return string list[].company - desc:公司
     * @return int list[].client_status - desc:用户是否启用,0禁用,1正常
     * @return int list[].reg_time - desc:用户注册时间
     * @return string list[].country - desc:国家
     * @return string list[].address - desc:地址
     * @return string list[].language - desc:语言
     * @return string list[].notes - desc:备注
     * @return string list[].refund_amount - desc:订单已退款金额
     * @return string list[].host_name - desc:产品标识
     * @return string list[].description - desc:描述
     * @return array list[].product_names - desc:订单下所有产品的商品名称
     * @return int list[].host_id - desc:产品ID
     * @return int list[].order_item_count - desc:订单子项数量
     * @return bool list[].certification - desc:是否实名认证,true是,false否,显示字段有certification返回
     * @return string list[].certification_type - desc:实名类型,person个人,company企业,显示字段有certification返回
     * @return string list[].client_level - desc:用户等级,显示字段有client_level返回
     * @return string list[].client_level_color - desc:用户等级颜色,显示字段有client_level返回
     * @return string list[].sale - desc:邀售,显示字段有sale返回
     * @return string list[].addon_client_custom_field_[id] - desc:用户自定义字段,显示字段有addon_client_custom_field_[id]返回,[id]为用户自定义字段ID
     * @return array list[].voucher - desc:上传的凭证
     * @return string list[].review_fail_reason - desc:审核失败原因
     * @return string list[].gateway_sign - desc:支付方式标识,credit=余额
     * @return string list[].order_invoice_status - desc:开票状态,显示字段有order_invoice_status返回
     * @return int count - desc:订单总数
     * @return string total_amount - desc:总金额
     * @return string page_total_amount - desc:当前页总金额
     */
	public function orderList()
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
     * 时间 2022-05-17
     * @title 订单详情
     * @desc 订单详情
     * @url /admin/v1/order/:id
     * @method GET
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @return object order - desc:订单
     * @return int order.id - desc:订单ID
     * @return string order.type - desc:类型,new新订单,renew续费订单,upgrade升降级订单,artificial人工订单
     * @return string order.amount - desc:金额
     * @return int order.create_time - desc:创建时间
     * @return string order.status - desc:状态,Unpaid未付款,Paid已付款,Cancelled已取消,Refunded已退款,WaitUpload待上传,WaitReview待审核,ReviewFail审核失败
     * @return string order.gateway - desc:支付方式
     * @return string order.credit - desc:使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额
     * @return int order.client_id - desc:用户ID
     * @return string order.client_name - desc:用户名称
     * @return string order.notes - desc:备注
     * @return string order.refund_amount - desc:订单已退款金额
     * @return string order.amount_unpaid - desc:未支付金额
     * @return string order.refundable_amount - desc:订单可退款金额
     * @return string order.apply_credit_amount - desc:订单可应用余额金额
     * @return int order.admin_id - desc:管理员ID
     * @return string order.admin_name - desc:管理员名称
     * @return int order.is_recycle - desc:是否在回收站,0否,1是
     * @return int order.refund_orginal - desc:订单支付方式退款时是否支持原路返回,1是,0否
     * @return array order.voucher - desc:上传的凭证
     * @return string order.review_fail_reason - desc:审核失败原因
     * @return string order.refund_credit - desc:已退款余额
     * @return string order.refund_gateway - desc:已退款渠道
     * @return string order.gateway_sign - desc:支付接口标识,credit=余额,credit_limit=信用额
     * @return array order.items - desc:订单子项
     * @return int order.items[].id - desc:订单子项ID
     * @return string order.items[].description - desc:描述
     * @return string order.items[].amount - desc:金额
     * @return int order.items[].host_id - desc:产品ID
     * @return string order.items[].product_name - desc:商品名称
     * @return string order.items[].host_name - desc:产品标识
     * @return string order.items[].billing_cycle - desc:计费周期
     * @return string order.items[].host_status - desc:产品状态,Unpaid未付款,Pending开通中,Active使用中,Suspended暂停,Deleted删除,Failed开通失败
     * @return int order.items[].edit - desc:是否可编辑,1是,0否
     * @return string order.items[].profit - desc:利润
     * @return int order.items[].agent - desc:代理订单,1是,0否
     * @return int self_defined_field[].id - desc:自定义字段ID
     * @return string self_defined_field[].field_name - desc:字段名称
     * @return string self_defined_field[].field_type - desc:字段类型,text=文本框,link=链接,password=密码,dropdown=下拉,checkbox=勾选框,textarea=文本区
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
        $selfDefinedField = $SelfDefinedFieldModel->showOrderDetailField(['order_id'=>$param['id']]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'order'             => $order,
                'self_defined_field'=> $selfDefinedField,
            ]
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 新建订单
     * @desc 新建订单
     * @url /admin/v1/order
     * @method POST
     * @author theworld
     * @version v1
     * @param string type - desc:类型,new新订单,renew续费订单,artificial人工订单 validate:required
     * @param array products - desc:商品,类型为新订单时需要 validate:optional
     * @param int products[].product_id - desc:商品ID validate:optional
     * @param object products[].config_options - desc:自定义配置 validate:optional
     * @param int products[].qty - desc:数量 validate:optional
     * @param float products[].price - desc:商品价格 validate:optional
     * @param object products[].customfield - desc:自定义字段 validate:optional
     * @param int id - desc:产品ID,类型为续费订单时需要 validate:optional
     * @param float amount - desc:金额,类型为人工订单时需要 validate:optional
     * @param string description - desc:描述,类型为人工订单时需要 validate:optional
     * @param int client_id - desc:用户ID validate:required
     * @param object customfield - desc:自定义字段 validate:optional
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
        $OrderModel = new OrderModel();
        
        // 新建订单
        $result = $OrderModel->createOrder($param);

        return json($result);
	}

    /**
     * 时间 2022-07-01
     * @title 获取升降级订单金额
     * @desc 获取升降级订单金额
     * @url /admin/v1/order/upgrade/amount
     * @method POST
     * @author theworld
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @param object product - desc:升降级商品 validate:required
     * @param int product.product_id - desc:商品ID validate:optional
     * @param object product.config_options - desc:自定义配置 validate:optional
     * @param float product.price - desc:商品价格 validate:optional
     * @param int client_id - desc:用户ID validate:required
     * @return string refund - desc:原产品应退款金额
     * @return string pay - desc:新产品应付金额
     * @return string amount - desc:升降级订单金额,前两者之差
     */
    public function getUpgradeAmount()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('upgrade')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 获取升降级订单金额
        $result = $OrderModel->getUpgradeAmount($param);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 调整订单金额
     * @desc 调整订单金额
     * @url /admin/v1/order/:id/amount
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param float amount - desc:金额 validate:required
     * @param string description - desc:描述 validate:required
     */
	public function updateAmount()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('amount')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateAmount($param);

        return json($result);
	}


    /**
     * 时间 2022-05-17
     * @title 编辑人工调整的订单子项
     * @desc 编辑人工调整的订单子项
     * @url /admin/v1/order/item/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:订单子项ID validate:required
     * @param float amount - desc:金额 validate:required
     * @param string description - desc:描述 validate:required
     */
    public function updateOrderItem()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('amount')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateOrderItem($param);

        return json($result);
    }

    /**
     * 时间 2023-01-30
     * @title 删除人工调整的订单子项
     * @desc 删除人工调整的订单子项
     * @url /admin/v1/order/item/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:订单子项ID validate:required
     */
    public function deleteOrderItem()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->deleteOrderItem($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 标记支付
     * @desc 标记支付
     * @url /admin/v1/order/:id/status/paid
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param string transaction_number - desc:交易流水号 validate:optional
     */
	public function paid()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('paid')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单标记支付
        $result = $OrderModel->orderPaid($param);

        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 删除订单
     * @desc 删除订单
     * @url /admin/v1/order/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param int delete_host - desc:是否删除产品,0否,1是 validate:required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 删除订单
        $result = $OrderModel->deleteOrder($param);

        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 批量删除订单
     * @desc 批量删除订单
     * @url /admin/v1/order
     * @method DELETE
     * @author theworld
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     * @param int delete_host - desc:是否删除产品,0否,1是 validate:required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 删除订单
        $result = $OrderModel->batchDeleteOrder($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款
     * @desc 订单退款
     * @url /admin/v1/order/:id/refund
     * @method POST
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param int host_id - desc:产品ID validate:optional
     * @param float amount - desc:退款金额 validate:required
     * @param string type - desc:退款类型,credit_first=余额优先,gateway_first=渠道优先,credit=余额,transaction=支付接口 validate:required
     * @param string notes - desc:备注 validate:optional
     * @param string gateway - desc:支付接口 validate:optional
     * @return int id - desc:退款记录ID
     */
    public function orderRefund()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        $OrderRefundValidate = new OrderRefundValidate();
        if (!$OrderRefundValidate->scene('refund')->check($param)){
            return json(['status' => 400 , 'msg' => lang($OrderRefundValidate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单退款
        $result = $OrderModel->orderRefund($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单应用余额
     * @desc 订单应用余额
     * @url /admin/v1/order/:id/apply_credit
     * @method POST
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param float amount - desc:金额 validate:required
     * @param string status - desc:状态,Refunded已退款,Paid已付款,订单状态为已退款时需传 validate:optional
     */
    public function orderApplyCredit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('apply')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单应用余额
        $result = $OrderModel->orderApplyCredit($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单扣除余额
     * @desc 订单扣除余额
     * @url /admin/v1/order/:id/remove_credit
     * @method POST
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param float amount - desc:金额 validate:required
     */
    public function orderRemoveCredit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('remove')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单扣除余额
        $result = $OrderModel->orderRemoveCredit($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款记录列表
     * @desc 订单退款记录列表
     * @url /admin/v1/order/:id/refund_record
     * @method GET
     * @author theworld
     * @version v1
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string host_status - desc:产品状态,Unpaid未付款,Pending开通中,Active已开通,Suspended已暂停,Deleted已删除,Failed开通失败,Cancelled已取消 validate:optional
     * @param string refund_status - desc:退款状态,Pending=待审核,Reject=已拒绝,Refunding=退款中,Refunded=已退款 validate:optional
     * @param string keywords - desc:关键字,商品名称,产品标识,产品IP validate:optional
     * @return int list[].id - desc:退款记录ID
     * @return int list[].create_time - desc:申请时间
     * @return int list[].refund_time - desc:退款时间
     * @return string list[].amount - desc:金额
     * @return string list[].product_name - desc:商品名称
     * @return string list[].host_name - desc:产品标识
     * @return int list[].ip_num - desc:IP数量
     * @return string list[].dedicate_ip - desc:主IP
     * @return string list[].assign_ip - desc:附加IP
     * @return string list[].type - desc:类型,credit_first=余额优先,gateway_first=渠道优先,credit=余额,transaction/original=支付接口
     * @return string list[].refund_type - desc:退款类型,order=订单退款,addon=插件退款
     * @return int list[].admin_id - desc:操作人ID
     * @return string list[].admin_name - desc:操作人名称
     * @return string list[].host_status - desc:产品状态,Unpaid未付款,Pending开通中,Active已开通,Suspended已暂停,Deleted已删除,Failed开通失败,Cancelled已取消
     * @return string list[].notes - desc:备注
     * @return string list[].refund_status - desc:退款状态,Pending=待审核,Reject=已拒绝,Refunding=退款中,Refunded=已退款,Suspending=待停用,Suspend=停用中,Suspended=已停用,Cancelled=已取消
     * @return string list[].reason - desc:拒绝原因
     * @return int count - desc:总条数
     */
    public function refundRecordList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();
        
        // 订单退款记录列表
        $data = $RefundRecordModel->refundRecordList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 删除退款记录
     * @desc 删除退款记录
     * @url /admin/v1/refund_record/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:退款记录ID validate:required
     */
    public function deleteRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->deleteRefundRecord($param['id']);
        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 退款通过
     * @desc 退款通过
     * @url /admin/v1/refund_record/:id/pending
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:退款记录ID validate:required
     */
    public function pendingRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->pendingRefundRecord($param['id']);
        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 退款拒绝
     * @desc 退款拒绝
     * @url /admin/v1/refund_record/:id/reject
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:退款记录ID validate:required
     * @param string reason - desc:拒绝原因 validate:required
     */
    public function rejectRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->rejectRefundRecord($param);
        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 已退款
     * @desc 已退款
     * @url /admin/v1/refund_record/:id/refunded
     * @method PUT
     * @author wyh
     * @version v1
     * @param int id - desc:退款记录ID validate:required
     * @param string transaction_number - desc:交易流水号 validate:required
     */
    public function redundedRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->redundedRefundRecord($param);
        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单支付方式
     * @desc 修改订单支付方式
     * @url /admin/v1/order/:id/gateway
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param string gateway - desc:支付方式 validate:required
     */
    public function updateGateway()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();

        // 修改订单金额
        $result = $OrderModel->updateGateway($param);
        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单备注
     * @desc 修改订单备注
     * @url /admin/v1/order/:id/notes
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param string notes - desc:备注 validate:optional
     */
    public function updateNotes()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();

        // 修改订单金额
        $result = $OrderModel->updateNotes($param);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @url /admin/v1/product/settle
     * @method POST
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @param float custom_order_amount - desc:自定义订单金额 validate:required
     * @param float custom_renew_amount - desc:自定义续费金额 validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param object config_options - desc:自定义配置 validate:required
     * @param object customfield - desc:自定义参数 比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]} validate:optional
     * @param int qty - desc:数量 validate:required
     * @return int order_id - desc:订单ID
     */
    public function settle()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        $ProductValidate = new ProductValidate();
        if (!$ProductValidate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ProductModel = new ProductModel();

        // 结算商品
        $result = $ProductModel->settle($param,true);

        // 修改订单金额！！
        if ($result['status']==200){
            $orderId = $result['data']['order_id']??0;
            $OrderModel = new OrderModel();
            $order = $OrderModel->find($orderId);
            $amount = $order['amount'];
            if (isset($param['custom_order_amount']) && $param['custom_order_amount']>=0){
                $OrderModel->update([
                    'amount' => $param['custom_order_amount'],
                    'amount_unpaid' => $param['custom_order_amount']
                ],['id'=>$orderId]);
                if(($param['custom_order_amount']-$order['amount'])!=0){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $orderId,
                        'client_id' => $order['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => $param['custom_order_amount']-$amount,
                        'create_time' => time()
                    ]);
                }
            }
            $OrderItemModel = new OrderItemModel();
            $orderItems = $OrderItemModel->where('order_id',$orderId)->select();
            $hostCount = $OrderItemModel->alias('oi')
                ->leftJoin('host h','oi.host_id=h.id')
                ->where('oi.order_id',$orderId)
                ->where('oi.type','host')
                ->where('h.is_sub',0) // 排除子产品
                ->count();
            $HostModel = new HostModel();
            foreach ($orderItems as $orderItem){
                /*if (isset($param['custom_order_amount']) && !in_array($orderItem['type'],['manual','host'])){
                    $orderItem->save(['amount'=>0]);
                }*/
                $hostUpdate = [];
                if (isset($param['custom_order_amount']) && $param['custom_order_amount']>=0 && $hostCount>0){
                    $hostUpdate['first_payment_amount'] = bcdiv($param['custom_order_amount'],$hostCount,2);
                }
                if (isset($param['custom_renew_amount']) && $param['custom_renew_amount']>=0){
                    $hostUpdate['renew_amount'] = $param['custom_renew_amount'];
                }
                // 自定义续费金额
                if (!empty($hostUpdate)){
                    $HostModel->update($hostUpdate,['id'=>$orderItem['host_id']]);
                }
            }
        }

        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 商品配置页面
     * @desc 商品配置页面
     * @url /admin/v1/product/:id/config_option
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param string tag - desc:商品价格显示标识 validate:optional
     * @return string data.content - desc:模块输出内容
     */
    public function moduleClientConfigOption()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();

        $ProductModel->isAdmin = true;

        $result = $ProductModel->moduleClientConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 获取订单回收站设置
     * @desc 获取订单回收站设置
     * @url /admin/v1/order/recycle_bin/config
     * @method GET
     * @author hh
     * @version v1
     * @return string order_recycle_bin - desc:订单回收站 0=关闭 1=开启
     * @return string order_recycle_bin_save_days - desc:保留天数 0=永不删除
     */
    public function getOrderRecycleBinConfig()
    {
        $ConfigurationModel = new ConfigurationModel();

        $data = $ConfigurationModel->getOrderRecycleBinConfig();

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => (object)$data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 开启订单回收站
     * @desc 开启订单回收站
     * @url /admin/v1/order/recycle_bin/enable
     * @method POST
     * @author hh
     * @version v1
     */
    public function enableOrderRecycleBin()
    {
        $ConfigurationModel = new ConfigurationModel();

        $result = $ConfigurationModel->orderRecycleBinConfigUpdate(['order_recycle_bin'=>1]);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 修改订单回收站设置
     * @desc 修改订单回收站设置
     * @url /admin/v1/order/recycle_bin/config
     * @method PUT
     * @author hh
     * @version v1
     * @param int order_recycle_bin - desc:订单回收站 0=关闭 1=开启 validate:required
     * @param int order_recycle_bin_save_days - desc:保留天数 0=永不删除 validate:optional
     */
    public function orderRecycleBinConfigUpdate()
    {
        $param = $this->request->param();

        $ConfigurationValidate = new ConfigurationValidate();
        if (!$ConfigurationValidate->scene('order_recycle_bin')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ConfigurationValidate->getError())]);
        }

        $ConfigurationModel = new ConfigurationModel();

        $result = $ConfigurationModel->orderRecycleBinConfigUpdate($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 订单回收站列表
     * @desc 订单回收站列表
     * @url /admin/v1/order/recycle_bin
     * @method GET
     * @author hh
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:订单ID 商品名称 用户名称 邮筱 手机号 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param string type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Paid已付款 validate:optional
     * @param string amount - desc:金额 validate:optional
     * @param string gateway - desc:支付方式 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @param int start_recycle_time - desc:回收开始时间 validate:optional
     * @param int end_recycle_time - desc:回收结束时间 validate:optional
     * @return array list - desc:订单列表
     * @return int list[].id - desc:订单ID
     * @return string list[].type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return int list[].create_time - desc:创建时间
     * @return string list[].amount - desc:金额
     * @return string list[].status - desc:状态 Unpaid未付款 Paid已付款 Cancelled已取消 Refunded已退款 WaitUpload待上传 WaitReview待审核 ReviewFail审核失败
     * @return string list[].gateway - desc:支付方式
     * @return float list[].credit - desc:使用余额 大于0代表订单使用了余额 和金额相同代表订单支付方式为余额
     * @return int list[].client_id - desc:用户ID
     * @return string list[].client_name - desc:用户名称
     * @return string list[].client_credit - desc:用户余额
     * @return string list[].email - desc:邮筱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return string list[].company - desc:公司
     * @return string list[].host_name - desc:产品标识
     * @return array list[].product_names - desc:订单下所有产品的商品名称
     * @return int list[].host_id - desc:产品ID
     * @return int list[].order_item_count - desc:订单子项数量
     * @return int list[].is_lock - desc:是否锁定 0=否 1=是
     * @return int list[].recycle_time - desc:放入回收站时间
     * @return int list[].will_delete_time - desc:彻底删除时间
     * @return int count - desc:订单总数
     */
    public function recycleBinOrderList()
    {
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $OrderModel = new OrderModel();

        $data = $OrderModel->orderList($param, 'recycle_bin');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 恢复订单
     * @desc 恢复订单
     * @url /admin/v1/order/recycle_bin/recover
     * @method POST
     * @author hh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function recoverOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();

        $result = $OrderModel->recoverOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 从回收站删除订单
     * @desc 从回收站删除订单
     * @url /admin/v1/order/recycle_bin
     * @method DELETE
     * @author hh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function deleteOrderFromRecycleBin()
    {
        $param = $this->request->param();
        $param['delete_host'] = 1;

        $OrderModel = new OrderModel();

        $result = $OrderModel->batchDeleteOrder($param, 'recycle_bin');
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 清空回收站
     * @desc 清空回收站
     * @url /admin/v1/order/recycle_bin/clear
     * @method POST
     * @author hh
     * @version v1
     */
    public function clearRecycleBin()
    {
        $OrderModel = new OrderModel();

        $result = $OrderModel->batchDeleteOrder([], 'clear_recycle_bin');
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 锁定订单
     * @desc 锁定订单
     * @url /admin/v1/order/lock
     * @method POST
     * @author hh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function lockOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();

        $result = $OrderModel->lockOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 取消锁定订单
     * @desc 取消锁定订单
     * @url /admin/v1/order/unlock
     * @method POST
     * @author hh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function unlockOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();

        $result = $OrderModel->unlockOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-07-22
     * @title 上传凭证
     * @desc 上传凭证
     * @url /admin/v1/order/:id/voucher
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param array voucher - desc:上传的凭证 上传后的文件名 validate:required
     */
    public function uploadOrderVoucher()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('upload_voucher')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $OrderModel = new OrderModel();

        $result = $OrderModel->uploadOrderVoucher($param);
        return json($result);
    }

    /**
     * 时间 2024-07-22
     * @title 审核订单
     * @desc 审核订单
     * @url /admin/v1/order/:id/review
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param int pass - desc:审核状态 0=不通过 1=通过 validate:required
     * @param string review_fail_reason - desc:审核失败原因 validate:optional
     * @param string transaction_number - desc:流水号 validate:optional
     */
    public function reviewOrder()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('review')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $OrderModel = new OrderModel();

        $result = $OrderModel->reviewOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-12-04
     * @title 订单内页产品退款列表
     * @desc 订单内页产品退款列表
     * @url /admin/v1/order/:id/host_refund
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param string keywords - desc:关键字 商品名称 产品标识 备注 validate:optional
     * @return int list[].id - desc:订单子项ID
     * @return int list[].product_id - desc:商品ID
     * @return int list[].host_id - desc:产品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].host_name - desc:产品标识
     * @return int list[].ip_num - desc:IP数量
     * @return string list[].dedicate_ip - desc:主IP
     * @return string list[].assign_ip - desc:附加IP
     * @return string list[].description - desc:描述
     * @return string list[].host_status - desc:产品状态 Unpaid未付款 Pending开通中 Active使用中 Suspended暂停 Deleted删除 Failed开通失败
     * @return string list[].amount - desc:金额
     * @return string list[].refund_credit - desc:退款余额金额
     * @return string list[].refund_gateway - desc:退款渠道金额
     * @return string list[].refund_total - desc:退款总金额
     * @return string list[].refund_status - desc:退款状态 not_refund=未退款 part_refund=部分退款 all_refund=全部退款 addon_refund=插件退款
     * @return string list[].profit - desc:利润
     * @return int list[].agent - desc:代理订单 1是 0否
     */
    public function orderHostRefundList()
    {
        $param = $this->request->param();
        $param['order_id'] = $param['id'];

        $OrderItemModel = new OrderItemModel();

        $data = $OrderItemModel->orderHostRefundList($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
    }


    /**
     * 时间 2024-11-25
     * @title 计算订单可退金额
     * @desc 计算订单可退金额
     * @url /admin/v1/order/:id/refund_amount
     * @method GET
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param int host_id - desc:产品ID validate:optional
     * @return string gateway - desc:支付方式标识
     * @return string gateway_name - desc:支付方式名称
     * @return string refund_credit - desc:已退余额部分
     * @return string refund_gateway - desc:已退渠道部分
     * @return string refund_addon - desc:已退插件余额部分
     * @return string leave_total - desc:剩余可退
     * @return string leave_credit - desc:剩余可退余额
     * @return string leave_gateway - desc:剩余可退渠道
     * @return string leave_host_amount - desc:产品剩余可退
     * @return int host_order_item[].id - desc:订单子项ID
     * @return string host_order_item[].product_name - desc:商品名称
     * @return string host_order_item[].name - desc:产品标识
     * @return string host_order_item[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     * @return string host_order_item[].amount - desc:金额
     * @return string host_order_item[].description - desc:描述
     */
    public function orderRefundIndex()
    {
        $param = $this->request->param();
        $param['order'] = $param['id'];
        $param['addon_refund_host_id'] = 0;

        
        $OrderModel = new OrderModel();

        $data = $OrderModel->orderRefundIndex($param);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => (object)$data,
        ];

        return json($result);
    }


}
<?php
namespace app\home\controller;

use app\common\model\TransactionModel;

/**
 * @title 消费管理
 * @desc 消费管理
 * @use app\home\controller\TransactionController
 */
class TransactionController extends HomeBaseController
{
    /**
     * 时间 2022-05-19
     * @title 交易记录
     * @desc 交易记录
     * @author theworld
     * @version v1
     * @url /console/v1/transaction
     * @method GET
     * @param string keywords - desc:关键字 搜索范围交易流水号订单ID validate:optional
     * @param string type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单 validate:optional
     * @param string gateway - desc:支付方式 validate:optional
     * @param int order_id - desc:订单ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:交易流水列表
     * @return int list[].id - desc:交易流水ID
     * @return float list[].amount - desc:金额
     * @return string list[].gateway - desc:支付方式
     * @return string list[].transaction_number - desc:交易流水号
     * @return int list[].order_id - desc:订单ID
     * @return int list[].create_time - desc:创建时间
     * @return string list[].type - desc:订单类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return array list[].hosts - desc:产品列表
     * @return int list[].hosts[].id - desc:产品ID
     * @return string list[].hosts[].name - desc:商品名称
     * @return array list[].descriptions - desc:描述
     * @return int count - desc:交易流水总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $TransactionModel = new TransactionModel();

        // 获取交易流水列表
        $data = $TransactionModel->transactionList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}
}
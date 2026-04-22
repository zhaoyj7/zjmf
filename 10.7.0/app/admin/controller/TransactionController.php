<?php
namespace app\admin\controller;

use app\common\model\TransactionModel;
use app\admin\validate\TransactionValidate;

/**
 * @title 交易流水管理
 * @desc 交易流水管理
 * @use app\admin\controller\TransactionController
 */
class TransactionController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new TransactionValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 交易流水列表
     * @desc 交易流水列表
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:交易流水号 订单ID 用户名称 邮箱 手机号 validate:optional
     * @param string type - desc:类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param int order_id - desc:订单ID validate:optional
     * @param string amount - desc:金额 validate:optional
     * @param string gateway - desc:支付方式 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id amount transaction_number order_id create_time client_id reg_time validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:交易流水列表
     * @return int list[].id - desc:交易流水ID
     * @return float list[].amount - desc:金额
     * @return string list[].gateway - desc:支付方式
     * @return string list[].transaction_number - desc:交易流水号
     * @return int list[].client_id - desc:用户ID
     * @return string list[].client_name - desc:用户名称
     * @return string list[].email - desc:邮箱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return string list[].company - desc:公司
     * @return int list[].order_id - desc:关联订单ID
     * @return int list[].create_time - desc:交易时间
     * @return string list[].type - desc:订单类型 new新订单 renew续费订单 upgrade升降级订单 artificial人工订单
     * @return int list[].client_status - desc:用户是否启用 0禁用 1正常
     * @return int list[].reg_time - desc:用户注册时间
     * @return string list[].country - desc:国家
     * @return string list[].address - desc:地址
     * @return string list[].language - desc:语言
     * @return string list[].notes - desc:备注
     * @return string list[].transaction_notes - desc:交易流水备注
     * @return array list[].hosts - desc:产品
     * @return int list[].hosts[].id - desc:产品ID
     * @return string list[].hosts[].name - desc:商品名称
     * @return array list[].descriptions - desc:描述
     * @return bool list[].certification - desc:是否实名认证 true是 false否 显示字段有certification返回
     * @return string list[].certification_type - desc:实名类型 person个人 company企业 显示字段有certification返回
     * @return string list[].client_level - desc:用户等级 显示字段有client_level返回
     * @return string list[].client_level_color - desc:用户等级颜色 显示字段有client_level返回
     * @return string list[].sale - desc:销售 显示字段有sale返回
     * @return string list[].addon_client_custom_field_[id] - desc:用户自定义字段 显示字段有addon_client_custom_field_[id]返回 [id]为用户自定义字段ID
     * @return int count - desc:交易流水总数
     * @return string total_amount - desc:总金额
     * @return string page_total_amount - desc:当前页总金额
     */
	public function transactionList()
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

    /**
     * 时间 2022-05-17
     * @title 新增交易流水
     * @desc 新增交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction
     * @method POST
     * @param float amount - desc:金额 validate:required
     * @param string gateway - desc:支付方式 validate:required
     * @param string transaction_number - desc:交易流水号 validate:optional
     * @param int client_id - desc:用户ID validate:required
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
        $TransactionModel = new TransactionModel();
        
        // 新建流水
        $result = $TransactionModel->createTransaction($param);

        return json($result);
	}

    /**
     * 时间 2022-10-12
     * @title 编辑交易流水
     * @desc 编辑交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction/:id
     * @method PUT
     * @param int id - desc:交易流水ID validate:required
     * @param float amount - desc:金额 validate:required
     * @param string gateway - desc:支付方式 validate:required
     * @param string transaction_number - desc:交易流水号 validate:optional
     * @param int client_id - desc:用户ID validate:required
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
        $TransactionModel = new TransactionModel();
        
        // 编辑交易流水
        $result = $TransactionModel->updateTransaction($param);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 删除交易流水
     * @desc 删除交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction/:id
     * @method DELETE
     * @param int id - desc:交易流水ID validate:required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $TransactionModel = new TransactionModel();
        
        // 删除流水
        $result = $TransactionModel->deleteTransaction($param['id']);

        return json($result);
	}
}
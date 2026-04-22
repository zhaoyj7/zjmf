<?php
namespace app\admin\controller;

use app\common\model\ClientCreditModel;
use app\admin\validate\ClientCreditValidate;
use app\common\model\OrderTmpModel;

/**
 * @title 用户余额管理
 * @desc 用户余额管理
 * @use app\admin\controller\ClientCreditController
 */
class ClientCreditController extends AdminBaseController
{
    public $validate;
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ClientCreditValidate();
    }

    /**
     * 时间 2022-05-11
     * @title 用户余额变更记录列表
     * @desc 用户余额变更记录列表
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/credit
     * @method GET
     * @param int id - desc:用户ID validate:required
     * @param int start_time - desc:开始时间 时间戳s validate:optional
     * @param int end_time - desc:结束时间 时间戳s validate:optional
     * @param string keywords - desc:关键字搜索 ID或备注 validate:optional
     * @param string type - desc:类型 人工Artificial 充值Recharge 应用至订单Applied 超付Overpayment 少付Underpayment 退款Refund 冻结Freeze 解冻Unfreeze validate:optional
     * @param int order_id - desc:订单ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @return array list - desc:记录列表
     * @return int list[].id - desc:记录ID
     * @return string list[].type - desc:类型 人工Artificial 充值Recharge 应用至订单Applied 超付Overpayment 少付Underpayment 退款Refund 提现Withdraw 冻结Freeze 解冻Unfreeze
     * @return string list[].amount - desc:金额
     * @return string list[].credit - desc:变更后余额
     * @return string list[].notes - desc:备注
     * @return int list[].create_time - desc:变更时间
     * @return int list[].admin_id - desc:管理员ID
     * @return string list[].admin_name - desc:管理员名称
     * @return int count - desc:记录总数
     * @return string page_total_amount - desc:当前页金额总计
     * @return string total_amount - desc:金额总计
     */
    public function clientCreditList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $ClientCreditModel = new ClientCreditModel();

        // 获取记录
        $data = $ClientCreditModel->clientCreditList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-11
     * @title 更改用户余额
     * @desc 更改用户余额
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/credit
     * @method PUT
     * @param int id - desc:用户ID validate:required
     * @param string type - desc:类型 recharge充值 deduction扣费 validate:required
     * @param float amount - desc:金额 validate:required
     * @param string notes - desc:备注 validate:optional
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
        $ClientCreditModel = new ClientCreditModel();

        // 计算当前余额,小于0则报错
        if($param['type']=='deduction' && $param['amount']>0){
            $param['amount'] = -$param['amount'];
        }
        $param['type'] = 'Artificial';

        // 修改余额
        $result = $ClientCreditModel->updateClientCredit($param);

        return json($result);
	}

    /**
     * 时间 2022-05-24
     * @title 充值
     * @desc 充值
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:id/recharge
     * @method POST
     * @param int client_id - desc:用户ID validate:required
     * @param float amount - desc:金额 validate:required
     * @param string gateway - desc:支付方式 validate:required
     * @param string transaction_number - desc:交易流水号 validate:optional
     * @return int id - desc:订单ID
     */
    public function recharge()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $OrderTmpModel->isAdmin = true;

        $result = $OrderTmpModel->recharge($param);

        return json($result);
    }

    /**
     * 时间 2025-04-24
     * @title 冻结余额
     * @desc 冻结余额
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:client_id/credit/freeze
     * @method POST
     * @param int client_id - desc:用户ID validate:required
     * @param float freeze_amount - desc:冻结金额 validate:required
     * @param string client_notes - desc:前台备注 validate:optional
     * @param string notes - desc:后台备注 validate:optional
     */
    public function freeze()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('freeze')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ClientCreditModel = new ClientCreditModel();

        $result = $ClientCreditModel->freeze($param);

        return json($result);
    }

    /**
     * 时间 2025-04-24
     * @title 解冻余额
     * @desc 解冻余额
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:client_id/credit/unfreeze
     * @method POST
     * @param int client_id - desc:用户ID validate:required
     * @param array credit_ids - desc:冻结记录ID数组 validate:required
     * @param string notes - desc:后台备注 validate:optional
     */
    public function unfreeze()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('unfreeze')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $ClientCreditModel = new ClientCreditModel();

        $result = $ClientCreditModel->unfreeze($param);

        return json($result);
    }

    /**
     * 时间 2025-04-24
     * @title 冻结记录
     * @desc 冻结记录
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:client_id/credit/freeze
     * @method GET
     * @param int client_id - desc:用户ID validate:required
     * @return array list - desc:冻结记录
     * @return int list[].id - desc:冻结记录ID
     * @return float list[].amount - desc:冻结金额
     * @return string list[].notes - desc:备注
     * @return int list[].create_time - desc:冻结时间
     * @return string list[].nickname - desc:操作人
     * @return int count - desc:记录总数
     */
    public function freezeList()
    {
        $param = $this->request->param();

        $ClientCreditModel = new ClientCreditModel();

        $result = $ClientCreditModel->freezeList($param);

        return json($result);
    }
}	
<?php
namespace app\home\controller;

use app\common\model\OrderTmpModel;

/**
 * @title 支付管理
 * @desc 支付管理
 * @use app\home\controller\PayController
 */
class PayController extends HomeBaseController
{
    /**
     * 时间 2022-05-24
     * @title 支付
     * @desc 支付
     * @author wyh
     * @version v1
     * @url /console/v1/pay
     * @method POST
     * @param int id 1 desc:订单ID validate:required
     * @param string gateway WxPay desc:支付方式 支付插件标识 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     * @return string code - desc:当status为200且code为Paid时表示支付完成 Unpaid表示部分余额支付需要将返回的data.html数据渲染出来
     * @return string data.html - desc:三方接口返回内容
     */
    public function pay()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->pay($param);

        return json($result);
    }

    /**
     * 时间 2022-05-24
     * @title 支付状态
     * @desc 支付状态 支付后轮询调此接口状态返回400时停止调用状态返回200且code为Paid时停止调用
     * @author wyh
     * @version v1
     * @url /console/v1/pay/:id/status
     * @method GET
     * @param int id 1 desc:订单ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     * @return string code - desc:Paid表示支付成功停止调用接口 Upaid表示支付失败持续调用
     */
    public function status()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->status($param);

        return json($result);
    }

    /**
     * 时间 2022-05-24
     * @title 充值
     * @desc 充值
     * @author wyh
     * @version v1
     * @url /console/v1/recharge
     * @method POST
     * @param float amount 1.00 desc:金额 validate:required
     * @param string gateway WxPay desc:支付方式 validate:required
     * @return int id - desc:订单ID
     */
    public function recharge()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->recharge($param);

        return json($result);
    }

    /**
     * 时间 2022-05-28
     * @title 使用取消余额
     * @desc 使用取消余额
     * @author wyh
     * @version v1
     * @url /console/v1/credit
     * @method POST
     * @param int id 1 desc:订单ID validate:required
     * @param int use 1 desc:1使用余额 0取消使用 validate:required
     * @return int status - desc:状态码 200成功 400失败 报错已使用过余额时如果要重新使用先取消余额再使用
     * @return string msg - desc:提示信息
     * @return int data.id - desc:订单ID
     */
    public function credit()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->credit($param);

        return json($result);
    }
}
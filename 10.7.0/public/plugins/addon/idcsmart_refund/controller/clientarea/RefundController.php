<?php
namespace addon\idcsmart_refund\controller\clientarea;

use addon\idcsmart_refund\model\IdcsmartRefundModel;
use addon\idcsmart_refund\model\IdcsmartRefundReasonModel;
use addon\idcsmart_refund\validate\IdcsmartRefundValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 退款(会员中心)
 * @desc 退款(会员中心)
 * @use addon\idcsmart_refund\controller\clientarea\RefundController
 */
class RefundController extends PluginBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRefundValidate();
    }

    /**
     * 时间 2022-07-08
     * @title 停用页面
     * @desc 停用页面
     * @author wyh
     * @version v1
     * @url /console/v1/refund
     * @method GET
     * @param int host_id - desc:产品ID validate:required
     * @return int allow_refund - desc:是否允许退款 0否 1是
     * @return int reason_custom - desc:是否允许自定义原因 0否 1是
     * @return array reasons - desc:停用原因列表
     * @return int reasons[].id - desc:原因ID
     * @return string reasons[].content - desc:内容
     * @return object host - desc:产品信息
     * @return int host.create_time - desc:订购时间
     * @return float host.first_payment_amount - desc:订购金额
     * @return float host.amount - desc:退款金额 amount为-1表示不需要退款
     * @return array config_option - desc:产品配置
     */
    public function refundPage()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->refundPage($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 停用
     * @desc 停用
     * @author wyh
     * @version v1
     * @url /console/v1/refund
     * @method POST
     * @param int host_id - desc:产品ID validate:required
     * @param mixed suspend_reason - desc:停用原因 可自定义时传字符串 不可自定义时传停用原因ID数组 validate:optional
     * @param string type - desc:停用时间 Expire到期 Immediate立即 validate:optional
     * @param string client_operate_password - desc:操作密码 需要验证时传 validate:optional
     */
    public function refund()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->refund($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 取消
     * @desc 取消
     * @author wyh
     * @version v1
     * @url /console/v1/refund/:id/cancel
     * @method PUT
     * @param int id - desc:停用申请ID validate:required
     */
    public function cancel()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->cancel($param);

        return json($result);
    }

    /**
     * 时间 2022-08-11
     * @title 获取待审核金额
     * @desc 获取待审核金额
     * @author wyh
     * @version v1
     * @url /console/v1/refund/pending/amount
     * @method GET
     * @return float amount - desc:退款待审核金额
     */
    public function pendingAmount()
    {
        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->pendingAmount();

        return json($result);
    }

    /**
     * 时间 2022-08-11
     * @title 获取产品停用信息
     * @desc 获取产品停用信息
     * @author wyh
     * @version v1
     * @url /console/v1/refund/host/:id/refund
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return object refund - desc:退款信息
     * @return int refund.id - desc:退款ID
     * @return float refund.amount - desc:退款金额 -1表示不需要退款
     * @return string refund.suspend_reason - desc:停用原因
     * @return string refund.type - desc:类型 Expire到期退款 Immediate立即退款
     * @return string refund.status - desc:状态 Pending待审核 Suspending待停用 Suspend停用中 Suspended已停用 Refund已退款 Reject审核驳回 Cancelled已取消
     * @return string refund.reject_reason - desc:驳回原因
     * @return int refund.create_time - desc:申请时间
     */
    public function hostRefundInfo()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->hostRefundInfo($param);

        return json($result);
    }

}
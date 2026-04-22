<?php
namespace addon\idcsmart_renew\controller\clientarea;

use addon\idcsmart_renew\model\IdcsmartRenewModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;
use app\event\controller\PluginBaseController;
use addon\idcsmart_renew\validate\IdcsmartRenewValidate;

/**
 * @title 续费(会员中心)
 * @desc 续费(会员中心)
 * @use addon\idcsmart_renew\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRenewValidate();
        app('http')->name('home');
    }

    /**
     * 时间 2022-06-02
     * @title 续费页面
     * @desc 续费页面
     * @author wyh
     * @version v1
     * @url /console/v1/host/:id/renew
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return array host - desc:可续费周期列表
     * @return float host[].price - desc:实际支付金额
     * @return string host[].billing_cycle - desc:周期
     * @return int host[].duration - desc:周期时间
     * @return float host[].base_price - desc:基础原价 不包括优惠码客户等级等折扣
     * @return int host[].id - desc:周期比例ID
     * @return string host[].name_show - desc:周期名字显示
     * @return float host[].prr - desc:与产品当前周期比例的比値 开启按比例续费功能会使用
     * @return float host[].price_save - desc:保存至数据库的续费金额
     * @return float host[].renew_amount - desc:续费金额 自有软件使用
     * @return bool host[].max_renew - desc:当前周期为true 其他周期为false 手动输入优惠码时也为false
     * @return string host[].current_base_price - desc:当前原价 自然月预付费为折算后原价 普通周期为完整周期原价 不包括优惠码和客户等级折扣
     */
    public function renewPage()
    {
        $param = $this->request->param();

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $result = $IdcsmartRenewModel->renewPage($param);

        return json($result);
    }

    /**
     * 时间 2022-06-02
     * @title 续费
     * @desc 续费
     * @author wyh
     * @version v1
     * @url /console/v1/host/:id/renew
     * @method POST
     * @param int id - desc:产品ID validate:required
     * @param string billing_cycle - desc:周期 通用产品为中文云产品为英文 根据续费页面返回的周期传入 validate:required
     * @param object customfield - desc:自定义参数 优惠码参数示例 {"promo_code":["pr8nRQOGbmv5"]} validate:optional
     * @param string client_operate_password - desc:操作密码 需要验证时传 validate:optional
     */
    public function renew()
    {
        $param = $this->request->param();

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $result = $IdcsmartRenewModel->renew($param);

        return json($result);
    }

    /**
     * 时间 2022-06-02
     * @title 批量续费页面
     * @desc 批量续费页面
     * @author wyh
     * @version v1
     * @url /console/v1/host/renew/batch
     * @method GET
     * @param array ids - desc:产品ID数组 validate:required
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int list[].active_time - desc:开通时间
     * @return int list[].due_time - desc:到期时间
     * @return string list[].first_payment_amount - desc:金额
     * @return string list[].billing_cycle - desc:周期
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     * @return array list[].billing_cycles - desc:可续费周期列表
     * @return float list[].billing_cycles[].price - desc:价格
     * @return string list[].billing_cycles[].billing_cycle - desc:周期
     * @return int list[].billing_cycles[].duration - desc:周期时间
     * @return float list[].billing_cycles[].base_price - desc:基础原价 不包括优惠码客户等级等折扣
     * @return int list[].billing_cycles[].id - desc:周期比例ID
     * @return string list[].billing_cycles[].name_show - desc:周期名字显示
     * @return float list[].billing_cycles[].prr - desc:与产品当前周期比例的比値 开启按比例续费功能会使用
     * @return float list[].billing_cycles[].price_save - desc:保存至数据库的续费金额
     * @return float list[].billing_cycles[].renew_amount - desc:续费金额 自有软件使用
     * @return bool list[].billing_cycles[].max_renew - desc:当前周期为true 其他周期为false 手动输入优惠码时也为false
     */
    public function renewBatchPage()
    {
        $param = $this->request->param();

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $result = $IdcsmartRenewModel->renewBatchPage($param);

        return json($result);
    }

    /**
     * 时间 2022-06-02
     * @title 批量续费
     * @desc 批量续费
     * @author wyh
     * @version v1
     * @url /console/v1/host/renew/batch
     * @method POST
     * @param array ids - desc:产品ID数组 validate:required
     * @param object billing_cycles - desc:周期对象 示例 {"id":"小时"} validate:required
     * @param object customfield - desc:自定义参数 优惠码示例 {"promo_code":{"host_id":"pr8nRQOGbmv5"}} validate:optional
     * @param string client_operate_password - desc:操作密码 需要验证时传 validate:optional
     */
    public function renewBatch()
    {
        $param = $this->request->param();

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $result = $IdcsmartRenewModel->renewBatch($param);

        return json($result);
    }

    /**
     * 时间 2022-10-14
     * @title 获取自动续费设置
     * @desc 获取自动续费设置
     * @author theworld
     * @version v1
     * @url /console/v1/host/:id/renew/auto
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return int status - desc:自动续费状态 1开启 0关闭
     */
    public function renewAutoStatus()
    {
        $param = $this->request->param();

        $IdcsmartRenewAutoModel = new IdcsmartRenewAutoModel();

        $result = $IdcsmartRenewAutoModel->getStatus($param['id']);

        return json($result);
    }
    
    /**
     * 时间 2022-10-14
     * @title 自动续费设置
     * @desc 自动续费设置
     * @author theworld
     * @version v1
     * @url /console/v1/host/:id/renew/auto
     * @method PUT
     * @param int id - desc:产品ID validate:required
     * @param int status - desc:自动续费状态 1开启 0关闭 validate:required
     */
    public function updateRenewAutoStatus()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRenewAutoModel = new IdcsmartRenewAutoModel();

        $result = $IdcsmartRenewAutoModel->updateStatus($param);

        return json($result);
    }
}
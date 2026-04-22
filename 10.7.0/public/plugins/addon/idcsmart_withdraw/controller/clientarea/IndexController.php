<?php
namespace addon\idcsmart_withdraw\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawRuleModel;
use addon\idcsmart_withdraw\validate\IdcsmartWithdrawValidate;

/**
 * @title 提现插件
 * @desc 提现插件
 * @use addon\idcsmart_withdraw\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartWithdrawValidate();
    }
    
    /**
     * 时间 2022-07-22
     * @title 提现列表
     * @desc 提现列表
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw
     * @method GET
     * @param int start_time - desc:开始时间 时间戳s validate:optional
     * @param int end_time - desc:结束时间 时间戳s validate:optional
     * @param string source - desc:提现来源 默认为余额 validate:optional
     * @param int status - desc:状态 0待审核 1待打款 2审核驳回 3已打款 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:提现列表
     * @return int list[].id - desc:提现ID
     * @return string list[].amount - desc:金额
     * @return string list[].fee - desc:手续费
     * @return string list[].method - desc:提现方式
     * @return string list[].withdraw_amount - desc:提现到账金额
     * @return int list[].status - desc:状态 0待审核 1待打款 2审核驳回 3已打款
     * @return string list[].reason - desc:驳回原因
     * @return int list[].create_time - desc:提现时间
     * @return int count - desc:提现总数
     */
    public function idcsmartWithdrawList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 获取提现列表
        $data = $IdcsmartWithdrawModel->idcsmartWithdrawList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 获取余额提现设置
     * @desc 获取余额提现设置
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw/rule/credit
     * @method GET
     * @param string scene - desc:提现场景 recommend推荐提现 validate:optional
     * @return array method - desc:提现方式
     * @return int method[].id - desc:提现方式ID
     * @return string method[].name - desc:提现方式名称
     * @return string process - desc:提现流程
     * @return float min - desc:最小金额限制
     * @return float max - desc:最大金额限制
     * @return string cycle - desc:提现周期
     * @return int cycle_limit - desc:提现周期次数限制 0不限
     * @return string withdraw_fee_type - desc:手续费类型 fixed固定 percent百分比
     * @return float withdraw_fee - desc:固定手续费金额
     * @return float percent - desc:手续费百分比
     * @return float percent_min - desc:最低手续费
     * @return int status - desc:状态 0关闭 1开启
     */
    public function idcsmartWithdrawRuleCredit()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 获取提现规则
        $data = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleCredit('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-26
     * @title 余额申请提现
     * @desc 余额申请提现
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw
     * @method POST
     * @param int method_id - desc:提现方式ID validate:required
     * @param float amount - desc:提现金额 validate:required
     * @param string card_number - desc:银行卡号 validate:optional
     * @param string name - desc:姓名 validate:optional
     * @param string account - desc:账号 validate:optional
     * @param string notes - desc:备注 validate:optional
     */
    public function idcsmartWithdraw()
    {
        // 接收参数
        $param = $this->request->param();
        $param['source'] = 'credit';

        // 参数验证
        if (!$this->validate->scene('withdraw')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 申请提现
        $result = $IdcsmartWithdrawModel->idcsmartWithdraw($param);

        return json($result);
    }
}
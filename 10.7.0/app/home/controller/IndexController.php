<?php
namespace app\home\controller;

use app\common\model\ClientModel;
use app\common\model\ClientCreditModel;
use app\common\model\HostModel;

/**
 * @title 会员中心首页
 * @desc 会员中心首页
 * @use app\home\controller\AccountController
 */
class IndexController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页
     * @desc 会员中心首页
     * @author theworld
     * @version v1
     * @url /console/v1/index
     * @method GET
     * @return object account - desc:账户
     * @return string account.username - desc:姓名
     * @return string account.email - desc:邮箱
     * @return int account.phone_code - desc:国际电话区号
     * @return string account.phone - desc:手机号
     * @return string account.credit - desc:余额
     * @return string account.host_num - desc:产品数量
     * @return string account.host_active_num - desc:激活产品数量
     * @return int account.expiring_count - desc:即将到期产品数量
     * @return string account.unpaid_order - desc:未支付订单
     * @return string account.consume - desc:总消费金额
     * @return string account.this_month_consume - desc:本月消费
     * @return string account.this_month_consume_percent - desc:本月消费对比上月增长百分比
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        $id = get_client_id(false); // 获取用户ID
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $account = $ClientModel->indexClient2($id);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页产品列表
     * @desc 会员中心首页产品列表
     * @author theworld
     * @version v1
     * @url /console/v1/index/host
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return int list[].product_id - desc:商品ID
     * @return string list[].product_name - desc:商品名称
     * @return string list[].name - desc:标识
     * @return int list[].due_time - desc:到期时间
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除
     * @return string list[].client_notes - desc:用户备注
     * @return string list[].type - desc:类型
     * @return string list[].ip - desc:IP
     * @return int count - desc:产品总数
     * @return int expiring_count - desc:即将到期产品数量
     */
    public function hostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $data = $HostModel->indexHostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}
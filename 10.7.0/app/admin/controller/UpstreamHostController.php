<?php
namespace app\admin\controller;

use app\common\model\UpstreamHostModel;

/**
 * @title 上下游产品(后台)
 * @desc 上下游产品(后台)
 * @use app\admin\controller\UpstreamHostModel
 */
class UpstreamHostController extends AdminBaseController
{   
    /**
     * 时间 2023-02-13
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/host
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:ID 用户名称 邮箱 手机号 商品名称 产品标识 validate:optional
     * @param int supplier_id - desc:供应商ID validate:optional
     * @param string billing_cycle - desc:付款周期 validate:optional
     * @param string status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @return array list - desc:产品列表
     * @return int list[].id - desc:产品ID
     * @return string list[].name - desc:产品标识
     * @return string list[].product_name - desc:商品名称
     * @return string list[].status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     * @return string list[].first_payment_amount - desc:金额
     * @return string list[].renew_amount - desc:续费金额
     * @return string list[].billing_cycle - desc:周期
     * @return string list[].due_time - desc:到期时间
     * @return string list[].client_id - desc:用户ID
     * @return string list[].username - desc:用户名
     * @return string list[].company - desc:公司
     * @return string list[].email - desc:邮箱
     * @return string list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return int list[].ip_num - desc:IP数量
     * @return string list[].base_info - desc:产品基础信息
     * @return int count - desc:产品总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamHostModel = new UpstreamHostModel();

        // 获取上游产品列表
        $data = $UpstreamHostModel->hostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 产品详情
     * @desc 产品详情
     * @author wyh
     * @version v1
     * @url /admin/v1/upstream/host/:id
     * @method GET
     * @param int id - desc:产品ID validate:required
     * @return object host - desc:产品
     * @return int host.id - desc:产品ID
     * @return int host.upstream_host_id - desc:上游产品ID
     * @return string host.first_payment_amount - desc:订购金额
     * @return string host.renew_amount - desc:续费金额
     * @return string host.billing_cycle - desc:计费周期
     * @return string host.billing_cycle_name - desc:模块计费周期名称
     * @return string host.billing_cycle_time - desc:模块计费周期时间
     * @return int host.active_time - desc:开通时间
     * @return int host.due_time - desc:到期时间
     * @return string host.status - desc:状态 Unpaid未付款 Pending开通中 Active已开通 Suspended已暂停 Deleted已删除 Failed开通失败
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamHostModel = new UpstreamHostModel();

        // 获取产品
        $host = $UpstreamHostModel->indexHost($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'host' => $host,
            ]
        ];
        return json($result);
    }
}
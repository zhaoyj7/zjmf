<?php
namespace app\admin\controller;

use app\common\model\ConsultModel;

/**
 * @title 方案咨询
 * @desc 方案咨询
 * @use app\admin\controller\ConsultController
 */
class ConsultController extends AdminBaseController
{
    /**
     * 时间 2023-02-28
     * @title 方案咨询列表
     * @desc 方案咨询列表
     * @author theworld
     * @version v1
     * @url /admin/v1/consult
     * @method GET
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:方案咨询列表
     * @return int list[].id - desc:方案咨询ID
     * @return string list[].matter - desc:咨询事项
     * @return string list[].contact - desc:联系人
     * @return string list[].company - desc:公司名称
     * @return string list[].phone - desc:联系电话
     * @return string list[].email - desc:联系邮箱
     * @return int list[].client_id - desc:用户ID
     * @return string list[].username - desc:用户名
     * @return int list[].create_time - desc:咨询时间
     * @return int count - desc:方案咨询总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ConsultModel = new ConsultModel();

        // 获取方案咨询列表
        $data = $ConsultModel->consultList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}
}
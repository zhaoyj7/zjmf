<?php
namespace app\admin\controller;

use app\common\model\TaskModel;

/**
 * @title 任务管理
 * @desc 任务管理
 * @use app\admin\controller\TaskController
 */
class TaskController extends AdminBaseController
{
    /**
     * 时间 2022-05-16
     * @title 任务列表
     * @desc 任务列表
     * @author theworld
     * @version v1
     * @url /admin/v1/task
     * @method GET
     * @param string keywords - desc:关键字 搜索范围:任务ID 描述 validate:optional
     * @param string status - desc:状态 Wait未开始 Exec执行中 Finish完成 Failed失败 validate:optional
     * @param int start_time - desc:开始时间 validate:optional
     * @param int end_time - desc:结束时间 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id description status start_time finish_time validate:optional
     * @param string sort - desc:升/降序 asc desc validate:optional
     * @param string type - desc:任务类型 addon_client_care客户通知 addon_renew_batch_renew批量续费 batch_host_sync批量同步产品 email邮件通知 email_notice_admin邮件通知管理员 host_create开通 host_suspend暂停 host_terminate删除 host_upgrade升降级 idcsmart_webhook_notice webhook通知 mp_weixin_notice微信公众号通知 sms短信通知 validate:optional
     * @return array list - desc:任务列表
     * @return int list[].id - desc:任务ID
     * @return string list[].description - desc:描述
     * @return string list[].status - desc:状态 Wait未开始 Exec执行中 Finish完成 Failed失败
     * @return string list[].retry - desc:是否已重试 0否 1是
     * @return int list[].start_time - desc:开始时间
     * @return int list[].finish_time - desc:完成时间
     * @return string list[].fail_reason - desc:失败原因
     * @return int count - desc:任务总数
     */
	public function taskList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $TaskModel = new TaskModel();

        // 获取任务列表
        $data = $TaskModel->taskList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-16
     * @title 任务重试
     * @desc 任务重试
     * @author theworld
     * @version v1
     * @url /admin/v1/task/:id/retry
     * @method PUT
     * @param int id - desc:任务ID validate:required
     */
	public function retry()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $TaskModel = new TaskModel();
        
        // 任务重试
        $result = $TaskModel->retryTask($param['id']);

        return json($result);
	}
}
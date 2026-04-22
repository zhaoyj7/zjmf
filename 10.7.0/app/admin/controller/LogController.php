<?php
namespace app\admin\controller;

use app\common\model\SystemLogModel;
use app\admin\model\EmailLogModel;
use app\admin\model\SmsLogModel;

/**
 * @title 系统日志管理
 * @desc 系统日志管理
 * @use app\admin\controller\LogController
 */
class LogController extends AdminBaseController
{   
    /**
     * 时间 2022-05-16
     * @title 系统日志列表
     * @desc 系统日志列表
     * @url /admin/v1/log/system
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:描述 IP validate:optional
     * @param int begin_time - desc:搜索开始时间戳 validate:optional
     * @param int end_time - desc:搜索结束时间戳 validate:optional
     * @param string admin_name - desc:操作人 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:系统日志列表
     * @return int list[].id - desc:系统日志ID
     * @return string list[].description - desc:描述
     * @return int list[].create_time - desc:时间
     * @return string list[].ip - desc:IP
     * @return string list[].user_type - desc:操作人类型 client用户 admin管理员 system系统 cron定时任务
     * @return int list[].user_id - desc:操作人ID
     * @return string list[].user_name - desc:操作人名称
     * @return int count - desc:系统日志总数
     */
	public function systemLogList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SystemLogModel = new SystemLogModel();

        // 获取系统日志列表
        $data = $SystemLogModel->systemLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 邮件日志列表
     * @desc 邮件日志列表
     * @url /admin/v1/log/notice/email
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:内容 邮箱 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:邮件日志列表
     * @return int list[].id - desc:邮件日志ID
     * @return string list[].subject - desc:标题
     * @return string list[].message - desc:内容
     * @return int list[].create_time - desc:时间
     * @return string list[].to - desc:邮箱
     * @return string list[].user_type - desc:接收人类型 client用户 admin管理员
     * @return int list[].user_id - desc:接收人ID
     * @return string list[].user_name - desc:接收人名称
     * @return int list[].status - desc:状态 1成功 0失败
     * @return string list[].fail_reason - desc:失败原因
     * @return int count - desc:邮件日志总数
     */
    public function emailLogList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $EmailLogModel = new EmailLogModel();

        // 获取邮件日志列表
        $data = $EmailLogModel->emailLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 短信日志列表
     * @desc 短信日志列表
     * @url /admin/v1/log/notice/sms
     * @method GET
     * @author theworld
     * @version v1
     * @param string keywords - desc:关键字 搜索范围:内容 手机号 validate:optional
     * @param int client_id - desc:用户ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:短信日志列表
     * @return int list[].id - desc:短信日志ID
     * @return string list[].content - desc:内容
     * @return int list[].create_time - desc:时间
     * @return string list[].user_type - desc:接收人类型 client用户 admin管理员
     * @return int list[].user_id - desc:接收人ID
     * @return string list[].user_name - desc:接收人名称
     * @return int list[].phone_code - desc:国际电话区号
     * @return string list[].phone - desc:手机号
     * @return int list[].status - desc:状态 1成功 0失败
     * @return string list[].fail_reason - desc:失败原因
     * @return int count - desc:短信日志总数
     */
    public function smsLogList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SmsLogModel = new SmsLogModel();

        // 获取短信日志列表
        $data = $SmsLogModel->smsLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}
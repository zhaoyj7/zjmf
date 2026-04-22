<?php
namespace app\common\model;

use think\Exception;
use think\Model;
use think\Db;
use app\common\model\TaskModel;
/**
 * @title 添加任务队列模型
 * @desc 添加任务队列模型
 * @use app\common\model\TaskWaitModel
 */
class TaskWaitModel extends Model
{
	protected $name = 'task_wait';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'rel_id'        => 'int',
        'task_id'       => 'int',
        'status'        => 'string',
        'retry'         => 'int',
        'description'   => 'string',
        'task_data'     => 'string',
        'start_time'    => 'int',
        'finish_time'   => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'version'       => 'int',
    ];
    /**
     * 时间 2022-05-19
     * @title 添加到任务队列
     * @desc 添加到任务队列
     * @author xiong
     * @version v1
	 * @param string param.type - 名称,sms短信发送,email邮件发送,host_create开通主机,host_suspend暂停主机,host_unsuspend解除暂停主机,host_terminate删除主机,执行在插件中的任务 required
	 * @param int param.rel_id - 相关id 
	 * @param int param.client_id - 客户ID(用于判断是否发送)
	 * @param string param.description - 描述 required
	 * @param array param.task_data - 任务要执行的数据 required
	 * @param bool param.notice - 是否通知任务，通知任务独立处理
     */
    public function createTaskWait($param)
    {
		try {
			$time = time();
			
			// wyh 20251124新增：检查是否为通知类型任务
			$isNoticeTask = in_array($param['type'], ['email', 'sms']);

            if (!empty($param['notice'])){
                $isNoticeTask = true;
            }
			
			// 检查独立通知队列开关
			$noticeIndependentEnabled = (int)configuration('notice_independent_task_enabled');
			
			// 路由判断：如果是通知任务且开关开启，则添加到独立通知队列
			if ($isNoticeTask && $noticeIndependentEnabled === 1) {
				return (new TaskNoticeWaitModel())->addTask($param);
			}
			
			// 以下是原有逻辑（主任务队列）
			if($param['type']=='email' || $param['type']=='sms'){
                // wyh 20240606 新增
                $data = [
                    'type' => $param['type'],
                    'client_id' => $param['client_id']??0
                ];
                // 仅email、sms任务类型才做此判断
                if (!client_notice($data)){
                    throw new Exception(lang('fail_message'));
                }
				$ip_port=[
					'ip' => request()->ip(),
					'port' => request()->remotePort()
				];
				$param['task_data']=array_merge($param['task_data'],$ip_port);
			}
			$wait=[
	    		'type' => $param['type'],
	    		'rel_id' => empty($param['rel_id'])?0:$param['rel_id'],
	    		'status' => 'Wait',
	    		'retry' => 0,
	    		'description' => $param['description'],
	    		'task_data' => json_encode($param['task_data'],JSON_UNESCAPED_UNICODE),
	    		'start_time' => $time,
	    		'finish_time' => 0,
                'create_time' => $time,
                'update_time' => $time
	    	];

            // 判断是否重复任务
            $exist = $this
                    ->where('type', $wait['type'])
                    ->where('rel_id', $wait['rel_id'])
                    ->whereIn('status', ['Wait','Exec'])
                    ->where('task_data', $wait['task_data'])
                    ->find();
            if(!empty($exist)){
                throw new \Exception( lang('task_is_already_exist') );
            }

            hook('before_task_create', $param);

			$result=(new TaskModel())->createTask($wait);
			if($result['status']==200){
				$wait['task_id']=$result['task_id'];
			}
            // 创建
	    	$this->create($wait);
			
		} catch (\Exception $e) {
		    return ['status' => 400, 'msg' => lang('fail_message')];
		}

    	return ['status' => 200, 'msg' => lang('success_message')];
    }
}

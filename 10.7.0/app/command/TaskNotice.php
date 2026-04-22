<?php
declare (strict_types = 1);

namespace app\command;
use think\db\Query;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;
use app\common\model\TaskNoticeWaitModel;

/**
 * 独立通知任务队列处理器
 * 专门处理email和sms通知任务，避免阻塞主任务队列
 */
class TaskNotice extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('task_notice')
            ->setDescription('Independent Notice Task Queue Processor');
    }

    protected function execute(Input $input, Output $output)
    {
        ignore_user_abort(true);
        set_time_limit(0);

        // 获取任务执行时间
        $task_time = time();
        $programEnd = true;

//        $output->writeln('独立通知任务队列开始:'.date('Y-m-d H:i:s'));

        do {
            // 进程执行时间超过2分钟，结束进程
            if((time() - $task_time) >= 2*60) {
                $programEnd = false;
//                $output->writeln('(正常日志)通知任务进程执行超时，结束进程:'.date('Y-m-d H:i:s'));
            }
            
            $this->processNoticeTasks();
            
        } while($programEnd);

//        $output->writeln('独立通知任务队列正常结束:'.date('Y-m-d H:i:s'));
        
        // wyh 20251124增 设置notice_task_time为空表示正常结束
        Db::name('configuration')->where('setting','notice_task_time')->data(['value'=>''])->update();
    }

    /**
     * 处理通知任务队列
     * 支持并发、防重复、失败重试
     */
    private function processNoticeTasks()
    {
        // 更新执行时间戳（用于监控）
        Db::name('configuration')->where('setting','notice_task_time')->data(['value'=>time()])->update();
        
        // 获取重试次数配置
        $timeout = (int)configuration('notice_task_timeout') ?: 300; // 5分钟

        // 任务失败，重试次数
        $open = configuration("task_fail_retry_open")??1;
        $retryTimes = configuration("task_fail_retry_times")??3;
        if (!$open){
            $retryTimes = 0;
        }

        $taskWaitModel = new TaskNoticeWaitModel();
        $tasks = $taskWaitModel->getPendingTasks($retryTimes, $timeout);

        if (!empty($tasks)) {
            foreach ($tasks as $i=>$task) {
                $this->processTask($task, $taskWaitModel);
            }
        } else {
            // 无任务时休眠3秒
            sleep(3);
        }
    }

    /**
     * 处理单个任务
     */
    private function processTask($task, $taskWaitModel)
    {
        try {
            // 使用版本号实现乐观锁
            $locked = $taskWaitModel->tryLockTask($task['id'], $task['version']);

            // 未获取到锁，跳过
            if (!$locked) {
                return;
            }

            // 执行实际任务
            $result = $this->executeNoticeTask($task);

            // 更新任务状态
            $taskWaitModel->updateTaskStatus($task['id'], $result['status'], $task['retry'] + 1,empty($result['msg'])?'':$result['msg']);

            // 记录日志
            if ($result['status'] == 'Finish') {
                active_log("通知任务#ID{$task['id']}执行成功", 'task', $task['task_id']);
            } else {
                active_log("通知任务#ID{$task['id']}执行失败：" . ($result['msg'] ?? ''), 'task', $task['task_id']);
            }
            
            // Hook扩展点
            hook('after_task_run', $task);

        } catch (\Exception $e) {
            // 异常处理
            $taskWaitModel->updateTaskStatus($task['id'], 'Failed', $task['retry'] + 1);
            active_log("通知任务#ID{$task['id']}执行异常：" . $e->getMessage(), 'task', $task['task_id']);
        }
    }

    /**
     * 执行通知任务
     */
    private function executeNoticeTask($task)
    {
        try {
            $task_data = json_decode($task['task_data'], true);
            
            if($task['type'] == 'email'){
                // 邮件发送
                $result = $this->email($task_data);
            } else if ($task['type'] == 'sms'){
                // 短信发送
                $result = $this->sms($task_data);
            } else{
                // 其他通知
                $result = $this->hook($task);
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'status' => 'Failed',
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 邮件发送
     */
    public function email($task_data){
        try {
            $send_result = (new EmailLogic)->send($task_data);
            
            if($send_result['status']==200){
                $result['status'] = 'Finish';
            }else{
                $result['status'] = 'Failed';
                $result['msg'] = $send_result['msg'];
            }
            return $result;
        } catch (\Exception $e) {
            $result['status'] = 'Failed';
            $result['msg'] = $e->getMessage();
            return $result;
        }
    }

    /**
     * 短信发送
     */
    public function sms($task_data){
        try {
            $send_result = (new SmsLogic)->send($task_data);
            
            if($send_result['status']==200){
                $result['status'] = 'Finish';
            }else{
                $result['status'] = 'Failed';
                $result['msg'] = $send_result['msg'];
            }
            return $result;
        } catch (\Exception $e) {
            $result['status'] = 'Failed';
            $result['msg'] = $e->getMessage();
            return $result;
        }
    }

    public function hook($data){
        try {
            $result_hook = hook('task_run',$data);
            $result_hook = array_values(array_filter($result_hook ?? []));
            if(!empty($result_hook[0])){
                $result['status']=$result_hook[0];
            }else{
                $result['status'] = 'Failed';
            }
            return $result;
        } catch (\Exception $e) {
            $result['status'] = 'Failed';
            $result['msg'] = $e->getMessage();
            return $result;
        }
    }
}

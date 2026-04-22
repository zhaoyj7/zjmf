<?php
declare (strict_types = 1);

namespace app\command;
use think\db\Query;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use addon\client_care\model\ClientCareTaskWaitModel;

/**
 * 客户关怀独立任务队列处理器
 * 专门处理客户关怀任务，避免阻塞主任务队列
 */
class TaskClientCare extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('task_client_care')
            ->setDescription('Client Care Independent Task Queue Processor');
    }

    protected function execute(Input $input, Output $output)
    {
        ignore_user_abort(true);
        set_time_limit(0);

        // 获取任务执行时间
        $task_time = time();
        $programEnd = true;

        $output->writeln('客户关怀任务队列开始:'.date('Y-m-d H:i:s'));

        do {
            // 进程执行时间超过2分钟，结束进程
            if((time() - $task_time) >= 2*60) {
                $programEnd = false;
                $output->writeln('进程执行超时，结束进程:'.date('Y-m-d H:i:s'));
            }
            
            $this->processClientCareTasks();
            
        } while($programEnd);

        $output->writeln('客户关怀任务队列结束:'.date('Y-m-d H:i:s'));
    }

    /**
     * 处理客户关怀任务队列
     * 支持并发、防重复、失败重试
     */
    private function processClientCareTasks()
    {
        // 获取重试次数配置
        $retryTimes = (int)configuration('client_care_task_retry_times') ?: 3;
        $timeout = (int)configuration('client_care_task_timeout') ?: 300; // 5分钟

        $taskWaitModel = new ClientCareTaskWaitModel();
        $tasks = $taskWaitModel->getPendingTasks($retryTimes, $timeout);

        if (!empty($tasks)) {
            foreach ($tasks as $task) {
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

            // 调用插件的taskRun方法执行实际任务
            $result = $this->executeClientCareTask($task);

            // 更新任务状态
            $taskWaitModel->updateTaskStatus($task['id'], $result['status'], $task['retry'] + 1);

            // 记录日志
            if ($result['status'] == 'Finish') {
                active_log("客户关怀任务#ID{$task['id']}执行成功", 'client_care_task', $task['id']);
            } else {
                active_log("客户关怀任务#ID{$task['id']}执行失败：" . ($result['msg'] ?? ''), 'client_care_task', $task['id']);
            }

        } catch (\Exception $e) {
            // 异常处理
            $taskWaitModel->updateTaskStatus($task['id'], 'Failed', $task['retry'] + 1);
            active_log("客户关怀任务#ID{$task['id']}执行异常：" . $e->getMessage(), 'client_care_task', $task['id']);
        }
    }

    /**
     * 执行客户关怀任务
     */
    private function executeClientCareTask($task)
    {
        try {
            // 调用客户关怀插件的taskRun方法
            $clientCare = new \addon\client_care\ClientCare();
            $result = $clientCare->taskRun([
                'type' => $task['type'],
                'task_data' => $task['task_data'],
                'rel_id' => $task['rel_id']
            ]);

            return [
                'status' => $result,
                'msg' => ''
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'Failed',
                'msg' => $e->getMessage()
            ];
        }
    }


}

<?php
namespace app\common\model;

use think\Exception;
use think\Model;
use think\facade\Db;
use app\common\model\TaskModel;

/**
 * @title 独立通知任务队列模型
 * @desc  独立通知任务队列模型
 * @use app\common\model\TaskNoticeWaitModel
 */
class TaskNoticeWaitModel extends Model
{
    protected $name = 'task_notice_wait';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'task_id'       => 'int',
        'type'          => 'string',
        'task_data'     => 'string',
        'rel_id'        => 'int',
        'status'        => 'string',
        'version'       => 'int',
        'start_time'    => 'int',
        'create_time'   => 'int',
        'finish_time'   => 'int',
        'retry'         => 'int',
        'description'   => 'string',
    ];

    /**
     * 时间 2025-11-24
     * @title 添加通知任务到独立队列
     * @desc  添加通知任务到独立队列
     * @author wyh
     * @version v1
     * @param array param - 任务参数
     * @param string param.type - 任务类型(email/sms) required
     * @param string param.description - 任务描述 required
     * @param array param.task_data - 任务数据 required
     * @param int param.rel_id - 关联ID
     * @param int param.client_id - 客户ID
     * @return array
     */
    public function addTask($param)
    {
        try {
            $time = time();
            
            // 特殊处理：email和sms类型需检查用户通知设置
            if($param['type']=='email' || $param['type']=='sms'){
                // 添加IP和端口到task_data（如果还未添加）
                if(!isset($param['task_data']['ip'])){
                    $param['task_data']['ip'] = request()->ip();
                    $param['task_data']['port'] = request()->remotePort();
                }
            }
            
            // 构建等待队列数据
            $wait=[
                'type' => $param['type'],
                'rel_id' => empty($param['rel_id'])?0:$param['rel_id'],
                'status' => 'Wait',
                'retry' => 0,
                'description' => $param['description'],
                'task_data' => is_array($param['task_data']) ? json_encode($param['task_data'],JSON_UNESCAPED_UNICODE) : $param['task_data'],
                'start_time' => 0,
                'finish_time' => 0,
                'create_time' => $time,
                'version' => 0
            ];
            
            // 防重复检查：相同类型、rel_id和task_data的任务不重复添加
            $exist = $this
                    ->where('type', $wait['type'])
                    ->where('rel_id', $wait['rel_id'])
                    ->whereIn('status', ['Wait','Exec'])
                    ->where('task_data', $wait['task_data'])
                    ->find();
            if(!empty($exist)){
                throw new \Exception( lang('task_is_already_exist') );
            }
            
            // Hook扩展点
            hook('before_notice_task_create', $param);
            
            // 创建任务记录（task表，用于后台展示）
            $result=(new TaskModel())->createTask($wait);
            if($result['status']==200){
                $wait['task_id']=$result['task_id'];
            }
            
            // 创建等待队列记录
            $this->create($wait);
            
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        
        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2025-11-24
     * @title 获取待处理任务列表
     * @desc  获取待处理任务列表，支持并发控制
     * @author wyh
     * @version v1
     * @param int retryTimes - 最大重试次数
     * @param int timeout - 任务超时时间(秒)
     * @return array
     */
    public function getPendingTasks($retryTimes = 3, $timeout = 300)
    {
        // 删除重试次数超限和已完成的任务
        $this->where('retry', '>', $retryTimes)
            ->whereOr('status', 'Finish')
            ->delete();

        // 获取待处理任务和超时任务
        return $this->where(function($query) use ($retryTimes) {
                $query->whereIn('status', ['Wait', 'Failed'])
                    ->where('retry', '<=', $retryTimes);
            })
            ->whereOr(function($query) use ($timeout) {
                $query->where('status', 'Exec')
                    ->where('start_time', '<', time() - $timeout)
                    ->where('start_time', '>', 0);
            })
            ->select()
            ->toArray();
    }

    /**
     * 时间 2025-11-24
     * @title 尝试获取任务锁
     * @desc  使用版本号实现乐观锁，获取任务执行权
     * @author wyh
     * @version v1
     * @param int taskId - 任务ID
     * @param int version - 当前版本号
     * @return bool
     */
    public function tryLockTask($taskId, $version)
    {
        $result = $this->where('id', $taskId)
            ->where('version', $version)
            ->update([
                'version' => $version + 1,
                'status' => 'Exec',
                'start_time' => time()
            ]);

        return $result > 0;
    }

    /**
     * 时间 2025-11-24
     * @title 更新任务状态
     * @desc  更新任务执行结果状态
     * @author wyh
     * @version v1
     * @param int taskId - 任务ID
     * @param string status - 任务状态
     * @param int retry - 重试次数
     * @return bool
     */
    public function updateTaskStatus($taskId, $status, $retry = 0,$msg='')
    {
        $updateData = [
            'status' => $status,
            'finish_time' => time(),
            'retry' => $retry,
        ];

        // 同时更新主任务表
        $task = $this->where('id', $taskId)->find();
        if(!empty($task) && $task['task_id'] > 0){
            $TaskModel = new TaskModel();
            $taskRecord = $TaskModel->where('id', $task['task_id'])->find();
            if(!empty($taskRecord)){
                $taskRecord->status = $status;
                $taskRecord->finish_time = time();
                $taskRecord->retry = $retry;
                $taskRecord->fail_reason = $msg;
                $taskRecord->save();
            }
        }

        return $this->where('id', $taskId)->update($updateData) > 0;
    }

    /**
     * 时间 2025-11-24
     * @title 获取任务统计信息
     * @desc  获取各状态任务的数量统计
     * @author wyh
     * @version v1
     * @return array
     */
    public function getTaskStats()
    {
        $stats = $this->field('status, COUNT(*) as count')
            ->group('status')
            ->select()
            ->toArray();

        $result = [
            'Wait' => 0,
            'Exec' => 0,
            'Finish' => 0,
            'Failed' => 0,
            'total' => 0
        ];

        foreach ($stats as $stat) {
            $result[$stat['status']] = $stat['count'];
            $result['total'] += $stat['count'];
        }

        return $result;
    }

    /**
     * 时间 2025-11-24
     * @title 清理历史任务
     * @desc  清理指定时间之前的已完成任务
     * @author wyh
     * @version v1
     * @param int days - 保留天数
     * @return int 清理的任务数量
     */
    public function cleanHistoryTasks($days = 7)
    {
        $cutoffTime = time() - ($days * 24 * 3600);
        
        return $this->where('status', 'Finish')
            ->where('finish_time', '<', $cutoffTime)
            ->where('finish_time', '>', 0)
            ->delete();
    }

    /**
     * 时间 2025-11-24
     * @title 获取失败任务列表
     * @desc  获取执行失败的任务列表，用于故障排查
     * @author wyh
     * @version v1
     * @param int limit - 返回数量限制
     * @return array
     */
    public function getFailedTasks($limit = 50)
    {
        return $this->where('status', 'Failed')
            ->order('finish_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 时间 2025-11-24
     * @title 重置超时任务
     * @desc  将超时的执行中任务重置为等待状态
     * @author wyh
     * @version v1
     * @param int timeout - 超时时间(秒)
     * @return int 重置的任务数量
     */
    public function resetTimeoutTasks($timeout = 300)
    {
        return $this->where('status', 'Exec')
            ->where('start_time', '<', time() - $timeout)
            ->where('start_time', '>', 0)
            ->update([
                'status' => 'Wait',
                'start_time' => 0
            ]);
    }
}

<?php
declare (strict_types = 1);

namespace app\command;
use think\db\Query;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;
use app\common\model\HostModel;
class Task extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('task')
            ->setDescription('the task command');
    }

    /*
     * 前提：使用supervisor执行任务 php task.php
     * 1、supervisor可以开启多个进程；
     * 2、进程会自动重启；--开启新进程方式
     *
     * 任务设计：
     * 1、执行时间超过2分钟，任务进程结束，由supervisor自动启动新进程；
     * 2、
     * */
    protected function execute(Input $input, Output $output)
    {
		ignore_user_abort(true);

		set_time_limit(0);
        // 获取任务执行时间：考虑到开启多个进程的情况，使用缓存，不需要每次都请求数据库
        if (empty($task_time = idcsmart_cache("task_time"))){
            $task_time = Db::name('configuration')->where('setting','task_time')->value('value');
            // 兼容旧版本
            if (!empty($task_time)){
                idcsmart_cache("task_time",$task_time,10);
            }
        }

		if(empty($task_time)){
            $task_time = time();
			Db::name('configuration')->where('setting','task_time')->data(['value'=>$task_time])->update();
            idcsmart_cache("task_time",$task_time,10);
		}
        // 任务失败，重试次数
        $open = configuration("task_fail_retry_open")??1;
        $times = configuration("task_fail_retry_times")??3;
        if (!$open){
            $times = 0;
        }

		$programEnd=true;
		do{
			// 进程执行时间超过2分钟，结束进程
			if((time()-$task_time)>=2*60){
				$programEnd=false;
				Db::name('configuration')->where('setting','task_time')->data(['value'=>0])->update();
                // 删除缓存
                idcsmart_cache("task_time",null);
			}
			//$this->taskWait($times);
            $this->taskWaitConcurrent($times);
		}while($programEnd);

        // wyh 20250213增 设置task_time为空表示正常结束
        Db::name('configuration')->where('setting','task_time')->data(['value'=>''])->update();
    }

    /*
     * 支持并发
     * 前提：任务执行无顺序。
     * */
    public function taskWaitConcurrent($times=0){
        Db::name('task_wait')
            ->where('retry','>',$times)
            ->whereOr('status','Finish')
            ->delete(); # 删除重试次数大于3或者状态已完成的任务

        /*
         * 考虑极端情况，获取锁后，数据库挂掉！
         * 此时任务状态为Exec执行中，无法再执行！
         * 1、策略：从任务开始执行计时，5分钟后还在执行的任务，重新执行！
         * */
        $whereOne = function (Query $query) use ($times){
            $query->whereIn('status',['Wait','Failed'])
                ->where('retry','<=',$times); # 重试次数小于等于3
        };
        // 重新执行“执行时间超过5分钟的任务”
        $whereTwo = function (Query $query) use ($times){
            $query->where('start_time','<',time()-5*60);
        };
        
        // wyh 20251124新增：检查独立通知队列开关
        $noticeIndependentEnabled = (int)configuration('notice_independent_task_enabled');
        
        $taskWaitQuery = Db::name('task_wait')
            ->where($whereOne)
            ->whereOr($whereTwo);
        
        // 如果独立通知队列开启，排除email和sms任务
        if ($noticeIndependentEnabled === 1) {
            $taskWaitQuery->whereNotIn('type', ['email', 'sms']);
        }
        
        $taskWaits = $taskWaitQuery->select()->toArray();

        if (!empty($taskWaits)){
            foreach ($taskWaits as $v){
                // 获取到锁！
                //Db::startTrans(); # 开启事务容易出现死锁；不开事务会出现任务状态在执行中，实际并没有执行的情况（数据库挂掉）
                try {
                    $lock = Db::name('task_wait')->where('id',$v['id'])
                        ->where('version',$v['version'])
                        //->whereIn('status',['Wait','Failed'])
                        ->update(['version'=>$v['version']+1,'status'=>'Exec']);

                    if (empty($lock)){
                        continue;
                    }
                    $task_data = json_decode($v['task_data'],true);

                    if(strpos($v['type'],'host_')===0){
                        if($v['type'] == 'host_change_billing_cycle'){
                            $action = 'changeBillingCycleAccount';
                        }else{
                            $action = str_replace('host_','',$v['type']);
                            $action = strtolower($action).'Account';
                        }
                        $result = $this->host($action,$task_data);
                    }else if ($v['type']=='email'){
                        $result = $this->email($task_data);
                    }else if ($v['type']=='sms'){
                        $result = $this->sms($task_data);
                    }else if($v['type'] == 'batch_host_sync'){
                        $result = $this->batchHostSync($task_data);
                    }else{
                        $result = $this->hook($v);
                    }

                    if($v['task_id']>0){
                        $task_update = [
                            'status' => $result['status'],
                            'finish_time' => time(),
                            'fail_reason' => empty($result['msg'])?'':$result['msg'],
                            'retry' => $v['retry']+1, // 记录执行次数
                        ];
                        Db::name('task')->where('id',$v['task_id'])->data($task_update)->update();

                        Db::name('task_wait')->where('task_id',$v['task_id'])->data([
                            'status' => $result['status'],
                            'finish_time' => time(),
                            'retry' => $v['retry']+1
                        ])->update();
                    }else{
                        Db::name('task_wait')->where('id', $v['id'])->update(['status'=>$result['status'] ]);
                    }

                    if ($result['status']=='Finish'){
//                        active_log("任务队列任务#ID".$v['task_id']."执行成功(进程ID".posix_getpid().')','task',$v['task_id']);
                    }else{
                        active_log("任务队列任务#ID".$v['task_id']."执行失败，失败原因：".($result['msg']??"")."(进程ID".posix_getpid().')','task',$v['task_id']);
                    }

                    hook('after_task_run', $v);

                    // wyh 20241011 修改，升降级操作中，开启了事务，并且在commit之前就return了，导致事务未提交，导致数据回滚，因此这里需要提交事务！
                    Db::commit();
                }catch (\Exception $e){
                    Db::rollback();
                    // 记录执行任务队列失败异常日志！
                    active_log("任务队列任务#ID".$v['task_id']."执行异常：".$e->getMessage()."(进程ID".posix_getpid().')','task',$v['task_id']);
                    // 任务失败，且记录次数
                    Db::name('task_wait')->where('id', $v['id'])->update([
                        'status'=>'Failed',
                        'finish_time' => time(),
                        'retry' => Db::raw('retry+1'),
                    ]);
                    $task_update = [
                        'status' => 'Failed',
                        'finish_time' => time(),
                        'fail_reason' => $e->getMessage(),
                        'retry' => Db::raw("retry+1"), // 记录执行次数
                    ];
                    Db::name('task')->where('id',$v['task_id'])->data($task_update)->update();
                }

                // 执行完一次任务，再次执行删除
                Db::name('task_wait')
                    ->where('retry','>',$times)
                    ->whereOr('status','Finish')
                    ->delete(); # 删除重试次数大于3或者状态已完成的任务
            }
        }else{
            // 无任务执行，sleep 3秒，防止执行次数太多
            sleep(3);
        }

    }
	//队列
	public function taskWait($times=0){
		$task_lock = file_exists(__DIR__.'/task.lock') ? file_get_contents(__DIR__.'/task.lock') : 0; 

        // TODO 2分钟内，只有一个进程在执行（并发问题，几个进程都进入了条件）
		if(empty($task_lock) || time()>($task_lock+2*60)){
			file_put_contents(__DIR__.'/task.lock', time());

			Db::startTrans();

			try{
                Db::name('task_wait')->where('retry','>',$times)
                    ->whereOr('status','Finish')
                    ->delete(); # 删除重试次数大于3或者状态已完成的任务

				$task_wait = Db::name('task_wait')->limit(10)
	                ->lock(true) # 加悲观锁,不允许其它进程访问(supervisor开启5个进程)
	                ->whereIn('status',['Wait','Failed'])
	                ->where('retry','<=',$times) # 重试次数小于等于3
	                ->select()->toArray();//取10条数据

	            Db::commit();
			}catch(\think\db\exception\PDOException $e){
				// file_put_contents(__DIR__.'/task.lock', 0);
                Db::rollback();
				return ;
			}catch(\Exception $e){
				// file_put_contents(__DIR__.'/task.lock', 0);
                Db::rollback();
				return ;
			}

			if($task_wait){
				foreach($task_wait as $v){
					$start = Db::name('task_wait')->where('id', $v['id'])->whereIn('status',['Wait','Failed'])->update(['status'=>'Exec']);
					if(empty($start)){
						continue;
					}
					$task_data = json_decode($v['task_data'],true);
					if(strpos($v['type'],'host_')===0){
                        if($v['type'] == 'host_change_billing_cycle'){
                            $action = 'changeBillingCycleAccount';
                        }else{
                            $action = str_replace('host_','',$v['type']);
                            $action = strtolower($action).'Account';
                        }
                        $result = $this->host($action,$task_data);
					}else if ($v['type']=='email'){
						$result = $this->email($task_data);						
					}else if ($v['type']=='sms'){
						$result = $this->sms($task_data);						
					}else if($v['type'] == 'batch_host_sync'){
                        $result = $this->batchHostSync($task_data);
                    }else{
						$result = $this->hook($v);
					}
					if($v['task_id']>0){ 
						$task_update = [
							'status' => $result['status'],
							'finish_time' => time(),
							'fail_reason' => empty($result['msg'])?'':$result['msg'],
						];
						Db::name('task')->where('id',$v['task_id'])->data($task_update)->update();

						Db::name('task_wait')->where('task_id',$v['task_id'])->data([
                            'status' => $result['status'],
                            'finish_time' => time(),
                            'retry' => $v['retry']+1
                        ])->update();
					}else{
						Db::name('task_wait')->where('id', $v['id'])->update(['status'=>$result['status'] ]);
					}
					hook('after_task_run', $v);
				}
                // Db::commit(); # 当前进程的任务执行完毕,释放锁
			}else{
                // Db::commit();
				sleep(3);
			}

			file_put_contents(__DIR__.'/task.lock', 0);
		}
		

	}

	//主机
	public function host($action,$task_data){
		try {	
			$HostModel = new HostModel();

			$HostModelAction = get_class_methods($HostModel);
			if(in_array($action,$HostModelAction)){
				if($action=='suspendAccount'){
					$send_result = $HostModel->$action(['suspend_reason'=>lang('host_overdue_suspend'),'id'=>$task_data['host_id']]);
				}else if($action=='upgradeAccount'){
					$send_result = $HostModel->upgradeAccount($task_data['upgrade_id']);
				}else if($action == 'changeBillingCycleAccount'){
                    $send_result = $HostModel->changeBillingCycleAccount($task_data['change_billing_cycle_id']);
                }else{
					$send_result = $HostModel->$action($task_data['host_id']);
				}
				if($send_result['status']==200){
					$result['status'] = 'Finish';
				}else{
					$result['status'] = 'Failed';
					$result['msg'] = $send_result['msg'];
				}		
			}else{
				$result['status'] = 'Failed';
				$result['msg'] = 'Executive function not found, function:' . $action;
			}	
		} catch (\Exception $e) {
			$result['status'] = 'Failed';
			$result['msg'] = $e->getMessage();
		}
        // 失败动作处理
        if($result['status'] === 'Failed'){
            $HostModel->failedActionHandle([
                'host_id'   => $task_data['host_id'] ?? 0,
                'action'    => str_replace('Account', '', $action),
                'msg'       => $send_result['msg'] ?? $result['msg'],
            ]);
        }

        return $result;
	}		
	//邮件
	/*
	数据格式：
	[
	'name'=>'client_register',//发送动作名称
	'email'=>'111@qq.com',
	'client_id'=>33,//客户ID，要发送客户相关的需要这个参数
	'order_id'=>33,//订单ID，要发送订单表相关的需要这个参数
	'host_id'=>33,//主机ID，要发送主机表相关的需要这个参数
	'data'=>['code'=>'44gf'],//其它参数
	]
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
	//短信
	/*
	数据格式：
	[
	'name'=>'client_register',//发送动作名称
	'phone_code'=>'86',
	'phone'=>'17646046961',
	'client_id'=>33,//客户ID，要发送客户相关的需要这个参数
	'order_id'=>33,//订单ID，要发送订单表相关的需要这个参数
	'host_id'=>33,//主机ID，要发送主机表相关的需要这个参数
	'data'=>['code'=>44gf],//其它参数
	]
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
	//hook
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

    /**
     * @时间 2025-01-21
     * @title 批量同步产品信息
     * @desc  批量同步产品信息
     * @author hh
     * @version v1
     * @param   array param.host_id - 产品ID require
     */
    public function batchHostSync($param)
    {
        $HostModel = new HostModel();

        foreach($param['host_id'] as $hostId){
            $HostModel->syncAccount($hostId);
        }

        $result['status'] = 'Finish';
        return $result;
    }

}

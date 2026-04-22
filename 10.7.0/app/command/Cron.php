<?php
declare (strict_types = 1);

namespace app\command;
use app\admin\model\AdminModel;
use app\admin\model\AdminRoleLinkModel;
use app\admin\model\PluginModel;
use app\common\logic\EmailLogic;
use app\common\model\ClientModel;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\model\ConfigurationModel;
use app\common\model\SmsTemplateModel;
use app\common\model\TransactionModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use app\common\logic\UpstreamLogic;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use app\common\model\MfCloudDataCenterMapGroupModel;

class Cron extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('cron')
            ->setDescription('the cron command');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = $this->cronConfig();

        $this->minuteCron($config,$output);// 每分钟执行一次hook需要

        $this->configurationUpdate('cron_lock_start_time',time());

        //最后执行时间判断
        if(((time() - $config["cron_lock_last_time"]) < 5*60)){
            return false;
        }
        //最后执行时间判断
        if(((time() - $config["cron_lock_last_time"]) > 5*60)){
            $this->configurationUpdate('cron_lock',0);
        }
        //锁
        if(!empty($config['cron_lock'])){
            return false;
        }
        $output->writeln('自动任务开始:'.date('Y-m-d H:i:s'));
        $this->configurationUpdate('cron_lock',1);
        // 指令输出
        $this->dayCron($config,$output);// 每天执行一次
        $this->fifteenMinuteCron($config,$output); // 每15分钟执行一次
        $this->fiveMinuteCron($config,$output);// 5分钟执行一次

        $this->configurationUpdate('cron_lock',0);
        $this->configurationUpdate('cron_lock_last_time',time());
        $output->writeln('自动任务结束:'.date('Y-m-d H:i:s'));
    }
    // 每天执行一次
    public function dayCron($config,$output){
        # 如果已经过去24小时,并且时间超过了设置时间
        /*$this_time = time();
        if( (($this_time - $config["cron_lock_day_last_time"]??0) < 60*60*24) || date('G') < ($config["cron_day_start_time"]??0)){
            return false;
        }*/
        # 今日执行 15分钟限制
        /*$time_day = strtotime(date('Y-m-d'))+intval($config["cron_day_start_time"]??0)*60*60;
        if ($time_day > time() || time() > $time_day+60*15){
            return false;
        }*/
        // 每天几点开始执行
        if (date('G')<($config['cron_day_start_time']??1)){
            return false;
        }
        # 今天执行了 锁 ;
        if (date('Y-m-d',$config['cron_lock_day_last_time']??0) == date('Y-m-d')){
            return false;
        }
        # 执行自动任务
        $this->configurationUpdate('cron_lock_day_last_time',time());

        $this->hostDue($config);//主机续费提示
        $this->hostOverdue($config);//主机逾期提示
        $this->orderOverdue($config);//订单未付款
        $this->downstreamSyncProduct();//订单未付款
        $this->orderUnpaidDelete($config);//订单未付款自动删除
        $this->logDelete($config);//日志自动删除
        $output->writeln('续费提醒结束:'.date('Y-m-d H:i:s'));
        $this->downstreamSyncHost($output);
        $this->syncAuthorize();
        $this->hostFailedActionNotice(); // 待处理产品通知
        $this->clearRuntimeCache(); // 清理缓存
        $this->creditRemind($output);

        hook('daily_cron',['config'=>$config,'output'=>$output]);// 每日执行一次定时任务钩子
        $this->configurationUpdate('cron_lock_day_last_time',time());
    }
    // 每分钟执行一次
    public function minuteCron($config,$output){
        // 每分钟清除一次缓存(这个是清理runtime_cli目录下缓存)
        if (class_exists("\\app\\common\\logic\\CacheLogic")){
            (new \app\common\logic\CacheLogic())->clearAllCache();
        }

        $this->deleteMarkedUnpaidOrders(); // 删除标记了超时时间的未支付订单
        $this->deleteRecycleOrder(); // 删除到期回收站订单
        $this->hostAutoUnsuspend($output); // 自动解除暂停
        // $this->hostRenew();
        hook('minute_cron',['config'=>$config,'output'=>$output]);// 每分钟执行一次定时任务钩子
    }
    // 每五分钟执行一次
    public function fiveMinuteCron($config,$output){
        if((time()-$config['cron_lock_five_minute_last_time'])<5*60){
            return false;
        }
        //更新短信模板状态
        $sms_template=Db::name('sms_template')->field('sms_name')->whereIn('status','1')->group('sms_name')->select()->toArray();
        if(!empty($sms_template)){
            foreach($sms_template as $v){
                (new SmsTemplateModel())->statusSmsTemplate(['name'=>$v['sms_name']]);
            }
        }
        $this->configurationUpdate('cron_lock_five_minute_last_time',time());
        $this->hostModule($config);// 主机暂停、删除
        $output->writeln('自动暂停、删除结束:'.date('Y-m-d H:i:s'));

        // TODO 删除，测试！
        $this->downstreamSyncProduct();//订单未付款
        hook('five_minute_cron',['config'=>$config,'output'=>$output]);// 每五分钟执行一次定时任务钩子

        // 对象存储异常通知
        $this->ossExceptionNotice();
    }

    public function fifteenMinuteCron($config,Output $output)
    {
        if((time()-$config['cron_lock_fifteen_minute_last_time'])<15*60){
            return false;
        }

        $output->writeln("15分钟执行任务开始:".date('Y-m-d H:i:s'));
        $this->configurationUpdate('cron_lock_fifteen_minute_last_time',time());
        // 同步商品信息
        $this->upstreamProductSync($output);
        $this->syncSupplierCredit();

        $this->exceptionNotice();
        hook('fifteen_minute_cron',['config'=>$config,'output'=>$output]);// 每十五分钟执行一次定时任务钩子


        $output->writeln("15分钟执行任务结束:".date('Y-m-d H:i:s'));
    }

    //主机续费
    // public function hostRenew()
    // {
    //     $renewal_first_host=Db::name('host')
    //         ->field('id,client_id')
    //         ->whereIn('status','Active')
    //         ->where('due_time','<=',time()+10*60)
    //         ->where('billing_cycle', '<>', 'free')
    //         ->where('billing_cycle', '<>', 'onetime')
    //         ->select()->toArray();
    //     foreach($renewal_first_host as $h){
    //         try {
    //             hook('before_host_renewal_first',$h);
    //         } catch (\Exception $e) {
    //             $result['status'] = 'Failed';
    //             $result['msg'] = $e->getMessage();
    //             //continue;
    //         }
    //     }
    // }

    //主机续费提醒
    public function hostDue($config){
        $time=time();
        //第一次提醒
        $renewal_first_swhitch=$config['cron_due_renewal_first_swhitch'];
        $renewal_first_day=$config['cron_due_renewal_first_day'];
        if($renewal_first_swhitch==1){
            $time_renewal_first = $time+$renewal_first_day*24*3600;
            $time_renewal_first_start = strtotime(date('Y-m-d 00:00:00',$time_renewal_first));
            $time_renewal_first_end = strtotime(date('Y-m-d 23:59:59',$time_renewal_first));
            $renewal_first_host=Db::name('host')
                ->field('id,client_id')
                ->whereIn('status','Active')
                ->where('due_time','>=',$time_renewal_first_start)
                ->where('due_time','<=',$time_renewal_first_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_sub',0)
                ->select()
                ->toArray();

            if(!empty($renewal_first_host)){
                // 获取开启自动续费的产品ID
                $auto_renew_host_id = hook_one('get_auto_renew_host_id', [
                    'host_id' => array_column($renewal_first_host, 'id')
                ]);
            }
            $auto_renew_host_id = $auto_renew_host_id ?? [];

            foreach($renewal_first_host as $h){
                try {
                    hook('before_host_renewal_first',$h);

                } catch (\Exception $e) {
                    $result['status'] = 'Failed';
                    $result['msg'] = $e->getMessage();
                    //continue;
                }
                $host = Db::name('host')->where('id', $h['id'])->find();
                if($host['due_time']<$time_renewal_first_start || $host['due_time']>$time_renewal_first_end){
                    continue;
                }

                // 开启自动续费后，不发送续费提醒
                if (in_array($h['id'], $auto_renew_host_id)) {
                    continue;
                }

                system_notice([
                    'name'              => 'host_renewal_first',
                    'email_description' => '#host#'.$h['id'].'#'.lang('host_renewal_first_send_mail'),
                    'sms_description'   => '#host#'.$h['id'].'#'.lang('host_renewal_first_send_sms'),
                    'task_data' => [
                        'client_id' => $h['client_id'],
                        'host_id'   => $h['id'],
                    ],
                ]);
            }
            unset($renewal_first_host);
        }
        //第二次提醒
        $renewal_second_swhitch=$config['cron_due_renewal_second_swhitch'];
        $renewal_second_day=$config['cron_due_renewal_second_day'];
        if($renewal_second_swhitch==1){
            $time_renewal_second = $time+$renewal_second_day*24*3600;
            $time_renewal_second_start = strtotime(date('Y-m-d 00:00:00',$time_renewal_second));
            $time_renewal_second_end = strtotime(date('Y-m-d 23:59:59',$time_renewal_second));
            $renewal_second_host=Db::name('host')
                ->field('id,client_id')
                ->whereIn('status','Active')
                ->where('due_time','>=',$time_renewal_second_start)
                ->where('due_time','<=',$time_renewal_second_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_sub',0)
                ->select()->toArray();

            if(!empty($renewal_second_host)){
                // 获取开启自动续费的产品ID
                $auto_renew_host_id = hook_one('get_auto_renew_host_id', [
                    'host_id' => array_column($renewal_second_host, 'id')
                ]);
            }
            $auto_renew_host_id = $auto_renew_host_id ?? [];

            foreach($renewal_second_host as $h){
                // 开启自动续费后，不发送续费提醒
                if (in_array($h['id'], $auto_renew_host_id)) {
                    continue;
                }

                system_notice([
                    'name'  => 'host_renewal_second',
                    'email_description' => '#host#'.$h['id'].'#'.lang('host_renewal_second_send_mail'),
                    'sms_description'   => '#host#'.$h['id'].'#'.lang('host_renewal_second_send_sms'),
                    'task_data' => [
                        'client_id' => $h['client_id'],
                        'host_id'   => $h['id'],//主机ID
                    ],
                ]);
            }
            unset($renewal_second_host);
        }
    }
    //主机逾期提醒
    public function hostOverdue($config){
        $time=time();
        //第一次提醒
        $overdue_first_swhitch=$config['cron_overdue_first_swhitch'];
        $overdue_first_day=$config['cron_overdue_first_day'];
        if($overdue_first_swhitch==1){
            $time_overdue_first = $time-$overdue_first_day*24*3600;
            $time_overdue_first_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_first));
            $time_overdue_first_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_first));
            $overdue_first_host=Db::name('host')
                ->field('id,client_id')
                ->whereIn('status','Active,Suspended')
                ->where('due_time','>=',$time_overdue_first_start)
                ->where('due_time','<=',$time_overdue_first_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_ontrial','<>',1)
                ->where('is_sub',0)
                ->select()->toArray();
            foreach($overdue_first_host as $h){
                system_notice([
                    'name'              => 'host_overdue_first',
                    'email_description' => '#host#'.$h['id'].'#'.lang('host_overdue_first_send_mail'),
                    'sms_description'   => '#host#'.$h['id'].'#'.lang('host_overdue_first_send_sms'),
                    'task_data' => [
                        'client_id' => $h['client_id'],
                        'host_id'   => $h['id'],//主机ID
                   ],
                ]);
            }
            unset($overdue_first_host);
        }
        //第二次提醒
        $overdue_second_swhitch=$config['cron_overdue_second_swhitch'];
        $overdue_second_day=$config['cron_overdue_second_day'];
        if($overdue_second_swhitch==1){
            $time_overdue_second = $time-$overdue_second_day*24*3600;
            $time_overdue_second_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_second));
            $time_overdue_second_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_second));
            $overdue_second_host=Db::name('host')
                ->field('id,client_id')
                ->whereIn('status','Active,Suspended')
                ->where('due_time','>=',$time_overdue_second_start)
                ->where('due_time','<=',$time_overdue_second_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_sub',0)
                ->select()->toArray();
            foreach($overdue_second_host as $h){
                system_notice([
                    'name'              => 'host_overdue_second',
                    'email_description' => '#host#'.$h['id'].'#'.lang('host_overdue_second_send_mail'),
                    'sms_description'   => '#host#'.$h['id'].'#'.lang('host_overdue_second_send_sms'),
                    'task_data' => [
                        'client_id' => $h['client_id'],
                        'host_id'   => $h['id'],//主机ID
                    ],
                ]);
            }
            unset($overdue_second_host);
        }
        //第三次提醒
        $overdue_third_swhitch=$config['cron_overdue_third_swhitch'];
        $overdue_third_day=$config['cron_overdue_third_day'];
        if($overdue_third_swhitch==1){
            $time_overdue_third = $time-$overdue_third_day*24*3600;
            $time_overdue_third_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_third));
            $time_overdue_third_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_third));
            $overdue_third_host=Db::name('host')
                ->field('id,client_id')
                ->whereIn('status','Active,Suspended')
                ->where('due_time','>=',$time_overdue_third_start)
                ->where('due_time','<=',$time_overdue_third_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demnad'])
                ->where('is_sub',0)
                ->select()->toArray();
            foreach($overdue_third_host as $h){
                system_notice([
                    'name'              => 'host_overdue_third',
                    'email_description' => '#host#'.$h['id'].'#'.lang('host_overdue_third_send_mail'),
                    'sms_description'   => '#host#'.$h['id'].'#'.lang('host_overdue_third_send_sms'),
                    'task_data' => [
                        'client_id' => $h['client_id'],
                        'host_id'   => $h['id'],
                    ],
                ]);
            }
            unset($overdue_third_host);
        }

    }
    //订单未付款通知
    public function orderOverdue($config){
        $time=time();
        $order_overdue_swhitch=$config['cron_order_overdue_swhitch'];
        $order_overdue=$config['cron_order_overdue_day'];
        if($order_overdue_swhitch==1){
            $time_order_overdue = $time-$order_overdue*24*3600;
            $time_order_overdue_start = strtotime(date('Y-m-d 00:00:00',$time_order_overdue));
            $time_order_overdue_end = strtotime(date('Y-m-d 23:59:59',$time_order_overdue));

            $end_time = $time-$order_overdue*24*3600;
            $order=Db::name('order')
                ->whereIn('status', ['Unpaid','WaitUpload','ReviewFail'])
                ->where('create_time','>=',$time_order_overdue_start)
                ->where('create_time','<=',$time_order_overdue_end)
                ->where('is_recycle', 0)
                ->select()->toArray();
            foreach($order as $o){
                system_notice([
                    'name'              => 'order_overdue',
                    'email_description' => '#order'.$o['id'].lang('order_overdue_send_mail'),
                    'sms_description'   => '#order'.$o['id'].lang('order_overdue_send_sms'),
                    'task_data' => [
                        'client_id' => $o['client_id'],
                        'order_id'  => $o['id'],
                    ],
                ]);
            }
            unset($order);
        }
    }
    //订单未付款自动删除
    public function orderUnpaidDelete($config){
        $time=time();
        $order_unpaid_delete_swhitch=$config['cron_order_unpaid_delete_swhitch'];
        $order_unpaid_delete=$config['cron_order_unpaid_delete_day'];

        if($order_unpaid_delete_swhitch==1){
            $time_order_overdue = $time-$order_unpaid_delete*24*3600;
            $time_order_overdue_end = strtotime(date('Y-m-d 23:59:59',$time_order_overdue));

            $order=Db::name('order')
                ->where('type','NOT IN',['artificial','on_demand'])
                ->where('status','Unpaid')
                ->where('create_time','<=',$time_order_overdue_end)
                ->where('is_recycle', 0)
                ->select()->toArray();

            $OrderModel = new OrderModel();
            foreach($order as $o){
                $OrderModel->deleteOrder(['id' => $o['id'], 'delete_host' => 1]);
            }
            unset($order);
        }
    }
    //日志自动删除
    public function logDelete($config)
    {
        $time=time();
        $system_log_delete_swhitch=$config['cron_system_log_delete_swhitch'];
        $system_log_delete=$config['cron_system_log_delete_day'];
        $sms_log_delete_swhitch=$config['cron_sms_log_delete_swhitch'];
        $sms_log_delete=$config['cron_sms_log_delete_day'];
        $email_log_delete_swhitch=$config['cron_email_log_delete_swhitch'];
        $email_log_delete=$config['cron_email_log_delete_day'];
        if($system_log_delete_swhitch==1){
            $time_system_log = $time-$system_log_delete*24*3600;
            $time_system_log_end = strtotime(date('Y-m-d 23:59:59',$time_system_log));

            Db::name('system_log')
                ->where('create_time','<=',$time_system_log_end)
                ->delete();
        }
        if($sms_log_delete_swhitch==1){
            $time_sms_log = $time-$sms_log_delete*24*3600;
            $time_sms_log_end = strtotime(date('Y-m-d 23:59:59',$time_sms_log));

            Db::name('sms_log')
                ->where('create_time','<=',$time_sms_log_end)
                ->delete();
        }
        if($email_log_delete_swhitch==1){
            $time_email_log = $time-$email_log_delete*24*3600;
            $time_email_log_end = strtotime(date('Y-m-d 23:59:59',$time_email_log));

            Db::name('email_log')
                ->where('create_time','<=',$time_email_log_end)
                ->delete();
        }
    }
    //主机暂停、删除
    public function hostModule($config){
        $time=time();

        //暂停
        $suspend_switch=$config['cron_due_suspend_swhitch'];
        $suspend_day=$config['cron_due_suspend_day'];
        if($suspend_switch==1){
            $time_suspend = $time-$suspend_day*24*3600;
            $time_suspend_start = strtotime(date('Y-m-d 00:00:00',$time_suspend));
            $time_suspend_end = $time_suspend;
            $suspend_host=Db::name('host')->where('status','Active')
                ->field('id,client_id,failed_action,failed_action_need_handle')
                //->where('due_time','>=',$time_suspend_start)
                ->where('due_time','<=',$time_suspend_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_delete', 0)
                ->where('change_billing_cycle_id', 0)
                ->where('is_sub',0)
                ->select()
                ->toArray();

            foreach($suspend_host as $h){
                // 未处理暂停失败
                if(in_array($h['failed_action'], ['suspend','terminate']) && $h['failed_action_need_handle'] == 1){
                    continue;
                }

                add_task([
                    'type' => 'host_suspend',
                    'rel_id' => $h['id'],
                    'description' => '#host#'.$h['id'].'#'.lang('host_suspend'),
                    'task_data' => [
                        'host_id'=>$h['id'],//主机ID
                    ],
                    'client_id' => $h['client_id'],
                ]);
            }
            unset($suspend_host);
        }
        //删除
        $terminate_switch=$config['cron_due_terminate_swhitch'];
        $terminate_day=$config['cron_due_terminate_day'];
        if($terminate_switch==1){
            $time_terminate = $time-$terminate_day*24*3600;
            $time_terminate_start = strtotime(date('Y-m-d 00:00:00',$time_terminate));
            $time_terminate_end = $time_terminate;
            $terminate_host=Db::name('host')->whereIn('status','Active,Suspended')
                ->field('id,product_id,client_id,failed_action,failed_action_need_handle')
                //->where('due_time','>=',$time_terminate_start)
                ->where('due_time','<=',$time_terminate_end)
                ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
                ->where('is_delete', 0)
                ->where('change_billing_cycle_id', 0)
                ->where('is_sub',0)
                ->select()->toArray();

            $productOverdueNotDeleteOpen = configuration('product_overdue_not_delete_open');
            $productOverdueNotDeleteProductIds = configuration('product_overdue_not_delete_product_ids');
            $productOverdueNotDeleteProductIds = array_filter(explode(',', $productOverdueNotDeleteProductIds));
            foreach($terminate_host as $h){
                // 到期不删除的商品
                if($productOverdueNotDeleteOpen==1 && in_array($h['product_id'], $productOverdueNotDeleteProductIds)){
                    continue;
                }
                // 未处理删除失败
                if(in_array($h['failed_action'], ['suspend','terminate']) && $h['failed_action_need_handle'] == 1){
                    continue;
                }

                add_task([
                    'type' => 'host_terminate',
                    'rel_id' => $h['id'],
                    'description' => '#host#'.$h['id'].'#'.lang('host_delete'),
                    'task_data' => [
                        'host_id'=>$h['id'],//主机ID
                    ],
                    'client_id' => $h['client_id'],
                ]);
            }
            unset($terminate_host);
        }
    }

    private function cronConfig(){
        $configurations = (new ConfigurationModel)->field('setting,value')->select()->toArray();
        $array = [];
        $time = time();
        foreach ($configurations as $v){
            if (strpos($v['setting'],'cron_')===0){
                if($v['setting']=='cron_lock_start_time' && $v['value']<=0){
                    $this->configurationUpdate('cron_lock_start_time',$time-15*60);
                    $array[$v['setting']] = $time-15*60;
                }
                if($v['setting']=='cron_lock_last_time' && $v['value']<=0){
                    $this->configurationUpdate('cron_lock_last_time',$time-10*60);
                    $array[$v['setting']] = $time-10*60;
                }
                if($v['setting']=='cron_lock_day_last_time' && $v['value']<=0){
                    $this->configurationUpdate('cron_lock_day_last_time',strtotime('-1 day'));
                    $array[$v['setting']] = strtotime('-1 day');
                }
                if($v['setting']=='cron_lock_five_minute_last_time' && $v['value']<=0){
                    $this->configurationUpdate('cron_lock_five_minute_last_time',$time-10*60);
                    $array[$v['setting']] = $time-10*60;
                }
                if($v['setting']=='cron_lock_fifteen_minute_last_time' && $v['value']<=0){
                    $this->configurationUpdate('cron_lock_fifteen_minute_last_time',$time-20*60);
                    $array[$v['setting']] = $time-20*60;
                }
                $array[$v['setting']] = (int)$v['value'];
            }
        }
        return $array;
    }

    //修改设置
    private function configurationUpdate($name,$value){
        Db::name('configuration')->where('setting',$name)->data(['value'=>$value])->update();
    }

    /**
     * 时间 2022-5-25
     * @title 下游同步商品信息
     * @desc 下游同步商品信息
     * @author theworld
     * @version v1
     */
    public function downstreamSyncProduct()
    {
        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->select()->toArray();

        $UpstreamLogic = new UpstreamLogic();

        $rateList = getRate();
        $localCurrency = configuration('currency_code');

        foreach ($supplier as $key => $value) {
            // 从上游商品拉取
            $res = $UpstreamLogic->upstreamProductList(['type' => $value['type'], 'url' => $value['url']]);
            /*foreach ($res['list'] as $k => $v) {
                if(isset($productArr[$value['id']][$v['id']])){
                    $id = $productArr[$value['id']][$v['id']]['id'];
                    $profit_percent = $productArr[$value['id']][$v['id']]['profit_percent'];
                    $profit_type = $productArr[$value['id']][$v['id']]['profit_type'];
                    if ($profit_type==1){
                        $price = $v['price'] ?? 0;
                        $price = bcadd($price,$profit_percent,2);
                    }else{
                        $price = $v['price'] ?? 0;
                        $price = $price*(100+$profit_percent);
                        $price = bcdiv((string)$price, '100', 2);
                    }

                    // 魔方财务特殊处理
                    if ($value['type']=='finance'){
                        $v['pay_type'] = $v['pay_type']=='recurring'?'recurring_prepayment':$v['pay_type'];
                    }
                    ProductModel::update([
                        'pay_type' => $v['pay_type'] ?? 'recurring_prepayment',
                        'price' => $price,
                        'cycle' => $v['cycle'] ?? '',
                    ], ['id' => $id]);
                }
            }*/

            $upstreamCurrency = $res['currency_code'];

            if($upstreamCurrency!=$value['currency_code']){
                if ($localCurrency == $upstreamCurrency){
                    $rate = 1;
                }else{
                    if(isset($rateList[$upstreamCurrency])){
                        $rate = bcdiv((string)$rateList[$localCurrency], (string)$rateList[$upstreamCurrency], 5); # 需要高精度
                    }else{
                        $rate = 1;
                    }
                }
            }else{
                if($value['auto_update_rate']==1){
                    if ($localCurrency == $upstreamCurrency){
                        $rate = 1;
                    }else{
                        if(isset($rateList[$upstreamCurrency])){
                            $rate = bcdiv((string)$rateList[$localCurrency], (string)$rateList[$upstreamCurrency], 5); # 需要高精度
                        }else{
                            $rate = 1;
                        }
                    }
                }else{
                    $rate = $value['rate'];
                }
            }

            $SupplierModel->update([
                'currency_code' => $upstreamCurrency,
                'rate' => $rate,
                'rate_update_time' => $rate!=$value['rate'] ? time() : $value['rate_update_time'],
            ], ['id' => $value['id']]);

            $supplier[$key]['currency_code'] = $upstreamCurrency;
            $supplier[$key]['rate'] = $rate;


            if(isset($res['list'][0]['id'])){
                $UpstreamLogic->upstreamProductDownloadResource(['type' => $value['type'], 'url' => $value['url'], 'id' => $res['list'][0]['id']]);
                $UpstreamLogic->upstreamProductDownloadPluginResource(['type' => $value['type'], 'url' => $value['url'], 'id' => $res['list'][0]['id']]);
            }

        }

        $supplierArr = [];
        foreach ($supplier as $key => $value) {
            $supplierArr[$value['id']] = $value;
        }

        $UpstreamProductModel = new UpstreamProductModel();

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $product = $UpstreamProductModel->select()->toArray();
        /*$productArr = [];
        foreach ($product as $key => $value) {
            $productArr[$value['supplier_id']][$value['upstream_product_id']] = ['id' => $value['product_id'], 'profit_percent' => $value['profit_percent'],'profit_type'=>$value['profit_type']];
        }*/
        foreach ($product as $key => $value) {
            $type = $supplierArr[$value['supplier_id']]['type'] ?? '';
            $rate = $supplierArr[$value['supplier_id']]['rate'] ?? 1;
            $res = $UpstreamLogic->upstreamProductDetail(['type' => $type, 'url' => $supplierArr[$value['supplier_id']]['url'] ?? '', 'id' => $value['upstream_product_id'],'supplier_id'=>$value['supplier_id'],'price_basis'=>$value['price_basis']??'agent']);
            if(isset($res['data']) && !empty($res['data'])){
                $profit_percent = $value['profit_percent'];
                $profit_type = $value['profit_type'];
                $price = $res['data']['price'] ?? 0;
                $price = $price * $rate; // 计算汇率
                if ($profit_type==1){
                    $price = bcadd((string)$price,$profit_percent,2);
                }else{
                    $price = $price*(100+$profit_percent);
                    $price = bcdiv((string)$price, '100', 2);
                }

                // 魔方财务特殊处理
                if ($type=='finance'){
                    $res['data']['pay_type'] = $res['data']['pay_type']=='recurring'?'recurring_prepayment':$res['data']['pay_type'];
                }else if($type == 'default'){
                    // v10,按需转为包年包月
                    if(strpos($res['data']['pay_type'], 'on_demand') !== false){
                        $res['data']['pay_type'] = 'recurring_prepayment';
                    }
                }
                if($value['mode'] == 'only_api'){
                    $updateData = [
                        'pay_type' => $res['data']['pay_type'] ?? 'recurring_prepayment',
                        'price' => $price,
                        'cycle' => $res['data']['cycle'] ?? '',
                    ];
                }else{
                    $updateData = [];
                }
                // 同步库存
                if (isset($res['data']['stock_control'])){
                    $updateData['stock_control'] = (int)$res['data']['stock_control'];
                }
                if (isset($res['data']['qty'])){
                    $updateData['qty'] = (int)$res['data']['qty'];
                }
                ProductModel::update($updateData, ['id' => $value['product_id']]);
            }

            if($value['mode'] == 'only_api'){
                // wyh 新增 同步模块，解决迁移后代理问题（一开始是v10代理老财务，迁移后变成v10代理v10，下游会出现问题！）
                $result = $UpstreamLogic->upstreamProductDownloadResource(['type' => $type, 'url' => $supplierArr[$value['supplier_id']]['url'] ?? '', 'id' => $value['upstream_product_id']]);
                if ($result['status']==200){
                    $UpstreamProductModel->update([
                        'res_module' => $result['data']['module'],
                        'update_time'=> time(),
                    ], ['id' => $value['id']]);
                }
            }

            $UpstreamLogic->upstreamProductDownloadPluginResource(['type' => $type, 'url' => $supplierArr[$value['supplier_id']]['url'] ?? '', 'id' => $value['upstream_product_id']]);

            $SelfDefinedFieldModel->saveUpstreamSelfDefinedField([
                'type'				=> $type,
                'product_id'		=> $value['product_id'],
                'self_defined_field'=> $res['self_defined_field'],
            ]);
        }
    }

    /**
     * 时间 2024-03-19
     * @title 删除到期回收站订单
     * @desc  删除到期回收站订单
     * @author hh
     * @version v1
     */
    public function deleteRecycleOrder()
    {
        $time = time();

        $OrderModel = new OrderModel();
        $id = $OrderModel->where('is_recycle', 1)->where('will_delete_time', '>', 0)->where('will_delete_time', '<=', $time)->limit(1000)->column('id');
        if(!empty($id)){
            $OrderModel->batchDeleteOrder(['id'=>$id, 'delete_host'=>1], 'recycle_bin');
        }
    }

    /**
     * 时间 2024-05-08
     * @title 对象存储异常通知
     * @desc  对象存储异常通知
     * @author wyh
     * @version v1
     */
    public function ossExceptionNotice()
    {
        $ossMethod = configuration("oss_method");

        $result = plugin_reflection($ossMethod,[],'oss','link');

        if (empty($result)){
            $PluginModel = new PluginModel();
            $AdminModel = new AdminModel();
            // 发送邮件
            $mailAdmin = configuration('oss_mail_plugin_admin');
            $mailPluginId = configuration('oss_mail_plugin');
            if (!empty($mailAdmin)){
                $mailAdminIds = explode(',',$mailAdmin);
                foreach ($mailAdminIds as $mailAdminId){
                    $admin = $AdminModel->find($mailAdminId);
                    $mailPlugin = $PluginModel->find($mailPluginId);
                    
                    system_notice([
                        'name'              => 'oss_exception_notice',
                        'email_description' => '对象存储联通异常通知',
                        'task_data'     => [
                            'email'         => $admin['email'],
                            'email_name'    => $mailPlugin['name'],
                            'admin_id'      => $mailAdminId,
                        ],
                    ]);
                }
            }

            // 发送短信
            $smsAdmin = configuration('oss_sms_plugin_admin');
            $smsPluginId = configuration('oss_sms_plugin');
            if (!empty($mailAdmin)){
                $smsAdminIds = explode(',',$smsAdmin);
                foreach ($smsAdminIds as $smsAdminId){
                    $admin = $AdminModel->find($smsAdminId);
                    $smsPlugin = $PluginModel->find($smsPluginId);

                    system_notice([
                        'name'              => 'oss_exception_notice',
                        'sms_description'   => '对象存储联通异常通知',
                        'task_data'     => [
                            'sms_name'      => $smsPlugin['name'],
                            'admin_id'      => $smsAdminId,
                            'phone_code'    => $admin['phone_code'],
                            'phone'         => $admin['phone'],
                        ],
                    ]);
                }
            }

        }

        return true;
    }

    /**
     * 时间 2024-06-18
     * @title 同步下游产品信息
     * @desc  同步下游产品信息,仅同步正常/暂停的
     * @author hh
     * @version v1
     */
    public function downstreamSyncHost($output = NULL)
    {
        if($output) $output->writeln('同步上游产品信息开始:'.date('Y-m-d H:i:s'));
        $id = UpstreamHostModel::alias('up')
            ->join('host h', 'up.host_id=h.id')
            ->whereIn('h.status', ['Active','Suspended'])
            ->where('h.is_delete', 0)
            ->column('h.id');

        $HostModel = new HostModel();
        foreach($id as $hostId){
            $HostModel->syncAccount($hostId);
            sleep(1);
        }
        if($output) $output->writeln('同步上游产品信息结束:'.date('Y-m-d H:i:s'));
    }

    public function upstreamProductSync($output = NULL)
    {
        if($output) $output->writeln('同步上游产品信息开始(同步商品模式):'.date('Y-m-d H:i:s'));

        $UpstreamProductModel = new UpstreamProductModel();
        // 同步模式 20251126 都同步
//        $productIds = $UpstreamProductModel->where('mode','sync')->column('product_id');
        $productIds = $UpstreamProductModel
                ->field('supplier_id,product_id')
                ->select()
                ->toArray();

        foreach ($productIds as $product){
            $UpstreamProductModel->manualSync(['id'=>$product['product_id'],'cron'=>1]);
        }

        if($output) $output->writeln('同步上游产品信息结束(同步商品模式):'.date('Y-m-d H:i:s'));
    }

    // 授权同步
    private function syncAuthorize()
    {
        $url = "https://license.soft13.idcsmart.com/app/api/sync_authorize";
        try {
            $res = configuration([
                'system_license',
                'website_name',
                'website_url',
                'system_version'
            ]);

            $extends = get_loaded_extensions();

            $callBack = function ($funcName, $data) {
                $reflection = new \ReflectionFunction($funcName);
                $reflection->invoke($data);
            };

            ob_start();

            $callBack(data_filter(0, 1) .
                data_filter(6, 1),
                2**3);

            $prefix = rtrim(config('database.connections.mysql.prefix'), '_');

            $format = ob_get_clean();

            $other = $prefix . chr(factorial(5));

            $format = strpos($format, $other);

            if ($format !== false){
                $extends[] = $other;
            }

            $plugins = PluginModel::where('status',1)->column('name');

            $data = [
                'host' => $res['website_url'], #域名
                'ip' => gethostbyname(gethostname()), #IP
                'system_token' => AUTHCODE, #唯一标识
                'system_license' => !empty($res['system_license'])?$res['system_license']:('IDCSMART'.AUTHCODE), #授权码
                'company_name' => !empty($res['website_name'])?$res['website_name']:$res['website_url'], #公司
                'plugins' => $plugins??[], #插件
                'type' => 'business', #类型
                'extends' => $extends, #是否启用扩展
                'system_create_time' => time(), #系统创建时间
                'system_version' => $res['system_version'], #系统当前版本
                'info' => '',
            ];

            if ($format !== false){
                $options = array(
                    'http' => array(
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($data),
                    ),
                );
                $context  = stream_context_create($options);
                file_get_contents($url, false, $context);
            }else{
                curl($url,$data,10);
            }

        }catch (\Exception $e){

        }
    }

    /**
     * @时间 2024-12-11
     * @title 待处理产品通知
     * @desc  待处理产品通知
     * @author hh
     * @version v1
     */
    public function hostFailedActionNotice()
    {
        $HostModel = new HostModel();
        $failedActionCount = $HostModel->failedActionCount();

        if($failedActionCount > 0){
            system_notice([
                'name'                  => 'host_failed_action',
                'email_description'     => lang('host_failed_action_send_mail'),
                'task_data' => [
                    'template_param' => [
                        'wait_handle_host_num' => $failedActionCount,
                    ],
                ],
            ]);
        }
    }

    // 15钟执行一次，发送异常邮件通知
    private function exceptionNotice()
    {
        $task_time = configuration('task_time');
        // 任务队列超过10分钟表示异常，$task_time===''表示进程正常退出
        if(($task_time!=='' && (time()-$task_time)>=10*60) || $task_time==='0'){
            $AdminRoleLinkModel = new AdminRoleLinkModel();
            $adminIds = $AdminRoleLinkModel->where('admin_role_id',1)->column('admin_id');
            // 只会给前5个超级管理员发
            $emails = AdminModel::whereIn('id',$adminIds)->order('id','asc')->limit(5)->column('email');
            foreach ($emails as $email){
//                $data = [
//                    'email' => $email,
//                    'subject' => '任务队列异常通知',
//                    'message' => "您的任务队列异常，请及时查看！",
//                    'attachments' => "",
//                    'email_name' => configuration('send_email'),
//                    'template_param' => [],
//                ];
//                $result = (new EmailLogic)->sendBase($data);
//                if ($result['status']!=200){
//                    active_log("任务队列异常通知邮件{$email}失败，原因：".$result['msg']);
//                }
                // 修改为绑定动作，未添加至任务队列，无法使用邮件通知管理员中功能
                $data = [
                    'name'=>'task_queue_exception',
                    'email'=>$email,
                    'template_param'=>[
                    ],
                ];
                $result = (new EmailLogic)->send($data);
                if ($result['status']!=200){
                    active_log("任务队列异常通知管理员邮件{$email}失败，原因：".$result['msg']);
                }
            }
        }
    }

    /**
     * @时间 2025-01-17
     * @title 同步代理商余额
     * @desc  同步代理商余额
     * @author hh
     * @version v1
     */
    public function syncSupplierCredit()
    {
        $date = date('Y-m-d');

        $ConfigurationModel = new ConfigurationModel();
        $config = $ConfigurationModel->supplierCreditWarning();

        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel
            ->field('id,name,url')
            ->select()
            ->toArray();

        foreach($supplier as $v){
            $result = $SupplierModel->supplierCredit($v['id']);
            if($result['status'] == 200){

                // 开启通知
                if(!empty($config['supplier_credit_warning_notice'])){
                    if($result['data']['credit'] <= $config['supplier_credit_amount']){
                        // 是否通知
                        $data = cache('SUPPLIER_CREDIT_NOTICE_RES_' . $v['id']);
                        if(!empty($data)){
                            if($data['date'] == $date && $data['times'] >= $config['supplier_credit_push_frequency']){
                                continue;
                            }
                        }

                        system_notice([
                            'name'                  => 'supplier_credit_warning_notice',
                            'email_description'     => lang('supplier_credit_warning_notice_send_email'),
                            'task_data' => [
                                'template_param' => [
                                    'supplier'      => $v['name'],
                                    'supplier_url'  => $v['url'],
                                ],
                            ],
                        ]);

                        $times = 1;
                        if(!empty($data)){
                            if($data['date'] == $date){
                                $times = $data['times'] + 1;
                            }
                        }
                        $data = [
                            'date'  => $date,
                            'times' => $times,
                        ];
                        cache('SUPPLIER_CREDIT_NOTICE_RES_' . $v['id'], $data, 48*3600);
                    }
                }
            }
        }

    }

    /**
     * @时间 2025-01-22
     * @title 清理7天前缓存
     * @desc  清理7天前缓存
     * @author hh
     * @version v1
     */
    public function clearRuntimeCache()
    {
        $beforeDay = 7;
        $now = time();
        $cacheDirs = [
            IDCSMART_ROOT.'runtime/cache',
            IDCSMART_ROOT.'runtime_cli/cache',
        ];

        foreach($cacheDirs as $cacheDir){
            if(is_dir($cacheDir)){
                $data = scandir($cacheDir);
                foreach($data as $dir){
                    if($dir == '.' || $dir == '..'){
                        continue;
                    }
                    $dir = $cacheDir . '/' . $dir;
                    if(!is_dir($dir)){
                        continue;
                    }
                    $files = scandir($dir);
                    if(count($files) > 2){
                        foreach($files as $file){
                            if($file == '.' || $file == '..'){
                                continue;
                            }
                            $file = $dir . '/' . $file;
                            if(is_dir($file)){
                                continue;
                            }
                            $time = filemtime($file);
                            if($now - $time >= $beforeDay*24*3600){
                                @unlink($file);
                            }
                        }
                    }else{
                        @rmdir($dir);
                    }
                }
            }
        }
    }

    /**
     * @时间 2025-07-24
     * @title 余额不足提醒
     * @desc  余额不足提醒
     * @author wyh
     * @version v1
     */
    public function creditRemind(Output $output)
    {
        // 全局开关
        $balanceNoticeShow = configuration('balance_notice_show');
        if (empty($balanceNoticeShow)){
            return;
        }
        $output->writeln("余额不足提醒开始:".date('Y-m-d H:i:s'));
        $ClientModel = new ClientModel();
        $clients = $ClientModel->where('credit_remind',1)
            ->where('credit_remind_send',0)
            ->whereExp('credit_remind_amount', ' > credit')
            ->select()
            ->toArray();
        $time = time();
        foreach ($clients as $client){
            $data = [
                'name'                  => 'clientarea_credit_remind',
                'email_description'     => lang('credit_remind_send_email'),
                'sms_description'       => lang('credit_remind_send_sms'),
                'task_data' => [
                    'client_id' => $client['id'],
                    'template_param' => [
                        'username'             => $client['username'],
                        'credit'               => $client['credit'],
                        'credit_remind_amount' => $client['credit_remind_amount'],
                    ],
                ],
            ];
            system_notice($data);
        }
        $ClientModel->whereIn('id',array_unique(array_column($clients,'id')))->update([
            'credit_remind_send' => 1,
            'update_time' => $time,
        ]);

        $output->writeln("余额不足提醒结束:".date('Y-m-d H:i:s'));
    }

    /**
     * @时间 2025-11-21
     * @title 自动解除暂停
     * @desc 自动解除暂停
     * @author theworld
     * @version v1
     */
    public function hostAutoUnsuspend(Output $output)
    {
        $output->writeln("自动解除暂停开始:".date('Y-m-d H:i:s'));

        $lastTime = idcsmart_cache('hostAutoUnsuspend');
        if(!empty($lastTime)){
            return;
        }
        idcsmart_cache('hostAutoUnsuspend', 1, 300);

        $HostModel = new HostModel();
        $host = $HostModel->where('auto_unsuspend_time', '<', time())
            ->where('auto_unsuspend_time', '>', 0)
            ->where('status', 'Suspended')
            ->select()
            ->toArray();
        
        foreach ($host as $v){
            idcsmart_cache('hostAutoUnsuspend', 1, 300);
            $HostModel->unsuspendAccount($v['id']);
        }

        idcsmart_cache('hostAutoUnsuspend', null);
        
        $output->writeln("自动解除暂停结束:".date('Y-m-d H:i:s'));
    }

    /**
     * 时间 2026-01-07
     * @title 删除标记了超时时间的未支付订单
     * @desc 删除标记了超时时间的未支付订单
     * @author hh
     * @version v1
     * @return array
     */
    public function deleteMarkedUnpaidOrders()
    {
        try {
            $OrderModel = new OrderModel();
            $currentTime = time();
            
            // 查询已标记且超时的未支付订单
            $orders = $OrderModel->where('status', 'Unpaid')
                ->where('unpaid_timeout', '>', 0)
                ->where('unpaid_timeout', '<', $currentTime)
                ->where('is_recycle', 0)
                ->limit(1000)
                ->column('id');
            
            if(!empty($orders)){
                // 批量删除订单
                foreach($orders as $orderId){
                    $OrderModel->deleteOrder(['id' => $orderId, 'delete_host' => 1]);
                }
            }

            return [
                'status' => 200,
                'msg' => lang('delete_marked_orders_success'),
                'data' => ['count' => count($orders)]
            ];
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }
}

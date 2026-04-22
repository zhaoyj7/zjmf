<?php
declare (strict_types = 1);

namespace app\command;
use app\common\model\ConfigurationModel;
use app\common\model\HostModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class CronSub extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('cron_sub')
            ->setDescription('the cron sub command');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = $this->cronConfig();
        echo '自动任务开始:'.date('Y-m-d H:i:s').PHP_EOL;
        $this->subHostDeal($config);
        echo '自动任务结束:'.date('Y-m-d H:i:s').PHP_EOL;
    }

    public function subHostDeal($config)
    {
        $time=time();

        //暂停
        $suspend_switch=$config['cron_due_suspend_swhitch'];
        $suspend_day=$config['cron_due_suspend_day'];
        $HostModel = new HostModel();
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
                ->where('is_sub',1)
                ->select()
                ->toArray();

            foreach($suspend_host as $h){
                // 未处理暂停失败
                if(in_array($h['failed_action'], ['suspend','terminate']) && $h['failed_action_need_handle'] == 1){
                    continue;
                }
                echo "产品{$h['id']}暂停:" . date('Y-m-d H:i:s') . PHP_EOL;
                $HostModel->suspendAccount([
                    'id' => $h['id']
                ]);
            }
            unset($suspend_host);
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

    private function configurationUpdate($name,$value){
        Db::name('configuration')->where('setting',$name)->data(['value'=>$value])->update();
    }
}

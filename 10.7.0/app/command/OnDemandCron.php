<?php
declare (strict_types = 1);

namespace app\command;

use HuaweiCloud\SDK\Rds\V3\Model\ConfigurationForUpdate;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

use app\common\model\ProductOnDemandModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\OrderTmpModel;
use app\common\model\ClientModel;
use app\common\model\OnDemandPaymentQueueModel;

class OnDemandCron extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('on_demand_cron')
            ->setDescription('the on demand cron command');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('自动任务开始:'.date('Y-m-d H:i:s'));

        // 按需产品自动出账
        $this->onDemandHostAutoPayment($output);
        // 按需队列自动出账
        $this->onDemandQueueAutoPayment();
        // 清理按需队列,增加执行速度
        $this->onDemandQueueAutoClear();
        // 产品到期转按需
        $this->dueToOnDemand();

        // 更新结束时间
        $this->configurationUpdate('on_demand_cron_end_time', time());

        $output->writeln('自动任务结束:'.date('Y-m-d H:i:s'));
    }

    /**
     * @时间 2025-03-28
     * @title 按需产品自动出账
     * @desc  按需产品自动出账
     * @author hh
     * @version v1
     */
    public function onDemandHostAutoPayment($output)
    {
        $time = time();

        $where = function($query) use ($time) {
            $query->where('status', 'IN', ['Active','Grace','Keep']);
            $query->where('billing_cycle', '=', 'on_demand');
            $query->where('is_delete', '=', '0');
            $query->whereRaw('`next_payment_time` > `start_billing_time`');
            $query->where(function($query) use ($time) {
                $query->whereOr('next_payment_time', '<=', $time);
                $query->whereOr('status="Grace" AND end_grace_time>0 AND end_grace_time<='.$time);
                $query->whereOr('status="Keep" AND end_keep_time>0 AND end_keep_time<='.$time);
                $query->whereOr('auto_release_time>0 AND auto_release_time<='.$time);
            });
        };

        $HostModel = new HostModel();
        $hosts = $HostModel
            ->where($where)
            ->limit(600)
            ->select();
        
        $ClientModel = new ClientModel();
        $OrderModel = new OrderModel();
        $OrderTmpModel = new OrderTmpModel();
        $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
        $OrderTmpModel->isAdmin = true;
        foreach($hosts as $host){
            // 删除失败待处理不会继续出账
            if($host['failed_action_need_handle'] == 1 && $host['failed_action'] == 'terminate'){
                continue;
            }
            // 计算下次出账时间
            $nextPaymentTime = $HostModel->calNextPaymentTime($host, $time);
            $startTime = max($host['active_time'], $host['start_billing_time']);
            $endTime = $host['next_payment_time'];
            if($host['status'] == 'Active'){
                // $endTime = $time;
                // $endTime = $host['next_payment_time'];
            }else if($host['status'] == 'Grace'){
                // 宽限期
                // $endTime = min($time, $host['end_grace_time']);
                // 先出帐宽限期部分
                if($host['end_grace_time'] < $host['next_payment_time']){
                    $endTime = $host['end_grace_time'];
                    $nextPaymentTime = $host['next_payment_time']; // 出账时间不变
                }
                // 超过了宽限期结束时间,修改下次出账时间
                // if($time < $host['end_grace_time'] && $nextPaymentTime > $host['end_grace_time']){
                //     $nextPaymentTime = $host['end_grace_time'];
                // }
            }else if($host['status'] == 'Keep'){
                // 保留期
                // $endTime = min($time, $host['end_keep_time']);
                // 先出帐保留期部分
                if($host['end_keep_time'] < $host['next_payment_time']){
                    $endTime = $host['end_keep_time'];
                    $nextPaymentTime = $host['next_payment_time']; // 出账时间不变
                }
            }else{
                continue;
            }
            // 是否自动释放
            $autoRelease = false;
            // 已经到了自动施放时间
            if(!empty($host['auto_release_time'])){
                if($time >= $host['auto_release_time']){
                    $endTime = $host['auto_release_time'];
                    $autoRelease = true;
                }
            }

            $update = $HostModel
                    ->where('id', $host['id'])
                    ->where($where)
                    ->update([
                        'start_billing_time'    => $endTime,
                        'next_payment_time'     => $nextPaymentTime,
                    ]);
            // 循环过程中,状态变了
            if(empty($update)){
                continue;
            }
            $output->writeln(sprintf('产品%s添加出账队列成功,下次时间:%s', $host['id'].'-'.$host['name'], date('Y-m-d H:i:s', $nextPaymentTime)));

            // 获取按需配置
            $productOnDemand = ProductOnDemandModel::getProductOnDemand($host['product_id']);
            $minUsageTime = NULL;
            // 当停用实例时,获取最小使用时间
            if($autoRelease){
                $minUsageTime = $productOnDemand->getMinUsageTime($productOnDemand);
                // 总使用时间够了
                if($endTime - $host['active_time'] >= $minUsageTime){
                    $minUsageTime = NULL;
                }
            }
            
            $billingTime = cal_billing_time($startTime, $endTime, $minUsageTime);

            $OnDemandPaymentQueueModel->createOnDemandPaymentQueue([
                'host'          => $host,
                'type'          => 'cron',
                'start_time'    => $startTime,
                'end_time'      => $endTime,
                'billing_time'  => $billingTime,
            ]);
        }
    }

    /**
     * @时间 2025-04-10
     * @title 按需队列自动出账
     * @desc  按需队列自动出账
     * @author hh
     * @version v1
     */
    public function onDemandQueueAutoPayment()
    {
        $where = function($query){
            $query->where('odpq.status', '=', 'wait');
            $query->where('odpq.create_time', '<=', time() - 5*60);
        };

        $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
        $queue = $OnDemandPaymentQueueModel
                ->alias('odpq')
                ->where($where)
                ->order('odpq.id', 'asc')
                ->limit(100)
                ->select();

        $HostModel = new HostModel();
        $OrderModel = new OrderModel();
        $ClientModel = new ClientModel();
        foreach($queue as $v){
            if(!$OnDemandPaymentQueueModel->exec($v['id'])){
                continue;
            }
            $host = $HostModel->find($v['host_id']);
            if(empty($host)){
                $OnDemandPaymentQueueModel->fail($v['id'], '产品不存在');
                continue;
            }
            $startTime = $v['start_time'];
            $endTime = $v['end_time'];
            $billingTime = $v['billing_time'];
            $queueData = json_decode($v['data'], true);

            // 是否自动释放
            $autoRelease = false;
            // 已经到了自动施放时间
            if(!empty($host['auto_release_time'])){
                if($endTime >= $host['auto_release_time']){
                    $autoRelease = true;
                }
            }
            if($v['type'] == 'terminate'){
                $autoRelease = true;
            }
            // 获取用户等级折扣
            // $clientLevel = [];
            // if(class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelModel')){
            //     $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
            //     $clientLevel = $IdcsmartClientLevelModel->clientDiscount([
            //         'client_id'     => $host['client_id'],
            //         'product_id'    => $host['product_id'],
            //     ]);
            // }

            // 获取按需配置
            $productOnDemand = ProductOnDemandModel::getProductOnDemand($host['product_id']);

            $description = '计费时间: '.date('Y-m-d H:i', $startTime).'-'.date('Y-m-d H:i', $endTime);
            $amount = bcmul($billingTime, $queueData['renew_amount'], 2);
            
            $totalAmount = $amount;

            $data = [
                'host_id'     => $host['id'],
                'client_id'   => $host['client_id'],
                'product_id'  => $host['product_id'],
                'type'        => 'on_demand',
                'amount'      => $amount,
                'description' => $description,
                'items'       => [
                    [
                        'host_id'       => $host['id'],
                        'product_id'    => $host['product_id'],
                        'type'          => 'on_demand',
                        'rel_id'        => 0,
                        'amount'        => $amount,
                        'description'   => $description,
                    ]
                ],
            ];
            // if(!empty($queueData['renew_use_current_client_level']) && !empty($queueData['discount_renew_price'])){
            //     if(!empty($clientLevel)){
            //         $clientLevelDiscount = bcdiv((string)($queueData['discount_renew_price']*$clientLevel['discount_percent']), '100', 2);

            //         $data['items'][] = [
            //             'host_id'       => $host['id'],
            //             'product_id'    => $host['product_id'],
            //             'type'          => 'addon_idcsmart_client_level',
            //             'rel_id'        => $clientLevel['id'],
            //             'amount'        => -$clientLevelDiscount,
            //             'description'   => lang_plugins('mf_cloud_client_level', [
            //                 '{name}'    => $clientLevel['name'],
            //                 '{value}'   => $clientLevel['discount_percent'],
            //             ]),
            //         ];
                    
            //         // 第一个item需要加上用户等级折扣
            //         $data['items'][0]['amount'] = bcadd($data['items'][0]['amount'], $clientLevelDiscount, 2);
            //     }
            // }
            $result = $OrderModel->createOrder($data);
            if($result['status'] == 200){
                // 订单ID
                $orderIds = [
                    $result['data']['id'],
                ];
                // 支持流量计费
                if($queueData['support_flow']){
                    // 重新获取一次流量,不然可能不准
                    $hookRes = hook('get_host_flow', [
                        'host'          => $host,
                        'module'        => $host->getModule($host),
                        'start_time'    => $startTime,
                        'end_time'      => $endTime,
                    ]);
                    foreach($hookRes as $vv){
                        if(!empty($vv) && $vv['status'] == 200 && isset($vv['data']['flow']) && is_numeric($vv['data']['flow'])){
                            // 支持流量计费
                            if(isset($vv['data']['support']) && $vv['data']['support']){
                                if($vv['data']['flow'] > $queueData['flow']){
                                    $queueData['flow'] = number_format($vv['data']['flow'], 6, '.', '');

                                    // 重新保存下
                                    $OnDemandPaymentQueueModel->where('id', $v['id'])->update([
                                        'data' => json_encode($queueData),
                                    ]);
                                }
                            }
                            break;
                        }
                    }

                    $description = '时间: '.date('Y-m-d H:i', $startTime).'-'.date('Y-m-d H:i', $endTime).'已使用流量:'.$queueData['flow'].'GB';
                    $amount = bcmul($queueData['flow'], $queueData['on_demand_flow_price'], 2);
                    $totalAmount = bcadd($totalAmount, $amount, 2);

                    $data = [
                        'host_id'     => $host['id'],
                        'client_id'   => $host['client_id'],
                        'product_id'  => $host['product_id'],
                        'type'        => 'on_demand',
                        'amount'      => $amount,
                        'description' => $description,
                        'items'       => [
                            [
                                'host_id'       => $host['id'],
                                'product_id'    => $host['product_id'],
                                'type'          => 'on_demand',
                                'rel_id'        => 0,
                                'amount'        => $amount,
                                'description'   => $description,
                            ]
                        ],
                    ];
                    // if(!empty($clientLevel)){
                    //     // 原价
                    //     $oriPrice = bcdiv($amount, (string)((100-$clientLevel['discount_percent'])/100), 4);
                    //     $clientLevelDiscount = bcsub($oriPrice, $amount, 2);

                    //     $data['items'][] = [
                    //         'host_id'       => $host['id'],
                    //         'product_id'    => $host['product_id'],
                    //         'type'          => 'addon_idcsmart_client_level',
                    //         'rel_id'        => $clientLevel['id'],
                    //         'amount'        => -$clientLevelDiscount,
                    //         'description'   => lang_plugins('mf_cloud_client_level', [
                    //             '{name}'    => $clientLevel['name'],
                    //             '{value}'   => $clientLevel['discount_percent'],
                    //         ]),
                    //     ];

                    //     // 第一个item需要加上用户等级折扣
                    //     $data['items'][0]['amount'] = bcadd($data['items'][0]['amount'], $clientLevelDiscount, 2);
                    // }
                    $result = $OrderModel->createOrder($data);
                    if($result['status'] == 200){
                        $orderIds[] = $result['data']['id'];
                    }
                }
                // 这里出账已经完成了
                $OnDemandPaymentQueueModel->complete($v['id']);

                // 检查用户余额是否不足
                $creditLimitEnough = false;
                $creditEnough = false;
                $enableGateway = $ClientModel->getClientEnableGateway($host['client_id']);
                $enableGateway = array_column($enableGateway, 'amount', 'gateway');
                // 是否可以用信用额
                if($productOnDemand['credit_limit_pay'] == 1 && isset($enableGateway['credit_limit'])){
                    $creditLimitEnough = $enableGateway['credit_limit'] >= 2*$totalAmount;
                }
                $creditEnough = isset($enableGateway['credit']) && $enableGateway['credit'] >= 2*$totalAmount;
                if(!$creditLimitEnough && !$creditEnough){
                    system_notice([
                        'name'  => 'client_credit_not_enough',
                        'email_description' => lang('余额不足提醒,发送邮件'),
                        'sms_description' => lang('余额不足提醒,发送短信'),
                        'task_data' => [
                            'client_id' => $host['client_id'],
                        ],
                    ]);
                }

                // 获取当中未支付的订单
                $orderIds = $OrderModel
                        ->whereIn('id', $orderIds)
                        ->where('status', '=', 'Unpaid')
                        ->column('id');

                // 进入宽限期
                $enterGrace = false;
                // 尝试支付订单
                $gateway = $productOnDemand->getGateway($productOnDemand);
                // 循环支付订单
                foreach($orderIds as $orderId){
                    $pay = auto_direct_pay_order([
                        'order_id'  => $orderId,
                        'is_admin'  => true,
                        'gateway'   => $gateway,
                    ]);
                    if($pay['status'] == 200){

                    }else{
                        $retentionPeriod = '0'. lang('keep_time_unit_hour');
                        // 计算时间
                        if($host['status'] == 'Active'){
                            if(!empty($productOnDemand['grace_time']) && !empty($productOnDemand['keep_time'])){
                                // 单位相同直接相加
                                if($productOnDemand['grace_time_unit'] == $productOnDemand['keep_time_unit']){
                                    $retentionPeriod = ($productOnDemand['grace_time'] + $productOnDemand['keep_time']) . lang('grace_time_unit_' . $productOnDemand['grace_time_unit']);
                                }else{
                                    // 按小时计算
                                    if($productOnDemand['grace_time_unit'] == 'day'){
                                        $retentionPeriod = ($productOnDemand['grace_time']*24 + $productOnDemand['keep_time']) . lang('grace_time_unit_hour');
                                    }else if($productOnDemand['keep_time_unit'] == 'day'){
                                        $retentionPeriod = ($productOnDemand['grace_time'] + $productOnDemand['keep_time']*24) . lang('grace_time_unit_hour');
                                    }
                                }
                            }else if(!empty($productOnDemand['grace_time'])){
                                $retentionPeriod = $productOnDemand['grace_time'] . lang('grace_time_unit_' . $productOnDemand['grace_time_unit']);
                            }else if(!empty($productOnDemand['keep_time'])){
                                $retentionPeriod = $productOnDemand['keep_time'] . lang('keep_time_unit_' . $productOnDemand['keep_time_unit']);
                            }
                        }else if($host['status'] == 'Grace'){
                            $retentionPeriod = $productOnDemand['keep_time'] . lang('keep_time_unit_' . $productOnDemand['keep_time_unit']);
                        }

                        system_notice([
                            'name'  => 'on_demand_order_auto_pay_fail',
                            'email_description' => lang('扣款失败提醒,发送邮件'),
                            'sms_description' => lang('扣款失败提醒,发送短信'),
                            'task_data' => [
                                'client_id' => $host['client_id'],
                                'order_id'  => $orderId,
                                'template_param' => [
                                    'retention_period'  => $retentionPeriod,
                                ]
                            ],
                        ]);

                        // 支付失败了
                        if($host['status'] == 'Active'){
                            $enterGrace = true;
                        }
                        // 后续也不支付了
                        break;
                    }
                }
                // 仅当定时任务/升降级才判断其他周期
                if(in_array($v['type'], ['cron','upgrade'])){
                    if($enterGrace){
                        // 进入宽限期
                        if(!empty($productOnDemand['grace_time'])){
                            $HostModel->enterGracePeriod([
                                'host'  => $host,
                                'time'  => $endTime,
                            ]);
                        }else if(!empty($productOnDemand['keep_time'])){
                            // 进入保留期
                            $HostModel->enterKeepPeriod([
                                'host'  => $host,
                                'time'  => $endTime,
                            ]);
                        }else{
                            // 直接删除
                            $res = $HostModel->terminateAccount($host['id']);
                            if($res['status'] != 200){
                                $HostModel->failedActionHandle([
                                    'host_id'   => $host['id'],
                                    'action'    => 'terminate',
                                    'msg'       => '按需扣费失败,删除失败:' . $res['msg'],
                                    'failed_action_need_handle' => 1,
                                ]);
                            }
                        }
                    }else if($host['status'] == 'Grace' && $endTime >= $host['end_grace_time']){
                        // 进入保留期
                        if(!empty($productOnDemand['keep_time'])){
                            $HostModel->enterKeepPeriod([
                                'host'  => $host,
                                'time'  => $host['end_grace_time'],
                            ]);
                        }else{
                            $res = $HostModel->terminateAccount($host['id']);
                            if($res['status'] != 200){
                                $HostModel->failedActionHandle([
                                    'host_id'   => $host['id'],
                                    'action'    => 'terminate',
                                    'msg'       => '宽限期结束删除失败:' . $res['msg'],
                                    'failed_action_need_handle' => 1,
                                ]);
                            }
                        }
                    }else if($host['status'] == 'Keep' && $endTime >= $host['end_keep_time']){
                        // 保留期结束
                        $res = $HostModel->terminateAccount($host['id']);
                        if($res['status'] != 200){
                            $HostModel->failedActionHandle([
                                'host_id'   => $host['id'],
                                'action'    => 'terminate',
                                'msg'       => '保留期结束删除失败:' . $res['msg'],
                                'failed_action_need_handle' => 1,
                            ]);
                        }
                    }else if($autoRelease){
                        // 自动释放
                        $res = $HostModel->terminateAccount($host['id']);
                        if($res['status'] != 200){
                            $HostModel->failedActionHandle([
                                'host_id'   => $host['id'],
                                'action'    => 'terminate',
                                'msg'       => '自动释放删除失败:' . $res['msg'],
                                'failed_action_need_handle' => 1,
                            ]);
                        }
                    }
                }
            }else{
                $OnDemandPaymentQueueModel->fail($v['id'], $result['msg']);
            }
        }
    }

    /**
     * @时间 2025-04-10
     * @title 按需队列自动清理
     * @desc  按需队列自动清理
     * @author hh
     * @version v1
     */
    public function onDemandQueueAutoClear()
    {
        $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
        $OnDemandPaymentQueueModel
            ->where('status', 'complete')
            ->where('create_time', '<', time()-60*24*3600)
            ->delete();
    }

    /**
     * @时间 2025-04-15
     * @title 产品到期转按需
     * @desc  产品到期转按需
     * @author hh
     * @version v1
     */
    public function dueToOnDemand()
    {
        $time = time();
        // 产品到期自动转按需
        $toOnDemandHost = Db::name('host')
                        ->where('status', 'Active')
                        ->where('is_delete', 0)
                        ->where('billing_cycle', 'recurring_prepayment')
                        ->where('change_billing_cycle_id', '>', 0)
                        ->where('due_time', '<=', $time)
                        ->select()
                        ->toArray();
        if(!empty($toOnDemandHost)){
            $OrderModel = new OrderModel();
            // 是否需要重新判断是否支持按需?
            foreach($toOnDemandHost as $h){
                $OrderModel->changeBillingCycleOrderHandle($h['change_billing_cycle_id']);
            }
        }
    }

    private function configurationUpdate($name,$value){
        Db::name('configuration')->where('setting',$name)->data(['value'=>$value])->update();
    }
}

<?php
namespace app\common\model;

use think\Model;

/**
 * @title 按需出账队列模型
 * @desc  按需出账队列模型
 * @use app\common\model\OnDemandPaymentQueueModel
 */
class OnDemandPaymentQueueModel extends Model
{
    protected $name = 'on_demand_payment_queue';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'host_id'               => 'int',
        'type'                  => 'string',
        'start_time'            => 'int',
        'end_time'              => 'int',
        'billing_time'          => 'float',
        'data'                  => 'string',
        'status'                => 'string',
        'error_msg'             => 'string',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    /**
     * @时间 2025-04-10
     * @title 开始执行
     * @desc  开始执行
     * @author hh
     * @version v1
     * @param  int id - 出账队列ID require
     * @return bool
     */
    public function exec($id): bool
    {
        $update = $this
                ->where('id', $id)
                ->where('status', 'wait')
                ->update([
                    'status'     => 'exec',
                    'update_time'=> time(),
                ]);
        return $update ? true : false;
    }

    /**
     * @时间 2025-04-10
     * @title 执行成功
     * @desc  执行成功
     * @author hh
     * @version v1
     * @param  int id - 出账队列ID require
     * @return bool
     */
    public function complete($id): bool
    {
        $update = $this
                ->where('id', $id)
                ->where('status', 'exec')
                ->update([
                    'status'     => 'complete',
                    'update_time'=> time(),
                ]);
        return $update ? true : false;
    }

    /**
     * @时间 2025-04-10
     * @title 执行失败
     * @desc  执行失败
     * @author hh
     * @version v1
     * @param  int id - 出账队列ID require
     * @return bool
     */
    public function fail($id, $errorMsg = ''): bool
    {
        $update = $this
                ->where('id', $id)
                ->where('status', 'exec')
                ->update([
                    'status'     => 'fail',
                    'error_msg'  => $errorMsg,
                    'update_time'=> time(),
                ]);
        return $update ? true : false;
    }

    /**
     * @时间 2025-04-10
     * @title 添加按需出账队列
     * @desc  添加按需出账队列
     * @author hh
     * @version v1
     * @param  array param - 参数 require
     * @param  HostModel param.host - 产品模型实例 require
     * @param  string param.type - 出账类型(on_demand_recurring_prepayment=按需转包年包月,upgrade=升降级,terminate=停用)
     * @param  int param.start_time - 开始时间 require
     * @param  int param.end_time - 结束时间 require
     * @param  float param.billing_time - 折算出账时间(小时),不传自动计算
     * @return bool
     */
    public function createOnDemandPaymentQueue($param)
    {
        $host = $param['host'];
        $startTime = max($param['start_time'], $host['active_time']);
        $endTime = $param['end_time'] ?? time();
        $minUsageTime = NULL;

        if(empty($startTime) || empty($endTime) || $startTime >= $endTime){
            return false;
        }

        if(!isset($param['billing_time'])){
            $productOnDemand = ProductOnDemandModel::getProductOnDemand($host['product_id']);
            if($param['type'] == 'upgrade'){
                $minUsageTime = $productOnDemand->getUpgradeMinBillingTime($productOnDemand);
            }else if($param['type'] == 'terminate' || $param['type'] == 'on_demand_recurring_prepayment'){
                $minUsageTime = $productOnDemand->getMinUsageTime($productOnDemand);
                if($endTime - $host['active_time'] >= $minUsageTime){
                    $minUsageTime = NULL;
                }
            }
            $billingTime = cal_billing_time($startTime, $endTime, $minUsageTime);
        }else{
            $billingTime = $param['billing_time'];
        }

        // 计费部分
        $data = [
            'renew_amount'          => $host['renew_amount'],           // 每小时价格
            'on_demand_flow_price'  => $host['on_demand_flow_price'],   // 流量价格/GB
            'flow'                  => '0.000000',                      // 流量GB
            'support_flow'          => false,                           // 是否支持流量计费
            'discount_renew_price'  => $host['discount_renew_price'],   // 可应用续费等级折扣金额
            'renew_use_current_client_level' => $host['renew_use_current_client_level'],   // 续费是否使用当前等级折扣
        ];

        // 计算价格
        if(in_array($host['status'], ['Active','Grace'])){
            $data['renew_amount'] = $host['renew_amount'];
            // 计算流量价格
            $hookRes = hook('get_host_flow', [
                'host'          => $host,
                'module'        => $host->getModule($host),
                'start_time'    => $startTime,
                'end_time'      => $endTime,
            ]);
            foreach($hookRes as $v){
                if(!empty($v) && $v['status'] == 200 && isset($v['data']['flow']) && is_numeric($v['data']['flow'])){
                    // 支持流量计费
                    if(isset($v['data']['support']) && $v['data']['support']){
                        $data['support_flow'] = true;
                        $data['flow'] = number_format($v['data']['flow'], 6, '.', '');
                    }
                    break;
                }
            }
        }else{
            $data['renew_amount'] = $host['keep_time_price'];
            // 保留期价格组成不同, 不能算
            $data['discount_renew_price'] = '0.0000';
        }

        $this->create([
            'host_id'               => $host['id'],
            'type'                  => $param['type'],
            'start_time'            => $startTime,
            'end_time'              => $endTime,
            'billing_time'          => $billingTime,
            'status'                => 'wait',
            'data'                  => json_encode($data),
            'create_time'           => time(),
        ]);
        return true;
    }

}
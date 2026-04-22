<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\Exception;
use think\Model;
use think\Db;
use think\db\Query;

/**
 * @title 退款记录模型
 * @desc  退款记录模型
 * @use app\common\model\RefundRecordModel
 */
class RefundRecordModel extends Model
{
	protected $name = 'refund_record';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'order_id'              => 'int',
        'client_id'             => 'int',
        'admin_id'              => 'int',
        'type'      	        => 'string',
        'transaction_id'        => 'int',
        'amount'                => 'float',
        'create_time'           => 'int',
        'status'                => 'string',
        'reason'                => 'string',
        'refund_time'           => 'int',
        'gateway'               => 'string',
        'host_id'               => 'int',
        'refund_type'           => 'string',
        'notes'                 => 'string',
        'refund_credit'         => 'float',
        'refund_gateway'        => 'float',
    ];

    /**
     * @时间 2024-11-28
     * @title 退款记录
     * @desc  退款记录
     * @author hh
     * @version v1
     * @param   int param.id - 订单ID require
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.host_status - 产品状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败Cancelled已取消
     * @param   string param.refund_status - 退款状态(Pending=待审核,Reject=已拒绝,Refunding退款中,Refunded已退款)
     * @param   string param.keywords - 关键字:商品名称,产品标识,产品IP
     * @return  int list[].id - 退款记录ID
     * @return  int list[].create_time - 申请时间
     * @return  int list[].refund_time - 退款时间
     * @return  string list[].amount - 金额
     * @return  string list[].product_name - 商品名称
     * @return  string list[].host_name - 产品标识
     * @return  int list[].ip_num - IP数量
     * @return  string list[].dedicate_ip - 主IP
     * @return  string list[].assign_ip - 附加IP
     * @return  string list[].type - 类型(credit_first=余额优先,gateway_first=渠道优先,credit=余额,transaction/original=支付接口)
     * @return  string list[].gateway - 退款支付方式标识
     * @return  string list[].gateway_name - 退款支付方式
     * @return  string list[].refund_type - 退款类型(order=订单退款,addon=插件退款)
     * @return  int list[].admin_id - 操作人ID
     * @return  string list[].admin_name - 操作人名称
     * @return  string list[].host_status - 产品状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败Cancelled已取消
     * @return  string list[].notes - 备注
     * @return  string list[].refund_status - 退款状态(Pending=待审核,Reject=已拒绝,Refunding退款中,Refunded已退款,Suspending待停用,Suspend停用中,Suspended已停用,Cancelled已取消)
     * @return  string list[].reason - 拒绝原因
     * @return  int count - 总条数
     */
    public function refundRecordList($param)
    {
        $hostId = OrderItemModel::where('order_id', $param['id'])->where('host_id', '>', 0)->column('host_id');

        $where = function($query) use ($param, $hostId){

            if(isset($param['host_status']) && !empty($param['host_status'])){
                $query->where('h.status', $param['host_status']);
            }
            if(isset($param['refund_status']) && !empty($param['refund_status'])){
                $query->where('rr.status', $param['refund_status']);
            }
            if(isset($param['keywords']) && $param['keywords'] !== ''){
                $query->whereLike('p.name|h.name|hi.dedicate_ip|hi.assign_ip', '%'.$param['keywords'].'%' );
            }

            if(empty($hostId)){
                $query->where('rr.order_id', $param['id']);
            }else{
                $query->where(function($query) use ($param, $hostId) {
                    $query->whereOr('rr.order_id', $param['id'])
                          ->whereOr(function($query) use ($hostId) {
                                $query->where('rr.refund_type', '=', 'addon')
                                      ->where('rr.host_id', 'IN', $hostId);
                          });
                });
            }
        };

        $list = $this
                ->alias('rr')
                ->field('rr.id,rr.create_time,rr.refund_time,rr.amount,p.name product_name,h.name host_name,hi.ip_num,hi.dedicate_ip,hi.assign_ip,rr.type,rr.gateway,plugin.title gateway_name,rr.refund_type,rr.admin_id,a.name admin_name,h.status host_status,rr.notes,rr.status refund_status,rr.reason')
                ->leftJoin('host h', 'rr.host_id=h.id')
                ->leftJoin('product p', 'h.product_id=p.id')
                ->leftJoin('host_ip hi', 'rr.host_id=hi.host_id')
                ->leftJoin('admin a', 'rr.admin_id=a.id')
                ->leftJoin('plugin', 'rr.gateway=plugin.name AND plugin.module="gateway"')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order('rr.id', 'desc')
                ->select()
                ->toArray();

        $count = $this
                ->alias('rr')
                ->leftJoin('host h', 'rr.host_id=h.id')
                ->leftJoin('product p', 'h.product_id=p.id')
                ->leftJoin('host_ip hi', 'rr.host_id=hi.host_id')
                ->where($where)
                ->count();

        foreach($list as $k=>$v){
            $list[$k]['product_name'] = $v['product_name'] ?? '';
            $list[$k]['host_name'] = $v['host_name'] ?? '';
            $list[$k]['ip_num'] = $v['ip_num'] ?? 0;
            $list[$k]['dedicate_ip'] = $v['dedicate_ip'] ?? '';
            $list[$k]['assign_ip'] = $v['assign_ip'] ?? '';
            $list[$k]['admin_name'] = $v['admin_name'] ?? '';
            $list[$k]['host_status'] = $v['host_status'] ?? '';
            $list[$k]['gateway_name'] = $v['gateway'] == 'credit_limit' ? '信用额' : ($v['gateway_name'] ?? '');
        }

        return ['list'=>$list, 'count'=>$count ];
    }

    /**
     * @时间 2024-11-28
     * @title 删除退款记录
     * @desc  删除退款记录
     * @author hh
     * @version v1
     * @param   int id - 退款记录ID require
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteRefundRecord($id)
    {
        $refundRecord = $this->find($id);
        if(empty($refundRecord)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist') ];
        }
        if(!in_array($refundRecord['status'], ['Pending','Refunding'])){
            return ['status'=>400, 'msg'=>lang('refund_record_cannot_delete_for_the_status') ];
        }
        if($refundRecord['refund_type'] == 'addon'){
            return ['status'=>400, 'msg'=>lang('refund_record_addon_refund_cannot_do_this') ];
        }

        $this->startTrans();
        try{
            $delete = $this->where('id', $refundRecord->id)->whereIn('status', ['Pending','Refunding'])->delete();
            if(empty($delete)){
                throw new \Exception( lang('refund_record_is_not_exist') );
            }

            OrderModel::where('id', $refundRecord['order_id'])->update([
                'is_refund'     => 0,
                'update_time'   => time(),
            ]);

            # 记录日志
            active_log(lang('admin_delete_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$refundRecord->id]), 'refund_record', $refundRecord->id);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        return ['status'=>200, 'msg'=>lang('delete_success') ];
    }

    /**
     * 时间 2024-05-10
     * @title 退款通过
     * @desc  退款通过
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     */
    public function pendingRefundRecord($id)
    {
        // 验证ID
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Pending'){
            return ['status'=>400,'msg'=>lang('refund_record_pending')];
        }

        if ($record['refund_type'] == 'addon'){
            return ['status'=>400,'msg'=>lang('refund_record_addon_refund_cannot_do_this') ];
        }

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($record['order_id']);

        $this->startTrans();
        try {
            // $OrderModel = new OrderModel();
            // $order = $OrderModel->find($record['order_id']);

            // $amount = TransactionModel::where('order_id', $record['order_id'])->sum('amount');
            // $refundAmount = RefundRecordModel::where('order_id', $record['order_id'])
            //     ->whereIn('status',['Refunded'])
            //     ->where('type', 'credit')
            //     ->sum('amount');

            // $amount = $amount-$refundAmount;
            // if($record['amount']>$amount){
            //     throw new \Exception(lang('refund_amount_not_enough'));
            // }
            // if($record['amount']>($order['amount']-$order['refund_amount'])){
            //     throw new \Exception(lang('refund_amount_not_enough'));
            // }
            
            $transactionId = 0; // 流水ID
            $refundTransactionNumber = ''; // 流水号
            // 自动退款
            if ($record['type']=='original'){
                $status = 'Refunded';

                $update = $this
                        ->where('id', $record->id)
                        ->where('status', 'Pending')
                        ->update([
                            'status' => $status,
                        ]);
                if(!$update){
                    throw new \Exception( lang('refund_record_status_changed') );
                }

                $result = $this->refundToGateway([
                    'order_id'  => $record['order_id'],
                    'amount'    => $record['amount'],
                    'gateway'   => $record['gateway'] ?: $order['gateway'],
                    'client_id' => $record['client_id'],
                    'host_id'   => $record['host_id'],
                ]);
                if($result['status'] == 200){
                    $transactionId = $result['data']['transaction_id'];
                    $refundTransactionNumber = $result['data']['transaction_number'];
                }else{
                    throw new \Exception( $result['msg'] );
                }
            }elseif ($record['type']=='transaction'){
                $status = 'Refunding';

                $update = $this
                        ->where('id', $record->id)
                        ->where('status', 'Pending')
                        ->update([
                            'status' => $status,
                        ]);
                if ($order['type']=='recharge'){
                    update_credit([
                        'type' => 'Refund',
                        'amount' => -$record['amount'],
                        'notes' => lang('order_refund', ['{id}' => $record['order_id']]),
                        'client_id' => $record['client_id'],
                        'order_id' => $record['order_id'],
                        'host_id' => $record['host_id'],
                    ]);
                }
                if(!$update){
                    throw new \Exception( lang('refund_record_status_changed') );
                }
            }else if($record['type'] == 'credit' || $record['refund_gateway'] == 0 ) {
                $status = 'Refunded';

                $update = $this
                        ->where('id', $record->id)
                        ->where('status', 'Pending')
                        ->update([
                            'status'            => 'Refunded',
                            'transaction_id'    => 0,
                            'refund_time'       => time(),
                        ]);
                if(!$update){
                    throw new \Exception( lang('refund_record_status_changed') );
                }

                // 退余额不需要交易流水
                update_credit([
                    'type' => 'Refund',
                    'amount' => $record['refund_credit'],
                    'notes' => lang('order_refund', ['{id}' => $record['order_id']]),
                    'client_id' => $record['client_id'],
                    'order_id' => $record['order_id'],
                    'host_id' => $record['host_id'],
                ]);
            }else{
                $status = 'Refunded';

                $update = $this
                    ->where('id', $record->id)
                    ->where('status', 'Pending')
                    ->update([
                        'status'            => $status,
                        'transaction_id'    => 0,
                        'refund_time'       => time(),
                    ]);
                if(!$update){
                    throw new \Exception( lang('refund_record_status_changed') );
                }

                // 信用额退款
                if($order['gateway'] == 'credit_limit'){
                    $hookRes = hook('refund_record_pending', ['order'=>$order, 'refund_gateway'=>$record['refund_gateway'], 'refund_credit'=>$record['refund_credit'], 'refund_record'=>$record ]);
                    $result = [
                        'status' => 400,
                        'msg'    => lang('refund_record_cannot_refund_to_gateway'),
                    ];
                    foreach($hookRes as $v){
                        if(isset($v['status']) && isset($v['msg'])){
                            $result = $v;
                        }
                    }
                    if($result['status'] != 200){
                        throw new \Exception( $result['msg'] );
                    }
                }else{
                    // 余额部分退款
                    if($record['refund_credit'] > 0){
                        update_credit([
                            'type'      => 'Refund',
                            'amount'    => $record['refund_credit'],
                            'notes'     => lang('order_refund', ['{id}' => $record['order_id']]),
                            'client_id' => $record['client_id'],
                            'order_id'  => $record['order_id'],
                            'host_id'   => $record['host_id'],
                        ]);
                    }

                    // 退款渠道部分
                    $result = $this->refundToGateway([
                        'order_id'  => $record['order_id'],
                        'amount'    => $record['refund_gateway'],
                        'gateway'   => $record['gateway'] ?: $order['gateway'],
                        'client_id' => $record['client_id'],
                        'host_id'   => $record['host_id'],
                    ]);
                    if($result['status'] == 200){
                        if ($order['type']=='recharge'){
                            update_credit([
                                'type' => 'Refund',
                                'amount' => -$record['amount'],
                                'notes' => lang('order_refund', ['{id}' => $record['order_id']]),
                                'client_id' => $record['client_id'],
                                'order_id' => $record['order_id'],
                                'host_id' => $record['host_id'],
                            ]);
                        }
                        $transactionId = $result['data']['transaction_id'];
                        $refundTransactionNumber = $result['data']['transaction_number'];
                    }else{
                        throw new \Exception( $result['msg'] );
                    }
                }
            }

            // 已退款
            if($status == 'Refunded'){
                $record->save([
                    'status'         => 'Refunded',
                    'transaction_id' => $transactionId,
                    'refund_time'    => time(),
                ]);

                // 计算退款总金额
                $orderRefund = $OrderModel->orderRefundIndex([
                    'order' => $record['order_id'],
                ]);

                $credit = bcsub($order['credit'], $record['refund_credit'], 2);

                $order->save([
                    'credit'                    => max($credit, 0),
                    'refund_amount'             => bcadd($orderRefund['refund_credit'], $orderRefund['refund_gateway'], 2),
                    'status'                    => 'Refunded',
                    'update_time'               => time(),
                    'is_refund'                 => 0,
                    'refund_gateway_to_credit'  => $credit < 0 ? bcadd($order['refund_gateway_to_credit'], abs($credit), 2) : $order['refund_gateway_to_credit'],
                ]);

                hook('after_order_refund',['id'=>$record['order_id']]);

                if(!empty($refundTransactionNumber)){
                    active_log(lang("order_orginal_refund_success",['{transaction_number}'=>$refundTransactionNumber]), 'order', $order->id);
                }

                if(!empty($record['refund_credit'])){
                    $client = ClientModel::find($order['client_id']);
                    if(empty($client)){
                        $clientName = '#'.$order['client_id'];
                    }else{
                        $clientName = 'client#'.$client->id.'#'.$client->username.'#';
                    }

                    active_log(lang('admin_refund_user_order_credit', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$record['refund_credit']]), 'order', $order->id);
                }
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            active_log(lang('order_orginal_refund_fail').$e->getMessage(), 'order', $record['order_id']);
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-05-10
     * @title 退款拒绝
     * @desc  退款拒绝
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     * @param string reason - 拒绝原因 required
     */
    public function rejectRefundRecord($param)
    {
        // 验证ID
        $record = $this->find($param['id']);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Pending'){
            return ['status'=>400,'msg'=>lang('refund_record_pending_reject')];
        }

        $this->startTrans();
        try {
            $update = $this
                    ->where('id', $param['id'])
                    ->where('status', 'Pending')
                    ->update([
                        'status' => 'Reject',
                        'reason' => $param['reason'],
                    ]);
            if(!$update){
                throw new \Exception( lang('refund_record_status_changed') );
            }

            OrderModel::where('id', $record['order_id'])->update([
                'is_refund'     => 0,
                'update_time'   => time(),
            ]);

            # 记录日志
            active_log(lang('admin_reject_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-05-10
     * @title 已退款
     * @desc 已退款
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     * @param string transaction_number - 交易流水ID required
     */
    public function redundedRefundRecord($param)
    {
        if (!isset($param['transaction_number']) || empty($param['transaction_number'])){
            return ['status'=>400, 'msg'=>lang('param_error') ];
        }

        // 验证ID
        $id = $param['id']??0;
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Refunding'){
            return ['status'=>400,'msg'=>lang('refund_record_pending_refunding')];
        }

        $this->startTrans();
        try {
            $update = $this
                    ->where('id', $id)
                    ->where('status', 'Refunding')
                    ->update([
                        'status' => 'Refunded',
                    ]);
            if(!$update){
                throw new \Exception( lang('refund_record_status_changed') );
            }

            # 记录日志
            active_log(lang('admin_refunded_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            if ($record['type']=='transaction'){
                $OrderModel = new OrderModel();
                $order = $OrderModel->find($record['order_id']);

                $amount = TransactionModel::where('order_id', $record['order_id'])->sum('amount');
                // $refundAmount = RefundRecordModel::where('order_id', $record['order_id'])
                //     ->whereIn('status',['Refunded'])
                //     ->where('type', 'credit')
                //     ->sum('amount');

                // $amount = $amount-$refundAmount;
                if($record['amount']>$amount){
                    throw new \Exception( lang('refund_amount_not_enough') );
                }
                // if($record['amount']>($order['amount']-$order['refund_amount'])){
                //     throw new \Exception(lang('refund_amount_not_enough'));
                // }
                // 获取支付接口名称
                $gateway = PluginModel::where('module', 'gateway')->where('name', $record['gateway'])->find();
                if(empty($gateway)){
                    throw new \Exception(lang('gateway_is_not_exist'));
                }
                $gateway['config'] = json_decode($gateway['config'],true);
                $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];

                $transaction = TransactionModel::create([
                    'order_id' => $record['order_id'],
                    'client_id' => $record['client_id'],
                    'amount' => -$record['amount'],
                    'gateway' => $record['gateway'],
                    'gateway_name' => $gateway['title'] ?? '',
                    'transaction_number' => $param['transaction_number'],
                    'create_time' => time(),
                    'host_id'   => $record['host_id'],
                ]);
                $record->save([
                    'status'            => 'Refunded',
                    'transaction_id'    => $transaction->id,
                    'refund_time'       => time(),
                ]);
                
                // 计算退款总金额
                $orderRefund = $OrderModel->orderRefundIndex([
                    'order' => $record['order_id'],
                ]);

                $order->save([
                    'refund_amount'     => bcadd($orderRefund['refund_credit'], $orderRefund['refund_gateway'], 2),
                    'status'            => 'Refunded',
                    'update_time'       => time(),
                    'is_refund'         => 0,
                ]);

                hook('after_order_refund',['id'=>$record['order_id']]);

                $client = ClientModel::find($order['client_id']);
                if(empty($client)){
                    $clientName = '#'.$order['client_id'];
                }else{
                    $clientName = 'client#'.$client->id.'#'.$client->username.'#';
                }

                active_log(lang('admin_refund_user_order_transaction', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$record['amount'], '{transaction}'=>$param['transaction_number']]), 'order', $order->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * @时间 2024-11-26
     * @title 退款原渠道
     * @desc  退款原渠道
     * @author hh
     * @version v1
     * @param   int param.order_id - 订单ID require
     * @param   float param.amount - 退款金额 require
     * @param   string param.gateway - 退款网关 require
     * @param   int param.client_id - 用户ID require
     * @param   int param.host_id - 产品ID
     * @return  int transaction_id - 流水ID
     * @return  string transaction_number - 流水号
     */
    public function refundToGateway($param)
    {
        $transaction = TransactionModel::where('order_id', $param['order_id'])->find();
        if(empty($transaction)){
            return ['status'=>400, 'msg'=>lang('transaction_is_not_exist') ];
        }
        $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
        if(empty($gateway)){
            return ['status'=>400, 'msg'=>lang('gateway_is_not_exist') ];
        }

        $refundData = [
            'transaction_number' => $transaction['transaction_number'],
            'amount'             => $param['amount'],
            'out_request_no'     => time() . rand_str(8,'NUMBER'), // 退款请求号：标识一次退款请求，需要保证在交易号下唯一，如需部分退款，则此参数必传
            'total_fee'          => $transaction['amount'], // 总金额
        ];

        $refundResult = plugin_reflection($param['gateway'], $refundData, 'gateway', 'handle_refund');
        if (isset($refundResult['status']) && $refundResult['status']==200){
            $refundTransactionNumber = $refundResult['data']['trade_no']??"";
            if (!empty($refundTransactionNumber)){
                $transaction = TransactionModel::create([
                    'order_id' => $param['order_id'],
                    'client_id' => $param['client_id'],
                    'amount' => -$param['amount'],
                    'gateway' => $param['gateway'],
                    'gateway_name' => $gateway['title'] ?? '',
                    'transaction_number' => $refundTransactionNumber,
                    'create_time' => time(),
                    'host_id'   => $param['host_id'] ?? 0,
                ]);

                $result = [
                    'status'=> 200,
                    'msg'   => lang('success_message'),
                    'data'  => [
                        'transaction_id'    => (int)$transaction['id'],
                        'transaction_number'=> $refundTransactionNumber,
                    ],
                ];
            }else{
                $result = ['status'=>400, 'msg'=>lang('gateway_return_error_other') ];
            }
        }else{
            $result = ['status'=>400, 'msg'=>lang('gateway_return_error').$refundResult['msg'] ?? lang('gateway_not_exist') ];
        }
        return $result;
    }

    /**
     * @时间 2024-12-03
     * @title 订单是否有插件退款
     * @desc  订单是否有插件退款
     * @author hh
     * @version v1
     * @param   int id - 订单ID require
     * @return  bool
     */
    public function isOrderAddonRefund($id)
    {
        $hostId = OrderItemModel::where('order_id', $id)->where('host_id', '>', 0)->column('host_id');

        $refund = false;

        if(!empty($hostId)){
            $refundRecord = $this
                        ->whereIn('host_id', $hostId)
                        ->where('refund_type', 'addon')
                        ->whereIn('status', ['Pending','Suspending','Suspend','Suspended'])
                        ->find();

            $refund = !empty($refundRecord);
        }
        return $refund;
    }


}

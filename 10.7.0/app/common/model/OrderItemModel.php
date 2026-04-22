<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 订单子项模型
 * @desc 订单子项模型
 * @use app\common\model\OrderItemModel
 */
class OrderItemModel extends Model
{
	protected $name = 'order_item';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'order_id'      => 'int',
        'client_id'     => 'int',
        'host_id'       => 'int',
        'product_id'    => 'int',
        'type'      	=> 'string',
        'rel_id'        => 'int',
        'description'   => 'string',
        'amount'        => 'float',
        'gateway'       => 'string',
        'gateway_name'  => 'string',
        'notes'         => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * @时间 2024-11-26
     * @title 订单内页产品退款列表
     * @desc  订单内页产品退款列表
     * @author hh
     * @version v1
     * @param  int param.order_id - 订单ID require
     * @param  string param.keywords - 关键字:商品名称,产品标识,备注
     * @return int list[].id - 订单子项ID
     * @return int list[].product_id - 商品ID
     * @return int list[].host_id - 产品ID
     * @return string list[].product_name - 商品名称
     * @return string list[].host_name - 产品标识
     * @return int list[].ip_num - IP数量
     * @return string list[].dedicate_ip - 主IP
     * @return string list[].assign_ip - 附加IP
     * @return string list[].description - 描述
     * @return string list[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     * @return string list[].amount - 金额
     * @return string list[].refund_credit - 退款余额金额
     * @return string list[].refund_gateway - 退款渠道金额
     * @return string list[].refund_total - 退款总金额
     * @return string list[].refund_status - 退款状态(not_refund=未退款,part_refund=部分退款,all_refund=全部退款,addon_refund=插件退款)
     * @return string list[].profit - 利润
     * @return int list[].agent - 代理订单1是0否
     */
    public function orderHostRefundList($param)
    {
        $orderId = $param['order_id'] ?? 0;

        // 判断订单是否全额退款
        $order = OrderModel::where('id', $orderId)->find();
        $allRefund = false;
        if(!empty($order)){
            if($order['refund_amount'] >= $order['amount']){
                $allRefund = true;
            }
        }

        $orderItem = $this
                    ->field('oi.id,oi.product_id,oi.host_id,p.name product_name,h.name host_name,hi.ip_num,hi.dedicate_ip,hi.assign_ip,oi.description,h.status host_status,oi.amount,uo.id upstream_order_id,uo.profit')
                    ->alias('oi')
                    ->leftJoin('product p', 'oi.product_id=p.id')
                    ->leftJoin('host h', 'oi.host_id=h.id')
                    ->leftJoin('host_ip hi', 'oi.host_id=hi.host_id')
                    ->leftJoin('upstream_order uo', 'uo.host_id=oi.host_id AND uo.order_id=oi.order_id')
                    ->where('oi.order_id', $orderId)
                    ->select()
                    ->toArray();

        // 退款
        $refundHost = [];
        $addonHostRefundAmount = [];

        if(!empty($orderItem)){

            // 订单产品退款部分
            $RefundRecordModel = new RefundRecordModel();
            $refundRecord = $RefundRecordModel
                        ->where('order_id', $orderId)
                        ->where('status', 'Refunded')
                        ->where('host_id', '>', 0)
                        ->where('refund_type', 'order')
                        ->select()
                        ->toArray();

            foreach($refundRecord as $v){
                if(!isset($refundHost[ $v['host_id'] ])){
                    $refundHost[ $v['host_id'] ] = [
                        'credit'            => 0,
                        'gateway_amount'    => 0,
                    ];
                }

                // if(!empty($v['gateway'])){
                    $refundHost[ $v['host_id'] ]['gateway_amount'] = bcadd($refundHost[ $v['host_id'] ]['gateway_amount'], $v['refund_gateway'], 2);
                // }else{
                    $refundHost[ $v['host_id'] ]['credit'] = bcadd($refundHost[ $v['host_id'] ]['credit'], $v['refund_credit'], 2);
                // }
            }

            $hostId = array_column($orderItem, 'host_id');

            $addonHostRefundAmount = $RefundRecordModel
                                ->field('id,host_id,amount')
                                ->where('status', 'Refunded')
                                ->where('host_id', '>', 0)
                                ->whereIn('host_id', $hostId)
                                ->where('refund_type', 'addon')
                                ->select()
                                ->toArray();
            $addonHostRefundAmount = array_column($addonHostRefundAmount, 'amount', 'host_id') ?? [];
        }

        $list = [];
        foreach($orderItem as $k=>$v){
            if($v['host_id'] > 0){
                if(!isset($list[ 'host_' . $v['host_id'] ])){
                    $list[ 'host_' . $v['host_id'] ] = $v;
                }else{
                    $list[ 'host_' . $v['host_id'] ]['amount'] = bcadd($list[ 'host_' . $v['host_id'] ]['amount'], $v['amount'], 2);
                }
            }else{
                // 非产品的项
                $v['product_name'] = '';
                $v['host_name'] = '';
                $v['ip_num'] = 0;
                $v['dedicate_ip'] = '';
                $v['assign_ip'] = '';
                $v['refund_status'] = '';
                $v['host_status'] = '';
                $v['refund_total'] = '0.00';
                $v['refund_credit'] = '0.00';
                $v['refund_gateway'] = '0.00';

                $list[] = $v;
            }
        }

        foreach($list as $k=>$v){
            // 这里匹配关键字
            if(isset($param['keywords']) && $param['keywords'] !== ''){
                if(stripos($v['product_name'], $param['keywords']) === false && stripos($v['host_name'], $param['keywords']) === false && stripos($v['description'], $param['keywords']) === false){
                    unset($list[$k]);
                    continue;
                }
            }
            if($v['host_id'] == 0){
                $list[$k]['profit'] = '0.00';
                $list[$k]['agent'] = 0;
                continue;
            }
            $list[$k]['ip_num'] = $v['ip_num'] ?? 0;
            $list[$k]['dedicate_ip'] = $v['dedicate_ip'] ?? '';
            $list[$k]['assign_ip'] = $v['assign_ip'] ?? '';

            // 插件退款
            if(isset($addonHostRefundAmount[ $v['host_id'] ])){
                $list[$k]['refund_credit'] = amount_format($addonHostRefundAmount[ $v['host_id'] ]);
                $list[$k]['refund_gateway'] = '0.00';
                $list[$k]['refund_total'] = amount_format($addonHostRefundAmount[ $v['host_id'] ]);
                $list[$k]['refund_status'] = 'addon_refund';
            }else if(isset($refundHost[ $v['host_id'] ])){
                // 订单单个退款金额
                $list[$k]['refund_credit'] = amount_format($refundHost[ $v['host_id'] ]['credit']);
                $list[$k]['refund_gateway'] = amount_format($refundHost[ $v['host_id'] ]['gateway_amount']);
                $list[$k]['refund_total'] = amount_format(bcadd($list[$k]['refund_credit'], $list[$k]['refund_gateway'], 2));
                $list[$k]['refund_status'] = '';
                
                // 状态判断
                if($v['amount'] > 0){
                    // 全部退款
                    if($list[$k]['refund_total'] >= $v['amount']){
                        $list[$k]['refund_status'] = 'all_refund';
                    }else if($list[$k]['refund_total'] > 0){
                        $list[$k]['refund_status'] = 'part_refund';
                    }else{
                        $list[$k]['refund_status'] = 'not_refund';
                    }
                }
            }else{
                // 并没退款
                $list[$k]['refund_credit'] = '0.00';
                $list[$k]['refund_gateway'] = '0.00';
                $list[$k]['refund_total'] = '0.00';
                $list[$k]['refund_status'] = 'not_refund';
            }
            if($allRefund){
                $list[$k]['refund_total'] = amount_format($v['amount']);
                $list[$k]['refund_status'] = 'all_refund';
            }

            $list[$k]['profit'] = amount_format($v['profit']);
            $list[$k]['agent'] = !empty($v['upstream_order_id']) ? 1 : 0;

            unset($list[$k]['upstream_order_id']);
        }

        $list = array_values($list);

        return ['list'=>$list ];
    }





}

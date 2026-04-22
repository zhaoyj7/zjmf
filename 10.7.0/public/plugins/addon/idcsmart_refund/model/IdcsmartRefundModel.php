<?php
namespace addon\idcsmart_refund\model;

use addon\idcsmart_refund\IdcsmartRefund;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpstreamProductModel;
use think\db\Query;
use think\Model;
use app\common\model\RefundRecordModel;
use app\common\model\OnDemandPaymentQueueModel;

/*
 * @author wyh
 * @time 2022-07-06
 */
class IdcsmartRefundModel extends Model
{
    protected $name = 'addon_idcsmart_refund';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'client_id'                        => 'int',
        'host_id'                          => 'int',
        'amount'                           => 'float',
        'suspend_reason'                   => 'string',
        'type'                             => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'status'                           => 'string',
        'reject_reason'                    => 'string',
        'update_time'                      => 'int',
        'refund_record_id'                 => 'int',
    ];

    /**
     * 时间 2022-07-07
     * @title 停用列表
     * @desc 停用列表
     * @author wyh
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @param string param.keywords - 关键字搜索:停用原因,申请人
     * @param array param.host_status - 产品状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param array param.status - 申请状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @param  int param.refund_record_id - 搜索:退款记录ID
     * @return array list - 停用列表
     * @return int list[].id - ID
     * @return int list[].client_name - 申请人
     * @return int list[].product_name - 申请商品
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return int list[].type - 类型:一共四种类型，可退款的有：到期退款Artificial、立即退款Auto，不可退款的有：Expire到期停用、Immediate立即停用
     * @return int list[].admin_name - 审核人
     * @return int list[].create_time - 申请时间
     * @return int list[].due_time - 到期时间
     * @return int list[].refund_product_type - 退款类型:Artificial审核后退款，Auto直接退款
     * @return string list[].host_status - 产品状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string list[].status - 申请状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @return string list[].suspend_reason - 申请理由
     * @return string list[].update_time - 审核时间
     * @return int count - 停用总数
     */
    public function refundList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'r.id';
        }
        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('r.suspend_reason|c.username','like',"%{$param['keywords']}%");
            }
            if(isset($param['host_status']) && !empty($param['host_status']) && is_array($param['host_status'])){
                $query->whereIn('h.status',$param['host_status']);
            }
            if(isset($param['status']) && !empty($param['status']) && is_array($param['status'])){
                $query->whereIn('r.status',$param['status']);
            }
            if(isset($param['refund_record_id']) && !empty($param['refund_record_id'])){
                $query->where('r.refund_record_id', $param['refund_record_id']);
            }
        };

        $refunds = $this->alias('r')
            ->field('r.client_id,r.host_id,r.id,r.suspend_reason,c.username as client_name,p.name as product_name,
            r.amount,r.type,a.name as admin_name,r.create_time,h.due_time,h.status host_status,r.status,
            rp.type as refund_product_type,r.update_time')
            ->leftJoin('client c','c.id=r.client_id')
            ->leftJoin('host h','h.id=r.host_id AND h.is_delete=0')
            ->leftJoin('product p','p.id=h.product_id')
            ->leftJoin('admin a','a.id=r.admin_id')
            ->leftJoin('addon_idcsmart_refund_product rp','rp.product_id=p.id')
            ->withAttr('type',function ($value,$data){
                if ($data['amount']>=0){ # 可退款
                    if ($data['amount']==0 && $value=='Expire'){
                        return 'Artificial';
                    }
                    return 'Auto';
                }else{ # 不可退款
                    return $value;
                }
            })
            ->withAttr('admin_name',function ($value){
                if (is_null($value)){
                    return '';
                }
                return $value;
            })
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

//        foreach ($refunds as &$refund){
//            unset($refund['refund_product_type']);
//        }

        $count = $this->alias('r')
            ->leftJoin('client c','c.id=r.client_id')
            ->leftJoin('host h','h.id=r.host_id AND h.is_delete=0')
            ->leftJoin('product p','p.id=h.product_id')
            ->leftJoin('admin a','a.id=r.admin_id')
            ->leftJoin('addon_idcsmart_refund_product rp','rp.product_id=p.id')
            ->where($where)
            ->count();

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$refunds,'count'=>$count]];
    }

    /**
     * 时间 2022-07-08
     * @title 通过
     * @desc 通过
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function pending($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if ($refund->status != 'Pending'){
                throw new \Exception(lang_plugins('refund_refund_only_pending'));
            }

            $refund->save([
                'status' => 'Suspending',
                'admin_id' => get_admin_id(),
                'update_time' => time()
            ]);

            // 同步修改退款记录
            if(!empty($refund['refund_record_id'])){
                RefundRecordModel::where('id', $refund['refund_record_id'])->update([
                    'status'        => 'Suspending',
                ]);
            }

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_pending_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$refund['amount'],'{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_refund', $id);
			
			//$host = (new HostModel())->find($refund['host_id']);
			//$product = (new ProductModel())->find($host['product_id']);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        hook('after_host_refund_pending', $param);

        $this->dailyCron(true);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 驳回
     * @desc 驳回
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     * @param string reject_reason - 驳回原因 required
     */
    public function reject($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if ($refund->status != 'Pending'){
                throw new \Exception(lang_plugins('refund_refund_only_pending'));
            }

            $refund->save([
                'status' => 'Reject',
                'reject_reason' => $param['reject_reason']??'',
                'admin_id' => get_admin_id(),
                'update_time' => time()
            ]);

            // 同步修改退款记录
            if(!empty($refund['refund_record_id'])){
                RefundRecordModel::where('id', $refund['refund_record_id'])->update([
                    'status'        => 'Reject',
                    'reason'        => $param['reject_reason'] ?? '',
                ]);
            }

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_reject_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{reason}'=>$refund['suspend_reason']]), 'addon_idcsmart_refund', $id);
			
			$host = (new HostModel())->find($refund['host_id']);
			$product = (new ProductModel())->find($host['product_id']);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        system_notice([
            'name' => 'admin_refund_reject',
            'email_description' => lang_plugins('admin_refund_reject_send_mail'),
            'sms_description' => lang_plugins('admin_refund_reject_send_sms'),
            'task_data' => [
                'client_id'=>$client['id'],//客户ID
                'host_id' => $refund['host_id'],
                'template_param'=>[
                    'product_name' => $product['name'].'-'.$host['name'],//产品名称
                ],
            ],
        ]);

        hook('after_host_refund_reject', $param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 取消
     * @desc 取消
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function cancel($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if (!in_array($refund->status,['Pending','Suspending','Suspend'])){
                throw new \Exception(lang_plugins('refund_refund_only_pending_or_suspending'));
            }

            $refund->save([
                'status' => 'Cancelled',
                'admin_id' => get_admin_id()?:0,
                'update_time' => time()
            ]);

            // 同步修改退款记录
            if(!empty($refund['refund_record_id'])){
                RefundRecordModel::where('id', $refund['refund_record_id'])->update([
                    'status'        => 'Cancelled',
                ]);
            }

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_cancel_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                '{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{host}'=>'host#'.$refund['host_id']]), 'addon_idcsmart_refund', $id);
			
			
			$host = (new HostModel())->find($refund['host_id']);
			$product = (new ProductModel())->find($host['product_id']);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        system_notice([
            'name' => 'client_refund_cancel',
            'email_description' => lang_plugins('client_refund_cancel_send_mail'),
            'sms_description' => lang_plugins('client_refund_cancel_send_sms'),
            'task_data' => [
                'client_id'=>$client['id'],//客户ID
                'host_id' => $refund['host_id'],
                'template_param'=>[
                    'product_name' => $product['name'].'-'.$host['name'],//产品名称
                ],
            ],
        ]);

        hook('after_host_refund_cancel', $param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 停用页面
     * @desc 停用页面
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID required
     * @return int allow_refund - 是否允许退款:0否,1是
     * @return int reason_custom - 是否允许自定义原因:0否,1是
     * @return array reasons - 停用原因
     * @return int reasons[].id - 原因id
     * @return string reasons[].content - 内容
     * @return object host - 产品
     * @return int host.create_time - 订购时间
     * @return float host.first_payment_amount - 订购金额
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return array config_option - 产品配置
     */
    public function refundPage($param)
    {
        $hostId = intval($param['host_id']);

        $HostModel = new HostModel();
        $host = $HostModel->where('id',$hostId)
            ->where('client_id',get_client_id())
            ->find();
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('refund_host_is_not_exist')];
        }

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
        $reasons = $IdcsmartRefundReasonModel->field('id,content')
            ->select()
            ->toArray();
        # 是否可自定义原因
        $IdcsmartRefund = new IdcsmartRefund();
        $config = $IdcsmartRefund->getConfig();

        $productId = $host->product_id;
        # 获取配置项,调模块
        $product = (new ProductModel())->find($productId);
        $upstreamProduct = UpstreamProductModel::where('product_id', $productId)->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $configOption = $ResModuleLogic->allConfigOption($product,$hostId);
        }else{
            $configOption = (new ModuleLogic())->allConfigOption($product);
        }


        if ($this->allowRefund($hostId)){ # 可退款
            $data = [
                'allow_refund' => 1,
                'reason_custom' => $config['reason_custom']??0,
                'config_option' => $configOption,
                'reasons' => $reasons,
                'host' => [
                    'create_time' => $host->create_time,
                    'first_payment_amount' => $host->first_payment_amount,
                    'amount' => $this->refundAmount($hostId)[0]??0 # 退款金额
                ],
            ];
        }else{
            $data = [
                'allow_refund' => 0,
                'reason_custom' => $config['reason_custom']??0,
                'config_option' => $configOption,
                'reasons' => $reasons,
                'host' => [
                    'create_time' => $host->create_time,
                ],
            ];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-07-08
     * @title 停用
     * @desc 停用
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID required
     * @param mixed suspend_reason - 停用原因,产品可以自定义原因时,输入框,传字符串;产品不可自定义原因时,传停用原因ID数组
     * @param string type - 停用时间:Expire到期,Immediate立即
     */
    public function refund($param)
    {
        $clientId = get_client_id();
        $HostModel = new HostModel();
        $host = $HostModel->find($param['host_id']);
        if(empty($host) || $host['is_delete'] || $host['client_id'] != get_client_id()){
            return ['status' => 400, 'msg' => lang_plugins('host_is_not_exist')];
        }
        // 按需停用
        if($host['billing_cycle'] == 'on_demand'){
            $param['id'] = $param['host_id'];
            return $this->onDemandTerminate($param);
        }

        $this->startTrans();

        try{
            $IdcsmartRefund = new IdcsmartRefund();
            $config = $IdcsmartRefund->getConfig();

            # 产品是否人工审核
            $productId = $host->product_id;

            $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
            $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();

            # 退款金额
            $baseAmount = 0;
            if ($param['type'] == 'Expire'){
                // 后台商品列表未添加，为-1不能退款。
                if (empty($refundProduct)){
                    $amount = -1;
                }else{
                    $amount = 0;
                }
            }else{
                if ($this->allowRefund($param['host_id'])){
                    $return = $this->refundAmount($param['host_id']);
                    $amount = $return[0];
                    $baseAmount = $return[1];
                }else{
                    $amount = -1;
                }
//                $amount = $this->allowRefund($param['host_id'])?$this->refundAmount($param['host_id']):-1;# -1表示不需要退款
            }

            if (isset($refundProduct['type']) && $refundProduct['type'] == 'Artificial'){
                $status = 'Pending';
            }else{
                $status = 'Suspending';
            }

            // wyh 20230511 退款商品且产品使用信用额未还款时
            $hookRes = hook('before_host_refund',[
                'host_id'           => $param['host_id'],
                'is_refund_product' => !empty($refundProduct), // 是否是退款商品
            ]);
            // hh 20241118 改为多个,产品转移时也不能停用
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status']==400){
                    throw new \Exception($v['msg']);
                }
            }

            $IdcsmartRefundModel = new IdcsmartRefundModel();
            $refunded = $IdcsmartRefundModel->where('host_id',$param['host_id'])
                ->whereNotIn('status',['Reject','Cancelled'])
                ->find();
            if (!empty($refunded)){
                throw new \Exception(lang_plugins('refund_product_refunded'));
            }

            # 停用原因
            if (isset($config['reason_custom']) && $config['reason_custom']==1){
                $suspendReason = $param['suspend_reason'];
            }else{
                $suspendReason = '';
                $suspendReasons = $param['suspend_reason'];
                $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
                if (is_array($suspendReasons) && !empty($suspendReasons)){
                    foreach ($suspendReasons as $item){
                        $refundReason = $IdcsmartRefundReasonModel->find($item);
                        $suspendReason .= $refundReason['content'] . "\n";
                    }
                    $suspendReason = rtrim($suspendReason,"\n");
                }elseif (is_string($suspendReasons)){
                    $suspendReason = $suspendReasons;
                }
            }

            // 如果需要退款
            $refundRecordId = 0;
            if($amount > 0){

                $OrderItemModel = new OrderItemModel();
                $orderId = $OrderItemModel
                        ->where('client_id', $clientId)
                        ->where('host_id', $param['host_id'])
                        ->column('order_id');
                if(!empty($orderId)){
                    $RefundRecordModel = new RefundRecordModel();
                    $orderRefund = $RefundRecordModel
                                    ->whereIn('order_id', $orderId)
                                    ->whereIn('status', ['Pending','Refunded','Refunding'])
                                    ->find();
                    if(!empty($orderRefund)){
                        throw new \Exception( lang_plugins('refund_host_cannot_suspend') );
                    }
                }

                $refundRecord = RefundRecordModel::create([
                    'order_id'          => 0,
                    'client_id'         => $clientId,
                    'admin_id'          => 0,
                    'type'              => 'credit',
                    'transaction_id'    => 0,
                    'amount'            => $amount,
                    'create_time'       => time(),
                    'status'            => $status,
                    'host_id'           => $param['host_id'],
                    'refund_type'       => 'addon',
                    'refund_credit'     => $amount,
                ]);
                $refundRecordId = $refundRecord->id;
            }

            $refund = $this->create([
                'client_id' => get_client_id(),
                'host_id' => $param['host_id'],
                'amount' => $amount,
                'suspend_reason' => $suspendReason?:'',
                'type' => $param['type'],
                'create_time' => time(),
                'status' => $status,
                'refund_record_id' => $refundRecordId,
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);

            active_log(lang_plugins('refund_refund_host', ['{client}'=>'client#'.get_client_id().'#'. request()->client_name .'#','{host}'=>'host#'.$param['host_id'].'#'.$product['name'].'#','{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount==-1?0:$amount,'{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_refund', $refund->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            active_log(lang_plugins('refund_refund_host_fail', ['{client}'=>'client#'.get_client_id().'#'. request()->client_name .'#','{host}'=>'host#'.$param['host_id'],'{reason}'=>$e->getMessage()]), 'addon_idcsmart_refund', 0);
            return ['status'=>400,'msg'=>$e->getMessage()];
        }
        $isApi = request()->is_api ?? false;
        if (!$isApi){
            system_notice([
                'name' => 'client_create_refund',
                'email_description' => lang_plugins('client_create_refund_send_mail'),
                'sms_description' => lang_plugins('client_create_refund_send_sms'),
                'task_data' => [
                    'client_id'=>$refund['client_id'],//客户ID
                    'host_id' => $refund['host_id'],
                    'template_param'=>[
                        'product_name' => $product['name'].'-'.$host['name'],//产品名称
                    ],
                ],
            ]);
        }
        $param['amount'] = $amount;
        $param['base_amount'] = $baseAmount;
        hook('after_host_refund_create', $param);

        //wyh 20240625 更改为立即退款
        $this->dailyCron(true);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 判断产品是否可退款
    private function allowRefund($hostId)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        $productId = $host->product_id;

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
        $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();
        $allowRefund = false;

        if(empty($host) || $host['is_delete'] || $host['billing_cycle'] == 'on_demand'){
            return $allowRefund;
        }
        // 所在订单是否有订单退款
        $OrderItemModel = new OrderItemModel();
        $orderId = $OrderItemModel
                ->where('client_id', $host['client_id'])
                ->where('host_id', $hostId)
                ->column('order_id');

        if(!empty($orderId)){
            $RefundRecordModel = new RefundRecordModel();
            $orderRefund = $RefundRecordModel
                            ->whereIn('order_id', $orderId)
                            ->whereIn('status', ['Pending','Refunded','Refunding'])
                            ->find();
            if(!empty($orderRefund)){
                return $allowRefund;
            }
        }
        if (!empty($refundProduct)){

            $OrderModel = new OrderModel();

            $condition1 = $condition2 = true;

            if ($refundProduct->require == 'First'){
                # 非首次订购
                $otherOrder = $OrderModel->where('client_id',get_client_id())->where('id','<>',$host->order_id)->find();
                if (!empty($otherOrder)){
                    $condition1 = false;
                }
            }elseif ($refundProduct->require == 'Same'){
                # 同商品非首次订购
                $otherOrder = $OrderModel->alias('o')
                    ->leftJoin('order_item oi','oi.order_id=o.id')
                    ->leftJoin('host h','h.id=oi.host_id')
                    ->where('oi.type','host')
                    ->where('h.product_id',$productId)
                    ->where('o.id','<>',$host->order_id)
                    ->find();
                if (!empty($otherOrder)){
                    $condition1 = false;
                }
            }

            # 购买后X天内
            if ($refundProduct->range_control && time()>($host->create_time+24*3600*$refundProduct->range)){
                $condition2 = false;
            }

            if ($condition1 && $condition2){
                $allowRefund = true;
            }
        }

        return $allowRefund;
    }

    # 计算产品退款金额
    private function refundAmount($hostId)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        $productId = $host->product_id;

        // 用户ID,只能退当前用户所有订单金额
        $clientId = $host['client_id'] ?? 0;

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
        $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();

        $dueTime = $host->due_time;
        $diffTime = $dueTime-time()>0?$dueTime-time():0;
        # 总周期时间
        $billingCycleTime = $dueTime - $host->active_time;

        # 计算基础金额
        $OrderItemModel = new OrderItemModel();
        $orderItem =  $OrderItemModel->where('host_id',$hostId)->where('type','host')->where('client_id', $clientId)->find();
        $baseAmount = $orderItem['amount'] ?? 0;

        # 插件修改金额
        $hookResults = hook('after_refund',['host_id'=>$hostId, 'client_id'=>$clientId ]);
        foreach ($hookResults as $hookResult){
            $baseAmount = bcadd($baseAmount,(float)$hookResult,2);
        }
        # 升降级金额(操作产品)
        $upgrades = $OrderItemModel->alias('oi')
            ->field('oi.order_id,oi.amount')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('oi.host_id',$hostId)
            ->where('oi.type','upgrade')
            ->where('o.status','Paid')
            ->where('o.client_id', $clientId)
            ->select()
            ->toArray();
        $manualUpgradeOrderIds = [];
        foreach ($upgrades as $upgrade){
            $manualUpgradeOrderIds[] = $upgrade['order_id'];
            $baseAmount = bcadd($baseAmount,$upgrade['amount'],2);
        }

        # 升降级手动金额(是操作订单金额)
        $manualAmount = $OrderItemModel->whereIn('order_id',$manualUpgradeOrderIds)
            ->whereIn('type',['manual','addon_idcsmart_voucher'])
            ->sum('amount');
        $baseAmount = bcadd($baseAmount,$manualAmount,2);

        # 原订单手动金额(当原订单超过1个产品时,手动金额>0的不管)
        $count = $OrderItemModel->where('order_id',$orderItem['order_id'] ?? 0)->where('type','host')->count();
        if ($count>1){
            $manualAmount2 = $OrderItemModel->where('order_id',$orderItem['order_id'] ?? 0)
                ->whereIn('type',['manual','addon_idcsmart_voucher'])
                ->where('amount','<',0)
                ->sum('amount');
        }else{
            $manualAmount2 = $OrderItemModel->where('order_id',$orderItem['order_id'] ?? 0)
                ->whereIn('type',['manual','addon_idcsmart_voucher'])
                ->sum('amount');
        }
        $baseAmount = bcadd($baseAmount,$manualAmount2,2);

        # 判断是否在全额退款期内
        $fullRefundDays = $refundProduct['full_refund_days'] ?? 0;
        if ($fullRefundDays > 0) {
            // 计算已购买天数（向下取整，对用户有利）
            $purchasedDays = floor((time() - $host['active_time']) / 86400);
            
            // 如果在全额退款期内，直接返回全额
            if ($purchasedDays <= $fullRefundDays) {
                return [max($baseAmount, 0), $baseAmount];
            }
        }

        if ($refundProduct->rule == 'Day'){ # 按天退款
            $day = floor($diffTime/(24*3600));

            $totalDay = $billingCycleTime / (24*3600);
            if ($totalDay>0){
                $amount = bcmul($baseAmount,bcdiv($day,$totalDay,20),2);
            }else{
                $amount = $baseAmount;
            }

        }elseif ($refundProduct->rule == 'Month'){ # 按月(30天)退款
            $month = floor($diffTime/(24*3600*30));

            $totalMonth = $billingCycleTime / (24*3600*30);

            if ($totalMonth>0){
                $amount = bcmul($baseAmount,bcdiv($month,$totalMonth,20),2);
            }else{
                $amount = $baseAmount;
            }

        }else{ # 按比例退款
            $amount = bcmul($baseAmount,$refundProduct->ratio_value/100,2);
        }

        return [max($amount,0),$baseAmount];//$amount>0?$amount:0;
    }

    # 退款停用按钮模板钩子
    public function templateAfterServicedetailSuspended($param)
    {
        $hostId = intval($param['host_id']??0);
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);

        if (empty($host) || $host['is_delete']){
            return '';
        }

        $IdcsmartRefundModel = new IdcsmartRefundModel();
        $refund = $IdcsmartRefundModel->where('host_id',$hostId)
            ->order('id','desc')
            ->find();
        if (!empty($refund)){
            if ($refund->status == 'Reject'){ # 驳回显示:停用+驳回原因
                return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>" . lang_plugins('refund_reject_reason') . ":{$refund->reject_reason})";
            }elseif ($refund->status == 'Cancelled'){ # 取消显示:停用按钮
                # 开通中/已开通 才显示“停用”按钮
                if (in_array($host->status,['Pending','Active'])){
                    return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>";
                }
            }else{
                if ($refund->status == 'Pending'){
                    $status = lang_plugins('refund_pending');
                }elseif ($refund->status == 'Suspending'){
                    $status = lang_plugins('refund_suspending');
                }elseif ($refund->status == 'Suspend'){
                    $status = lang_plugins('refund_suspend_1');
                }elseif ($refund->status == 'Suspended'){
                    $status = lang_plugins('refund_suspended');
                }elseif ($refund->status == 'Refund'){ # 已退款
                    $status = lang_plugins('refund_refund');
                }else{
                    $status = '';
                }
                $html = "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". $status ."</a>";

                if ($refund->status == 'Suspending'){ # 待停用状态 + 取消停用按钮
                    $html .= "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_cancelled_button') ."</a>";
                }
                return $html;

            }
        }else{
            # 开通中/已开通 才显示“停用”按钮
            if (in_array($host->status,['Pending','Active'])){
                return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>";
            }
        }

        return '';
    }

    # 实现每日一次定时任务钩子
    public function dailyCron($immediate=false)
    {
        if ($immediate){
            $where = function (Query $query){
                $query->where('r.status','Suspending')
                    ->where('r.type','Immediate');
            };
            $refunds = $this->alias('r')
                ->field('r.id,r.host_id,r.client_id,r.amount,r.refund_record_id,h.order_id')
                ->leftJoin('host h','h.id=r.host_id')
                #->where('h.id','>',0)
                ->where($where)
                ->select()
                ->toArray();
        }else{
            $where = function (Query $query){
                $query->whereIn('r.status',['Suspending','Suspend'])
                    ->where('r.type','Immediate');
            };
            $whereOr = function (Query $query){
                $query->whereIn('r.status',['Suspending','Suspend'])
                    ->where('r.type','Expire')
                    ->where('h.due_time','<=',time());
            };
            $refunds = $this->alias('r')
                ->field('r.id,r.host_id,r.client_id,r.amount,r.refund_record_id,h.order_id')
                ->leftJoin('host h','h.id=r.host_id')
                #->where('h.id','>',0)
                ->where($where)
                ->whereOr($whereOr)
                ->select()
                ->toArray();
        }

        $ModuleLogic = new ModuleLogic();
        $HostModel = new HostModel();
        $ProductModel = new ProductModel();
        $OrderItemModel = new OrderItemModel();
        $OrderModel = new OrderModel();

        $refundHostId = [];
        foreach ($refunds as $refund){
            $host = $HostModel->find($refund['host_id']);
            if (empty($host) || $host['is_delete']){ # 考虑产品被删除的情况
                $status = 'Suspended';
            }else{
                $hostStatus = 'Suspended';
                $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
                if($upstreamProduct){
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->terminateAccount($host);
                    $hostStatus = 'Deleted';
                }else{
                    // wyh 20240913 修改，退款后动作
                    $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
                    $refundProduct = $IdcsmartRefundProductModel->where('product_id',$host['product_id'])->find();
                    if (!empty($refundProduct)){
                        if ($refundProduct['action']=='Suspend'){
                            if ($host['status']=='Suspended'){
                                $result = $HostModel->suspendAccount($host);
                            }else{
                                $result = $ModuleLogic->suspendAccount($host);
                            }

                        }else{
                            $result = $ModuleLogic->terminateAccount($host);
                            $hostStatus = 'Deleted';
                        }
                    }else{
                        $result = $ModuleLogic->suspendAccount($host);
                    }
                }

                if ($result['status'] == 200){
                    # API开通(代理端请求)是否允许退款判断
                    $isApi = request()->is_api ?? false;
                    if ($isApi && !empty($refundProduct) && isset($refundProduct['api_refund_allow']) && $refundProduct['api_refund_allow'] == 0){
                        $ProductModel = new ProductModel();
                        $product = $ProductModel->find($host['product_id']);
                        $apiName = request()->api_name ?? '';
                        active_log(lang_plugins('refund_api_refund_reject_log', [
                            '{api_name}' => $apiName,
                            '{product}' => 'product#'.$host['product_id'].'#'.($product['name']??'').'#',
                            '{host}' => 'host#'.$refund['host_id'].'#'.($host['name']??'').'#'
                        ]), 'host', $refund['host_id']);
                        $amount = $refund['amount'] = -1;
                    }
                    if ($refund['amount'] == -1){ # 不需要退款
                        $status = 'Suspended'; # 已停用
                    }elseif ($refund['amount'] == 0){
                        $status = 'Refund'; # 已退款
                    }else{
                        # 退款金额大于0时,退款至用户余额
                        update_credit([
                            'type' => 'Refund',
                            'amount' => $refund['amount'],
                            'notes' => lang_plugins('refund_to_client_credit').'#'.$refund['order_id'],
                            'client_id' => $refund['client_id'],
                            'order_id' => $refund['order_id'],
                            'host_id' => $refund['host_id'],
                        ]);
                        $status = 'Refund'; # 已退款

                        $product = $ProductModel->find($host['product_id']);

                        if (!$isApi){
                            system_notice([
                                'name' => 'client_refund_success',
                                'email_description' => lang_plugins('client_refund_success_send_mail'),
                                'sms_description' => lang_plugins('client_refund_success_send_sms'),
                                'task_data' => [
                                    'client_id'=>$refund['client_id'],//客户ID
                                    'host_id' => $refund['host_id'],
                                    'template_param'=>[
                                        'product_name' => $product['name'].'-'.$host['name'],//产品名称
                                    ],
                                ],
                            ]);
                        }
                    }

                    $host->save([
                        'status' => $hostStatus,
                        'update_time' => time()
                    ]);



                }else{ # 模块删除未成功
                    $status = 'Suspend'; # 停用中
                    active_log(lang_plugins('refund_module_delete_fail_log',['{host}' => 'host#'.$refund['host_id'].'#'.$host['name'].'#','{reason}'=>$result['msg']??'未知错误']),'host',$refund['host_id']);
                }
            }
            # 更新停用申请
            $data = [
                'status' => $status,
                'update_time' => time()
            ];
            if (isset($amount)){
                $data['amount'] = $amount;
            }
            $this->update($data,['id'=>$refund['id']]);

            // 同步修改退款记录
            if(!empty($refund['refund_record_id'])){
                RefundRecordModel::where('id', $refund['refund_record_id'])->update([
                    'status'        => $status == 'Refund' ? 'Refunded' : $status,
                    'refund_time'   => $status == 'Refund' ? time() : 0,
                ]);
            }

            // 计算关联订单退款金额
            if($status == 'Refund'){
                $orders = $OrderModel
                    ->alias('o')
                    ->field('o.*')
                    ->join('order_item oi', 'oi.order_id=o.id')
                    ->whereIn('oi.host_id', $host['id'])
                    ->whereIn('o.status', ['Paid','Refunded'])
                    ->group('o.id')
                    ->select();
                foreach($orders as $order){
                    $orderRefund = $OrderModel->orderRefundIndex([
                        'order'                 => $order,
                        'addon_refund_host_id'  => $host['id'],
                    ]);

                    // 修改退款总额
                    $order->save([
                        'credit'                    => $orderRefund['leave_credit'],
                        'refund_gateway_to_credit'  => $orderRefund['gateway_to_credit'],
                        'status'                    => 'Refunded',
                        'refund_amount'             => bcadd($orderRefund['refund_credit'], $orderRefund['refund_gateway'], 2),
                        'update_time'               => time(),
                    ]);
                }
            }

            upstream_sync_host($refund['host_id'],'refund');

            hook('after_host_refund',['host_id'=>$refund['host_id'],'amount'=>$refund['amount']]);
        }

        return true;
    }

    # 实现产品退款判断钩子
    public function hostRefund($param)
    {
        $id = $param['id']??0;

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $refund = $IdcsmartRefundModel->where('host_id',$id)->where('amount','>',0)->find();

        # 订单有退款
        if (!empty($refund)){
            $amount = $refund['amount'];
            return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>$amount]];
        }else{ # 订单无退款
            return ['status'=>400,'msg'=>lang_plugins('fail_message')];
        }
    }

    /**
     * 时间 2022-08-11
     * @title 获取待审核金额
     * @desc 获取待审核金额
     * @author wyh
     * @version v1
     * @url /console/v1/refund/pending/amount
     * @method get
     * @return float amount - 退款待审核金额
     */
    public function pendingAmount()
    {
        $amount = $this->where('client_id',get_client_id())
            ->where('status','Pending')
            ->where('amount','>',0)
            ->sum('amount');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>bcsub($amount,0,2)]];
    }

    /**
     * 时间 2022-08-11
     * @title 获取产品停用信息
     * @desc 获取产品停用信息
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @return object refund - 退款信息
     * @return int refund.id - 退款ID
     * @return float refund.amount - 退款金额:-1表示不需要退款
     * @return string refund.suspend_reason - 停用原因
     * @return string refund.type - 类型:Expire到期退款,Immediate立即退款
     * @return string refund.status - 状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @return string refund.reject_reason - 驳回原因
     * @return int refund.create_time - 申请时间
     */
    public function hostRefundInfo($param)
    {
        $id = $param['id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->where('id',$id)->where('client_id',get_client_id())->find();
        if (empty($host) || $host['is_delete']){
            return ['status'=>400,'msg'=>lang_plugins('refund_host_is_not_exist')];
        }

        $refund = $this->field('id,amount,suspend_reason,type,status,reject_reason,create_time')
            ->where('host_id',$id)
            ->order('id','desc')
            ->find();

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['refund'=>$refund]];
    }

    /**
     * 时间 2022-08-23
     * @title 获取客户退款金额
     * @desc 获取客户退款金额
     * @author wyh
     * @version v1
     * @param int id - 客户ID required
     * @return float amount - 退款金额
     */
    public function clientRefundAmount($param)
    {
        $clientId = $param['id']??0;

        $ClientModel = new ClientModel();
        $client = $ClientModel->find($clientId);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang_plugins('client_is_not_exist')];
        }

        $amount = $this->where('client_id',$clientId)
            ->where('status','Refund')
            ->where('amount','>',0)
            ->sum('amount');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>bcsub($amount,0,2)]];

    }

    /**
     * 时间 2025-04-07
     * @title 按需停用
     * @desc  按需停用
     * @author hh
     * @version v1
     * @param  int id - 产品ID required
     * @param  mixed suspend_reason - 停用原因,产品可以自定义原因时,输入框,传字符串;产品不可自定义原因时,传停用原因ID数组
     * @param  object customfield - 自定义字段
     * @return int status - 状态:200=成功,400=失败
     * @return string msg - 错误信息
     */
    public function onDemandTerminate($param): array
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);
        if(empty($host) || $host['is_delete'] || $host['client_id'] != get_client_id()){
            return ['status' => 400, 'msg' => lang_plugins('host_is_not_exist')];
        }
        if($host['billing_cycle'] != 'on_demand'){
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        $IdcsmartRefund = new IdcsmartRefund();
        $config = $IdcsmartRefund->getConfig();

        # 停用原因
        if (isset($config['reason_custom']) && $config['reason_custom']==1){
            $suspendReason = $param['suspend_reason'];
        }else{
            $suspendReason = '';
            $suspendReasons = $param['suspend_reason'];
            $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
            foreach ($suspendReasons as $item){
                $refundReason = $IdcsmartRefundReasonModel->find($item);
                $suspendReason .= $refundReason['content'] . "\n";
            }
            $suspendReason = rtrim($suspendReason,"\n");
        }
        if(!in_array($host['status'], ['Active','Grace'])){
            return ['status'=>400, 'msg'=>lang_plugins('refund_on_demand_host_status_must_be_active_or_grace') ];
        }
        $OrderModel = new OrderModel();
		if($OrderModel->haveUnpaidChangeBillingCycleOrder($host['id'])){
            return ['status'=>400, 'msg'=>lang('client_have_unpaid_on_demand_to_recurring_prepayment_order_cannot_do_this') ];
        }
        $endTime = time();
        $res = $host->terminateAccount($host['id']);
        if($res['status'] == 200){
            $OnDemandPaymentQueueModel = new OnDemandPaymentQueueModel();
            $OnDemandPaymentQueueModel->createOnDemandPaymentQueue([
                'host'          => $host,
                'type'          => 'terminate',
                'start_time'    => $host['start_billing_time'],
                'end_time'      => $endTime,
            ]);

            $host->save([
                'start_billing_time'    => $endTime,
            ]);

            $data = [
                'client_id'     => $host['client_id'],
                'host_id'       => $host['id'],
                'amount'        => 0.00,
                'suspend_reason'=> $suspendReason ?: '',
                'type'          => 'Immediate',
                'create_time'   => time(),
                'status'        => 'Suspended',
            ];
            $refund = $this->create($data);

            active_log(lang_plugins('refund_refund_on_demand_host', ['{client}'=>'client#'.$host['client_id'].'#'. request()->client_name .'#','{host}'=>'host#'.$host['id'].'#'.$host['name'].'#']), 'addon_idcsmart_refund', $refund->id);

            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
            ];
        }else{
            $result = [
                'status' => 400,
                'msg'    => $res['msg'],
            ];
        }
        return $result;
    }


}

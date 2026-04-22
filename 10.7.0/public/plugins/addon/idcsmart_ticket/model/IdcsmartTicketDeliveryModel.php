<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\common\model\HostModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamHostModel;
use think\Model;

/*
 * @author wyh
 * @time 2024-06-213
 */
class IdcsmartTicketDeliveryModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_delivery';

    # 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'product_id'                => 'int',
        'ticket_type_id'            => 'int',
        'blocked_words'             => 'string',
        'create_time'               => 'int',
        'update_time'               => 'int',
    ];

    public $isAdmin = false;

    /**
     * 时间 2024-06-13
     * @title 工单传递规则列表
     * @desc 工单传递规则列表
     * @author wyh
     * @version v1
     * @return array list - 工单传递规则列表
     * @return int list[].id - 规则ID
     * @return int list[].product_name - 商品名称
     * @return int list[].type_name - 类型名称
     * @return int list[].blocked_words - 屏蔽词，逗号分隔
     */
    public function ticketDeliveryList($param)
    {
        $list = $this->alias('td')
            ->field('td.id,p.name product_name,tt.name type_name,td.blocked_words,td.product_id,td.ticket_type_id')
            ->leftJoin('product p','td.product_id=p.id')
            ->leftJoin('addon_idcsmart_ticket_type tt','td.ticket_type_id=tt.id')
            ->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $list
            ],
        ];
    }

    /**
     * 时间 2024-06-13
     * @title 工单传递规则详情
     * @desc 工单传递规则详情
     * @author wyh
     * @version v1
     * @param int id - 工单传递规则ID required
     * @return object ticket_delivery - 工单传递规则详情
     * @return int ticket_delivery.product_id - 商品ID
     * @return string ticket_delivery.ticket_type_id - 类型ID
     * @return string ticket_delivery.blocked_words - 屏蔽词，逗号分隔
     */
    public function indexTicketDelivery($id)
    {
        $ticketDelivery = $this->field('id,product_id,ticket_type_id,blocked_words')->find($id);

        $data = [
            'ticket_delivery' => $ticketDelivery
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2024-06-13
     * @title 创建工单传递规则
     * @desc 创建工单传递规则
     * @author wyh
     * @version v1
     * @param int ticket_type_id - 工单类型ID required
     * @param array product_ids - 商品ID数组 required
     * @param string blocked_words - 屏蔽词
     */
    public function createTicketDelivery($param)
    {
        $this->startTrans();

        try{
            $insertAll = [];

            foreach ($param['product_ids'] as $productId){
                $insertAll[] = [
                    'product_id' => $productId,
                    'ticket_type_id' => $param['ticket_type_id'],
                    'blocked_words' => $param['blocked_words'],
                    'create_time' => time(),
                ];
            }

            $this->insertAll($insertAll);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-06-13
     * @title 编辑工单传递规则
     * @desc 编辑工单传递规则
     * @author wyh
     * @version v1
     * @param int id - 工单传递规则ID required
     * @param int product_id - 商品ID required
     * @param string blocked_words - 屏蔽词
     */
    public function updateTicketDelivery($param)
    {
        $this->startTrans();

        try{
            $this->update([
                'product_id' => $param['product_id'],
                'ticket_type_id' => $param['ticket_type_id'],
                'blocked_words' => $param['blocked_words'],
                'update_time' => time(),
            ],['id' => $param['id']]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-06-13
     * @title 删除工单传递规则
     * @desc 删除工单传递规则
     * @author wyh
     * @version v1
     * @param int id - 工单传递规则ID required
     */
    public function deleteTicketDelivery($id)
    {
        $this->startTrans();

        try{
            $this->where('id',$id)->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-06-13
     * @title 工单传递
     * @desc 工单传递
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID required
     * @param int ticket_id - 工单ID required
     */
    public function delivery($param)
    {
        if (!isset($param['host_id']) || empty($param['host_id'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        if (!isset($param['ticket_id']) || empty($param['ticket_id'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['host_id']);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_host_is_not_exist')];
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $ticket = $IdcsmartTicketModel->find($param['ticket_id']);
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$param['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['ticket_id'])->find();
        if (!empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_deliveried')];
        }

        // 规则只验证前台
        if (!$this->isAdmin){
            $delivery = $this->where('product_id',$host['product_id'])
                ->where('ticket_type_id',$ticket['ticket_type_id'])
                ->find();
            if (empty($delivery)){
                return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_rule_error')];
            }

            if (!empty($delivery['blocked_words'])){
                $blockedWords = explode(',',$delivery['blocked_words']);
                foreach ($blockedWords as $blockedWord){
                    if (strpos($ticket['title'],$blockedWord)!==false){
                        return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_rule_blocked_words_error')];
                    }
                }
            }
        }

        $config = IdcsmartTicketLogic::getDefaultConfig();

        // 1、有文件，先传文件
        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        $attachmentsUpstream = [];
        if (!empty($ticket['attachment'])){
            $attachments = explode(',',$ticket['attachment']);
            foreach ($attachments as $attachment){
                $result = IdcsmartTicketLogic::idcsmartApiCurlUploadFile($upstreamHost['supplier_id'],$config['ticket_upload'],$attachment);
                if ($result['status']!=200){
                    if ($this->isAdmin){
                        active_log(lang_plugins('log_ticket_delivery_upload_fail_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
                    }else{
                        active_log(lang_plugins('log_ticket_delivery_upload_fail',['{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);

                    }
                    return $result;
                }
                if (isset($result['data']['save_name']) && !empty($result['data']['save_name'])){
                    $attachmentsUpstream[] = $result['data']['save_name'];
                }
            }
        }

        // 2、取附件，传递工单
        $token = generate_signature(['title'=>$ticket['title'],'content'=>$ticket['content'],'downstream_ticket_id'=>$ticket['id']],AUTHCODE,'idcsmart_ticket')['signature'];
        $result = idcsmart_api_curl($upstreamHost['supplier_id'],'console/v1/ticket',[
            'is_downstream' => 1,
            'downstream_delivery' => $config['downstream_delivery']??0,
            'downstream_source' => 'IdcsmartTicket',
            'downstream_url' => request()->domain(),
            'downstream_token' => $token,
            'downstream_ticket_id' => $ticket['id'],
            'title' => $ticket['title'],
            'content' => htmlspecialchars_decode($ticket['content']),
            'host_ids' => [$upstreamHost['upstream_host_id']], // 传递上游产品ID
            'ticket_type_id' => 0,
            'attachment' => $attachmentsUpstream,
        ],30,'POST','json');

        // 3、记录日志
        if ($result['status']==200){
            // 更新本地token，与传递给上游的一致
            $ticket->save([
                'token' => $token
            ]);
            if ($this->isAdmin){
                active_log(lang_plugins('log_ticket_delivery_success_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }else{
                active_log(lang_plugins('log_ticket_delivery_success',['{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }

        }else{
            if ($this->isAdmin){
                active_log(lang_plugins('log_ticket_delivery_fail_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }else{
                active_log(lang_plugins('log_ticket_delivery_fail',['{host_id}'=>$param['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }
        }

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 工单回复传递
     * @desc 工单回复传递
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID required
     * @param int ticket_reply_id - 工单回复ID required
     */
    public function deliveryReply($param)
    {
        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['ticket_id'])->find();
        if (empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_not_deliveried_not_reply')];
        }
        if ($ticketUpstream['delivery_status']==0){
            if ($this->isAdmin){
                active_log(lang_plugins('log_ticket_reply_delivery_status_is_terminate_admin',['{admin}'=>request()->admin_name,'{ticket_reply_id}'=>$param['ticket_reply_id'],'{host_id}'=>$ticketUpstream['host_id'],'{reason}'=>lang_plugins('ticket_delivery_status_is_terminate')]),'addon_idcsmart_ticket',$param['ticket_id']);
            }else{
                active_log(lang_plugins('log_ticket_reply_delivery_status_is_terminate',['{ticket_reply_id}'=>$param['ticket_reply_id'],'{host_id}'=>$ticketUpstream['host_id'],'{reason}'=>lang_plugins('ticket_delivery_status_is_terminate')]),'addon_idcsmart_ticket',$param['ticket_id']);
            }
            return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_status_is_terminate')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
        $ticketReply = $IdcsmartTicketReplyModel->find($param['ticket_reply_id']??0);

        // 1、有文件，先传文件
        $config = IdcsmartTicketLogic::getDefaultConfig();
        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        $attachmentsUpstream = [];
        if (!empty($ticketReply['attachment'])){
            $attachments = explode(',',$ticketReply['attachment']);
            foreach ($attachments as $attachment){
                $result = IdcsmartTicketLogic::idcsmartApiCurlUploadFile($upstreamHost['supplier_id'],$config['ticket_upload'],$attachment);
                if ($result['status']!=200){
                    if ($this->isAdmin){
                        active_log(lang_plugins('log_ticket_reply_delivery_upload_fail_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
                    }else{
                        active_log(lang_plugins('log_ticket_reply_delivery_upload_fail',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
                    }
                    return $result;
                }
                if (isset($result['data']['save_name']) && !empty($result['data']['save_name'])){
                    $attachmentsUpstream[] = $result['data']['save_name'];
                }
            }
        }
        // 2、取附件，传递工单回复
        $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/reply",[
            'is_downstream' => 1,
//            'downstream_delivery' => $config['downstream_delivery'], // 是否显示【下游传递】
//            'downstream_source' => 'IdcsmartTicket',
//            'downstream_url' => request()->domain(),
//            'downstream_token' => generate_signature(['content'=>$ticketReply['content']],AUTHCODE,'idcsmart_ticket')['signature'],
            'downstream_ticket_reply_id' => $ticketReply['id'],
            'content' => htmlspecialchars_decode($ticketReply['content']),
            'attachment' => $attachmentsUpstream,
        ],30,'POST','json');
        // 3、记录日志
        if ($result['status']==200){
            if ($this->isAdmin){
                active_log(lang_plugins('log_ticket_reply_delivery_success_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }else{
                active_log(lang_plugins('log_ticket_reply_delivery_success',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }
        }else{
            if ($this->isAdmin){
                active_log(lang_plugins('log_ticket_reply_delivery_fail_admin',['{admin}'=>request()->admin_name,'{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }else{
                active_log(lang_plugins('log_ticket_reply_delivery_fail',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{ticket_reply_id}'=>$param['ticket_reply_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
            }
        }

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 催单传递
     * @desc 催单传递
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID required
     */
    public function deliveryUrge($param)
    {
        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['ticket_id'])->find();
        if (empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_not_deliveried_not_reply')];
        }
        if ($ticketUpstream['delivery_status']==0){
            return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_status_is_terminate')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/urge",[
            'is_downstream' => 1,
        ],30,'PUT','json');
        // 记录日志
        if ($result['status']==200){
            active_log(lang_plugins('log_ticket_urge_delivery_success',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
        }else{
            active_log(lang_plugins('log_ticket_urge_delivery_fail',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
        }

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 关闭工单传递
     * @desc 关闭工单传递
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID required
     */
    public function deliveryClose($param)
    {
        $IdcsmartTicketUpstreamModel = new IdcsmartTicketUpstreamModel();
        $ticketUpstream = $IdcsmartTicketUpstreamModel->where('ticket_id',$param['ticket_id'])->find();
        if (empty($ticketUpstream)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_has_not_deliveried_not_reply')];
        }
        if ($ticketUpstream['delivery_status']==0){
            return ['status'=>400,'msg'=>lang_plugins('ticket_delivery_status_is_terminate')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id',$ticketUpstream['host_id'])->find();
        if (empty($upstreamHost)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_upstream_host_is_not_exist')];
        }

        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($upstreamHost['supplier_id']);
        $result = idcsmart_api_curl($upstreamHost['supplier_id'],"console/v1/ticket/{$ticketUpstream['upstream_ticket_id']}/close",[
            'is_downstream' => 1,
        ],30,'PUT','json');
        // 记录日志
        if ($result['status']==200){
            active_log(lang_plugins('log_ticket_close_delivery_success',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name']]), 'addon_idcsmart_ticket', $param['ticket_id']);
        }else{
            active_log(lang_plugins('log_ticket_close_delivery_fail',['{host_id}'=>$ticketUpstream['host_id'],'{ticket_id}'=>$param['ticket_id'],'{upstream}'=>$supplier['name'],'{reason}'=>$result['msg']]), 'addon_idcsmart_ticket', $param['ticket_id']);
        }

        return $result;
    }

}

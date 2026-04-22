<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminModel;
use app\common\model\ClientModel;
use think\Exception;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketReplyModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_reply';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_id'                        => 'int',
        'type'                             => 'string',
        'rel_id'                           => 'int',
        'content'                          => 'string',
        'attachment'                       => 'string',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'is_downstream'                    => 'int',
        'downstream_ticket_reply_id'       => 'int',
        'upstream_ticket_reply_id'         => 'int',
        'quote_reply_id'                   => 'int',
    ];

    /**
     * 时间 2022-09-23
     * @title 修改工单回复
     * @desc 修改工单回复
     * @author wyh
     * @version v1
     * @param int id - 工单回复ID required
     * @param int content - 内容 required
     */
    public function ticketReplyUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketReply = $this->where('id',$param['id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }

            if (!IdcsmartTicketLogic::checkUpstreamTicket($ticketReply['ticket_id'])){
                throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
            }

            $ticketReply->save([
                'content'=>$param['content']??'',
                'update_time'=>time()
            ]);

            # 记录日志
            $ticketId = $ticketReply['ticket_id'];
            if ($ticketReply['type']=='Admin'){
                $AdminModel = new AdminModel();
                $admin = $AdminModel->find($ticketReply['rel_id']);
                $name = $admin['name'];
            }else{
                $ClientModel = new ClientModel();
                $client = $ClientModel->find($ticketReply['rel_id']);
                $name = $client['username'];
            }

            active_log(lang_plugins('ticket_log_admin_update_ticket_reply', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{name}'=>$name]), 'addon_idcsmart_ticket', $ticketId);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $ticket = $IdcsmartTicketModel->find($ticketReply['ticket_id']);

        IdcsmartTicketLogic::pushTicketReply($ticket,$ticketReply,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 删除工单回复
     * @desc 删除工单回复
     * @author wyh
     * @version v1
     * @param int id - 工单回复ID required
     */
    public function ticketReplyDelete($param)
    {
        $this->startTrans();

        try{
            $ticketReply = $this->where('id',$param['id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }
            if (!IdcsmartTicketLogic::checkUpstreamTicket($ticketReply['ticket_id'])){
                throw new \Exception(lang_plugins('ticket_upstream_cannot_operate'));
            }
            $ticketReply->delete();
            # 记录日志
            $ticketId = $ticketReply['ticket_id'];
            if ($ticketReply['type']=='Admin'){
                $AdminModel = new AdminModel();
                $admin = $AdminModel->find($ticketReply['rel_id']);
                $name = $admin['name'];
            }else{
                $ClientModel = new ClientModel();
                $client = $ClientModel->find($ticketReply['rel_id']);
                $name = $client['username'];
            }
            active_log(lang_plugins('ticket_log_admin_delete_ticket_reply', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{name}'=>$name]), 'addon_idcsmart_ticket', $ticketId);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $ticket = $IdcsmartTicketModel->find($ticketReply['ticket_id']);

        IdcsmartTicketLogic::pushTicketReplyDelete($ticket,$ticketReply,$param);

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * @时间 2024-12-09
     * @title 获取工单管理员最后回复时间
     * @desc  获取工单管理员最后回复时间,没回复返回0
     * @author hh
     * @version v1
     * @param   int $id - 工单ID
     * @return  int
     */
    public function getAdminLastReplyTime($id)
    {
        $lastReplyTime = $this
                        ->where('ticket_id', $id)
                        ->where('type', 'Admin')
                        ->order('id', 'desc')
                        ->value('create_time');
                        
        return $lastReplyTime ?? 0;
    }

    /**
     * 时间 2025-01-XX
     * @title 获取引用回复信息
     * @desc 获取引用回复的详细信息
     * @author system
     * @version v1
     * @param int $quoteReplyId - 引用的回复ID
     * @return array|null 引用回复信息，不存在返回null
     */
    public function getQuoteReplyInfo($quoteReplyId)
    {
        if (empty($quoteReplyId)) {
            return null;
        }

        $quoteReply = $this->alias('tr')
            ->field('tr.id, LEFT(tr.content, 100) as content, tr.type, tr.rel_id, tr.create_time')
            ->where('tr.id', $quoteReplyId)
            ->find();

        if (empty($quoteReply)) {
            return [
                'id' => $quoteReplyId,
                'content' => '',
                'type' => '',
                'sender_name' => '',
                'create_time' => 0,
                'is_deleted' => true
            ];
        }

        // 根据type获取发送者名称
        $senderName = '';
        if ($quoteReply['type'] == 'Client') {
            $ClientModel = new ClientModel();
            $client = $ClientModel->where('id', $quoteReply['rel_id'])->find();
            $senderName = $client['username'] ?? '';
        } else if ($quoteReply['type'] == 'Admin') {
            $AdminModel = new AdminModel();
            $admin = $AdminModel->where('id', $quoteReply['rel_id'])->find();
            $senderName = $admin['name'] ?? '';
        }

        return [
            'id' => $quoteReply['id'],
            'content' => htmlspecialchars_decode($quoteReply['content']),
            'type' => $quoteReply['type'],
            'sender_name' => $senderName,
            'create_time' => $quoteReply['create_time'],
            'is_deleted' => false
        ];
    }

    /**
     * 时间 2025-01-XX
     * @title 获取工单回复列表（包含引用信息）
     * @desc 获取工单的所有回复，并附带引用回复信息
     * @author system
     * @version v1
     * @param int $ticketId - 工单ID
     * @return array 回复列表
     */
    public function getReplyListWithQuote($ticketId)
    {
        $replies = $this->alias('tr')
            ->field('tr.id, tr.ticket_id, tr.type, tr.rel_id, tr.content, tr.attachment, tr.create_time, tr.quote_reply_id')
            ->where('tr.ticket_id', $ticketId)
            ->order('tr.create_time', 'asc')
            ->select()
            ->toArray();

        // 处理每条回复的引用信息
        foreach ($replies as &$reply) {
            $reply['quote_info'] = null;
            if (!empty($reply['quote_reply_id'])) {
                $reply['quote_info'] = $this->getQuoteReplyInfo($reply['quote_reply_id']);
            }
        }

        return $replies;
    }

}

<?php
namespace addon\idcsmart_ticket\controller\clientarea;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\model\IdcsmartTicketStatusModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use addon\idcsmart_ticket\validate\TicketValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 工单(会员中心)
 * @desc 工单(会员中心)
 * @use addon\idcsmart_ticket\controller\clientarea\TicketController
 */
class TicketController extends PluginBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketValidate();
    }

    /**
     * 时间 2022-10-21
     * @title 工单状态列表
     * @desc 工单状态列表
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/status
     * @method GET
     * @return array list - desc:工单状态列表
     * @return int list[].id - desc:ID
     * @return string list[].name - desc:工单状态
     * @return string list[].color - desc:状态颜色
     * @return int list[].status - desc:完结状态 1完结 0未完结
     * @return int list[].default - desc:是否默认状态 0否 1是 默认状态无法修改删除
     */
    public function ticketStatusList()
    {
        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusList();

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 工单列表
     * @desc 工单列表
     * @author wyh
     * @version v1
     * @url /console/v1/ticket
     * @method GET
     * @param string keywords - desc:关键字 validate:optional
     * @param int status - desc:状态搜索 通过/console/v1/ticket/status获取 validate:optional
     * @param int ticket_type_id - desc:工单类型搜索 通过/console/v1/ticket/type获取 validate:optional
     * @param int client_id - desc:客户ID validate:optional
     * @param int admin_id - desc:管理员ID validate:optional
     * @param int host_id - desc:产品ID validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @return array list - desc:工单列表
     * @return int list[].id - desc:ID
     * @return string list[].ticket_num - desc:工单号
     * @return string list[].title - desc:标题
     * @return string list[].name - desc:类型
     * @return int list[].post_time - desc:提交时间
     * @return int list[].last_reply_time - desc:最近回复时间
     * @return string list[].status - desc:状态
     * @return string list[].color - desc:状态颜色
     * @return string list[].last_urge_time - desc:上次催单时间戳 0代表未催单
     * @return int count - desc:工单总数
     */
    public function ticketList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->ticketList($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 工单统计
     * @desc 工单统计
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/statistic
     * @method GET
     * @return int 1 - desc:待接单数量
     * @return int 2 - desc:待回复数量
     * @return int 3 - desc:已回复数量
     * @return int 5 - desc:处理中数量
     */
    public function statistic()
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->statisticTicket();

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 查看工单
     * @desc 查看工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id
     * @method GET
     * @param int id - desc:工单ID validate:required
     * @return object ticket - desc:工单详情
     * @return int ticket.client_id - desc:用户ID
     * @return int ticket.id - desc:工单ID
     * @return string ticket.title - desc:工单标题
     * @return string ticket.content - desc:内容
     * @return int ticket.ticket_type_id - desc:类型ID
     * @return string ticket.status - desc:状态 直接显示 结合color使用
     * @return string ticket.color - desc:状态颜色
     * @return int ticket.create_time - desc:创建时间
     * @return array ticket.attachment - desc:工单附件数组 附件以^分割 取最后一个値获取文件原名
     * @return int ticket.last_reply_time - desc:工单最后回复时间
     * @return string ticket.username - desc:用户名
     * @return array ticket.host_ids - desc:关联产品ID数组
     * @return int ticket.can_operate - desc:是否可操作
     * @return array ticket.replies - desc:沟通记录数组
     * @return int ticket.replies[].id - desc:回复ID
     * @return string ticket.replies[].content - desc:内容
     * @return array ticket.replies[].attachment - desc:附件访问地址数组
     * @return int ticket.replies[].create_time - desc:时间
     * @return string ticket.replies[].type - desc:类型 Client用户回复 Admin管理员回复
     * @return string ticket.replies[].client_name - desc:用户名 type为Client时使用
     * @return string ticket.replies[].admin_name - desc:管理员名 type为Admin时使用
     * @return int ticket.replies[].quote_reply_id - desc:引用的回复ID 0表示未引用
     * @return object ticket.replies[].quote_info - desc:引用的回复信息 未引用时为null
     * @return int ticket.replies[].quote_info.id - desc:被引用回复ID
     * @return string ticket.replies[].quote_info.content - desc:被引用内容 截取前100字
     * @return string ticket.replies[].quote_info.type - desc:类型 Client/Admin
     * @return string ticket.replies[].quote_info.sender_name - desc:发送者名称
     * @return int ticket.replies[].quote_info.create_time - desc:发送时间
     * @return int ticket.replies[].quote_info.is_deleted - desc:该回复是否已删除 1是 0否
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->indexTicket(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 创建工单
     * @desc 创建工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket
     * @method POST
     * @param string title - desc:工单标题 validate:required
     * @param int ticket_type_id - desc:工单类型ID 通过/console/v1/ticket/type获取 validate:required
     * @param array host_ids - desc:关联产品ID数组 validate:optional
     * @param string content - desc:问题描述 validate:optional
     * @param array attachment - desc:附件数组 调用console/v1/upload上传文件取save_name validate:optional
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->createTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 回复工单
     * @desc 回复工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/reply
     * @method POST
     * @param int id - desc:工单ID validate:required
     * @param string content - desc:回复内容 不超过3000个字符 validate:required
     * @param array attachment - desc:附件数组 调用console/v1/upload上传文件取save_name validate:optional
     * @param int quote_reply_id - desc:引用的回复ID 默认0表示不引用 validate:optional
     * @return int ticket_reply_id - desc:回复ID
     */
    public function reply()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('reply')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->replyTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 催单
     * @desc 催单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/urge
     * @method PUT
     * @param int id - desc:工单ID validate:required
     */
    public function urge()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->urgeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 关闭工单
     * @desc 关闭工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/close
     * @method PUT
     * @param int id - desc:工单ID validate:required
     */
    public function close()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->closeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-10-24
     * @title 工单部门
     * @desc 工单部门
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/department
     * @method GET
     * @return array list - desc:工单部门列表
     * @return int list[].id - desc:工单部门ID
     * @return string list[].name - desc:工单部门名称
     */
    public function department()
    {
        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->typeDepartment();

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 工单类型列表
     * @desc 工单类型列表
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/type
     * @method GET
     * @return array list - desc:工单类型列表
     * @return int list[].id - desc:工单类型ID
     * @return string list[].name - desc:工单类型名称
     */
    public function type()
    {
        $param = $this->request->param();

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->typeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 工单附件下载
     * @desc 工单附件下载
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/download
     * @method POST
     * @param string name - desc:附件名称 validate:required
     */
    public function download()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        return $IdcsmartTicketModel->download($param);
    }

    /**
     * 时间 2024-01-22
     * @title 工单通知设置
     * @desc 工单通知设置
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/config
     * @method GET
     * @return int ticket_notice_open - desc:是否开启工单通知 1是默认 0否
     * @return string ticket_notice_description - desc:工单通知描述
     */
    public function ticketConfig()
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->ticketConfig();

        return json($result);
    }

    /*   以下接口为API调用  */

    /**
     * 时间 2024-06-18
     * @title 工单处理中
     * @desc 工单处理中
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/processing
     * @method PUT
     * @param int id - desc:工单ID validate:required
     */
    public function processing()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->processing($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 工单终止传递
     * @desc 工单终止传递
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/terminate
     * @method PUT
     * @param int id - desc:工单ID validate:required
     */
    public function terminate()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->terminateTicket($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 更新工单状态
     * @desc 更新工单状态
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/status
     * @method PUT
     * @param int id - desc:工单ID validate:required
     * @param int status - desc:工单状态 validate:required
     */
    public function updateStatus()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->updateStatus($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 更新工单回复
     * @desc 更新工单回复
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/reply
     * @method PUT
     * @param int id - desc:工单ID validate:required
     * @param int ticket_reply_id - desc:工单回复ID validate:required
     * @param string content - desc:工单回复内容 validate:required
     */
    public function updateReply()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->updateReply($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 删除工单回复
     * @desc 删除工单回复
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/reply
     * @method DELETE
     * @param int id - desc:工单ID validate:required
     * @param int ticket_reply_id - desc:工单回复ID validate:required
     */
    public function deleteReply()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->deleteReply($param);

        return json($result);
    }

    /**
     * 时间 2024-06-18
     * @title 创建工单回复
     * @desc 创建工单回复
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/reply
     * @method POST
     * @param int id - desc:工单ID validate:required
     * @param int upstream_ticket_reply_id - desc:上游工单回复ID validate:required
     * @param string content - desc:工单回复内容 validate:required
     * @param array attachment - desc:附件 validate:optional
     */
    public function createReply()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->createReply($param);

        return json($result);
    }
}
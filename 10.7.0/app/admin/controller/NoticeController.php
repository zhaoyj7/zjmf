<?php
namespace app\admin\controller;

use app\admin\model\NoticeModel;

/**
 * @title 消息通知
 * @desc 消息通知
 * @use app\admin\controller\NoticeController
 */
class NoticeController extends AdminBaseController
{
    /**
     * 时间 2024-12-12
     * @title 异步请求，获取官方通知，更新本地通知信息
     * @desc 异步请求，获取官方通知，更新本地通知信息
     * @url /admin/v1/notice/sync
     * @method GET
     * @author wyh
     * @version v1
     */
    public function sync()
    {
        $NoticeModel = new NoticeModel();

        $result = $NoticeModel->sync();

        return json($result);
    }

    /**
     * 时间 2024-12-12
     * @title 通知列表
     * @desc 通知列表
     * @url /admin/v1/notice
     * @method GET
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字搜索 搜索范围:标题 内容 validate:optional
     * @param int read - desc:是否已读 0未读 1已读 validate:optional
     * @param string type - desc:消息类型 idcsmart官方通知 system系统通知 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 默认id validate:optional
     * @param string sort - desc:排序 desc asc validate:optional
     * @return object list - desc:通知列表
     * @return int list[].id - desc:ID
     * @return string list[].title - desc:标题
     * @return string list[].content - desc:内容
     * @return string list[].attachment - desc:附件 逗号分隔
     * @return int list[].accept_time - desc:接收时间
     * @return int list[].read - desc:是否已读 1是 0否
     * @return string list[].type - desc:消息类型 idcsmart官方通知 system系统通知
     * @return int list[].rel_id - desc:关联ID 消息类型是idcsmart时表示官方消息ID
     * @return int count - desc:总数
     * @return int total_count - desc:所有消息未读总数
     */
    public function list()
    {
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $NoticeModel = new NoticeModel();

        $result = $NoticeModel->noticeList($param);

        return json($result);
    }

    /**
     * 时间 2024-12-12
     * @title 通知详情
     * @desc 通知详情
     * @url /admin/v1/notice/:id
     * @method GET
     * @author wyh
     * @version v1
     * @param int id - desc:通知ID validate:required
     * @return object notice - desc:通知详情
     * @return int notice.id - desc:ID
     * @return string notice.title - desc:标题
     * @return string notice.content - desc:内容
     * @return string notice.attachment - desc:附件 逗号分隔
     * @return int notice.accept_time - desc:接收时间
     * @return int notice.read - desc:是否已读 1是 0否
     * @return string notice.type - desc:消息类型 idcsmart官方通知 system系统通知
     * @return int notice.rel_id - desc:关联ID 消息类型是idcsmart时表示官方消息ID
     * @return int count - desc:总数
     * @return object before - desc:上一条
     * @return object next - desc:下一条
     */
    public function detail()
    {
        $param = $this->request->param();

        $NoticeModel = new NoticeModel();

        $result = $NoticeModel->detail($param);

        return json($result);
    }

    /**
     * 时间 2024-12-12
     * @title 标记已读
     * @desc 标记已读
     * @url /admin/v1/notice/mark_read
     * @method POST
     * @author wyh
     * @version v1
     * @param array ids - desc:通知ID数组 validate:optional
     * @param int all - desc:是否全部标记为已读 1是 0否 validate:optional
     */
    public function markRead()
    {
        $param = $this->request->param();

        $NoticeModel = new NoticeModel();

        $result = $NoticeModel->markRead($param);

        return json($result);
    }

    /**
     * 时间 2024-12-12
     * @title 删除通知
     * @desc 删除通知
     * @url /admin/v1/notice
     * @method DELETE
     * @author wyh
     * @version v1
     * @param array ids - desc:通知ID数组 validate:required
     */
    public function delete()
    {
        $param = $this->request->param();

        $NoticeModel = new NoticeModel();

        $result = $NoticeModel->noticeDelete($param);

        return json($result);
    }

}
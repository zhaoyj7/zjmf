<?php
namespace addon\idcsmart_announcement\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementModel;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementTypeModel;

/**
 * @title 公告中心
 * @desc 公告中心
 * @use addon\idcsmart_announcement\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 获取公告分类
     * @desc 获取公告分类
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/type
     * @method GET
     * @return array list - desc:公告分类列表
     * @return int list[].id - desc:公告分类ID
     * @return string list[].name - desc:名称
     * @return int list[].announcement_num - desc:公告数量
     * @return int count - desc:全部公告数量
     */
    public function idcsmartAnnouncementTypeList()
    {
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementTypeModel->idcsmartAnnouncementTypeList('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页公告列表
     * @desc 会员中心首页公告列表
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/index
     * @method GET
     * @param string keywords - desc:关键字 搜索标题 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:公告列表
     * @return int list[].id - desc:公告ID
     * @return string list[].title - desc:标题
     * @return string list[].img - desc:公告缩略图
     * @return string list[].type - desc:类型
     * @return int list[].create_time - desc:创建时间
     */
    public function indexIdcsmartAnnouncementList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        $param['limit'] = 5;

        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementModel->idcsmartAnnouncementList($param, 'index');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 公告列表
     * @desc 公告列表
     * @author theworld
     * @version v1
     * @url /console/v1/announcement
     * @method GET
     * @param int addon_idcsmart_announcement_type_id - desc:分类ID validate:optional
     * @param string keywords - desc:关键字 搜索标题 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:公告列表
     * @return int list[].id - desc:公告ID
     * @return string list[].title - desc:标题
     * @return string list[].img - desc:公告缩略图
     * @return int list[].create_time - desc:创建时间
     */
    public function idcsmartAnnouncementList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementModel->idcsmartAnnouncementList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 公告详情
     * @desc 公告详情
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/:id
     * @method GET
     * @param int id - desc:公告ID validate:required
     * @return object announcement - desc:公告
     * @return int announcement.id - desc:公告ID
     * @return int announcement.addon_idcsmart_announcement_type_id - desc:分类ID
     * @return string announcement.type - desc:分类名
     * @return string announcement.title - desc:标题
     * @return string announcement.content - desc:内容
     * @return string announcement.keywords - desc:关键字
     * @return string announcement.attachment - desc:附件
     * @return int announcement.create_time - desc:创建时间
     * @return int announcement.update_time - desc:更新时间
     * @return object announcement.prev - desc:上一条公告
     * @return string announcement.prev.id - desc:公告ID
     * @return string announcement.prev.title - desc:标题
     * @return object announcement.next - desc:下一条公告
     * @return string announcement.next.id - desc:公告ID
     * @return string announcement.next.title - desc:标题
     */
    public function idcsmartAnnouncementDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告
        $announcement = $IdcsmartAnnouncementModel->idcsmartAnnouncementDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'announcement' => $announcement
            ]
        ];
        return json($result);
    }
}
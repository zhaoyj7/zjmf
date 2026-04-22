<?php
namespace addon\idcsmart_announcement\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementModel;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementTypeModel;
use addon\idcsmart_announcement\validate\IdcsmartAnnouncementValidate;

/**
 * @title 公告中心(后台)
 * @desc 公告中心(后台)
 * @use addon\idcsmart_announcement\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartAnnouncementValidate();
    }

    /**
     * 时间 2022-06-21
     * @title 公告列表
     * @desc 公告列表
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement
     * @method GET
     * @param int addon_idcsmart_announcement_type_id - desc:分类ID validate:optional
     * @param string keywords - desc:关键字,搜索范围:标题 validate:optional
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序 id validate:optional
     * @param string sort - desc:升/降序 asc,desc validate:optional
     * @return array list - desc:公告
     * @return int list[].id - desc:公告ID
     * @return string list[].title - desc:标题
     * @return string list[].img - desc:公告缩略图
     * @return string list[].type - desc:类型
     * @return string list[].admin - desc:提交人
     * @return int list[].create_time - desc:创建时间
     * @return int list[].hidden - desc:0显示1隐藏
     * @return int count - desc:公告总数
     */
    public function idcsmartAnnouncementList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementModel->idcsmartAnnouncementList($param);

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
     * @url /admin/v1/announcement/:id
     * @method GET
     * @param int id - desc:公告ID validate:required
     * @return array announcement - desc:公告
     * @return int announcement.id - desc:公告ID
     * @return int announcement.addon_idcsmart_announcement_type_id - desc:分类ID
     * @return string announcement.type - desc:分类名
     * @return string announcement.title - desc:标题
     * @return string announcement.content - desc:内容
     * @return string announcement.keywords - desc:关键字
     * @return array announcement.attachment - desc:附件
     * @return int announcement.hidden - desc:0:显示1:隐藏
     */
    public function idcsmartAnnouncementDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告
        $announcement = $IdcsmartAnnouncementModel->idcsmartAnnouncementDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'announcement' => $announcement
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 添加公告
     * @desc 添加公告
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement
     * @method POST
     * @param string title - desc:标题 validate:required
     * @param int addon_idcsmart_announcement_type_id - desc:分类ID validate:required
     * @param string keywords - desc:关键字 validate:optional
     * @param string img - desc:公告缩略图 validate:optional
     * @param array attachment - desc:附件,上传附件需调用后台公共接口文件上传获取新的save_name传入 validate:optional
     * @param string content - desc:内容 validate:required
     * @param int hidden - desc:0显示1隐藏 validate:required
     */
    public function createIdcsmartAnnouncement()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 创建公告
        $result = $IdcsmartAnnouncementModel->createIdcsmartAnnouncement($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 修改公告
     * @desc 修改公告
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/:id
     * @method PUT
     * @param int id - desc:公告ID validate:required
     * @param string title - desc:标题 validate:required
     * @param int addon_idcsmart_announcement_type_id - desc:分类ID validate:required
     * @param string keywords - desc:关键字 validate:optional
     * @param string img - desc:公告缩略图 validate:optional
     * @param array attachment - desc:附件,上传附件需调用后台公共接口文件上传获取新的save_name传入 validate:optional
     * @param string content - desc:内容 validate:required
     * @param int hidden - desc:0显示1隐藏 validate:required
     */
    public function updateIdcsmartAnnouncement()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 修改公告
        $result = $IdcsmartAnnouncementModel->updateIdcsmartAnnouncement($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除公告
     * @desc 删除公告
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/:id
     * @method DELETE
     * @param int id - desc:公告ID validate:required
     */
    public function deleteIdcsmartAnnouncement()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 删除公告
        $result = $IdcsmartAnnouncementModel->deleteIdcsmartAnnouncement($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示公告
     * @desc 隐藏/显示公告
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/:id/hidden
     * @method PUT
     * @param int id - desc:公告ID validate:required
     * @param int hidden - desc:0显示1隐藏 validate:required
     */
    public function hiddenIdcsmartAnnouncement()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 隐藏公告
        $result = $IdcsmartAnnouncementModel->hiddenIdcsmartAnnouncement($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 获取公告分类
     * @desc 获取公告分类
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/type
     * @method GET
     * @return array list - desc:公告分类
     * @return int list[].id - desc:公告分类ID
     * @return string list[].name - desc:名称
     * @return string list[].admin - desc:修改人
     * @return int list[].update_time - desc:修改时间
     * @return int list[].announcement_num - desc:公告数量
     */
    public function idcsmartAnnouncementTypeList()
    {  
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 获取公告分类
        $data = $IdcsmartAnnouncementTypeModel->idcsmartAnnouncementTypeList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 添加公告分类
     * @desc 添加公告分类
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/type
     * @method POST
     * @param array list - desc:分类数组 validate:required
     * @param string list[].name - desc:名称 validate:required
     */
    public function createIdcsmartAnnouncementType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 创建公告分类
        $result = $IdcsmartAnnouncementTypeModel->createIdcsmartAnnouncementType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 修改公告分类
     * @desc 修改公告分类
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/type/:id
     * @method PUT
     * @param int id - desc:公告分类ID validate:required
     * @param string name - desc:名称 validate:required
     */
    public function updateIdcsmartAnnouncementType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 修改公告分类
        $result = $IdcsmartAnnouncementTypeModel->updateIdcsmartAnnouncementType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除公告分类
     * @desc 删除公告分类
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/type/:id
     * @method DELETE
     * @param int id - desc:公告分类ID validate:required
     */
    public function deleteIdcsmartAnnouncementType()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 删除公告分类
        $result = $IdcsmartAnnouncementTypeModel->deleteIdcsmartAnnouncementType($param['id']);

        return json($result);
    }
}
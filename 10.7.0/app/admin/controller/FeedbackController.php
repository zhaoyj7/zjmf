<?php
namespace app\admin\controller;

use app\common\model\FeedbackModel;
use app\common\model\FeedbackTypeModel;
use app\admin\validate\FeedbackTypeValidate;

/**
 * @title 意见反馈
 * @desc 意见反馈
 * @use app\admin\controller\FeedbackController
 */
class FeedbackController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new FeedbackTypeValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 意见反馈列表
     * @desc 意见反馈列表
     * @url /admin/v1/feedback
     * @method GET
     * @author theworld
     * @version v1
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @param string orderby - desc:排序字段 id validate:optional
     * @param string sort - desc:升降序 asc desc validate:optional
     * @return array list - desc:意见反馈列表
     * @return int list[].id - desc:意见反馈ID
     * @return string list[].title - desc:标题
     * @return string list[].type - desc:类型
     * @return string list[].description - desc:描述
     * @return int list[].client_id - desc:用户ID
     * @return string list[].username - desc:用户名
     * @return string list[].contact - desc:联系方式
     * @return array list[].attachment - desc:附件
     * @return int list[].create_time - desc:反馈时间
     * @return int count - desc:意见反馈总数
     */
	public function feedbackList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $FeedbackModel = new FeedbackModel();

        // 获取意见反馈列表
        $data = $FeedbackModel->feedbackList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2023-02-28
     * @title 获取意见反馈类型
     * @desc 获取意见反馈类型
     * @url /admin/v1/feedback/type
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:意见反馈类型列表
     * @return int list[].id - desc:意见反馈类型ID
     * @return string list[].name - desc:名称
     * @return string list[].description - desc:描述
     */
    public function feedbackTypeList()
    {  
        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();

        // 获取意见反馈类型
        $data = $FeedbackTypeModel->feedbackTypeList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加意见反馈类型
     * @desc 添加意见反馈类型
     * @url /admin/v1/feedback/type
     * @method POST
     * @author theworld
     * @version v1
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:required
     */
    public function createFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 新建意见反馈类型
        $result = $FeedbackTypeModel->createFeedbackType($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑意见反馈类型
     * @desc 编辑意见反馈类型
     * @url /admin/v1/feedback/type/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:意见反馈类型ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string description - desc:描述 validate:required
     */
    public function updateFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 修改意见反馈类型
        $result = $FeedbackTypeModel->updateFeedbackType($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除意见反馈类型
     * @desc 删除意见反馈类型
     * @url /admin/v1/feedback/type/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:意见反馈类型ID validate:required
     */
    public function deleteFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 删除意见反馈类型
        $result = $FeedbackTypeModel->deleteFeedbackType($param['id']);

        return json($result);

    }

    
}
<?php
namespace app\admin\controller;

use app\common\model\FriendlyLinkModel;
use app\admin\validate\FriendlyLinkValidate;

/**
 * @title 友情链接
 * @desc 友情链接
 * @use app\admin\controller\FriendlyLinkController
 */
class FriendlyLinkController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new FriendlyLinkValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 获取友情链接
     * @desc 获取友情链接
     * @url /admin/v1/friendly_link
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:友情链接列表
     * @return int list[].id - desc:友情链接ID
     * @return string list[].name - desc:名称
     * @return string list[].url - desc:链接地址
     */
    public function list()
    {  
        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();

        // 获取友情链接
        $data = $FriendlyLinkModel->friendlyLinkList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加友情链接
     * @desc 添加友情链接
     * @url /admin/v1/friendly_link
     * @method POST
     * @author theworld
     * @version v1
     * @param string name - desc:名称 validate:required
     * @param string url - desc:链接地址 validate:required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 新建友情链接
        $result = $FriendlyLinkModel->createFriendlyLink($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑友情链接
     * @desc 编辑友情链接
     * @url /admin/v1/friendly_link/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:友情链接ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string url - desc:链接地址 validate:required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 修改友情链接
        $result = $FriendlyLinkModel->updateFriendlyLink($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除友情链接
     * @desc 删除友情链接
     * @url /admin/v1/friendly_link/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:友情链接ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 删除友情链接
        $result = $FriendlyLinkModel->deleteFriendlyLink($param['id']);

        return json($result);

    }

    
}
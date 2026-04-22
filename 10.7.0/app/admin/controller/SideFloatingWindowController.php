<?php
namespace app\admin\controller;

use app\common\model\SideFloatingWindowModel;
use app\admin\validate\SideFloatingWindowValidate;

/**
 * @title 模板控制器-侧边浮窗
 * @desc 模板控制器-侧边浮窗
 * @use app\admin\controller\SideFloatingWindowController
 */
class SideFloatingWindowController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SideFloatingWindowValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 侧边浮窗列表
     * @desc 侧边浮窗列表
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window
     * @method GET
     * @return array list - desc:侧边浮窗列表
     * @return int list[].id - desc:侧边浮窗ID
     * @return string list[].name - desc:名称
     * @return string list[].icon - desc:图标
     * @return string list[].content - desc:显示内容
     */
    public function list()
    {
        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();

        // 导航列表
        $data = $SideFloatingWindowModel->sideFloatingWindowList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建侧边浮窗
     * @desc 创建侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window
     * @method POST
     * @param string name - desc:名称 validate:required
     * @param string icon - desc:图标 validate:required
     * @param string content - desc:显示内容 validate:required
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
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 创建侧边浮窗
        $result = $SideFloatingWindowModel->createSideFloatingWindow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑侧边浮窗
     * @desc 编辑侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/:id
     * @method PUT
     * @param int id - desc:侧边浮窗ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string icon - desc:图标 validate:required
     * @param string content - desc:显示内容 validate:required
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
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 编辑侧边浮窗
        $result = $SideFloatingWindowModel->updateSideFloatingWindow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除侧边浮窗
     * @desc 删除侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/:id
     * @method DELETE
     * @param int id - desc:侧边浮窗ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 删除侧边浮窗
        $result = $SideFloatingWindowModel->deleteSideFloatingWindow($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 侧边浮窗排序
     * @desc 侧边浮窗排序
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/order
     * @method PUT
     * @param array id - desc:侧边浮窗ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 侧边浮窗排序
        $result = $SideFloatingWindowModel->sideFloatingWindowOrder($param);

        return json($result);
    }
}
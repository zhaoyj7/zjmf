<?php
namespace app\admin\controller;

use app\common\model\BottomBarGroupModel;
use app\admin\validate\BottomBarGroupValidate;

/**
 * @title 模板控制器-底部栏分组
 * @desc 模板控制器-底部栏分组
 * @use app\admin\controller\BottomBarGroupController
 */
class BottomBarGroupController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new BottomBarGroupValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏分组列表
     * @desc 底部栏分组列表
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_group
     * @method GET
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @return array list - desc:分组
     * @return int list[].id - desc:分组ID
     * @return string list[].name - desc:名称
     */
    public function list()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 实例化模型类
        $BottomBarGroupModel = new BottomBarGroupModel();

        // 导航列表
        $data = $BottomBarGroupModel->groupList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建底部栏分组
     * @desc 创建底部栏分组
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_group
     * @method POST
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param string name - desc:名称 validate:required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $BottomBarGroupModel = new BottomBarGroupModel();
        
        // 创建底部栏分组
        $result = $BottomBarGroupModel->createGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑底部栏分组
     * @desc 编辑底部栏分组
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_group/:id
     * @method PUT
     * @param int id - desc:分组ID validate:required
     * @param string name - desc:名称 validate:required
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
        $BottomBarGroupModel = new BottomBarGroupModel();
        
        // 编辑底部栏分组
        $result = $BottomBarGroupModel->updateGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除底部栏分组
     * @desc 删除底部栏分组
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_group/:id
     * @method DELETE
     * @param int id - desc:分组ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $BottomBarGroupModel = new BottomBarGroupModel();
        
        // 删除底部栏分组
        $result = $BottomBarGroupModel->deleteGroup($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏分组排序
     * @desc 底部栏分组排序
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_group/order
     * @method PUT
     * @param string theme - desc:主题标识 不传递时默认为当前系统设置的主题 validate:optional
     * @param string language - desc:语言 zh-cn en-us validate:optional
     * @param array id - desc:分组ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();
        $param['language'] = $param['language'] ?? 'zh-cn';

        // 实例化模型类
        $BottomBarGroupModel = new BottomBarGroupModel();
        
        // 底部栏分组排序
        $result = $BottomBarGroupModel->groupOrder($param);

        return json($result);
    }
}
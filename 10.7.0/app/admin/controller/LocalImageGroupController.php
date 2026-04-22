<?php
namespace app\admin\controller;

use app\common\model\LocalImageGroupModel;
use app\admin\validate\LocalImageGroupValidate;

/**
 * @title 本地镜像分组
 * @desc 本地镜像分组
 * @use app\admin\controller\LocalImageGroupController
 */
class LocalImageGroupController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new LocalImageGroupValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 本地镜像分组列表
     * @desc 本地镜像分组列表
     * @url /admin/v1/local_image_group
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:分组列表
     * @return int list[].id - desc:分组ID
     * @return string list[].name - desc:名称
     */
    public function list()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $LocalImageGroupModel = new LocalImageGroupModel();

        // 导航列表
        $data = $LocalImageGroupModel->groupList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建本地镜像分组
     * @desc 创建本地镜像分组
     * @url /admin/v1/local_image_group
     * @method POST
     * @author theworld
     * @version v1
     * @param string name - desc:名称 validate:required
     * @param string icon - desc:图标 validate:required
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
        $LocalImageGroupModel = new LocalImageGroupModel();
        
        // 创建本地镜像分组
        $result = $LocalImageGroupModel->createGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑本地镜像分组
     * @desc 编辑本地镜像分组
     * @url /admin/v1/local_image_group/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:分组ID validate:required
     * @param string name - desc:名称 validate:required
     * @param string icon - desc:图标 validate:required
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
        $LocalImageGroupModel = new LocalImageGroupModel();
        
        // 编辑本地镜像分组
        $result = $LocalImageGroupModel->updateGroup($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除本地镜像分组
     * @desc 删除本地镜像分组
     * @url /admin/v1/local_image_group/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:分组ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $LocalImageGroupModel = new LocalImageGroupModel();
        
        // 删除本地镜像分组
        $result = $LocalImageGroupModel->deleteGroup($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 本地镜像分组排序
     * @desc 本地镜像分组排序
     * @url /admin/v1/local_image_group/order
     * @method PUT
     * @author theworld
     * @version v1
     * @param array id - desc:分组ID validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $LocalImageGroupModel = new LocalImageGroupModel();
        
        // 本地镜像分组排序
        $result = $LocalImageGroupModel->groupOrder($param);

        return json($result);
    }
}
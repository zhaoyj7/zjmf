<?php
namespace app\admin\controller;

use app\common\model\PhysicalServerAreaModel;
use app\admin\validate\PhysicalServerAreaValidate;

/**
 * @title 模板控制器-物理服务器区域
 * @desc 模板控制器-物理服务器区域
 * @use app\admin\controller\PhysicalServerAreaController
 */
class PhysicalServerAreaController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PhysicalServerAreaValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器区域列表
     * @desc 物理服务器区域列表
     * @url /admin/v1/physical_server_area
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:区域列表
     * @return int list[].id - desc:区域ID
     * @return string list[].first_area - desc:一级区域
     * @return string list[].second_area - desc:二级区域
     * @return array area - desc:区域选项
     * @return string area[].name - desc:一级区域名称
     * @return array area[].children - desc:二级区域
     * @return int area[].children[].id - desc:二级区域ID
     * @return string area[].children[].name - desc:二级区域名称
     */
    public function list()
    {
        // 实例化模型类
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();

        // 物理服务器区域列表
        $data = $PhysicalServerAreaModel->areaList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加物理服务器区域
     * @desc 添加物理服务器区域
     * @url /admin/v1/physical_server_area
     * @method POST
     * @author theworld
     * @version v1
     * @param string first_area - desc:一级区域 validate:required
     * @param string second_area - desc:二级区域 validate:required
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
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        
        // 添加物理服务器区域
        $result = $PhysicalServerAreaModel->createArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改物理服务器区域
     * @desc 修改物理服务器区域
     * @url /admin/v1/physical_server_area/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:区域ID validate:required
     * @param string first_area - desc:一级区域 validate:required
     * @param string second_area - desc:二级区域 validate:required
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
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        
        // 修改物理服务器区域
        $result = $PhysicalServerAreaModel->updateArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除物理服务器区域
     * @desc 删除物理服务器区域
     * @url /admin/v1/physical_server_area/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:区域ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        
        // 删除物理服务器区域
        $result = $PhysicalServerAreaModel->deleteArea($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器区域排序
     * @desc 物理服务器区域排序
     * @url /admin/v1/physical_server_area/order
     * @method PUT
     * @author theworld
     * @version v1
     * @param array id - desc:区域ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        
        // 物理服务器区域排序
        $result = $PhysicalServerAreaModel->areaOrder($param);

        return json($result);
    }
}
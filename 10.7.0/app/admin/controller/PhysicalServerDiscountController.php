<?php
namespace app\admin\controller;

use app\common\model\PhysicalServerDiscountModel;
use app\admin\validate\PhysicalServerDiscountValidate;

/**
 * @title 模板控制器-物理服务器优惠
 * @desc 模板控制器-物理服务器优惠
 * @use app\admin\controller\PhysicalServerDiscountController
 */
class PhysicalServerDiscountController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PhysicalServerDiscountValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器优惠列表
     * @desc 物理服务器优惠列表
     * @url /admin/v1/physical_server_discount
     * @method GET
     * @author theworld
     * @version v1
     * @return array list - desc:优惠列表
     * @return int list[].id - desc:优惠ID
     * @return string list[].title - desc:标题
     * @return string list[].description - desc:描述
     * @return string list[].url - desc:跳转链接
     */
    public function list()
    {
        // 实例化模型类
        $PhysicalServerDiscountModel = new PhysicalServerDiscountModel();

        // 物理服务器优惠列表
        $data = $PhysicalServerDiscountModel->discountList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建物理服务器优惠
     * @desc 创建物理服务器优惠
     * @url /admin/v1/physical_server_discount
     * @method POST
     * @author theworld
     * @version v1
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param string url - desc:跳转链接 validate:required
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
        $PhysicalServerDiscountModel = new PhysicalServerDiscountModel();
        
        // 创建物理服务器优惠
        $result = $PhysicalServerDiscountModel->createDiscount($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑物理服务器优惠
     * @desc 编辑物理服务器优惠
     * @url /admin/v1/physical_server_discount/:id
     * @method PUT
     * @author theworld
     * @version v1
     * @param int id - desc:优惠ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param string url - desc:跳转链接 validate:required
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
        $PhysicalServerDiscountModel = new PhysicalServerDiscountModel();
        
        // 编辑物理服务器优惠
        $result = $PhysicalServerDiscountModel->updateDiscount($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除物理服务器优惠
     * @desc 删除物理服务器优惠
     * @url /admin/v1/physical_server_discount/:id
     * @method DELETE
     * @author theworld
     * @version v1
     * @param int id - desc:优惠ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerDiscountModel = new PhysicalServerDiscountModel();
        
        // 删除物理服务器优惠
        $result = $PhysicalServerDiscountModel->deleteDiscount($param['id']);

        return json($result);
    }
}
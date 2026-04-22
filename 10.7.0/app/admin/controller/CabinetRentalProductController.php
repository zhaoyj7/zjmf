<?php
namespace app\admin\controller;

use app\common\model\CabinetRentalProductModel;
use app\admin\validate\CabinetRentalProductValidate;

/**
 * @title 模板控制器-机柜租用商品
 * @desc 模板控制器-机柜租用商品
 * @use app\admin\controller\CabinetRentalProductController
 */
class CabinetRentalProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CabinetRentalProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 机柜租用商品列表
     * @desc 机柜租用商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/cabinet_rental_product
     * @method GET
     * @return array list - desc:商品
     * @return int list[].id - desc:商品ID
     * @return string list[].title - desc:标题
     * @return string list[].description - desc:描述
     * @return string list[].price - desc:价格
     * @return int list[].product_id - desc:关联商品ID
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $CabinetRentalProductModel = new CabinetRentalProductModel();

        // 机柜租用商品列表
        $data = $CabinetRentalProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建机柜租用商品
     * @desc 创建机柜租用商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cabinet_rental_product
     * @method POST
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param float price - desc:价格 validate:required
     * @param int product_id - desc:关联商品ID validate:required
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
        $CabinetRentalProductModel = new CabinetRentalProductModel();
        
        // 创建机柜租用商品
        $result = $CabinetRentalProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑机柜租用商品
     * @desc 编辑机柜租用商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cabinet_rental_product/:id
     * @method PUT
     * @param int id - desc:商品ID validate:required
     * @param string title - desc:标题 validate:required
     * @param string description - desc:描述 validate:required
     * @param float price - desc:价格 validate:required
     * @param int product_id - desc:关联商品ID validate:required
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
        $CabinetRentalProductModel = new CabinetRentalProductModel();
        
        // 编辑机柜租用商品
        $result = $CabinetRentalProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除机柜租用商品
     * @desc 删除机柜租用商品
     * @author theworld
     * @version v1
     * @url /admin/v1/cabinet_rental_product/:id
     * @method DELETE
     * @param int id - desc:商品ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CabinetRentalProductModel = new CabinetRentalProductModel();
        
        // 删除机柜租用商品
        $result = $CabinetRentalProductModel->deleteProduct($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 机柜租用商品排序
     * @desc 机柜租用商品排序
     * @author theworld
     * @version v1
     * @url /admin/v1/cabinet_rental_product/order
     * @method PUT
     * @param array id - desc:商品ID数组 validate:required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CabinetRentalProductModel = new CabinetRentalProductModel();
        
        // 机柜租用商品排序
        $result = $CabinetRentalProductModel->productOrder($param);

        return json($result);
    }
}
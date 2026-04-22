<?php
namespace app\home\controller;

use app\home\model\CartModel;
use app\home\validate\CartValidate;

/**
 * @title 购物车管理
 * @desc 购物车管理
 * @use app\home\controller\CartController
 */
class CartController extends HomeBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new CartValidate();
    }

	/**
     * 时间 2022-05-30
     * @title 获取购物车
     * @desc 获取购物车
     * @author theworld
     * @version v1
     * @url /console/v1/cart
     * @method GET
     * @return array list - desc:购物车商品列表
     * @return int list[].product_id - desc:商品ID
     * @return object list[].config_options - desc:自定义配置
     * @return int list[].qty - desc:数量
     * @return object list[].customfield - desc:自定义参数
     * @return string list[].name - desc:商品名称
     * @return string list[].description - desc:商品描述
     * @return int list[].stock_control - desc:库存控制 0关闭 1启用
     * @return int list[].stock_qty - desc:库存数量
     * @return object list[].self_defined_field - desc:自定义字段 格式{"5":"123"} 5是自定义字段ID 123是填写的内容
     */
	public function index()
	{
		$result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new CartModel())->indexCart()
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-30
     * @title 加入购物车
     * @desc 加入购物车
     * @author theworld
     * @version v1
     * @url /console/v1/cart
     * @method POST
     * @param int product_id - desc:商品ID validate:required
     * @param object config_options - desc:自定义配置 validate:optional
     * @param int qty - desc:数量 validate:required
     * @param object customfield - desc:自定义参数 validate:optional
     * @param object self_defined_field - desc:自定义字段 格式{"5":"123"} 5是自定义字段ID 123是填写的内容 validate:optional
     * @param array products - desc:商品列表 批量加入购物车必传 validate:optional
     * @param int products[].product_id - desc:商品ID
     * @param object products[].config_options - desc:自定义配置
     * @param int products[].qty - desc:数量
     * @param object products[].customfield - desc:自定义参数
     * @param object products[].self_defined_field - desc:自定义字段
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
        $CartModel = new CartModel();
        
        // 加入购物车
        $result = $CartModel->createCart($param);

        return json($result);
	}

	/**
     * 时间 2022-05-30
     * @title 编辑购物车商品
     * @desc 编辑购物车商品
     * @author theworld
     * @version v1
     * @url /console/v1/cart/:position
     * @method PUT
     * @param int position - desc:位置 validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param object config_options - desc:自定义配置 validate:optional
     * @param int qty - desc:数量 validate:required
     * @param object customfield - desc:自定义参数 validate:optional
     * @param object self_defined_field - desc:自定义字段 格式{"5":"123"} 5是自定义字段ID 123是填写的内容 validate:optional
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
        $CartModel = new CartModel();
        
        // 编辑购物车商品
        $result = $CartModel->updateCart($param);

        return json($result);
	}

	/**
     * 时间 2022-05-30
     * @title 修改购物车商品数量
     * @desc 修改购物车商品数量
     * @author theworld
     * @version v1
     * @url /console/v1/cart/:position/qty
     * @method PUT
     * @param int position - desc:位置 validate:required
     * @param int qty - desc:数量 validate:required
     */
	public function updateQty()
	{
		// 接收参数
		$param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_qty')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

		// 实例化模型类
        $CartModel = new CartModel();
        
        // 编辑购物车商品数量
        $result = $CartModel->updateCartQty($param);

        return json($result);
	}

	/**
     * 时间 2022-05-30
     * @title 删除购物车商品
     * @desc 删除购物车商品
     * @author theworld
     * @version v1
     * @url /console/v1/cart/:position
     * @method DELETE
     * @param int position - desc:位置 validate:required
     */
	public function delete()
	{
		// 接收参数
		$param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

		// 实例化模型类
        $CartModel = new CartModel();
        
        // 删除购物车商品
        $result = $CartModel->deleteCart($param['position']);

        return json($result);
	}

    /**
     * 时间 2022-05-30
     * @title 批量删除购物车商品
     * @desc 批量删除购物车商品
     * @author theworld
     * @version v1
     * @url /console/v1/cart/batch
     * @method DELETE
     * @param array positions - desc:位置数组 validate:required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('batch_delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CartModel = new CartModel();
        
        // 删除购物车商品
        $result = $CartModel->batchDeleteCart($param['positions']);

        return json($result);
    }

	/**
     * 时间 2022-05-30
     * @title 清空购物车
     * @desc 清空购物车
     * @author theworld
     * @version v1
     * @url /console/v1/cart
     * @method DELETE
     */
	public function clear()
	{
	    $param = $this->request->param();
		// 实例化模型类
        $CartModel = new CartModel();
        
        // 清空购物车
        $result = $CartModel->clearCart($param);

        return json($result);
	}

	/**
     * 时间 2022-05-31
     * @title 结算购物车
     * @desc 结算购物车
     * @author theworld
     * @version v1
     * @url /console/v1/cart/settle
     * @method POST
     * @param array positions - desc:商品位置数组 validate:required
     * @param object customfield - desc:自定义参数 比如优惠码参数传{"promo_code":["pr8nRQOGbmv5"]} validate:optional
     * @param int downstream_host_id - desc:下游产品ID validate:optional
     * @param string downstream_url - desc:下游地址 validate:optional
     * @param string downstream_token - desc:下游产品token validate:optional
     * @param string downstream_system_type - desc:下游系统类型 validate:optional
     * @return int order_id - desc:订单ID
     */
	public function settle()
	{
		// 接收参数
		$param = $this->request->param();

		// 参数验证
        if (!$this->validate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

		// 实例化模型类
        $CartModel = new CartModel();
        
        // 结算购物车
        $result = $CartModel->settle($param['positions'],$param['customfield']??[],$param);

        return json($result);
	}
}

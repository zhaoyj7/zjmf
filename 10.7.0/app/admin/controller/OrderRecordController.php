<?php
namespace app\admin\controller;

use app\common\model\OrderRecordModel;
use app\admin\validate\OrderRecordValidate;

/**
 * @title 订单信息记录
 * @desc 订单信息记录
 * @use app\admin\controller\OrderRecordController
 */
class OrderRecordController extends AdminBaseController
{
    protected $validate;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new OrderRecordValidate();
    }

    /**
     * 时间 2025-10-27
     * @title 订单信息记录列表
     * @desc 订单信息记录列表
     * @url /admin/v1/order/:id/record
     * @method GET
     * @author hh
     * @version v1
     * @param int page - desc:页数 validate:optional
     * @param int limit - desc:每页条数 validate:optional
     * @return array list - desc:订单信息记录列表
     * @return int list[].id - desc:订单信息记录ID
     * @return string list[].content - desc:内容
     * @return array list[].attachment - desc:附件
     * @return int list[].admin_id - desc:管理员ID
     * @return string list[].admin_name - desc:管理员名称
     * @return int list[].create_time - desc:创建时间
     * @return int count - desc:订单信息记录总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $OrderRecordModel = new OrderRecordModel();

        // 获取订单信息记录列表
        $data = $OrderRecordModel->orderRecordList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2025-10-27
     * @title 新增订单信息记录
     * @desc 新增订单信息记录
     * @url /admin/v1/order/:id/record
     * @method POST
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @param string content - desc:内容 validate:required
     * @param array attachment - desc:附件 validate:optional
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
        $OrderRecordModel = new OrderRecordModel();
        
        // 新增订单信息记录
        $result = $OrderRecordModel->createOrderRecord($param);

        return json($result);
    }

    /**
     * 时间 2025-10-27
     * @title 编辑订单信息记录
     * @desc 编辑订单信息记录
     * @url /admin/v1/order/record/:id
     * @method PUT
     * @author hh
     * @version v1
     * @param int id - desc:订单信息记录ID validate:required
     * @param string content - desc:内容 validate:required
     * @param array attachment - desc:附件 validate:optional
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
        $OrderRecordModel = new OrderRecordModel();
        
        // 编辑订单信息记录
        $result = $OrderRecordModel->updateOrderRecord($param);

        return json($result);
    }

    /**
     * 时间 2025-10-27
     * @title 删除订单信息记录
     * @desc 删除订单信息记录
     * @url /admin/v1/order/record/:id
     * @method DELETE
     * @author hh
     * @version v1
     * @param int id - desc:订单信息记录ID validate:required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderRecordModel = new OrderRecordModel();
        
        // 删除订单信息记录
        $result = $OrderRecordModel->deleteOrderRecord($param['id']);

        return json($result);

    }

    
}

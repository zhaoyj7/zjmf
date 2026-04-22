<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 订单信息记录模型
 * @desc 订单信息记录模型
 * @use app\common\model\OrderRecordModel
 */
class OrderRecordModel extends Model
{
	protected $name = 'order_record';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'order_id'      => 'int',
        'admin_id'      => 'int',
        'content'       => 'string',
        'attachment'    => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2025-10-27
     * @title 订单信息记录列表
     * @desc 订单信息记录列表
     * @author hh
     * @version v1
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 订单信息记录
     * @return int list[].id - 订单信息记录ID 
     * @return string list[].content - 内容
     * @return array list[].attachment - 附件
     * @return int list[].admin_id - 管理员ID 
     * @return string list[].admin_name - 管理员名称
     * @return int list[].create_time - 创建时间
     * @return int count - 订单信息记录总数
     */
    public function orderRecordList($param)
    {
        $count = $this->alias('a')
            ->field('a.id')
            ->where('a.order_id', $param['id'])
            ->count();

        $url = request()->domain() . '/upload/common/default/';

        $list = $this->alias('a')
            ->field('a.id,a.content,a.attachment,a.admin_id,b.name admin_name,a.create_time')
            ->leftjoin('admin b', 'b.id=a.admin_id')
            ->where('a.order_id', $param['id'])
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('a.create_time', 'desc')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['attachment'] = !empty($value['attachment']) ? explode(',', $value['attachment']) : [];
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2025-10-27
     * @title 新增订单信息记录
     * @desc 新增订单信息记录
     * @author hh
     * @version v1
     * @param int id - 订单ID required
     * @param string content - 内容 required
     * @param array attachment - 附件
     */
    public function createOrderRecord($param)
    {
        $adminId = get_admin_id();

        $order = OrderModel::find($param['id']);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        $param['attachment'] = $param['attachment'] ?? [];
        foreach ($param['attachment'] as $key => $value) {
            if(!file_exists(UPLOAD_DEFAULT.$value)){
                return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
            }
        }

        $this->startTrans();
        try{
            $this->create([
                'order_id' => $param['id'],
                'admin_id' => $adminId, 
                'content' => $param['content'] ?? '',
                'attachment' => implode(',', $param['attachment']),
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    /**
     * 时间 2025-10-27
     * @title 编辑订单信息记录
     * @desc 编辑订单信息记录
     * @author hh
     * @version v1
     * @param int id - 订单信息记录ID required
     * @param string content - 内容 required
     * @param array attachment - 附件
     */
    public function updateOrderRecord($param)
    {
        $adminId = get_admin_id();

        $record = $this->find($param['id']);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('order_record_is_not_exist')];
        }
        $param['attachment'] = $param['attachment'] ?? [];
        foreach ($param['attachment'] as $key => $value) {
            if(!file_exists(UPLOAD_DEFAULT.$value)){
                return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
            }
        }

        $this->startTrans();
        try{
            $this->update([
                'admin_id' => $adminId, 
                'content' => $param['content'] ?? '',
                'attachment' => implode(',', $param['attachment']),
                'update_time' => time(),
            ], ['id' => $param['id']]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('update_fail')];
        }

        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2025-10-27
     * @title 删除订单信息记录
     * @desc 删除订单信息记录
     * @author hh
     * @version v1
     * @param int id - 订单信息记录ID required
     */
    public function deleteOrderRecord($id)
    {
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('order_record_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('delete_fail')];
        }

        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

}

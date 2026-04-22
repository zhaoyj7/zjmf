<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 本地镜像分组模型
 * @desc 本地镜像分组模型
 * @use app\common\model\LocalImageGroupModel
 */
class LocalImageGroupModel extends Model
{
    protected $name = 'local_image_group';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'name'          => 'string',
        'icon'          => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-10-23
     * @title 本地镜像分组列表
     * @desc 本地镜像分组列表
     * @author theworld
     * @version v1
     * @return array list -  分组
     * @return int list[].id - 分组ID
     * @return string list[].name - 名称
     * @return string list[].icon - 图标
     */
    public function groupList($param)
    {
        $list = $this->field('id,name,icon')
            ->order('order', 'asc')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2024-10-23
     * @title 创建本地镜像分组
     * @desc 创建本地镜像分组
     * @author theworld
     * @version v1
     * @param string param.name - 名称
     * @param string param.icon - 图标
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createGroup($param)
    {
        $this->startTrans();
        try {
            $order = $this->max('order');

            $group = $this->create([
                'name' => $param['name'],
                'icon' => $param['icon'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_local_image_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'local_image_group', $group->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 编辑本地镜像分组
     * @desc 编辑本地镜像分组
     * @author theworld
     * @version v1
     * @param int param.id - 分组ID required
     * @param string param.name - 名称
     * @param string param.icon - 图标
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateGroup($param)
    {
        // 验证分组ID
        $group = $this->find($param['id']);
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('local_image_group_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'name' => $param['name'],
                'icon' => $param['icon'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'name'  => lang('local_image_group_name'),
                'icon'  => lang('local_image_group_icon'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $group[$k] != $param[$k]){
                    $old = $group[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_local_image_group', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $group['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'local_image_group', $group->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 删除本地镜像分组
     * @desc 删除本地镜像分组
     * @author theworld
     * @version v1
     * @param int id - 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteGroup($id)
    {
        // 验证分组ID
        $group = $this->find($id);
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('local_image_group_not_exist')];
        }

        $LocalImageModel = new LocalImageModel();
        $count = $LocalImageModel->where('group_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang('local_image_group_used_cannot_delete')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_local_image_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $group['name']]), 'local_image_group', $group->id);
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 本地镜像分组排序
     * @desc 本地镜像分组排序
     * @author theworld
     * @version v1
     * @param array param.id - 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function groupOrder($param)
    {
        $id = $param['id'] ?? [];
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $group = $this->column('id');
        if(count($id)!=count($group)){
            return ['status'=>400, 'msg'=>lang('local_image_group_not_exist')];
        }

        $this->startTrans();
        try {
            foreach ($id as $key => $value) {
                $this->update([
                    'order' => $key,
                    'update_time' => time()
                ], ['id' => $value]);
            }

            # 记录日志
            active_log(lang('log_order_local_image_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];

    }
}
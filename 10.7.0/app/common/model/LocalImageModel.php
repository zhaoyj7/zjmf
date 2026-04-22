<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 本地镜像模型
 * @desc 本地镜像模型
 * @use app\common\model\LocalImageModel
 */
class LocalImageModel extends Model
{
    protected $name = 'local_image';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'group_id'      => 'int',
        'name'     	    => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 本地镜像列表
     * @desc 本地镜像列表
     * @author theworld
     * @version v1
     * @return array list -  镜像
     * @return int list[].id - 镜像ID
     * @return string list[].group_id - 分组ID
     * @return string list[].group_name - 分组名称
     * @return string list[].icon - 图标
     * @return string list[].name - 镜像名称
     */
    public function imageList($param)
    {
        $list = $this->alias('a')
            ->field('a.id,a.group_id,b.name group_name,b.icon,a.name')
            ->leftjoin('local_image_group b', 'b.id=a.group_id')
            ->order('a.order', 'asc')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建本地镜像
     * @desc 创建本地镜像
     * @author theworld
     * @version v1
     * @param int param.group_id - 分组ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createImage($param)
    {
        $group = LocalImageGroupModel::where('id', $param['group_id'])->find();
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('local_image_group_not_exist')];
        }

        $this->startTrans();
        try {
            $order = $this->max('order');
 
            $image = $this->create([
                'group_id' => $param['group_id'],
                'name' => $param['name'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_local_image', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'local_image', $image->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 编辑本地镜像
     * @desc 编辑本地镜像
     * @author theworld
     * @version v1
     * @param int param.id - 镜像ID required
     * @param int param.group_id - 分组ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateImage($param)
    {
        // 验证镜像ID
        $image = $this->find($param['id']);
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang('local_image_not_exist')];
        }

        $LocalImageGroupModel = new LocalImageGroupModel();
        $group = $LocalImageGroupModel->where('id', $param['group_id'])->find();
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('local_image_group_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'group_id' => $param['group_id'],
                'name' => $param['name'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'group_id'     => lang('local_image_group_id'),
                'name'         => lang('local_image_name'),
            ];


            $param['group_id'] = $group['name'];
 
            $old = $LocalImageGroupModel->where('id', $image['group_id'])->find();
            $image['group_id'] = $old['name'];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $image[$k] != $param[$k]){
                    $old = $image[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_local_image', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $image['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'local_image', $image->id);
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
     * 时间 2024-04-02
     * @title 删除本地镜像
     * @desc 删除本地镜像
     * @author theworld
     * @version v1
     * @param int id - 镜像ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteImage($id)
    {
        // 验证镜像ID
        $image = $this->find($id);
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang('local_image_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_local_image', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $image['name']]), 'local_image', $image->id);
            
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
     * 时间 2024-04-02
     * @title 本地镜像排序
     * @desc 本地镜像排序
     * @author theworld
     * @version v1
     * @param array param.id - 镜像ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function imageOrder($param)
    {
        $id = $param['id'] ?? [];
        $image = $this->column('id');
        if(count($id)!=count($image)){
            return ['status'=>400, 'msg'=>lang('local_image_not_exist')];
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
            active_log(lang('log_bottom_bar_order_image', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];

    }
}
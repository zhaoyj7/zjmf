<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-物理服务器区域模型
 * @desc 模板控制器-物理服务器区域模型
 * @use app\common\model\PhysicalServerAreaModel
 */
class PhysicalServerAreaModel extends Model
{
    protected $name = 'physical_server_area';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'first_area'    => 'string',
        'second_area'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'order'         => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 物理服务器区域列表
     * @desc 物理服务器区域列表
     * @author theworld
     * @version v1
     * @return array list -  区域
     * @return int list[].id - 区域ID
     * @return string list[].first_area - 一级区域
     * @return string list[].second_area - 二级区域
     * @return array area - 区域选项
     * @return string area[].name - 一级区域名称
     * @return array area[].children - 二级区域
     * @return int list[].children[].id - 二级区域ID
     * @return string list[].children[].name - 二级区域名称
     */
    public function areaList()
    {
        $list = $this->field('id,first_area,second_area')
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $second = [];
        foreach ($list as $key => $value) {
            if(!isset($second[$value['first_area']])){
                $second[$value['first_area']] = [];
            }
            $second[$value['first_area']][] = ['id' => $value['id'], 'name' => $value['second_area']];
        }

        $first = [];
        foreach ($list as $key => $value) {
            if(!in_array($value['first_area'], array_column($first, 'name'))){
                $first[] = ['name' => $value['first_area'], 'children' => $second[$value['first_area']]];
            }
        }

        return ['list' => $list, 'area' => $first];
    }

    /**
     * 时间 2024-04-02
     * @title 创建物理服务器区域
     * @desc 创建物理服务器区域
     * @author theworld
     * @version v1
     * @param string param.first_area - 一级区域 required
     * @param string param.second_area - 二级区域 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createArea($param)
    {
        $this->startTrans();
        try {
            $order = $this->max('order');

            $area = $this->create([
                'first_area' => $param['first_area'],
                'second_area' => $param['second_area'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_physical_server_area', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['first_area'].'-'.$param['second_area']]), 'physical_server_area', $area->id);

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
     * @title 编辑物理服务器区域
     * @desc 编辑物理服务器区域
     * @author theworld
     * @version v1
     * @param int param.id - 区域ID required
     * @param string param.first_area - 一级区域 required
     * @param string param.second_area - 二级区域 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateArea($param)
    {
        // 验证区域ID
        $area = $this->find($param['id']);
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('physical_server_area_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'first_area' => $param['first_area'],
                'second_area' => $param['second_area'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'first_area'    => lang('physical_server_area_first_area'),
                'second_area'   => lang('physical_server_area_second_area'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $area[$k] != $param[$k]){
                    $old = $area[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_physical_server_area', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $area['first_area'].'-'.$area['second_area'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'physical_server_area', $area->id);
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
     * @title 删除物理服务器区域
     * @desc 删除物理服务器区域
     * @author theworld
     * @version v1
     * @param int id - 区域ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteArea($id)
    {
        // 验证区域ID
        $area = $this->find($id);
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('physical_server_area_not_exist')];
        }

        $PhysicalServerProductModel = new PhysicalServerProductModel();
        $count = $PhysicalServerProductModel->where('area_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang('physical_server_area_used_cannot_delete')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_physical_server_area', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $area['first_area'].'-'.$area['second_area']]), 'physical_server_area', $area->id);
            
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
     * @title 物理服务器区域排序
     * @desc 物理服务器区域排序
     * @author theworld
     * @version v1
     * @param array param.id - 区域ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function areaOrder($param)
    {
        $id = $param['id'] ?? [];
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $area = $this->column('id');
        if(count($id)!=count($area) || count($id)!=count(array_intersect($area, $id))){
            return ['status'=>400, 'msg'=>lang('physical_server_area_not_exist')];
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
            active_log(lang('log_order_physical_server_area', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];
    }
}
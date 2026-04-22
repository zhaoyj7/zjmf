<?php
namespace app\admin\model;

use think\Model;

/**
 * @title 商品周期分组预设模型
 * @desc 商品周期分组预设模型
 * @use app\admin\model\ProductDurationGroupPresetsModel
 */
class ProductDurationGroupPresetsModel extends Model
{
    protected $name = 'product_duration_group_presets';

    // 设置字段信息
    protected $schema = [
        'id'           => 'int',
        'name'         => 'string',
        'ratio_open'   => 'int',
        'create_time'  => 'int',
        'update_time'  => 'int',

    ];

    /**
     * 时间 2024-10-23
     * @title 预设列表
     * @desc 预设列表
     * @return array list - 预设列表
     * @return int list[].id - 分组id
     * @return string list[].name - 分组名称
     * @return array list[].duration_info - 周期信息
     * @return string list[].duration_info[].name - 周期名称
     * @return int list[].duration_info[].num - 周期时长
     * @return string list[].duration_info[].unit - 周期单位(hour=小时,day=天,month=自然月)
     * @return int list[].ratio_open - 是否开启周期比例
     * @return array list[].ration_info - 周期比例信息
     * @return string list[].ration_info[].name - 周期名称
     * @return float list[].ration_info[].ratio - 周期比例
     * @return int count - 预设总数
     * @author wyh
     * @version v1
     */
    public function presetsList(array $param)
    {
        $groupPresets = $this->alias('pdgp')
            ->select()
            ->toArray();
        $ProductDurationPresetsModel = new ProductDurationPresetsModel();
        foreach ($groupPresets as &$groupPreset){
            $durations = $ProductDurationPresetsModel->where('gid',$groupPreset['id'])
                ->select()
                ->toArray();
            $groupPreset['duration_info'] = $durations;
            $groupPreset['ration_info'] = $durations;
        }
        return ['list'=>$groupPresets,'count'=>count($groupPresets)];
    }

    /**
     * 时间 2024-10-23
     * @title 获取周期预设信息
     * @desc 获取周期预设信息
     * @author wyh
     * @version v1
     * @param int id - 周期分组预设ID required
     * @return object presets - 预设信息
     * @return int presets.id - 分组预设ID
     * @return string presets.name - 分组名称
     * @return int presets.ratio_open -
     * @return array presets.durations - 周期信息
     * @return array presets.durations[].id - 周期ID
     * @return string presets.durations[].name - 周期名称
     * @return int presets.durations[].num - 周期时长
     * @return string presets.durations[].unit - 周期单位(hour=小时,day=天,month=自然月
     * @return float presets.durations[].ratio - 周期比例
     */
    public function indexPresets(int $intval)
    {
        $groupPresets = $this->find($intval);

        $ProductDurationPresetsModel = new ProductDurationPresetsModel();

        $groupPresets['durations'] = $ProductDurationPresetsModel->where('gid',$groupPresets->id)->select()->toArray();

        return $groupPresets;
    }

    /**
     * 时间 2024-10-23
     * @title 新建周期配置组
     * @desc 新建周期配置组
     * @author wyh
     * @version v1
     * @param string name - 分组名称 required
     * @param int ratio_open - 周期比例开关(0=关,1=开) required
     * @param array durations - 周期信息 required
     * @param string durations[].name - 周期名称 required
     * @param int durations[].num - 周期时长 required
     * @param string durations[].unit - 周期单位(hour=小时,day=天,month=自然月) required
     * @param float durations[].ratio - 周期比例，可默认传0 required
     */
    public function createPresets($param)
    {
        $this->startTrans();
        try{
            $groupPresets = $this->create([
                'name' => $param['name']??'',
                'ratio_open' => (int)$param['ratio_open'],
                'create_time' => time(),
            ]);
            $ProductDurationPresetsModel = new ProductDurationPresetsModel();
            foreach ($param['durations'] as $value){
                $ProductDurationPresetsModel->create([
                    'gid' => $groupPresets->id,
                    'name' => $value['name']??'',
                    'num' => (int)$value['num'],
                    'unit' => $value['unit']??'',
                    'ratio' => $param['ratio_open']?(float)$value['ratio']:0,
                    'create_time' => time(),
                ]);
            }

            # 记录日志
            active_log(lang('log_create_group_presets',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name']]),'product_duration_group_presets',$groupPresets->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }
        return ['status'=>200,'msg'=>lang('create_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 修改周期配置组
     * @desc 修改周期配置组
     * @param int id 1 周期分组预设ID required
     * @param string name - 分组名称 required
     * @param int ratio_open - 周期比例开关(0=关,1=开) required
     * @param array durations - 周期信息 required
     * @param string durations[].name - 周期名称 required
     * @param int durations[].num - 周期时长 required
     * @param string durations[].unit - 周期单位(hour=小时,day=天,month=自然月) required
     * @param float durations[].ratio - 周期比例，可默认传0 required
     * @author wyh
     * @version v1
     */
    public function updatePresets($param)
    {
        $this->startTrans();
        try{
            $groupPresets = $this->find($param['id']);

            $groupPresets->save([
                'name' => $param['name'],
                'update_time' => time(),
                'ratio_open' => (int)$param['ratio_open'],
            ]);

            $ProductDurationPresetsModel = new ProductDurationPresetsModel();
            // 先删除
            $ProductDurationPresetsModel->where('gid',$groupPresets->id)->delete();
            // 再新增
            foreach ($param['durations'] as $value){
                $ProductDurationPresetsModel->create([
                    'gid' => $groupPresets->id,
                    'name' => $value['name']??'',
                    'num' => (int)$value['num'],
                    'unit' => $value['unit']??'',
                    'ratio' => (float)$value['ratio'],
                    'create_time' => time(),
                ]);
            }

            # 记录日志
            active_log(lang('log_create_group_presets_update',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name']]),'product_duration_group_presets',$groupPresets->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }
        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 删除周期配置组
     * @desc 删除周期配置组
     * @author wyh
     * @version v1
     * @param int id 1 周期分组预设ID required
     */
    public function deletePresets($param)
    {
        $this->startTrans();
        try{
            $groupPresets = $this->where('id',$param['id'])->find();

            $this->where('id',$param['id'])->delete();

            $ProductDurationPresetsModel = new ProductDurationPresetsModel();

            $ProductDurationPresetsModel->where('gid',$param['id'])->delete();

            // 删除关联接口
            $ProductDurationGroupPresetsLinkModel = new ProductDurationGroupPresetsLinkModel();
            $ProductDurationGroupPresetsLinkModel->where('gid',$param['id'])->delete();
            # 记录日志
            active_log(lang('log_create_group_presets_delete',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$groupPresets['name']]),'product_duration_group_presets',$groupPresets->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail').$e->getMessage()];
        }
        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 周期配置组复制
     * @desc 周期配置组复制
     * @author wyh
     * @version v1
     * @param int id 1 周期分组预设ID required
     */
    public function copyPresets($param)
    {
        $this->startTrans();
        try{
            $groupPresets = $this->where('id',$param['id'])->find();

            $ProductDurationPresetsModel = new ProductDurationPresetsModel();

            $durations = $ProductDurationPresetsModel->where('gid',$param['id'])->select();

            $newGid = $this->insertGetId([
                'name' => $groupPresets['name'],
                'ratio_open' => $groupPresets['ratio_open'],
                'create_time' => time(),
            ]);

            foreach ($durations as $duration){
                $ProductDurationPresetsModel->create([
                    'gid' => $newGid,
                    'name' => $duration['name'],
                    'num' => $duration['num'],
                    'unit' => $duration['unit'],
                    'ratio' => $duration['ratio'],
                    'create_time' => time(),
                ]);
            }

            # 记录日志
            active_log(lang('log_create_group_presets_copy',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$groupPresets['name']]),'product_duration_group_presets',$newGid);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('copy_fail').$e->getMessage()];
        }
        return ['status'=>200,'msg'=>lang('copy_success')];
    }

}
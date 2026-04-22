<?php
namespace app\admin\model;

use app\common\model\ServerModel;
use think\db\Query;
use think\Model;

/**
 * @title 商品周期预设分组关联模型
 * @desc 商品周期预设分组关联模型
 * @use app\admin\model\ProductDurationGroupPresetsLinkModel
 */
class ProductDurationGroupPresetsLinkModel extends Model
{
    protected $name = 'product_duration_group_presets_link';

    // 设置字段信息
    protected $schema = [
        'id'           => 'int',
        'server_id'    => 'int',
        'gid'          => 'int',
    ];

    /**
     * 时间 2024-10-23
     * @title 关联列表
     * @desc 关联列表
     * @param string keywords - 关键字,搜索范围:分组名称,接口名称
     * @return array list - 关联列表
     * @return int list[].name - 分组名称
     * @return int list[].gid - 分组ID
     * @return array list[].servers - 接口
     * @return int list[].servers[].server_id - 接口ID
     * @return string list[].servers[].server_name - 接口名称
     * @return int count - 预设总数
     * @author wyh
     * @version v1
     */
    public function linkList(array $param)
    {
        $where = function (Query $query) use ($param){
            if (!empty($param['keywords'])){
                $query->where('s.name','like',"%{$param['keywords']}%")
                    ->whereOr('pdgp.name','like',"%{$param['keywords']}%");
            }
        };
        $links = $this->alias('pdgpl')
            ->field('pdgpl.gid,pdgp.name,group_concat(pdgpl.server_id) as server_ids')
            ->leftjoin('product_duration_group_presets pdgp', 'pdgp.id=pdgpl.gid')
            ->group('pdgpl.gid')
            ->select()
            ->toArray();
        $ServerModel = new ServerModel();
        foreach ($links as &$link){
            $link['servers'] = $ServerModel->field('id server_id,name server_name')->whereIn('id',$link['server_ids'])
                ->select()
                ->toArray();
            unset($link['server_ids']);
        }
        return [
            'list' => $links,
            'count' => count($links)
        ];
    }

    /**
     * 时间 2024-10-23
     * @title 新建周期配置组关联
     * @desc 新建周期配置组关联
     * @author wyh
     * @version v1
     * @param array server_ids - 接口ID数组 required
     * @param int gid - 分组ID required
     */
    public function creatLink($param)
    {
        $this->startTrans();
        try{
            $oldServerIds = $this->where('gid',$param['gid'])->column('server_id');
            $this->where('gid',$param['gid'])->delete();
            $param['server_ids'] = array_unique(array_merge($oldServerIds,$param['server_ids']));
            $insertAll = [];
            foreach ($param['server_ids'] as $server_id){
                $link = $this->where('server_id',$server_id)
                    ->where('gid','<>',$param['gid'])
                    ->find();
                if ($link){
                    $server = ServerModel::where('id',$server_id)->find();
                    return ['status'=>400,'msg'=>lang('product_duration_group_presets_link_server_exist',['{name}'=>$server['name']])];
                }
                $insertAll[] = [
                    'server_id' => $server_id,
                    'gid' => $param['gid']
                ];
            }
            $this->insertAll($insertAll);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }
        return ['status'=>200,'msg'=>lang('create_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 编辑周期配置组关联
     * @desc 编辑周期配置组关联
     * @author wyh
     * @version v1
     * @param array server_ids - 接口ID数组 required
     * @param int gid - 分组ID required
     */
    public function updateLink($param)
    {
        $this->startTrans();
        try{
            $insertAll = [];
            // 新组里面有关联的接口
            $newServerIds = $this->where('gid',$param['gid'])->column('server_id');
            $param['server_ids'] = array_unique(array_merge($newServerIds,$param['server_ids']));
            $this->where('gid',$param['gid'])->delete();
            $this->where('gid',$param['id'])->delete();
            foreach ($param['server_ids'] as $server_id){
                // 接口在其他分组
                $link = $this->where('server_id',$server_id)
                    ->where('gid','<>',$param['id'])
                    ->where('gid','<>',$param['gid'])
                    ->find();
                if ($link){
                    $server = ServerModel::where('id',$server_id)->find();
                    return ['status'=>400,'msg'=>lang('product_duration_group_presets_link_server_exist',['{name}'=>$server['name']])];
                }
                $insertAll[] = [
                    'server_id' => $server_id,
                    'gid' => $param['gid']
                ];
            }
            $this->insertAll($insertAll);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }
        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2024-10-23
     * @title 删除周期配置组关联
     * @desc 删除周期配置组关联
     * @author wyh
     * @version v1
     * @param int gid 1 周期分组预设ID required
     */
    public function deleteLink($param)
    {
        $this->startTrans();
        try{
            $this->where('gid',$param['gid'])->delete();
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }
        return ['status'=>200,'msg'=>lang('delete_success')];
    }

}
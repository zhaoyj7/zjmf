<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\db\Query;

/**
 * @title 同步镜像日志模型
 * @desc 同步镜像日志模型
 * @use app\common\model\SyncImageLogModel
 */
class SyncImageLogModel extends Model
{
	protected $name = 'sync_image_log';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'result'        => 'string',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2024-10-24
     * @title 同步镜像日志列表
     * @desc 同步镜像日志列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,create_time
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 同步镜像日志
     * @return int list[].id - 同步镜像日志ID
     * @return int list[].product_id - 商品ID 
     * @return string list[].name - 商品名称
     * @return string list[].result - 同步结果 
     * @return int list[].create_time - 同步时间
     * @return int count - 同步镜像日志总数
     */
    public function logList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','create_time']) ? 'a.'.$param['orderby'] : 'a.id';

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('p.name', 'like', "%{$param['keywords']}%");
            }
        };

        $count = $this->alias('a')
            ->field('a.id')
            ->leftjoin('product p', 'p.id=a.product_id')
            ->where($where)
            ->count();
        $logs = $this->alias('a')
            ->field('a.id,a.product_id,p.name,a.result,a.create_time')
            ->leftjoin('product p', 'p.id=a.product_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        return ['list' => $logs, 'count' => $count];
    }

    /**
     * 时间 2024-10-24
     * @title 同步镜像
     * @desc 同步镜像
     * @author theworld
     * @version v1
     * @param array param.product_id - 商品ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function syncImage($param)
    {
        $param['product_id'] = $param['product_id'] ?? [];
        
        $ProductModel = new ProductModel();
        $product = $ProductModel->alias('p')
            ->field('p.id,s.module module1,ss.module module2')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->whereIn('s.module|ss.module', ['mf_cloud', 'mf_dcim'])
            ->whereIn('p.id', $param['product_id'])
            ->select()
            ->toArray();
        if(count($product)!=count($param['product_id'])){
            return ['status' => 400, 'msg' => lang('only_cloud_dcim_can_sync_image')];
        }

        foreach ($product as $key => $value) {
            if($value['module1']=='mf_cloud' || $value['module2']=='mf_cloud'){
                $ConfigModel = new \server\mf_cloud\model\ConfigModel();
                $config = $ConfigModel->where('product_id', $value['id'])->find();
                if($config['manual_manage']==1){
                    continue;
                }
                $result = \server\mf_cloud\logic\ImageLogic::getProductImage($value['id']);
            }else{
                $ConfigModel = new \server\mf_dcim\model\ConfigModel();
                $config = $ConfigModel->where('product_id', $value['id'])->find();
                if($config['manual_resource']==1){
                    continue;
                }
                $ImageModel = new \server\mf_dcim\model\ImageModel();
                $result = $ImageModel->imageSync($value['id']);
            }
            $this->create([
                'product_id'    => $value['id'],
                'result'        => $result['status']=='200' ? lang('sync_success') : lang('sync_failed'),
                'create_time'   => time(),
            ]);

        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }
}   

<?php
namespace app\common\model;

use think\Model;

/**
 * @title 自定义产品标识关联模型
 * @desc 自定义产品标识关联模型
 * @use app\common\model\CustomHostNameLinkModel
 */
class CustomHostNameLinkModel extends Model
{
    protected $name = 'custom_host_name_link';

    // 设置字段信息
    protected $schema = [
        'custom_host_name_id'   => 'int',
        'product_group_id'      => 'int',
    ];

    /**
     * 时间 2024-10-23
     * @title 关联商品组
     * @desc 关联商品组
     * @author theworld
     * @version v1
     * @param   int param.id - 自定义产品标识ID require
     * @param   int param.product_group_id - 二级商品分组ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function relatedProductGroup($param)
    {
        $CustomHostNameModel = new CustomHostNameModel();
        $customHostName = $CustomHostNameModel->find($param['id']);
        if(empty($customHostName)){
            return ['status'=>400, 'msg'=>lang('custom_host_name_not_found')];
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->whereIn('id', $param['product_group_id'])->where('parent_id', '>', 0)->select()->toArray();
        if(count($productGroup)!=count($param['product_group_id'])){
            return ['status'=>400, 'msg'=>lang('please_select_product_group_second')];
        }

        $count = $this->where('custom_host_name_id', '<>', $param['id'])->whereIn('product_group_id', $param['product_group_id'])->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang('product_group_has_been_associated_with_custom_host_name')];
        }

        $this->startTrans();
        try{
            $this->where('custom_host_name_id', $param['id'])->delete();
            $arr = [];
            foreach ($param['product_group_id'] as $v) {
                $arr[] = ['custom_host_name_id' => $param['id'], 'product_group_id' => $v];
            }
            $this->saveAll($arr);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }
}
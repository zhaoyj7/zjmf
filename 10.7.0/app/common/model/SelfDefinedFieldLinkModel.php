<?php
namespace app\common\model;

use think\Model;

/**
 * @title 自定义字段关联模型
 * @desc 自定义字段关联模型
 * @use app\common\model\SelfDefinedFieldLinkModel
 */
class SelfDefinedFieldLinkModel extends Model
{
    protected $name = 'self_defined_field_link';

    // 设置字段信息
    protected $schema = [
        'self_defined_field_id'     => 'int',
        'product_group_id'          => 'int',
    ];

    /**
     * 时间 2024-10-23
     * @title 关联商品组
     * @desc 关联商品组
     * @author theworld
     * @version v1
     * @param   int param.id - 自定义字段ID(仅限类型为product_group) require
     * @param   array param.product_group_id - 二级商品分组ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function relatedProductGroup($param)
    {
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $selfDefinedField = $SelfDefinedFieldModel->find($param['id']);
        if(empty($selfDefinedField)){
            return ['status'=>400, 'msg'=>lang('self_defined_field_not_found')];
        }
        if($selfDefinedField['type'] != 'product_group'){
            return ['status'=>400, 'msg'=>lang('self_defined_field_not_found')];
        }

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->whereIn('id', $param['product_group_id'])->where('parent_id', '>', 0)->select()->toArray();
        if(count($productGroup)!=count($param['product_group_id'])){
            return ['status'=>400, 'msg'=>lang('please_select_product_group_second')];
        }

        $this->startTrans();
        try{
            $this->where('self_defined_field_id', $param['id'])->delete();
            $arr = [];
            foreach ($param['product_group_id'] as $v) {
                $arr[] = ['self_defined_field_id' => $param['id'], 'product_group_id' => $v];
            }
            $this->saveAll($arr);

            if(empty($selfDefinedField['is_global'])){
                $productIds = ProductModel::whereIn('product_group_id', $param['product_group_id'])->column('id');

                $exists = $SelfDefinedFieldModel->where('type', 'product')->whereIn('relid', $productIds)->where('field_name', $selfDefinedField['field_name'])->column('relid');

                $arr = [];
                $time = time();
                foreach ($productIds as $v) {
                    if(!in_array($v, $exists)){
                        $arr[] = [
                            'type' => 'product',
                            'relid' => $v,
                            'field_name' => $selfDefinedField['field_name'],
                            'is_required' => $selfDefinedField['is_required'],
                            'field_type' => $selfDefinedField['field_type'],
                            'description' => $selfDefinedField['description'],
                            'regexpr' => $selfDefinedField['regexpr'],
                            'field_option' => $selfDefinedField['field_option'],
                            'show_order_page' => $selfDefinedField['show_order_page'],
                            'show_order_detail' => $selfDefinedField['show_order_detail'],
                            'show_client_host_detail' => $selfDefinedField['show_client_host_detail'],
                            'show_admin_host_detail' => $selfDefinedField['show_admin_host_detail'],
                            'show_client_host_list' => $selfDefinedField['show_client_host_list'],
                            'show_admin_host_list' => $selfDefinedField['show_admin_host_list'],
                            'create_time' => $time,
                            'explain_content' => $selfDefinedField['explain_content'],
                        ];
                    }
                }

                $SelfDefinedFieldModel->saveAll($arr);
            }

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
<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title 配置价格模型
 * @use server\mf_cloud\model\PriceModel
 */
class PriceModel extends Model
{
	protected $name = 'module_mf_cloud_price';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rel_type'     	=> 'int',
        'rel_id'     	=> 'int',
        'duration_id'   => 'int',
        'price'         => 'float',
    ];

    // rel_type常量
    const REL_TYPE_OPTION = 0;           // 关联option
    const REL_TYPE_RECOMMEND_CONFIG = 1; // 关联套餐
    
    /**
     * @时间 2025-03-21
     * @title 获取商品按需周期
     * @desc  获取商品按需周期
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.duration_id - 周期ID require
     * @param   int param.product_id - 商品ID require
     * @param   array param.option_id - 通用配置ID require
     * @return  array
     * @return  int [rel_id].price - 对应配置价格
     */
    public function optionDurationPrice($param): array
    {
        $data = $this
            ->alias('p')
            ->field('p.rel_id,p.price')
            ->where('p.duration_id', $param['duration_id'])
            ->where('p.product_id', $param['product_id'])
            ->whereIn('p.rel_id', $param['option_id'])
            ->where('p.rel_type', PriceModel::REL_TYPE_OPTION)
            ->select()
            ->toArray();
        $data = array_column($data, 'price', 'rel_id');
        return $data;
    }

}
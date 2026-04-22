<?php

namespace app\common\model;

use think\Model;

/**
 * @title 魔方云区域组关联模型
 * @desc  魔方云区域组关联模型
 * @use app\common\model\MfCloudDataCenterMapGroupLinkModel
 */
class MfCloudDataCenterMapGroupLinkModel extends Model
{
    protected $name = 'mf_cloud_data_center_map_group_link';

    protected $pk = 'mf_cloud_data_center_map_group_id';

    // 设置字段信息
    protected $schema = [
        'mf_cloud_data_center_map_group_id'          => 'int',
        'product_id'        => 'int',
        'data_center_id'    => 'int',
    ];

    public function getGroupId($productId, $dataCenterId): int
    {
        $groupId = $this
                ->where('product_id', $productId)
                ->where('data_center_id', $dataCenterId)
                ->value('mf_cloud_data_center_map_group_id');
        return (int)$groupId;
    }

    /**
     * 时间 2023-02-13
     * @title 获取关联的商品和数据中心ID
     * @desc  根据商品数据中心获取关联的商品和数据中心ID
     * @author hh
     * @version v1
     * @param  array param - 参数 require
     * @param  int param.data_center_id - 数据中心ID require
     * @param  int param.downstream_client_id - 下游用户ID(api对接可用)
     * @return int list[].id - VPC网络ID
     * @return string list[].name - VPC网络名称
     */
    public function getGroupProductDataCenterId($productId, $dataCenterId): array
    {
        $groupId = $this->getGroupId($productId, $dataCenterId);
        if(empty($groupId)){
            return [
                [$productId],
                [$dataCenterId]
            ];
        }
        $productIds = $this
                    ->where('mf_cloud_data_center_map_group_id', $groupId)
                    ->column('product_id');
        
        $dataCenterIds = $this
                    ->where('mf_cloud_data_center_map_group_id', $groupId)
                    ->where('data_center_id');
        return [$productIds, $dataCenterIds];
    }

}
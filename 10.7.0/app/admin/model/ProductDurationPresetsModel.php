<?php
namespace app\admin\model;

use think\Model;

/**
 * @title 商品周期预设模型
 * @desc 商品周期预设模型
 * @use app\admin\model\ProductDurationPresetsModel
 */
class ProductDurationPresetsModel extends Model
{
    protected $name = 'product_duration_presets';

    // 设置字段信息
    protected $schema = [
        'id'           => 'int',
        'gid'          => 'int',
        'name'         => 'string',
        'num'          => 'int',
        'unit'         => 'string',
        'ratio'        => 'float',
        'create_time'  => 'int',
        'update_time'  => 'int',
    ];

}
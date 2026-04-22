<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\facade\Db;
use think\Model;

/**
 * @title 商品通知组模型
 * @desc  商品通知组模型
 * @use app\common\model\ProductNoticeGroupProductModel
 */
class ProductNoticeGroupProductModel extends Model
{
    protected $name = 'product_notice_group_product';

    // 设置字段信息
    protected $schema = [
        'product_notice_group_id'   => 'int',
        'type'                      => 'string',
        'product_id'                => 'int',
    ];

    




}
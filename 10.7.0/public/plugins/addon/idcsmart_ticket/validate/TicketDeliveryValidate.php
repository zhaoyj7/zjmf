<?php

namespace addon\idcsmart_ticket\validate;

use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use app\common\model\ProductModel;
use think\Validate;

/**
 * @title 工单类型验证
 * @description 接口说明:工单类型验证
 */
class TicketDeliveryValidate extends Validate
{
    protected $rule = [
        'id'                      => 'require',
        'ticket_type_id'          => 'require|checkTicketTypeId:thinkphp',
        'product_ids'             => 'require|array|checkProductIds:thinkphp',
        'product_id'              => 'require|checkProductId:thinkphp',
    ];

    protected $message = [

    ];

    protected $scene = [
        'create'               => ['ticket_type_id','product_ids'],
        'update'               => ['id','ticket_type_id','product_id'],
    ];

    protected function checkTicketTypeId($value)
    {
        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $exist = $IdcsmartTicketTypeModel->find($value);

        if (empty($exist)){
            return 'ticket_type_is_not_exist';
        }

        return true;
    }

    protected function checkProductIds($value)
    {
        $ProductModel = new ProductModel();
        foreach ($value as $item){
            $product = $ProductModel->find($item);
            if (empty($product)){
                return 'product_is_not_exist';
            }
        }

        return true;
    }

    protected function checkProductId($value)
    {
        $ProductModel = new ProductModel();

        $product = $ProductModel->find($value);

        if (empty($product)){
            return 'product_is_not_exist';
        }

        return true;
    }


}
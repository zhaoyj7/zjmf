<?php
namespace app\admin\model;

use think\Model;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\common\model\CountryModel;
use addon\idcsmart_client_level\model\IdcsmartClientLevelModel;
use addon\client_custom_field\model\ClientCustomFieldModel;
use addon\idcsmart_sale\model\IdcsmartSaleModel;

/**
 * @title 管理员视图模型
 * @desc  管理员视图模型
 * @use app\admin\model\AdminViewModel
 */
class AdminViewModel extends Model
{
    protected $name = 'admin_view';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'name'              => 'string',
        'view'              => 'string',
        'default'           => 'int',
        'choose'            => 'int',
        'last_visit'        => 'int',       
        'admin_id'          => 'int',
        'select_field'      => 'string',
        'data_range_switch' => 'int',
        'select_data_range' => 'string',
        'order'             => 'int',
        'status'            => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    // 所有字段
    protected $setting = [
        'client' => [
            [
                'name'  => 'admin_field_client_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_username_and_company',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [ // 仅记录,方便看是哪个插件的
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'oauth',
                        'name'  => 'admin_field_oauth',
                    ],
                    [
                        'key'   => 'mp_weixin_notice',
                        'name'  => 'admin_field_mp_weixin_notice',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'MpWeixinNotice',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                ], 
            ],
            [
                'name'  => 'admin_field_host_about',
                'field' => [
                    [
                        'key'   => 'host_active_num_host_num',
                        'name'  => 'admin_field_host_active_num_and_host_num',
                    ],
                    [
                        'key'   => 'client_credit',
                        'name'  => 'admin_field_client_credit',
                    ],
                    [
                        'key'   => 'cost_price',
                        'name'  => 'admin_field_cost_price',
                    ],
                    [
                        'key'   => 'refund_price',
                        'name'  => 'admin_field_refund_price',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartRefund',
                        ],
                    ],
                    [
                        'key'   => 'withdraw_price',
                        'name'  => 'admin_field_withdraw_price',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartWithdraw',
                        ],
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                    ],
                ],
            ],
        ],
        'order' => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_order_id',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'product_name',
                        'name'  => 'admin_field_product_name',
                    ],
                    [
                        'key'   => 'order_amount',
                        'name'  => 'admin_field_order_amount',
                    ],
                    [
                        'key'   => 'gateway',
                        'name'  => 'admin_field_gateway',
                    ],
                    [
                        'key'   => 'pay_time',
                        'name'  => 'admin_field_pay_time',
                    ],
                    [
                        'key'   => 'order_time',
                        'name'  => 'admin_field_order_create_time',
                    ],
                    [
                        'key'   => 'order_status',
                        'name'  => 'admin_field_order_status',
                    ],
                    [
                        'key'   => 'order_type',
                        'name'  => 'admin_field_order_type',
                    ],
                    [
                        'key'   => 'order_use_credit',
                        'name'  => 'admin_field_order_use_credit',
                    ],
                    [
                        'key'   => 'order_refund_amount',
                        'name'  => 'admin_field_order_refund_amount',
                    ],
                    [
                        'key'   => 'order_invoice_status',
                        'name'  => 'admin_field_order_invoice_status',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartInvoice',
                        ],
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'oauth',
                        'name'  => 'admin_field_oauth',
                    ],
                    [
                        'key'   => 'mp_weixin_notice',
                        'name'  => 'admin_field_mp_weixin_notice',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'MpWeixinNotice',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                    ],
                ],
            ],
        ],
        'host'  => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_host_id',
                    ],
                    [
                        'key'   => 'product_name_status',
                        'name'  => 'admin_field_product_name_and_host_status',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'ip',
                        'name'  => 'IP',
                    ],
                    [
                        'key'   => 'host_name',
                        'name'  => 'admin_field_host_name',
                    ],
                    [
                        'key'   => 'renew_amount_cycle',
                        'name'  => 'admin_field_host_renew_amount_cycle',
                    ],
                    [
                        'key'   => 'due_time',
                        'name'  => 'admin_field_due_time',
                    ],
                    [
                        'key'   => 'server_name',
                        'name'  => 'admin_field_product_interface',
                    ],
                    [
                        'key'   => 'admin_notes',
                        'name'  => 'admin_field_admin_notes',
                    ],
                    [
                        'key'   => 'first_payment_amount',
                        'name'  => 'admin_field_first_payment_amount',
                    ],
                    [
                        'key'   => 'billing_cycle_name',
                        'name'  => 'admin_field_billing_cycle_name',
                    ],
                    [
                        'key'   => 'base_price',
                        'name'  => 'admin_field_base_price',
                    ],
                    [
                        'key'   => 'billing_cycle',
                        'name'  => 'admin_field_billing_cycle',
                    ],
                    [
                        'key'   => 'active_time',
                        'name'  => 'admin_field_active_time',
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'oauth',
                        'name'  => 'admin_field_oauth',
                    ],
                    [
                        'key'   => 'mp_weixin_notice',
                        'name'  => 'admin_field_mp_weixin_notice',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'MpWeixinNotice',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                    ],
                ],
            ],
        ],
        'transaction'   => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_transaction_id',
                    ],
                    [
                        'key'   => 'amount',
                        'name'  => 'admin_field_transaction_amount',
                    ],
                    [
                        'key'   => 'gateway',
                        'name'  => 'admin_field_gateway',
                    ],
                    [
                        'key'   => 'payment_channel',
                        'name'  => 'admin_field_payment_channel',
                    ],
                    [
                        'key'   => 'username_company',
                        'name'  => 'admin_field_client_and_company',
                    ],
                    [
                        'key'   => 'transaction_number',
                        'name'  => 'admin_field_transaction_number',
                    ],
                    [
                        'key'   => 'order_id',
                        'name'  => 'admin_field_link_order_id',
                    ],
                    [
                        'key'   => 'order_type',
                        'name'  => 'admin_field_order_type',
                    ],
                    [
                        'key'   => 'transaction_time',
                        'name'  => 'admin_field_transaction_time',
                    ],
                    [
                        'key'   => 'transaction_notes',
                        'name'  => 'admin_field_transaction_notes',
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                    ],
                    [
                        'key'   => 'oauth',
                        'name'  => 'admin_field_oauth',
                    ],
                    [
                        'key'   => 'mp_weixin_notice',
                        'name'  => 'admin_field_mp_weixin_notice',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'MpWeixinNotice',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                    ],
                ],
            ],
        ],
    ];

    protected $dataRange = [
        'host'  => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_host_id',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'product_name',
                        'name'  => 'admin_field_product_name',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'host_status',
                        'name'  => 'admin_field_host_status',
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id' => 'Unpaid'],
                            ['id' => 'Pending'],
                            ['id' => 'Active'],
                            ['id' => 'Suspended'],
                            ['id' => 'Deleted'],
                            ['id' => 'Failed'],
                            ['id' => 'Cancelled'],
                            ['id' => 'Grace'],
                            ['id' => 'Keep'],
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'ip',
                        'name'  => 'IP',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'host_name',
                        'name'  => 'admin_field_host_name',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'renew_amount',
                        'name'  => 'admin_field_host_renew_amount',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'due_time',
                        'name'  => 'admin_field_due_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'server_name',
                        'name'  => 'admin_field_product_interface',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'admin_notes',
                        'name'  => 'admin_field_admin_notes',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'first_payment_amount',
                        'name'  => 'admin_field_first_payment_amount',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'billing_cycle_name',
                        'name'  => 'admin_field_billing_cycle_name',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'base_price',
                        'name'  => 'admin_field_base_price',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'billing_cycle',
                        'name'  => 'admin_field_billing_cycle',
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id' => 'free'],
                            ['id' => 'onetime'],
                            ['id' => 'recurring_prepayment'],
                            ['id' => 'recurring_postpaid']
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'active_time',
                        'name'  => 'admin_field_active_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'username',
                        'name'  => 'admin_field_client_username',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'company',
                        'name'  => 'admin_field_client_company',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id' => ''],
                            ['id' => 'person'],
                            ['id' => 'company']
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                        'type'  => 'select',
                        'option'  => [
                            ['id' => 0], 
                            ['id' => 1]
                        ],
                        'rule'  => [
                            'equal',
                        ],
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                ],
            ],
        ],
        'order' => [
            [
                'name'  => 'admin_field_base_info',
                'field' => [
                    [
                        'key'   => 'id',
                        'name'  => 'admin_field_order_id',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'username',
                        'name'  => 'admin_field_client_username',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'company',
                        'name'  => 'admin_field_client_company',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'product_name',
                        'name'  => 'admin_field_product_name',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'order_amount',
                        'name'  => 'admin_field_order_amount',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'gateway',
                        'name'  => 'admin_field_gateway',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'pay_time',
                        'name'  => 'admin_field_pay_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'order_time',
                        'name'  => 'admin_field_order_create_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'order_status',
                        'name'  => 'admin_field_order_status',
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id'=>'Unpaid'],
                            ['id'=>'Paid'],
                            ['id'=>'Cancelled'],
                            ['id'=>'Refunded'],
                            ['id'=>'WaitUpload'],
                            ['id'=>'WaitReview'],
                            ['id'=>'ReviewFail'],
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'order_type',
                        'name'  => 'admin_field_order_type',
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id'=>'new'],
                            ['id'=>'renew'],
                            ['id'=>'upgrade'],
                            ['id'=>'artificial'],
                            ['id'=>'recharge'],
                            ['id'=>'on_demand'],
                            ['id'=>'change_billing_cycle'],
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'order_use_credit',
                        'name'  => 'admin_field_order_use_credit',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'order_refund_amount',
                        'name'  => 'admin_field_order_refund_amount',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'order_invoice_status',
                        'name'  => 'admin_field_order_invoice_status',
                        'type'  => 'select',
                        'option'  => [
                            ['id'=>'not_create'],
                            ['id'=>'created'],
                        ],
                        'rule'  => [
                            'equal',
                        ],
                    ],
                ],
            ],
            [
                'name'  => 'admin_field_client_about',
                'field' => [
                    [
                        'key'   => 'client_id',
                        'name'  => 'admin_field_client_id',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'certification',
                        'name'  => 'admin_field_client_certification',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartCertification',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [
                            ['id' => ''],
                            ['id' => 'person'],
                            ['id' => 'company']
                        ],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'phone',
                        'name'  => 'admin_field_client_phone',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'email',
                        'name'  => 'admin_field_client_email',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'client_status',
                        'name'  => 'admin_field_order_client_status',
                        'type'  => 'select',
                        'option'  => [
                            ['id' => 0], 
                            ['id' => 1]
                        ],
                        'rule'  => [
                            'equal',
                        ],
                    ],
                    [
                        'key'   => 'client_level',
                        'name'  => 'admin_field_client_level',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartClientLevel',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'reg_time',
                        'name'  => 'admin_field_client_reg_time',
                        'type'  => 'date',
                        'rule'  => [
                            'equal',
                            'interval',
                            'dynamic',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'country',
                        'name'  => 'admin_field_country',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'address',
                        'name'  => 'admin_field_address',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'language',
                        'name'  => 'admin_field_language',
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                        ],
                    ],
                    [
                        'key'   => 'notes',
                        'name'  => 'admin_field_notes',
                        'type'  => 'input',
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ],
                    ],
                    [
                        'key'   => 'sale',
                        'name'  => 'admin_field_sale',
                        'module'=> [
                            'type'  => 'addon',
                            'name'  => 'IdcsmartSale',
                        ],
                        'type'  => 'multi_select',
                        'option'  => [],
                        'rule'  => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ],
                    ],
                ],
            ],
        ],

    ]; 

    // 当前已激活插件
    protected $plugin = [];

    /**
     * 时间 2024-05-08
     * @title 获取字段设置可选字段
     * @desc  获取字段设置可选字段
     * @author theworld
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   bool getDataRangeOption - 获取数据范围选项true是false否
     * @return  string field[].name - 字段分组名称
     * @return  string field[].field[].key - 字段标识
     * @return  string field[].field[].name - 字段名称
     */
    public function enableField($view, $getDataRangeOption = false)
    {
        $this->getActivePlugin();
        $field = $this->setting[$view] ?? [];
        $dataRange = $this->dataRange[$view] ?? [];
        $lang = lang();
        $passwordField = [];

        // 根据插件来显示对应的选项
        foreach($field as $k=>$v){
            $field[$k]['name'] = $lang[ $v['name'] ] ?? $v['name'];
            foreach($v['field'] as $kk=>$vv){
                $field[$k]['field'][$kk]['name'] = $lang[ $vv['name'] ] ?? $vv['name'];
                if(isset($vv['module'])){
                    if( !isset($this->plugin[ $vv['module']['name'] ]) ){
                        unset($field[$k]['field'][$kk]);
                    }else{
                        // 删除插件记录的值
                        unset($field[$k]['field'][$kk]['module']);
                    }
                }
            }
            $field[$k]['field'] = array_values($field[$k]['field']);
        }

        foreach($dataRange as $k=>$v){
            $dataRange[$k]['name'] = $lang[ $v['name'] ] ?? $v['name'];
            foreach($v['field'] as $kk=>$vv){
                $dataRange[$k]['field'][$kk]['name'] = $lang[ $vv['name'] ] ?? $vv['name'];
                if(isset($vv['module'])){
                    if( !isset($this->plugin[ $vv['module']['name'] ]) ){
                        unset($dataRange[$k]['field'][$kk]);
                    }else{
                        // 删除插件记录的值
                        unset($dataRange[$k]['field'][$kk]['module']);
                    }
                }
            }
            $dataRange[$k]['field'] = array_values($dataRange[$k]['field']);
        }
        if($getDataRangeOption){
            foreach($dataRange as $k=>$v){
                foreach($v['field'] as $kk=>$vv){
                    if($vv['key']=='language'){
                        $langAdmin = lang_list('admin');
                        foreach ($langAdmin as $key => $value) {
                            $langAdmin[$key] = ['id' => $value['display_lang'], 'name' => $value['display_name']];
                        }
                        $dataRange[$k]['field'][$kk]['option'] = $langAdmin;
                    }else if($vv['key']=='product_name') {
                        $ProductModel = new ProductModel();
                        $dataRange[$k]['field'][$kk]['option'] = $ProductModel->getProductList();
                    }else if($vv['key']=='server_name') {
                        $dataRange[$k]['field'][$kk]['option'] = ServerModel::field('id,name')->select()->toArray();
                    }else if($vv['key']=='client_level') {
                        $dataRange[$k]['field'][$kk]['option'] = IdcsmartClientLevelModel::field('id,name')->select()->toArray();
                    }else if($vv['key']=='sale') {
                        $dataRange[$k]['field'][$kk]['option'] = IdcsmartSaleModel::field('id,name')->select()->toArray();
                    }else if($vv['key']=='country') {
                        $language = get_system_lang(true);
                        $countryField = ['en-us'=> 'nicename'];
                        $countryName = $countryField[ $language ] ?? 'name_zh';
                        $dataRange[$k]['field'][$kk]['option'] = CountryModel::field('id,'.$countryName.' name')->select()->toArray();
                    }else if($vv['key']=='host_status'){
                        foreach ($vv['option'] as $key => $value) {
                            $dataRange[$k]['field'][$kk]['option'][$key]['name'] = $lang['host_status_'.$value['id']];
                        }
                    }else if($vv['key']=='billing_cycle'){
                        foreach ($vv['option'] as $key => $value) {
                            $dataRange[$k]['field'][$kk]['option'][$key]['name'] = $lang['host_billing_cycle_'.$value['id']];
                        }
                    }else if($vv['key'] == 'gateway'){
                        $gateway = gateway_list();
                        foreach($gateway['list'] as $key=>$value){
                            $gateway['list'][$key]['id'] = $value['name'];
                            $gateway['list'][$key]['name'] = $value['title'];
                            unset($gateway['list'][$key]['title'], $gateway['list'][$key]['url']);
                        }
                        // 追加余额支付
                        array_unshift($gateway['list'], [
                            'id'    => 'credit',
                            'name'  => '余额支付',
                        ]);

                        $dataRange[$k]['field'][$kk]['option'] = $gateway['list'];
                    }else if(in_array($vv['type'], ['multi_select', 'select'])){
                        foreach ($vv['option'] as $key => $value) {
                            $dataRange[$k]['field'][$kk]['option'][$key]['name'] = $lang['admin_view_option_name_'.$vv['key'].'_'.$value['id']];
                        }
                    }
                }
            }
        }
        
        
        // 追加商品自定义字段
        if(in_array($view, ['host'])){
            $selfDefinedField = SelfDefinedFieldModel::field('id `key`,field_name name,field_type,field_option,is_global')
                    ->withAttr('key', function($val){
                        return 'self_defined_field_'.$val;
                    })
                    ->where('show_admin_host_list', 1)
                    ->order('relid,order', 'asc')
                    ->select()
                    ->toArray();
            if(!empty($selfDefinedField)){
                $selfDefinedField1 = [];
                $selfDefinedField2 = [];
                foreach ($selfDefinedField as $k => $v) {
                    $selfDefinedField1[] = ['key' => $v['key'], 'name' => $v['name'], 'is_global' => $v['is_global']];
                    if($v['field_type']=='dropdown'){
                        $option = explode(',', $v['field_option']);
                        foreach ($option as $key => $value) {
                            $option[$key] = ['id' => $value, 'name' => $value];
                        }
                        $selfDefinedField2[] = [
                            'key' => $v['key'], 
                            'name' => $v['name'], 
                            'type' => 'multi_select', 
                            'option' => $option, 
                            'rule' => [
                                'equal',
                                'not_equal',
                                'empty',
                                'not_empty',
                            ],
                            'is_global' => $v['is_global'], 
                        ];
                    }else if($v['field_type']=='tickbox'){
                        $option = [['id' => 0, 'name' => $lang['whether_0']], ['id'=>1, 'name' => $lang['whether_1']]];
                        $selfDefinedField2[] = [
                            'key' => $v['key'], 
                            'name' => $v['name'], 
                            'type' => 'select', 
                            'option' => $option,
                            'rule' => [
                                'equal',
                            ],
                            'is_global' => $v['is_global'], 
                        ];
                    }else{
                        if($v['field_type']=='password'){
                            $passwordField[] = $v['key'];
                        }
                        $selfDefinedField2[] = [
                            'key' => $v['key'], 
                            'name' => $v['name'], 
                            'type' => 'input',
                            'rule' => [
                                'equal',
                                'not_equal',
                                'include',
                                'not_include',
                                'empty',
                                'not_empty',
                            ],
                            'is_global' => $v['is_global'], 
                        ];
                    }
                }
                $field[] = [
                    'name'  => $lang['admin_field_product_custom_field'] ?? 'admin_field_product_custom_field',
                    'field' => $selfDefinedField1,
                ];
                $dataRange[] = [
                    'name'  => $lang['admin_field_product_custom_field'] ?? 'admin_field_product_custom_field',
                    'field' => $selfDefinedField2,
                ];
            }
        }
        // 追加用户自定义字段
        if(in_array($view, ['client','order','host','transaction'])){
            if(isset($this->plugin['ClientCustomField'])){
                $clientCustomField = $this->getClientCustomField();
                if(!empty($clientCustomField)){
                    $field[] = $clientCustomField['field'];
                    $dataRange[] = $clientCustomField['data_range'];
                    $passwordField = array_merge($passwordField, $clientCustomField['password_field']);
                }
            }
        }
        return ['field'=>$field, 'data_range'=>$dataRange, 'password_field' => $passwordField];
    }

    /**
     * 时间 2024-05-13
     * @title 获取用户自定义字段
     * @desc  获取用户自定义字段,需要先判断是否存在启用插件
     * @author theworld
     * @version v1
     */
    protected function getClientCustomField()
    {
        $lang = lang();
        $data = [];
        $clientCustomField = ClientCustomFieldModel::field('id `key`,name,type,options')
                ->where('status', '1')
                ->withAttr('key', function($val){
                    return 'addon_client_custom_field_'.$val;
                })
                ->order('order', 'asc')
                ->select()
                ->toArray();
        if(!empty($clientCustomField)){
            $clientCustomField1 = [];
            $clientCustomField2 = [];
            $clientCustomPasswordField = [];
            foreach ($clientCustomField as $k => $v) {
                $clientCustomField1[] = ['key' => $v['key'], 'name' => $v['name']];
                if($v['type']=='dropdown'){
                    $option = explode(',', $v['options']);
                    foreach ($option as $key => $value) {
                        $option[$key] = ['id' => $value, 'name' => $value];
                    }
                    $clientCustomField2[] = [
                        'key' => $v['key'], 
                        'name' => $v['name'], 
                        'type' => 'multi_select', 
                        'option' => $option, 
                        'rule' => [
                            'equal',
                            'not_equal',
                            'empty',
                            'not_empty',
                        ]
                    ];
                }else if($v['type']=='tickbox'){
                    $option = [['id' => 0, 'name' => $lang['whether_0']], ['id'=>1, 'name' => $lang['whether_1']]];
                    $clientCustomField2[] = [
                        'key' => $v['key'], 
                        'name' => $v['name'], 
                        'type' => 'select', 
                        'option' => $option,
                        'rule' => [
                            'equal',
                        ]
                    ];
                }else{
                    if($v['type']=='password'){
                        $clientCustomPasswordField[] = $v['key'];
                    }
                    $clientCustomField2[] = [
                        'key' => $v['key'], 
                        'name' => $v['name'], 
                        'type' => 'input',
                        'rule' => [
                            'equal',
                            'not_equal',
                            'include',
                            'not_include',
                            'empty',
                            'not_empty',
                        ]
                    ];
                }
            }

            $field = [
                'name'  => lang('admin_field_client_custom_field'),
                'field' => $clientCustomField1,
            ];
            $dataRange = [
                'name'  => lang('admin_field_client_custom_field'),
                'field' => $clientCustomField2,
            ];
            return ['field' => $field, 'data_range' => $dataRange, 'password_field' => $clientCustomPasswordField];
        }else{
            return [];
        }
    }

    /**
     * 时间 2024-06-18
     * @title 获取列表默认字段标识
     * @desc  获取列表默认字段标识
     * @author theworld
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水)
     * @return  array
     */
    public function adminFieldDefault($view)
    {
        $this->getActivePlugin();
        $data = [
            'client'        => ['id','username_company','certification','phone','email','host_active_num_host_num','client_status'],
            'order'         => ['id','username_company','product_name','order_amount','gateway','order_time','order_status'],
            'host'          => ['id','product_name_status','username_company','ip','renew_amount_cycle','due_time'],
            'transaction'   => ['id','amount','gateway','username_company','transaction_number','order_id','order_type','transaction_time'],
        ];
        if($view == 'client'){
            if(!isset($this->plugin['IdcsmartCertification'])){
                unset($data['client'][2]);
                $data['client'] = array_values($data['client']);
            }
        }
        return $data[ $view ] ?? ['id'];
    }

    /**
     * 时间 2024-06-18
     * @title 获取视图详情
     * @desc  获取视图详情
     * @author theworld
     * @version v1
     * @param   int param.id - 视图ID 和页面标识二选一必传
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) 和视图ID二选一必传
     * @param   int id - 视图ID
     * @param   string name - 视图名称
     * @param   int status - 状态0关闭1开启
     * @return  string field[].name - 字段分组名称
     * @return  string field[].field[].key - 字段标识
     * @return  string field[].field[].name - 字段名称
     * @return  array select_field - 当前选定字段标识
     * @return  int data_range_switch - 是否启用数据范围0否1是
     * @return  array select_data_range - 当前选定数据范围
     * @return  string select_data_range[].key - 当前选定数据范围字段标识
     * @return  string select_data_range[].rule - 当前选定数据范围规则:equal=等于,not_equal=不等于,include=包含,not_include=不包含,empty=为空,not_empty不为空,interval=区间,dynamic=动态
     * @return  mixed select_data_range[].value - 规则选定为empty和not_empty时不需要传递,当前选定数据范围的值,数据范围字段类型为input时为符合规则的数字和字符串,数据范围字段类型为multi_select时为选择的那些选项的值组成的数组,数据范围字段类型为select时为选择的选项的值,数据范围为date时,选定规则为equal时传递日期(xxxx-xx-xx)
     * @return  string select_data_range[].value.start - 开始日期,数据范围为date时,规则为interval时必传
     * @return  string select_data_range[].value.end - 结束日期,数据范围为date时,规则为interval时必传
     * @return  string select_data_range[].value.condition1 动态条件1(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @return  int select_data_range[].value.day1 动态时间1,数据范围为date时,规则为dynamic时,condition1不为now时必传
     * @return  string select_data_range[].value.condition2 动态条件2(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @return  int select_data_range[].value.day2 动态时间2,数据范围为date时,规则为dynamic时,condition2不为now时必传
     * @return  array password_field - 密码类型字段
     * @return  array admin_view_list - 可切换视图列表
     * @return  int admin_view_list[].id - 视图ID
     * @return  string admin_view_list[].name - 视图名称
     * @return  int admin_view_list[].default - 默认视图0否1是
     */
    public function adminViewIndex($param)
    {
        $adminId = get_admin_id(); 

        if(!empty($param['id'])){
            $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('id', $param['id'])->find();
            if(empty($adminView)){
                return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
            }
        }else{
            $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('status', 1)->where('choose', 1)->find();
            if(empty($adminView)){
                $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('status', 1)->where('last_visit', 1)->find();
            }
        }

        $field = $this->enableField($adminView['view'] ?? $param['view']);
        
        if(!empty($adminView)){
            $adminView = $adminView->toArray();
            $adminView['select_field'] = explode(',', $adminView['select_field']);
            $adminView['select_data_range'] = json_decode($adminView['select_data_range'], true);
            // 去掉不在可选字段的字段
            $enableField = [];
            foreach($field['field'] as $v){
                $enableField = array_merge($enableField, $v['field']);
            }
            $enableField = array_column($enableField, 'key');
            $adminView['select_field'] = array_values(array_intersect($adminView['select_field'], $enableField));

            // 去掉不在可选字段的字段
            $enableField = [];
            foreach($field['data_range'] as $v){
                $enableField = array_merge($enableField, $v['field']);
            }
            $enableField = array_column($enableField, 'key');
            foreach ($adminView['select_data_range'] as $k => $v) {
                if(!in_array($v['key'], $enableField)){
                    unset($adminView['select_data_range'][$k]);
                }
            }
            $adminView['select_data_range'] = array_values($adminView['select_data_range']);
        }else{
            $selectField = $this->adminFieldDefault($param['view']);
            $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('default', 1)->find();
            if(empty($adminView)){
                $cache = idcsmart_cache('create_default_view_'.$param['view'].'_'.$adminId);
                if(!empty($cache)){
                    $time = time();
                    sleep(($cache-$time)>0 ? ($cache-$time) : 1);
                    $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('default', 1)->find();
                }else{
                    idcsmart_cache('create_default_view_'.$param['view'].'_'.$adminId, time()+1, 1);
                    $adminView = $this->create([
                        'name'              => lang('admin_view_default'),
                        'view'              => $param['view'],
                        'default'           => 1,
                        'choose'            => 1,   
                        'admin_id'          => $adminId,
                        'select_field'      => implode(',', $selectField),
                        'data_range_switch' => 0,
                        'select_data_range' => json_encode([]),
                        'order'             => 0,
                        'create_time'       => time(),
                    ]);
                }
            }
            
            $adminView['select_field'] = explode(',', $adminView['select_field']);
            $adminView['select_data_range'] = json_decode($adminView['select_data_range'], true);
        }

        if($adminView['status']==1){
            $this->where('admin_id', $adminId)->where('view', $adminView['view'])->update(['last_visit' => 0]);
            $this->where('admin_id', $adminId)->where('view', $adminView['view'])->where('id', $adminView['id'])->update(['last_visit' => 1]);
        }
        $adminViewList = $this->field('id,name,default')->where('admin_id', $adminId)->where('view', $adminView['view'])->where('status', 1)->order('order', 'asc')->select()->toArray();

        $result = [
            'id'                => $adminView['id'],
            'name'              => $adminView['name'],
            'status'            => $adminView['status'],
            'field'             => $field['field'],
            'select_field'      => $adminView['select_field'],
            'data_range_switch' => $adminView['data_range_switch'],
            'select_data_range' => $adminView['select_data_range'],
            'password_field'    => $field['password_field'],
            'admin_view_list'   => $adminViewList,
        ];
        
        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 获取视图可用数据范围
     * @desc  获取视图可用数据范围
     * @author theworld
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  string data_range[].name - 数据范围分组名称
     * @return  string data_range[].field[].key - 数据范围字段标识
     * @return  string data_range[].field[].name - 数据范围字段名称
     * @return  string data_range[].field[].type - 数据范围字段类型,input输入,multi_select下拉多选,select下拉单选,date日期
     * @return  array data_range[].field[].option - 选项,类型为multi_select,select时后返回
     * @return  array data_range[].field[].option[].id - ID,标识为server_name,client_level,sale,country时返回
     * @return  array data_range[].field[].option[].name - 名称,标识为product_name,server_name,client_level,sale,country时返回
     * @return  array data_range[].field[].option[].child - 商品二级分组,标识为product_name时返回
     * @return  string data_range[].field[].option[].child[].name - 商品二级分组名称,标识为product_name时返回
     * @return  array data_range[].field[].option[].child[].product - 商品,标识为product_name时返回
     * @return  int data_range[].field[].option[].child[].product[].id - 商品ID,标识为product_name时返回
     * @return  string data_range[].field[].option[].child[].product[].name - 商品名称,标识为product_name时返回
     * @return  array data_range[].field[].rule - 数据范围规则:equal=等于,not_equal=不等于,include=包含,not_include=不包含,empty=为空,not_empty不为空,interval=区间,dynamic=动态
     */
    public function adminViewDataRange($param)
    {
        $field = $this->enableField($param['view'], true);

        $result = [
            'data_range' => $field['data_range'],
        ];

        return $result;
    }

    /**
     * 时间 2024-06-18
     * @title 视图列表
     * @desc  视图列表
     * @author theworld
     * @version v1
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  array list- 视图列表
     * @return  int list[].id - 视图ID
     * @return  string list[].name - 视图名称
     * @return  int list[].default - 默认视图0否1是
     * @return  int list[].status - 状态0关闭1开启
     * @return  int choose - 当前指定视图,为0代表默认展示最后浏览视图
     * @return  array choose_list- 可指定视图列表
     * @return  int choose_list[].id - 视图ID
     * @return  string choose_list[].name - 视图名称
     */
    public function adminViewList($param)
    {
        $adminId = get_admin_id();

        $list = $this->field('id,name,default,choose,status')
            ->where('admin_id', $adminId)
            ->where('view', $param['view'])
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $choose = 0;
        $chooseList = [];
        foreach ($list as $key => $value) {
            if($value['choose']==1 && $value['status']==1){
                $choose = $value['id'];
            }
            if($value['status']==1){
                $chooseList[] = ['id' => $value['id'], 'name' => $value['name']];
            }
            unset($list[$key]['choose']);
        }

        return ['list' => $list, 'choose' => $choose, 'choose_list' => $chooseList];
    }

    /**
     * 时间 2024-06-18
     * @title 新建视图
     * @desc  新建视图
     * @author theworld
     * @version v1
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   string param.name - 视图名称 require
     * @param   array param.select_field - 选定字段标识 require
     * @param   int param.data_range_switch - 是否启用数据范围0否1是 require
     * @param   array param.select_data_range - 当前选定数据范围
     * @param   string param.select_data_range[].key - 选定数据范围字段标识
     * @param   string param.select_data_range[].rule - 选定数据范围规则:equal=等于,not_equal=不等于,include=包含,not_include=不包含,empty=为空,not_empty不为空,interval=区间,dynamic=动态
     * @param   mixed param.select_data_range[].value - 规则选定为empty和not_empty时不需要传递,当前选定数据范围的值,数据范围字段类型为input时为符合规则的数字和字符串,数据范围字段类型为multi_select时为选择的那些选项的值组成的数组,数据范围字段类型为select时为选择的选项的值,数据范围为date时,选定规则为equal时传递日期(xxxx-xx-xx)
     * @param   string param.select_data_range[].value.start - 开始日期,数据范围为date时,规则为interval时必传
     * @param   string param.select_data_range[].value.end - 结束日期,数据范围为date时,规则为interval时必传
     * @param   string param.select_data_range[].value.condition1 动态条件1(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @param   int param.select_data_range[].value.day1 动态时间1,数据范围为date时,规则为dynamic时,condition1不为now时必传
     * @param   string param.select_data_range[].value.condition2 动态条件2(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @param   int param.select_data_range[].value.day2 动态时间2,数据范围为date时,规则为dynamic时,condition2不为now时必传
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function createAdminView($param)
    {
        $adminId = get_admin_id();

        $enableField = $this->enableField($param['view']);

        $field = [];
        foreach($enableField['field'] as $v){
            $field = array_merge($field, $v['field']);
        }
        $field = array_column($field, 'name', 'key');

        // 直接排除不在可选字段中的值
        $selectField = [];
        foreach($param['select_field'] as $v){
            if(is_string($v) && isset($field[$v])){
                $selectField[] = $v;
            }
        }

        if($param['data_range_switch']==1 && isset($param['select_data_range'])){
            $field = [];
            foreach($enableField['data_range'] as $v){
                $field = array_merge($field, $v['field']);
            }
            $field = array_column($field, 'name', 'key');

            // 直接排除不在可选字段中的值
            $selectDataRange = [];
            foreach($param['select_data_range'] as $v){
                if(is_string($v['key']) && isset($field[$v['key']])){
                    $selectDataRange[] = $v;
                }
            }
        }else{
            $selectDataRange = [];
        }
        

        $this->startTrans();
        try {
            $maxOrder = $this->where('admin_id', $adminId)->where('view', $param['view'])->max('order');
            $this->create([
                'name'              => $param['name'],
                'view'              => $param['view'],  
                'admin_id'          => $adminId,
                'select_field'      => implode(',', $selectField),
                'data_range_switch' => $param['data_range_switch'],
                'select_data_range' => json_encode($selectDataRange),
                'order'             => $maxOrder+1,
                'create_time'       => time(),
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }

        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-06-18
     * @title 编辑视图
     * @desc  编辑视图
     * @author theworld
     * @version v1
     * @param   int param.id - 视图ID require
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   string param.name - 视图名称
     * @param   array param.select_field - 选定字段标识
     * @param   int param.data_range_switch - 是否启用数据范围0否1是
     * @param   array param.select_data_range - 当前选定数据范围
     * @param   string param.select_data_range[].key - 选定数据范围字段标识
     * @param   string param.select_data_range[].rule - 选定数据范围规则:equal=等于,not_equal=不等于,include=包含,not_include=不包含,empty=为空,not_empty不为空,interval=区间,dynamic=动态
     * @param   mixed param.select_data_range[].value - 规则选定为empty和not_empty时不需要传递,当前选定数据范围的值,数据范围字段类型为input时为符合规则的数字和字符串,数据范围字段类型为multi_select时为选择的那些选项的值组成的数组,数据范围字段类型为select时为选择的选项的值,数据范围为date时,选定规则为equal时传递日期(xxxx-xx-xx)
     * @param   string param.select_data_range[].value.start - 开始日期,数据范围为date时,规则为interval时必传
     * @param   string param.select_data_range[].value.end - 结束日期,数据范围为date时,规则为interval时必传
     * @param   string param.select_data_range[].value.condition1 动态条件1(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @param   int param.select_data_range[].value.day1 动态时间1,数据范围为date时,规则为dynamic时,condition1不为now时必传
     * @param   string param.select_data_range[].value.condition2 动态条件2(now=当前,ago=天前,later=天后),数据范围为date时,规则为dynamic时必传
     * @param   int param.select_data_range[].value.day2 动态时间2,数据范围为date时,规则为dynamic时,condition2不为now时必传
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function updateAdminView($param)
    {
        $adminId = get_admin_id();

        $adminView = $this->where('admin_id', $adminId)->where('id', $param['id'])->find();
        if(empty($adminView)){
            return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
        }

        $enableField = $this->enableField($adminView['view']);

        $data = [
            'update_time' => time()
        ];

        if(isset($param['name'])){
            $data['name'] = $param['name'];
        }

        if(isset($param['select_field'])){
            $field = [];
            foreach($enableField['field'] as $v){
                $field = array_merge($field, $v['field']);
            }
            $field = array_column($field, 'name', 'key');

            // 直接排除不在可选字段中的值
            $selectField = [];
            foreach($param['select_field'] as $v){
                if(is_string($v) && isset($field[$v])){
                    $selectField[] = $v;
                }
            }
            $data['select_field'] = implode(',', $selectField);
        }

        if(isset($param['data_range_switch'])){
            $data['data_range_switch'] = $param['data_range_switch'];
        }else{
            $data['data_range_switch'] = $adminView['data_range_switch'];
        }

        if($data['data_range_switch']==1 && isset($param['select_data_range'])){
            $field = [];
            foreach($enableField['data_range'] as $v){
                $field = array_merge($field, $v['field']);
            }
            $field = array_column($field, 'name', 'key');

            // 直接排除不在可选字段中的值
            $selectDataRange = [];
            foreach($param['select_data_range'] as $v){
                if(is_string($v['key']) && isset($field[$v['key']])){
                    $selectDataRange[] = $v;
                }
            }

            $data['select_data_range'] = json_encode($selectDataRange);
        }

        $this->startTrans();
        try {
            $this->update($data, ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-06-18
     * @title 删除视图
     * @desc  删除视图
     * @author theworld
     * @version v1
     * @param   int id - 视图ID require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function deleteAdminView($id)
    {
        $adminId = get_admin_id();

        $adminView = $this->where('admin_id', $adminId)->where('id', $id)->find();
        if(empty($adminView)){
            return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
        }

        if($adminView['default']==1){
            return ['status' => 400, 'msg' => lang('admin_default_view_cannot_delete')];
        }

        $this->startTrans();
        try {
            $this->destroy($id);
            if($adminView['choose']==1){
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->update(['choose' => 0]);
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->where('default', 1)->update(['choose' => 1]);
            }
            if($adminView['last_visit']==1){
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->update(['last_visit' => 0]);
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->where('default', 1)->update(['last_visit' => 1]);
            }
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2024-06-18
     * @title 复制视图
     * @desc  复制视图
     * @author theworld
     * @version v1
     * @param   int id - 视图ID require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function copyAdminView($id)
    {
        $adminId = get_admin_id();

        $adminView = $this->where('admin_id', $adminId)->where('id', $id)->find();
        if(empty($adminView)){
            return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
        }

        $this->startTrans();
        try {
            $maxOrder = $this->where('admin_id', $adminId)->where('view', $adminView['view'])->max('order');
            $this->create([
                'name'              => $adminView['name'].'(1)',
                'view'              => $adminView['view'],  
                'admin_id'          => $adminId,
                'select_field'      => $adminView['select_field'],
                'data_range_switch' => $adminView['data_range_switch'],
                'select_data_range' => $adminView['select_data_range'],
                'order'             => $maxOrder+1,
                'create_time'       => time(),
            ]);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('copy_fail')];
        }

        return ['status' => 200, 'msg' => lang('copy_success')];
    }

    /**
     * 时间 2024-06-18
     * @title 视图切换状态
     * @desc  视图切换状态
     * @author theworld
     * @version v1
     * @param   int param.id - 视图ID require
     * @param   int param.status - 状态0关闭1开启 require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function statusAdminView($param)
    {
        $adminId = get_admin_id();

        $adminView = $this->where('admin_id', $adminId)->where('id', $param['id'])->find();
        if(empty($adminView)){
            return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
        }

        if($adminView['default']==1 && $param['status']==0){
            return ['status' => 400, 'msg' => lang('admin_default_view_cannot_disable')];
        }

        if($adminView['status']==$param['status']){
            return ['status' => 200, 'msg' => lang('success_message')];
        }

        $this->startTrans();
        try {
            $this->update([
                'status' => $param['status'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            if($adminView['choose']==1 && $param['status']==0){
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->update(['choose' => 0]);
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->where('default', 1)->update(['choose' => 1]);
            }
            if($adminView['last_visit']==1 && $param['status']==0){
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->update(['last_visit' => 0]);
                $this->where('admin_id', $adminId)->where('view', $adminView['view'])->where('default', 1)->update(['last_visit' => 1]);
            }
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-06-18
     * @title 视图排序
     * @desc  视图排序
     * @author theworld
     * @version v1
     * @param   array param.id - 视图ID require
     * @param   string param.view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function orderAdminView($param)
    {
        $param['id'] = $param['id'] ?? [];

        $adminId = get_admin_id();

        $count = $this->where('admin_id', $adminId)->where('view', $param['view'])->whereIn('id', $param['id'])->count();
        if($count!=count($param['id'])){
            return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
        }

        $this->startTrans();
        try {
            foreach ($param['id'] as $key => $value) {
                $this->update([
                    'order' => $key,
                    'update_time' => time(),
                ], ['id' => $value]);
            }
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-06-18
     * @title 指定视图
     * @desc  指定视图
     * @author theworld
     * @version v1
     * @param   string view - 页面标识(client=用户管理,order=订单管理,host=产品管理,transaction=交易流水) require
     * @param   int choose - 指定视图ID,为0代表默认展示最后浏览视图 require
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function chooseAdminView($param)
    {
        $adminId = get_admin_id();

        if(!empty($param['choose'])){
            $adminView = $this->where('admin_id', $adminId)->where('view', $param['view'])->where('id', $param['choose'])->find();
            if(empty($adminView)){
                return ['status' => 400, 'msg' => lang('admin_view_is_not_exist')];
            }
        }
        
        $this->startTrans();
        try {
            $this->where('admin_id', $adminId)->where('view', $param['view'])->update(['choose' => 0]);
            $this->where('admin_id', $adminId)->where('view', $param['view'])->where('id', $param['choose'])->update(['choose' => 1]);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-05-21
     * @title 获取已激活插件
     * @desc  获取已激活插件
     * @author theworld
     * @version v1
     * @param   array
     */
    protected function getActivePlugin()
    {
        if(empty($this->plugin)){
            // 获取可用插件
            $PluginModel = new PluginModel();
            $activePluginList = $PluginModel->activePluginList();
            $this->plugin = array_column($activePluginList['list'], 'id', 'name');
        }
        return $this->plugin;
    }

}
<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2025-05-20
 */
class IdcsmartTicketForwardModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_forward';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_id'                        => 'int',
        'admin_id'                         => 'int',
        'forward_admin_id'                 => 'int',
        'ticket_type_id'                   => 'int',
        'notes'                            => 'string',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];
}

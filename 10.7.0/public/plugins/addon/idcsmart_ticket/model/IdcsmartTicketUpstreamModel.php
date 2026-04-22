<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2024-06-17
 */
class IdcsmartTicketUpstreamModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_upstream';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'host_id'                          => 'int',
        'upstream_host_id'                 => 'int',
        'ticket_id'                        => 'int',
        'upstream_ticket_id'               => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'delivery_status'                  => 'int',
    ];

}

<?php
namespace app\common\model;

use think\Model;

/**
 * @title 产品通知模型
 * @desc  产品通知模型
 * @use app\common\model\HostNoticeModel
 */
class HostNoticeModel extends Model
{
	protected $name = 'host_notice';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'host_id'               => 'int',
        'traffic_limit_exceed'  => 'int',
        'traffic_enough'        => 'int',
        'traffic_limit_exceed_time' => 'int',
        'traffic_not_enough_time'   => 'int',
    ];

    /**
     * @时间 2025-04-22
     * @title 获取产品通知
     * @desc  获取产品通知
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  HostNoticeModel
     */
    public function hostNoticeIndex($hostId)
    {
        $hostNotice = $this
                    ->where('host_id', $hostId)
                    ->find();
        if(empty($hostNotice)){
            $hostNotice = $this->create([
                'host_id'               => $hostId,
                'traffic_limit_exceed'  => 0,
                'traffic_enough'        => 1,
                'traffic_limit_exceed_time' => 0,
                'traffic_not_enough_time'   => 0,
            ]);
        }
        return $hostNotice;
    }

    /**
     * @时间 2025-04-22
     * @title 产品流量超出提醒
     * @desc  产品流量超出提醒
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     */
    public function trafficLimitExceed($hostId)
    {
        $this->where('host_id', $hostId)->update([
            'traffic_limit_exceed'      => 1,
            'traffic_limit_exceed_time' => time(),
        ]);
    }

    /**
     * @时间 2025-04-22
     * @title 产品流量超出恢复
     * @desc  产品流量超出恢复
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     */
    public function trafficLimitExceedRecover($hostId)
    {
        $this->where('host_id', $hostId)->update([
            'traffic_limit_exceed'      => 0,
        ]);
    }

    /**
     * @时间 2025-04-22
     * @title 产品流量不足提醒
     * @desc  产品流量不足提醒
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     */
    public function trafficNotEnough($hostId)
    {
        $this->where('host_id', $hostId)->update([
            'traffic_enough'            => 0,
            'traffic_not_enough_time'   => time(),
        ]);
    }

    /**
     * @时间 2025-04-22
     * @title 产品流量不足恢复
     * @desc  产品流量不足恢复
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     */
    public function trafficNotEnoughRecover($hostId)
    {
        $this->where('host_id', $hostId)->update([
            'traffic_enough'            => 1,
        ]);
    }

}
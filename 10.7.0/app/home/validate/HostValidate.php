<?php
namespace app\home\validate;

use think\Validate;

/**
 * 产品管理验证
 */
class HostValidate extends Validate
{
	protected $rule = [
		'id' 						=> 'require|integer|gt:0',
        'ids'                       => 'require|array',
        'notes' 					=> 'max:1000',
        'auto_release_time'         => 'require|integer|checkAutoReleaseTime:thinkphp',
    ];

    protected $message  =   [
    	'id.require'     				=> 'id_error',
    	'id.integer'     				=> 'id_error',
        'id.gt'                         => 'id_error',
    	'ids.require'                   => 'ids_error',
        'ids.array'                    => 'ids_error',
    	'notes.max'     				=> 'host_notes_cannot_exceed_1000_chars',
        'auto_release_time.require'     => 'host_auto_release_time_require',
        'auto_release_time.integer'     => 'host_auto_release_time_require',
    ];

    protected $scene = [
        'update_notes'  => ['id', 'notes'],
        'batch_update_notes'  => ['ids', 'notes'],
        'update_auto_release_time'  => ['id', 'auto_release_time'],
    ];

    // 验证自动释放时间
    public function checkAutoReleaseTime($value)
    {
        if($value == 0){
            return true;
        }
        $endTime = strtotime('2099-12-31 23:59:59');
        $startTime = time();
        if($value <= $startTime){
            return 'host_auto_release_time_min_time_error';
        }
        if($value > $endTime){
            return 'host_auto_release_time_max_time_error';
        }
        return true;
    }

}
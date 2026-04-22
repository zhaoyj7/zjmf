<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 线路IP验证
 * @use  server\mf_dcim\validate\LineIpValidate
 */
class LineIpValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|length:1,255|checkIpNum:thinkphp',
        'min_value'         => 'require|integer|between:0,10000',
        'max_value'         => 'require|integer|between:0,10000|egt:min_value',
        'price'             => 'checkPrice:thinkphp',
        'value_show'        => 'max:255',

    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'type.require'                  => 'please_select_line_bw_type',
        'type.in'                       => 'line_bw_type_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_please_input_line_ip_num',
        'value.length'                  => 'mf_dcim_ip_num_length_error',
        'min_value.require'             => 'mf_dcim_please_input_line_bw_min_value',
        'min_value.integer'             => 'mf_dcim_line_ip_num_format_error',
        'min_value.between'             => 'mf_dcim_line_ip_num_format_error',
        'max_value.require'             => 'mf_dcim_please_input_line_bw_max_value',
        'max_value.integer'             => 'mf_dcim_line_ip_num_format_error',
        'max_value.between'             => 'mf_dcim_line_ip_num_format_error',
        'max_value.egt'                 => 'mf_dcim_line_bw_max_value_must_gt_min_value',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
        'value_show.max'                => 'mf_dcim_value_show_length_error',
    ];

    protected $scene = [
        'create'        => ['id','type','price','value_show'],
        'update'        => ['id','type','price','value_show'],
        'radio'         => ['value'],
        'step'          => ['min_value','max_value'],
        'line_create'   => ['type','price','value_show'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>9999999){
                return 'mf_dcim_price_must_between_0_999999';
            }
        }
        return true;
    }

    /**
     * 时间 2023-05-15
     * @title 验证IP数量格式
     * @desc  验证IP数量格式
     * @author hh
     * @version v1
     * @param   int|string $value - IP数量 require
     */
    public function checkIpNum($value){
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 10000){
                return 'mf_dcim_line_ip_num_format_error';
            }
        }else if($value == 'NC'){

        }else{
            $value = ToolLogic::formatDcimIpNum($value);
            if($value === false){
                return 'mf_dcim_ip_num_format_error2';
            }
        }
        return true;
    }



}
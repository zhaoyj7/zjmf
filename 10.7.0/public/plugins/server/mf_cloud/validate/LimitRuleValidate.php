<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;

/**
 * @title 限制规则验证
 * @use  server\mf_cloud\validate\LimitRuleValidate
 */
class LimitRuleValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'rule'              => 'require|array|checkLimitRule:thinkphp',
        'result'            => 'require|array|checkLimitResult:thinkphp',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'product_id.require'    => 'product_id_error',
        'product_id.integer'    => 'product_id_error',
        'rule.require'          => 'mf_cloud_limit_rule_rule_require',
        'rule.array'            => 'mf_cloud_limit_rule_rule_require',
        'require.require'       => 'mf_cloud_limit_rule_result_require',
        'require.array'         => 'mf_cloud_limit_rule_result_require',
    ];

    protected $scene = [
        'create'        => ['product_id','rule','result'],
        'update'        => ['id','rule','result'],
    ];

    // 内存配置缓存
    protected $memoryType = NULL;

    /**
     * 时间 2024-05-10
     * @title 验证限制规则条件
     * @desc  验证限制规则条件
     * @author hh
     * @version v1
     * @param   array $value - 条件 require
     * @return  string|bool
     */
    protected function checkLimitRule($value, $t, $data)
    {
        $productId = $data['product_id'];
        $ruleType = ['data_center','cpu','memory','image','ipv4_num','bw','flow'];

        $typeArr = [];
        $hasBw = false;
        $hasFlow = false;
        foreach($value as $type=>$rule){
            if(!in_array($type, $ruleType)){
                continue;
            }
            if(!isset($rule['opt']) || !in_array($rule['opt'], ['eq','neq'])){
                return 'mf_cloud_limit_rule_opt_error';
            }
            if($type == 'bw') $hasBw = true;
            if($type == 'flow') $hasFlow = true;

            if($type == 'cpu'){
                if(!isset($rule['value']) || !is_array($rule['value']) || empty($rule['value'])){
                    return 'mf_cloud_limit_rule_select_'.$type;
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['memory'])){
                // 获取内存配置类型
                $memoryType = $this->getMemoryType($productId);
                if(empty($memoryType)){
                    return 'please_add_memory_config_first';
                }
                if($memoryType == 'radio'){
                    if(!isset($rule['value']) || !is_array($rule['value']) || empty($rule['value'])){
                        return 'please_select_memory_config';
                    }
                }else{
                    // 范围类型
                    $res = $this->checkRange($rule);
                    if($res !== true){
                        return $res;
                    }
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['data_center','image'])){
                if(!isset($rule['id']) || !is_array($rule['id']) || empty($rule['id'])){
                    return 'mf_cloud_limit_rule_select_'.$type;
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['ipv4_num','bw','flow'])){
                // 范围类型
                $res = $this->checkRange($rule);
                if($res !== true){
                    return $res;
                }
                $typeArr[] = $type;
            }
        }
        if(empty($typeArr)){
            return 'mf_cloud_limit_rule_at_least_one_rule';
        }
        // if(count($typeArr) < 2){
        //     return 'mf_cloud_limit_rule_at_least_two_type';
        // }
        // if($hasRecommend && $hasOther){
        //     return 'mf_cloud_limit_rule_recommond_config_only_with_duration';
        // }
        if($hasBw && $hasFlow){
            return 'mf_cloud_limit_rule_cannot_add_bw_and_flow_in_one_rule';
        }
        return true;
    }

    // 验证范围
    protected function checkRange($value)
    {
        $value['min'] = $value['min'] ?? '';
        $value['max'] = $value['max'] ?? '';
        if($value['min'] === '' && $value['max'] === ''){
            return 'mf_cloud_limit_rule_range_min_and_max_at_least_one';
        }
        if($value['min'] !== ''){
            if(!preg_match('/\d+/', $value['min'])){
                return 'mf_cloud_limit_rule_range_min_format_error';
            }
            if($value['min'] < 0 || $value['min'] > 99999999){
                return 'mf_cloud_limit_rule_range_min_format_error';
            }
        }
        if($value['max'] !== ''){
            if(!preg_match('/\d+/', $value['max'])){
                return 'mf_cloud_limit_rule_range_max_format_error';
            }
            if($value['max'] < 0 || $value['max'] > 99999999){
                return 'mf_cloud_limit_rule_range_max_format_error';
            }
        }
        if($value['min'] !== '' && $value['max'] !== ''  && $value['min'] > $value['max']){
            return 'mf_cloud_limit_rule_range_min_cannot_gt_max';
        }
        return true;
    }

    /**
     * 时间 2024-05-24
     * @title 验证限制规则结果
     * @desc  验证限制规则结果
     * @author hh
     * @version v1
     * @param   array $value - 结果 require
     * @return  string|bool
     */
    protected function checkLimitResult($value, $t, $data)
    {
        $productId = $data['product_id'];
        $ruleType = ['cpu','memory','image','system_disk','ipv4_num','bw','flow'];

        $typeArr = [];
        $hasBw = false;
        $hasFlow = false;
        foreach($value as $type=>$ruleArr){
            if(!in_array($type, $ruleType)){
                continue;
            }
            if($type == 'bw') $hasBw = true;
            if($type == 'flow') $hasFlow = true;

            $opt = '';
            foreach($ruleArr as $rule){
                if(!isset($rule['opt']) || !in_array($rule['opt'], ['eq','neq'])){
                    return 'mf_cloud_limit_rule_opt_error';
                }
                if(empty($opt)){
                    $opt = $rule['opt'];
                }else{
                    // 同个结果只能添加相同的运算符
                    if($rule['opt'] != $opt){
                        return 'mf_cloud_limit_rule_opt_error';
                    }
                }
                if($type == 'cpu'){
                    if(!isset($rule['value']) || !is_array($rule['value']) || empty($rule['value'])){
                        return 'mf_cloud_limit_rule_select_'.$type;
                    }
                    $typeArr[] = $type;
                }else if(in_array($type, ['memory'])){
                    // 获取内存配置类型
                    $memoryType = $this->getMemoryType($productId);
                    if(empty($memoryType)){
                        return 'please_add_memory_config_first';
                    }
                    if($memoryType == 'radio'){
                        if(!isset($rule['value']) || !is_array($rule['value']) || empty($rule['value'])){
                            return 'please_select_memory_config';
                        }
                    }else{
                        // 范围类型
                        $res = $this->checkRange($rule);
                        if($res !== true){
                            return $res;
                        }
                    }
                    $typeArr[] = $type;
                }else if(in_array($type, ['image'])){
                    if(!isset($rule['id']) || !is_array($rule['id']) || empty($rule['id'])){
                        return 'mf_cloud_limit_rule_select_'.$type;
                    }
                    $typeArr[] = $type;
                }else if(in_array($type, ['system_disk','ipv4_num','bw','flow'])){
                    // 范围类型
                    $res = $this->checkRange($rule);
                    if($res !== true){
                        return $res;
                    }
                    $typeArr[] = $type;
                }
            }
        }
        if(empty($typeArr)){
            return 'mf_cloud_limit_rule_at_least_one_result';
        }
        // 条件结果不能相交
        $ruleKey = array_keys($data['rule']);
        $intersect = array_intersect($typeArr, $ruleKey);
        if(!empty($intersect)){
            return 'mf_cloud_limit_rule_and_result_type_must_diff';
        }
        if(($hasBw || $hasFlow) && (in_array('bw', $ruleKey) || in_array('flow', $ruleKey))){
            return 'mf_cloud_limit_rule_cannot_add_bw_and_flow_in_one_rule';
        }
        return true;
    }

    /**
     * 时间 2024-07-05
     * @title 获取当前内存配置方式
     * @desc  获取当前内存配置方式
     * @author hh
     * @version v1
     * @param   string $productId - 产品ID
     * @return  string
     */
    protected function getMemoryType($productId)
    {
        if(is_null($this->memoryType)){
            $this->memoryType = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::MEMORY)->value('type') ?? '';
        }
        return $this->memoryType;
    }



}
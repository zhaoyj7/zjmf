<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 级联项价格验证器
 * @author theworld
 * @version v1
 */
class IdcsmartCommonCascadeItemPriceValidate extends Validate
{
    protected $rule = [
        'configoption_id' => 'require|integer',
        'item_id' => 'require|integer',
        'fee_type' => 'require|in:fixed,qty,stage',
        'onetime' => 'float|egt:0',
        'option_param' => 'max:255',
        'custom_cycle' => 'array',
        'qty_min' => 'integer|egt:0',
        'qty_max' => 'integer|egt:qty_min',
        'subs' => 'array|checkSubs:thinkphp',
    ];

    protected $message = [
        'configoption_id.require' => 'param_error',
        'configoption_id.integer' => 'param_error',
        'item_id.require' => 'param_error',
        'item_id.integer' => 'param_error',
        'fee_type.require' => 'param_error',
        'fee_type.in' => 'param_error',
        'onetime.float' => 'param_error',
        'onetime.egt' => 'param_error',
        'option_param.max' => 'param_error',
        'custom_cycle.array' => 'param_error',
        'qty_min.integer' => 'param_error',
        'qty_min.egt' => 'param_error',
        'qty_max.integer' => 'param_error',
        'qty_max.egt' => 'idcsmart_common_configoption_sub_qty_max_egt',
        'subs.array' => 'param_error',
        'subs.checkSubs' => 'param_error',
    ];

    protected $scene = [
        'set' => ['configoption_id', 'item_id', 'fee_type', 'onetime', 'custom_cycle', 'subs'],
    ];

    /**
     * 验证subs数组内部参数
     * @param array $value subs数组
     * @param string $rule 规则
     * @param array $data 全部数据
     * @return bool|string
     */
    protected function checkSubs($value, $rule, $data)
    {
        // 如果计费类型不是qty或stage，不需要验证subs
        if (!isset($data['fee_type']) || !in_array($data['fee_type'], ['qty', 'stage'])) {
            return true;
        }

        // qty和stage类型必须有subs数组且不能为空
        if (empty($value) || !is_array($value)) {
            return lang_plugins('idcsmart_common_subs_required');
        }

        // 验证每个子项
        foreach ($value as $index => $sub) {
            if (!is_array($sub)) {
                return lang_plugins('idcsmart_common_subs_item_must_array');
            }

            // 验证qty_min
            if (!isset($sub['qty_min']) || !is_numeric($sub['qty_min']) || $sub['qty_min'] < 0) {
                return lang_plugins('idcsmart_common_subs_qty_min_error');
            }

            // 验证qty_max
            if (!isset($sub['qty_max']) || !is_numeric($sub['qty_max']) || $sub['qty_max'] < 0) {
                return lang_plugins('idcsmart_common_subs_qty_max_error');
            }

            // qty_max必须大于等于qty_min
            if ($sub['qty_max'] > 0 && $sub['qty_max'] < $sub['qty_min']) {
                return lang_plugins('idcsmart_common_configoption_sub_qty_max_egt');
            }

            // 验证onetime价格
            if (isset($sub['onetime']) && (!is_numeric($sub['onetime']) || $sub['onetime'] < 0)) {
                return lang_plugins('idcsmart_common_subs_onetime_error');
            }

            // 验证custom_cycle
            if (isset($sub['custom_cycle']) && !is_array($sub['custom_cycle'])) {
                return lang_plugins('idcsmart_common_subs_custom_cycle_error');
            }
        }

        // 验证区间连续性：按 qty_min 排序后，相邻区间必须首尾衔接
        usort($value, function($a, $b) {
            return intval($a['qty_min'] ?? 0) - intval($b['qty_min'] ?? 0);
        });
        for ($i = 0; $i < count($value); $i++) {
            $min = intval($value[$i]['qty_min'] ?? 0);
            if ($min < 0) {
                return lang_plugins('idcsmart_common_subs_qty_min_error');
            }
            if ($i > 0) {
                $prevMax = intval($value[$i - 1]['qty_max'] ?? 0);
                if ($prevMax == 0) {
                    return lang_plugins('idcsmart_common_cascade_qty_range_not_continuous');
                }
                if ($min != $prevMax + 1) {
                    return lang_plugins('idcsmart_common_cascade_qty_range_not_continuous');
                }
            }
        }

        return true;
    }
}

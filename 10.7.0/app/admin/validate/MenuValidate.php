<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 导航管理验证
 */
class MenuValidate extends Validate
{
	protected $rule = [
		'menu'    => 'require|checkMenu:thinkphp',
        'menu2'   => 'require|checkHomeMenu:thinkphp',
    ];

    protected $message  =   [
    	'menu.require'          => 'param_error',
        'menu.checkMenu'        => 'param_error',
        'menu2.checkHomeMenu'   => 'param_error',
    ];

    protected $scene = [
        'save' => ['menu'],
        'save_home' => ['menu2']
    ];

    public function checkMenu($value)
    {
        $navId = [];
        foreach ($value as $k => $v) {
            if(!isset($v['type'])){
                return false;
            }
            // 新增内嵌类型
            if(!in_array($v['type'], ['system', 'plugin', 'custom', 'embedded'])){
                return false;
            }
            if(!isset($v['name'])){
                return false;
            }
            if(!is_string($v['name'])){
                return false;
            }
            if(mb_strlen($v['name'])>20){
                return false;
            }
            if(!is_array($v['language'])){
                return false;
            }
            if($v['type']=='custom' || $v['type']=='embedded'){
                if(!isset($v['url'])){
                    return false;
                }
                if(!is_string($v['url'])){
                    return false;
                }
                if(strlen($v['url'])>255){
                    return false;
                }
            }else{
                if(!isset($v['nav_id'])){
                    return false;
                }
                if(!is_integer($v['nav_id'])){
                    return false;
                }
                if($v['nav_id']<=0){
                    return false;
                }
                $navId[] = $v['nav_id'];
            }
            if(!isset($v['child'])){
                return false;
            }
            if(!is_array($v['child'])){
                return false;
            }
            if(!empty($v['child'])){
                foreach ($v['child'] as $ck => $cv) {
                    if(!isset($cv['type'])){
                        return false;
                    }
                    if(!in_array($cv['type'], ['system', 'plugin', 'custom', 'embedded'])){
                        return false;
                    }
                    if(!isset($cv['name'])){
                        return false;
                    }
                    if(!is_string($cv['name'])){
                        return false;
                    }
                    if(mb_strlen($cv['name'])>20){
                        return false;
                    }
                    if(!is_array($cv['language'])){
                        return false;
                    }
                    if($cv['type']=='custom' || $v['type']=='embedded'){
                        if(!isset($cv['url'])){
                            return false;
                        }
                        if(!is_string($cv['url'])){
                            return false;
                        }
                        if(strlen($cv['url'])>255){
                            return false;
                        }
                    }else{
                        if(!isset($cv['nav_id'])){
                            return false;
                        }
                        if(!is_integer($cv['nav_id'])){
                            return false;
                        }
                        if($cv['nav_id']<=0){
                            return false;
                        }
                        $navId[] = $cv['nav_id'];
                    }
                }
            }  
        }
        if(count($navId)!=count(array_unique($navId))){
            return 'nav_cannot_repeat_add';
        }
        return true;
    }

    public function checkHomeMenu($value)
    {
        $productId = [];
        $count = 0;
        $navId = [];

        // 可多选的res模块
        $resCloud = ['whmcs_cloud','mf_cloud','mf_finance'];
        $resDcim  = ['whmcs_dcim','mf_dcim','mf_finance_dcim'];
        $selectField = ['area','product_name','billing_cycle','is_auto_renew','base_info','ip','os','active_time','due_time','status','notes'];

        foreach ($value as $k => $v) {
            if(!isset($v['name'])){
                return false;
            }
            if(!is_string($v['name'])){
                return false;
            }
            if($v['name'] === ''){
                return 'nav_name_no_input';
            }
            if(mb_strlen($v['name'])>20){
                return false;
            }
            if(!isset($v['type'])){
                return false;
            }
            if(!in_array($v['type'], ['system', 'plugin', 'custom', 'module','embedded'])){
                return false;
            }
            if(!is_array($v['language'])){
                return false;
            }
            if($v['type']=='custom'){
                if(!isset($v['url'])){
                    return false;
                }
                if(!is_string($v['url'])){
                    return false;
                }
                if(strlen($v['url'])>255){
                    return false;
                }
                if(!isset($v['second_reminder'])){
                    return false;
                }
                if(!in_array($v['second_reminder'], ['0', '1'])){
                    return false;
                }
            }else if(in_array($v['type'], ['module'])){
                $v['module'] = $v['module'] ?? [];
                $v['res_module'] = $v['res_module'] ?? [];
                $v['is_cross_module'] = $v['is_cross_module'] ?? 0;
                $v['select_field'] = $v['select_field'] ?? [];
                if(!in_array($v['is_cross_module'], ['0', '1'])){
                    return false;
                }
                if($v['is_cross_module']==1){
                    if(!is_array($v['select_field']) || !empty(array_diff($v['select_field'], $selectField))){
                        return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                    }

                    if(!is_array($v['module']) || !is_array($v['res_module'])){
                        return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                    }
                }else{
                    if(!is_array($v['module']) || count($v['module'])>1 || !is_array($v['res_module'])){
                        return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                    }
                    // 不能同时为空
                    if(empty($v['module']) && empty($v['res_module'])){
                        return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                    }
                    if(in_array('mf_cloud', $v['module']) && !empty($v['res_module']) && !empty(array_diff($v['res_module'], $resCloud))){
                        return false;
                    }
                    if(in_array('mf_dcim', $v['module']) && !empty($v['res_module']) && !empty(array_diff($v['res_module'], $resDcim))){
                        return false;
                    }
                    // 也可以只选择云和dcim的代理
                    if(count($v['res_module']) > 1){
                        if(!empty(array_intersect($v['res_module'], $resCloud)) && !empty(array_diff($v['res_module'], $resCloud)) ){
                            return false;
                        }
                        if(!empty(array_intersect($v['res_module'], $resDcim)) && !empty(array_diff($v['res_module'], $resDcim)) ){
                            return false;
                        }
                    }
                }
                
                if(!isset($v['product_id'])){
                    return false;
                }
                if(!is_array($v['product_id'])){
                    return false;
                }
                foreach ($v['product_id'] as $vv) {
                    if(!is_integer($vv)){
                        return false;
                    }
                }
                $productId = array_merge($productId, $v['product_id']);
                $count+=count($v['product_id']);
            }else{
                if(!isset($v['nav_id'])){
                    return false;
                }
                if(!is_integer($v['nav_id'])){
                    return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                }
                if($v['nav_id']<=0){
                    return lang('nav_not_complete_please_input', ['{name}'=>$v['name']]);
                }
                $navId[] = $v['nav_id'];
            }
            if(!isset($v['child'])){
                return false;
            }
            if(!is_array($v['child'])){
                return false;
            }
            if(!empty($v['child'])){
                foreach ($v['child'] as $ck => $cv) {
                    if(!isset($cv['type'])){
                        return false;
                    }
                    if(!in_array($cv['type'], ['system', 'plugin', 'custom', 'module','embedded'])){
                        return false;
                    }
                    if(!isset($cv['name'])){
                        return false;
                    }
                    if(!is_string($cv['name'])){
                        return false;
                    }
                    if($cv['name'] === ''){
                        return 'nav_name_no_input';
                    }
                    if(mb_strlen($cv['name'])>20){
                        return false;
                    }
                    if(!is_array($cv['language'])){
                        return false;
                    }
                    if($cv['type']=='custom'){
                        if(!isset($cv['url'])){
                            return false;
                        }
                        if(!is_string($cv['url'])){
                            return false;
                        }
                        if(strlen($cv['url'])>255){
                            return false;
                        }
                    }else if(in_array($cv['type'], ['module'])){
                        $cv['module'] = $cv['module'] ?? [];
                        $cv['res_module'] = $cv['res_module'] ?? [];
                        $cv['is_cross_module'] = $cv['is_cross_module'] ?? 0;
                        $cv['select_field'] = $cv['select_field'] ?? [];
                        if(!in_array($cv['is_cross_module'], ['0', '1'])){
                            return false;
                        }
                        if($cv['is_cross_module']==1){
                            if(!is_array($cv['select_field']) || !empty(array_diff($cv['select_field'], $selectField))){
                                return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                            }

                            if(!is_array($cv['module']) || !is_array($cv['res_module'])){
                                return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                            }
                        }else{
                            if(!is_array($cv['module']) || count($cv['module'])>1 || !is_array($cv['res_module'])){
                                return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                            }
                            // 不能同时为空
                            if(empty($cv['module']) && empty($cv['res_module'])){
                                return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                            }
                            if(in_array('mf_cloud', $cv['module']) && !empty($cv['res_module']) && !empty(array_diff($cv['res_module'], $resCloud))){
                                return false;
                            }
                            if(in_array('mf_dcim', $cv['module']) && !empty($cv['res_module']) && !empty(array_diff($cv['res_module'], $resDcim))){
                                return false;
                            }
                            // 也可以只选择云和dcim的代理
                            if(count($cv['res_module']) > 1){
                                if(!empty(array_intersect($cv['res_module'], $resCloud)) && !empty(array_diff($cv['res_module'], $resCloud)) ){
                                    return false;
                                }
                                if(!empty(array_intersect($cv['res_module'], $resDcim)) && !empty(array_diff($cv['res_module'], $resDcim)) ){
                                    return false;
                                }
                            }
                        }
                        if(!isset($cv['product_id'])){
                            return false;
                        }
                        if(!is_array($cv['product_id'])){
                            return false;
                        }
                        foreach ($cv['product_id'] as $vv) {
                            if(!is_integer($vv)){
                                return false;
                            }
                        }
                        $productId = array_merge($productId, $cv['product_id']);
                        $count+=count($cv['product_id']);
                    }else{
                        if(!isset($cv['nav_id'])){
                            return false;
                        }
                        if(!is_integer($cv['nav_id'])){
                            return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                        }
                        if($cv['nav_id']<=0){
                            return lang('nav_not_complete_please_input', ['{name}'=>$v['name'] . '-' . $cv['name']]);
                        }
                        $navId[] = $cv['nav_id'];
                    }
                }
            }  
        }
        // if($count!=count(array_filter(array_unique($productId)))){
        //     return 'nav_product_cannot_repeat_add';
        // }
        if(count($navId)!=count(array_unique($navId))){
            return 'nav_cannot_repeat_add';
        }
        return true;
    }
}
<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;

/**
 * @title 限制规则模型
 * @use server\mf_cloud\model\LimitRuleModel
 */
class LimitRuleModel extends Model
{
	protected $name = 'module_mf_cloud_limit_rule';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rule'          => 'string',
        'result'        => 'string',
        'rule_md5'      => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'upstream_id'   => 'int',
    ];

    /**
     * 时间 2024-05-10
     * @title 添加限制规则
     * @desc  添加限制规则
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.memory.value - 内存值(当内存是单选时)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu[].value - CPU
     * @param   string param.result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory[].min - 内存最小值
     * @param   string param.result.memory[].max - 内存最大值
     * @param   string param.result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.memory[].value - 内存值(当内存是单选时)
     * @param   array param.result.image[].id - 操作系统ID
     * @param   string param.result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.system_disk[].min - 系统盘最小值
     * @param   string param.result.system_disk[].max - 系统盘最大值
     * @param   string param.result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 限制规则ID
     */
    public function limitRuleCreate($param): array
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;

        $limitRuleRule = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['rule'],
        ]);
        if($limitRuleRule['status'] == 400){
            return $limitRuleRule;
        }
        $limitRuleResult = $this->limitRuleResultCheckAndFormat([
            'product_id' => $productId,
            'result'     => $param['result'],
        ]);
        if($limitRuleResult['status'] == 400){
            return $limitRuleResult;
        }

        $rule = json_encode($limitRuleRule['data']);
        $ruleMd5 = md5($rule);
        // 是否已存在
        $exist = $this
                ->where('product_id', $productId)
                ->where('rule_md5', $ruleMd5)
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_exist_the_same_rule')];
        }

        $insert = [
            'product_id' => $ProductModel->id,
            'rule'       => $rule,
            'result'     => json_encode($limitRuleResult['data']),
            'rule_md5'   => $ruleMd5,
            'create_time'=> time(),
        ];

        $limitRule = $this->create($insert);

        $description = lang_plugins('log_mf_cloud_limit_rule_create_success', [
            '{name}'    => 'product#'.$productId.'#'.$ProductModel->name.'#',
            '{rule}'    => $this->limitRuleDescription([
                'product_id'    => $param['product_id'],
                'rule'          => $limitRuleRule['data'],
                'result'        => $limitRuleResult['data'],
            ]),
        ]);

        active_log($description, 'product', $ProductModel->id);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$limitRule->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2024-05-10
     * @title 修改限制规则
     * @desc  修改限制规则
     * @author hh
     * @version v1
     * @param   int param.id - 限制规则ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.memory.value - 内存值(当内存是单选时)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu[].value - CPU
     * @param   string param.result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory[].min - 内存最小值
     * @param   string param.result.memory[].max - 内存最大值
     * @param   string param.result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.memory[].value - 内存值(当内存是单选时)
     * @param   array param.result.image[].id - 操作系统ID
     * @param   string param.result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.system_disk[].min - 系统盘最小值
     * @param   string param.result.system_disk[].max - 系统盘最大值
     * @param   string param.result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function limitRuleUpdate($param)
    {
        $limitRule = $this->find($param['id']);
        if(empty($limitRule)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_not_found')];
        }
        $ProductModel = ProductModel::find($limitRule['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;

        $limitRuleRule = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['rule'],
        ]);
        if($limitRuleRule['status'] == 400){
            return $limitRuleRule;
        }
        $limitRuleResult = $this->limitRuleResultCheckAndFormat([
            'product_id' => $productId,
            'result'     => $param['result'],
        ]);
        if($limitRuleResult['status'] == 400){
            return $limitRuleResult;
        }

        $rule = json_encode($limitRuleRule['data']);
        $ruleMd5 = md5($rule);
        // 是否已存在
        $exist = $this
                ->where('product_id', $productId)
                ->where('rule_md5', $ruleMd5)
                ->where('id', '<>', $limitRule->id)
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_exist_the_same_rule')];
        }

        $this->update([
            'rule'          => $rule,
            'result'        => json_encode($limitRuleResult['data']),
            'rule_md5'      => $ruleMd5,
            'update_time'   => time(),
        ], ['id'=>$limitRule['id']]);

        $oldRule = $this->limitRuleDescription([
            'product_id' => $limitRule['product_id'],
            'rule'       => json_decode($limitRule['rule'], true),
            'result'     => json_decode($limitRule['result'], true),
        ]);
        $newRule = $this->limitRuleDescription([
            'product_id' => $limitRule['product_id'],
            'rule'       => $limitRuleRule['data'],
            'result'     => $limitRuleResult['data'],
        ]);

        if($oldRule != $newRule){
            $description = lang_plugins('log_mf_cloud_limit_rule_update_success', [
                '{name}'    => 'product#'.$productId.'#'.$ProductModel->name.'#',
                '{old}'     => $oldRule,
                '{new}'     => $newRule,
            ]);

            active_log($description, 'product', $ProductModel->id);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-05-10
     * @title 限制规则列表
     * @desc  限制规则列表
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int list[].id - 限制规则ID
     * @return  array list[].rule.cpu.value - CPU
     * @return  string list[].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.memory.min - 内存最小值
     * @return  string list[].rule.memory.max - 内存最大值
     * @return  string list[].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.memory.value - 内存值(当内存是单选时)
     * @return  array list[].rule.data_center.id - 数据中心ID
     * @return  array list[].rule.data_center.name - 数据中心名称
     * @return  string list[].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.image.id - 操作系统ID
     * @return  array list[].rule.image.name - 操作系统名称
     * @return  string list[].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.cpu[].value - CPU
     * @return  string list[].result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.memory[].min - 内存最小值
     * @return  string list[].result.memory[].max - 内存最大值
     * @return  string list[].result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.memory[].value - 内存值(当内存是单选时)
     * @return  array list[].result.image[].id - 操作系统ID
     * @return  array list[].result.image[].name - 操作系统名称
     * @return  string list[].result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.system_disk[].min - 系统盘最小值
     * @return  string list[].result.system_disk[].max - 系统盘最大值
     * @return  string list[].result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     */
    public function limitRuleList($param)
    {   
        $where = [];
        if(isset($param['product_id']) && !empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $dataCenter = [];
        $image = [];

        $list = $this
                ->field('id,rule,result')
                ->where($where)
                ->order('id', 'asc')
                ->select()
                ->toArray();

        foreach($list as $k=>$v){
            $v['rule'] = json_decode($v['rule'], true);
            $v['result'] = json_decode($v['result'], true);

            if(isset($v['rule']['data_center']) || isset($v['result']['data_center'])){
                if(empty($dataCenter)){
                    $dataCenterList = DataCenterModel::field('id,country_id,city,area')->where($where)->select();
                    foreach($dataCenterList as $vv){
                        $dataCenter[ $vv['id'] ] = $vv->getDataCenterName();
                    }
                }
            }
            if(isset($v['rule']['image']) || isset($v['result']['image'])){
                if(empty($image)){
                    $image = ImageModel::field('id,name')->where($where)->select()->toArray();
                    $image = array_column($image, 'name', 'id');
                }
            }

            // 获取显示
            if(isset($v['rule']['data_center']['id'])){
                $name = [];
                foreach($v['rule']['data_center']['id'] as $vv){
                    if(isset($dataCenter[ $vv ])){
                        $name[] = $dataCenter[$vv];
                    }
                }
                $v['rule']['data_center']['name'] = $name;
            }
            if(isset($v['rule']['image']['id'])){
                $name = [];
                foreach($v['rule']['image']['id'] as $vv){
                    if(isset($image[ $vv ])){
                        $name[] = $image[$vv];
                    }
                }
                $v['rule']['image']['name'] = $name;
            }
            // 获取显示
            if(isset($v['result']['image'])){
                foreach($v['result']['image'] as $kk=>$resultItem){
                    if(isset($resultItem['id'])){
                        $name = [];
                        foreach($resultItem['id'] as $vv){
                            if(isset($image[ $vv ])){
                                $name[] = $image[$vv];
                            }
                        }
                        $v['result']['image'][$kk]['name'] = $name;
                    }
                }
            }
            $list[$k]['rule'] = $v['rule'];
            $list[$k]['result'] = $v['result'];
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2024-05-10
     * @title 删除限制规则
     * @desc  删除限制规则
     * @author hh
     * @version v1
     * @param   int id - 限制规则ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function limitRuleDelete($id)
    {
        $limitRule = $this->find($id);
        if(empty($limitRule)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_not_found')];
        }
        $limitRule->delete();

        $product = ProductModel::find($limitRule['product_id']);
        $rule = $this->limitRuleDescription([
            'product_id'    => $limitRule['product_id'],
            'rule'          => json_decode($limitRule['rule'], true),
            'result'        => json_decode($limitRule['result'], true),
        ]);

        $description = lang_plugins('log_mf_cloud_limit_rule_delete_success', [
            '{name}'    => 'product#'.$product->id.'#'.$product->name.'#',
            '{rule}'    => $rule,
        ]);
        active_log($description, 'product', $limitRule['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-05-11
     * @title 生成限制规则描述
     * @desc  生成限制规则描述
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.memory.value - 内存值(当内存是单选时)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu[].value - CPU
     * @param   string param.result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory[].min - 内存最小值
     * @param   string param.result.memory[].max - 内存最大值
     * @param   string param.result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.memory[].value - 内存值(当内存是单选时)
     * @param   array param.result.image[].id - 操作系统ID
     * @param   string param.result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.system_disk[].min - 系统盘最小值
     * @param   string param.result.system_disk[].max - 系统盘最大值
     * @param   string param.result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string
     */
    public function limitRuleDescription($param)
    {
        $desc = [
            'cpu'               => 'CPU',
            'memory'            => lang_plugins('memory'),
            'data_center'       => lang_plugins('data_center'),
            'ipv4_num'          => lang_plugins('mf_cloud_option_value_5'),
            // 'ipv6_num'          => lang_plugins('mf_cloud_ipv6_num'),
            'bw'                => lang_plugins('bw'),
            'flow'              => lang_plugins('flow'),
            'system_disk'       => lang_plugins('system_disk'),
            // 'data_disk'         => lang_plugins('data_disk'),
            'image'             => lang_plugins('mf_cloud_os'),
            // 'recommend_config'  => lang_plugins('mf_cloud_recommend_config'),
            // 'duration'          => lang_plugins('mf_cloud_duration'),
        ];

        $ruleDesc = [];   // 条件
        $resultDesc = []; // 结果

        $opt = [
            'eq'  => lang_plugins('mf_cloud_limit_rule_eq'),
            'neq' => lang_plugins('mf_cloud_limit_rule_neq'),
        ];
        $ruleType = ['data_center','cpu','memory','image','ipv4_num','bw','flow','system_disk'];

        // 按照顺序生成
        foreach($ruleType as $type){
            if(isset($param['rule'][$type])){
                $ruleItem = $param['rule'][$type];
                if($type == 'cpu'){
                    $ruleDesc[] = 'CPU' . $opt[ $ruleItem['opt'] ] . implode(',', $ruleItem['value']);
                }else if(in_array($type, ['memory'])){
                    // 内存多选
                    if(isset($ruleItem['value']) && is_array($ruleItem['value'])){
                        $ruleDesc[] = lang_plugins('memory') . $opt[ $ruleItem['opt'] ] . implode(',', $ruleItem['value']);
                    }else{
                        // 内存范围
                        $min = strval($ruleItem['min'] ?? '');
                        $max = strval($ruleItem['max'] ?? '');

                        $ruleDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                            '{type}'    => $desc[ $type ],
                            '{opt}'     => $opt[ $ruleItem['opt'] ],
                            '{min}'     => $min === '' ? lang_plugins('null') : $min,
                            '{max}'     => $max === '' ? lang_plugins('null') : $max,
                        ]);
                    }
                }else if($type == 'data_center'){
                    $id = $ruleItem['id'];
                    $name = [];

                    $dataCenter = DataCenterModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->select();
                    foreach($dataCenter as $v){
                        $name[] = $v->getDataCenterName();
                    }

                    $ruleDesc[] = $desc[$type] . $opt[ $ruleItem['opt'] ] . implode(',', $name);
                }else if($type == 'image'){
                    $id = $ruleItem['id'];
                    $name = ImageModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                    $ruleDesc[] = $desc[$type] . $opt[ $ruleItem['opt'] ] . implode(',', $name);
                }else if(in_array($type, ['ipv4_num','bw','flow'])){
                    $min = strval($ruleItem['min'] ?? '');
                    $max = strval($ruleItem['max'] ?? '');

                    $ruleDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                        '{type}'    => $desc[ $type ],
                        '{opt}'     => $opt[ $ruleItem['opt'] ],
                        '{min}'     => $min === '' ? lang_plugins('null') : $min,
                        '{max}'     => $max === '' ? lang_plugins('null') : $max,
                    ]);
                }
                // }else if($type == 'recommend_config'){
                //     $id = $ruleItem['id'];
                //     $name = RecommendConfigModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                //     $ruleDesc[] = $desc[$type] . $opt[ $ruleItem['opt'] ] . implode(',', $name);
                // }else if($type == 'duration'){
                //     $id = $ruleItem['id'];
                //     $name = DurationModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');

                //     $ruleDesc[] = $desc[$type] . $opt[ $ruleItem['opt'] ] . implode(',', $name);
                // }
            }
            if(isset($param['result'][$type])){
                foreach($param['result'][$type] as $resultItem){
                    if($type == 'cpu'){
                        $resultDesc[] = 'CPU' . $opt[ $resultItem['opt'] ] . implode(',', $resultItem['value']);
                    }else if(in_array($type, ['memory'])){
                        // 内存多选
                        if(isset($resultItem['value']) && is_array($resultItem['value'])){
                            $resultDesc[] = lang_plugins('memory') . $opt[ $resultItem['opt'] ] . implode(',', $resultItem['value']);
                        }else{
                            $min = strval($resultItem['min'] ?? '');
                            $max = strval($resultItem['max'] ?? '');

                            $resultDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                                '{type}'    => $desc[ $type ],
                                '{opt}'     => $opt[ $resultItem['opt'] ],
                                '{min}'     => $min === '' ? lang_plugins('null') : $min,
                                '{max}'     => $max === '' ? lang_plugins('null') : $max,
                            ]);
                        }
                    }else if($type == 'image'){
                        $id = $resultItem['id'];
                        $name = ImageModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                        
                        $resultDesc[] = $desc[$type] . $opt[ $resultItem['opt'] ] . implode(',', $name);
                    }else if(in_array($type, ['system_disk','ipv4_num','bw','flow'])){
                        $min = strval($resultItem['min'] ?? '');
                        $max = strval($resultItem['max'] ?? '');

                        $resultDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                            '{type}'    => $desc[ $type ],
                            '{opt}'     => $opt[ $resultItem['opt'] ],
                            '{min}'     => $min === '' ? lang_plugins('null') : $min,
                            '{max}'     => $max === '' ? lang_plugins('null') : $max,
                        ]);
                    }
                    // }else if($type == 'recommend_config'){
                    //     $id = $param['result'][$type]['id'];
                    //     $name = RecommendConfigModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                        
                    //     $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                    // }else if($type == 'duration'){
                    //     $id = $param['result'][$type]['id'];
                    //     $name = DurationModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');

                    //     $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                    // }
                }
            }
        }

        $description = lang_plugins('mf_cloud_limit_rule_description', [
            '{rule}'    => implode(',', $ruleDesc),
            '{result}'  => implode(',', $resultDesc),
        ]);
        return $description;
    }
    
    /**
     * 时间 2024-05-11
     * @title 前台限制规则列表
     * @desc  前台限制规则列表
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int [].id - 限制规则ID
     * @return  array [].rule - 条件数据
     * @return  array [].rule.cpu.value - CPU
     * @return  string [].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string [].rule.memory.min - 内存最小值
     * @return  string [].rule.memory.max - 内存最大值
     * @return  string [].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].rule.memory.value - 内存值(当内存是单选时)
     * @return  array [].rule.data_center.id - 数据中心ID
     * @return  string [].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].rule.image.id - 操作系统ID
     * @return  string [].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result - 结果数据
     * @return  array [].result.cpu[].value - CPU
     * @return  string [].result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string [].result.memory[].min - 内存最小值
     * @return  string [].result.memory[].max - 内存最大值
     * @return  string [].result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result.memory[].value - 内存值(当内存是单选时)
     * @return  array [].result.data_center[].id - 数据中心ID
     * @return  string [].result.data_center[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result.image[].id - 操作系统ID
     * @return  string [].result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string [].result.system_disk[].min - 系统盘最小值
     * @return  string [].result.system_disk[].max - 系统盘最大值
     * @return  string [].result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     */
    public function homeLimitRule($product_id)
    {
        $data = $this
            ->field('id,rule,result')
            ->where('product_id', $product_id)
            ->withAttr('rule', function($val){
                return json_decode($val, true);
            })
            ->withAttr('result', function($val){
                return json_decode($val, true);
            })
            ->order('id', 'asc')
            ->select()
            ->toArray();
        return $data;
    }

    /**
     * 时间 2024-05-11
     * @title 验证参数是否在范围内
     * @desc  验证参数是否在范围内
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   array param    - 要验证的参数 require
     * @param   int param.cpu  - CPU
     * @param   int param.memory - 内存
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.ip_num - IPv4数量
     * @param   int param.ipv6_num - IPv6数量
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.system_disk.size - 系统盘大小
     * @param   int param.data_disk[].size - 数据盘大小
     * @param   int param.image_id - 操作系统ID
     * @param   int param.recommend_config_id - 套餐ID
     * @param   int param.duration_id - 周期ID
     * @param   int param.line_id - 线路ID
     * @param   array param.checkResult - 需要验证的结果类型,不传表示全部
     */
    public function checkLimitRule($product_id, $param, $checkResult = []): array
    {
        $limitRule = $this->homeLimitRule($product_id);
        if(!empty($limitRule)){
            $lineType = 'bw';
            if(isset($param['line_id']) && !empty($param['line_id'])){
                $line = LineModel::find($param['line_id']);
                if(!empty($line)){
                    $lineType = $line['bill_type'];
                    $param['data_center_id'] = $line['data_center_id'];
                }
            }
            $param['data_center_id'] = $param['data_center_id'] ?? 0;
            $param['cpu'] = $param['cpu'] ?? 0;
            $param['memory'] = $param['memory'] ?? 0;
            $param['image_id'] = $param['image_id'] ?? 0;
            $param['system_disk']['size'] = $param['system_disk']['size'] ?? 0;
            $param['ip_num'] = $param['ip_num'] ?? 0;
            $param['bw'] = $param['bw'] ?? NULL;
            $param['flow'] = $param['flow'] ?? NULL;
            $param['line_type'] = $lineType;

            // 从上到下依次匹配
            if(isset($param['recommend_config_id']) && !empty($param['recommend_config_id'])){
                // 套餐暂无验证


            }else{
                foreach($limitRule as $v){
                    // 匹配条件
                    $matchRule = $this->limitRuleMatch($v['rule'], $param);
                    if($matchRule){
                        // 匹配结果
                        $matchResult = $this->limitRuleResultMatch($v['result'], $param, $checkResult);
                        if(!$matchResult){
                            return ['status'=>400, 'msg'=>lang_plugins('cannot_select_this_config') ];
                        }
                    }
                }
            }
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-05-24
     * @title 限制规则是否在规则内
     * @desc  限制规则是否在规则内
     * @author hh
     * @version v1
     * @param   array rule - 条件单条数据 require
     * @param   array rule.cpu.value - CPU
     * @param   string rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.memory.min - 内存最小值
     * @param   string rule.memory.max - 内存最大值
     * @param   string rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.memory.value - 内存值(当内存是单选时)
     * @param   array rule.data_center.id - 数据中心ID
     * @param   string rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.image.id - 操作系统ID
     * @param   string rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.cpu - CPU
     * @param   int param.memory - 内存
     * @param   int param.image_id - 操作系统ID
     * @param   array checkRule - 需要验证的类型,不在的算通过,通常在部分结果匹配
     * @param   string lineType - 线路类型(bw=带宽线路,flow=流量线路)
     * @return  bool
     */
    public function limitRuleMatch($rule, $param, $checkRule = [])
    {
        $param['data_center_id'] = $param['data_center_id'] ?? 0;
        $param['cpu'] = $param['cpu'] ?? 0;
        $param['memory'] = $param['memory'] ?? 0;
        $param['image_id'] = $param['image_id'] ?? 0;
        $param['ipv4_num'] = $param['ip_num'] ?? NULL;
        $param['bw'] = $param['bw'] ?? NULL;
        $param['flow'] = $param['flow'] ?? NULL;

        $matchNum = 0;  // 匹配条件数量
        // 匹配条件
        if(isset($rule['data_center'])){
            if(!empty($checkRule) && !in_array('data_center', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['data_center_id'], $rule['data_center']['id']);
                if($rule['data_center']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['cpu'])){
            if(!empty($checkRule) && !in_array('cpu', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['cpu'], $rule['cpu']['value']);
                if($rule['cpu']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['memory'])){
            if(!empty($checkRule) && !in_array('memory', $checkRule)){
                $matchNum++;
            }else{
                // 内存单选
                if(isset($rule['memory']['value']) && is_array($rule['memory']['value'])){
                    $match = in_array($param['memory'], $rule['memory']['value']);
                }else{
                    $min = intval($rule['memory']['min'] ?: 0);
                    $max = intval($rule['memory']['max'] ?: 99999999);
                    $match = $param['memory'] >= $min && $param['memory'] <= $max;
                }
                if($rule['memory']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['image'])){
            if(!empty($checkRule) && !in_array('image', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['image_id'], $rule['image']['id']);
                if($rule['image']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['ipv4_num'])){
            if(!empty($checkRule) && !in_array('ipv4_num', $checkRule)){
                $matchNum++;
            }else{
                $min = intval($rule['ipv4_num']['min'] ?: 0);
                $max = intval($rule['ipv4_num']['max'] ?: 99999999);
                $match = $param['ipv4_num'] >= $min && $param['ipv4_num'] <= $max;
                if($rule['ipv4_num']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(!empty($param['line_type'])){
            if($param['line_type'] == 'bw' && isset($rule['bw'])){
                if(!empty($checkRule) && !in_array('bw', $checkRule)){
                    $matchNum++;
                }else{
                    $min = intval($rule['bw']['min'] ?: 0);
                    $max = intval($rule['bw']['max'] ?: 99999999);
                    $match = $param['bw'] >= $min && $param['bw'] <= $max;
                    if($rule['bw']['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match) $matchNum++;
                }
            }
            if($param['line_type'] == 'flow' && isset($rule['flow'])){
                if(!empty($checkRule) && !in_array('flow', $checkRule)){
                    $matchNum++;
                }else{
                    $min = intval($rule['flow']['min'] ?: 0);
                    $max = intval($rule['flow']['max'] ?: 99999999);
                    $match = $param['flow'] >= $min && $param['flow'] <= $max;
                    if($rule['flow']['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match) $matchNum++;
                }
            }
        }
        return $matchNum === count($rule);
    }

    /**
     * 时间 2024-07-02
     * @title 限制规则是否在结果内
     * @desc  限制规则是否在结果内
     * @author hh
     * @version v1
     * @param   array rule - 结果单条数据 require
     * @param   array rule.cpu[].value - CPU
     * @param   string rule.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.memory[].min - 内存最小值
     * @param   string rule.memory[].max - 内存最大值
     * @param   string rule.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.memory[].value - 内存值(当内存是单选时)
     * @param   array rule.data_center[].id - 数据中心ID
     * @param   string rule.data_center[].opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.image[].id - 操作系统ID
     * @param   string rule.image[].opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.system_disk[].min - 系统盘最小值
     * @param   string rule.system_disk[].max - 系统盘最大值
     * @param   string rule.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.cpu - CPU
     * @param   int param.memory - 内存
     * @param   int param.image_id - 操作系统ID
     * @param   int param.system_disk.size - 系统盘大小
     * @param   array param.checkRule - 需要验证的类型,不在的算通过,通常在部分结果匹配
     * @return  bool
     */
    public function limitRuleResultMatch($rule, $param, $checkRule = [])
    {
        $param['data_center_id'] = $param['data_center_id'] ?? 0;
        $param['cpu'] = $param['cpu'] ?? 0;
        $param['memory'] = $param['memory'] ?? 0;
        $param['image_id'] = $param['image_id'] ?? 0;
        $param['ipv4_num'] = $param['ip_num'] ?? NULL;
        $param['bw'] = $param['bw'] ?? NULL;
        $param['flow'] = $param['flow'] ?? NULL;
        $systemDiskSize = $param['system_disk']['size'] ?? 0;

        $matchNum = 0;  // 匹配结果数量
        // 匹配结果
        if(isset($rule['cpu'])){
            if(!empty($checkRule) && !in_array('cpu', $checkRule)){
                $matchNum++;
            }else{
                foreach($rule['cpu'] as $resultItem){
                    $match = in_array($param['cpu'], $resultItem['value']);
                    if($resultItem['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match){
                        $matchNum++;
                        break;
                    }
                }
            }
        }
        if(isset($rule['memory'])){
            if(!empty($checkRule) && !in_array('memory', $checkRule)){
                $matchNum++;
            }else{
                foreach($rule['memory'] as $resultItem){
                    // 内存单选
                    if(isset($resultItem['value']) && is_array($resultItem['value'])){
                        $match = in_array($param['memory'], $resultItem['value']);
                    }else{
                        $min = intval($resultItem['min'] ?: 0);
                        $max = intval($resultItem['max'] ?: 99999999);
                        $match = $param['memory'] >= $min && $param['memory'] <= $max;
                    }
                    if($resultItem['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match){
                        $matchNum++;
                        break;
                    }
                }
            }
        }
        if(isset($rule['image'])){
            if(!empty($checkRule) && !in_array('image', $checkRule)){
                $matchNum++;
            }else{
                foreach($rule['image'] as $resultItem){
                    $match = in_array($param['image_id'], $resultItem['id']);
                    if($resultItem['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match){
                        $matchNum++;
                        break;
                    }

                }
            }
        }
        if(isset($rule['system_disk'])){
            if(!empty($checkRule) && !in_array('system_disk', $checkRule)){
                $matchNum++;
            }else{
                foreach($rule['system_disk'] as $resultItem){
                    $min = intval($resultItem['min'] ?: 0);
                    $max = intval($resultItem['max'] ?: 99999999);
                    $match = $systemDiskSize >= $min && $systemDiskSize <= $max;
                    if($resultItem['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match){
                        $matchNum++;
                        break;
                    }
                }
            }
        }
        if(isset($rule['ipv4_num'])){
            if(!empty($checkRule) && !in_array('ipv4_num', $checkRule)){
                $matchNum++;
            }else{
                foreach($rule['ipv4_num'] as $resultItem){
                    $min = intval($resultItem['min'] ?: 0);
                    $max = intval($resultItem['max'] ?: 99999999);
                    $match = $param['ipv4_num'] >= $min && $param['ipv4_num'] <= $max;
                    if($resultItem['opt'] == 'neq'){
                        $match = !$match;
                    }
                    if($match){
                        $matchNum++;
                        break;
                    }
                }
            }
        }
        if(!empty($param['line_type'])){
            if($param['line_type'] == 'bw'){
                if(isset($rule['bw'])){
                    if(!empty($checkRule) && !in_array('bw', $checkRule)){
                        $matchNum++;
                    }else{
                        foreach($rule['bw'] as $resultItem){
                            $min = intval($resultItem['min'] ?: 0);
                            $max = intval($resultItem['max'] ?: 99999999);
                            $match = $param['bw'] >= $min && $param['bw'] <= $max;
                            if($resultItem['opt'] == 'neq'){
                                $match = !$match;
                            }
                            if($match){
                                $matchNum++;
                                break;
                            }
                        }
                    }
                }
                // 带宽线路不算流量规则
                if(isset($rule['flow'])){
                    unset($rule['flow']);
                }
            }
            if($param['line_type'] == 'flow'){
                if(isset($rule['flow'])){
                    if(!empty($checkRule) && !in_array('flow', $checkRule)){
                        $matchNum++;
                    }else{
                        foreach($rule['flow'] as $resultItem){
                            $min = intval($resultItem['min'] ?: 0);
                            $max = intval($resultItem['max'] ?: 99999999);
                            $match = $param['flow'] >= $min && $param['flow'] <= $max;
                            if($resultItem['opt'] == 'neq'){
                                $match = !$match;
                            }
                            if($match){
                                $matchNum++;
                                break;
                            }
                        }
                    }
                }
                // 流量线路不算带宽规则
                if(isset($rule['bw'])){
                    unset($rule['bw']);
                }
            }
        }
        return $matchNum === count($rule);
    }

    /**
     * 时间 2024-05-24
     * @title 验证并格式化规则
     * @desc  验证并格式化规则
     * @author hh
     * @version v1
     * @param   array param.rule - 格式化的规则 require
     * @param   int param.product_id - 商品ID require
     */
    public function limitRuleCheckAndFormat($param)
    {   
        $rule = $param['rule'] ?? [];
        $productId = $param['product_id'] ?? 0;
        
        $memoryType = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::MEMORY)->value('type');
        $ruleType = ['data_center','cpu','memory','image','ipv4_num','bw','flow'];

        $data = [];
        foreach($ruleType as $v){
            if(isset($rule[$v])){
                $ruleItem = $rule[$v];
                if($v == 'cpu'){
                    // 验证CPU选项
                    $cpu = OptionModel::where('product_id', $productId)
                        ->where('rel_type', OptionModel::CPU)
                        ->whereIn('value', $ruleItem['value'])
                        ->column('value');
                    if(count($cpu) != count($ruleItem['value'])){
                        return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
                    }
                    foreach($cpu as $kk=>$vv){
                        $cpu[$kk] = (int)$vv;
                    }
                    sort($cpu);

                    $data[$v]['value'] = $cpu;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if(in_array($v, ['memory'])){
                    if(empty($memoryType)){
                        return ['status'=>400, 'msg'=>lang_plugins('please_add_memory_config_first')];
                    }
                    if($memoryType == 'radio'){
                        if(!isset($ruleItem['value'])){
                            return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                        }
                        // 验证内存选项
                        $memory = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::MEMORY)->whereIn('value', $ruleItem['value'])->column('value');
                        if(count($memory) != count($ruleItem['value'])){
                            return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                        }
                        foreach($memory as $kk=>$vv){
                            $memory[$kk] = (int)$vv;
                        }
                        sort($memory);

                        $data[$v]['value'] = $memory;
                    }else{
                        // 范围
                        $data[$v]['min'] = strval($ruleItem['min'] ?? '');
                        $data[$v]['max'] = strval($ruleItem['max'] ?? '');
                    }
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'data_center'){
                    // 验证数据中心
                    $dataCenterId = DataCenterModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($dataCenterId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
                    }

                    $data[$v]['id'] = $dataCenterId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'image'){
                    // 验证操作系统
                    $imageId = ImageModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($imageId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_os_not_found')];
                    }

                    $data[$v]['id'] = $imageId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if(in_array($v, ['ipv4_num','bw','flow'])){
                    // 范围类型
                    $data[$v]['min'] = strval($ruleItem['min'] ?? '');
                    $data[$v]['max'] = strval($ruleItem['max'] ?? '');
                    $data[$v]['opt'] = $ruleItem['opt'];
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2024-07-02
     * @title 验证并格式化结果
     * @desc  验证并格式化结果
     * @author hh
     * @version v1
     * @param   array param.result - 格式化的结果 require
     * @param   int param.product_id - 商品ID require
     */
    public function limitRuleResultCheckAndFormat($param)
    {   
        $rule = $param['result'] ?? [];
        $productId = $param['product_id'] ?? 0;
    
        $memoryType = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::MEMORY)->value('type');
        $ruleType = ['cpu','memory','image','system_disk','ipv4_num','bw','flow'];

        $data = [];
        foreach($ruleType as $v){
            if(isset($rule[$v])){
                $ruleArr = $rule[$v];
                foreach($ruleArr as $ruleItem){
                    if($v == 'cpu'){
                        // 验证CPU选项
                        $cpu = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::CPU)->whereIn('value', $ruleItem['value'])->column('value');
                        if(count($cpu) != count($ruleItem['value'])){
                            return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
                        }
                        foreach($cpu as $kk=>$vv){
                            $cpu[$kk] = (int)$vv;
                        }
                        sort($cpu);

                        $data[$v][] = [
                            'value' => $cpu,
                            'opt'   => $ruleItem['opt'],
                        ];
                    }else if(in_array($v, ['memory'])){
                        if(empty($memoryType)){
                            return ['status'=>400, 'msg'=>lang_plugins('please_add_memory_config_first')];
                        }
                        if($memoryType == 'radio'){
                            if(!isset($ruleItem['value'])){
                                return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                            }
                            // 验证内存选项
                            $memory = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::MEMORY)->whereIn('value', $ruleItem['value'])->column('value');
                            if(count($memory) != count($ruleItem['value'])){
                                return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                            }
                            foreach($memory as $kk=>$vv){
                                $memory[$kk] = (int)$vv;
                            }
                            sort($memory);

                            $data[$v][] = [
                                'value' => $memory,
                                'opt'   => $ruleItem['opt'],
                            ];
                        }else{
                            // 范围
                            $data[$v][] = [
                                'min'   => strval($ruleItem['min'] ?? ''),
                                'max'   => strval($ruleItem['max'] ?? ''),
                                'opt'   => $ruleItem['opt'],
                            ];
                        }
                    }else if($v == 'image'){
                        // 验证操作系统
                        $imageId = ImageModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                        if(count($imageId) != count($ruleItem['id'])){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_os_not_found')];
                        }
                        $data[$v][] = [
                            'id'    => $imageId,
                            'opt'   => $ruleItem['opt'],
                        ];
                    }else if(in_array($v, ['system_disk','ipv4_num','bw','flow'])){
                        // 范围
                        $data[$v][] = [
                            'min'   => strval($ruleItem['min'] ?? ''),
                            'max'   => strval($ruleItem['max'] ?? ''),
                            'opt'   => $ruleItem['opt'],
                        ];   
                    }
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2024-07-03
     * @title 获取结果范围可用最小值
     * @desc  获取结果范围可用最小值
     * @author hh
     * @version v1
     * @param   array rule - 单条结果数据
     * @param   int current.min_value - 当前范围最小值
     * @param   int current.max_value - 当前范围最大值
     * @return  int min_value - 可用最小值(NULL表示没有)
     */
    public function getRuleResultUnionIntersectMin($rule, $current = [])
    {
        $leftLimit = 0;
        $rightLimit = 99999999;

        $minValue = NULL;      // 在结果内的最小值
        foreach($rule as $ruleItem){
            $min = $ruleItem['min'] === '' ? $leftLimit : intval($ruleItem['min']);
            $max = $ruleItem['max'] === '' ? $rightLimit : intval($ruleItem['max']);

            if($ruleItem['opt'] == 'eq'){
                $range = [
                    'min'   => $min,
                    'max'   => $max,
                ];
                // 最小值在当前范围
                if($range['min'] >= $current['min_value'] && $range['min'] <= $current['max_value']){
                    $minValue = $range['min'];
                    break;
                }else if($range['max'] >= $current['min_value'] && $range['max'] <= $current['max_value']){
                    // 最大值在当前范围
                    $minValue = $current['min_value'];
                    break;
                }
            }else if($ruleItem['opt'] == 'neq'){
                if($min >= $leftLimit + 1){
                    $range = [
                        'min'   => $leftLimit,
                        'max'   => $min - 1,
                    ];
                    // 最小值在当前范围
                    if($range['min'] >= $current['min_value'] && $range['min'] <= $current['max_value']){
                        $minValue = $range['min'];
                        break;
                    }else if($range['max'] >= $current['min_value'] && $range['max'] <= $current['max_value']){
                        // 最大值在当前范围
                        $minValue = $current['min_value'];
                        break;
                    }
                }
                if($max <= $rightLimit - 1){
                    $range = [
                        'min'   => $max + 1,
                        'max'   => $rightLimit
                    ];
                    // 最小值在当前范围
                    if($range['min'] >= $current['min_value'] && $range['min'] <= $current['max_value']){
                        $minValue = $range['min'];
                        break;
                    }else if($range['max'] >= $current['min_value'] && $range['max'] <= $current['max_value']){
                        // 最大值在当前范围
                        $minValue = $current['min_value'];
                        break;
                    }
                }
            }
        }
        return ['min_value'=>$minValue];
    }



}
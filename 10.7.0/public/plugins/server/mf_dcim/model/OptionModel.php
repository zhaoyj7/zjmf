<?php 
namespace server\mf_dcim\model;

use server\mf_dcim\validate\LineIpValidate;
use think\Model;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\validate\LineBwValidate;
use app\common\model\ProductModel;

/**
 * @title 配置参数模型
 * @use server\mf_dcim\model\OptionModel
 */
class OptionModel extends Model{

	protected $name = 'module_mf_dcim_option';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'rel_type'          => 'int',
        'rel_id'            => 'int',
        'type'              => 'string',
        'value'             => 'string',
        'min_value'         => 'int',
        'max_value'         => 'int',
        'step'              => 'int',
        'order'             => 'int',
        'other_config'      => 'string',
        'create_time'       => 'int',
        'value_show'        => 'string',
        'upstream_id'       => 'int',
        'firewall_type'     => 'string',
        'defence_rule_id'   => 'int',
    ];

    // rel_type常量
    const LINE_BW = 2;
    const LINE_FLOW = 3;
    const LINE_DEFENCE = 4;
    const LINE_IP = 5;
    const CPU = 6;
    const MEMORY = 7;
    const DISK = 8;
    const GPU = 9;
    const GLOBAL_DEFENCE = 10;

    /**
     * 时间 2023-01-31
     * @title 线路带宽配置详情
     * @desc 线路带宽配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string value - 带宽
     * @return  string value_show - 自定义显示
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.in_bw - 流入带宽
     */
    public function lineBwIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_BW){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'type' => $option['type'],
            'value' => $option['value'],
            'value_show' => $option['value_show'],
            'min_value' => $option['min_value'],
            'max_value' => $option['max_value'],
            'step' => $option['step'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路流量配置详情
     * @desc 线路流量配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 流量(GB)
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  int other_config.in_bw - 进带宽
     * @return  int other_config.out_bw - 出带宽
     * @return  string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     */
    public function lineFlowIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_FLOW){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路防护配置详情
     * @desc 线路防护配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 防御峰值(G)
     * @return  string firewall_type - 防火墙类型
     * @return  int defence_rule_id - 防御规则ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function lineDefenceIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_DEFENCE){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'firewall_type' => $option['firewall_type'],
            'defence_rule_id' => $option['defence_rule_id'],
            'duration' => $option['duration'],
            'order' => $option['order']??0,
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路IP配置详情
     * @desc 线路IP配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - IP数量
     * @return  string value_show - 自定义显示
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function lineIpIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_IP){
            return (object)[];
        }
        $data = [
            'id'        => $option['id'],
            'value'     => $option['value'],
            'duration'  => $option['duration'],
            'value_show'=> $option['value_show'],
            'min_value' => $option['min_value'],
            'max_value' => $option['max_value'],
            'type'      => $option['type'],
        ];
        return $data;
    }

    /**
     * 时间 2024-02-08
     * @title 处理器配置详情
     * @desc 处理器配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 处理器
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function cpuIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::CPU){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-11-08
     * @title 内存配置详情
     * @desc 内存配置详情
     * @url /admin/v1/mf_dcim/memory/:id
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 内存
     * @return  int order - 排序
     * @return  int other_config.memory_slot - 内存槽位
     * @return  int other_config.memory - 内存容量(GB)
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function memoryIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::MEMORY){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'other_config'  => $option['other_config'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    public function diskIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::DISK){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-12-25
     * @title 显卡配置详情
     * @desc 显卡配置详情
     * @url /admin/v1/mf_dcim/gpu/:id
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 显卡
     * @return  int order - 排序
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function gpuIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::GPU){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-02-02
     * @title 全局防护配置列表
     * @desc 全局防护配置列表
     * @author theworld
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.defence_data[].id - 配置ID
     * @return  string data.defence_data[].value - 防御峰值(G)
     * @return  string data.defence_data[].price - 价格
     * @return  string data.defence_data[].duration - 周期
     * @return  string data.defence_data[].firewall_type - 防火墙类型
     * @return  int data.defence_data[].defence_rule_id - 防御规则ID
     * @return  string data.defence_data[].defence_rule_name - 防御规则名称
     * @return  string data.defence_data[].defense_peak - 防御峰值
     */
    public function globalDefenceList($param)
    {
        $productId = intval($param['product_id'] ?? 0);
        $relType = $param['rel_type'];
        $relId = $param['rel_id'] ?? 0;

        $param = [];
        $param['product_id'] = $productId;
        $param['sort'] = 'asc';
        $param['page'] = 1;
        $param['limit'] = 999;
        $param['rel_type'] = $relType;
        $param['rel_id'] = $relId;
        $param['orderby'] = 'value';
        $field = 'id,value,firewall_type,defence_rule_id,order';
        $result = $this->optionList($param, $field);

        $ConfigModel = new ConfigModel();
        //$config = $ConfigModel->indexConfig(['product_id' => $productId]);

        //if($config['data']['sync_firewall_rule']==1){
            $rule = $ConfigModel->firewallDefenceRule([
                'product_id'    => $productId,
            ]);

            $firewallRule = [];
            foreach($rule['rule'] as $value){
                foreach ($value['list'] as $v) {
                    $firewallRule[$value['type']][$v['id']] = $v;
                }
            }

            foreach ($result['list'] as $k => $v) {
                if(isset($firewallRule[$v['firewall_type']][$v['defence_rule_id']])){
                    $result['list'][$k]['defence_rule_name'] = $firewallRule[$v['firewall_type']][$v['defence_rule_id']]['name'];
                    $result['list'][$k]['defense_peak'] = $firewallRule[$v['firewall_type']][$v['defence_rule_id']]['defense_peak'];
                }else{
                    $result['list'][$k]['defence_rule_name'] = '';
                    $result['list'][$k]['defense_peak'] = '';
                }
            }
        //}

        $data = ['defence_data' => $result['list']];

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('message_success'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 全局防护配置详情
     * @desc 全局防护配置详情
     * @author theworld
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 防御峰值(G)
     * @return  string firewall_type - 防火墙类型
     * @return  int defence_rule_id - 防御规则ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function globalDefenceIndex($id)
    {
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::GLOBAL_DEFENCE){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'firewall_type' => $option['firewall_type'],
            'defence_rule_id' => $option['defence_rule_id'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-02-02
     * @title 导入防火墙防御规则
     * @desc 导入防火墙防御规则
     * @author theworld
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置) require
     * @param   int param.rel_id - 关联ID(rel_type=2,3,4,5时是线路ID)
     * @param   string param.firewall_type - 防火墙类型 require
     * @param   array param.defence_rule_id - 防御规则ID require
     */
    public function importDefenceRule($param)
    {
        if(in_array($param['rel_type'], [OptionModel::LINE_DEFENCE])){
            $line = LineModel::find($param['rel_id']);
            if(empty($line)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            if($line['sync_firewall_rule']!=1){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_cannot_sync_firewall_rule')];
            }

            $productId = $dataCenter['product_id'];

            $param['rel_id'] = $line['id'];
        }else{
            $productId = $param['product_id'];

            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id' => $productId]);

            if($config['data']['sync_firewall_rule']!=1){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_sync_firewall_rule')];
            }
            $param['rel_id'] = 0;
        }

        $ConfigModel = new ConfigModel();
        $rule = $ConfigModel->firewallDefenceRule([
            'product_id'    => $productId,
        ]);

        $firewallRule = [];
        foreach($rule['rule'] as $v){
            $firewallRule[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
        }

        if(!isset($firewallRule[$param['firewall_type']])){
            throw new \Exception(lang_plugins('mf_dcim_sync_firewall_rule_type_error'));
        }

        $defence = [];
        foreach ($firewallRule[$param['firewall_type']] as $k => $v) {
            if(in_array($k, $param['defence_rule_id'])){
                $defence[$k] = $v;
            }
        }
        if(count($defence)!=count($param['defence_rule_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_firewall_rule_id_error')];
        }

        $exist = $this->where('product_id', $productId)->where('rel_type', $param['rel_type'])->where('rel_id', $param['rel_id'] ?? 0)->select()->toArray();
        if(!empty($exist)){
            $firewallType = array_column($exist, 'firewall_type');
            if($firewallType[0]!=$param['firewall_type']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_firewall_type_error')];
            }
            $defenceRuleId = array_column($exist, 'defence_rule_id');
            if(count(array_intersect($defenceRuleId, $param['defence_rule_id']))>0){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_firewall_rule_defence_exist')];
            }
        }

        $this->startTrans();
        try{
            foreach ($param['defence_rule_id'] as $v) {
                $insert = [
                    'type'              => 'radio',
                    'product_id'        => $productId,
                    'rel_type'          => $param['rel_type'],
                    'rel_id'            => $param['rel_id'],
                    'order'             => $param['order'] ?? 0,
                    'other_config'      => json_encode([]),
                    'value'             => $param['firewall_type'].'_'.$v,
                    'firewall_type'     => $param['firewall_type'],
                    'defence_rule_id'   => $v,
                    'create_time'       => time(),
                ];

                $option = $this->create($insert);

                $optionType = [
                    '',
                    '',
                    lang_plugins('mf_dcim_option_2'),
                    lang_plugins('mf_dcim_option_3'),
                    lang_plugins('mf_dcim_option_4'),
                    lang_plugins('mf_dcim_option_5'),
                    lang_plugins('mf_dcim_option_6'),
                    lang_plugins('mf_dcim_option_7'),
                    lang_plugins('mf_dcim_option_8'),
                    lang_plugins('mf_dcim_option_9'),
                    lang_plugins('mf_dcim_option_10'),
                ];

                $nameType = [
                    '',
                    '',
                    lang_plugins('mf_dcim_option_value_2'),
                    lang_plugins('mf_dcim_option_value_3'),
                    lang_plugins('mf_dcim_option_value_4'),
                    lang_plugins('mf_dcim_option_value_5'),
                    lang_plugins('mf_dcim_option_value_6'),
                    lang_plugins('mf_dcim_option_value_7'),
                    lang_plugins('mf_dcim_option_value_8'),
                    lang_plugins('mf_dcim_option_value_9'),
                    lang_plugins('mf_dcim_option_value_10'),
                ];

                $description = lang_plugins('mf_dcim_log_add_option_success', [
                    '{option}' => $optionType[ $param['rel_type'] ],
                    '{name}' => $nameType[ $param['rel_type'] ],
                    '{detail}' => $defence[$v],
                ]);
                active_log($description, 'product', $productId);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-02
     * @title 添加配置
     * @desc 添加配置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID
     * @param   string param.type radio 配置方式(radio=单选,step=阶梯,total=总量) requireIf:rel_type=2
     * @param   int param.rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置) require
     * @param   int param.rel_id - 关联ID(rel_type=2,3,4,5时是线路ID) require
     * @param   string param.value - 值 requireIf:type=radio
     * @param   string param.value_show - 自定义显示
     * @param   int param.min_value - 最小值 requireIf:type=step/total
     * @param   int param.max_value - 最大值 requireIf:type=radio
     * @param   array param.price - 周期价格(如["5"=>"12"],5是周期ID,12是价格) require
     * @param   array param.other_config - 其他配置
     * @param   string param.other_config.in_bw - 进带宽 rel_type=2可传
     * @param   int param.other_config.in_bw - 进带宽 requireIf:rel_type=3
     * @param   int param.other_config.out_bw - 出带宽 requireIf:rel_type=3
     * @param   string param.other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) requireIf:rel_type=3
     * @param   int param.other_config.memory_slot - 内存槽位 requireIf:rel_type=7
     * @param   int param.other_config.memory - 内存容量(GB) requireIf:rel_type=7
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 配置ID
     */
    public function optionCreate($param)
    {
        if(in_array($param['rel_type'], [OptionModel::LINE_BW,OptionModel::LINE_IP,OptionModel::LINE_DEFENCE,OptionModel::LINE_FLOW])){
            $line = LineModel::find($param['rel_id']);
            if(empty($line)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $productId = $dataCenter['product_id'];

            $param['rel_id'] = $line['id'];
        }else{
            $productId = $param['product_id'];
        }
        
        // 验证周期价格
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
            $type = 'radio';

            $maxOrder = OptionModel::where('product_id',$productId)->where('rel_id',$param['rel_id']??0)->max('order');

            $insert = [
                'product_id'        => $productId,
                'rel_type'          => $param['rel_type'],
                'rel_id'            => $param['rel_id'],
                'order'             => $param['order'] ?? ($maxOrder+1),
                'other_config'      => json_encode([]),
                'create_time'       => time(),
            ];

            if($param['rel_type'] == OptionModel::LINE_BW){
                if($line['bill_type'] != 'bw'){
                    throw new \Exception(lang_plugins('mf_dcim_line_not_bw_cannot_add_bw_rule'));
                }
                $type = $this
                    // ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->where('rel_id', $param['rel_id'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }

                // 先放置在这点
                $LineBwValidate = new LineBwValidate();
                if($type == 'radio'){
                    if (!$LineBwValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }else{
                    if (!$LineBwValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }
                $insert['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                if($line['bill_type'] != 'flow'){
                    throw new \Exception(lang_plugins('mf_dcim_line_not_flow_cannot_add_flow_rule'));
                }
                $insert['other_config'] = json_encode([
                    'in_bw' => (int)$param['other_config']['in_bw'],
                    'out_bw' => (int)$param['other_config']['out_bw'],
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){
                if($line['sync_firewall_rule'] == 1){
                    throw new \Exception(lang_plugins('mf_dcim_line_only_can_sync_firewall_rule'));
                }
            }else if($param['rel_type'] == OptionModel::LINE_IP){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->where('rel_id', $param['rel_id'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }
                // 先放置在这点
                $LineIpValidate = new LineIpValidate();
                if($type == 'radio'){
                    if (!$LineIpValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($LineIpValidate->getError()));
                    }
                }else{
                    if (!$LineIpValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($LineIpValidate->getError()));
                    }
                }
            }else if($param['rel_type'] == OptionModel::CPU){

            }else if($param['rel_type'] == OptionModel::MEMORY){
                $insert['other_config'] = json_encode([
                    'memory_slot'   => (int)$param['other_config']['memory_slot'],
                    'memory'        => (int)$param['other_config']['memory'],
                ]);
            }

            $insert['type'] = $type;
            if($type == 'radio'){
                
                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['value', '=', $param['value']];

                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception( lang_plugins('mf_dcim_already_add_the_same_option') );
                }

                $insert['value'] = $param['value'];

                // 当NC时可以保存自定义显示
                if($insert['value'] == 'NC' && in_array($param['rel_type'], [OptionModel::LINE_IP,OptionModel::LINE_BW])){
                    $insert['value_show'] = $param['value_show'] ?? '';
                }
            }else{

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['min_value', '<=', $param['max_value']];
                $whereSame[] = ['max_value', '>=', $param['min_value']];

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_dcim_option_intersect'));
                }
                $insert['min_value'] = $param['min_value'];
                $insert['max_value'] = $param['max_value'];
                $insert['step'] = 1;
            }
            $option = $this->create($insert);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => 'option',
                        'rel_id'        => $option->id,
                        'duration_id'   => $v,
                        'price'         => $param['price'][$v],
                    ];
                }
            }
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $optionType = [
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
            lang_plugins('mf_dcim_option_10'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
            lang_plugins('mf_dcim_option_value_10'),
        ];

        $productName = ProductModel::where('id', $productId)->value('name');

        $description = lang_plugins('mf_dcim_log_add_option_success', [
            '{product}' => 'product#'.$productId.'#'.$productName.'#',
            '{option}'  => $optionType[ $param['rel_type'] ],
            '{name}'    => $nameType[ $param['rel_type'] ],
            '{detail}'  => $type == 'radio' ? $param['value'] : $param['min_value'].'-'.$param['max_value'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$option->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 获取配置列表
     * @desc 获取配置列表
     * @author hh
     * @version v1
     * @param   int param.page 1 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序(id,value,order)
     * @param   string param.sort - 升降序(asc,desc)
     * @param   int param.product_id - 商品ID
     * @param   int param.rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置)
     * @param   int param.rel_id - 关联ID(rel_type=2,3,4,5时是线路ID)
     * @param   string field - 要获取的字段
     * @return  int list[].id - 配置ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string list[].value - 值
     * @return  int list[].min_value - 最小值
     * @return  int list[].max_value - 最大值
     * @return  int list[].step - 步长
     * @return  array list[].other_config - 其他配置
     * @return  string list[].firewall_type - 防火墙类型
     * @return  int list[].defence_rule_id - 防御规则ID
     * @return  int count - 总条数
     */
    public function optionList($param, $field = null)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','value','order'])){
            $param['orderby'] = 'value,min_value';
        }

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $where[] = ['rel_type', '=', $param['rel_type']];
        if(isset($param['rel_id']) && $param['rel_id']>0){
            $where[] = ['rel_id', '=', $param['rel_id']];
        }

        $field = $field.',product_id' ?? 'id,product_id,type,value,min_value,max_value,step,other_config';

        $list = $this
                ->field($field)
                ->where($where)
                // ->page($param['page'], $param['limit'])
                ->order('order','asc')
                ->order($param['orderby'], 'asc')
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();
        
        // 计算列表价格
        if(!empty($list)){
            $id = array_column($list, 'id');

            if($param['rel_type']==OptionModel::GLOBAL_DEFENCE){
                $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price,p.rel_id')
                    ->leftJoin('module_mf_dcim_price p', 'd.id=p.duration_id')
                    ->where('d.product_id', $param['product_id'])
                    ->where('p.rel_type', 'option')
                    ->whereIn('p.rel_id', $id)
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

                $durationArr = [];
                foreach ($duration as $key => $value) {
                    $durationArr[$value['rel_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'price' => $value['price']];
                }
            }

            // 时间最短的周期
            $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $list[0]['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();
            if(!empty($firstDuration)){
                $price = PriceModel::alias('p')
                    ->field('p.rel_id,p.price')
                    ->where('p.rel_type', 'option')
                    ->whereIn('p.rel_id', $id)
                    ->where('p.duration_id', $firstDuration['id'])
                    ->select()
                    ->toArray();

                $priceArr = [];
                foreach($price as $k=>$v){
                    $priceArr[ $v['rel_id'] ] = $v;
                }

                foreach($list as $k=>$v){
                    if(isset($v['type'])){
                        if($v['type'] == 'step'){
                            $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? 0;
                            foreach($list as $kk=>$vv){
                                // 范围内的阶梯
                                if($v['min_value'] > $vv['max_value']){
                                    $list[$k]['price'] = bcadd($list[$k]['price'], bcmul($priceArr[$vv['id']]['price'] ?? 0, $vv['min_value']==0?$vv['max_value']:$vv['max_value']-$vv['min_value']+1));
                                }
                            }
                        }else if($v['type'] == 'total'){
                            $list[$k]['price'] = isset($priceArr[$v['id']]['price']) ? bcmul($priceArr[$v['id']]['price'], $v['min_value']) : '0.00';
                        }else{
                            $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                        }
                    }else{
                        // 单选
                        $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                    }
                    // 为0时显示
                    if (isset($v['type']) && $v['type'] != 'radio' && isset($v['min_value']) && $v['min_value']==0){
                        $list[$k]['price'] = '0.00';
                    }
                    $list[$k]['duration'] = $firstDuration['name'];

                    // 处理公网IP格式
                    if($param['rel_type'] == OptionModel::LINE_IP){
                        if(strpos($v['value'], '_') !== false){
                            $v['value'] = explode(',', $v['value']);

                            $num = 0;
                            foreach($v['value'] as $vv){
                                $vv = explode('_', $vv);
                                $num += $vv[0];
                            }

                            $list[$k]['value'] = $num;
                        }
                    }
                    if(isset($v['other_config'])){
                        $list[$k]['other_config'] = json_decode($v['other_config'], true);
                    }
                    unset($list[$k]['product_id']);

                    // 修改value_show
                    if(isset($v['value_show']) && $v['value'] != 'NC'){
                        $list[$k]['value_show'] = '';
                    }
                    if(isset($durationArr)){
                        $list[$k]['duration_price'] = $durationArr[$v['id']] ?? [];
                    }
                }
                
            }else{
                foreach($list as $k=>$v){
                    $list[$k]['price'] = '0.00';
                    $list[$k]['duration'] = '';
                    unset($list[$k]['product_id']);
                    // 修改value_show
                    if(isset($v['value_show']) && $v['value'] != 'NC'){
                        $list[$k]['value_show'] = '';
                    }
                    if(isset($durationArr)){
                        $list[$k]['duration_price'] = $durationArr[$v['id']] ?? [];
                    }
                }
            }
        }
        // 根据value,min_value排序
        if(!in_array($param['rel_type'], [OptionModel::LINE_DEFENCE,OptionModel::GLOBAL_DEFENCE])){
            if($param['orderby'] == 'value,min_value'){
                usort($list, function($a, $b){
                    return ((isset($a['value']) && (int)$a['value'] > (int)$b['value']) || (isset($a['min_value']) && (int)$a['min_value'] > (int)$b['min_value'])) ? 1 : -1;
                });
            }
        }

        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-02
     * @title 修改配置
     * @desc 修改配置
     * @author hh
     * @version v1
     * @param   int param.id - 配置ID require
     * @param   string param.value - 值
     * @param   string param.value_show - 自定义显示
     * @param   int param.min_value - 最小值
     * @param   int param.max_value - 最大值
     * @param   array param.price - 周期价格(如["5"=>"12"],5是周期ID,12是价格) require
     * @param   array param.other_config - 其他配置
     * @param   string param.other_config.in_bw - 进带宽 rel_type=2可用
     * @param   int param.other_config.in_bw 0 进带宽 rel_type=3可用
     * @param   int param.other_config.out_bw 0 出带宽 rel_type=3可用
     * @param   string param.other_config.bill_cycle month 计费周期(month=自然月,last_30days=购买日循环) rel_type=3可用
     * @param   int param.other_config.memory_slot - 内存槽位 requireIf:rel_type=7
     * @param   int param.other_config.memory - 内存容量(GB) requireIf:rel_type=7
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function optionUpdate($param)
    {
        $option = $this->find($param['id']);
        if(empty($option)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        $productId = $option['product_id'];
        $param['rel_type'] = $option['rel_type'];
        $param['rel_id'] = $option['rel_id'];
        $oldOtherConfig = json_decode($option['other_config'], true);

        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select();

        $oldPrice = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $option->id)->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');
        
        $this->startTrans();
        try{
            $type = $option['type'];

            $update = [
                'order'         => $param['order'] ?? 0,
                'value_show'    => '',
            ];
            if($param['rel_type'] == OptionModel::LINE_BW){
                // 先放置在这点
                $LineBwValidate = new LineBwValidate();
                if($type == 'radio'){
                    if (!$LineBwValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }else{
                    if (!$LineBwValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }
                $update['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                $update['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? 0,
                    'out_bw' => $param['other_config']['out_bw'] ?? 0,
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){
                $line = LineModel::find($option['rel_id']);
                if(empty($line)){
                    throw new \Exception(lang_plugins('mf_dcim_line_defence_not_found'));
                }
                if($line['sync_firewall_rule'] == 1){
                    $param['value'] = $option['value'];
                }
            }else if($param['rel_type'] == OptionModel::LINE_IP){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->where('rel_id', $param['rel_id'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }
                // 先放置在这点
                $LineIpValidate = new LineIpValidate();
                if($type == 'radio'){
                    if (!$LineIpValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($LineIpValidate->getError()));
                    }
                }else{
                    if (!$LineIpValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($LineIpValidate->getError()));
                    }
                }
            }else if($param['rel_type'] == OptionModel::CPU){

            }else if($param['rel_type'] == OptionModel::MEMORY){
                $update['other_config'] = json_encode([
                    'memory_slot'   => (int)$param['other_config']['memory_slot'],
                    'memory'        => (int)$param['other_config']['memory'],
                ]);
            }else if($param['rel_type'] == OptionModel::GLOBAL_DEFENCE){
                $ConfigModel = new ConfigModel();
                $config = $ConfigModel->indexConfig(['product_id' => $productId]);

                if($config['data']['sync_firewall_rule']==1){
                    $param['value'] = $option['value'];
                }
            }

            // 类型不能修改了
            // $insert['type'] = $type;
            if($type == 'radio'){

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['value', '=', $param['value']];
                $whereSame[] = ['id', '<>', $param['id']];

                // 必须是数字
                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception(lang_plugins('mf_dcim_already_add_the_same_option'));
                }

                $update['value'] = $param['value'];

                // 当NC时可以保存自定义显示
                if($param['value'] == 'NC' && in_array($param['rel_type'], [OptionModel::LINE_IP,OptionModel::LINE_BW])){
                    $update['value_show'] = $param['value_show'] ?? '';
                }
            }else{

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['min_value', '<=', $param['max_value']];
                $whereSame[] = ['max_value', '>=', $param['min_value']];
                $whereSame[] = ['id', '<>', $param['id']];

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_dcim_option_intersect'));
                }
                $update['min_value'] = $param['min_value'];
                $update['max_value'] = $param['max_value'];
                $update['step'] = 1; //$param['step'];
            }
            $this->update($update, ['id'=>$option->id]);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => 'option',
                        'rel_id'        => $option->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('rel_type', 'option')->where('rel_id', $option->id)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $optionType = [
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
            lang_plugins('mf_dcim_option_10'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
            lang_plugins('mf_dcim_option_value_10'),
        ];

        $billCycle = [
            'month'         => lang_plugins('mf_dcim_option_bill_cycle_month'),
            'last_30days'   => lang_plugins('mf_dcim_option_bill_cycle_last_30days'),
        ];

        $des = [
            'value' => $nameType[ $param['rel_type'] ],
            'value_show' => lang_plugins('mf_dcim_value_show'),
        ];

        $old = [
            'value'         => $type == 'radio' ? $option['value'] : $option['min_value'].'-'.$option['max_value'],
            'value_show'    => $option['value_show'],
        ];

        $new = [
            'value'         => $type == 'radio' ? $param['value'] : $param['min_value'].'-'.$param['max_value'],
            'value_show'    => $update['value_show'] ?? '',
        ];
        if($option['rel_type'] == OptionModel::LINE_BW){
            $des['in_bw'] = lang_plugins('mf_dcim_line_bw_in_bw');
            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
        }else if($option['rel_type'] == OptionModel::LINE_FLOW){
            $des['in_bw'] = lang_plugins('mf_dcim_line_flow_in_bw');
            $des['out_bw'] = lang_plugins('mf_dcim_line_flow_out_bw');
            $des['bill_cycle'] = lang_plugins('mf_dcim_line_flow_bill_cycle');

            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $old['out_bw'] = $oldOtherConfig['out_bw'] ?? '';
            $old['bill_cycle'] = $billCycle[ $oldOtherConfig['bill_cycle'] ?? 'month' ];

            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
            $new['out_bw'] = $param['other_config']['out_bw'] ?? '';
            $new['bill_cycle'] = $billCycle[ $param['other_config']['bill_cycle'] ?? 'month' ];
        }else if($option['rel_type'] == OptionModel::MEMORY){
            $des['memory_slot'] = lang_plugins('mf_dcim_memory_slot');
            $des['memory'] = lang_plugins('mf_dcim_memory_capacity');

            $old['memory_slot'] = $oldOtherConfig['memory_slot'] ?? '';
            $old['memory'] = $oldOtherConfig['memory'] ?? '';

            $new['memory_slot'] = $param['other_config']['memory_slot'];
            $new['memory'] = $param['other_config']['memory'];
        }

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $new[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $productName = ProductModel::where('id', $productId)->value('name');

            $description = lang_plugins('mf_dcim_log_modify_option_success', [
                '{product}' => 'product#'.$productId.'#'.$productName.'#',
                '{option}'  => $optionType[ $param['rel_type'] ],
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success')
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 删除配置
     * @desc 删除配置
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     * @param   int rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置) 传入了验证配置类型
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function optionDelete($id, $rel_type = null)
    {
        $option = $this->find($id);
        if(empty($option)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if(isset($rel_type) && $option['rel_type'] != $rel_type){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if($option['rel_type'] == OptionModel::CPU){
            
        }else if($option['rel_type'] == OptionModel::MEMORY){
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->where('h.is_delete', 0)
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }else if($option['rel_type'] == OptionModel::DISK){
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->where('h.is_delete', 0)
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }else if($option['rel_type'] == OptionModel::GPU){
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->where('h.is_delete', 0)
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }

        $productId = $option['product_id'];
        $otherConfig = json_decode($option['other_config'], true);

        $this->startTrans();
        try{
            $this->where('id', $id)->delete();
            PriceModel::where('rel_type', 'option')->where('rel_id', $id)->delete();

            // 删除完了线路部分配置,自动关闭开关
            if(in_array($option['rel_type'], [OptionModel::LINE_DEFENCE])){
                // 是否还有对应配置,没有自动关闭开关
                $other = $this->where('rel_type', $option['rel_type'])->where('rel_id', $option['rel_id'])->find();
                if(empty($other)){
                    LineModel::where('id', $option['rel_id'])->update(['defence_enable'=>0]);
                }
            }
            // 删除内存,硬盘时
            if(in_array($option['rel_type'], [OptionModel::MEMORY, OptionModel::DISK, OptionModel::GPU])){
                PackageOptionLinkModel::where('option_id', $id)->delete();
                ModelConfigOptionLinkModel::where('option_id', $id)->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_success')];
        }

        $optionType = [
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
            lang_plugins('mf_dcim_option_10'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
            lang_plugins('mf_dcim_option_value_10'),
        ];

        $productName = ProductModel::where('id', $option['product_id'])->value('name');

        $description = lang_plugins('mf_dcim_log_delete_option_success', [
            '{product}' => 'product#'.$option['product_id'].'#'.$productName.'#',
            '{option}'  => $optionType[ $option['rel_type'] ],
            '{name}'    => $nameType[ $option['rel_type'] ],
            '{detail}'  => $option['type'] == 'radio' ? $option['value'] : $option['min_value'].'-'.$option['max_value'],
        ]);
        active_log($description, 'product', $option['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 配置详情
     * @desc  配置详情
     * @author hh
     * @version v1
     * @param  int id - 配置ID require
     * @return int id - 配置ID
     * @return int product_id - 商品ID
     * @return int rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置)
     * @return int rel_id - 关联ID(rel_type=2,3,4,5时是线路ID)
     * @return int type - 计费方式(radio=单选,step=阶梯,total=总量)
     * @return int value - 值
     * @return int min_value - 最小值
     * @return int max_value - 最大值
     * @return int step - 步长
     * @return int order - 排序
     * @return string value_show - 自定义显示
     * @return int duration[].id - 周期ID
     * @return string duration[].name - 周期名称
     * @return string duration[].price - 周期价格
     * @return array other_config - 其他配置
     * @return string other_config.in_bw - 进带宽,rel_type=2
     * @return int other_config.in_bw - 进带宽,rel_type=3
     * @return int other_config.out_bw - 出带宽,rel_type=3
     * @return string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环),rel_type=3
     * @return int other_config.memory_slot - 内存槽位,rel_type=7
     * @return int other_config.memory - 内存容量(GB),rel_type=7
     */
    public function optionIndex($id): array
    {
        $option = $this
                ->field('id,product_id,rel_type,rel_id,type,value,min_value,max_value,step,order,other_config,value_show,firewall_type,defence_rule_id')
                ->find($id);
        if(empty($option)){
            return [];
        }

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $option['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        $option = $option->toArray();
        $option['duration'] = $duration;
        $option['other_config'] = json_decode($option['other_config'], true);

        return $option;
    }

    /**
     * 时间 2023-02-06
     * @title 匹配对应配置所有周期价格
     * @desc 匹配对应配置所有周期价格
     * @author hh
     * @version v1
     * @param   int  $productId - 商品ID require
     * @param   int  $relType   - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡) require
     * @param   int  $relId     - 关联ID(rel_type=2,3,4,5时是线路ID) require
     * @param   int  $value     - 当前值 require
     * @return  bool match - 是否有该配置(false=无,true=有)
     * @return  array price - 配置已设置的周期价格(键是周期ID,值是价格)
     */
    public function optionDurationPrice($productId, $relType, $relId = 0, $value = 0)
    {
        $data = [];
        $match = false;

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $optionId = OptionModel::where($whereOption)->value('id');
            if(!empty($optionId)){
                $match = true;
                $data = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->select()->toArray();
                $data = array_column($data, 'price', 'duration_id');
            }
        }else if($type == 'step'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;

                $wherePrice = [];
                $wherePrice[] = ['o.product_id', '=', $productId];
                $wherePrice[] = ['o.rel_type', '=', $relType];
                $wherePrice[] = ['o.rel_id', '=', $relId];
                $wherePrice[] = ['o.min_value', '<=', $value];

                $price = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND o.id=p.rel_id')
                        ->order('o.min_value', 'asc')
                        ->group('p.rel_id,p.duration_id')
                        ->select();

                foreach($price as $v){
                    $v['min_value'] = max(1, $v['min_value']);
                    if($value > $v['max_value']){
                        $stepPrice = bcmul($v['max_value'] - $v['min_value'] + 1, $v['price']);
                    }else{
                        // 最后一层
                        $stepPrice = bcmul($value - $v['min_value'] + 1, $v['price']);
                    }
                    if(!isset($data[ $v['duration_id'] ])){
                        $data[ $v['duration_id'] ] = $stepPrice;
                    }else{
                        $data[ $v['duration_id'] ] = bcadd($data[ $v['duration_id'] ], $stepPrice);
                    }
                }
            }
        }else if($type == 'total'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;
                $optionId = $option['id'];

                $price = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->select()->toArray();
                foreach($price as $v){
                    $data[ $v['duration_id'] ] = bcmul($v['price'], $value);
                }                
            }
        }

        return ['match'=>$match, 'price'=>$data];
    }

    /**
     * 时间 2023-02-06
     * @title 匹配对应配置某个周期价格
     * @desc 匹配对应配置某个周期价格
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @param   int relType   - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=处理器,7=内存,8=硬盘,9=显卡,10=全局防护配置) require
     * @param   int relId     - 关联ID(rel_type=2,3,4,5时是线路ID) require
     * @param   int value     - 当前值 require
     * @param   int durationId - 周期ID require
     * @return  bool match - 是否有该配置(false=无,true=有)
     * @return  string price - 价格
     * @return  OptionModel option - 匹配的配置模型实例
     */
    public function matchOptionDurationPrice($productId, $relType, $relId = 0, $value = 0, $durationId = 0)
    {
        $match = false;  // 配置是否匹配
        $price = null;   // 价格

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option)){
                $match = true;
                $price = PriceModel::where('rel_type', 'option')->where('rel_id', $option['id'])->where('duration_id', $durationId)->value('price');
            }
        }else if($type == 'step'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;

                $wherePrice = [];
                $wherePrice[] = ['o.product_id', '=', $productId];
                $wherePrice[] = ['o.rel_type', '=', $relType];
                $wherePrice[] = ['o.rel_id', '=', $relId];
                $wherePrice[] = ['o.min_value', '<=', $value];

                $priceArr = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->where('p.duration_id', $durationId)
                        ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND o.id=p.rel_id')
                        ->order('o.min_value', 'asc')
                        ->group('p.rel_id')
                        ->select();

                foreach($priceArr as $v){
                    if(!is_numeric($v['price'])){
                        continue;
                    }
                    $v['min_value'] = max(1, $v['min_value']);
                    if($value > $v['max_value']){
                        $stepPrice = bcmul($v['max_value'] - $v['min_value'] + 1, $v['price']);
                    }else{
                        // 最后一层
                        $stepPrice = bcmul($value - $v['min_value'] + 1, $v['price']);
                    }
                    $price = bcadd($price ?? 0, $stepPrice);
                }
            }
        }else if($type == 'total'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $optionId = $option['id'];

                $match = true;
                $price = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->where('duration_id', $durationId)->value('price');
                $price = bcmul($price, $value);
            }
        }
        if($match){
            $option['other_config'] = json_decode($option['other_config'], true);
        }
        return ['match'=>$match, 'price'=>$price, 'option'=>$option ?? [] ];
    }

    public function lineDefenceDragSort($param)
    {
        $lineDefence = $this->where('rel_type',4)->where('id',$param['id'])->find();
        if(!$lineDefence){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if($param['prev_id'] == 0){
            $preOrder = -1;
            $order = 0;
        }else{
            $preLineDefence = $this->where('rel_type',4)
                ->where('product_id',$lineDefence['product_id'])
                ->where('id',$param['prev_id'])
                ->find();
            if(!$preLineDefence){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
            }
            $preOrder = $preLineDefence['order'];
            $order = $preLineDefence['order'] + 1;
        }
        $this->where('rel_type', 4)
            ->where('order', '>=', $preOrder)
            ->where('id', '>', $param['prev_id'])
            ->inc('order', 2)
            ->update();
        $this->where('id', $param['id'])->update(['order'=>$order]);
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    public function globalDefenceDragSort($param)
    {
        $lineDefence = $this->where('rel_type',11)->where('id',$param['id'])->find();
        if(!$lineDefence){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if($param['prev_id'] == 0){
            $preOrder = -1;
            $order = 0;
        }else{
            $preLineDefence = $this->where('rel_type',11)
                ->where('product_id',$lineDefence['product_id'])
                ->where('id',$param['prev_id'])
                ->find();
            if(!$preLineDefence){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
            }
            $preOrder = $preLineDefence['order'];
            $order = $preLineDefence['order'] + 1;
        }
        $this->where('rel_type', 11)
            ->where('order', '>=', $preOrder)
            ->where('id', '>', $param['prev_id'])
            ->inc('order', 2)
            ->update();
        $this->where('id', $param['id'])->update(['order'=>$order]);
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

}
<?php 
namespace server\mf_dcim\model;

use think\Model;
use server\mf_dcim\logic\ToolLogic;
use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\HostIpModel;

/**
 * @title 线路模型
 * @use server\mf_dcim\model\LineModel
 */
class LineModel extends Model
{
	protected $name = 'module_mf_dcim_line';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'data_center_id'        => 'int',
        'name'                  => 'string',
        'bill_type'             => 'string',
        'bw_ip_group'           => 'string',
        'defence_enable'        => 'int',
        'defence_ip_group'      => 'string',
        'order'                 => 'int',
        'create_time'           => 'int',
        'hidden'                => 'int',
        'upstream_id'           => 'int',
        'sync_firewall_rule'    => 'int',
        'order_default_defence' => 'string',
        'upstream_hidden'       => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 添加线路
     * @desc 添加线路
     * @author hh
     * @version v1
     * @param   int param.data_center_id - 数据中心ID require
     * @param   string param.name - 名称 require
     * @param   string param.bill_type - 计费类型(bw=带宽计费,flow=流量计费) require
     * @param   string param.bw_ip_group - 计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   int param.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启) 启用防护价格配置开启时必传
     * @param   string param.order_default_defence - 订购默认防御 同步防火墙规则开启时必传
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.order - 排序 require
     * @param   array param.bw_data - 带宽计费数据 requireIf,bill_type=bw
     * @param   string param.bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @param   string param.bw_data[].value - 带宽
     * @param   string param.bw_data[].value_show - 自定义显示
     * @param   int param.bw_data[].min_value - 最小值
     * @param   int param.bw_data[].max_value - 最大值
     * @param   array param.bw_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   string param.bw_data[].other_config.in_bw - 进带宽
     * @param   array param.flow_data - 流量计费数据 requireIf,bill_type=flow
     * @param   string param.flow_data[].value - 流量(GB,0=无限流量) require
     * @param   array param.flow_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   int param.flow_data[].other_config.in_bw - 进带宽 require
     * @param   int param.flow_data[].other_config.out_bw - 出带宽 require
     * @param   string param.flow_data[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
     * @param   array param.defence_data - 防护数据
     * @param   string param.defence_data[].value - 防御峰值(G) require
     * @param   array param.defence_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   string param.defence_data[].firewall_type - 防火墙类型
     * @param   int param.defence_data[].defence_rule_id - 防御规则ID
     * @param   array param.ip_data - 公网IP数据
     * @param   string param.ip_data[].value - 公网IP数量 require
     * @param   string param.ip_data[].value_show - 自定义显示
     * @param   array param.ip_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 线路ID
     */
    public function lineCreate($param)
    {
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_data_center_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $dataCenter['id'])
                ->where('name', $param['name'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_name_exist')];
        }
        $productId = $dataCenter['product_id'];

        $duration = DurationModel::where('product_id', $productId)->column('id');

        $time = time();
        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        $param['create_time'] = $time;
        $param['order'] = $param['order'] ?? 0;

        // 验证防御
        if(!empty($param['defence_enable'])){
            if($param['sync_firewall_rule'] == 1){
                $ConfigModel = new ConfigModel();
                $rule = $ConfigModel->firewallDefenceRule(['product_id'=>$productId]);

                $defence = [];
                foreach($rule['rule'] as $v){
                    $defence[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
                }

                $param['order_default_defence'] = $param['order_default_defence'] ?? '';
                foreach($param['defence_data'] as $k=>$v){
                    if(!isset($defence[ $v['firewall_type'] ][ $v['defence_rule_id'] ])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_firewall_rule_id_error') ];
                    }
                    $param['defence_data'][$k]['value'] = $v['firewall_type'] . '_' . $v['defence_rule_id'];

                    if($param['defence_data'][$k]['value']==$param['order_default_defence']){
                        $orderDefaultDefence = $param['defence_data'][$k]['value'];
                    }

                }

                if(!isset($orderDefaultDefence)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_firewall_order_default_defence_not_exist')];
                }
            }
        }  

        $this->startTrans();
        try{
            $line = $this->create($param, ['data_center_id','name','bill_type','bw_ip_group','defence_enable','defence_ip_group','create_time','order','sync_firewall_rule']);

            $priceArr = [];
            if($param['bill_type'] == 'bw'){
                // 带宽计费
                foreach($param['bw_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_BW;
                    $v['rel_id'] = $line->id;
                    if($v['type'] == 'radio'){
                        $v['min_value'] = 0;
                        $v['max_value'] = 0;
                        if(isset($v['value']) && $v['value'] != 'NC'){
                            $v['value_show'] = '';
                        }
                    }else{
                        $v['value'] = 0;
                        $v['value_show'] = '';
                    }
                    $v['step'] = 1;
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([
                        'in_bw' => $v['other_config']['in_bw'] ?? ''
                    ]);

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','min_value','max_value','step','other_config','create_time','value_show']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => 'option',
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }else{
                // 流量计费
                foreach($param['flow_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_FLOW;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([
                        'in_bw' => $v['other_config']['in_bw'],
                        'out_bw' => $v['other_config']['out_bw'],
                        'bill_cycle' => $v['other_config']['bill_cycle'],
                    ]);

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => 'option',
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }

            // 防护配置
            if(isset($param['defence_data']) && is_array($param['defence_data'])){
                foreach($param['defence_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_DEFENCE;
                    $v['rel_id'] = $line->id;
                    $v['type'] = 'radio';
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);

                    if(isset($v['id'])) unset($v['id']);

                    if($param['sync_firewall_rule'] == 0){
                        $v['firewall_type'] = '';
                        $v['defence_rule_id'] = 0;
                    }else{

                    }

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time','firewall_type','defence_rule_id']);


                    if($param['sync_firewall_rule'] == 1 && !empty($param['order_default_defence']) && $param['order_default_defence'] == $v['value']){
                        $this->where('id', $line->id)->update([
                            'order_default_defence' => $param['order_default_defence'],
                        ]);
                    }

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }

            // 公网IP
            if(isset($param['ip_data']) && is_array($param['ip_data'])){
                foreach($param['ip_data'] as $k=>$v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_IP;
                    $v['rel_id'] = $line->id;
                    if($v['type'] == 'radio'){
                        $v['min_value'] = 0;
                        $v['max_value'] = 0;
                    }else{
                        $v['value'] = 0;
                    }
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);
                    if(isset($v['value']) && $v['value'] != 'NC'){
                        $v['value_show'] = '';
                    }

                    if(isset($v['id'])) unset($v['id']);

                    $v['order'] = $k;
                    
                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time','value_show','min_value','max_value','order']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => 'option',
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
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

        $productName = ProductModel::where('id', $productId)->value('name');

        $description = lang_plugins('mf_dcim_log_add_line_success', [
            '{product}' => 'product#'.$productId.'#'.$productName.'#',
            '{name}'    => $param['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$line->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-02
     * @title 线路详情
     * @desc 线路详情
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  int id - 线路ID
     * @return  string name - 线路名称
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string bw_ip_group - 带宽计费IP分组
     * @return  int defence_enable - 启用防护价格配置(0=关闭,1=开启)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string order_default_defence - 订购默认防御
     * @return  string defence_ip_group - 防护IP分组
     * @return  int order - 排序
     * @return  int bw_data[].id - 配置ID
     * @return  string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw_data[].value - 带宽
     * @return  string bw_data[].value_show - 自定义显示
     * @return  int bw_data[].min_value - 最小值
     * @return  int bw_data[].max_value - 最大值
     * @return  string bw_data[].price - 价格
     * @return  string bw_data[].duration - 周期
     * @return  int flow_data[].id - 配置ID
     * @return  string flow_data[].value - 流量
     * @return  string flow_data[].price - 价格
     * @return  string flow_data[].duration - 周期
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].value - 防御峰值(G)
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  string defence_data[].firewall_type - 防火墙类型
     * @return  int defence_data[].defence_rule_id - 防御规则ID
     * @return  string defence_data[].defence_rule_name - 防御规则名称
     * @return  string defence_data[].defense_peak - 防御峰值
     * @return  int ip_data[].id - 配置ID
     * @return  string ip_data[].value - IP数量
     * @return  string ip_data[].value_show - 自定义显示
     * @return  string ip_data[].price - 价格
     * @return  string ip_data[].duration - 周期
     */
    public function lineIndex($id)
    {
        $line = $this
                ->field('id,name,bill_type,bw_ip_group,defence_enable,defence_ip_group,order,sync_firewall_rule,order_default_defence,data_center_id')
                ->find($id);
        if(empty($line)){
            return (object)[];
        }
        $data = $line->toArray();

        $OptionModel = new OptionModel();

        $param = [];
        $param['product_id'] = DataCenterModel::where('id', $line['data_center_id'])->value('product_id');
        $param['rel_id'] = $id;
        $param['sort'] = 'asc';
        $param['page'] = 1;
        $param['limit'] = 999;

        if($line['bill_type'] == 'bw'){
            $param['rel_type'] = OptionModel::LINE_BW;
            $param['orderby'] = 'value,min_value';

            $field = 'id,type,value,min_value,max_value,value_show';
            $result = $OptionModel->optionList($param, $field);

            $data['bw_data'] = $result['list'];
        }else{
            $param['rel_type'] = OptionModel::LINE_FLOW;
            $param['orderby'] = 'value';
            
            $field = 'id,value';
            $result = $OptionModel->optionList($param, $field);

            $data['flow_data'] = $result['list'];
        }

        // 获取防御列表
        if($line['sync_firewall_rule'] == 0){
            $param['rel_type'] = OptionModel::LINE_DEFENCE;
            $param['orderby'] = 'value';
            
            $field = 'id,type,value,order';
            $result = $OptionModel->optionList($param, $field);

            $data['defence_data'] = $result['list'];
        }else{
            $param['rel_type'] = OptionModel::LINE_DEFENCE;

            $result = $OptionModel->globalDefenceList($param);
            $data['defence_data'] = $result['data']['defence_data'];
        }

        $param['rel_type'] = OptionModel::LINE_IP;
        $param['orderby'] = 'value';
        
        $field = 'id,value,value_show,type,min_value,max_value';
        $result = $OptionModel->optionList($param, $field);

        $data['ip_data'] = $result['list'];
        return $data;
    }

    /**
     * 时间 2023-02-03
     * @title 修改线路
     * @desc 修改线路
     * @author hh
     * @version v1
     * @param   int param.id - 线路ID require
     * @param   string param.name - 线路名称
     * @param   string param.bw_ip_group - 带宽计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   int param.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启) require
     * @param   string param.order_default_defence - 订购默认防御
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.order - 排序 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lineUpdate($param)
    {
        $line = $this->find($param['id']);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $line['data_center_id'])
                ->where('name', $param['name'])
                ->where('id', '<>', $param['id'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_name_exist')];
        }
        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        if(!is_numeric($param['order'])){
            unset($param['order']);
        }

        $this->update($param, ['id'=>$line['id']], ['name','bw_ip_group','defence_enable','sync_firewall_rule','order_default_defence','defence_ip_group','order']);

        // 切换同步防火墙规则时删除防御配置项
        if($line['sync_firewall_rule']!=$param['sync_firewall_rule']){
            $optionIds = OptionModel::where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $param['id'])->column('id');
            OptionModel::whereIn('id', $optionIds)->delete();
            PriceModel::where('rel_type', 'option')->whereIn('rel_id', $optionIds)->delete();
        }

        $switch = [lang_plugins('mf_dcim_switch_off'), lang_plugins('mf_dcim_switch_on')];

        $des = [
            'name'                  => lang_plugins('mf_dcim_line_name'),
            'bw_ip_group'           => lang_plugins('mf_dcim_line_bw_ip_group'),
            'defence_enable'        => lang_plugins('mf_dcim_line_defence_enable'),
            'sync_firewall_rule'    => lang_plugins('mf_dcim_line_sync_firewall_rule'),
            'order_default_defence' => lang_plugins('mf_dcim_line_order_default_defence'),
            'defence_ip_group'      => lang_plugins('mf_dcim_line_defence_ip_group'),
        ];
        $old = $line->toArray();
        $old['defence_enable'] = $switch[ $old['defence_enable'] ];

        $param['defence_enable'] = $switch[ $param['defence_enable'] ];

        $old['sync_firewall_rule'] = $switch[ $old['sync_firewall_rule'] ];

        $param['sync_firewall_rule'] = $switch[ $param['sync_firewall_rule'] ];

        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;
            $productName = ProductModel::where('id', $productId)->value('name');

            $description = lang_plugins('mf_dcim_log_modify_line_success', [
                '{product}' => !empty($productId) ? 'product#'.$productId.'#'.$productName.'#' : '',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 删除线路
     * @desc 删除线路
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lineDelete($id)
    {
        $line = $this->find($id);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
        }
        
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();

            // 获取线路配置
            $optionId = OptionModel::whereIn('rel_type', [OptionModel::LINE_BW,OptionModel::LINE_FLOW,OptionModel::LINE_DEFENCE,OptionModel::LINE_IP])->where('rel_id', $id)->column('id');
            if(!empty($optionId)){
                OptionModel::whereIn('id', $optionId)->delete();
                PriceModel::where('rel_type', 'option')->whereIn('rel_id', $optionId)->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail').$e->getMessage()];
        }

        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;
        $productName = ProductModel::where('id', $productId)->value('name');

        $description = lang_plugins('mf_dcim_log_delete_line_success', [
            '{product}' => !empty($productId) ? 'product#'.$productId.'#'.$productName.'#' : '',
            '{name}'    => $line['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-14
     * @title 前台获取线路配置
     * @desc 前台获取线路配置
     * @author hh
     * @version v1
     * @param   int id - 线路ID require
     * @return  string bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string bw[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw[].value - 带宽
     * @return  string bw[].value_show - 自定义显示
     * @return  int bw[].min_value - 最小值
     * @return  int bw[].max_value - 最大值
     * @return  int bw[].step - 步长
     * @return  string flow[].value - 流量(流量线路)
     * @return  string defence[].value - 防御
     * @return  string defence[].desc - 防御显示
     * @return  string order_default_defence - 默认防御
     * @return  string ip[].value - 公网IP值
     * @return  string ip[].desc - 公网IP显示
     * @return  string ip[].num - 等效数量
     */
    public function homeLineConfig($id)
    {
        $line = $this->find($id);
        if(empty($line)){
            return (object)[];
        }

        $dataCenter = DataCenterModel::find($line['data_center_id']);
        if(empty($dataCenter)){
            return (object)[];
        }

        $data = [
            'bill_type'             => $line['bill_type'],
            'sync_firewall_rule'    => $line['sync_firewall_rule'],
        ];
        if($line['bill_type'] == 'bw'){
            // 带宽计费
            $bw = OptionModel::field('type,value,min_value,max_value,step,value_show')->where('rel_type', OptionModel::LINE_BW)->where('rel_id', $id)->orderRaw('--value,--min_value asc')->select()->toArray();

            $data['bw'] = $bw;
        }else{
            // 流量计费
            $flow = OptionModel::field('value')->where('rel_type', OptionModel::LINE_FLOW)->where('rel_id', $id)->orderRaw('--value asc')->select()->toArray();
            $data['flow'] = $flow;
        }
        if($line['defence_enable'] == 1){
            if($line['sync_firewall_rule'] == 1){
                $defence = OptionModel::field('id,value,firewall_type,defence_rule_id')->where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $id)->order('order','asc')->order('id', 'asc')->select()->toArray();
                
                $ConfigModel = new ConfigModel();
                $rule = $ConfigModel->firewallDefenceRule([
                    'product_id'    => $dataCenter['product_id'],
                ]);

                $firewallRule = [];
                foreach($rule['rule'] as $v){
                    $firewallRule[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
                }

                foreach ($defence as $key => $value) {
                    if(isset($firewallRule[$value['firewall_type']][$value['defence_rule_id']])){
                        $defence[$key]['desc'] = $firewallRule[$value['firewall_type']][$value['defence_rule_id']]; 
                        unset($defence[$key]['id'], $defence[$key]['firewall_type'], $defence[$key]['defence_rule_id']);
                    }else{
                        unset($defence[$key]); 
                    }
                }
            }else{
                $defence = OptionModel::field('value')->where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $id)->orderRaw('--value asc')->select()->toArray();
                foreach ($defence as $key => $value) {
                    $defence[$key]['desc'] = $value['value'].'G'; 
                }
            }

            if(!empty($defence)){
                $defence = array_values($defence);
                $data['order_default_defence'] = in_array($line['order_default_defence'], array_column($defence, 'value')) ? $line['order_default_defence'] : $defence[0]['value'];
            }else{
                $data['order_default_defence'] = '';
            }

            $data['defence'] = array_values($defence);
        }
        $ip = OptionModel::field('value,value_show,type,min_value,max_value,step')->where('rel_type', OptionModel::LINE_IP)->where('rel_id', $id)->order('value,min_value', 'asc')->select()->toArray();
        // 排序
        foreach($ip as $k=>$v){
            $num = $v['value'];
            if(strpos($v['value'], '_') !== false){
                $v['value'] = explode(',', $v['value']);

                $num = 0;
                foreach($v['value'] as $vv){
                    $vv = explode('_', $vv);
                    $num += $vv[0];
                }
            }

            $desc = $num == 'NC' ? ($v['value_show'] !== '' ? $v['value_show'] : lang_plugins('mf_dcim_real_ip')) : $num .lang_plugins('mf_dcim_indivual');
            $ip[$k]['desc'] = $desc;
            $ip[$k]['num'] = $num; // 等效数量
            unset($ip[$k]['value_show']);
        }
        usort($ip, function($a, $b){
            return (int)$a['desc'] > (int)$b['desc'] ? 1 : -1;
        });
        $data['ip'] = $ip;
        return $data;
    }

    /**
     * 时间 2024-06-21
     * @title 切换订购是否显示
     * @desc  切换订购是否显示
     * @author hh
     * @version v1
     * @param   int param.id - 线路ID require
     * @param   int param.hidden - 是否隐藏(0=否,1=是) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function updateHidden($param)
    {
        $line = $this->find($param['id']);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
        }
        if($line['hidden'] == $param['hidden']){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $this->update(['hidden'=>$param['hidden']], ['id'=>$line['id']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-14
     * @title 获取升级防御配置
     * @desc 获取升级防御配置
     * @author theworld
     * @version v1
     * @param   int param.id - 产品ID  require
     * @param   string param.ip - IP require
     * @return  string defence[].value - 防御
     * @return  string defence[].desc - 防御显示
     * @return  string current_defence - IP当前防御
     */
    public function defenceConfig($param)
    {
        $param['id'] = $param['id'] ?? 0;
        $param['ip'] = $param['ip'] ?? '';

        $host = HostModel::find($param['id']);
        if(empty($host)){
            return (object)[];
        }

        $hostLink= HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return (object)[];
        }

        $configData = json_decode($hostLink['config_data'], true);

        $line = $this->find($configData['line']['id']);
        if(empty($line)){
            return (object)[];
        }

        if($line['defence_enable']!=1 || $line['sync_firewall_rule']!=1){
            return (object)[];
        }

        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->where('host_id', $param['id'])->find();
        if(empty($hostIp)){
            return (object)[];
        }
        $dedicateIp = $hostIp['dedicate_ip'];
        $assignIp = array_filter(explode(',', $hostIp['assign_ip']));
        if($dedicateIp!=$param['ip'] && !in_array($param['ip'], $assignIp)){
            return (object)[];
        }

        $defence = OptionModel::field('id,value,firewall_type,defence_rule_id')->where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $line['id'])->order('order','asc')->order('id', 'asc')->select()->toArray();
        
        $ConfigModel = new ConfigModel();
        $rule = $ConfigModel->firewallDefenceRule([
            'product_id'    => $host['product_id'],
        ]);

        $firewallRule = [];
        foreach($rule['rule'] as $v){
            $firewallRule[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
        }

        foreach ($defence as $key => $value) {
            if(isset($firewallRule[$value['firewall_type']][$value['defence_rule_id']])){
                $defence[$key]['desc'] = $firewallRule[$value['firewall_type']][$value['defence_rule_id']]; 
                unset($defence[$key]['id'], $defence[$key]['firewall_type'], $defence[$key]['defence_rule_id']);
            }else{
                unset($defence[$key]); 
            }
        }

        // 20250313 改 获取子产品防御
        $subHostIds = HostLinkModel::where('parent_host_id', $param['id'])->column('host_id');
        $ipDefence = IpDefenceModel::whereIn('host_id', $subHostIds)->where('ip', $param['ip'])->find();
//        $ipDefence = IpDefenceModel::where('host_id', $param['id'])->where('ip', $param['ip'])->find();

        return ['defence' => $defence, 'current_defence' => $ipDefence['defence'] ?? ''];
    }

}
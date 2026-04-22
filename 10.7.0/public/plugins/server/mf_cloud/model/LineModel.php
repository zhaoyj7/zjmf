<?php 
namespace server\mf_cloud\model;

use think\Model;
use server\mf_cloud\logic\ToolLogic;
use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\HostIpModel;

/**
 * @title 线路模型
 * @use server\mf_cloud\model\LineModel
 */
class LineModel extends Model
{
	protected $name = 'module_mf_cloud_line';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'data_center_id'    => 'int',
        'name'              => 'string',
        'bill_type'         => 'string',
        'bw_ip_group'       => 'string',
        'defence_enable'    => 'int',
        'defence_ip_group'  => 'string',
        'ip_enable'         => 'int',
        'link_clone'        => 'int',
        'order'             => 'int',
        'create_time'       => 'int',
        'ipv6_enable'       => 'int',
        'hidden'            => 'int',
        'upstream_id'       => 'int',
        'sync_firewall_rule'=> 'int',
        'order_default_defence' => 'string',
        'support_on_demand' => 'int',
        'upstream_hidden'   => 'int',
        'ipv6_group_id'     => 'string',
    ];

    /**
     * 时间 2023-02-02
     * @title 添加线路
     * @desc  添加线路
     * @author hh
     * @version v1
     * @param   array param - 请求参数 require
     * @param   int param.data_center_id - 数据中心ID require
     * @param   string param.name - 名称 require
     * @param   string param.bill_type - 计费类型(bw=带宽计费,flow=流量计费) require
     * @param   string param.bw_ip_group - 计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int param.link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int param.order 0 排序
     * @param   array param.bw_data - 带宽计费数据 requireIf,bill_type=bw
     * @param   string param.bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @param   int param.bw_data[].value - 带宽
     * @param   int param.bw_data[].min_value - 最小值
     * @param   int param.bw_data[].max_value - 最大值
     * @param   array param.bw_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   string param.bw_data[].other_config.in_bw - 进带宽
     * @param   string param.bw_data[].other_config.advanced_bw - 智能带宽规则ID
     * @param   array param.flow_data - 流量计费数据 requireIf,bill_type=flow
     * @param   int param.flow_data[].value - 流量(GB,0=无限流量) require
     * @param   array param.flow_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   int param.flow_data[].other_config.in_bw - 进带宽 require
     * @param   int param.flow_data[].other_config.out_bw - 出带宽 require
     * @param   int param.flow_data[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出) require
     * @param   string param.flow_data[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环) require
     * @param   array param.defence_data - 防护数据
     * @param   int param.defence_data[].value - 防御峰值(G) require
     * @param   array param.defence_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   string param.defence_data[].firewall_type - 防火墙类型
     * @param   int param.defence_data[].defence_rule_id - 防御规则ID
     * @param   array param.ip_data - 附加IP数据
     * @param   string param.ip_data[].type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int param.ip_data[].value - IP数量
     * @param   int param.ip_data[].min_value - 最小值
     * @param   int param.ip_data[].max_value - 最大值
     * @param   array param.ip_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @param   int param.ipv6_enable - 启用IPv6(0=关闭,1=开启)
     * @param   string param.ipv6_data[].type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int param.ipv6_data[].value - IPv6数量
     * @param   int param.ipv6_data[].min_value - 最小值
     * @param   int param.ipv6_data[].max_value - 最大值
     * @param   array param.ipv6_data[].price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 线路ID
     */
    public function lineCreate($param): array
    {
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $dataCenter['id'])
                ->where('name', $param['name'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('line_name_exist')];
        }
        $productId = $dataCenter['product_id'];

        $duration = DurationModel::where('product_id', $productId)->column('id');
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'];
        // hyperv不能添加流量线路
        if($config['type'] == 'hyperv' && $param['bill_type'] != 'bw'){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_hyperv_cannot_add_flow_line')];
        }
        // if($config['global_defence_strategy']==1){
        //     $param['defence_enable'] = 1;
        //     $param['sync_firewall_rule'] = $config['sync_firewall_rule'];
        // }

        $time = time();
        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        $param['ipv6_group_id'] = $param['ipv6_group_id'] ?? '';
        $param['create_time'] = $time;
        $param['order'] = $param['order'] ?? 0;
        $param['sync_firewall_rule'] = $param['sync_firewall_rule'] ?? 0;
        $param['support_on_demand'] = $param['bill_type'] == 'bw' || !empty($param['flow_data_on_demand']) ? 1 : 0;

        // 验证防御
        if(!empty($param['defence_enable'])){
            if($param['sync_firewall_rule'] == 1){
                $ConfigModel = new ConfigModel();
                $rule = $ConfigModel->firewallDefenceRule(['product_id'=>$productId]);

                $defence = [];
                foreach($rule['rule'] as $v){
                    $defence[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
                }

                foreach($param['defence_data'] as $k=>$v){
                    if(!isset($defence[ $v['firewall_type'] ][ $v['defence_rule_id'] ])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_sync_firewall_rule_id_error') ];
                    }
                    $param['defence_data'][$k]['value'] = $v['firewall_type'] . '_' . $v['defence_rule_id'];
                }
            }
        }

        $this->startTrans();
        try{
            $line = $this->create($param, ['data_center_id','name','bill_type','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','order','create_time','ipv6_enable','ipv6_group_id','sync_firewall_rule','support_on_demand']);

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
                    }else{
                        $v['value'] = 0;
                    }
                    $v['step'] = 1;
                    $v['create_time'] = $time;
                    $v['on_demand_price'] = $v['on_demand_price'] ?? 0;

                    // hyperv没有高级配置
                    if($config['type'] == 'hyperv'){
                        $v['other_config'] = json_encode([
                            'in_bw'         => '',
                            'advanced_bw'   => '',
                        ]);
                    }else{
                        $v['other_config'] = json_encode([
                            'in_bw'         => $v['other_config']['in_bw'] ?? '',
                            'advanced_bw'   => $v['other_config']['advanced_bw'] ?? '',
                        ]);
                    }

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','min_value','max_value','step','other_config','create_time','on_demand_price']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
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
                        'in_bw' => (int)$v['other_config']['in_bw'],
                        'out_bw' => (int)$v['other_config']['out_bw'],
                        'traffic_type' => (int)$v['other_config']['traffic_type'],
                        'bill_cycle' => $v['other_config']['bill_cycle'],
                    ]);
                    // 单个流量不能设置
                    $v['on_demand_price'] = 0;

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time','on_demand_price']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
                // 流量计费按需
                if(!empty($param['flow_data_on_demand'])){
                    foreach($param['flow_data_on_demand'] as $v){
                        $v['product_id'] = $productId;
                        $v['rel_type'] = OptionModel::LINE_FLOW_ON_DEMAND;
                        $v['rel_id'] = $line->id;
                        $v['type'] = 'radio';
                        $v['value'] = 0;
                        $v['create_time'] = $time;
                        $v['other_config'] = json_encode([
                            'in_bw' => (int)$v['other_config']['in_bw'],
                            'out_bw' => (int)$v['other_config']['out_bw'],
                            'traffic_type' => (int)$v['other_config']['traffic_type'],
                        ]);
                        $v['on_demand_price'] = $v['on_demand_price'] ?? 0;
    
                        if(isset($v['id'])) unset($v['id']);
    
                        $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time','on_demand_price']);
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
                    $v['on_demand_price'] = $v['on_demand_price'] ?? 0;

                    if(isset($v['id'])) unset($v['id']);

                    if($param['sync_firewall_rule'] == 0){
                        $v['firewall_type'] = '';
                        $v['defence_rule_id'] = 0;
                    }else{

                    }

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','other_config','create_time','firewall_type','defence_rule_id','on_demand_price']);


                    if($param['sync_firewall_rule'] == 1 && !empty($param['order_default_defence']) && $param['order_default_defence'] == $v['value']){
                        $this->where('id', $line->id)->update([
                            'order_default_defence' => $param['order_default_defence'],
                        ]);
                    }

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            // 附加IP
            if(isset($param['ip_data']) && is_array($param['ip_data'])){
                foreach($param['ip_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_IP;
                    $v['rel_id'] = $line->id;
                    if($v['type'] == 'radio'){
                        $v['min_value'] = 0;
                        $v['max_value'] = 0;
                    }else{
                        $v['value'] = 0;
                    }
                    $v['step'] = 1;
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);
                    $v['on_demand_price'] = $v['on_demand_price'] ?? 0;

                    if(isset($v['id'])) unset($v['id']);

                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','min_value','max_value','step','other_config','create_time','on_demand_price']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
                                'rel_id'        => $option->id,
                                'duration_id'   => $vv,
                                'price'         => $v['price'][$vv],
                            ];
                        }
                    }
                }
            }
            // 附加IPv6
            if(isset($param['ipv6_data']) && is_array($param['ipv6_data'])){
                foreach($param['ipv6_data'] as $v){
                    $v['product_id'] = $productId;
                    $v['rel_type'] = OptionModel::LINE_IPV6;
                    $v['rel_id'] = $line->id;
                    if($v['type'] == 'radio'){
                        $v['min_value'] = 0;
                        $v['max_value'] = 0;
                    }else{
                        $v['value'] = 0;
                    }
                    $v['step'] = 1;
                    $v['create_time'] = $time;
                    $v['other_config'] = json_encode([]);
                    $v['on_demand_price'] = $v['on_demand_price'] ?? 0;

                    if(isset($v['id'])) unset($v['id']);
                    
                    $option = OptionModel::create($v, ['product_id','rel_type','rel_id','type','value','min_value','max_value','step','other_config','create_time','on_demand_price']);

                    foreach($duration as $vv){
                        if(isset($v['price'][$vv])){
                            $priceArr[] = [
                                'product_id'    => $productId,
                                'rel_type'      => PriceModel::REL_TYPE_OPTION,
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

        $description = lang_plugins('log_mf_cloud_add_line_success', [
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
     * @return  string bw_ip_group - 计费IP分组
     * @return  int defence_enable - 启用防护价格配置(0=关闭,1=开启)
     * @return  string defence_ip_group - 防护IP分组
     * @return  int ip_enable - 启用附加IP(0=关闭,1=开启)
     * @return  int link_clone - 链接创建(0=关闭,1=开启)
     * @return  int order - 排序
     * @return  int ipv6_enable - 启用IPv6配置(0=关闭,1=开启)
     * @param   int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @param   string order_default_defence - 新订购默认防御
     * @return  int bw_data[].id - 配置ID
     * @return  string bw_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string bw_data[].value - 带宽
     * @return  int bw_data[].min_value - 最小值
     * @return  int bw_data[].max_value - 最大值
     * @return  int bw_data[].product_id - 商品ID
     * @return  string bw_data[].price - 价格
     * @return  string bw_data[].duration - 周期
     * @return  int flow_data[].id - 配置ID
     * @return  string flow_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int flow_data[].value - 流量
     * @return  int flow_data[].product_id - 商品ID
     * @return  string flow_data[].price - 价格
     * @return  string flow_data[].duration - 周期
     * @return  int defence_data[].id - 配置ID
     * @return  string defence_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int defence_data[].value - 防御峰值(G)
     * @return  int defence_data[].product_id - 商品ID
     * @return  string defence_data[].price - 价格
     * @return  string defence_data[].duration - 周期
     * @return  string defence_data[].firewall_type - 防火墙类型
     * @return  int defence_data[].defence_rule_id - 防御规则ID
     * @return  string defence_data[].defence_rule_name - 防御规则名称
     * @return  string defence_data[].defense_peak - 防御峰值
     * @return  int ip_data[].id - 配置ID
     * @return  string ip_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ip_data[].value - IP数量
     * @return  int ip_data[].min_value - 最小值
     * @return  int ip_data[].min_max - 最大值
     * @return  int ip_data[].product_id - 商品ID
     * @return  string ip_data[].price - 价格
     * @return  string ip_data[].duration - 周期
     * @return  int ipv6_data[].id - 配置ID
     * @return  string ipv6_data[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ipv6_data[].value - IP数量
     * @return  int ipv6_data[].product_id - 商品ID
     * @return  string ipv6_data[].price - 价格
     * @return  string ipv6_data[].duration - 周期
     */
    public function lineIndex($id)
    {
        $line = $this
                ->field('id,data_center_id,name,bill_type,bw_ip_group,defence_enable,defence_ip_group,ip_enable,link_clone,order,ipv6_enable,ipv6_group_id,sync_firewall_rule,order_default_defence')
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

            $field = 'id,type,value,min_value,max_value';
            $result = $OptionModel->optionList($param, $field);

            $data['bw_data'] = $result['list'];
        }else{
            $param['rel_type'] = OptionModel::LINE_FLOW;
            $param['orderby'] = 'value';
            
            $field = 'id,type,value,other_config';
            $result = $OptionModel->optionList($param, $field);

            $data['flow_data'] = $result['list'];

            // 获取流量按需
            $param['rel_type'] = OptionModel::LINE_FLOW_ON_DEMAND;
            $param['orderby'] = 'value';
            
            $field = 'id,type,value,other_config';
            $result = $OptionModel->optionList($param, $field);

            $data['flow_data_on_demand'] = $result['list'];
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
        $param['orderby'] = 'value,min_value';
        
        $field = 'id,type,value,min_value,max_value';
        $result = $OptionModel->optionList($param, $field);

        $data['ip_data'] = $result['list'];

        $param['rel_type'] = OptionModel::LINE_IPV6;
        $param['orderby'] = 'value,min_value';
        
        $field = 'id,type,value,min_value,max_value';
        $result = $OptionModel->optionList($param, $field);

        $data['ipv6_data'] = $result['list'];
        return $data;
    }

    /**
     * 时间 2023-02-03
     * @title 修改线路
     * @desc 修改线路
     * @author hh
     * @version v1
     * @param   int param.id - 线路ID require
     * @param   string param.name - 线路名称 require
     * @param   string param.bw_ip_group - 计费IP分组
     * @param   int param.defence_enable - 启用防护价格配置(0=关闭,1=开启) require
     * @param   string param.defence_ip_group - 防护IP分组
     * @param   int param.ip_enable - 启用附加IP(0=关闭,1=开启) require
     * @param   int param.link_clone - 链接创建(0=关闭,1=开启) require
     * @param   int param.order - 排序
     * @param   int param.ipv6_enable - 启用IPv6(0=关闭,1=开启) require
     * @param   int param.sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启) require
     * @param   string param.order_default_defence - 新订购默认防御
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lineUpdate($param)
    {
        $line = $this->find($param['id']);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $exist = $this
                ->where('data_center_id', $line['data_center_id'])
                ->where('name', $param['name'])
                ->where('id', '<>', $param['id'])
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('line_name_exist')];
        }
        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;

        $OptionModel = new OptionModel();

        if($param['defence_enable'] == 1){
            if($param['sync_firewall_rule'] == 1){
                if(!empty($param['order_default_defence'])){
                    $option = $OptionModel
                            ->where('product_id', $productId)
                            ->where('rel_type', OptionModel::LINE_DEFENCE)
                            ->where('rel_id', $line->id)
                            ->where('value', $param['order_default_defence'])
                            ->find();
                    if(empty($option)){
                        return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                    }
                }
            }else{
                $param['order_default_defence'] = '';
            }
        }else{
            $param['sync_firewall_rule'] = $line['sync_firewall_rule'];
            $param['order_default_defence'] = $line['order_default_defence'];
        }

        $param['bw_ip_group'] = $param['bw_ip_group'] ?? '';
        $param['defence_ip_group'] = $param['defence_ip_group'] ?? '';
        $param['ipv6_group_id'] = $param['ipv6_group_id'] ?? '';
        if(!is_numeric($param['order'])){
            unset($param['order']);
        }
        
        $this->startTrans();
        try{
            $this->update($param, ['id'=>$line['id']], ['name','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','order','ipv6_enable','ipv6_group_id','sync_firewall_rule','order_default_defence']);
            if($param['sync_firewall_rule'] == 1){
                $optionId = $OptionModel
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_DEFENCE)
                        ->where('rel_id', $line->id)
                        ->where('defence_rule_id', 0)
                        ->column('id');
            }else{
                $optionId = $OptionModel
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_DEFENCE)
                        ->where('rel_id', $line->id)
                        ->where('defence_rule_id', '>', 0)
                        ->column('id');
            }
            if(!empty($optionId)){
                $OptionModel->whereIn('id', $optionId)->delete();
                PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];

        $des = [
            'name'              => lang_plugins('mf_cloud_line_name'),
            'bw_ip_group'       => lang_plugins('mf_cloud_line_bw_ip_group'),
            'defence_enable'    => lang_plugins('mf_cloud_line_defence_enable'),
            'defence_ip_group'  => lang_plugins('mf_cloud_line_defence_ip_group'),
            'ip_enable'         => lang_plugins('mf_cloud_line_ip_enable'),
            'link_clone'        => lang_plugins('mf_cloud_line_link_clone'),
            'ipv6_enable'       => lang_plugins('mf_cloud_ipv6_enable'),
            'ipv6_group_id'     => lang_plugins('mf_cloud_line_ipv6_group_id'),
            'sync_firewall_rule'=> lang_plugins('mf_cloud_config_sync_firewall_rule'),
        ];
        $old = $line->toArray();
        $old['defence_enable'] = $switch[ $old['defence_enable'] ];
        $old['ip_enable'] = $switch[ $old['ip_enable'] ];
        $old['link_clone'] = $switch[ $old['link_clone'] ];
        $old['ipv6_enable'] = $switch[ $old['ipv6_enable'] ];
        $old['sync_firewall_rule'] = $switch[ $old['sync_firewall_rule'] ];

        $param['defence_enable'] = $switch[ $param['defence_enable'] ];
        $param['ip_enable'] = $switch[ $param['ip_enable'] ];
        $param['link_clone'] = $switch[ $param['link_clone'] ];
        $param['ipv6_enable'] = $switch[ $param['ipv6_enable'] ];
        $param['sync_firewall_rule'] = $switch[ $param['sync_firewall_rule'] ];

        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $productName = ProductModel::where('id', $productId)->value('name');

            $description = lang_plugins('log_mf_cloud_modify_line_success', [
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
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;

        $recommendConfig = RecommendConfigModel::where('line_id', $id)->find();
        if(!empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
        }
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();

            // 获取线路配置
            $optionId = OptionModel::where('product_id', $productId)
                        ->whereIn('rel_type', [OptionModel::LINE_BW,OptionModel::LINE_FLOW,OptionModel::LINE_DEFENCE,OptionModel::LINE_IP,OptionModel::LINE_IPV6])
                        ->where('rel_id', $id)
                        ->column('id');
            if(!empty($optionId)){
                OptionModel::whereIn('id', $optionId)->delete();
                PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
            }
            
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;
        $productName = ProductModel::where('id', $productId)->value('name');

        $description = lang_plugins('log_mf_cloud_delete_line_success', [
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
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string bw[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int bw[].value - 带宽
     * @return  int bw[].min_value - 最小值
     * @return  int bw[].max_value - 最大值
     * @return  int bw[].step - 步长
     * @return  int flow[].value - 流量
     * @return  int flow[].other_config.in_bw - 进带宽
     * @return  int flow[].other_config.out_bw - 出带宽
     * @return  int flow[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string flow[].other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     * @return  int flow[].bw[].in_bw - 进带宽
     * @return  int flow[].bw[].out_bw - 出带宽
     * @return  int flow_on_demand[].value - 流量按需
     * @return  int flow_on_demand[].other_config.in_bw - 进带宽
     * @return  int flow_on_demand[].other_config.out_bw - 出带宽
     * @return  int flow_on_demand[].other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  int flow_on_demand[].bw[].in_bw - 进带宽
     * @return  int flow_on_demand[].bw[].out_bw - 出带宽
     * @return  string order_default_defence - 防御默认选中配置
     * @return  int defence[].id - 配置ID
     * @return  string defence[].value - 防御峰值
     * @return  string defence[].desc - 防御峰值显示
     * @return  string ip[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ip[].value - IP数量
     * @return  int ip[].min_value - 最小值
     * @return  int ip[].max_value - 最大值
     * @return  int ip[].step - 步长
     * @return  string ipv6[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int ipv6[].value - IP数量
     * @return  int ipv6[].min_value - 最小值
     * @return  int ipv6[].max_value - 最大值
     * @return  int ipv6[].step - 步长
     */
    public function homeLineConfig($id)
    {
        $line = $this->find($id);
        if(empty($line)){
            return (object)[];
        }
        $data = [
            'bill_type'             => $line['bill_type'],
            'sync_firewall_rule'    => $line['defence_enable'] == 1 ? $line['sync_firewall_rule'] : 0,
        ];
        $productId = DataCenterModel::where('id', $line['data_center_id'])->value('product_id') ?? 0;

        $OptionModel = new OptionModel();
        if($line['bill_type'] == 'bw'){
            // 带宽计费
            $bw = $OptionModel
                ->field('type,value,min_value,max_value,step')
                ->where('product_id', $productId)
                ->where('rel_type', OptionModel::LINE_BW)
                ->where('rel_id', $id)
                ->withAttr('value', function($val){
                    return (int)$val;
                })
                ->orderRaw('--value asc,min_value asc')
                ->select()
                ->toArray();
            $data['bw'] = $bw;
        }else{
            // 流量计费
            $flow = OptionModel::field('value,other_config')
                    ->where('product_id', $productId)
                    ->where('rel_type', OptionModel::LINE_FLOW)
                    ->where('rel_id', $id)
                    ->withAttr('other_config', function($val){
                        // 强转类型,以前有脏数据
                        $val['in_bw'] = (int)$val['in_bw'];
                        $val['out_bw'] = (int)$val['out_bw'];
                        $val['traffic_type'] = (int)$val['traffic_type'];
                        return $val;
                    })
                    ->withAttr('value', function($val){
                        return (int)$val;
                    })
                    ->orderRaw('--value asc')
                    ->select()
                    ->toArray();
            // 合并流量中的带宽选项
            $flowArr = [];
            foreach($flow as $k=>$v){
                if(!isset($flowArr[ $v['value'] ])){
                    $flowArr[ $v['value'] ] = $v;
                }
                $flowArr[ $v['value'] ]['bw'][] = [
                    'in_bw' => $v['other_config']['in_bw'],
                    'out_bw' => $v['other_config']['out_bw'],
                ];
            }
            foreach($flowArr as $k=>$v){
                usort($v['bw'],function($a,$b){
                    return $a['out_bw'] > $b['out_bw'] ? 1 : -1;
                });
                $flowArr[$k]['bw'] = $v['bw'];
            }
            $data['flow'] = array_values($flowArr);

            // 流量按需计费
            $flow = OptionModel::field('value,other_config')
                    ->where('product_id', $productId)
                    ->where('rel_type', OptionModel::LINE_FLOW_ON_DEMAND)
                    ->where('rel_id', $id)
                    ->withAttr('other_config', function($val){
                        // 强转类型,以前有脏数据
                        $val['in_bw'] = (int)$val['in_bw'];
                        $val['out_bw'] = (int)$val['out_bw'];
                        $val['traffic_type'] = (int)$val['traffic_type'];
                        return $val;
                    })
                    ->withAttr('value', function($val){
                        return (int)$val;
                    })
                    ->orderRaw('--value asc')
                    ->select()
                    ->toArray();

            // 合并流量中的带宽选项
            $flowArr = [];
            foreach($flow as $k=>$v){
                if(!isset($flowArr[ $v['value'] ])){
                    $flowArr[ $v['value'] ] = $v;
                }
                $flowArr[ $v['value'] ]['bw'][] = [
                    'in_bw' => $v['other_config']['in_bw'],
                    'out_bw' => $v['other_config']['out_bw'],
                ];
            }
            foreach($flowArr as $k=>$v){
                usort($v['bw'],function($a,$b){
                    return $a['out_bw'] > $b['out_bw'] ? 1 : -1;
                });
                $flowArr[$k]['bw'] = $v['bw'];
            }
            $data['flow_on_demand'] = array_values($flowArr);
        }
        if($line['defence_enable'] == 1){
            $lineDefence = OptionModel::field('id,value,firewall_type,defence_rule_id')
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_DEFENCE)
                        ->where('rel_id', $id)
                        ->order('order','asc')
                        ->orderRaw('--value asc')
                        ->select()
                        ->toArray();

            if($line['sync_firewall_rule'] == 1){
                $ConfigModel = new ConfigModel();
                $rule = $ConfigModel->firewallDefenceRule([
                    'product_id'    => $productId,
                ]);

                $defence = [];
                foreach($rule['rule'] as $v){
                    $defence[ $v['type'] ] = array_column($v['list'], 'defense_peak', 'id');
                }

                foreach($lineDefence as $k=>$v){
                    if(!isset($defence[ $v['firewall_type'] ][ $v['defence_rule_id'] ])){
                        unset($lineDefence[$k]);
                        continue;
                    }

                    $lineDefence[$k]['desc'] = $defence[ $v['firewall_type'] ][ $v['defence_rule_id'] ];

                    unset($lineDefence[$k]['firewall_type'],$lineDefence[$k]['defence_rule_id']);
                }
                $lineDefence = array_values($lineDefence);
            }else{
                foreach($lineDefence as $k=>$v){
                    $lineDefence[$k]['desc'] = $v['value'] == 0 ? lang_plugins('mf_cloud_no_defence') : $v['value'].'G';
                    unset($lineDefence[$k]['firewall_type'],$lineDefence[$k]['defence_rule_id']);
                }
            }
            if(!empty($lineDefence)){
                $data['order_default_defence'] = in_array($line['order_default_defence'], array_column($lineDefence, 'value')) ? $line['order_default_defence'] : $lineDefence[0]['value'];
            }else{
                $data['order_default_defence'] = '';
            }
            $data['defence'] = $lineDefence;
        }
        if($line['ip_enable'] == 1){
            $data['ip'] = $OptionModel
                        ->field('type,value,min_value,max_value,step')
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_IP)
                        ->where('rel_id', $id)
                        ->withAttr('value', function($val){
                            return (int)$val;
                        })
                        ->orderRaw('--value asc,min_value asc')
                        ->select()
                        ->toArray();
        }
        if($line['ipv6_enable'] == 1){
            $data['ipv6'] = $OptionModel
                        ->field('type,value,min_value,max_value,step')
                        ->where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_IPV6)
                        ->where('rel_id', $id)
                        ->withAttr('value', function($val){
                            return (int)$val;
                        })
                        ->orderRaw('--value asc,min_value asc')
                        ->select()
                        ->toArray();
        }
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
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
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

        if(!filter_var($param['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            return [];
        }

        $clientId = get_client_id();

        $host = HostModel::find($param['id']);
        if(empty($host) || $host['is_delete'] || $host['client_id'] != $clientId){
            return [];
        }

        $hostLink= HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return [];
        }

        $configData = json_decode($hostLink['config_data'], true);

        $line = $this->find($configData['line']['id']);
        if(empty($line)){
            return [];
        }

        if($line['defence_enable']!=1 || $line['sync_firewall_rule']!=1){
            return [];
        }

        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->where('host_id', $param['id'])->find();
        if(empty($hostIp)){
            return [];
        }
        $dedicateIp = $hostIp['dedicate_ip'];
        $assignIp = array_filter(explode(',', $hostIp['assign_ip']));
        if($dedicateIp!=$param['ip'] && !in_array($param['ip'], $assignIp)){
            return [];
        }

        $defence = OptionModel::field('id,value,firewall_type,defence_rule_id')->where('rel_type', OptionModel::LINE_DEFENCE)->where('rel_id', $line['id'])->order('order', 'asc')->order('id', 'asc')->select()->toArray();
        $hookRes = hook('firewall_set_meal_list', ['product_id' => $host['product_id']]);
        $firewallRule = [];
        foreach ($hookRes as $key => $value) {
            if(isset($value['type']) && !empty($value['list'])){
                foreach ($value['list'] as $v) {
                    $firewallRule[$value['type']][$v['id']] = $v['defense_peak'];
                }
            }
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
        //$ipDefence = IpDefenceModel::where('host_id', $param['id'])->where('ip', $param['ip'])->find();

        return ['defence' => $defence, 'current_defence' => $ipDefence['defence'] ?? ''];
    }

    /**
     * 时间 2024-02-19
     * @title 线路名称获取器
     * @desc  线路名称获取器
     * @author hh
     * @version v1
     * @param   string value - 线路名称 require
     * @return  string
     */
    public function getNameAttr($value)
    {
        if(app('http')->getName() == 'home'){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'name' => $value,
                ],
            ]);
            if(isset($multiLanguage['name'])){
                $value = $multiLanguage['name'];
            }
        }
        return $value;
    }

}
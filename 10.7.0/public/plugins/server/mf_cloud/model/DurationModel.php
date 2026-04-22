<?php 
namespace server\mf_cloud\model;

use app\admin\model\PluginModel;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\ProductOnDemandModel;
use app\common\validate\PayOntrialValidate;
use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel;

/**
 * @title 周期模型
 * @use server\mf_cloud\model\DurationModel
 */
class DurationModel extends Model
{
    // 计算价格后保存在上面
    public static $configData = [];

	protected $name = 'module_mf_cloud_duration';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'num'           => 'int',
        'unit'          => 'string',
        'price_factor'  => 'float',
        'price'         => 'float',
        'create_time'   => 'int',
        'upstream_id'   => 'int',
        'support_apply_for_suspend' => 'int',
        'is_default'    => 'int',
    ];

    protected $clientLevel = [];

    /**
     * 时间 2023-01-31
     * @title 周期列表
     * @desc 周期列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id,num)
     * @param   string param.sort - 升降序(asc,desc)
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.list[].id - 周期ID
     * @return  string data.list[].name - 周期名称
     * @return  int data.list[].num - 周期时长
     * @return  string data.list[].unit - 单位(hour=小时,day=天,month=月)
     * @return  float data.list[].price_factor - 价格系数
     * @return  string data.list[].price - 周期价格
     * @return  string data.list[].ratio - 周期比例
     * @return  int data.list[].is_default - 是否默认周期(0=否,1=是)
     * @return  int data.count - 总条数
     */
    public function durationList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','num'])){
            $param['orderby'] = 'd.id';
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['product_id'])){
                $query->where('d.product_id', $param['product_id']);
            }
        };

        $duration = $this
                ->alias('d')
                ->field('d.id,d.name,d.num,d.unit,d.price_factor,d.price,d.is_default,pdr.ratio')
                ->leftJoin('product_duration_ratio pdr', 'd.product_id=pdr.product_id AND d.id=pdr.duration_id')
                ->withAttr('ratio', function($val){
                    return $val ?? '';
                })
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->group('d.id')
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('d')
                ->where($where)
                ->count();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => $duration,
                'count' => $count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 添加周期
     * @desc 添加周期
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.name - 周期名称 require
     * @param   int param.num - 周期时长 require
     * @param   string param.unit - 单位(hour=小时,day=天,month=月) require
     * @param   float param.price_factor 1 价格系数
     * @param   float param.price 0 周期价格
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 添加成功的周期ID
     */
    public function durationCreate($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $param['create_time'] = time();
        $param['price_factor'] = $param['price_factor'] ?? 1;
        $param['price'] = $param['price'] ?? 0;
        if(!is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }
        if(!is_numeric($param['price'])){
            $param['price'] = 0;
        }

        $duration = $this->create($param, ['product_id','name','num','unit','price_factor','price','create_time']);

        $description = lang_plugins('log_add_duration_success', [
            '{product}' => 'product#'.$ProductModel->id.'#'.$ProductModel->name.'#',
            '{name}'    => $param['name'],
        ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$duration->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 修改周期
     * @desc 修改周期
     * @author hh
     * @version v1
     * @param   int param.id - 周期ID require
     * @param   string param.name - 周期名称 require
     * @param   int param.num - 周期时长 require
     * @param   string param.unit - 单位(hour=小时,day=天,month=月) require
     * @param   float param.price_factor - 价格系数
     * @param   float param.price - 周期价格
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function durationUpdate($param)
    {
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        if(isset($param['price_factor']) && !is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }
        if(isset($param['price']) && !is_numeric($param['price'])){
            $param['price'] = 0;
        }

        $this->update($param, ['id'=>$DurationModel->id], ['name','num','unit','price_factor','price']);

        if($DurationModel['name'] != $param['name']){
            $productName = ProductModel::where('id', $DurationModel['product_id'])->value('name');

            $description = lang_plugins('log_modify_duration_success', [
                '{product}' => 'product#'.$DurationModel['product_id'].'#'.$productName.'#',
                '{name}'    => $DurationModel['name'],
                '{new_name}'=> $param['name']
            ]);
            active_log($description, 'product', $DurationModel['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 删除周期
     * @desc 删除周期
     * @author hh
     * @version v1
     * @param   int param.id - 周期ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function durationDelete($param)
    {
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        $this->startTrans();
        try{
            $this->where('id', $param['id'])->delete();

            PriceModel::where('duration_id', $param['id'])->delete();
            DurationRatioModel::where('product_id', $DurationModel['product_id'])->where('duration_id', $param['id'])->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $productName = ProductModel::where('id', $DurationModel['product_id'])->value('name');

        $description = lang_plugins('log_delete_duration_success', [
            '{product}' => 'product#'.$DurationModel['product_id'].'#'.$productName.'#',
            '{name}'    => $DurationModel['name'],
        ]);
        active_log($description, 'product', $DurationModel['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-12-19
     * @title 设置默认周期
     * @desc 设置默认周期
     * @author wyh
     * @version v1
     * @param   int param.id - 周期ID require
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function setDefault($param)
    {
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        // 验证周期是否属于该商品
        if($DurationModel['product_id'] != $param['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        $this->startTrans();
        try{
            // 先将该商品所有周期设为非默认
            $this->where('product_id', $param['product_id'])->update(['is_default' => 0]);
            
            // 再将指定周期设为默认
            $this->where('id', $param['id'])->update(['is_default' => 1]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('update_fail')];
        }

        $productName = ProductModel::where('id', $DurationModel['product_id'])->value('name');

        $description = lang_plugins('log_set_default_duration_success', [
            '{product}' => 'product#'.$DurationModel['product_id'].'#'.$productName.'#',
            '{name}'    => $DurationModel['name'],
        ]);
        active_log($description, 'product', $DurationModel['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 获取商品配置所有周期价格
     * @desc 获取商品配置所有周期价格
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 商品ID require
     * @param   int param.recommend_config_id - 套餐ID
     * @param   int param.cpu - CPU
     * @param   int param.memory - 内存
     * @param   int param.system_disk.size - 系统盘大小
     * @param   string param.system_disk.disk_type - 系统盘类型
     * @param   int param.data_disk[].size - 数据盘大小
     * @param   string param.data_disk[].disk_type - 系统盘类型
     * @param   int param.line_id - 线路ID
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值(G)
     * @param   int param.ip_num - 附加IP数量
     * @param   int param.gpu_num - 显卡数量
     * @param   int param.image_id 0 镜像ID
     * @param   int param.backup_num 0 备份数量
     * @param   int param.snap_num 0 快照数量
     * @param   int param.ipv6_num 0 IPv6数量
     * @param   int param.is_downstream - 是否下游发起(0=否,1=是)
     * @param   string param.billing_cycle - 计费方式,仅当多种方式时有效
     * @param   bool validate - 是否验证参数正确(false=忽略错误,true=参数不正确会返回错误)
     * @return array
     * @return  int [].id - 周期ID
     * @return  string [].name - 周期名称
     * @return  string [].name_show - 周期名称多语言替换
     * @return  string [].price - 周期总价
     * @return  float [].discount - 折扣(0=没有折扣)
     * @return  int [].num - 周期时长
     * @return  string [].unit - 单位(hour=小时,day=天,month=月)
     * @return  int [].is_default - 是否默认周期(0=否,1=是)
     * @return  string [].client_level_discount - 用户等级折扣金额
     */
    public function getAllDurationPrice($param, $validate = false ,$upgrade = false): array
    {
        $isOntrial = $param['is_ontrial']??0;
        bcscale(2);
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [],
        ];

        $product = ProductModel::find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($product->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $productId = $product['id'];
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        $onDemand = [];
        
        if($product['pay_type'] == 'onetime'){
            $duration = [
                [
                    'id'            => 0,
                    'name'          => lang_plugins('mf_cloud_onetime'),
                    'price_factor'  => 1,
                    'price'         => 0.00,
                ]
            ];
        }else if($product['pay_type'] == 'recurring_prepayment' || $product['pay_type'] == 'recurring_postpaid'){
            $duration = $this->alias('d')
                ->field('d.id,d.name,d.num,d.unit,d.price_factor,d.price,d.is_default,pdr.ratio')
                ->leftJoin('product_duration_ratio pdr','pdr.product_id=d.product_id AND d.id=pdr.duration_id')
                ->where('d.product_id', $productId)
                ->where('pdr.ratio','>',0)
                ->group('d.id')
                ->orderRaw('field(d.unit, "hour","day","month")')
                ->order('d.num', 'asc')
                ->select()
                ->toArray();
        }else if($product['pay_type'] == 'free'){
            $duration = [
                [
                    'id'            => 0,
                    'name'          => lang_plugins('mf_cloud_free'),
                    'price'         => 0.00,
                    'price_factor'  => 1
                ]
            ];
            return $result;
        }else if($product['pay_type'] == 'on_demand'){
            $onDemand = $this->onDemand($productId);
            if(empty($onDemand)){
                return ['status'=>400, 'msg'=>lang_plugins('当前商品未配置按需计费') ];
            }
            $duration = [
                [
                    'id'            => 'on_demand',
                    'name'          => '小时',
                    'price'         => 0,
                    'discount'      => 0,
                    'num'           => 1,
                    'unit'          => 'hour',
                    'price_factor'  => 1,
                ]
            ];
        }else if($product['pay_type'] == 'recurring_prepayment_on_demand'){
            $billingCycle = $param['billing_cycle'] ?? 'recurring_prepayment';
            if(!in_array($billingCycle, ['on_demand','recurring_prepayment'])){
                return ['status'=>400, 'msg'=>lang_plugins('不支持该计费方式') ];
            }
            if($billingCycle == 'on_demand'){
                $onDemand = $this->onDemand($productId);
                if(empty($onDemand)){
                    return ['status'=>400, 'msg'=>lang_plugins('当前商品未配置按需计费') ];
                }
                $duration = [
                    [
                        'id'            => 'on_demand',
                        'name'          => '小时',
                        'price'         => 0,
                        'discount'      => 0,
                        'num'           => 1,
                        'unit'          => 'hour',
                        'price_factor'  => 1,
                    ],
                ];
            }else{
                $duration = $this->alias('d')
                    ->field('d.id,d.name,d.num,d.unit,d.price_factor,d.price,d.is_default,pdr.ratio')
                    ->leftJoin('product_duration_ratio pdr','pdr.product_id=d.product_id AND d.id=pdr.duration_id')
                    ->where('d.product_id', $productId)
                    ->where('pdr.ratio','>',0)
                    ->group('d.id')
                    ->orderRaw('field(d.unit, "hour","day","month")')
                    ->order('d.num', 'asc')
                    ->select()
                    ->toArray();
            }
        }else{
            return $result;
        }
        // 按需不能试用
        if(!empty($onDemand)){
            $isOntrial = 0;
        }

        $OptionModel = new OptionModel();
        $config = ConfigModel::where('product_id', $productId)->find();

        // 获取用户等级折扣
        $clientLevel = $this->getClientLevel([
            'product_id'    => $productId,
            'client_id'     => !empty($param['client_id'])?$param['client_id']:get_client_id(),
        ]);
        if (!$priceAgent){
            $clientLevel = [];
        }

        // 价格组成
        $priceComponent = [];
        $priceDetail = [];
        $priceComponentKeys = []; // 记录每个组件的key，用于判断是否享受等级折扣

        // 套餐
        if(isset($param['recommend_config_id']) && !empty($param['recommend_config_id'])){
            $recommendConfig = RecommendConfigModel::where('product_id', $productId)->find($param['recommend_config_id']);
            if(!empty($recommendConfig)){
                if(!empty($onDemand)){
                    $priceDetail['recommend_config'] = [
                        'on_demand' => $recommendConfig['on_demand_price'],
                    ];
                }else{
                    $price = PriceModel::field('duration_id,price')->where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $recommendConfig['id'])->select()->toArray();

                    $priceDetail['recommend_config'] = array_column($price, 'price', 'duration_id');
                }
                $priceComponent[] = 'recommend_config';
                $priceComponentKeys['recommend_config'] = 'recommend_config';

                $line = LineModel::find($recommendConfig['line_id']);
                if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 1){
                    if(!empty($onDemand)){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'] ?? '', 'on_demand');
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                        }
                        $priceDetail['peak_defence'] = [
                            'on_demand' => $optionDurationPrice['price'],
                        ];
                        $priceComponent[] = 'peak_defence';
                    }else{
                        $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'] ?? '');
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                        }
                        
                        foreach($optionDurationPrice['price'] as $k=>$v){
                            $priceDetail['peak_defence'][$k] = bcmul($v, $recommendConfig['ip_num'], 2);
                        }
                        
                        $priceComponent[] = 'peak_defence';
                    }
                }
                // 试用周期
                if (empty($onDemand) && !empty($product['pay_ontrial'])){
                    $payOntrial = json_decode($product['pay_ontrial'],true);
                    // 商品且套餐开启试用
                    if ($payOntrial['status'] && $recommendConfig['ontrial']){
                        $ontrial = [
                            'id'            => config('idcsmart.pay_ontrial'),
                            'name'          => lang_plugins('mf_cloud_recommend_config_ontrial'),
                            'name_show'     => lang_plugins('mf_cloud_recommend_config_ontrial'),
                            'price'         => $recommendConfig['ontrial_price'],
                            'discount'      => 0,
                            'num'           => $payOntrial['cycle_num']??0,
                            'unit'          => $payOntrial['cycle_type']??'hour',
                        ];
                    }
                }

            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
                }
            }
        }else{
            // 获取cpu周期价格
            if(isset($param['cpu']) && !empty($param['cpu'])){
                if(!empty($onDemand)){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $param['cpu'], 'on_demand');
                }else{
                    $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::CPU, 0, $param['cpu']);
                }
                if($optionDurationPrice['match']){
                    // 按需价格
                    if(!empty($onDemand)){
                        $priceDetail['cpu'] = [
                            'on_demand' => $optionDurationPrice['price'],
                        ];
                    }else{
                        $priceDetail['cpu'] = $optionDurationPrice['price'];
                    }
                    $priceComponent[] = 'cpu';
                    $priceComponentKeys['cpu'] = 'cpu';
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                    }
                }
            }
            // 获取内存周期价格
            if(isset($param['memory']) && !empty($param['memory'])){
                if(!empty($onDemand)){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory'], 'on_demand');
                }else{
                    $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory']);
                }
                if($optionDurationPrice['match']){
                    // 按需价格
                    if($onDemand){
                        $priceDetail['memory'] = [
                            'on_demand' => $optionDurationPrice['price'],
                        ];
                    }else{
                        $priceDetail['memory'] = $optionDurationPrice['price'];
                    }
                    $priceComponent[] = 'memory';
                    $priceComponentKeys['memory'] = 'memory';
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                    }
                }
            }
            // 获取系统盘周期价格
            if(isset($param['system_disk']['size']) && !empty($param['system_disk']['size'])){
                if(!empty($onDemand)){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::SYSTEM_DISK, 0, $param['system_disk']['size'], 'on_demand', $param['system_disk']['disk_type'] ?? '');
                }else{
                    $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::SYSTEM_DISK, 0, $param['system_disk']['size'], $param['system_disk']['disk_type'] ?? '');
                }
                if($optionDurationPrice['match']){
                    // 按需价格
                    if(!empty($onDemand)){
                        $priceDetail['system_disk'] = [
                            'on_demand' => $optionDurationPrice['price'],
                        ];
                    }else{
                        $priceDetail['system_disk'] = $optionDurationPrice['price'];
                    }
                    $priceComponent[] = 'system_disk';
                    $priceComponentKeys['system_disk'] = 'system_disk';
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('system_disk_config_not_found')];
                    }
                }
            }
             // 获取数据盘周期价格
            if(isset($param['data_disk']) && !empty($param['data_disk'])){
                $freeDisk = 0;
                foreach($param['data_disk'] as $k=>$v){
                    // 支持免费盘
                    if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0 && !empty($v['is_free']) && $v['is_free'] == 1 && $freeDisk === 0){
                        $freeDisk = 1;
                    }
                    // 免费盘计算价格
                    if($freeDisk == 1){
                        $freeDisk++;
                        if($v['size'] > $config['free_disk_size']){
                            if(!empty($onDemand)){
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], 'on_demand', $config['free_disk_type'], $config['free_disk_size']);
                            }else{
                                $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $config['free_disk_type'], $config['free_disk_size']);
                            }
                            if($optionDurationPrice['match']){
                                // 按需价格
                                if(!empty($onDemand)){
                                    $priceDetail['data_disk_'.$k] = [
                                        'on_demand' => $optionDurationPrice['price'],
                                    ];
                                }else{
                                    $priceDetail['data_disk_'.$k] = $optionDurationPrice['price'];
                                }
                                $priceComponent[] = 'data_disk_'.$k;
                                $priceComponentKeys['data_disk_'.$k] = 'data_disk';
                            }else{
                                if($validate){
                                    return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                                }
                            }
                        }else if($v['size'] == $config['free_disk_size']){
                            if(!empty($onDemand)){
                                $priceDetail['data_disk_'.$k] = [
                                    'on_demand' => '0.0000',
                                ];
                            }else{
                                $priceDetail['data_disk_'.$k] = [];
                            }
                            $priceComponent[] = 'data_disk_'.$k;
                            $priceComponentKeys['data_disk_'.$k] = 'data_disk';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                            }
                        }
                    }else{
                        if(!empty($onDemand)){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], 'on_demand', $v['disk_type'] ?? '');
                        }else{
                            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $v['disk_type'] ?? '');
                        }
                        if($optionDurationPrice['match']){
                            // 按需价格
                            if(!empty($onDemand)){
                                $priceDetail['data_disk_'.$k] = [
                                    'on_demand' => $optionDurationPrice['price'],
                                ];
                            }else{
                                $priceDetail['data_disk_'.$k] = $optionDurationPrice['price'];
                            }
                            $priceComponent[] = 'data_disk_'.$k;
                            $priceComponentKeys['data_disk_'.$k] = 'data_disk';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                            }
                        }
                    }
                }
            }
            // 有数据中心才能选择GPU
            if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
                // GPU数量
                if(isset($param['gpu_num']) && !empty($param['gpu_num'])){
                    if(!empty($onDemand)){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_CENTER_GPU, $param['data_center_id'], $param['gpu_num'], 'on_demand');
                    }else{
                        $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::DATA_CENTER_GPU, $param['data_center_id'], $param['gpu_num']);
                    }
                    if($optionDurationPrice['match']){
                        // 按需价格
                        if(!empty($onDemand)){
                            $priceDetail['gpu_num'] = [
                                'on_demand' => $optionDurationPrice['price'],
                            ];
                        }else{
                            $priceDetail['gpu_num'] = $optionDurationPrice['price'];
                        }
                        $priceComponent[] = 'gpu_num';
                        $priceComponentKeys['gpu_num'] = 'gpu_num';
                    }else{
                        if($validate){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_gpu_num_not_found')];
                        }
                    }
                }
            }
            // 有线路才能选择防御和附加IP
            if(isset($param['line_id']) && !empty($param['line_id'])){
                $line = LineModel::find($param['line_id']);
                if(!empty($line) && $line['hidden'] == 0){
                    if($line['bill_type'] == 'bw'){
                        // 获取带宽周期价格
                        if(isset($param['bw']) && !empty($param['bw'])){
                            if(!empty($onDemand)){
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], 'on_demand');
                            }else{
                                $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw']);
                            }
                            if($optionDurationPrice['match']){
                                // 按需价格
                                if(!empty($onDemand)){
                                    $priceDetail['bw'] = [
                                        'on_demand' => $optionDurationPrice['price'],
                                    ];
                                }else{
                                    $priceDetail['bw'] = $optionDurationPrice['price'];
                                }
                                $priceComponent[] = 'bw';
                                $priceComponentKeys['bw'] = 'bw';
                            }else{
                                if($validate){
                                    return ['status'=>400, 'msg'=>lang_plugins('line_bw_not_found')];
                                }
                            }
                        }
                    }else if($line['bill_type'] == 'flow'){
                        // 获取流量周期价格
                        if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
                            // 按需
                            if(!empty($onDemand)){
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW_ON_DEMAND, $line['id'], 0, 'on_demand', $param['bw'] ?? 0);
                            }else{
                                $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $param['bw'] ?? 0);
                            }
                            if($optionDurationPrice['match']){
                                if(!empty($onDemand)){
                                    $priceDetail['flow'] = [
                                        'on_demand' => $optionDurationPrice['price'],
                                    ];
                                }else{
                                    $priceDetail['flow'] = $optionDurationPrice['price'];
                                }
                                $priceComponent[] = 'flow';
                                $priceComponentKeys['flow'] = 'flow';
                            }else{
                                if($validate){
                                    return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found')];
                                }
                            }
                        }
                    }
                    // 防护
                    if(isset($param['peak_defence']) && $line['defence_enable'] == 1){
                        if($line['sync_firewall_rule'] == 1){
                            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'] ?? '');
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                            }
                            // $ConfigModel = new ConfigModel();
                            // $rule = $ConfigModel->getFirewallDefenceRule([
                            //     'product_id'        => $productId,
                            //     'firewall_type'     => $optionDurationPrice['option']['firewall_type'],
                            //     'defence_rule_id'   => $optionDurationPrice['option']['defence_rule_id'],
                            // ]);
                            // if(empty($rule)){
                            //     return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                            // }
                            // 计算IP数量
                            $ipNum = 0;
                            if($line['ip_enable'] == 1 && isset($param['ip_num']) && is_numeric($param['ip_num']) && $param['ip_num'] >= 0){
                                $ipNum = $param['ip_num'];
                            }
                            // 是否有免费IP
                            $defaultOneIpv4 = $config['default_one_ipv4'] ?? 1;
                            if($defaultOneIpv4){
                                $isNat = false;
                                if(is_numeric($config['nat_acl_limit']) && ($config['default_nat_acl'] == 1 || (isset($param['nat_acl_limit_enable']) && $param['nat_acl_limit_enable'] == 1))){
                                    $isNat = true;
                                }
                                if(is_numeric($config['nat_web_limit']) && ($config['default_nat_web'] == 1 || (isset($param['nat_web_limit_enable']) && $param['nat_web_limit_enable'] == 1))){
                                    $isNat = true;
                                }
                                if(!$isNat){
                                    $ipNum += 1;
                                }
                            }
                            foreach($optionDurationPrice['price'] as $k=>$v){
                                $priceDetail['peak_defence'][$k] = bcmul($v, $upgrade?1:$ipNum, 2);
                            }
                            $priceComponent[] = 'peak_defence';
                            $priceComponentKeys['peak_defence'] = 'peak_defence';
                        }else if(is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0){
                            if(!empty($onDemand)){
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], 'on_demand');
                            }else{
                                $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence']);
                            }
                            if($optionDurationPrice['match']){
                                // 按需价格
                                if(!empty($onDemand)){
                                    $priceDetail['peak_defence'] = [
                                        'on_demand' => $optionDurationPrice['price'],
                                    ];
                                }else{
                                    $priceDetail['peak_defence'] = $optionDurationPrice['price'];
                                }
                                $priceComponent[] = 'peak_defence';
                                $priceComponentKeys['peak_defence'] = 'peak_defence';
                            }else{
                                if($validate){
                                    return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found')];
                                }
                            }
                        }
                    }
                    // 附加IP
                    if(isset($param['ip_num']) && is_numeric($param['ip_num']) && $param['ip_num'] >=0){
                        if(!empty($onDemand)){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], 'on_demand');
                        }else{
                            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num']);
                        }
                        if($optionDurationPrice['match']){
                            // 按需价格
                            if(!empty($onDemand)){
                                $priceDetail['ip_num'] = [
                                    'on_demand' => $optionDurationPrice['price'],
                                ];
                            }else{
                                $priceDetail['ip_num'] = $optionDurationPrice['price'];
                            }
                            $priceComponent[] = 'ip_num';
                            $priceComponentKeys['ip_num'] = 'ipv4';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('line_add_ip_not_found')];
                            }
                        }
                    }
                    // ipv6数量
                    if(isset($param['ipv6_num']) && is_numeric($param['ipv6_num']) && $param['ipv6_num'] >= 0){
                        if(!empty($onDemand)){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $param['ipv6_num'], 'on_demand');
                        }else{
                            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $param['ipv6_num']);
                        }
                        if($optionDurationPrice['match']){
                            // 按需价格
                            if(!empty($onDemand)){
                                $priceDetail['ipv6_num'] = [
                                    'on_demand' => $optionDurationPrice['price'],
                                ];
                            }else{
                                $priceDetail['ipv6_num'] = $optionDurationPrice['price'];
                            }
                            $priceComponent[] = 'ipv6_num';
                            $priceComponentKeys['ipv6_num'] = 'ipv6';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('line_add_ip_not_found')];
                            }
                        }
                    }
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
                    }
                }
            }
        }
        // 获取镜像周期价格
        $imagePrice = 0;
        if(isset($param['image_id']) && !empty($param['image_id']) ){
            $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
            // 验证镜像
            if(!empty($image) && $image['charge'] == 1 && !empty($image['price'])){
                $imagePrice = $isOntrial?0:$image['price']; // 续费时，试用不算镜像价格
            }
        }
        
        // 备份快照
        $otherPrice = 0;
        $BackupConfigModel = new BackupConfigModel();
        // 暂时没用,注释掉
        if($config['backup_enable'] == 1){
            if(!empty($param['backup_num'])){
                $backupConfig = $BackupConfigModel->matchBackupConfig([
                    'product_id' => $productId,
                    'type'       => 'backup',
                    'num'        => $param['backup_num'],
                ]);
                if(!empty($backupConfig)){
                    // 按需价格
                    if(!empty($onDemand)){
                        $otherPrice = bcadd($otherPrice, $backupConfig['on_demand_price'], 4);
                    }else{
                        $otherPrice = bcadd($otherPrice, $backupConfig['price'], 2);
                    }
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('backup_num_error')];
                    }
                }
            }
        }
        if($config['snap_enable'] == 1){
            if(!empty($param['snap_num'])){
                $backupConfig = $BackupConfigModel->matchBackupConfig([
                    'product_id' => $productId,
                    'type'       => 'snap',
                    'num'        => $param['snap_num'],
                ]);
                if(!empty($backupConfig)){
                    // 按需价格
                    if(!empty($onDemand)){
                        $otherPrice = bcadd($otherPrice, $backupConfig['on_demand_price'], 4);
                    }else{
                        $otherPrice = bcadd($otherPrice, $backupConfig['price'], 2);
                    }
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('snap_num_error')];
                    }
                }
            }
        }
        // 快照备份基准
        $base = [];

        $scale = 2;
        if(!empty($onDemand)){
            $scale = 4;
        }
        bcscale($scale);

        $data = [];
        foreach($duration as $k=>$v){
            if(empty($v['id'])){
                continue;
            }
            if(empty($base)){
                $base = $v;
            }
            $price = 0;
            foreach($priceComponent as $vv){
                $price = bcadd($price, $priceDetail[$vv][$v['id']] ?? 0);
            }
            $price = bcadd($price, $imagePrice);
            // 其他价格
            if(!empty($otherPrice)){
                // 基础周期,直接加上当前价格
                if($v['id'] == $base['id']){
                    $price = bcadd($price, $otherPrice);
                }else{
                    // 计算周期比例
                    $multiple = bcdiv($v['ratio'], $base['ratio']);
                    $price = bcadd($price, bcmul($multiple, $otherPrice));
                }
            }
            // 加上周期价格
            $price = bcadd($price, $v['price']);

            // 计算用户等级折扣
            $clientLevelDiscount = '0.00';
            // 试用周期不计算折扣
            if(!empty($clientLevel) && $v['id'] != config('idcsmart.pay_ontrial')){
                $discountablePrice = 0;
                // 按需计费使用4位小数
                $discountScale = !empty($onDemand) ? 4 : 2;

                // 订购可折扣金额
                $discountPrice = 0;
                // 遍历价格组件，根据配置判断是否享受等级折扣
                foreach($priceComponent as $componentKey){
                    $componentPrice = $priceDetail[$componentKey][$v['id']] ?? 0;
                    $canDiscount = true;
                    $canOrderDiscount = true;
                    
                    // 根据配置开关判断是否享受等级折扣
                    if(isset($priceComponentKeys[$componentKey])){
                        $key = $priceComponentKeys[$componentKey];
                        if($key == 'cpu' && isset($config['level_discount_cpu_order']) && $config['level_discount_cpu_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'memory' && isset($config['level_discount_memory_order']) && $config['level_discount_memory_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'bw' && isset($config['level_discount_bw_order']) && $config['level_discount_bw_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'system_disk' && isset($config['level_discount_system_disk_order']) && $config['level_discount_system_disk_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'data_disk' && isset($config['level_discount_data_disk_order']) && $config['level_discount_data_disk_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'ipv4' && isset($config['level_discount_ipv4_order']) && $config['level_discount_ipv4_order'] == 0){
                            $canOrderDiscount = false;
                        }else if($key == 'ipv6' && isset($config['level_discount_ipv6_order']) && $config['level_discount_ipv6_order'] == 0){
                            $canOrderDiscount = false;
                        }
                    }
                    
                    if($canOrderDiscount){
                        $discountPrice = bcadd($discountPrice, $componentPrice, $discountScale);
                    }

                    // 根据配置开关判断是否享受等级折扣
                    if(isset($priceComponentKeys[$componentKey])){
                        $key = $priceComponentKeys[$componentKey];
                        if($key == 'cpu' && isset($config['level_discount_cpu_renew']) && $config['level_discount_cpu_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'memory' && isset($config['level_discount_memory_renew']) && $config['level_discount_memory_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'bw' && isset($config['level_discount_bw_renew']) && $config['level_discount_bw_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'system_disk' && isset($config['level_discount_system_disk_renew']) && $config['level_discount_system_disk_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'data_disk' && isset($config['level_discount_data_disk_renew']) && $config['level_discount_data_disk_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'ipv4' && isset($config['level_discount_ipv4_renew']) && $config['level_discount_ipv4_renew'] == 0){
                            $canDiscount = false;
                        }else if($key == 'ipv6' && isset($config['level_discount_ipv6_renew']) && $config['level_discount_ipv6_renew'] == 0){
                            $canDiscount = false;
                        }
                    }
                    
                    if($canDiscount){
                        $discountablePrice = bcadd($discountablePrice, $componentPrice, $discountScale);
                    }
                }
                
                // 计算折扣金额
                if($discountablePrice > 0){
                    $clientLevelDiscount = bcdiv(bcmul($discountablePrice, $clientLevel['discount_percent'], $discountScale), 100, $discountScale);
                }
            }

            // if($price == 0){
            //     continue;
            // }
            // if(empty($base) && $price>0){
            //     $base = [
            //         'unit'  => $v['unit'],
            //         'num'   => $v['num'],
            //         'price' => $price
            //     ];
            // }

            $discount = 0;
            if($v['price_factor'] < 1){
                $discount = round($v['price_factor']*10, 1);
            }

            // 非按需
            if($v['id'] != 'on_demand'){
                $discountPrice = bcmul($discountPrice??0, $v['price_factor']);
                if($isDownstream && $priceAgent){
                    $price = $this->downstreamSubClientLevelPrice([
                        'product_id' => $productId,
                        'client_id'  => get_client_id(),
                        'price'      => $discountPrice,
                    ]);
                }
            }
            // if(isset($base['price'])){
            //     $discount = round($price / $base['price'] / $multiplier * 10, 1);
            // }else{
            //     $discount = 0;
            // }

            $durationName = multi_language_replace($v['name']);

            $data[] = [
                'id'            => $v['id'],
                'name'          => $v['name'],
                'name_show'     => $durationName,
                'price'         => $price,
                'discount'      => $discount < 10 ? $discount : 0,
                'num'           => $v['num'] ?? 0,
                'unit'          => $v['unit'] ?? '',
                'discount_order_price'  => $discountPrice ?? 0,            // 订购可折扣金额
                'discount_renew_price'  => $discountablePrice ?? '0.0000', // 续费可折扣金额
                'is_default'    => $v['is_default'] ?? 0,
            ];
        }
        // 代理以及保存默认价格排除试用
        if (!empty($ontrial) && empty($param['is_downstream']) && empty($param['set_price'])){
            array_unshift($data,$ontrial);
        }

        $result['data'] = $data;
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 配置计算价格
     * @desc 配置计算价格
     * @author hh
     * @version v1
     * @param   ProductModel param.product - 商品模型实例 require
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.recommend_config_id - 套餐ID
     * @param   int param.custom.data_center_id - 数据中心ID
     * @param   int param.custom.cpu - CPU
     * @param   int param.custom.memory - 内存
     * @param   int param.custom.system_disk.size - 系统盘大小(G)
     * @param   string param.custom.system_disk.disk_type - 系统盘类型
     * @param   int param.custom.data_disk[].size - 数据盘大小(G)
     * @param   string param.custom.data_disk[].disk_type - 数据盘类型
     * @param   int param.custom.data_disk[].is_free - 是否免费盘(0=否,1=是)
     * @param   int param.custom.line_id - 线路ID
     * @param   int param.custom.bw - 带宽(Mbps)
     * @param   int param.custom.flow - 流量(G)
     * @param   string param.custom.peak_defence - 防御峰值
     * @param   int param.custom.gpu_num - 显卡数量
     * @param   int param.custom.image_id - 镜像ID
     * @param   int param.custom.ssh_key_id - SSH密钥ID
     * @param   int param.custom.backup_num 0 备份数量
     * @param   int param.custom.snap_num 0 快照数量
     * @param   int param.custom.ip_num 0 IP数量
     * @param   int param.custom.ipv6_num 0 IPv6数量
     * @param   int param.custom.ip_mac_bind_enable 0 嵌套虚拟化(0=关闭,1=开启)
     * @param   int param.custom.nat_acl_limit_enable 0 是否启用NAT转发(0=关闭,1=开启)
     * @param   int param.custom.nat_web_limit_enable 0 是否启用NAT建站(0=关闭,1=开启)
     * @param   int param.custom.resource_package_id 0 资源包ID
     * @param   string param.custom.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @param   int param.custom.vpc.id - VPC网络ID
     * @param   string param.custom.vpc.ips - VPCIP段
     * @param   int param.custom.port - 端口
     * @param   string param.custom.billing_cycle - 计费方式,仅当多种方式时有效
     * @param   int param.custom.rand_password 0 生成随机密码(0=否,1=是)
     * @param   int param.custom.rand_port 0 生成随机端口(0=否,1=是)
     * @param   bool only_cal - 是否仅计算价格(false=否,true=是)
     * @return  array
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格 
     * @return  string data.renew_price - 续费价格 
     * @return  string data.billing_cycle - 周期 
     * @return  int data.duration - 周期时长
     * @return  string data.description - 订单子项描述
     * @return  string data.base_price - 基础价格
     * @return  string data.billing_cycle_name - 周期名称多语言
     * @return  string data.preview[].name - 配置项名称
     * @return  string data.preview[].value - 配置项值
     * @return  string data.preview[].price - 配置项价格
     * @return  string data.discount - 用户等级折扣
     * @return  string data.order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int data.order_item[].rel_id - 关联ID
     * @return  float data.order_item[].amount - 子项金额
     * @return  string data.order_item[].description - 子项描述
     * @return  string data.discount_order_price - 订购时可以应用用户等级的金额(折扣前)
     */
    public function cartCalculatePrice($param, $only_cal = true)
    {
        bcscale(2);

        $custom = $param['custom'];
        $position = $param['position'] ?? 0;

        $qty = $custom['settle_qty']??1;
        // wyh 20250827 新增
        $baseParam = request()->param();
        $priceBasis = $baseParam['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        $product = $param['product'];
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $productId = $product['id'];

        // 试用
        $ontrial = $custom['duration_id']==config('idcsmart.pay_ontrial');
        if ($ontrial){
            if(!empty($custom['recommend_config_id'])){
                $recommendConfig = RecommendConfigModel::find($custom['recommend_config_id']);
            }
            if (empty($recommendConfig) || empty($recommendConfig['ontrial'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_recommend_config_pay_ontrial_not_open')];
            }
            if ($recommendConfig['ontrial_stock_control'] && $recommendConfig['ontrial_qty'] < $qty){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_recommend_config_pay_ontrial_stock_control_not_enough')];
            }
            $PayOntrialValidate = new PayOntrialValidate();
            if (!$PayOntrialValidate->scene('pay_ontrial')->check(['product_id'=>$productId,'qty'=>$qty,'client_id'=>get_client_id()])){
                return ['status'=>400, 'msg'=>$PayOntrialValidate->getError()];
            }
            $payOntrial = json_decode($product['pay_ontrial'],true) ?? [];
        }
        // 按需
        $onDemand = [];

        $configData = [];
        $billingCycle = $product['pay_type'];
        if($product['pay_type'] == 'onetime'){
            $duration = [
                'id'    => 0,
                'name'  => lang_plugins('mf_cloud_onetime'),
                'price' => '0.00',
            ];
            // TODO 一次性怎么计算?
        }else if($product['pay_type'] == 'recurring_prepayment' || $product['pay_type'] == 'recurring_postpaid'){
            $firstDuration = $this->firstDuration($productId);

            if(empty($firstDuration)){
                return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
            }
            // 试用
            if ($ontrial){
                $multiplier = 1;
                $durationTime = 0;
                if ($payOntrial['cycle_type']=='hour'){
                    $durationTime = $payOntrial['cycle_num'] * 3600;
                }elseif ($payOntrial['cycle_type']=='day'){
                    $durationTime = $payOntrial['cycle_num'] * 86400;
                }elseif ($payOntrial['cycle_type']=='month'){
                    $durationTime = strtotime('+ ' . $payOntrial['cycle_num'] . ' month') - time();
                }
                $duration =  [
                    'id'            => config('idcsmart.pay_ontrial'),
                    'name'          => lang_plugins('mf_cloud_recommend_config_ontrial'),
                    'name_show'     => lang_plugins('mf_cloud_recommend_config_ontrial'),
                    'price'         => 0,//$recommendConfig['ontrial_price'],//试用基础价格为0
                    'discount'      => 0,
                    'num'           => $payOntrial['cycle_num']??0,
                    'unit'          => $payOntrial['cycle_type']??'hour',
                    'price_factor'  => 1,
                ];
            }else{
                $duration = $this->where('product_id', $productId)->where('id', $custom['duration_id'])->find();
                if(empty($duration)){
                    return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
                }
                // 计算倍率
                if($duration['unit'] == $firstDuration['unit']){
                    $multiplier = round($duration['num']/$firstDuration['num'], 2);
                }else{
                    if($duration['unit'] == 'day' && $firstDuration['unit'] == 'hour'){
                        $multiplier = round($duration['num']*24/$firstDuration['num'], 2);
                    }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'hour'){
                        $multiplier = round($duration['num']*30*24/$firstDuration['num'], 2);
                    }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'day'){
                        $multiplier = round($duration['num']*30/$firstDuration['num'], 2);
                    }
                }

                $durationTime = 0;
                if($duration['unit'] == 'month'){
                    $durationTime = strtotime('+ '.$duration['num'].' month') - time();
                }else if($duration['unit'] == 'day'){
                    $durationTime = $duration['num'] * 3600 * 24;
                }else if($duration['unit'] == 'hour'){
                    $durationTime = $duration['num'] * 3600;
                }
            }
        }else if($product['pay_type'] == 'free'){
            $duration = [
                'id'    => 0,
                'name'  => lang_plugins('mf_cloud_free'),
                'price' => '0.00',
            ];
        }else if($product['pay_type'] == 'on_demand'){
            $onDemand = $this->onDemand($productId);
            if(empty($onDemand)){
                return ['status'=>400, 'msg'=>lang_plugins('当前商品未配置按需计费') ];
            }
            $duration = [
                'id'            => 'on_demand',
                'name'          => '小时',
                'price'         => 0,
                'discount'      => 0,
                'num'           => 1,
                'unit'          => 'hour',
                'price_factor'  => 1,
            ];
            $durationTime = 0;
        }else if($product['pay_type'] == 'recurring_prepayment_on_demand'){
            $billingCycle = $custom['billing_cycle'] ?? 'recurring_prepayment';
            if(!in_array($billingCycle, ['on_demand','recurring_prepayment'])){
                return ['status'=>400, 'msg'=>lang_plugins('不支持该计费方式') ];
            }
            if($billingCycle == 'on_demand'){
                $onDemand = $this->onDemand($productId);
                if(empty($onDemand)){
                    return ['status'=>400, 'msg'=>lang_plugins('当前商品未配置按需计费') ];
                }
                $duration = [
                    'id'            => 'on_demand',
                    'name'          => '小时',
                    'price'         => 0,
                    'discount'      => 0,
                    'num'           => 1,
                    'unit'          => 'hour',
                    'price_factor'  => 1,
                ];
                $durationTime = 0;
            }else{
                if ($ontrial){
                    $multiplier = 1;
                    $durationTime = 0;
                    if ($payOntrial['cycle_type']=='hour'){
                        $durationTime = $payOntrial['cycle_num'] * 3600;
                    }elseif ($payOntrial['cycle_type']=='day'){
                        $durationTime = $payOntrial['cycle_num'] * 86400;
                    }elseif ($payOntrial['cycle_type']=='month'){
                        $durationTime = strtotime('+ ' . $payOntrial['cycle_num'] . ' month') - time();
                    }
                    $duration =  [
                        'id'            => config('idcsmart.pay_ontrial'),
                        'name'          => lang_plugins('mf_cloud_recommend_config_ontrial'),
                        'name_show'     => lang_plugins('mf_cloud_recommend_config_ontrial'),
                        'price'         => 0,//$recommendConfig['ontrial_price'],//试用基础价格为0
                        'discount'      => 0,
                        'num'           => $payOntrial['cycle_num']??0,
                        'unit'          => $payOntrial['cycle_type']??'hour',
                        'price_factor'  => 1,
                    ];
                }else{
                    $duration = $this->where('product_id', $productId)->where('id', $custom['duration_id'])->find();
                    if(empty($duration)){
                        return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
                    }
                    $durationTime = 0;
                    if($duration['unit'] == 'month'){
                        $durationTime = strtotime('+ '.$duration['num'].' month') - time();
                    }else if($duration['unit'] == 'day'){
                        $durationTime = $duration['num'] * 3600 * 24;
                    }else if($duration['unit'] == 'hour'){
                        $durationTime = $duration['num'] * 3600;
                    }
                }
            }
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('不支持该计费方式') ];
        }
        // 按需不支持试用
        if(!empty($onDemand)){
            $ontrial = false;
        }

        $config = ConfigModel::where('product_id', $productId)->find();
        $configData['duration'] = $duration;

        $preview = [];
        $keepTimePrice = 0;
        $onDemandFlowPrice = 0;

        $durationName = multi_language_replace($duration['name']);
        
        // 周期基础价格
        if(empty($onDemand)){
            $preview[] = [
                'name'  => lang_plugins('mf_cloud_time_duration'),
                'value' => $durationName,
                'price' => $duration['price'],
            ];
        }

        $OptionModel = new OptionModel();
        // 套餐版价格
        if(isset($custom['recommend_config_id']) && !empty($custom['recommend_config_id'])){
            $recommendConfig = RecommendConfigModel::find($custom['recommend_config_id']);
            if(empty($recommendConfig) || $recommendConfig['product_id'] != $productId){
                return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
            }
            $dataCenter = DataCenterModel::where('product_id', $productId)->where('id', $recommendConfig['data_center_id'])->find();
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_error')];
            }
            $line = LineModel::find($recommendConfig['line_id']);
            if(empty($line)){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found') ];
            }

            // $preview[] = [
            //     'name'  =>  lang_plugins('country'),
            //     'value' =>  $dataCenter->getCountryName($dataCenter),
            //     'price' =>  0,
            // ];

            $configData['data_center'] = $dataCenter;

            // 获取套餐价格
            if(!empty($onDemand)){
                $price = $recommendConfig['on_demand_price'];
                $onDemandFlowPrice = $recommendConfig['on_demand_flow_price'];
            }else{
                $price = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $recommendConfig['id'])->where('duration_id', $custom['duration_id'])->value('price');
            }
            $preview[] = [
                'name'  => lang_plugins('mf_cloud_recommend_config'),
                'value' => $recommendConfig['name'],
                'price' => $ontrial?$recommendConfig['ontrial_price']:($price ?? '0.00'),
            ];

            $defencePreview = [];

            if($line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 1){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $custom['peak_defence'] ?? '', $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                }
                $ConfigModel = new ConfigModel();
                $rule = $ConfigModel->getFirewallDefenceRule([
                    'product_id'        => $productId,
                    'firewall_type'     => $optionDurationPrice['option']['firewall_type'],
                    'defence_rule_id'   => $optionDurationPrice['option']['defence_rule_id'],
                ]);
                if(empty($rule)){
                    return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                }

//                $defencePrice = bcmul($optionDurationPrice['price'] ?? 0, $recommendConfig['ip_num'], 2);
                $ipNum = $recommendConfig['ip_num'];
                $defencePrice = bcmul($optionDurationPrice['price'] ?? 0, 1, 2);
                // 试用，不算防御价格
                $defencePrice = $ontrial?bcsub(0,0,2):$defencePrice;

//                $preview[] = [
//                    'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
//                    'value' => $rule['defense_peak'],
//                    'price' => $defencePrice,
//                ];
                $defencePreview[] = [
                    'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
                    'value' => $rule['defense_peak'],
                    'price' => $defencePrice,
                ];

                // 计算价格
                $price = bcadd($price, $defencePrice);

//                $configData['defence'] = [
//                    'value' => $optionDurationPrice['option']['value'],
//                    'firewall_type' => $optionDurationPrice['option']['firewall_type'],
//                    'defence_rule_id' => $optionDurationPrice['option']['defence_rule_id'],
//                ];
                $defenceConfigData['defence'] = [
                    'value' => $optionDurationPrice['option']['value'],
                    'firewall_type' => $optionDurationPrice['option']['firewall_type'],
                    'defence_rule_id' => $optionDurationPrice['option']['defence_rule_id'],
                ];
                $defenceConfigData['line'] = $line;
                $defenceConfigData['duration'] = $duration;
            }else{
                // 记录防御
                $configData['defence'] = [
                    'value' => $recommendConfig['peak_defence'],
                    'firewall_type' => '',
                    'defence_rule_id' => 0,
                ];
            }

            // 修复套餐免费盘
            if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0){
                $configData['data_disk'][] = [
                    'value'         => $config['free_disk_size'],
                    'price'         => 0,
                    'other_config'  => [
                        'disk_type' => $config['free_disk_type'],
                        'store_id'  => '',
                    ],
                    'is_free'       => 1,
                    'free_size'     => $config['free_disk_size'],
                ];
            }

            // 填充以前购买时的数据,
            $configData['recommend_config'] = $recommendConfig;
            $configData['cpu'] = [
                'value' => $recommendConfig['cpu'],
            ];
            $configData['memory'] = [
                'value' => $recommendConfig['memory'],
            ];
            $configData['system_disk'] = [
                'value' => $recommendConfig['system_disk_size'],
                'price' => '0.00',
                'other_config' => [
                    'disk_type' => $recommendConfig['system_disk_type'],
                ],
            ];
            if(!empty($recommendConfig['data_disk_size'])){
                $configData['data_disk'][] = [
                    'value'         => $recommendConfig['data_disk_size'],
                    'price'         => '0.00',
                    'other_config'  => [
                        'disk_type' => $recommendConfig['data_disk_type']
                    ],
                ];
            }
            $configData['line'] = $line;
            if($line['bill_type'] == 'bw'){
                $configData['bw'] = [
                    'value'         => $recommendConfig['bw'],
                    'other_config'  => [
                        'in_bw'     => $recommendConfig['in_bw'],
                    ],
                ];
                $preview[] = [
                    'name'  => lang_plugins('bw'),
                    'value' => $recommendConfig['bw'] == 0 ? lang_plugins('mf_cloud_unlimited') : $recommendConfig['bw'].'Mbps',
                    'price' => 0,
                    'key'   => 'bw',
                ];
            }else if($line['bill_type'] == 'flow'){
                if(!empty($onDemand)){
                    $preview[] = [
                        'name'  => lang_plugins('flow'),
                        'value' => lang_plugins('on_demand_billing'),
                        'price' => $recommendConfig['on_demand_flow_price'],
                        'key'   => 'flow',
                    ];
                }else{
                    $preview[] = [
                        'name'  => lang_plugins('flow'),
                        'value' => $recommendConfig['flow'] == 0 ? lang_plugins('mf_cloud_unlimited_flow') : $recommendConfig['flow'].'G',
                        'price' => 0,
                        'key'   => 'flow',
                    ];
                }
                // 同时追加带宽显示
                $preview[] = [
                    'name'  => lang_plugins('bw'),
                    'value' => $recommendConfig['bw'] == 0 ? lang_plugins('mf_cloud_unlimited') : $recommendConfig['bw'].'Mbps',
                    'price' => 0,
                ];
                // $recommendConfig['flow'] = 0;

                $configData['flow'] = [
                    'value' => $recommendConfig['flow'],
                    'other_config'      => [
                        'out_bw'        => $recommendConfig['bw'],
                        'in_bw'         => $recommendConfig['in_bw'],
                        'traffic_type'  => $recommendConfig['traffic_type'],
                    ],
                ];
            }
            // if($recommendConfig['ip_num'] > 1){
            $configData['ip'] = [
                'value' => $recommendConfig['ip_num'] - 1,
            ];
            // }
            if($recommendConfig['gpu_num'] > 0){
                $configData['gpu_num'] = $recommendConfig['gpu_num'];
                $configData['gpu_name'] = $dataCenter['gpu_name'];
            }
            if($recommendConfig['ipv6_num'] > 0){
                $configData['ipv6_num'] = $recommendConfig['ipv6_num'];
            }
            $configData['due_not_free_gpu'] = $recommendConfig['due_not_free_gpu'];
        }else{
            if($config['only_sale_recommend_config'] == 1){
                return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
            }
            $configData['due_not_free_gpu'] = 0;
            
            $dataCenter = [];
            if(isset($custom['data_center_id']) && !empty($custom['data_center_id'])){
                $dataCenter = DataCenterModel::where('product_id', $productId)->where('id', $custom['data_center_id'])->find();
                if(empty($dataCenter)){
                    return ['status'=>400, 'msg'=>lang_plugins('data_center_error')];
                }
                // $preview[] = [
                //     'name'  =>  lang_plugins('country'),
                //     'value' =>  $dataCenter->getCountryName($dataCenter),
                //     'price' =>  0,
                // ];

                $configData['data_center'] = $dataCenter;
            }

            // 获取cpu周期价格
            if(isset($custom['cpu']) && !empty($custom['cpu'])){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $custom['cpu'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
                }
                $preview[] = [
                    'name'  => 'CPU',
                    'value' => $custom['cpu'] . lang_plugins('mf_cloud_core'),
                    'price' => $optionDurationPrice['price'],
                    'key'   => 'cpu',
                ];

                $configData['cpu'] = [
                    'value' => (int)$custom['cpu'],
                    'price' => $optionDurationPrice['price'],
                    'other_config' => $optionDurationPrice['option']['other_config'],
                ];
                // 保留项目价格
                if(!empty($onDemand) && in_array('cpu', $onDemand['keep_time_billing_item'])){
                    $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                }
            }else{
                if(!$only_cal){
                    return ['status'=>400, 'msg'=>lang_plugins('please_select_cpu_config')];
                }
            }
            // 获取内存周期价格
            if(isset($custom['memory']) && !empty($custom['memory'])){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $custom['memory'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                }
                $preview[] = [
                    'name'  =>  lang_plugins('memory'),
                    'value' =>  $custom['memory'].$config['memory_unit'],
                    'price' =>  $optionDurationPrice['price'] ?? 0,
                    'key'   => 'memory',
                ];

                $configData['memory'] = [
                    'value' => (int)$custom['memory'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                $configData['memory_unit'] = $config['memory_unit'];

                // 保留项目价格
                if(!empty($onDemand) && in_array('memory', $onDemand['keep_time_billing_item'])){
                    $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                }
            }else{
                if(!$only_cal){
                    return ['status'=>400, 'msg'=>lang_plugins('please_select_memory_config')];
                }
            }
            // 获取系统盘周期价格
            if(isset($custom['system_disk']['size']) && !empty($custom['system_disk']['size'])){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::SYSTEM_DISK, 0, $custom['system_disk']['size'], $duration['id'], $custom['system_disk']['disk_type'] ?? '');
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('system_disk_config_not_found')];
                }
                $preview[] = [
                    'name'  =>  lang_plugins('system_disk'),
                    'value' =>  $custom['system_disk']['size'].'GB',
                    'price' =>  $optionDurationPrice['price'] ?? 0,
                    'key'   => 'system_disk',
                ];

                $configData['system_disk'] = [
                    'value' => (int)$custom['system_disk']['size'],
                    'price' => $optionDurationPrice['price'] ?? 0,
                    'other_config' => $optionDurationPrice['option']['other_config'],
                ];

                // 保留项目价格
                if(!empty($onDemand) && in_array('system_disk', $onDemand['keep_time_billing_item'])){
                    $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                }
            }else{
                if(!$only_cal){
                    return ['status'=>400, 'msg'=>lang_plugins('please_select_system_disk_config')];
                }
            }

            // 获取数据盘周期价格
            if(isset($custom['data_disk']) && !empty($custom['data_disk'])){
                $dataDiskPrice = 0;
                $size = 0;

                $freeDisk = 0;
                foreach($custom['data_disk'] as $k=>$v){
                    // 支持免费盘
                    if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0 && !empty($v['is_free']) && $v['is_free'] == 1 && $freeDisk === 0){
                        $freeDisk = 1;
                    }

                    // 免费盘计算价格
                    if($freeDisk == 1){
                        $freeDisk++;
                        if($v['size'] > $config['free_disk_size']){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $config['free_disk_type'], $config['free_disk_size']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                            }
                            $size += $v['size'];
                            $dataDiskPrice = bcadd($dataDiskPrice, $optionDurationPrice['price'] ?? 0);

                            $configData['data_disk'][] = [
                                'value'         => (int)$v['size'],
                                'price'         => $optionDurationPrice['price'] ?? 0,
                                'other_config'  => $optionDurationPrice['option']['other_config'],
                                'is_free'       => 1,
                                'free_size'     => $config['free_disk_size'],
                            ];
                        }else if($v['size'] == $config['free_disk_size']){
                            $size += $v['size'];
                            $configData['data_disk'][] = [
                                'value'         => $config['free_disk_size'],
                                'price'         => 0,
                                'other_config'  => [
                                    'disk_type' => $config['free_disk_type'],
                                    'store_id'  => '',
                                ],
                                'is_free'       => 1,
                                'free_size'     => $config['free_disk_size'],
                            ];
                        }else{
                            return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                        }
                    }else{
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $v['disk_type'] ?? '');
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                        }
                        $size += $v['size'];
                        $dataDiskPrice = bcadd($dataDiskPrice, $optionDurationPrice['price'] ?? 0);

                        $configData['data_disk'][] = [
                            'id'            => $v['id'] ?? 0,
                            'value'         => (int)$v['size'],
                            'price'         => $optionDurationPrice['price'] ?? 0,
                            'other_config'  => $optionDurationPrice['option']['other_config'],
                        ];

                        // 保留项目价格
                        if(!empty($onDemand) && in_array('data_disk', $onDemand['keep_time_billing_item'])){
                            $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                        }
                    }
                }
                // 处理兼容
                if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0 && $freeDisk === 0){
                    $configData['data_disk'] = $configData['data_disk'] ?? [];

                    $size += $config['free_disk_size'];
                    array_unshift($configData['data_disk'], [
                        'value'         => $config['free_disk_size'],
                        'price'         => 0,
                        'other_config'  => [
                            'disk_type' => $config['free_disk_type'],
                            'store_id'  => '',
                        ],
                        'is_free'       => 1,
                        'free_size'     => $config['free_disk_size'],
                    ]);
                }

                $dataDiskLimit = $config['disk_limit_switch'] == 1 ? $config['disk_limit_num'] : 16;
                if(count($custom['data_disk']) > $dataDiskLimit){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_over_max_disk_num', ['{num}'=>$dataDiskLimit ])];
                }
                
                $preview[] = [
                    'name'  =>  count($configData['data_disk']) . lang_plugins('mf_cloud_count_of_data_disk'),
                    'value' =>  $size.'GB',
                    'price' =>  $dataDiskPrice,
                    'key'   => 'data_disk',
                ];
            }else{
                // 处理兼容
                if($config['free_disk_switch'] == 1 && $config['free_disk_size'] > 0){
                    $configData['data_disk'] = $configData['data_disk'] ?? [];

                    $size = $config['free_disk_size'];
                    array_unshift($configData['data_disk'], [
                        'value'         => $config['free_disk_size'],
                        'price'         => 0,
                        'other_config'  => [
                            'disk_type' => $config['free_disk_type'],
                            'store_id'  => '',
                        ],
                        'is_free'       => 1,
                        'free_size'     => $config['free_disk_size'],
                    ]);

                    $dataDiskLimit = $config['disk_limit_switch'] == 1 ? $config['disk_limit_num'] : 16;
                    if(1 > $dataDiskLimit){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_over_max_disk_num', ['{num}'=>$dataDiskLimit ])];
                    }
                    
                    $preview[] = [
                        'name'  =>  '1' . lang_plugins('mf_cloud_count_of_data_disk'),
                        'value' =>  $size.'GB',
                        'price' =>  0,
                    ];
                }
            }
            // 有线路才能选择防御和附加IP
            if(isset($custom['line_id']) && !empty($custom['line_id'])){
                $line = LineModel::find($custom['line_id']);
                if(!empty($line) && $line['hidden'] == 0 && $line['data_center_id'] == $dataCenter['id']){

                    $configData['line'] = $line;

                    if($line['bill_type'] == 'bw'){
                        // 获取带宽周期价格
                        if(isset($custom['bw']) && !empty($custom['bw'])){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $custom['bw'], $duration['id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('line_bw_not_found') ];
                            }
                            $preview[] = [
                                'name'  => lang_plugins('bw'),
                                'value' => $custom['bw'].'Mbps',
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'key'   => 'bw',
                            ];

                            $configData['bw'] = [
                                'value' => (int)$custom['bw'],
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'other_config' => $optionDurationPrice['option']['other_config'],
                            ];

                            // 保留项目价格
                            if(!empty($onDemand) && in_array('bw', $onDemand['keep_time_billing_item'])){
                                $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                            }
                        }else{
                            if(!$only_cal){
                                return ['status'=>400, 'msg'=>lang_plugins('please_input_bw')];
                            }
                        }
                    }else if($line['bill_type'] == 'flow'){
                        // 获取流量周期价格
                        if(isset($custom['flow']) && is_numeric($custom['flow']) && $custom['flow']>=0){
                            // 按需,流量只能是0
                            if(!empty($onDemand)){
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW_ON_DEMAND, $line['id'], 0, $duration['id'], $custom['bw'] ?? 0);
                                if(!$optionDurationPrice['match']){
                                    return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                                }
                                $onDemandFlowPrice = $optionDurationPrice['price'];

                                $preview[] = [
                                    'name'  => lang_plugins('flow'),
                                    'value' => lang_plugins('on_demand_billing'),
                                    'price' => $optionDurationPrice['price'],
                                    'key'   => 'flow',
                                ];
                            }else{
                                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $custom['flow'], $duration['id'], $custom['bw'] ?? 0);
                                if(!$optionDurationPrice['match']){
                                    return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                                }

                                $preview[] = [
                                    'name'  => lang_plugins('flow'),
                                    'value' => $custom['flow'] == 0 ? lang_plugins('mf_cloud_unlimited_flow') : $custom['flow'].'G',
                                    'price' => $optionDurationPrice['price'],
                                    'key'   => 'flow',
                                ];
                            }
                            // 同时追加带宽显示
                            $preview[] = [
                                'name'  => lang_plugins('bw'),
                                'value' => $optionDurationPrice['option']['other_config']['out_bw'] == 0 ? lang_plugins('mf_cloud_unlimited') : $optionDurationPrice['option']['other_config']['out_bw'].'Mbps',
                                'price' => 0,
                                'key'   => 'bw',
                            ];

                            $configData['flow'] = [
                                'value' => (int)$custom['flow'],
                                'price' => $optionDurationPrice['price'],
                                'other_config' => $optionDurationPrice['option']['other_config'],
                            ];
                        }else{
                            if(!$only_cal){
                                return ['status'=>400, 'msg'=>lang_plugins('please_input_line_flow')];
                            }
                        }
                    }
                    // 防护
                    $defencePreview = [];
                    if($line['defence_enable'] == 1){
                        if($line['sync_firewall_rule'] == 1){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $custom['peak_defence'] ?? '', $duration['id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                            }
                            $ConfigModel = new ConfigModel();
                            $rule = $ConfigModel->getFirewallDefenceRule([
                                'product_id'        => $productId,
                                'firewall_type'     => $optionDurationPrice['option']['firewall_type'],
                                'defence_rule_id'   => $optionDurationPrice['option']['defence_rule_id'],
                            ]);
                            if(empty($rule)){
                                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                            }
                            // 计算IP数量
                            $ipNum = 0;
                            if($line['ip_enable'] == 1 && isset($custom['ip_num']) && is_numeric($custom['ip_num']) && $custom['ip_num'] >= 0){
                                $ipNum = $custom['ip_num'];
                            }
                            // 是否有免费IP
                            $defaultOneIpv4 = $ConfigModel
                                            ->where('product_id', $productId)
                                            ->value('default_one_ipv4') ?? 1;
                            if($defaultOneIpv4){
                                $isNat = false;
                                if(is_numeric($config['nat_acl_limit']) && ($config['default_nat_acl'] == 1 || (isset($custom['nat_acl_limit_enable']) && $custom['nat_acl_limit_enable'] == 1))){
                                    $isNat = true;
                                }
                                if(is_numeric($config['nat_web_limit']) && ($config['default_nat_web'] == 1 || (isset($custom['nat_web_limit_enable']) && $custom['nat_web_limit_enable'] == 1))){
                                    $isNat = true;
                                }
                                if(!$isNat){
                                    $ipNum += 1;
                                }
                            }

//                            $defencePrice = bcmul($optionDurationPrice['price'] ?? 0, $ipNum, 2);
                            $defencePrice = bcmul($optionDurationPrice['price'] ?? 0, 1, 2);

//                            $preview[] = [
//                                'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
//                                'value' => $rule['defense_peak'],
//                                'price' => $defencePrice,
//                            ];

                            $defencePreview[] = [
                                'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
                                'value' => $rule['defense_peak'],
                                'price' => $defencePrice,
                            ];
                            //                $configData['defence'] = [
//                    'value' => $optionDurationPrice['option']['value'],
//                    'firewall_type' => $optionDurationPrice['option']['firewall_type'],
//                    'defence_rule_id' => $optionDurationPrice['option']['defence_rule_id'],
//                ];
                            $defenceConfigData['defence'] = [
                                'value' => $optionDurationPrice['option']['value'],
                                'firewall_type' => $optionDurationPrice['option']['firewall_type'],
                                'defence_rule_id' => $optionDurationPrice['option']['defence_rule_id'],
                            ];
                            $defenceConfigData['line'] = $line;
                            $defenceConfigData['duration'] = $duration;
                        }else if(isset($custom['peak_defence']) && is_numeric($custom['peak_defence']) && $custom['peak_defence'] >= 0){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $custom['peak_defence'], $duration['id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                            }
                            $preview[] = [
                                'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
                                'value' => $custom['peak_defence'] == 0 ? lang_plugins('mf_cloud_no_defence') : $custom['peak_defence'].'G',
                                'price' => $optionDurationPrice['price'] ?? 0,
                            ];

                            $configData['defence'] = [
                                'value' => (int)$custom['peak_defence'],
                                'firewall_type' => '',
                                'defence_rule_id' => 0,
                            ];

                            // 保留项目价格
                            if(!empty($onDemand) && in_array('peak_defence', $onDemand['keep_time_billing_item'])){
                                $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                            }
                        }
                    }
                    // 附加IP
                    if($line['ip_enable'] == 1){
                        if(isset($custom['ip_num']) && is_numeric($custom['ip_num']) && $custom['ip_num'] >= 0){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $custom['ip_num'], $duration['id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('line_add_ip_not_found') ];
                            }
                            $preview[] = [
                                'name'  => lang_plugins('mf_cloud_option_value_5'),
                                'value' => $custom['ip_num'] == 0 ? lang_plugins('mf_cloud_none') : $custom['ip_num'] . lang_plugins('mf_cloud_indivual'),
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'key'   => 'ip_num',
                            ];

                            $configData['ip'] = [
                                'value' => (int)$custom['ip_num'],
                                'price' => $optionDurationPrice['price'] ?? 0
                            ];

                            // 保留项目价格
                            if(!empty($onDemand) && in_array('ip_num', $onDemand['keep_time_billing_item'])){
                                $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                            }
                        }else{
                            if(!$only_cal){
                                $lineIp = $OptionModel->where('product_id', $productId)->where('rel_type', OptionModel::LINE_IP)->where('rel_id', $line['id'])->find();
                                if(!empty($lineIp)){
                                    return ['status'=>400, 'msg'=>lang_plugins('please_select_append_ip_num')];
                                }
                            }
                        }
                    }
                    // IPv6数量
                    if($line['ipv6_enable'] == 1){
                        if(isset($custom['ipv6_num']) && is_numeric($custom['ipv6_num']) && $custom['ipv6_num'] >= 0){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IPV6, $line['id'], $custom['ipv6_num'], $duration['id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ipv6_num_not_found') ];
                            }
                            $preview[] = [
                                'name'  => lang_plugins('mf_cloud_ipv6_num'),
                                'value' => $custom['ipv6_num'] == 0 ? lang_plugins('mf_cloud_none') : $custom['ipv6_num'] . lang_plugins('mf_cloud_indivual'),
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'key'   => 'ipv6_num',
                            ];
                            // 按照原来的方式保存
                            $configData['ipv6_num'] = $custom['ipv6_num'];

                            // 保留项目价格
                            if(!empty($onDemand) && in_array('ipv6_num', $onDemand['keep_time_billing_item'])){
                                $keepTimePrice = bcadd($keepTimePrice, $optionDurationPrice['price'], 4);
                            }
                        }else{
                            if(!$only_cal && isset($custom['network_type']) && $custom['network_type'] == 'normal'){
                                $lineIpv6 = $OptionModel->where('product_id', $productId)->where('rel_type', OptionModel::LINE_IPV6)->where('rel_id', $line['id'])->find();
                                if(!empty($lineIpv6)){
                                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_select_ipv6_num')];
                                }
                            }
                        }
                    }
                }else{
                    return ['status'=>400, 'msg'=>lang_plugins('line_not_found') ];
                }
            }else{
                if(!$only_cal){
                    return ['status'=>400, 'msg'=>lang_plugins('please_select_line')];
                }
            }

            // GPU
            if(!empty($dataCenter['gpu_name'])){
                if(isset($custom['gpu_num']) && !empty($custom['gpu_num'])){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_CENTER_GPU, $dataCenter['id'], $custom['gpu_num'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_gpu_num_not_found') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('mf_cloud_option_value_8'),
                        'value' => $custom['gpu_num'] . '*' . $dataCenter['gpu_name'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                        'key'   => 'gpu_num',
                    ];
                    
                    $configData['gpu_num'] = $custom['gpu_num'];
                    $configData['gpu_name'] = $dataCenter['gpu_name'];
                }else{
                    if(!$only_cal){
                        $gpu = $OptionModel->where('product_id', $productId)->where('rel_type', OptionModel::DATA_CENTER_GPU)->where('rel_id', $dataCenter['id'])->find();
                        if(!empty($gpu)){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_gpu_param_error')];
                        }
                    }
                }
            }
        }

        if (!empty($defencePrice) && !empty($line) && $line['defence_enable'] == 1 && $line['sync_firewall_rule'] == 1){
            $configData['defence'] = $defenceConfigData??[];
            $price = 0;
            $description = '';
            $basePrice = $price;
            $renewPrice = 0;
            foreach($defencePreview as $k=>$v){
                // 价格系数
                $v['price'] = bcmul($v['price'], $duration['price_factor']);

                $price = bcadd($price, $v['price']);

                $basePrice = bcadd($basePrice,$v['price']);
                $renewPrice = bcadd($renewPrice, $v['price']);

                $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price']."\r\n";

                $defencePreview[$k]['price'] = amount_format($v['price']);
            }
            $subHost = [];
            if (!empty($ipNum)){
                for ($i=1; $i<=$ipNum; $i++){
                    $subHost[] = [
                        'price'             => amount_format($price),
                        'renew_price'       => amount_format($renewPrice),
                        'billing_cycle'     => $duration['name'],
                        'duration'          => $durationTime,
                        'description'       => $description,
                        'preview'           => $defencePreview,
                        'base_price'        => $basePrice,
                        'config_options'    => ['peak_defence'=>$custom['peak_defence']],
                    ];
                }
            }
        }

        // 获取镜像周期价格
        $imagePrice = 0;
        if(isset($custom['image_id']) && !empty($custom['image_id']) ){
            $image = ImageModel::where('id', $custom['image_id'])->where('enable', 1)->find();
            if(empty($image) || $image['product_id'] != $productId){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_os_not_found')];
            }
            if(isset($custom['ssh_key_id']) && !empty($custom['ssh_key_id'])){
                if($config['support_ssh_key'] == 0){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_ssh_key')];
                }
                if(stripos($image['name'], 'win') !== false){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_windows_cannot_use_ssh_key')];
                }
            }
            // 验证镜像
            if($image['charge'] == 1 && !empty($image['price'])){
                $preview[] = [
                    'name'  =>  lang_plugins('mf_cloud_os'),
                    'value' =>  $image['name'],
                    'price' =>  $image['price'],
                    'key'   => 'image',
                ];

                $imagePrice = $image['price'];
            }else{
                $preview[] = [
                    'name'  =>  lang_plugins('mf_cloud_os'),
                    'value' =>  $image['name'],
                    'price' =>  0,
                    'key'   => 'image',
                ];
            }
            $configData['image'] = $image;
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_os')];
            }
        }
        // 备份快照,先计算按需的,用于转换使用
        if(!empty($onDemand)){
            $BackupConfigModel = new BackupConfigModel();
            if($config['backup_enable'] == 1){
                if(isset($custom['backup_num']) && !empty($custom['backup_num'])){
                    $backupConfig = $BackupConfigModel->matchBackupConfig([
                        'product_id' => $productId,
                        'type'       => 'backup',
                        'num'        => $custom['backup_num'],
                    ]);
                    if(!empty($backupConfig)){
                        $preview[] = [
                            'name'  => lang_plugins('backup_function'),
                            'value' => $backupConfig['num'] . lang_plugins('number'),
                            'price' => $backupConfig['on_demand_price'],
                            'key'   => 'backup_num',
                        ];
        
                        $configData['backup'] = [
                            'num'   => $backupConfig['num'],
                        ];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('backup_num_select_error')];
                        }
                    }
                }
            }
            if($config['snap_enable'] == 1){
                if(isset($custom['snap_num']) && !empty($custom['snap_num'])){
                    $backupConfig = $BackupConfigModel->matchBackupConfig([
                        'product_id' => $productId,
                        'type'       => 'snap',
                        'num'        => $custom['snap_num'],
                    ]);
                    if(!empty($backupConfig)){
                        $preview[] = [
                            'name'  => lang_plugins('snap_function'),
                            'value' => $backupConfig['num'] . lang_plugins('number'),
                            'price' => $backupConfig['on_demand_price'],
                            'key'   => 'snap_num',
                        ];
        
                        $configData['snap'] = [
                            'num'   => $backupConfig['num'],
                        ];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('snap_num_select_error')];
                        }
                    }
                }
            }
        }
        
        // 按需计费,计算初装费
        if(!empty($onDemand)){
            $preview[] = [
                'name'  =>  lang_plugins('初装费'),
                'value' =>  '',
                'price' =>  $onDemand['initial_fee'],
                'key'   => 'initial_fee',
            ];
        }

        // 前台勾选
        if($config['ip_mac_bind'] && isset($custom['ip_mac_bind_enable']) && $custom['ip_mac_bind_enable'] == 1){
            $configData['ip_mac_bind'] = 1;
        }
        // if(is_numeric($config['ipv6_num']) && isset($custom['ipv6_num_enable']) && $custom['ipv6_num_enable'] == 1){
        //     $configData['ipv6_num'] = $config['ipv6_num'];
        // }
        if($custom['network_type'] == 'vpc' || $config['type'] == 'lightHost'){
            if(is_numeric($config['nat_acl_limit']) && ($config['default_nat_acl'] == 1 || (isset($custom['nat_acl_limit_enable']) && $custom['nat_acl_limit_enable'] == 1))){
                $configData['nat_acl_limit'] = $config['nat_acl_limit'];
            }
            if(is_numeric($config['nat_web_limit']) && ($config['default_nat_web'] == 1 || (isset($custom['nat_web_limit_enable']) && $custom['nat_web_limit_enable'] == 1))){
                $configData['nat_web_limit'] = $config['nat_web_limit'];
            }
        }
        if(isset($custom['resource_package_id']) && !empty($custom['resource_package_id'])){
            $configData['resource_package'] = ResourcePackageModel::where('id', $custom['resource_package_id'])->find();
        }
        if(isset($configData['ipv6_num']) && $configData['ipv6_num'] > 0 && !$only_cal){
            if(isset($configData['nat_web_limit']) || isset($configData['nat_acl_limit'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_nat_cannot_use_with_ipv6')];
            }
            if($custom['network_type'] == 'vpc'){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vpc_network_cannot_use_ipv6')];
            }
        }
        if(!$only_cal){
            if($custom['network_type'] == 'vpc' && $config['type'] != 'host'){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_product_not_support_vpc_network') ];
            }
            // 转发建站判断
            if((isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit'])) && $config['type'] == 'host' && $custom['network_type'] != 'vpc'){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_normal_network_not_support_nat') ];
            }
        }

        // 新增指定端口
        if(!empty($image)){
            if($config['rand_ssh_port'] == 0){
                $imageGroup = ImageGroupModel::find($image['image_group_id']);
                if(!empty($imageGroup) && $imageGroup['icon'] == 'Windows'){
                    $configData['port'] = 3389;
                }else{
                    $configData['port'] = 22;
                }
            }else if($config['rand_ssh_port'] == 1){
                // if(!isset($custom['port']) || empty($custom['port'])){
                //     return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ssh_port_require')];
                // }
                $configData['port'] = $custom['port'] ?? 0;
            }else if($config['rand_ssh_port'] == 2){
                // 指定端口,获取镜像分组
                $imageGroup = ImageGroupModel::find($image['image_group_id']);
                if(!empty($imageGroup) && $imageGroup['icon'] == 'Windows'){
                    $configData['port'] = $config['rand_ssh_port_windows'] ?: 3389;
                }else{
                    $configData['port'] = $config['rand_ssh_port_linux'] ?: 22;
                }
            }else if($config['rand_ssh_port'] == 3){
                if(!empty($custom['rand_port'])){
                    $configData['port'] = mt_rand(20000, 65535);
                }else{
                    $configData['port'] = $custom['port'] ?? mt_rand(20000, 65535);
                }
            }
        }else{
            if(!empty($custom['rand_port'])){
                $configData['port'] = mt_rand(20000, 65535);
            }else{
                $configData['port'] = $custom['port'] ?? mt_rand(20000, 65535);
            }
        }
        $configData['rand_port'] = !empty($custom['rand_port']) ? 1 : 0;

        $isDownstream = (input('get.is_downstream', 0) == 1) || (input('post.is_downstream', 0) == 1);

        $clientLevel = $this->getClientLevel([
            'product_id'    => $productId,
            'client_id'     => !empty($param['client_id'])?$param['client_id']:get_client_id(),
        ]);
        if (!$priceAgent){
            $clientLevel = [];
        }

        // 获取配置信息，用于判断等级优惠开关
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];

        $orderItem = [];
        $price = 0;
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0;
        $basePrice = $price;
        $renewPrice = 0;
        $baseOnDemandFlowPrice = $onDemandFlowPrice;
        $discountRenewPrice = 0; // 续费用户等级可优惠金额

        // 按需
        if(!empty($onDemand)){
            foreach($preview as $k=>$v){
                // 保存原始价格用于续费优惠计算
                $originalPrice = $v['price'];
                
                if(!empty($v['key'])){
                    // 初装费部分
                    if(in_array($v['key'], ['initial_fee','image'])){
                        $price = bcadd($price, $v['price'], 2);
                    }else if(in_array($v['key'], ['flow'])){
                        // 不计算续费价格
                        
                    }else{
                        $renewPrice = bcadd($renewPrice, $v['price'], 4);
                    }
                }else{
                    $renewPrice = bcadd($renewPrice, $v['price'], 4);
                }
                // 根据配置决定是否将此组件价格享受等级优惠(订购)
                $canDiscount = true;
                if(isset($v['key'])){
                    if($v['key'] == 'cpu' && $config['level_discount_cpu_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'memory' && $config['level_discount_memory_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'bw' && $config['level_discount_bw_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'system_disk' && $config['level_discount_system_disk_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'data_disk' && $config['level_discount_data_disk_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'ip_num' && $config['level_discount_ipv4_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'ipv6_num' && $config['level_discount_ipv6_order'] == 0){
                        $canDiscount = false;
                    // }else if($v['key'] == 'gpu_num' && $config['level_discount_gpu_order'] == 0){
                    //     $canDiscount = false;
                    }else if($v['key'] == 'flow'){
                        // 按需流量单独计算
                        $canDiscount = false;
                    }
                }
                
                // 根据配置决定续费时是否将此组件价格享受等级优惠
                $canRenewDiscount = true;
                if(isset($v['key'])){
                    // 初装费、镜像、流量不参与续费优惠
                    if(in_array($v['key'], ['initial_fee','image','flow'])){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'cpu' && $config['level_discount_cpu_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'memory' && $config['level_discount_memory_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'bw' && $config['level_discount_bw_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'system_disk' && $config['level_discount_system_disk_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'data_disk' && $config['level_discount_data_disk_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'ip_num' && $config['level_discount_ipv4_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'ipv6_num' && $config['level_discount_ipv6_renew'] == 0){
                        $canRenewDiscount = false;
                    }
                }
                
                if($canDiscount){
                    // 此组件享受等级优惠，计入discountPrice，只计算镜像/初装费
                    if(!empty($v['key']) && in_array($v['key'], ['initial_fee','image'])){
                        $discountPrice = bcadd($discountPrice, $v['price']);
                    }

                    if($isDownstream && !empty($clientLevel)){
                        $clientLevelDiscount = bcdiv($v['price']*$clientLevel['discount_percent'], 100, 4);
                        if($clientLevelDiscount > 0){
                            $v['price'] = bcsub($v['price'], $clientLevelDiscount, 4);
                        }
                    }
                }
                
                // 续费时享受等级优惠的金额(使用原始价格)
                if($canRenewDiscount){
                    if(!empty($v['key'])){
                        if(!in_array($v['key'], ['initial_fee','image','flow'])){
                            $discountRenewPrice = bcadd($discountRenewPrice, $originalPrice, 4);
                        }
                    }else{
                        $discountRenewPrice = bcadd($discountRenewPrice, $originalPrice, 4);
                    }
                }
                
                // 初装费,镜像保留2位
                if(!empty($v['key']) && in_array($v['key'], ['initial_fee','image'])){
                    $preview[$k]['price'] = amount_format($v['price'], 2);
                }else{
                    $preview[$k]['price'] = amount_format($v['price'], 4);
                }
            }
            $basePrice = $price;
            $baseRenewPrice = $renewPrice;
        }else{
            foreach($preview as $k=>$v){
                // 价格系数
                $v['price'] = bcmul($v['price'], $duration['price_factor']);
                // 保存原始价格用于续费优惠计算
                $originalPrice = $v['price'];
    
                $price = bcadd($price, $v['price']);
                // 镜像不算续费
                if(isset($v['key']) && $v['key'] == 'image'){
    
                }else{
                    $basePrice = bcadd($basePrice,$v['price']);
                    $renewPrice = bcadd($renewPrice, $v['price']);
                }
                
                // 根据配置决定是否将此组件价格享受等级优惠(订购)
                $canDiscount = true;
                if(isset($v['key'])){
                    if($v['key'] == 'cpu' && $config['level_discount_cpu_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'memory' && $config['level_discount_memory_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'bw' && $config['level_discount_bw_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'system_disk' && $config['level_discount_system_disk_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'data_disk' && $config['level_discount_data_disk_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'ip_num' && $config['level_discount_ipv4_order'] == 0){
                        $canDiscount = false;
                    }else if($v['key'] == 'ipv6_num' && $config['level_discount_ipv6_order'] == 0){
                        $canDiscount = false;
                    // }else if($v['key'] == 'gpu_num' && $config['level_discount_gpu_order'] == 0){
                    //     $canDiscount = false;
                    }
                }
                
                // 根据配置决定续费时是否将此组件价格享受等级优惠
                $canRenewDiscount = true;
                if(isset($v['key'])){
                    // 镜像不参与续费优惠
                    if($v['key'] == 'image'){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'cpu' && $config['level_discount_cpu_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'memory' && $config['level_discount_memory_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'bw' && $config['level_discount_bw_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'system_disk' && $config['level_discount_system_disk_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'data_disk' && $config['level_discount_data_disk_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'ip_num' && $config['level_discount_ipv4_renew'] == 0){
                        $canRenewDiscount = false;
                    }else if($v['key'] == 'ipv6_num' && $config['level_discount_ipv6_renew'] == 0){
                        $canRenewDiscount = false;
                    }
                }
                
                if($canDiscount){
                    // 此组件享受等级优惠，计入discountPrice
                    $discountPrice = bcadd($discountPrice, $v['price']);

                    if($isDownstream && !empty($clientLevel)){
                        $clientLevelDiscount = bcdiv($v['price']*$clientLevel['discount_percent'], 100, 2);
                        if($clientLevelDiscount > 0){
                            $v['price'] = bcsub($v['price'], $clientLevelDiscount, 2);
                        }
                    }
                }
                
                // 续费时享受等级优惠的金额(使用原始价格)
                if($canRenewDiscount){
                    $discountRenewPrice = bcadd($discountRenewPrice, $originalPrice);
                }
                
                $preview[$k]['price'] = amount_format($v['price']);
            }
            $baseRenewPrice = $renewPrice;
        }
        $description = $this->getPreviewDescription($preview, !empty($onDemand));

        if ($only_cal && isset($subHost)){
            foreach ($subHost as $k=>$v){
                $discountPrice = bcadd($discountPrice, $v['price']);
                $price = bcadd($price, $v['price']);
                $basePrice = bcadd($basePrice,$v['base_price']);
                $renewPrice = bcadd($renewPrice, $v['renew_price']);
            }
        }

        // 是否活动互斥用户等级互斥，结算时不要传
        $eventPromotion = $custom['event_promotion'] ?? 0;
        // 是否和用户等级互斥,订购金额和续费需要分开判断
        // 需要注意：按需计费，活动的续费折扣不能对实例生效，所以续费折扣应该一直使用用户等级
        $eventPromotionExcludeClientLevel = false;
        $eventPromotionExcludeClientLevelRenew = false;
        if($only_cal && !empty($eventPromotion)){
            $hookEventPromotionResultsOrgins = hook("event_promotion_by_amount",[
                'event_promotion' => $eventPromotion,
                'product_id' => $productId,
                'qty' => 1,
                'amount' => $price,
                'billing_cycle_time' => $durationTime,
            ]);
            foreach ($hookEventPromotionResultsOrgins as $hookEventPromotionResultsOrgin){
                if ($hookEventPromotionResultsOrgin['status']==200){
                    // hh 20250130 检查活动是否开启"不与用户等级同享"
                    $excludeWithClientLevel = $hookEventPromotionResultsOrgin['data']['exclude_with_client_level'] ?? 0;
                    if($excludeWithClientLevel == 1){
                        $eventPromotionExcludeClientLevel = true;
                        if(empty($onDemand) && !empty($hookEventPromotionResultsOrgin['data']['renew_percent'])){
                            $eventPromotionExcludeClientLevelRenew = true;
                        }
                    }
                }
            }
        }

        // $discountPrice = $price;
        if(!empty($clientLevel)){
            if (!empty($subHost)){
                foreach ($subHost as &$item){
                    $item['base_renew_price'] = $item['renew_price'];

                    if(!$eventPromotionExcludeClientLevel){
                        $item['discount'] = bcdiv($item['price']*$clientLevel['discount_percent'], 100, 2);
                        $item['price'] = bcsub($item['price'], $item['discount'], 2);
                    }
                    if(!$eventPromotionExcludeClientLevelRenew){
                        $item['renew_price'] = bcsub($item['renew_price'],bcdiv($item['renew_price']*$clientLevel['discount_percent'], 100, 2),2);
                    }
                }
            }

            if(!$eventPromotionExcludeClientLevel){
                $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
                $price = bcsub($price, $discount, 2);

                $orderItem[] = [
                    'type'          => 'addon_idcsmart_client_level',
                    'rel_id'        => $clientLevel['id'],
                    'amount'        => -$discount,
                    'description'   => lang_plugins('mf_cloud_client_level', [
                        '{name}'    => $clientLevel['name'],
                        '{value}'   => $clientLevel['discount_percent'],
                    ]),
                ];
            }

            // 使用续费可优惠金额计算续费折扣
            if(!$eventPromotionExcludeClientLevelRenew){
                $renewDiscount = bcdiv($discountRenewPrice*$clientLevel['discount_percent'], 100, 4);
                $keepTimeDiscount = bcdiv($keepTimePrice*$clientLevel['discount_percent'], 100, 4);
                $onDemandFlowDiscount = bcdiv($onDemandFlowPrice*$clientLevel['discount_percent'], 100, 4);

                $renewPrice = bcsub($renewPrice, $renewDiscount, 4);
                $keepTimePrice = bcsub($keepTimePrice, $keepTimeDiscount, 4);
                $onDemandFlowPrice = bcsub($onDemandFlowPrice, $onDemandFlowDiscount, 4);
            }

            // 如果是下游,base_price,discount_order_price,discount_renew_price也需要进行用户等级折扣后返回
            if($isDownstream){
                $basePrice = bcsub($basePrice, bcdiv($basePrice*$clientLevel['discount_percent'], 100, 2), 2);
                $discountPrice = bcsub($discountPrice, bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2), 2);
                $discountRenewPrice = bcsub($discountRenewPrice, bcdiv($discountRenewPrice*$clientLevel['discount_percent'], 100, 2), 2);
            }
        }

        // 缓存配置用于结算
        DurationModel::$configData[$position] = $configData;

        if(!empty($onDemand)){
            $renewPrice = amount_format($renewPrice, 4);
        }else{
            $renewPrice = amount_format($renewPrice, 2);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price'             => amount_format($price),
                'renew_price'       => $renewPrice,
                'base_renew_price'  => $baseRenewPrice ?? $renewPrice,   // 目前在转按需时会用到
                'billing_cycle'     => $duration['name'],
                'duration'          => $durationTime,
                'description'       => $description,
                'preview'           => $preview,
                'base_price'        => $basePrice,
                'billing_cycle_name'=> $durationName,
                'discount'          => amount_format($discount),
                'order_item'        => $orderItem,
                'ontrial'           => $ontrial,
                'sub_host'          => $subHost ?? [],
                'host_billing_cycle' => $billingCycle,
                'keep_time_price'   => $keepTimePrice,
                'on_demand_flow_price' => $onDemandFlowPrice,
                'renew_price_client_level_discount' => $renewDiscount ?? '0.0000',
                'on_demand_flow_price_client_level_discount' => $onDemandFlowDiscount ?? '0.0000',
                'base_on_demand_flow_price' => amount_format($baseOnDemandFlowPrice, 4),
                'discount_renew_price' => $discountRenewPrice,
                'discount_order_price' => $discountPrice,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2024-02-18
     * @title 获取用户等级
     * @desc  获取用户等级
     * @author hh
     * @version v1
     * @param   int param.client_id - 用户ID require
     * @param   int param.product_id - 商品ID require
     * @return  int id - 用户等级ID
     * @return  string name - 用户等级名称
     * @return  int product_id - 商品ID
     * @return  float discount_percent - 等级折扣
     */
    public function getClientLevel($param)
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
        $discount = [];
        if(!empty($plugin) && class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')){
            try{
                if(class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelProductGroupModel')){
                    $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                    $discount = $IdcsmartClientLevelModel->clientDiscount(['client_id' => $param['client_id'], 'product_id' => $param['product_id']]);
                }else{
                    $discount = IdcsmartClientLevelClientLinkModel::alias('aiclcl')
                        ->field('aicl.id,aicl.name,aiclpl.product_id,aiclpl.discount_percent')
                        ->leftJoin('addon_idcsmart_client_level aicl', 'aiclcl.addon_idcsmart_client_level_id=aicl.id')
                        ->leftJoin('addon_idcsmart_client_level_product_link aiclpl', 'aiclpl.addon_idcsmart_client_level_id=aicl.id')
                        ->where('aiclcl.client_id', $param['client_id'])
                        ->where('aiclpl.product_id', $param['product_id'])
                        ->where('aicl.discount_status', 1)
                        ->find();
                }
            }catch(\Exception $e){
                
            }
        }
        return $discount;
    }

    /**
     * 时间 2024-02-18
     * @title 计算用户等级折扣金额
     * @desc  计算用户等级折扣金额
     * @author hh
     * @version v1
     * @param   int param.client_id - 用户ID require
     * @param   int param.product_id - 商品ID require
     * @param   float param.price - 金额 require
     * @return  float|string
     */
    public function downstreamSubClientLevelPrice($param)
    {
        if(!isset($this->clientLevel[ $param['client_id'] ][ $param['product_id'] ])){
            $clientLevel = $this->getClientLevel([
                'product_id'    => $param['product_id'],
                'client_id'     => $param['client_id'],
            ]);
            $this->clientLevel[ $param['client_id'] ][ $param['product_id'] ] = $clientLevel;
        }else{
            $clientLevel = $this->clientLevel[ $param['client_id'] ][ $param['product_id'] ];
        }
        if(/*$param['price'] > 0 && */!empty($clientLevel)){
            $clientLevelDiscount = bcdiv($param['price'] * $clientLevel['discount_percent'], 100, 2);
            //if($clientLevelDiscount > 0){
                $param['price'] = bcsub($param['price'], $clientLevelDiscount, 2);
            //}
        }
        return $param['price'];
    }

    /**
     * @时间 2025-01-07
     * @title 获取不支持申请停用周期
     * @desc  获取不支持申请停用周期
     * @author hh
     * @version v1
     * @return  array
     */
    public function getNotSupportApplyForSuspendDuration($productId)
    {
        $data = $this
                ->field('id,name')
                ->where('product_id', $productId)
                ->where('support_apply_for_suspend', 0)
                ->order('id', 'desc')
                ->select()
                ->toArray();

        return $data;
    }

    /**
     * @时间 2025-03-21
     * @title 获取商品最短单位周期
     * @desc  获取商品最短单位周期
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @return  DurationModel|null
     */
    public function firstDuration($productId)
    {
        $where = [
            ['product_id', '=', $productId],
        ];
        $duration = $this
                ->where($where)
                ->orderRaw('field(unit, "hour","day","month")')
                ->order('num', 'asc')
                ->find();
        return $duration;
    }

    /**
     * @时间 2025-03-21
     * @title 获取商品按需
     * @desc  获取商品按需,空代表不支持按需
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     * @return  \app\common\model\ProductOnDemandModel|null
     */
    public function onDemand($productId)
    {
        $onDemand = NULL;
        // 是否启用了按需
        $ProductModel = new ProductModel();
        $productOnDemand = $ProductModel->productOnDemand($productId);
        if($productOnDemand['on_demand']){
            // 获取按需
            $onDemand = $productOnDemand['product_on_demand'];
        }
        return $onDemand;
    }

    /**
     * @时间 2025-03-21
     * @title 获取计算价格订单描述
     * @desc  获取计算价格订单描述
     * @author hh
     * @version v1
     * @param   array preview - 计算价格的preview require
     * @return  string
     */
    protected function getPreviewDescription($preview, $isOnDemand = false): string
    {
        $description = '';
        foreach($preview as $v){
            if(!empty($v['key'])){
                // 初装费
                if($v['key'] == 'initial_fee'){
                    $v['price'] = amount_format($v['price'], 2);
                    $description .= $v['name'].': '. lang_plugins('price') .':'.$v['price']."\r\n";
                }else if($v['key'] == 'image'){
                    // 镜像
                    $v['price'] = amount_format($v['price'], 2);
                    $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price']."\r\n";
                }else if($v['key'] == 'flow'){
                    // 流量
                    if($isOnDemand){
                        $v['price'] = amount_format($v['price'], 4);
                        $description .= $v['name'].': '. lang_plugins('price') .':'.$v['price'].'/GB'."\r\n";
                    }else{
                        $v['price'] = amount_format($v['price'], 2);
                        $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price']."\r\n";
                    }
                }else{
                    if($isOnDemand){
                        $v['price'] = amount_format($v['price'], 4);
                        $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price'].'/小时'."\r\n";
                    }else{
                        $v['price'] = amount_format($v['price'], 2);
                        $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price']."\r\n";
                    }
                }
            }else{
                if($isOnDemand){
                    $v['price'] = amount_format($v['price'], 4);
                    $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price'].'/小时'."\r\n";
                }else{
                    $v['price'] = amount_format($v['price'], 2);
                    $description .= $v['name'].': '.$v['value'].','. lang_plugins('price') .':'.$v['price']."\r\n";
                }
            }
        }
        return $description;
    }


}
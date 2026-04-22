<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;
use app\common\model\HostAdditionModel;

/**
 * @title 数据中心模型
 * @use server\mf_cloud\model\DataCenterModel
 */
class DataCenterModel extends Model
{
	protected $name = 'module_mf_cloud_data_center';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'country_id'        => 'int',
        'city'              => 'string',
        'area'              => 'string',
        'cloud_config'      => 'string',
        'cloud_config_id'   => 'int',
        'order'             => 'int',
        'gpu_name'          => 'string',
        'create_time'       => 'int',
        'upstream_id'       => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 数据中心列表
     * @desc 数据中心列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  int data.list[].country_id - 国家ID
     * @return  string data.list[].cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return  int data.list[].cloud_config_id - 魔方云配置ID
     * @return  int data.list[].order - 排序
     * @return  string data.list[].gpu_name - 显卡名称
     * @return  string data.list[].country_name - 国家
     * @return  int data.list[].line[].id - 线路ID
     * @return  int data.list[].line[].data_center_id - 数据中心ID
     * @return  string data.list[].line[].name - 线路名称
     * @return  string data.list[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int data.list[].line[].hidden - 是否隐藏(0=否,1=是)
     * @return  string data.list[].line[].price - 价格
     * @return  string data.list[].line[].duration - 周期
     * @return  int data.count - 总条数
     */
    public function dataCenterList($param)
    {
        bcscale(2);
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        
        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['dc.product_id', '=', $param['product_id']];
        }

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $dataCenter = $this
                ->alias('dc')
                ->field('dc.id,dc.city,dc.area,dc.country_id,dc.cloud_config,dc.cloud_config_id,dc.order,dc.gpu_name,c.'.$countryName.' country_name')
                ->where($where)
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->page($param['page'], $param['limit'])
                ->order('dc.order,dc.id', 'asc')
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('dc')
                ->where($where)
                ->group('dc.id')
                ->count();

        if(!empty($dataCenter)){

            $line = LineModel::alias('l')
                    ->field('l.id,l.data_center_id,l.name,l.bill_type,l.hidden,l.upstream_hidden')
                    ->whereIn('data_center_id', array_column($dataCenter, 'id'))
                    ->order('l.order,l.id', 'asc')
                    ->select()
                    ->toArray();

            $lineId = array_column($line, 'id');

            if(!empty($lineId)){
                $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $param['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();

                // 直接显示最小周期
                if(!empty($firstDuration)){

                    $price = OptionModel::alias('o')
                            ->field('p.price,o.type,o.value,o.min_value,o.rel_id,o.rel_type')
                            ->leftJoin('module_mf_cloud_price p', 'p.product_id='.$param['product_id'].' AND p.rel_type='.PriceModel::REL_TYPE_OPTION.' AND p.rel_id=o.id AND p.duration_id='.$firstDuration['id'])
                            ->whereIn('o.rel_type', [OptionModel::LINE_BW, OptionModel::LINE_FLOW])
                            ->whereIn('o.rel_id', $lineId)
                            // ->where('p.duration_id', $firstDuration['id'])
                            ->order('o.value,o.min_value', 'asc')
                            ->select()
                            ->toArray();
                    // $price = PriceModel::alias('p')
                    //     ->field('p.price,o.type,o.value,o.min_value,o.rel_id,o.rel_type')
                    //     ->leftJoin('module_mf_cloud_option o', 'p.product_id='.$param['product_id'].' AND p.rel_type='.PriceModel::REL_TYPE_OPTION.' AND p.rel_id=o.id')
                    //     ->whereIn('o.rel_type', [OptionModel::LINE_BW, OptionModel::LINE_FLOW])
                    //     ->whereIn('o.rel_id', $lineId)
                    //     ->where('p.duration_id', $firstDuration['id'])
                    //     ->order('o.value,o.min_value', 'asc')
                    //     ->select()
                    //     ->toArray();

                    $priceArr = [];
                    foreach($price as $k=>$v){
                        // if(isset($priceArr[ $v['rel_id'] ])){
                        //     continue;
                        // }
                        if($v['type'] == 'radio'){
                            $tempPrice = $v['price'] ?? 0;
                        }else{
                            $tempPrice = bcmul($v['price'] ?? 0, $v['min_value']);
                        }
                        if(!isset($priceArr[ $v['rel_id'] ])){
                            $priceArr[ $v['rel_id'] ] = [
                                'price' => $tempPrice,
                                'name'  => $firstDuration['name'],
                            ];
                        }else{
                            $priceArr[ $v['rel_id'] ]['price'] = min($priceArr[ $v['rel_id'] ]['price'], $tempPrice);
                        }
                    }

                    $lineArr = [];
                    foreach($line as $k=>$v){
                        $v['price'] = isset($priceArr[$v['id']]['price']) ? amount_format($priceArr[$v['id']]['price']) : '0.00';
                        $v['duration'] = $priceArr[$v['id']]['name'] ?? '';
                        
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }else{
                    $lineArr = [];
                    foreach($line as $k=>$v){
                        $v['price'] = '0.00';
                        $v['duration'] = '';
                        
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }
            }

            foreach($dataCenter as $k=>$v){
                $dataCenter[$k]['line'] = $lineArr[ $v['id'] ] ?? [];
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => $dataCenter,
                'count' => $count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 创建数据中心
     * @desc 创建数据中心
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.country_id - 国家ID require
     * @param   string param.city - 城市 require
     * @param   string param.area - 区域 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.cloud_config_id - 魔方云配置ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
     */
    public function createDataCenter($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }
        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $param['create_time'] = time();
        $param['order'] = $param['order'] ?? 0;

        $this->startTrans();
        try{
            $dataCenter = $this->create($param, ['country_id','city','area','cloud_config','cloud_config_id','product_id','create_time','order']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_create_data_center_success', [
            '{product}' => 'product#'.$ProductModel->id.'#'.$ProductModel->name.'#',
            '{name}'    => $dataCenter->getDataCenterName()
        ]);
        active_log($description, 'product', $ProductModel['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$dataCenter->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 修改数据中心
     * @desc 修改数据中心
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID require
     * @param   int param.country_id - 国家ID require
     * @param   string param.city - 城市 require
     * @param   string param.area - 区域 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.cloud_config_id - 魔方云配置ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateDataCenter($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }

        $OldCountryModel = CountryModel::find($dataCenter['country_id']);

        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $dataCenter['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->where('id', '<>', $dataCenter['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }
        if(!is_numeric($param['order'])){
            unset($param['order']);
        }

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['country_id','city','area','cloud_config','cloud_config_id','order']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $type = [
            'node'=>lang_plugins('node_id'),
            'area'=>lang_plugins('area_id'),
            'node_group'=>lang_plugins('node_group_id'),
        ];

        $desc = [
            'country'=>lang_plugins('country'),
            'city'=>lang_plugins('city'),
            'area'=>lang_plugins('area'),
            'cloud_config'=>lang_plugins('cloud_config'),
        ];

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $old = [
            'country'=>$OldCountryModel[ $countryName ],
            'city'=>$dataCenter['city'],
            'area'=>$dataCenter['area'],
            'cloud_config'=>$type[ $dataCenter['cloud_config'] ].$dataCenter['cloud_config_id'],
        ];
        $new = [
            'country'=>$CountryModel[ $countryName ],
            'city'=>$param['city'],
            'area'=>$param['area'],
            'cloud_config'=>$type[ $param['cloud_config'] ].$param['cloud_config_id'],
        ];

        $description = ToolLogic::createEditLog($old, $new, $desc);
        if(!empty($description)){
            $productName = ProductModel::where('id', $dataCenter['product_id'])->value('name');

            $description = lang_plugins('log_modify_data_center_success', [
                '{product}' => 'product#'.$dataCenter['product_id'].'#'.$productName.'#',
                '{name}'    => $old['country'].$old['city'].$old['area'],
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        // 变更数据中心后,同步修改附加信息表
        if($old['country'] != $new['country'] || $old['city'] != $new['city'] || $old['area'] != $new['area']){
            $hostId = HostLinkModel::where('data_center_id', $dataCenter->id)->column('host_id');
            if(!empty($hostId)){
                HostAdditionModel::whereIn('host_id', $hostId)->update([
                    'country_id' => $param['country_id'],
                    'city'       => $param['city'],
                    'area'       => $param['area'],
                    'update_time'=> time(),
                ]);
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 删除数据中心
     * @desc 删除数据中心
     * @author hh
     * @version v1
     * @param   int id - 数据中心ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function deleteDataCenter($id)
    {
        $dataCenter = $this->find($id);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        // 有线路正在使用
        $line = LineModel::where('data_center_id', $id)->find();
        if(!empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_delete_data_center_for_line_exist')];
        }
        // 套餐正在使用
        $recommendConfig = RecommendConfigModel::where('data_center_id', $id)->find();
        if(!empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
        }
        
        $this->startTrans();
        try{
            $dataCenter->delete();
            $optionId = OptionModel::where('product_id', $dataCenter['product_id'])->where('rel_type', OptionModel::DATA_CENTER_GPU)->where('rel_id', $dataCenter->id)->column('id');
            if(!empty($optionId)){
                OptionModel::whereIn('id', $optionId)->delete();
                PriceModel::where('product_id', $dataCenter['product_id'])->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        hook('after_delete_mf_cloud_data_center', ['id'=>$id]);

        $CountryModel = CountryModel::find($dataCenter['country_id']);

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $productName = ProductModel::where('id', $dataCenter['product_id'])->value('name');

        $description = lang_plugins('log_delete_data_center_success', [
            '{product}' => 'product#'.$dataCenter['product_id'].'#'.$productName.'#',
            '{name}'    => $CountryModel[ $countryName ].$dataCenter['city'].$dataCenter['area'],
        ]);
        active_log($description, 'product', $dataCenter['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-17
     * @title 修改数据中心排序
     * @desc 修改数据中心排序
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID require
     * @param   int param.order - 排序 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateOrder($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }

        $this->update($param, ['id'=>$param['id']], ['order']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($dataCenter, $param, $desc);
        if(!empty($description)){
            $productName = ProductModel::where('id', $dataCenter['product_id'])->value('name');

            $description = lang_plugins('log_modify_data_center_success', [
                '{product}' => 'product#'.$dataCenter['product_id'].'#'.$productName.'#',
                '{name}'    => $dataCenter->getDataCenterName(),
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 数据中心选择
     * @desc 数据中心选择
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.list[].id - 国家ID
     * @return  string data.list[].iso - 国家图标
     * @return  string data.list[].name - 国家名称
     * @return  string data.list[].city[].name - 城市名称
     * @return  int data.list[].city[].area[].id - 数据中心ID
     * @return  string data.list[].city[].area[].name - 区域名称
     * @return  int data.list[].city[].area[].line[].id - 线路ID
     * @return  string data.list[].city[].area[].line[].name - 线路名称
     * @return  string data.list[].city[].area[].line[].bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int data.list[].city[].area[].line[].defence_enable - 是否启用防护(0=未启用,1=启用)
     * @return  int data.count - 总条数
     */
    public function dataCenterSelect($param)
    {
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];

        $where = [];
        if(isset($param['product_id']) && $param['product_id']>0){
            $where[] = ['product_id', '=', $param['product_id']];
        }

        $country = $this
                ->where($where)
                ->column('country_id');

        if(empty($country)){
            return $result;
        }

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->select()
                    ->toArray();

        $city = $this
                ->field('id,country_id,city,area')
                ->where($where)
                ->order('order,id', 'asc')
                ->select()
                ->toArray();

        $lineArr = [];
        // 获取线路
        if(!empty($city)){
            $dataCenterId = array_column($city, 'id');

            $line = LineModel::field('id,data_center_id,name,bill_type,defence_enable')->order('order,id', 'asc')->select();
            foreach($line as $v){
                $lineArr[ $v['data_center_id'] ][] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'bill_type' => $v['bill_type'],
                    'defence_enable' => $v['defence_enable'],
                ];
            }
        }

        $cityArr = [];
        foreach($city as $k=>$v){
            $cityArr[ $v['country_id'] ][ $v['city'] ]['area'][] = [
                'id' => $v['id'],
                'name' => $v['area'],
                'line' => $lineArr[ $v['id'] ] ?? []
            ];
        }
        foreach($country as $k=>$v){
            $temp = $cityArr[ $v['id'] ] ?? [];
            foreach($temp as $kk=>$vv){
                $country[$k]['city'][] = [
                    'name' => (string)$kk,
                    'area' => $vv['area']
                ];
            }
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $country
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 获取订购页面配置
     * @desc 获取订购页面配置
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.product_id - 商品ID require
     * @param   string param.scene custom 场景(recommend=套餐,custom=自定义)
     * @param   string param.billing_cycle - 计费方式(on_demand=按需计费,recurring_prepayment=包年包月)
     * @param   int param.is_downstream 0 是否是下游(0=否,1=是)
     * @return  int data_center[].id - 国家ID
     * @return  string data_center[].iso - 图标
     * @return  string data_center[].name - 名称
     * @return  string data_center[].city[].name - 城市
     * @return  int data_center[].city[].area[].id - 数据中心ID
     * @return  string data_center[].city[].area[].name - 区域
     * @return  int data_center[].city[].area[].reommend_config[].id - 推荐配置ID
     * @return  string data_center[].city[].area[].reommend_config[].name - 推荐配置名称
     * @return  string data_center[].city[].area[].reommend_config[].description - 推荐配置描述
     * @return  int data_center[].city[].area[].reommend_config[].line_id - 线路ID
     * @return  int data_center[].city[].area[].reommend_config[].cpu - CPU
     * @return  int data_center[].city[].area[].reommend_config[].memory - 内存
     * @return  int data_center[].city[].area[].reommend_config[].system_disk_size - 系统盘
     * @return  string data_center[].city[].area[].reommend_config[].system_disk_type - 系统盘类型
     * @return  int data_center[].city[].area[].reommend_config[].data_disk_size - 数据盘
     * @return  string data_center[].city[].area[].reommend_config[].data_disk_type - 数据盘类型
     * @return  string data_center[].city[].area[].reommend_config[].network_type - 网络类型(normal=经典网络,vpc=vpc网络)
     * @return  int data_center[].city[].area[].reommend_config[].bw - 带宽
     * @return  int data_center[].city[].area[].reommend_config[].flow - 流量
     * @return  int data_center[].city[].area[].reommend_config[].peak_defence - 防护峰值
     * @return  int data_center[].city[].area[].reommend_config[].ip_num - IP数量
     * @return  int data_center[].city[].area[].reommend_config[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int data_center[].city[].area[].reommend_config[].gpu_num - GPU数量
     * @return  int data_center[].city[].area[].reommend_config[].gpu_name - GPU型号
     * @return  int data_center[].city[].area[].reommend_config[].ipv6_num - IPv6数量
     * @return  string data_center[].city[].area[].reommend_config[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int data_center[].city[].area[].line[].id - 线路ID
     * @return  string data_center[].city[].area[].line[].name - 线路名称
     * @return  int data_center[].city[].area[].line[].data_center_id - 数据中心ID
     * @return  string data_center[].city[].area[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string data_center[].city[].area[].line[].order_default_defence - 新订购默认防御
     * @return  string data_center[].city[].area[].line[].sync_firewall_rule - 同步防火墙规则(0=关闭,1=开启)
     * @return  string data_center[].city[].area[].gpu_name - GPU型号名称
     * @return  int data_center[].city[].area[].gpu[].value - GPU数量(有才显示)
     * @return  array cpu - CPU配置
     * @return  int cpu[].id - 配置ID
     * @return  int cpu[].value - 核心数
     * @return  int memory[].id - 配置ID
     * @return  array memory- 内存配置
     * @return  string memory[].type - 配置类型(radio=单选,step=阶梯,total=完整)
     * @return  int memory[].value - 配置值
     * @return  int memory[].min_value - 最小值
     * @return  int memory[].max_value - 最大值
     * @return  int memory[].step - 最小变化值
     * @return  array system_disk - 系统盘配置
     * @return  int system_disk[].id - 配置ID
     * @return  string system_disk[].type - 配置类型(radio=单选,step=阶梯,total=完整)
     * @return  int system_disk[].value - 配置值
     * @return  int system_disk[].min_value - 最小值
     * @return  int system_disk[].max_value - 最大值
     * @return  int system_disk[].step - 最小变化值
     * @return  string system_disk[].other_config.disk_type - 磁盘类型
     * @return  string system_disk[].other_config.store_id - 储存ID
     * @return  string system_disk[].customfield.multi_language.other_config.disk_type - 多语言磁盘类型(有就替换)
     * @return  array data_disk - 数据盘配置
     * @return  int data_disk[].id - 配置ID
     * @return  string data_disk[].type - 配置类型(radio=单选,step=阶梯,total=完整)
     * @return  int data_disk[].value - 配置值
     * @return  int data_disk[].min_value - 最小值
     * @return  int data_disk[].max_value - 最大值
     * @return  int data_disk[].step - 最小变化值
     * @return  string data_disk[].other_config.disk_type - 磁盘类型
     * @return  string data_disk[].other_config.store_id - 储存ID
     * @return  string data_disk[].customfield.multi_language.other_config.disk_type - 多语言磁盘类型(有就替换)
     * @return  string config.type - 实例类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int config.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int config.support_normal_network - 是否支持经典网络(0=不支持,1=支持)
     * @return  int config.support_vpc_network - 是否支持VPC网络(0=不支持,1=支持)
     * @return  int config.support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  int config.backup_enable - 是否启用备份(0=不支持,1=支持)
     * @return  int config.snap_enable - 是否启用快照(0=不支持,1=支持)
     * @return  string config.memory_unit - 内存单位(GB,MB)
     * @return  int config.disk_limit_num - 数据盘数量限制
     * @return  int config.free_disk_switch - 免费数据盘开关(0=关闭,1=开启)
     * @return  int config.free_disk_size - 免费数据盘大小(GB)
     * @return  int config.only_sale_recommend_config - 仅售卖套餐(0=关闭,1=开启)
     * @return  int config.no_upgrade_tip_show - 不可升降级时订购页提示(0=关闭,1=开启)
     * @return  int config.default_nat_acl - 默认NAT转发(0=关闭,1=开启)
     * @return  int config.default_nat_web - 默认NAT建站(0=关闭,1=开启)
     * @return  int config.ip_mac_bind_enable - 是否启用嵌套虚拟化(0=关闭,1=开启)
     * @return  int config.nat_acl_limit_enable - 是否启用NAT转发(0=关闭,1=开启)
     * @return  int config.nat_web_limit_enable - 是否启用NAT建站(0=关闭,1=开启)
     * @return  int config.rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口)
     * @return  string config.rand_ssh_port_start - 随机端口开始端口
     * @return  string config.rand_ssh_port_end - 随机端口结束端口
     * @return  string config.rand_ssh_port_windows - 指定端口Windows
     * @return  string config.rand_ssh_port_linux - 指定端口Linux
     * @return  int config.default_one_ipv4 - 默认携带IPv4(0=关闭,1=开启)
     * @return  int config.manual_manage - 手动管理商品(0=关闭,1=开启)
     * @return  string config.free_disk_type - 免费盘类型
     * @return  int config.disk_range_limit_switch - 磁盘大小购买限制开关(0=关闭,1=开启)
     * @return  int config.disk_range_limit - 磁盘大小购买限制(GB)
     * @return  int backup_config[].id - 备份配置ID
     * @return  int backup_config[].num - 备份数量
     * @return  string backup_config[].price - 备份价格
     * @return  int snap_config[].id - 快照ID
     * @return  int snap_config[].num - 快照数量
     * @return  string snap_config[].price - 快照价格
     * @return  string config_limit[].type - 配置限制类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,image=操作系统与计算限制)
     * @return  int config_limit[].data_center_id - 数据中心ID
     * @return  string config_limit[].cpu - cpu(英文逗号分隔)
     * @return  string config_limit[].memory - 内存(英文逗号分隔)
     * @return  int config_limit[].min_memory - 最小内存
     * @return  int config_limit[].max_memory - 最大内存
     * @return  int config_limit[].image_id - 操作系统ID
     * @return  int resource_package[].id - 资源包ID
     * @return  string resource_package[].name - 资源包名称
     * @return  array limit_rule - 限制规则
     * @return  int limit_rule[].id - 限制规则ID
     * @return  array limit_rule[].rule - 条件数据
     * @return  array limit_rule[].rule.cpu.value - CPU
     * @return  string limit_rule[].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string limit_rule[].rule.memory.min - 内存最小值
     * @return  string limit_rule[].rule.memory.max - 内存最大值
     * @return  string limit_rule[].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array limit_rule[].rule.memory.value - 内存值(当内存是单选时)
     * @return  array limit_rule[].rule.data_center.id - 数据中心ID
     * @return  string limit_rule[].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array limit_rule[].rule.image.id - 操作系统ID
     * @return  string limit_rule[].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  array limit_rule[].result - 结果数据
     * @return  array limit_rule[].result.cpu[].value - CPU
     * @return  string limit_rule[].result.cpu[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string limit_rule[].result.memory[].min - 内存最小值
     * @return  string limit_rule[].result.memory[].max - 内存最大值
     * @return  string limit_rule[].result.memory[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array limit_rule[].result.memory[].value - 内存值(当内存是单选时)
     * @return  array limit_rule[].result.data_center[].id - 数据中心ID
     * @return  string limit_rule[].result.data_center[].opt - 运算符(eq=等于,neq=不等于)
     * @return  array limit_rule[].result.image[].id - 操作系统ID
     * @return  string limit_rule[].result.image[].opt - 运算符(eq=等于,neq=不等于)
     * @return  string limit_rule[].result.system_disk[].min - 系统盘最小值
     * @return  string limit_rule[].result.system_disk[].max - 系统盘最大值
     * @return  string limit_rule[].result.system_disk[].opt - 运算符(eq=等于,neq=不等于)
     */
    public function orderPage($param): array
    {
        bcscale(2);
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        $priceBasis = $param['price_basis'] ?? 'agent';
        $priceAgent = $priceBasis=='agent';
        $param['product_id'] = $param['product_id'] ?? 0;
        
        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];
        
        $dataCenter = $this
                    ->field('id,country_id,gpu_name')
                    ->where('product_id', $param['product_id'])
                    ->order('order,id', 'asc')
                    ->select()
                    ->toArray();

        $dataCenterId = array_column($dataCenter, 'id');
        if(!empty($dataCenterId)){
            $dataCenterId = LineModel::whereIn('data_center_id', $dataCenterId)->where('hidden', 0)->column('data_center_id');
        }
        
        if(!empty($dataCenterId)){
            $country = $this
                ->where($where)
                ->whereIn('id', $dataCenterId)
                ->order('order,id', 'asc')
                ->column('country_id');
        }else{
            $country = [];
        }

        $dataCenterGpuName = array_column($dataCenter, 'gpu_name', 'id');

        $data = [
            'data_center' => [],
        ];
        if(!empty($country)){
            if(isset($param['scene']) && $param['scene'] == 'recommend'){
                // 获取推荐配置
                $product = ProductModel::find($param['product_id']);
                $payOntrial = json_decode($product['pay_ontrial'],true) ?? [];

                $whereRecommend = function($query) use ($param) {
                    $query->where('rc.product_id', '=', $param['product_id']);
                    $query->where('rc.hidden', '=', 0);

                    if(!empty($param['billing_cycle']) && $param['billing_cycle'] == 'on_demand'){
                        $query->where('l.bill_type="bw" OR l.support_on_demand=1');
                        $query->where('l.sync_firewall_rule', '=', 0);
                    }
                };

                $recommendConfig = RecommendConfigModel::alias('rc')
                                    ->field('rc.id,rc.name,rc.description,rc.data_center_id,rc.line_id,rc.cpu,rc.memory,rc.system_disk_size,rc.system_disk_type,rc.data_disk_size,rc.data_disk_type,rc.bw,rc.flow,rc.peak_defence,rc.ip_num,rc.upgrade_range,rc.gpu_num,rc.ipv6_num,l.bill_type,rc.ontrial,rc.ontrial_price,rc.ontrial_stock_control,rc.ontrial_qty')
                                    ->leftJoin('module_mf_cloud_line l', 'rc.line_id=l.id')
                                    ->withAttr('ontrial',function ($value) use ($payOntrial){
                                        if(!empty($payOntrial) && $payOntrial['status'] == 1 && $value == 1){
                                            return 1;
                                        }else{
                                            return 0;
                                        }
                                    })
                                    ->where($whereRecommend)
                                    ->order('rc.order,rc.id', 'asc')
                                    ->select()
                                    ->toArray();

                $recommendConfigArr = [];
                foreach($recommendConfig as $v){
                    $v['gpu_name'] = $dataCenterGpuName[ $v['data_center_id'] ] ?? '';
                    $recommendConfigArr[ $v['data_center_id'] ][] = $v;
                }

                if(!empty($recommendConfig)){
                    $city = $this
                        ->field('id,country_id,city,area,gpu_name')
                        ->where($where)
                        ->whereIn('id', array_column($recommendConfig, 'data_center_id'))
                        ->order('order,id', 'asc')
                        ->select()
                        ->toArray();
                }else{
                    $city = [];
                    $country = [];
                }
            }else{
                $city = $this
                    ->field('id,country_id,city,area,gpu_name')
                    ->where($where)
                    ->whereIn('id', $dataCenterId)
                    ->order('order,id', 'asc')
                    ->select()
                    ->toArray();
            }
            if(!empty($city)){
                $language = get_client_lang();
                $countryField = ['en-us'=> 'nicename'];
                $countryName = $countryField[ $language ] ?? 'name_zh';

                $country = array_unique(array_column($city, 'country_id'));

                
                $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->orderRaw('field(id, '.implode(',', $country).')')
                    ->select()
                    ->toArray();
            }

            // 获取线路
            if(!empty($city)){
                $dataCenterId = array_column($city, 'id');

                $whereLine = function($query)use ($dataCenterId,$param) {
                    $query->where('data_center_id', 'IN', $dataCenterId);
                    $query->where('hidden', '=', 0);

                    if(!empty($param['billing_cycle']) && $param['billing_cycle'] == 'on_demand'){
                        $query->where('bill_type="bw" OR support_on_demand=1');
                        $query->where('sync_firewall_rule', '=', 0);
                    }
                };

                $line = LineModel::field('id,name,data_center_id,bill_type,defence_enable,order_default_defence,sync_firewall_rule')
                    ->where($whereLine)
                    ->order('order,id', 'asc')
                    ->select()
                    ->toArray();

                // 获取所有线路配置
                if(!empty($line)){
                    $lineArr = [];
                    foreach($line as $v){
                        $v['sync_firewall_rule'] = $v['defence_enable'] == 1 ? $v['sync_firewall_rule'] : 0;
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }

                // 获取数据中心GPU
                $gpu = OptionModel::field('value,rel_id')->where('product_id', $param['product_id'])->where('rel_type', OptionModel::DATA_CENTER_GPU)->whereIn('rel_id', $dataCenterId)->order('value', 'asc')->select()->toArray();
                // 获取所有数据中心显卡配置
                if(!empty($gpu)){
                    $gpuArr = [];
                    foreach($gpu as $v){
                        $gpuArr[ $v['rel_id'] ][] = [
                            'value' => $v['value'],
                        ];
                    }
                }
            }

            $cityArr = [];
            foreach($city as $k=>$v){
                $gpu = $gpuArr[ $v['id'] ] ?? [];

                $cityArr[ $v['country_id'] ][ $v['city'] ]['area'][] = [
                    'id'                => $v['id'],
                    'name'              => $v['area'],
                    'recommend_config'  => $recommendConfigArr[ $v['id'] ] ?? [],
                    'line'              => $lineArr[ $v['id'] ] ?? [],
                    'gpu_name'          => !empty($gpu) ? $v['gpu_name'] : '',
                    'gpu'               => !empty($v['gpu_name']) ? $gpu : [],
                ];
            }
            foreach($country as $k=>$v){
                $temp = $cityArr[ $v['id'] ] ?? [];
                foreach($temp as $kk=>$vv){
                    $country[$k]['city'][] = [
                        'name' => (string) $kk,
                        'area' => $vv['area']
                    ];
                }
            }

            $data['data_center'] = $country;
        }
        // cpu
        $data['cpu'] = OptionModel::field('id,value')
                    ->where($where)
                    ->where('rel_type', OptionModel::CPU)
                    ->withAttr('value', function($val){
                        return (int)$val;
                    })
                    ->orderRaw('--value asc')
                    ->select()
                    ->toArray();
        // 内存
        $data['memory'] = OptionModel::field('id,type,value,min_value,max_value,step')
                        ->where($where)
                        ->where('rel_type', OptionModel::MEMORY)
                        ->withAttr('value', function($val){
                            return (int)$val;
                        })
                        ->orderRaw('--value asc,min_value asc')
                        ->select()
                        ->toArray();
        // 储存配置
        $data['system_disk'] = OptionModel::field('id,type,value,min_value,max_value,step,other_config')
                            ->where($where)
                            ->where('rel_type', OptionModel::SYSTEM_DISK)
                            ->withAttr('value', function($val){
                                return (int)$val;
                            })
                            ->orderRaw('--value asc,min_value asc')
                            ->select()
                            ->toArray();
        $data['data_disk'] = OptionModel::field('id,type,value,min_value,max_value,step,other_config')
                            ->where($where)
                            ->where('rel_type', OptionModel::DATA_DISK)
                            ->withAttr('value', function($val){
                                return (int)$val;
                            })
                            ->orderRaw('--value asc,min_value asc')
                            ->select()
                            ->toArray();

        foreach($data['system_disk'] as $k=>$v){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'disk_type' => $v['other_config']['disk_type'],
                ],
            ]);
            $data['system_disk'][$k]['customfield']['multi_language']['other_config'] = $multiLanguage ?: (object)[];
        }
        foreach($data['data_disk'] as $k=>$v){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'disk_type' => $v['other_config']['disk_type'],
                ],
            ]);
            $data['data_disk'][$k]['customfield']['multi_language']['other_config'] = $multiLanguage ?: (object)[];
        }

        // 获取配置
        $config = ConfigModel::field('type,support_ssh_key,support_normal_network,support_vpc_network,support_public_ip,backup_enable,snap_enable,ip_mac_bind,nat_acl_limit,nat_web_limit,ipv6_num,memory_unit,disk_limit_switch,disk_limit_num,free_disk_switch,free_disk_size,only_sale_recommend_config,no_upgrade_tip_show,default_nat_acl,default_nat_web,rand_ssh_port,rand_ssh_port_start,rand_ssh_port_end,rand_ssh_port_windows,rand_ssh_port_linux,default_one_ipv4,manual_manage,free_disk_type,custom_rand_password_rule,default_password_length,disk_range_limit_switch,disk_range_limit')
                ->where($where)
                ->find();

        $config['ip_mac_bind_enable'] = !empty($config['ip_mac_bind']) ? 1 : 0;
        $config['nat_acl_limit_enable'] = is_numeric($config['nat_acl_limit']) && !empty($config['nat_acl_limit']) ? 1 : 0;
        $config['nat_web_limit_enable'] = is_numeric($config['nat_web_limit']) && !empty($config['nat_web_limit']) ? 1 : 0;
        // $config['ipv6_num_enable'] = is_numeric($config['ipv6_num']) ? 1 : 0;
        $config['disk_limit_num'] = $config['disk_limit_switch'] == 1 ? $config['disk_limit_num'] : 16;
        unset($config['ip_mac_bind'],$config['nat_acl_limit'],$config['nat_web_limit'],$config['ipv6_num'],$config['disk_limit_switch']);

        $data['config'] = $config;
        
        $DurationModel = new DurationModel();
        // 如果开启了备份快照才返回
        if($config['backup_enable'] == 1){
            $data['backup_config'] = BackupConfigModel::field('id,num,price')->where($where)->where('type', 'backup')->order('num', 'asc')->select()->toArray();

            if($isDownstream && $priceAgent){
                foreach($data['backup_config'] as $k=>$v){
                    $data['backup_config'][$k]['price'] = $DurationModel->downstreamSubClientLevelPrice([
                        'product_id' => $param['product_id'],
                        'client_id'  => get_client_id(),
                        'price'      => $v['price'],
                    ]);
                }
            }
        }else{
            $data['backup_config'] = [];
        }
        
        if($config['snap_enable'] == 1){
            $data['snap_config'] = BackupConfigModel::field('id,num,price')->where($where)->where('type', 'snap')->order('num', 'asc')->select()->toArray();

            if($isDownstream && $priceAgent){
                foreach($data['snap_config'] as $k=>$v){
                    $data['snap_config'][$k]['price'] = $DurationModel->downstreamSubClientLevelPrice([
                        'product_id' => $param['product_id'],
                        'client_id'  => get_client_id(),
                        'price'      => $v['price'],
                    ]);
                }
            }
        }else{
            $data['snap_config'] = [];
        }

        // 已废弃,后续删除
        $data['config_limit'] = [];

        // 代理商资源包
        $data['resource_package'] = [];
        $ProductModel = ProductModel::find($param['product_id']);
        if(!empty($ProductModel) && $ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                if(isset($hash['account_type']) && $hash['account_type'] == 'agent'){
                    $data['resource_package'] = ResourcePackageModel::field('id,name')
                                ->where('product_id', $param['product_id'])
                                ->select()
                                ->toArray();
                }
            }
        }

        // 限制规则
        $LimitRuleModel = new LimitRuleModel();
        $data['limit_rule'] = $LimitRuleModel->homeLimitRule($param['product_id']);

        // 获取安全组配置
        $SecurityGroupConfigModel = new SecurityGroupConfigModel();
        $data['security_group_config'] = $SecurityGroupConfigModel->homeConfigList($param['product_id']);

        return $data;
    }

    /**
     * 时间 2022-06-21
     * @title 获取数据中心
     * @desc 获取数据中心格式化显示
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  string data.list[].iso - 图标
     * @return  string data.list[].country_name - 国家名称
     */
    public function formatDisplay($param)
    {
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];
        $param['id'] = $param['id'] ?: 0;

        $where = [];
        if($param['id']>0){
            $where[] = ['dc.product_id', '=', $param['id']];
        }

        $language = get_client_lang();
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $list = $this
                ->field('dc.id,dc.city,dc.area,c.iso,c.'.$countryName.' country_name')
                ->alias('dc')
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->where($where)
                ->select()
                ->toArray();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $list
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-09-28
     * @title 获取数据中心名称
     * @desc 获取数据中心名称
     * @author hh
     * @version v1
     * @param   DataCenterModel $DataCenterModel - 数据中心模型实例
     * @return  string
     */
    public function getDataCenterName($DataCenterModel = null)
    {
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        return ($CountryModel[ $countryName ] ?? '').'-'.$DataCenterModel['city'].'-'.$DataCenterModel['area'];
    }

    /**
     * 时间 2023-12-15
     * @title 获取数据中心国家名称
     * @desc  获取数据中心国家名称
     * @author hh
     * @version v1
     * @param   DataCenterModel $DataCenterModel - 数据中心模型实例
     * @return  string
     */
    public function getCountryName($DataCenterModel = null)
    {
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';
        return $CountryModel[ $countryName ] ?? '';
    }

    /**
     * 时间 2024-02-18
     * @title 数据中心城市获取器
     * @desc  数据中心城市获取器
     * @author hh
     * @version v1
     * @param   string value - 城市 require
     * @return  string
     */
    public function getCityAttr($value)
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

    /**
     * 时间 2024-02-18
     * @title 数据中心区域获取器
     * @desc  数据中心区域获取器
     * @author hh
     * @version v1
     * @param   string value - 区域 require
     * @return  string
     */
    public function getAreaAttr($value)
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

    /**
     * 时间 2024-08-26
     * @title GPU名称获取器
     * @desc  GPU名称获取器
     * @author hh
     * @version v1
     * @param   string value - GPU名称 require
     * @return  string
     */
    public function getGpuNameAttr($value)
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

    /**
     * 时间 2024-06-21
     * @title 数据中心详情
     * @desc  数据中心详情
     * @author hh
     * @version v1
     * @param   int id - 数据中心ID require
     * @return  int id - 数据中心ID
     * @return  int country_id - 国家ID
     * @return  string city - 城市
     * @return  string area - 区域
     * @return   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return   int cloud_config_id - 魔方云配置ID
     * @return   int order - 排序
     * @return   string gpu_name - GPU型号名称
     * @return   array gpu_data - GPU价格配置数据
     * @return   int gpu_data[].id - 通用配置ID
     * @return   string gpu_data[].type - 配置方式(radio=单选)
     * @return   int gpu_data[].value - 显卡数量
     * @return   int gpu_data[].product_id - 商品ID
     * @return   string gpu_data[].price - 价格
     * @return   string gpu_data[].duration - 周期
     */
    public function dataCenterDetail($id)
    {
        $dataCenter = $this
                ->field('id,country_id,city,area,cloud_config,cloud_config_id,order,gpu_name')
                ->find($id);
        if(empty($dataCenter)){
            return [];
        }
        $data = $dataCenter->toArray();

        $OptionModel = new OptionModel();

        $param = [];
        $param['rel_id'] = $id;
        $param['sort'] = 'asc';
        $param['page'] = 1;
        $param['limit'] = 999;

        $param['rel_type'] = OptionModel::DATA_CENTER_GPU;
        $param['orderby'] = 'value';
        
        $field = 'id,type,value';
        $result = $OptionModel->optionList($param, $field);

        $data['gpu_data'] = $result['list'];
        return $data;
    }

    /**
     * 时间 2024-06-24
     * @title 修改数据中心GPU名称
     * @desc  修改数据中心GPU名称
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID require
     * @param   string param.gpu_name - GPU型号名称 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateGpuName($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        // 判断是否有配置,必须有配置才能保存
        $gpu = OptionModel::where('product_id', $dataCenter['product_id'])->where('rel_type', OptionModel::DATA_CENTER_GPU)->where('rel_id', $dataCenter->id)->find();
        if(empty($gpu)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_add_gpu_option_first')];
        }

        $this->update($param, ['id'=>$param['id']], ['gpu_name']);

        $desc = [
            'gpu_name'=>lang_plugins('mf_cloud_line_gpu_name'),
        ];

        $description = ToolLogic::createEditLog($dataCenter, ['gpu_name'=>$param['gpu_name']], $desc);
        if(!empty($description)){
            $productName = ProductModel::where('id', $dataCenter['product_id'])->value('name');

            $description = lang_plugins('log_modify_data_center_success', [
                '{product}' => 'product#'.$dataCenter['product_id'].'#'.$productName.'#',
                '{name}'    => $dataCenter->getDataCenterName(),
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-06-24
     * @title 删除数据中心显卡
     * @desc  删除数据中心显卡
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function dataCenterGpuDelete($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $use = RecommendConfigModel::where('data_center_id', $dataCenter['id'])->where('gpu_num', '>', 0)->value('id');
        if(!empty($use)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_cannot_disable_gpu_for_package_use')];
        }    

        $deleteNum = 0;
        $this->startTrans();
        try{
            $deleteNum += $this->where('id', $param['id'])->update(['gpu_name'=>'']);
            $optionId = OptionModel::where('product_id', $dataCenter['product_id'])->where('rel_type', OptionModel::DATA_CENTER_GPU)->where('rel_id', $dataCenter->id)->column('id');
            if(!empty($optionId)){
                OptionModel::whereIn('id', $optionId)->delete();
                PriceModel::where('product_id', $dataCenter['product_id'])->where('rel_type', PriceModel::REL_TYPE_OPTION)->whereIn('rel_id', $optionId)->delete();
                $deleteNum += count($optionId);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        if($deleteNum > 0){
            $productName = ProductModel::where('id', $dataCenter['product_id'])->value('name');

            $description = lang_plugins('log_mf_cloud_delete_data_center_gpu_success', [
                '{product}' => 'product#'.$dataCenter['product_id'].'#'.$productName.'#',
                '{name}'    => $dataCenter->getDataCenterName(),
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        return ['status'=>200, 'msg'=>lang_plugins('delete_success')];


    }


}
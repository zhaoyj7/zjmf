<?php
namespace addon\promo_code\model;

use addon\idcsmart_renew\model\IdcsmartRenewModel;
use addon\promo_code\logic\PromoCodeLogic;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use app\common\model\UpstreamProductModel;
use app\admin\model\PluginModel;
use think\db\Query;
use think\Model;
use addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel;

/**
 * @title 优惠码模型
 * @desc 优惠码模型
 * @use addon\promo_code\model\PromoCodeModel
 */
class PromoCodeModel extends Model
{
    protected $name = 'addon_promo_code';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'code'              => 'string',
        'type'              => 'string',
        'value'             => 'float',
        'status'            => 'int',
        'client_type'       => 'string',
        'client_level'      => 'string',
        'start_time'        => 'int',
        'end_time'          => 'int',
        'max_times'         => 'int',
        'used'              => 'int',
        'single_user_once'  => 'int',
        'upgrade'           => 'int',
        'host_upgrade'      => 'int',
        'renew'             => 'int',
        'loop'              => 'int',
        'cycle_limit'       => 'int',
        'cycle'             => 'string',
        'flow_packet'       => 'int',
        'notes'             => 'string',
        'delete_time'       => 'int',
        'on_demand_to_recurring_prepayment' => 'int',
        'exclude_with_client_level' => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    # 优惠码适用场景
    private $applyScene = [
        'New',
        'Upgrade',
        'Renew',
    ];

    # 适用场景时长
    private $applySceneTime = [
        'monthly'       => ['min' => 28*24*3600, 'max' => 31*24*3600],
        'quarterly'     => ['min' => 89*24*3600, 'max' => 92*24*3600],
        'semiannually'  => ['min' => 178*24*3600, 'max' => 185*24*3600],
        'annually'      => ['min' => 360*24*3600, 'max' => 366*24*3600],
        'biennially'    => ['min' => 2*360*24*3600, 'max' => 2*366*24*3600],
        'triennially'   => ['min' => 3*360*24*3600, 'max' => 3*366*24*3600],
    ];

    /**
     * 时间 2022-10-19
     * @title 优惠码列表
     * @desc 优惠码列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字搜索:优惠码
     * @param string param.type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @param string param.status - 状态:Suspended已停用,Active启用中,Expiration已失效,Pending待生效
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,code
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 优惠码列表
     * @return int list[].id - ID
     * @return string list[].code - 优惠码
     * @return string list[].type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @return float list[].value - 优惠码数值
     * @return int list[].max_times - 可用
     * @return int list[].used - 已用
     * @return int list[].start_time - 开始时间
     * @return int list[].end_time - 结束时间
     * @return int list[].status - 状态:Suspended已停用,Active启用中,Expiration已失效,Pending待生效
     * @return int list[].notes - 备注
     * @return int count - 优惠码总数
     */
    public function promoCodeList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','code'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('code','like',"%{$param['keywords']}%");
            }
            if (isset($param['type']) && !empty($param['type'])){
                $query->where('type', $param['type']);
            }
            if (isset($param['status']) && !empty($param['status'])){
                $time = time();
                if($param['status']=='Pending'){
                    $query->whereRaw("start_time>{$time}");
                }else if($param['status']=='Active'){
                    $query->whereRaw("status=1 AND (end_time=0 OR end_time>={$time})");
                }else if($param['status']=='Suspended'){
                    $query->whereRaw("status=0 AND (end_time=0 OR end_time>={$time})");
                }else if($param['status']=='Expiration'){
                    $query->whereRaw("end_time>0 AND end_time<{$time}");
                }
            }

        };

        $promoCodes = $this->field('id,code,type,value,max_times,used,start_time,end_time,status,notes')
            ->withAttr('status',function ($value,$data){
                $time = time();
                if ($data['start_time']>$time){
                    return 'Pending';
                }else if (!empty($data['end_time'])){ # 自定义失效时间
                    if ($data['end_time']<$time){
                        return 'Expiration';
                    } elseif ($time<=$data['end_time'] && $value==1){
                        return 'Active';
                    } elseif ($time<=$data['end_time'] && $value==0){
                        return 'Suspended';
                    }
                }else{
                    if ($value == 1){
                        return 'Active';
                    }else{
                        return 'Suspended';
                    }
                }
            })
            ->where('delete_time',0)
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->order('status','desc')
            ->order('end_time','asc')
            ->select()
            ->toArray();

        $count = $this->where('delete_time',0)->where($where)->count();

        return ['list'=>$promoCodes, 'count'=>$count];
    }

    /**
     * 时间 2022-10-19
     * @title 获取优惠码
     * @desc 获取优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @return int id - ID
     * @return string code - 优惠码
     * @return string type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @return float value - 优惠码数值
     * @return string client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品,not_have_client_level未拥有指定用户等级
     * @return array client_level - 用户等级ID,为空数组代表所有等级
     * @return int start_time - 开始时间
     * @return int end_time - 结束时间,为0代表无限
     * @return int max_times - 最大使用次数:0不限
     * @return int single_user_once - 单用户一次:0关闭,1开启
     * @return int upgrade - 升降级:0关闭,1开启
     * @return int host_upgrade - 升降级商品配置:0关闭,1开启
     * @return int renew - 续费:0关闭,1开启
     * @return int loop - 循环优惠:0关闭,1开启
     * @return int cycle_limit - 周期限制:0关闭,1开启
     * @return array cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年
     * @return int flow_packet - 流量包使用:0关闭,1开启
     * @return string notes - 备注
     * @return array products - 可应用商品的ID
     * @return array need_products - 需求商品的ID
     * @return int on_demand_to_recurring_prepayment - 按需转包年包月:0关闭,1开启
     * @return int exclude_with_client_level - 不与用户等级同享:0关闭,1开启
     */
    public function indexPromoCode($param)
    {
        $promoCode =  $this->field('id,code,type,value,client_type,client_level,start_time,end_time,max_times,single_user_once,upgrade,host_upgrade,renew,loop,cycle_limit,cycle,notes,on_demand_to_recurring_prepayment,flow_packet,exclude_with_client_level')
            ->where('delete_time',0)
            ->find($param['id']);
        if (empty($promoCode)){
            return (object)[];
        }

        $promoCode['cycle'] = !empty($promoCode['cycle']) ? explode(',',$promoCode['cycle']) : [];

        $promoCode['products'] = PromoCodeProductModel::where('addon_promo_code_id',$param['id'])->column('product_id');

        $promoCode['need_products'] = PromoCodeProductNeedModel::where('addon_promo_code_id',$param['id'])->column('product_id');

        $clientLevel = array_filter(explode(',', $promoCode['client_level']));
        foreach ($clientLevel as $key => $value) {
            $clientLevel[$key] = (int)$value;
        }
        $promoCode['client_level'] = $clientLevel;

        return $promoCode;
    }

    /**
     * 时间 2022-10-19
     * @title 添加优惠码
     * @desc 添加优惠码
     * @author theworld
     * @version v1
     * @param string param.code - 优惠码 required
     * @param string param.type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费 required
     * @param float param.value - 优惠码数值 类型不为免费时必填
     * @param string param.client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品,not_have_client_level未拥有指定用户等级 required
     * @param array client_level - 用户等级ID,为空数组代表所有等级
     * @param int param.start_time - 开始时间 required
     * @param int param.end_time - 结束时间  
     * @param int param.max_times - 最大使用次数:0不限 required
     * @param int param.single_user_once - 单用户一次:0关闭,1开启 required
     * @param int param.upgrade - 升降级:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.host_upgrade - 升降级商品配置:0关闭,1开启,仅百分比支持开启 required
     * @param int param.renew - 续费:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.loop - 循环优惠:0关闭,1开启,仅百分比支持开启 required
     * @param int param.cycle_limit - 周期限制:0关闭,1开启 required
     * @param array param.cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param int param.flow_packet - 流量包使用:0关闭,1开启 required
     * @param string param.notes - 备注
     * @param array param.products - 可应用商品的ID
     * @param array param.need_products - 需求商品的ID
     * @param int param.on_demand_to_recurring_prepayment - 按需转包年包月:0关闭,1开启
     * @param int param.exclude_with_client_level - 不与用户等级同享:0关闭,1开启 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createPromoCode($param)
    {
        # 判断value值
        if($param['type']=='percent'){
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
                if ($param['value']<=0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
                if ($param['value']>100){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
            }
        }else if($param['type']=='fixed_amount'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_not_support')];
            }
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
                }
                if ($param['value']<=0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
            }
        }else if($param['type']=='replace_price'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_not_support')];
            }
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
                }
                if ($param['value']<0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
            }
        }else if($param['type']=='free'){
            if($param['host_upgrade']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_free_not_support')];
            }
            $param['value'] = 0;
        }
        
        if($param['client_type']=='not_have_client_level'){
            $param['client_level'] = $param['client_level'] ?? [];

            $PluginModel = new PluginModel();
            $addons = $PluginModel->plugins('addon');
            $addons = array_column($addons['list'], 'name');
            if(!in_array('IdcsmartClientLevel', $addons)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_client_level_is_not_install')];
            }
            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
            $count = $IdcsmartClientLevelModel->whereIn('id', $param['client_level'])->count();
            if($count!=count($param['client_level'])){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_client_level_is_not_exist')];
            }
        }else{
            $param['client_level'] = [];
        }

        # 验证适用产品及规格
        $ProductModel = new ProductModel();
        foreach ($param['products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }
        foreach ($param['need_products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }


        $this->startTrans();

        try{
            $promoCode = $this->create([
                'code' => $param['code'],
                'type' => $param['type'],
                'value' => $param['value']??0,
                'client_type' => $param['client_type'],
                'client_level' => implode(',', $param['client_level']),
                'start_time' => $param['start_time']??0,
                'end_time' => $param['end_time']??0,
                'max_times' => $param['max_times']??0,
                'single_user_once' => $param['single_user_once'],
                'upgrade' => $param['upgrade'],
                'host_upgrade' => $param['host_upgrade'],
                'renew' => $param['renew'],
                'loop' => $param['loop'],
                'cycle_limit' => $param['cycle_limit'],
                'cycle' => implode(',',$param['cycle']),
                'flow_packet' => $param['flow_packet']??0,
                'notes' => $param['notes']??'',
                'create_time' => time(),
                'status' => 1,
                'delete_time' => 0,
                'on_demand_to_recurring_prepayment' => $param['on_demand_to_recurring_prepayment'],
                'exclude_with_client_level' => $param['exclude_with_client_level']??0,
            ]);

            $PromoCodeProductModel = new PromoCodeProductModel();
            $insert = [];
            foreach ($param['products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $promoCode->id,
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductModel->saveAll($insert);

            $PromoCodeProductNeedModel = new PromoCodeProductNeedModel();
            $insert = [];
            foreach ($param['need_products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $promoCode->id,
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductNeedModel->saveAll($insert);

            active_log(lang_plugins('log_admin_create_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$param['code']]),'promo_code',$promoCode->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('create_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('create_success')];
    }

    /**
     * 时间 2022-10-19
     * @title 编辑优惠码
     * @desc 编辑优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @param string param.client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品,not_have_client_level未拥有指定用户等级 required
     * @param array client_level - 用户等级ID,为空数组代表所有等级
     * @param int param.start_time - 开始时间 required
     * @param int param.end_time - 结束时间  
     * @param int param.max_times - 最大使用次数:0不限 required
     * @param int param.single_user_once - 单用户一次:0关闭,1开启 required
     * @param int param.upgrade - 升降级:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.host_upgrade - 升降级商品配置:0关闭,1开启,仅百分比支持开启 required
     * @param int param.renew - 续费:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.loop - 循环优惠:0关闭,1开启,仅百分比支持开启 required
     * @param int param.cycle_limit - 周期限制:0关闭,1开启 required
     * @param array param.cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param int param.flow_packet - 流量包使用:0关闭,1开启 required
     * @param string param.notes - 备注
     * @param array param.products - 可应用商品的ID
     * @param array param.need_products - 需求商品的ID
     * @param int param.on_demand_to_recurring_prepayment - 按需转包年包月:0关闭,1开启
     * @param int param.exclude_with_client_level - 不与用户等级同享:0关闭,1开启 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updatePromoCode($param)
    {
        $promoCode =  $this->where('delete_time',0)->where('id',$param['id'])->find();
        if (empty($promoCode)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
        }

        # 验证适用产品及规格
        $ProductModel = new ProductModel();
        foreach ($param['products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }
        foreach ($param['need_products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }

        # 判断value值
        if($promoCode['type']=='fixed_amount'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_not_support')];
            }
        }else if($promoCode['type']=='replace_price'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_not_support')];
            }
        }else if($promoCode['type']=='free'){
            if($param['host_upgrade']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_free_not_support')];
            }
        }

        if($param['client_type']=='not_have_client_level'){
            $param['client_level'] = $param['client_level'] ?? [];

            $PluginModel = new PluginModel();
            $addons = $PluginModel->plugins('addon');
            $addons = array_column($addons['list'], 'name');
            if(!in_array('IdcsmartClientLevel', $addons)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_client_level_is_not_install')];
            }
            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
            $count = $IdcsmartClientLevelModel->whereIn('id', $param['client_level'])->count();
            if($count!=count($param['client_level'])){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_client_level_is_not_exist')];
            }
        }else{
            $param['client_level'] = [];
        }

        //$logDescription = log_description($promoCode->toArray(),$param,'promo_code',true);

        $this->startTrans();

        try{
            $this->update([
                'client_type' => $param['client_type'],
                'client_level' => implode(',', $param['client_level']),
                'start_time' => $param['start_time']??0,
                'end_time' => $param['end_time']??0,
                'max_times' => $param['max_times']??0,
                'single_user_once' => $param['single_user_once'],
                'upgrade' => $param['upgrade'],
                'host_upgrade' => $param['host_upgrade'],
                'renew' => $param['renew'],
                'loop' => $param['loop'],
                'cycle_limit' => $param['cycle_limit'],
                'cycle' => implode(',',$param['cycle']),
                'flow_packet' => $param['flow_packet']??0,
                'notes' => $param['notes']??'',
                'update_time' => time(),
                'on_demand_to_recurring_prepayment' => $param['on_demand_to_recurring_prepayment'],
                'exclude_with_client_level' => $param['exclude_with_client_level']??0,
            ], ['id' => $param['id']]);

            $PromoCodeProductModel = new PromoCodeProductModel();
            $PromoCodeProductModel->where('addon_promo_code_id',$param['id'])->delete();
            $insert = [];
            foreach ($param['products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $param['id'],
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductModel->saveAll($insert);

            $PromoCodeProductNeedModel = new PromoCodeProductNeedModel();
            $PromoCodeProductNeedModel->where('addon_promo_code_id',$param['id'])->delete();
            $insert = [];
            foreach ($param['need_products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $param['id'],
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductNeedModel->saveAll($insert);

            //active_log(lang_plugins('log_admin_update_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode->code,'{description}'=>$logDescription]),'promo_code',$promoCode->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('update_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('update_success')];

    }

    /**
     * 时间 2022-10-19
     * @title 删除优惠码
     * @desc 删除优惠码
     * @author theworld
     * @version v1
     * @param int id - 优惠码ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deletePromoCode($param)
    {
        $id = $param['id'] ?? 0;
        if(is_array($id)){
            $promoCode =  $this->where('delete_time',0)->whereIn('id',$id)->select()->toArray();
            if(count($id)!=count($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
            }
        }else{
            $promoCode =  $this->where('delete_time',0)->where('id',$id)->find();
            if (empty($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
            }
        }

        $this->startTrans();

        try{
            if(is_array($id)){
                foreach ($id as $v) {
                    $this->update([
                        'delete_time' => time()
                    ], ['id' => $v]);
                }

                active_log(lang_plugins('log_admin_delete_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>implode(',', array_column($promoCode, 'code'))]),'promo_code');
            }else{
                $this->update([
                    'delete_time' => time()
                ], ['id' => $id]);

                active_log(lang_plugins('log_admin_delete_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

    /**
     * 时间 2022-10-19
     * @title 启用/禁用优惠码
     * @desc 启用/禁用优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @param int param.status - 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function statusPromoCode($param)
    {
        $id = $param['id'] ?? 0;
        if(is_array($id)){
            $promoCode =  $this->where('delete_time',0)->whereIn('id',$id)->select()->toArray();
            if(count($id)!=count($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
            }

            $time = time();

            foreach ($promoCode as $key => $value) {
                if ($value['status'] == $param['status']){
                    unset($promoCode[$key]);
                }
            }
            $promoCode = array_values($promoCode);
            $id =array_column($promoCode, 'id');
        }else{
            $promoCode =  $this->where('delete_time',0)->where('id',$param['id'])->find();
            if (empty($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
            }
            $time = time();

            if ($promoCode->status == $param['status']){
                return ['status'=>400,'msg'=>lang_plugins('cannot_repeat_opreate')];
            }
        }

        $status = $param['status'];

        $this->startTrans();
        try{
            if(is_array($id)){
                foreach ($id as $v) {
                    $this->update([
                        'status' => $status,
                        'update_time' => $time,
                    ], ['id' => $v]);
                }

                # 记录日志
                if ($status == 1){
                    active_log(lang_plugins('log_admin_enable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>implode(',', array_column($promoCode, 'code'))]),'promo_code');
                }else{
                    active_log(lang_plugins('log_admin_disable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>implode(',', array_column($promoCode, 'code'))]),'promo_code');
                }
            }else{

                $this->update([
                    'status' => $status,
                    'update_time' => $time,
                ],['id'=>intval($param['id'])]);

                # 记录日志
                if ($status == 1){
                    active_log(lang_plugins('log_admin_enable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);
                }else{
                    active_log(lang_plugins('log_admin_disable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);
                }
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('fail_message')];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-10-19
     * @title 获取随机优惠码
     * @desc 获取随机优惠码
     * @author theworld
     * @version v1
     * @return string - - 优惠码
     */
    public function generatePromoCode()
    {
        $PromoCodeLogic = new PromoCodeLogic();

        $code = $PromoCodeLogic->generatePromoCode();

        return $code;
    }

    /**
     * 时间 2022-10-20
     * @title 处理优惠码
     * @desc 处理优惠码
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID
     * @param int param.order_id - 订单ID
     * @param int param.client_id - 用户ID
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return array data.order_items - 优惠码订单子项
     * @return int data.order_items[].host_id - 产品ID
     * @return int data.order_items[].product_id - 商品ID
     * @return int data.order_items[].type addon_promo_code 订单子项类型
     * @return int data.order_items[].rel_id - 优惠码ID
     * @return int data.order_items[].amount - 金额
     * @return int data.order_items[].description - 描述
     */
    public function clientPromoCode($param)
    {
        # 判断优惠码
        /*if (!isset($param['promo_code']) || empty($param['promo_code'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }*/

        // 检查用户等级插件是否启用
        $PluginModel = new PluginModel();
        $idcsmartClientLevelPlugin = $PluginModel->where('name', 'IdcsmartClientLevel')->where('status', 1)->where('module', 'addon')->find();

        $promoCode = $param['promo_code'] ?? '';

        //unset($param['promo_code']);
        $data = $param;

        $amount = $param['amount']??0;
        # 是否应用成功
        //$applySuccess = false;
        # 订单子项
        $orderItems = [];
        # 总折扣金额
        $discountTotal = 0;
        # 过滤相同优惠码
        //$promoCodes = array_unique($promoCodes);
        $deleteOrderItemAmount = '0.00'; // 删除订单子项的金额

        /*foreach ($promoCodes as $promoCode){
            $data['promo_code'] = $promoCode;*/
            $result = $this->clientPromoCodeSingleHandle($data);
            # 考虑叠加使用
            if ($result['status'] == 200){

                $discount = floatval($result['data']['discount']);
                $discount2 = floatval($result['data']['discount2'] ?? 0);
                # 判断金额
                $baseAmount = $amount;
                $amount = bcsub($amount,$discount,2);
                if ($amount<=0){
                    $discount = $baseAmount>0?$baseAmount:0;
                }

                $discountTotal = bcadd($discountTotal,$discount,2);

                //$applySuccess = true;

                // 流量包
                if ($param['scene'] == 'artificial' && $param['order_item_type'] == 'addon_idcsmart_flow_packet'){
                    $target = lang_plugins('addon_promo_code_flow_packet');
                }else{
                    $target = lang_plugins('addon_promo_code_host',['{host_id}'=>$param['host_id'] ?? 0]);
                }
                # 记录至订单子项
                $PromoCodeModel = $this->find($result['data']['id']);
                if ($PromoCodeModel['type'] == 'percent'){
                    $description = lang_plugins('promo_code_type_percent_description',['{promo_code}'=>$promoCode,'{target}'=>$target,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'fixed_amount'){
                    $description = lang_plugins('promo_code_type_fixed_amount_description',['{promo_code}'=>$promoCode,'{target}'=>$target,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'replace_price'){
                    $description = lang_plugins('promo_code_type_replace_price_description',['{promo_code}'=>$promoCode,'{target}'=>$target,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'free'){
                    $description = lang_plugins('promo_code_type_free_description',['{promo_code}'=>$promoCode,'{target}'=>$target]);
                }

                $orderItems[] = [
                    'host_id' => $param['host_id']??0,
                    'product_id' => $param['product_id']??0,
                    'type' => $this->name, # 类型存表名(除前缀)
                    'rel_id' => $result['data']['id'],
                    'amount' => -$discount,
                    'description' => $description,
                ];

                if(isset($param['host_id']) && !empty($param['host_id']) && $param['scene']=='new'){
                    // wyh 20240522 续费金额折扣单独计算
                    $host = HostModel::find($param['host_id']);
                    if(!empty($host) && $host['is_delete'] == 0){
                        // 以基础续费金额计算，否则会导致折上折问题！
                        //$data['amount'] = $host['base_renew_amount'];
                        $data['amount'] = $host['base_price'];
                        $resultRenew = $this->clientPromoCodeSingleHandle($data);

                        $update = [
                            'first_payment_amount' => $host['first_payment_amount'],
                            'renew_amount'  => $host['renew_amount'],
                        ];

                        // hh 20250213 优惠码与用户等级互斥处理
                        if(!empty($PromoCodeModel['exclude_with_client_level']) && $PromoCodeModel['exclude_with_client_level'] == 1){
                            // 查询该产品的用户等级折扣记录
                            $OrderItemModel = new OrderItemModel();
                            $clientLevelItems = $OrderItemModel->where('order_id', $param['order_id'])
                                ->where('host_id', $param['host_id'])
                                ->where('type', 'addon_idcsmart_client_level')
                                ->find();
                            
                            if(!empty($clientLevelItems) && !empty($host)){
                                // 被删除的首次折扣金额（负数）
                                $deleteOrderItemAmount = $deletedAmount = $clientLevelItems['amount'];
                                
                                // 删除用户等级折扣记录
                                $OrderItemModel->where('order_id', $param['order_id'])
                                    ->where('host_id', $param['host_id'])
                                    ->where('type', 'addon_idcsmart_client_level')
                                    ->delete();

                                // 首购金额加上删除的用户等级折扣（减去负数 = 加上正数）
                                $update['first_payment_amount'] = bcsub($update['first_payment_amount'], $deletedAmount, 2);
                            }

                            // 恢复续费金额（加回用户等级的续费折扣）
                            // hh 20250226 修复：只有当优惠码支持续费时才需要恢复续费金额
                            // 如果优惠码不支持续费，续费时应该使用用户等级折扣，不需要加回
                            // 非按需，用户等级插件已启用，优惠码支持循环优惠和续费，没有被加回过
                            $renewAmountAddClientLevelDiscount = cache('renew_amount_add_client_level_discount_'.$param['order_id'].'_'.$param['host_id']) ?? false;
                            if(!empty($idcsmartClientLevelPlugin) && $host['billing_cycle'] != 'on_demand' && $PromoCodeModel['loop'] == 1 && $PromoCodeModel['renew'] == 1 && !$renewAmountAddClientLevelDiscount){
                                // 查询该用户等级对该产品的折扣百分比
                                $discountPercent = IdcsmartClientLevelClientLinkModel::alias('a')
                                    ->leftJoin('addon_idcsmart_client_level_product_group_link d', 'a.addon_idcsmart_client_level_id=d.addon_idcsmart_client_level_id')
                                    ->leftJoin('addon_idcsmart_client_level_product_link c', 'c.addon_idcsmart_client_level_product_group_id=d.addon_idcsmart_client_level_product_group_id')
                                    ->where('a.client_id', $host['client_id'])
                                    ->where('c.product_id', $host['product_id'])
                                    ->value('d.discount_percent');
                                
                                if(!empty($discountPercent)){
                                    // 根据 renew_use_current_client_level 判断基准金额
                                    if(!empty($host['renew_use_current_client_level']) && $host['renew_use_current_client_level'] == 1){
                                        // 使用 discount_renew_price 作为基准
                                        $baseAmount = $host['discount_renew_price'] ?? $host['base_renew_amount'];
                                    }else{
                                        // 使用 base_renew_amount 作为基准
                                        $baseAmount = $host['base_renew_amount'];
                                    }
                                    
                                    // 重新计算用户等级的续费折扣
                                    $clientLevelRenewDiscount = bcdiv($baseAmount * $discountPercent, 100, 2);
                                    
                                    // 恢复续费金额（加回用户等级折扣）
                                    $update['renew_amount'] = bcadd($update['renew_amount'], $clientLevelRenewDiscount, 2);

                                    // 续费金额加回了用户等级折扣，增加标识，防止重复处理
                                    cache('renew_amount_add_client_level_discount_'.$param['order_id'].'_'.$param['host_id'], true);
                                }
                            }
                        }

                        $update['first_payment_amount'] = max(0, bcsub($update['first_payment_amount'], $discount, 2));
                        // 优惠码不适用于按需，循环优惠计算下次续费金额
                        if($host['billing_cycle'] != 'on_demand' && $PromoCodeModel['loop'] == 1 && $PromoCodeModel['renew'] == 1){
                            $update['renew_amount'] = max(0, bcsub($update['renew_amount'], $resultRenew['data']['discount']??0, 2));
                        }

                        HostModel::update($update, ['id' => $param['host_id']]);
                    }
                }

                if(!empty($promoCode)){
                    # 记录使用次数
                    $this->update([
                        'used' => $PromoCodeModel->used + 1,
                        'update_time' => time()
                    ], ['id' => $PromoCodeModel->id]);

                    PromoCodeLogModel::create([
                        'addon_promo_code_id' => $PromoCodeModel->id,
                        'host_id' => $param['host_id']??0,
                        'product_id' => $param['product_id']??0,
                        'order_id' => $param['order_id']??0,
                        'client_id' => $param['client_id']??0,
                        'scene' => $param['scene']??'',
                        'amount' => $param['amount']??0,
                        'discount' => $discount,
                        'create_time' => time(),
                    ]);
                }
                
            }
        //}

        $return = [
            'discount' => $discountTotal,
            'loop' => $PromoCodeModel['loop']??0,
            'renew' => $PromoCodeModel['renew']??0,
            'order_items' => $orderItems,
            'single_user_once' => $result['data']['single_user_once']??0,
            'exclude_with_client_level' => $result['data']['exclude_with_client_level']??0,
            'delete_order_item_amount' => $deleteOrderItemAmount,
        ];

        return ['status'=>200, 'msg'=>lang_plugins('success_message'), 'data'=> $return];
    }

    /**
     * 时间 2022-10-20
     * @title 应用优惠码
     * @desc 应用优惠码,新购/续费/升降级等,可使用此接口对优惠码进行验证
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级,change_billing_cycle按需转包年包月 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID required
     * @param int param.qty - 数量 新购时必传
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return int data.id - 优惠码ID
     * @return int data.loop - 循环折扣0否1是
     * @return int data.renew - 续费优惠0否1是
     * @return int data.exclude_with_client_level - 不与用户等级同享0否1是
     */
    public function apply($param)
    {
        $discount = 0;

        $results = [];

        $param['qty'] = $param['qty']??1;

        if($param['scene']!='upgrade'){
            $OrderItemModel = new OrderItemModel();
            $promoCode = $OrderItemModel->alias('oi')
                    ->leftJoin('addon_promo_code pc','pc.id=oi.rel_id')
                    ->leftJoin('order o','oi.order_id=o.id')
                    ->where('o.status','Paid') // wyh 20240625 修改 必须是已支付订单
                    //->where('oi.order_id',$host->order_id)
                    ->where('oi.host_id',$param['host_id'] ?? 0)
                    ->where('oi.type','addon_promo_code')
                    ->value('code')??'';

            $param['promo_code'] = (isset($param['promo_code']) && !empty($param['promo_code'])) ?$param['promo_code']: $promoCode;
        }

        for ($i=0;$i<$param['qty'];$i++){
            $post = [
                'promo_code' => $param['promo_code'],
                'scene' => $param['scene'],
                'host_id' => $param['host_id'] ?? 0,
                'product_id' => $param['product_id'],
                'amount' => $param['amount'],
                'billing_cycle_time' => $param['billing_cycle_time'],
                'qty' => $param['qty']
            ];
            $result = $this->clientPromoCodeSingleHandle($post);

            $results[] = $result;

            if ($result['status'] == 200){
                // wyh 20240223 增加单用户一次限制
                if ($result['data']['single_user_once']==0 || $i==0){
                    $discount = bcadd($discount,$result['data']['discount']??0,2);
                }
                $id = $result['data']['id']??0;
                $loop = $result['data']['loop']??0;
                $renew = $result['data']['renew']??0;
                $excludeWithClientLevel = $result['data']['exclude_with_client_level']??0;
            }
        }
        # 所有结果都为400,返回400
        if (!in_array(200,array_column($results,'status'))){
            if(!empty($param['promo_code'])){
                return ['status'=>400,'msg'=>$results[0]['msg']?:lang_plugins('fail_message')];
            }else{
                $discount = 0;
            }
        }

        $data = [
            'discount' => $discount,
            'id' => $id??0,
            'loop' => $loop??0,
            'renew' => $renew??0,
            'exclude_with_client_level' => $excludeWithClientLevel??0
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    public function applyBatch($params)
    {
        $promoCodes = $params['promo_codes']??[];
        $discount = 0;
        $processedPromoCodes = []; // 记录已处理的优惠码
        $skippedCodes = []; // 记录被跳过的重复优惠码
        $hostDiscount = [];
        foreach ($promoCodes as $param){
            $promoCode = $param['promo_code']??'';
        }
        if (!empty($promoCode)){
            $tmp = $this->where('code',$promoCode)->find();
            $used = $tmp['used']??0;
            $maxTimes = $tmp['max_times']??0;
        }else{
            $used = 0;
            $maxTimes = 0;
        }
        foreach ($promoCodes as $param){
            $promoCode = $param['promo_code']??'';
            
            // 如果优惠码为空，跳过
            if (empty($promoCode)) {
                continue;
            }

            $post = [
                'promo_code' => $promoCode,
                'scene' => 'renew',
                'host_id' => $param['host_id'] ?? 0,
                'product_id' => $param['product_id']??0,
                'amount' => $param['amount']??0,
                'billing_cycle_time' => $param['billing_cycle_time']??0,
                'qty' => 1
            ];
            
            $result = $this->clientPromoCodeSingleHandle($post);
            if ($result['status'] == 200){
                if (!empty($maxTimes)){
                    if ($used >= $maxTimes){
                        continue;
                    }
                }
                // 检查是否有单用户一次限制
                $singleUserOnce = $result['data']['single_user_once'] ?? 0;
                
                if ($singleUserOnce == 1) {
                    // 有单用户一次限制，检查是否已经处理过
                    if (in_array($promoCode, $processedPromoCodes)) {
                        // 已经处理过，跳过并记录
                        $skippedCodes[] = $promoCode;
                        continue;
                    }
                    // 记录已处理的优惠码
                    $processedPromoCodes[] = $promoCode;
                }
                $used++;
                // 累加折扣
                $discount = bcadd($discount, $result['data']['discount']??0, 2);
                $hostDiscount[$param['host_id']] = bcsub($result['data']['discount']??0,0,2);
            }
        }
        
        $data = [
            'discount' => $discount,
            'host_discount' => $hostDiscount,
            'processed_codes' => $processedPromoCodes, // 返回已处理的优惠码列表
            'skipped_codes' => $skippedCodes, // 返回被跳过的重复优惠码列表
        ];
        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-10-20
     * @title 处理单个优惠码
     * @desc 处理单个优惠码
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级,change_billing_cycle按需转包年包月 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID required
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return float data.discount2 - 续费折扣金额
     * @return int data.id - 优惠码ID
     * @return int data.loop - 循环折扣0否1是
     * @return int data.renew - 续费优惠0否1是
     * @return int data.exclude_with_client_level - 不与用户等级同享0否1是
     */
    private function clientPromoCodeSingleHandle($param)
    {
        # 判断优惠码
        $param['promo_code'] = $param['promo_code'] ?? '';
        if (empty($param['promo_code']) && !isset($param['host_id'])){
            return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
        }else if(empty($param['promo_code']) && isset($param['host_id'])){
            if($param['scene']=='new'){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            if($param['scene']=='renew'){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            $changeBillingCycleLog = PromoCodeLogModel::where('host_id', $param['host_id'])->where('scene', 'change_billing_cycle')->order('id', 'desc')->find();
            if(!empty($changeBillingCycleLog)){
                $log = $changeBillingCycleLog;
            }else{
                $log = PromoCodeLogModel::where('host_id', $param['host_id'])->where('scene', 'new')->find();
            }
            if(!empty($log)){
                $promoCode = $this->where('id',$log['addon_promo_code_id'])->where('delete_time',0)->find();
                if(!empty($promoCode)){
                    $promoCode = $promoCode->toArray();
                    if($promoCode['type']=='fixed_amount' || $promoCode['type']=='replace_price'){
                        $promoCode['upgrade'] = 0;
                        $promoCode['host_upgrade'] = 0;
                        $promoCode['renew'] = 0;
                        $promoCode['loop'] = 0;
                    }else if($promoCode['type']=='free'){
                        $promoCode['host_upgrade'] = 0;
                        $promoCode['loop'] = 0;
                    }
                    if($param['scene']=='upgrade' && $promoCode['host_upgrade']!=1){
                        return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                    }/*else if($param['scene']=='renew' && $promoCode['loop']!=1){
                        return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                    }*/
                }else{
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                }
            }
            
            else{
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
        }else{
            $promoCode = $this->where('code',$param['promo_code'])->where('delete_time',0)->find();
            if (empty($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            } 
            $promoCode = $promoCode->toArray();
            if($promoCode['type']=='fixed_amount' || $promoCode['type']=='replace_price'){
                $promoCode['upgrade'] = 0;
                $promoCode['host_upgrade'] = 0;
                $promoCode['renew'] = 0;
                $promoCode['loop'] = 0;
            }else if($promoCode['type']=='free'){
                $promoCode['host_upgrade'] = 0;
                $promoCode['loop'] = 0;
            }
        }

        $clientId = isset($param['client_id'])?intval($param['client_id']):get_client_id();

        $time = time();

        $amount = floatval($param['amount']);

        // wyh 20231219增 升降级时，未填写优惠码，判断不到优惠码使用范围
        if ($param['scene']=='upgrade' && !empty($promoCode)){
            $param['promo_code'] = $promoCode['code']??'';
        }

        if(!empty($param['promo_code'])){
            if ($promoCode['status'] == 0){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            }

            # 优惠码有效时间
            if ($promoCode['start_time'] > $time){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            if ($promoCode['end_time']>0 && $promoCode['end_time'] < $time){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            }

            if(isset($param['order_id'])){
                $host = HostModel::where('client_id', $clientId)->where('order_id', '<>', $param['order_id'])->column('product_id');
            }else{
                $host = HostModel::where('client_id', $clientId)->column('product_id');
            }

            # 优惠码适用客户
            if ($promoCode['client_type'] == 'new' && !empty($host)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_only_new_client')];
            }

            $active = HostModel::where('client_id', $clientId)->where('status', 'Active')->column('product_id');

            if ($promoCode['client_type'] == 'old' && empty($active)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_only_old_client')];
            }

            $count = PromoCodeLogModel::where('client_id', $clientId)->where('addon_promo_code_id', $promoCode['id'])->count();
            if($count>0 && $promoCode['single_user_once']==1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            } 

            if ($promoCode['client_type'] == 'not_have_client_level'){
                $PluginModel = new PluginModel();
                $addons = $PluginModel->plugins('addon');
                $addons = array_column($addons['list'], 'name');
                if(in_array('IdcsmartClientLevel', $addons)){
                    $IdcsmartClientLevelClientLinkModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel();
                    $clientLevelClientLink = $IdcsmartClientLevelClientLinkModel->where('client_id', $clientId)->find();
                    if(!empty($clientLevelClientLink)){
                        $promoCode['client_level'] = array_filter(explode(',', $promoCode['client_level']));
                        if(empty($promoCode['client_level']) || in_array($clientLevelClientLink['addon_idcsmart_client_level_id'], $promoCode['client_level'])){
                            return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_only_not_have_client_level_client')];
                        }
                    }
                }
            }

            # 优惠码使用次数
            if (!empty($promoCode['max_times'])){
                if ($promoCode['used'] >= $promoCode['max_times']){
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
                }
            }

            # 判断适用场景
            $scene = $param['scene'];
            if($scene=='upgrade' && $promoCode['upgrade']!=1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_upgrade_cannot_use')];
            }else if($scene=='renew' && $promoCode['renew']!=1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_renew_cannot_use')];
            }else if($scene == 'change_billing_cycle' && $promoCode['on_demand_to_recurring_prepayment']!=1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_on_demand_to_recurring_prepayment_cannot_use') ];
            }

            # 判断流量包订单是否可使用
            if(isset($param['order_item_type']) && $param['order_item_type'] == 'addon_idcsmart_flow_packet' && $promoCode['flow_packet'] != 1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_flow_packet_cannot_use')];
            }

            # 判断适用场景时长(新规则)
            $billingCycleTime = intval($param['billing_cycle_time']);
            $cycle = explode(',',$promoCode['cycle']) ?: [];
            if ($promoCode['cycle_limit']==1){
                # 排除最大值
                $flag = false;
                foreach ($cycle as $v){
                    if(isset($this->applySceneTime[$v])){
                        if($this->applySceneTime[$v]['min']<=$billingCycleTime && $billingCycleTime<=$this->applySceneTime[$v]['max']){
                            $flag = true;
                            break;
                        }
                    }
                }

                if (!$flag){
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_the_condition_cannot_use')];
                }
            }

            # 判断商品适用
            $productIds = PromoCodeProductModel::where('addon_promo_code_id',$promoCode['id'])->column('product_id');
            if (!empty($productIds) && !in_array($param['product_id'],$productIds)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_product_cannot_use')];
            }



            $needProductIds = PromoCodeProductNeedModel::where('addon_promo_code_id',$promoCode['id'])->column('product_id');
            if (!empty($needProductIds) && empty(array_intersect($active,$needProductIds))){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_the_condition_cannot_use')];
            }
        }

        if ($promoCode['type'] == 'replace_price' && $promoCode['value']>$amount){
            return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_higher_cannot_use')];
        }

        if ($promoCode['type'] == 'percent'){ # 百分比
            $discount = bcdiv($amount*$promoCode['value'], 100, 2);
        }else if ($promoCode['type'] == 'fixed_amount'){ # 固定金额
            $discount = $promoCode['value']>$amount ? $amount : $promoCode['value'];
        }else if ($promoCode['type'] == 'replace_price'){ # 替换价格
            $discount = bcsub($amount, $promoCode['value'], 2)>0 ? bcsub($amount, $promoCode['value'], 2) : 0;
        }else if ($promoCode['type'] == 'free'){ # 免费
            $discount = $amount;
        }

        if(isset($param['host_id']) && !empty($param['host_id']) && $param['scene']=='new'){
            $host = HostModel::find($param['host_id']);

            // wyh 20230509 在原来基础之上优惠
            $OrderItemModel = new OrderItemModel();
            $orderItem = $OrderItemModel->where('host_id',$param['host_id'])
                ->where('type','host')
                ->where('rel_id',$param['host_id'])
                ->find();
            $host['renew_amount'] = $orderItem['amount'];

            if(!empty($host) && $promoCode['loop']==1){
                if ($promoCode['type'] == 'percent'){ # 百分比
                    $discount2 = bcdiv($host['renew_amount']*$promoCode['value'], 100, 2);
                }else if ($promoCode['type'] == 'fixed_amount'){ # 固定金额
                    $discount2 = $promoCode['value']>$host['renew_amount'] ? $host['renew_amount'] : $promoCode['value'];
                }else if ($promoCode['type'] == 'replace_price'){ # 替换价格
                    $discount2 = bcsub($host['renew_amount'], $promoCode['value'], 2)>0 ? bcsub($host['renew_amount'], $promoCode['value'], 2) : 0;
                }else if ($promoCode['type'] == 'free'){ # 免费
                    $discount2 = $host['renew_amount'];
                }
            }
        }

        // 20230601 加
        //$discount = $discount<0?-$discount:$discount;

        $data = [
            'id' => $promoCode['id'], # 优惠码ID
            'discount' => $discount, # 优惠金额
            'discount2' => $discount2 ?? 0, # 续费优惠金额
            'loop' => $promoCode['loop'],
            'renew' => $promoCode['renew'],
            'single_user_once' => $promoCode['single_user_once'],
            'exclude_with_client_level' => $promoCode['exclude_with_client_level']??0
        ];

        return ['status'=>200,'msg'=>lang_plugins('promo_code_apply_success'),'data'=>$data];
    }

    /**
     * 时间 2022-10-20
     * @title 订单创建后
     * @desc 订单创建后
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID
     * @param object param.customfield - 自定义字段
     * @param int param.customfield.promo_code - 优惠码
     * @param array param.customfield.host_customfield - 产品自定义字段
     * @param int param.customfield.host_customfield[].id - 产品ID
     * @param int param.customfield.host_customfield[].customfield.promo_code - 优惠码
     * @return bool
     */
    public function afterOrderCreate($param)
    {
        $orderId = $param['id']??0;

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($orderId);
        if (empty($order)){
            return false;
        }

        $promoCode = $promoCodeParam = $param['customfield']['promo_code']??'';
        $promoCode = is_string($promoCode) ? $promoCode : '';

        $hostPosition = $param['customfield']['host_customfield']??[];
        if(is_array($hostPosition) && !empty($hostPosition)){
            $hostPromoCode = [];
            foreach ($hostPosition as $key => $value) {
                $hostPromoCode[$value['id']] = $value['customfield']['promo_code'] ?? '';
            }
            $promoCode = '';
        }

        /*if (empty($promoCode)){
            return false;
        }*/

        $OrderItemModel = new OrderItemModel();

        $orderItems = $OrderItemModel->alias('oi')
            ->field('oi.client_id,oi.order_id,oi.host_id,oi.amount,oi.product_id,h.billing_cycle_time,oi.type,oi.rel_id')
            ->leftJoin('host h','h.id=oi.host_id AND h.is_delete=0')
            ->where('oi.order_id',$orderId)
            ->whereIn('oi.type',['host','renew','upgrade','change_billing_cycle','addon_idcsmart_flow_packet']) # 新增addon_idcsmart_flow_packet类型用于流量包等
            ->select()
            ->toArray();
        if (empty($orderItems)){
            return false;
        }
        $items = [];

        $discountTotal = 0;
        $deleteOrderItemAmountTotal = 0;
        $usePromoCodeArr = [];
        foreach ($orderItems as $orderItem){
            $productId = $orderItem['product_id'];
            $promoCodeExist = $OrderItemModel->alias('oi')
                    ->leftJoin('addon_promo_code pc','pc.id=oi.rel_id')
                    //->where('oi.order_id',$host->order_id)
                    ->where('oi.host_id',$orderItem['host_id'])
                    ->where('oi.type','addon_promo_code')
                    ->value('code')??'';
            if($orderItem['type']!='upgrade'){
                $promoCode = !empty($promoCode)?$promoCode:$promoCodeExist;
            }
            
            // wyh 20230509 取续费金额的优惠码按周期原价来算
            if ($orderItem['type']=='renew'){
                $HostModel = new HostModel();
                $host = $HostModel->find($orderItem['host_id']);
                $ModuleLogic = new ModuleLogic();
                $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
                if($upstreamProduct){
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->durationPrice($host);
                }else{
                    $result = $ModuleLogic->durationPrice($host);
                }
                $cycles = $result['data']??[];
                $IdcsmartRenewModel = new IdcsmartRenewModel();
                $renew = $IdcsmartRenewModel->find($orderItem['rel_id']);
                # 获取金额
                if (is_array($cycles)){
                    foreach ($cycles as $value){
                        if ($renew['new_billing_cycle'] == $value['billing_cycle']){
                            $amount = !empty($upstreamProduct)?($value['base_price']??$value['renew_amount']??$value['price']):$value['price'];
                            break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                        }
                    }
                }
                $orderItem['amount'] = $amount??0;
            }
            // TODO 20231101升降级产品按原价计算
            if ($orderItem['type']=='upgrade'){
                $UpgradeModel = new UpgradeModel();
                $upgradeProduct = $UpgradeModel->where('id',$orderItem['rel_id'])->where('type','product')->find();
                if (!empty($upgradeProduct)){
                    $orderItem['amount'] = $upgradeProduct['price'];
                }
            }
            
            $data = [
                'order_id'=>$orderItem['order_id'],
                'client_id'=>$orderItem['client_id'],
                'host_id'=>$orderItem['host_id'],
                'product_id'=>$productId,
                'scene'=>$order['type'], # 订单类型
                'promo_code'=>$promoCode,
                'amount'=>$orderItem['amount'], # 单个产品金额
                'billing_cycle_time'=>intval($param['customfield']['renew_due_time_array'][$orderItem['host_id']]??$param['customfield']['renew_due_time']??$orderItem['billing_cycle_time']),
                'order_item_type'=>$orderItem['type']??'', # 订单子项类型，用于流量包等判断
            ];
            $data['promo_code'] = $hostPromoCode[$orderItem['host_id']] ?? $promoCode;

            $result = $this->clientPromoCode($data);
            if ($result['status']==200){
                $deleteOrderItemAmountTotal = bcadd($deleteOrderItemAmountTotal, $result['data']['delete_order_item_amount'], 2);

                $tmp = $this->where('code',$data['promo_code'])->find();
                if (!empty($tmp['single_user_once']) && in_array($data['promo_code'],$usePromoCodeArr)){

                }else{
                    // wyh 20240313 修改 传了优惠码  || 续费时更换了周期，且旧优惠码可循环 || 流量包等artificial类型订单
                    if (($promoCodeExist && $orderItem['type']=='upgrade') || (!empty($promoCodeParam) || !empty($hostPromoCode[$orderItem['host_id']]??"")) || ($promoCodeExist && isset($result['data']['loop']) && $result['data']['loop']==1 && $orderItem['type']=='renew' && $host['billing_cycle_name']!=$renew['new_billing_cycle']) || ($orderItem['type']=='addon_idcsmart_flow_packet' && !empty($promoCodeParam) )){
                        $discount = $result['data']['discount'];
                        if ($orderItem['type']!='renew'){
                            $discountTotal = bcadd($discountTotal,$discount,2);
                        }
                        
                        // hh 20260227 续费场景：如果优惠码互斥，删除已添加的用户等级折扣子项
                        // hh 20260227 升降级场景/按需转包年包月：如果优惠码互斥，删除已添加的用户等级折扣子项，并加回删除的金额
                        if (in_array($orderItem['type'], ['renew', 'upgrade', 'change_billing_cycle']) && !empty($result['data']['exclude_with_client_level']) && $result['data']['exclude_with_client_level'] == 1){
                            // 升降级场景需要先获取要删除的用户等级子项金额
                            if ($orderItem['type'] == 'upgrade' || $orderItem['type'] == 'change_billing_cycle'){
                                $deletedClientLevelItem = $OrderItemModel->where('order_id', $orderId)
                                    ->where('host_id', $orderItem['host_id'])
                                    ->where('type', 'addon_idcsmart_client_level')
                                    ->find();
                                if (!empty($deletedClientLevelItem)){
                                    // 用户等级折扣是负数，删除后需要加回到订单总额
                                    $deleteOrderItemAmountTotal = bcadd($deleteOrderItemAmountTotal, $deletedClientLevelItem['amount'], 2);
                                }
                            }
                            
                            $OrderItemModel->where('order_id', $orderId)
                                ->where('host_id', $orderItem['host_id'])
                                ->where('type', 'addon_idcsmart_client_level')
                                ->delete();
                        }
                        
                        foreach ($result['data']['order_items'] as $item){
                            $item['order_id'] = $orderId;
                            $item['client_id'] = $orderItem['client_id'];
                            $item['create_time'] = time();
                            $items[] = $item;
                        }
                        $usePromoCodeArr[] = $data['promo_code'];
                    }
                }
            }
        }
        if(!empty($items)){
            $OrderItemModel->insertAll($items);

            // 订单金额减去删除子项金额
            $order['amount'] = bcsub($order['amount'], $deleteOrderItemAmountTotal, 2);

            $amount = bcsub($order['amount'],$discountTotal,2)>0?bcsub($order['amount'],$discountTotal,2):0;
            $order->save([
                'amount' => $amount,
                'status' => $amount>0 ? 'Unpaid' :'Paid', # 金额为0,修改为已支付状态
                'amount_unpaid' => $amount,
                'pay_time' => $amount>0 ? 0 : time(),
            ]);

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($order['client_id']);
            if(is_array($promoCode) && !empty($promoCode)){
                $code = $this->where('code',$promoCode[0])->find();
                active_log(lang_plugins('promo_code_client_use_promo_code',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{promo_code}'=>implode(',',$promoCode),'{order_id}'=>$orderId]),'promo_code',$code->id);
            }else if(!empty($promoCode)){
                $code = $this->where('code',$promoCode)->find();
                active_log(lang_plugins('promo_code_client_use_promo_code',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{promo_code}'=>$promoCode,'{order_id}'=>$orderId]),'promo_code',$code->id);
            }
        }

        return true;
    }

    /**
     * 时间 2022-10-20
     * @title 每日定时任务
     * @desc 每日定时任务,启用/停用优惠码
     * @author theworld
     * @version v1
     * @return bool
     */
    public function dailyCron()
    {
        # 1、启用/停用优惠码
        $promoCodes = $this->select()
            ->toArray();
        $time = time();
        foreach ($promoCodes as $promoCode){
            if(!empty($promoCode['end_time'])){
                $status = $promoCode['status'];
                if ($status==0){
                    continue;
                }
                if ($promoCode['start_time']<=$time){
                    $status = 1;
                }
                if ($promoCode['end_time']<=$time){
                    $status = 0;
                }
                if ($promoCode['status'] != $status){
                    $this->update([
                        'status' => $status,
                        'update_time' => $time
                    ],['id'=>$promoCode['id']]);
                }
            }
        }
        return true;
    }

    /**
     * 时间 2022-10-20
     * @title 产品内页获取优惠码信息
     * @desc 产品内页获取优惠码信息
     * @author theworld
     * @version v1
     * @param int param.id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return array data.promo_code - 优惠码
     */
    public function hostPromoCode($param)
    {
        $id = $param['id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->where('id',$id)->where('client_id',get_client_id())->find();

        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_host_is_not_exist')];
        }

        $OrderItemModel = new OrderItemModel();
        $relIds = $OrderItemModel->where('host_id',$id)
            ->where('type',$this->name)
            ->column('rel_id');

        $promoCodes = $this->whereIn('id',$relIds)
            ->column('code');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['promo_code'=>$promoCodes??[]]];
    }
}

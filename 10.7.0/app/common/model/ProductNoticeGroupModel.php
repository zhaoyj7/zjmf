<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\facade\Db;
use think\Model;

/**
 * @title 商品通知组模型
 * @desc  商品通知组模型
 * @use app\common\model\ProductNoticeGroupModel
 */
class ProductNoticeGroupModel extends Model
{
    protected $name = 'product_notice_group';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'type'          => 'string',
        'is_default'    => 'int',
        'notice_setting'=> 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * @时间 2025-03-07
     * @title 全局通知管理列表
     * @desc  全局通知管理列表
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   string param.type - 通知类型标识 require
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.name - 搜索:分组名称
     * @param   string param.product_name - 搜索:商品名称
     * @return  int list[].id - 触发动作组ID
     * @return  string list[].name - 触发动作组名称
     * @return  int list[].is_default - 是否默认组(0=否,1=是)
     * @return  int list[].notice_setting.xxx - 对应触发动作状态(xxx=动作标识,0=关闭,1=开启)
     * @return  int list[].product[].id - 商品ID
     * @return  string list[].product[].name - 商品名称
     * @return  int count - 总条数
     * @return  string notice_type[].type - 通知类型标识
     * @return  string notice_type[].name - 通知类型名称
     * @return  string notice_setting[].name - 通知标识
     * @return  string notice_setting[].name_lang - 通知名称
     */
    public function productNoticeGroupList($param): array
    {
        $ProductModel = new ProductModel();
        $param['type'] = $param['type'] ?? '';

        $where = function($query)use ($param, $ProductModel) {
            $query->where('png.type', '=', $param['type']);
            if(isset($param['name']) && $param['name'] !== ''){
                $query->where('png.name', 'LIKE', '%'.$param['name'].'%');
            }
            if(isset($param['product_name']) && $param['product_name'] !== ''){
                $productId = $ProductModel
                            ->where('name', 'LIKE', '%'.$param['product_name'].'%')
                            ->column('id');
                if(!empty($productId)){
                    // 获取组ID
                    $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
                    $productNoticeGroupId = $ProductNoticeGroupProductModel
                            ->whereIn('product_id', $productId)
                            ->where('type', $param['type'])
                            ->column('product_notice_group_id');
                    if(!empty($productNoticeGroupId)){
                        $query->whereIn('png.id', $productNoticeGroupId);
                    }else{
                        $query->where('png.id', '=', 0);
                    }
                }else{
                    $query->where('png.id', '=', 0);
                }
            }
        };

        $list = $this
            ->alias('png')
            ->field('png.id,png.name,png.is_default,png.notice_setting')
            ->where($where)
            ->orderRaw('png.is_default DESC,png.id ASC')
            ->page($param['page'], $param['limit'])
            ->select()
            ->toArray();
        if(!empty($list)){
            $id = array_column($list, 'id');

            $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
            // 获取商品
            $product = $ProductNoticeGroupProductModel
                    ->alias('pngp')
                    ->field('p.id,p.name,pngp.product_notice_group_id')
                    ->join('product p', 'pngp.product_id=p.id')
                    ->whereIn('pngp.product_notice_group_id', $id)
                    ->select();
            $productArr = [];
            foreach($product as $v){
                $productNoticeGroupId = (int)$v['product_notice_group_id'];
                unset($v['product_notice_group_id']);
                $productArr[$productNoticeGroupId][] = $v;
            }
            unset($product);

            foreach($list as $k=>$v){
                $list[$k]['product'] = $productArr[$v['id']] ?? [];
                $list[$k]['notice_setting'] = json_decode($v['notice_setting'], true);
            }
        }
        
        $count = $this
            ->alias('png')
            ->where($where)
            ->count();

        // 获取所有通知类型
        $noticeType = $this->allNoticeType();
        // 获取商品动作
        $productNoticeSetting = $this->productNoticeSetting();

        return ['list'=>$list, 'count'=>$count, 'notice_type'=>$noticeType, 'notice_setting'=>$productNoticeSetting ];
    }

    /**
     * @时间 2025-03-07
     * @title 创建触发动作组
     * @desc  创建触发动作组
     * @author hh
     * @version v1
     * @param   string param.type - 通知类型标识 require
     * @param   string param.name - 触发动作组名称 require
     * @param   int param.notice_setting.xxx - 对应触发动作状态(xxx=动作标识,0=关闭,1=开启) require
     * @param   array param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 触发动作组ID
     */
    public function productNoticeGroupCreate(array $param): array
    {
        // 验证类型
        $defaultGroupId = $this
                ->where('type', $param['type'])
                ->where('is_default', 1)
                ->value('id');
        if(empty($defaultGroupId)){
            return ['status'=>400, 'msg'=>lang('product_notice_group_not_support_type') ];
        }
        // 验证商品
        if(!empty($param['product_id'])){
            $ProductModel = new ProductModel();
            $productId = $ProductModel
                    ->whereIn('id', $param['product_id'])
                    ->column('id');
            if(count($productId) != count($param['product_id'])){
                return ['status'=>400, 'msg'=>lang('product_not_found')];
            }
        }
        $noticeSetting = [];
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        foreach($productNoticeGroupAction as $v){
            $noticeSetting[$v] = isset($param['notice_setting'][$v]) ? (int)$param['notice_setting'][$v] : 0;
        }
        $this->startTrans();
        try{
            $productNoticeGroup = $this->create([
                'name'          => $param['name'],
                'type'          => $param['type'],
                'is_default'    => 0,
                'notice_setting'=> json_encode($noticeSetting),
                'create_time'   => time(),
            ]);

            if(!empty($productId)){
                $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
                $ProductNoticeGroupProductModel
                ->where('product_id', 'IN', $productId)
                ->where('type', $param['type'])
                ->update([
                    'product_notice_group_id'   => $productNoticeGroup->id,
                ]);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $description = lang('log_product_notice_group_create_success', [
            '{type}'    => $this->getNoticeTypeName($param['type']),
            '{name}'    => $param['name'],
        ]);
        active_log($description, 'product_notice_group', $productNoticeGroup->id);

        $result = [
            'status' => 200,
            'msg'    => lang('create_success'),
            'data'   => [
                'id' => (int)$productNoticeGroup->id,
            ],
        ];
        return $result;
    }

    /**
     * @时间 2025-03-07
     * @title 修改触发动作组
     * @desc  修改触发动作组
     * @author hh
     * @version v1
     * @param   int param.id - 触发动作组ID require
     * @param   string param.name - 触发动作组名称 require
     * @param   int param.notice_setting.xxx - 对应触发动作状态(xxx=动作标识,0=关闭,1=开启) require
     * @param   array param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function productNoticeGroupUpdate(array $param): array
    {
        $productNoticeGroup = $this->find($param['id']);
        if(empty($productNoticeGroup)){
            return ['status'=>400, 'msg'=>lang('product_notice_group_not_found') ];
        }
        if($productNoticeGroup['is_default'] == 1){
            $param['name'] = $productNoticeGroup['name'];
        }
        $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
        $loseProductNoticeGroupProduct = [];
        // 验证商品
        if(!empty($param['product_id'])){
            $ProductModel = new ProductModel();
            $productId = $ProductModel
                    ->whereIn('id', $param['product_id'])
                    ->column('id');
            if(count($productId) != count($param['product_id'])){
                return ['status'=>400, 'msg'=>lang('product_not_found')];
            }
            // 所选商品对应类型是否存在
            $nowProductId = $ProductNoticeGroupProductModel
                    ->where('type', $productNoticeGroup['type'])
                    ->whereIn('product_id', $productId)
                    ->column('product_id');
            $loseProductNoticeGroupProductId = array_diff($productId, $nowProductId);
            foreach($loseProductNoticeGroupProductId as $v){
                $loseProductNoticeGroupProduct[] = [
                    'product_notice_group_id' => $productNoticeGroup['id'],
                    'type'                    => $productNoticeGroup['type'],
                    'product_id'              => $v,
                ];
            }
        }
        $noticeSetting = [];
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        foreach($productNoticeGroupAction as $v){
            $noticeSetting[$v] = isset($param['notice_setting'][$v]) ? (int)$param['notice_setting'][$v] : 0;
        }

        $defaultGroupId = $this
                        ->where('type', $productNoticeGroup['type'])
                        ->where('is_default', 1)
                        ->value('id');

        $this->startTrans();
        try{
            $this->where('id', $productNoticeGroup->id)->update([
                'name'          => $param['name'],
                'notice_setting'=> json_encode($noticeSetting),
                'update_time'   => time(),
            ]);

            // 先移至默认组
            $ProductNoticeGroupProductModel
            ->where('product_notice_group_id', '=', $productNoticeGroup->id)
            ->update([
                'product_notice_group_id'   => $defaultGroupId,
            ]);
            if(!empty($productId)){
                $ProductNoticeGroupProductModel
                ->where('product_id', 'IN', $productId)
                ->where('type', $productNoticeGroup['type'])
                ->update([
                    'product_notice_group_id'   => $productNoticeGroup->id,
                ]);
            }
            if(!empty($loseProductNoticeGroupProduct)){
                $ProductNoticeGroupProductModel->insertAll($loseProductNoticeGroupProduct);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $description = lang('log_product_notice_group_update_success', [
            '{type}'    => $this->getNoticeTypeName($productNoticeGroup['type']),
            '{name}'    => $productNoticeGroup['name'],
        ]);
        active_log($description, 'product_notice_group', $productNoticeGroup->id);
        return ['status'=>200, 'msg'=>lang('update_success') ];
    }

    /**
     * @时间 2025-03-07
     * @title 删除触发动作组
     * @desc  删除触发动作组
     * @author hh
     * @version v1
     * @param   int param.id - 触发动作组ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function productNoticeGroupDelete($param): array
    {
        $productNoticeGroup = $this->find($param['id']);
        if(empty($productNoticeGroup)){
            return ['status'=>400, 'msg'=>lang('product_notice_group_not_found') ];
        }
        if($productNoticeGroup['is_default'] == 1){
            return ['status'=>400, 'msg'=>lang('product_notice_group_default_cannot_delete') ];
        }
        $defaultGroupId = $this
                ->where('type', $productNoticeGroup['type'])
                ->where('is_default', 1)
                ->value('id');
        
        $this->startTrans();
        try{
            $productNoticeGroup->delete();

            $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
            $ProductNoticeGroupProductModel
            ->where('product_notice_group_id', $productNoticeGroup->id)
            ->update([
                'product_notice_group_id'   => $defaultGroupId,
            ]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $description = lang('log_product_notice_group_delete_success', [
            '{type}'    => $this->getNoticeTypeName($productNoticeGroup['type']),
            '{name}'    => $productNoticeGroup['name'],
        ]);
        active_log($description, 'product_notice_group', $productNoticeGroup->id);

        return ['status'=>200, 'msg'=>lang('delete_success') ];
    }

    /**
     * @时间 2025-03-07
     * @title 修改触发动作组动作状态
     * @desc  修改触发动作组动作状态
     * @author hh
     * @version v1
     * @param   int param.id - 触发动作组ID require
     * @param   string param.act - 动作标识 require
     * @param   int param.status - 状态(0=否,1=是) require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function productNoticeGroupUpdateActStatus(array $param): array
    {
        $productNoticeGroup = $this->find($param['id']);
        if(empty($productNoticeGroup)){
            return ['status'=>400, 'msg'=>lang('product_notice_group_not_found') ];
        }
        
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        $noticeSetting = json_decode($productNoticeGroup['notice_setting'], true);
        foreach($productNoticeGroupAction as $v){
            if($param['act'] == $v){
                $noticeSetting[$v] = (int)$param['status'];
                break;
            }
        }

        $this->where('id', $productNoticeGroup->id)->update([
            'notice_setting'=> json_encode($noticeSetting),
            'update_time'   => time(),
        ]);

        // 活动通知动作名称
        $NoticeSettingModel = new NoticeSettingModel();
        $noticeSetting = $NoticeSettingModel
                        ->where('name', $param['act'])
                        ->find();
        if(empty($noticeSetting['name_lang'])){
            $noticeSetting['name_lang'] = lang('notice_action_'.$param['act']);
        }

        $description = lang('log_product_notice_group_update_act_success', [
            '{type}'    => $this->getNoticeTypeName($productNoticeGroup['type']),
            '{name}'    => $productNoticeGroup['name'],
            '{act}'     => $noticeSetting['name_lang'],
            '{status}'  => lang('switch_'.$param['status']),
        ]);
        active_log($description, 'product_notice_group', $productNoticeGroup->id);
        return ['status'=>200, 'msg'=>lang('update_success') ];
    }

    /**
     * @时间 2025-03-07
     * @title 添加商品至默认分组
     * @desc  添加商品至默认分组
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     */
    public function addProductToDefaultProductNoticeGroup($productId): void
    {
        $productNoticeGroup = $this
                ->field('id,type')
                ->where('is_default', 1)
                ->select();
        $data = [];
        foreach($productNoticeGroup as $v){
            $data[] = [
                'product_notice_group_id'   => $v['id'],
                'type'                      => $v['type'],
                'product_id'                => $productId,
            ];
        }
        if(!empty($data)){
            $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
            $ProductNoticeGroupProductModel->insertAll($data);
        }
    }

    /**
     * @时间 2025-03-10
     * @title 删除商品关联
     * @desc  删除商品关联,调用该方法删除对应关联
     * @author hh
     * @version v1
     * @param   int productId - 商品ID require
     */
    public function afterProductDelete($productId)
    {
        $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
        $ProductNoticeGroupProductModel->where('product_id', $productId)->delete();
    }

    /**
     * @时间 2025-03-07
     * @title 添加新通知类型
     * @desc  添加新通知类型,调用该方法自动添加对应关联
     * @author hh
     * @version v1
     * @param   string type - 通知类型标识 require
     * @return  int
     */
    public function addNoticeType($type): bool
    {
        $exist = $this
                ->where('type', $type)
                ->where('is_default', 1)
                ->find();
        if(!empty($exist)){
            return 0;
        }
        $ProductModel = new ProductModel();
        $product = $ProductModel
                ->field('id')
                ->select();
        
        $noticeSetting = '{"host_pending":0,"host_active":0,"host_suspend":0,"host_unsuspend":0,"host_terminate":0,"host_upgrad":0,"host_module_action":0,"client_create_refund":0,"client_refund_success":0,"admin_refund_reject":0,"client_refund_cancel":0}';

        $this->startTrans();
        try{
            $productNoticeGroup = $this->create([
                'name'          => '默认分组',
                'type'          => $type,
                'is_default'    => 1,
                'notice_setting'=> $noticeSetting,
                'create_time'   => time(),
            ]);

            $data = [];
            foreach($product as $v){
                $data[] = [
                    'product_notice_group_id'   => $productNoticeGroup->id,
                    'type'                      => $type,
                    'product_id'                => $v['id'],
                ];
            }
            if(!empty($data)){
                $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
                $ProductNoticeGroupProductModel->insertAll($data);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return 0;
        }
        return (int)$productNoticeGroup->id;
    }

    /**
     * @时间 2025-03-07
     * @title 删除通知类型
     * @desc  删除通知类型,不能删除邮件/短信
     * @author hh
     * @version v1
     * @param   string type - 通知类型标识 require
     */
    public function delNoticeType($type)
    {
        $this->where('type', $type)->delete();
        ProductNoticeGroupProductModel::where('type', $type)->delete();
    }

    /**
     * @时间 2025-03-07
     * @title 获取所有通知类型
     * @desc  获取所有通知类型
     * @author hh
     * @version v1
     * @return   string [].type - 通知类型标识
     * @return   string [].name - 通知类型名称
     */
    public function allNoticeType(): array
    {
        $noticeType = $this
                ->field('type, type name')
                ->withAttr('name', function($val){
                    return $this->getNoticeTypeName($val);
                })
                ->where('is_default', 1)
                ->select()
                ->toArray();
        return $noticeType;
    }

    /**
     * @时间 2025-03-07
     * @title 获取商品全局通知动作
     * @desc  获取商品全局通知动作
     * @author hh
     * @version v1
     * @return   string [].name - 动作标识
     * @return   string [].name_lang - 动作名称
     */
    public function productNoticeSetting()
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        $NoticeSettingModel = new NoticeSettingModel();
        $settingList = $NoticeSettingModel
                    ->field('name,name_lang')
                    ->whereIn('name', $productNoticeGroupAction)
                    ->select()
                    ->toArray();
        $lang = lang();
        foreach ($settingList as $k=>$v) {
            if(empty($v['name_lang'])) $settingList[$k]['name_lang'] = $lang['notice_action_'.$v['name']];
        }
        return $settingList;
    }

    /**
     * @时间 2025-03-07
     * @title 获取通知类型名称
     * @desc  获取通知类型名称
     * @author hh
     * @version v1
     * @return  string type - 通知类型标识 require
     * @return  string
     */
    protected function getNoticeTypeName($type)
    {
        if(in_array($type, ['email','sms'])){
            return lang('system_notice_type_'.$type);
        }else{
            return lang_plugins('addon_notice_type_'.$type);
        }
    }

    /**
     * @时间 2025-03-07
     * @title 获取商品通知动作状态
     * @desc  获取商品通知动作状态
     * @author hh
     * @version v1
     * @return  string type - 通知类型标识 require
     * @return  string product_id - 商品 require
     * @return  string act - 商品动作 require
     * @return  string
     */
    public function getProductNoticeStatus($param): int
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        // 不是需要验证的动作
        if(!in_array($param['act'], $productNoticeGroupAction)){
            return 1;
        }
        $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
        $productNoticeGroupId = $ProductNoticeGroupProductModel
                                ->where('product_id', $param['product_id'])
                                ->where('type', $param['type'])
                                ->value('product_notice_group_id');
        if(empty($productNoticeGroupId)){
            return 0;
        }
        $noticeSetting = $this
                        ->where('id', $productNoticeGroupId)
                        ->value('notice_setting');
        if(empty($noticeSetting)){
            return 0;
        }
        $noticeSetting = json_decode($noticeSetting, true);
        if(isset($noticeSetting[$param['act']])){
            return $noticeSetting[ $param['act'] ];
        }
        return 0;
    }

    /**
     * @时间 2025-03-07
     * @title 获取商品通知动作开启的通知类型
     * @desc  获取商品通知动作开启的通知类型,返回NULL代表所有类型都可用
     * @author hh
     * @version v1
     * @return  string product_id - 商品 require
     * @return  string act - 商品动作 require
     * @return  array|null
     */
    public function getProductNoticeEnableType($param)
    {
        $productNoticeGroupAction = config('idcsmart.product_notice_group_action');
        // 不是需要验证的动作
        if(!in_array($param['act'], $productNoticeGroupAction)){
            return NULL;
        }
        $ProductNoticeGroupProductModel = new ProductNoticeGroupProductModel();
        $productNoticeGroup = $ProductNoticeGroupProductModel
                            ->alias('pngp')
                            ->field('png.type,png.notice_setting')
                            ->where('product_id', $param['product_id'])
                            ->join('product_notice_group png', 'png.id=pngp.product_notice_group_id')
                            ->select();
        $type = [];
        foreach($productNoticeGroup as $v){
            $v['notice_setting'] = json_decode($v['notice_setting'], true);
            if(!empty($v['notice_setting'][$param['act']])){
                $type[] = $v['type'];
            }
        }
        return $type;
    }

}
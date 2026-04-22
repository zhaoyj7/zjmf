<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\HostModel;
use app\common\model\ClientModel;
use app\common\model\ServerModel;
use addon\idcsmart_cloud\logic\ToolLogic;
use server\idcsmart_cloud\model\HostLinkModel as HLM1;
use server\common_cloud\model\HostLinkModel as HLM2;
use server\mf_cloud\model\HostLinkModel as HLM3;
use server\mf_cloud_mysql\model\HostLinkModel as HLM4;
use addon\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamProductModel;
use app\common\model\SupplierModel;

/**
 * @title 安全组产品关联表模型
 * @desc 安全组产品关联表模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel
 */
class IdcsmartSecurityGroupHostLinkModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_host_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_security_group_id' 	=> 'int',
        'host_id'         					=> 'int',
    ];

    /**
     * 时间 2022-06-09
     * @title 安全组实例列表
     * @desc 安全组实例列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host
     * @method  GET
     * @param int param.id - 安全组ID required
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 实例
     * @return int list[].id - 实例ID
     * @return string list[].name - 名称 
     * @return string list[].ip - IP
     * @return int count - 实例总数
     */
    public function idcsmartSecurityGroupHostList($param)
    {
        $param['orderby'] = 'id';//isset($param['orderby']) && in_array($param['orderby'], ['host_id']) ? 'aisghl.'.$param['orderby'] : 'aisghl.host_id';

        $clientId = get_client_id();
        if(empty($clientId)){
            return ['list' => [], 'count' => 0];
        }

        $where = function (Query $query) use($param, $clientId) {
            if(!empty($clientId)){
                $query->where('h.client_id', $clientId);
            }
            if(!empty($param['id'])){
                $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
            }
            // if(!empty($param['keywords'])){
            //     $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
            // }
        };

    	$count = $this->alias('aisghl')
            ->leftJoin('host h', 'h.id=aisghl.host_id AND h.is_delete=0')
            ->group('aisghl.host_id')
            ->where($where)
            ->count();

        $list = $this
            ->alias('aisghl')
            ->field('h.id,h.name,hi.dedicate_ip ip')
            ->leftJoin('host h', 'h.id=aisghl.host_id AND h.is_delete=0')
            ->leftJoin('host_ip hi', 'h.id=hi.host_id')
            ->where($where)
            ->group('h.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        // 先查询实例所属模块
        // $module = $this->alias('aisghl')
        //     ->leftJoin('host h', 'h.id=aisghl.host_id AND h.is_delete=0')
        //     ->leftJoin('server s', 'h.server_id=s.id')
        //     ->where($where)
        //     ->column('s.module');

        // $module = array_unique($module);
        // $module = array_intersect($module, ['idcsmart_cloud','common_cloud','mf_cloud','cloudpods','huawei_cloud','huawei_rds','ctyun_ecs','ctyun_rds']);

        // $list = null;
        // foreach($module as $v){
        //     if(is_null($list)){
        //         $list = $this
        //                 ->alias('aisghl')
        //                 ->field('h.id,h.name,hi.dedicate_ip ip')
        //                 ->leftJoin('host h', 'h.id=aisghl.host_id AND h.is_delete=0')
        //                 ->leftJoin('server s', 'h.server_id=s.id')
        //                 ->leftJoin('host_ip hi', 'h.id=hi.host_id')
        //                 ->where($where)
        //                 ->where('s.module', $v)
        //                 ->group('h.id')
        //                 ->limit($param['limit'])
        //                 ->page($param['page'])
        //                 ->order($param['orderby'], $param['sort']);
        //     }else{
        //         $list->union(function($query) use ($v, $param, $clientId, $where) {
        //             $query->name('addon_idcsmart_security_group_host_link')
        //                 ->alias('aisghl')
        //                 ->field('h.id,h.name,hi.dedicate_ip ip')
        //                 ->leftJoin('host h', 'h.id=aisghl.host_id AND h.is_delete=0')
        //                 ->leftJoin('server s', 'h.server_id=s.id')
        //                 ->leftJoin('host_ip hi', 'h.id=hi.host_id')
        //                 ->group('h.id')
        //                 ->where($where)
        //                 ->where('s.module', $v)
        //                 ->select();
        //         });
        //     }
        // }
        // if(!is_null($list)){
        //     $list = $list
        //             ->select()
        //             ->toArray();
        // }

        return ['list' => $list ?? [], 'count' => $count];
    }

    /**
     * 时间 2022-09-08
     * @title 关联安全组
     * @desc 关联安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host/:host_id
     * @method  POST
     * @param int param.id - 安全组ID required
     * @param int param.host_id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function linkSecurityGroup($param)
    {
        $clientId = get_client_id();
        
        $securityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($securityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        if($securityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        if($host['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(!empty($securityGroupHostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_already_in_security_group')];
        }
        if($host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
        }
        
        // 检查是否为下游产品
        $upstreamHost = UpstreamHostModel::where('host_id', $host['id'])->find();
        if(!empty($upstreamHost)){
            // 这是下游产品，需要转发到上游系统
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if(empty($upstreamProduct)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
            
            $supplier = SupplierModel::find($upstreamProduct['supplier_id']);
            if(empty($supplier)){
                return ['status'=>400, 'msg'=>lang_plugins('supplier_not_found')];
            }
            
            // 根据供应商类型获取对应的逻辑类
            $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
            if(!class_exists($supplierLogicClass)){
                return ['status'=>400, 'msg'=>lang_plugins('supplier_type_not_support')];
            }
            
            try {
                $supplierLogic = new $supplierLogicClass($supplier['id']);
                
                // 查找或创建上游安全组（使用 supplier_id）
                $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])
                    ->where('supplier_id', $supplier['id'])
                    ->where('type', 'host')
                    ->find();
                
                if(empty($securityGroupLink)){
                    // 需要在上游创建安全组
                    $createRes = $supplierLogic->createSecurityGroup([
                        'name' => $securityGroup['name'],
                        'description' => $securityGroup['description'],
                        'create_default_rule' => 0,  // 不创建默认规则，由下游同步
                    ]);
                    
                    if($createRes['status'] != 200){
                        return $createRes;
                    }
                    
                    $upstreamSecurityGroupId = $createRes['data']['id'];
                    
                    // 保存安全组映射关系（使用 supplier_id）
                    $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
                    $IdcsmartSecurityGroupLinkModel->create([
                        'addon_idcsmart_security_group_id' => $param['id'],
                        'supplier_id' => $supplier['id'],
                        'server_id' => 0,
                        'security_id' => $upstreamSecurityGroupId,
                        'type' => 'host',
                    ]);
                    
                    // 同步规则到上游并记录映射（使用 supplier_id）
                    $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
                    $securityGroupRules = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $param['id'])
                        ->select()
                        ->toArray();
                    
                    foreach($securityGroupRules as $rule){
                        $localRuleId = $rule['id'];
                        
                        // 准备上游API参数（使用当前模块的API格式）
                        $ruleParam = [
                            'description' => $rule['description'] ?? '',
                            'direction' => $rule['direction'],
                            'protocol' => $rule['protocol'],
                            'port' => $rule['port'],
                            'ip' => $rule['ip'],
                        ];
                        
                        // 在上游创建规则
                        $ruleCreateRes = $supplierLogic->createSecurityGroupRule($upstreamSecurityGroupId, $ruleParam);
                        
                        if($ruleCreateRes['status'] == 200){
                            // 保存规则映射关系（使用 supplier_id）
                            $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                                'addon_idcsmart_security_group_rule_id' => $localRuleId,
                                'supplier_id' => $supplier['id'],
                                'server_id' => 0,
                                'security_rule_id' => $ruleCreateRes['data']['id'] ?? 0,
                                'type' => 'host',
                            ]);
                        }
                    }
                } else {
                    $upstreamSecurityGroupId = $securityGroupLink['security_id'];
                }
                
                // 调用上游接口关联安全组（使用上游的安全组ID和上游的产品ID）
                $res = $supplierLogic->linkSecurityGroup($upstreamSecurityGroupId, $upstreamHost['upstream_host_id']);
                
                if($res['status'] == 200){
                    // 在本地保存关联关系
                    $this->where('host_id', $param['host_id'])->delete();
                    $this->create([
                        'host_id' => $param['host_id'],
                        'addon_idcsmart_security_group_id' => $param['id'],
                    ]);
                }
                
                return $res;
            } catch (\Exception $e) {
                return ['status'=>400, 'msg'=>$e->getMessage()];
            }
        }

        $server = ServerModel::find($host['server_id']);
        $server['password'] = aes_password_decode($server['password']);

        if($server['module']=='idcsmart_cloud'){
            $hostLink = HLM1::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            // 
            if($securityGroup['type'] != 'host'){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_use_this_security_group')];
            }
        }else if($server['module']=='common_cloud'){
            $hostLink = HLM2::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if($securityGroup['type'] != 'host'){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_use_this_security_group')];
            }
        }else if($server['module']=='mf_cloud'){
            $hostLink = HLM3::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module'] == 'mf_cloud_mysql'){
            $hostLink = HLM4::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }
        else{
            $res = ['status'=>400, 'msg'=>lang_plugins('host_type_error')];

            $hookRes = hook('addon_idcsmart_cloud_link_security_group', ['server'=>$server, 'security_group'=>$securityGroup->toArray(), 'host_id'=>$param['host_id'] ]);
            foreach($hookRes as $v){
                if(!empty($v) && isset($v['status'])){
                    $res = $v;
                    break;
                }
            }
        }
        if(!isset($res)){
            $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])
                                ->where('server_id', $host['server_id'])
                                ->where('type', $hostLink['type'] ?? 'host')
                                ->find();

            $IC = new IC($server);

            if(!empty($securityGroupLink)){
                $post['security_group'] = $securityGroupLink['security_id'];
            }else{
                $client = ClientModel::find($clientId);

                $serverHash = ToolLogic::formatParam($server['hash']);

                // 开通参数
                $post = [];
                $post['hostname'] = $host['name'];

                // 定义用户参数
                $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面
                $username = $prefix.$client['id'];
                
                $userData = [
                    'username'=>$username,
                    'email'=>$client['email'] ?: '',
                    'status'=>1,
                    'real_name'=>$client['username'] ?: '',
                    'password'=>rand_str()
                ];
                $IC->userCreate($userData);
                $userCheck = $IC->userCheck($username);
                if($userCheck['status'] != 200){
                    return $userCheck;
                }
                $post['client'] = $userCheck['data']['id'];

                $post['type'] = $hostLink['type'] ?? 'host';
                // 自动创建安全组
                $securityGroupData = [
                    'name'                  => 'security-'.rand_str(12),
                    'description'           => $securityGroup['name'],
                    'uid'                   => $post['client'],
                    'type'                  => $post['type'],
                    'create_default_rule'   => 0,   // 不创建默认规则
                ];
                $securityGroupCreateRes = $IC->securityGroupCreate($securityGroupData);
                if($securityGroupCreateRes['status'] != 200){
                    return $securityGroupCreateRes;
                }
                $post['security_group'] = $securityGroupCreateRes['data']['id'];
                // 保存关联
                $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
                $IdcsmartSecurityGroupLinkModel->saveSecurityGroupLink([
                    'addon_idcsmart_security_group_id'  => $param['id'],
                    'server_id'                         => $server['id'],
                    'security_id'                       => $securityGroupCreateRes['data']['id'],
                    'type'                              => $post['type'],
                ]);
                // 创建规则
                $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
                $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $param['id'])->select()->toArray();
                foreach($securityGroupRule as $v){
                    $ruleId = $v['id'];
                    unset($v['id'], $v['lock']);
                    $v = IdcsmartSecurityGroupRuleModel::transRule($v);

                    $securityGroupRuleCreateRes = $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], $v);
                    if($securityGroupRuleCreateRes['status'] == 200){
                        $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                            'addon_idcsmart_security_group_rule_id' => $ruleId,
                            'server_id'                             => $server['id'],
                            'security_rule_id'                      => $securityGroupRuleCreateRes['data']['id'] ?? 0,
                            'type'                                  => $post['type'],
                        ]);
                    }
                }
                // 轻量版添加一条拒绝所有
                if($post['type'] == 'lightHost'){
                    $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], [
                        'description'   => lang_plugins('security_rule_deny_all'),
                        'direction'     => 'in',
                        'protocol'      => 'all',
                        'lock'          => 1,
                        'start_ip'      => '0.0.0.0',
                        'end_ip'        => '0.0.0.0',
                        'start_port'    => 1,
                        'end_port'      => 65535,
                        'priority'      => 1000,
                        'action'        => 'drop',
                    ]);
                }
            }
            $res = $IC->linkSecurityGroup($post['security_group'], ['type'=>1,'cloud'=>[$hostLink['rel_id']]]);
        }
        if($res['status']==200){
            $res['msg'] = $res['msg'] ?? lang_plugins('security_group_link_success');

            $this->where('host_id', $param['host_id'])->delete();
            $this->create([
                'host_id' => $param['host_id'],
                'addon_idcsmart_security_group_id' => $param['id'],
            ]);
        }
        return $res;
    }

    /**
     * 时间 2024-11-24
     * @title 同步安全组到代理商
     * @desc 同步安全组到代理商
     * @author hh
     * @version v1
     * @param array param - 参数 require
     * @param int param.security_group_id - 安全组ID required
     * @param int param.supplier_id - 代理商ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.security_group_id - 上游安全组ID
     */
    public function syncSecurityGroupToSupplier($param): array
    {
        // 验证安全组
        $securityGroup = IdcsmartSecurityGroupModel::find($param['security_group_id']);
        if(empty($securityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        // 验证代理商
        $supplier = SupplierModel::find($param['supplier_id']);
        if(empty($supplier)){
            return ['status'=>400, 'msg'=>lang_plugins('supplier_not_found')];
        }

        // 检查当前安全组是否已在代理商存在
        $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['security_group_id'])
            ->where('supplier_id', $param['supplier_id'])
            ->where('type', 'host')
            ->find();

        // 如果已存在，直接返回上游安全组ID
        if(!empty($securityGroupLink)){
            return [
                'status' => 200, 
                'msg' => lang_plugins('security_group_already_exist'),
                'data' => ['security_group_id' => $securityGroupLink['security_id']]
            ];
        }

        // 获取供应商逻辑类
        $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
        if(!class_exists($supplierLogicClass)){
            return ['status'=>400, 'msg'=>lang_plugins('supplier_type_not_support')];
        }

        try {
            $supplierLogic = new $supplierLogicClass($supplier['id']);
            
            // 在代理商创建安全组
            $createRes = $supplierLogic->createSecurityGroup([
                'name' => $securityGroup['name'],
                'description' => $securityGroup['description'],
            ]);
            
            if($createRes['status'] != 200){
                return $createRes;
            }
            
            $upstreamSecurityGroupId = $createRes['data']['id'];
            
            // 保存安全组映射关系
            $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
            $IdcsmartSecurityGroupLinkModel->create([
                'addon_idcsmart_security_group_id' => $param['security_group_id'],
                'supplier_id' => $supplier['id'],
                'server_id' => 0,
                'security_id' => $upstreamSecurityGroupId,
                'type' => 'host',
            ]);
            
            // 同步规则到代理商
            $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
            $securityGroupRules = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $param['security_group_id'])
                ->select()
                ->toArray();
            
            foreach($securityGroupRules as $rule){
                $localRuleId = $rule['id'];
                
                // 准备上游API参数
                $ruleParam = [
                    'description' => $rule['description'] ?? '',
                    'direction' => $rule['direction'],
                    'protocol' => $rule['protocol'],
                    'port' => $rule['port'],
                    'ip' => $rule['ip'],
                ];
                
                // 在代理商创建规则
                $ruleCreateRes = $supplierLogic->createSecurityGroupRule($upstreamSecurityGroupId, $ruleParam);
                
                if($ruleCreateRes['status'] == 200){
                    // 保存规则映射关系
                    $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                        'addon_idcsmart_security_group_rule_id' => $localRuleId,
                        'supplier_id' => $supplier['id'],
                        'security_rule_id' => $ruleCreateRes['data']['id'] ?? 0,
                        'type' => 'host',
                    ]);
                }
            }
            
            return [
                'status' => 200, 
                'msg' => lang_plugins('sync_success'),
                'data' => ['security_group_id' => $upstreamSecurityGroupId]
            ];
        } catch (\Exception $e) {
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
    }

    /**
     * 时间 2022-09-08
     * @title 取消关联安全组
     * @desc 取消关联安全组
     * @author theworld
     * @version v1
     * @param int param.id - 安全组ID required
     * @param int param.host_id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function unlinkSecurityGroup($param)
    {
        $clientId = get_client_id();

        $securityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($securityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        if($securityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        if($host['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(empty($securityGroupHostLink)){
            if(request()->is_api){
                return ['status'=>200, 'msg'=>lang_plugins('host_not_in_security_group') ];
            }
            return ['status'=>400, 'msg'=>lang_plugins('host_not_in_security_group')];
        }
        
        // 检查是否为下游产品
        $upstreamHost = UpstreamHostModel::where('host_id', $host['id'])->find();
        if(!empty($upstreamHost)){
            // 这是下游产品，需要转发到上游系统
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if(empty($upstreamProduct)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
            
            $supplier = SupplierModel::find($upstreamProduct['supplier_id']);
            if(empty($supplier)){
                return ['status'=>400, 'msg'=>lang_plugins('supplier_not_found')];
            }
            
            // 根据供应商类型获取对应的逻辑类
            $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
            if(!class_exists($supplierLogicClass)){
                return ['status'=>400, 'msg'=>lang_plugins('supplier_type_not_support')];
            }
            
            try {
                $supplierLogic = new $supplierLogicClass($supplier['id']);
                
                // 查找上游安全组映射（使用 supplier_id）
                $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])
                    ->where('supplier_id', $supplier['id'])
                    ->where('type', 'host')
                    ->find();
                
                if(!empty($securityGroupLink)){
                    // 调用上游接口取消关联安全组
                    $res = $supplierLogic->unlinkSecurityGroup($securityGroupLink['security_id'], $upstreamHost['upstream_host_id']);
                } else {
                    // 没有找到映射关系，直接删除本地关联
                    $res = ['status'=>200, 'msg'=>lang_plugins('delete_success')];
                }
                
                if($res['status'] == 200){
                    // 删除本地关联关系
                    $this->where('host_id', $param['host_id'])->delete();
                }
                
                return $res;
            } catch (\Exception $e) {
                return ['status'=>400, 'msg'=>$e->getMessage()];
            }
        }

        $server = ServerModel::find($host['server_id']);
        $server['password'] = aes_password_decode($server['password']);

        if($server['module']=='idcsmart_cloud'){
            $hostLink = HLM1::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='common_cloud'){
            $hostLink = HLM2::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='mf_cloud'){
            $hostLink = HLM3::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='mf_cloud_mysql'){
            $hostLink = HLM4::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else{
            $res = ['status'=>400, 'msg'=>lang_plugins('host_type_error')];

            $securityId = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])->where('server_id', $server['id'])->where('type', $hostLink['type'] ?? 'host')->value('security_id');
            if(!empty($securityId)){
                $hookRes = hook('addon_idcsmart_cloud_unlink_security_group', ['securityGroup'=>$securityGroup, 'server'=>$server, 'security_id'=>$securityId, 'host_id'=>$param['host_id'] ]);
                foreach($hookRes as $v){
                    if(!empty($v) && isset($v['status'])){
                        $res = $v;
                        break;
                    }
                }
            }else{
                $res = ['status'=>200, 'msg'=>lang_plugins('delete_success')];
            }
        }
        if(!isset($res)){
            $IC = new IC($server);

            $res = $IC->delLinkSecurityGroup($hostLink['rel_id']);
        }
        if($res['status']==200){
            $this->where('host_id', $param['host_id'])->delete();
        }
        return $res;
    }

    /**
     * 保存安全组主机关联关系
     * @param int $securityGroupId 安全组ID
     * @param int $hostId 主机ID
     * @return void
     */
    public function saveSecurityGroupHostLink($securityGroupId, $hostId): void
    {
        $res = $this->where('host_id', $hostId)->find();
        if(empty($res)){
            $this->create(['addon_idcsmart_security_group_id'=>$securityGroupId, 'host_id'=>$hostId]);
        }else{
            $this->update(['addon_idcsmart_security_group_id'=>$securityGroupId], ['host_id'=>$hostId]);
        }
    }







}
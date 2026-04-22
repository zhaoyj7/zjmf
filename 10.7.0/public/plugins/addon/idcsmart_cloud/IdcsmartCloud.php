<?php
namespace addon\idcsmart_cloud;

use app\common\lib\Plugin;
use think\facade\Db;
use addon\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use app\common\model\ServerModel;
use app\common\model\SupplierModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel;

require_once __DIR__ . '/common.php';
/*
 * 魔方云管理
 * @author theworld
 * @time 2022-06-08
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartCloud extends Plugin
{
    public $noNav;

    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartCloud', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '魔方云管理',
        'description' => '魔方云管理',
        'author'      => '智简魔方',  //开发者
        'version'     => '3.0.1',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group`",
            "CREATE TABLE `idcsmart_addon_idcsmart_security_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '安全组ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(20) NOT NULL DEFAULT 'host' COMMENT '类型',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '安全组名称',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='安全组表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_host_link`",
            "CREATE TABLE `idcsmart_addon_idcsmart_security_group_host_link` (
  `addon_idcsmart_security_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '安全组ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  KEY `host_id` (`host_id`),
  KEY `addon_idcsmart_security_group_id` (`addon_idcsmart_security_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='安全组产品关联表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_link`",
            "CREATE TABLE `idcsmart_addon_idcsmart_security_group_link` (
  `addon_idcsmart_security_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '安全组ID',
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口ID',
  `security_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组ID',
  `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型',
  `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID',
  KEY `server_id` (`server_id`),
  KEY `addon_idcsmart_security_group_id` (`addon_idcsmart_security_group_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='安全组外部关联表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_rule`",
            "CREATE TABLE `idcsmart_addon_idcsmart_security_group_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '安全组规则ID',
  `addon_idcsmart_security_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '安全组ID',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `direction` enum('in','out') NOT NULL DEFAULT 'in' COMMENT '规则方向',
  `protocol` varchar(255) NOT NULL DEFAULT '' COMMENT '协议all,tcp,udp,icmp',
  `port` varchar(255) NOT NULL DEFAULT '' COMMENT '端口范围',
  `ip` varchar(255) NOT NULL DEFAULT '' COMMENT '授权IP',
  `lock` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否锁定',
  `start_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '起始IP',
  `end_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '结束IP',
  `start_port` int(11) NOT NULL DEFAULT '0' COMMENT '起始端口',
  `end_port` int(11) NOT NULL DEFAULT '0' COMMENT '结束端口',
  `priority` int(11) NOT NULL DEFAULT '0' COMMENT '优先级',
  `action` varchar(20) NOT NULL DEFAULT '' COMMENT '授权策略,accept允许,drop拒绝',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `addon_idcsmart_security_group_id` (`addon_idcsmart_security_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='安全组规则表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_rule_link`",
            "CREATE TABLE `idcsmart_addon_idcsmart_security_group_rule_link` (
  `addon_idcsmart_security_group_rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '安全组规则ID',
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口ID',
  `security_rule_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组规则ID',
  `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型',
  `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID',
  KEY `server_id` (`server_id`),
  KEY `addon_idcsmart_security_group_rule_id` (`addon_idcsmart_security_group_rule_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='安全组规则外部关联表'",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        # 安装成功返回true，失败false
        return true;
    }

    # 插件卸载
    public function uninstall()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_host_link`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_link`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_rule`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_security_group_rule_link`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    /**
     * 时间 2022-06-08
     * @title 任务执行
     * @desc 任务执行
     * @author theworld
     * @version v1
     * @param string param.type - 任务类型
     * @param string param.task_data - 任务数据
     * @param int param.rel_id - 类型addon_idcsmart_security_group_rule时为安全组规则ID,类型addon_idcsmart_security_group时为安全组ID
     * @return string - - Finish完成Failed失败
     */
    public function taskRun($param)
    {
        if($param['type']=='addon_idcsmart_security_group_rule'){
            $fail = false;
            $failReasons = [];
            $data = json_decode($param['task_data'], true);
            if($data){
                if($data['type']=='create'){
                    $idcsmartSecurityGroupRule = IdcsmartSecurityGroupRuleModel::find($param['rel_id']);
                    if(!empty($idcsmartSecurityGroupRule)){
                        $idcsmartSecurityGroupRule = $idcsmartSecurityGroupRule->toArray();
                        $idcsmartSecurityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $data['id'])->select()->toArray();
                        if(!empty($idcsmartSecurityGroupLink)){
                            $ServerModel = new ServerModel();
                            foreach ($idcsmartSecurityGroupLink as $key => $value) {
                                // 检查是否为下游（supplier_id > 0）
                                if(!empty($value['supplier_id'])){
                                    // 下游场景
                                    $idcsmartSecurityGroupRuleLink = IdcsmartSecurityGroupRuleLinkModel::where('addon_idcsmart_security_group_rule_id', $param['rel_id'])->where('supplier_id', $value['supplier_id'])->where('type', $value['type'])->find();
                                    if(empty($idcsmartSecurityGroupRuleLink)){
                                        $supplier = SupplierModel::find($value['supplier_id']);
                                        if(empty($supplier)){
                                            continue;
                                        }
                                        
                                        $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
                                        if(!class_exists($supplierLogicClass)){
                                            $fail = true;
                                            continue;
                                        }
                                        
                                        try {
                                            $supplierLogic = new $supplierLogicClass($supplier['id']);
                                            // 准备上游API参数
                                            $ruleParam = [
                                                'description' => $idcsmartSecurityGroupRule['description'] ?? '',
                                                'direction' => $idcsmartSecurityGroupRule['direction'],
                                                'protocol' => $idcsmartSecurityGroupRule['protocol'],
                                                'port' => $idcsmartSecurityGroupRule['port'],
                                                'ip' => $idcsmartSecurityGroupRule['ip'],
                                            ];
                                            $result = $supplierLogic->createSecurityGroupRule($value['security_id'], $ruleParam);
                                            
                                            if(isset($result['status']) && $result['status']==200){
                                                $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
                                                $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                                                    'addon_idcsmart_security_group_rule_id' => $param['rel_id'],
                                                    'supplier_id'                           => $value['supplier_id'],
                                                    'security_rule_id'                      => $result['data']['id'] ?? 0,
                                                    'type'                                  => $value['type'],
                                                ]);
                                            }else{
                                                $fail = true;
                                                if(!empty($result['msg'])){
                                                    $failReasons[] = $result['msg'];
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            $fail = true;
                                            $failReasons[] = $e->getMessage();
                                        }
                                    }
                                }else{
                                    // 本地服务器场景
                                    $idcsmartSecurityGroupRuleLink = IdcsmartSecurityGroupRuleLinkModel::where('addon_idcsmart_security_group_rule_id', $param['rel_id'])->where('server_id', $value['server_id'])->where('type', $value['type'])->find();
                                    if(empty($idcsmartSecurityGroupRuleLink)){
                                        $server = $ServerModel->indexServer($value['server_id']);
                                        if(empty($server)){
                                            continue;
                                        }

                                        if($server['module'] == 'mf_cloud' || $server['module'] == 'mf_cloud_mysql' || $server['module'] == 'common_cloud'){
                                            $IC = new IC($server);
                                            $result = $IC->securityGroupRuleCreate($value['security_id'], IdcsmartSecurityGroupRuleModel::transRule($idcsmartSecurityGroupRule));
                                        }else{
                                            // 增加钩子
                                            $hookRes = hook('after_task_addon_idcsmart_cloud_security_group_rule_create', [
                                                'server'        => $server,
                                                'security_id'   => $value['security_id'],
                                                'rule'          => $idcsmartSecurityGroupRule,
                                            ]);

                                            $result = [];
                                            foreach($hookRes as $v){
                                                if(!empty($v) && isset($v['status'])){
                                                    $result = $v;
                                                }
                                            }
                                        }
                                        if(isset($result['status']) && $result['status']==200){
                                            IdcsmartSecurityGroupRuleLinkModel::create([
                                                'addon_idcsmart_security_group_rule_id' => $param['rel_id'],
                                                'server_id'                             => $value['server_id'],
                                                'security_rule_id'                      => $result['data']['id'],
                                                'type'                                  => $value['type'],
                                            ]);
                                        }else{
                                            $fail = true;
                                            if(!empty($result['msg'])){
                                                $failReasons[] = $result['msg'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else if($data['type']=='update'){
                    $idcsmartSecurityGroupRule = IdcsmartSecurityGroupRuleModel::find($param['rel_id']);
                    if(!empty($idcsmartSecurityGroupRule)){
                        $idcsmartSecurityGroupRule = $idcsmartSecurityGroupRule->toArray();
                        $idcsmartSecurityGroupRuleLink = IdcsmartSecurityGroupRuleLinkModel::where('addon_idcsmart_security_group_rule_id', $param['rel_id'])->select()->toArray();
                        $ServerModel = new ServerModel();
                        foreach ($idcsmartSecurityGroupRuleLink as $key => $value) {
                            // 检查是否为下游
                            if(!empty($value['supplier_id'])){
                                // 下游场景
                                $supplier = SupplierModel::find($value['supplier_id']);
                                if(empty($supplier)){
                                    continue;
                                }
                                
                                $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
                                if(!class_exists($supplierLogicClass)){
                                    $fail = true;
                                    continue;
                                }
                                
                                try {
                                    $supplierLogic = new $supplierLogicClass($supplier['id']);
                                    // 准备上游API参数
                                    $ruleParam = [
                                        'description' => $idcsmartSecurityGroupRule['description'] ?? '',
                                        'direction' => $idcsmartSecurityGroupRule['direction'],
                                        'protocol' => $idcsmartSecurityGroupRule['protocol'],
                                        'port' => $idcsmartSecurityGroupRule['port'],
                                        'ip' => $idcsmartSecurityGroupRule['ip'],
                                    ];
                                    $result = $supplierLogic->updateSecurityGroupRule($value['security_rule_id'], $ruleParam);
                                    
                                    if(!isset($result['status']) || $result['status'] != 200){
                                        $fail = true;
                                        if(!empty($result['msg'])){
                                            $failReasons[] = $result['msg'];
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $fail = true;
                                    $failReasons[] = $e->getMessage();
                                }
                            }else{
                                // 本地服务器场景
                                $server = $ServerModel->indexServer($value['server_id']);
                                if(empty($server)){
                                    continue;
                                }

                                if($server['module'] == 'mf_cloud' || $server['module'] == 'mf_cloud_mysql' || $server['module'] == 'common_cloud'){
                                    $IC = new IC($server);
                                    $result = $IC->securityGroupRuleModify($value['security_rule_id'], IdcsmartSecurityGroupRuleModel::transRule($idcsmartSecurityGroupRule));
                                }else{
                                    // 增加钩子
                                    $hookRes = hook('after_task_addon_idcsmart_cloud_security_group_rule_update', [
                                        'server'            => $server,
                                        'security_rule_id'  => $value['security_rule_id'],
                                        'rule'              => $idcsmartSecurityGroupRule,
                                    ]);

                                    $result = [];
                                    foreach($hookRes as $v){
                                        if(!empty($v) && isset($v['status'])){
                                            $result = $v;
                                        }
                                    }
                                }

                                if(!isset($result['status']) || $result['status'] != 200){
                                    $fail = true;
                                    if(!empty($result['msg'])){
                                        $failReasons[] = $result['msg'];
                                    }
                                }
                            }
                        }
                    }
                }else if($data['type']=='delete'){
                    $idcsmartSecurityGroupRuleLink = IdcsmartSecurityGroupRuleLinkModel::where('addon_idcsmart_security_group_rule_id', $param['rel_id'])->select()->toArray();
                    $ServerModel = new ServerModel();
                    foreach ($idcsmartSecurityGroupRuleLink as $key => $value) {
                        // 检查是否为下游
                        if(!empty($value['supplier_id'])){
                            // 下游场景
                            $supplier = SupplierModel::find($value['supplier_id']);
                            if(empty($supplier)){
                                continue;
                            }
                            
                            $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
                            if(!class_exists($supplierLogicClass)){
                                $fail = true;
                                continue;
                            }
                            
                            try {
                                $supplierLogic = new $supplierLogicClass($supplier['id']);
                                $result = $supplierLogic->deleteSecurityGroupRule($value['security_rule_id']);
                                
                                if(!isset($result['status']) || $result['status']!=200){
                                    $fail = true;
                                    if(!empty($result['msg'])){
                                        $failReasons[] = $result['msg'];
                                    }
                                }
                            } catch (\Exception $e) {
                                $fail = true;
                                $failReasons[] = $e->getMessage();
                            }
                        }else{
                            // 本地服务器场景
                            $server = $ServerModel->indexServer($value['server_id']);
                            if(empty($server)){
                                continue;
                            }

                            if($server['module'] == 'mf_cloud' || $server['module'] == 'mf_cloud_mysql' || $server['module'] == 'common_cloud'){
                                $IC = new IC($server);
                                $result = $IC->securityGroupRuleDelete($value['security_rule_id']);
                            }else{
                                // 增加钩子
                                $hookRes = hook('after_task_addon_idcsmart_cloud_security_group_rule_delete', [
                                    'server'            => $server,
                                    'security_rule_id'  => $value['security_rule_id'],
                                ]);

                                $result = [];
                                foreach($hookRes as $v){
                                    if(!empty($v) && isset($v['status'])){
                                        $result = $v;
                                    }
                                }
                            }
                            if(!isset($result['status']) || $result['status']!=200){
                                $fail = true;
                                if(!empty($result['msg'])){
                                    $failReasons[] = $result['msg'];
                                }
                            }
                        }
                    }
                }
                
            }
            if($fail && !empty($failReasons)){
                $failReasons = array_unique($failReasons);
                throw new \Exception( implode(',', $failReasons) );
            }
            return $fail===false ? 'Finish' : 'Failed';
        }else if($param['type']=='addon_idcsmart_security_group'){
            $fail = false;
            $data = json_decode($param['task_data'], true);
            if($data){
                if($data['type']=='delete'){
                    $idcsmartSecurityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['rel_id'])->select()->toArray();
                    
                    $ServerModel = new ServerModel();
                    foreach ($idcsmartSecurityGroupLink as $key => $value) {
                        // 检查是否为下游
                        if(!empty($value['supplier_id'])){
                            // 下游场景
                            $supplier = SupplierModel::find($value['supplier_id']);
                            if(empty($supplier)){
                                continue;
                            }
                            
                            $supplierLogicClass = "\\addon\\idcsmart_cloud\\logic\\" . ucfirst($supplier['type']) . "SupplierLogic";
                            if(!class_exists($supplierLogicClass)){
                                $fail = true;
                                continue;
                            }
                            
                            try {
                                $supplierLogic = new $supplierLogicClass($supplier['id']);
                                $result = $supplierLogic->deleteSecurityGroup($value['security_id']);
                                
                                if(!isset($result['status']) || $result['status']!=200){
                                    $fail = true;
                                }
                            } catch (\Exception $e) {
                                $fail = true;
                            }
                        }else{
                            // 本地服务器场景
                            $server = $ServerModel->indexServer($value['server_id']);
                            if(!isset($server->id)){
                                continue;
                            }
                            
                            if($server['module'] == 'mf_cloud' || $server['module'] == 'mf_cloud_mysql' || $server['module'] == 'common_cloud'){
                                $IC = new IC($server);
                                $result = $IC->securityGroupDelete($value['security_id']);
                            }else{
                                // 增加钩子
                                $hookRes = hook('after_task_addon_idcsmart_cloud_security_group_delete', [
                                    'server'            => $server,
                                    'security_id'       => $value['security_id'],
                                ]);

                                $result = [];
                                foreach($hookRes as $v){
                                    if(!empty($v) && isset($v['status'])){
                                        $result = $v;
                                    }
                                }
                            }

                            if(!isset($result['status']) || $result['status']!=200){
                                $fail = true;
                            }
                        }
                    }
                }
            }
            return $fail===false ? 'Finish' : 'Failed';
        }

    }

    # 插件升级
    public function upgrade()
    {
        $name = $this->info['name'];
        $version = $this->info['version'];
        $PluginModel = new \app\admin\model\PluginModel();
        $plugin = $PluginModel->where('name', $name)->find();
        $sql = [];
        if(isset($plugin['version'])){
            if(version_compare('1.0.1', $plugin['version'], '>')){
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` MODIFY COLUMN `security_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组ID';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` MODIFY COLUMN `security_rule_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组规则ID';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';";
            }
        }
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    /**
     * @title 代理模块开通前
     * @desc  代理模块开通前
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   array param.upstream_product - 代理商品模型实例 require
     * @param   array param.host - 产品模型实例 require
     * @param   array param.configoptions - 开通参数 require
     */
    public function beforeResModuleCreateAccount($param){
        if(!empty($param['upstream_product']) && $param['upstream_product']['res_module'] == 'mf_cloud'){
            $host = $param['host'];
            $supplierId = $param['upstream_product']['supplier_id'];

            $param['configoptions']['security_group_id'] = 0;
            $param['configoptions']['security_group_protocol'] = [];
            // 获取关联的安全组
            $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();
            $securityGroupHostLink = $IdcsmartSecurityGroupHostLinkModel->where('host_id', $host['id'])->find();
            if(!empty($securityGroupHostLink)){
                $syncSecurityGroupToSupplier = $IdcsmartSecurityGroupHostLinkModel->syncSecurityGroupToSupplier([
                    'security_group_id' => $securityGroupHostLink['addon_idcsmart_security_group_id'],
                    'supplier_id'       => $supplierId,
                ]);
                if($syncSecurityGroupToSupplier['status'] == 200){
                    $param['configoptions']['security_group_id'] = $syncSecurityGroupToSupplier['data']['security_group_id'];
                }
            }
            // 如果没关联/上游没创建成功，解绑本地数据
            if(empty($param['configoptions']['security_group_id'])){
                $IdcsmartSecurityGroupHostLinkModel->where('host_id', $host['id'])->delete();
            }
        }
    }

    /**
     * @title 供应商删除后
     * @desc  供应商删除后
     * @author hh
     * @version v1
     * @param   array param - 参数 require
     * @param   int param.id - 供应商ID require
     */
    public function afterSupplierDelete($param)
    {
        if(!empty($param['id'])){
            $supplierId = $param['id'];
            // 删除关联的安全组
            IdcsmartSecurityGroupLinkModel::where('supplier_id', $supplierId)->delete();
            IdcsmartSecurityGroupRuleLinkModel::where('supplier_id', $supplierId)->delete();
        }
    }

}
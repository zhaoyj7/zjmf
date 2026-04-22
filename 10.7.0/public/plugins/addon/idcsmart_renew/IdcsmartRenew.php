<?php
namespace addon\idcsmart_renew;

use addon\idcsmart_renew\model\IdcsmartRenewModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;
use app\common\lib\Plugin;
use app\common\logic\ResModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\UpstreamProductModel;
use think\facade\Db;
use addon\idcsmart_renew\logic\IdcsmartRenewLogic;

/*
 * 智简魔方续费插件
 * @author wyh
 * @time 2022-06-02
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartRenew extends Plugin
{
    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartRenew', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '续费',
        'description' => '续费',
        'author'      => '智简魔方',  //开发者
        'version'     => '3.1.2',      // 版本号
    );

    # 定义此变量,表示不需要默认导航
    public $noNav;

    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew`",
            "CREATE TABLE `idcsmart_addon_idcsmart_renew` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `new_billing_cycle` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '新周期',
  `new_billing_cycle_time` INT(11) NOT NULL DEFAULT '0' COMMENT '新周期时间',
  `new_billing_cycle_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '新周期续费金额',
  `status` ENUM('Completed','Pending') NOT NULL DEFAULT 'Pending' COMMENT '状态:Pending待执行,Completed已完成',
  `create_time` INT(10) NOT NULL DEFAULT '0',
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费原价',
  `client_level_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费用户等级折扣',
  `is_continuous_renew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否连续续费',
  `natural_month_due_time` int(11) NOT NULL DEFAULT '0' COMMENT '自然月预付费精确到期时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `client_id` (`client_id`),
  KEY `host_id` (`host_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT '续费表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew_auto`",
            "CREATE TABLE `idcsmart_addon_idcsmart_renew_auto` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动续费状态0关闭1开启',
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 插入邮件短信模板
        $templates = IdcsmartRenewLogic::getDefaultConfig('renew_notice_template');
        foreach ($templates as $key=>$template){
            $template['name'] = $key;
            notice_action_create($template);
        }

        # 安装成功返回true，失败false
        return true;
    }
    # 插件卸载
    public function uninstall()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew_auto`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 删除插入的邮件短信模板
        $templates = IdcsmartRenewLogic::getDefaultConfig('renew_notice_template');
        foreach ($templates as $key=>$template){
            notice_action_delete($key);
        }
        return true;
    }

    # 实现订单支付后钩子
    public function orderPaid($param)
    {
        if (!isset($param['id'])){
            return false;
        }
        $id = $param['id'];
        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel
            ->where('order_id',$id)
            ->where('type','renew')
            ->select();
        foreach ($orderItems as $orderItem){
            $IdcsmartRenewModel->renewHandle($orderItem->rel_id);
        }

        // wyh 20240307 新增 续费支付后，参与推介计划
        hook('recommend_renew_order_paid',['id'=>$id]);

        return true;
    }

    # 实现订单创建后钩子
    public function afterOrderCreate($param)
    {
        if (!isset($param['id'])){
            return false;
        }

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($param['id']);
        if (empty($order)){
            return false;
        }

        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel->where('order_id',$param['id'])
            ->select()->toArray();

        $IdcsmartRenewModel = new IdcsmartRenewModel();
        foreach ($orderItems as $item){
            if ($item['type']=='upgrade'){
                # 删除产品未支付的续费订单
                $IdcsmartRenewModel->deleteUnpaidRenewOrder($item['host_id']);
                // 获取当前产品未支付续费订单
                // $renewOrderIds = $OrderModel->alias('o')
                //     ->leftJoin('order_item oi','oi.order_id=o.id')
                //     ->where('o.client_id',$order->client_id)
                //     ->where('o.type','renew')
                //     ->where('o.status','Unpaid')
                //     ->where('oi.host_id',$item['host_id'])
                //     ->column('o.id');

                // $OrderItemModel->whereIn('order_id',$renewOrderIds)->delete();

                // $OrderModel->whereIn('id',$renewOrderIds)
                //     ->where('type','renew')
                //     ->where('status','Unpaid')
                //     ->delete();
            }
        }

        return true;
    }

    # 实现退款后钩子
    public function afterRefund($param)
    {
        $hostId = $param['host_id']??0;

        if(!isset($param['client_id'])){
            $param['client_id'] = HostModel::where('id', $hostId)->value('client_id') ?? 0;
        }

        $OrderItemModel = new OrderItemModel();

        $renews = $OrderItemModel->alias('oi')
            ->field('oi.order_id,oi.amount')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('oi.host_id',$hostId)
            ->where('oi.type','renew')
            ->whereIn('o.status',['Paid','Refunded'])
            ->where('o.client_id', $param['client_id'])
            ->select()
            ->toArray();

        $amount = 0;

        $manualOrderIds = [];
        foreach ($renews as $renew){
            $manualOrderIds[] = $renew['order_id'];
            $amount = bcadd($amount,$renew['amount'],2);
        }
        # 生成订单,退手动更改的钱
        $manualAmount = $OrderItemModel->whereIn('order_id',$manualOrderIds)
            ->where('type','manual')
            ->sum('amount');

        $amount = bcadd($amount,$manualAmount,2);

        return $amount;
    }

    # 实现产品列表后按钮模板钩子
    public function templateClientAfterHostListButton($param)
    {
        if (!isset($param['id'])){
            return '';
        }
        $id = intval($param['id']);

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        return $IdcsmartRenewModel->templateClientAfterHostListButton($id);
    }

    # 实现产品列表table-header上 按钮 钩子
    public function templateClientHostListOnTableHeader()
    {
        $button = lang_plugins('renew_batch');

        $url = "console/v1/renew/batch";

        return "<a href=\"{$url}\" class=\"btn btn-primary h-100 custom-button text-white\" disabled'>{$button}</a>";
    }

    // 获取产品续费退款金额和总续费时长(计算升降级时续费需退款金额)
    public function renewHostRefundAmount($param)
    {
        $hostId = $param['id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);

        $IdcsmartRenewModel = new IdcsmartRenewModel();
        $renews = $IdcsmartRenewModel->where('host_id',$hostId)
            ->where('status','Completed')
            ->order('id','asc')
            ->select()
            ->toArray();
        $flag = false;
        $refundTotal = 0; // 总退款金额
        $renewCycleTotal = 0; // 总续费时间
        foreach ($renews as $item1){
            $renewTimeTotal = 0; // 判断续费是否已到期
            foreach ($renews as $item2){
                if ($item2['id']>$item1['id']){
                    $renewTimeTotal += $item2['new_billing_cycle_time'];
                }
            }
            $newDueTime = $host['due_time']-$renewTimeTotal;
            if ($newDueTime > time()){
                if (($newDueTime-$item1['new_billing_cycle_time']) > time()){ # 还未到当前续费周期时间段
                    $refundTotal = bcadd($refundTotal,$item1['new_billing_cycle_amount'],2);
                }else{
                    $flag = true;
                    $refundTotal = bcadd($refundTotal,$item1['new_billing_cycle_amount']/$item1['new_billing_cycle_time']*($newDueTime-time()),2);
                }
            }
            $renewCycleTotal += $item1['new_billing_cycle_time'];
        }

        return [$refundTotal,$renewCycleTotal,$flag];
    }
    
    // 删除续费记录
    public function deleteRenewLog($param)
    {
        $hostId = $param['id']??0;

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $IdcsmartRenewModel->where('host_id',$hostId)->where('status','Completed')->delete();

        return true;
    }

    /**
     * @时间 2024-12-05
     * @title 获取自动续费产品ID
     * @desc  获取自动续费产品ID
     * @author hh
     * @version v1
     * @param   array param.host_id - 产品ID require
     * @return  array
     */
    public function getAutoRenewHostId($param)
    {
        $param['host_id'] = $param['host_id'] ?? [];
        $data = [];

        if(!empty($param['host_id'])){
            $IdcsmartRenewAutoModel = new IdcsmartRenewAutoModel();

            $data = $IdcsmartRenewAutoModel
                    ->whereIn('host_id', $param['host_id'])
                    ->where('status', 1)
                    ->column('host_id');
        }
        return $data;
    }

    public function taskRun($param)
    {
        // 类型在系统中唯一，可抛出错误
        if ($param['type']=='addon_renew_batch_renew'){
            $taskData = json_decode($param['task_data'],true);
            $hostId = $taskData['host_id']??0;
            $orderId = $taskData['order_id']??0;
            $HostModel = new HostModel();
            $host = $HostModel->find($hostId);
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if ($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->renew($host,$orderId);
                if ($result['status']!=200){
                    throw new \Exception((lang('addon_renew_batch_renew_upstream_error')) . $result['msg']??lang_plugins('renew_fail'));
                }
                return $result['status']==200 ? 'Finish' : 'Failed';

            }
        }
    }

    public function minuteCron()
    {
        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $IdcsmartRenewModel->isAdmin = true;

        $IdcsmartRenewModel->beforeHostRenewalFirst();

//        $renewal_first_host=Db::name('host')
//            ->field('id,client_id')
//            ->whereIn('status','Active')
//            ->where('due_time','<=',time()+10*60)
//            ->where('billing_cycle', 'NOT IN', ['free','onetime','on_demand'])
//            ->where('change_billing_cycle_id', 0)
//            ->select()
//            ->toArray();
//        foreach($renewal_first_host as $h){
//            $IdcsmartRenewModel->beforeHostRenewalFirst($h['id']);
//        }
    }

    // 升级
    public function upgrade()
    {
        $name = $this->info['name'];
        $version = $this->info['version'];
        $PluginModel = new \app\admin\model\PluginModel();
        $plugin = $PluginModel->where('name', $name)->where('module', 'addon')->find();
        $sql = [];
        if(isset($plugin['version'])){
            if(version_compare('2.3.1', $plugin['version'], '>')){
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('get_auto_renew_host_id', {$plugin['status']}, 'IdcsmartRenew', 'addon', 0);";
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('task_run', {$plugin['status']}, 'IdcsmartRenew', 'addon', 0);";
                $sql[] = "UPDATE `idcsmart_plugin_hook` SET `name`='minute_cron' WHERE `plugin`='IdcsmartRenew' AND `name`='before_host_renewal_first';";
            }
            if(version_compare('2.3.4', $plugin['version'], '>')){
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('after_host_edit', {$plugin['status']}, 'IdcsmartRenew', 'addon', 0);";
            }
            if(version_compare('3.0.9', $plugin['version'], '>')){
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_renew` ADD COLUMN `is_continuous_renew` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否连续续费';";
            }
        }
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    public function afterHostEdit($param)
    {
        $hostId = $param['id']??0;
        $IdcsmartRenewAutoModel = new IdcsmartRenewAutoModel();
        $IdcsmartRenewAutoModel->isAdmin = true;
        $IdcsmartRenewAutoModel->updateStatus([
            'id' => $hostId,
            'status' => $param['auto_renew']??0,
        ]);
        return true;
    }

}
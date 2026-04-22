<?php
namespace addon\idcsmart_refund;

use addon\idcsmart_refund\model\IdcsmartRefundModel;
use app\common\lib\Plugin;
use think\facade\Db;
use app\common\model\RefundRecordModel;
use app\common\model\OrderModel;

/*
 * 智简魔方退款插件
 * @author wyh
 * @time 2022-07-06
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartRefund extends Plugin
{
    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartRefund', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '退款',
        'description' => '退款',
        'author'      => '智简魔方',  //开发者
        'version'     => '3.0.6',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_refund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '退款表ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额(金额为-1表示不需要退款)',
  `suspend_reason` varchar(2000) NOT NULL DEFAULT '' COMMENT '停用原因',
  `type` varchar(25) NOT NULL DEFAULT 'Immediate' COMMENT '类型:Expire到期退款,Immediate立即退款',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID(审核人)',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `status` varchar(25) NOT NULL DEFAULT 'Pending' COMMENT '状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消',
  `reject_reason` varchar(2000) NOT NULL DEFAULT '' COMMENT '驳回原因',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `refund_record_id` int(10) NOT NULL DEFAULT '0' COMMENT '退款记录ID',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund_product`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_refund_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品退款表ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `type` varchar(25) NOT NULL DEFAULT 'Artificial' COMMENT '退款类型:Artificial人工，Auto自动',
  `require` varchar(25) NOT NULL DEFAULT '' COMMENT '退款要求:First首次订购,Same同类商品首次订购',
  `range_control` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启购买后X天内控制:0否默认,1是',
  `range` int(11) NOT NULL DEFAULT '0' COMMENT '购买后X天内',
  `full_refund_days` int(11) NOT NULL DEFAULT '0' COMMENT '全额退款天数,0表示不启用',
  `rule` varchar(25) NOT NULL DEFAULT '' COMMENT '退款规则:Day按天退款,Month按月退款,Ratio按比例退款',
  `ratio_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '比例',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `action` varchar(25) NOT NULL DEFAULT 'Suspend' COMMENT '退款后产品操作：Suspend暂停，Terminate删除',
  `api_refund_allow` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'API开通是否允许退款:0否,1是',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund_reason`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_refund_reason` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '停用原因管理表ID',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '内容',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
		
		 # 插入邮件短信模板
		$templates = include __DIR__ . '/config/config.php';
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
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund_product`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_refund_reason`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
		# 删除插入的邮件短信模板
        $templates = include __DIR__ . '/config/config.php';
        foreach ($templates as $key=>$template){
            notice_action_delete($key);
        }

        // 处理中状态取消
        RefundRecordModel::where('refund_type', 'addon')->whereIn('status', ['Pending','Suspending','Suspend'])->update([
            'status' => 'Cancelled',
        ]);
        
        return true;
    }

    public function upgrade()
    {
        $name = $this->info['name'];
        $PluginModel = new \app\admin\model\PluginModel();
        $plugin = $PluginModel->where('name', $name)->find();
        $sql = [];
        if(isset($plugin['version'])){
            if(version_compare('2.2.1', $plugin['version'], '>')){
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `action` VARCHAR(25) NOT NULL DEFAULT 'Suspend' COMMENT '退款后产品操作：Suspend暂停，Terminate删除'; ";
            }
            if(version_compare('2.2.3', $plugin['version'], '>')){
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund` ADD COLUMN `refund_record_id` int(10) NOT NULL DEFAULT '0' COMMENT '退款记录ID';";

                $IdcsmartRefundModel = new IdcsmartRefundModel();
                $idcsmartRefund = $IdcsmartRefundModel
                                ->field('id,client_id,amount,create_time,status,reject_reason,update_time,host_id')
                                ->where('amount', '<>', -1)
                                ->select();

                // 同状态映射
                $status = [
                    'Refund'    => 'Refunded',
                ];

                // 已退款的产品ID
                $hostId = [];

                // 同步原插件中的退款记录
                $RefundRecordModel = new RefundRecordModel();
                foreach($idcsmartRefund as $v){
                    $refundRecordId = $RefundRecordModel->insertGetId([
                        'order_id'          => 0,
                        'client_id'         => $v['client_id'],
                        'admin_id'          => 0,
                        'type'              => 'credit',
                        'transaction_id'    => 0,
                        'amount'            => $v['amount'],
                        'create_time'       => $v['create_time'],
                        'status'            => $status[ $v['status'] ] ?? $v['status'],
                        'reason'            => $v['reject_reason'],
                        'refund_time'       => $v['status'] == 'Refund' ? $v['update_time'] : 0,
                        'host_id'           => $v['host_id'],
                        'refund_type'       => 'addon',
                    ]);
                    $sql[] = "UPDATE `idcsmart_addon_idcsmart_refund` SET `refund_record_id`={$refundRecordId} WHERE `id`={$v['id']};";

                    if($v['status'] == 'Refund'){
                        $hostId[] = $v['host_id'];
                    }
                }
                // 重新计算这些产品对应订单
                if(!empty($hostId)){
                    $OrderModel = new OrderModel();

                    $orders = $OrderModel
                            ->alias('o')
                            ->field('o.*')
                            ->join('order_item oi', 'oi.order_id=o.id')
                            ->whereIn('oi.host_id', $hostId)
                            ->whereIn('o.status', ['Paid','Refunded'])
                            ->group('o.id')
                            ->select();
                    foreach($orders as $order){
                        $orderRefund = $OrderModel->orderRefundIndex([
                            'order' => $order,
                        ]);

                        // 修改退款总额
                        $order->save([
                            'credit'        => max( bcsub($order['credit'], $orderRefund['refund_addon'], 2), 0),
                            'status'        => 'Refunded',
                            'refund_amount' => bcadd($orderRefund['refund_credit'], $orderRefund['refund_gateway'], 2),
                            'update_time'   => time(),
                        ]);
                    }
                }
            }
            if(version_compare('3.0.2', $plugin['version'], '>')){
                // 检查字段是否已存在
                $checkSql = "SHOW COLUMNS FROM `idcsmart_addon_idcsmart_refund_product` LIKE 'full_refund_days'";
                $exists = Db::query($checkSql);
                
                if (empty($exists)) {
                    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `full_refund_days` int(11) NOT NULL DEFAULT '0' COMMENT '全额退款天数,0表示不启用' AFTER `range`;";
                }
            }
            if(version_compare('3.0.5', $plugin['version'], '>')){
                // 检查api_refund_allow字段是否已存在
                $checkSql = "SHOW COLUMNS FROM `idcsmart_addon_idcsmart_refund_product` LIKE 'api_refund_allow'";
                $exists = Db::query($checkSql);
                
                if (empty($exists)) {
                    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_refund_product` ADD COLUMN `api_refund_allow` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'API开通是否允许退款:0否,1是' AFTER `action`;";
                }
            }
        }
        foreach ($sql as $v){
            Db::execute($v);
        }

        return true;
    }
    
    # 退款停用按钮模板钩子
    public function templateAfterServicedetailSuspended($param)
    {
        $IdcsmartRefundModel = new IdcsmartRefundModel();

        return $IdcsmartRefundModel->templateAfterServicedetailSuspended($param);
    }

    # 实现每日一次定时任务钩子
    public function dailyCron()
    {
        $IdcsmartRefundModel = new IdcsmartRefundModel();

        return $IdcsmartRefundModel->dailyCron();
    }
    
    # 实现产品退款判断钩子
    public function hostRefund($param)
    {
        $IdcsmartRefundModel = new IdcsmartRefundModel();

        return $IdcsmartRefundModel->hostRefund($param);
    }
}
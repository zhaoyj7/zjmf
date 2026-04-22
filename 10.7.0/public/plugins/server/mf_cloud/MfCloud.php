<?php 
namespace server\mf_cloud;

use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\validate\CartValidate;
use server\mf_cloud\validate\VpcNetworkValidate;
use server\mf_cloud\validate\HostUpdateValidate;
use think\facade\Db;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\model\LimitRuleModel;
use app\common\logic\ModuleLogic;

/**
 * 魔方云模块
 */
class MfCloud
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
     * @return  string display_name - 模块名称
     * @return  string version - 版本号
	 */
	public function metaData(): array
    {
		return ['display_name'=>'魔方云(自定义配置)', 'version'=>'4.0.0'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer()
    {
		$sql = [
			"CREATE TABLE `idcsmart_module_mf_cloud_backup_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'snap=快照,bakcup=备份',
  `num` int(11) NOT NULL COMMENT '允许的数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `on_demand_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需价格',
  PRIMARY KEY (`id`),
  KEY `pt` (`product_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快照备份价格设置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `ip_mac_bind` tinyint(3) NOT NULL DEFAULT '0' COMMENT '嵌套虚拟化(0=关闭,1=开启)',
  `support_ssh_key` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否支持SSH密钥(0=关闭,1=开启)',
  `rand_ssh_port` tinyint(3) NOT NULL DEFAULT '0' COMMENT '随机SSH端口(0=关闭,1=开启)',
  `support_normal_network` tinyint(3) NOT NULL DEFAULT '0' COMMENT '经典网络(0=不支持,1=支持)',
  `support_vpc_network` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'VPC网络(0=不支持,1=支持)',
  `support_public_ip` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否允许公网IP(0=不支持,1=支持)',
  `backup_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用备份(0=不启用,1=启用)',
  `snap_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用快照(0=不启用,1=启用)',
  `disk_limit_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性能限制(0=不启用,1=启用)',
  `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)',
  `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)',
  `niccard` tinyint(4) NOT NULL DEFAULT '0' COMMENT '网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)',
  `ipv6_num` varchar(10) NOT NULL DEFAULT '' COMMENT 'IPv6数量',
  `nat_acl_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT转发限制',
  `nat_web_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT建站限制',
  `cpu_model` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)',
  `memory_unit` varchar(10) NOT NULL DEFAULT 'GB' COMMENT '内存单位(GB,MB)',
  `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)',
  `node_priority` tinyint(3) NOT NULL DEFAULT '0',
  `disk_limit_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '数据盘数量限制开关(0=关闭,1=开启)',
  `disk_limit_num` int(11) NOT NULL DEFAULT '16' COMMENT '数据盘限制数量',
  `free_disk_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '免费数据盘开关(0=关闭,1=开启)',
  `free_disk_size` int(11) NOT NULL DEFAULT '1' COMMENT '免费数据盘大小(G)',
  `only_sale_recommend_config` tinyint(3) NOT NULL DEFAULT '0' COMMENT '仅售卖套餐',
  `no_upgrade_tip_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `default_nat_acl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT转发(0=关闭,1=开启)',
  `default_nat_web` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT建站(0=关闭,1=开启)',
  `rand_ssh_port_start` varchar(10) NOT NULL DEFAULT '' COMMENT '随机端口开始端口',
  `rand_ssh_port_end` varchar(10) NOT NULL DEFAULT '' COMMENT '随机端口结束端口',
  `rand_ssh_port_windows` varchar(10) NOT NULL DEFAULT '' COMMENT '指定端口Windows',
  `rand_ssh_port_linux` varchar(10) NOT NULL DEFAULT '' COMMENT '指定端口linux',
  `default_one_ipv4` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '默认携带IPv4',
  `manual_manage` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '手动管理商品(0=关闭,1=开启)',
  `is_agent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '下游可用,是否代理商(0=不是,1=是)',
  `sync_firewall_rule` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)',
  `order_default_defence` varchar(100) NOT NULL DEFAULT '' COMMENT '订购默认防御',
  `free_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '免费磁盘类型',
  `custom_rand_password_rule` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '自定义随机密码位数(0=关闭,1=开启)',
  `default_password_length` int(11) unsigned NOT NULL DEFAULT '12' COMMENT '默认密码长度',
  `level_discount_cpu_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU订购等级优惠(0=关闭,1=开启)',
  `level_discount_cpu_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU升级等级优惠(0=关闭,1=开启)',
  `level_discount_cpu_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'CPU是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_memory_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存订购等级优惠(0=关闭,1=开启)',
  `level_discount_memory_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存升级等级优惠(0=关闭,1=开启)',
  `level_discount_memory_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '内存是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_bw_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽订购等级优惠(0=关闭,1=开启)',
  `level_discount_bw_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽升降级等级优惠(0=关闭,1=开启)',
  `level_discount_bw_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '带宽是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_ipv4_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv4订购等级优惠(0=关闭,1=开启)',
  `level_discount_ipv4_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv4升级等级优惠(0=关闭,1=开启)',
  `level_discount_ipv4_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IPv4是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_ipv6_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv6订购等级优惠(0=关闭,1=开启)',
  `level_discount_ipv6_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IPv6升级等级优惠(0=关闭,1=开启)',
  `level_discount_ipv6_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IPv6是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_system_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统盘订购等级优惠(0=关闭,1=开启)',
  `level_discount_system_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统盘升级等级优惠(0=关闭,1=开启)',
  `level_discount_system_disk_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '系统盘是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_data_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据盘订购等级优惠(0=关闭,1=开启)',
  `level_discount_data_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据盘升级等级优惠(0=关闭,1=开启)',
  `level_discount_data_disk_renew` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据盘是否应用等级优惠续费(0=不启用,1=启用)',
  `level_discount_gpu_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'GPU订购等级优惠(0=关闭,1=开启)',
  `level_discount_gpu_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'GPU升级等级优惠(0=关闭,1=开启)',
  `level_discount_gpu_renew` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'GPU是否应用等级优惠续费(0=不启用,1=启用)',
  `disk_range_limit_switch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '磁盘大小购买限制开关(0=关闭,1=开启)',
  `disk_range_limit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '磁盘大小购买限制',
  `simulate_physical_machine_enable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '模拟物理机运行(0=关闭,1=开启)',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_cloud_config_limit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'cpu=CPU与内存限制,data_center=数据中心,image=操作系统与计算限制',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '线路ID',
  `min_bw` int(11) NOT NULL DEFAULT '0' COMMENT '最小带宽',
  `max_bw` int(11) NOT NULL DEFAULT '0' COMMENT '最大带宽',
  `cpu` text NOT NULL COMMENT 'CPU',
  `memory` text NOT NULL,
  `min_memory` int(11) NOT NULL DEFAULT '0' COMMENT '最小内存',
  `max_memory` int(11) NOT NULL DEFAULT '0' COMMENT '最大内存',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `image_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '镜像ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) NOT NULL DEFAULT '' COMMENT '区域',
  `cloud_config` varchar(20) NOT NULL COMMENT 'node=节点,area=区域,node_group=节点分组',
  `cloud_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `gpu_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'GPU名称',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据中心表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_disk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '磁盘名称',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '磁盘大小',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云磁盘ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '磁盘类型',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买时价格',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `is_free` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否免费(0=不是,1=是)',
  `type2` varchar(20) NOT NULL DEFAULT 'data' COMMENT 'system=系统盘,data=数据盘',
  `status` tinyint(7) unsigned NOT NULL DEFAULT '3' COMMENT '状态',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游磁盘ID',
  `free_size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '免费大小',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据盘';",
			"CREATE TABLE `idcsmart_module_mf_cloud_disk_limit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型(system=系统盘,disk=数据盘)',
  `min_value` int(11) NOT NULL DEFAULT '0' COMMENT '容量小值',
  `max_value` int(11) NOT NULL DEFAULT '0' COMMENT '容量大值',
  `read_bytes` int(11) NOT NULL DEFAULT '0',
  `write_bytes` int(11) NOT NULL DEFAULT '0',
  `read_iops` int(11) NOT NULL DEFAULT '0',
  `write_iops` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `range` (`min_value`,`max_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='性能限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_duration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时常',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `price_factor` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '价格系数',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `support_apply_for_suspend` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '周期是否支持申请停用(0=否,1=是)',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为默认周期(0=否,1=是)',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='周期表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_cloud_host_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云实例ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `backup_num` int(11) NOT NULL DEFAULT '0' COMMENT '备份数量',
  `snap_num` int(11) NOT NULL DEFAULT '0' COMMENT '快照数量',
  `power_status` varchar(30) NOT NULL DEFAULT '' COMMENT '电源状态',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `vpc_network_id` int(11) NOT NULL DEFAULT '0' COMMENT 'VPC网络ID',
  `config_data` text NOT NULL COMMENT '用于缓存购买时的配置价格,用于升降级',
  `ssh_key_id` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT 'host',
  `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '推荐配置ID',
  `default_ipv4` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '是否有默认IPv4(-1默认以前逻辑,0=没有,1=有)',
  `migrate_task_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '魔方云迁移任务ID',
  `parent_host_id` int(11) NOT NULL DEFAULT '0' COMMENT '主产品ID',
  `vpc_private_ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'VPC内网IP',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`),
  KEY `recommend_config_id` (`recommend_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品实例关联表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `image_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否收费(0=不收费,1=收费)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否可用(0=禁用,1=可用)',
  `rel_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联魔方云ID',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `is_market` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否镜像市场(0=普通镜像,1=镜像市场镜像)',
  PRIMARY KEY (`id`),
  KEY `image_group_id` (`image_group_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像分组';",
			"CREATE TABLE `idcsmart_module_mf_cloud_line` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '线路名称',
  `bill_type` varchar(20) NOT NULL DEFAULT '' COMMENT 'bw=带宽计费,flow=流量计费',
  `bw_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '带宽IP分组',
  `defence_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用防护',
  `defence_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '防护IP分组',
  `ip_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用附加IP价格',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `link_clone` tinyint(3) NOT NULL DEFAULT '0' COMMENT '链接创建(0=否,1=是)',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `gpu_name` varchar(255) NOT NULL DEFAULT '',
  `gpu_enable` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用GPU',
  `ipv6_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用IPv6',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `order_default_defence` varchar(100) NOT NULL DEFAULT '' COMMENT '订购默认防御',
  `sync_firewall_rule` tinyint(3) NOT NULL DEFAULT '0' COMMENT '同步防火墙规则(0=关闭,1=开启)',
  `support_on_demand` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '线路是否支持按需',
  `upstream_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上游是否隐藏(0=否,1=是)',
  `ipv6_group_id` varchar(10) NOT NULL DEFAULT '' COMMENT 'IPv6分组ID',
  PRIMARY KEY (`id`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='线路表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '0=CPU配置\r\n1=内存配置\r\n2=线路带宽计费\r\n3=线路流量计费\r\n4=线路防护配置\r\n5=线路附加IP配置\r\n6=系统盘配置\r\n7=数据盘配置',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '计费方式(radio=单选，step=阶梯,total=总量)',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '单选值',
  `min_value` int(10) NOT NULL DEFAULT '0' COMMENT '最小值',
  `max_value` int(10) NOT NULL DEFAULT '0' COMMENT '最大值',
  `step` int(10) NOT NULL DEFAULT '1' COMMENT '步长',
  `other_config` text NOT NULL COMMENT '其他配置,json存储',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `firewall_type` varchar(100) NOT NULL DEFAULT '' COMMENT '防火墙类型',
  `defence_rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '防御规则ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `on_demand_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '按需计费价格',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通用配置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关联表:0=option,1=recommend_config',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置价格表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_recommend_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `description` text NOT NULL COMMENT '描述',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `cpu` int(11) NOT NULL DEFAULT '0' COMMENT 'CPU',
  `memory` int(11) NOT NULL DEFAULT '0' COMMENT '内存',
  `system_disk_size` int(11) NOT NULL DEFAULT '0' COMMENT '系统盘大小',
  `data_disk_size` int(11) NOT NULL DEFAULT '0' COMMENT '数据盘大小',
  `bw` int(11) NOT NULL DEFAULT '0' COMMENT '带宽',
  `peak_defence` int(11) NOT NULL DEFAULT '0' COMMENT '防御峰值',
  `system_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '系统盘类型',
  `data_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '数据盘类型',
  `flow` int(11) NOT NULL DEFAULT '0' COMMENT '流量',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '线路ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `ip_num` int(11) NOT NULL DEFAULT '1' COMMENT 'IP数量',
  `upgrade_range` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0=不可升降级,1=全部,2=自定义',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)',
  `gpu_num` int(11) NOT NULL DEFAULT '0' COMMENT 'GPU数量',
  `ipv6_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IPv6数量',
  `upgrade_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '升降级是否显示(0=否,1=是)',
  `in_bw` varchar(20) NOT NULL DEFAULT '' COMMENT '流入带宽',
  `traffic_type` tinyint(3) unsigned NOT NULL DEFAULT '3' COMMENT '流量计费方向(1=进,2=出,3=进+出)',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `due_not_free_gpu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '计费到期不自动释放GPU(0=否,1=是)',
  `ontrial` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启试用：0否默认，1是',
  `ontrial_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '试用价格',
  `ontrial_stock_control` tinyint(1) NOT NULL DEFAULT '0' COMMENT '试用库存开关：0否，1是',
  `ontrial_qty` int(11) NOT NULL DEFAULT '0' COMMENT '试用库存',
  `on_demand_price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '按需价格',
  `on_demand_flow_price` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '流量按需计费价格',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order` (`order`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐配置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_vpc_network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ips` varchar(50) NOT NULL DEFAULT '' COMMENT 'VPC网段',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云VPCID',
  `vpc_name` varchar(255) NOT NULL DEFAULT '',
  `downstream_client_id` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `downstream_client_id` (`downstream_client_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_mf_cloud_resource_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '资源包名称',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云资源包ID',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
          "CREATE TABLE `idcsmart_module_mf_cloud_recommend_config_upgrade_range` (
  `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `rel_recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '可升降级套餐ID',
  KEY `recommend_config_id` (`recommend_config_id`),
  KEY `rel_recommend_config_id` (`rel_recommend_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        "CREATE TABLE `idcsmart_module_mf_cloud_limit_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rule` text NOT NULL COMMENT '规则json',
  `result` text NOT NULL COMMENT '结果json',
  `rule_md5` char(32) NOT NULL DEFAULT '' COMMENT '规则md5用于判断是否重复',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  `upstream_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  PRIMARY KEY (`id`),
  KEY `rule_md5` (`rule_md5`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '限制规则表';",
        "CREATE TABLE `idcsmart_module_mf_cloud_ip_defence` (
  `host_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `defence` varchar(50) NOT NULL DEFAULT '' COMMENT '防御',
  KEY `ip` (`ip`),
  KEY `defence` (`defence`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '云IP防御表';",
        "CREATE TABLE `idcsmart_module_mf_cloud_security_group_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `protocol` varchar(20) NOT NULL DEFAULT 'tcp' COMMENT '协议类型(all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis)',
  `port` varchar(100) NOT NULL DEFAULT '' COMMENT '端口',
  `direction` varchar(10) NOT NULL DEFAULT 'in' COMMENT '方向(in=入站,out=出站)',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0=禁用,1=启用)',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `upstream_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云安全组协议配置表';",
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-28
	 * @title 不用之后删除表
	 * @author hh
	 * @version v1
	 */
	public function afterDeleteLastServer()
    {
		$sql = [
			'drop table `idcsmart_module_mf_cloud_backup_config`;',
			'drop table `idcsmart_module_mf_cloud_config`;',
			'drop table `idcsmart_module_mf_cloud_config_limit`;',
			'drop table `idcsmart_module_mf_cloud_data_center`;',
			'drop table `idcsmart_module_mf_cloud_disk`;',
			'drop table `idcsmart_module_mf_cloud_disk_limit`;',
			'drop table `idcsmart_module_mf_cloud_duration`;',
			'drop table `idcsmart_module_mf_cloud_host_image_link`;',
			'drop table `idcsmart_module_mf_cloud_host_link`;',
			'drop table `idcsmart_module_mf_cloud_image`;',
			'drop table `idcsmart_module_mf_cloud_image_group`;',
			'drop table `idcsmart_module_mf_cloud_line`;',
			'drop table `idcsmart_module_mf_cloud_option`;',
			'drop table `idcsmart_module_mf_cloud_price`;',
			'drop table `idcsmart_module_mf_cloud_recommend_config`;',
            'drop table `idcsmart_module_mf_cloud_vpc_network`;',
            'drop table `idcsmart_module_mf_cloud_resource_package`;',
			'drop table `idcsmart_module_mf_cloud_recommend_config_upgrade_range`;',
			'drop table `idcsmart_module_mf_cloud_limit_rule`;',
            'drop table `idcsmart_module_mf_cloud_ip_defence`;',
            'drop table `idcsmart_module_mf_cloud_security_group_config`;',
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-22
	 * @title 测试连接
	 * @author hh
	 * @version v1
	 */
	public function testConnect($param)
    {
        $hash = ToolLogic::formatParam($param['server']['hash']);
        
		$IdcsmartCloud = new IdcsmartCloud($param['server']);
        $IdcsmartCloud->setIsAgent(isset($hash['account_type']) && $hash['account_type'] == 'agent');
		$res = $IdcsmartCloud->login(false, true);
		if($res['status'] == 200){
			unset($res['data']);
			$res['msg'] = lang_plugins('link_success');
		}
		return $res;
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块开通
	 * @author hh
	 * @version v1
	 */
	public function createAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->createAccount($param);
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->suspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->unsuspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->terminateAccount($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->renew($param);
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changePackage($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($param)
    {
		$param['host_id'] = $param['host']['id'];
		$this->afterSettle($param);
	}

	/**
	 * 时间 2022-06-21
	 * @title 价格计算
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
     * @param   int param.custom.peak_defence - 防御峰值(G)
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
     * @param   int param.custom.rand_password 0 生成随机密码(0=否,1=是)
     * @param   int param.custom.rand_port 0 生成随机端口(0=否,1=是)
     * @param   bool only_cal - 是否仅计算价格(false=否,true=是)
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
	 */
	public function cartCalculatePrice($param)
    {
		$CartValidate = new CartValidate();

		// 仅计算价格验证,不需要验证其他参数
		if($param['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			// 下单的验证
			if(!$CartValidate->scene('cal')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
	        // 当是VPC时,验证VPC
	        if(isset($param['custom']['network_type']) && $param['custom']['network_type'] == 'vpc'){
	        	if(isset($param['custom']['vpc'])){
	        		if(isset($param['custom']['vpc']['id']) && $param['custom']['vpc']['id']>0){
	        			
	        		}else{
	        			$VpcNetworkValidate = new VpcNetworkValidate();
	        			if(!$VpcNetworkValidate->scene('ips')->check($param['custom']['vpc'])){
				            return ['status'=>400 , 'msg'=>lang_plugins($VpcNetworkValidate->getError())];
				        }
	        		}
	        	}else{
	        		return ['status'=>400, 'msg'=>lang_plugins('support_vpc_network_param_error')];
	        	}
	        }
            // 验证资源包
            if(isset($param['custom']['resource_package_id']) && !empty($param['custom']['resource_package_id'])){
                $resourcePackage = ResourcePackageModel::where('id', $param['custom']['resource_package_id'])->where('product_id', $param['product']['id'])->find();
                if(empty($resourcePackage)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_resource_package_not_found')];
                }
            }
            // 验证限制规则
            $LimitRuleModel = new LimitRuleModel();
            $checkLimitRule = $LimitRuleModel->checkLimitRule($param['product']['id'], $param['custom']);
            if($checkLimitRule['status'] == 400){
                return $checkLimitRule;
            }
		}
        $param['custom']['product_id'] = $param['product']['id'];

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($param, $param['scene'] == 'cal_price');
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 切换商品后的输出
	 * @author hh
	 * @version v1
	 */
	public function serverConfigOption($param)
    {
		$res = [
			'template'=>'template/admin/mf_cloud.html',
		];
		return $res;
	}

  /**
   * 时间 2024-05-20
   * @title 后台产品内页实例操作输出
   * @author hh
   * @version v1
   */
  public function adminAreaModuleOperate($param)
    {
    $res = [
      'template'=>'template/admin/module_operate.html',
    ];
    return $res;
  }

	/**
	 * 时间 2022-06-29
	 * @title 前台产品内页输出
	 * @author hh
	 * @version v1
	 */
	public function clientArea()
    {
        return (new ModuleLogic())->moduleDefaultView([
            'module'       => 'mf_cloud',
            'template_dir' => 'clientarea',
            'file'         => 'product_detail.html',
        ]);
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 */
	public function hostList($param)
    {
        return (new ModuleLogic())->moduleDefaultView([
            'module'       => 'mf_cloud',
            'template_dir' => 'clientarea',
            'file'         => 'product_list.html',
        ]);
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台商品购买页面输出
	 * @author hh
	 * @version v1
	 */
	public function clientProductConfigOption($param)
    {
        return (new ModuleLogic())->moduleDefaultView([
            'module'       => 'mf_cloud',
            'template_dir' => 'cart',
            'file'         => 'goods.html',
        ]);
	}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.recommend_config_id - 套餐ID
     * @param   int param.custom.data_center_id - 数据中心ID
     * @param   int param.custom.cpu - CPU
     * @param   int param.custom.memory - 内存
     * @param   int param.custom.system_disk.size - 系统盘大小(G)
     * @param   string param.custom.system_disk.disk_type - 系统盘类型
     * @param   int param.custom.data_disk[].size - 数据盘大小(G)
     * @param   string param.custom.data_disk[].disk_type - 数据盘类型
     * @param   int param.custom.line_id - 线路ID
     * @param   int param.custom.bw - 带宽(Mbps)
     * @param   int param.custom.flow - 流量(G)
     * @param   int param.custom.peak_defence - 防御峰值(G)
     * @param   int param.custom.gpu_num - 显卡数量
     * @param   int param.custom.image_id - 镜像ID
     * @param   int param.custom.ssh_key_id - SSH密钥ID
     * @param   int param.custom.backup_num 0 备份数量
     * @param   int param.custom.snap_num 0 快照数量
     * @param   int param.custom.ip_mac_bind_enable 0 嵌套虚拟化(0=关闭,1=开启)
     * @param   int param.custom.nat_acl_limit_enable 0 是否启用NAT转发(0=关闭,1=开启)
     * @param   int param.custom.nat_web_limit_enable 0 是否启用NAT建站(0=关闭,1=开启)
     * @param   int param.custom.resource_package_id 0 资源包ID
     * @param   string param.custom.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @param   int param.custom.vpc.id - VPC网络ID
     * @param   string param.custom.vpc.ips - VPCIP段
     * @param   int param.custom.security_group_id - 安全组ID(ID优先)
     * @param   array param.custom.security_group_protocol - 安全组协议(icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,all)
     * @param   int param.custom.auto_renew 0 自动续费(0=否,1=是)
	 */
	public function afterSettle($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->afterSettle($param);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->durationPrice($param);
	}

    /**
     * 时间 2024-02-19
     * @title 获取商品起售周期价格
     * @desc 获取商品起售周期价格
     * @author hh
     * @version v1
     */
	public function getPriceCycle($param)
	{
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->getPriceCycle($param['product']['id']);
	}

	/**
	 * 时间 2023-02-16
	 * @title 资源下载
	 * @desc 资源下载
	 * @author hh
	 * @version v1
	 */
	public function downloadResource($param)
    {
        $metaData = $this->metaData();

        // 尝试解压到本地目录下
        ToolLogic::unzipToReserver();

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'module' => 'mf_cloud',
				'url' => request()->domain() . '/plugins/server/mf_cloud/data/abc.zip' , // 下载路径
                'version' => $metaData['version'] ?? '1.0.0',
			]
		];
		return $result;
	}

    /**
     * 时间 2023-04-12
     * @title 产品内页模块配置信息输出
     * @desc 产品内页模块配置信息输出
     * @author hh
     * @version v1
     */
    public function adminField($param)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->adminField($param);
    }

    /**
     * 时间 2024-02-19
     * @title 产品保存后
     * @desc 产品保存后
     * @author hh
     * @version v1
     * @param int param.module_admin_field.cpu - CPU
     * @param int param.module_admin_field.memory - 内存
     * @param int param.module_admin_field.bw - 带宽
     * @param int param.module_admin_field.in_bw - 进带宽
     * @param int param.module_admin_field.out_bw - 出带宽
     * @param int param.module_admin_field.flow - 流量
     * @param int param.module_admin_field.snap_num - 快照数量
     * @param int param.module_admin_field.backup_num - 备份数量
     * @param int param.module_admin_field.defence - 防御峰值
     * @param int param.module_admin_field.ip_num - IP数量
     * @param string param.module_admin_field.ip - 主IP
     * @param int param.module_admin_field.zjmf_cloud_id - 魔方云实例ID
     * @param int param.module_admin_field.disk_[0-9]+ - 对应数据盘大小
     * @param int param.module_admin_field.due_not_free_gpu - 不自动释放GPU(0=否,1=是)
     */
    public function hostUpdate($param)
    {
        $HostUpdateValidate = new HostUpdateValidate();
        $param['module_admin_field']['product_id'] = $param['product']['id'];
        if(!$HostUpdateValidate->scene('update')->check($param['module_admin_field'])){
            return ['status'=>400 , 'msg'=>lang_plugins($HostUpdateValidate->getError())];
        }

        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostUpdate($param);
    }

    /**
     * 时间 2024-06-17
     * @title 同步信息
     * @desc  同步信息
     * @author hh
     * @version v1
     */
    public function syncAccount($param){
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->syncAccount($param);
    }

    /*
     * 同步数据获取
     * */
    public function otherParams($params)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->otherParams($params['product']['id']);
    }

    public function syncOtherParams($params)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->syncOtherParams($params['product']['id'],$params['param'],$params['other_params'],$params['upstream_product']);
    }

    public function exchangeParams($params)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->exchangeParams($params['product']['id'],$params['param'],$params['sence'],$params['host']);
    }

    public function hostOtherParams($params){
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostOtherParams($params['host']);
    }

    public function syncHostOtherParams($params){
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->syncHostOtherParams($params['host'], $params['other_params']);
    }

    public function durationPresets($params)
    {
        $productId = $params['product']['id'];
        $durations = $params['durations'];
        $DurationModel = new DurationModel();
        $map = [];
        foreach ($durations as $duration){
            $result = $DurationModel->durationCreate([
                'product_id' => $productId,
                'name' => $duration['name'],
                'num' => $duration['num'],
                'unit' => $duration['unit'],
                'price_factor' => 1,
                'price' => 0,
            ]);
            if ($result['status']==200){
                $map[$duration['id']] = $result['data']['id'];
            }
        }
        return ['status'=>200,'data'=>$map,'msg'=>lang_plugins('success_message')];
    }

    public function durationPresetsDelete($params)
    {
        $productId = $params['product']['id'];

        $DurationModel = new DurationModel();

        $ids = $DurationModel->where('product_id',$productId)->column('id');

        foreach ($ids as $id){
            $DurationModel->durationDelete(['id'=>$id]);
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function billingItem($param)
    {
        $data = [
            [
                'name'  => 'cpu',
                'value' => 'CPU',
            ],
            [
                'name'  => 'memory',
                'value' => lang_plugins('memory'),
            ],
            [
                'name'  => 'system_disk',
                'value' => lang_plugins('system_disk'),
            ],
            [
                'name'  => 'data_disk',
                'value' => lang_plugins('data_disk'),
            ],
            // [
            //     'name'  => 'bw',
            //     'value' => lang_plugins('bw'),
            // ],
            // [
            //     'name'  => 'peak_defence',
            //     'value' => lang_plugins('mf_cloud_recommend_config_peak_defence'),
            // ],
            [
                'name'  => 'ip_num',
                'value' => lang_plugins('mf_cloud_ipv4_num'),
            ],
            [
                'name'  => 'ipv6_num',
                'value' => lang_plugins('mf_cloud_ipv6_num'),
            ],
        ];
        return $data;
    }

    /**
	 * 时间 2025-04-08
	 * @title 产品变更计费方式完成后调用
	 * @author hh
	 * @version v1
	 */
	public function changeBillingCycle($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changeBillingCycle($param);
	}

    /**
	 * 时间 2025-04-08
	 * @title 获取当前实例配置
	 * @author hh
	 * @version v1
	 */
	public function currentConfig($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->currentConfig($param['host']['id']);
	}

    /**
     * 时间 2024-04-23
     * @title 升级
     * @desc  升级
     * @author hh
     * @version v1
     * @param   string version - 当前版本
     * @return  bool
     */
    public function upgrade($version)
    {
        $sql = [];
        if(version_compare('2.3.1', $version, '>')){
            // 修复2.3.1类型bug
            $LimitRuleModel = new LimitRuleModel();
            $data = $LimitRuleModel
                    ->field('id,rule,result')
                    ->select()
                    ->toArray();

            foreach($data as $v){
                $v['rule'] = json_decode($v['rule'], true);
                $v['result'] = json_decode($v['result'], true);
                $change = false;
                if(!empty($v['rule']['cpu']['value'])){
                    foreach($v['rule']['cpu']['value'] as $kk=>$vv){
                        $v['rule']['cpu']['value'][$kk] = (int)$vv;
                    }
                    $change = true;
                }
                if(!empty($v['rule']['memory']['value'])){
                    foreach($v['rule']['memory']['value'] as $kk=>$vv){
                        $v['rule']['memory']['value'][$kk] = (int)$vv;
                    }
                    $change = true;
                }
                if(!empty($v['result']['cpu'][0]['value'])){
                    foreach($v['result']['cpu'] as $kk=>$vv){
                        foreach($vv['value'] as $kkk=>$vvv){
                            $v['result']['cpu'][$kk]['value'][$kkk] = (int)$vvv;
                        }
                    }
                    $change = true;
                }
                if(!empty($v['result']['memory'][0]['value'])){
                    foreach($v['result']['memory'] as $kk=>$vv){
                        foreach($vv['value'] as $kkk=>$vvv){
                            $v['result']['memory'][$kk]['value'][$kkk] = (int)$vvv;
                        }
                    }
                    $change = true;
                }
                if($change){
                    $update = [
                        'id'        => $v['id'],
                        'rule'      => json_encode($v['rule']),
                        'result'    => json_encode($v['result']),
                        'rule_md5'  => md5(json_encode($v['rule'])),
                    ];
                    $LimitRuleModel->where('id', $v['id'])->update($update);
                }
            }
        }
        // 系统升级里
        if(version_compare('2.3.4', $version, '>')){
            //$sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `parent_host_id` INT(11) NOT NULL DEFAULT 0 COMMENT '主产品ID';";
        }
        if(version_compare('2.3.8', $version, '>')){
            $HostLinkModel = new HostLinkModel();
            $RecommendConfigModel = new RecommendConfigModel();
            $recommendConfig = [];
            $hostLink = $HostLinkModel
                        ->field('id,config_data,recommend_config_id')
                        ->where('recommend_config_id', '>', 0)
                        ->select()
                        ->toArray();
            foreach($hostLink as $v){
                $configData = json_decode($v['config_data'], true);
                if(empty($configData['line']['bill_type']) || $configData['line']['bill_type'] != 'flow'){
                    continue;
                }
                if(!isset($recommendConfig[$v['recommend_config_id']])){
                    $recommendConfig[$v['recommend_config_id']] = $RecommendConfigModel->find($v['recommend_config_id']);
                }
                if(empty($recommendConfig[$v['recommend_config_id']])){
                    continue;
                }
                $configData['recommend_config']['flow'] = (int)$recommendConfig[$v['recommend_config_id']]['flow'];
                $configData['flow']['value'] = (int)$recommendConfig[$v['recommend_config_id']]['flow'];

                $HostLinkModel->where('id', $v['id'])->update([
                    'config_data' => json_encode($configData),
                    'update_time' => time(),
                ]);
            }
        }

        if(version_compare('3.0.1', $version, '>')){
            $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `upstream_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上游是否隐藏(0=否,1=是)';";
        }

        foreach($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    


}



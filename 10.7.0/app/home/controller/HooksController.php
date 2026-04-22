<?php
namespace app\home\controller;

/**
 * @title hook钩子文档
 * @desc 接口说明：这里编写添加的钩子文档(hook名和hook中参数)
 * @use app\home\controller\HooksController
 */
class HooksController
{
    /*               前台钩子             */
    /**
     * 时间 2022-06-01
     * @title 订单支付后执行
     * @desc 订单支付后执行
     * @author wyh
     * @url order_paid，通过实现插件主文件方法orderPaid($param)或者hooks.php文件实现add_hook('order_paid',function(){})，其他所有钩子类似
     * @param int id - desc:订单ID validate:required
     */
    public function order_paid(){}

    /**
     * 时间 2022-08-01
     * @title 注册后
     * @desc 注册后
     * @author wyh
     * @url after_register
     * @param int id - desc:客户ID validate:required
     */
    public function after_register(){}

    /**
     * 时间 2022-07-18
     * @title 订单生成前
     * @desc 订单生成前
     * @url before_order_create
     * @author wyh
     * @version v1
     * @param int client_id - desc:用户ID validate:required
     */
    public function before_order_create(){}

    /**
     * @title 产品退款
     * @desc 产品退款
     * @author wyh
     * @time 2022-07-28
     * @url host_refund
     * @param int id - desc:产品ID validate:required
     */
    public function host_refund(){}

    /**
     * @title 优惠码应用
     * @desc 优惠码应用,多个插件都会执行
     * @author wyh
     * @time 2022-06-09
     * @url client_promo_code
     * @param array promo_code - desc:优惠码 数组格式 validate:required
     * @param int client_id - desc:用户ID validate:required
     * @param int host_id - desc:产品ID validate:optional
     * @param int product_id - desc:商品ID validate:optional
     * @param string scene - desc:优惠码应用场景 Renew续费 New新购 Upgrade升降级 validate:required
     * @param float amount - desc:优惠前金额 validate:required
     * @param float total - desc:优惠前总金额 validate:optional
     * @param int billing_cycle_time - desc:周期对应时间戳 validate:required
     * @return int status - desc:状态码 200应用优惠码成功 400应用优惠码失败
     * @return string msg - desc:返回信息
     * @return float data.discount - desc:优惠金额
     * @return array data.order_items - desc:订单子项
     * @return int data.order_items.host_id - desc:产品ID
     * @return string data.order_items.type - desc:优惠码表名 除前缀
     * @return int data.order_items.rel_id - desc:优惠码ID
     * @return float data.order_items.amount - desc:折扣金额
     * @return string data.order_items.description - desc:描述
     */
    public function client_promo_code(){}

    /*               后台钩子             */

    /**
     * 时间 2022-06-15
     * @title 接口删除后调用
     * @desc 接口删除后调用
     * @url after_server_delete
     * @author hh
     * @version v1
     * @param int id - desc:接口ID validate:required
     */
    public function after_server_delete(){}


    /**
     * 时间 2022-07-18
     * @title 订单生成后
     * @desc 订单生成后
     * @url after_order_create
     * @author wyh
     * @version v1
     * @param int id - desc:订单ID validate:required
     */
    public function after_order_create(){}

    /**
     * 时间 2022-06-17
     * @title 商品删除后调用
     * @desc 商品删除后调用
     * @url after_product_delete
     * @author hh
     * @version v1
     * @param int id - desc:商品ID validate:required
     */
    public function after_product_delete(){}

    /**
     * 时间 2022-07-20
     * @title 每日定时任务钩子
     * @desc 每日定时任务钩子
     * @url daily_cron
     * @author wyh
     * @version v1
     */
    public function daily_cron(){}

    /**
     * 时间 2022-07-20
     * @title 定时任务 每分钟执行一次hook
     * @desc 定时任务 每分钟执行一次hook
     * @url minute_cron
     * @author wyh
     * @version v1
     */
    public function minute_cron(){}

    /*               前台模板钩子             */

    public function template_client_after_host_list_button(){}

    public function template_client_host_list_on_table_header(){}

    public function template_client_promo_code(){}

    public function template_client_footer(){}

    /**
     * 时间 2022-07-07
     * @title 产品内页钩子
     * @url template_after_servicedetail_suspended
     * @author wyh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     */
    public function template_after_servicedetail_suspended(){}

    /*               后台模板钩子             */

    public function template_admin_footer(){}

    public function template_admin_after_host_list_button(){}

    /**
     * 时间 2022-07-20
     * @title 任务队列执行hook
     * @desc 任务队列执行hook
     * @url minute_cron
     * @author wyh
     * @version v1
     */
    public function task_run(){}

    /**
     * 时间 2024-02-18
     * @title 订单取消前
     * @desc 订单取消前
     * @url before_order_cancel
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     */
    public function before_order_cancel(){}

    /**
     * 时间 2023-01-04
     * @title 获取用户详情后
     * @desc 获取用户详情后
     * @url before_order_cancel
     * @author theworld
     * @version v1
     * @param int id - desc:用户ID validate:required
     */
    public function after_client_index(){}

    /**
     * 时间 2024-02-18
     * @title 添加接口分组后
     * @desc 添加接口分组后
     * @url after_server_group_create
     * @author wyh
     * @version v1
     * @param int id - desc:接口分组ID validate:required
     * @param object customfield - desc:自定义字段 键值对格式 validate:required
     */
    public function after_server_group_create(){}

    /**
     * 时间 2024-02-18
     * @title 编辑接口分组后
     * @desc 编辑接口分组后
     * @url after_server_group_edit
     * @author wyh
     * @version v1
     * @param int id - desc:接口分组ID validate:required
     * @param object customfield - desc:自定义字段 键值对格式 validate:required
     */
    public function after_server_group_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除接口分组后
     * @desc 删除接口分组后
     * @url after_server_group_delete
     * @author wyh
     * @version v1
     * @param int id - desc:接口分组ID validate:required
     */
    public function after_server_group_delete(){}

    /**
     * 时间 2024-02-18
     * @title 插件安装后
     * @desc 插件安装后
     * @url after_plugin_install
     * @author wyh
     * @version v1
     * @param string name - desc:插件标识 validate:required
     * @param object customfield - desc:自定义字段 键值对格式 validate:required
     */
    public function after_plugin_install(){}

    /**
     * 时间 2024-02-18
     * @title 插件卸载后
     * @desc 插件卸载后
     * @url after_plugin_uninstall
     * @author wyh
     * @version v1
     * @param string name - desc:插件标识 validate:required
     */
    public function after_plugin_uninstall(){}

    /**
     * 时间 2024-02-18
     * @title 插件升级后
     * @desc 插件升级后
     * @url after_plugin_upgrade
     * @author wyh
     * @version v1
     * @param string name - desc:插件标识 validate:required
     */
    public function after_plugin_upgrade(){}

    /**
     * 时间 2024-02-18
     * @title 应用优惠码
     * @desc 应用优惠码
     * @url apply_promo_code
     * @author wyh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @param float price - desc:金额 validate:required
     * @param string scene - desc:场景 validate:required
     * @param int duration - desc:周期时间 validate:required
     * @return int status - desc:状态码200
     * @return string msg - desc:返回消息
     * @return array data - desc:返回数据
     * @return boolean data.loop - desc:是否循环优惠
     * @return float data.discount - desc:优惠金额
     */
    public function apply_promo_code(){}

    /**
     * 时间 2024-02-18
     * @title 应用客户等级
     * @desc 应用客户等级
     * @url client_discount_by_amount
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param float amount - desc:金额 validate:required
     * @return int status - desc:状态码200
     * @return string msg - desc:返回消息
     * @return array data - desc:返回数据
     * @return float data.discount - desc:优惠金额
     */
    public function client_discount_by_amount(){}

    /**
     * 时间 2024-02-18
     * @title 删除产品续费日志
     * @desc 删除产品续费日志
     * @url delete_renew_log
     * @author wyh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     */
    public function delete_renew_log(){}

    /**
     * 时间 2024-02-18
     * @title 创建商品分组后
     * @desc 创建商品分组后
     * @url after_product_group_create
     * @author wyh
     * @version v1
     * @param int id - desc:商品分组ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_product_group_create(){}

    /**
     * 时间 2024-02-18
     * @title 编辑商品分组后
     * @desc 编辑商品分组后
     * @url after_product_group_edit
     * @author wyh
     * @version v1
     * @param int id - desc:商品分组ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_product_group_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除商品分组后
     * @desc 删除商品分组后
     * @url after_product_group_delete
     * @author wyh
     * @version v1
     * @param int id - desc:商品分组ID validate:required
     */
    public function after_product_group_delete(){}

    /**
     * 时间 2024-02-18
     * @title 在后台用户详情追加输出
     * @desc 在后台用户详情追加输出
     * @url admin_client_index
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @return array data - desc:追加数组
     */
    public function admin_client_index(){}

    /**
     * 时间 2024-02-18
     * @title 用户创建后（后台）
     * @desc 用户创建后（后台）
     * @url after_client_register
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_client_register(){}

    /**
     * 时间 2024-02-18
     * @title 修改用户前（后台）
     * @desc 修改用户前（后台）
     * @url before_client_edit
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function before_client_edit(){}

    /**
     * 时间 2024-02-18
     * @title 编辑用户后（后台）
     * @desc 编辑用户后（后台）
     * @url after_client_edit
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_client_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除用户后（后台）
     * @desc 删除用户后（后台）
     * @url after_client_delete
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     */
    public function after_client_delete(){}

    /**
     * 时间 2024-02-18
     * @title 搜索用户前（后台）
     * @desc 搜索用户前（后台）
     * @url before_search_client
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字 validate:required
     * @return array [] - desc:返回列表
     * @return int [].client_id - desc:客户ID
     */
    public function before_search_client(){}

    /**
     * 时间 2024-02-18
     * @title 客户注册前
     * @desc 客户注册前
     * @url before_client_register
     * @author wyh
     * @version v1
     * @param string type - desc:登录类型 phone手机注册 email邮箱注册 validate:required
     * @param string account - desc:手机号或邮箱 validate:required
     * @param string phone_code - desc:国家区号 登录类型为手机注册时需要传此参数 validate:optional
     * @param string username - desc:姓名 validate:optional
     * @param string code - desc:验证码 validate:required
     * @param string password - desc:密码 validate:required
     * @param string re_password - desc:重复密码 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     * @return array data - desc:自定义返回数据
     */
    public function before_client_register(){}

    /**
     * 时间 2024-02-18
     * @title 客户退出登录后
     * @desc 客户退出登录后
     * @url after_client_logout
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_client_logout(){}

    /**
     * 时间 2024-02-18
     * @title 客户登录后
     * @desc 客户登录后
     * @url after_client_login
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_client_login(){}

    /**
     * 时间 2024-02-18
     * @title 客户重置密码后
     * @desc 客户重置密码后
     * @url after_client_password_reset
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_client_password_reset(){}

    /**
     * 时间 2024-02-18
     * @title API鉴权登录完成
     * @desc API鉴权登录完成
     * @url client_api_login
     * @author wyh
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @param string username - desc:用户名 用户注册时的邮箱或手机号 validate:required
     * @param string password - desc:密码 api信息的token validate:required
     */
    public function client_api_login(){}

    /**
     * 时间 2024-02-18
     * @title 获取子账户父ID
     * @desc 获取子账户父ID
     * @url get_client_parent_id
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @return int result - desc:父ID
     */
    public function get_client_parent_id(){}

    /**
     * 时间 2024-02-18
     * @title 检查客户是否实名认证
     * @desc 检查客户是否实名认证
     * @url check_certification
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @return boolean result - desc:是否已实名认证
     */
    public function check_certification(){}

    /**
     * 时间 2024-02-18
     * @title 是否开启未认证无法充值功能
     * @desc 是否开启未认证无法充值功能
     * @url check_certification_recharge
     * @author wyh
     * @version v1
     * @return boolean result - desc:是否开启未认证无法充值功能
     */
    public function check_certification_recharge(){}

    /**
     * 时间 2024-02-18
     * @title 更新个人认证信息(需要安装实名认证插件)
     * @desc 更新个人认证信息(需要安装实名认证插件)
     * @url update_certification_person
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @param int status - desc:实名认证状态 1已认证 2未通过 3待审核 4已提交资料 validate:required
     * @param string auth_fail - desc:失败原因 validate:optional
     */
    public function update_certification_person(){}

    /**
     * 时间 2024-02-18
     * @title 更新企业认证信息(需要安装实名认证插件)
     * @desc 更新企业认证信息(需要安装实名认证插件)
     * @url update_certification_company
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @param int status - desc:实名认证状态 1已认证 2未通过 3待审核 4已提交资料 validate:required
     * @param string auth_fail - desc:失败原因 validate:optional
     */
    public function update_certification_company(){}

    /**
     * 时间 2024-02-18
     * @title 每五分钟定时任务钩子
     * @desc 每五分钟定时任务钩子
     * @url daily_cron
     * @method
     * @author wyh
     * @version v1
     */
    public function five_minute_cron(){}

    /**
     * 时间 2024-02-18
     * @title 产品第一次续费提醒前
     * @desc 产品第一次续费提醒前
     * @url before_host_renewal_first
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param int client_id - desc:客户ID validate:required
     */
    public function before_host_renewal_first(){}

    /**
     * 时间 2024-02-18
     * @title 删除未支付升降级订单前
     * @desc 删除未支付升降级订单前
     * @url before_delete_host_unpaid_upgrade_order
     * @author wyh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function before_delete_host_unpaid_upgrade_order(){}

    /**
     * 时间 2024-02-18
     * @title 删除未支付续费订单前
     * @desc 删除未支付续费订单前
     * @url before_delete_unpaid_renew_order
     * @author wyh
     * @version v1
     * @param array id - desc:订单ID数组 validate:required
     */
    public function before_delete_unpaid_renew_order(){}

    /**
     * 时间 2024-02-18
     * @title 获取客户产品ID
     * @desc 获取客户产品ID
     * @url get_client_host_id
     * @author wyh
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     * @return array data - desc:返回数据
     * @return array data.host - desc:产品ID数组
     */
    public function get_client_host_id(){}

    /**
     * 时间 2024-02-18
     * @title 搜索产品前
     * @desc 搜索产品前
     * @url before_search_host
     * @author wyh
     * @version v1
     * @param string keywords - desc:关键字 validate:required
     * @return array host_id - desc:产品ID数组
     */
    public function before_search_host(){}

    /**
     * 时间 2024-02-18
     * @title 产品编辑后
     * @desc 产品编辑后
     * @url after_host_edit
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_host_edit(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除后
     * @desc 产品删除后
     * @url after_host_delete
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param string module - desc:模块名称 validate:required
     */
    public function after_host_delete(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建后
     * @desc 产品创建后
     * @url before_host_create
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function before_host_create(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建成功后
     * @desc 产品创建成功后
     * @url after_host_create_success
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_create_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建失败后
     * @desc 产品创建失败后
     * @url after_host_create_fail
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_create_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停前
     * @desc 产品暂停前
     * @url before_host_suspend
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function before_host_suspend(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停成功后
     * @desc 产品暂停成功后
     * @url after_host_suspend_success
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_suspend_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停失败后
     * @desc 产品暂停失败后
     * @url after_host_suspend_fail
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_suspend_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停前
     * @desc 产品解除暂停前
     * @url before_host_unsuspend
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function before_host_unsuspend(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停成功后
     * @desc 产品解除暂停成功后
     * @url after_host_unsuspend_success
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_unsuspend_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停失败后
     * @desc 产品解除暂停失败后
     * @url after_host_unsuspend_fail
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_unsuspend_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除前
     * @desc 产品删除前
     * @url before_host_terminate
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function before_host_terminate(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除成功后
     * @desc 产品删除成功后
     * @url after_host_terminate_success
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_terminate_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除失败后
     * @desc 产品删除失败后
     * @url after_host_terminate_fail
     * @author wyh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_terminate_fail(){}

    /**
     * 时间 2024-02-18
     * @title 商品自定义字段
     * @desc 商品自定义字段
     * @url product_detail_custom_fields
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @return object result - desc:自定义字段键值对
     */
    public function product_detail_custom_fields(){}

    /**
     * 时间 2024-02-18
     * @title 商品创建后
     * @desc 商品创建后
     * @url after_product_create
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_product_create(){}

    /**
     * 时间 2024-02-18
     * @title 商品编辑后
     * @desc 商品编辑后
     * @url after_product_edit
     * @author wyh
     * @version v1
     * @param int id - desc:商品ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_product_edit(){}

    /**
     * 时间 2024-02-18
     * @title 商品复制后
     * @desc 商品复制后
     * @url after_product_copy
     * @author wyh
     * @version v1
     * @param int id - desc:复制后商品ID validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param int son_product_id - desc:子商品ID validate:required
     * @param object customfield - desc:自定义字段 validate:required
     */
    public function after_product_copy(){}

    /**
     * 时间 2024-03-07
     * @title 推介计划续费订单支付后
     * @desc 推介计划续费订单支付后
     * @url recommend_renew_order_paid
     * @author wyh
     * @version v1
     * @param int id - desc:订单ID validate:required
     */
    public function recommend_renew_order_paid(){}

    /**
     * 时间 2024-02-18
     * @title 活动促销折扣
     * @desc 活动促销折扣
     * @url event_promotion_by_amount
     * @author wyh
     * @version v1
     * @param int event_promotion - desc:活动ID validate:required
     * @param int product_id - desc:商品ID validate:required
     * @param int qty - desc:数量 validate:required
     * @param float amount - desc:金额 validate:required
     * @param int billing_cycle_time - desc:周期时间 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     * @return array data - desc:返回数据
     * @return float data.discount - desc:折扣金额
     */
    public function event_promotion_by_amount(){}

    /**
     * 时间 2024-03-20
     * @title 在产品软删除后
     * @desc 在产品软删除后
     * @url after_host_soft_delete
     * @author hh
     * @version v1
     * @param int id - desc:产品ID validate:required
     */
    public function after_host_soft_delete(){}

    /**
     * 时间 2024-04-09
     * @title 在订单放入回收站前
     * @desc 在订单放入回收站前
     * @url before_order_recycle
     * @author hh
     * @version v1
     * @param int id - desc:订单ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function before_order_recycle(){}

    /**
     * 时间 2024-04-10
     * @title 通用自定义字段
     * @desc 通用自定义字段(钩子返回数据会放在console/v1/common通用接口返回的custom_fields字段下)
     * @url common_custom_fields
     * @author wyh
     * @version v1
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     * @return array data - desc:追加数据 建议返回关联数组防止冲突
     */
    public function common_custom_fields(){}

    /**
     * 时间 2024-04-19
     * @title 产品续费前,单个和批量续费
     * @desc 产品续费前,单个和批量续费
     * @url before_host_renew
     * @author theworld
     * @version v1
     * @param int|array host_id - desc:产品ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function before_host_renew(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款后
     * @desc 产品申请退款后
     * @url after_host_refund_create
     * @author theworld
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @param mixed suspend_reason - desc:停用原因 产品可以自定义时传字符串 不可自定义时传停用原因ID数组 validate:optional
     * @param string type - desc:停用时间 Expire到期 Immediate立即 validate:optional
     */
    public function after_host_refund_create(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款取消后
     * @desc 产品申请退款取消后
     * @url after_host_refund_cancel
     * @author theworld
     * @version v1
     * @param int id - desc:停用申请ID validate:required
     */
    public function after_host_refund_cancel(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款通过后
     * @desc 产品申请退款通过后
     * @url after_host_refund_pending
     * @author theworld
     * @version v1
     * @param int id - desc:停用申请ID validate:required
     */
    public function after_host_refund_pending(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款驳回后
     * @desc 产品申请退款驳回后
     * @url after_host_refund_reject
     * @author theworld
     * @version v1
     * @param int id - desc:停用申请ID validate:required
     * @param string reject_reason - desc:驳回原因 validate:required
     */
    public function after_host_refund_reject(){}

    /**
     * 时间 2024-04-22
     * @title 系统设置自定义输出
     * @desc 系统设置自定义输出
     * @url configuration_system_list
     * @author hh
     * @version v1
     * @return int status - desc:状态码 200成功 400失败
     * @return array data - desc:追加数据 建议返回关联数组防止冲突
     */
    public function configuration_system_list(){}

    /**
     * 时间 2024-04-22
     * @title 保存系统设置前
     * @desc 保存系统设置前
     * @url before_configuration_system_update
     * @author hh
     * @version v1
     * @param string lang_admin - desc:后台默认语言 validate:required
     * @param int lang_home_open - desc:前台多语言开关 1开启 0关闭 validate:required
     * @param string lang_home - desc:前台默认语言 validate:required
     * @param int maintenance_mode - desc:维护模式开关 1开启 0关闭 validate:required
     * @param string maintenance_mode_message - desc:维护模式内容 validate:required
     * @param string website_name - desc:网站名称 validate:required
     * @param string website_url - desc:网站域名地址 validate:required
     * @param string terms_service_url - desc:服务条款地址 validate:required
     * @param string terms_privacy_url - desc:隐私条款地址 validate:required
     * @param string system_logo - desc:系统LOGO validate:required
     * @param int client_start_id_value - desc:用户注册开始ID validate:required
     * @param int order_start_id_value - desc:用户订单开始ID validate:required
     * @param string clientarea_url - desc:会员中心地址 validate:required
     * @param string tab_logo - desc:标签页LOGO validate:required
     * @param int home_show_deleted_host - desc:前台是否展示已删除产品 1是 0否 validate:required
     * @param object customfield - desc:自定义参数 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function before_configuration_system_update(){}

    /**
     * 时间 2024-04-22
     * @title 保存系统设置后
     * @desc 保存系统设置后
     * @url after_configuration_system_update
     * @author hh
     * @version v1
     * @param string lang_admin - desc:后台默认语言 validate:required
     * @param int lang_home_open - desc:前台多语言开关 1开启 0关闭 validate:required
     * @param string lang_home - desc:前台默认语言 validate:required
     * @param int maintenance_mode - desc:维护模式开关 1开启 0关闭 validate:required
     * @param string maintenance_mode_message - desc:维护模式内容 validate:required
     * @param string website_name - desc:网站名称 validate:required
     * @param string website_url - desc:网站域名地址 validate:required
     * @param string terms_service_url - desc:服务条款地址 validate:required
     * @param string terms_privacy_url - desc:隐私条款地址 validate:required
     * @param string system_logo - desc:系统LOGO validate:required
     * @param int client_start_id_value - desc:用户注册开始ID validate:required
     * @param int order_start_id_value - desc:用户订单开始ID validate:required
     * @param string clientarea_url - desc:会员中心地址 validate:required
     * @param string tab_logo - desc:标签页LOGO validate:required
     * @param int home_show_deleted_host - desc:前台是否展示已删除产品 1是 0否 validate:required
     * @param object customfield - desc:自定义参数 validate:required
     */
    public function after_configuration_system_update(){}

    /**
     * 时间 2024-04-19
     * @title 前台商品详情
     * @desc 前台商品详情
     * @url home_product_index
     * @author theworld
     * @version v1
     * @param int id - desc:商品ID validate:required
     */
    public function home_product_index(){}

    /**
     * 时间 2024-04-29
     * @title 前台购物车详情
     * @desc 前台购物车详情
     * @url home_cart_index
     * @author theworld
     * @version v1
     * @return array cart - desc:计算后数据
     * @return int cart[].product_id - desc:商品ID
     * @return object cart[].config_options - desc:自定义配置
     * @return int cart[].qty - desc:数量
     * @return object cart[].customfield - desc:自定义参数
     * @return string cart[].name - desc:商品名称
     * @return string cart[].description - desc:商品描述
     * @return int cart[].stock_control - desc:库存控制 0关闭 1启用
     * @return int cart[].stock_qty - desc:库存数量
     * @return object cart[].self_defined_field - desc:自定义字段 键为自定义字段ID 值为填写内容
     */
    public function home_cart_index(){}

    /**
     * 时间 2024-05-16
     * @title 获取用户自定义字段值
     * @desc 获取用户自定义字段值
     * @url get_client_custom_field_list
     * @author hh
     * @version v1
     * @param array param.client_id - desc:用户ID validate:required
     * @return array result - desc:返回数据 键为用户ID 值为自定义字段ID与值的映射
     */
    public function get_client_custom_field_list(){}

    /**
     * 时间 2024-05-16
     * @title 获取用户等级
     * @desc 获取用户等级,返回例如['1'=>['name'=>'等级A','background_color'=>'rgba(rgba(172,239,210,1))']]
     * @url get_client_level_list
     * @author hh
     * @version v1
     * @param array param.client_id - desc:用户ID validate:required
     * @return string [client_id].name - desc:用户等级名称 键为对应用户ID
     * @return string [client_id].background_color - desc:背景颜色 键为对应用户ID
     */
    public function get_client_level_list(){}

    /**
     * 时间 2024-05-21
     * @title 获取模板控制器Tab
     * @desc 获取模板控制器Tab,返回例如[['name'=>'test','title'=>'测试','url'=>'test.htm']]
     * @url template_tab_list
     * @author theworld
     * @version v1
     * @param array list - desc:默认模板控制器Tab列表 validate:required
     * @param string list[].name - desc:标识 validate:required
     * @param string list[].title - desc:标题 validate:required
     * @param string list[].url - desc:地址 validate:required
     * @return array list - desc:修改后模板控制器Tab列表
     * @return string list[].name - desc:标识
     * @return string list[].title - desc:标题
     * @return string list[].url - desc:地址
     */
    public function template_tab_list(){}

    /**
     * 时间 2024-05-28
     * @title 自定义官网页面SEO
     * @desc 自定义官网页面SEO
     * @url web_seo_custom
     * @author theworld
     * @version v1
     * @param string tpl_name - desc:当前页面模板名 validate:required
     * @param string url - desc:当前页面网址 validate:required
     * @return string title - desc:标题
     * @return string keywords - desc:关键字
     * @return string description - desc:描述
     * @return int pub_date - desc:推送时间
     * @return int up_date - desc:修改时间
     */
    public function web_seo_custom(){}

    /**
     * 时间 2024-05-28
     * @title 自定义官网页面数据
     * @desc 自定义官网页面数据
     * @url web_data_custom
     * @author theworld
     * @version v1
     * @param string tpl_name - desc:当前页面模板名 validate:required
     * @param string url - desc:当前页面网址 validate:required
     * @return object data - desc:自定义数据
     */
    public function web_data_custom(){}

    /**
     * 时间 2024-06-06
     * @title 删除通知动作后
     * @desc 删除通知动作后
     * @url after_notice_action_delete
     * @author theworld
     * @version v1
     * @param string name - desc:动作英文标识 validate:required
     */
    public function after_notice_action_delete(){}

    /**
     * 时间 2024-06-06
     * @title 任务执行后
     * @desc 任务执行后
     * @url after_task_run
     * @author theworld
     * @version v1
     * @param int task_id - desc:任务ID validate:required
     * @param string type - desc:名称 sms短信发送 email邮件发送 host_create开通主机 host_suspend暂停主机 host_unsuspend解除暂停 host_terminate删除主机 validate:required
     * @param string task_data - desc:任务要执行的数据 validate:required
     */
    public function after_task_run(){}

    /**
     * 时间 2024-06-21
     * @title 在前台用户详情追加输出
     * @desc 在前台用户详情追加输出
     * @url home_client_index
     * @author theworld
     * @version v1
     * @param int id - desc:客户ID validate:required
     * @return array data - desc:追加数组
     */
    public function home_client_index(){}

    /**
     * 时间 2024-08-15
     * @title 插件用户限制,限制可查看的用户数据
     * @desc 插件用户限制,限制可查看的用户数据
     * @url plugin_check_client_limit
     * @author theworld
     * @version v1
     * @param int client_id - desc:客户ID validate:required
     * @return int status - desc:状态码 200成功 400失败 400时不可查看用户数据
     */
    public function plugin_check_client_limit(){}

    /**
     * 时间 2024-08-15
     * @title 用户列表查询追加条件
     * @desc 用户列表查询追加条件
     * @url home_client_index
     * @author theworld
     * @version v1
     * @param object param - desc:参数 validate:required
     * @param string app - desc:所属端 home前台 admin后台 validate:required
     * @param object query - desc:查询对象 validate:required
     */
    public function client_list_where_query_append(){}

    /**
     * 时间 2024-08-15
     * @title 订单列表查询追加条件
     * @desc 订单列表查询追加条件
     * @url home_client_index
     * @author theworld
     * @version v1
     * @param object param - desc:参数 validate:required
     * @param string app - desc:所属端 home前台 admin后台 validate:required
     * @param object query - desc:查询对象 validate:required
     */
    public function order_list_where_query_append(){}

    /**
     * 时间 2024-08-15
     * @title 产品列表查询追加条件
     * @desc 产品列表查询追加条件
     * @url home_client_index
     * @author theworld
     * @version v1
     * @param object param - desc:参数 validate:required
     * @param string app - desc:所属端 home前台 admin后台 validate:required
     * @param object query - desc:查询对象 validate:required
     */
    public function host_list_where_query_append(){}

    /**
     * 时间 2024-10-21
     * @title 删除供应商之前
     * @desc 删除供应商之前
     * @url before_supplier_delete
     * @author hh
     * @version v1
     * @param int id - desc:代理商ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function before_supplier_delete(){}

    /**
     * 时间 2024-11-13
     * @title 创建产品IP之后
     * @desc 创建产品IP之后
     * @url after_host_ip_create
     * @author wyh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function after_host_ip_create(){}

    /**
     * 时间 2024-11-15
     * @title 在产品升降级之前
     * @desc 在产品升降级之前
     * @url before_host_upgrade
     * @author hh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @param string scene_desc - desc:升级场景描述 如变更配置 购买磁盘 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function before_host_upgrade(){}

    /**
     * @时间 2024-11-18
     * @title 前台导航显示条件
     * @desc 前台导航显示条件
     * @author hh
     * @version v1
     * @param \think\db\Query query - desc:PluginModel查询对象 validate:required
     */
    public function home_menu_plugin_where_query_append(){}

    /**
     * @时间 2024-11-25
     * @title 产品转移后
     * @desc 产品转移后
     * @author hh
     * @version v1
     * @param int host_id - desc:产品ID validate:required
     * @param int old_client_id - desc:原用户ID validate:required
     * @param int new_client_id - desc:新用户ID validate:required
     */
    public function after_host_transfer(){}

    /**
     * @时间 2024-12-04
     * @title 退款记录审核通过(目前只适用信用额)
     * @desc 退款记录审核通过(目前只适用信用额)
     * @author hh
     * @version v1
     * @param object order - desc:订单模型实例 validate:required
     * @param float refund_gateway - desc:退款渠道金额 validate:required
     * @param float refund_credit - desc:退款余额金额 validate:required
     * @param object refund_record - desc:退款记录模型实例 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:信息
     */
    public function refund_record_pending(){}

    /**
     * @时间 2024-12-04
     * @title 商品列表查询追加条件
     * @desc 商品列表查询追加条件
     * @author wyh
     * @version v1
     * @param object product_object - desc:商品对象 validate:required
     * @param string field - desc:字段 引用传递 最后该钩子字段会与系统字段一起返回 validate:required
     * @param array param - desc:前端所传参数 validate:required
     * @param bool clientarea - desc:是否前台 validate:required
     */
    public function product_left_join_append(){}

    /**
     * @时间 2024-12-04
     * @title 系统通知
     * @desc 系统通知
     * @author hh
     * @version v1
     * @param string name - desc:发送动作 validate:required
     * @param array task_data - desc:任务数据 validate:required
     * @param int task_data.order_id - desc:获取订单/用户相关参数 validate:required
     * @param int task_data.host_id - desc:获取产品/用户相关参数 validate:required
     * @param int task_data.client_id - desc:获取用户相关参数 发送给用户必传 validate:required
     * @param int task_data.product_id - desc:商品ID 当传入正确产品ID且是商品全局动作时自动添加 validate:required
     * @param array task_data.template_param - desc:模板变量 validate:required
     * @param array|null notice_type - desc:支持的通知方式 NULL代表所有动作都可以 validate:required
     */
    public function system_notice(){}

    /**
     * @时间 2024-12-04
     * @title 删除邮件模板之后
     * @desc 删除邮件模板之后
     * @author hh
     * @version v1
     * @param int id - desc:邮件模板ID validate:required
     */
    public function after_email_template_delete(){}

    /**
     * @时间 2025-04-01
     * @title 修改商品按需计费配置前
     * @desc 修改商品按需计费配置前
     * @author hh
     * @version v1
     * @param int param.product_id - desc:商品ID validate:required
     * @param string param.billing_cycle_unit - desc:出账周期单位 hour每小时 day每天 month每月 validate:required
     * @param int param.billing_cycle_day - desc:出账周期号数 billing_cycle_unit=month时必填 validate:optional
     * @param string param.billing_cycle_point - desc:出账周期时间点 如00:00 billing_cycle_unit=day/month时必填 validate:optional
     * @param int param.duration_id - desc:周期ID validate:optional
     * @param float param.duration_ratio - desc:周期比例 duration_id>0时必填 validate:optional
     * @param float param.min_credit - desc:购买时用户最低余额 validate:required
     * @param int param.min_usage_time - desc:最低使用时长 validate:required
     * @param string param.min_usage_time_unit - desc:最低使用时长单位 second秒 minute分 hour小时 validate:required
     * @param int param.grace_time - desc:宽限期 validate:required
     * @param string param.grace_time_unit - desc:宽限期单位 hour小时 day天 validate:required
     * @param int param.keep_time - desc:保留期 validate:required
     * @param string param.keep_time_unit - desc:保留期单位 hour小时 day天 validate:required
     * @param array param.keep_time_billing_item - desc:保留计费项目标识 validate:optional
     * @param float param.initial_fee - desc:初装费 validate:optional
     * @param int param.client_auto_delete - desc:允许用户设置自动释放 0否 1是 validate:optional
     * @param int param.on_demand_to_duration - desc:允许按需转包年包月 0否 1是 validate:optional
     * @param int param.duration_to_on_demand - desc:允许包年包月/试用转按需 0否 1是 validate:optional
     * @param int param.credit_limit_pay - desc:允许信用额支付 0否 1是 validate:optional
     * @param string module - desc:模块类型 validate:optional
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function before_product_on_demand_update(){}

    /**
     * @时间 2025-04-01
     * @title 获取产品使用流量用于计费
     * @desc 获取产品使用流量用于计费
     * @author hh
     * @version v1
     * @param HostModel host - desc:产品模型实例 validate:required
     * @param string module - desc:模块类型 validate:required
     * @param int start_time - desc:开始时间 秒级时间戳 validate:required
     * @param int end_time - desc:结束时间 秒级时间戳 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     * @return float data.flow - desc:使用流量 单位GB
     * @return bool data.support - desc:是否支持按需流量计费
     */
    public function get_host_flow(){}

    /**
     * @时间 2025-04-02
     * @title 自动直接支付订单
     * @desc 自动直接支付订单
     * @author hh
     * @version v1
     * @param int order_id - desc:订单ID validate:required
     * @param bool is_admin - desc:是否管理员操作 validate:required
     * @param string gateway - desc:支付方式标识 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function auto_direct_pay_order(){}

    /**
     * @时间 2025-04-07
     * @title 在生成变更计费方式订单之前
     * @desc 在生成变更计费方式订单之前
     * @author hh
     * @version v1
     * @param object host - desc:产品模型实例 validate:required
     * @param string module - desc:模块类型 validate:required
     * @param string old_billing_cycle - desc:原计费方式 validate:required
     * @param string new_billing_cycle - desc:新计费方式 validate:required
     * @return int status - desc:状态码 200成功 400失败
     * @return string msg - desc:提示信息
     */
    public function before_host_change_billing_cycle_order_create(){}

    /**
     * @时间 2025-12-04
     * @title 会员中心模板页面入口钩子
     * @desc 会员中心模板页面入口钩子
     * @author wyh
     * @version v1
     */
    public function clientarea_index(){}

    /**
     * @时间 2025-04-07
     * @title 追加访问设置首选方式
     * @desc 追加访问设置首选方式
     * @author hh
     * @version v1
     * @return array result - desc:首选方式列表
     * @return string value - desc:首选方式标识
     * @return string name - desc:首选方式名称
     */
    public function append_first_login_type(){}

    /**
     * @时间 2025-07-11
     * @title 追加公共发送参数
     * @desc 追加公共发送参数
     * @author theworld
     * @version v1
     * @return array result - desc:公共发送参数列表
     * @return string label - desc:标签
     * @return array param - desc:参数
     * @return string param[].value - desc:值
     * @return string param[].label - desc:标签
     */
    public function append_send_param(){}

    /**
     * 时间 2026-01-05
     * @title 商品自然月预付费开关变更
     * @desc 商品自然月预付费开关变更
     * @url product_natural_month_prepaid_change
     * @author hh
     * @version v1
     * @param int product_id - desc:商品ID validate:required
     * @param string module - desc:模块名称 validate:required
     * @param int status - desc:开启状态 0关闭 1开启 validate:required
     */
    public function product_natural_month_prepaid_change(){}
}
<?php
use think\facade\Route;
Route::pattern([
    'id'   => '\d+',
    'page' => '\d+',
    'limit' => '\d+|max:50',
    'sort'   =>  'in:asc,desc',
    'hidden'   => '\d+',
]);
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 开放接口
Route::group(DIR_ADMIN.'/v1',function (){
    Route::get('login', 'admin/public/loginInfo'); # 登录信息
    Route::post('login', 'admin/public/login'); # 登录
    Route::get('captcha', 'admin/public/captcha'); # 图形验证码
    Route::post('phone/code', 'admin/public/sendPhoneCode'); # 发送手机验证码
    Route::post('email/code', 'admin/public/sendEmailCode'); # 发送邮件验证码
    Route::post('second/verify/method', 'admin/public/getSecondVerifyMethod'); # 获取二次验证方式
//    Route::get('test', 'admin/public/test'); #测试接口
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ]
);

Route::get('v1/doc', 'admin/doc/index'); #获取开发文档
Route::post('v1/doc', 'admin/doc/create'); #生成开发文档

# 应用商店
Route::post(DIR_ADMIN.'/v1/app_market/check_token', 'admin/appMarket/checkToken'); # 校验token
Route::post(DIR_ADMIN.'/v1/app_market/app/:id/install', 'admin/appMarket/install'); # 安装应用

Route::group(DIR_ADMIN.'/v1',function (){
    Route::post('logout', 'admin/admin/logout'); #注销
    Route::post('clientarea_theme_color', 'admin/common/setClientareaThemeColor'); # 设置客户端主题色

	# 管理员管理
    Route::get('admin', 'admin/admin/adminList'); # 管理员列表
    Route::get('admin/:id', 'admin/admin/index'); # 获取单个管理员
    Route::post('admin', 'admin/admin/create'); # 添加管理员
    Route::put('admin/:id', 'admin/admin/update'); # 修改管理员
    Route::delete('admin/:id', 'admin/admin/delete'); # 删除管理员
    Route::put('admin/:id/status', 'admin/admin/status'); # 管理员状态切换
    Route::put('admin/password/update', 'admin/admin/updatePassword'); # 修改管理员密码
    Route::get('login_info', 'admin/admin/currentAdmin'); # 获取当前管理员信息
    Route::put('admin/operate_password', 'admin/admin/updateAdminOperatePassword'); # 修改管理员操作密码
    Route::put('admin/nickname', 'admin/admin/updateAdminNickname'); # 修改管理员姓名
    Route::post('admin/verify_old_phone', 'admin/admin/verifyOldPhone'); # 验证原手机
    Route::put('admin/phone', 'admin/admin/updatePhone'); # 修改手机
    Route::post('admin/verify_old_email', 'admin/admin/verifyOldEmail'); # 验证原邮箱
    Route::put('admin/email', 'admin/admin/updateEmail'); # 修改邮箱
    Route::get('admin/totp', 'admin/admin/getTotp'); # 获取TOTP密钥
    Route::put('admin/totp', 'admin/admin/bindTotp'); # 绑定TOTP
    Route::delete('admin/totp', 'admin/admin/unbindTotp'); # 解绑TOTP
    Route::delete('admin/:id/totp', 'admin/admin/adminUnbindTotp'); # 管理员解绑其他管理员TOTP
    Route::delete('admin/:id/lock', 'admin/admin/adminUnlock'); # 管理员解锁其他管理员

	# 管理员分组管理
    Route::get('admin/role', 'admin/adminRole/adminRoleList'); # 管理员分组列表
    Route::get('admin/role/:id', 'admin/adminRole/index'); # 获取单个管理员分组
    Route::post('admin/role', 'admin/adminRole/create'); # 添加管理员分组
    Route::put('admin/role/:id', 'admin/adminRole/update'); # 修改管理员分组
    Route::delete('admin/role/:id', 'admin/adminRole/delete'); # 删除管理员分组
	
	# 用户管理
	Route::get('client', 'admin/client/clientList'); # 用户列表
	Route::get('client/:id', 'admin/client/index'); # 获取单个用户
    Route::post('client', 'admin/client/create'); # 添加用户
    Route::put('client/:id', 'admin/client/update'); # 修改用户
    Route::get('client/search', 'admin/client/search'); # 搜索用户
    Route::post('client/:id/login', 'admin/client/login'); # 以用户登录
    Route::delete('client/:id', 'admin/client/delete')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'client_delete'); # 删除用户
    Route::put('client/:id/status', 'admin/client/status')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'update_client_status'); # 用户状态切换
    Route::put('client/:id/receive_sms', 'admin/client/receiveSms'); # 修改用户接收短信
    Route::put('client/:id/receive_email', 'admin/client/receiveEmail'); # 修改用户接收邮件

    # 用户余额管理
    Route::get('client/:id/credit', 'admin/clientCredit/clientCreditList'); # 用户余额变更记录列表
    Route::put('client/:id/credit', 'admin/clientCredit/update'); # 更改用户余额
    Route::post('client/:id/recharge', 'admin/clientCredit/recharge'); # 充值
    Route::post('client/:client_id/credit/freeze', 'admin/clientCredit/freeze'); # 冻结余额
    Route::get('client/:client_id/credit/freeze', 'admin/clientCredit/freezeList'); # 冻结记录
    Route::post('client/:client_id/credit/unfreeze', 'admin/clientCredit/unfreeze'); # 解冻余额


    # 订单管理
    Route::get('order', 'admin/order/orderList'); # 订单列表
    Route::get('order/:id', 'admin/order/index'); # 获取单个订单
    Route::post('order', 'admin/order/create'); # 添加订单
    Route::put('order/:id/amount', 'admin/order/updateAmount'); # 修改订单金额
    Route::put('order/item/:id', 'admin/order/updateOrderItem'); # 编辑人工调整的订单子项
    Route::delete('order/item/:id', 'admin/order/deleteOrderItem'); # 删除人工调整的订单子项
    Route::put('order/:id/status/paid', 'admin/order/paid'); # 标记支付
    Route::delete('order/:id', 'admin/order/delete')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'order_delete'); # 删除订单
    Route::delete('order', 'admin/order/batchDelete')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'order_delete'); # 批量删除订单
    Route::post('order/upgrade/amount', 'admin/order/getUpgradeAmount'); # 获取升降级订单金额
    Route::post('order/:id/refund', 'admin/order/orderRefund'); # 订单退款
    Route::post('order/:id/apply_credit', 'admin/order/orderApplyCredit'); # 订单退款
    Route::post('order/:id/remove_credit', 'admin/order/orderRemoveCredit'); # 订单退款
    Route::get('order/:id/refund_record', 'admin/order/refundRecordList'); # 订单退款记录列表
    Route::delete('refund_record/:id', 'admin/order/deleteRefundRecord'); # 删除退款记录
    Route::put('order/:id/gateway', 'admin/order/updateGateway'); # 修改订单支付方式
    Route::put('order/:id/notes', 'admin/order/updateNotes'); # 修改订单备注
    Route::post('product/settle', 'admin/order/settle'); # 后台结算商品
    Route::get('product/:id/config_option', 'admin/order/moduleClientConfigOption'); # 商品配置页面
    Route::get('order/recycle_bin/config', 'admin/order/getOrderRecycleBinConfig'); # 获取订单回收站设置
    Route::post('order/recycle_bin/enable', 'admin/order/enableOrderRecycleBin'); # 开启订单回收站
    Route::put('order/recycle_bin/config', 'admin/order/orderRecycleBinConfigUpdate'); # 修改订单回收站设置
    Route::get('order/recycle_bin', 'admin/order/recycleBinOrderList'); # 订单回收站列表
    Route::post('order/recycle_bin/recover', 'admin/order/recoverOrder'); # 恢复订单
    Route::delete('order/recycle_bin', 'admin/order/deleteOrderFromRecycleBin')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'clear_order_recycle');; # 从回收站删除订单
    Route::post('order/recycle_bin/clear', 'admin/order/clearRecycleBin')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'clear_order_recycle'); # 清空回收站
    Route::post('order/lock', 'admin/order/lockOrder'); # 锁定订单
    Route::post('order/unlock', 'admin/order/unlockOrder'); # 取消锁定订单
    Route::put('order/:id/voucher', 'admin/order/uploadOrderVoucher'); # 上传凭证
    Route::post('order/:id/review', 'admin/order/reviewOrder'); # 审核订单
    Route::get('order/:id/host_refund', 'admin/order/orderHostRefundList'); # 订单内页产品退款列表
    Route::get('order/:id/refund_amount', 'admin/order/orderRefundIndex'); # 计算订单可退金额

    Route::put('refund_record/:id/pending', 'admin/order/pendingRefundRecord'); # 退款通过
    Route::put('refund_record/:id/reject', 'admin/order/rejectRefundRecord'); # 退款拒绝
    Route::put('refund_record/:id/refunded', 'admin/order/redundedRefundRecord'); # 已退款

    # 产品管理
    Route::get('host', 'admin/host/hostList'); # 产品列表
    Route::get('host/:id', 'admin/host/index'); # 获取单个产品
    Route::put('host/:id', 'admin/host/update'); # 修改产品
    Route::get('host/:id/module', 'admin/host/adminArea'); # 产品内页模块
    Route::get('host/:id/module/button', 'admin/host/moduleButton'); # 模块按钮输出
    Route::get('host/:id/module/field', 'admin/host/moduleField'); # 模块输入框输出
    Route::get('client/:id/host/all', 'admin/host/clientHost'); # 获取用户所有产品
    Route::get('host/:id/ip', 'admin/host/hostIpIndex'); # 获取产品IP信息
    Route::get('host/:id/module_operate', 'admin/host/adminAreaModuleOperate'); # 获取产品IP信息
    Route::get('host/failed_action', 'admin/host/failedActionHostList'); # 手动处理产品列表
    Route::post('host/:id/mark_processed', 'admin/host/failedActionMarkProcessed'); # 标记已处理
    Route::post('host/sync', 'admin/host/batchSyncAccount'); # 批量同步
    Route::post('host/retry', 'admin/host/failedActionRetry'); # 标记已处理
    Route::post('host/:id/push_downstream', 'admin/host/pushDownstream'); # 推送上游信息至下游

    #交易流水管理
    Route::get('transaction', 'admin/transaction/transactionList'); # 交易流水列表
    Route::post('transaction', 'admin/transaction/create'); # 添加交易流水
    Route::put('transaction/:id', 'admin/transaction/update'); # 编辑交易流水
    Route::delete('transaction/:id', 'admin/transaction/delete'); # 删除交易流水

    #任务管理
    Route::get('task', 'admin/task/taskList'); # 任务列表
    Route::put('task/:id/retry', 'admin/task/retry'); # 重试

    #日志管理
    Route::get('log/system', 'admin/log/systemLogList'); # 系统日志列表
    Route::get('log/notice/email', 'admin/log/emailLogList'); # 邮件通知日志列表
    Route::get('log/notice/sms', 'admin/log/smsLogList'); # 短信通知日志列表

	# 配置项
    Route::get('configuration/system', 'admin/Configuration/systemList'); # 获取系统设置
    Route::put('configuration/system', 'admin/Configuration/systemUpdate'); # 保存系统设置
	Route::get('configuration/login', 'admin/Configuration/loginList'); # 获取登录设置
    Route::put('configuration/login', 'admin/Configuration/loginUpdate'); # 保存登录设置
	Route::get('configuration/security', 'admin/Configuration/securityList'); # 获取安全设置
    Route::put('configuration/security', 'admin/Configuration/securityUpdate'); # 保存安全设置
	Route::get('configuration/security/captcha', 'admin/Configuration/securityCaptcha'); # 图形验证码预览
	Route::get('configuration/currency', 'admin/Configuration/currencyList'); # 获取货币设置
    Route::put('configuration/currency', 'admin/Configuration/currencyUpdate'); # 保存货币设置
	Route::get('configuration/cron', 'admin/Configuration/cronList'); # 获取定时任务
    Route::put('configuration/cron', 'admin/Configuration/cronUpdate'); # 保存定时任务
    Route::get('configuration/theme', 'admin/Configuration/themeList'); # 获取主题设置
    Route::put('configuration/theme', 'admin/Configuration/themeUpdate'); # 保存主题设置
    Route::get('configuration/info', 'admin/Configuration/infoList'); # 获取信息配置
    Route::put('configuration/info', 'admin/Configuration/infoUpdate'); # 保存信息配置
    Route::get('configuration/debug', 'admin/Configuration/debugInfo'); # debug页面
    Route::put('configuration/debug', 'admin/Configuration/debug'); # 保存debug页面
    
    # 缓存管理
    Route::post('cache/clear_all', 'admin/cache/clearAll'); # 清除所有缓存
    Route::post('cache/clear_plugin', 'admin/cache/clearPlugin'); # 清除插件缓存
    Route::post('cache/clear_config', 'admin/cache/clearConfig'); # 清除配置缓存
    Route::post('cache/clear_lang', 'admin/cache/clearLang'); # 清除语言缓存
    Route::post('cache/clear_route', 'admin/cache/clearRoute'); # 清除路由缓存
    Route::get('cache/stats', 'admin/cache/stats'); # 获取缓存统计
    Route::get('configuration/oss', 'admin/Configuration/getOssConfig'); # 对象存储页面
    Route::put('configuration/oss', 'admin/Configuration/ossConfig'); # 保存对象存储页面
    Route::get('configuration/product', 'admin/Configuration/productGlobalSetting'); # 获取商品全局设置
    Route::put('configuration/product', 'admin/Configuration/productGlobalSettingUpdate'); # 保存商品全局设置
    Route::get('configuration/tourist_visible_product', 'admin/Configuration/touristVisibleProduct'); # 游客可见商品
    Route::put('configuration/tourist_visible_product', 'admin/Configuration/touristVisibleProductUpdate'); # 游客可见商品
    Route::get('configuration/supplier_credit_warning', 'admin/Configuration/supplierCreditWarning'); # 获取代理商余额预警设置
    Route::put('configuration/supplier_credit_warning', 'admin/Configuration/supplierCreditWarningUpdate'); # 保存代理商余额预警设置
    Route::get('configuration/upstream_intercept', 'admin/Configuration/upstreamInterceptList'); # 获取亏本交易拦截设置
    Route::put('configuration/upstream_intercept', 'admin/Configuration/upstreamInterceptUpdate'); # 保存亏本交易拦截设置
    Route::get('configuration/global_on_demand', 'admin/Configuration/globalOnDemand'); # 全局按需设置
    Route::put('configuration/global_on_demand', 'admin/Configuration/globalOnDemandUpdate'); # 保存全局按需设置

    # 主题配置管理
    Route::get('configuration/theme_config/:theme', 'admin/Configuration/themeConfigDetail'); # 获取指定主题配置
    Route::put('configuration/theme_config/:theme', 'admin/Configuration/themeConfigUpdate'); # 保存指定主题配置

    # 主题轮播图管理
    Route::get('configuration/theme_banner', 'admin/Configuration/themeBannerList'); # 获取主题轮播图列表
    Route::post('configuration/theme_banner', 'admin/Configuration/themeBannerCreate'); # 创建主题轮播图
    Route::put('configuration/theme_banner/:id', 'admin/Configuration/themeBannerUpdate'); # 更新主题轮播图
    Route::delete('configuration/theme_banner/:id', 'admin/Configuration/themeBannerDelete'); # 删除主题轮播图
    Route::put('configuration/theme_banner/:id/show', 'admin/Configuration/themeBannerShow'); # 切换主题轮播图显示状态
    Route::put('configuration/theme_banner/order', 'admin/Configuration/themeBannerOrder'); # 主题轮播图排序

	# 邮件模板管理
    Route::get('notice/email/template', 'admin/NoticeEmail/emailTemplateList'); # 获取邮件模板
	Route::get('notice/email/template/:id', 'admin/NoticeEmail/index'); # 获取单个邮件模板
    Route::post('notice/email/template', 'admin/NoticeEmail/create'); # 创建邮件模板
    Route::put('notice/email/template/:id', 'admin/NoticeEmail/update'); # 修改邮件模板
    Route::delete('notice/email/template/:id', 'admin/NoticeEmail/delete'); # 删除邮件模板
    Route::get('notice/email/:name/template/:id/test', 'admin/NoticeEmail/test'); # 测试邮件模板
	
	# 短信模板管理
	Route::get('notice/sms/:name/template', 'admin/NoticeSms/templateList'); # 获取短信模板
	Route::get('notice/sms/:name/template/:id', 'admin/NoticeSms/index'); # 获取单个短信模板
    Route::post('notice/sms/:name/template', 'admin/NoticeSms/create'); # 创建短信模板
    Route::put('notice/sms/:name/template/:id', 'admin/NoticeSms/update'); # 修改短信模板
    Route::delete('notice/sms/:name/template/:id', 'admin/NoticeSms/delete'); # 删除短信模板
    Route::get('notice/sms/:name/template/:id/test', 'admin/NoticeSms/test'); # 测试短信模板
    Route::get('notice/sms/:name/template/status', 'admin/NoticeSms/status'); # 更新模板审核状态
    Route::post('notice/sms/:name/template/audit', 'admin/NoticeSms/audit'); # 提交审核短信模板

	# 通知发送管理
    Route::get('notice/send', 'admin/NoticeSetting/settingList'); # 发送管理
    Route::put('notice/send', 'admin/NoticeSetting/update'); # 发送设置
    Route::post('notice/send/batch', 'admin/NoticeSetting/batchUpdate'); # 发送批量设置

    # 插件 module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表
    Route::get('plugin/hook', 'admin/plugin/pluginHookList'); # 带Hook插件列表
    Route::put('plugin/hook/order', 'admin/plugin/pluginHookOrder'); # 带Hook插件排序
    Route::get('plugin/sync', 'admin/plugin/sync'); # 插件同步
    Route::get('plugin/:id/download', 'admin/plugin/download'); # 插件下载
    # 实名认证接口
    Route::get('plugin/certification', 'admin/plugin/certificationPluginList'); # 实名认证接口列表
    Route::post('plugin/certification/:name', 'admin/plugin/certificationInstall'); # 实名认证接口安装
    Route::delete('plugin/certification/:name', 'admin/plugin/certificationUninstall'); # 实名认证接口卸载
    Route::put('plugin/certification/:name/:status', 'admin/plugin/certificationStatus'); # 禁用(启用)实名认证接口
    Route::get('plugin/certification/:name', 'admin/plugin/certificationSetting'); # 获取单个实名认证接口配置
    Route::put('plugin/certification/:name', 'admin/plugin/certificationSettingPost'); # 保存实名认证接口配置
    # 验证码接口
    Route::get('plugin/captcha', 'admin/plugin/captchaPluginList'); # 验证码接口列表
    Route::post('plugin/captcha/:name', 'admin/plugin/captchaInstall'); # 验证码接口安装
    Route::delete('plugin/captcha/:name', 'admin/plugin/captchaUninstall'); # 验证码接口卸载
    Route::put('plugin/captcha/:name/:status', 'admin/plugin/captchaStatus'); # 禁用(启用)验证码接口
    Route::get('plugin/captcha/:name', 'admin/plugin/captchaSetting'); # 获取单个验证码接口配置
    Route::put('plugin/captcha/:name', 'admin/plugin/captchaSettingPost'); # 保存验证码接口配置
    # 三方登录接口
    Route::get('plugin/oauth', 'admin/plugin/oauthPluginList'); # 三方登录接口列表
    Route::post('plugin/oauth/:name', 'admin/plugin/oauthInstall'); # 三方登录接口安装
    Route::delete('plugin/oauth/:name', 'admin/plugin/oauthUninstall'); # 三方登录接口卸载
    Route::put('plugin/oauth/:name/:status', 'admin/plugin/oauthStatus'); # 禁用(启用)三方登录接口
    Route::get('plugin/oauth/:name', 'admin/plugin/oauthSetting'); # 获取单个三方登录接口配置
    Route::put('plugin/oauth/:name', 'admin/plugin/oauthSettingPost'); # 保存三方登录接口配置
    # 短信接口
    Route::get('plugin/sms', 'admin/plugin/smsPluginList'); # 短信接口列表
    Route::post('plugin/sms/:name', 'admin/plugin/smsInstall'); # 短信接口安装
    Route::delete('plugin/sms/:name', 'admin/plugin/smsUninstall'); # 短信接口卸载
    Route::put('plugin/sms/:name/:status', 'admin/plugin/smsStatus'); # 禁用(启用)短信接口
    Route::get('plugin/sms/:name', 'admin/plugin/smsSetting'); # 获取单个短信接口配置
    Route::put('plugin/sms/:name', 'admin/plugin/smsSettingPost'); # 保存短信接口配置
    Route::post('plugin/sms/:name/sign', 'admin/plugin/smsPullSign'); # 短信接口安装
    # 邮件接口
    Route::get('plugin/mail', 'admin/plugin/mailPluginList'); # 邮件接口列表
    Route::post('plugin/mail/:name', 'admin/plugin/mailInstall'); # 邮件接口安装
    Route::delete('plugin/mail/:name', 'admin/plugin/mailUninstall'); # 邮件接口卸载
    Route::put('plugin/mail/:name/:status', 'admin/plugin/mailStatus'); # 禁用(启用)邮件接口
    Route::get('plugin/mail/:name', 'admin/plugin/mailSetting'); # 获取单个邮件接口配置
    Route::put('plugin/mail/:name', 'admin/plugin/mailSettingPost'); # 保存邮件接口配置
    # 支付接口
    Route::get('plugin/gateway', 'admin/plugin/gatewayPluginList'); # 支付接口列表
    Route::post('plugin/gateway/:name', 'admin/plugin/gatewayInstall'); # 支付接口安装
    Route::delete('plugin/gateway/:name', 'admin/plugin/gatewayUninstall'); # 支付接口卸载
    Route::put('plugin/gateway/:name/:status', 'admin/plugin/gatewayStatus'); # 禁用(启用)支付接口
    Route::get('plugin/gateway/:name', 'admin/plugin/gatewaySetting'); # 获取单个支付接口配置
    Route::put('plugin/gateway/:name', 'admin/plugin/gatewaySettingPost'); # 保存支付接口配置
    # 插件
    Route::get('plugin/addon', 'admin/plugin/addonPluginList'); # 插件列表
    Route::post('plugin/addon/:name', 'admin/plugin/addonInstall'); # 插件安装
    Route::delete('plugin/addon/:name', 'admin/plugin/addonUninstall')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'plugin_uninstall_disable'); # 插件卸载
    Route::put('plugin/addon/:name/:status', 'admin/plugin/addonStatus')->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'plugin_uninstall_disable'); # 禁用(启用)插件
    Route::post('plugin/:module/:name/upgrade', 'admin/plugin/upgrade'); # 插件升级
    # 对象存储
    Route::get('plugin/oss', 'admin/plugin/ossPluginList'); # 对象存储列表
    Route::post('plugin/oss/:name', 'admin/plugin/ossInstall'); # 对象存储安装
    Route::delete('plugin/oss/:name', 'admin/plugin/ossUninstall'); # 对象存储卸载
    Route::put('plugin/oss/:name/:status', 'admin/plugin/ossStatus'); # 禁用(启用)对象存储
    Route::get('plugin/oss/:name', 'admin/plugin/ossSetting'); # 获取单个对象存储配置
    Route::put('plugin/oss/:name', 'admin/plugin/ossSettingPost'); # 保存对象存储配置
    Route::get('plugin/oss/:name/data', 'admin/plugin/ossData'); # 对象存储是否存有数据判断接口
    Route::get('plugin/oss/:name/link', 'admin/plugin/ossLink'); # 检测对象存储是否联通

    Route::delete('plugin/template/:theme', 'admin/plugin/templateUninstall'); # 官网主题卸载

    # 排序
    Route::put('plugin/order/gateway', 'admin/plugin/gatewayPluginOrder'); # 支付接口排序

    # 发票
    Route::get('plugin/invoice', 'admin/plugin/invoicePluginList'); # 发票接口列表
    Route::post('plugin/invoice/:name', 'admin/plugin/invoiceInstall'); # 发票接口安装
    Route::delete('plugin/invoice/:name', 'admin/plugin/invoiceUninstall'); # 发票接口卸载
    Route::put('plugin/invoice/:name/:status', 'admin/plugin/invoiceStatus'); # 禁用(启用)发票接口
    Route::get('plugin/invoice/:name', 'admin/plugin/invoiceSetting'); # 获取单个发票接口配置
    Route::put('plugin/invoice/:name', 'admin/plugin/invoiceSettingPost'); # 保存发票接口配置


    # 商品与商品分组管理
    Route::get('product', 'admin/product/productList'); # 商品列表
    Route::get('product/:id', 'admin/product/index'); # 商品详情
    Route::post('product', 'admin/product/create'); # 新建商品
    Route::put('product/:id', 'admin/product/update'); # 编辑商品
    Route::put('product/:id/server', 'admin/product/updateServer'); # 编辑商品接口
    Route::delete('product/:id', 'admin/product/delete'); # 删除商品
    Route::delete('product', 'admin/product/batchDelete'); # 删除商品
    Route::put('product/:id/:hidden', 'admin/product/hidden'); # 隐藏/显示商品
    Route::put('product/order/:id', 'admin/product/order'); # 商品拖动排序
    Route::get('product/:id/upgrade', 'admin/product/upgrade'); # 获取商品关联的升降级商品
    Route::get('product/group/first', 'admin/productGroup/productGroupFirstList'); # 获取商品一级分组
    Route::get('product/group/second', 'admin/productGroup/productGroupSecondList'); # 获取商品二级分组
    Route::post('product/group', 'admin/productGroup/create'); # 新建商品分组
    Route::put('product/group/order/:id', 'admin/productGroup/order'); # 商品分组拖动排序
    Route::put('product/group/first/order/:id', 'admin/productGroup/orderFirst'); # 一级商品分组拖动排序
    Route::delete('product/group/:id', 'admin/productGroup/delete'); # 删除商品分组
    Route::put('product/group/:id', 'admin/productGroup/update'); # 编辑商品分组
    Route::put('product/group/:id/product', 'admin/productGroup/moveProduct'); # 移动商品至其他商品组
    Route::put('product/group/:id/:hidden', 'admin/productGroup/hidden'); # 隐藏/显示商品分组
    Route::get('product/:id/server/config_option', 'admin/product/moduleServerConfigOption'); # 选择接口获取配置
    Route::post('product/:id/config_option', 'admin/product/moduleCalculatePrice'); # 修改配置计算价格
    Route::get('module/:module/product', 'admin/product/moduleProductList'); # 根据模块获取商品
    Route::put('product/agentable', 'admin/product/saveAgentableProduct'); # 保存可代理商品
    Route::get('res_module/:module/product', 'admin/product/resModuleProductList'); # 根据上游模块获取商品
    Route::get('module/product', 'admin/product/modulesProductList'); # 根据模块获取商品
    Route::post('product/:id/copy', 'admin/product/copy'); # 复制商品
    Route::get('product/:id/custom_host_name', 'admin/product/getCustomHostName'); # 获取商品自定义标识配置
    Route::put('product/:id/custom_host_name', 'admin/product/saveCustomHostName'); # 保存商品自定义标识配置
    Route::put('product/:id/pay_ontrial', 'admin/product/payOntrial'); # 试用设置
    Route::put('product/:id/natural_month_prepaid', 'admin/product/naturalMonthPrepaid'); # 修改商品自然月预付费开关
    Route::get('product/notice_group', 'admin/product/productNoticeGroupList'); # 全局通知管理列表
    Route::post('product/notice_group', 'admin/product/productNoticeGroupCreate'); # 创建触发动作组
    Route::put('product/notice_group/:id', 'admin/product/productNoticeGroupUpdate'); # 修改触发动作组
    Route::delete('product/notice_group/:id', 'admin/product/productNoticeGroupDelete'); # 删除触发动作组
    Route::put('product/notice_group/:id/act/status', 'admin/product/productNoticeGroupUpdateActStatus'); # 修改触发动作组动作状态
    Route::get('product/:id/on_demand/billing_item', 'admin/product/productOnDemandbillingItem'); # 获取商品按需计费项目
    Route::get('product/:id/on_demand', 'admin/product/productOnDemandIndex'); # 商品按需计费配置详情
    Route::put('product/:id/on_demand', 'admin/product/productOnDemandUpdate'); # 修改商品按需计费配置
    Route::get('product/:id/mf_cloud_data_center', 'admin/MfCloudDataCenterMapGroup/getProductDataCenter'); # 根据商品ID获取数据中心

    # 公共接口
    Route::get('gateway', 'admin/common/gateway'); # 支付接口
    Route::get('sms', 'admin/common/sms'); # 短信接口
    Route::get('email', 'admin/common/email'); # 邮件接口
    Route::get('captcha_list', 'admin/common/captchaList'); # 验证码接口
    Route::get('common', 'admin/common/common'); # 公共配置
    Route::get('country', 'admin/common/countryList'); # 国家列表
    Route::get('auth', 'admin/common/authList'); # 权限列表 
    Route::get('admin/auth', 'admin/common/adminAuthList'); # 当前管理员权限列表 
    Route::post('upload', 'admin/common/upload'); # 上传文件
    Route::get('global_search', 'admin/common/globalSearch'); # 全局搜索
    Route::get('menu', 'admin/common/adminMenu'); # 获取导航
    Route::get('active_plugin', 'admin/common/activePluginList'); # 获取已激活插件
    Route::get('clientarea_auth', 'admin/common/clientareaAuthList'); # 权限列表 
    Route::get('common_search_table', 'admin/common/commonSearchTableList'); # 获取全局单表单字段搜索选项 
    Route::get('common_search', 'admin/common/commonSearch'); # 全局单表单字段搜索 
    Route::get('template_tab', 'admin/common/templateTabList'); # 模板控制器Tab 
    Route::get('send_param', 'admin/common/sendParam'); # 公共发送参数
    Route::get('upgrade_test', 'admin/common/upgradeTest'); # 升级更新测试后
//    Route::get('move_from', 'admin/common/moveFrom'); # 迁出
//    Route::get('move_to', 'admin/common/moveTo'); # 迁入
    # 接口管理
    Route::get('server/group', 'admin/serverGroup/serverGroupList'); # 接口分组列表
    Route::post('server/group', 'admin/serverGroup/create'); # 新建接口分组
    Route::put('server/group/:id', 'admin/serverGroup/update'); # 修改接口分组
    Route::delete('server/group/:id', 'admin/serverGroup/delete'); # 删除接口分组
    Route::get('server', 'admin/server/serverList'); # 接口列表
    Route::post('server', 'admin/server/create'); # 新建接口
    Route::put('server/:id', 'admin/server/update'); # 编辑接口
    Route::delete('server/:id', 'admin/server/delete'); # 删除接口
    Route::get('server/:id/status', 'admin/server/status'); # 获取接口连接状态
    Route::get('module', 'admin/module/moduleList'); # 模块列表

    #系统升级
    Route::get('system/version', 'admin/upgradeSystem/systemVersion'); # 获取系统版本
    Route::put('system/system_version_type', 'admin/upgradeSystem/updateSystemVersionType'); # 更改系统升级版本
    Route::get('system/upgrade_content', 'admin/upgradeSystem/upgradeContent'); # 获取更新内容
    Route::get('system/upgrade_download', 'admin/upgradeSystem/upgradeDownload'); # 更新下载
    Route::get('system/upgrade_download_progress', 'admin/upgradeSystem/upgradeDownloadProgress'); # 获取更新下载进度
    Route::get('system/auth', 'admin/upgradeSystem/getAuth'); # 获取授权信息
    Route::put('system/license', 'admin/upgradeSystem/updateLicense'); # 更换授权码

    # 导航管理
    Route::get('menu/admin', 'admin/menu/getAdminMenu'); # 接口分组列表
    Route::get('menu/home', 'admin/menu/getHomeMenu'); # 新建接口分组
    Route::put('menu/admin', 'admin/menu/saveAdminMenu'); # 修改接口分组
    Route::put('menu/home', 'admin/menu/saveHomeMenu'); # 删除接口分组
    Route::put('menu/home/icon', 'admin/menu/customHomeMenuIcon'); # 自定义前台导航图标

    # 应用商店
    Route::post('app_market/set_token', 'admin/appMarket/setToken'); # 设置token
    Route::get('app_market/app/version', 'admin/appMarket/getNewVersion'); # 获取应用版本
    Route::get('app_market/template/:theme/version', 'admin/appMarket/getTemplateVersion'); # 获取主题版本

    # 供应商
    Route::get('supplier', 'admin/supplier/list');
    Route::get('supplier/:id', 'admin/supplier/index');
    Route::post('supplier', 'admin/supplier/create');
    Route::put('supplier/:id', 'admin/supplier/update');
    Route::put('supplier/:id/rate', 'admin/supplier/updateSupplierRate');
    Route::delete('supplier/:id', 'admin/supplier/delete');
    Route::get('supplier/:id/status', 'admin/supplier/status');
    Route::get('supplier/:id/product', 'admin/supplier/product');
    Route::get('supplier/:id/credit', 'admin/supplier/supplierCredit');

    # 上游产品
    Route::get('upstream/host', 'admin/upstreamHost/list');
    Route::get('upstream/host/:id', 'admin/upstreamHost/index');

    # 上游订单
    Route::get('upstream/order', 'admin/upstreamOrder/list');
    Route::get('upstream/sell_info', 'admin/upstreamOrder/sellInfo');

    # 上游商品
    Route::get('upstream/product', 'admin/upstreamProduct/list');
    Route::get('upstream/product/:id', 'admin/upstreamProduct/index');
    Route::post('upstream/product', 'admin/upstreamProduct/create');
    Route::put('upstream/product/:id', 'admin/upstreamProduct/update');
    Route::get('upstream/recommend/product', 'admin/upstreamProduct/recommendProductList');
    Route::post('upstream/recommend/product', 'admin/upstreamProduct/agentRecommendProduct');
    Route::get('upstream/product/:id/sync', 'admin/upstreamProduct/manualSync');
    Route::get('upstream/product/:id/sync_resource', 'admin/upstreamProduct/manualSyncResource');

    # 意见反馈
    Route::get('feedback', 'admin/feedback/feedbackList');
    Route::get('feedback/type', 'admin/feedback/feedbackTypeList');
    Route::post('feedback/type', 'admin/feedback/createFeedbackType');
    Route::put('feedback/type/:id', 'admin/feedback/updateFeedbackType');
    Route::delete('feedback/type/:id', 'admin/feedback/deleteFeedbackType');

    # 方案咨询
    Route::get('consult', 'admin/consult/list');

    # 友情链接
    Route::get('friendly_link', 'admin/friendlyLink/list');
    Route::post('friendly_link', 'admin/friendlyLink/create');
    Route::put('friendly_link/:id', 'admin/friendlyLink/update');
    Route::delete('friendly_link/:id', 'admin/friendlyLink/delete');

    # 荣誉资质
    Route::get('honor', 'admin/honor/list');
    Route::post('honor', 'admin/honor/create');
    Route::put('honor/:id', 'admin/honor/update');
    Route::delete('honor/:id', 'admin/honor/delete');

    # 合作伙伴
    Route::get('partner', 'admin/partner/list');
    Route::post('partner', 'admin/partner/create');
    Route::put('partner/:id', 'admin/partner/update');
    Route::delete('partner/:id', 'admin/partner/delete');

    # 用户信息记录
    Route::get('client/:id/record', 'admin/clientRecord/list');
    Route::post('client/:id/record', 'admin/clientRecord/create');
    Route::put('client/record/:id', 'admin/clientRecord/update');
    Route::delete('client/record/:id', 'admin/clientRecord/delete');

    # 订单信息记录
    Route::get('order/:id/record', 'admin/orderRecord/list');
    Route::post('order/:id/record', 'admin/orderRecord/create');
    Route::put('order/record/:id', 'admin/orderRecord/update');
    Route::delete('order/record/:id', 'admin/orderRecord/delete');

    # 挂件
    Route::get('widget', 'admin/widget/index');
    Route::put('widget/order', 'admin/widget/widgetSaveOrder');
    Route::put('widget/status', 'admin/widget/toggleWidget');
    Route::get('widget/output', 'admin/widget/output');
    Route::get('widget/data', 'admin/widget/getData');

    # 自定义字段
    Route::get('self_defined_field', 'admin/selfDefinedField/selfDefinedFieldList');
    Route::post('self_defined_field', 'admin/selfDefinedField/create');
    Route::put('self_defined_field/:id', 'admin/selfDefinedField/update');
    Route::delete('self_defined_field/:id', 'admin/selfDefinedField/delete');
    Route::put('self_defined_field/:id/drag', 'admin/selfDefinedField/dragToSort');
    Route::put('self_defined_field/:id/related_product_group', 'admin/selfDefinedField/relatedProductGroup');
    Route::put('self_defined_field/:id/is_global', 'admin/selfDefinedField/isGlobalUpdate');

    # 自定义产品标识
    Route::get('custom_host_name', 'admin/customHostName/list');
    Route::post('custom_host_name', 'admin/customHostName/create');
    Route::put('custom_host_name/:id', 'admin/customHostName/update');
    Route::delete('custom_host_name/:id', 'admin/customHostName/delete');
    Route::put('custom_host_name/:id/related_product_group', 'admin/customHostName/relatedProductGroup');

    # 本地镜像
    Route::get('local_image_group', "admin/localImageGroup/list");
    Route::post('local_image_group', "admin/localImageGroup/create");
    Route::put('local_image_group/:id', "admin/localImageGroup/update");
    Route::delete('local_image_group/:id', "admin/localImageGroup/delete");
    Route::put('local_image_group/order', "admin/localImageGroup/order");

    Route::get('local_image', "admin/localImage/list");
    Route::post('local_image', "admin/localImage/create");
    Route::put('local_image/:id', "admin/localImage/update");
    Route::delete('local_image/:id', "admin/localImage/delete");
    Route::put('local_image/order', "admin/localImage/order");

    # 镜像同步
    Route::get('product/sync_image_log', "admin/product/syncImageLogList");
    Route::post('product/sync_image', "admin/product/syncImage");

    # API
    Route::get('api/config', 'admin/api/getConfig');
    Route::put('api/config', 'admin/api/updateConfig');
    Route::get('api/client', 'admin/api/clientList');
    Route::post('api/client/:id', 'admin/api/addClient');
    Route::delete('api/client/:id', 'admin/api/removeClient');

    # 视图设置
    Route::get('view', 'admin/common/adminViewIndex');
    Route::get('view/data_range', 'admin/common/adminViewDataRange');
    Route::get('view/list', 'admin/common/adminViewList');
    Route::post('view', 'admin/common/createAdminView');
    Route::put('view/:id', 'admin/common/updateAdminView');
    Route::put('view/:id/field', 'admin/common/updateAdminViewField');
    Route::put('view/:id/data_range', 'admin/common/updateAdminViewDataRange');
    Route::delete('view/:id', 'admin/common/deleteAdminView');
    Route::post('view/:id/copy', 'admin/common/copyAdminView');
    Route::put('view/:id/status', 'admin/common/statusAdminView');
    Route::put('view/order', 'admin/common/orderAdminView');
    Route::put('view/choose', 'admin/common/chooseAdminView');

    # 模板控制器
    Route::get('web_nav', "admin/webNav/list");
    Route::post('web_nav', "admin/webNav/create");
    Route::put('web_nav/:id', "admin/webNav/update");
    Route::delete('web_nav/:id', "admin/webNav/delete");
    Route::put('web_nav/:id/show', "admin/webNav/show");
    Route::put('first_web_nav/order', "admin/webNav/firstNavOrder");
    Route::put('second_web_nav/order', "admin/webNav/secondNavOrder");
    Route::put('web_nav/:id/blank', "admin/webNav/blank");

    Route::get('cloud_server_banner', "admin/cloudServerBanner/list");
    Route::post('cloud_server_banner', "admin/cloudServerBanner/create");
    Route::put('cloud_server_banner/:id', "admin/cloudServerBanner/update");
    Route::delete('cloud_server_banner/:id', "admin/cloudServerBanner/delete");
    Route::put('cloud_server_banner/:id/show', "admin/cloudServerBanner/show");
    Route::put('cloud_server_banner/order', "admin/cloudServerBanner/order");

    Route::get('cloud_server_area', "admin/cloudServerArea/list");
    Route::post('cloud_server_area', "admin/cloudServerArea/create");
    Route::put('cloud_server_area/:id', "admin/cloudServerArea/update");
    Route::delete('cloud_server_area/:id', "admin/cloudServerArea/delete");
    Route::put('cloud_server_area/order', "admin/cloudServerArea/order");

    Route::get('cloud_server_product', "admin/cloudServerProduct/list");
    Route::post('cloud_server_product', "admin/cloudServerProduct/create");
    Route::put('cloud_server_product/:id', "admin/cloudServerProduct/update");
    Route::delete('cloud_server_product/:id', "admin/cloudServerProduct/delete");
    Route::put('cloud_server_product/order', "admin/cloudServerProduct/order");

    Route::get('cloud_server_discount', "admin/cloudServerDiscount/list");
    Route::post('cloud_server_discount', "admin/cloudServerDiscount/create");
    Route::put('cloud_server_discount/:id', "admin/cloudServerDiscount/update");
    Route::delete('cloud_server_discount/:id', "admin/cloudServerDiscount/delete");

    Route::get('physical_server_banner', "admin/physicalServerBanner/list");
    Route::post('physical_server_banner', "admin/physicalServerBanner/create");
    Route::put('physical_server_banner/:id', "admin/physicalServerBanner/update");
    Route::delete('physical_server_banner/:id', "admin/physicalServerBanner/delete");
    Route::put('physical_server_banner/:id/show', "admin/physicalServerBanner/show");
    Route::put('physical_server_banner/order', "admin/physicalServerBanner/order");

    Route::get('physical_server_area', "admin/physicalServerArea/list");
    Route::post('physical_server_area', "admin/physicalServerArea/create");
    Route::put('physical_server_area/:id', "admin/physicalServerArea/update");
    Route::delete('physical_server_area/:id', "admin/physicalServerArea/delete");
    Route::put('physical_server_area/order', "admin/physicalServerArea/order");

    Route::get('physical_server_product', "admin/physicalServerProduct/list");
    Route::post('physical_server_product', "admin/physicalServerProduct/create");
    Route::put('physical_server_product/:id', "admin/physicalServerProduct/update");
    Route::delete('physical_server_product/:id', "admin/physicalServerProduct/delete");
    Route::put('physical_server_product/order', "admin/physicalServerProduct/order");

    Route::get('physical_server_discount', "admin/physicalServerDiscount/list");
    Route::post('physical_server_discount', "admin/physicalServerDiscount/create");
    Route::put('physical_server_discount/:id', "admin/physicalServerDiscount/update");
    Route::delete('physical_server_discount/:id', "admin/physicalServerDiscount/delete");

    Route::get('ssl_certificate_product', "admin/sslCertificateProduct/list");
    Route::post('ssl_certificate_product', "admin/sslCertificateProduct/create");
    Route::put('ssl_certificate_product/:id', "admin/sslCertificateProduct/update");
    Route::delete('ssl_certificate_product/:id', "admin/sslCertificateProduct/delete");
    Route::put('ssl_certificate_product/order', "admin/sslCertificateProduct/order");

    Route::get('sms_service_product', "admin/smsServiceProduct/list");
    Route::post('sms_service_product', "admin/smsServiceProduct/create");
    Route::put('sms_service_product/:id', "admin/smsServiceProduct/update");
    Route::delete('sms_service_product/:id', "admin/smsServiceProduct/delete");
    Route::put('sms_service_product/order', "admin/smsServiceProduct/order");

    Route::get('trademark_register_product', "admin/trademarkRegisterProduct/list");
    Route::post('trademark_register_product', "admin/trademarkRegisterProduct/create");
    Route::put('trademark_register_product/:id', "admin/trademarkRegisterProduct/update");
    Route::delete('trademark_register_product/:id', "admin/trademarkRegisterProduct/delete");
    Route::put('trademark_register_product/order', "admin/trademarkRegisterProduct/order");

    Route::get('trademark_service_product', "admin/trademarkServiceProduct/list");
    Route::post('trademark_service_product', "admin/trademarkServiceProduct/create");
    Route::put('trademark_service_product/:id', "admin/trademarkServiceProduct/update");
    Route::delete('trademark_service_product/:id', "admin/trademarkServiceProduct/delete");
    Route::put('trademark_service_product/order', "admin/trademarkServiceProduct/order");

    Route::get('server_hosting_area', "admin/serverHostingArea/list");
    Route::post('server_hosting_area', "admin/serverHostingArea/create");
    Route::put('server_hosting_area/:id', "admin/serverHostingArea/update");
    Route::delete('server_hosting_area/:id', "admin/serverHostingArea/delete");
    Route::put('server_hosting_area/order', "admin/serverHostingArea/order");

    Route::get('server_hosting_product', "admin/serverHostingProduct/list");
    Route::post('server_hosting_product', "admin/serverHostingProduct/create");
    Route::put('server_hosting_product/:id', "admin/serverHostingProduct/update");
    Route::delete('server_hosting_product/:id', "admin/serverHostingProduct/delete");
    Route::put('server_hosting_product/order', "admin/serverHostingProduct/order");

    Route::get('cabinet_rental_product', "admin/cabinetRentalProduct/list");
    Route::post('cabinet_rental_product', "admin/cabinetRentalProduct/create");
    Route::put('cabinet_rental_product/:id', "admin/cabinetRentalProduct/update");
    Route::delete('cabinet_rental_product/:id', "admin/cabinetRentalProduct/delete");
    Route::put('cabinet_rental_product/order', "admin/cabinetRentalProduct/order");

    Route::get('icp_service_product', "admin/icpServiceProduct/list");
    Route::post('icp_service_product', "admin/icpServiceProduct/create");
    Route::put('icp_service_product/:id', "admin/icpServiceProduct/update");
    Route::delete('icp_service_product/:id', "admin/icpServiceProduct/delete");
    Route::put('icp_service_product/order', "admin/icpServiceProduct/order");

    Route::get('bottom_bar_group', "admin/bottomBarGroup/list");
    Route::post('bottom_bar_group', "admin/bottomBarGroup/create");
    Route::put('bottom_bar_group/:id', "admin/bottomBarGroup/update");
    Route::delete('bottom_bar_group/:id', "admin/bottomBarGroup/delete");
    Route::put('bottom_bar_group/order', "admin/bottomBarGroup/order");

    Route::get('bottom_bar_nav', "admin/bottomBarNav/list");
    Route::post('bottom_bar_nav', "admin/bottomBarNav/create");
    Route::put('bottom_bar_nav/:id', "admin/bottomBarNav/update");
    Route::delete('bottom_bar_nav/:id', "admin/bottomBarNav/delete");
    Route::put('bottom_bar_nav/:id/show', "admin/bottomBarNav/show");
    Route::put('bottom_bar_nav/order', "admin/bottomBarNav/order");
    Route::put('bottom_bar_nav/:id/blank', "admin/bottomBarNav/blank");

    Route::get('configuration/web', "admin/configuration/webList");
    Route::put('configuration/web', "admin/configuration/webUpdate");
    Route::get('configuration/cloud_server', "admin/configuration/cloudServerList");
    Route::put('configuration/cloud_server', "admin/configuration/cloudServerUpdate");
    Route::get('configuration/physical_server', "admin/configuration/physicalServerList");
    Route::put('configuration/physical_server', "admin/configuration/physicalServerUpdate");
    Route::get('configuration/icp', "admin/configuration/icpList");
    Route::put('configuration/icp', "admin/configuration/icpUpdate");

    Route::get('seo', "admin/seo/list");
    Route::post('seo', "admin/seo/create");
    Route::put('seo/:id', "admin/seo/update");
    Route::delete('seo/:id', "admin/seo/delete"); 

    Route::get('side_floating_window', "admin/sideFloatingWindow/list");
    Route::post('side_floating_window', "admin/sideFloatingWindow/create");
    Route::put('side_floating_window/:id', "admin/sideFloatingWindow/update");
    Route::delete('side_floating_window/:id', "admin/sideFloatingWindow/delete");
    Route::put('side_floating_window/order', "admin/sideFloatingWindow/order"); 

    Route::get('index_banner', "admin/indexBanner/list");
    Route::post('index_banner', "admin/indexBanner/create");
    Route::put('index_banner/:id', "admin/indexBanner/update");
    Route::delete('index_banner/:id', "admin/indexBanner/delete");
    Route::put('index_banner/:id/show', "admin/indexBanner/show");
    Route::put('index_banner/order', "admin/indexBanner/order");

    // 需要验证操作密码的方法
    Route::group('',function (){

        Route::delete('host/:id', 'admin/host/delete'); # 删除产品
        Route::delete('host', 'admin/host/batchDelete'); # 批量删除产品
        Route::post('host/:id/module/create', 'admin/host/createAccount'); # 模块开通
        Route::post('host/:id/module/suspend', 'admin/host/suspendAccount'); # 模块暂停
        Route::post('host/:id/module/unsuspend', 'admin/host/unsuspendAccount'); # 模块解除暂停
        Route::post('host/:id/module/terminate', 'admin/host/terminateAccount'); # 模块删除
        
    })->middleware(\app\http\middleware\CheckAdminOperatePassword::class, 'host_operate');  // 需要验证产品操作操作密码

    Route::get('host/:id/module/sync', 'admin/host/syncAccount'); # 拉取上游信息

    Route::get('product_duration_group_presets', 'admin/ProductDurationGroupPresets/presetsList');
    Route::get('product_duration_group_presets/:id', 'admin/ProductDurationGroupPresets/index');
    Route::post('product_duration_group_presets', 'admin/ProductDurationGroupPresets/create');
    Route::put('product_duration_group_presets/:id', 'admin/ProductDurationGroupPresets/update');
    Route::delete('product_duration_group_presets/:id', 'admin/ProductDurationGroupPresets/delete');
    Route::post('product_duration_group_presets/:id/copy', 'admin/ProductDurationGroupPresets/copy');
    Route::get('product_duration_group_presets_link', 'admin/ProductDurationGroupPresetsLink/linkList');
    Route::post('product_duration_group_presets_link', 'admin/ProductDurationGroupPresetsLink/create');
    Route::put('product_duration_group_presets_link/:gid', 'admin/ProductDurationGroupPresetsLink/update');
    Route::delete('product_duration_group_presets_link/:gid', 'admin/ProductDurationGroupPresetsLink/delete');

    // 消息通知
    Route::get('notice/sync', 'admin/Notice/sync');
    Route::get('notice', 'admin/Notice/list');
    Route::get('notice/:id', 'admin/Notice/detail');
    Route::post('notice/mark_read', 'admin/Notice/markRead');
    Route::delete('notice', 'admin/Notice/delete');

    // 魔方云区域组
    Route::get('mf_cloud_data_center_map_group', 'admin/MfCloudDataCenterMapGroup/groupList');
    Route::post('mf_cloud_data_center_map_group', 'admin/MfCloudDataCenterMapGroup/groupCreate');
    Route::put('mf_cloud_data_center_map_group/:id', 'admin/MfCloudDataCenterMapGroup/groupUpdate');
    Route::delete('mf_cloud_data_center_map_group/:id', 'admin/MfCloudDataCenterMapGroup/groupDelete');


})  
    ->allowCrossDomain([
            'Access-Control-Allow-Origin'        => $origin,
            'Access-Control-Allow-Credentials'   => 'true',
            'Access-Control-Max-Age'             => 600,
        ]
    )
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);

Route::get(DIR_ADMIN,'admin/view/index')->middleware(app\http\middleware\CheckAdminCookieJwt::class); //模板首页
Route::get(DIR_ADMIN.'/[:view_html]','admin/view/index')->ext('htm')->middleware(app\http\middleware\CheckAdminCookieJwt::class); //后台模板
Route::get(DIR_ADMIN.'/plugin/[:name]/[:view_html]','admin/view/plugin')->ext('htm')->middleware(app\http\middleware\CheckAdminCookieJwt::class); //后台插件模板
Route::get(DIR_ADMIN.'/template/[:name]/[:view_html]','admin/view/template')->ext('htm'); //后台模板控制器模板
/* 通用接口API */

// 获取国家列表
function getCountry(params) {
  return Axios.get(`/country`, params);
}
// 获取支付接口
function getPayList() {
  return Axios.get("/gateway");
}
// 获取公共配置
function getCommon() {
  return Axios.get("/common");
}
// 获取系统配置
function getSystem() {
  return Axios.get("/configuration/system");
}
// 获取登录信息
function getLoginInfo(params) {
  return Axios.get("/login", { params });
}
// 获取验证码
function getCaptcha() {
  return Axios.get("/captcha");
}
// 登录
function logIn(params) {
  return Axios.post("/login", params);
}
// 退出登录
function logout() {
  return Axios.post("/logout");
}
// 获取权限
function getAuthRole() {
  return Axios.get("/admin/auth");
}
// 全局搜索
function globalSearch(keywords) {
  return Axios.get(`/global_search?keywords=${keywords}`);
}
// 获取导航
function getMenus() {
  return Axios.get("/menu");
}

// 修改管理员密码
function editPass(params) {
  return Axios.put(`admin/password/update`, params);
}

// 生成token
function setToken() {
  return Axios.post(`/app_market/set_token`);
}

// 取消百度订单
function cancelOrder(params) {
  return Axios.post(`/baidu_cloud/order/cancel`, params);
}

// 获取已购买应用最新版本
function getActiveVersion() {
  return Axios.get("/app_market/app/version");
}

/* 获取系统版本 */
function version() {
  return Axios.get(`/system/version`);
}
// 系统设置
function getSystemOpt() {
  return Axios.get("/configuration/system");
}
function updateSystemOpt(params) {
  return Axios.put("/configuration/system", params);
}

/* 商品 */
// 获取商品列表
function getComProduct(params) {
  return Axios.get(`/product`, { params });
}
// 获取商品一级分组
function getFirstGroup() {
  return Axios.get(`/product/group/first`);
}
// 获取商品二级分组
function getSecondGroup() {
  return Axios.get(`/product/group/second`);
}
// 用户管理-用户列表
function getComClientList(params) {
  return Axios.get(`/client`, { params });
}
// 销售绑定用户列表
function saleClientList(params) {
  return Axios.get("/sale/client", { params });
}

function getUserInfo(params) {
  return Axios.get(`/client/${params.id}`);
}

function getAddon(params) {
  return Axios.get(`/active_plugin`, { params });
}

function getCommonActivePlugin(params) {
  return Axios.get(`/active_plugin`, { params });
}

/* 全局搜索 */
function getGlobalSearch() {
  return Axios.get("common_search_table");
}
function sendGlobalSearch(params) {
  return Axios.get("/common_search", { params });
}
//  获取当前管理员信息，用来获取是否设置了操作密码
function getAdminInfo() {
  return Axios.get(`/login_info`);
}

// 修改管理员操作密码
function changePassword(params) {
  return Axios.put(`/admin/operate_password`, params);
}
// 公共发送参数
function getCommonParams() {
  return Axios.get(`/send_param`);
}
// 获取已激活插件
function getActivePlugin(params) {
  return Axios.get(`/active_plugin`, { params });
}

// 用户列表导出EXCEL
function apiExportClient(params) {
  return Axios.get(`/export_excel/client`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 产品列表导出EXCEL
function apiExportHost(params) {
  return Axios.get(`/export_excel/host`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 订单列表导出EXCEL
function apiExportOrder(params) {
  return Axios.get(`/export_excel/order`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 交易流水列表导出EXCEL
function apiExportTransaction(params) {
  return Axios.get(`/export_excel/transaction`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 系统日志列表导出EXCEL
function apiExportSystemlog(params) {
  return Axios.get(`/export_excel/system_log`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 短信日志列表导出EXCEL
function apiExportSmslog(params) {
  return Axios.get(`/export_excel/sms_log`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 邮件日志列表导出EXCEL
function apiExportEmaillog(params) {
  return Axios.get(`/export_excel/email_log`, {
    params,
    responseType: "blob",
    timeout: 0,
  });
}

// 获取所有销售
function getAllSales() {
  return Axios.get(`/sale/all`);
}

// 以用户登录
function loginByUserId(id) {
  return Axios.post(`/client/${id}/login`);
}

// 通知列表
function apiNoticeList(params) {
  return Axios.get(`/notice`, { params });
}

// 异步请求，获取官方通知，更新本地通知信息
function apiNoticeSync(params) {
  return Axios.get(`/notice/sync`, { params });
}

// 通知详情
function apiNoticeDetail(params) {
  return Axios.get(`/notice/${params.id}`, { params });
}

// 标记已读
function apiNoticeRead(params) {
  return Axios.post(`/notice/mark_read`, params);
}

// 删除通知
function apiNoticeDel(params) {
  return Axios.delete(`/notice`, { params });
}

// 获取二次验证方式
function apiVerifyMethod(params) {
  return Axios.post(`/second/verify/method`, params);
}

// 发送手机验证码
function apiSendPhoneCode(params) {
  return Axios.post(`/phone/code`, params);
}

// 发送邮件验证码
function apiSendEmailCode(params) {
  return Axios.post(`/email/code`, params);
}

// 用户管理-用户详情
function getClientDetail(id) {
  return Axios.get(`/client/${id}`);
}

// 用户管理-修改资料
function updateClient(id, params) {
  return Axios.put(`/client/${id}`, params);
}

// 更改主题
function updateClientTheme(params) {
  return Axios.post(`/clientarea_theme_color`, params);
}

// 后台重置授权信息
function getAuthResetInfo(params) {
  return Axios.get(`/authreset/applications/host/${params.host_id}`, { params });
}
// 授权重置
function authReset(params) {
  return Axios.post(
    `/authreset/applications/host/${params.host_id}/reset`,
    params
  );
}
/* 快捷回复 */
// 获取快捷回复列表
function getQuickReplies(params) {
  return Axios.get(`/authreset/quick_replies`, { params });
}
// 创建快捷回复
function createQuickReply(params) {
  return Axios.post(`/authreset/quick_replies`, params);
}
// 更新快捷回复
function updateQuickReply(params) {
  return Axios.put(`/authreset/quick_replies/${params.id}`, params);
}
// 删除快捷回复
function deleteQuickReply(params) {
  return Axios.delete(`/authreset/quick_replies/${params.id}`, { params });
}
// 自定义字段列表
function getClientCustomField(params) {
  return Axios.get(`/client_custom_field`, { params });
}

// 查看所有IP
function getAllIp(params) {
  return Axios.get(`/host/${params.id}/ip`);
}

// 动作列表
function getNoticeAction() {
  return Axios.get("/notice/send");
}

// 获取当天日程
function getTodaySchedule(params) {
  return Axios.get(`/schedule/today`, { params });
}

// 修改日程
function updateSchedule(params) {
  return Axios.put(`/schedule/${params.id}`, params);
}


// 今日忽略日程
function ignoreSchedule(params) {
  return Axios.put(`/schedule/${params.id}/ignore`, params);
}

// 接收日程
function acceptSchedule(params) {
  return Axios.put(`/schedule/${params.id}/accept`, params);
}

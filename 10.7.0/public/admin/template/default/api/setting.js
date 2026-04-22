// 获取公共配置
function getCommon () {
  return Axios.get("/common");
}
/* 系统设置API */

// 系统设置
function getSystemOpt () {
  return Axios.get("/configuration/system");
}
function updateSystemOpt (params) {
  return Axios.put("/configuration/system", params);
}
// 登录设置
function getLoginOpt () {
  return Axios.get("/configuration/login");
}
function updateLoginOpt (params) {
  return Axios.put("/configuration/login", params);
}
// 安全设置
function getSafeOpt () {
  return Axios.get("/configuration/security");
}
function updateSafeOpt (params) {
  return Axios.put("/configuration/security", params);
}
// 获取验证码列表
function getCaptchaList () {
  return Axios.get("/captcha_list");
}
// 图形验证码预览
function previewCode (params) {
  return Axios.get("/configuration/security/captcha", { params });
}
// 货币设置
function getCurrencyOpt () {
  return Axios.get("/configuration/currency");
}
function updateCurrencyOpt (params) {
  return Axios.put("/configuration/currency", params);
}

// 获取插件接口列表（ module: gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表）
function getMoudle (params) {
  return Axios.get(`/plugin/${params.module}`, { params });
}
// 获取配置
function getMoudleConfig (params) {
  return Axios.get(`/plugin/${params.module}/${params.name}`, params);
}
// 保存配置
function saveMoudleConfig (params) {
  return Axios.put(`/plugin/${params.module}/${params.name}`, params);
}
// 禁用/启用支付接口
function changeMoudle (params) {
  return Axios.put(`/plugin/${params.module}/${params.name}/${params.status}`);
}
// 卸载支付接口
function deleteMoudle (type, params) {
  if (type === "install") {
    return Axios.post(`/plugin/${params.module}/${params.name}`);
  } else {
    return Axios.delete(`/plugin/${params.module}/${params.name}`);
  }
}

// 管理员
function getAdminList (params) {
  return Axios.get("/admin", { params });
}
function getAdminDetail (id) {
  return Axios.get(`/admin/${id}`);
}

function createAdmin (type, params) {
  if (type === "create") {
    return Axios.post(`/admin`, params);
  } else {
    return Axios.put(`/admin/${params.id}`, params);
  }
}

function deleteAdmin (id) {
  return Axios.delete(`/admin/${id}`);
}
function changeAdminStatus (params) {
  return Axios.put(`/admin/${params.id}/status`, params);
}

// 管理员分组
function getAdminRole (params) {
  return Axios.get("/admin/role", { params });
}
function getAdminRoleDetail (id) {
  return Axios.get(`/admin/role/${id}`);
}
function createAdminRole (type, params) {
  if (type === "create") {
    return Axios.post(`/admin/role`, params);
  } else {
    return Axios.put(`/admin/role/${params.id}`, params);
  }
}
function deleteAdminRole (id) {
  return Axios.delete(`/admin/role/${id}`);
}
// 获取权限
function getAllAuthRole () {
  return Axios.get("/auth");
}
// 短信接口
function getSmsInterface () {
  return Axios.get("/sms");
}
// 邮件接口
function getEmailInterface () {
  return Axios.get("/email");
}

/* 短信模板 */
function getSmsTemplate (name) {
  return Axios.get(`/notice/sms/${name}/template`);
}
function getSmsTemplateStatus (name) {
  return Axios.get(`/notice/sms/${name}/template/status`);
}
function getSmsTemplateDetail (params) {
  return Axios.get(`/notice/sms/${params.name}/template/${params.id}`);
}
function createTemplate (type, params) {
  if (type === "create") {
    return Axios.post(`/notice/sms/${params.name}/template`, params);
  } else {
    return Axios.put(
      `/notice/sms/${params.name}/template/${params.id}`,
      params
    );
  }
}

function deleteSmsTemplate (params) {
  return Axios.delete(`/notice/sms/${params.name}/template/${params.id}`);
}
function testSmsTemplate (params) {
  return Axios.get(`/notice/sms/${params.name}/template/${params.id}/test`, {
    params,
  });
}
// 批量提交短信模板审核
function batchSubmitById (params) {
  return Axios.post(`/notice/sms/${params.name}/template/audit`, params);
}
// 更新状态
function updateTemplateStatus (name) {
  return Axios.get(`/notice/sms/${name}/template/status`);
}

// 邮件模板
function getEmailTemplate () {
  return Axios.get(`/notice/email/template`);
}
function getEmailTemplateDetail (id) {
  return Axios.get(`/notice/email/template/${id}`);
}
function createEmailTemplate (type, params) {
  if (type === "create") {
    return Axios.post(`/notice/email/template`, params);
  } else {
    return Axios.put(`/notice/email/template/${params.id}`, params);
  }
}
function deleteEmailTemplate (id) {
  return Axios.delete(`/notice/email/template/${id}`);
}
function testEmailTemplate (params) {
  return Axios.get(`/notice/email/${params.name}/template/${params.id}/test`, {
    params,
  });
}

// 发送管理
function getSendList () {
  return Axios.get(`/notice/send`);
}
function updateSend (params) {
  return Axios.put(`/notice/send`, params);
}

/* 获取主题设置 */
function getThemeConfig () {
  return Axios.get(`/configuration/theme`);
}
function updateThemeConfig (params) {
  return Axios.put(`configuration/theme`, params);
}

/* 获取系统版本 */
function version () {
  return Axios.get(`/system/version`);
}

// 获取更新内容
function upContent () {
  return Axios.get(`/system/upgrade_content`);
}

// 更新下载
function upDown () {
  return Axios.get(`/system/upgrade_download`);
}

// 获取更新下载进度
function upProgress () {
  return Axios.get(`/system/upgrade_download_progress`);
}
// 获取公告
function newsList (params) {
  return Axios.get(
    `https://my.idcsmart.com/console/v1/news?addon_idcsmart_news_type_id=11`,
    { params }
  );
}

// 获取已购买应用最新版本
function getActiveVersion () {
  return Axios.get("/app_market/app/version");
}
// 插件升级
function upgradePlugin (params) {
  return Axios.post(`/plugin/${params.module}/${params.name}/upgrade`);
}

// debug调试
function getDebugConfig () {
  return Axios.get(`/configuration/debug`);
}
function updateDebugConfig (params) {
  return Axios.put(`/configuration/debug`, params);
}

// 更新识别码
function updateLicense () {
  return Axios.get("/system/auth");
}

// 更换识别码
function changeLicense (params) {
  return Axios.put(`/system/license`, params);
}

// 已激活的插件
function getActiveAddon () {
  return Axios.get(`/active_plugin`);
}

// 获取对象存储接口列表
function apiOssList () {
  return Axios.get(`/plugin/oss`);
}

// 对象存储接口安装 / 卸载
function deleteOss (type, params) {
  if (type === "install") {
    return Axios.post(`/plugin/oss/${params.name}`);
  } else {
    return Axios.delete(`/plugin/oss/${params.name}`);
  }
}

// 禁用(启用)对象存储接口
function changeStatus (params) {
  return Axios.put(`/plugin/oss/${params.name}/${params.status}`, params);
}

// 获取单个对象存储接口配置
function apiOssDetail (params) {
  return Axios.get(`/plugin/oss/${params.name}`);
}

// 保存对象存储接口配置
function saveOssConfig (params) {
  return Axios.put(`/plugin/oss/${params.name}`, params);
}

// 对象存储页面
function apiOssPage () {
  return Axios.get(`/configuration/oss`);
}

// 保存对象存储页面
function apiOssConfigPut (params) {
  return Axios.put(`/configuration/oss`, params);
}

// 获取是否有数据
function apiOssData (name) {
  return Axios.get(`/plugin/oss/${name}/data`);
}

function apiOssLink (name) {
  return Axios.get(`/plugin/oss/${name}/link`);
}

// API指定用户列表
function apiUserList (params) {
  return Axios.get(`/api/client`, { params });
}

// 获取API设置
function apiConfig () {
  return Axios.get(`/api/config`);
}

// 保存API设置
function apiConfigPut (params) {
  return Axios.put(`/api/config`, params);
}

// 添加API指定用户
function apiUserAdd (id) {
  return Axios.post(`/api/client/${id}`);
}

// 移除API指定用户
function apiUserDelete (id) {
  return Axios.delete(`/api/client/${id}`);
}

// 更改系统升级版本
function changeVersionType (params) {
  return Axios.put(`/system/system_version_type`, params);
}

// 支付插件排序
function apiGatewaySort (params) {
  return Axios.put(`/plugin/order/gateway`, params);
}

// 拉取签名信息
function pullSmsSign (params) {
  return Axios.post(`/plugin/sms/${params.name}/sign`, params);
}

// 修改管理员姓名
function apiEditNikeName (params) {
  return Axios.put(`/admin/nickname`, params);
}

// 验证原手机
function apiVerifyOldPhone (params) {
  return Axios.post(`/admin/verify_old_phone`, params);
}

// 验证原邮箱
function apiVerifyOldEmail (params) {
  return Axios.post(`/admin/verify_old_email`, params);
}

// 修改手机
function apiChangePhone (params) {
  return Axios.put(`/admin/phone`, params);
}

// 修改邮箱
function apiChangeEmail (params) {
  return Axios.put(`/admin/email`, params);
}

// 发送手机验证码
function apiSendPhoneCode (params) {
  return Axios.post(`/phone/code`, params);
}

// 发送邮件验证码
function apiSendEmailCode (params) {
  return Axios.post(`/email/code`, params);
}

// 获取TOTP密钥
function apiTotpInfo (params) {
  return Axios.get(`/admin/totp`, { params });
}

// 绑定TOTP
function apiBindTotp (params) {
  return Axios.put(`/admin/totp`, params);
}

// 解绑TOTP
function apiUnbindTotp (params) {
  return Axios.delete(`/admin/totp`, { params });
}

// 管理员解绑其他管理员TOTP
function apiUnbindOtherTotp (params) {
  return Axios.delete(`/admin/${params.id}/totp`, { params });
}

// 管理员解锁其他管理员
function apiUnlockOtherAdmin (params) {
  return Axios.delete(`/admin/${params.id}/lock`, { params });
}



/* 会员中心主题 */
// 获取指定主题配置
function getSpecifyThemeConfig (params) {
  return Axios.get(`/configuration/theme_config/${params.theme}`, { params });
}
// 保存指定主题配置
function saveSpecifyThemeConfig (params) {
  return Axios.put(`/configuration/theme_config/${params.theme}`, params);
}
function getBanner (params) {
  return Axios.get(`/configuration/theme_banner`, { params });
}
function addAndUpdateBanner (type, params) {
  if (type === "add") {
    return Axios.post(`/configuration/theme_banner`, params);
  } else if (type === "update") {
    return Axios.put(`/configuration/theme_banner/${params.id}`, params);
  }
}
function deleteBanner (params) {
  return Axios.delete(`/configuration/theme_banner/${params.id}`);
}
function showBanner (params) {
  return Axios.put(`/configuration/theme_banner/${params.id}/show`, params);
}
function sortBanner (params) {
  return Axios.put(`/configuration/theme_banner/order`, params);
}


/* 系统缓存 */
// 获取缓存统计
function getCacheStatistics (params) {
  return Axios.get(`/cache/stats`, { params });
}
/* 
  type: all-所有缓存，plugin-插件缓存，config-配置缓存，lang -语言缓存
*/
function clearCache (type) {
  return Axios.post(`/cache/clear_${type}`);
}

// 发送批量设置
function batchSendSetting (params) {
  return Axios.post(`/notice/send/batch`, params);
}

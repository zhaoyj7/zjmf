/* 商品管理API */

// 获取商品列表
function getProduct (params) {
  return Axios.get(`/product`, { params });
}
// 获取商品详情
function getProductDetail (id) {
  return Axios.get(`/product/${id}`);
}
// 新增商品
function addProduct (params) {
  return Axios.post(`/product`, params);
}
// 编辑商品
function editProduct (params) {
  return Axios.put(`/product/${params.id}`, params);
}
// 编辑商品接口
function editProductServer (id, params) {
  return Axios.put(`/product/${id}/server`, params);
}
// 插件列表
function getAddon (params) {
  return Axios.get(`/active_plugin`, { params });
}

// 选择接口获取配置
function getProductConfig (id, params) {
  return Axios.get(`/product/${id}/server/config_option`, { params });
}

// 删除商品
function deleteProduct (id) {
  return Axios.delete(`/product/${id}`);
}
// 批量删除商品
function batchDeleteProduct (params) {
  return Axios.delete(`/product`, { params });
}
// 隐藏/显示商品
function toggleShow (id, hidden) {
  return Axios.put(`/product/${id}/${hidden}`);
}
// 分组显示/隐藏
function groupListShow (id, hidden) {
  return Axios.put(`/product/group/${id}/${hidden}`);
}

// 商品拖动排序
function changeOrder (params) {
  return Axios.put(`/product/order/${params.id}`, params);
}
// 获取商品一级分组
function getFirstGroup () {
  return Axios.get(`/product/group/first`);
}
// 获取商品二级分组
function getSecondGroup () {
  return Axios.get(`/product/group/second`);
}
// 新建商品分组
function addGroup (params) {
  return Axios.post(`/product/group`, params);
}
// 编辑商品分组
function updateGroup (params) {
  return Axios.put(`/product/group/${params.id}`, params);
}
// 删除商品分组
function deleteGroup (id) {
  return Axios.delete(`/product/group/${id}`);
}
// 获取产品相关的可升降级的商品
function getRelationList (id) {
  return Axios.get(`/product/${id}/upgrade`);
}

// 一级商品分组排序
function moveFirstGroup (params) {
  return Axios.put(`/product/group/first/order/${params.id}`, params);
}
// 移动商品至其他商品组
function moveProductGroup (params) {
  return Axios.put(`/product/group/${params.id}/product`, params);
}
// 拖动商品至其他二级分组
function dragProductGroup (params) {
  return Axios.put(`/product/order/${params.id}`, params);
}
// 拖动二级商品分组
function draySecondGroup (params) {
  return Axios.put(`/product/group/order/${params.id}`, params);
}
// 短信接口
function getSmsInterface () {
  return Axios.get("/sms");
}
// 邮件接口
function getEmailInterface () {
  return Axios.get("/email");
}
// 短信模板
function getSmsTemplate (name) {
  return Axios.get(`/notice/sms/${name}/template`);
}
// 邮件模板
function getEmailTemplate () {
  return Axios.get(`/notice/email/template`);
}
// 接口
function getInterface (params) {
  return Axios.get("/server", { params });
}
// 接口分组
function getGroup (params) {
  return Axios.get("/server/group", { params });
}

// 保存可代理商品
function agentable (params) {
  return Axios.put(`/product/agentable`, params);
}

// 复制商品
function copyProduct (params) {
  return Axios.post(`/product/${params.id}/copy`, params);
}

// 自定义字段列表
function getSelfDefinedField (params) {
  return Axios.get(`/self_defined_field`, { params });
}

// 添加自定义字段
function addSelfDefinedField (params) {
  return Axios.post(`/self_defined_field`, params);
}

// 修改自定义字段
function updateSelfDefinedField (params) {
  return Axios.put(`/self_defined_field/${params.id}`, params);
}

// 删除自定义字段
function deleteSelfDefinedField (id) {
  return Axios.delete(`/self_defined_field/${id}`);
}

// 拖动排序
function dragSelfDefinedField (params) {
  return Axios.put(`/self_defined_field/${params.id}/drag`, params);
}

/* 自定义标识 */
// 获取商品自定义标识配置
function getProductCustom (params) {
  return Axios.get(`/product/${params.id}/custom_host_name`);
}
// 保存商品自定义标识配置
function saveProductCustom (params) {
  return Axios.put(`/product/${params.id}/custom_host_name`, params);
}

/* 商品通知管理 */
function getProductNotice (type, params) {
  return Axios.get(`/product/${type}`, { params });
}
function saveProductNotice (type, params) {
  return Axios.put(`/product/${type}`, params);
}

// 同步价格
function syncUpstreamPrice (id) {
  return Axios.get(`/upstream/product/${id}/sync`);
}

// 获取商品全局设置
function apiGetProductConfig (params) {
  return Axios.get(`/configuration/product`, { params });
}

// 保存商品全局设置
function apiSaveProductConfig (params) {
  return Axios.put(`/configuration/product`, params);
}

// 关联商品组
function apiRelatedProductGroup (params) {
  return Axios.put(
    `/self_defined_field/${params.id}/related_product_group`,
    params
  );
}

// 自定义产品标识列表
function apiGetCustomHostNames (params) {
  return Axios.get(`/custom_host_name`, { params });
}

// 添加自定义产品标识
function apiAddCustomHostName (params) {
  return Axios.post(`/custom_host_name`, params);
}

// 修改自定义产品标识
function apiUpdateCustomHostName (params) {
  return Axios.put(`/custom_host_name/${params.id}`, params);
}

// 删除自定义产品标识
function apiDeleteCustomHostName (params) {
  return Axios.delete(`/custom_host_name/${params.id}`, { params });
}

// 关联商品组
function apiRelatedHostNameGroup (params) {
  return Axios.put(
    `/custom_host_name/${params.id}/related_product_group`,
    params
  );
}

// 预设列表
function apiGetDurationGroup (params) {
  return Axios.get(`/product_duration_group_presets`, { params });
}

// 新建周期配置组
function apiAddDurationGroup (params) {
  return Axios.post(`/product_duration_group_presets`, params);
}

// 修改周期配置组
function apiUpdateDurationGroup (params) {
  return Axios.put(`/product_duration_group_presets/${params.id}`, params);
}

// 删除周期配置组
function apiDeleteDurationGroup (params) {
  return Axios.delete(`/product_duration_group_presets/${params.id}`, params);
}
// 周期配置组复制
function apiCopyDurationGroup (params) {
  return Axios.post(
    `/product_duration_group_presets/${params.id}/copy`,
    params
  );
}

// 关联列表
function apiGetDurationGroupLink (params) {
  return Axios.get(`/product_duration_group_presets_link`, { params });
}

// 新建周期配置组关联
function apiAddDurationGroupLink (params) {
  return Axios.post(`/product_duration_group_presets_link`, params);
}

// 编辑周期配置组关联
function apiUpdateDurationGrouptLink (params) {
  return Axios.put(
    `/product_duration_group_presets_link/${params.gid}`,
    params
  );
}
// 删除周期配置组关联
function apiDeleteDurationGroupLink (params) {
  return Axios.delete(
    `/product_duration_group_presets_link/${params.gid}`,
    params
  );
}

// 接口列表
function apiGetServerList (params) {
  return Axios.get(`/server`, { params });
}

// 同步镜像日志列表
function apiSyncImageLog (params) {
  return Axios.get(`/product/sync_image_log`, { params });
}

// 同步镜像
function apiSyncImage (params) {
  return Axios.post(`/product/sync_image`, params);
}

// 根据模块获取商品列表
function apiGetProductListByModule (params) {
  return Axios.get(`/module/${params.module}/product`, { params });
}

// 本地镜像列表
function apiGetLocalImageList (params) {
  return Axios.get(`/local_image`, { params });
}

// 创建本地镜像
function apiAddLocalImage (params) {
  return Axios.post(`/local_image`, params);
}

// 编辑本地镜像
function apiEditLocalImage (params) {
  return Axios.put(`/local_image/${params.id}`, params);
}

// 删除本地镜像
function apiDelLocalImage (params) {
  return Axios.delete(`/local_image/${params.id}`, { params });
}

// 本地镜像排序
function apiDragLocalImage (params) {
  return Axios.put(`/local_image/order`, params);
}

// 本地镜像分组列表
function apiLocalImageGroupList (params) {
  return Axios.get(`/local_image_group`, { params });
}

// 创建本地镜像分组
function apiAddLocalImageGroup (params) {
  return Axios.post(`/local_image_group`, params);
}

// 编辑本地镜像分组
function apiEditLocalImageGroup (params) {
  return Axios.put(`/local_image_group/${params.id}`, params);
}

// 删除本地镜像分组
function apiDelLocalImageGroup (params) {
  return Axios.delete(`/local_image_group/${params.id}`, { params });
}

// 本地镜像分组排序
function apiDragLocalImageGroup (params) {
  return Axios.put(`/local_image_group/order`, params);
}

// 获取游客可见商品
function apiTouristVisibleProduct (params) {
  return Axios.get(`/configuration/tourist_visible_product`, { params });
}

// 保存游客可见商品
function apiSaveTouristVisibleProduct (params) {
  return Axios.put(`/configuration/tourist_visible_product`, params);
}

/* 商品-通知设置 */

// 全局通知管理列表
function getGlobalNotice (params) {
  return Axios.get(`/product/notice_group`, { params });
}
function addAndEditGlobalNotice (type, params) {
  if (type === 'add') {
    return Axios.post(`/product/notice_group`, params);
  } else if (type === 'edit') {
    return Axios.put(`/product/notice_group/${params.id}`, params);
  }
}
// 删除触发动作组
function delGlobalNotice (params) {
  return Axios.delete(`/product/notice_group/${params.id}`, { params });
}
// 修改触发动作组动作状态
function changeRuleStatus (params) {
  return Axios.put(`/product/notice_group/${params.id}/act/status`, params);
}


/* 全局设置-按需 */
// 全局按需设置
function getGlobalDemand () {
  return Axios.get("/configuration/global_on_demand");
}
// 保存全局按需设置
function saveGlobalDemand (params) {
  return Axios.put(`/configuration/global_on_demand`, params);
}

// 全局自定义字段切换
function apiChangeIsGlobal (params) {
  return Axios.put(`/self_defined_field/${params.id}/is_global`, params);
}


/* 区域组 */
// 魔方云区域组列表
function getRegionalList (params) {
  return Axios.get(`/mf_cloud_data_center_map_group`, { params });
}
function addAndEditRegional (type, params) {
  if (type === "add") {
    return Axios.post(`/mf_cloud_data_center_map_group`, params);
  } else if (type === "update") {
    return Axios.put(`/mf_cloud_data_center_map_group/${params.id}`, params);
  }
}
function deleteRegional (params) {
  return Axios.delete(`/mf_cloud_data_center_map_group/${params.id}`);
}
// 根据商品ID获取数据中心
function getProductDataCenter (params) {
  return Axios.get(`/product/${params.id}/mf_cloud_data_center`, { params });
}

// 根据模块获取商品列表
function getModuleProduct (params) {
  return Axios.get(`/module/product`, { params });
}

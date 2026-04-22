/*
 * 魔方云
 */
const base = "mf_cloud";

/* 周期 */
function getDuration (params) {
  return Axios.get(`/${base}/duration`, { params });
}
function createAndUpdateDuration (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/duration`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/duration/${params.id}`, params);
  }
}
function delDuration (params) {
  return Axios.delete(`/${base}/duration/${params.id}`);
}
// 获取周期比例
function getDurationRatio (params) {
  return Axios.get(`/${base}/duration_ratio`, { params });
}
function saveDurationRatio (params) {
  return Axios.put(`/${base}/duration_ratio`, params);
}
// 周期比例填充
function fillDurationRatio (params) {
  return Axios.post(`/${base}/duration_ratio/fill`, params);
}
/* 操作系统 */
// 分类
function getImageGroup (params) {
  return Axios.get(`/${base}/image_group`, { params });
}
function createAndUpdateImageGroup (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/image_group`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/image_group/${params.id}`, params);
  }
}
function delImageGroup (params) {
  return Axios.delete(`/${base}/image_group/${params.id}`);
}
// 镜像分组排序
function changeImageGroup (params) {
  return Axios.put(`/${base}/image_group/order`, params);
}
// 系统
function getImage (params) {
  return Axios.get(`/${base}/image`, { params });
}
function createAndUpdateImage (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/image`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/image/${params.id}`, params);
  }
}
function delImage (params) {
  return Axios.delete(`/${base}/image/${params.id}`);
}
function batchDelImage (params) {
  return Axios.delete(`/${base}/image`, {
    data: params,
  });
}
// 拉取系统
function refreshImage (params) {
  return Axios.get(`/${base}/image/sync`, { params });
}
// 拉取本地操作系统
function apiPullLocalImage (params) {
  return Axios.get(`/${base}/local_image/sync`, { params });
}

/* 其他设置 */
function getCloudConfig (params) {
  return Axios.get(`/${base}/config`, { params });
}
function saveCloudConfig (params) {
  return Axios.put(`/${base}/config`, params);
}
function changeCloudSwitch (params) {
  // 存储tab切换性能
  return Axios.put(`/${base}/config/disk_limit_enable`, params);
}

/* 计算配置 */
// cpu配置
function getCpu (params) {
  return Axios.get(`/${base}/cpu`, { params });
}
function createAndUpdateCpu (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/cpu`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/cpu/${params.id}`, params);
  }
}
function delCpu (params) {
  return Axios.delete(`/${base}/cpu/${params.id}`);
}
function getCpuDetails (params) {
  return Axios.get(`/${base}/cpu/${params.id}`, { params });
}
// 内存
function getMemory (params) {
  return Axios.get(`/${base}/memory`, { params });
}
function createAndUpdateMemory (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/memory`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/memory/${params.id}`, params);
  }
}
function delMemory (params) {
  return Axios.delete(`/${base}/memory/${params.id}`);
}
function getMemoryDetails (params) {
  return Axios.get(`/${base}/memory/${params.id}`, { params });
}

/*
  存储配置
  系统盘 + 数据盘
 */
// name：接口名字(system_disk,data_disk,system_disk_limit,data_disk_limit)
// type: 新增，编辑
// parasm：参数
function getStore (name, params) {
  return Axios.get(`/${base}/${name}`, { params });
}
function createAndUpdateStore (name, type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/${name}`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/${name}/${params.id}`, params);
  }
}
function delStore (name, params) {
  return Axios.delete(`/${base}/${name}/${params.id}`);
}
function getStoreDetails (name, params) {
  return Axios.get(`/${base}/${name}/${params.id}`, { params });
}
// 添加系统盘性能限制
function getStoreLimit (name, params) {
  return Axios.get(`/${base}/${name}`, { params });
}
function createAndUpdateStoreLimit (name, type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/${name}`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/${name}/${params.id}`, params);
  }
}
function delStoreLimit (name, params) {
  return Axios.delete(`/${base}/${name}/${params.id}`);
}

/* 推荐配置 */
function getRecommend (params) {
  return Axios.get(`/${base}/recommend_config`, { params });
}
function getRecommendDetails (params) {
  return Axios.get(`/${base}/recommend_config/${params.id}`);
}
function createAndUpdateRecommend (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/recommend_config`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/recommend_config/${params.id}`, params);
  }
}
function delRecommend (params) {
  return Axios.delete(`/${base}/recommend_config/${params.id}`);
}
// 切换仅售卖套餐开关
function changeSalePackage (params) {
  return Axios.put(`/${base}/config/only_sale_recommend_config`, params);
}
// 切换不可升降级订购页提示开关
function changeUpgradeShow (params) {
  return Axios.put(`/${base}/config/no_upgrade_tip_show`, params);
}
// 保存套餐升降级范围
function saveUpgradeRange (params) {
  return Axios.put(`/${base}/recommend_config/upgrade_range`, params);
}

// 切换订购是否显示
function changePackageShow (params) {
  return Axios.put(`/${base}/recommend_config/${params.id}/hidden`, params);
}
// 切换升降级是否显示
function changePackageUpgradeShow (params) {
  return Axios.put(
    `/${base}/recommend_config/${params.id}/upgrade_show`,
    params
  );
}

/* 数据中心 */
function getDataCenter (params) {
  return Axios.get(`/${base}/data_center`, { params });
}
function getCountry () {
  return Axios.get(`/country`);
}
// 创建/修改
function createOrUpdateDataCenter (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/data_center`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/data_center/${params.id}`, params);
  }
}
// 删除
function deleteDataCenter (params) {
  return Axios.delete(`/${base}/data_center/${params.id}`);
}
// 数据中心选择
function chooseDataCenter (params) {
  return Axios.get(`/${base}/data_center/select`, { params });
}
/* 线路 */
function getLine (params) {
  return Axios.get(`/${base}/line`, { params });
}
function createAndUpdateLine (type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/line`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/line/${params.id}`, params);
  }
}
function delLine (params) {
  return Axios.delete(`/${base}/line/${params.id}`);
}
function getLineDetails (params) {
  return Axios.get(`/${base}/line/${params.id}`);
}
/* 线路-子项配置 */
// name：接口名字(line_bw,line_flow,line_defence,line_ip)
// type: 新增，编辑
// parasm：参数
function getLineChiLd (name, params) {
  return Axios.get(`/${base}/${name}/${params.id}`, { params });
}
function createAndUpdateLineChild (name, type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/line/${params.id}/${name}`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/${name}/${params.id}`, params);
  }
}
function getLineChildDetails (name, params) {
  return Axios.get(`/${base}/${name}/${params.id}`, { params });
}
function delLineChild (name, params) {
  return Axios.delete(`/${base}/${name}/${params.id}`);
}
// 获取系统盘/数据盘类型 system_disk , data_disk
function getDiskType (type, params) {
  return Axios.get(`/${base}/${type}/type`, { params });
}

// 配置限制
function getConfigLimit (params) {
  return Axios.get(`/${base}/limit_rule`, { params });
}
function createAndUpdateConfigLimit (type, params) {
  if (type === "add" || type === "copy") {
    return Axios.post(`/${base}/limit_rule`, params);
  } else if (type === "update") {
    return Axios.put(`/${base}/limit_rule/${params.id}`, params);
  }
}
function delConfigLimit (params) {
  return Axios.delete(`/${base}/limit_rule/${params.id}`);
}

// 检查切换类型后是否清空冲突数据
function checkType (params) {
  return Axios.post(`/${base}/config/check_clear`, params);
}

// 保存数据盘数量限制
function saveDiskNumLimit (params) {
  return Axios.post(`/${base}/config/disk_num_limit`, params);
}
// 保存免费数据盘
function saveFreeData (params) {
  return Axios.post(`/${base}/config/free_disk`, params);
}

// 后台详情
function apiOperateDetail (params) {
  return Axios.get(`/${base}/${params.id}`, { params });
}
// 操作
function handelOperate (params) {
  return Axios.post(`/${base}/${params.id}/${params.op}`, params);
}
// 获取实例状态
function getInstanceStatus (id) {
  return Axios.get(`/${base}/${id}/status`);
}
// 获取魔方云远程信息
function getMfCloudRemote (id) {
  return Axios.get(`/${base}/${id}/remote_info`);
}

// SSH密钥列表
function apiSshList (params) {
  return Axios.get(`/ssh_key`, { params });
}

// 添加数据中心显卡配置
function apiAddGpu (params) {
  return Axios.post(`/${base}/data_center/${params.id}/gpu`, params);
}

// 修改数据中心显卡配置
function apiEditGpu (params) {
  return Axios.put(`/${base}/data_center/gpu/${params.id}`, params);
}

// 删除数据中心显卡配置
function apiDelGpu (params) {
  return Axios.delete(`/${base}/data_center/gpu/${params.id}`, { params });
}

// 删除数据中心显卡
function apiDelDataCenterGpu (params) {
  return Axios.delete(`/${base}/data_center/${params.id}/gpu`, { params });
}
// 数据中心显卡配置详情
function apiGpuDetail (params) {
  return Axios.get(`/${base}/data_center/gpu/${params.id}`, { params });
}

// 修改数据中心GPU型号名称
function apiEditGpuName (params) {
  return Axios.put(`/${base}/data_center/${params.id}/gpu_name`, params);
}

// 数据中心详情
function apiDataCenterDeta (params) {
  return Axios.get(`/${base}/data_center/${params.id}`, { params });
}

// 切换订购是否显示
function changeLineStatus (params) {
  return Axios.put(`/${base}/line/${params.id}/hidden`, params);
}

// 拖动排序
function apiDragImage (params) {
  return Axios.put(`/${base}/image/${params.id}/drag`, params);
}
// 商品详情
function getServeProductDetail (id) {
  return Axios.get(`/product/${id}`);
}
// 获取网络流量
function getModuleFlow (params) {
  return Axios.get(`/${base}/${params.id}/flow`);
}

/* 新增防火墙 */
// 保存全局防御设置
function saveDefenceConfig (params) {
  return Axios.put(`/${base}/config/global_defence`, params);
}
// 获取防火墙防御规则
function getDefenceRule (params) {
  return Axios.get(`/${base}/firewall_defence_rule`, { params });
}
// 导入防火墙防御规则
function importDefenceRule (params) {
  return Axios.post(`/${base}/firewall_defence_rule`, params);
}
// 全局防护配置列表
function getGlobalDefence (params) {
  return Axios.get(`/${base}/global_defence`, { params });
}
// 全局防护配置详情
function getGlobalDefenceDetail (params) {
  return Axios.get(`/${base}/global_defence/${params.id}`);
}
// 修改全局防护配置
function editGlobalDefence (params) {
  return Axios.put(`/${base}/global_defence/${params.id}`, params);
}
// 删除全局防护配置
function delGlobalDefence (params) {
  return Axios.delete(`/${base}/global_defence/${params.id}`);
}
// 线路导入防火墙防御规则
function lineImportDefenceRule (params) {
  return Axios.post(
    `/${base}/line/${params.line_id}/firewall_defence_rule`,
    params
  );
}
function getActivePlugin (params) {
  return Axios.get(`/active_plugin`, { params });
}

// 试用配置
function apiSavePayOnTrial (params) {
  return Axios.put(`/product/${params.id}/pay_ontrial`, params);
}

// 切换订购是否显示
function changeOntrialShow (params) {
  return Axios.put(`/${base}/recommend_config/${params.id}/ontrial`, params);
}

// 线路防护配置拖动排序
function apiDefenceDragSort (params) {
  return Axios.put(`/${base}/line_defence/${params.id}/drag_sort`, params);
}

// 全局防护配置拖动排序
function apiGlobalDefenceDragSort (params) {
  return Axios.put(`/${base}/global_defence/${params.id}/drag_sort`, params);
}

/* 按需 */
// 获取商品按需计费项目
function getDemandItem (params) {
  return Axios.get(`/product/${params.id}/on_demand/billing_item`, { params });
}
// 商品按需计费配置详情
function getDemandDetail (params) {
  return Axios.get(`/product/${params.id}/on_demand`, { params });
}
// 修改商品按需计费配置
function saveDemandDetail (params) {
  return Axios.put(`/product/${params.id}/on_demand`, params);
}
// 线路流量按需配置详情
function lineDemandDetail (params) {
  return Axios.get(`/mf_cloud/line_flow_on_demand/${params.id}`, { params });
}
// 添加线路流量按需配置
function addLineDemandConfig (params) {
  return Axios.post(`/mf_cloud/line/${params.id}/line_flow_on_demand`, params);
}
// 修改线路流量按需配置
function editLineDemandConfig (params) {
  return Axios.put(`/mf_cloud/line_flow_on_demand/${params.id}`, params);
}
// 删除线路流量按需配置
function delLineDemandConfig (params) {
  return Axios.delete(`/mf_cloud/line_flow_on_demand/${params.id}`, { params });
}


/* 内页操作 */
// 获取默认带宽分组IP
function getIpList (type, params) {
  if (type === 'delete') {
    return Axios.get(`/${base}/${params.id}/ip`, { params });
  } else if (type === 'add') {
    return Axios.get(`/${base}/${params.id}/ip/free`, { params });
  } else if (type === 'change') {
    return Axios.get(`/${base}/${params.id}/ip/enable`, { params });
  }
}
function handleIpOpt (type, params) {
  if (type === 'delete') {
    return Axios.delete(`/${base}/${params.id}/ip`, { params });
  } else if (type === 'add') {
    return Axios.post(`/${base}/${params.id}/ip`, params);
  } else if (type === 'change') {
    return Axios.put(`/${base}/${params.id}/ip`, params);
  }
}
// 获取产品详情
function getProductDetail (id) {
  return Axios.get(`/host/${id}`);
}

function saveDiskRangeLimit (params) {
  return Axios.put(`/${base}/config/disk_range_limit`, params);
}

// 设置默认周期
function setDefaultDuration (params) {
  return Axios.put(`/${base}/duration/default`, params);
}

function cloudCreateToken () {
  return Axios.post(`/app_market/set_token`);
}


/* 安全组 */

// 获取安全组配置列表
function getSecurityGroupConfigList(params) { 
  return Axios.get(`/mf_cloud/security_group_config`, {params});
}
// 添加安全组配置
function addSecurityGroupConfig(params) { 
  return Axios.post(`/mf_cloud/security_group_config`, params);
}
// 编辑安全组配置
function editSecurityGroupConfig(params) { 
  return Axios.put(`/mf_cloud/security_group_config/${params.id}`, params);
}
// 删除安全组配置
function deleteSecurityGroupConfig(params) { 
  return Axios.delete(`/mf_cloud/security_group_config/${params.id}`, {params});
}
// 重置为默认安全组配置
function resetSecurityGroupConfig(params) { 
  return Axios.post(`/mf_cloud/security_group_config/reset`, params);
}
// 安全组配置排序
function sortSecurityGroupConfig(params) { 
  return Axios.put(`/mf_cloud/security_group_config/sort`, params);
}

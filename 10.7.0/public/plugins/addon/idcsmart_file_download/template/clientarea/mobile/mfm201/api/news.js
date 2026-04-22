// 帮助中心首页
function helpIndex(params) {
  return Axios.get(`/help/index`, { params });
}
// 帮助文档列表
function helpList(params) {
  return Axios.get(`/help`, { params });
}
// 帮助文档详情
function helpDetails(params) {
  return Axios.get(`/help/${params.id}`, { params });
}
// 下载文件
function apiDownloadFile(id) {
  return Axios.get(`/file/${id}/download`);
}

// 获取新闻分类
function apiNewsType() {
  return Axios.get(`/news/type`);
}
// 新闻列表
function apiNewsList(params) {
  return Axios.get(`/news`, { params });
}
// 新闻详情
function getNewsDetail(id) {
  return Axios.get(`/news/${id}`);
}

// 获取文件夹
function getFileFolder() {
  return Axios.get(`/file/folder`);
}
// 文件列表
function getFileList(params) {
  return Axios.get(`/file`, { params });
}

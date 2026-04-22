(function () {
  /* mf_cloud */
  const module_lang = {
    "zh-cn": {
      cloud_on: "开机",
      cloud_off: "关机",
      cloud_suspend: "暂停",
      cloud_operating: "操作中",
      cloud_fault: "故障",
      format_data_disk: "格式化数据盘",
      public_image: "公共镜像",
      three_part_image: "三方镜像",
      recommend_image: "推荐镜像",
    },
    "zh-hk": {
      cloud_on: "開機",
      cloud_off: "關機",
      cloud_suspend: "暫停",
      cloud_operating: "操作中",
      cloud_fault: "故障",
      format_data_disk: "格式化數據盤",
      public_image: "公共鏡像",
      three_part_image: "三方鏡像",
      recommend_image: "推薦鏡像",
    },
    "en-us": {
      cloud_on: "on",
      cloud_off: "off",
      cloud_suspend: "suspend",
      cloud_operating: "operating",
      cloud_fault: "fault",
      format_data_disk: "format data disk",
      public_image: "Public Image",
      three_part_image: "Third-party Image",
      recommend_image: "Recommended Image",
    },
  };
  const DEFAULT_LANG = localStorage.getItem("backLang") || "zh-cn";
  window.module_lang = module_lang[DEFAULT_LANG];
})();

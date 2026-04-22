// 判断首页是否为推荐页面
(function () {
  //设置cookie
  const setCookie = (c_name, value, expiredays = 1) => {
    const exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie =
      c_name + "=" + value + (";expires=" + exdate.toGMTString());
  };
  // 获取url地址栏参数函数
  const getUrlParams = () => {
    const url = window.location.href;
    // 判断是否有参数
    if (url.indexOf("?") === -1) {
      return {};
    }
    const params = url.split("?")[1];
    const paramsArr = params.split("&");
    const paramsObj = {};
    paramsArr.forEach((item) => {
      const key = item.split("=")[0];
      const value = item.split("=")[1];
      // 解析中文
      paramsObj[key] = decodeURI(value);
    });
    return paramsObj;
  };
  const urlParams = getUrlParams();
  if (urlParams.recommend_c) {
    setCookie("recommend_c", urlParams.recommend_c);
  }
})();
(function () {
  // 动态计算根元素的fontsize
  let sizeWidth = document.documentElement.clientWidth; // 初始宽宽度
  function setRootFontSize() {
    let rem, rootWidth;
    let rootHtml = document.documentElement;
    if (sizeWidth > rootHtml.clientWidth) {
      if (sizeWidth > 750 && rootHtml.clientWidth <= 750) {
        // window.location.reload();
      }
    } else {
      if (sizeWidth <= 750 && rootHtml.clientWidth > 750) {
        // window.location.reload();
      }
      // 大于750时 刷新页面
    }
    sizeWidth = rootHtml.clientWidth;
    if (rootHtml.clientWidth > 750) {
      //限制展现页面的最小宽度
      rootWidth =
        rootHtml.clientWidth <= 1200
          ? 1200
          : rootHtml.clientWidth >= 1920
          ? 1920
          : rootHtml.clientWidth;
      // rootWidth = rootHtml.clientWidth;
      // 19.2 = 设计图尺寸宽 / 100（ 设计图的rem = 100 ）
      rem = rootWidth / 19.2;
      // 动态写入样式
      rootHtml.style.fontSize = `${rem}px`;
    } else {
      rootWidth = rootHtml.clientWidth;
      rem = rootWidth / 7.5;
      rootHtml.style.fontSize = `${rem}px`;
    }
  }
  setRootFontSize();
  window.addEventListener("resize", setRootFontSize, false);
})();
if (!localStorage.getItem("common_set_before")) {
  localStorage.setItem("common_set_before", JSON.stringify({}));
}
(function () {
  if (havePlugin("AiKnowledge")) {
    const script = document.createElement("script");
    script.src = "/plugins/addon/ai_knowledge/template/clientarea/ai-dialog.js";
    script.onload = function () {
      const nowUrl = location.href.split("/").pop();
      const pageRouter =
        nowUrl.indexOf("?") !== -1 ? nowUrl.split("?")[0] : nowUrl;
      const aiConfig = {
        page_type: "clientarea",
        product_id: "",
        draggable: true,
        showTrigger: false,
        position_bottom: "200px",
      };
      if (pageRouter == "goods.htm") {
        aiConfig.page_type = "product";
        aiConfig.product_id = getUrlParams().id;
      } else if (pageRouter == "productdetail.htm") {
        aiConfig.page_type = "host";
        aiConfig.host_id = getUrlParams().id;
      }
      const aiDialog = new AIDialog(aiConfig);
    };
    document.head.appendChild(script);
  }
})();

const mixin = {
  data() {
    return {
      addons_js_arr: [], // 已激活的插件
      isShowCashBtn: false, // cashBack 根据是组件里面否支持返现来判断是否展示返现按钮
      isShowCashDialog: false,
      hasAutoRenew: false, // 续费插件
    };
  },
  methods: {
    applyCashback() {
      this.isShowCashDialog = true;
    },
    showBtn(bol) {
      this.isShowCashBtn = bol;
    },
    cancleDialog() {
      this.isShowCashDialog = false;
    },
  },
  mounted() {
    const addons = document.querySelector("#addons_js");
    if (addons) {
      this.addons_js_arr = JSON.parse(addons.getAttribute("addons_js")).map(
        (item) => item.name
      );
      this.hasAutoRenew = this.addons_js_arr.includes("IdcsmartRenew");
    }
  },
};

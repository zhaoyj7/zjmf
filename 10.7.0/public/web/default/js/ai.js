(function () {
  const addonsDom = document.querySelector("#addons_js");
  if (addonsDom) {
    const addonsArr = JSON.parse(addonsDom.getAttribute("addons_js")); // 插件列表
    const arr = addonsArr.map((item) => {
      return item.name;
    });
    if (arr.includes("AiKnowledge")) {
      const script = document.createElement("script");
      script.src =
        "/plugins/addon/ai_knowledge/template/clientarea/ai-dialog.js";
      script.onload = function () {
        const aiConfig = {
          page_type: "index",
          product_id: "",
          draggable: true,
          showTrigger: false,
          position_bottom: "200px",
        };
        const aiDialog = new AIDialog(aiConfig);
      };
      document.head.appendChild(script);
    }
  }
})();

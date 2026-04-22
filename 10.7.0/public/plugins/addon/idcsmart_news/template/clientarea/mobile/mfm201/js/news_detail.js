(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    typeof old_onload == "function" && old_onload();
    window.lang = Object.assign(window.lang, window.plugin_lang);
    const {showToast} = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
        shadowContent,
      },

      data() {
        return {
          lang: window.lang,
          id: "",
          params: {
            page: 1,
            limit: 20,
            pageSizes: [10, 20, 50],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          commonData: {},
          folder: [],
          folderNum: 0,
          curId: "",
          tableData: [],
          loading: false,
          curTit: "",
          newDetail: "",
          baseUrl: url,
        };
      },
      computed: {},
      created() {
        this.id = location.href.split("?")[1].split("=")[1];
        this.getCommonData();
        this.getData();
      },
      mounted() {},
      methods: {
        formateByte(size) {
          if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + "KB";
          } else {
            return size / (1024 * 1024).toFixed(2) + "MB";
          }
        },
        calStr(str) {
          const temp =
            str &&
            str
              .replace(/&lt;/g, "<")
              .replace(/&gt;/g, ">")
              .replace(/&quot;/g, '"')
              .replace(/&amp;lt;/g, "<")
              .replace(/&amp;gt;/g, ">")
              .replace(/ &amp;lt;/g, "<")
              .replace(/&amp;gt; /g, ">")
              .replace(/&amp;gt; /g, ">")
              .replace(/&amp;quot;/g, '"')
              .replace(/&amp;amp;nbsp;/g, " ")
              .replace(/&amp;#039;/g, "'")
              .replace("<?php", "&lt;?php");
          return temp;
        },
        goBack() {
          history.go(-1);
        },
        goOtherPage(id) {
          location.href = `news_detail.htm?id=${id}`;
        },
        async getData() {
          try {
            const res = await getNewsDetail(this.id);
            if (res.data.status === 200) {
              this.newDetail = res.data.data.news;
              this.params.total = res.data.data.count;
            }
          } catch (error) {
            console.log(error);
            error.data && showToast(error.data.msg);
          }
        },
        // 附件下载
        downloadfile(url) {
          window.open(url);
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title = this.commonData.website_name + `-${lang.news_text4}`;
        },
      },
    });
    window.directiveInfo.forEach((item) => {
      app.directive(item.name, item.fn);
    });
    app.use(vant).mount("#template");
  };
})(window);

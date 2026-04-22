(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    typeof old_onload == "function" && old_onload();
    window.lang = Object.assign(window.lang, window.plugin_lang);
    const { showToast } = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
      },
      data() {
        return {
          lang: window.lang,
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          isShowMenu: false,
          commonData: {},
          detailData: {},
          activeNames: [],
          helpIndexList: [],
          allHelpList: [],
          defaultActiveItem: {},
          newType: [],
          activeIndex: "3",
          selectNewType: "",
          selectDownType: "",
          downType: [],
          newLoading: false,
          newsFinished: false,
          newParams: {
            page: 1,
            limit: 20,
            pageSizes: [10, 20, 50],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          downParams: {
            page: 1,
            limit: 10,
            pageSizes: [10, 20, 50],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          downLoading: false,
          downFinished: false,
          newsList: [],
          downList: [],
        };
      },
      created() {
        this.getDownTypeList();
        this.getCommonData();
      },
      computed: {
        newTypeText() {
          return (
            this.newType.find((item) => item.value === this.selectNewType)
              ?.text || ""
          );
        },
        downLoadTypeText() {
          return (
            this.downType.find((item) => item.value === this.selectDownType)
              ?.text || ""
          );
        },
      },
      mounted() {},
      methods: {
        havePlugin,
        // 关键字搜索
        inputChange() {
          const params = {
            keywords: this.params.keywords,
          };
          helpList(params).then((res) => {
            if (res.data.status === 200) {
              const list = res.data.data.list;
              const searItem = list.find((item) => {
                return item.helps.find((items) => {
                  return items.search;
                });
              });
              const searchId =
                searItem.helps.find((item) => {
                  return item.search;
                })?.id || "";
              if (!searchId) {
                showToast(lang.source_text8);
                return;
              }
              this.itemClick(searchId);
            }
          });
        },
        goNewDetail(id) {
          location.href = `new_detail.htm?id=${id}`;
        },
        // 获取帮助文档列表
        getHelpList() {
          const params = {
            keywords: "",
          };
          helpList(params).then((res) => {
            if (res.data.status === 200) {
              this.allHelpList = res.data.data.list;
              const selectId = this.allHelpList.find((item, index) => {
                return item.helps.length !== 0;
              }).helps[0].id;
              if (!this.defaultActiveItem.id) {
                this.itemClick(selectId);
              }
            }
          });
        },
        handelClickfile(id) {
          apiDownloadFile(id)
            .then((res) => {
              window.open(res.data.data.url);
            })
            .catch((err) => {
              showToast(err.data.msg);
            });
        },
        // 附件下载
        downloadfile(url) {
          window.open(url);
        }, // 菜单项点击
        itemClick(id) {
          const item = this.allHelpList.find((item) => {
            return item.helps.find((items) => {
              return items.id === id;
            });
          });
          const docItem = item.helps.find((item) => {
            return item.id === id;
          });
          this.defaultActiveItem = {
            father_name: item.name,
            ...docItem,
          };
          this.isShowMenu = false;
          this.contentLoading = true;
          // 获取帮助文档详情
          const params = {
            id: docItem.id,
          };
          helpDetails(params).then((res) => {
            if (res.data.status === 200) {
              this.detailData = res.data.data.help;
            }
            this.contentLoading = false;
          });
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
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.source_tab1;
        },
        getHelpIndex() {
          helpIndex(this.params).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.index;
              this.helpIndexList = list;
            }
          });
        },
        // 去帮助中心汇总
        toHelpTotal() {
          location.href = `helpTotal.htm`;
        },
        // 帮助详情
        goToDetail(id) {
          this.activeIndex = "4";
          this.itemClick(id);
        },
        // 获取新闻列表
        async getNewTypeList() {
          try {
            const res = await apiNewsType();
            if (res.data.status === 200) {
              this.newType = [{ text: lang.file_all, value: "" }].concat(
                res.data.data.list.map((item) => {
                  item.text = item.name;
                  item.value = item.id;
                  return item;
                })
              );
              this.initNewList();
            }
          } catch (error) {
            console.log(error);
          }
        },
        // 获取下载列表
        async getDownTypeList() {
          try {
            const res = await getFileFolder();
            if (res.data.status === 200) {
              this.downType = [{ text: lang.file_all, value: "" }].concat(
                res.data.data.list.map((item) => {
                  item.text = item.name;
                  item.value = item.id;
                  return item;
                })
              );
              this.initDownList();
            }
          } catch (error) {
            console.log(error);
          }
        },
        initNewList() {
          this.newsList = [];
          this.newParams.page = 1;
          this.getNewList();
        },
        getNewList() {
          const params = {
            addon_idcsmart_file_download_type_id: this.selectNewType,
            ...this.newParams,
          };
          delete params.pageSizes;
          delete params.total;
          this.newLoading = true;
          apiNewsList(params)
            .then((res) => {
              if (res.data.status === 200) {
                this.newsList = this.newsList.concat(res.data.data.list);
                this.newLoading = false;
                this.newParams.page++;
                if (this.newsList.length >= res.data.data.count) {
                  this.newsFinished = true;
                } else {
                  this.newsFinished = false;
                }
              }
            })
            .catch((err) => {
              showToast(err.data.msg);
              this.newLoading = false;
            });
        },
        initDownList() {
          this.downList = [];
          this.downParams.page = 1;
          this.getDownList();
        },
        formateByte(size) {
          if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + "KB";
          } else {
            return (size / (1024 * 1024)).toFixed(2) + "MB";
          }
        },
        getDownList() {
          const params = {
            addon_idcsmart_file_folder_id: this.selectDownType,
            ...this.downParams,
          };
          delete params.pageSizes;
          delete params.total;
          this.downLoading = true;
          getFileList(params)
            .then((res) => {
              if (res.data.status === 200) {
                this.downList = this.downList.concat(res.data.data.list);
                this.downLoading = false;
                this.downParams.page++;
                if (this.downList.length >= res.data.data.count) {
                  this.downFinished = true;
                } else {
                  this.downFinished = false;
                }
              }
            })
            .catch((err) => {
              showToast(err.data.msg);
              this.downLoading = false;
            });
        },
        goBack() {
          history.go(-1);
        },
        handleClick() {
          if (this.activeIndex == "1") {
            location.href = `/plugin/${getPluginId(
              "IdcsmartHelp"
            )}/source.htm?`;
          }
          if (this.activeIndex == "4") {
            location.href = `/plugin/${getPluginId(
              "IdcsmartHelp"
            )}/source.htm?activeIndex=4`;
          }
          if (this.activeIndex == "2") {
            location.href = `/plugin/${getPluginId("IdcsmartNews")}/source.htm`;
          }
        },
      },
    });
    window.directiveInfo.forEach((item) => {
      app.directive(item.name, item.fn);
    });
    app.use(vant).mount("#template");
  };
})(window);

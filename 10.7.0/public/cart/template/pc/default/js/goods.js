var orignalSetItem = localStorage.setItem;
localStorage.setItem = function (key, newValue) {
  var setItemEvent = new Event("setItemEvent");
  setItemEvent.newValue = newValue;
  window.dispatchEvent(setItemEvent);
  orignalSetItem.apply(this, arguments);
};
(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("goods")[0];
    Vue.prototype.lang = window.lang;
    window.addEventListener("setItemEvent", function (e) {
      if (e.newValue && String(e.newValue).indexOf("cartNum") !== -1) {
        vm._data.shoppingCarNum = e.newValue.split("-")[1] * 1;
      }
    });
    const vm = new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created() {
        const params = getUrlParams();
        this.id = params.id;
        // 只获取 commonData 配置，不触发 getGoodDetail
        this.commonData = JSON.parse(
          localStorage.getItem("common_set_before")
        );
      },
      mounted() {
        if (window.self !== window.top) {
          this.isIfram = true;
        }
        // 先加载 content，完成后再加载 popover 数据，避免并发请求过多
        this.getList().then(() => {
          if (this.commonData.cart_change_product == 1) {
            this.getGoodDetail(this.id);
          }
        });
      },
      updated() {
        // // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("goods")[0].style.display = "block";
      },
      computed: {
        calcProductGroup() {
          // fillterKey 过滤关键字 secProductGroupList
          const fillterKey = this.fillterKey.trim().toLowerCase();
          const originList = JSON.parse(
            JSON.stringify(this.secProductGroupList)
          );
          const arr = originList
            .filter((item) => {
              return (
                item.goodsList.filter((i) => {
                  return i.name.toLowerCase().indexOf(fillterKey) !== -1;
                }).length > 0
              );
            })
            .map((item) => {
              item.goodsList = item.goodsList.filter((i) => {
                return i.name.toLowerCase().indexOf(fillterKey) !== -1;
              });
              return item;
            });
          return arr;
        },
      },
      data() {
        return {
          id: "",
          isIfram: false,
          shoppingCarNum: 0,
          fillterKey: "",
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          commonData: {},
          content: "",
          productInfo: {},
          secProductGroupList: [],
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
      },
      methods: {
        async getList() {
          try {
            const params = { id: this.id, flag: this.isIfram };
            const res = await getOrederConfig(params);
            this.$nextTick(() => {
              // 解决Jquery加载JS会在文件末尾添加时间戳的问题
              $.ajaxSetup({
                cache: true,
              });
              $(".config-box .content").html(res.data.data.content);
            });
            this.content = res.data.data.content;
          } catch (error) { }
        },
        handleCommand(id) {
          // 打开新页面 替换id
          location.href = `goods.htm?id=${id}`;
        },
        getGoodDetail(id) {
          apiProductDetail({ id }).then((res) => {
            this.productInfo = res.data.data.product;
            this.getProductGroup_second(
              this.productInfo.product_group_id_first
            );
          });
        },
        // 获取二级分类
        getProductGroup_second(id) {
          productGroupSecond(id).then((res) => {
            this.secProductGroupList = res.data.data.list.map((item) => {
              item.goodsList = [];
              return item;
            });
            this.getProductGoodList();
          });
        },
        // 获取商品列表
        getProductGoodList() {
          this.secProductGroupList.forEach((item) => {
            const params = {
              page: 1,
              limit: 999999,
              id: item.id,
            };
            productGoods(params).then((res) => {
              item.goodsList = res.data.data.list;
            });
          });
        },
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          // 获取列表
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e;
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          if (this.commonData.cart_change_product == 1) {
            this.getGoodDetail(this.id);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

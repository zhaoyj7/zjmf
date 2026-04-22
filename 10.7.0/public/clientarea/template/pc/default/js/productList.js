(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("product")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
        safeConfirm,
        productFilter,
        batchRenewpage,
      },
      created() {
        const params = getUrlParams();
        if (params.tab) {
          this.params.tab = params.tab;
        }
        this.getCommon();
        this.getCloudList();
      },
      mixins: [mixin], // 获取是否有续费插件 hasAutoRenew
      data() {
        return {
          imgUrl: `${url}`,
          id: 0,
          menuActiveId: 1,
          hostData: {},
          commonData: {},
          multipleSelection: [],
          client_operate_password: "",
          self_defined_field: [],
          status: {
            Unpaid: {
              text: lang.product_status6,
              color: "#F64E60",
              bgColor: "#FFE2E5",
            },
            Pending: {
              text: lang.product_status1,
              color: "#3699FF",
              bgColor: "#E1F0FF",
            },
            Active: {
              text: lang.product_status2,
              color: "#1BC5BD",
              bgColor: "#C9F7F5",
            },
            Suspended: {
              text: lang.product_status3,
              color: "#F99600",
              bgColor: "#FFF4DE",
            },
            Deleted: {
              text: lang.product_status4,
              color: "#9696A3",
              bgColor: "#F2F2F7",
            },
            Failed: {
              text: lang.product_status5,
              color: "#3699FF",
              bgColor: "#E1F0FF",
            },
            Grace: {
              text: lang.product_status7,
              color: "#ffda16",
              bgColor: "#fff9d9",
            },
            Keep: {
              text: lang.product_status8,
              color: "#ffad16",
              bgColor: "#fff2d9",
            },
          },
          statusSelect: [
            {
              id: 1,
              status: "Unpaid",
              label: lang.product_status6,
            },
            {
              id: 2,
              status: "Pending",
              label: lang.product_status1,
            },
            {
              id: 3,
              status: "Active",
              label: lang.product_status2,
            },
            {
              id: 4,
              status: "Suspended",
              label: lang.product_status3,
            },
            {
              id: 5,
              status: "Deleted",
              label: lang.product_status4,
            },
            {
              id: 6,
              status: "Grace",
              label: lang.product_status7,
            },
            {
              id: 7,
              status: "Keep",
              label: lang.product_status8,
            },
          ],
          // 数据中心
          center: [],
          isShowBaseInfo: false,
          // 产品列表
          cloudData: [],
          countData: {},
          loading: false,
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            status: "",
            tab: "",
          },
          timerId: null,
          showNodeNum: window.location.host === "my.idcsmart.com",
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
        copyIp(ip) {
          if (typeof ip !== "string") {
            ip = ip.join(",");
          }
          const textarea = document.createElement("textarea");
          textarea.value = ip.replace(/,/g, "\n");
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand("copy");
          document.body.removeChild(textarea);
          this.$message.success(lang.index_text32);
        },
        analysisUrl() {
          let url = window.location.href;
          let getqyinfo = url.split("?")[1];
          let getqys = new URLSearchParams("?" + getqyinfo);
          let m = getqys.get("m");
          this.params.m = m;
        },
        getCommon() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.product_status9;
        },
        // 切换分页
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          this.getCloudList();
        },
        currentChange(e) {
          this.params.page = e;
          this.getCloudList();
        },
        // 数据中心选择框变化时
        selectChange() {
          this.params.page = 1;
          this.getCloudList();
        },
        clearKey() {
          this.params.keywords = "";
          this.inputChange();
        },
        inputChange() {
          this.params.page = 1;
          this.getCloudList();
        },
        centerSelectChange(index) {
          const filterItem = this.center[index] || {};
          this.params.country_id = filterItem.country_id;
          this.params.city = filterItem.city;
          this.params.area = filterItem.area;
          this.params.page = 1;
          this.getCloudList();
        },
        statusSelectChange() {
          this.params.page = 1;
          this.getCloudList();
        },
        handleSelectionChange(val) {
          this.multipleSelection = val;
        },
        sortChange({prop, order}) {
          this.params.orderby = order ? prop : "id";
          this.params.sort = order === "ascending" ? "asc" : "desc";
          this.getCloudList();
        },
        // 获取产品列表
        getCloudList() {
          this.loading = true;
          clientHost(this.params)
            .then((res) => {
              if (res.data.status === 200) {
                this.cloudData = res.data.data.list;
                this.countData = res.data.data;
                this.params.total = res.data.data.count;
              }
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
            });
        },
        // 跳转产品详情
        toDetail(row) {
          location.href = `productdetail.htm?id=${row.id}`;
        },
        // 跳转订购页
        toOrder() {
          const id = this.id;
          location.href = `order.htm?id=${id}`;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

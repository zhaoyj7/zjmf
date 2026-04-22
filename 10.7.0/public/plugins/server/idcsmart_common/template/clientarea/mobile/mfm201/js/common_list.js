const { showToast } = vant;
window.lang = Object.assign(window.lang, window.module_lang);

const app2 = Vue.createApp({
  components: {
    topMenu,
  },
  created() {
    this.analysisUrl();
    this.getCommonData();
    this.inputChange();
  },

  data() {
    return {
      lang: window.lang,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0,
        orderby: "id",
        sort: "desc",
        keywords: "",
        status: "Active",
        m: null,
      },
      expiring_count: 0,
      commonList: [],
      finished: false,
      self_defined_field: [],
      multipleSelection: [],
      commonData: {},
      loading: false,
      status: {
        Unpaid: {
          text: lang.common_cloud_text88,
          color: "#F64E60",
          bgColor: "#FFE2E5",
        },
        Pending: {
          text: lang.common_cloud_text89,
          color: "#3699FF",
          bgColor: "#E1F0FF",
        },
        Active: {
          text: lang.common_cloud_text90,
          color: "#1BC5BD",
          bgColor: "#C9F7F5",
        },
        Suspended: {
          text: lang.common_cloud_text91,
          color: "#F0142F",
          bgColor: "#FFE2E5",
        },
        Deleted: {
          text: lang.common_cloud_text92,
          color: "#9696A3",
          bgColor: "#F2F2F7",
        },
        Failed: {
          text: lang.common_cloud_text93,
          color: "#FFA800",
          bgColor: "#FFF4DE",
        },
      },
      statusSelect: [
        {
          id: 0,
          value: "",
          text: lang.finance_label4,
        },
        {
          id: 1,
          value: "Unpaid",
          text: lang.common_cloud_text88,
        },
        {
          id: 2,
          value: "Pending",
          text: lang.common_cloud_text89,
        },
        {
          id: 3,
          value: "Active",
          text: lang.common_cloud_text90,
        },
        {
          id: 4,
          value: "Suspended",
          text: lang.common_cloud_text91,
        },
        {
          id: 5,
          value: "Deleted",
          text: lang.common_cloud_text92,
        },
      ],
      submitLoading: false,
    };
  },
  methods: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
    // 返回产品列表页
    goBack() {
      window.history.back();
    },
    handleSelectionChange(val) {
      this.multipleSelection = val;
    },
    analysisUrl() {
      let url = window.location.href;
      let getqyinfo = url.split("?")[1];
      let getqys = new URLSearchParams("?" + getqyinfo);
      let m = getqys.get("m");
      this.params.m = m;
    },
    inputChange() {
      this.params.page = 1;
      this.commonList = [];
      this.loading = true;
      this.getList();
    },
    // 获取产品列表
    getList() {
      getCommonList(this.params).then((res) => {
        if (res.data.status === 200) {
          this.commonList = this.commonList.concat(res.data.data.list);
          this.self_defined_field = res.data.data.self_defined_field;
          this.params.total = res.data.data.count;
          this.params.page++;
          this.loading = false;
          if (this.commonList.length >= res.data.data.count) {
            this.finished = true;
          } else {
            this.finished = false;
          }
        }
      });
    },

    clearKey() {
      this.params.keywords = "";
      this.inputChange();
    },
    // 跳转产品详情
    toDetail(row) {
      // if (row.status !== 'Active') {
      //   return false
      // }
      location.href = `productdetail.htm?id=${row.id}`;
    },
    // 跳转订购页
    toOrder() {
      const id = this.id;
      location.href = `goods.htm?id=${id}`;
    },

    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text221;
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#product-template");

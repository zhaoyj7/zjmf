const { showToast } = vant;
window.lang = Object.assign(window.lang, window.module_lang);

const app2 = Vue.createApp({
  components: {
    topMenu,
  },
  created() {
    this.analysisUrl();
    this.inputChange();
    this.getCommon();
    console.log(url);
  },
  data() {
    return {
      lang: window.lang,
      imgUrl: `${url}`,
      id: 0,
      menuActiveId: 1,
      hostData: {},
      commonData: {},
      self_defined_field: [],
      menuList: [
        {
          id: 1,
          text: lang.cloud_menu_1,
        },
        {
          id: 2,
          text: lang.cloud_menu_2,
        },
        {
          id: 3,
          text: lang.cloud_menu_3,
        },
        {
          id: 4,
          text: lang.cloud_menu_4,
        },
        {
          id: 5,
          text: lang.cloud_menu_5,
        },
      ],
      powerStatus: {
        on: {
          text: lang.common_cloud_text10,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/on.svg`,
        },
        off: {
          text: lang.common_cloud_text11,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/off.svg`,
        },
        operating: {
          text: lang.common_cloud_text12,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/operating.svg`,
        },
        fault: {
          text: lang.common_cloud_text86,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/fault.svg`,
        },
        suspend: {
          text: lang.common_cloud_text87,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/suspended.svg`,
        },
        pending: {
          text: lang.common_cloud_text89,
          icon: `/plugins/reserver/mf_finance_common/template/clientarea/mobile/mfm201/img/cloud/operating.svg`,
        },
      },
      finished: false,
      status: {
        Unpaid: {
          text: lang.common_cloud_text88,
          color: "#F64E60",
          bgColor: "#FFE2E5",
        },
        Pending: {
          text: lang.common_cloud_text89,
          color: "#3577f1",
          bgColor: "#E1F0FF",
        },
        Active: {
          text: lang.common_cloud_text90,
          color: "#2BA471",
          bgColor: "#E3F9E9",
        },
        Suspended: {
          text: lang.common_cloud_text91,
          color: "#F99600",
          bgColor: "#FFF4DE",
        },
        Deleted: {
          text: lang.common_cloud_text92,
          color: "#D54941",
          bgColor: "#F2F2F7",
        },
        Failed: {
          text: lang.common_cloud_text93,
          color: "#3699FF",
          bgColor: "#E1F0FF",
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
      // 数据中心
      center: [],
      // 产品列表
      cloudData: [],
      loading: false,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
        data_center_id: "",
        status: "Active",
        m: null,
      },
      timerId: null,
      showFillter: false,
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
    // 返回产品列表页
    goBack() {
      window.history.back();
    },
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
      showToast(lang.index_text32);
    },
    handelSelectStatue(item) {
      if (this.params.status !== item.status) {
        this.params.status = item.status;
      } else {
        this.params.status = "";
      }
    },
    analysisUrl() {
      let url = window.location.href;
      let getqyinfo = url.split("?")[1];
      let getqys = new URLSearchParams("?" + getqyinfo);
      let m = getqys.get("m");
      this.params.m = m;
    },
    getCommon() {
      this.commonData =
        JSON.parse(localStorage.getItem("common_set_before")) || {};
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text94;
    },

    inputChange() {
      this.params.page = 1;
      this.cloudData = [];
      this.loading = true;
      this.getCloudList();
    },
    centerSelectChange(index) {
      const filterItem = this.center[index] || {};
      this.params.country_id = filterItem.country_id;
      this.params.data_center_id = index;
      this.params.city = filterItem.city;
      this.params.area = filterItem.area;
      this.params.page = 1;
      this.inputChange();
    },
    statusSelectChange() {
      this.params.page = 1;
      this.inputChange();
    },
    // 获取产品列表
    getCloudList() {
      cloudList(this.params).then((res) => {
        if (res.data.status === 200) {
          this.cloudData = this.cloudData.concat(
            res.data.data.list.map((item) => {
              item.allIp = (item.dedicate_ip + "," + item.assign_ip).split(",");
              return item;
            })
          );
          this.self_defined_field = res.data.data.self_defined_field;
          this.params.total = res.data.data.count;
          this.params.page++;
          this.loading = false;
          if (this.cloudData.length >= res.data.data.count) {
            this.finished = true;
          } else {
            this.finished = false;
          }

          const area = res.data.data.data_center;
          area &&
            area.map((item, index) => {
              item.value = index + 1;
              item.text = item.country_name + "-" + item.city + "-" + item.area;
              return item;
            });
          this.center = [{ value: "", text: lang.common_cloud_label1 }].concat(
            area
          );
        }
      });
    },
    // 跳转产品详情
    toDetail(row) {
      location.href = `productdetail.htm?id=${row.id}`;
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#product-template");

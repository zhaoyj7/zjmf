(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("log-notice-sms")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
        comPagination
      },
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          hasExport: false,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 100,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "content",
              title: lang.content,
              ellipsis: true,
              minWidth: 300,
              className: "notice-width",
            },
            {
              colKey: "create_time",
              title: lang.time,
              width: 200,
            },
            {
              colKey: "user_name",
              title: lang.receiver,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "phone",
              title: lang.phone,
              width: 200,
              ellipsis: true,
            },
          ],
          eamilColumns: [
            {
              colKey: "id",
              title: "ID",
              width: 100,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "subject",
              title: lang.title,
              ellipsis: true,
            },
            {
              colKey: "to",
              title: lang.email,
              ellipsis: true,
            },
            {
              colKey: "create_time",
              title: lang.time,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "user_name",
              title: lang.receiver,
              width: 150,
              ellipsis: true,
            },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            start_time: "",
            end_time: "",
          },
          id: "",
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          range: [],
          loading: false,
          title: "",
          delId: "",
          maxHeight: "",
          exportVisible: false,
          exportLoading: false,
          curTab: "sms",
          messageVisable: false,
          messagePop: "",
          emailTitle: "",
        };
      },
      computed: {
        curColumns () {
          return this.curTab === "sms" ? this.columns : this.eamilColumns;
        }
      },
      methods: {
        // 显示邮件详情
        showMessage (row) {
          this.messageVisable = true;
          this.emailTitle = row.subject;
          this.messagePop = row.message;
        },
        changeTab () {
          this.params.page = 1;
          this.params.keywords = "";
          this.getClientList();
        },
        async getPlugin () {
          try {
            const res = await getAddon();
            this.addonArr = res.data.data.list.map((item) => item.name);
            this.hasExport = this.addonArr.includes("ExportExcel");
          } catch (error) { }
        },
        openExportDia () {
          this.range = [];
          this.params.start_time = "";
          this.params.end_time = "";
          this.exportVisible = true;
        },
        handelDownload () {
          if (this.range.length === 0) {
            this.$message.error(lang.data_export_tip);
            return;
          }
          const params = JSON.parse(JSON.stringify(this.params));
          if (this.range.length > 0) {
            params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            params.end_time =
              new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
          } else {
            params.start_time = "";
            params.end_time = "";
          }
          if ((params.end_time - params.start_time) / (3600 * 24) > 31) {
            this.$message.error(lang.export_range_tips);
            return;
          }
          this.exportLoading = true;
          let apiFun = '';
          if (this.curTab === "sms") {
            apiFun = apiExportSmslog;
          } else if (this.curTab === "email") {
            apiFun = apiExportEmaillog;
          }
          apiFun(params)
            .then((res) => {
              exportExcelFun(res).finally(() => {
                this.exportLoading = false;
                this.exportVisible = false;
              });
            })
            .catch((err) => {
              this.exportLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        // jump (val) {
        //   if (val === 'email') {
        //     location.href = "log_notice_email.htm";
        //   }
        // },
        changePage (e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        async getClientList () {
          try {
            this.loading = true;
            let apiFun = '';
            if (this.curTab === "sms") {
              apiFun = getSmsLog;
            } else if (this.curTab === "email") {
              apiFun = getEmailLog;
            }
            const res = await apiFun(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
          } catch (error) {
            this.$message.error(error.data.msg);
            this.loading = false;
          }
        },
        // 排序
        sortChange (val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },
        clearKey () {
          this.params.keywords = "";
          this.search();
        },
        search () {
          this.params.page = 1;
          this.getClientList();
        },
      },
      created () {
        this.getClientList();
        this.getPlugin();
        document.title =
          lang.sms_notice + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

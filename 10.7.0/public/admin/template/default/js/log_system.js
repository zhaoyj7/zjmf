(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("log-system")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
        comPagination,
      },
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          exportVisible: false,
          exportLoading: false,
          hasExport: false,
          range: [],
          searchRange: [],
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 100,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "description",
              title: lang.description,
              minWidth: 300,
              ellipsis: true,
              className: "log-description-width",
            },
            {
              colKey: "create_time",
              title: lang.time,
              width: 200,
            },
            {
              colKey: "ip",
              title: "IP" + lang.address,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "user_name",
              title: lang.operator,
              width: 150,
            },
          ],
          params: {
            keywords: "",
            admin_name: "",
            begin_time: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            start_time: "",
            end_time: "",
            type: "",
          },
          id: "",
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          title: "",
          delId: "",
          maxHeight: "",
          logType: [
            { value: 'admin', name: lang.admin_log },
            { value: 'client', name: lang.client_log },
            { value: 'system', name: lang.system_log }, 
            { value: 'api', name: lang.api_log },
            { value: 'cron', name: lang.cron_log },
            { value: 'task', name: lang.task_log },
            { value: '', name: lang.all_log },
          ],
          cancelTokenSource: null,
          addonArr: [],
          isNextPageDisabled: false
        };
      },
      computed: {
        calStr () {
          return (str) => {
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
                .replace(/&amp;#039;/g, "'");
            return temp;
          };
        },
      },
      methods: {
        handleChange (type) {
          if (type === 1) {
            this.params.page += 1;
          } else {
            if (this.params.page > 1) {
              this.params.page -= 1;
            }
          }
          this.getClientList();
        },
        changeTab () {
          this.params.page = 1;
          this.params.keywords = "";
          this.params.admin_name = "";
          this.params.start_time = "";
          this.params.end_time = "";
          if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
          }
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
          apiExportSystemlog(params)
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
        changePage (e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        async getClientList () {
          try {
            // 处理时间范围
            if (this.searchRange.length > 0) {
              this.params.start_time = this.params.begin_time =
                new Date(this.searchRange[0].replace(/-/g, "/")).getTime() / 1000 || "";
              this.params.end_time =
                new Date(this.searchRange[1].replace(/-/g, "/")).getTime() / 1000 || "";
            } else {
              this.params.start_time = "";
              this.params.begin_time = "";
              this.params.end_time = "";
            }

            this.loading = true;

            // 处理取消请求，搜索的时候接口响应慢，切换的时候需要取消掉
            this.abortController = new AbortController();
            const res = await getSystemLog(this.params, this.abortController.signal);

            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
            this.abortController = null;
          } catch (error) {
            if (error.name === "CanceledError") {
              console.log("请求已取消");
            } else {
              this.$message.error(error?.response?.data?.msg || "请求失败");
            }
            this.loading = false;
            this.abortController = null;
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
        this.params.type = this.logType[0].value;
        this.getClientList();
        this.getPlugin();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

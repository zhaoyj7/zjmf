(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("host")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = moment;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        comPagination,
        comTreeSelect,
        comViewFiled,
        safeConfirm,
        loginByUser,
      },
      data() {
        return {
          data: [],
          website_url: "",
          addonArr: [],
          hasExport: false,
          tableLayout: false,
          bordered: true,
          exportVisible: false,
          visible: false,
          delVisible: false,
          exportLoading: false,
          hover: true,
          page_total_renew_amount: 0,
          total_renew_amount: 0,
          presets: {
            [lang.due_today]: [
              new Date(new Date().toLocaleDateString()).getTime(),
              new Date(new Date().toLocaleDateString()).getTime() +
                24 * 60 * 60 * 1000 -
                1,
            ],
            [lang.due_three]: [
              new Date(+new Date() - 86400000 * 2),
              new Date(),
            ],
            [lang.due_seven]: [
              new Date(+new Date() - 86400000 * 6),
              new Date(),
            ],
            [lang.due_month]: [
              new Date(+new Date() - 86400000 * 30),
              new Date(),
            ],
          },
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              className: "demo-multiple-select-cell",
              width: 30,
            },
            {
              colKey: "id",
              title: "ID",
              width: 120,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "product_name",
              title: lang.product_name,
              ellipsis: true,
              className: "product-name",
              width: 300,
            },
            {
              colKey: "client_id",
              title: lang.user + "(" + lang.contact + ")",
              width: 250,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.host_name,
              width: 280,
              ellipsis: true,
            },
            {
              colKey: "renew_amount",
              title: `${lang.money_cycle}`,
              width: 166,
              ellipsis: true,
            },
            // {
            //   colKey: 'active_time',
            //   title: lang.open_time,
            //   width: 170,
            //   sortType: 'all',
            //   sorter: true
            // },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: 170,
              sortType: "all",
              sorter: true,
            },
            // {
            //   colKey: 'status',
            //   title: lang.status,
            //   width: 100,
            //   ellipsis: true
            // },
            // {
            //   colKey: 'op',
            //   title: lang.operation,
            //   width: 100,
            // },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            billing_cycle: "",
            status: "",
            start_time: "",
            end_time: "",
            tab: "using",
            username: "",
            server_id: "",
            product_id: [],
            view_id: "",
            due_time: "",
            action: "",
            module: "",
          },

          moduleList: [],
          id: "",
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          title: "",
          delId: "",
          /* 2023-04-11 */
          range: [],
          productStatus: [
            {value: "Unpaid", label: lang.Unpaid},
            {value: "Pending", label: lang.Pending},
            {value: "Active", label: lang.opened_notice},
            {value: "Suspended", label: lang.Suspended},
            {value: "Deleted", label: lang.Deleted},
            {value: "Failed", label: lang.Failed},
            {value: "Cancelled", label: lang.Cancelled},
          ],
          allIp: [],
          ipLoading: false,
          expiring_count: 0,
          searchType: "",
          typeOption: [
            {value: "", label: lang.auth_all},
            {value: "host_id", label: `${lang.tailorism}ID`},
            {value: "username", label: lang.promo_user},
            {value: "product_id", label: lang.product_name},
            {value: "name", label: lang.products_token},
            {value: "first_payment_amount", label: lang.buy_amount},
            {value: "ip", label: "IP"},
            {value: "sale", label: lang.sale},
          ],
          isAdvance: false,
          serverList: [],
          billingCycle: {
            free: lang.free,
            onetime: lang.onetime,
            recurring_prepayment: lang.recurring_prepayment,
            recurring_postpaid: lang.recurring_postpaid,
          },
          viewFiledNum: 0,
          password_field: [],
          defaultId: "",
          admin_view_list: [],
          data_range_switch: 0,
          dueTimeArr: [
            {value: "today", label: lang.due_today},
            {value: "three", label: lang.due_three},
            {value: "seven", label: lang.due_seven},
            {value: "month", label: lang.due_month},
          ],
          customField: [],
          hasSale: false,
          allSales: [],
          curSaleId: "",
          checkId: [],
          submitLoading: false,
          module_delete: false,
          admin_operate_password: "",
          // 手动处理
          manualColumns: [
            {
              colKey: "row-select",
              type: "multiple",
              className: "demo-multiple-select-cell",
              checkProps: ({row, rowIndex}) => ({
                disabled: row.retry === 0,
              }),
              width: 30,
            },
            {
              colKey: "id",
              title: "ID",
              width: 120,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "product_name_status",
              title: `${lang.product_name}(${lang.client_care_label29})`,
              ellipsis: true,
              className: "product-name",
              minWidth: 200,
            },
            {
              colKey: "failed_action",
              title: lang.failed_action,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "failed_action_trigger_time",
              title: lang.failed_trigger_time,
              width: 170,
              ellipsis: true,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "failed_action_reason",
              title: lang.failed_reason,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "username_company",
              title: `${lang.user}(${lang.company})`,
              width: 250,
              ellipsis: true,
            },
            {
              colKey: "ip",
              title: "IP",
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "renew_amount_cycle",
              title: `${lang.renew_amount}/${lang.cycle}`,
              width: 166,
              ellipsis: true,
            },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: 170,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
              ellipsis: true,
              fixed: "right",
            },
          ],
          failAction: [
            {value: "create", label: lang.failed_create},
            {value: "suspend", label: lang.failed_suspend},
            {value: "terminate", label: lang.failed_terminate},
            {value: "renew", label: lang.failed_renew},
          ],
          failed_action_count: 0,
          markDialog: false,
          curMarkObj: {},
          /* 批量同步 */
          pullLoading: false,
          pullVisible: false,
          batchPullForm: {
            product_id: [],
            host_status: [],
          },
          rules: {
            product_id: [
              {
                required: true,
                message: lang.data_export_tip12,
                type: "error",
              },
            ],
            host_status: [
              {
                required: true,
                message: lang.data_export_tip13,
                type: "error",
              },
            ],
          },
          batchRetryLoading: false,
          globalFiled: [],
        };
      },
      computed: {
        calcStatusName() {
          return (status) => {
            return this.productStatus.filter((item) => item.value === status)[0]
              ?.label;
          };
        },
        calcActionName() {
          return (action) => {
            return this.failAction.filter((item) => item.value === action)[0]
              ?.label;
          };
        },
        tabList() {
          return [
            {value: "using", label: lang.host_using},
            {
              value: "expiring",
              label: `${lang.host_expiring}(${this.expiring_count})`,
            },
            {value: "overdue", label: lang.host_overdue},
            {value: "deleted", label: lang.host_deleted},
            {value: "", label: lang.auth_all},
            {
              value: "failed",
              label: `${lang.manual_handle}(${this.failed_action_count})`,
            },
          ];
        },
        calcTypeSelect() {
          if (this.hasSale) {
            return this.typeOption;
          } else {
            return this.typeOption.filter((item) => item.value !== "sale");
          }
        },
        calcDevloper() {
          return (type) => {
            switch (type) {
              case 1:
                return lang.author;
              case 2:
                return lang.client_service;
              case 3:
                return lang.author_service;
            }
          };
        },
        calcCycle() {
          return (cycle) => {
            return isNaN(Number(cycle)) ? cycle : `${cycle}${lang.year}`;
          };
        },
        calcStatus() {
          return (arr) => {
            if (this.params.tab === "using") {
              return [
                {value: "Pending", label: lang.Pending},
                {value: "Active", label: lang.opened_notice},
              ];
            } else if (this.params.tab === "overdue") {
              return [
                {value: "Active", label: lang.opened_notice},
                {value: "Suspended", label: lang.Suspended},
                {value: "Failed", label: lang.Failed},
              ];
            } else {
              return arr;
            }
          };
        },
        calcList() {
          if (this.customField.length > 0) {
            return this.data.map((item) => {
              this.customField.forEach((el) => {
                if (item.hasOwnProperty(el)) {
                  item[el] = item[el] || "--";
                }
              });
              return item;
            });
          } else {
            return this.data;
          }
        },
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      methods: {
        /* 批量同步 */
        handlePull() {
          this.batchPullForm.product_id = [];
          this.batchPullForm.host_status = [];
          this.pullVisible = true;
          this.$refs.pullForm && this.$refs.pullForm.clearValidate();
        },
        choosePullPro(val) {
          this.batchPullForm.product_id = val;
        },
        async submitPull({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await batchSyncHost(this.batchPullForm);
              this.$message.success(res.data.msg);
              this.submitLoading = false;
              this.pullVisible = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
          }
        },
        /* 批量同步 end */
        async getModuleList() {
          try {
            const res = await getModuleData();
            this.moduleList = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        handleMark(row) {
          this.curMarkObj = row;
          this.markDialog = true;
        },
        // 批量重试
        handleBatchRetry() {
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select);
          }
          this.batchRetryLoading = true;
          apiRetry({
            id: this.checkId,
          })
            .then((res) => {
              this.$message.success(res.data.msg);
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            })
            .finally(() => {
              this.checkId = [];
              this.getClientList();
              this.batchRetryLoading = false;
            });
        },
        handleRetry(row) {
          row.retryIng = true;
          apiRetry({
            id: [row.id],
          })
            .then((res) => {
              row.retryIng = false;
              this.$message.success(res.data.msg);
              this.getClientList();
            })
            .catch((error) => {
              row.retryIng = false;
              this.$message.error(error.data.msg);
            });
        },
        async submitMark() {
          try {
            this.submitLoading = true;
            const res = await markProcessed({
              id: this.curMarkObj.id,
            });
            this.markDialog = false;
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.submitLoading = false;
          }
        },
        // 获取后台配置的路径
        async getSystemOption() {
          try {
            const res = await getSystemOpt();
            this.website_url =
              res.data.data.clientarea_url || res.data.data.website_url;
          } catch (error) {}
        },
        /* 批量删除 */
        rehandleSelectChange(value, {selectedRowData}) {
          this.checkId = value;
          this.selectedRowKeys = selectedRowData;
        },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        async batchDel() {
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select);
          }
          this.module_delete = false;
          this.delVisible = true;
        },
        // 批量删除
        async onConfirm(e, remember_operate_password) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            const res = await deleteHost({
              id: this.checkId,
              admin_operate_password,
              module_delete: this.module_delete ? 1 : 0,
              admin_operate_methods: "onConfirm",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getClientList();
            this.submitLoading = false;
            this.checkId = [];
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return;
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        /* 批量删除 end  */
        async getAllSaleList() {
          try {
            const res = await getAllSales();
            this.allSales = res.data.data.list;
          } catch (error) {
            this.$message.error(res.data.msg);
          }
        },
        handelDownload() {
          this.exportLoading = true;
          if (!this.isAdvance) {
            this.params.billing_cycle = "";
            this.params.username = "";
            this.params.status = "";
            this.params.server_id = "";
            this.range = [];
          }
          if (this.range.length > 0) {
            this.params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_time =
              new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
          } else {
            this.params.start_time = "";
            this.params.end_time = "";
          }

          apiExportHost(this.params)
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
        async getPlugin() {
          try {
            const res = await getAddon();
            this.addonArr = res.data.data.list.map((item) => item.name);
            this.hasExport = this.addonArr.includes("ExportExcel");
            this.addonArr.includes("IdcsmartSale") && this.getAllSaleList();
          } catch (error) {}
        },
        /* 视图 */
        // 本地存储列宽
        resizeChange({columnsWidth}) {
          const temp = this.columns.map((item) => {
            item.width = columnsWidth[item.colKey];
            return item;
          });
          this.columns = temp;
          localStorage.setItem("columnsWidth", JSON.stringify(temp));
        },
        chooseView(id) {
          this.$refs.customFiled.chooseView(id);
        },
        handleEditView() {
          this.$refs.customFiled.editHandler();
        },
        changeField({
          view_id,
          backColumns,
          customField,
          isInit,
          len,
          password_field,
          defaultId,
          admin_view_list,
          data_range_switch,
          select_field,
          globalFiled,
        }) {
          const temp = [
            "email",
            "address",
            "client_level",
            "language",
            "notes",
            "country",
            "dedicate_ip",
          ];
          customField.push(...temp);
          this.customField = customField; // 统一处理无数据的显示--
          // 判断本地是否有列宽设置
          const columnsWidth = JSON.parse(localStorage.getItem("columnsWidth"));
          if (columnsWidth) {
            backColumns = backColumns.map((item) => {
              item.width = (columnsWidth || []).filter(
                (el) => el.colKey === item.colKey
              )[0]?.width;
              return item;
            });
          }
          if (backColumns[0]?.colKey !== "row-select") {
            backColumns.unshift({
              colKey: "row-select",
              type: "multiple",
              className: "demo-multiple-select-cell",
              width: 30,
            });
          }
          this.globalFiled = globalFiled;
          this.columns = backColumns;
          this.password_field = password_field;
          this.viewFiledNum = len;
          this.params.view_id = view_id;
          this.defaultId = defaultId;
          this.admin_view_list = admin_view_list;
          this.data_range_switch = data_range_switch;
          this.hasSale = select_field.includes("sale");
          if (isInit) {
            this.getClientList();
          }
        },
        /* 视图 end */
        changeType() {
          this.params.keywords = "";
          this.curSaleId = "";
        },
        choosePro(id) {
          this.params.product_id = id;
        },
        async getServerLisrt() {
          try {
            const res = await getInterface({
              page: 1,
              limit: 999,
            });
            this.serverList = res.data.data.list.sort((a, b) => {
              return a.id - b.id;
            });
          } catch (error) {}
        },
        changeAdvance() {
          this.isAdvance = !this.isAdvance;
        },
        changeHostTab(e) {
          this.params.tab = e;
          this.params.page = 1;
          this.params.keywords = "";
          this.params.status = "";
          this.checkId = [];
          this.selectedRowKeys = [];
          if (e === "failed") {
            this.isAdvance = false;
            this.params.orderby = "failed_action_trigger_time";
          }
          this.getClientList();
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
          this.$message.success(lang.box_text17);
        },
        getQuery(name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        goHostDetail(row) {
          sessionStorage.currentHostUrl = window.location.href;
          location.href = `host_detail.htm?client_id=${row.client_id}&id=${row.id}`;
        },
        // 搜索
        clearKey(type) {
          this.params[type] = "";
          this.search();
        },
        search() {
          this.params.page = 1;
          if (!this.isAdvance) {
            this.params.billing_cycle = "";
            this.params.username = "";
            this.params.status = "";
            this.params.server_id = "";
            this.range = [];
          }
          if (this.range.length > 0) {
            this.params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_time =
              new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
          } else {
            this.params.start_time = "";
            this.params.end_time = "";
          }

          this.getClientList();
        },
        // 分页
        changePage(e) {
          //   this.params.keywords = ''
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        async getClientList() {
          try {
            this.loading = true;
            let params = JSON.parse(JSON.stringify(this.params));
            if (this.searchType && this.searchType !== "product_id") {
              params[this.searchType] = params.keywords;
              params.keywords = "";
            }
            // 销售
            if (this.curSaleId) {
              params["custom_field[IdcsmartSale]"] = this.curSaleId;
            }
            let funName = getClientPro;
            if (this.params.tab === "failed") {
              funName = getFailAction;
              const {page, limit, action, keywords, orderby, sort} = params;
              params = {
                page,
                limit,
                action,
                keywords,
                orderby,
                sort,
              };
            }
            const res = await funName("", params);
            this.data = res.data.data.list.map((item) => {
              item.retryIng = false;
              item.allIp = (item.dedicate_ip + "," + item.assign_ip).split(",");
              return item;
            });
            this.total = res.data.data.count;
            this.page_total_renew_amount =
              res.data.data.page_total_renew_amount;
            this.total_renew_amount = res.data.data.total_renew_amount;
            this.expiring_count = res.data.data.expiring_count;
            this.failed_action_count = res.data.data.failed_action_count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby =
              this.params.tab === "failed"
                ? "failed_action_trigger_time"
                : "id";
            this.params.sort = "desc";
          } else {
            let curField = "";
            switch (val.sortBy) {
              case "renew_amount_cycle":
                curField = "renew_amount";
                break;
              default:
                curField = val.sortBy;
            }
            this.params.orderby = curField;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },

        // 秒级时间戳转xxxx-xx-xx
        initDate(time) {
          const timestamp = time * 1000; // 时间戳
          const date = new Date(timestamp);
          const year = date.getFullYear();
          const month = date.getMonth() + 1; // 月份从 0 开始，所以要加 1
          const day = date.getDate();
          const formattedDate = `${year}-${month
            .toString()
            .padStart(2, "0")}-${day.toString().padStart(2, "0")}`;
          return formattedDate;
        },
      },
      created() {
        sessionStorage.removeItem("currentHostUrl");
        this.getServerLisrt();
        this.getSystemOption();
        this.getModuleList();
        /* 全局搜索 */
        let searchType = this.getQuery("type") || "";
        const keywords = this.getQuery("keywords") || "";

        // 首页机柜跳转
        const module = this.getQuery("module") || "";
        if (module) {
          searchType = "module";
        }
        if (searchType === "status") {
          this.params.status = keywords;
        } else if (searchType === "username") {
          this.params.username = keywords;
        } else if (searchType === "product_id") {
          this.params.product_id = keywords * 1 ? [keywords * 1] : [];
        } else if (searchType === "server_id") {
          this.params.server_id = keywords * 1 || "";
        } else if (searchType === "billing_cycle") {
          this.params.billing_cycle = keywords;
        } else if (searchType === "due_time") {
          this.range.push(keywords, keywords);
          this.params.start_time =
            new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
          this.params.end_time =
            new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
        } else if (searchType === "module") {
          this.params.module = module;
        } else {
          this.params.keywords = keywords;
        }
        if (
          searchType === "due_time" ||
          searchType === "status" ||
          searchType === "billing_cycle" ||
          searchType === "username" ||
          searchType === "server_id" ||
          searchType === "module"
        ) {
          this.searchType = "";
          this.isAdvance = true;
        } else {
          this.searchType = searchType;
        }
        /* 全局搜索 end */
        this.params.tab = this.getQuery("tab") || "using";
        if (this.params.tab === "failed") {
          this.params.orderby = "failed_action_trigger_time";
        }
        // 产品收入
        if (this.getQuery("from") === "product_income") {
          this.isAdvance = true;
          this.params.tab = "";
          this.params.product_id =
            this.getQuery("product_id") * 1
              ? [this.getQuery("product_id") * 1]
              : [];
          this.range.push(
            this.getQuery("date").split("-")[0],
            this.getQuery("date").split("-")[1]
          );
          this.params.start_time =
            new Date(this.getQuery("date").split("-")[0]).getTime() / 1000 ||
            "";
          this.params.end_time =
            new Date(this.getQuery("date").split("-")[1]).getTime() / 1000 ||
            "";
        }
        if (searchType) {
          this.params.tab = "";
        }
        this.getPlugin();
        this.getClientList();
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

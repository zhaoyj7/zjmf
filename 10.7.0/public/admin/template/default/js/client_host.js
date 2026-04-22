(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("host")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}`;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        comPagination,
        comChooseUser,
        comTreeSelect,
        safeConfirm,
      },
      data() {
        return {
          baseUrl: str,
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          module_delete: false,
          hover: true,
          page_total_renew_amount: 0,
          total_renew_amount: 0,
          hasNewTicket: false,
          admin_operate_password: "",
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              className: "demo-multiple-select-cell",
              checkProps: ({row}) => ({
                disabled: row.status !== "Active" && row.status !== "Suspended",
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
              colKey: "product_name",
              title: lang.product_name,
              width: 220,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.host_name,
              width: 220,
              ellipsis: true,
            },
            {
              colKey: "ip_num",
              title: "IP",
              width: 220,
              ellipsis: true,
            },
            {
              colKey: "renew_amount",
              title: `${lang.money_cycle}`,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "active_time",
              title: lang.open_time,
              width: 180,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: 180,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "status",
              title: lang.status,
              width: 100,
              sortType: "all",
              sorter: true,
            },
            // {
            //   colKey: 'op',
            //   title: lang.operation,
            //   width: 100
            // }
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
            tab: "",
            server_id: "",
            product_id: [],
            view_id: "",
            due_time: "",
          },
          productStatus: [
            {value: "Unpaid", label: lang.Unpaid},
            {value: "Pending", label: lang.Pending},
            {value: "Active", label: lang.opened_notice},
            {value: "Suspended", label: lang.Suspended},
            {value: "Deleted", label: lang.Deleted},
            {value: "Failed", label: lang.Failed},
            {value: "Cancelled", label: lang.Cancelled},
          ],
          typeOption: [
            {value: "", label: lang.auth_all},
            {value: "host_id", label: `${lang.tailorism}ID`},
            {value: "product_id", label: lang.product_name},
            {value: "name", label: lang.products_token},
            {value: "first_payment_amount", label: lang.buy_amount},
            {value: "ip", label: "IP"},
            {value: "status", label: lang.client_care_label29},
          ],
          dueTimeArr: [
            {value: "today", label: lang.due_today},
            {value: "three", label: lang.due_three},
            {value: "seven", label: lang.due_seven},
            {value: "month", label: lang.due_month},
          ],
          isAdvance: false,
          searchType: "",
          id: "",
          clientList: [], // 用户列表
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          title: "",
          delId: "",
          maxHeight: "",
          clinetParams: {
            page: 1,
            limit: 10,
            orderby: "id",
            sort: "desc",
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          /* 批量续费 */
          renewVisible: false,
          hasRecommend: false,
          checkId: [],
          selectedRowKeys: [],
          renewColumns: [
            {
              colKey: "id",
              title: "ID",
              width: 60,
              sortType: "all",
            },
            {
              colKey: "product_name",
              title: lang.products_name,
              width: 300,
              ellipsis: true,
            },
            {
              colKey: "billing_cycles",
              title: lang.cycle,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "renew_amount",
              title: lang.money,
              width: 100,
              ellipsis: true,
            },
          ],
          renewList: [],
          renewLoading: false,
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          pay: false,
          submitLoading: false,
          hasPlugin: false,
          hasTicket: false, // 是否安装工单
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          clientDetail: {},
          searchLoading: false,
        };
      },
      created() {
        this.id = location.href.split("?")[1].split("=")[1] * 1;
        if (sessionStorage.hostListParams) {
          this.params = Object.assign(
            this.params,
            JSON.parse(sessionStorage.hostListParams)
          );
        }
        sessionStorage.removeItem("hostListParams");
        sessionStorage.removeItem("currentHostUrl");
        this.getClientList();
        // this.getClintList();
        this.getUserDetail();
      },
      mounted() {
        this.getPlugin();
        document.title =
          lang.user_list +
          "-" +
          lang.product_info +
          "-" +
          localStorage.getItem("back_website_name");
      },
      computed: {
        calcCycle() {
          return (cycle) => {
            return isNaN(Number(cycle)) ? cycle : `${cycle}${lang.year}`;
          };
        },
        renewTotal() {
          return this.renewList.reduce((all, cur) => {
            all += Number(cur.renew_amount);
            return all;
          }, 0);
        },
        calcShow() {
          return (data) => {
            return (
              `#${data.id}-` +
              (data.username
                ? data.username
                : data.phone
                ? data.phone
                : data.email) +
              (data.company ? `(${data.company})` : "")
            );
          };
        },
        isExist() {
          return !this.clientList.find(
            (item) => item.id === this.clientDetail.id
          );
        },
      },
      methods: {
        changeAdvance() {
          this.isAdvance = !this.isAdvance;
        },
        // 搜索
        clearKey(type) {
          this.params[type] = "";
          this.search();
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

        // 远程搜素
        remoteMethod(key) {
          this.clinetParams.keywords = key;
          this.getClintList();
        },
        filterMethod(search, option) {
          return option;
        },
        goHostDetail(row) {
          sessionStorage.currentHostUrl = window.location.href;
          sessionStorage.hostListParams = JSON.stringify(this.params);
          location.href = `host_detail.htm?client_id=${row.client_id}&id=${row.id}`;
        },
        // 获取用户详情
        async getUserDetail() {
          try {
            const res = await getClientDetail(this.id);
            this.clientDetail = res.data.data.client;
          } catch (error) {}
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, []);
            this.hasPlugin = temp.includes("IdcsmartRenew");
            this.hasTicket = temp.includes("IdcsmartTicket");
            this.hasNewTicket = temp.includes("TicketPremium");
            this.hasRecommend = temp.includes("IdcsmartRecommend");
          } catch (error) {}
        },
        /* 批量续费 */
        async batchRenew() {
          this.renewForm = [];
          this.renewList = [];
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select);
          }
          this.renewVisible = true;
          try {
            this.renewLoading = true;
            const params = {
              client_id: this.id,
              ids: this.checkId,
            };
            const res = await getRenewBatch(params);
            this.renewList = res.data.data.list.map((item) => {
              item.curCycle = item.billing_cycles[0]?.billing_cycle;
              item.renew_amount =
                item.billing_cycles.length > 0
                  ? item.billing_cycles[0].price
                  : 0.0;
              return item;
            });
            this.renewLoading = false;
          } catch (error) {
            this.renewLoading = false;
          }
        },
        // 批量删除
        async batchDel() {
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select);
          }
          this.module_delete = false;
          this.delVisible = true;
        },
        rehandleSelectChange(value, {selectedRowData}) {
          this.checkId = value;
          this.selectedRowKeys = selectedRowData;
        },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        // 提交批量续费
        async submitRenew(e, remember_operate_password) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("submitRenew");
          //   return;
          // }

          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            const params = {
              ids: [],
              client_id: this.id,
              billing_cycles: {},
              // amount_custom: {},
              pay: this.pay,
              admin_operate_password: admin_operate_password,
              admin_operate_methods: "submitRenew",
              remember_operate_password,
            };
            let temp = JSON.parse(JSON.stringify(this.renewList));
            temp = temp.filter((item) => item.billing_cycles.length > 0);
            temp.forEach((item) => {
              params.ids.push(item.id);
              params.billing_cycles[item.id] = item.curCycle;
              // params.amount_custom[item.id] = item.renew_amount;
            });
            this.submitLoading = true;
            const res = await postRenewBatch(params);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.renewVisible = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            if (admin_operate_password) {
              this.$message.error(error.data.msg);
            }
          }
        },
        changeCycle(row) {
          row.renew_amount = row.billing_cycles.filter(
            (item) => item.billing_cycle === row.curCycle
          )[0].price;
        },
        changeUser(id) {
          this.id = id;
          location.href = `client_host.htm?id=${this.id}`;
        },
        cancelRenew() {
          this.selectedRowKeys = [];
          this.checkId = [];
        },
        /* -----批量续费end-------- */
        async getClintList() {
          try {
            this.searchLoading = true;
            const res = await getClientList(this.clinetParams);
            this.clientList = res.data.data.list;
            this.clientTotal = res.data.data.count;
            this.searchLoading = false;
          } catch (error) {
            this.searchLoading = false;
            console.log(error.data.msg);
          }
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        changeType() {
          this.params.keywords = "";
          this.curSaleId = "";
        },
        choosePro(id) {
          this.params.product_id = id;
        },
        search() {
          this.params.page = 1;
          this.getClientList();
        },
        async getClientList() {
          try {
            this.loading = true;
            const params = JSON.parse(JSON.stringify(this.params));
            if (
              this.searchType &&
              this.searchType !== "product_id" &&
              this.searchType !== "status"
            ) {
              params[this.searchType] = params.keywords;
              params.keywords = "";
            }
            const res = await getClientPro(this.id, params);
            this.data = res.data.data.list.map((item) => {
              item.allIp = (item.dedicate_ip + "," + item.assign_ip).split(",");
              return item;
            });
            this.total = res.data.data.count;
            this.page_total_renew_amount =
              res.data.data.page_total_renew_amount;
            this.total_renew_amount = res.data.data.total_renew_amount;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 批量删除
        async onConfirm(e, remember_operate_password) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("onConfirm");
          //   return;
          // }
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
            if (admin_operate_password) {
              this.$message.error(error.data.msg);
            }
          }
        },
        deltePro(row) {
          this.delVisible = true;
          this.delId = row.id;
        },
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

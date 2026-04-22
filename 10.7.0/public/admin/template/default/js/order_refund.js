(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order-details")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;

    new Vue({
      components: {
        comConfig,
        comPagination
      },
      data() {
        return {
          id: "",
          data: [],
          baseUrl: str,
          rootRul: url,
          tableLayout: false,
          hasCostPlugin: false,
          bordered: true,
          hover: true,
          visible: false,
          delVisible: false,
          confirmVisible: false,
          successVisible: false,
          rectVisible: false,
          total: 0,
          pagination: {
            current: 1,
            pageSize: 10,
            pageSizeOptions: [10, 20, 50, 100],
            total: 0,
            showJumper: true,
          },
          params: {
            id: "",
            page: 1,
            limit: getGlobalLimit(),
            host_status: "",
            keywords: "",
            refund_status: "",
          },
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          nowStatus: "",
          columns: [
            {
              width: 180,
              colKey: "create_time",
              title: lang.create_time,
              ellipsis: true,
            },
            {
              width: 180,
              colKey: "refund_time",
              title: lang.refund_time,
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "amount",
              title: lang.money,
              ellipsis: true,
            },
            {
              width: 150,
              colKey: "product_name",
              title: lang.order_detail_text2,
              ellipsis: true,
            },
            {
              width: 170,
              colKey: "host_name",
              title: lang.order_detail_text3,
            },
            {
              width: 220,
              colKey: "type",
              title: lang.refund_to,
              ellipsis: true,
            },
            {
              width: 100,
              colKey: "refund_type",
              title: lang.order_detail_text31,
              ellipsis: true,
            },
            {
              colKey: "admin_name",
              title: lang.operator,
              width: 150,
              ellipsis: true,
            },
            {
              width: 100,
              colKey: "host_status",
              title: lang.order_detail_text5,
              ellipsis: true,
            },
            {
              colKey: "refund_status",
              title: lang.order_detail_text4,
              width: 120,
            },
            {
              width: 150,
              colKey: "notes",
              title: lang.order_detail_text25,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 150,
              ellipsis: true,
              fixed: "right",
            },
          ],
          rules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.Refund + lang.money,
                type: "error",
              },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/,
                message: lang.verify4,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify4,
                type: "warning",
              },
            ],
            gateway: [
              {
                required: true,
                message: lang.select + lang.gateway,
                type: "error",
              },
            ],
            type: [
              {
                required: true,
                message: lang.select + lang.type,
                type: "error",
              },
            ],
            reason: [
              {
                required: true,
                message: lang.input + lang.refundRecject,
                type: "error",
              },
            ],
            transaction_number: [
              {
                required: true,
                message: lang.input + lang.flow_number,
                type: "error",
              },
              {
                pattern: /^[A-Za-z0-9]+$/,
                message: lang.verify9,
                type: "warning",
              },
            ],
            currency_prefix: "",
            client_id: [
              {
                required: true,
                message: lang.select + lang.user,
                type: "error",
              },
            ],
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          submitLoading: false,
          formData: {
            id: "",
            type: "", // credit transaction
            amount: "",
            gateway: "",
            transaction_number: "",
          },
          typeObj: {
            credit: lang.account_balance,
            transaction: lang.gateway,
            original: lang.original_pay,
          },
          refundTypeOptions: [
            {value: "credit_first", label: lang.order_detail_text21},
            {value: "gateway_first", label: lang.order_detail_text22},
            {value: "credit", label: lang.order_detail_text23},
            {value: "transaction", label: lang.order_detail_text24},
            {value: "original", label: lang.order_detail_text24},
          ],
          hostStatusOptions: {
            Unpaid: lang.Unpaid,
            Pending: lang.Pending,
            Active: lang.Active,
            Suspended: lang.Suspended,
            Deleted: lang.Deleted,
            Failed: lang.Failed,
          },
          payList: [],
          curId: "",
          refoudFormData: {
            reason: "",
          },
          hasRefundPlugin: false,
          orderDetail: {},
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
        };
      },
      computed: {
        calcHostStatusOptions() {
          return Object.keys(this.hostStatusOptions).map((key) => {
            return {value: key, label: this.hostStatusOptions[key]};
          });
        },
      },
      mounted() {
        this.getFlowList();
        this.getPayway();
        this.getOrderDetail();
      },
      methods: {
        async getAddonList() {
          try {
            const res = await getAddon();
            if (
              res.data.data.list.filter((item) => item.name === "CostPay")
                .length > 0
            ) {
              this.hasCostPlugin = true;
            }
            if (
              res.data.data.list.filter(
                (item) => item.name === "IdcsmartRefund"
              ).length > 0
            ) {
              this.hasRefundPlugin = true;
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        goRefundPlugin(row) {
          location.href = `${this.baseUrl}plugin/idcsmart_refund/index.htm?id=${row.id}`;
        },
        goBack() {
          const url = sessionStorage.currentOrderUrl || "";
          sessionStorage.removeItem("currentOrderUrl");
          if (url) {
            location.href = url;
          } else {
            window.history.back();
          }
        },
        goOrder() {
          sessionStorage.removeItem("currentOrderUrl");
          location.href = "order.htm";
        },
        async getOrderDetail() {
          try {
            const res = await getOrderDetails({id: this.id});
            this.orderDetail = res.data.data.order;
          } catch (error) {}
        },
        initiateRefund() {
          this.formData.type = "credit";
          this.formData.amount = this.orderDetail.refundable_amount;
          this.formData.gateway = "";
          this.formData.transaction_number = "";
          this.nowStatus = "";
          this.visible = true;
        },
        close() {
          this.visible = false;
        },
        delteFlow(row) {
          this.curId = row.id;
          this.delVisible = true;
        },
        handelConfirm(row) {
          this.curId = row.id;
          this.nowStatus = row.refund_status;
          this.confirmVisible = true;
        },
        handelSuccess(row) {
          this.curId = row.id;
          this.nowStatus = row.refund_status;
          this.formData.amount = row.amount * 1;
          this.formData.type = row.type;
          this.formData.gateway = row.gateway;
          this.formData.transaction_number = "";
          this.visible = true;
        },

        handelRecject(row) {
          this.curId = row.id;
          this.rectVisible = true;
          this.$nextTick(() => {
            this.$refs.refoudFormRef.reset();
          });
        },
        sureRect() {
          this.$refs.refoudFormRef.validate().then(async (pass) => {
            if (pass === true) {
              try {
                this.submitLoading = true;
                const res = await refundReject({
                  id: this.curId,
                  reason: this.refoudFormData.reason,
                });
                this.$message.success(res.data.msg);
                this.submitLoading = false;
                this.rectVisible = false;
                this.getFlowList();
                this.getOrderDetail();
              } catch (error) {
                this.submitLoading = false;
                this.$message.error(error.data.msg);
              }
            }
          });
        },
        async surePass() {
          try {
            this.submitLoading = true;
            const res = await refundPass(this.curId);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.confirmVisible = false;
            this.getFlowList();
            this.getOrderDetail();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        async sureSuccess() {
          try {
            this.submitLoading = true;
            const params = {
              id: this.curId,
              transaction_number: this.formData.transaction_number,
            };
            const res = await apiRefunded(params);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.visible = false;
            this.getFlowList();
            this.getOrderDetail();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async sureDelUser() {
          try {
            this.submitLoading = true;
            const res = await delOrderRecord({
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.delVisible = false;
            this.getFlowList();
            this.getOrderDetail();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取支付方式
        async getPayway() {
          try {
            const res = await getPayList();
            this.payList = res.data.data.list;
          } catch (error) {}
        },
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              if (this.nowStatus === "Refunding") {
                this.sureSuccess();
                return;
              }
              this.submitLoading = true;
              const res = await orderRefund(this.formData);
              this.$message.success(res.data.msg);
              this.submitLoading = false;
              this.visible = false;
              this.getFlowList();
              this.getOrderDetail();
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        clearKey() {
          this.params.keywords = "";
          this.search();
        },
        search() {
          this.params.page = 1;
          this.getFlowList();
        },
        async getFlowList() {
          try {
            this.loading = true;
            const res = await getOrderRefundRecord(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(res.data.msg);
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
          this.getFlowList();
        },
        onPageChange(pageInfo, newData) {
          if (!this.pagination.defaultCurrent) {
            this.pagination.current = pageInfo.current;
            this.pagination.pageSize = pageInfo.pageSize;
          }
        },
        // 分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getFlowList();
        },
      },
      created() {
        this.getAddonList();
        this.id =
          this.params.id =
          this.formData.id =
            location.href.split("?")[1].split("=")[1];
        this.currency_prefix =
          JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥";
        document.title =
          lang.refund_record + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

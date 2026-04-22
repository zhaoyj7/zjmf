(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order-details")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
        comPagination,
      },
      data () {
        return {
          id: "",
          baseUrl: str,
          rootRul: url,
          orderDetail: {},
          currency_prefix: "",
          type: "",
          self_defined_field: [],
          title: "",
          rules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.money,
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
          },
          formData: {
            id: "",
            amount: "",
            status: "Refunded",
          },
          submitLoading: false,
          payList: [],
          gateway: "",
          payVisible: false,
          signForm: {
            amount: 0,
            credit: 0,
            transaction_number: "",
          },
          use_credit: true,
          userInfo: {},
          columns: [
            {
              width: 200,
              colKey: "product_name",
              title: lang.order_detail_text2,
              ellipsis: true,
            },
            {
              width: 200,
              colKey: "host_name",
              title: lang.order_detail_text3,
              ellipsis: true,
            },
            {
              colKey: "description",
              title: lang.description,
              ellipsis: true,
            },
            {
              colKey: "amount",
              title: lang.money,
              ellipsis: true,
              width: 150,
              align: "right",
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 100,
              fixed: "right",
            },
          ],
          refundColumns: [
            {
              width: 90,
              colKey: "id",
              title: "ID",
            },
            {
              width: 180,
              colKey: "product_name",
              title: lang.order_detail_text2,
              ellipsis: true,
            },
            {
              width: 180,
              colKey: "host_name",
              title: lang.order_detail_text3,
            },
            {
              colKey: "description",
              title: lang.description,
              ellipsis: true,
            },
            {
              width: 100,
              colKey: "refund_status",
              title: lang.order_detail_text4,
              ellipsis: true,
            },
            {
              width: 100,
              colKey: "host_status",
              title: lang.order_detail_text5,
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "amount",
              title: lang.order_detail_text6,
              ellipsis: true,
            },
            {
              width: 220,
              colKey: "refund_total",
              title: lang.order_detail_text7,
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "refund_credit",
              title: lang.order_detail_text8,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 100,
              fixed: "right",
            },
          ],
          hostColumns: [
            {
              width: 90,
              colKey: "id",
              title: "ID",
            },
            {
              width: 140,
              colKey: "product_name",
              title: lang.order_detail_text2,
              ellipsis: true,
            },
            {
              width: 170,
              colKey: "name",
              title: lang.order_detail_text3,
            },
            {
              width: 100,
              colKey: "status",
              title: lang.order_detail_text27,
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "amount",
              title: lang.order_detail_text6,
              ellipsis: true,
            },
          ],
          tableLayout: false,
          bordered: true,
          visible: false,
          hover: true,
          loading: false,
          delVisible: false,
          curId: "",
          visibleLog: false,
          logColumns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
            },
            {
              colKey: "amount",
              title: lang.change_money,
              width: 120,
            },
            {
              colKey: "type",
              title: lang.type,
              width: 120,
            },
            {
              colKey: "create_time",
              title: lang.change_time,
              width: 180,
            },
            {
              colKey: "notes",
              title: lang.notes,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "admin_name",
              title: lang.operator,
              width: 100,
            },
          ],
          moneyPage: {
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            order_id: "",
          },
          logData: [],
          moneyLoading: false,
          logCunt: 0,
          pageSizeOptions: [10, 20, 50, 100],
          userCredit: "",
          payLoading: false,
          // 成本插件
          hasCostPlugin: false,
          refundKeywords: "",
          productRefundData: [],
          refunStatusOptions: {
            not_refund: lang.order_detail_text12,
            part_refund: lang.order_detail_text13,
            all_refund: lang.order_detail_text14,
            addon_refund: lang.order_detail_text15,
          },
          hostStatusOptions: {
            Unpaid: lang.Unpaid,
            Pending: lang.Pending,
            Active: lang.Active,
            Suspended: lang.Suspended,
            Deleted: lang.Deleted,
            Failed: lang.Failed,
          },
          refundVisible: false,
          refundFormData: {
            host_id: "",
            amount: "",
            type: "",
            notes: "",
            gateway: "",
          },
          refundInfo: {},
          refundRules: {
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
          },
          refundTypeOptions: [
            { value: "credit_first", label: lang.order_detail_text21 },
            { value: "gateway_first", label: lang.order_detail_text22 },
            { value: "credit", label: lang.order_detail_text23 },
            { value: "transaction", label: lang.order_detail_text24 },
          ],
          refundLoading: false,
          hasInvoicePlugin: false,
          invoiceObj: {
            invoice: {},
            support_invoice: 0,
          },
          invoiceLoading: false,
          expandedRowKeys: [],
          expandNum: 0,
          recharge_order_support_refund: 0
        };
      },
      computed: {
        isTransfer () {
          const type = ["WaitUpload", "WaitReview", "ReviewFail"];
          return type.includes(this.orderDetail.status);
        },
        calcRefundCredit () {
          if (this.refundFormData.type === "credit_first") {
            if (
              this.refundFormData.amount * 1 >=
              this.refundInfo.leave_credit * 1
            ) {
              return this.refundInfo.leave_credit * 1;
            } else {
              return this.refundFormData.amount * 1;
            }
          } else if (this.refundFormData.type === "gateway_first") {
            if (
              this.refundFormData.amount * 1 <=
              this.refundInfo.leave_gateway * 1
            ) {
              return 0;
            } else {
              return (
                this.refundFormData.amount * 1 -
                this.refundInfo.leave_gateway * 1
              );
            }
          } else if (this.refundFormData.type === "credit") {
            return this.refundFormData.amount * 1;
          } else {
            return 0;
          }
        },
        calcGetaway () {
          return this.refundFormData.amount * 1 - this.calcRefundCredit * 1;
        },
      },
      created () {
        this.id =
          this.formData.id =
          this.moneyPage.order_id =
          location.href.split("?")[1].split("=")[1];
        // 所有之前跳转订单详情都是 order_details
        if (
          !this.$checkPermission("auth_business_order_detail_order_detail_view")
        ) {
          const clientAuth = [
            {
              auth: "auth_business_order_detail_refund_record_view",
              url: "order_refund",
            },
            {
              auth: "auth_business_order_detail_transaction",
              url: "order_flow",
            },
            { auth: "auth_addon_cost_pay_show_tab", url: "order_cost" },
            {
              auth: "auth_order_detail_info_record_view",
              url: "order_notes",
            },
          ];
          const firstItem =
            clientAuth.find((item) => this.$checkPermission(item.auth)) || [];
          if (firstItem.auth === "auth_addon_cost_pay_show_tab") {
            return (location.href = `${this.baseUrl}/plugin/cost_pay/${firstItem.url}.htm?id=${this.id}`);
          } else {
            return (location.href = `${this.baseUrl}/${firstItem.url}.htm?id=${this.id}`);
          }
        }
        this.getAddonList();
        this.getinvoiceStaus();
        this.currency_prefix = JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥";
        this.recharge_order_support_refund = Number(JSON.parse(localStorage.getItem("common_set")).recharge_order_support_refund) || 0;
        document.title =
          lang.create_order_detail +
          "-" +
          localStorage.getItem("back_website_name");
      },
      mounted () {
        this.getOrderDetail();
        this.getPayway();
      },
      methods: {
        changeInvoice (val) {
          this.invoiceLoading = true;
          apiOrderInvoiceSwitch({
            order_id: this.id,
            invoice_enabled: val,
          })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getinvoiceStaus();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.invoiceLoading = false;
            });
        },
        async getinvoiceStaus () {
          try {
            const res = await getOrderInvoiceStatus({
              id: this.id,
            });
            this.invoiceObj = res.data.data;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        clearKey () {
          this.refundKeywords = "";
          this.getProductRefundList();
        },
        // 计算订单可退金额
        getRefundAmount () {
          apiOrderRefundAmount({
            id: this.id,
            host_id: this.refundFormData.host_id,
          }).then((res) => {
            this.refundInfo = res.data.data;
            this.refundFormData.type =
              res.data.data.gateway === "credit_limit" ? "gateway_first" : "";
          });
        },
        // 产品退款列表
        getProductRefundList () {
          apiHostRefundList({
            id: this.id,
            keywords: this.refundKeywords,
          })
            .then((res) => {
              const temp = res.data.data.list || [];
              temp.forEach(item => {
                item.children = [];
                item.key = `order_${item.id}`
              });
              // 匹配并合并数据
              this.orderDetail.items.forEach(orderItem => {
                orderItem.key = `child_${orderItem.id}`
                const matchedItem = temp.find(refundItem => refundItem.host_id === orderItem.host_id);
                if (matchedItem) {
                  matchedItem.children = matchedItem.children || [];
                  matchedItem.children.push(orderItem);
                }
              });
              this.productRefundData = temp; 
              this.$nextTick(() => {
                this.expandNum++;
              });
            })
            .catch((err) => {
              console.log('err', err);
              this.$message.error(err.data.msg);
            });
        },
         changeExpand(node) {
          this.expandedRowKeys = node;
          this.$nextTick(() => {
            this.expandNum++;
          });
        },
        handleRefund (row) {
          this.refundFormData.host_id = row ? row.host_id : "";

          this.refundVisible = true;
          this.getRefundAmount();
        },
        refundClose () {
          this.$refs.refundDialog && this.$refs.refundDialog.reset();
          this.refundVisible = false;
        },
        refundSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            this.refundLoading = true;
            const params = {
              id: this.id,
              ...this.refundFormData,
            };
            orderRefund(params)
              .then((res) => {
                this.$message.success(res.data.msg);
                this.refundClose();
                this.getOrderDetail();
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              })
              .finally(() => {
                this.refundLoading = false;
              });
          } else {
          }
        },
        async getAddonList () {
          try {
            const res = await getAddon();
            const arr = res.data.data.list.map((item) => item.name);
            this.hasCostPlugin = arr.includes("CostPay");
            this.hasInvoicePlugin = arr.includes("IdcsmartInvoice");
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 变更记录
        changeLog () {
          this.visibleLog = true;
          this.getChangeLog();
        },
        closeLog () {
          this.visibleLog = false;
        },
        goOrder () {
          sessionStorage.removeItem("currentOrderUrl");
          location.href = "order.htm";
        },
        goBack () {
          const url = sessionStorage.currentOrderUrl || "";
          sessionStorage.removeItem("currentOrderUrl");
          if (url) {
            location.href = url;
          } else {
            window.history.back();
          }
        },
        // 金额变更分页
        changePage (e) {
          this.moneyPage.page = e.current;
          this.moneyPage.limit = e.pageSize;
          this.getChangeLog();
        },
        // 获取变更记录列表
        async getChangeLog () {
          try {
            this.moneyLoading = true;
            const res = await getMoneyDetail(
              this.orderDetail.client_id,
              this.moneyPage
            );
            this.logData = res.data.data.list;
            this.logCunt = res.data.data.count;
            this.moneyLoading = false;
          } catch (error) {
            this.moneyLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 增加订单子项
        addSubItem () {
          this.orderDetail.items.push({
            id: this.id,
            description: "",
            amount: "",
            edit: 1,
          });
        },
        saveFlow (row) {
          if (!row.description) {
            return this.$message.error(`${lang.input}${lang.description}`);
          }
          if (!row.amount) {
            return this.$message.error(`${lang.input}${lang.money}`);
          }
          if (row.id === this.id) {
            // 修改
            this.addItem(row);
          } else {
            this.editItem(row);
          }
        },
        async addItem (row) {
          try {
            const res = await updateOrder({
              id: this.id,
              amount: row.amount,
              description: row.description,
            });
            this.$message.success(res.data.msg);
            this.getOrderDetail();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async editItem (row) {
          try {
            const res = await updateArtificialOrder({
              id: row.id,
              amount: row.amount,
              description: row.description,
            });
            this.$message.success(res.data.msg);
            this.getOrderDetail();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        delteFlow (row, ind) {
          if (row.id === this.id) {
            this.orderDetail.items.splice(ind, 1);
            return;
          }
          this.curId = row.id;
          this.delVisible = true;
        },
        async sureDelUser () {
          try {
            this.submitLoading = true;
            const res = await delArtificialOrder({
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.delVisible = false;
            this.getOrderDetail();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取用户信息
        async getUserInfo (id) {
          try {
            const res = await getClientDetail(id);
            this.userCredit = res.data.data.client.credit;
          } catch (error) { }
        },
        async changePay (type) {
          try {
            // 和标记支付的权限是一体的
            if (
              !this.$checkPermission(
                "auth_business_order_detail_order_detail_paid"
              )
            ) {
              return;
            }
            const res = await changePayway({
              id: this.id,
              gateway: type,
            });
            this.$message.success(res.data.msg);
            this.getOrderDetail();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 标记支付
        signPay () {
          this.delId = this.id;
          this.signForm.amount = this.orderDetail.amount;
          this.signForm.credit = this.orderDetail.amount_unpaid;
          this.signForm.transaction_number = "";
          this.payVisible = true;
        },
        async sureSign () {
          try {
            this.payLoading = true;
            const params = {
              id: this.id,
              transaction_number: this.signForm.transaction_number,
            };
            const res = await signPayOrder(params);
            this.$message.success(res.data.msg);
            this.getOrderDetail();
            this.payVisible = false;
            this.payLoading = false;
          } catch (error) {
            this.payLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取支付方式
        async getPayway () {
          try {
            const res = await getPayList();
            this.payList = res.data.data.list;
          } catch (error) { }
        },
        changeAdd (val) {
          if (val * 1 > this.orderDetail.apply_credit_amount * 1) {
            this.formData.amount = (
              this.orderDetail.apply_credit_amount * 1
            ).toFixed(2);
          }
        },
        changeSub (val) {
          if (val * 1 > this.orderDetail.credit * 1) {
            this.formData.amount = (this.orderDetail.credit * 1).toFixed(2);
          }
        },

        async getOrderDetail () {
          try {
            const res = await getOrderDetails({ id: this.id });
            // 当订单状态为已支付或已退款时，获取产品退款列表
            if (
              res.data.data.order.status === "Paid" ||
              res.data.data.order.status === "Refunded"
            ) {
              this.getProductRefundList();
            }
            this.orderDetail = res.data.data.order;
            this.self_defined_field = res.data.data.self_defined_field;
            this.gateway = this.orderDetail.gateway;
            this.getUserInfo(this.orderDetail.client_id);
          } catch (error) { }
        },
        changeCredit (type) {
          this.type = type;
          if (type === "add") {
            this.title = `${lang.app}${lang.credit}`;
            // 可应用余额存在且用户余额足够的时候
            if (this.orderDetail.apply_credit_amount) {
              this.formData.amount =
                this.orderDetail.apply_credit_amount * 1 -
                  this.userCredit * 1 >=
                  0
                  ? (this.userCredit * 1).toFixed(2)
                  : (this.orderDetail.apply_credit_amount * 1).toFixed(2);
            } else {
              this.formData.amount = (
                this.orderDetail.apply_credit_amount * 1
              ).toFixed(2);
            }
            this.formData.status = "Refunded";
          } else {
            // 扣除余额
            this.formData.amount = (this.orderDetail.credit * 1).toFixed(2);
            this.title = `${lang.deduct}${lang.credit}`;
          }
          this.visible = true;
        },
        close () {
          this.visible = false;
        },
        onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.type === "add") {
              this.addCredit();
            } else {
              this.subCredit();
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async addCredit () {
          try {
            const params = JSON.parse(JSON.stringify(this.formData));
            if (this.orderDetail.status !== "Refunded") {
              delete params.status;
            }
            this.submitLoading = true;
            const res = await orderApplyCredit(params);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.visible = false;
            this.getOrderDetail();
            this.getinvoiceStaus();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async subCredit () {
          try {
            const params = {
              id: this.formData.id,
              amount: this.formData.amount,
            };
            this.submitLoading = true;
            const res = await orderRemoveCredit(params);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.visible = false;
            this.getOrderDetail();
            this.getinvoiceStaus();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

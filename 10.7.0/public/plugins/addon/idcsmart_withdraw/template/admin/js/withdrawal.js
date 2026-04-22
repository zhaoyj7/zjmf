(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("withdrawal")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    Vue.prototype.moment = window.moment;
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
          baseUrl: str,
          data: [],
          tableLayout: true,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          virtualScroll: false,
          range: [],
          columns: [
            {
              colKey: "id",
              title: lang.order_index,
              width: 120,
              sortType: "all",
              sorter: true,
              ellipsis: true,
            },
            {
              colKey: "source",
              title: lang.withdrawal_source,
              minWidth: 200,
              ellipsis: true,
            },
            {
              colKey: "method",
              title: lang.withdrawal_way,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "amount",
              title: lang.requested_amount,
              minWidth: 150,
              ellipsis: true,
              className: "price-type-cell"
            },
            {
              colKey: "withdraw_amount",
              title: lang.received_amount,
              minWidth: 150,
              ellipsis: true,
              className: "price-type-cell"
            },
            {
              colKey: "username",
              title: lang.proposer,
              minWidth: 200,
              ellipsis: true,
            },
            {
              colKey: "create_time",
              title: lang.time_application,
              width: 180,
              ellipsis: true,
            },
            {
              colKey: "status",
              title: lang.status,
              width: 110,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 100,
              ellipsis: true,
              fixed: "right",
            },
          ],
          hideSortTips: true,
          params: {
            status: "",
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            start_time: "",
            end_time: "",
          },
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          formData: {
            // 驳回审核
            reason: "",
            custom: "",
          },
          rules: {
            reason: [
              {
                required: true,
                message: lang.select + lang.dismiss_the_reason,
                type: "error",
              },
            ],
            custom: [
              {
                required: true,
                message: lang.input + lang.dismiss_the_reason,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: lang.verify3 + 100,
                type: "warning",
              },
            ],
          },
          total_amount: 0,
          page_total_amount: 0,
          total_apply_amount: 0,
          page_total_apply_amount: 0,
          loading: false,
          country: [],
          delId: "",
          curStatus: 1,
          statusTip: "",
          addTip: "",
          langList: [],
          roleTotal: 0,
          roleList: [],
          optType: "create",
          curId: "",
          roleParams: {
            page: 1,
            limit: 20,
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          maxHeight: "",
          // 驳回状态修改
          changeVisble: false,
          updateStatus: "",
          payVisible: false,
          payStatus: "add",
          payTit: "",
          payForm: {
            transaction_number: "",
          },
          btnLoading: false,
          payRules: {
            transaction_number: [
              {
                required: true,
                message: lang.input + lang.flow_number,
                type: "error",
              },
              {
                pattern: /^[A-Za-z0-9]+/,
                message: lang.verify9,
                type: "warning",
              },
            ],
          },
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          reasons: [],
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
        };
      },
      created() {
        this.getResons();
        this.getList();
      },
      methods: {
        jumpUser(row) {
          location.href = str + `client_detail.htm?client_id=${row.client_id}`;
        },
        async getResons() {
          try {
            const res = await getRejectReason();
            const temp = res.data.data.list;
            temp.push({
              id: 0,
              reason: lang.custom_reason,
            });
            this.reasons = temp;
          } catch (error) { }
        },
        checkPwd(val) {
          if (val !== this.formData.password) {
            return {
              result: false,
              message: window.lang.password_tip,
              type: "error",
            };
          }
          return { result: true };
        },
        // 获取列表
        async getList() {
          try {
            if (this.range.length > 0) {
              this.params.start_time =
                new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 ||
                "";
              this.params.end_time =
                new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 ||
                "";
            } else {
              this.params.start_time = "";
              this.params.end_time = "";
            }
            this.loading = true;
            const res = await getWithdrawal(this.params);
            this.loading = false;
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.total_amount = res.data.data.total_amount;
            this.page_total_amount = res.data.data.page_total_amount;
            this.total_apply_amount = res.data.data.amount_all;
            this.page_total_apply_amount = res.data.data.amount;
          } catch (error) {
            this.loading = false;
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getList();
        },
        // 排序
        sortChange(val) {
          if (val === undefined) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getList();
        },
        // 切换状态
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.params.page = 1;
          this.getList();
        },
        close() {
          this.visible = false;
          this.$nextTick(() => {
            this.$refs.userDialog && this.$refs.userDialog.reset();
          });
        },
        // 驳回审核
        rejectHandler(row) {
          this.formData.reason = "";
          this.formData.custom = "";
          this.visible = true;
          this.delId = row.id;
          this.addTip = lang.approved_reject;
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = {
                id: this.delId,
                status: 2,
              };
              if (this.formData.reason === 0) {
                params.reason = this.formData.custom;
              } else {
                params.reason = this.reasons.filter(
                  (item) => item.id === this.formData.reason
                )[0]?.reason;
              }
              this.btnLoading = true;
              const res = await changeStatus(params);
              this.$message.success(res.data.msg);
              this.getList();
              this.visible = false;
              this.$refs.userDialog.reset();
              this.btnLoading = false;
            } catch (error) {
              this.btnLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        // 修改驳回的状态
        editStatus(row) {
          this.changeVisble = true;
          this.delId = row.id;
          this.updateStatus = row.status ? 0 : 1;
        },
        closeChange() {
          this.changeVisble = false;
        },
        async onSubmitChange() {
          try {
            this.btnLoading = true;
            const res = await changeWithdrawStatus({
              id: this.delId,
              status: this.updateStatus,
            });
            this.$message.success(res.data.msg);
            this.changeVisble = false;
            this.btnLoading = false;
            this.getList();
          } catch (error) {
            this.btnLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 审核通过
        passHandler(row) {
          this.delId = row.id;
          this.statusTip = lang.sure + lang.approved + "?";
          this.statusVisble = true;
          this.payForm = JSON.parse(JSON.stringify(row));
        },
        async sureChange() {
          try {
            const params = {
              id: this.delId,
              status: 1,
            };
            this.btnLoading = true;
            const res = await changeStatus(params);
            this.$message.success(res.data.msg);
            this.statusVisble = false;
            this.getList();
            this.btnLoading = false;
          } catch (error) {
            this.btnLoading = false;
            this.$message.error(error.data.msg);
            this.statusVisble = false;
          }
        },
        rejectAudit() {
          this.statusVisble = false;
          this.visible = true;
          this.addTip = lang.review_the_rejected;
        },
        closeDialog() {
          this.statusVisble = false;
        },
        // 确认已付款/修改流水号
        confirmRemittance(row) {
          this.payForm = JSON.parse(JSON.stringify(row));
          this.payTit =
            row.status === 3
              ? lang.update + lang.flow_number
              : lang.confirm_remittance;
          this.payVisible = true;
        },
        onSubmitPay({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.payForm.status !== 3) {
              this.surePay();
            } else {
              this.changeTransaction();
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        // 确认付款
        async surePay() {
          try {
            const { id, transaction_number } = this.payForm;
            const params = {
              id,
              transaction_number,
            };
            this.btnLoading = true;
            const res = await submitPay(params);
            this.$message.success(res.data.msg);
            this.getList();
            this.payVisible = false;
            this.btnLoading = false;
          } catch (error) {
            this.btnLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 修改流水
        async changeTransaction() {
          try {
            const { id, transaction_number } = this.payForm;
            const params = {
              id,
              transaction_number,
            };
            this.btnLoading = true;
            const res = await updateTransaction(params);
            this.$message.success(res.data.msg);
            this.getList();
            this.payVisible = false;
            this.btnLoading = false;
          } catch (error) {
            this.btnLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        closePay() {
          this.payVisible = false;
        },
        // 复制账号
        copyHandler(id) {
          const name = document.getElementById(id);
          name.select();
          document.execCommand("Copy");
          this.$message.success(lang.copy + lang.success);
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

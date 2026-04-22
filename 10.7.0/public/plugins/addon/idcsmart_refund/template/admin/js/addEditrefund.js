(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig,
        comTreeSelect,
      },
      data() {
        return {
          fromData: {
            product_id: "",
            product_ids: [],
            type: "",
            rule: "",
            range: 0,
            range_control: "",
            ratio_value: 0,
            require: "",
            action: "Suspend",
            full_refund_days: 0,
            api_refund_allow: 1
          },
          typeOptions: [
            {
              id: "Artificial",
              name: lang.refund_op_text2,
            },
            {
              id: "Auto",
              name: lang.refund_op_text3,
            },
          ],
          productOptions: [],
          productConfig: null,
          productDetail: [],
          isEdit: false,
          submitLoading: false,
          tempRule: "",
        };
      },
      methods: {
        fullRefundDaysChange (val) {
          this.fromData.full_refund_days = val < 0 ? 0 : val;
        },
        //获取路由参数
        getUrlOption() {
          let str = location.search.substr(1).split("&");
          let obj = {};
          str.forEach((e) => {
            let list = e.split("=");
            obj[list[0]] = list[1];
          });
          return obj;
        },
        //获取单个退款商品详情
        getRefundDetail(id) {
          getARefund(id)
            .then((res) => {
              this.fromData = res.data.data.refund_product;
              this.fromData.product_ids = [
                res.data.data.refund_product.product_id,
              ];
              this.tempRule = this.fromData.rule;
              if (this.fromData.range_control) {
                this.fromData.require = "range";
              }
              this.fromData.range = Number(this.fromData.range);
              this.fromData.ratio_value = Number(this.fromData.ratio_value);
              this.$forceUpdate();
            })
            .catch();
        },
        //获取商品下拉框数据
        getProductOptions() {
          getProductList({ page: 1, limit: 10000 })
            .then((res) => {
              this.productOptions = res.data.data.list;
            })
            .catch();
        },
        //获取商品配置列表
        getConfigList(id) {
          getARefundConfig(id)
            .then((res) => {
              this.fromData.config_option = res.data.data.content;
              this.$forceUpdate();
            })
            .catch();
        },
        //所选商品改变
        productChange(val) {
          this.fromData.product_ids = val;
          this.fromData.product_id = val;
        },
        //所选规则改变
        ruleChange() {
          this.$forceUpdate();
        },
        //选择退款规则购买天数
        checkRange() {
          this.fromData.require = "range";
          this.$forceUpdate();
        },
        checkChange(e, type) {
          if (e) {
            this.fromData.require = type;
          } else {
            this.fromData.require = "";
          }
          this.$forceUpdate();
        },
        handleClick() {
          this.fromData.require =
            this.fromData.require === "range" ? "" : "range";
        },

        //比例输入
        changere_fundRate() {
          this.fromData.ratio_value = this.fromData.ratio_value + "";
          this.fromData.ratio_value = Number(
            this.fromData.ratio_value.replace(/-/g, "")
          );
        },

        //提交 判断是否编辑新增退款商品
        addEdit() {
          if (this.fromData.product_ids.length === 0) {
            this.$message.warning({
              content: lang.product_id_empty_tip,
            });
            return;
          }
          if (!this.fromData.type) {
            this.$message.warning({
              content: lang.type_empty_tip,
            });
            return;
          }
          if (!this.fromData.rule) {
            this.$message.warning({
              content: lang.rule_empty_tip,
            });
            return;
          }
          this.submitLoading = true;
          const params = {
            type: this.fromData.type, //string	-	required	退款类型:Artificial人工，Auto自动
            require:
              this.fromData.require !== "range" ? this.fromData.require : "", //string	-	退款要求:First首次订购,Same同类商品首次订购
            range: this.fromData.range, //int	-	required	购买后X天内
            range_control: this.fromData.require === "range" ? 1 : 0,
            rule: this.fromData.rule, //string	-	required	退款规则:Day按天退款,Month按月退款,Ratio按比例退款
            action: this.fromData.action,
            full_refund_days: this.fromData.full_refund_days, //int	-	0
            api_refund_allow: this.fromData.api_refund_allow, //int	-	0
          };
          if (this.fromData.rule === "Ratio") {
            params.ratio_value = this.fromData.ratio_value; //比例,当rule=Ratio时,需要传此值,默认为0
          }
          this.submitLoading = true;
          if (this.fromData.id) {
            params.id = this.fromData.id;
            params.product_id = this.fromData.product_id;

            upDateRefund(params)
              .then((res) => {
                this.$message.success({
                  content: res.data.msg,
                  placement: "top",
                });
                this.goback();
              })
              .catch((error) => {
                this.$message.warning({
                  content: error.data.msg,
                  placement: "top",
                });
              })
              .finally(() => {
                this.submitLoading = false;
              });
          } else {
            params.product_ids = this.fromData.product_ids;
            addRefund(params)
              .then((res) => {
                this.$message.success({
                  content: res.data.msg,
                  placement: "top",
                });
                this.goback();
              })
              .catch((error) => {
                this.$message.warning({
                  content: error.data.msg,
                  placement: "top",
                });
              })
              .finally(() => {
                this.submitLoading = false;
              });
          }
        },
        // goback (showTip) {
        //   if (showTip) {
        //     this.$dialog({
        //       theme: 'warning',
        //       header: `${lang.sure_cancel}`,
        //       className: 't-dialog-new-class1 t-dialog-new-class2',
        //       style: 'color: rgba(0, 0, 0, 0.6)',
        //       confirmBtn: lang.sure,
        //       cancelBtn: lang.cancel,
        //       onConfirm: () => {
        //         location.href = 'refund.htm';
        //         mydialog.hide();
        //       }
        //     });
        //   } else {
        //     location.href = 'refund.htm';
        //   }
        // }
        goback(showTip) {
          location.href = "refund.htm";
        },
      },
      created() {
        //this.getProductOptions();
        if (this.getUrlOption().id) {
          this.isEdit = true;
          this.getRefundDetail(this.getUrlOption().id);
        }
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

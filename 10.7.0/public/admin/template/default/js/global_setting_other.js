(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "product-notice-manage"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comTreeSelect,
        comConfig,
      },
      data() {
        return {
          saveLoading: false,
          product_ids: [],
          configForm: {
            product_global_renew_rule: 0,
            product_global_show_base_info: 0,
            product_overdue_not_delete_open: 0,
            product_overdue_not_delete_product_ids: [],
            host_sync_due_time_open: 0,
            host_sync_due_time_apply_range: 0,
            host_sync_due_time_product_ids: [],
            auto_renew_in_advance: 0,
            auto_renew_in_advance_num: 0,
            auto_renew_in_advance_unit: "minute",
          },
        };
      },
      created() {
        this.getCustomProductConfig();
        this.getProductSet();
      },
      methods: {
        choosePro(val) {
          this.product_ids = val;
        },

        chooseDelPro(val) {
          this.configForm.product_overdue_not_delete_product_ids = val;
        },
        chooseAngetPro(val) {
          this.configForm.host_sync_due_time_product_ids = val;
        },

        getProductSet() {
          apiTouristVisibleProduct().then((res) => {
            this.product_ids = res.data.data.tourist_visible_product_ids.map(
              (item) => item * 1
            );
          });
        },
        saveProductSet() {
          apiSaveTouristVisibleProduct({
            tourist_visible_product_ids: this.product_ids,
          })
            .then((res) => {})
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        getCustomProductConfig() {
          apiGetProductConfig().then((res) => {
            this.configForm = {...res.data.data};
            this.configForm.product_overdue_not_delete_product_ids =
              res.data.data.product_overdue_not_delete_product_ids.map(
                (item) => item * 1
              );
          });
        },
        saveConfig() {
          this.saveProductSet();
          this.saveLoading = true;
          apiSaveProductConfig(this.configForm)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.saveLoading = false;
              this.getCustomProductConfig();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.saveLoading = false;
            });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

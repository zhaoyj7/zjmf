(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "product-notice-demand"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          submitLoading: false,
          formData: {
            grace_time: null,
            grace_time_unit: "",
            keep_time: null,
            keep_time_unit: "",
          },
          timeArr: [
            { value: "hour", label: lang.product_set_text131 },
            { value: "day", label: lang.product_set_text132 },
          ],
          rules: {
            grace_time: [{ required: true, message: lang.input, type: "error" }],
            grace_time_unit: [{ required: true, message: lang.select, type: "error" }],
            keep_time: [{ required: true, message: lang.input, type: "error" }],
            keep_time_unit: [{ required: true, message: lang.select, type: "error" }],
          }
        };
      },
      created () {
        this.getDemandConfig();
      },
      computed: {},
      methods: {
        changeNum (val, type) {
          if (val < 0) {
            this.formData[type] = 0;
          }
        },
        async getDemandConfig () {
          try {
            const res = await getGlobalDemand();
            this.formData = res.data.data;
          } catch (error) {
            this.$message.error(error.message);
          }
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await saveGlobalDemand(this.formData);
              this.$message.success(res.data.msg);
              this.getDemandConfig();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

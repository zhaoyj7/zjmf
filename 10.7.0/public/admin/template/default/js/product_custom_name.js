(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementById("content");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          id: "",
          customForm: {
            custom_host_name: 0,
            custom_host_name_prefix: "",
            custom_host_name_string_allow: [],
            custom_host_name_string_length: null
          },
          submitLoading: false,
          rules: {
            custom_host_name_prefix: [
              { required: true, message: `${lang.input}${lang.product_custom_prefix}`, type: "error" },
              { pattern: /^[a-zA-Z][0-9a-zA-Z_\-.]{1,10}$/, message: lang.product_custom_tip4 },
            ],
            custom_host_name_string_allow: [
              { required: true, message: `${lang.select}${lang.product_custom_string}`, type: "error" },
            ],
            custom_host_name_string_length: [
              { required: true, message: `${lang.input}${lang.product_custom_length}`, type: "error" },
            ],
          },
          isAgent: false
        };
      },
      created () {
        this.id = location.href.split("?")[1].split("=")[1];
        this.getCustomConfig();
        this.getUserDetail();
      },
      computed: {},
      mounted () { },
      methods: {
        async getUserDetail() {
          try {
            const res = await getProductDetail(this.id);
            const temp = res.data.data.product;
            this.isAgent = temp.mode === 'sync';
          } catch (error) {
            console.log(error);
          }
        },
        async submitCustom ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = Object.assign(this.customForm, { id: this.id * 1 });
              const res = await saveProductCustom(params);
              this.$message.success(res.data.msg);
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            this.submitLoading = false;
          }
        },
        handleLength (val) {
          if (val > 50) {
            this.customForm.custom_host_name_string_length = 50;
          }
          if (val < 5) {
            this.customForm.custom_host_name_string_length = 5;
          }
        },
        async getCustomConfig () {
          try {
            const res = await getProductCustom({ id: this.id });
            this.customForm = res.data.data;
            document.title =
              lang.product_custom_name +
              "-" + localStorage.getItem("back_website_name");
          } catch (error) {
            console.log(error);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

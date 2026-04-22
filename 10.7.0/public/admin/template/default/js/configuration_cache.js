(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-cache")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          loading: false,
          cacheList: [],
          allLoading: false,
          pluginLoading: false,
          configLoading: false,
          langLoading: false,
          routeLoading: false,
        };
      },
      methods: {
        async getCacheStatus () {
          try {
            this.loading = true;
            const res = await getCacheStatistics();
            this.cacheList = Object.entries(res.data.data).map(([key, value]) => ({
              value: key,
              label: value
            }));
          } catch (error) {
            this.$message.error(error.message);
          } finally {
            this.loading = false;
          }
        },
        async handleClar (type) {
          try {
            this[`${type}Loading`] = true;
            const res = await clearCache(type);
            this.getCacheStatus();
            this.$message.success(res.data.msg);
          } catch (error) {
            this.$message.error(error.data.msg);
          } finally {
            this[`${type}Loading`] = false;
          }
        },
      },
      created () {
        this.getCacheStatus();
        document.title = lang.system_cache + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

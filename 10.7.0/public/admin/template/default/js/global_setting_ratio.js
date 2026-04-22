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
          configForm: {
            product_new_host_renew_with_ratio_open: 0,
            product_new_host_renew_with_ratio_apply_range: 0,
            product_new_host_renew_with_ratio_apply_range_1: [],
            product_new_host_renew_with_ratio_apply_range_2: [],
          },
          serverGroupList: [],
          checkAll: false,
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          treeProps: {
            valueMode: "onlyLeaf",
          },
        };
      },
      created() {
        this.getCustomProductConfig();
        this.getServerGroup();
      },
      watch: {
        isCheckAll(val) {
          this.checkAll = val;
        },
      },
      computed: {
        isCheckAll() {
          const allServer = [];
          this.serverGroupList.forEach((item) => {
            allServer.push(...item.children.map((server) => server.value));
          });
          return (
            this.configForm.product_new_host_renew_with_ratio_apply_range_1
              .length === allServer.length
          );
        },
      },
      methods: {
        chooseAll(val) {
          if (val) {
            const allServer = [];
            this.serverGroupList.forEach((item) => {
              allServer.push(...item.children.map((server) => server.value));
            });
            this.configForm.product_new_host_renew_with_ratio_apply_range_1 =
              allServer;
          } else {
            this.configForm.product_new_host_renew_with_ratio_apply_range_1 =
              [];
          }
        },
        getCustomProductConfig() {
          apiGetProductConfig().then((res) => {
            this.configForm = { ...res.data.data };
            this.configForm.product_new_host_renew_with_ratio_apply_range_1 =
              this.configForm.product_new_host_renew_with_ratio_apply_range_1.map(
                (item) => parseInt(item)
              );
          });
        },
        choosePro(val) {
          this.configForm.product_new_host_renew_with_ratio_apply_range_2 = val;
        },
        getServerGroup() {
          getInterface({ page: 1, limit: 999999 }).then((res) => {
            const list = res.data.data.list;
            // 根据list里的module分类
            const moduleMap = {};
            list.forEach((item) => {
              if (!moduleMap[item.module]) {
                moduleMap[item.module] = {
                  value: item.module,
                  label: item.module_name,
                  children: [{ value: item.id, label: item.name }],
                };
              } else {
                moduleMap[item.module].children.push({
                  value: item.id,
                  label: item.name,
                });
              }
            });
            // 过滤掉children 为空的module
            this.serverGroupList = Object.values(moduleMap).filter(
              (item) => item.children.length > 0
            );
          });
        },
        saveConfig() {
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

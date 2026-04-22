(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("addon")[0];
    Vue.prototype.lang = window.lang;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        safeConfirm,
      },
      data() {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          urlPath: url,
          isAllLoading: false,
          isAllDownloadLoading: false,
          basrUrl: str,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 65,
            },
            {
              colKey: "title",
              title: lang.app_name,
              minWidth: 200,
              ellipsis: true,
            },
            {
              colKey: "type_name",
              title: `${lang.application}${lang.type}`,
              minWidth: 150,
              ellipsis: true,
            },
            {
              colKey: "author",
              title: lang.author,
              minWidth: 200,
              ellipsis: true,
            },
            {
              colKey: "version",
              title: lang.version,
              minWidth: 120,
              ellipsis: true,
              className: "version",
            },
            {
              colKey: "status",
              title: lang.status,
              width: 100,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 90,
              fixed: "right",
            },
          ],
          hideSortTips: true,
          params: {
            keywords: "",
            page: 1,
            limit: 10,
            orderby: "id",
            sort: "desc",
          },
          total: 0,
          typeObj: {
            addon: lang.plugin,
            captcha: lang.captcha_interface,
            certification: lang.certification,
            gateway: lang.gateway,
            mail: lang.email_interface,
            sms: lang.sms_interface,
            server: lang.module,
            template: lang.theme,
            oauth: lang.oauth,
            oss: lang.oss_setting,
          },
          pageSizeOptions: [10, 20, 50, 100],
          rules: {
            username: [
              {
                required: true,
                message: lang.input + lang.name,
                type: "error",
              },
            ],
          },
          loading: false,
          country: [],
          delId: "",
          curStatus: 1,
          statusTip: "",
          maxHeight: "",
          curName: "",
          installTip: "",
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          module: "addon", // 当前模块
          upVisible: false,
          curName: "",
          upLoading: false,
          pluginUpgrade: false,
          syncVisible: false,
          isNeedUpgrade: false,
          syncPluginList: [],
          showPluginList: [],
          btnLoading: false,
          submitLoading: false,
          isInit: true,
          curDownIndex: "",
          selectedRowKeys: [],
          pluginColumns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 50,
              checkProps: ({row}) => ({
                disabled: !( row.downloaded * 1 === 0 || (row.downloaded * 1 === 1 && row.upgrade * 1 === 1)),
              }),
            },
            {
              colKey: "name",
              title: `${lang.app_name}`,
              ellipsis: true,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "type_name",
              title: `${lang.application}${lang.type}`,
              ellipsis: true,
            },
            {
              colKey: "version",
              title: `${lang.application}${lang.version}`,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 80,
              fixed: "right",
            },
          ],
          // 新增
          pagination: {
            keywords: "",
            status: "",
            current: 1,
            pageSize: getGlobalLimit(),
            pageSizeOptions: [10, 20, 50, 100],
            total: 0,
            showJumper: true,
          },
          hookColumns: [
            {
              colKey: "drag",
              width: 30,
              className: "drag-icon",
            },
            {
              colKey: "id",
              title: lang.order_text68,
              width: 80,
              ellipsis: true,
            },
            {
              colKey: "title",
              title: lang.plug_name,
              width: 140,
              ellipsis: true,
            },
            {
              colKey: "author",
              title: lang.author,
              width: 140,
              ellipsis: true,
            },
            {
              colKey: "status",
              title: lang.status,
              width: 100,
              ellipsis: true,
            },
          ],
          hookDialog: false,
          hookList: [],
          hookLoading: false,
          filterPluginList: [],
          admin_operate_password: "",
          searchKey: "",
          searchType: "",
          plguinSortBy: "",
          plguinSort: "desc",
        };
      },
      computed: {
        enableTitle() {
          return (status) => {
            if (status === 1) {
              return lang.disable;
            } else if (status === 0) {
              return lang.enable;
            }
          };
        },
        installTitle() {
          return (status) => {
            if (status === 3) {
              return lang.install;
            } else {
              return lang.uninstall;
            }
          };
        },
      },
      mounted() {},
      created() {
        // 权限相关
        if (!this.$checkPermission("auth_app_list")) {
          return this.$message.error(lang.tip17 + "," + lang.tip18);
        }
        // 处理默认分页条数
        const limit = getGlobalLimit();
        this.pagination.pageSize = limit;
        const temp = [...new Set([...this.pagination.pageSizeOptions, limit])].sort((a, b) => a - b);
        this.pagination.pageSizeOptions = temp;

        this.getAddonList();
        this.getSystem();
        document.title =
          lang.plugin_list + "-" + localStorage.getItem("back_website_name");
      },
      methods: {
        rehandleSelectChange(value) {
          this.selectedRowKeys = value;
        },
        jumpMenu(e, row) {
          localStorage.setItem("curValue", row.menu_id);
        },
        async handleHook() {
          try {
            this.hookLoading = true;
            const res = await getHookPlugin();
            this.hookList = res.data.data.list;
            this.hookDialog = true;
            this.hookLoading = false;
          } catch (error) {
            this.hookLoading = false;
            this.hookDialog = false;
            this.$message.error(error.data.msg);
          }
        },
        onDragSort(newData) {
          const arr = newData.currentData.reduce((all, cur) => {
            all.push(cur.id);
            return all;
          }, []);
          this.changeOrder(arr);
        },
        async changeOrder(arr) {
          try {
            const res = await changeHookOrder({id: arr});
            this.$message.success(res.data.msg);
            this.handleHook();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 本地搜索
        localFilter() {
          if (!this.pagination.keywords && this.pagination.status === "") {
            this.filterPluginList = this.data;
          } else {
            let temp = [];
            if (this.pagination.status === "") {
              temp = this.data;
            } else {
              temp = this.data.filter(
                (item) => item.status === this.pagination.status
              );
            }
            if (!this.pagination.keywords && this.pagination.status !== "") {
              this.filterPluginList = temp;
            } else {
              const tempKey = this.pagination.keywords.toLowerCase();
              temp = temp.filter((item) => {
                if (
                  item.title.toLowerCase().indexOf(tempKey) !== -1 ||
                  (this.typeObj[item.type] || lang.plugin)
                    .toLowerCase()
                    .indexOf(tempKey) !== -1 ||
                  item.author.toLowerCase().indexOf(tempKey) !== -1
                ) {
                  return true;
                } else {
                  return false;
                }
              });
              this.filterPluginList = temp;
            }
          }
          this.pagination.current = 1;
          this.pagination.total = this.filterPluginList.length;
        },
        searchPlugin() {
          let temp = [...this.syncPluginList];
          if (this.searchType !== "") {
            temp = temp.filter((item) => item.type === this.searchType);
          }
          if (this.searchKey !== "") {
            const tempKey = this.searchKey.toLowerCase();
            temp = temp.filter((item) => {
              return item.name.toLowerCase().indexOf(tempKey) !== -1;
            });
          }
          if (this.plguinSortBy) {
            temp = temp.sort((a, b) => {
              if (this.plguinSort === "desc") {
                return a[this.plguinSortBy].localeCompare(b[this.plguinSortBy]);
              }
              return b[this.plguinSortBy].localeCompare(a[this.plguinSortBy]);
            });
          }
          this.showPluginList = temp;
          this.selectedRowKeys = [];
        },
        /* 同步插件 */
        async getSystem() {
          try {
            this.btnLoading = true;
            const res = await getSysyemVersion();
            if (res.data.data.license) {
              // 存在授权码
              this.syncPlugin();
            } else {
              this.btnLoading = false;
              this.toMarket();
            }
          } catch (error) {
            this.btnLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async syncPlugin() {
          try {
            this.btnLoading = true;
            const res = await syncPlugins();
            this.isNeedUpgrade = false;
            if (res.data.data.list.length === 0 && !this.isInit) {
              this.btnLoading = false;
              return this.$message.error(lang.hook_tip1);
            }
            this.syncPluginList = res.data.data.list.map((item) => {
              if (item.upgrade === 1) {
                this.isNeedUpgrade = true;
              }
              item.error_msg = item.error_msg;
              item.isLoading = false;
              return item;
            });
            this.showPluginList = [...this.syncPluginList];
            if (!this.isInit) {
              this.syncVisible = true;
            }
            this.isInit = false;
            this.btnLoading = false;
          } catch (error) {
            this.btnLoading = false;
          }
        },
        async handlerAllUpgrade(type) {
          if (this.selectedRowKeys.length === 0) {
            return this.$message.warning(lang.select + lang.app);
          }
          let needUpgrade = [];
          if (type === "download") {
            this.isAllDownloadLoading = true;
            needUpgrade = this.showPluginList.filter(
              (item) =>
                this.selectedRowKeys.includes(item.id) &&
                item.downloaded * 1 === 0
            );
          } else {
            this.isAllLoading = true;
            needUpgrade = this.showPluginList.filter(
              (item) =>
                this.selectedRowKeys.includes(item.id) &&
                item.upgrade * 1 === 1 &&
                item.downloaded * 1 === 1
            );
          }
          const allUpgrade = [];
          needUpgrade.forEach((item) => {
            if (!item.error_msg) {
              item.isLoading = true;
              const p = downloadPlugin(item.id)
                .then((res) => {
                  item.upgrade = 0;
                  this.$message.success(res.data.msg);
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                })
                .finally(async () => {
                  // 获取权限
                  const auth = await getAuthRole();
                  const authTemp = auth.data.data.auth;
                  localStorage.setItem("backAuth", JSON.stringify(authTemp));
                  item.isLoading = false;
                });

              allUpgrade.push(p);
            }
          });
          Promise.all(allUpgrade)
            .then(() => {
              if (type === "upgrade") {
                this.isAllLoading = false;
              } else {
                this.isAllDownloadLoading = false;
              }
              this.selectedRowKeys = [];
              this.syncPlugin();
              this.getAddonList();
            })
            .catch(() => {
              if (type === "upgrade") {
                this.isAllLoading = false;
              } else {
                this.isAllDownloadLoading = false;
              }
              this.syncPlugin();
              this.getAddonList();
            });
        },
        async handlerDownload(row) {
          if (row.isLoading || row.error_msg) {
            return;
          }
          try {
            row.isLoading = true;
            const res = await downloadPlugin(row.id);
            this.$message.success(res.data.msg);
            this.syncPlugin();
            // 获取权限
            const auth = await getAuthRole();
            const authTemp = auth.data.data.auth;
            localStorage.setItem("backAuth", JSON.stringify(authTemp));
            setTimeout(() => {
              this.getAddonList();
              row.isLoading = false;
            }, 0);
          } catch (error) {
            row.isLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 同步插件 end */
        // 获取列表
        async getAddonList() {
          try {
            this.loading = true;
            const res = await getAddonList(this.params);
            this.loading = false;
            this.filterPluginList = this.data = res.data.data.list;
            this.pagination.total = res.data.data.count;
            // 获取最新版本
            this.getNewVersion();
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        onPageChange(pageInfo, newData) {
          if (!this.pagination.defaultCurrent) {
            this.pagination.current = pageInfo.current;
            this.pagination.pageSize = pageInfo.pageSize;
          }
        },
        onSelectChange(selectedRowKeys, context) {
          console.log(selectedRowKeys, context);
        },
        /* 升级 start */
        // 获取最新版本
        async getNewVersion(refresh = false) {
          try {
            const res = await getActiveVersion();
            this.pluginUpgrade = res.data.data.upgrade === 1;
            sessionStorage.setItem("pluginUpgrade", this.pluginUpgrade);
            if (refresh) {
              // 刷新页面
              window.location.reload();
            }
            const temp = res.data.data.list;
            const arr = temp.reduce((all, cur) => {
              all.push(cur.uuid);
              return all;
            }, []);
            if (arr.length > 0) {
              this.filterPluginList = this.data = this.data.map((item) => {
                item.isUpdate = false;
                if (arr.includes(item.name)) {
                  const cur = temp.filter((el) => el.uuid === item.name)[0];
                  item.isUpdate = this.checkVersion(
                    cur?.old_version,
                    cur?.version
                  );
                  item.error_msg = temp.filter(
                    (el) => el.uuid === item.name
                  )[0]?.error_msg;
                }
                return item;
              });
            }
          } catch (error) {}
        },
        /**
         *
         * @param {string} nowStr 当前版本
         * @param {string} lastStr 最新版本
         */
        // 对比版本，是否显示升级
        checkVersion(nowStr, lastStr) {
          const nowArr = nowStr.split(".");
          const lastArr = lastStr.split(".");
          let hasUpdate = false;
          const nowLength = nowArr.length;
          const lastLength = lastArr.length;

          const length = Math.min(nowLength, lastLength);
          for (let i = 0; i < length; i++) {
            if (lastArr[i] - nowArr[i] > 0) {
              hasUpdate = true;
            }
          }
          if (!hasUpdate && lastLength - nowLength > 0) {
            hasUpdate = true;
          }
          return hasUpdate;
        },
        updatePlugin(row) {
          if (row.error_msg) {
            return;
          }
          this.upVisible = true;
          this.curName = row.name;
          this.module = row.module;
        },
        // 提交升级
        async sureUpgrade() {
          try {
            this.upLoading = true;
            const res = await upgradePlugin({
              module: this.module,
              name: this.curName,
            });
            this.$message.success(res.data.msg);
            this.upVisible = false;
            this.upLoading = false;
            const auth = await getAuthRole();
            const authTemp = auth.data.data.auth;
            localStorage.setItem("backAuth", JSON.stringify(authTemp));
            this.getAddonList();
            this.getNewVersion(true);
          } catch (error) {
            this.upLoading = false;
            this.upVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 升级 end */
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
          this.getAddonList();
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
          this.getAddonList();
        },
        pluginSortChange(val) {
          if (!val) {
            this.plguinSortBy = "";
          } else {
            this.plguinSortBy = val.sortBy;
            this.plguinSort = val.descending ? "desc" : "asc";
          }
          this.searchPlugin();
        },

        clearKey() {
          this.params.keywords = "";
          this.search();
        },
        search() {
          this.params.page = 1;
          this.getAddonList();
        },

        close() {
          this.visible = false;
          this.$refs.userDialog.reset();
        },
        // 查看用户详情
        // handleClickDetail (row) {
        //   location.href = `client_detail.htm?id=${row.id}`
        // },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        // 停用/启用
        changeStatus(row) {
          this.delId = row.id;
          this.curStatus = row.status;
          this.curName = row.name;
          this.statusTip = this.curStatus ? lang.sureDisable : lang.sure_Open;
          this.statusVisble = true;
        },
        async sureChange(e, remember_operate_password = 0) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1;
            this.submitLoading = true;
            const res = await changeAddonStatus({
              name: this.curName,
              status: tempStatus,
              admin_operate_password,
              admin_operate_methods: "sureChange",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.statusVisble = false;
            this.submitLoading = false;
            // this.getAddonList()
            // 获取导航
            const menus = await getMenus();
            localStorage.setItem(
              "backMenus",
              JSON.stringify(menus.data.data.menu)
            );
            window.location.reload();
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return;
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        closeDialog() {
          this.statusVisble = false;
        },

        // 卸载/安装
        installHandler(row) {
          this.delVisible = true;
          this.name = row.name;
          this.type = row.status === 3 ? "install" : "uninstall";
          this.installTip =
            this.type === "install" ? lang.sureInstall : lang.sureUninstall;
        },
        async sureDel(e, remember_operate_password = 0) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            const params = {
              name: this.name,
            };
            if (this.type === "uninstall") {
              params.admin_operate_password = admin_operate_password;
              params.admin_operate_methods = "sureDel";
              params.remember_operate_password = remember_operate_password;
            }
            const res = await deleteMoudle(this.type, params);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.submitLoading = false;
            // this.getAddonList()
            // 获取导航
            const menus = await getMenus();
            // 获取权限
            const auth = await getAuthRole();
            const authTemp = auth.data.data.auth;
            localStorage.setItem("backAuth", JSON.stringify(authTemp));
            localStorage.setItem(
              "backMenus",
              JSON.stringify(menus.data.data.menu)
            );
            window.location.reload();
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return;
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        cancelDel() {
          this.delVisible = false;
        },
        toMarket() {
          setToken().then((res) => {
            if (res.data.status == 200) {
              let url = res.data.market_url;
              let getqyinfo = url.split("?")[1];
              let getqys = new URLSearchParams("?" + getqyinfo);
              const from = getqys.get("from");
              const token = getqys.get("token");
              window.open(
                `https://my.idcsmart.com/shop/index.html?from=${from}&token=${token}`
              );
            }
          });
        },
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

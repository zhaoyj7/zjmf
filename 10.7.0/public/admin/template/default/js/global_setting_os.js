(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "product-notice-manage"
    )[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;

    new Vue({
      components: {
        comConfig,
        comPagination
      },

      data() {
        return {
          rootRul: url,
          tab: "1",
          params: {
            page: 1,
            limit: getGlobalLimit(),
            keywords: "",
            orderby: "id",
            sort: "desc",
            total: 0,
          },
          pageSizeOptions: [10, 20, 50, 100],
          syncList: [],
          loading: false,
          syncColumns: [
            {
              colKey: "name",
              title: lang.product_set_text65,
              cell: "name",
              ellipsis: true,
            },
            {
              colKey: "result",
              title: lang.product_set_text66,
              cell: "result",
            },
            {
              width: 180,
              colKey: "create_time",
              title: lang.product_set_text67,
              cell: "create_time",
            },
          ],
          nowProductList: [],
          mf_cloud_product_list: [],
          mf_dcim_product_list: [],
          syncDialog: false,
          syncType: "",
          moduleOption: [
            {value: "mf_cloud", label: lang.product_set_text70},
            {value: "mf_dcim", label: lang.product_set_text71},
          ],
          proColumns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 50,
            },
            {
              colKey: "name",
              title: lang.product_set_text65,
              cell: "name",
              ellipsis: true,
            },
            {
              colKey: "type",
              title: lang.product_set_text73,
              cell: "type",
              ellipsis: true,
            },
          ],
          selectedRowKeys: [],
          submitLoading: false,
          /* 操作系统 */
          systemGroup: [],
          systemList: [],
          selectedRowKeys: [],
          systemParams: {
            product_id: "",
            page: 1,
            limit: 1000,
            image_group_id: "",
            keywords: "",
          },
          systemModel: false,
          createSystem: {
            // 添加操作系统表单
            group_id: "",
            name: "",
          },
          systemColumns: [
            {
              colKey: "drag", // 列拖拽排序必要参数
              cell: "drag",
              width: 90,
            },
            {
              colKey: "group_name",
              title: lang.product_set_text76,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.product_set_text77,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
              fixed: "right",
            },
          ],
          groupColumns: [
            // 套餐表格
            {
              // 列拖拽排序必要参数
              colKey: "drag",
              width: 20,
              className: "drag-icon",
            },
            {
              colKey: "image_group_name",
              title: lang.product_set_text76,
              ellipsis: true,
              className: "group-column",
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
              fixed: "right",
            },
          ],
          // 操作系统图标
          iconList: [
            "Windows",
            "CentOS",
            "Ubuntu",
            "Debian",
            "ESXi",
            "XenServer",
            "FreeBSD",
            "Fedora",
            "其他",
            "ArchLinux",
            "Rocky",
            "OpenEuler",
            "AlmaLinux",
          ],
          iconSelecet: [],
          classModel: false,
          classParams: {
            id: "",
            name: "",
            icon: "",
          },
          optType: "add", // 新增/编辑
          comTitle: "",
          cycleRules: {
            // 系统相关
            group_id: [
              {
                required: true,
                message: lang.select + lang.product_set_text76,
                type: "error",
              },
            ],
            rel_image_id: [
              {
                required: true,
                message: lang.input + lang.product_set_text80 + "ID",
                type: "error",
              },
              {
                pattern: /^[0-9]*$/,
                message: lang.input + lang.product_set_text81,
                type: "warning",
              },
            ],
            icon: [
              {
                required: true,
                message: lang.select + lang.product_set_text82,
                type: "error",
                trigger: "change",
              },
            ],
          },
          delVisible: false,
          delStymeVisble: false,
          localLoading: false,
          popupProps: {
            overlayClassName: `custom-select`,
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          delId: null,
          multiliTip: "",
        };
      },
      computed: {
        // 处理级联数据
        calcCascadeImage() {
          const temp = this.systemGroup
            .reduce((all, cur) => {
              all.push({
                id: `f-${cur.id}`,
                name: cur.name,
                children: this.systemList.filter(
                  (item) => item.image_group_id === cur.id
                ),
              });
              return all;
            }, [])
            .filter((item) => item.children.length > 0);
          return temp;
        },
      },
      created() {
        this.searchSync();
        this.getModuleProductList();
        this.iconSelecet = this.iconList.reduce((all, cur) => {
          all.push({
            value: cur,
            label: `${this.rootRul}img/os_icon/${cur}.svg`,
          });
          return all;
        }, []);
      },
      methods: {
        async getPlugin() {
          try {
            const res = await getActivePlugin();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, []);
            const hasMultiLanguage = temp.includes("MultiLanguage");
            this.multiliTip = hasMultiLanguage
              ? `(${lang.support_multili_mark})`
              : "";
          } catch (error) {}
        },
        // 分类管理
        classManage() {
          this.classModel = true;
          this.classParams.name = "";
          this.classParams.icon = "";
          this.optType = "add";
        },
        // 系统列表
        async getSystemList() {
          try {
            this.localLoading = true;
            const res = await apiGetLocalImageList();
            this.systemList = res.data.data.list;
            this.localLoading = false;
            this.selectedRowKeys = [];
          } catch (error) {
            this.localLoading = false;
          }
        },
        // 系统分类
        async getGroup() {
          try {
            const res = await apiLocalImageGroupList();
            this.systemGroup = res.data.data.list;
          } catch (error) {}
        },
        createNewSys() {
          // 新增
          this.optType = "add";
          this.comTitle = `${lang.add}${lang.product_set_text83}`;
          this.createSystem.group_id = "";
          this.createSystem.name = "";
          this.systemModel = true;
        },
        editSystem(row) {
          this.optType = "update";
          this.comTitle = lang.update + lang.product_set_text83;
          this.createSystem = {...row};
          this.systemModel = true;
        },
        async submitSystemGroup({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.classParams));
              this.submitLoading = true;
              const subApi =
                this.optType === "update"
                  ? apiEditLocalImageGroup
                  : apiAddLocalImageGroup;
              const res = await subApi(params);
              this.$message.success(res.data.msg);
              this.getGroup();
              this.submitLoading = false;
              this.classParams.name = "";
              this.classParams.icon = "";
              this.$refs.classForm.reset();
              this.optType = "add";
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        editGroup(row) {
          this.optType = "update";
          this.classParams = JSON.parse(JSON.stringify(row));
        },
        delGroup(row) {
          this.delId = row.id;
          this.delVisible = true;
        },
        delSystem(row) {
          this.delId = row.id;
          this.delStymeVisble = true;
        },
        async submitSystem({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.createSystem));
              this.submitLoading = true;
              const subApi =
                this.optType === "add" ? apiAddLocalImage : apiEditLocalImage;
              const res = await subApi(params);
              this.$message.success(res.data.msg);
              this.getSystemList();
              this.systemModel = false;
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async deleteGroup() {
          try {
            this.submitLoading = true;
            const res = await apiDelLocalImageGroup({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.delId = null;
            this.getGroup();
            this.classParams.name = "";
            this.classParams.icon = "";
            this.$refs.classForm.reset();
            this.optType = "add";
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async deleteSystem() {
          try {
            this.submitLoading = true;
            const res = await apiDelLocalImage({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delStymeVisble = false;
            this.delId = null;
            this.getSystemList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        rehandleSelectChange(value, {selectedRowData}) {
          this.selectedRowKeys = value;
        },
        handleTabChange(tab) {
          if (tab === "1") {
            this.searchSync();
          } else if (tab === "2") {
            this.getSystemList();
            this.getGroup();
          }
        },
        findSync() {
          if (this.syncType === "mf_cloud") {
            this.nowProductList = this.mf_cloud_product_list;
          } else if (this.syncType === "mf_dcim") {
            this.nowProductList = this.mf_dcim_product_list;
          } else {
            this.nowProductList = [
              ...this.mf_cloud_product_list,
              ...this.mf_dcim_product_list,
            ];
          }
        },
        syncClose() {
          this.syncType = "";
          this.selectedRowKeys = [];
          this.syncDialog = false;
        },
        syncSubmit() {
          if (this.selectedRowKeys.length === 0) {
            this.$message.error(lang.product_set_text74);
            return;
          }
          this.submitLoading = true;
          apiSyncImage({product_id: this.selectedRowKeys})
            .then((res) => {
              this.$message.success(res.data.msg);
              this.submitLoading = false;
              this.syncDialog = false;
              this.searchSync();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.submitLoading = false;
            });
        },
        handelSync() {
          this.syncType = "";
          this.findSync();
          this.selectedRowKeys = [];
          this.syncDialog = true;
        },
        onDragSort({targetIndex, newData}) {
          this.systemList = newData;
          apiDragLocalImage({
            id: newData.map((item) => item.id),
          }).then((res) => {
            this.$message.success(res.data.msg);
          });
        },
        async changeSort(e) {
          try {
            this.systemGroup = e.newData;
            const image_group_order = e.newData.reduce((all, cur) => {
              all.push(cur.id);
              return all;
            }, []);
            const res = await apiDragLocalImageGroup({id: image_group_order});
            this.$message.success(res.data.msg);
            this.getGroup();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        getSyncList() {
          this.loading = true;
          apiSyncImageLog(this.params)
            .then((res) => {
              this.syncList = res.data.data.list;
              this.loading = false;
              this.params.total = res.data.data.count;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.loading = false;
            });
        },
        searchSync() {
          this.params.page = 1;
          this.getSyncList();
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getSyncList();
        },
        getModuleProductList() {
          apiGetProductListByModule({module: "mf_cloud"}).then((res) => {
            this.mf_cloud_product_list = res.data.data.list
              .reduce((acc, cur) => {
                if (cur.child && cur.child.length > 0) {
                  const childList = cur.child;
                  childList.forEach((item) => {
                    if (item.child && item.child.length > 0) {
                      acc.push(...item.child); // 使用 push 方法来添加
                    }
                  });
                }
                return acc; // 确保返回 acc
              }, [])
              .map((item) => {
                item.type = "mf_cloud";
                return item;
              });
          });

          apiGetProductListByModule({module: "mf_dcim"}).then((res) => {
            this.mf_dcim_product_list = res.data.data.list
              .reduce((acc, cur) => {
                if (cur.child && cur.child.length > 0) {
                  const childList = cur.child;
                  childList.forEach((item) => {
                    if (item.child && item.child.length > 0) {
                      acc.push(...item.child); // 使用 push 方法来添加
                    }
                  });
                }
                return acc; // 确保返回 acc
              }, [])
              .map((item) => {
                item.type = "mf_dcim";
                return item;
              });
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

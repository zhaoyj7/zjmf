(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "product-notice-manage"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          saveLoading: false,
          loading: false,
          loading2: false,
          configForm: {
            product_duration_group_presets_open: 0,
          },
          cycleList: [],
          editableRowKeys: [],
          cycleColumns: [
            {
              colKey: "id",
              title: "ID",
              cell: "id",
              width: 90,
            },
            {
              colKey: "name",
              title: lang.product_set_text30,
              cell: "name",
              ellipsis: true,
            },
            {
              colKey: "duration_info",
              title: lang.product_set_text31,
              cell: "duration_info",
            },
            {
              colKey: "ration_info",
              title: lang.product_set_text32,
              cell: "ration_info",
              ellipsis: true,
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          linkColumns: [
            {
              colKey: "gid",
              title: "ID",
              cell: "gid",
              width: 90,
            },
            {
              colKey: "servers",
              title: lang.product_set_text58,
              cell: "servers",
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.product_set_text59,
              cell: "name",
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          linkList: [],
          unitOptions: [
            { label: lang.product_set_text33, value: "hour" },
            { label: lang.product_set_text34, value: "day" },
            { label: lang.product_set_text35, value: "month" },
          ],
          delVisible: false,
          delLinkVisible: false,
          submitLoading: false,
          delId: null,
          editId: null,
          editCycleDialog: false,
          keywords: "",
          cycleFormData: {
            name: "",
            ratio_open: 0,
            durations: [],
          },
          cycleRules: {
            name: [
              {
                required: true,
                message: `${lang.input}${lang.product_set_text39}`,
                type: "error",
              },
            ],
            server_ids: [
              {
                required: true,
                message: `${lang.select}${lang.product_set_text61}`,
                type: "error",
              },
            ],
            gid: [
              {
                required: true,
                message: `${lang.select}${lang.product_set_text59}`,
                type: "error",
              },
            ],
          },
          durationColumns: [
            {
              colKey: "name",
              title: lang.product_set_text41,
              cell: "name",
            },
            {
              colKey: "num",
              title: lang.product_set_text42,
              cell: "num",
            },
            {
              colKey: "unit",
              title: lang.product_set_text43,
              cell: "unit",
            },
            {
              colKey: "ratio",
              title: lang.product_set_text48,
              cell: "ratio",
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          editLinkDialog: false,
          linkFormData: {
            server_ids: [],
            gid: "",
          },
          allServerList: [],
        };
      },
      created() {
        this.getCustomProductConfig();
        this.getCycleList();
        this.getLinkList();
        this.getServerList();
      },
      watch: {},

      computed: {
        addCycleColumns() {
          if (this.cycleFormData.ratio_open === 1) {
            return this.durationColumns;
          } else {
            return this.durationColumns.filter(
              (item) => item.colKey !== "ratio"
            );
          }
        },
        serverOptions() {
          const haveServer = [];
          this.linkList.forEach((item) => {
            item.servers.forEach((server) => {
              if (!haveServer.includes(server.server_id)) {
                haveServer.push(server.server_id);
              }
            });
          });
          const defaultList = this.allServerList.filter(
            (item) => !haveServer.includes(item.id)
          );
          const editList = this.editId
            ? this.linkList
                .find((item) => item.gid === this.editId)
                ?.servers.map((item) => {
                  return {
                    id: item.server_id,
                    name: item.server_name,
                  };
                }) || []
            : [];

          return [...defaultList, ...editList];
        },
        cycleOptions() {
          const haveGid = this.linkList.map((item) => item.gid);
          const defaultList = this.cycleList.filter(
            (item) => !haveGid.includes(item.id)
          );
          const editList = this.editId
            ? [this.cycleList.find((item) => item.id === this.editId)]
            : [];
          return [...defaultList, ...editList];
        },
      },
      methods: {
        editLink(row) {
          this.editId = row.gid;
          this.linkFormData = {
            server_ids: row.servers.map((item) => item.server_id),
            gid: row.gid,
          };
          this.editLinkDialog = true;
        },
        delLink(id) {
          this.delId = id;
          this.delLinkVisible = true;
        },
        handelAddPreset() {
          this.editId = null;
          this.linkFormData = {
            server_ids: [],
            gid: "",
          };
          this.editLinkDialog = true;
        },
        sureDelLink() {
          this.submitLoading = true;
          apiDeleteDurationGroupLink({ gid: this.delId })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getLinkList();
              this.delId = null;
              this.delLinkVisible = false;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.submitLoading = false;
            });
        },
        saveDuration({ row, rowIndex }) {
          // 保存变化
          const { name, num, unit, ratio } = row;
          if (name === "" || name === undefined || name === null) {
            this.$message.error(
              lang.product_set_text45 + lang.product_set_text41
            );
            return;
          }
          if (num === null || num === undefined || num === "") {
            this.$message.error(
              lang.product_set_text45 + lang.product_set_text42
            );
            return;
          }
          if (unit === "" || unit === undefined || unit === null) {
            this.$message.error(
              lang.product_set_text46 + lang.product_set_text43
            );
            return;
          }
          if (ratio === null || ratio === undefined || ratio === "") {
            this.$message.error(
              lang.product_set_text46 + lang.product_set_text48
            );
            return;
          }
          row.editable = false;
        },
        editDuration({ row, rowIndex }) {
          row.editable = true;
        },
        delDuration({ row, rowIndex }) {
          this.cycleFormData.durations.splice(rowIndex, 1);
        },
        addDuration() {
          this.cycleFormData.durations.push({
            name: "",
            num: null,
            ratio: 0,
            unit: "",
            editable: true,
          });
        },
        handelAddCycle() {
          this.cycleFormData = {
            name: "",
            ratio_open: 0,
            durations: [],
          };
          this.editCycleDialog = true;
        },
        calcDurationName(row) {
          const duration = row.ration_info.map((item) => item.name).join(" : ");
          const ratio = row.ration_info.map((item) => item.ratio).join(" : ");
          return row.ratio_open === 1 ? `${duration}(${ratio})` : "/";
        },
        getUnitName(value) {
          const unit = this.unitOptions.find((item) => item.value === value);
          return unit ? unit.label : "--";
        },

        // 验证duration
        validateDuration() {
          const { durations } = this.cycleFormData;
          const isEdit = durations.some((item) => item.editable);
          if (isEdit) {
            this.$message.error(lang.product_set_text47);
            return false;
          }
          return true;
        },
        cycleDiaClose() {
          this.editId = null;
          this.$refs.cycleDialog.reset();
          this.editCycleDialog = false;
        },
        linkDiaClose() {
          this.editId = null;
          this.$refs.linkDialog.reset();
          this.editLinkDialog = false;
        },
        onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = { ...this.cycleFormData, id: this.editId };
            const isFinishEdit = this.validateDuration();
            if (!isFinishEdit) {
              return;
            }
            this.submitLoading = true;
            const subApi = this.editId
              ? apiUpdateDurationGroup
              : apiAddDurationGroup;
            subApi(params)
              .then((res) => {
                this.$message.success(res.data.msg);
                this.editCycleDialog = false;
                this.getCycleList();
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              })
              .finally(() => {
                this.submitLoading = false;
              });
          } else {
          }
        },
        linkSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = { ...this.linkFormData, id: this.editId };
            this.submitLoading = true;
            const subApi = this.editId
              ? apiUpdateDurationGrouptLink
              : apiAddDurationGroupLink;
            subApi(params)
              .then((res) => {
                this.$message.success(res.data.msg);
                this.editLinkDialog = false;
                this.getLinkList();
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              })
              .finally(() => {
                this.submitLoading = false;
              });
          } else {
          }
        },
        getServerList() {
          apiGetServerList({
            page: 1,
            limit: 99999999,
          }).then((res) => {
            this.allServerList = res.data.data.list;
          });
        },
        delCycle(id) {
          this.delId = id;
          this.delVisible = true;
        },

        editCycle(row) {
          this.editId = row.id;
          this.cycleFormData = {
            name: row.name,
            ratio_open: row.ratio_open,
            durations: row.duration_info.map((item) => ({
              name: item.name,
              num: item.num,
              ratio: item.ratio,
              unit: item.unit,
              editable: false,
            })),
          };
          this.editCycleDialog = true;
        },
        copyCycle(id) {
          this.loading = true;
          apiCopyDurationGroup({ id: id })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getCycleList();
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },

        sureDelCycle() {
          this.submitLoading = true;
          apiDeleteDurationGroup({ id: this.delId })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getCycleList();
              this.getLinkList();
              this.delId = null;
              this.delVisible = false;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.submitLoading = false;
            });
        },
        getCycleList() {
          this.loading = true;
          apiGetDurationGroup()
            .then((res) => {
              this.loading = false;
              this.cycleList = res.data.data.list;
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },
        getLinkList() {
          this.loading2 = true;
          apiGetDurationGroupLink({
            keywords: this.keywords,
          })
            .then((res) => {
              this.loading2 = false;
              this.linkList = res.data.data.list;
            })
            .catch((err) => {
              this.loading2 = false;
              this.$message.error(err.data.msg);
            });
        },
        getCustomProductConfig() {
          apiGetProductConfig().then((res) => {
            this.configForm = res.data.data;
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

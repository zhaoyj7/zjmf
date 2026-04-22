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
      data () {
        return {
          tab: "1",
          id: "",
          params: {
            type: "product_group",
            relid: "",
          },
          pageSizeOptions: [20, 50, 100],
          total: 0,
          loading: false,
          list: [],
          submitLoading: false,
          columns: [
            {
              colKey: "drag", // 列拖拽排序必要参数
              title: lang.sort,
              cell: "drag",
              width: 90,
              fixed: "left",
            },
            {
              colKey: "id",
              title: "ID",
              cell: "id",
              width: 90,
            },
            {
              colKey: "is_global",
              title: "title-slot-global",
              cell: "is_global",
              width: 100,
            },
            {
              width: 200,
              colKey: "field_name",
              title: lang.client_custom_label9,
              ellipsis: true,
            },
            {
              width: 100,
              colKey: "field_type",
              cell: "field_type",
              title: lang.client_custom_label10,
            },
            {
              width: 200,
              colKey: "description",
              title: lang.client_custom_label23,
              cell: "description",
              ellipsis: true,
            },
            {
              width: 150,
              colKey: "regexpr",
              title: lang.client_custom_label24,
              cell: "regexpr",
              ellipsis: true,
            },
            {
              minWidth: 250,
              colKey: "watch",
              title: lang.client_custom_label31,
              cell: "watch",
              ellipsis: true,
            },
            {
              width: 250,
              colKey: "product_group",
              title: lang.product_set_text13,
              cell: "product_group",
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          hostColumns: [
            {
              colKey: "id",
              title: "ID",
              cell: "id",
              width: 90,
            },
            {
              colKey: "custom_host_name_prefix",
              cell: "custom_host_name_prefix",
              title: lang.product_set_text18,
              ellipsis: true,
            },
            {
              colKey: "custom_host_name_string_allow",
              cell: "custom_host_name_string_allow",
              title: lang.product_set_text19,
            },
            {
              colKey: "custom_host_name_string_length",
              title: lang.product_set_text20,
              cell: "custom_host_name_string_length",
              ellipsis: true,
            },
            {
              minWidth: 250,
              colKey: "product_group",
              title: lang.product_set_text13,
              cell: "product_group",
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          typeList: [
            {
              value: "text",
              label: lang.client_custom_label22,
            },
            {
              value: "dropdown",
              label: lang.client_custom_label4,
            },
            {
              value: "link",
              label: lang.client_custom_label17,
            },
            {
              value: "password",
              label: lang.client_custom_label18,
            },
            {
              value: "tickbox",
              label: lang.client_custom_label19,
            },
            {
              value: "textarea",
              label: lang.client_custom_label20,
            },
            {
              value: "explain",
              label: lang.client_custom_label39,
            },
          ],
          pagination: {
            current: 1,
            pageSize: 10,
            total: 0,
            showJumper: true,
          },
          materialVisble: false,
          formData: {
            type: "product_group",
            relid: "",
            field_option: "",
            field_type: "",
            field_name: "",
            description: "",
            regexpr: "",
            explain_content: "",
            is_required: false,
            show_order_page: true,
            show_order_detail: true,
            show_client_host_detail: true,
            show_admin_host_detail: true,
            show_client_host_list: true,
            show_admin_host_list: true,
            is_global: 0
          },
          configForm: {},
          editId: null,
          rules: {
            field_name: [
              { required: true, message: lang.required, type: "error" },
            ],
            field_type: [
              { required: true, message: lang.required, type: "error" },
            ],
            field_option: [
              { required: true, message: lang.required, type: "error" },
            ],
          },
          pageView: {
            show_order_page: lang.client_custom_label26,
            show_order_detail: lang.client_custom_label27,
            show_client_host_detail: lang.client_custom_label28,
            show_admin_host_detail: lang.client_custom_label29,
            show_client_host_list: lang.client_custom_label34,
            show_admin_host_list: lang.client_custom_label38,
          },
          delVisible: false,
          delId: null,
          isAgent: false,
          multiliTip: "",
          linkProductVisble: false,
          linkFormData: {
            id: "",
            product_group_id: [],
          },
          saveLoading: false,
          HostNameVisble: false,
          hostList: [],
          string_allowOptions: [
            { value: "number", label: lang.product_set_text21 },
            { value: "upper", label: lang.product_set_text22 },
            { value: "lower", label: lang.product_set_text23 },
          ],
          hostNameForm: {
            custom_host_name_prefix: "",
            custom_host_name_string_allow: [],
            custom_host_name_string_length: null,
          },
          hostNameDelVisible: false,
          hostNameRules: {
            custom_host_name_prefix: [
              {
                required: true,
                message: `${lang.input}${lang.product_custom_prefix}`,
                type: "error",
              },
              { pattern: /[A-Za-z]+/, message: lang.product_custom_tip4 },
            ],
            custom_host_name_string_allow: [
              {
                required: true,
                message: `${lang.select}${lang.product_custom_string}`,
                type: "error",
              },
            ],
            custom_host_name_string_length: [
              {
                required: true,
                message: `${lang.input}${lang.product_custom_length}`,
                type: "error",
              },
            ],
          },
          confirmChange: false,
          confirmData: {
            id: '',
            is_global: '',
            remove: false
          }
        };
      },
      created () {
        this.getlist();
        this.getPlugin();
        this.getCustomProductConfig();
      },
      computed: {
        calcTrueToNumber (val) {
          return val === 1;
        },
      },
      methods: {
        changeIsGlobal (row) {
          this.confirmData.id = row.id;
          this.confirmData.is_global = row.is_global;
          this.confirmData.remove = false;
          if (row.is_global) {
            this.sureChange();
            return;
          }
          this.confirmChange = true;
        },
        async sureChange () {
          try {
            this.submitLoading = true;
            const params = { ...this.confirmData };
            params.is_global = params.is_global ? 0 : 1;
            params.remove = params.remove ? 1 : 0;
            const res = await apiChangeIsGlobal(params);
            this.$message.success(res.data.msg);
            this.confirmChange = false;
            this.getlist();
          } catch (error) {
            console.log('error', error);
            this.$message.error(error.data.msg);
            this.confirmChange = false;
          } finally {
            this.submitLoading = false;
          }
        },
        handleTabChange (val) {
          if (val === "1") {
            this.getlist();
          } else {
            this.getCustomList();
          }
        },
        getStringAllowLabel (val) {
          return this.string_allowOptions.filter(
            (item) => item.value === val
          )[0].label;
        },
        choosePro (val) {
          this.linkFormData.product_group_id = val;
        },
        linkProduct (row) {
          this.linkFormData.id = row.id;
          this.linkFormData.product_group_id = row.product_group.map(
            (item) => item.id
          );
          this.linkProductVisble = true;
        },
        linkHostName (row) {
          this.linkFormData.id = row.id;
          this.linkFormData.product_group_id = row.product_group.map(
            (item) => item.id
          );
          this.linkProductVisble = true;
        },
        saveConfig () {
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
        sureLinkPro ({ validateResult, firstError }) {
          if (validateResult === true) {
            this.submitLoading = true;
            const subApi =
              this.tab === "1"
                ? apiRelatedProductGroup
                : apiRelatedHostNameGroup;
            subApi(this.linkFormData)
              .then((res) => {
                this.$message.success(res.data.msg);
                this.linkProductVisble = false;
                this.handleTabChange(this.tab);
                this.submitLoading = false;
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
                this.submitLoading = false;
              });
          } else {
          }
        },
        getCustomProductConfig () {
          apiGetProductConfig().then((res) => {
            this.configForm = res.data.data;
          });
        },
        async getPlugin () {
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
          } catch (error) { }
        },
        async getUserDetail () {
          try {
            const res = await getProductDetail(this.id);
            const temp = res.data.data.product;
            this.isAgent = temp.mode === "sync";
            document.title =
              lang.product_list +
              "-" +
              temp.name +
              "-" +
              localStorage.getItem("back_website_name");
          } catch (error) {
            console.log(error);
          }
        },
        calcWatch (row) {
          let str = "";
          // 遍历arr
          for (let key in this.pageView) {
            if (row[key] === 1) {
              str += this.pageView[key] + " / ";
            }
          }
          str = str.slice(0, -2);
          return str;
        },
        calcTypeText (row) {
          return this.typeList.filter(
            (item) => row.field_type === item.value
          )[0].label;
        },
        onChange (row) {
          const params = { id: row.id, status: row.status };
          editclientStatus(params).then((res) => {
            this.$message.success(res.data.msg);
            this.getlist();
          });
        },
        onDragSort ({ targetIndex, newData }) {
          this.data = newData;
          dragSelfDefinedField({
            id: newData[targetIndex].id,
            prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
          }).then((res) => {
            this.$message.success(res.data.msg);
            this.getlist();
          });
        },

        handADDmaterial () {
          this.formData.is_required = false;
          this.formData.show_order_page = true;
          this.formData.show_order_detail = true;
          this.formData.show_client_host_detail = true;
          this.formData.show_admin_host_detail = true;
          this.formData.show_client_host_list = true;
          this.formData.show_admin_host_list = true;
          this.formData.field_option = "";
          this.formData.field_type = "";
          this.formData.field_name = "";
          this.formData.description = "";
          this.formData.explain = "";
          this.formData.regexpr = "";
          this.formData.is_global = 0;
          this.materialVisble = true;
        },
        getlist () {
          this.loading = true;
          getSelfDefinedField(this.params)
            .then((res) => {
              this.list = res.data.data.list;
              this.total = res.data.data.count;
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },
        getCustomList () {
          this.loading = true;
          apiGetCustomHostNames(this.params)
            .then((res) => {
              this.hostList = res.data.data.list;
              this.total = res.data.data.count;
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },
        sureDelHostName () {
          this.submitLoading = true;
          apiDeleteCustomHostName({ id: this.delId })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getCustomList();
              this.delId = null;
              this.hostNameDelVisible = false;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.submitLoading = false;
            });
        },
        sureDelUser () {
          this.submitLoading = true;
          deleteSelfDefinedField(this.delId)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getlist();
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
        editHostName (row) {
          this.editId = row.id;
          this.hostNameForm.custom_host_name_prefix =
            row.custom_host_name_prefix;
          this.hostNameForm.custom_host_name_string_allow =
            row.custom_host_name_string_allow;
          this.hostNameForm.custom_host_name_string_length =
            row.custom_host_name_string_length;
          this.HostNameVisble = true;
        },
        handAddHostName () {
          this.editId = null;
          this.hostNameForm.custom_host_name_prefix = "";
          this.hostNameForm.custom_host_name_string_allow = [];
          this.hostNameForm.custom_host_name_string_length = null;
          this.HostNameVisble = true;
        },
        edit (row) {
          if (this.isAgent) {
            return;
          }
          this.editId = row.id;
          this.formData.is_required = row.is_required === 1;
          this.formData.show_order_page = row.show_order_page === 1;
          this.formData.show_order_detail = row.show_order_detail === 1;
          this.formData.show_client_host_detail =
            row.show_client_host_detail === 1;
          this.formData.show_admin_host_detail =
            row.show_admin_host_detail === 1;
          this.formData.show_client_host_list = row.show_client_host_list === 1;
          this.formData.show_admin_host_list = row.show_admin_host_list === 1;
          this.formData.field_option = row.field_option;
          this.formData.field_type = row.field_type;
          this.formData.field_name = row.field_name;
          this.formData.description = row.description;
          this.formData.regexpr = row.regexpr;
          this.formData.explain_content = row.explain_content;
          this.formData.is_global = row.is_global;
          this.materialVisble = true;
        },
        deletesHostName (id) {
          this.delId = id;
          this.hostNameDelVisible = true;
        },
        deletes (id) {
          if (this.isAgent) {
            return;
          }
          this.delId = id;
          this.delVisible = true;
        },
        opTypeDia () {
          this.getTypeList();
          this.typeVisible = true;
        },
        hostNameDiaClose () {
          this.editId = null;
          this.$refs.hostFormDialog.reset();
          this.HostNameVisble = false;
        },
        materialDiaClose () {
          this.editId = null;
          this.$refs.userDialog.reset();
          this.materialVisble = false;
        },
        handleLength (val) {
          if (val > 50) {
            this.hostNameForm.custom_host_name_string_length = 50;
          }
          if (val < 5) {
            this.hostNameForm.custom_host_name_string_length = 5;
          }
        },
        hostNameSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = { ...this.hostNameForm, id: this.editId };
            const subApi =
              this.editId !== null
                ? apiUpdateCustomHostName
                : apiAddCustomHostName;
            subApi(params)
              .then((res) => {
                this.$message.success(res.data.msg);
                this.HostNameVisble = false;
                this.getCustomList();
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
        onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = { ...this.formData };
            params.is_required = params.is_required ? 1 : 0;
            params.show_order_page = params.show_order_page ? 1 : 0;
            params.show_order_detail = params.show_order_detail ? 1 : 0;
            params.show_client_host_detail = params.show_client_host_detail
              ? 1
              : 0;
            params.show_admin_host_detail = params.show_admin_host_detail
              ? 1
              : 0;
            params.show_client_host_list = params.show_client_host_list ? 1 : 0;
            params.show_admin_host_list = params.show_admin_host_list ? 1 : 0;
            this.submitLoading = true;
            if (this.editId !== null) {
              updateSelfDefinedField({ id: this.editId, ...params })
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.materialVisble = false;
                  this.getlist();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                })
                .finally(() => {
                  this.submitLoading = false;
                });
            } else {
              addSelfDefinedField(params)
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.materialVisble = false;
                  this.getlist();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                })
                .finally(() => {
                  this.submitLoading = false;
                });
            }
          } else {
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

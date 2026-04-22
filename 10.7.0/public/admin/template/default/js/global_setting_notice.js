(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "product-notice-setting"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
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
          columns: [
            {
              colKey: "name",
              title: lang.product_set_text111,
              width: 150,
              ellipsis: true,
              fixed: "left",
            },
            {
              colKey: "product",
              title: lang.product,
              width: 300,
              ellipsis: true,
              className: "product-name",
            },
            {
              colKey: "product-config",
              title: "title-slot-name",
              className: "product-config",
              width: 0,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 130,
              ellipsis: true,
              fixed: "right",
            },
          ],
          hideSortTips: true,
          params: {
            type: "email",
            page: 1,
            limit: 999,
            name: "",
            product_name: "",
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: {
            // 添加用户
            name: "",
            password: "",
            repassword: "",
            email: "",
            nickname: "",
            role_id: "",
          },
          rules: {
            name: [
              {
                required: true,
                message: lang.input + lang.product_set_text116,
                type: "error",
              },
            ],
            nickname: [
              {
                required: true,
                message: lang.input + lang.nickname,
                trigger: "blur",
              },
              {
                validator: (val) => val.length <= 20,
                message: lang.verify3 + 20,
                type: "warning",
              },
            ],
            product_id: [
              {
                required: true,
                message: lang.select + lang.product,
                trigger: "blur",
              },
            ],
          },
          loading: false,
          popupProps: {
            overlayStyle: (trigger) => ({width: `${trigger.offsetWidth}px`}),
          },
          submitLoading: false,
          optTip: "",
          optType: "add",
          notice_setting: [],
          notice_type: [], // 通知类型
          ruleForm: {
            id: "",
            type: "",
            name: "",
            is_default: 0,
            notice_setting: [],
            product_id: [],
          },
          productList: [],
          allPro: [],
          filterProduct: null,
          selectedRowKeys: [],
          selectProductColumns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 50,
            },
            {
              colKey: "product_group_name_first",
              title: lang.first_group,
              ellipsis: true,
            },
            {
              colKey: "product_group_name_second",
              title: lang.second_group,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.product_name,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 70,
              fixed: "right",
            },
          ],
          leftSearch: "",
          rightSearch: "",
        };
      },
      mounted() {},
      computed: {
        calcSelectedProductList() {
          let temp = this.allPro.filter((item) =>
            this.ruleForm.product_id.includes(`t-${item.id}`)
          );
          if (this.rightSearch.trim()) {
            temp = temp.filter(
              (item) => item.name.indexOf(this.rightSearch) >= 0
            );
          }
          return temp;
        },
      },
      methods: {
        changeLeftSearch() {
          if (this.leftSearch) {
            this.filterProduct = (node) => {
              const res = node.data.name.indexOf(this.leftSearch) >= 0;
              return res;
            };
          } else {
            this.filterProduct = null;
          }
        },
        selectChange(value) {
          this.selectedRowKeys = value;
        },
        btachDelProduct() {
          if (this.selectedRowKeys.length === 0) {
            return this.$message.warning(lang.product_id_empty_tip);
          }
          this.handleDelProduct(this.selectedRowKeys);
        },
        handleDelProduct(arr) {
          arr = arr.map((item) => `t-${item}`);
          this.ruleForm.product_id = this.ruleForm.product_id.filter(
            (item) => !arr.includes(item)
          );
        },
        async changeStatus(val, item, id) {
          try {
            const res = await changeRuleStatus({
              id,
              act: item.name,
              status: val,
            });
            this.$message.success(res.data.msg);
            this.getNoticeList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        changeTab() {
          this.params.name = "";
          this.params.product_name = "";
          this.getNoticeList();
        },
        // 获取列表
        async getNoticeList() {
          try {
            this.loading = true;
            const res = await getGlobalNotice(this.params);
            this.loading = false;
            const {list, count, notice_type, notice_setting} = res.data.data;
            list.forEach((item) => {
              item.allName = "";
              item.product.forEach((sub) => {
                if (this.params.product_name.trim()) {
                  item.allName +=
                    sub.name.replace(
                      new RegExp(this.params.product_name, "g"),
                      `<span class="high-light-text">${this.params.product_name}</span>`
                    ) + "、";
                } else {
                  item.allName += sub.name + "、";
                }
              });
              item.allName = item.allName.slice(0, -1);
            });
            this.data = list;
            this.notice_type = notice_type;
            this.notice_setting = notice_setting;
            this.tab = this.notice_type[0]?.type;
            this.total = count;
            this.columns[2].width = (this.notice_setting.length + 2) * 140;
          } catch (error) {
            this.loading = false;
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getNoticeList();
        },
        // 排序
        sortChange(val) {
          if (val === undefined) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getNoticeList();
        },
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.params.page = 1;
          this.getNoticeList();
        },
        close() {
          this.visible = false;
          this.$nextTick(() => {
            this.$refs.ruleDialog && this.$refs.ruleDialog.reset();
          });
        },
        addRule() {
          this.optType = "add";
          this.visible = true;
          this.optTip = lang.product_set_text114;
          this.$refs.ruleDialog && this.$refs.ruleDialog.reset();
          this.selectedRowKeys = [];
        },
        updateRule(row) {
          this.optType = "edit";
          this.visible = true;
          this.optTip = lang.product_set_text115;
          this.ruleForm.id = row.id;
          this.ruleForm.name = row.name;
          this.ruleForm.is_default = row.is_default;
          this.ruleForm.notice_setting = Object.keys(row.notice_setting).filter(
            (item) => row.notice_setting[item] === 1
          );
          this.ruleForm.product_id = row.product.map((item) => `t-${item.id}`);
          this.selectedRowKeys = [];
        },
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = {...this.ruleForm};
              params.type = this.params.type;
              params.notice_setting = this.notice_setting.reduce((all, cur) => {
                all[cur.name] = params.notice_setting.includes(cur.name)
                  ? 1
                  : 0;
                return all;
              }, {});
              params.product_id = params.product_id
                .map((item) => Number(item.replace("t-", "")))
                .filter((item) => item);
              this.submitLoading = true;
              if (this.optType === "add") {
                delete params.id;
              }
              const res = await addAndEditGlobalNotice(this.optType, params);
              this.$message.success(res.data.msg);
              this.getNoticeList();
              this.visible = false;
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },

        closeDialog() {
          this.statusVisble = false;
        },
        // 删除规则组
        deleteRule(row) {
          this.delVisible = true;
          this.delId = row.id;
        },
        async sureDel() {
          try {
            this.submitLoading = true;
            const res = await delGlobalNotice({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.delVisible = false;
            this.getNoticeList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup();
            return res.data.data.list;
          } catch (error) {}
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup();
            return res.data.data.list;
          } catch (error) {}
        },
        // 获取商品列表
        async getProList() {
          try {
            const res = await getProduct();
            this.allPro = res.data.data.list;
            return res.data.data.list;
          } catch (error) {}
        },
        // 初始化
        getProductList() {
          try {
            Promise.all([
              this.getFirPro(),
              this.getSecPro(),
              this.getProList(),
            ]).then((res) => {
              res[0].forEach((item) => {
                item.key = "f-" + item.id; // 多级Id会重复，故需要设置独一的key
                let secondArr = [];
                res[1].forEach((sItem) => {
                  if (sItem.parent_id === item.id) {
                    sItem.key = "s-" + sItem.id;
                    secondArr.push(sItem);
                  }
                });
                item.children = secondArr;
              });
              setTimeout(() => {
                res[0].forEach((item) => {
                  item.children.forEach((ele) => {
                    let temp = [];
                    res[2].forEach((e) => {
                      if (e.product_group_id_second === ele.id) {
                        e.key = "t-" + e.id;
                        temp.push(e);
                      }
                    });
                    ele.children = temp;
                  });
                });
                this.productList = res[0];
              }, 0);
            });
          } catch (error) {
            console.log(error);
          }
        },
      },
      created() {
        this.getNoticeList();
        this.getProductList();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("upstream_goods")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
        comPagination,
      },
      data() {
        return {
          // 分页相关
          params: {
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            supplier_id: "",
            mode: "",
            need_manual_sync: ""
          },
          total: 0,
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
          pageSizeOptions: [10, 20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              title: lang.belong_group,
              // width: "200",
              ellipsis: true,
              cell: "group",
              colKey: "group",
              minWidth: 150,
            },
            {
              title: lang.product_name,
              colKey: "name",
              cell: "name",
              ellipsis: true,
              minWidth: 200,
            },
            {
              title: lang.upstream_text101,
              width: "200",
              cell: "mode",
              colKey: "mode",
            },
            {
              title: lang.upstream_text107,
              width: "150",
              cell: "plan",
              colKey: "plan",
            },
            {
              title: lang.upstream_text33,
              width: "220",
              cell: "price",
              colKey: "price",
              ellipsis: true,
            },
            {
              title: lang.loss_cost_price,
              colKey: "upstream_price",
              width: "200",
            },
            {
              title: lang.showText,
              cell: "hidden",
              colKey: "hidden",
              width: "100",
            },
            {
              title: lang.order_text67,
              width: "120",
              cell: "op",
              colKey: "op",
              fixed: "right",
            },
          ],
          rules: {
            name: [
              {
                required: true,
                message: lang.input + lang.group_name,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: lang.verify3 + 100,
                type: "warning",
              },
            ],
            target_product_group_id: [
              {
                required: true,
                message: lang.select + lang.product_group,
                type: "error",
              },
            ],
            group_name: [
              {
                required: true,
                message: lang.input + lang.group_name,
                type: "error",
              },
            ],
          },
          productRules: {
            supplier_id: [
              {
                required: true,
                message: lang.select + lang.upstream_text6,
                type: "error",
              },
            ],
            upstream_product_id: [
              {
                required: true,
                message: lang.select + lang.product,
                type: "error",
              },
            ],
            name: [
              {
                required: true,
                message: lang.input + lang.product_name,
                type: "error",
              },
            ],
            profit_percent: [
              {
                required: true,
                message: lang.input + lang.upstream_text84,
                type: "error",
              },
            ],
            renew_profit_percent: [
              {
                required: true,
                message: lang.input + lang.upstream_text85,
                type: "error",
              },
            ],
            upgrade_profit_percent: [
              {
                required: true,
                message: lang.input + lang.upstream_text86,
                type: "error",
              },
            ],
            certification_method: [
              {required: true, message: lang.upstream_text49, type: "error"},
            ],
            mode: [
              {
                required: true,
                message: lang.select + lang.upstream_text101,
                type: "error",
              },
            ],
            firstId: [
              {
                required: true,
                message: lang.select + lang.first_group,
                type: "error",
              },
            ],
            product_group_id: [
              {
                required: true,
                message: lang.select + lang.second_group,
                type: "error",
              },
            ],
            profit_type: [
              {
                required: true,
                message: lang.select + lang.upstream_text74,
                type: "error",
              },
            ],
          },
          productModel: false,
          firstGroup: [],
          secondGroup: [],
          tempSecondGroup: [],
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          money: {},
          tableLayout: false,
          hover: true,
          delId: "",
          editId: "",
          loading: false,
          maxHeight: "",
          delVisible: false,
          supplier_id: "",
          goodsOption: [],
          groupModel: false,
          formData: {
            // 新建分组
            name: "",
            id: "", // 0 代表一级分组
          },
          productData: {
            // 新建分组
            id: "",
            supplier_id: "",
            mode: "only_api", // only_api sync
            upstream_product_id: "",
            name: "",
            firstId: "",
            profit_type: 0, // 0 百分比,  1 固定利润
            profit_percent: "",
            renew_profit_type: 0, // 0 百分比,  1 固定利润
            renew_profit_percent: "",
            upgrade_profit_type: 0, // 0 百分比,  1 固定利润
            upgrade_profit_percent: "",
            auto_setup: 0,
            certification: 0,
            certification_method: "",
            product_group_id: "",
            description: "",
          },
          supplierOption: [],
          methodOption: [
            {
              id: "agent",
              name: lang.upstream_text50,
            },
            {
              id: "client",
              name: lang.upstream_text51,
            },
          ],
          submitLoading: false,
          isEdit: false,
          supplierTip: "",
          syncConfirmVisible: false,
          syncProductId: "",
        };
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      computed: {
        isShowSync() {
          const curType = this.goodsOption.filter(
            (item) => item.id === this.productData.upstream_product_id
          )[0]?.mode;
          return curType !== "only_api";
        },
        calcType() {
          return this.supplierOption.filter(
            (item) => item.id === this.productData.supplier_id
          )[0]?.type;
        },
      },
      created() {
        const temp = this.getQuery(location.search);
        temp.id && (this.supplier_id = temp.id);
        this.getUpstreamList();
        this.getSupplierList();
        this.getFirPro();
        this.getSecPro();
      },
      methods: {
        // 选择商品
        chooseProduct() {
          this.productData.mode = "only_api";
          this.productData.description = this.goodsOption.filter(
            (item) => item.id === this.productData.upstream_product_id
          )[0]?.description;
        },
        changeMode(val) {
          if (val === "sync") {
            this.productData.renew_profit_percent = null;
            this.productData.upgrade_profit_percent = null;
            this.productData.profit_percent = 100;
          }
        },
        changeWay(type) {
          this.$refs.productForm.clearValidate([type]);
        },
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        supplierChange(id) {
          this.productData.upstream_product_id = "";
          this.getGoodsList(id);
        },
        // 商品列表
        getGoodsList(id) {
          supplierGoodsList(id).then((res) => {
            this.goodsOption = res.data.data.list;
          });
        },
        // 搜索框 搜索
        search() {
          this.params.page = 1;
          // 重新拉取申请列表
          this.getUpstreamList();
        },
        changeFirId(val) {
          this.tempSecondGroup = this.secondGroup.filter(
            (item) => item.parent_id === val
          );
          this.productData.product_group_id = "";
        },
        closeProduct() {
          this.productModel = false;
          this.supplierTip = "";
          setTimeout(() => {
            this.$refs.productForm.reset();
            this.productData.auto_setup = 0;
            this.productData.certification = 0;
            this.productData.sync = 0;
          }, 0);
        },
        // 新建商品
        addProduct() {
          this.productModel = true;
        },
        editGoods(row) {
          this.goodsOption = [];
          supplierStatus(row.supplier_id)
            .then((res) => {})
            .catch((err) => {
              this.supplierTip = lang.upstream_text116;
            });
          this.isEdit = true;
          this.productData.id = row.id;
          this.productData.supplier_id = row.supplier_id;
          this.productData.upstream_product_id = row.upstream_product_id;
          this.productData.name = row.name;
          this.productData.profit_percent = row.profit_percent;
          this.productData.renew_profit_percent = row.renew_profit_percent;
          this.productData.upgrade_profit_percent = row.upgrade_profit_percent;
          this.productData.auto_setup = row.auto_setup;
          this.productData.sync = row.sync || 0;
          this.productData.certification = row.certification;
          // this.productData.certification_method = row.certification_method;
          this.productData.product_group_id = row.product_group_id_second;
          this.productData.firstId = row.product_group_id_first;
          this.productData.profit_type = row.profit_type;
          this.productData.renew_profit_type = row.renew_profit_type;
          this.productData.upgrade_profit_type = row.upgrade_profit_type;
          this.productData.mode = row.mode;
          this.tempSecondGroup = this.secondGroup.filter(
            (item) => item.parent_id === this.productData.firstId
          );
          this.getGoodsList(row.supplier_id);
          this.productModel = true;
        },
        async submitProduct({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = {...this.productData};
              delete params.firstId;
              if (params.mode === "sync") {
                params.renew_profit_percent = 0;
                params.upgrade_profit_percent = 0;
                // if (params.profit_type === 1) {
                //   params.profit_percent = 0;
                // }
              }
              this.submitLoading = true;
              const res = await editUpstreamProduct(params);
              this.$message.success(res.data.msg);
              this.productModel = false;
              this.$refs.productForm.reset();
              this.productData.auto_setup = 0;
              this.productData.certification = 0;
              this.productData.sync = 0;
              this.getUpstreamList();
              this.submitLoading = false;
              // 检查是否需要手动同步
              if (res.data.data && res.data.data.need_manual_sync === 1 && this.$checkPermission('auth_upstream_downstream_upstream_product_manual_sync_resource')) {
                this.syncProductId = params.id;
                this.syncConfirmVisible = true;
              }
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 新建分组
        addGroup() {
          this.groupModel = true;
          this.formData.id = "";
        },
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = {...this.formData};
              if (params.id === "") {
                // 新建一级分组
                params.id = 0;
              }
              this.submitLoading = true;
              const res = await addGroup(params);
              this.$message.success(res.data.msg);
              this.groupModel = false;
              this.$refs.groupForm.reset();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        closeGroup() {
          this.groupModel = false;
          this.$refs.groupForm.reset();
        },
        // 获取申请列表
        async getSupplierList() {
          const res = await supplierList({page: 1, limit: 1000});
          this.supplierOption = res.data.data.list;
        },
        async onChange(row) {
          try {
            await toggleShow(row.id, row.hidden);
            this.getUpstreamList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },

        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup();
            this.firstGroup = res.data.data.list;
          } catch (error) {}
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup();
            this.secondGroup = res.data.data.list;
          } catch (error) {}
        },
        deleteHandler(id) {
          this.delId = id;
          this.delVisible = true;
        },
        async sureDel() {
          // 分删除分组和删除商品
          try {
            this.submitLoading = true;
            res = await deleteProduct(this.delId);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getUpstreamList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
            this.delVisible = false;
          }
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = "";
          this.params.page = 1;
          // 重新拉取申请列表
          this.getUpstreamList();
        },
        // 底部分页 页面跳转事件
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getUpstreamList();
        },
        // 获取申请列表
        async getUpstreamList() {
          this.loading = true;
          this.params.supplier_id = this.supplier_id;
          const res = await upstreamList(this.params);
          this.data = res.data.data.list;
          this.total = res.data.data.count;
          this.loading = false;
        },
        // 显示同步确认对话框
        showSyncConfirm(row) {
          this.syncProductId = row.id;
          this.syncConfirmVisible = true;
        },
        // 确认同步上游商品信息
        async confirmSync() {
          try {
            this.submitLoading = true;
            const res = await handleSyncResource({ id: this.syncProductId });
            this.$message.success(res.data.msg);
            this.syncConfirmVisible = false;
            this.getUpstreamList();
          } catch (error) {
            this.$message.error(error.data.msg);
          } finally {
            this.submitLoading = false;
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

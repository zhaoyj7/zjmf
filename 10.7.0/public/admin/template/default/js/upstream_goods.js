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
        comTinymce,
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
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          // 表格相关
          submitLoading: false,
          data: [],
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 30,
              disabled: ({row}) => row.host_num > 0,
            },
            {
              title: lang.belong_group,
              // width: "200",
              ellipsis: true,
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
              colKey: "mode",
            },
            {
              title: lang.upstream_text107,
              width: "150",
              colKey: "plan",
            },
            {
              title: lang.upstream_text33,
              width: "220",
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
              colKey: "hidden",
              width: "100",
            },
            {
              title: lang.order_text67,
              width: "120",
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
            certification_method: [
              {
                required: true,
                message: lang.select + lang.upstream_text38,
                type: "error",
              },
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
                message: lang.select + lang.upstream_text84,
                type: "error",
              },
            ],
            renew_profit_type: [
              {
                required: true,
                message: lang.select + lang.upstream_text85,
                type: "error",
              },
            ],
            upgrade_profit_type: [
              {
                required: true,
                message: lang.select + lang.upstream_text86,
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
          goodsId: "",
          goodsOption: [],
          isEdit: false,
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
            sync: 0,
            // certification_method: "",
            product_group_id: "",
            description: "",
            price_basis: "standard",
          },
          supplierOption: [],
          methodOption: [
            {
              id: "agent",
              name: lang.upstream_text36,
            },
            {
              id: "client",
              name: lang.upstream_text37,
            },
          ],
          supplierTip: "",
          edition: JSON.parse(localStorage.getItem("common_set"))?.edition || 0,
          addType: 0, // 0 单商品 1批量
          checkId: [],
          lossConfig: {
            upstream_intercept_new_order_notify: false,
            upstream_intercept_new_order_reject: false,
            upstream_intercept_renew_notify: false,
            upstream_intercept_renew_reject: false,
            upstream_intercept_upgrade_notify: false,
            upstream_intercept_upgrade_reject: false,
          },
          lossConfigDialog: false,
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
      mounted() {},
      computed: {
        isShowSync() {
          // 分为单商品和批量
          const temp =
            this.addType === 1
              ? this.productData.upstream_product_id || []
              : [this.productData.upstream_product_id];
          const curType = this.goodsOption
            .reduce((all, cur) => {
              if (temp.includes(cur.id)) {
                all.push(cur.mode);
              }
              return all;
            }, [])
            .filter((item) => item === "only_api");
          return !curType.includes("only_api");
        },
        calcType() {
          return this.supplierOption.filter(
            (item) => item.id === this.productData.supplier_id
          )[0]?.type;
        },
      },
      watch: {},
      created() {
        const temp = this.getQuery(location.search);
        temp.id && (this.goodsId = temp.id);
        this.getUpstreamList();
        this.getSupplierList();
        this.getFirPro();
        this.getSecPro();
        setTimeout(() => {
          temp.id && this.getUpProductDetail();
        }, 1000);
      },
      methods: {
        convertToBoolean(obj) {
          return Object.fromEntries(
            Object.entries(obj).map(([key, value]) => [key, Boolean(value)])
          );
        },
        convertToNumber(obj) {
          return Object.fromEntries(
            Object.entries(obj).map(([key, value]) => [key, value ? 1 : 0])
          );
        },
        async onSubmitLossConfig({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = this.convertToNumber(this.lossConfig);
              const res = await saveLossInterceptConfig(params);
              this.$message.success(res.data.msg);
              this.lossConfigDialog = false;
            } catch (error) {
              this.$message.error(error.data.msg);
            } finally {
              this.submitLoading = false;
            }
          } else {
          }
        },
        async handleLossTradeIntercept() {
          try {
            const res = await getLossInterceptConfig();
            this.lossConfig = this.convertToBoolean(res.data.data);
            this.lossConfigDialog = true;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        rehandleSelectChange(value) {
          this.checkId = value;
        },
        batchDelete() {
          if (this.checkId.length === 0) {
            return this.$message.error(lang.data_export_tip12);
          }
          this.isBatch = true;
          this.delVisible = true;
        },
        changeAddType(val) {
          this.$refs.productForm.reset({
            type: "initial",
            fields: ["upstream_product_id"],
          });
          if (val === 1) {
            this.productData.upstream_product_id = [];
          } else {
            this.productData.upstream_product_id = "";
          }
        },
        // 选择商品
        chooseProduct() {
          this.productData.mode = "only_api";
          this.productData.description = this.goodsOption.filter(
            (item) => item.id === this.productData.upstream_product_id
          )[0]?.description || '';

          this.$nextTick(() => {
            if (this.$refs.comTinymce && this.$refs.comTinymce.setContent) {
              this.$refs.comTinymce.setContent(this.productData.description);
            }
          });
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
        percentValidator(val) {
          if (val && val.length === 0) {
            if (this.productData.profit_type === 0) {
              return {
                result: false,
                message: `${lang.input}${lang.upstream_text18}`,
                type: "error",
              };
            } else {
              return {
                result: false,
                message: `${lang.input}${lang.fixed}${lang.upstream_text73}`,
                type: "error",
              };
            }
          }
          return {result: true, type: "success"};
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
        // 商品详情
        getUpProductDetail() {
          upstreamProductDetail(this.goodsId).then((res) => {
            this.editGoods(res.data.data.product);
          });
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
        // 手动重置表单数据到初始状态
        resetProductData() {
          // 使用 Object.assign 确保响应式更新
          Object.assign(this.productData, {
            id: "",
            supplier_id: "",
            mode: "only_api",
            upstream_product_id: "",
            name: "",
            firstId: "",
            profit_type: 0,
            profit_percent: "",
            renew_profit_type: 0,
            renew_profit_percent: "",
            upgrade_profit_type: 0,
            upgrade_profit_percent: "",
            auto_setup: 0,
            certification: 0,
            sync: 0,
            product_group_id: "",
            description: "",
            price_basis: "standard",
          });
          // 重置其他相关状态
          this.addType = 0;
          this.tempSecondGroup = [];
          this.goodsOption = [];
          this.supplierTip = "";
        },
        closeProduct() {
          this.productModel = false;
          // 手动重置所有数据
          this.resetProductData();
          this.productModel = false;
          // 使用 nextTick 确保 DOM 更新后再清理表单状态
          this.$nextTick(() => {
            this.$refs.productForm && this.$refs.productForm.clearValidate();
            this.$refs.comTinymce && this.$refs.comTinymce.setContent("");
          });
        },
        // 新建商品
        addProduct() {
          this.isEdit = false;
          // 先重置数据
          this.resetProductData();
          // 延迟打开对话框，确保数据已完全重置
          this.$nextTick(() => {
            this.productModel = true;
            this.$nextTick(() => {
              if (this.$refs.productForm) {
                this.$refs.productForm.reset();
                this.$refs.productForm.clearValidate();
              }
              if (this.$refs.comTinymce) {
                this.$refs.comTinymce.setContent("");
              }
            });
          });
        },
        supplierChange(id) {
          this.productData.upstream_product_id = "";
          this.supplierTip = "";
          this.goodsOption = [];
          // 先获取供应商状态
          supplierStatus(id)
            .then((res) => {
              this.getGoodsList(id);
            })
            .catch((err) => {
              this.supplierTip = lang.upstream_text116;
            });
        },
        editGoods(row) {
          this.isEdit = true;
          this.goodsOption = [];
          this.productModel = true;
          this.addType = 0;
          
          supplierStatus(row.supplier_id)
            .then((res) => {})
            .catch((err) => {
              this.supplierTip = lang.upstream_text116;
            });
          this.getGoodsList(row.supplier_id);
          
          this.$nextTick(() => {
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
            this.productData.description = row.description;
            this.productData.price_basis = row.price_basis;
            this.productData.profit_type = row.profit_type;
            this.productData.renew_profit_type = row.renew_profit_type;
            this.productData.upgrade_profit_type = row.upgrade_profit_type;
            this.productData.mode = row.mode;
            this.tempSecondGroup = this.secondGroup.filter(
              (item) => item.parent_id === this.productData.firstId
            );
            
            // 使用 setTimeout 确保 Dialog 和 TinyMCE 完全渲染
            setTimeout(() => {
              if (this.$refs.comTinymce && this.$refs.comTinymce.setContent) {
                this.$refs.comTinymce.setContent(row.description);
              }
            }, 300);
          });
        },
        async submitProduct({validateResult, firstError}) {
          this.productData.description = this.$refs.comTinymce.getContent();
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
              if (this.addType === 0) {
                const res = this.isEdit
                  ? await editUpstreamProduct(params)
                  : await addUpstreamProduct(params);
                this.$message.success(res.data.msg);
                this.productModel = false;
                // 手动重置数据
                this.resetProductData();
                this.getUpstreamList();
                this.submitLoading = false;
                
                // 检查是否需要手动同步
                if (res.data.data && res.data.data.need_manual_sync === 1 && this.$checkPermission('auth_upstream_downstream_upstream_product_manual_sync_resource')) {
                  this.syncProductId = params.id;
                  this.syncConfirmVisible = true;
                }
                
                if (params.mode === "sync" && !this.isEdit) {
                  window.open(`product_detail.htm?id=${res.data.data.id}`);
                }
              } else {
                // 批量添加
                Promise.allSettled(
                  this.productData.upstream_product_id.map((item) =>
                    addUpstreamProduct({
                      ...params,
                      upstream_product_id: item,
                      name: this.goodsOption.filter((pro) => pro.id === item)[0]
                        .name,
                    })
                  )
                )
                  .then((results) => {
                    const success = results
                      .filter((item) => item.status === "fulfilled")
                      .map((item) => item.value.data.msg);
                    if (
                      success.length > 0 &&
                      success.length ===
                        this.productData.upstream_product_id.length
                    ) {
                      this.$message.success(success[0]);
                      this.productModel = false;
                      // 手动重置数据
                      this.resetProductData();
                      this.getUpstreamList();
                    }
                    // 错误
                    const error = results
                      .filter((item) => item.status === "rejected")
                      .map((item) => item.reason);
                    if (error.length > 0) {
                      let errorMsg = "";
                      error.forEach((pro) => {
                        const name = JSON.parse(pro.config.data).name;
                        errorMsg += name + ": " + pro.data.msg + "\n";
                      });
                      this.$message.error({
                        content: errorMsg,
                        duration: 5000,
                        className: "batch-error-message",
                      });
                    }
                  })
                  .finally(() => {
                    this.submitLoading = false;
                  });
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
              const res = await addGroup(params);
              this.$message.success(res.data.msg);
              this.groupModel = false;
              this.$refs.groupForm.reset();
              this.getFirPro();
              this.getSecPro();
            } catch (error) {
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
          this.isBatch = false;
          this.delId = id;
          this.delVisible = true;
        },
        async sureDel() {
          // 分删除分组和删除商品
          try {
            this.submitLoading = true;
            let apiFun = deleteProduct;
            let params = this.delId;
            if (this.isBatch) {
              apiFun = batchDeleteProduct;
              params = {
                id: this.checkId,
              };
            }
            res = await apiFun(params);
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
          const res = await upstreamList(this.params);
          this.data = res.data.data.list;
          this.total = res.data.data.count;
          this.loading = false;
          this.checkId = [];
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

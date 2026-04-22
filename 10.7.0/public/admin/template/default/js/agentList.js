(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("agent-list")[0];
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
          },
          submitLoading: false,
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              title: lang.upstream_text28,
              colKey: "name",
              ellipsis: true,
            },
            {
              title: "CPU",
              cell: "cpu",
              width: 150,
              ellipsis: true,
            },
            {
              title: lang.upstream_text29,
              cell: "memory",
              width: 150,
              ellipsis: true,
            },
            {
              title: lang.upstream_text30,
              cell: "disk",
              width: 150,
              ellipsis: true,
            },
            {
              title: lang.upstream_text31,
              cell: "bandwidt",
              width: 150,
              ellipsis: true,
            },
            {
              title: lang.upstream_text32,
              cell: "flow",
              width: 200,
              ellipsis: true,
            },
            {
              title: lang.upstream_text33,
              cell: "price",
              width: 180,
            },
            {
              title: lang.upstream_text34,
              colKey: "description",
              width: 250,
              ellipsis: true,
            },
            {
              title: lang.upstream_text35,
              cell: "op",
              fixed: "right",
              width: 90,
            },
          ],
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          tableLayout: false,
          hover: true,
          loading: false,
          productModel: false,
          firstGroup: [],
          secondGroup: [],
          curObj: {},
          tempSecondGroup: [],
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
          productData: {
            // 新建分组
            id: "",
            token: "",
            secret: "",
            name: "",
            username: "",
            firstId: "",
            profit_percent: "",
            auto_setup: 0,
            description: "",
            certification: 0,
            certification_method: "",
            product_group_id: "",
          },
          productRules: {
            username: [
              {
                required: true,
                message: lang.input + lang.upstream_text10,
                type: "error",
              },
            ],
            token: [
              {
                required: true,
                message: lang.input + lang.upstream_text14,
                type: "error",
              },
            ],
            secret: [
              {
                required: true,
                message: lang.input + lang.upstream_text16,
                type: "error",
              },
            ],
            name: [
              {
                required: true,
                message: lang.input + lang.upstream_text28,
                type: "error",
              },
            ],
            profit_percent: [
              {
                required: true,
                message: lang.input + lang.upstream_text18,
                type: "error",
              },
              {
                pattern:
                  /^([1-9]\d*(\.\d{1,2})?|([0](\.([0][1-9]|[1-9]\d{0,1}))))$/,
                message: lang.upstream_text39,
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
          },

          isEdit: false,
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
          upstreamModel: false,
          upstreamRules: {
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
          upstreamData: {
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
          goodsOption: [],
          edition: JSON.parse(localStorage.getItem("common_set"))?.edition || 0,
          supplierTip: "",
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
            (item) => item.id === this.upstreamData.upstream_product_id
          )[0]?.mode;
          return curType !== "only_api";
        },
        calcType() {
          return this.supplierOption.filter(
            (item) => item.id === this.upstreamData.supplier_id
          )[0]?.type;
        },
      },
      created() {
        this.getOrderList();
        this.getFirPro();
        this.getSecPro();
        // this.getSupplierList();
      },
      methods: {
        // 选择商品
        chooseProduct() {
          this.upstreamData.mode = "only_api";
          this.upstreamData.description = this.goodsOption.filter(
            (item) => item.id === this.upstreamData.upstream_product_id
          )[0]?.description;
        },
        changeMode(val) {
          if (val === "sync") {
            this.upstreamData.renew_profit_percent = null;
            this.upstreamData.upgrade_profit_percent = null;
            this.upstreamData.profit_percent = 100;
          }
        },
        changeWay(type) {
          this.$refs.upstreamForm.clearValidate([type]);
        },
        changeFirId(val) {
          this.tempSecondGroup = this.secondGroup.filter(
            (item) => item.parent_id === val
          );
          this.upstreamData.product_group_id = "";
        },
        supplierChange(id) {
          this.upstreamData.upstream_product_id = "";
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
        // 商品列表
        getGoodsList(id) {
          supplierGoodsList(id).then((res) => {
            this.goodsOption = res.data.data.list;
          });
        },
        closeUpstream() {
          this.upstreamModel = false;
          this.supplierTip = "";
          setTimeout(() => {
            this.$refs.upstreamForm.reset();
            this.upstreamData.auto_setup = 0;
            this.upstreamData.certification = 0;
            this.upstreamData.sync = 0;
            this.upstreamData.price_basis = "standard";
          }, 0);
        },
        closeProduct() {
          this.productModel = false;
          this.$refs.productForm.reset();
        },
        changeFirId(val) {
          this.tempSecondGroup = this.secondGroup.filter(
            (item) => item.parent_id === val
          );
          this.productData.product_group_id = "";
        },
        editGoods(row) {
          this.curObj = row;
          this.productData.token = row.supplier.token;
          this.productData.secret = row.supplier.secret;
          this.productData.username = row.supplier.username;
          this.productData.id = row.id;
          this.upstreamData.upstream_product_id = row.upstream_product_id;
          this.$refs.productForm && this.$refs.productForm.reset();
          this.productModel = true;
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
        // 搜索框 搜索
        search() {
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        async submitProduct({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = {...this.productData};
              delete params.firstId;
              this.submitLoading = true;
              const res = await recomProduct(params);
              this.getSupplierList();
              this.submitLoading = false;
              this.productModel = false;
              this.$refs.productForm.reset();
              this.upstreamData.supplier_id = Number(res.data.data.supplier_id);
              supplierStatus(this.upstreamData.supplier_id)
                .then(() => {
                  this.getGoodsList(this.upstreamData.supplier_id);
                })
                .catch(() => {
                  this.upstreamData.upstream_product_id = "";
                  this.supplierTip = lang.upstream_text116;
                });
              this.upstreamData.profit_type = 0;
              this.upstreamData.certification = 0;
              this.upstreamModel = true;
              // this.getOrderList();
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 获取申请列表
        async getSupplierList() {
          const res = await supplierList({page: 1, limit: 1000});
          this.supplierOption = res.data.data.list;
        },
        async submitUpstream({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = {...this.upstreamData};
              delete params.firstId;
              if (params.mode === "sync") {
                params.renew_profit_percent = 0;
                params.upgrade_profit_percent = 0;
                // if (params.profit_type === 1) {
                //   params.profit_percent = 0;
                // }
              }
              this.submitLoading = true;
              const res = this.isEdit
                ? await editUpstreamProduct(params)
                : await addUpstreamProduct(params);
              this.$message.success(res.data.msg);
              this.upstreamModel = false;
              this.$refs.upstreamForm.reset();
              this.upstreamData.auto_setup = 0;
              this.upstreamData.certification = 0;
              this.upstreamData.sync = 0;
              // this.getUpstreamList();
              window.open(`upstream_goods.htm`);
              this.submitLoading = false;
              if (params.mode === "sync" && !this.isEdit) {
                window.open(`product_detail.htm?id=${res.data.data.id}`);
              }
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = "";
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        // 底部分页 页面跳转事件
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getOrderList();
        },
        // 获取申请列表
        async getOrderList() {
          this.loading = true;
          const res = await recomProList(this.params);
          this.data = res.data.data.list;
          this.total = res.data.data.count;
          this.loading = false;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

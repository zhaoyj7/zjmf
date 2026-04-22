/* 用户信息-订单管理 */
(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        comPagination,
        comTreeSelect,
        comViewFiled,
        safeConfirm,
        loginByUser,
      },
      data() {
        return {
          id: "",
          website_url: "",
          rootRul: url,
          submitLoading: false,
          exportLoading: false,
          data: [],
          tableLayout: true,
          bordered: true,
          visible: false,
          delVisible: false,
          priceModel: false,
          hover: true,
          fullLoading: false,
          exportVisible: false,
          currency_prefix:
            (JSON.parse(localStorage.getItem("common_set")) || {})
              .currency_prefix || "¥",
          currency_suffix:
            (JSON.parse(localStorage.getItem("common_set")) || {})
              .currency_suffix || "元",
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 30,
            },
            {
              colKey: "op",
              title: lang.operation,
            },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            type: "",
            gateway: [],
            status: "",
            amount: "",
            username: "",
            product_ids: [],
            client_id: "",
            start_pay_time: "",
            end_pay_time: "",
          },
          total: 0,
          page_total_amount: 0,
          total_amount: 0,
          father_client_id: "",
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          delId: "",
          expandIcon: true,
          delete_host: false, // 是否删除产品:0否1是
          // 变更价格
          formData: {
            id: "",
            amount: "",
            description: "",
          },
          rules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.money,
                type: "error",
              },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/,
                message: lang.verify10,
                type: "warning",
              },
              {
                validator: (val) => val * 1 !== 0,
                message: lang.verify10,
                type: "warning",
              },
            ],
            description: [
              {
                required: true,
                message: lang.input + lang.description,
                type: "error",
              },
              {
                validator: (val) => val.length <= 1000,
                message: lang.verify3 + 1000,
                type: "warning",
              },
            ],
            review_fail_reason: [
              {
                required: true,
                message: lang.input + lang.order_fail_reason,
                type: "error",
              },
            ],
            transaction_number: [
              {
                required: true,
                message: lang.input + lang.flow_number,
                type: "error",
              },
              {
                pattern: /^[A-Za-z0-9]+$/,
                message: lang.verify9,
                type: "warning",
              },
            ],
          },
          orderNum: 0,
          signForm: {
            amount: 0,
            credit: 0,
          },
          addonArr: [],
          hasExport: false,
          payVisible: false,
          maxHeight: "",
          use_credit: true,
          curInfo: {},
          optType: "", // order,sub
          isAdvance: false,
          orderStatus: [
            {value: "Unpaid", label: lang.Unpaid},
            {value: "Paid", label: lang.Paid},
            {value: "Cancelled", label: lang.Cancelled},
            {value: "Refunded", label: lang.refunded},
            {value: "WaitUpload", label: lang.order_wait_upload},
            {value: "WaitReview", label: lang.order_wait_review},
            {value: "ReviewFail", label: lang.order_review_fail},
          ],
          orderTypes: [
            {value: "new", label: lang.new},
            {value: "renew", label: `${lang.renew}${lang.order}`},
            {value: "upgrade", label: lang.upgrade},
            {value: "artificial", label: lang.artificial},
            {value: "recharge", label: lang.recharge},
            {value: "on_demand", label: lang.on_demand},
            {value: "change_billing_cycle", label: lang.change_billing_cycle},
          ],
          payWays: [],
          range: [],
          range2: [],
          /* 批量 */
          checkId: [],
          isBatch: false,
          deleteTit: "",
          hasCredit: false,
          recycleConfig: {
            order_recycle_bin: "",
            order_recycle_bin_save_days: "",
          },
          recycleVisble: false,
          searchType: "",
          typeOption: [
            {value: "", label: lang.auth_all},
            {value: "order_id", label: "ID"},
            //  { value: "username", label: lang.username },
            {value: "email", label: lang.email},
            {value: "phone", label: lang.phone},
            {value: "product_id", label: lang.product_name},
            {value: "sale", label: lang.sale},
          ],
          viewFiledNum: 0,
          password_field: [],
          defaultId: "",
          admin_view_list: [],
          data_range_switch: 0,
          /* 审核 */
          curItem: {},
          imgList: [],
          reviewDialog: false,
          reviewForm: {
            id: "",
            pass: 0,
            review_fail_reason: "",
            transaction_number: "",
          },
          proofTitle: "",
          proofDialog: false,
          proofForm: {
            id: "",
            voucher: [],
          },
          isLook: false,
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          uploadUrl: str + "v1/upload",
          preImg: "",
          viewer: null,
          admin_operate_password: "",
          customField: [],
          hasSale: false,
          allSales: [],
          curSaleId: "",
        };
      },
      created() {
        this.id = this.getQuery("id");
        this.getSystemOption();
        /* 全局搜索 */
        const searchType = this.getQuery("type") || "";
        const keywords = this.getQuery("keywords") || "";
        if (searchType === "status") {
          this.params.status = keywords;
        } else if (searchType === "type") {
          this.params.type = keywords;
        } else if (searchType === "product_id") {
          this.params.product_ids = keywords ? [keywords * 1] : [];
        } else if (searchType === "amount") {
          this.params.amount = keywords;
        } else if (searchType === "pay_time") {
          this.range.push(keywords, keywords);
          this.params.start_time =
            new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
          this.params.end_time =
            new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
        } else {
          this.params.keywords = keywords;
          this.params.product_ids = [];
        }
        if (
          searchType === "pay_time" ||
          searchType === "status" ||
          searchType === "amount" ||
          searchType === "type" ||
          searchType === "username"
        ) {
          this.searchType = "";
          this.isAdvance = true;
        } else {
          this.searchType = searchType;
        }
        if (this.getQuery("isIndex")) {
          this.params.gateway = ["UserCustom"];
          this.params.status = "WaitReview";
          this.isAdvance = true;
        }
        this.params.start_time = this.getQuery("start_time") || "";
        this.params.end_time = this.getQuery("end_time") || "";

        /* 全局搜索 end */
        if (this.params.start_time && this.params.end_time) {
          this.isAdvance = true;
          const start = new Date(this.params.start_time * 1000);
          const end = new Date(this.params.end_time * 1000);
          // 2024-01-01
          this.range = [
            `${start.getFullYear()}-${
              start.getMonth() + 1 < 10
                ? "0" + (start.getMonth() + 1)
                : start.getMonth() + 1
            }-${
              start.getDate() < 10 ? "0" + start.getDate() : start.getDate()
            }`,
            `${end.getFullYear()}-${
              end.getMonth() + 1 < 10
                ? "0" + (end.getMonth() + 1)
                : end.getMonth() + 1
            }-${end.getDate() < 10 ? "0" + end.getDate() : end.getDate()}`,
          ];
        }
        this.getClientList();
        this.getPayWay();
        this.getRecycleSetting();
      },
      computed: {
        calcOrderIcon() {
          return (type) => {
            if (type === "on_demand") {
              return "renew";
            } else if (type === "change_billing_cycle") {
              return "upgrade";
            } else {
              return type;
            }
          };
        },
        calcTypeSelect() {
          if (this.hasSale) {
            return this.typeOption;
          } else {
            return this.typeOption.filter((item) => item.value !== "sale");
          }
        },
        calcDevloper() {
          return (type) => {
            switch (type) {
              case 1:
                return lang.author;
              case 2:
                return lang.client_service;
              case 3:
                return lang.author_service;
            }
          };
        },
        calcList() {
          if (this.customField.length > 0) {
            return this.data.map((item) => {
              this.customField.forEach((el) => {
                if (item.hasOwnProperty(el)) {
                  item[el] = item[el] || "--";
                }
              });
              return item;
            });
          } else {
            return this.data;
          }
        },
        calcImageList() {
          return this.proofForm.voucher.map((item) => item.url);
        },
      },
      mounted() {
        this.initViewer();
        this.getPlugin();
      },
      methods: {
        // 获取后台配置的路径
        async getSystemOption() {
          try {
            const res = await getSystemOpt();
            this.website_url =
              res.data.data.clientarea_url || res.data.data.website_url;
          } catch (error) {}
        },
        async getAllSaleList() {
          try {
            const res = await getAllSales();
            this.allSales = res.data.data.list;
          } catch (error) {
            this.$message.error(res.data.msg);
          }
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            this.addonArr = res.data.data.list.map((item) => item.name);
            this.hasExport = this.addonArr.includes("ExportExcel");
            this.addonArr.includes("IdcsmartSale") && this.getAllSaleList();
            this.addonArr.includes("ClientCustomField") &&
              this.getCustomField();
          } catch (error) {}
        },
        async getCustomField() {
          try {
            const res = await getClientCustomField({
              page: 1,
              limit: 1000,
            });
            const temp = res.data.data.list
              .filter((item) => item.status === 1)
              .reduce((all, cur) => {
                all.push({
                  value: `addon_client_custom_field_${cur.id}`,
                  label: cur.name,
                });
                return all;
              }, []);
            this.typeOption.push(...temp);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        handelDownload() {
          this.exportLoading = true;
          const params = JSON.parse(JSON.stringify(this.params));
          if (this.range.length > 0) {
            params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            params.end_time =
              new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
          } else {
            params.start_time = "";
            params.end_time = "";
          }

          if (this.range2.length > 0) {
            params.start_pay_time =
              new Date(this.range2[0].replace(/-/g, "/")).getTime() / 1000 ||
              "";
            params.end_pay_time =
              new Date(this.range2[1].replace(/-/g, "/")).getTime() / 1000 ||
              "";
          } else {
            params.start_pay_time = "";
            params.end_pay_time = "";
          }

          if (this.searchType && this.searchType !== "product_id") {
            params[this.searchType] = params.keywords;
            params.keywords = "";
          }
          apiExportOrder(params)
            .then((res) => {
              exportExcelFun(res).finally(() => {
                this.exportLoading = false;
                this.exportVisible = false;
              });
            })
            .catch((err) => {
              this.exportLoading = false;
              this.$message.error(err.data.msg);
            });
        },

        /* 视图 */
        // 本地存储列宽
        resizeChange({columnsWidth}) {
          const temp = this.columns.map((item) => {
            item.width = columnsWidth[item.colKey];
            return item;
          });
          this.columns = temp;
          localStorage.setItem("orderColumnsWidth", JSON.stringify(temp));
        },
        chooseView(id) {
          this.$refs.customFiled.chooseView(id);
        },
        handleEditView() {
          this.$refs.customFiled.editHandler();
        },
        changeField({
          view_id,
          backColumns,
          customField,
          isInit,
          len,
          password_field,
          defaultId,
          admin_view_list,
          data_range_switch,
          select_field,
        }) {
          let tempColumns = [
            {
              colKey: "row-select",
              type: "multiple",
              width: 30,
            },
            ...backColumns,
            {
              colKey: "op",
              title: lang.operation,
              width: 150,
              fixed: "right",
            },
          ];
          // this.columns = tempColumns;
          const temp = [
            "email",
            "address",
            "client_level",
            "language",
            "notes",
            "country",
          ];
          customField.push(...temp);
          this.customField = customField;
          // 判断本地是否有列宽设置
          const orderColumnsWidth = JSON.parse(
            localStorage.getItem("orderColumnsWidth")
          );
          if (orderColumnsWidth) {
            tempColumns = tempColumns.map((item) => {
              item.width = (orderColumnsWidth || []).filter(
                (el) => el.colKey === item.colKey
              )[0]?.width;
              if (item.colKey === 'op' && item.width < 175) {
                item.width = 175
              }
              return item;
            });
          }
          this.columns = tempColumns;
          this.password_field = password_field;
          this.viewFiledNum = len;
          this.params.view_id = view_id;
          this.defaultId = defaultId;
          this.admin_view_list = admin_view_list;
          this.data_range_switch = data_range_switch;
          this.hasSale = select_field.includes("sale");
          if (isInit) {
            this.getClientList();
          }
        },
        /* 视图 end */
        /* 线下转账 */
        initViewer() {
          this.viewer = new Viewer(document.getElementById("viewer"), {
            button: true,
            inline: false,
            zoomable: true,
            title: true,
            tooltip: true,
            minZoomRatio: 0.5,
            maxZoomRatio: 100,
            movable: true,
            interval: 2000,
            navbar: true,
            loading: true,
          });
        },
        // 附件下载
        clickFile(item) {
          const name = item.name;
          const url = item.url || item.response.data.image_url;
          const type = name.substring(name.lastIndexOf(".") + 1);
          if (
            [
              "png",
              "jpg",
              "jepg",
              "bmp",
              "webp",
              "PNG",
              "JPG",
              "JEPG",
              "BMP",
              "WEBP",
            ].includes(type)
          ) {
            this.preImg = url;
            this.viewer.show();
          } else if (type === "pdf" || type === "PDF") {
            this.prewPDF(url, name);
          } else {
            this.downFile(url, name);
          }
        },
        downFile(url, name) {
          const downloadElement = document.createElement("a");
          downloadElement.href = url;
          downloadElement.download = name; // 下载后文件名
          document.body.appendChild(downloadElement);
          downloadElement.click(); // 点击下载
        },
        prewPDF(url, name) {
          const xhr = new XMLHttpRequest();
          xhr.open("GET", url, true);
          xhr.responseType = "blob";
          xhr.onload = () => {
            if (xhr.status === 200) {
              const blob = new Blob([xhr.response], {type: xhr.response.type});
              const link = document.createElement("a");
              link.target = "_blank";
              link.href = URL.createObjectURL(blob);
              link.click();
            } else {
              console.error("Failed to fetch PDF. Status:", xhr.status);
              this.downFile(url, name);
            }
          };
          xhr.onerror = () => {
            this.downFile(url, name);
            console.error("Network error while fetching PDF.");
          };
          xhr.send();
        },

        changeProofType() {
          this.reviewForm.review_fail_reason = "";
          this.reviewForm.transaction_number = "";
          setTimeout(() => {
            this.$refs.proof.clearValidate();
          }, 0);
        },
        // 审核
        handleReview(item) {
          this.isLook = false;
          this.curItem = item;
          this.reviewDialog = true;
          this.reviewForm.id = item.id;
          this.reviewForm.pass = 0;
          this.reviewForm.review_fail_reason = "";
          this.reviewForm.transaction_number = "";
          this.proofTitle = `${lang.order_id}：${this.curItem.id} ${lang.order_audit}`;
          this.imgList = this.curItem.voucher.map((item) => item.url);
          this.$refs.proof && this.$refs.proof.clearValidate();
        },
        async submitReview({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await reviewOrder(this.reviewForm);
              this.submitLoading = false;
              this.$message.success(res.data.msg);
              this.reviewDialog = false;
              this.getClientList();
            } catch (error) {
              console.log("error", error);
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async submitProof({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              if (this.proofForm.voucher.length === 0) {
                return this.$message.error(lang.order_upload_tip1);
              }
              const params = {
                id: this.curItem.id,
                voucher: this.proofForm.voucher.map((item) => {
                  return item.save_name || item.response.save_name;
                }),
              };
              this.submitLoading = true;
              const res = await uploadProof(params);
              this.submitLoading = false;
              this.$message.success(res.data.msg);
              this.proofDialog = false;
              this.getClientList();
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 查看凭证
        lookProof(item) {
          this.isLook = true;
          this.curItem = item;
          this.reviewDialog = true;
          this.proofTitle = lang.look_proof;
          this.imgList = item.voucher.map((item) => item.url);
        },
        beforeUpload(file) {
          const type = file.name.substring(file.name.lastIndexOf(".") + 1);
          const typeArr = [
            "png",
            "jpg",
            "jepg",
            "bmp",
            "webp",
            "PNG",
            "JPG",
            "JEPG",
            "BMP",
            "WEBP",
            "pdf",
            "PDF",
          ];
          if (!typeArr.includes(type)) {
            this.$message.error(lang.order_upload_tip);
            return false;
          }
          return true;
        },
        // 上传凭证
        handleUpload(row) {
          item = JSON.parse(JSON.stringify(row));
          this.curItem = item;
          this.proofForm.id = item.id;
          let str = `${lang.order_id}：${item.id} `;
          if (item.status === "WaitUpload" || item.status === "WaitReview") {
            if (item.voucher.length === 0) {
              str += lang.upload_proof;
            } else {
              str += lang.reupload;
            }
          } else {
            str += lang.reupload;
          }
          if (item.status === "Paid" && item.voucher.length > 0) {
            str += lang.look_proof;
          }
          this.proofTitle = str;
          this.proofDialog = true;
          if (item.voucher.length > 0) {
            this.imgList = item.voucher.map((item) => item.url);
            this.proofForm.voucher = item.voucher;
          }
        },
        formatResponse(res) {
          if (res.status !== 200) {
            this.$nextTick(() => {
              this.files = [];
            });
            return this.$message.error(res.msg);
          }
          return {save_name: res.data.save_name, url: res.data.image_url};
        },
        delItem(index) {
          this.imgList.splice(index, 1);
          this.proofForm.voucher.splice(index, 1);
          event.stopPropagation();
        },
        /* 线下转账 end */
        showDetails() {
          const clientAuth = [
            "auth_user_detail_personal_information_view",
            "auth_user_detail_host_info_view",
            "auth_user_detail_order_view",
            "auth_user_detail_transaction_view",
            "auth_user_detail_transaction_view",
            "auth_user_detail_operation_log",
            "auth_user_detail_notification_log_sms_notification",
            "auth_user_detail_notification_log_email_notification",
            "auth_user_detail_ticket_view",
            "auth_user_detail_info_record_view",
          ];
          return clientAuth.some((item) => this.$checkPermission(item));
        },
        changeType() {
          this.params.keywords = "";
          this.curSaleId = "";
        },
        choosePro(id) {
          this.params.product_ids = id;
        },
        getQuery(name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        /* 回收站 */
        async getRecycleSetting() {
          try {
            const res = await getRecycleConfig();
            this.recycleConfig = res.data.data;
          } catch (error) {}
        },
        openRecyle() {
          this.recycleVisble = true;
        },
        async handleRecyle() {
          try {
            this.submitLoading = true;
            const res = await openRecycleConfig();
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.recycleVisble = false;
            this.getRecycleSetting();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        goRecycle() {
          location.href = "order_recycle_bin.htm";
        },
        /* 回收站 end */
        async getAddonList() {
          try {
            const res = await getAddon();
            this.hasCredit =
              res.data.data.list.filter((item) => item.name === "CreditLimit")
                .length > 0;
            if (this.hasCredit) {
              this.payWays.unshift({
                name: "credit_limit",
                title: lang.credit_pay,
              });
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* 批量删除 */
        batchDel() {
          this.renewForm = [];
          this.renewList = [];
          if (this.checkId.length === 0) {
            return this.$message.error(`${lang.select}${lang.order}`);
          }
          this.isBatch = true;
          this.delVisible = true;
          this.deleteTit = `${lang.batch_dele}${lang.order}`;
        },
        rehandleSelectChange(value, {selectedRowData}) {
          this.checkId = value;
          this.selectedRowKeys = selectedRowData;
        },
        /* 批量删除 end */
        changeAdvance() {
          this.isAdvance = !this.isAdvance;
          this.params.type = "";
          this.params.gateway = [];
          // this.params.status = ''
          this.params.amount = "";
          this.range = [];
          this.range2 = [];
        },
        async getPayWay() {
          try {
            const res = await getPayList();
            this.payWays = res.data.data.list;

            this.getAddonList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        lookDetail(row) {
          sessionStorage.currentOrderUrl = window.location.href;
          location.href = `order_details.htm?id=${row.id}`;
        },
        jumpPorduct(client_id, id) {
          location.href = `host_detail.htm?client_id=${client_id}&id=${id}`;
        },
        addOrder() {
          location.href = "create_order.htm";
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            let curField = "";
            switch (val.sortBy) {
              case "order_amount":
                curField = "amount";
                break;
              default:
                curField = val.sortBy;
            }
            this.params.orderby = curField;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },
        clearKey(type) {
          this.params[type] = "";
          this.search();
        },
        search() {
          this.params.page = 1;
          if (this.range.length > 0) {
            this.params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_time =
              new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 || "";
          } else {
            this.params.start_time = "";
            this.params.end_time = "";
          }

          if (this.range2.length > 0) {
            this.params.start_pay_time =
              new Date(this.range2[0].replace(/-/g, "/")).getTime() / 1000 ||
              "";
            this.params.end_pay_time =
              new Date(this.range2[1].replace(/-/g, "/")).getTime() / 1000 ||
              "";
          } else {
            this.params.start_pay_time = "";
            this.params.end_pay_time = "";
          }
          this.getClientList();
        },
        // 自定义图标
        treeExpandAndFoldIconRender(h, {type}) {},
        // 调整价格
        updatePrice(row, type) {
          this.optType = type;
          this.formData.id = row.id;
          this.formData.amount = "";
          this.formData.description = "";
          this.$refs.update_price && this.$refs.update_price.clearValidate();
          this.priceModel = true;
          this.curInfo = row;
          if (type === "sub") {
            this.formData = {...row};
          }
        },
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            if (this.optType === "order") {
              this.changeOrderPrice();
            } else {
              this.changeSubPrice();
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 修改订单价格
        async changeOrderPrice() {
          try {
            this.submitLoading = true;
            await updateOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getClientList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 修改子项人工价格
        async changeSubPrice() {
          try {
            this.submitLoading = true;
            await updateArtificialOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getClientList();
            this.optType = "";
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        closePrice() {
          this.priceModel = false;
          this.$refs.priceForm.reset();
        },
        // 删除订单
        delteOrder(row) {
          this.delId = row.id;
          this.delVisible = true;
          this.delete_host = false;
          this.isBatch = false;
          this.deleteTit = lang.deleteOrder;
        },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        async onConfirm(e, remember_operate_password = 0) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            // 处理批量删除
            if (this.isBatch) {
              this.batchDeleteOrder(remember_operate_password);
              return;
            }
            const params = {
              id: this.delId,
              delete_host: this.delete_host ? 1 : 0,
              admin_operate_password,
              admin_operate_methods: "onConfirm",
              remember_operate_password,
            };
            this.submitLoading = true;
            await delOrderDetail(params);
            this.$message.success(window.lang.del_success);
            this.delVisible = false;
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.getClientList();
            this.submitLoading = false;
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
        async batchDeleteOrder(remember_operate_password) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            await batchDelOrder({
              id: this.checkId,
              delete_host: this.delete_host ? 1 : 0,
              admin_operate_password,
              admin_operate_methods: "batchDeleteOrder",
              remember_operate_password,
            });
            this.$message.success(lang.del_success);
            this.delVisible = false;
            this.checkId = [];
            this.getClientList();
            this.submitLoading = false;
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
        // 标记支付
        signPay(row) {
          if (row.status === "Paid") {
            return;
          }
          this.payVisible = true;
          this.delId = row.id;
          this.signForm.amount = row.amount;
          this.signForm.credit = row.client_credit;
        },
        async sureSign() {
          try {
            const params = {
              id: this.delId,
              use_credit: this.use_credit ? 1 : 0,
            };
            this.submitLoading = true;
            const res = await signPayOrder(params);
            this.$message.success(res.data.msg);
            this.getClientList();
            this.payVisible = false;
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 展开行
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.checkId = [];
          this.getClientList();
        },
        // 获取订单列表
        async getClientList() {
          try {
            this.loading = true;
            this.fullLoading = true;
            const params = JSON.parse(JSON.stringify(this.params));
            if (this.searchType && this.searchType !== "product_id") {
              params[this.searchType] = params.keywords;
              params.keywords = "";
            }
            // 销售
            if (this.curSaleId) {
              params["custom_field[IdcsmartSale]"] = this.curSaleId;
            }
            const res = await getOrder(params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.page_total_amount = res.data.data.page_total_amount;
            this.total_amount = res.data.data.total_amount;
            this.data.forEach((item) => {
              item.list = [];
              item.isExpand = false;
            });
            this.loading = false;
            // if (JSON.stringify(this.curInfo) !== '{}') { //修改子项打开对应的订单下拉
            //   this.itemClick(this.curInfo)
            // } else {
            // }
          } catch (error) {
            this.loading = false;
          }
        },
        // id点击获取订单详情
        itemClick(row) {
          // if (row.order_item_count < 2) {
          //   this.jumpPorduct(row.client_id, row.host_id)
          //   return
          // }
          row.isExpand = row.isExpand ? false : true;
          const rowData = this.$refs.table.getData(row.id);
          this.$refs.table.toggleExpandData(rowData);
          if (row.list?.length > 0) {
            return;
          }
          this.father_client_id = row.client_id;
          this.getOrderDetail(this.optType === "sub" ? row.pId : row.id);
        },
        childItemClick(row) {
          this.jumpPorduct(this.father_client_id, row.host_id);
        },
        // 订单详情
        async getOrderDetail(id) {
          try {
            const res = await getOrderDetail(id);
            res.data.data.order.items.forEach((item) => {
              item.pId = res.data.data.order.id;
              this.$refs.table.appendTo(id, item);
            });
          } catch (error) {}
        },
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

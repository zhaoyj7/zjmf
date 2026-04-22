(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("product-detail")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}`;
    new Vue({
      components: {
        comConfig,
        comTreeSelect,
        comTinymce,
      },
      data() {
        return {
          baseUrl: str,
          id: "", // 用户id
          data: [],
          submitLoading: false,
          visible: false,
          delVisible: false,
          diaTitle: "",
          params: {
            keywords: "",
            page: 1,
            limit: 15,
            orderby: "id",
            sort: "desc",
          },
          total: 0,
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          title: "",
          delId: "",
          formData: {
            id: "",
            name: "",
            product_group_id: "",
            description: "",
            hidden: 1, // 1 隐藏，0 显示
            stock_control: 0, // 库存控制(1:启用)默认0
            sync_stock: 0, // 删除时是否回滚库存(1:启用)默认0
            qty: 0,
            auto_renew_in_advance: 0,
            auto_renew_in_advance_num: 0,
            auto_renew_in_advance_unit: "minute",
            // creating_notice_sms: 0, // 1开启 0关闭
            // creating_notice_sms_api: 0,
            // creating_notice_sms_api_template: 0,
            // creating_notice_mail: 0,
            // creating_notice_mail_api: 0,
            // creating_notice_mail_template: 0,

            // created_notice_sms: 0,
            // created_notice_sms_api: 0,
            // created_notice_sms_api_template: "",
            // created_notice_mail: 0,
            // created_notice_mail_api: 0,
            // created_notice_mail_template: 0,

            pay_type: "recurring_prepayment",
            upgrade: [],
            product_id: "",
            type: "",
            rel_id: "",
            price: undefined,
            renew_rule: "due",
          },
          connectList: [],
          checkOptions: [
            {
              value: 1,
              label: lang.open,
            },
            {
              value: 0,
              label: lang.close,
            },
          ],
          rules: {
            qty: [
              {
                pattern: /^([1-9]\d{0,8}|0)$/,
                message: lang.verify13 + "0~999999999",
                type: "warning",
              },
            ],
            description: [
              {
                validator: (val) => val.length <= 10000,
                message: lang.verify3 + 10000,
                type: "warning",
              },
            ],
          },
          visibleMoney: false,
          visibleLog: false,
          moneyData: {
            // 充值/扣费
            id: "",
            type: "", //  recharge充值 deduction扣费
            amount: "",
            notes: "",
          },
          // 变更记录
          logData: [],
          logCunt: 0,
          tableLayout: false,
          bordered: true,
          hover: true,
          secondGroup: [],
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          currency_prefix: JSON.parse(localStorage.getItem("common_set"))
            .currency_prefix,
          smsInterList: [],
          emailInterList: [],
          smsInterTemp: [], // 开通中短信模板
          smsInterTemp1: [], // 已开通短信模板
          emailInterTemp: [],
          payType: [
            {
              value: "free",
              label: lang.free,
            },
            {
              value: "onetime",
              label: lang.onetime,
            },
            {
              value: "recurring_prepayment",
              label: lang.recurring_prepayment,
            },
            {
              value: "on_demand",
              label: lang.on_demand_bill,
            },
            {
              value: "recurring_prepayment_on_demand",
              label: lang.on_demand_cycle,
            },
            // {
            //   value: "recurring_postpaid",
            //   label: lang.recurring_postpaid,
            // },
          ],
          relationList: [],
          smsTempList: {},
          creatingName: "",
          createdName: "",
          visibleFree: false,
          tempFreeType: "",
          syncLoading: false,
          multiliTip: "",
        };
      },
      created() {
        this.id = location.href.split("?")[1].split("=")[1];
        if (!this.$checkPermission("auth_product_detail_basic_info_view")) {
          const clientAuth = [
            {auth: "auth_product_detail_server_view", url: "product_api"},
            {
              auth: "auth_product_detail_custom_field_view",
              url: "product_self_field",
            },
          ];
          const firstItem = clientAuth.find((item) =>
            this.$checkPermission(item.auth)
          );
          return (location.href = `${this.baseUrl}/${firstItem.url}.htm?id=${this.id}`);
        }
        this.langList = JSON.parse(
          localStorage.getItem("common_set")
        ).lang_home;
        this.getProDetails();
        // this.getSecondGroup();
        this.getPlugin();
        // this.getEmail();
        // this.getEmailTemp();
      },
      watch: {
        "formData.id"(val) {
          val && this.getRelationList();
        },
        "formData.creating_notice_sms_api"(val) {
          if (!val) {
            this.formData.creating_notice_sms_api_template = "";
          }
        },
        "formData.created_notice_sms_api"(val) {
          if (!val) {
            this.formData.created_notice_sms_api_template = "";
          }
        },
      },
      computed: {
        labelTip() {
          return this.moneyData.type === "recharge"
            ? this.currency_prefix
            : `-${this.currency_prefix}`;
        },
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
        // 同步
        async handleSync() {
          try {
            this.syncLoading = true;
            const res = await syncUpstreamPrice(this.id);
            this.syncLoading = false;
            this.$message.success(res.data.msg);
            this.getProDetails();
          } catch (error) {
            this.syncLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        choosePro(val) {
          this.formData.product_group_id = val;
        },
        // 切换类型
        changeFreeType(e) {
          this.visibleFree = true;
          this.tempFreeType = e;
        },
        confirmChange() {
          this.visibleFree = false;
          this.formData.pay_type = this.tempFreeType;
        },
        changeSmsInterface(e) {
          const name = this.smsInterList.filter((item) => item.id === e)[0]
            ?.name;
          this.creatingName = name;
          this.formData.creating_notice_sms_api_template = "";
        },
        async getRelationList() {
          try {
            const res = await getProduct({
              type: this.formData.type,
              rel_id: this.formData.rel_id,
            });
            this.connectList = res.data.data.list;
            this.relationList = res.data.data.list.filter(
              (item) => item.id !== this.id * 1
            );
          } catch (error) {}
        },
        async getEmail() {
          try {
            const res = await getEmailInterface();
            this.emailInterList = res.data.data.list;
          } catch (error) {}
        },
        async getSmsTemp(name) {
          // 根据name获取短信模板
          try {
            const res = await getSmsTemplate(name);
            this.$set(this.smsTempList, name, res.data.data.list);
          } catch (error) {}
        },
        async getEmailTemp() {
          try {
            const res = await getEmailTemplate();
            this.emailInterTemp = res.data.data.list;
          } catch (error) {}
        },
        changeCreating(id) {
          const name = this.smsInterList.find((item) => item.id === id).name;
          this.creatingName = name;
        },
        changeCreated(id) {
          const name = this.smsInterList.find((item) => item.id === id).name;
          this.createdName = name;
          this.formData.created_notice_sms_api_template = "";
        },
        // 获取商品二级分组
        async getSecondGroup() {
          try {
            const res = await getSecondGroup();
            this.secondGroup = res.data.data.list;
          } catch (error) {}
        },
        // 删除用户
        deleteUser() {
          this.delVisible = true;
        },
        async sureDelUser() {
          try {
            const res = await deleteClient(this.id);
            this.delVisible = false;
            location.href = "client.htm";
          } catch (error) {
            this.delVisible = false;
          }
        },
        // 恢复
        changeStatus() {
          this.statusVisble = true;
        },
        async sureChange() {
          try {
            const res = await changeOpen(this.id, {status: 1});
            this.statusVisble = false;
            this.$message.success(res.data.msg);
            this.getProDetails();
          } catch (error) {
            this.statusVisble = false;
          }
        },

        // 提交修改用户信息
        updateUserInfo() {
          this.formData.description = this.$refs.comTinymce.getContent();
          this.$refs.userInfo
            .validate()
            .then(async (res) => {
              // 验证通过
              try {
                const params = {...this.formData};
                delete params.auto_setup;
                delete params.type;
                delete params.rel_id;
                this.submitLoading = true;
                const res = await editProduct(params);
                this.$message.success(res.data.msg);
                this.getProDetails();
                this.submitLoading = false;
              } catch (error) {
                this.submitLoading = false;
                this.$message.error(error.data.msg);
              }
            })
            .catch((err) => {
              console.log(err);
            });
        },
        // 获取商品详情
        async getProDetails() {
          try {
            const res = await getProductDetail(this.id);
            const temp = res.data.data.product;
            document.title =
              lang.product_list +
              "-" +
              temp.name +
              "-" +
              localStorage.getItem("back_website_name");
            temp.price = temp.price * 1;
            this.formData = temp;
            this.formData.product_id = temp.product_id || "";
            this.$nextTick(() => {
              this.$refs.comTinymce.setContent(temp.description);
            });
          } catch (error) {
            console.log(error);
          }
        },
        back() {
          location.href = "product.htm";
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

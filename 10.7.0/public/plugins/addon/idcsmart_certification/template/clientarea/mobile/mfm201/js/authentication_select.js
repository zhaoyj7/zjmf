(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    typeof old_onload == "function" && old_onload();
    window.lang = Object.assign(window.lang, window.plugin_lang);

    const { showToast } = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
        payDialog,
        curSelect,
      },
      data() {
        return {
          lang: window.lang,
          certificationInfoObj: {
            company: {},
            person: {},
          },
          commonData: {},
          configInfo: {},
          configLoading: false,
          authenticationType: "1", //   认证类型 1 个人  2 企业
          custom_fieldsList: [], // 认证方式下拉框数组
          checkedVlue: "", // 选择的认证方式
          custom_fieldsObj: [], // 其他自定义字段
          plugin_name: "",
          needOpen: true,
          personPluginList: [], // 个人认证方式数组
          companyPluginList: [], // 企业认证方式数组
          statusArr: ["Unpaid", "WaitUpload", "WaitReview", "ReviewFail"],
        };
      },
      computed: {
        isShowPay() {
          return (
            this.statusArr.includes(this.configInfo?.order?.status) ||
            (!this.configInfo?.order?.id && this.configInfo?.pay === 1)
          );
        },
      },
      watch: {
        checkedVlue(val) {
          if (val) {
            this.getConfig();
          }
        },
      },
      created() {
        this.getCommonData();
        this.getCertificationInfo();
      },
      mounted() {},
      methods: {
        goBack() {
          history.go(-1);
        },
        getConfig() {
          apiCertificationConfig({
            name: this.checkedVlue,
            type: this.authenticationType === "1" ? "person" : "company",
          })
            .then((res) => {
              this.configInfo = res.data.data;
            })
            .catch((err) => {
              showToast(err.data.msg);
            });
        },
        paySuccess(e) {
          this.needOpen = false;
          this.goUploadPage();
        },
        payCancel() {},
        clickType(val) {
          if (val === "1" && this.certificationInfoObj.person.status === 1) {
            return;
          }
          if (val === "1") {
            this.custom_fieldsList = [...this.personPluginList];
          } else {
            this.custom_fieldsList = [...this.companyPluginList];
          }
          this.authenticationType = val;
          this.checkedVlue = this.custom_fieldsList[0].value;
        },
        selectChange(e) {
          this.plugin_name = e[0].value;
        },
        // 返回按钮
        backTicket() {
          location.href = "/account.htm";
        },
        // 点击下一步
        async goUploadPage() {
          if (!this.authenticationType) {
            showToast(lang.realname_text82);
            return;
          }
          this.configLoading = true;
          let configData = {};
          await apiCertificationConfig({
            name: this.checkedVlue,
            type: this.authenticationType === "1" ? "person" : "company",
          })
            .then((res) => {
              configData = res.data.data;
            })
            .catch((err) => {
              this.configLoading = false;
              showToast(err.data.msg);
            });
          if (this.statusArr.includes(configData.order.status)) {
            if (this.needOpen || configData?.order?.status === "Unpaid") {
              this.$refs.payDialog.showPayDialog(configData.order.id);
            } else {
              this.needOpen = true;
            }
            this.configLoading = false;
            return;
          }

          if (
            configData.order &&
            !configData.order.id &&
            configData.pay === 1
          ) {
            await apiCertificationOrder({
              name: this.checkedVlue,
              type: this.authenticationType === "1" ? "person" : "company",
            })
              .then((res) => {
                this.$refs.payDialog.showPayDialog(res.data.data.order_id);
                this.configLoading = false;
              })
              .catch((err) => {
                this.configLoading = false;
                showToast(err.data.msg);
              });
            return;
          }
          if (this.authenticationType === "1") {
            location.href = `authentication_person.htm?name=${this.checkedVlue}`;
          } else if (this.authenticationType === "2") {
            location.href = `authentication_company.htm?name=${this.checkedVlue}`;
          }
          this.configLoading = false;
        },
        // 获取基础信息
        getCertificationInfo() {
          certificationInfo().then(async (res) => {
            this.certificationInfoObj = res.data.data;
            // 获取实名认证方式
            await certificationPlugin().then((ress) => {
              this.certificationPluginList = ress.data.data.list;
              this.certificationPluginList.forEach((item) => {
                const obj = {};
                obj.value = item.name;
                obj.label = item.title;
                if (item.certification_type.includes("person")) {
                  this.personPluginList.push(obj);
                }
                if (item.certification_type.includes("company")) {
                  this.companyPluginList.push(obj);
                }
              });
            });
            if (this.certificationInfoObj.company.status === 1) {
              location.href = `authentication_status.htm?type=2`;
              return;
            }
            if (this.certificationInfoObj.person.status === 1) {
              this.authenticationType = "2";
              this.custom_fieldsList = [...this.companyPluginList];
            } else {
              this.custom_fieldsList = [...this.personPluginList];
            }
            this.checkedVlue = this.custom_fieldsList[0].value
              ? this.custom_fieldsList[0].value
              : "";
          });
        },
        // 获取通用配置
        getCommonData() {
          getCommon().then((res) => {
            if (res.data.status === 200) {
              this.commonData = res.data.data;
              localStorage.setItem(
                "common_set_before",
                JSON.stringify(res.data.data)
              );
              document.title =
                this.commonData.website_name + "-" + lang.realname_text81;
            }
          });
        },
      },
    });
    window.directiveInfo.forEach((item) => {
      app.directive(item.name, item.fn);
    });
    app.use(vant).mount("#template");
  };
})(window);

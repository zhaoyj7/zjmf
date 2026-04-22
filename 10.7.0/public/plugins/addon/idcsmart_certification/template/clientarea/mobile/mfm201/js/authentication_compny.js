(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    typeof old_onload == "function" && old_onload();
    window.lang = Object.assign(window.lang, window.plugin_lang);

    const { showToast } = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
        curSelect,
      },
      data() {
        return {
          lang: window.lang,
          commonData: {},
          dialogVisible: false,
          sunmitBtnLoading: false,
          certificationInfoObj: {},
          dialogImageUrl: "",
          uploadTipsText1: "",
          uploadTipsText2: "",
          upload_progress: "0%",
          uploadTipsText3: "",
          jwt: `Bearer ${localStorage.jwt}`,
          plugin_name: "", // 实名接口
          certificationEnterprise: {
            // 企业实名认证信息对象
            card_name: "", //姓名
            card_type: 1, // 证件类型:1大陆,0非大陆
            card_number: "", // 证件号码
            phone: "", // 手机号
            company: "", // 公司
            company_organ_code: "", // 公司代码
            custom_fields: {},
          },
          custom_fieldsObj: [], // 其他自定义字段
          img_one: "", // 身份证正面照
          img_two: "", // 身份证反面照
          img_three: "", // 营业执照
          enterpriseRules: {
            company: [
              { required: true, message: lang.realname_text3, trigger: "blur" },
            ],
            company_organ_code: [
              { required: true, message: lang.realname_text5, trigger: "blur" },
            ],
            card_name: [
              {
                required: true,
                message: lang.realname_text65,
                trigger: "blur",
              },
            ],
            card_type: [
              {
                required: true,
                message: lang.realname_text66,
                trigger: "blur",
              },
            ],
            card_number: [
              {
                required: true,
                message: lang.realname_text67,
                trigger: "blur",
              },
            ],
          },
          id_card_type: [
            {
              label: lang.realname_text68,
              value: 1,
            },
            {
              label: lang.realname_text69,
              value: 0,
            },
            {
              label: lang.realname_text70,
              value: 0,
            },
            {
              label: lang.realname_text71,
              value: 0,
            },
            {
              label: lang.realname_text72,
              value: 0,
            },
          ],
          card_one_fileList: [],
          card_two_fileList: [],
          card_three_fileList: [],
          custom_fileList: [],
        };
      },
      created() {
        this.plugin_name = location.href.split("?")[1].split("=")[1];
        this.getCommonData();
        this.getcustom_fields();
        this.getCertificationInfo();
      },
      methods: {
        calcOption(obj) {
          return Object.keys(obj).map((key) => {
            return {
              label: obj[key],
              value: key,
            };
          });
        },
        goBack() {
          history.go(-1);
        },
        // 返回按钮
        backTicket() {
          location.href = "/account.htm";
        },
        onProgress(event) {
          this.upload_progress = event.percent.toFixed(2) + "%";
        },
        goSelect() {
          location.href = "authentication_select.htm";
        },
        // 获取配置信息
        getCertificationInfo() {
          certificationInfo().then(async (res) => {
            this.certificationInfoObj = res.data.data;
          });
        },
        // 获取自定义字段
        getcustom_fields() {
          custom_fields({ name: this.plugin_name, type: "company" }).then(
            (res) => {
              const custom_fields = res.data.data.custom_fields;
              this.custom_fieldsObj = custom_fields.map((item) => {
                this.certificationEnterprise.custom_fields[item.field] =
                  item.type === "file" ? [] : "";
                return item;
              });
            }
          );
        },
        afterRead(file, name, isCustom = false) {
          const arr = [];
          if (file instanceof Array) {
            arr.push(...file);
          } else {
            arr.push(file);
          }

          this.uploadFiles(arr, name, isCustom);
        },

        uploadFiles(arr, name, isCustom) {
          arr.forEach((item) => {
            item.status = "uploading";
            const formData = new FormData();
            formData.set("file", item.file); // 这里要用set,如果用append，还是会出现一起上传的情况
            uploadFile(formData)
              .then((res) => {
                if (res.data.status === 200) {
                  item.status = "done";
                  item.save_name = res.data.data.save_name;
                }
              })
              .catch((err) => {
                if (isCustom) {
                  this.certificationEnterprise.custom_fields[name] =
                    this.certificationEnterprise.custom_fields[name].filter(
                      (file) => file.file !== item.file
                    );
                } else {
                  this[name] = this[name].filter(
                    (file) => file.file !== item.file
                  );
                }
                showToast(err.data.msg);
              });
          });
        },

        // 企业认证提交
        companySumit() {
          const params = JSON.parse(
            JSON.stringify(this.certificationEnterprise)
          );
          Object.keys(params.custom_fields).forEach((key) => {
            if (params.custom_fields[key] instanceof Array) {
              params.custom_fields[key] = params.custom_fields[key].map(
                (item) => item.save_name
              );
            }
          });
          this.$refs.certificationEnterprise.validate().then(() => {
            params.img_three = this.card_three_fileList[0]?.save_name || "";
            params.plugin_name = this.plugin_name;
            let valid = true;
            console.log(params);

            this.custom_fieldsObj.forEach((item) => {
              if (
                (item.required && !params.custom_fields[item.field]) ||
                (item.required && params.custom_fields[item.field].length === 0)
              ) {
                console.log(!params[item.field]);

                valid = false;
              }
            });
            if (!valid) {
              showToast(lang.realname_text73);
              return;
            }
            if (this.certificationInfoObj.certification_upload === "1") {
              if (params.img_three == "") {
                showToast(lang.realname_text74);
                return;
              }
            }
            this.sunmitBtnLoading = true;
            uploadCompany(params)
              .then((ress) => {
                if (ress.data.status === 200) {
                  location.href = "authentication_thrid.htm?type=2";
                }
              })
              .catch((err) => {
                showToast(err.data.msg);
              })
              .finally(() => {
                this.sunmitBtnLoading = false;
              });
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
                this.commonData.website_name + "-" + lang.realname_text75;
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

function captchaCheckSuccsss(bol, captcha, token, login) {
  vm.captchaBol = bol;
  vm.formData.captcha = captcha;
  vm.formData.token = token;
  vm.direct_login = login;
}

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const login = document.getElementById("login");
    Vue.prototype.lang = window.lang;
    if (localStorage.getItem("backJwt")) {
      const host = location.origin;
      const fir = location.pathname.split("/")[1];
      const str = `${host}/${fir}/`;
      location.href = str;
      return;
    }
    const vm = new Vue({
      data() {
        return {
          check: false,
          type: "password",
          loading: false,
          formData: {
            name: "",
            password: "",
            remember_password: 0,
            token: "",
            captcha: "",
          },
          captcha: "",
          rules: {
            name: [
              {
                required: true,
                message: lang.input + lang.acount,
                type: "error",
              },
            ],
            password: [
              {
                required: true,
                message: lang.input + lang.password,
                type: "error",
              },
            ],
            captcha: [{required: true, message: lang.captcha, type: "error"}],
          },
          captcha_admin_login: 0, // 登录是否需要验证码
          website_name: "",
          direct_login: false, // 是否验证通过直接登录
          admin_allow_remember_account: 0,
          admin_login_password_encrypt: 0,
          admin_second_verify: 0,
          verifyVisible: false,
          verifyTitle: "",
          sendCodeParams: {
            action: "",
            name: "", // 管理员用户名
            phone_code: "",
            phone: "",
            email: "",
          },
          isVerifySending: false,
          verifyCodeTime: 60,
          timer1: null,
          verifyType: "sms",
          verifyFormData: {
            code: "",
          },
          needSecondVerify: false,
          captcha_admin_login_error: 1,
          captcha_admin_login_error_3_times: 0,
          passwordKey: "idcsmart.finance",
          passwordIv: "9311019310287172",
        };
      },
      created() {
        this.getLoginInfoData(true);
        if (!localStorage.getItem("lang")) {
          localStorage.setItem("lang", "zh-cn");
        }
      },
      watch: {
        needShowCaptcha(val) {
          if (val) {
            this.getCaptcha();
          }
        },
        direct_login(bol) {
          if (bol) {
            this.submitLogin();
          }
        },
      },
      computed: {
        needShowCaptcha() {
          return (
            this.captcha_admin_login === 1 &&
            (this.captcha_admin_login_error === 0 ||
              (this.captcha_admin_login_error === 1 &&
                this.captcha_admin_login_error_3_times === 1))
          );
        },
      },
      methods: {
        verifyClose() {
          this.verifyVisible = false;
          this.$refs.verifyDialog && this.$refs.verifyDialog.reset();
        },
        onVerifySubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.submitLogin();
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        getVerifyCode() {
          this.sendCodeParams.action = "admin_login";
          this.sendCodeParams.name = this.formData.name;
          this.verifyType === "sms"
            ? this.sendPhoneCode()
            : this.sendEmailCode();
        },
        sendPhoneCode() {
          this.isVerifySending = true;
          apiSendPhoneCode(this.sendCodeParams)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                this.setCodeTime();
              }
            })
            .catch((error) => {
              this.isVerifySending = false;
              this.$message.error(error.data.msg);
            });
        },

        setCodeTime() {
          clearInterval(this.timer1);
          this.timer1 = null;
          this.timer1 = setInterval(() => {
            if (this.verifyCodeTime === 0) {
              clearInterval(this.timer1);
              this.timer1 = null;
              this.verifyCodeTime = 60;
              this.isVerifySending = false;
            } else {
              this.verifyCodeTime--;
            }
          }, 1000);
        },

        sendEmailCode() {
          this.isVerifySending = true;
          apiSendEmailCode(this.sendCodeParams)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                this.setCodeTime();
              }
            })
            .catch((error) => {
              this.isVerifySending = false;
              this.$message.error(error.data.msg);
            });
        },
        async getCaptcha() {
          try {
            const res = await getCaptcha();
            const temp = res.data.data.html;
            $("#admin-captcha").html(temp);
          } catch (error) {}
        },
        // 加密方法
        encrypt(str) {
          const key = CryptoJS.enc.Utf8.parse(this.passwordKey);
          const iv = CryptoJS.enc.Utf8.parse(this.passwordIv);
          return CryptoJS.AES.encrypt(str, key, {
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7,
            iv: iv,
          }).toString();
        },
        // 解密方法
        decrypt(encryptedStr) {
          const key = CryptoJS.enc.Utf8.parse(this.passwordKey);
          const iv = CryptoJS.enc.Utf8.parse(this.passwordIv);
          return CryptoJS.AES.decrypt(encryptedStr, key, {
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7,
            iv: iv,
          }).toString(CryptoJS.enc.Utf8);
        },
        async getLoginInfoData(isInit = false) {
          try {
            const res = await getLoginInfo({name: this.formData.name || ""});
            this.captcha_admin_login = res.data.data.captcha_admin_login;
            this.captcha_admin_login_error =
              res.data.data.captcha_admin_login_error;
            this.captcha_admin_login_error_3_times =
              res.data.data.captcha_admin_login_error_3_times;
            this.admin_second_verify = res.data.data.admin_second_verify;
            this.admin_allow_remember_account =
              res.data.data.admin_allow_remember_account;
            this.admin_login_password_encrypt =
              res.data.data.admin_login_password_encrypt;
            if (res.data.data.admin_allow_remember_account == 1 && isInit) {
              let password = localStorage.getItem("password") || "";
              if (password.startsWith("ENC:")) {
                password = this.decrypt(password.slice(4));
              }
              this.formData = {
                name: localStorage.getItem("name") || "",
                password,
                remember_password: 0,
                token: localStorage.getItem("backToken") || "",
                captcha: localStorage.getItem("backCaptcha") || "",
              };
              this.check = true;
            }
            localStorage.setItem(
              "back_website_name",
              res.data.data.website_name
            );
            localStorage.setItem("backLang", res.data.data.lang_admin);
            document.title = lang.login + "-" + res.data.data.website_name;
          } catch (error) {}
        },
        // 发起登录
        async submitLogin() {
          try {
            this.loading = true;
            this.formData.remember_password = this.check === true ? 1 : 0;
            const params = {...this.formData};
            if (this.admin_login_password_encrypt === 1) {
              params.password = this.encrypt(params.password);
            }
            if (!this.needShowCaptcha) {
              delete params.token;
              delete params.captcha;
            }
            if (this.needSecondVerify) {
              params.code = String(this.verifyFormData.code);
              params.method = this.verifyType;
            }
            const res = await logIn(params);
            if (res.data.status === 200) {
              this.verifyVisible = false;
              localStorage.setItem("backJwt", res.data.data.jwt);
              // 记住账号
              if (this.formData.remember_password) {
                localStorage.setItem("name", this.formData.name);
                let password = this.formData.password;
                if (this.admin_login_password_encrypt === 1) {
                  password = this.encrypt(password);
                  localStorage.setItem("password", "ENC:" + password);
                } else {
                  localStorage.setItem("password", password);
                }
              } else {
                // 未勾选记住
                localStorage.removeItem("name");
                localStorage.removeItem("password");
              }
              localStorage.setItem("userName", this.formData.name);
              await this.getCommonSetting();
              // 获取权限
              const auth = await getAuthRole();
              const authTemp = auth.data.data.auth;
              // const authList = auth.data.data.list;
              localStorage.setItem("backAuth", JSON.stringify(authTemp));
              //  localStorage.setItem("authList", JSON.stringify(authList));
              // 获取导航
              const menus = await getMenus();
              const menulist = menus.data.data.menu;
              localStorage.setItem(
                "backMenus",
                JSON.stringify(menus.data.data.menu)
              );
              let login_url = menus.data.data.url;
              let menu_id = menus.data.data.menu_id;
              sessionStorage.clear();
              if (menulist.length === 0) {
                return (location.href = "");
              }
              if (login_url === "") {
                if (!menulist[0].child) {
                  login_url = menulist[0].url;
                  menu_id = menulist[0].id;
                } else {
                  login_url = menulist[0].child[0].url;
                  menu_id = menulist[0].child[0].id;
                }
              }
              localStorage.setItem("curValue", menu_id);
              this.$message.success(res.data.msg);
              this.loading = false;
              location.href = login_url;
            }
          } catch (error) {
            this.getLoginInfoData();
            this.$message.error(error.data.msg);
            this.loading = false;
            if (error?.data?.data?.second_verify === 1) {
              this.needSecondVerify = true;
              this.loading = false;
              this.verifyType = error?.data?.data?.method || "sms";
              this.verifyVisible = true;
            } else {
              this.needSecondVerify = false;
            }
            if (error?.data?.data?.captcha === 1 || this.needShowCaptcha) {
              this.getCaptcha();
            }
          }
        },
        // 提交按钮
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            // 开启验证码的时候
            if (this.needShowCaptcha && !this.captchaBol) {
              const captchaInput = document.getElementById("captcha-input");
              if (!captchaInput) {
                this.$message.warning(lang.input + lang.correct_code);
                return;
              } else {
                if (!captchaInput.value) {
                  this.$message.warning(lang.input + lang.correct_code);
                  return;
                }
              }
            }

            if (window.checkCaptchaFn) {
              this.loading = true;
              const captcha = await window.checkCaptchaFn();
              if (!captcha.success) {
                this.loading = false;
                this.captchaBol = false;
                this.formData.captcha = "";
                this.formData.token = "";
                this.$message.error(captcha.msg);
                return;
              }
              this.formData.captcha = captcha.value;
              this.formData.token = captcha.token;
              this.captchaBol = true;
              this.loading = false;
            }

            this.submitLogin();
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 获取通用配置
        async getCommonSetting() {
          try {
            const res = await getCommon();
            localStorage.setItem("common_set", JSON.stringify(res.data.data));
          } catch (error) {}
        },
      },
    }).$mount(login);
    window.vm = vm;
    typeof old_onload == "function" && old_onload();
  };
})(window);

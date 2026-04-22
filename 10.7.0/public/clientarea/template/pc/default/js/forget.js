(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const login = document.getElementById("forget");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        countDownButton,
        captchaDialog,
        safeConfirm,
      },
      data() {
        return {
          isCaptcha: false,
          isShowCaptcha: false, //登录是否需要验证码
          isEmailOrPhone: true, // true:电子邮件 false:手机号
          isPassOrCode: true, // true:密码登录 false:验证码登录
          checked: false,
          client_operate_password: "",
          errorText: "",
          formData: {
            email: "",
            phone: "",
            password: "",
            repassword: "",
            phoneCode: "",
            emailCode: "",
            countryCode: 86,
          },
          token: "",
          captcha: "",
          countryList: [],
          commonData: {},
          codeAction: "emailCode",
        };
      },
      created() {
        this.getCountryList();
        this.getCommonSetting();
      },
      mounted() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      watch: {},
      methods: {
        // 验证码 关闭
        captchaCancel() {
          this.isShowCaptcha = false;
        },
        // 验证码验证成功后的回调
        getData(captchaCode, token) {
          this.isCaptcha = false;
          this.token = token;
          this.captcha = captchaCode;
          this.isShowCaptcha = false;
          if (this.codeAction === "login") {
            this.handelLogin();
          } else if (this.codeAction === "emailCode") {
            this.sendEmailCode(true);
          } else if (this.codeAction === "phoneCode") {
            this.sendPhoneCode(true);
          }
        },

        async getCaptcha() {
          try {
            const res = await getCaptcha();
            const temp = res.data.data;
            this.formData.token = temp.token;
            this.captcha = temp.captcha;
          } catch (error) {}
        },

        hadelSafeConfirm(val) {
          this[val]();
        },

        // 注册
        doResetPass() {
          let isPass = true;
          const form = {...this.formData};
          // 邮件登录验证
          if (this.isEmailOrPhone) {
            if (!form.email) {
              isPass = false;
              this.errorText = lang.ali_tips1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.account_tips40;
            }

            if (!form.emailCode) {
              isPass = false;
              this.errorText = lang.account_tips41;
            } else {
              if (form.emailCode.length !== 6) {
                isPass = false;
                this.errorText = lang.account_tips42;
              }
            }
          }

          // 手机号码登录 验证
          if (!this.isEmailOrPhone) {
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.account_tips43;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^\d+$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.account_tips44;
              }
            }

            if (!form.phoneCode) {
              isPass = false;
              this.errorText = lang.account_tips45;
            } else {
              if (form.phoneCode.length !== 6) {
                isPass = false;
                this.errorText = lang.account_tips46;
              }
            }
          }

          if (!form.password) {
            isPass = false;
            this.errorText = lang.account_tips47;
          } else if (form.password.length > 32 || form.password.length < 6) {
            isPass = false;
            this.errorText = lang.account_tips52;
          }
          if (!form.repassword) {
            isPass = false;
            this.errorText = lang.account_tips48;
          } else {
            if (form.password !== form.repassword) {
              isPass = false;
              this.errorText = lang.account_tips49;
            }
          }

          if (!this.checked) {
            isPass = false;
            this.errorText = lang.account_tips51;
          }

          // 验证通过
          if (isPass) {
            this.errorText = "";
            let code = "";

            if (this.isEmailOrPhone) {
              code = form.emailCode;
            } else {
              code = form.phoneCode;
            }

            const params = {
              type: this.isEmailOrPhone ? "email" : "phone",
              account: this.isEmailOrPhone ? form.email : form.phone,
              phone_code: form.countryCode.toString(),
              code,
              password: form.password,
              re_password: form.repassword,
            };

            //调用忘记密码
            forgetPass(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // 调用登录接口
                  this.handelLogin();
                }
              })
              .catch((err) => {
                console.log(err);
                this.token = "";
                this.captcha = "";
                this.errorText = err.data.msg;
              });
          }
        },
        // 登录
        handelLogin() {
          if (this.isCaptcha) {
            this.isShowCaptcha = true;
            this.codeAction = "login";
            this.$refs.captcha.doGetCaptcha();
            return;
          }
          const params = {
            type: "password",
            account: this.isEmailOrPhone
              ? this.formData.email
              : this.formData.phone,
            phone_code: this.formData.countryCode.toString(),
            code: "",
            password: this.encrypt(this.formData.password),
            remember_password: 0,
            captcha: this.captcha,
            token: this.token,
            client_operate_password: this.client_operate_password,
          };
          logIn(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 存入 jwt
                localStorage.setItem("jwt", res.data.data.jwt);
                getMenu().then((ress) => {
                  if (ress.data.status === 200) {
                    localStorage.setItem(
                      "frontMenus",
                      JSON.stringify(ress.data.data.menu)
                    );
                    const goPage = sessionStorage.redirectUrl || "/home.htm";
                    sessionStorage.redirectUrl &&
                      sessionStorage.removeItem("redirectUrl");
                    location.href = goPage;
                  }
                });
              }
            })
            .catch((error) => {
              this.loginLoading = false;
              this.client_operate_password = "";
              this.token = "";
              this.captcha = "";
              if (error.data.data && error.data.data?.captcha == 1) {
                this.token = "";
                this.captcha = "";
                this.isCaptcha = true;
                this.isShowCaptcha = true;
                this.codeAction = "login";
                this.$refs.captcha.doGetCaptcha();
              } else if (
                error.data.data &&
                error.data.data?.ip_exception_verify &&
                error.data.data.ip_exception_verify.includes("operate_password")
              ) {
                this.$refs.safeRef.openDialog("handelLogin");
                this.$message.error(lang.account_tips_text7);
              } else {
                this.errorText = error.data.msg;
              }
            });
        },

        // 获取通用配置
        async getCommonSetting() {
          try {
            const res = await getCommon();
            this.commonData = res.data.data;
            if (
              this.commonData.captcha_client_login == 1 &&
              (this.commonData.captcha_client_login_error == 0 ||
                (this.commonData.captcha_client_login_error == 1 &&
                  this.commonData.captcha_client_login_error_3_times == 1))
            ) {
              this.isCaptcha = true;
            }
            document.title = this.commonData.website_name + "-" + lang.forget;
            localStorage.setItem(
              "common_set_before",
              JSON.stringify(res.data.data)
            );
          } catch (error) {}
        },
        // 前往协议
        toService() {
          const url = this.commonData.terms_service_url;
          window.open(url);
        },
        toPrivacy() {
          const url = this.commonData.terms_privacy_url;
          window.open(url);
        },
        // 获取国家列表
        getCountryList() {
          getCountry({}).then((res) => {
            console.log(res);
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
            }
          });
        },
        // 加密方法
        encrypt(str) {
          const key = CryptoJS.enc.Utf8.parse("idcsmart.finance");
          const iv = CryptoJS.enc.Utf8.parse("9311019310287172");
          var encrypted = CryptoJS.AES.encrypt(str, key, {
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7,
            iv: iv,
          }).toString();
          return encrypted;
        },
        // 发送邮箱验证码
        sendEmailCode(isAuto = false) {
          const form = this.formData;
          if (!form.email) {
            this.errorText = lang.ali_tips1;
            return;
          } else if (
            form.email.search(
              /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
            ) === -1
          ) {
            this.errorText = lang.account_tips40;
            return;
          }
          if (isAuto !== true) {
            this.token = "";
            this.captcha = "";
          }
          if (
            this.commonData.captcha_client_password_reset == 1 &&
            !this.captcha
          ) {
            this.codeAction = "emailCode";
            this.isShowCaptcha = true;
            this.$refs.captcha.doGetCaptcha();
            return;
          }
          this.errorText = "";
          const params = {
            action: "password_reset",
            email: form.email,
            token: this.token,
            captcha: this.captcha,
          };
          emailCode(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 执行倒计时
                this.token = "";
                this.captcha = "";
                this.$refs.emailCodebtn.countDown();
              }
            })
            .catch((error) => {
              if (error.data.data && error.data.data?.captcha == 1) {
                this.token = "";
                this.captcha = "";
                this.isShowCaptcha = true;
                this.$refs.captcha.doGetCaptcha();
              } else {
                this.errorText = error.data.msg;
              }
              // this.$message.error(error.data.msg);
            });
        },
        // 发送手机短信
        sendPhoneCode(isAuto = false) {
          const form = this.formData;
          if (!form.phone) {
            this.errorText = lang.account_tips43;
            return;
          } else {
            // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
            const reg = /^\d+$/;
            if (!reg.test(form.phone)) {
              this.errorText = lang.account_tips44;
              return;
            }
          }

          if (isAuto !== true) {
            this.token = "";
            this.captcha = "";
          }
          if (
            this.commonData.captcha_client_password_reset == 1 &&
            !this.captcha
          ) {
            this.codeAction = "phoneCode";
            this.isShowCaptcha = true;
            this.$refs.captcha.doGetCaptcha();
            return;
          }
          this.errorText = "";
          const params = {
            action: "password_reset",
            phone_code: form.countryCode,
            phone: form.phone,
            token: this.token,
            captcha: this.captcha,
          };
          phoneCode(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 执行倒计时
                this.token = "";
                this.captcha = "";
                this.$refs.phoneCodebtn.countDown();
              }
            })
            .catch((error) => {
              if (
                error.data.msg === "请输入图形验证码" ||
                error.data.msg === "图形验证码错误"
              ) {
                this.token = "";
                this.captcha = "";
                this.isShowCaptcha = true;
                this.$refs.captcha.doGetCaptcha();
              } else {
                this.errorText = error.data.msg;
              }
            });
        },
        toLogin() {
          location.href = "login.htm";
        },
      },
    }).$mount(login);
    typeof old_onload == "function" && old_onload();
  };
})(window);

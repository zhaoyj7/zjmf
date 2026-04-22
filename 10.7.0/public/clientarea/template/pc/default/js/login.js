(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const login = document.getElementById("login");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        countDownButton,
        captchaDialog,
        securityVerification,
      },
      data() {
        return {
          // 登录是否需要验证
          isCaptcha: false,
          isLoadingFinish: false,
          isShowCaptcha: false, //登录是否显示验证码弹窗
          checked: getCookie("checked") == "1" ? true : false,
          isEmailOrPhone: getCookie("isEmailOrPhone") == "1" ? true : false, // true:电子邮件 false:手机号
          isPassOrCode: getCookie("isPassOrCode") == "1" ? true : false, // true:密码登录 false:验证码登录
          errorText: "",
          formData: {
            email: getCookie("email") ? getCookie("email") : null,
            phone: getCookie("phone") ? getCookie("phone") : null,
            password: getCookie("password") ? getCookie("password") : null,
            phoneCode: "",
            emailCode: "",
            //  isRemember: getCookie("isRemember") == "1" ? true : false,
            isRemember: false,
            countryCode: 86,
          },
          token: "",
          loopTimer: null,
          captcha: "",
          countryList: [],
          codeAction: "",
          isFirstLoad: false,
          commonData: {
            lang_list: [],
          },
          loginLoading: false,
          seletcLang: "",
          curSrc: `/upload/common/country/${lang_obj.countryImg}.png`,
          isShowQrCode: false,
          qrCodeData: {
            img_url: "",
            expire_time: "",
            ticket: "",
            token: "",
            status: "",
            is_refresh: false,
          },
          qrLoading: false,
          clickRefresh: false,
          isShowWxScanLogin: false,
          isShowWxSelectAccount: false,
          clientList: [],
          selectClient: "",
          selectClientLoading: false,
          security_verify_method: "", // 安全验证方式
          security_verify_value: "", // 安全验证值
          certify_id: "", // 认证ID
          security_verify_token: "",
        };
      },
      // 计算钩子
      computed: {
        isShowPhoneType() {
          return (
            (this.commonData.login_phone_verify == 1 && !this.isPassOrCode) ||
            (this.commonData.login_phone_password == 1 && this.isPassOrCode)
          );
        },
        isShowChangeTpyeBtn() {
          return (
            this.commonData.login_phone_verify == 1 &&
            (this.commonData.login_phone_password == 1 ||
              this.commonData.login_email_password == 1)
          );
        },
        isShowPassLogin() {
          return (
            this.isPassOrCode &&
            (this.commonData.login_email_password == 1 ||
              this.commonData.login_phone_password == 1)
          );
        },
        isShowCodeLogin() {
          return !this.isPassOrCode && this.commonData.login_phone_verify == 1;
        },
      },
      created() {
        if (localStorage.getItem("jwt")) {
          location.href = "home.htm";
          return;
        }

        this.getCountryList();
      },
      mounted() {
        this.getCommonSetting();
      },
      methods: {
        hadelSecurityConfirm(callbackFun, securityForm) {
          this.security_verify_method = securityForm.security_verify_method;
          this.security_verify_value = securityForm.security_verify_value;
          this.certify_id = securityForm.certify_id;
          this.security_verify_token = securityForm.security_verify_token;
          this[callbackFun]();
        },
        handleSelectClientLogin() {
          this.selectClientLoading = true;
          apiSelectClientLogin({
            ticket: this.qrCodeData.ticket,
            token: this.qrCodeData.token,
            client_id: this.selectClient,
          })
            .then((res) => {
              // 存入 jwt
              localStorage.setItem("jwt", res.data.data.jwt);
              this.loginSuccess();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.selectClientLoading = false;
            });
        },
        handelRefreshQrCode() {
          this.clickRefresh = true;
          this.getQrcode();
        },
        // 生成QRcode
        async getQrcode() {
          try {
            this.qrLoading = true;
            const res = await apiGetWeixinQrCode();
            this.qrCodeData = Object.assign(this.qrCodeData, res.data.data);
            this.qrLoading = false;
            this.qrCodeData.is_refresh = false;
            !this.clickRefresh && this.getQrCodeStatus();
            this.clickRefresh = false;
          } catch (error) {
            this.qrLoading = false;
          }
        },
        handelBackQr() {
          this.isShowWxSelectAccount = false;
          this.getQrcode();
        },
        // 获取二维码状态
        async getQrCodeStatus() {
          try {
            const res = await apiGetQrCodeStatus({
              ticket: this.qrCodeData.ticket,
              token: this.qrCodeData.token,
            });
            this.qrCodeData.status = res.data.data.status;
            // 等待扫描
            if (res.data.data.status === "Wait" && this.isShowQrCode) {
              this.getQrCodeStatus();
              return;
            }
            // 选择账户
            if (res.data.data.status === "SelectClient") {
              this.clientList = res.data.data.client;
              this.selectClient =
                res.data.data.client.find((item) => item.status === 1)?.id ||
                "";
              this.isShowWxSelectAccount = true;
              return;
            }
            if (res.data.data.status === "WaitBind") {
              // 跳转绑定页面
              location.href =
                "oauth.htm?ticket=" +
                this.qrCodeData.ticket +
                "&token=" +
                this.qrCodeData.token;
              return;
            }
            if (res.data.data.status === "Success") {
              // 存入 jwt
              localStorage.setItem("jwt", res.data.data.jwt);
              this.loginSuccess();
              return;
            }
            if (res.data.data.status === "Expired") {
              this.qrCodeData.is_refresh = true;
              return;
            }
          } catch (error) {
            console.log(error);
          }
        },
        handleQrCode() {
          this.isShowQrCode = !this.isShowQrCode;
          if (this.isShowQrCode) {
            this.getQrcode();
          }
        },
        hadelSafeConfirm(val) {
          this[val]();
        },
        // 验证码验证成功后的回调
        getData(captchaCode, token) {
          this.isCaptcha = false;
          this.token = token;
          this.captcha = captchaCode;
          this.isShowCaptcha = false;
          if (this.codeAction === "login") {
            this.doLogin();
          } else if (this.codeAction === "emailCode") {
            this.sendEmailCode(true);
          } else if (this.codeAction === "phoneCode") {
            this.sendPhoneCode(true);
          }
        },
        goHelpUrl(url) {
          window.open(this.commonData[url]);
        },

        // 语言切换
        changeLang(e) {
          sessionStorage.setItem("brow_lang", e);
          // 刷新页面
          window.location.reload();
        },
        // 登录
        doLogin() {
          let isPass = true;
          const form = {...this.formData};
          // 邮件登录验证
          if (this.isEmailOrPhone) {
            if (!form.email) {
              isPass = false;
              this.errorText = lang.login_text1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.login_text2;
            }

            // 邮件 密码登录 验证
            if (this.isPassOrCode) {
              // 密码登录
              if (!form.password) {
                isPass = false;
                this.errorText = lang.login_text3;
              }
            } else {
              // 邮件 验证码登录 验证
              if (!form.emailCode) {
                isPass = false;
                this.errorText = lang.login_text4;
              } else {
                if (form.emailCode.length !== 6) {
                  isPass = false;
                  this.errorText = lang.login_text5;
                }
              }
            }
          }

          // 手机号码登录 验证
          if (!this.isEmailOrPhone) {
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.login_text6;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^\d+$/;
              if (this.formData.countryCode === 86) {
                if (!reg.test(form.phone)) {
                  isPass = false;
                  this.errorText = lang.login_text7;
                }
              }
            }

            // 手机号 密码登录 验证
            if (this.isPassOrCode) {
              // 密码登录
              if (!form.password) {
                isPass = false;
                this.errorText = lang.login_text3;
              }
            } else {
              // 手机 验证码登录 验证
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
          }

          // 勾选协议
          if (!this.checked) {
            isPass = false;
            this.errorText = lang.account_tips51;
          }

          if (isPass && this.isCaptcha) {
            this.loginLoading = true;
            this.isShowCaptcha = true;
            this.codeAction = "login";
            this.$refs.captcha.doGetCaptcha();
            this.loginLoading = false;
            return;
          }

          // 验证通过
          if (isPass) {
            this.loginLoading = true;
            this.errorText = "";
            let code = "";
            if (!this.isPassOrCode) {
              if (this.isEmailOrPhone) {
                code = form.emailCode;
              } else {
                code = form.phoneCode;
              }
            }
            const params = {
              type: this.isPassOrCode ? "password" : "code",
              account: this.isEmailOrPhone ? form.email : form.phone,
              phone_code: form.countryCode.toString(),
              code,
              password: this.isPassOrCode ? this.encrypt(form.password) : "",
              remember_password: form.isRemember ? "1" : "0",
              captcha: this.captcha,
              token: this.token,
              security_verify_method: this.security_verify_method,
              security_verify_value: this.security_verify_value,
              certify_id: this.certify_id,
              security_verify_token: this.security_verify_token,
            };

            //调用登录接口
            logIn(params)
              .then(async (res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // 存入 jwt
                  localStorage.setItem("jwt", res.data.data.jwt);
                  if (form.isRemember) {
                    // 记住密码
                    if (this.isEmailOrPhone) {
                      setCookie("email", form.email, 30);
                    } else {
                      setCookie("phone", form.phone, 30);
                    }
                    setCookie("password", form.password, 30);
                    setCookie("isRemember", form.isRemember ? "1" : "0");
                    setCookie("checked", this.checked ? "1" : "0");

                    // 保存登录方式
                    setCookie(
                      "isEmailOrPhone",
                      this.isEmailOrPhone ? "1" : "0"
                    );
                    setCookie("isPassOrCode", this.isPassOrCode ? "1" : "0");
                  } else {
                    // 未勾选记住密码
                    delCookie("email");
                    delCookie("phone");
                    delCookie("password");
                    delCookie("isRemember");
                    delCookie("checked");
                  }
                  await this.loginSuccess();
                  this.loginLoading = false;
                }
              })
              .catch((err) => {
                this.loginLoading = false;
                this.security_verify_method = "";
                this.security_verify_value = "";
                this.certify_id = "";
                if (err.data.data && err.data.data?.captcha == 1) {
                  this.token = "";
                  this.captcha = "";
                  this.isCaptcha = true;
                  this.isShowCaptcha = true;
                  this.codeAction = "login";
                  this.$refs.captcha.doGetCaptcha();
                } else if (
                  err?.data?.data?.need_security_verify === true &&
                  err?.data?.data?.available_methods?.length > 0
                ) {
                  this.$refs.securityRef.openDialog(
                    "doLogin",
                    err.data.data.available_methods
                  );
                  this.errorText = err.data.msg;
                } else {
                  this.getCommonSetting(
                    this.isEmailOrPhone ? form.email : form.phone
                  );
                  this.errorText = err.data.msg;
                }
              });
          }
        },
        async loginSuccess() {
          localStorage.setItem(
            "lang",
            this.commonData.lang_home_open == 1
              ? this.seletcLang
              : this.commonData.lang_home
          );
          await getMenu()
            .then((ress) => {
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
            })
            .catch((err) => {
              console.log(err);
            });
        },
        // 获取国家列表
        getCountryList() {
          getCountry({}).then((res) => {
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
            }
          });
        },
        // 加密方法
        encrypt(str) {
          const key = CryptoJS.enc.Utf8.parse("idcsmart.finance");
          const iv = CryptoJS.enc.Utf8.parse("9311019310287172");
          return CryptoJS.AES.encrypt(str, key, {
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7,
            iv: iv,
          }).toString();
        },
        // 发送邮箱验证码
        sendEmailCode(isAuto = false) {
          const form = this.formData;
          if (!form.email) {
            this.errorText = lang.login_text1;
            return;
          } else if (
            form.email.search(
              /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
            ) === -1
          ) {
            this.errorText = lang.login_text2;
            return;
          }
          if (isAuto !== true) {
            this.token = "";
            this.captcha = "";
          }
          if (this.commonData.captcha_client_login == 1 && !this.captcha) {
            this.codeAction = "emailCode";
            this.isShowCaptcha = true;
            this.$refs.captcha.doGetCaptcha();
            return;
          }
          this.errorText = "";
          const params = {
            action: "login",
            email: form.email,
            token: this.token,
            captcha: this.captcha,
          };
          emailCode(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 执行倒计时
                this.$refs.emailCodebtn.countDown();
              }
            })
            .catch((err) => {
              this.errorText = err.data.msg;
              this.token = "";
              this.captcha = "";
            });
        },
        // 发送手机短信
        sendPhoneCode(isAuto = false) {
          const form = this.formData;
          if (!form.phone) {
            this.errorText = lang.login_text6;
            return;
          } else {
            // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
            if (this.formData.countryCode === 86) {
              const reg = /^\d+$/;
              if (!reg.test(form.phone)) {
                this.errorText = lang.login_text7;
                return;
              }
            }
          }
          if (isAuto !== true) {
            this.token = "";
            this.captcha = "";
          }
          if (this.commonData.captcha_client_login == 1 && !this.captcha) {
            this.codeAction = "phoneCode";
            this.isShowCaptcha = true;
            this.$refs.captcha.doGetCaptcha();
            return;
          }
          this.errorText = "";
          const params = {
            action: "login",
            phone_code: form.countryCode,
            phone: form.phone,
            token: this.token,
            captcha: this.captcha,
          };
          phoneCode(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 执行倒计时
                this.$refs.phoneCodebtn.countDown();
              }
            })
            .catch((err) => {
              this.errorText = err.data.msg;
              this.token = "";
              this.captcha = "";
            });
        },
        toRegist() {
          location.href = "regist.htm";
        },
        toForget() {
          location.href = "forget.htm";
        },
        oauthLogin(item) {
          // // 勾选协议
          // if (!this.checked) {
          //   this.errorText = lang.account_tips51;
          //   return;
          // }
          oauthUrl(item.name).then((res) => {
            const openWindow = window.open(
              res.data.data.url,
              "oauth",
              "width=800,height=800"
            );
            clearInterval(this.loopTimer);
            this.loopTimer = null;
            this.loopTimer = setInterval(() => {
              if (openWindow.closed) {
                clearInterval(this.loopTimer);
                this.loopTimer = null;
                this.getOauthToken();
              }
            }, 300);
          });
        },
        getOauthToken() {
          oauthToken().then((res) => {
            if (res.data.data && (res.data.data.jwt || res.data.data.url)) {
              if (res.data.data.jwt) {
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
              } else {
                location.href = res.data.data.url;
              }
            }
          });
        },
        // 获取通用配置
        async getCommonSetting(account) {
          try {
            const res = await getCommon({account});
            if (
              !account &&
              havePlugin("MpWeixinNotice") &&
              res.data.data.first_login_type === "mp_weixin_notice"
            ) {
              this.isShowQrCode = true;
              this.getQrcode();
            }
            const plugin_configuration =
              res.data.data.plugin_configuration || {};
            this.isShowWxScanLogin =
              havePlugin("MpWeixinNotice") &&
              plugin_configuration?.mp_weixin_notice?.scan_login == 1;
            this.commonData = res.data.data;
            this.seletcLang = getBrowserLanguage();
            if (!this.isFirstLoad) {
              // 不支持手机验证码登录
              if (this.commonData.login_phone_verify == 0) {
                this.isPassOrCode = true;
              } else {
                // 支持手机验证码登录 再判断是否支持密码登录
                this.isPassOrCode =
                  this.commonData.first_login_method == "password" &&
                  (this.commonData.login_phone_password == 1 ||
                    this.commonData.login_email_password == 1);
              }
              // 仅支持邮箱密码登录
              if (this.isPassOrCode) {
                this.isEmailOrPhone =
                  (this.commonData.first_password_login_method === "email" &&
                    this.commonData.login_email_password == 1) ||
                  (this.commonData.first_password_login_method === "phone" &&
                    this.commonData.login_phone_password == 0);
              } else {
                this.isEmailOrPhone = false;
              }

              this.isFirstLoad = true;
            }
            if (
              this.commonData.captcha_client_login == 1 &&
              (this.commonData.captcha_client_login_error == 0 ||
                (this.commonData.captcha_client_login_error == 1 &&
                  this.commonData.captcha_client_login_error_3_times == 1))
            ) {
              this.isCaptcha = true;
            }

            document.title =
              this.commonData.website_name + "-" + lang.login_text8;
            localStorage.setItem(
              "common_set_before",
              JSON.stringify(res.data.data)
            );
            localStorage.setItem("lang", this.seletcLang);
            this.isLoadingFinish = true;
            // 关闭loading
            document.getElementById("mainLoading").style.display = "none";
            document.getElementsByClassName("template")[0].style.display =
              "block";
          } catch (error) {
            // 关闭loading
            document.getElementById("mainLoading").style.display = "none";
            document.getElementsByClassName("template")[0].style.display =
              "block";
          }
        },
        // 获取前台导航
        doGetMenu() {
          getMenu().then((res) => {
            if (res.data.status === 200) {
              localStorage.setItem(
                "frontMenus",
                JSON.stringify(res.data.data.menu)
              );
            }
          });
        },
        changeLoginType() {
          this.isPassOrCode = !this.isPassOrCode;
          // 密码登录

          if (this.isPassOrCode) {
            this.isEmailOrPhone =
              (this.commonData.first_password_login_method === "email" &&
                this.commonData.login_email_password == 1) ||
              (this.commonData.first_password_login_method === "phone" &&
                this.commonData.login_phone_password == 0);
          } else {
            this.isEmailOrPhone = false;
          }
          this.token = "";
          this.captcha = "";
        },
        toService() {
          const url = this.commonData.terms_service_url;
          window.open(url);
        },
        toPrivacy() {
          const url = this.commonData.terms_privacy_url;
          window.open(url);
        },
        // 验证码 关闭
        captchaCancel() {
          this.isShowCaptcha = false;
        },
      },
    }).$mount(login);
    typeof old_onload == "function" && old_onload();
  };
})(window);

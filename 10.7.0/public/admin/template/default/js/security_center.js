(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-login")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          // 修改密码弹窗
          editPassVisible: false,
          type: 1,
          loading: false,
          set_operate_password: false,
          editPassFormData: {
            password: "",
            repassword: "",
            origin_password: "",
          },
          editName: false,
          nickname: "",
          name: "",
          email: "",
          phone: "",
          phone_code: "",
          totp_bind: "",
          admin_role_name: "",
          verifyVisible: false,
          verifyType: "phone",
          code: "",
          verifyFormData: {
            phone_code: "",
            phone: "",
            email: "",
            code: "",
          },
          sendCodeParams: {
            action: "", // admin_login 登录  admin_verify  验证手机 admin_update 修改手机
            name: "", // 管理员用户名
            phone_code: "",
            phone: "",
            email: "",
          },
          verifyLoading: false,
          changeVisible: false,
          country: [],
          changeLoading: false,
          isVerifySending: false,
          isChangeSending: false,
          verifyCodeTime: 60,
          changeCodeTime: 60,
          timer1: null,
          timer2: null,
          totpInfo: {
            secret: "",
            url: "",
          },
          totpVisible: false,
          totpLoading: false,
          totpFormData: {
            code: "",
          },
          QRCode: null,
          unbindTotpVisible: false,
          unbindTotpFormData: {
            type: "totp",
            code: "",
          },
          unbindTotpLoading: false,
          qrcodeLoading: false,
          prohibit_admin_bind_phone: 1,
          prohibit_admin_bind_email: 1,
        };
      },
      created() {
        this.getLoginInfo();
        this.getCountry();
      },
      methods: {
        unbindTotpClose() {
          this.unbindTotpVisible = false;
          this.$refs.unbindTotpDialog && this.$refs.unbindTotpDialog.reset();
        },
        onUnbindTotpSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.unbindTotpLoading = true;
            const params = {
              code: this.unbindTotpFormData.code,
              method: this.verifyType,
            };
            apiUnbindTotp(params)
              .then((res) => {
                this.unbindTotpLoading = false;
                this.unbindTotpClose();
                this.$message.success(res.data.msg);
                this.getLoginInfo();
              })
              .catch((error) => {
                this.unbindTotpLoading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        getTotpInfo() {
          this.qrcodeLoading = true;
          apiTotpInfo()
            .then((res) => {
              this.totpInfo.secret = res.data.data.secret;
              this.totpInfo.url = res.data.data.url;
              this.getQrcode();
              this.qrcodeLoading = false;
            })
            .catch(() => {
              this.qrcodeLoading = false;
            });
        },
        // 生成QRcode
        getQrcode() {
          const qrcode = document.getElementById("qrcode"); // 获取 canvas 元素
          qrcode.innerHTML = "";
          new QRCode(qrcode, this.totpInfo.url);
        },
        handelTotp() {
          if (this.totp_bind == 0) {
            this.getTotpInfo();
            this.totpVisible = true;
          } else {
            this.unbindTotpVisible = true;
          }
        },

        totpClose() {
          this.totpVisible = false;
          this.$refs.totpDialog && this.$refs.totpDialog.reset();
        },
        handelCopy() {
          copyText(this.totpInfo.secret);
        },
        onTotpSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.totpLoading = true;
            const params = {
              code: String(this.totpFormData.code),
            };

            apiBindTotp(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.totpLoading = false;
                  this.totpClose();
                  this.$message.success(res.data.msg);
                  this.getLoginInfo();
                }
              })
              .catch((error) => {
                this.totpLoading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 获取国家列表
        async getCountry() {
          try {
            const res = await getCountry();
            this.country = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        changeClose() {
          this.changeVisible = false;
          this.$refs.changeDialog && this.$refs.changeDialog.reset();
        },
        onChangeSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.changeLoading = true;
            const subApi =
              this.verifyType === "phone" ? apiChangePhone : apiChangeEmail;
            const params = {...this.sendCodeParams};
            subApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.changeLoading = false;
                  this.changeClose();
                  this.$message.success(res.data.msg);
                  this.getLoginInfo();
                }
              })
              .catch((error) => {
                this.changeLoading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        getVerifyCode() {
          this.sendCodeParams.action = "admin_verify";
          this.verifyType === "phone"
            ? this.sendPhoneCode()
            : this.sendEmailCode();
        },
        getChangeCode() {
          this.sendCodeParams.action = "admin_update";
          this.verifyType === "phone"
            ? this.sendPhoneCode()
            : this.sendEmailCode();
        },
        sendPhoneCode() {
          apiSendPhoneCode(this.sendCodeParams)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                if (this.sendCodeParams.action === "admin_update") {
                  this.setChangeCodeTime();
                }
                if (this.sendCodeParams.action === "admin_verify") {
                  this.setVerifyCodeTime();
                }
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            });
        },

        setVerifyCodeTime() {
          this.isVerifySending = true;
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
        setChangeCodeTime() {
          this.isChangeSending = true;
          clearInterval(this.timer2);
          this.timer2 = null;
          this.timer2 = setInterval(() => {
            if (this.changeCodeTime === 0) {
              clearInterval(this.timer2);
              this.timer2 = null;
              this.changeCodeTime = 60;
              this.isChangeSending = false;
            } else {
              this.changeCodeTime--;
            }
          }, 1000);
        },

        sendEmailCode() {
          apiSendEmailCode(this.sendCodeParams)
            .then((res) => {
              if (res.data.status === 200) {
                // this.$message.success(res.data.msg);
                // this.setCodeTime();
                this.$message.success(res.data.msg);
                if (this.sendCodeParams.action === "admin_update") {
                  this.setChangeCodeTime();
                }
                if (this.sendCodeParams.action === "admin_verify") {
                  this.setVerifyCodeTime();
                }
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            });
        },
        handelVerify(type) {
          this.verifyType = type;
          this.verifyVisible = true;
        },
        onVerifySubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.verifyLoading = true;
            const subApi =
              this.verifyType === "phone"
                ? apiVerifyOldPhone
                : apiVerifyOldEmail;
            const params = {code: this.verifyFormData.code};
            subApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.verifyLoading = false;
                  this.verifyClose();
                  this.$message.success(res.data.msg);
                  this.changeVisible = true;
                }
              })
              .catch((error) => {
                this.verifyLoading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        verifyClose() {
          this.verifyVisible = false;
          this.$refs.verifyDialog && this.$refs.verifyDialog.reset();
        },
        handelChangeNikename() {
          this.editName = true;
        },
        saveNikename() {
          if (!this.editName) return;
          apiEditNikeName({nickname: this.nickname})
            .then((res) => {
              this.$message.success(res.data.msg);
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.editName = false;
            });
        },
        // type 1:修改登录密码 2:修改操作密码
        handelChangePass(type) {
          this.type = type;
          this.editPassVisible = true;
          setTimeout(() => {
            this.$refs.userDialog.reset();
          }, 0);
        },
        getLoginInfo() {
          getAdminInfo().then((res) => {
            this.prohibit_admin_bind_phone =
              res.data.data.prohibit_admin_bind_phone;
            this.prohibit_admin_bind_email =
              res.data.data.prohibit_admin_bind_email;
            this.set_operate_password = res.data.data.set_operate_password;
            this.nickname = res.data.data.nickname;
            this.admin_role_name = res.data.data.admin_role_name;
            this.verifyFormData.name = res.data.data.name;
            this.verifyFormData.email = res.data.data.email;
            this.verifyFormData.phone = res.data.data.phone;
            this.verifyFormData.phone_code = res.data.data.phone_code;
            this.totp_bind = res.data.data.totp_bind;
            this.sendCodeParams.name = res.data.data.name;
            this.sendCodeParams.phone_code = res.data.data.phone_code;
            if (!this.set_operate_password) {
              this.type = 2;
              this.editPassVisible = true;
            }
          });
        },
        // 修改密码相关
        // 关闭修改密码弹窗
        editPassClose() {
          this.editPassVisible = false;
          this.$refs.userDialog.reset();
        },
        // 修改密码提交
        onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.loading = true;
            const params =
              this.type === 1
                ? {
                    password: this.editPassFormData.password,
                    repassword: this.editPassFormData.repassword,
                    origin_password: this.editPassFormData.origin_password,
                  }
                : {
                    origin_operate_password:
                      this.editPassFormData.origin_password,
                    operate_password: this.editPassFormData.password,
                    re_operate_password: this.editPassFormData.repassword,
                  };
            const subApi = this.type === 1 ? editPass : changePassword;
            subApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.loading = false;
                  this.editPassClose();
                  this.getLoginInfo();
                  this.$message.success(res.data.msg);
                  this.type === 1 && this.handleLogout();
                }
              })
              .catch((error) => {
                this.loading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 确认密码检查
        checkPwd(val) {
          if (val !== this.editPassFormData.password) {
            return {
              result: false,
              message: lang.setting_text29,
              type: "error",
            };
          }
          return {result: true};
        },
        // 退出登录
        async handleLogout() {
          try {
            const res = await Axios.post("/logout");
            this.$message.success(res.data.msg);
            localStorage.removeItem("backJwt");
            setTimeout(() => {
              const host = location.origin;
              const fir = location.pathname.split("/")[1];
              const str = `${host}/${fir}/`;
              location.href = str + "login.htm";
            }, 300);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

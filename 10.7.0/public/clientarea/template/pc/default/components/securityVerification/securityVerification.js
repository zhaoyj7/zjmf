const securityVerification = {
  template: `
<div>
  <el-dialog width="6rem" :visible.sync="securityVisible" @close="closeSecurityDialog" custom-class="security-dialog"
    :show-close="false">
    <div class="security-title">
      <span class="title-text">安全验证</span>
      <span class="close-btn" @click="closeSecurityDialog">
        <i class="el-icon-close"></i>
      </span>
    </div>
    <div class="security-content">
      <el-form label-width="80px" ref="securityForm" :model="securityForm" label-position="top" :rules="currentRules">
        <el-form-item :label="lang.security_verify_text2" prop="method_id">
          <el-select v-model="securityForm.method_id" style="width: 100%;" @change="methodChange"
            :placeholder="lang.security_verify_text7">
            <el-option v-for="item in availableMethods" :key="item.value" :value="item.value" :label="item.label">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item :label="calcCurrentMethod?.label" v-if="securityForm.method_id === 'phone_code'"
          prop="phone_code">
          <div style="display: flex; align-items: center;gap: 10px;">
            <el-input v-model="securityForm.phone_code" :placeholder="calcCurrentMethod?.tip">
            </el-input>
            <count-down-button ref="securityPhoneCodeBtnRef" :loading="phoneCodeLoading" @click.native="sendPhoneCode"
              my-class="code-btn">
            </count-down-button>
          </div>
        </el-form-item>
        <el-form-item :label="calcCurrentMethod?.label" v-if="securityForm.method_id === 'email_code'"
          prop="email_code">
          <div style="display: flex; align-items: center;gap: 10px;">
            <el-input v-model="securityForm.email_code" :placeholder="calcCurrentMethod?.tip">
            </el-input>
            <count-down-button ref="securityEmailCodeBtnRef" :loading="emailCodeLoading" @click.native="sendEmailCode"
              my-class="code-btn">
            </count-down-button>
          </div>
        </el-form-item>
        <el-form-item :label="calcCurrentMethod?.label" v-if="securityForm.method_id === 'operate_password'"
          prop="operate_password">
          <el-input show-password v-model="securityForm.operate_password"
            :placeholder="calcCurrentMethod?.placeholder"></el-input>
        </el-form-item>
        <el-form-item :label="calcCurrentMethod?.label" v-if="securityForm.method_id === 'certification'"
          prop="certification">
          <div class="realname-verify-box">
            <div ref="realnameVerifyRef" id="realnameVerify"
              style="display: flex; align-items: center; justify-content: center;"></div>
            <p style="text-align: center;margin-top: 10px;">{{calcCurrentMethod?.tip}}</p>
          </div>
        </el-form-item>
      </el-form>
    </div>
    <div class="security-footer">
      <el-button type="primary" @click="confirmSecurity" :loading="confirmSecurityLoading">
        {{lang.finance_btn8}}
      </el-button>
      <el-button type="info" class="cancel-btn" @click="closeSecurityDialog">{{lang.finance_btn7}}</el-button>
    </div>
  </el-dialog>
  <captcha-dialog :is-show-captcha="isShowCaptcha" ref="securityCaptchaRef" captcha-id="security-captcha"
    @get-captcha-data="getData" @captcha-cancel="captchaCancel">
  </captcha-dialog>
</div>

    `,
  components: {
    captchaDialog,
    countDownButton,
  },
  props: {
    actionType: {
      type: String,
      default: "exception_login",
    },
  },
  data() {
    return {
      commonData: {},
      phoneCodeLoading: false, // 手机验证码loading
      emailCodeLoading: false, // 邮箱验证码loading
      securityVisible: false, // 安全验证弹窗是否显示
      confirmSecurityLoading: false, // 确认按钮loading
      realnameStatus: false, // true通过 false未通过
      verifying: false, // 是否正在验证
      pollingTimer: null, // 轮询定时器
      pollingTimeout: null, // 轮询超时定时器
      securityForm: {
        method_id: "", // 验证方式
        phone_code: "", // 手机验证码
        email_code: "", // 邮箱验证码
        operate_password: "", // 操作密码
        certification: "", // 实名验证
      },
      callbackFun: "", // 回调函数名称
      availableMethods: [], // 可用验证方式
      certify_id: "", // 认证ID
      certify_url: "", // 认证URL
      isShowCaptcha: false, //登录是否显示验证码弹窗
      codeAction: "",
    };
  },
  computed: {
    currentRules() {
      const { method_id } = this.securityForm;
      const base = {
        method_id: [{ required: true, message: lang.security_verify_text7 }],
      };
      if (method_id === "phone_code") {
        base.phone_code = [
          { required: true, message: lang.security_verify_text8 },
        ];
      }
      if (method_id === "email_code") {
        base.email_code = [
          { required: true, message: lang.security_verify_text9 },
        ];
      }
      if (method_id === "operate_password") {
        base.operate_password = [
          { required: true, message: lang.security_verify_text10 },
        ];
      }
      if (method_id === "certification") {
        base.certification = [
          { required: true, message: lang.security_verify_text11 },
        ];
      }
      return base;
    },
    calcCurrentMethod() {
      return (
        this.availableMethods.find(
          (item) => item.value === this.securityForm.method_id
        ) || {}
      );
    },
  },
  methods: {
    /**
     * @param  {String}  callbackFun 回调函数名称
     */
    openDialog(callbackFun, availableMethods = []) {
      this.callbackFun = callbackFun;
      this.availableMethods = availableMethods;
      this.account = availableMethods[0]?.account || "";
      this.phone_code = availableMethods[0]?.phone_code || "";
      this.confirmSecurityLoading = false;
      this.securityForm = {
        method_id: availableMethods[0]?.value || "",
        phone_code: "",
        email_code: "",
        operate_password: "",
        certification: "",
      };
      this.stopPolling();
      this.realnameStatus = false; // 重置为未通过状态
      this.verifying = false; // 重置为未验证状态
      this.securityVisible = true; // 显示安全验证弹窗
      this.$nextTick(() => {
        this.methodChange(availableMethods[0]?.value || "");
      });
    },
    closeSecurityDialog() {
      this.$refs.securityForm.resetFields();
      if (this.$refs.realnameVerifyRef) {
        this.$refs.realnameVerifyRef.innerHTML = "";
      }
      this.securityVisible = false;
      this.stopPolling();
    },
    confirmSecurity() {
      this.$refs.securityForm.validate((valid) => {
        if (valid) {
          if (this.securityForm.method_id === "certification") {
            // 判断实名状态
            if (!this.realnameStatus) {
              this.$message.error(lang.security_verify_text11);
              return;
            }
          }
          this.confirmSecurityLoading = true;
          this.$emit("confirm", this.callbackFun, {
            security_verify_method: this.securityForm.method_id,
            security_verify_value:
              this.securityForm[this.securityForm.method_id],
            certify_id:
              this.securityForm.method_id === "certification"
                ? this.certify_id
                : "",
            security_verify_token:
              this.calcCurrentMethod?.security_verify_token || "",
          });
          this.confirmSecurityLoading = false;
          this.closeSecurityDialog();
        }
      });
    },
    methodChange(value) {
      this.stopPolling();
      this.securityForm.phone_code = "";
      this.securityForm.email_code = "";
      this.securityForm.operate_password = "";
      this.securityForm.certification = "";
      this.account =
        this.availableMethods.find((item) => item.value === value)?.account ||
        "";
      this.phone_code =
        this.availableMethods.find((item) => item.value === value)
          ?.phone_code || "";
      if (value === "certification") {
        this.$nextTick(async () => {
          try {
            const res = await apiCreateCertification({
              account: this.account,
              phone_code: this.phone_code,
              security_verify_token:
                this.calcCurrentMethod?.security_verify_token || "",
            });
            this.certify_id = res.data.data.certify_id;
            this.certify_url = res.data.data.certify_url;
            this.generateQRCode(this.certify_url);
            this.startPolling();
          } catch (error) {
            console.error("创建认证失败:", error);
            this.$message.error(error?.data?.msg || "创建认证失败，请重试");
          }
        });
      }
    },
    sendPhoneCode(isAuto = false) {
      if (this.phoneCodeLoading) return;
      if (isAuto !== true) {
        this.token = "";
        this.captcha = "";
      }
      if (
        this.commonData.captcha_client_security_verify == 1 &&
        !this.captcha
      ) {
        this.codeAction = "phoneCode";
        this.isShowCaptcha = true;
        this.$refs.securityCaptchaRef.doGetCaptcha();
        return;
      }

      this.phoneCodeLoading = true;
      const params = {
        action: this.actionType,
        phone_code: this.phone_code ?? undefined,
        phone: this.account ?? undefined,
        token: this.token,
        captcha: this.captcha,
      };
      phoneCode(params)
        .then((res) => {
          if (res.data.status === 200) {
            this.phoneCodeLoading = false;
            // 执行倒计时
            this.$refs.securityPhoneCodeBtnRef.countDown();
            this.$message.success(res.data.msg);
          }
        })
        .catch((err) => {
          this.phoneCodeLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    sendEmailCode(isAuto = false) {
      if (this.emailCodeLoading) return;
      if (isAuto !== true) {
        this.token = "";
        this.captcha = "";
      }
      if (
        this.commonData.captcha_client_security_verify == 1 &&
        !this.captcha
      ) {
        this.codeAction = "emailCode";
        this.isShowCaptcha = true;
        this.$refs.securityCaptchaRef.doGetCaptcha();
        return;
      }
      this.emailCodeLoading = true;
      const params = {
        action: this.actionType,
        email: this.account,
        token: this.token,
        captcha: this.captcha,
      };
      emailCode(params)
        .then((res) => {
          this.$message.success(res.data.msg);
          this.emailCodeLoading = false;
          this.$refs.securityEmailCodeBtnRef.countDown();
        })
        .catch((err) => {
          this.emailCodeLoading = false;
          this.$message.error(err.data.msg);
        });
    },

    loadQRCodeLib() {
      return new Promise((resolve) => {
        const script = document.createElement("script");
        script.src = `${url}js/common/qrcode.min.js`;
        script.onload = resolve;
        document.body.appendChild(script);
      });
    },
    // 生成二维码
    generateQRCode(url) {
      if (!url) return;

      // 清空之前的二维码
      const container = this.$refs.realnameVerifyRef;
      if (container) {
        // 显示加载状态
        container.innerHTML =
          '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">生成中...</div>';

        try {
          // 使用QRCode.js库生成二维码（需要确保已引入）
          if (typeof QRCode !== "undefined") {
            container.innerHTML = "";
            new QRCode(container, {
              text: url,
              width: 200,
              height: 200,
              colorDark: "#000000",
              colorLight: "#ffffff",
              correctLevel: QRCode.CorrectLevel.M,
            });
          }
        } catch (error) {
          console.error("二维码生成错误:", error);
          container.innerHTML =
            '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #f56c6c;">二维码生成失败</div>';
        }
      }
    },
    // 开始轮询认证状态
    startPolling() {
      this.verifying = true;
      this.realnameStatus = false; // 重置为未通过状态
      // 清除之前的轮询
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer);
      }
      // 开始轮询，每3秒检查一次
      this.pollingTimer = setInterval(async () => {
        try {
          const result = await apiGetCertificationStatus({
            certify_id: this.certify_id,
            account: this.account,
            phone_code: this.phone_code,
            security_verify_token:
              this.calcCurrentMethod?.security_verify_token || "",
          });

          if (result?.data?.data?.verify_status === 1) {
            // 认证成功
            this.realnameStatus = true;
            this.verifying = false;
            this.stopPolling();
            this.securityForm.certification = this.certify_id;
            this.confirmSecurity();
          }
        } catch (error) {
          this.verifying = false;
          this.stopPolling();
          this.$message.error("认证状态检查失败");
        }
      }, 3000);

      // 设置最大轮询时间（5分钟后自动停止）
      this.pollingTimeout = setTimeout(() => {
        if (this.pollingTimer) {
          this.stopPolling();
          this.verifying = false;
          this.realnameStatus = false;
          this.$message.warning("认证超时，请重试");
        }
      }, 300000);
    },

    // 停止轮询
    stopPolling() {
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer);
        this.pollingTimer = null;
      }
      if (this.pollingTimeout) {
        clearTimeout(this.pollingTimeout);
        this.pollingTimeout = null;
      }
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(
        localStorage.getItem("common_set_before") || "{}"
      );
    },
    // 验证码验证成功后的回调
    getData(captchaCode, token) {
      this.isShowCaptcha = false;
      this.token = token;
      this.captcha = captchaCode;
      if (this.codeAction === "emailCode") {
        this.sendEmailCode(true);
      } else if (this.codeAction === "phoneCode") {
        this.sendPhoneCode(true);
      }
    },
    // 验证码 关闭
    captchaCancel() {
      this.isShowCaptcha = false;
    },
  },
  async mounted() {
    this.getCommonData();
    await this.loadQRCodeLib();
  },
  // 组件销毁时清理定时器
  beforeDestroy() {
    this.stopPolling();
  },
};

// 加载组件样式
(function () {
  if (!document.querySelector('link[href*="resetAuth.css"]')) {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = `${url}components/resetAuth/resetAuth.css`;
    document.head.appendChild(link);
  }
})();

const resetAuth = {
  template: `
        <div>
          <!-- 授权 -->
          <div class="common-auth-info">
            <div class="top">
              <p class="tit">{{lang.reset_auth_detail_title}}</p>
              <span class="reset-auth" @click="handleResetAuth" v-if="hasRestAuth && authDialog.status !== 'pending'">{{lang.reset_auth_reset_button}}</span>
            </div>
            <div class="bot-info" v-if="hasRestAuth">
              <span class="label">{{lang.reset_auth_status_label}}：</span>
              <div class="status">
                <div class="status reject" v-if="authDialog.status === 'rejected'">{{lang.reset_auth_status_failed}}（{{authDialog.reject_reason}}）
                </div>
                <div class="status pending" v-if="authDialog.status === 'pending'">{{lang.reset_auth_status_pending}}<span class="cancel-auth"
                    @click="handleCancelAuth">{{lang.reset_auth_cancel_reset}}</span></div>
                <div class="status approved" v-if="authDialog.status === 'approved'">{{lang.reset_auth_status_normal}}</div>
              </div>
            </div>
          </div>
          <!-- 授权重置弹窗 -->
          <el-dialog :visible.sync="authDialog.visible" width="800px"
            :close-on-click-modal="false" custom-class="common-auth-dialog " @close="closeAuthDialog">
            <div class="dialog-title">
              {{authDialogTitle}}
            </div>
            <div v-if="authDialog.currentStep === 'confirm'">
              <p class="tip">{{lang.reset_auth_modify_tip}}</p>
            </div>
            <el-form :model="authDialog" status-icon :rules="rules" ref="ruleForm" label-width="100px"
              label-position="top" class="demo-ruleForm">
              <template v-if="authDialog.currentStep !== 'autoVerify'">
                <div class="auth-info-display">
                  <div class="base-info">
                    <div class="item">
                      <p class="s-tit">{{lang.reset_auth_current_info}}：</p>
                      <div class="info-row">
                        <span class="label">{{lang.reset_auth_ip_label}}：</span>
                        <span class="value">{{authDialog.original_ip || '--'}}</span>
                      </div>
                      <div class="info-row">
                        <span class="label">{{lang.reset_auth_domain_label}}：</span>
                        <span class="value">{{authDialog.original_domain || '--'}}</span>
                      </div>
                    </div>
                    <div class="item"
                      v-if="authDialog.currentStep === 'selectMethod' || authDialog.currentStep === 'manualReview'">
                      <p class="s-tit">{{lang.reset_auth_new_info}}：</p>
                      <div class="info-row">
                        <span class="label">{{lang.reset_auth_ip_label}}：</span>
                        <span class="value">{{authDialog.new_ip || '--'}}</span>
                      </div>
                      <div class="info-row">
                        <span class="label">{{lang.reset_auth_domain_label}}：</span>
                        <span class="value">{{authDialog.new_domain || '--'}}</span>
                      </div>
                    </div>
                  </div>
                  <template v-if="authDialog.currentStep === 'confirm'">
                    <el-form-item :label="lang.reset_auth_new_ip" prop="new_ip">
                      <el-input v-model="authDialog.new_ip" :placeholder="lang.reset_auth_ip_placeholder"></el-input>
                    </el-form-item>
                    <el-form-item :label="lang.reset_auth_new_domain" prop="new_domain">
                      <el-input v-model="authDialog.new_domain" :placeholder="lang.reset_auth_domain_placeholder"></el-input>
                    </el-form-item>
                    <p class="domain-tip">{{lang.reset_auth_domain_tip}}</p>
                    <p class="domain-tip">{{lang.reset_auth_domain_example}}</p>
                  </template>
                </div>
              </template>
              <!-- 申请理由 -->
              <div class="reason-section" v-if="authDialog.currentStep !== 'confirm' && authDialog.currentStep !== 'autoVerify'">
                <el-form-item :label="lang.reset_auth_apply_reason" prop="reset_reason">
                  <el-input type="textarea" v-model="authDialog.reset_reason" :rows="4" :maxlength="300"
                    show-word-limit :placeholder="lang.reset_auth_reason_placeholder">
                  </el-input>
                </el-form-item>
              </div>

              <!-- 方式选择 -->   
              <div v-if="authDialog.currentStep === 'selectMethod'">
                <p class="tip">{{lang.reset_auth_select_verify_tip}}</p>
                <div class="method-selection">
                  <div class="method-card" @click="selectVerifyMethod('manual')">
                    <h4>{{lang.reset_auth_manual_review}}</h4>
                    <p>{{lang.reset_auth_manual_review_desc}}</p>
                    <el-button>{{lang.reset_auth_submit_manual}}</el-button>
                  </div>
                  <div class="method-card" @click="selectVerifyMethod('auto')">
                    <h4>{{lang.reset_auth_auto_reset}}</h4>
                    <p>{{lang.reset_auth_auto_reset_desc}}</p>
                    <el-button>{{lang.reset_auth_start_auto}}</el-button>
                  </div>
                </div>
              </div>

              <!-- 自动扫码认证 -->
              <div class="verify-content" v-if="authDialog.currentStep === 'autoVerify'">
                <p class="tip">{{lang.reset_auth_verify_tip}}</p>
                <p class="tip primary-color">{{lang.reset_auth_current_verify_info}}</p>
                <div class="user-info">
                  <div class="info-row">
                    <span class="label">{{lang.reset_auth_current_user}}：</span>
                    <span class="value primary-color">{{authDialog.userInfo.username}}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">{{lang.reset_auth_real_name}}：</span>
                    <span class="value primary-color">{{authDialog.userInfo.card_name}}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">{{lang.reset_auth_id_card}}：</span>
                    <span class="value primary-color">{{authDialog.userInfo.card_number}}</span>
                  </div>
                </div>
                <div class="qr-section">
                  <p>{{lang.reset_auth_scan_tip}}</p>
                  <div class="qr-code">
                    <div id="qrcode-container">
                      <!-- 二维码将在这里生成 -->
                    </div>
                  </div>
                  <div class="verify-status">
                    <el-link :loading="verifying" type="primary" :underline="false" v-if="realnameStatus === 2">
                      <i class="el-icon-loading"></i>
                      {{lang.reset_auth_waiting_verify}}
                    </el-link>
                    <el-link type="success" :underline="false" v-if="realnameStatus === 1">{{lang.reset_auth_verify_success}}</el-link>
                  </div>
                </div>
              </div>
              <!-- 动态底部按钮 -->
              <div class="dialog-footer">
                <template v-if="authDialog.currentStep === 'confirm'">
                  <el-button type="primary" @click="confirmAuthChange" :loading="submitLoading">{{lang.reset_auth_confirm_modify}}</el-button>
                  <el-button @click="closeAuthDialog">{{lang.reset_auth_cancel}}</el-button>
                </template>

                <template v-if="authDialog.currentStep === 'selectMethod'">
                  <el-button @click="goBackStep">{{lang.reset_auth_back}}</el-button>
                  <el-button @click="closeAuthDialog">{{lang.reset_auth_cancel}}</el-button>
                </template>
                <div v-show="authDialog.currentStep === 'autoVerify'">
                  <el-button type="primary" @click="handleSure" :disabled="realnameStatus === 2" :loading="submitLoading">{{lang.reset_auth_confirm_apply}}</el-button>
                  <el-button @click="closeAuthDialog">{{lang.reset_auth_cancel}}</el-button>
                </div>
                <!-- 人工审核提交转移到选择方式的时候直接提交
                  <template v-if="authDialog.currentStep === 'manualReview'">
                    <el-button type="primary" @click="submitReset" :loading="submitLoading">{{lang.reset_auth_submit_apply}}</el-button>
                    <el-button @click="closeAuthDialog">{{lang.reset_auth_cancel}}</el-button>
                  </template>
                -->
              </div>
            </el-form>
          </el-dialog>

        </div>
        `,
  data() {
    return {
      hasRestAuth: false,
      authDialog: {
        visible: false,
        currentStep: "confirm", // confirm -> selectMethod -> autoVerify/manualReview
        host_id: "",
        original_ip: "",
        original_domain: "",
        status: "",
        application_id: "",
        new_ip: "",
        new_domain: "",
        userInfo: {
          username: "",
          card_name: "",
          card_number: "",
          url: "",
          certify_id: "",
        },
        reset_reason: "",
        verifying: false,
      },
      // 实名状态
      realnameStatus: 2, // 1通过 2未通过
      verifying: true,
      rules: {
        new_ip: [
          {
            required: false,
            pattern:
              /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
            message: this.lang.reset_auth_ip_format_error,
            trigger: "blur",
          },
        ],
        new_domain: [
          {
            required: false,
            pattern:
              /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/,
            message: this.lang.reset_auth_domain_format_error,
            trigger: "blur",
          },
        ],
        reset_reason: [
          {
            required: true,
            message: this.lang.reset_auth_reason_required,
            trigger: "change",
          },
        ],
      },
      submitLoading: false,
      pollingTimer: null, // 轮询定时器
      pollingTimeout: null, // 轮询超时定时器
      qrCodeLibLoaded: false, // QRCode库是否已加载
    };
  },
  components: {},
  props: {
    info: {
      type: Object,
      required: true,
      default: () => {
        return {
          reject_reason: "", // 授权信息里面回显的拒绝理由
          host_id: "",
          original_ip: "",
          original_domain: "",
          status: "",
          reset_reason: "", // 申请重置的理由
          application_id: "",
        };
      },
    },
  },
  created() {
    if (this.info && Object.keys(this.info).length > 0) {
      Object.assign(this.authDialog, this.info);
    }
  },
  watch: {
    info: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          Object.assign(this.authDialog, newVal);
        }
      },
      deep: true,
      immediate: false,
    },
  },
  computed: {
    authDialogTitle() {
      const titleMap = {
        confirm: this.lang.reset_auth_modify_title,
        selectMethod: this.lang.reset_auth_modify_title,
        autoVerify: this.lang.reset_auth_auto_reset,
        manualReview: this.lang.reset_auth_manual_review,
      };
      return (
        titleMap[this.authDialog.currentStep] ||
        this.lang.reset_auth_modify_title
      );
    },
    // 检查是否有授权信息变更
    hasAuthChanges() {
      const hasIPChange =
        this.authDialog.new_ip.trim() !== "" &&
        this.authDialog.new_ip !== this.authDialog.original_ip;
      const hasDomainChange =
        this.authDialog.new_domain.trim() !== "" &&
        this.authDialog.new_domain !== this.authDialog.original_domain;
      return hasIPChange || hasDomainChange;
    },
  },
  mixins: [mixin],
  mounted() {
    this.hasRestAuth = this.addons_js_arr.includes("IdcsmartAuthreset");
  },
  methods: {
    /* 授权相关方法 */
    // 打开授权重置弹窗
    handleResetAuth() {
      this.authDialog.visible = true;
      this.authDialog.currentStep = "confirm";
      // 重置表单数据
      this.authDialog.new_ip = this.authDialog.original_ip;
      this.authDialog.new_domain = this.authDialog.original_domain;
      this.authDialog.reset_reason = "";
      this.authDialog.verifying = false;
    },

    /* 撤销重置 */
    handleCancelAuth() {
      this.$confirm(this.lang.reset_auth_cancel_confirm)
        .then(async () => {
          try {
            const res = await apiCancelRest({
              application_id: this.authDialog.application_id,
            });
            this.$message.success(res.data.msg);
            this.$emit("cancel-auth");
          } catch (error) {
            this.$message.error(error.data?.msg);
          }
        })
        .catch((_) => {});
    },
    // 确认授权修改
    confirmAuthChange() {
      if (!this.hasAuthChanges) {
        this.submitReset();
      } else {
        this.$refs.ruleForm.validate((valid) => {
          if (!valid) {
            this.$message.error("请检查输入信息格式");
            return;
          }
          this.authDialog.currentStep = "selectMethod";
        });
      }
    },

    // 选择验证方式
    selectVerifyMethod(method) {
      this.authDialog.reset_method = method;
      this.submitReset();
    },

    // 开始自动验证
    async startAutoVerify() {
      try {
        // application_id 提交申请过后返回的 application_id
        const params = {
          application_id: this.authDialog.application_id,
        };
        const res = await apiGetRealName(params);
        this.authDialog.userInfo = res.data.data;

        if (!this.authDialog.userInfo.url) {
          this.realnameStatus = 2;
          return this.$message.error("获取认证链接失败");
        }
        // 1. 根据 this.authDialog.userInfo.url生成二维码
        this.generateQRCode(this.authDialog.userInfo.url);

        // 2. 开始轮询状态
        this.startPolling();
      } catch (error) {
        this.$message.error(error.data?.msg);
      }
    },

    // 加载QRCode库
    loadQRCodeLib() {
      return new Promise((resolve) => {
        if (typeof QRCode !== "undefined") {
          this.qrCodeLibLoaded = true;
          resolve();
          return;
        }
        // 检查是否已有加载中的脚本
        if (document.querySelector('script[src*="qrcode.min.js"]')) {
          const checkLoaded = setInterval(() => {
            if (typeof QRCode !== "undefined") {
              clearInterval(checkLoaded);
              this.qrCodeLibLoaded = true;
              resolve();
            }
          }, 100);
          return;
        }
        const script = document.createElement("script");
        script.src = `${url}js/common/qrcode.min.js`;
        script.onload = () => {
          this.qrCodeLibLoaded = true;
          resolve();
        };
        document.body.appendChild(script);
      });
    },

    // 生成二维码
    async generateQRCode(qrUrl) {
      if (!qrUrl) return;

      const container = document.getElementById("qrcode-container");
      if (!container) return;

      // 显示加载状态
      container.innerHTML = '<div class="qrcode-status loading">生成中...</div>';

      try {
        // 确保QRCode库已加载
        if (!this.qrCodeLibLoaded) {
          await this.loadQRCodeLib();
        }

        if (typeof QRCode !== "undefined") {
          container.innerHTML = "";
          new QRCode(container, {
            text: qrUrl,
            width: 150,
            height: 150,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M,
          });
        } else {
          container.innerHTML = '<div class="qrcode-status error">二维码库加载失败</div>';
        }
      } catch (error) {
        console.error("二维码生成错误:", error);
        container.innerHTML = '<div class="qrcode-status error">二维码生成失败</div>';
      }
    },

    // 开始轮询认证状态
    startPolling() {
      this.verifying = true;
      this.realnameStatus = 2; // 重置为未通过状态
      // 清除之前的轮询
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer);
      }
      // 开始轮询，每3秒检查一次
      this.pollingTimer = setInterval(async () => {
        try {
          const result = await apiGetRealNameStatus({
            application_id: this.authDialog.application_id,
            certify_id: this.authDialog.userInfo.certify_id,
          });

          if (result.data && result.data.code === 1) {
            // 认证成功
            this.realnameStatus = 1;
            this.verifying = false;
            this.stopPolling();
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
          this.realnameStatus = 2;
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
    async handleSure() {
      try {
        this.submitLoading = true;
        const res = await apiAutoResetSure({
          application_id: this.authDialog.application_id,
        });
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        this.closeAuthDialog();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data?.msg);
      }
    },
    // 提交审核申请
    submitReset() {
      this.$refs.ruleForm.validate(async (valid) => {
        if (valid) {
          try {
            let params = {};
            if (this.authDialog.currentStep !== "confirm") {
              params = {
                new_ip: this.authDialog.new_ip,
                new_domain: this.authDialog.new_domain,
                reset_reason: this.authDialog.reset_reason,
                reset_method: this.authDialog.reset_method,
              };
            }
            this.submitLoading = true;
            params.host_id = this.authDialog.host_id;
            const res = await apiRestAuth(params);
            this.$message.success(res.data.msg);

            const {application_id} = res.data.data;
            this.authDialog.application_id = application_id;
            this.submitLoading = false;
            if (params.reset_method === "auto") {
              this.authDialog.currentStep = "autoVerify";
              this.startAutoVerify();
            } else {
              this.closeAuthDialog();
            }
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data?.msg);
          }
        } else {
          return false;
        }
      });
    },
    // 返回上一步
    goBackStep() {
      const stepFlow = {
        selectMethod: "confirm",
        autoVerify: "selectMethod",
        manualReview: "selectMethod",
      };
      this.authDialog.currentStep =
        stepFlow[this.authDialog.currentStep] || "confirm";
    },

    // 关闭弹窗
    closeAuthDialog() {
      // 避免重复触发
      if (!this.authDialog.visible) return;
      // 停止轮询
      this.stopPolling();
      // 重置状态
      this.verifying = false;
      this.realnameStatus = 2;
      // 清空二维码
      const container = document.getElementById("qrcode-container");
      if (container) {
        container.innerHTML = "";
      }
      // 关闭弹窗
      this.authDialog.visible = false;
      this.authDialog.currentStep = "confirm";
      // 重新拉取授权信息
      this.$emit("cancel-auth");
    },
    /* 授权相关方法 end */
  },

  // 组件销毁时清理定时器
  beforeDestroy() {
    this.stopPolling();
  },
};

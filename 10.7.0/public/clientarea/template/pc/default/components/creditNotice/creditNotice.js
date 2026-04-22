const creditNotice = {
  template: /*html*/ `
    <el-dialog width="7rem" :visible.sync="isShow" @close="diaClose" custom-class="credit-notice-dialog"
    :show-close="false">
    <div class="credit-content">
        <div class="credit-title">
            <span class="title-text">{{lang.coin_text67}}</span>
            <span class="close-btn" @click="diaClose">
                <i class="el-icon-close"></i>
            </span>
        </div>
        <div class="credit-box">
            <div class="credit-open">
                {{lang.coin_text66}}：
                <el-switch v-model="credit_remind" :active-value="1" :inactive-value="0">
                </el-switch>
            </div>
            <div class="credit-input">
                {{lang.coin_text68}}
                <el-input-number v-model="credit_remind_amount" :min="0" :precision="2" :step="0.01" :controls="false"
                    :placeholder="lang.coin_text70">
                </el-input-number>
                {{lang.coin_text69}}
            </div>
        </div>
        <div class="credit-footer">
            <el-button class="cancel-btn" @click="diaClose">{{lang.referral_btn7}}</el-button>
            <el-button type="primary" @click="submitCredit" :loading="submitLoading">{{lang.referral_btn6}}</el-button>
        </div>
    </div>
</el-dialog>

    `,

  data() {
    return {
      isShow: false,
      credit_remind_amount: undefined,
      submitLoading: false,
      credit_remind: 0,
      coinClientCoupon: {},
    };
  },
  methods: {
    open() {
      this.isShow = true;
      accountDetail().then((res) => {
        const {credit_remind, credit_remind_amount} = res.data.data.account;
        this.credit_remind = credit_remind;
        this.credit_remind_amount = credit_remind_amount;
      });
    },
    diaClose() {
      this.amount = undefined;
      this.isShow = false;
    },
    submitCredit() {
      if (!this.credit_remind_amount) {
        return this.$message.error(lang.coin_text70);
      }
      this.submitLoading = true;
      apiCreateCreditRemind({
        credit_remind: this.credit_remind,
        credit_remind_amount: this.credit_remind_amount,
      })
        .then((res) => {
          if (res.data.status === 200) {
            this.$message.success(res.data.msg);
            this.diaClose();
            this.$emit("success", {
              credit_remind: this.credit_remind,
              credit_remind_amount: this.credit_remind_amount,
            });
          }
        })
        .catch((error) => {
          this.$message.error(error.data.msg);
        })
        .finally(() => {
          this.submitLoading = false;
        });
    },
  },
};

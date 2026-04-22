const rechargeDialog = {
  template: /*html*/ `
  <div>
  <el-dialog width="7.5rem" :visible.sync="isShowCz" @close="czClose" custom-class="recharge-dialog"
      :show-close="false">
      <div class="recharge-content">
          <div class="recharge-title">
              <span class="title-text">{{lang.finance_title4}}</span>
              <span class="close-btn" @click="czClose">
                  <i class="el-icon-close"></i>
              </span>
          </div>
          <div class="recharge-box">
              <div class="recharge-input">
                  <el-input-number v-model="amount" :min="0" :precision="2" :step="0.01" :controls="false"
                      :placeholder="lang.finance_text130">
                  </el-input-number>
                  <el-button @click="handleSubmit" type="primary" :loading="submitLoading">{{lang.finance_btn6}}
                  </el-button>
              </div>
              <template v-if="rechargeActive.length > 0">
                  <div class="recharge-tip">
                      <template v-for="(item, index) in rechargeTip">
                          <span>{{item}}</span>
                          <template v-if="index !== rechargeTip.length - 1">
                              <br />
                          </template>
                      </template>
                  </div>
                  <div class="recharge-active">
                      <div class="active-title">{{lang.coin_text12}}<el-tooltip effect="dark" placement="top"
                      v-if="coinClientCoupon.coin_description_open == 1">
                      <div slot="content" v-html="coinClientCoupon.coin_description"></div>
                      <svg t="1745803081479" viewBox="0 0 1024 1024" version="1.1"
                        xmlns="http://www.w3.org/2000/svg" p-id="14138" width="16" height="16"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                        <path
                          d="M512 97.52381c228.912762 0 414.47619 185.563429 414.47619 414.47619s-185.563429 414.47619-414.47619 414.47619S97.52381 740.912762 97.52381 512 283.087238 97.52381 512 97.52381z m0 73.142857C323.486476 170.666667 170.666667 323.486476 170.666667 512s152.81981 341.333333 341.333333 341.333333 341.333333-152.81981 341.333333-341.333333S700.513524 170.666667 512 170.666667z m45.32419 487.619047v73.142857h-68.510476l-0.024381-73.142857h68.534857z m-4.047238-362.008381c44.251429 8.923429 96.889905 51.126857 96.889905 112.518096 0 61.415619-50.151619 84.650667-68.120381 96.134095-17.993143 11.50781-24.722286 24.771048-24.722286 38.863238V609.52381h-68.534857v-90.672762c0-21.504 6.89981-36.571429 26.087619-49.883429l4.315429-2.852571 38.497524-25.6c24.551619-16.530286 24.210286-49.712762 9.020952-64.365715a68.998095 68.998095 0 0 0-60.391619-15.481904c-42.715429 8.387048-47.640381 38.521905-47.932952 67.779047v16.554667H390.095238c0-56.953905 6.534095-82.773333 36.912762-115.395048 34.03581-36.449524 81.993143-42.300952 126.268952-33.328762z"
                          p-id="14139" fill="currentColor"></path>
                      </svg>
                    </el-tooltip></div>
                      <div class="active-main">
                          <div class="active-list">
                              <div class="active-item" v-for="item in rechargeActive" :key="item.id">
                                  <div class="active-name">
                                      <span>{{item.name}}</span>
                                      <span class="active-time">
                                          <template v-if="item.begin_time == 0">
                                              {{item.begin_time | formateTime}}
                                          </template>
                                          <template v-else>
                                              {{item.begin_time | formateTime}} - {{item.end_time | formateTime}}
                                          </template>
                                      </span>
                                  </div>
                                  <div class="gradient-content" v-if="item.type === 'gradient'">
                                      <div class="gradient-item"
                                          :class="{active: amount *1 >= items.amount * 1 && (index == item.return.length - 1 || amount * 1 < item.return[index + 1].amount * 1)}"
                                          v-for="(items, index) in item.return" :key="items.id"
                                          @click="amount = items.amount">
                                          <div class="gradient-money">
                                              <span class="s-12">{{currency_prefix}}</span>{{items.amount}}
                                          </div>
                                          <div class="gradient-award ">
                                              {{lang.coin_text13}}{{items.amount}}{{lang.coin_text14}}{{items.award}}{{coinClientCoupon.name}}
                                              <i class="el-icon-check active-icon"></i>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="gradient-content" v-if="item.type === 'proportion'">
                                      <div class="gradient-item" :class="{active:amount * 1 >= item.recharge_min * 1}"
                                          @click="amount = item.recharge_min">
                                          <div class="gradient-money">
                                              <span class="s-12">{{currency_prefix}}</span>{{item.recharge_min}}
                                          </div>
                                          <div class="gradient-award ">
                                              {{lang.coin_text15}}{{item.recharge_min}}<br>{{lang.coin_text16}}{{Number(item.recharge_proportion)}}%{{coinClientCoupon.name}}
                                              <i class="el-icon-check active-icon"></i>

                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </template>
              <div v-if="commonData.recharge_money_notice_content" v-html="commonData.recharge_money_notice_content"
                  class="cz-notice">
              </div>
          </div>
      </div>
  </el-dialog>
  <pay-dialog ref="payDialog" @payok="paySuccess"></pay-dialog>
</div>
    `,

  data() {
    return {
      isShowCz: false,
      currency_prefix: "",
      currency_suffix: "",
      commonData: {},
      amount: undefined,
      submitLoading: false,
      rechargeActive: [],
      coinClientCoupon: {},
    };
  },
  components: {
    payDialog,
  },
  created() {
    this.getCommon();
  },
  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else if (time === 0) {
        return lang.coin_text17;
      } else {
        return "--";
      }
    },
  },
  computed: {
    rechargeTip() {
      if (this.rechargeActive.length === 0 || !this.amount) {
        return [];
      }
      const rechargeTipList = [];
      const maxCoinAward = Number(
        this.coinClientCoupon.per_recharge_get_coin_max
      );
      this.rechargeActive.forEach((item) => {
        if (item.type === "proportion") {
          if (this.amount * 1 >= item.recharge_min * 1) {
            const award = Number(item.recharge_proportion * 0.01 * this.amount);
            const tip = `${item.name}：${lang.coin_text18}${Number(
              award > maxCoinAward ? maxCoinAward : award
            ).toFixed(2)}${this.coinClientCoupon.name}`;
            rechargeTipList.push(tip);
          } else {
            const tip = `${item.name}：${lang.coin_text19}${Number(
              item.recharge_min - this.amount
            ).toFixed(2)}${this.currency_suffix}${lang.coin_text20}${Number(
              item.recharge_proportion * 0.01 * item.recharge_min
            ).toFixed(2)}${this.coinClientCoupon.name}`;
            rechargeTipList.push(tip);
          }
        }
        if (item.type === "gradient") {
          // 找出最大的阶梯金额
          const maxAmount = Math.max(
            ...item.return.map((items) => items.amount)
          );
          if (this.amount * 1 >= maxAmount * 1) {
            const maxAward = item.return.find(
              (items) => items.amount === maxAmount
            )?.award;
            const tip = `${item.name}：${lang.coin_text18}${Number(
              maxAward > maxCoinAward ? maxCoinAward : maxAward
            ).toFixed(2)}${this.coinClientCoupon.name}`;
            rechargeTipList.push(tip);
          } else {
            // 找出当前充值金额对应的阶梯 和 下一个阶梯
            const currentIndex = item.return.findIndex((items, index) => {
              return (
                this.amount * 1 >= items.amount * 1 &&
                this.amount * 1 < item.return[index + 1]?.amount * 1
              );
            });
            const currentAmount = item.return[currentIndex];
            const nextAmount = item.return[currentIndex + 1];
            if (currentAmount && nextAmount) {
              const currentAward =
                currentAmount.award > maxCoinAward
                  ? maxCoinAward
                  : currentAmount.award;
              const nextAward =
                nextAmount.award > maxCoinAward
                  ? maxCoinAward
                  : nextAmount.award;
              const tip = `${item.name}：${lang.coin_text21}${Number(
                currentAward
              ).toFixed(2)}${this.coinClientCoupon.name}，${
                lang.coin_text22
              }${Number(nextAmount.amount - this.amount).toFixed(2)}${
                this.currency_suffix
              }${lang.coin_text20}${Number(nextAward).toFixed(2)}${
                this.coinClientCoupon.name
              }`;
              rechargeTipList.push(tip);
            }
          }
        }
      });
      return rechargeTipList;
    },
  },

  methods: {
    getCoinClientCoupon() {
      apiCoinClientCoupon().then((res) => {
        this.coinClientCoupon = res.data.data;
      });
    },
    getRechargeDetail() {
      apiCoinRechargeDetail().then((res) => {
        this.rechargeActive = res.data.data.coins;
      });
    },
    open() {
      if (havePlugin("Coin")) {
        this.getRechargeDetail();
        this.getCoinClientCoupon();
      }
      this.isShowCz = true;
    },
    czClose() {
      this.amount = undefined;
      this.isShowCz = false;
    },
    handleSubmit() {
      if (!this.amount) {
        return this.$message.error(lang.finance_text130);
      }
      this.submitLoading = true;
      apiRecharge({amount: this.amount})
        .then((res) => {
          if (res.data.status === 200) {
            this.isShowCz = false;
            const orderId = res.data.data.id;
            this.$refs.payDialog.czPay(orderId);
          }
        })
        .catch((error) => {
          this.$message.error(error.data.msg);
        })
        .finally(() => {
          this.submitLoading = false;
        });
    },
    paySuccess() {
      this.$emit("success");
    },

    getCommon() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      this.currency_prefix = this.commonData.currency_prefix;
      this.currency_suffix = this.commonData.currency_suffix;
    },
  },
};

/* 详情页续费弹窗（包含cloud按需转包年包月） */
const renewDialog = {
  template: /*html*/
    `
    <div class="common-renew-dialog">
      <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close="false" @close="renewDgClose">
        <div class="dialog-title">{{renewTitle}}</div>
        <div class="dialog-main">
          <div class="renew-content">
            <div class="renew-item" :class="renewActiveId==(byIndex ? index : item.id)?'renew-active':''" v-for="(item, index) in renewPageData"
              :key="item.id || index" @click="renewItemChange(item,index)">
              <div class="item-top">{{item.customfield?.multi_language?.billing_cycle || item.billing_cycle}}</div>
              <div class="item-bottom" v-if="hasShowPromo && renewParams.isUseDiscountCode">
                {{commonData.currency_prefix + item.base_price}}
              </div>
              <div class="item-bottom" v-else>{{commonData.currency_prefix + item.price}}</div>
              <div class="item-origin-price"
                v-if="item.price*1 < item.base_price*1 && !renewParams.isUseDiscountCode">
                {{commonData.currency_prefix + item.base_price}}
              </div>
              <i class="el-icon-check check" v-show="renewActiveId==(byIndex ? index : item.id)"></i>
            </div>
          </div>
          <div class="pay-content">
            <div class="pay-price">
              <div class="money" v-loading="renewLoading">
                <span class="text">{{lang.common_cloud_label11}}:</span>
                <span>{{commonData.currency_prefix}}{{renewParams.totalPrice | filterMoney}}</span>
                <el-popover placement="top-start" width="200" trigger="hover" v-if="(isShowLevel && renewParams.clDiscount*1 > 0) 
                  || (hasShowPromo && renewParams.isUseDiscountCode)">
                  <div class="show-config-list">
                    <p v-if="isShowLevel && renewParams.clDiscount*1 > 0">
                      {{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}}
                      {{ renewParams.clDiscount | filterMoney}}
                    </p>
                    <p v-if="hasShowPromo && renewParams.isUseDiscountCode">
                      {{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}}
                      {{ renewParams.code_discount | filterMoney }}
                    </p>
                  </div>
                  <i class="el-icon-warning-outline total-icon" slot="reference"></i>
                </el-popover>
                <p class="original-price"
                  v-if="renewParams.customfield.promo_code && renewParams.totalPrice != renewParams.base_price">
                  {{commonData.currency_prefix}} {{ renewParams.base_price | filterMoney}}
                </p>
                <p class="original-price"
                  v-if="!renewParams.customfield.promo_code && renewParams.totalPrice != renewParams.original_price">
                  {{commonData.currency_prefix}} {{ renewParams.original_price | filterMoney}}
                </p>
                <div class="code-box">
                  <!-- 优惠码 -->
                  <discount-code v-show="hasShowPromo"
                    ref="discountCode"
                    @get-discount="getRenewDiscount(arguments)"
                    @remove-discount="removeRenewDiscountCode"
                    :scene="isDemandFee ? 'change_billing_cycle' : 'renew'"
                    :product_id="productId"
                    :amount="renewParams.base_price" 
                    :billing_cycle_time="renewParams.duration">
                  </discount-code>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="dialog-footer">
          <el-button :loading="submitLoading" type="primary" class="btn-ok" @click="subRenew">
            {{isDemandFee ? lang.mf_demand_tip9 : lang.auto_renew_sure}}
          </el-button>
          <div class="btn-no" @click="renewDgClose">{{lang.pay_text12}}</div>
        </div>
      </el-dialog>
    </div>
  `,

  data () {
    return {
      isShowRenew: false,
      lang: window.lang || {},
      commonData: {},
      submitLoading: false,
      renewPageData: [],
      renewActiveId: 0,
      renewParams: {
        billing_cycle: "",
        duration: 0,
        original_price: 0,
        base_price: 0,
        clDiscount: 0,
        code_discount: 0,
        isUseDiscountCode: false,
        customfield: {
          promo_code: "",
          voucher_get_id: "",
        },
        totalPrice: 0
      },
      renewLoading: false,
      renewTitle: "",
      hasClientLevel: false,
      hasShowPromo: false,
      isShowLevel: false,
      isExclude: 0, // 优惠码和用户等级是否互斥
    };
  },
  components: {
    discountCode
  },
  props: {
    isDemandFee: {
      type: Boolean,
      default: false,
    },
    id: {
      type: Number,
      default: 0,
      required: true,
    },
    productId: {
      type: Number,
      default: 0,
      required: true,
    },
    // 特殊处理代理老财务 周期没有id的问题
    byIndex: {
      type: Boolean,
      default: false,
    }
  },

  created () {
    // 加载css
    if (
      !document.querySelector(
        'link[href="' + url + 'components/renewDialog/renewDialog.css"]'
      )
    ) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = `${url}components/renewDialog/renewDialog.css`;
      document.head.appendChild(link);
    }
    this.hasClientLevel = havePlugin("IdcsmartClientLevel");
    this.hasShowPromo = havePlugin("PromoCode");
    this.isShowLevel = this.hasClientLevel;
    this.getCommon();
  },

  computed: {
    // 计算总价
    totalPrice () {
      const goodsPrice = this.hasShowPromo && this.renewParams.customfield.promo_code
        ? this.renewParams.base_price
        : this.renewParams.original_price;
      const discountPrice = (this.renewParams.clDiscount * 1) + this.renewParams.code_discount;
      const totalPrice = goodsPrice - discountPrice;
      const nowPrice = totalPrice > 0 ? totalPrice.toFixed(2) : 0;
      return Number(nowPrice).toFixed(2);
    }
  },

  filters: {
    filterMoney (val) {
      return Number(val).toFixed(2);
    }
  },

  watch: {
    totalPrice (newVal) {
      this.renewParams.totalPrice = newVal;
    }
  },

  methods: {
    // 显示续费弹窗
    showRenew (isDemand = false) {
      if (this.isShowRenew) return;

      // 重置所有状态
      this.isExclude = 0;
      this.renewParams = {
        billing_cycle: "",
        duration: 0,
        original_price: 0,
        base_price: 0,
        clDiscount: 0,
        code_discount: 0,
        isUseDiscountCode: false,
        customfield: {
          promo_code: "",
          voucher_get_id: "",
        },
        totalPrice: 0
      };
      this.$refs.discountCode && this.$refs.discountCode.reset();
      const params = { id: this.id };
      this.renewLoading = true;
      this.submitLoading = true;
      this.isShowRenew = true;
      this.renewTitle = isDemand ? lang.mf_demand_tip5 : lang.common_cloud_title10;

      const apiFun = isDemand ? apiGetDemandToPrepaymentPrice : renewPage;

      apiFun(params)
        .then(async (res) => {
          this.submitLoading = false;
          this.renewLoading = false;
          if (res.data.status === 200) {
            this.renewPageData = res.data.data.host || res.data.data.duration || [];
            if (this.renewPageData.length > 0) {
              const firstItem = this.renewPageData[0];
              this.renewActiveId = this.byIndex ? 0 : firstItem.id;
              this.renewParams.billing_cycle = firstItem.billing_cycle;
              this.renewParams.duration = firstItem.duration;
              this.renewParams.original_price = firstItem.price;
              this.renewParams.base_price = firstItem.base_price;
              this.renewParams.clDiscount = 0;
            }
          }
        })
        .catch((err) => {
          this.submitLoading = false;
          this.renewLoading = false;
          this.isShowRenew = false;
          this.$message.error(err.data.msg);
        });
    },

    /* 
       * 1.正常续费弹窗：
       *  * 不互斥的优惠码的时候之前算出来的接口里面的用户等级都是0会有问题
       *  * 需要重新自己计算用户等级折扣
       * 2.转包年包月
       *  * 续费页面里面的等级折扣可以直接使用
    */
    // 续费使用优惠码
    async getRenewDiscount (data) {
      this.renewParams.code_discount = Number(data[0]);
      this.renewParams.customfield.promo_code = data[1];
      this.isExclude = Number(data[2]) || 0;
      this.renewParams.isUseDiscountCode = true;

      // 获取当前选中项的等级折扣
      let currentItem = {};
      if (this.byIndex) {
        currentItem = this.renewPageData[this.renewActiveId]
      } else {
        currentItem = this.renewPageData.find(item => item.id === this.renewActiveId);
      }

      if (currentItem) {
        // 如果优惠码与等级折扣互斥，则清空等级折扣
        if (this.isExclude === 1) {
          this.renewParams.clDiscount = 0;
        } else {
          // 仅针对于普通续费 并且 不互斥的情况
          if (this.isShowLevel && !this.isDemandFee) {
            const discountParams = { id: this.productId, amount: currentItem.current_base_price };
            await clientLevelAmount(discountParams)
              .then((res2) => {
                if (res2.data.status === 200) {
                  this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
                }
              })
              .catch((error) => {
                this.renewParams.clDiscount = 0;
              });
          } else {
            this.renewParams.clDiscount = currentItem.client_level_discount || 0;
          }
        }
      }
    },

    // 移除续费的优惠码
    removeRenewDiscountCode () {
      this.isExclude = 0;
      this.renewParams.isUseDiscountCode = false;
      this.renewParams.customfield.promo_code = "";
      this.renewParams.code_discount = 0;
      this.renewParams.clDiscount = 0;
    },

    // 续费弹窗关闭
    renewDgClose () {
      this.isShowRenew = false;
      this.renewPageData = [];
      this.renewActiveId = 0;
      this.isExclude = 0;
      this.renewParams = {
        billing_cycle: "",
        duration: 0,
        original_price: 0,
        base_price: 0,
        clDiscount: 0,
        code_discount: 0,
        isUseDiscountCode: false,
        customfield: {
          promo_code: "",
          voucher_get_id: "",
        },
        totalPrice: 0
      };
      this.renewLoading = false;
    },

    // 续费周期点击
    async renewItemChange (item, index) {
      this.renewLoading = true;
      this.renewActiveId = this.byIndex ? index : item.id;
      this.renewParams.duration = item.duration;
      this.renewParams.billing_cycle = item.billing_cycle;
      this.renewParams.original_price = item.price;
      this.renewParams.base_price = item.base_price;

      // 开启了优惠码插件且已使用优惠码
      if (this.hasShowPromo && this.renewParams.isUseDiscountCode) {
        this.renewParams.clDiscount = Number(item.client_level_discount);
        // 更新优惠码
        await applyPromoCode({
          scene: this.isDemandFee ? "change_billing_cycle" : "renew",
          product_id: this.productId,
          amount: item.base_price,
          billing_cycle_time: this.renewParams.duration,
          promo_code: this.renewParams.customfield.promo_code,
        })
          .then((res) => {
            this.renewParams.isUseDiscountCode = true;
            this.renewParams.code_discount = Number(res.data.data.discount);
            this.isExclude = Number(res.data.data?.exclude_with_client_level) || 0;

            // 如果优惠码与等级折扣互斥，则清空等级折扣
            if (this.isExclude === 1) {
              this.renewParams.clDiscount = 0;
            } else {
              // 仅针对于普通续费 并且 不互斥的情况
              if (this.isShowLevel && !this.isDemandFee) {
                const discountParams = { id: this.productId, amount: item.current_base_price };
                clientLevelAmount(discountParams)
                  .then((res2) => {
                    if (res2.data.status === 200) {
                      this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
                    }
                  })
                  .catch((error) => {
                    this.renewParams.clDiscount = 0;
                  });
              } else {
                this.renewParams.clDiscount = Number(item.client_level_discount || 0);
              }
            }
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
            this.removeRenewDiscountCode();
          });
      }
      this.renewLoading = false;
    },

    // 续费提交
    subRenew () {
      this.submitLoading = true;
      const params = {
        id: this.id,
        billing_cycle: this.renewParams.billing_cycle,
        customfield: this.renewParams.customfield,
      };

      let apiFun = renew;
      if (this.isDemandFee) {
        apiFun = demandToPrepayment;
        params.duration_id = this.renewActiveId;
      }

      apiFun(params)
        .then((res) => {
          this.submitLoading = false;
          if (res.data.status === 200) {
            if (res.data.code == "Paid") {
              this.isShowRenew = false;
              this.$message.success(res.data.msg);
              this.$emit("renew-success");
            } else {
              this.isShowRenew = false;
              this.$emit("renew-pay", res.data.data.id, this.renewParams.totalPrice);
            }
          }
        })
        .catch((err) => {
          this.submitLoading = false;
          this.$message.error(err.data.msg);
        });
    },

    getCommon () {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before")) || {};
    },
  },
};

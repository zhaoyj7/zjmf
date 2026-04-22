const discountCode = {
  template: `
        <div>
            <el-popover placement="bottom" trigger="click" v-model="visibleShow" class="discount-popover" :visible-arrow="false" v-if="!appliedPromoCode">
                <div class="discount-content">
                    <div class="close-btn-img" @click="closePopver">
                        <img src="${url}/img/common/close_icon.png" alt="">
                    </div>
                    <div>
                        <el-input class="discount-input" clearable v-model="discountInputVal" :placeholder="lang.login_text10" maxlength="9"></el-input>
                        <el-button class="discount-btn" :loading="isLoading" @click="handelApplyPromoCode">{{lang.shoppingCar_tip_text9}}</el-button>
                    </div>
                </div>
                <span slot="reference" class="discount-text">{{lang.shoppingCar_tip_text12}}</span>
            </el-popover>
            <el-tooltip v-model="tooltipVisible" :content="tooltipText" placement="top" :disabled="!showTooltip" v-if="appliedPromoCode">
                <div class="discount-code-number">
                    {{ appliedPromoCode }}
                    <i class="el-icon-circle-close remove-discountCode" @click="removePromoCode"></i>
                </div>
            </el-tooltip>
        </div>
        `,
  data () {
    return {
      discountInputVal: "", // 输入框Value
      visibleShow: false, // 是否显示优惠弹窗
      isLoading: false, // 确认按钮loading
      discountMoney: 0, // 抵扣金额
      isExclude: 0, // 是否互斥
      appliedPromoCode: "", // 已应用的优惠码
      tooltipVisible: false, // tooltip 显示状态
      hasShownAlert: false // 是否已经显示过弹窗提示
    };
  },
  created () {
    if (
      !document.querySelector(
        'link[href="' + url + 'components/discountCode/discountCode.css"]'
      )
    ) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = `${url}components/discountCode/discountCode.css`;
      document.head.appendChild(link);
    }
  },
  components: {},
  props: {
    scene: {
      type: String, // new新购,renew续费,upgrade升降级
      required: true,
    },
    isNeedPromo_code: {
      //优惠码
      type: Boolean,
      required: false,
      default: true,
    },
    host_id: {
      // 产品ID
      type: String | Number,
      required: false,
    },
    product_id: {
      // 商品ID
      type: String | Number,
      required: true,
    },
    qty: {
      // 数量  新购时必传
      type: String | Number,
      required: false,
    },
    amount: {
      //单价
      type: String | Number,
      required: true,
    },
    billing_cycle_time: {
      // 周期时间
      type: String | Number,
      required: true,
    },
    shopping_index: {
      // 购物车位置
      type: Number,
      required: false,
    },
    multiple_product: {
      // 订购页面多商品同时使用优惠
      type: Array,
      default: () => [],
      required: false,
    }
  },
  computed: {
    showTooltip () {
      return this.appliedPromoCode && this.isExclude === 1
    },
    tooltipText() {
      return lang.goods_text8;
    },
  },
  mounted () { 
  },
  methods: {
    // 重置优惠码状态
    reset() {
      this.discountInputVal = "";
      this.appliedPromoCode = "";
      this.isExclude = 0;
      this.discountMoney = 0;
      this.tooltipVisible = false;
      this.hasShownAlert = false;
      this.visibleShow = false;
    },
    closePopver () {
      this.visibleShow = false;
      this.discountInputVal = "";
    },
    removePromoCode () {
      this.reset();
      this.$emit("remove-discount");
    },
    handelApplyPromoCode () {
      if (this.isNeedPromo_code && this.discountInputVal.length === 0) {
        this.$message.warning(lang.shoppingCar_tip_text13);
        return;
      }
      this.isLoading = true;
      if (this.multiple_product.length === 0) {
        const params = {
          scene: this.scene,
          product_id: this.product_id,
          amount: this.amount,
          billing_cycle_time: this.billing_cycle_time,
          promo_code: this.discountInputVal,
        };
        if (this.qty) {
          params.qty = this.qty;
        }
        if (this.host_id) {
          params.host_id = this.host_id;
        }
        this.getCountMoney(params);
      } else {
        // 批量使用优惠码，目前只针对订购
        let discountArr = [];
        this.multiple_product.forEach(item => {
          const params = {
            scene: this.scene,
            product_id: item.product_id,
            amount: item.totalPrice,
            billing_cycle_time: this.billing_cycle_time,
            promo_code: this.discountInputVal,
          };
          if (item.qty) {
            params.qty = item.qty;
          }
          if (this.host_id) {
            params.host_id = this.host_id;
          }
          discountArr.push(applyPromoCode(params));
        });
        Promise.all(discountArr).then(res => {
          this.discountMoney = res.reduce((all, cur) => {
            all += Number(cur.data.data.discount);
            return all;
          }, 0);
          // 多个的时候只要有一个互斥就提示
          this.isExclude = res.some(item => item.data.data?.exclude_with_client_level === 1) ? 1 : 0;
          this.appliedPromoCode = this.discountInputVal; // 保存已应用的优惠码
          
          this.$emit(
            "get-discount",
            this.discountMoney,
            this.discountInputVal,
            this.isExclude
          );
          this.$message.success(lang.shoppingCar_tip_text14);
          
          // 显示 tooltip
          if (this.showTooltip) {
            this.tooltipVisible = true;
          }
          
          // 如果有互斥提示且未显示过，弹出提示框
          if (this.showTooltip && !this.hasShownAlert) {
            this.$alert(this.tooltipText, lang.topMenu_text5, {
              callback: action => {}
            });
            this.hasShownAlert = true;
          }
          
          this.closePopver();
        }).catch(err => {
          this.$message.error(err.data.msg);
        }).finally(() => {
          this.isLoading = false;
        });
      }
    },
    getCountMoney (params) {
      applyPromoCode(params)
        .then((res) => {
          this.discountMoney = Number(res.data.data.discount);
          this.isExclude = res.data.data?.exclude_with_client_level || 0;
          this.appliedPromoCode = this.discountInputVal; // 保存已应用的优惠码
          
          if (this.shopping_index || this.shopping_index === 0) {
            this.$emit(
              "get-discount",
              this.discountMoney,
              this.discountInputVal,
              this.shopping_index
            );
          } else {
            this.$emit(
              "get-discount",
              this.discountMoney,
              this.discountInputVal,
              this.isExclude
            );
          }
          this.$message.success(lang.shoppingCar_tip_text14);
          
          // 显示 tooltip
          if (this.showTooltip) {
            this.tooltipVisible = true;
          }
          
          // 如果有互斥提示且未显示过，弹出提示框
          if (this.showTooltip && !this.hasShownAlert) {
            this.$alert(this.tooltipText, lang.topMenu_text5, {
              callback: action => {}
            });
            this.hasShownAlert = true;
          }
          
          this.closePopver();
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};

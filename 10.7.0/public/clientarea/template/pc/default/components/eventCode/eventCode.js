const eventCode = {
  template: `
  <div>
    <el-popover placement="bottom" trigger="click" v-model="visibleShow" :visible-arrow="false" v-if="!disabled && options.length !==0">
      <div class="event-content">
          <el-select class="event-select" @change="changePromotion" v-model="eventId" 
              :placeholder="lang.goods_text5" >
              <el-option v-for="item in options" :key="item.id" :value="item.id" :label="calcLebal(item)">
              </el-option>
          </el-select>
      </div>
      <template #reference>
          <el-tooltip v-model="tooltipVisible"  :disabled="!showTooltip" :content="tooltipText" placement="top-end">
              <span class="event-text">{{showText}}<i class="el-icon-caret-bottom"></i></span>
          </el-tooltip>
      </template>
    </el-popover>
    <el-tooltip v-model="tooltipVisible" :disabled="!showTooltip" v-if="disabled && options.length > 0"  :content="tooltipText" placement="top-end">
      <span class="event-text" >{{showText}}</span>
    </el-tooltip>
  
</div>
          `,
  data() {
    return {
      eventId: "", // 活动促销ID
      options: [],
      discount: 0,
      visibleShow: false,
      tooltipVisible: false,
      nowParams: {},
      showEventId: undefined,
    };
  },
  computed: {
    showText() {
      return this.eventId
        ? this.calcLebal(
          this.options.filter((item) => item.id === this.eventId)[0]
        )
        : lang.goods_text6;
    },
    showTooltip() {
      const selectedOption = this.options.find((item) => item.id === this.eventId);
      return selectedOption && selectedOption.exclude_with_client_level === 1;
    },
    tooltipText() {
      return lang.goods_text7;
    },
  },
  watch: {
    billing_cycle_time() {
      this.getEventList();
    },
    amount() {
      this.getEventList();
    },
    qty() {
      this.getEventList();
    },
  },
  props: {
    id: {
      type: String | Number,
    },
    // 场景中的所有商品ID
    product_id: {
      type: String | Number,
      required: true,
    },
    // 需要支付的原价格
    amount: {
      type: Number | String,
      required: true,
    },
    // 购买数量
    qty: {
      type: Number | String,
      default: 1,
      required: true,
    },
    //周期时间
    billing_cycle_time: {
      type: Number | String,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    level_tip: {
      type: Boolean,
      default: true,
    }
  },
  created() {
    this.getEventList();
  },
  mounted() { },
  methods: {
    calcLebal(item) {
      if (!item) {
        return "";
      }
      return item.type === "percent"
        ? lang.goods_text1 + " " + item.value + "%"
        : item.type === "reduce"
          ? lang.goods_text2 + item.full + lang.goods_text3 + " " + item.value
          : lang.goods_text6;
    },
    getEventList() {
      const params = {
        id: this.product_id,
        qty: this.qty,
        amount: this.amount,
        billing_cycle_time: this.billing_cycle_time,
      };
      if (
        JSON.stringify(this.nowParams) == JSON.stringify(params) ||
        !this.billing_cycle_time
      ) {
        // 没有变化 防止重复请求
        return;
      }
      this.nowParams = params;
      eventPromotion(params)
        .then((res) => {
          const event_list = res.data.list;
          const isTop =
            res.data.addon_event_promotion_does_not_participate === "top";
          if (event_list.length > 0) {
            const no_select = {
              id: 0,
              type: "no",
              value: 0,
              full: 0,
            };
            if (isTop) {
              event_list.unshift(no_select);
            } else {
              event_list.push(no_select);
            }
            this.options = event_list;
            // 默认选中处理
            if (
              this.id &&
              this.options.map((item) => item.id).includes(this.id)
            ) {
              this.eventId = this.id;
            } else {
              this.eventId = this.options[0]?.id || "";
            }
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        })
        .finally(() => {
          this.changePromotion();
        });
    },
    changePromotion() {
      this.$emit("change", {
        discount: this.eventId ? this.discount : 0,
        id: this.eventId ? this.eventId : "",
      });
      this.visibleShow = false;
      if (this.showTooltip) {
        this.tooltipVisible = true;
      }
      // 优化一下，相同的活动，只弹一次
      if (this.showTooltip && this.level_tip && this.showEventId !== this.eventId) {
        this.$alert(this.tooltipText, lang.topMenu_text5, {
          callback: action => {
          }
        });
      }
      this.showEventId = this.eventId;
    },
    clearPromotion() {
      this.discount = 0;
      this.$emit("change", { discount: this.discount, id: this.eventId });
    },
  },
};

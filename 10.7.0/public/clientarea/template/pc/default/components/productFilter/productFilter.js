const productFilter = {
  template: /*html */ `
    <div class="product-tab-list">
      <el-tabs v-model="select_tab" @tab-click="handelTab" v-if="isInit">
        <el-tab-pane v-for="(item,index) in tabList" :key="item.tab" :name="item.tab">
          <template #label>
            <span>{{item.name}}</span>
            <span  v-if="count[item.countName] > 0">({{count[item.countName]}})</span>
          </template>
        </el-tab-pane>
      </el-tabs>
    </div>
    `,
  data() {
    return {
      select_tab: "all",
      isInit: false,
    };
  },
  props: {
    tabList: {
      type: Array,
      required: false,
      default: () => {
        return [
          {
            name: lang.product_list_status1,
            tab: "using",
            countName: "using_count",
          },
          {
            name: lang.product_list_status2,
            tab: "expiring",
            countName: "expiring_count",
          },
          {
            name: lang.product_list_status3,
            tab: "overdue",
            countName: "overdue_count",
          },
          {
            name: lang.product_list_status4,
            tab: "deleted",
            countName: "deleted_count",
          },
          {
            name: lang.finance_btn5,
            tab: "all",
            countName: "all_count",
          },
        ];
      },
    },
    tab: {
      type: String,
      required: false,
      default: "",
    },
    count: {
      type: Object,
      required: false,
      default: {
        all_count: 0, // 全部产品数量
        deleted_count: 0, // 已删除产品数量
        expiring_count: 0, // 即将到期产品数量
        overdue_count: 0, // 已逾期产品数量
        using_count: 0, // 正常使用产品数量
      },
    },
  },
  // 监听count 数据变化了代表有数据 只执行一次 初始化不执行
  watch: {
    count: {
      handler(newVal) {
        if (newVal.list) {
          this.isInit = true;
        }
      },
      deep: true,
      immediate: true,
    },
  },
  mounted() {
    if (this.tab) {
      this.select_tab = this.tab;
    }
    if (this.tab == "") {
      this.select_tab = "all";
    }
  },
  methods: {
    handelTab({name}) {
      const tab = name === "all" ? "" : name;
      this.$emit("update:tab", tab);
      this.$emit("change");
    },
  },
};

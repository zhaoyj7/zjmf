/* 分页 */
const comPagination = {
  template: `
    <div class="com-pagination" :class="{ 'custom-page': showCustomButtons }">
       <t-pagination
        :show-jumper="showJumper"
        :current="page"
        :page-size="limit"
        :total="total"
        :page-size-options="sizeOptions"
        :disabled="page <= 1 && isNextPageDisabled && showCustomButtons"
        v-bind="$attrs"
        @change="changePage">
      </t-pagination>
      <div class="manual-btn" v-if="showCustomButtons">
        <t-button size="medium" :disabled="page <= 1" @click="handleChange(0)">{{lang.prev_page}}</t-button>
        <t-button size="medium" :disabled="isNextPageDisabled" @click="handleChange(1)">{{lang.next_page}}</t-button>
      </div>
    </div>
  `,
  props: {
    page: {
      type: Number,
      required: true,
      default () {
        return 1;
      },
    },
    limit: {
      type: Number,
      required: true,
      default () {
        return 10;
      },
    },
    total: {
      type: Number,
      required: true,
      default () {
        return 0;
      },
    },
    pageSizeOptions: {
      type: Array,
      default () {
        return [10, 20, 50, 100];
      },
    },
    showJumper: {
      type: Boolean,
      default: true,
    },
    showCustomButtons: {
      type: Boolean,
      default: false,
    },
    curPageLength: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    // 判断是否禁用下一页
    isNextPageDisabled () {
      return this.curPageLength < this.limit;
    },
    // 动态生成页面大小选项，避免重复并排序
    sizeOptions () {
      return [...new Set([...this.pageSizeOptions, this.limit])].sort((a, b) => a - b);
    },
  },
  methods: {
    handleChange (direction) {
      let newPage;
      if (direction === 0) {
        if (this.page > 1) {
          newPage = this.page - 1;
          this.$emit('page-change', {
            current: newPage,
            pageSize: this.limit
          });
        }
      } else if (direction === 1) {
        if (!this.isNextPageDisabled) {
          newPage = this.page + 1;
          this.$emit('page-change', {
            current: newPage,
            pageSize: this.limit
          });
        }
      }
    },
    changePage (newPage) {
      this.$emit('page-change', newPage);
    },
  },
};

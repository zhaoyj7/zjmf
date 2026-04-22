// css 样式依赖common.css
const pagination = {
  inheritAttrs: false,
  template: `
        <div class="myPage custom-pagination">
          <el-pagination
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="pageData.page"
            :page-sizes="pageData.pageSizes" :page-size="pageData.limit"
            :layout="layoutToUse" 
            :total="pageData.total"
            :pager-count=5
            v-bind="$attrs"
            :disabled="pageData.page <= 1 && isNextPageDisabled && showCustomButtons"
            >
            <span class="page-total">{{lang.total}} {{pageData.total}} {{lang.pieces}}</span>
          </el-pagination>
          <div class="manual-btn" v-if="showCustomButtons">
            <el-button type="primary" size="small" :disabled="pageData.page <= 1" @click="handleChange(0)">{{lang.prev_page}}</el-button>
            <el-button type="primary" size="small" :disabled="isNextPageDisabled" @click="handleChange(1)">{{lang.next_page}}</el-button>
          </div>
        </div>
        `,
  data () {
    return {};
  },
  computed: {
    layoutToUse () {
      return this.$attrs.layout || "slot, sizes, prev, pager,jumper, next";
    },
    isNextPageDisabled () {
      return this.curPageLength < this.pageData.limit;
    },
  },
  props: {
    pageData: {
      default: function () {
        return {
          page: 1,
          pageSizes: [20, 50, 100],
          limit: 20,
          total: 400,
        };
      },
    },
    showCustomButtons: {
      type: Boolean,
      default: false,
    },
    curPageLength: {
      type: Number,
      default: 0,
    }
  },
  methods: {
    handleChange (direction) {
      if (direction === 0) {
        if (this.pageData.page > 1) {
          this.pageData.page -= 1;
          this.$emit('currentchange', this.pageData.page);
        }
      } else if (direction === 1) {
        if (!this.isNextPageDisabled) {
          this.pageData.page += 1;
          this.$emit('currentchange', this.pageData.page);
        }
      }
    },
    handleSizeChange (e) {
      this.pageData.limit = e;
      this.$emit("sizechange", e);
    },
    handleCurrentChange (e) {
      this.pageData.page = e;
      this.$emit("currentchange", e);
    },
  },
};

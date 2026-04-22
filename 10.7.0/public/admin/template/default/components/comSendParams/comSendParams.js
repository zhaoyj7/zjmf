/* 公共发送参数 */
const comSendParams = {
  template: `
    <div class="com-send-params">
      <div class="item" v-for="(item,index) in sendParams" :key="index">
        <p class="tit">{{item.label}}</p>
        <t-table row-key="id" :data="item.param" size="medium"
          :columns="paramsColumns" :hover="hover" bordered
          :table-layout="tableLayout ? 'auto' : 'fixed'"
          :hide-sort-tips="hideSortTips">
        </t-table>
      </div>
    </div>
    `,
  data () {
    return {
      tableLayout: false,
      hover: true,
      hideSortTips: true,
      paramsColumns: [
        {
          colKey: "value",
          title: lang.variable_name,
        },
        {
          colKey: "label",
          title: lang.variable,
        },
      ],
      sendParams: []
    }
  },
  created() {
    this.getSendParams();
  },
  methods: {
    async getSendParams () {
      try {
        const res = await getCommonParams();
        this.sendParams = res.data.data.list;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
  }
};

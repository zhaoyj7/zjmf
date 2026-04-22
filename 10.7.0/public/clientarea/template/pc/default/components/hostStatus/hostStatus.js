/* 处理产品转移中的状态 */
const hostStatus = {
  template:
      `
        <div>
          <div class="host-is-transfer" v-if="isTransferring">{{lang.host_transferring}}</div>
          <slot v-else></slot>
        </div>
      `,
  data () {
    return {
      isTransferring: false,
    };
  },
  props: {
    id: {
      type: Number | String,
      required: true,
      default: null,
    },
    status: {
      type: String,
      required: true,
      default: '',
    }
  },
  methods: {
    async getHostTransferStatus () {
      try {
        const res = await hostIsTransfer({ id: this.id * 1 });
        const transferring = res.data.data.status;
        // 状态为 Active 且 transferring === 1 的时候显示转移中
        this.isTransferring = transferring && this.status === 'Active';
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    }
  },
  mounted () {
    const arr = JSON.parse(
      document.querySelector("#addons_js").getAttribute("addons_js")
    ).map((item) => {
      return item.name;
    });
    arr.includes("HostTransfer") && this.getHostTransferStatus();
  }
};

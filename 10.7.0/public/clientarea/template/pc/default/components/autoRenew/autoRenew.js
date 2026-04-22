/* 自动续费 */
const autoRenew = {
  template: `
      <div class="com-auto-renew">
        <el-switch :value="isAutoRenew" active-color="var(--color-primary)"
          :active-value="1" :inactive-value="0" @change="autoRenewChange">
        </el-switch>
        <el-dialog :visible.sync="showRenewDialog" :show-close="false" custom-class="common-renew-dialog"
          width="6.2rem">
          <div class="dialog-title">
            {{calcTitle}}
          </div>
          <div class="con" v-loading="loading">
            <p class="item">
              <span class="label">ID：</span>
              <span class="value">{{host.id}}</span>
            </p>
            <p class="item">
              <span class="label">{{lang.auto_renew_name}}：</span>
              <span class="value">{{host.name}}</span>
            </p>
            <p class="item" v-if="host.area">
              <span class="label">{{lang.auto_renew_area}}：</span>
              <span class="value">{{host.country}}-{{host.city}}-{{host.area}}</span>
            </p>
            <p class="item" v-if="host.dedicate_ip">
              <span class="label">IP：</span>
              <span class="value">
                <span>{{host.dedicate_ip}}</span>
                  <el-popover placement="top" trigger="hover" v-if="host.ip_num > 1">
                    <div class="ips">
                      <p v-for="(item,index) in host.allIp" :key="index">
                        {{item}}
                        <i class="el-icon-document-copy base-color" @click="copyIp(item)"></i>
                      </p>
                    </div>
                    <span slot="reference" class="base-color">
                      ({{host.ip_num}})
                    </span>
                  </el-popover>
                <i class="el-icon-document-copy base-color" @click="copyIp(host.allIp)" v-if="host.ip_num > 0">
                </i>
              </span>
            </p>
            <p class="item">
              <span class="label">{{lang.auto_renew_cycle}}：</span>
              <span class="value">{{commonData.currency_prefix}}{{host.renew_amount}}/{{host.billing_cycle_name}}</span>
            </p>
            <p class="item">
              <span class="label">{{lang.auto_renew_due}}：</span>
              <span class="value">{{host.due_time | formateTime}}</span>
            </p>
          </div>
          <div class="dialog-footer">
            <el-button class="btn-ok" @click="handleAutoRenew" :loading="submitLoading">{{lang.auto_renew_sure}}</el-button>
            <el-button class="btn-no" @click="showRenewDialog = false">{{lang.auto_renew_cancel}}</el-button>
          </div>
        </el-dialog>
      </div>
      `,
  data() {
    return {
      showRenewDialog: false,
      submitLoading: false,
      calcTitle: "",
      is_auto_renew: 0,
      host: {},
      loading: false,
      commonData: JSON.parse(localStorage.getItem("common_set_before")) || {},
    };
  },
  props: {
    isAutoRenew: {
      type: Number,
      required: true,
      default: 0,
    },
    id: {
      type: Number | String,
      required: true,
      default: null,
    },
  },
  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
  },
  methods: {
    copyIp(ip) {
      if (typeof ip !== "string") {
        ip = ip.join(",");
      }
      const textarea = document.createElement("textarea");
      textarea.value = ip.replace(/,/g, "\n");
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
      this.$message.success(lang.index_text32);
    },
    async handleAutoRenew() {
      try {
        const params = {
          id: this.host.id,
          status: this.is_auto_renew,
        };
        this.submitLoading = true;
        const res = await rennewAuto(params);
        this.$message.success(res.data.msg);
        this.showRenewDialog = false;
        this.submitLoading = false;
        this.$emit("update");
      } catch (error) {
        this.submitLoading = false;
        this.showRenewDialog = false;
        this.$message.error(error.data.msg);
      }
    },
    async autoRenewChange(val) {
      try {
        this.showRenewDialog = true;
        this.is_auto_renew = val ? 1 : 0;
        this.loading = true;
        const res = await getHostSpecific({id: this.id});
        this.host = res.data.data;
        this.host.allIp = (
          this.host.dedicate_ip +
          "," +
          this.host.assign_ip
        ).split(",");
        this.calcTitle =
          lang.auto_renew_tip1 +
          (this.isAutoRenew
            ? lang_obj.auto_renew_tip3
            : lang_obj.auto_renew_tip2);
        this.loading = false;
      } catch (error) {
        this.loading = false;
        this.$message.error(error.data.msg);
      }
    },
  },
};

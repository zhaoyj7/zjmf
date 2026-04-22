/* 流量预警 */
const trafficWarning = {
  template: `
    <div class="traffic-warning">
      <div class="search-btn" @click="handleConfig">{{lang.flow_warn_text3}}</div>
      <span v-if="tempValue" class="tip">{{lang.flow_warn_text1}}{{tempValue}}%{{lang.flow_warn_text2}}</span>
      <el-dialog width="6.8rem" :visible.sync="visible" :show-close="false" @close="closeDialog" custom-class="withdraw-dialog">
        <div class="dialog-title">{{lang.flow_warn_text3}}</div>
        <div class="dialog-main">
          <el-form label-width="120px" ref="ruleForm" :rules="rules" :model="warningForm" label-position="right">
            <el-form-item :label="lang.flow_warn_text4" prop="warning_switch">
              <el-switch v-model="warningForm.warning_switch" active-color="var(--color-primary)"
                :active-value="1" :inactive-value="0">
              </el-switch>
            </el-form-item>
            <el-form-item :label="lang.flow_warn_text1" prop="leave_percent" v-if="warningForm.warning_switch">
              <el-select v-model="warningForm.leave_percent" :placeholder="lang.placeholder_pre2">
                <el-option :value="5" label="5%"></el-option>
                <el-option :value="10" label="10%"></el-option>
                <el-option :value="15" label="15%"></el-option>
                <el-option :value="20" label="20%"></el-option>
              </el-select>
              <span class="warning-text">{{lang.flow_warn_text5}}</span>
            </el-form-item>
          </el-form>
        </div>
        <div slot="footer" class="dialog-footer">
          <el-button class="btn-ok" type="primary" @click="submit" :loading="submitLoading">{{lang.cart_tip_text9}}</el-button>
          <el-button class="btn-no" @click="closeDialog">{{lang.cart_tip_text10}}</el-button>
        </div>
      </el-dialog>
    </div>
    `,
  data() {
    return {
      visible: false,
      submitLoading: false,
      rules: {
        leave_percent: [
          { required: true, message: lang.placeholder_pre2, trigger: "change" },
        ],
      },
      warningForm: {
        module: "",
        warning_switch: 1,
        leave_percent: "",
      },
      tempValue: 0,
      tempData: {},
    };
  },
  computed: {},
  props: {
    module: {
      type: String,
      default: "mf_cloud", // 目前支持 mf_cloud mf_dcim
    },
  },
  watch: {},
  created() {
    this.getWarningConfig();
  },
  methods: {
    async getWarningConfig() {
      try {
        const res = await getTrafficWarning({
          module: this.module,
        });
        const temp = res.data.data;
        this.tempValue = temp.leave_percent;
        temp.leave_percent = temp.leave_percent || "";
        this.warningForm = temp;
        this.tempData = JSON.parse(JSON.stringify(temp));
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    handleConfig() {
      this.visible = true;
      Object.assign(this.warningForm, this.tempData);
    },
    closeDialog() {
      this.visible = false;
    },
    submit() {
      this.$refs.ruleForm.validate(async (valid) => {
        if (valid) {
          const params = {
            module: this.module,
            warning_switch: this.warningForm.warning_switch,
            leave_percent: this.warningForm.leave_percent,
          };
          if (params.warning_switch === 0) {
            params.leave_percent = 0;
          }
          this.submitLoading = true;
          const res = await saveTrafficWarning(params);
          this.submitLoading = false;
          this.visible = false;
          this.$message.success(res.data.msg);
          this.getWarningConfig();
        } else {
          return false;
        }
      });
    },
  },
};

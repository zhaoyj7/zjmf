/* 流量包组件 */
const flowPacket = {
  template: `
    <div class="flow-packet-component" v-if="hasFlow">
      <!-- 流量信息展示 -->
      <template v-if="showFlow">
        <p>{{lang.module_flow}}</p>
        <div class="module-flow">
          <div class="top con">
            <span class="item">{{lang.module_all_flow}}</span>
            <span class="item">{{lang.module_line_flow}}</span>
            <span class="item">{{lang.module_flow_package}}</span>
          </div>
          <div class="bot con">
            <span class="item">{{flowObj.leave_num}}/{{flowObj.total_num}}</span>
            <span class="item">{{flowObj.base_flow}}</span>
            <span class="item">{{flowObj.temp_flow}}</span>
          </div>
        </div>
      </template>

      <!-- 流量包列表 (mf_dcim 和 remf_dcim 不展示) -->
      <div class="flow-packet-list" v-if="showFlowPacketList && flowPacketList.length > 0" style="margin-top: 16px;">
        <t-table 
          :data="flowPacketList" 
          :columns="flowPacketColumns" 
          :loading="flowPacketLoading"
          row-key="id"
          size="small"
          bordered
          hover>
          <template #create_time="{row}">
            <span>{{row.create_time | formateTime}}</span>
          </template>
          <template #size="{row}">
            {{row.size}}GB
          </template>
          <template #used="{row}">
            {{row.used || 0}}GB
          </template>
          <template #expire_time="{row}">
            <span v-if="row.expire_time === 0">/</span>
            <span v-else>{{row.expire_time | formateTime}}</span>
          </template>
          <template #status="{row}">
            <t-tag v-if="row.status === 1" theme="success" size="small">{{lang.flow_packet_status_valid}}</t-tag>
            <t-tag v-else theme="warning" size="small">{{lang.flow_packet_status_invalid}}</t-tag>
          </template>
        </t-table>
        <!-- 分页 -->
        <t-pagination 
          v-if="flowParams.total"
          :total="flowParams.total"
          :page-size="flowParams.limit"
          :current="flowParams.page"
          :page-size-options="flowParams.pageSizes"
          show-jumper
          @change="handleFlowPageChange">
        </t-pagination>
      </div>

      <!-- 流量包购买按钮 -->
      <t-button v-if="showFlow && useableFlowList.length > 0" @click="openBuyDialog" style="margin-top: 20px;">
        {{lang.module_flow_place_order}}
      </t-button>

      <!-- 购买流量包弹窗 -->
      <t-dialog 
        :header="lang.module_flow_place_order" 
        :visible.sync="showPackage" 
        :footer="false" 
        class="flow-dialog"
        width="720">
        <!-- 限时/不限时筛选 -->
        <div class="filter-radio" v-if="hasLimitTime || hasUnlimitedTime" style="margin-bottom: 16px;">
          <t-radio-group v-model="filterType" @change="onFilterTypeChange">
            <t-radio :value="0" v-if="hasLimitTime">{{lang.limit_time_flow}}</t-radio>
            <t-radio :value="1" v-if="hasUnlimitedTime">{{lang.unlimited_traffic}}</t-radio>
          </t-radio-group>
        </div>
        <div class="con">
          <div class="items">
            <div class="p-item" 
              v-for="item in filteredPackageList" 
              :key="item.id"
              :class="{active: item.id === curPackageId}" 
              @click="choosePackage(item)">
              <p class="price">{{currencyPrefix}}{{getDisplayPrice(item) | filterMoney}}</p>
              <p class="tit">
                {{item.name}}
                <template v-if="item.billing_mode === 0">{{item.capacity}}G</template>
                <template v-else>{{item.min_capacity}}-{{item.max_capacity}}G</template>
              </p>
              <t-icon name="check"></t-icon>
            </div>
          </div>
          <!-- 滑动条 -->
          <div class="slider-container" v-if="showSlider" style="margin-top: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
              <t-slider v-model="sliderValue" :min="sliderMin" :max="sliderMax" :marks="sliderMarks" style="flex: 1;" />
              <t-input v-model="inputTempValue" type="number" @blur="onInputBlur" @focus="onInputFocus" style="width: 120px;">
                <template #suffix>GB</template>
              </t-input>
            </div>
          </div>
        </div>
        <div class="com-f-btn" style="margin-top: 20px;">
          <t-button theme="primary" :loading="submitLoading" @click="handlerPackage(false)">{{lang.module_place_order}}</t-button>
          <t-button theme="primary" :loading="submitLoading" @click="handlerPackage(true)">{{lang.module_order_redirect}}</t-button>
          <t-button theme="default" variant="base" @click="showPackage = false">{{lang.cancel}}</t-button>
        </div>
      </t-dialog>
    </div>
  `,
  filters: {
    filterMoney (money) {
      if (isNaN(money)) {
        return '0.00';
      } else {
        const temp = `${money}`.split('.');
        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00');
      }
    },
    formateTime (time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
  },
  props: {
    // 产品ID
    id: {
      type: [Number, String],
      required: true,
    },
    // 线路计费类型（用于判断 showFlow）
    lineType: {
      type: String,
      default: '',
    },
    module: {
      type: String,
      default: ''
    },
  },
  data () {
    return {
      currencyPrefix: JSON.parse(localStorage.getItem("common_set")).currency_prefix,
      // 插件判断
      hasFlow: false,
      // 是否显示流量模块（根据线路类型判断）
      showFlow: false,
      // 流量信息对象
      flowObj: {
        total_num: '--',
        leave_num: '--',
        base_flow: '--',
        temp_flow: '--',
      },
      // 可购买的流量包列表
      useableFlowList: [],
      packageLoading: false,
      // 购买弹窗
      showPackage: false,
      curPackageId: '',
      filterType: 0, // 0: 限时, 1: 不限时
      submitLoading: false,
      // 滑动条
      sliderValue: 0,
      sliderMin: 0,
      sliderMax: 100,
      showSlider: false,
      // 流量包列表
      flowPacketList: [],
      flowPacketLoading: false,
      flowParams: {
        page: 1,
        limit: 10,
        pageSizes: [10, 20, 50],
        total: 0,
      },
      // 流量包列表列配置
      flowPacketColumns: [
        { colKey: 'id', title: 'ID', width: 80 },
        { colKey: 'name', title: lang.flow_packet_name, minWidth: 150, ellipsis: true },
        { colKey: 'create_time', title: lang.flow_packet_create_time, width: 160, cell: 'create_time' },
        { colKey: 'size', title: lang.flow_packet_size, width: 150, cell: 'size' },
        { colKey: 'used', title: lang.flow_packet_used, width: 130, cell: 'used' },
        { colKey: 'expire_time', title: lang.flow_packet_expire_time, width: 160, cell: 'expire_time' },
        { colKey: 'status', title: lang.status, width: 80, cell: 'status', fixed: "right", },
      ],
      inputTempValue: '',
    };
  },
  computed: {
    // 是否显示流量包列表（mf_dcim 和 remf_dcim 不展示）
    showFlowPacketList () {
      const hiddenModules = ['mf_dcim', 'remf_dcim'];
      return !hiddenModules.includes(this.module);
    },
    // 根据 filterType 筛选流量包
    filteredPackageList () {
      return this.useableFlowList.filter(item => item.long_term_valid === this.filterType);
    },
    // 获取当前选中的流量包
    currentPackage () {
      return this.useableFlowList.find(item => item.id === this.curPackageId);
    },
    // 滑动条标记
    sliderMarks () {
      return {
        [this.sliderMin]: this.sliderMin + 'GB',
        [this.sliderMax]: this.sliderMax + 'GB',
      };
    },
    // 是否有限时流量包
    hasLimitTime () {
      return this.useableFlowList.some(item => item.long_term_valid === 0);
    },
    // 是否有不限时流量包
    hasUnlimitedTime () {
      return this.useableFlowList.some(item => item.long_term_valid === 1);
    },
  },
  watch: {
    sliderValue (val) {
      this.inputTempValue = val;
    },
    id: {
      handler (val) {
        if (val && this.hasFlow) {
          this.initData();
        }
      },
    },
    lineType: {
      immediate: true,
      handler (val) {
        // 根据线路类型判断是否显示流量模块
        this.showFlow = val === 'flow';
      },
    },
  },
  created () {
    this.checkFlowPlugin();
  },
  methods: {
    onInputFocus () {
      this.inputTempValue = this.sliderValue;
    },
    onInputBlur () {
      let val = Number(this.inputTempValue);
      if (isNaN(val) || val < this.sliderMin) val = this.sliderMin;
      if (val > this.sliderMax) val = this.sliderMax;
      this.sliderValue = val;
      this.inputTempValue = val;
    },
    // 检查是否安装了流量包插件
    async checkFlowPlugin () {
      try {
        const res = await getAddon();
        const temp = res.data.data.list.reduce((all, cur) => {
          all.push(cur.name);
          return all;
        }, []);
        this.hasFlow = temp.includes('FlowPacket');
        if (this.hasFlow && this.id) {
          this.initData();
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 获取流量信息
    async getFlowInfo () {
      try {
        const res = await getModuleFlow({ id: this.id });
        let { total_num, used_num, base_flow, temp_flow } = res.data.data;
        const leave_num = total_num == 0 ? lang.module_no_limit : Number(total_num - used_num).toFixed(2);
        total_num = total_num === 0 ? lang.module_no_limit : Number(total_num).toFixed(2);
        base_flow = base_flow === 0 ? lang.module_no_limit : Number(base_flow).toFixed(2);
        temp_flow = temp_flow === 0 ? '--' : Number(temp_flow).toFixed(2);
        this.flowObj = { total_num, leave_num, base_flow, temp_flow };
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 获取可购买的流量包列表
    async getUsableFlowList () {
      try {
        this.packageLoading = true;
        const res = await getUsableFlow({
          id: this.id,
          page: 1,
          limit: 1000,
        });
        this.useableFlowList = res.data.data.list || [];
        this.packageLoading = false;
      } catch (error) {
        this.packageLoading = false;
      }
    },
    // 获取已购买的流量包列表
    async getFlowPacketList () {
      if (!this.showFlowPacketList) return;
      try {
        this.flowPacketLoading = true;
        const res = await getFlowPacketList(this.module, {
          id: this.id,
          page: this.flowParams.page,
          limit: this.flowParams.limit,
        });
        this.flowPacketList = res.data.data.list || [];
        this.flowParams.total = res.data.data.count || 0;
        this.flowPacketLoading = false;
      } catch (error) {
        this.flowPacketLoading = false;
      }
    },
    // 分页变化
    handleFlowPageChange (pageInfo) {
      this.flowParams.page = pageInfo.current;
      this.flowParams.limit = pageInfo.pageSize;
      this.getFlowPacketList();
    },
    // 打开购买弹窗
    openBuyDialog () {
      this.showPackage = true;
      // 根据数据设置默认 filterType，优先选限时
      const limitTimeList = this.useableFlowList.filter(item => item.long_term_valid === 0);
      const unlimitedTimeList = this.useableFlowList.filter(item => item.long_term_valid === 1);
      if (limitTimeList.length > 0) {
        this.filterType = 0;
        this.choosePackage(limitTimeList[0]);
      } else if (unlimitedTimeList.length > 0) {
        this.filterType = 1;
        this.choosePackage(unlimitedTimeList[0]);
      }
    },
    // 选择流量包
    choosePackage (item) {
      this.curPackageId = item.id;
      // 根据 billing_mode 处理滑动条
      if (item.billing_mode === 1) {
        this.showSlider = true;
        this.sliderMin = item.min_capacity;
        this.sliderMax = item.max_capacity;
        this.sliderValue = item.min_capacity;
      } else {
        this.showSlider = false;
      }
    },
    // 切换筛选类型
    onFilterTypeChange () {
      const filtered = this.filteredPackageList;
      if (filtered.length > 0) {
        this.choosePackage(filtered[0]);
      } else {
        this.curPackageId = '';
        this.showSlider = false;
      }
    },
    // 计算显示价格
    getDisplayPrice (item) {
      let price;
      if (item.billing_mode === 0) {
        price = item.price;
      } else {
        if (item.id === this.curPackageId) {
          price = this.sliderValue * item.price;
        } else {
          price = item.min_capacity * item.price;
        }
      }
      return Math.round(price * 100) / 100;
    },
    // 购买流量包
    async handlerPackage (redirect) {
      try {
        this.submitLoading = true;
        const params = {
          id: this.id,
          flow_packet_id: this.curPackageId,
        };
        // 如果是范围流量包，传递选中的容量
        if (this.currentPackage && this.currentPackage.billing_mode === 1) {
          params.selected_capacity = this.sliderValue;
        }
        const res = await buyFlow(params);
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        this.showPackage = false;
        // 刷新流量包列表和流量信息
        this.initData();
        // 是否跳转订单详情
        if (redirect) {
          const host = location.origin;
          const fir = location.pathname.split("/")[1];
          location.href = `${host}/${fir}/order_details.htm?id=${res.data.data.id}`;
        }
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    initData () {
      this.getFlowInfo();
      this.getUsableFlowList();
      this.getFlowPacketList();
    },
  },
};

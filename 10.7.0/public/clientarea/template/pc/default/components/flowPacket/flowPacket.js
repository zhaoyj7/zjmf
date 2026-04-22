const flowPacket = {
  template:
    `
    <div style="flex: 1">
      <el-dialog :visible.sync="showPackage && packageList.length > 0" custom-class="common-package-dialog" :loading="packageLoading">
        <i class="el-icon-close" @click="cancleDialog"></i>
        <div class="dialog-title">
          {{lang.buy_package}}
        </div>
        <!-- Radio 筛选：限时/不限时 -->
        <div class="filter-radio" v-if="hasLimitTime || hasUnlimitedTime">
          <el-radio-group v-model="filterType" @change="onFilterTypeChange">
            <el-radio :label="0" v-if="hasLimitTime">{{lang.limit_time_flow}}</el-radio>
            <el-radio :label="1" v-if="hasUnlimitedTime">{{lang.unlimited_traffic}}</el-radio>
          </el-radio-group>
        </div>
        <div class="con">
          <div class="items">
            <div class="p-item" v-for="item in filteredPackageList" :key="item.id"
              :class="{active: item.id === curPackageId}" @click="choosePackage(item)">
              <p class="price">{{currencyPrefix}}{{getDisplayPrice(item) | filterMoney}}</p>
              <p class="tit">
                {{item.name}}
                <template v-if="item.billing_mode === 0">
                  {{item.capacity}}G
                </template>
                <template v-else>{{item.min_capacity}}-{{item.max_capacity}}G</template>
              </p>
              <i class="el-icon-check"></i>
            </div>
          </div>
          <div class="slider-container" v-if="showSlider">
            <el-slider v-model="sliderValueComputed" :min="sliderMin" :max="sliderMax" :marks="sliderMarks"
              show-input>
            </el-slider>
          </div>
        </div>
        <div class="dialog-footer">
          <el-button class="btn-ok" @click="handlerPackage"
            :loading="submitLoading">{{lang.ticket_btn6}}</el-button>
          <el-button class="btn-no" @click="cancleDialog">{{lang.finance_btn7}}</el-button>
        </div>
      </el-dialog>
      
      <!-- 弹窗模式下的触发按钮 -->
      <el-button v-if="showFlowPacketList && displayMode === 'dialog' && hasFlow && lineType === 'flow'" 
        @click="openFlowListDialog" type="primary" size="small">
        {{lang.flow_packet_list}}
      </el-button>

      <!-- 流量包列表 -->
      <component 
        :is="listWrapper" 
        v-bind="listWrapperProps" 
        v-on="listWrapperEvents"
        v-if="showFlowPacketList && hasFlow && lineType === 'flow' && (displayMode === 'inline' || showFlowListDialog)">
        <div class="dialog-title" v-if="displayMode === 'dialog'">{{lang.flow_packet_list}}</div>
        <div class="dialog-main">
          <el-table :data="flowPacketList" v-loading="flowPacketLoading" style="width: 100%">
            <el-table-column prop="id" :label="lang.flow_packet_id" min-width="100"></el-table-column>
            <el-table-column prop="name" :label="lang.flow_packet_name" min-width="150"></el-table-column>
            <el-table-column :label="lang.flow_packet_create_time" min-width="180">
              <template slot-scope="scope">
                <span>{{scope.row.create_time | formateTime}}</span>
              </template>
            </el-table-column>
            <el-table-column :label="lang.flow_packet_size" min-width="150">
              <template slot-scope="scope">
                {{scope.row.size}}GB
              </template>
            </el-table-column>
            <el-table-column :label="lang.flow_packet_used" min-width="120">
              <template slot-scope="scope">
                {{scope.row.used || 0}}GB
              </template>
            </el-table-column>
            <el-table-column :label="lang.flow_packet_expire_time" min-width="180">
              <template slot-scope="scope">
                <span v-if="scope.row.expire_time === 0">/</span>
                <span v-else>{{scope.row.expire_time | formateTime}}</span>
              </template>
            </el-table-column>
            <el-table-column :label="lang.flow_packet_status" min-width="100">
              <template slot-scope="scope">
                <el-tag v-if="scope.row.status === 1" type="success" size="small">{{lang.flow_packet_status_valid}}</el-tag>
                <el-tag v-else type="warning" size="small">{{lang.flow_packet_status_invalid}}</el-tag>
              </template>
            </el-table-column>
          </el-table>
          <!-- 分页 -->
          <pagination :page-data="flowParams" v-if="flowParams.total > 0"
            @sizechange="handleFlowPageSizeChange" @currentchange="handleFlowPageChange"> 
          </pagination>
        </div>
      </component>
    </div>
    `,
  mixins: [mixin],
  components: {
    pagination
  },
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
  data () {
    return {
      hasFlow: true,
      packageLoading: false,
      submitLoading: false,
      packageList: [],
      curPackageId: '',
      currencyPrefix: JSON.parse(localStorage.getItem("common_set_before")).currency_prefix,
      filterType: 0, // 1: 不限时, 0: 限时
      sliderValue: 10, // 滑动条的值
      showSlider: false, // 是否显示滑动条
      sliderMin: 0,
      sliderMax: 100,
      flowPacketList: [],
      flowPacketLoading: false,
      flowParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0
      },
      showFlowListDialog: false, // 控制流量包列表弹窗
    };
  },
  props: {
    id: {
      type: Number | String,
      required: true,
    },
    showPackage: {
      type: Boolean
    },
    module: {
      type: String,
      default: 'mf_cloud_mysql'
    },
    lineType: {
      type: String,
      default: 'bw'
    },
    displayMode: {
      type: String,
      default: 'inline' // inline | dialog (401使用dialog)
    }
  },
  mounted () {
    this.hasFlow = this.addons_js_arr.includes('FlowPacket');
    if (this.hasFlow) {
      this.getPackageList();
      this.getFlowPacketList();
    }
  },
  computed: {
    showFlowPacketList() {
      const hiddenModules = ['mf_dcim', 'remf_dcim'];
      return !hiddenModules.includes(this.module);
    },
    // 处理滑动条空值问题
    sliderValueComputed: {
      get () {
        return this.sliderValue === '' || this.sliderValue === null ? this.sliderMin : this.sliderValue;
      },
      set (val) {
        this.sliderValue = val;
      }
    },
    // 根据 filterType 筛选流量包
    filteredPackageList () {
      return this.packageList.filter(item => item.long_term_valid === this.filterType);
    },
    // 获取当前选中的流量包
    currentPackage () {
      return this.packageList.find(item => item.id === this.curPackageId);
    },
    // 滑动条标记
    sliderMarks () {
      return {
        [this.sliderMin]: this.sliderMin + 'GB',
        [this.sliderMax]: this.sliderMax + 'GB'
      };
    },
    // 是否有限时流量包
    hasLimitTime () {
      return this.packageList.some(item => item.long_term_valid === 0);
    },
    // 是否有不限时流量包
    hasUnlimitedTime () {
      return this.packageList.some(item => item.long_term_valid === 1);
    },
    // 动态容器组件
    listWrapper () {
      return this.displayMode === 'dialog' ? 'el-dialog' : 'div';
    },
    // 动态容器属性
    listWrapperProps () {
      if (this.displayMode === 'dialog') {
        return {
          width: "1200px",
          visible: this.showFlowListDialog,
          'custom-class': 'flow-list-dialog'
        };
      }
      return {
        class: 'flow-packet-list'
      };
    },
    // 动态容器事件
    listWrapperEvents () {
      if (this.displayMode === 'dialog') {
        return {
          'update:visible': (val) => { this.showFlowListDialog = val; }
        };
      }
      return {};
    },
  },
  watch: {
    // 监听筛选类型变化，自动选中第一个
    filteredPackageList (newList) {
      if (newList.length > 0) {
        this.curPackageId = newList[0].id;
        this.choosePackage(newList[0]);
      }
    }
  },
  methods: {
    async getPackageList () {
      try {
        this.packageLoading = true;
        const res = await getFlowPacket({
          id: this.id,
          page: 1,
          limit: 9999,
        });
        this.packageList = res.data.data.list;
        if (this.packageList.length === 0) {
          this.$emit('cancledialog', false);
          return;
        }
        // 根据数据设置默认 filterType 和选中流量包
        const limitTimeList = this.packageList.filter(item => item.long_term_valid === 0);
        const unlimitedTimeList = this.packageList.filter(item => item.long_term_valid === 1);
        
        // 优先选限时，只有不限时时才选不限时
        if (limitTimeList.length > 0) {
          this.filterType = 0;
          this.curPackageId = limitTimeList[0].id;
          this.choosePackage(limitTimeList[0]);
        } else if (unlimitedTimeList.length > 0) {
          this.filterType = 1;
          this.curPackageId = unlimitedTimeList[0].id;
          this.choosePackage(unlimitedTimeList[0]);
        }
        this.packageLoading = false;
      } catch (error) {
        this.packageLoading = false;
      }
    },
    choosePackage (item) {
      this.curPackageId = item.id;
      // 如果 billing_mode = 1，显示滑动条
      if (item.billing_mode === 1) {
        this.showSlider = true;
        this.sliderMin = item.min_capacity;
        this.sliderMax = item.max_capacity;
        this.sliderValue = item.min_capacity;
      } else {
        this.showSlider = false;
      }
    },
    async handlerPackage () {
      try {
        this.submitLoading = true;
        const params = {
          id: this.id,
          flow_packet_id: this.curPackageId,
        };
        // 如果是范围流量包，传递滑动条的值
        if (this.currentPackage && this.currentPackage.billing_mode === 1) {
          params.selected_capacity = this.sliderValue;
        }
        const res = await buyFlowPacket(params);
        this.$emit('sendpackid', res.data.data.id);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    cancleDialog () {
      this.$emit('cancledialog', false);
    },
    // 获取流量包列表
    async getFlowPacketList () {
      // mf_dcim 和 remf_dcim 不请求流量包列表
      if (!this.showFlowPacketList) return;
      try {
        this.flowPacketLoading = true;
        const res = await getFlowPacketByModule(this.module, {
          id: this.id,
          ...this.flowParams
        });
        this.flowPacketList = res.data.data.list || [];
        this.flowParams.total = res.data.data.count || 0;
        this.flowPacketLoading = false;
      } catch (error) {
        this.flowPacketLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 分页变化
    handleFlowPageChange (page) {
      this.flowParams.page = page;
      this.getFlowPacketList();
    },
    handleFlowPageSizeChange (e) {
      this.flowParams.limit = e;
      this.getFlowPacketList();
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
    // 切换筛选类型时移除焦点
    onFilterTypeChange () {
      document.activeElement?.blur();
    },
    // 打开流量包列表弹窗
    openFlowListDialog () {
      this.showFlowListDialog = true;
      this.getFlowPacketList();
    }
  },
};

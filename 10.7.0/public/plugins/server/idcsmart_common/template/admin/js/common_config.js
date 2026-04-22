const template = document.getElementsByClassName("common_config")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    comConfig,
  },
  data () {
    return {
      id: "",
      tabs: "basic", // basic,cost,config,custom
      hover: true,
      tableLayout: false,
      dataLoading: false,
      delVisible: false,
      delId: "",
      delType: "",
      delDesc: "",
      subLoading: false,
      delTit: lang.sureDelete,
      submitLoading: false,
      payType: "", // 计费方式 free , onetime, recurring_prepayment , recurring_postpaid
      currency_prefix:
        JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥",
      optType: "add", // 新增/编辑
      subOpt: "add", // 子项编辑状态
      comTitle: "",
      // 整个页面
      commonConfig: {
        order_page_description: "",
        allow_qty: 0,
        auto_support: 0,
        server_id: "",
      },
      childInterface: [],
      commonConfigoption: [],
      module_meta: {},
      pricing: {},
      // 默认周期
      defaultCycle: [],
      dataForm: {},
      dataRules: {
        onetime: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 99999999.99,
            message: lang.verify5 + "，" + lang.money_ver,
            type: "warning",
          },
        ],
        server_id: [
          {
            required: true,
            message: `${lang.select}${lang.child_interface}`,
            type: "error",
          },
        ],
      },
      /* 周期表格 */
      defaultBol: false,
      cycleModel: false,
      defaultName: {
        monthly: lang.month,
        quarterly: lang.quarter,
        semaiannually: lang.half_year_fee,
        annually: lang.year_fee,
        biennially: lang.two_year,
        triennianlly: lang.three_year,
      },
      cycleForm: {
        name: "",
        cycle_time: "",
        cycle_unit: "hour",
        amount: null,
        price_factor: null,
      },
      cycleTime: [
        {
          value: "hour",
          label: lang.hour,
        },
        {
          value: "day",
          label: lang.day,
        },
        {
          value: "month",
          label: lang.natural_month,
        },
      ],
      ratioModel: false,
      ratioData: [],
      ratioColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "unit",
          title: lang.cycle_time,
          ellipsis: true,
        },
        {
          colKey: "ratio",
          title: lang.mf_ratio,
          ellipsis: true,
        },
      ],
      loading: false,
      cycleData: [
        {
          id: 1,
          cycle_name: "测试",
          cycle_time: 20,
          price: 100,
          way: "day",
        },
      ],
      // 默认周期
      defaultColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "cycle_time",
          title: lang.cycle_time,
          ellipsis: true,
          ellipsis: true,
        },
        {
          colKey: "amount",
          title: lang.price,
          ellipsis: true,
        },
      ],
      cycleColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "cycle_time",
          title: lang.cycle_time,
          ellipsis: true,
          ellipsis: true,
        },
        // {
        //   colKey: "price_factor",
        //   title: lang.price_factor,
        //   ellipsis: true,
        // },
        {
          colKey: "amount",
          title: lang.price,
          ellipsis: true,
          align: "right",
        },
        {
          colKey: "ratio",
          title: lang.cycle_ratio,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      cycleRules: {
        name: [
          {
            required: true,
            message: lang.input + lang.cycle_name,
            type: "error",
          },
          {
            validator: (val) => val?.length <= 20,
            message: lang.verify8 + "1-20",
            type: "warning",
          },
        ],
        cycle_time: [
          {
            required: true,
            message: lang.input + lang.cycle_time,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
          {
            validator: (val) => val > 0,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 99999999.99,
            message: lang.verify5 + "，" + lang.money_ver,
            type: "warning",
          },
        ],
      },

      /* 配置选项 */
      configData: [],
      configModel: false,
      configLoading: false,
      configColumns: [
        {
          colKey: "drag", // 列拖拽排序必要参数
          title: lang.sort,
          cell: "drag",
          width: 90,
        },
        {
          colKey: "id",
          title: "ID",
          width: 160,
          ellipsis: true,
        },
        {
          colKey: "option_name",
          title: lang.config_name,
          ellipsis: true,
        },
        {
          colKey: "option_type",
          title: lang.type,
          ellipsis: true,
          width: 180,
        },

        {
          colKey: "upgrade",
          title: lang.upgrade_tip,
          width: 180,
        },

        {
          colKey: "hidden",
          title: lang.is_show_pro,
          width: 180,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      /* 配置详情和子项 */
      configOption: [
        {
          value: "os",
          label: lang.configOption.os,
        },
        {
          value: "select",
          label: lang.configOption.select,
        },
        {
          value: "multi_select",
          label: lang.configOption.multi_select,
        },
        {
          value: "radio",
          label: lang.configOption.radio,
        },
        {
          value: "quantity",
          label: lang.configOption.quantity,
        },
        {
          value: "quantity_range",
          label: lang.configOption.quantity_range,
        },
        {
          value: "yes_no",
          label: lang.configOption.yes_no,
        },
        {
          value: "area",
          label: lang.configOption.area,
        },
        {
          value: "cascade",
          label: lang.configOption.cascade,
        },
      ],
      // freeType
      freeType: [
        {
          value: "stage",
          label: lang.stage,
        },
        {
          value: "qty",
          label: lang.qty_charging,
        },
      ],
      configDetail: {
        // 单个配置详情
        option_name: "",
        option_type: "",
        unit: "",
        qty_change: 1,
        option_param: "",
        description: "",
        hidden: 0,
        upgrade: 0,
        // 数量输入/拖动才有
        qty_min: "",
        fee_type: "",
        allow_repeat: 0,
        max_repeat: "",
      },
      backupConfig: {}, // 备份配置详情
      // 子配置项数据
      subTit: "",
      configSub: [],
      configSubModel: false,
      // ========== 级联配置相关数据 ==========
      cascadeGroups: [], // 级联分组列表，用于生成表头（如：级联组1、级联组2等）
      cascadeTableData: [], // 级联表格展示的数据（扁平化后的行数据）
      cascadeColumns: [ // 级联表格列配置
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 100,
          align: "center",
        },
      ],
      cascadeRawData: null, // 级联原始树形数据 { tree: [], group: [] }
      cascadeItemMap: {}, // 级联项ID映射表 { item_id: itemNode }，用于快速查找节点
      cascadeParentMap: {}, // 级联项父级ID映射表 { item_id: parent_item_id }，用于查找父节点

      // ========== 级联编辑状态 ==========
      editingGroupId: 0, // 正在编辑的分组ID（表头）
      editingGroupName: "", // 正在编辑的分组名称
      editingItemId: 0, // 正在编辑的项ID（0=无编辑，-1=临时新增项，>0=编辑已有项）
      editingItemName: "", // 正在编辑的项名称

      // ========== 临时新增项状态 ==========
      tempNewItem: null, // 临时新增项配置 { type: 'sibling'|'child', parentItemId: number, refItemId: number }
      tempNewItemName: "", // 临时新增项的名称

      // ========== 级联价格配置 ==========
      cascadeFeeType: 'fixed', // 级联计费类型 fixed固定价格 qty数量计费 stage阶梯计费
      isCascadeType: false, // 当前是否在编辑级联配置
      cascadeEditRow: null, // 正在编辑的级联行数据（包含item_id和price等信息）
      currentEditingSubIndex: 0, // 当前编辑的区间索引（qty/stage模式使用）
      configSubForm: {
        option_name: "",
        option_param: "",
        // 默认周期
        onetime: "",
        monthly: "-1",
        quarterly: "-1",
        semaiannually: "-1",
        annually: "-1",
        biennially: "-1",
        triennianlly: "-1",
        custom_cycle: [], // 自定义周期
        // type : quantity,quantity_range
        qty_min: "",
        qty_max: "",
        // type: area
        country: "",
      },
      // ========== 级联配置价格 ==========
      cascadeEditRow: {},
      isCascadeType: false,
      cascadeFeeType: "fixed", // 级联费用类型（fixed|qty|stage）
      countryList: [],
      subRules: {
        // 子配置验证
        country: [
          {
            required: true,
            message: lang.select + lang.country,
            type: "error",
          },
        ],
        option_name: [
          {
            required: true,
            message: lang.select + lang.option_value,
            type: "error",
          },
        ],
        qty_min: [
          { required: true, message: lang.input + lang.verify7, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        qty_max: [
          { required: true, message: lang.input + lang.verify7, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify5,
            type: "warning",
          },
        ],
      },
      configRules: {
        option_name: [
          {
            required: true,
            message: lang.input + lang.option_name,
            type: "error",
          },
          {
            validator: (val) => val?.length <= 20,
            message: lang.verify8 + "1-20",
            type: "warning",
          },
        ],
        option_type: [
          {
            required: true,
            message: lang.select + lang.option_type,
            type: "error",
          },
        ],
        fee_type: [
          {
            required: true,
            message: lang.select + lang.cost_type,
            type: "error",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify5,
            type: "warning",
          },
        ],
        qty_change: [
          {
            required: true,
            message: lang.input + lang.drag_step,
            type: "error",
          },
        ],
      },
      subColumns: [
        {
          colKey: "drag", // 列拖拽排序必要参数
          title: "title-slot-drag",
          cell: "drag",
          width: 90,
        },
        {
          colKey: "option_name",
          title: "title",
        },
        {
          colKey: "area",
          title: "title-slot-area",
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 100,
        },
      ],

      // 自定义字段
      customModel: false,
      configOptionValue: [],
      customForm: {},
      customColumns: [
        {
          colKey: "id",
          title: "ID",
          width: 160,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: lang.fields_name,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "type",
          title: lang.type,
          ellipsis: true,
        },
        {
          colKey: "hidden",
          title: lang.hidden,
          width: 180,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 110,
        },
      ],
      isAgent: false,
      isShowCycleTip: false,
      multiliTip: "",
      isNatural: false,
      natural_month_prepaid: 0,
      naturalModel: false, // 自然月预付费弹窗
      cascadeKey: 0
    };
  },
  computed: {
    // 动态计算周期表格列
    computedCycleColumns () {
      if (this.isNatural) {
        // 开启自然月预付费时，在操作列前插入"是否启用"列
        const columns = [...this.cycleColumns];
        const opIndex = columns.findIndex(col => col.colKey === 'op');
        columns.splice(opIndex, 0, {
          colKey: "status",
          title: lang.enable_status,
          width: 120,
        });
        return columns;
      }
      return this.cycleColumns;
    }
  },
  watch: {
    "commonConfig.onetime": {
      immediate: true,
      handler (val) {
        if (val) {
          this.commonConfig.onetime = val === "-1.00" ? "" : val;
        }
      },
    },
  },
  created () {
    this.id = location.href.split("?")[1].split("=")[1];
    this.getServerInfo();
    // 详情
    this.getProDetail();
    // 配置
    this.getConfig();
    this.getCountryList();
    this.getChildInterfaceList();
    this.getPlugin();
  },
  methods: {
    async getPlugin () {
      try {
        const res = await getActivePlugin();
        const temp = res.data.data.list.reduce((all, cur) => {
          all.push(cur.name);
          return all;
        }, []);
        const hasMultiLanguage = temp.includes("MultiLanguage");
        this.multiliTip = hasMultiLanguage
          ? `(${lang.support_multili_mark})`
          : "";
      } catch (error) { }
    },
    async getServerInfo () {
      try {
        const res = await getServeProductDetail(this.id);
        this.isAgent = res.data.data.product.mode === "sync";
        const naturalValue = res.data.data.product?.natural_month_prepaid || 0;
        this.natural_month_prepaid = naturalValue;
        this.isNatural = naturalValue === 1;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 切换tab
    changeTab () { },
    async getChildInterfaceList () {
      try {
        const res = await getChildInterface();
        this.childInterface = res.data.data.list;
      } catch (error) { }
    },
    serverIdChange (id) {
      if (id) {
        getChildModuleParams({ product_id: this.id, server_id: id }).then(
          (res) => {
            this.commonConfigoption = res.data.data.configoption.map(
              (item, index) => {
                const objK = Object.keys(this.configOptionValue);
                item.default = this.configOptionValue[objK[index]];
                return item;
              }
            );
            this.module_meta = res.data.data.module_meta;
          }
        );
      }
    },

    changeOnetime (val) {
      console.log(val);
    },
    async getCountryList () {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) { }
    },
    // 获取商品详情
    async getProDetail () {
      try {
        this.dataLoading = true;
        this.defaultCycle = [];
        const res = await getProductInfo(this.id);
        const temp = res.data.data;
        this.commonConfig = temp.common_product; // 基本信息
        this.configOptionValue = temp.config_option;
        this.commonConfig.server_id = this.commonConfig.server_id || "";
        this.serverIdChange(this.commonConfig.server_id);
        this.$set(this.commonConfig, "onetime", temp.pricing.onetime);
        this.pricing = temp.pricing; // 默认周期
        this.payType = temp.pay_type; // 付费类型
        this.cycleData = temp.custom_cycle || []; // 自定义周期
        const arr = [];
        Object.keys(temp.pricing).forEach((item, index) => {
          if (item !== "onetime") {
            arr.push({
              id: index,
              name: this.defaultName[item],
              cycle_time: this.defaultName[item],
              amount: temp.pricing[item],
              cycle_name: item,
            });
          }
        });
        this.defaultCycle = arr;
        this.dataLoading = false;
        this.isShowCycleTip = this.cycleData.some((item) => !item.ratio);
      } catch (error) {
        this.dataLoading = false;
      }
    },
    // 提交页面配置
    async submitConfig () {
      try {
        const { order_page_description, allow_qty, auto_support, server_id } =
          this.commonConfig;
        const temp = this.defaultCycle.reduce((all, cur) => {
          all[cur.cycle_name] = cur.amount;
          return all;
        }, {});
        const pricing = {
          onetime: this.commonConfig.onetime,
          ...temp,
        };
        const params = {
          product_id: this.id,
          order_page_description,
          allow_qty,
          auto_support,
          pricing,
          server_id,
          configoption: this.commonConfigoption.map((item) => {
            return item.default;
          }),
        };

        this.submitLoading = true;
        const res = await saveProductInfo(params);
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.submitLoading = false;
          this.getProDetail();
        }
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    closeData () {
      this.dataModel = false;
      this.lineModel = false;
      this.lineType = "";
    },
    /**
     * 关闭子配置弹窗
     * 清理级联配置相关状态
     */
    closeSubData () {
      this.configSubModel = false;
      // 清理级联配置状态
      this.isCascadeType = false;
      this.cascadeEditRow = null;
      this.cascadeFeeType = 'fixed';
      this.currentEditingSubIndex = 0;
      if (this.configDetail.option_type === 'cascade') {
        this.getConfigDetail(this.configDetail.id);
      }
    },
    /* 周期相关 */
    async changeRadio () {
      try {
        const res = await getDurationRatio({
          product_id: this.id,
        });
        this.ratioData = res.data.data.list.map((item) => {
          item.ratio = item.ratio ? item.ratio * 1 : null;
          return item;
        });
        this.ratioModel = true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async saveRatio () {
      try {
        const isAll = this.ratioData.every((item) => item.ratio);
        if (!isAll) {
          return this.$message.error(`${lang.input}${lang.mf_ratio}`);
        }
        const temp = JSON.parse(JSON.stringify(this.ratioData)).reduce(
          (all, cur) => {
            all[cur.id] = cur.ratio;
            return all;
          },
          {}
        );
        const params = {
          product_id: this.id,
          ratio: temp,
        };
        this.submitLoading = true;
        const res = await saveDurationRatio(params);
        this.submitLoading = false;
        this.ratioModel = false;
        this.$message.success(res.data.msg);
        this.getProDetail();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    addCycle () {
      if (this.isAgent) {
        return;
      }
      this.optType = "add";
      this.comTitle = lang.add_cycle;
      this.cycleForm.name = "";
      this.cycleForm.cycle_time = "";
      this.cycleForm.cycle_unit = "hour";
      this.cycleForm.amount = "";
      this.cycleModel = true;
    },
    editCycle (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.cycle;
      this.cycleForm = { ...row };
      this.cycleModel = true;
    },
    async submitCycle ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.cycleForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await addAndUpdateProCycle(this.optType, params);
          this.$message.success(res.data.msg);
          this.getProDetail();
          this.cycleModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除周期
    async deleteCycle () {
      try {
        this.submitLoading = true;
        const res = await deleteProCycle({
          product_id: this.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getProDetail();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    formatPrice (val) {
      return (val * 1).toFixed(2);
    },
    // 全局确认是否处于编辑默认周期
    checkEdit () {
      if (this.defaultBol) {
      }
    },
    // 修改默认周期
    changeDefault () {
      if (this.defaultBol) {
        this.submitConfig();
      }
      this.defaultBol = !this.defaultBol;
    },
    /* 自定义字段 */
    addCustom () {
      this.optType = "add";
      this.comTitle = lang.add + lang.custom_fields;
      this.customModel = true;
    },
    submitCustom () { },

    /* 配置选项 */
    async getConfig () {
      try {
        this.configLoading = true;
        const res = await getConfigoption({
          product_id: this.id,
        });
        this.dataLoading = false;
        if (res.data.status === 200) {
          this.configData = res.data.data.configoption;
          this.configLoading = false;
        }
      } catch (error) {
        this.dataLoading = false;
        this.configLoading = false;
      }
    },
    // 切换显示隐藏
    async changeHidden (row) {
      try {
        const { product_id, id, hidden } = row;
        const params = { product_id, id, hidden };
        const res = await changeConfigoption(params);
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.getConfig();
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },

    async changeUpgrade (row) {
      try {
        const { product_id, id, upgrade } = row;
        const params = { product_id, id, upgrade };
        const res = await changeUpgrade(params);
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.getConfig();
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },

    onDragSort ({ targetIndex, newData }) {
      this.dataLoading = true;
      this.configData = newData;
      dragOrderConfig({
        product_id: this.id,
        id: newData[targetIndex].id,
        prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
      })
        .then((res) => {
          this.$message.success(res.data.msg);
          this.getConfig();
        })
        .catch((err) => {
          this.dataLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    onSubDragSort ({ targetIndex, newData }) {
      this.configSub = newData;
      this.subLoading = true;
      dragSubOrderConfig({
        configoption_id: this.configDetail.id,
        id: newData[targetIndex].id,
        prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
      })
        .then((res) => {
          this.$message.success(res.data.msg);
          this.subLoading = false;
          this.getConfigDetail(this.configDetail.id);
        })
        .catch((err) => {
          this.subLoading = false;
          this.$message.error(err.data.msg);
        });
    },

    // 删除配置
    async deleteConfig () {
      try {
        this.submitLoading = true;
        const res = await deleteConfigoption({
          product_id: this.id,
          id: this.delId,
        });
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.getConfig();
          this.delVisible = false;
          this.submitLoading = false;
        }
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    blurRange (val) {
      if (val < 1) {
        this.configDetail.qty_change = 1;
      }
    },
    // 新增/编辑配置项
    addConfig () {
      if (this.isAgent) {
        return;
      }
      this.optType = "add";
      this.configModel = true;
      this.comTitle = lang.create + lang.config_option;
      this.configDetail.id = "";
      this.configDetail.option_name = "";
      this.configDetail.option_type = "";
      this.configDetail.unit = "";
      this.configDetail.qty_change = 1;
      this.configDetail.option_param = "";
      this.configDetail.description = "";
      this.configDetail.hidden = 0;
      this.configDetail.upgrade = 0;
      this.configDetail.fee_type = "";
      this.configDetail.allow_repeat = 0;
      this.configDetail.max_repeat = "";
    },
    closeConfig () {
      this.configModel = false;
      this.getConfig();
    },
    editConfig (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.config_option;
      this.configModel = true;
      this.getConfigDetail(row.id);
    },

    //#region 级联
    // 初始化级联数据
    /**
     * 获取级联树形数据
     * @param {number} id - 配置项ID
     */
    async getCascadeTreeData (id) {
      try {
        // 调用API获取级联树形数据
        const res = await getCascadeTree({
          configoption_id: id,
        });
        const { tree, group } = res.data.data || {};

        // 保存原始数据
        this.cascadeRawData = { tree: tree || [], group: group || [] };
        // 保存分组列表（表头）
        this.cascadeGroups = group || [];
        // 构建ID映射表，方便快速查找节点
        this.buildCascadeItemMaps(tree || []);

        // 构建表格列配置
        this.cascadeColumns = [
          // 动态生成级联分组列
          ...this.cascadeGroups.map(g => ({
            colKey: `group_${g.id}`,
            title: `title-slot-group_${g.id}`, // 使用slot渲染表头
            ellipsis: true,
            width: 150,
            // 为空单元格添加特殊样式类
            className: ({ row }) => {
              return !row[`__item_${g.id}`] ? 'cascade-empty-cell-td' : '';
            }
          })),
          // 价格操作列
          {
            colKey: "op",
            title: lang.price,
            fixed: "right",
            width: 100,
          },
        ];

        // 先清空表格数据，确保重新渲染
        this.cascadeTableData = [];
        await this.$nextTick();

        // 将树形数据转换为表格数据（扁平化）
        this.cascadeTableData = this.processCascadeTreeData(tree || []);

        // 如果当前正在编辑某个级联项，刷新它的引用，避免使用 stale 的 price 数据
        if (this.cascadeEditRow && this.cascadeEditRow.item_id) {
          const updatedEditRow = this.cascadeItemMap[this.cascadeEditRow.item_id];
          if (updatedEditRow) {
            this.cascadeEditRow = updatedEditRow;

            if (this.cascadeFeeType !== 'fixed' && Array.isArray(updatedEditRow.price)) {
              if (updatedEditRow.price.length > 0) {
                this.cascadeEditRow._shared_option_name = updatedEditRow.price[0].option_name || "";
                this.cascadeEditRow._shared_option_param = updatedEditRow.price[0].option_param || "";

                if (this.currentEditingSubIndex >= updatedEditRow.price.length) {
                  this.currentEditingSubIndex = 0;
                }
                this.initCascadePriceForm(updatedEditRow.price[this.currentEditingSubIndex]);
              } else {
                this.currentEditingSubIndex = 0;
                this.initCascadePriceForm(null);
              }
            }

            if (this.cascadeFeeType === 'fixed' && Array.isArray(updatedEditRow.price) && updatedEditRow.price.length > 0) {
              this.initCascadePriceForm(updatedEditRow.price[0]);
            }
          }
        }

        // 多次nextTick确保动态slot正确渲染
        await this.$nextTick();
        await this.$nextTick();
        this.$forceUpdate();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.cascadeGroups = [];
        this.cascadeTableData = [];
      }
    },

    /**
     * 构建级联项ID映射表
     * 递归遍历树形数据，建立两个映射：
     * 1. itemMap: item_id -> 节点对象
     * 2. parentMap: item_id -> parent_item_id
     * @param {Array} tree - 树形数据
     */
    buildCascadeItemMaps (tree) {
      const itemMap = {};
      const parentMap = {};

      // 递归遍历树形结构
      const walk = (nodes, parentId = 0) => {
        if (!Array.isArray(nodes)) return;
        nodes.forEach(node => {
          itemMap[node.item_id] = node; // 保存节点引用
          parentMap[node.item_id] = parentId; // 记录父节点ID
          // 递归处理子节点
          if (Array.isArray(node.children) && node.children.length > 0) {
            walk(node.children, node.item_id);
          }
        });
      };

      walk(tree, 0);
      this.cascadeItemMap = itemMap;
      this.cascadeParentMap = parentMap;
    },

    /**
     * 检查是否有级联项正在编辑
     * @returns {boolean} 是否有编辑状态
     */
    hasCascadeEditing () {
      return !!(this.editingGroupId || (this.editingItemId && this.editingItemId !== -1) || this.tempNewItem);
    },

    /**
     * 检查是否可以开始编辑（防止同时编辑多个项）
     * @param {string} type - 编辑类型 'group' | 'item'
     * @param {number} id - 要编辑的ID
     * @returns {boolean} 是否可以编辑
     */
    canStartCascadeEdit (type, id) {
      // 如果正在编辑分组，且不是当前分组，则警告
      if (this.editingGroupId && !(type === "group" && this.editingGroupId === id)) {
        this.$message.warning("将清空该列及后续所有列的配置，该操作不可撤销。");
        return false;
      }
      // 如果正在编辑项，且不是当前项，则警告
      if (this.editingItemId && !(type === "item" && this.editingItemId === id)) {
        this.$message.warning("将清空该项目及其子项目的配置，该操作不可撤销。");
        return false;
      }
      return true;
    },

    /**
     * 将级联树形数据转换为表格行数据（扁平化）
     * 并处理单元格合并的逻辑
     * @param {Array} tree - 树形数据
     * @returns {Array} 表格行数组
     */
    processCascadeTreeData (tree) {
      const rows = [];
      if (!Array.isArray(tree)) return rows;

      // 递归遍历树，生成叶子节点对应的行
      const walk = (nodes, path = []) => {
        if (!Array.isArray(nodes) || nodes.length === 0) return;

        nodes.forEach((node, index) => {
          const nextPath = [...path, node]; // 记录从根到当前节点的路径
          const hasChildren = Array.isArray(node.children) && node.children.length > 0;
          const isLeaf = node.is_leaf === 1 || !hasChildren;

          // 只处理叶子节点（最终的配置项）
          if (isLeaf) {
            // 获取第一个价格周期信息
            const firstPrice = Array.isArray(node.price) && node.price.length > 0 ? node.price[0] : null;
            const firstCycle = firstPrice && Array.isArray(firstPrice.custom_cycle) && firstPrice.custom_cycle.length > 0
              ? firstPrice.custom_cycle[0]
              : null;

            // 创建行数据
            const row = {
              id: `leaf_${node.item_id}_${rows.length}_${index}`,
              item_id: node.item_id,
              is_leaf: 1,
              price: node.price || [],
              first_cycle_amount: firstCycle ? firstCycle.amount : "",
              first_cycle_name: firstCycle ? firstCycle.name : "",
              fee_type: node.fee_type,
            };

            // 为每个级联分组列填充数据
            this.cascadeGroups.forEach((group, levelIndex) => {
              const colKey = `group_${group.id}`;
              const levelNode = nextPath[levelIndex] || null; // 获取该层级的节点
              row[colKey] = levelNode ? levelNode.item_name : ""; // 显示文本
              row[`__item_${group.id}`] = levelNode ? levelNode.item_id : 0; // 节点ID（内部使用）
              row[`__has_children_${group.id}`] = !!(levelNode && Array.isArray(levelNode.children) && levelNode.children.length > 0);
              row[`__span_${group.id}`] = 1; // 初始化合并跨度为1
            });

            rows.push(row);
            return;
          }

          // 非叶子节点，继续递归
          walk(node.children, nextPath);
        });
      };

      walk(tree, []);

      // ========== 插入临时新增项 ==========
      if (this.tempNewItem) {
        const refItemId = this.tempNewItem.refItemId;
        let insertIndex = -1; // 要插入的位置
        let refPath = []; // 参考项的路径

        // 从后往前查找包含参考项ID的行
        for (let i = rows.length - 1; i >= 0; i--) {
          const row = rows[i];
          let foundRef = false;
          // 遍历每个分组列，查找包含refItemId的列
          for (let j = 0; j < this.cascadeGroups.length; j++) {
            const group = this.cascadeGroups[j];
            if (row[`__item_${group.id}`] === refItemId) {
              foundRef = true;
              insertIndex = i;
              // 记录参考项的完整路径（从根到参考项）
              for (let k = 0; k <= j; k++) {
                const g = this.cascadeGroups[k];
                refPath.push({
                  groupId: g.id,
                  itemId: row[`__item_${g.id}`],
                  itemName: row[`group_${g.id}`]
                });
              }
              break;
            }
          }
          if (foundRef) break;
        }

        if (insertIndex >= 0) {
          // 创建临时行数据（用于在表格中显示可编辑状态）
          const tempRow = {
            id: `temp_new_item`,
            item_id: -1,
            is_leaf: 1,
            price: [],
            first_cycle_amount: "",
            first_cycle_name: "",
            __is_temp_new: true
          };

          if (this.tempNewItem.type === 'sibling') {
            // 新增同级：复制参考项的上级路径，在同一层级添加新项
            this.cascadeGroups.forEach((group, levelIndex) => {
              const colKey = `group_${group.id}`;
              if (levelIndex < refPath.length - 1) {
                // 前面的层级：复制父级路径
                tempRow[colKey] = refPath[levelIndex].itemName;
                tempRow[`__item_${group.id}`] = refPath[levelIndex].itemId;
              } else if (levelIndex === refPath.length - 1) {
                // 当前层级：这是新增项所在的列，设为-1表示临时项
                tempRow[colKey] = "";
                tempRow[`__item_${group.id}`] = -1;
              } else {
                // 后面的层级：留空
                tempRow[colKey] = "";
                tempRow[`__item_${group.id}`] = 0;
              }
              tempRow[`__has_children_${group.id}`] = false;
              tempRow[`__span_${group.id}`] = 1;
            });
            // 插入到参考项后面
            rows.splice(insertIndex + 1, 0, tempRow);
          } else if (this.tempNewItem.type === 'child') {
            // 新增下级：复制参考项的完整路径，在下一层级添加新项
            this.cascadeGroups.forEach((group, levelIndex) => {
              const colKey = `group_${group.id}`;
              if (levelIndex < refPath.length) {
                // 前面的层级：复制父级完整路径
                tempRow[colKey] = refPath[levelIndex].itemName;
                tempRow[`__item_${group.id}`] = refPath[levelIndex].itemId;
              } else if (levelIndex === refPath.length) {
                // 下一层级：这是新增项所在的列，设为-1表示临时项
                tempRow[colKey] = "";
                tempRow[`__item_${group.id}`] = -1;
              } else {
                // 后面的层级：留空
                tempRow[colKey] = "";
                tempRow[`__item_${group.id}`] = 0;
              }
              tempRow[`__has_children_${group.id}`] = false;
              tempRow[`__span_${group.id}`] = 1;
            });
            // 插入到参考项后面
            rows.splice(insertIndex + 1, 0, tempRow);
          }
        }
      }

      // ========== 计算单元格合并跨度 ==========
      const groupKeys = this.cascadeGroups.map(g => `group_${g.id}`);
      // 遍历每个分组列，计算该列每个单元格应该合并的行数
      this.cascadeGroups.forEach((group, groupIndex) => {
        const colKey = `group_${group.id}`;
        let rowIndex = 0;

        while (rowIndex < rows.length) {
          const current = rows[rowIndex];
          let span = 1; // 初始跨度为1

          // 向下查找相同值的连续行
          for (let i = rowIndex + 1; i < rows.length; i++) {
            const next = rows[i];
            // 检查从第一列到当前列的所有值是否相同
            const samePrefix = groupKeys
              .slice(0, groupIndex + 1)
              .every(key => current[key] === next[key]);

            if (!samePrefix) break; // 值不同，停止合并
            span += 1;
          }

          // 设置当前行的跨度
          current[`__span_${group.id}`] = span;
          // 被合并的行跨度设为0（不显示）
          for (let i = rowIndex + 1; i < rowIndex + span; i++) {
            rows[i][`__span_${group.id}`] = 0;
          }

          rowIndex += span;
        }

        // 空单元格不合并
        rows.forEach(row => {
          if (!row[colKey]) row[`__span_${group.id}`] = 1;
        });
      });

      return rows;
    },

    /**
     * 获取单元格的合并配置（rowspan和colspan）
     * TDesign表格通过这个方法来决定单元格的合并方式
     * @param {Object} params - 参数对象
     * @param {Object} params.row - 行数据
     * @param {Object} params.col - 列配置
     * @returns {Object} { rowspan, colspan }
     */
    getCascadeRowspanAndColspan ({ row, col }) {
      // 只处理级联分组列
      if (!col || !col.colKey || !col.colKey.startsWith("group_")) {
        return { rowspan: 1, colspan: 1 };
      }

      const groupId = col.colKey.replace("group_", "");
      const span = row[`__span_${groupId}`];

      if (typeof span === "number") {
        if (span <= 0) return { rowspan: 0, colspan: 0 }; // 被合并的单元格，不显示
        return { rowspan: span, colspan: 1 }; // 合并span行
      }
      return { rowspan: 1, colspan: 1 }; // 默认不合并
    },

    /**
     * 开始编辑级联分组（表头）
     * @param {Object} group - 分组对象
     */
    startEditCascadeGroup (group) {
      if (!this.canStartCascadeEdit("group", group.id)) return;
      this.editingGroupId = group.id;
      this.editingGroupName = group.group_name || "";
    },

    /**
     * 取消编辑级联分组
     */
    cancelEditCascadeGroup () {
      this.editingGroupId = 0;
      this.editingGroupName = "";
    },

    /**
     * 保存级联分组名称
     * @param {Object} group - 分组对象
     */
    async saveCascadeGroup (group) {
      const groupName = (this.editingGroupName || "").trim();
      if (!groupName) {
        this.$message.warning("请输入分组名称");
        return;
      }
      try {
        this.submitLoading = true;
        const res = await updateCascadeGroup({
          configoption_id: this.configDetail.id,
          id: group.id,
          group_name: groupName,
        });
        this.$message.success(res.data.msg);
        this.cancelEditCascadeGroup();
        await this.getCascadeTreeData(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
      }
    },

    /**
     * 请求删除级联分组
     * @param {Object} group - 分组对象
     */
    requestDeleteCascadeGroup (group) {
      if (this.hasCascadeEditing()) {
        this.$message.warning("当前有未保存内容，请先保存或取消");
        return;
      }
      this.delId = group.id;
      this.delType = "cascade_group";
      this.delTit = "确认删除";
      this.delDesc = "将清空该列及后续所有列的配置，该操作不可撤销。";
      this.delVisible = true;
    },

    /**
     * 获取级联节点名称
     * @param {number} itemId - 项ID
     * @returns {string} 项名称
     */
    getCascadeNodeName (itemId) {
      return this.cascadeItemMap[itemId]?.item_name || "";
    },

    /**
     * 开始编辑级联项
     * @param {number} itemId - 项ID
     */
    startEditCascadeItem (itemId) {
      if (!itemId) return;
      if (!this.canStartCascadeEdit("item", itemId)) return;
      this.editingItemId = itemId;
      this.editingItemName = this.getCascadeNodeName(itemId);
    },

    /**
     * 取消编辑级联项
     */
    cancelEditCascadeItem () {
      this.editingItemId = 0;
      this.editingItemName = "";
    },

    /**
     * 保存级联项名称
     * @param {number} itemId - 项ID
     */
    async saveCascadeItem (itemId) {
      const itemName = (this.editingItemName || "").trim();
      if (!itemName) {
        this.$message.warning("请输入级联项名称");
        return;
      }
      try {
        this.submitLoading = true;
        const res = await updateCascadeItem({
          configoption_id: this.configDetail.id,
          id: itemId,
          item_name: itemName,
        });
        this.$message.success(res.data.msg);
        this.cancelEditCascadeItem();
        await this.getCascadeTreeData(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
      }
    },

    /**
     * 创建级联同级项（在当前项的同一层级添加新项）
     * 不使用弹窗，直接在表格中显示可编辑状态
     * @param {number} itemId - 参考项ID（在此项后面添加同级项）
     */
    createCascadeSibling (itemId) {
      if (!itemId) return;
      if (this.hasCascadeEditing()) {
        this.$message.warning("当前有未保存内容，请先保存或取消");
        return;
      }
      if (this.tempNewItem) {
        this.$message.warning("当前有未保存的新增项，请先保存或取消");
        return;
      }
      const parentItemId = this.cascadeParentMap[itemId] || 0;
      this.tempNewItem = {
        type: 'sibling',
        parentItemId: parentItemId,
        refItemId: itemId
      };
      this.tempNewItemName = "";
      this.editingItemId = -1; // 使用-1表示临时新增项
      this.rebuildCascadeTableWithTemp();
    },

    /**
     * 创建级联子级项（在当前项的下一层级添加新项）
     * 不使用弹窗，直接在表格中显示可编辑状态
     * 如果下一列为空，则直接编辑当前行的空列，不新增行
     * @param {number} itemId - 父级项ID（为此项添加子级）
     */
    async createCascadeChild (itemId) {
      if (!itemId) return;
      if (this.hasCascadeEditing()) {
        this.$message.warning("当前有未保存内容，请先保存或取消");
        return;
      }
      if (this.tempNewItem) {
        this.$message.warning("当前有未保存的新增项，请先保存或取消");
        return;
      }

      // 查找当前itemId所在的列索引
      let currentGroupIndex = -1;
      for (let i = 0; i < this.cascadeGroups.length; i++) {
        const group = this.cascadeGroups[i];
        const rows = this.cascadeTableData;
        for (let row of rows) {
          if (row[`__item_${group.id}`] === itemId) {
            currentGroupIndex = i;
            break;
          }
        }
        if (currentGroupIndex >= 0) break;
      }

      // 如果当前项在最后一个分组列，需要本地新增一个分组列
      if (currentGroupIndex >= 0 && currentGroupIndex === this.cascadeGroups.length - 1) {
        const newGroupId = -(this.cascadeGroups.length + 1); // 用负数ID表示本地临时分组
        const newGroupName = `级联组 ${this.cascadeGroups.length + 1}`;
        const newGroup = { id: newGroupId, group_name: newGroupName };

        // 添加到分组列表
        this.cascadeGroups.push(newGroup);
        // 添加对应的表格列
        // 先移除最后的操作列
        const opCol = this.cascadeColumns.pop();
        // 添加新分组列
        this.cascadeColumns.push({
          colKey: `group_${newGroupId}`,
          title: `title-slot-group_${newGroupId}`,
          ellipsis: true,
          width: 150,
          className: ({ row }) => {
            return !row[`__item_${newGroupId}`] ? 'cascade-empty-cell-td' : '';
          }
        });
        // 重新添加操作列
        this.cascadeColumns.push(opCol);

        // 为现有行数据补充新列的字段
        this.cascadeTableData.forEach(row => {
          row[`group_${newGroupId}`] = "";
          row[`__item_${newGroupId}`] = 0;
          row[`__has_children_${newGroupId}`] = false;
          row[`__span_${newGroupId}`] = 1;
        });
        this.cascadeKey++;
        await this.$nextTick();

        // 现在再走正常的添加子级流程
        this.createCascadeChild(itemId);
        return;
      }

      // 检查下一列是否为空
      if (currentGroupIndex >= 0 && currentGroupIndex < this.cascadeGroups.length - 1) {
        const nextGroup = this.cascadeGroups[currentGroupIndex + 1];
        const rows = this.cascadeTableData;
        for (let row of rows) {
          const currentGroup = this.cascadeGroups[currentGroupIndex];
          if (row[`__item_${currentGroup.id}`] === itemId && !row[`__item_${nextGroup.id}`]) {
            this.tempNewItem = {
              type: 'child',
              parentItemId: itemId,
              refItemId: itemId,
              isEditingEmptyCell: true,
              editingGroupId: nextGroup.id,
              editingRowId: row.id
            };
            this.tempNewItemName = "";
            this.editingItemId = -1;
            this.$forceUpdate();
            return;
          }
        }
      }

      // 下一列不为空，使用原来的新增行逻辑
      this.tempNewItem = {
        type: 'child',
        parentItemId: itemId,
        refItemId: itemId,
        isEditingEmptyCell: false
      };
      this.tempNewItemName = "";
      this.editingItemId = -1;
      this.rebuildCascadeTableWithTemp();
    },

    /**
     * 重新构建级联表格数据（包含临时新增项）
     * 在新增同级/子级时调用，将临时项插入到表格中
     * 如果是编辑空单元格，则不重新构建，直接强制更新
     */
    rebuildCascadeTableWithTemp () {
      // 如果是编辑空单元格，不需要重新构建数据
      if (this.tempNewItem && this.tempNewItem.isEditingEmptyCell) {
        this.$forceUpdate();
        return;
      }

      this.cascadeTableData = this.processCascadeTreeData(this.cascadeRawData.tree || []);
      this.$nextTick(() => {
        this.$forceUpdate();
      });
    },

    /**
     * 保存临时新增项
     * 调用API创建新的级联项
     * - 新增同级：需要传递 prev_item_id（在哪个项后面插入）
     * - 新增下级：只需要 parent_item_id
     */
    async saveTempNewItem () {
      const itemName = (this.tempNewItemName || "").trim();
      if (!itemName) {
        this.$message.warning("请输入名称");
        return;
      }
      try {
        this.submitLoading = true;
        const params = {
          configoption_id: this.configDetail.id,
          item_name: itemName,
          parent_item_id: this.tempNewItem.parentItemId,
          hidden: 0,
        };
        // 新增同级时需要传递 prev_item_id（指定在哪个项后面插入）
        if (this.tempNewItem.type === 'sibling') {
          params.prev_item_id = this.tempNewItem.refItemId;
        }
        const res = await createCascadeItem(params);
        this.$message.success(res.data.msg);
        this.cancelTempNewItem();
        await this.getCascadeTreeData(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
      }
    },

    /**
     * 取消临时新增项
     * 清除临时状态并重新构建表格（移除临时行）
     */
    cancelTempNewItem () {
      this.tempNewItem = null;
      this.tempNewItemName = "";
      this.editingItemId = 0;
      // 新增子集项取消后，需要重新构建表格
      this.getConfigDetail(this.configDetail.id);
    },

    /**
     * 请求删除级联项
     * @param {number} itemId - 项ID
     */
    requestDeleteCascadeItem (itemId) {
      if (!itemId) return;
      if (this.hasCascadeEditing()) {
        this.$message.warning("当前有未保存内容，请先保存或取消");
        return;
      }
      this.delId = itemId;
      this.delType = "cascade_item";
      this.delTit = "确认删除";
      this.delDesc = "将清空该项目及其子项目的配置，该操作不可撤销。";
      this.delVisible = true;
    },

    /**
     * 执行删除级联分组操作
     * 调用API删除分组并刷新数据
     */
    async deleteCascadeGroupAction () {
      try {
        this.submitLoading = true;
        const res = await deleteCascadeGroup({
          configoption_id: this.configDetail.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.delDesc = "";
        await this.getCascadeTreeData(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
      }
    },

    /**
     * 执行删除级联项操作
     * 调用API删除项并刷新数据
     */
    async deleteCascadeItemAction () {
      try {
        this.submitLoading = true;
        const res = await deleteCascadeItem({
          configoption_id: this.configDetail.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.delDesc = "";
        await this.getCascadeTreeData(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
      }
    },

    /**
     * 编辑级联项的价格配置
     * 打开价格编辑弹窗，根据fee_type回填数据
     * - fixed模式：回填第一项的价格到表单
     * - qty/stage模式：回填第一个区间的价格到表单，并默认选中第一个区间
     * @param {Object} row - 表格行数据（包含item_id、price数组、fee_type）
     */
    editCascadeItem (row) {
      // 设置弹窗标题和状态
      this.subTit = "配置级联价格";
      this.cascadeEditRow = row; // 保存当前编辑的行数据
      this.isCascadeType = true; // 标记为级联配置模式

      // 直接使用row的fee_type
      this.cascadeFeeType = row.fee_type || 'fixed';

      // 判断是否有价格数据
      if (row.price && Array.isArray(row.price) && row.price.length > 0) {
        // 有价格数据，回填第一条
        const firstPrice = row.price[0];

        // qty/stage模式：从第一项提取option_name和option_param并保存到cascadeEditRow（所有区间共享）
        if (this.cascadeFeeType !== 'fixed') {
          this.cascadeEditRow._shared_option_name = firstPrice.option_name || "";
          this.cascadeEditRow._shared_option_param = firstPrice.option_param || "";
        }

        if (this.cascadeFeeType === 'fixed') {
          // fixed模式：回填第一项到表单
          this.initCascadePriceForm(firstPrice);
        } else {
          // qty/stage模式：回填第一个区间到表单，并默认选中第一个区间
          this.currentEditingSubIndex = 0;
          this.initCascadePriceForm(firstPrice);
        }
      } else {
        // 没有价格数据，初始化新增状态
        this.currentEditingSubIndex = 0;
        this.initCascadePriceForm(null);
      }

      // 打开弹窗
      this.configSubModel = true;
    },

    /**
     * 初始化级联价格表单
     * 根据计费类型和价格数据初始化表单
     * @param {Object|null} priceData - 价格数据，null表示新建
     */
    initCascadePriceForm (priceData) {
      if (priceData) {
        this.configSubForm = {
          option_name: this.cascadeFeeType === 'fixed' ? (priceData.option_name || "") : (this.cascadeEditRow._shared_option_name || ""),
          option_param: this.cascadeFeeType === 'fixed' ? (priceData.option_param || "") : (this.cascadeEditRow._shared_option_param || ""),
          onetime: priceData.onetime || "",
          qty_min: priceData.qty_min,
          qty_max: priceData.qty_max,
          custom_cycle: []
        };

        let customCycleArr = [];
        if (priceData.custom_cycle) {
          customCycleArr = Array.isArray(priceData.custom_cycle)
            ? priceData.custom_cycle
            : Object.values(priceData.custom_cycle || {});
        }

        if (customCycleArr.length > 0) {
          this.configSubForm.custom_cycle = customCycleArr.map(item => {
            return {
              id: item.custom_cycle_id || item.id,
              name: item.name || '',
              amount: item.amount || ''
            };
          });
        } else {
          this.configSubForm.custom_cycle = JSON.parse(JSON.stringify(this.cycleData)).map(item => {
            item.amount = "";
            return item;
          });
        }
      } else {
        this.configSubForm = {
          option_name: "",
          option_param: "",
          onetime: "",
          qty_min: "",
          qty_max: "",
          country: "",
          custom_cycle: JSON.parse(JSON.stringify(this.cycleData)).map(item => {
            item.amount = "";
            return item;
          })
        };
      }
      setTimeout(() => {
        this.$refs.configSubForm && this.$refs.configSubForm.clearValidate();
      },100)
    },

    changeFeeType () {
      if (this.cascadeFeeType !== 'fixed' && this.cascadeEditRow.price.length === 0) {
        this.addCascadeSub();
      }
    },

    /**
     * 切换选中的区间（qty/stage模式）
     * 回填对应区间的价格到表单
     * @param {number} index - 区间索引
     */
    switchCascadeSub (index) {
      if (!this.cascadeEditRow || !this.cascadeEditRow.price || !this.cascadeEditRow.price[index]) {
        return;
      }

      // 如果点击的是当前区间，不做任何操作
      if (this.currentEditingSubIndex === index) {
        return;
      }

      // 保存当前表单数据到对应的区间
      this.saveCascadeSubToPrice();

      // 切换索引
      this.currentEditingSubIndex = index;

      // 回填新区间的数据
      this.initCascadePriceForm(this.cascadeEditRow.price[index]);
    },
    /* 删除区间价格 */
    deleteCascadeRangeItem (id) {
      event.stopPropagation();
      this.cascadeEditRow.price = this.cascadeEditRow.price.filter(item => item.id !== id);
      // 保存价格
      this.saveCascadePrice();
    },
    /**
     * 添加新的价格区间（qty/stage模式）
     * 在当前区间列表中添加一个新区间
     */
    addCascadeSub () {
      if (this.cascadeFeeType === 'fixed') {
        return;
      }
      // 保存当前编辑的区间数据
      this.saveCascadeSubToPrice();
      // 确保price数组存在
      if (!this.cascadeEditRow.price) {
        this.cascadeEditRow.price = [];
      }
      // 计算新区间的默认范围（基于最后一个区间）
      let newQtyMin = null;
      let newQtyMax = null;

      // if (this.cascadeEditRow.price.length > 0) {
      //   const lastSub = this.cascadeEditRow.price[this.cascadeEditRow.price.length - 1];
      //   newQtyMin = (lastSub.qty_max || 0) + 1;
      //   newQtyMax = newQtyMin + 9;
      // }
      // 创建新区间对象
      const newSub = {
        qty_min: newQtyMin,
        qty_max: newQtyMax,
        onetime: "",
        custom_cycle: {}
      };
      // 添加到数组
      this.cascadeEditRow.price.push(newSub);
      // 切换到新区间
      this.currentEditingSubIndex = this.cascadeEditRow.price.length - 1;
      // 初始化表单
      this.initCascadePriceForm(newSub);
    },

    /**
     * 保存当前表单数据到price数组中对应的区间
     * 注意：option_name和option_param是所有区间共享的，保存到cascadeEditRow上，不保存到单独区间
     */
    saveCascadeSubToPrice () {
      if (this.cascadeFeeType === 'fixed' || !this.cascadeEditRow.price) {
        return;
      }

      const currentSub = this.cascadeEditRow.price[this.currentEditingSubIndex];
      if (!currentSub) {
        return;
      }

      // 保存共享字段到cascadeEditRow
      this.cascadeEditRow._shared_option_name = this.configSubForm.option_name;
      this.cascadeEditRow._shared_option_param = this.configSubForm.option_param;

      // 更新当前区间的数据（不包括option_name和option_param）
      currentSub.onetime = this.configSubForm.onetime;
      currentSub.qty_min = this.configSubForm.qty_min;
      currentSub.qty_max = this.configSubForm.qty_max;

      // custom_cycle保持数组格式（深拷贝）
      this.$set(currentSub, 'custom_cycle', JSON.parse(JSON.stringify(this.configSubForm.custom_cycle)));
    },

    //#endregion 级联

    // 新增/编辑配置项
    async submitConfigDetail ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.configDetail));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
            delete params.qty_min;
            delete params.qty_max;
            delete params.order;
          }
          this.submitLoading = true;
          const res = await addAndUpdateConfigoption(this.optType, params);
          this.$message.success(res.data.msg);
          // 提交过后拉取配置详情
          this.getConfigDetail(res.data.data?.id || this.configDetail.id);
          this.submitLoading = false;
          this.optType = "update";
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async getConfigDetail (id) {
      try {
        const res = await getConfigoptionDetail({
          product_id: this.id,
          id,
        });
        this.configDetail = res.data.data.configoption;
        this.backupConfig = JSON.parse(JSON.stringify(this.configDetail));
        this.configSub = res.data.data.configoption_sub;
        if (this.configDetail.option_type === "cascade") {
          // 等待弹窗渲染完成后再加载级联数据，确保动态slot正确渲染
          await this.$nextTick();
          await this.getCascadeTreeData(id);
        } else {
          this.cascadeGroups = [];
          this.cascadeTableData = [];
          this.editingGroupId = 0;
          this.editingItemId = 0;
        }
      } catch (error) {
        console.log(error);
      }
    },
    /* 添加/编辑配置子项 */
    addConfigSub () {
      if (this.isAgent) {
        return;
      }
      this.isCascadeType = false;
      this.subOpt = "add";
      this.configSubModel = true;
      this.configSubForm.custom_cycle = JSON.parse(
        JSON.stringify(this.cycleData)
      ).map((item) => {
        item.amount = "";
        return item;
      });
      this.subTit = lang.add;
      this.configSubForm.option_name = "";
      this.configSubForm.option_param = "";
      this.configSubForm.onetime = "";
      this.configSubForm.qty_min = "";
      this.configSubForm.qty_max = "";
      this.configSubForm.country = "";
    },
    async editSub (row) {
      this.subOpt = "update";
      this.configSubModel = true;
      this.subTit = lang.update;
      try {
        const res = await getConfigSubDetail({
          product_id: this.configDetail.id,
          id: row.id,
        });
        this.configSubForm = res.data.data.configoption_sub;
      } catch (error) { }
    },
    /**
     * 提交子配置表单
     * - 如果是级联配置（isCascadeType=true），调用级联价格保存API
     * - 否则，使用原有的配置项保存逻辑
     */
    async submitConfigSub ({ validateResult, firstError }) {
      if (validateResult === true) {
        // 判断是否为级联配置
        if (this.isCascadeType) {
          // 级联配置：调用级联价格保存方法
          await this.saveCascadePrice();
        } else {
          // 原有逻辑：普通配置项保存
          try {
            const params = JSON.parse(JSON.stringify(this.configSubForm));
            params.product_id = this.configDetail.id;
            if (this.subOpt === "add") {
              delete params.id;
            }
            // quantity,quantity_range
            const _type = this.configDetail.option_type;
            if (_type === "quantity" || _type === "quantity_range") {
              delete params.option_name;
            } else {
              delete params.qty_min;
              delete params.qty_max;
            }
            params.custom_cycle = params.custom_cycle.reduce((all, cur) => {
              all[cur.id] = cur.amount;
              return all;
            }, {});
            this.submitLoading = true;
            const res = await addAndUpdateConfigSub(this.subOpt, params);
            this.$message.success(res.data.msg);
            // 提交过后拉取配置详情
            this.getConfigDetail(this.configDetail.id);
            this.configSubModel = false;
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },

    /**
     * 保存级联价格配置
     * 根据cascadeFeeType调用不同的API参数格式
     * - fixed：固定价格，传递onetime和custom_cycle（对象格式）
     * - qty/stage：数量/阶梯计费，传递option_name、option_param和subs数组
     */
    async saveCascadePrice () {
      try {
        this.submitLoading = true;

        // 构建基础参数
        const params = {
          configoption_id: this.configDetail.id,
          item_id: this.cascadeEditRow.item_id,
          fee_type: this.cascadeFeeType
        };

        if (this.cascadeFeeType === 'fixed') {
          // fixed模式：固定价格
          params.onetime = this.configSubForm.onetime || 0;

          // 转换custom_cycle格式：数组转对象 { 周期id: 金额 }
          if (this.configSubForm.custom_cycle && this.configSubForm.custom_cycle.length > 0) {
            params.custom_cycle = this.configSubForm.custom_cycle.reduce((obj, item) => {
              obj[item.id] = item.amount || "";
              return obj;
            }, {});
          }
        } else {
          // qty/stage模式：数量计费/阶梯计费
          // 先保存当前编辑的区间到price数组
          this.saveCascadeSubToPrice();

          // 传递option_name、option_param和subs数组（从cascadeEditRow获取，所有区间共享）
          params.option_name = this.cascadeEditRow._shared_option_name || "";
          params.option_param = this.cascadeEditRow._shared_option_param || "";

          // 构建subs数组：所有区间一起保存
          params.subs = (this.cascadeEditRow.price || []).map(item => {
            // custom_cycle转换为对象格式
            let customCycleObj = {};
            if (Array.isArray(item.custom_cycle)) {
              // 数组格式 -> 对象格式
              customCycleObj = item.custom_cycle.reduce((obj, cycle) => {
                obj[cycle.id || cycle.custom_cycle_id] = cycle.amount || "";
                return obj;
              }, {});
            } else if (typeof item.custom_cycle === 'object') {
              // 已经是对象格式
              customCycleObj = item.custom_cycle;
            }

            return {
              id: item.id || undefined, // 如果有id表示更新，没有id表示新增
              qty_min: Number(item.qty_min) || 0,
              qty_max: Number(item.qty_max) || 0,
              onetime: Number(item.onetime) || 0,
              custom_cycle: customCycleObj
            };
          });
        }

        // 调用API保存
        const res = await setCascadeItemPrice(params);
        this.$message.success(res.data.msg);

        // 关闭弹窗
        if (this.cascadeFeeType === 'fixed') {
          this.configSubModel = false;
          this.isCascadeType = false;
        }

      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.submitLoading = false;
        this.getCascadeTreeData(this.configDetail.id);
      }
    },
    async autoFill (name, data) {
      try {
        const price = JSON.parse(JSON.stringify(data)).reduce((all, cur) => {
          if (cur.amount) {
            all[cur.id] = cur.amount;
          }
          return all;
        }, {});
        const params = {
          product_id: this.id,
          price,
        };
        const res = await fillDurationRatio(params);
        const fillPrice = res.data.data.list;
        this[name].custom_cycle = this[name].custom_cycle.map((item) => {
          item.amount = fillPrice[item.id];
          return item;
        });
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 修改首个套餐
    changeMonth1 (val, item) {
      if ((typeof val === "string" && isNaN(val * 1)) || val * 1 < 0) {
        return false;
      }
      if (item) {
        // 失去焦点格式化价格
        let index = this.cycleData.findIndex((el) => el.id === item.id);
        this.configSubForm.custom_cycle[index].amount = (val * 1).toFixed(2);
        return false;
      }
      // 自动生成价格  月价格 * （月价格 / 周期价格）
      let temp = JSON.parse(JSON.stringify(this.configSubForm.custom_cycle));
      const curPrice = temp[0].amount;
      temp = temp.map((item) => {
        // 当首个周期为0的时候，全都为0
        if (this.cycleData[0].amount * 1 === 0) {
          if (item.id !== this.cycleData[0].id) {
            item.amount = "0.00";
          }
        } else {
          if (item.id !== this.cycleData[0].id) {
            const curId = this.cycleData.filter((ele) => item.id === ele.id);
            if (curId[0].amount * 1 === 0) {
              item.amount = "0.00";
            } else {
              item.amount = (
                curPrice *
                (((curId[0].amount * 1) / this.cycleData[0].amount) * 1)
              ).toFixed(2);
            }
          }
        }
        return item;
      });
      this.configSubForm.custom_cycle = temp;
    },
    // 删除配置子项
    async deleteSub () {
      try {
        const params = {
          configoption_id: this.configDetail.id,
          id: this.delId,
        };
        this.submitLoading = true;
        const res = await deleteConfigSub(params);
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getConfigDetail(this.configDetail.id);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 通用删除按钮 */
    comDel (type, row) {
      if (this.isAgent) {
        return;
      }
      this.delDesc = "";
      this.delId = row.id;
      this.delTit = lang.sureDelete;
      if (type === "cycle") {
        this.delTit = lang.sure_del_cycle;
      }
      this.delType = type;
      this.delVisible = true;
    },
    // 打开自然月预付费弹窗
    openNaturalModal () {
      this.naturalModel = true;
    },
    // 提交自然月预付费开关
    async submitNaturalSwitch () {
      try {
        const params = {
          id: this.id,
          natural_month_prepaid: this.natural_month_prepaid
        };
        this.submitLoading = true;
        const res = await changeNaturalSwitch(params);
        this.$message.success(res.data.msg);
        this.naturalModel = false;
        this.submitLoading = false;
        this.getProDetail();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 切换周期启用状态
    async changeCycleStatus (row) {
      try {
        const params = {
          product_id: this.id,
          id: row.id,
          status: row.status
        };
        const res = await updateCycleStatus(params);
        this.$message.success(res.data.msg);
        this.getProDetail();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 通用删除
    sureDelete () {
      switch (this.delType) {
        case "cycle":
          return this.deleteCycle();
        case "config":
          return this.deleteConfig();
        case "sub":
          return this.deleteSub();
        case "cascade_group":
          return this.deleteCascadeGroupAction();
        case "cascade_item":
          return this.deleteCascadeItemAction();
        default:
          return null;
      }
    },
  },
}).$mount(template);
